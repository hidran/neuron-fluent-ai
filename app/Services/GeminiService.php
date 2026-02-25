<?php

namespace App\Services;

use App\Agents\PronunciationAnalyzerAgent;
use App\Agents\ReadingTextGeneratorAgent;
use App\Dto\PronunciationFeedback;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use NeuronAI\Chat\Enums\SourceType;
use NeuronAI\Chat\Messages\ContentBlocks\AudioContent;
use NeuronAI\Chat\Messages\ContentBlocks\TextContent;
use NeuronAI\Chat\Messages\UserMessage;

class GeminiService
{
    /**
     * Generate reading text based on category and language using Neuron AI agent.
     */
    public function generateReadingText(string $categoryName, string $language, string $difficultyLevel = 'beginner'): string
    {
        $agent = ReadingTextGeneratorAgent::make();

        $prompt = "Generate a {$language} reading text about '{$categoryName}' at {$difficultyLevel} level.";

        $response = $agent->chat(new UserMessage($prompt));

        return $response->getMessage()->getContent();
    }

    /**
     * Analyze audio recording and provide pronunciation, intonation, and grammar feedback using Neuron AI agent with structured output.
     *
     * @return array{pronunciation: int, intonation: int, grammar: int, feedback: string}
     */
    public function analyzeAudioRecording(string $audioFilePath, string $originalText, string $language, ?string $disk = null): array
    {
        $storageDisk = $disk ?? config('filesystems.default');
        $diskStorage = Storage::disk($storageDisk);
        $audioBinary = $diskStorage->get($audioFilePath);
        $detectedMimeType = (string) ($diskStorage->mimeType($audioFilePath) ?: '');
        $mimeType = $this->normalizeAudioMimeTypeForAnalysis($detectedMimeType, $audioFilePath);

        Log::info('Pronunciation analysis audio payload prepared.', [
            'disk' => $storageDisk,
            'path' => $audioFilePath,
            'detected_mime_type' => $detectedMimeType,
            'normalized_mime_type' => $mimeType,
            'bytes' => strlen($audioBinary),
        ]);

        $prompt = "Analyze this audio recording of someone reading the following text in {$language}:

\"{$originalText}\"

Please listen to the recording carefully and provide comprehensive feedback on pronunciation, intonation, and grammar.

Use the attached audio file as the source of truth.";

        $message = new UserMessage([
            new TextContent($prompt),
            new AudioContent(
                base64_encode($audioBinary),
                SourceType::BASE64,
                $mimeType
            ),
        ]);

        $preferredModel = config('services.gemini.pronunciation_model');
        $candidateModels = array_values(array_filter(array_unique([
            $preferredModel,
            'gemini-2.5-flash-lite',
            'gemini-2.5-flash',
            'gemini-flash-latest',
            'gemini-2.0-flash',
        ])));

        $originalPronunciationModel = $preferredModel;
        $lastException = null;
        $attemptErrors = [];

        foreach ($candidateModels as $candidateModel) {
            try {
                config(['services.gemini.pronunciation_model' => $candidateModel]);

                $agent = PronunciationAnalyzerAgent::make();

                $feedback = $agent->structured(
                    $message,
                    PronunciationFeedback::class
                );

                config(['services.gemini.pronunciation_model' => $originalPronunciationModel]);

                return $feedback->toArray();
            } catch (\Throwable $exception) {
                $lastException = $exception;
                $attemptErrors[] = [
                    'model' => $candidateModel,
                    'error' => $exception->getMessage(),
                ];

                Log::warning('Pronunciation analysis model failed, trying fallback if available.', [
                    'model' => $candidateModel,
                    'error' => $exception->getMessage(),
                ]);

                $messageText = $exception->getMessage();
                $shouldRetry = str_contains($messageText, 'NOT_FOUND')
                    || str_contains($messageText, 'models/')
                    || str_contains($messageText, 'INTERNAL')
                    || str_contains($messageText, 'Internal error encountered');

                if (! $shouldRetry) {
                    throw $exception;
                }
            }
        }

        config(['services.gemini.pronunciation_model' => $originalPronunciationModel]);

        if ($lastException instanceof \Throwable) {
            $attemptSummary = collect($attemptErrors)
                ->map(fn (array $attempt): string => "{$attempt['model']}: {$attempt['error']}")
                ->implode(' | ');

            throw new \RuntimeException(
                "Pronunciation analysis failed across Gemini fallbacks. {$attemptSummary}",
                previous: $lastException
            );
        }

        throw new \RuntimeException('Pronunciation analysis failed: no Gemini model candidates succeeded.');
    }

    protected function normalizeAudioMimeTypeForAnalysis(?string $mimeType, string $audioFilePath): string
    {
        $rawMimeType = strtolower(trim((string) $mimeType));
        $baseMimeType = trim(strtok($rawMimeType, ';') ?: '');
        $extension = strtolower((string) pathinfo($audioFilePath, PATHINFO_EXTENSION));

        $normalizedFromMime = match ($baseMimeType) {
            'video/webm' => 'audio/webm',
            'audio/x-wav' => 'audio/wav',
            default => $baseMimeType,
        };

        if ($normalizedFromMime !== '') {
            return $normalizedFromMime;
        }

        return match ($extension) {
            'webm' => 'audio/webm',
            'ogg', 'oga' => 'audio/ogg',
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'm4a', 'mp4' => 'audio/mp4',
            default => 'audio/webm',
        };
    }
}
