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
                'answer' => 'Cache - это система кеширования данных для ускорения. Драйверы: database, file, redis, memcached, array (для тестов), dynamodb, null. Дефолт зависит от версии и .env: в Laravel 11 skeleton по умолчанию database (CACHE_STORE=database), в более старых версиях был file. Конфигурируется в config/cache.php. Используется через Cache фасад.',
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
                'answer' => 'Cache::remember - получить значение из кеша или, если его нет, выполнить closure, записать результат в кеш и вернуть. Простыми словами: "если в кеше есть - возьми, если нет - вычисли и положи". Также есть rememberForever (без TTL).',
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
                'answer' => 'Сессия - это хранилище данных пользователя между запросами. Драйверы: database, file, cookie, redis, memcached, array, dynamodb. Дефолт зависит от версии и .env: в Laravel 11 skeleton по умолчанию database (SESSION_DRIVER=database), в более старых версиях был file. Доступ через session() helper, $request->session() или Session фасад. Защищена от session fixation, регенерация ID при логине.',
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
