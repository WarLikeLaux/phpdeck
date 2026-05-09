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

class ReviewController extends Controller
{
    private const SESSION_KEY = 'review.seen';

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
        $seen = $this->seenIds($request);

        $flashcard = $this->pickCard($userId, $seen, $excludeId);

        $totalLearned = FlashcardUserProgress::query()
            ->forUser($userId)
            ->where('is_learned', true)
            ->count();
        $remaining = FlashcardUserProgress::query()
            ->forUser($userId)
            ->where('is_learned', true)
            ->whereNotIn('flashcard_id', $seen)
            ->count();

        $payload = null;
        if ($flashcard !== null) {
            $progress = FlashcardUserProgress::forCurrent($flashcard->id);
            $payload = array_merge(
                $flashcard->only(self::CONTENT_FIELDS),
                $progress->exists ? $progress->asArray() : FlashcardUserProgress::defaults(),
            );
        }

        return Inertia::render('review/index', [
            'flashcard' => $payload,
            'stats' => [
                'total' => $totalLearned,
                'seen' => count($seen),
                'remaining' => $remaining,
            ],
        ]);
    }

    public function remember(Request $request, Flashcard $flashcard): RedirectResponse
    {
        $this->markSeen($request, $flashcard->id);

        FlashcardEvent::create([
            'user_id' => $request->user()->id,
            'flashcard_id' => $flashcard->id,
            'kind' => 'review_remember',
            'occurred_at' => now(),
        ]);

        return redirect()->route('review.show');
    }

    public function forgot(Request $request, Flashcard $flashcard): RedirectResponse
    {
        $progress = FlashcardUserProgress::forCurrent($flashcard->id);
        if ($progress->exists) {
            $progress->markIncorrect();
        }
        $this->markSeen($request, $flashcard->id);

        FlashcardEvent::create([
            'user_id' => $request->user()->id,
            'flashcard_id' => $flashcard->id,
            'kind' => 'review_forgot',
            'occurred_at' => now(),
        ]);

        return redirect()->route('review.show');
    }

    public function skip(Request $request, Flashcard $flashcard): RedirectResponse
    {
        FlashcardEvent::create([
            'user_id' => $request->user()->id,
            'flashcard_id' => $flashcard->id,
            'kind' => 'skipped',
            'occurred_at' => now(),
        ]);

        return redirect()->route('review.show', ['exclude' => $flashcard->id]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->session()->forget(self::SESSION_KEY);

        return redirect()->route('review.show');
    }

    /**
     * @param  array<int, int>  $seen
     */
    private function pickCard(int $userId, array $seen, ?int $excludeId): ?Flashcard
    {
        $build = function () use ($userId, $seen): Builder {
            return Flashcard::query()
                ->whereHas('progress', fn ($p) => $p
                    ->where('user_id', $userId)
                    ->where('is_learned', true))
                ->whereNotIn('id', $seen);
        };

        if ($excludeId !== null) {
            $card = $build()->where('id', '!=', $excludeId)->inRandomOrder()->first();
            if ($card !== null) {
                return $card;
            }
        }

        return $build()->inRandomOrder()->first();
    }

    /**
     * @return array<int, int>
     */
    private function seenIds(Request $request): array
    {
        $raw = $request->session()->get(self::SESSION_KEY, []);

        return array_values(array_map('intval', (array) $raw));
    }

    private function markSeen(Request $request, int $id): void
    {
        $seen = $this->seenIds($request);
        if (! in_array($id, $seen, true)) {
            $seen[] = $id;
        }
        $request->session()->put(self::SESSION_KEY, $seen);
    }
}
