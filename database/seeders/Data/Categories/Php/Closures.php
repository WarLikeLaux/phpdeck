<?php

namespace Database\Seeders\Data\Categories\Php;

class Closures
{
    public static function all(): array
    {
        return [
            [
                'category' => 'PHP',
                'question' => 'Что такое замыкание (closure) в PHP?',
                'answer' => '**Замыкание** — анонимная функция, которая может **«захватывать» переменные** из окружающей области видимости. Под капотом — объект класса **`Closure`**.

**В PHP захват ЯВНЫЙ** (в отличие от JS, где переменные видны автоматически):
- **`use ($v)`** — захват **по значению** (копия на момент создания closure)
- **`use (&$v)`** — захват **по ссылке** (изменения видны с обеих сторон)
- в **arrow function** **`fn() => ...`** — захват **автоматический** по значению, `use` не нужен

**Про `$this`:**
- closure, созданный **внутри метода**, автоматически биндится к `$this`
- перепривязать → **`Closure::bind($closure, $newThis, $scope)`** или **`$closure->bindTo($newThis, $scope)`**
- **`static function ()`** — закрывает доступ к `$this` навсегда

**`Closure::fromCallable()` vs first-class callable:**
- старый способ: `Closure::fromCallable("strlen")`, `Closure::fromCallable([$obj, "method"])`
- современный (PHP 8.1+): **`strlen(...)`**, **`$obj->method(...)`** — **короче**, видно IDE и статанализу
- если callable передан как `[$obj, "method"]`, результирующий Closure **уже привязан** к `$obj` — для смены контекста нужен `bindTo`/`Closure::bind`',
                'code_example' => '<?php
$multiplier = 3;

// По значению
$fn = function($x) use ($multiplier) {
    return $x * $multiplier;
};
echo $fn(5); // 15

$multiplier = 10;
echo $fn(5); // 15 (захватили старое!)

// По ссылке
$counter = 0;
$inc = function() use (&$counter) {
    $counter++;
};
$inc(); $inc(); $inc();
echo $counter; // 3

// Closure::bind - перепривязка $this
$closure = function() { return $this->name; };
class User { public string $name = "Иван"; }
$bound = Closure::bind($closure, new User(), User::class);
echo $bound(); // Иван',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.closures',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое arrow functions в PHP?',
                'answer' => '**Arrow function (PHP 7.4+)** — краткий синтаксис анонимной функции: **`fn(args) => expr`**.

**Ключевые отличия от обычной `function`:**
- **автоматически захватывают** переменные из родительской области **по значению** — `use` писать **не нужно**
- **тело — одно выражение**, которое **неявно возвращается** (без `return`)
- **нельзя несколько инструкций**, нельзя присваивания внутри тела (только в выражении), нельзя `;`

**Что поддерживают:**
- параметры с типами и значениями по умолчанию
- возвращаемый тип: `fn(int $x): int => $x * 2`
- variadic `...`, named arguments

**Когда брать что:**
- **arrow** — короткий callback для `array_map`, `array_filter`, `usort`, `Collection::map`
- **обычная anonymous function** — нужно несколько строк, захват по ссылке, временные переменные

**Подводный камень:** захват **по значению** — изменения внешней переменной **после** создания arrow внутрь не попадают. Для ссылочного захвата нужна обычная `function () use (&$v)`.',
                'code_example' => '<?php
$factor = 3;

// Старый стиль
$fn1 = function($x) use ($factor) {
    return $x * $factor;
};

// PHP 7.4+
$fn2 = fn($x) => $x * $factor;

echo $fn2(5); // 15

// В array_map
$nums = [1, 2, 3, 4];
$squared = array_map(fn($x) => $x ** 2, $nums);
// [1, 4, 9, 16]

// Несколько параметров
$add = fn(int $a, int $b): int => $a + $b;

// Ограничение: только одно выражение
// $bad = fn($x) => { $y = $x * 2; return $y; }; // нельзя!',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.closures',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое анонимный класс в PHP?',
                'answer' => '**Анонимный класс (PHP 7+)** — класс **без имени**, объявленный и инстанцированный одновременно через **`new class { ... }`**.

**Что умеет:**
- **реализовывать** интерфейсы — `new class implements Logger { ... }`
- **наследоваться** от класса — `new class extends BaseRepo { ... }`
- **использовать** трейты
- принимать **аргументы конструктора** — `new class("arg") { ... }`

**Где удобен:**
- **одноразовый мок** в тесте — без отдельного файла
- быстрая реализация интерфейса для **callback-сценария**
- **локальный helper** внутри метода

**Подкапотная деталь — имя класса:**
- внутреннее имя вида **`class@anonymous /file.php:42$0`** — привязано к **месту в исходнике**
- один и тот же `new class { ... }` в цикле компилируется **ОДИН РАЗ**, переиспользуется для всех инстансов
- `get_class()` возвращает то же имя для всех созданных объектов, `instanceof` работает
- **разные места** в коде = **разные анонимные классы**

**Вывод:** цикл с `new class { ... }` **не утечка классов** — множатся только инстансы.',
                'code_example' => '<?php
interface Logger {
    public function log(string $msg): void;
}

function processData(Logger $logger) {
    $logger->log("processing");
}

// Передаём анонимный класс
processData(new class implements Logger {
    public function log(string $msg): void {
        echo "LOG: $msg\\n";
    }
});

// С аргументами конструктора
$obj = new class("test") {
    public function __construct(public string $name) {}
};

// Наследование + интерфейс
$mock = new class extends BaseRepo implements Storable {
    public function save(): void { /* мок */ }
};',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.closures',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое first-class callable syntax (PHP 8.1) и зачем он нужен?',
                'answer' => '**First-class callable syntax (PHP 8.1+)** — `(...)` после имени функции/метода создаёт **`Closure`-объект** без строковых имён.

**Все формы:**
- **`strlen(...)`** — глобальная функция
- **`$obj->method(...)`** — метод объекта (привязывает `$this` к `$obj`)
- **`User::fromArray(...)`** — статический метод
- **`User::method(...)`** — инстанс-метод без привязки (требует `$this` при вызове через `bind`)
- **`new Foo(...)`** не работает — у `new` свой синтаксис

**Чем лучше старых форм:**

| | Старое | First-class callable |
| --- | --- | --- |
| Синтаксис | `"strlen"`, `[$obj, "method"]` | `strlen(...)`, `$obj->method(...)` |
| **Опечатки** | ловятся в **рантайме** | ловятся **статанализом / IDE** |
| **Рефакторинг** | строки не переименовываются | IDE переименует автоматически |
| **Phpstan/Psalm** | не видит зависимость | **отслеживает** вызовы |

**Где применять:**
- параметры `array_map`, `array_filter`, `usort`
- pipeline / коллекции в Laravel
- DI-резолверы, фабрики

**Аналог `Closure::fromCallable()`:** делает то же, но строкой/массивом — оставлен для совместимости.',
                'code_example' => '<?php
// ❌ Старые формы — строки, IDE не видит, опечатки в рантайме
$fn = "strlen";
$fn = [$obj, "method"];
$fn = Closure::fromCallable("strlen");

// ✅ PHP 8.1+ first-class callable syntax — Closure-объект
$len   = strlen(...);
$save  = $repo->save(...);
$build = User::fromArray(...);   // статический метод
$factor = fn(int $x) => $x * 2;

// В array_map — короче и типобезопасно
$lengths = array_map(strlen(...), ["a", "bb", "ccc"]);
// [1, 2, 3]',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.closures',
            ],
            [
                'category' => 'PHP',
                'question' => 'В чём разница между обычным и static-замыканием? Влияет ли static на производительность?',
                'answer' => 'Обычное замыкание, созданное внутри метода класса, неявно захватывает $this — текущий объект и его scope. Это позволяет внутри closure обращаться к $this->property и приватным членам класса, и предотвращает GC объекта до тех пор, пока жив closure. static-замыкание (static function() use (...) {...} или static fn() => ...) НЕ привязывается к $this и не привязывается к scope создавшего класса — попытка обратиться к $this внутри даст Error. Зачем нужно: 1) Утечки памяти — если closure хранится долго (в очереди, кэше, длинной коллекции), не-static версия удерживает объект-родителя; static освобождает его сразу. 2) Безопасность — если closure уходит в чужой код, static гарантирует, что внутри не утечёт состояние объекта. 3) Семантическая ясность — функция, которой не нужен объект, должна быть static. Производительность: на отдельный вызов разница микроскопическая (десятки наносекунд) — JIT и opcache всё равно оптимизируют. Заметная экономия проявляется не на скорости вызова, а на ПАМЯТИ и работе GC, когда closures массово создаются/хранятся — например, в Laravel-pipeline, обработчиках событий, генераторах. Также для closure, передаваемого в Closure::bind/bindTo, static — единственный способ сказать «не привязывайся ни к чему».',
                'code_example' => '<?php
class Service {
    public function __construct(private Logger $logger) {}

    public function tasks(): array {
        // Утечёт $this в очередь
        $a = function(int $x) { $this->logger->log($x); return $x * 2; };

        // Лучше: явный static и захват только нужного
        $logger = $this->logger;
        $b = static function(int $x) use ($logger) {
            $logger->log($x);
            return $x * 2;
        };

        return [$a, $b];
    }
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.closures',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое анонимная функция простыми словами?',
                'answer' => '**Функция без имени**, которую можно положить в переменную или передать как аргумент. Под капотом — объект класса **`Closure`**.

**Где удобна:**
- короткие callback-и «на лету» — `array_map`, `usort`, фильтры коллекций
- без объявления отдельной именованной функции

**Захват переменных окружения** — через `use ($var)`: `function($x) use ($mult) { ... }`.

**Короткая форма (PHP 7.4+)** — **arrow function**: `fn($x) => $x * 2`. Переменные окружения захватываются автоматически.',
                'code_example' => '<?php
$double = function (int $x): int {
    return $x * 2;
};
echo $double(5); // 10

// Передача как аргумент
$nums = [1, 2, 3];
$result = array_map(fn($x) => $x * 2, $nums); // [2, 4, 6]

// Захват окружения
$factor = 3;
$mul = fn($x) => $x * $factor; // arrow тянет $factor сам',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.closures',
            ],
            [
                'category' => 'PHP',
                'question' => 'Зачем нужен use в анонимной функции?',
                'answer' => 'Чтобы «**затащить**» переменную из внешней области видимости **внутрь** анонимной функции — по умолчанию `function () {}` их **не видит** (в отличие от JS).

**Два режима захвата:**
- **`use ($v)`** — **по значению**: копия на момент создания closure, последующие изменения снаружи не видны
- **`use (&$v)`** — **по ссылке**: изменения внутри видны снаружи и наоборот

**Arrow function (`fn`)** — переменные затягиваются **автоматически по значению**, `use` не пишется.

**Типичный нюанс:** при захвате по значению переменная **«замораживается»** на момент объявления closure — внутри её последующая мутация снаружи никак не влияет.',
                'code_example' => '$mult = 3;
$fn = function($x) use ($mult) { return $x * $mult; };
echo $fn(5); // 15

// arrow function — короче
$fn2 = fn($x) => $x * $mult;',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.closures',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое callable в PHP простыми словами?',
                'answer' => '**Псевдо-тип «всё, что можно вызвать»**. Используется как тип параметра: `function apply(callable $fn) {}`.

**Пять допустимых форм:**
- **строка с именем функции** — `"strlen"`
- **`[$object, "method"]`** — метод объекта
- **`[ClassName::class, "method"]`** или **`"ClassName::method"`** — статический метод
- **анонимная функция / `Closure`** — включая arrow `fn() => ...`
- **объект с магическим `__invoke`** — `$obj` вызывается как `$obj($args)`

**Проверка:** `is_callable($value)` — true для любой из форм.

**Современный способ (PHP 8.1+)** — **first-class callable syntax**: `$f = strlen(...)`, `$m = $obj->method(...)`. Возвращает `Closure`, видно IDE/статанализу, поддерживает рефакторинг.',
                'code_example' => '<?php
function apply(callable $fn, mixed $x): mixed {
    return $fn($x);
}

apply("strlen", "hello");              // 5  — строка-имя
apply(fn($x) => $x * 2, 5);            // 10 — closure
apply(strtoupper(...), "hi");          // "HI" — first-class callable

class Doubler {
    public function __invoke(int $x): int { return $x * 2; }
}
apply(new Doubler(), 5);               // 10 — объект с __invoke

// Метод объекта
apply([$repo, "find"], 1);',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.closures',
            ],
            [
                'category' => 'PHP',
                'question' => 'Можно ли вернуть функцию из функции в PHP и зачем это нужно?',
                'answer' => '**Да.** Функция может вернуть **замыкание** (или любой `callable`), и его потом вызывают как обычную функцию. Это называется **функция высшего порядка**.

**Зачем нужно:**
- **Частичное применение / каррирование** — «запечь» один аргумент сейчас, остальные передать позже:
  `$add5 = makeAdder(5); $add5(10) === 15`
- **Фабрики коллбэков** — `makeValidator($rule)`, `makeFormatter($locale)`
- **Декораторы** — функция-обёртка добавляет логирование/кеш вокруг чужой
- **DSL** — маршрутизатор Laravel: `Route::get(...)` строит цепочки замыканий
- **Стратегия** — выбрать алгоритм и вернуть его как функцию',
                'code_example' => '<?php
function makeMultiplier(int $factor): Closure {
    return fn(int $x) => $x * $factor;
}

$double = makeMultiplier(2);
$triple = makeMultiplier(3);

echo $double(10); // 20
echo $triple(10); // 30',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.closures',
            ],
        ];
    }
}
