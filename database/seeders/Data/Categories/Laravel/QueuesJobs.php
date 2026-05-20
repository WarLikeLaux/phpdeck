<?php

namespace Database\Seeders\Data\Categories\Laravel;

class QueuesJobs
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Laravel',
                'question' => 'Что такое очереди (Queues) в Laravel?',
                'answer' => '**Очереди** — механизм отложенного выполнения задач в фоне. Тяжёлая работа (отправка email, обработка изображения, генерация PDF, экспорт CSV) кладётся в очередь, и юзер **не ждёт** в HTTP-запросе.

Поток:

1. Контроллер вызывает `Job::dispatch(...)` — задача попадает в хранилище очереди.
2. Отдельный процесс **worker** (`php artisan queue:work`) её забирает и выполняет.
3. Юзер уже получил быстрый ответ.

Драйверы (`config/queue.php`):

- **`redis`** — прод, быстрый.
- **`database`** — таблица `jobs`. Дефолт в Laravel 11.
- **`sqs`** — AWS managed.
- **`beanstalkd`** — старая школа.
- **`sync`** — выполняет **синхронно**, без воркера. Для отладки и в тестах.
- **`null`** — заглушка, ничего не делает.',
                'code_example' => 'php artisan make:job ProcessPodcast

class ProcessPodcast implements ShouldQueue {
    use Queueable;
    public function handle(): void { /* работа */ }
}

ProcessPodcast::dispatch($podcast);
ProcessPodcast::dispatch($podcast)->onQueue(\'high\')->delay(now()->addMinutes(5));',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.queues_jobs',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как работают воркеры очередей и как их запускать в продакшене?',
                'answer' => 'Воркер - это процесс php artisan queue:work, который непрерывно забирает и выполняет задачи из очереди. В продакшене запускается под Supervisor (или systemd, k8s), чтобы автоматически перезапускался при падении. После деплоя нужно делать queue:restart, чтобы воркеры перечитали код.',
                'code_example' => 'php artisan queue:work redis --queue=high,default --tries=3 --timeout=60
php artisan queue:restart

# supervisor config
[program:laravel-worker]
command=php /var/www/artisan queue:work redis --tries=3
autostart=true
autorestart=true
numprocs=4',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'laravel.queues_jobs',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое retry, backoff и failed jobs?',
                'answer' => '$tries - максимальное количество попыток. backoff - задержка между попытками (число или массив для прогрессивного backoff). При исчерпании попыток job попадает в таблицу failed_jobs. Можно посмотреть через queue:failed, повторить через queue:retry, удалить через queue:flush.',
                'code_example' => 'class ProcessPodcast implements ShouldQueue {
    public int $tries = 5;
    public array $backoff = [1, 5, 10, 30];

    public function failed(\Throwable $e): void {
        // уведомить, залогировать
    }
}

php artisan queue:retry all
php artisan queue:flush',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.queues_jobs',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое ShouldBeUnique?',
                'answer' => 'ShouldBeUnique - интерфейс, гарантирующий что в очереди в один момент есть только одна копия задачи с тем же ключом. Простыми словами: защита от дубликатов в очереди. Можно реализовать uniqueId() и uniqueFor() (TTL).',
                'code_example' => 'class UpdateSearchIndex implements ShouldQueue, ShouldBeUnique {
    public int $uniqueFor = 3600;

    public function uniqueId(): string {
        return $this->product->id;
    }
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.queues_jobs',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Job Batching и Chains?',
                'answer' => 'Chain - последовательное выполнение задач: если одна провалилась, следующие не запускаются. Batch - параллельное выполнение группы задач с общим callback (then/catch/finally) и прогрессом.',
                'code_example' => '// Chain
Bus::chain([
    new ImportCsv,
    new GenerateReport,
    new SendNotification,
])->dispatch();

