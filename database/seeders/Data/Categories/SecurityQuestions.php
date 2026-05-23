<?php

namespace Database\Seeders\Data\Categories;

use Database\Seeders\Data\Categories\Security\Auth;
use Database\Seeders\Data\Categories\Security\Crypto;
use Database\Seeders\Data\Categories\Security\Owasp;
use Database\Seeders\Data\Categories\Security\Secrets;
use Database\Seeders\Data\Categories\Security\Sessions;
use Database\Seeders\Data\Categories\Security\Tokens;
use Database\Seeders\Data\Categories\Security\WebAttacks;

class SecurityQuestions
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, cloze_text?: ?string, short_answer?: ?string, assemble_chunks?: ?array<int, string>, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return array_merge(
            Owasp::all(),
            WebAttacks::all(),
            Auth::all(),
            Sessions::all(),
            Tokens::all(),
            Crypto::all(),
            Secrets::all(),
        );
    }
}
