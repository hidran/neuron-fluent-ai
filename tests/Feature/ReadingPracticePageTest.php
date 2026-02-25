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
        $this->get('/admin/reading-practice')
            ->assertOk();
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
        ReadingCategory::factory()->create(['name' => 'Active Cat', 'is_active' => true]);
        ReadingCategory::factory()->create(['name' => 'Inactive Cat', 'is_active' => false]);

        Livewire::test(ReadingPractice::class)
            ->assertFormFieldExists('selectedCategory')
            ->assertSee('Active Cat')
            ->assertDontSee('Inactive Cat');
    }

    public function test_language_field_has_language_options(): void
    {
        Livewire::test(ReadingPractice::class)
            ->assertFormFieldExists('selectedLanguage')
            ->assertSee('English')
            ->assertSee('Spanish')
            ->assertSee('French');
    }

    public function test_voice_field_has_default_value(): void
    {
        Livewire::test(ReadingPractice::class)
            ->assertFormFieldExists('selectedVoice')
            ->assertSee('Nova');
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

    public function test_generated_text_field_is_initially_hidden(): void
    {
        Livewire::test(ReadingPractice::class)
            ->assertFormFieldIsHidden('generatedText');
    }

    public function test_audio_upload_field_is_initially_hidden(): void
    {
        Livewire::test(ReadingPractice::class)
            ->assertFormFieldIsHidden('audioFile');
    }

    public function test_page_has_correct_title(): void
    {
        $this->get('/admin/reading-practice')
            ->assertSee('Reading Practice');
    }

    public function test_page_requires_authentication(): void
    {
        auth()->logout();

        $this->get('/admin/reading-practice')
            ->assertRedirect('/admin/login');
    }
}
