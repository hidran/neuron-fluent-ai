<?php

declare(strict_types=1);

namespace App\Services\ReadingPractice;

use App\Models\ReadingRecording;
use App\Models\ReadingSession;
use App\Services\AudioMimeTypeNormalizer;
use App\Services\GeminiService;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ReadingRecordingService
{
    public function __construct(
        protected GeminiService $geminiService,
        protected AudioMimeTypeNormalizer $audioMimeTypeNormalizer,
    ) {}

    public function storeUploadedRecording(
        ReadingSession $session,
        TemporaryUploadedFile $uploadedRecording,
        string $storageDisk = 'public',
    ): ReadingRecording {
        $storedPath = $uploadedRecording->store('reading-recordings', $storageDisk);

        return $session->recordings()->create([
            'storage_disk' => $storageDisk,
            'audio_file_path' => $storedPath,
            'mime_type' => $this->audioMimeTypeNormalizer->normalize(
                $uploadedRecording->getMimeType(),
                $uploadedRecording->getClientOriginalName()
            ),
            'file_size' => $uploadedRecording->getSize(),
        ]);
    }

    /**
     * @return array{pronunciation: int, intonation: int, grammar: int, feedback: string}
     */
    public function analyzeAndPersistFeedback(
        ReadingSession $session,
        ReadingRecording $recording,
        string $originalText,
        string $language,
    ): array {
        $feedback = $this->geminiService->analyzeAudioRecording(
            $recording->audio_file_path,
            $originalText,
            $language,
            $recording->storage_disk
        );

        $recording->update([
            'ai_feedback' => $feedback['feedback'],
            'pronunciation_score' => $feedback['pronunciation'],
            'intonation_score' => $feedback['intonation'],
            'grammar_score' => $feedback['grammar'],
            'analyzed_at' => now(),
        ]);

        $session->update([
            'audio_file_path' => $recording->audio_file_path,
            'ai_feedback' => $feedback['feedback'],
            'pronunciation_score' => $feedback['pronunciation'],
            'intonation_score' => $feedback['intonation'],
            'grammar_score' => $feedback['grammar'],
        ]);

        return $feedback;
    }
}
