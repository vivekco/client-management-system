<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Client;
use App\Models\DuplicateGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClientApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_all_clients()
    {
        $group = DuplicateGroup::factory()->create();

        Client::factory()->create([
            'company_name' => 'ABC Pvt Ltd',
            'email' => 'abc@example.com',
            'phone_number' => '9801234567',
            'signature' => 'sig-1',
            'duplicate_group_id' => null,
        ]);

        Client::factory()->create([
            'company_name' => 'XYZ Traders',
            'email' => 'xyz@example.com',
            'phone_number' => '9801234568',
            'signature' => 'sig-2',
            'duplicate_group_id' => $group->id,
        ]);

        Client::factory()->create([
            'company_name' => 'LMN Corp',
            'email' => 'lmn@example.com',
            'phone_number' => '9801234569',
            'signature' => 'sig-3',
            'duplicate_group_id' => $group->id,
        ]);

        $response = $this->getJson('/api/clients');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'current_page',
                'data',
                'per_page',
                'total',
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_it_returns_only_unique_clients()
    {
        $group = DuplicateGroup::factory()->create();

        Client::factory()->create([
            'signature' => 'sig-1',
            'duplicate_group_id' => null,
        ]);

        Client::factory()->create([
            'signature' => 'sig-2',
            'duplicate_group_id' => $group->id,
        ]);

        Client::factory()->create([
            'signature' => 'sig-3',
            'duplicate_group_id' => $group->id,
        ]);

        $response = $this->getJson('/api/clients?unique=1');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_it_returns_only_duplicate_clients()
    {
        $group = DuplicateGroup::factory()->create();

        Client::factory()->create([
            'signature' => 'sig-1',
            'duplicate_group_id' => null,
        ]);

        Client::factory()->create([
            'signature' => 'sig-2',
            'duplicate_group_id' => $group->id,
        ]);

        Client::factory()->create([
            'signature' => 'sig-3',
            'duplicate_group_id' => $group->id,
        ]);

        $response = $this->getJson('/api/clients?duplicates=1');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_it_rejects_conflicting_filters()
    {
        $response = $this->getJson('/api/clients?unique=1&duplicates=1');

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'You cannot request both duplicates and unique at the same time.',
            ]);
    }

    public function test_it_exports_all_clients()
    {
        Client::factory()->count(3)->create();

        $response = $this->get('/api/clients/export');

        $response->assertStatus(200);
    }

    public function test_it_exports_only_unique_clients()
    {
        $group = DuplicateGroup::factory()->create();

        Client::factory()->create([
            'duplicate_group_id' => null,
        ]);

        Client::factory()->create([
            'duplicate_group_id' => $group->id,
        ]);

        $response = $this->get('/api/clients/export?unique=1');

        $response->assertStatus(200);
    }

    public function test_it_exports_only_duplicate_clients()
    {
        $group = DuplicateGroup::factory()->create();

        Client::factory()->create([
            'duplicate_group_id' => null,
        ]);

        Client::factory()->create([
            'duplicate_group_id' => $group->id,
        ]);

        Client::factory()->create([
            'duplicate_group_id' => $group->id,
        ]);

        $response = $this->get('/api/clients/export?duplicates=1');

        $response->assertStatus(200);
    }
}