<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyChallenge;
use App\Models\UserChallengeProgress;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DailyChallengeController extends Controller
{
    public function index(Request $request)
    {
        $user  = $request->user();
        $today = now()->toDateString();

        $existing = UserChallengeProgress::where('user_id', $user->id)
            ->where('date', $today)
            ->with('challenge')
            ->get();

if ($existing->isEmpty()) {
    $randomChallenges = DailyChallenge::all()->shuffle()->take(3);

    foreach ($randomChallenges as $challenge) {
        UserChallengeProgress::create([
            'user_id'      => $user->id,
            'challenge_id' => $challenge->id,
            'date'         => $today,
            'completed'    => false,
        ]);
    }

    $existing = UserChallengeProgress::where('user_id', $user->id)
        ->where('date', $today)
        ->with('challenge')
        ->get();
}

        $challenges = $existing->map(fn($p) => [
            'id'          => $p->challenge->id,
            'key'         => $p->challenge->key,
            'title'       => $p->challenge->title,
            'description' => $p->challenge->description,
            'points'      => $p->challenge->points,
            'completed'   => (bool) $p->completed,
        ]);

        return response()->json(['success' => true, 'challenges' => $challenges]);
    }

public static function completeForUser(User $user, string $key): void
{
    $today     = now()->toDateString();
    $challenge = DailyChallenge::where('key', $key)->first();

    if (!$challenge) return;

    DB::table('user_challenge_progress')
        ->where('user_id', $user->id)
        ->where('challenge_id', $challenge->id)
        ->where('date', $today)
        ->where('completed', false)
        ->update(['completed' => true]);
}
}