<?php

namespace Database\Seeders\Data\Categories\SystemDesign;

class Caching
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое кэш простыми словами?',
                'answer' => '**Кэш** — быстрое временное хранилище для **часто запрашиваемых данных**.

Аналогия: записная книжка под рукой — не нужно каждый раз идти в большой архив.

**Как работает:**

- **cache hit** — данные есть в кэше, отдаём моментально
- **cache miss** — нет: идём в БД, **кладём в кэш**, отдаём

**Где хранят:**

- `Redis` — самый частый выбор: in-memory, структуры данных, persistence
- `Memcached` — чистый key/value, быстрый, простой
- **локальная память** приложения (`array driver` в Laravel) — быстро, но не делится между процессами
- **файловый кэш** — для одного сервера, без зависимостей

В Laravel чаще всего используют `Cache::remember($key, $ttl, $callback)` — он сам делает get/miss/put за одну строчку.',
                'code_example' => '<?php
// Laravel: одна строка делает get → miss → DB → put
$user = Cache::remember("user:$id", 3600, function () use ($id) {
    return User::find($id);
});

// Инвалидация при обновлении
$user->update($data);
Cache::forget("user:$id");',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Какие уровни кэширования бывают в веб-приложении?',
                'answer' => 'Слои кэша **от клиента к БД** — чем ближе к пользователю, тем быстрее, но меньше данных можно хранить.

| Уровень | Где живёт | Что кэширует | Как управляется |
| --- | --- | --- | --- |
| **1. Browser cache** | в браузере пользователя | статика, ответы API | заголовки `Cache-Control`, `ETag`, `Last-Modified` |
| **2. CDN (edge)** | у провайдера (Cloudflare, CloudFront) | статика, HTML, кэш API | заголовки + правила в CDN |
| **3. Reverse proxy cache** | Nginx, Varnish перед приложением | целые HTTP-ответы | `proxy_cache`, VCL |
| **4. Application cache** | Redis, Memcached, APCu | вычисленные значения, результаты запросов | `Cache::remember()`, TTL |
| **5. ORM / query cache** | внутри Eloquent / Doctrine | результаты query | model caching, `remember()` |
| **6. Database buffer pool** | InnoDB / Postgres shared_buffers | страницы индексов и данных | внутри СУБД |

**Принципы:**

- **Cache hierarchy** — запрос проходит сверху вниз, останавливается на первом hit
- **Cheapest cache** — попадание в browser cache = 0 ms и 0 нагрузки на инфраструктуру
- **Coherence** — чем больше слоёв, тем сложнее инвалидация (cache invalidation — это hard)
- **Не путать `Cache-Control: private`** (только browser) и **`public`** (можно кэшировать CDN-у)
- **`stale-while-revalidate`** на уровне CDN/браузера — отдать старое, обновить в фоне',
                'code_example' => '# Уровни в Laravel-приложении

# 1. Browser + CDN: правильные заголовки на статику
location ~* \\.(css|js|jpg|png|woff2)$ {
    expires 30d;
    add_header Cache-Control "public, max-age=2592000, immutable";
}

# 2. CDN кэширует HTML страниц с stale-while-revalidate
add_header Cache-Control "public, max-age=60, stale-while-revalidate=300";

# 3. Reverse proxy cache (nginx)
proxy_cache_path /var/cache/nginx levels=1:2 keys_zone=app:10m;
location /api/products {
    proxy_cache app;
    proxy_cache_valid 200 5m;
}

# 4. Application cache (Laravel)
$products = Cache::remember("products:list", 600, fn () => Product::all());

# 5. Query-level model cache (rennokki/laravel-eloquent-query-cache)
Product::cacheFor(60)->where("active", true)->get();

# 6. БД-кэш виден через: SHOW STATUS LIKE "Innodb_buffer_pool%";',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое cache-aside (lazy loading) стратегия?',
                'answer' => 'Cache-aside - приложение само управляет кэшем: при чтении сначала смотрит в кэш, если нет - читает из БД и кладёт в кэш. При записи - пишет в БД и инвалидирует/обновляет кэш. Простыми словами: ты сам решаешь, что и когда положить в записную книжку. Плюсы: простота, кэш не зависит от БД. Минусы: возможен stale data при гонке, первый запрос всегда медленный. Подвох: проверка через `=== null` не отличает реально закэшированный null (negative caching) от cache miss — в Laravel надёжнее `Cache::has()` или `Cache::remember()` со специальным sentinel-значением.',
                'code_example' => '<?php
