<?php

namespace Database\Seeders\Data\Categories\Laravel;

class OctaneHorizon
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Octane? Какие у него плюсы и подводные камни?',
                'answer' => '**`Laravel Octane`** — пакет, заменяющий PHP-FPM на **долгоживущие воркеры**, которые держат приложение **в памяти между запросами** вместо перезагрузки фреймворка.

**Базовая идея:**

- **PHP-FPM:** каждый запрос → fork → autoload → providers → routes → handler → exit. Bootstrap ~10-50 ms на каждый запрос.
- **Octane:** воркер стартует **один раз**, фреймворк уже в памяти, каждый запрос — только handler. **RPS x3-5**.

**Поддерживаемые серверы:**

| Сервер | Язык runtime | Особенности |
|---|---|---|
| **`Swoole`** | C extension | Самый зрелый, **task workers**, корутины, `octane:install --server=swoole` |
| **`RoadRunner`** | Go | Чистый бинарь без PHP-расширения, `Goridge` протокол |
| **`FrankenPHP`** | Go (Caddy) | HTTP/2, HTTP/3, worker mode, новейший |

**Главные подводные камни (state leakage):**

1. **Утечки памяти** — переменные класса/`use static` накапливаются между запросами. Лечение — `--max-requests=500`.
2. **Singleton-ы переживают** между запросами — `Auth::user()` в singleton-е утечёт следующему запросу. Используй **`$this->app->scoped()`** для per-request зависимостей.
3. **Глобальные/статические свойства** — `static $cache = []` будет расти бесконечно.
4. **Long-lived DB connections** — отваливаются по `wait_timeout` (`MySQL server has gone away`). Решение: **`DB::reconnect()`** на исключение, **PDO ATTR_PERSISTENT = false**, либо `--max-requests`.
5. **Фасады закешированы** — `Config::set()` в одном запросе **видит** следующий запрос; нужно `Octane::tick()` или сброс state в `RequestTerminated`.

**Lifecycle hooks** (`config/octane.php → listeners`):

- `WorkerStarting` — поднялся воркер (один раз).
- `RequestReceived` — пришёл запрос.
- `RequestHandled` — обработан.
- `RequestTerminated` — последний шанс сбросить state.
- `WorkerErrorOccurred` / `WorkerStopping`.',
                'code_example' => '# Установка
composer require laravel/octane
php artisan octane:install --server=roadrunner  # или swoole, frankenphp
php artisan octane:start --workers=8 --max-requests=500

# --max-requests - перезапуск воркера после N запросов (страховка от утечек)
# --task-workers=2 - только для Swoole, для async tasks

<?php
// === Per-request state - НЕ singleton, а scoped ===
// AppServiceProvider::register
$this->app->scoped(RequestContext::class, function ($app) {
    return new RequestContext($app->make(Request::class));
});
// Octane сбросит scoped-биндинг между запросами автоматически

// === Очистка состояния через listener ===
// config/octane.php
"listeners" => [
    RequestTerminated::class => [
        FlushUploadedFiles::class,
        FlushTemporaryContainerInstances::class,
        // свой listener: Auditor::reset(), TenantContext::flush() и т.п.
    ],
],

// === Deploy без даунтайма ===
// php artisan octane:reload  # грейсфул-перезапуск воркеров',
                'code_language' => 'bash',
                'difficulty' => 5,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Horizon?',
                'answer' => '**`Laravel Horizon`** — пакет для управления **Redis-очередями** в Laravel: дашборд, балансировка воркеров, метрики, мониторинг failed jobs, теги задач.

**Что даёт поверх `queue:work`:**

- **Web UI** — `/horizon` — список pending/processing/failed/recent.
- **Auto-scaling** воркеров — стратегии `simple`/`auto`/`false`.
- **Метрики** — throughput, runtime, failed rate в реальном времени.
- **Tags** — у каждого Job свой тег (например, `App\\Models\\User:42`) для фильтрации.
- **Failed jobs** — детальный stack trace + retry одним кликом.
- **Notifications** — Slack/SMS при превышении wait time.
- **Graceful deploy** — `horizon:terminate` дорабатывает текущие jobs и перезапускает.

**Ограничения:**

- **Только Redis** — для SQS/database/RabbitMQ нужен обычный `queue:work` (или RoadRunner Jobs).
- **Supervisor для самого Horizon** — процесс должен под чем-то жить (systemd/supervisord).
- **Auth dashboard** — по умолчанию доступ только локально; для прода — `Gate::define(\'viewHorizon\', ...)` в `HorizonServiceProvider`.

**Стратегии `balance`:**

| Стратегия | Что делает |
|---|---|
| **`simple`** | Делит `maxProcesses` поровну между всеми очередями |
| **`auto`** | Перераспределяет процессы динамически по нагрузке (wait time) |
| **`false`** | Каждая очередь имеет фиксированное число процессов |

**Параметры supervisor-а:**

- `connection` — драйвер очереди (`redis`).
- `queue` — массив очередей (приоритет по порядку).
- `balance`, `minProcesses`, `maxProcesses`.
- `tries`, `timeout`, `memory`, `nice`.
- `balanceMaxShift` / `balanceCooldown` — скорость auto-balance.',
                'code_example' => '# Установка
composer require laravel/horizon
php artisan horizon:install
php artisan horizon

# config/horizon.php
"environments" => [
    "production" => [
        "supervisor-1" => [
            "connection"      => "redis",
            "queue"           => ["high", "default", "emails", "notifications"],
            "balance"         => "auto",
            "minProcesses"    => 1,
            "maxProcesses"    => 20,
            "balanceMaxShift" => 1,
            "balanceCooldown" => 3,
            "tries"           => 3,
            "timeout"         => 60,
            "memory"          => 128,
        ],
    ],
],

# Deploy hook (zero-downtime)
php artisan horizon:terminate     # текущие jobs дорабатывают, потом restart

