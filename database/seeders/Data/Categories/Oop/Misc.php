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
                'answer' => 'Ключевое слово **`final`** запрещает расширение:

- **`final class`** — от него **нельзя наследоваться** (`extends FinalClass` → Fatal error).
- **`final method`** — нельзя **переопределить** (`override`) в потомках.
- **`final const`** (PHP 8.1) — нельзя переопределить константу в наследнике.

**Зачем:**

- **Защита дизайна** — явно сообщаешь: «этот класс не предназначен для расширения».
- **Защита инвариантов** — наследник не сможет сломать критичный метод.
- Помогает обходить проблемы **LSP** и **fragile base class**.

**Рекомендация (Effective Java, Sandi Metz):** делай классы `final` **по умолчанию**, открывай наследование **осознанно**. Часто эквивалент `final` — `private` конструктор + статические фабрики.',
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
                'answer' => '**Магические методы** — специальные методы PHP, **начинающиеся с двух подчёркиваний**, вызываемые **автоматически** в определённых ситуациях.

**Полный набор:**

| Метод | Когда вызывается |
|---|---|
| **`__construct()`** | при `new` |
| **`__destruct()`** | при уничтожении объекта (refcount = 0 / завершение скрипта) |
| **`__get($name)`** | чтение **несуществующего** или недоступного свойства |
| **`__set($name, $value)`** | запись в несуществующее или недоступное свойство |
| **`__isset($name)`** | `isset($obj->prop)` / `empty(...)` для несуществующих |
| **`__unset($name)`** | `unset($obj->prop)` для несуществующих |
| **`__call($name, $args)`** | вызов **несуществующего** метода объекта |
| **`__callStatic($name, $args)`** | вызов несуществующего статического метода |
| **`__toString()`** | приведение объекта к строке (`"$obj"`, `echo`) |
| **`__invoke(...$args)`** | вызов **объекта как функции**: `$obj(...)` |
| **`__clone()`** | при `clone $obj` — для **глубокого** копирования |
| **`__sleep()` / `__wakeup()`** | старая сериализация (до PHP 7.4) |
| **`__serialize()` / `__unserialize()`** | новый формат сериализации (PHP 7.4+) |
| **`__debugInfo()`** | то, что покажет `var_dump()` |
| **`__set_state($props)`** | при `var_export()` |

**Где применяются:**

- **`__get`/`__set`** — Eloquent `Model` для доступа к атрибутам.
- **`__call`** — Eloquent **dynamic finders** / **scopes**, query builder.
- **`__callStatic`** — Laravel **Facades** (`Cache::get()` через `__callStatic`).
- **`__toString`** — Stringable VO (`Money`, `Email`).
- **`__invoke`** — single-action controllers, callables, middleware.

**Подводные камни:**

- **Скрывают поведение** — IDE и статанализаторы не видят, какие свойства/методы есть. PhpStorm частично разбирает через `@property` / `@method`.
- **Стоят дороже** обычного доступа — лишний вызов.
- **Усложняют отладку** — стек-трейс показывает `__call`, а не реальный метод.
- **`__get`/`__set`** легко делают класс **анемичным**, ломают инкапсуляцию.

**Правило:** используй магию, когда нужна **динамика** (DSL, ORM, прокси), а не как «удобство по умолчанию».',
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
                'answer' => '**Late Static Binding (LSB, позднее статическое связывание)** — механизм, позволяющий внутри родительского кода ссылаться на **ФАКТИЧЕСКИЙ класс**, через который сделан вызов.

| | **`self::`** | **`static::`** | **`parent::`** |
|---|---|---|---|
| Указывает на | класс, **ГДЕ написан** код | класс, **ОТ которого вызвали** | родительский класс |
| Связывание | **раннее** (compile-time) | **позднее** (runtime) | compile-time |
| Учитывает наследование | нет | **да** | да (один шаг вверх) |
| Появилось в | всегда | **PHP 5.3** | всегда |

**Зачем нужен LSB:**

- **Фабричные методы** в **базовом классе**, которые должны вернуть **конкретного наследника**.
- **Active Record / Eloquent** — `Model::find($id)` должен вернуть `User`, если вызвали `User::find($id)`.
- **Method chaining** в базовом классе с возвратом наследника.

