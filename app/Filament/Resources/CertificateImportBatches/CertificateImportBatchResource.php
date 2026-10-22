<?php

namespace App\Filament\Resources\CertificateImportBatches;

use App\Filament\Resources\CertificateImportBatches\Pages\ListCertificateImportBatches;
use App\Jobs\ProcessBulkCertificateUpload;
use App\Models\CertificateImportBatch;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class CertificateImportBatchResource extends Resource
{
    protected static ?string $model = CertificateImportBatch::class;

    protected static ?string $navigationLabel = 'Riwayat Impor Massal';

    protected static ?string $modelLabel = 'Batch Impor';

    protected static ?string $pluralModelLabel = 'Riwayat Impor Massal';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentDuplicate;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Batch ID')
                    ->sortable(),

                TextColumn::make('uploader.name')
                    ->label('Diunggah Oleh')
                    ->default('Sistem / Admin'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'completed_with_errors' => 'warning',
                        'failed' => 'danger',
                        'processing' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('total_rows')
                    ->label('Total Baris')
                    ->numeric(),

                TextColumn::make('successful_rows')
                    ->label('Berhasil')
                    ->numeric()
                    ->color('success'),

                TextColumn::make('failed_rows')
                    ->label('Gagal')
                    ->numeric()
                    ->color('danger'),

                TextColumn::make('skipped_rows')
                    ->label('Dilewati')
                    ->numeric()
                    ->color('warning'),

                TextColumn::make('started_at')
                    ->label('Mulai')
                    ->dateTime('d M Y H:i:s'),

                TextColumn::make('completed_at')
                    ->label('Selesai')
                    ->dateTime('d M Y H:i:s'),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([
                Action::make('downloadReport')
                    ->label('Unduh Laporan Kendala')
                    ->icon('heroicon-o-document-text')
                    ->color('danger')
                    ->visible(fn (CertificateImportBatch $record): bool => !empty($record->error_report_path) && Storage::disk('local')->exists($record->error_report_path))
                    ->action(fn (CertificateImportBatch $record) => Storage::disk('local')->download($record->error_report_path, "laporan_kendala_batch_{$record->id}.csv")),

                Action::make('retry')
                    ->label('Ulangi Proses')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (CertificateImportBatch $record): bool => in_array($record->status, ['failed', 'completed_with_errors']))
                    ->action(function (CertificateImportBatch $record): void {
                        ProcessBulkCertificateUpload::dispatch($record);
                        Notification::make()
                            ->title('Proses Ulang Dijadwalkan')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCertificateImportBatches::route('/'),
        ];
    }
}
