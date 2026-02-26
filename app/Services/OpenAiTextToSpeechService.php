<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Providers\OpenAI\Audio\OpenAITextToSpeech;

class OpenAiTextToSpeechService
{
    /**
     * @return array{audio_url: string, audio_file_path: string, storage_disk: string, mime_type: string}
     */
    public function synthesizeReading(string $text, string $voice, ?int $sessionId = null, string $storageDisk = 'public'): array
    {
        $openAiKey = (string) config('services.openai.key');

        if ($openAiKey === '') {
            throw new \RuntimeException('OpenAI API key is not configured.');
        }

        $ttsModel = (string) config('services.openai.tts_model', 'gpt-4o-mini-tts');
        $relativePath = $this->buildStoragePath($text, $voice, $ttsModel, $sessionId);
        $disk = Storage::disk($storageDisk);

        if (! $disk->exists($relativePath)) {
            $provider = new OpenAITextToSpeech(
                key: $openAiKey,
                model: $ttsModel,
                voice: $voice
            );

            $message = $provider->chat(new UserMessage($text));
            $audioContent = $message->getAudio();

            if (! $audioContent) {
                throw new \RuntimeException('Text-to-speech provider did not return audio content.');
            }

            $audioBinary = base64_decode($audioContent->getContent(), true);

            if ($audioBinary === false) {
                throw new \RuntimeException('Could not decode text-to-speech audio response.');
            }

            $disk->put($relativePath, $audioBinary);
        }

        return [
            'audio_url' => $this->playbackUrl($relativePath, $storageDisk),
            'audio_file_path' => $relativePath,
            'storage_disk' => $storageDisk,
            'mime_type' => 'audio/mpeg',
        ];
    }

    protected function buildStoragePath(string $text, string $voice, string $model, ?int $sessionId): string
    {
        $voiceSegment = Str::of($voice)
            ->lower()
            ->replaceMatches('/[^a-z0-9_-]+/', '-')
            ->trim('-')
            ->value();

        $hash = sha1($model.'|'.$voice.'|'.$text);
        $sessionSegment = $sessionId ? "sessions/{$sessionId}" : 'adhoc';

        return "reading-tts/{$sessionSegment}/{$voiceSegment}-{$hash}.mp3";
    }

    protected function playbackUrl(string $path, string $storageDisk): string
    {
        if ($storageDisk === 'public') {
            return '/storage/'.ltrim($path, '/');
        }

        return Storage::disk($storageDisk)->url($path);
    }
}
