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
                'answer' => 'trim - обрезает с обеих сторон, ltrim - слева, rtrim - справа. По умолчанию обрезают пробелы, табы, переводы строк, \r, \0, \v. Вторым параметром можно указать свой набор символов (это набор, не подстрока!). Для обрезки конкретной подстроки используй preg_replace или substr.',
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
                'answer' => 'Это добавленные в PHP 8.0 функции, которые проверяют вхождение, префикс и суффикс подстроки и возвращают bool. Они заменяют громоздкие конструкции на strpos() === false и работают корректно для пустой иглы, не требуя ручной обработки граничных случаев.',
                'difficulty' => 2,
                'topic' => 'php.strings',
            ],
        ];
    }
}
