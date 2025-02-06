<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityHistory;
use App\Models\Module;
use App\Models\User;
use App\Models\UserLesson;
use Auth;
use Illuminate\Http\Request;
use Throwable;

class UserController extends Controller
{
     /**
     * Update the user profile.
     */
    public function updateProfile(Request $request)
    {
        try {
            // Validate incoming request
            $this->validate($request, [
                'first_name' => 'sometimes|string|max:255',
                'last_name' => 'sometimes|string|max:255',
                'profile_picture' => 'sometimes|url',  // Assuming profile picture is a URL
                'address' => 'sometimes|string|max:255',  // Add validation for address
            ]);

            // Get the currently authenticated user (or use $request->user_id if using user_id)
            $user = Auth::user(); // This assumes you're using JWT authentication for the authenticated user
            
            // Save the changes
            $user->update($request->all());

            return response()->json(User::with(['history', 'bookmarks.module'])->find($user->id), 200);
            
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function achievements() {
        try {
            $badges = [];

            if (!Auth::user()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $modules = $this->modulesAchievements();
            $activities = $this->activityAchievements();

            $badges = array_merge( $modules['badges'], $activities['badges']);

            if ($modules['overall'] && $activities['overall']) {
                $badges[] = [   
                    'name' => 'King of the Road',
                    'image' => './assets/badges/modules&quiz.png',
                ];
            }

            return response()->json($badges, 200);

        } catch (Throwable $e) {
            report($e);
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    private function modulesAchievements() {
        try {
            $modules = Module::get();
            $badges = [];
            $module_badges = [
                [   
                    'module_id' => 1,
                    'name' => 'Sign Novice',
                    'image' => './assets/badges/module_beginner.png',
                ],
                [
                    'module_id' => 2,
                    'name' => 'PathFinder',
                    'image' => './assets/badges/module_advanced.png',
                ],
                [
                    'module_id' => 3,
                    'name' => 'Sign Expert',
                    'image' => './assets/badges/module_expert.png',
                ],
                [
                    'module_id' => 4,
                    'name' => 'Road Scholar',
                    'image' => './assets/badges/modules.png',
                ]
            ];

            $count = 0;
            foreach ($modules as $module) {
                if ($this->checkModuleProgress($module->id)) {
                    $badge = array_filter($module_badges, function($badge) use ($module) {
                        return $badge['module_id'] === $module->id;
                    });

                    $badges[] =  reset($badge);
                    $count++;
                }
            }

            if ($count == $modules->count()) {
                $badges[] = $module_badges[3];
            }

            $badges = array_map(function($badge) {
                unset($badge['module_id']);
                return $badge;
            }, $badges);

            return [
                "badges" => $badges,
                "overall" => $count == $modules->count()
            ];
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function activityAchievements() {
        try {
            $activities = Activity::get();
            $badges = [];
            $quiz_badges = [
                [   
                    'activity_id' => 1,
                    'name' => 'Rising Pro',
                    'image' => './assets/badges/quiz_beginner.png',
                ],
                [
                    'activity_id' => 2,
                    'name' => 'Advanced Ace',
                    'image' => './assets/badges/quiz_advanced.png',
                ],
                [
                    'activity_id' => 3,
                    'name' => 'Grandmaster',
                    'image' => './assets/badges/quiz_expert.png',
                ],
                [
                    'activity_id' => 4,
                    'name' => 'Mastermind',
                    'image' => './assets/badges/quiz.png',
                ],
            ];

            $count = 0;
            foreach ($activities as $activity) {
                if ($this->checkActivityProgress($activity->id)) {
                    $badge = array_filter($quiz_badges, function($badge) use ($activity) {
                        return $badge['activity_id'] === $activity->id;
                    });
                    $badges[] =  reset($badge);
                    $count++;
                }
            }

            if ($count == $activities->count()) {
                $badges[] = $quiz_badges[3];
            }

            $badges = array_map(function($badge) {
                unset($badge['activity_id']);
                return $badge;
            }, $badges);

            return [
                "badges" => $badges,
                "overall" => $count == $activities->count()
            ];

        } catch (Throwable $e) {
            report($e);
        }
    }

    private function checkModuleProgress($module_id) {
        try {
            $module = Module::with('lessons')->findOrFail($module_id);
            $userLessons = UserLesson::where('user_id', Auth::user()->id)
                            ->where('module_id', $module_id)
                            ->get();

            return $module->lessons->count() == $userLessons->count();

        } catch (Throwable $e) {
            report($e);
            return false;
        }
    }

    private function checkActivityProgress($activity_id) {
        try {
            $score = ActivityHistory::where('user_id', Auth::user()->id)
                            ->where('activity_id', $activity_id)
                            ->max('score');

            return 14 <= $score;

        } catch (Throwable $e) {
            report($e);
            return false;
        }
    }
}
