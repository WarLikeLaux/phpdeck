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
                'answer' => 'Переменная - это именованная ячейка в памяти, в которой хранится значение. В PHP переменные начинаются со знака доллара $, тип не указывается явно, он определяется по присвоенному значению. Объявление - просто присвоение значения: $name = "Иван". Подробные правила к имени переменной (что допустимо после $) - см. отдельную карточку.',
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
                'answer' => 'В PHP есть скалярные типы: int (целые), float (дробные), string (строки), bool (true/false). Составные типы: array (массив), object (объект), callable (вызываемое). Специальные: null (отсутствие значения), resource (ресурс, например, файловый дескриптор). С PHP 8.0 добавили mixed (любой тип) и static (только return); с PHP 8.1 - never (функция никогда не возвращает значение, всегда throw / exit / бесконечный цикл; только как return type).',
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
                'answer' => 'foreach - цикл для перебора массивов и объектов, реализующих Iterator/IteratorAggregate. Есть две формы: только значения, и ключ-значение. Внутри foreach создаётся копия массива (благодаря copy-on-write это дёшево). Если нужно изменять элементы исходного массива, используют & для передачи по ссылке - но после цикла обязательно делать unset() для переменной.',
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
                'answer' => 'Помимо foreach есть: for - классический цикл с инициализацией, условием и шагом; while - выполняется пока условие истинно; do-while - сначала выполняет тело, потом проверяет условие (минимум один проход). Есть break (выйти из цикла), continue (перейти к следующей итерации), оба принимают число уровней: break 2 выходит из двух циклов сразу.',
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
                'answer' => 'Константа - это значение, которое нельзя изменить после объявления. Объявляется через define() или ключевое слово const. const работает на этапе компиляции, define() - в рантайме. const можно использовать в классах, define нельзя. По соглашению имена констант пишут в UPPER_CASE. Доступ без знака доллара.',
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
                'difficulty' => 3,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Какие требования предъявляются к имени переменной в PHP?',
                'answer' => 'Имя переменной в PHP начинается со знака доллара, после которого идёт буква латинского алфавита или подчёркивание, а дальше — любая комбинация букв, цифр и подчёркиваний. С цифры имя начинаться не может, пробелы и спецсимволы внутри запрещены. Кириллицу формально допускают как байты со старшим битом (диапазон 0x80–0xFF), но на практике её не используют. Имена регистрозависимы, и зарезервированные слова вроде $this разрешены только в специальных контекстах.',
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
                'answer' => 'Суперглобальные переменные - встроенные массивы, доступные везде без global. $_GET - параметры из URL. $_POST - тело POST-запроса. $_REQUEST - по умолчанию объединение GET и POST (request_order = "GP"); попадание COOKIE настраивается через request_order в php.ini. $_SERVER - данные сервера и заголовки. $_FILES - загруженные файлы. $_COOKIE - cookies. $_SESSION - данные сессии. $_ENV - переменные окружения. $GLOBALS - все глобальные переменные.',
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
                'answer' => 'Четыре: int (целое), float (дробное), string (строка), bool (true/false).',
                'difficulty' => 1,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое null в PHP?',
                'answer' => 'Специальное значение, означающее «нет значения». Переменная типа null — это не строка "null" и не 0. Проверяется через is_null($x) или $x === null.',
                'difficulty' => 1,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Какие значения в PHP считаются «ложными» (falsy)?',
                'answer' => 'false, 0, 0.0, "" (пустая строка), "0" (строка-ноль), [] (пустой массив), null. Всё остальное — truthy, включая строку "false" и массив [0].',
                'difficulty' => 2,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем отличается == от === в PHP?',
                'answer' => '== сравнивает значения с приведением типов (0 == "0" → true, 0 == "abc" в PHP 8+ → false). === сравнивает значения И типы строго (0 === "0" → false). Правило: всегда используй ===, кроме случаев, когда осознанно нужно приведение.',
                'difficulty' => 1,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем отличаются одинарные и двойные кавычки в PHP?',
                'answer' => 'В двойных кавычках работает интерполяция переменных и escape-последовательности: "Hi, $name\n". В одинарных всё хранится как есть: \'Hi, $name\\n\' — это буквально семь символов. Двойные чуть медленнее (парсер ищет переменные).',
                'code_example' => '$name = "Иван";
echo "Привет, $name";  // Привет, Иван
echo \'Привет, $name\'; // Привет, $name',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как объединять строки в PHP?',
                'answer' => 'Через оператор точки: $full = $first . " " . $last;. Для накопления — оператор .=: $msg .= "новая часть";. Конкатенация через +-знак не работает (в PHP + только для чисел).',
                'difficulty' => 1,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как создать массив в PHP?',
                'answer' => 'Через короткий синтаксис []: $arr = [1, 2, 3]; или старый array(): $arr = array(1, 2, 3);. Ассоциативный: $u = ["name" => "Иван", "age" => 30];. Пустой массив: $arr = [];.',
                'difficulty' => 1,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'В чём разница между индексным и ассоциативным массивом в PHP?',
                'answer' => 'В индексном ключи — целые числа автоматически (0, 1, 2...): [10, 20, 30]. В ассоциативном ключи задаются явно строками (или числами): ["name" => "Иван"]. На самом деле в PHP это один тип array — внутри это упорядоченная hashmap.',
                'difficulty' => 1,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как пройти по массиву циклом в PHP?',
                'answer' => 'foreach — самый частый способ. Только значения: foreach ($arr as $v) {...}. Ключ и значение: foreach ($arr as $k => $v) {...}. for и while используются реже, в основном когда нужно явное управление счётчиком.',
                'code_example' => '$users = ["Иван", "Петя", "Маша"];
