<?php

namespace Database\Seeders\Data\Categories\Oop;

class EventSourcingCqrs
{
    public static function all(): array
    {
        return [
            [
                'category' => 'ООП',
                'topic' => 'oop.event_sourcing_cqrs',
                'difficulty' => 5,
                'question' => 'Что такое Event Sourcing?',
                'answer' => '**Event Sourcing** — подход, при котором состояние системы хранится **не как текущий снимок**, а как **последовательность событий**, которые к нему привели.

**Пример со счётом:**

| Подход | Что хранится |
|---|---|
| **State-based** | `balance = 100` |
| **Event Sourcing** | `MoneyDeposited(50)`, `MoneyDeposited(80)`, `MoneyWithdrawn(30)` |

**Текущее состояние** = **replay** всех событий с нуля (`fromEvents()`).

**Плюсы:**

- **Полный аудит** — кто, когда, что сделал.
- **Time travel** — состояние на любую точку прошлого.
- **Новые проекции** добавляются без перезаписи данных — просто перепрогнать events.
- Естественно ложится на **CQRS** и **integration via events**.

**Минусы:**

- **Сложность** — события надо версионировать, делать `upcasters`.
- **Eventual consistency** между write-model и read-model.
- **Миграции событий** — нельзя просто `ALTER TABLE`.
- **Snapshot** нужен для больших агрегатов, иначе replay медленный.

**Когда брать:** регулируемый домен (финансы, медицина), сложная история состояний. **Когда нет:** простой CRUD.

**Важно:** `recordThat()` для **новых событий**, `mutate()` — только смена состояния (используется и при `replay`, и при `recordThat`, чтобы не задублировать event при `save`).',
                'code_example' => '<?php
abstract class Event { public \DateTimeImmutable $occurredAt; }
class MoneyDeposited extends Event {
    public function __construct(public readonly int $amount) {
        $this->occurredAt = new \DateTimeImmutable();
    }
}
class MoneyWithdrawn extends Event {
    public function __construct(public readonly int $amount) {
        $this->occurredAt = new \DateTimeImmutable();
    }
}

class Account
{
    private int $balance = 0;
    /** @var Event[] */
    private array $events = [];

    public function deposit(int $amount): void
    {
        $this->recordThat(new MoneyDeposited($amount));
    }

    public function withdraw(int $amount): void
    {
        if ($amount > $this->balance) {
            throw new \DomainException(\'Недостаточно средств\');
        }
        $this->recordThat(new MoneyWithdrawn($amount));
    }

    // Только мутация состояния, без записи в events (используется и при replay)
    private function mutate(Event $e): void
    {
        match (true) {
            $e instanceof MoneyDeposited => $this->balance += $e->amount,
            $e instanceof MoneyWithdrawn => $this->balance -= $e->amount,
            default => throw new \LogicException(\'Unknown event\'),
        };
    }

    // Новое событие: меняем состояние и пишем в журнал для последующего save
    private function recordThat(Event $e): void
    {
        $this->mutate($e);
        $this->events[] = $e;
    }

    // Восстановление состояния из истории (replay) - НЕ порождает новых событий
    public static function fromEvents(array $events): self
    {
        $account = new self();
        foreach ($events as $e) {
            $account->mutate($e); // только mutate, иначе события задублируются при save
        }
        return $account;
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.event_sourcing_cqrs',
                'difficulty' => 4,
                'question' => 'Что такое CQRS?',
                'answer' => '**CQRS (Command Query Responsibility Segregation)** — разделение операций **записи (`Command`)** и **чтения (`Query`)** на **разные модели**.

**Идея:** одни классы только **пишут** изменения, другие только **читают** данные. У каждой стороны — своя оптимизация.

**Разные требования у двух сторон:**

| Сторона | Что важно |
|---|---|
| **Write-model** (`Command`) | валидация, транзакции, инварианты, нормализация |
| **Read-model** (`Query`) | скорость, денормализация, кеш, индексы под выборки |

**Типичные read-storage:** read-only реплики, денормализованная таблица, `Elasticsearch`, `Redis`, materialized view.

**CQRS vs Event Sourcing — это РАЗНЫЕ паттерны:**

| Паттерн | Что делает |
|---|---|
| **CQRS** | разделяет **API** на команды и запросы |
| **Event Sourcing** | хранит **state** как лог событий |

- **CQRS без ES** — типично: write через агрегаты в Postgres-master, read из реплики или ES.
- **ES без CQRS** — редко, но возможно.
- **Вместе** — когда нужна **полная история** + быстрые проекции.

**90% реальных CQRS-систем работают без ES.** Не путай их.',
                'code_example' => '<?php
// Command - изменяет состояние, обычно возвращает void/id (не data)
final class CreateOrderCommand
{
    public function __construct(
        public string $userId,
        public array $items,
    ) {}
}

class CreateOrderHandler
{
    public function handle(CreateOrderCommand $cmd): void { /* пишем */ }
}

// Query - читает данные, не меняет состояние
final class GetUserOrdersQuery
{
    public function __construct(public string $userId) {}
}

class GetUserOrdersHandler
{
    public function handle(GetUserOrdersQuery $q): array { /* читаем */ return []; }
}',
                'code_language' => 'php',
            ],
        ];
    }
}
