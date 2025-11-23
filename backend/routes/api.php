<?php

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

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::apiResource('requests', \App\Http\Controllers\ProductRequestController::class);
    Route::post('/requests/{id}/confirm', [\App\Http\Controllers\ProductRequestController::class, 'confirm']);
    
    // Admin routes
    Route::middleware('role:Admin')->group(function () {
        // Customers
        Route::get('/customers', [\App\Http\Controllers\CustomerController::class, 'index']);
        Route::get('/customers/{id}', [\App\Http\Controllers\CustomerController::class, 'show']);
        Route::post('/customers/{id}/toggle-status', [\App\Http\Controllers\CustomerController::class, 'toggleStatus']);
        
        // Products
        Route::apiResource('products', \App\Http\Controllers\ProductController::class);
        
        // Categories
        Route::apiResource('categories', \App\Http\Controllers\CategoryController::class);
        
        // Coupons
        Route::apiResource('coupons', \App\Http\Controllers\CouponController::class);
        Route::post('/coupons/apply', [\App\Http\Controllers\CouponController::class, 'apply']);
    });
});
