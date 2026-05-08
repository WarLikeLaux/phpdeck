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
                'difficulty' => 3,
                'topic' => 'php.closures',
            ],
        ];
    }
}
