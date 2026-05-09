<?php

use App\Http\Controllers\FlashcardController;
use App\Http\Controllers\LearnController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\StudyController;
use App\Http\Controllers\TroubledController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/flashcards')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('flashcards', [FlashcardController::class, 'index'])->name('flashcards.index');
    Route::post('flashcards/reset', [FlashcardController::class, 'reset'])->name('flashcards.reset');

    Route::get('learn', [LearnController::class, 'show'])->name('learn.show');
    Route::post('learn/{flashcard}/studied', [LearnController::class, 'studied'])->name('learn.studied');
    Route::post('learn/{flashcard}/skip', [LearnController::class, 'skip'])->name('learn.skip');

    Route::get('study', [StudyController::class, 'show'])->name('study.show');
    Route::post('study/{flashcard}/answer', [StudyController::class, 'answer'])->name('study.answer');
    Route::post('study/{flashcard}/skip', [StudyController::class, 'skip'])->name('study.skip');
    Route::post('study/matching', [StudyController::class, 'matching'])->name('study.matching');

    Route::get('review', [ReviewController::class, 'show'])->name('review.show');
    Route::post('review/reset', [ReviewController::class, 'reset'])->name('review.reset');
    Route::post('review/{flashcard}/remember', [ReviewController::class, 'remember'])->name('review.remember');
    Route::post('review/{flashcard}/forgot', [ReviewController::class, 'forgot'])->name('review.forgot');
    Route::post('review/{flashcard}/skip', [ReviewController::class, 'skip'])->name('review.skip');

    Route::get('stats', [StatsController::class, 'show'])->name('stats.show');

    Route::get('troubled', [TroubledController::class, 'show'])->name('troubled.show');
    Route::post('troubled/{flashcard}/clear', [TroubledController::class, 'clear'])->name('troubled.clear');
});

require __DIR__.'/settings.php';
