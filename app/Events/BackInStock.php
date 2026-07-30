<?php

namespace App\Events;

use App\Models\Product;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BackInStock
{
    use Dispatchable, SerializesModels;

    /**
     * The product that is back in stock.
     */
    public Product $product;

    /**
     * The previous stock quantity.
     */
    public int $oldStock;

    /**
     * The new stock quantity.
     */
    public int $newStock;

    /**
     * Create a new event instance.
     */
    public function __construct(Product $product, int $oldStock, int $newStock)
    {
        $this->product = $product;
        $this->oldStock = $oldStock;
        $this->newStock = $newStock;
    }
}