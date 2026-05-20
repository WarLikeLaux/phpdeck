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
                'answer' => 'DateTime - изменяемый объект даты, методы modify/add/sub МУТИРУЮТ объект. DateTimeImmutable - неизменяемый, методы возвращают НОВЫЙ объект. Всегда предпочитай Immutable - мутабельность дат причина множества багов. Форматирование через format(). Парсинг через createFromFormat. Разница через diff(). Часовые пояса через DateTimeZone.',
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
                'answer' => 'Сериализация - превращение PHP-объекта или структуры в строку, из которой потом можно восстановить. serialize() / unserialize() - бинарный PHP-формат, сохраняет тип. json_encode() / json_decode() - текстовый, межъязыковой. С PHP 7.4 есть __serialize / __unserialize - современная замена устаревших Serializable. ВАЖНО: unserialize небезопасен с недоверенными данными - может выполнить код через __wakeup/__destruct (POP-цепочки).',
                'code_example' => '<?php
class User {
    public function __construct(
        public string $name,
        private string $secret,
    ) {}

    public function __serialize(): array {
        return ["name" => $this->name];
    }

    public function __unserialize(array $data): void {
        $this->name = $data["name"];
        $this->secret = "";
    }
}

$user = new User("Иван", "pwd");
$str = serialize($user);

$user2 = unserialize($str);

// Безопасный режим
$obj = unserialize($str, ["allowed_classes" => [User::class]]);',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.std_lib',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое SPL и какие структуры из неё реально полезны на собеседованиях?',
                'answer' => 'Standard PHP Library предоставляет специализированные структуры данных и итераторы. SplQueue/SplStack/SplDoublyLinkedList - связные списки с O(1) на голову/хвост. SplPriorityQueue - куча. SplObjectStorage - set/map для объектов. SplFixedArray - массив с числовыми индексами и фиксированным размером; немного экономит память по сравнению с обычным array (~1.1-1.3x на PHP 8 для int/string-значений - замерено через memory_get_usage), а не в 3-5 раз, как часто пишут (это легенда из эпохи PHP 5, когда HashTable был тяжёлым; в PHP 7+ packed array хранится как сплошной блок и почти догоняет SplFixedArray). Реальная польза SplFixedArray сегодня - жёсткая фиксация размера и невозможность нечисловых ключей, а не радикальная экономия памяти. Итераторы (RecursiveIteratorIterator, FilterIterator) дают компонуемые потоки.',
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
