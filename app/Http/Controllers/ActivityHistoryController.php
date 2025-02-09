<?php

namespace App\Http\Controllers;

use App\Models\ActivityHistory;
use App\Models\Module;
use App\Models\UserLesson;
use Auth;
use DB;
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
            $id = $request->id ?? null;
            // Retrieve the activity history for the given user ID
            $activityHistory = ActivityHistory::with('activity') // Eager load related activities
                ->where('user_id', $id ?? Auth::user()->id)  // Filter by user ID
                ->get();  // Get the results

            // Check if activity history exists for the user
            if ($activityHistory->isEmpty()) {
                return response()->json($activityHistory, 200);
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

    public function leaderboards() {
        try {
            // Get user ID
            $userId = Auth::user()->id;

            // Get latest scores per activity per user
            $userRanks = ActivityHistory::select(
                'user_id', 
                DB::raw('SUM(score) as total_score')
            )
                ->whereIn('id', function ($query) {
                    $query->select(DB::raw('MAX(id)'))
                        ->from('activity_histories')
                        ->groupBy('user_id', 'activity_id');
                })
                ->groupBy('user_id')
                ->orderByDesc('total_score')
                ->get();

            // Find user's rank
            $rank = 1;
            foreach ($userRanks as $userRank) {
                if ($userRank->user_id == $userId) {
                    return response()->json([
                        'rank' => $rank,
                        'total_score' => $userRank->total_score,
                        'total_ranks' => $userRanks->count()
                    ]);
                }
                $rank++;
            }

            return response()->json([
                'rank' => 0,
                'total_score' => 0,
                'total_ranks' => $userRanks->count()
            ], 200);

        } catch (Throwable $e) {
            report($e);
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function engagements() {
        try {
            // Get user ID
            $userId = Auth::user()->id;

            // Get total lessons across all modules
            $totalLessons = Module::withCount('lessons')->get()->sum('lessons_count');

            // Get total completed lessons by user
            $completedLessons = UserLesson::where('user_id', $userId)->count();

            // Calculate total engagement percentage
            $overallEngagement = $totalLessons > 0 ? ($completedLessons / $totalLessons) * 100 : 0;

            return response()->json([
                'total_lessons' => $totalLessons,
                'completed_lessons' => $completedLessons,
                'overall_engagement_percentage' => round($overallEngagement, 0)
            ]);

        } catch (Throwable $e) {
            report($e);
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function getTotalModuleHours()
    {
        try {
            // Get user ID
            $userId = Auth::user()->id;

            // Sum the duration (assuming duration is stored in seconds)
            $totalSeconds = UserLesson::where('user_id', $userId)->sum('duration');

            // Convert seconds to hours and minutes
            $hours = floor($totalSeconds / 3600);  // Hours
            $minutes = floor(($totalSeconds % 3600) / 60);  // Minutes

            return response()->json([
                'total_seconds' => $totalSeconds,   // Total duration in seconds
                'total_hours' => $hours,            // Total hours
                'total_minutes' => $minutes,        // Total minutes
                'formatted_time' => sprintf("%02dh %02dm", $hours, $minutes) // Formatted output: "05h 30m"
            ]);
            
        } catch (Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
