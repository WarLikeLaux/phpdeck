<?php

namespace Database\Seeders\Data\Categories\Laravel;

class CacheSession
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Laravel',
                'question' => 'Что такое Cache в Laravel и какие драйверы существуют?',
                'answer' => '**Cache** — система кеширования данных для ускорения (вместо повторных тяжёлых запросов в БД/API кладём результат в быстрое хранилище).

Драйверы (`config/cache.php`):

- **`redis`**, **`memcached`** — прод, быстрые in-memory.
- **`database`** — таблица `cache`. Дефолт в Laravel 11 (`CACHE_STORE=database`).
- **`file`** — на диск. Был дефолтом до L11.
- **`array`** — только в памяти процесса, для тестов.
- **`dynamodb`**, **`null`** — AWS / отключить.

Доступ через фасад `Cache` или helper `cache()`. Ключевые методы: `put`, `get`, `has`, `remember`, `forget`, `flush`.',
                'code_example' => 'Cache::put(\'key\', \'value\', 3600);
$value = Cache::get(\'key\', \'default\');
Cache::has(\'key\');
Cache::forget(\'key\');
Cache::flush();',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.cache_session',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что делает Cache::remember?',
                'answer' => '**`Cache::remember($key, $ttl, $callback)`** — самый частый паттерн кеширования.

Логика:

1. Смотрит, есть ли `$key` в кеше.
2. **Есть** → возвращает закешированное.
3. **Нет** → выполняет `$callback`, записывает результат с TTL в секундах, возвращает.

Удобно для **«вычислить один раз и не дёргать БД на каждый запрос»**.

Вариации:

- **`Cache::rememberForever($key, $callback)`** — без TTL, живёт пока не очистят.
- **`Cache::flexible($key, [$fresh, $stale], $callback)`** — SWR-стратегия (stale-while-revalidate).',
                'code_example' => '$users = Cache::remember(\'users.all\', 600, function () {
    return User::all();
});

// без TTL
$value = Cache::rememberForever(\'config\', fn() => loadHeavyConfig());',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.cache_session',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое cache tags?',
                'answer' => '**Cache tags** — **группировка** кеш-записей по тегам для **инвалидации сразу группы**. Помечаешь несколько ключей тегом `"users"` и одним вызовом сбрасываешь все.

**Поддержка по сторам:**

| Стор | Теги |
|---|---|
| **`redis`**, **`memcached`**, **`apc`** | **да** (наследуют `TaggableStore`) |
| **`array`** | **да** (для тестов с `CACHE_STORE=array`) |
| **`null`** | **да** (no-op) |
| **`database`**, **`file`**, **`dynamodb`** | **нет** — `BadMethodCallException` |

**Подвох в Laravel 11:** дефолтный store — **`database`**, теги на нём **не работают**. Решения:
- явно использовать **`Cache::store("redis")->tags(["users"])->put(...)`**.
- или вместо тегов делать **версионирование ключей**: `"users:v{$version}:{$id}"` + bump `$version` при инвалидации.

**Когда брать теги:**
- Несколько ключей нужно сбрасывать **вместе** при одном событии (изменили роль → дропнули все `permissions:user:*`).
- Хочется **не хранить список** ключей самостоятельно.

**Когда не стоит:**
- Только один store-сценарий — версионирование часто проще и переносимо между сторами.
- Очень большой объём — внутри Redis теги хранят SET с ключами, при `flush` сначала идёт `SMEMBERS`, потом массовый `DEL`; на миллионах ключей это блокирующая операция.',
                'code_example' => 'Cache::tags([\'users\', \'admins\'])->put(\'user.1\', $user, 600);
Cache::tags(\'users\')->flush(); // удалит всё с тегом users',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.cache_session',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое atomic locks в Cache?',
                'answer' => '**Atomic lock** — **распределённая блокировка** через cache-стор, гарантирующая, что **только один процесс одновременно** может выполнять секцию кода.

**Зачем нужны:**

- Запретить **двойной запуск cron-задачи** (один cron на нескольких серверах).
- Сериализовать **доступ к внешнему API** с лимитом на параллельные запросы.
- Защитить **переход состояния** сущности (заказ → paid).
- Реализовать **leader election** между N воркерами.

**API:**

| Метод | Семантика |
|---|---|
| **`Cache::lock($key, $seconds)`** | Создать lock-объект (НЕ блокирует) |
| **`->get()`** | Попытаться взять lock; вернёт `true`/`false` сразу |
| **`->get($callback)`** | Взять + выполнить callback + auto-release |
| **`->block($wait, $callback)`** | **Подождать до `$wait` секунд** lock-а, потом выполнить (если не дождались — `LockTimeoutException`) |
| **`->release()`** | Отпустить вручную |
| **`->forceRelease()`** | Отпустить **любой** держатель (опасно!) |

**Поддержка по cache-сторам:**

| Стор | Atomic lock |
|---|---|
| **`redis`** | **Да** (через `SET NX EX`) |
| **`memcached`** | Да (`add` с TTL) |
| **`database`** | Да (через `INSERT` с unique) |
| **`dynamodb`** | Да |
| **`file`** | Да (flock) |
| **`array`** | Да (in-process only) |

**Критичные паттерны:**

- **`$seconds` обязателен** — без TTL зависший процесс заблокирует ключ навсегда.
- **`->owner()`** — string-токен владельца, чтобы только держатель мог `release()`.
- **`try/finally`** — обязательно отпускать lock в `finally`, иначе exception оставит lock висеть.

**Подводные камни:**

- **Lock != транзакция** — между взятием lock и работой состояние БД могло измениться.
- **TTL должно быть БОЛЬШЕ** времени работы — иначе lock истечёт во время выполнения и второй процесс зайдёт параллельно.
- **`ShouldBeUnique` под капотом** использует именно `Cache::lock`.',
                'code_example' => '<?php
// 1) Простой lock — выйти, если занят
\$lock = Cache::lock("process-orders", 10);

if (\$lock->get()) {
    try {
        // эксклюзивная работа
        ProcessAllOrders::run();
    } finally {
        \$lock->release();  // обязательно в finally!
    }
} else {
    Log::info("Another worker is processing orders");
}

// 2) Короче — auto-release через callback
Cache::lock("process-orders", 10)->get(function () {
    ProcessAllOrders::run();
});

// 3) Подождать до 5 секунд, если занят
try {
    Cache::lock("critical-section", 30)->block(5, function () {
        // выполнить эксклюзивно
    });
} catch (LockTimeoutException \$e) {
    Log::warning("Could not acquire lock in 5s");
}

// 4) Lock с owner-токеном — передать в другой процесс/job
\$lock = Cache::lock("export-report", 600);
if (\$lock->get()) {
    \$owner = \$lock->owner();  // токен владельца
    GenerateReportJob::dispatch(\$reportId, \$owner);
}

// В job — release по токену (только если мы держатели)
class GenerateReportJob implements ShouldQueue
{
    public function handle()
    {
        // долгая генерация...
        Cache::restoreLock("export-report", \$this->lockOwner)->release();
    }
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.cache_session',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как работает session в Laravel?',
                'answer' => '**Сессия** — серверное хранилище данных пользователя между HTTP-запросами. Идентифицируется cookie `laravel_session` с зашифрованным session id.

Драйверы (`config/session.php`):

- **`database`** — таблица `sessions`. Дефолт в Laravel 11 (`SESSION_DRIVER=database`).
- **`file`** — файлы в `storage/framework/sessions`. Был дефолтом до L11.
- **`redis`**, **`memcached`** — для нескольких серверов за балансировщиком.
- **`cookie`** — на стороне клиента (зашифровано).
- **`array`** — для тестов.

Доступ через `session()` helper, `$request->session()` или фасад `Session`.

Защита:

- Cookie всегда `HttpOnly`.
- Регенерация ID при логине (`session()->regenerate()`) защищает от **session fixation**.',
                'code_example' => 'session([\'key\' => \'value\']);
$value = session(\'key\', \'default\');
session()->forget(\'key\');
session()->flush();
session()->regenerate();
$request->session()->flash(\'status\', \'Saved\'); // только для следующего запроса',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.cache_session',
            ],
        ];
    }
}
