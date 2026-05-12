<?php

namespace Database\Seeders\Data\Categories;

use Database\Seeders\Data\Categories\Testing\Basics;
use Database\Seeders\Data\Categories\Testing\Doubles;
use Database\Seeders\Data\Categories\Testing\Practice;
use Database\Seeders\Data\Categories\Testing\Tools;

class TestingQuestions
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, cloze_text?: ?string, short_answer?: ?string, assemble_chunks?: ?array<int, string>, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return array_merge(
            Basics::all(),
            Doubles::all(),
            Practice::all(),
            Tools::all(),
        );
    }
}
