<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PhoneVerificationController;
use App\Http\Controllers\Api\V1\PasswordResetController;
use App\Http\Controllers\Api\V1\Admin\ProductManagementController;
use App\Http\Controllers\Api\V1\Admin\OrderManagementController;



Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        
    
        // Registration & Login
        Route::post('/register', [AuthController::class, 'register'])
            ->middleware('throttle:5,1');
            
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:10,1');
        
        //admin routes
        Route::prefix('admin')
            ->middleware(['auth:sanctum', 'admin'])
            ->group(function () {
                
                // ─── Product Management ───
                Route::apiResource('products', ProductManagementController::class);
                Route::post('/products/{product}/stock', [ProductManagementController::class, 'updateStock']);
                
                // ─── Order Management ───
                Route::get('/orders', [OrderManagementController::class, 'index']);
                Route::get('/orders/{order}', [OrderManagementController::class, 'show']);
                Route::put('/orders/{order}/status', [OrderManagementController::class, 'updateStatus']);
                Route::post('/orders/{order}/cancel', [OrderManagementController::class, 'cancel']);
                Route::get('/orders/statistics', [OrderManagementController::class, 'statistics']);
        });

        //user routes
        Route::middleware(['auth:sanctum'])->group(function () {
        
            //Products
            Route::get('/products', [\App\Http\Controllers\Api\V1\ProductController::class, 'index']);
            Route::get('/products/{product}', [\App\Http\Controllers\Api\V1\ProductController::class, 'show']);
            
            //User's own orders
            Route::get('/orders', [\App\Http\Controllers\Api\V1\OrderController::class, 'index']);
            Route::get('/orders/{order}', [\App\Http\Controllers\Api\V1\OrderController::class, 'show']);
            Route::post('/orders', [\App\Http\Controllers\Api\V1\OrderController::class, 'store']);
            Route::post('/orders/{order}/cancel', [\App\Http\Controllers\Api\V1\OrderController::class, 'cancel']);
            
            //Profile 
            Route::get('/profile', [\App\Http\Controllers\Api\V1\ProfileController::class, 'show']);
            Route::put('/profile', [\App\Http\Controllers\Api\V1\ProfileController::class, 'update']);
        });


        //PROTECTED ROUTES — AUTH REQUIRED
      
        Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
            
            // Authentication
            Route::delete('/logout', [AuthController::class, 'logout']);
            Route::get('/user', [AuthController::class, 'user']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
            
            // Change Password (User must be logged in)
            Route::post('/change-password', [AuthController::class, 'changePassword']);
            
            // Phone Verification Routes
            Route::prefix('phone')->group(function () {
                Route::post('/verify/send', [PhoneVerificationController::class, 'send']);
                Route::post('/verify', [PhoneVerificationController::class, 'verify']);
                Route::post('/verify/resend', [PhoneVerificationController::class, 'resend']);
            });
        });
    });

    //Password Reset Routes 
    Route::prefix('password')->group(function () {
        Route::post('/forgot', [PasswordResetController::class, 'forgot'])
                ->middleware('throttle:5,1');

        Route::post('/auth/password/verify', [PasswordResetController::class, 'verifyCode'])
            ->withoutMiddleware([\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class])
            ->middleware('throttle:10,1');

        Route::post('/reset', [PasswordResetController::class, 'reset'])
            ->middleware('throttle:5,1');
    });


});

// Test route (optional)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');