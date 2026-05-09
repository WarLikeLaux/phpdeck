<?php

use App\Models\Flashcard;
use App\Models\FlashcardUserProgress;
use App\Models\User;

function makeProgress(array $attributes = []): FlashcardUserProgress
{
    $user = User::factory()->create();
    $card = Flashcard::factory()->create([
        'question' => 'Q',
        'answer' => 'A',
    ]);

    return FlashcardUserProgress::query()->create(array_merge([
        'user_id' => $user->id,
        'flashcard_id' => $card->id,
    ], $attributes));
}

it('marks a card learned after three distinct modes by default', function (): void {
    $progress = makeProgress();

    $progress->markCorrect('reveal');
    expect($progress->is_learned)->toBeFalse()
        ->and($progress->correct_modes)->toBe(['reveal']);

    $progress->markCorrect('type_in');
    expect($progress->is_learned)->toBeFalse();

    $progress->markCorrect('multiple_choice');
    expect($progress->is_learned)->toBeTrue()
        ->and($progress->correct_streak)->toBe(3)
        ->and($progress->correct_modes)->toBe(['reveal', 'type_in', 'multiple_choice'])
        ->and($progress->srs_step)->toBe(0)
        ->and($progress->next_review_at)->not->toBeNull();
});

it('does not double-count the same mode', function (): void {
    $progress = makeProgress();

    $progress->markCorrect('reveal');
    $progress->markCorrect('reveal');
    $progress->markCorrect('reveal');

    expect($progress->is_learned)->toBeFalse()
        ->and($progress->correct_modes)->toBe(['reveal'])
        ->and($progress->correct_streak)->toBe(3);
});

it('clears correct_modes and srs state on a mistake', function (): void {
    $progress = makeProgress();

    $progress->markCorrect('reveal');
    $progress->markCorrect('type_in');
    expect($progress->correct_modes)->toBe(['reveal', 'type_in']);

    $progress->markIncorrect();
    expect($progress->correct_streak)->toBe(0)
        ->and($progress->correct_modes)->toBe([])
        ->and($progress->is_learned)->toBeFalse()
        ->and($progress->srs_step)->toBe(0)
        ->and($progress->next_review_at)->toBeNull();
});

it('resets progress fully', function (): void {
    $user = User::factory()->create();
    $card = Flashcard::factory()->create();
    $progress = FlashcardUserProgress::factory()
        ->for($user)
        ->for($card, 'flashcard')
        ->learned()
        ->create();

    $progress->resetProgress();

    expect($progress->correct_streak)->toBe(0)
        ->and($progress->correct_modes)->toBe([])
        ->and($progress->is_learned)->toBeFalse()
        ->and($progress->studied)->toBeFalse()
        ->and($progress->srs_step)->toBe(0)
        ->and($progress->next_review_at)->toBeNull();
});

it('scopes due to non-learned, studied progress rows', function (): void {
    $user = User::factory()->create();

    // studied + not learned = due
    FlashcardUserProgress::factory()
        ->for($user)
        ->for(Flashcard::factory()->create(), 'flashcard')
        ->create(['studied' => true, 'is_learned' => false]);

    // learned, future review = not due
    FlashcardUserProgress::factory()
        ->for($user)
        ->for(Flashcard::factory()->create(), 'flashcard')
        ->learned()
        ->create();

    // unstudied = not due
    FlashcardUserProgress::factory()
        ->for($user)
        ->for(Flashcard::factory()->create(), 'flashcard')
        ->unstudied()
        ->create();

    expect(FlashcardUserProgress::query()->forUser($user->id)->due()->count())->toBe(1);
});

it('marks a card as studied', function (): void {
    $progress = makeProgress(['studied' => false]);

    expect($progress->studied)->toBeFalse();

    $progress->markStudied();

    expect($progress->fresh()->studied)->toBeTrue();
});

it('honors a custom required_correct threshold', function (): void {
    $progress = makeProgress(['required_correct' => 2]);

    $progress->markCorrect('reveal');
    expect($progress->is_learned)->toBeFalse();

    $progress->markCorrect('type_in');
    expect($progress->is_learned)->toBeTrue();
});