// Batch
Bus::batch([
    new ProcessFile(\'a.csv\'),
    new ProcessFile(\'b.csv\'),
])->then(fn($batch) => Log::info("Done"))
  ->catch(fn($batch, $e) => Log::error($e))
  ->dispatch();',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.queues_jobs',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как обеспечить идемпотентность Job в очереди и что произойдёт при двойном запуске?',
                'answer' => 'Очередь даёт at-least-once: при таймауте/падении воркера job перейдёт в attempts+1. Для идемпотентности используют ключ операции (заказ id, request id) и проверяют через WithoutOverlapping или БД-запись unique constraint, либо реализуют ShouldBeUnique. Альтернатива - middleware Throttled с уникальным ключом. Также важно ставить retry_after > timeout, чтобы не дублировать запуск из-за таймаута слушателя.',
                'code_example' => '<?php
class ProcessPayment implements ShouldQueue, ShouldBeUnique {
    public int $uniqueFor = 3600;
    public function __construct(public int $orderId) {}
    public function uniqueId(): string { return (string) $this->orderId; }
    public function handle() { /* charge once */ }
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.queues_jobs',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как настроить экспоненциальный backoff и максимальное число попыток для Job?',
                'answer' => 'Число попыток задаётся свойством $tries на классе Job, либо CLI-флагом queue:work --tries=N. Метод retryUntil(): \\DateTimeInterface задаёт абсолютный дедлайн (когда retryUntil вернул будущее время, число попыток игнорируется). backoff() возвращает int или массив задержек по каждой попытке (экспоненциальный backoff). Для долгих jobs нужна синхронизация $timeout (sec) и retry_after в конфиге queue, чтобы воркер не считал job упавшим. failed() вызывается после исчерпания tries - место для алертов.',
                'code_example' => '<?php
class SyncCrm implements ShouldQueue {
    public int $tries = 5;
    public int $timeout = 120;
    public function backoff(): array { return [10, 30, 60, 120, 300]; }
    public function failed(Throwable $e): void { Log::critical("CRM sync gave up", ["e" => $e]); }
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.queues_jobs',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что произойдёт при деплое, если воркеры очереди держат старый код?',
                'answer' => 'Воркер бутстрапит фреймворк один раз и держит его в памяти. После деплоя он продолжит обрабатывать jobs со старыми сериализованными моделями и старыми классами. Решение - выполнять php artisan queue:restart, который выставляет таймстамп в кэше; воркеры периодически его проверяют и грейсфул-завершаются. Supervisor поднимет их с новым кодом. Также job-классы нельзя переименовывать без compatibility-shim, иначе сериализованные данные не десериализуются.',
                'code_example' => '# deploy.sh
php artisan queue:restart
php artisan migrate --force
php artisan config:cache route:cache event:cache',
                'code_language' => 'bash',
                'difficulty' => 4,
                'topic' => 'laravel.queues_jobs',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как правильно диспатчить jobs внутри DB::transaction и как включить afterCommit глобально?',
                'answer' => 'Если внутри транзакции диспатчить job без оговорок, воркер может подхватить его ДО коммита внешней транзакции - и не найти ещё не закоммиченных строк (race-condition между приложением и воркером). Решения по возрастанию стоимости: 1) DB::afterCommit(fn() => Job::dispatch(...)) - точечно. 2) Свойство public bool $afterCommit = true; на классе Job - откладывает диспатч до фактического коммита САМОГО внешнего уровня (вложенные SAVEPOINT не считаются). 3) ГЛОБАЛЬНО для всего соединения очереди - в config/queue.php у нужного драйвера выставить "after_commit" => true; тогда КАЖДЫЙ job, отправленный в это соединение, ждёт коммита, и не нужно дублировать $afterCommit на каждом классе. Это удобно, когда вся команда работает в транзакциях и забывать про afterCommit опасно. Также важно: длинные транзакции внутри job блокируют строки и держат connection - предпочитайте short-lived транзакции и идемпотентные операции (см. ShouldBeUnique / WithoutOverlapping).',
                'code_example' => '<?php
// Вариант 1: точечно
DB::transaction(function () use ($order) {
    $order->save();
    DB::afterCommit(fn() => SendInvoice::dispatch($order));
});

// Вариант 2: на конкретном Job
class SendInvoice implements ShouldQueue {
    public bool $afterCommit = true;
}

// Вариант 3: глобально для всего соединения - config/queue.php
"connections" => [
    "redis" => [
        "driver"       => "redis",
        "connection"   => "default",
        "queue"        => env("REDIS_QUEUE", "default"),
        "retry_after"  => 90,
        "block_for"    => null,
        "after_commit" => true, // ← все jobs ждут commit, без $afterCommit на каждом
    ],
],

// Точечно отключить для одного диспатча, если глобально true:
SendCriticalAlert::dispatch($incident)->beforeCommit();',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.queues_jobs',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Job Middleware и какие встроенные middleware есть в Laravel?',
                'answer' => 'Job Middleware - это middleware, оборачивающий выполнение Job-а; работает аналогично HTTP middleware, но для очередей. Позволяет вынести cross-cutting логику (rate limiting, дедупликацию, retry-стратегии) из самого Job. Подключается через метод middleware() на классе Job - возвращает массив объектов middleware. Встроенные классы: 1) WithoutOverlapping - блокирует параллельное выполнение Job-ов с одинаковым ключом через cache lock; критично для операций над одной сущностью (например, начисление баланса юзеру), чтобы две копии одного Job не выполнялись одновременно. 2) RateLimited - ограничивает частоту выполнения Job-ов (использует RateLimiter::for()); если лимит исчерпан, Job возвращается обратно в очередь с задержкой. 3) ThrottlesExceptions - если Job падает с исключениями слишком часто (например, внешний API лежит), middleware прекращает попытки на заданное время вместо того, чтобы выжигать retry-budget; полезно для интеграций с нестабильными сервисами. 4) SkipIfBatchCancelled - не выполнять Job, если batch отменён. Кастомные middleware - класс с методом handle($job, $next).',
                'code_example' => '<?php
use Illuminate\\Queue\\Middleware\\WithoutOverlapping;
use Illuminate\\Queue\\Middleware\\RateLimited;
use Illuminate\\Queue\\Middleware\\ThrottlesExceptions;

class ProcessPayment implements ShouldQueue
{
    public function __construct(public int $userId) {}

    public function middleware(): array
    {
        return [
            // одна и та же оплата не выполнится дважды параллельно
            (new WithoutOverlapping($this->userId))
                ->expireAfter(180)        // авто-снять lock через 3 мин
                ->releaseAfter(60),       // вернуть в очередь через 60 сек если занято

            // не более 10 jobs в минуту на стороне внешнего API
            new RateLimited("payments-api"),

            // если упал 5 раз за минуту - подождать 5 минут
            (new ThrottlesExceptions(5, 60))->backoff(5),
        ];
    }

    public function handle(): void
    {
        // ...
    }
}

// Регистрация лимитера для RateLimited
RateLimiter::for("payments-api", fn () => Limit::perMinute(10));',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.queues_jobs',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что делать, если воркер очереди завис? Чем отличается timeout от retry_after, и причём здесь pcntl?',
                'answer' => 'Зависший job (бесконечный цикл, deadlock на внешнем API без таймаута) - типичная боль prod-очередей. Laravel предлагает два независимых механизма с разной семантикой. (1) --timeout=N в queue:work / queue:listen - это HARD KILL процесса воркера снаружи через сигнал SIGTERM/SIGKILL по истечении N секунд. КРИТИЧНОЕ требование из документации: "The PCNTL PHP extension must be installed in order to specify job timeouts". Без pcntl флаг --timeout молча игнорируется, и бесконечный цикл не прервётся ничем. PCNTL не работает на Windows нативно (нужен WSL/Docker). Когда job убит по таймауту НА ТЕКУЩЕЙ ПОПЫТКЕ - метод failed() в том же процессе НЕ успевает выполниться, потому что воркер получает SIGALRM из registerTimeoutHandler и тут же делает kill. Однако failed() всё-таки сработает при условиях: 1) на классе job задано $failOnTimeout = true - markJobAsFailedIfItShouldFailOnTimeout пометит job как failed на этой же попытке с TimeoutExceededException, и при следующем pickup воркер вызовет failed(); 2) попытки исчерпаны - markJobAsFailedIfWillExceedMaxAttempts пометит job как failed с MaxAttemptsExceededException. Без $failOnTimeout таймаут просто перепланирует job по retry_after, и failed() сработает только когда attempts кончатся - поэтому делать его обработчиком "каждого timeout-attempt" нельзя; для алертов на каждый таймаут нужны Horizon-метрики или supervisor-логи. (2) retry_after в config/queue.php - это TTL "видимости" job в очереди: если job не закоммитил delete за retry_after секунд, очередь считает его упавшим и выдаёт ВТОРОМУ воркеру. Если retry_after меньше или равно --timeout - job будет дублироваться: первый воркер ещё пишет данные, а второй уже взял ту же задачу. Документированное правило: "The --timeout value should always be at least several seconds shorter than your retry_after configuration value" - например, timeout=60, retry_after=90. После деплоя обязательно queue:restart, иначе старые воркеры держат старый код. Также для долгоиграющих jobs делайте идемпотентность (ShouldBeUnique / WithoutOverlapping), потому что at-least-once гарантия очереди = "когда-то выполнится дважды".',
                'code_example' => '<?php
// На Job - per-job таймаут (требует pcntl)
class GenerateMonthlyReport implements ShouldQueue
{
    public int $timeout = 600; // 10 минут максимум
    public int $tries   = 3;

    public function failed(\Throwable $e): void {
        // ВНИМАНИЕ: при kill по таймауту это НЕ вызовется
        Slack::alert("Report job failed", $e);
    }
}

// config/queue.php - retry_after > timeout
"connections" => [
    "redis" => [
        "driver"      => "redis",
        "queue"       => "default",
        "retry_after" => 660, // > 600 (timeout job)
        "block_for"   => null,
    ],
],

// supervisor - всегда с --timeout, но pcntl должен быть установлен
// php -m | grep pcntl  (если пусто - timeout не работает)
[program:laravel-worker]
command=php /var/www/artisan queue:work redis --queue=default \
    --timeout=600 --tries=3 --max-time=3600
autorestart=true
numprocs=4

// После деплоя
php artisan queue:restart // воркеры грейсфул-завершатся, supervisor поднимет с новым кодом',
                'code_language' => 'php',
                'difficulty' => 5,
                'topic' => 'laravel.queues_jobs',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое job в Laravel?',
                'answer' => '**Job** — класс с методом `handle()`, описывающий **одну задачу** для очереди.

Структура:

- Реализует **`ShouldQueue`** — без этого интерфейса задача выполнится синхронно.
- Использует трейт **`Queueable`** — даёт `onQueue()`, `onConnection()`, `delay()`.
- Создаётся через `php artisan make:job SendWelcomeEmail`.
- В конструкторе хранятся данные (модель, id, payload).
- В `handle()` — сама работа.

Запуск:

- **`SendEmail::dispatch($user)`** — поставить в очередь.
- **`->onQueue(\'high\')`** — на конкретную очередь.
- **`->delay(now()->addMinutes(5))`** — с задержкой.
- **`->onConnection(\'redis\')`** — на конкретное соединение.

Под капотом: задача **сериализуется** (свойства класса), попадает в хранилище (Redis/database), ждёт, пока worker её возьмёт.

Eloquent-модели сериализуются как **id** (через `SerializesModels`), и `handle()` достаёт свежую копию из БД.',
                'code_example' => 'class SendWelcomeEmail implements ShouldQueue {
    public function __construct(public User $user) {}
    public function handle(): void {
        Mail::to($this->user)->send(new WelcomeMail());
    }
}
SendWelcomeEmail::dispatch($user);',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.queues_jobs',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое worker и зачем он нужен?',
                'answer' => '**Worker** — процесс PHP, запущенный командой **`php artisan queue:work`**.

Что делает:

- **Непрерывно опрашивает** очередь.
- Забирает job, выполняет `handle()`.
- При успехе — удаляет из очереди.
- При исключении — возвращает на retry или в `failed_jobs` после исчерпания попыток.

Параллелизм:

- Один worker обрабатывает задачи **последовательно** (по одной).
- Для параллелизма — поднимают **несколько процессов**.

В проде:

- Запускается под **Supervisor** (или `systemd`/k8s) — авто-перезапуск после падения или OOM.
- После деплоя — **`php artisan queue:restart`**, иначе воркеры продолжат работать со **старым кодом** в памяти.
- Часто запускают с **`--max-time=3600`** — воркер сам завершится через час, и Supervisor поднимет с новым кодом.

Альтернатива `queue:work` — `queue:listen`: перезагружает фреймворк на каждой задаче (медленнее, не нужен `queue:restart`). Используется в dev.',
                'code_example' => '# Запустить worker
php artisan queue:work redis --queue=high,default --tries=3 --timeout=60

# Запустить с лимитом по времени (worker сам завершится через час)
php artisan queue:work --max-time=3600

# После деплоя
php artisan queue:restart

# /etc/supervisor/conf.d/worker.conf
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/artisan queue:work redis --tries=3 --timeout=60
autostart=true
autorestart=true
numprocs=4
user=www-data',
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'laravel.queues_jobs',
            ],
        ];
    }
}
