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
        // Get all users who have a traveller profile (applicants)
        $query = User::whereHas('travellerProfile')->with('travellerProfile');

        if ($request->has('status')) {
            $status = $request->status; // pending, approved, rejected
            $query->whereHas('travellerProfile', function($q) use ($status) {
                $q->where('verification_status', $status);
            });
        }

        if ($request->has('search')) {
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

        return response()->json(['message' => 'Status updated successfully', 'user' => $user->load('travellerProfile')]);
    }
    
    /**
     * Get details of a specific traveller
     */
    public function show($id)
    {
        $user = User::with('travellerProfile')->findOrFail($id);
        return response()->json($user);
    }
}
