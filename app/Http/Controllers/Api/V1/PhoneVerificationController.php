<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\PhoneVerification\SendVerificationCodeAction;
use App\Actions\PhoneVerification\VerifyPhoneAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\SendVerificationCodeRequest;
use App\Http\Requests\Api\V1\Auth\VerifyPhoneRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class PhoneVerificationController extends Controller
{
    /**
     * Send a verification code to the user's phone.
     */
    public function send(
        SendVerificationCodeRequest $request,
        SendVerificationCodeAction $action
    ): JsonResponse {
        $user = $request->user();

        // Check if phone is already verified
        if ($user->hasVerifiedPhone()) {
            return response()->json([
                'message' => 'Phone number is already verified.',
            ], 400);
        }

        try {
            // Send verification code
            $action->execute($user);

            return response()->json([
                'message' => 'Verification code sent successfully.',
                'expires_in' => 10, // minutes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send verification code.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify the phone using the provided code.
     */
    public function verify(
        VerifyPhoneRequest $request,
        VerifyPhoneAction $action
    ): JsonResponse {
        $user = $request->user();

        // Check if phone is already verified
        if ($user->hasVerifiedPhone()) {
            return response()->json([
                'message' => 'Phone number is already verified.',
            ], 400);
        }

        try {
            $action->execute($user, $request->input('code'));

            return response()->json([
                'message' => 'Phone verified successfully.',
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to verify phone.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resend verification code.
     */
    public function resend(
        SendVerificationCodeRequest $request,
        SendVerificationCodeAction $action
    ): JsonResponse {
        $user = $request->user();

        // Check if phone is already verified
        if ($user->hasVerifiedPhone()) {
            return response()->json([
                'message' => 'Phone number is already verified.',
            ], 400);
        }

        try {
            // Invalidate existing codes and send new one
            $action->execute($user);

            return response()->json([
                'message' => 'Verification code resent successfully.',
                'expires_in' => 10, // minutes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to resend verification code.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}