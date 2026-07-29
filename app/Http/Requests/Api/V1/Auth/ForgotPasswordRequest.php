<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
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
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'Phone number is required.',
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