<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\OrderItem;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'status', 'items']);

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('order_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function($userQuery) use ($request) {
                      $userQuery->where('name', 'like', '%' . $request->search . '%')
                                ->orWhere('email', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // Status filter
        if ($request->has('status_id') && $request->status_id) {
            $query->where('status_id', $request->status_id);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Payment status filter
        if ($request->has('payment_status') && $request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        // Date range filter
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // User filter (for customer view)
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Paginated response
        $orders = $query->latest()->paginate($request->per_page ?? 15);
        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.product_name' => 'required|string',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'shipping' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'payment_method' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'shipping_name' => 'nullable|string',
            'shipping_phone' => 'nullable|string',
            'shipping_email' => 'nullable|email',
            'notes' => 'nullable|string',
        ]);

        // Calculate total
        $total = $validated['subtotal'] 
               + ($validated['tax'] ?? 0) 
               + ($validated['shipping'] ?? 0) 
               - ($validated['discount'] ?? 0);

        // Calculate minimum payment amount for shop orders
        $minPaymentPercentage = \App\Models\Setting::get('min_payment_percentage_shop', 0);
        $minPaymentAmount = ($total * $minPaymentPercentage) / 100;

        // Get default status
        $defaultStatus = OrderStatus::where('is_default', true)->first();
        if (!$defaultStatus) {
            $defaultStatus = OrderStatus::where('name', 'pending')->first();
        }

        $order = Order::create([
            'user_id' => $validated['user_id'],
            'status_id' => $defaultStatus?->id,
            'status' => $defaultStatus?->name ?? 'pending',
            'subtotal' => $validated['subtotal'],
            'tax' => $validated['tax'] ?? 0,
            'shipping' => $validated['shipping'] ?? 0,
            'discount' => $validated['discount'] ?? 0,
            'total' => $total,
            'min_payment_amount' => $minPaymentAmount,
            'currency' => $validated['currency'] ?? 'BDT',
            'payment_method' => $validated['payment_method'] ?? null,
            'shipping_address' => $validated['shipping_address'] ?? null,
            'shipping_name' => $validated['shipping_name'] ?? null,
            'shipping_phone' => $validated['shipping_phone'] ?? null,
            'shipping_email' => $validated['shipping_email'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Create order items
        foreach ($validated['items'] as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'] ?? null,
                'product_name' => $item['product_name'],
                'product_sku' => $item['product_sku'] ?? null,
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'subtotal' => $item['price'] * $item['quantity'],
                'image' => $item['image'] ?? null,
                'variant_data' => $item['variant_data'] ?? null,
            ]);
        }

        // Create initial status history
        if ($defaultStatus) {
            $order->updateStatus($defaultStatus->id, 'Order created', Auth::id());
        }

        $order->load(['user', 'status', 'items']);

        return response()->json($order, 201);
    }

    public function show($id)
    {
        $order = Order::with(['user', 'status', 'items.product', 'statusHistories.changedBy', 'statusHistories.status'])
                     ->findOrFail($id);
        return response()->json($order);
    }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'status_id' => 'sometimes|exists:order_statuses,id',
            'payment_status' => 'sometimes|in:pending,paid,failed,refunded',
            'shipping_address' => 'nullable|string',
            'shipping_name' => 'nullable|string',
            'shipping_phone' => 'nullable|string',
            'shipping_email' => 'nullable|email',
            'notes' => 'nullable|string',
        ]);

        // Handle status update
        if (isset($validated['status_id'])) {
            $newStatus = OrderStatus::findOrFail($validated['status_id']);
            
            // Check if transition is allowed
            if ($order->status && $order->status->canTransitionTo($validated['status_id'])) {
                $order->updateStatus($validated['status_id'], $request->status_note ?? null, Auth::id());
            } else {
                return response()->json([
                    'message' => 'Status transition not allowed'
                ], 422);
            }
        }

        // Update other fields
        $order->update($validated);
        $order->load(['user', 'status', 'items.product', 'statusHistories.changedBy']);

        return response()->json([
            'message' => 'Order updated successfully',
            'order' => $order
        ]);
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        
        // Only allow deletion of pending/cancelled orders
        if (!in_array($order->status, ['pending', 'cancelled'])) {
            return response()->json([
                'message' => 'Cannot delete order with status: ' . $order->status
            ], 422);
        }

        $order->delete();

        return response()->json([
            'message' => 'Order deleted successfully'
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        $request->validate([
            'status_id' => 'required|exists:order_statuses,id',
            'note' => 'nullable|string',
        ]);

        $newStatus = OrderStatus::findOrFail($request->status_id);
        
        // Check if transition is allowed
        if ($order->status && !$order->status->canTransitionTo($request->status_id)) {
            return response()->json([
                'message' => 'Status transition not allowed from ' . $order->status->label . ' to ' . $newStatus->label
            ], 422);
        }

        $order->updateStatus($request->status_id, $request->note, Auth::id());
        $order->load(['user', 'status', 'items.product', 'statusHistories.changedBy']);

        return response()->json([
            'message' => 'Order status updated successfully',
            'order' => $order
        ]);
    }
}
