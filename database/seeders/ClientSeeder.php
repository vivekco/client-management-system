<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\DuplicateGroup;
use App\Services\DuplicateService;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $duplicateService = app(DuplicateService::class);

        // Unique clients
        $uniqueClients = [
            [
                'company_name' => 'ABC Pvt Ltd',
                'email' => 'abc@example.com',
                'phone_number' => '9801234567',
            ],
            [
                'company_name' => 'XYZ Traders',
                'email' => 'xyz@example.com',
                'phone_number' => '9801234568',
            ],
            [
                'company_name' => 'LMN Corp',
                'email' => 'lmn@example.com',
                'phone_number' => '9801234569',
            ],
            [
                'company_name' => 'Tech Solutions',
                'email' => 'tech@example.com',
                'phone_number' => '9801234570',
            ],
            [
                'company_name' => 'Global Ventures',
                'email' => 'global@example.com',
                'phone_number' => '9801234571',
            ],
        ];

        foreach ($uniqueClients as $client) {
            Client::create([
                'company_name' => $client['company_name'],
                'email' => $client['email'],
                'phone_number' => $client['phone_number'],
                'signature' => $duplicateService->generateSignature(
                    $client['company_name'],
                    $client['email'],
                    $client['phone_number']
                ),
                'duplicate_group_id' => null,
            ]);
        }

        // Duplicate group 1
        $group1Signature = $duplicateService->generateSignature(
            'Nepa Supplies',
            'sales@nepa.com',
            '9801234572'
        );

        $group1 = DuplicateGroup::create([
            'signature' => $group1Signature,
        ]);

        Client::create([
            'company_name' => 'Nepa Supplies',
            'email' => 'sales@nepa.com',
            'phone_number' => '9801234572',
            'signature' => $group1Signature,
            'duplicate_group_id' => $group1->id,
        ]);

        Client::create([
            'company_name' => 'Nepa Supplies',
            'email' => 'sales@nepa.com',
            'phone_number' => '9801234572',
            'signature' => $group1Signature,
            'duplicate_group_id' => $group1->id,
        ]);

        // Duplicate group 2
        $group2Signature = $duplicateService->generateSignature(
            'Summit Enterprises',
            'info@summit.com',
            '9801234573'
        );

        $group2 = DuplicateGroup::create([
            'signature' => $group2Signature,
        ]);

        Client::create([
            'company_name' => 'Summit Enterprises',
            'email' => 'info@summit.com',
            'phone_number' => '9801234573',
            'signature' => $group2Signature,
            'duplicate_group_id' => $group2->id,
        ]);

        Client::create([
            'company_name' => 'Summit Enterprises',
            'email' => 'info@summit.com',
            'phone_number' => '9801234573',
            'signature' => $group2Signature,
            'duplicate_group_id' => $group2->id,
        ]);

        Client::create([
            'company_name' => 'Summit Enterprises',
            'email' => 'info@summit.com',
            'phone_number' => '9801234573',
            'signature' => $group2Signature,
            'duplicate_group_id' => $group2->id,
        ]);
    }
}