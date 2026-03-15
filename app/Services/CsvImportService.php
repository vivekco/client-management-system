<?php

namespace App\Services;

use App\Models\Client;
use App\Models\DuplicateGroup;
use App\Models\ImportLog;
use App\Models\ImportSummary;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
                'total_rows' => 0,
                'inserted' => 0,
                'skipped' => 0,
                'duplicates' => 0,
            ];
        }

        $fileName = basename($path);
        fgetcsv($handle); // skip header
        $rowNumber = 1;

        $stats = [
            'total_rows' => 0,
            'inserted' => 0,
            'skipped' => 0,
            'duplicates' => 0,
        ];

        $batch = [];
        $logs = [];

        /*
        $seenSignatures structure:
        [
            'signature' => [
                'seen_once' => true,
                'group_id' => null|int
            ]
        ]
        */
        $seenSignatures = [];

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            $stats['total_rows']++;

            $company = trim($row[0] ?? '');
            $email   = strtolower(trim($row[1] ?? ''));
            $phone   = preg_replace('/\D+/', '', $row[2] ?? '');

            $validator = Validator::make([
                'company_name' => $company,
                'email' => $email,
                'phone_number' => $phone,
            ], [
                'company_name' => 'required|string|max:255',
                'email' => 'required|email',
                'phone_number' => 'required|digits:10',
            ]);

            if ($validator->fails()) {
                $logs[] = [
                    'file_name' => $fileName,
                    'row_number' => $rowNumber,
                    'data' => [
                        'company_name' => $company,
                        'email' => $email,
                        'phone_number' => $phone,
                    ],
                    'errors' => $validator->errors()->all(),
                ];

                $stats['skipped']++;
                continue;
            }

            $signature = $duplicateService->generateSignature($company, $email, $phone);
            $duplicateGroupId = null;

            /*
             |------------------------------------------------------------
             | STEP 1: Check if already seen in the SAME CSV import
             |------------------------------------------------------------
             | First CSV occurrence stays unique.
             | Second and later become duplicate.
             */
            if (array_key_exists($signature, $seenSignatures)) {
                if (!empty($seenSignatures[$signature]['group_id'])) {
                    $duplicateGroupId = $seenSignatures[$signature]['group_id'];
                } else {
                    $group = DuplicateGroup::create([
                        'signature' => $signature,
                    ]);

                    $duplicateGroupId = $group->id;
                    $seenSignatures[$signature]['group_id'] = $duplicateGroupId;
                }

                $stats['duplicates']++;
            } else {
                /*
                 |--------------------------------------------------------
                 | STEP 2: Not yet seen in current CSV.
                 | Check database.
                 |--------------------------------------------------------
                 */

                // First check if any duplicate group already exists for this signature
                $existingGrouped = Client::where('signature', $signature)
                    ->whereNotNull('duplicate_group_id')
                    ->first();

                if ($existingGrouped) {
                    // Current row is duplicate of existing grouped records
                    $duplicateGroupId = $existingGrouped->duplicate_group_id;
                    $stats['duplicates']++;

                    $seenSignatures[$signature] = [
                        'seen_once' => true,
                        'group_id' => $duplicateGroupId,
                    ];
                } else {
                    // Check if an original unique record already exists in DB
                    $existingOriginal = Client::where('signature', $signature)
                        ->whereNull('duplicate_group_id')
                        ->first();

                    if ($existingOriginal) {
                        // Current row is the second occurrence overall
                        $group = DuplicateGroup::create([
                            'signature' => $signature,
                        ]);

                        $duplicateGroupId = $group->id;
                        $stats['duplicates']++;

                        $seenSignatures[$signature] = [
                            'seen_once' => true,
                            'group_id' => $duplicateGroupId,
                        ];
                    } else {
                        // Truly first occurrence ever
                        $seenSignatures[$signature] = [
                            'seen_once' => true,
                            'group_id' => null,
                        ];
                    }
                }
            }

            $batch[] = [
                'company_name' => $company,
                'email' => $email,
                'phone_number' => $phone,
                'signature' => $signature,
                'duplicate_group_id' => $duplicateGroupId,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= $this->batchSize) {
                $this->insertBatch($batch);
                $stats['inserted'] += count($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            $this->insertBatch($batch);
            $stats['inserted'] += count($batch);
        }

        fclose($handle);

        foreach ($logs as $log) {
            ImportLog::create($log);
        }

        ImportSummary::updateOrCreate(
            ['file_name' => $fileName],
            [
                'total_rows' => $stats['total_rows'],
                'inserted' => $stats['inserted'],
                'skipped' => $stats['skipped'],
                'duplicates' => $stats['duplicates'],
            ]
        );

        return array_merge(['status' => 'success'], $stats);
    }

    protected function insertBatch(array $batch)
    {
        DB::transaction(function () use ($batch) {
            Client::insert($batch);
        });
    }
}
