<?php

namespace Database\Factories;

use App\Models\Flashcard;
use App\Models\FlashcardUserProgress;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FlashcardUserProgress>
 */
class FlashcardUserProgressFactory extends Factory
{
    protected $model = FlashcardUserProgress::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'flashcard_id' => Flashcard::factory(),
            'studied' => true,
            'is_learned' => false,
            'correct_streak' => 0,
            'correct_modes' => [],
            'required_correct' => FlashcardUserProgress::LEARN_THRESHOLD,
            'srs_step' => 0,
            'next_review_at' => null,
            'note' => null,
        ];
    }

    public function unstudied(): self
    {
        return $this->state(fn () => [
            'studied' => false,
            'is_learned' => false,
            'correct_streak' => 0,
            'correct_modes' => [],
            'srs_step' => 0,
            'next_review_at' => null,
        ]);
    }

    public function learned(): self
    {
        return $this->state(fn () => [
            'studied' => true,
            'is_learned' => true,
            'correct_streak' => FlashcardUserProgress::LEARN_THRESHOLD,
            'correct_modes' => ['reveal', 'type_in', 'multiple_choice'],
            'required_correct' => FlashcardUserProgress::LEARN_THRESHOLD,
            'srs_step' => 0,
            'next_review_at' => now()->addDays(FlashcardUserProgress::SRS_INTERVALS_DAYS[0]),
        ]);
    }

    public function dueForReview(): self
    {
        return $this->state(fn () => [
            'studied' => true,
            'is_learned' => true,
            'correct_streak' => FlashcardUserProgress::LEARN_THRESHOLD,
            'correct_modes' => ['reveal', 'type_in', 'multiple_choice'],
            'required_correct' => FlashcardUserProgress::LEARN_THRESHOLD,
            'srs_step' => 0,
            'next_review_at' => now()->subDay(),
        ]);
    }

    public function graduated(): self
    {
        return $this->state(fn () => [
            'studied' => true,
            'is_learned' => true,
            'correct_streak' => FlashcardUserProgress::LEARN_THRESHOLD + count(FlashcardUserProgress::SRS_INTERVALS_DAYS),
            'correct_modes' => ['reveal', 'type_in', 'multiple_choice'],
            'required_correct' => FlashcardUserProgress::LEARN_THRESHOLD,
            'srs_step' => count(FlashcardUserProgress::SRS_INTERVALS_DAYS),
            'next_review_at' => null,
        ]);
    }
}
