<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Jobs\ImportClientsJob;
use App\Models\Client;
use App\Models\ImportLog;
use App\Models\ImportSummary;
use App\Services\CsvImportService;
use App\Services\DuplicateService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProcessClientImportJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_processes_valid_csv_and_inserts_clients()
    {
        $relativePath = 'test_valid_clients.csv';
        $fullPath = storage_path('app/' . $relativePath);

        file_put_contents($fullPath, implode("\n", [
            'company_name,email,phone_number',
            'ABC,test1@example.com,9801234567',
            'XYZ,test2@example.com,9801234568',
        ]));

        $job = new ImportClientsJob($relativePath);

        $job->handle(
            app(CsvImportService::class),
            app(DuplicateService::class)
        );

        $this->assertEquals(2, Client::count());
        $this->assertEquals(0, ImportLog::count());

        $summary = ImportSummary::where('file_name', 'test_valid_clients.csv')->first();
        $this->assertNotNull($summary);

        unlink($fullPath);
    }

    public function test_job_marks_only_second_occurrence_as_duplicate()
    {
        $relativePath = 'test_duplicate_clients.csv';
        $fullPath = storage_path('app/' . $relativePath);

        file_put_contents($fullPath, implode("\n", [
            'company_name,email,phone_number',
            'ABC,test1@example.com,9801234567',
            'ABC,test1@example.com,9801234567',
        ]));

        $job = new ImportClientsJob($relativePath);

        $job->handle(
            app(CsvImportService::class),
            app(DuplicateService::class)
        );

        $clients = Client::orderBy('id')->get();

        $this->assertCount(2, $clients);
        $this->assertNull($clients[0]->duplicate_group_id);
        $this->assertNotNull($clients[1]->duplicate_group_id);

        unlink($fullPath);
    }

    public function test_job_skips_invalid_rows_and_logs_them()
    {
        $relativePath = 'test_invalid_clients.csv';
        $fullPath = storage_path('app/' . $relativePath);

        file_put_contents($fullPath, implode("\n", [
            'company_name,email,phone_number',
            'ABC,invalid-email,9801234567',
            'XYZ,test2@example.com,9801234568',
        ]));

        $job = new ImportClientsJob($relativePath);

        $job->handle(
            app(CsvImportService::class),
            app(DuplicateService::class)
        );

        $this->assertEquals(1, Client::count());
        $this->assertEquals(1, ImportLog::count());

        unlink($fullPath);
    }

    public function test_job_handles_duplicate_against_existing_database_record()
    {
        $signature = app(DuplicateService::class)->generateSignature(
            'ABC',
            'test1@example.com',
            '9801234567'
        );

        Client::factory()->create([
            'company_name' => 'ABC',
            'email' => 'test1@example.com',
            'phone_number' => '9801234567',
            'signature' => $signature,
            'duplicate_group_id' => null,
        ]);

        $relativePath = 'test_existing_duplicate.csv';
        $fullPath = storage_path('app/' . $relativePath);

        file_put_contents($fullPath, implode("\n", [
            'company_name,email,phone_number',
            'ABC,test1@example.com,9801234567',
        ]));

        $job = new ImportClientsJob($relativePath);

        $job->handle(
            app(CsvImportService::class),
            app(DuplicateService::class)
        );

        $this->assertEquals(2, Client::count());
        $this->assertEquals(1, Client::whereNotNull('duplicate_group_id')->count());

        unlink($fullPath);
    }
}