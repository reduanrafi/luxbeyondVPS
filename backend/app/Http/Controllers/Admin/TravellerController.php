<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TravellerProfile;
use Illuminate\Http\Request;

class TravellerController extends Controller
{
    /**
     * Display a listing of the travellers.
     */
    public function index(Request $request)
    {
        // Get users who are Travellers (role) OR have applied (have profile)
        $query = User::where(function($q) {
            $q->role('Traveller')
              ->orWhereHas('travellerProfile');
        })->with('travellerProfile');

        if ($request->filled('status')) {
            $status = $request->status; // pending, approved, rejected
            $query->whereHas('travellerProfile', function($q) use ($status) {
                $q->where('verification_status', $status);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return response()->json($query->latest()->paginate(10));
    }

    /**
     * Update the verification status.
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,pending',
        ]);

        $user = User::findOrFail($id);
        
        // Ensure profile exists
        $profile = $user->travellerProfile()->firstOrCreate(['user_id' => $user->id]);
        
        $profile->update([
            'verification_status' => $validated['status']
        ]);

        // Sync Traveller role based on status
        if ($validated['status'] === 'approved') {
            $user->assignRole('Traveller');
        } else {
            $user->removeRole('Traveller');
        }

        $user->notify(new \App\Notifications\TravellerStatusUpdated($validated['status']));

        return response()->json(['message' => 'Status updated successfully', 'user' => $user->load('travellerProfile')]);
    }

    /**
     * Update traveller profile details.
     */
    public function updateProfile(Request $request, $id)
    {
        $validated = $request->validate([
            'passport_number' => 'required|string',
            'phone_number' => 'required|string',
            // Add other fields if necessary
        ]);

        $user = User::findOrFail($id);
        $profile = $user->travellerProfile()->firstOrCreate(['user_id' => $user->id]);
        
        $profile->update($validated);

        return response()->json(['message' => 'Profile updated successfully', 'user' => $user->load('travellerProfile')]);
    }
    
    /**
     * Get details of a specific traveller
     */
    public function show($id)
    {
        $user = User::with(['travellerProfile', 'trips', 'payouts'])->findOrFail($id);
        return response()->json($user);
    }
}
