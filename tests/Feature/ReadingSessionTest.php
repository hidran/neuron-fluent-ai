<?php

namespace Tests\Feature;

use App\Models\ReadingCategory;
use App\Models\ReadingSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_reading_session(): void
    {
        $user = User::factory()->create();
        $category = ReadingCategory::factory()->create();

        $session = ReadingSession::create([
            'user_id' => $user->id,
            'reading_category_id' => $category->id,
            'language' => 'en',
            'generated_text' => 'Test text for reading practice.',
            'ai_voice' => 'nova',
        ]);

        $this->assertDatabaseHas('reading_sessions', [
            'user_id' => $user->id,
            'language' => 'en',
        ]);
    }

    public function test_reading_session_belongs_to_user(): void
    {
        $session = ReadingSession::factory()->create();

        $this->assertInstanceOf(User::class, $session->user);
    }

    public function test_reading_session_belongs_to_category(): void
    {
        $session = ReadingSession::factory()->create();

        $this->assertInstanceOf(ReadingCategory::class, $session->readingCategory);
    }

    public function test_can_store_ai_feedback_as_array(): void
    {
        $session = ReadingSession::factory()->create([
            'ai_feedback' => ['pronunciation' => 'good', 'intonation' => 'needs work'],
        ]);

        $feedback = $session->ai_feedback;

        $this->assertIsArray($feedback);
        $this->assertEquals('good', $feedback['pronunciation']);
        $this->assertEquals('needs work', $feedback['intonation']);
    }

    public function test_can_store_pronunciation_scores(): void
    {
        $session = ReadingSession::factory()->create([
            'pronunciation_score' => 85,
            'intonation_score' => 78,
            'grammar_score' => 92,
        ]);

        $this->assertEquals(85, $session->pronunciation_score);
        $this->assertEquals(78, $session->intonation_score);
        $this->assertEquals(92, $session->grammar_score);
    }

    public function test_can_filter_sessions_by_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        ReadingSession::factory()->count(3)->create(['user_id' => $user1->id]);
        ReadingSession::factory()->count(2)->create(['user_id' => $user2->id]);

        $user1Sessions = ReadingSession::where('user_id', $user1->id)->get();

        $this->assertCount(3, $user1Sessions);
    }

    public function test_can_filter_sessions_by_language(): void
    {
        ReadingSession::factory()->count(2)->create(['language' => 'en']);
        ReadingSession::factory()->count(3)->create(['language' => 'es']);

        $englishSessions = ReadingSession::where('language', 'en')->get();

        $this->assertCount(2, $englishSessions);
    }
}
