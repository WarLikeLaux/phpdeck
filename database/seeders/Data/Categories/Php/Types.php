<?php

namespace Database\Seeders\Data\Categories\Php;

class Types
{
    public static function all(): array
    {
        return [
            [
                'category' => 'PHP',
                'question' => 'Что такое nullable types и union types?',
                'answer' => '**Nullable type** `?Type` (PHP 7.1+) — значение может быть `Type` или `null`. Эквивалент `Type|null`.

**Union type** `Type1|Type2` (PHP 8.0+) — значение может быть **любым из перечисленных** типов.

**Связанные типы — кратко** (отдельные карточки):

| Тип | Версия | Смысл |
|---|---|---|
| **`?T`** | 7.1 | `T` или `null` |
| **`T1\\|T2`** | 8.0 | один из перечисленных (OR) |
| **`mixed`** | 8.0 | любой тип (включая `null`) |
| **`never`** | 8.1 | функция не возвращает (throw / exit / loop) |
| **`T1&T2`** | 8.1 | объект реализует **все** перечисленные интерфейсы (AND) |
| **`(A&B)\\|C`** | 8.2 (DNF) | комбинация union и intersection |
| **`true`/`false`/`null`** | 8.2 | как самостоятельные типы |

**Правила union-типов:**
- **избыточные** типы запрещены: `int|int`, `int|mixed`
- `?T` нельзя комбинировать с `null`: `?int|null` запрещено
- порядок не имеет значения для проверки
- **ковариантность возврата**: наследник может **сузить** union (`int|string` → `int`)
- **контравариантность параметра**: наследник может **расширить** union параметра',
                'code_example' => '<?php
// Nullable
function find(int $id): ?User {
    return $id > 0 ? new User() : null;
}

// Union (PHP 8)
function format(int|float|string $value): string {
    return (string) $value;
}

// Intersection (PHP 8.1)
function process(Countable&Iterator $items): void {
    echo count($items);
    foreach ($items as $item) {}
}

// DNF (PHP 8.2)
function handle((Countable&Iterator)|null $x): void {}

// never
function abort(string $msg): never {
    throw new RuntimeException($msg);
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.types',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое readonly свойства и классы?',
                'answer' => '**`readonly`** — модификатор, обеспечивающий **запись ровно один раз** в области видимости класса. Идеально для **immutable value objects** и DTO.

**Эволюция фичи:**

| Версия | Что разрешено |
| --- | --- |
| **PHP 8.1** | **`readonly property`** на отдельных свойствах класса |
| **PHP 8.2** | **`readonly class`** — все нестатические свойства автоматически readonly |
| **PHP 8.3** | переинициализация readonly **внутри `__clone()`** того же класса (deep-clone, сброс кеша) |
| **PHP 8.4** | property hooks могут писать readonly через `set` hook |

**Правила и ограничения:**

| Правило | Деталь |
| --- | --- |
| Запись разрешена **только из области видимости класса** | сабкласс с тем же scope тоже может, чужой код — нет |
| Запись **ровно один раз** | повторное присваивание → `Error: Cannot modify readonly property` |
| Свойство **должно быть типизированным** | нельзя `public readonly $x;` без типа |
| **Static** свойства быть readonly **не могут** | только инстанс-свойства |
| **Нельзя `unset()`** на readonly | значение нельзя «сбросить» |
| `clone` без `__clone()` копирует значения **как есть** | readonly сохраняется в новой копии |

**Wither-паттерн до PHP 8.3:**

```php
final class Point {
    public function __construct(
        public readonly float \$x,
        public readonly float \$y,
    ) {}

    public function withX(float \$x): self {
        return new self(\$x, \$this->y);  // новый объект
    }
}
```

**Что можно с PHP 8.3 — переинициализация в `__clone()`:**

```php
final class Order {
    public function __construct(
        public readonly Money \$total,
        public readonly array \$items,
    ) {}

    public function __clone(): void {
        // OK внутри __clone того же класса
        \$this->total = clone \$this->total;  // deep clone
    }
}
```

**Что ДО СИХ ПОР НЕЛЬЗЯ** даже в 8.3:

```php
public function withX(float \$x): self {
    \$clone = clone \$this;
    \$clone->x = \$x;  // ❌ Error — модификация СНАРУЖИ __clone
    return \$clone;
}
```

**`readonly class` (PHP 8.2+):**

```php
final readonly class Coordinates {
    public function __construct(
        public float \$lat,
        public float \$lng,
    ) {}
    // все свойства автоматически readonly
}
```

**Подводные камни:**

- **`readonly` не делает объект immutable глубоко** — если свойство держит `Collection`/массив, **внутреннее содержимое можно менять**: `\$obj->items[] = ...` запретит запись `$items`, но `\$obj->items->push(...)` (если items — мутируемая Collection) пройдёт
- **сериализация**: `serialize`/`unserialize` корректно восстанавливает readonly через `__unserialize`
- **`ReflectionProperty::setValue()`** **обходит** readonly — это нужно для ORM и сериализаторов
- **clone** копирует значение readonly в новый объект — у клона свойство **уже инициализировано** и записать нельзя (если не в `__clone`)',
                'code_example' => '<?php
class Point {
    public function __construct(
        public readonly float $x,
        public readonly float $y,
    ) {}

    // ✅ Wither - канонический паттерн для immutable update, работает с 8.1
    public function withX(float $x): self {
        return new self($x, $this->y);
    }
}

$p = new Point(1, 2);
// $p->x = 5; // Error: cannot modify readonly

// ❌ ТАК НЕЛЬЗЯ даже на 8.3 - модификация снаружи __clone()
// public function withX(float $x): self {
//     $clone = clone $this;
//     $clone->x = $x; // Error: Cannot modify readonly property
//     return $clone;
// }

// ✅ PHP 8.3 - reinitialize ТОЛЬКО внутри __clone(); полезно для
// глубокого клонирования и сброса кеша на копии
class Order {
    public function __construct(
        public readonly DateTimeImmutable $createdAt,
        public readonly Money $total,
    ) {}

    public function __clone(): void {
        // OK - readonly можно записать в __clone того же класса
        $this->total = clone $this->total; // deep clone вложенного VO
    }
}

// PHP 8.2 readonly class
readonly class Coordinates {
    public function __construct(
        public float $lat,
        public float $lng,
    ) {}
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.types',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем intersection types отличаются от union types?',
                'answer' => '| Свойство | **Union** `T1\\|T2` | **Intersection** `T1&T2` |
|---|---|---|
| Версия | PHP **8.0** | PHP **8.1** |
| Логика | **ИЛИ** | **И** |
| Что разрешено | `int`, `string`, `array`, объекты — скаляры тоже | **только интерфейсы/классы** объектов |
| Скаляры | да | **нет** |
| Пример | `int\\|string` | `Countable&Iterator` |

**Intersection** требует, чтобы объект **одновременно** реализовывал все указанные интерфейсы или был экземпляром всех указанных классов (на практике почти всегда — несколько интерфейсов, multiple inheritance классов в PHP нет).

**Где применять:**

| Случай | Тип |
|---|---|
| «Это либо `User`, либо `null`» | `?User` или `User\|null` |
| «Это число или строка» | `int\|string` |
| «Это объект, который и `count`-имый, и итерируемый» | `Countable&Iterator` |
| «Любой тип» | `mixed` (но лучше уточнять) |

**DNF (PHP 8.2)** — комбинация: `(Countable&Iterator)|null` — intersection в скобках, объединение через `|`.

**Подводный камень intersection:** анализаторы пока хуже подсказывают, чем union; объекты, не реализующие нужных интерфейсов вместе, требуют **обёрток**.',
                'code_example' => '<?php
// Union — int ИЛИ string
function format(int|string $value): string {
    return (string) $value;
}

// Intersection — реализует И Countable, И Iterator
function dump(Countable&Iterator $items): void {
    echo count($items);
    foreach ($items as $i) {}
}

// DNF (PHP 8.2) — комбинация: (A&B) ИЛИ null
function maybe((Countable&Iterator)|null $x): void {}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.types',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое mixed-тип в PHP 8 и когда его использовать?',
                'answer' => '**`mixed`** (PHP 8.0+) — тип «**любое значение**»: `int`, `float`, `string`, `bool`, `array`, `object`, `callable`, `resource`, `null`.

**По сути:** эквивалент **отсутствия типа** в старом коде, но **явно объявлен** в сигнатуре.

**Зачем нужен:**
- сделать «нет типа» **явным** — IDE и анализаторы видят, что это намеренно
- параметры/возврат, **по контракту** действительно произвольные: `__get`, `__set`, generic-контейнеры, JSON-парсеры
- эквивалент **`any`** в TypeScript

**Главный минус:**
- **отключает** большинство проверок статанализа
- PHPStan/Psalm не могут вывести типы дальше
- IDE не подсказывает методы возвращаемого значения

**Правило применения:**

| Сценарий | Тип |
|---|---|
| Точно знаю узкий набор | **`int\\|string`** (предпочтительнее) |
| Возможен `null` плюс что-то | **`?T`** |
| Магические методы (`__get`, `__set`) | **`mixed`** обязательно |
| Container `get($id)` | `mixed` (но добавьте PHPDoc generic) |

**Альтернативы для лучшей типизации:**
- **Generics через PHPDoc** (PHPStan/Psalm): `@template T` + `@return T`
- сделать **специализированные** методы (`findUser()` вместо `find()`)

**Особенности:** `mixed` **ковариантен** — наследник может сузить до `int|string`, но не расширить (он уже широкий).',
                'code_example' => '<?php
// ❌ mixed — теряем проверки типов
function get(string $key): mixed {
    return $this->data[$key] ?? null;
}

// ✅ Лучше — узкий union
function get(string $key): int|string|null {
    return $this->data[$key] ?? null;
}

// mixed уместен в магических методах
class Bag {
    public function __get(string $name): mixed { /* ... */ }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.types',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое DNF-типы в PHP 8.2 и зачем нужна именно дизъюнктивная нормальная форма?',
                'answer' => '**DNF (Disjunctive Normal Form)** — стандартная форма булевой алгебры: **ИЛИ из групп И**, то есть `(A & B & C) | (D & E) | F`.

**В PHP 8.2+** разрешено комбинировать **union** и **intersection** типы, **но только в DNF**. Каждая intersection-группа должна быть **в скобках**.

**Эволюция системы типов:**

| Версия | Возможности |
| --- | --- |
| **7.0** | scalar types: `int`, `string`, `bool`, `float`, `array` |
| **7.1** | `?T` (nullable), `void`, `iterable` |
| **8.0** | **union** `T1\\|T2`, `mixed`, `false` (только в union) |
| **8.1** | **intersection** `T1&T2`, `never`, `readonly` |
| **8.2** | **DNF** `(T1&T2)\\|T3`, `true`/`false`/`null` standalone, `readonly` class |

**Канонический пример:**

```php
// Раньше: либо union, либо intersection — не вместе
function f(Countable&Traversable \$x): void {}   // OK
function f(Countable|Traversable \$x): void {}   // OK
function f(Countable&Traversable|null \$x): void {}  // ERROR до 8.2

// PHP 8.2 — DNF
function f((Countable&Traversable)|null \$x): void {}     // OK
function f((A&B)|(C&D)|null \$x): void {}                 // OK
function f((A&B)|C \$x): void {}                          // OK
```

**Что разрешено и что НЕ разрешено:**

| Тип | Разрешено? | Почему |
| --- | --- | --- |
| `(A&B)|null` | ✅ | DNF |
| `(A&B)|(C&D)` | ✅ | DNF |
| `(A|B)&C` | ❌ | это **CNF**, не DNF |
| `A&B|C` (без скобок) | ❌ | синтаксически двусмысленно |
| `((A&B))|C` | ❌ | вложенные скобки запрещены |

**Зачем именно DNF, а не CNF:**

- DNF **проще для системы типов** — каждая ветка `|` независима, проверка реализуется как «удовлетворяет хотя бы одной»
- CNF потребовал бы **дистрибуции** и привёл бы к комбинаторному взрыву при подтипировании
- DNF — это **«перечисли допустимые комбинации»**, что естественнее для type-checking

**Применение:**

- **`(Countable&Traversable)|null`** — «коллекция (или массивоподобное) **или** null», без введения промежуточного интерфейса `CountableTraversable extends Countable, Traversable`
- API, принимающие либо «сборную» абстракцию, либо `null`
- замена hack-ам со множественным `instanceof` в одном методе

**Подводный камень:** анализаторы (`PHPStan`, `Psalm`) поддерживают DNF, но их **сужение типа** (narrowing) после `instanceof` иногда работает неточно — проверяйте предупреждения.',
                'difficulty' => 4,
                'topic' => 'php.types',
            ],
            [
                'category' => 'PHP',
                'question' => 'Зачем PHP 8.2 разрешил null, false и true как самостоятельные типы?',
                'answer' => '**До PHP 8.2** значения `false`/`true`/`null` могли стоять **только внутри union** (например, `string|false` для `strpos`). Нельзя было сказать «функция всегда возвращает `false`» — приходилось писать `bool`, что не отражало точный контракт.

**С PHP 8.2** — самостоятельные типы:

```php
function failed(): false { return false; }
function ok(): true { return true; }
function nothing(): null { return null; }
```

**Главное практическое применение:**

| Случай | Тип |
|---|---|
| Legacy-функции вроде `strpos` | `int\\|false` — корректно описывает контракт |
| Заглушка/throw-функция | `false` или `never` |
| Singleton-возврат | `true` или конкретный enum |
| Симметрия `true`/`false` в API | оба типа |

**Зачем `true` как тип:**
- симметрия с `false`
- статанализ знает: после вызова такой функции **сужение типа** возможно
- инструменты могут проверить, что вы реально возвращаете именно `true`

**Тип `null`** официально оформил то, что и так использовалось:
- `?T` — это сахар над `T|null`
- теперь `null` равноправный «гражданин» системы типов',
                'code_example' => '<?php
// До PHP 8.2 — нельзя написать просто ": false"
function findUser(int $id): User|false {
    return $repository->find($id) ?? false;
}

// PHP 8.2 — отдельные null/false/true
function alwaysFails(): false {
    return false;
}

function isPhp(): true {
    return true;
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.types',
            ],
            [
                'category' => 'PHP',
                'question' => 'Зачем в PHP 8.4 объявили устаревшим неявный nullable у параметров?',
                'answer' => '**Старое поведение:** конструкция `function f(string $name = null)` **неявно** делала параметр nullable — значением по умолчанию был `null`, и PHP молча разрешал передавать `null` в `string`-параметр.

**Чем это плохо:**
- **противоречит принципу «тип говорит правду»** — в сигнатуре `string`, а на деле может быть `null`
- источник багов при **рефакторинге**: убрал значение по умолчанию → код стал жёстче типизированным → всплыли реальные `null`-передачи
- IDE/анализаторы не могут вывести правильный тип параметра
- разное поведение между **сигнатурой** и **PHPDoc**

**С PHP 8.4:** такой код выдаёт **`E_DEPRECATED`**. В будущей версии станет fatal.

**Правильно:**
```php
function f(?string $name = null) { ... }
// или
function f(string|null $name = null) { ... }
```

**Что делать с legacy:**
- **Rector** имеет правило `AddNullableTypeFromDefaultRector` — добавляет `?` автоматически
- PHPStan правило `disallowImplicitNullable` помечает места
- ручная миграция: **только** добавить `?` перед типом — поведение не меняется

**Подводный камень:** убирая значение по умолчанию (`= null`), но оставляя тип `?string`, вы делаете параметр **обязательным**, но с разрешённым `null`. Это разные вещи: `?string` — можно передать `null` явно, `?string = null` — можно не передавать вообще.',
                'code_example' => '<?php
// ❌ До PHP 8.4 — неявный nullable, c 8.4 — deprecation
function greet(string $name = null): string {
    return "Hi, " . ($name ?? "Guest");
}

// ✅ Явный nullable — работает на всех версиях
function greet(?string $name = null): string {
    return "Hi, " . ($name ?? "Guest");
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.types',
            ],
            [
                'category' => 'PHP',
                'question' => 'Какие значения PHP считает ложными при приведении к bool?',
                'answer' => '**Falsy** (приводятся к `false`):
- `false`
- **`0`**, **`-0`** (целые)
- **`0.0`**, **`-0.0`** (float)
- **`""`** — пустая строка
- **`"0"`** — строка из одного нолика ⚠️
- **`[]`** — пустой массив
- `null`
- `SimpleXML`-объект из пустых тегов

**Truthy — всё остальное**, включая ловушки:

| Значение | Результат | Почему |
|---|---|---|
| `"false"` | **`true`** | непустая строка ≠ `"0"` |
| `"0.0"` | **`true`** | не равно `"0"` |
| `"00"` | **`true`** | не равно `"0"` |
| `"null"` | **`true`** | непустая строка |
| `[0]` | **`true`** | непустой массив |
| `new stdClass` | **`true`** | любой объект |

**`empty($x)` эквивалентно `!(bool)$x`** (с поправкой: `empty` не даёт warning на неопределённой переменной).

Поэтому `empty("0.0") === false` и `(bool)"0.0" === true` **согласованы**.

**Расхождение возникает с нестрогим равенством:**
```php
"0.0" == 0      // true  — числовое сравнение
(bool)"0.0"    // true  — строка непустая
```

Это разные алгоритмы: `==` пытается **сравнить как числа**, `(bool)` смотрит на **«пустота строки»** (только `""` и `"0"` — пустые).

**Best practice:** для проверок наличия данных — **явные** сравнения (`$x !== ""`, `$x !== null`, `count($x) > 0`).',
                'difficulty' => 3,
                'topic' => 'php.types',
            ],
            [
                'category' => 'PHP',
                'question' => 'Почему числа с плавающей точкой в PHP неточны и как корректно сравнивать float?',
                'answer' => '**Почему float неточны:**
- PHP хранит float в формате **IEEE 754 double** (64-битный binary)
- многие десятичные дроби (`0.1`, `0.2`, `0.7`) в **двоичной** системе — **бесконечные периодические**
- уже на этапе **литерала** теряется точность

**Классический пример:**
```php
0.1 + 0.2          // 0.30000000000000004
0.1 + 0.2 == 0.3   // false ⚠️
```

**Правильное сравнение — через эпсилон:**
```php
abs($a - $b) < $epsilon
```

**Выбор `$epsilon`:**

| Сценарий | `$epsilon` |
|---|---|
| **Одна** элементарная операция | `PHP_FLOAT_EPSILON` (≈ 2.22e-16) |
| Накопленная ошибка после цикла | существенно больше, **1e-9** ÷ **1e-6** |
| Зависит от **величины** значений | **относительный**: `abs($a - $b) / max(abs($a), abs($b)) < $eps` |

**Где НЕ использовать float:**
- **деньги**, биллинг, финансовые расчёты
- **identifiers**, ключи в map (потеря точности при больших числах)
- сравнение через `==` или `===` — почти всегда баг

**Альтернативы для точной арифметики:**

| Назначение | Решение |
|---|---|
| Деньги | **`Money`** value object с **целыми** копейками |
| Произвольная точность десятичная | **`BCMath`** (`bcadd`, `bccomp`) |
| Большие **целые** | **`GMP`** |
| В БД | **`DECIMAL(19,4)`**, не `FLOAT`/`DOUBLE` |',
                'difficulty' => 3,
                'topic' => 'php.types',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем BCMath отличается от GMP и когда что выбирать?',
                'answer' => '**Два расширения для арифметики произвольной точности** в PHP, с разными нишами.

| | **BCMath** (`ext-bcmath`) | **GMP** (`ext-gmp`) |
| --- | --- | --- |
| **Что поддерживает** | целые **И дробные** числа | **только целые** произвольной длины |
| **Внутреннее представление** | **строки** в PHP | объекты `GMP` (PHP 5.6+) поверх **libgmp** (C-нативная) |
| **API** | процедурный: `bcadd`, `bcsub`, `bcmul`, `bcdiv`, `bccomp`, `bcscale` | объектный + процедурный: `gmp_add`, `gmp_pow`, `gmp_powm`, операторы `+ - * /` через ArrayAccess |
| **Точность** | задаётся `bcscale()` глобально или 3-м параметром каждого вызова | абсолютная (это целые числа) |
| **Скорость** | медленнее — строковая арифметика в PHP | **в 10-100× быстрее** на больших числах — нативная C |
| **Размер чисел** | ограничен только памятью | то же, но эффективнее упаковано |

**BCMath — для денег и десятичной арифметики:**

```php
bcscale(4);                       // 4 знака после точки для всех операций

\$price = "199.99";
\$tax   = "20.00";
\$total = bcadd(\$price, bcmul(\$price, bcdiv(\$tax, "100")));
// "239.9880" — точно

// Сравнение НЕЛЬЗЯ через == ("100.0" != "100"), нужно bccomp
if (bccomp(\$total, "200.00") > 0) { /* больше */ }
```

**GMP — для криптографии и теории чисел:**

```php
\$a = gmp_init("12345678901234567890");
\$b = gmp_init("98765432109876543210");
\$sum = \$a + \$b;             // через ArithmeticOperator
\$mod = gmp_mod(\$sum, gmp_init("1000000007"));

// Модульное возведение в степень — для RSA
\$cipher = gmp_powm(\$plain, \$e, \$n);

// Поиск простых
\$prime = gmp_nextprime(gmp_init("100000000000"));
```

**Когда что брать:**

| Задача | Выбор |
| --- | --- |
| Деньги, биллинг, проценты, валюты | **BCMath** + хранение `DECIMAL` в БД |
| Финансовый калькулятор с округлениями | BCMath + `bcadd`/`bcmul` с явным scale |
| RSA, ECC, Diffie-Hellman | **GMP** |
| Хеш-задачи (BTC-mining прототип, факторизация) | GMP |
| Generative ID (Snowflake-подобные) | GMP для битовых операций на больших int |
| Финансовые расчёты, где нужны простые ставки | BCMath |

**Подводные камни:**

| BCMath | GMP |
| --- | --- |
| `bcscale()` **глобальная** — изменение в одном месте ломает другие модули | объекты `GMP` нельзя серилизовать стандартно в JSON без преобразования |
| Все аргументы **строки**, `bcadd(1.5, 2.5)` приведёт `float→string` с потерей | целочисленность — нельзя дроби |
| Производительность плохая в hot-path | требует установленной libgmp в Docker-образе |

**Готовые пакеты** для денег (поверх BCMath или string-int): `moneyphp/money`, `brick/money`. Для math (поверх GMP): `brick/math`.',
                'difficulty' => 4,
                'topic' => 'php.types',
            ],
            [
                'category' => 'PHP',
                'question' => 'Почему деньги нельзя хранить в float и какой тип использовать в БД?',
                'answer' => '**Почему `float` — не для денег:**
- в PHP и большинстве СУБД `float` — это **IEEE 754 double**
- не может точно представить `0.1`, `0.7`, `0.2` и многие другие десятичные
- после цепочки сложений/умножений **накапливается ошибка** в доли копейки
- в финансовом учёте это **недопустимо** (acceptance bug, расхождение баланса с банком)

**Два правильных подхода:**

**1. `DECIMAL(precision, scale)` в БД + строки/`BCMath` в PHP**

| База | Тип | Применение |
|---|---|---|
| MySQL/PostgreSQL | `DECIMAL(19, 4)` или `NUMERIC` | стандарт |
| Чтение в PHP | как **строка** (`"123.45"`) | избежать конверсии в float |
| Арифметика | **`BCMath`** (`bcadd`, `bcmul`, `bccomp`) | точная десятичная |

**2. Целое число «в копейках/центах»** + Money value-object

```php
final class Money {
    public function __construct(
        public readonly int $amount,   // в копейках
        public readonly string $currency,
    ) {}
    public function add(Money $other): self { /* int + int */ }
}
```

| Плюс | Минус |
|---|---|
| Никаких float-ошибок | Нужно помнить про precision currency (JPY — 0, USD — 2, BHD — 3) |
| Быстрая арифметика (int) | Большие суммы могут переполнить int64 на экзотических случаях |
| Простая сериализация | Парсинг/форматирование требует кода |

**Готовые пакеты:** `moneyphp/money`, `brick/money` — реализуют корректное округление, конверсии валют, форматирование.

**Float остаётся** для научных расчётов, где приближённость допустима.',
                'difficulty' => 3,
                'topic' => 'php.types',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое zval и как он устроен в PHP 7+?',
                'answer' => '**`zval`** (zend value) — внутренняя структура **Zend Engine**, в которую завёрнута **любая** PHP-переменная. Понимание zval помогает объяснить, почему scalar-операции быстрые, почему **copy-on-write** работает на массивах, и почему `WeakMap` отличается от обычного массива.

**Размер: 16 байт в PHP 7+** (было 48 в PHP 5):

```
┌──────────────────────────────────────┐  16 bytes total
│  value (8 bytes)                     │
├──────────────────────────────────────┤
│  u1 (4 bytes): type info + flags     │
├──────────────────────────────────────┤
│  u2 (4 bytes): vars (next, cache,    │
│                  fe_pos, ...)        │
└──────────────────────────────────────┘
```

**Поле `value` — union 8 байт:**

| Что хранит | Случай |
| --- | --- |
| **`zend_long lval`** | `IS_LONG` (int) |
| **`double dval`** | `IS_DOUBLE` (float) |
| **`zend_string *str`** | `IS_STRING` (указатель на string-структуру с refcount) |
| **`zend_array *arr`** | `IS_ARRAY` |
| **`zend_object *obj`** | `IS_OBJECT` |
| **`zend_resource *res`** | `IS_RESOURCE` |
| **`zend_reference *ref`** | `IS_REFERENCE` (когда `&\$var`) |

**Поле `u1` — type info:**

| Бит | Значение |
| --- | --- |
| тип (`IS_LONG`, `IS_STRING`, ...) | 8 бит |
| флаги: `IS_TYPE_REFCOUNTED`, `IS_TYPE_COLLECTABLE`, `IS_INTERNED` | для скаляров не установлены |

**Поле `u2`** используется по-разному:

| Контекст | Что лежит |
| --- | --- |
| Элемент `HashTable` | смещение **следующего bucket** |
| Свойство объекта | индекс в `property_offset` |
| `foreach` | позиция в массиве (`fe_pos`) |

**Ключевая оптимизация PHP 7+: refcount вынесен из zval.**

В PHP 5 zval содержал поле `refcount` — **скаляры тоже имели подсчёт ссылок**, что давало overhead на простые операции `\$i = \$j`.

В PHP 7+ refcount **внутри сложных структур** (`zend_string`, `zend_array`, `zend_object`). Скаляры (`int`, `float`, `bool`, `null`) хранятся **прямо в zval по значению** — никакой аллокации, никакого refcount.

**Следствия:**

| Тип | Где живёт значение | Refcount? |
| --- | --- | --- |
| `null`, `false`, `true` | в zval напрямую (по флагу `u1`) | нет |
| `int`, `float` | в zval напрямую | нет |
| `string` | в `zend_string` через указатель | да |
| `array` | в `zend_array` через указатель | да |
| `object` | в `zend_object` через указатель | да |
| `resource` | в `zend_resource` | да |

**Что это объясняет:**

- **scalar assignment** `\$a = \$b` для int/float — это просто **`memcpy`** 16 байт, никакой аллокации
- **массивы copy-on-write**: `\$a = \$b` копирует указатель + увеличивает refcount; **отдельная копия** создаётся только при модификации
- **строки interned**: одинаковые литералы в коде делят один `zend_string` (флаг `IS_INTERNED`)
- **GC циклы**: только refcounted-типы участвуют в cycle collector

**Где это знание помогает:**

- понимание утечек памяти (`memory_get_usage(true)`) — почему массив на миллион элементов **не освобождается** мгновенно
- профилирование hot-path: «много `string`-аллокаций» = «у меня везде `"prefix_" . \$id` без interning»
- объяснение, почему **`WeakMap`** требует отдельного механизма (refcount держит объект жить)',
                'difficulty' => 4,
                'topic' => 'php.types',
            ],
            [
                'category' => 'PHP',
                'question' => 'Влияет ли типизация параметров и свойств на производительность PHP?',
                'answer' => '**Краткий ответ:** влияет, обычно в плюс, **но не так, как принято думать** — «строже типы → быстрее» работает только косвенно.

**Что реально происходит на низком уровне:**

**1. Типы — runtime-проверка → микро-overhead**

На каждый вызов типизированной функции и каждое присваивание `typed property` PHP проверяет соответствие. Без `strict_types=1` ещё добавляется попытка **type coercion** (`"5"` → `5`). Это микро-overhead **в наносекундах**.

**2. Типы дают `opcache`/JIT информацию для оптимизации → плюс на CPU-bound**

Tracing JIT использует типизированные сигнатуры, чтобы генерировать **специализированный нативный код**:

| Что без типов | Что с типами |
| --- | --- |
| Каждый вызов проверяет тип аргумента | известно «параметр — `int`», проверка убирается |
| Сложение `\$a + \$b` — universal `ADD` opcode | прямой машинный `addq` на регистрах |
| Возврат — упаковка в `zval` | для int-возврата без аллокации |

На CPU-bound коде (математика, обработка байтов) выигрыш JIT с типами **+30-50%**.

**3. `typed properties` хранятся эффективнее**

| | Нетипизированное `$age` | `public int $age` |
| --- | --- | --- |
| Хранение | `zval` без подсказок | `zval` с известным типом |
| Доступ | проверка типа на каждое чтение | прямой |
| Память | стандарт | меньше per-instance, особенно при массивах объектов |
| Init | `null` по умолчанию | **uninitialized** до явного присваивания |

**4. В реальных бенчмарках на веб-приложениях:**

| Сценарий | Выигрыш от типов |
| --- | --- |
| Чистый Laravel-API с типизированными моделями vs без | **0-3%** |
| CPU-bound (image processing, парсинг) с JIT | **15-40%** |
| Чисто `int`/`float`-арифметика в цикле | **до 2-3×** |

**Главная польза типов — НЕ скорость, а:**

- **корректность** — `TypeError` ловится сразу, а не через 3 шага в null-pointer
- **статанализ** — PHPStan/Psalm на максимальном уровне `9`/`max`
- **рефакторинг** — IDE видит, где использовать `findOrFail` vs `find`
- **read-time** — читая сигнатуру `public function f(User \$u): Money` понятно всё
- **API-контракт** — публичные методы документированы машинно

**Best practice senior-уровня:**

| Что делать | Зачем |
| --- | --- |
| `declare(strict_types=1)` в **каждом** файле | предотвратить тихую коэрцию |
| Типизировать **все** свойства и параметры | максимум выгоды для анализа и JIT |
| `readonly` свойства для иммутабельности | + проверки в compile-time |
| Использовать **enum** вместо string-констант | type-safety на уровне множества значений |
| Generics через **PHPDoc** (`@template`) | то, чего нет нативно в PHP |
| Применять **PHPStan/Psalm** на максимуме в CI | ловить ошибки до прода |

**Главное правило:** делайте typed properties и `strict_types` ради **качества и поддерживаемости**, а не ради 1% RPS — это бонус, а не цель.',
                'difficulty' => 4,
                'topic' => 'php.types',
            ],
        ];
    }
}
