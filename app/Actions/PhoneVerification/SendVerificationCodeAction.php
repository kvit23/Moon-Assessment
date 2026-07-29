<?php

namespace App\Actions\PhoneVerification;

use App\Events\PhoneVerificationRequested;
use App\Models\User;
use App\Models\VerificationCode;

class SendVerificationCodeAction
{
    protected GenerateVerificationCodeAction $generateCodeAction;

    public function __construct(GenerateVerificationCodeAction $generateCodeAction)
    {
        $this->generateCodeAction = $generateCodeAction;
    }

    /**
     * Send a verification code to the user's phone.
     *
     * @param User $user
     * @return VerificationCode
     */
    public function execute(User $user): VerificationCode
    {
        // Generate the verification code
        $verificationCode = $this->generateCodeAction->execute($user);

        // Dispatch event for SMS sending
        event(new PhoneVerificationRequested($user, $verificationCode));

        return $verificationCode;
    }
}