# Авторизация dashboard - HorizonServiceProvider::gate
Gate::define("viewHorizon", function ($user) {
    return in_array($user->email, ["admin@example.com"]);
});',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Какие подводные камни у Octane по сравнению с обычным FPM?',
                'answer' => '**Главное отличие:** Octane держит фреймворк в памяти между запросами. Это даёт **x3-5 RPS**, но ломает базовое допущение PHP — «каждый запрос с чистого листа».

**Категории подводных камней:**

**1. State leakage (утечка данных между пользователями):**

- **Singleton-ы переживают** — `Auth::user()`, request-scoped сервисы протекают.
- **Статические свойства** — `static $cache = []` шарится между всеми запросами этого воркера.
- **Глобальные переменные** — `$GLOBALS`, суперглобальные кеши пакетов.
- **Закешированные фасады** — `Cache::set()` в одном запросе виден следующему.

**Симптом:** юзер A видит данные юзера B.

**2. Утечки памяти:**

- **Накопление в массивах** статиков, не-`weak`-referenced listeners.
- **Memory leak в C-расширениях** (особенно Swoole).
- **Большие объекты в singleton** — закачали 100 МБ в кеш, воркер вырос до GB.

**Лечение:** `--max-requests=500` — перезапуск воркера после N запросов.

**3. Долгоживущие соединения:**

- **MySQL `wait_timeout`** — соединение в idle 8 часов → `gone away`. Решение: `DB::reconnect()` на исключение, либо `--max-requests`.
- **Redis** — то же самое, но Predis сам делает retry.
- **PDO `ATTR_PERSISTENT = true`** — нельзя, ломает scoped lifecycle.

**4. Блокирующие операции:**

- Octane **синхронный** в рамках одного воркера — `sleep(10)` блокирует **этот** воркер целиком на 10 секунд.
- Для async — **Swoole task workers** или **`Octane::concurrently([...])`** для параллельных HTTP/БД-вызовов.

**Запрещённые паттерны в коде:**

| Антипаттерн | Что делать |
|---|---|
| Хранить `Auth::user()` в singleton | `$this->app->scoped()` |
| `static array $cache = []` в моделях/сервисах | Сбрасывать в `RequestTerminated` listener |
| Изменять контейнер из контроллеров (`app()->bind(...)`) | Только в Service Provider |
| `Config::set(...)` в контроллере | Сбросить в listener или не использовать |

**Lifecycle hooks** для сброса state:

- `RequestReceived` — пришёл запрос (инициализация per-request).
- `RequestTerminated` — последний шанс сбросить state (чистим static, reconnect БД, flush tenant context).
- `WorkerStarting` / `WorkerStopping` — старт/останов воркера.',
                'code_example' => '<?php
// === Антипаттерн в Octane: статика накапливается ===
class CartHolder {
    public static array $items = [];   // ❌ переживёт ВСЕ запросы воркера
}

// === Правильно: scoped binding ===
// AppServiceProvider::register
$this->app->scoped(CartHolder::class, fn () => new CartHolder());

// === Сброс state через listener ===
// config/octane.php
"listeners" => [
    \\Laravel\\Octane\\Events\\RequestTerminated::class => [
        \\Laravel\\Octane\\Listeners\\FlushUploadedFiles::class,
        \\Laravel\\Octane\\Listeners\\FlushTemporaryContainerInstances::class,
        \\App\\Listeners\\FlushTenantContext::class,
        \\App\\Listeners\\ReconnectDatabaseIfBroken::class,
    ],
],

// === Свой listener сброса tenant контекста ===
class FlushTenantContext {
    public function handle(RequestTerminated $event): void {
        TenantContext::reset();
        Auditor::flushBuffer();
        // принудительный reconnect если что-то случилось
        try { DB::connection()->getPdo(); }
        catch (\\Throwable) { DB::reconnect(); }
    }
}

// === Параллельные операции через Octane::concurrently ===
[$users, $posts, $stats] = Octane::concurrently([
    fn () => User::all(),
    fn () => Post::published()->get(),
    fn () => Stats::lastMonth(),
], waitTimeoutInSeconds: 5);',
                'code_language' => 'php',
                'difficulty' => 5,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как работает Laravel Horizon и какие метрики он даёт?',
                'answer' => '**`Horizon`** — дашборд и супервизор для **Redis-очередей**. Конфигурируется в **`config/horizon.php`**.

**Архитектура:**

- **Master process** — `php artisan horizon` запускает мастер.
- **Supervisor(s)** — мастер форкает по одному supervisor-у на каждую запись из `environments[env].supervisor-N`.
- **Workers** — каждый supervisor поднимает 1..N PHP-воркеров (как `queue:work`) и **балансирует** их по очередям.
- **Redis** — отдельный prefix `horizon:` для метрик и состояния супервизоров.

**Стратегии `balance`:**

| Стратегия | Что делает |
|---|---|
| **`simple`** | Делит `maxProcesses` поровну между очередями |
| **`auto`** | Перераспределяет процессы по wait time каждой очереди |
| **`false`** | Фиксированное число процессов per queue |

**Параметры auto-balance:**

- `balanceMaxShift` — макс. число процессов, перемещаемых за раз.
- `balanceCooldown` — пауза между ребалансировками (секунд).
- `minProcesses` / `maxProcesses` — нижняя/верхняя граница.

**Что показывает дашборд `/horizon`:**

| Раздел | Метрики |
|---|---|
| **Dashboard** | Jobs per minute, max wait, total jobs, recent failed |
| **Pending Jobs** | Очередь по приоритетам, текущий wait time |
| **Completed/Failed/Silenced** | История jobs с тегами |
| **Metrics → Queue** | Throughput, runtime per queue |
| **Metrics → Job** | По имени класса — runtime distribution, frequency |
| **Recent Jobs** | Live-таблица последних обработанных |

**Deploy:**

- **`php artisan horizon:terminate`** — мастер посылает SIGTERM всем воркерам, они дорабатывают текущий job и завершаются. Supervisor (systemd) перезапускает мастера с новым кодом — **zero downtime**.
- **`horizon:pause`** / **`horizon:continue`** — приостановить/возобновить обработку.
- **`horizon:status`** — статус для health check.

