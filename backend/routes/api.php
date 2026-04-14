<?php

use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductRequestController;

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
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

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
    
    // If no payment methods found in database, return default methods
    if ($methods->isEmpty()) {
        return response()->json([
            [
                'id' => 0,
                'type' => 'bkash',
                'name' => 'bKash',
                'description' => 'Pay with bKash mobile wallet',
                'is_active' => true,
                'is_online' => true,
                'sort_order' => 1,
                'config' => null // Will use .env credentials
            ],
            [
                'id' => 0,
                'type' => 'bank_transfer',
                'name' => 'Bank Transfer',
                'description' => 'Pay via bank transfer and upload payment slip',
                'is_active' => true,
                'is_online' => false,
                'sort_order' => 2,
                'config' => null
            ]
        ]);
    }
    
    return response()->json($methods);
});

// Public checkout settings
Route::get('/settings/checkout', [\App\Http\Controllers\Public\SettingsController::class, 'getCheckoutSettings']);
Route::get('/settings/general', [\App\Http\Controllers\Public\SettingsController::class, 'getGeneralSettings']);


// Public payment callback (bKash redirects here)
Route::get('/payments/bkash/callback', [\App\Http\Controllers\PaymentController::class, 'bkashCallback']);


// Public Tracking
Route::post('/track-order', [\App\Http\Controllers\Public\TrackingController::class, 'track']);
Route::get('/download-invoice/{orderId}', [\App\Http\Controllers\OrderController::class, 'downloadInvoice']);

