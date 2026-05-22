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
                'answer' => '**Исключение** — объект, выбрасываемый через `throw` и перехватываемый через `try/catch`. **Прерывает** обычный поток и «всплывает» вверх по стеку, пока не найдёт подходящий `catch`.

**Блоки:**
- **`try`** — защищаемый код.
- **`catch (Type $e)`** — обработчик; можно несколько подряд для разных типов; конкретные **сверху**, общие — **снизу**.
- **`finally`** — выполняется **всегда** (даже при `return`/`throw` внутри `try`/`catch`). Для закрытия ресурсов.

**Иерархия (важно):**
- **`Throwable`** — корневой **интерфейс**.
- **`Exception`** — ошибки приложения (`InvalidArgumentException`, `RuntimeException`, ваши кастомы).
- **`Error`** — ошибки движка (`TypeError`, `ValueError`, `DivisionByZeroError`, `ParseError`).
- **`Exception` и `Error` — не родственники**, две параллельные ветки.

**Возможности:**
- **Multi-catch через `|`** (PHP **7.1+**): `catch (TypeError | ValueError $e)`.
- **`throw` как выражение** (PHP **8+**): можно в `??`, `?:`, стрелочных функциях:
  - `$user = $repo->find($id) ?? throw new NotFoundException();`
- **Chained exceptions** — третий аргумент конструктора `previous`: оборачиваем `PDOException` в свой `DatabaseException`, сохраняя цепочку.

**Подводный камень:** `catch (Exception $e)` **не поймает** `TypeError` — для всего сразу пишут `catch (Throwable $e)`.',
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
                'answer' => '**Главное:** **`Exception`** и **`Error`** — **две независимые ветки** иерархии. Общий предок — интерфейс **`Throwable`**, прямого родства между ними **нет**.

**Что куда относится:**

| Ветка | Назначение | Примеры |
|---|---|---|
| **`Exception`** | бизнес/приложение | `InvalidArgumentException`, `RuntimeException`, `LogicException`, `JsonException`, ваши кастомные |
| **`Error`** | проблемы движка/runtime | `TypeError`, `ValueError`, `DivisionByZeroError`, `ArgumentCountError`, `AssertionError`, `ParseError` |

**Ловушка собеса:** `catch (Exception $e)` **НЕ поймает** `TypeError` / `ValueError` / `DivisionByZeroError` — это всё `Error`.

**Почему это особенно важно в PHP 8+:**
- Раньше многие сбои были `Warning` / `Fatal Error` **без возможности перехвата**.
- В PHP 8 они стали **`Error`**: несоответствие типов, передача `null` в non-nullable, обращение к свойству на `null`, деление на ноль.

**Правило для production-кода:**
- **Локально** ловят конкретный класс (`InvalidArgumentException`, `JsonException`) — обрабатывают **ожидаемые** ситуации.
- **На верхнем уровне** (global exception handler, middleware) ловят **`Throwable`** — поймает и приложение, и движок. Это спасает от white screen of death.

**Разделение по смыслу:**
- **`Exception`** — «ситуация, которую программа умеет обработать» (валидация, NotFound).
- **`Error`** — «программный баг» — локально не лечат, но обязательно **логируют** на верху.',
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
                'answer' => '**`Warning`, `Notice`, `Deprecated` НЕ являются исключениями** — это сообщения движка через систему ошибок PHP.

**Что с ними происходит по умолчанию:**
- **залогируются** и выполнение **продолжится**
- при **`@`-suppression** (`@file_get_contents(...)`) — тихо проигнорируются
- **`try/catch`** их **НЕ** ловит — нечего ловить, не объект

**Решение — `set_error_handler($cb)`:**
- регистрирует **глобальный обработчик** для уровней **`E_WARNING`**, **`E_NOTICE`**, **`E_DEPRECATED`**, **`E_USER_*`**
- внутри handler-а **`throw new ErrorException($msg, 0, $level, $file, $line)`** — превращаем в **перехватываемое** исключение

**Используется как:**
- **«нулевая толерантность к warning-ам»** в проде
- **Laravel** делает это в `Illuminate\\Foundation\\Bootstrap\\HandleExceptions::handleError` — превращает все non-fatal в `ErrorException` и шлёт в свой `ExceptionHandler`

**Уровни ошибок, которые попадают в handler:**

| Уровень | Когда |
| --- | --- |
| `E_WARNING` | runtime warning (например, `file_get_contents` не нашёл файл) |
| `E_NOTICE` | undefined variable, undefined index |
| `E_DEPRECATED` | использование устаревших функций |
| `E_USER_*` | `trigger_error()` из user-space |
| `E_STRICT` | устарело (в современных PHP не используется) |

**Что `set_error_handler` НЕ ловит (fatal errors):**

| Уровень | Что |
| --- | --- |
| `E_ERROR` | Out of memory, runtime fatal |
| `E_PARSE` | синтаксическая ошибка |
| `E_CORE_ERROR` / `E_COMPILE_ERROR` | ошибки ядра |
| Stack overflow | переполнение стека |

Для них — **`register_shutdown_function`** + **`error_get_last()`**.',
                'code_example' => '<?php
// Превращаем все warning/notice в исключения
set_error_handler(function (int $severity, string $message, string $file, int $line) {
    if (!(error_reporting() & $severity)) return false; // уважаем @
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

// Локально для одного блока — set + finally + restore
$prev = set_error_handler(fn() => throw new ErrorException("..."));
try {
    json_decode($maybe, flags: JSON_THROW_ON_ERROR); // JsonException
} finally {
    set_error_handler($prev);
}

// Fatal errors — только через shutdown function
register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err["type"], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        Log::critical("fatal", $err);
        // отправить sentry/bugsnag-нотификацию до выхода
    }
});',
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
                'answer' => '**Оператор `@` перед выражением** (`@file_get_contents("/no/such")`, `@json_decode($s)`) — **временно** устанавливает **`error_reporting() = 0`** на время вычисления выражения.

