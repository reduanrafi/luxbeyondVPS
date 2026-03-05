<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    /**
     * Display a listing of events
     */
    public function index(Request $request)
    {
        $query = Event::query();

        // Search
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('slug', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by status (only if status is not empty)
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'live') {
                $now = now();
                $query->where('is_active', true)
                    ->where('start_date', '<=', $now)
                    ->where('end_date', '>=', $now);
            } elseif ($request->status === 'upcoming') {
                $now = now();
                $query->where('is_active', true)
                    ->where('start_date', '>', $now);
            } elseif ($request->status === 'expired') {
                $now = now();
                $query->where('end_date', '<', $now);
            }
        }

        // Filter by position (only if position is not empty)
        if ($request->filled('position')) {
            $query->where(function($q) use ($request) {
                $q->where('position', $request->position)
                  ->orWhere('position', 'both');
            });
        }

        // Include products count and relationships
        $query->withCount('products');

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $events = $query->paginate($perPage);

        // Return paginated response in the format expected by frontend
        return response()->json([
            'data' => $events->items(),
            'meta' => [
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total(),
                'from' => $events->firstItem(),
                'to' => $events->lastItem(),
            ],
            'links' => [
                'first' => $events->url(1),
                'last' => $events->url($events->lastPage()),
                'prev' => $events->previousPageUrl(),
                'next' => $events->nextPageUrl(),
            ]
        ]);
    }

    /**
     * Store a newly created event
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:events,slug',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'banner_image' => 'required_without:event|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'bg_color' => 'nullable|string|max:7',
            'position' => 'required|in:hero,sidebar,both',
            'url' => 'nullable|string|max:255',
            'show_button' => 'nullable|boolean',
            'button_text' => 'nullable|string|max:255',
            'button_color' => 'nullable|string|max:7',
            'price' => 'nullable|numeric|min:0',
            'price_type' => 'nullable|in:fixed,percentage',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_active' => 'nullable|boolean',
            'send_notification' => 'nullable|boolean',
            'sort_order' => 'integer',
            'priority' => 'integer',
            'meta' => 'nullable|array',
            'products' => 'nullable|array',
            'products.*' => 'exists:products,id',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('events', 'public');
        }

        if ($request->hasFile('banner_image')) {
            $validated['banner_image'] = $request->file('banner_image')->store('events/banners', 'public');
        }

        // Convert boolean strings to actual booleans
        if (isset($validated['is_active'])) {
            $validated['is_active'] = filter_var($validated['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
        }
        if (isset($validated['show_button'])) {
            $validated['show_button'] = filter_var($validated['show_button'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
        }

        // Extract products before creating event
        $products = $validated['products'] ?? [];
        unset($validated['products']);

        // Create event
        $event = Event::create($validated);

        // Send notification if enabled (when event is created)
        if ($event->send_notification) {
            dispatch(new \App\Jobs\SendEventNotificationJob($event, 'created'))->delay(now()->addSeconds(2));
        }

        // Attach products
        if (!empty($products)) {
            $syncData = [];
            foreach ($products as $index => $productId) {
                $syncData[$productId] = ['sort_order' => $index];
            }
            $event->products()->sync($syncData);
        }

        // Refresh to get accessors
        $event->refresh();

        // Load relationships
        $event->load('products');

        return response()->json([
            'message' => 'Event created successfully',
            'event' => $event
        ], 201);
    }

    /**
     * Display the specified event
     */
    public function show($id)
    {
        $event = Event::with('products')->findOrFail($id);
        return response()->json($event);
    }

    /**
     * Update the specified event
     */
    public function update(Request $request, $id)
    {

        logger($request->all());

        $event = Event::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('events')->ignore($id)],
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'bg_color' => 'nullable|string|max:7',
            'position' => 'sometimes|in:hero,sidebar,both',
            'url' => 'nullable|string|max:255',
            'show_button' => 'nullable|boolean',
            'button_text' => 'nullable|string|max:255',
            'button_color' => 'nullable|string|max:7',
            'price' => 'nullable|numeric|min:0',
            'price_type' => 'sometimes|in:fixed,percentage',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'is_active' => 'nullable|boolean',
            'send_notification' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'priority' => 'nullable|integer',
            'meta' => 'nullable|array',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($event->image && Storage::disk('public')->exists($event->image)) {
                Storage::disk('public')->delete($event->image);
            }
            $validated['image'] = $request->file('image')->store('events', 'public');
        }

        if ($request->hasFile('banner_image')) {
            // Delete old banner
            if ($event->banner_image && Storage::disk('public')->exists($event->banner_image)) {
                Storage::disk('public')->delete($event->banner_image);
            }
            $validated['banner_image'] = $request->file('banner_image')->store('events/banners', 'public');
        }

        // Convert boolean strings to actual booleans
        if (isset($validated['is_active'])) {
            $validated['is_active'] = filter_var($validated['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $event->is_active;
        }
        if (isset($validated['show_button'])) {
            $validated['show_button'] = filter_var($validated['show_button'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $event->show_button ?? true;
        }
        if (isset($validated['send_notification'])) {
            $validated['send_notification'] = filter_var($validated['send_notification'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $event->send_notification ?? false;
        }

        // Extract products - FormData sends products[] as array
        // When using FormData with products[], Laravel parses it as 'products' key
        $products = [];

        // Get all request data to check for products
        $allInput = $request->all();

        // Check if products are sent in the request (FormData sends products[] as 'products' array)
        if (isset($allInput['products']) && is_array($allInput['products'])) {
            $products = $allInput['products'];
        } elseif ($request->has('products')) {
            $products = $request->input('products', []);
            if (is_string($products)) {
                $products = json_decode($products, true) ?? [];
            }
        }

        // Log for debugging
        \Log::debug('Products received in update', [
            'has_products' => $request->has('products'),
            'products_input' => $request->input('products'),
            'all_input_has_products' => isset($allInput['products']),
            'products_array' => $products,
            'products_count' => count($products)
        ]);

        // Filter out any non-numeric values and ensure they're integers
        $products = array_values(array_filter(array_map('intval', $products), function($id) {
            return $id > 0;
        }));

        // Update event
        $event->update($validated);
        $event->refresh();

        // Note: Event start notifications are handled by the scheduled command
        // which runs daily and checks for events starting today

        // Sync products (always sync, even if empty array to clear products)
        $syncData = [];
        foreach ($products as $index => $productId) {
            if (is_numeric($productId)) {
                $syncData[$productId] = ['sort_order' => $index];
            }
        }
        $event->products()->sync($syncData);

        // Refresh to get accessors
        $event->refresh();

        // Load relationships
        $event->load('products');

        return response()->json([
            'message' => 'Event updated successfully',
            'event' => $event
        ]);
    }

    /**
     * Remove the specified event
     */
    public function destroy($id)
    {
        $event = Event::findOrFail($id);

        // Delete images
        if ($event->image && Storage::disk('public')->exists($event->image)) {
            Storage::disk('public')->delete($event->image);
        }
        if ($event->banner_image && Storage::disk('public')->exists($event->banner_image)) {
            Storage::disk('public')->delete($event->banner_image);
        }

        $event->delete();

        return response()->json(['message' => 'Event deleted successfully']);
    }

    /**
     * Get all products for event assignment
     */
    public function getProducts(Request $request)
    {
        $query = Product::query();

        // Only get products without variants (has_variants = false)
        $query->where('has_variants', false);

        // Only get published products
        $query->where('status', 'published');

        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->select('id', 'name', 'image', 'price', 'sellable_price')
            ->limit(100)
            ->get();

        // image_url is automatically included via the Product model's $appends array

        return response()->json($products);
    }
}

