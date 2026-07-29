<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyResetCodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * MUST return true for public endpoints
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^[0-9\+\-\s\(\)]+$/',
            ],
            'code' => [
                'required',
                'string',
                'size:6',
                'regex:/^[0-9]+$/',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'phone.required' => 'Phone number is required.',
            'phone.regex' => 'Please enter a valid phone number.',
            'code.required' => 'Verification code is required.',
            'code.size' => 'Verification code must be exactly 6 digits.',
            'code.regex' => 'Verification code must contain only numbers.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge([
                'phone' => $this->normalizePhoneNumber($this->phone),
            ]);
        }
    }

    /**
     * Normalize phone number.
     */
    private function normalizePhoneNumber(string $phone): string
    {
        return preg_replace('/[^0-9+]/', '', $phone);
    }
}