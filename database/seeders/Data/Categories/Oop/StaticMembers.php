<?php

namespace Database\Seeders\Data\Categories\Oop;

class StaticMembers
{
    public static function all(): array
    {
        return [
            [
                'category' => 'ООП',
                'topic' => 'oop.static_members',
                'difficulty' => 2,
                'question' => 'Что такое статические методы и свойства?',
                'answer' => 'Статические методы и свойства принадлежат самому классу, а не его экземплярам. Доступ - через имя класса и оператор :: (Class::method()). Статические свойства одни на весь класс - значение общее для всех. Статические методы не имеют $this, но имеют self/static. Используются для утилитарных функций (без состояния), фабричных методов, счётчиков. Антипаттерн: реализация singleton через private static $instance - превращает статику в скрытый глобальный state и блокирует тестируемость. Минусы: усложняют тестирование (тяжело замокать), скрытая глобальная зависимость.',
                'code_example' => '<?php
class Counter
{
    public static int $count = 0;

    public static function increment(): void
    {
        self::$count++;
    }
}

Counter::increment();
Counter::increment();
echo Counter::$count; // 2',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.static_members',
                'difficulty' => 3,
                'question' => 'В чём разница между self, static и parent в PHP?',
                'answer' => 'self - указывает на тот класс, где написан код (раннее связывание). static - указывает на фактический класс, через который был сделан вызов (позднее статическое связывание, late static binding): для нестатического контекста - runtime-класс $this, для статического - класс из Foo::method(). parent - указывает на родительский класс. Различие self/static важно при наследовании и фабричных методах: при new self() всегда создастся объект исходного класса, а при new static() - объект класса-наследника, на котором вызвали метод.',
                'code_example' => '<?php
class A
{
    public static function createSelf(): self
    {
        return new self();
    }
    public static function createStatic(): static
    {
        return new static();
    }
}

class B extends A {}

var_dump(B::createSelf());   // object(A)
var_dump(B::createStatic()); // object(B)',
                'code_language' => 'php',
            ],
        ];
    }
}
