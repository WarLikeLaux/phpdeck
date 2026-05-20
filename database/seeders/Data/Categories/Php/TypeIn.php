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
                'answer' => 'explode($delimiter, $string, $limit = PHP_INT_MAX) - обратная к implode/join.',
                'code_example' => '$parts = explode(",", "a,b,c"); // ["a","b","c"]',
                'code_language' => 'php',
                'short_answer' => 'explode',
                'difficulty' => 2,
                'topic' => 'php.type_in',
            ],
            [
                'category' => 'PHP',
                'question' => 'Функция, склеивающая массив строк в одну строку.',
                'answer' => 'implode($glue, $array). Алиас - join.',
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
                'answer' => 'array_key_exists возвращает true даже если значение под ключом - null, в отличие от isset.',
                'short_answer' => 'array_key_exists',
                'difficulty' => 2,
                'topic' => 'php.type_in',
            ],
            [
                'category' => 'PHP',
                'question' => 'Функция для сортировки ассоциативного массива по значениям с сохранением ключей.',
                'answer' => 'asort сортирует по значению по возрастанию и сохраняет ассоциативные ключи. arsort - то же по убыванию.',
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
