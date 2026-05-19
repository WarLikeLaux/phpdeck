<?php

namespace Database\Seeders\Data\Categories\Oop;

class Misc
{
    public static function all(): array
    {
        return [
            [
                'category' => 'ООП',
                'topic' => 'oop.misc',
                'difficulty' => 2,
                'question' => 'Что такое final-классы и методы?',
                'answer' => 'Ключевое слово final запрещает наследование класса и/или переопределение метода. final class - от него нельзя наследоваться. final method - его нельзя переопределить в потомках. Используется для защиты дизайна: явно показывает, что класс/метод не предназначен для расширения. Помогает избежать неожиданных проблем с LSP и инкапсуляцией. Совет от Effective Java/Sandi Metz: "по умолчанию делайте классы final, наследование разрешайте осознанно".',
                'code_example' => '<?php
final class Money
{
    // от этого класса нельзя наследоваться
}

class Service
{
    final public function critical(): void
    {
        // нельзя переопределить в потомках
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.misc',
                'difficulty' => 3,
                'question' => 'Что такое магические методы в PHP?',
                'answer' => 'Магические методы - специальные методы PHP, начинающиеся с двух подчёркиваний. Они вызываются автоматически в определённых ситуациях. Основные: __construct/__destruct, __get/__set/__isset/__unset (для свойств), __call/__callStatic (для несуществующих методов), __toString, __invoke (вызов объекта как функции), __clone (клонирование), __sleep/__wakeup, __serialize/__unserialize. Используются осторожно: они скрывают поведение и усложняют отладку.',
                'code_example' => '<?php
class Container
{
    private array $data = [];

    public function __get(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }
    public function __set(string $name, mixed $value): void
    {
        $this->data[$name] = $value;
    }
    public function __invoke(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }
}

$c = new Container();
$c->foo = 42;     // __set
echo $c->foo;     // __get
echo $c(\'foo\'); // __invoke',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.misc',
                'difficulty' => 3,
                'question' => 'Что такое late static binding (позднее статическое связывание)?',
                'answer' => 'Механизм, позволяющий внутри родительского кода ссылаться на ФАКТИЧЕСКИЙ класс, через который сделан вызов. self:: указывает на класс, ГДЕ написан код (раннее связывание); static:: — на класс, ОТ которого вызвали (с учётом наследования). Нужен для фабричных методов в иерархии: Model::create() с new static() вернёт User, если вызвали User::create(). С PHP 8.0 для этого есть возвращаемый тип static.',
                'code_example' => '<?php
class Model
{
    public static function create(): static
    {
        return new static(); // создастся фактический класс
    }
}

class User extends Model {}

$u = User::create(); // вернёт User, а не Model',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.misc',
                'difficulty' => 2,
                'question' => 'Что такое анонимный класс?',
                'answer' => 'Анонимный класс (anonymous class) - класс без имени, объявленный и инстанцированный одним выражением. Появился в PHP 7.0. Удобен для одноразовых реализаций интерфейсов (например, для тестов или мелких mock-объектов), для inline-стратегий, без создания отдельного файла.',
                'code_example' => '<?php
interface Logger {
    public function log(string $m): void;
}

function doWork(Logger $logger): void
{
    $logger->log(\'work\');
}

doWork(new class implements Logger {
    public function log(string $m): void
    {
        echo $m;
    }
});',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.misc',
                'difficulty' => 3,
                'question' => 'Чем агрегация отличается от композиции?',
                'answer' => 'Оба отношения - "часть-целое" (has-a), но: Композиция (сильная) - часть не существует без целого, целое управляет жизненным циклом части (например, дом и комнаты - удалили дом, удалились комнаты). Агрегация (слабая) - часть может существовать самостоятельно, целое лишь использует её (например, университет и студенты - универ закрылся, студенты остались). В коде разница часто в том, кто создаёт зависимость: при композиции - внутри объекта, при агрегации - передаётся снаружи (DI).',
                'code_example' => '<?php
// Композиция: Engine создаётся внутри Car, живёт с ним
class Car
{
    private Engine $engine;
    public function __construct()
    {
        $this->engine = new Engine();
    }
}

// Агрегация: Driver передаётся снаружи, может ездить на разных машинах
class Vehicle
{
    public function __construct(private Driver $driver) {}
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.misc',
                'difficulty' => 3,
                'question' => 'Что такое immutable объект?',
                'answer' => 'Immutable (неизменяемый) объект - объект, состояние которого нельзя изменить после создания. Любые операции, которые "меняют" его, на самом деле возвращают новый объект. Плюсы: безопасность в многопоточных средах, можно использовать как ключ в кешах, проще отлаживать (значение не меняется), исключает баги из-за случайного изменения. В PHP 8.1+ удобно делать через readonly. Классический пример - Value Objects, DateTimeImmutable.',
                'code_example' => '<?php
final readonly class Point
{
    public function __construct(
        public float $x,
        public float $y,
    ) {}

    public function moveX(float $dx): self
    {
        // не меняем this, возвращаем новый
        return new self($this->x + $dx, $this->y);
    }
}

$p1 = new Point(1, 2);
$p2 = $p1->moveX(5); // p1 не изменился',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.misc',
                'difficulty' => 3,
                'question' => 'Что такое DTO (Data Transfer Object)?',
                'answer' => 'DTO (объект передачи данных) - простой объект для переноса данных между слоями приложения. Содержит только публичные свойства (или геттеры) без бизнес-логики. Часто иммутабелен. Используется на границах: API-запрос/ответ, передача данных из контроллера в сервис, между микросервисами. В PHP 8 удобно через readonly + constructor promotion. Не путать с Value Object: VO имеет валидацию и поведение, DTO - просто данные.',
                'code_example' => '<?php
final readonly class CreateUserDto
{
    public function __construct(
        public string $name,
        public string $email,
        public int $age,
    ) {}
}

class UserController
{
    public function create(Request $r): void
    {
        $dto = new CreateUserDto(
            $r->input(\'name\'),
            $r->input(\'email\'),
            (int) $r->input(\'age\'),
        );
        $this->service->create($dto);
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.misc',
                'difficulty' => 4,
                'question' => 'Что такое covariance и contravariance в PHP?',
                'answer' => 'Это правила, как наследник может менять типы параметров и возврата у переопределяемого метода (PHP 7.4+). Covariance — возвращаемый тип можно СУЗИТЬ (Animal → Dog). Contravariance — тип параметра можно РАСШИРИТЬ (Dog → Animal). Цель — соблюсти LSP: код, написанный против родителя, должен корректно работать с любым наследником. Типы свойств — инвариантны: переопределить тип поля в наследнике нельзя (Fatal error). Если нужно — используют геттер/сеттер вместо публичного поля.',
                'code_example' => '<?php
class Animal {}
class Dog extends Animal {}

class Shelter
{
    public function adopt(): Animal { return new Animal(); }
}

class DogShelter extends Shelter
{
    // ковариантность: возвращаем более конкретный тип
    public function adopt(): Dog { return new Dog(); }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.misc',
                'difficulty' => 3,
                'question' => 'Что такое перегрузка методов (method overloading) и есть ли она в PHP?',
                'answer' => 'Терминологическая ловушка PHP. В C++/Java перегрузка — несколько методов с одним именем и разными сигнатурами. В этом смысле в PHP перегрузки НЕТ: два метода с одним именем — fatal error «Cannot redeclare method». А в документации PHP «overloading» — это совсем другое: магические методы __get/__set/__call/__callStatic, которые перехватывают обращения к несуществующим свойствам и методам. Если на собесе спросили про перегрузку — сначала уточни, какой смысл. Имитировать классическую перегрузку лучше через именованные фабрики: Money::fromCents(), Money::fromDollars().',
                'code_example' => '<?php
class Money
{
    private function __construct(public int $amount, public string $currency) {}

    // именованные "конструкторы" вместо перегрузки
    public static function fromCents(int $cents, string $cur): self
    {
        return new self($cents, $cur);
    }
    public static function fromDollars(float $dollars): self
    {
        return new self((int)($dollars * 100), \'USD\');
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.misc',
                'difficulty' => 4,
                'question' => 'Что такое Open Recursion и проблема fragile base class?',
                'answer' => 'Open recursion — когда метод родителя через $this вызывает другой метод того же объекта, а потомок переопределяет этот метод. Гибко, но опасно: изменения в родителе ломают потомков (fragile base class). Решения: помечать «хук-методы» final, либо выносить расширяемость через композицию (Strategy) с явным контрактом — а не через наследование.',
                'code_example' => '<?php
class Counter
{
    private int $count = 0;

    public function add(int $n): void
    {
        $this->count += $n;
    }

    // open recursion: addMany использует add через $this
    public function addMany(array $nums): void
    {
        foreach ($nums as $n) {
            $this->add($n);
        }
    }
}

class LoggingCounter extends Counter
{
    public function add(int $n): void
    {
        error_log("add($n)");
        parent::add($n);
    }
    // addMany унаследован, но уже вызывает наш add()!
    // Если в родителе изменят addMany на оптимизированный вариант
    // (sum + один add), логи начнут сыпаться по-другому - потомок сломан.
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.misc',
                'difficulty' => 3,
                'question' => 'Что такое typed properties (типизированные свойства)?',
                'answer' => 'С PHP 7.4 у свойств можно объявить тип: public string $name; public ?int $age = null. PHP проверяет тип при записи. Поддерживаются скаляры, объекты, array, iterable, nullable (?T), self/parent, union (int|string, PHP 8.0), intersection (Foo&Bar, PHP 8.1), mixed. Типы static/never — только для возврата метода, не для свойства. Если у типизированного свойства нет дефолта и его не инициализировали — чтение бросит Error «uninitialized».',
                'code_example' => '<?php
class Profile
{
    public string $name;                 // обязательно инициализировать
    public ?int $age = null;             // nullable со значением по умолчанию
    public array $tags = [];
    public int|string $id;               // union (PHP 8.0)
    public \Countable&\Iterator $col;    // intersection (PHP 8.1)
    public (\Countable&\Iterator)|null $maybeCol = null; // DNF (PHP 8.2)
    public readonly string $hash;        // readonly (PHP 8.1)
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.misc',
                'difficulty' => 3,
                'question' => 'Что такое Active Record vs Data Mapper?',
                'answer' => 'Active Record - паттерн, в котором объект представляет строку в БД и сам умеет себя сохранять/удалять/искать ($user->save()). Плюсы: просто, удобно для CRUD. Минусы: смешивает доменную логику и инфраструктуру, нарушает SRP, сложнее тестировать. Пример - Eloquent в Laravel. Data Mapper - паттерн, в котором есть отдельный объект-маппер, который перекладывает данные между объектами и БД ($mapper->save($user)). Объект домена не знает о хранилище. Лучше для DDD и сложной логики. Пример - Doctrine.',
                'code_example' => '<?php
// Active Record (Eloquent)
$user = new User();
$user->name = \'Иван\';
$user->save(); // объект сам пишет в БД

// Data Mapper (Doctrine)
$user = new User(\'Иван\'); // чистый POPO
$entityManager->persist($user);
$entityManager->flush(); // маппер пишет в БД',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.misc',
                'difficulty' => 3,
                'question' => 'Что такое Null Object паттерн?',
                'answer' => 'Null Object - объект, имитирующий поведение "ничего" вместо null. Это позволяет избежать проверок на null в клиентском коде. Реализует тот же интерфейс, что и реальный объект, но методы возвращают пустые/нейтральные значения. Например, NullLogger ничего не пишет; NullUser - анонимный гость с пустыми правами. Делает код чище и безопаснее.',
                'code_example' => '<?php
interface Logger
{
    public function log(string $m): void;
}

class FileLogger implements Logger
{
    public function log(string $m): void { /* пишем */ }
}

class NullLogger implements Logger
{
    public function log(string $m): void { /* ничего */ }
}

class Service
{
    public function __construct(private Logger $logger = new NullLogger()) {}
    public function run(): void
    {
        $this->logger->log(\'work\'); // не нужно if($this->logger)
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.misc',
                'difficulty' => 1,
                'question' => 'YAGNI, KISS, DRY - что это?',
                'answer' => 'Это базовые принципы хорошего кода. DRY (Don\'t Repeat Yourself) - не повторяйся: одно знание - в одном месте. Дублирование логики - источник багов при правках. Важная оговорка: DRY - про знание, а не про похожий код; два случайно похожих куска лучше не объединять (rule of three). KISS (Keep It Simple, Stupid) - делайте проще: не усложняйте, пока не нужно. Простой код легче читать и поддерживать. YAGNI (You Aren\'t Gonna Need It) - не пишите код "на будущее" в надежде, что пригодится. Чаще всего не пригодится, а лишний код придётся поддерживать. Принципы взаимодополняют SOLID.',
                'code_example' => null,
                'code_language' => null,
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.misc',
                'difficulty' => 4,
                'question' => 'Что такое cohesion на уровне модулей и классов?',
                'answer' => 'Cohesion (сплочённость) показывает, насколько элементы внутри одного модуля связаны общей целью. Высокая cohesion - класс делает одно дело и делает его хорошо. Низкая - класс мешает разнородные функции. Виды (от плохой к хорошей): coincidental (случайная), logical (функции одной категории), temporal (вызываются вместе), procedural (выполняются последовательно), communicational (работают с одними данными), sequential (выход одного - вход другого), functional (всё для одной задачи) - идеал. Высокая cohesion - следствие SRP.',
                'code_example' => '<?php
// Низкая cohesion: разнородные методы в одном классе
class Utility
{
    public function calculateTax(Money $m): Money {}
    public function sendEmail(string $to, string $body): void {}
    public function parseCsv(string $file): array {}
    public function hashPassword(string $pwd): string {}
}

// Высокая cohesion: класс сосредоточен на одной задаче
class TaxCalculator
{
    public function __construct(private TaxRateProvider $rates) {}
    public function calculate(Money $m, Country $c): Money {}
    public function applyDiscount(Money $tax, Discount $d): Money {}
    public function totalWithTax(Money $base, Country $c): Money {}
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.misc',
                'difficulty' => 4,
                'question' => 'Что такое coupling на уровне модулей и классов?',
                'answer' => 'Coupling (зацепление, связанность) показывает, насколько модули зависят друг от друга. Виды от плохого к хорошему: content (один модуль лезет во внутренности другого), common (общие глобальные данные), control (один управляет логикой другого через флаги), stamp (передают сложные структуры, но используют только часть), data (передают только нужные параметры), message coupling (взаимодействие только через интерфейсы) - идеал. Низкое coupling - результат DIP, ISP, инкапсуляции и Law of Demeter.',
                'code_example' => '<?php
// Control coupling: флаг управляет логикой чужого модуля
class Reporter
{
    public function build(array $data, bool $asPdf): string
    {
        if ($asPdf) return $this->toPdf($data);
        return $this->toHtml($data);
    }
}

// Stamp coupling: передаём весь User, хотя нужен только email
class Mailer
{
    public function notify(User $user): void
    {
        mail($user->email, \'Hi\', \'...\'); // используем одно поле из десяти
    }
}

// Message coupling (хорошо): зависим от узкого интерфейса
interface EmailRecipient { public function email(): string; }
class Mailer2
{
    public function notify(EmailRecipient $r): void
    {
        mail($r->email(), \'Hi\', \'...\');
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.misc',
                'difficulty' => 3,
                'question' => 'Что такое паттерн Specification?',
                'answer' => 'Условие выборки или проверки оформлено как объект с методом isSatisfiedBy($entity). Спецификации можно комбинировать через AndSpec, OrSpec, NotSpec. Зачем: избавиться от десятков методов вроде findActiveUsersOlderThan() в репозитории — клиент сам собирает нужное условие из кирпичиков. В Eloquent похожая роль у query scopes.',
                'code_example' => '<?php
interface Spec { public function isSatisfiedBy(User $u): bool; }

class IsActive implements Spec {
    public function isSatisfiedBy(User $u): bool { return $u->bannedAt === null; }
}

class AndSpec implements Spec {
    public function __construct(private Spec $a, private Spec $b) {}
    public function isSatisfiedBy(User $u): bool {
        return $this->a->isSatisfiedBy($u) && $this->b->isSatisfiedBy($u);
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.misc',
                'difficulty' => 3,
                'question' => 'Что такое анти-паттерн Primitive Obsession и как с ним бороться?',
                'answer' => 'Primitive Obsession — использование базовых типов (string, int, array) там, где у домена есть собственные правила: string $email, int $userId, int $cents. Проблемы: валидация размазана по коду, легко перепутать порядок аргументов (charge($amount, $userId)), нельзя различить рубли и доллары — обе int. Решение — Value Objects: класс Email валидирует e-mail в конструкторе, Money хранит сумму и валюту, UserId — числовой id. Тип в сигнатуре сразу гарантирует контракт.',
                'code_example' => '<?php
// ❌ Primitive obsession
class OrderService
{
    public function place(int $userId, string $email, int $amountCents, string $currency): void
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException;
        if ($amountCents <= 0) throw new InvalidArgumentException;
        if (! in_array($currency, ["USD", "EUR", "RUB"])) throw new InvalidArgumentException;
        // и так в каждом методе сервиса
    }
}

// ✅ Value Objects - правила в самих типах
final readonly class Email
{
    public function __construct(public string $value)
    {
        if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("invalid email: $value");
        }
    }
}

enum Currency: string { case USD = "USD"; case EUR = "EUR"; case RUB = "RUB"; }

final readonly class Money
{
    public function __construct(public int $cents, public Currency $currency)
    {
        if ($cents < 0) throw new InvalidArgumentException("negative");
    }

    public function add(self $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new DomainException("different currencies");
        }
        return new self($this->cents + $other->cents, $this->currency);
    }
}

final readonly class UserId
{
    public function __construct(public int $value)
    {
        if ($value <= 0) throw new InvalidArgumentException;
    }
}

class OrderService
{
    public function place(UserId $user, Email $email, Money $amount): void
    {
        // никаких проверок - типы это уже гарантируют
    }
}',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.misc',
                'difficulty' => 3,
                'question' => 'Чем паттерн Registry отличается от Service Locator?',
                'answer' => 'Оба — формы глобального доступа к объектам и считаются анти-паттернами из-за скрытых зависимостей. Registry — тупое хранилище: положили объект под ключом, забрали по ключу. Сам ничего не создаёт. Service Locator — умное хранилище: знает фабрики/биндинги, при запросе сам собирает объект и его зависимости. Главная проблема обоих: класс, дёргающий $locator->get(X), прячет зависимости от сигнатуры конструктора. Альтернатива — Dependency Injection: зависимости в параметрах конструктора, никакой «глобальной точки доступа».',
                'code_example' => '<?php
// Registry — просто хранилище
final class Registry {
    private static array $items = [];
    public static function set(string $key, object $v): void { self::$items[$key] = $v; }
    public static function get(string $key): object { return self::$items[$key]; }
}

// Service Locator — умеет создавать
final class ServiceLocator {
    private array $factories = [];
    public function bind(string $name, \Closure $f): void { $this->factories[$name] = $f; }
    public function get(string $name): object { return ($this->factories[$name])($this); }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Что такое GRASP и как он соотносится с SOLID?',
                'answer' => 'GRASP (General Responsibility Assignment Software Patterns) — набор из девяти принципов распределения ответственностей между классами: Information Expert, Creator, Controller, Low Coupling, High Cohesion, Polymorphism, Pure Fabrication, Indirection, Protected Variations. SOLID отвечает на вопрос «какими должны быть классы», а GRASP — «какому классу отдать конкретную обязанность», поэтому они дополняют друг друга на этапе проектирования.',
                'difficulty' => 4,
                'topic' => 'oop.misc',
            ],
            [
                'category' => 'ООП',
                'question' => 'Что такое переопределение метода (method overriding) и чем оно отличается от перегрузки?',
                'answer' => 'Переопределение (override) — дочерний класс задаёт свою реализацию метода родителя, сохраняя совместимую сигнатуру. Вызов через объект потомка всегда идёт в его версию; к родительской реализации обращаются через parent::. Перегрузка (overload) — несколько методов с одним именем и разными параметрами; PHP её НЕ поддерживает (имитируется через __call или статические фабрики). Атрибут #[\\Override] из PHP 8.3 фиксирует намерение и ловит опечатки в имени.',
                'difficulty' => 2,
                'topic' => 'oop.misc',
                'code_example' => '<?php
class Animal
{
    public function speak(): string { return \'звук\'; }
}

class Dog extends Animal
{
    #[\Override] // PHP 8.3 - проверка, что метод реально есть у родителя
    public function speak(): string
    {
        return parent::speak() . \' / гав\';
    }
}

echo (new Dog())->speak(); // звук / гав

// Перегрузки в PHP НЕТ - такое не скомпилируется:
// class X { public function f(int $a) {} public function f(string $a) {} }
// Fatal error: Cannot redeclare X::f()',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Какие типы наследования различают в теории ООП и какие из них поддерживает PHP?',
                'answer' => 'Обычно выделяют четыре формы: одиночное (класс наследует одного родителя), многоуровневое (цепочка A → B → C), иерархическое (несколько потомков у одного родителя) и множественное (один класс — два и более прямых родителя). PHP напрямую поддерживает только одиночное, многоуровневое и иерархическое наследование классов. Множественное наследование классов в PHP запрещено, но эффект «гибридного» получают за счёт реализации нескольких интерфейсов и подмешивания трейтов. Это даёт композицию поведения без diamond-проблемы и неоднозначности диспетчеризации.',
                'difficulty' => 3,
                'topic' => 'oop.misc',
            ],
        ];
    }
}
