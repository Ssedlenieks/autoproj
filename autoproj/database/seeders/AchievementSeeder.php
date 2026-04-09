<?php
// database/seeders/AchievementSeeder.php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    public function run()
    {
        $achievements = [
            // Build Count Achievements
            [
                'name' => 'First Steps',
                'slug' => 'first-build',
                'description' => 'Create your first build',
                'icon' => '🚗',
                'category' => 'builds',
                'points' => 10,
                'requirement_type' => 'count',
                'requirement_value' => 1,
            ],
            [
                'name' => 'Collector',
                'slug' => 'five-builds',
                'description' => 'Create 5 builds',
                'icon' => '🏎️',
                'category' => 'builds',
                'points' => 50,
                'requirement_type' => 'count',
                'requirement_value' => 5,
            ],
            [
                'name' => 'Garage King',
                'slug' => 'ten-builds',
                'description' => 'Create 10 builds',
                'icon' => '👑',
                'category' => 'builds',
                'points' => 100,
                'requirement_type' => 'count',
                'requirement_value' => 10,
            ],

            // HP Achievements
            [
                'name' => 'Power Hungry',
                'slug' => 'hp-300',
                'description' => 'Build a car with 300+ HP',
                'icon' => '⚡',
                'category' => 'hp',
                'points' => 20,
                'requirement_type' => 'threshold',
                'requirement_value' => 300,
            ],
            [
                'name' => 'Beast Mode',
                'slug' => 'hp-500',
                'description' => 'Build a car with 500+ HP',
                'icon' => '🔥',
                'category' => 'hp',
                'points' => 50,
                'requirement_type' => 'threshold',
                'requirement_value' => 500,
            ],
            [
                'name' => 'Hypercar Territory',
                'slug' => 'hp-700',
                'description' => 'Build a car with 700+ HP',
                'icon' => '💥',
                'category' => 'hp',
                'points' => 100,
                'requirement_type' => 'threshold',
                'requirement_value' => 700,
            ],

            // Parts Achievements
            [
                'name' => 'Tuner',
                'slug' => 'parts-5',
                'description' => 'Add 5 parts to a single build',
                'icon' => '🔧',
                'category' => 'parts',
                'points' => 15,
                'requirement_type' => 'count',
                'requirement_value' => 5,
            ],
            [
                'name' => 'Full Build',
                'slug' => 'parts-10',
                'description' => 'Add 10 parts to a single build',
                'icon' => '🛠️',
                'category' => 'parts',
                'points' => 30,
                'requirement_type' => 'count',
                'requirement_value' => 10,
            ],

            // Special Achievements
            [
                'name' => 'BMW Fanboy',
                'slug' => 'bmw-specialist',
                'description' => 'Create 3 BMW builds',
                'icon' => '🇩🇪',
                'category' => 'special',
                'points' => 25,
                'requirement_type' => 'special',
                'requirement_value' => null,
            ],
            [
                'name' => 'Budget Warrior',
                'slug' => 'budget-build',
                'description' => 'Create a build under $1000',
                'icon' => '💰',
                'category' => 'special',
                'points' => 20,
                'requirement_type' => 'special',
                'requirement_value' => null,
            ],
        ];

        foreach ($achievements as $achievement) {
            Achievement::create($achievement);
        }
    }
}
