<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProductCollection;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


 

class ProductController extends Controller
{


    /**
     * List published products.
     */
    public function index(Request $request): JsonResponse
    {
        $products = Product::published()
            ->when($request->has('search'), function ($query) use ($request) {
                $search = $request->input('search');
                return $query->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            })
            ->when($request->has('min_price'), function ($query) use ($request) {
                return $query->where('price', '>=', $request->input('min_price'));
            })
            ->when($request->has('max_price'), function ($query) use ($request) {
                return $query->where('price', '<=', $request->input('max_price'));
            })
            ->when($request->has('in_stock'), function ($query) use ($request) {
                if ($request->boolean('in_stock')) {
                    return $query->where('stock_quantity', '>', 0);
                }
            })
            ->orderBy($request->input('sort_by', 'created_at'), $request->input('sort_order', 'desc'))
            ->paginate($request->input('per_page', 20));

        return (new ProductCollection($products))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Show a single product.
     */
    public function show(Product $product): JsonResponse
    {
        $this->authorize('view', $product);

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(200);
    }
}