<?php
// app/Models/Project.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'user_id',
        'car_id',
        'engine_id',
        'project_name',
        'description',
        'base_hp',
        'base_torque',
        'total_hp_gain',
        'total_torque_gain',
        'final_hp',
        'total_cost',
        'is_public',
        'views',
        'likes',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'total_cost' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function engine(): BelongsTo
    {
        return $this->belongsTo(Engine::class);
    }

    public function parts(): HasMany
    {
        return $this->hasMany(ProjectPart::class);
    }

    // Helper: Calculate HP per dollar
    public function hpPerDollar()
    {
        return $this->total_cost > 0
            ? round($this->total_hp_gain / $this->total_cost, 2)
            : 0;
    }
}
