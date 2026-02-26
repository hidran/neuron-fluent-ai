<?php

use App\Http\Controllers\Api\ReadingPracticeController;
use App\Http\Controllers\Api\ReadingSessionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('reading-practice')->name('reading-practice.')->group(function () {
    Route::get('/categories', [ReadingPracticeController::class, 'getCategories'])->name('categories');
    Route::post('/generate-text', [ReadingPracticeController::class, 'generateText'])->name('generate-text');
    Route::post('/analyze-recording', [ReadingPracticeController::class, 'analyzeRecording'])->name('analyze-recording');
    Route::post('/save', [ReadingPracticeController::class, 'save'])->name('save');
});

Route::apiResource('reading-sessions', ReadingSessionController::class)
    ->only(['index', 'show']);
