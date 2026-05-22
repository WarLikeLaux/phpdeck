<?php

namespace Database\Seeders\Data\Categories\Php;

class Generators
{
    public static function all(): array
    {
        return [
            [
                'category' => 'PHP',
                'question' => 'Что такое yield и для чего он нужен?',
                'answer' => '**`yield`** превращает функцию в **генератор**: вместо того чтобы построить весь массив в памяти и вернуть, генератор **отдаёт значения по одному**, лениво (lazy).

**Что это даёт:**
- **Экономия памяти** — обрабатываешь миллион строк без построения массива из миллиона.
- **Сохраняется состояние** между `yield` — локальные переменные живут, как «пауза».
- **Объединяется с `foreach`** — функция-генератор используется так же, как обычный iterable.

**Возможности `yield`:**
- `yield $value` — отдать значение.
- `yield $key => $value` — отдать пару ключ-значение.
- **`yield from`** — делегировать другому iterable (генератору, массиву, `Traversable`).
- `$x = yield $value` — принять данные обратно через **`Generator::send()`** (корутины).

**Подвох:**
- Генератор **одноразовый**: пройти второй раз — заново вызвать функцию.
- Возвращаемый тип в сигнатуре — **`Generator`** (или `iterable`), не `array`.
- Внутри генератора уже **не вернёшь массив** через `return $arr` — `return` имеет особое значение (`getReturn()`), без значения — просто завершить.

**Типовые применения:**
- чтение **больших файлов** построчно.
- стрим из **БД** (Eloquent `cursor()` → генератор).
- **бесконечные** последовательности (range, числа Фибоначчи).
- корутины / event-loop (ReactPHP, Amp).',
                'code_example' => '<?php
function range_gen(int $start, int $end) {
    for ($i = $start; $i <= $end; $i++) {
        yield $i;
    }
}

// Не строит массив на 1млн элементов
foreach (range_gen(1, 1_000_000) as $num) {
    if ($num > 5) break;
    echo $num;
}

// Чтение большого файла построчно
function readLines(string $file) {
    $fh = fopen($file, "r");
    while (($line = fgets($fh)) !== false) {
        yield $line;
    }
    fclose($fh);
}

foreach (readLines("huge.log") as $line) {
    if (str_contains($line, "ERROR")) echo $line;
}

// yield from
function combined() {
    yield 1;
    yield from [2, 3, 4];
    yield from range_gen(5, 7);
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.generators',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что делает yield from и как получить return-значение из генератора через getReturn()?',
                'answer' => '**`yield from $iterable`** — **делегирует** итерацию другому iterable (генератор, массив, **`Traversable`**). Родительский генератор пробрасывает **все значения** дочернего, как будто это его собственные.

**Главный нюанс: возвращаемое значение.**
- `yield from $gen` **является выражением**, возвращающим **результат** дочернего генератора
- внутри generator-функции **`return $value`** — это **НЕ** возврат массива (генератор всегда возвращает `Generator`-объект)
- это **финальное значение**, доступное через **`$gen->getReturn()`** **ПОСЛЕ** окончания итерации

**Правила `getReturn()`:**

| Состояние | `getReturn()` |
| --- | --- |
| Генератор **завершён** через `return $value` | `$value` |
| Генератор **завершён** без `return` | `null` |
| Генератор **ещё не завершён** | **`Exception: Cannot get return value of a generator that hasn\\\'t returned`** |

**Что это даёт:**

- **Рекурсивные генераторы**, накапливающие итог (обход дерева + общая сумма)
- **Компонуемые корутины** — родитель использует результат вложенной
- **Делегация** в async-фреймворках (AMPHP до Fibers строили `await` через `yield from`)

**Подкапотные нюансы:**
- `yield from $array` тоже работает — массив обходится как итератор
- **ключи сохраняются**: если у дочернего числовые ключи, родитель **переиспользует их** — типичная ловушка при `yield from` двух массивов с пересекающимися ключами (поздние затрут ранние)
- `$gen->send()` и `$gen->throw()` **прозрачно** проходят через `yield from` в дочерний генератор
- `Generator::send` принимает значение, возвращаемое из текущего `yield`',
                'code_example' => '<?php
// Вложенный генератор + getReturn
function inner(): Generator
{
    yield 1;
    yield 2;
    return "done"; // финальное значение
}

function outer(): Generator
{
    yield 0;
    $result = yield from inner(); // 1, 2 пробрасываются наружу
    yield "inner result: $result";
}

foreach (outer() as $v) echo $v . " "; // 0 1 2 inner result: done

// getReturn после завершения
$gen = inner();
foreach ($gen as $v) { /* итерируем до конца */ }
echo $gen->getReturn(); // "done"

// Рекурсивный обход дерева
function walk(array $node): Generator
{
    yield $node["name"];
    foreach ($node["children"] ?? [] as $child) {
        yield from walk($child);
    }
}

// Корутина-аккумулятор
function sum(): Generator
{
    $total = 0;
    while (($x = yield) !== null) $total += $x;
    return $total;
}

$g = sum();
$g->current(); // запуск
$g->send(10);
$g->send(20);
$g->send(null); // завершить
echo $g->getReturn(); // 30',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.generators',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как обработать CSV-файл на 10 ГБ на сервере с 512 МБ RAM?',
                'answer' => '**Классический senior-вопрос** на понимание стримов и генераторов. Решение **не должно загружать весь файл в память**.

**Что НЕ работает:**

| Подход | Почему |
| --- | --- |
| **`file()`** | вернёт массив из 100 млн строк → OOM |
| **`file_get_contents()`** | строка 10 ГБ → OOM сразу |
| **`str_getcsv($whole_file)`** | то же самое |

**Что РАБОТАЕТ построчно:**

**1. `fopen()` + `fgetcsv()`** в цикле:
- читает по одной строке через **буфер ОС** (~8 KB)
- возвращает массив колонок

**2. `SplFileObject`** с флагом **`READ_CSV`**:
- реализует **`Iterator`** + **`SeekableIterator`** — работает в `foreach`
- `setCsvControl($delimiter, $enclosure, $escape)` для нестандартных CSV

**3. Generator-обёртка с `yield`:**
- **не для экономии памяти** на чтении (`fopen` и так ленивый)
- для **переиспользуемого ленивого API** — вызывающий код просто `foreach`-ит, не зная про `fopen`

**Запись результата — тоже стрим:**
- `fopen + fputcsv` для другого файла
- или **batch-вставка** в БД (1000 строк за раз) с `unset()` и `gc_collect_cycles()` между батчами

**Полезные приёмы:**

| Wrapper / приём | Где помогает |
| --- | --- |
| **`compress.zlib://`** | прозрачная декомпрессия `gzip` |
| **`stream_filter_append`** | on-the-fly преобразования (cp1251 → utf-8) |
| **`php://temp`** | переключается на диск выше 2 МБ — для small-medium буферов |
| **`php://memory`** | только для маленьких данных |

**Подводный камень — битые строки:**
- `fgetcsv` может вернуть массив с **числом колонок ≠ числу заголовков** (частый случай в гигабайтных дампах)
- **`array_combine($header, $row)`** → **`ValueError`** (PHP 8.0+) или `Warning + false` (до 8.0)
- проверять `count($row) === count($header)`, битые строки в **dead-letter** лог

**Профиль выполнения:**
- **память O(1)** — ~5-10 MB на любом размере файла
- **время линейное** от размера, упирается в **disk IO**',
                'code_example' => '<?php
function readCsv(string $path): Generator
{
    $fh = fopen($path, "r");
    if ($fh === false) throw new RuntimeException("cannot open $path");

    try {
        $header = fgetcsv($fh); // первая строка - заголовки
        if ($header === false) return;

        while (($row = fgetcsv($fh)) !== false) {
            // ассоциативная строка: ["email" => "...", "name" => "..."]
            yield array_combine($header, $row);
        }
    } finally {
        fclose($fh); // даже при exception
    }
}

// Обработка 10 ГБ файла, batch-вставка по 1000 строк
$batch = [];
foreach (readCsv("/data/users-10gb.csv") as $row) {
    $batch[] = ["email" => $row["email"], "name" => $row["name"]];

    if (count($batch) >= 1000) {
        DB::table("users")->insert($batch);
        $batch = [];               // освободить память
        gc_collect_cycles();       // принудительно
    }
}
if ($batch) DB::table("users")->insert($batch);

// Если файл сжат - прозрачная декомпрессия
$gz = fopen("compress.zlib:///data/big.csv.gz", "r");
while (($row = fgetcsv($gz)) !== false) { /* ... */ }

// Память на 10 ГБ файле:
echo memory_get_peak_usage(true) / 1024 / 1024; // ~6 MB',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.generators',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что делает Generator::throw() и для чего он применяется?',
                'answer' => '**`$generator->throw(Throwable $e)`** — **внедряет исключение** внутрь генератора в точке, где он сейчас приостановлен (последний `yield`).

**С точки зрения кода генератора:**
- выглядит так, **как будто** исключение было выброшено прямо в строчке `yield`
- его можно **поймать через `try/catch` вокруг `yield`**
- если не поймать — вылетит **наружу** из `throw()` обратно в вызывающий код

**Парный механизм к `send()`:**

| Метод | Что делает |
| --- | --- |
| **`current()`** | получить значение текущего `yield` |
| **`send($value)`** | возобновить + «вернуть» значение из `yield` |
| **`throw(Throwable $e)`** | возобновить + **бросить** исключение в точке `yield` |
| **`next()`** | возобновить + вернуть `null` из `yield` |

**Где реально используется — async-runtimes:**
- **event loop** вызывает `throw()` в корутину, чтобы сообщить ей о неуспехе IO-операции (**`TimeoutException`**, **`ConnectionResetException`**)
- корутина обрабатывает ошибку через `try/catch` вокруг `yield` и **продолжает работу** — попробовать другой URL, fallback
- **amphp/promise** (до Fibers), **ReactPHP** до async/await, любые библиотеки кооперативной многозадачности на yield-based корутинах

**Без `throw()`** невозможно было бы пробросить ошибку IO в код, который её ожидает — корутина «висела» бы вечно или получала бы `null` из `send`, не зная, что произошёл сбой.

**После PHP 8.1 / Fibers:** для нового кода **Fiber** удобнее, потому что:
- не путает iterator-семантику с control-flow
- `Fiber::suspend()` симметричен `resume()` / `throw()`
- работает в **любом** месте, не только в generator-функциях

**`Generator::throw()`** остаётся актуален для **существующего** yield-based кода и для случаев, где iterator-фасад нужен сам по себе.',
                'code_example' => '<?php
function fetchOrFallback(): Generator
{
    try {
        $body = yield $primaryFetch;     // ждём результата
    } catch (TimeoutException $e) {
        // throw() из event loop материализуется здесь
        $body = yield $fallbackFetch;
    }
    return strlen($body);
}

$gen = fetchOrFallback();
$gen->current();             // запускаем до первого yield (отдаёт $primaryFetch)

// Симулируем неудачу таймаута: event loop сообщает корутине
$gen->throw(new TimeoutException("primary timed out"));
// внутри генератора это выглядит как throw в строчке yield;
// catch (TimeoutException) ловит, и yield $fallbackFetch отдаётся наружу

$gen->send($responseBody);   // event loop отдаёт удачный fallback-результат
echo $gen->getReturn();      // длина

// Если throw не поймать - вылетит наружу
$gen2 = simpleCoroutine();
$gen2->current();
try {
    $gen2->throw(new RuntimeException("oops"));
} catch (RuntimeException $e) {
    echo "пробросилось обратно";
}',
                'code_language' => 'php',
                'difficulty' => 5,
                'topic' => 'php.generators',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое Fiber в PHP 8.1 и чем он отличается от generator и от корутины Go?',
                'answer' => '**Fiber (PHP 8.1+)** — примитив **пользовательских стеков**: можно **приостановить** (`Fiber::suspend()`) и **возобновить** (`$fiber->resume()`) выполнение в **произвольной точке**, не только на `yield`.

**Сравнение с Generator:**

| | **Generator** | **Fiber** |
| --- | --- | --- |
| Версия | PHP 5.5+ | **PHP 8.1+** |
| Точка приостановки | **только `yield`** | **`Fiber::suspend()`** в любом месте |
| Семантика | iterator | **co-routine** |
| API | `current`/`send`/`throw`/`next` | `start`/`resume`/`throw`/`getReturn` |
| Используется в `foreach` | да | **нет** |
| Можно ли создать **внутри** функции и приостановить **глубоко вложенный** вызов | **нет** (`yield` только в той же функции) | **да** (suspend работает через стек вызовов) |

**Главное отличие от Generator:**
- generator может «паузить» **только сам себя**
- fiber может паузить **из любого вложенного вызова** — поэтому годится для **прозрачного await**

**Сравнение с горутинами Go:**

| | **Go goroutine** | **PHP Fiber** |
| --- | --- | --- |
| Конкуррентность | да | да |
| **Параллелизм** (несколько ядер) | **да** | **нет** — single-threaded |
| Планировщик | **в рантайме** Go | **нет в PHP** — пишет фреймворк (ReactPHP, AMPHP) |
| Сообщения | каналы (`chan`) | руками через очереди/promises |
| Стоимость создания | ~2 KB stack | заметно дороже (полный zend stack) |

**Где используется на практике:**
- **AMPHP v3** — построил `await` поверх Fibers
- **ReactPHP** — добавили fiber-сопрягаемые методы
- **Symfony Mailer** / **HttpClient** — асинхронные запросы

**Что Fiber НЕ даёт:**
- **параллельность** — PHP по-прежнему **однопоточный** в рамках одного процесса
- **превентивный шедулинг** — fiber **сам** должен вызвать `suspend`; **`while(true) {}`** заблокирует весь event loop
- автоматического подбора корутин — поверх Fiber нужен **runtime** (AMPHP, ReactPHP)',
                'code_example' => '<?php
// Базовый цикл: start → suspend → resume → suspend → ...

$fiber = new Fiber(function (): string {
    echo "1. start\n";
    $x = Fiber::suspend("ready");      // вернёт значение из resume()
    echo "2. resumed with: $x\n";
    $y = Fiber::suspend("more");
    echo "3. resumed with: $y\n";
    return "done";                     // итог
});

$msg = $fiber->start();                // выполнит до первого suspend
echo "main got: $msg\n";               // "ready"

$msg = $fiber->resume("hello");        // suspend вернёт "hello"
echo "main got: $msg\n";               // "more"

$msg = $fiber->resume("world");
var_dump($fiber->isTerminated());      // true
echo $fiber->getReturn();              // "done"

// Прокинуть исключение в точку suspend
$fiber2 = new Fiber(function () {
    try {
        Fiber::suspend();
    } catch (RuntimeException $e) {
        echo "caught: " . $e->getMessage();
    }
});
$fiber2->start();
$fiber2->throw(new RuntimeException("oops")); // "caught: oops"',
                'code_language' => 'php',
                'difficulty' => 5,
                'topic' => 'php.generators',
            ],
        ];
    }
}
