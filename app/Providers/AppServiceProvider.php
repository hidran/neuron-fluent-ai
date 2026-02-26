<?php

namespace App\Providers;

use App\Contracts\ReadingPractice\AudioAnalyzerInterface;
use App\Contracts\ReadingPractice\TextGeneratorInterface;
use App\Services\GeminiService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TextGeneratorInterface::class, GeminiService::class);
        $this->app->bind(AudioAnalyzerInterface::class, GeminiService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
