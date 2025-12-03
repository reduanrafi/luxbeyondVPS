<?php

namespace App\Http\Controllers;

use App\Models\ProductRequest;
use App\Models\Setting;
use App\Models\Currency;
use App\Models\Charge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductRequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user->hasRole('Admin')) {
            return response()->json(ProductRequest::with('user')->latest()->get());
        }
        return response()->json($user->productRequests()->latest()->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'currency' => 'required|string|exists:currencies,code',
            'declared_shipping_cost' => 'nullable|numeric|min:0',
            'is_inside_city' => 'boolean',
            'weight' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
        ]);

        // Get currency
        $currency = Currency::where('code', $request->currency)->first();
        if (!$currency) {
            return response()->json(['message' => 'Invalid currency'], 422);
        }

        // Calculate product total in base currency (BDT)
        $productTotal = ($request->price * $request->quantity);
        if (!$currency->is_base) {
            $productTotal = $productTotal * $currency->rate_to_base;
        }

        // Calculate charges - only charges in the selected currency or base currency
        $charges = Charge::where('is_active', true)
            ->whereHas('currency', function($q) use ($currency) {
                // Get charges in the selected currency OR base currency (which applies to all)
                $q->where('id', $currency->id)
                  ->orWhere('is_base', true);
            })
            ->with('currency')
            ->orderBy('sort_order')
            ->get();
        $totalCharges = 0;
        $chargeBreakdown = [];

        foreach ($charges as $charge) {
            // Convert product total to charge currency if needed
            $chargeBaseAmount = $productTotal;
            if (!$charge->currency->is_base && $currency->is_base) {
                $chargeBaseAmount = $productTotal / $charge->currency->rate_to_base;
            } elseif ($charge->currency->is_base && !$currency->is_base) {
                $chargeBaseAmount = $productTotal * $currency->rate_to_base;
            }

            $chargeAmount = $charge->calculate($chargeBaseAmount, [
                'weight' => $request->weight ?? 0,
                'is_inside_city' => $request->is_inside_city ?? false,
            ]);

            $totalCharges += $chargeAmount;
            $chargeBreakdown[] = [
                'name' => $charge->name,
                'type' => $charge->type,
                'amount' => $chargeAmount,
            ];
        }

        // Calculate delivery charges from settings (in BDT, no conversion needed)
        $isInsideCity = $request->is_inside_city ?? false;
        $weight = $request->weight ?? 0;
        $paymentMethod = $request->payment_method ?? null;
        $deliveryCharge = $this->calculateDeliveryCharge($productTotal, $isInsideCity, $weight, $paymentMethod);
        $totalCharges += $deliveryCharge;

        // Add declared shipping cost if provided (convert to base currency)
        $declaredShipping = $request->declared_shipping_cost ?? 0;
        if ($declaredShipping > 0 && !$currency->is_base) {
            $declaredShipping = $declaredShipping * $currency->rate_to_base;
        }

        // Calculate subtotal before payment processing fee
        $subtotalBeforePaymentFee = $productTotal + $totalCharges + $declaredShipping;

        // Calculate payment processing fee (based on grand total: product + all charges + delivery + declared shipping)
        $paymentProcessingFee = $this->calculatePaymentProcessingFee($subtotalBeforePaymentFee, $paymentMethod);
        $totalCharges += $paymentProcessingFee;

        $totalBDT = $productTotal + $totalCharges + $declaredShipping;

        // Calculate minimum payment amount for request orders
        $minPaymentPercentage = Setting::get('min_payment_percentage_request', 0);
        $minPaymentAmount = ($totalBDT * $minPaymentPercentage) / 100;

        $productRequest = ProductRequest::create([
            'user_id' => Auth::id(),
            'url' => $request->url,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'currency' => $request->currency,
            'declared_shipping_cost' => $request->declared_shipping_cost ?? 0,
            'is_inside_city' => $request->is_inside_city ?? false,
            'total_amount_bdt' => $totalBDT,
            'min_payment_amount' => $minPaymentAmount,
            'status' => 'pending',
        ]);

        return response()->json([
            'product_request' => $productRequest,
            'calculation' => [
                'product_total' => round($productTotal, 2),
                'charges' => $chargeBreakdown,
                'total_charges' => round($totalCharges, 2),
                'delivery_charge' => round($deliveryCharge, 2),
                'payment_processing_fee' => round($paymentProcessingFee, 2),
                'declared_shipping' => round($declaredShipping, 2),
                'grand_total' => round($totalBDT, 2),
            ]
        ], 201);
    }

    public function show($id)
    {
        $productRequest = ProductRequest::with('user')->findOrFail($id);
        $this->authorize('view', $productRequest);
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
            'admin_image_url' => 'nullable|url',
            'status' => 'required|string',
            'admin_note' => 'nullable|string',
        ]);

        $productRequest->update($request->only(['admin_image_url', 'status', 'admin_note']));

        return response()->json($productRequest);
    }

    public function confirm(Request $request, $id)
    {
        $productRequest = ProductRequest::findOrFail($id);
        
        if ($productRequest->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'transactionId' => 'required|string',
            'method' => 'required|string',
        ]);

        // Here you would save the transaction details to a Transaction model
        // For now, we just update the status
        $productRequest->update(['status' => 'paid']);

        return response()->json($productRequest);
    }

    /**
     * Calculate delivery charge from settings
     */
    private function calculateDeliveryCharge($totalAmount, $isInsideCity, $weight = 0, $paymentMethod = null)
    {
        // Get delivery charge settings
        $insideCityCharge = Setting::get('delivery_charge_inside_city', 0);
        $outsideCityCharge = Setting::get('delivery_charge_outside_city', 0);
        $freeDeliveryThreshold = Setting::get('free_delivery_threshold', 0);
        $chargePerKg = Setting::get('delivery_charge_per_kg', 0);

        // Check if order qualifies for free delivery
        if ($freeDeliveryThreshold > 0 && $totalAmount >= $freeDeliveryThreshold) {
            // Free delivery, but still calculate weight-based charges if applicable
            $weightCharge = $weight > 0 ? ($weight * $chargePerKg) : 0;
            return $weightCharge;
        }

        // Get base delivery charge based on location
        $baseCharge = $isInsideCity ? $insideCityCharge : $outsideCityCharge;

        // Add weight-based charge if applicable
        $weightCharge = $weight > 0 ? ($weight * $chargePerKg) : 0;

        // Check if payment method affects delivery charge
        $totalDeliveryCharge = $baseCharge + $weightCharge;

        // Payment method specific adjustments
        if ($paymentMethod) {
            $paymentMethodObj = \App\Models\PaymentMethod::where('type', $paymentMethod)
                ->orWhere('name', 'like', '%' . $paymentMethod . '%')
                ->where('is_active', true)
                ->first();
            
            // If payment method has specific delivery charge override in config, use it
            if ($paymentMethodObj && isset($paymentMethodObj->config['delivery_charge_override'])) {
                $totalDeliveryCharge = $paymentMethodObj->config['delivery_charge_override'] + $weightCharge;
            }
        }

        return $totalDeliveryCharge;
    }

    /**
     * Calculate payment processing fee from payment method
     */
    private function calculatePaymentProcessingFee($totalAmount, $paymentMethod = null)
    {
        if (!$paymentMethod) {
            return 0;
        }

        $paymentMethodObj = \App\Models\PaymentMethod::where('type', $paymentMethod)
            ->orWhere('name', 'like', '%' . $paymentMethod . '%')
            ->where('is_active', true)
            ->first();

        if (!$paymentMethodObj) {
            return 0;
        }

        $fee = 0;

        // If percentage fee is set, calculate from total amount
        if ($paymentMethodObj->fee_percentage && $paymentMethodObj->fee_percentage > 0) {
            $fee = ($totalAmount * $paymentMethodObj->fee_percentage) / 100;
        }
        // If fixed fee is set, use it
        elseif ($paymentMethodObj->fee && $paymentMethodObj->fee > 0) {
            $fee = $paymentMethodObj->fee;
        }

        return round($fee, 2);
    }
}
