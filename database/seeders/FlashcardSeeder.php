<?php

namespace Database\Seeders;

use App\Models\Flashcard;
use Database\Seeders\Data\Categories\DatabaseQuestions;
use Database\Seeders\Data\Categories\LaravelQuestions;
use Database\Seeders\Data\Categories\NetworkingQuestions;
use Database\Seeders\Data\Categories\OopQuestions;
use Database\Seeders\Data\Categories\PhpQuestions;
use Database\Seeders\Data\Categories\SecurityQuestions;
use Database\Seeders\Data\Categories\SystemDesignQuestions;
use Database\Seeders\Data\Categories\TestingQuestions;
use Illuminate\Database\Seeder;

class FlashcardSeeder extends Seeder
{
    public function run(): void
    {
        $cards = [
            ...PhpQuestions::all(),
            ...OopQuestions::all(),
            ...LaravelQuestions::all(),
            ...DatabaseQuestions::all(),
            ...SystemDesignQuestions::all(),
            ...NetworkingQuestions::all(),
            ...SecurityQuestions::all(),
            ...TestingQuestions::all(),
        ];

        foreach ($cards as $card) {
            Flashcard::query()->create($card);
        }
    }
}
