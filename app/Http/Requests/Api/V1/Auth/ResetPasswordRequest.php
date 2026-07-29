<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public endpoint
    }

    public function rules(): array
    {
        return [
            'phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^[0-9\+\-\s\(\)]+$/',
            ],
            'reset_token' => [
                'required',
                'string',
                'min:60',
                'max:60',
            ],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
            'password_confirmation' => [
                'required',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'Phone number is required.',
            'phone.regex' => 'Please enter a valid phone number.',
            'reset_token.required' => 'Reset token is required.',
            'reset_token.min' => 'Invalid reset token.',
            'password.required' => 'New password is required.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.uncompromised' => 'This password appears in a data breach. Please choose a different one.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge([
                'phone' => $this->normalizePhoneNumber($this->phone),
            ]);
        }
    }

    private function normalizePhoneNumber(string $phone): string
    {
        return preg_replace('/[^0-9+]/', '', $phone);
    }
}