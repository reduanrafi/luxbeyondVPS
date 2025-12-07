<?php

use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Public product routes
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']); // Supports both slug and ID for backward compatibility

// Public currency routes (for request form)
    Route::get('/currencies', [\App\Http\Controllers\CurrencyController::class, 'index']);
    Route::get('/order-statuses', [\App\Http\Controllers\OrderStatusController::class, 'index'])->middleware('auth:sanctum');

    // Public brand routes (for shop filters)
Route::get('/brands', [\App\Http\Controllers\BrandController::class, 'index']);
Route::get('/categories', [\App\Http\Controllers\Public\CategoryController::class, 'index']);

// Public charge calculation (for request form estimation)
Route::post('/charges/calculate', [\App\Http\Controllers\ChargeController::class, 'calculate']);

// Public routes for payment methods (active only)
Route::get('/payment-methods', function () {
    $methods = \App\Models\PaymentMethod::where('is_active', true)
        ->orderBy('sort_order')
        ->get();
    return response()->json($methods);
});

// Public payment callback (bKash redirects here)
Route::get('/payments/bkash/callback', [\App\Http\Controllers\PaymentController::class, 'bkashCallback']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);

    Route::apiResource('requests', \App\Http\Controllers\ProductRequestController::class);
    Route::post('/requests/{id}/confirm', [\App\Http\Controllers\ProductRequestController::class, 'confirm']);
    
    // Orders (for customers)
    Route::get('/orders', [\App\Http\Controllers\OrderController::class, 'index']);
    Route::post('/orders', [\App\Http\Controllers\OrderController::class, 'store']);
    Route::get('/orders/{id}', [\App\Http\Controllers\OrderController::class, 'show']);
    Route::post('/orders/{id}/upload-payment-slip', [\App\Http\Controllers\PaymentController::class, 'uploadPaymentSlip']);
    
    // Payment initiation (requires auth)
    Route::post('/payments/bkash/initiate', [\App\Http\Controllers\PaymentController::class, 'initiateBkashPayment']);

    // Cart (persisted per user)
    Route::get('/cart', [\App\Http\Controllers\CartController::class, 'index']);
    Route::post('/cart', [\App\Http\Controllers\CartController::class, 'store']);
    Route::delete('/cart/{cartItem}', [\App\Http\Controllers\CartController::class, 'destroy']);
    Route::patch('/cart/{cartItem}', [\App\Http\Controllers\CartController::class, 'update']);
    Route::post('/cart/clear', [\App\Http\Controllers\CartController::class, 'clear']);

    // Wishlist (persisted per user)
    Route::get('/wishlist', [\App\Http\Controllers\WishlistController::class, 'index']);
    Route::post('/wishlist', [\App\Http\Controllers\WishlistController::class, 'store']);
    Route::post('/wishlist/toggle', [\App\Http\Controllers\WishlistController::class, 'toggle']);
    Route::delete('/wishlist/{wishlistItem}', [\App\Http\Controllers\WishlistController::class, 'destroy']);
    
    // Coupon apply (for customers during checkout)
    Route::post('/coupons/apply', [\App\Http\Controllers\CouponController::class, 'apply']);
    
    // Admin routes
    Route::middleware('role:Admin')->prefix('admin')->group(function () {
        // Customers
        Route::get('/customers', [\App\Http\Controllers\CustomerController::class, 'index']);
        Route::get('/customers/{id}', [\App\Http\Controllers\CustomerController::class, 'show']);
        Route::post('/customers/{id}/toggle-status', [\App\Http\Controllers\CustomerController::class, 'toggleStatus']);
        
        // Products
        Route::apiResource('products', \App\Http\Controllers\ProductController::class);
        
        // Categories
        Route::apiResource('categories', \App\Http\Controllers\CategoryController::class);

        // Brands
        Route::apiResource('brands', \App\Http\Controllers\BrandController::class);

        // Coupons
        Route::apiResource('coupons', \App\Http\Controllers\CouponController::class);
        Route::post('/coupons/apply', [\App\Http\Controllers\CouponController::class, 'apply']);

        // Currencies
        Route::apiResource('currencies', \App\Http\Controllers\CurrencyController::class);

        // Charges
        Route::apiResource('charges', \App\Http\Controllers\ChargeController::class);
        Route::post('/charges/calculate', [\App\Http\Controllers\ChargeController::class, 'calculate']);

        // Orders
        Route::apiResource('orders', \App\Http\Controllers\OrderController::class);
        Route::post('/orders/{id}/update-status', [\App\Http\Controllers\OrderController::class, 'updateStatus']);
        
        // Payment verification
        Route::post('/payments/verify-bkash', [\App\Http\Controllers\PaymentController::class, 'verifyBkashPayment']);

        // Order Statuses
        Route::apiResource('order-statuses', \App\Http\Controllers\OrderStatusController::class);

        // Settings
        Route::prefix('settings')->group(function () {
            Route::get('/', [\App\Http\Controllers\SettingsController::class, 'getSettings']);
            Route::post('/update', [\App\Http\Controllers\SettingsController::class, 'updateSettings']);
            Route::put('/{key}', [\App\Http\Controllers\SettingsController::class, 'updateSetting']);
            
            // Payment Methods
            Route::get('/payment-methods', [\App\Http\Controllers\SettingsController::class, 'getPaymentMethods']);
            Route::post('/payment-methods', [\App\Http\Controllers\SettingsController::class, 'storePaymentMethod']);
            Route::put('/payment-methods/{id}', [\App\Http\Controllers\SettingsController::class, 'updatePaymentMethod']);
            Route::delete('/payment-methods/{id}', [\App\Http\Controllers\SettingsController::class, 'deletePaymentMethod']);
            
            // Notification Settings
            Route::get('/notifications', [\App\Http\Controllers\SettingsController::class, 'getNotificationSettings']);
            Route::post('/notifications', [\App\Http\Controllers\SettingsController::class, 'storeNotificationSetting']);
            Route::put('/notifications/{id}', [\App\Http\Controllers\SettingsController::class, 'updateNotificationSetting']);
            Route::delete('/notifications/{id}', [\App\Http\Controllers\SettingsController::class, 'deleteNotificationSetting']);
        });

        // Events Management
        Route::apiResource('events', \App\Http\Controllers\Admin\EventController::class);
        Route::get('/events/products/search', [\App\Http\Controllers\Admin\EventController::class, 'getProducts']);
    });
});

// Public Events API
Route::get('/events', [\App\Http\Controllers\Public\EventController::class, 'index']);
Route::get('/events/{slug}', [\App\Http\Controllers\Public\EventController::class, 'show']);
