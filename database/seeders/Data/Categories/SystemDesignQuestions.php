<?php

namespace Database\Seeders\Data\Categories;

use Database\Seeders\Data\Categories\SystemDesign\Api;
use Database\Seeders\Data\Categories\SystemDesign\Architecture;
use Database\Seeders\Data\Categories\SystemDesign\Caching;
use Database\Seeders\Data\Categories\SystemDesign\DesignTasks;
use Database\Seeders\Data\Categories\SystemDesign\Devops;
use Database\Seeders\Data\Categories\SystemDesign\Distributed;
use Database\Seeders\Data\Categories\SystemDesign\Git;
use Database\Seeders\Data\Categories\SystemDesign\MessagingQueues;
use Database\Seeders\Data\Categories\SystemDesign\Performance;
use Database\Seeders\Data\Categories\SystemDesign\Security;
use Database\Seeders\Data\Categories\SystemDesign\Tools;

class SystemDesignQuestions
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, cloze_text?: ?string, short_answer?: ?string, assemble_chunks?: ?array<int, string>, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return array_merge(
            Api::all(),
            Architecture::all(),
            Caching::all(),
            DesignTasks::all(),
            Devops::all(),
            Distributed::all(),
            Git::all(),
            MessagingQueues::all(),
            Performance::all(),
            Security::all(),
            Tools::all(),
        );
    }
}
