<?php

namespace Tests\Feature;

use App\Models\ReadingCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_reading_category(): void
    {
        $category = ReadingCategory::create([
            'name' => 'Test Category',
            'description' => 'Test description',
            'difficulty_level' => 'beginner',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->assertDatabaseHas('reading_categories', [
            'name' => 'Test Category',
            'difficulty_level' => 'beginner',
        ]);
    }

    public function test_reading_category_has_default_values(): void
    {
        $category = ReadingCategory::create([
            'name' => 'Test Category',
        ]);

        $this->assertTrue($category->is_active);
        $this->assertEquals('beginner', $category->difficulty_level);
        $this->assertEquals(0, $category->sort_order);
    }

    public function test_reading_category_has_sessions_relationship(): void
    {
        $category = ReadingCategory::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $category->readingSessions());
    }

    public function test_can_filter_active_categories(): void
    {
        ReadingCategory::factory()->create(['is_active' => true, 'name' => 'Active Category']);
        ReadingCategory::factory()->create(['is_active' => false, 'name' => 'Inactive Category']);

        $activeCategories = ReadingCategory::where('is_active', true)->get();

        $this->assertCount(1, $activeCategories);
        $this->assertEquals('Active Category', $activeCategories->first()->name);
    }

    public function test_categories_can_be_ordered_by_sort_order(): void
    {
        ReadingCategory::factory()->create(['name' => 'Third', 'sort_order' => 3]);
        ReadingCategory::factory()->create(['name' => 'First', 'sort_order' => 1]);
        ReadingCategory::factory()->create(['name' => 'Second', 'sort_order' => 2]);

        $categories = ReadingCategory::orderBy('sort_order')->get();

        $this->assertEquals('First', $categories->first()->name);
        $this->assertEquals('Third', $categories->last()->name);
    }
}
