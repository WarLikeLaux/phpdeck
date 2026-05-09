<?php

namespace App\Models;

use Database\Factories\FlashcardUserProgressFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class FlashcardUserProgress extends Model
{
    /** @use HasFactory<FlashcardUserProgressFactory> */
    use HasFactory;

    protected $table = 'flashcard_user_progress';

    public const LEARN_THRESHOLD = 3;

    /** @var array<int, int> Days between SRS reviews after a card is learned. */
    public const SRS_INTERVALS_DAYS = [1, 3, 5, 7];

    protected $fillable = [
        'user_id',
        'flashcard_id',
        'studied',
        'is_learned',
        'correct_streak',
        'correct_modes',
        'required_correct',
        'srs_step',
        'next_review_at',
        'note',
    ];

    protected $casts = [
        'correct_modes' => 'array',
        'correct_streak' => 'integer',
        'required_correct' => 'integer',
        'is_learned' => 'boolean',
        'studied' => 'boolean',
        'next_review_at' => 'datetime',
        'srs_step' => 'integer',
    ];

    protected $attributes = [
        'correct_streak' => 0,
        'required_correct' => self::LEARN_THRESHOLD,
        'is_learned' => false,
        'studied' => false,
        'srs_step' => 0,
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Flashcard, $this>
     */
    public function flashcard(): BelongsTo
    {
        return $this->belongsTo(Flashcard::class);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public static function forCurrent(int $flashcardId): self
    {
        $userId = (int) auth()->id();

        return self::firstOrNew([
            'user_id' => $userId,
            'flashcard_id' => $flashcardId,
        ]);
    }

    /**
     * @return array{studied: bool, is_learned: bool, correct_streak: int, correct_modes: array<int, string>, required_correct: int, srs_step: int, next_review_at: Carbon|null, note: string|null}
     */
    public static function defaults(): array
    {
        return [
            'studied' => false,
            'is_learned' => false,
            'correct_streak' => 0,
            'correct_modes' => [],
            'required_correct' => self::LEARN_THRESHOLD,
            'srs_step' => 0,
            'next_review_at' => null,
            'note' => null,
        ];
    }

    /**
     * @return array{studied: bool, is_learned: bool, correct_streak: int, correct_modes: array<int, string>, required_correct: int, srs_step: int, next_review_at: Carbon|null, note: string|null}
     */
    public function asArray(): array
    {
        return [
            'studied' => (bool) $this->studied,
            'is_learned' => (bool) $this->is_learned,
            'correct_streak' => (int) $this->correct_streak,
            'correct_modes' => (array) ($this->correct_modes ?? []),
            'required_correct' => (int) ($this->required_correct ?: self::LEARN_THRESHOLD),
            'srs_step' => (int) $this->srs_step,
            'next_review_at' => $this->next_review_at,
            'note' => $this->note,
        ];
    }

    public function scopeDue(Builder $query): Builder
    {
        return $query
            ->where('studied', true)
            ->where(function (Builder $q) {
                $q->where('is_learned', false)
                    ->orWhere(function (Builder $q2) {
                        $q2->where('is_learned', true)
                            ->whereNotNull('next_review_at')
                            ->where('next_review_at', '<=', now());
                    });
            });
    }

    public function scopeUnstudied(Builder $query): Builder
    {
        return $query->where('studied', false);
    }

    public function markStudied(): void
    {
        $this->studied = true;
        $this->save();
    }

    public function markCorrect(?string $mode = null): void
    {
        $this->correct_streak++;

        if ($this->is_learned) {
            $this->advanceSrsStep();
        } else {
            if ($mode !== null) {
                $modes = (array) ($this->correct_modes ?? []);
                if (! in_array($mode, $modes, true)) {
                    $modes[] = $mode;
                }
                $this->correct_modes = array_values($modes);
            }

            if ($this->isReadyToLearn()) {
                $this->is_learned = true;
                $this->srs_step = 0;
                $this->next_review_at = now()->addDays(self::SRS_INTERVALS_DAYS[0]);
            }
        }

        $this->save();
    }

    public function markIncorrect(): void
    {
        $this->correct_streak = 0;
        $this->correct_modes = [];
        $this->is_learned = false;
        $this->srs_step = 0;
        $this->next_review_at = null;
        $this->save();
    }

    public function resetProgress(): void
    {
        $this->correct_streak = 0;
        $this->correct_modes = [];
        $this->is_learned = false;
        $this->studied = false;
        $this->srs_step = 0;
        $this->next_review_at = null;
        $this->save();
    }

    private function advanceSrsStep(): void
    {
        $this->srs_step++;

        if ($this->srs_step >= count(self::SRS_INTERVALS_DAYS)) {
            $this->next_review_at = null;

            return;
        }

        $this->next_review_at = now()->addDays(self::SRS_INTERVALS_DAYS[$this->srs_step]);
    }

    private function isReadyToLearn(): bool
    {
        $distinct = count((array) ($this->correct_modes ?? []));
        $threshold = (int) ($this->required_correct ?: self::LEARN_THRESHOLD);

        return $distinct >= $threshold;
    }
}
