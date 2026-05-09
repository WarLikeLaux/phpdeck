<?php

namespace App\Http\Controllers;

use App\Models\Flashcard;
use App\Models\FlashcardUserProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FlashcardController extends Controller
{
    private const CONTENT_FIELDS = [
        'id', 'category', 'topic', 'difficulty',
        'question', 'answer',
        'code_example', 'code_language',
        'cloze_text', 'short_answer', 'assemble_chunks',
    ];

    public function index(Request $request): Response
    {
        $userId = (int) $request->user()->id;
        $q = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', 'all');
        $category = (string) $request->query('category', 'all');

        $query = Flashcard::query();

        if ($q !== '') {
            $like = '%'.$q.'%';
            $query->where(function ($w) use ($like) {
                $w->where('question', 'like', $like)
                    ->orWhere('answer', 'like', $like)
                    ->orWhere('category', 'like', $like)
                    ->orWhere('short_answer', 'like', $like);
            });
        }

        if ($status === 'due') {
            $query->whereDoesntHave('progress', fn ($p) => $p
                ->where('user_id', $userId)
                ->where('is_learned', true));
        } elseif ($status === 'learned') {
            $query->whereHas('progress', fn ($p) => $p
                ->where('user_id', $userId)
                ->where('is_learned', true));
        }

        if ($category !== 'all' && $category !== '') {
            $query->where('category', $category);
        }

        $paginator = $query
            ->orderBy('difficulty')
            ->orderBy('id')
            ->paginate(24, self::CONTENT_FIELDS)
            ->withQueryString();

        $progressMap = FlashcardUserProgress::query()
            ->forUser($userId)
            ->whereIn('flashcard_id', collect($paginator->items())->pluck('id'))
            ->get()
            ->keyBy('flashcard_id');

        $paginator->setCollection(
            $paginator->getCollection()->map(function (Flashcard $card) use ($progressMap) {
                $progress = $progressMap->get($card->id);

                return array_merge(
                    $card->only(self::CONTENT_FIELDS),
                    $progress?->asArray() ?? FlashcardUserProgress::defaults(),
                );
            })
        );

        return Inertia::render('flashcards/index', [
            'flashcards' => $paginator,
            'stats' => $this->stats($userId),
            'categoryStats' => $this->categoryStats($userId),
            'filters' => [
                'q' => $q,
                'status' => in_array($status, ['all', 'due', 'learned'], true) ? $status : 'all',
                'category' => $category,
            ],
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        FlashcardUserProgress::query()
            ->forUser((int) $request->user()->id)
            ->delete();

        return redirect()->route('flashcards.index');
    }

    /**
     * @return array{total: int, due: int, learned: int}
     */
    private function stats(int $userId): array
    {
        return [
            'total' => Flashcard::query()->count(),
            'learned' => FlashcardUserProgress::query()
                ->forUser($userId)
                ->where('is_learned', true)
                ->count(),
            'due' => FlashcardUserProgress::query()
                ->forUser($userId)
                ->due()
                ->count(),
        ];
    }

    /**
     * @return array<int, array{name: string, total: int, learned: int}>
     */
    private function categoryStats(int $userId): array
    {
        return Flashcard::query()
            ->leftJoin('flashcard_user_progress as p', function ($join) use ($userId) {
                $join->on('p.flashcard_id', '=', 'flashcards.id')
                    ->where('p.user_id', $userId);
            })
            ->whereNotNull('flashcards.category')
            ->select('flashcards.category')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN p.is_learned = 1 THEN 1 ELSE 0 END) as learned')
            ->groupBy('flashcards.category')
            ->orderBy('flashcards.category')
            ->get()
            ->map(fn ($r) => [
                'name' => (string) $r->category,
                'total' => (int) $r->total,
                'learned' => (int) $r->learned,
            ])
            ->all();
    }
}
