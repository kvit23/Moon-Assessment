<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Enums\UserRoleEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendVerificationCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // User must be authenticated (handled by middleware)
    }

    public function rules(): array
    {
        return [
            'phone' => [
                'sometimes',
                'string',
                'max:20',
                'regex:/^[0-9\+\-\s\(\)]+$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Please enter a valid phone number.',
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