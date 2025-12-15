<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Payout::with(['user', 'trip']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->latest()->paginate(10));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payout $payout)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,paid,failed',
            'transaction_id' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $payout->update($validated);

        return response()->json(['message' => 'Payout status updated', 'payout' => $payout]);
    }
}
