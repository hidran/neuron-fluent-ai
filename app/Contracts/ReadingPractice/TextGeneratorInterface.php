<?php

declare(strict_types=1);

namespace App\Contracts\ReadingPractice;

interface TextGeneratorInterface
{
    public function generate(string $topic, string $language, string $difficulty): string;
}
