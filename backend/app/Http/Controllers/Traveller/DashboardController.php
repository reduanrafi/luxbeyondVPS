<?php

namespace App\Http\Controllers\Traveller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TravellerProfile;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function getProfile()
    {
        $user = auth()->user();
        
        // Auto-create profile if not exists, syncing data from User table
        $profile = $user->travellerProfile()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'passport_number' => $user->passport_no,
                'phone_number' => $user->phone
            ]
        );
        
        return response()->json(['profile' => $profile, 'user' => $user]);
    }
    
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $profile = $user->travellerProfile()->firstOrCreate(['user_id' => $user->id]);

        $validated = $request->validate([
            'passport_number' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'passport_image' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        $data = [
            'passport_number' => $validated['passport_number'] ?? $profile->passport_number,
            'phone_number' => $validated['phone_number'] ?? $profile->phone_number,
        ];

        if ($request->hasFile('passport_image')) {
            $path = $request->file('passport_image')->store('traveller-documents', 'public');
            $existingDocs = $profile->documents ?? [];
            $existingDocs[] = $path;
            $data['documents'] = $existingDocs;
            
            // Set status to pending whenever new docs are uploaded
            $data['verification_status'] = 'pending';

            // Notify Admins
            $admins = \App\Models\User::role('Admin')->get();
            \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\NewTravellerApplication($user));
        }

        $profile->update($data);

        return response()->json(['message' => 'Profile updated successfully', 'profile' => $profile]);
    }
}
