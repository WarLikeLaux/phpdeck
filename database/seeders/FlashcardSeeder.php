<?php

namespace Database\Seeders;

use App\Models\Flashcard;
use Database\Seeders\Data\Categories\CodeReviewQuestions;
use Database\Seeders\Data\Categories\DatabaseQuestions;
use Database\Seeders\Data\Categories\DevopsQuestions;
use Database\Seeders\Data\Categories\LaravelQuestions;
use Database\Seeders\Data\Categories\NetworkingQuestions;
use Database\Seeders\Data\Categories\OopQuestions;
use Database\Seeders\Data\Categories\PhpQuestions;
use Database\Seeders\Data\Categories\SecurityQuestions;
use Database\Seeders\Data\Categories\SystemDesignQuestions;
use Database\Seeders\Data\Categories\TestingQuestions;
use Database\Seeders\Data\Categories\Yii2Questions;
use Illuminate\Database\Seeder;

class FlashcardSeeder extends Seeder
{
    public function run(): void
    {
        $cards = [
            ...PhpQuestions::all(),
            ...OopQuestions::all(),
            ...LaravelQuestions::all(),
            ...Yii2Questions::all(),
            ...DatabaseQuestions::all(),
            ...SystemDesignQuestions::all(),
            ...NetworkingQuestions::all(),
            ...SecurityQuestions::all(),
            ...TestingQuestions::all(),
            ...DevopsQuestions::all(),
            ...CodeReviewQuestions::all(),
        ];

        $seenSlugs = [];
        $created = 0;
        $updated = 0;

        foreach ($cards as $card) {
            $slug = Flashcard::slugFor($card['category'], $card['question']);

            if (isset($seenSlugs[$slug])) {
                // Дубль внутри сидера — пропускаем, чтобы upsert не натыкался на дубли в одной пачке.
                continue;
            }
            $seenSlugs[$slug] = true;

            $existing = Flashcard::query()->where('slug', $slug)->first();
            if ($existing) {
                $existing->fill($card)->save();
                $updated++;
            } else {
                Flashcard::query()->create(['slug' => $slug] + $card);
                $created++;
            }
        }

        // Удаляем «осиротевшие» карточки — те, что были в БД, но больше не описаны в сидерах.
        // Прогресс юзеров по ним удалится каскадно (FK cascadeOnDelete) — это ожидаемо,
        // потому что карточки больше нет.
        $deleted = Flashcard::query()->whereNotIn('slug', array_keys($seenSlugs))->delete();

        $this->command?->info("FlashcardSeeder: created=$created, updated=$updated, deleted=$deleted, total=".count($seenSlugs));
    }
}
