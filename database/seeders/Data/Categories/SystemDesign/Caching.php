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
                'answer' => 'Write-behind - запись только в кэш, в БД асинхронно через некоторое время или батчем. Простыми словами: записал в блокнот, в большую тетрадь перепишу позже. Самая быстрая запись. Минусы: в наивной реализации (in-memory only) при падении кэша теряются ещё не сброшенные данные; production-решения (Caffeine + RDBMS, Hazelcast WriteBehind) добавляют WAL/persistence. Сложнее реализовать. Подходит для счётчиков, метрик, логов - где небольшая потеря не катастрофа.',
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
                'answer' => 'Cache stampede (он же dogpile, thundering herd) - когда популярный ключ истекает в кэше и тысячи запросов одновременно начинают перестраивать его, забивая БД. Простыми словами: магазин закрылся на учёт - все клиенты ломятся одновременно. Решения: 1) atomic lock на пересборку (SET NX + блокировка - только один запрос строит, остальные ждут или отдают stale); важно: сначала Cache::get, и только при miss - брать lock, чтобы не сериализовать cache hits; внутри lock - повторная проверка кэша (double-check), потому что пока ждали lock, другой воркер мог уже перестроить. 2) probabilistic early expiration (XFetch: с растущей вероятностью при подходе к TTL один воркер заранее перестраивает). 3) stale-while-revalidate (отдаём старое значение, пока в фоне обновляется новое). 4) refresh-ahead / cache warming - перестройка по cron / событию ДО истечения TTL, через очередь job-ов; пользовательский запрос всегда попадает на горячий ключ. (Не путать с BGREWRITEAOF - это команда Redis для перезаписи AOF-файла, к stampede отношения не имеет.)',
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
                'answer' => 'RDB периодически делает снимок всей базы на диск через fork процесса — компактный файл, быстрая загрузка, но при падении теряются данные с момента последнего снимка (минуты). AOF (Append-Only File) дозаписывает каждую модифицирующую команду; с fsync=everysec теряется максимум секунда, при always — ничего, ценой скорости. AOF файл крупнее RDB и периодически сжимается через rewrite. На практике в проде включают оба: RDB для быстрого восстановления, AOF для минимальной потери данных, либо отключают всё, если Redis используется только как кэш.',
                'difficulty' => 4,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Redis Pub/Sub и чем он отличается от Redis Streams?',
                'answer' => 'Pub/Sub — это fire-and-forget модель: подписчик получает только те сообщения, что пришли пока он подключён, нет хранения и offset, при дисконнекте всё пропущенное теряется. Streams — это persistent append-only лог: сообщения хранятся в ключе, у каждого есть id, поддерживаются consumer groups с подтверждением (XACK) и pending-list, как в Kafka. Pub/Sub годится для realtime-уведомлений, где допустима потеря, Streams — для очередей задач и event-логов с гарантиями at-least-once.',
                'difficulty' => 4,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как делаются транзакции в Redis через MULTI/EXEC и при чём тут WATCH?',
                'answer' => 'MULTI начинает блок команд, которые буферизуются на сервере и выполняются атомарно одним пакетом по EXEC — никакой другой клиент не вклинится между ними. Но это не транзакции в смысле RDBMS: rollback нет, ошибки исполнения отдельных команд не отменяют остальные. WATCH ключ_X добавляет оптимистическую блокировку: если до EXEC другой клиент изменит этот ключ, EXEC вернёт nil и весь блок не выполнится — клиент должен повторить попытку. Это паттерн optimistic concurrency control для Redis. В Redis Cluster MULTI/EXEC ограничен ключами одного hash-slot — мульти-ключевые транзакции через шарды не работают.',
                'difficulty' => 4,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Чем Redis Sentinel отличается от Redis Cluster?',
                'answer' => 'Sentinel — это сервис мониторинга поверх классической схемы master + реплики: он следит за здоровьем мастера, инициирует failover (повышает реплику в мастера) и сообщает клиентам новый адрес. Данные при этом не шардируются — весь датасет лежит на каждом узле. Cluster — это шардирование: ключи распределяются по 16384 hash-слотам между несколькими мастерами, каждый со своими репликами; failover встроен. Sentinel выбирают, когда нужна HA, но датасет помещается в один сервер; Cluster — когда нужно масштабировать запись или объём за пределы одной машины.',
                'difficulty' => 4,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Зачем в Redis Lua-скрипты через EVAL?',
                'answer' => 'EVAL выполняет Lua-скрипт на сервере атомарно: пока он работает, никакая другая команда не вклинится. Это позволяет реализовать сложную логику типа «прочитай ключ, проверь условие, обнови несколько ключей» одной операцией без race condition между чтением и записью. Классические применения — кастомные rate limiter (token bucket), безопасное снятие распределённого lock (DEL только если значение совпадает с владельцем), атомарные счётчики с лимитом. Минус: длинный скрипт блокирует весь однопоточный сервер, поэтому Lua должен быть быстрым.',
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
                'question' => 'Как сделать распределённый lock на Redis и что не так с наивным SETNX?',
                'answer' => 'Наивная схема SETNX key 1 без TTL опасна: если владелец упадёт, ключ останется навсегда и заблокирует всех. Правильный минимум — SET key uniq_token NX PX ttl_ms одной командой и при освобождении DEL только если значение совпадает (через Lua), иначе можно случайно удалить чужой lock после истечения TTL. Для multi-master Redis есть алгоритм Redlock от автора Redis: брать lock на N>=5 независимых узлах, считать успешным при majority. Redlock спорен: Martin Kleppmann в статье "How to do distributed locking" (2016) критиковал его за зависимость от системных часов и отсутствие fencing-токенов, для критичных кейсов берут etcd/ZooKeeper.',
                'difficulty' => 4,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое hot key и big key в Redis и чем они опасны?',
                'answer' => 'Hot key — ключ, на который идёт непропорционально много трафика; в Cluster он не распределяется по шардам, поэтому весь load бьёт в один мастер и упирается в его CPU. Лечится локальным кэшем перед Redis, репликой только под этот ключ или шардированием значения по версии (key:1, key:2, ...). Big key — слишком большое значение (мегабайты, миллионы элементов в коллекции): команды над ним блокируют однопоточный сервер на десятки миллисекунд и тормозят всех. Лечится разбиением (hash вместо одной строки), сканированием через HSCAN/SSCAN и мониторингом MEMORY USAGE.',
                'difficulty' => 4,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое negative caching и зачем он нужен?',
                'answer' => 'Negative caching — это кэширование самого факта «ничего нет»: если запрос к БД вернул null или 404, мы кладём в кэш специальный маркер с коротким TTL. Без этого каждый запрос несуществующего id бьёт мимо кэша прямо в БД — атакующий может уронить базу подбором случайных id (cache penetration). Альтернатива/дополнение — Bloom filter перед кэшем: он быстро отвечает «такого id точно нет», без обращения к хранилищу. TTL у негативного кэша делают коротким, чтобы новая запись стала видимой быстро.',
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
                'answer' => 'L1 — это локальный in-process кэш в памяти приложения (APCu, array в long-running воркере), L2 — общий распределённый кэш (Redis/Memcached). L1 даёт наносекундные обращения без сети, L2 — общую согласованность между инстансами. Главная проблема — инвалидация: при изменении данных надо сбросить L1 на всех воркерах сразу, иначе разные инстансы возвращают разные значения. Решают через короткий TTL на L1, pub/sub-уведомления об инвалидации или event-driven сброс. В Octane/RoadRunner об этом надо помнить отдельно: L1 переживает запросы.',
                'difficulty' => 4,
                'topic' => 'system_design.caching',
            ],
        ];
    }
}
