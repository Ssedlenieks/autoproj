<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PowerModVariant extends Model
{
    protected $table = 'power_mod_variants';
    protected $fillable = [
        'power_mod_id',
        'car_id',
        'engine_id',
        'hp_gain',
        'torque_nm_gain',
        'notes'
    ];
    public $timestamps = true;

    public function powerMod()
    {
        return $this->belongsTo(PowerMod::class, 'power_mod_id');
    }

    public function car()
    {
        return $this->belongsTo(Car::class, 'car_id');
    }

    public function engine()
    {
        return $this->belongsTo(Engine::class, 'engine_id');
    }
}
