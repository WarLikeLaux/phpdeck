<?php

namespace Database\Seeders\Data\Categories\Oop;

class FourPrinciples
{
    public static function all(): array
    {
        return [
            [
                'category' => 'ООП',
                'topic' => 'oop.four_principles',
                'difficulty' => 2,
                'question' => 'Какие 4 основных принципа ООП?',
                'answer' => '4 столпа ООП: 1) Инкапсуляция - сокрытие внутреннего состояния и предоставление контролируемого доступа через методы. 2) Наследование - создание новых классов на основе существующих, переиспользуя их код. 3) Полиморфизм - возможность объектов разных классов реагировать на одинаковые вызовы по-разному. 4) Абстракция - выделение существенных характеристик объекта и сокрытие несущественных деталей.',
                'code_example' => null,
                'code_language' => null,
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.four_principles',
                'difficulty' => 2,
                'question' => 'Что такое инкапсуляция?',
                'answer' => 'Инкапсуляция - это объединение данных и методов работы с ними в одном классе плюс ограничение прямого доступа к внутреннему состоянию объекта. Внешний код не видит "внутренностей" и работает только через публичный интерфейс. Это защищает данные от некорректного использования и позволяет менять реализацию без поломки клиентов. В PHP реализуется через модификаторы public/protected/private.',
                'code_example' => '<?php
class BankAccount
{
    private float $balance = 0;

    public function deposit(float $amount): void
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException(\'Сумма должна быть положительной\');
        }
        $this->balance += $amount;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }
}
// Напрямую $balance изменить нельзя - только через deposit()',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.four_principles',
                'difficulty' => 2,
                'question' => 'Что такое наследование?',
                'answer' => 'Наследование - механизм, позволяющий создать новый класс (потомок, дочерний) на основе существующего (родительского). Потомок получает все public/protected свойства и методы родителя и может добавлять свои или переопределять родительские. В PHP наследование одиночное (один класс может наследовать только один родительский класс). Можно реализовывать несколько интерфейсов (это контракты, не наследование реализации). Трейты дают горизонтальное переиспользование кода, но это не наследование - у класса всё равно один родитель.',
                'code_example' => '<?php
class Animal
{
    public function eat(): string
    {
        return \'ест\';
    }
}

class Dog extends Animal
{
    public function bark(): string
    {
        return \'гав!\';
    }
}

$dog = new Dog();
echo $dog->eat();  // ест (унаследовано)
echo $dog->bark(); // гав!',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.four_principles',
                'difficulty' => 2,
                'question' => 'Что такое полиморфизм?',
                'answer' => 'Полиморфизм (от греч. "много форм") - возможность объектов разных классов обрабатываться единообразно через общий интерфейс. Простыми словами: один и тот же вызов метода может приводить к разному поведению в зависимости от конкретного класса объекта. В PHP полиморфизм подтипов реализуется через наследование и интерфейсы (subtype polymorphism). Это даёт расширяемость: можно добавлять новые типы без изменения старого кода.',
                'code_example' => '<?php
interface Shape
{
    public function area(): float;
}

class Circle implements Shape
{
    public function __construct(private float $r) {}
    public function area(): float
    {
        return M_PI * $this->r ** 2;
    }
}

class Square implements Shape
{
    public function __construct(private float $side) {}
    public function area(): float
    {
        return $this->side ** 2;
    }
}

function printArea(Shape $s): void
{
    echo $s->area(); // вызов один, поведение разное
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.four_principles',
                'difficulty' => 2,
                'question' => 'Что такое абстракция?',
                'answer' => 'Абстракция - выделение в объекте только существенных для решаемой задачи свойств и поведения, игнорируя несущественные детали. Простыми словами: вы пользуетесь автомобилем через руль, педали и КПП, не зная, как устроен двигатель внутри. В коде абстракция реализуется через классы, абстрактные классы и интерфейсы, которые описывают "что делает объект", скрывая "как он это делает".',
                'code_example' => '<?php
// Абстракция: интерфейс описывает контракт без деталей
interface PaymentGateway
{
    public function pay(float $amount): bool;
}

// Конкретные реализации скрывают детали
class StripeGateway implements PaymentGateway
{
    public function pay(float $amount): bool
    {
        // вся сложная логика Stripe API скрыта
        return true;
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.four_principles',
                'difficulty' => 4,
                'question' => 'Можно ли из метода объекта класса A обратиться к private-свойству ДРУГОГО объекта того же класса A?',
                'answer' => 'Да - и это часто удивляет на собесе. В PHP (как и в Java/C#/C++) модификаторы видимости работают на уровне КЛАССА, а не на уровне отдельного экземпляра. То есть private значит "доступно изнутри класса, где объявлено", а не "доступно только своему this". Это нужно для канонических операций, которые требуют чтения чужого внутреннего состояния: equals(self $other), compareTo, copy/merge, factory-методов, операций над двумя объектами одного типа (Money::add(Money $other) с доступом к $other->amount). protected расширяет это до иерархии (доступно классу и наследникам); public - всем. Для разделения на уровне инстансов в PHP нет встроенного модификатора - если такая семантика нужна, её эмулируют через публичные геттеры или интерфейс. На собесе: "private = per-class, не per-instance" - короткий правильный ответ.',
                'code_example' => '<?php
final class Money
{
    public function __construct(
        private readonly int $amount,
        private readonly string $currency,
    ) {}

    public function add(Money $other): self
    {
        // ✅ Доступ к private $other->amount и $other->currency - ОК
        // т.к. мы внутри методов того же класса Money
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException("currency mismatch");
        }
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function equals(self $other): bool
    {
        return $this->amount === $other->amount
            && $this->currency === $other->currency;
    }
}

$a = new Money(100, "USD");
$b = new Money(50,  "USD");
$c = $a->add($b);  // 150 USD - читать $b->amount можно

// Из ВНЕШНЕГО кода private так не достать:
// echo $a->amount; // Error: Cannot access private property',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'В чём разница между абстракцией и инкапсуляцией как принципами ООП?',
                'answer' => 'Абстракция — это про дизайн интерфейса: мы выделяем существенные операции и скрываем внутреннюю сложность, отвечая на вопрос «что объект умеет». Инкапсуляция — это про защиту состояния: мы прячем внутренние данные за модификаторами видимости и публикуем только методы, гарантирующие инварианты, отвечая на вопрос «как именно объект защищает себя». Абстракция чаще выражается интерфейсами и абстрактными классами, инкапсуляция — приватными свойствами и геттерами/сеттерами. Они дополняют друг друга: без инкапсуляции абстракция протекает, а без абстракции инкапсуляция превращается в анемичный набор геттеров.',
                'difficulty' => 2,
                'topic' => 'oop.four_principles',
            ],
        ];
    }
}
