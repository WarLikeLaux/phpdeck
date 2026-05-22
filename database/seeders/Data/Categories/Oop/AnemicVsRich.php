<?php

namespace Database\Seeders\Data\Categories\Oop;

class AnemicVsRich
{
    public static function all(): array
    {
        return [
            [
                'category' => 'ООП',
                'topic' => 'oop.anemic_vs_rich',
                'difficulty' => 4,
                'question' => 'Anemic vs Rich domain model - в чём разница?',
                'answer' => '**Сравнение моделей:**

| Признак | **Anemic** (анемичная) | **Rich** (богатая) |
|---|---|---|
| Где данные | в объекте | в объекте |
| Где **поведение** | в сервисах | **в объекте** |
| Стиль | процедурный с геттерами/сеттерами | ООП |
| Инкапсуляция | разрушена сеттерами | защищена методами |
| Защита инвариантов | в сервисах (легко обойти) | **в самом объекте** |
| Имена | `OrderService::cancel($order)` | `$order->cancel()` |

**Anemic** — `Order` это просто **сумка данных** с `getStatus()`/`setStatus()`. Вся логика — в `OrderService`. **Anti-pattern** в DDD (Martin Fowler).

**Rich** — `Order` содержит **и данные, и поведение**. `$order->cancel()` сам проверяет, что не отгружено. Объект **гарантирует** валидное состояние.

**Почему Rich лучше:**

- **Инварианты защищены** — невозможно создать невалидный `Order`.
- **Понятный API** — `$order->cancel()`, а не `OrderService::doStuff($order, "cancel")`.
- **Меньше дублирования** — правило `cancel` в одном месте, а не в каждом сервисе.
- **Тестируется без моков** — просто новый `Order`, без `OrderRepository`.

**Когда anemic оправдан:** очень простой CRUD без бизнес-правил, либо DTO между слоями.',
                'code_example' => '<?php
// Anemic - объект-сумка, логика снаружи
class OrderAnemic
{
    public string $status;
    public \DateTime $createdAt;
}
class OrderService
{
    public function cancel(OrderAnemic $o): void
    {
        if ($o->status === \'shipped\') throw new \Exception();
        $o->status = \'cancelled\';
    }
}

// Rich - логика внутри объекта
class Order
{
    private string $status = \'new\';
    public function cancel(): void
    {
        if ($this->status === \'shipped\') {
            throw new \DomainException(\'Нельзя отменить отгруженный\');
        }
        $this->status = \'cancelled\';
    }
}',
                'code_language' => 'php',
            ],
        ];
    }
}
