<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NotificationPreferenceController extends Controller
{
    /**
     * Get user's notification preferences.
     */
    public function index()
    {
        $user = Auth::user();
        return response()->json(['data' => $user->notification_preferences ?? []]);
    }

    /**
     * Update user's notification preferences.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'email_order_updates' => 'boolean',
            'email_promotions' => 'boolean',
            'sms_order_updates' => 'boolean',
            'sms_promotions' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Merge existing preferences with new ones
        $currentPreferences = $user->notification_preferences ?? [];
        $newPreferences = $validator->validated();
        
        $user->notification_preferences = array_merge($currentPreferences, $newPreferences);
        $user->save();

        return response()->json([
            'message' => 'Notification preferences updated',
            'data' => $user->notification_preferences
        ]);
    }
}
