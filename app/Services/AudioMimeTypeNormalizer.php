<?php

declare(strict_types=1);

namespace App\Services;

class AudioMimeTypeNormalizer
{
    public function normalize(?string $mimeType, ?string $filePathOrName = null, string $default = 'audio/webm'): string
    {
        $rawMimeType = strtolower(trim((string) $mimeType));
        $baseMimeType = trim(strtok($rawMimeType, ';') ?: '');

        $normalizedFromMime = match ($baseMimeType) {
            'video/webm' => 'audio/webm',
            'audio/x-wav' => 'audio/wav',
            default => $baseMimeType,
        };

        if ($normalizedFromMime !== '') {
            return $normalizedFromMime;
        }

        $extension = strtolower((string) pathinfo((string) $filePathOrName, PATHINFO_EXTENSION));

        return match ($extension) {
            'webm' => 'audio/webm',
            'ogg', 'oga' => 'audio/ogg',
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'm4a', 'mp4' => 'audio/mp4',
            default => $default,
        };
    }
}
