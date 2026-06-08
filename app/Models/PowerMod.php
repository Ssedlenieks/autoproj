<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PowerMod extends Model
{
    protected $table = 'power_mods';
    protected $fillable = [
        'name',
        'brand',
        'category',
        'notes',
        'is_estimate',
        'youtube_url',
        'youtube_channel',
    ];
    public $timestamps = true;

    public function variants()
    {
        return $this->hasMany(PowerModVariant::class, 'power_mod_id');
    }

    public function cars()
    {
        return $this->belongsToMany(Car::class, 'power_mod_variants', 'power_mod_id', 'car_id')
            ->withPivot('engine_id', 'hp_gain', 'torque_nm_gain', 'notes');
    }
}