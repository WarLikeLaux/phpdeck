<?php

namespace Database\Seeders\Data\Categories\Laravel;

class Middleware
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Laravel',
                'question' => 'Что такое Middleware и для чего оно нужно?',
                'answer' => '**Middleware** — слой между запросом и контроллером (и ответ → клиенту обратно). «Фильтр» для HTTP-запросов.

Может:

- **Пропустить** запрос дальше — `return $next($request)`.
- **Модифицировать** запрос/ответ — добавить заголовки, локаль, заменить параметры.
- **Отклонить** — вернуть свой ответ (редирект/`403`/`401`), контроллер тогда **не вызовется**.

Типичные задачи:

- **Аутентификация** (`auth`) — пускать только залогиненных.
- **Авторизация** ролей и прав.
- **CORS** — заголовки для кросс-доменных запросов.
- **Throttle** — ограничение количества запросов.
- **Логирование**, добавление trace-id, локализация.
- **CSRF-проверка** для POST/PUT/DELETE.

Создаётся через `php artisan make:middleware EnsureUserIsActive`. Метод `handle($request, Closure $next)` принимает запрос и решает, что делать.',
                'code_example' => 'class CheckAge {
    public function handle(Request $request, Closure $next) {
        if ($request->age < 18) {
            return redirect(\'/home\');
        }
        return $next($request);
    }
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.middleware',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Какие типы middleware бывают в Laravel?',
                'answer' => 'Четыре способа применения:

- **Глобальные** — выполняются для **всех** запросов (например, `TrustProxies`, `HandleCors`).
- **Group middleware** — для группы роутов: `web` (сессии, CSRF, cookies) и `api` (без сессий).
- **Route middleware** — навешиваются вручную на конкретные роуты через **alias** (`auth`, `verified`, `signed`, `throttle`).
- **Terminate-middleware** — у класса есть метод `terminate($request, $response)`, который вызывается **после** отправки ответа клиенту. Удобно для логирования.

Регистрация:

- **Laravel 11+** — всё в **`bootstrap/app.php`**, метод **`withMiddleware()`**. `Http\\Kernel.php` удалён.
- **Laravel 10 и ниже** — `app/Http/Kernel.php`, свойства `$middleware`, `$middlewareGroups`, `$routeMiddleware`.',
                'code_example' => '// Laravel 11+ bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(EnsureUserIsActive::class);          // глобально
    $middleware->web(append: [TrackVisits::class]);          // в группу web
    $middleware->alias([\'subscribed\' => Subscribed::class]); // alias для роутов
})

Route::middleware([\'auth\', \'verified\', \'subscribed\'])->group(function () {
    Route::get(\'/dashboard\', [DashboardController::class, \'index\']);
});',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.middleware',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое terminate middleware?',
                'answer' => '**Terminate middleware** — middleware с методом **`terminate($request, $response)`**, который Laravel вызывает **ПОСЛЕ формирования и отправки ответа** клиенту.

**Подходит для лёгкой постобработки:**

- Запись **access-логов**.
- **Метрики** (HTTP duration, status codes).
- Лёгкая **аналитика**.
- **Очистка** request-scoped ресурсов.

**Когда клиент реально не ждёт `terminate()` — зависит от SAPI:**

| SAPI | Клиент ждёт `terminate`? | Механизм |
|---|---|---|
| **PHP-FPM / FastCGI** | **Нет** | `fastcgi_finish_request()` — ответ улетел, процесс продолжает работать |
| **Octane (RoadRunner / Swoole / FrankenPHP)** | **Нет** | Долгоживущий воркер: ответ в сокет, потом `terminate` |
| **Apache mod_php (prefork)** | **Да** | Нет механизма «закрыть response» |
| **PHP встроенный dev-сервер** | **Да** | То же |
| **CLI-SAPI** (artisan) | N/A | Нет HTTP-ответа |

**КРИТИЧНО — `terminate` НЕ замена очередям:**

- Тяжёлые операции (внешние HTTP к Stripe, отправка почты, генерация PDF, долгая аналитика) **держат воркер занятым** — снижают throughput пула, блокируют процесс под следующим запросом.
- Для тяжёлой работы нужен **queue/job**, а не `terminate`.

**Гарантии доставки:**

- `terminate` **не запустится**, если процесс убили `kill -9` / OOM / сегфолтом до его вызова.
- Это **не гарантия доставки** — критичный аудит-лог пишите **до** ответа.

**Подключение:** middleware с методом `terminate` достаточно зарегистрировать обычным способом — Laravel сам найдёт метод через рефлексию и вызовет.',
                'code_example' => 'class LogRequestMiddleware {
    public function handle($request, Closure $next) {
        return $next($request);
    }

    public function terminate($request, $response): void {
        Log::info(\'Request finished\', [\'url\' => $request->url()]);
    }
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.middleware',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как реализовать rate limiting в Laravel?',
                'answer' => '**Rate limiting** реализуется через middleware **`throttle`** + именованные лимитеры в `RateLimiter::for()`.

**Два формата записи middleware:**

| Формат | Что значит |
|---|---|
| **`throttle:60,1`** | 60 запросов в 1 минуту на ключ (по умолчанию `auth_id` / IP) |
| **`throttle:api`** | Именованный limiter из `RateLimiter::for("api", ...)` |

**Где регистрировать лимитеры:**

| Версия | Файл |
|---|---|
| **Laravel 11+** | **`AppServiceProvider::boot()`** (RouteServiceProvider удалён из скелета) |
| **Laravel 10 и старше** | **`RouteServiceProvider::configureRateLimiting()`** |

**При апгрейде на L11 — лимитеры нужно перенести вручную.**

**Доступные методы `Limit`:**

| Метод | Доступно с |
|---|---|
| **`Limit::perSecond(2)`** | **Laravel 11+** (для burst-защиты login/OTP) |
| **`Limit::perMinute(60)`** | Всегда |
| **`Limit::perHour(1000)`** | Всегда |
| **`Limit::perDay(10000)`** | Всегда |
| **`Limit::none()`** | Без лимита (whitelist) |

**Ключи (через `->by(...)`):**

- **`auth_id`** — лимит на пользователя.
- **`ip`** — лимит на IP (для гостей).
- **`header`** — лимит на API-key через `$request->header("X-API-Key")`.

**Заголовки ответа:** `X-RateLimit-Limit`, `X-RateLimit-Remaining`, `Retry-After` при `429 Too Many Requests`.

**Подводный камень за балансером:** `$request->ip()` вернёт **IP балансера**, не клиента. Нужен **`TrustProxies`** middleware (в L11 — настраивается в `bootstrap/app.php`), иначе один IP уронит всех.',
                'code_example' => 'Route::middleware(\'throttle:60,1\')->group(...);

// Кастомный
RateLimiter::for(\'api\', function (Request $request) {
    return $request->user()
        ? Limit::perMinute(120)->by($request->user()->id)
        : Limit::perMinute(30)->by($request->ip());
});

Route::middleware(\'throttle:api\')->group(...);',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.middleware',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как работает rate limiting в Laravel и почему предпочтителен Redis-драйвер?',
                'answer' => '**`RateLimiter`** работает **поверх cache-store**. Под капотом и Redis, и database драйвер используют атомарные операции:

- **Redis** — `INCR` + `EXPIRE` (O(1)).
- **Database** — инкремент через `UPDATE` + cache-locks.

**At-the-protocol-level гонок не должно быть в обоих.** Тем не менее **Redis на проде предпочтительнее** по двум причинам:

**1) Производительность:**

- Всё в **RAM**, `INCR/EXPIRE` — O(1) операции.
- Нагрузка на БД **не растёт** от каждого HTTP-запроса.
- Database-драйвер на больших RPS забивает **binlog/WAL** бессмысленными апдейтами счётчиков.

**2) TTL автоматически обслуживается Redis-сервером:**

- Просроченные ключи **удаляются** Redis-ом сам собой.
- Database-драйвер: протухшие записи **висят в `cache`**, пока их не удалит `cache:prune-stale-tags` / cron.

**Сравнение драйверов для rate limiting:**

| Параметр | **Redis** | **Database** | **File** |
|---|---|---|---|
| Скорость | O(1) RAM | UPDATE + lock | I/O диск |
| Auto-cleanup TTL | **Да** | Нет, нужен prune | Нет |
| Распределённость | Да | Да | **Нет** (только локально) |
| Production | **Дефолт** | Только small projects | Нет |

**Как переключить:**

- **`.env`** — `CACHE_STORE=redis` (Laravel 11) или `CACHE_DRIVER=redis` (старше).
- **`config/cache.php`** — `default` → `redis`.

**Сценарии:**

- **`Limit::perSecond(2)`** (Laravel 11+) — тонкое ограничение **burst-трафика** на login/OTP.
- **`->by($request->user()->id)`** — лимит на пользователя.
- **`->by($request->ip())`** — лимит для гостей.
- **`->response(fn () => response()->json([...], 429))`** — кастомный ответ при превышении.',
                'code_example' => '<?php
// AppServiceProvider::boot() (Laravel 11+)
RateLimiter::for("api", fn (Request $r) =>
    $r->user() ? Limit::perMinute(60)->by($r->user()->id)
               : Limit::perMinute(10)->by($r->ip())
);

// тонкое ограничение burst (Laravel 11+)
RateLimiter::for("uploads", fn (Request $r) =>
    Limit::perSecond(2)->by($r->user()->id)
);

// routes/api.php
Route::middleware("throttle:api")->group(...);

// .env - какой драйвер используется
CACHE_STORE=redis    // production
CACHE_STORE=database // dev/small projects',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.middleware',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое $middlewarePriority и почему StartSession должен выполниться до VerifyCsrfToken / Authenticate?',
                'answer' => 'Когда middleware регистрируются как **глобальные** или как **часть группы** (`web`/`api`), Laravel выстраивает их в стек в **порядке регистрации**. Но для **некоторых** middleware важен **жёсткий взаимный порядок**, не зависящий от того, как их добавили в Kernel.

**Где задаётся приоритет:**

| Версия | Где |
|---|---|
| **Laravel 10 и ниже** | **`$middlewarePriority`** в `app/Http/Kernel.php` |
| **Laravel 11+** | **`$middleware->priority([...])`** в `bootstrap/app.php` |

Если несколько priority-middleware активны на маршруте, Laravel **пересортирует их именно по этому списку**, а не по порядку добавления.

**Классические зависимости порядка:**

| Что должно идти раньше | Что после | Почему |
|---|---|---|
| **`StartSession`** | `ShareErrorsFromSession`, `AuthenticateSession`, `VerifyCsrfToken` | Чтобы в последующих был доступ к `$request->session()`. CSRF-токен **проверяется по значению из сессии** — без StartSession токена просто нет |
| **`EncryptCookies`** | `AddQueuedCookiesToResponse` | Зашифровать куки **перед** ответом |
| **`SubstituteBindings`** | `Authenticate`, `Authorize` | Route-binding (`Post $post`) должен быть разрезолвлен **до** policy-check |
| **`StartSession`** | `Authenticate` | Сессионный guard читает user из сессии |

**Что ломается при неправильном порядке:**

- **`VerifyCsrfToken` до `StartSession`** → токен всегда `null` / не совпадает → **все POST дают `419 PAGE EXPIRED`**.
- **`Authenticate` до `SubstituteBindings`** → **`$request->user()` ещё не доступен** в момент инжекта policy.
- **`AddQueuedCookies` после рендера ответа** → «cookie не приходит».

**Как проверить применённый порядок:**

- **`php artisan route:list -v`** — покажет middleware-стек каждого роута.
- В отладке — dump через простой middleware-дебаггер, выводящий список класс-имён.',
                'code_example' => '<?php
// Laravel 10 и ниже - app/Http/Kernel.php
class Kernel extends HttpKernel {
    protected $middlewarePriority = [
        \Illuminate\Cookie\Middleware\EncryptCookies::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        \Illuminate\Routing\Middleware\ThrottleRequests::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \Illuminate\Auth\Middleware\Authenticate::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\Auth\Middleware\Authorize::class,
    ];
}

// Laravel 11+ - bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->priority([
        \Illuminate\Cookie\Middleware\EncryptCookies::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \Illuminate\Auth\Middleware\Authenticate::class,
        \Illuminate\Auth\Middleware\Authorize::class,
    ]);
})

// Симптомы поломанного порядка
// 419 PAGE EXPIRED на каждом POST - VerifyCsrfToken до StartSession
// $request->user() === null в Authenticate - guard до StartSession
// "Cookie не приходит" - AddQueuedCookies после рендера ответа',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.middleware',
            ],
        ];
    }
}
