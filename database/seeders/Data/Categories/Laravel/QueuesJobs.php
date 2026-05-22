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
                'answer' => '**Воркер** — процесс **`php artisan queue:work`**, непрерывно опрашивающий очередь и выполняющий задачи **последовательно**, по одной.

**Чем отличается от `queue:listen`:**

- `queue:work` — фреймворк **загружается один раз** при старте, держится в памяти → быстро, но при изменении кода воркер видит **старую** версию.
- `queue:listen` — на каждую задачу бутстрапит фреймворк заново → медленно, но всегда свежий код. Для dev.

**Запуск в проде — под Supervisor / systemd / k8s:**

- **Авто-перезапуск** при падении или OOM.
- **Несколько процессов** (`numprocs=4`) для параллелизма.
- **`--max-time=3600`** — воркер сам завершится через час, супервизор поднимет заново (страховка от утечек памяти).
- **`--max-jobs=1000`** — рестарт после N обработанных задач.
- **`--queue=high,default`** — приоритет: сначала `high`, потом `default`.
- **`--tries=3 --timeout=60 --backoff=10`** — попытки, таймаут, задержка между ретраями.

**Деплой:**

- После каждого деплоя — **`php artisan queue:restart`**: ставит timestamp в кэше, воркеры **грейсфул**-завершаются на ближайшей задаче, Supervisor поднимает с новым кодом.
- Без этого воркер продолжит работать со **старым** классом джоба (старыми сериализаторами моделей).

**Альтернативы:** `Laravel Horizon` (UI + автомасштабирование для Redis), `RoadRunner Jobs` (без отдельного воркера).',
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
                'answer' => '**Жизненный цикл задачи с ошибкой:**

1. **Исключение** в `handle()` → задача возвращается в очередь.
2. Через **`backoff`** секунд воркер пробует снова.
3. Когда попыток больше **`$tries`** — задача попадает в таблицу **`failed_jobs`** и зовётся метод **`failed(Throwable $e)`** на классе.

**Где настраивается:**

| Что | Где задаётся |
| --- | --- |
| Число попыток | `public int $tries = 5;` или CLI `--tries=5` |
| Дедлайн | `public function retryUntil(): DateTimeInterface { return now()->addHours(2); }` (когда возвращает будущее — `tries` игнорируется) |
| Задержка между попытками | `public int $backoff = 10;` или массив `[1, 5, 30, 120]` (экспоненциальный) |
| Поведение при провале | `public function failed(Throwable $e): void { ... }` |

**Управление failed-задачами (CLI):**

- `php artisan queue:failed` — список упавших.
- `php artisan queue:retry all` / `queue:retry {id}` — поднять обратно.
- `php artisan queue:forget {id}` — удалить одну.
- `php artisan queue:flush` — очистить таблицу полностью.
- `php artisan queue:prune-failed --hours=48` — почистить старые.

**Подводные камни:**

- При **kill по таймауту** (`pcntl`) `failed()` **не успевает** выполниться — задача просто перепланируется на следующий attempt. Чтобы поймать таймаут — `public bool $failOnTimeout = true;`.
- Связка `$timeout` (per-job) и `retry_after` (per-connection): **`retry_after > timeout`**, иначе задача задвоится.',
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
                'answer' => '**`ShouldBeUnique`** — маркер-интерфейс на классе job, гарантирующий, что **в очереди одновременно живёт только одна копия** задачи с тем же уникальным ключом. Защита от дубликатов в горизонте `uniqueFor` секунд.

**Как работает под капотом:**

- Перед `push()` Laravel берёт **`Cache::lock("laravel_unique_job:{class}:{uniqueId}", $uniqueFor)`**.
- Lock взялся → job отправляется в очередь.
- Lock **не взялся** → `dispatch` молча отбрасывается (никаких исключений).
- Lock снимается **после успешного `handle()`** или по истечении `$uniqueFor`.

