<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * Display a listing of published products.
     */
    public function index(): JsonResponse
    {
        $products = Product::where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'data' => $products,
        ]);
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product): JsonResponse
    {
        // Policy check
        $this->authorize('view', $product);

        return response()->json([
            'data' => $product,
        ]);
    }
}