<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\Charge;
use App\Models\Setting;

class PricingService
{
    /**
     * Calculate the full breakdown of costs for a product request.
     */
    public function calculateBreakdown($productSubtotal, $currencyCode, $scope = 'hub', $additionalData = [])
    {
        // ── 1. Resolve currency ─────────────────────────────
        $currency = Currency::where('code', $currencyCode)->first();

        if (!$currency) {
            throw new \Exception("Invalid currency: {$currencyCode}");
        }

        $exchangeRate = (float) $currency->rate_to_base;
        $originalProductPrice = (float) $productSubtotal;

        // ── 2. Base product conversion ──────────────────────
        $productBDT = $originalProductPrice * $exchangeRate;

        // ── 3. DB Charges ───────────────────────────────────
        $dbCharges = Charge::where('is_active', true)
            ->where('currency_id', $currency->id)
            ->orderBy('sort_order')
            ->get();

        $weight = (float) ($additionalData['weight'] ?? 0);
        $chargesBreakdown = [];
        $dbChargesTotalBDT = 0;
        $weightChargeBDT = 0;

        foreach ($dbCharges as $charge) {
            // Calculate in original currency (e.g. USD), then convert once to BDT
            $amountInOriginalCurrency = $this->calculateChargeAmount($charge, $originalProductPrice, $weight);
            $amountInBDT = $amountInOriginalCurrency * $exchangeRate;

            $dbChargesTotalBDT += $amountInBDT;

            // Track weight-based charges separately
            if ($charge->type === 'weight') {
                $weightChargeBDT += $amountInBDT;
            }

            $chargesBreakdown[] = [
                'currency' => $currency->code,
                'charge' => $charge->name,
                'calculation_type' => $charge->calculation_type,
                'value' => $charge->value,
                'amount_in_currency' => round($amountInOriginalCurrency, 2),
                'amount_in_bdt' => round($amountInBDT, 2),
            ];
        }

        // ── 4. Shipping ──────────────────────────────────────
        $isInsideCity = $additionalData['is_inside_city'] ?? true;
        $isInternational = $additionalData['is_international'] ?? false;

        $deliveryChargeBDT = $isInsideCity
            ? (float) Setting::get('delivery_charge_inside_city', 0)
            : (float) Setting::get('delivery_charge_outside_city', 0);

        $intlShippingChargeBDT = 0;
        if ($isInternational) {
            $perKg = (float) Setting::get('shipping_charge_per_kg', 0);
            $intlShippingChargeBDT = $weight * $perKg;
        }

        $totalShippingBDT = $isInternational ? $intlShippingChargeBDT : $deliveryChargeBDT;

        // ── 5. Seller shipping ─────────────────────────────
        $sellerShippingBDT = (float) ($additionalData['seller_shipping_cost'] ?? 0) * $exchangeRate;

        // ── 6. Manual extras (passed as BDT) ──────────────
        $manualTaxBDT = (float) ($additionalData['tax'] ?? 0);
        $processingFeeBDT = (float) ($additionalData['payment_processing_fee'] ?? 0);
        $manualAdditionalBDT = (float) ($additionalData['additional_charges'] ?? 0);

        // ── 7. Grand total ───────────────────────────────────
        $grandTotalBDT =
            $productBDT
            + $dbChargesTotalBDT
            + $totalShippingBDT
            + $sellerShippingBDT
            + $manualTaxBDT
            + $processingFeeBDT
            + $manualAdditionalBDT;

        $grandTotalCurrency = $exchangeRate > 0 ? $grandTotalBDT / $exchangeRate : $grandTotalBDT;

        // ── 8. Min payment ───────────────────────────────────
        $minPercentKey = ($scope === 'request') ? 'min_payment_percentage_request' : 'min_payment_percentage_shop';
        $minPaymentPercent = (float) Setting::get($minPercentKey, 100);
        $minPaymentAmount = ($grandTotalBDT * $minPaymentPercent) / 100;

        // ── 9. Append shipping to breakdown ──────────────────
        if (!$isInternational) {
            $chargesBreakdown[] = [
                'currency' => 'BDT',
                'charge' => 'Delivery Charge',
                'calculation_type' => 'fixed',
                'value' => $deliveryChargeBDT,
                'amount_in_currency' => round($exchangeRate > 0 ? $deliveryChargeBDT / $exchangeRate : $deliveryChargeBDT, 2),
                'amount_in_bdt' => round($deliveryChargeBDT, 2),
            ];
        } else {
            $chargesBreakdown[] = [
                'currency' => 'BDT',
                'charge' => 'International Shipping',
                'calculation_type' => 'weight_based',
                'value' => Setting::get('shipping_charge_per_kg', 0),
                'amount_in_currency' => round($exchangeRate > 0 ? $intlShippingChargeBDT / $exchangeRate : $intlShippingChargeBDT, 2),
                'amount_in_bdt' => round($intlShippingChargeBDT, 2),
            ];
        }

        if ($sellerShippingBDT > 0) {
            $chargesBreakdown[] = [
                'currency' => $currency->code,
                'charge' => 'Seller Shipping',
                'calculation_type' => 'fixed',
                'value' => $additionalData['seller_shipping_cost'],
                'amount_in_currency' => round((float) ($additionalData['seller_shipping_cost'] ?? 0), 2),
                'amount_in_bdt' => round($sellerShippingBDT, 2),
            ];
        }

        return [
            'product_total_original' => round($productSubtotal, 2),
            'product_total_bdt' => round($productBDT, 2),
            'delivery_charge' => round($deliveryChargeBDT, 2),
            'weight_charge' => round($weightChargeBDT, 2),
            'international_shipping' => round($intlShippingChargeBDT, 2),
            'seller_shipping' => round($sellerShippingBDT, 2),
            'additional_charges_bdt' => round($dbChargesTotalBDT, 2),
            'tax' => round($manualTaxBDT, 2),
            'processing_fee' => round($processingFeeBDT, 2),
            'grand_total' => round($grandTotalBDT, 2),
            'grand_total_currency' => round($grandTotalCurrency, 3),
            'min_payment_amount' => round($minPaymentAmount, 2),
            'breakdown' => $chargesBreakdown,
        ];
    }

    private function calculateChargeAmount($charge, $productSubtotal, $weight = 0): float
    {
        if ($charge->type === 'weight') {
            return (float) $charge->value * max((float) $weight, 0);
        }

        if ($charge->calculation_type === 'fixed') {
            return (float) $charge->value;
        }

        $amount = $productSubtotal * (float) $charge->value / 100;

        if (!empty($charge->min_value)) {
            $amount = max($amount, (float) $charge->min_value);
        }
        if (!empty($charge->max_value)) {
            $amount = min($amount, (float) $charge->max_value);
        }

        return $amount;
    }
}