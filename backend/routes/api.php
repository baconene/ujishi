<?php

use App\Http\Controllers\API\Admin\AdminCategoryController;
use App\Http\Controllers\API\Admin\AdminDashboardController;
use App\Http\Controllers\API\Admin\AdminHomepageController;
use App\Http\Controllers\API\Admin\AdminMediaController;
use App\Http\Controllers\API\Admin\AdminOrderController;
use App\Http\Controllers\API\Admin\AdminProductController;
use App\Http\Controllers\API\Admin\AdminSettingsController;
use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\Shop\CartController;
use App\Http\Controllers\API\Shop\HomepageController;
use App\Http\Controllers\API\Shop\NewsletterController;
use App\Http\Controllers\API\Shop\OrderController;
use App\Http\Controllers\API\Shop\ProductController;
use App\Http\Controllers\API\Shop\ReviewController;
use App\Http\Controllers\API\Shop\WishlistController;
use Illuminate\Support\Facades\Route;

// --- Public ---
Route::get('/homepage', [HomepageController::class, 'index']);
Route::get('/categories', [HomepageController::class, 'categories']);
Route::get('/pages/{slug}', [HomepageController::class, 'page']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe']);
Route::post('/payment/webhook', [OrderController::class, 'webhook']);

// --- Auth ---
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
    });
});

// --- Cart & Wishlist (guest + auth, optional Sanctum token) ---
// These work without a token (guest) and also when authenticated.
// Controllers call $request->user('sanctum') which returns null for guests.
Route::get('/cart', [CartController::class, 'index']);
Route::post('/cart', [CartController::class, 'store']);
Route::put('/cart/{item}', [CartController::class, 'update']);
Route::delete('/cart/{item}', [CartController::class, 'destroy']);
Route::post('/cart/coupon', [CartController::class, 'applyCoupon']);

// --- Customer (authenticated) ---
Route::middleware('auth:sanctum')->group(function () {
    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle']);

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);

    // Reviews
    Route::post('/products/{productId}/reviews', [ReviewController::class, 'store']);
});

// --- Admin ---
Route::middleware(['auth:sanctum', \App\Http\Middleware\AdminMiddleware::class])->prefix('admin')->group(function () {
    // Dashboard
    Route::get('/dashboard/stats', [AdminDashboardController::class, 'stats']);

    // Products
    Route::get('/products', [AdminProductController::class, 'index']);
    Route::post('/products', [AdminProductController::class, 'store']);
    Route::get('/products/{id}', [AdminProductController::class, 'show']);
    Route::put('/products/{id}', [AdminProductController::class, 'update']);
    Route::delete('/products/{id}', [AdminProductController::class, 'destroy']);

    // Categories
    Route::get('/categories', [AdminCategoryController::class, 'index']);
    Route::post('/categories', [AdminCategoryController::class, 'store']);
    Route::put('/categories/{id}', [AdminCategoryController::class, 'update']);
    Route::delete('/categories/{id}', [AdminCategoryController::class, 'destroy']);

    // Orders
    Route::get('/orders', [AdminOrderController::class, 'index']);
    Route::get('/orders/{id}', [AdminOrderController::class, 'show']);
    Route::put('/orders/{id}/status', [AdminOrderController::class, 'updateStatus']);

    // Media
    Route::get('/media', [AdminMediaController::class, 'index']);
    Route::post('/media', [AdminMediaController::class, 'store']);
    Route::put('/media/{id}', [AdminMediaController::class, 'update']);
    Route::delete('/media/{id}', [AdminMediaController::class, 'destroy']);

    // Homepage Builder
    Route::get('/homepage/sections', [AdminHomepageController::class, 'sections']);
    Route::put('/homepage/sections/{id}', [AdminHomepageController::class, 'updateSection']);
    Route::post('/homepage/sections/reorder', [AdminHomepageController::class, 'reorderSections']);

    // Carousel
    Route::get('/carousel', [AdminHomepageController::class, 'slides']);
    Route::post('/carousel', [AdminHomepageController::class, 'storeSlide']);
    Route::put('/carousel/{id}', [AdminHomepageController::class, 'updateSlide']);
    Route::delete('/carousel/{id}', [AdminHomepageController::class, 'destroySlide']);
    Route::post('/carousel/reorder', [AdminHomepageController::class, 'reorderSlides']);

    // Banners
    Route::get('/banners', [AdminHomepageController::class, 'banners']);
    Route::post('/banners', [AdminHomepageController::class, 'storeBanner']);
    Route::put('/banners/{id}', [AdminHomepageController::class, 'updateBanner']);
    Route::delete('/banners/{id}', [AdminHomepageController::class, 'destroyBanner']);

    // Settings
    Route::get('/settings', [AdminSettingsController::class, 'index']);
    Route::put('/settings', [AdminSettingsController::class, 'update']);
});
