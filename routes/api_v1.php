<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CartController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', function () {
        return response()->json(['status' => 'ok']);
    });

    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
        });
    });

    Route::prefix('payments')->group(function () {
        Route::get('/gateways', [\App\Http\Controllers\Api\V1\PaymentController::class, 'gateways']);
        Route::post('/callback/{gateway}', [\App\Http\Controllers\Api\V1\PaymentController::class, 'callback']);
        
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/initiate', [\App\Http\Controllers\Api\V1\PaymentController::class, 'initiate']);
            Route::get('/transactions', [\App\Http\Controllers\Api\V1\PaymentController::class, 'index']);
            Route::get('/transactions/{id}', [\App\Http\Controllers\Api\V1\PaymentController::class, 'show']);
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
