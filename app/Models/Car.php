<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Car extends Model {
    protected $fillable = ['model_id', 'year', 'trim', 'body_style', 'drive_type', 'weight_kg', 'image_path'];
    public $timestamps = true;

    public function model() {
        return $this->belongsTo(VehicleModel::class, 'model_id');
    }

    public function engines() {
        return $this->belongsToMany(Engine::class, 'car_engine')->withPivot('power_hp', 'torque_nm', 'acceleration_0_100', 'top_speed');
    }

    public function powerMods() {
        return $this->belongsToMany(PowerMod::class, 'power_mod_variants', 'car_id', 'power_mod_id')
            ->withPivot('engine_id', 'hp_gain', 'torque_nm_gain', 'notes');
    }
}
