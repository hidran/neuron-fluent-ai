<?php

namespace Tests\Feature;

use App\Filament\Pages\ReadingPractice;
use App\Models\ReadingCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReadingPracticePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_reading_practice_page_can_render(): void
    {
        Livewire::test(ReadingPractice::class)
            ->assertFormExists();
    }

    public function test_reading_practice_page_has_form_components(): void
    {
        Livewire::test(ReadingPractice::class)
            ->assertFormExists()
            ->assertFormFieldExists('selectedCategory')
            ->assertFormFieldExists('selectedLanguage')
            ->assertFormFieldExists('selectedVoice');
    }

    public function test_category_field_loads_active_categories(): void
    {
        $activeCategory = ReadingCategory::factory()->create(['name' => 'Active Cat', 'is_active' => true]);
        $inactiveCategory = ReadingCategory::factory()->create(['name' => 'Inactive Cat', 'is_active' => false]);

        Livewire::test(ReadingPractice::class)
            ->assertFormFieldExists('selectedCategory', function ($field) use ($activeCategory, $inactiveCategory): bool {
                $options = $field->getOptions();

                return array_key_exists((string) $activeCategory->id, $options)
                    && $options[(string) $activeCategory->id] === 'Active Cat'
                    && ! array_key_exists((string) $inactiveCategory->id, $options);
            });
    }

    public function test_language_field_has_language_options(): void
    {
        Livewire::test(ReadingPractice::class)
            ->assertFormFieldExists('selectedLanguage', fn ($field): bool => $field->getOptions() === ReadingPractice::LANGUAGE_OPTIONS);
    }

    public function test_voice_field_has_default_value(): void
    {
        Livewire::test(ReadingPractice::class)
            ->assertFormFieldExists('selectedVoice', fn ($field): bool => $field->getOptions() === ReadingPractice::VOICE_OPTIONS)
            ->assertFormSet([
                'selectedVoice' => 'nova',
            ]);
    }

    public function test_can_select_category_and_language(): void
    {
        $category = ReadingCategory::factory()->create(['is_active' => true]);

        Livewire::test(ReadingPractice::class)
            ->fillForm([
                'selectedCategory' => $category->id,
                'selectedLanguage' => 'en',
            ])
            ->assertHasNoFormErrors();
    }

    public function test_reading_text_section_is_initially_hidden(): void
    {
        Livewire::test(ReadingPractice::class)
            ->assertDontSee('Read this passage aloud, then record your attempt.')
            ->assertDontSee('Pronunciation Assessment');
    }

    public function test_recording_controls_are_initially_hidden_until_text_is_generated(): void
    {
        Livewire::test(ReadingPractice::class)
            ->assertDontSee('Start Recording')
            ->assertDontSee('Analyze Recording');
    }

    public function test_page_has_correct_title(): void
    {
        Livewire::test(ReadingPractice::class)
            ->assertSee('Reading Practice');
    }

    public function test_page_requires_authentication(): void
    {
        auth()->logout();

        $this->get('/admin/reading-practice')
            ->assertRedirect('/admin/login');
    }
}
