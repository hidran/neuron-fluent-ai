<?php

declare(strict_types=1);

namespace App\Services;

use App\Agents\PronunciationAnalyzerAgent;
use App\Agents\ReadingTextGeneratorAgent;
use App\Contracts\ReadingPractice\AudioAnalyzerInterface;
use App\Contracts\ReadingPractice\TextGeneratorInterface;
use App\Dto\PronunciationFeedback;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use NeuronAI\Chat\Enums\SourceType;
use NeuronAI\Chat\Messages\ContentBlocks\AudioContent;
use NeuronAI\Chat\Messages\ContentBlocks\TextContent;
use NeuronAI\Chat\Messages\UserMessage;

class GeminiService implements AudioAnalyzerInterface, TextGeneratorInterface
{
    public function __construct(
        protected AudioMimeTypeNormalizer $audioMimeTypeNormalizer,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function generate(string $topic, string $language, string $difficulty): string
    {
        $agent = ReadingTextGeneratorAgent::make();

        $prompt = "Generate a $language reading text about '$topic' at $difficulty level.";

        return $agent->chat(new UserMessage($prompt))
            ->getMessage()
            ->getContent();
    }

    /**
     * {}
     */
    public function analyze(string $audioFilePath, string $originalText, string $language, ?string $disk = null): PronunciationFeedback
    {
        $storageDisk = $disk ?? config('filesystems.default');
        $diskStorage = Storage::disk($storageDisk);
        $audioBinary = $diskStorage->get($audioFilePath);
        $detectedMimeType = (string) ($diskStorage->mimeType($audioFilePath) ?? '');
        $mimeType = $this->audioMimeTypeNormalizer->normalize($detectedMimeType, $audioFilePath);

        Log::info('Pronunciation analysis prepared.', [
            'disk' => $storageDisk,
            'path' => $audioFilePath,
            'mime' => $mimeType,
        ]);

        $message = new UserMessage([
            new TextContent("Analyze this recording of someone reading \"$originalText\" in $language."),
            new AudioContent(
                base64_encode($audioBinary),
                SourceType::BASE64,
                $mimeType
            ),
        ]);

        return PronunciationAnalyzerAgent::make()
            ->structured($message, PronunciationFeedback::class);
    }
}
