<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Achievement;
use App\Models\DailyChallenge as Challenge;
use App\Models\Project;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // ── Overview ──────────────────────────────────────────────
    public function overview()
    {
        $topUsers = User::get()
            ->map(fn($u) => [
                'id'     => $u->id,
                'name'   => $u->name,
                'points' => $u->totalPoints(),
                'level'  => $u->level(),
                'rank'   => $u->rank(),
            ])
            ->sortByDesc('points')
            ->take(5)
            ->values();

        return response()->json([
            'success' => true,
            'stats'   => [
                'total_users'        => User::count(),
                'total_projects'     => Project::count(),
                'total_achievements' => Achievement::count(),
                'total_challenges'   => Challenge::count(),
                'top_users'          => $topUsers,
            ],
        ]);
    }

    // ── Users ─────────────────────────────────────────────────
    public function users()
    {
        $users = User::with('role')->get()->map(fn($u) => [
            'id'         => $u->id,
            'name'       => $u->name,
            'email'      => $u->email,
            'role_id'    => $u->role_id,
            'points'     => $u->totalPoints(),
            'level'      => $u->level(),
            'created_at' => $u->created_at,
        ]);

        return response()->json(['success' => true, 'users' => $users]);
    }

    public function updateRole(Request $request, $id)
    {
        $request->validate(['role_id' => 'required|integer|exists:roles,id']);
        User::findOrFail($id)->update(['role_id' => $request->role_id]);
        return response()->json(['success' => true]);
    }

    public function deleteUser($id)
    {
        User::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    // ── Achievements ──────────────────────────────────────────
    public function achievements()
    {
        return response()->json([
            'success'      => true,
            'achievements' => Achievement::all(),
        ]);
    }

    public function storeAchievement(Request $request)
    {
        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'description'       => 'required|string',
            'icon'              => 'nullable|string|max:10',
            'points'            => 'required|integer|min:0',
            'slug'              => 'required|string|unique:achievements,slug',
            'category'          => 'required|string|max:100',
            'requirement_type'  => 'required|string|in:count,threshold,special',
            'requirement_value' => 'nullable|integer',
        ]);

        $achievement = Achievement::create($data);
        return response()->json(['success' => true, 'achievement' => $achievement], 201);
    }

    public function updateAchievement(Request $request, $id)
    {
        $data = $request->validate([
            'name'              => 'sometimes|string|max:255',
            'description'       => 'sometimes|string',
            'icon'              => 'nullable|string|max:10',
            'points'            => 'sometimes|integer|min:0',
            'slug'              => 'sometimes|string|unique:achievements,slug,' . $id,
            'category'          => 'sometimes|string|max:100',
            'requirement_type'  => 'sometimes|string|in:count,threshold,special',
            'requirement_value' => 'nullable|integer',
        ]);

        $achievement = Achievement::findOrFail($id);
        $achievement->update($data);
        return response()->json(['success' => true, 'achievement' => $achievement]);
    }

    public function deleteAchievement($id)
    {
        Achievement::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    // ── Challenges ────────────────────────────────────────────
    public function challenges()
    {
        return response()->json([
            'success'    => true,
            'challenges' => Challenge::all(),
        ]);
    }

    public function storeChallenge(Request $request)
    {
        $data = $request->validate([
            'key'         => 'required|string|unique:daily_challenges,key',
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'points'      => 'required|integer|min:0',
        ]);

        $challenge = Challenge::create($data);
        return response()->json(['success' => true, 'challenge' => $challenge], 201);
    }

    public function updateChallenge(Request $request, $id)
    {
        $data = $request->validate([
            'key'         => 'sometimes|string|unique:daily_challenges,key,' . $id,
            'title'       => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'points'      => 'sometimes|integer|min:0',
        ]);

        $challenge = Challenge::findOrFail($id);
        $challenge->update($data);
        return response()->json(['success' => true, 'challenge' => $challenge]);
    }

    public function deleteChallenge($id)
    {
        Challenge::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    // ── Projects ──────────────────────────────────────────────
    public function projects()
    {
        $projects = Project::with('user', 'car.model.make')->get()->map(fn($p) => [
            'id'           => $p->id,
            'project_name' => $p->project_name,
            'user'         => $p->user->name ?? '—',
            'car'          => optional($p->car->model->make)->name . ' ' . optional($p->car->model)->name,
            'final_hp'     => $p->final_hp,
            'is_public'    => $p->is_public,
            'created_at'   => $p->created_at,
        ]);

        return response()->json(['success' => true, 'projects' => $projects]);
    }

    public function deleteProject($id)
    {
        Project::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}