**Notifications** (`config/horizon.php → notifications`):

- `waits` — алёрт, если очередь ждёт >N секунд.
- Slack/SMS/email каналы.',
                'code_example' => '<?php
// config/horizon.php - полная конфигурация
return [
    "environments" => [
        "production" => [
            "default-supervisor" => [
                "connection"      => "redis",
                "queue"           => ["high", "default", "emails"],
                "balance"         => "auto",
                "minProcesses"    => 2,
                "maxProcesses"    => 20,
                "balanceMaxShift" => 1,
                "balanceCooldown" => 3,
                "tries"           => 3,
                "timeout"         => 60,
                "memory"          => 128,
                "nice"            => 0,
            ],

            // отдельный supervisor под тяжёлые long-running jobs
            "long-running" => [
                "connection"   => "redis",
                "queue"        => ["exports", "imports"],
                "balance"      => "simple",
                "maxProcesses" => 4,
                "timeout"      => 600,
                "memory"       => 512,
            ],
        ],
    ],

    "waits" => [
        "redis:default" => 60,        // алёрт, если default ждёт > 60 сек
        "redis:emails"  => 30,
    ],
];

# CLI
php artisan horizon           # старт мастера (под systemd/supervisord)
php artisan horizon:status    # для health probe
php artisan horizon:terminate # graceful restart на деплое
php artisan horizon:pause     # пауза обработки (но не приёма)
php artisan horizon:continue
php artisan horizon:snapshot  # сохранить метрики (по cron каждые 5 минут)',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое RoadRunner и за счёт чего он быстрее PHP-FPM?',
                'answer' => '**`RoadRunner`** — PHP application server и менеджер процессов, написанный **на Go**. Держит **пул PHP-воркеров живыми** и передаёт им запросы по бинарному протоколу **`Goridge`**.

**Откуда выигрыш в скорости:**

| | `PHP-FPM` | `RoadRunner` |
| --- | --- | --- |
| Жизненный цикл | Каждый запрос — **новый процесс**, заново bootstrap фреймворка | Воркер живёт долго, фреймворк загружен **один раз** |
| Bootstrap (autoload, providers, routes) | **На каждый запрос** (~10–50 ms на Laravel) | **Один раз** при старте воркера |
| RPS на типовом эндпоинте | x | **3–5× выше** |

**Что меняется в коде:**

- `singleton`-ы и **статические свойства переживают** между запросами — нужно следить за **state leakage** (запрос N+1 может увидеть данные запроса N).
- Per-request зависимости — через **`$this->app->scoped(...)`** (Octane сбрасывает scoped-биндинги между запросами).
- Не использовать `dd()`/`echo` — они ломают pipes-протокол Goridge (см. карточку про Goridge).

**Экосистема:** `Laravel Octane` — официальная интеграция с RoadRunner (а также `Swoole` и `FrankenPHP`). Команды `octane:start`, `octane:reload`, `octane:status`.',
                'code_example' => '# Установка RoadRunner + Octane
composer require laravel/octane spiral/roadrunner-cli
php artisan octane:install --server=roadrunner

# Запуск
php artisan octane:start --server=roadrunner --workers=8 --max-requests=500
# --max-requests - перезапустить воркер после N запросов (страховка от утечек)

# .rr.yaml - конфиг RoadRunner
version: "3"
server:
  command: "php artisan octane:start --server=roadrunner"
http:
  address: 0.0.0.0:8080
  pool:
    num_workers: 8
    max_jobs: 500    # перезапуск после 500 запросов
    max_memory: 256  # MB - перезапуск при превышении
    debug: false     # true в dev - новый воркер на каждый запрос

# В коде - для per-request state: scoped, НЕ singleton
$this->app->scoped(RequestContext::class);',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое протокол Goridge и какие каналы связи он поддерживает?',
                'answer' => '**`Goridge`** — бинарный протокол, по которому Go-сервер `RoadRunner` общается с PHP-воркерами.

**Каналы связи (`relay` в `.rr.yaml`):**

- **`pipes`** — `stdin`/`stdout`, **дефолт**. Ничего настраивать не надо, подходит большинству.
- **`tcp`** — TCP-сокет. Позволяет разнести RoadRunner и пул PHP-воркеров по **разным контейнерам/машинам** (k8s sidecar).
- **`unix`** — Unix-сокет. Самая быстрая **локальная** связь.

**Главное правило безопасности `pipes`:** PHP-воркер общается с RoadRunner через `STDOUT`. **Любой `echo`, `var_dump`, `print_r`, warning или `die(\'debug\')` в STDOUT повреждает фрейм протокола** — RoadRunner получит мусор и убьёт воркер.

**Что можно вместо:**

- `error_log(\'debug\')` — уходит в `STDERR`.
- `\\Log::info(\'debug\')` — в `storage/logs/laravel.log`.
- `fwrite(STDERR, \'...\')` — явно в STDERR.

RoadRunner 2.0+ **автоматически** редиректит STDOUT в STDERR (страховка), но опираться на это в продакшен-коде нельзя. В Octane есть свой `dump()`, который безопасен.',
                'code_example' => '# .rr.yaml - выбор канала связи (relay)
server:
  command: "php worker.php"
  relay: "pipes"           # дефолт: stdin/stdout
  # relay: "tcp://127.0.0.1:7000"  # TCP - если воркеры в отдельных контейнерах
  # relay: "unix:///var/run/rr.sock" # Unix socket - быстрая локальная связь

# В PHP коде категорически НЕЛЬЗЯ:
echo "debug";           # ломает pipes-протокол
var_dump($x);           # то же
print_r($x);            # то же

# Можно:
error_log("debug");     # уходит в STDERR
\\Log::info("debug");    # уходит в logs/laravel.log
fwrite(STDERR, "...");  # явно в STDERR',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое PHP Worker в RoadRunner и как выглядит его жизненный цикл?',
                'answer' => '**PHP Worker** — обычный PHP-скрипт, в котором через библиотеку **`Spiral\\RoadRunner`** крутится бесконечный цикл `while waitRequest()`.

