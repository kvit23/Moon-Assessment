<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\PasswordReset\RequestPasswordResetAction;
use App\Actions\PasswordReset\ResetPasswordAction;
use App\Actions\PasswordReset\VerifyResetCodeAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use App\Http\Requests\Api\V1\Auth\VerifyResetCodeRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    /**
     * Request a password reset code.
     */
    public function forgot(
        ForgotPasswordRequest $request,
        RequestPasswordResetAction $action
    ): JsonResponse {
        try {
            $result = $action->execute($request->input('phone'));

            return response()->json($result);
        } catch (ValidationException $e) {
            // For security, return generic message if user not found
            return response()->json([
                'message' => 'If the phone number exists, a reset code will be sent.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send password reset code.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function verifyCode(VerifyResetCodeRequest $request, VerifyResetCodeAction $action): JsonResponse
    {
        // Log EVERYTHING
        \Log::info('VERIFY CODE ENDPOINT HIT', [
            'method' => request()->method(),
            'path' => request()->path(),
            'headers' => request()->headers->all(),
            'user' => request()->user(), // Will be null if not authenticated
            'is_authenticated' => request()->user() ? 'YES' : 'NO',
        ]);

        try {
            $result = $action->execute(
                $request->input('phone'),
                $request->input('code')
            );

            return response()->json($result);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('VERIFY CODE ERROR', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'message' => 'Failed to verify reset code.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    // /**
    //  * Verify the reset code and get a reset token.
    //  */
    // public function verifyCode(
    //     VerifyResetCodeRequest $request,
    //     VerifyResetCodeAction $action
    // ): JsonResponse {
    //     try {
    //         $result = $action->execute(
    //             $request->input('phone'),
    //             $request->input('code')
    //         );

    //         return response()->json($result);
    //     } catch (ValidationException $e) {
    //         throw $e;
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => 'Failed to verify reset code.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    /**
     * Reset the password using a valid reset token.
     */
    public function reset(
        ResetPasswordRequest $request,
        ResetPasswordAction $action
    ): JsonResponse {
        try {
            $action->execute(
                $request->input('phone'),
                $request->input('reset_token'),
                $request->input('password')
            );

            return response()->json([
                'message' => 'Password reset successfully. Please login with your new password.',
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to reset password.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}