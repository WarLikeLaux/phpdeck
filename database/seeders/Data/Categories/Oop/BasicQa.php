<?php

namespace Database\Seeders\Data\Categories\Oop;

class BasicQa
{
    public static function all(): array
    {
        return [
            [
                'category' => 'ООП',
                'topic' => 'oop.basic_qa',
                'difficulty' => 1,
                'question' => 'Что такое полиморфизм простыми словами?',
                'answer' => '**Полиморфизм** — один вызов, разное поведение в зависимости от типа объекта.

Есть интерфейс `Shape` с методом `area()` и классы `Circle`, `Square`, `Triangle`, его реализующие. Функция `printArea(Shape $s)` одинаково работает со всеми, **не зная** конкретного класса.

Добавил `Hexagon` — старая функция работает без изменений. Это даёт **расширяемость**: новый тип = новый класс, существующий код не трогаем.

Опирается на **наследование** или **интерфейсы**.',
                'code_example' => '<?php
interface Shape { public function area(): float; }

class Circle implements Shape {
    public function __construct(private float $r) {}
    public function area(): float { return M_PI * $this->r ** 2; }
}

class Square implements Shape {
    public function __construct(private float $side) {}
    public function area(): float { return $this->side ** 2; }
}

function printArea(Shape $s): void
{
    // тот же $s->area() даёт РАЗНЫЙ результат в зависимости от класса
    echo $s->area() . PHP_EOL;
}

printArea(new Circle(5));  // 78.54...
printArea(new Square(4));  // 16',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.basic_qa',
                'difficulty' => 1,
                'question' => 'Что такое инкапсуляция простыми словами?',
                'answer' => '**Инкапсуляция** — спрятать внутреннее состояние объекта за публичными методами.

- Свойства делают `private` или `protected`.
- Доступ снаружи — **только** через методы, которые гарантируют правила (например, баланс не уйдёт в минус).
- Менять реализацию внутри можно без поломки кода, который пользуется объектом.',
                'code_example' => '<?php
class Account
{
    private int $balance = 0;

    public function deposit(int $amount): void
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException(\'positive only\');
        }
        $this->balance += $amount;
    }

    public function balance(): int { return $this->balance; }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.basic_qa',
                'difficulty' => 1,
                'question' => 'Что такое абстракция простыми словами?',
                'answer' => '**Абстракция** — выделить из объекта только важное для задачи и спрятать остальное.

Пользователь крутит руль и жмёт педали — детали работы двигателя ему не нужны. В коде это выражается **интерфейсами** и **абстрактными классами**: они описывают, **ЧТО** объект умеет, не раскрывая, **КАК** именно он это делает.

Клиентский код зависит от абстракции (`PaymentGateway`), а конкретную реализацию (`Stripe`, `PayPal`, мок в тесте) можно подменить.',
                'code_example' => '<?php
// Абстракция: только важный для клиента контракт
interface PaymentGateway
{
    public function pay(int $cents): bool;
}

// Конкретные реализации - детали скрыты
class StripeGateway implements PaymentGateway
{
    public function pay(int $cents): bool
    {
        // обращение к API Stripe скрыто внутри
        return true;
    }
}

class PayPalGateway implements PaymentGateway
{
    public function pay(int $cents): bool { return true; }
}

// Клиенту достаточно знать про pay() - тип не важен
function charge(PaymentGateway $g): void
{
    $g->pay(100);
}

charge(new StripeGateway());
charge(new PayPalGateway());',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.basic_qa',
                'difficulty' => 2,
                'question' => 'Что значит "программируй на интерфейс, а не на реализацию"?',
                'answer' => 'Зависимости и переменные объявляй **типом интерфейса**, а не конкретного класса.

**Что даёт:**

- **Подмена реализации** без правки клиента: `StripeGateway` → `PayPalGateway` → `FakeGateway` в тестах.
- **Слабая связанность** — клиент знает только контракт, не детали.
- Основа принципов **DIP** (Dependency Inversion) и **DI** (Dependency Injection).

**Признак нарушения:** в конструкторе или typehint стоит `new ConcreteClass()` или `function pay(StripeGateway $g)` — клиент привязан к конкретике. Заменяй на интерфейс `PaymentGateway`.',
                'code_example' => '<?php
// Хорошо: зависимость от интерфейса
class OrderService
{
    public function __construct(private PaymentGateway $gateway) {}
}

// Можно дать StripeGateway, PaypalGateway или FakeGateway в тесте
$service = new OrderService(new StripeGateway());',
                'code_language' => 'php',
            ],
        ];
    }
}
