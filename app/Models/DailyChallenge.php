<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyChallenge extends Model
{
    protected $fillable = ['key', 'title', 'description', 'points'];

    public function progress()
    {
        return $this->hasMany(UserChallengeProgress::class, 'challenge_id');
    }
}