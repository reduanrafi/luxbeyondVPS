<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $query = Coupon::query();

        // Search
        if ($request->has('search') && $request->search) {
            $query->where('code', 'like', "%{$request->search}%");
        }

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'expired') {
                $query->where('expires_at', '<', now());
            }
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        $perPage = $request->get('per_page', 15);
        $coupons = $query->withCount('usages')->latest()->paginate($perPage);

        return response()->json($coupons);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:coupons,code|max:255',
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1',
            'min_spend' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_private' => 'boolean',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
        ]);

        // Convert code to uppercase
        $validated['code'] = strtoupper($validated['code']);

        DB::beginTransaction();
        try {
            $coupon = Coupon::create($validated);

            // Assign to users if private
            if (!empty($request->user_ids)) {
                $coupon->users()->attach($request->user_ids);
            }

            // Restrict to products
            if (!empty($request->product_ids)) {
                $coupon->products()->attach($request->product_ids);
            }

            DB::commit();

            return response()->json([
                'message' => 'Coupon created successfully',
                'coupon' => $coupon->load(['users', 'products'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create coupon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $coupon = Coupon::with(['users', 'products', 'usages'])->findOrFail($id);
        return response()->json($coupon);
    }

    public function update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        $validated = $request->validate([
            'code' => 'sometimes|string|unique:coupons,code,' . $id . '|max:255',
            'type' => 'sometimes|in:fixed,percent',
            'value' => 'sometimes|numeric|min:0',
            'description' => 'nullable|string',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1',
            'min_spend' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_private' => 'boolean',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
        ]);

        // Convert code to uppercase if provided
        if (isset($validated['code'])) {
            $validated['code'] = strtoupper($validated['code']);
        }

        DB::beginTransaction();
        try {
            $coupon->update($validated);

            // Sync users
            if ($request->has('user_ids')) {
                $coupon->users()->sync($request->user_ids ?? []);
            }

            // Sync products
            if ($request->has('product_ids')) {
                $coupon->products()->sync($request->product_ids ?? []);
            }

            DB::commit();

            return response()->json([
                'message' => 'Coupon updated successfully',
                'coupon' => $coupon->load(['users', 'products'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update coupon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $coupon = Coupon::findOrFail($id);
        
        // Check if coupon has been used
        if ($coupon->usage_count > 0) {
            return response()->json([
                'message' => 'Cannot delete coupon that has been used. Consider deactivating it instead.'
            ], 422);
        }

        $coupon->delete();

        return response()->json([
            'message' => 'Coupon deleted successfully'
        ]);
    }

    // Apply coupon to cart
    public function apply(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
            'product_ids' => 'nullable|array',
        ]);

        // Convert code to uppercase for case-sensitive search
        $code = strtoupper($validated['code']);
        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid coupon code'
            ], 404);
        }

        $userId = auth()->id();

        if (!$coupon->canBeUsedBy($userId)) {
            return response()->json([
                'valid' => false,
                'message' => 'This coupon cannot be used'
            ], 422);
        }

        // Check minimum spend
        if ($coupon->min_spend && $validated['subtotal'] < $coupon->min_spend) {
            return response()->json([
                'valid' => false,
                'message' => "Minimum spend of ৳{$coupon->min_spend} required"
            ], 422);
        }

        // Check product restrictions
        if ($coupon->products()->count() > 0 && !empty($validated['product_ids'])) {
            $allowedProductIds = $coupon->products()->pluck('id')->toArray();
            $hasValidProduct = !empty(array_intersect($validated['product_ids'], $allowedProductIds));
            
            if (!$hasValidProduct) {
                return response()->json([
                    'valid' => false,
                    'message' => 'This coupon is not valid for the selected products'
                ], 422);
            }
        }

        $discount = $coupon->calculateDiscount($validated['subtotal']);

        return response()->json([
            'valid' => true,
            'coupon' => $coupon,
            'discount' => $discount,
            'message' => 'Coupon applied successfully'
        ]);
    }

    /**
     * Get available coupons for the authenticated user
     */
    public function available()
    {
        $user = auth()->user();

        // basic query for active coupons
        // In a real app, you might check if the user has already used the coupon limit, etc.
        $coupons = Coupon::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->where(function ($q) {
                // Public coupons OR coupons assigned to this user
                $q->where('is_private', false)
                    ->orWhereHas('users', function ($q2) {
                    $q2->where('users.id', auth()->id());
                });
            })
            ->latest()
            ->get();

        return response()->json($coupons);
    }
}
