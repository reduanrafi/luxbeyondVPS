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
        // Decode variants if sent as JSON string
        if ($request->has('variants') && is_string($request->variants)) {
            $request->merge(['variants' => json_decode($request->variants, true)]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku',
            'category' => 'required|string',
            'brand' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'has_variants' => 'boolean',
            'variants' => 'nullable|array',
            'gallery' => 'nullable|array',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products/images', 'public');
        }

        $product = Product::create($validated);

        // Handle Variants
        if ($request->has_variants && !empty($request->variants)) {
            foreach ($request->variants as $variantData) {
                $product->variants()->create($variantData);
            }
        }

        // Handle Gallery
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $index => $file) {
                $path = $file->store('products/gallery', 'public');
                $type = str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image';
                $product->images()->create([
                    'path' => $path,
                    'type' => $type,
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

        // Decode variants if sent as JSON string
        if ($request->has('variants') && is_string($request->variants)) {
            $request->merge(['variants' => json_decode($request->variants, true)]);
        }
        // Decode existing_gallery_ids if sent as JSON string
        if ($request->has('existing_gallery_ids') && is_string($request->existing_gallery_ids)) {
            $request->merge(['existing_gallery_ids' => json_decode($request->existing_gallery_ids, true)]);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'sku' => 'sometimes|string|unique:products,sku,' . $id,
            'category' => 'sometimes|string',
            'brand' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'image' => 'nullable',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'has_variants' => 'boolean',
            'variants' => 'nullable|array',
            'gallery' => 'nullable|array',
            'existing_gallery_ids' => 'nullable|array',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products/images', 'public');
        }

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
        // 1. Handle existing images deletion
        if ($request->has('existing_gallery_ids')) {
            $existingIds = $request->input('existing_gallery_ids', []);
            $imagesToDelete = $product->images()->whereNotIn('id', $existingIds)->get();
            foreach ($imagesToDelete as $img) {
                if (Storage::disk('public')->exists($img->path)) {
                    Storage::disk('public')->delete($img->path);
                }
                $img->delete();
            }
        } elseif ($request->hasFile('gallery')) {
            // If new gallery files are uploaded but no existing_gallery_ids sent, 
            // and we are not explicitly saying "keep nothing", 
            // it's ambiguous. But usually if I upload new files I might want to keep old ones.
            // However, if I want to clear gallery, I should send empty existing_gallery_ids.
            // Let's assume if existing_gallery_ids is NOT present, we keep all existing images.
        }

        // 2. Handle new images
        if ($request->hasFile('gallery')) {
            $currentMaxSortOrder = $product->images()->max('sort_order') ?? -1;
            foreach ($request->file('gallery') as $index => $file) {
                $path = $file->store('products/gallery', 'public');
                $type = str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image';
                $product->images()->create([
                    'path' => $path,
                    'type' => $type,
                    'sort_order' => $currentMaxSortOrder + 1 + $index,
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
