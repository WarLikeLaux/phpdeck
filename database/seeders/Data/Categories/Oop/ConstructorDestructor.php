<?php

namespace Database\Seeders\Data\Categories\Oop;

class ConstructorDestructor
{
    public static function all(): array
    {
        return [
            [
                'category' => 'ООП',
                'topic' => 'oop.constructor_destructor',
                'difficulty' => 2,
                'question' => 'Что такое конструктор?',
                'answer' => 'Конструктор - специальный метод __construct(), который вызывается автоматически при создании объекта через new. Используется для инициализации свойств объекта. С PHP 8.0 есть constructor property promotion - можно объявлять свойства прямо в параметрах конструктора, что сокращает бойлерплейт. Конструктор может принимать параметры. PHP НЕ вызывает родительский конструктор автоматически (в отличие от Java/C++) - если потомок переопределил __construct, разработчик обязан явно вызвать parent::__construct(...).',
                'code_example' => '<?php
// Старый стиль
class UserOld
{
    public string $name;
    public int $age;

    public function __construct(string $name, int $age)
    {
        $this->name = $name;
        $this->age = $age;
    }
}

// PHP 8+ promotion
class User
{
    public function __construct(
        public string $name,
        public int $age,
    ) {}
}

$u = new User(\'Иван\', 30);',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.constructor_destructor',
                'difficulty' => 2,
                'question' => 'Что такое деструктор?',
                'answer' => 'Деструктор - метод __destruct(), который вызывается автоматически при уничтожении объекта (когда на него больше нет ссылок или скрипт завершается). Используется для освобождения ресурсов: закрытия файлов, соединений, очистки кешей. В PHP с автоматическим управлением памятью деструкторы используются реже, чем в C++. Особенности: не гарантируется порядок вызова при завершении скрипта; деструкторы могут быть НЕ вызваны при fatal error, OOM или некоторых случаях циклических ссылок на shutdown; бросать исключения из деструктора крайне нежелательно - если вызов произошёл при shutdown, исключение становится fatal error.',
                'code_example' => '<?php
class FileLogger
{
    private $handle;

    public function __construct(string $path)
    {
        $this->handle = fopen($path, \'a\');
    }

    public function log(string $msg): void
    {
        fwrite($this->handle, $msg . PHP_EOL);
    }

    public function __destruct()
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
        }
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Зачем нужен constructor property promotion простыми словами?',
                'answer' => 'Сокращённый синтаксис: параметры конструктора с модификатором видимости становятся свойствами автоматически. Вместо: public string $name; function __construct(string $name) { $this->name = $name; } — пишешь public function __construct(public string $name) {}. Меньше шаблонного кода для DTO и Value Objects.',
                'difficulty' => 2,
                'topic' => 'oop.constructor_destructor',
            ],
            [
                'category' => 'ООП',
                'question' => 'Зачем вызывать parent::__construct() в конструкторе наследника?',
                'answer' => 'PHP НЕ вызывает родительский конструктор автоматически. Если у родителя в конструкторе инициализируются важные свойства (например, в Eloquent\\Model задаются атрибуты), а ты не позвал parent::__construct() — объект будет в неполном состоянии. Правило: в конструкторе ребёнка вызывай parent::__construct(...) первой строкой.',
                'difficulty' => 2,
                'topic' => 'oop.constructor_destructor',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.constructor_destructor',
                'difficulty' => 2,
                'question' => 'Что такое readonly-свойство (PHP 8.1)?',
                'answer' => 'Свойство, которое можно записать ТОЛЬКО один раз — в конструкторе того же класса. После этого любая попытка записи бросит Error. Идеально для иммутабельных объектов (Value Objects, DTO): гарантирует, что значение не изменится после создания. С PHP 8.2 целый класс можно объявить readonly — тогда readonly применяется ко всем его свойствам.',
                'code_example' => '<?php
final class Point
{
    public function __construct(
        public readonly float $x,
        public readonly float $y,
    ) {}
}

$p = new Point(1.0, 2.0);
// $p->x = 5.0; // Error: Cannot modify readonly property',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.constructor_destructor',
                'difficulty' => 2,
                'question' => 'Можно ли в PHP объявить несколько конструкторов (перегрузка)?',
                'answer' => 'Нет, в одном классе может быть только один метод __construct. Имитируют разные способы создания через статические фабричные методы (named constructors): Money::fromCents(100), Money::fromDollars(1.5). Это и понятнее (название говорит о смысле), и обходит ограничение PHP на перегрузку методов.',
                'code_example' => '<?php
final class Money
{
    private function __construct(public int $cents) {}

    public static function fromCents(int $cents): self
    {
        return new self($cents);
    }

    public static function fromDollars(float $dollars): self
    {
        return new self((int) round($dollars * 100));
    }
}

$a = Money::fromCents(150);
$b = Money::fromDollars(1.5);',
                'code_language' => 'php',
            ],
        ];
    }
}
