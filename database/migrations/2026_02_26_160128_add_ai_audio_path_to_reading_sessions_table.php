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
        Schema::table('reading_sessions', function (Blueprint $table) {
            $table->string('ai_audio_path')->nullable()->after('ai_voice');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reading_sessions', function (Blueprint $table) {
            $table->dropColumn('ai_audio_path');
        });
    }
};
