<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReadingSession extends Model
{
    /** @use HasFactory<\Database\Factories\ReadingSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reading_category_id',
        'language',
        'generated_text',
        'ai_voice',
        'ai_audio_path',
        'audio_file_path',
        'ai_feedback',
        'pronunciation_score',
        'intonation_score',
        'grammar_score',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function readingCategory(): BelongsTo
    {
        return $this->belongsTo(ReadingCategory::class);
    }

    public function recordings(): HasMany
    {
        return $this->hasMany(ReadingRecording::class);
    }
}
