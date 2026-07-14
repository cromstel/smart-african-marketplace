<?php

namespace Tests\Feature\Api;

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AddressApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_authenticated_user_can_create_and_list_addresses(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $create = $this->postJson('/api/v1/addresses', [
            'type' => 'shipping',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'address_line1' => '123 Market St',
            'city' => 'Lagos',
            'country' => 'NG',
            'is_default' => true,
        ]);

        $create->assertStatus(201)
            ->assertJsonPath('data.first_name', 'Jane')
            ->assertJsonPath('data.is_default', true);

        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'address_line1' => '123 Market St',
        ]);

        $list = $this->getJson('/api/v1/addresses');
        $list->assertStatus(200)
            ->assertJsonStructure(['success', 'data', 'message'])
            ->assertJsonCount(1, 'data');
    }

    public function test_user_cannot_access_others_address(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $address = Address::create([
            'user_id' => $owner->id,
            'type' => 'shipping',
            'first_name' => 'Owner',
            'last_name' => 'Person',
            'address_line1' => '1 Owner St',
            'city' => 'Lagos',
            'country' => 'NG',
        ]);

        Sanctum::actingAs($other);

        $this->getJson("/api/v1/addresses/{$address->id}")->assertStatus(403);
        $this->deleteJson("/api/v1/addresses/{$address->id}")->assertStatus(403);
    }

    public function test_validation_requires_required_fields(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/addresses', ['type' => 'shipping'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['first_name', 'last_name', 'address_line1', 'city', 'country']);
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $this->getJson('/api/v1/addresses')->assertStatus(401);
    }
}
