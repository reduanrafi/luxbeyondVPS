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
Route::get('/products/{id}', [ProductController::class, 'show']);

// Public currency routes (for request form)
Route::get('/currencies', [\App\Http\Controllers\CurrencyController::class, 'index']);

// Public brand routes (for shop filters)
Route::get('/brands', [\App\Http\Controllers\BrandController::class, 'index']);

// Public charge calculation (for request form estimation)
Route::post('/charges/calculate', [\App\Http\Controllers\ChargeController::class, 'calculate']);

// Public routes for payment methods (active only)
Route::get('/payment-methods', function () {
    $methods = \App\Models\PaymentMethod::where('is_active', true)
        ->orderBy('sort_order')
        ->get();
    return response()->json($methods);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::apiResource('requests', \App\Http\Controllers\ProductRequestController::class);
    Route::post('/requests/{id}/confirm', [\App\Http\Controllers\ProductRequestController::class, 'confirm']);
    
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
    });
});
