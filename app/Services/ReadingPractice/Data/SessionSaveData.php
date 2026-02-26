<?php

declare(strict_types=1);

namespace App\Services\ReadingPractice\Data;

use App\Models\ReadingCategory;
use Illuminate\Http\UploadedFile;

/**
 * PHP 8.4 Readonly Class for Session Storage Data
 */
final readonly class SessionSaveData
{
    public function __construct(
        public int $userId,
        public ReadingCategory $category,
        public string $language,
        public string $text,
        public UploadedFile $audio,
        public array $feedback,
        public ?string $aiAudioUrl = null,
    ) {}
}
