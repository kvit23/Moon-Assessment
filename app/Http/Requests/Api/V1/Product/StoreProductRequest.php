<?php

namespace App\Http\Requests\Api\V1\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            // 'slug' => ['nullable', 'string', 'unique:products,slug'], // ❌ REMOVE THIS
            'description' => ['nullable', 'string'],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'cost' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Product title is required.',
            'sku.required' => 'SKU is required.',
            'sku.unique' => 'This SKU is already in use.',
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a valid number.',
            'stock_quantity.required' => 'Stock quantity is required.',
            'stock_quantity.integer' => 'Stock quantity must be a whole number.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be draft, published, or archived.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a JPEG, PNG, or JPG file.',
            'image.max' => 'The image size must not exceed 2MB.',
        ];
    }

}