foreach ($users as $i => $name) {
    echo "$i: $name\n";
}',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое тернарный оператор и короткий тернарник ?:?',
                'answer' => 'Тернарник $a ? $b : $c — короткий if/else в виде выражения. $age >= 18 ? "взрослый" : "ребёнок". Короткий ?: (Elvis) опускает среднюю часть: $name ?: "Гость" эквивалент $name ? $name : "Гость".',
                'difficulty' => 2,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое оператор ?? (null coalescing)?',
                'answer' => '$a ?? $b возвращает $a, если он установлен и не null, иначе $b. Удобно для значений по умолчанию: $name = $_GET["name"] ?? "Гость". Не выбрасывает Warning, если $_GET["name"] не существует.',
                'difficulty' => 2,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое nullsafe-оператор ?->?',
                'answer' => 'Безопасный доступ к свойству или методу через возможный null (PHP 8+). $user?->profile?->bio вернёт null, если $user или $user->profile равны null, без Error. Без ?-> пришлось бы писать вложенные if или нагромождать ??.',
                'difficulty' => 2,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем if/else отличается от switch?',
                'answer' => 'if/else — для произвольных условий ($age > 18). switch — для проверки ОДНОЙ переменной на несколько значений. switch сравнивает через == (не строго!), требует break, иначе провал в следующий case. С PHP 8 появился match — то же, но через === и без fallthrough.',
                'difficulty' => 1,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое функция и как её объявить в PHP?',
                'answer' => 'Именованный блок кода с параметрами и возвратом. Объявление: function greet(string $name): string { return "Hi, $name"; }. Вызов: greet("Ivan"). Параметры могут иметь значения по умолчанию: function greet($name = "Гость").',
                'difficulty' => 1,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое return и что произойдёт, если его нет?',
                'answer' => 'return возвращает значение из функции и завершает её. Без явного return функция возвращает null. Если у функции объявлен возвращаемый тип (: int), без return будет ошибка (или TypeError при возврате null).',
                'difficulty' => 1,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем echo отличается от print?',
                'answer' => 'Оба выводят строку. echo может принимать несколько аргументов через запятую и НИЧЕГО не возвращает — это языковая конструкция. print принимает только один и возвращает 1 (может использоваться в выражениях). На практике используют echo.',
                'difficulty' => 1,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое глобальные и локальные переменные в PHP?',
                'answer' => 'Локальные — видны только внутри функции, где объявлены. Глобальные — объявлены вне функции, но внутри функции не видны автоматически. Чтобы достать глобальную внутри функции — global $var; или $GLOBALS["var"]. Хорошая практика — передавать всё через параметры, а не через global.',
                'difficulty' => 2,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Зачем нужен include / require / require_once?',
                'answer' => 'Вставляют содержимое другого PHP-файла. require падает с фатальной ошибкой, если файла нет; include даёт только Warning. *_once — гарантирует, что файл подключится не больше раза. В современном PHP подключение делается автозагрузчиком Composer, ручной require используется только для bootstrap.',
                'difficulty' => 2,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое константа и чем она отличается от переменной?',
                'answer' => 'Константа — значение, которое нельзя изменить после объявления, без знака $. Объявляется через define("MAX", 100) или ключевое слово const MAX = 100;. Доступ просто по имени: echo MAX;. Применяется для неизменяемых значений (версия API, лимиты, ключи).',
                'difficulty' => 1,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое аргумент функции по умолчанию?',
                'answer' => 'Значение, которое параметр получает, если при вызове его не передали. Синтаксис: function greet($name = "Гость") {} — можно вызвать greet() или greet("Иван"). Аргументы по умолчанию должны идти ПОСЛЕ обязательных. Значением может быть скаляр, массив, константа или (с PHP 8.1) new Класс.',
                'difficulty' => 1,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое variadic-функция (переменное число аргументов)?',
                'answer' => 'Функция, принимающая произвольное число аргументов. Синтаксис: function sum(int ...$nums) {} — внутри $nums будет массивом всех переданных значений. Вызов: sum(1, 2, 3) или sum(...$array). Удобно для логгеров, агрегаторов, фабрик.',
                'difficulty' => 2,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое type hints для параметров функции?',
                'answer' => 'Объявление ожидаемого типа параметра: function greet(string $name, int $age) {}. Если передать неверный тип — PHP бросит TypeError. Поддерживаются: int, float, string, bool, array, callable, iterable, object, имена классов/интерфейсов, ?Type (nullable), union (int|string), intersection (A&B).',
                'difficulty' => 2,
                'topic' => 'php.basic_syntax',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое приведение типов (type juggling) в PHP?',
                'answer' => 'Автоматическое преобразование значения из одного типа в другой при операции. "5" + 3 даст 8 (string → int). "abc" + 3 даст 3 (нечисловая строка → 0). Это удобно, но опасно — источник тонких багов. Явное приведение: (int)"5", (string)42, intval("5"), strval(42). С strict_types=1 при передаче в функцию авто-приведения нет — TypeError.',
                'difficulty' => 2,
                'topic' => 'php.basic_syntax',
            ],
        ];
    }
}
