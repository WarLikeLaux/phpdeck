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
                'answer' => 'yield - ключевое слово, превращающее функцию в генератор. Простыми словами: вместо того чтобы построить весь массив в памяти и вернуть, генератор отдаёт значения по одному, "лениво". Огромная экономия памяти при работе с большими наборами данных. Между вызовами генератор сохраняет состояние. Можно yield-ить ключ => значение. yield from - делегирует другому генератору или iterable.',
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
                'answer' => 'yield from делегирует итерацию другому iterable (генератору, массиву, Traversable): родительский генератор пробрасывает все значения дочернего, как будто это его собственные. Главный нюанс - yield from возвращает результат вложенного генератора. Если внутри generator-функции есть return $value (без операнда тоже допустимо), то это НЕ обычный return массива (генератор всегда возвращает Generator-объект), а финальное значение, доступное через $generator->getReturn() ПОСЛЕ окончания итерации. Это позволяет писать рекурсивные генераторы, которые накапливают результат, и компонуемые корутины. Если вызвать getReturn() до завершения генератора - Exception. Если генератор не вернул значения через return - вернётся null.',
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
                'answer' => 'Классический Senior-вопрос на проверку понимания стримов и генераторов. Решение НЕ должно загружать весь файл в память. Что НЕ работает: file() / file_get_contents() - построят массив/строку на 10 ГБ, OOM. str_getcsv от всего файла - то же самое. Что РАБОТАЕТ построчно: 1) fopen() + fgetcsv() в цикле while - читает по одной строке через буфер ОС (~8KB), возвращает массив колонок. 2) SplFileObject сам реализует Iterator (RecursiveIterator/SeekableIterator) и читает построчно через fgets, foreach по нему НЕ грузит файл в память; для CSV есть SplFileObject::READ_CSV / setCsvControl. 3) yield нужен НЕ для экономии памяти на чтении (fopen и SplFileObject и так ленивые), а чтобы обернуть чтение в свой переиспользуемый ленивый API: generator-функция отдаёт строки одну за другой, вызывающий код просто foreach-ит результат, не зная про fopen. 4) Обрабатываем построчно, ничего не накапливая в массиве. Для записи результата - тоже стрим: fopen + fputcsv или прямо в БД через batch-вставку (1000 строк за раз с unset() и gc_collect_cycles между батчами). Дополнительные приёмы: stream_filter_append для on-the-fly декомпрессии (gzip), wrappers (php://memory только для маленьких данных, php://temp - переключается на диск выше 2 МБ). Память при таком подходе - O(1), по сути только размер одной строки + буфер ОС, на 10 ГБ файле занято 5-10 МБ независимо от размера. Время линейное от размера файла, упирается в дисковый IO. На проде помните, что fgetcsv может вернуть массив с числом колонок, не равным числу заголовков (битая или обрезанная строка - частый случай в гигабайтных дампах): array_combine($header, $row) в этом случае выдаст ValueError (PHP 8.0+) или Warning + false (до PHP 8.0) и сломает обход. Поэтому перед array_combine проверяйте count($row) === count($header), а битые строки складывайте в отдельный лог или dead-letter.',
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
                'answer' => 'Метод $generator->throw(Throwable $e) внедряет исключение ВНУТРЬ генератора в точке, где он сейчас приостановлен (последний yield). С точки зрения кода генератора это выглядит так, как будто исключение было выброшено прямо в строчке yield - его можно поймать через try/catch вокруг yield, а если не поймать - оно вылетит наружу из throw() обратно в вызывающий код. Это парный механизм к send($value) (который как бы "возвращает" значение из yield) и используется в построении async-runtimes: event loop вызывает throw() в корутину, чтобы сообщить ей о неуспехе IO-операции (TimeoutException, ConnectionResetException). Корутина может обработать ошибку через try/catch вокруг yield и продолжить работу - например, попробовать другой URL, сделать fallback. В обычном коде применяется редко; основные пользователи - amphp/promise, ReactPHP, любые библиотеки коопертивной многозадачности на yield-based корутинах. Без throw() невозможно было бы пробросить ошибку IO в код, который её ожидает.',
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
        ];
    }
}
