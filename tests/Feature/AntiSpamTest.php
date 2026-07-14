<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Verifies the `antispam` middleware (honeypot + minimum fill-time) blocks
 * automated submissions on the public auth forms without affecting real users.
 *
 * Uses the isolated `african_market_test` DB clone (see phpunit.xml); writes
 * are wrapped in a transaction and rolled back.
 */
class AntiSpamTest extends TestCase
{
    use DatabaseTransactions;

    public function test_login_page_renders_honeypot_fields(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('name="website_url"', false);
        $response->assertSee('name="form_loaded_at"', false);
    }

    public function test_legitimate_login_is_not_flagged(): void
    {
        $user = User::factory()->create([
            'email' => 'antispam_'.uniqid().'@example.com',
            'password' => 'changeme',
            'is_active' => true,
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'changeme',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect();
    }

    public function test_login_with_filled_honeypot_is_blocked(): void
    {
        $user = User::factory()->create([
            'email' => 'antispam_'.uniqid().'@example.com',
            'password' => 'changeme',
            'is_active' => true,
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'changeme',
            'website_url' => 'http://spam.example',
        ]);

        $this->assertGuest();
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_login_submitted_too_fast_is_blocked(): void
    {
        $user = User::factory()->create([
            'email' => 'antispam_'.uniqid().'@example.com',
            'password' => 'changeme',
            'is_active' => true,
        ]);

        // form_loaded_at == now => elapsed 0s < minimum fill time.
        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'changeme',
            'form_loaded_at' => time(),
        ]);

        $this->assertGuest();
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_register_with_filled_honeypot_is_blocked(): void
    {
        $email = 'antispam_reg_'.uniqid().'@example.com';

        $response = $this->post(route('register'), [
            'firstname' => 'Jane',
            'lastname' => 'Doe',
            'email' => $email,
            'password' => 'changeme',
            'password_confirmation' => 'changeme',
            'website_url' => 'http://spam.example',
        ]);

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => $email]);
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_forgot_password_with_filled_honeypot_is_blocked(): void
    {
        $user = User::factory()->create([
            'email' => 'antispam_'.uniqid().'@example.com',
            'password' => 'changeme',
            'is_active' => true,
        ]);

        $response = $this->post(route('password.email'), [
            'email' => $user->email,
            'website_url' => 'http://spam.example',
        ]);

        $this->assertGuest();
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}
