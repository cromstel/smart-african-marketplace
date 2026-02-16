<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('shop.index');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');
    Route::get('/analytics/revenue-data', [\App\Http\Controllers\Admin\AnalyticsController::class, 'revenueData'])->name('analytics.revenue');
    Route::get('/analytics/top-products', [\App\Http\Controllers\Admin\AnalyticsController::class, 'topProducts'])->name('analytics.top-products');
    Route::get('/analytics/category-distribution', [\App\Http\Controllers\Admin\AnalyticsController::class, 'categoryDistribution'])->name('analytics.categories');
    Route::get('/analytics/export', [\App\Http\Controllers\Admin\AnalyticsController::class, 'export'])->name('analytics.export');
    Route::resource('products', ProductController::class);
    Route::delete('/product-images/{image}', [ProductController::class, 'deleteImage'])->name('products.images.delete');
    Route::post('/product-images/{image}/primary', [ProductController::class, 'setPrimaryImage'])->name('products.images.primary');
    Route::get('/products/{product}/variants', [\App\Http\Controllers\Admin\ProductVariantController::class, 'index'])->name('products.variants.index');
    Route::get('/products/{product}/variants/create', [\App\Http\Controllers\Admin\ProductVariantController::class, 'create'])->name('products.variants.create');
    Route::post('/products/{product}/variants', [\App\Http\Controllers\Admin\ProductVariantController::class, 'store'])->name('products.variants.store');
    Route::get('/products/{product}/variants/{variant}/edit', [\App\Http\Controllers\Admin\ProductVariantController::class, 'edit'])->name('products.variants.edit');
    Route::put('/products/{product}/variants/{variant}', [\App\Http\Controllers\Admin\ProductVariantController::class, 'update'])->name('products.variants.update');
    Route::delete('/products/{product}/variants/{variant}', [\App\Http\Controllers\Admin\ProductVariantController::class, 'destroy'])->name('products.variants.destroy');
    Route::resource('categories', CategoryController::class);
    Route::resource('orders', AdminOrderController::class);
    Route::put('/orders/{order}/status', [\App\Http\Controllers\Admin\OrderStatusController::class, 'updateStatus'])->name('orders.update-status');
    Route::resource('vendors', VendorController::class);
    Route::resource('coupons', \App\Http\Controllers\Admin\CouponController::class)->except(['show']);
    Route::resource('salon-services', \App\Http\Controllers\Admin\SalonServiceController::class)->except(['show']);
    Route::resource('bookings', \App\Http\Controllers\Admin\BookingController::class)->only(['index', 'show']);
    Route::put('/bookings/{booking}/status', [\App\Http\Controllers\Admin\BookingController::class, 'updateStatus'])->name('bookings.update-status');
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
});

Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{product}', [ShopController::class, 'show'])->name('shop.show');

Route::get('/salons', [\App\Http\Controllers\SalonController::class, 'index'])->name('salons.index');
Route::get('/salons/{service}', [\App\Http\Controllers\SalonController::class, 'show'])->name('salons.show');

Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    Route::resource('addresses', AddressController::class);

    Route::get('/payment/stripe/{order}', [PaymentController::class, 'stripe'])->name('payment.stripe');
    Route::post('/payment/stripe/{order}', [PaymentController::class, 'stripeProcess'])->name('payment.stripe.process');
    Route::get('/payment/paystack/{order}', [PaymentController::class, 'paystack'])->name('payment.paystack');
    Route::get('/payment/paystack/{order}/callback', [PaymentController::class, 'paystackCallback'])->name('payment.paystack.callback');
    Route::get('/payment/momo/{order}', [PaymentController::class, 'momo'])->name('payment.momo');
    Route::post('/payment/momo/{order}', [PaymentController::class, 'momoProcess'])->name('payment.momo.process');
    Route::get('/payment/success/{order}', [PaymentController::class, 'success'])->name('payment.success');
    Route::get('/payment/failed/{order}', [PaymentController::class, 'failed'])->name('payment.failed');

    Route::post('/products/{product}/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/reviews/{review}', [\App\Http\Controllers\ReviewController::class, 'destroy'])->name('reviews.destroy');

    Route::get('/wishlist', [\App\Http\Controllers\WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist', [\App\Http\Controllers\WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{wishlist}', [\App\Http\Controllers\WishlistController::class, 'destroy'])->name('wishlist.destroy');

    Route::post('/coupon/apply', [\App\Http\Controllers\CouponController::class, 'apply'])->name('coupon.apply');
    Route::delete('/coupon/remove', [\App\Http\Controllers\CouponController::class, 'remove'])->name('coupon.remove');

    Route::get('/bookings', [\App\Http\Controllers\BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [\App\Http\Controllers\BookingController::class, 'show'])->name('bookings.show');
    Route::get('/salons/{service}/book', [\App\Http\Controllers\BookingController::class, 'create'])->name('bookings.create');
    Route::post('/salons/{service}/book', [\App\Http\Controllers\BookingController::class, 'store'])->name('bookings.store');
    Route::put('/bookings/{booking}/cancel', [\App\Http\Controllers\BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::get('/api/salons/{service}/available-slots', [\App\Http\Controllers\BookingController::class, 'getAvailableSlots'])->name('bookings.available-slots');
});

Route::prefix('vendor')->name('vendor.')->middleware(['auth', 'vendor'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Vendor\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('products', \App\Http\Controllers\Vendor\ProductController::class)->except(['show']);
    Route::delete('/product-images/{image}', [\App\Http\Controllers\Vendor\ProductController::class, 'deleteImage'])->name('products.images.delete');
    Route::post('/product-images/{image}/primary', [\App\Http\Controllers\Vendor\ProductController::class, 'setPrimaryImage'])->name('products.images.primary');
});

Route::post('/webhooks/stripe', [\App\Http\Controllers\WebhookController::class, 'stripe'])->name('webhooks.stripe');
Route::post('/webhooks/paystack', [\App\Http\Controllers\WebhookController::class, 'paystack'])->name('webhooks.paystack');

Route::get('/auth/{provider}/redirect', [\App\Http\Controllers\Auth\SocialAuthController::class, 'redirect'])->name('social.redirect');
Route::get('/auth/{provider}/callback', [\App\Http\Controllers\Auth\SocialAuthController::class, 'callback'])->name('social.callback');

require __DIR__.'/auth.php';