// Facebook Catalog XML
Route::get('/catalog.xml', [\App\Http\Controllers\Public\CatalogController::class, 'facebookCatalog']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);

    // Product Requests
    Route::post('/product-requests/bkash/initiate', [ProductRequestController::class, 'initiateBkashPayment']);
    Route::apiResource('product-requests', ProductRequestController::class);
    Route::post('/product-requests/{id}/payment', [ProductRequestController::class, 'submitPaymentDetails']);
    Route::post('/product-requests/{id}/confirm-order', [ProductRequestController::class, 'confirmOrder']);
    Route::post('/product-requests/create-order', [ProductRequestController::class, 'createOrderFromRequests']); // New Route
    Route::post('/product-requests/{id}/update-quantity', [ProductRequestController::class, 'updateQuantity']);
    
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

    // Customer Dashboard Data
    Route::get('/coupons/available', [\App\Http\Controllers\CouponController::class, 'available']);

    Route::post('/addresses/shipping', [\App\Http\Controllers\AddressController::class, 'saveShipping']);
    Route::post('/addresses/billing', [\App\Http\Controllers\AddressController::class, 'saveBilling']);

    Route::get('/notifications/preferences', [\App\Http\Controllers\NotificationPreferenceController::class, 'index']);
    Route::put('/notifications/preferences', [\App\Http\Controllers\NotificationPreferenceController::class, 'update']);

    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
    Route::get('/notifications/unread', [\App\Http\Controllers\NotificationController::class, 'unread']);
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead']);

    Route::delete('/account', [\App\Http\Controllers\AccountController::class, 'destroy']);

    // Coupon apply (for customers during checkout)
    Route::post('/coupons/apply', [\App\Http\Controllers\CouponController::class, 'apply']);
    
    // Admin routes
    Route::middleware('role:Admin')->prefix('admin')->group(function () {
        // Customers
        Route::get('/customers', [\App\Http\Controllers\CustomerController::class, 'index']);
        Route::post('/customers', [\App\Http\Controllers\CustomerController::class, 'store']);
        Route::get('/customers-stats', [\App\Http\Controllers\CustomerController::class, 'stats']);
        Route::get('/customers/{id}', [\App\Http\Controllers\CustomerController::class, 'show']);
        Route::put('/customers/{id}', [\App\Http\Controllers\CustomerController::class, 'update']);
        Route::post('/customers/{id}/toggle-status', [\App\Http\Controllers\CustomerController::class, 'toggleStatus']);

    // User & Role Management
    Route::apiResource('users', \App\Http\Controllers\Admin\UsersController::class);
    Route::apiResource('roles', \App\Http\Controllers\Admin\RolesController::class);
    Route::get('/permissions', [\App\Http\Controllers\Admin\RolesController::class, 'permissions']);

    // Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [App\Http\Controllers\Admin\DashboardController::class, 'stats']);
        Route::get('/charts', [App\Http\Controllers\Admin\DashboardController::class, 'charts']);
        Route::get('/top-products', [App\Http\Controllers\Admin\DashboardController::class, 'topProducts']);
    });

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
        Route::get('/orders/stats', [\App\Http\Controllers\OrderController::class, 'stats']);
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
            Route::post('/logo', [\App\Http\Controllers\SettingsController::class, 'uploadLogo']);
            Route::put('/{key}', [\App\Http\Controllers\SettingsController::class, 'updateSetting']);
            
            // Payment Methods
            Route::get('/payment-methods', [\App\Http\Controllers\SettingsController::class, 'getPaymentMethods']);
            Route::post('/payment-methods', [\App\Http\Controllers\SettingsController::class, 'storePaymentMethod']);
            Route::put('/payment-methods/{id}', [\App\Http\Controllers\SettingsController::class, 'updatePaymentMethod']);
            Route::delete('/payment-methods/{id}', [\App\Http\Controllers\SettingsController::class, 'deletePaymentMethod']);

            // Product Requests (Admin)
            Route::get('/product-requests', [\App\Http\Controllers\ProductRequestController::class, 'adminIndex']);

            // Notification Settings
            Route::get('/notifications', [\App\Http\Controllers\SettingsController::class, 'getNotificationSettings']);
            Route::post('/notifications', [\App\Http\Controllers\SettingsController::class, 'storeNotificationSetting']);
            Route::put('/notifications/{id}', [\App\Http\Controllers\SettingsController::class, 'updateNotificationSetting']);
            Route::delete('/notifications/{id}', [\App\Http\Controllers\SettingsController::class, 'deleteNotificationSetting']);
        });

        // Product Requests
        Route::get('/requests', [\App\Http\Controllers\ProductRequestController::class, 'adminIndex']);
        Route::get('/requests/{id}', [\App\Http\Controllers\ProductRequestController::class, 'show']);
        Route::put('/requests/{id}', [\App\Http\Controllers\ProductRequestController::class, 'update']);
        Route::post('/requests/{id}/convert', [\App\Http\Controllers\ProductRequestController::class, 'convertToOrder']);

        // Events Management
        Route::apiResource('events', \App\Http\Controllers\Admin\EventController::class);
        Route::get('/events/products/search', [\App\Http\Controllers\Admin\EventController::class, 'getProducts']);

        // Blog Management
        Route::apiResource('blogs', \App\Http\Controllers\Admin\BlogController::class);
        
        // Traveller Management
        Route::get('travellers', [\App\Http\Controllers\Admin\TravellerController::class, 'index']);
        Route::get('travellers/{id}', [\App\Http\Controllers\Admin\TravellerController::class, 'show']);
        Route::post('travellers/{id}/status', [\App\Http\Controllers\Admin\TravellerController::class, 'updateStatus']);
        
        // Payout Management
        Route::apiResource('payouts', \App\Http\Controllers\Admin\PayoutController::class)->only(['index', 'update']);

        // Pages / CMS Management
        Route::apiResource('pages', \App\Http\Controllers\Admin\PageContentController::class);

        // Extended Traveller Management
        Route::put('travellers/{id}/profile', [\App\Http\Controllers\Admin\TravellerController::class, 'updateProfile']);
        
        // Admin Trip Management
        Route::put('trips/{id}/status', [\App\Http\Controllers\Admin\TripController::class, 'updateStatus']);
        Route::delete('trips/{id}', [\App\Http\Controllers\Admin\TripController::class, 'destroy']);
    });
});

// Public Events API
Route::get('/events', [\App\Http\Controllers\Public\EventController::class, 'index']);
Route::get('/events/{slug}', [\App\Http\Controllers\Public\EventController::class, 'show']);

// Public Blog API
Route::get('/blogs', [\App\Http\Controllers\Public\BlogController::class, 'index']);
Route::get('/blogs/recent', [\App\Http\Controllers\Public\BlogController::class, 'getRecent']);
Route::get('/blogs/{slug}', [\App\Http\Controllers\Public\BlogController::class, 'show']);

// Public Pages API (CMS)
Route::get('/pages', [\App\Http\Controllers\Public\PageContentController::class, 'index']);
Route::get('/pages/{key}', [\App\Http\Controllers\Public\PageContentController::class, 'show']);

// Authenticated Routes
Route::middleware('auth:sanctum')->group(function () {
    // User Context
    Route::get('/user', function (Request $request) {
        return $request->user()->load(['roles', 'permissions']);
    });
    
    // Traveller Dashboard
    Route::get('/traveller/profile', [\App\Http\Controllers\Traveller\DashboardController::class, 'getProfile']);
    Route::post('/traveller/profile', [\App\Http\Controllers\Traveller\DashboardController::class, 'updateProfile']);
    
    // Traveller Trips
    Route::apiResource('/traveller/trips', \App\Http\Controllers\Traveller\TripController::class);
});