**Что можно настроить на классе:**

| Свойство/метод | Назначение |
|---|---|
| `public int $uniqueFor = 3600;` | **TTL** lock-а в секундах (страховка от висящего lock-а) |
| `public function uniqueId(): string` | Уникальный ключ (по умолчанию — пустая строка → один job на класс) |
| `public function uniqueVia(): Repository` | Какой cache-store использовать (по умолчанию — дефолтный) |

**Варианты интерфейса:**

- **`ShouldBeUnique`** — lock держится **до конца `handle()`** (другая копия может появиться, когда первая закончила).
- **`ShouldBeUniqueUntilProcessing`** — lock снимается, **как только воркер взял** job в работу (новые dispatch можно ставить **во время** обработки).

**Подводные камни:**

- **Не путать с `WithoutOverlapping`**: первый — про **постановку в очередь**, второй — про **параллельное выполнение**. Обычно комбинируют оба.
- `uniqueFor` **обязательно** ставить — без TTL зависший воркер заблокирует диспатч навсегда.
- На `cache.store = database` lock работает, но медленнее — для high-load лучше `redis`.',
                'code_example' => 'use Illuminate\\Contracts\\Queue\\ShouldBeUnique;

class UpdateSearchIndex implements ShouldQueue, ShouldBeUnique
{
    public int $uniqueFor = 3600; // 1 час максимум

    public function __construct(public Product $product) {}

    public function uniqueId(): string
    {
        return (string) $this->product->id;
    }

    public function handle(): void
    {
        // переиндексация продукта
    }
}

// Два dispatch подряд — второй молча отброшен
UpdateSearchIndex::dispatch($product); // OK, поставлен в очередь
UpdateSearchIndex::dispatch($product); // дубликат — отброшен

// ShouldBeUniqueUntilProcessing — lock снимается на старте handle()
use Illuminate\\Contracts\\Queue\\ShouldBeUniqueUntilProcessing;

class GenerateReport implements ShouldQueue, ShouldBeUniqueUntilProcessing {}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.queues_jobs',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Job Batching и Chains?',
                'answer' => 'Два механизма Laravel-bus для **сложных пайплайнов** из нескольких jobs.

**`Bus::chain([...])` — последовательное выполнение:**

- Задачи выполняются **по очереди**, следующая стартует **после успеха** предыдущей.
- Если одна **упала** — оставшиеся **не запускаются**, можно навесить `->catch(fn ($e) => ...)`.
- Кейс: «импорт CSV → построить отчёт → отправить email».

**`Bus::batch([...])` — параллельная группа с общим callback:**

- Задачи отправляются **независимо**, могут выполняться **параллельно** на разных воркерах.
- Общие коллбэки: **`->then`** (все ОК), **`->catch`** (первый провал), **`->finally`** (после завершения в любом случае).
- Поддерживается **прогресс**: `$batch->totalJobs`, `$batch->processedJobs()`, `$batch->progress()` — для UI.
- Можно **отменять**: `$batch->cancel()` + middleware `SkipIfBatchCancelled` на задачах.
- Требует миграцию `job_batches` (`php artisan make:queue-batches-table` / Laravel 11 поднимает автоматически в default-конфиге).

**Сравнение:**

| | `chain` | `batch` |
| --- | --- | --- |
| Порядок | Строгий, последовательный | Параллельный |
| Провал одной | Остальные **не идут** | Остальные **продолжают** (если не `allowFailures`) |
| Прогресс | Нет | Да, отдельная запись в `job_batches` |
| Типовой кейс | Pipeline | Bulk-обработка (миллион писем, генерация миниатюр) |

**Комбинации:** chain *внутри* batch разрешён — каждая «лента» обрабатывается своим воркером последовательно, а сами ленты — параллельно.',
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
                'answer' => '**Очередь Laravel — это at-least-once**: при таймауте, падении воркера или OOM job вернётся в очередь и `attempts++`. Поэтому **любой job должен быть идемпотентным** — повторный запуск с теми же входными данными **не должен** давать побочный эффект дважды.

