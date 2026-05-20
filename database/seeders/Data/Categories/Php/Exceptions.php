<?php

namespace Database\Seeders\Data\Categories\Php;

class Exceptions
{
    public static function all(): array
    {
        return [
            [
                'category' => 'PHP',
                'question' => 'Как работают исключения в PHP?',
                'answer' => 'Исключение - объект, выбрасываемый через throw. Перехватывается через try/catch. finally выполняется всегда (даже при return/throw). Иерархия: Throwable - корень, его наследуют Exception (можно ловить) и Error (внутренние ошибки PHP). Можно ловить несколько типов через | (multi-catch, PHP 7.1+). С PHP 8 throw - выражение, можно использовать в ?: и ??.',
                'code_example' => '<?php
try {
    if ($x < 0) {
        throw new InvalidArgumentException("отрицательное");
    }
} catch (InvalidArgumentException | TypeError $e) {
    // multi-catch (PHP 7.1+)
    echo $e->getMessage();
} catch (Exception $e) {
    echo "Общая ошибка: " . $e->getMessage();
} finally {
    echo "Выполнится всегда";
}

// Кастомное исключение
class NotFoundException extends Exception {}

// throw как выражение (PHP 8)
$user = $repo->find($id) ?? throw new NotFoundException();

// Цепочка исключений
try {
    /* ... */
} catch (PDOException $e) {
    throw new DatabaseException("DB error", 0, $e); // 3й - предыдущее
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.exceptions',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем Exception отличается от Error и почему важно ловить Throwable, а не Exception?',
                'answer' => 'И Exception, и Error реализуют интерфейс Throwable, но НЕ являются родственниками - они два независимых корня иерархии. Это критично: catch (Exception $e) НЕ поймает TypeError, ValueError, DivisionByZeroError, ArgumentCountError, AssertionError - это всё наследники Error. В PHP 8 многие ситуации, которые раньше были Warning или Fatal Error без возможности перехвата, переведены в Error: вызов несуществующего метода, передача неподходящего типа в функцию, undefined-обращения в strict-режиме - всё это TypeError/Error. Поэтому global-обработчик в проде должен ловить Throwable: catch (Throwable $e) поймает И Exception (бизнес-логика), И Error (рантайм-проблемы), залогирует и красиво ответит клиенту вместо white screen of death. Exception - для ожидаемых ситуаций, которые программа умеет обрабатывать (валидация, NotFound, ConflictException). Error - для проблем рантайма; их обычно НЕ ловят локально, но обязательно перехватывают на верхнем уровне (middleware/exception handler) для логирования.',
                'code_example' => '<?php
try {
    intdiv(10, 0);  // DivisionByZeroError (наследник Error)
} catch (DivisionByZeroError $e) {
    echo "ошибка деления";
}

try {
    $x = "abc";
    $x();  // вызов несуществующей функции - Error
} catch (Error $e) {
    echo "Error: " . $e->getMessage();
}

// Поймать всё
try {
    riskyOperation();
} catch (Throwable $t) {
    logError($t);
    throw $t;
}

// PHP 8: TypeError при несовпадении типа
function add(int $a, int $b): int {
    return $a + $b;
}
add("abc", 5); // TypeError',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.exceptions',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как перехватить Warning или Notice в try/catch?',
                'answer' => 'Сами по себе Warning, Notice, Deprecated НЕ являются исключениями - это сообщения движка через систему ошибок PHP. Их нельзя поймать через try/catch напрямую: PHP залогирует и пойдёт дальше, или (для @-suppressed) тихо проигнорирует. Чтобы превратить их в перехватываемые исключения, регистрируется глобальный set_error_handler($callback) - функция, которая будет вызвана для каждой ошибки уровня E_WARNING/E_NOTICE/E_DEPRECATED/E_USER_*. Внутри handler-а бросают throw new ErrorException($msg, 0, $level, $file, $line) - и теперь это полноценное исключение, ловится через catch. Это стандартный приём для "нулевой толерантности к warning-ам" в проде. Laravel так и делает: Illuminate\\Foundation\\Bootstrap\\HandleExceptions::handleError превращает все non-fatal ошибки в ErrorException и пробрасывает через свой ExceptionHandler. Альтернатива - error_reporting + наблюдение в логах, но для надёжного фейла теста / отказа в API лучше ErrorException. Подводный камень: set_error_handler НЕ ловит fatal errors (Out of memory, Stack overflow, ParseError) - для них есть register_shutdown_function + error_get_last.',
                'code_example' => '<?php
// Превращаем все warning/notice в исключения
set_error_handler(function (int $severity, string $message, string $file, int $line) {
    if (! (error_reporting() & $severity)) return false; // уважаем @
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Теперь это ловится
try {
    $data = file_get_contents("/no/such/file"); // обычно Warning
} catch (ErrorException $e) {
    Log::warning("read failed", ["err" => $e->getMessage()]);
    $data = "";
}

// Восстановить предыдущий обработчик
restore_error_handler();

// Локально для одного блока
$prev = set_error_handler(fn() => throw new ErrorException("..."));
try {
    json_decode($maybe, flags: JSON_THROW_ON_ERROR); // флаг доступен с PHP 7.3 - кидает JsonException
} finally {
    set_error_handler($prev);
}

// Fatal errors не ловятся - только через shutdown function
register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err["type"], [E_ERROR, E_PARSE, E_CORE_ERROR])) {
        Log::critical("fatal", $err);
    }
});',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.exceptions',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что делает оператор @ (shut up) и почему кастомный set_error_handler ломается без проверки error_reporting()?',
                'answer' => 'Оператор @ перед выражением (@file_get_contents("/no/such"), @json_decode($s)) ВРЕМЕННО устанавливает error_reporting() в 0 на время вычисления выражения. Сами warning-и/notice-ы при этом всё равно ГЕНЕРИРУЮТСЯ внутри PHP - просто стандартный обработчик ошибок их игнорирует, потому что error_reporting() пуст. ВАЖНОЕ ВЗАИМОДЕЙСТВИЕ: если вы поставили свой set_error_handler() (например, чтобы превращать warning-и в ErrorException), ваш callback вызывается по-прежнему, даже если выражение под @. Если внутри callback вы наивно throw new ErrorException(...) безусловно - получите исключение там, где legacy-код или фреймворк рассчитывали на тихое подавление через @ (типичные места: file_get_contents() для опционального файла, json_decode() от мусора, fopen() с проверкой результата). Правильный паттерн: внутри error_handler-а проверяйте (error_reporting() & $severity) === 0 и в этом случае возвращайте false (значит "PHP, обрабатывай как обычно, то есть тихо"). Это поведение явно описано в документации set_error_handler. С PHP 8.0 @ больше НЕ подавляет fatal-ошибки и убил часть исторических трюков. Совет: новый код не должен полагаться на @, использовать явные проверки (file_exists, is_resource); кастомный handler писать с error_reporting()-чеком, чтобы не ломать чужие вызовы под @.',
                'code_example' => '<?php
// ❌ Плохой error handler - игнорирует @
set_error_handler(function ($severity, $msg, $file, $line) {
    throw new ErrorException($msg, 0, $severity, $file, $line);
});

// Где-то в legacy: рассчитывается на тихий warning при отсутствии файла
$content = @file_get_contents("/optional/path.json");
if ($content === false) { /* fallback */ }
// Из-за наивного handler сюда прилетит ErrorException, fallback не сработает

// ✅ Правильный handler - уважает @
set_error_handler(function ($severity, $msg, $file, $line) {
    // если выражение под @ - error_reporting() = 0, не вмешиваемся
    if ((error_reporting() & $severity) === 0) {
        return false; // PHP сам решит (т.е. тихо проигнорирует)
    }
    throw new ErrorException($msg, 0, $severity, $file, $line);
});',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.exceptions',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое Fatal Error Backtraces в PHP 8.5?',
                'answer' => 'С PHP 8.5 фатальные ошибки, включая превышение max_execution_time и out-of-memory, печатают полную трассировку стека с именами функций и файлами. Раньше такие ошибки давали лишь точку остановки, и причину долгих циклов или утечек приходилось искать вслепую.',
                'difficulty' => 4,
                'topic' => 'php.exceptions',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое исключение (exception) простыми словами?',
                'answer' => '**Исключение** — объект, описывающий ошибочную ситуацию в коде. Когда что-то идёт не так (нет файла, неверный ввод, упала БД), вместо «тихого» `return false` выбрасывается исключение командой **`throw new SomeException("message")`**.

**Как работает:**
- прерывает обычный поток выполнения
- «поднимается» вверх по стеку вызовов, пока его не поймают через `try/catch`
- если не поймали — программа упадёт с сообщением «Uncaught Exception» и трассировкой стека

**Полезные методы у всех исключений:**
- `getMessage()` — текст ошибки
- `getCode()` — код
- `getFile()` / `getLine()` — где случилось
- `getTrace()` — стек вызовов

**Плюсы перед `return false`:**
- ошибку **нельзя случайно проигнорировать**
- логика «всё хорошо» не смешивается с обработкой ошибок',
                'code_example' => '<?php
function divide(int $a, int $b): int {
    if ($b === 0) {
        throw new InvalidArgumentException("Деление на ноль");
    }
    return intdiv($a, $b);
}

try {
    echo divide(10, 0);
} catch (InvalidArgumentException $e) {
    echo "Ошибка: " . $e->getMessage();   // "Ошибка: Деление на ноль"
    echo " в " . $e->getFile() . ":" . $e->getLine();
}',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.exceptions',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работает try/catch в PHP простыми словами?',
                'answer' => 'Три блока для безопасного выполнения «рискованного» кода:

- **`try`** — пробуем выполнить код, который может бросить исключение.
- **`catch (Exception $e)`** — если бросилось — обработай его. Можно несколько `catch` подряд для разных типов.
- **`finally`** — выполнится **в любом случае** (и при успехе, и при ошибке). Удобно для закрытия ресурсов: файлов, соединений.',
                'code_example' => 'try {
    $data = json_decode($input, true, flags: JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
    echo "Кривой JSON: " . $e->getMessage();
} finally {
    cleanup();
}',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.exceptions',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что делает оператор throw в PHP?',
                'answer' => '**`throw`** бросает исключение — создаёт объект-ошибку и **прерывает** обычный поток выполнения.

**Что происходит дальше:**
- исключение поднимается **вверх по стеку вызовов**, пока не встретит подходящий `catch`
- если подходящего `catch` не нашлось — программа упадёт с сообщением «Uncaught Exception»

**С PHP 8** `throw` стал **выражением**, поэтому его можно использовать в тернарнике и в `??`:
```
$user = $repo->find($id) ?? throw new NotFoundException();
```',
                'code_example' => '<?php
function divide(int $a, int $b): int {
    if ($b === 0) {
        throw new InvalidArgumentException("Деление на ноль");
    }
    return intdiv($a, $b);
}

// PHP 8: throw как выражение
$user = $repo->find($id) ?? throw new RuntimeException("not found");',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.exceptions',
            ],
            [
                'category' => 'PHP',
                'question' => 'Можно ли ловить несколько типов исключений в одном catch?',
                'answer' => 'Да, через | (multi-catch, PHP 7.1+): catch (TypeError | ValueError $e). До 7.1 для каждого типа нужен был отдельный блок catch с одинаковой обработкой. Также можно перечислить несколько разных catch — порядок ВАЖЕН: конкретные типы ставят выше, общий (Exception, Throwable) — последним, иначе общий ветка перехватит всё первой. С PHP 8 имя переменной в catch необязательно — можно писать catch (LogicException) {}, если объект не нужен.',
                'code_example' => '<?php
try {
    $data = json_decode($input, true, flags: JSON_THROW_ON_ERROR);
    $user = $repo->findOrFail($data["id"]);
} catch (JsonException | InvalidArgumentException $e) {
    // multi-catch — общая обработка для двух типов
    return response("Bad input: " . $e->getMessage(), 400);
} catch (NotFoundException) {
    // имя переменной можно опустить (PHP 8+)
    return response("Not found", 404);
} catch (Throwable $e) {
    // общий ловец — последним
    report($e);
    return response("Server error", 500);
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.exceptions',
            ],
            [
                'category' => 'PHP',
                'question' => 'В чём разница между Exception, Error и Throwable простыми словами?',
                'answer' => 'Throwable — корневой ИНТЕРФЕЙС, всё, что можно бросить через throw. От него наследуются ДВЕ параллельные ветки: Exception (ошибки уровня приложения — InvalidArgumentException, RuntimeException, LogicException) и Error (ошибки уровня PHP-движка — TypeError, ValueError, DivisionByZeroError, ParseError). Важный момент: catch (Exception $e) НЕ поймает TypeError или DivisionByZeroError — они наследники Error, не Exception. До PHP 7 ошибки движка были fatal и не ловились вообще; с PHP 7 их тоже можно перехватить через catch (Error) или catch (Throwable). На верхнем уровне (middleware, exception handler) обычно ловят именно Throwable.',
                'code_example' => '<?php
try {
    intdiv(10, 0);
} catch (Exception $e) {
    echo "не сработает";   // DivisionByZeroError — не Exception!
} catch (Error $e) {
    echo "поймали Error";
}

// Универсальный верхний уровень
try {
    riskyOperation();
} catch (Throwable $t) {     // ловит И Exception, И Error
    report($t);
    throw $t;
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.exceptions',
            ],
        ];
    }
}
