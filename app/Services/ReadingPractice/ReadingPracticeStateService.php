<?php

declare(strict_types=1);

namespace App\Services\ReadingPractice;

use App\Models\ReadingCategory;
use App\Models\ReadingRecording;
use App\Models\ReadingSession;

class ReadingPracticeStateService
{
    public function createSession(
        int $userId,
        ReadingCategory $category,
        string $language,
        string $generatedText,
        ?string $voice = null,
    ): ReadingSession {
        return ReadingSession::create([
            'user_id' => $userId,
            'reading_category_id' => $category->id,
            'language' => $language,
            'generated_text' => $generatedText,
            'ai_voice' => $voice,
        ]);
    }

    public function findOwnedSession(int $sessionId, int $userId, bool $withRecordings = false): ?ReadingSession
    {
        $query = ReadingSession::query()
            ->whereKey($sessionId)
            ->where('user_id', $userId);

        if ($withRecordings) {
            $query->with('recordings');
        }

        return $query->first();
    }

    /**
     * @return array{pronunciation: int|null, intonation: int|null, grammar: int|null, feedback: string}|null
     */
    public function latestFeedbackPayload(ReadingSession $session): ?array
    {
        $recording = $this->latestAnalyzedRecording($session);

        if (! $recording) {
            return null;
        }

        return [
            'pronunciation' => $recording->pronunciation_score,
            'intonation' => $recording->intonation_score,
            'grammar' => $recording->grammar_score,
            'feedback' => (string) $recording->ai_feedback,
        ];
    }

    /**
     * @return list<array{
     *     id: int,
     *     audio_url: string,
     *     created_at: string|null,
     *     mime_type: string|null,
     *     file_size: int|null,
     *     pronunciation_score: int|null,
     *     intonation_score: int|null,
     *     grammar_score: int|null,
     *     ai_feedback: string|null,
     *     analyzed_at: string|null
     * }>
     */
    public function savedRecordingsPayload(ReadingSession $session): array
    {
        return $session->recordings()
            ->latest('created_at')
            ->latest('id')
            ->get()
            ->map(fn (ReadingRecording $recording): array => $this->recordingPayload($recording))
            ->all();
    }

    protected function latestAnalyzedRecording(ReadingSession $session): ?ReadingRecording
    {
        if ($session->relationLoaded('recordings')) {
            /** @var \Illuminate\Database\Eloquent\Collection<int, ReadingRecording> $recordings */
            $recordings = $session->recordings;

            return $recordings
                ->whereNotNull('ai_feedback')
                ->sortByDesc(fn (ReadingRecording $recording): string => (string) $recording->analyzed_at)
                ->first();
        }

        return $session->recordings()
            ->whereNotNull('ai_feedback')
            ->latest('analyzed_at')
            ->latest('id')
            ->first();
    }

    /**
     * @return array{
     *     id: int,
     *     audio_url: string,
     *     created_at: string|null,
     *     mime_type: string|null,
     *     file_size: int|null,
     *     pronunciation_score: int|null,
     *     intonation_score: int|null,
     *     grammar_score: int|null,
     *     ai_feedback: string|null,
     *     analyzed_at: string|null
     * }
     */
    protected function recordingPayload(ReadingRecording $recording): array
    {
        return [
            'id' => $recording->id,
            'audio_url' => $recording->playbackUrl(),
            'created_at' => $recording->created_at?->format('Y-m-d H:i:s'),
            'mime_type' => $recording->mime_type,
            'file_size' => $recording->file_size,
            'pronunciation_score' => $recording->pronunciation_score,
            'intonation_score' => $recording->intonation_score,
            'grammar_score' => $recording->grammar_score,
            'ai_feedback' => $recording->ai_feedback,
            'analyzed_at' => $recording->analyzed_at?->format('Y-m-d H:i:s'),
        ];
    }
}
