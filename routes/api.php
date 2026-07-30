<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PhoneVerificationController;
use App\Http\Controllers\Api\V1\PasswordResetController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\Admin\ProductManagementController;
use App\Http\Controllers\Api\V1\Admin\OrderManagementController;
use App\Http\Controllers\Api\V1\BackInStockController;

Route::prefix('v1')->group(function () {
    
    Route::prefix('auth')->group(function () {
        
        //PUBLIC ROUTES — No Authentication Required
        
        
        // Registration & Login
        Route::post('/register', [AuthController::class, 'register'])
            ->middleware('throttle:5,1');
            
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:10,1');
        
        //Password Reset  
        Route::prefix('password')->group(function () {
            Route::post('/forgot', [PasswordResetController::class, 'forgot'])
                ->middleware('throttle:5,1');
                
            Route::post('/verify', [PasswordResetController::class, 'verifyCode'])
                ->middleware('throttle:10,1');
                
            Route::post('/reset', [PasswordResetController::class, 'reset'])
                ->middleware('throttle:5,1');
        });
        
         
        // 2. PUBLIC PRODUCT ROUTES (No Auth Required)
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/{product}', [ProductController::class, 'show']);
        
        
        //PROTECTED ROUTES — Authentication Required
        
        Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
            
            // Authentication
            Route::delete('/logout', [AuthController::class, 'logout']);
            Route::get('/user', [AuthController::class, 'user']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
            Route::post('/change-password', [AuthController::class, 'changePassword']);
            
            // Phone Verification
            Route::prefix('phone')->group(function () {
                Route::post('/verify/send', [PhoneVerificationController::class, 'send']);
                Route::post('/verify', [PhoneVerificationController::class, 'verify']);
                Route::post('/verify/resend', [PhoneVerificationController::class, 'resend']);
            });
            
            // User's own orders
            Route::prefix('orders')->group(function () {
                Route::get('/', [OrderController::class, 'index']);
                Route::get('/{order}', [OrderController::class, 'show']);
                Route::post('/', [OrderController::class, 'store']);
                Route::post('/{order}/cancel', [OrderController::class, 'cancel']);
            });
            
            //orders
                Route::get('/orders', [OrderController::class, 'index']);
                Route::get('/orders/{order}', [OrderController::class, 'show']);
                Route::post('/orders', [OrderController::class, 'store']);


            
            // Profile
            Route::prefix('profile')->group(function () {
                Route::get('/', [ProfileController::class, 'show']);
                Route::put('/', [ProfileController::class, 'update']);
            });
        });
    });
    
 
    //ADMIN ROUTES (Separate from 'auth' prefix)
   
    Route::prefix('admin')
        ->middleware(['auth:sanctum', 'admin'])
        ->group(function () {
            
            //Product Management 
            Route::get('/products', [ProductManagementController::class, 'index']);
            Route::post('/products', [ProductManagementController::class, 'store']);
            Route::get('/products/{product}', [ProductManagementController::class, 'show']);
            Route::put('/products/{product}', [ProductManagementController::class, 'update']);
            Route::delete('/products/{product}', [ProductManagementController::class, 'destroy']);
            Route::post('/products/{id}/restore', [ProductManagementController::class, 'restore']);
            Route::delete('/products/{id}/force', [ProductManagementController::class, 'forceDelete']);
            
            // ─── Order Management ───
            Route::get('/orders', [OrderManagementController::class, 'index']);
            Route::get('/orders/{order}', [OrderManagementController::class, 'show']);
            Route::put('/orders/{order}/status', [OrderManagementController::class, 'updateStatus']);
            Route::post('/orders/{order}/cancel', [OrderManagementController::class, 'cancel']);
            Route::get('/orders/statistics', [OrderManagementController::class, 'statistics']);
    });

    // Back-in-stock subscription
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/products/{product}/subscribe', [BackInStockController::class, 'subscribe']);
        Route::delete('/products/{product}/unsubscribe', [BackInStockController::class, 'unsubscribe']);
        Route::get('/subscriptions', [BackInStockController::class, 'mySubscriptions']);
    });

});

// Test route (optional)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');