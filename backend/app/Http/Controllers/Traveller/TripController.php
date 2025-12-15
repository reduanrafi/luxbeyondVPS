<?php

namespace App\Http\Controllers\Traveller;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use Illuminate\Http\Request;

class TripController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $trips = auth()->user()->trips()->latest()->get();
        return response()->json($trips);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'origin' => 'required|string',
            'destination' => 'required|string',
            'travel_date' => 'required|date|after:today',
            'luggage_capacity' => 'required|integer|min:1',
            'available_capacity' => 'required|integer|min:0|max:' . $request->luggage_capacity,
        ]);

        $trip = auth()->user()->trips()->create($validated);

        return response()->json(['message' => 'Trip created successfully', 'trip' => $trip], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Trip $trip)
    {
        // Ensure user owns the trip
        if ($trip->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'origin' => 'sometimes|string',
            'destination' => 'sometimes|string',
            'travel_date' => 'sometimes|date|after:today',
            // Allow updating capacity, but handle carefully in real logic if items booked
            'luggage_capacity' => 'sometimes|integer|min:1',
            'available_capacity' => 'sometimes|integer|min:0', 
            'status' => 'sometimes|in:scheduled,ongoing,completed,cancelled',
        ]);

        $trip->update($validated);

        return response()->json(['message' => 'Trip updated successfully', 'trip' => $trip]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Trip $trip)
    {
        if ($trip->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        // Soft delete or status change is safer, but strictly for "delete" endpoint:
        $trip->delete();

        return response()->json(['message' => 'Trip deleted successfully']);
    }
}
