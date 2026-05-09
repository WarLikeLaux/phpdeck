<?php

use App\Models\Flashcard;
use App\Models\FlashcardEvent;
use App\Models\FlashcardUserProgress;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('renders the learn page with an unstudied flashcard', function (): void {
    Flashcard::factory()->count(3)->create(['category' => 'PHP']);

    $this->get(route('learn.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('learn/index')
            ->has('flashcard')
            ->has('categories')
            ->where('stats.total', 3)
            ->where('stats.unstudied', 3)
            ->where('stats.studied', 0)
            ->where('filters.category', 'all')
        );
});

it('only picks unstudied cards', function (): void {
    $studiedCard = Flashcard::factory()->create(['question' => 'studied-card']);
    FlashcardUserProgress::factory()
        ->for($this->user)
        ->for($studiedCard, 'flashcard')
        ->create(['studied' => true, 'is_learned' => false]);

    $unstudied = Flashcard::factory()->create(['question' => 'fresh-card']);

    $this->get(route('learn.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('flashcard.id', $unstudied->id)
        );
});

it('starts with the easiest unstudied difficulty', function (): void {
    Flashcard::factory()->create(['difficulty' => 5, 'question' => 'hard']);
    $easy = Flashcard::factory()->create(['difficulty' => 1, 'question' => 'easy']);

    $this->get(route('learn.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('flashcard.id', $easy->id)
        );
});

it('filters cards by category on the learn page', function (): void {
    Flashcard::factory()->count(2)->create(['category' => 'PHP']);
    Flashcard::factory()->count(3)->create(['category' => 'Laravel']);

    $this->get(route('learn.show', ['category' => 'PHP']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('stats.total', 2)
            ->where('stats.unstudied', 2)
            ->where('filters.category', 'PHP')
            ->where('flashcard.category', 'PHP')
        );
});

it('marks a card as studied via the studied endpoint', function (): void {
    $card = Flashcard::factory()->create();

    $this->post(route('learn.studied', $card))
        ->assertRedirect();

    $progress = FlashcardUserProgress::query()
        ->forUser($this->user->id)
        ->where('flashcard_id', $card->id)
        ->first();

    expect($progress)->not->toBeNull()
        ->and($progress->studied)->toBeTrue();
});

it('logs a studied event when a card is marked studied', function (): void {
    $card = Flashcard::factory()->create();

    $this->post(route('learn.studied', $card))->assertRedirect();

    $event = FlashcardEvent::query()->latest('id')->first();
    expect($event)->not->toBeNull()
        ->and($event->user_id)->toBe($this->user->id)
        ->and($event->flashcard_id)->toBe($card->id)
        ->and($event->kind)->toBe('studied')
        ->and($event->occurred_at)->not->toBeNull();
});

it('shows empty state when no unstudied cards remain', function (): void {
    $cards = Flashcard::factory()->count(2)->create();
    foreach ($cards as $card) {
        FlashcardUserProgress::factory()
            ->for($this->user)
            ->for($card, 'flashcard')
            ->create(['studied' => true, 'is_learned' => false]);
    }

    $this->get(route('learn.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('flashcard', null)
            ->where('stats.total', 2)
            ->where('stats.unstudied', 0)
            ->where('stats.studied', 2)
        );
});

it('respects exclude param', function (): void {
    $a = Flashcard::factory()->create();
    $b = Flashcard::factory()->create();

    $this->get(route('learn.show', ['exclude' => $a->id]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('flashcard.id', $b->id)
        );
});

it('treats studied state per user', function (): void {
    $card = Flashcard::factory()->create();
    $other = User::factory()->create();

    // Other user has studied the only card; current user should still see it.
    FlashcardUserProgress::factory()
        ->for($other)
        ->for($card, 'flashcard')
        ->create(['studied' => true]);

    $this->get(route('learn.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('flashcard.id', $card->id)
            ->where('stats.unstudied', 1)
        );
});

it('learn.show requires authentication', function (): void {
    auth()->logout();

    $this->get(route('learn.show'))
        ->assertRedirect(route('login'));
});
