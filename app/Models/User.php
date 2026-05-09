<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'bio',
        'avatar_url',
        'country',
        'favourite_car',
        'role_id',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = ['avatar_url'];

    // ================================
    // ACCESSORS
    // ================================

    public function getAvatarUrlAttribute()
    {
        if ($this->attributes['avatar'] ?? null) {
            return Storage::url($this->attributes['avatar']);
        }
        return null;
    }

    // ================================
    // RELATIONSHIPS
    // ================================

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function achievements(): BelongsToMany
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements')
            ->withTimestamps()
            ->withPivot('unlocked_at');
    }

    // ================================
    // ROLE CHECKS
    // ================================

    public function isAdmin(): bool
    {
        return $this->role?->slug === 'admin';
    }

    public function isModerator(): bool
    {
        return $this->role?->slug === 'moderator';
    }

    // ================================
    // GAMIFICATION - PROGRESSIVE LEVELING
    // ================================

    /**
     * Get total points from achievements + completed daily challenges
     */
    public function totalPoints(): int
    {
        $achievementPoints = $this->achievements()->sum('points');

        $challengePoints = DB::table('user_challenge_progress')
            ->join('daily_challenges', 'daily_challenges.id', '=', 'user_challenge_progress.challenge_id')
            ->where('user_challenge_progress.user_id', $this->id)
            ->where('user_challenge_progress.completed', true)
            ->sum('daily_challenges.points');

        return $achievementPoints + (int) $challengePoints;
    }

    /**
     * Calculate current level based on points
     * Progressive formula: Each level requires 25% more points than previous
     * Level 1: 100 pts
     * Level 2: 225 pts (100 + 125)
     * Level 3: 381 pts (225 + 156)
     * Level 4: 576 pts (381 + 195)
     */
    public function level(): int
    {
        $points = $this->totalPoints();
        $level = 1;
        $requiredPoints = 0;
        $pointsForNextLevel = 100;

        while ($points >= $requiredPoints + $pointsForNextLevel) {
            $requiredPoints += $pointsForNextLevel;
            $level++;
            $pointsForNextLevel = (int) ceil($pointsForNextLevel * 1.25);
        }

        return $level;
    }

    /**
     * Points required for current level
     */
    public function pointsForCurrentLevel(): int
    {
        $points = $this->totalPoints();
        $level = 1;
        $requiredPoints = 0;
        $pointsForNextLevel = 100;

        while ($points >= $requiredPoints + $pointsForNextLevel) {
            $requiredPoints += $pointsForNextLevel;
            $level++;
            $pointsForNextLevel = (int) ceil($pointsForNextLevel * 1.25);
        }

        return $requiredPoints;
    }

    /**
     * Points required for next level
     */
    public function pointsForNextLevel(): int
    {
        $currentLevel = $this->level();
        $level = 1;
        $totalRequired = 0;
        $increment = 100;

        while ($level <= $currentLevel) {
            $totalRequired += $increment;
            $level++;
            $increment = (int) ceil($increment * 1.25);
        }

        return $totalRequired;
    }

    /**
     * Points needed to reach next level
     */
    public function pointsToNextLevel(): int
    {
        return $this->pointsForNextLevel() - $this->totalPoints();
    }

    /**
     * Progress percentage to next level (0-100)
     */
    public function levelProgress(): float
    {
        $currentLevelPoints = $this->pointsForCurrentLevel();
        $nextLevelPoints = $this->pointsForNextLevel();
        $totalPoints = $this->totalPoints();

        $pointsInLevel = $totalPoints - $currentLevelPoints;
        $pointsNeededForLevel = $nextLevelPoints - $currentLevelPoints;

        return $pointsNeededForLevel > 0
            ? round(($pointsInLevel / $pointsNeededForLevel) * 100, 1)
            : 100;
    }

    /**
     * Get rank/title based on level
     */
    public function rank(): string
    {
        $level = $this->level();

        return match(true) {
            $level >= 50 => 'Legend',
            $level >= 40 => 'Master Tuner',
            $level >= 30 => 'Pro Builder',
            $level >= 20 => 'Expert',
            $level >= 15 => 'Advanced',
            $level >= 10 => 'Intermediate',
            $level >= 5  => 'Enthusiast',
            default      => 'Beginner',
        };
    }

    /**
     * Get level color for UI
     */
    public function levelColor(): string
    {
        $level = $this->level();

        return match(true) {
            $level >= 40 => '#ff0000',
            $level >= 30 => '#ff00ff',
            $level >= 20 => '#ffd700',
            $level >= 15 => '#ff8c00',
            $level >= 10 => '#9370db',
            $level >= 5  => '#1e90ff',
            default      => '#10b981',
        };
    }

    // ================================
    // LEGACY BADGE SYSTEM
    // ================================

    public function addBadge(string $badge): void
    {
        $badges = $this->badges ?? [];
        if (!in_array($badge, $badges)) {
            $badges[] = $badge;
            $this->update(['badges' => array_values($badges)]);
        }
    }

    public function removeBadge(string $badge): void
    {
        $badges = $this->badges ?? [];
        $badges = array_filter($badges, fn($b) => $b !== $badge);
        $this->update(['badges' => array_values($badges)]);
    }
}