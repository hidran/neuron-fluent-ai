<?php

declare(strict_types=1);

namespace App\Dto;

use NeuronAI\StructuredOutput\SchemaProperty;

class PronunciationFeedback
{
    #[SchemaProperty(
        description: 'Pronunciation accuracy score from 0 to 100, where 0 is poor and 100 is perfect native-like pronunciation.',
        required: true
    )]
    public int $pronunciationScore;

    #[SchemaProperty(
        description: 'Intonation and rhythm score from 0 to 100, evaluating natural speech flow, stress patterns, and pitch variations.',
        required: true
    )]
    public int $intonationScore;

    #[SchemaProperty(
        description: 'Grammar accuracy score from 0 to 100, checking correct word forms, sentence structure, and usage.',
        required: true
    )]
    public int $grammarScore;

    #[SchemaProperty(
        description: 'Detailed feedback explaining specific pronunciation errors, intonation issues, and grammar mistakes with examples.',
        required: true
    )]
    public string $detailedFeedback;

    #[SchemaProperty(
        description: 'Specific, actionable suggestions for improvement including practice exercises and tips.',
        required: true
    )]
    public string $suggestions;

    /**
     * Convert to array format expected by the application.
     *
     * @return array{pronunciation: int, intonation: int, grammar: int, feedback: string}
     */
    public function toArray(): array
    {
        return [
            'pronunciation' => $this->pronunciationScore,
            'intonation' => $this->intonationScore,
            'grammar' => $this->grammarScore,
            'feedback' => $this->detailedFeedback."\n\n".$this->suggestions,
        ];
    }
}
