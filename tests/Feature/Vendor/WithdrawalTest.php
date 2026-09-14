<?php

namespace Tests\Feature\Vendor;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payout;
use App\Models\Product;
use App\Models\Setting;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Models\Vendor;
use App\Models\VendorBalance;
use App\Models\Withdrawal;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Vendor withdrawals — money leaving the platform.
 *
 * The regression these exist for: available balance was computed with
 * `auth()->id()` used as `products.vendor_id`. That column references
 * `vendors.id`, not `users.id`, and the two diverge for every vendor in
 * practice — in the seeded data all 146 vendors have `id != user_id`. So a
 * vendor's balance was calculated from a different vendor's sales, and they
 * could request a withdrawal against earnings that were not theirs.
 *
 * The same variable was used correctly two lines later for
 * `withdrawals.user_id`, which genuinely is a user id. That is why it survived
 * review, and why the first test here pins the attribution explicitly.
 *
 * available_balance itself was reworked in a later fix: it used to be
 * computed live from raw OrderItem sums, entirely independent of
 * VendorBalance/VendorLedger -- the same balance Payout draws from -- which
 * meant a vendor could withdraw against the OrderItem total and separately
 * request a Payout against the ledger total for the very same sales.
 * available_balance now reads VendorBalance.available, credited by
 * VendorLedgerService only once an order reaches status=delivered. The
 * fixtures below push orders through the real pending -> paid -> delivered
 * transitions (rather than inserting them pre-paid) so the ledger is
 * genuinely credited, the same way OrderObserver credits it in production.
 * Vendor commission_rate is pinned to 10% on every fixture vendor so the
 * ledger-based numbers match this file's pre-existing expected values
 * (net_earnings is still computed separately, from the platform-wide
 * `platform_commission_rate` setting, for the informational dashboard
 * breakdown -- see WithdrawalController::getVendorStats()).
 */
class WithdrawalTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::updateOrCreate(['key' => 'platform_commission_rate'], ['value' => 10]);
        Setting::updateOrCreate(['key' => 'minimum_withdrawal_amount'], ['value' => 50]);
        Setting::updateOrCreate(['key' => 'withdrawal_fee_percentage'], ['value' => 2]);
    }

    public function test_earnings_are_attributed_to_the_vendor_not_a_user_with_the_same_id(): void
    {
        // Constructed so the two readings give different answers.
        //
        // The earner owns vendor V and has 1000 of paid sales -> 900 after
        // commission. A decoy vendor is then created with vendor id equal to
        // the earner's *user* id, holding 500 of sales -> 450.
        //
        // Reading products.vendor_id with auth()->id() (the original bug) finds
        // the decoy and reports 450. Reading it with the user's actual vendor id
        // reports 900. Asserting both directions pins which one is in use.
        [$earner, $earnerVendor] = $this->vendorWithPaidSales(1000.00);

        $this->assertNotSame(
            $earner->id,
            $earnerVendor->id,
            'Fixture is not exercising the bug: user id and vendor id coincide.'
        );

        $this->makeDecoyVendorWithId($earner->id, 500.00);

        $response = $this->actingAs($earner)->get(route('vendor.withdrawals.index'));
        $response->assertOk();

        $body = $response->getContent();

        $this->assertStringContainsString(
            '900',
            $body,
            "The vendor's own earnings (900) are not shown."
        );
        $this->assertStringNotContainsString(
            '450',
            $body,
            'The balance was computed from another vendor\'s sales (450). '
                .'products.vendor_id is being read with a user id.'
        );
    }

    public function test_a_vendor_cannot_withdraw_more_than_their_available_balance(): void
    {
        [$user] = $this->vendorWithPaidSales(200.00);   // 180 after commission

        $this->actingAs($user)
            ->post(route('vendor.withdrawals.store'), $this->form(5000))
            ->assertRedirect();

        $this->assertSame(0, Withdrawal::where('user_id', $user->id)->count());
    }

    public function test_a_vendor_cannot_open_a_second_pending_withdrawal(): void
    {
        [$user] = $this->vendorWithPaidSales(1000.00);

        $this->actingAs($user)->post(route('vendor.withdrawals.store'), $this->form(100));
        $this->actingAs($user)->post(route('vendor.withdrawals.store'), $this->form(100));

        $this->assertSame(
            1,
            Withdrawal::where('user_id', $user->id)->count(),
            'A second pending withdrawal was accepted against the same balance.'
        );
    }

    public function test_the_minimum_withdrawal_amount_is_enforced(): void
    {
        [$user] = $this->vendorWithPaidSales(1000.00);

        $this->actingAs($user)
            ->post(route('vendor.withdrawals.store'), $this->form(10))
            ->assertSessionHasErrors('amount');

        $this->assertSame(0, Withdrawal::where('user_id', $user->id)->count());
    }

    public function test_a_vendor_cannot_view_another_vendors_withdrawal(): void
    {
        [$mine] = $this->vendorWithPaidSales(1000.00);
        [$theirs] = $this->vendorWithPaidSales(1000.00);

        $theirWithdrawal = Withdrawal::create([
            'user_id' => $theirs->id,
            'amount' => 100, 'fee' => 2, 'net_amount' => 98,
            'method' => 'bank_transfer', 'status' => 'pending',
            'account_details' => ['account_name' => 'X', 'account_number' => '1', 'bank_name' => 'Y'],
        ]);

        $this->actingAs($mine)
            ->get(route('vendor.withdrawals.show', $theirWithdrawal))
            ->assertNotFound();
    }

    public function test_a_rejected_withdrawal_refunds_the_reserved_amount(): void
    {
        [$user, $vendor] = $this->vendorWithPaidSales(1000.00); // 900 available

        $this->actingAs($user)->post(route('vendor.withdrawals.store'), $this->form(300));
        $withdrawal = Withdrawal::where('user_id', $user->id)->firstOrFail();

        $this->assertSame('600.00', VendorBalance::where('vendor_id', $vendor->id)->value('available'));

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->post(route('admin.withdrawals.reject', $withdrawal), [
            'rejection_reason' => 'Bank details could not be verified.',
        ]);

        $this->assertSame('900.00', VendorBalance::where('vendor_id', $vendor->id)->value('available'));
        $this->assertSame('rejected', $withdrawal->fresh()->status);
    }

    public function test_a_withdrawal_and_a_payout_cannot_together_overdraw_the_same_balance(): void
    {
        [$user, $vendor] = $this->vendorWithPaidSales(1000.00); // 900 available

        // Give the vendor what Payout requires: approved verification and a
        // bank detail set, so both requests are judged on balance alone.
        $vendor->verification()->create(['status' => 'approved']);
        $vendor->update(['bank_account_number' => '0000000000', 'bank_name' => 'Test Bank']);

        $withdrawalResponse = $this->actingAs($user)
            ->post(route('vendor.withdrawals.store'), $this->form(700));
        $payoutResponse = $this->actingAs($user)
            ->post(route('vendor.payouts.store'), [
                'amount' => 700,
                'method' => 'bank',
            ]);

        $withdrawalResponse->assertRedirect();
        $payoutResponse->assertRedirect();

        $totalReserved = Withdrawal::where('user_id', $user->id)->where('status', 'pending')->sum('amount')
            + Payout::where('vendor_id', $vendor->id)->where('status', 'pending')->sum('amount');

        $this->assertLessThanOrEqual(
            900,
            $totalReserved,
            "A withdrawal and a payout together reserved {$totalReserved} against a 900 balance."
        );
    }

    // --------------------------------------------------------------- Helpers

    /**
     * @return array<string, mixed>
     */
    private function form(float $amount): array
    {
        return [
            'amount' => $amount,
            'method' => 'bank_transfer',
            'account_name' => 'Test Account',
            'account_number' => '00000000',
            'bank_name' => 'Test Bank',
        ];
    }

    /**
     * Creates a vendor whose primary key is forced to $vendorId, owned by an
     * unrelated user, with the given value of paid sales. Used to make the
     * "user id read as vendor id" mistake produce a distinguishable number.
     */
    private function makeDecoyVendorWithId(int $vendorId, float $grossSales): void
    {
        if (Vendor::whereKey($vendorId)->exists()) {
            $this->markTestSkipped("A vendor already occupies id {$vendorId}; cannot build the decoy.");
        }

        $owner = User::factory()->vendor()->create();

        $decoy = new Vendor([
            'user_id' => $owner->id,
            'business_name' => 'Decoy '.uniqid(),
            'slug' => 'decoy-'.uniqid(),
            'business_email' => 'decoy'.uniqid().'@example.com',
            'business_phone' => '+15550000001',
            'business_address' => '2 Decoy Street',
            'status' => 'approved',
            'is_active' => true,
            'commission_rate' => 10,
        ]);
        $decoy->id = $vendorId;
        $decoy->save();

        $category = Category::create([
            'name' => 'Decoy Cat '.uniqid(),
            'slug' => 'decoy-cat-'.uniqid(),
        ]);

        $product = Product::create([
            'vendor_id' => $decoy->id,
            'category_id' => $category->id,
            'name' => 'Decoy Product '.uniqid(),
            'slug' => 'decoy-product-'.uniqid(),
            'sku' => 'DEC-'.strtoupper(uniqid()),
            'description' => 'Decoy fixture.',
            'price' => $grossSales,
            'stock_quantity' => 10,
            'stock_status' => 'in_stock',
            'is_active' => true,
        ]);

        $order = Order::create([
            'order_number' => 'DEC-'.uniqid(),
            'user_id' => User::factory()->create()->id,
            'subtotal' => $grossSales, 'tax' => 0, 'shipping_cost' => 0,
            'discount' => 0, 'total' => $grossSales,
            'payment_status' => 'paid',
            'status' => 'processing',
            'payment_method' => 'card',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'vendor_id' => $decoy->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'quantity' => 1,
            'price' => $grossSales,
            'subtotal' => $grossSales,
            'total' => $grossSales,
        ]);
    }

    /**
     * A vendor with the given value of paid, delivered sales -- pushed
     * through the real payment_status/status transitions so OrderObserver
     * credits VendorBalance via the ledger, the same as production traffic.
     *
     * @return array{0: User, 1: Vendor}
     */
    private function vendorWithPaidSales(float $grossSales): array
    {
        $user = User::factory()->vendor()->create();

        $vendor = Vendor::create([
            'user_id' => $user->id,
            'business_name' => 'Vendor '.uniqid(),
            'slug' => 'vendor-'.uniqid(),
            'business_email' => 'v'.uniqid().'@example.com',
            'business_phone' => '+15550000000',
            'business_address' => '1 Test Street',
            'status' => 'approved',
            'is_active' => true,
            'commission_rate' => 10,
        ]);

        $plan = SubscriptionPlan::create([
            'name' => 'Withdrawal Test Plan '.uniqid(),
            'slug' => 'withdrawal-test-plan-'.uniqid(),
            'feature_keys' => ['vendor_withdrawals', 'multiple_product_category'],
        ]);
        UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
        ]);

        if ($grossSales > 0) {
            $category = Category::create([
                'name' => 'Cat '.uniqid(),
                'slug' => 'cat-'.uniqid(),
            ]);

            $product = Product::create([
                'vendor_id' => $vendor->id,
                'category_id' => $category->id,
                'name' => 'Product '.uniqid(),
                'slug' => 'product-'.uniqid(),
                'sku' => 'SKU-'.strtoupper(uniqid()),
                'description' => 'Withdrawal fixture.',
                'price' => $grossSales,
                'stock_quantity' => 10,
                'stock_status' => 'in_stock',
                'is_active' => true,
            ]);

            $order = Order::create([
                'order_number' => 'WD-'.uniqid(),
                'user_id' => User::factory()->create()->id,
                'subtotal' => $grossSales, 'tax' => 0, 'shipping_cost' => 0,
                'discount' => 0, 'total' => $grossSales,
                'payment_status' => 'pending',
                'status' => 'processing',
                'payment_method' => 'card',
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'vendor_id' => $vendor->id,
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'quantity' => 1,
                'price' => $grossSales,
                'subtotal' => $grossSales,
                'total' => $grossSales,
            ]);

            $order->update(['payment_status' => 'paid']);
            $order->update(['status' => 'delivered']);
        }

        return [$user->fresh(), $vendor];
    }
}
