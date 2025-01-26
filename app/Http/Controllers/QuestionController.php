<?php

namespace App\Http\Controllers;

use App\Models\Choice;
use App\Models\Question;
use Illuminate\Http\Request;
use Throwable;

class QuestionController extends Controller
{
    /**
     * Get all questions.
     */
    public function index()
    {
        try {
            // Fetch all questions with their activity and choices
            $questions = Question::with('activity', 'choices')->get();
            return response()->json($questions, 200);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get a single question by ID.
     */
    public function show($id)
    {
        try {
            // Find the question by ID with its related activity and choices
            $question = Question::with('activity', 'choices')->findOrFail($id);
            return response()->json($question, 200);
        } catch (Throwable $e) {
            return response()->json(['message' => 'Question not found!'], 404);
        }
    }

    /**
     * Create a new question.
     */
    public function store(Request $request)
    {
        try {
            // Validate the input
            $this->validate($request, [
                'question' => 'required|string|max:255',
                'type' => 'required|string',
                'activity_id' => 'required|exists:activities,id', // Ensure the activity exists
                'choices' => 'required|array', // Ensure choices is an array
                'choices.*.context' => 'required|string|max:255', // Each choice context
                'choices.*.is_correct' => 'required|boolean', // Ensure each choice has a correct answer flag
            ]);

            // Create the question
            $question = Question::create([
                'question' => $request->question,
                'type' => $request->type,
                'activity_id' => $request->activity_id,
            ]);

            // Store the choices
            $choicesData = [];
            foreach ($request->choices as $choice) {
                $choicesData[] = [
                    'context' => $choice['context'],
                    'is_correct' => $choice['is_correct'],
                    'question_id' => $question->id, // Link choice to the created question
                ];
            }

            // Bulk insert choices for the question
            $question->choices()->createMany($choicesData);

            return response()->json($question->load('choices'), 201); // Return the question with its choices
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update an existing question.
     */
    public function update(Request $request, $id)
    {
        try {
            // Validate the input
            $this->validate($request, [
                'question' => 'required|string|max:255',
                'type' => 'required|string',
                'choices' => 'required|array', // Ensure choices is an array
                'choices.*.id' => 'nullable|exists:choices,id', // Optional: If updating existing choices
                'choices.*.context' => 'required|string|max:255', // Each choice context
                'choices.*.is_correct' => 'required|boolean', // Ensure each choice has a correct answer flag
            ]);

            // Find the question by ID
            $question = Question::findOrFail($id);

            // Update the question itself
            $question->update([
                'question' => $request->question,
                'type' => $request->type,
            ]);

            // Process the new/updated choices
            $choicesData = [];
            $updatedChoiceIds = [];
            
            foreach ($request->choices as $choice) {
                if (isset($choice['id']) && $choice['id']) {
                    // If the choice ID exists, update it
                    $existingChoice = Choice::findOrFail($choice['id']);
                    $existingChoice->update([
                        'context' => $choice['context'],
                        'is_correct' => $choice['is_correct'],
                    ]);
                    $updatedChoiceIds[] = $existingChoice->id;
                } else {
                    // If the choice does not have an ID, create a new choice
                    $choicesData[] = [
                        'context' => $choice['context'],
                        'is_correct' => $choice['is_correct'],
                        'question_id' => $question->id,
                    ];
                }
            }

            // Bulk insert new choices
            if (count($choicesData)) {
                $question->choices()->createMany($choicesData);
            }

            // Delete any choices that were not updated (i.e., were removed from the request)
            $question->choices()->whereNotIn('id', $updatedChoiceIds)->delete();

            return response()->json($question->load('choices'), 200); // Return the updated question with its choices
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a question.
     */
    public function destroy($id)
    {
        try {
            // Find and delete the question
            $question = Question::findOrFail($id);
            $question->delete();

            return response()->json(['message' => 'Question deleted successfully'], 200);
        } catch (Throwable $e) {
            return response()->json(['message' => 'Question not found!'], 404);
        }
    }
}
