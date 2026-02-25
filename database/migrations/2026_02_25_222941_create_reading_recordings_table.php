<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reading_recordings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reading_session_id')->constrained()->cascadeOnDelete();
            $table->string('storage_disk')->default('public');
            $table->string('audio_file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->text('ai_feedback')->nullable();
            $table->unsignedTinyInteger('pronunciation_score')->nullable();
            $table->unsignedTinyInteger('intonation_score')->nullable();
            $table->unsignedTinyInteger('grammar_score')->nullable();
            $table->timestamp('analyzed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reading_recordings');
    }
};
