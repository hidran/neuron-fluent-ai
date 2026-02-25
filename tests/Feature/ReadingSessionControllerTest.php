<?php

namespace Tests\Feature;

use App\Models\ReadingRecording;
use App\Models\ReadingSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingSessionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_paginated_reading_sessions_for_a_user(): void
    {
        $user = User::factory()->create();
        ReadingSession::factory()->count(16)->for($user)->create();

        $response = $this->actingAs($user)->getJson('/api/reading-sessions');

        $response->assertSuccessful();
        $response->assertJsonCount(15, 'data');
        $response->assertJsonStructure(['data', 'links']);
    }

    public function test_can_get_a_single_reading_session_with_its_recording(): void
    {
        $user = User::factory()->create();
        $session = ReadingSession::factory()->for($user)->create();
        $recording = ReadingRecording::factory()->for($session)->create();

        $response = $this->actingAs($user)->getJson("/api/reading-sessions/{$session->id}");

        $response->assertSuccessful();
        $response->assertJson([
            'id' => $session->id,
            'recordings' => [
                [
                    'id' => $recording->id,
                ]
            ],
        ]);
    }
}
