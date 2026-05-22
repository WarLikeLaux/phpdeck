<?php

namespace Database\Seeders\Data\Categories\Laravel;

class ServiceProviders
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Laravel',
                'question' => 'Что такое Service Provider и в чём разница между методами register и boot?',
                'answer' => '**Service Provider** — класс, описывающий, **как фреймворку поднять и связать сервисы** при старте приложения. **Центральное место** инициализации в Laravel.

**Жизненный цикл — два этапа:**

| Этап | Метод | Что можно | Что нельзя |
|---|---|---|---|
| **1. Регистрация** | **`register()`** | биндинги в контейнер (`bind`, `singleton`, `scoped`, `extend`) | **обращаться к другим сервисам** — они могут быть ещё не зарегистрированы |
| **2. Загрузка** | **`boot()`** | роуты пакета, события, Blade-директивы, ViewComposer, валидация, политики | — |

**Почему такое разделение:**
- Контейнер строится **поэтапно**: сначала **все** провайдеры пишут биндинги, **потом** Laravel вызывает `boot()` у каждого. Это гарантирует, что в `boot()` все сервисы уже доступны.
- Обращение к другому сервису в `register()` → разрешается **частичный** граф, что может «закостылить» биндинги.

**Что обычно делают в `register()`:**
- `$this->app->singleton(Interface::class, Concrete::class)`.
- `$this->app->bind(...)` для фабрик с параметрами.
- `$this->app->extend(...)` для декораторов.
- `$this->mergeConfigFrom(...)` для конфигов пакета.

**Что делают в `boot()`:**
- `Route::middleware("api")->group(...)` — пакетные роуты.
- `Event::listen(...)`.
- `Blade::directive(...)`, `Blade::if(...)`.
- `View::composer(...)`.
- `Validator::extend(...)`.
- `Gate::policy(...)`, `Gate::define(...)`.

**Где регистрируются:**
- **Laravel 11+** — в **`bootstrap/providers.php`**.
- **Laravel ≤10** — в массиве `providers` в **`config/app.php`**.
- В L11 дефолтный скаффолд оставил только `AppServiceProvider`; задачи `Auth/Event/Broadcast/Route`-провайдеров переехали в `bootstrap/app.php` и auto-discovery.

