<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReadingCategory;
use App\Services\ReadingPractice\Data\SessionSaveData;
use App\Services\ReadingPractice\ReadingPracticeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Controller for Reading Practice API.
 * Uses PHP 8.4 Property Promotion and Strict Types.
 */
final class ReadingPracticeController extends Controller
{
    public function __construct(
        private readonly ReadingPracticeService $service,
    ) {}

    public function getCategories(): JsonResponse
    {
        return response()->json(ReadingCategory::all());
    }

    public function generateText(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'topic' => ['required', 'string', 'max:255'],
            'level' => ['required', 'string', 'in:beginner,intermediate,advanced'],
            'language' => ['nullable', 'string'],
            'voice' => ['nullable', 'string', 'in:alloy,echo,fable,onyx,nova,shimmer'],
        ]);

        $text = $this->service->generatePracticeText(
            $validated['topic'],
            $validated['language'] ?? 'English',
            $validated['level']
        );

        $response = ['text' => $text];

        if (! empty($validated['voice'])) {
            $audio = $this->service->synthesizeSpeech($text, $validated['voice']);
            $response['audio_url'] = $audio['audio_url'];
        }

        return response()->json($response);
    }

    public function analyzeRecording(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'audio' => ['required', 'file', 'mimes:mp3,wav,ogg,webm'],
            'text' => ['required', 'string'],
            'language' => ['nullable', 'string'],
        ]);

        $tempPath = $request->file('audio')->store('temp', 'public');

        try {
            $feedback = $this->service->analyzeCurrentRecording(
                $tempPath,
                $validated['text'],
                $validated['language'] ?? 'English',
                'public'
            );

            Storage::disk('public')->delete($tempPath);

            return response()->json($feedback);
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($tempPath);

            return response()->json(['message' => 'Analysis failed.', 'error' => $e->getMessage()], 500);
        }
    }

    public function save(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'text' => ['required', 'string'],
            'audio' => ['required', 'file', 'mimes:mp3,wav,ogg,webm'],
            'feedback' => ['required', 'json'],
            'user_id' => ['required', 'exists:users,id'],
            'reading_category_id' => ['required', 'exists:reading_categories,id'],
            'language' => ['required', 'string'],
            'ai_audio_url' => ['nullable', 'string'],
        ]);

        $this->service->persistCompleteSession(new SessionSaveData(
            userId: (int) $validated['user_id'],
            category: ReadingCategory::findOrFail($validated['reading_category_id']),
            language: $validated['language'],
            text: $validated['text'],
            audio: $request->file('audio'),
            feedback: json_decode($validated['feedback'], true),
            aiAudioUrl: $validated['ai_audio_url'] ?? null,
        ));

        return response()->json(['message' => 'Session saved successfully.']);
    }
}