**Что произойдёт при двойном запуске неидемпотентного job:**

- `ChargePayment` спишет деньги дважды.
- `SendInvoice` отправит письмо дважды.
- `CreateOrder` создаст два заказа.

**Способы сделать job идемпотентным (по возрастанию надёжности):**

| Подход | Гарантия | Когда применять |
|---|---|---|
| **`ShouldBeUnique`** | Защита от **двойного диспатча** (lock на постановку) | Дедупликация по бизнес-ключу |
| **`WithoutOverlapping`** | Защита от **параллельного выполнения** двух job-ов с одним ключом | Операции над одной сущностью (баланс, инвентарь) |
| **`unique constraint` в БД** | Жёсткая гарантия в storage layer | Платежи, заказы — критичные данные |
| **Idempotency-Key + audit-table** | Полная идемпотентность по бизнес-ключу | Платежи через внешний gateway (Stripe `Idempotency-Key`) |

**Дополнительные правила:**

- **`retry_after > $timeout`** в `config/queue.php` — иначе при таймауте воркера job задвоится.
- **`$afterCommit = true`** — job не диспатчится, пока транзакция не закоммитилась.
- Внешние API дёргать с **`Idempotency-Key`** (Stripe, Checkout) — gateway сам отбросит дубль.
- В `handle()` проверять **состояние** в БД: `if ($order->paid_at) return;` — guard на повторный запуск.',
                'code_example' => '<?php
use Illuminate\\Contracts\\Queue\\ShouldBeUnique;
use Illuminate\\Queue\\Middleware\\WithoutOverlapping;

// 1) Защита от двойного диспатча через ShouldBeUnique
class ProcessPayment implements ShouldQueue, ShouldBeUnique
{
    public int $uniqueFor = 3600;

    public function __construct(public int $orderId) {}

    public function uniqueId(): string {
        return (string) \$this->orderId;
    }

    public function handle(StripeClient \$stripe): void {
        \$order = Order::find(\$this->orderId);

        // 2) Idempotency guard в БД — повторный запуск ничего не сделает
        if (\$order->paid_at) {
            return;
        }

        // 3) Idempotency-Key для внешнего API
        \$stripe->charges->create(
            params: ["amount" => \$order->amount_cents],
            options: ["idempotency_key" => "order-{\$order->id}"],
        );

        \$order->update(["paid_at" => now()]);
    }

    // 4) Защита от параллельного выполнения
    public function middleware(): array {
        return [(new WithoutOverlapping(\$this->orderId))->expireAfter(180)];
    }
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.queues_jobs',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как настроить экспоненциальный backoff и максимальное число попыток для Job?',
                'answer' => '**Жизненный цикл job с ошибкой:** исключение в `handle()` → задача возвращается в очередь → через **backoff** секунд воркер пробует снова → когда `attempts > $tries` или `retryUntil` истёк → job попадает в **`failed_jobs`** и вызывается `failed(Throwable $e)`.

**Где задаются параметры:**

| Параметр | Где | Семантика |
|---|---|---|
| **`public int $tries = 5;`** | На классе Job или CLI `--tries=5` | Максимум попыток |
| **`public function retryUntil(): DateTimeInterface`** | Метод класса | **Абсолютный дедлайн** — `$tries` игнорируется, пока возвращает будущее |
| **`public int $backoff = 10;`** | Свойство | Фиксированная задержка между попытками (сек) |
| **`public function backoff(): array`** | Метод | **Экспоненциальный** — массив задержек по попытке `[10, 30, 60, 120, 300]` |
| **`public int $timeout = 120;`** | На классе или CLI `--timeout=120` | Hard kill процесса через N сек (требует **`pcntl`**) |
| **`public function failed(Throwable $e)`** | Метод | Хук после **исчерпания** попыток — алерт, компенсация |

**Критичное правило для долгих job-ов:**

- **`retry_after` (в `config/queue.php`) > `$timeout`** — иначе очередь решит, что job упал, и отдаст его **второму воркеру**, пока первый ещё работает. Документация: «retry_after should always be at least several seconds shorter than timeout» (то есть retry_after **больше** timeout).

**Экспоненциальный backoff — для нестабильных внешних API:**

- `[10, 30, 60, 120, 300]` — даёт upstream-сервису время восстановиться.
- Альтернатива: формула `backoff = (2 ** attempts) * 5 + rand(0, 5)` — **с jitter** против thundering herd.

**Когда `failed()` НЕ вызовется:**

- Job убит по `$timeout` без `$failOnTimeout = true` → просто перепланируется на следующий attempt.
- Воркер убит `kill -9` / OOM до завершения handle().',
                'code_example' => '<?php
class SyncCrm implements ShouldQueue
{
    public int $tries = 5;
    public int $timeout = 120;
    public bool $failOnTimeout = true;   // failed() вызовется и при таймауте

