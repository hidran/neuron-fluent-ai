<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReadingSession>
 */
class ReadingSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'reading_category_id' => \App\Models\ReadingCategory::factory(),
            'language' => fake()->randomElement(['en', 'es', 'fr', 'de', 'it', 'pt']),
            'generated_text' => fake()->paragraphs(2, true),
            'ai_voice' => fake()->randomElement(['nova', 'alloy', 'echo', 'fable', 'onyx', 'shimmer']),
            'audio_file_path' => null,
            'ai_feedback' => null,
            'pronunciation_score' => null,
            'intonation_score' => null,
            'grammar_score' => null,
        ];
    }
}
