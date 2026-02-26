<?php

declare(strict_types=1);

namespace App\Services\ReadingPractice;

use App\Contracts\ReadingPractice\AudioAnalyzerInterface;
use App\Contracts\ReadingPractice\TextGeneratorInterface;
use App\Models\ReadingRecording;
use App\Models\ReadingSession;
use App\Services\AudioMimeTypeNormalizer;
use App\Services\OpenAiTextToSpeechService;
use App\Services\ReadingPractice\Data\SessionSaveData;
use Illuminate\Support\Facades\DB;

/**
 * Facade-style Service for Reading Practice Operations.
 * Respects SOLID by delegating specialized tasks to injected interfaces.
 */
class ReadingPracticeService
{
    public function __construct(
        protected TextGeneratorInterface $textGenerator,
        protected AudioAnalyzerInterface $audioAnalyzer,
        protected AudioMimeTypeNormalizer $mimeTypeNormalizer,
        protected OpenAiTextToSpeechService $ttsService,
    ) {}

    public function generatePracticeText(string $topic, string $language, string $difficulty): string
    {
        return $this->textGenerator->generate($topic, $language, $difficulty);
    }

    /**
     * @return array{audio_url: string, audio_file_path: string, storage_disk: string, mime_type: string}
     */
    public function synthesizeSpeech(string $text, string $voice): array
    {
        return $this->ttsService->synthesizeReading($text, $voice);
    }

    /**
     * @return array{pronunciation: int, intonation: int, grammar: int, feedback: string}
     */
    public function analyzeCurrentRecording(string $audioPath, string $text, string $language, string $disk = 'public'): array
    {
        return $this->audioAnalyzer->analyze($audioPath, $text, $language, $disk)->toArray();
    }

    public function persistCompleteSession(SessionSaveData $data): void
    {
        DB::transaction(function () use ($data) {
            $path = $data->audio->store('recordings', 'public');

            $session = ReadingSession::create([
                'user_id' => $data->userId,
                'reading_category_id' => $data->category->id,
                'language' => $data->language,
                'generated_text' => $data->text,
                'ai_audio_path' => $data->aiAudioUrl ? str_replace('/storage/', '', $data->aiAudioUrl) : null,
                'audio_file_path' => $path,
                'pronunciation_score' => $data->feedback['pronunciation'] ?? null,
                'intonation_score' => $data->feedback['intonation'] ?? null,
                'grammar_score' => $data->feedback['grammar'] ?? null,
                'ai_feedback' => $data->feedback['feedback'] ?? null,
            ]);

            ReadingRecording::create([
                'reading_session_id' => $session->id,
                'audio_file_path' => $path,
                'storage_disk' => 'public',
                'mime_type' => $this->mimeTypeNormalizer->normalize(
                    $data->audio->getMimeType(),
                    $data->audio->getClientOriginalName()
                ),
                'file_size' => $data->audio->getSize(),
                'ai_feedback' => json_encode($data->feedback),
                'pronunciation_score' => $data->feedback['pronunciation'] ?? null,
                'intonation_score' => $data->feedback['intonation'] ?? null,
                'grammar_score' => $data->feedback['grammar'] ?? null,
                'analyzed_at' => now(),
            ]);
        });
    }
}
