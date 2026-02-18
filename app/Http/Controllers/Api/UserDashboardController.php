<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserDashboardController extends Controller
{
    /**
     * Show current user's dashboard
     */
    public function show(Request $request)
    {
        try {
            $user = $request->user()->load([
                'role',
                'achievements',
                'projects.car.model.make',
                'projects.engine',
                'projects.parts.powerMod'
            ]);

            $stats = [
                'total_builds' => $user->projects()->count(),
                'total_points' => $user->totalPoints(),
                'level' => $user->level(),
                'rank' => $user->rank(),
                'level_color' => $user->levelColor(),
                'level_progress' => $user->levelProgress(),
                'points_to_next_level' => $user->pointsToNextLevel(),
                'points_for_next_level' => $user->pointsForNextLevel(),
                'achievements_unlocked' => $user->achievements()->count(),
                'total_hp_gained' => $user->projects()->sum('total_hp_gain') ?? 0,
                'most_powerful_build' => $user->projects()
                    ->with('car.model.make', 'engine')
                    ->orderBy('final_hp', 'desc')
                    ->first(),
            ];

            return response()->json([
                'success' => true,
                'user' => $user,
                'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load dashboard: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    /**
     * Show any user's profile (for viewing others)
     */
    public function showProfile(Request $request, $id)
    {
        try {
            $user = User::with([
                'achievements',
                'projects.car.model.make',
                'projects.engine'
            ])->findOrFail($id);

            $stats = [
                'total_builds' => $user->projects()->count(),
                'total_points' => $user->totalPoints(),
                'level' => $user->level(),
                'rank' => $user->rank(),
                'level_color' => $user->levelColor(),
                'level_progress' => $user->levelProgress(),
                'achievements_unlocked' => $user->achievements()->count(),
                'total_hp_gained' => $user->projects()->sum('total_hp_gain') ?? 0,
            ];

            return response()->json([
                'success' => true,
                'user' => $user,
                'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }
    }
}
