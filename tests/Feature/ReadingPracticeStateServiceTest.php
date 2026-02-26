<?php

namespace Tests\Feature;

use App\Models\ReadingCategory;
use App\Models\ReadingSession;
use App\Models\User;
use App\Services\ReadingPractice\ReadingPracticeStateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPracticeStateServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_find_owned_session_scopes_by_user(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $session = ReadingSession::factory()->create([
            'user_id' => $owner->id,
        ]);

        $service = app(ReadingPracticeStateService::class);

        $this->assertNotNull($service->findOwnedSession($session->id, $owner->id));
        $this->assertNull($service->findOwnedSession($session->id, $otherUser->id));
    }

    public function test_latest_feedback_payload_uses_latest_analyzed_recording(): void
    {
        $session = ReadingSession::factory()->create();

        $session->recordings()->create([
            'storage_disk' => 'public',
            'audio_file_path' => 'reading-recordings/older.webm',
            'mime_type' => 'audio/webm',
            'file_size' => 1200,
            'ai_feedback' => 'Older feedback',
            'pronunciation_score' => 70,
            'intonation_score' => 71,
            'grammar_score' => 72,
            'analyzed_at' => now()->subMinutes(10),
        ]);

        $session->recordings()->create([
            'storage_disk' => 'public',
            'audio_file_path' => 'reading-recordings/not-analyzed.webm',
            'mime_type' => 'audio/webm',
            'file_size' => 1400,
            'ai_feedback' => null,
            'pronunciation_score' => null,
            'intonation_score' => null,
            'grammar_score' => null,
            'analyzed_at' => null,
        ]);

        $latestAnalyzed = $session->recordings()->create([
            'storage_disk' => 'public',
            'audio_file_path' => 'reading-recordings/latest.webm',
            'mime_type' => 'audio/webm',
            'file_size' => 1600,
            'ai_feedback' => 'Latest feedback',
            'pronunciation_score' => 91,
            'intonation_score' => 92,
            'grammar_score' => 93,
            'analyzed_at' => now(),
        ]);

        $session->load('recordings');

        $payload = app(ReadingPracticeStateService::class)->latestFeedbackPayload($session);

        $this->assertSame([
            'pronunciation' => $latestAnalyzed->pronunciation_score,
            'intonation' => $latestAnalyzed->intonation_score,
            'grammar' => $latestAnalyzed->grammar_score,
            'feedback' => $latestAnalyzed->ai_feedback,
        ], $payload);
    }

    public function test_saved_recordings_payload_is_sorted_latest_first_and_uses_playback_url(): void
    {
        $session = ReadingSession::factory()->create();

        $older = $session->recordings()->create([
            'storage_disk' => 'public',
            'audio_file_path' => 'reading-recordings/older.webm',
            'mime_type' => 'audio/webm',
            'file_size' => 1000,
            'ai_feedback' => 'Older',
            'pronunciation_score' => 80,
            'intonation_score' => 81,
            'grammar_score' => 82,
            'analyzed_at' => now()->subMinute(),
            'created_at' => now()->subMinute(),
            'updated_at' => now()->subMinute(),
        ]);

        $latest = $session->recordings()->create([
            'storage_disk' => 'public',
            'audio_file_path' => 'reading-recordings/latest.webm',
            'mime_type' => 'audio/webm',
            'file_size' => 2000,
            'ai_feedback' => 'Latest',
            'pronunciation_score' => 90,
            'intonation_score' => 91,
            'grammar_score' => 92,
            'analyzed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $payload = app(ReadingPracticeStateService::class)->savedRecordingsPayload($session);

        $this->assertCount(2, $payload);
        $this->assertSame($latest->id, $payload[0]['id']);
        $this->assertSame($older->id, $payload[1]['id']);
        $this->assertSame('/storage/reading-recordings/latest.webm', $payload[0]['audio_url']);
    }

    public function test_create_session_builds_expected_record(): void
    {
        $user = User::factory()->create();
        $category = ReadingCategory::factory()->create();

        $session = app(ReadingPracticeStateService::class)->createSession(
            $user->id,
            $category,
            'en',
            'Sample generated text',
            'nova',
        );

        $this->assertDatabaseHas('reading_sessions', [
            'id' => $session->id,
            'user_id' => $user->id,
            'reading_category_id' => $category->id,
            'language' => 'en',
            'generated_text' => 'Sample generated text',
            'ai_voice' => 'nova',
        ]);
    }
}
