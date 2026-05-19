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
                'answer' => 'Octane - это пакет для Laravel, который держит приложение в памяти между запросами вместо перезагрузки. Простыми словами: обычный PHP при каждом запросе заново загружает Laravel - это медленно. Octane загружает один раз и потом каждый запрос обрабатывается мгновенно. Серверы: Swoole, RoadRunner, FrankenPHP. Подводные камни: 1) Утечки памяти - переменные класса не сбрасываются. 2) Состояние singleton-ов сохраняется. 3) Глобальные/статические переменные опасны. 4) Нужно использовать scoped-биндинги вместо singleton там, где состояние per-request. 5) Долгоживущие соединения с БД могут отваливаться по wait_timeout (gone away) - нужны reconnect-стратегии или DB::reconnect() на лонг-айдл.',
                'code_example' => 'composer require laravel/octane
php artisan octane:install
php artisan octane:start --workers=4 --task-workers=2  # --task-workers только для Swoole

// scoped binding для per-request состояния
$this->app->scoped(RequestContext::class);',
                'code_language' => 'bash',
                'difficulty' => 5,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Horizon?',
                'answer' => 'Horizon - это пакет для управления Redis-очередями: красивый dashboard, балансировка воркеров (auto/simple), метрики, мониторинг failed jobs, теги задач. Простыми словами: GUI и автомасштабирование для php artisan queue:work на Redis.',
                'code_example' => 'composer require laravel/horizon
php artisan horizon:install
php artisan horizon

// config/horizon.php
\'environments\' => [
    \'production\' => [
        \'supervisor-1\' => [
            \'connection\' => \'redis\',
            \'queue\' => [\'default\', \'high\'],
            \'balance\' => \'auto\',
            \'maxProcesses\' => 10,
        ],
    ],
],',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Какие подводные камни у Octane по сравнению с обычным FPM?',
                'answer' => 'Octane держит фреймворк в памяти между запросами. Singletons и статические свойства не сбрасываются - типичный источник утечек данных между пользователями. Запрещено хранить Auth::user() в синглтонах, использовать array-кэши на жизнь приложения, изменять контейнер из контроллеров. Решения: 1) регистрировать per-request сервисы через $this->app->scoped() - Octane сам сбрасывает scoped-биндинги между запросами; 2) подписаться на lifecycle-события Octane (RequestReceived/RequestHandled/RequestTerminated/WorkerStarting в config/octane.php → listeners) и сбрасывать там state, чистить статику, переподключать БД. Также Octane не любит долгие и блокирующие операции - нужна модель Tasks/Coroutines.',
                'code_example' => '<?php
// плохо в Octane
class CartHolder { public static array $items = []; }

// хорошо
$this->app->scoped(CartHolder::class, fn() => new CartHolder());',
                'code_language' => 'php',
                'difficulty' => 5,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как работает Laravel Horizon и какие метрики он даёт?',
                'answer' => 'Horizon - дашборд и супервизор для Redis-очередей. Конфигурируется в config/horizon.php: массив supervisors с балансингом (auto/simple/false), maxProcesses, queues, balanceMaxShift, balanceCooldown. Дашборд показывает throughput, runtime, failed jobs, worker memory, recent jobs. auto-balance перераспределяет процессы между очередями по нагрузке. horizon:terminate грейсфул-перезапускает воркеры при деплое (текущие job дорабатываются).',
                'code_example' => '// config/horizon.php
\'environments\' => [
    \'production\' => [
        \'supervisor-1\' => [
            \'connection\' => \'redis\',
            \'queue\' => [\'default\', \'emails\', \'notifications\'],
            \'balance\' => \'auto\',
            \'minProcesses\' => 1,
            \'maxProcesses\' => 20,
            \'tries\' => 3,
            \'timeout\' => 60,
        ],
    ],
],

