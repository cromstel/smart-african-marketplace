<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VendorCategory;
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

    public function test_admin_can_login(): void
    {
        $admin = User::factory()->admin()->create([
            'password' => 'Password123!',
            'is_active' => true,
        ]);

        $response = $this->post(route('admin.login'), [
            'email' => $admin->email,
            'password' => 'Password123!',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_non_admin_is_rejected_from_admin_login(): void
    {
        $user = $this->makeUser();

        $response = $this->post(route('admin.login'), [
            'email' => $user->email,
            'password' => 'Password123!',
        ]);

        $this->assertGuest();
        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
    }

    public function test_customer_can_register(): void
    {
        $email = 'reg_'.uniqid().'@example.com';

        $response = $this->post(route('register'), [
            'firstname' => 'Jane',
            'lastname' => 'Doe',
            'email' => $email,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => $email, 'role' => 'customer']);
        $response->assertRedirect();
    }

    public function test_vendor_can_register(): void
    {
        $category = VendorCategory::first();
        $email = 'vendor_'.uniqid().'@example.com';

        $response = $this->post(route('register'), [
            'firstname' => 'Jane',
            'lastname' => 'Doe',
            'email' => $email,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'register_as_vendor' => '1',
            'business_name' => 'Test Business',
            'business_email' => 'biz@example.com',
            'business_phone' => '1234567890',
            'business_address' => '123 Market St',
            'vendor_category_id' => $category->id,
        ]);

        $this->assertAuthenticated();

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->isVendor());
        $this->assertDatabaseHas('vendors', ['user_id' => $user->id]);
        $response->assertRedirect();
    }
}
