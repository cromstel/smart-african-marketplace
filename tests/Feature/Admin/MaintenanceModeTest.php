<?php

namespace Tests\Feature\Admin;

use App\Models\Setting;
use App\Models\User;
use App\Services\MaintenanceService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * MaintenanceService previously persisted nothing: the boolean "enabled"
 * flag lived in a 5-minute Cache::put() with no underlying durable value to
 * fall back to (so it silently turned itself back off), and every other
 * setting (title, message, retry, allowed_ips, allowed_emails) was written
 * with the Config facade -- process-local, gone the instant the request or
 * CLI command that set it finished. The enforcing middleware also read
 * config('maintenance.*') directly for message/expected/retry/allowed_ips,
 * so even a config value that did somehow persist would never have reached
 * a real visitor. These tests exercise the fix: persistence through the
 * Setting model, with no TTL and no request-local mutation, consumed
 * consistently by the admin controller and the middleware alike.
 */
class MaintenanceModeTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // DatabaseTransactions rolls back Setting rows between tests, but
        // not the array cache driver (per-process, outlives any one test),
        // so a maintenance_mode value cached true by an earlier test would
        // otherwise leak into this one even after its underlying Setting
        // row is gone.
        Cache::forget('maintenance_mode');
    }

    public function test_enabling_maintenance_mode_persists_past_the_cache_ttl(): void
    {
        app(MaintenanceService::class)->enable();

        // Simulate the 5-minute cache window lapsing -- this used to be
        // exactly the moment maintenance mode silently turned back off,
        // because Config::get() fell back to the static file default.
        Cache::forget('maintenance_mode');

        $this->assertTrue(
            app(MaintenanceService::class)->isActive(),
            'Maintenance mode must stay active after its cache entry expires, not silently revert.'
        );
        $this->assertSame('1', Setting::where('key', 'maintenance_enabled')->value('value'));
    }

    public function test_admin_updating_the_maintenance_config_persists_across_requests(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.maintenance.update'), [
            'title' => 'Back in a bit',
            'message' => 'Upgrading the database, hang tight.',
            'retry' => 900,
            'allowed_ips' => "10.0.0.1\n10.0.0.2",
            'allowed_emails' => 'ops@example.com',
        ])->assertRedirect(route('admin.maintenance.index'));

        // A fresh service instance simulates a brand new request reading
        // this back -- the whole point of the fix.
        $config = app(MaintenanceService::class)->getConfig();

        $this->assertSame('Back in a bit', $config['title']);
        $this->assertSame('Upgrading the database, hang tight.', $config['message']);
        $this->assertSame(900, $config['retry']);
        $this->assertSame(['10.0.0.1', '10.0.0.2'], $config['allowed_ips']);
        $this->assertSame(['ops@example.com'], $config['allowed_emails']);

        // Round-trip through the actual admin page too.
        $this->actingAs($admin)->get(route('admin.maintenance.index'))
            ->assertOk()
            ->assertSee('Back in a bit', false)
            ->assertSee('Upgrading the database, hang tight.', false);
    }

    public function test_a_real_visitor_sees_the_persisted_custom_message_when_maintenance_is_active(): void
    {
        app(MaintenanceService::class)->updateConfig([
            'title' => 'Down for maintenance',
            'message' => 'We are performing scheduled maintenance.',
            // config/maintenance.php defaults allowed_ips to 127.0.0.1,::1
            // so a developer isn't locked out of their own local instance --
            // and the test HTTP client's simulated request also defaults to
            // 127.0.0.1, so this test must pick a non-allowlisted IP to
            // actually exercise "an ordinary visitor is blocked", same as
            // test_an_allowed_ip_bypasses_maintenance_mode does deliberately
            // in the other direction.
            'allowed_ips' => [],
        ]);
        app(MaintenanceService::class)->enable();

        $response = $this->get('/', ['REMOTE_ADDR' => '198.51.100.9']);

        $response->assertStatus(503);
        $response->assertSee('Down for maintenance', false);
        $response->assertSee('We are performing scheduled maintenance.', false);
    }

    public function test_an_allowed_ip_bypasses_maintenance_mode(): void
    {
        app(MaintenanceService::class)->updateConfig(['allowed_ips' => ['203.0.113.5']]);
        app(MaintenanceService::class)->enable();

        $this->get('/', ['REMOTE_ADDR' => '203.0.113.5'])->assertStatus(200);
        $this->get('/', ['REMOTE_ADDR' => '198.51.100.9'])->assertStatus(503);
    }

    public function test_previewing_the_maintenance_page_does_not_enable_it_for_real_visitors(): void
    {
        $admin = User::factory()->admin()->create();
        $this->assertFalse(app(MaintenanceService::class)->isActive());

        $this->actingAs($admin)->get(route('admin.maintenance.preview'))->assertOk();

        $this->assertFalse(
            app(MaintenanceService::class)->isActive(),
            'Previewing the maintenance page must not be a destructive action.'
        );
        $this->get('/')->assertStatus(200);
    }
}
