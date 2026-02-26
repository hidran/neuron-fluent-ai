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
    public function __construct(
        protected AudioMimeTypeNormalizer $audioMimeTypeNormalizer,
    ) {}

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
        $mimeType = $this->audioMimeTypeNormalizer->normalize($detectedMimeType, $audioFilePath);

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

        $configuredModel = (string) config('services.gemini.pronunciation_model');

        try {
            $agent = PronunciationAnalyzerAgent::make();

            $feedback = $agent->structured(
                $message,
                PronunciationFeedback::class
            );

            return $feedback->toArray();
        } catch (\Throwable $exception) {
            Log::warning('Pronunciation analysis failed using configured Gemini model.', [
                'model' => $configuredModel,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