**Возвращаемый тип `static` (PHP 8.0):**

- Раньше для типизации использовали `self`, но это **возвращало** статический тип — нельзя было типизировать «тот же класс, что и вызывающий».
- `static` как тип возврата фиксирует контракт: **метод возвращает тот класс, через который его позвали**.

**Подводный камень:** `new static()` в **`final`-классе** эквивалентен `new self()`. Но если класс не `final` — кто-то может унаследоваться и получить **неожиданное поведение** в фабриках (например, `Model::create()` создаст экземпляр без нужных полей). Поэтому в **immutable** иерархиях `final readonly class` — хорошая практика.

**LSB не даёт доступ** к `private`-полям наследника из родителя — позднее связывание касается **только разрешения имени класса**, не области видимости.',
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
                'answer' => '**Анонимный класс** — класс **без имени**, объявленный и сразу инстанцированный одним выражением через `new class { ... }`. Появился в PHP 7.0.

**Возможности:**

- Можно `extends` родителя и `implements` интерфейсы: `new class extends Foo implements Bar {}`.
- Можно передать параметры в конструктор: `new class($x, $y) {}`.
- Может иметь свойства, методы, использовать трейты.

**Где удобно:**

- **Тесты** — быстрый mock интерфейса без отдельного класса.
- **Inline Strategy** — одноразовая реализация на месте.
- **Callback с состоянием** — где замыкания не хватает.

**Не злоупотреблять:** если используется в нескольких местах — пора заводить нормальный именованный класс.',
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
                'answer' => 'Оба — отношения **«часть-целое» (has-a)**, разница в **силе связи и управлении жизненным циклом**.

| | **Композиция** (сильная) | **Агрегация** (слабая) |
|---|---|---|
| Аналогия | дом и комнаты | университет и студенты |
| Часть существует без целого? | **нет** — удалили целое, удалились части | **да** — закрыли универ, студенты остались |
| Жизненный цикл | управляет **целое** | управляет **сама часть** |
| Кто создаёт часть | **внутри** целого (`new`) | передаётся **снаружи** (DI) |
| UML | **закрашенный** ромб | **пустой** ромб |
| Эксклюзивность | часть **принадлежит** одному целому | часть может **разделяться** между целыми |

**В коде:**

- **Композиция** — `new` внутри конструктора: `$this->engine = new Engine()`. Engine **не существует** без Car.
- **Агрегация** — зависимость **инжектится**: `__construct(Driver $driver)`. Driver был до Vehicle и переживёт его.

**Когда что выбирать:**

| Сценарий | Решение |
|---|---|
| Часть бессмысленна без целого (адресная строка у заказа) | **композиция** |
| Часть существует независимо и переиспользуется (`User`, `Logger`) | **агрегация** |
| Нужна **тестируемость** — подмена зависимости в тестах | **агрегация (DI)** |
| Нужна **инкапсуляция жизненного цикла** | **композиция** |

**Связь с DI:** агрегация = DI. Композиция = `new` внутри (что в большинстве случаев **антипаттерн** для зависимостей, но **корректно** для **внутренних деталей реализации**, которые не нужно мокать — `new \\DateTimeImmutable()`, `new \\ArrayObject()`).

**Подводный камень:** граница между ними **размыта** в чистом PHP — GC всё равно соберёт объекты по refcount-у. Композиция/агрегация — про **дизайн** и **намерение**, а не про физическое управление памятью.',
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
                'answer' => '**Immutable (неизменяемый) объект** — объект, состояние которого **нельзя изменить после создания**. Любые операции, которые «меняют» его, на самом деле **возвращают новый объект**.

**Плюсы:**

- **Безопасность в многопоточных средах** (Octane, Swoole, RoadRunner) — нельзя случайно мутировать общий объект.
- **Можно использовать как ключ кеша** — hash не изменится между записью и чтением.
- **Проще отлаживать** — значение не меняется в процессе работы метода.
- **Исключает баги случайного изменения** — `$user->setName(\'A\')` где-то глубоко в стеке не сломает остальной код.
- **Гарантия инвариантов в конструкторе** — проверка один раз при создании.
- **Equals по значению** — два `Money(100, USD)` равны, потому что внутреннее состояние идентично.

