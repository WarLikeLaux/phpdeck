<?php

namespace Database\Seeders\Data\Categories\Php;

class Operators
{
    public static function all(): array
    {
        return [
            [
                'category' => 'PHP',
                'question' => 'Как привести строку к числу в PHP?',
                'answer' => 'Способов несколько: явное приведение через (int) или (float), функции intval()/floatval(), умножение на 1. Касты сами по себе не выдают warning - даже (int) "abc" вернёт 0. В АРИФМЕТИКЕ с PHP 8 поведение зависит от типа строки (термины из спецификации PHP): leading-numeric / "non-well-formed numeric string" вроде "42abc" + 1 - даёт Warning "A non-numeric value encountered" и приводится к 42 (итог 43). Полностью нечисловая ("abc" + 1) - TypeError "Unsupported operand types: string + int". До PHP 8 оба случая были тихим Warning. intval() принимает второй аргумент - систему счисления (base 2..36).',
                'code_example' => '<?php
$str = "42abc";

$n1 = (int) $str;        // 42 (без warning)
$n2 = intval($str);      // 42
$n3 = $str * 1;          // 42, но Warning в PHP 8+
$n4 = (int) "abc";       // 0 (без warning)
$n5 = (float) "3.14e2";  // float(314)

// intval с системой счисления
intval("0xFF", 16);      // 255
intval("ff", 16);        // 255

// Безопасный парсинг целого
if (ctype_digit($str)) {
    $num = (int) $str;
}

// filter_var для строгой валидации
$num = filter_var("42", FILTER_VALIDATE_INT);   // 42
$num = filter_var("42abc", FILTER_VALIDATE_INT); // false',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.operators',
            ],
            [
                'category' => 'PHP',
                'question' => 'Какие операторы сравнения есть в PHP и в чём подвох?',
                'answer' => 'Основные: == (равно с приведением типов), === (строго равно, без приведения), != или <> (не равно), !== (строго не равно), <, >, <=, >=. Оператор <=> (spaceship, PHP 7+) возвращает -1, 0 или 1 - удобен для сортировки. Подвох == в том, что "0" == false, "abc" == 0 (до PHP 8), null == 0. Всегда предпочитай ===.',
                'code_example' => '<?php
var_dump(0 == "abc");   // false (PHP 8+), true до PHP 8
var_dump(0 == "");      // false (PHP 8+), true до PHP 8
var_dump("1" == "01");  // true (оба - 1)
var_dump("10" == "1e1"); // true
var_dump(100 == "1e2");  // true
var_dump(0 === "0");     // false (разные типы)

// Spaceship для сортировки
usort($arr, fn($a, $b) => $a <=> $b);

// Null coalescing
$name = $user["name"] ?? "Guest";',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.operators',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое оператор ?? и чем отличается от ?: ?',
                'answer' => '?? - null coalescing (PHP 7+), возвращает левый операнд если он не null и определён, иначе правый. ?: - ternary shortcut, возвращает левый если он truthy, иначе правый. Разница: ?? проверяет именно на null/undefined, ?: - на любое falsy значение (0, "", false, []). С PHP 7.4 есть ??= - присваивание с null coalescing.',
                'code_example' => '<?php
$a = null;
$b = "";
$c = 0;

echo $a ?? "default";  // "default"
echo $b ?? "default";  // "" (b не null)
echo $c ?? "default";  // 0  (c не null)

echo $a ?: "default";  // "default"
echo $b ?: "default";  // "default" ("" - falsy)
echo $c ?: "default";  // "default" (0  - falsy)

// ??= оператор
$config["timeout"] ??= 30; // если не задано, поставить 30',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.operators',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое pipe operator |> в PHP 8.5?',
                'answer' => 'Конвейерный оператор |> передаёт результат левого выражения как аргумент правому callable, позволяя выстраивать цепочки преобразований слева направо. Это альтернатива вложенным вызовам вроде strtolower(trim($s)) и хорошо сочетается с first-class callable syntax.',
                'difficulty' => 3,
                'topic' => 'php.operators',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что делает оператор «космический корабль» <=>?',
                'answer' => 'Оператор <=> из PHP 7.0 выполняет трёхстороннее сравнение и возвращает -1, 0 или 1 в зависимости от того, меньше, равен или больше левый операнд правому. Он удобен в callbacks для usort, заменяя громоздкие if/else или вычитание чисел, которое ломается на больших значениях.',
                'difficulty' => 2,
                'topic' => 'php.operators',
            ],
        ];
    }
}
