<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Engine extends Model {
    protected $fillable = ['code', 'subvariant', 'fuel_type', 'cylinder', 'enginecreatedat', 'engine_discontinued'];
    public $timestamps = true;

    public function cars() {
        return $this->belongsToMany(Car::class, 'car_engine')->withPivot('power_hp', 'torque_nm', 'acceleration_0_100', 'top_speed');
    }
}
