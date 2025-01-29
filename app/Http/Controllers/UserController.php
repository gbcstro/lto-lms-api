<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Throwable;

class UserController extends Controller
{
     /**
     * Update the user profile.
     */
    public function updateProfile(Request $request)
    {
        try {
            // Validate incoming request
            $this->validate($request, [
                'first_name' => 'sometimes|string|max:255',
                'last_name' => 'sometimes|string|max:255',
                'profile_picture' => 'sometimes|url',  // Assuming profile picture is a URL
                'address' => 'sometimes|string|max:255',  // Add validation for address
            ]);

            // Get the currently authenticated user (or use $request->user_id if using user_id)
            $user = Auth::user(); // This assumes you're using JWT authentication for the authenticated user
            
            // Save the changes
            $user->update($request->all());

            return response()->json(Auth::user()->with(['history', 'bookmarks.module'])->first(), 200);
            
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
