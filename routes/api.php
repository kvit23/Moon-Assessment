<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PhoneVerificationController;
use App\Http\Controllers\Api\V1\PasswordResetController;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // 1. PUBLIC ROUTES — NO AUTH REQUIRED
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        
        // Registration & Login
        Route::post('/register', [AuthController::class, 'register'])
            ->middleware('throttle:5,1');
            
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:10,1');
        
        // ─── Password Reset Routes (PUBLIC) ───
        Route::prefix('password')->group(function () {
            Route::post('/forgot', [PasswordResetController::class, 'forgot'])
                ->middleware('throttle:5,1');

        Route::post('/auth/password/verify', [PasswordResetController::class, 'verifyCode'])
            ->withoutMiddleware([\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class])
            ->middleware('throttle:10,1');

            Route::post('/reset', [PasswordResetController::class, 'reset'])
                ->middleware('throttle:5,1');
        });
        
        
        // 2. PROTECTED ROUTES — AUTH REQUIRED
      
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
});

// Test route (optional)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');