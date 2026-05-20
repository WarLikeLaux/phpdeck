<?php

namespace Database\Seeders\Data\Categories\Oop;

class CompositionVsInheritance
{
    public static function all(): array
    {
        return [
            [
                'category' => 'ООП',
                'topic' => 'oop.composition_vs_inheritance',
                'difficulty' => 3,
                'question' => 'Композиция vs наследование - что выбрать?',
                'answer' => 'Композиция - объект содержит другие объекты как поля и делегирует им работу. Наследование - класс получает функциональность от родителя через extends. Принцип "Composition over Inheritance" говорит: предпочитайте композицию. Наследование жёстко связывает классы, нарушает инкапсуляцию (потомок зависит от деталей родителя), не подходит для отношений "имеет" (has-a). Используйте наследование для "является" (is-a) и при настоящей иерархии типов.',
                'code_example' => '<?php
class Engine
{
    public function run(): void {}
}

// Плохо: наследование там, где отношение has-a, а не is-a
// Car не "является" Engine, у машины "есть" двигатель
class CarBad extends Engine {}

// Хорошо: композиция через DI
class Car
{
    public function __construct(private Engine $engine) {}

    public function start(): void
    {
        $this->engine->run();
    }
}

// Бонус: легко подменить реализацию (электро/бензин/мок в тестах)
$car = new Car(new Engine());',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.composition_vs_inheritance',
                'difficulty' => 1,
                'question' => 'Что такое композиция объектов простыми словами?',
                'answer' => '**Композиция** — когда один объект **содержит** другие объекты как свои поля и делегирует им работу.

Отношение **«has-a»** (имеет): у `Car` есть `Engine`.

- Двигатель передаётся снаружи через **конструктор**.
- Реализацию (бензиновый/электрический/мок в тесте) можно **подменить**, не трогая `Car`.
- Гибче наследования и **не связывает классы жёстко**.',
                'code_example' => '<?php
class Engine
{
    public function run(): void { /* ... */ }
}

class Car
{
    // Car "имеет" Engine - композиция
    public function __construct(private Engine $engine) {}

    public function start(): void
    {
        $this->engine->run();
    }
}

$car = new Car(new Engine());
$car->start();',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.composition_vs_inheritance',
                'difficulty' => 1,
                'question' => 'Когда выбирать композицию, а когда наследование простыми словами?',
                'answer' => '- **Наследование** — для отношения **«является»** (is-a): `Admin` — это `User`.
- **Композиция** — для **«имеет»** (has-a): у `Car` есть `Engine`.

Правило **«composition over inheritance»**: если сомневаешься — выбирай композицию.

**Почему:** наследование жёстко связывает классы, потомок зависит от внутренних деталей родителя — менять родителя становится страшно.',
                'code_example' => '<?php
// is-a → наследование
class User { public string $name = \'\'; }
class Admin extends User { public function ban(): void {} }

// has-a → композиция
class Engine { public function run(): void {} }
class Car
{
    public function __construct(private Engine $engine) {}
}

// Антипример: has-a через наследование
// class Car extends Engine {} // плохо: Car не "является" Engine',
                'code_language' => 'php',
            ],
        ];
    }
}
