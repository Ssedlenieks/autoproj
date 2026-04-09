<?php
// app/Models/ProjectPart.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectPart extends Model
{
    protected $fillable = [
        'project_id',
        'power_mod_id',
        'hp_gain',
        'torque_nm_gain',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function powerMod(): BelongsTo
    {
        return $this->belongsTo(PowerMod::class);
    }
}
