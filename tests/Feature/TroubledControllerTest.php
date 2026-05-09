<?php

use App\Models\Flashcard;
use App\Models\FlashcardEvent;
use App\Models\FlashcardUserProgress;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

function logTroubledEvent(int $userId, Flashcard $card, string $kind, ?string $when = null): void
{
    FlashcardEvent::create([
        'user_id' => $userId,
        'flashcard_id' => $card->id,
        'kind' => $kind,
        'occurred_at' => $when ?? now(),
    ]);
}

it('renders empty state when there are no events', function (): void {
    Flashcard::factory()->count(3)->create();

    $this->get(route('troubled.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('troubled/index')
            ->where('rows', [])
        );
});

it('lists cards with high error rate', function (): void {
    $bad = Flashcard::factory()->create(['question' => 'Bad']);
    $good = Flashcard::factory()->create(['question' => 'Good']);
    $userId = $this->user->id;

    foreach (range(1, 4) as $_) {
        logTroubledEvent($userId, $bad, 'study_incorrect');
    }
    logTroubledEvent($userId, $bad, 'study_correct');

    foreach (range(1, 5) as $_) {
        logTroubledEvent($userId, $good, 'study_correct');
    }

    $this->get(route('troubled.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('rows', 1)
            ->where('rows.0.flashcard.id', $bad->id)
            ->where('rows.0.metrics.bad', 4)
            ->where('rows.0.metrics.total', 5)
            ->where('rows.0.metrics.error_rate', 0.8)
        );
});

it('counts skipped events as bad', function (): void {
    $card = Flashcard::factory()->create();
    $userId = $this->user->id;

    logTroubledEvent($userId, $card, 'skipped');
    logTroubledEvent($userId, $card, 'skipped');
    logTroubledEvent($userId, $card, 'study_correct');

    $this->get(route('troubled.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('rows', 1)
            ->where('rows.0.metrics.skipped', 2)
            ->where('rows.0.metrics.bad', 2)
        );
});

it('ignores cards with fewer than 3 events', function (): void {
    $card = Flashcard::factory()->create();
    $userId = $this->user->id;

    logTroubledEvent($userId, $card, 'study_incorrect');
    logTroubledEvent($userId, $card, 'study_incorrect');

    $this->get(route('troubled.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('rows', []));
});

it('only considers events from the last 30 days', function (): void {
    $card = Flashcard::factory()->create();
    $userId = $this->user->id;

    logTroubledEvent($userId, $card, 'study_incorrect', now()->subDays(40)->toDateTimeString());
    logTroubledEvent($userId, $card, 'study_incorrect', now()->subDays(35)->toDateTimeString());
    logTroubledEvent($userId, $card, 'study_incorrect', now()->subDays(31)->toDateTimeString());

    $this->get(route('troubled.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('rows', []));
});

it('orders cards by error rate descending', function (): void {
    $worse = Flashcard::factory()->create(['question' => '90%']);
    $better = Flashcard::factory()->create(['question' => '50%']);
    $userId = $this->user->id;

    foreach (range(1, 9) as $_) {
        logTroubledEvent($userId, $worse, 'study_incorrect');
    }
    logTroubledEvent($userId, $worse, 'study_correct');

    foreach (range(1, 2) as $_) {
        logTroubledEvent($userId, $better, 'study_incorrect');
    }
    foreach (range(1, 2) as $_) {
        logTroubledEvent($userId, $better, 'study_correct');
    }

    $this->get(route('troubled.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('rows.0.flashcard.id', $worse->id)
            ->where('rows.1.flashcard.id', $better->id)
        );
});

it('logs a skipped event when learn skip is hit', function (): void {
    $card = Flashcard::factory()->create();

    $this->post(route('learn.skip', $card))->assertRedirect();

    $event = FlashcardEvent::query()->latest('id')->first();
    expect($event?->kind)->toBe('skipped')
        ->and($event?->user_id)->toBe($this->user->id)
        ->and($event?->flashcard_id)->toBe($card->id);
});

it('logs a skipped event when study skip is hit', function (): void {
    $card = Flashcard::factory()->create();

    $this->post(route('study.skip', $card))->assertRedirect();

    $event = FlashcardEvent::query()->latest('id')->first();
    expect($event?->kind)->toBe('skipped')
        ->and($event?->user_id)->toBe($this->user->id)
        ->and($event?->flashcard_id)->toBe($card->id);
});

it('logs a skipped event when review skip is hit', function (): void {
    $card = Flashcard::factory()->create();
    FlashcardUserProgress::factory()
        ->for($this->user)
        ->for($card, 'flashcard')
        ->learned()
        ->create();

    $this->post(route('review.skip', $card))->assertRedirect();

    $event = FlashcardEvent::query()->latest('id')->first();
    expect($event?->kind)->toBe('skipped')
        ->and($event?->user_id)->toBe($this->user->id)
        ->and($event?->flashcard_id)->toBe($card->id);
});

it('clears bad events for the current user via the clear endpoint', function (): void {
    $card = Flashcard::factory()->create();
    $userId = $this->user->id;

    logTroubledEvent($userId, $card, 'study_incorrect');
    logTroubledEvent($userId, $card, 'study_incorrect');
    logTroubledEvent($userId, $card, 'skipped');
    logTroubledEvent($userId, $card, 'review_forgot');
    logTroubledEvent($userId, $card, 'study_correct');
    logTroubledEvent($userId, $card, 'review_remember');

    $this->post(route('troubled.clear', $card))->assertRedirect();

    $kinds = FlashcardEvent::query()
        ->where('user_id', $userId)
        ->where('flashcard_id', $card->id)
        ->pluck('kind')
        ->all();

    expect($kinds)->toBe(['study_correct', 'review_remember']);
});

it('does not clear bad events for other users', function (): void {
    $card = Flashcard::factory()->create();
    $other = User::factory()->create();

    logTroubledEvent($other->id, $card, 'study_incorrect');
    logTroubledEvent($other->id, $card, 'skipped');
    logTroubledEvent($this->user->id, $card, 'study_incorrect');

    $this->post(route('troubled.clear', $card))->assertRedirect();

    expect(FlashcardEvent::query()->where('user_id', $other->id)->count())->toBe(2)
        ->and(FlashcardEvent::query()->where('user_id', $this->user->id)->count())->toBe(0);
});

it('paginates troubled cards with 20 per page', function (): void {
    $userId = $this->user->id;

    for ($i = 0; $i < 25; $i++) {
        $card = Flashcard::factory()->create();
        foreach (range(1, 4) as $_) {
            logTroubledEvent($userId, $card, 'study_incorrect');
        }
        logTroubledEvent($userId, $card, 'study_correct');
    }

    $this->get(route('troubled.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('rows', 20)
            ->where('pagination.current_page', 1)
            ->where('pagination.last_page', 2)
            ->where('pagination.total', 25)
            ->where('pagination.per_page', 20)
        );

    $this->get(route('troubled.show', ['page' => 2]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('rows', 5)
            ->where('pagination.current_page', 2)
        );
});

it('isolates troubled stats per user', function (): void {
    $card = Flashcard::factory()->create();
    $other = User::factory()->create();

    // Lots of bad events for other user — must NOT show up for current user.
    foreach (range(1, 5) as $_) {
        logTroubledEvent($other->id, $card, 'study_incorrect');
    }

    $this->get(route('troubled.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('rows', []));
});
