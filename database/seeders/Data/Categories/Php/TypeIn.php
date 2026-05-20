<?php

namespace Database\Seeders\Data\Categories\Php;

class TypeIn
{
    public static function all(): array
    {
        return [
            [
                'category' => 'PHP',
                'question' => 'Функция PHP для разбиения строки на массив по разделителю.',
                'answer' => '**`explode($delimiter, $string, $limit = PHP_INT_MAX)`** — разбивает строку на массив по строке-разделителю.

**Обратная функция:** **`implode($glue, $array)`** (она же `join`).

**Особенности:**
- разделитель — **строка** (не regex), для regex — `preg_split`
- **`$limit`** ограничивает количество кусков (полезно для парсинга `"key=value=extra"` на 2 части)
- если разделитель **не найден** — вернёт массив с одним элементом (исходной строкой)
- пустая строка-разделитель → **`ValueError`**',
                'code_language' => 'php',
                'short_answer' => 'explode',
                'difficulty' => 2,
                'topic' => 'php.type_in',
            ],
            [
                'category' => 'PHP',
                'question' => 'Функция, склеивающая массив строк в одну строку.',
                'answer' => '**`implode($glue, $array)`** — склеивает элементы массива в строку через разделитель.

**Алиас** — **`join`** (полностью идентично).

**Пример:** `implode(", ", ["a", "b", "c"])` → `"a, b, c"`.

**Обратная функция:** **`explode($delimiter, $string)`**.

**Нюанс:** порядок аргументов исторически был гибким, но с PHP 8.0 устаревший вариант `implode($array, $glue)` **удалён** — только `implode($glue, $array)`.',
                'short_answer' => 'implode',
                'difficulty' => 2,
                'topic' => 'php.type_in',
            ],
            [
                'category' => 'PHP',
                'question' => 'Функция, возвращающая количество элементов массива.',
                'answer' => '**`count($array, $mode = COUNT_NORMAL)`** — возвращает число элементов массива.

Алиас — **`sizeof`** (то же самое, отличий нет).

С флагом `COUNT_RECURSIVE` считает элементы во вложенных массивах.',
                'short_answer' => 'count',
                'difficulty' => 1,
                'topic' => 'php.type_in',
            ],
            [
                'category' => 'PHP',
                'question' => 'Функция для проверки существования ключа в массиве (не путать с isset).',
                'answer' => '**`array_key_exists($key, $array)`** — проверяет, **существует ли ключ** в массиве.

**Главное отличие от `isset($array[$key])`:**
- **`isset`** возвращает **`false`**, если значение под ключом — **`null`**
- **`array_key_exists`** вернёт **`true`** — ключ есть, неважно какое значение

**Когда это важно:** различить «ключа нет в массиве» и «ключ есть, но значение `null`». Например, **частичный апдейт DTO**: `["name" => null]` означает «обнули поле», а отсутствие ключа — «не трогай».

**Производительность:** `isset` чуть быстрее, поэтому в горячем коде, где `null` не ожидается, используют его.',
                'short_answer' => 'array_key_exists',
                'difficulty' => 2,
                'topic' => 'php.type_in',
            ],
            [
                'category' => 'PHP',
                'question' => 'Функция для сортировки ассоциативного массива по значениям с сохранением ключей.',
                'answer' => '**`asort($array)`** — сортирует по **значению** по возрастанию и **сохраняет ассоциативные ключи**.

**Семейство sort-функций в PHP:**
- **`sort`** / **`rsort`** — по значению, **переиндексирует** (ключи теряются)
- **`asort`** / **`arsort`** — по значению, **ключи сохраняются** (a = associative)
- **`ksort`** / **`krsort`** — по **ключу** (k = key)
- **`usort`** / **`uasort`** / **`uksort`** — пользовательская функция сравнения (u = user)

**Префикс `r`** — reverse (по убыванию). **Все эти функции мутируют** исходный массив (передан по ссылке).',
                'short_answer' => 'asort',
                'difficulty' => 2,
                'topic' => 'php.type_in',
            ],
            [
                'category' => 'PHP',
                'question' => 'SPL-класс - двусвязный список с push/pop/shift/unshift на обоих концах (deque), основа для SplStack и SplQueue.',
                'answer' => 'SplDoublyLinkedList - двусвязный список с операциями на обоих концах. На его базе реализованы SplQueue (FIFO, разрешает enqueue/dequeue) и SplStack (LIFO).',
                'short_answer' => 'SplDoublyLinkedList',
                'difficulty' => 4,
                'topic' => 'php.type_in',
            ],
            [
                'category' => 'PHP',
                'question' => 'Функция для безопасного сравнения строк, устойчивая к timing-атакам.',
                'answer' => 'hash_equals($known, $user) выполняется за константное время и применяется при сравнении токенов/HMAC.',
                'short_answer' => 'hash_equals',
                'difficulty' => 3,
                'topic' => 'php.type_in',
            ],
        ];
    }
}
