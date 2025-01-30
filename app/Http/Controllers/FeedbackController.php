<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Auth;
use Illuminate\Http\Request;
use Throwable;

class FeedbackController extends Controller
{
    /**
     * Get all feedback entries.
     */
    public function index()
    {
        try {
            $feedbacks = Feedback::with('user')->get();
            return response()->json($feedbacks, 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Store new feedback.
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'rating' => 'nullable|integer|min:1|max:5',
                'comment' => 'nullable|string',
                'follow_up' => 'boolean',
            ]);

            $feedback = Feedback::create([
                'rating' => $request->rating,
                'comment' => $request->comment,
                'follow_up' => $request->follow_up ?? false,
                'user_id' => Auth::user()->id,
            ]);

            return response()->json($feedback, 201);
        } catch (Throwable $e) {
            report($e);
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Show a single feedback.
     */
    public function show($id)
    {
        try {
            $feedback = Feedback::with('user')->findOrFail($id);
            return response()->json($feedback, 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update feedback.
     */
    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'rating' => 'nullable|integer|min:1|max:5',
                'comment' => 'nullable|string',
                'follow_up' => 'boolean',
            ]);

            $feedback = Feedback::findOrFail($id);
            $feedback->update($request->only(['rating', 'comment', 'follow_up']));

            return response()->json($feedback, 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete feedback.
     */
    public function destroy($id)
    {
        try {
            $feedback = Feedback::findOrFail($id);
            $feedback->delete();

            return response()->json(['message' => 'Feedback deleted'], 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
