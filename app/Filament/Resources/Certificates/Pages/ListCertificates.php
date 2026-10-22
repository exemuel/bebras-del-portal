<?php

namespace App\Filament\Resources\Certificates\Pages;

use App\Filament\Resources\Certificates\CertificateResource;
use App\Jobs\ProcessBulkCertificateUpload;
use App\Models\CertificateImportBatch;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListCertificates extends ListRecords
{
    protected static string $resource = CertificateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('bulkImport')
                ->label('Bulk Import (ZIP + CSV)')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->modalHeading('Unggah Massal Sertifikat (ZIP & CSV)')
                ->modalDescription('Unggah berkas ZIP yang berisi berkas PDF sertifikat beserta berkas CSV yang berisi data pemetaan peserta.')
                ->schema([
                    FileUpload::make('archive')
                        ->label('Berkas ZIP Sertifikat')
                        ->acceptedFileTypes(['application/zip', 'application/x-zip-compressed', 'application/octet-stream'])
                        ->disk('local')
                        ->directory('certificate-imports')
                        ->required()
                        ->helperText('Arsip ZIP berisi seluruh file PDF sertifikat peserta.'),

                    FileUpload::make('csv')
                        ->label('Berkas CSV Metadata')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                        ->disk('local')
                        ->directory('certificate-imports')
                        ->required()
                        ->helperText('Header CSV wajib: bebras_username, participant_name, category, award_type, year, file_name (opsional: school_name)'),
                ])
                ->action(function (array $data): void {
                    $batch = CertificateImportBatch::create([
                        'uploaded_by' => Auth::id(),
                        'status' => CertificateImportBatch::STATUS_PENDING,
                        'archive_path' => $data['archive'],
                        'csv_path' => $data['csv'],
                    ]);

                    ProcessBulkCertificateUpload::dispatch($batch);

                    Notification::make()
                        ->title('Proses Impor Dimulai')
                        ->body("Batch impor #{$batch->id} sedang diproses di latar belakang. Anda dapat memantau statusnya di menu Riwayat Impor.")
                        ->success()
                        ->send();
                }),

            CreateAction::make()
                ->label('Tambah Sertifikat Manual'),
        ];
    }
}
