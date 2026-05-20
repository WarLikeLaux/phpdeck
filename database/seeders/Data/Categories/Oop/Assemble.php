<?php

namespace Database\Seeders\Data\Categories\Oop;

class Assemble
{
    public static function all(): array
    {
        return [
            [
                'category' => 'ООП',
                'topic' => 'oop.assemble',
                'difficulty' => 2,
                'question' => 'Собери класс с конструктором и приватным свойством.',
                'answer' => '**Constructor property promotion** (PHP 8.0) объявляет и инициализирует свойство **в одной строке**.

- Модификатор видимости (`private`/`public`/`protected`) перед параметром превращает его в свойство.
- `readonly` фиксирует значение — записать можно только в конструкторе.
- `final class` запрещает наследование — типичный приём для **Value Object**.',
                'assemble_chunks' => [
                    'final class OrderTotal {',
                    '    public function __construct(',
                    '        private readonly Money $amount,',
                    '    ) {}',
                    '}',
                ],
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.assemble',
                'difficulty' => 1,
                'question' => 'Собери интерфейс Logger с одним методом.',
                'answer' => '**Интерфейс** описывает только **сигнатуры** методов, без тела. Все методы автоматически `public` и `abstract`.',
                'assemble_chunks' => [
                    'interface Logger',
                    '{',
                    '    public function log(string $message): void;',
                    '}',
                ],
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.assemble',
                'difficulty' => 1,
                'question' => 'Собери наследование класса Admin от User с переопределением метода.',
                'answer' => '- `extends` задаёт **родителя**.
- `parent::` вызывает **родительскую** реализацию метода.',
                'assemble_chunks' => [
                    'class Admin extends User',
                    '{',
                    '    public function greet(): string',
                    '    {',
                    '        return parent::greet() . \' (admin)\';',
                    '    }',
                    '}',
                ],
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.assemble',
                'difficulty' => 2,
                'question' => 'Собери абстрактный класс Shape с одним абстрактным методом area().',
                'answer' => '- `abstract class` — **запрещает** создавать объекты через `new`.
- `abstract public function area(): float;` — **обязательный** к реализации в потомках метод (без тела).
- Потомок, не реализовавший все `abstract`-методы, тоже обязан быть `abstract`.',
                'assemble_chunks' => [
                    'abstract class Shape',
                    '{',
                    '    abstract public function area(): float;',
                    '}',
                ],
            ],
        ];
    }
}
