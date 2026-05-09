<?php

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
            'success'  => true,
            'projects' => $projects,
        ]);
    }

    /**
     * Citu lietotāju publiskie projekti — paginēti pa 12
     */
    public function publicIndex(Request $request)
    {
        $projects = Project::with([
                'user:id,name,avatar',
                'car.model.make',
                'engine',
                'parts.powerMod',
            ])
            ->where('is_public', 1)
            ->where('user_id', '!=', auth()->id())
            ->latest()
            ->paginate(12);

        return response()->json([
            'success'  => true,
            'projects' => $projects,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'car_id'                   => 'required|exists:cars,id',
            'engine_id'                => 'required|exists:engines,id',
            'project_name'             => 'required|string|max:255',
            'description'              => 'nullable|string',
            'base_hp'                  => 'required|integer',
            'base_torque'              => 'required|integer',
            'is_public'                => 'boolean',
            'parts'                    => 'required|array|min:1',
            'parts.*.power_mod_id'     => 'required|exists:power_mods,id',
            'parts.*.hp_gain'          => 'required|integer',
            'parts.*.torque_nm_gain'   => 'required|integer',
        ]);

        DB::beginTransaction();
        try {
            $totalHPGain     = collect($validated['parts'])->sum('hp_gain');
            $totalTorqueGain = collect($validated['parts'])->sum('torque_nm_gain');
            $finalHp         = $validated['base_hp'] + $totalHPGain;

            $project = Project::create([
                'user_id'            => $request->user()->id,
                'car_id'             => $validated['car_id'],
                'engine_id'          => $validated['engine_id'],
                'project_name'       => $validated['project_name'],
                'description'        => $validated['description'],
                'base_hp'            => $validated['base_hp'],
                'base_torque'        => $validated['base_torque'],
                'total_hp_gain'      => $totalHPGain,
                'total_torque_gain'  => $totalTorqueGain,
                'final_hp'           => $finalHp,
                'final_torque'       => $validated['base_torque'] + $totalTorqueGain,
                'total_cost'         => 0,
                'is_public'          => $validated['is_public'] ?? false,
            ]);

            $partModIds = [];
            foreach ($validated['parts'] as $part) {
                ProjectPart::create([
                    'project_id'     => $project->id,
                    'power_mod_id'   => $part['power_mod_id'],
                    'hp_gain'        => $part['hp_gain'],
                    'torque_nm_gain' => $part['torque_nm_gain'],
                    'price'          => 0,
                ]);
                $partModIds[] = $part['power_mod_id'];
            }

            $newAchievements = $this->checkAchievements($request->user(), $project);

            // ── Daily challenge completions ──────────────────────────────
            $user = $request->user();

            DailyChallengeController::completeForUser($user, 'save_build');
            DailyChallengeController::completeForUser($user, 'new_build');

            if ($finalHp >= 400) {
                DailyChallengeController::completeForUser($user, 'reach_400hp');
            }
            if ($finalHp >= 500) {
                DailyChallengeController::completeForUser($user, 'reach_500hp');
            }

            $partCategories = \App\Models\PowerMod::whereIn('id', $partModIds)
                ->pluck('category')
                ->map(fn($c) => strtolower($c))
                ->toArray();

            if (in_array('turbo', $partCategories)) {
                DailyChallengeController::completeForUser($user, 'add_turbo');
            }
            if (in_array('intake', $partCategories)) {
                DailyChallengeController::completeForUser($user, 'add_intake');
            }
            if (in_array('exhaust', $partCategories)) {
                DailyChallengeController::completeForUser($user, 'add_exhaust');
            }
            if (in_array('ecu', $partCategories)) {
                DailyChallengeController::completeForUser($user, 'add_ecu');
            }

            $make = $project->car->model->make->name ?? '';
            $europeanMakes = ['BMW', 'Mercedes', 'Audi', 'Volkswagen', 'Peugeot', 'Renault', 'Fiat', 'Volvo', 'Porsche', 'Seat', 'Skoda', 'Opel'];
            if (in_array($make, $europeanMakes)) {
                DailyChallengeController::completeForUser($user, 'euro_build');
            }

            if (!empty($validated['is_public'])) {
                DailyChallengeController::completeForUser($user, 'make_public');
            }
            // ────────────────────────────────────────────────────────────

            DB::commit();

            return response()->json([
                'success'         => true,
                'message'         => 'Build saved successfully!',
                'project'         => $project->load(['car.model.make', 'engine', 'parts.powerMod']),
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

    public function update(Request $request, $id)
    {
        $project = Project::where('user_id', auth()->id())->findOrFail($id);

        $validated = $request->validate([
            'car_id'                   => 'required|exists:cars,id',
            'engine_id'                => 'required|exists:engines,id',
            'project_name'             => 'required|string|max:255',
            'description'              => 'nullable|string',
            'base_hp'                  => 'required|integer',
            'base_torque'              => 'required|integer',
            'is_public'                => 'boolean',
            'parts'                    => 'required|array|min:1',
            'parts.*.power_mod_id'     => 'required|exists:power_mods,id',
            'parts.*.hp_gain'          => 'required|integer',
            'parts.*.torque_nm_gain'   => 'required|integer',
        ]);

        DB::beginTransaction();
        try {
            $totalHPGain     = collect($validated['parts'])->sum('hp_gain');
            $totalTorqueGain = collect($validated['parts'])->sum('torque_nm_gain');

            $project->update([
                'car_id'            => $validated['car_id'],
                'engine_id'         => $validated['engine_id'],
                'project_name'      => $validated['project_name'],
                'description'       => $validated['description'] ?? $project->description,
                'base_hp'           => $validated['base_hp'],
                'base_torque'       => $validated['base_torque'],
                'total_hp_gain'     => $totalHPGain,
                'total_torque_gain' => $totalTorqueGain,
                'final_hp'          => $validated['base_hp'] + $totalHPGain,
                'final_torque'      => $validated['base_torque'] + $totalTorqueGain,
                'is_public'         => $validated['is_public'] ?? $project->is_public,
            ]);

            // Dzēst vecās detaļas un ierakstīt jaunās
            $project->parts()->delete();

            foreach ($validated['parts'] as $part) {
                ProjectPart::create([
                    'project_id'     => $project->id,
                    'power_mod_id'   => $part['power_mod_id'],
                    'hp_gain'        => $part['hp_gain'],
                    'torque_nm_gain' => $part['torque_nm_gain'],
                    'price'          => 0,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Projekts atjaunināts!',
                'project' => $project->load(['car.model.make', 'engine', 'parts.powerMod']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Neizdevās atjaunināt: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function toggleVisibility(Request $request, $id)
    {
        $project = Project::where('user_id', auth()->id())->findOrFail($id);
        $project->is_public = !$project->is_public;
        $project->save();

        if ($project->is_public) {
            DailyChallengeController::completeForUser($request->user(), 'make_public');
        }

        return response()->json([
            'success'   => true,
            'is_public' => $project->is_public,
            'message'   => $project->is_public ? 'Projekts publisks' : 'Projekts privāts',
        ]);
    }

    public function show($id)
    {
        $project = Project::with([
                'car.model.make',
                'engine',
                'parts.powerMod',
                'user:id,name,avatar',
            ])
            ->findOrFail($id);

        if (!$project->is_public && $project->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'This build is private',
            ], 403);
        }

        $project->increment('views');

        // Pievienot projects_count autoram
        $project->user->projects_count = Project::where('user_id', $project->user_id)
            ->where('is_public', 1)
            ->count();

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
        $achievements       = \App\Models\Achievement::all();
        $userAchievementIds = $user->achievements->pluck('id')->toArray();
        $newlyUnlocked      = [];

        foreach ($achievements as $achievement) {
            if (in_array($achievement->id, $userAchievementIds)) {
                continue;
            }

            $unlocked = false;

            switch ($achievement->requirement_type) {
                case 'count':
                    if ($achievement->category === 'builds') {
                        $unlocked = $user->projects()->count() >= $achievement->requirement_value;
                    } elseif ($achievement->category === 'parts') {
                        $unlocked = $project->parts()->count() >= $achievement->requirement_value;
                    }
                    break;

                case 'threshold':
                    if ($achievement->category === 'hp') {
                        $unlocked = $project->final_hp >= $achievement->requirement_value;
                    } elseif ($achievement->category === 'torque') {
                        $unlocked = $project->final_torque >= $achievement->requirement_value;
                    }
                    break;

                case 'special':
                    if ($achievement->slug === 'bmw-specialist') {
                        $bmwCount = $user->projects()
                            ->whereHas('car.model.make', fn($q) => $q->where('name', 'BMW'))
                            ->count();
                        $unlocked = $bmwCount >= 3;
                    }
                    break;
            }

            if ($unlocked) {
                $user->achievements()->attach($achievement->id, ['unlocked_at' => now()]);
                $newlyUnlocked[] = $achievement;
            }
        }

        return $newlyUnlocked;
    }
}