**Жизненный цикл одного воркера:**

1. **Старт** — `require autoload.php`, загрузка фреймворка (bootstrap **один раз**).
2. **Цикл `while (true)`**:
   - `waitRequest()` — блокируется, ждёт следующий HTTP-запрос от RoadRunner.
   - Конвертация в **PSR-7** через `HttpWorker` (или в `Symfony Request` через прослойку).
   - Обработка (роутинг → контроллер → ответ).
   - `respond($psrResponse)` — отдать ответ.
3. **Между итерациями** — скрипт **остаётся в памяти** со всем загруженным фреймворком (вот откуда экономия на bootstrap).

**Обработка ошибок:**

- При **исключении** надо позвать `$worker->error($message)` — иначе RoadRunner посчитает воркера сломанным и **kill-нет** (и поднимет новый).
- При **fatal/segfault** воркер падает — RoadRunner спокойно поднимает замену из пула.
- По достижению `max_jobs` / `max_memory` воркер **грейсфул-завершится** — пул автоматически заменит.

**Когда писать руками:** при работе с Laravel **`Octane` делает всю обвязку сам** в `octane:start`. Понимать механику нужно для отладки и для не-Laravel сценариев (бэкенды на чистом RR).',
                'code_example' => '<?php
// Простейший воркер без Laravel - чтобы понять механику
require __DIR__ . "/vendor/autoload.php";

use Spiral\\RoadRunner;
use Nyholm\\Psr7;

$worker = RoadRunner\\Worker::create();
$psrFactory = new Psr7\\Factory\\Psr17Factory();
$psr7 = new RoadRunner\\Http\\PSR7Worker($worker, $psrFactory, $psrFactory, $psrFactory);