function getUser(int $id): User {
    if (! Cache::has("user:$id")) {
        $user = User::find($id);
        Cache::put("user:$id", $user, 3600);
        return $user;
    }
    return Cache::get("user:$id");
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое write-through стратегия кэша?',
                'answer' => '**Write-through** — на запись приложение / кэш-прослойка пишет **одновременно (синхронно) и в кэш, и в БД**. Ответ возвращается **только после успешной записи в оба слоя**.

**Это стратегия записи.** Путь чтения — отдельный паттерн (**read-through** или **cache-aside**); они часто комбинируются, но это разные стратегии.

**Плюсы:**

- **Кэш не отстаёт от БД** на штатном пути записи
- **Не нужно ловить инвалидацию** в коде — кэш всегда свежий после `save()`
- Простая mental model: «обновил → видно всем»

**Минусы:**

- **Запись медленнее** — два последовательных I/O (БД + кэш) на штатном пути
- **Кэш заполняется только тем, что записывали** — cold reads пойдут в БД, если **read-through не настроен**
- Усложняет код, если делать руками; чище через ORM-event или middleware

**⚠️ «Нет stale data» — слишком сильное заявление**, гарантия скорее «штатно консистентен», не «никогда не отстаёт». Stale всё ещё возможны:

- При **сбоях между шагами** — запись в кэш прошла, в БД упала, и наоборот (нет распределённой транзакции)
- При **асинхронной репликации БД** — мастер обновился, реплика отстаёт
- При **гонках** с другими записями (последний writer wins)

**Когда применять:** **чтение во много раз чаще записи**, важна свежесть кэша по штатному пути, можно мириться с двумя I/O на запись (профили пользователей, конфигурация, справочники).',
                'code_example' => '<?php
// Write-through в Laravel через Eloquent observer
class UserObserver
{
    public function saved(User $user): void
    {
        // Сначала БД (Eloquent уже сохранил), теперь кэш
        Cache::put("user:{$user->id}", $user, 3600);
    }

    public function deleted(User $user): void
    {
        Cache::forget("user:{$user->id}");
    }
}

// Чтение — отдельный паттерн (cache-aside)
$user = Cache::remember("user:$id", 3600, fn () => User::find($id));

// ⚠️ Транзакционная согласованность — после COMMIT
DB::transaction(function () use ($data) {
    $user = User::create($data);
    // observer запустится здесь — после транзакции через afterCommit
});

// Если хочется явный двойной write:
DB::beginTransaction();
$user = User::create($data);
DB::commit();
Cache::put("user:$user->id", $user, 3600);   // только если COMMIT прошёл',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое read-through стратегия кэша?',
                'answer' => '**Read-through** — **кэш сам читает из БД при промахе**, приложение **всегда обращается только к кэшу**.

**Ключевое отличие от cache-aside:**

| | Cache-aside | Read-through |
| --- | --- | --- |
| **Кто ловит miss** | приложение | сам кэш-провайдер |
| **Кто идёт в БД** | приложение | кэш через **loader function** |
| **Логика в коде** | явная (`if (!cached) { db; put }`) | скрыта внутри библиотеки |
| **Гибкость** | максимальная | в рамках loader API |

**Плюсы:**

- **Код приложения проще** — единая точка работы с данными
- **Единый loader** для всего проекта — меньше копипасты
- Естественно сочетается с **write-through**

**Минусы:**

- **Первый запрос всегда медленный** (cache miss → загрузка → ответ)
- Нужна **готовая интеграция** кэша с источником
- Сложнее логировать «откуда данные» — всё спрятано в провайдере

**Примеры реализаций:**

- **Hibernate 2nd-level cache** (Java) — стандарт
- **Ehcache CacheLoader** — явный интерфейс
- **AWS DAX** перед DynamoDB — read-through кэш на уровне инфраструктуры
- **Caffeine** (Java) с `CacheLoader`

**В Laravel `Cache::remember()`** формально cache-aside, но с **API в стиле read-through** — callback играет роль loader-а.',
                'code_example' => '<?php
// В Laravel: Cache::remember() выглядит как read-through API
$user = Cache::remember("user:$id", 3600, function () use ($id) {
    // loader function — вызывается только при miss
    return User::find($id);
});

// Шире — фабрика-репозиторий, инкапсулирующая логику
class UserRepository
{
    public function find(int $id): ?User
    {
        return Cache::remember(
            "user:$id",
            3600,
            fn () => User::find($id)
        );
    }
}

// В коде — только обращение к репозиторию, никаких if-cached
$user = $repo->find($id);   // прозрачно, есть ли в кэше

// AWS DAX (DynamoDB Accelerator) — настоящий read-through на инфре
// Приложение шлёт запрос в DAX endpoint, DAX сам идёт в DynamoDB при miss',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое write-behind (write-back) стратегия кэша?',
                'answer' => '**Write-behind** (write-back) — приложение пишет **только в кэш**, **в БД асинхронно** через некоторое время или батчем. Самая быстрая запись.

Аналогия: записал в блокнот, в большую тетрадь перепишу позже.

**Поток:**

1. Write: app → кэш → **сразу ответ юзеру**
2. Background worker / WAL: пачка изменений → БД (раз в N секунд или N записей)

**Плюсы:**

- **Минимальная latency записи** — один in-memory I/O
- **Batching** — несколько UPDATE на один ключ сливаются в один SQL
- **Сглаживание spike-ов** — БД не видит пиковую нагрузку

**Минусы:**

- **Риск потери данных** при падении кэша **до flush** в БД (в наивной in-memory реализации)
- **Eventual consistency** — другие сервисы, читающие БД напрямую, видят старое
- **Сложная реализация** — нужен фоновый flusher, обработка ошибок, restart-safe буфер
- **Порядок операций** — если несколько ключей зависят друг от друга, batch-flush может нарушить causality

**Production-решения** добавляют **WAL/persistence** к буферу:

| Реализация | Где живёт | Persistence |
|---|---|---|
| **Caffeine + RDBMS writer** (Java) | in-process | внешний WAL/Kafka |
| **Hazelcast WriteBehind** | distributed cache | реплицируется |
| **Redis + AOF + voucher** | external | AOF файл |
| **CDC-обратное** (Debezium) | через Kafka | топик Kafka |

**Когда брать:**

- **Счётчики** (`INCR posts:42:views`) — потеря последних N просмотров не катастрофа
- **Метрики**, **логи**, **аналитика** — write-heavy, чтения мало
- **Игровые leaderboards** — `score`-значения обновляются часто, флашатся раз в минуту

**Когда НЕ брать:** платежи, заказы, любые **денежные транзакции** — там нужна **synchronous durability**.',
                'difficulty' => 4,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое write-around стратегия кэша?',
                'answer' => '**Write-around** — **запись идёт сразу в БД, минуя кэш**. Кэш **заполняется только при чтении** (через cache-aside или read-through).

**Поток:**

- Запись: app → БД (кэш не трогаем)
- Чтение: app → кэш → (miss) → БД → кэш → app

**Плюсы:**

- **Не засоряем кэш редко читаемыми данными** — память кэша остаётся под «горячее»
- **Простая запись** — один I/O вместо двух
- Решает проблему write-heavy workload, когда **write-through убивает производительность кэша**

**Минусы:**

- **Первое чтение после записи всегда медленное** (cache miss → БД)
- Не подходит «прочитал-сразу-после-записи» паттернам

**Сравнение стратегий записи:**

| | Write-through | Write-around | Write-behind |
| --- | --- | --- | --- |
| **Запись в** | кэш + БД синхронно | только БД | только кэш (БД асинхронно) |
| **Скорость записи** | медленно (2 I/O) | средне | **быстро** |
| **Read after write** | hit | **miss** | hit |
| **Риск потери при сбое** | низкий | низкий | **высокий** |

**Когда применять write-around:**

- **Записей много, читается малая часть** данных (**логи событий**, **аудит**, IoT-метрики, **append-only event store**)
- Кэш дорог, под него стоит держать только реально востребованные ключи
- Записываются bulk-данные (импорт), которые не нужны сразу после',
                'difficulty' => 3,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Какие политики вытеснения (eviction) есть в кэше?',
                'answer' => 'Когда кэш упирается в **`maxmemory`**, нужно решить — какой ключ выкинуть. Это и есть **eviction policy**.

**Основные политики:**

| Политика | Что вытесняет |
| --- | --- |
| **LRU** (Least Recently Used) | то, что **давно не использовали** |
| **LFU** (Least Frequently Used) | то, что **редко используется** (счётчик обращений) |
| **FIFO** | в порядке **поступления** |
| **Random** | случайно (быстро, без статистики) |
| **TTL** | по **времени жизни** — раньше истекающие первыми |

**Redis-варианты (`maxmemory-policy`):**

- **`noeviction`** *(default)* — **отказывает в записи** при OOM (ошибка клиенту)
- `allkeys-lru` — LRU по **всем ключам**
- `volatile-lru` — LRU по ключам **с установленным TTL**
- `allkeys-lfu` / `volatile-lfu` — то же, но LFU
- `allkeys-random` / `volatile-random` — случайно
- `volatile-ttl` — раньше истекающие первыми

**Выбор зависит от паттерна доступа:**

- **Веб-кэш горячих данных** → `allkeys-lru` (доступ временной локальностью)
- **Кэш долгоживущего контента с частотой запросов** → `allkeys-lfu`
- **Mixed-use Redis** (кэш + очереди + сессии) → `volatile-lru` (вытеснять только то, что объявлено как TTL-кэш, не трогать персистентные ключи)
- **Хранилище без вытеснения** (persistent store) → `noeviction` + мониторинг memory

**Подводные камни:**

- **`noeviction` + забыли TTL** → запись отвалится с ошибкой, приложение упадёт
- **`volatile-*` + нет ключей с TTL** → ведёт себя как `noeviction`
- Redis LRU **приближённый** (выборка из N случайных ключей, по умолчанию 5) — не идеален, но почти бесплатен',
                'code_example' => '# redis.conf
maxmemory 2gb
maxmemory-policy allkeys-lru
maxmemory-samples 10        # точнее LRU, чуть медленнее

# Проверить runtime
$ redis-cli INFO memory
used_memory_human:1.85G
maxmemory_human:2.00G
maxmemory_policy:allkeys-lru
evicted_keys:1234567

# Сменить без рестарта
$ redis-cli CONFIG SET maxmemory-policy allkeys-lfu
$ redis-cli CONFIG REWRITE   # записать в файл

# В Laravel cache.php — указать prefix, чтобы не пересечься с очередями
"redis" => [
    "driver" => "redis",
    "connection" => "cache",   # отдельная Redis-DB
    "prefix" => "myapp_cache:",
],',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое cache stampede (dogpile) и как с ним бороться?',
                'answer' => '**Cache stampede** (он же **dogpile**, **thundering herd**) — когда **популярный ключ истекает** в кэше и **тысячи запросов одновременно** начинают перестраивать его, забивая БД.

Аналогия: магазин закрылся на учёт — все клиенты ломятся одновременно.

**Сценарий:**

1. Ключ `home:hot_products` живёт 5 минут, на странице 10k RPS
2. В момент TTL=0 — **все 10k запросов** видят miss
3. Каждый идёт в БД делать тяжёлый `SELECT` с агрегацией
4. БД захлёбывается → каскадный отказ

**Решения:**

| Подход | Принцип | Подходит |
|---|---|---|
| **Atomic lock** | `SET NX` + только один пересобирает, остальные ждут или отдают stale | hot keys с тяжёлым recompute |
| **Probabilistic early expiration (XFetch)** | с растущей вероятностью обновлять **до** TTL | равномерная нагрузка |
| **Stale-while-revalidate** | отдаём старое, в фоне обновляется новое | tolerable staleness |
| **Refresh-ahead / cache warming** | cron/event пересобирает ДО TTL через очередь | предсказуемая нагрузка |

**1. Atomic lock — double-check pattern:**

- Сначала **`Cache::get`** — чтобы **не сериализовать** cache hits
- Только при miss — берём `Cache::lock(...)`
- **Внутри lock — повторная проверка кэша** (double-check): пока ждали, другой воркер мог уже перестроить
- Если всё ещё miss — пересчитываем

**2. XFetch — probabilistic early refresh:**

`if (now + Δ * ln(rand()) * β >= expiry) { recompute }` — чем ближе к TTL, тем выше шанс одного воркера обновить заранее.

**3. Stale-while-revalidate** — в Laravel 11 это **`Cache::flexible([$fresh, $stale], ...)`**: до `$fresh` секунд кэш «свежий», между `$fresh` и `$stale` — отдаём старое и асинхронно через queue обновляем.

**4. Refresh-ahead** — cron/job обновляет популярные ключи каждые `TTL/2` секунд. Пользовательский запрос **всегда попадает на горячий ключ**.

**Не путать с `BGREWRITEAOF`** — это команда Redis для перезаписи AOF, к stampede отношения не имеет.',
                'code_example' => '<?php
// ✅ Double-check pattern: cache hit не блокируется
$value = Cache::get($key);
if ($value === null) {
    $value = Cache::lock("rebuild:$key", 10)->block(5, function () use ($key) {
        // в lock - снова проверяем: другой воркер мог уже перестроить
        return Cache::remember($key, 300, fn () => expensiveQuery());
    });
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое CDN простыми словами?',
                'answer' => '**CDN** (Content Delivery Network) — сеть **edge-серверов по всему миру**, которые хранят копии твоих статичных файлов (картинки, JS, CSS, видео) **близко к пользователям**.

Аналогия: твой сайт в Москве, пользователь в Токио. Вместо запроса через полпланеты ему отдаёт файл **CDN-узел в Токио** (~10 мс вместо ~250 мс).

**Что даёт:**

- **низкая latency** — файлы рядом с пользователем
- **разгрузка origin** — твой сервер не упирается в раздачу `*.js`/`*.png`
- **защита от DDoS** — CDN абсорбирует трафик, до тебя доходит только живой
- **TLS termination** на edge — быстрее handshake
- **кэш на уровне HTTP** — управляется заголовками `Cache-Control`, `ETag`

**Что кладут в CDN:** статика (`*.css`, `*.js`, шрифты, картинки, видео), `index.html` SPA, иногда — кэш ответов API через `Cache-Control: public, max-age=...`.

**Популярные:** `Cloudflare`, `Fastly`, `AWS CloudFront`, `Akamai`, `bunny.net`.',
                'difficulty' => 2,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'В чём разница между Redis и Memcached?',
                'answer' => 'Оба — **in-memory хранилища**, но **Memcached делает одно и хорошо**, а **Redis умеет много всего**.

| | Memcached | Redis |
| --- | --- | --- |
| **Тип данных** | только строки/блобы | **строки, hashes, lists, sets, sorted sets, streams, bitmaps, HyperLogLog, geo** |
| **Threading** | **multi-threaded** | single-threaded I/O loop (Redis 6+ — multi-threaded I/O) |
| **Persistence** | нет (в OSS) | **RDB snapshots + AOF** |
| **Репликация** | нет (в OSS) | **master/replica + Sentinel** |
| **Шардирование** | client-side | **Redis Cluster** (встроено) |
| **Аллокатор** | slab (фиксированные классы) | jemalloc (фрагментация выше, гибче) |
| **Lua-скрипты** | нет | **есть** (атомарные операции) |
| **Pub/Sub, очереди** | нет | **есть** |
| **Транзакции** | нет | `MULTI/EXEC` + `WATCH` |

**Когда что:**

**Memcached** — когда нужен:

- **Только LRU-кэш строк** (HTML-фрагменты, сериализованные объекты)
- **Шардирование клиентом** и предсказуемая slab-память
- **Максимальная пропускная способность** на multi-core (нет single-threaded bottleneck)
- Простое, скучное, надёжное — без лишних опций

**Redis** — когда нужны:

- **Структуры данных:** rate limit на sorted set, leaderboards, sessions с TTL по ключу, sets для уникальных счётчиков
- **Очереди задач** (через lists, streams, BullMQ, Laravel queue)
- **Pub/Sub** для realtime
- **Persistence** (пережить рестарт)
- **Replication** для HA или read scaling

**На практике в современных проектах** Redis закрывает 90% задач — Memcached остался в legacy или там, где multi-threaded throughput критичен.',
                'difficulty' => 3,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Почему cache invalidation - это сложно?',
                'answer' => 'Знаменитая цитата Phil Karlton: **«There are only two hard things in CS: cache invalidation and naming things.»**

**Почему действительно сложно:**

1. **Трудно понять, когда данные устарели** — кэш не знает о бизнес-логике; «свежесть» зависит от домена
2. **Одно изменение в БД может затронуть много ключей кэша** — обновили пост → надо инвалидировать его карточку, список постов автора, теги, главную, ленту
3. **В распределённой системе инвалидация сама требует консистентности** — между несколькими Redis, между L1 (in-process) и L2 (Redis), между разными датацентрами
4. **Баланс между актуальностью и производительностью** — короткий TTL = свежесть + нагрузка на БД; длинный TTL = stale data
5. **Race conditions** — между чтением, обновлением БД и инвалидацией возможны окна, когда кэш «свежий старый»

**Подходы (от простого к сложному):**

| Подход | Как работает | Плюсы | Минусы |
| --- | --- | --- | --- |
| **TTL** | данные живут N секунд | проще всего | до TTL — stale |
| **Версионирование ключей** | в ключ зашита версия (`post:42:v3`) | мгновенная инвалидация по версии | растёт key space |
| **Tag-based** | ключи помечены тегами, инвалидируем тег | гибко | нужен поддерживающий драйвер (Laravel `Cache::tags()` — только в Redis/Memcached) |
| **Event-driven** | observer/event сбрасывает ключи при `save()` | свежесть на штатном пути | пропуски при прямом SQL/import |
| **CDC (Change Data Capture)** | Debezium/binlog → invalidator | работает на любых апдейтах БД | сложная инфра |
| **Stale-while-revalidate** | отдать старое, обновить в фоне | без latency-спайков | timing inconsistency |

**Правило большого пальца:** **начни с TTL** (короткий — для важного, длинный — для редкого). Усложняй, только когда видишь конкретную проблему.',
                'code_example' => '<?php
// 1) TTL — простейшая страховка
Cache::put("posts", $posts, now()->addMinutes(5));

// 2) Версионирование — глобальный сброс
$ver = Cache::get("posts:version", 1);
Cache::remember("posts:list:v$ver", 3600, fn () => Post::all());
// При обновлении любого поста:
Cache::increment("posts:version");

// 3) Tag-based (Redis / Memcached)
Cache::tags(["posts", "user-{$post->user_id}"])->remember(
    "post:$id", 3600, fn () => Post::find($id)
);
// Инвалидация:
Cache::tags(["user-42"])->flush();

// 4) Event-driven через model observer
class PostObserver
{
    public function saved(Post $post): void
    {
        Cache::forget("post:{$post->id}");
        Cache::tags("posts-list")->flush();
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Какие основные структуры данных предоставляет Redis и где их применяют?',
                'answer' => '**Redis** — не просто **key-value**, а **сервер структур данных**. Все они хранятся под обычным строковым ключом, но команды специфичны для типа.

**Основные типы:**

| Тип | Команды | Размер | Типовые применения |
|---|---|---|---|
| **String** | `SET`, `GET`, `INCR`, `APPEND` | до 512 МБ | счётчики (`INCR`), кэш сериализованных значений, JWT-токены |
| **List** | `LPUSH`, `RPOP`, `LRANGE`, `BLPOP` | до 4B элементов | простые очереди задач (FIFO/LIFO), recent activity feed |
| **Hash** | `HSET`, `HGET`, `HINCRBY`, `HGETALL` | до 4B полей | объекты целиком (User, Session), экономия памяти vs JSON |
| **Set** | `SADD`, `SISMEMBER`, `SINTER`, `SUNION` | до 4B элементов | уникальные visitors, **проверка принадлежности O(1)**, friendship-relations |
| **Sorted Set (ZSet)** | `ZADD`, `ZRANGE`, `ZRANGEBYSCORE` | до 4B элементов | **leaderboards**, time-series по timestamp, rate-limiter |
| **Stream** | `XADD`, `XREAD`, `XGROUP CREATE`, `XACK` | append-only | event log, **очереди с consumer groups** (как лёгкий Kafka) |
| **Bitmap / Bitfield** | `SETBIT`, `BITCOUNT`, `BITOP` | до 512MB бит | флаги активности по дням, A/B-testing groups |
| **HyperLogLog** | `PFADD`, `PFCOUNT` | ~12KB | **приближённый** подсчёт уникальных (миллионы юзеров с погрешностью 0.8%) |
| **Geo** | `GEOADD`, `GEORADIUS` | поверх ZSet | гео-поиск «рядом со мной» |

**Типовой выбор:**
- Счётчик просмотров поста → **String** (`INCR posts:42:views`).
- Топ-10 игроков → **Sorted Set** (`ZREVRANGE leaderboard 0 9`).
- Очередь задач → **List** (`LPUSH/BRPOP`) или **Stream** (если нужны acks).
- Множество онлайн-юзеров → **Set** (`SADD online:users :id`).
- Кэш модели пользователя → **Hash** (быстрее `SET <user-id> json` + читаешь только нужные поля).

**Подвох:** **big keys** (одно значение в гигабайт) блокируют **однопоточный** сервер. Большие коллекции читай через `HSCAN`/`SSCAN`/`ZSCAN`, не через `*GETALL`.',
                'difficulty' => 3,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Какие механизмы персистентности есть у Redis и в чём разница RDB и AOF?',
                'answer' => 'У Redis **два механизма** durability, и они часто **комбинируются**.

| | **RDB** (snapshot) | **AOF** (Append-Only File) |
|---|---|---|
| **Принцип** | бинарный **снимок** всей базы | **дозапись каждой** модифицирующей команды |
| **Триггер** | `save N M` (M изменений за N сек), `BGSAVE` | каждая `WRITE`-команда |
| **Размер файла** | **компактный** (бинарный, сжатый) | **крупнее** (текст команд) |
| **Скорость загрузки** | **быстрая** | медленнее (replay команд) |
| **Окно потерь при падении** | **минуты** (с последнего snapshot) | **до 1 секунды** или 0 |
| **Влияние на CPU** | fork(), copy-on-write | постоянная запись |

**RDB:**

- Через `fork()` дочерний процесс пишет дамп на диск, родитель продолжает обслуживать
- **Copy-on-write** Linux позволяет сэкономить память — модифицируемые страницы копируются по требованию
- **Подвох:** при write-heavy нагрузке fork может удвоить RSS

**AOF:**

- Три режима **fsync**:
  - **`always`** — `fsync` после каждой команды, потерь нет, **очень медленно**
  - **`everysec`** *(default)* — `fsync` раз в секунду, потеря **до 1 сек**
  - **`no`** — на усмотрение ОС, потеря **минуты**
- Периодически **`BGREWRITEAOF`** — компактифицирует файл (например, 1000 `INCR` сворачиваются в один `SET`)

**Когда что:**

- **Только кэш, потеря допустима** → выключить оба (`save ""` + `appendonly no`), при падении — холодный старт
- **HA с минимальной потерей** → **оба**: RDB для быстрого warm-restart + AOF `everysec` для durability
- **Очень критичные данные** → AOF `always` + репликация + бэкап RDB
- **Большие объёмы памяти** (>50 ГБ) → отдельный slave для snapshot, чтобы fork не блокировал master

**В кластере / Sentinel** persistence не заменяет репликацию — это разные задачи.',
                'difficulty' => 4,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Redis Pub/Sub и чем он отличается от Redis Streams?',
                'answer' => 'Это **два разных способа** доставки сообщений в Redis с **противоположными гарантиями**.

| | **Pub/Sub** | **Streams** |
|---|---|---|
| **Модель** | fire-and-forget broadcast | append-only лог |
| **Команды** | `PUBLISH`, `SUBSCRIBE` | `XADD`, `XREAD`, `XGROUP`, `XACK` |
| **Хранение** | **нет**, только в момент доставки | **да**, в ключе с id |
| **Дисконнект подписчика** | **всё пропущенное теряется** | при подключении читает с нужного `id` |
| **Consumer groups** | нет (broadcast всем) | **да** (как Kafka) |
| **Подтверждение (`ack`)** | нет | **`XACK`** + pending list |
| **Backpressure** | нет (быстрый publish, медленный sub отстанет) | да, через `id`-курсор |
| **Гарантия доставки** | **at-most-once** | **at-least-once** |
| **С версии** | 2.0 | **5.0** (2018) |

**Pub/Sub:**

- `PUBLISH channel msg` рассылает **всем активным подписчикам** канала
- Никакой persistence — кто отключён, тот пропустил
- В Cluster Pub/Sub **шардируется** через `SPUBLISH`/`SSUBSCRIBE` (Redis 7+) или **broadcasted** по всем нодам

**Streams:**

- `XADD mystream * field1 value1` добавляет сообщение с **уникальным id** (`<ms>-<seq>`)
- `XREAD STREAMS mystream 0` — читать с начала
- `XGROUP CREATE mystream grp` + `XREADGROUP` — **consumer groups**: сообщение получает один consumer из группы
- `XACK mystream grp id` — подтверждение, иначе остаётся в **pending list** для retry

**Когда что:**

- **Realtime-уведомления**, чаты, presence, push (WebSocket fanout) → **Pub/Sub** (потеря допустима)
- **Очереди задач с гарантиями**, event log, replayable streams → **Streams**
- **Если есть Kafka** — Streams часто избыточен, его сила в том, что Redis уже есть

**В Laravel:** Broadcasting через `Redis::publish(...)` использует Pub/Sub. Очереди (`queue:work redis`) под капотом — **lists** (`LPUSH/BRPOP`), не Streams.',
                'difficulty' => 4,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как делаются транзакции в Redis через MULTI/EXEC и при чём тут WATCH?',
                'answer' => '`MULTI/EXEC` — **атомарный батч команд** в Redis, но **это не транзакции** в смысле `RDBMS`.

**Поток:**

1. `MULTI` — начало блока
2. Команды **буферизуются** на сервере (клиент получает `QUEUED` на каждую)
3. `EXEC` — выполняются **атомарно одним пакетом**, между ними **никто не вклинится**
4. `DISCARD` — отменить блок до `EXEC`

**Чем отличается от RDBMS-транзакций:**

| | **Redis MULTI/EXEC** | **RDBMS BEGIN/COMMIT** |
|---|---|---|
| **Атомарность исполнения** | **да** (никто не вклинится) | **да** |
| **Rollback при ошибке** | **нет** | **да** |
| **Ошибка одной команды отменяет остальные** | **нет** | **да** |
| **Isolation levels** | нет | да (`READ COMMITTED`, `SERIALIZABLE`) |
| **Cross-key через шарды** | **нет** (только один hash-slot в Cluster) | да |

**`WATCH` — оптимистическая блокировка (`CAS`):**

1. `WATCH key1 key2` — подписка на изменения
2. Читаешь значение, готовишь команды
3. `MULTI ... EXEC`
4. Если **между `WATCH` и `EXEC`** другой клиент изменил `key1` или `key2` → **`EXEC` вернёт `nil`**, блок **не выполнится**
5. Клиент **повторяет попытку**

Это **optimistic concurrency control** — никто не блокируется на чтение, конфликт обнаруживается на commit.

**Типовое применение** — атомарный счётчик с проверкой лимита:

```
WATCH user:42:credits
val = GET user:42:credits
если val >= 100:
    MULTI
    DECRBY user:42:credits 100
    INCR user:42:purchases
    EXEC          # nil → кто-то опередил, retry
```

**В Cluster** `MULTI/EXEC` ограничен **ключами одного hash-slot** — мульти-ключевые транзакции через шарды **не работают**. Решение: **hash tags** (`{user42}:credits` и `{user42}:purchases` попадут в один слот).

**Альтернатива** — **Lua-скрипт через `EVAL`** — тоже атомарен, без issue с `WATCH`-цикла, часто проще.',
                'difficulty' => 4,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Чем Redis Sentinel отличается от Redis Cluster?',
                'answer' => 'Это **два разных способа** обеспечения **HA в Redis** с разными целями.

| | **Sentinel** | **Cluster** |
|---|---|---|
| **Назначение** | High Availability (HA) | HA + **горизонтальное шардирование** |
| **Шардирование данных** | **нет** — весь датасет на каждом мастере | **да** — `16384` hash-slot между мастерами |
| **Топология** | 1 master + N replicas | M masters + N replicas каждому |
| **Failover** | Sentinel-процессы голосуют, повышают replica | встроенный gossip-протокол |
| **Объём данных** | ограничен RAM **одного узла** | сумма RAM всех мастеров |
| **Write throughput** | ограничен **одним мастером** | масштабируется по мастерам |
| **Cross-key операции** | работают (всё на одном) | **только в одном hash-slot** |
| **Клиент** | спрашивает Sentinel про текущего мастера | редиректы `MOVED`/`ASK`, smart client |

**Sentinel:**

- **3+ Sentinel-процесса** (нечётное число для кворума) мониторят master и replicas
- При падении мастера `Sentinel`-процессы **голосуют** (`quorum`), повышают replica в master
- Клиент подключается **к Sentinel**, спрашивает «кто сейчас master?», потом идёт туда
- Простая модель, **не масштабирует запись**

**Cluster:**

- Ключ хешируется в `slot = CRC16(key) % 16384`
- Каждый slot принадлежит одному мастеру
- При запросе не на тот мастер — `MOVED slot ip:port`, клиент идёт куда сказали
- **Resharding** на лету через перенос слотов
- **Минимум 3 мастера + 3 реплики** для HA
- **Cross-slot** операции (`MGET k1 k2`, `MULTI/EXEC`, Lua) **работают только** если ключи в одном slot — используется **hash tag** `{group}:k1`, `{group}:k2`

**Когда что:**

- **Датасет помещается в один сервер**, нужна HA → **Sentinel**
- **Датасет > RAM одного сервера** или **писать надо больше, чем выдерживает один master** → **Cluster**
- **Управляемый сервис** (AWS ElastiCache, Upstash, Aiven) — выбираешь режим по тарифу',
                'difficulty' => 4,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Зачем в Redis Lua-скрипты через EVAL?',
                'answer' => '`EVAL` выполняет **Lua-скрипт на сервере атомарно** — пока он работает, **никакая другая команда не вклинится**. Это позволяет реализовать сложную логику без race condition между чтением и записью.

**Зачем нужно:**

- **read-modify-write** одним вызовом без `WATCH`/retry-цикла
- **атомарные условные операции** («декремент только если >0»)
- **снижение RTT** — несколько команд за один round-trip
- **сложная логика** на сервере без затаскивания данных в приложение

**Команды:**

- **`EVAL "script" numkeys key1 key2 arg1 arg2`** — выполнить
- **`SCRIPT LOAD "script"`** + **`EVALSHA <sha1>`** — закэшировать на сервере, потом вызывать по SHA (экономит трафик)

**Классические применения:**

| Задача | Зачем Lua |
|---|---|
| **Token bucket rate limiter** | прочитать tokens, обновить timestamp, decrement — атомарно |
| **Безопасное снятие distributed lock** | `DEL` только если значение `== owner_token` |
| **Атомарный счётчик с лимитом** | `INCR + проверка лимита + откат` |
| **Очередь с приоритетом** | переместить из `pending` в `processing` по условию |
| **Bloom filter / probabilistic** | сложная битовая логика |

**Пример — безопасный unlock:**

```lua
if redis.call("GET", KEYS[1]) == ARGV[1] then
    return redis.call("DEL", KEYS[1])
else
    return 0
end
```

Без Lua пришлось бы делать `GET → CMP → DEL`, между которыми другой клиент мог занять lock.

**Грабли:**

- **Длинный скрипт блокирует** весь **однопоточный** сервер — все остальные клиенты ждут
- `lua-time-limit` (default 5s) — превышение даёт `BUSY`, дальше только `SCRIPT KILL` или `SHUTDOWN NOSAVE`
- В **Cluster** `KEYS` должны быть в **одном hash-slot** (иначе `CROSSSLOT`)
- Скрипт **не должен зависеть от времени** на сервере — для **детерминированной** репликации (`SCRIPT EFFECTS REPLICATION` mode частично решает)
- **Не использовать `KEYS *`** или `SCAN` в скрипте — медленно',
                'difficulty' => 4,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Redis pipelining и почему он ускоряет работу?',
                'answer' => '**Pipelining** — клиент **шлёт пачку команд одним пакетом**, не дожидаясь ответов после каждой, и потом читает все ответы подряд.

**Что устраняется — RTT:**
- В обычном режиме: команда → **ждём ответа** → команда → ждём → ... Каждая операция стоит **один RTT** (round-trip time) до сервера.
- В pipelining: отправили **1000 команд** одним TCP-batch → получили **1000 ответов** одним batch.

**Масштаб ускорения:**
- При **RTT ≈ 1ms**: 1000 операций без pipelining = **~1 секунда**.
- С pipelining = **~10ms** (упирается уже не в сеть, а в parsing и обработку).
- Ускорение **~100x** на медленной сети, ~10x на локальной.

**Что НЕ делает pipelining:**
- **Не делает команды атомарными** — между ними **может вклиниться** другой клиент. Это **не транзакция** (`MULTI/EXEC`).
- Не уменьшает CPU-нагрузку Redis — те же команды, только без RTT.
- Не работает с командами, чей результат нужен **для следующей** команды (есть зависимость).

**Где особенно помогает:**
- **Массовый прогрев** кэша (`SET key1 v1`, `SET key2 v2`, …, ×1000).
- **Bulk-чтение**: один `MGET` лучше, но если нужно с разными командами — pipelining.
- **Импорт** данных в Redis (миграция, seed).
- Сбор **метрик** / агрегация счётчиков перед сохранением.

**В Laravel:**
- **`Redis::pipeline(function ($pipe) { ... })`** — буферизует команды и шлёт одним пакетом.
- **`Redis::transaction(...)`** — то же + атомарность через `MULTI/EXEC`.

**Подвох:** pipeline-buffer **расходует память** клиента и сервера — не запихивать **миллионы** команд одним пайплайном, делить на батчи по 1000-10000.',
                'difficulty' => 3,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое hot key и big key в Redis и чем они опасны?',
                'answer' => 'Это **две типичные патологии** в production-Redis, которые ломают latency несмотря на «нормальные» средние метрики.

| | **Hot key** | **Big key** |
|---|---|---|
| **Что это** | один ключ с непропорционально высокой нагрузкой | одно значение слишком большое |
| **Чем опасен** | весь load в **один мастер** в Cluster | команды над ним **блокируют** однопоточный сервер |
| **Как обнаружить** | `redis-cli --hotkeys` (нужен LFU), monitoring per-key ops | `redis-cli --bigkeys`, `MEMORY USAGE key` |
| **Видно в метриках** | один shard ест CPU, остальные idle | spike в p99 latency на всех клиентах |

**Hot key — детали:**

- Ключ имеет один **hash-slot** → попадает на один мастер в Cluster
- 100k RPS на одну страницу `home:featured` = 100k RPS в один Redis-процесс
- **Симптомы:** `redis-cli --hotkeys`, CPU одного шарда 95%, остальные 5%
- **Решения:**
  1. **Локальный in-process кэш** перед Redis (L1/L2) — Octane/RoadRunner
  2. **Реплики** для read scaling (если read-heavy)
  3. **Шардирование значения**: `key:1`, `key:2`, ..., `key:N` — клиент пишет в случайный, читает из случайного и мерджит
  4. **CDN перед API** (если данные публичные)

**Big key — детали:**

- Список / хеш / set с миллионами элементов или строка в гигабайт
- `LRANGE`, `HGETALL`, `SMEMBERS` блокируют сервер на **десятки/сотни мс**
- `DEL` большого ключа тоже блокирует — используй **`UNLINK`** (async free)
- Сложности с репликацией и failover (большой ключ долго копируется)
- **Решения:**
  1. **Разбиение**: `user:42:posts:page:1`, `user:42:posts:page:2` вместо одного огромного списка
  2. **Hash вместо одного blob**: `HMSET user:42 name "..." age 30` вместо `SET user:42 json` (читай только нужные поля)
  3. **Сканирование через `HSCAN`/`SSCAN`/`ZSCAN`** курсором, не `*GETALL`
  4. **Лимит на сторону приложения**: если коллекция превышает N — выносить в БД/S3
  5. **`MEMORY USAGE key`** в мониторинге, алерт на ключи >10 МБ

**Анти-паттерн:** хранить **всю историю** действий пользователя в одном списке. Через год это многомегабайтный big key и hot key одновременно.',
                'difficulty' => 4,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое negative caching и зачем он нужен?',
                'answer' => '**Negative caching** — кэширование **самого факта «ничего нет»**. Если запрос к БД вернул `null` или `404`, мы кладём в кэш **специальный маркер** с коротким TTL.

**Проблема, которую решает — cache penetration:**

- Атакующий или баг шлёт запросы на **несуществующие `id`**: `/users/9999991`, `/users/9999992`, ...
- Кэш пуст → каждый запрос **идёт в БД**
- БД выполняет `SELECT WHERE id = ?` (даже с индексом — это I/O)
- При высоком RPS БД захлёбывается, **stampede в худшем виде**

**Решение — negative caching:**

```php
$user = Cache::remember("user:$id", 60, function () use ($id) {
    return User::find($id) ?? "MISSING"; // сентинел вместо null
});
if ($user === "MISSING") return null;
```

Или явно через два вызова: `Cache::has` + специальный sentinel.

**Параметры:**

- **TTL у negative cache** делают **коротким** (30 сек – 5 мин), чтобы **новая запись** стала видимой быстро после создания
- **Marker** должен отличаться от valid-значений (`"::NULL::"`, `null`-объект, отрицательный id)

**Альтернатива/дополнение — Bloom filter:**

- **Bloom filter** — probabilistic структура: «такого id **точно нет**» или «**возможно есть**»
- Проверяется **перед** Redis: если bloom говорит «нет» → возвращаем `null` без сети
- **`PFADD/PFCOUNT`** (HyperLogLog — это другое), bloom — внешняя либа (`redis-bloom` module)
- **Плюс:** O(1), без обращения к хранилищу
- **Минус:** false positives возможны (false negatives — нет)

**Когда применять:**

- **Открытые API** с предсказуемыми id (`/users/42`, `/posts/123`) — защита от penetration
- **Lookup на существование** — `email exists?`, `username taken?`
- **Кэш `404`-ответов** на edge (Cloudflare умеет)

**Грабли:**

- Слишком длинный TTL → новая запись «не видна» после `INSERT` (запросто 5 минут «такого юзера нет»)
- Не путать с **rate limiting** — это разные защиты, обычно используются вместе',
                'difficulty' => 4,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Зачем добавлять jitter к TTL в кэше?',
                'answer' => '**Проблема — синхронное истечение (cache stampede):**
- Множество ключей создано **одной операцией** с **одинаковым TTL** (типичный пример — прогрев кэша при деплое, batch-импорт каталога).
- Все эти ключи **истекут одновременно**.
- В эту секунду **все промахи** одновременно ударят по БД → пиковая нагрузка → возможен отказ БД.

**Jitter — что это:**
- Добавление **случайной поправки** к TTL: `ttl + rand(0, ttl * 0.1)` — разброс ±10%.
- Истечение **размазывается во времени**.
- На каждые 100 ключей с TTL=60s истечение происходит **равномерно** между 60s и 66s.

**Пример в PHP/Laravel:**
- `Cache::put($key, $value, 60 + random_int(0, 6))`.
- При `remember` — обернуть `fn() => ...` так, чтобы возвращаемый TTL варьировался.

**Когда особенно нужно:**
- **Прогрев** кэша при деплое (`php artisan cache:warmup` — N тысяч ключей).
- **Batch-импорт** товаров / постов / документов.
- **Расписания** (`cron`) — задача каждый час обновляет все ключи.

**Что комбинируется с jitter:**
- **Single-flight lock** (`Cache::lock(...)`) — только один процесс пересчитывает, остальные ждут.
- **Probabilistic early expiration** (XFetch) — иногда обновлять кэш **до** истечения, с вероятностью растущей к моменту TTL.
- **Stale-while-revalidate** (`Cache::flexible([$fresh, $stale], ...)`) — отдавать устаревшее, асинхронно обновляя.

**Подвох:** jitter не помогает, если **одновременно** запрошено **несколько кэш-промахов** на разные ключи под одного пользователя — нужны single-flight на сами вычисления.',
                'difficulty' => 3,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое hit ratio и о чём говорит низкий показатель?',
                'answer' => '**Hit ratio** — основная метрика эффективности кэша:

`hit_ratio = hits / (hits + misses)`

- **Hit** — ключ нашёлся в кэше.
- **Miss** — ключа нет → пришлось идти к источнику (БД, API).

**Ориентиры:**
- **Зрелый кэш горячих данных** — обычно **90+%**.
- **Cold start** после деплоя — низкий, постепенно растёт.
- **Системный** кэш ОС / CDN — часто **>99%**.

**Низкий hit ratio (<70%) — возможные причины:**

| Причина | Признак | Решение |
|---|---|---|
| **TTL слишком короткий** | ключи истекают **раньше** повторного запроса | увеличить TTL, добавить **jitter** |
| **Памяти не хватает** | работает **eviction**, нужные ключи выкидываются | поднять `maxmemory`, поменять `maxmemory-policy`, шардировать |
| **Слишком большое key-space** | плоское распределение (long tail), few popular items | архитектурно кэш не помогает, нужен **CDN** / другая стратегия |
| **Неправильный ключ** | ключ включает timestamp / uuid / random — каждый запрос уникален | нормализовать ключ, убрать нестабильные части |
| **Cold cache** после деплоя | редкий запрос на старте | **прогрев кэша** (`cache:warmup`), staged rollout |
| **Cache bypass** | код часто читает напрямую из БД (`->fresh()`, `withoutCache`) | проверить вызовы |

**С чем смотреть вместе:**
- **`evicted_keys`** — сколько ключей вытеснено по `maxmemory-policy`.
- **`used_memory`** / **`maxmemory`** — близко к лимиту?
- **`keyspace_misses`** / **`keyspace_hits`** — нативные счётчики Redis.
- **`expired_keys`** — сколько истекло по TTL.
- **Распределение по ключам** — `redis-cli --hotkeys` (если включён LFU).

**Подвох:** высокий hit ratio **не всегда хорошо** — может означать, что **TTL слишком длинный** и пользователи видят **устаревшие** данные. Метрика балансируется с **freshness** требованиями домена.',
                'difficulty' => 3,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое многоуровневый кэш (L1/L2) и какие у него подводные камни?',
                'answer' => '**Многоуровневый кэш** — комбинация **локального in-process** (`L1`) и **общего распределённого** (`L2`) кэша.

**Сравнение уровней:**

| | **L1** (in-process) | **L2** (distributed) |
|---|---|---|
| **Где живёт** | RAM приложения | Redis / Memcached |
| **Драйверы** | **`APCu`**, **`array`** в long-running воркере, in-memory map | Redis, Memcached |
| **Latency** | **наносекунды** (без сети) | **0.5–2 мс** (сеть) |
| **Объём** | МБ–десятки МБ на воркер | ГБ–десятки ГБ |
| **Делится между процессами** | **нет** | **да** |
| **Переживает рестарт** | нет | зависит от persistence |

**Поток чтения:**

1. **L1** — есть? → отдать
2. **L2** — есть? → положить в L1 → отдать
3. **DB** — fetch → положить в L1 и L2 → отдать

**Зачем нужно:**

- **L1 экономит сеть** — для горячих ключей `1µs` vs `1ms`, разница 1000x
- **L2 гарантирует cross-process consistency** — все инстансы видят одно

**Главная проблема — инвалидация L1:**

При `UPDATE` надо **сбросить L1 на ВСЕХ воркерах сразу**, иначе разные инстансы возвращают разные значения. L1 — это «карманные копии», их нельзя обойти.

**Стратегии инвалидации L1:**

| Стратегия | Принцип | Подходит |
|---|---|---|
| **Короткий TTL** | L1 живёт `5–30 сек`, eventually consistent | большинство случаев |
| **Pub/Sub broadcast** | при invalidate шлём `PUBLISH` → все воркеры сбрасывают L1 | критичная свежесть |
| **Version stamp** | в L2 хранится `version`, L1 хранит пару `(data, version)`, на чтение сверяет | без pub/sub |
| **Event-driven** | при `Model::saved` шлём в очередь invalidate-job | через систему очередей |

**Грабли в Octane / RoadRunner:**

- **L1 переживает запросы** (это long-running воркер!) — нельзя класть user-specific данные в `static`/singleton, иначе следующий запрос увидит чужие
- **Memory leak** — растёт array кэш без выселения
- **Stale на одном воркере** — два запроса к одному инстансу видят разное, балансер маскирует
- **Десериализация** — L1 хранит объект, L2 хранит сериализованный blob, надо учитывать в TTL-сравнении

**Не путать с L1/L2 в Hibernate** (Java) — там terminology та же, но L1 — на сессию (per-request), L2 — на app instance.',
                'difficulty' => 4,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что произойдёт, если Redis упал? Как проектировать систему, чтобы пережить это?',
                'answer' => "Redis — **stateful** компонент, его падение влияет на много частей CRM-системы. Что именно ломается зависит от того, **как** ты его используешь.\n\n**Что страдает при падении Redis**:\n\n| Использование | Что произойдёт | Как пережить |\n| --- | --- | --- |\n| **Cache** | каждый запрос идёт в БД, нагрузка ×10-100 | fallback на источник правды (БД); rate-limit входящих запросов |\n| **Sessions** | юзеры разлогинены | вторая нода Redis в Sentinel; fallback на DB-сессии |\n| **Queue** | джобы не запускаются, **очередь не пишется** | переключение на другой брокер; persist queue (RabbitMQ/SQS) |\n| **Rate limiter** | все лимиты сброшены | fallback на in-memory лимиты с большим запасом |\n| **Distributed lock** | блокировок нет — две операции могут выполниться одновременно | критические операции — DB-уровень блокировок; не-критические — `best-effort` |\n| **Pub/Sub** | события не доставляются | дублирование через persist-механизмы (БД + воркер) |\n| **Источник правды** (счётчики, остатки) | **потеря данных** | **никогда не использовать Redis как единственный источник правды** |\n\n**Главное правило**: Redis — **ускоритель** или **транспорт**, не **источник правды**. Любая критичная данность должна жить в **БД с durability** (PostgreSQL), а Redis — производное.\n\n**Стратегии устойчивости**:\n\n1. **Cache** с fallback в коде:\n   - `try { Redis-call } catch { DB-call }` — на каждое чтение.\n   - **Stale-while-revalidate**: показать просроченный кеш + асинхронно обновить.\n   - **Circuit breaker** — после N подряд таймаутов перейти в режим «Redis недоступен», читать только из БД.\n2. **HA-конфигурация Redis**:\n   - **Sentinel** — автоматический failover между master и replica.\n   - **Redis Cluster** — sharding + replication, переживает падение части нод.\n   - **Persistence** — `AOF` (append-only file) + `RDB`-снапшоты. Не спасает от полного падения процесса, но даёт восстановиться.\n3. **Sessions**:\n   - **Sticky sessions** на балансировщике — пользователь идёт на тот же сервер. При смерти сервера разлогинится.\n   - **DB-fallback** — `config/web.php` `session.class = DbSession`. Медленнее, но устойчивее.\n   - **Cookie-based sessions** (Laravel) — токен в куке, данные в Redis. При падении Redis — разлогин, но cookie остаётся.\n4. **Queues**:\n   - **Durable queue** (RabbitMQ, SQS) — джобы переживают рестарт.\n   - **Database queue** на старте проекта — медленнее, но stop-the-world не теряет джобы.\n   - **Persist `IGNORE`** в Redis queue — `appendonly yes` + `appendfsync everysec`.\n\n**Поведение Yii2 / Laravel при недоступном Redis**:\n\n- **Yii2 `redis` cache** — бросит `\\\\yii\\\\redis\\\\SocketException`. По умолчанию **не ловится** — упадёт страница 500. Лечится через `try/catch` в `getOrSet()` или через `\\\\yii\\\\caching\\\\Cache::set('keyPrefix', ...)` с fallback-кешем.\n- **Laravel `Cache::store('redis')`** — кинет `RedisException`. Если используешь `Cache::remember()`, оберни в try/catch.\n- **Queue worker** — `php artisan queue:work` зациклится в `Connection refused`, спамит лог. Решение: supervisor с backoff, alert при > 5 fail.\n\n**Что нужно сделать заранее**:\n\n- **Healthcheck** на Redis — алёрт через Slack / Telegram при недоступности.\n- **Метрика hit rate** — если падает к нулю, кеш не работает (даже если Redis жив).\n- **Тест прода** — отключить Redis на staging и убедиться, что система **деградирует, но работает** (read-only режим).\n- **Документировать** — какие данные потеряются при падении Redis, что менеджеры увидят на UI («не удаётся применить промокод, попробуйте позже» вместо 500).",
                'code_example' => "<?php\n// 1) Cache с fallback на БД\nfunction getCustomer(int \$id): ?Customer\n{\n    try {\n        return Yii::\\\$app->cache->getOrSet(\n            \"customer:{\$id}\",\n            fn () => Customer::findOne(\$id),\n            300\n        );\n    } catch (\\Throwable \$e) {\n        Yii::warning('Redis недоступен: ' . \$e->getMessage(), 'cache');\n        return Customer::findOne(\$id); // деградируем, но работаем\n    }\n}\n\n// 2) Circuit breaker — простая реализация\nclass CacheCircuitBreaker\n{\n    private static int \$failCount = 0;\n    private static int \$openUntil = 0;\n\n    public function get(string \$key, callable \$fallback)\n    {\n        if (time() < self::\\\$openUntil) {\n            return \$fallback(); // circuit open — Redis считаем мёртвым\n        }\n\n        try {\n            \$result = Yii::\\\$app->cache->get(\$key);\n            self::\\\$failCount = 0; // успех — обнуляем счётчик\n            return \$result !== false ? \$result : \$fallback();\n        } catch (\\Throwable \$e) {\n            if (++self::\\\$failCount >= 5) {\n                self::\\\$openUntil = time() + 30; // 30 сек не пытаемся\n            }\n            return \$fallback();\n        }\n    }\n}\n\n// 3) Конфиг fallback для sessions (Yii2)\n// config/web.php\n'components' => [\n    'session' => [\n        'class' => YII_ENV_PROD ? \\yii\\redis\\Session::class : \\yii\\web\\DbSession::class,\n        // На проде — Redis для скорости. Если Redis в HA-сетапе (Sentinel),\n        // переключится автоматически. Иначе нужна обвязка для fallback на db\n    ],\n],\n\n// 4) Healthcheck эндпоинт\npublic function actionHealthcheck()\n{\n    \$health = ['ok' => true, 'components' => []];\n\n    try {\n        Yii::\\\$app->redis->ping();\n        \$health['components']['redis'] = 'ok';\n    } catch (\\Throwable \$e) {\n        \$health['components']['redis'] = 'down';\n        \$health['ok'] = false;\n    }\n\n    Yii::\\\$app->response->statusCode = \$health['ok'] ? 200 : 503;\n    return \$health;\n}\n\n# Sentinel-конфиг для HA\n# /etc/redis/sentinel.conf\nsentinel monitor mymaster 10.0.0.1 6379 2\nsentinel down-after-milliseconds mymaster 5000\nsentinel failover-timeout mymaster 10000\n\n# В клиенте указываем sentinel-ноды, не master напрямую\n# Yii2 redis-extension не поддерживает sentinel из коробки — используют\n# proxy типа twemproxy или предиктивный DNS",
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'system_design.caching',
            ],
        ];
    }
}
