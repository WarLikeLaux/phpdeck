<?php

use App\Models\Flashcard;
use App\Models\FlashcardUserProgress;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

function learnedProgressFor(User $user, Flashcard $card): FlashcardUserProgress
{
    return FlashcardUserProgress::factory()
        ->for($user)
        ->for($card, 'flashcard')
        ->learned()
        ->create();
}

it('renders the index page with cards and stats', function (): void {
    $cards = Flashcard::factory()->count(3)->create();
    $learnedCard = Flashcard::factory()->create();
    learnedProgressFor($this->user, $learnedCard);

    $this->get(route('flashcards.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('flashcards/index')
            ->has('flashcards.data', 4)
            ->has('categoryStats')
            ->where('filters.q', '')
            ->where('filters.status', 'all')
            ->where('filters.category', 'all')
            ->where('stats.total', 4)
            ->where('stats.learned', 1)
            ->where('stats.due', 0)
        );
});

it('filters cards by status=due', function (): void {
    $due = Flashcard::factory()->create(['question' => 'Q1']);
    $learned = Flashcard::factory()->create(['question' => 'Q2']);
    learnedProgressFor($this->user, $learned);

    $this->get(route('flashcards.index', ['status' => 'due']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('flashcards.data', 1)
            ->where('flashcards.data.0.question', 'Q1')
            ->where('filters.status', 'due')
        );
});

it('filters cards by status=learned', function (): void {
    Flashcard::factory()->count(2)->create();
    $done = Flashcard::factory()->create(['question' => 'Done']);
    learnedProgressFor($this->user, $done);

    $this->get(route('flashcards.index', ['status' => 'learned']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('flashcards.data', 1)
            ->where('flashcards.data.0.question', 'Done')
        );
});

it('filters cards by category', function (): void {
    Flashcard::factory()->create(['category' => 'PHP']);
    Flashcard::factory()->create(['category' => 'PHP']);
    Flashcard::factory()->create(['category' => 'Laravel']);

    $this->get(route('flashcards.index', ['category' => 'PHP']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('flashcards.data', 2)
            ->where('filters.category', 'PHP')
        );
});

it('searches by question, answer, category, short_answer', function (): void {
    Flashcard::factory()->create([
        'question' => 'What is PSR-4?',
        'answer' => 'Autoloading standard.',
    ]);
    Flashcard::factory()->create([
        'question' => 'What is implode?',
        'answer' => 'Joins array values.',
        'short_answer' => 'implode',
    ]);

    $this->get(route('flashcards.index', ['q' => 'PSR']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('flashcards.data', 1));

    $this->get(route('flashcards.index', ['q' => 'implode']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('flashcards.data', 1));

    $this->get(route('flashcards.index', ['q' => 'array values']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('flashcards.data', 1));
});

it('returns category breakdown counts', function (): void {
    Flashcard::factory()->count(3)->create(['category' => 'PHP']);
    $phpLearned = Flashcard::factory()->create(['category' => 'PHP']);
    learnedProgressFor($this->user, $phpLearned);
    Flashcard::factory()->count(2)->create(['category' => 'Laravel']);

    $this->get(route('flashcards.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('categoryStats', 2)
            ->where('categoryStats.0.name', 'Laravel')
            ->where('categoryStats.0.total', 2)
            ->where('categoryStats.0.learned', 0)
            ->where('categoryStats.1.name', 'PHP')
            ->where('categoryStats.1.total', 4)
            ->where('categoryStats.1.learned', 1)
        );
});

it('redirects root to flashcards', function (): void {
    $this->get('/')->assertRedirect(route('flashcards.index'));
});

it('resets only the current users progress', function (): void {
    $other = User::factory()->create();

    $cards = Flashcard::factory()->count(3)->create();
    foreach ($cards as $card) {
        learnedProgressFor($this->user, $card);
        learnedProgressFor($other, $card);
    }

    $this->post(route('flashcards.reset'))
        ->assertRedirect(route('flashcards.index'));

    expect(FlashcardUserProgress::query()->forUser($this->user->id)->count())->toBe(0)
        ->and(FlashcardUserProgress::query()->forUser($other->id)->count())->toBe(3)
        ->and(FlashcardUserProgress::query()
            ->forUser($other->id)
            ->where('is_learned', true)
            ->count())->toBe(3);
});

it('flashcards.reset requires authentication', function (): void {
    auth()->logout();

    $this->post(route('flashcards.reset'))
        ->assertRedirect(route('login'));
});
