<?php

namespace Database\Seeders\Data\Categories;

use Database\Seeders\Data\Categories\Oop\AbstractAndInterfaces;
use Database\Seeders\Data\Categories\Oop\AnemicVsRich;
use Database\Seeders\Data\Categories\Oop\Assemble;
use Database\Seeders\Data\Categories\Oop\BasicConcepts;
use Database\Seeders\Data\Categories\Oop\BasicQa;
use Database\Seeders\Data\Categories\Oop\CleanCode;
use Database\Seeders\Data\Categories\Oop\Cloze;
use Database\Seeders\Data\Categories\Oop\CompositionVsInheritance;
use Database\Seeders\Data\Categories\Oop\ConstructorDestructor;
use Database\Seeders\Data\Categories\Oop\CouplingCohesion;
use Database\Seeders\Data\Categories\Oop\Ddd;
use Database\Seeders\Data\Categories\Oop\DependencyInjection;
use Database\Seeders\Data\Categories\Oop\EventSourcingCqrs;
use Database\Seeders\Data\Categories\Oop\FourPrinciples;
use Database\Seeders\Data\Categories\Oop\GofBehavioral;
use Database\Seeders\Data\Categories\Oop\GofCreational;
use Database\Seeders\Data\Categories\Oop\GofStructural;
use Database\Seeders\Data\Categories\Oop\Misc;
use Database\Seeders\Data\Categories\Oop\Solid;
use Database\Seeders\Data\Categories\Oop\StaticMembers;
use Database\Seeders\Data\Categories\Oop\Traits;
use Database\Seeders\Data\Categories\Oop\Visibility;

class OopQuestions
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, cloze_text?: ?string, short_answer?: ?string, assemble_chunks?: ?array<int, string>, topic?: string, difficulty?: int}>
     */
    public static function all(): array
    {
        return array_merge(
            BasicConcepts::all(),
            FourPrinciples::all(),
            Visibility::all(),
            ConstructorDestructor::all(),
            StaticMembers::all(),
            AbstractAndInterfaces::all(),
            Traits::all(),
            CompositionVsInheritance::all(),
            Solid::all(),
            GofCreational::all(),
            GofStructural::all(),
            GofBehavioral::all(),
            DependencyInjection::all(),
            Ddd::all(),
            EventSourcingCqrs::all(),
            AnemicVsRich::all(),
            CouplingCohesion::all(),
            Misc::all(),
            CleanCode::all(),
            BasicQa::all(),
            Cloze::all(),
            Assemble::all(),
        );
    }
}
