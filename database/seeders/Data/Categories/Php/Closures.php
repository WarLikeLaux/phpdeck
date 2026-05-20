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
                'answer' => 'Замыкание - это анонимная функция, которая может "захватывать" переменные из окружения. В PHP в отличие от JS захват ЯВНЫЙ через use. По умолчанию переменные захватываются по значению (копия), для захвата по ссылке - use (&$var). $this автоматически биндится если closure создан в методе. Перепривязать $this можно через ->bindTo($newThis, $scope) или статический Closure::bind($closure, $newThis, $scope). ВАЖНО про Closure::fromCallable($callable): это конвертация callable в объект Closure (строка-имя функции, [$obj, "method"], [Class::class, "staticMethod"], объект с __invoke). $this тут не "обнуляется": если callable передан как [$obj, "method"], результирующий Closure уже привязан к $obj и вызывы используют этот контекст. Чего fromCallable НЕ делает - это произвольный rebind, как bindTo($newThis): для смены контекста объекта нужен именно bindTo/Closure::bind. Closure::fromCallable() остаётся, но в новом коде используйте first-class callable: $fn = strlen(...); $m = $obj->method(...) - тот же результат, но синтаксис короче и поддерживается статанализом.',
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
                'answer' => 'Arrow functions (PHP 7.4+) - это краткий синтаксис анонимных функций через fn() => expr. Главное отличие: автоматически захватывают переменные из родительской области по ЗНАЧЕНИЮ (без use). Тело - одно выражение, которое неявно возвращается. Не могут содержать несколько инструкций. Идеальны для маленьких callback в array_map/filter/reduce.',
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
                'answer' => 'Анонимный класс (PHP 7+) - класс без имени, объявляемый и инстанцируемый одновременно через new class. Полезен для одноразовых объектов: моков в тестах, простых implementations интерфейсов, передачи как параметра. Внутреннее имя - class@anonymous /file.php:LINE$N - привязано к МЕСТУ В ИСХОДНИКЕ: один и тот же new class в цикле / в часто вызываемой функции компилируется ровно ОДИН РАЗ и переиспользуется для всех инстансов (get_class() возвращает то же имя, instanceof работает). Разные синтаксические места = разные анонимные классы. Это значит, что цикл с new class { ... } НЕ создаёт утечку из растущего числа классов - множатся только инстансы. Может реализовывать интерфейсы, наследовать класс, использовать трейты.',
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
                'answer' => 'Синтаксис $fn = strlen(...) или $obj->method(...) создаёт Closure из функции/метода без строкового имени. По сравнению со старым [$obj, "method"] и "strlen" - типобезопасно, поддерживает рефакторинг IDE, и, что важно, ловит ошибки опечаток на этапе компиляции. Удобно для array_map, pipeline и DI-резолверов.',
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
                'answer' => 'Чтобы «затащить» переменную из окружения внутрь функции — по умолчанию анонимка их не видит. function($x) use ($mult) { return $x * $mult; }. use ($v) — копия, use (&$v) — по ссылке. В arrow function (fn) переменные затягиваются автоматически.',
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
                'answer' => 'Псевдо-тип «всё, что можно вызвать». В нём допустимы пять форм: имя функции в виде строки ("strlen"), пара [объект, "имя метода"], пара [имя класса, "имя статического метода"], анонимная функция/Closure (включая arrow fn() =>), объект с магическим __invoke. Используется как тип параметра функций: function apply(callable $fn). С PHP 8.1 рекомендуют новый first-class callable syntax: $f = strlen(...) — типобезопасно и видно IDE.',
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
                'answer' => 'Да. Любая функция в PHP может вернуть замыкание, и его потом вызывают как обычную функцию. Это пригодится для частичного применения (запекаем один аргумент сейчас, остальные передаём позже), фабрик коллбэков и в DSL вроде маршрутизатора Laravel: function makeAdder($a) { return fn($b) => $a + $b; }; $add5 = makeAdder(5); echo $add5(10); // 15.',
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
