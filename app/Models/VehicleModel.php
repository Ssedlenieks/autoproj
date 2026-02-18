<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;

class VehicleModel extends BaseModel
{
    protected $table = 'models';
    protected $fillable = ['make_id', 'name'];
    public $timestamps = false;

    public function make()
    {
        return $this->belongsTo(Make::class);
    }

    public function cars()
    {
        return $this->hasMany(Car::class, 'model_id');
    }
}
