<?php

namespace App\Filament\Resources\CertificateImportBatches\Pages;

use App\Filament\Resources\CertificateImportBatches\CertificateImportBatchResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCertificateImportBatch extends EditRecord
{
    protected static string $resource = CertificateImportBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
