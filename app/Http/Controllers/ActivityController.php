<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityHistory;
use App\Models\Choice;
use App\Models\Question;
use App\Models\UserAnswer;
use Auth;
use DB;
use Illuminate\Http\Request;
use Throwable;

class ActivityController extends Controller
{
    /**
     * Get all activities.
     */
    public function index()
    {
        try {
            // Fetch all activities and process each one
            $activities = Activity::with('questions.choices')->get()->map(function ($activity) {
                return $this->attachQuestions($activity);
            });

            return response()->json($activities, 200);
            
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get a single activity by ID.
     */
    public function show($id)
    {
        try {
            // Find the activity by ID, including its related module and questions
            $activity = Activity::with([
                'module', 
                'questions.choices'
            ])->findOrFail($id);

            // Attach questions to the activity
            $activity = $this->attachQuestions($activity);

            return response()->json($activity, 200);
            
        } catch (Throwable $e) {
            return response()->json(['message' => 'Activity not found!'], 404);
        }
    }

    /**
     * Attach exactly 14 questions to an activity.
     * Ensures at least one question per required category.
     *
     * @param Activity $activity
     * @return Activity
     */
    private function attachQuestions(Activity $activity)
    {
        $requiredCategories = ['situational', 'normal', 'interactive'];

        // Fetch at least one question per required category
        $selectedQuestions = collect();
        foreach ($requiredCategories as $category) {
            $question = Question::where('category', $category)
                ->where('activity_id', $activity->id)
                ->inRandomOrder()
                ->limit(1)
                ->get();
            $selectedQuestions = $selectedQuestions->merge($question);
        }

        // Get remaining questions to complete 14 total
        $remainingCount = 14 - $selectedQuestions->count();
        $remainingQuestions = Question::where('activity_id', $activity->id)
            ->whereNotIn('id', $selectedQuestions->pluck('id'))
            ->inRandomOrder()
            ->limit($remainingCount)
            ->get();

        // Merge, shuffle, and load choices
        $finalQuestions = $selectedQuestions->merge($remainingQuestions)->shuffle();

        // Manually load choices for each question
        $finalQuestions->each(function ($question) {
            $question->loadMissing('choices'); // This loads choices only if they aren't loaded yet
        });

        // Attach questions to the activity
        return $activity->setRelation('questions', $finalQuestions);
    }
    

    /**
     * Create a new activity.
     */
    public function store(Request $request)
    {
        try {
            // Validate input
            $this->validate($request, [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'module_id' => 'required|exists:modules,id', // Ensure the module exists
            ]);

            // Create the new activity
            $activity = Activity::create([
                'title' => $request->title,
                'description' => $request->description,
                'module_id' => $request->module_id,
            ]);

            return response()->json($activity, 201);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update an existing activity.
     */
    public function update(Request $request, $id)
    {
        try {
            // Validate input
            $this->validate($request, [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'module_id' => 'required|exists:modules,id', // Ensure the module exists
            ]);

            // Find and update activity
            $activity = Activity::findOrFail($id);
            $activity->update([
                'title' => $request->title,
                'description' => $request->description,
                'module_id' => $request->module_id,
            ]);

            return response()->json($activity, 200);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete an activity.
     */
    public function destroy($id)
    {
        try {
            // Find and delete activity
            $activity = Activity::findOrFail($id);
            $activity->delete();

            return response()->json(['message' => 'Activity deleted successfully'], 200);
        } catch (Throwable $e) {
            return response()->json(['message' => 'Activity not found!'], 404);
        }
    }

    /**
     * Store the user answers and save the activity history.
     */
    public function saveUserAnswers(Request $request, $id)
    {
        try {
            // Validate the incoming request using $this->validate()
            $this->validate($request, [
                'answers' => 'required|array',
                'duration' => 'required|integer', // Ensure duration is a positive integer
            ]);

            $userId = Auth::user()->id;
            $activityId = $id;
            $answers = (array) $request->answers; // Array of answers [question_id => choice_id]

            $score = 0;
            $correctAnswers = [];
            $totalQuestions = count($answers);

            DB::beginTransaction();
            // Save each user answer to the UserAnswer table
            foreach ($answers as $answer) {
                $choice = Choice::find($answer);

                if ($choice && $choice->is_correct) {
                    $correctAnswers[] = $choice;
                    $score++;  // Increase the score for each correct answer
                }

                if ($choice) {
                    // Save the user answer
                    UserAnswer::create([
                        'user_id' => $userId,
                        'question_id' => $choice->question_id,
                        'choice_id' => $choice->id,
                    ]);
                }
            }

            // Calculate the duration and save activity history once all answers are stored
            $duration = $request->duration; // Duration is in minutes from the request
            $isCompleted = true;

            // Save the activity history
            $history = ActivityHistory::create([
                'user_id' => $userId,
                'activity_id' => $activityId,
                'score' => $score,
                'duration' => $duration,
                'is_completed' => $isCompleted,
            ]);
            
            DB::commit();
            return response()->json($history, 200);

        } catch (Throwable $e) {
            report($e);
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
