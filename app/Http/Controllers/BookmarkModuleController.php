<?php

namespace App\Http\Controllers;

use App\Models\BookmarkModule;
use App\Models\Module;
use Auth;
use Illuminate\Http\Request;
use Throwable;

class BookmarkModuleController extends Controller
{
    public function index()
    {
        try {
            $user = Auth::user();
            $bookmarks = BookmarkModule::where('user_id', $user->id)->get();

            return response()->json($bookmarks, 200);
        } catch (Throwable $e) {
            report($e);
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function store($id)
    {
        try {
            $user = Auth::user();
            $module = Module::findOrFail($id);
            // Check if the module is already bookmarked
            $existingBookmark = BookmarkModule::where('user_id', $user->id ?? 1)
                                            ->where('module_id', $module->id)
                                            ->first();

            if ($existingBookmark) {
                BookmarkModule::find($existingBookmark->id)->delete();
                return response()->json(['message' => 'Bookmark removed.', 'value' => false], 201);
            }

            // Create a new bookmark
            BookmarkModule::create([
                'user_id' => $user->id ?? 1,
                'module_id' => $module->id,
            ]);

            return response()->json(['message' => 'Module bookmarked!.', 'value' => true], 201);

        } catch (Throwable $e) {
            report($e);
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
