<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReadingCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Daily Conversations',
                'description' => 'Practice everyday conversation topics',
                'difficulty_level' => 'beginner',
                'sort_order' => 1,
            ],
            [
                'name' => 'Business & Work',
                'description' => 'Professional communication and workplace scenarios',
                'difficulty_level' => 'intermediate',
                'sort_order' => 2,
            ],
            [
                'name' => 'Travel & Tourism',
                'description' => 'Travel-related conversations and situations',
                'difficulty_level' => 'beginner',
                'sort_order' => 3,
            ],
            [
                'name' => 'Academic Topics',
                'description' => 'Educational content and academic discussions',
                'difficulty_level' => 'advanced',
                'sort_order' => 4,
            ],
            [
                'name' => 'News & Current Events',
                'description' => 'Reading news articles and discussing current affairs',
                'difficulty_level' => 'intermediate',
                'sort_order' => 5,
            ],
        ];

        foreach ($categories as $category) {
            \App\Models\ReadingCategory::create($category);
        }
    }
}