    // Экспоненциальный backoff с jitter — против thundering herd
    public function backoff(): array
    {
        return [10, 30, 60, 120, 300];
    }

    // Альтернатива — абсолютный дедлайн (приоритетнее $tries)
    public function retryUntil(): \\DateTimeInterface
    {
        return now()->addHours(2);
    }

    public function handle(CrmClient \$crm): void
    {
        \$crm->push(\$this->payload);
    }

    public function failed(\\Throwable \$e): void
    {
        Log::critical("CRM sync gave up", ["error" => \$e->getMessage()]);
        Slack::alert("CRM down — заявка ушла в failed_jobs");
    }
}

// config/queue.php — retry_after > timeout
"connections" => [
    "redis" => [
        "driver"      => "redis",
        "queue"       => "default",
        "retry_after" => 180,  // больше любого $timeout
        "block_for"   => null,
    ],
],',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.queues_jobs',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что произойдёт при деплое, если воркеры очереди держат старый код?',
                'answer' => '**`queue:work` бутстрапит фреймворк один раз** и держит классы в памяти процесса PHP — это ускорение, но и **главная боль деплоя**.

**Что сломается без `queue:restart` после деплоя:**

| Симптом | Причина |
|---|---|
| Job выполняется со **старой версией `handle()`** | Класс уже автозагружен в воркере |
| `ModelNotFoundException` на `__wakeup` | Сериализован старый класс модели, в новой версии переименовано свойство |
| **Старые миграции** в коде воркера → запрос к **несуществующей колонке** | Воркер не перезагружен после `migrate` |
| Job-класс **переименовали** → `Class App\\Jobs\\OldName not found` при unserialize | Старый payload в очереди ссылается на старое имя |
| Конфиг ещё **старый** (`config/queue.php`) | Без `config:cache` старый конфиг закеширован в singleton |

**Решение — graceful restart:**

1. **`php artisan queue:restart`** — пишет timestamp в кеш (`illuminate:queue:restart`).
2. Каждый воркер **между job-ами** проверяет timestamp и, если он новее старта воркера, **грейсфул-завершается** (текущий job дорабатывает).
3. **Supervisor / k8s** автоматически поднимает новый процесс с новым кодом.

**Полный deploy-скрипт:**

- **`composer install --no-dev --optimize-autoloader`** — без dev-пакетов.
- **`migrate --force`** — с флагом для прода.
- **`config:cache route:cache event:cache view:cache`** — пересобрать кеши.
- **`queue:restart`** — грейсфул-остановить старых воркеров.
- Если есть **`Horizon`** — `horizon:terminate` (та же логика, но с UI).

**Подводные камни:**

- **Никогда не переименовывайте Job-классы без compatibility-shim** — оставьте старый класс наследником нового на 1-2 деплоя.
- **Не добавляйте обязательные поля** в конструктор job — старые сериализованные payload их не содержат → fatal error в воркере.
- **Removed-properties** — те же грабли с десериализацией, делайте deprecation через 2 релиза.',
                'code_example' => '#!/bin/bash
# deploy.sh — production deploy script

set -e

# 1) Подтянуть код
git pull origin main

# 2) Composer без dev
composer install --no-dev --optimize-autoloader

# 3) Миграции (--force для прода)
php artisan migrate --force

# 4) Пересобрать кеши
php artisan config:cache
php artisan route:cache
php artisan event:cache
php artisan view:cache

# 5) Грейсфул-рестарт воркеров — Supervisor поднимет с новым кодом
php artisan queue:restart

# Если используется Horizon
# php artisan horizon:terminate

# Опционально — прогрев OPcache
# curl -fsS http://localhost/up > /dev/null

echo "Deploy done."',
                'code_language' => 'bash',
                'difficulty' => 4,
                'topic' => 'laravel.queues_jobs',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как правильно диспатчить jobs внутри DB::transaction и как включить afterCommit глобально?',
                'answer' => '**Проблема race-condition между приложением и воркером:** если внутри `DB::transaction` диспатчить job без оговорок, **воркер может подхватить его ДО коммита** внешней транзакции и не найти ещё не закоммиченных строк.

**Типичный сценарий бага:**

1. Контроллер начинает транзакцию.
2. `Order::create([...])` — строка в БД, но **не закоммичена**.
3. `SendInvoice::dispatch($order)` — job сразу в очереди.
4. Воркер **мгновенно** подхватил job → `Order::find($id)` возвращает **`null`**.
5. Транзакция коммитится — поздно, job уже упал.

**Решения по возрастанию стоимости:**

| Подход | Гранулярность | Когда применять |
|---|---|---|
| **`DB::afterCommit(fn () => Job::dispatch(...))`** | Точечно, **per call-site** | Один-два особых диспатча |
| **`public bool $afterCommit = true;`** на классе job | Per-class | Job, который **всегда** диспатчится из транзакции |
| **`after_commit => true`** в `config/queue.php` (per-connection) | **Глобально** на соединение | Когда вся команда работает в транзакциях |
| **`->beforeCommit()`** на диспатче | Опт-аут | Если глобально `after_commit=true`, но один job нужен мгновенно |

**Что важно понимать про вложенность:**

- `$afterCommit` срабатывает после **самого внешнего** commit-а.
- **Вложенные `SAVEPOINT`** (nested transactions) **не считаются** — job ждёт реальный commit корня.

**Связанные паттерны:**

- **Длинные транзакции внутри job-а блокируют строки** и держат connection. Делайте **short-lived транзакции** + идемпотентные операции (`ShouldBeUnique` / `WithoutOverlapping`).
- В **тестах** при `RefreshDatabase` транзакция оборачивает весь тест — `$afterCommit` job-ов **не сработает**. Решение: `DB::commit()` вручную или `Bus::fake()`.',
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
                'answer' => '**Job Middleware** — обёртка вокруг выполнения job-а, аналог **HTTP middleware**, но для очередей. Позволяет вынести **cross-cutting логику** (rate limiting, дедупликацию, retry-стратегии) из самого job-а.

**Как подключается:**

- Метод **`middleware(): array`** на классе job возвращает массив объектов middleware.
- Каждый middleware — класс с **`handle($job, Closure $next)`**, как у HTTP.

**Встроенные middleware:**

| Класс | Что делает | Когда применять |
|---|---|---|
| **`WithoutOverlapping`** | Блокирует **параллельное** выполнение job-ов с одинаковым ключом (cache lock) | Операции над одной сущностью (начисление баланса, обновление инвентаря) |
| **`RateLimited`** | Ограничивает **частоту** выполнения (использует `RateLimiter::for()`); при превышении job возвращается в очередь с задержкой | Лимит внешнего API (Stripe, Twilio) |
| **`ThrottlesExceptions`** | При **N исключениях за период** — приостановить попытки на M секунд, не выжигая retry-budget | Нестабильные интеграции, downtime upstream |
| **`SkipIfBatchCancelled`** | Не выполнять job, если **batch отменён** через `$batch->cancel()` | Batch с возможностью отмены пользователем |
| **`Skip`** (L11+) | Условный skip job-а по предикату | Feature flags, maintenance mode |

**API типичных middleware:**

- **`WithoutOverlapping($key)`**:
  - `->expireAfter(180)` — auto-release lock через 3 мин (если воркер упал).
  - `->releaseAfter(60)` — вернуть job в очередь через 60 сек, если занято.
  - `->dontRelease()` — отбросить job, если занято.
- **`RateLimited("limiter-name")`** — `RateLimiter::for("limiter-name", ...)` в `AppServiceProvider`.
- **`ThrottlesExceptions(maxAttempts, decayMinutes)`** + `->backoff(5)` — задержка после срабатывания.

**Кастомный middleware:**

```php
class LogJobExecution {
    public function handle($job, Closure $next) {
        Log::info("Job start", ["class" => get_class($job)]);
        $next($job);
        Log::info("Job end");
    }
}
```',
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
                'answer' => '**Зависший job** (бесконечный цикл, deadlock на внешнем API без таймаута) — типичная боль prod-очередей. Laravel даёт **два независимых механизма** с разной семантикой.

**1) `--timeout=N` (или `public int $timeout` на job) — hard kill процесса воркера**

