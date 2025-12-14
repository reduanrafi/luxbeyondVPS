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
            'currency_id' => 'nullable|exists:currencies,id', // Made nullable
            'scope' => 'nullable|in:hub,request',
            'charge_types' => 'nullable|array',
            'additional_data' => 'nullable|array',
        ]);

        $currency = null;
        if ($request->currency_id) {
            $currency = \App\Models\Currency::find($request->currency_id);
        }
        
        $scope = $request->scope ?? 'request';

        // Build Query
        $query = Charge::where('is_active', true);
        
        if ($currency) {
            $query->whereHas('currency', function($q) use ($currency) {
                // Get charges in the selected currency OR base currency
                $q->where('id', $currency->id)
                  ->orWhere('is_base', true);
            });
        }
        // If no currency, maybe return no database charges or just base ones?
        // For 'hub', we mostly use settings, so it's fine.

        // Scope Filter
        if ($scope === 'hub') {
             $query->where('type', 'hub');
        } else {
            // For Product Requests:
            // Fetch 'request' type charges AND any specific charges for the selected currency
            $query->where(function ($q) use ($request, $currency) {
                // ALWAYS include 'request' type charges (global BDT fees etc)
                $q->where('type', 'request');

                // If specific charge types requested
                if ($request->has('charge_types') && !empty($request->charge_types)) {
                    $q->orWhereIn('type', $request->charge_types);
                }
                
                // CRITICAL: Include ALL charges that belong to the selected currency
                // (User requirement: "all charges from the charge table should be added for the selected currency")
                if ($currency) {
                    $q->orWhere('currency_id', $currency->id);
                }
            });
            
            // Safety: Never include 'hub' charges in requests
            $query->where('type', '!=', 'hub');
        }

        $charges = $query->with('currency')->orderBy('sort_order')->get();

        $totalCharges = 0;
        $chargeBreakdown = [];

        // Note: base_amount from frontend is already in BDT for requests, usually.
        // We need to convert it back to the selected currency for percentage calculations if needed.
        $baseAmountInSelectedCurrency = $request->base_amount;
        if ($currency && !$currency->is_base && $currency->rate_to_base > 0) {
            $baseAmountInSelectedCurrency = $request->base_amount / $currency->rate_to_base;
        }

        foreach ($charges as $charge) {
            // Determine the base amount to use for calculation in the charge's currency
            $chargeBaseAmount = $request->base_amount; 
            
            if ($currency) {
                if ($charge->currency->id === $currency->id) {
                    $chargeBaseAmount = $baseAmountInSelectedCurrency;
                } elseif ($charge->currency->is_base) {
                    $chargeBaseAmount = $request->base_amount;
                } else {
                    if (!$currency->is_base) {
                        $chargeBaseAmount = ($baseAmountInSelectedCurrency * $currency->rate_to_base) / $charge->currency->rate_to_base;
                    } else {
                        $chargeBaseAmount = $request->base_amount / $charge->currency->rate_to_base;
                    }
                }
            } else {
               // No target currency, just use base amount assume BDT/Base
               if (!$charge->currency->is_base) {
                   // naive conversion if charge is foreign but request is base?
                   // Assuming request is base currency if currency_id is null
                   $chargeBaseAmount = $request->base_amount / $charge->currency->rate_to_base;
               }
            }

            // Calculate charge
            $chargeAmountInCurrency = 0;
            if ($charge->calculation_type === 'fixed') {
                $chargeAmountInCurrency = $charge->value;
            } elseif ($charge->calculation_type === 'percentage') {
                $chargeAmountInCurrency = ($chargeBaseAmount * $charge->value) / 100;
            }

            // Min/Max constraints
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

        // --- Additional Logic for Delivery & Payment Fees ---
        $deliveryCharge = 0;
        $paymentProcessingFee = 0;

        // Delivery Charge Logic (Usually for both, but definitely for 'hub' or if requested)
        // For 'hub' (Shop), we always calculate delivery if it's not a purely digital/service order (assuming physical)
        // For 'request', we usually rely on declared shipping, BUT user might want system delivery charge too.
        // Let's keep it: Calculate if additional_data provided.

        $additionalData = $request->additional_data ?? [];
        $isInsideCity = $additionalData['is_inside_city'] ?? false;
        $weight = $additionalData['weight'] ?? 0;
        $paymentMethod = $additionalData['payment_method'] ?? null;
        
        $deliveryCharge = $this->calculateDeliveryCharge($request->base_amount, $isInsideCity, $weight, $paymentMethod);

        // Add delivery to total
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

        // Payment Processing Fee
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
        $insideCityCharge = (float) \App\Models\Setting::get('delivery_charge_inside_city', 0);
        $outsideCityCharge = (float) \App\Models\Setting::get('delivery_charge_outside_city', 0);
        $freeDeliveryThreshold = (float) \App\Models\Setting::get('free_delivery_threshold', 0);
        $deliveryChargePerKg = (float) \App\Models\Setting::get('delivery_charge_per_kg', 0);

        \Illuminate\Support\Facades\Log::info('Calculating Delivery Charge', [
            'totalAmount' => $totalAmount,
            'isInsideCity' => $isInsideCity,
            'weight' => $weight,
            'insideCityCharge' => $insideCityCharge,
            'outsideCityCharge' => $outsideCityCharge,
            'freeDeliveryThreshold' => $freeDeliveryThreshold,
            'deliveryChargePerKg' => $deliveryChargePerKg,
        ]);

        // Check if order qualifies for free delivery
        if ($freeDeliveryThreshold > 0 && $totalAmount >= $freeDeliveryThreshold) {
            return 0; // Free delivery
        }

        // Base delivery charge based on location
        $deliveryCharge = $isInsideCity ? $insideCityCharge : $outsideCityCharge;

        // Add weight-based charge (if applicable)
        if ($weight > 1 && $deliveryChargePerKg > 0) {
            // First 1kg is covered by base charge, charge for extra weight
            $extraWeight = ceil($weight - 1);
            if ($extraWeight > 0) {
                $deliveryCharge += ($extraWeight * $deliveryChargePerKg);
            }
        }

        return $deliveryCharge;
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