**Важный нюанс:**
- сами warning-и/notice-ы **всё равно ГЕНЕРИРУЮТСЯ** внутри PHP
- стандартный обработчик ошибок их **игнорирует**, потому что `error_reporting()` пуст
- логи **не пополняются**, выполнение продолжается

**Взаимодействие с `set_error_handler`:**
- ваш callback **вызывается по-прежнему**, даже если выражение под `@`
- если внутри **наивно** `throw new ErrorException(...)` **безусловно** — получите исключение там, где legacy-код рассчитывал на **тихое подавление**

**Где это ломается типично:**

| Legacy-вызов | Что ожидалось | Что получается с наивным handler |
| --- | --- | --- |
| **`@file_get_contents("/optional.json")`** | вернёт `false`, дальше fallback | `ErrorException` ломает fallback |
| **`@json_decode($maybe_string)`** | вернёт `null`, дальше проверка | исключение в неожиданном месте |
| **`@fopen($path, "r")`** | вернёт `false`, обработать | то же |

**Правильный паттерн в handler:**
```php
set_error_handler(function ($severity, $msg, ...) {
    if ((error_reporting() & $severity) === 0) {
        return false;  // PHP, обрабатывай как обычно (т. е. тихо)
    }
    throw new ErrorException($msg, 0, $severity, ...);
});
```

`return false` означает «**PHP, делай дефолт**» — что в случае `@` = тихо проигнорировать. Это поведение **явно описано** в документации `set_error_handler`.

**PHP 8.0+:** `@` **больше не подавляет fatal-ошибки** — `@$obj->method()` на `null` всё равно даёт `Error`. Это убрало часть исторических трюков (раньше `@` глотало даже `Fatal Error: Allowed memory size`).

**Best practice:**
- **новый код** — **не полагаться на `@`**, использовать **явные проверки** (`file_exists`, `is_resource`, `array_key_exists`)
- **кастомный handler** — обязательно с `error_reporting()`-чеком
- **`@` остаётся** уместен в нескольких сценариях: вызов legacy-функций с не-throw API, где альтернатива — громоздкая проверка перед каждым обращением',
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
                'answer' => '**Fatal Error Backtraces (PHP 8.5+)** — фатальные ошибки печатают **полную трассировку стека** с именами функций и файлами.

**Что попадает под нововведение:**

| Ошибка | До 8.5 | С 8.5 |
| --- | --- | --- |
| Превышение **`max_execution_time`** | «Maximum execution time of 30 exceeded» — **где** именно? | **полный backtrace** последнего кадра |
| **Out of memory** | «Allowed memory size of 128M exhausted» | + стек, где это случилось |
| Stack overflow | terse-сообщение | стек до точки переполнения |
| Прочие `E_ERROR` | минимум данных | trace с файлами и строками |

**Почему это важно:**
- **до 8.5**: причину долгого цикла или утечки приходилось искать **вслепую** через xdebug-tracer / strace / профилирование
- **с 8.5**: сразу видно, какая функция «зависла» — typically замкнувшийся `while`, рекурсия без выхода, обход циклического графа

**Как включить / настроить:**
- **по умолчанию включено** в PHP 8.5
- глубину контролирует **`zend.exception_string_param_max_len`** (для аргументов в trace)
- интегрируется с **`error_log`** и SAPI-логами

**Связанные тюнинги (актуальны и до 8.5):**
- **`memory_get_peak_usage(true)`** в shutdown-handler — кэп памяти
- **xdebug.show_local_vars** — local-переменные в trace
- **Sentry / Bugsnag** теперь получают значительно более полезные backtrace на OOM, без обвязки

**Best practice:** не отключать (`zend.exception_ignore_args=0` для read-only), и логировать на критичных сервисах в **отдельный файл**, чтобы автоматизированный анализ мог парсить trace.',
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
                'answer' => '**Да** — двумя способами:

**1. Multi-catch через `|`** (PHP 7.1+): **`catch (TypeError | ValueError $e)`** — одна общая обработка для нескольких разнородных типов.

**2. Несколько `catch`-блоков подряд** — каждый со своей логикой.

**Важно про порядок:**
- **конкретные** типы ставятся **выше**
- **общий** (`Exception`, `Throwable`) — **последним**
- иначе общий перехватит всё первой, и нижние блоки никогда не сработают

**PHP 8+ нюанс:** имя переменной в `catch` **необязательно** — `catch (LogicException) {}` валидно, если сам объект не нужен (только сам факт типа).',
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
                'answer' => '**`Throwable`** — корневой **интерфейс**, всё, что можно бросить через `throw`.

**От него наследуются ДВЕ параллельные ветки:**
- **`Exception`** — ошибки **уровня приложения**: `InvalidArgumentException`, `RuntimeException`, `LogicException`, `JsonException`, ваши кастомные классы
- **`Error`** — ошибки **уровня PHP-движка**: `TypeError`, `ValueError`, `DivisionByZeroError`, `ParseError`, `ArgumentCountError`

**Главная ловушка:** **`catch (Exception $e)` НЕ ловит `Error`** — это разные ветки, не родственники.

**Историческая справка:** до PHP 7 ошибки движка были fatal и не ловились вообще. С PHP 7 их можно перехватить через `catch (Error)` или `catch (Throwable)`.

**Правило:** на верхнем уровне (middleware, exception handler) ловят именно **`Throwable`** — он поймает и то и другое.',
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
