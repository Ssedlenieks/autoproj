<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Make extends Model {
    protected $fillable = ['name'];
    public $timestamps = true;

    public function models() {
        return $this->hasMany(VehicleModel::class);
    }
}
