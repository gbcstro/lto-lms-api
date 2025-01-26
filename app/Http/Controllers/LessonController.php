<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Illuminate\Http\Request;
use Throwable;

class LessonController extends Controller
{
    /**
     * Get all lessons.
     */
    public function index()
    {
        try {
            // Fetch all lessons along with their module details
            $lessons = Lesson::with('module')->get();
            return response()->json($lessons, 200);

        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get a single lesson by ID.
     */
    public function show($id)
    {
        try {
            // Find the lesson by ID with its module
            $lesson = Lesson::with('module')->findOrFail($id);
            return response()->json($lesson, 200);
        } catch (Throwable $e) {
            return response()->json(['message' => 'Lesson not found!'], 404);
        }
    }

    /**
     * Create a new lesson.
     */
    public function store(Request $request)
    {
        try {
            // Validate input
            $this->validate($request, [
                'title' => 'required|string|max:255',
                'content' => 'nullable|string',
                'module_id' => 'required|exists:modules,id', // Ensure the module exists
            ]);

            // Create the new lesson
            $lesson = Lesson::create([
                'title' => $request->title,
                'content' => $request->content,
                'module_id' => $request->module_id,
            ]);

            return response()->json($lesson, 201);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update an existing lesson.
     */
    public function update(Request $request, $id)
    {
        try {
            // Validate input
            $this->validate($request, [
                'title' => 'required|string|max:255',
                'content' => 'nullable|string',
            ]);

            // Find and update lesson
            $lesson = Lesson::findOrFail($id);
            $lesson->update([
                'title' => $request->title,
                'content' => $request->content,
            ]);

            return response()->json($lesson, 200);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a lesson.
     */
    public function destroy($id)
    {
        try {
            // Find and delete the lesson
            $lesson = Lesson::findOrFail($id);
            $lesson->delete();

            return response()->json(['message' => 'Lesson deleted successfully'], 200);
        } catch (Throwable $e) {
            return response()->json(['message' => 'Lesson not found!'], 404);
        }
    }

}
