<?php

namespace Tests\Feature;

use App\Jobs\OptimizeMediaLibraryItem;
use App\Jobs\ProcessVendorPayout;
use App\Jobs\ReindexChatbotKnowledgeJob;
use App\Jobs\SendAbandonedCartRecoveryEmail;
use App\Jobs\SendWelcomeSeriesEmail;
use App\Jobs\SendWinBackCampaignEmail;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Cart;
use App\Models\Category;
use App\Models\MediaLibrary;
use App\Models\Newsletter;
use App\Models\Order;
use App\Models\Payout;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Covers Roadmap 3.2: campaign sends, media optimization, chatbot
 * reindexing, and payout processing all used to run their real work inline
 * -- inside a scheduled command's own process, or (for payouts) inside an
 * admin's HTTP request. These tests assert the dispatch side: that the
 * right job goes on the right queue with the right data instead of the work
 * happening synchronously. Each job's own delivery behavior (sending the
 * email, calling the gateway, etc.) is covered separately -- see
 * ProcessVendorPayoutJobTest for the payout job's real end-to-end behavior.
 */
class BackgroundJobsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_abandoned_cart_command_queues_a_job_only_for_the_eligible_cart(): void
    {
        Bus::fake();

        $eligibleUser = User::factory()->create();
        $product = $this->sellableProduct();

        $eligibleCart = Cart::create([
            'user_id' => $eligibleUser->id,
            'subtotal' => 25, 'tax' => 0, 'shipping' => 0, 'discount' => 0, 'total' => 25,
        ]);
        $eligibleCart->items()->create(['product_id' => $product->id, 'quantity' => 1, 'price' => 25, 'subtotal' => 25]);
        $this->backdate($eligibleCart, now()->subHours(3));

        // Updated too recently to be "abandoned" yet -- excluded by the
        // command's own where('updated_at', '<=', ...) filter, so it never
        // reaches the per-cart eligibility checks at all.
        $tooRecentUser = User::factory()->create();
        $tooRecentCart = Cart::create([
            'user_id' => $tooRecentUser->id,
            'subtotal' => 25, 'tax' => 0, 'shipping' => 0, 'discount' => 0, 'total' => 25,
        ]);
        $tooRecentCart->items()->create(['product_id' => $product->id, 'quantity' => 1, 'price' => 25, 'subtotal' => 25]);

        $this->artisan('campaigns:send-abandoned-cart', ['--hours' => 2])->assertSuccessful();

        Bus::assertDispatched(SendAbandonedCartRecoveryEmail::class, fn ($job) => $job->cart->is($eligibleCart));
        Bus::assertDispatchedTimes(SendAbandonedCartRecoveryEmail::class, 1);
    }

    public function test_welcome_series_command_queues_the_correct_step_and_skips_already_sent_steps(): void
    {
        Bus::fake();

        $stepOneSubscriber = Newsletter::create([
            'email' => 'step1-'.uniqid().'@example.com',
            'status' => 'subscribed',
        ]);
        $this->backdate($stepOneSubscriber, now());

        $alreadySentSubscriber = Newsletter::create([
            'email' => 'already-'.uniqid().'@example.com',
            'status' => 'subscribed',
        ]);
        $this->backdate($alreadySentSubscriber, now());
        DB::table('newsletter_welcome_email_logs')->insert([
            'newsletter_id' => $alreadySentSubscriber->id,
            'email' => $alreadySentSubscriber->email,
            'series_step' => 1,
            'sent_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan('campaigns:send-welcome-series')->assertSuccessful();

        Bus::assertDispatched(SendWelcomeSeriesEmail::class, fn ($job) => $job->subscriber->is($stepOneSubscriber) && $job->step === 1);
        Bus::assertNotDispatched(SendWelcomeSeriesEmail::class, fn ($job) => $job->subscriber->is($alreadySentSubscriber));
    }

    public function test_winback_command_creates_recipients_and_queues_a_job_per_recipient(): void
    {
        Bus::fake();

        $user = User::factory()->create();
        $subscriber = Newsletter::create([
            'email' => $user->email,
            'status' => 'subscribed',
            'user_id' => $user->id,
        ]);

        Order::create([
            'order_number' => 'ORD-'.uniqid(),
            'user_id' => $user->id,
            'status' => 'delivered',
            'payment_status' => 'paid',
            'subtotal' => 25, 'tax' => 0, 'shipping_cost' => 0, 'discount' => 0, 'total' => 25,
        ]);
        DB::table('orders')->where('user_id', $user->id)->update(['created_at' => now()->subDays(90)]);

        $this->artisan('campaigns:send-winback', ['--days' => 60])->assertSuccessful();

        $campaign = Campaign::where('campaign_type', 'win_back')->first();
        $this->assertNotNull($campaign);
        $this->assertSame('sent', $campaign->status);

        $recipient = CampaignRecipient::where('campaign_id', $campaign->id)->where('email', $user->email)->first();
        $this->assertNotNull($recipient);
        $this->assertSame('pending', $recipient->status);

        Bus::assertDispatched(SendWinBackCampaignEmail::class, fn ($job) => $job->recipient->is($recipient));
    }

    public function test_media_optimize_command_queues_one_job_per_pending_image(): void
    {
        Bus::fake();

        $pending = MediaLibrary::create([
            'file_name' => 'pending.jpg',
            'original_name' => 'pending.jpg',
            'file_path' => 'media/pending.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 1000,
            'type' => 'image',
            'is_optimized' => false,
        ]);

        MediaLibrary::create([
            'file_name' => 'done.jpg',
            'original_name' => 'done.jpg',
            'file_path' => 'media/done.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 1000,
            'type' => 'image',
            'is_optimized' => true,
        ]);

        $this->artisan('media:optimize-pending')->assertSuccessful();

        Bus::assertDispatched(OptimizeMediaLibraryItem::class, fn ($job) => $job->media->is($pending));
        Bus::assertDispatchedTimes(OptimizeMediaLibraryItem::class, 1);
    }

    public function test_chatbot_reindex_command_queues_the_job(): void
    {
        Bus::fake();

        $this->artisan('chatbot:reindex-knowledge')->assertSuccessful();

        Bus::assertDispatched(ReindexChatbotKnowledgeJob::class, 1);
    }

    public function test_admin_processing_a_payout_flips_status_immediately_and_queues_the_job(): void
    {
        Bus::fake();

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
        ]);
        $payout = Payout::create([
            'vendor_id' => $vendor->id,
            'amount' => 100,
            'method' => 'stripe',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.payouts.process', $payout));

        $response->assertRedirect();
        $this->assertSame('processing', $payout->fresh()->status);

        Bus::assertDispatched(ProcessVendorPayout::class, function ($job) use ($payout, $admin) {
            return $job->payout->is($payout) && $job->actorId === $admin->id;
        });
    }

    private function sellableProduct(): Product
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

        $category = Category::create([
            'name' => 'Test Category '.uniqid(),
            'slug' => 'test-category-'.uniqid(),
        ]);

        return Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Test Product '.uniqid(),
            'slug' => 'test-product-'.uniqid(),
            'sku' => 'SKU-'.strtoupper(uniqid()),
            'description' => 'A product created for background job tests.',
            'price' => 25.00,
            'stock_quantity' => 100,
            'stock_status' => 'in_stock',
            'is_active' => true,
        ]);
    }

    /**
     * Backdates a model's updated_at (and created_at, where the caller
     * cares about age-based logic) around Eloquent's own timestamp
     * auto-management, which would otherwise overwrite any value passed at
     * create() time with the real "now".
     */
    private function backdate($model, $at): void
    {
        $model->timestamps = false;
        $model->forceFill(['created_at' => $at, 'updated_at' => $at])->save();
        $model->timestamps = true;
    }
}
