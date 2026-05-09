<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class RotateDailyChallenges extends Command
{
    protected $signature = 'challenges:rotate';
    protected $description = 'Pick 3 random daily challenges for today';

    public function handle(): void
    {
        $today = Carbon::today()->toDateString();

        // Already rotated today? Skip
        $alreadySet = DB::table('active_daily_challenges')
            ->where('date', $today)
            ->exists();

        if ($alreadySet) {
            $this->info('Challenges already set for today.');
            return;
        }

        $challenges = DB::table('daily_challenges')
            ->inRandomOrder()
            ->limit(3)
            ->pluck('id');

        foreach ($challenges as $challengeId) {
            DB::table('active_daily_challenges')->insert([
                'challenge_id' => $challengeId,
                'date'         => $today,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        $this->info("Rotated challenges for {$today}.");
    }
}