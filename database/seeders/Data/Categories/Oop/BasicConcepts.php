<?php

namespace Database\Seeders\Data\Categories\Oop;

class BasicConcepts
{
    public static function all(): array
    {
        return [
            [
                'category' => 'ООП',
                'topic' => 'oop.basic_concepts',
                'difficulty' => 1,
                'question' => 'Что такое ООП?',
                'answer' => '**ООП (объектно-ориентированное программирование)** — парадигма, в которой программа моделируется как набор взаимодействующих **объектов**. У каждого объекта есть:

- **состояние** — свойства (данные)
- **поведение** — методы (действия)

**4 базовых принципа:**

1. **Инкапсуляция** — прячем детали реализации за публичным API.
2. **Наследование** — один класс расширяет другой (`extends`).
3. **Полиморфизм** — один интерфейс, разные реализации.
4. **Абстракция** — выделяем главное, опускаем мелочи.

Помогает структурировать большие программы, переиспользовать код и моделировать предметную область.',
                'code_example' => '<?php
// Класс - описание (шаблон)
class User
{
    // свойство (состояние)
    public string $name;

    // метод (поведение)
    public function greet(): string
    {
        return \'Привет, я \' . $this->name;
    }
}

// Объект - конкретный экземпляр
$user = new User();
$user->name = \'Иван\';
echo $user->greet(); // Привет, я Иван',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.basic_concepts',
                'difficulty' => 2,
                'question' => 'Какие основные парадигмы программирования существуют?',
                'answer' => 'Основные парадигмы: 1) Императивная (пошаговое описание команд) - например, процедурное программирование. 2) Декларативная (SQL, логическая) - описание "что нужно", а не "как". 3) ООП (объектно-ориентированная) - программа как набор объектов. 4) Функциональная - частный случай декларативной: программа как композиция чистых функций. 5) Логическая - на основе логических утверждений (Prolog). PHP поддерживает несколько парадигм одновременно.',
                'code_example' => null,
                'code_language' => null,
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.basic_concepts',
                'difficulty' => 1,
                'question' => 'Зачем нужно ООП?',
                'answer' => 'ООП решает проблемы сложности больших программ:

1. **Структура** — код разбит на логические блоки (классы).
2. **Переиспользование** — наследование и композиция вместо копипаста.
3. **Инкапсуляция** — детали реализации скрыты, наружу торчит только нужный API.
4. **Полиморфизм** — поведение меняется добавлением класса, а не переписыванием старого.
5. **Близость к реальному миру** — проще моделировать домен (`User`, `Order`, `Product`).
6. **Тестируемость** — модуль изолируется, зависимости подменяются мок-ами.
7. **Командная разработка** — чёткое разделение ответственностей между классами.',
                'code_example' => '<?php
// ❌ Процедурно: данные и функции отдельно, легко перепутать
$userName = \'Иван\';
$userBalance = 100;

function deposit(&$balance, int $amount): void
{
    $balance += $amount;
}

deposit($userBalance, 50);

// ✅ ООП: данные и поведение склеены в объект
class Account
{
    private int $balance = 0;

    public function deposit(int $amount): void
    {
        $this->balance += $amount;
    }

    public function balance(): int
    {
        return $this->balance;
    }
}

$account = new Account();
$account->deposit(50);
echo $account->balance(); // 50',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.basic_concepts',
                'difficulty' => 1,
                'question' => 'Что такое класс?',
                'answer' => '**Класс** — это шаблон (чертёж), описывающий структуру и поведение будущих объектов. В классе объявляют:

- **свойства** — данные объекта
- **методы** — действия, которые объект умеет делать

Сам по себе класс — это не объект, а описание того, какими будут его экземпляры. Аналогия: класс — это чертёж дома, а объект — конкретный построенный дом.',
                'code_example' => '<?php
class User
{
    public string $name;
    public int $age;

    public function greet(): string
    {
        return \'Привет, я \' . $this->name;
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.basic_concepts',
                'difficulty' => 1,
                'question' => 'Что такое объект?',
                'answer' => '**Объект** — конкретный экземпляр класса, существующий в памяти. Ключевое:

- у каждого объекта **своё** состояние (значения свойств)
- поведение (методы) **общее**, описано в классе
- создаётся оператором `new`
- у одного класса может быть **много** объектов, каждый со своими данными',
                'code_example' => '<?php
$user1 = new User();
$user1->name = \'Иван\';

$user2 = new User();
$user2->name = \'Мария\';

// Это два разных объекта одного класса
echo $user1->greet(); // Привет, я Иван
echo $user2->greet(); // Привет, я Мария',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.basic_concepts',
                'difficulty' => 1,
                'question' => 'Чем класс отличается от объекта?',
                'answer' => '**Класс** — описание (тип, шаблон). **Объект** — конкретный экземпляр класса в памяти.

- Класс существует на этапе определения, объект — во время выполнения.
- Класс **один**, объектов может быть **много**.
- Аналогия: класс `Cat` — это понятие «кошка вообще», а объект — конкретная Мурка с её цветом, возрастом и именем.',
                'code_example' => '<?php
// Класс - описание
class Cat
{
    public string $name;
}

// Объекты - конкретные экземпляры
$murka = new Cat();
$murka->name = \'Мурка\';

$barsik = new Cat();
$barsik->name = \'Барсик\';',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.basic_concepts',
                'difficulty' => 1,
                'question' => 'Что такое метод?',
                'answer' => '**Метод** — функция, объявленная внутри класса. Описывает поведение объектов и обычно работает с их данными (свойствами).

- Обычный метод вызывают через объект: `$obj->method()`.
- Статический — через имя класса: `Class::method()`.
- Внутри обычного метода доступен `$this` (текущий объект), в статическом — нет.',
                'code_example' => '<?php
class Calculator
{
    public function add(int $a, int $b): int
    {
        return $a + $b;
    }
}

$calc = new Calculator();
echo $calc->add(2, 3); // 5',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.basic_concepts',
                'difficulty' => 1,
                'question' => 'Что такое свойство?',
                'answer' => '**Свойство** (property, поле) — переменная, объявленная внутри класса. Хранит данные конкретного объекта: у каждого объекта свой набор значений, но имена и типы свойств описаны в классе.

- Можно указать **тип**: `public string $name`.
- Можно задать **значение по умолчанию**: `public int $count = 0`.
- С PHP 8.1 можно пометить `readonly` — запись только из конструктора.
- Доступ снаружи через стрелку: `$user->name`.',
                'code_example' => '<?php
class Product
{
    public string $name;
    public float $price;
    public int $quantity = 0; // значение по умолчанию
}

$p = new Product();
$p->name = \'Книга\';
$p->price = 599.99;',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Чем свойство отличается от метода?',
                'answer' => 'Грубо: **свойство ХРАНИТ, метод ДЕЛАЕТ**.

- **Свойство** — переменная объекта, хранит данные (состояние): `public string $name`.
- **Метод** — функция объекта, описывает действие (поведение): `public function greet() {...}`.

Обращение разное:

- к свойству — без скобок: `$obj->name`
- к методу — со скобками: `$obj->greet()`',
                'difficulty' => 1,
                'topic' => 'oop.basic_concepts',
                'code_example' => '<?php
class User
{
    public string $name = \'\';            // свойство (данные)

    public function greet(): string       // метод (действие)
    {
        return \'Hi, \' . $this->name;
    }
}

$u = new User();
$u->name = \'Иван\';     // обращение к свойству
echo $u->greet();      // вызов метода',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Что такое $this в методе?',
                'answer' => '`$this` — ссылка на **текущий объект**, у которого вызвали метод.

- `$this->property` — свойство ЭТОГО экземпляра.
- `$this->method()` — вызов другого метода ЭТОГО экземпляра.
- Доступен **только внутри нестатических** методов: в `static`-методах `$this` не существует.',
                'difficulty' => 1,
                'topic' => 'oop.basic_concepts',
                'code_example' => '<?php
class User
{
    public string $name = \'\';

    public function rename(string $newName): void
    {
        $this->name = $newName;       // свойство этого объекта
    }

    public function greet(): string
    {
        return \'Hi, \' . $this->name; // вызвалось у конкретного $u
    }
}

$u = new User();
$u->rename(\'Иван\');
echo $u->greet(); // Hi, Иван',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Что такое self и в чём разница с $this?',
                'answer' => 'self ссылается на текущий КЛАСС (а не на объект). Используется для доступа к статическим свойствам/методам и константам: self::COUNT, self::$instances. $this — на конкретный объект. Внутри обычного метода работают оба, в статическом методе $this недоступен.',
                'difficulty' => 2,
                'topic' => 'oop.basic_concepts',
                'code_example' => '<?php
class Counter
{
    public const STEP = 1;
    public static int $count = 0;

    public int $local = 0;

    public function bump(): void
    {
        $this->local += self::STEP;   // $this - объект, self - класс
        self::$count += self::STEP;   // статическое свойство класса
    }

    public static function reset(): void
    {
        self::$count = 0; // здесь $this недоступен
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Можно ли создать экземпляр абстрактного класса?',
                'answer' => 'Нет. new AbstractClass() даст Error: «Cannot instantiate abstract class». Абстрактный класс — только для наследования. Чтобы получить объект — наследуй конкретным классом и реализуй абстрактные методы, потом new ConcreteChild().',
                'difficulty' => 2,
                'topic' => 'oop.basic_concepts',
                'code_example' => '<?php
abstract class Shape
{
    abstract public function area(): float;
}

// ❌ new Shape(); // Error: Cannot instantiate abstract class Shape

class Circle extends Shape
{
    public function __construct(private float $r) {}
    public function area(): float { return M_PI * $this->r ** 2; }
}

$c = new Circle(2.0); // OK - конкретный наследник
echo $c->area();',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Как работает синтаксис extends простыми словами?',
                'answer' => '`class Admin extends User` — `Admin` наследует **все** `public` и `protected` свойства и методы `User`. Потомок может:

- добавить **новые** свойства/методы
- **переопределить** (override) существующие
- использовать родительские **как есть**

**PHP разрешает наследовать ТОЛЬКО ОДИН класс.** Для нескольких контрактов — `implements` у интерфейсов, для подмешивания поведения — `use` трейта.',
                'difficulty' => 1,
                'topic' => 'oop.basic_concepts',
                'code_example' => '<?php
class User
{
    public string $name = \'\';
    public function greet(): string { return \'Hi, \' . $this->name; }
}

class Admin extends User
{
    public function ban(string $reason): void { /* ... */ }
}

$a = new Admin();
$a->name = \'Root\';
echo $a->greet(); // унаследовано
$a->ban(\'spam\'); // своё',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Что такое parent:: и зачем оно нужно?',
                'answer' => '`parent::` — обращение к методу или константе **родителя** из наследника. Два типичных кейса:

1. В конструкторе потомка вызвать `parent::__construct(...)`, чтобы инициализировать родительские свойства.
2. При **override** — позвать родительскую реализацию и добавить своё поведение: `parent::save(); $this->log();`',
                'difficulty' => 1,
                'topic' => 'oop.basic_concepts',
                'code_example' => '<?php
class Repository
{
    public function save(): void { /* запись в БД */ }
}

class LoggingRepository extends Repository
{
    public function save(): void
    {
        parent::save();              // сначала родительская логика
        error_log(\'saved at \' . time()); // потом своё
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Что такое константа класса (const)?',
                'answer' => '**Константа класса** — неизменяемое значение, привязанное к классу.

- Объявляется через `const`, перед именем нет `$` — это не переменная.
- Доступ через `::`: `Status::ACTIVE`.
- По соглашению имена пишут `UPPER_CASE`.
- Применяется для перечислений статусов, кодов ошибок, версий API.
- С PHP 8.1 можно пометить `final` — потомок не сможет переопределить.
- С PHP 8.3 константе можно указать тип.

Константы можно объявлять и внутри интерфейсов.',
                'difficulty' => 1,
                'topic' => 'oop.basic_concepts',
                'code_example' => '<?php
class Status
{
    const ACTIVE = 1;
    const BANNED = 2;
    const VERSION = \'v1\';
}

echo Status::ACTIVE;  // 1
echo Status::VERSION; // v1

// Доступ изнутри класса - через self::
class Order
{
    const MAX_ITEMS = 100;

    public function check(int $count): bool
    {
        return $count <= self::MAX_ITEMS;
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Что такое implements в PHP простыми словами?',
                'answer' => 'Ключевое слово, обозначающее реализацию интерфейса. class Email implements Sendable — класс Email обязан содержать все методы интерфейса Sendable. Можно реализовывать несколько: class Foo implements Sendable, Cacheable, Loggable. Любой класс, реализующий интерфейс, можно type-hint через имя интерфейса: function send(Sendable $s).',
                'difficulty' => 2,
                'topic' => 'oop.basic_concepts',
                'code_example' => '<?php
interface Sendable  { public function send(): void; }
interface Loggable  { public function log(): string; }

// Один класс - несколько контрактов
class Email implements Sendable, Loggable
{
    public function __construct(private string $to) {}
    public function send(): void { /* SMTP */ }
    public function log(): string { return "email to $this->to"; }
}

// Type-hint по интерфейсу - принимает любую реализацию
function deliver(Sendable $s): void
{
    $s->send();
}

deliver(new Email(\'user@example.com\'));',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.basic_concepts',
                'difficulty' => 1,
                'question' => 'Что такое геттер и сеттер?',
                'answer' => '- **Геттер** (getter) — метод, возвращающий значение свойства: `getName(): string`.
- **Сеттер** (setter) — метод, меняющий значение свойства: `setName(string $name): void`.

**Зачем нужны:** свойство объявляют `private`, а доступ дают через методы — так можно **валидировать** вход, **логировать**, **скрыть** детали хранения.

По соглашению имена начинаются с `get`/`set`, но в PHP это обычные методы — никакой магии.',
                'code_example' => '<?php
class User
{
    private string $name = \'\';

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        if ($name === \'\') {
            throw new \InvalidArgumentException(\'Имя не может быть пустым\');
        }
        $this->name = $name;
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.basic_concepts',
                'difficulty' => 1,
                'question' => 'Что такое наследование простыми словами?',
                'answer' => '**Наследование** — когда один класс (потомок) получает все `public` и `protected` свойства и методы другого класса (родителя). Объявляется через `extends`: `class Admin extends User`.

Потомок может:

1. добавить **свои** методы
2. **переопределить** родительские (override)
3. использовать родительские **как есть**

**Важно:** PHP разрешает наследовать только **один** класс. Для нескольких контрактов — `implements` (интерфейсы), для шаринга методов — `use` (трейты).

Применяется, когда между классами есть отношение **«является»** (is-a): `Admin` — это `User`.',
                'code_example' => '<?php
class User
{
    public function greet(): string { return \'Привет\'; }
}

class Admin extends User
{
    // 1) Свой метод
    public function ban(): void { /* ... */ }

    // 2) Переопределение родительского
    public function greet(): string
    {
        return \'Привет, я админ\';
    }
}

$a = new Admin();
echo $a->greet(); // Привет, я админ (свой override)
$a->ban();        // свой метод',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.basic_concepts',
                'difficulty' => 2,
                'question' => 'Что такое переопределение метода (override)?',
                'answer' => 'Когда наследник заменяет реализацию метода родителя своей, сохраняя совместимую сигнатуру. Вызов через объект-наследник всегда идёт в его версию. К родительской реализации можно обратиться через parent::method(). С PHP 8.3 есть атрибут #[\\Override] — он не обязателен, но IDE и компилятор подскажут, если в имени опечатка и метода у родителя на самом деле нет.',
                'code_example' => '<?php
class Animal
{
    public function speak(): string { return \'звук\'; }
}

class Dog extends Animal
{
    #[\\Override]
    public function speak(): string { return \'гав\'; }
}

echo (new Dog())->speak(); // гав',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.basic_concepts',
                'difficulty' => 1,
                'question' => 'Зачем нужен интерфейс простыми словами?',
                'answer' => '**Интерфейс** — список методов без реализации. Класс, который его `implements`, обязан реализовать **все** эти методы.

Это **контракт**: тот, кто получает `Logger $log`, знает, что у объекта есть метод `log()` — независимо от того, `FileLogger` это или `NullLogger`.

**Зачем нужен:**

1. **Подменяемость** реализаций (DI, тесты с моками).
2. **Полиморфизм** — один вызов, разное поведение.
3. Разные иерархии могут реализовать один интерфейс.',
                'code_example' => '<?php
interface Logger
{
    public function log(string $message): void;
}

class FileLogger implements Logger
{
    public function log(string $message): void { /* в файл */ }
}

class NullLogger implements Logger
{
    public function log(string $message): void { /* ничего */ }
}

function run(Logger $log): void { $log->log(\'work\'); }',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.basic_concepts',
                'difficulty' => 1,
                'question' => 'Что такое typehint (объявление типа) простыми словами?',
                'answer' => '**Typehint** — указание ожидаемого типа аргумента, возвращаемого значения или свойства: `function save(User $user): void`. PHP при вызове проверит, что передан именно `User` (или его наследник/реализация интерфейса) — иначе бросит `TypeError`.

**Зачем:**

- делает контракты **явными**
- помогает IDE и статическому анализу
- ловит ошибки **до запуска**

**Поддерживаемые формы:**

- скаляры: `int`, `string`, `float`, `bool`
- `array`
- объекты по имени класса или интерфейса
- nullable: `?Type` (или `Type|null`)
- union: `int|string`
- `self`, `static`, `void`, `never`',
                'code_example' => '<?php
// Скалярные типы для параметров и возврата
function greet(string $name): string
{
    return \'Привет, \' . $name;
}

greet(\'Иван\'); // OK
// greet(123);  // TypeError: argument must be of type string

// Type-hint объекта: примет User или его наследника
class User { public string $name = \'\'; }

function save(User $user): void
{
    /* ... */
}

// Nullable: разрешает либо string, либо null
function findName(?string $query): ?User
{
    return null;
}',
                'code_language' => 'php',
            ],
        ];
    }
}
