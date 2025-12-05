<?php

namespace App\Http\Controllers;

use App\Models\OrderStatus;
use Illuminate\Http\Request;

class OrderStatusController extends Controller
{
    public function index(Request $request)
    {
        $query = OrderStatus::query();

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('label', 'like', '%' . $request->search . '%');
            });
        }

        // Status filter
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Check if requesting all statuses (for dropdowns)
        if ($request->has('all') && $request->all) {
            return response()->json($query->where('is_active', true)->orderBy('sort_order')->orderBy('label')->get());
        }

        // Public endpoint - return active statuses
        if (!$request->user() || !$request->user()->hasRole('Admin')) {
            return response()->json($query->where('is_active', true)->orderBy('sort_order')->orderBy('label')->get());
        }

        // Paginated response
        $statuses = $query->orderBy('sort_order')->orderBy('label')->paginate($request->per_page ?? 15);
        return response()->json($statuses);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:order_statuses,name',
            'label' => 'required|string|max:255',
            'color' => 'required|string|max:7',
            'icon' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'integer',
            'is_default' => 'boolean',
            'is_final' => 'boolean',
            'is_active' => 'boolean',
            'allowed_next_statuses' => 'nullable|array',
        ]);

        // If setting as default, unset other defaults
        if ($validated['is_default'] ?? false) {
            OrderStatus::where('is_default', true)->update(['is_default' => false]);
        }

        $status = OrderStatus::create($validated);
        return response()->json($status, 201);
    }

    public function show($id)
    {
        $status = OrderStatus::withCount('orders')->findOrFail($id);
        return response()->json($status);
    }

    public function update(Request $request, $id)
    {
        $status = OrderStatus::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:order_statuses,name,' . $id,
            'label' => 'sometimes|string|max:255',
            'color' => 'sometimes|string|max:7',
            'icon' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'integer',
            'is_default' => 'boolean',
            'is_final' => 'boolean',
            'is_active' => 'boolean',
            'allowed_next_statuses' => 'nullable|array',
        ]);

        // If setting as default, unset other defaults
        if (isset($validated['is_default']) && $validated['is_default']) {
            OrderStatus::where('is_default', true)->where('id', '!=', $id)->update(['is_default' => false]);
        }

        $status->update($validated);

        return response()->json([
            'message' => 'Order status updated successfully',
            'status' => $status
        ]);
    }

    public function destroy($id)
    {
        $status = OrderStatus::findOrFail($id);
        
        // Check if status has orders
        if ($status->orders()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete status with existing orders'
            ], 422);
        }

        // Don't allow deleting default status
        if ($status->is_default) {
            return response()->json([
                'message' => 'Cannot delete default status'
            ], 422);
        }

        $status->delete();

        return response()->json([
            'message' => 'Order status deleted successfully'
        ]);
    }
}
