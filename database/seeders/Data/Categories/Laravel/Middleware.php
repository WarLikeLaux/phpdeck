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
                'answer' => 'Middleware - это слой между запросом и контроллером, который может перехватывать, модифицировать или отклонять запросы. Простыми словами: это "фильтр" для HTTP-запросов. Используется для аутентификации, логирования, CORS, throttle, проверки прав и т.д.',
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
                'answer' => 'Глобальные middleware - выполняются для всех запросов. Group middleware - применяются к группе роутов (web, api). Route middleware - назначаются вручную на конкретные роуты через alias. Terminate-middleware - выполняется после отправки ответа клиенту (метод terminate). В Laravel 10 и ниже регистрация шла через app/Http/Kernel.php, в Laravel 11+ всё это переехало в bootstrap/app.php (метод withMiddleware).',
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
                'answer' => 'Terminate middleware - middleware с методом terminate(), который Laravel вызывает ПОСЛЕ формирования и отправки ответа. Подходит для ЛЁГКОЙ постобработки: запись access-логов, метрики, лёгкая аналитика, очистка request-scoped ресурсов. Сам метод вызывается всегда, но реальный выигрыш по latency для клиента есть только на тех SAPI, что умеют закрыть HTTP-ответ до завершения процесса: FastCGI/PHP-FPM через fastcgi_finish_request(), а на Octane-стеке RoadRunner/Swoole/FrankenPHP - за счёт самой архитектуры долгоживущего воркера, где ответ улетает в сокет и только потом исполняется terminate(). На встроенном dev-сервере, в CLI-SAPI и в Apache mod_php (prefork) клиент будет ждать завершения terminate() - там нет fastcgi_finish_request(). ВАЖНО: terminate - НЕ замена очередям. Тяжёлые операции (внешние HTTP-вызовы платёжному шлюзу, отправка почты, генерация PDF, долгая аналитика) в любом случае держат воркер занятым и снижают throughput пула - блокируют процесс под следующим запросом независимо от SAPI; для них нужен queue/job. Также terminate не запустится, если процесс убили kill -9 / OOM / сегфолтом до его вызова - не гарантия доставки.',
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
                'answer' => 'Через middleware throttle. Можно по умолчанию (60 запросов в минуту), кастомный лимит, по группам. Сложные правила задаются через RateLimiter::for() в провайдере. ВАЖНО про Laravel 11: в нём RouteServiceProvider удалён из дефолтного скелета - регистрация лимитеров перенесена в AppServiceProvider::boot() (или в bootstrap/app.php через withRouting). В Laravel 10 и старше лимитеры жили в RouteServiceProvider::configureRateLimiting(). Если вы апгрейдитесь, эти регистрации нужно переехать самостоятельно. Также в Laravel 11+ доступен perSecond() (раньше был только perMinute/perHour/perDay).',
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
                'answer' => 'RateLimiter работает поверх cache-store. Под капотом и Redis, и database драйвер используют атомарные операции (Redis - INCR/EXPIRE, database - инкремент через UPDATE и cache-locks), так что at-the-protocol-level гонок не должно быть в обоих. Тем не менее Redis на проде предпочтительнее по двум причинам: (1) производительность - всё в RAM, INCR/EXPIRE - O(1), нагрузка на БД не растёт от каждого запроса; (2) TTL автоматически обслуживается Redis-сервером (просроченные ключи удаляются), тогда как для database-драйвера протухшие записи висят в таблице cache, пока их не удалит cache:prune-stale-tags / cron. На больших RPS database-драйвер ещё и забивает binlog/WAL бессмысленными апдейтами счётчиков. RateLimiter::for() в AppServiceProvider определяет лимит, throttle:apiName применяет; ключ задаётся через by() (user_id, ip, header). Также с Laravel 11+ доступен perSecond() для тонкого ограничения burst-трафика.',
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
                'answer' => 'Когда middleware регистрируются как глобальные ИЛИ как часть группы (web/api), Laravel выстраивает их в стек в порядке регистрации - но для НЕКОТОРЫХ middleware важен жёсткий взаимный порядок, который не должен зависеть от того, как их добавили в Kernel. Для этого есть свойство $middlewarePriority в HttpKernel (Laravel 10 и ниже) или $middleware->priority(...) в bootstrap/app.php (Laravel 11+) - массив классов, задающий "правильный" взаимный порядок этих конкретных middleware. Если несколько из них активны на маршруте, Laravel пересортирует их именно по этому списку, а не по порядку добавления. Классические зависимости. 1) StartSession ДО ShareErrorsFromSession, AuthenticateSession, VerifyCsrfToken: чтобы во всех последующих был доступ к $request->session(); CSRF-токен проверяется по значению из сессии, без StartSession токена просто нет. 2) AddQueuedCookiesToResponse и EncryptCookies должны идти между терминальной обработкой и ответом, чтобы зашифровать поставленные ниже куки. 3) Authenticate ПОСЛЕ StartSession и SubstituteBindings - чтобы guard сессионный смог достать user, а route-binding ($user в типизированном параметре) уже разрезолвили модель. Что бывает при поломке порядка: VerifyCsrfToken до StartSession - токен всегда null/несовпадает, все POST дают 419; Authenticate до SubstituteBindings - $request->user() ещё не доступен в момент инжекта policy. Проверить применённый порядок: php artisan route:list -v или dump через middleware-дебаггер.',
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
