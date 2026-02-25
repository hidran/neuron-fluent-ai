<?php

declare(strict_types=1);

namespace App\Agents;

use NeuronAI\Agent\Agent;
use NeuronAI\Agent\SystemPrompt;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\Gemini\Gemini;

class ReadingTextGeneratorAgent extends Agent
{
    protected function provider(): AIProviderInterface
    {
        return new Gemini(
            key: config('services.gemini.key'),
            model: config('services.gemini.model'),
        );
    }

    protected function instructions(): string
    {
        return (string) new SystemPrompt(
            background: [
                'You are an expert language teacher and content creator.',
                'Your specialty is generating engaging, natural, and educational reading texts for language learners.',
                'You understand different proficiency levels (beginner, intermediate, advanced).',
                'You create contextually appropriate content based on specific topics and categories.',
            ],
            steps: [
                'Generate reading texts that are appropriate for the specified language and difficulty level.',
                'Make the texts engaging, natural, and suitable for language practice.',
                'Adjust vocabulary, sentence complexity, and content depth based on the difficulty level.',
                'For beginners: use simple vocabulary and short sentences.',
                'For intermediate: use moderate vocabulary with some complex sentences and idiomatic expressions.',
                'For advanced: use sophisticated vocabulary, complex structures, and nuanced expressions.',
            ],
            output: [
                'Keep texts between 150-200 words.',
                'Ensure content is culturally appropriate and educational.',
                'Use natural, conversational language that natives would actually use.',
                'Do not include any markdown formatting, just plain text.',
            ],
        );
    }
}
