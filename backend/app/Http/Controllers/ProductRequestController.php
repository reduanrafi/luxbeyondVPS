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
use Illuminate\Support\Facades\DB;

class ProductRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = ProductRequest::with(['orderStatus', 'timeline'])
            ->where('user_id', $user->id)
            ->whereDoesntHave('orderItem');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%")
                  ->orWhere('url', 'like', "%{$search}%")
                  ->orWhere('request_number', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status) {
            $query->where('status_id', $request->status);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($requests);
    }

    public function adminIndex(Request $request)
    {
        $query = ProductRequest::with(['user', 'orderStatus', 'timeline'])
            ->whereDoesntHave('orderItem');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%")
                  ->orWhere('url', 'like', "%{$search}%")
                  ->orWhere('request_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('status') && $request->status) {
            $statusId = $request->status;
            // Support filtering by both ID and Name if needed, but ID is cleaner
            $query->where(function($q) use ($statusId) {
                $q->where('status_id', $statusId)
                  ->orWhere('status', $statusId); // Fallback for legacy status strings
            });
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(20);

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

        if ($request->has('declared_shipping_cost') && $request->declared_shipping_cost != null) {
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

        $weightCharge = 0;

        // Add additional weight charge if applicable
        if ($request->weight > 1) {
             $perKgCharge = \App\Models\Setting::get('delivery_charge_per_kg', 0);
             $weightCharge += ($request->weight - 1) * $perKgCharge;
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
                    'currency' => $currency->code, // The charge is associated with this currency
                    'charge' => $charge->name,
                    'calculation_type' => $charge->calculation_type,
                    'value' => $charge->value,
                    'amount_in_currency' => $charge->calculation_type === 'fixed' ? $charge->value : ($productSubtotal * $charge->value / 100),
                    'amount_in_bdt' => $chargeAmountBDT,
                    'base_amount_in_bdt' => $chargeAmountBDT
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

        $minPaymentAmountPercent =\App\Models\Setting::get('min_payment_percentage_request', 100);
        $minPaymentAmount = ($grandTotal * $minPaymentAmountPercent) / 100;

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
            'charges_breakdown' => $chargesBreakdown,
            'min_payment_amount' => $minPaymentAmount,
            'weight_charge' => $weightCharge
        ]);

        $productRequest->refresh();
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
            'request_id' => 'required|exists:product_requests,id',
            'amount' => 'required|numeric|min:1',
            'payment_type' => 'nullable|string|in:full,partial',
        ]);

        $productRequest = ProductRequest::where(function($query) use ($validated){
            $query->where('id', $validated['request_id'])->orWhere('request_number', $validated['request_id']);
        })->firstOrFail();

        if ($productRequest->user_id != Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Configure Bkash - use DB config if available, otherwise fallback to .env
        $paymentMethod = \App\Models\PaymentMethod::where('type', 'bkash')->where('is_active', true)->first();

        // if ($paymentMethod && $paymentMethod->config) {
        //     // Use database credentials
        //     // config([
        //     //     'bkash.sandbox' => $paymentMethod->config['is_sandbox'] ?? env('BKASH_SANDBOX', true),
        //     //     'bkash.credentials.app_key' => $paymentMethod->config['app_key'] ?? env('BKASH_APP_KEY', ''),
        //     //     'bkash.credentials.app_secret' => $paymentMethod->config['app_secret'] ?? env('BKASH_APP_SECRET', ''),
        //     //     'bkash.credentials.username' => $paymentMethod->config['username'] ?? env('BKASH_USERNAME', ''),
        //     //     'bkash.credentials.password' => $paymentMethod->config['password'] ?? env('BKASH_PASSWORD', ''),
        //     // ]);
        // } else {
        //     // Fallback to .env credentials
        //     config([
        //         'bkash.sandbox' => env('BKASH_SANDBOX', true),
        //         'bkash.credentials.app_key' => env('BKASH_APP_KEY', ''),
        //         'bkash.credentials.app_secret' => env('BKASH_APP_SECRET', ''),
        //         'bkash.credentials.username' => env('BKASH_USERNAME', ''),
        //         'bkash.credentials.password' => env('BKASH_PASSWORD', ''),
        //     ]);
        // }

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
        $productRequest = ProductRequest::where(function($query) use ($id){
            $query->where('id', $id)->orWhere('request_number', $id);
        })->firstOrFail();

        if ($productRequest->user_id != Auth::id()) {
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
        $productRequest = ProductRequest::where(function($query) use ($id){
            $query->where('id', $id)->orWhere('request_number', $id);
        })->firstOrFail();

        if ($productRequest->user_id != Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'shipping_address' => 'required|array',
            'shipping_address.name' => 'required|string',
            'shipping_address.division' => 'required|string',
            'shipping_address.thana' => 'required|string',
            'shipping_address.street' => 'required|string',
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
        $productRequest = ProductRequest::where(function($query) use ($id) {
            $query->where('id', $id)
                ->orWhere('request_number', $id);
        })->first();

        if ($productRequest->user_id != Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Only allow update if not paid, completed, or cancelled
        if (in_array($productRequest->status, ['completed', 'cancelled', 'paid']) || $productRequest->payment_status === 'paid') {
           return response()->json(['message' => 'Cannot update quantity for this request status'], 400);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $oldQuantity = $productRequest->quantity;
        $newQuantity = $validated['quantity'];

        if ($oldQuantity != $newQuantity && $oldQuantity > 0) {
            $ratio = $newQuantity / $oldQuantity;

            // Scale explicit fields
            // $productRequest->tax = ($productRequest->tax ?? 0) * $ratio;
            // $productRequest->delivery_charge = ($productRequest->delivery_charge ?? 0) * $ratio;
            // $productRequest->additional_charges = ($productRequest->additional_charges ?? 0) * $ratio;
            // $productRequest->payment_processing_fee = ($productRequest->payment_processing_fee ?? 0) * $ratio;
            // $productRequest->declared_shipping_cost = ($productRequest->declared_shipping_cost ?? 0) * $ratio;

            // Scale charges breakdown
            $updatedBreakdown = [];
            if (!empty($productRequest->charges_breakdown)) {
                foreach ($productRequest->charges_breakdown as $charge) {
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

            $productRequest->weightCharge = $productRequest->weightCharge * $ratio;
            $productRequest->total_amount_bdt = $productRequest->total_amount_bdt * $ratio;
            $minPaymentAmountPercent = \App\Models\Setting::get('min_payment_percentage_request', 100);
            $productRequest->min_payment_amount = ($productRequest->total_amount_bdt * $minPaymentAmountPercent) / 100;

            $productRequest->quantity = $newQuantity;
            $productRequest->save();
        }

        return response()->json([
            'message' => 'Quantity updated successfully',
            'product_request' => $productRequest
        ]);
    }
    public function show($id)
    {

        $productRequest = ProductRequest::with(['user', 'orderStatus', 'timeline.creator'])->where(function($query) use ($id){
            $query->where('id', $id)->orWhere('request_number', $id);
        })->firstOrFail();

        if ($productRequest->user_id != Auth::id() && !Auth::user()->hasRole('Admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json($productRequest);
    }

    // Admin approves and updates image
    public function update(Request $request, $id)
    {
        $productRequest = ProductRequest::where(function($query) use ($id){
            $query->where('id', $id)->orWhere('request_number', $id);
        })->firstOrFail();

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
            'paid_amount'       => 'nullable|numeric|min:0',
            'due_amount'       => 'nullable|numeric|min:0',
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
        if ($newStatusName && $newStatusName != $oldStatus) {
            \App\Models\ProductRequestTimeline::create([
                'product_request_id' => $productRequest->id,
                'status' => $newStatusName,
                'note' => $request->admin_note ? 'Status changed: ' . $newStatusName : 'Status updated',
                'created_by' => Auth::id(),
            ]);

            // Notify user about status change
            try {
                $productRequest->user->notify(new \App\Notifications\ProductRequestStatusUpdated($productRequest, $newStatusName));
            } catch (\Exception $e) {
                \Log::error('Failed to notify user about status update: ' . $e->getMessage());
            }
        } elseif (isset($updateData['status_id']) && $updateData['status_id'] != $oldStatusId) {
             // Fallback if name was same but ID changed (unlikely but safe)
             \App\Models\ProductRequestTimeline::create([
                'product_request_id' => $productRequest->id,
                'status' => $newStatusName ?? 'updated',
                'note' => 'Status ID updated',
                'created_by' => Auth::id(),
            ]);

            try {
                $productRequest->user->notify(new \App\Notifications\ProductRequestStatusUpdated($productRequest, $newStatusName ?? 'updated'));
            } catch (\Exception $e) {
                \Log::error('Failed to notify user about status ID update: ' . $e->getMessage());
            }
        }

        return response()->json([
            'message' => 'Product request updated successfully',
            'product_request' => $productRequest
        ]);
    }

    public function convertToOrder(Request $request, $id)
    {
        $productRequest = ProductRequest::with('user')->where(function($query) use ($id){
            $query->where('id', $id)->orWhere('request_number', $id);
        })->firstOrFail();

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
            'shipping' => $productRequest->delivery_fee,
            'tax' => $productRequest->tax,
            'payment_method' => $productRequest->payment_method,
            'payment_status' => $productRequest->payment_status ?? 'unpaid',
            'shipping_address' => $productRequest->shipping_address,
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
        $productRequest = ProductRequest::where(function($query) use ($id){
            $query->where('id', $id)->orWhere('request_number', $id);
        })->firstOrFail();
        if ($productRequest->user_id != Auth::id() && !Auth::user()->hasRole('Admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $productRequest->delete();
        return response()->json(['message' => 'Product request deleted successfully']);
    }

    public function createOrderFromRequests(Request $request)
    {
        $validated = $request->validate([
            'request_id'     => 'required',
            'payment_method' => 'required|string|in:bkash,bank_transfer',
            'payment_type'   => 'nullable|string|in:full,partial',
            'payment_slip'   => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf|max:5120',
            'shipping_address'          => 'required|array',
            'shipping_address.name'     => 'required|string',
            'shipping_address.division' => 'nullable|string',
            'shipping_address.thana'    => 'nullable|string',
            'shipping_address.street'   => 'required|string',
            'shipping_address.phone'    => 'required|string',
        ]);

        $userId = Auth::id();

        // Support both numeric ID and request_number
        $productRequest = ProductRequest::where(function ($q) use ($validated) {
            $q->where('id', $validated['request_id'])
              ->orWhere('request_number', $validated['request_id']);
        })
        ->where('user_id', $userId)
        ->whereIn('status', ['request_accepted', 'accepted'])
        ->first();

        if (!$productRequest) {
            return response()->json(['message' => 'Request not found, not yours, or not eligible for order creation.'], 422);
        }

        try {
            DB::beginTransaction();

            // Use pre-calculated totals stored on the request by admin
            $totalAmount      = $productRequest->total_amount_bdt ?? 0;
            $minPaymentAmount = $request->payment_type == 'partial' && $productRequest->min_payment_amount == 0 ? \App\Models\Setting::get('min_payment_percentage_request', 60) * $totalAmount / 100 : $productRequest->min_payment_amount;
            $tax              = $productRequest->tax ?? 0;
            $shipping         = ($productRequest->declared_shipping_cost ?? 0) + ($productRequest->delivery_charge ?? 0);
            $currency = \App\Models\Currency::where('code', $productRequest->currency)->first();
            $price = $productRequest->price * $currency->rate_to_base;
            $subtotal         = $price * $productRequest->quantity;

            // Determine default order status
            $defaultStatus = OrderStatus::where('name', 'pending')->first();

            // Create Order
            $order = Order::create([
                'user_id'          => $userId,
                'status_id'        => $defaultStatus?->id,
                'status'           => $defaultStatus?->name ?? 'pending',
                'subtotal'         => $subtotal,
                'tax'              => $tax,
                'shipping'         => $shipping,
                'total'            => $totalAmount,
                'min_payment_amount' => $minPaymentAmount,
                'currency'         => 'BDT',
                'payment_method'   => $validated['payment_method'],
                'payment_status'   => 'unpaid',
                'shipping_address' => $validated['shipping_address'],
                'shipping_name'    => $validated['shipping_address']['name'] ?? Auth::user()->name,
                'shipping_phone'   => $validated['shipping_address']['phone'],
                'notes'            => 'Order from Request #' . $productRequest->request_number,
            ]);

            // Handle payment slip upload (bank transfer)
            if ($request->hasFile('payment_slip')) {
                $file     = $request->file('payment_slip');
                $folder   = 'payment_slips';
                $filename = $order->order_number . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs($folder, $filename, 'public');
                $order->update([
                    'payment_slip'   => $folder . '/' . $filename,
                    'payment_status' => 'pending',
                ]);
            }

            // Create Order Item from request data
            \App\Models\OrderItem::create([
                'request_id'   => $productRequest->id,
                'order_id'     => $order->id,
                'product_name' => $productRequest->product_name ?? 'Product Request #' . $productRequest->request_number,
                'price'        => $price,
                'quantity'     => $productRequest->quantity,
                'subtotal'     => $totalAmount,
                'image'        => $productRequest->admin_image_url ?? $productRequest->url,
                'variant_data' => [
                    'request_url'    => $productRequest->url,
                    'request_number' => $productRequest->request_number,
                    'request_id'     => $productRequest->id,
                ],
            ]);

            // Mark request as completed and save shipping address
            $productRequest->update([
                'status'           => 'completed',
                'shipping_address' => $validated['shipping_address'],
            ]);

            // Timeline entry
            \App\Models\ProductRequestTimeline::create([
                'product_request_id' => $productRequest->id,
                'status'             => 'completed',
                'note'               => 'Converted to Order #' . $order->order_number,
                'created_by'         => $userId,
            ]);

            // Initial order status history
            $order->updateStatus($defaultStatus->id, 'Created from Request #' . $productRequest->request_number, $userId);

            // Notify user
            try {
                Auth::user()->notify(new \App\Notifications\OrderPlacedNotification($order));
            } catch (\Exception $e) {}

            DB::commit();

            // bKash: return flag for frontend to initiate payment
            if ($validated['payment_method'] === 'bkash') {
                $payAmount = ($validated['payment_type'] ?? 'partial') === 'full'
                    ? $order->total
                    : $order->min_payment_amount;

                return response()->json([
                    'message'       => 'Order created. Redirecting to bKash...',
                    'order_id'      => $order->id,
                    'order_number'  => $order->order_number,
                    'initiate_bkash' => true,
                    'pay_amount'    => $payAmount,
                    'payment_type'  => $validated['payment_type'] ?? 'partial',
                ]);
            }

            return response()->json([
                'message'      => 'Order created successfully',
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order Creation Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create order: ' . $e->getMessage()], 500);
        }
    }
}
