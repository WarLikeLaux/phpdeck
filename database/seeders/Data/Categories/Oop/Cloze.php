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
                'answer' => 'interface вводит контракт, implements обязывает класс реализовать все методы.',
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
                'answer' => 'extends задаёт родителя, parent:: вызывает реализацию родителя.',
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
                'answer' => 'private скрывает поле, public открывает методы наружу.',
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
                'answer' => 'abstract запрещает new и помечает обязательный для потомков метод.',
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
                'answer' => 'static в return-типе и в new создаёт объект фактического класса-наследника.',
                'cloze_text' => 'class Model {
    public static function create(): {{static}} {
        return new {{static}}();
    }
}',
            ],
        ];
    }
}
