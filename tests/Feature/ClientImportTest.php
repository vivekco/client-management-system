<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Jobs\ImportClientsJob;
use Illuminate\Support\Facades\Queue;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClientImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_endpoint_dispatches_job()
    {
        Queue::fake();

        $csvContent = "company_name,email,phone_number\n";
        $csvContent .= "ABC,test1@example.com,9801234567\n";

        $file = UploadedFile::fake()->createWithContent('clients.csv', $csvContent);

        $response = $this->post('/api/clients/import', [
            'file' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'CSV import started',
            ]);

        Queue::assertPushed(ImportClientsJob::class);
    }
}