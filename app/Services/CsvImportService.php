<?php

namespace App\Services;

use App\Models\Client;
use App\Models\DuplicateGroup;
use App\Models\ImportLog;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CsvImportService
{
    protected $batchSize = 500;

    public function processFile($path, $duplicateService)
    {
        $handle = fopen($path, 'r');
        if (!$handle) {
            return [
                'status' => 'error',
                'message' => 'Unable to open file',
                'processed' => 0,
                'inserted' => 0,
                'skipped' => 0,
                'duplicates' => 0
            ];
        }

        $fileName = basename($path);
        $header = fgetcsv($handle); // skip header
        $rowNumber = 1;

        $stats = [
            'processed' => 0,
            'inserted' => 0,
            'skipped' => 0,
            'duplicates' => 0
        ];

        $batch = [];
        $logs = [];

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            $stats['processed']++;

            $company = $row[0] ?? null;
            $email = $row[1] ?? null;
            $phone = $row[2] ?? null;

            // Validate row
            $validator = Validator::make([
                'company_name' => $company,
                'email' => $email,
                'phone_number' => $phone
            ], [
                'company_name' => 'required|string|max:255',
                'email' => 'required|email',
                'phone_number' => 'required|digits:10'
            ]);

            if ($validator->fails()) {
                $logs[] = [
                    'file_name' => $fileName,
                    'row_number' => $rowNumber,
                    'data' => [
                        'company_name' => $company,
                        'email' => $email,
                        'phone_number' => $phone
                    ],
                    'errors' => $validator->errors()->all()
                ];
                $stats['skipped']++;
                continue;
            }

            // Duplicate detection
            $signature = $duplicateService->generateSignature($company, $email, $phone);
            $existing = Client::where('signature', $signature)->first();

            $duplicateGroupId = null;

            if ($existing) {
                $duplicateGroupId = $existing->duplicate_group_id;

                if (!$duplicateGroupId) {
                    $group = DuplicateGroup::create(['signature' => $signature]);
                    $existing->update(['duplicate_group_id' => $group->id]);
                    $duplicateGroupId = $group->id;
                }
                $stats['duplicates']++;
            }

            // Prepare row for batch insert
            $batch[] = [
                'company_name' => $company,
                'email' => $email,
                'phone_number' => $phone,
                'signature' => $signature,
                'duplicate_group_id' => $duplicateGroupId,
                'created_at' => now(),
                'updated_at' => now()
            ];

            if (count($batch) >= $this->batchSize) {
                $this->insertBatch($batch);
                $stats['inserted'] += count($batch);
                $batch = [];
            }
        }

        // Insert remaining batch
        if (!empty($batch)) {
            $this->insertBatch($batch);
            $stats['inserted'] += count($batch);
        }

        fclose($handle);

        // Save import logs
        foreach ($logs as $log) {
            ImportLog::create($log);
        }

        return array_merge(['status' => 'success'], $stats);
    }

    protected function insertBatch(array $batch)
    {
        DB::transaction(function () use ($batch) {
            Client::insert($batch);
        });
    }
}
