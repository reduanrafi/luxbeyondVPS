<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'in_stock') {
                $query->where('stock', '>', 10);
            } elseif ($request->status === 'low_stock') {
                $query->whereBetween('stock', [1, 10]);
            } elseif ($request->status === 'out_of_stock') {
                $query->where('stock', 0);
            }
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $products = $query->latest()->paginate($perPage);

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku',
            'category' => 'required|string',
            'brand' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|string',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'has_variants' => 'boolean',
            'variants' => 'nullable|array',
            'gallery' => 'nullable|array',
        ]);

        $product = Product::create($validated);

        // Handle Variants
        if ($request->has_variants && !empty($request->variants)) {
            foreach ($request->variants as $variantData) {
                $product->variants()->create($variantData);
            }
        }

        // Handle Gallery
        if (!empty($request->gallery)) {
            foreach ($request->gallery as $index => $item) {
                $product->images()->create([
                    'path' => $item['path'],
                    'type' => $item['type'] ?? 'image',
                    'sort_order' => $index,
                ]);
            }
        }

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product->load(['variants', 'images'])
        ], 201);
    }

    public function show($id)
    {
        $product = Product::with(['variants', 'images'])->findOrFail($id);
        return response()->json($product);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'sku' => 'sometimes|string|unique:products,sku,' . $id,
            'category' => 'sometimes|string',
            'brand' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'image' => 'nullable|string',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'has_variants' => 'boolean',
            'variants' => 'nullable|array',
            'gallery' => 'nullable|array',
        ]);

        $product->update($validated);

        // Update Variants
        if (isset($validated['has_variants'])) {
            // Remove existing variants
            $product->variants()->delete();
            
            if ($validated['has_variants'] && !empty($request->variants)) {
                foreach ($request->variants as $variantData) {
                    $product->variants()->create($variantData);
                }
            }
        }

        // Update Gallery
        if (isset($request->gallery)) {
            // Remove existing images
            $product->images()->delete();
            
            foreach ($request->gallery as $index => $item) {
                $product->images()->create([
                    'path' => $item['path'],
                    'type' => $item['type'] ?? 'image',
                    'sort_order' => $index,
                ]);
            }
        }

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product->load(['variants', 'images'])
        ]);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully'
        ]);
    }
}
