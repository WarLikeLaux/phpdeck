<?php

namespace App\Http\Controllers;

use App\Models\Flashcard;
use App\Models\FlashcardEvent;
use App\Models\FlashcardUserProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TroubledController extends Controller
{
    private const CONTENT_FIELDS = [
        'id', 'category', 'topic', 'difficulty',
        'question', 'answer',
        'code_example', 'code_language',
        'cloze_text', 'short_answer', 'assemble_chunks',
    ];

    private const BAD_KINDS = [
        'study_incorrect',
        'matching_incorrect',
        'review_forgot',
        'skipped',
    ];

    private const WINDOW_DAYS = 30;

    private const MIN_EVENTS = 3;

    private const PER_PAGE = 20;

    public function show(Request $request): Response
    {
        $userId = (int) $request->user()->id;
        $window = now()->subDays(self::WINDOW_DAYS);

        $stats = DB::table('flashcard_events')
            ->where('user_id', $userId)
            ->where('occurred_at', '>=', $window)
            ->select(
                'flashcard_id',
                DB::raw('COUNT(*) as total'),
                DB::raw($this->badSumExpression().' as bad'),
                DB::raw($this->kindCountExpression('skipped').' as skipped'),
                DB::raw($this->kindCountExpression('study_incorrect').' as incorrect'),
                DB::raw($this->kindCountExpression('matching_incorrect').' as matching_incorrect'),
                DB::raw($this->kindCountExpression('review_forgot').' as forgot'),
                DB::raw('MAX(occurred_at) as last_seen'),
            )
            ->groupBy('flashcard_id')
            ->having('total', '>=', self::MIN_EVENTS)
            ->havingRaw($this->badSumExpression().' > 0')
            ->orderByRaw('('.$this->badSumExpression().' * 1.0 / COUNT(*)) DESC')
            ->orderByRaw($this->badSumExpression().' DESC')
            ->get();

        $totalRows = $stats->count();
        $page = max(1, (int) $request->query('page', 1));
        $perPage = self::PER_PAGE;
        $lastPage = (int) max(1, ceil($totalRows / $perPage));
        $page = min($page, $lastPage);

        $pageStats = $stats->slice(($page - 1) * $perPage, $perPage);

        $cardIds = $pageStats->pluck('flashcard_id');

        $cards = Flashcard::query()
            ->whereIn('id', $cardIds)
            ->get(self::CONTENT_FIELDS)
            ->keyBy('id');

        $progressMap = FlashcardUserProgress::query()
            ->forUser($userId)
            ->whereIn('flashcard_id', $cardIds)
            ->get()
            ->keyBy('flashcard_id');

        $rows = $pageStats
            ->map(function ($stat) use ($cards, $progressMap) {
                /** @var Flashcard|null $card */
                $card = $cards->get($stat->flashcard_id);
                if ($card === null) {
                    return null;
                }

                $total = (int) $stat->total;
                $bad = (int) $stat->bad;
                $progress = $progressMap->get($stat->flashcard_id);

                return [
                    'flashcard' => array_merge(
                        $card->only(self::CONTENT_FIELDS),
                        $progress?->asArray() ?? FlashcardUserProgress::defaults(),
                    ),
                    'metrics' => [
                        'total' => $total,
                        'bad' => $bad,
                        'incorrect' => (int) $stat->incorrect,
                        'matching_incorrect' => (int) $stat->matching_incorrect,
                        'forgot' => (int) $stat->forgot,
                        'skipped' => (int) $stat->skipped,
                        'error_rate' => $total > 0 ? round($bad / $total, 3) : 0.0,
                        'last_seen' => (string) $stat->last_seen,
                    ],
                ];
            })
            ->filter()
            ->values()
            ->all();

        return Inertia::render('troubled/index', [
            'rows' => $rows,
            'pagination' => [
                'current_page' => $page,
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'total' => $totalRows,
                'from' => $totalRows === 0 ? 0 : ($page - 1) * $perPage + 1,
                'to' => $totalRows === 0 ? 0 : min($page * $perPage, $totalRows),
            ],
            'window_days' => self::WINDOW_DAYS,
            'min_events' => self::MIN_EVENTS,
        ]);
    }

    public function clear(Request $request, Flashcard $flashcard): RedirectResponse
    {
        FlashcardEvent::query()
            ->where('user_id', $request->user()->id)
            ->where('flashcard_id', $flashcard->id)
            ->whereIn('kind', self::BAD_KINDS)
            ->delete();

        return redirect()->back();
    }

    private function badSumExpression(): string
    {
        $kinds = collect(self::BAD_KINDS)
            ->map(fn (string $k) => "'{$k}'")
            ->implode(',');

        return "SUM(CASE WHEN kind IN ({$kinds}) THEN 1 ELSE 0 END)";
    }

    private function kindCountExpression(string $kind): string
    {
        return "SUM(CASE WHEN kind = '{$kind}' THEN 1 ELSE 0 END)";
    }
}
