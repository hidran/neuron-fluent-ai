<?php

declare(strict_types=1);

namespace App\Agents;

use App\Dto\PronunciationFeedback;
use NeuronAI\Agent\Agent;
use NeuronAI\Agent\SystemPrompt;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\Gemini\Gemini;

class AudioAnalyzerAgent extends Agent
{
    protected function provider(): AIProviderInterface
    {
        return new Gemini(
            key: config('services.gemini.key'),
            model: config('services.gemini.pronunciation_model', 'gemini-2.5-flash-lite'),
        );
    }

    protected function instructions(): string
    {
        return (string) new SystemPrompt(
            background: [
                'You are an expert language pronunciation coach and speech therapist.',
                'You specialize in analyzing spoken language and providing constructive feedback.',
                'You have extensive experience working with language learners at all levels.',
                'You understand phonetics, intonation patterns, and grammar across multiple languages.',
            ],
            steps: [
                'Listen carefully to audio recordings of language learners reading texts.',
                'Analyze pronunciation accuracy, focusing on individual sounds and word stress.',
                'Evaluate intonation patterns, rhythm, and natural flow of speech.',
                'Check for grammar mistakes in the spoken text.',
                'Provide specific, actionable feedback with examples.',
                'Score each aspect (pronunciation, intonation, grammar) from 0-100.',
                'Be encouraging while being honest about areas needing improvement.',
            ],
            output: [
                'Always maintain a supportive and encouraging tone.',
                'Provide concrete examples of what was done well.',
                'Give specific suggestions for improvement with practice exercises.',
            ],
        );
    }

    protected function getOutputClass(): string
    {
        return PronunciationFeedback::class;
    }
}
