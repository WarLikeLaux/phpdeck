<?php

use App\Models\Flashcard;
use App\Models\FlashcardEvent;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

function logStatsEvent(int $userId, Flashcard $card, string $kind, ?string $when = null, ?string $mode = null): void
{
    FlashcardEvent::create([
        'user_id' => $userId,
        'flashcard_id' => $card->id,
        'kind' => $kind,
        'mode' => $mode,
        'occurred_at' => $when ?? now(),
    ]);
}

it('renders the stats page with zero stats', function (): void {
    $this->get(route('stats.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('stats/index')
            ->where('streak', 0)
            ->where('today.studied', 0)
            ->where('today.correct', 0)
            ->where('today.incorrect', 0)
            ->where('today.remembered', 0)
            ->where('today.forgot', 0)
            ->where('totals.total', 0)
            ->where('totals.due_now', 0)
            ->has('daily', 14)
            ->has('categories', 0)
            ->has('weak_topics', 0)
        );
});

it("counts today's events correctly", function (): void {
    $card = Flashcard::factory()->create();
    $userId = $this->user->id;

    logStatsEvent($userId, $card, 'studied');
    logStatsEvent($userId, $card, 'study_correct', mode: 'reveal');
    logStatsEvent($userId, $card, 'study_correct', mode: 'true_false');
    logStatsEvent($userId, $card, 'study_incorrect', mode: 'cloze');
    logStatsEvent($userId, $card, 'matching_correct');
    logStatsEvent($userId, $card, 'review_remember');
    logStatsEvent($userId, $card, 'review_forgot');
    // Old event — must not be counted in today.
    logStatsEvent($userId, $card, 'studied', now()->subDays(5)->toDateTimeString());

    $this->get(route('stats.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('today.studied', 1)
            ->where('today.correct', 3)
            ->where('today.incorrect', 1)
            ->where('today.remembered', 1)
            ->where('today.forgot', 1)
        );
});

it('builds 14-day daily array even when most days are empty', function (): void {
    $card = Flashcard::factory()->create();
    $userId = $this->user->id;

    logStatsEvent($userId, $card, 'studied');
    logStatsEvent($userId, $card, 'study_correct', now()->subDays(3)->toDateTimeString(), 'reveal');

    $this->get(route('stats.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('daily', 14)
            ->where('daily.13.date', now()->toDateString())
            ->where('daily.13.studied', 1)
            ->where('daily.10.date', now()->subDays(3)->toDateString())
            ->where('daily.10.correct', 1)
            ->where('daily.0.date', now()->subDays(13)->toDateString())
            ->where('daily.0.studied', 0)
            ->where('daily.0.correct', 0)
        );
});

it('counts streak as consecutive days from today backward', function (): void {
    $card = Flashcard::factory()->create();
    $userId = $this->user->id;

    foreach ([0, 1, 2] as $offset) {
        logStatsEvent($userId, $card, 'studied', now()->subDays($offset)->toDateTimeString());
    }

    // gap on day 3, then activity on day 4
    logStatsEvent($userId, $card, 'studied', now()->subDays(4)->toDateTimeString());

    $this->get(route('stats.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('streak', 3));
});

it('streak counts from yesterday when today has no events', function (): void {
    $card = Flashcard::factory()->create();
    $userId = $this->user->id;

    logStatsEvent($userId, $card, 'studied', now()->subDay()->toDateTimeString());
    logStatsEvent($userId, $card, 'studied', now()->subDays(2)->toDateTimeString());

    $this->get(route('stats.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('streak', 2));
});

it('streak is zero when latest activity was 2+ days ago', function (): void {
    $card = Flashcard::factory()->create();
    $userId = $this->user->id;

    logStatsEvent($userId, $card, 'studied', now()->subDays(3)->toDateTimeString());

    $this->get(route('stats.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('streak', 0));
});

it('ranks weak topics by error rate', function (): void {
    $easy = Flashcard::factory()->create(['topic' => 'php.arrays']);
    $hard = Flashcard::factory()->create(['topic' => 'php.regex']);
    $mid = Flashcard::factory()->create(['topic' => 'php.oop']);
    $userId = $this->user->id;

    // Easy topic: 1 incorrect / 5 events = 20%
    logStatsEvent($userId, $easy, 'study_correct', mode: 'reveal');
    logStatsEvent($userId, $easy, 'study_correct', mode: 'reveal');
    logStatsEvent($userId, $easy, 'study_correct', mode: 'reveal');
    logStatsEvent($userId, $easy, 'study_correct', mode: 'reveal');
    logStatsEvent($userId, $easy, 'study_incorrect', mode: 'reveal');

    // Hard topic: 3 incorrect / 4 events = 75%
    logStatsEvent($userId, $hard, 'study_correct', mode: 'reveal');
    logStatsEvent($userId, $hard, 'study_incorrect', mode: 'reveal');
    logStatsEvent($userId, $hard, 'study_incorrect', mode: 'reveal');
    logStatsEvent($userId, $hard, 'study_incorrect', mode: 'reveal');

    // Mid topic: 1 incorrect / 3 = 33%
    logStatsEvent($userId, $mid, 'study_correct', mode: 'reveal');
    logStatsEvent($userId, $mid, 'study_correct', mode: 'reveal');
    logStatsEvent($userId, $mid, 'review_forgot');

    // Below threshold: 1 event only — must be excluded.
    $rare = Flashcard::factory()->create(['topic' => 'php.cloze']);
    logStatsEvent($userId, $rare, 'study_incorrect', mode: 'reveal');

    $this->get(route('stats.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('weak_topics', 3)
            ->where('weak_topics.0.topic', 'php.regex')
            ->where('weak_topics.0.error_rate', 0.75)
            ->where('weak_topics.1.topic', 'php.oop')
            ->where('weak_topics.2.topic', 'php.arrays')
        );
});

it('computes per-category accuracy over study events', function (): void {
    $php = Flashcard::factory()->create(['category' => 'PHP']);
    $sql = Flashcard::factory()->create(['category' => 'Database']);
    $userId = $this->user->id;

    logStatsEvent($userId, $php, 'study_correct', mode: 'reveal');
    logStatsEvent($userId, $php, 'study_correct', mode: 'reveal');
    logStatsEvent($userId, $php, 'study_incorrect', mode: 'reveal');

    // matching events should NOT influence category accuracy
    logStatsEvent($userId, $sql, 'matching_correct');

    $this->get(route('stats.show'))
        ->assertOk()
        ->assertInertia(function ($page) {
            $page->has('categories', 2);
            $page->where('categories.0.name', 'Database');
            $page->where('categories.0.accuracy', null);
            $page->where('categories.1.name', 'PHP');
            $page->where('categories.1.accuracy', round(2 / 3, 4));

            return $page;
        });
});

it('isolates stats per user', function (): void {
    $card = Flashcard::factory()->create(['topic' => 'php.regex']);
    $other = User::factory()->create();

    // Other user logs lots of events — must be ignored for current user's stats.
    foreach (range(1, 5) as $_) {
        logStatsEvent($other->id, $card, 'studied');
    }

    $this->get(route('stats.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('streak', 0)
            ->where('today.studied', 0)
        );
});
