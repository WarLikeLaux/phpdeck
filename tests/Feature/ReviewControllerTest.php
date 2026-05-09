<?php

use App\Models\Flashcard;
use App\Models\FlashcardEvent;
use App\Models\FlashcardUserProgress;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

function reviewLearned(User $user, ?Flashcard $card = null): array
{
    $card ??= Flashcard::factory()->create();
    $progress = FlashcardUserProgress::factory()
        ->for($user)
        ->for($card, 'flashcard')
        ->learned()
        ->create();

    return [$card, $progress];
}

it('renders a learned card on the review page', function (): void {
    [$learned] = reviewLearned($this->user);
    Flashcard::factory()->create();

    $this->get(route('review.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('review/index')
            ->where('flashcard.id', $learned->id)
            ->where('stats.total', 1)
            ->where('stats.seen', 0)
            ->where('stats.remaining', 1)
        );
});

it('only shows learned cards', function (): void {
    Flashcard::factory()->count(3)->create();
    [$learned] = reviewLearned($this->user);

    $this->get(route('review.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('flashcard.id', $learned->id));
});

it('marks a card as remembered and removes it from the queue', function (): void {
    [$a] = reviewLearned($this->user);
    [$b] = reviewLearned($this->user);

    $this->withSession([])->post(route('review.remember', $a))
        ->assertRedirect(route('review.show'));

    $this->get(route('review.show'))
        ->assertInertia(fn ($page) => $page
            ->where('flashcard.id', $b->id)
            ->where('stats.seen', 1)
            ->where('stats.remaining', 1)
        );
});

it('marks a card as forgotten and sends it back to study', function (): void {
    [$a, $progress] = reviewLearned($this->user);

    $this->post(route('review.forgot', $a))
        ->assertRedirect(route('review.show'));

    $fresh = $progress->fresh();
    expect($fresh->is_learned)->toBeFalse()
        ->and($fresh->correct_modes)->toBe([])
        ->and($fresh->srs_step)->toBe(0)
        ->and($fresh->next_review_at)->toBeNull();
});

it('respects exclude param for repeat', function (): void {
    [$a] = reviewLearned($this->user);
    [$b] = reviewLearned($this->user);

    $this->get(route('review.show', ['exclude' => $a->id]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('flashcard.id', $b->id));
});

it('shows empty state once every learned card has been seen', function (): void {
    [$a] = reviewLearned($this->user);

    $this->withSession(['review.seen' => [$a->id]])
        ->get(route('review.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('flashcard', null)
            ->where('stats.remaining', 0)
        );
});

it('resets the session via the reset endpoint', function (): void {
    [$a] = reviewLearned($this->user);

    $this->withSession(['review.seen' => [$a->id]])
        ->post(route('review.reset'))
        ->assertRedirect(route('review.show'));

    $this->get(route('review.show'))
        ->assertInertia(fn ($page) => $page->where('stats.seen', 0));
});

it('shows empty state when there are no learned cards at all', function (): void {
    Flashcard::factory()->count(2)->create();

    $this->get(route('review.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('flashcard', null)
            ->where('stats.total', 0)
        );
});

it('logs a review_remember event with user_id', function (): void {
    [$a] = reviewLearned($this->user);

    $this->post(route('review.remember', $a))->assertRedirect();

    $event = FlashcardEvent::query()->latest('id')->first();
    expect($event?->kind)->toBe('review_remember')
        ->and($event?->user_id)->toBe($this->user->id)
        ->and($event?->flashcard_id)->toBe($a->id);
});

it('logs a review_forgot event with user_id', function (): void {
    [$a] = reviewLearned($this->user);

    $this->post(route('review.forgot', $a))->assertRedirect();

    $event = FlashcardEvent::query()->latest('id')->first();
    expect($event?->kind)->toBe('review_forgot')
        ->and($event?->user_id)->toBe($this->user->id)
        ->and($event?->flashcard_id)->toBe($a->id);
});

it('does not show another users learned cards', function (): void {
    $other = User::factory()->create();
    reviewLearned($other);

    $this->get(route('review.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('flashcard', null)
            ->where('stats.total', 0)
        );
});