**Как делать в PHP:**

| Подход | Версия | Особенность |
|---|---|---|
| **`final` + `private` поля + getter** | всегда | многословно |
| **`final readonly class`** | **PHP 8.2+** | весь класс immutable одной строкой |
| **`public readonly`** свойства | **PHP 8.1+** | каждое поле помечается отдельно |
| **Clone + новый объект** в «модифицирующих» методах (`with*`) | всегда | паттерн PSR-7 |

**Подводные камни:**

- **`readonly` на коллекциях** — поле нельзя переприсвоить, но **содержимое массива/объекта** внутри **можно мутировать** (`$obj->items[] = ...`). Для глубокой неизменяемости нужны **immutable-коллекции** или возврат копии.
- **Клонирование** делает **поверхностную** копию — вложенные объекты остаются общими. Реализуй `__clone()` для **глубокого** копирования.
- **Стоимость** — много мелких объектов вместо мутации одного.

**Классические примеры:** `Value Objects` (`Money`, `Email`, `Address`), **`DateTimeImmutable`** (в отличие от мутирующего `DateTime`), **PSR-7 HTTP Messages** (`withHeader()` возвращает новый Request).',
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
                'answer' => '**DTO (Data Transfer Object, объект передачи данных)** — простой объект для **переноса данных между слоями приложения**.

**Свойства DTO:**

