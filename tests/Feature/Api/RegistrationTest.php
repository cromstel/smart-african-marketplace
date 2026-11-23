<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_registration_returns_token_and_user(): void
    {
        $response = $this->postJson('/api/v1/mobile/auth/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'device_name' => 'test-device',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'token',
                'token_type',
                'user' => ['id', 'name', 'email', 'role'],
            ])
            ->assertJsonPath('user.email', 'jane@example.com')
            ->assertJsonPath('user.role', 'customer')
            ->assertJsonPath('token_type', 'Bearer');

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'role' => 'customer',
            'is_active' => true,
        ]);
    }

    public function test_registration_requires_unique_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->postJson('/api/v1/mobile/auth/register', [
            'name' => 'Dup User',
            'email' => 'taken@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_registration_validates_password_confirmation(): void
    {
        $response = $this->postJson('/api/v1/mobile/auth/register', [
            'name' => 'No Match',
            'email' => 'nomatch@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_login_throttled_after_too_many_attempts(): void
    {
        $last = null;
        for ($i = 0; $i < 11; $i++) {
            $last = $this->postJson('/api/v1/mobile/auth/login', [
                'email' => 'nobody@example.com',
                'password' => 'wrong-password',
            ]);
        }

        $this->assertEquals(429, $last->status());
    }
}
