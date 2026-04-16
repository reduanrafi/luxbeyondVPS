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

use App\Services\PricingService;
use App\Services\OrderService;

class ProductRequestController extends Controller
{
    protected $pricingService;
    protected $orderService;

    public function __construct(PricingService $pricingService, OrderService $orderService)
    {
        $this->pricingService = $pricingService;
        $this->orderService = $orderService;
    }
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

        // Calculate breakdown using PricingService
        $breakdown = $this->pricingService->calculateBreakdown(
            $request->price * $request->quantity,
            $request->currency,
            'request',
            [
                'is_inside_city' => $isInsideCity,
                'weight' => $request->weight,
                'seller_shipping_cost' => $request->declared_shipping_cost,
                'tax' => $request->tax ?? 0,
                'payment_processing_fee' => $request->payment_processing_fee ?? 0,
                'additional_charges' => $request->additional_charges ?? 0,
            ]
        );

        $productRequest = ProductRequest::create([
            'user_id' => Auth::id(),
            'url' => $request->url,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'currency' => $request->currency,
            'declared_shipping_cost' => $shippingCost,
            'is_inside_city' => $isInsideCity,
            'weight' => $request->weight,
            'status' => 'pending', 
            'payment_status' => 'pending',
            'product_name' => $request->product_name ?? null,
            'total_amount_bdt' => $breakdown['grand_total'],
            'tax' => $breakdown['tax'],
            'delivery_charge' => $breakdown['delivery_charge'],
            'payment_processing_fee' => $breakdown['processing_fee'],
            'charges_breakdown' => $breakdown['breakdown'],
            'min_payment_amount' => $breakdown['min_payment_amount'],
            'weight_charge' => $breakdown['weight_charge']
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
            $productRequest = $this->pricingService->scalePricing($productRequest, $newQuantity);
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

        $order = $this->orderService->convertRequestToOrder($productRequest);

        // Notify User
        $productRequest->user->notify(new \App\Notifications\OrderPlacedNotification($order));

        return response()->json([
            'message' => 'Request converted to order successfully',
            'order_id' => $order->id,
            'order_number' => $order->order_number
        ]);

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
            $order = $this->orderService->convertRequestToOrder($productRequest, [
                'payment_method' => $validated['payment_method'],
                'shipping_address' => $validated['shipping_address'],
                'shipping_name' => $validated['shipping_address']['name'] ?? null,
                'shipping_phone' => $validated['shipping_address']['phone'] ?? null,
            ]);

            // Handle manual payment slip (bank transfer)
            if ($request->hasFile('payment_slip')) {
                $file = $request->file('payment_slip');
                $folder = 'payment_slips';
                $filename = $order->order_number . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs($folder, $filename, 'public');
                $order->update([
                    'payment_slip' => $folder . '/' . $filename,
                    'payment_status' => 'pending',
                ]);
            }

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
            Log::error('Order Creation Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create order: ' . $e->getMessage()], 500);
        }
    }
}
