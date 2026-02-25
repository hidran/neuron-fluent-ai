<?php

namespace Tests\Feature;

use App\Models\ReadingCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReadingPracticeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_save_reading_practice_session(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $category = ReadingCategory::factory()->create();
        $audio = UploadedFile::fake()->create('recording.mp3', 100);

        $feedback = [
            'pronunciation' => 85,
            'intonation' => 90,
            'grammar' => 95,
            'feedback' => 'Great job!',
        ];

        $response = $this->post('/api/reading-practice/save', [
            'user_id' => $user->id,
            'reading_category_id' => $category->id,
            'language' => 'en-US',
            'text' => 'This is a test text.',
            'audio' => $audio,
            'feedback' => json_encode($feedback),
        ]);

        $response->assertSuccessful();
        $response->assertJson(['message' => 'Reading practice session saved successfully.']);

        $this->assertDatabaseHas('reading_sessions', [
            'user_id' => $user->id,
            'reading_category_id' => $category->id,
            'language' => 'en-US',
            'generated_text' => 'This is a test text.',
            'pronunciation_score' => 85,
            'intonation_score' => 90,
            'grammar_score' => 95,
            'ai_feedback' => 'Great job!',
        ]);

        $this->assertDatabaseHas('reading_recordings', [
            'pronunciation_score' => 85,
            'intonation_score' => 90,
            'grammar_score' => 95,
        ]);

        $recording = \App\Models\ReadingRecording::first();
        Storage::disk('public')->assertExists($recording->audio_file_path);
    }
}
