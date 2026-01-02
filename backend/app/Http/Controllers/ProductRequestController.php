<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ProductRequest;
use App\Models\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Ihasan\Bkash\Facades\Bkash;

class ProductRequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        // Always return only the user's requests for the standard index route
        $requests = ProductRequest::with(['orderStatus', 'timeline'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($requests);
    }

    public function adminIndex()
    {
        // Admin only route to get all requests
        $requests = ProductRequest::with(['user', 'orderStatus', 'timeline'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($requests);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'currency' => 'required|string|max:3',
            'declared_shipping_cost' => 'nullable|numeric|min:0',
            'is_inside_city' => 'nullable|boolean',
            'weight' => 'nullable|numeric|min:0',
            'total_amount_bdt' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'delivery_charge' => 'nullable|numeric|min:0',
            'payment_processing_fee' => 'nullable|numeric|min:0',
            'additional_charges' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('declared_shipping_cost') && $request->declared_shipping_cost !== null) {
            $shippingCost = $request->declared_shipping_cost;
        } else {
            // Basic estimation logic if not provided (server-side fallback)
            $shippingCost = 0;
        }

        // Calculate default delivery charge from settings
        $isInsideCity = $request->is_inside_city ?? false;
        $defaultDeliveryCharge = 0;
        
        if ($isInsideCity) {
            $defaultDeliveryCharge = \App\Models\Setting::get('delivery_charge_inside_city', 0);
        } else {
            $defaultDeliveryCharge = \App\Models\Setting::get('delivery_charge_outside_city', 0);
        }

        // Add additional weight charge if applicable
        if ($request->weight > 1) {
             $perKgCharge = \App\Models\Setting::get('delivery_charge_per_kg', 0);
             $defaultDeliveryCharge += ($request->weight - 1) * $perKgCharge;
        }

        // Calculate Additional Charges from 'charges' table based on currency
        $currencyCode = $request->currency;
        $currency = \App\Models\Currency::where('code', $currencyCode)->first();
        
        $chargesBreakdown = [];
        $additionalChargesTotal = 0;
        $productSubtotal = $request->price * $request->quantity;

        // Fetch active charges that match the currency
        if ($currency) {
            $charges = \App\Models\Charge::where('is_active', true)
                ->where('currency_id', $currency->id)
                ->orderBy('sort_order')
                ->get();

            foreach ($charges as $charge) {
                // Determine base amount for calculation - usually product subtotal
                $chargeAmount = $charge->calculate($productSubtotal);
                
                // Convert charge to BDT if needed for the total? 
                // The 'calculate' method in Charge model usually returns amount in Charge's currency or Base?
                // Looking at Charge.php: 'if ($this->currency && !$this->currency->is_base) { $amount = $amount * rate_to_base; }'
                // This implies 'calculate' returns the BDT (Base) amount if rate_to_base is applied.
                
                $chargeAmountBDT = $chargeAmount; // Since calculate() handles conversion to base
                
                // Store in breakdown - usually we want to show original currency amount too?
                // Let's assume calculate returns BDT value as per previous analysis of Charge.php
                
                // However, we might want to store the original calculated amount in the currency too?
                // Let's re-read Charge.php carefully. 
                // Ah, check Charge.php content again.
                // It converts to base if !is_base. So it returns Base Currency (BDT).
                
                $additionalChargesTotal += $chargeAmountBDT;
                
                $chargesBreakdown[] = [
                    'charge' => $charge->name,
                    'currency' => $currency->code, // The charge is associated with this currency
                    'amount_in_currency' => $charge->calculation_type === 'fixed' ? $charge->value : ($productSubtotal * $charge->value / 100),
                    'amount_in_bdt' => $chargeAmountBDT
                ];
            }
        }

        // Calculate final total
        // Note: frontend might be sending total_amount_bdt, tax etc.
        // If we are auto-calculating, we should override or merge?
        // User asked to "add them", implying backend calculation should take precedence or at least default them.
        
        // Let's trust the backend calculation for new requests
        $tax = $request->tax ?? 0;
        $processingFee = $request->payment_processing_fee ?? 0;
        $otherAdditional = $request->additional_charges ?? 0; // Manual additional charges if any
        
        // Total additional includes calculated + manual
        $finalAdditionalCharges = $additionalChargesTotal + $otherAdditional;
        
        // We need to calculate Product Total in BDT for the grand total
        $productTotalBDT = $productSubtotal;
        if ($currency && !$currency->is_base) {
            $productTotalBDT = $productSubtotal * $currency->rate_to_base;
        }
        
        $grandTotal = $productTotalBDT + $defaultDeliveryCharge + $tax + $finalAdditionalCharges + $processingFee + $shippingCost;

        $productRequest = ProductRequest::create([
            'user_id' => Auth::id(),
            'url' => $request->url,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'currency' => $request->currency,
            'declared_shipping_cost' => $shippingCost,
            'is_inside_city' => $isInsideCity,
            'weight' => $request->weight,
            'status' => 'pending', // Initial status
            'payment_status' => 'pending',
            'product_name' => $request->product_name ?? null,
            'total_amount_bdt' => $grandTotal,
            'tax' => $tax,
            'delivery_charge' => $defaultDeliveryCharge,
            'payment_processing_fee' => $processingFee,
            'additional_charges' => $finalAdditionalCharges,
            'charges_breakdown' => $chargesBreakdown,
        ]);

        // Create initial timeline entry
        \App\Models\ProductRequestTimeline::create([
            'product_request_id' => $productRequest->id,
            'status' => 'pending',
            'note' => 'Request created',
            'created_by' => Auth::id(),
        ]);

        // Notify User
        $user = Auth::user();
        try {
            $user->notify(new \App\Notifications\ProductRequestSubmittedNotification($productRequest));
        } catch (\Exception $e) {
            Log::error('Failed to notify user: ' . $e->getMessage());
        }

        // Notify Admins
        try {
            // Adjust depending on how roles are implemented. Assuming Spatie or similar:
            $admins = \App\Models\User::role('Admin')->get();
            if ($admins->count() > 0) {
                \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\NewProductRequestNotification($productRequest));
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify admins: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Product request submitted successfully',
            'product_request' => $productRequest,
        ], 201);
    }

    public function initiateBkashPayment(Request $request)
    {
        $validated = $request->validate([
            'product_request_id' => 'required|exists:product_requests,id',
            'amount' => 'required|numeric|min:1',
        ]);

        $productRequest = ProductRequest::findOrFail($validated['product_request_id']);

        if ($productRequest->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Configure Bkash
        $paymentMethod = \App\Models\PaymentMethod::where('type', 'bkash')->where('is_active', true)->first();
        if (!$paymentMethod || !$paymentMethod->config) {
            return response()->json(['message' => 'bKash not configured'], 500);
        }
        config([
            'bkash.sandbox' => $paymentMethod->config['is_sandbox'] ?? true,
            'bkash.credentials.app_key' => $paymentMethod->config['app_key'] ?? '',
            'bkash.credentials.app_secret' => $paymentMethod->config['app_secret'] ?? '',
            'bkash.credentials.username' => $paymentMethod->config['username'] ?? '',
            'bkash.credentials.password' => $paymentMethod->config['password'] ?? '',
        ]);

        try {
            $backendUrl = rtrim(config('app.url'), '/');
            $callbackUrl = $backendUrl . '/api/payments/bkash/callback?request_id=' . $productRequest->id;

            $paymentData = [
                'amount' => number_format((float) $validated['amount'], 2, '.', ''),
                'payer_reference' => 'req_' . $productRequest->id,
                'callback_url' => $callbackUrl,
                'merchant_invoice_number' => 'REQ-' . $productRequest->id,
            ];

            $response = \Ihasan\Bkash\Facades\Bkash::createPayment($paymentData);

            return response()->json([
                'paymentID' => $response['paymentID'] ?? null,
                'bkashURL' => $response['bkashURL'] ?? null,
                'successCallback' => $response['successCallback'] ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function submitPaymentDetails(Request $request, $id)
    {
        $productRequest = ProductRequest::findOrFail($id);

        if ($productRequest->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'payment_method' => 'required|string|in:bank_transfer,bkash,cod', // cod unlikely for request but ok
            'payment_slip' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'payment_reference' => 'nullable|string',
        ]);

        if ($request->hasFile('payment_slip')) {
            $path = $request->file('payment_slip')->store('payment_slips', 'public');
            $productRequest->payment_slip = $path;
        }

        $productRequest->payment_method = $request->payment_method;
        if ($request->payment_reference) {
            $productRequest->payment_reference = $request->payment_reference;
        }

        // If submitting manual details, status becomes processing
        $productRequest->payment_status = 'processing';
        $productRequest->save();

        return response()->json(['message' => 'Payment details submitted successfully']);
    }

    public function confirmOrder(Request $request, $id)
    {
        $productRequest = ProductRequest::findOrFail($id);

        if ($productRequest->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'shipping_address' => 'required|array',
            'shipping_address.street' => 'required|string',
            'shipping_address.city' => 'required|string',
            'shipping_address.phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $productRequest->shipping_address = $request->shipping_address;
        $productRequest->save();

        // Log confirmation
        \App\Models\ProductRequestTimeline::create([
            'product_request_id' => $productRequest->id,
            'status' => $productRequest->status, // Status might not change, or could change to 'confirmed'
            'note' => 'User confirmed order and provided shipping address',
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Order confirmed successfully',
            'product_request' => $productRequest
        ]);
    }
    
    public function updateQuantity(Request $request, $id)
    {
        $productRequest = ProductRequest::findOrFail($id);

        if ($productRequest->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Only allow update if not paid, completed, or cancelled
        if (in_array($productRequest->status, ['completed', 'cancelled', 'paid']) || $productRequest->payment_status === 'paid') {
           return response()->json(['message' => 'Cannot update quantity for this request status'], 400); 
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $productRequest->quantity = $validated['quantity'];
        
        // Recalculate Total
        // Base Formula: (Price * Qty * Rate) + Tax + Delivery + Additional + Processing + Ship
        // We assume Delivery/Tax/Additional/Processing/Ship are fixed amounts set by Admin
        // However, if charges are percentage-based, they SHOULD be recalculated.
        // For simplicity and safety (not knowing exact charge rules per request), we will update the Product Subtotal component of the Total.
        // But wait, Total Amount BDT is stored as a single value.
        // We need to re-derive it.
        
        $currency = \App\Models\Currency::where('code', $productRequest->currency)->first();
        $rate = ($currency && !$currency->is_base) ? $currency->rate_to_base : 1;
        
        $productTotalBDT = $productRequest->price * $productRequest->quantity * $rate;
        
        // We must re-sum the charges from breakdown if possible, or use the stored fields?
        // Stored fields (tax, delivery, etc) are flat amounts.
        // Assuming they are fixed for now.
        
        // Charges Breakdown: 'amount_in_bdt' might need update if it was % based on product total.
        // If we don't update them, we might be wrong.
        // But we don't know the rule (fixed vs %) from just the array.
        // Ideally we should re-run Charge::calculate().
        // BUT, admin might have customized them.
        
        // Compromise:
        // Update product total.
        // Leave charges as is (User is just changing quantity, admin usually sets per-order charges (delivery, tax etc might be fixed)).
        // Warn user? Or just do it.
        // Let's just update the product component of total.
        
        $chargesTotal = ($productRequest->tax ?? 0) +
                        ($productRequest->delivery_charge ?? 0) +
                        ($productRequest->additional_charges ?? 0) +
                        ($productRequest->payment_processing_fee ?? 0) +
                        ($productRequest->declared_shipping_cost ?? 0);
                        
        // Also add breakdown items sum in case they aren't in the explicit fields (since we refactored)
        // Wait, did we refactor backend to NOT use explicit fields?
        // Admin edits sets them to 0 and puts everything in breakdown.
        // So we MUST sum breakdown.
        
        $breakdownTotal = 0;
        if (!empty($productRequest->charges_breakdown)) {
             foreach ($productRequest->charges_breakdown as $charge) {
                 $breakdownTotal += ($charge['amount_in_bdt'] ?? 0);
             }
        }
        
        // Avoid double counting if migration happened partially or not at all.
        // If breakdown exists, rely on it?
        // Admin controller 'update' sums them all?
        // Let's look at EditProductRequest.vue: calculates total as ProductTotal + Breakdown + Shipping.
        // It IGNORES tax/delivery/etc if breakdown exists (or adds them if migrated).
        // So: Total = ProductTotalBDT + DeclaredShipping + BreakdownSum.
        
        $newTotal = $productTotalBDT + ($productRequest->declared_shipping_cost ?? 0) + $breakdownTotal;
        
        // Fallback for legacy (if breakdown empty)
        if (empty($productRequest->charges_breakdown)) {
             $newTotal = $productTotalBDT + $chargesTotal;
        }

        $productRequest->total_amount_bdt = $newTotal;
        $productRequest->save();

        return response()->json([
            'message' => 'Quantity updated successfully',
            'product_request' => $productRequest
        ]);
    }
    public function show($id)
    {
        $productRequest = ProductRequest::with(['user', 'orderStatus', 'timeline.creator'])->findOrFail($id);
        if ($productRequest->user_id !== Auth::id() && !Auth::user()->hasRole('Admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json($productRequest);
    }
    
    // Admin approves and updates image
    public function update(Request $request, $id)
    {
        $productRequest = ProductRequest::findOrFail($id);
        
        // Check permission (simple check for now, better to use Policies)
        if (!Auth::user()->hasRole('Admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'url' => 'nullable|url',
            'product_name' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|integer|min:1',
            'currency' => 'nullable|string|max:3',
            'declared_shipping_cost' => 'nullable|numeric|min:0',
            'is_inside_city' => 'nullable|boolean',
            'weight' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'tax' => 'nullable|numeric|min:0',
            'additional_charges' => 'nullable|numeric|min:0',
            'delivery_charge' => 'nullable|numeric|min:0',
            'payment_processing_fee' => 'nullable|numeric|min:0',
            'admin_image_url' => 'nullable|string',
            'status' => 'nullable|string',
            'status_id' => 'nullable|exists:order_statuses,id',
            'admin_note' => 'nullable|string',
            'total_amount_bdt' => 'nullable|numeric|min:0',
            'min_payment_amount' => 'nullable|numeric|min:0',
            'payment_status' => 'nullable|string',
            'charges_breakdown' => 'nullable|array',
        ]);

        $oldStatus = $productRequest->status;
        $oldStatusId = $productRequest->status_id;

        // Update all provided fields
        $updateData = $request->except(['_token', '_method']);

        // Convert is_inside_city to boolean if provided
        if ($request->has('is_inside_city')) {
            $updateData['is_inside_city'] = filter_var($request->is_inside_city, FILTER_VALIDATE_BOOLEAN);
        }

        // If status_id is provided, also update status name from order_status
        $newStatusName = null;
        if (isset($updateData['status_id'])) {
            $orderStatus = OrderStatus::find($updateData['status_id']);
            if ($orderStatus) {
                $newStatusName = $orderStatus->name;
                $updateData['status'] = $orderStatus->name; 
            }
        } elseif (isset($updateData['status'])) {
            $newStatusName = $updateData['status'];
        }

        // Check for image upload
        if ($request->hasFile('admin_image_file')) {
            $path = $request->file('admin_image_file')->store('requests/'.$request->id, 'public');
            $updateData['admin_image_url'] = asset(config('app.storage_repo').'/'. $path);
        }

        $productRequest->update($updateData);

        // Check for status change and log timeline
        if ($newStatusName && $newStatusName !== $oldStatus) {
            \App\Models\ProductRequestTimeline::create([
                'product_request_id' => $productRequest->id,
                'status' => $newStatusName,
                'note' => $request->admin_note ? 'Status changed: ' . $newStatusName : 'Status updated',
                'created_by' => Auth::id(),
            ]);
        } elseif (isset($updateData['status_id']) && $updateData['status_id'] != $oldStatusId) {
             // Fallback if name was same but ID changed (unlikely but safe)
             \App\Models\ProductRequestTimeline::create([
                'product_request_id' => $productRequest->id,
                'status' => $newStatusName ?? 'updated',
                'note' => 'Status ID updated',
                'created_by' => Auth::id(),
            ]);
        }

        return response()->json([
            'message' => 'Product request updated successfully',
            'product_request' => $productRequest
        ]);
    }

    public function convertToOrder(Request $request, $id)
    {
        $productRequest = ProductRequest::with('user')->findOrFail($id);
        
        // Admin only
        if (!Auth::user()->hasRole('Admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if already converted or completed (optional safeguard)
        // if ($productRequest->status === 'completed') { ... }

        // Use request status for the order
        $orderStatusId = $productRequest->status_id;
        $orderStatusName = $productRequest->status;

        // If for some reason status_id is missing but we have a name, try to find the ID
        if (!$orderStatusId && $orderStatusName) {
            $existingStatus = OrderStatus::where('name', $orderStatusName)->first();
            if ($existingStatus) {
                $orderStatusId = $existingStatus->id;
            }
        }

        // Fallback default if still no valid status found
        if (!$orderStatusId) {
             $defaultStatus = OrderStatus::where('is_default', true)->first() 
                              ?? OrderStatus::where('name', 'pending')->first();
             $orderStatusId = $defaultStatus?->id;
             $orderStatusName = $defaultStatus?->name ?? 'pending';
        }

        // Calculate total
        $total = $productRequest->total_amount_bdt ?: 0;
        
        // Create Order
        $order = Order::create([
            'user_id' => $productRequest->user_id,
            'status_id' => $orderStatusId,
            'status' => $orderStatusName,
            'subtotal' => $total, // Simplified: treating Total BDT as subtotal since it includes all
            'total' => $total,
            'currency' => 'BDT',
            'payment_method' => $productRequest->payment_method,
            'payment_status' => $productRequest->payment_status ?? 'unpaid',
            'shipping_address' => json_encode($productRequest->shipping_address),
            'notes' => 'Converted from Request #' . $productRequest->request_number,
        ]);

        // Create Order Item
        \App\Models\OrderItem::create([
            'order_id' => $order->id,
            'product_name' => $productRequest->product_name ?? 'Custom Product Request',
            'price' => $productRequest->price, // Storing original price, though total is what matters
            'quantity' => $productRequest->quantity,
            'subtotal' => $total,
            'image' => $productRequest->admin_image_url ?? $productRequest->url, // Use admin image if set
            'variant_data' => [
                'request_url' => $productRequest->url,
                'request_number' => $productRequest->request_number
            ],
        ]);

        // Update Request Status
        $productRequest->update([
            'status' => 'completed',
            // You might want a specific status for converted requests if available
        ]);

        // Log Timeline
        \App\Models\ProductRequestTimeline::create([
            'product_request_id' => $productRequest->id,
            'status' => 'completed',
            'note' => 'Converted to Order #' . $order->order_number,
            'created_by' => Auth::id(),
        ]);
        
        // Create Status History for Order
        $order->updateStatus($defaultStatus->id, 'Created from Request #' . $productRequest->request_number, Auth::id());
        
        // Notify User
        $productRequest->user->notify(new \App\Notifications\OrderPlacedNotification($order));

        return response()->json([
            'message' => 'Request converted to order successfully',
            'order_id' => $order->id,
            'order_number' => $order->order_number
        ]);
    }

    public function destroy($id)
    {
        $productRequest = ProductRequest::findOrFail($id);
        if ($productRequest->user_id !== Auth::id() && !Auth::user()->hasRole('Admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $productRequest->delete();
        return response()->json(['message' => 'Product request deleted successfully']);
    }
}
