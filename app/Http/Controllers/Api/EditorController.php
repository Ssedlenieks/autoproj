<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Make;
use App\Models\CarModel;
use App\Models\Car;
use App\Models\Engine;
use App\Models\CarEngine;
use App\Models\PowerMod;
use App\Models\PowerModVariant;
use Illuminate\Http\Request;

class EditorController extends Controller
{
    // ================================
    // MAKES
    // ================================
    public function storeMake(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:makes,name'],
        ]);
        $make = Make::create($validated);
        return response()->json(['success' => true, 'make' => $make], 201);
    }

    public function updateMake(Request $request, $id)
    {
        $make = Make::findOrFail($id);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:makes,name,' . $id],
        ]);
        $make->update($validated);
        return response()->json(['success' => true, 'make' => $make]);
    }

    public function deleteMake($id)
    {
        Make::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Make deleted']);
    }

    // ================================
    // MODELS
    // ================================
    public function storeModel(Request $request)
    {
        $validated = $request->validate([
            'make_id' => ['required', 'exists:makes,id'],
            'name'    => ['required', 'string', 'max:255'],
        ]);
        $model = CarModel::create($validated);
        return response()->json(['success' => true, 'model' => $model], 201);
    }

    public function updateModel(Request $request, $id)
    {
        $model = CarModel::findOrFail($id);
        $validated = $request->validate([
            'make_id' => ['sometimes', 'exists:makes,id'],
            'name'    => ['sometimes', 'string', 'max:255'],
        ]);
        $model->update($validated);
        return response()->json(['success' => true, 'model' => $model]);
    }

    public function deleteModel($id)
    {
        CarModel::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Model deleted']);
    }

    // ================================
    // CARS
    // ================================
    public function storeCar(Request $request)
    {
        $validated = $request->validate([
            'model_id'   => ['required', 'exists:models,id'],
            'year'       => ['required', 'integer', 'min:1900', 'max:2100'],
            'trim'       => ['required', 'string', 'max:255'],
            'body_style' => ['required', 'string', 'max:255'],
            'drive_type' => ['required', 'string', 'max:255'],
            'weight_kg'  => ['nullable', 'integer'],
            'image_url'  => ['nullable', 'string', 'max:255'],
        ]);
        $car = Car::create($validated);
        return response()->json(['success' => true, 'car' => $car], 201);
    }

    public function updateCar(Request $request, $id)
    {
        $car = Car::findOrFail($id);
        $validated = $request->validate([
            'model_id'   => ['sometimes', 'exists:models,id'],
            'year'       => ['sometimes', 'integer', 'min:1900', 'max:2100'],
            'trim'       => ['sometimes', 'string', 'max:255'],
            'body_style' => ['sometimes', 'string', 'max:255'],
            'drive_type' => ['sometimes', 'string', 'max:255'],
            'weight_kg'  => ['nullable', 'integer'],
            'image_url'  => ['nullable', 'string', 'max:255'],
        ]);
        $car->update($validated);
        return response()->json(['success' => true, 'car' => $car]);
    }

    public function deleteCar($id)
    {
        Car::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Car deleted']);
    }

    // ================================
    // ENGINES
    // ================================
    public function getEngines(Request $request)
    {
        $query = Engine::select(
                'engines.id',
                'engines.code',
                'engines.subvariant',
                'engines.fuel_type',
                'engines.cylinder',
                'car_engine.power_hp',
                'car_engine.torque_nm',
                'cars.trim as car_trim',
                'cars.year as car_year',
                'models.name as model_name',
                'makes.name as make_name'
            )
            ->join('car_engine', 'engines.id', '=', 'car_engine.engine_id')
            ->join('cars', 'car_engine.car_id', '=', 'cars.id')
            ->join('models', 'cars.model_id', '=', 'models.id')
            ->join('makes', 'models.make_id', '=', 'makes.id');

        if ($request->fuel_type)
            $query->where('engines.fuel_type', $request->fuel_type);

        if ($request->cylinders)
            $query->where('engines.cylinder', $request->cylinders);

        if ($request->min_hp)
            $query->where('car_engine.power_hp', '>=', $request->min_hp);

        if ($request->max_hp)
            $query->where('car_engine.power_hp', '<=', $request->max_hp);

        return response()->json(['success' => true, 'engines' => $query->get()]);
    }

    public function storeEngine(Request $request)
    {
        $validated = $request->validate([
            'code'       => ['required', 'string', 'max:255'],
            'subvariant' => ['nullable', 'string', 'max:255'],
            'fuel_type'  => ['required', 'string', 'max:255'],
            'cylinder'   => ['nullable', 'string', 'max:255'],
        ]);
        $engine = Engine::create($validated);
        return response()->json(['success' => true, 'engine' => $engine], 201);
    }

    public function updateEngine(Request $request, $id)
    {
        $engine = Engine::findOrFail($id);
        $validated = $request->validate([
            'code'       => ['sometimes', 'string', 'max:255'],
            'subvariant' => ['nullable', 'string', 'max:255'],
            'fuel_type'  => ['sometimes', 'string', 'max:255'],
            'cylinder'   => ['nullable', 'string', 'max:255'],
        ]);
        $engine->update($validated);
        return response()->json(['success' => true, 'engine' => $engine]);
    }

    public function deleteEngine($id)
    {
        Engine::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Engine deleted']);
    }

    // ================================
    // CAR-ENGINE LINKS
    // ================================
    public function storeCarEngine(Request $request)
    {
        $validated = $request->validate([
            'car_id'            => ['required', 'exists:cars,id'],
            'engine_id'         => ['required', 'exists:engines,id'],
            'power_hp'          => ['required', 'integer'],
            'torque_nm'         => ['required', 'integer'],
            'acceleration_0100' => ['nullable', 'numeric'],
            'top_speed'         => ['nullable', 'integer'],
        ]);
        $carEngine = CarEngine::create($validated);
        return response()->json(['success' => true, 'car_engine' => $carEngine], 201);
    }

    public function updateCarEngine(Request $request, $id)
    {
        $carEngine = CarEngine::findOrFail($id);
        $validated = $request->validate([
            'power_hp'          => ['sometimes', 'integer'],
            'torque_nm'         => ['sometimes', 'integer'],
            'acceleration_0100' => ['nullable', 'numeric'],
            'top_speed'         => ['nullable', 'integer'],
        ]);
        $carEngine->update($validated);
        return response()->json(['success' => true, 'car_engine' => $carEngine]);
    }

    public function deleteCarEngine($id)
    {
        CarEngine::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Car-engine link deleted']);
    }

    // ================================
    // POWER MODS
    // ================================
    public function storePowerMod(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'brand'       => ['nullable', 'string', 'max:255'],
            'category'    => ['required', 'string', 'max:255'],
            'is_estimate' => ['boolean'],
            'notes'       => ['nullable', 'string'],
        ]);
        $mod = PowerMod::create($validated);
        return response()->json(['success' => true, 'power_mod' => $mod], 201);
    }

    public function updatePowerMod(Request $request, $id)
    {
        $mod = PowerMod::findOrFail($id);
        $validated = $request->validate([
            'name'        => ['sometimes', 'string', 'max:255'],
            'brand'       => ['nullable', 'string', 'max:255'],
            'category'    => ['sometimes', 'string', 'max:255'],
            'is_estimate' => ['boolean'],
            'notes'       => ['nullable', 'string'],
        ]);
        $mod->update($validated);
        return response()->json(['success' => true, 'power_mod' => $mod]);
    }

    public function deletePowerMod($id)
    {
        PowerMod::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Power mod deleted']);
    }

    // ================================
    // POWER MOD VARIANTS
    // ================================
    public function storePowerModVariant(Request $request)
    {
        $validated = $request->validate([
            'power_mod_id'   => ['required', 'exists:power_mods,id'],
            'car_id'         => ['required', 'exists:cars,id'],
            'engine_id'      => ['required', 'exists:engines,id'],
            'hp_gain'        => ['required', 'integer'],
            'torque_nm_gain' => ['required', 'integer'],
            'notes'          => ['nullable', 'string'],
        ]);
        $variant = PowerModVariant::create($validated);
        return response()->json(['success' => true, 'variant' => $variant], 201);
    }

    public function updatePowerModVariant(Request $request, $id)
    {
        $variant = PowerModVariant::findOrFail($id);
        $validated = $request->validate([
            'hp_gain'        => ['sometimes', 'integer'],
            'torque_nm_gain' => ['sometimes', 'integer'],
            'notes'          => ['nullable', 'string'],
        ]);
        $variant->update($validated);
        return response()->json(['success' => true, 'variant' => $variant]);
    }

    public function deletePowerModVariant($id)
    {
        PowerModVariant::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Variant deleted']);
    }
}