<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\Charge;
use App\Models\Setting;

class PricingService
{
    /**
     * Calculate cost breakdown for a given base amount and currency.
     */
    public function calculateBreakdown($productSubtotal, $currencyCode, $scope = 'hub', $additionalData = [])
    {
        $currency = Currency::where('code', $currencyCode)->first();
        if (!$currency) {
            $currency = Currency::where('is_base', true)->first();
        }

        $rateToBase = $currency->rate_to_base ?? 1;
        $productTotalBDT = $productSubtotal * $rateToBase;

        $charges = Charge::where('is_active', true)
            ->where('currency_id', $currency->id)
            ->orderBy('sort_order')
            ->get();

        $chargesBreakdown = [];
        $additionalChargesTotalBDT = 0;

        foreach ($charges as $charge) {
            $chargeAmountBDT = $charge->calculate($productSubtotal);
            $additionalChargesTotalBDT += $chargeAmountBDT;

            $chargesBreakdown[] = [
                'currency' => $currency->code,
                'charge' => $charge->name,
                'calculation_type' => $charge->calculation_type,
                'value' => $charge->value,
                'amount_in_currency' => $charge->calculation_type === 'fixed' ? $charge->value : ($productSubtotal * $charge->value / 100),
                'amount_in_bdt' => $chargeAmountBDT,
            ];
        }

        // Local Delivery Charges (Inside/Outside City)
        $isInsideCity = $additionalData['is_inside_city'] ?? true;
        $weight = (float)($additionalData['weight'] ?? 0);
        
        $deliveryCharge = $isInsideCity 
            ? Setting::get('delivery_charge_inside_city', 0)
            : Setting::get('delivery_charge_outside_city', 0);
            
        // Extra weight charge (typically for local delivery above 1kg)
        $weightCharge = 0;
        if ($weight > 1) {
            $perKgCharge = Setting::get('delivery_charge_per_kg', 0);
            // CALCULATE FRACTIONAL: (.6kg extra should be .6 * perKgCharge)
            $weightCharge = ($weight - 1) * $perKgCharge;
        }
        
        // International Shipping Charge (based on total weight)
        $shippingChargePerKg = Setting::get('shipping_charge_per_kg', 0);
        $internationalShippingCharge = $weight * $shippingChargePerKg;

        $finalDeliveryCharge = $deliveryCharge + $weightCharge + $internationalShippingCharge;

        // Manual shipping cost (already in BDT or needs conversion?)
        // Usually seller_shipping_cost is provided in the target currency
        $sellerShippingCost = $additionalData['seller_shipping_cost'] ?? 0;
        $sellerShippingCostBDT = $sellerShippingCost * $rateToBase;

        $tax = $additionalData['tax'] ?? 0;
        $processingFee = $additionalData['payment_processing_fee'] ?? 0;
        $manualAdditional = $additionalData['additional_charges'] ?? 0;

        $grandTotal = $productTotalBDT + $finalDeliveryCharge + $tax + $additionalChargesTotalBDT + $manualAdditional + $processingFee + $sellerShippingCostBDT;

        $minPaymentPercent = Setting::get($scope === 'request' ? 'min_payment_percentage_request' : 'min_payment_percentage_shop', 100);
        $minPaymentAmount = ($grandTotal * $minPaymentPercent) / 100;

        return [
            'product_total_bdt' => $productTotalBDT,
            'delivery_charge' => $finalDeliveryCharge,
            'weight_charge' => $weightCharge,
            'international_shipping' => $internationalShippingCharge,
            'additional_charges_bdt' => $additionalChargesTotalBDT,
            'grand_total' => $grandTotal,
            'min_payment_amount' => $minPaymentAmount,
            'breakdown' => $chargesBreakdown,
            'tax' => $tax,
            'processing_fee' => $processingFee,
        ];
    }

    /**
     * Scale all pricing components of a ProductRequest or OrderItem by a ratio.
     */
    public function scalePricing($model, $newQuantity)
    {
        $oldQuantity = (float)$model->quantity;
        if ($oldQuantity <= 0 || $oldQuantity == $newQuantity) {
            return $model;
        }

        $ratio = (float)$newQuantity / $oldQuantity;

        if (isset($model->tax)) $model->tax = (float)$model->tax * $ratio;
        if (isset($model->delivery_charge)) $model->delivery_charge = (float)$model->delivery_charge * $ratio;
        if (isset($model->additional_charges)) $model->additional_charges = (float)$model->additional_charges * $ratio;
        if (isset($model->payment_processing_fee)) $model->payment_processing_fee = (float)$model->payment_processing_fee * $ratio;
        if (isset($model->declared_shipping_cost)) $model->declared_shipping_cost = (float)$model->declared_shipping_cost * $ratio;
        if (isset($model->weight_charge)) $model->weight_charge = (float)$model->weight_charge * $ratio;
        if (isset($model->international_shipping)) $model->international_shipping = (float)$model->international_shipping * $ratio;

        // Scale charges breakdown
        if (!empty($model->charges_breakdown)) {
            $breakdown = is_string($model->charges_breakdown) ? json_decode($model->charges_breakdown, true) : $model->charges_breakdown;
            if (is_array($breakdown)) {
                $updatedBreakdown = [];
                foreach ($breakdown as $charge) {
                    if (isset($charge['amount_in_currency'])) $charge['amount_in_currency'] *= $ratio;
                    if (isset($charge['amount_in_bdt'])) $charge['amount_in_bdt'] *= $ratio;
                    if (isset($charge['amount'])) $charge['amount'] *= $ratio;
                    $updatedBreakdown[] = $charge;
                }
                $model->charges_breakdown = $updatedBreakdown;
            }
        }

        $model->total_amount_bdt *= $ratio;
        
        // Recalculate min payment based on settings
        $scope = ($model instanceof \App\Models\ProductRequest) ? 'request' : 'shop';
        $minPaymentPercent = Setting::get($scope === 'request' ? 'min_payment_percentage_request' : 'min_payment_percentage_shop', 100);
        $model->min_payment_amount = ($model->total_amount_bdt * $minPaymentPercent) / 100;

        $model->quantity = $newQuantity;
        return $model;
    }
}
