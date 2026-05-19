<?php

namespace Database\Seeders\Data\Categories\Php;

class Strings
{
    public static function all(): array
    {
        return [
            [
                'category' => 'PHP',
                'question' => 'Как заменить подстроку в строке?',
                'answer' => 'str_replace($search, $replace, $subject) - простая замена, можно передать массивы (массив искомых в массив заменяемых поэлементно). str_ireplace - регистронезависимая. substr_replace - замена по позиции и длине. preg_replace - по регулярному выражению. strtr - перевод символов или подстрок по карте.',
                'code_example' => '<?php
echo str_replace("мир", "PHP", "Привет, мир!");
// "Привет, PHP!"

// Массивы
echo str_replace(
    ["a", "e"],
    ["@", "3"],
    "apple"
); // "@ppl3"

// По позиции (заменить с 7 длиной 3)
echo substr_replace("Hello, World!", "PHP", 7, 5);
// "Hello, PHP!"

// Карта замен
echo strtr("test", ["t" => "T", "e" => "3"]);
// "T3sT"',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.strings',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем mb_strlen отличается от strlen?',
                'answer' => 'strlen возвращает количество БАЙТ в строке, а не символов. Для ASCII это одно и то же. Для UTF-8 кириллический символ занимает 2 байта, простой эмодзи - 4. mb_strlen возвращает количество СИМВОЛОВ (точнее - Unicode codepoint-ов) с учётом кодировки. Если работаешь с многобайтными строками - всегда используй mb_* функции (mb_substr, mb_strtolower, mb_str_split, mb_strpos). ВАЖНО: mb_strlen считает codepoint-ы, а не визуальные символы (графемы). Эмодзи семьи "👨‍👩‍👧‍👦" - это последовательность из 4 человечков, склеенных тремя ZWJ (U+200D), всего 7 codepoint-ов; mb_strlen вернёт 7, а не 1. Эмодзи с модификатором цвета кожи "👍🏽" - 2 codepoint-а. Чтобы получить визуальную длину, используй grapheme_strlen() / grapheme_substr() из ext-intl - они работают с расширенными кластерами графем по UAX #29.',
                'code_example' => '<?php
$str = "Привет";

echo strlen($str);    // 12 (по 2 байта на букву в UTF-8)
echo mb_strlen($str); // 6  (реальная длина)

// Простой эмодзи занимает 4 байта = 1 codepoint = 1 графема
echo strlen("🚀");          // 4
echo mb_strlen("🚀");       // 1
echo grapheme_strlen("🚀"); // 1

// Составной эмодзи: codepoint != графема
echo strlen("👨‍👩‍👧‍👦");          // 25 байт
echo mb_strlen("👨‍👩‍👧‍👦");       // 7 codepoint-ов (4 человечка + 3 ZWJ)
echo grapheme_strlen("👨‍👩‍👧‍👦"); // 1 — реальная визуальная длина

// Аналогично для substr - режет ПО БАЙТАМ, "П" в UTF-8 = 2 байта
echo substr("Привет", 0, 1);    // 0xd0 - "битый" символ (половина "П")!
echo substr("Привет", 0, 2);    // "П" - случайно валидно, потому что П = ровно 2 байта
echo substr("Привет", 0, 3);    // "П" + 0xd1 - битый хвост
echo mb_substr("Привет", 0, 2); // "Пр" - корректные ДВА символа

// Регистр
echo strtolower("ПРИВЕТ");    // ПРИВЕТ - не работает!
echo mb_strtolower("ПРИВЕТ"); // привет',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.strings',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как форматировать строку в PHP?',
                'answer' => 'sprintf($format, ...$args) форматирует и возвращает строку, printf - сразу выводит. number_format форматирует числа с разделителями. Спецификаторы: %s - строка, %d - int, %f - float, %x - hex, %b - binary. Можно задать ширину, точность, выравнивание. Для интерполяции в шаблонах часто проще использовать обычные двойные кавычки или heredoc.',
                'code_example' => '<?php
$price = 19.5;
$name = "Книга";

echo sprintf("Товар: %s, цена: %.2f", $name, $price);
// "Товар: Книга, цена: 19.50"

// Ширина и выравнивание
echo sprintf("|%-10s|%10s|", "left", "right");
// "|left      |     right|"

// Числа с разделителями
echo number_format(1234567.891, 2, ".", " ");
// "1 234 567.89"

// Padding
echo str_pad("5", 3, "0", STR_PAD_LEFT); // "005"',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.strings',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как найти подстроку в строке в PHP?',
                'answer' => 'strpos($haystack, $needle) - первое вхождение, возвращает позицию или FALSE. ВАЖНО: используй === false, потому что 0 == false! stripos - регистронезависимый. strrpos - последнее вхождение. С PHP 8 появились str_contains/str_starts_with/str_ends_with - проще и безопаснее. Для регулярок - preg_match.',
                'code_example' => '<?php
$str = "Hello, World!";

// Старый способ
$pos = strpos($str, "World");
if ($pos !== false) {        // строго!
    echo "Найдено на $pos";  // 7
}

// PHP 8+
if (str_contains($str, "World")) { /* ... */ }
if (str_starts_with($str, "Hello")) { /* ... */ }
if (str_ends_with($str, "!")) { /* ... */ }

// Ловушка с == false
if (strpos("0123", "0") == false) {
    echo "Не найдено"; // НО ОНО НАЙДЕНО на позиции 0!
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.strings',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как обрезать пробелы и спецсимволы в строке?',
                'answer' => 'trim - обрезает с обеих сторон, ltrim - слева, rtrim - справа. По умолчанию обрезают пробелы, табы, переводы строк, \\r, \\0, \\v. Вторым параметром можно указать свой набор символов (это набор, не подстрока!). Для обрезки конкретной подстроки используй preg_replace или substr.',
                'code_example' => '<?php
echo trim("  hello  ");           // "hello"
echo ltrim("---test", "-");        // "test"
echo rtrim("file.txt", ".txt");    // "file" (поштучно убирает символы!)

// Символьный набор - это НЕ подстрока
echo rtrim("test.txt", "txt.");    // "tes" - убрало все t,x,t,.

// Обрезать конкретный суффикс
$path = "/var/www/";
$path = rtrim($path, "/");         // "/var/www"

// PHP 8: убрать конкретный префикс/суффикс
function stripPrefix(string $s, string $p): string {
    return str_starts_with($s, $p) ? substr($s, strlen($p)) : $s;
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.strings',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что делают str_contains(), str_starts_with() и str_ends_with()?',
                'answer' => 'Функции из PHP 8.0, проверяющие вхождение, префикс и суффикс подстроки. Возвращают bool, поэтому ими можно сразу if-ить — без классической ловушки старого strpos() === false (где 0 == false и поиск в начале выглядит как «не нашёл»). Все три работают по байтам, не по символам — для UTF-8 это корректно для ASCII-подстрок и для байтовых сравнений; для unicode-нормализации нужен ext-intl. Граничный случай: пустая «игла» всегда даёт true.',
                'code_example' => '<?php
$s = "Hello, World!";

if (str_contains($s, "World"))   { /* нашли */ }
if (str_starts_with($s, "Hello")) { /* начинается с */ }
if (str_ends_with($s, "!"))       { /* заканчивается на */ }

// До PHP 8.0 приходилось писать так — с === false
if (strpos($s, "Hello") === 0)             { /* префикс */ }
if (substr($s, -1) === "!")                { /* суффикс */ }
if (strpos($s, "World") !== false)         { /* вхождение */ }',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.strings',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем mb_* функции отличаются от обычных строковых и когда это критично?',
                'answer' => 'strlen, substr, strtolower работают побайтово. Для UTF-8 один кириллический символ - 2 байта, эмодзи - 4. mb_* функции учитывают кодировку и возвращают длину/срез в символах. Использование strlen для валидации длины пароля или substr для превью текста - частый источник багов и mojibake. Дефолтную кодировку для mb_* функций задают через ini default_charset=UTF-8 (актуальная общая настройка кодировки PHP, которой следуют mbstring/htmlspecialchars/etc) или явно вызовом mb_internal_encoding("UTF-8") в bootstrap. Старая ini mbstring.internal_encoding deprecated с PHP 5.6 - не используйте её в новых проектах.',
                'code_example' => '<?php
$s = "Привет";

// Длина — для валидации логина/превью нужны СИМВОЛЫ, не байты
strlen($s);       // 12
mb_strlen($s);    // 6  ← правильно

// Срез — substr ломает UTF-8
substr($s, 0, 3);     // битый хвост
mb_substr($s, 0, 3);  // "При"

// Регистр
strtolower("ПРИВЕТ");     // "ПРИВЕТ" — не работает
mb_strtolower("ПРИВЕТ");  // "привет"

// В bootstrap проекта
mb_internal_encoding("UTF-8");',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.strings',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как узнать длину строки в PHP и в чём подвох с UTF-8?',
                'answer' => 'strlen($s) возвращает длину строки в БАЙТАХ. Для ASCII (только латиница, цифры, базовая пунктуация) это совпадает с числом символов. Для UTF-8 один кириллический символ — 2 байта, эмодзи — 4 байта, поэтому strlen("Привет") даёт 12, а не 6. Чтобы получить число символов в UTF-8, используют mb_strlen($s, "UTF-8") — она вернёт 6. Правило: для байтовой длины (например, для лимита в БД на TEXT-колонку) — strlen, для пользовательских ограничений ("логин не длиннее 20 символов") — mb_strlen.',
                'code_example' => '<?php
echo strlen("Hello");    // 5
echo strlen("Привет");   // 12 (по 2 байта)
echo mb_strlen("Привет"); // 6',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.strings',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как получить подстроку в PHP и что значат отрицательные индексы?',
                'answer' => 'substr($string, $start, $length) — берёт кусок строки. $start — позиция начала (с 0). Отрицательный $start отсчитывается от конца: substr("hello", -2) даёт "lo". Если $length опущен — до конца строки; отрицательная $length — сколько символов отбросить с конца. ВАЖНО: substr режет ПО БАЙТАМ и ломает UTF-8 символы. Для UTF-8 используйте mb_substr с тем же интерфейсом.',
                'code_example' => '<?php
echo substr("hello world", 0, 5);   // "hello"  — с 0-го, 5 символов
echo substr("hello world", 6);      // "world"  — с 6-го до конца
echo substr("hello world", -5);     // "world"  — последние 5
echo substr("hello world", 0, -6);  // "hello"  — отбросить 6 с конца

// ⚠️ substr ломает UTF-8 (режет по байтам)
echo substr("Привет", 0, 2);        // битый хвост — "П" = 2 байта
echo mb_substr("Привет", 0, 3);     // "При" — правильно',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.strings',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как изменить регистр строки в PHP?',
                'answer' => 'strtolower / strtoupper — в нижний/верхний регистр. ucfirst — первую букву в верхний. lcfirst — первую в нижний. ucwords — каждое слово с заглавной. Все эти функции работают только с ASCII; для кириллицы и других нелатинских алфавитов используют mb_strtolower / mb_strtoupper / mb_convert_case с указанием кодировки UTF-8. ucfirst и ucwords для кириллицы тоже не работают — для них есть mb_convert_case с MB_CASE_TITLE.',
                'code_example' => '<?php
echo strtolower("HELLO");        // "hello"
echo strtoupper("hello");        // "HELLO"
echo ucfirst("hello world");     // "Hello world"
echo ucwords("hello world");     // "Hello World"

// Кириллица — только mb_*
echo strtoupper("привет");                            // "привет" — НЕ работает
echo mb_strtoupper("привет", "UTF-8");                // "ПРИВЕТ"
echo mb_convert_case("привет мир", MB_CASE_TITLE);    // "Привет Мир"',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.strings',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое heredoc и nowdoc в PHP?',
                'answer' => 'Heredoc — синтаксис для многострочной строки с интерполяцией переменных, как в двойных кавычках: $s = <<<EOT\nПривет, $name\nEOT;. Nowdoc — то же, но БЕЗ интерполяции, как одинарные кавычки: $s = <<<\'EOT\'\nПривет, $name (тут $name буквально)\nEOT;. С PHP 7.3 ослаблены требования к закрывающему маркеру: его можно делать с отступом, и сам маркер не обязан стоять в первой колонке.',
                'code_example' => '<?php
$name = "Иван";
$heredoc = <<<TXT
Здравствуйте, $name!
Сегодня хорошая погода.
TXT;

$nowdoc = <<<\'TXT\'
Тут $name остаётся как есть.
TXT;',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.strings',
            ],
        ];
    }
}