# деплой
php artisan horizon:terminate',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое RoadRunner и за счёт чего он быстрее PHP-FPM?',
                'answer' => 'RoadRunner - PHP application server и менеджер процессов, написанный на Go. Держит пул PHP-воркеров живыми и передаёт им запросы по бинарному протоколу Goridge. В отличие от PHP-FPM, который при каждом запросе создаёт новый процесс/потомка с нуля и бутстрапит фреймворк заново (autoload, конфиги, провайдеры, роуты), RoadRunner загружает Laravel ОДИН раз при старте воркера и держит его в памяти. Каждый последующий запрос - просто вызов handle($request) на уже готовом приложении. Накладные расходы на bootstrap (~10-50ms на FPM) исчезают, throughput часто в 3-5x выше. Octane - официальная интеграция Laravel с RoadRunner (и Swoole, FrankenPHP). Цена: нужно следить за state leakage между запросами (singleton-ы, статические переменные), потому что они переживают.',
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
                'answer' => 'Goridge - бинарный протокол, по которому Go-сервер RoadRunner общается с PHP-воркерами. Каналы связи: 1) pipes (stdin/stdout, по умолчанию) - проще всего, ничего не настраивать, годится для большинства случаев. 2) TCP-сокеты - можно разнести RoadRunner и пул PHP-воркеров по разным машинам/контейнерам (k8s sidecar-сценарий). 3) Unix-сокеты - самая быстрая локальная связь. Главное правило: PHP-воркер общается с RoadRunner через STDIN/STDOUT в режиме pipes, поэтому ЛЮБОЙ echo, var_dump, warning или die("debug") в STDOUT повредит фрейм протокола - RoadRunner получит мусор и убьёт воркер. RoadRunner 2.0+ автоматически перенаправляет STDOUT в STDERR, но логи через error_log() и кастомные обёртки всё равно надо направлять в файл/STDERR. Для дампов в Octane есть dump() с дополнительной обработкой.',
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
                'answer' => 'PHP Worker - обычный PHP-скрипт, в котором через библиотеку Spiral\\RoadRunner крутится бесконечный цикл while waitRequest(). Воркер блокируется в waitRequest(), ждёт следующего HTTP-запроса от RoadRunner-сервера, обрабатывает его (через PSR-7 HttpWorker - конвертация в Symfony Request/Response), отдаёт ответ методом respond() и снова идёт ждать. Между итерациями скрипт остаётся в памяти со всем загруженным фреймворком - вот откуда экономия на bootstrap. При исключении в обработке надо вызвать $worker->error($message), иначе RoadRunner посчитает воркера сломанным и kill-ит его (поднимет новый). При работе с Laravel Octane всю эту обвязку Octane делает сам в octane:start - но понимать что происходит критично для отладки.',
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
                'answer' => 'Базовые параметры в секции http.pool / jobs.pool: 1) num_workers - стартовое количество процессов, обычно по числу CPU-ядер или чуть больше для I/O-bound нагрузки. 2) max_jobs - число запросов до плановой перезагрузки воркера; критично как страховка от утечек памяти в чужих пакетах (типично 500-2000). 3) max_memory - порог в МБ, при превышении воркер плавно завершится; ещё одна страховка от утечек (типично 256-512 МБ). 4) allocate_timeout - сколько ждать выделения воркера из пула до 503. 5) destroy_timeout - время на graceful shutdown воркера (default 60s). 6) supervisor.exec_ttl - максимальное время на ОДИН запрос (защита от зависших воркеров) - аналог timeout у FPM. В разработке удобно ставить pool.debug=true: на каждый запрос создаётся НОВЫЙ воркер, состояние не переживает - упрощает отладку. В продакшене debug=false обязательно.',
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
                'answer' => 'Главных четыре: утечки памяти накапливаются между запросами, поэтому ставят max_memory или max_jobs как страховку; статические свойства, синглтоны и глобальное состояние сохраняются и могут «протекать» данные одного пользователя в запрос другого; долгоживущие соединения с БД и файловые дескрипторы могут отвалиться по таймауту, нужен retry или ttl воркера; необработанные исключения роняют воркер целиком, их обязательно ловят и репортят через $worker->error().',
                'difficulty' => 4,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Зачем нужен KV-плагин в RoadRunner и какие у него драйверы?',
                'answer' => 'KV-плагин даёт единый интерфейс к key-value хранилищам и позволяет вынести часть кеша или управление состоянием из PHP в Go-процесс RoadRunner. Драйверы: redis и memcached (распределённые, для горизонтального масштабирования), boltdb (файловое embedded-хранилище без внешних зависимостей - годится для конфигов и feature flags на одной ноде), memory (in-memory кеш на жизнь процесса RR - быстрее всего, но теряется при рестарте, шарится МЕЖДУ воркерами одного RR-экземпляра, не между разными нодами). Полезно когда нужно делиться состоянием между воркерами без накладных расходов на полноценный внешний кеш (например, rate-limiter, который должен видеть запросы от всех воркеров одной ноды). Используется через PHP-клиент Spiral\\Goridge / Spiral\\RoadRunner\\KeyValue.',
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
                'answer' => 'RoadRunner сам выступает консьюмером и менеджером задач: подписывается на брокер, получает задачи и передаёт их PHP-воркерам через Goridge - отдельный CLI-консьюмер (queue:work) НЕ нужен. Поддерживаемые драйверы: amqp (RabbitMQ), beanstalk, redis, sqs (AWS), nats, kafka, а также boltdb (embedded) и memory (in-process) для локальных и тестовых сценариев. Преимущества над классическим queue:work + Supervisor: 1) Единый процесс для HTTP и Jobs - проще деплой, меньше supervisord-конфигов. 2) Go-сторона эффективнее держит долгие соединения с брокером. 3) Pool jobs изолирован от HTTP pool - можно настраивать отдельно. 4) Pipelines (логические очереди) описываются декларативно в YAML, не в PHP. Минус: для Laravel классический queue:work с Horizon даёт лучший дашборд, потому RoadRunner Jobs чаще выбирают для не-Laravel или больших нагрузок.',
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
                'answer' => 'HTTP-плагин RoadRunner принимает запросы как обычный веб-сервер и преобразует их в PSR-7 объекты для PHP-воркера. На уровне Go умеет: gzip-сжатие (автоматически для типовых mime-типов), кастомные заголовки и middleware, отдачу статических файлов из заданной директории (без вызова PHP), SSL/TLS, HTTP/2, HTTP/3 (с RR 2.10+), CORS, headers-стрипинг. Поддерживается Response Streaming: PHP-воркер может через generator или ResponseFactory отдавать ответ инкрементально (Symfony StreamedResponse), не держа весь контент в памяти - критично для скачиваний больших файлов и server-sent events (SSE). FastCGI-фронтенд через nginx обычно НЕ нужен - RoadRunner сам обрабатывает HTTP/2 и TLS. Удобно: nginx как edge-proxy с TLS-терминацией, дальше plain HTTP на RR.',
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
                'answer' => 'Плагин Centrifuge интегрирует RoadRunner с Centrifugo — сервером сообщений в реальном времени по WebSockets и SockJS. Тысячи постоянных соединений держит Go-часть, а PHP-воркеры получают только события и реализуют бизнес-логику. Это снимает с PHP типовую боль с долгими WS-соединениями, для которых однопоточная синхронная модель плохо подходит.',
                'difficulty' => 4,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что делает плагин Service в RoadRunner?',
                'answer' => 'Плагин Service позволяет RoadRunner запускать произвольный бинарник или скрипт как sidecar-процесс, следить за его работоспособностью и автоматически перезапускать при падении. Это удобно для фоновых консьюмеров, экспортёров метрик, прокси-серверов и других вспомогательных демонов, которые хочется поднимать вместе с приложением одним конфигом, без отдельного supervisord.',
                'difficulty' => 4,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как работает плагин Locks в RoadRunner?',
                'answer' => 'Плагин Locks даёт PHP-воркерам распределённые блокировки для синхронизации доступа к общим ресурсам. Lock можно взять: 1) Между несколькими воркерами одного экземпляра RoadRunner (типичный случай "только один воркер сейчас обрабатывает background-задачу"). 2) Между несколькими экземплярами RR на разных машинах (если используется распределённый бэкенд Redis). Бэкенды: memory - локальная память Go-процесса, шарится между воркерами этого RR, но не между нодами; redis - распределённый, синхронизирует все ноды. API похож на atomic locks Laravel Cache: ->lock(name, ttl)->get($callback) - блокирующий вариант через RR быстрее, чем через PHP-Redis-клиент, потому что отсутствуют PHP-overhead и пересоздание соединения на каждом acquire.',
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
                'answer' => 'Главный процесс держат под systemd или supervisord, чтобы он автоматически поднимался. Обязательно ставят max_jobs или max_memory, иначе утечки в сторонних библиотеках раздуют память. Подключают плагин status для liveness/readiness, увеличивают ulimit -n под большое число дескрипторов и настраивают структурированное JSON-логирование для агрегатора. Без этих базовых вещей долгоживущий процесс быстро деградирует.',
                'difficulty' => 4,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что выдают эндпоинты /health и /ready плагина status в RoadRunner?',
                'answer' => '/health отвечает «жив ли сам сервер RoadRunner» — это liveness-проба, по которой Kubernetes понимает, что под надо перезапустить. /ready проверяет «есть ли хотя бы один свободный PHP-воркер, готовый принять запрос» — это readiness-проба, она убирает под из балансировщика, пока пул занят или ещё прогревается. Оба эндпоинта обычно подключают как probes в Kubernetes или как healthcheck в LB.',
                'difficulty' => 4,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как RoadRunner интегрируется с Temporal?',
                'answer' => 'Temporal — это движок оркестрации stateful и долгоживущих workflow, и RoadRunner является его основным PHP-воркером. PHP-разработчик пишет workflow и activity в виде обычных PHP-классов, а RoadRunner обеспечивает связь с сервером Temporal по протоколу Goridge: получает задания, вызывает методы воркфлоу, возвращает результаты. Это позволяет делать сложную распределённую логику с retry и таймерами на стандартном PHP.',
                'difficulty' => 4,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Почему в Octane/RoadRunner опасно держать состояние, специфичное для запроса, в синглтонах сервис-контейнера?',
                'answer' => 'Синглтоны и статические свойства живут весь жизненный цикл воркера, то есть переживают тысячи запросов. Если положить в синглтон текущего пользователя, request-scoped репозиторий или открытое соединение, эти данные утекут в следующие запросы — другому пользователю отдастся чужой контекст. Поэтому request-scoped сервисы регистрируют как scoped (Octane сбрасывает их между запросами) или явно ребиндят на каждый запрос.',
                'difficulty' => 4,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Чем Laravel Octane отличается от Laravel Horizon в задачах производительности?',
                'answer' => 'Это разные слои стека и разные задачи. Octane ускоряет HTTP-запросы: подменяет FPM на Swoole/RoadRunner/FrankenPHP, держит Laravel в памяти между запросами, экономит ~50ms bootstrap. Цена - надо следить за state leakage (singleton-ы переживают между запросами, статика не сбрасывается). Запускается php artisan octane:start. Horizon ускоряет/оркеструет фоновую обработку: дашборд + автомасштабирование воркеров Redis-очередей (не для других драйверов), метрики по jobs, throughput, failed_jobs, теги для группировки. Не ускоряет сам PHP - просто более удобный supervisor для queue:work на Redis. Запускается php artisan horizon. Слои не конкурируют - часто стоят оба: Octane для онлайн-запросов, Horizon для оффлайн-задач. Деплой: для Octane нужно octane:reload, для Horizon - horizon:terminate.',
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
