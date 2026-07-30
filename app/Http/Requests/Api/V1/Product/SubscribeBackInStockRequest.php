<?php

namespace App\Http\Requests\Api\V1\Product;

use Illuminate\Foundation\Http\FormRequest;

class SubscribeBackInStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        // User must be authenticated
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            // No additional fields needed — user_id and product_id come from route/auth
        ];
    }

    public function messages(): array
    {
        return [];
    }
}