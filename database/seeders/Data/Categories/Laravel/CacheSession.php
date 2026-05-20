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
                'answer' => 'Cache tags - группировка кеш-записей по тегам, чтобы инвалидировать сразу группу. Простыми словами: пометили несколько ключей тегом "users" и потом одним вызовом сбросили все. Теги поддерживают сторы, наследующие TaggableStore: redis, memcached, apc, array (специально, чтобы тестовый CACHE_STORE=array не валил код, использующий теги), null. НЕ поддерживают: database, file, dynamodb - вызов tags() на них кидает BadMethodCallException. Если в проекте дефолтный store - database (как в Laravel 11 skeleton), а нужны теги - либо явно использовать Cache::store("redis")->tags(...), либо вместо тегов делать версионирование ключей ("users:v$version:{id}" + bump $version при инвалидации).',
                'code_example' => 'Cache::tags([\'users\', \'admins\'])->put(\'user.1\', $user, 600);
Cache::tags(\'users\')->flush(); // удалит всё с тегом users',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.cache_session',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое atomic locks в Cache?',
                'answer' => 'Atomic lock - это распределённая блокировка через кеш, чтобы только один процесс мог выполнять секцию кода в один момент. Простыми словами: защита от одновременного выполнения (например, чтобы cron-задача не запустилась дважды).',
                'code_example' => '$lock = Cache::lock(\'process-orders\', 10);

if ($lock->get()) {
    try {
        // эксклюзивная работа
    } finally {
        $lock->release();
    }
}

// или короче
Cache::lock(\'foo\', 10)->block(5, function () {
    // ...
});',
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
