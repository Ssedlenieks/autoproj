<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DailyChallengeSeeder extends Seeder
{
    public function run(): void
    {
        $challenges = [
            ['key' => 'save_build',    'title' => 'Saglabā projektu',      'description' => 'Saglabā jebkuru automašīnas projektu šodien',    'points' => 50],
            ['key' => 'add_turbo',     'title' => 'Pieliec turbo',          'description' => 'Pievieno turbokompresoru jebkuram projektam',     'points' => 75],
            ['key' => 'reach_400hp',   'title' => 'Liela jauda',            'description' => 'Sasniegt 400+ ZS jebkurā projektā',              'points' => 100],
            ['key' => 'reach_500hp',   'title' => 'Briesmīgais projekts',   'description' => 'Sasniegt 500+ ZS jebkurā projektā',              'points' => 150],
            ['key' => 'make_public',   'title' => 'Parādi pasaulei',        'description' => 'Publisko kādu no saviem projektiem',             'points' => 40],
            ['key' => 'add_intake',    'title' => 'Dziļa elpa',             'description' => 'Pievieno gaisa filtru jebkuram projektam',       'points' => 40],
            ['key' => 'add_exhaust',   'title' => 'Skaļš un lepns',         'description' => 'Pievieno izpūtēju jebkuram projektam',           'points' => 40],
            ['key' => 'add_ecu',       'title' => 'Dzinēja tūnings',        'description' => 'Pievieno ECU tūningu jebkuram projektam',        'points' => 60],
            ['key' => 'new_build',     'title' => 'Jauns sākums',           'description' => 'Uzsāc pilnīgi jaunu projektu šodien',            'points' => 50],
            ['key' => 'euro_build',    'title' => 'Euro stils',             'description' => 'Saglabā projektu ar Eiropas automašīnu',         'points' => 60],
            ['key' => 'visit_leader',  'title' => 'Izpēti konkurentus',     'description' => 'Apmeklē līderu sarakstu',                        'points' => 20],
        ];

        DB::table('daily_challenges')->upsert(
            $challenges,
            ['key'],           // unique key to match on
            ['title', 'description', 'points']  // columns to update if exists
        );
    }
}