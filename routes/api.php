<?php

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

Route::prefix('v1')->group(function () {
    // Products
    Route::get('/products', function () {
        return response()->json([
            'message' => 'Product list endpoint',
            'data' => [],
        ]);
    });

    // Categories
    Route::get('/categories', function () {
        return response()->json([
            'message' => 'Category list endpoint',
            'data' => [],
        ]);
    });
});

/*
|--------------------------------------------------------------------------
| Authenticated API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Cart
    Route::post('/cart/add', function (Request $request) {
        return response()->json([
            'message' => 'Item added to cart',
            'data' => [],
        ]);
    });

    // Orders
    Route::get('/orders', function (Request $request) {
        return response()->json([
            'message' => 'User orders',
            'data' => [],
        ]);
    });
});
