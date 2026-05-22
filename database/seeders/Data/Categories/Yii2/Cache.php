<?php

namespace Database\Seeders\Data\Categories\Yii2;

class Cache
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Yii2',
                'question' => 'Как использовать кэш в Yii2?',
                'answer' => 'Yii2 даёт **компонент `cache`** через `Yii::$app->cache` — единый API независимо от драйвера.

**Базовый API:**

- **`set($key, $value, $duration, $dependency = null)`** — сохранить с TTL (секунды).
- **`get($key)`** — получить значение или **`false`**, если ключа нет/истёк TTL.
- **`delete($key)`** — удалить по ключу.
- **`flush()`** — очистить **весь** кэш текущего компонента.
- **`exists($key)`** — проверить наличие (быстрее, чем `get`).
- **`multiSet`** / **`multiGet`** — пачкой.

**Важно:** `get` возвращает `false`, поэтому проверять надо строго через `=== false` (значение `0` или `""` валидное).',
                'code_example' => 'use Yii;
use app\\models\\User;

$cache = Yii::$app->cache;

// Запись на 1 час
$cache->set(\'users:active\', $users, 3600);

// Чтение с фолбэком
$users = $cache->get(\'users:active\');
if ($users === false) {
    $users = User::find()->where([\'status\' => 1])->all();
    $cache->set(\'users:active\', $users, 3600);
}

$cache->delete(\'users:active\');
$cache->flush();',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.cache',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Какие драйверы кэша есть в Yii2 и чем отличаются?',
                'answer' => 'Драйвер кэша задаётся в **конфиге компонента `cache`**. Все наследуют `yii\\caching\\Cache` и имеют одинаковый API.

| Драйвер | Класс | Где живёт | Когда брать |
| --- | --- | --- | --- |
| **FileCache** | `yii\\caching\\FileCache` | файлы в `runtime/cache` | dev/маленький проект, нет Redis |
| **DbCache** | `yii\\caching\\DbCache` | таблица в БД | если уже есть БД, нет Redis/Memcached |
| **ApcCache** | `yii\\caching\\ApcCache` | shared memory (APCu) | **только** один сервер, очень быстро |
| **MemCache** | `yii\\caching\\MemCache` | Memcached-сервер | кластер серверов |
| **RedisCache** | `yii\\redis\\Cache` (из `yii2-redis`) | Redis | **рекомендуемый prod-выбор** |
| **ArrayCache** | `yii\\caching\\ArrayCache` | память процесса | тесты, кэш на один HTTP-запрос |
| **DummyCache** | `yii\\caching\\DummyCache` | заглушка | отключить кэш в dev |

**RedisCache** требует пакет `yiisoft/yii2-redis`.',
                'code_example' => '// config/web.php
return [
    \'components\' => [
        // FileCache — без зависимостей
        \'cache\' => [
            \'class\' => \'yii\\caching\\FileCache\',
        ],

        // RedisCache (yiisoft/yii2-redis)
        // \'cache\' => [
        //     \'class\' => \'yii\\redis\\Cache\',
        //     \'redis\' => [
        //         \'hostname\' => \'localhost\',
        //         \'port\' => 6379,
        //         \'database\' => 0,
        //     ],
        // ],

        // MemCache
        // \'cache\' => [
        //     \'class\' => \'yii\\caching\\MemCache\',
        //     \'servers\' => [
        //         [\'host\' => \'127.0.0.1\', \'port\' => 11211],
        //     ],
        // ],
    ],
];',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.cache',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое cache dependency в Yii2?',
                'answer' => '**Cache dependency** — объект, который описывает условие, при изменении которого кэш считается **протухшим** (даже если TTL ещё не вышел).

**Стандартные классы (`yii\\caching\\*`):**

- **`FileDependency`** — кэш протухает при изменении **mtime** файла.
- **`DbDependency`** — задаётся SQL-запрос; если результат изменился — инвалидация.
- **`ExpressionDependency`** — PHP-выражение, его результат сравнивается с сохранённым.
- **`ChainedDependency`** — несколько зависимостей через `AND`/`OR` (флаг `dependOnAll`).
- **`TagDependency`** — пометить кэш тегом, потом инвалидировать всё с этим тегом через `TagDependency::invalidate($cache, [\'tag\'])`.

Зависимость **третий аргумент** в `set` или в `query->cache(..., $dependency)`.',
                'code_example' => 'use yii\\caching\\DbDependency;
use yii\\caching\\TagDependency;

// Кэш протухнет при изменении count(*) в orders
$dependency = new DbDependency([
    \'sql\' => \'SELECT COUNT(*) FROM orders\',
]);
Yii::$app->cache->set(\'stats\', $data, 3600, $dependency);

// Tag-инвалидация — пометили несколько ключей одним тегом
$tag = new TagDependency([\'tags\' => [\'users\']]);
Yii::$app->cache->set(\'user:1\', $u1, 0, $tag);
Yii::$app->cache->set(\'user:2\', $u2, 0, $tag);

// При обновлении любого пользователя — сбросить весь тег:
TagDependency::invalidate(Yii::$app->cache, [\'users\']);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.cache',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как работает query caching в Yii2?',
                'answer' => '**Query caching** — кэширование **результата SQL-запроса** на уровне `yii\\db\\Connection`. Есть **два способа**:

- **Через `$db->cache(callable, $duration, $dependency)`** — оборачивает все запросы внутри коллбэка.
- **Через `$query->cache($duration, $dependency)`** — точечно на конкретный `Query`/`ActiveQuery`.

**Требования:**

- В конфиге `db`-компонента должны быть **`enableQueryCache = true`** (по умолчанию) и **`queryCache => \'cache\'`** (ссылка на компонент кэша).
- Кэшируются **только SELECT**-запросы.
- `$query->noCache()` отключает кэш даже внутри `$db->cache(...)`.

**Не путать** с кэшированием результата ActiveRecord-объектов целиком — query cache хранит только raw-данные строк.',
                'code_example' => 'use Yii;
use app\\models\\Post;

// Способ 1: блоком
$posts = Yii::$app->db->cache(function ($db) {
    return Post::find()
        ->where([\'status\' => Post::STATUS_PUBLISHED])
        ->all();
}, 60);

// Способ 2: точечно через Query
$posts = Post::find()
    ->where([\'status\' => Post::STATUS_PUBLISHED])
    ->cache(60)
    ->all();

// Отключить кэш для отдельного запроса внутри блока
$fresh = Yii::$app->db->cache(function ($db) {
    $cached = Post::find()->all();
    $live = Post::find()->noCache()->count();
    return [$cached, $live];
}, 60);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.cache',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое fragment caching в Yii2?',
                'answer' => '**Fragment caching** — кэширование **куска отрендеренного HTML** внутри view. Оборачивается парой `beginCache()` / `endCache()`.

**Особенности:**

- Кэшируется уже готовый HTML — экономит и SQL, и время рендера.
- Принимает массив опций: `duration`, `dependency`, `variations` (разные версии под user/lang), `enabled` (отключить условно).
- **Вложенные** фрагменты допустимы.
- Если блок есть в кэше — `beginCache` возвращает `false`, и тело **не выполняется**.

Подходит для блоков типа «список последних новостей в сайдбаре», «футер с категориями».',
                'code_example' => '<?php
// views/site/index.php
use yii\\helpers\\Html;

if ($this->beginCache(\'latest-news\', [
    \'duration\' => 300,
    \'variations\' => [Yii::$app->language],
])) {
    $news = app\\models\\News::find()
        ->orderBy([\'created_at\' => SORT_DESC])
        ->limit(5)
        ->all();

    foreach ($news as $item) {
        echo Html::tag(\'h3\', Html::encode($item->title));
    }

    $this->endCache();
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.cache',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как работает page caching и HttpCache в Yii2?',
                'answer' => '**Page caching** — кэширование **целой страницы** контроллера через фильтр **`yii\\filters\\PageCache`** (наследник `Behavior`/`ActionFilter`).

- Подключается в `behaviors()` контроллера.
- Параметры: `only` (для каких экшенов), `duration`, `dependency`, `variations`.
- Кэширует **финальный ответ** — последующие запросы вообще не доходят до контроллера-экшена.

**`HttpCache`** — другой фильтр, **не** хранит контент сервера, а управляет **HTTP-кэшем браузера** через заголовки `Last-Modified` / `ETag`. При совпадении возвращает `304 Not Modified`.

| Фильтр | Где кэш | Что отвечает |
| --- | --- | --- |
| **`PageCache`** | сервер (Yii cache) | сразу из кэша, минуя экшен |
| **`HttpCache`** | браузер | `304` если не изменилось |',
                'code_example' => 'use yii\\filters\\PageCache;
use yii\\filters\\HttpCache;
use yii\\caching\\DbDependency;

public function behaviors()
{
    return [
        [
            \'class\' => PageCache::class,
            \'only\' => [\'index\'],
            \'duration\' => 60,
            \'variations\' => [Yii::$app->language],
            \'dependency\' => new DbDependency([
                \'sql\' => \'SELECT MAX(updated_at) FROM post\',
            ]),
        ],
        [
            \'class\' => HttpCache::class,
            \'only\' => [\'view\'],
            \'lastModified\' => function ($action, $params) {
                return (int) Post::find()->max(\'updated_at\');
            },
        ],
    ];
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.cache',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое schema caching в Yii2?',
                'answer' => '**Schema caching** — кэширование **метаданных таблиц БД** (список колонок, типы, PK, индексы), которые Yii запрашивает у БД через `INFORMATION_SCHEMA` / `SHOW COLUMNS` при работе ActiveRecord.

**Зачем:** без кэша на каждый запрос AR `Post::find()` Yii делает дополнительные запросы для построения схемы — это медленно.

**Настройка в конфиге `db`-компонента:**

- **`enableSchemaCache => true`** — включить.
- **`schemaCache => \'cache\'`** — ID кэш-компонента.
- **`schemaCacheDuration => 3600`** — TTL.
- **`schemaCacheExclude => []`** — таблицы, которые не кэшировать.

**Важно:** после миграции схемы (`migrate`) надо **сбросить** schema cache — иначе AR будет видеть старую структуру. Команда: `./yii cache/flush-schema`.',
                'code_example' => '// config/db.php (или config/main.php → components → db)
return [
    \'class\' => \'yii\\db\\Connection\',
    \'dsn\' => \'mysql:host=localhost;dbname=app\',
    \'username\' => \'root\',
    \'password\' => \'\',

    // Schema caching
    \'enableSchemaCache\' => true,
    \'schemaCache\' => \'cache\',
    \'schemaCacheDuration\' => 3600,
    \'schemaCacheExclude\' => [\'log\', \'session\'],
];

// После миграции — сбросить кэш схемы:
// ./yii cache/flush-schema',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.cache',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Чем fragment caching отличается от page и query caching?',
                'answer' => 'В Yii2 **три уровня кэширования** результатов, отличаются гранулярностью и расположением:

| Уровень | Что кэширует | Где задаётся | Скорость | Когда брать |
| --- | --- | --- | --- | --- |
| **Query cache** | результат **SQL-запроса** (raw rows) | `$db->cache(...)` или `$query->cache(...)` | средняя | дорогие SELECT, повторяющиеся выборки |
| **Fragment cache** | **HTML-кусок** во view | `beginCache/endCache` | высокая | блок «топ-5 новостей», сайдбар |
| **Page cache** | **весь ответ экшена** | фильтр `PageCache` в `behaviors()` | максимальная | анонимные страницы (главная, статика) |

**Правило выбора:**

- **Page cache** — максимальный эффект, но **не подходит** для персонализированных страниц.
- **Fragment cache** — компромисс: страница динамическая, кэшируем только тяжёлые куски.
- **Query cache** — точечно, если HTML строится сложным образом и хранить его невыгодно.

Все три используют **один компонент `cache`** под капотом.',
                'code_example' => '// Page cache (весь экшен)
public function behaviors()
{
    return [
        [\'class\' => \\yii\\filters\\PageCache::class, \'only\' => [\'home\'], \'duration\' => 60],
    ];
}

// Fragment cache (HTML-блок во view)
if ($this->beginCache(\'sidebar\', [\'duration\' => 300])) {
    echo $this->render(\'_sidebar\');
    $this->endCache();
}

// Query cache (только SQL-результат)
$posts = Post::find()->where([\'status\' => 1])->cache(60)->all();',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.cache',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое cache stampede / dogpile и как с ним бороться в Yii2: locks, soft TTL, probabilistic early expiration, TagDependency?',
                'answer' => '**Проблема:** популярный ключ кэша истёк, **тысячи** запросов одновременно увидели `cache->get() === false`, **все** пошли пересчитывать → БД ложится под нагрузкой пересчёта. Это и есть **cache stampede** (он же **thundering herd**, **dogpile**).

**4 стратегии защиты:**

| Стратегия | Идея | Когда брать |
|---|---|---|
| **Mutex / lock** | пересчитывает **один** поток, остальные ждут или отдают старое | один сервер, низкая нагрузка |
| **Soft TTL** (early refresh) | хранить **2 TTL** — soft и hard, обновлять заранее | средняя нагрузка, есть фоновые джобы |
| **Probabilistic early expiration** (XFetch) | каждый запрос с **возрастающей вероятностью** обновляет до истечения | равномерная нагрузка |
| **Stale-while-revalidate** | отдавать просроченное + асинхронно обновлять | критичная latency, любой объём |

**1. Mutex lock в Yii2:**

```
if (!$cache->get($key)) {
    if (Yii::$app->mutex->acquire($key, 0)) {
        try { /* recompute + set */ } finally { Yii::$app->mutex->release($key); }
    } else {
        // другой поток уже считает — спим или отдаём stale
    }
}
```

**2. Soft TTL (двухуровневый):**

- Хранить `[\'value\' => ..., \'softExpire\' => time + 50min]` с **hard TTL = 60min**.
- Если `softExpire < now < hardTTL` → отдать `value` + **триггернуть джобу** на обновление.

**3. Probabilistic early expiration (XFetch, Vattani 2015):**

- При чтении вычислять: `recompute = now - delta * beta * ln(rand) >= expiry`.
- Чем ближе к истечению — тем выше вероятность обновить **досрочно**.
- Только **один-два** потока «случайно решат» обновить заранее → стампеда нет.

**`Cache::getOrSet` — встроенная защита (Yii 2.0.11+):**

- `getOrSet($key, $closure, $duration, $dependency)` — атомарно: проверить → выполнить → записать.
- **НЕ защищает** от race condition между двумя процессами — внутри только один `set()`.
- Помогает от логических ошибок, но НЕ от stampede.

**TagDependency для группового сброса:**

- Все ключи с одним тегом инвалидируются **одним** `invalidate()`.
- Под капотом — bump-счётчик: `auth_users_v17` вместо удаления — сменили на `_v18` и старые ключи "забыты".
- Не вызывает stampede, потому что **invalidate ≠ удаление**.

**Race condition в fragment cache:**

- Между `beginCache()` и `endCache()` БД-запросы внутри блока **не защищены** от stampede.
- Решение: **обернуть `beginCache` mutex-ом** + `enabled => false` если lock не взят, отдавая старое.',
                'code_example' => '// 1. Mutex protection — один воркер пересчитывает
$cache = Yii::$app->cache;
$mutex = Yii::$app->mutex;
$key = \'top:articles\';

$result = $cache->get($key);
if ($result === false) {
    if ($mutex->acquire($key, 0)) {
        try {
            // Перепроверка под лок-ом — кто-то мог записать пока ждали
            $result = $cache->get($key);
            if ($result === false) {
                $result = Article::find()->orderBy([\'views\' => SORT_DESC])->limit(10)->all();
                $cache->set($key, $result, 3600);
            }
        } finally {
            $mutex->release($key);
        }
    } else {
        // Кто-то другой обновляет — отдаём stale или ждём
        $result = $cache->get($key . \':stale\') ?: [];
    }
}

// 2. Soft TTL: хранить значение + softExpire
function cacheWithSoftTtl(string $key, callable $producer, int $softTtl, int $hardTtl)
{
    $cache = Yii::$app->cache;
    $wrapped = $cache->get($key);
    $now = time();

    if ($wrapped === false) {
        $value = $producer();
        $cache->set($key, [\'value\' => $value, \'softExpire\' => $now + $softTtl], $hardTtl);
        return $value;
    }

    if ($wrapped[\'softExpire\'] < $now) {
        // Soft expired — кидаем джобу на refresh, отдаём старое
        Yii::$app->queue->push(new RefreshCacheJob([\'key\' => $key]));
    }
    return $wrapped[\'value\'];
}

// 3. Probabilistic early expiration (XFetch)
function xfetch(string $key, callable $producer, int $ttl, float $beta = 1.0)
{
    $cache = Yii::$app->cache;
    $wrapped = $cache->get($key);
    $now = microtime(true);

    if ($wrapped === false || $now + $beta * $wrapped[\'delta\'] * log(random_int(1, 1000) / 1000) >= $wrapped[\'expiry\']) {
        $start = microtime(true);
        $value = $producer();
        $delta = microtime(true) - $start;
        $cache->set($key, [\'value\' => $value, \'delta\' => $delta, \'expiry\' => $now + $ttl], $ttl);
        return $value;
    }
    return $wrapped[\'value\'];
}

// 4. TagDependency — сбросить всё с тегом без stampede-удаления
use yii\caching\TagDependency;
$tag = new TagDependency([\'tags\' => [\'users\', \'profile\']]);
$cache->set(\'user:1\', $u1, 3600, $tag);
$cache->set(\'user:1:profile\', $p1, 3600, $tag);
// Атомарная инвалидация по тегу — НЕ ходит в провайдер удалять каждый ключ
TagDependency::invalidate($cache, [\'users\']);

// 5. Fragment cache + mutex (защита от stampede на тяжёлом рендере)
if (Yii::$app->mutex->acquire(\'sidebar\', 0)) {
    if ($this->beginCache(\'sidebar\', [\'duration\' => 300])) {
        echo $this->render(\'_heavy_sidebar\');
        $this->endCache();
    }
    Yii::$app->mutex->release(\'sidebar\');
} else {
    echo $this->render(\'_lightweight_sidebar\');   // fallback
}',
                'code_language' => 'php',
                'difficulty' => 5,
                'topic' => 'yii2.cache',
            ],
        ];
    }
}
