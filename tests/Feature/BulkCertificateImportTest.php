<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\CertificateImportBatch;
use App\Services\BulkCertificateImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class BulkCertificateImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulk_certificate_import_pipeline_succeeds_with_valid_files(): void
    {
        Storage::fake('local');

        // Create temporary real files for ZipArchive to work with
        $tempZipPath = tempnam(sys_get_temp_dir(), 'test_zip_');
        $zip = new ZipArchive();
        $zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('smp_dodi_1.pdf', "%PDF-1.4\nSample Dodi PDF content");
        $zip->addFromString('sma_rina_2.pdf', "%PDF-1.4\nSample Rina PDF content");
        $zip->close();

        // Save ZIP into storage fake
        $zipStoragePath = 'certificate-imports/test_batch.zip';
        Storage::disk('local')->put($zipStoragePath, file_get_contents($tempZipPath));
        @unlink($tempZipPath);

        // Prepare CSV content
        $csvContent = implode("\n", [
            'bebras_username,participant_name,school_name,category,award_type,year,file_name',
            'smp_dodi_1,Dodi Pratama,SMP Negeri 2 Balige,Penggalang,Participant,2024,smp_dodi_1.pdf',
            'sma_rina_2,Rina Manurung,SMA Unggul Del,Penegak,Winner,2024,sma_rina_2.pdf',
        ]);

        $csvStoragePath = 'certificate-imports/test_batch.csv';
        Storage::disk('local')->put($csvStoragePath, $csvContent);

        $batch = CertificateImportBatch::create([
            'status' => CertificateImportBatch::STATUS_PENDING,
            'archive_path' => $zipStoragePath,
            'csv_path' => $csvStoragePath,
        ]);

        $service = new BulkCertificateImportService();
        $service->process($batch);

        $batch->refresh();

        $this->assertEquals(CertificateImportBatch::STATUS_COMPLETED, $batch->status);
        $this->assertEquals(2, $batch->successful_rows);
        $this->assertEquals(0, $batch->failed_rows);

        $this->assertDatabaseHas('certificates', [
            'bebras_username' => 'smp_dodi_1',
            'participant_name' => 'Dodi Pratama',
            'category' => 'Penggalang',
            'award_type' => 'Participant',
            'year' => 2024,
        ]);

        $this->assertDatabaseHas('certificates', [
            'bebras_username' => 'sma_rina_2',
            'participant_name' => 'Rina Manurung',
            'category' => 'Penegak',
            'award_type' => 'Winner',
            'year' => 2024,
        ]);
    }

    public function test_bulk_certificate_import_handles_duplicates_and_missing_files(): void
    {
        Storage::fake('local');

        $tempZipPath = tempnam(sys_get_temp_dir(), 'test_zip2_');
        $zip = new ZipArchive();
        $zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('ada_di_zip.pdf', "%PDF-1.4\nValid PDF");
        $zip->close();

        $zipStoragePath = 'certificate-imports/test_batch2.zip';
        Storage::disk('local')->put($zipStoragePath, file_get_contents($tempZipPath));
        @unlink($tempZipPath);

        // Pre-create duplicate record
        Certificate::create([
            'bebras_username' => 'ada_di_zip',
            'participant_name' => 'Peserta Sudah Ada',
            'category' => 'Siaga',
            'award_type' => 'Participant',
            'year' => 2024,
            'file_path' => 'certificates/2024/ada.pdf',
        ]);

        // CSV with 1 duplicate and 1 missing PDF
        $csvContent = implode("\n", [
            'bebras_username,participant_name,school_name,category,award_type,year,file_name',
            'ada_di_zip,Peserta Sudah Ada,SD 1,Siaga,Participant,2024,ada_di_zip.pdf',
            'hilang_di_zip,Peserta Hilang,SD 2,SiKecil,Winner,2024,tidak_ada.pdf',
        ]);

        $csvStoragePath = 'certificate-imports/test_batch2.csv';
        Storage::disk('local')->put($csvStoragePath, $csvContent);

        $batch = CertificateImportBatch::create([
            'status' => CertificateImportBatch::STATUS_PENDING,
            'archive_path' => $zipStoragePath,
            'csv_path' => $csvStoragePath,
        ]);

        $service = new BulkCertificateImportService();
        $service->process($batch);

        $batch->refresh();

        $this->assertEquals(CertificateImportBatch::STATUS_FAILED, $batch->status);
        $this->assertEquals(1, $batch->skipped_rows);
        $this->assertEquals(1, $batch->failed_rows);
        $this->assertNotNull($batch->error_report_path);
    }
}
