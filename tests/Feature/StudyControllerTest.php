<?php

use App\Models\Flashcard;
use App\Models\FlashcardUserProgress;
use App\Models\User;
use Illuminate\Testing\TestResponse;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

function studyMode(TestResponse $response): ?string
{
    $content = $response->getOriginalContent();
    $data = is_object($content) && method_exists($content, 'getData') ? $content->getData() : [];

    return $data['page']['props']['mode'] ?? null;
}

/**
 * Create a card and ensure the current user has it studied (so it shows up
 * as "due for study" in StudyController). Returns the Flashcard.
 *
 * @param  array<string, mixed>  $cardAttrs
 */
function studyCardForUser(User $user, array $cardAttrs = []): Flashcard
{
    $card = Flashcard::factory()->create($cardAttrs);
    FlashcardUserProgress::factory()
        ->for($user)
        ->for($card, 'flashcard')
        ->create(['studied' => true, 'is_learned' => false]);

    return $card;
}

it('shows a due card with stats and a study mode', function (): void {
    studyCardForUser($this->user);
    studyCardForUser($this->user);

    $learned = Flashcard::factory()->create();
    FlashcardUserProgress::factory()
        ->for($this->user)
        ->for($learned, 'flashcard')
        ->learned()
        ->create();

    $response = $this->get(route('study.show'));

    $response
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('study/index')
            ->has('flashcard')
            ->has('mode')
            ->where('stats.total', 3)
            ->where('stats.due', 2)
            ->where('stats.learned', 1)
        );

    expect(studyMode($response))->toBeIn(['reveal', 'true_false', 'multiple_choice', 'matching']);
});

