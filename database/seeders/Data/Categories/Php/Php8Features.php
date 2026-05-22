<?php

namespace Database\Seeders\Data\Categories\Php;

class Php8Features
{
    public static function all(): array
    {
        return [
            [
                'category' => 'PHP',
                'question' => 'Что такое именованные аргументы (named arguments)?',
                'answer' => '**Named arguments** (PHP 8.0+) — передача аргументов **по имени параметра**, а не по позиции. Синтаксис: **`name: value`**.

**Зачем нужно:**
- много опциональных параметров — **не надо помнить порядок** и пропускать промежуточные `null, null, null`
- читаемость на вызове: видно, что значит каждый аргумент
- удобно для функций со многими булевыми флагами (`str_replace`, `preg_match`)

**Правила:**
- именованные **строго после** позиционных, смешивать можно
- имя должно совпадать с именем параметра — переименование параметра становится breaking change
- работает и в **атрибутах** (`#[Route(path: "/x", methods: ["GET"])]`)',
                'code_example' => '<?php
function createUser(
    string $name,
    int $age = 18,
    bool $isAdmin = false,
    string $role = "user",
) {}

// Старый способ - пришлось бы передавать всё
createUser("Иван", 30, false, "manager");

// Named arguments - только нужное
createUser(name: "Иван", role: "manager");

// Можно в любом порядке
createUser(role: "admin", name: "Аня");

// Смешанно
createUser("Петя", isAdmin: true);

// В сложных функциях очень помогает
str_replace(
    search: ["a", "b"],
    replace: ["1", "2"],
    subject: $text,
);',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работает match-выражение в PHP 8?',
                'answer' => '**`match`** (PHP 8.0+) — выражение для сопоставления значений, безопасная замена `switch`.

**Чем отличается от `switch`:**

| Свойство | `match` | `switch` |
|---|---|---|
| Сравнение | **строгое `===`** | нестрогое `==` |
| Возвращает значение | да (это **выражение**) | нет (стейтмент) |
| `break` | не нужен (нет fallthrough) | обязателен |
| Без совпадения | бросает **`UnhandledMatchError`** | тихо проходит мимо |
| Несколько значений в ветке | через запятую | падающие `case`-ы |

**Подводные камни:**
- **строгое `===`** — `match(1)` не совпадёт с веткой `"1"`
- **`default`** обязателен, если не покрыты все возможные значения, иначе `UnhandledMatchError`
- ветки `match` — **выражения**, нельзя выполнить блок из нескольких операторов (для этого вызывайте функцию)
- значение слева вычисляется **один раз**, в отличие от цепочки `if/elseif`',
                'code_example' => '<?php
$status = "active";

// match - выражение
$label = match($status) {
    "active", "online" => "Активен",
    "inactive" => "Неактивен",
    "banned" => "Заблокирован",
    default => "Неизвестно",
};

// switch требует break и не возвращает значение
switch ($status) {
    case "active":
    case "online":
        $label = "Активен";
        break;
    // ...
}

// match со строгим сравнением
$result = match(1) {
    "1" => "string",  // не совпадёт!
    1 => "int",       // совпадёт
};

// Без default - UnhandledMatchError при отсутствии
$x = match($y) { 1 => "a" };',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое enum в PHP 8.1?',
                'answer' => '**`enum`** (PHP 8.1+) — тип-перечисление с фиксированным набором значений. Заменяет константы класса там, где значение должно ограничиваться **закрытым множеством**.

**Два вида:**
- **Pure enum** — просто кейсы без скалярного значения (`case Active;`)
- **Backed enum** — каждый кейс привязан к **`int` или `string`** (`case Active = "active";`), удобно для БД и JSON

**Возможности:**
- кейс — **синглтон**, сравнение через `===` (или через `match`)
- может реализовывать **интерфейсы** и иметь методы (обычно через `match($this)`)
- статический метод `cases()` — все варианты
- у backed: **`from($v)`** бросает `ValueError`, **`tryFrom($v)`** возвращает `null`

**Ограничения:**
- нельзя `new Enum()` — только статический доступ
- **нет состояния** — свойства запрещены (поэтому методы должны полагаться только на `$this`)
- backed enum не наследуется, но может имплементить `BackedEnum`/`UnitEnum`',
                'code_example' => '<?php
// Pure enum
enum Status {
    case Active;
    case Inactive;
    case Banned;
}

$s = Status::Active;
var_dump($s === Status::Active); // true

// Backed enum
enum Role: string {
    case Admin = "admin";
    case User = "user";
    case Guest = "guest";

    public function label(): string {
        return match($this) {
            Role::Admin => "Администратор",
            Role::User => "Пользователь",
            Role::Guest => "Гость",
        };
    }
}

$role = Role::from("admin");          // Role::Admin
$role = Role::tryFrom("xxx");         // null
echo Role::Admin->value;              // "admin"
echo Role::Admin->label();            // "Администратор"
print_r(Role::cases());               // все варианты',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как PHP управляет памятью в долгоживущих CLI-процессах (демонах, воркерах) и как избежать утечек?',
                'answer' => '**Контекст:** в обычном **FPM-цикле** каждый запрос — свежий процесс, память освобождается на выходе. В **долгоживущих воркерах** (`queue:work`, Octane, Swoole, ReactPHP, RoadRunner) процесс живёт **часами/днями** — любой неотпущенный объект **навсегда** занимает память.

**Двухуровневый GC в PHP:**

| Механизм | Когда срабатывает |
| --- | --- |
| **Reference counting** | мгновенно при `refcount = 0` |
| **Mark-and-sweep cycle collector** | при накоплении `10000` возможных корней или вручную `gc_collect_cycles()` |

Циклические ссылки (`A→B, B→A`) не освобождаются по refcount — для них нужен cycle collector.

**Тонкий момент — Zend Memory Manager:**

- даже после `gc_collect_cycles()` ZMM **удерживает память в виде арен и чанков**
- формально она «свободна» в PHP, но **не возвращена в ОС**
- для возврата RSS в ОС нужен **`gc_mem_caches()`** — очищает внутренние кеши ZMM
- без этого **RSS процесса** (видный в `top`/`htop`) только **растёт**

**Типичные источники утечек:**

1. **Глобальные/статические** массивы-кеши без TTL
2. **Eloquent-модели**, удерживаемые в Job через свойства
3. **Singleton-сервисы** с накапливающимся state
4. **Замыкания**, захватывающие `$this` и удерживающие крупные объекты
5. **Listeners**, подписанные на каждом запросе без отписки

**Практика для разных рантаймов:**

| Рантайм | Подход |
| --- | --- |
| **Laravel `queue:work`** | флаги `--max-jobs=1000 --max-time=3600 --memory=256` — воркер сам перезапускается |
| **Laravel Octane** | listener `Laravel\\Octane\\Listeners\\CollectGarbage` на `OperationTerminated`, **порог в `config(\"octane.garbage\")`** (дефолт **50 МБ**) |
| **RoadRunner** | `RR_HTTP_MAX_JOBS=1000` — перезапуск воркера |
| **Symfony Messenger** | `--limit=N --time-limit=N --memory-limit=128M` |

**ВАЖНО про Octane:** в стоковом `config/octane.php` listener **CollectGarbage** ЗАКОММЕНТИРОВАН — без раскомментирования автоматического GC между запросами **не будет**; `gc_mem_caches()` Octane **не зовёт вообще**, для реального возврата RSS в ОС нужно вызывать руками или через свой listener.

**Мониторинг:**

| Функция | Что возвращает |
| --- | --- |
| `memory_get_usage(true)` | память, выделенная ZMM из ОС (с учётом пула) |
| `memory_get_usage(false)` | только реально используемая |
| `memory_get_peak_usage(true)` | пик памяти (с учётом пула) |
| `/proc/<pid>/status` поле `VmRSS` | **настоящий RSS процесса** из ОС |',
                'code_example' => '<?php
// Job, накапливающий утечку
class ProcessOrderJob
{
    private static array $cache = []; // ⚠️ статика живёт между jobs

    public function handle(int $orderId): void
    {
        self::$cache[$orderId] = Order::with("items")->find($orderId);
        // через 100k jobs - OOM
    }
}

// Грамотный воркер
// supervisor: php artisan queue:work --max-jobs=1000 --max-time=3600 --memory=256

// Принудительная очистка между jobs
gc_collect_cycles();   // собрать циклические ссылки
gc_mem_caches();       // вернуть кеши Zend MM в ОС - снижает RSS
DB::disconnect();

// Мониторинг
Log::info("memory", [
    "mb" => round(memory_get_usage(true) / 1024 / 1024, 1),
    "peak" => round(memory_get_peak_usage(true) / 1024 / 1024, 1),
]);',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое FFI (Foreign Function Interface) в PHP?',
                'answer' => '**`ext-ffi`** (расширение **php-ffi**, формально PHP **7.4**, активно в современных 8.x-стеках) позволяет **вызывать функции C-библиотек напрямую** из PHP-кода без написания нативного PHP-расширения на C.

**До FFI:**

- интеграция с **libsodium, libcurl, libwebp** требовала писать `.c`-обёртку
- компилировать её в **`.so` / `.dll`** под каждую платформу
- релизить как **PECL-расширение** или поставлять руками
- этап «написать обёртку» отсекал большинство задач

**С FFI:**

```php
\$ffi = FFI::cdef(
    "size_t strlen(const char *s);",  // C-объявления как строка
    "libc.so.6"                         // или путь к собственной .so
);
echo \$ffi->strlen("hello"); // 5
```

**Применение:**

| Сценарий | Пример |
| --- | --- |
| **Системные библиотеки** без extension-разработки | вызов `libcrypto`, `libpng`, `libjpeg` |
| **Прототипирование** биндингов | проверить идею до полноценного PECL-пакета |
| **Доступ к нативным API ОС** | `inotify`, `kqueue`, **`io_uring`** |
| **Интеграция с Go/Rust** | через `cgo`-генерированные shared libs |
| **FrankenPHP** | использует FFI/cgo для интеграции PHP-runtime с Go-сервером Caddy |

**Подводные камни:**

| Проблема | Деталь |
| --- | --- |
| **Медленнее** PECL-расширения | overhead на маршалинг типов между PHP zval и C |
| **Небезопасен** | типичные C-проблемы: **segfault**, undefined behavior, memory corruption |
| **`.h` парсится при каждом `cdef`** | в проде нужен **`opcache.preload`** для FFI, иначе парсинг на каждом запросе |
| **`ffi.enable=preload`** (production-режим) | **`FFI::cdef`/`FFI::load`** вне preload-скрипта запрещены |
| FFI-объекты, **созданные в preload** | через **`FFI::scope("name")`** доступны везде после загрузки |
| **`Zend Memory Manager`** не знает про C-память | долгоживущие FFI-указатели **не учитываются** в `memory_get_usage()` |
| **Жизненный цикл** C-объектов | нужно явно `unset()` или дать GC — иначе утечки в native heap |

**Канонический setup в проде:**

```ini
; php.ini
ffi.enable = preload
opcache.preload = /path/to/preload.php
```

```php
// preload.php
\$ffi = FFI::load(__DIR__ . "/mylib.h");
FFI::scope("MyLib");  // сохранить под именем для доступа в рантайме
```

```php
// в рантайме (например, в Laravel-контроллере)
\$lib = FFI::scope("MyLib");  // не парсит .h заново
\$result = \$lib->compute(\$data);
```

**Альтернативы FFI:**

| Подход | Когда |
| --- | --- |
| **PECL-extension** (классика) | критичная по скорости интеграция, готовы поддерживать .c-код |
| **`exec()` / `Symfony\\Process`** | вызов внешнего CLI-бинаря |
| **gRPC/HTTP к C-сервису** | если C-код можно вынести в отдельный процесс |
| **Standard library** PHP | проверьте `ext-*` — часто нужное уже есть |',
                'code_example' => '<?php
// Вызов libc strlen
$ffi = FFI::cdef(
    "size_t strlen(const char *s);",
    "libc.so.6"
);
echo $ffi->strlen("hello"); // 5

// Своя .so
$lib = FFI::cdef(file_get_contents("mylib.h"), "./mylib.so");
$result = $lib->compute_hash("data");

// php.ini в проде
// ffi.enable=preload
// opcache.preload=/path/to/preload.php',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Дают ли Fibers настоящую многопоточность? Чем они отличаются от потоков ОС?',
                'answer' => '**НЕТ.** **`Fiber`** (PHP **8.1+**) — это **кооперативная многозадачность** (concurrency), а **НЕ параллелизм** (parallelism).

**Critical distinction:**

| | **Fibers** | **OS threads** | **Processes** |
| --- | --- | --- | --- |
| **Где живёт** | в одном потоке ОС | в одном процессе, на разных ядрах | разные процессы |
| **Переключение** | **явное** `Fiber::suspend()` | preemptive (планировщик ОС) | preemptive |
| **CPU-параллелизм** | НЕТ — одно ядро | да | да |
| **Shared memory** | да (один процесс) | да (один процесс) | через IPC |
| **Сложность** | управляет программист | планировщик ОС | межпроцессное взаимодействие |
| **Стоимость** | очень дёшево (~KB stack) | средне (~MB stack) | дорого (полный fork) |

**Что это значит на практике:**

- **CPU-bound задачи в fibers не ускорятся** — используется **одно ядро**
- **IO-bound задачи эффективно совмещаются** — пока один fiber ждёт сокет, шедулер запускает другой
- два fiber **никогда не работают одновременно** — это иллюзия многозадачности

**Зачем тогда добавили Fibers в PHP?**

Чтобы решить **проблему «function colors»** (Bob Nystrom, 2015): в JS/Python любая async-операция **«заражает»** цепочку — все вызывающие функции тоже должны стать `async`. Получается **два мира** — синхронный и асинхронный — и нельзя вызвать async из sync.

**С Fibers async-runtime может приостанавливать стек ВНУТРИ синхронной функции:**

```php
// Сигнатура НЕ имеет async/await — но под капотом fiber suspend/resume
function fetchUser(int $id): User {
    return $pdo->query("SELECT ...")->fetchObject(User::class);
    //     ^^^^ внутри драйвер вызывает Fiber::suspend(),
    //          event loop запускает другой fiber, ждущий сокета
}

// Пользователь пишет ОБЫЧНЫЙ код, без async/await
function index(): array {
    return ["user" => fetchUser(42), "posts" => fetchPosts(42)];
}
```

**Кто использует Fibers как примитив:**

| Runtime | Версия |
| --- | --- |
| **amphp v3** | переписан под Fibers |
| **ReactPHP** | новые версии |
| **RoadRunner** | для PSR-7 worker mode |
| **FrankenPHP** | для workers |

**Базовый Fiber API:**

```php
\$fiber = new Fiber(function (): void {
    echo "1 ";
    \$value = Fiber::suspend("paused");  // отдать управление
    echo "3 (получил: \$value) ";
});

echo \$fiber->start();   // "1 paused"
echo "2 ";
\$fiber->resume("hi");   // "3 (получил: hi)"
// Вывод: "1 paused 2 3 (получил: hi) "
```

**Для НАСТОЯЩЕГО параллелизма (несколько ядер) в PHP:**

| Подход | Деталь |
| --- | --- |
| **`ext-parallel`** | отдельные потоки с **shared-nothing**, `Runtime::run(fn() => heavyWork())` |
| **`pcntl_fork()`** | fork процесса (Unix only) |
| **`Symfony\\Process`** | внешние CLI-процессы |
| **Очереди** (`queue:work`) | распределение задач на отдельные воркеры |
| **Несколько FPM-воркеров** | параллелизм на уровне обработки независимых HTTP-запросов |

**Важно:** **Fibers != Goroutines** (Go) и **!= async/await** (JS). Goroutines умеют преэмптивно перепланироваться и могут идти на разные ядра; async/await в JS — синтаксический сахар над промисами, но в одном event loop.',
                'code_example' => '<?php
$fiber = new Fiber(function (): void {
    echo "1 ";
    $value = Fiber::suspend("paused");
    echo "3 (получил: $value) ";
});

echo $fiber->start();   // "1 paused"
echo "2 ";
$fiber->resume("hi"); // "3 (получил: hi)"
// Вывод: "1 paused 2 3 (получил: hi) "

// Всё в ОДНОМ потоке - никакого параллелизма
// Для CPU-параллелизма:
// - ext-parallel: $runtime->run(fn() => heavyWork())
// - pcntl_fork()
// - очереди: dispatch(new HeavyJob)->onQueue("workers")',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Какие инструменты профилирования PHP-кода есть и в чём их различия?',
                'answer' => '**Профилирование** — сбор статистики по **горячим точкам** в коде: CPU-время, аллокации памяти, длительность IO-вызовов.

**Главные инструменты в экосистеме PHP:**

| Инструмент | Стоимость | Overhead | Где применять |
| --- | --- | --- | --- |
| **Xdebug Profiler** | бесплатно | **10-100×** замедление | локально, на воспроизводимом сценарии |
| **Blackfire** | платный (есть free tier) | **5-10%** | прод-выборочно, CI-тесты регрессий |
| **SPX** | бесплатно, open-source | низкий | прод-выборочно, команды без бюджета |
| **Tideways** | платный | низкий | APM + профайлер |
| **Datadog / NewRelic APM** | платный | очень низкий | непрерывный мониторинг |

**1. Xdebug Profiler** — встроен в Xdebug, генерирует **Cachegrind-файлы** (формат от valgrind).

| Плюсы | Минусы |
| --- | --- |
| Бесплатно | **10-100× замедление** — не для прода |
| Локально | результирующий файл **огромный** (десятки МБ) |
| Точные данные по каждому **function call** | UI требует установки `KCachegrind`/`qcachegrind`/`Webgrind` |

Настройка:
```ini
zend_extension = xdebug
xdebug.mode = profile
xdebug.output_dir = /tmp/xdebug
xdebug.start_with_request = trigger
```

Запуск через query-параметр `?XDEBUG_PROFILE=1`. Открывать через **PhpStorm** или **KCachegrind**.

**2. Blackfire** — SaaS-профайлер от создателей Symfony.

| Плюсы | Минусы |
| --- | --- |
| **Production-ready** — гоняется на части запросов | платный для серьёзного использования |
| Отличный web-UI: timeline, call-graph, **сравнение профилей** | отправка данных в SaaS |
| **Performance-тесты регрессий** в CI | нужен агент в инфраструктуре |
| Метрики по **CPU/IO/SQL/HTTP** отдельно | — |

Запуск: `blackfire run php artisan import:big` или через расширение + webhook в CI.

**3. SPX** (open-source, бесплатный) — альтернатива от NoiseByNorthwest.

| Плюсы | Минусы |
| --- | --- |
| Low overhead, **prod-ready** | менее зрелый UI |
| Web-UI похож на Blackfire | нет регрессионных тестов |
| Триггер по query/cookie — профилировать **только этот запрос** | требует сборки из исходников или PECL |

Настройка:
```ini
extension = spx
spx.http_enabled = 1
spx.http_key = "secret"
spx.http_ip_whitelist = "10.0.0.0/8"
```

**4. APM (Datadog/NewRelic/Tideways)** — не полноценные профайлеры, но дают **агрегированную картину** «какой endpoint медленный в проде».

**Когда что использовать:**

| Сценарий | Выбор |
| --- | --- |
| Локально, известный медленный сценарий | **Xdebug** |
| CI-тесты регрессий perf | **Blackfire** |
| Команда без бюджета, прод-выборочно | **SPX** |
| Непрерывный мониторинг прода | **APM** (Datadog/NewRelic) |
| Heavy enterprise + поддержка | **Tideways** |

**Senior-приёмы:**

| Приём | Зачем |
| --- | --- |
| **Профилируйте ПЕРЕД оптимизацией** | угадывание узкого места почти всегда ошибается |
| **Правило 80/20** | 80% времени в 20% кода — найти и оптимизировать именно их |
| Простейший inline-замер | `hrtime(true)` + `memory_get_peak_usage(true)` для гипотез |
| **Bench на проде-подобном железе** | dev-машина даёт другие пропорции |
| Сравнивайте **профили до/после** | Blackfire `compare`, SPX `diff` |

**Простейший замер для разовой проверки гипотезы:**

```php
\$t = hrtime(true);
\$result = expensiveOperation();
\$elapsedMs = (hrtime(true) - \$t) / 1_000_000;
Log::info("expensive", ["ms" => \$elapsedMs]);
```',
                'code_example' => '<?php
// 1. Xdebug Profiler - локально
// php.ini:
//   zend_extension = xdebug
//   xdebug.mode = profile
//   xdebug.output_dir = /tmp/xdebug
//   xdebug.start_with_request = trigger
// curl "https://localhost/api/slow?XDEBUG_PROFILE=1"
// откроет /tmp/xdebug/cachegrind.out.<pid>
// открыть в KCachegrind / PhpStorm

// 2. SPX - быстрый production-ready профайлер
// php.ini:
//   extension = spx
//   spx.http_enabled = 1
//   spx.http_key = "secret"
//   spx.http_ip_whitelist = "10.0.0.0/8"
// curl -H "SPX_KEY: secret" -H "SPX_ENABLED: 1" https://app/slow
// открыть https://app/?SPX_UI_URI=/

// 3. Blackfire - в проде на части запросов
// blackfire run php artisan import:big
// или через расширение + вебхук в CI

// 4. Простейший inline-замер для проверки гипотезы
$t = hrtime(true);
$result = expensiveOperation();
$elapsedMs = (hrtime(true) - $t) / 1_000_000;
Log::info("expensive", ["ms" => $elapsedMs, "mem_mb" => memory_get_peak_usage(true) / 1024 / 1024]);

// Правило: ИЗМЕРЯЙ перед оптимизацией; угадывание узкого места почти всегда ошибается',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что делает атрибут #[\\Override] (PHP 8.3) и зачем он нужен?',
                'answer' => '**`#[\\Override]`** (PHP 8.3) — атрибут на методе, который проверяет в момент компиляции, что метод **действительно переопределяет** метод родителя или реализует метод интерфейса/абстрактного класса.

**Какую проблему решает:**
- родитель **переименовал/удалил** метод — наследник тихо превращается в обычный метод, полиморфизм ломается без ошибки
- **опечатка** в имени (`speack` вместо `speak`) — создаётся новый метод, родительский никогда не подменяется
- класс переопределяет метод **трейта**, который потом переименовали — orphaned-метод, который никто не вызовет

**Поведение без `#[\\Override]`:** PHP молча принимает «новый» метод и баг проявляется через `parent::method()` или в рантайме, иногда через недели.

**Поведение с `#[\\Override]`:** компилятор сразу бросает `Override of unknown method`.

**Ключевые свойства:**
- **compile-time only** — никакого overhead в рантайме
- работает и для **методов интерфейса** (наследник имплементит — добавляем `#[\\Override]`)
- **аналоги:** `@Override` в Java, `override` в C#/Kotlin/Swift/TypeScript

**Практика:** добавлять на **все** переопределяющие методы. PHPStan/Psalm и IDE добавляют автоматически. Особенно важен для методов с длинными именами (`handle`, `process`) и в крупных миграциях фреймворка.',
                'code_example' => '<?php
class Animal
{
    public function speak(): string { return "..."; }
}

class Dog extends Animal
{
    #[\\Override]
    public function speak(): string { return "Гав"; } // OK

    #[\\Override]
    public function speack(): string { return "..."; }
    // ⚠️ Compile error: Dog::speack() has #[\\Override] attribute,
    // but no matching parent method exists
}

// Без атрибута опечатка тихо создаёт новый метод
class DogBad extends Animal
{
    public function speack(): string { return "..."; } // новый метод!
}
$d = new DogBad();
echo $d->speak();  // "..." из Animal - молча сломалось

// Работает и с интерфейсами
interface Repository { public function find(int $id): ?Model; }

class UserRepo implements Repository
{
    #[\\Override]
    public function find(int $id): ?User { return /* ... */; }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Зачем нужна функция json_validate() (PHP 8.3)?',
                'answer' => '**`json_validate(string $json): bool`** (PHP 8.3) — проверяет, что строка является валидным JSON, **без создания PHP-структуры в памяти**.

**Зачем нужна:**

До 8.3 единственный способ — `json_decode` + `json_last_error()` (или `JSON_THROW_ON_ERROR` с `try/catch`). Но `json_decode` **аллоцирует** массив/объект под весь декодированный JSON. На больших payload-ах (мегабайты вебхуков, дампы) — лишний расход CPU и RAM, особенно в горячем пути и в долгоживущих воркерах.

**Что делает `json_validate`:**
- использует тот же парсер, что и `json_decode`, но **только парсит**, ничего не строит
- **O(N) по времени**, **O(1) по памяти**

**Аргументы:**
- `$depth` — макс. вложенность
- `$flags` — из публичных разрешён **только** `JSON_INVALID_UTF8_IGNORE`
- передача `JSON_THROW_ON_ERROR` валит `ValueError` (by design — функция возвращает `bool`, оборачивать в `try/catch` не надо)

**Применение:**
- rate-limit роутов с большим JSON-телом
- валидация **webhook**-ов до постановки в очередь
- фильтрация мусора на API gateway
- проверка структуры до тяжёлой записи

**Бенчмарк на 50 МБ JSON:** `json_decode` — ~120 МБ peak memory, `json_validate` — ~600 КБ.',
                'code_example' => '<?php
$payload = file_get_contents("php://input");

// ❌ До PHP 8.3 - аллоцируется вся структура
json_decode($payload);
if (json_last_error() !== JSON_ERROR_NONE) {
    return response("invalid json", 400);
}
// + дополнительный декод позже когда нужно реально использовать

// ✅ PHP 8.3+ - O(1) памяти
if (! json_validate($payload)) {
    return response("invalid json", 400);
}
// затем декодируем, когда уверены и реально нужен результат
$data = json_decode($payload, true);

// ❌ Так НЕ работает: json_validate запрещает JSON_THROW_ON_ERROR
// и кинет ValueError, а не JsonException - try/catch бессмысленен
// json_validate("invalid", flags: JSON_THROW_ON_ERROR);

// ✅ Правильный способ - проверять bool
if (! json_validate($payload)) {
    Log::warning("bad json", ["payload_size" => strlen($payload)]);
    return response("invalid json", 400);
}

// Бенчмарк: на 50 МБ JSON
// json_decode + last_error: ~120 МБ peak memory
// json_validate:            ~600 КБ peak memory',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Какие новые функции для массивов появились в PHP 8.4?',
                'answer' => 'PHP 8.4 добавил **четыре функции**, заполняющие давнюю дыру в stdlib. Все принимают `$array` и `$callback`.

| Функция | Возвращает | Аналог в JS |
|---|---|---|
| `array_find($arr, $cb)` | первое **значение**, где `$cb` вернул truthy, иначе `null` | `Array.find` |
| `array_find_key($arr, $cb)` | **ключ** первого совпадения, иначе `null` | — |
| `array_any($arr, $cb)` | `true`, если **хотя бы один** удовлетворяет | `Array.some` |
| `array_all($arr, $cb)` | `true`, если **все** удовлетворяют | `Array.every` |

**Главное преимущество — `early termination`:** останавливаются на первом совпадении (или первом несовпадении для `array_all`), **не проходят весь массив**.

**Поведение на пустом массиве:**
- `array_any([])` → `false`
- `array_all([])` → `true` (**vacuous truth** — для всех 0 элементов утверждение тривиально верно)

**До 8.4** для тех же задач писали:
- `array_filter()` — но проходит **весь** массив и **аллоцирует** промежуточный результат
- явный `foreach` с `break` — многословно

Это аналоги `Array.find/some/every` из JS и `anyMatch/allMatch/findFirst` из Java Streams.',
                'code_example' => '<?php
$users = [
    ["name" => "Иван", "active" => false],
    ["name" => "Аня",  "active" => true,  "admin" => true],
    ["name" => "Петя", "active" => true],
];

// PHP 8.4
$admin = array_find($users, fn($u) => $u["admin"] ?? false);
// ["name"=>"Аня", "active"=>true, "admin"=>true] или null

$adminIndex = array_find_key($users, fn($u) => $u["admin"] ?? false);
// 1 или null

$hasActive = array_any($users, fn($u) => $u["active"]);
// true

$allActive = array_all($users, fn($u) => $u["active"]);
// false (Иван неактивен)

// До PHP 8.4 - быстро, но многословно
$admin = null;
foreach ($users as $u) {
    if ($u["admin"] ?? false) { $admin = $u; break; }
}

// Антипаттерн через array_filter - проходит весь массив
$admin = array_filter($users, fn($u) => $u["admin"] ?? false);
$admin = $admin ? reset($admin) : null;',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что делает атрибут #[\\NoDiscard] в PHP 8.5?',
                'answer' => '**`#[\\NoDiscard]`** (PHP **8.5+**) — атрибут на функции/методе, помечающий, что **возвращаемое значение нельзя игнорировать**. Если результат вызова не используется, PHP выдаёт **`E_WARNING`** в момент компиляции/вызова.

**Какие проблемы решает:**

| Сценарий | До 8.5 | С `#[\\NoDiscard]` |
| --- | --- | --- |
| Иммутабельный wither: `\$req->withHeader(...)` — без `=` копия теряется | тихий баг | warning |
| Валидатор: `validate(\$input)` возвращает результат, а вы игнорируете | тихо «прошло» | warning |
| Money: `\$money->add(\$other)` создаёт новый объект | копия теряется | warning |
| `Result<T, E>` тип, Either/Maybe | пропущенный error-path | warning |

**Сигнатура и параметры:**

```php
#[\\NoDiscard("message")]  // опциональное сообщение
public function withHeader(string \$name, string \$value): self
```

**Аналоги в других языках:**

| Язык | Аналог |
| --- | --- |
| C/C++ | `[[nodiscard]]` (C++17) |
| Rust | `#[must_use]` |
| Java | `@CheckReturnValue` (ErrorProne) |
| Swift | `@discardableResult` (наоборот — разрешает игнорировать) |

**Где особенно полезно:**
- **PSR-7** wither-методы (`withHeader`, `withUri`, `withBody`) — иммутабельные
- **Validator**: `Validator::make(...)` возвращает результат проверки
- любая **fluent API** с immutable-семантикой

**Ограничения:**
- атрибут — **подсказка**, не runtime-блокер исполнения
- можно явно подавить через `(void) \$foo->bar()` или присваивание во временную переменную',
                'difficulty' => 4,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что даёт встроенное расширение URI в PHP 8.5?',
                'answer' => 'Встроенное **расширение `ext-uri`** (PHP **8.5+**) добавляет иммутабельные классы для парсинга и сборки URL по двум основным стандартам:

| Класс | Стандарт | Где применять |
| --- | --- | --- |
| `Uri\\Rfc3986\\Uri` | **RFC 3986** | строгое поведение для API, HTTP-клиентов, серверного кода |
| `Uri\\WhatWg\\Url` | **WHATWG URL Living Standard** | совместимость с поведением **браузеров и `fetch()`** |

**Что было до 8.5:**

- **`parse_url()`** — нестандартное поведение, не валидирует, **тихо** возвращает странные значения на битом URL
- сторонние библиотеки: `league/uri`, `nyholm/psr7`, `guzzlehttp/psr7` — каждая со своими API
- ловушки `parse_url`:
  - `parse_url("http:///example.com")` молча проглатывает пустой хост
  - `parse_url("//example.com")` без схемы — `host` в `path`
  - Unicode в hostname не нормализуется в IDN
  - не разделяет username/password корректно для `user:pass@host`

**Что даёт `ext-uri`:**

- **валидация** при создании — битый URI → `Uri\\InvalidUriException`
- корректная **нормализация** (case-folding scheme/host, decode unreserved characters)
- **процент-кодирование** по RFC
- **IDN** для не-ASCII хостов
- **иммутабельность** с wither-методами (`->withScheme(...)`, `->withHost(...)`)

**Зачем два класса (RFC vs WHATWG):**

| Различие | RFC 3986 | WHATWG |
| --- | --- | --- |
| Trailing dots в hostname | сохраняются | удаляются |
| `//` после схемы | требуется | гибче |
| Special schemes (http/https/file/ws) | как все | особое поведение по WHATWG |
| Unicode в path | percent-encoded | сохраняется как UTF-8 |

**Практически:** для **серверного** кода (HTTP-клиент, валидация) — **RFC 3986**. Для **«как браузер видит URL»** (анти-фрод, сравнение с реферрером, web-скраппинг) — **WHATWG**.

**Влияние на экосистему:** PSR-7 (`UriInterface`) — отдельный интерфейс; `ext-uri` его **не реализует напрямую**, но фреймворки/PSR-имплементации могут под капотом использовать новые классы.',
                'difficulty' => 4,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое типизированные константы классов в PHP 8.3?',
                'answer' => '**Типизированные константы** (PHP 8.3+) — у констант **класса, интерфейса, enum, trait** можно указывать тип: `const string ROLE = "admin"`.

**Зачем нужно:**
- тип проверяется при **объявлении** — нельзя положить значение неверного типа
- тип проверяется при **переопределении в наследнике** — нельзя сузить до несовместимого
- особенно полезно для **констант интерфейсов**: реализации больше не могут случайно положить туда что-то не то

**Допустимые типы:** скаляры (`int`, `string`, `float`, `bool`), `array`, `object`, конкретные классы, union/intersection, `nullable`, `mixed`. Запрещены `void`, `never`, `callable`.

**Правило ковариантности:** наследник может **уточнить** тип константы (расширить нельзя). Если у предка `string|int`, у потомка можно `string`, но не `string|int|null`.',
                'code_example' => '<?php
interface HasStatus {
    const string DEFAULT = "active";   // тип константы интерфейса
}

class User implements HasStatus {
    // const int DEFAULT = 1;          // Error: тип несовместим
    const string DEFAULT = "guest";    // OK — тот же тип
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое динамическое получение констант класса в PHP 8.3?',
                'answer' => '**Динамический доступ к константам класса** (PHP 8.3+) — синтаксис `MyClass::{$name}` для обращения по имени из переменной.

**До 8.3:**
```php
echo constant(Status::class . "::" . $name);
```
- многословно
- работает только со строкой полного имени
- легко допустить опечатку, IDE плохо подсказывает

**С 8.3:**
```php
echo Status::{$name};
```

**Свойства:**
- симметрично динамическому доступу к **свойствам** (`$obj->{$prop}`) и **методам** (`$obj->{$method}()`)
- работает с **enum cases** (`Role::{"Admin"}`) — удобно при десериализации
- работает со **statics** через имя константы из переменной/выражения

**Применение:** маршрутизация (имя константы соответствует параметру роута), десериализация enum-значений из JSON/БД, плагинная архитектура с лукапом по имени.',
                'code_example' => '<?php
class Status {
    const ACTIVE = "active";
    const BANNED = "banned";
}

$name = "ACTIVE";

// До PHP 8.3
echo constant(Status::class . "::" . $name); // "active"

// PHP 8.3+
echo Status::{$name};                        // "active"

// Работает и с enum
enum Role: string { case Admin = "admin"; }
echo Role::{"Admin"}->value;                 // "admin"',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что разрешает синтаксис new в инициализаторах PHP 8.1?',
                'answer' => '**`new` в инициализаторах** (PHP 8.1+) — объект, созданный через `new`, можно использовать как:
- значение **по умолчанию для параметра**
- значение **свойства**
- инициализатор **статической переменной**
- значение **параметра атрибута**

**Какую проблему решает:** убирает костыль с присваиванием в теле конструктора через `?? new NullLogger()` и упрощает DI с разумными дефолтами.

**Ограничения:**
- аргументы конструктора **должны быть константными выражениями** — нельзя `new Foo($someService)`, можно `new Foo("static")` или `new Foo(SomeEnum::A)`
- **рекурсия запрещена** — нельзя `class A { public function __construct(B $b = new B()) {} }` где B зависит от A
- объект создаётся **при каждом вызове** (не один раз) — для синглтона нужен явный контейнер

**Типичный паттерн:** Null Object для опционального логгера/кеша/диспетчера событий без `if ($logger === null)` в каждом методе.',
                'code_example' => '<?php
class Service {
    // До PHP 8.1
    public function __construct(?LoggerInterface $logger = null) {
        $this->logger = $logger ?? new NullLogger();
    }
}

// PHP 8.1+
class ServiceNew {
    public function __construct(
        private LoggerInterface $logger = new NullLogger(),
    ) {}
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Зачем нужен атрибут #[SensitiveParameter] в PHP 8.2?',
                'answer' => '**`#[\\SensitiveParameter]`** (PHP 8.2+) — атрибут на параметре функции, помечающий его как **чувствительный** (пароль, токен, API-key).

**Что меняет:**
- при формировании **stack trace** (исключения, `debug_backtrace()`, логи Sentry/Bugsnag) значение параметра заменяется на объект **`SensitiveParameterValue`** без реальных данных
- сама переменная внутри функции **не меняется** — это только про сериализацию трейса

**Зачем нужно:** до 8.2 пароль/токен попадал в стек-трейс при любом исключении глубже по коду — и оттуда в логи, Sentry, email-уведомления. Чтобы скрыть, надо было руками **зачищать трейсы** или ловить исключения и переоборачивать. Атрибут делает это автоматически.

**Где применять:**
- параметры с паролями, JWT, API-ключами, секретами
- свежий контракт PSR/Laravel — `Hash::make($password)`, `password_verify($password, $hash)` уже помечены
- свои сервисы аутентификации, платёжные интеграции

**Ограничения:** не защищает от **`var_dump`/`print_r`** внутри функции (это явный вывод значения, не трейс).',
                'code_example' => '<?php
function login(
    string $email,
    #[\\SensitiveParameter] string $password,
): void {
    throw new RuntimeException("auth failed");
}

try {
    login("a@b.c", "supersecret");
} catch (Throwable $e) {
    echo $e->getTraceAsString();
    // В трейсе вместо "supersecret" — Object(SensitiveParameterValue)
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что нового в работе с DateTime появилось в PHP 8.4?',
                'answer' => 'PHP 8.4 добавил статические методы **`DateTime::createFromTimestamp()`** и **`DateTimeImmutable::createFromTimestamp()`**, принимающие `int` или `float` и возвращающие объект соответствующего типа.

**Что было неудобно раньше:**
- `new DateTimeImmutable("@$ts")` — конструкция с `@` **всегда** интерпретирует timestamp как UTC и **игнорирует переданную таймзону**
- чтобы получить корректное локальное время, надо было руками вызывать `setTimezone()`
- это типичная ловушка: люди передают часовой пояс в конструктор, а он молча игнорируется

**Новый API:**
- принимает `int` (секунды) или **`float`** (с микросекундами — приятный бонус)
- возвращает объект с дефолтной таймзоной (или явно переданной)
- предсказуемое поведение, не требует «знать про @-багу»

**Применение:** парсинг Unix-timestamps из JSON-API, БД (`UNIX_TIMESTAMP()`), внешних сервисов.',
                'code_example' => '<?php
// До PHP 8.4
$dt = (new DateTimeImmutable("@1700000000"))
    ->setTimezone(new DateTimeZone("Europe/Moscow"));

// PHP 8.4+
$dt = DateTimeImmutable::createFromTimestamp(1700000000);
$dt = DateTimeImmutable::createFromTimestamp(1700000000.123); // микросекунды',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое функция request_parse_body() в PHP 8.4?',
                'answer' => '**`request_parse_body()`** (PHP **8.4+**) — ручной парсинг тела HTTP-запроса в форматах **`multipart/form-data`** и **`application/x-www-form-urlencoded`**, возвращающий **`array{array, array}`** (аналоги `$_POST` и `$_FILES`).

**Какую дыру закрывает:**

| HTTP-метод + Content-Type | До 8.4 | С 8.4 |
| --- | --- | --- |
| `POST + multipart` | **автоматически** в `$_POST`/`$_FILES` | то же |
| `POST + urlencoded` | автоматически в `$_POST` | то же |
| **`PUT + multipart`** | `$_POST` **пустой**, парсить вручную из `php://input` | `request_parse_body()` |
| **`PATCH + multipart`** | то же — вручную | `request_parse_body()` |
| `DELETE + multipart` | вручную | `request_parse_body()` |

**Сигнатура:**

```php
request_parse_body(?array \$options = null): array
// returns: [array \$body, array \$files]
```

**`\$options`:**

| Ключ | Что управляет |
| --- | --- |
| `post_max_size` | размер тела |
| `upload_max_filesize` | размер файла |
| `max_file_uploads` | количество файлов |
| `max_multipart_body_parts` | количество частей multipart |
| `max_input_vars` | количество переменных |
| `max_input_nesting_level` | глубина массивов |
| `upload_tmp_dir` | каталог для tmp-файлов |

**Канонический use-case:**

```php
// REST API: PUT /users/42 с multipart (для загрузки аватара одновременно с данными)
if (\$_SERVER["REQUEST_METHOD"] === "PUT") {
    [\$body, \$files] = request_parse_body();
    // \$body["name"] = "...", \$files["avatar"] = [...]
}
```

**До 8.4** для того же сценария:
- читать **`php://input`** руками
- парсить multipart-границу из `Content-Type: multipart/form-data; boundary=...`
- разбирать каждую часть, сохранять файлы в `tmp_dir` руками
- либо тянуть пакет `riverline/multipart-parser` или Symfony HttpFoundation

**Что НЕ парсит:**

- **JSON-тело** (`application/json`) — это `file_get_contents("php://input")` + `json_decode`/`json_validate`
- **raw binary** — `php://input` напрямую

**Frameworks:** Laravel/Symfony имеют свои парсеры (`Symfony\\Component\\HttpFoundation\\Request::createFromGlobals`), которые **сами** обрабатывают PUT/PATCH multipart через `php://input`. Новая встроенная функция позволяет **отказаться** от этих внутренних обходных путей.',
                'difficulty' => 4,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что изменилось в синтаксисе создания и использования объектов в PHP 8.4?',
                'answer' => 'PHP 8.4 разрешил **цепочку прямо после `new` без внешних скобок**.

**До 8.4** — обязательны внешние скобки:
```php
$id = (new User($name))->save();
$path = (new Config())->path;
```

**С 8.4:**
```php
$id = new User($name)->save();
$path = new Config()->path;
```

**Что разрешено в цепочке:**
- вызов **методов**: `new Foo()->bar()`
- обращение к **свойствам**: `new Foo()->prop`
- **индексирование**: `new Bar()[0]`
- цепочка из **нескольких** методов: `new Req("GET", $url)->withHeader(...)->send()`

**Что не меняется:** внутри по-прежнему создаётся новый объект, приоритет такой же, как у обычной цепочки. Это **синтаксический сахар**, никаких новых семантик.

**Где даёт выигрыш:** текучие (fluent) API, фабрики, разовые DTO, билдеры — везде, где раньше внешние скобки были визуальным шумом.',
                'code_example' => '<?php
// До PHP 8.4 — обязательны скобки
$id = (new User($name))->save();
$path = (new Config())->path;

// PHP 8.4+
$id = new User($name)->save();
$path = new Config()->path;

// Текучий API
$response = new Request("GET", $url)
    ->withHeader("Accept", "application/json")
    ->send();',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Почему в PHP 8.4 exit и die превратили из языковых конструкций в функции?',
                'answer' => 'До PHP 8.4 **`exit`** и **`die`** были **языковыми конструкциями** (как `echo`, `print`, `include`), а не функциями. Это приводило к ограничениям:

- нельзя было передать как **callable**: `array_walk($items, "exit")` — ошибка
- нельзя было использовать **first-class callable syntax**: `exit(...)` не работало
- сложно **замокать в тестах** — обычный мок-фреймворк не подменит конструкцию
- неконсистентно с другими «выходами»: `throw` стал выражением в 8.0, а `exit` оставался конструкцией

**В PHP 8.4** это полноценные функции с сигнатурой `exit(string|int $status = 0): never` (и `die` как алиас).

**Что даёт:**
- согласованность с остальным языком
- можно использовать в **функциях высшего порядка** и pipe-цепочках
- удобно в тестах: задизайнить через интерфейс/обёртку и подменить
- тип возврата **`never`** — статический анализ знает, что после `exit()` код недостижим',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Зачем в PHP 8.4 повысили cost у password_hash для PASSWORD_BCRYPT?',
                'answer' => '**Изменение:** дефолтный `cost` для **`PASSWORD_BCRYPT`** поднят с **10** до **12** (PHP **8.4+**).

**Что такое cost у bcrypt:**

- bcrypt — алгоритм с **настраиваемой стоимостью**: каждый `+1` к cost **удваивает** время хеширования (2^cost итераций)
- цель — сделать **brute-force паролей атакующим заведомо медленным**, при этом сохранив приемлемое время на стороне сервера

**Сравнение времён** (на типичном x86_64, 3 GHz):

| Cost | Время одного хеша | Сколько хешей в секунду атакующий с GPU |
| --- | --- | --- |
| 8 | ~10 ms | очень много |
| 10 (default до 8.3) | ~50-80 ms | много, brute-force старых хешей реален |
| **12 (default с 8.4)** | ~200-300 ms | существенно сложнее |
| 14 | ~1 секунда | DoS-риск на стороне сервера |

**Зачем поднимать:**
- железо за 10 лет (default `10` появился в **PHP 5.5, 2013**) **существенно быстрее**: ASIC/GPU для bcrypt дают тысячи хешей/сек
- **OWASP Password Storage Cheat Sheet** рекомендует cost **`12+`** на 2024+
- старое значение перестало быть «достаточно медленным» против современного атакующего

**Что это значит для вашего кода:**

1. **новые** пароли в 8.4+ хешируются с `cost=12` автоматически — ничего не меняем
2. **старые** хеши в БД остаются с прежним `cost` — они не «ломаются»
3. при следующем **успешном логине** старого пользователя вызовите **`password_needs_rehash()`** — функция вернёт `true` для пароля со старым cost
4. перехешируйте и сохраните

**Канонический паттерн миграции:**

```php
if (password_verify(\$password, \$user->password_hash)) {
    if (password_needs_rehash(\$user->password_hash, PASSWORD_BCRYPT)) {
        \$user->password_hash = password_hash(\$password, PASSWORD_BCRYPT);
        \$user->save();
    }
    // login successful
}
```

**Альтернатива bcrypt:** `PASSWORD_ARGON2ID` — современный memory-hard алгоритм, рекомендован OWASP **выше** bcrypt. PHP поддерживает с **7.3** при сборке с libargon2.

**Подводный камень bcrypt:** **обрезает пароль до 72 байт**. Длинные пароли молча усекаются. Если бизнес позволяет «парольные фразы» — берите `argon2id`.',
                'difficulty' => 4,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое функции array_first() и array_last() в PHP 8.5?',
                'answer' => '**`array_first()`** и **`array_last()`** (PHP 8.5+) — возвращают **первое и последнее значение** массива (или `null`, если массив пуст).

**Главное свойство — чистота:**
- **не двигают** внутренний указатель массива
- **не аллоцируют** новых массивов
- **не мутируют** исходный

**До 8.5** приходилось писать `reset()`/`end()`:
- они **мутируют** внутренний указатель — невидимый побочный эффект, легко создать баг при foreach по тому же массиву позже
- передача по значению — `reset($arr)` создаёт **копию** массива (для не-references)
- альтернатива `array_key_first()` + обращение по ключу — два вызова

**Поведение:**
- `array_first([])` → **`null`** (не warning, как у `reset` на пусто)
- работает и со **списками**, и с **ассоциативными** массивами
- значения по умолчанию нет (явно сравнивать с `null` или использовать `??`)

**Где применять:** получение первого/последнего элемента в коллекции без мутации — особенно полезно в **immutable-стиле** и функциональном коде.',
                'code_example' => '<?php
$users = ["a" => "Иван", "b" => "Аня", "c" => "Петя"];

// До PHP 8.5
$first = reset($users);  // "Иван", но указатель смещён
$last  = end($users);    // "Петя", указатель в конце

// PHP 8.5+ — чисто
$first = array_first($users); // "Иван"
$last  = array_last($users);  // "Петя"

array_first([]); // null',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что разрешил PHP 8.5 в константных выражениях относительно замыканий?',
                'answer' => '**В константных выражениях** теперь допустимы **статические замыкания** и **first-class callable syntax**.

**Что такое «константное выражение»** в PHP:
- значение **константы класса/интерфейса/enum**
- **дефолт параметра** функции/метода
- значение **свойства** (для нетипизированных или совместимых типов)
- **аргумент атрибута** `#[Attr(...)]`
- значение **константы через `define()`** в некоторых местах

**Эволюция:**

| Версия | Разрешено |
| --- | --- |
| < 8.1 | только литералы, `+ - * /`, обращение к константам |
| **8.1** | + **`new`** в инициализаторах (с ограничениями) |
| **8.4** | + property hooks с константными выражениями |
| **8.5** | + **`static fn() => ...`**, **first-class callables** `strtoupper(...)` |

**Что теперь работает:**

```php
class Config {
    const DEFAULT_TRANSFORMER = strtoupper(...);
    const VALIDATOR = static fn(string \$s) => strlen(\$s) > 0;
}

function process(
    string \$value,
    callable \$transform = strtoupper(...),  // 8.5+
): string {
    return \$transform(\$value);
}

#[Validates(static fn(\$v) => is_int(\$v))]
public string \$count;
```

**Ограничения:**
- замыкание должно быть **`static`** — нельзя захватывать `\$this`
- **`use (\$var)`** запрещён — нет переменных в скоупе
- first-class callable работает для **функций**, **статических методов**, методов **через имя класса**: `MyClass::method(...)`

**Зачем нужно:**
- **дефолт-функция** для DI: «по умолчанию используй `strtoupper`, можно подменить»
- **атрибут с валидатором**: декларативно описать правило на свойстве
- **constants библиотек**: `const DEFAULT_PARSER = MyParser::parse(...)`

**До 8.5** приходилось либо передавать **строку** имени функции (`"strtoupper"`) и потом `call_user_func`, либо хранить **отдельную фабрику**. Теперь — единый декларативный синтаксис.',
                'difficulty' => 4,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что нового в применении атрибутов появилось в PHP 8.5?',
                'answer' => 'PHP 8.5 расширил применимость атрибутов, закрыв дыры, оставшиеся с 8.0:

**1. Атрибуты на константах класса**
```php
class Config {
    #[\\Deprecated("используйте VERSION_V2")]
    const VERSION = "1.0";
}
```
Раньше пометить устаревшую константу можно было только PHPDoc-тегом.

**2. `#[\\Override]` на свойствах**
Полезно вместе с **property hooks** (PHP 8.4) — зафиксировать факт переопределения свойства в наследнике, чтобы рефакторинг родителя ломал компиляцию, а не молча создавал orphan-свойство.

**3. `#[\\Deprecated]` на трейтах и константах**
Раньше работал только на функциях, методах, классах. Теперь генерирует `E_USER_DEPRECATED` для всех видов «вызовов» устаревшего API единообразно.

**Зачем все эти точечные расширения:** единый рантайм-механизм deprecation вместо смеси PHPDoc-тегов, `trigger_error()` и комментариев в README.',
                'code_example' => '<?php
class Config {
    #[\\Deprecated("используйте VERSION_V2")]
    const VERSION = "1.0";       // атрибут на константе (PHP 8.5)
}

class Admin extends User {
    #[\\Override]
    public string $role = "admin"; // Override на свойстве (PHP 8.5)
}

#[\\Deprecated("используйте трейт LoggableV2")]
trait Loggable {}                  // Deprecated на трейте',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Какие важные deprecation-ы пришли в PHP 8.5?',
                'answer' => '**Подтверждённое в UPGRADING для 8.5:**
- завершение **case-веток** через **точку с запятой** вместо двоеточия (`case 1; ... break;` — синтаксический deprecation)
- продвижение **PIE** как рекомендованного инструмента установки расширений вместо устаревшего **PECL**
- точечные изменения в `date`/`curl`

**В RFC обсуждалось, но в финальный 8.5 в виде `E_DEPRECATED` НЕ вошло:**
- `__sleep`/`__wakeup` (в пользу `__serialize`/`__unserialize` из 7.4)
- backtick-исполнение `` `cmd` `` (в пользу `proc_open` / `Symfony\\Process`)
- нестандартные имена приведений: `(boolean)`, `(integer)`, `(double)` (в пользу канонических `(bool)`, `(int)`, `(float)`)

**Совет для подготовки кода:** ориентироваться на **релизные заметки** и `UPGRADING.md` в репозитории php-src, а не только на RFC-странички — между «принят RFC» и «попал в релиз» часто проходит цикл-два.',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Зачем в PHP 8.2 ввели новое расширение Random с классом Randomizer?',
                'answer' => '**Проблемы старого API:**
- `rand()`/`mt_rand()` используют **единый глобальный движок**
- сложно **подменить в тестах** (нет инъекции)
- невозможно иметь **несколько независимых потоков** случайности с воспроизводимыми seed
- `srand()` сидирует **глобал**, что ломает другие части программы

**Что даёт `ext-random`:**
- класс **`Random\\Randomizer`** — инкапсулирует состояние генератора
- принимает в конструктор **движок** через интерфейс `Random\\Engine`

**Доступные движки:**

| Движок | Назначение |
|---|---|
| `Random\\Engine\\Mt19937` | Mersenne Twister, совместим со старым `mt_rand` |
| `Random\\Engine\\Xoshiro256StarStar` | быстрый современный PRNG |
| `Random\\Engine\\PcgOneseq128XslRr64` | PCG, хорошая статистика |
| `Random\\Engine\\Secure` | **CSPRNG** (используется когда нужна крипто-безопасность) |

**Практическая польза:**
- **детерминированный seed** для воспроизводимых тестов (`new Mt19937(42)`)
- **несколько независимых** потоков случайности
- **Secure engine** — единый API для крипто и для не-крипто кейсов',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем атрибут #[\\Deprecated] из PHP 8.4 лучше пометки @deprecated в PHPDoc?',
                'answer' => '| Свойство | `@deprecated` в PHPDoc | `#[\\Deprecated]` (PHP 8.4+) |
|---|---|---|
| Видимость | только **IDE** и статанализаторы | часть **рантайма** |
| Требует исходников | да (PHPDoc) | нет (хранится в opcache) |
| Поведение при вызове | ничего | **`E_USER_DEPRECATED`** |
| Формат сообщения | произвольный текст | стандартный (since + message) |
| Можно поймать в логе/тесте | нет | да (`set_error_handler`) |
| Включается в opcache.preload | нет | да |

**Что даёт атрибут:**
- **унифицированный формат** сообщений в библиотеках экосистемы
- легко **поймать в CI** через `set_error_handler` или `expectDeprecation()` в PHPUnit
- агрегируется в **Sentry/Bugsnag** как обычные ошибки уровня notice
- видно даже если автор кода скрыл PHPDoc через минификацию

**Применение в библиотеках:** Symfony, Doctrine, Laravel постепенно мигрируют с собственных `trigger_error(..., E_USER_DEPRECATED)` на атрибут.

**Параметры:** `since` (версия), `message` (что использовать взамен).',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'В чём разница между Function JIT и Tracing JIT в PHP 8?',
                'answer' => '**JIT в PHP** — компиляция опкодов **`opcache`** в нативный машинный код **во время** выполнения, через **DynASM** (написан Дмитрием Стоговым). Два режима:

| | **Function JIT** | **Tracing JIT** |
| --- | --- | --- |
| Что компилирует | **функцию целиком** при первом вызове | **горячие трассы** (sequences of opcodes), пересекающие границы функций |
| Когда срабатывает | **сразу** при первом вызове | по достижении порога «горячести» (counter) |
| Что знает о runtime | ничего | **профиль** реального исполнения |
| Оптимизации | базовые (constant folding, type inference на сигнатуре) | агрессивные (inlining, dead code elim, type specialization) |
| Размер кэш-кода | больше (компилирует ВСЁ) | компактнее (только горячее) |
| Эффект на CPU-bound | скромный, +10-20% | **в разы**, до 3-5× |
| Эффект на I/O-bound веб | ~0% | ~0% (узкое место не в CPU) |

**Управление через `opcache.jit`:**

| Значение | Режим |
| --- | --- |
| `tracing` или `1254` | **Tracing JIT** — рекомендован |
| `function` или `1205` | **Function JIT** |
| `0` или `off` | выключен |
| `disable` | полностью запрещён (быстрее старта) |

**Что значит четырёхзначное число `1254`:**

`CRTO` — четыре цифры, по одной на каждый аспект:

| Позиция | Что управляет | Для `1254` |
| --- | --- | --- |
| **C** (1) | CPU-specific opts | `1` = AVX, `0` = generic |
| **R** (2) | стиль регистров | `2` = global register allocation |
| **T** (5) | trigger | `5` = on hot trace |
| **O** (4) | optimization | `4` = inlining + specialization |

**Дополнительные параметры:**

- `opcache.jit_buffer_size` — память под скомпилированный код (`128M` типично)
- `opcache.jit_max_root_traces` — лимит количества трасс
- `opcache.jit_hot_loop` / `opcache.jit_hot_func` — пороги «горячести»

**Когда JIT даёт реальный выигрыш:**

- **CPU-intensive PHP** (без I/O): обработка изображений в pure PHP, математика, шифрование
- **долгоживущие воркеры** (Octane/RoadRunner) — трассы успевают накопить «горячесть»
- **ML/DSP** в PHP

**Когда JIT почти не виден:**

- классический FPM с обычным веб-приложением — узкое место в БД и сети
- короткие CLI-скрипты — не успевают «прогреть» трассы

**Подводные камни:**

- JIT в **8.0** иногда крашил процесс при сложных трассах — многие выключали в проде через `opcache.jit_buffer_size=0`
- к **8.3-8.4** стабильность хорошая, но **бенчмарк перед включением** — обязателен
- JIT **не помогает** регексам с обратными ссылками и рекурсии — PCRE сам управляет своим JIT',
                'difficulty' => 4,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое атрибуты PHP 8 (Attributes)?',
                'answer' => '**Attributes** (PHP **8.0+**) — структурированные метаданные на элементах кода через синтаксис **`#[Name(args)]`**. Это **встроенный в язык** механизм аннотаций, который **парсится компилятором**, а не комментарии-аннотации в PHPDoc.

**Где можно вешать атрибуты:**

| Цель | Константа `Attribute::TARGET_*` | С версии |
| --- | --- | --- |
| Класс | `TARGET_CLASS` | 8.0 |
| Метод | `TARGET_METHOD` | 8.0 |
| Свойство | `TARGET_PROPERTY` | 8.0 |
| Параметр функции/метода | `TARGET_PARAMETER` | 8.0 |
| Функция | `TARGET_FUNCTION` | 8.0 |
| Константа класса | `TARGET_CLASS_CONSTANT` | 8.1 |
| Все сразу | `TARGET_ALL` | — |

**Сравнение с PHPDoc-аннотациями (Doctrine, Symfony до 6):**

| | PHPDoc-аннотация | Attribute |
| --- | --- | --- |
| Где живёт | в **комментарии** `/** @Route(...) */` | в коде `#[Route(...)]` |
| Парсится | пакетом `doctrine/annotations` через **regex** | компилятором PHP |
| Проверка во время компиляции | нет (опечатки тихие) | да (ошибки на этапе компиляции) |
| Производительность | парсинг при каждом вызове Reflection | хранится в opcache |
| IDE-поддержка | хорошая через плагины | **нативная** |
| Доступ в рантайме | через regex-парсер | через `ReflectionClass::getAttributes()` |

**Жизненный цикл атрибута:**

1. Объявляется класс с `#[Attribute]` — он становится «атрибут-классом»
2. Применяется через `#[MyAttr(args)]` на цель
3. Получение через Reflection: `getAttributes()` возвращает массив **`ReflectionAttribute`**
4. **`->newInstance()`** на `ReflectionAttribute` **создаёт объект** атрибута — **только** в этот момент исполняется конструктор

**Это важно:** атрибуты — **lazy**. Конструктор не зовётся при загрузке файла, только при явном `newInstance()`.

**Параметры `Attribute`:**
- `Attribute::IS_REPEATABLE` — можно повесить **несколько** атрибутов одного типа на одну цель
- битовая маска `TARGET_*` ограничивает, **куда** атрибут можно вешать

**Примеры применения в экосистеме:**

| Фреймворк | Атрибуты |
| --- | --- |
| Symfony 6+ | `#[Route]`, `#[AsCommand]`, `#[AsMessageHandler]`, `#[Required]`, `#[Target]` |
| Laravel | `#[Scope]` (новые Eloquent), `#[ObservedBy]`, `#[CollectedBy]` |
| Doctrine ORM | `#[Entity]`, `#[Column]`, `#[ManyToOne]` |
| PHPUnit | `#[Test]`, `#[DataProvider]`, `#[Depends]`, `#[Group]` |
| PHP native | `#[\\Override]`, `#[\\Deprecated]`, `#[\\SensitiveParameter]`, `#[\\NoDiscard]`, `#[\\AllowDynamicProperties]` |',
                'code_example' => '<?php
#[Attribute(Attribute::TARGET_METHOD)]
class Route {
    public function __construct(
        public string $path,
        public string $method = "GET",
    ) {}
}

class UserController {
    #[Route("/users", method: "GET")]
    public function index() {}

    #[Route("/users/{id}", method: "POST")]
    public function update(int $id) {}
}

// Чтение
$ref = new ReflectionClass(UserController::class);
foreach ($ref->getMethods() as $method) {
    foreach ($method->getAttributes(Route::class) as $attr) {
        $route = $attr->newInstance();
        echo "$route->method $route->path\\n";
    }
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое constructor property promotion в PHP 8 простыми словами?',
                'answer' => '**Сокращённый синтаксис конструктора** (PHP 8.0+): **одна строка** вместо трёх — объявить свойство, принять параметр, присвоить его.

**Как работает:** модификатор видимости **прямо в параметре конструктора** (`public string $name`) автоматически:
- создаёт свойство `$this->name` с этим типом
- принимает параметр
- присваивает `$this->name = $name`

**Можно комбинировать с:**
- **`readonly`** (PHP 8.1+) — иммутабельные DTO одной строкой
- **типами**, значениями по умолчанию, **named arguments** на вызове

**Ограничения:** нельзя в `abstract`-конструкторах и интерфейсах, нельзя для `static`-свойств.',
                'code_example' => 'class User {
    public function __construct(
        public string $name,
        public int $age,
    ) {}
}
$u = new User("Иван", 30);
echo $u->name; // "Иван"',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Зачем нужен nullsafe-оператор ?-> и как он работает?',
                'answer' => '**`?->`** (PHP 8.0+) — безопасное обращение к свойству или методу через цепочку, в которой что-то может быть `null`.

**Как работает:** `$user?->profile?->avatar->url`
- если `$user` или `$user->profile` равны `null` — вся цепочка **сразу возвращает `null`**
- без него получили бы `Error: Attempt to read property on null`

**Что заменяет:** вложенные `if ($x !== null)` или каскад из `??`.

**Ограничения:**
- **не работает на запись** — `$obj?->prop = 1` запрещено
- **не работает со статикой** — нет `?::method`
- short-circuit: при `null` остаток цепочки не вычисляется (это важно для побочных эффектов)',
                'code_example' => '// Старый стиль
$avatar = null;
if ($user !== null && $user->profile !== null) {
    $avatar = $user->profile->avatar;
}

// PHP 8.0+
$avatar = $user?->profile?->avatar;',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем отличаются ключевые версии PHP 5.6 → 7.x → 8.x?',
                'answer' => 'Главные вехи (то, что чаще всего спрашивают на собесе):

**PHP 5.6 (2014)** — последняя 5.x. Variadic `...`, spread в вызовах, constant expressions.

**PHP 7.0 (2015)** — переписан движок (Phpng): **2× скорость**, **2× меньше памяти** против 5.6. **Скалярные типы** (`int`, `string`), **return types**, **`??`** (null coalescing), **`<=>`** (spaceship), anonymous classes, **`Throwable`** как корень `Error`/`Exception`.

**PHP 7.1** — **nullable-типы** (`?int`), `void`, `iterable`, **multi-catch** (`catch (A|B $e)`).

**PHP 7.2** — `object` тип, Sodium как ext, deprecation `each()`.

**PHP 7.3** — стабильные heredoc, `list()` с ключами.

**PHP 7.4** — **typed properties** (`public int $age`), **arrow functions** `fn() =>`, spread в массиве, **FFI**, **preloading** в opcache, **`??=`**, covariant return / contravariant param.

**PHP 8.0 (2020)** — **JIT**, **named arguments**, **attributes** `#[...]`, **constructor property promotion**, **`match`**, **nullsafe `?->`**, **union types** `int|string`, `throw` как выражение, `WeakMap`, **`str_contains`/`str_starts_with`/`str_ends_with`**.

**PHP 8.1** — **`enum`** (pure + backed), **`readonly`** properties, **first-class callable syntax** `strlen(...)`, **`never`** type, **intersection types** `A&B`, **`new` в инициализаторах**, **`Fiber`**.

**PHP 8.2** — **`readonly` классы**, **DNF**-типы `(A&B)|null`, `true`/`false`/`null` standalone, deprecation **динамических свойств**.

**PHP 8.3** — **typed class constants**, **`json_validate()`**, **`#[\\Override]`**, динамический доступ к константам.

**PHP 8.4** — **property hooks**, **asymmetric visibility** (`public private(set)`), **`array_find`/`array_all`/`array_any`**, цепочка после `new` без скобок.

**PHP 8.5** — `array_first`/`array_last`, атрибуты на константах, расширение применимости `#[\\Override]` и `#[\\Deprecated]`.',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Какие проблемы могут возникнуть при переходе проекта на новую мажорную версию PHP?',
                'answer' => 'Главные категории проблем при апгрейде:

**1. BC breaks из RFC**
- удалённые/изменённые функции: `each`, `create_function`, `mb_strrpos` с offset, `money_format`
- изменённое поведение операторов: `1 + "5 apples"` в 8.0 — `Warning`, в 9.0 — ошибка

**2. Строже типизация**
- внутренние функции теперь бросают **`TypeError`** вместо тихих warnings
- `strlen(null)` в 8.1+ — deprecation, в 9.0 — fatal

**3. Новые зарезервированные слова**
- `readonly`, `enum`, `never`, `match` как идентификаторы перестают работать
- ломаются методы/константы с такими именами

**4. Удалённые расширения**
- `mysql_*` (5 → 7), `ereg`, `mcrypt` (7.2)
- `oci8` отдельной установкой

**5. Динамические свойства (8.2)**
- deprecation на присвоение свойства, не объявленного в классе
- решения: атрибут **`#[AllowDynamicProperties]`** или явные свойства/DTO

**6. opcache/JIT**
- старые prefab-конфиги могут уронить performance или съесть память
- JIT в 8.0 в типовом веб-проде даёт ~0% и иногда крашит — обычно выключают (`opcache.jit_buffer_size=0`)

**7. Composer-зависимости**
- `composer outdated` / `composer why-not php:8.x`
- часто застревают: Symfony <5, Doctrine <2.10, старые сторонние SDK

**8. Driver-несовместимость**
- `pdo_mysql`/`mysqlnd` отдают новые типы (BIGINT теперь `int`, не `string` в 8.1+)
- ломается код, ожидавший строки

**9. PECL-расширения и opcache-shared-memory** — иногда отстают на месяцы.

**Стратегия миграции:**
1. **`rector --set php:81`** + PHPStan/Psalm на максимум
2. CI с **матрицей версий** PHP
3. **Канареечный деплой**
4. Обязательные **e2e + smoke + load**-тесты — часть проблем (память, GC, JIT) видна только под нагрузкой',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
        ];
    }
}
