<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\OrderStatus;
use App\Models\OrderItem;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\OrderPendingNotification;
use App\Notifications\NewOrderNotification;
use App\Notifications\OrderStatusUpdated;

class OrderController extends Controller
{
    public function stats()
    {
        $totalOrders = Order::count();

        $stats = OrderStatus::where('is_active', true)
            ->withCount('orders')
            ->orderBy('sort_order')
            ->get()
            ->map(function ($status) {
                return [
                    'id' => $status->id,
                    'label' => $status->label,
                    'value' => $status->orders_count,
                    'color' => $status->color,
                    'icon' => $status->icon,
                ];
            });

        return response()->json([
            'total' => $totalOrders,
            'statuses' => $stats
        ]);
    }

    public function index(Request $request)
    {
        $query = Order::with(['user', 'status', 'event', 'coupon', 'items']);

        // Check if this is an admin request (admin routes have /admin prefix)
        $isAdminRequest = $request->is('api/admin/*');

        // For customer requests, automatically filter by authenticated user
        if (!$isAdminRequest && $request->user()) {
            $query->where('user_id', $request->user()->id);
        }

        // User filter (for admin view or explicit filtering)
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

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

        // Event filter
        if ($request->has('event_id') && $request->event_id) {
            $query->where('event_id', $request->event_id);
        }

        // Date range filter
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Paginated response
        $orders = $query->latest()->paginate($request->per_page ?? 15);
        return response()->json($orders);
    }

    public function store(Request $request)
    {
        // Decode items if sent as JSON string (common in multipart/form-data)
        if ($request->has('items') && is_string($request->items)) {
            $request->merge(['items' => json_decode($request->items, true)]);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'event_id' => 'nullable|exists:events,id',
            'coupon_id' => 'nullable|exists:coupons,id',
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
            'payment_slip' => 'nullable|file|image|max:5120', // 5MB max
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
            'event_id' => $validated['event_id'] ?? null,
            'coupon_id' => $validated['coupon_id'] ?? null,
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

        // Handle payment slip upload for bank transfer
        $paymentSlipPath = null;
        if ($request->hasFile('payment_slip') && $validated['payment_method'] === 'bank_transfer') {
            $file = $request->file('payment_slip');
            $filename = 'payment_slips/' . $order->order_number . '_' . time() . '.' . $file->getClientOriginalExtension();
            $paymentSlipPath = $file->storeAs('public', $filename);
            $paymentSlipPath = $filename; // Store relative path

            // Create payment record with pending status for bank transfer
            OrderPayment::create([
                'order_id' => $order->id,
                'payment_method' => 'bank_transfer',
                'amount' => $total,
                'payment_slip' => $paymentSlipPath,
                'status' => 'pending', // Pending admin verification
            ]);

            // Update order to pending payment status
            $order->update([
                'payment_status' => 'pending',
                'payment_slip' => $paymentSlipPath,
            ]);
        }

        $order->load(['user', 'status', 'event', 'coupon', 'items', 'payments']);

        // Send notification based on payment method
        if ($validated['payment_method'] === 'bank_transfer') {
            $order->user->notify(new OrderPendingNotification($order));
        } else {
            $order->user->notify(new OrderPlacedNotification($order));
        }

        // Notify Admins
        try {
            $admins = \App\Models\User::role('Admin')->get();
            if ($admins->count() > 0) {
                \Illuminate\Support\Facades\Notification::send($admins, new NewOrderNotification($order));
            }
        } catch (\Exception $e) {
            // Log error
        }

        return response()->json($order, 201);
    }

    public function show(Request $request, $id)
    {
        // Support both ID and order_number lookup
        $order = Order::with(['user', 'status', 'event', 'coupon', 'items.product', 'statusHistories.changedBy', 'statusHistories.status', 'payments'])
                     ->where(function($query) use ($id) {
                         $query->where('id', $id)
                               ->orWhere('order_number', $id);
                     })
                     ->firstOrFail();

        // For customer requests, ensure they can only view their own orders
        if (!$request->is('api/admin/*') && $request->user() && $order->user_id != $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Append computed attributes
        $order->append(['paid_amount', 'due_amount', 'is_fully_paid']);

        return response()->json($order);
    }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'status_id' => 'sometimes|exists:order_statuses,id',
            'event_id' => 'nullable|exists:events,id',
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
            // Fix: $order->status returns string column, use status_id to find model
            $currentStatus = OrderStatus::find($order->status_id);
            if ($currentStatus && $currentStatus->canTransitionTo($validated['status_id'])) {
                $order->updateStatus($validated['status_id'], $request->status_note ?? null, Auth::id());
                // Notify User
                try {
                    $order->user->notify(new OrderStatusUpdated($order, $newStatus->name ?? $newStatus->label));
                } catch (\Exception $e) {}
            } elseif (!$currentStatus) {
                // If no current status (shouldn't happen), allow update
                $order->updateStatus($validated['status_id'], $request->status_note ?? null, Auth::id());
                 // Notify User
                try {
                    $order->user->notify(new OrderStatusUpdated($order, $newStatus->name ?? $newStatus->label));
                } catch (\Exception $e) {}
            } else {
                return response()->json([
                    'message' => 'Status transition not allowed'
                ], 422);
            }
        }

        // Update other fields
        $order->update($validated);

        // Check for Admin Approval of Bank Transfer
        // If payment method is bank_transfer AND (status changed to Processing/Completed OR payment_status changed to paid)
        if ($order->payment_method === 'bank_transfer') {
            $isApproved = false;

            // Check status change (assuming ID 2 is Processing, 3 is Completed, 4 is Delivered)
            // Or simply if status is NOT Pending (1) and NOT Cancelled (5)
            if (isset($validated['status_id']) && in_array($validated['status_id'], [2, 3, 4])) {
                $isApproved = true;
            }

            // Check payment status change
            if (isset($validated['payment_status']) && $validated['payment_status'] === 'paid') {
                $isApproved = true;
            }

            if ($isApproved) {
                // Determine if we should send the "Placed" notification (which acts as Approved/Confirmed)
                // We typically only want to send this once.
                // For simplicity, we send it.
                $order->user->notify(new OrderPlacedNotification($order));
            }
        }

        $order->load(['user', 'status', 'event', 'coupon', 'items.product', 'statusHistories.changedBy']);

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
        $currentStatus = OrderStatus::find($order->status_id);
        if ($currentStatus && !$currentStatus->canTransitionTo($request->status_id)) {
            return response()->json([
                'message' => 'Status transition not allowed from ' . $currentStatus->label . ' to ' . $newStatus->label
            ], 422);
        }

        $order->updateStatus($request->status_id, $request->note, Auth::id());

        try {
            $order->user->notify(new OrderStatusUpdated($order, $newStatus->name ?? $newStatus->label));
        } catch (\Exception $e) {}

        $order->load(['user', 'status', 'items.product', 'statusHistories.changedBy']);

        return response()->json([
            'message' => 'Order status updated successfully',
            'order' => $order
        ]);
    }
}
