<?php

namespace App\Http\Controllers;

use App\Models\BookmarkModule;
use App\Models\Module;
use App\Models\UserLesson;
use Auth;
use Illuminate\Http\Request;
use Throwable;

class ModuleController extends Controller
{
    /**
     * Get all modules.
     */
    public function index()
    {
        try {
            // Fetch all modules with lessons & activities
            $modules = Module::with('lessons')->get();

            foreach ($modules as $module) {
                $module = $this->additionalData($module);
            }

            return response()->json($modules, 200);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get a single module by ID.
     */
    public function show($id)
    {
        try {
            // Find the module or throw 404
            $module = Module::with('lessons')->findOrFail($id);

            $module = $this->additionalData($module);

            return response()->json($module, 200);
            
        } catch (Throwable $e) {
            return response()->json(['message' => 'Module not found!'], 404);
        }
    }

    /**
     * Create a new module.
     */
    public function store(Request $request)
    {
        try {
            // Validate input
            $this->validate($request, [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            // Create new module
            $module = Module::create([
                'title' => $request->title,
                'description' => $request->description,
            ]);

            return response()->json($module, 201);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update an existing module.
     */
    public function update(Request $request, $id)
    {
        try {
            // Validate input
            $this->validate($request, [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            // Find and update module
            $module = Module::findOrFail($id);
            $module->update([
                'title' => $request->title,
                'description' => $request->description,
            ]);

            return response()->json($module, 200);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a module.
     */
    public function destroy($id)
    {
        try {
            // Find and delete module
            $module = Module::findOrFail($id);
            $module->delete();

            return response()->json(['message' => 'Module deleted successfully'], 200);
        } catch (Throwable $e) {
            return response()->json(['message' => 'Module not found!'], 404);
        }
    }

    private function additionalData($module)
    {
        $totalLessons = $module->lessons->count();
        $completedLessons = UserLesson::where('user_id', Auth::user()->id ?? 1)
            ->where('module_id', $module->id)
            ->whereIn('lesson_id', $module->lessons->pluck('id'))
            ->count();

        $progress = ($totalLessons > 0) ? round(($completedLessons / $totalLessons) * 100, 2) : 0;
        $module->is_bookmarked = BookmarkModule::where('user_id', Auth::user()->id ?? 1)
                                ->where('module_id', $module->id)
                                ->exists();

        $module->progress = [
            'progress' => $progress . '%',
            'completed_lessons' => $completedLessons,
            'total_lessons' => $totalLessons,
            ];
        return $module;
    }
}
