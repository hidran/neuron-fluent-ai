<?php

declare(strict_types=1);

namespace App\Contracts\ReadingPractice;

use App\Dto\PronunciationFeedback;

interface AudioAnalyzerInterface
{
    public function analyze(string $audioPath, string $text, string $language, ?string $disk = null): PronunciationFeedback;
}
