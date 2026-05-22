<?php

namespace Database\Seeders\Data\Categories\Php;

class FilesStreams
{
    public static function all(): array
    {
        return [
            [
                'category' => 'PHP',
                'question' => 'Как читать и писать файлы в PHP?',
                'answer' => 'Два уровня API: **«всё одной командой»** и **потоковый**.

**Простые функции (всё в одну строку):**
- **`file_get_contents($path)`** — читает **всё содержимое** в строку.
- **`file_put_contents($path, $data)`** — пишет строку в файл (перезаписывает).
- **`file($path)`** — читает файл в **массив строк** (одна строка = один элемент).

**Потоковый API (для больших файлов):**
- **`fopen($path, $mode)`** — открыть, режимы: `r`, `r+`, `w`, `a`, `x`, `b` (binary).
- **`fread($fh, $bytes)`** / **`fwrite($fh, $data)`** — блочное чтение/запись.
- **`fgets($fh)`** — **построчное** чтение.
- **`fclose($fh)`** — закрыть дескриптор.

**Флаги `file_put_contents`:**
- **`FILE_APPEND`** — дописать в конец, а не перезаписать.
- **`LOCK_EX`** — эксклюзивная блокировка от конкурентной записи (важно для логов).

**Правила:**
- **Большие файлы** (логи, CSV) — **`fopen` + `fgets`**, иначе OOM. Для совсем больших — **генератор + `yield`**.
- **`fclose`** в **`finally`** — иначе при исключении дескриптор течёт.
- Текст vs **binary** — на Windows без `"b"` режим конвертирует `\\n` ↔ `\\r\\n`.
- Сетевые URL (`http://`, `s3://`) работают только с включённым `allow_url_fopen`.',
                'code_example' => '<?php
// Простое чтение
$content = file_get_contents("file.txt");

// Простая запись
file_put_contents("file.txt", "data");

// Дописать с блокировкой
file_put_contents("log.txt", "line\\n", FILE_APPEND | LOCK_EX);

// Чтение в массив строк
$lines = file("file.txt", FILE_IGNORE_NEW_LINES);

// Большой файл построчно
$fh = fopen("big.log", "r");
while (($line = fgets($fh)) !== false) {
    if (str_contains($line, "ERROR")) {
        echo $line;
    }
}
fclose($fh);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.files_streams',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое stream wrappers и как использовать php://?',
                'answer' => '**Stream wrappers** — механизм PHP для работы с разными источниками данных через **единый файловый API** (`fopen`, `file_get_contents`, `fread`).

**Встроенные обёртки:**

| Wrapper | Что это | Когда |
| --- | --- | --- |
| **`file://`** | локальные файлы (default, можно опустить) | `fopen("/etc/passwd", "r")` |
| **`php://stdin`** / **`stdout`** / **`stderr`** | CLI-потоки | `fgets(STDIN)` или прямой `fopen` |
| **`php://input`** | **тело HTTP-запроса** | API получает JSON-payload |
| **`php://output`** | пишет в **ответ** напрямую | потоковая отдача больших файлов |
| **`php://memory`** | буфер **в памяти** | временный буфер для тестов |
| **`php://temp`** | буфер **в памяти, при >2 MB — на диск** | большие in-flight буферы |
| **`php://filter`** | цепочка фильтров поверх другого потока | base64, deflate, charset |
| **`http://`** / **`https://`** | HTTP(S) клиент | требует **`allow_url_fopen`** |
| **`ftp://`** / **`ftps://`** | FTP | |
| **`compress.zlib://`** | прозрачная gz-декомпрессия | `fopen("compress.zlib:///x.gz", "r")` |
| **`compress.bzip2://`** | bzip2 | |
| **`phar://`** | внутри PHP Archive | |
| **`glob://`** | iter-патчей по маске | `new DirectoryIterator("glob:///*.php")` |
| **`data://`** | data-URI inline | `fopen("data://text/plain,Hello", "r")` |

**Регистрация своих:**
- **`stream_wrapper_register(string $scheme, string $class)`** — класс реализует протокол (`stream_open`, `stream_read`, `stream_write`, `stream_eof`, `stream_close`, `url_stat`, ...)
- так делают **`vfsStream`** для тестов файловой системы, **AWS SDK `s3://`**, **Flysystem-обёртки**

**Stream context** — параметры обёртки:
- **`stream_context_create(["http" => [...]])`** — таймауты, заголовки, прокси
- **`stream_context_set_default(...)`** — глобальные

**Подводные камни:**
- **`allow_url_fopen=Off`** в проде по безопасности — **`http://`-обёртка отключена**
- **`allow_url_include=Off`** запрещает `include "http://..."` (LFI/RFI защита)
- **`file_get_contents("http://...")`** — простая, но **без** retries, redirects (поведение `follow_location=1` по умолчанию)',
                'code_example' => '<?php
// Тело POST-запроса
$body = file_get_contents("php://input");
$data = json_decode($body, true);

// В память (быстро)
$mem = fopen("php://memory", "r+");
fwrite($mem, "data");
rewind($mem);
echo stream_get_contents($mem);

// Чтение из stdin (CLI)
$line = trim(fgets(STDIN));

// HTTP с context
$context = stream_context_create([
    "http" => ["method" => "POST", "content" => "data"],
]);
$res = file_get_contents($url, false, $context);',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.files_streams',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем отличаются абсолютный и относительный путь к файлу в PHP?',
                'answer' => '- **Абсолютный** — от корня файловой системы: `/var/www/app/config.php` (Linux) или `C:\\app\\config.php` (Windows). Однозначен, не зависит от рабочей директории.
- **Относительный** — от **cwd** (current working directory): `config.php`, `./uploads/file.txt`. Какой файл откроется, зависит от того, **откуда запущен скрипт**. Узнать cwd — через `getcwd()`.

**Магическая константа `__DIR__`** — каталог того файла, где она написана. Чтобы построить абсолютный путь от **текущего файла** (а не от cwd):

```
require __DIR__ . "/../config.php";
```

Работает независимо от того, где запустили PHP.

**Правило:** для `include`/`require` и работы с ресурсами проекта **всегда используй `__DIR__`**.',
                'code_example' => '<?php
// /var/www/app/public/index.php
echo __DIR__;     // "/var/www/app/public"
echo __FILE__;    // "/var/www/app/public/index.php"
echo getcwd();    // зависит от того, откуда запустили скрипт!

// ❌ Сломается, если запустить php из другого каталога
require "config.php";
require "../config.php";

// ✅ Работает всегда — абсолютный путь от текущего файла
require __DIR__ . "/../config.php";',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.files_streams',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как проверить, что файл существует, перед чтением?',
                'answer' => '**`file_exists($path)`** — возвращает `true`, если файл или директория существуют.

**Более точные проверки:**
- `is_file($path)` — это именно **файл** (не директория)
- `is_dir($path)` — это **директория**
- `is_readable($path)` — есть права на **чтение**
- `is_writable($path)` — есть права на **запись**

**Нюанс:** все эти функции **кэшируют** результат внутри одного запроса. Для пере-проверки после изменений на диске — `clearstatcache()`.',
                'code_example' => '<?php
$path = "/var/log/app.log";
if (file_exists($path) && is_readable($path)) {
    $content = file_get_contents($path);
} else {
    throw new RuntimeException("файл недоступен: $path");
}',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.files_streams',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как в PHP получить тело HTTP-запроса (raw body)?',
                'answer' => 'Через стрим **`php://input`**: `$raw = file_get_contents("php://input")` — сырое, ещё не распарсенное тело запроса.

**Что важно знать:**
- работает **для любого Content-Type**, КРОМЕ `multipart/form-data` (там тело уже разобрано в `$_POST` и `$_FILES`, `php://input` пуст)
- **`$_POST` автоматически парсится** только для `application/x-www-form-urlencoded` и `multipart/form-data`
- для **`application/json`** (типовой API) `$_POST = []`, нужно читать `php://input` и делать **`json_decode($raw, true)`**
- размер тела ограничен **`post_max_size`** в `php.ini` (с PHP 8.1+ — глобальный лимит)

**В фреймворках:**
- **Laravel** — `$request->getContent()` или `$request->json()->all()`
- **Symfony** — `$request->getContent()`
- **PSR-7** — `$request->getBody()->getContents()`

Все они в итоге читают тот же `php://input`.',
                'code_example' => '<?php
// API получает JSON
$raw = file_get_contents("php://input");
$data = json_decode($raw, true, flags: JSON_THROW_ON_ERROR);

// Laravel/Symfony
$body = $request->getContent();    // строка
$json = $request->json()->all();   // уже массив, Laravel

// PSR-7
$body = (string) $request->getBody();',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.files_streams',
            ],
        ];
    }
}
