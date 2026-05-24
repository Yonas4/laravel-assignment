<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\PaymentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', function () {
        return response()->json(['status' => 'ok']);
    });

    // ──────────────────────────────────────────────────────
    // AUTH
    // ──────────────────────────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
        });
    });

    // ──────────────────────────────────────────────────────
    // PAYMENTS
    // ──────────────────────────────────────────────────────
    Route::prefix('payments')->group(function () {
        // Public
        Route::get('/gateways', [PaymentController::class, 'gateways']);
        Route::post('/callback/{gateway}', [PaymentController::class, 'callback']);

        // Protected
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/initiate', [PaymentController::class, 'initiate']);
            Route::get('/transactions', [PaymentController::class, 'index']);
            Route::get('/transactions/{id}', [PaymentController::class, 'show']);
        });
    });

    Route::prefix('subscriptions')->middleware('auth:sanctum')->group(function () {
        Route::post('/trial', [\App\Http\Controllers\Api\V1\SubscriptionController::class, 'trial']);
    });

    Route::prefix('services')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\V1\ServiceController::class, 'index']);
        
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/{id}/book', [\App\Http\Controllers\Api\V1\BookingController::class, 'store']);
        });
    });

    Route::prefix('packages')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\V1\PackageController::class, 'index']);
        
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/{id}/add-to-cart', [\App\Http\Controllers\Api\V1\PackageController::class, 'addToCart']);
        });
    });

    // ──────────────────────────────────────────────────────
    // CART
    // ──────────────────────────────────────────────────────
    Route::prefix('cart')->middleware('auth:sanctum')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/items', [CartController::class, 'store']);
        Route::delete('/items/{id}', [CartController::class, 'destroy']);
        Route::delete('/', [CartController::class, 'clear']);
    });
});
