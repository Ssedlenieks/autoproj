<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PowerMod;
use App\Models\Car;
use App\Models\Engine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PowerModController extends Controller
{
    /**
     * Get all power mods (for Editor Panel)
     */
    public function index()
    {
        $mods = PowerMod::orderBy('category')->orderBy('name')->get();
        return response()->json($mods);
    }

    /**
     * Get available power mods for a specific car and engine
     * Grouped by category
     */
    public function getAvailableParts($carId, $engineId)
    {
        try {
            $carEngine = DB::table('car_engine')
                ->where('car_id', $carId)
                ->where('engine_id', $engineId)
                ->first();

            if (!$carEngine) {
                return response()->json([
                    'success' => false,
                    'message' => 'Car/Engine combination not found in database'
                ], 404);
            }

            $car = Car::with('model.make')->findOrFail($carId);
            $engine = Engine::findOrFail($engineId);

            $parts = PowerMod::with(['variants' => function($query) use ($carId, $engineId) {
                $query->where('car_id', $carId)
                      ->where('engine_id', $engineId);
            }])
            ->whereHas('variants', function($query) use ($carId, $engineId) {
                $query->where('car_id', $carId)
                      ->where('engine_id', $engineId);
            })
            ->get()
            ->filter(function($part) {
                return $part->variants->count() > 0;
            })
            ->groupBy('category')
            ->map(function($categoryParts) {
                return $categoryParts->map(function($part) {
                    $variant = $part->variants->first();
                    return [
                        'id'              => $part->id,
                        'name'            => $part->name,
                        'brand'           => $part->brand,
                        'category'        => $part->category,
                        'price'           => $part->price ?? 0,
                        'is_estimate'     => $part->is_estimate,
                        'notes'           => $variant->notes ?? $part->notes,
                        'hp_gain'         => $variant->hp_gain ?? 0,
                        'torque_nm_gain'  => $variant->torque_nm_gain ?? 0,
                        'youtube_url'     => $part->youtube_url,
                        'youtube_channel' => $part->youtube_channel,
                    ];
                });
            });

            return response()->json([
                'success'          => true,
                'carId'            => $carId,
                'engineId'         => $engineId,
                'totalParts'       => $parts->flatten()->count(),
                'categories'       => $parts,
                'baseHP'           => $carEngine->power_hp ?? 0,
                'baseTorque'       => $carEngine->torque_nm ?? 0,
                'acceleration0100' => $carEngine->acceleration_0_100,
                'topSpeed'         => $carEngine->top_speed,
                'car' => [
                    'id'    => $car->id,
                    'make'  => $car->model->make->name,
                    'model' => $car->model->name,
                    'trim'  => $car->trim,
                    'year'  => $car->year,
                ],
                'engine' => [
                    'id'         => $engine->id,
                    'code'       => $engine->code,
                    'subvariant' => $engine->subvariant,
                    'fuel_type'  => $engine->fuel_type,
                    'cylinder'   => $engine->cylinder,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load power mods: ' . $e->getMessage()
            ], 500);
        }
    }
}