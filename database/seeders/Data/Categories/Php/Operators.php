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
                'answer' => '**Способы:**
- явный каст: **`(int) $str`**, **`(float) $str`**
- функции: **`intval($str)`**, **`floatval($str)`**
- умножение на 1: `$str * 1` (но в PHP 8 даёт `Warning` на «грязных» строках)

**Касты не выдают Warning** — даже `(int) "abc"` тихо вернёт `0`.

**В арифметике (PHP 8.0+):**
- **`"42abc" + 1`** → `43` + **`Warning`** (leading-numeric string)
- **`"abc" + 1`** → **`TypeError`** (полностью нечисловая)
- **`"3.14e2" + 0`** → `314.0` (научная форма понимается)

**Безопасная валидация:**
- **`ctype_digit($str)`** — только цифры? (важно: `"-5"` даст `false`)
- **`filter_var($str, FILTER_VALIDATE_INT)`** — вернёт `int` или `false`
- **`is_numeric($str)`** — `true` для любой валидной численной строки

**Спецтрюк:** `intval($str, $base)` — парсинг с указанной **системой счисления** (`2..36`).',
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
                'code_example' => '<?php
// ❌ Без pipe — читать справа налево
$result = ucfirst(strtolower(trim("  HELLO  ")));

// ✅ PHP 8.5+ pipe — читать слева направо
$result = "  HELLO  "
    |> trim(...)
    |> strtolower(...)
    |> ucfirst(...);
// "Hello"',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.operators',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что делает оператор «космический корабль» <=>?',
                'answer' => '**`<=>`** (PHP 7+) — оператор **трёхстороннего сравнения**:
- **`-1`** — левый меньше правого
- **`0`** — равны
- **`1`** — левый больше правого

**Главный кейс — callback для `usort`:**
```
usort($arr, fn($a, $b) => $a <=> $b);
```

**Почему лучше `$a - $b`:** старый трюк `$a - $b` для целых ломается на больших значениях (переполнение `int`) и не работает для строк/float.

**Что умеет:**
- **числа** — по значению
- **строки** — по словарю (как `strcmp`)
- **массивы** — поэлементно

**Сортировка по нескольким полям** — через `?:` (Elvis):
```
$a["priority"] <=> $b["priority"]
  ?: $a["name"] <=> $b["name"]
```',
                'code_example' => '<?php
echo 1 <=> 2;   // -1
echo 2 <=> 2;   //  0
echo 3 <=> 2;   //  1

// Сортировка массива объектов
$users = [["age" => 30], ["age" => 18], ["age" => 25]];
usort($users, fn($a, $b) => $a["age"] <=> $b["age"]);
// 18, 25, 30

// Сортировка по нескольким полям
usort($items, fn($a, $b) =>
    $a["priority"] <=> $b["priority"]
    ?: $a["name"]  <=> $b["name"]
);',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.operators',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем отличается isset, empty и is_null?',
                'answer' => '- **`isset($x)`** — **`true`**, если переменная **существует И не равна `null`**. **Не выдаёт Warning** на несуществующих переменных.
- **`empty($x)`** — **`true`**, если переменной нет ИЛИ её значение **falsy** (`""`, `0`, `"0"`, `null`, `[]`, `false`). Тоже **без Warning**.
- **`is_null($x)`** / **`$x === null`** — строго проверяет равенство `null`. **Выдаст Warning** «Undefined variable», если переменная не объявлена.

**Правила выбора:**
- «существует ли переменная вообще?» → **только** `isset` / `empty`
- «уверен, что объявлена, проверяю на null?» → `is_null` или `=== null`
- «есть ли в массиве ключ, даже если значение `null`?» → **`array_key_exists`** (isset вернёт `false`!)

**Внимание на `empty`:** строка **`"0"`** считается **`empty`** — частый источник багов с формами.',
                'code_example' => '<?php
$a = null;
$b = "";
$c = 0;
$d = "0";
$e = "hello";

var_dump(isset($a)); // false (null)
var_dump(isset($b)); // true ("")

var_dump(empty($a)); // true
var_dump(empty($b)); // true ("")
var_dump(empty($c)); // true (0)
var_dump(empty($d)); // true ("0" - тоже falsy!)
var_dump(empty($e)); // false

var_dump(is_null($a)); // true

// Массив с null
$arr = ["key" => null];
var_dump(isset($arr["key"]));            // false
var_dump(array_key_exists("key", $arr)); // true',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.operators',
            ],
            [
                'category' => 'PHP',
                'question' => 'Какие арифметические операторы есть в PHP и чем отличаются / и intdiv?',
                'answer' => '**Стандартный набор:**
- **`+`**, **`-`**, **`*`** — сложение, вычитание, умножение
- **`/`** — деление
- **`%`** — остаток от деления
- **`**`** — возведение в степень (PHP 5.6+)

**`/` vs `intdiv`:**
- **`/`** — возвращает **`float`**, если результат не целый: `7 / 2` → `3.5`, `6 / 2` → `int(3)`
- **`intdiv($a, $b)`** — **целочисленное** деление с отбрасыванием дробной части: `intdiv(7, 2)` → `3`

**Деление на ноль:**
- **`$x / 0`** или **`intdiv($x, 0)`** или **`$x % 0`** → **`DivisionByZeroError`** (PHP 8+)
- **`fdiv($x, 0.0)`** — мягкий float-вариант: `INF`, `-INF` или `NAN`, без исключения

**Приоритет:** `**` > `*` `/` `%` > `+` `-`. Когда сомневаешься — ставь скобки.',
                'code_example' => '<?php
echo 7 / 2;        // 3.5  (float)
echo intdiv(7, 2); // 3    (int)
echo 7 % 2;        // 1    (остаток)
echo 2 ** 10;      // 1024 (степень)',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.operators',
            ],
            [
                'category' => 'PHP',
                'question' => 'Какие логические операторы есть в PHP и в чём подвох пары and/or?',
                'answer' => '**Основные:** **`&&`** (И), **`||`** (ИЛИ), **`!`** (НЕ), **`xor`** (исключающее ИЛИ).

**Ленивая оценка (short-circuit):**
- **`$a && $b`** — если `$a` уже `false`, **`$b` не вычисляется**
- **`$a || $b`** — если `$a` уже `true`, **`$b` не вычисляется**

Это позволяет писать `$user && $user->isActive()` — без проверки на null отдельно.

**Текстовые аналоги:** **`and`**, **`or`**, **`xor`** работают идентично, **НО у них ниже приоритет**, чем у **`=`**.

**Ловушка:**
- `$x = $a || $b` → `$x` получит результат **(`$a || $b`)**
- `$x = $a or $b` → работает как **`($x = $a) or $b`** — `$x` получит только `$a`!

**Best practice:** использовать только **`&&`** и **`||`**, никогда не путать с `and`/`or`.',
                'code_example' => '<?php
$x = false || true;   // true
$y = false or true;   // ($y = false) or true → $y === false (ловушка!)
var_dump($x, $y);',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.operators',
            ],
        ];
    }
}
