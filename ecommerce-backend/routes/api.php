<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/payment-methods', [PaymentController::class, 'getPaymentMethods']);

// Auth routes (public)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes for authenticated users
Route::middleware('auth:sanctum')->group(function () {

     // User endpoint 
    Route::get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    });

    // Routes for all authenticated users
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);

    // Payment routes
    Route::prefix('payment')->group(function () {
        Route::post('/create-intent', [PaymentController::class, 'createPaymentIntent']);
        Route::post('/confirm', [PaymentController::class, 'confirmPayment']);
        Route::post('/process', [PaymentController::class, 'processPayment']);
    });

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Admin-only routes
    Route::middleware('admin')->group(function () {
        // Product management
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);

        // Order management - ADD THESE ROUTES
        Route::get('/admin/orders', [OrderController::class, 'adminIndex']); // Get all orders
        Route::put('/admin/orders/{id}', [OrderController::class, 'adminUpdate']); // Update order

        // Order status management (keep existing for compatibility)
        Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);
    });
});

// Stripe webhook endpoint (public)
Route::post('/payment/webhook', [PaymentController::class, 'webhook']);