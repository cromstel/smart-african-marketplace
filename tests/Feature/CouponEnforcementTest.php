<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Regression guard for coupon usage limits being validated and stored by
 * the vendor form (see CouponFormFieldsTest) but never actually enforced
 * at redemption time.
 *
 * Coupon::incrementUsage() existed but was never called anywhere, so
 * usage_count stayed at 0 forever and a usage_limit could never be
 * reached. Separately, Coupon::canBeUsedByUser()'s per_user_limit check
 * counted every Order row with a matching coupon_code, including the
 * per-vendor child orders a single checkout creates alongside its parent
 * -- inflating a user's usage count by however many vendors their cart
 * spanned. Both are fixed in CheckoutController (web + API) and Coupon.
 */
class CouponEnforcementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_completing_a_checkout_increments_the_coupons_usage_count(): void
    {
        $user = User::factory()->create();
        $product = $this->makeVendorProduct();
        $coupon = Coupon::create([
            'code' => 'SAVE10',
            'type' => 'fixed',
            'value' => 5,
            'usage_limit' => 1,
            'usage_count' => 0,
            'is_active' => true,
        ]);
        $this->cartWithProductAndCoupon($user, $product, $coupon);
        $billing = $this->makeAddress($user, 'billing');
        $shipping = $this->makeAddress($user, 'shipping');

        $this->actingAs($user)->post(route('checkout.store'), [
            'billing_address_id' => $billing->id,
            'shipping_address_id' => $shipping->id,
            'payment_method' => 'cash_on_delivery',
        ])->assertRedirect();

        $this->assertSame(1, $coupon->fresh()->usage_count);
    }

    public function test_a_coupon_cannot_be_applied_again_once_its_usage_limit_is_reached(): void
    {
        $user = User::factory()->create();
        $coupon = Coupon::create([
            'code' => 'ONESHOT',
            'type' => 'fixed',
            'value' => 5,
            'usage_limit' => 1,
            'usage_count' => 1, // already redeemed once
            'is_active' => true,
        ]);
        $product = $this->makeVendorProduct();
        $this->cartWithProduct($user, $product);

        $response = $this->actingAs($user)->post(route('coupon.apply'), ['code' => $coupon->code]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'This coupon is no longer valid');
        $this->assertNull(Cart::where('user_id', $user->id)->first()->coupon_code);
    }

    public function test_a_multi_vendor_checkout_only_counts_as_one_redemption_for_the_per_user_limit(): void
    {
        $user = User::factory()->create();
        $productA = $this->makeVendorProduct();
        $productB = $this->makeVendorProduct();
        $coupon = Coupon::create([
            'code' => 'ONEPERUSER',
            'type' => 'fixed',
            'value' => 5,
            'per_user_limit' => 1,
            'usage_count' => 0,
            'is_active' => true,
        ]);

        $cart = Cart::create(['user_id' => $user->id, 'subtotal' => 0, 'tax' => 0, 'shipping' => 0, 'discount' => 0, 'total' => 0]);
        foreach ([$productA, $productB] as $product) {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => 1,
                'price' => $product->price,
                'subtotal' => $product->price,
            ]);
        }
        $cart->update(['coupon_code' => $coupon->code, 'discount' => 5]);
        $cart->calculateTotals();

        $billing = $this->makeAddress($user, 'billing');
        $shipping = $this->makeAddress($user, 'shipping');

        $this->actingAs($user)->post(route('checkout.store'), [
            'billing_address_id' => $billing->id,
            'shipping_address_id' => $shipping->id,
            'payment_method' => 'cash_on_delivery',
        ])->assertRedirect();

        // Two vendors -> one parent Order + two child Orders all carrying
        // coupon_code. The per-user usage count must reflect one checkout,
        // not three matching rows.
        $this->assertSame(
            1,
            Order::where('user_id', $user->id)->where('coupon_code', $coupon->code)->where('is_parent', true)->count()
        );

        $secondProduct = $this->makeVendorProduct();
        $this->cartWithProduct($user, $secondProduct);

        $response = $this->actingAs($user)->post(route('coupon.apply'), ['code' => $coupon->code]);

        $response->assertSessionHas('error', 'You have reached the usage limit for this coupon');
    }

    private function makeVendorProduct(int $stock = 100): Product
    {
        $owner = User::factory()->create();

        $vendor = Vendor::create([
            'user_id' => $owner->id,
            'business_name' => 'Vendor '.uniqid(),
            'slug' => 'vendor-'.uniqid(),
            'business_email' => 'vendor'.uniqid().'@example.com',
            'business_phone' => '+15550000000',
            'business_address' => '1 Test Street',
            'status' => 'approved',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Category '.uniqid(),
            'slug' => 'category-'.uniqid(),
        ]);

        return Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Product '.uniqid(),
            'slug' => 'product-'.uniqid(),
            'sku' => 'SKU-'.strtoupper(uniqid()),
            'description' => 'A product created for coupon enforcement tests.',
            'price' => 25.00,
            'stock_quantity' => $stock,
            'stock_status' => 'in_stock',
            'is_active' => true,
        ]);
    }

    private function cartWithProduct(User $user, Product $product, int $quantity = 1): Cart
    {
        $cart = Cart::create([
            'user_id' => $user->id,
            'subtotal' => 0,
            'tax' => 0,
            'shipping' => 0,
            'discount' => 0,
            'total' => 0,
        ]);

        $cart->items()->create([
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price' => $product->price,
            'subtotal' => $product->price * $quantity,
        ]);

        $cart->calculateTotals();

        return $cart;
    }

    private function cartWithProductAndCoupon(User $user, Product $product, Coupon $coupon, int $quantity = 1): Cart
    {
        $cart = $this->cartWithProduct($user, $product, $quantity);
        $cart->update(['coupon_code' => $coupon->code, 'discount' => (float) $coupon->value]);
        $cart->calculateTotals();

        return $cart;
    }

    private function makeAddress(User $user, string $type): Address
    {
        return Address::create([
            'user_id' => $user->id,
            'type' => $type,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'address_line1' => '123 Market St',
            'city' => 'Lagos',
            'country' => 'NG',
            'is_default' => true,
        ]);
    }
}
