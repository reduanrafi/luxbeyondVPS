<?php

namespace App\Http\Controllers;

use App\Models\Charge;
use App\Models\Currency;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use App\Services\PricingService;

class ChargeController extends Controller
{
    protected PricingService $pricingService;

    public function __construct(PricingService $pricingService)
    {
        $this->pricingService = $pricingService;
    }

    public function index(Request $request)
    {
        $query = Charge::with('currency');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('currency_id')) {
            $query->where('currency_id', $request->currency_id);
        }

        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Return all (for dropdowns)
        if ($request->boolean('all')) {
            return response()->json(
                $query->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get()
            );
        }

        return response()->json(
            $query->orderBy('sort_order')->orderBy('name')->paginate($request->per_page ?? 15)
        );
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
        return response()->json(Charge::with('currency')->findOrFail($id));
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

        return response()->json(['message' => 'Charge updated successfully', 'charge' => $charge]);
    }

    public function destroy($id)
    {
        Charge::findOrFail($id)->delete();

        return response()->json(['message' => 'Charge deleted successfully']);
    }

    /**
     * Calculate charges for a product and return a full breakdown.
     *
     * Expected body:
     *  {
     *    "base_amount": 20,           // product price × quantity in original currency (e.g. USD)
     *    "currency_id": 1,            // currency of base_amount
     *    "scope": "request"|"hub",
     *    "additional_data": {
     *      "weight": 0.5,
     *      "is_inside_city": true,
     *      "is_international": false,
     *      "seller_shipping_cost": 0, // in original currency — backend converts to BDT
     *      "tax": 0,                  // already in BDT
     *      "payment_processing_fee": 0,
     *      "additional_charges": 0
     *    }
     *  }
     */
    public function calculate(Request $request)
    {
        $request->validate([
            'base_amount' => 'required|numeric|min:0',
            'currency_id' => 'nullable|exists:currencies,id',
            'scope' => 'nullable|in:hub,request',
            'additional_data' => 'nullable|array',
            // seller_shipping_cost is in the original currency, passed inside additional_data
        ]);

        $currencyCode = 'BDT';
        if ($request->filled('currency_id')) {
            $currency = Currency::findOrFail($request->currency_id);
            $currencyCode = $currency->code;
        }

        $additionalData = $request->input('additional_data', []);

        $result = $this->pricingService->calculateBreakdown(
            $request->base_amount,
            $currencyCode,
            $request->input('scope', 'request'),
            $additionalData
        );

        return response()->json([
            'base_amount' => $request->base_amount,
            'product_total_bdt' => $result['product_total_bdt'],
            'total_charges' => round($result['grand_total'] - $result['product_total_bdt'], 2),
            'delivery_charge' => $result['delivery_charge'],
            'international_shipping' => $result['international_shipping'],
            'seller_shipping' => $result['seller_shipping'],
            'tax' => $result['tax'],
            'processing_fee' => $result['processing_fee'],
            'grand_total' => $result['grand_total'],
            'grand_total_currency' => $result['grand_total_currency'],
            'min_payment_amount' => $result['min_payment_amount'],
            'breakdown' => $result['breakdown'],
        ]);
    }

    // ─── Payment helpers (used by other parts of the app) ────────────────────

    private function getPaymentMethod(string $paymentMethod): ?PaymentMethod
    {
        return PaymentMethod::where('is_active', true)
            ->where(function ($q) use ($paymentMethod) {
                $q->where('type', $paymentMethod)
                    ->orWhere('name', 'like', '%' . $paymentMethod . '%');
            })
            ->first();
    }

    private function calculatePaymentProcessingFee(float $totalAmount, ?string $paymentMethod = null): float
    {
        $method = $this->getPaymentMethod($paymentMethod ?? '');
        if (!$method)
            return 0.0;

        if ($method->fee_percentage > 0) {
            return round($totalAmount * $method->fee_percentage / 100, 2);
        }

        return round((float) ($method->fee ?? 0), 2);
    }

    private function getPaymentFeeType(?string $paymentMethod = null): string
    {
        $method = $this->getPaymentMethod($paymentMethod ?? '');
        if (!$method)
            return 'fixed';

        return ($method->fee_percentage && $method->fee_percentage > 0) ? 'percentage' : 'fixed';
    }

    private function getPaymentFeeValue(?string $paymentMethod = null): float
    {
        $method = $this->getPaymentMethod($paymentMethod ?? '');
        if (!$method)
            return 0.0;

        return (float) ($method->fee_percentage ?? $method->fee ?? 0);
    }
}