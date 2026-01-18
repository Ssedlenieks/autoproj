<?php
namespace App\Http\Controllers\Api;

use App\Models\Car;
use App\Models\VehicleModel;
use App\Models\Engine;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CarController extends Controller {
    // GET /api/models?make_id=1
    public function index(Request $request) {
        if ($request->has('make_id')) {
            return VehicleModel::where('make_id', $request->make_id)
                ->get(['id', 'name', 'make_id']);
        }

        // GET /api/cars?model_id=1
        if ($request->has('model_id')) {
            return Car::where('model_id', $request->model_id)
                ->with('engines')
                ->get(['id', 'model_id', 'year', 'trim', 'body_style', 'drive_type', 'weight_kg']);
        }

        return [];
    }

    // GET /api/cars/2573
    public function show($id) {
        return Car::with('engines', 'powerMods')
            ->findOrFail($id);
    }
}
