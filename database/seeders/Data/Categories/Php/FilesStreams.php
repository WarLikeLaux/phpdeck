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
                'answer' => 'Простые функции: file_get_contents (всё в строку), file_put_contents (записать). file() - читает в массив строк. fopen/fread/fwrite/fclose - для потоковой работы. fgets - построчно. file_put_contents с FILE_APPEND - дописывает. LOCK_EX - блокировка от конкурентной записи. Для больших файлов используй fopen + fgets, чтобы не загружать всё в память; в реальном коде оборачивай в try/finally для гарантированного fclose даже при исключении.',
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
                'answer' => 'Stream wrappers - механизм PHP для работы с разными источниками данных через единый файловый API (fopen, file_get_contents). Встроенные: php://stdin, php://stdout, php://memory (в памяти), php://temp (диск, если переполнило), php://input (тело запроса), php://output. file:// - локальные файлы (по умолчанию). http://, https://, ftp:// - сеть. Можно регистрировать свои через stream_wrapper_register.',
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
                'answer' => 'Через стрим php://input: $raw = file_get_contents("php://input") или построчно через fopen("php://input","r"). Это сырое, ещё не распарсенное тело запроса. Что важно знать: 1) Работает для любого Content-Type, кроме multipart/form-data — там тело уже разобрано в $_POST и $_FILES, php://input будет пустым. 2) $_POST автоматически парсится только для application/x-www-form-urlencoded и multipart/form-data; для application/json (типовой случай API) $_POST = [], нужно читать php://input и делать json_decode($raw, true). 3) Стрим можно перечитывать (в PHP 5.6+), но один раз — лучше сохранить в переменную. 4) Размер тела ограничен post_max_size в php.ini (даже для не-form-data в PHP 8.1+ оно работает как глобальный лимит на тело запроса); превышение даёт пустой $_POST и $_SERVER["CONTENT_LENGTH"] больше реально прочитанного. 5) В фреймворках работают абстракции: Laravel — $request->getContent(), Symfony — $request->getContent(), PSR-7 — $request->getBody()->getContents(); все они в итоге читают тот же php://input.',
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
