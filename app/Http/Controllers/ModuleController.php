<?php

namespace App\Http\Controllers;

use App\Models\Module;
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
            $module = Module::with('lessons', 'activities')->findOrFail($id);
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
}
