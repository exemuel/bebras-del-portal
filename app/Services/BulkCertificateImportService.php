<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\CertificateImportBatch;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class BulkCertificateImportService
{
    /**
     * Process a bulk certificate import batch.
     */
    public function process(CertificateImportBatch $batch): void
    {
        $batch->update([
            'status' => CertificateImportBatch::STATUS_PROCESSING,
            'started_at' => now(),
        ]);

        $tempExtractDir = storage_path('app/temporary/import_' . $batch->id . '_' . Str::random(8));
        File::makeDirectory($tempExtractDir, 0755, true, true);

        $errorRows = [];
        $processed = 0;
        $successful = 0;
        $failed = 0;
        $skipped = 0;

        try {
            // 1. Locate zip and csv on local/private disk
            $zipFullPath = Storage::disk('local')->path($batch->archive_path);
            $csvFullPath = Storage::disk('local')->path($batch->csv_path);

            if (!file_exists($zipFullPath) || !file_exists($csvFullPath)) {
                throw new \RuntimeException("Berkas ZIP atau CSV tidak ditemukan di storage.");
            }

            // 2. Extract ZIP safely
            $zip = new ZipArchive();
            $res = $zip->open($zipFullPath);
            if ($res !== true) {
                throw new \RuntimeException("Gagal membuka berkas ZIP (Error code: {$res}).");
            }

            // Path traversal protection
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entryName = $zip->getNameIndex($i);
                if (str_contains($entryName, '..') || str_starts_with($entryName, '/') || str_starts_with($entryName, '\\')) {
                    $zip->close();
                    throw new \SecurityException("Berkas ZIP mengandung path tidak aman (path traversal): {$entryName}");
                }
            }

            $zip->extractTo($tempExtractDir);
            $zip->close();

            // 3. Build a map of extracted files (basename => full path)
            $extractedFiles = [];
            foreach (File::allFiles($tempExtractDir) as $file) {
                $extractedFiles[strtolower($file->getFilename())] = $file->getRealPath();
            }

            // 4. Parse CSV
            $handle = fopen($csvFullPath, 'r');
            if ($handle === false) {
                throw new \RuntimeException("Tidak dapat membaca berkas CSV.");
            }

            $header = fgetcsv($handle, 2048, ',');
            if (!$header) {
                fclose($handle);
                throw new \RuntimeException("Berkas CSV kosong atau tidak memiliki header.");
            }

            // Normalize header columns
            $headerMap = [];
            foreach ($header as $idx => $col) {
                $cleanCol = strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $col)));
                $headerMap[$cleanCol] = $idx;
            }

            $requiredCols = ['bebras_username', 'participant_name', 'category', 'award_type', 'year'];
            foreach ($requiredCols as $req) {
                if (!isset($headerMap[$req])) {
                    fclose($handle);
                    throw new \RuntimeException("Kolom wajib '{$req}' tidak ditemukan pada header CSV.");
                }
            }

            $rowNum = 1;
            while (($data = fgetcsv($handle, 4096, ',')) !== false) {
                $rowNum++;
                $processed++;

                $username = isset($headerMap['bebras_username']) ? trim($data[$headerMap['bebras_username']] ?? '') : '';
                $name = isset($headerMap['participant_name']) ? trim($data[$headerMap['participant_name']] ?? '') : '';
                $school = isset($headerMap['school_name']) ? trim($data[$headerMap['school_name']] ?? '') : null;
                $category = isset($headerMap['category']) ? trim($data[$headerMap['category']] ?? '') : '';
                $award = isset($headerMap['award_type']) ? trim($data[$headerMap['award_type']] ?? '') : '';
                $year = isset($headerMap['year']) ? intval(trim($data[$headerMap['year']] ?? '0')) : 0;
                $fileName = isset($headerMap['file_name']) ? trim($data[$headerMap['file_name']] ?? '') : '';

                // If file_name column empty, fall back to username.pdf
                if (empty($fileName)) {
                    $fileName = $username . '.pdf';
                }

                // Row validations
                if (empty($username) || empty($name)) {
                    $failed++;
                    $errorRows[] = [$rowNum, $username, 'Username atau Nama peserta kosong.'];
                    continue;
                }

                $validCategories = ['SiKecil', 'Siaga', 'Penggalang', 'Penegak'];
                if (!in_array($category, $validCategories, true)) {
                    $failed++;
                    $errorRows[] = [$rowNum, $username, "Kategori '{$category}' tidak valid. Harus salah satu dari: " . implode(', ', $validCategories)];
                    continue;
                }

                $validAwards = ['Participant', 'Winner'];
                if (!in_array($award, $validAwards, true)) {
                    $failed++;
                    $errorRows[] = [$rowNum, $username, "Tipe penghargaan '{$award}' tidak valid. Harus 'Participant' atau 'Winner'."];
                    continue;
                }

                if ($year < 2010 || $year > 2050) {
                    $failed++;
                    $errorRows[] = [$rowNum, $username, "Tahun '{$year}' di luar jangkauan valid."];
                    continue;
                }

                // Match PDF file in extracted files
                $lookupKey = strtolower($fileName);
                if (!isset($extractedFiles[$lookupKey])) {
                    $failed++;
                    $errorRows[] = [$rowNum, $username, "Berkas sertifikat '{$fileName}' tidak ditemukan dalam ZIP."];
                    continue;
                }

                $sourcePdf = $extractedFiles[$lookupKey];
                $fileSize = filesize($sourcePdf);
                if ($fileSize < 10) {
                    $failed++;
                    $errorRows[] = [$rowNum, $username, "Berkas '{$fileName}' terlalu kecil atau korup."];
                    continue;
                }

                // Verify PDF signature (%PDF-)
                $pdfHandle = fopen($sourcePdf, 'rb');
                $pdfHeader = fread($pdfHandle, 5);
                fclose($pdfHandle);
                if ($pdfHeader !== '%PDF-') {
                    $failed++;
                    $errorRows[] = [$rowNum, $username, "Berkas '{$fileName}' bukan berkas PDF yang valid."];
                    continue;
                }

                $canonicalUser = Certificate::normalizeUsername($username);

                // Check duplicate
                $existing = Certificate::where('bebras_username', $canonicalUser)
                    ->where('year', $year)
                    ->where('category', $category)
                    ->where('award_type', $award)
                    ->first();

                if ($existing) {
                    $skipped++;
                    $errorRows[] = [$rowNum, $username, "Sertifikat untuk ({$canonicalUser}, {$year}, {$category}, {$award}) sudah ada di database (dilewati)."];
                    continue;
                }

                // Copy to private storage
                $sha256 = hash_file('sha256', $sourcePdf);
                $storageSubpath = "certificates/{$year}/" . Str::slug($canonicalUser) . "_{$award}_" . substr($sha256, 0, 8) . ".pdf";
                
                Storage::disk('local')->put($storageSubpath, file_get_contents($sourcePdf));

                // Save record
                Certificate::create([
                    'bebras_username' => $canonicalUser,
                    'participant_name' => $name,
                    'school_name' => $school,
                    'category' => $category,
                    'award_type' => $award,
                    'year' => $year,
                    'file_path' => $storageSubpath,
                    'original_filename' => $fileName,
                    'file_size' => $fileSize,
                    'file_sha256' => $sha256,
                ]);

                $successful++;
            }

            fclose($handle);

            // Generate error report CSV if there are failures or skips
            $errorReportPath = null;
            if (!empty($errorRows)) {
                $errorReportSubpath = "certificate-reports/report_batch_{$batch->id}_" . date('Ymd_His') . ".csv";
                
                $memStream = fopen('php://temp', 'r+');
                fputcsv($memStream, ['Baris CSV', 'Bebras Username', 'Pesan Kendala / Status']);
                foreach ($errorRows as $err) {
                    fputcsv($memStream, $err);
                }
                rewind($memStream);
                $csvData = stream_get_contents($memStream);
                fclose($memStream);

                Storage::disk('local')->put($errorReportSubpath, $csvData);
                $errorReportPath = $errorReportSubpath;
            }

            $finalStatus = CertificateImportBatch::STATUS_COMPLETED;
            if ($failed > 0 && $successful > 0) {
                $finalStatus = CertificateImportBatch::STATUS_COMPLETED_WITH_ERRORS;
            } elseif ($failed > 0 && $successful === 0) {
                $finalStatus = CertificateImportBatch::STATUS_FAILED;
            }

            $batch->update([
                'status' => $finalStatus,
                'total_rows' => $processed,
                'processed_rows' => $processed,
                'successful_rows' => $successful,
                'failed_rows' => $failed,
                'skipped_rows' => $skipped,
                'error_report_path' => $errorReportPath,
                'completed_at' => now(),
            ]);

        } catch (\Throwable $e) {
            Log::error("Bulk certificate import failed for batch {$batch->id}: " . $e->getMessage(), [
                'exception' => $e,
            ]);

            $batch->update([
                'status' => CertificateImportBatch::STATUS_FAILED,
                'total_rows' => $processed,
                'processed_rows' => $processed,
                'successful_rows' => $successful,
                'failed_rows' => $failed,
                'completed_at' => now(),
            ]);
        } finally {
            // Cleanup temp extract folder
            if (File::isDirectory($tempExtractDir)) {
                File::deleteDirectory($tempExtractDir);
            }
        }
    }
}
