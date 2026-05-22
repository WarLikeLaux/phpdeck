<?php

namespace Database\Seeders\Data\Categories\Php;

class StdLib
{
    public static function all(): array
    {
        return [
            [
                'category' => 'PHP',
                'question' => 'Как работать с DateTime и DateTimeImmutable?',
                'answer' => '**`DateTime` vs `DateTimeImmutable`:**

| Аспект | **`DateTime`** | **`DateTimeImmutable`** |
|---|---|---|
| `modify` / `add` / `sub` | **мутирует объект** | **возвращает новый** |
| `setDate` / `setTime` / `setTimezone` | мутирует | возвращает новый |
| Безопасно передавать в функцию | нет (могут изменить) | **да** |
| Когда брать | почти **никогда** | **по умолчанию** |

**Правило:** **всегда `DateTimeImmutable`** — мутабельные даты типичный источник багов (передал в функцию, она `modify("+1 day")`, у тебя «время улетело»).

**Базовое API (общее для обоих):**
- **`format("Y-m-d H:i:s")`** — форматирование, символы как в `date()`.
- **`createFromFormat($fmt, $str)`** — парсинг строки по шаблону.
- **`diff($other)`** — возвращает `DateInterval` с `days`, `h`, `i`, `invert`.
- **`getTimestamp()`** / **`setTimestamp()`** — Unix-секунды.

**Часовые пояса:**
- `new DateTimeZone("Europe/Moscow")` → передать в конструктор.
- `setTimezone($tz)` — конвертация **без сдвига точки на оси времени** (то же мгновение в другом TZ).
- Для **`timestamp`** часовой пояс не важен — он всегда **UTC**.

**Подводные камни:**
- `format("u")` — микросекунды, требует ввода с микросекундами (иначе всегда `000000`).
- В PHP 8.4+ — **`DateTime::createFromTimestamp()`** прямой статический фабричный метод.
- Сравнение через `<=>` работает корректно для обоих типов.
- **В Laravel** — `Carbon` (наследник `DateTime`), `CarbonImmutable` — обёртка с fluent-API.',
                'code_example' => '<?php
$dt = new DateTime("2026-05-01");
$dt->modify("+1 day");
echo $dt->format("Y-m-d"); // 2026-05-02 - изменился!

$dti = new DateTimeImmutable("2026-05-01");
$dti2 = $dti->modify("+1 day");
echo $dti->format("Y-m-d");  // 2026-05-01
echo $dti2->format("Y-m-d"); // 2026-05-02

// Парсинг
$dt = DateTimeImmutable::createFromFormat("d.m.Y", "01.05.2026");

// Разница
$diff = $dti->diff($dti2);
echo $diff->days; // 1

// Часовой пояс
$tz = new DateTimeZone("Europe/Moscow");
$dt = new DateTimeImmutable("now", $tz);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.std_lib',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работать с JSON в PHP?',
                'answer' => '**Две основные функции:**
- **`json_encode($data)`** — PHP-структура → JSON-строка
- **`json_decode($json)`** — JSON → PHP-структура

**Важный нюанс `json_decode`:** по умолчанию возвращает **`stdClass`**, передай **`true`** вторым аргументом для **ассоциативного массива** — обычно нужен именно массив.

**Полезные флаги:**
- **`JSON_THROW_ON_ERROR`** (PHP 7.3+) — вместо тихого `null`/`false` выбрасывает **`JsonException`** (лучшая практика)
- **`JSON_UNESCAPED_UNICODE`** — не экранировать кириллицу (читаемее)
- **`JSON_PRETTY_PRINT`** — форматирование с переносами
- **`JSON_UNESCAPED_SLASHES`** — не экранировать `/`

**Обработка ошибок:** до 7.3 проверяли через `json_last_error()`, сейчас — через `try/catch JsonException`.',
                'code_example' => '<?php
$data = ["name" => "Иван", "age" => 30];

$json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

// Парсинг в массив
$arr = json_decode($json, true);

// Парсинг в объект
$obj = json_decode($json);
echo $obj->name;

// С исключением
try {
    $data = json_decode($invalid, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
    echo $e->getMessage();
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.std_lib',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое сериализация в PHP?',
                'answer' => '**Сериализация** — превращение PHP-объекта или структуры в **строку**, из которой потом можно **восстановить** значение.

**Два основных формата:**

| Формат | Функции | Особенности |
| --- | --- | --- |
| **PHP serialize** | `serialize()` / `unserialize()` | **бинарный**, сохраняет **тип и класс**, размер компактный, только PHP-to-PHP |
| **JSON** | `json_encode()` / `json_decode()` | **текстовый**, **межъязыковой**, теряет тип объекта (восстановит как `array` или `stdClass`) |

**Магические методы для контроля сериализации:**

| Метод | Когда вызывается | Что должен делать |
| --- | --- | --- |
| **`__serialize(): array`** (PHP 7.4+) | при `serialize($obj)` | вернуть массив — то, что будет сериализовано |
| **`__unserialize(array $data): void`** (PHP 7.4+) | при `unserialize($str)` | восстановить состояние |
| `__sleep()` / `__wakeup()` | устаревшие — оставлены для совместимости |
| **`Serializable`-интерфейс** | **deprecated с PHP 8.1**, удалён в 9.0 | использовать `__serialize` / `__unserialize` |

**⚠️ КРИТИЧНО — `unserialize` небезопасен с НЕдоверенными данными:**
- может вызвать **`__wakeup()`**, **`__destruct()`**, **`__toString()`** на произвольных классах
- атакующий конструирует **POP-цепочку** (Property-Oriented Programming) — комбинирует магические методы существующих классов для **RCE**
- классический пример — **уязвимости в Laravel / Symfony / WordPress** при `unserialize($_COOKIE["user"])`

**Защита:**
- **`allowed_classes`** в опциях: `unserialize($str, ["allowed_classes" => [User::class]])` — whitelist
- **`allowed_classes => false`** — запретить **все** классы, только скалары/массивы
- **никогда** не `unserialize` пользовательский ввод — использовать **JSON** + явная валидация
- альтернатива — **подписанный токен** (`hash_hmac`) поверх serialize, проверять подпись до `unserialize`',
                'code_example' => '<?php
class User {
    public function __construct(
        public string $name,
        private string $secret,
    ) {}

    // Контролируем, что попадёт в сериализованную строку
    public function __serialize(): array {
        return ["name" => $this->name];  // secret НЕ серилизуем
    }

    public function __unserialize(array $data): void {
        $this->name = $data["name"];
        $this->secret = "";              // пересоздадим в безопасное состояние
    }
}

$user = new User("Иван", "pwd");
$str = serialize($user);
// O:4:"User":1:{s:4:"name";s:8:"Иван";}

$user2 = unserialize($str);

// ✅ БЕЗОПАСНО — whitelist классов
$obj = unserialize($str, ["allowed_classes" => [User::class]]);

// ✅ Совсем без объектов
$data = unserialize($str, ["allowed_classes" => false]);
// массивы/скаляры останутся, объекты станут __PHP_Incomplete_Class

// ❌ Опасно — данные из вне приложения
$data = unserialize($_COOKIE["state"]);  // RCE-уязвимость',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.std_lib',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое SPL и какие структуры из неё реально полезны на собеседованиях?',
                'answer' => '**SPL (Standard PHP Library)** — встроенное расширение со специализированными **структурами данных** и **итераторами**.

**Структуры данных:**

| Класс | Что это | Сложность |
| --- | --- | --- |
| **`SplDoublyLinkedList`** | двусвязный список (deque) | `push`/`pop`/`shift`/`unshift` — **O(1)** |
| **`SplStack`** extends DoublyLinkedList | LIFO | `push`/`pop` — O(1) |
| **`SplQueue`** extends DoublyLinkedList | FIFO | `enqueue`/`dequeue` — **O(1)** (vs `array_shift` O(N)) |
| **`SplPriorityQueue`** | мин/макс-куча | `insert` — O(log N), `extract` — O(log N) |
| **`SplHeap`** (`SplMinHeap` / `SplMaxHeap`) | абстрактная куча | то же |
| **`SplObjectStorage`** | map/set с **объектами** в качестве ключей | hash через `spl_object_hash` |
| **`SplFixedArray`** | массив фиксированного размера, только int-ключи | компактнее обычного array (миф об «в 5× меньше» сегодня устарел) |

**Про `SplFixedArray` — частая ловушка собеса:**
- легенда «экономит в 3-5×» — **из эпохи PHP 5**, когда HashTable был тяжёлым
- в **PHP 7+** packed array хранится **сплошным блоком**, разница реально **~1.1-1.3×**
- сегодняшняя польза — **жёсткая фиксация размера** и **невозможность нечисловых ключей**, а не радикальная экономия памяти

**Итераторы (компонуемые потоки):**

| Итератор | Что делает |
| --- | --- |
| **`ArrayIterator`** | обёртка над массивом → `Iterator` |
| **`FilterIterator`** | фильтрация по callback |
| **`LimitIterator`** | offset + limit (как SQL `LIMIT`) |
| **`RecursiveIteratorIterator`** | плоский обход дерева |
| **`RecursiveDirectoryIterator`** | рекурсивный обход файловой системы |
| **`AppendIterator`** | конкатенация нескольких итераторов |
| **`CallbackFilterIterator`** | filter с замыканием |

**Где используется реально:**
- **`SplPriorityQueue`** — реализация Dijkstra, шедулеры задач
- **`SplObjectStorage`** — visitor-паттерн, отслеживание состояния объектов
- **`SplQueue`** — in-memory очередь между корутинами
- **`RecursiveDirectoryIterator` + `RecursiveIteratorIterator`** — обход проектов в Composer-сканерах, тестовых рунерах',
                'difficulty' => 4,
                'topic' => 'php.std_lib',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как прочитать файл целиком в строку в PHP?',
                'answer' => '**`file_get_contents($path)`** — простейший способ. Читает весь файл в строку и возвращает её (или `false` при ошибке + Warning).

**Зеркальная функция для записи:** `file_put_contents($path, $data)` — создаёт или перезаписывает файл.

**Подводный камень:** для **больших файлов** так делать нельзя — всё содержимое попадёт в память. Читают **потоково** через `fopen` + `fgets`/`fread` в цикле и закрывают через `fclose`.',
                'code_example' => '<?php
$content = file_get_contents("config.json");
if ($content === false) {
    throw new RuntimeException("Не удалось прочитать файл");
}

file_put_contents("out.txt", $content);

// Большой файл - построчно
$fh = fopen("big.log", "r");
while (($line = fgets($fh)) !== false) {
    // обработать $line
}
fclose($fh);',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.std_lib',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что делает fopen и какие основные режимы?',
                'answer' => '**`fopen($path, $mode)`** открывает файл (или stream-обёртку: `php://input`, `php://memory`, `https://`) и возвращает **дескриптор-resource**.

**Основные режимы:**
- **`"r"`** — чтение с начала
- **`"w"`** — запись, **СТИРАЕТ** файл (или создаёт)
- **`"a"`** — дозапись **в конец** (append)
- **`"x"`** — создать новый, **упасть** если уже есть

**Модификаторы:**
- **`"+"`** — чтение + запись (`"r+"`, `"w+"`, `"a+"`)
- **`"b"`** — бинарный режим (**всегда указывайте на Windows** — иначе ломает `\r\n`)

**Правила работы:**
- **обязательно `fclose($fh)`** в конце — лучше через **`try/finally`**
- для разовых задач удобнее **`file_get_contents`** / **`file_put_contents`** (сами открывают и закрывают)
- для больших файлов — `fopen` + `fgets`/`fread` в цикле',
                'code_example' => '<?php
$fh = fopen("data.log", "a");        // открыли на дозапись
try {
    fwrite($fh, "line\n");
    rewind($fh);                     // указатель в начало (для "+" режимов)
} finally {
    fclose($fh);                     // ВСЕГДА закрываем
}

// Построчное чтение большого файла
$fh = fopen("big.log", "r");
while (($line = fgets($fh)) !== false) {
    process($line);
}
fclose($fh);',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.std_lib',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работать с датой в PHP простыми словами?',
                'answer' => '**Простые задачи — функции:**
- `date("Y-m-d H:i:s")` — форматирует текущее время
- `time()` — Unix timestamp (секунды с `1970-01-01`)
- `strtotime("+1 day")` — разбирает человекочитаемую строку в timestamp

**Серьёзные задачи (часовые пояса, арифметика, immutability) — классы:**
- `DateTime` — изменяемый
- **`DateTimeImmutable`** — неизменяемый, **предпочтительный** (у `DateTime` методы мутируют объект — источник багов)

**В Laravel** поверх стандартных классов используется **`Carbon`** с удобным API.',
                'code_example' => '<?php
echo date("Y-m-d");              // "2026-05-19"
echo time();                     // 1747...
echo date("Y-m-d", strtotime("+1 week"));

$dt = new DateTimeImmutable("2026-05-01");
$next = $dt->modify("+1 day");
echo $next->format("Y-m-d");     // "2026-05-02"
echo $dt->format("Y-m-d");       // "2026-05-01" (не изменился)',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.std_lib',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что делают json_encode и json_decode?',
                'answer' => '- **`json_encode($data)`** — превращает PHP-массив/объект в **JSON-строку**.
- **`json_decode($json, true)`** — обратное преобразование. Второй аргумент `true` даёт **массив**, без него (или `false`) — объект `stdClass`.

**Подводный камень:** по умолчанию при ошибке `json_decode` возвращает `null` — легко пропустить. Решение — флаг **`JSON_THROW_ON_ERROR`** (PHP 7.3+): невалидный JSON выбросит `JsonException`.

**Полезные флаги для encode:**
- `JSON_UNESCAPED_UNICODE` — не экранировать кириллицу
- `JSON_PRETTY_PRINT` — форматирование с переносами
- `JSON_UNESCAPED_SLASHES` — не экранировать `/`',
                'code_example' => '<?php
$data = ["name" => "Иван", "age" => 30];

$json = json_encode($data, JSON_UNESCAPED_UNICODE);
// {"name":"Иван","age":30}

$arr = json_decode($json, true);
echo $arr["name"]; // "Иван"

try {
    json_decode("{kaput}", true, flags: JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
    echo "битый JSON";
}',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.std_lib',
            ],
            [
                'category' => 'PHP',
                'question' => 'Какие функции в PHP применяют для математики простыми словами?',
                'answer' => '**Базовые:**
- `abs($n)` — модуль (`abs(-5) === 5`)
- `round($n, $precision)` — округление, `floor` / `ceil` — вниз / вверх
- `min(...$args)` и `max(...$args)` — минимум и максимум (принимают и список аргументов, и массив)
- `pow($base, $exp)` или `**` — возведение в степень
- `sqrt($n)` — квадратный корень
- `intval` / `floatval` — приведение к числу

**Случайные числа:**
- `rand` / `mt_rand` — общего назначения
- **`random_int`** / **`random_bytes`** — **криптостойкие** (для токенов, паролей)',
                'code_example' => '<?php
echo abs(-5);          // 5
echo round(3.7);       // 4
echo round(3.14159, 2);// 3.14
echo max(1, 5, 3);     // 5
echo min([4, 2, 7]);   // 2
echo sqrt(16);         // 4',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.std_lib',
            ],
        ];
    }
}
