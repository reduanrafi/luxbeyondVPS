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

        $shippingCost = 0;
        if ($request->has('declared_shipping_cost') && $request->declared_shipping_cost !== null) {
            $shippingCost = $request->declared_shipping_cost;
        } else {
            // Basic estimation logic if not provided (server-side fallback)
            // Ideally should match frontend logic or be calculated by Admin
            $shippingCost = 0; // Default to 0 if not declared, let Admin set it.
        }

        $productRequest = ProductRequest::create([
            'user_id' => Auth::id(),
            'url' => $request->url,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'currency' => $request->currency,
            'declared_shipping_cost' => $shippingCost,
            'is_inside_city' => $request->is_inside_city ?? false,
            'weight' => $request->weight,
            'status' => 'pending', // Initial status
            'payment_status' => 'pending',
            'total_amount_bdt' => $request->total_amount_bdt ?? 0,
            'tax' => $request->tax ?? 0,
            'delivery_charge' => $request->delivery_charge ?? 0,
            'payment_processing_fee' => $request->payment_processing_fee ?? 0,
            'additional_charges' => $request->additional_charges ?? 0,
            'charges_breakdown' => $request->charges_breakdown ?? null,
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
            'admin_image_url' => 'nullable|url',
            'status' => 'nullable|string',
            'status_id' => 'nullable|exists:order_statuses,id',
            'admin_note' => 'nullable|string',
            'total_amount_bdt' => 'nullable|numeric|min:0',
            'min_payment_amount' => 'nullable|numeric|min:0',
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
