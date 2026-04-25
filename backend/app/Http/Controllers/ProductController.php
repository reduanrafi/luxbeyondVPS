<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        // Only show published products for public API (header search, shop page, etc.)
        // Admin can see all products by passing status parameter
        if (!$request->has('status') || $request->status != 'all') {
            $query->whereIn('status', ['published', 'publish', 'active']);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhere('brand', 'like', '%' . $request->search . '%')
                  ->orWhere('category', 'like', '%' . $request->search . '%');
            });
        }

        // Category filter (including all child categories)
        if ($request->has('category') && $request->category) {
            $categoryNames = \App\Models\Category::getCategoryNamesWithChildren($request->category);
            $query->whereIn('category', $categoryNames);
        }

        // Event filter
        $eventPrice = null;
        $eventPriceType = null;
        $eventDiscountPercentage = null;
        if ($request->has('events') && $request->events) {
            $event = \App\Models\Event::where('slug', $request->events)
                ->where('is_active', true)
                ->first();
            
            if ($event) {
                $now = now();
                // Only filter if event is live or upcoming
                if (($event->start_date <= $now && $event->end_date >= $now) || $event->start_date > $now) {
                    $productIds = $event->products()->pluck('products.id');
                    $query->whereIn('id', $productIds);
                    // Store event pricing info
                    $eventPriceType = $event->price_type ?? 'fixed';
                    if ($eventPriceType === 'fixed' && $event->price) {
                        $eventPrice = $event->price;
                    } elseif ($eventPriceType === 'percentage' && $event->discount_percentage) {
                        $eventDiscountPercentage = $event->discount_percentage;
                    }
                }
            }
        }

        // Brand filter
        if ($request->has('brands') && $request->brands) {
            $brands = is_array($request->brands) 
                ? $request->brands 
                : explode(',', $request->brands);
            $query->whereIn('brand', $brands);
        }

        // Price range filter
        if ($request->has('min_price') && $request->min_price != null) {
            $query->where(function($q) use ($request) {
                $q->where('sellable_price', '>=', $request->min_price)
                  ->orWhere(function($subQ) use ($request) {
                      $subQ->whereNull('sellable_price')
                           ->where('price', '>=', $request->min_price);
                  });
            });
        }

        if ($request->has('max_price') && $request->max_price != null) {
            $query->where(function($q) use ($request) {
                $q->where(function($subQ) use ($request) {
                    $subQ->whereNotNull('sellable_price')
                         ->where('sellable_price', '<=', $request->max_price);
                })->orWhere(function($subQ) use ($request) {
                    $subQ->whereNull('sellable_price')
                         ->where('price', '<=', $request->max_price);
                });
            });
        }

        // Filter by featured
        if ($request->has('featured') && $request->featured) {
            $query->where('is_featured', true);
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

        // Eager load variants for stock calculation
        $query->with('variants');

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');

        $allowedSorts = ['name', 'price', 'created_at', 'stock'];
        if (in_array($sortBy, $allowedSorts)) {
            if ($sortBy === 'price') {
                // Sort by sellable_price if available, otherwise price
                $query->orderByRaw('COALESCE(sellable_price, price) ' . strtoupper($order));
            } else {
                $query->orderBy($sortBy, $order);
            }
        } else {
            $query->latest();
        }

        // Pagination
        $perPage = $request->get('per_page', 12);
        $products = $query->paginate($perPage);

        // Ensure slug is included in response and generate if missing
        // Also apply event price if available
        $products->getCollection()->transform(function ($product) use ($eventPrice, $eventPriceType, $eventDiscountPercentage) {
            // Generate slug if missing
            if (empty($product->slug) && !empty($product->name)) {
                $product->slug = Product::generateUniqueSlug($product->name, $product->id);
                $product->saveQuietly();
            }
            // Ensure slug is always set (even if null, it will be in the response)
            if (empty($product->slug)) {
                $product->slug = null; // Explicitly set to null so it's in JSON
            }
            // Apply event price if set
            if ($eventPriceType === 'fixed' && $eventPrice != null) {
                $product->event_price = $eventPrice;
                $product->original_price = $product->sellable_price ?: $product->price;
            } elseif ($eventPriceType === 'percentage' && $eventDiscountPercentage != null) {
                // Calculate percentage discount
                $originalPrice = floatval($product->sellable_price ?: $product->price);
                $discountAmount = ($originalPrice * floatval($eventDiscountPercentage)) / 100;
                $product->event_price = $originalPrice - $discountAmount;
                $product->original_price = $originalPrice;
            }
            return $product;
        });

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
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'sku' => 'required|string|unique:products,sku',
            'category' => 'required|string',
            'brand' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sellable_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|mimes:jpeg,png,jpg,gif,svg,webp,avif|max:2048',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'has_variants' => 'boolean',
            'is_featured' => 'boolean',
            'variants' => 'nullable|array',
            'gallery' => 'nullable|array',
            'attribute_definitions' => 'nullable|json',
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug']) && !empty($validated['name'])) {
            $validated['slug'] = Product::generateUniqueSlug($validated['name']);
        }

        // Handle attribute definition images
        if ($request->has('attribute_definitions')) {
            $definitions = $request->attribute_definitions;
            if (is_string($definitions)) {
                $definitions = json_decode($definitions, true);
            }

            $validated['attribute_definitions'] = $definitions;
        }

        $product = Product::create($validated);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store("products/$product->slug/images", 'public');
        }

        // Handle Variants
        if ($request->has_variants && !empty($request->variants)) {
            foreach ($request->variants as $index => $variantData) {
                // Handle variant image
                if ($request->hasFile("variant_images.$index")) {
                    $variantData['image'] = $request->file("variant_images.$index")->store("products/$product->slug/variants", 'public');
                }
                $product->variants()->create($variantData);
            }
        }

        // Handle Gallery
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $index => $file) {
                $path = $file->store("products/$product->slug/gallery", 'public');
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

    public function show($slug)
    {
        // Support both slug and ID for backward compatibility
        $product = Product::with(['variants', 'images', 'events'])
            ->where('slug', $slug)
            ->orWhere('id', $slug)
            ->firstOrFail();
        
        // Ensure slug exists
        if (empty($product->slug) && !empty($product->name)) {
            $product->slug = Product::generateUniqueSlug($product->name, $product->id);
            $product->saveQuietly();
        }
        
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
                $product->event_price = $activeEvent->price;
                $product->original_price = $product->sellable_price ?: $product->price;
            } elseif ($priceType === 'percentage' && $activeEvent->discount_percentage) {
                // Calculate percentage discount
                $originalPrice = floatval($product->sellable_price ?: $product->price);
                $discountAmount = ($originalPrice * floatval($activeEvent->discount_percentage)) / 100;
                $product->event_price = $originalPrice - $discountAmount;
                $product->original_price = $originalPrice;
            }
        }
        
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
            'slug' => 'nullable|string|max:255|unique:products,slug,' . $id,
            'sku' => 'sometimes|string|unique:products,sku,' . $id,
            'category' => 'sometimes|string',
            'brand' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'sellable_price' => 'nullable|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'image' => 'nullable|mimes:jpeg,png,jpg,gif,svg,webp,avif|max:2048',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'has_variants' => 'boolean',
            'is_featured' => 'boolean',
            'variants' => 'nullable|array',
            'gallery' => 'nullable|array',
            'existing_gallery_ids' => 'nullable|array',
            'attribute_definitions' => 'nullable|json',
        ]);

        // Auto-generate slug if name changed and slug is empty
        if (isset($validated['name']) && empty($validated['slug'])) {
            $validated['slug'] = Product::generateUniqueSlug($validated['name'], $id);
        }

        if ($request->has('attribute_definitions')) {
            $definitions = $request->attribute_definitions;
            if (is_string($definitions)) {
                $definitions = json_decode($definitions, true);
            }
            $validated['attribute_definitions'] = $definitions;
        }

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
            // Remove existing variants (simplest approach for now, but we lose IDs)
            // Ideally we should update existing ones, but for now we recreate.
            // We need to be careful about deleting old variant images if they are not reused.
            // But since we are storing paths in the new variants, we can just keep the files.
            // Or better: check if the old variant image is being reused.

            $product->variants()->delete();
            
            if ($validated['has_variants'] && !empty($request->variants)) {
                foreach ($request->variants as $index => $variantData) {
                    // Handle variant image
                    if ($request->hasFile("variant_images.$index")) {
                        // New image uploaded
                        $variantData['image'] = $request->file("variant_images.$index")->store('products/variants', 'public');
                    }
                    // If no new image, $variantData['image'] might already be set from the frontend (existing path)
                    // We don't need to do anything else.

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