- **Сигнал SIGTERM/SIGALRM** через `pcntl_alarm()` по истечении N секунд.
- **Критичное требование документации:** `pcntl` **должен быть установлен**, иначе флаг `--timeout` **молча игнорируется** — бесконечный цикл не прервётся.
- **Не работает на Windows нативно** — нужен WSL/Docker.
- Проверка: **`php -m | grep pcntl`**.

**2) `retry_after` в `config/queue.php` — TTL «видимости» job в очереди**

- Если job не закоммитил `delete` за `retry_after` секунд → очередь считает его упавшим и **выдаёт второму воркеру**.
- Документированное правило: **`retry_after` > `timeout`** на несколько секунд (например, `timeout=60`, `retry_after=90`).
- **Иначе job задвоится:** первый воркер ещё пишет в БД, второй уже взял ту же задачу.

**Когда `failed()` срабатывает при таймауте — таблица:**

| Условие | Вызовется `failed()`? |
|---|---|
| Hard kill по `$timeout` на текущей попытке, **без `$failOnTimeout`** | **Нет** — job просто перепланируется на следующий attempt |
| Hard kill по `$timeout`, **`$failOnTimeout = true`** | **Да** — `TimeoutExceededException`, при следующем pickup воркер вызовет `failed()` |
| Попытки **исчерпаны** (`attempts > $tries`) | **Да** — `MaxAttemptsExceededException` |
| Воркер убит `kill -9` / OOM | **Нет** — процесс умер до записи |

**Правильная связка для критичных job-ов:**

| Параметр | Значение | Зачем |
|---|---|---|
| `$timeout = 60` | На классе | Hard kill через 60 сек |
| `retry_after = 90` | В `config/queue.php` | На 30 сек больше timeout |
| `$failOnTimeout = true` | На классе | Чтобы `failed()` сработал и при таймауте |
| `$tries = 3` | На классе | Лимит попыток |
| `ShouldBeUnique` / `WithoutOverlapping` | Через `middleware()` | At-least-once → возможны повторы |

**Что делать после деплоя:** обязательно **`php artisan queue:restart`** — иначе старые воркеры держат старый код в памяти.

**Для алертов на каждый timeout-attempt:** `failed()` не подходит (вызывается только на финальном провале). Использовать **Horizon-метрики**, **Supervisor-логи** или собственный middleware с логированием.',
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
