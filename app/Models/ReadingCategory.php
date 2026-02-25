<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReadingCategory extends Model
{
    /** @use HasFactory<\Database\Factories\ReadingCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'difficulty_level',
        'is_active',
        'sort_order',
    ];

    protected $attributes = [
        'difficulty_level' => 'beginner',
        'is_active' => true,
        'sort_order' => 0,
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function readingSessions(): HasMany
    {
        return $this->hasMany(ReadingSession::class);
    }
}
