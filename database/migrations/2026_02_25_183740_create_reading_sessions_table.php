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
        Schema::create('reading_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reading_category_id')->constrained()->cascadeOnDelete();
            $table->string('language', 10); // e.g., 'en', 'es', 'fr'
            $table->text('generated_text');
            $table->string('ai_voice')->nullable(); // voice type selected
            $table->string('audio_file_path')->nullable(); // user's recording
            $table->text('ai_feedback')->nullable(); // JSON with pronunciation, intonation, grammar feedback
            $table->integer('pronunciation_score')->nullable();
            $table->integer('intonation_score')->nullable();
            $table->integer('grammar_score')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reading_sessions');
    }
};
