<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use NeuronAI\Chat\Enums\MessageRole;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Providers\Gemini\Gemini;
use NeuronAI\Providers\OpenAI\OpenAI;

class TestAIProviders extends Command
{
    protected $signature = 'test:ai-providers';

    protected $description = 'Test OpenAI and Gemini API connections';

    public function handle(): int
    {
        $this->info('Testing AI Provider Connections...');
        $this->newLine();

        // Test OpenAI
        $this->info('🔵 Testing OpenAI...');
        try {
            $openai = new OpenAI(
                key: config('services.openai.key'),
                model: 'gpt-4o-mini',
            );

            $response = $openai->chat(
                new Message(MessageRole::USER, 'Say "OpenAI connection successful!" and nothing else.')
            );

            $content = $response->getContent();
            $this->info('✅ OpenAI Response: ' . $content);
        } catch (\Exception $e) {
            $this->error('❌ OpenAI Error: ' . $e->getMessage());
        }

        $this->newLine();

        // Test Gemini
        $this->info('🟢 Testing Gemini...');
        try {
            $gemini = new Gemini(
                key: config('services.gemini.key'),
                model: config('services.gemini.model'),
            );

            $response = $gemini->chat(
                new Message(MessageRole::USER, 'Say "Gemini connection successful!" and nothing else.')
            );

            $content = $response->getContent();
            $this->info('✅ Gemini Response: ' . $content);
        } catch (\Exception $e) {
            $this->error('❌ Gemini Error: ' . $e->getMessage());
        }

        $this->newLine();
        $this->info('Testing complete!');

        return self::SUCCESS;
    }
}
