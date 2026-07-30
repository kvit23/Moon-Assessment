<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Product\StoreProductRequest;
use App\Http\Requests\Api\V1\Product\UpdateProductRequest;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductManagementController extends Controller
{
    /**
     * List all products (including drafts).
     */
    public function index(): JsonResponse
    {
        $products = Product::withTrashed()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return ProductResource::collection($products)
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Create a new product.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);

        $validated = $request->validated();

        $product = DB::transaction(function () use ($validated, $request) {
            // Handle image upload
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('products', 'public');
                $validated['image'] = $path;
            }

            // Set created_by
            $validated['created_by'] = $request->user()->id;

            // Set published_at if status is published
            if ($validated['status'] === 'published') {
                $validated['published_at'] = now();
            }

            return Product::create($validated);
        });

        return (new ProductResource($product))
            ->additional(['message' => 'Product created successfully.'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Show a product (admin view).
     */
    public function show(Product $product): JsonResponse
    {
        $this->authorize('view', $product);

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Update a product.
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $validated = $request->validated();

        $product = DB::transaction(function () use ($validated, $request, $product) {
            // Handle new image upload
            if ($request->hasFile('image')) {
                // Delete old image
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }

                $path = $request->file('image')->store('products', 'public');
                $validated['image'] = $path;
            }

            // Handle removing image
            if ($request->input('remove_image', false)) {
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
                $validated['image'] = null;
            }

            // Update slug if title changed
            if (isset($validated['title'])) {
                $validated['slug'] = Str::slug($validated['title']);
            }

            // Update published_at if status changed to published
            if (isset($validated['status']) && 
                $validated['status'] === 'published' && 
                $product->status !== 'published') {
                $validated['published_at'] = now();
            }

            // Set updated_by
            $validated['updated_by'] = $request->user()->id;

            $product->update($validated);

            return $product;
        });

        return (new ProductResource($product))
            ->additional(['message' => 'Product updated successfully.'])
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Delete a product (soft delete).
     */
    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('delete', $product);

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully.',
        ], 200);
    }

    /**
     * Restore a soft-deleted product.
     */
    public function restore(int $id): JsonResponse
    {
        $product = Product::withTrashed()->findOrFail($id);
        
        $this->authorize('restore', $product);

        $product->restore();

        return response()->json([
            'message' => 'Product restored successfully.',
            'data' => new ProductResource($product),
        ], 200);
    }

    /**
     * Permanently delete a product.
     */
    public function forceDelete(int $id): JsonResponse
    {
        $product = Product::withTrashed()->findOrFail($id);
        
        $this->authorize('forceDelete', $product);

        DB::transaction(function () use ($product) {
            // Delete image
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $product->forceDelete();
        });

        return response()->json([
            'message' => 'Product permanently deleted.',
        ], 200);
    }
}