**Создать:** `php artisan make:provider PaymentServiceProvider`.',
                'code_example' => 'class AppServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->singleton(PaymentInterface::class, StripePayment::class);
    }

    public function boot(): void {
        Blade::directive(\'money\', fn($expr) => "<?= number_format($expr, 2) ?>");
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.service_providers',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое deferred providers (отложенные провайдеры)?',
                'answer' => '**Deferred provider** — провайдер, **загружающийся только когда реально нужен** один из его сервисов.

**Зачем:**

- Тяжёлый сервис (платёжный SDK, поисковый клиент) **не должен** грузиться на **каждый** запрос.
- Сокращает **cold start** приложения.
- При `php artisan` команде, которая сервис не использует, провайдер вообще не выполнится.

**Как сделать провайдер deferred:**

| Шаг | Что |
|---|---|
| 1 | Реализовать **`Illuminate\\Contracts\\Support\\DeferrableProvider`** |
| 2 | Вернуть из метода **`provides(): array`** список класс-имён, которые провайдер регистрирует |
| 3 | Зарегистрировать как обычно — в `bootstrap/providers.php` |

**Как работает под капотом:**

- При **`artisan package:discover`** или **`config:cache`** Laravel вызывает `provides()` у каждого DeferrableProvider.
- Список сервис → провайдер сохраняется в **`bootstrap/cache/services.php`** (manifest).
- На запросе **manifest читается**, провайдер **не загружается**.
- При первом `app(HeavyService::class)` контейнер видит manifest → загружает провайдер → резолвит сервис.

**Когда НЕ делать deferred:** см. следующую карточку — есть критичные ограничения по `boot()`.',
                'code_example' => '<?php
use Illuminate\\Contracts\\Support\\DeferrableProvider;
use Illuminate\\Support\\ServiceProvider;

class HeavyServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        \$this->app->singleton(HeavyService::class, function (\$app) {
            return new HeavyService(
                config("services.heavy.endpoint"),
                config("services.heavy.key"),
            );
        });
    }

    // Список сервисов — manifest для lazy-loading
    public function provides(): array
    {
        return [HeavyService::class];
    }
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.service_providers',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как работают deferred service providers и какие у них ограничения?',
                'answer' => '**Deferred provider** не загружается при бутстрапе; в **кэшированном manifest** (`bootstrap/cache/services.php`) указано, какие сервисы он предоставляет. Когда контейнер **впервые резолвит** один из этих сервисов, провайдер регистрируется и загружается лениво.

**Что это даёт:**

- Сокращение **cold-start**: тяжёлые провайдеры не запускаются, если сервис не нужен.
- Запросы, не использующие сервис (например, артизан-команды) — **не платят** за инициализацию.

**Условия использования:**

- Реализовать **`Illuminate\\Contracts\\Support\\DeferrableProvider`**.
- Метод **`provides(): array`** возвращает список класс-имён биндов.

**КРИТИЧНОЕ ОГРАНИЧЕНИЕ из официальной документации:**

> «If your provider is **ONLY** registering bindings in the service container, you may choose to defer its registration.»

**Deferred-провайдер пригоден ИСКЛЮЧИТЕЛЬНО для регистрации биндингов в контейнере.**

**Что НЕ выполнится при бутстрапе у deferred-провайдера:**

| Действие в `boot()` | Что сломается |
|---|---|
| **`Route::get(...)`** | Маршрут не зарегистрирован |
| **`Event::listen(...)`** | Listener не подписан |
| **`Blade::directive(...)`** | Директива недоступна в шаблонах |
| **`View::composer(...)`** | Composer не вызывается |
| **`Validator::extend(...)`** | Кастомное правило не работает |
| **`Gate::define(...)`** | Gate не зарегистрирован |
| **`Schema::defaultStringLength(...)`** | Не применится к миграциям |

**Всё это «оживёт» только если кто-то явно резолвит сервис из `provides()`.**

**Правило:** если в провайдере есть **и тяжёлый bind, и регистрация роутов/евентов** — **НЕЛЬЗЯ** делать deferred. Иначе роуты молча перестанут работать.

**Решение:** разделить на два провайдера — `HeavyServiceProvider` (deferred, только binding) и `HeavyRoutesProvider` (обычный, регистрация роутов).',
                'code_example' => '<?php
// OK: только container bindings - можно делать deferred
class StripeServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void {
        $this->app->singleton(StripeClient::class,
            fn() => new StripeClient(config("services.stripe.key")));
    }
    public function provides(): array { return [StripeClient::class]; }
}

// ЛОВУШКА: с deferred этот boot() НЕ вызовется при бутстрапе
class WrongDeferredProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void {
        $this->app->singleton(SearchService::class, fn() => new SearchService());
    }
    public function boot(): void {
        Route::get("/search", SearchController::class); // НЕ зарегистрируется!
        Event::listen(UserCreated::class, IndexUserListener::class); // НЕ подпишется!
    }
    public function provides(): array { return [SearchService::class]; }
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.service_providers',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Service Provider простыми словами?',
                'answer' => '**Service Provider** — класс, описывающий, как фреймворку поднять и связать сервисы при старте приложения.

Каждый запрос Laravel пробегает по списку провайдеров и у каждого вызывает два метода:

1. **`register()`** — регистрирует биндинги в **Service Container** (`bind`, `singleton`). Здесь **нельзя** обращаться к другим сервисам — они могут быть ещё не зарегистрированы.
2. **`boot()`** — вызывается **после** регистрации всех провайдеров. Здесь можно использовать другие сервисы: слушать события, добавлять Blade-директивы, ViewComposer, маршруты пакетов.

**Где живут:**
- Файлы — в `app/Providers`.
- Регистрация — в `bootstrap/providers.php` (Laravel 11+) или в `config/app.php` (L10 и старше).

В Laravel 11 дефолтный скаффолд оставил только `AppServiceProvider` — задачи `Auth/Route/Event/Broadcast`-провайдеров перенесены в `bootstrap/app.php` и auto-discovery.

Создать свой: `php artisan make:provider PaymentServiceProvider`.',
                'code_example' => 'namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    // Только биндинги в контейнер - НИКАКИХ обращений к другим сервисам
    public function register(): void
    {
        $this->app->singleton(PaymentGateway::class, StripeGateway::class);
    }

    // Запускается после регистрации ВСЕХ провайдеров - тут можно всё
    public function boot(): void
    {
        Blade::directive(\'money\', fn ($amount) =>
            "<?= number_format($amount, 2, \',\', \' \') ?> ₽");
    }
}',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'laravel.service_providers',
            ],
        ];
    }
}
