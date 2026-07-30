<?php

namespace App\Models; 

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'sku',
        'price',
        'cost',
        'stock_quantity',
        'reorder_level',
        'status',
        'image',
        'created_by',
        'updated_by',
        'published_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'stock_quantity' => 'integer',
        'reorder_level' => 'integer',
        'published_at' => 'datetime',
    ];

    /**
     * Get the full URL for the product image.
     */
    public function getImageUrlAttribute(): ?string
    {
        if ($this->image) {
            return Storage::url('public/' . $this->image);
        }
        return null;
    }

    /**
     * Scope for published products.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope for in-stock products.
     */
    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }
}