<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleModel extends Model {
    protected $table = 'models';
    protected $fillable = ['make_id', 'name'];
    public $timestamps = true;

    public function make() {
        return $this->belongsTo(Make::class);
    }

    public function cars() {
        return $this->hasMany(Car::class, 'model_id');
    }
}
