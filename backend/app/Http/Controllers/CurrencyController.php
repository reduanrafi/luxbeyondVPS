<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function index(Request $request)
    {
        $query = Currency::query();

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('code', 'like', '%' . $request->search . '%')
                  ->orWhere('name', 'like', '%' . $request->search . '%');
            });
        }

        // Status filter
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Check if requesting all currencies (for dropdowns)
        if ($request->has('all') && $request->all) {
            return response()->json($query->where('is_active', true)->orderBy('sort_order')->orderBy('code')->get());
        }

        // Paginated response
        $currencies = $query->orderBy('sort_order')->orderBy('code')->paginate($request->per_page ?? 15);
        return response()->json($currencies);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:3|unique:currencies,code',
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:10',
            'rate_to_base' => 'required|numeric|min:0',
            'is_base' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        // If setting as base currency, update other currencies
        if ($validated['is_base'] ?? false) {
            Currency::where('is_base', true)->update(['is_base' => false]);
            $validated['rate_to_base'] = 1;
        }

        $currency = Currency::create($validated);
        return response()->json($currency, 201);
    }

    public function show($id)
    {
        $currency = Currency::with('charges')->findOrFail($id);
        return response()->json($currency);
    }

    public function update(Request $request, $id)
    {
        $currency = Currency::findOrFail($id);

        $validated = $request->validate([
            'code' => 'sometimes|string|max:3|unique:currencies,code,' . $id,
            'name' => 'sometimes|string|max:255',
            'symbol' => 'sometimes|string|max:10',
            'rate_to_base' => 'sometimes|numeric|min:0',
            'is_base' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        // If setting as base currency, update other currencies
        if (isset($validated['is_base']) && $validated['is_base']) {
            Currency::where('is_base', true)->where('id', '!=', $id)->update(['is_base' => false]);
            $validated['rate_to_base'] = 1;
        }

        $currency->update($validated);

        return response()->json([
            'message' => 'Currency updated successfully',
            'currency' => $currency
        ]);
    }

    public function destroy($id)
    {
        $currency = Currency::findOrFail($id);
        
        // Check if currency has charges
        if ($currency->charges()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete currency with existing charges'
            ], 422);
        }

        // Don't allow deleting base currency
        if ($currency->is_base) {
            return response()->json([
                'message' => 'Cannot delete base currency'
            ], 422);
        }

        $currency->delete();

        return response()->json([
            'message' => 'Currency deleted successfully'
        ]);
    }
}
