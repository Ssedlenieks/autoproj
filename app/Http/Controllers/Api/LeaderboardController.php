<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    public function index()
    {
        $users = User::with('achievements')
            ->withCount('projects as builds_count')
            ->get();

        $topXpUsers = $users->map(function ($user) {
            return [
                'id'           => $user->id,
                'name'         => $user->name,
                'avatar_url'   => $user->avatar_url,
                'xp'           => $user->totalPoints(),
                'level'        => $user->level(),
                'rank'         => $user->rank(),
                'builds_count' => $user->builds_count,
            ];
        })->sortByDesc('xp')->values()->take(10);

        $topHpBuilds = Project::select(
                'projects.id',
                'projects.project_name',
                'projects.final_hp',
                'projects.base_hp',
                'users.name as user_name'
            )
            ->join('users', 'users.id', '=', 'projects.user_id')
            ->orderByDesc('projects.final_hp')
            ->limit(10)
            ->get();

        $topHpUsers = Project::select(
                'users.id',
                'users.name',
                'users.avatar_url',
                DB::raw('SUM(projects.final_hp - projects.base_hp) as total_hp_gain'),
                DB::raw('COUNT(projects.id) as builds_count')
            )
            ->join('users', 'users.id', '=', 'projects.user_id')
            ->groupBy('users.id', 'users.name', 'users.avatar_url')
            ->orderByDesc('total_hp_gain')
            ->limit(10)
            ->get();

        return response()->json([
            'success'     => true,
            'topXpUsers'  => $topXpUsers,
            'topHpBuilds' => $topHpBuilds,
            'topHpUsers'  => $topHpUsers,
        ]);
    }
}