- **Только публичные свойства** (или getter\'ы) — без поведения.
- **Без бизнес-логики** — он не знает, что с данными делать.
- **Часто immutable** — `final readonly class`.
- **Сериализуемый** — JSON/XML/array для передачи по сети.
- **Без зависимостей** от инфраструктуры (БД, HTTP).

**Где используется:**

- **API-вход/выход** — `CreateUserRequest`, `UserResponse`.
- **Контроллер → Сервис** — передача данных формы.
- **Между микросервисами** — payload events / commands.
- **Очереди задач** — параметры job.
- **Конфигурация** — типизированная замена массивов конфига.

**DTO vs Value Object vs Entity:**

| | **DTO** | **Value Object** | **Entity** |
|---|---|---|---|
| Назначение | **передача данных** | **значение домена** | **сущность** с identity |
| Поведение | **нет** | **есть** (валидация, операции, equals) | **есть** (бизнес-логика) |
| Identity | нет | по **значению** | по **id** |
| Immutability | обычно immutable | **всегда** immutable | mutable |
| Валидация | базовая (типы) или внешняя | **в конструкторе** | в сеттерах/методах |
| Пример | `CreateUserDto` | `Email`, `Money` | `User`, `Order` |

**Подводные камни:**

- **«Анемичная модель»** — если DTO начинают использовать **вместо** доменных моделей, теряется ООП.
- **Дублирование** — DTO для API + Entity для домена → нужен **mapper** между ними. Это нормально (Anti-Corruption Layer), но требует кода.
- **Соблазн добавить поведение** — методы вроде `isValid()` или `getFullName()` — постепенно DTO превращается в god object. Лучше **держать DTO чистыми**, поведение — в Value Object или Entity.

**В Laravel:**

- `FormRequest::validated()` — возвращает массив, удобно сразу мапить в DTO.
- Пакет `spatie/laravel-data` — DTO с авто-валидацией, кастами, JSON-сериализацией.',
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
                'answer' => '**Терминологическая ловушка PHP** — слово «overloading» означает **два разных** понятия.

| | **Классическая перегрузка** (C++/Java/C#) | **«Overloading» в PHP docs** |
|---|---|---|
| Суть | несколько методов с **одним именем**, разные сигнатуры | **магические методы** перехвата |
| Пример | `f(int)` и `f(string)` | `__get`, `__set`, `__call`, `__callStatic` |
| В PHP | **НЕТ** — fatal error «Cannot redeclare method» | **есть** (но это другое) |

**Если на собесе спросили про «перегрузку» — уточни, какой смысл имеется в виду.**

**Чем имитируют классическую перегрузку в PHP:**

1. **Именованные фабрики (named constructors)** — лучший вариант:

```php
Money::fromCents(100);
Money::fromDollars(1.50);
DateTime::createFromFormat(\'Y-m-d\', \'2026-05-20\');
```

2. **Union types (PHP 8.0)** — один метод принимает разные типы:

```php
public function add(int|string $value): self
```

3. **Variadic + проверка типов** — `func_get_args()` и анализ. **Антипаттерн**: непрозрачно, IDE не подсказывает.

4. **Named arguments (PHP 8.0)** — для опциональных параметров: `createUser(name: \'A\', email: \'b@c\')`.

**Почему PHP пошёл этим путём:**

- PHP — **динамически типизированный**, разрешать имена по сигнатурам типов сложно.
- Магические методы (`__call`) дают **более гибкий** механизм для DSL и ORM.
- Named arguments + union types покрывают **большинство** реальных кейсов.

**Подводный камень:** Reflection-инструменты, IDE и статанализаторы видят **только сигнатуру**, объявленную в коде, — магия `__call` для них прозрачна только через PHPDoc `@method`.',
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
                'answer' => '**Typed properties** (PHP 7.4+) — у свойств класса можно **объявить тип**, PHP **проверяет** его при записи и бросает `TypeError` при несовпадении.

```php
public string $name;
public ?int $age = null;
```

**Эволюция возможностей:**

| Версия | Что добавилось |
|---|---|
| **PHP 7.4** | базовые типы свойств: скаляры, объекты, `array`, `iterable`, `?T`, `self`, `parent` |
| **PHP 8.0** | **union types** (`int\|string`), `mixed` |
| **PHP 8.1** | **`readonly`** свойства, **intersection types** (`Foo&Bar`), `never` (только для возврата) |
| **PHP 8.2** | **DNF-типы** (`(Foo&Bar)\|null`), `readonly class` |
| **PHP 8.3** | typed class constants, `#[\\Override]` |
| **PHP 8.4** | **property hooks** (`get`/`set` без отдельных методов), **asymmetric visibility** (`public private(set)`) |

**Особенности:**

- Типы **`static` и `never`** — **только для возврата метода**, не для свойства.
- **Свойство без дефолта и без инициализации** — чтение бросит **`Error: Typed property must not be accessed before initialization`** (это **не `null`**).
- Тип **`null` сам по себе** запрещён — нужно `?T` или `T|null`.

**Что даёт:**

- **Контракт на уровне свойства** — клиент не подсунет `string` в `int`.
- **Документация в коде** — IDE и статанализаторы видят типы без PHPDoc.
- **Раннее обнаружение багов** — `TypeError` сразу, а не «странное поведение через 100 строк».

**Подводные камни:**

- **Uninitialized vs null** — два разных состояния. Если поле просто не задано, читать его **нельзя**.
- **Свойства-объекты по ссылке** — `readonly` запрещает **переприсвоить**, но не **мутировать** содержимое (если объект не immutable).
- **Property type variance** — переопределить тип поля в наследнике **нельзя** (инвариантность). Если нужно — используют геттер/сеттер.',
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
                'answer' => 'Два **архитектурных паттерна** для работы с БД (из «Patterns of Enterprise Application Architecture» М. Фаулера).

| | **Active Record** | **Data Mapper** |
|---|---|---|
| Кто работает с БД | **сам объект** (`$user->save()`) | **отдельный mapper** (`$em->persist($user)`) |
| Объект знает про БД? | **да** — таблицу, колонки, связи | **нет** — POPO без зависимостей |
| Связь с persistence | **прибит** к ORM | **не зависит** от ORM |
| Простота старта | **проста** для CRUD | **сложнее** — нужны mapper-ы, repositories |
| SRP | **нарушен** (домен + persistence) | **соблюдён** |
| Тестируемость | хуже — `User` нельзя создать без БД | **лучше** — unit-тесты без БД |
| Подходит для | CRUD-приложения, прототипы | **сложная доменная логика, DDD** |
| Примеры | **Eloquent** (Laravel), Yii ActiveRecord, Rails | **Doctrine**, Hibernate, Cycle ORM |

**Active Record — плюсы и минусы:**

| ✅ Плюсы | ❌ Минусы |
|---|---|
| Минимум кода для CRUD | **Смешивает** доменную логику и инфраструктуру |
| Удобно для быстрого прототипа | Базовый класс **тяжёлый** (наследует от `Model`) |
| Магия `__get`/`__set` для атрибутов | Тестировать без БД сложно |
| Все методы в одном месте | Нарушает SRP, DIP |
| | Persistence-поведение **протекает в домен** |

**Data Mapper — плюсы и минусы:**

| ✅ Плюсы | ❌ Минусы |
|---|---|
| **Чистый домен** — `User` не знает про БД | Больше boilerplate (mapper, repository) |
| Легко **переключить** ORM/БД | Сложнее для CRUD-страничек |
| Тесты домена **без БД** | Unit of Work / Identity Map — нетривиально |
| **Подходит для DDD** | Менее «магично» — больше явного кода |

**Когда что выбирать:**

| Сценарий | Решение |
|---|---|
| MVP, прототип, простой CRUD | **Active Record** (Eloquent) |
| Сложная доменная логика, DDD, агрегаты | **Data Mapper** (Doctrine) |
| Команда уже знает Eloquent, но домен растёт | Eloquent + **Repository слой** + **rich domain** в отдельных классах (паттерн) |
| Микросервис с одной задачей | Active Record — быстрее |

**В Laravel** — Eloquent доминирует, но **Doctrine** доступен через сторонние пакеты. Часто на больших проектах поверх Eloquent надстраивают слой **Repository + Domain Models** — гибрид.',
                'code_example' => '<?php
// Active Record (Eloquent) - объект сам знает БД
class User extends \Illuminate\Database\Eloquent\Model
{
    protected $fillable = [\'name\', \'email\'];

    // Бизнес-метод прямо в модели
    public function deactivate(): void
    {
        $this->banned_at = now();
        $this->save();
    }
}

$user = new User();
$user->name = \'Иван\';
$user->email = \'ivan@x.ru\';
$user->save();          // объект сам пишет в БД

$user = User::find(1);  // ищет в БД сам
$user->deactivate();

// Data Mapper (Doctrine) - чистый POPO, без знания о БД
namespace App\Domain;

final class User
{
    public function __construct(
        private string $name,
        private string $email,
        private ?\DateTimeImmutable $bannedAt = null,
    ) {}

    public function deactivate(): void
    {
        $this->bannedAt = new \DateTimeImmutable();
    }

    // ни одного метода save/find/delete - User не знает про БД
}

// Mapper/EntityManager делает работу с persistence
$user = new User(\'Иван\', \'ivan@x.ru\');
$entityManager->persist($user);
$entityManager->flush();        // mapper SQL-запишет

$user = $userRepository->find(1);
$user->deactivate();
$entityManager->flush();        // mapper отследит изменения сам',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.misc',
                'difficulty' => 3,
                'question' => 'Что такое Null Object паттерн?',
                'answer' => '**Null Object** — поведенческий паттерн: **объект, имитирующий поведение «ничего»** вместо `null`.

**Идея:**

- Реализует **тот же интерфейс**, что и реальный объект.
- Методы **ничего не делают** или **возвращают нейтральные значения** (пустую строку, `false`, пустой массив).
- Клиент **не пишет `if ($x === null)`** — вызывает методы как обычно.

**Классические примеры:**

| Null Object | Что делает |
|---|---|
| **`NullLogger`** (PSR-3) | методы `log()`, `info()`, `error()` — **ничего** не пишут |
| **`GuestUser`** | `name() → \'Guest\'`, `can() → false`, `email() → \'\'` |
| **`NullCache`** | `get() → null`, `set()` — no-op |
| **`NullEventDispatcher`** | `dispatch()` — ничего не делает |

**Что даёт:**

- **Линейный код** — без россыпи `if`-ов.
- **Соблюдение интерфейса** — клиент не различает Null и Real.
- **LSP сохранён** — Null корректно подменяет реальный объект.
- **Безопасный default** — если зависимость не передана, используется Null Object вместо null-pointer.

**Null Object vs `null`:**

| | `null` | **Null Object** |
|---|---|---|
| Проверки | `if ($x !== null)` | **не нужны** |
| Тип возврата | `?Type` | `Type` |
| Использование | прямой доступ к полям | вызов методов как обычно |
| Контракт | «может быть нет» | «всегда есть, поведение пустое» |

**Подводные камни:**

- **Сложно реализовать** Null Object для интерфейсов, которые возвращают **не-нейтральные** значения (что вернёт `NullDatabase::query()`? Пустой результат сет может быть **семантически некорректным**).
- **Маскировка ошибок** — если Null Object используется по ошибке (забыли подменить), баг найти **сложнее**, чем падение на `null`.
- **Не подходит**, если поведение «отсутствия» **отличается** от поведения «реального объекта с пустым состоянием». Тогда лучше явный `null` или `Maybe<T>`.

**Когда брать:** есть **легитимный** сценарий «объекта нет, но код должен работать как раньше». Не брать ради того, чтобы спрятать `null` и сделать вид, что баг не баг.',
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
                'answer' => 'Три базовых принципа хорошего кода — дополнение к SOLID.

- **DRY (Don\'t Repeat Yourself)** — не повторяйся: одно знание живёт в одном месте. Дублирование логики — источник багов при правках. Важная оговорка: DRY про **знание**, а не про похожий код. Два случайно похожих куска лучше **не** объединять (rule of three).
- **KISS (Keep It Simple, Stupid)** — делай проще, не усложняй, пока не нужно. Простой код легче читать и поддерживать.
- **YAGNI (You Aren\'t Gonna Need It)** — не пиши код «на будущее» в надежде, что пригодится. Чаще всего не пригодится, а лишний код придётся поддерживать.',
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
                'answer' => '**Specification (спецификация)** — паттерн из **DDD**: бизнес-правило отбора или проверки оформлено как объект с методом **`isSatisfiedBy($entity): bool`**.

**Идея:**

- Каждое правило — **отдельный класс** (`IsActive`, `OlderThan(18)`, `HasSubscription`).
- Спецификации **комбинируются** через **`AndSpec`**, **`OrSpec`**, **`NotSpec`** — получается **мини-DSL** для условий.
- Клиент **собирает условие** из кирпичиков и передаёт его в репозиторий/фильтр.

**Зачем нужен:**

- Избавиться от взрыва методов в репозитории — `findActiveUsersOlderThan18WithSubscription()`, `findActiveAdminsInRegion()`, и так до сотен.
- **Reuse** бизнес-правил между местами — то же правило для **отбора в БД** и **проверки одного объекта** в памяти.
- **Тестируемость** — каждое правило тестируется в **изоляции**.
- **Композиция > наследование** — комбинируем правила в рантайме.

**Три применения Specification:**

| Применение | Метод | Где |
|---|---|---|
| **Валидация** одного объекта | `isSatisfiedBy($entity)` | проверка инварианта, право на действие |
| **In-memory фильтр** коллекции | `array_filter(..., fn($e) => $spec->isSatisfiedBy($e))` | фильтрация после загрузки |
| **Перевод в SQL** | `toQuery(Builder $qb)` | продвинутые версии — генерация where-условий |

**Specification vs Eloquent Query Scopes:**

| | **Specification** | **Query Scope** |
|---|---|---|
| Где живёт | отдельный класс домена | метод Eloquent-модели |
| Переносится между in-memory и DB | **да** (`isSatisfiedBy` + `toQuery`) | только DB |
| Зависимости | нет | от модели и query builder |
| Композиция | Composite (`AndSpec`, `OrSpec`) | цепочка методов |

**Когда брать:**

- Сложные бизнес-правила, **меняющиеся комбинации** условий.
- Логика, которую нужно проверять **в нескольких местах** (UI + API + домен).
- DDD-проект с явным **bounded context**.

**Когда не брать:**

- Один-два простых условия — **проще метод репо** или scope.
- CRUD-приложение без сложной доменной логики — over-engineering.
- Условия используются только в **одном месте** — YAGNI.',
                'code_example' => '<?php
interface Spec
{
    public function isSatisfiedBy(User $u): bool;
}

final class IsActive implements Spec
{
    public function isSatisfiedBy(User $u): bool
    {
        return $u->bannedAt === null;
    }
}

final class OlderThan implements Spec
{
    public function __construct(private int $age) {}
    public function isSatisfiedBy(User $u): bool
    {
        return $u->age >= $this->age;
    }
}

final class HasSubscription implements Spec
{
    public function isSatisfiedBy(User $u): bool
    {
        return $u->subscription !== null;
    }
}

// Комбинаторы
final class AndSpec implements Spec
{
    /** @param Spec[] $specs */
    public function __construct(private array $specs) {}
    public function isSatisfiedBy(User $u): bool
    {
        foreach ($this->specs as $s) {
            if (! $s->isSatisfiedBy($u)) return false;
        }
        return true;
    }
}

final class NotSpec implements Spec
{
    public function __construct(private Spec $inner) {}
    public function isSatisfiedBy(User $u): bool
    {
        return ! $this->inner->isSatisfiedBy($u);
    }
}

// Клиент сам собирает условие из кирпичиков
$canSeeDiscount = new AndSpec([
    new IsActive(),
    new OlderThan(18),
    new HasSubscription(),
]);

$eligible = array_filter($users, fn(User $u) => $canSeeDiscount->isSatisfiedBy($u));',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.misc',
                'difficulty' => 3,
                'question' => 'Что такое анти-паттерн Primitive Obsession и как с ним бороться?',
                'answer' => '**Primitive Obsession** — использование **базовых типов** (`string`, `int`, `array`) там, где у домена есть **собственные правила**: `string $email`, `int $userId`, `int $cents`, `string $currency`.

**Признаки проблемы:**
- **Валидация размазана** — `filter_var($email, FILTER_VALIDATE_EMAIL)` дублируется в десяти местах.
- **Лёгко перепутать порядок аргументов**: `charge(int $amount, int $userId)` vs `charge(int $userId, int $amount)` — IDE не подскажет.
- **Нельзя различить** разнотипные значения «по виду»: рубли и доллары — обе `int`; user_id и order_id — обе `int`.
- **Магические строки** для статусов / валют разбросаны по коду.

**Решение — Value Objects:**
- **`Email`** — конструктор валидирует, объект **самосогласован**.
- **`Money(cents, currency)`** — арифметика, гарантия одной валюты при сложении.
- **`UserId(int)`** — обёртка над int, исключает путаницу с `OrderId`.
- **`enum Currency: string`** — закрытый список валидных значений (PHP **8.1+**).

**Что это даёт:**
- **Контракт в типе**: `place(UserId $u, Email $e, Money $a)` — порядок и смысл аргументов **не перепутаешь**.
- **Валидация в одном месте** — в конструкторе VO.
- **Безопасные операции** — `Money::add()` сама проверяет валюту.
- **PHPStan / IDE** видят полные сигнатуры и подсвечивают ошибки.

**Подвох:** не превращай **каждый** `int` в Value Object — это перебор. Применяй там, где значение имеет **домен-специфичные правила** или **смысл** (а не просто число строк в кэше).',
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
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.misc',
                'difficulty' => 3,
                'question' => 'Чем паттерн Registry отличается от Service Locator?',
                'answer' => 'Оба — формы **глобального доступа** к объектам и **оба считаются анти-паттернами** из-за **скрытых зависимостей**.

**Сравнение:**

| | **Registry** | **Service Locator** |
|---|---|---|
| Что делает | **хранилище** «ключ → объект» | хранилище **фабрик** «ключ → как собрать» |
| Создаёт объекты сам | **нет**, кладёт готовые | **да**, по фабрике / биндингу |
| Знает про зависимости | нет | **да**, может рекурсивно собирать |
| Пример из жизни | глобальный `Config::set("db", $db)` | `$container->make(UserService::class)` |

**Общая проблема (почему анти-паттерны):**
- Класс, дёргающий **`$locator->get(X)`**, **прячет** свои зависимости от сигнатуры конструктора.
- Невозможно понять, что нужно классу, **не прочитав весь код**.
- Тесты вынуждены **настраивать локатор** перед каждым тестом — больше boilerplate.
- Подмену реализации не выразить в типе — нет статической проверки.

**Альтернатива — Dependency Injection:**
- зависимости **явно** в параметрах конструктора → видно сразу.
- IDE / PHPStan показывают полный «список покупок» класса.
- в тестах передаёшь моки **напрямую**, без глобального стейта.
- DI-контейнер (Laravel, Symfony) **сам** собирает граф зависимостей — но в коде ты всё равно пишешь `__construct(Dep $d)`, а не `$container->get(Dep::class)`.

**Когда Service Locator всё-таки терпим:**
- На **самом краю** приложения (фронт-контроллер, фабрика action-классов) — один вызов, дальше DI.
- В **legacy-коде** как мостик при поэтапном внедрении DI.',
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
                'answer' => '**Переопределение (override)** — наследник задаёт **свою** реализацию метода родителя, сохраняя совместимую сигнатуру.

- Вызов через объект потомка всегда идёт в его версию — это полиморфизм.
- К родительской реализации обращаются через `parent::method()`.
- С PHP 8.3 атрибут `#[\\Override]` фиксирует намерение и ловит опечатки.

**Перегрузка (overload)** — несколько методов с **одним именем** и разными параметрами.

- В PHP **классической перегрузки НЕТ**: два метода с одним именем → fatal error `Cannot redeclare`.
- В документации PHP «overloading» означает **магические методы** (`__get`, `__set`, `__call`, `__callStatic`) — это другое.
- Имитируют через **именованные фабрики**: `Money::fromCents()`, `Money::fromDollars()`.

**Запомни разницу:** override — **по вертикали** (родитель → потомок), overload — **по горизонтали** (несколько методов в одном классе).',
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
                'answer' => '**Четыре основные формы** наследования:

| Тип | Описание | Поддержка в PHP |
|---|---|---|
| **Одиночное** | один родитель: `B extends A` | **да** |
| **Многоуровневое** | цепочка: `A → B → C` | **да** |
| **Иерархическое** | один родитель — много потомков (`Admin extends User`, `Guest extends User`) | **да** |
| **Множественное** | один класс наследует **двух и более** родителей напрямую | **нет** для классов |

**Почему PHP запретил множественное наследование классов:**
- **Diamond problem** — общий предок «по двум веткам» создаёт **неоднозначность**: какое поле / какой метод победит?
- Уменьшает сложность языка и проектирования.

**Чем компенсируется:**
1. **Несколько интерфейсов** — множественное наследование **контрактов** разрешено: `class X implements A, B, C {}`. Конфликтов нет — интерфейсы не содержат реализации (default-методов как в Java у PHP нет).
2. **Трейты (PHP 5.4+)** — **горизонтальная композиция** кода, «миксины» с явным разрешением конфликтов:
   - **`insteadof`** — какой trait выигрывает.
   - **`as`** — переименовать метод / изменить видимость.

**Композиция vs Наследование:**
- Современное проектирование предпочитает **композицию** (`final class X { private Y $y; }`) над глубокими иерархиями.
- Наследование оправдано там, где есть **реальная LSP-совместимость** и **общее поведение**, а не «удобно переиспользовать код».',
                'difficulty' => 3,
                'topic' => 'oop.misc',
                'code_example' => '<?php
// 1) Одиночное
class Animal {}
class Dog extends Animal {}

// 2) Многоуровневое - цепочка
class A {}
class B extends A {}
class C extends B {} // C получает всё от A и B

// 3) Иерархическое - один родитель, много потомков
class User {}
class Admin extends User {}
class Guest extends User {}
class Moderator extends User {}

// 4) Множественное наследование классов - ЗАПРЕЩЕНО
// class Child extends A, B {} // Parse error в PHP

// ✅ Множественное наследование ИНТЕРФЕЙСОВ - разрешено
interface Loggable { public function log(string $m): void; }
interface Cacheable { public function cacheKey(): string; }
interface Serializable { public function serialize(): string; }

class Report implements Loggable, Cacheable, Serializable
{
    public function log(string $m): void {}
    public function cacheKey(): string { return \'r\'; }
    public function serialize(): string { return \'\'; }
}

// ✅ Трейты - горизонтальная композиция реализации
trait HasTimestamps { public ?\DateTimeImmutable $createdAt = null; }
trait Searchable    { public function search(string $q): array { return []; } }

class Article
{
    use HasTimestamps, Searchable; // подмешали два «миксина»
}',
                'code_language' => 'php',
            ],
        ];
    }
}
