<?php

namespace Database\Seeders\Data\Categories;

use Database\Seeders\Data\Categories\Php\Algorithms;
use Database\Seeders\Data\Categories\Php\Arrays;
use Database\Seeders\Data\Categories\Php\Assemble;
use Database\Seeders\Data\Categories\Php\BasicQa;
use Database\Seeders\Data\Categories\Php\BasicSyntax;
use Database\Seeders\Data\Categories\Php\Closures;
use Database\Seeders\Data\Categories\Php\Cloze;
use Database\Seeders\Data\Categories\Php\CodeTraps;
use Database\Seeders\Data\Categories\Php\ComposerAutoload;
use Database\Seeders\Data\Categories\Php\Exceptions;
use Database\Seeders\Data\Categories\Php\FilesStreams;
use Database\Seeders\Data\Categories\Php\Generators;
use Database\Seeders\Data\Categories\Php\MagicMethods;
use Database\Seeders\Data\Categories\Php\Oop;
use Database\Seeders\Data\Categories\Php\Operators;
use Database\Seeders\Data\Categories\Php\Php8Features;
use Database\Seeders\Data\Categories\Php\Psr;
use Database\Seeders\Data\Categories\Php\Regex;
use Database\Seeders\Data\Categories\Php\Runtime;
use Database\Seeders\Data\Categories\Php\Security;
use Database\Seeders\Data\Categories\Php\Sessions;
use Database\Seeders\Data\Categories\Php\StdLib;
use Database\Seeders\Data\Categories\Php\Strings;
use Database\Seeders\Data\Categories\Php\Symfony;
use Database\Seeders\Data\Categories\Php\Testing;
use Database\Seeders\Data\Categories\Php\TypeIn;
use Database\Seeders\Data\Categories\Php\Types;

class PhpQuestions
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, cloze_text?: ?string, short_answer?: ?string, assemble_chunks?: ?array<int, string>, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return array_merge(
            Algorithms::all(),
            Arrays::all(),
            Assemble::all(),
            BasicQa::all(),
            BasicSyntax::all(),
            Closures::all(),
            Cloze::all(),
            CodeTraps::all(),
            ComposerAutoload::all(),
            Exceptions::all(),
            FilesStreams::all(),
            Generators::all(),
            MagicMethods::all(),
            Oop::all(),
            Operators::all(),
            Php8Features::all(),
            Psr::all(),
            Regex::all(),
            Runtime::all(),
            Security::all(),
            Sessions::all(),
            StdLib::all(),
            Strings::all(),
            Symfony::all(),
            Testing::all(),
            TypeIn::all(),
            Types::all(),
        );
    }
}
