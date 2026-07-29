<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PhoneVerificationController;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        // Public routes
        Route::post('/register', [AuthController::class, 'register'])
            ->middleware('throttle:5,1');
            
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:10,1');
        
        // Protected routes
        Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
            // Simple, secure logout
            Route::delete('/logout', [AuthController::class, 'logout']);
            
            // User management
            Route::get('/user', [AuthController::class, 'user']);
            Route::post('/refresh', [AuthController::class, 'refresh']);

            //Phone Verification Routes
            Route::prefix('phone')->group(function () {
                Route::post('/verify/send', [PhoneVerificationController::class, 'send']);
                Route::post('/verify', [PhoneVerificationController::class, 'verify']);
                Route::post('/verify/resend', [PhoneVerificationController::class, 'resend']);
            });

        });
    });
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