it('shows null flashcard when nothing is due', function (): void {
    $card = Flashcard::factory()->create();
    FlashcardUserProgress::factory()
        ->for($this->user)
        ->for($card, 'flashcard')
        ->learned()
        ->create();

    $this->get(route('study.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('flashcard', null)
            ->where('mode', null)
        );
});

it('falls back to reveal when no other cards exist in the category', function (): void {
    studyCardForUser($this->user, ['category' => 'Solo']);

    $this->get(route('study.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('mode', 'reveal')
            ->where('shown', null)
            ->where('options', null)
        );
});

it('builds multiple_choice options including the right answer', function (): void {
    for ($i = 0; $i < 7; $i++) {
        studyCardForUser($this->user, ['category' => 'PHP']);
    }

    $modes = collect();
    for ($i = 0; $i < 60; $i++) {
        $response = $this->get(route('study.show'));
        $modes->push(studyMode($response));
    }

    expect($modes->unique()->values()->all())
        ->toContain('multiple_choice');
});

it('enables multiple_choice when topic is tiny but category has neighbors', function (): void {
    studyCardForUser($this->user, [
        'category' => 'PHP',
        'topic' => 'php.tiny',
        'question' => 'Q',
    ]);
    studyCardForUser($this->user, [
        'category' => 'PHP',
        'topic' => 'php.tiny',
    ]);
    for ($i = 0; $i < 5; $i++) {
        studyCardForUser($this->user, [
            'category' => 'PHP',
            'topic' => 'php.other',
        ]);
    }

    $modes = collect();
    for ($i = 0; $i < 80; $i++) {
        $modes->push(studyMode($this->get(route('study.show'))));
    }

    expect($modes->unique()->values()->all())->toContain('multiple_choice');
});

it('marks a card correct via the answer endpoint after three distinct modes', function (): void {
    $card = Flashcard::factory()->create();

    $this->post(route('study.answer', $card), ['result' => 'correct', 'mode' => 'reveal'])
        ->assertRedirect(route('study.show'));
    $this->post(route('study.answer', $card), ['result' => 'correct', 'mode' => 'true_false']);
    $this->post(route('study.answer', $card), ['result' => 'correct', 'mode' => 'multiple_choice']);

    $progress = FlashcardUserProgress::query()
        ->forUser($this->user->id)
        ->where('flashcard_id', $card->id)
        ->first();

    expect($progress)->not->toBeNull()
        ->and($progress->is_learned)->toBeTrue();
});

it('does not mark a card learned when same mode repeats', function (): void {
    $card = Flashcard::factory()->create();

    $this->post(route('study.answer', $card), ['result' => 'correct', 'mode' => 'reveal']);
    $this->post(route('study.answer', $card), ['result' => 'correct', 'mode' => 'reveal']);
    $this->post(route('study.answer', $card), ['result' => 'correct', 'mode' => 'reveal']);

    $progress = FlashcardUserProgress::query()
        ->forUser($this->user->id)
        ->where('flashcard_id', $card->id)
        ->first();

    expect($progress?->is_learned)->toBeFalse();
});

it('marks a card incorrect and clears correct_modes', function (): void {
    $card = Flashcard::factory()->create();

    $this->post(route('study.answer', $card), ['result' => 'correct', 'mode' => 'reveal']);
    $this->post(route('study.answer', $card), ['result' => 'incorrect', 'mode' => 'true_false'])
        ->assertRedirect(route('study.show'));

    $progress = FlashcardUserProgress::query()
        ->forUser($this->user->id)
        ->where('flashcard_id', $card->id)
        ->first();

    expect($progress?->is_learned)->toBeFalse()
        ->and($progress?->correct_modes)->toBe([]);
});

it('rejects an unknown result value', function (): void {
    $card = Flashcard::factory()->create();

    $this->post(route('study.answer', $card), ['result' => 'maybe'])
        ->assertSessionHasErrors('result');
});

it('exposes cloze mode when cloze_text is set', function (): void {
    $card = Flashcard::factory()->withCloze()->create(['category' => 'PHP']);
    FlashcardUserProgress::factory()
        ->for($this->user)
        ->for($card, 'flashcard')
        ->create(['studied' => true, 'is_learned' => false]);

    $modes = collect();
    for ($i = 0; $i < 30; $i++) {
        $modes->push(studyMode($this->get(route('study.show'))));
    }

    expect($modes->unique()->all())->toContain('cloze');
});

it('exposes type_in mode when short_answer is set', function (): void {
    $card = Flashcard::factory()->withShortAnswer()->create(['category' => 'PHP']);
    FlashcardUserProgress::factory()
        ->for($this->user)
        ->for($card, 'flashcard')
        ->create(['studied' => true, 'is_learned' => false]);

    $modes = collect();
    for ($i = 0; $i < 30; $i++) {
        $modes->push(studyMode($this->get(route('study.show'))));
    }

    expect($modes->unique()->all())->toContain('type_in');
});

it('exposes assemble mode with a shuffled pool when assemble_chunks is set', function (): void {
    $card = Flashcard::factory()->withAssemble()->create(['category' => 'PHP']);
    FlashcardUserProgress::factory()
        ->for($this->user)
        ->for($card, 'flashcard')
        ->create(['studied' => true, 'is_learned' => false]);

    for ($i = 0; $i < 40; $i++) {
        $response = $this->get(route('study.show'));
        if (studyMode($response) === 'assemble') {
            $props = $response->getOriginalContent()->getData()['page']['props'];
            expect($props['assemble']['pool'])
                ->toBeArray()
                ->and(count($props['assemble']['pool']))->toBeGreaterThanOrEqual(2);

            return;
        }
    }
    $this->fail('assemble mode was never selected over 40 trials');
});

it('builds a matching payload when 4+ cards in a category have short_answer', function (): void {
    $cards = Flashcard::factory()->count(4)->withShortAnswer()->create(['category' => 'Match']);
    foreach ($cards as $card) {
        FlashcardUserProgress::factory()
            ->for($this->user)
            ->for($card, 'flashcard')
            ->create(['studied' => true, 'is_learned' => false]);
    }

    $matched = false;
    for ($i = 0; $i < 60; $i++) {
        $response = $this->get(route('study.show'));
        if (studyMode($response) === 'matching') {
            $props = $response->getOriginalContent()->getData()['page']['props'];
            expect($props['matching']['questions'])->toHaveCount(4)
                ->and($props['matching']['answers'])->toHaveCount(4)
                ->and($props['matching']['category'])->toBe('Match');
            $matched = true;
            break;
        }
    }
    expect($matched)->toBeTrue();
});

it('records matching answers correctly per pair', function (): void {
    $a = Flashcard::factory()->create();
    $b = Flashcard::factory()->create();

    $this->post(route('study.matching'), [
        'pairs' => [
            ['question_id' => $a->id, 'answer_id' => $a->id],
            ['question_id' => $b->id, 'answer_id' => $a->id],
        ],
    ])->assertRedirect(route('study.show'));

    $progressA = FlashcardUserProgress::query()
        ->forUser($this->user->id)
        ->where('flashcard_id', $a->id)
        ->first();
    $progressB = FlashcardUserProgress::query()
        ->forUser($this->user->id)
        ->where('flashcard_id', $b->id)
        ->first();

    expect($progressA?->correct_modes)->toBe(['matching'])
        ->and($progressA?->is_learned)->toBeFalse()
        ->and($progressB?->correct_modes)->toBe([])
        ->and($progressB?->is_learned)->toBeFalse();
});

it('rejects matching payload without pairs', function (): void {
    $this->post(route('study.matching'), [])
        ->assertSessionHasErrors('pairs');
});

it('study answer scopes progress to current user', function (): void {
    $card = Flashcard::factory()->create();
    $other = User::factory()->create();

    // pre-existing progress for other user — must NOT be touched
    $otherProgress = FlashcardUserProgress::factory()
        ->for($other)
        ->for($card, 'flashcard')
        ->learned()
        ->create();

    $this->post(route('study.answer', $card), ['result' => 'correct', 'mode' => 'reveal']);

    $mine = FlashcardUserProgress::query()
        ->forUser($this->user->id)
        ->where('flashcard_id', $card->id)
        ->first();
    $theirs = $otherProgress->fresh();

    expect($mine?->correct_modes)->toBe(['reveal'])
        ->and($theirs->is_learned)->toBeTrue()
        ->and($theirs->correct_modes)->toBe(['reveal', 'type_in', 'multiple_choice']);
});
