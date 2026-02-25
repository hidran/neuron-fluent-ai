<?php

namespace App\Filament\Pages;

use App\Models\ReadingCategory;
use App\Models\ReadingRecording;
use App\Models\ReadingSession;
use App\Services\GeminiService;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class ReadingPractice extends Page implements HasForms
{
    use InteractsWithForms;
    use WithFileUploads;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMicrophone;

    protected string $view = 'filament.pages.reading-practice';

    protected static ?string $title = 'Reading Practice';

    public ?array $data = [];

    public ?string $generatedText = null;

    public ?array $feedback = null;

    public ?int $currentReadingSessionId = null;

    public array $savedRecordings = [];

    public $recordingUpload = null;

    public function mount(): void
    {
        $this->form->fill();

        $sessionId = request()->integer('session');

        if ($sessionId > 0) {
            $this->loadSessionForRecording($sessionId);
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('selectedCategory')
                    ->label('Category')
                    ->options(ReadingCategory::query()->where('is_active', true)->pluck('name', 'id'))
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn () => $this->resetReadingState()),
                Select::make('selectedLanguage')
                    ->label('Language')
                    ->options([
                        'en' => 'English',
                        'es' => 'Spanish',
                        'fr' => 'French',
                        'de' => 'German',
                        'it' => 'Italian',
                        'pt' => 'Portuguese',
                    ])
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn () => $this->resetReadingState()),
                Select::make('selectedVoice')
                    ->label('AI Voice')
                    ->options([
                        'nova' => 'Nova',
                        'alloy' => 'Alloy',
                        'echo' => 'Echo',
                        'fable' => 'Fable',
                        'onyx' => 'Onyx',
                        'shimmer' => 'Shimmer',
                    ])
                    ->default('nova'),
            ])
            ->statePath('data');
    }

    public function generateText(): void
    {
        try {
            $this->validate([
                'data.selectedCategory' => 'required',
                'data.selectedLanguage' => 'required',
            ]);

            $category = ReadingCategory::find($this->data['selectedCategory']);

            if (! $category) {
                throw new \Exception('Category not found');
            }

            $geminiService = app(GeminiService::class);

            $this->generatedText = $geminiService->generateReadingText(
                $category->name,
                $this->data['selectedLanguage'],
                $category->difficulty_level
            );

            $session = ReadingSession::create([
                'user_id' => Auth::id(),
                'reading_category_id' => $category->id,
                'language' => $this->data['selectedLanguage'],
                'generated_text' => $this->generatedText,
                'ai_voice' => $this->data['selectedVoice'] ?? null,
            ]);

            $this->feedback = null;
            $this->recordingUpload = null;
            $this->currentReadingSessionId = $session->id;
            $this->refreshSavedRecordings();

            Notification::make()
                ->title('Text generated successfully!')
                ->success()
                ->send();
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error generating text: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);

            Notification::make()
                ->title('Error generating text')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function analyzeRecording(): void
    {
        if (! $this->generatedText) {
            Notification::make()
                ->title('Please generate text first')
                ->warning()
                ->send();

            return;
        }

        $this->validate([
            'recordingUpload' => 'required|file|max:51200',
        ]);

        $session = $this->getCurrentReadingSession();

        if (! $session) {
            Notification::make()
                ->title('Reading session not found')
                ->body('Generate a new reading text before analyzing a recording.')
                ->warning()
                ->send();

            return;
        }

        try {
            $storageDisk = 'public';
            $storedPath = $this->recordingUpload->store('reading-recordings', $storageDisk);

            $recording = $session->recordings()->create([
                'storage_disk' => $storageDisk,
                'audio_file_path' => $storedPath,
                'mime_type' => $this->normalizeRecordedAudioMimeType(
                    $this->recordingUpload->getMimeType(),
                    $this->recordingUpload->getClientOriginalName()
                ),
                'file_size' => $this->recordingUpload->getSize(),
            ]);

            $geminiService = app(GeminiService::class);

            $feedback = $geminiService->analyzeAudioRecording(
                $recording->audio_file_path,
                $this->generatedText,
                $this->data['selectedLanguage'],
                $recording->storage_disk
            );

            $this->feedback = $feedback;

            $recording->update([
                'ai_feedback' => $feedback['feedback'],
                'pronunciation_score' => $feedback['pronunciation'],
                'intonation_score' => $feedback['intonation'],
                'grammar_score' => $feedback['grammar'],
                'analyzed_at' => now(),
            ]);

            $session->update([
                'audio_file_path' => $recording->audio_file_path,
                'ai_feedback' => $feedback['feedback'],
                'pronunciation_score' => $feedback['pronunciation'],
                'intonation_score' => $feedback['intonation'],
                'grammar_score' => $feedback['grammar'],
            ]);

            $this->recordingUpload = null;
            $this->refreshSavedRecordings();

            Notification::make()
                ->title('Recording analyzed successfully!')
                ->success()
                ->send();
        } catch (\Exception $e) {
            $this->refreshSavedRecordings();

            Notification::make()
                ->title('Error analyzing recording')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function resetReadingState(): void
    {
        $this->generatedText = null;
        $this->feedback = null;
        $this->currentReadingSessionId = null;
        $this->savedRecordings = [];
        $this->recordingUpload = null;
    }

    protected function loadSessionForRecording(int $sessionId): void
    {
        $session = ReadingSession::query()
            ->with('recordings')
            ->whereKey($sessionId)
            ->where('user_id', Auth::id())
            ->first();

        if (! $session) {
            Notification::make()
                ->title('Reading not found')
                ->body('The selected reading could not be loaded.')
                ->warning()
                ->send();

            return;
        }

        $this->form->fill([
            'selectedCategory' => $session->reading_category_id,
            'selectedLanguage' => $session->language,
            'selectedVoice' => $session->ai_voice ?: 'nova',
        ]);

        $this->generatedText = $session->generated_text;
        $this->currentReadingSessionId = $session->id;
        $this->recordingUpload = null;

        $latestAnalyzedRecording = $session->recordings
            ->whereNotNull('ai_feedback')
            ->sortByDesc('analyzed_at')
            ->first();

        $this->feedback = $latestAnalyzedRecording
            ? [
                'pronunciation' => $latestAnalyzedRecording->pronunciation_score,
                'intonation' => $latestAnalyzedRecording->intonation_score,
                'grammar' => $latestAnalyzedRecording->grammar_score,
                'feedback' => $latestAnalyzedRecording->ai_feedback,
            ]
            : null;

        $this->refreshSavedRecordings();

        Notification::make()
            ->title('Reading loaded')
            ->body('You can record a new attempt for this reading text now.')
            ->success()
            ->send();
    }

    protected function getCurrentReadingSession(): ?ReadingSession
    {
        if (! $this->currentReadingSessionId) {
            return null;
        }

        return ReadingSession::query()
            ->whereKey($this->currentReadingSessionId)
            ->where('user_id', Auth::id())
            ->first();
    }

    protected function refreshSavedRecordings(): void
    {
        $session = $this->getCurrentReadingSession();

        if (! $session) {
            $this->savedRecordings = [];

            return;
        }

        $this->savedRecordings = $session->recordings()
            ->latest()
            ->get()
            ->map(function (ReadingRecording $recording): array {
                return [
                    'id' => $recording->id,
                    'audio_url' => $recording->playbackUrl(),
                    'created_at' => $recording->created_at?->format('Y-m-d H:i:s'),
                    'mime_type' => $recording->mime_type,
                    'file_size' => $recording->file_size,
                    'pronunciation_score' => $recording->pronunciation_score,
                    'intonation_score' => $recording->intonation_score,
                    'grammar_score' => $recording->grammar_score,
                    'ai_feedback' => $recording->ai_feedback,
                    'analyzed_at' => $recording->analyzed_at?->format('Y-m-d H:i:s'),
                ];
            })
            ->all();
    }

    protected function normalizeRecordedAudioMimeType(?string $mimeType, ?string $originalFilename = null): string
    {
        $rawMimeType = strtolower(trim((string) $mimeType));
        $baseMimeType = trim(strtok($rawMimeType, ';') ?: '');

        if ($baseMimeType === 'video/webm') {
            return 'audio/webm';
        }

        if ($baseMimeType !== '') {
            return $baseMimeType;
        }

        $extension = strtolower((string) pathinfo((string) $originalFilename, PATHINFO_EXTENSION));

        return match ($extension) {
            'webm' => 'audio/webm',
            'ogg', 'oga' => 'audio/ogg',
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'm4a', 'mp4' => 'audio/mp4',
            default => 'audio/webm',
        };
    }
}
