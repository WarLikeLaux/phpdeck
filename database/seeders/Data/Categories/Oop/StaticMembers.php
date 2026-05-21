<?php

namespace Database\Seeders\Data\Categories\Oop;

class StaticMembers
{
    public static function all(): array
    {
        return [
            [
                'category' => 'ООП',
                'topic' => 'oop.static_members',
                'difficulty' => 2,
                'question' => 'Что такое статические методы и свойства?',
                'answer' => '**Статические** методы и свойства принадлежат **самому классу**, а не его экземплярам.

- Доступ — через имя класса и `::`: `Counter::increment()`, `Counter::$count`.
- Статическое свойство **одно** на весь класс — значение общее для всех.
- В статическом методе **нет** `$this`, но есть `self::`, `static::`, `parent::`.

**Где уместно:**

- утилитарные функции без состояния (`StringHelper::slug()`)
- фабричные методы (`Money::fromCents()`)
- счётчики уровня класса

**Минусы:**

- сложно мокать в тестах — скрытая глобальная зависимость
- статичное состояние живёт между тестами (надо сбрасывать)
- **Антипаттерн**: Singleton через `private static $instance` — глобальный state в обёртке.',
                'code_example' => '<?php
class Counter
{
    public static int $count = 0;

    public static function increment(): void
    {
        self::$count++;
    }
}

Counter::increment();
Counter::increment();
echo Counter::$count; // 2',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.static_members',
                'difficulty' => 3,
                'question' => 'В чём разница между self, static и parent в PHP?',
                'answer' => 'Три ключевых слова для **доступа к классу из себя самого** — но указывают на **разные** классы.

| Конструкция | Указывает на | Когда определяется |
|---|---|---|
| **`self::`** | класс, **где написан** код | **на этапе компиляции** (early binding) |
| **`static::`** | **фактический** класс, через который вызвали (**LSB** — late static binding) | **в runtime** |
| **`parent::`** | **родительский** класс | на этапе компиляции |

**Главный практический эффект — фабрики:**

- **`new self()`** — **всегда** создаёт объект **того класса, где написана строка**. Даже если вызвали из наследника.
- **`new static()`** — создаёт объект **класса-наследника**, через который пришёл вызов.

**Парный кейс — статические свойства:**

- **`self::$prop`** обращается к свойству класса, где написан код.
- **`static::$prop`** — к свойству **вызывающего** класса; если наследник переопределил — увидим его значение.

**`parent::`:**

- вызов конструктора предка: `parent::__construct(...)`.
- вызов **родительской** реализации перегруженного метода: `parent::handle()`.
- **не** проходит дальше через всю цепочку — только **один шаг вверх**.

**Где это важно:**

- **ORM / Active Record**: базовый класс `Model` с `static function create(): static` — каждый наследник (`User`, `Post`) получает фабрику, возвращающую **свой** тип.
- **Singleton-паттерн с наследованием**: `self::$instance` сделает наследников **общими**, `static::$instance` — у каждого свой.
- **Шаблонные методы**: `static::class` для логирования имени фактического класса.

**Подвох:** для **`new self()`** в trait — `self` указывает на **класс, использующий trait**, не на сам trait. Это часто сюрприз.',
                'code_example' => '<?php
class A
{
    public static function createSelf(): self
    {
        return new self();
    }
    public static function createStatic(): static
    {
        return new static();
    }
}

class B extends A {}

var_dump(B::createSelf());   // object(A)
var_dump(B::createStatic()); // object(B)',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.static_members',
                'difficulty' => 1,
                'question' => 'Что такое static простыми словами?',
                'answer' => '`static` — метод или свойство принадлежат самому **классу**, а не конкретному объекту.

- Доступ через имя класса и `::`: `Counter::$count`, `Counter::increment()`.
- **Объект создавать не нужно.**
- Внутри статического метода **нет** `$this` — но есть `self::` и `static::` для обращения к другим статическим членам.

**Когда брать:** утилиты без состояния (`StringHelper::slug()`), фабрики (`Money::fromCents()`), счётчики уровня класса.',
                'code_example' => '<?php
class Counter
{
    public static int $count = 0;

    public static function increment(): void
    {
        self::$count++; // $this недоступен
    }
}

Counter::increment();
Counter::increment();
echo Counter::$count; // 2 - значение общее для всего класса',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.static_members',
                'difficulty' => 2,
                'question' => 'Когда использовать static, а когда — нет?',
                'answer' => '**Брать `static`, когда:**

- **Чистая утилита** без состояния и без зависимостей: `StringHelper::slugify()`.
- **Фабрика / named constructor**: `Money::fromCents(100)`, `DateTime::createFromFormat(...)`.
- **Счётчики** уровня класса, кэш в памяти процесса.

**НЕ брать, когда:**

- Метод обращается к **БД, файлам, HTTP, времени** — в тестах не подменишь.
- Хочется «удобно вызвать отовсюду» — это **скрытая глобальная зависимость**, плохой запах.
- Тянет завести `private static $instance` — это **Singleton**, антипаттерн в 95% случаев.

**Правило:** есть зависимости или состояние → обычный класс через **DI**. Нет ни того ни другого → можно `static`.',
                'code_example' => '<?php
// ✅ Хорошо: чистая утилита без состояния
final class StringHelper
{
    public static function slugify(string $s): string
    {
        return strtolower(preg_replace(\'/\W+/\', \'-\', $s));
    }
}

// ✅ Хорошо: named constructor (фабрика)
final class Money
{
    private function __construct(public int $cents) {}
    public static function fromCents(int $c): self { return new self($c); }
}

// ❌ Плохо: статика дёргает БД - не подменяется в тестах
class UserRepoBad
{
    public static function find(int $id): ?User
    {
        return DB::query(\'SELECT * FROM users WHERE id = ?\', [$id]);
    }
}

// ✅ Лучше: обычный класс через DI - легко мокается
class UserRepo
{
    public function __construct(private Database $db) {}
    public function find(int $id): ?User { return $this->db->find($id); }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.static_members',
                'difficulty' => 2,
                'question' => 'Что такое late static binding (static::) простыми словами?',
                'answer' => '**Late Static Binding (LSB)** — отложенное определение класса до момента вызова.

- `self::` — класс, **ГДЕ написан** код (раннее связывание, разрешается на этапе компиляции).
- `static::` — класс, **ОТ которого реально вызвали** метод (разрешается во время выполнения).

**Пример эффекта:**

- `new self()` в методе `Model` — **всегда** создаёт `Model`, даже из `User::create()`.
- `new static()` — создаёт `User`, если вызвали `User::create()`.

**Где нужно:** фабрики и shared-методы в **базовом классе**, которые должны возвращать конкретного наследника (паттерн в Eloquent, Active Record, многих ORM).',
                'code_example' => '<?php
class Model
{
    public static function createSelf(): self
    {
        return new self();   // всегда Model
    }

    public static function createStatic(): static
    {
        return new static(); // тот класс, через который вызвали
    }
}

class User extends Model {}

var_dump(User::createSelf());   // object(Model)
var_dump(User::createStatic()); // object(User) - LSB',
                'code_language' => 'php',
            ],
        ];
    }
}
