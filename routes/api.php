<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\BrandProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\Mobile\AuthController as MobileAuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SalonController as ApiSalonController;
use App\Http\Controllers\Api\Vendor\ModuleAccessController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Public API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->name('api.')->group(function () {
    Route::apiResource('products', ProductController::class)->only(['index', 'show']);
    Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
    Route::get('brands', [BrandController::class, 'index'])->name('brands.index');
    Route::get('brands/{brand:slug}', [BrandController::class, 'show'])->name('brands.show');
    Route::get('brands/{brand:slug}/products', [BrandProductController::class, 'index'])->name('brands.products.index');
    Route::get('salon', [ApiSalonController::class, 'index'])->name('salon.index');
});

/*
|--------------------------------------------------------------------------
| Authenticated API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->name('api.')->middleware('auth:sanctum')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');

    Route::apiResource('orders', OrderController::class)->only(['index', 'show']);
    Route::apiResource('addresses', AddressController::class);
    Route::post('/checkout/shipping-preview', [CheckoutController::class, 'shippingPreview'])->name('checkout.shipping-preview');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
});

Route::prefix('v1/vendor')->name('api.vendor.')->middleware(['auth:sanctum', 'vendor'])->group(function () {
    Route::get('/modules', [ModuleAccessController::class, 'index'])->name('modules.index');
});

Route::prefix('v1/mobile')->name('api.mobile.')->group(function () {
    Route::post('/auth/login', [MobileAuthController::class, 'login'])->middleware('throttle:10,1')->name('auth.login');
    Route::post('/auth/register', [MobileAuthController::class, 'register'])->name('auth.register');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [MobileAuthController::class, 'me'])->name('auth.me');
        Route::put('/auth/profile', [MobileAuthController::class, 'update'])->name('auth.profile');
        Route::post('/auth/logout', [MobileAuthController::class, 'logout'])->name('auth.logout');
    });
});
