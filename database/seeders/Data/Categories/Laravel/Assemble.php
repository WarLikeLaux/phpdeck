<?php

namespace Database\Seeders\Data\Categories\Laravel;

class Assemble
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, assemble_chunks?: ?array<int, string>, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Laravel',
                'question' => 'Собери Eloquent-запрос: всех активных юзеров, упорядоченных по имени.',
                'answer' => 'Типичная цепочка Eloquent:

- **`User::`** — стартует Query Builder от модели.
- **`where(\'active\', 1)`** — фильтр.
- **`orderBy(\'name\')`** — сортировка по возрастанию (для убывания — `orderByDesc(\'name\')`).
- **`get()`** — выполнить запрос и вернуть **`Collection`** моделей.

Важно помнить: пока не вызвал терминальный метод (`get`/`first`/`paginate`/`count`), запрос **не выполняется**. Можно собирать цепочку условно через `when()` и выполнить в конце.',
                'assemble_chunks' => ['User::', "where('active', 1)", '->', "orderBy('name')", '->', 'get()'],
                'code_example' => 'User::where(\'active\', 1)->orderBy(\'name\')->get();',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.assemble',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Собери eager load с ограничением relations.',
                'answer' => '**`with()`** — eager loading: подтягивает связанные модели **двумя запросами** вместо N+1.

**Формы вызова:**

- **Строка** — `with(\'comments\')`.
- **Массив** — `with([\'comments\', \'author\'])`.
- **С условием** — `with([\'comments\' => fn ($q) => $q->latest()])`.
- **Только нужные колонки** — `with(\'comments:id,post_id,body\')` (обязательно включить FK!).
- **Вложенные** — `with(\'comments.author.profile\')`.

**Что произойдёт без `with`:**

- `Post::paginate(20)` → 1 запрос на посты.
- В Blade `{{ $post->comments->count() }}` → ещё 20 запросов (N+1).

**С `with(\'comments\')`:**

- `SELECT * FROM posts ... LIMIT 20`.
- `SELECT * FROM comments WHERE post_id IN (1,...,20)`.

**Подводный камень `limit` внутри `with`:**

- В **Laravel 11+** — это **per-parent limit** (по 5 комментов на каждый пост).
- В **Laravel ≤10** — лимит на **общую** выборку (5 комментов на ВСЕ посты вместе) — классическая ловушка.',
                'assemble_chunks' => [
                    'Post::',
                    "with(['comments' => fn(\$q) => \$q->latest()])",
                    '->',
                    'paginate(20)',
                ],
                'difficulty' => 3,
                'topic' => 'laravel.assemble',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Собери транзакцию с retry на 3 попытки.',
                'answer' => 'DB::transaction(closure, attempts: N) сам повторяет транзакцию при ошибках конкуренции (deadlock, serialization failure, lock wait timeout - всё, что ConcurrencyErrorDetector считает таковым). На constraint violation, syntax error и обычный QueryException ретрая нет - исключение пробрасывается сразу.',
                'assemble_chunks' => [
                    'DB::',
                    'transaction(function () {',
                    '    Account::lockForUpdate()->find($id);',
                    '    // ...',
                    '}, 3)',
                ],
                'difficulty' => 4,
                'topic' => 'laravel.assemble',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Собери диспатч джобы в очередь high с задержкой 30 секунд.',
                'answer' => 'Цепочка модификаторов перед отправкой job в очередь.

**Основные методы:**

- **`onQueue(\'high\')`** — выбрать очередь. Воркеры обычно слушают несколько с приоритетом: `php artisan queue:work --queue=high,default,low`.
- **`onConnection(\'redis\')`** — конкретный driver, если их несколько в `config/queue.php`.
- **`delay(now()->addSeconds(30))`** — отложенный запуск.
- **`afterCommit()`** — диспатчить **только после успешного commit** текущей транзакции (страхует от «job в очереди есть, а строки в БД ещё нет»).

**Альтернатива через статику:**

- `ProcessOrder::dispatch($order)->onQueue(\'high\')->delay(30)`.
- `ProcessOrder::dispatchAfterResponse($order)` — выполнить **после** HTTP-ответа (sync, но не блокирует клиента).

**Подводные камни:**

- **Очередь должна быть указана у воркера** — иначе job будет лежать вечно.
- **`delay` для `sync`-драйвера игнорируется** — job выполнится мгновенно.
- На Redis-драйвере `delayed` job-ы лежат в `ZSET`, и `queue:work` забирает их в момент дедлайна.',
                'assemble_chunks' => [
                    'ProcessOrder::',
                    'dispatch($order)',
                    '->',
                    "onQueue('high')",
                    '->',
                    'delay(now()->addSeconds(30))',
                ],
                'difficulty' => 3,
                'topic' => 'laravel.assemble',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Собери rate-limited маршрут в группе.',
                'answer' => '**`throttle`** — middleware Laravel для **rate limiting**. Поддерживает два формата:

- **`throttle:60,1`** — короткая запись: **60 запросов в 1 минуту** на ключ.
- **`throttle:api`** — **именованный limiter**, определённый в `RouteServiceProvider::configureRateLimiting()` (или в `AppServiceProvider::boot` в L11).

**Ключ по умолчанию:**

- Аутентифицирован — `auth_id` пользователя.
- Гость — IP-адрес.

**Заголовки ответа:**

- `X-RateLimit-Limit`, `X-RateLimit-Remaining` — текущее состояние.
- При исчерпании — `429 Too Many Requests` + `Retry-After`.

**Именованные limiter-ы — гибче:**

```php
RateLimiter::for(\'api\', fn (Request $r) =>
    $r->user()
        ? Limit::perMinute(120)->by($r->user()->id)
        : Limit::perMinute(30)->by($r->ip())
);
```

**В Laravel 11+ есть `perSecond()`** — для критичных эндпоинтов (login, OTP).

**Подводные камни:**

- За балансером **IP — это IP балансера**: нужен `TrustProxies` middleware, иначе один IP уронит всех.
- Хранилище limiter-а — кеш (`config/cache.php`); на серверном кластере должен быть **общий** (Redis), иначе лимит будет per-server.',
                'assemble_chunks' => [
                    'Route::',
                    "middleware(['auth', 'throttle:60,1'])",
                    '->',
                    'group(function () {',
                    '    Route::get(\'/profile\', ProfileController::class);',
                    '})',
                ],
                'difficulty' => 3,
                'topic' => 'laravel.assemble',
            ],
        ];
    }
}
