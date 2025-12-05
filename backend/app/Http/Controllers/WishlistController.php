<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\WishlistItem;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * Get current user's wishlist items.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $items = WishlistItem::with(['product', 'variant'])
            ->where('user_id', $user->id)
            ->get()
            ->map(function (WishlistItem $item) {
                $product = $item->product;
                $variant = $item->variant;

                return [
                    'wishlist_item_id' => $item->id,
                    'id' => $product->id,
                    'slug' => $product->slug,
                    'name' => $product->name,
                    'image_url' => $variant?->image_url ?? $product->image_url,
                    'sellable_price' => $variant?->price ?? $product->sellable_price ?? $product->price,
                    'price' => $variant?->price ?? $product->sellable_price ?? $product->price,
                    'variant' => $variant ? [
                        'id' => $variant->id,
                        'attributes' => $variant->attributes,
                        'price' => $variant->price,
                        'stock' => $variant->stock,
                        'sku' => $variant->sku,
                        'image' => $variant->image,
                        'image_url' => $variant->image_url,
                    ] : null,
                ];
            });

        return response()->json($items);
    }

    /**
     * Add item to wishlist.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
        ]);

        $product = Product::findOrFail($data['product_id']);
        $variant = isset($data['product_variant_id'])
            ? ProductVariant::findOrFail($data['product_variant_id'])
            : null;

        $wishlistItem = WishlistItem::firstOrCreate([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
        ]);

        return response()->json([
            'message' => 'Added to wishlist',
            'item' => $wishlistItem->fresh(['product', 'variant']),
        ]);
    }

    /**
     * Remove item from wishlist.
     */
    public function destroy(Request $request, WishlistItem $wishlistItem)
    {
        if ($wishlistItem->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $wishlistItem->delete();

        return response()->json([
            'message' => 'Removed from wishlist',
        ]);
    }

    /**
     * Toggle wishlist item by product (helper endpoint).
     */
    public function toggle(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
        ]);

        $existing = WishlistItem::where('user_id', $user->id)
            ->where('product_id', $data['product_id'])
            ->where('product_variant_id', $data['product_variant_id'] ?? null)
            ->first();

        if ($existing) {
            $existing->delete();

            return response()->json([
                'message' => 'Removed from wishlist',
                'toggled' => false,
            ]);
        }

        $item = WishlistItem::create([
            'user_id' => $user->id,
            'product_id' => $data['product_id'],
            'product_variant_id' => $data['product_variant_id'] ?? null,
        ]);

        return response()->json([
            'message' => 'Added to wishlist',
            'toggled' => true,
            'item' => $item->fresh(['product', 'variant']),
        ]);
    }
}


