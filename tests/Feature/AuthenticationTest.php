<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    // The app reads settings/cache tables at bootstrap, so we run against the
    // (already migrated + seeded) database and roll back all writes per test.
    use DatabaseTransactions;

    private function makeUser(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'email' => 'auth_test_'.uniqid().'@example.com',
            'password' => 'Password123!',
            'is_active' => true,
        ], $attrs));
    }

    public function test_login_page_renders(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('Smart African Marketplace');
    }

    public function test_guests_can_authenticate(): void
    {
        $user = $this->makeUser();

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'Password123!',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = $this->makeUser();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_forgot_password_redirects_to_login_modal(): void
    {
        $response = $this->get(route('password.request'));

        $response->assertRedirect(route('login', ['modal' => 'forgot-password']));
    }

    public function test_register_page_renders(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('Smart African Marketplace');
    }

    public function test_admin_login_page_renders(): void
    {
        $response = $this->get(route('admin.login'));

        $response->assertStatus(200);
        $response->assertSee('Smart African Marketplace');
    }

    public function test_password_reset_link_can_be_requested(): void
    {
        Notification::fake();

        $user = $this->makeUser();

        $this->post(route('password.email'), ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = $this->makeUser();

        $this->post(route('password.email'), ['email' => $user->email]);

        Notification::assertSentTo(
            $user,
            ResetPassword::class,
            function (ResetPassword $notification) use ($user) {
                $response = $this->get(route('password.reset', $notification->token));
                $response->assertStatus(200);
                $response->assertSee('name="password"', false);

                $this->post(route('password.store'), [
                    'token' => $notification->token,
                    'email' => $user->email,
                    'password' => 'NewPassword123!',
                    'password_confirmation' => 'NewPassword123!',
                ]);

                $this->assertTrue(Hash::check('NewPassword123!', $user->fresh()->password));
                $this->assertGuest();

                return true;
            }
        );
    }
}
