<?php

namespace App\Services;

use App\Models\Client;
use App\Models\DuplicateGroup;

class CsvImportService
{

    public function processFile($path, DuplicateDetectionService $duplicateService)
    {
        $handle = fopen($path, 'r');

        if (!$handle) {
            return;
        }

        $header = fgetcsv($handle); // skip header row

        while (($row = fgetcsv($handle)) !== false) {

            $company = $row[0] ?? null;
            $email = $row[1] ?? null;
            $phone = $row[2] ?? null;

            if (!$company || !$email || !$phone) {
                continue;
            }

            $signature = $duplicateService->generateSignature(
                $company,
                $email,
                $phone
            );

            $existing = Client::where('signature', $signature)->first();

            $duplicateGroupId = null;

            if ($existing) {

                $duplicateGroupId = $existing->duplicate_group_id;

                if (!$duplicateGroupId) {

                    $group = DuplicateGroup::create([
                        'signature' => $signature
                    ]);

                    $existing->update([
                        'duplicate_group_id' => $group->id
                    ]);

                    $duplicateGroupId = $group->id;
                }
            }

            Client::create([
                'company_name' => $company,
                'email' => $email,
                'phone_number' => $phone,
                'signature' => $signature,
                'duplicate_group_id' => $duplicateGroupId
            ]);
        }

        fclose($handle);
    }
}
