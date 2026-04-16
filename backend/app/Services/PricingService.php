<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\Charge;
use App\Models\Setting;

class PricingService
{
    public function calculateBreakdown($productSubtotal, $currencyCode, $scope = 'hub', $additionalData = [])
    {
        // ── 1. Resolve currency ─────────────────────────────
        $currency = Currency::where('code', $currencyCode)->first();

        if (!$currency) {
            throw new \Exception("Invalid currency: {$currencyCode}");
        }

        $rate = (float) $currency->rate_to_base;

        // ── 2. Base product conversion ──────────────────────
        $productBDT = $productSubtotal * $rate;

        // ── 3. Charges (DB based) ───────────────────────────
        $dbCharges = Charge::where('is_active', true)
            ->where('currency_id', $currency->id)
            ->orderBy('sort_order')
            ->get();

        $chargesBreakdown = [];
        $dbChargesTotalBDT = 0;

        foreach ($dbCharges as $charge) {

            // Base calculation in ORIGINAL currency
            $amountInCurrency = $this->calculateChargeAmount($charge, $productSubtotal);

            // Convert once to BDT
            $amountInBDT = $amountInCurrency * $rate;

            $dbChargesTotalBDT += $amountInBDT;

            $chargesBreakdown[] = [
                'currency' => $currency->code,
                'charge' => $charge->name,
                'calculation_type' => $charge->calculation_type,
                'value' => $charge->value,
                'amount_in_currency' => round($amountInCurrency, 2),
                'amount_in_bdt' => round($amountInBDT, 2),
            ];
        }

        // ── 4. Shipping logic (FIXED: no double counting) ───
        $isInsideCity = $additionalData['is_inside_city'] ?? true;
        $weight = (float) ($additionalData['weight'] ?? 0);
        $isInternational = $additionalData['is_international'] ?? false;

        // Local shipping
        $deliveryChargeBDT = $isInsideCity
            ? (float) Setting::get('delivery_charge_inside_city', 0)
            : (float) Setting::get('delivery_charge_outside_city', 0);

        $weightChargeBDT = 0;
        if ($weight > 1) {
            $perKgBDT = (float) Setting::get('delivery_charge_per_kg', 0);
            $weightChargeBDT = ($weight - 1) * $perKgBDT;
        }

        // International shipping (ONLY if flagged)
        $intlShippingChargeBDT = 0;
        if ($isInternational) {
            $perKg = (float) Setting::get('shipping_charge_per_kg', 0);
            $intlShippingChargeBDT = $weight * $perKg;
        }

        $totalShippingBDT = $isInternational
            ? $intlShippingChargeBDT
            : ($deliveryChargeBDT + $weightChargeBDT);

        // Seller shipping (original currency → BDT)
        $sellerShippingBDT = (float) ($additionalData['seller_shipping_cost'] ?? 0) * $rate;

        // ── 5. Manual extras (already BDT) ──────────────────
        $manualTaxBDT = (float) ($additionalData['tax'] ?? 0);
        $processingFeeBDT = (float) ($additionalData['payment_processing_fee'] ?? 0);
        $manualAdditionalBDT = (float) ($additionalData['additional_charges'] ?? 0);

        // ── 6. Grand total ───────────────────────────────────
        $grandTotalBDT =
            $productBDT +
            $dbChargesTotalBDT +
            $totalShippingBDT +
            $sellerShippingBDT +
            $manualTaxBDT +
            $processingFeeBDT +
            $manualAdditionalBDT;

        $grandTotalCurrency = $rate > 0
            ? $grandTotalBDT / $rate
            : $grandTotalBDT;

        // ── 7. Min payment ──────────────────────────────────
        $minPaymentPercent = (float) Setting::get(
            $scope === 'request'
            ? 'min_payment_percentage_request'
            : 'min_payment_percentage_shop',
            100
        );

        $minPaymentAmount = ($grandTotalBDT * $minPaymentPercent) / 100;

        // ── 8. Add shipping breakdown ───────────────────────
        if (!$isInternational) {
            $chargesBreakdown[] = [
                'currency' => 'BDT',
                'charge' => 'Delivery Charge',
                'calculation_type' => 'fixed',
                'value' => $deliveryChargeBDT,
                'amount_in_currency' => round($deliveryChargeBDT / $rate, 2),
                'amount_in_bdt' => round($deliveryChargeBDT, 2),
            ];

            if ($weightChargeBDT > 0) {
                $chargesBreakdown[] = [
                    'currency' => 'BDT',
                    'charge' => 'Extra Weight Charge',
                    'calculation_type' => 'weight_based',
                    'value' => Setting::get('delivery_charge_per_kg', 0),
                    'amount_in_currency' => round($weightChargeBDT / $rate, 2),
                    'amount_in_bdt' => round($weightChargeBDT, 2),
                ];
            }
        } else {
            $chargesBreakdown[] = [
                'currency' => 'BDT',
                'charge' => 'International Shipping',
                'calculation_type' => 'weight_based',
                'value' => Setting::get('shipping_charge_per_kg', 0),
                'amount_in_currency' => round($intlShippingChargeBDT / $rate, 2),
                'amount_in_bdt' => round($intlShippingChargeBDT, 2),
            ];
        }

        // ── 9. Return (UNCHANGED STRUCTURE) ─────────────────
        return [
            'product_total_original' => round($productSubtotal, 2),
            'product_total_bdt' => round($productBDT, 2),
            'delivery_charge' => round($deliveryChargeBDT, 2),
            'weight_charge' => round($weightChargeBDT, 2),
            'international_shipping' => round($intlShippingChargeBDT, 2),
            'additional_charges_bdt' => round($dbChargesTotalBDT, 2),
            'grand_total' => round($grandTotalBDT, 2),
            'grand_total_currency' => round($grandTotalCurrency, 3),
            'min_payment_amount' => round($minPaymentAmount, 2),
            'breakdown' => $chargesBreakdown,
            'tax' => round($manualTaxBDT, 2),
            'processing_fee' => round($processingFeeBDT, 2),
        ];
    }

    /**
     * Pure charge calculator (isolated logic)
     */
    private function calculateChargeAmount($charge, $productSubtotal)
    {
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