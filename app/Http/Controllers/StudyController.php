<?php

namespace App\Http\Controllers;

use App\Models\Flashcard;
use App\Models\FlashcardEvent;
use App\Models\FlashcardUserProgress;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class StudyController extends Controller
{
    private const CONTENT_FIELDS = [
        'id', 'category', 'topic', 'difficulty',
        'question', 'answer',
        'code_example', 'code_language',
        'cloze_text', 'short_answer', 'assemble_chunks',
    ];

    private const MODES = [
        'reveal', 'true_false', 'multiple_choice',
        'cloze', 'type_in', 'assemble', 'matching',
    ];

    public function show(Request $request): Response
    {
        $userId = (int) $request->user()->id;
        $excludeId = $request->integer('exclude') ?: null;

        $matching = $this->buildMatching($userId);

        if ($matching !== null && random_int(1, 5) === 1) {
            return Inertia::render('study/index', [
                'mode' => 'matching',
                'flashcard' => null,
                'shown' => null,
                'options' => null,
                'assemble' => null,
                'matching' => $matching,
                'stats' => $this->stats($userId),
            ]);
        }

        $flashcard = $this->pickDueCard($userId, $excludeId);

        if ($flashcard === null) {
            return Inertia::render('study/index', [
                'mode' => null,
                'flashcard' => null,
                'shown' => null,
                'options' => null,
                'assemble' => null,
                'matching' => null,
                'stats' => $this->stats($userId),
            ]);
        }

        $progress = FlashcardUserProgress::forCurrent($flashcard->id);
        $modes = $this->availableModes($flashcard);
        $mode = $this->pickMode($progress, $modes);

        return Inertia::render('study/index', [
            'mode' => $mode,
            'flashcard' => array_merge(
                $flashcard->only(self::CONTENT_FIELDS),
                $progress->exists ? $progress->asArray() : FlashcardUserProgress::defaults(),
            ),
            'shown' => $mode === 'true_false' ? $this->trueFalseAnswer($flashcard) : null,
            'options' => $mode === 'multiple_choice' ? $this->multipleChoiceOptions($flashcard) : null,
            'assemble' => $mode === 'assemble' ? $this->assemblePool($flashcard) : null,
            'matching' => null,
            'stats' => $this->stats($userId),
        ]);
    }

    public function skip(Request $request, Flashcard $flashcard): RedirectResponse
    {
        FlashcardEvent::create([
            'user_id' => $request->user()->id,
            'flashcard_id' => $flashcard->id,
            'kind' => 'skipped',
            'occurred_at' => now(),
        ]);

        return redirect()->route('study.show', ['exclude' => $flashcard->id]);
    }

    public function answer(Request $request, Flashcard $flashcard): RedirectResponse
    {
        $data = $request->validate([
            'result' => ['required', Rule::in(['correct', 'incorrect'])],
            'mode' => ['nullable', 'string', Rule::in(self::MODES)],
        ]);

        $progress = FlashcardUserProgress::forCurrent($flashcard->id);

        $data['result'] === 'correct'
            ? $progress->markCorrect($data['mode'] ?? null)
            : $progress->markIncorrect();

        FlashcardEvent::create([
            'user_id' => $request->user()->id,
            'flashcard_id' => $flashcard->id,
            'kind' => $data['result'] === 'correct' ? 'study_correct' : 'study_incorrect',
            'mode' => $data['mode'] ?? null,
            'occurred_at' => now(),
        ]);

        return redirect()->route('study.show');
    }

    public function matching(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'pairs' => ['required', 'array', 'min:1', 'max:20'],
            'pairs.*.question_id' => ['required', 'integer', 'exists:flashcards,id'],
            'pairs.*.answer_id' => ['required', 'integer', 'exists:flashcards,id'],
        ]);

        $userId = (int) $request->user()->id;

        foreach ($data['pairs'] as $pair) {
            $card = Flashcard::query()->find($pair['question_id']);
            if ($card === null) {
                continue;
            }

            $progress = FlashcardUserProgress::forCurrent($card->id);

            $pair['question_id'] === $pair['answer_id']
                ? $progress->markCorrect('matching')
                : $progress->markIncorrect();

            FlashcardEvent::create([
                'user_id' => $userId,
                'flashcard_id' => $card->id,
                'kind' => $pair['question_id'] === $pair['answer_id']
                    ? 'matching_correct'
                    : 'matching_incorrect',
                'occurred_at' => now(),
            ]);
        }

        return redirect()->route('study.show');
    }

    private function pickDueCard(int $userId, ?int $excludeId = null): ?Flashcard
    {
        $build = function () use ($userId, $excludeId): Builder {
            $q = Flashcard::query()->whereHas('progress', fn ($p) => $p
                ->where('user_id', $userId)
                ->where('studied', true)
                ->where(function ($q2) {
                    $q2->where('is_learned', false)
                        ->orWhere(function ($q3) {
                            $q3->where('is_learned', true)
                                ->whereNotNull('next_review_at')
                                ->where('next_review_at', '<=', now());
                        });
                }));
            if ($excludeId !== null) {
                $q->where('id', '!=', $excludeId);
            }

            return $q;
        };

        $minDifficulty = $build()->min('difficulty');

        if ($minDifficulty === null) {
            return $excludeId !== null ? $this->pickDueCard($userId, null) : null;
        }

        return $build()
            ->where('difficulty', $minDifficulty)
            ->inRandomOrder()
            ->first();
    }

    /**
     * @param  array<int, string>  $modes
     */
    private function pickMode(FlashcardUserProgress $progress, array $modes): string
    {
        $taken = (array) ($progress->correct_modes ?? []);
        $remaining = array_values(array_diff($modes, $taken));

        $pool = $remaining !== [] ? $remaining : $modes;

        return $pool[array_rand($pool)];
    }

    /**
     * @return array<int, string>
     */
    private function availableModes(Flashcard $card): array
    {
        $modes = ['reveal'];

        $categoryCount = Flashcard::query()
            ->where('id', '!=', $card->id)
            ->where('category', $card->category)
            ->count();

        if ($categoryCount >= 1) {
            $modes[] = 'true_false';
        }

        if ($categoryCount >= 3) {
            $modes[] = 'multiple_choice';
        }

        if ($card->cloze_text !== null && preg_match('/\{\{(.+?)\}\}/', $card->cloze_text) === 1) {
            $modes[] = 'cloze';
        }

        if (filled($card->short_answer)) {
            $modes[] = 'type_in';
        }

        if (is_array($card->assemble_chunks) && count($card->assemble_chunks) >= 2) {
            $modes[] = 'assemble';
        }

        return $modes;
    }

    /**
     * @return array{answer: string, is_correct: bool}
     */
    private function trueFalseAnswer(Flashcard $card): array
    {
        $showReal = random_int(0, 1) === 0;

        if ($showReal) {
            return ['answer' => $card->answer, 'is_correct' => true];
        }

        $distractor = $this->neighborQuery($card)->inRandomOrder()->first();

        if ($distractor === null && $card->topic !== null) {
            $distractor = Flashcard::query()
                ->where('id', '!=', $card->id)
                ->where('category', $card->category)
                ->inRandomOrder()
                ->first();
        }

        if ($distractor === null) {
            return ['answer' => $card->answer, 'is_correct' => true];
        }

        return ['answer' => $distractor->answer, 'is_correct' => false];
    }

    /**
     * @return array<int, array{id: int, answer: string, is_correct: bool}>
     */
    private function multipleChoiceOptions(Flashcard $card): array
    {
        $distractors = $this->neighborQuery($card)
            ->inRandomOrder()
            ->limit(3)
            ->get();

        if ($distractors->count() < 3 && $card->topic !== null) {
            $needed = 3 - $distractors->count();
            $extra = Flashcard::query()
                ->where('id', '!=', $card->id)
                ->where('category', $card->category)
                ->whereNotIn('id', $distractors->pluck('id'))
                ->inRandomOrder()
                ->limit($needed)
                ->get();
            $distractors = $distractors->concat($extra);
        }

        return $distractors
            ->map(fn (Flashcard $c) => [
                'id' => $c->id,
                'answer' => $c->answer,
                'is_correct' => false,
            ])
            ->push([
                'id' => $card->id,
                'answer' => $card->answer,
                'is_correct' => true,
            ])
            ->shuffle()
            ->values()
            ->toArray();
    }

    /**
     * @return array{pool: array<int, string>}
     */
    private function assemblePool(Flashcard $card): array
    {
        /** @var array<int, string> $correct */
        $correct = (array) $card->assemble_chunks;

        $distractors = $this->neighborQuery($card)
            ->whereNotNull('assemble_chunks')
            ->inRandomOrder()
            ->limit(5)
            ->get();

        if ($distractors->isEmpty() && $card->topic !== null) {
            $distractors = Flashcard::query()
                ->where('id', '!=', $card->id)
                ->where('category', $card->category)
                ->whereNotNull('assemble_chunks')
                ->inRandomOrder()
                ->limit(5)
                ->get();
        }

        $chunks = $distractors
            ->flatMap(fn (Flashcard $c) => (array) $c->assemble_chunks)
            ->reject(fn (string $chunk) => in_array($chunk, $correct, true))
            ->unique()
            ->shuffle()
            ->take(2)
            ->values()
            ->all();

        $pool = collect([...$correct, ...$chunks])
            ->shuffle()
            ->values()
            ->all();

        return ['pool' => $pool];
    }

    private function neighborQuery(Flashcard $card): Builder
    {
        $query = Flashcard::query()->where('id', '!=', $card->id);

        return $card->topic !== null
            ? $query->where('topic', $card->topic)
            : $query->where('category', $card->category);
    }

    /**
     * @return array{
     *     category: string,
     *     questions: array<int, array{id: int, text: string}>,
     *     answers: array<int, array{id: int, text: string}>
     * }|null
     */
    private function buildMatching(int $userId): ?array
    {
        $dueScope = fn ($p) => $p
            ->where('user_id', $userId)
            ->where('studied', true)
            ->where(function ($q2) {
                $q2->where('is_learned', false)
                    ->orWhere(function ($q3) {
                        $q3->where('is_learned', true)
                            ->whereNotNull('next_review_at')
                            ->where('next_review_at', '<=', now());
                    });
            });

        $topic = Flashcard::query()
            ->whereHas('progress', $dueScope)
            ->whereNotNull('short_answer')
            ->whereNotNull('topic')
            ->groupBy('topic')
            ->havingRaw('COUNT(*) >= 4')
            ->inRandomOrder()
            ->value('topic');

        if ($topic !== null) {
            $cards = Flashcard::query()
                ->whereHas('progress', $dueScope)
                ->whereNotNull('short_answer')
                ->where('topic', $topic)
                ->inRandomOrder()
                ->limit(4)
                ->get(['id', 'category', 'question', 'short_answer']);
        } else {
            $category = Flashcard::query()
                ->whereHas('progress', $dueScope)
                ->whereNotNull('short_answer')
                ->groupBy('category')
                ->havingRaw('COUNT(*) >= 4')
                ->inRandomOrder()
                ->value('category');

            if ($category === null) {
                return null;
            }

            $cards = Flashcard::query()
                ->whereHas('progress', $dueScope)
                ->whereNotNull('short_answer')
                ->where('category', $category)
                ->inRandomOrder()
                ->limit(4)
                ->get(['id', 'category', 'question', 'short_answer']);
        }

        return [
            'category' => (string) $cards->first()?->category,
            'questions' => $cards
                ->map(fn (Flashcard $c) => ['id' => $c->id, 'text' => $c->question])
                ->values()
                ->all(),
            'answers' => $cards
                ->shuffle()
                ->map(fn (Flashcard $c) => ['id' => $c->id, 'text' => (string) $c->short_answer])
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array{total: int, due: int, learned: int}
     */
    private function stats(int $userId): array
    {
        return [
            'total' => Flashcard::query()->count(),
            'due' => FlashcardUserProgress::query()
                ->forUser($userId)
                ->due()
                ->count(),
            'learned' => FlashcardUserProgress::query()
                ->forUser($userId)
                ->where('is_learned', true)
                ->count(),
        ];
    }
}
