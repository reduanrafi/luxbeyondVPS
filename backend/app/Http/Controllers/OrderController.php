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
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Dompdf\Options;


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
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', '%' . $search . '%')
                  ->orWhere('id', 'like', '%' . $search . '%')
                  ->orWhere('payment_reference', 'like', '%' . $search . '%')
                  ->orWhere('shipping_phone', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', '%' . $search . '%')
                                ->orWhere('email', 'like', '%' . $search . '%')
                                ->orWhere('phone', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('items', function($itemQuery) use ($search) {
                      $itemQuery->where('product_name', 'like', '%' . $search . '%')
                                ->orWhere('product_sku', 'like', '%' . $search . '%');
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
        if ($request->has('shipping_address') && is_string($request->shipping_address)) {
            $addr = json_decode($request->shipping_address, true);
            if (is_array($addr)) {
                $request->merge(['shipping_address' => $addr]);
            }
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
            'items.*.variant_data' => 'nullable',
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'shipping' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'payment_method' => 'nullable|string',
            'shipping_address' => 'nullable|array',
            'shipping_address.division' => 'nullable|string',
            'shipping_address.thana' => 'nullable|string',
            'shipping_address.street' => 'nullable|string',
            'shipping_name' => 'nullable|string',
            'shipping_phone' => 'nullable|string',
            'notes' => 'nullable|string',
            'payment_slip' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf|max:5120', // 5MB max
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
            $folder = 'payment_slips';
            $filename = $order->order_number . '_' . time() . '.' . $file->getClientOriginalExtension();
    
            $file->storeAs($folder, $filename, 'public');
            $paymentSlipPath = $folder . '/' . $filename;

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
        $order = Order::with(['user', 'status', 'event', 'coupon', 'items.product', 'items.request', 'statusHistories.changedBy', 'statusHistories.status', 'payments'])
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
            'total' => 'sometimes|numeric|min:0',
            'shipping_address' => 'nullable|array',
            'shipping_address.division' => 'nullable|string',
            'shipping_address.thana' => 'nullable|string',
            'shipping_address.street' => 'nullable|string',
            'shipping_name' => 'nullable|string',
            'shipping_phone' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.id' => 'nullable',
            'items.*.product_id' => 'nullable',
            'items.*.product_name' => 'nullable|string',
            'items.*.price' => 'nullable|numeric|min:0',
            'items.*.quantity' => 'nullable|integer|min:1',
            'items.*.variant_data' => 'nullable',
            'items.request.*' => 'nullable',
            'paid_amount' => 'nullable|numeric|min:0',
            'due_amount' => 'nullable|numeric|min:0',
            'is_fully_paid' => 'nullable|boolean',
        ]);

        // 1. FIXED: Handle status update ONLY if it changed
        if (isset($validated['status_id']) && (int)$validated['status_id'] !== (int)$order->status_id) {
            $newStatus = OrderStatus::findOrFail($validated['status_id']);
            $currentStatus = OrderStatus::find($order->status_id);

            if (!$currentStatus || $currentStatus->canTransitionTo($validated['status_id'])) {
                $order->updateStatus($validated['status_id'], $request->status_note ?? null, Auth::id());
                try {
                    $order->user->notify(new OrderStatusUpdated($order, $newStatus->label));
                } catch (\Exception $e) {}
            } else {
                return response()->json(['message' => 'Status transition not allowed'], 422);
            }
        }

        // 2. Update the main Order fields
        $order->update($request->only([
            'event_id', 'payment_status', 'shipping_address', 'shipping_name',
            'shipping_phone', 'notes', 'total', 'paid_amount', 'due_amount', 'is_fully_paid'
        ]));

        // 3. FIXED: Sync Items (Add, Update, or Remove)
        if ($request->has('items')) {
            $incomingItemIds = collect($request->items)->pluck('id')->filter()->toArray();

            // A. Delete items that were removed in the UI
            $order->items()->whereNotIn('id', $incomingItemIds)->delete();

            $runningSubtotal = 0;

           foreach ($request->items as $itemData) {
    $price = (float)$itemData['price'];
    $qty = (int)$itemData['quantity'];
    $itemSubtotal = $price * $qty; // Calculate row subtotal
    $runningSubtotal += $itemSubtotal;

    $orderItem = $order->items()->updateOrCreate(
        ['id' => $itemData['id'] ?? null],
        [
            'product_id'   => $itemData['product_id'],
            'product_name' => $itemData['product_name'],
            'price'        => $price,
            'quantity'     => $qty,
            'subtotal'     => $itemSubtotal, // <--- ADD THIS LINE
            'variant_data' => is_array($itemData['variant_data']) 
                              ? json_encode($itemData['variant_data']) 
                              : $itemData['variant_data'],
        ]
    );

    // If this item is tied to a ProductRequest, scale its charges dynamically
    if ($orderItem->request_id) {
        $productRequest = \App\Models\ProductRequest::find($orderItem->request_id);
        if ($productRequest && $productRequest->quantity != $qty && $productRequest->quantity > 0) {
            $ratio = $qty / $productRequest->quantity;

            // Scale explicit fields
            $productRequest->tax = ($productRequest->tax ?? 0) * $ratio;
            $productRequest->delivery_charge = ($productRequest->delivery_charge ?? 0) * $ratio;
            $productRequest->additional_charges = ($productRequest->additional_charges ?? 0) * $ratio;
            $productRequest->payment_processing_fee = ($productRequest->payment_processing_fee ?? 0) * $ratio;
            $productRequest->declared_shipping_cost = ($productRequest->declared_shipping_cost ?? 0) * $ratio;

            // Scale charges breakdown
            $updatedBreakdown = [];
            if (!empty($productRequest->charges_breakdown)) {
                $breakdown = is_string($productRequest->charges_breakdown) ? json_decode($productRequest->charges_breakdown, true) : $productRequest->charges_breakdown;
                if(is_array($breakdown)){
                    foreach ($breakdown as $charge) {
                        if (isset($charge['amount_in_currency'])) {
                            $charge['amount_in_currency'] *= $ratio;
                        }
                        if (isset($charge['amount_in_bdt'])) {
                            $charge['amount_in_bdt'] *= $ratio;
                        }
                        if (isset($charge['amount'])) {
                            $charge['amount'] *= $ratio;
                        }
                        $updatedBreakdown[] = $charge;
                    }
                    $productRequest->charges_breakdown = $updatedBreakdown;
                }
            }

            // Scale total and min payment
            $productRequest->total_amount_bdt = $productRequest->total_amount_bdt * $ratio;
            $minPaymentAmountPercent = \App\Models\Setting::get('min_payment_percentage_request', 100);
            $productRequest->min_payment_amount = ($productRequest->total_amount_bdt * $minPaymentAmountPercent) / 100;

            $productRequest->quantity = $qty;
            $productRequest->save();
        }
    }
}

            // C. Update Order Subtotal/Total
            $order->subtotal = $runningSubtotal;
            if ($request->has('total')) {
                $order->total = $request->total;
            } else {
                $order->total = $runningSubtotal + $order->tax + $order->shipping - $order->discount;
            }
            $order->save();
        }

        // 4. Notification Logic for Bank Transfer
        if ($order->payment_method === 'bank_transfer') {
            $isApproved = (isset($validated['status_id']) && in_array($validated['status_id'], [2, 3, 4])) 
                    || (isset($validated['payment_status']) && $validated['payment_status'] === 'paid');

            if ($isApproved) {
                $order->user->notify(new OrderPlacedNotification($order));
            }
        }

        $order->load(['user', 'status', 'event', 'items.product']);

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

     public function downloadInvoice($orderId)
    {
        $payloads = [];
       
        try {
            $order = Order::with('items.product')->where('order_number',$orderId)->latest()->first();
            if(!$order){
                return response()->json([
                    'success'=> false
                ]);
            }
            $logoPath = public_path('/logo.png'); // Adjust path as needed

            $logoBase64 = '';

            if (file_exists($logoPath)) {
                $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
            }
           
            $invoiceData = [
                'order' => $order,
                'logo' => $logoBase64,
            ];

            $pdf = PDF::loadView('pdf.order-invoice', $invoiceData);

            $options = new Options();
            $options->setIsPhpEnabled(true);
            $options->setIsJavascriptEnabled(true);
            $pdf->getDomPDF()->setOptions($options);

            $filename = 'invoice-order-' . $order->order_number . '.pdf';

            return $pdf->download($filename);
        } catch (MarvelException $e) {
            throw new MarvelException(NOT_FOUND);
        }
    }
}
