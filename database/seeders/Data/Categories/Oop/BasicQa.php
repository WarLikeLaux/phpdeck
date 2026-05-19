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
                'answer' => 'Один вызов — разное поведение в зависимости от типа объекта. Если есть интерфейс Shape с методом area() и классы Circle, Square, Triangle, его реализующие, — функция printArea(Shape $s) одинаково работает со всеми, не зная конкретного класса. Добавил Hexagon — старая функция работает без изменений.',
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

function printArea(Shape $s): void { echo $s->area(); }',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.basic_qa',
                'difficulty' => 1,
                'question' => 'Что такое инкапсуляция простыми словами?',
                'answer' => 'Спрятать внутреннее состояние объекта за публичными методами. Свойства делают private/protected, доступ снаружи — только через методы, которые гарантируют правила (например, баланс не уйдёт в минус). Менять реализацию внутри можно без поломки кода, который пользуется объектом.',
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
                'answer' => 'Выделить ИЗ объекта только важное для задачи и спрятать остальное. Пользователь крутит руль и жмёт педали — детали работы двигателя ему не нужны. В коде это выражается интерфейсами и абстрактными классами: они описывают, что объект умеет, не раскрывая, как именно он это делает.',
                'code_example' => '<?php
interface PaymentGateway
{
    public function pay(int $cents): bool;
}

// Клиенту достаточно знать про pay()
function charge(PaymentGateway $g): void { $g->pay(100); }',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.basic_qa',
                'difficulty' => 2,
                'question' => 'Что значит "программируй на интерфейс, а не на реализацию"?',
                'answer' => 'Зависимости и переменные объявляй типом ИНТЕРФЕЙСА, а не конкретного класса. Тогда в любой момент конкретную реализацию можно подменить (другой провайдер, mock в тесте), и код, использующий интерфейс, не нужно трогать. Это основа гибкого ООП-дизайна, DIP и DI.',
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
