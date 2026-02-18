<?php
// app/Http/Controllers/Api/ProjectController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectPart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = $request->user()
            ->projects()
            ->with(['car.model.make', 'engine', 'parts.powerMod'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'projects' => $projects,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'car_id' => 'required|exists:cars,id',
            'engine_id' => 'required|exists:engines,id',
            'project_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_hp' => 'required|integer',
            'base_torque' => 'required|integer',
            'parts' => 'required|array|min:1',
            'parts.*.power_mod_id' => 'required|exists:power_mods,id',
            'parts.*.hp_gain' => 'required|integer',
            'parts.*.torque_nm_gain' => 'required|integer',
            // ✅ Removed price validation
        ]);

        DB::beginTransaction();
        try {
            // Calculate totals
            $totalHPGain = collect($validated['parts'])->sum('hp_gain');
            $totalTorqueGain = collect($validated['parts'])->sum('torque_nm_gain');
            // ✅ Removed $totalCost calculation

            // Create project
            $project = Project::create([
                'user_id' => $request->user()->id,
                'car_id' => $validated['car_id'],
                'engine_id' => $validated['engine_id'],
                'project_name' => $validated['project_name'],
                'description' => $validated['description'],
                'base_hp' => $validated['base_hp'],
                'base_torque' => $validated['base_torque'],
                'total_hp_gain' => $totalHPGain,
                'total_torque_gain' => $totalTorqueGain,
                'final_hp' => $validated['base_hp'] + $totalHPGain,
                'total_cost' => 0, // ✅ Set to 0 (keep column but don't use it)
            ]);

            // Add parts
            foreach ($validated['parts'] as $part) {
                ProjectPart::create([
                    'project_id' => $project->id,
                    'power_mod_id' => $part['power_mod_id'],
                    'hp_gain' => $part['hp_gain'],
                    'torque_nm_gain' => $part['torque_nm_gain'],
                    'price' => 0, // ✅ Set to 0 (keep column but don't use it)
                ]);
            }

            // ✅ Check for achievements and return newly unlocked ones
            $newAchievements = $this->checkAchievements($request->user(), $project);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Build saved successfully!',
                'project' => $project->load(['car.model.make', 'engine', 'parts.powerMod']),
                'newAchievements' => $newAchievements,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to save build: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $project = Project::with(['car.model.make', 'engine', 'parts.powerMod', 'user'])
            ->findOrFail($id);

        // Check if user can view (public or owner)
        if (!$project->is_public && $project->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'This build is private',
            ], 403);
        }

        // Increment views
        $project->increment('views');

        return response()->json([
            'success' => true,
            'project' => $project,
        ]);
    }

    public function destroy($id)
    {
        $project = Project::where('user_id', auth()->id())->findOrFail($id);
        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'Build deleted successfully',
        ]);
    }

    private function checkAchievements($user, $project)
    {
        $achievements = \App\Models\Achievement::all();
        $userAchievementIds = $user->achievements->pluck('id')->toArray();
        $newlyUnlocked = [];

        foreach ($achievements as $achievement) {
            // Skip if already unlocked
            if (in_array($achievement->id, $userAchievementIds)) {
                continue;
            }

            $unlocked = false;

            switch ($achievement->requirement_type) {
                case 'count':
                    if ($achievement->category === 'builds') {
                        $count = $user->projects()->count();
                        $unlocked = $count >= $achievement->requirement_value;
                    } elseif ($achievement->category === 'parts') {
                        $count = $project->parts()->count();
                        $unlocked = $count >= $achievement->requirement_value;
                    }
                    break;

                case 'threshold':
                    if ($achievement->category === 'hp') {
                        $unlocked = $project->final_hp >= $achievement->requirement_value;
                    }
                    break;

                case 'special':
                    // ✅ Removed budget-build achievement check
                    if ($achievement->slug === 'bmw-specialist') {
                        $bmwCount = $user->projects()
                            ->whereHas('car.model.make', function($q) {
                                $q->where('name', 'BMW');
                            })
                            ->count();
                        $unlocked = $bmwCount >= 3;
                    }
                    break;
            }

            if ($unlocked) {
                $user->achievements()->attach($achievement->id, [
                    'unlocked_at' => now()
                ]);
                $newlyUnlocked[] = $achievement;
            }
        }

        return $newlyUnlocked;
    }
}
