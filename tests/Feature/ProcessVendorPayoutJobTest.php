<?php

namespace Tests\Feature;

use App\Jobs\ProcessVendorPayout;
use App\Models\ActivityLog;
use App\Models\Payout;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vendor;
use App\Services\ActivityLogger;
use App\Services\PayoutService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Covers Roadmap 3.2: ProcessVendorPayout wraps the same
 * PayoutService::process() call Admin\PayoutController used to make
 * synchronously. This proves extracting it into a job did not change the
 * actual payout outcome, and that the admin who queued it (captured at
 * dispatch time, since Auth::id() is unavailable in the queue worker) is
 * still the actor recorded on the resulting activity log entry.
 */
class ProcessVendorPayoutJobTest extends TestCase
{
    use DatabaseTransactions;

    public function test_handle_completes_a_stripe_payout_and_logs_the_captured_actor(): void
    {
        Setting::set('stripe_enabled', true);
        Setting::set('stripe_secret_key', 'sk_test_123');

        $admin = User::factory()->admin()->create();
        $vendor = Vendor::create([
            'user_id' => User::factory()->create()->id,
            'business_name' => 'Test Vendor '.uniqid(),
            'slug' => 'test-vendor-'.uniqid(),
            'business_email' => 'vendor'.uniqid().'@example.com',
            'business_phone' => '+15550000000',
            'business_address' => '1 Test Street',
            'status' => 'approved',
            'is_active' => true,
            'stripe_account_id' => 'acct_test123',
        ]);
        $payout = Payout::create([
            'vendor_id' => $vendor->id,
            'amount' => 150.00,
            'method' => 'stripe',
            'status' => 'processing',
        ]);

        Http::fake([
            'api.stripe.com/v1/transfers' => Http::response(['id' => 'tr_test_123'], 200),
        ]);

        $job = new ProcessVendorPayout($payout, $admin->id);
        $job->handle(app(PayoutService::class), app(ActivityLogger::class));

        $payout->refresh();
        $this->assertSame('completed', $payout->status);
        $this->assertSame('tr_test_123', $payout->provider_ref);

        $log = ActivityLog::where('action', 'payout.processed')->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertSame($admin->id, $log->actor_id);
        $this->assertSame($payout->id, $log->subject_id);
    }

    public function test_handle_skips_a_payout_that_already_reached_a_terminal_state(): void
    {
        $vendor = Vendor::create([
            'user_id' => User::factory()->create()->id,
            'business_name' => 'Test Vendor '.uniqid(),
            'slug' => 'test-vendor-'.uniqid(),
            'business_email' => 'vendor'.uniqid().'@example.com',
            'business_phone' => '+15550000000',
            'business_address' => '1 Test Street',
            'status' => 'approved',
            'is_active' => true,
        ]);
        $payout = Payout::create([
            'vendor_id' => $vendor->id,
            'amount' => 150.00,
            'method' => 'stripe',
            'status' => 'completed',
            'processed_at' => now(),
        ]);

        Http::fake();

        $job = new ProcessVendorPayout($payout);
        $job->handle(app(PayoutService::class), app(ActivityLogger::class));

        Http::assertNothingSent();
        $this->assertDatabaseCount('activity_logs', 0);
    }
}
