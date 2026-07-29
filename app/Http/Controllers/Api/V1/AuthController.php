<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Auth\CreateUserAction;
use App\Events\UserRegistered;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ChangePasswordRequest;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request, CreateUserAction $createUserAction): JsonResponse
    {
        try {
            $user = $createUserAction->execute($request->validated());
            event(new UserRegistered($user));

            return response()->json([
                'message' => 'User registered successfully.',
                'data' => new UserResource($user),
                'token' => $user->token,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Registration failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login user and create token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::where('phone', $validated['phone'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'phone' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'phone' => ['Your account has been deactivated.'],
            ]);
        }

        // Update last login
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        // Create token
        $deviceName = $validated['device_name'] ?? 'api';
        $token = $user->createToken($deviceName, ['*']);

        return response()->json([
            'message' => 'Login successful.',
            'data' => new UserResource($user),
            'token' => $token->plainTextToken,
        ]);
    }

    /**
     * Logout user (revoke current token).
     * Secure: No token IDs exposed, just successful message.
     */
    public function logout(Request $request): JsonResponse
    {
        // Revoke the current access token
        $request->user()->currentAccessToken()->delete();

        // Generic success message - no exposure of token details
        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Get the authenticated user.
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($request->user()),
        ]);
    }

    /**
     * Refresh token (create new token, revoke old).
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Revoke old token
        $request->user()->currentAccessToken()->delete();
        
        // Create new token
        $deviceName = $request->input('device_name', 'api');
        $token = $user->createToken($deviceName, ['*']);

        return response()->json([
            'message' => 'Token refreshed successfully.',
            'token' => $token->plainTextToken,
        ]);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();
        
        // Update password
        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);

        // Optional: Revoke all tokens except current one
        // $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();

        return response()->json([
            'message' => 'Password changed successfully.',
        ]);
    }

}