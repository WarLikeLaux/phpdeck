<?php

namespace Database\Seeders\Data\Categories\Oop;

class Ddd
{
    public static function all(): array
    {
        return [
            [
                'category' => 'ООП',
                'topic' => 'oop.ddd',
                'difficulty' => 4,
                'question' => 'Что такое Domain-Driven Design (DDD)?',
                'answer' => '**DDD (Domain-Driven Design)** — подход к разработке сложных систем, в центре которого **глубокое понимание предметной области**. Код отражает **бизнес-домен**, а не техническую реализацию.

**Делится на два уровня:**

| Уровень | Что включает |
|---|---|
| **Strategic DDD** | `Bounded Context`, `Context Map`, `Ubiquitous Language`, типы интеграций |
| **Tactical DDD** | `Entity`, `ValueObject`, `Aggregate`, `Repository`, `Domain Service`, `Domain Event` |

**Когда оправдан:**

- Сложная бизнес-логика, **не CRUD**.
- Долгоживущий продукт с активным участием экспертов домена.
- Команда, готовая инвестировать в **общий язык** с бизнесом.

**Когда оверкилл:**

- Простые CRUD-приложения.
- Прототипы и MVP без устоявшегося домена.
- Тонкая обёртка над БД — добавит сложности без выигрыша.

**Главная идея:** **модель → код → разговор с бизнесом** идут на одном языке.',
                'code_example' => null,
                'code_language' => null,
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.ddd',
                'difficulty' => 4,
                'question' => 'Что такое Aggregate Root в DDD?',
                'answer' => '**Aggregate Root (корень агрегата)** — главный объект внутри группы связанных объектов (**агрегата**). Всё взаимодействие извне идёт **через него**, а не напрямую с child-сущностями внутри.

**Пример — корзина покупок:** корзина объединяет товары, скидки, итоговую сумму. **Корзина = Aggregate Root**, `OrderItem` — child entity внутри.

**Зачем нужен:**

- **Согласованность данных** — все инварианты проверяются в одном месте.
- **Транзакционная граница** — один агрегат = одна транзакция.
- **Понятный API** — внешний код не лазит в кишки.

**Жёсткие правила:**

1. **Нельзя модифицировать** child-сущности извне — только через методы корня.
2. **Нельзя ссылаться** на child entities из других агрегатов.
3. Связь между агрегатами — **только по `id` корня** (а не по ссылке на объект).
4. Изменения внутри агрегата атомарны — `save(Aggregate)` сохраняет всё разом.

**Анти-паттерн:** `$order->getItems()[0]->setQuantity(5)` — обход корня, инварианты не проверяются.',
                'code_example' => '<?php
class Order // Aggregate Root
{
    /** @var OrderItem[] */
    private array $items = [];

    public function addItem(Product $p, int $qty): void
    {
        if (count($this->items) >= 100) {
            throw new \DomainException(\'Лимит товаров\');
        }
        $this->items[] = new OrderItem($p, $qty); // правила проверяются в корне
    }

    public function removeItem(string $itemId): void
    {
        // изменение child проходит через корень
        $this->items = array_filter(
            $this->items,
            fn (OrderItem $i) => $i->id() !== $itemId
        );
    }

    /** @return OrderItem[] readonly snapshot */
    public function items(): array
    {
        return $this->items; // только чтение, мутация через addItem/removeItem
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.ddd',
                'difficulty' => 4,
                'question' => 'Что такое Value Object в DDD?',
                'answer' => '**ValueObject (объект-значение)** — объект **без идентичности**: он определяется только своими значениями. Если у двух VO одинаковые поля — они **равны**.

**Примеры:** `Money` (`100 USD`), `Address`, `DateRange`, `Email`, `Coordinates`.

**Ключевые свойства:**

- **Иммутабельность** — после создания не меняется; новое значение = **новый объект**.
- **Сравнение по значению** — `equals()`, не по ссылке.
- **Инкапсуляция валидации** — невозможно создать невалидный VO.
- **Самодостаточность** — операции возвращают новый VO (`add()`, `withCurrency()`).

**ValueObject vs Entity:**

| Свойство | `ValueObject` | `Entity` |
|---|---|---|
| Идентичность | по значению | по `id` |
| Изменчивость | **иммутабелен** | мутабельна |
| Сравнение | `equals()` по полям | по `id` |
| Жизненный цикл | заменяется целиком | существует во времени |

**В PHP** удобно через `final readonly class` (PHP 8.2+).',
                'code_example' => '<?php
final readonly class Money
{
    public function __construct(
        public int $amount,
        public string $currency,
    ) {
        if ($amount < 0) {
            throw new \InvalidArgumentException(\'Сумма не может быть отрицательной\');
        }
    }

    public function add(Money $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new \DomainException(\'Валюты не совпадают\');
        }
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount
            && $this->currency === $other->currency;
    }
}

// иммутабельность: new Money(100, \'USD\') ===-эквивалентен любому другому Money(100, \'USD\')',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.ddd',
                'difficulty' => 4,
                'question' => 'Что такое Entity в DDD?',
                'answer' => '**Entity (сущность)** — объект с **уникальным идентификатором (`id`)**, чья идентичность **сохраняется во времени**, даже если меняются другие свойства.

**Пример:** пользователь может сменить имя, email, адрес — но это **всё тот же пользователь** с тем же `id`.

**Ключевые свойства:**

- **Сравнение по `id`**, не по полям — `equals()` смотрит только на идентификатор.
- **Жизненный цикл** — создание, изменения, удаление.
- **Мутабельна** — методы меняют состояние (но через бизнес-операции, не сеттеры).
- **Инкапсулирует инварианты** — `changeEmail()` валидирует, не позволяет невалидное состояние.

**Entity vs ValueObject:**

| Признак | `Entity` | `ValueObject` |
|---|---|---|
| Идентичность | `id` обязателен | нет |
| Изменчивость | можно менять поля | **иммутабелен** |
| Сравнение | по `id` | по значению |

**Когда сомневаешься** — спроси: «Важно ли отличать два экземпляра с одинаковыми полями?». Да → `Entity`. Нет → `ValueObject`.',
                'code_example' => '<?php
class User // Entity
{
    public function __construct(
        public readonly string $id,
        private string $name,
        private string $email,
    ) {}

    public function changeName(string $name): void
    {
        $this->name = $name; // поля меняются, но id - тот же
    }

    public function equals(User $other): bool
    {
        return $this->id === $other->id; // идентичность по id, не по полям
    }
}

$u1 = new User(\'u-1\', \'Alice\', \'a@x\');
$u2 = new User(\'u-1\', \'Bob\',   \'b@x\'); // тот же user после rename
$u1->equals($u2); // true',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.ddd',
                'difficulty' => 4,
                'question' => 'Что такое Repository в DDD?',
                'answer' => '**Repository (репозиторий)** — паттерн, **абстрагирующий доступ к хранилищу агрегатов**. Снаружи выглядит как **коллекция в памяти** (`find`, `save`, `remove`), а внутри обращается к БД, кешу, внешнему API.

**Зачем нужен:**

- **Доменный код не знает** о деталях хранения — ни SQL, ни ORM в бизнес-слое.
- **Подменяемость** — `InMemoryRepository` для тестов, `PostgresRepository` в проде.
- **Чёткий API** — `findById`, `save`, `remove` — а не «универсальный QueryBuilder».

**Правила:**

1. **Один репозиторий = один Aggregate Root**. Нет `OrderItemRepository` — только `OrderRepository`.
2. Возвращает **полные агрегаты**, а не плоские строки.
3. **Интерфейс — в домене**, реализация — в инфраструктуре (Hexagonal).

**Repository vs DAO:**

| Признак | `Repository` (DDD) | `DAO` |
|---|---|---|
| Работает с | агрегатами | таблицами |
| Возвращает | целые объекты домена | строки/DTO |
| Где живёт интерфейс | в домене | в data-слое |

**В Laravel:** Eloquent-модель часто **сразу выступает** Active Record + Repository. В строгом DDD интерфейс выносят отдельно.',
                'code_example' => '<?php
// интерфейс - в слое домена
interface UserRepository
{
    public function findById(string $id): ?User;
    public function save(User $user): void;
    public function remove(User $user): void;
}

// реализация - в инфраструктурном слое
class PostgresUserRepository implements UserRepository
{
    public function __construct(private \PDO $pdo) {}

    public function findById(string $id): ?User
    {
        // SELECT ... FROM users WHERE id = :id
        return null;
    }
    public function save(User $user): void { /* INSERT/UPDATE */ }
    public function remove(User $user): void { /* DELETE */ }
}

// доменный/прикладной код знает только интерфейс
class RegisterUser
{
    public function __construct(private UserRepository $users) {}
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.ddd',
                'difficulty' => 4,
                'question' => 'Что такое Domain Service в DDD?',
                'answer' => '**Domain Service (доменный сервис)** — объект с **бизнес-логикой**, которая **не принадлежит** ни одной `Entity` или `ValueObject` естественным образом. Живёт **в слое домена**, не в application.

**Пример:** перевод денег между двумя счетами. Это операция **над двумя агрегатами**, не принадлежащая ни одному.

**Ключевые свойства:**

- **Stateless** — без состояния, только методы.
- **Имена на доменном языке** — `MoneyTransferService`, не `AccountManager`.
- **Оперирует доменными объектами**, не примитивами.
- Содержит **бизнес-правила**, а не оркестрацию инфраструктуры.

**Domain Service vs Application Service:**

| Признак | `Domain Service` | `Application Service` |
|---|---|---|
| Слой | домен | application |
| Знает про | агрегаты и VO | use case целиком |
| Транзакции | нет | **да** — `DB::transaction()` |
| События | публикует через агрегат | **диспатчит** наружу |
| Авторизация | нет | **да** |
| Пример | `MoneyTransferService` | `RegisterUserUseCase` |

**Анти-паттерн:** запихнуть всё в Domain Service вместо метода в `Entity` — это **anaemic domain model**. Сначала ищи место в агрегате, и только потом — сервис.',
                'code_example' => '<?php
// Domain Service: операция между двумя агрегатами
class MoneyTransferService
{
    public function transfer(
        Account $from,
        Account $to,
        Money $amount,
    ): void {
        $from->withdraw($amount); // инварианты внутри Account
        $to->deposit($amount);
    }
}

// Application Service: оркестрирует use case вокруг доменного сервиса
class TransferMoneyUseCase
{
    public function __construct(
        private AccountRepository $accounts,
        private MoneyTransferService $transfer,
    ) {}

    public function execute(string $fromId, string $toId, Money $amount): void
    {
        \DB::transaction(function () use ($fromId, $toId, $amount) {
            $from = $this->accounts->findById($fromId);
            $to   = $this->accounts->findById($toId);
            $this->transfer->transfer($from, $to, $amount);
            $this->accounts->save($from);
            $this->accounts->save($to);
        });
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.ddd',
                'difficulty' => 4,
                'question' => 'Что такое Bounded Context в DDD?',
                'answer' => '**Bounded Context (ограниченный контекст)** — **граница**, внутри которой модель и язык имеют **конкретное значение**.

**Пример со словом «Продукт»:**

| Контекст | Что такое «Продукт» |
|---|---|
| **Sales** | товар с **ценой** и наличием |
| **Warehouse** | коробка с **весом** и габаритами |
| **Catalog** | карточка с **описанием** и фото |
| **Shipping** | груз с **габаритами** и зоной доставки |

**Один и тот же SKU — разные модели в разных контекстах.**

**Зачем нужен:**

- Разделяет систему на **независимые куски** со своей моделью.
- **Свой `Ubiquitous Language`** внутри контекста — никакой путаницы.
- Контексты — **кандидаты на микросервисы** (но не обязательно).

**Интеграция между контекстами:**

- **Shared Kernel** — общий маленький модуль (риск связности).
- **Customer-Supplier** — один зависит от другого, есть договорённость.
- **Anti-Corruption Layer (`ACL`)** — слой-переводчик, защищает свою модель от чужой.
- **Open Host Service** — публичный API контекста.
- **Published Language** — общий формат обмена (JSON Schema, protobuf).

**`Context Map`** — диаграмма всех контекстов и их связей.',
                'code_example' => '<?php
// Контекст Sales: важна цена и наличие
namespace Sales;

final class Product
{
    public function __construct(
        public readonly string $sku,
        public readonly Money $price,
        public int $stock,
    ) {}
}

// Контекст Warehouse: важны габариты и расположение
namespace Warehouse;

final class Product
{
    public function __construct(
        public readonly string $sku,
        public readonly float $weightKg,
        public readonly Dimensions $size,
        public readonly string $shelf,
    ) {}
}

// один и тот же SKU - разные модели в разных контекстах
// связь только через ACL или published language',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.ddd',
                'difficulty' => 4,
                'question' => 'Что такое Ubiquitous Language в DDD?',
                'answer' => '**Ubiquitous Language (вездесущий язык)** — **единый язык** между разработчиками, бизнес-аналитиками и заказчиками, который **дословно живёт в коде**.

**Правило:** если бизнес говорит «оформить заказ» — в коде **должен быть** метод `placeOrder()`, а не `doStuff()` / `process()` / `update()`.

**Что устраняет:**

- **Двусмысленность** — все говорят одними словами.
- **Потери при переводе** требований в код.
- Разрыв «**аналитик пишет одно, программист понимает другое**».

**Правила использования:**

1. Имена классов, методов, переменных = **термины бизнеса**.
2. **Никаких** технических `Manager`, `Helper`, `Processor` — это запах.
3. Язык живёт **внутри `Bounded Context`** — в другом контексте те же слова могут значить иное.
4. Если бизнес поменял термин — **меняй и код** (рефакторинг имён).

**Признак плохого UL:**

- Метод называется `update($data)` — но бизнес говорит «отгрузить», «отменить», «оплатить» — три **разные операции**, не одна.
- В коде термины из БД (`OrderTable`) — а не из домена.',
                'code_example' => '<?php
// Плохо: технические термины, оторванные от бизнеса
class OrderManager
{
    public function process(int $id, int $status): bool { /* ... */ }
    public function update(int $id, array $data): void { /* ... */ }
}

// Хорошо: код говорит на языке бизнеса
class Order
{
    public function place(): void { /* оформить */ }
    public function pay(Money $amount): void { /* оплатить */ }
    public function ship(Address $to): void { /* отгрузить */ }
    public function cancel(string $reason): void { /* отменить */ }
}

// В разговоре с бизнесом и в коде - одни и те же слова',
                'code_language' => 'php',
            ],
        ];
    }
}
