<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    /**
     * Delete the authenticated user's account.
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();

        // Optional: Require password confirmation before deletion for security
        // For now, we proceed with deletion as requested by the plan.
        
        // Revoke all tokens
        $user->tokens()->delete();
        
        // Delete the user
        // Note: Soft delete is recommended for a real system, but standard delete as per request
        $user->delete();

        return response()->json(['message' => 'Account deleted successfully']);
    }
}
