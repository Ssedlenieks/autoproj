<?php

namespace App\Models;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable {
  use Notifiable;

  protected $fillable = [
    'name', 'email', 'username', 'password',
    'bio', 'avatar_url', 'country', 'favourite_car',
    'xp', 'level', 'badges', 'role_id',
  ];

  protected $hidden = ['password', 'remember_token'];

  protected $casts = [
    'email_verified_at' => 'datetime',
    'badges' => 'array',
  ];

  public function role() {
    return $this->belongsTo(Role::class);
  }

  public function isAdmin(): bool {
    return $this->role?->slug === 'admin';
  }

  public function isModerator(): bool {
    return $this->role?->slug === 'moderator';
  }

  public function addXp(int $amount): void {
    $this->xp += $amount;
    $this->level = floor($this->xp / 1000) + 1;
    $this->save();
  }

  public function addBadge(string $badge): void {
    $badges = $this->badges ?? [];
    if (!in_array($badge, $badges)) {
      $badges[] = $badge;
      $this->update(['badges' => $badges]);
    }
  }

  public function removeBadge(string $badge): void {
    $badges = $this->badges ?? [];
    $badges = array_filter($badges, fn($b) => $b !== $badge);
    $this->update(['badges' => array_values($badges)]);
  }
}
