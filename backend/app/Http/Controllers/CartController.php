<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Event;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Get current user's cart items.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $items = CartItem::with(['product', 'variant'])
            ->where('user_id', $user->id)
            ->get()
            ->map(function (CartItem $item) {
                $product = $item->product;
                $variant = $item->variant;

                // Get base price logic
                $productBasePrice = $product->sellable_price ?? $product->price;
                $variantPrice = $variant ? $variant->price : 0;

                // Original price is Product Original (if exists) or Product Base + Variant Price
                $productOriginalPrice = $product->sellable_price ? $product->original_price : $product->price;
                $originalPrice = $productOriginalPrice + $variantPrice;

                $finalProductPrice = $productBasePrice;

                // Check if product is in any active event with price
                $now = now();
                $activeEvent = $product->events()
                    ->where('is_active', true)
                    ->where('start_date', '<=', $now)
                    ->where('end_date', '>=', $now)
                    ->where(function($q) {
                        $q->whereNotNull('price')
                          ->orWhereNotNull('discount_percentage');
                    })
                    ->orderBy('priority', 'desc')
                    ->first();

                if ($activeEvent) {
                    $priceType = $activeEvent->price_type ?? 'fixed';
                    if ($priceType === 'fixed' && $activeEvent->price) {
                        $finalProductPrice = $activeEvent->price;
                    } elseif ($priceType === 'percentage' && $activeEvent->discount_percentage) {
                        // Calculate percentage discount
                        $discountAmount = ($productBasePrice * floatval($activeEvent->discount_percentage)) / 100;
                        $finalProductPrice = $productBasePrice - $discountAmount;
                    }
                }

                $finalPrice = $finalProductPrice + $variantPrice;

                return [
                    'cart_item_id' => $item->id,
                    'id' => $product->id,
                    'slug' => $product->slug,
                    'name' => $product->name,
                    'image_url' => $variant?->image_url ?? $product->image_url,
                    'sellable_price' => $finalPrice,
                    'price' => $finalPrice,
                    'original_price' => $originalPrice,
                    'total_stock' => $variant ? $variant->stock : $product->stock,
                    'weight' => $variant ? $variant->weight : $product->weight,
                    'event_price' => $activeEvent ? $finalPrice : null,
                    'quantity' => $item->quantity,
                    'variant' => $variant ? [
                        'id' => $variant->id,
                        'attributes' => $variant->attributes,
                        'price' => $variant->price,
                        'stock' => $variant->stock,
                        'weight' => $variant->weight,
                        'sku' => $variant->sku,
                        'image' => $variant->image,
                        'image_url' => $variant->image_url,
                    ] : null,
                ];
            });

        return response()->json($items);
    }

    /**
     * Add or update an item in the cart.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($data['product_id']);
        $variant = isset($data['product_variant_id'])
            ? ProductVariant::findOrFail($data['product_variant_id'])
            : null;

        // Get base price logic
        $productBasePrice = $product->sellable_price ?? $product->price;
        $variantPrice = $variant ? $variant->price : 0;

        $finalProductPrice = $productBasePrice;

        // Check if product is in any active event with price
        $now = now();
        $activeEvent = $product->events()
            ->where('is_active', true)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->where(function($q) {
                $q->whereNotNull('price')
                  ->orWhereNotNull('discount_percentage');
            })
            ->orderBy('priority', 'desc')
            ->first();

        if ($activeEvent) {
            $priceType = $activeEvent->price_type ?? 'fixed';
            if ($priceType === 'fixed' && $activeEvent->price) {
                $finalProductPrice = $activeEvent->price;
            } elseif ($priceType === 'percentage' && $activeEvent->discount_percentage) {
                // Calculate percentage discount
                $discountAmount = ($productBasePrice * floatval($activeEvent->discount_percentage)) / 100;
                $finalProductPrice = $productBasePrice - $discountAmount;
            }
        }

        $finalPrice = $finalProductPrice + $variantPrice;

        $cartItem = CartItem::updateOrCreate(
            [
                'user_id' => $user->id,
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
            ],
            [
                'quantity' => $data['quantity'],
                'price' => $finalPrice,
            ]
        );

        return response()->json([
            'message' => 'Cart updated successfully',
            'item' => $cartItem->fresh(['product', 'variant']),
        ]);
    }

    /**
     * Update quantity for a cart item.
     */
    public function update(Request $request, CartItem $cartItem)
    {
        if ($cartItem->user_id != $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem->update([
            'quantity' => $data['quantity'],
        ]);

        return response()->json([
            'message' => 'Cart item updated',
            'item' => $cartItem->fresh(['product', 'variant']),
        ]);
    }

    /**
     * Remove item from cart.
     */
    public function destroy(Request $request, CartItem $cartItem)
    {
        if ($cartItem->user_id != $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $cartItem->delete();

        return response()->json([
            'message' => 'Item removed from cart',
        ]);
    }

    /**
     * Clear cart for current user.
     */
    public function clear(Request $request)
    {
        $user = $request->user();

        CartItem::where('user_id', $user->id)->delete();

        return response()->json([
            'message' => 'Cart cleared',
        ]);
    }
}


