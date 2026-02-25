<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ReadingRecording extends Model
{
    /** @use HasFactory<\Database\Factories\ReadingRecordingFactory> */
    use HasFactory;

    protected $fillable = [
        'reading_session_id',
        'storage_disk',
        'audio_file_path',
        'mime_type',
        'file_size',
        'ai_feedback',
        'pronunciation_score',
        'intonation_score',
        'grammar_score',
        'analyzed_at',
    ];

    protected function casts(): array
    {
        return [
            'analyzed_at' => 'datetime',
        ];
    }

    public function readingSession(): BelongsTo
    {
        return $this->belongsTo(ReadingSession::class);
    }

    public function playbackUrl(): string
    {
        if ($this->storage_disk === 'public') {
            return '/storage/' . ltrim((string) $this->audio_file_path, '/');
        }

        return Storage::disk($this->storage_disk)->url((string) $this->audio_file_path);
    }
}
