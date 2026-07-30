<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'subtotal' => (float) $this->subtotal,
            'tax' => (float) $this->tax,
            'shipping_cost' => (float) $this->shipping_cost,
            'discount' => (float) $this->discount,
            'total_price' => (float) $this->total_price,
            'total_formatted' => number_format($this->total_price, 2),
            'shipping_address' => $this->shipping_address,
            'notes' => $this->notes,
            'items' => $this->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_title' => $item->product->title ?? null,
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'subtotal' => (float) $item->subtotal,
                ];
            }),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}