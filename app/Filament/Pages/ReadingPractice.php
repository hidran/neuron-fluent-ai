<?php

namespace App\Filament\Pages;

use App\Models\ReadingCategory;
use App\Models\ReadingSession;
use App\Services\GeminiService;
use App\Services\ReadingPractice\ReadingPracticeStateService;
use App\Services\ReadingPractice\ReadingRecordingService;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class ReadingPractice extends Page implements HasForms
{
    use InteractsWithForms;
    use WithFileUploads;

    public const LANGUAGE_OPTIONS = [
        'en' => 'English',
        'es' => 'Spanish',
        'fr' => 'French',
        'de' => 'German',
        'it' => 'Italian',
        'pt' => 'Portuguese',
    ];

    public const VOICE_OPTIONS = [
        'nova' => 'Nova',
        'alloy' => 'Alloy',
        'echo' => 'Echo',
        'fable' => 'Fable',
        'onyx' => 'Onyx',
        'shimmer' => 'Shimmer',
    ];

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
                    ->options(self::LANGUAGE_OPTIONS)
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn () => $this->resetReadingState()),
                Select::make('selectedVoice')
                    ->label('AI Voice')
                    ->options(self::VOICE_OPTIONS)
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

            $this->generatedText = app(GeminiService::class)->generateReadingText(
                $category->name,
                $this->data['selectedLanguage'],
                $category->difficulty_level
            );

            $session = $this->stateService()->createSession(
                $this->currentUserId(),
                $category,
                (string) $this->data['selectedLanguage'],
                $this->generatedText,
                $this->data['selectedVoice'] ?? null,
            );

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
            Log::error('Error generating text: '.$e->getMessage(), [
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

        if (! $this->recordingUpload instanceof TemporaryUploadedFile) {
            Notification::make()
                ->title('Invalid recording upload')
                ->body('Please record audio again before analyzing.')
                ->danger()
                ->send();

            return;
        }

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
            $recordingService = app(ReadingRecordingService::class);
            $recording = $recordingService->storeUploadedRecording($session, $this->recordingUpload, 'public');
            $feedback = $recordingService->analyzeAndPersistFeedback(
                $session,
                $recording,
                $this->generatedText,
                (string) $this->data['selectedLanguage']
            );

            $this->feedback = $feedback;

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
        $session = $this->stateService()->findOwnedSession($sessionId, $this->currentUserId(), withRecordings: true);

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

        $this->feedback = $this->stateService()->latestFeedbackPayload($session);

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

        return $this->stateService()->findOwnedSession($this->currentReadingSessionId, $this->currentUserId());
    }

    protected function refreshSavedRecordings(): void
    {
        $session = $this->getCurrentReadingSession();

        if (! $session) {
            $this->savedRecordings = [];

            return;
        }

        $this->savedRecordings = $this->stateService()->savedRecordingsPayload($session);
    }

    protected function currentUserId(): int
    {
        return (int) Auth::id();
    }

    protected function stateService(): ReadingPracticeStateService
    {
        return app(ReadingPracticeStateService::class);
    }
}
