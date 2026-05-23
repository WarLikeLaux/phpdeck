<?php

namespace Database\Seeders\Data\Categories;

use Database\Seeders\Data\Categories\Devops\CodeReview;
use Database\Seeders\Data\Categories\Devops\Devops;
use Database\Seeders\Data\Categories\Devops\Git;
use Database\Seeders\Data\Categories\Devops\Tools;

class DevopsQuestions
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, cloze_text?: ?string, short_answer?: ?string, assemble_chunks?: ?array<int, string>, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return array_merge(
            Devops::all(),
            Git::all(),
            Tools::all(),
            CodeReview::all(),
        );
    }
}
