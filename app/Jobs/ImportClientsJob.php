<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\CsvImportService;
use App\Services\DuplicateService;
use Illuminate\Support\Facades\Log;

class ImportClientsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $path;

    /**
     * Create a new job instance.
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Execute the job.
     */
    public function handle(
        CsvImportService $importService,
        DuplicateService $duplicateService
    ) {
        $path = storage_path('app/' . $this->path);

        $stats = $importService->processFile($path, $duplicateService);

        \App\Models\ImportSummary::updateOrCreate(
            ['file_name' => basename($path)],
            [
                'total_rows' => $stats['total_rows'] ?? 0,
                'inserted'   => $stats['inserted'] ?? 0,
                'skipped'    => $stats['skipped'] ?? 0,
                'duplicates' => $stats['duplicates'] ?? 0,
            ]
        );
    }
}
