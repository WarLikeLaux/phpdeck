<?php

namespace Database\Seeders\Data\Categories\Php;

class MagicMethods
{
    public static function all(): array
    {
        return [
            [
                'category' => 'PHP',
                'question' => 'Какие магические методы есть в PHP?',
                'answer' => '**Магические методы** — методы с префиксом **`__`**, которые PHP вызывает **автоматически** в специальных ситуациях.

**Группы:**

| Метод | Когда вызывается | Назначение |
|---|---|---|
| **`__construct`** / **`__destruct`** | `new` / уничтожение объекта | инициализация / очистка |
| **`__get`** / **`__set`** | чтение/запись **несуществующего** свойства | динамические свойства, прокси |
| **`__isset`** / **`__unset`** | `isset($o->x)` / `unset($o->x)` для виртуальных свойств | парные к `__get`/`__set` |
| **`__call`** / **`__callStatic`** | вызов **несуществующего** метода | прокси, facades, fluent-API |
| **`__toString`** | `echo $o`, `(string)$o` | строковое представление |
| **`__invoke`** | `$o(...)` — вызов объекта как функции | invokable-классы, callable |
| **`__clone`** | после `clone $o` | глубокое клонирование |
| **`__serialize`** / **`__unserialize`** | `serialize` / `unserialize` (PHP 7.4+) | контроль сериализации |
| **`__debugInfo`** | `var_dump($o)` | что показать в дампе |
| **`__set_state`** | `var_export` → `eval` | восстановление из экспорта |

**Важные правила:**
- **`__serialize`/`__unserialize`** — современная пара (PHP 7.4+); **задавать вместе**. Старая пара `__sleep`/`__wakeup` — legacy.
- **Интерфейс `Serializable`** **deprecated** с PHP 8.1 → переходить на `__serialize`/`__unserialize`.
- Магия **не видна IDE и статанализу** — компенсируется PHPDoc (`@property`, `@method`).
- Замедляют вызовы относительно прямого доступа (важно в горячем коде).',
                'code_example' => '<?php
class Container {
    private array $data = [];

    public function __get(string $name) {
        return $this->data[$name] ?? null;
    }

    public function __set(string $name, $value): void {
        $this->data[$name] = $value;
    }

    public function __isset(string $name): bool {
        return isset($this->data[$name]);
    }

    public function __call(string $method, array $args) {
        echo "Вызван несуществующий: $method";
    }

    public function __toString(): string {
        return json_encode($this->data);
    }

    public function __invoke(string $key) {
        return $this->data[$key] ?? null;
    }
}

$c = new Container();
$c->name = "Иван";    // __set
echo $c->name;        // __get
echo $c;              // __toString
echo $c("name");      // __invoke',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.magic_methods',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работает __invoke и зачем он нужен?',
                'answer' => '**`__invoke($args)`** позволяет **вызывать объект как функцию**: `$obj(...)` транслируется в `$obj->__invoke(...)`.

**Главное свойство:** объект с `__invoke` автоматически считается **`callable`** — его можно передавать туда, где ожидают функцию (`array_map`, `array_filter`, middleware-pipeline, `Route::get(..., InvokableController::class)`).

**Зачем нужно (на практике):**
- **Single Action Controller** в Laravel — `class CreateUserController { public function __invoke(Request $r) {...} }` + `Route::post(..., CreateUserController::class)`.
- **Action / Use Case** классы — одна публичная операция вместо «класс с одним методом `handle`».
- **Стратегии / политики** — фабрика возвращает callable-объект, потребитель его просто вызывает.
- **Middleware** в PSR-15 / Slim — invokable-классы.
- Замена замыкания, когда нужно **состояние** (через свойства) и **type-hint** на callable-объект.

**Подвох:** `is_callable($obj)` вернёт `true`, но для **`Closure::fromCallable($obj)`** есть нюанс — она оборачивает в анонимную функцию, теряя сам объект. И **PHPStan** требует объявить `__invoke` с явными типами, чтобы вывести сигнатуру вызова.',
                'code_example' => '<?php
class Multiplier {
    public function __construct(private int $factor) {}

    public function __invoke(int $x): int {
        return $x * $this->factor;
    }
}

$double = new Multiplier(2);
echo $double(5);  // 10 - вызвали как функцию

// Передача в функции, ожидающие callable
$nums = [1, 2, 3, 4];
$result = array_map($double, $nums);
// [2, 4, 6, 8]

// Паттерн Single Action в Laravel
class CreateUserAction {
    public function __invoke(array $data): User {
        return User::create($data);
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.magic_methods',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое clone и как работает магический __clone?',
                'answer' => '**`clone $obj`** создаёт **поверхностную (shallow) копию** объекта: все свойства копируются по значению, но **вложенные объекты остаются одной и той же ссылкой**.

**Что важно понять:**
- Скаляры (`int`, `string`, `bool`), массивы → копируются **полностью**.
- Объекты внутри свойств → **тот же объект**, не копия. Изменение через клон отразится на оригинале.

**`__clone()`** — магический метод, вызываемый **сразу после** копирования свойств. **Внутри уже стоит на новом объекте** (`$this` — клон). Задача: **довести копию до глубокой** — вручную клонировать вложенные объекты, ресурсы, массивы объектов.

**Тонкости:**
- **`readonly`-свойства** (PHP 8.1) обычно нельзя изменить — но **внутри `__clone` того класса**, где они объявлены, **PHP 8.3+ разрешает** переприсваивать их (нужно как раз для re-clone вложенных VO).
- **`__clone` не может бросить исключение** в плохом контексте — будет fatal, лучше валидировать заранее.
- **Циклические ссылки** при `__clone` нужно обрабатывать осторожно — иначе бесконечная рекурсия.
- **Глубокий клон по умолчанию** можно получить через `unserialize(serialize($obj))` (медленно, но универсально).',
                'code_example' => '<?php
class Address {
    public string $city = "Moscow";
}

class User {
    public Address $address;
    public function __construct() {
        $this->address = new Address();
    }
}

$a = new User();
$b = clone $a;
$b->address->city = "SPB";
echo $a->address->city; // "SPB"! - тот же объект

// Глубокая копия
class UserDeep {
    public Address $address;
    public function __clone(): void {
        $this->address = clone $this->address;
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.magic_methods',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что делает __destruct()?',
                'answer' => '**Магический метод-деструктор.** PHP вызывает его **автоматически**, когда объект уничтожается:
- **refcount = 0** — ушли все ссылки на объект
- или при **завершении скрипта** — для всё ещё живых объектов

**Зачем нужен:** очистка **внешних ресурсов** — закрыть файл/сокет, отвязать соединение, освободить блокировку.

**Когда лучше НЕ полагаться на `__destruct`:**
- момент вызова **не детерминирован** (особенно при циклических ссылках — ждёт GC)
- порядок уничтожения объектов может удивить
- **бросать исключения в `__destruct` опасно** — фатальная ошибка, если объект уничтожается во время другой ошибки

**На практике** чаще предпочитают **`try/finally`** или явный метод `close()` — гарантированно и сразу.',
                'code_example' => '<?php
class FileLogger {
    private $fh;

    public function __construct(string $path) {
        $this->fh = fopen($path, "a");
    }

    public function log(string $msg): void {
        fwrite($this->fh, $msg . PHP_EOL);
    }

    public function __destruct() {
        if (is_resource($this->fh)) {
            fclose($this->fh);   // закрыли файл при удалении объекта
        }
    }
}

$logger = new FileLogger("/tmp/app.log");
$logger->log("started");
unset($logger); // тут вызовется __destruct',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.magic_methods',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что делает __toString() в PHP?',
                'answer' => '**`__toString()`** позволяет объекту **вести себя как строка**: `echo $obj` или `(string) $obj` автоматически вызовет `$obj->__toString()`.

**Когда удобно:**
- Value Objects — `Money`, `Email`, `Url`, `Uuid`
- логирование объекта — `echo "[INFO] $obj"`
- работа с шаблонами (`{{ $money }}` в Blade)

**Важные детали:**
- метод **должен вернуть `string`** — иначе `Error`
- с PHP 8 объект с `__toString()` **автоматически реализует** интерфейс **`Stringable`** — можно использовать как тип параметра
- **не должен бросать исключения** (исторически было запрещено, с PHP 7.4 разрешено, но всё равно не лучшая идея)',
                'code_example' => 'class Money {
    public function __construct(public int $amount, public string $currency) {}
    public function __toString(): string {
        return "{$this->amount} {$this->currency}";
    }
}
echo new Money(100, "USD"); // "100 USD"',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.magic_methods',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что делают __get и __set в PHP простыми словами?',
                'answer' => '**Магические методы перехвата** обращений к **несуществующим или недоступным** свойствам:

- **`__get($name)`** — вызывается при **чтении**: `echo $obj->unknown` → `__get("unknown")` (вместо `Error`)
- **`__set($name, $value)`** — при **записи**: `$obj->unknown = 5` → `__set("unknown", 5)`
- **`__isset($name)`** — для `isset($obj->x)` и `empty($obj->x)`
- **`__unset($name)`** — для `unset($obj->x)`

**Где применяется:**
- динамические **контейнеры** и **прокси**
- **lazy-loading** — атрибуты Eloquent (`$user->name` берётся из массива `attributes`)
- **DTO** на массивах

**Минус:** непрозрачно — **IDE и PHPStan** не видят таких свойств, помогают `@property` PHPDoc-теги. В новом коде обычно лучше **явные типизированные свойства** или **property hooks** (PHP 8.4+).',
                'code_example' => '<?php
class Bag {
    private array $data = [];

    public function __get(string $name): mixed {
        return $this->data[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void {
        $this->data[$name] = $value;
    }

    public function __isset(string $name): bool {
        return isset($this->data[$name]);
    }
}

$b = new Bag();
$b->city = "Moscow";   // __set
echo $b->city;         // "Moscow" — __get
echo $b->foo ?? "—";   // "—" — __get вернул null',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.magic_methods',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что делает __construct() простыми словами?',
                'answer' => '**`__construct()`** — магический метод-конструктор, который PHP **автоматически** вызывает при создании объекта через `new`.

**Зачем нужен:** инициализировать свойства из переданных аргументов.

**С PHP 8 — constructor property promotion:** модификатор видимости прямо в параметре сразу делает его свойством, без отдельного объявления и присваивания.

**Важный нюанс:** конструктор родителя **НЕ вызывается автоматически** — при наследовании надо явно `parent::__construct(...)`.',
                'code_example' => '<?php
// Классический вид
class UserOld {
    public string $name;
    public function __construct(string $name) {
        $this->name = $name;
    }
}

// PHP 8: property promotion
class User {
    public function __construct(public string $name, public int $age) {}
}

$u = new User("Иван", 30);
echo $u->name; // "Иван"',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.magic_methods',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что делает __call() в PHP простыми словами?',
                'answer' => '**`__call($name, $args)`** вызывается при попытке обратиться к **несуществующему или недоступному** методу объекта:

`$obj->unknown(1, 2)` → `$obj->__call("unknown", [1, 2])`

Без `__call` PHP выбросил бы `Error: Call to undefined method`.

**Парный метод** — **`__callStatic($name, $args)`** для статических вызовов: `ClassName::unknown()` → `__callStatic("unknown", [...])`.

**Где применяется на практике:**
- **Eloquent**: `User::where(...)` и `->orderBy(...)` ловятся через `__call`/`__callStatic` и пересылаются в `QueryBuilder`
- **Laravel Facades** — `Cache::get()` через `__callStatic` идёт в реальный сервис из контейнера
- **прокси** и **декораторы** — перехват + переадресация
- **fluent-API** и DSL

**Минус:** не виден IDE/PHPStan (нужны `@method` PHPDoc-теги или IDE Helper).',
                'code_example' => 'class Proxy {
    public function __call(string $name, array $args): mixed {
        echo "Вызван $name с " . count($args) . " аргументами";
        return null;
    }
}
$p = new Proxy();
$p->doSomething(1, 2, 3); // "Вызван doSomething с 3 аргументами"',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.magic_methods',
            ],
        ];
    }
}
