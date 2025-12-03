<?php

namespace App\Http\Controllers;

use App\Models\Charge;
use Illuminate\Http\Request;

class ChargeController extends Controller
{
    public function index(Request $request)
    {
        $query = Charge::with('currency');

        // Search
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Type filter
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Currency filter
        if ($request->has('currency_id') && $request->currency_id) {
            $query->where('currency_id', $request->currency_id);
        }

        // Status filter
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Check if requesting all charges (for dropdowns)
        if ($request->has('all') && $request->all) {
            return response()->json($query->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get());
        }

        // Paginated response
        $charges = $query->orderBy('sort_order')->orderBy('name')->paginate($request->per_page ?? 15);
        return response()->json($charges);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'currency_id' => 'required|exists:currencies,id',
            'calculation_type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0',
            'min_value' => 'nullable|numeric|min:0',
            'max_value' => 'nullable|numeric|min:0',
            'conditions' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'description' => 'nullable|string',
        ]);

        $charge = Charge::create($validated);
        $charge->load('currency');

        return response()->json($charge, 201);
    }

    public function show($id)
    {
        $charge = Charge::with('currency')->findOrFail($id);
        return response()->json($charge);
    }

    public function update(Request $request, $id)
    {
        $charge = Charge::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|max:255',
            'currency_id' => 'sometimes|exists:currencies,id',
            'calculation_type' => 'sometimes|in:fixed,percentage',
            'value' => 'sometimes|numeric|min:0',
            'min_value' => 'nullable|numeric|min:0',
            'max_value' => 'nullable|numeric|min:0',
            'conditions' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'description' => 'nullable|string',
        ]);

        $charge->update($validated);
        $charge->load('currency');

        return response()->json([
            'message' => 'Charge updated successfully',
            'charge' => $charge
        ]);
    }

    public function destroy($id)
    {
        $charge = Charge::findOrFail($id);
        $charge->delete();

        return response()->json([
            'message' => 'Charge deleted successfully'
        ]);
    }

    /**
     * Calculate charges for a product request
     */
    public function calculate(Request $request)
    {
        $request->validate([
            'base_amount' => 'required|numeric|min:0',
            'currency_id' => 'required|exists:currencies,id',
            'charge_types' => 'nullable|array',
            'additional_data' => 'nullable|array', // weight, distance, payment_method, etc.
        ]);

        $currency = \App\Models\Currency::findOrFail($request->currency_id);
        $additionalData = $request->additional_data ?? [];
        $isInsideCity = $additionalData['is_inside_city'] ?? false;
        $weight = $additionalData['weight'] ?? 0;
        $paymentMethod = $additionalData['payment_method'] ?? null;
        
        // Get active charges that match the selected currency
        // Charges should be in the same currency as the selected currency, or in base currency (which applies to all)
        $query = Charge::where('is_active', true)
            ->whereHas('currency', function($q) use ($currency) {
                // Get charges in the selected currency OR base currency (which applies to all currencies)
                $q->where('id', $currency->id)
                  ->orWhere('is_base', true);
            });
        
        if ($request->has('charge_types') && !empty($request->charge_types)) {
            $query->whereIn('type', $request->charge_types);
        }

        $charges = $query->with('currency')->orderBy('sort_order')->get();
        $totalCharges = 0;
        $chargeBreakdown = [];

        // Note: base_amount from frontend is already in BDT
        // We need to convert it back to the selected currency for percentage calculations
        $baseAmountInSelectedCurrency = $request->base_amount;
        if (!$currency->is_base) {
            // Convert BDT back to selected currency (e.g., 5118.72 BDT / 128 = 39.99 USD)
            $baseAmountInSelectedCurrency = $request->base_amount / $currency->rate_to_base;
        }

        foreach ($charges as $charge) {
            // Determine the base amount to use for calculation in the charge's currency
            $chargeBaseAmount = 0;
            
            if ($charge->currency->id === $currency->id) {
                // Charge is in same currency as selected product - use selected currency amount
                // Example: Product in USD, Charge in USD -> use USD amount (39.99)
                $chargeBaseAmount = $baseAmountInSelectedCurrency;
            } elseif ($charge->currency->is_base) {
                // Charge is in base currency (BDT) - use BDT amount directly
                // Example: Product in USD, Charge in BDT -> use BDT amount (5118.72)
                $chargeBaseAmount = $request->base_amount;
            } else {
                // Charge is in a different currency than selected
                // Convert from selected currency to charge currency
                if (!$currency->is_base) {
                    // Selected currency to charge currency via BDT
                    // Example: Product in USD, Charge in EUR
                    // USD amount -> BDT -> EUR amount
                    $chargeBaseAmount = ($baseAmountInSelectedCurrency * $currency->rate_to_base) / $charge->currency->rate_to_base;
                } else {
                    // Selected is base (BDT), convert to charge currency
                    $chargeBaseAmount = $request->base_amount / $charge->currency->rate_to_base;
                }
            }

            // Calculate charge in its own currency first
            $chargeAmountInCurrency = 0;
            if ($charge->calculation_type === 'fixed') {
                $chargeAmountInCurrency = $charge->value;
            } elseif ($charge->calculation_type === 'percentage') {
                $chargeAmountInCurrency = ($chargeBaseAmount * $charge->value) / 100;
            }

            // Apply min/max constraints
            if ($charge->min_value !== null && $chargeAmountInCurrency < $charge->min_value) {
                $chargeAmountInCurrency = $charge->min_value;
            }
            if ($charge->max_value !== null && $chargeAmountInCurrency > $charge->max_value) {
                $chargeAmountInCurrency = $charge->max_value;
            }

            // Convert to base currency (BDT) for total
            $chargeAmountBDT = $chargeAmountInCurrency;
            if (!$charge->currency->is_base) {
                $chargeAmountBDT = $chargeAmountInCurrency * $charge->currency->rate_to_base;
            }

            $totalCharges += $chargeAmountBDT;
            
            $chargeBreakdown[] = [
                'charge' => $charge->name,
                'type' => $charge->type,
                'amount_in_currency' => round($chargeAmountInCurrency, 2),
                'amount_in_bdt' => round($chargeAmountBDT, 2),
                'currency' => $charge->currency->code,
                'currency_symbol' => $charge->currency->symbol,
                'calculation_type' => $charge->calculation_type,
                'value' => $charge->value,
                'rate_to_base' => $charge->currency->is_base ? 1 : $charge->currency->rate_to_base,
            ];
        }

        // Calculate delivery charges from settings (in BDT, no conversion needed)
        $additionalData = $request->additional_data ?? [];
        $isInsideCity = $additionalData['is_inside_city'] ?? false;
        $weight = $additionalData['weight'] ?? 0;
        $paymentMethod = $additionalData['payment_method'] ?? null;
        
        $deliveryCharge = $this->calculateDeliveryCharge($request->base_amount, $isInsideCity, $weight, $paymentMethod);
        if ($deliveryCharge > 0) {
            $totalCharges += $deliveryCharge;
            $chargeBreakdown[] = [
                'charge' => 'Delivery Charge',
                'type' => 'delivery',
                'amount_in_currency' => round($deliveryCharge, 2),
                'amount_in_bdt' => round($deliveryCharge, 2),
                'currency' => 'BDT',
                'currency_symbol' => '৳',
                'calculation_type' => 'fixed',
                'value' => $deliveryCharge,
                'rate_to_base' => 1,
            ];
        }

        // Calculate payment processing fee (based on grand total: product + all charges + delivery)
        $subtotalBeforePaymentFee = $request->base_amount + $totalCharges;
        $paymentProcessingFee = $this->calculatePaymentProcessingFee($subtotalBeforePaymentFee, $paymentMethod);
        if ($paymentProcessingFee > 0) {
            $totalCharges += $paymentProcessingFee;
            $chargeBreakdown[] = [
                'charge' => 'Payment Processing Fee',
                'type' => 'payment_fee',
                'amount_in_currency' => round($paymentProcessingFee, 2),
                'amount_in_bdt' => round($paymentProcessingFee, 2),
                'currency' => 'BDT',
                'currency_symbol' => '৳',
                'calculation_type' => $this->getPaymentFeeType($paymentMethod),
                'value' => $this->getPaymentFeeValue($paymentMethod),
                'rate_to_base' => 1,
            ];
        }

        return response()->json([
            'base_amount' => $request->base_amount,
            'total_charges' => round($totalCharges, 2),
            'grand_total' => round($request->base_amount + $totalCharges, 2),
            'breakdown' => $chargeBreakdown,
            'delivery_charge' => round($deliveryCharge, 2),
            'payment_processing_fee' => round($paymentProcessingFee, 2),
        ]);
    }

    /**
     * Calculate delivery charge from settings
     */
    private function calculateDeliveryCharge($totalAmount, $isInsideCity, $weight = 0, $paymentMethod = null)
    {
        // Get delivery charge settings
        $insideCityCharge = \App\Models\Setting::get('delivery_charge_inside_city', 0);
        $outsideCityCharge = \App\Models\Setting::get('delivery_charge_outside_city', 0);
        $freeDeliveryThreshold = \App\Models\Setting::get('free_delivery_threshold', 0);
        $chargePerKg = \App\Models\Setting::get('delivery_charge_per_kg', 0);

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
        // Some payment methods might have different delivery charges
        $totalDeliveryCharge = $baseCharge + $weightCharge;

        // Payment method specific adjustments (if needed in future)
        // Example: Cash on delivery might have higher charge
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

    /**
     * Get payment fee type (percentage or fixed)
     */
    private function getPaymentFeeType($paymentMethod = null)
    {
        if (!$paymentMethod) {
            return 'fixed';
        }

        $paymentMethodObj = \App\Models\PaymentMethod::where('type', $paymentMethod)
            ->orWhere('name', 'like', '%' . $paymentMethod . '%')
            ->where('is_active', true)
            ->first();

        if (!$paymentMethodObj) {
            return 'fixed';
        }

        return ($paymentMethodObj->fee_percentage && $paymentMethodObj->fee_percentage > 0) ? 'percentage' : 'fixed';
    }

    /**
     * Get payment fee value
     */
    private function getPaymentFeeValue($paymentMethod = null)
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

        return $paymentMethodObj->fee_percentage ?? $paymentMethodObj->fee ?? 0;
    }
}
