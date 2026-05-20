<?php

namespace Database\Seeders\Data\Categories\Oop;

class Cloze
{
    public static function all(): array
    {
        return [
            [
                'category' => 'ООП',
                'topic' => 'oop.cloze',
                'difficulty' => 2,
                'question' => 'Заполни декларацию интерфейса и реализации для команды.',
                'answer' => '- `interface` объявляет **контракт** — список методов без реализации.
- `implements` **обязывает** класс реализовать все методы интерфейса с совместимыми сигнатурами.
- Один класс может реализовать **несколько** интерфейсов через запятую.',
                'cloze_text' => '{{interface}} Command {
    public function execute(): void;
}

class SendEmail {{implements}} Command {
    public function execute(): void { /* ... */ }
}',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.cloze',
                'difficulty' => 1,
                'question' => 'Заполни наследование и переопределение метода.',
                'answer' => '- `extends` задаёт **родителя**.
- `parent::` вызывает **реализацию родителя** внутри переопределённого метода.',
                'cloze_text' => 'class Admin {{extends}} User {
    public function greet(): string {
        return {{parent}}::greet() . \' (admin)\';
    }
}',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.cloze',
                'difficulty' => 1,
                'question' => 'Заполни модификаторы видимости в классе с приватным состоянием.',
                'answer' => '- `private` **скрывает** поле — доступ только внутри класса.
- `public` **открывает** метод наружу — доступ откуда угодно.',
                'cloze_text' => 'class Counter {
    {{private}} int $value = 0;

    {{public}} function increment(): void {
        $this->value++;
    }
}',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.cloze',
                'difficulty' => 2,
                'question' => 'Заполни абстрактный класс с одним абстрактным методом.',
                'answer' => '- `abstract class` — **запрещает** `new` для этого класса.
- `abstract public function ...` — метод **без тела**, обязателен к реализации в потомках.
- В абстрактном классе можно держать и **обычные** методы — общую логику для всех потомков.',
                'cloze_text' => '{{abstract}} class Shape {
    {{abstract}} public function area(): float;

    public function describe(): string {
        return \'Площадь: \' . $this->area();
    }
}',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.cloze',
                'difficulty' => 2,
                'question' => 'Заполни статический фабричный метод с late static binding.',
                'answer' => '**Late Static Binding (LSB)** через ключевое слово `static`:

- `static` в **return-типе** — обещает вернуть объект **фактического** класса, через который вызвали метод.
- `new static()` — создаёт экземпляр того класса, на котором вызвали `::create()`, а не того, где написан код.
- В отличие от `self::` (всегда исходный класс), `static::` учитывает наследование — нужно для **фабрик в базовом классе**.',
                'cloze_text' => 'class Model {
    public static function create(): {{static}} {
        return new {{static}}();
    }
}',
            ],
        ];
    }
}
