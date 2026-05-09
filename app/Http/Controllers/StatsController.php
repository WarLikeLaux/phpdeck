<?php

namespace App\Http\Controllers;

use App\Models\Flashcard;
use App\Models\FlashcardEvent;
use App\Models\FlashcardUserProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class StatsController extends Controller
{
    private const DAILY_DAYS = 14;

    private const RECENT_DAYS = 30;

    private const WEAK_TOPIC_MIN_EVENTS = 3;

    private const WEAK_TOPIC_LIMIT = 5;

    public function show(Request $request): Response
    {
        $userId = (int) $request->user()->id;

        return Inertia::render('stats/index', [
            'streak' => $this->streak($userId),
            'today' => $this->today($userId),
            'daily' => $this->daily($userId),
            'totals' => $this->totals($userId),
            'categories' => $this->categories($userId),
            'weak_topics' => $this->weakTopics($userId),
        ]);
    }

    private function streak(int $userId): int
    {
        $dates = FlashcardEvent::query()
            ->where('user_id', $userId)
            ->selectRaw('DATE(occurred_at) as d')
            ->groupBy('d')
            ->orderByDesc('d')
            ->pluck('d')
            ->map(fn ($d) => Carbon::parse($d)->toDateString())
            ->all();

        if ($dates === []) {
            return 0;
        }

        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        $most = $dates[0];
        if ($most !== $today && $most !== $yesterday) {
            return 0;
        }

        $cursor = Carbon::parse($most);
        $set = array_flip($dates);
        $streak = 0;

        while (isset($set[$cursor->toDateString()])) {
            $streak++;
            $cursor = $cursor->subDay();
        }

        return $streak;
    }

    /**
     * @return array{studied: int, correct: int, incorrect: int, remembered: int, forgot: int}
     */
    private function today(int $userId): array
    {
        $start = now()->startOfDay();
        $end = now()->endOfDay();

        $counts = FlashcardEvent::query()
            ->where('user_id', $userId)
            ->whereBetween('occurred_at', [$start, $end])
            ->selectRaw('kind, COUNT(*) as c')
            ->groupBy('kind')
            ->pluck('c', 'kind');

        return [
            'studied' => (int) ($counts['studied'] ?? 0),
            'correct' => (int) (($counts['study_correct'] ?? 0) + ($counts['matching_correct'] ?? 0)),
            'incorrect' => (int) (($counts['study_incorrect'] ?? 0) + ($counts['matching_incorrect'] ?? 0)),
            'remembered' => (int) ($counts['review_remember'] ?? 0),
            'forgot' => (int) ($counts['review_forgot'] ?? 0),
        ];
    }

    /**
     * @return array<int, array{date: string, studied: int, correct: int, incorrect: int, remembered: int, forgot: int}>
     */
    private function daily(int $userId): array
    {
        $start = now()->subDays(self::DAILY_DAYS - 1)->startOfDay();
        $end = now()->endOfDay();

        $rows = FlashcardEvent::query()
            ->where('user_id', $userId)
            ->whereBetween('occurred_at', [$start, $end])
            ->selectRaw('DATE(occurred_at) as d, kind, COUNT(*) as c')
            ->groupBy('d', 'kind')
            ->get();

        /** @var array<string, array<string, int>> $byDate */
        $byDate = [];
        foreach ($rows as $row) {
            $date = Carbon::parse($row->d)->toDateString();
            $byDate[$date][$row->kind] = (int) $row->c;
        }

        $out = [];
        for ($i = self::DAILY_DAYS - 1; $i >= 0; $i--) {
            $day = now()->subDays($i)->toDateString();
            $kinds = $byDate[$day] ?? [];

            $out[] = [
                'date' => $day,
                'studied' => (int) ($kinds['studied'] ?? 0),
                'correct' => (int) (($kinds['study_correct'] ?? 0) + ($kinds['matching_correct'] ?? 0)),
                'incorrect' => (int) (($kinds['study_incorrect'] ?? 0) + ($kinds['matching_incorrect'] ?? 0)),
                'remembered' => (int) ($kinds['review_remember'] ?? 0),
                'forgot' => (int) ($kinds['review_forgot'] ?? 0),
            ];
        }

        return $out;
    }

    /**
     * @return array{total: int, studied: int, learned: int, graduated: int, due_now: int}
     */
    private function totals(int $userId): array
    {
        $progressBase = FlashcardUserProgress::query()->forUser($userId);

        return [
            'total' => Flashcard::query()->count(),
            'studied' => (clone $progressBase)->where('studied', true)->count(),
            'learned' => (clone $progressBase)->where('is_learned', true)->count(),
            'graduated' => (clone $progressBase)
                ->where('is_learned', true)
                ->whereNull('next_review_at')
                ->where('srs_step', '>', 0)
                ->count(),
            'due_now' => (clone $progressBase)->due()->count(),
        ];
    }

    /**
     * @return array<int, array{name: string, total: int, learned: int, accuracy: float|null}>
     */
    private function categories(int $userId): array
    {
        $start = now()->subDays(self::RECENT_DAYS)->startOfDay();

        $cards = Flashcard::query()
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
            ->get();

        $accuracyRows = FlashcardEvent::query()
            ->join('flashcards', 'flashcards.id', '=', 'flashcard_events.flashcard_id')
            ->where('flashcard_events.user_id', $userId)
            ->where('flashcard_events.occurred_at', '>=', $start)
            ->whereIn('flashcard_events.kind', ['study_correct', 'study_incorrect'])
            ->whereNotNull('flashcards.category')
            ->selectRaw('flashcards.category as category')
            ->selectRaw("SUM(CASE WHEN flashcard_events.kind = 'study_correct' THEN 1 ELSE 0 END) as correct")
            ->selectRaw("SUM(CASE WHEN flashcard_events.kind = 'study_incorrect' THEN 1 ELSE 0 END) as incorrect")
            ->groupBy('flashcards.category')
            ->get()
            ->keyBy('category');

        $out = [];
        foreach ($cards as $row) {
            $name = (string) $row->category;
            $accRow = $accuracyRows->get($name);
            $accuracy = null;

            if ($accRow !== null) {
                $correct = (int) $accRow->correct;
                $incorrect = (int) $accRow->incorrect;
                $denom = $correct + $incorrect;
                if ($denom > 0) {
                    $accuracy = round($correct / $denom, 4);
                }
            }

            $out[] = [
                'name' => $name,
                'total' => (int) $row->total,
                'learned' => (int) $row->learned,
                'accuracy' => $accuracy,
            ];
        }

        return $out;
    }

    /**
     * @return array<int, array{topic: string, errors: int, total: int, error_rate: float}>
     */
    private function weakTopics(int $userId): array
    {
        $start = now()->subDays(self::RECENT_DAYS)->startOfDay();

        $rows = FlashcardEvent::query()
            ->join('flashcards', 'flashcards.id', '=', 'flashcard_events.flashcard_id')
            ->where('flashcard_events.user_id', $userId)
            ->where('flashcard_events.occurred_at', '>=', $start)
            ->whereIn('flashcard_events.kind', [
                'study_correct',
                'study_incorrect',
                'matching_correct',
                'matching_incorrect',
                'review_forgot',
            ])
            ->whereNotNull('flashcards.topic')
            ->selectRaw('flashcards.topic as topic')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN flashcard_events.kind IN ('study_incorrect','matching_incorrect','review_forgot') THEN 1 ELSE 0 END) as errors")
            ->groupBy('flashcards.topic')
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $total = (int) $row->total;
            if ($total < self::WEAK_TOPIC_MIN_EVENTS) {
                continue;
            }

            $errors = (int) $row->errors;
            $out[] = [
                'topic' => (string) $row->topic,
                'errors' => $errors,
                'total' => $total,
                'error_rate' => round($errors / $total, 4),
            ];
        }

        usort($out, function (array $a, array $b): int {
            if ($a['error_rate'] === $b['error_rate']) {
                return $b['errors'] <=> $a['errors'];
            }

            return $b['error_rate'] <=> $a['error_rate'];
        });

        return array_slice($out, 0, self::WEAK_TOPIC_LIMIT);
    }
}
