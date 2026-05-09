<?php

namespace App\Http\Controllers;

use App\Models\Flashcard;
use App\Models\FlashcardEvent;
use App\Models\FlashcardUserProgress;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LearnController extends Controller
{
    private const CONTENT_FIELDS = [
        'id', 'category', 'topic', 'difficulty',
        'question', 'answer',
        'code_example', 'code_language',
        'cloze_text', 'short_answer', 'assemble_chunks',
    ];

    public function show(Request $request): Response
    {
        $userId = (int) $request->user()->id;
        $excludeId = $request->integer('exclude') ?: null;
        $category = trim((string) $request->query('category', ''));
        $topic = trim((string) $request->query('topic', ''));

        $flashcard = $this->pickCard($userId, $excludeId, $category, $topic);

        $payload = null;
        if ($flashcard !== null) {
            $progress = FlashcardUserProgress::forCurrent($flashcard->id);
            $payload = array_merge(
                $flashcard->only(self::CONTENT_FIELDS),
                $progress->exists ? $progress->asArray() : FlashcardUserProgress::defaults(),
            );
        }

        return Inertia::render('learn/index', [
            'flashcard' => $payload,
            'stats' => $this->stats($userId, $category, $topic),
            'categories' => $this->categories(),
            'filters' => [
                'category' => $category === '' ? 'all' : $category,
                'topic' => $topic === '' ? null : $topic,
            ],
        ]);
    }

    public function studied(Request $request, Flashcard $flashcard): RedirectResponse
    {
        $progress = FlashcardUserProgress::forCurrent($flashcard->id);
        $progress->markStudied();

        FlashcardEvent::create([
            'user_id' => $request->user()->id,
            'flashcard_id' => $flashcard->id,
            'kind' => 'studied',
            'occurred_at' => now(),
        ]);

        return redirect()->route('learn.show', $this->forwardFilters($request, $flashcard));
    }

    public function skip(Request $request, Flashcard $flashcard): RedirectResponse
    {
        FlashcardEvent::create([
            'user_id' => $request->user()->id,
            'flashcard_id' => $flashcard->id,
            'kind' => 'skipped',
            'occurred_at' => now(),
        ]);

        return redirect()->route('learn.show', $this->forwardFilters($request, $flashcard));
    }

    /**
     * @return array<string, string|int>
     */
    private function forwardFilters(Request $request, Flashcard $flashcard): array
    {
        return array_filter([
            'exclude' => $flashcard->id,
            'category' => $request->input('category'),
            'topic' => $request->input('topic'),
        ]);
    }

    private function pickCard(int $userId, ?int $excludeId, string $category, string $topic): ?Flashcard
    {
        $build = function () use ($userId, $excludeId, $category, $topic): Builder {
            $q = Flashcard::query()
                ->whereDoesntHave('progress', fn ($p) => $p
                    ->where('user_id', $userId)
                    ->where('studied', true));
            if ($excludeId !== null) {
                $q->where('id', '!=', $excludeId);
            }
            if ($category !== '') {
                $q->where('category', $category);
            }
            if ($topic !== '') {
                $q->where('topic', $topic);
            }

            return $q;
        };

        $minDifficulty = $build()->min('difficulty');

        if ($minDifficulty === null) {
            return $excludeId !== null ? $this->pickCard($userId, null, $category, $topic) : null;
        }

        return $build()
            ->where('difficulty', $minDifficulty)
            ->inRandomOrder()
            ->first();
    }

    /**
     * @return array{total: int, unstudied: int, studied: int, learned: int}
     */
    private function stats(int $userId, string $category, string $topic): array
    {
        $base = function () use ($category, $topic): Builder {
            $q = Flashcard::query();
            if ($category !== '') {
                $q->where('category', $category);
            }
            if ($topic !== '') {
                $q->where('topic', $topic);
            }

            return $q;
        };

        $studied = (clone $base())
            ->whereHas('progress', fn ($p) => $p
                ->where('user_id', $userId)
                ->where('studied', true))
            ->count();

        $learned = (clone $base())
            ->whereHas('progress', fn ($p) => $p
                ->where('user_id', $userId)
                ->where('is_learned', true))
            ->count();

        $total = $base()->count();

        return [
            'total' => $total,
            'unstudied' => $total - $studied,
            'studied' => $studied,
            'learned' => $learned,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function categories(): array
    {
        return Flashcard::query()
            ->whereNotNull('category')
            ->select('category')
            ->groupBy('category')
            ->orderBy('category')
            ->pluck('category')
            ->all();
    }
}
