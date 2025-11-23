<?php

namespace App\Http\Controllers;

use App\Models\ProductRequest;
use App\Models\Setting;
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
            'currency' => 'required|string|in:USD,CNY,BDT',
            'declared_shipping_cost' => 'nullable|numeric|min:0',
            'is_inside_city' => 'boolean',
        ]);

        // Calculate estimated total in BDT
        $rateKey = 'currency_rate_' . strtolower($request->currency);
        $rate = Setting::where('key', $rateKey)->value('value') ?? 1;
        
        if ($request->currency === 'BDT') {
            $rate = 1;
        }

        $productTotal = ($request->price * $request->quantity) * $rate;
        $shipping = $request->declared_shipping_cost ?? 0; // This might need conversion too if in foreign currency, assuming BDT for now or same currency
        // Assuming declared shipping is in the same currency as product for simplicity, or BDT. 
        // Let's assume user inputs shipping in BDT or it's calculated later. 
        // For now, let's just store the raw values and calculate total_amount_bdt roughly.
        
        $totalBDT = $productTotal + ($shipping * ($request->currency === 'BDT' ? 1 : $rate));

        $productRequest = ProductRequest::create([
            'user_id' => Auth::id(),
            'url' => $request->url,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'currency' => $request->currency,
            'declared_shipping_cost' => $request->declared_shipping_cost ?? 0,
            'is_inside_city' => $request->is_inside_city ?? false,
            'total_amount_bdt' => $totalBDT,
            'status' => 'pending',
        ]);

        return response()->json($productRequest, 201);
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
}