it('schedules the first SRS review one day after learning', function (): void {
    $progress = makeProgress();

    $progress->markCorrect('reveal');
    $progress->markCorrect('type_in');
    $progress->markCorrect('multiple_choice');

    expect($progress->is_learned)->toBeTrue()
        ->and($progress->srs_step)->toBe(0)
        ->and($progress->next_review_at)->not->toBeNull()
        ->and(abs($progress->next_review_at->diffInHours(now(), true)))->toBeGreaterThanOrEqual(23);
});

it('advances SRS interval on correct review of a learned card', function (): void {
    $user = User::factory()->create();
    $card = Flashcard::factory()->create();
    $progress = FlashcardUserProgress::factory()
        ->for($user)
        ->for($card, 'flashcard')
        ->dueForReview()
        ->create();

    $progress->markCorrect('reveal');
    expect($progress->srs_step)->toBe(1)
        ->and(round(abs($progress->next_review_at->diffInDays(now(), true))))->toBe(3.0);

    $progress->markCorrect('reveal');
    expect($progress->srs_step)->toBe(2)
        ->and(round(abs($progress->next_review_at->diffInDays(now(), true))))->toBe(5.0);

    $progress->markCorrect('reveal');
    expect($progress->srs_step)->toBe(3)
        ->and(round(abs($progress->next_review_at->diffInDays(now(), true))))->toBe(7.0);
});

it('graduates a card after the final SRS step', function (): void {
    $user = User::factory()->create();
    $card = Flashcard::factory()->create();
    $progress = FlashcardUserProgress::factory()
        ->for($user)
        ->for($card, 'flashcard')
        ->create([
            'studied' => true,
            'is_learned' => true,
            'correct_modes' => ['reveal', 'type_in', 'multiple_choice'],
            'srs_step' => 3,
            'next_review_at' => now()->subDay(),
        ]);

    $progress->markCorrect('reveal');

    expect($progress->srs_step)->toBe(4)
        ->and($progress->next_review_at)->toBeNull()
        ->and($progress->is_learned)->toBeTrue();
});

it('resets a learned card to relearn on incorrect review', function (): void {
    $user = User::factory()->create();
    $card = Flashcard::factory()->create();
    $progress = FlashcardUserProgress::factory()
        ->for($user)
        ->for($card, 'flashcard')
        ->dueForReview()
        ->create();

    $progress->markIncorrect();

    expect($progress->is_learned)->toBeFalse()
        ->and($progress->correct_modes)->toBe([])
        ->and($progress->srs_step)->toBe(0)
        ->and($progress->next_review_at)->toBeNull();
});

it('scopeDue includes overdue SRS reviews', function (): void {
    $user = User::factory()->create();

    // studied, not yet learned (in learning phase)
    $learning = FlashcardUserProgress::factory()
        ->for($user)
        ->for(Flashcard::factory()->create(), 'flashcard')
        ->create(['studied' => true, 'is_learned' => false]);

    // overdue SRS review
    $review = FlashcardUserProgress::factory()
        ->for($user)
        ->for(Flashcard::factory()->create(), 'flashcard')
        ->dueForReview()
        ->create();

    // learned with future review (not due)
    FlashcardUserProgress::factory()
        ->for($user)
        ->for(Flashcard::factory()->create(), 'flashcard')
        ->learned()
        ->create();

    // graduated (no next review) — not due
    FlashcardUserProgress::factory()
        ->for($user)
        ->for(Flashcard::factory()->create(), 'flashcard')
        ->graduated()
        ->create();

    $due = FlashcardUserProgress::query()->forUser($user->id)->due()->pluck('id')->all();

    expect($due)->toContain($learning->id, $review->id)->toHaveCount(2);
});

it('scopes by user', function (): void {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $card = Flashcard::factory()->create();

    FlashcardUserProgress::factory()->for($userA)->for($card, 'flashcard')->learned()->create();
    FlashcardUserProgress::factory()->for($userB)->for($card, 'flashcard')->create(['studied' => true, 'is_learned' => false]);

    expect(FlashcardUserProgress::query()->forUser($userA->id)->where('is_learned', true)->count())->toBe(1)
        ->and(FlashcardUserProgress::query()->forUser($userB->id)->where('is_learned', true)->count())->toBe(0);
});
