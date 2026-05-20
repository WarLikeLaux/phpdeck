<?php

namespace Database\Seeders\Data\Categories\Php;

class BasicSyntax
{
    public static function all(): array
    {
        return [
            [
                'category' => 'PHP',
                'question' => 'Что такое переменная в PHP и как её объявить?',
                'answer' => '**Переменная** — именованная ячейка в памяти, в которой хранится значение.

**Правила PHP:**
- имя всегда начинается со знака **`$`**: `$name`, `$age`
- тип **не указывается** — определяется по значению (**динамическая типизация**)
- объявление = присваивание: `$name = "Иван";`

**Следствие динамической типизации:** один и тот же `$x` может хранить сначала `int`, потом `string`, потом массив — в течение одного скрипта.',
                'code_example' => '<?php
$name = "Иван";      // строка
$age = 30;            // целое число
$price = 19.99;       // float
$isAdmin = true;      // bool
$items = [];          // массив
$user = null;         // null

echo $name; // Иван',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Какие основные типы данных есть в PHP?',
                'answer' => '**Скалярные** (одиночное значение):
- **`int`** — целое: `42`
- **`float`** — дробное: `3.14`
- **`string`** — строка: `"hello"`
- **`bool`** — `true` / `false`

**Составные** (структуры):
- **`array`** — упорядоченная hash-map
- **`object`** — экземпляр класса
- **`callable`** — функция/метод, который можно вызвать

**Специальные:**
- **`null`** — «значения нет»
- **`resource`** — handle (файл, соединение с БД)

**Типы только для возврата:**
- **`void`** — функция ничего не возвращает
- **`mixed`** (PHP 8.0) — любой тип
- **`never`** (PHP 8.1) — функция не возвращает: `throw` / `exit` / бесконечный цикл
- **`static`** (PHP 8.0) — экземпляр того же класса',
                'code_example' => '<?php
var_dump(42);           // int(42)
var_dump(3.14);         // float(3.14)
var_dump("hello");      // string(5) "hello"
var_dump(true);         // bool(true)
var_dump([1, 2, 3]);    // array(3)
var_dump(null);         // NULL
var_dump(new stdClass); // object(stdClass)',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работает foreach в PHP?',
                'answer' => '**`foreach`** — цикл для перебора **массивов** и **объектов**, реализующих `Iterator` / `IteratorAggregate`.

**Две формы:**
- только значения: `foreach ($arr as $value)`
- ключ и значение: `foreach ($arr as $key => $value)`

**Под капотом:** PHP работает со **снимком** массива (copy-on-write — фактическая копия создаётся только при модификации). Изменения исходного массива внутри обычного `foreach` не влияют на ход итерации.

**Изменять элементы по месту** — `foreach ($arr as &$value)` (по ссылке).

**Внимание:** после `foreach` с `&` обязательно делай **`unset($value)`** — иначе переменная остаётся ссылкой на последний элемент массива и следующий цикл его молча перезапишет.',
                'code_example' => '<?php
$users = ["Иван", "Аня", "Петя"];

// Только значения
foreach ($users as $user) {
    echo $user . "\\n";
}

// Ключ и значение
foreach ($users as $i => $user) {
    echo "$i: $user\\n";
}

// По ссылке (для изменения)
foreach ($users as &$user) {
    $user = strtoupper($user);
}
unset($user); // ВАЖНО! иначе будут баги

print_r($users); // ["ИВАН", "АНЯ", "ПЕТЯ"]',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Какие циклы есть в PHP кроме foreach?',
                'answer' => 'Три классических цикла:

- **`for`** — счётчик с инициализацией, условием и шагом: `for ($i = 0; $i < 10; $i++)`. Удобно, когда заранее знаешь число итераций.
- **`while`** — выполняется, **пока условие истинно**. Тело может не выполниться ни разу.
- **`do { ... } while`** — сначала тело, потом проверка. **Гарантирует минимум один проход.**

**Управление потоком:**
- **`break`** — выйти из цикла
- **`continue`** — пропустить тело и перейти к следующей итерации
- оба принимают число уровней: **`break 2`** выходит сразу из двух вложенных циклов.',
                'code_example' => '<?php
// for
for ($i = 0; $i < 5; $i++) {
    echo $i;
}

// while
$i = 0;
while ($i < 5) {
    echo $i++;
}

// do-while
$i = 0;
do {
    echo $i++;
} while ($i < 5);

// break/continue с уровнем
for ($i = 0; $i < 3; $i++) {
    for ($j = 0; $j < 3; $j++) {
        if ($j == 2) break 2;
        echo "$i,$j ";
    }
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое константы в PHP и как их объявлять?',
                'answer' => '**Константа** — значение, которое нельзя изменить после объявления.

**Два способа объявления:**
- **`const NAME = value`** — на этапе **компиляции**. Работает на верхнем уровне И **внутри классов**. Значение — литерал или constant expression.
- **`define("NAME", $value)`** — в **рантайме**. Только глобальные, в классах работать не будет. Зато можно вычислить значение из переменной.

**Соглашения:**
- имена — в `UPPER_SNAKE_CASE`
- доступ — **без `$`**: `echo MAX_USERS;`
- константа класса — через `::`: `Config::VERSION`

**`final const`** (PHP 8.1+) — запрещает переопределение в наследниках.',
                'code_example' => '<?php
// Глобальные константы
define("MAX_USERS", 100);
const APP_NAME = "MyApp";

echo MAX_USERS;  // 100
echo APP_NAME;   // MyApp

// Константы класса
class Config {
    const VERSION = "1.0";
    final const SECRET = "abc"; // PHP 8.1+
}

echo Config::VERSION;

// Константы-выражения (PHP 5.6+)
const HOUR = 60 * 60;',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что произойдёт, если в foreach захватить элемент по ссылке и забыть unset()?',
                'answer' => 'Классическая ловушка PHP. После foreach($arr as &$item) {} переменная $item остаётся ссылкой на ПОСЛЕДНИЙ элемент массива - её "связь" с этим элементом сохраняется и за пределами цикла. Если потом запустить ВТОРОЙ foreach по тому же массиву уже без &, то на каждой итерации значение текущего элемента будет присваиваться в $item - а $item это всё ещё ссылка на последний слот массива! В результате последний элемент будет переписан значениями всех остальных по очереди, и в нём окажется предпоследнее значение. Это один из самых неочевидных багов в PHP - код выглядит корректно, тесты на одном проходе работают, ломается только при повторной итерации. Решение - всегда делать unset($item) сразу после foreach с & (это best practice; PHPStan/Psalm/IDE подсвечивают забытый unset, но в самом PSR-12 такого требования нет - PSR-12 регулирует форматирование кода, а не семантику ссылок). Альтернатива - не использовать reference foreach вообще, а модифицировать массив через ключи: foreach($arr as $k => $v) $arr[$k] = transform($v).',
                'code_example' => '<?php
$nums = [1, 2, 3, 4];

// Первый проход с & - удваиваем
foreach ($nums as &$n) {
    $n *= 2;
}
// $nums = [2, 4, 6, 8] - OK
// ⚠️ НО: $n всё ещё ссылка на $nums[3]

// Второй "невинный" проход - ломаем массив
foreach ($nums as $n) {
    // $n получает значения 2, 4, 6, 8 - но $n это ссылка на $nums[3]!
}
// $nums = [2, 4, 6, 6] - последний элемент переписан

// ✅ Правильно: всегда unset после foreach с &
foreach ($nums as &$n) {
    $n *= 2;
}
unset($n); // разорвать ссылку

foreach ($nums as $n) { /* теперь безопасно */ }

// ✅ Альтернатива - модификация по ключу, без ссылок
foreach ($nums as $k => $n) {
    $nums[$k] = $n * 2;
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое разделители в числовых литералах PHP?',
                'answer' => 'С PHP 7.4 в числовых литералах разрешён символ подчёркивания между цифрами для повышения читаемости, например 1_000_000 или 0xFF_EC. На рантайм это не влияет: парсер игнорирует подчёркивания, и значение хранится как обычное число.',
                'code_example' => '<?php
$population = 8_000_000_000;      // читается как 8 миллиардов
$mask       = 0xFF_FF_FF;         // hex по байтам
$timeoutNs  = 1_500_000;          // 1.5 ms в наносекундах

var_dump($population);            // int(8000000000)
var_dump($population === 8000000000); // true — разделители не меняют значение',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Какие требования предъявляются к имени переменной в PHP?',
                'answer' => '**Правила синтаксиса:**
- начинается со знака **`$`**
- после `$` — **буква** (`a-z`, `A-Z`) или **подчёркивание** `_`
- дальше — буквы, цифры, подчёркивания
- **с цифры начинаться нельзя**: `$1user` — ошибка
- пробелы и спецсимволы внутри запрещены

**Регистрозависимость:** `$user` и `$User` — **разные** переменные.

**Зарезервированные имена:** `$this` использовать как обычную переменную нельзя — это указатель на текущий объект внутри методов.

**Стиль (PSR-12):** `$camelCase` для переменных, `$snake_case` встречается, но реже.',
                'code_example' => '<?php
$user      = "ok";    // ✅
$_count    = 5;       // ✅ можно с _
$userAge2  = 30;      // ✅ цифры внутри
// $1name  = "fail";  // ❌ нельзя с цифры
// $user-name = "fail"; // ❌ дефис запрещён

$user = "Иван";
$User = "Анна";
echo $user; // "Иван" — это разные переменные',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое «переменные переменные» ($$x) в PHP и зачем они нужны?',
                'answer' => 'Конструкция $$name означает, что в качестве имени второй переменной используется значение первой: если $name = "user", то $$name обращается к переменной $user. Это динамическое имя, разрешаемое в рантайме. На практике почти всегда это антипаттерн: код становится непрозрачным для статических анализаторов, IDE не видит таких переменных, и ошибки в имени всплывают только в рантайме. Вместо переменных переменных правильнее использовать ассоциативный массив с явным ключом.',
                'difficulty' => 3,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Какие магические константы есть в PHP и от чего зависят их значения?',
                'answer' => 'Магические константы — это псевдо-константы, значение которых вычисляется парсером в зависимости от того, где они написаны: __LINE__ возвращает номер строки, __FILE__ — полный путь к файлу, __DIR__ — каталог файла, __FUNCTION__ — имя текущей функции, __CLASS__ — имя класса, __METHOD__ — Class::method, __TRAIT__ — имя трейта, __NAMESPACE__ — текущее пространство имён. Особняком стоит ClassName::class — это constant expression, который раскрывается в полное имя класса со всеми namespace в момент компиляции и работает даже без autoload.',
                'code_example' => '<?php
namespace App\\Services;

class UserService {
    public function find(): void {
        echo __LINE__;         // номер строки
        echo __FILE__;         // /app/Services/UserService.php
        echo __DIR__;          // /app/Services
        echo __FUNCTION__;     // "find"
        echo __CLASS__;        // "App\\Services\\UserService"
        echo __METHOD__;       // "App\\Services\\UserService::find"
        echo __NAMESPACE__;    // "App\\Services"
    }
}

echo UserService::class;       // "App\\Services\\UserService" — без autoload',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое передача по ссылке и по значению в PHP?',
                'answer' => 'По умолчанию переменные передаются по ЗНАЧЕНИЮ - функция получает копию. Чтобы изменения отражались на оригинале - используй & в сигнатуре (передача по ссылке). Объекты особый случай: переменная содержит ИДЕНТИФИКАТОР объекта, копируется он, но указывает на тот же объект. Поэтому изменения свойств видны вне функции, но переприсвоение - нет.',
                'code_example' => '<?php
function byValue($x) { $x = 100; }
function byRef(&$x) { $x = 100; }

$a = 5;
byValue($a);
echo $a; // 5

$b = 5;
byRef($b);
echo $b; // 100

// Объекты
function modify($obj) { $obj->name = "New"; }
function reassign($obj) { $obj = new stdClass(); }

$user = new stdClass();
$user->name = "Иван";
modify($user);
echo $user->name; // "New" - свойство изменилось

reassign($user);
echo $user->name; // "New" - переприсвоение НЕ работает',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работают namespaces в PHP?',
                'answer' => 'Namespace - механизм группировки классов, функций, констант для избежания конфликтов имён. Объявляется namespace App\\Models; в начале файла. Используется через use App\\Models\\User;. На практике по PSR-4 принято "один файл - один namespace и один класс" (нужно для автозагрузки), но синтаксически PHP допускает несколько namespace в одном файле через блочный синтаксис namespace Foo { ... } namespace Bar { ... } - официальная документация явно называет это strongly discouraged. Полное имя начинается с \\ (FQCN). Поддерживает алиасы (use X as Y), групповые импорты (PHP 7+).',
                'code_example' => '<?php
// src/Models/User.php
namespace App\\Models;

class User {}

// src/Services/UserService.php
namespace App\\Services;

use App\\Models\\User;
use App\\Models\\Post as PostModel;

// Групповой импорт (PHP 7+)
use App\\Models\\{User as U, Post, Comment};

class UserService {
    public function find(): U {
        return new U();
    }
}

// FQCN
$cls = \\App\\Models\\User::class;',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое spread-оператор в PHP?',
                'answer' => 'Spread-оператор ... распаковывает массив в аргументы функции (PHP 5.6+) или в другой массив (PHP 7.4+). С PHP 8.1 поддерживает строковые ключи. В сигнатуре функции ...$args собирает все аргументы в массив (variadic). Альтернатива call_user_func_array.',
                'code_example' => '<?php
// Variadic - собирает аргументы
function sum(int ...$nums): int {
    return array_sum($nums);
}
echo sum(1, 2, 3, 4); // 10

// Распаковка в вызов
$args = [1, 2, 3];
echo sum(...$args); // 6

// Распаковка в массив (PHP 7.4)
$first = [1, 2, 3];
$second = [...$first, 4, 5]; // [1,2,3,4,5]

// Со строковыми ключами (PHP 8.1)
$a = ["name" => "Иван"];
$b = ["age" => 30];
$user = [...$a, ...$b];

// Spread в named arguments
$params = ["name" => "Иван", "age" => 30];
createUser(...$params);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое строгая типизация (strict_types) в PHP?',
                'answer' => 'declare(strict_types=1) в начале файла включает строгую типизацию. ВАЖНО про точное место действия (часто путают): для ПАРАМЕТРОВ функции strict_types управляется файлом, в котором стоит ВЫЗОВ - то есть передал "5" в int-параметр в strict-файле получишь TypeError, даже если функция объявлена в файле без strict_types. А вот для ВОЗВРАЩАЕМОГО типа strict_types управляется файлом, где функция ОБЪЯВЛЕНА: если функция в strict-файле объявила `: int` и пытается вернуть "5", получит TypeError независимо от настроек вызывающего. Это две разные точки контроля - параметры читаются от caller, return - от callee. Без strict_types PHP в обоих случаях пытается привести типы (coercive mode): "5" в int-параметре станет 5, "5" из `: int` тоже станет 5. Лучшая практика - всегда включать strict_types в начале каждого файла, чтобы не зависеть от настройки вызывающего.',
                'code_example' => '<?php
declare(strict_types=1);

function add(int $a, int $b): int {
    return $a + $b;
}

add(5, 10);    // 15 - OK
// add("5", 10);  // TypeError со strict_types - параметры контролируются caller-ом

// Файл objects/Foo.php (с strict_types=1)
// function maybeId(): int { return "42"; }
// → TypeError на return - return контролируется callee
// (даже если вызвать из файла без strict_types)

// Без strict_types это сработало бы:
// "5" -> 5, 5.5 -> 5',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое суперглобальные переменные в PHP?',
                'answer' => '**Суперглобалы** — встроенные массивы, доступные **везде** (включая функции) без `global`.

**Запрос:**
- **`$_GET`** — параметры URL (`?q=php&page=2`)
- **`$_POST`** — тело POST-формы (`Content-Type: application/x-www-form-urlencoded`)
- **`$_REQUEST`** — объединение GET и POST (по `request_order` в `php.ini`)
- **`$_FILES`** — загруженные через форму файлы
- **`$_COOKIE`** — cookies из заголовка `Cookie`

**Сервер и окружение:**
- **`$_SERVER`** — данные сервера и заголовки (`REQUEST_METHOD`, `REMOTE_ADDR`)
- **`$_ENV`** — переменные окружения
- **`$_SESSION`** — данные сессии (после `session_start()`)
- **`$GLOBALS`** — все глобальные переменные

**На практике:** в Laravel сырые суперглобалы трогать не нужно — есть `$request->input()`, `$request->file()`, `session()`.',
                'code_example' => '<?php
// URL: /search?q=php&page=2
$query = $_GET["q"] ?? "";     // "php"
$page = (int) ($_GET["page"] ?? 1);

// POST форма
$email = $_POST["email"] ?? "";

// Заголовки и сервер
$method = $_SERVER["REQUEST_METHOD"];
$ip = $_SERVER["REMOTE_ADDR"];

// Загруженный файл
if ($_FILES["avatar"]["error"] === UPLOAD_ERR_OK) {
    move_uploaded_file(
        $_FILES["avatar"]["tmp_name"],
        "uploads/" . $_FILES["avatar"]["name"]
    );
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работают типы never и void и в чём практическая разница?',
                'answer' => 'void - функция не возвращает ничего полезного, но завершается нормально. never (PHP 8.1) - функция никогда не возвращает: либо бросает исключение, либо вызывает exit/die/бесконечный цикл. Тип never используется анализаторами для exhaustiveness checking: компилятор знает, что код после вызова never-функции недостижим. Это чище, чем void для guard-функций вроде throwIfInvalid() и помогает type narrowing после ранних возвратов.',
                'difficulty' => 4,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Какие скалярные типы есть в PHP?',
                'answer' => '**Скаляр** — простой тип, хранящий одно значение. Их в PHP четыре:

1. **`int`** — целое число: `42`, `-7`
2. **`float`** — дробное: `3.14`
3. **`string`** — строка: `"hello"`
4. **`bool`** — логическое: `true` / `false`

Всё остальное скаляром не является: **массивы**, **объекты**, **`null`**, **ресурсы**.

**Проверить тип:** `is_int`, `is_float`, `is_string`, `is_bool` или универсально — `get_debug_type($x)`.',
                'code_example' => '<?php
$age = 30;       // int
$price = 19.99;  // float
$name = "Иван";  // string
$isAdmin = true; // bool

echo get_debug_type($age);   // "int"
echo get_debug_type($price); // "float"',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое null в PHP?',
                'answer' => '**`null`** — специальное значение «нет значения» (отсутствие данных). Это **отдельный тип**, не строка `"null"`, не `0` и не пустая строка.

**Когда переменная становится null:**
- явное присвоение: `$x = null`
- после `unset($x)`
- если функция не вернула значение через `return`

**Как проверять:**
- `is_null($x)` или строго `$x === null` (предпочтительнее)

**Nullable-тип:** `function find(int $id): ?User` — вернёт `User` или `null`.

**Значение по умолчанию:** оператор `??` — `$name = $input ?? "Гость"`.',
                'code_example' => '<?php
$x = null;
var_dump($x);             // NULL
var_dump(is_null($x));    // true
var_dump($x === null);    // true (строгая проверка — предпочтительнее)

function find(int $id): ?User {
    return $id > 0 ? new User($id) : null;
}

// Значение по умолчанию через ??
$name = $input ?? "Гость";',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Какие значения в PHP считаются «ложными» (falsy)?',
                'answer' => '**Falsy** — значения, которые приводятся к `false` в булевом контексте (`if`, `&&`, `||`, тернарник):

- **`false`**
- **`0`** (int) и **`0.0`** (float)
- **`""`** — пустая строка
- **`"0"`** — строка с одним нулём ⚠️
- **`[]`** — пустой массив
- **`null`**
- **undefined-переменная** (только для `empty()`)

Всё остальное — **truthy**, включая `"false"`, `"0.0"`, `[0]` и любой объект.

**Главный подвох — `"0"`.** В большинстве языков любая непустая строка truthy. В PHP — нет. Поэтому `if ($input)` пропустит вводимое пользователем `"0"`. Если возможен ноль — проверяй явно: `$input === ""`.',
                'code_example' => '<?php
var_dump((bool) "");      // false
var_dump((bool) "0");     // false ⚠️
var_dump((bool) "0.0");   // true (не "0"!)
var_dump((bool) "false"); // true
var_dump((bool) []);      // false
var_dump((bool) [0]);     // true',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем отличается == от === в PHP?',
                'answer' => '- **`==`** — сравнение **значений с приведением типов**: `0 == "0"` → `true`, `true == 1` → `true`.
- **`===`** — сравнение **значений И типов строго**: `0 === "0"` → `false` (слева `int`, справа `string`).

**Правило:** всегда используй `===`, кроме случаев, когда осознанно нужно приведение.

**Классическая ловушка** — проверка результата `strpos`: функция возвращает либо `int`-позицию, либо `false`. Если использовать `==`, то `0` (найдено в самом начале) будет равно `false` (не найдено) — баг. Поэтому `=== false`.',
                'code_example' => '<?php
var_dump(0 == "0");    // true  — приведение
var_dump(0 === "0");   // false — разные типы (int vs string)
var_dump(1 == true);   // true
var_dump(1 === true);  // false
var_dump(null == false); // true
var_dump(null === false);// false

// Классическая ловушка
$pos = strpos("hello", "h");  // 0 — нашли в начале
if ($pos == false) echo "не найдено"; // СРАБОТАЕТ! 0 == false
if ($pos === false) echo "не найдено"; // НЕ сработает — правильно',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем отличаются одинарные и двойные кавычки в PHP?',
                'answer' => '**Двойные `"..."`** — интерполяция переменных (`$name` подставится значением) и escape-последовательности (`\n`, `\t`, `\r`).

**Одинарные `\'...\'`** — всё буквально: `\'Hi, $name\'` — это пять символов после `$`, переменная **не** подставится. Из escape работают только `\\\\` (бэкслеш) и `\\\'` (одинарная кавычка).

**Подсказки:**
- Свойства и элементы массива в двойных кавычках — через **фигурные скобки**: `"Hi, {$user->name}"`, `"{$arr[\'key\']}"`.
- По скорости разница пренебрежима — выбирай по смыслу: нужна подстановка — двойные, нужен литерал — одинарные.',
                'code_example' => '<?php
$name = "Иван";
echo "Привет, $name\n";   // Привет, Иван (с переводом строки)
echo \'Привет, $name\n\'; // Привет, $name\n (буквально)

// Свойства и массивы — фигурные скобки
$user = ["name" => "Аня"];
echo "User: {$user[\'name\']}";

// Конкатенация — если переменная в одинарных кавычках
echo \'Hi, \' . $name;',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как объединять строки в PHP?',
                'answer' => 'Конкатенация — через оператор **точка** (`.`):

- `$full = $first . " " . $last;`
- Накопление в существующую переменную — **`.=`**: `$msg .= "новая часть";`

**Внимание:** `+` в PHP работает только для чисел — конкатенация плюсом (как в JS) **не сработает**.

**Альтернативы:**
- интерполяция в двойных кавычках: `"Hello, $name"`
- `sprintf("%s, %d", $name, $age)` — форматирование
- `implode(", ", $items)` — массив строк в одну',
                'code_example' => '<?php
$first = "Иван";
$last = "Иванов";

$full = $first . " " . $last;   // "Иван Иванов"
$full = "$first $last";          // то же через интерполяцию

$msg = "Привет";
$msg .= ", " . $first;           // "Привет, Иван"

// implode для массива
echo implode(", ", ["a", "b", "c"]); // "a, b, c"',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как создать массив в PHP?',
                'answer' => '**Короткий синтаксис `[]`** (предпочтительно) или старый `array()`.

- Индексный: `$arr = [1, 2, 3]`
- Ассоциативный (с явными ключами): `["name" => "Иван", "age" => 30]`
- Пустой: `$arr = []`
- Добавить элемент в конец: `$arr[] = $value`

В PHP один тип `array` — под капотом это **упорядоченная hash-map**. Ключами могут быть только `int` или `string`.',
                'code_example' => '<?php
$indexed = [1, 2, 3];
$assoc = ["name" => "Иван", "age" => 30];
$empty = [];

$empty[] = "first";    // [0 => "first"]
$assoc["email"] = "i@i.ru";

print_r($assoc);',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'В чём разница между индексным и ассоциативным массивом в PHP?',
                'answer' => '- **Индексный** — ключи назначаются автоматически целыми числами с 0: `[10, 20, 30]` → ключи `0, 1, 2`.
- **Ассоциативный** — ключи задаёшь сам, строками или числами: `["name" => "Иван", "age" => 30]`.

На уровне типа в PHP это **ОДИН тип `array`** (упорядоченная hashmap), поэтому оба стиля можно смешивать в одном массиве. Перебор — через `foreach`.',
                'code_example' => '<?php
$indexed = ["a", "b", "c"];
echo $indexed[0]; // "a"

$assoc = ["name" => "Иван", "age" => 30];
echo $assoc["name"]; // "Иван"

// Смешанный (валидно, но не рекомендуется)
$mixed = [0 => "first", "key" => "value", 1 => "second"];',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что делает оператор % (остаток от деления) в PHP и где его применяют?',
                'answer' => '**`%`** возвращает **остаток от деления**: `7 % 3` даёт `1`, `10 % 2` даёт `0`.

**Где применяют:**
- проверка чётности: `$n % 2 === 0`
- разбивка на группы по N
- пагинация, FizzBuzz
- «каждый k-й» элемент

**Нюансы:**
- знак результата совпадает со **знаком левого операнда**: `-7 % 3` даёт `-1`
- для дробных чисел — `fmod($a, $b)`',
                'code_example' => '<?php
for ($i = 1; $i <= 10; $i++) {
    if ($i % 2 === 0) {
        echo "$i — чётное\n";
    }
}
echo fmod(5.5, 2); // 1.5',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое тернарный оператор и короткий тернарник ?:?',
                'answer' => '**Тернарник** `$cond ? $a : $b` — короткий `if/else` как **выражение** (возвращает значение).

**Короткий `?:`** (Elvis) — опускает среднюю часть: `$name ?: "Гость"` равно `$name ? $name : "Гость"`. Левый операнд возвращается, если он **truthy**, иначе — правый.

**Не путать с `??`:**
- **`?:`** проверяет **truthy** (`0`, `""`, `null` — все falsy → возьмёт правый)
- **`??`** проверяет именно на **`null`** или **отсутствие** (`0` и `""` оставит как есть)

Тернарники **нельзя вкладывать без скобок** (с PHP 8 это ошибка): `$a ? $b : $c ? $d : $e` → пиши `($a ? $b : ($c ? $d : $e))`.',
                'code_example' => '<?php
// Полный тернарник
$status = $age >= 18 ? "взрослый" : "ребёнок";

// Короткий ?:
$name = $input ?: "Гость";   // если $input truthy — оставить, иначе "Гость"

// Разница ?: и ??
$x = 0;
echo $x ?: "default";   // "default" (0 — falsy)
echo $x ?? "default";   // 0 (не null)',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое оператор ?? (null coalescing)?',
                'answer' => '**`$a ?? $b`** возвращает `$a`, **если он установлен и не равен `null`**, иначе `$b`.

**Ключевая фишка:** работает по правилам **`isset`**, поэтому **не выбрасывает Warning**, если переменная или ключ массива не существует.

**Цепочки:** `$a ?? $b ?? $c ?? "default"` — берёт первый «существующий и не null».

**Присваивание по умолчанию** — `??=` (PHP 7.4+): `$config["timeout"] ??= 30` присвоит `30`, только если ключа нет или там `null`.

**Не путать с `?:`:** `?:` срабатывает на любом falsy (`0`, `""`), `??` — только на `null` или отсутствии.',
                'code_example' => '<?php
$name = $_GET["name"] ?? "Гость";   // нет ключа? — "Гость"

$user = ["email" => null];
$email = $user["email"] ?? "no@mail";  // null → "no@mail"

// Цепочка
$lang = $_GET["lang"] ?? $_COOKIE["lang"] ?? "ru";

// ??= — присвоить, только если пусто
$config["timeout"] ??= 30;',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое nullsafe-оператор ?->?',
                'answer' => '**`?->`** (PHP 8.0+) — безопасный доступ к свойству или методу через **возможный `null`**.

**Как работает:** если слева от `?->` стоит `null`, **вся цепочка** дальше **не выполняется** и выражение возвращает `null` — без `TypeError` («Attempt to read property … on null»).

**Без `?->` пришлось бы писать вложенные `if` или громоздкие тернарники.**

**Ограничения:**
- работает только на **чтение** — присвоить `$user?->name = "X"` нельзя
- цепочки можно длиннее: `$user?->profile?->avatar?->url`
- для `null`-безопасного **значения по умолчанию** часто сочетают с `??`: `$bio = $user?->profile?->bio ?? "нет био"`',
                'code_example' => '<?php
class User {
    public ?Profile $profile = null;
}
class Profile {
    public ?string $bio = null;
}

$user = null;
echo $user?->profile?->bio ?? "нет данных"; // "нет данных" — без Error

$user = new User();
echo $user?->profile?->bio ?? "нет био";    // "нет био"

// До PHP 8 пришлось бы так
$bio = isset($user) && isset($user->profile) ? $user->profile->bio : null;',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем if/else отличается от switch?',
                'answer' => '- **`if/else`** — для **произвольных условий**: `$age > 18`, наличие в массиве, любые булевы выражения.
- **`switch`** — для проверки **одной переменной** на несколько конкретных значений.

**Подводные камни `switch`:**
- сравнение через **`==`** (нестрого)
- обязательный **`break`** — без него выполнение «провалится» в следующий case (**fall-through**)

**С PHP 8 — `match`:** современная замена `switch`:
- сравнивает через `===`
- является **выражением** (возвращает значение)
- без `break`',
                'code_example' => '<?php
// switch - нужны break
switch ($status) {
    case 200:
    case 201:
        echo "OK"; break;
    case 404:
        echo "Not found"; break;
    default:
        echo "Other";
}

// match (PHP 8+) - выражение, ===, без break
echo match ($status) {
    200, 201 => "OK",
    404      => "Not found",
    default  => "Other",
};',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое функция и как её объявить в PHP?',
                'answer' => '**Функция** — именованный переиспользуемый блок кода, принимающий параметры и опционально возвращающий значение.

- Объявляется ключевым словом **`function`**.
- Типы параметров и возврата указывать **опционально**, но рекомендуется.
- Вызов — по имени со скобками: `greet("Иван")`.

**Регистрозависимость:** имена функций **регистронезависимы** (`Greet()` == `greet()`), а имена переменных — **регистрозависимы** (`$user` ≠ `$User`).',
                'code_example' => '<?php
function greet(string $name): string {
    return "Hi, $name";
}

echo greet("Иван");   // "Hi, Иван"

// Без возврата (void)
function log(string $msg): void {
    echo $msg . PHP_EOL;
}',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое return и что произойдёт, если его нет?',
                'answer' => '**`return`** возвращает значение из функции и **сразу** завершает её работу — код после `return` не выполнится.

**Что если return нет:**
- функция вернёт **`null`**
- если объявлен возвращаемый тип (`: int`) и `return` отсутствует или вернул не тот тип — **`TypeError`**

**Для функций без возврата** — явный тип **`: void`**. Внутри тогда можно писать `return;` без значения для раннего выхода.',
                'code_example' => '<?php
function sum(int $a, int $b): int {
    return $a + $b;     // вернули int — функция завершилась
    echo "не выполнится";
}

function greet(string $name): void {
    if ($name === "") {
        return;          // ранний выход, без значения
    }
    echo "Hi, $name";
}

function broken(): int {
    // нет return → TypeError при вызове
}',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем echo отличается от print?',
                'answer' => 'Оба выводят строку и оба — **языковые конструкции**, а не функции, поэтому скобки необязательны.

**Различия:**
- **`echo`** — принимает **несколько аргументов** через запятую, ничего не возвращает.
- **`print`** — принимает **только один аргумент**, всегда возвращает `1` (можно использовать в выражениях, например в тернарнике).

На практике почти всегда пишут **`echo`** — чуть быстрее и привычнее.',
                'code_example' => '<?php
echo "Hello", " ", "world";   // несколько аргументов
print "Hello";                // только один
$ok = print "Hi";             // 1 - можно использовать в выражении',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое глобальные и локальные переменные в PHP?',
                'answer' => '**Локальная** — объявлена внутри функции, видна **только** там, исчезает после выхода из функции.

**Глобальная** — объявлена вне функции (на верхнем уровне скрипта).

**Главное отличие PHP** от многих других языков: **внутри функции глобальная переменная автоматически НЕ видна**. Прямой доступ даст `Warning: Undefined variable`.

**Способы достать глобал внутри функции:**
- **`global $var;`** в начале функции — импортирует ссылку
- **`$GLOBALS["var"]`** — суперглобал, работает без `global`

**Best practice:** не используй `global` вообще — передавай зависимости **через параметры**. Так зависимости функции видны в сигнатуре, код легче тестировать.',
                'code_example' => '<?php
$counter = 0;          // глобальная

function bad() {
    echo $counter;     // Warning: Undefined variable
}

function viaGlobal() {
    global $counter;   // импортируем глобальную
    $counter++;
}

function viaSuper() {
    $GLOBALS["counter"]++;  // то же без global
}

// Лучше — явно через параметр
function good(int $c): int {
    return $c + 1;
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Зачем нужен include / require / require_once?',
                'answer' => 'Все четыре конструкции **вставляют содержимое другого PHP-файла** в текущее место — как «копипаст» на этапе выполнения.

**Различия:**
- **`require`** — если файла нет, **фатальная ошибка** (`Error`), скрипт умирает
- **`include`** — если файла нет, **Warning**, выполнение продолжается
- **`require_once` / `include_once`** — то же самое, но **файл подключится максимум один раз** (защита от повторного объявления классов и функций)

**Что использовать:**
- критичные файлы (bootstrap, config) — **`require`**
- шаблоны/виды, без которых можно жить — `include`
- в современном PHP подключение классов делает **автозагрузчик Composer**, ручной `require` нужен фактически только для `vendor/autoload.php`',
                'code_example' => '<?php
require __DIR__ . "/vendor/autoload.php"; // обязательно, иначе fatal
require_once "config.php";                // защита от повторного include
include "header.php";                     // если нет — Warning, идём дальше
include_once "footer.php";

// Подключаемый файл может вернуть значение
$config = require __DIR__ . "/config.php"; // если файл делает return [...]',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое константа и чем она отличается от переменной?',
                'answer' => '**Константа** — значение, которое **нельзя изменить** после объявления. Имя пишется **без знака `$`**, по соглашению — в `UPPER_SNAKE_CASE`.

**Два способа объявить:**
1. **`define("MAX", 100)`** — рантайм, только глобальные.
2. **`const MAX = 100`** — этап компиляции, работает **и внутри классов**.

**Доступ** — просто по имени: `echo MAX;`.

**Зачем нужны:** значения, которые не должны меняться — версия API, лимиты, ключи конфигов, режимы.',
                'code_example' => '<?php
const APP_VERSION = "1.0";
define("MAX_USERS", 100);

echo APP_VERSION;  // "1.0"
echo MAX_USERS;    // 100

// MAX_USERS = 200;  // Error: нельзя переопределить

class Config {
    const DB_NAME = "app";
}
echo Config::DB_NAME;',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое аргумент функции по умолчанию?',
                'answer' => 'Значение, которое параметр получает, если при вызове его **не передали**.

**Синтаксис:** `function greet($name = "Гость") {}` — можно вызвать `greet()` или `greet("Иван")`.

**Правила:**
- параметры с дефолтным значением должны идти **после обязательных** (либо пропускаться через **именованные аргументы** с PHP 8: `greet(hi: "Hi")`)
- значением может быть **скаляр**, **массив**, **константа** или (с PHP 8.1) **`new Класс`**',
                'code_example' => '<?php
function greet(string $name = "Гость", string $hi = "Привет"): string {
    return "$hi, $name!";
}

echo greet();                  // "Привет, Гость!"
echo greet("Иван");            // "Привет, Иван!"
echo greet("Аня", "Здравствуй"); // "Здравствуй, Аня!"

// Именованные аргументы (PHP 8+)
echo greet(hi: "Hi");          // "Hi, Гость!"',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое variadic-функция (переменное число аргументов)?',
                'answer' => '**Variadic-функция** принимает **произвольное число аргументов**.

**Синтаксис:** `...$args` в **последнем** параметре собирает все «лишние» аргументы в **массив**.

**Типизация:** `int ...$nums` ограничивает тип каждого элемента — функция примет только `int`-ы, иначе `TypeError`.

**Вызов:**
- обычный: `sum(1, 2, 3)`
- с **распаковкой** массива: `sum(...$arr)` — оператор `...` развернёт `$arr` в отдельные аргументы

**Зачем:** логгеры (`log("INFO", "msg1", "msg2", ...)`), форматтеры, билдеры, обёртки над `print_r` и подобными — везде, где число аргументов заранее не известно.

**Заменяет** старую `func_get_args()` — она работает, но не видна в сигнатуре функции, IDE её «не понимает».',
                'code_example' => '<?php
function sum(int ...$nums): int {
    return array_sum($nums);
}

echo sum(1, 2, 3);          // 6
echo sum(...[10, 20, 30]);  // 60 — распаковка массива

// Обязательные параметры идут до variadic
function log(string $level, string ...$messages): void {
    echo "[$level] " . implode(", ", $messages);
}
log("INFO", "started", "ok");',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое type hints для параметров функции?',
                'answer' => '**Type hint** — объявление ожидаемого типа параметра и возвращаемого значения. Если тип не подходит — **`TypeError`**.

**Поддерживаемые типы:**
- **скаляры:** `int`, `float`, `string`, `bool`
- **структуры:** `array`, `iterable`, `object`, `callable`
- **классы и интерфейсы:** `User`, `Logger`, `\\App\\Models\\User`
- **`?Type`** — nullable, эквивалент `Type|null`
- **union (PHP 8.0):** `int|string`
- **intersection (PHP 8.1):** `Countable&ArrayAccess`
- **`self` / `static` / `parent`** — для возврата экземпляра
- **только для возврата:** `void`, `never`, `mixed`

**Режимы проверки:**
- **без `declare(strict_types=1)`** (coercive mode) — PHP **приведёт** значение: `"5"` в `int` станет `5`.
- **со `declare(strict_types=1)`** — приведение запрещено: `"5"` в `int` сразу даст `TypeError`.

**Совет:** ставь `declare(strict_types=1)` в начале каждого файла — баги ловятся раньше.',
                'code_example' => '<?php
declare(strict_types=1);

function greet(string $name, ?int $age = null): string {
    return $age ? "$name, $age" : $name;
}

function pickFirst(int|string $x): int|string { return $x; }

// TypeError со strict_types=1
// greet(123, 30);

// Union возврата
function find(int $id): ?User { /* ... */ }',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое приведение типов (type juggling) в PHP?',
                'answer' => '**Type juggling** — автоматическое преобразование значения из одного типа в другой при операциях и сравнениях.

**Примеры:**
- `"5" + 3` → `8` (строка стала `int`)
- `"5.5" + 3` → `8.5` (стала `float`)
- `1 == "1"` → `true` (сравнение с приведением)

**С PHP 8 ужесточили арифметику:**
- **`"abc" + 3`** — теперь **`TypeError`** (раньше тихий 0)
- **`"5abc" + 3`** — `8` + **`Warning`** (leading-numeric string)

**Явное приведение** — предпочтительный способ:
- касты: `(int)`, `(float)`, `(string)`, `(bool)`, `(array)`
- функции: `intval()`, `floatval()`, `strval()`, `boolval()`

**Опасные ловушки:**
- `(bool) "0"` → `false` ⚠️ (непустая строка, но falsy)
- `(int) "abc"` → `0` без ошибки

**С `declare(strict_types=1)`** автоприведения при передаче в функцию **нет** — сразу `TypeError`.',
                'code_example' => '<?php
echo "5" + 3;        // 8     — int + int
echo "5.5" + 3;      // 8.5   — float
echo "5abc" + 3;     // 8     + Warning (leading-numeric)
// echo "abc" + 3;   // TypeError (PHP 8+)

// Явное приведение
$n = (int) "42";     // 42
$s = (string) 42;    // "42"
$b = (bool) "0";     // false ⚠️
$a = (array) "hi";   // ["hi"]',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'В чём разница между префиксным и постфиксным инкрементом ($x++ vs ++$x)?',
                'answer' => 'Оба увеличивают переменную на 1, но **возвращают разное**:

- **`++$x` (префикс)** — сначала инкремент, потом значение (новое).
- **`$x++` (постфикс)** — сначала значение (старое), потом инкремент.

То же для декремента: `--$x` / `$x--`.

**Разница важна в выражениях:**
- `$a = $x++` — `$a` получит **старое** значение
- `$a = ++$x` — `$a` получит **новое** значение

Когда используется отдельной строкой (`$x++;`) — разницы нет.',
                'code_example' => '<?php
$x = 5;
$a = $x++;  // $a = 5, $x = 6 (постфикс — вернул старое)

$x = 5;
$b = ++$x;  // $b = 6, $x = 6 (префикс — вернул новое)

// Самостоятельно — разницы нет
$x++;
++$x;       // оба просто увеличили $x на 1',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое динамические переменные ($$var) в PHP?',
                'answer' => '**`$$var`** — способ обратиться к переменной, **имя которой хранится в другой переменной**.

**Как это работает:** `$name = "user"; $$name = "Иван";` — равносильно `$user = "Иван";`.

**Явная запись** через фигурные скобки — `${$name} = "Иван";` (так читабельнее).

**Почему почти никогда не нужно:**
- **IDE не подсказывает** такие переменные
- статанализаторы (**PHPStan**, **Psalm**) ругаются
- ошибки в имени всплывают только в **рантайме**
- код становится непрозрачным

**Что использовать вместо:** ассоциативный массив с явным ключом: `$data["user"] = "Иван";`. Это явно, типобезопасно и тестируемо.',
                'code_example' => '$varName = "color";
$$varName = "red";   // эквивалентно $color = "red"
echo $color;          // "red"
echo ${$varName};     // "red" (явная запись)',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.basic_syntax',
            ],
        ];
    }
}
