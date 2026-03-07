<?php

namespace App\Observers;

use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\Product;

class OrderItemObserver
{
    
    public function created(OrderItem $item)
    {
        $this->adjustStock($item, $item->quantity);
    }

    public function updated(OrderItem $item)
    {
        if ($item->isDirty('quantity')) {
            $difference = $item->quantity - $item->getOriginal('quantity');
            $this->adjustStock($item, $difference);
        }
    }

    public function deleted(OrderItem $item)
    {
        // Restore stock by passing a negative amount to decrement (which increments)
        $this->adjustStock($item, -$item->quantity);
    }

    protected function adjustStock(OrderItem $item, $amount)
    {
        if ($amount == 0) return;

        // 1. Get the parent product
        $product = Product::find($item->product_id);
        if (!$product) return;

        // 2. Determine if we use Variant Stock or Main Product Stock
        if ($product->has_variants) {
            $variantData = is_string($item->variant_data) 
                ? json_decode($item->variant_data, true) 
                : $item->variant_data;

            $variantId = $variantData['id'] ?? null;

            if ($variantId) {
                ProductVariant::where('id', $variantId)->decrement('stock', $amount);
            }
        } else {
            // Product has no definitions/variants, use main stock
            $product->decrement('stock', $amount);
        }
    }

    /**
     * Handle the OrderItem "restored" event.
     */
    public function restored(OrderItem $orderItem): void
    {
        //
    }

    /**
     * Handle the OrderItem "force deleted" event.
     */
    public function forceDeleted(OrderItem $orderItem): void
    {
        //
    }
}
