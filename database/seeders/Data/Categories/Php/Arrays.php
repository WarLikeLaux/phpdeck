<?php

namespace Database\Seeders\Data\Categories\Php;

class Arrays
{
    public static function all(): array
    {
        return [
            [
                'category' => 'PHP',
                'question' => 'Как работает array_map?',
                'answer' => '**`array_map($callback, $array)`** — применяет `$callback` к **каждому элементу** массива и возвращает **новый** массив с результатами.

**Свойства:**
- **не мутирует** исходный массив (иммутабельная операция)
- **ключи сохраняются** для одного массива
- если массивов несколько — **ключи сбрасываются** в `0, 1, 2...`

**С несколькими массивами:** `array_map(fn($x, $y) => $x + $y, $a, $b)` — callback получает по одному элементу из каждого массива; результат — поэлементное применение.

**Спецтрюк:** `array_map(null, $a, $b)` — «склеивание» массивов в пары (zip).',
                'code_example' => '<?php
$nums = [1, 2, 3, 4];
$squares = array_map(fn($x) => $x ** 2, $nums);
// [1, 4, 9, 16]

// С несколькими массивами
$a = [1, 2, 3];
$b = [10, 20, 30];
$sums = array_map(fn($x, $y) => $x + $y, $a, $b);
// [11, 22, 33]

// С null callback - "склеивание"
$result = array_map(null, $a, $b);
// [[1,10], [2,20], [3,30]]',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.arrays',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем отличаются array_filter и array_map?',
                'answer' => 'Обе функции принимают массив и callback, **не мутируют** оригинал — но делают разное.

- **`array_map`** — **преобразует** каждый элемент. Размер массива не меняется.
- **`array_filter`** — **отсеивает**. Оставляет только те элементы, для которых callback вернул **truthy**. Размер обычно уменьшается.

**Ключи в обоих сохраняются** — после `array_filter` массив может иметь «дыры» в индексах (`[0 => "a", 2 => "c"]`). Чтобы сбросить — оберни в **`array_values()`**.

**`array_filter` без callback** — удаляет все **falsy** значения: `0`, `""`, `null`, `false`, `[]`, `"0"`.

**Третий аргумент `array_filter`** — режим: `ARRAY_FILTER_USE_KEY` (callback получает ключ), `ARRAY_FILTER_USE_BOTH` (ключ и значение).',
                'code_example' => '<?php
$nums = [1, 2, 3, 4, 5];

// filter - оставить чётные
$even = array_filter($nums, fn($x) => $x % 2 === 0);
// [1 => 2, 3 => 4] - ключи сохранены!

// Сбросить ключи
$even = array_values($even);

// filter без callback - убрать falsy
$cleaned = array_filter([1, 0, "", "a", null, false]);
// [0 => 1, 3 => "a"]

// map - преобразовать
$doubled = array_map(fn($x) => $x * 2, $nums);
// [2, 4, 6, 8, 10]',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.arrays',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работает array_reduce?',
                'answer' => 'array_reduce сворачивает массив в одно значение, применяя callback пошагово. Callback принимает аккумулятор и текущий элемент, возвращает новое значение аккумулятора. Третий параметр - начальное значение (по умолчанию null). Используется для сумм, конкатенаций, поиска макс/мин, построения деревьев.',
                'code_example' => '<?php
$nums = [1, 2, 3, 4, 5];

// Сумма
$sum = array_reduce($nums, fn($carry, $x) => $carry + $x, 0);
// 15

// Произведение
$prod = array_reduce($nums, fn($c, $x) => $c * $x, 1);
// 120

// Объединение объектов
$users = [["age" => 20], ["age" => 30], ["age" => 25]];
$totalAge = array_reduce($users, fn($c, $u) => $c + $u["age"], 0);
// 75',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.arrays',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем отличается array_merge от оператора + для массивов?',
                'answer' => 'array_merge: для строковых ключей второй массив перезаписывает первый, для числовых - элементы добавляются и перенумеровываются. Оператор +: для любых ключей берётся значение из ЛЕВОГО массива (если ключ совпадает), числовые ключи НЕ перенумеровываются. То есть + объединяет с приоритетом левого, merge - с приоритетом правого для строк и склеиванием для чисел.',
                'code_example' => '<?php
$a = ["a" => 1, "b" => 2, 0 => "x"];
$b = ["b" => 3, "c" => 4, 0 => "y"];

print_r(array_merge($a, $b));
// ["a" => 1, "b" => 3, "c" => 4, 0 => "x", 1 => "y"]
// числовой ключ перенумерован, "b" взято из $b

print_r($a + $b);
// ["a" => 1, "b" => 2, 0 => "x", "c" => 4]
// "b" из $a, ключ 0 из $a, "c" добавлен из $b

// Spread (PHP 7.4+, с PHP 8.1 со строковыми ключами)
$merged = [...$a, ...$b];',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.arrays',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как проверить наличие элемента в массиве?',
                'answer' => 'in_array($needle, $haystack) - проверяет значение, без третьего параметра делает нестрогое сравнение (опасно!). Третий параметр true - строгое сравнение. array_search - возвращает ключ найденного значения или false. ⚠️ Классическая ловушка: array_search для первого элемента вернёт ключ 0, а 0 == false → true, поэтому ВСЕГДА сравнивайте через !== false, а не через == false / !$result. Та же проблема у strpos. isset($arr[$key]) - проверяет наличие ключа (но false если значение null). array_key_exists - проверяет ключ даже если значение null. Для МНОЖЕСТВЕННЫХ проверок по одному большому массиву (M проверок по N-элементному массиву внутри цикла) лучше один раз сделать array_flip - получится O(N) на flip + O(1) на каждую проверку = O(N+M) вместо O(N*M) у наивного in_array. Но если проверка ровно одна - in_array($val, $arr, true) дешевле: array_flip аллоцирует целый новый хэш-массив (доп. память O(N) и проход по всему оригиналу). Правило: flip-трюк оправдан только когда множественные lookup-ы в горячем пути; разовая проверка - in_array. С PHP 8.4 для коротких сценариев есть array_any / array_all / array_find / array_find_key - возвращают bool/значение и останавливаются на первом совпадении.',
                'code_example' => '<?php
$users = ["Иван", "Аня", "Петя"];

var_dump(in_array("Аня", $users));      // true
var_dump(in_array("0", $users, true));  // false (строго)

$key = array_search("Иван", $users);    // int(0) - первый элемент!

// ❌ Опасно - 0 == false → true, считаем "не найдено"
if ($key == false) { echo "not found"; } // НЕВЕРНО для первого элемента

// ✅ Правильно - строгое сравнение
if ($key === false) { echo "not found"; }
if (($key = array_search("X", $users)) !== false) { /* нашли */ }

$data = ["name" => null];
isset($data["name"]);              // false (null!)
array_key_exists("name", $data);   // true

// Быстрая проверка для большого набора
$set = array_flip(["a", "b", "c"]); // ["a"=>0, "b"=>1, "c"=>2]
isset($set["a"]); // O(1)',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.arrays',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем отличаются array_keys и array_values?',
                'answer' => '- **`array_keys($arr)`** — возвращает массив всех **ключей**, перенумерованный с 0.
- **`array_values($arr)`** — возвращает массив **значений**, перенумерованный с 0 (теряет исходные ключи).

**Полезные приёмы:**
- **`array_keys($arr, $value)`** — вернёт только ключи, где значение равно `$value` (можно третьим параметром `true` сделать сравнение строгим)
- **`array_values(array_filter(...))`** — типичная связка для сброса «дырявых» ключей после фильтрации
- **`array_combine($keys, $values)`** — обратная операция: собрать ассоциативный массив из двух массивов',
                'code_example' => '<?php
$user = ["name" => "Иван", "age" => 30, "role" => "admin"];

print_r(array_keys($user));
// [0 => "name", 1 => "age", 2 => "role"]

print_r(array_values($user));
// [0 => "Иван", 1 => 30, 2 => "admin"]

// Ключи с конкретным значением
$status = ["a" => "ok", "b" => "fail", "c" => "ok"];
print_r(array_keys($status, "ok"));
// [0 => "a", 1 => "c"]

// Сброс ключей после filter
$clean = array_values(array_filter($items));',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.arrays',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем отличается == от === при сравнении массивов?',
                'answer' => 'Массивы в PHP сравниваются по правилам, отличным от объектов и скаляров. Оператор == (loose equality): массивы равны, если содержат ОДИНАКОВЫЕ ПАРЫ ключ → значение в любом порядке (для каждого ключа сравнение значений тоже == с приведением типов). Порядок ключей не важен. Оператор === (strict identity): дополнительно требует одинакового ПОРЯДКА ключей и строгого сравнения значений (без приведения типов). Для индексированных массивов это означает: [1,2] === [1,2] true, [1,2] === [2,1] false (порядок ключей 0,1 разный по значениям). Для ассоциативных: ["a"=>1,"b"=>2] == ["b"=>2,"a"=>1] true (== игнорирует порядок), но === false. Это часто всплывает в тестах: assertEquals использует ==, assertSame - === - последний поймает баги, где код случайно перемешал порядок ключей. Сравнение через json_encode/serialize - не workaround: json_encode тоже зависит от порядка ключей.',
                'code_example' => '<?php
// Индексированные массивы
var_dump([1, 2, 3] == [1, 2, 3]);   // true
var_dump([1, 2, 3] === [1, 2, 3]);  // true
var_dump([1, 2] == [2, 1]);          // false (разные пары ключ→значение)
var_dump([1, 2] === [2, 1]);         // false

// Ассоциативные - порядок ключей различает только ===
$a = ["x" => 1, "y" => 2];
$b = ["y" => 2, "x" => 1];

var_dump($a == $b);   // true  - те же пары
var_dump($a === $b);  // false - разный порядок ключей

// Type juggling под == с приведением
var_dump(["n" => "1"] == ["n" => 1]);   // true  (string "1" == int 1)
var_dump(["n" => "1"] === ["n" => 1]);  // false (разные типы)

// На собеседовании: для уверенного сравнения структур всегда ===',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.arrays',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что произойдёт, если внутри foreach изменять сам массив (добавлять/удалять элементы)?',
                'answer' => 'foreach внутри работает с КОПИЕЙ массива (точнее - со снимком, через CoW: пока никто не пишет, физическая копия не создаётся; как только начинаются модификации - PHP отделяет копию). Поэтому добавления/удаления внутри цикла НЕ влияют на ход итерации: цикл продолжает идти по исходным элементам. Это безопасно, но может сбивать с толку, если ожидать, что новый элемент будет обработан в этом же проходе. ИСКЛЮЧЕНИЕ: foreach по ссылке (foreach ($arr as &$v)) работает с реальным массивом, и модификации $arr внутри цикла могут привести к неопределённому поведению - порядку, пропускам, бесконечному циклу. Также foreach по объекту, реализующему Iterator, идёт по реальному состоянию объекта - тут модификации видны.',
                'code_example' => '<?php
// foreach по значению - изменения вне цикла не влияют
$arr = [1, 2, 3];
foreach ($arr as $v) {
    if ($v === 2) $arr[] = 100;  // добавляем
    echo $v . " ";                // 1 2 3 - 100 НЕ напечатано
}
print_r($arr);  // массив реально стал [1, 2, 3, 100]

// foreach по ссылке + модификация - undefined behavior
$arr = [1, 2, 3];
foreach ($arr as &$v) {
    if ($v === 2) $arr[] = 100; // потенциальный бесконечный цикл / краш
}
// НЕ ДЕЛАЙТЕ ТАК

// Безопасный паттерн для модификации в процессе:
// собрать изменения отдельно, применить после цикла
$toAdd = [];
foreach ($arr as $v) {
    if (needsExpansion($v)) $toAdd[] = expand($v);
}
$arr = array_merge($arr, $toAdd);',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.arrays',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что делает array_diff и чем отличается от array_intersect?',
                'answer' => '- **`array_diff($a, $b, ...)`** — возвращает значения **из `$a`, которых нет** в остальных массивах. Аналог «вычитания» множеств.
- **`array_intersect($a, $b, ...)`** — возвращает значения, **присутствующие во всех** массивах. Аналог пересечения.

**Оба сравнивают значения через `(string)$x === (string)$y`** — не `==` и не `===`. Поэтому на объектах без `__toString` будет ошибка, а у `float` возможны сюрпризы с округлением.

**Варианты:**
- **`*_key`** (`array_diff_key`, `array_intersect_key`) — сравнивают **только ключи**
- **`*_assoc`** (`array_diff_assoc`, `array_intersect_assoc`) — сравнивают **и ключи, и значения**
- **`array_udiff` / `array_uintersect`** — callback-варианты для **своего** сравнения (например, по `===` или по полю объекта)',
                'code_example' => '<?php
$a = [1, 2, 3, 4, 5];
$b = [3, 4, 5, 6, 7];

print_r(array_diff($a, $b));
// [0 => 1, 1 => 2] - что есть в $a, но нет в $b

print_r(array_intersect($a, $b));
// [2 => 3, 3 => 4, 4 => 5] - общие

// По ключам
$x = ["a" => 1, "b" => 2];
$y = ["a" => 5, "c" => 3];
print_r(array_diff_key($x, $y));   // ["b" => 2]
print_r(array_diff_assoc($x, $y)); // ["a" => 1, "b" => 2]',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.arrays',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как отсортировать массив в PHP?',
                'answer' => 'Семейство функций сортировки — все они **мутируют** исходный массив (передача по ссылке) и возвращают `bool`.

**По значениям:**
- **`sort` / `rsort`** — по возрастанию/убыванию, **ключи теряются** (перенумеровка)
- **`asort` / `arsort`** — по значениям, **ключи сохраняются** (для ассоциативных массивов)

**По ключам:**
- **`ksort` / `krsort`** — сортирует по ключу

**С callback:**
- **`usort($a, fn($x, $y) => $x <=> $y)`** — своя логика сравнения, ключи теряются
- **`uasort`** — то же, но ключи сохраняются
- **`uksort`** — callback сортирует ключи

**Специальные:**
- **`natsort`** — «натуральная» сортировка: `file2 < file10` (обычная сортировка дала бы `file10 < file2`)
- **`array_multisort`** — сортировка нескольких массивов параллельно

**С PHP 8.0** все встроенные сортировки **стабильны** (равные элементы сохраняют порядок).',
                'code_example' => '<?php
$nums = [3, 1, 4, 1, 5, 9, 2, 6];

sort($nums);  // мутирует! [1, 1, 2, 3, 4, 5, 6, 9]
rsort($nums); // по убыванию

$users = ["c" => 3, "a" => 1, "b" => 2];
asort($users); // сохраняет ключи: ["a"=>1, "b"=>2, "c"=>3]
ksort($users); // сортирует по ключам

// Кастомная
$products = [
    ["name" => "B", "price" => 100],
    ["name" => "A", "price" => 50],
];
usort($products, fn($x, $y) => $x["price"] <=> $y["price"]);

// Натуральная
$files = ["img10", "img2", "img1"];
natsort($files); // img1, img2, img10',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.arrays',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как объединить массив в строку и обратно?',
                'answer' => '**Массив → строка:**
- **`implode($glue, $array)`** — склеивает элементы через разделитель. **`join()`** — синоним.

**Строка → массив:**
- **`explode($delimiter, $string, $limit)`** — разбивает по разделителю.
- Третий параметр `$limit`:
  - **положительный** — максимум кусков (последний кусок «соберёт» хвост)
  - **отрицательный** — сколько кусков **отбросить с конца**
  - **`0`** трактуется как `1`

**Альтернативы:**
- **`str_split($str, $length)`** — разбивает на части **фиксированной длины** (по байтам! для UTF-8 — `mb_str_split`)
- **`preg_split("/$regex/", $str)`** — разбивает по **регулярке** (например, по любому из нескольких разделителей)

**Внимание:** разделитель `implode` должен быть **строкой**, а не массивом — иначе ошибка с PHP 8.',
                'code_example' => '<?php
$arr = ["apple", "banana", "cherry"];
echo implode(", ", $arr); // "apple, banana, cherry"

$str = "one,two,three,four";
print_r(explode(",", $str));
// ["one", "two", "three", "four"]

// Ограничение
print_r(explode(",", $str, 2));
// ["one", "two,three,four"]

print_r(explode(",", $str, -1));
// ["one", "two", "three"] - отбросили последний

// На фиксированные части
print_r(str_split("abcdef", 2));
// ["ab", "cd", "ef"]',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.arrays',
            ],
            [
                'category' => 'PHP',
                'question' => 'Какая алгоритмическая сложность у популярных операций над массивами в PHP?',
                'answer' => 'PHP-массив - это упорядоченная hashtable (HashTable из ext/standard), а не классический индексный массив, как в C. Это даёт ровную картину сложностей. O(1) - амортизированно: array_push / $a[] = (вставка в конец), array_pop, isset($a[$k]), array_key_exists($k, $a), unset($a[$k]) (но не пересчитывает индексы), count() (хранится отдельно). O(N): array_unshift, array_shift - сдвигают все integer-ключи на 1, поэтому линейные; in_array, array_search - линейный поиск по значениям; array_values, array_keys, array_flip, array_merge, array_filter, array_map - проходят весь массив. O(N log N): sort, asort, ksort, usort и компания (quicksort/mergesort внутри). Практический Senior-приём: для частых проверок "есть ли элемент" не используй in_array(O(N)) - сделай array_flip один раз и потом isset (O(1)). Для очереди FIFO с большим N используй SplDoublyLinkedList или SplQueue вместо array_shift - последний O(N) на каждом вызове. Учти расход памяти: PHP-массив тяжёлый (один элемент ~100+ байт из-за hashtable + zval); для больших данных используй SplFixedArray или объекты с типизированными свойствами.',
                'code_example' => '<?php
// ❌ Плохо: O(N²) - линейный поиск внутри цикла
$ids = range(1, 100_000);
$lookup = [42, 7, 99_999];
foreach ($lookup as $id) {
    if (in_array($id, $ids)) { /* O(N) на каждой итерации */ }
}

// ✅ Хорошо: O(N) на flip + O(1) на проверку
$flipped = array_flip($ids); // [1=>0, 2=>1, ...]
foreach ($lookup as $id) {
    if (isset($flipped[$id])) { /* O(1) */ }
}

// ❌ Плохо: O(N²) на разворот через unshift в цикле
$result = [];
foreach ($items as $item) {
    array_unshift($result, $item); // сдвиг всех элементов
}

// ✅ Хорошо: O(N) push + reverse
$result = [];
foreach ($items as $item) {
    $result[] = $item;     // O(1)
}
$result = array_reverse($result); // O(N)

// Очередь FIFO
$q = new SplQueue();
$q->enqueue("a"); $q->dequeue(); // обе O(1), без сдвигов',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.arrays',
            ],
            [
                'category' => 'PHP',
                'question' => 'В чём разница между array_slice и array_splice?',
                'answer' => 'Похожие имена - совершенно разное поведение, классический вопрос-ловушка. array_slice($arr, $offset, $length) - ИММУТАБЕЛЕН: возвращает новый массив с куском исходного, оригинал не трогает. По умолчанию пере-индексирует целочисленные ключи (пере-нумерация), строковые сохраняет; чтобы сохранить и числовые - четвёртый аргумент preserve_keys=true. array_splice(&$arr, $offset, $length, $replacement) - МУТИРУЕТ: вырезает из исходного массива указанный кусок (через ссылку), возвращает вырезанные элементы, и опционально вставляет на их место $replacement (массив или одно значение). Целочисленные ключи всегда пере-нумеруются, строковые сохраняются. Удобно для "удалить элемент по индексу", "заменить кусок". Senior-приём: array_splice через ссылку - редкий случай, когда стандартная функция мутирует аргумент; легко не заметить, что массив изменился. Для иммутабельного "удалить элемент" используй array_diff_key + array_values или array_filter.',
                'code_example' => '<?php
$arr = [10, 20, 30, 40, 50];

// array_slice - копия
$piece = array_slice($arr, 1, 2);
// $piece = [20, 30]
// $arr   = [10, 20, 30, 40, 50] - не изменился

// array_splice - мутирует, возвращает вырезанное
$cut = array_splice($arr, 1, 2);
// $cut = [20, 30]
// $arr = [10, 40, 50] - элементы удалены, ключи пере-нумерованы

// array_splice + замена
$arr = [10, 20, 30, 40, 50];
array_splice($arr, 1, 2, ["a", "b", "c"]);
// $arr = [10, "a", "b", "c", 40, 50]

// preserve_keys для slice
$assoc = ["x" => 1, 5 => "z", "y" => 2];
print_r(array_slice($assoc, 1, 2));
// ["x"=>... убрано, числовой ключ пере-нумерован]
print_r(array_slice($assoc, 1, 2, preserve_keys: true));
// числовые ключи сохранены

// "Удалить элемент по индексу" - частый use case
unset($arr[2]);                    // оставит "дыру" в индексах [0,1,3,4]
array_splice($arr, 2, 1);          // переиндексирует, удалит элемент
$arr = array_values(array_filter($arr, fn($_, $i) => $i !== 2,
                                  ARRAY_FILTER_USE_BOTH)); // immutable',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.arrays',
            ],
            [
                'category' => 'PHP',
                'question' => 'Почему array_shift медленный и какие быстрые альтернативы без SPL?',
                'answer' => 'array_shift($arr) удаляет ПЕРВЫЙ элемент массива и возвращает его, при этом РЕИНДЕКСИРУЕТ все целочисленные ключи (сдвигает 1→0, 2→1, ... N→N-1). Это O(N) на каждый вызов: на массиве из 100 000 элементов цикл while ($x = array_shift($arr)) даст квадратичную сложность O(N²). Та же проблема у array_unshift (вставка в начало). Решения: 1) Если порядок важен и SPL доступен - SplDoublyLinkedList / SplQueue с O(1) на голову/хвост. 2) Без SPL и если можно поменять направление обхода - array_reverse один раз (O(N)), а потом array_pop в цикле: array_pop удаляет последний элемент за O(1) без реиндексации; total = O(N). 3) Просто индекс-переменная: $i = 0; while ($i < count($arr)) { $x = $arr[$i++]; ... } - не мутирует массив вообще, O(1) на итерацию, но не освобождает память. 4) end()/prev() с встроенным указателем массива - тоже O(1) на ход, но мутируют внутренний pointer, что чревато сюрпризами при вложенной итерации. Senior-приём: помнить, что array_shift/unshift - O(N), и для очередей FIFO с большим N брать SPL или реверс+pop.',
                'code_example' => '<?php
// ❌ O(N²) - каждое array_shift сдвигает все индексы
$queue = range(1, 100000);
while (count($queue) > 0) {
    $task = array_shift($queue); // O(N) на каждой итерации
    process($task);
}
// На 100k элементов - десятки секунд

// ✅ O(N) total - один раз reverse, дальше pop с конца
$queue = range(1, 100000);
$queue = array_reverse($queue); // O(N) один раз
while (count($queue) > 0) {
    $task = array_pop($queue);   // O(1) - снимает с конца, не сдвигает
    process($task);
}
// Те же 100k - доли секунды

// ✅ Альтернатива - SPL с O(1) на оба конца
$queue = new SplQueue();
foreach (range(1, 100000) as $n) $queue->enqueue($n);
while (!$queue->isEmpty()) {
    $task = $queue->dequeue(); // O(1)
    process($task);
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.arrays',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как использовать распаковку массивов со строковыми ключами через ...?',
                'answer' => 'С PHP 8.1 оператор ... внутри литерала массива поддерживает строковые ключи, поэтому [...$defaults, ...$custom] работает как слияние с приоритетом правого операнда. До 8.1 такой код вызывал фатальную ошибку, и приходилось использовать array_merge.',
                'code_example' => '<?php
$defaults = ["timeout" => 30, "retries" => 3];
$custom   = ["timeout" => 10, "ssl" => true];

// PHP 8.1+
$config = [...$defaults, ...$custom];
// ["timeout" => 10, "retries" => 3, "ssl" => true]
// timeout перебит правым (как и в array_merge)',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.arrays',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что делает функция array_is_list()?',
                'answer' => 'Появившаяся в PHP 8.1 функция возвращает true, если массив имеет последовательные целочисленные ключи начиная с нуля и без пропусков, то есть является списком. Она удобнее ручных проверок через array_keys() и используется при сериализации в JSON, где список и объект различаются.',
                'code_example' => '<?php
array_is_list([1, 2, 3]);                  // true
array_is_list([]);                          // true
array_is_list([0 => "a", 1 => "b"]);        // true
array_is_list([1 => "a", 2 => "b"]);        // false — не с 0
array_is_list(["a" => 1]);                  // false — строковые ключи
array_is_list([0 => "a", 2 => "b"]);        // false — пропуск

// json_encode серилизует list как [...], а dict как {...}
echo json_encode([1, 2, 3]);                // "[1,2,3]"
echo json_encode([0 => "a", 2 => "b"]);     // \'{"0":"a","2":"b"}\'',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.arrays',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работает деструктуризация массива со строковыми ключами?',
                'answer' => 'Синтаксис [\'name\' => $name, \'age\' => $age] = $person извлекает значения по ключам из ассоциативного массива в одноимённые переменные. Поддержка ключей появилась в PHP 7.1 и заменяет вызовы list() с явными именами, а отсутствующие ключи дают предупреждение и null.',
                'code_example' => '<?php
$person = ["name" => "Иван", "age" => 30, "email" => "i@i.ru"];

["name" => $name, "age" => $age] = $person;
echo $name; // "Иван"

// Вложенная деструктуризация
$data = ["user" => ["id" => 42, "name" => "Аня"]];
["user" => ["id" => $id, "name" => $name]] = $data;

// В foreach
$users = [["id" => 1, "name" => "A"], ["id" => 2, "name" => "B"]];
foreach ($users as ["id" => $id, "name" => $name]) {
    echo "$id: $name ";
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.arrays',
            ],
        ];
    }
}
