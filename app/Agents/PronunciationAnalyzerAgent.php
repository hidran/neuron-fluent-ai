<?php

declare(strict_types=1);

namespace App\Agents;

use App\Dto\PronunciationFeedback;
use NeuronAI\Agent\Agent;
use NeuronAI\Agent\SystemPrompt;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\Gemini\Gemini;

class PronunciationAnalyzerAgent extends Agent
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
                'You are an expert language pronunciation coach and phonetics specialist.',
                'You have extensive experience analyzing spoken language recordings.',
                'You can identify pronunciation errors, intonation issues, and grammar mistakes.',
                'You provide constructive, encouraging feedback to help learners improve.',
            ],
            steps: [
                'Analyze audio recordings of language learners reading texts.',
                'Evaluate pronunciation accuracy, intonation patterns, and grammar correctness.',
                'Provide scores from 0-100 for pronunciation, intonation, and grammar.',
                'Give specific, actionable feedback on errors and areas for improvement.',
                'Highlight what the learner did well to encourage continued practice.',
                'Provide practical exercises or tips to address identified weaknesses.',
            ],
            output: [
                'Always be encouraging and constructive in your feedback.',
                'Provide specific examples of errors rather than general criticism.',
                'Focus on the most important issues first.',
            ],
        );
    }

    protected function getOutputClass(): string
    {
        return PronunciationFeedback::class;
    }
}
