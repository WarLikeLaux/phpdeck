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
                'answer' => 'Версии и правила: PHP 8.1 — readonly property: запись ровно один раз из области видимости класса, далее изменение даёт Error. Идеально для immutable value objects. PHP 8.2 — readonly class: все нестатические свойства автоматически readonly. Ограничения: только типизированные свойства, нельзя статические. PHP 8.3 — переинициализация readonly-свойства строго ВНУТРИ __clone() того класса, где оно объявлено (deep-cloning вложенных readonly-объектов и сброс кеша на копии); снаружи __clone() запись по-прежнему Error. До 8.3 «обновлённую» копию делали wither-методом new self(...).',
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
                'answer' => 'DNF-types позволяют комбинировать union- и intersection-типы, но только в дизъюнктивной нормальной форме — то есть как ИЛИ из групп И, например (Countable&Traversable)|null. Каждая intersection-группа обязана быть в скобках, иначе синтаксис неоднозначен. Это снимает прежний запрет на смешение & и | и позволяет описывать вещи вроде «коллекция, либо null», не вводя промежуточный интерфейс.',
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
                'answer' => 'BCMath работает с числами произвольной точности, представленными как строки, и поддерживает дробную часть и масштаб (bcscale, bcadd, bccomp), поэтому подходит для денежных расчётов, где важна десятичная точность. GMP оперирует только целыми числами произвольной длины, использует объектный или ресурсный API и заметно быстрее BCMath за счёт нативной libgmp. Поэтому GMP выбирают для криптографии и работы с большими целыми, а BCMath — для финансов и точной десятичной арифметики, где деньги хранятся как DECIMAL в БД.',
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
                'answer' => 'zval — внутренняя структура Zend Engine, в которую завёрнута любая переменная PHP. В PHP 7 её сжали до 16 байт и состоит она из трёх частей: value (объединение размером 8 байт, где лежит lval/dval для скаляров или указатель на zend_string, zend_array, zend_object), u1 с тегом типа и флагами, и u2 для вспомогательных данных вроде следующего бакета хеш-таблицы. Refcount вынесен из zval в сами сложные структуры, поэтому скаляры (int, float, bool, null) хранятся прямо в zval без аллокаций в куче и без подсчёта ссылок.',
                'difficulty' => 4,
                'topic' => 'php.types',
            ],
            [
                'category' => 'PHP',
                'question' => 'Влияет ли типизация параметров и свойств на производительность PHP?',
                'answer' => 'Влияет, и обычно в плюс — но не так, как многие думают: «типы строже → код быстрее» не работает напрямую. Что реально происходит: 1) Типы — это runtime-проверка. На каждый вызов типизированной функции и каждое присваивание typed property PHP выполняет дополнительную проверку соответствия типа. Без strict_types ещё добавляется попытка type coercion ("5" → 5). Это микро-overhead в наносекундах. 2) Зато типы дают opcache/JIT много информации для оптимизации. С PHP 8 JIT (Tracing JIT с 8.4) использует типизированные сигнатуры, чтобы генерировать специализированный нативный код без проверок типов внутри горячих циклов — там, где известно «параметр всегда int», JIT выкидывает coercion и работает напрямую с регистрами. 3) Typed properties в PHP 7.4+ хранятся эффективнее: zval уже «знает» тип, нет лишней метаинформации, доступ быстрее, чем к нетипизированному свойству, особенно на массивах объектов. 4) В реальных бенчмарках на типовом веб-приложении (Laravel/Symfony) разница 0–3% — типизация не делает прод заметно быстрее сама по себе. Главный выигрыш — корректность, инструменты статанализа (phpstan, psalm), ловля багов в CI. Делайте typed property и strict_types ради качества, а не ради 1% RPS — это бонус, а не цель.',
                'difficulty' => 4,
                'topic' => 'php.types',
            ],
        ];
    }
}
