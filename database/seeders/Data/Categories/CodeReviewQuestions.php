<?php

namespace Database\Seeders\Data\Categories;

use Database\Seeders\Data\Categories\CodeReview\Gotchas;
use Database\Seeders\Data\Categories\CodeReview\Process;

class CodeReviewQuestions
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, cloze_text?: ?string, short_answer?: ?string, assemble_chunks?: ?array<int, string>, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return array_merge(
            Process::all(),
            Gotchas::all(),
        );
    }
}
