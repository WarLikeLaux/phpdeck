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
        ];
    }
}
