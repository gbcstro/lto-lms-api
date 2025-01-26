<?php

namespace App\Http\Controllers;

use App\Models\BookmarkModule;
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


    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'module_id' => 'required|exists:modules,id',
            ]);
            
            $user = Auth::user();
            
            // Check if the module is already bookmarked
            $existingBookmark = BookmarkModule::where('user_id', $user->id)
                                            ->where('module_id', $request->module_id)
                                            ->first();

            if ($existingBookmark) {
                return response()->json(['message' => 'Module already bookmarked.'], 400);
            }

            // Create a new bookmark
            $bookmark = BookmarkModule::create([
                'user_id' => $user->id,
                'module_id' => $request->module_id,
            ]);

            return response()->json( $bookmark, 201);

        } catch (Throwable $e) {
            report($e);
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
