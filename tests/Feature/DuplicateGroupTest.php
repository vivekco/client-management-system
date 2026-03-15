<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Client;
use App\Models\DuplicateGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DuplicateGroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_duplicate_groups()
    {
        DuplicateGroup::factory()->count(2)->create();

        $response = $this->getJson('/api/duplicate-groups');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'current_page',
                'data',
                'per_page',
                'total',
            ]);
    }

    public function test_it_shows_duplicate_group_clients()
    {
        $group = DuplicateGroup::factory()->create();

        Client::factory()->count(2)->create([
            'duplicate_group_id' => $group->id,
        ]);

        $response = $this->getJson('/api/duplicate-groups/' . $group->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'group',
                'clients',
            ]);

        $this->assertCount(2, $response->json('clients'));
    }

    public function test_it_returns_404_for_missing_duplicate_group()
    {
        $response = $this->getJson('/api/duplicate-groups/999999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Duplicate group not found.',
            ]);
    }
}