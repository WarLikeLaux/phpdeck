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
                'answer' => '**4 столпа ООП:**

1. **Инкапсуляция** — данные и методы работы с ними внутри одного класса; внешнее состояние спрятано за `private`/`protected`, наружу торчит контролируемый API.
2. **Наследование** — новый класс **расширяет** существующий через `extends`, переиспользуя его код и добавляя своё (отношение **is-a**).
3. **Полиморфизм** — один вызов даёт **разное** поведение в зависимости от конкретного класса. Реализуется через интерфейсы и переопределение методов.
4. **Абстракция** — выделяем **существенное** для задачи, прячем детали. Описываем «что объект умеет» через интерфейсы и абстрактные классы.

Часто SOLID, DRY, KISS считают **уточнениями** этих четырёх принципов.',
                'code_example' => null,
                'code_language' => null,
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.four_principles',
                'difficulty' => 2,
                'question' => 'Что такое инкапсуляция?',
                'answer' => '**Инкапсуляция** = **объединение** данных и методов + **сокрытие** внутреннего состояния.

**Два аспекта:**

- **Группировка** — состояние и поведение лежат вместе, в одном классе.
- **Сокрытие** — поля делают `private`/`protected`, наружу торчит только публичный API.

**Что даёт:**

- Защита **инвариантов**: `deposit(int $amount)` проверит, что сумма положительная.
- **Свобода менять реализацию** — пока публичный API не сломан, внутренности можно переписать.
- **Меньше связности** — клиент не знает, как именно хранятся данные.

**В PHP** реализуется через модификаторы `public`/`protected`/`private` (и `readonly` для иммутабельности).',
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
                'answer' => '**Наследование** — новый класс (**потомок**) создаётся на основе существующего (**родителя**) и получает все его `public`/`protected` свойства и методы.

**Потомок может:**

- добавить **свои** методы и свойства
- **переопределить** родительские (`override`)
- использовать родительские **как есть**

**Ограничения в PHP:**

- Наследование **одиночное** — один родитель, не больше.
- Несколько контрактов — через `implements` (интерфейсы).
- Горизонтальное переиспользование кода — через `use` (трейты), но это **не** наследование.

**Когда применять:** есть чёткое отношение **is-a** (`Admin` is a `User`). Если отношение «**содержит**» — лучше **композиция** (`Car has-a Engine`).',
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
                'answer' => '**Полиморфизм** (греч. «много форм») — один вызов метода даёт **разное поведение** в зависимости от конкретного класса объекта.

**Как работает в PHP:**

- Базовый тип задаётся `interface Shape { area(); }` или абстрактным классом.
- Конкретные реализации (`Circle`, `Square`) предоставляют свой `area()`.
- Клиентский код `function print(Shape $s) { echo $s->area(); }` **не знает** конкретного класса — работает с любым, кто реализует контракт.

**Что даёт:**

- **Расширяемость** — добавили `Triangle`, старый код не меняем.
- Заменяет **switch по типу** на полиморфный вызов.
- Основа DI и подмены реализаций в тестах.

Это **subtype polymorphism**. В PHP его дают `extends` и `implements`.',
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
                'answer' => '**Абстракция** — выделить **существенные** для задачи свойства и поведение, **игнорируя** мелочи.

**Аналогия:** ты пользуешься машиной через руль, педали и КПП. Что там внутри — карбюратор, инжектор, электромотор — для тебя неважно. Машина — это **абстракция** «средство передвижения».

**В коде:**

- **Интерфейс** (`PaymentGateway::pay()`) — описывает **что** объект умеет.
- **Конкретные реализации** (`StripeGateway`, `PayPalGateway`) — детали **как** скрыты внутри.
- Клиент работает с абстракцией — реализацию подменяют без правки клиента.

**Связь с другими принципами:**

- **Инкапсуляция** делает реализацию **закрытой**.
- **Абстракция** делает контракт **открытым и минимальным**.
- Вместе они дают **слабую связанность** и расширяемость.',
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
                'answer' => '**Да.** В PHP (как в Java / C# / C++) модификаторы видимости работают **на уровне класса**, а **не экземпляра**.

**Точная формулировка:**

> `private` значит **«доступно изнутри своего класса»**, а **не** «только своему `$this`».

**Где это нужно:**

- **Бинарные операции** над объектами одного типа — `Money::add(Money $other)`, `Vector::dot(Vector $other)`.
- **Сравнение по полям** — `equals(self $other)`.
- **Конструктор копирования** — `clone()`-вариации.
- **Внутренняя инкапсуляция** агрегатов без публичных геттеров.

**Сравнение PHP с другими языками:**

| Язык | Модификатор `private` |
|---|---|
| **PHP**, **Java**, **C#**, **C++** | **per-class** — все объекты класса видят private друг друга |
| **Swift**, **Ruby** | `private` — per-instance (только `self`); для per-class нужен `fileprivate`/`protected` |

**Формулировка для собеса:** «**`private` — per-class, не per-instance**».

**Что НЕ работает:** доступ к `private` **родителя** в наследнике (используй `protected`) и доступ к `private` **другого класса** в той же иерархии.',
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
                'answer' => 'Это **разные оси**, часто путают.

- **Абстракция** — про **дизайн API**. Выделить существенные операции, скрыть сложность. Отвечает на: «**что** объект умеет?» Реализуется через `interface`, `abstract class`.
- **Инкапсуляция** — про **защиту состояния**. Спрятать данные за `private`, дать методы, гарантирующие инварианты. Отвечает на: «**как** объект защищает себя?» Реализуется через модификаторы видимости, геттеры/сеттеры, `readonly`.

**Дополняют друг друга:**

- Без инкапсуляции абстракция **протекает** — клиент лезет внутрь.
- Без абстракции инкапсуляция превращается в **анемичный набор геттеров/сеттеров** без смысла.',
                'difficulty' => 2,
                'topic' => 'oop.four_principles',
                'code_example' => '<?php
// АБСТРАКЦИЯ: контракт "что умеет" - без деталей реализации
interface PaymentGateway
{
    public function pay(int $cents): bool;
}

// ИНКАПСУЛЯЦИЯ: внутренности скрыты, доступ только через методы
final class Account
{
    private int $balance = 0; // private = инкапсулировано

    public function deposit(int $amount): void
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException(\'positive only\');
        }
        $this->balance += $amount; // инвариант защищён
    }

    public function balance(): int { return $this->balance; }
}',
                'code_language' => 'php',
            ],
        ];
    }
}
