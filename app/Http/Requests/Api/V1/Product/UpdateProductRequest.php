<?php


namespace App\Http\Requests\Api\V1\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product');

        return [
            'title' => ['sometimes', 'string', 'max:255'],
            // 'slug' => ['sometimes', 'string', 'unique:products,slug,' . $productId], // ❌ REMOVE THIS
            'description' => ['nullable', 'string'],
            'sku' => ['sometimes', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($productId)],
            'price' => ['sometimes', 'numeric', 'min:0', 'max:99999999.99'],
            'cost' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'stock_quantity' => ['sometimes', 'integer', 'min:0'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
            'status' => ['sometimes', Rule::in(['draft', 'published', 'archived'])],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'remove_image' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'sku.unique' => 'This SKU is already in use.',
            'price.numeric' => 'Price must be a valid number.',
            'stock_quantity.integer' => 'Stock quantity must be a whole number.',
            'status.in' => 'Status must be draft, published, or archived.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a JPEG, PNG, or JPG file.',
            'image.max' => 'The image size must not exceed 2MB.',
        ];
    }

}