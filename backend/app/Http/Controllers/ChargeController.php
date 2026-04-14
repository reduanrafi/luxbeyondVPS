<?php

namespace App\Http\Controllers;

use App\Models\Charge;
use Illuminate\Http\Request;
use App\Services\PricingService;

class ChargeController extends Controller
{
    protected $pricingService;

    public function __construct(PricingService $pricingService)
    {
        $this->pricingService = $pricingService;
    }
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
            'currency_id' => 'nullable|exists:currencies,id',
            'scope' => 'nullable|in:hub,request',
            'additional_data' => 'nullable|array',
        ]);

        $currency = null;
        if ($request->currency_id) {
            $currency = \App\Models\Currency::find($request->currency_id);
        }

        $additionalData = $request->additional_data ?? [];
        
        $result = $this->pricingService->calculateBreakdown(
            $request->base_amount, 
            $currency ? $currency->code : 'BDT',
            $request->scope ?? 'request',
            $additionalData
        );

        return response()->json([
            'base_amount' => $request->base_amount,
            'total_charges' => round($result['grand_total'] - $request->base_amount, 2),
            'grand_total' => round($result['grand_total'], 2),
            'breakdown' => $result['breakdown'],
            'delivery_charge' => round($result['delivery_charge'], 2),
            'payment_processing_fee' => round($result['processing_fee'], 2),
            'international_shipping' => round($result['international_shipping'] ?? 0, 2),
        ]);
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
