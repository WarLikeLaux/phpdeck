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
                'answer' => '**`str_replace($search, $replace, $subject)`** — самая частая функция. Все вхождения заменяет на `$replace`.

**Можно передавать массивы:**
- `str_replace(["a","b"], ["x","y"], $s)` — поэлементная замена `a→x, b→y`
- `str_replace(["a","b"], "x", $s)` — `a` и `b` оба → `x`

**Близкие функции:**
- **`str_ireplace`** — то же, но **регистронезависимо**
- **`substr_replace($s, $repl, $offset, $length)`** — замена по **позиции и длине**, без поиска
- **`strtr($s, $map)`** — замена по **карте** `[from => to, ...]`, эффективнее чем многократный `str_replace`
- **`preg_replace`** — по **регулярному выражению**

**Внимание:** строки **иммутабельны** — все функции возвращают **новую** строку, исходный `$subject` не меняется.',
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
                'answer' => '**`sprintf($format, ...$args)`** — форматирует и **возвращает** строку. **`printf`** — сразу выводит.

**Основные спецификаторы:**
- **`%s`** — строка
- **`%d`** — целое (`int`)
- **`%f`** — float (по умолчанию 6 знаков)
- **`%.2f`** — float с **двумя знаками** после запятой
- **`%x`** / **`%X`** — hex (нижний/верхний регистр)
- **`%b`** — binary

**Ширина и выравнивание:**
- **`%10s`** — минимум 10 символов, выравнивание **по правому краю** (паддинг пробелами)
- **`%-10s`** — выравнивание **по левому краю**
- **`%05d`** — паддинг нулями слева (`007`)

**Альтернативы:**
- **`number_format`** — числа с разделителями тысяч и десятичной частью
- **`str_pad`** — добить строку до нужной длины символами
- для простой подстановки — **интерполяция** `"Hi, $name"` или **heredoc**',
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
                'answer' => '**Старый способ:** `strpos($haystack, $needle)` — возвращает **позицию** (`int`) или **`false`**, если не нашёл.

**Классическая ловушка:** если подстрока найдена в начале, возвращается `0`. А `0 == false` → `true`. Поэтому **всегда** проверяй через **`=== false`**, а не `==`:

```
if (strpos($s, "x") !== false) { /* нашли */ }
```

**Варианты strpos:**
- **`stripos`** — регистронезависимо
- **`strrpos`** — **последнее** вхождение
- **`strripos`** — последнее, без регистра

**С PHP 8.0 — современный способ** (возвращают `bool`, ловушки нет):
- **`str_contains($s, $needle)`** — содержит?
- **`str_starts_with($s, $prefix)`** — начинается с?
- **`str_ends_with($s, $suffix)`** — заканчивается на?

**Для регулярок** — `preg_match`.',
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
                'answer' => '- **`trim($s)`** — обрезает с **обеих** сторон
- **`ltrim($s)`** — только **слева**
- **`rtrim($s)`** / **`chop($s)`** — только **справа**

**По умолчанию** обрезают: пробел, **`\\t`** (таб), **`\\n`**, **`\\r`**, **`\\0`** (null-байт), **`\\v`** (вертикальная табуляция).

**Свой набор символов** — вторым параметром: `rtrim($s, "/")` уберёт все `/` с конца.

**Главная ловушка:** второй параметр — это **НАБОР символов**, а **не подстрока**!
- `rtrim("file.txt", ".txt")` → `"file"` — убрал все `.`, `t`, `x` с конца **поштучно**.

**Чтобы убрать конкретный суффикс/префикс** — используй проверки + `substr`:
- `str_ends_with($s, ".txt") ? substr($s, 0, -4) : $s`

С PHP 8.4 — **`str_ends_with`** + `substr` или собственный хелпер.',
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
                'answer' => 'Три функции из **PHP 8.0** для проверки **вхождения**, **префикса** и **суффикса**:

- **`str_contains($haystack, $needle)`** — содержит ли строка подстроку
- **`str_starts_with($haystack, $prefix)`** — начинается ли с
- **`str_ends_with($haystack, $suffix)`** — заканчивается ли на

**Все три возвращают `bool`** — можно сразу использовать в `if`, без ловушки старого `strpos() === false` (где `0 == false` превращало совпадение в начале строки в «не нашёл»).

**Работают по байтам.** Для ASCII-подстроки в UTF-8 — корректно. Для unicode-нормализации (например, для регистронезависимого сравнения кириллицы) — `mb_stripos` или `ext-intl`.

**Граничные случаи:**
- пустая `$needle` → всегда **`true`**
- сравнение чувствительно к регистру',
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
                'answer' => '**`strlen($s)`** возвращает длину строки **в байтах**.

- Для **ASCII** (латиница, цифры) байт = символ.
- Для **UTF-8** один кириллический символ — **2 байта**, эмодзи — **4 байта**. Поэтому `strlen("Привет")` даёт `12`, а не `6`.

**`mb_strlen($s, "UTF-8")`** возвращает число **символов** — `6`.

**Когда что использовать:**
- байтовая длина (лимит в БД на TEXT-колонку) — `strlen`
- пользовательские ограничения («логин не длиннее 20 символов») — **`mb_strlen`**',
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
                'answer' => '**`substr($string, $start, $length)`** — берёт кусок строки.

**Параметры:**
- `$start` — позиция начала (с 0). **Отрицательный** — от конца: `substr("hello", -2)` → `"lo"`.
- `$length` — если опущен, берёт до конца. **Отрицательный** — сколько символов отбросить с конца.

**Важно:** `substr` режет **по байтам** и ломает UTF-8 символы. Для UTF-8 используй **`mb_substr`** с тем же интерфейсом.',
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
                'answer' => '**Для ASCII (только латиница):**
- `strtolower` / `strtoupper` — нижний / верхний регистр
- `ucfirst` — первую букву в верхний
- `lcfirst` — первую в нижний
- `ucwords` — каждое слово с заглавной

**Для кириллицы и других нелатинских алфавитов — только `mb_*`:**
- `mb_strtolower` / `mb_strtoupper` с указанием кодировки `"UTF-8"`
- `mb_convert_case($s, MB_CASE_TITLE)` — аналог `ucwords` для UTF-8

`ucfirst` и `ucwords` для кириллицы **не работают** — буквы остаются как есть.',
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
                'answer' => 'Синтаксис для **многострочных строк**, удобный для шаблонов, SQL, HTML.

**Heredoc** — как **двойные кавычки** (с интерполяцией переменных и escape-последовательностями):
```
$s = <<<EOT
Привет, $name!
EOT;
```

**Nowdoc** — как **одинарные кавычки** (всё буквально, БЕЗ интерполяции). Маркер в одинарных кавычках:
```
$s = <<<\'EOT\'
Тут $name остаётся как есть.
EOT;
```

**Правила маркера:**
- открывающий — `<<<NAME` (имя по выбору, по соглашению UPPERCASE)
- закрывающий — то же имя, дальше `;`
- с **PHP 7.3+** закрывающий маркер можно делать **с отступом** (любым) — отступ обрезается от всех строк содержимого

**Применение:** длинные SQL-запросы, шаблоны писем, многострочные сообщения об ошибках.',
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
