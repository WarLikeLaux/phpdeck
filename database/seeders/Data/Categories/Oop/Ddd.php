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
                'answer' => 'DDD (предметно-ориентированное проектирование) - это подход к разработке сложных систем, в центре которого глубокое понимание предметной области. Идея: код должен отражать бизнес-домен, а не техническую реализацию. Делится на Strategic DDD (стратегические паттерны: Bounded Context, Context Map, Ubiquitous Language) и Tactical DDD (тактические паттерны: Entity, Value Object, Aggregate, Repository, Domain Service, Domain Event). DDD оправдан в проектах со сложной бизнес-логикой; для CRUD-приложений - оверкилл.',
                'code_example' => null,
                'code_language' => null,
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.ddd',
                'difficulty' => 4,
                'question' => 'Что такое Aggregate Root в DDD?',
                'answer' => 'Aggregate Root (корень агрегата) - это главный объект внутри группы связанных объектов (агрегата). Простыми словами: представь корзину покупок - она объединяет товары, скидки, итоговую сумму. Корзина - это Aggregate Root, всё взаимодействие извне идёт через неё, а не напрямую с товарами внутри. Это гарантирует согласованность данных: правила (например, лимит на товары) проверяются в одном месте. Запрет именно на МОДИФИКАЦИЮ внутренних сущностей извне (внешний код не должен ссылаться на child entities за пределами транзакции корня) и на прямые ссылки между агрегатами - они связываются только по id корня.',
                'code_example' => '<?php
class Order // Aggregate Root
{
    private array $items = [];

    public function addItem(Product $p, int $qty): void
    {
        if (count($this->items) >= 100) {
            throw new \DomainException(\'Лимит товаров\');
        }
        $this->items[] = new OrderItem($p, $qty); // правила в корне
    }

    // нельзя получить items напрямую и менять их
    public function items(): array
    {
        return $this->items;
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.ddd',
                'difficulty' => 4,
                'question' => 'Что такое Value Object в DDD?',
                'answer' => 'Value Object (объект-значение) - объект, у которого нет идентичности; он определяется только своими значениями. Если у двух VO одинаковые поля - они равны. Примеры: Money (100 USD), Address, DateRange, Email. Свойства: иммутабельны (не меняются после создания, новое значение - новый объект), сравниваются по значению, инкапсулируют валидацию. В отличие от Entity, у которой есть id и идентичность сохраняется во времени.',
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
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.ddd',
                'difficulty' => 4,
                'question' => 'Что такое Entity в DDD?',
                'answer' => 'Entity (сущность) - объект, у которого есть уникальный идентификатор (id) и идентичность сохраняется во времени, даже если меняются другие свойства. Простыми словами: пользователь может сменить имя, email, адрес, но это всё ещё тот же пользователь (с тем же id). Entity сравниваются по id, а не по полям. Примеры: User, Order, Article. Противоположность - Value Object, у которого идентичности нет.',
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
        $this->name = $name; // меняется, но id - тот же
    }

    public function equals(User $other): bool
    {
        return $this->id === $other->id; // по id, не по полям
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.ddd',
                'difficulty' => 4,
                'question' => 'Что такое Repository в DDD?',
                'answer' => 'Repository (репозиторий) - паттерн, абстрагирующий доступ к хранилищу агрегатов. Простыми словами: репозиторий выглядит как коллекция в памяти - find/save/remove - а внутри обращается к БД, кешу или внешнему API. Доменный код не знает о деталях хранения. Один репозиторий обычно работает с одним Aggregate Root. Это даёт возможность менять способ хранения без изменения бизнес-логики.',
                'code_example' => '<?php
interface UserRepository
{
    public function findById(string $id): ?User;
    public function save(User $user): void;
    public function remove(User $user): void;
}

// Реализация для PostgreSQL
class PostgresUserRepository implements UserRepository
{
    public function __construct(private \PDO $pdo) {}

    public function findById(string $id): ?User
    {
        // SELECT * FROM users WHERE id = :id ...
        return null;
    }
    public function save(User $user): void { /* INSERT/UPDATE */ }
    public function remove(User $user): void { /* DELETE */ }
}

// Доменный код работает с интерфейсом, не зная об SQL
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
                'answer' => 'Domain Service (доменный сервис) - объект, содержащий бизнес-логику, которая не принадлежит ни одной Entity или Value Object естественным образом. Живёт ВНУТРИ слоя домена (не application). Например, перевод денег между двумя счетами - это операция над двумя агрегатами, не принадлежит ни одному из них. Domain Service оперирует доменными объектами, не имеет состояния (stateless). Не путать с Application Service - тот оркестрирует use case (транзакции, события, авторизация) и живёт в application-слое.',
                'code_example' => '<?php
class MoneyTransferService // Domain Service
{
    public function transfer(
        Account $from,
        Account $to,
        Money $amount,
    ): void {
        $from->withdraw($amount);
        $to->deposit($amount);
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.ddd',
                'difficulty' => 4,
                'question' => 'Что такое Bounded Context в DDD?',
                'answer' => 'Bounded Context (ограниченный контекст) - граница, внутри которой модель имеет конкретное значение. Простыми словами: слово "продукт" в контексте продаж - это товар с ценой, а в контексте склада - это коробка с весом и габаритами. Это разные модели! BC разделяет систему на независимые куски, у каждого своя модель и язык. Между контекстами - явные интеграции (anti-corruption layer, shared kernel). Помогает бороться со сложностью больших доменов.',
                'code_example' => '<?php
// Контекст продаж: важна цена и наличие
namespace Sales;
final class Product
{
    public function __construct(
        public readonly string $sku,
        public readonly Money $price,
        public int $stock,
    ) {}
}

// Контекст склада: важны габариты и расположение
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
// Один и тот же SKU - разные модели в разных контекстах',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.ddd',
                'difficulty' => 4,
                'question' => 'Что такое Ubiquitous Language в DDD?',
                'answer' => 'Ubiquitous Language (вездесущий язык) - единый язык, на котором общаются разработчики, бизнес-аналитики и заказчики. Этот же язык используется в коде: имена классов, методов, переменных совпадают с терминами бизнеса. Простыми словами: если бизнес говорит "оформить заказ" - в коде должен быть метод placeOrder(), а не doStuff(). Это устраняет двусмысленность и потери при переводе требований в код. Язык живёт в рамках одного Bounded Context - в другом контексте те же слова могут значить иное.',
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
