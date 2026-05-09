<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserChallengeProgress extends Model
{
    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = ['user_id', 'challenge_id', 'completed', 'date'];

    protected $casts = [
        'date'      => 'date',
        'completed' => 'boolean',
    ];

    public function challenge()
    {
        return $this->belongsTo(DailyChallenge::class, 'challenge_id');
    }
}