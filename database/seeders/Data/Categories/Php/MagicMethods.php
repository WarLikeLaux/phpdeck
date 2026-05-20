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
                'answer' => 'Магические методы вызываются автоматически в специальных ситуациях, имеют префикс __. Основные: __construct/__destruct, __get/__set/__isset/__unset (для несуществующих свойств), __call/__callStatic (для несуществующих методов), __toString (приведение к строке), __invoke (вызов объекта как функции), __clone (после клонирования), __serialize/__unserialize (с PHP 7.4 - современная замена пары __sleep/__wakeup; новые проекты должны использовать только их). Точнее про статус: __sleep/__wakeup сами по себе НЕ помечены как deprecated в спеке - это просто legacy-стиль; формально deprecated в PHP 8.1 был именно интерфейс Serializable, который PHP советует менять на __serialize/__unserialize. Также есть __debugInfo (для var_dump) и __set_state (используется при eval(var_export($x, true))). Пара __serialize/__unserialize имеет смысл задавать ВМЕСТЕ — иначе магия частично не работает. Минус магических методов: непрозрачны, тяжелее анализировать.',
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
                'answer' => '__invoke позволяет вызывать объект как функцию. Простыми словами: добавляешь метод __invoke - и можно писать $obj() вместо $obj->method(). Полезно для callable-объектов: handlers, action-классы, single-action invokables в Laravel, middleware. Объект с __invoke считается callable, его можно передавать туда, где ожидается функция.',
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
                'answer' => 'clone создаёт ПОВЕРХНОСТНУЮ копию объекта - все свойства копируются. НО: вложенные объекты копируются по ссылке-идентификатору (то есть указывают на тот же объект). Для глубокой копии нужно реализовать __clone и в нём вручную клонировать вложенные объекты. __clone вызывается автоматически после копирования свойств.',
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
