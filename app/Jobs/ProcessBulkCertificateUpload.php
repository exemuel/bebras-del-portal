<?php

namespace App\Jobs;

use App\Models\CertificateImportBatch;
use App\Services\BulkCertificateImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBulkCertificateUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600; // 10 minutes max for bulk operations

    public function __construct(public CertificateImportBatch $batch)
    {
    }

    public function handle(BulkCertificateImportService $service): void
    {
        $service->process($this->batch);
    }
}
