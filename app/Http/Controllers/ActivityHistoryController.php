<?php

namespace App\Http\Controllers;

use App\Models\ActivityHistory;
use Auth;
use Illuminate\Http\Request;
use Throwable;

class ActivityHistoryController extends Controller
{
    /**
     * Show the activity history for a specific user.
     */
    public function index(Request $request)
    {
        try {
            // Retrieve the activity history for the given user ID
            $activityHistory = ActivityHistory::with('activity') // Eager load related activities
                ->where('user_id', Auth::user()->id)  // Filter by user ID
                ->get();  // Get the results

            // Check if activity history exists for the user
            if ($activityHistory->isEmpty()) {
                return response()->json(['message' => 'No activity history found for this user.'], 404);
            }

            return response()->json($activityHistory, 200);

        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update an existing ActivityHistory.
     */
    public function update(Request $request, $id)
    {
        try {
            // Validate the input
            $this->validate($request, [
                'score' => 'required|numeric',
                'duration' => 'required|numeric',
                'is_completed' => 'required|boolean',
            ]);

            // Find the activity history by ID
            $activityHistory = ActivityHistory::findOrFail($id);

            // Update the activity history
            $activityHistory->update([
                'score' => $request->score,
                'duration' => $request->duration,
                'is_completed' => $request->is_completed,
            ]);

            return response()->json($activityHistory, 200); // Return the updated ActivityHistory
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the activity history for a specific user.
     */
    public function show($userId)
    {
        try {
            // Get the activity history of the user
            $activityHistory = ActivityHistory::where('user_id', $userId)->with('activity')->get();

            return response()->json($activityHistory, 200); // Return the user's activity history
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete an activity history.
     */
    public function destroy($id)
    {
        try {
            // Find and delete the activity history by ID
            $activityHistory = ActivityHistory::findOrFail($id);
            $activityHistory->delete();

            return response()->json(['message' => 'Activity history deleted successfully.'], 200);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
