<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'sku' => $this->sku,
            'price' => (float) $this->price,
            'price_formatted' => number_format($this->price, 2),
            'cost' => $this->cost ? (float) $this->cost : null,
            'stock_quantity' => $this->stock_quantity,
            'reorder_level' => $this->reorder_level,
            'status' => $this->status,
            'image_url' => $this->image_url,
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];

        if ($request->user()) {
            $data['permissions'] = [
                'can_update' => $request->user()->can('update', $this->resource),
                'can_delete' => $request->user()->can('delete', $this->resource),
            ];
        }

        return $data;
    }
}