<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReadingRecording>
 */
class ReadingRecordingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'storage_disk' => 'public',
            'audio_file_path' => $this->faker->filePath(),
            'mime_type' => 'audio/mpeg',
            'file_size' => $this->faker->numberBetween(1000, 5000),
            'ai_feedback' => json_encode(['pronunciation' => $this->faker->numberBetween(70, 100)]),
            'pronunciation_score' => $this->faker->numberBetween(70, 100),
            'intonation_score' => $this->faker->numberBetween(70, 100),
            'grammar_score' => $this->faker->numberBetween(70, 100),
            'analyzed_at' => now(),
        ];
    }
}