while (true) {
    try {
        $request = $psr7->waitRequest();
        if ($request === null) break; // сигнал на остановку
    } catch (\\Throwable $e) {
        $psr7->respond(new Psr7\\Response(400));
        continue;
    }

    try {
        // здесь обработка запроса
        $response = new Psr7\\Response(200, [], "Hello, " . $request->getUri()->getPath());
        $psr7->respond($response);
    } catch (\\Throwable $e) {
        $psr7->respond(new Psr7\\Response(500, [], "Error"));
        $worker->error((string) $e);  // сообщить RR об ошибке
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Какие лимиты пула воркеров RoadRunner важно настраивать в .rr.yaml?',
                'answer' => '**Базовые параметры** живут в секциях `http.pool` / `jobs.pool` файла **`.rr.yaml`**:

| Параметр | Что делает | Типовое значение |
| --- | --- | --- |
| `num_workers` | Стартовое число процессов | По числу CPU (для I/O-bound — чуть больше) |
| `max_jobs` | Плановый рестарт воркера после N запросов | `500–2000` — страховка от утечек |
| `max_memory` | Плавно завершить воркер при превышении МБ | `256–512` МБ |
| `allocate_timeout` | Сколько ждать свободного воркера до `503` | `60s` |
| `destroy_timeout` | Время на graceful shutdown | `60s` |
| `supervisor.exec_ttl` | Макс. время на **один запрос** (аналог FPM `request_terminate_timeout`) | `30s` |

**Профили окружений:**

- **Production:** `debug: false`, воркеры переиспользуются. Обязательно `max_jobs` **или** `max_memory` — без них утечки в сторонних пакетах раздуют память за часы.
- **Development:** `pool.debug: true` — на каждый запрос создаётся **новый** воркер, состояние не переживает. Упрощает отладку и обновление кода без `octane:reload`.

**Подводные камни:**

- Слишком маленький `exec_ttl` рубит долгие выгрузки/импорты — выноси их в `jobs`, а не в HTTP.
- `num_workers` без `max_jobs` = бомба замедленного действия: одна утечка на каждом запросе → OOM через сутки.',
                'code_example' => '# .rr.yaml - production
version: "3"

server:
  command: "php artisan octane:start --server=roadrunner"

http:
  address: 0.0.0.0:8080
  pool:
    num_workers: 8
    max_jobs: 1000             # перезапуск после 1000 запросов
    max_memory: 256            # MB - страховка от утечек
    allocate_timeout: 60s
    destroy_timeout: 60s
    debug: false               # production: воркеры переиспользуются

  supervisor:
    watch_tick: 1s
    exec_ttl: 30s              # макс. время на 1 запрос
    max_worker_memory: 256

logs:
  mode: production
  level: error
  encoding: json

# dev .rr.yaml - воркер на запрос
http:
  pool:
    debug: true                # каждый запрос - новый воркер',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Какие основные подводные камни кода в долгоживущем PHP-окружении (RoadRunner, Octane)?',
                'answer' => '**Четыре главных проблемы**, которые ломают код, написанный под обычный PHP-FPM, когда его кладут в долгоживущий воркер.

**1. Утечки памяти:**

- Между запросами накапливаются объекты в `static`-свойствах, listener-ах фасадов, не-`weak`-ссылках.
- **Симптом:** воркер растёт от 50 МБ до 1 ГБ за час.
- **Защита:** `max_memory` (`RoadRunner`) или `--max-requests` (`Octane`) — перезапуск после порога.

**2. State leakage через singleton/static:**

- Singleton-ы, статические свойства, глобальное состояние **переживают** между запросами.
- Самое опасное: данные одного пользователя протекают в запрос другого (`Auth::user()` в singleton, request-scoped кеш в static).
- **Защита:** `$this->app->scoped()` вместо `singleton()`; listener на `RequestTerminated` сбрасывает свои static-структуры.

**3. Долгоживущие соединения:**

- **MySQL `wait_timeout`** (дефолт 8 часов) — соединение в idle получает `MySQL server has gone away`.
- **Redis** / **Elasticsearch** / **AMQP** — то же самое со своими таймаутами.
- **Файловые дескрипторы** — могут утечь через `fopen` без `fclose`.
- **Защита:** `DB::reconnect()` на исключение, periodic ping, либо `--max-requests` как страховка.

**4. Необработанные исключения роняют воркер:**

- В FPM exception = HTTP 500 и конец процесса. В долгоживущем воркере unhandled exception **убивает весь воркер**, RoadRunner поднимает новый — но текущий запрос потерян.
- **Защита:** глобальный `try/catch` на уровне HTTP-кернела (в Laravel уже есть через `Handler::report`), для RR — `$worker->error((string) $e)` вместо crash.

**Резюме:** долгоживущий PHP требует **гигиены состояния** — каждый `static`, `singleton`, открытое соединение нужно либо сбрасывать в `RequestTerminated`, либо страховать перезапуском.',
                'difficulty' => 4,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Зачем нужен KV-плагин в RoadRunner и какие у него драйверы?',
                'answer' => '**`KV`-плагин** даёт **единый интерфейс к key-value хранилищам** и позволяет вынести часть кеша или общее состояние из PHP **в Go-процесс RoadRunner**.

**Драйверы:**

| Драйвер | Где живёт | Зачем |
| --- | --- | --- |
| `redis` | Внешний Redis | Распределённый кеш — несколько нод, продакшен |
| `memcached` | Внешний Memcached | То же, альтернатива Redis |
| `boltdb` | **Файл на диске** | Embedded, без внешних зависимостей — конфиги, feature flags на одной ноде |
| `memory` | RAM Go-процесса | **Самый быстрый**; шарится **между всеми воркерами одного RR**, теряется при рестарте |

**Главный кейс — `memory`:** разделить состояние **между воркерами одной ноды без сети**. Пример — **rate-limiter**, который должен видеть запросы от всех воркеров одного RR-экземпляра; PHP-кеш в `array` не подходит (он только в текущем воркере), а Redis — это лишний RTT.

**Доступ из PHP:**

- Клиент — `Spiral\\RoadRunner\\KeyValue\\Factory` через RPC (`Spiral\\Goridge\\RPC`).
- API похож на массив: `$kv->set($k, $v, $ttl)`, `$kv->get($k)`, `$kv->delete($k)`.

Альтернатива в Laravel — `Cache::store(\'array\')` (только текущий воркер) или `Cache::store(\'redis\')` (через сеть); KV занимает удобную нишу «межворкерный кеш без сети».',
                'code_example' => '# .rr.yaml
kv:
  user-cache:
    driver: redis
    config:
      addrs:
        - "redis:6379"

  shared-state:
    driver: memory
    config:
      interval: 60     # GC interval

  feature-flags:
    driver: boltdb
    config:
      file: "/var/data/flags.db"
      permissions: 0666

# PHP-использование
use Spiral\\RoadRunner\\KeyValue\\Factory;
use Spiral\\Goridge\\RPC\\RPC;

$factory = new Factory(RPC::create("tcp://127.0.0.1:6001"));
$cache = $factory->select("user-cache");
$cache->set("user:42", $userArray, 3600);
$user = $cache->get("user:42");',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как работают очереди (Jobs) в RoadRunner и какие брокеры поддерживаются?',
                'answer' => '**RoadRunner сам выступает консьюмером и менеджером задач**: подписывается на брокер, получает задачи и передаёт их PHP-воркерам через `Goridge`. Отдельный CLI-консьюмер (`queue:work`) **не нужен** — `jobs.pool` живёт рядом с `http.pool`.

**Поддерживаемые драйверы:**

- Внешние: **`amqp`** (RabbitMQ), **`beanstalk`**, **`redis`**, **`sqs`** (AWS), **`nats`**, **`kafka`**.
- Локальные: **`boltdb`** (embedded), **`memory`** (in-process) — для dev/тестов.

**Плюсы над классическим `queue:work` + Supervisor:**

- **Единый процесс** для HTTP и Jobs — проще деплой, меньше supervisord-конфигов.
- Go-сторона эффективнее держит **долгие соединения** с брокером (RabbitMQ keepalive, SQS long-poll).
- `jobs.pool` **изолирован** от `http.pool` — настраиваются независимо.
- **Pipelines** (логические очереди) описываются декларативно в YAML.

**Минусы для Laravel-проектов:**

- В Laravel **`Horizon`** даёт **готовый дашборд** и автомасштабирование для Redis-очередей — RR Jobs такого UI не имеют.
- Поэтому в Laravel-стеке RR Jobs выбирают, **когда нужны брокеры мимо Redis** (Kafka/NATS/SQS) или **гигантские RPS** на брокере, где Horizon уже не тянет.

**В коде Laravel:** `dispatch()` остаётся обычным — конфигурация `queue` в `config/queue.php` указывается с драйвером, совместимым с RR (например, `redis`, `sqs`).',
                'code_example' => '# .rr.yaml - очереди через RoadRunner
amqp:
  addr: amqp://guest:guest@rabbitmq:5672

jobs:
  num_pollers: 10
  pipeline_size: 100000

  pool:
    num_workers: 8
    max_jobs: 0          # 0 = без лимита
    allocate_timeout: 60s

  pipelines:
    default:
      driver: amqp
      config:
        queue: "default"
        priority: 1

    high:
      driver: redis
      config:
        addr: "redis:6379"
        priority: 10

  consume:
    - "default"
    - "high"

# PHP - dispatch как обычно через Laravel Queue фасад
SendEmail::dispatch($user)->onQueue("high");',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что HTTP-плагин RoadRunner делает с входящими запросами и поддерживает ли он стриминг ответов?',
                'answer' => '**HTTP-плагин RoadRunner** принимает запросы как обычный веб-сервер и **преобразует их в PSR-7** объекты для PHP-воркера.

**Что делает Go-сторона (без вызова PHP):**

- **`gzip`-сжатие** для типовых MIME.
- **Статика** из заданной директории (без `php artisan`).
- **`SSL/TLS`**, **HTTP/2**, **HTTP/3** (с RR 2.10+).
- **CORS**, кастомные заголовки, headers-stripping.

**Стриминг ответов — да, поддерживается:**

- PHP-воркер может через `Symfony\\Component\\HttpFoundation\\StreamedResponse` или `response()->stream(...)` Laravel **отдавать ответ инкрементально**.
- Критично для:
  - **Больших скачиваний** (CSV, отчёты) — не держать весь файл в памяти.
  - **Server-Sent Events** (SSE) — `Content-Type: text/event-stream`, длинное соединение.
- Стриминг работает **поверх HTTP/1.1 chunked** и **HTTP/2**.

**Архитектурный выбор:** FastCGI-фронтенд через nginx обычно **не нужен** — RR сам тянет HTTP/2 и TLS. Типовая конфигурация — nginx как edge-proxy с TLS-терминацией, дальше plain HTTP на RR (если нужны WAF/балансировка); либо RR напрямую с certbot.',
                'code_example' => '# .rr.yaml
http:
  address: 0.0.0.0:8080
  middleware: ["gzip", "headers", "static"]

  static:
    dir: "/var/www/public"
    forbid: [".php", ".htaccess"]

  headers:
    response:
      X-Powered-By: ""    # убрать заголовок

  ssl:
    address: ":443"
    cert: /etc/ssl/cert.pem
    key:  /etc/ssl/key.pem

  http2:
    h2c: false
    max_concurrent_streams: 128

# PHP - стриминг через Laravel
return response()->stream(function () {
    foreach (Report::cursor() as $row) {
        echo csvLine($row);
        ob_flush(); flush();
    }
}, 200, ["Content-Type" => "text/csv"]);

# SSE
return response()->stream(function () {
    while (true) {
        echo "data: " . json_encode(["time" => now()]) . "\\n\\n";
        ob_flush(); flush();
        sleep(1);
    }
}, 200, ["Content-Type" => "text/event-stream"]);',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Зачем нужен плагин Centrifuge в RoadRunner?',
                'answer' => '**Плагин `Centrifuge`** интегрирует `RoadRunner` с **Centrifugo** — сервером сообщений реального времени по WebSockets, SSE, SockJS.

**Разделение ответственности:**

| Слой | Что делает | Чем |
|---|---|---|
| **Centrifugo + RR** | Держит **тысячи постоянных WS-соединений**, broadcast, presence, history | Go (эффективно с long-lived TCP) |
| **PHP-воркеры** | **Бизнес-логика** — обрабатывают входящие события, авторизуют, генерируют исходящие | PHP-стек как обычно |

**Зачем именно так:**

- **PHP плохо подходит для долгих WebSocket-соединений** — однопоточная синхронная модель, на каждое WS нужен отдельный воркер.
- **Centrifugo на Go** — один процесс держит **10k+** соединений в памяти.
- **RR-плагин** связывает их по `GRPC`/`HTTP` — каждое WS-сообщение проксируется на PHP-воркер, который отвечает и возвращает результат в Centrifugo для рассылки клиентам.

**Возможности через интеграцию:**

- **`connect_proxy`** — авторизация WS-подключения через PHP.
- **`refresh_proxy`** — обновление JWT-токена.
- **`publish_proxy`** — модерация сообщений перед публикацией.
- **`rpc_proxy`** — клиент-серверные RPC поверх WS.

**Альтернативы в Laravel-стеке:**

- **`Laravel Reverb`** (L11+) — официальный PHP WS-сервер, тоже on Go-подобной модели (ReactPHP), но проще в setup.
- **`Pusher`** — managed SaaS.
- **`laravel-websockets`** (Beyond Code) — устаревает, заменён Reverb.',
                'difficulty' => 4,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что делает плагин Service в RoadRunner?',
                'answer' => '**Плагин `Service`** в `RoadRunner` — встроенный **process supervisor**, который позволяет запускать произвольные бинарники/скрипты как **sidecar-процессы** в одном конфиге с приложением.

**Что умеет:**

- **Запуск произвольной команды** — `command: "node worker.js"`, `php artisan custom:daemon`, и т.д.
- **Restart policy** — `restart_sec`, `restart_after_exit` (`always`/`never`).
- **Health monitoring** — RR следит за процессом, перезапускает при падении.
- **Logging** — stdout/stderr процесса попадают в общий лог RR.
- **Env vars** — пробрасываются из основного конфига.

**Типичные use cases:**

| Сценарий | Что запустить через `service` |
|---|---|
| **Фоновый консьюмер** | `php artisan some:consumer` (не Laravel queue) |
| **Экспортёр метрик** | Prometheus node_exporter, statsd_exporter |
| **Sidecar proxy** | nginx, envoy для авторизации/rate-limit на отдельном порту |
| **Cron-aware демон** | `php artisan schedule:work` (Laravel 11 встроенный watcher) |
| **WebSocket-сервер** | `php artisan reverb:start` |

**Зачем вместо `supervisord` / `systemd`:**

- **Один конфиг** — `.rr.yaml` описывает HTTP + Jobs + sidecars в одном месте.
- **Автоматическая координация** — все процессы стартуют/останавливаются вместе с RR.
- **Docker-friendly** — один контейнер = один RoadRunner, который тянет N процессов; не нужен supervisord-base-image.

**Ограничение:** не заменяет полноценный init-систем (systemd) — у service нет cgroups, namespaces, oom_score_adj. На bare metal под production-нагрузкой обычно сочетают `systemd` для RR + `service` для маленьких sidecar-ов.',
                'difficulty' => 4,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как работает плагин Locks в RoadRunner?',
                'answer' => '**`Locks`-плагин** даёт PHP-воркерам **распределённые блокировки** для синхронизации доступа к общим ресурсам.

**Уровни лока:**

- **Между воркерами одного RR-инстанса** — типовой кейс «только один воркер сейчас выполняет ежедневный отчёт».
- **Между несколькими RR-нодами** — нужен распределённый бэкенд (Redis).

**Бэкенды:**

| Бэкенд | Где живёт | Покрытие |
| --- | --- | --- |
| `memory` | RAM Go-процесса | Все воркеры **одного** RR; не между нодами |
| `redis` | Redis | **Все ноды**, полноценный distributed lock |

**API похож на atomic locks `Laravel Cache`:**

- `$lock->lock(\'report.daily\', ttl: 30)` — взять lock на 30 секунд.
- `$lock->lock(\'k\', ttl: 60, waitTtl: 5)` — блокирующий вариант: ждать до 5 секунд.
- `$lock->release(\'k\')` — отпустить.

**Когда выбирать что в Laravel:**

- Если уже есть Redis → проще **`Cache::lock(\'key\', 30)->get(fn () => ...)`** (тот же механизм, без дополнительной зависимости).
- RR `Locks` имеет смысл, когда **Redis не нужен**, а синхронизация между воркерами одной ноды есть (бэкенд `memory`) — быстрее любого внешнего лока.',
                'code_example' => '# .rr.yaml
lock:
  driver: redis      # или memory
  config:
    addrs:
      - "redis:6379"

# PHP
use Spiral\\RoadRunner\\Lock\\Lock;
use Spiral\\Goridge\\RPC\\RPC;

$lock = new Lock(RPC::create("tcp://127.0.0.1:6001"));

// Захватить лок на 30 сек на ресурс "report.daily"
if ($lock->lock("report.daily", ttl: 30)) {
    try {
        generateDailyReport(); // только один воркер сделает это
    } finally {
        $lock->release("report.daily");
    }
}

// С block - ждать пока освободится (но не больше waitTtl)
if ($lock->lock("user:42", ttl: 60, waitTtl: 5)) {
    // ...
}

// Совет: для Laravel-приложений проще использовать
// Cache::lock("key", 30)->get(fn() => ...) - тот же механизм,
// без отдельной зависимости',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Какие лучшие практики запуска RoadRunner в продакшене?',
                'answer' => 'Чек-лист продакшен-развёртывания `RoadRunner`:

**1. Process supervisor для самого RR:**

- **`systemd`** unit или **`supervisord`** — главный процесс RR должен автоматически подниматься при crash и при ребуте.
- `Restart=always` + `RestartSec=5` в systemd unit.

**2. Лимиты пула воркеров (страховка от утечек):**

- **`max_jobs: 500..2000`** — перезапуск воркера после N запросов.
- **`max_memory: 256..512`** МБ — graceful restart при превышении.
- Без обоих — утечки в сторонних пакетах раздуют RAM за часы.

**3. Health/Readiness probes — плагин `status`:**

- **`/health`** — liveness, для Kubernetes restart unhealthy pod.
- **`/ready`** — readiness, исключает pod из балансировки до прогрева пула.
- В K8s — `livenessProbe` / `readinessProbe` на эти эндпоинты.

**4. Системные лимиты:**

- **`ulimit -n` ≥ 65535** — много открытых сокетов под нагрузкой.
- **`vm.swappiness=10`** — меньше swap-а, важно для latency.
- **`net.core.somaxconn`** ≥ 4096.

**5. Логирование:**

- **`mode: production`** + **`encoding: json`** — структурированный лог для агрегатора (ELK, Loki, Datadog).
- **`level: error`** — без info-спама в проде.
- Отдельно — централизованный Laravel-лог через `daily`/`stack` → файл → промежуточный shipper.

**6. Метрики:**

- Плагин **`metrics`** — Prometheus endpoint `/metrics` с RPS, latency, worker pool stats.

**7. Graceful deploy:**

- **`./rr reset`** — перечитать конфиг и грейсфул-перезапустить воркеров без потери запросов.
- Под Laravel — `php artisan octane:reload`.

**8. Resource isolation:**

- В Docker — `--cpus`, `--memory` limits + соответствие `num_workers` числу CPU.

Без этих базовых вещей долгоживущий процесс быстро деградирует под нагрузкой.',
                'difficulty' => 4,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что выдают эндпоинты /health и /ready плагина status в RoadRunner?',
                'answer' => '**Плагин `status`** в `RoadRunner` даёт два HTTP-эндпоинта для проб Kubernetes / LB:

| Endpoint | Что проверяет | Какая probe в K8s |
|---|---|---|
| **`/health`** | **Жив ли сам сервер `RoadRunner`** (процесс отвечает) | **`livenessProbe`** |
| **`/ready`** | **Есть ли хотя бы один свободный PHP-воркер**, готовый принять запрос | **`readinessProbe`** |

**Семантика:**

- **`/health` → 200** — RR alive. Если падает на 500/timeout — K8s решает, что под мёртв, и **перезапускает** его.
- **`/ready` → 200** — есть свободный воркер. Если 503 — pod **исключается из балансировки** (но не перезапускается). Возвращается в балансировку, когда воркеры освободятся.

**Зачем оба, а не один:**

- **Только `/health`** → во время прогрева воркеров (15 секунд после старта) балансировщик уже шлёт трафик → запросы ждут / падают.
- **Только `/ready`** → если воркеры залипли в deadlock, под не перезапускается, а просто отключается → трафик мигрирует на остальные поды, но в кластере накапливаются мёртвые.

**Конфигурация в `.rr.yaml`:**

```yaml
status:
  address: 127.0.0.1:2114  # отдельный порт для probes
```

**Параметры probes в Kubernetes:**

- `livenessProbe.initialDelaySeconds: 10` — дать RR подняться.
- `readinessProbe.periodSeconds: 5` — частая проверка готовности.
- **`failureThreshold`** — сколько провалов до отметки «not ready»/«dead».

**Расширения:** `/jobs` — статус Jobs-пула, `/workers` — состояние конкретных PHP-воркеров (RAM, jobs done).',
                'difficulty' => 4,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как RoadRunner интегрируется с Temporal?',
                'answer' => '**`Temporal`** — open-source движок оркестрации **stateful и долгоживущих workflow**: распределённые саги, retry с backoff, таймеры на дни/недели, гарантированное выполнение.

**Архитектура:**

- **Temporal Server** (Go) — хранит состояние workflow, диспатчит задачи.
- **`RoadRunner` + `temporal-php`** — **основной PHP-runtime** для Temporal workflow.
- **PHP-разработчик** пишет workflow и activity как обычные PHP-классы.

**Разделение обязанностей:**

| Слой | Что делает |
|---|---|
| **Temporal Server** | Хранит state, очередь задач, таймеры, history |
| **RoadRunner** | Держит PHP-воркеры живыми, связь по `Goridge` |
| **PHP Workflow** | Декларативное описание шагов (long-running) |
| **PHP Activity** | Атомарные единицы работы (отправить email, списать с карты) |

**Что даёт по сравнению с Laravel Queue:**

- **Гарантия выполнения** — workflow либо доходит до конца, либо завершается с явным `failed`-статусом; никаких «потерянных» job-ов после crash воркера.
- **State machine** — переменные внутри workflow сохраняются в Temporal между шагами; продолжение через дни/недели **без БД-таблицы для прогресса**.
- **Distributed retry** — встроенный exponential backoff с настройкой per activity.
- **Timers** — `Workflow::timer(\'P30D\')` — пауза на 30 дней без воркера в памяти.
- **Versioning** — изменения логики workflow без поломки уже бегущих экземпляров.

**Типичные сценарии:**

- **Onboarding пользователя** на 7 дней с цепочкой триггеров.
- **Saga для платежей** через 3 внешних провайдера с компенсациями.
- **Долгие ETL** с retry на каждом шаге.
- **Подписочный billing** с timer-ами и pro-rata.

**Запуск:** `php artisan temporal:make-worker`, `./rr serve` — воркер регистрируется на `task queue` и принимает workflow/activity tasks от Temporal Server.',
                'difficulty' => 4,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Почему в Octane/RoadRunner опасно держать состояние, специфичное для запроса, в синглтонах сервис-контейнера?',
                'answer' => '**Главный анти-паттерн долгоживущего PHP** — request-scoped state в singleton-е.

**Жизненные циклы биндингов:**

| Тип | FPM | Octane/RR |
|---|---|---|
| **`bind()`** (transient) | Новый инстанс на каждый `resolve` | То же |
| **`singleton()`** | Один на запрос (запрос = процесс) | **Один на ВСЁ время жизни воркера** (тысячи запросов) |
| **`scoped()`** (L8+) | То же, что singleton | **Один на запрос** — Octane сбрасывает между запросами |

**Что ломается, если положить request-данные в `singleton`:**

- **`Auth::user()`** в singleton-сервисе → запрос N+1 видит юзера из запроса N.
- **`TenantContext`** на singleton → клиент A видит данные клиента B.
- **Открытое БД-соединение** конкретно «под запрос» → утекают prepared statements, утрачивается изоляция транзакций.
- **Request-scoped кеш** (`OncePerRequest` маркер) → данные переживают и протекают.

**Почему это критично:**

- Это **не «случайная утечка»**, а **гарантированный** баг через несколько запросов на одном воркере.
- В тестах **не воспроизводится** — там обычно один request на test case.
- В проде **видно по жалобам пользователей** на «чужие данные», иногда через дни.

**Как правильно:**

| Что нужно | Чем зарегистрировать |
|---|---|
| Глобальный сервис без state (логгер, mailer, HTTP-клиент) | **`singleton()`** — нормально |
| Сервис с **request-scoped state** | **`scoped()`** — Octane сбрасывает |
| Чистая функция/фабрика | **`bind()`** — каждый раз новый |

**Что Octane сбрасывает между запросами автоматически:**

- `scoped`-биндинги.
- Уже подкачанные `request`, `response`, `session`, `cookie` (через `FlushTemporaryContainerInstances` listener).
- Uploaded files.

**Что НЕ сбрасывает (нужно руками в `RequestTerminated`):**

- **Статические свойства** ваших классов.
- **Закешированные значения** в фасадах (`Config::set` живёт до конца воркера).
- Свой singleton-state — нужно явно сбрасывать или мигрировать на `scoped`.',
                'difficulty' => 4,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Чем Laravel Octane отличается от Laravel Horizon в задачах производительности?',
                'answer' => '**Это разные слои стека и разные задачи — они не конкурируют, часто стоят оба.**

| | `Octane` | `Horizon` |
| --- | --- | --- |
| Слой | **HTTP** | **Queue** |
| Что делает | Подменяет PHP-FPM на `Swoole`/`RoadRunner`/`FrankenPHP`, держит Laravel в памяти | Дашборд + автомасштабирование воркеров **Redis-очередей** |
| Выигрыш | Экономия ~50 ms на bootstrap, RPS x3–5 | Удобство, метрики, balance стратегий |
| Запуск | `php artisan octane:start` | `php artisan horizon` |
| Перезапуск (deploy) | `php artisan octane:reload` | `php artisan horizon:terminate` |
| Цена/ограничение | **State leakage**: singleton-ы переживают между запросами; нужен `scoped` | Работает **только** с Redis-драйвером очереди |

**Сценарий вместе:**

- **Octane** обрабатывает онлайн-запросы пользователей.
- **Horizon** оркеструет фон (отправка писем, ресайз картинок, отчёты).
- Между ними — Redis (Octane ↔ cache/session, Horizon ↔ очередь).

**Что не делает Horizon:** не ускоряет сам PHP — это **просто удобный супервизор** для `queue:work` на Redis с UI и метриками.',
                'code_example' => '# Octane - HTTP layer
php artisan octane:install --server=roadrunner
php artisan octane:start --workers=8 --max-requests=500

# Horizon - Jobs layer (только Redis)
php artisan horizon:install
php artisan horizon

# config/horizon.php
"environments" => [
    "production" => [
        "supervisor-1" => [
            "connection"   => "redis",
            "queue"        => ["default", "emails"],
            "balance"      => "auto",
            "minProcesses" => 1,
            "maxProcesses" => 20,
        ],
    ],
],

# Deploy hook - перезапустить оба
php artisan octane:reload      # без даунтайма HTTP
php artisan horizon:terminate  # текущие job-ы доработают, потом restart',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'laravel.octane_horizon',
            ],
        ];
    }
}
