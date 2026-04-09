<?php
// app/Models/Achievement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Achievement extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'category',
        'points',
        'requirement_type',
        'requirement_value',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_achievements')
            ->withTimestamps()
            ->withPivot('unlocked_at');
    }
}
