<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    /**
     * Save shipping address for the authenticated user.
     */
    public function saveShipping(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'street' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'phone' => 'nullable|string|max:30',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->shipping_address = $validator->validated();
        $user->save();

        return response()->json(['message' => 'Shipping address saved', 'data' => $user->shipping_address]);
    }

    /**
     * Save billing address for the authenticated user.
     */
    public function saveBilling(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'street' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'phone' => 'nullable|string|max:30',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->billing_address = $validator->validated();
        $user->save();

        return response()->json(['message' => 'Billing address saved', 'data' => $user->billing_address]);
    }
}
