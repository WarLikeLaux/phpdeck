<?php

namespace App\Models;

use Database\Factories\FlashcardFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Flashcard extends Model
{
    /** @use HasFactory<FlashcardFactory> */
    use HasFactory;

    protected $fillable = [
        'slug',
        'category',
        'topic',
        'difficulty',
        'question',
        'answer',
        'code_example',
        'code_language',
        'cloze_text',
        'short_answer',
        'assemble_chunks',
    ];

    protected static function booted(): void
    {
        static::saving(function (Flashcard $card) {
            if (empty($card->slug) && $card->category && $card->question) {
                $card->slug = hash('sha256', $card->category.'|'.$card->question);
            }
        });
    }

    public static function slugFor(string $category, string $question): string
    {
        return hash('sha256', $category.'|'.$question);
    }

    protected $casts = [
        'assemble_chunks' => 'array',
        'difficulty' => 'integer',
    ];

    protected $attributes = [
        'difficulty' => 1,
    ];

    /**
     * @return HasMany<FlashcardUserProgress, $this>
     */
    public function progress(): HasMany
    {
        return $this->hasMany(FlashcardUserProgress::class);
    }

    /**
     * @return HasOne<FlashcardUserProgress, $this>
     */
    public function progressFor(int $userId): HasOne
    {
        return $this->hasOne(FlashcardUserProgress::class)->where('user_id', $userId);
    }

    /**
     * @return HasMany<FlashcardEvent, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(FlashcardEvent::class);
    }
}
