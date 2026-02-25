<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReadingCategory;
use App\Models\ReadingRecording;
use App\Models\ReadingSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ReadingPracticeController extends Controller
{
    public function getCategories()
    {
        return ReadingCategory::all();
    }

    public function generateText(Request $request)
    {
        $request->validate([
            'topic' => 'required|string|max:255',
            'level' => 'required|string|in:beginner,intermediate,advanced',
        ]);

        // This is where you would call your AI service to generate text.
        // For now, we'll return some dummy data.
        $generatedText = "This is a sample text about {$request->topic} for {$request->level} learners.";

        return response()->json(['text' => $generatedText]);
    }

    public function analyzeRecording(Request $request)
    {
        $request->validate([
            'audio' => 'required|file|mimes:mp3,wav,ogg',
            'text' => 'required|string',
        ]);

        // This is where you would call your AI service to analyze the recording.
        // For now, we'll return some dummy data.
        $feedback = [
            'pronunciation' => 85,
            'intonation' => 90,
            'grammar' => 95,
            'feedback' => 'Great job! Your pronunciation was clear and your intonation was engaging. Keep up the good work.',
        ];

        return response()->json($feedback);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'text' => 'required|string',
            'audio' => 'required|file|mimes:mp3,wav,ogg',
            'feedback' => 'required|json',
            'user_id' => 'required|exists:users,id',
            'reading_category_id' => 'required|exists:reading_categories,id',
            'language' => 'required|string',
        ]);

        $feedback = json_decode($validated['feedback'], true);
        $audioFile = $request->file('audio');

        try {
            DB::beginTransaction();

            $readingSession = ReadingSession::create([
                'user_id' => $validated['user_id'],
                'reading_category_id' => $validated['reading_category_id'],
                'language' => $validated['language'],
                'generated_text' => $validated['text'],
                'pronunciation_score' => $feedback['pronunciation'] ?? null,
                'intonation_score' => $feedback['intonation'] ?? null,
                'grammar_score' => $feedback['grammar'] ?? null,
                'ai_feedback' => $feedback['feedback'] ?? null,
            ]);

            $path = $audioFile->store('recordings', 'public');

            ReadingRecording::create([
                'reading_session_id' => $readingSession->id,
                'audio_file_path' => $path,
                'storage_disk' => 'public',
                'mime_type' => $audioFile->getMimeType(),
                'file_size' => $audioFile->getSize(),
                'ai_feedback' => $validated['feedback'],
                'pronunciation_score' => $feedback['pronunciation'] ?? null,
                'intonation_score' => $feedback['intonation'] ?? null,
                'grammar_score' => $feedback['grammar'] ?? null,
                'analyzed_at' => now(),
            ]);

            DB::commit();

            return response()->json(['message' => 'Reading practice session saved successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($path)) {
                Storage::disk('public')->delete($path);
            }
            return response()->json(['message' => 'Failed to save session.', 'error' => $e->getMessage()], 500);
        }
    }
}

