<?php

namespace App\Filament\Resources\CertificateImportBatches\Pages;

use App\Filament\Resources\CertificateImportBatches\CertificateImportBatchResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCertificateImportBatches extends ListRecords
{
    protected static string $resource = CertificateImportBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
