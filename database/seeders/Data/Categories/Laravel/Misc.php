<?php

namespace Database\Seeders\Data\Categories\Laravel;

class Misc
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel?',
                'answer' => '**Laravel** — самый популярный PHP-фреймворк для веб-приложений и API, построенный на архитектуре **MVC**.

Что входит «из коробки»:

- ORM **Eloquent** (Active Record) для работы с БД.
- Маршрутизация (`routes/web.php`, `routes/api.php`).
- Шаблонизатор **Blade**.
- Миграции, сидеры, фабрики.
- Аутентификация, очереди, кеш, события, уведомления.
- CLI **Artisan** для генерации кода и обслуживания.

Создатель — Тейлор Отвелл. Стартовать: `composer create-project laravel/laravel my-app`.',
                'code_example' => 'composer create-project laravel/laravel example-app
cd example-app
php artisan serve',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Какие основные преимущества Laravel?',
                'answer' => 'Главные плюсы Laravel:

1. **Читаемый и элегантный синтаксис** — код легко поддерживать.
2. **Полная экосистема**: `Sanctum`, `Horizon`, `Telescope`, `Octane`, `Scout`, `Cashier`, `Pulse`, `Reverb`.
3. **Eloquent ORM** — работа с БД через объекты, со связями и событиями.
4. **Миграции, сидеры, фабрики** — версионирование схемы и тестовых данных.
5. **Из коробки**: очереди, события, кеш, сессии, аутентификация, валидация.
6. **Artisan CLI** — генерация кода и обслуживание одной командой.
7. **Огромное community** и подробная документация на laravel.com.
8. **Регулярные релизы** — раз в год, активное развитие.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 1,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое архитектура MVC и как она реализована в Laravel?',
                'answer' => '**MVC (Model-View-Controller)** — паттерн разделения логики на 3 части:

- **Model** — работа с данными. В Laravel это Eloquent-модели в `app/Models`. Знают про БД, но не про HTTP.
- **View** — отображение. Blade-шаблоны в `resources/views`. Знают про вёрстку, но не про БД.
- **Controller** — оркестратор. Принимает HTTP-запрос, дёргает модель, передаёт результат во view.

Поток запроса в Laravel:

1. **Route** (`routes/web.php`) принимает URL и направляет в метод контроллера.
2. **Controller** валидирует ввод, дёргает **Model**.
3. **Model** общается с БД.
4. **Controller** возвращает `view(...)` или JSON.

Цель — каждый слой делает свою работу. Бизнес-логика обычно выносится из контроллера в **Service/Action**-классы, чтобы контроллер оставался тонким.',
                'code_example' => '// Route
Route::get(\'/users/{id}\', [UserController::class, \'show\']);

// Controller
class UserController extends Controller {
    public function show($id) {
        $user = User::findOrFail($id); // Model
        return view(\'users.show\', compact(\'user\')); // View
    }
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Опиши структуру проекта Laravel - что лежит в основных папках?',
                'answer' => 'Основные папки в корне Laravel-проекта:

- **`app/`** — код приложения: `Models`, `Http/Controllers`, `Http/Middleware`, `Providers`, `Console`, `Jobs`, `Events`, `Listeners`.
- **`bootstrap/`** — стартовые файлы (`app.php` — конфиг ядра в L11+) и кеш фреймворка.
- **`config/`** — конфиги (`app.php`, `database.php`, `auth.php`, ...).
- **`database/`** — `migrations/`, `seeders/`, `factories/`.
- **`public/`** — точка входа `index.php` и статика (CSS, JS, изображения). Сюда смотрит web-сервер.
- **`resources/`** — `views/` (Blade), `js/`, `css/`, `lang/`.
- **`routes/`** — `web.php`, `api.php`, `console.php`, `channels.php`.
- **`storage/`** — логи, кеш, скомпилированные view, загруженные файлы (`app/`, `framework/`, `logs/`).
- **`tests/`** — тесты (`Feature/`, `Unit/`).
- **`vendor/`** — зависимости Composer (в Git **не коммитится**).',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 2,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Опиши жизненный цикл запроса в Laravel.',
                'answer' => 'Каждый HTTP-запрос проходит через **9 этапов**:

1. **`public/index.php`** — точка входа web-сервера. Подключает Composer autoload и `bootstrap/app.php`.
2. **`Application`** — создаётся экземпляр контейнера (`Illuminate\\Foundation\\Application`).
3. **Bootstraps** — `LoadEnvironmentVariables`, `LoadConfiguration`, `HandleExceptions`, `RegisterFacades`, `RegisterProviders`, `BootProviders`.
4. **HTTP Kernel** (`Illuminate\\Foundation\\Http\\Kernel`) пропускает запрос через **глобальные middleware** (`TrustProxies`, `HandleCors`, `PreventRequestsDuringMaintenance`).
5. **Router** — находит маршрут и применяет его **route/group middleware**.
6. **Controller / closure** — выполняется бизнес-логика.
7. **Response** — Laravel приводит возврат (`view`, `array`, `Resource`) к объекту `Response`.
8. **Middleware response phase** — middleware обрабатывают **исходящий** ответ.
9. **`terminate()`** — middleware с этим методом выполняются **после отправки ответа клиенту** (например, сохранение сессии, отправка логов).

**Где конфигурируется в Laravel 11:**

- **`app/Http/Kernel.php` удалён** — middleware-стек настраивается в `bootstrap/app.php` через `->withMiddleware(...)`.
- **`app/Console/Kernel.php` удалён** — расписание/команды в `routes/console.php`.
- **`app/Exceptions/Handler.php` удалён** — обработка через `->withExceptions(...)` в `bootstrap/app.php`.

Сам класс `Foundation\\Http\\Kernel` остался **внутри фреймворка** — просто без user-facing наследника.',
                'code_example' => '// bootstrap/app.php (Laravel 11)
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.\'/../routes/web.php\',
        api: __DIR__.\'/../routes/api.php\',
        commands: __DIR__.\'/../routes/console.php\',
        health: \'/up\',
    )
    ->withMiddleware(function (Middleware $m) {
        $m->web(append: [EnsureUserIsActive::class]);
        $m->alias([\'admin\' => EnsureIsAdmin::class]);
    })
    ->withExceptions(function (Exceptions $e) {
        $e->dontReport(MissedPaymentException::class);
    })
    ->create();',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Facades в Laravel и как они работают?',
                'answer' => '**Facade** — статический «прокси» к объекту, который Laravel получает из контейнера.

Вы пишете `Cache::put(...)`, но **внутри** Laravel дёргает `app(\'cache\')->put(...)` — то есть метод **обычного объекта-singleton** из контейнера.

**Как это работает:**

- Класс `Cache` наследует `Illuminate\\Support\\Facades\\Facade`.
- Метод `getFacadeAccessor()` возвращает строку-binding в контейнере: `\'cache\'`.
- Магический `__callStatic` перенаправляет любой статический вызов на резолв из контейнера: `app(\'cache\')->put(...)`.

**Зачем такой механизм:**

- **Краткость** — `Cache::put()` vs `app(\'cache\')->put()` или DI-конструктор.
- **Под капотом — обычный объект** — Laravel не нарушает тестируемость: фасады поддерживают `Cache::shouldReceive(...)` (mock) и `Cache::fake()`.
- **Не путать со Service Locator** — фасады дают **синтаксический сахар** над DI, не замена.

**Сравнение с альтернативами:**

| Способ | Краткость | Тестируемость | Явность зависимостей |
|---|---|---|---|
| **Facade** (`Cache::get`) | Высокая | Через `shouldReceive` | Скрыта |
| **Helper** (`cache()->get(...)`) | Высокая | Через `Cache::fake()` | Скрыта |
| **DI Contract** (`Repository $cache`) | Средняя | Тривиально через `instance()` | Явная |

**Real-time facades:** `Facades\\App\\Services\\OrderService::create()` — Laravel сам делает фасад из любого класса, добавив префикс `Facades\\`. Удобно для legacy-кода.',
                'code_example' => 'use Illuminate\Support\Facades\Cache;

Cache::put(\'key\', \'value\', 60);
// эквивалентно
app(\'cache\')->put(\'key\', \'value\', 60);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как работает локализация в Laravel?',
                'answer' => 'Lang-файлы лежат в **`lang/`** (в Laravel 9+, до этого — `resources/lang/`). Публикуются командой `php artisan lang:publish`.

Два формата ключей:

- **JSON** (`lang/ru.json`) — ключ = английская фраза, значение = перевод. Удобно для длинных строк.
- **PHP-массивы** (`lang/ru/messages.php`) — короткие ключи: `__(\'messages.greeting\')`. Удобно для группировки.

Хелперы:

- **`__(\'key\')`** или **`trans(\'key\')`** — читает строку.
- **`trans_choice(\'apples\', $count)`** — учёт числа (`1 яблоко|:count яблок`).
- **`@lang(\'...\')`** в Blade.

Управление локалью:

- **`app()->getLocale()`** — текущая.
- **`app()->setLocale(\'ru\')`** — переключить.
- Дефолт — `config(\'app.locale\')` (берётся из `APP_LOCALE` в `.env`).

Обычно локаль ставится через middleware на основе сессии/заголовка `Accept-Language`/URL.',
                'code_example' => '// lang/ru.json
{ "Welcome": "Добро пожаловать" }

// lang/ru/messages.php
return [\'greeting\' => \'Привет, :name\'];

echo __(\'Welcome\');
echo __(\'messages.greeting\', [\'name\' => \'Anna\']);
echo trans_choice(\'apples\', 5);',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Scout?',
                'answer' => '**Laravel Scout** (`laravel/scout`) — официальный пакет для **полнотекстового поиска** в Eloquent-моделях. Абстракция над движком: API одинаковый, драйвер меняется в `.env`.

**Драйверы:**

| Драйвер | Тип | Подходит для |
|---|---|---|
| **Algolia** | SaaS | Production, лучшее ранжирование |
| **Meilisearch** | Self-hosted (Rust) | Middle-проекты, бесплатно |
| **Typesense** | Self-hosted (C++) | Большие корпуса, геопоиск |
| **database** | LIKE по БД | Прототип, до ~100k записей |
| **collection** | LIKE в памяти | Тесты |

**Как подключить:**

- На модели — `use Searchable;`
- Метод `toSearchableArray(): array` — какие поля индексировать.
- Save/delete модели **автоматически** обновляют индекс (опционально через очередь — `SCOUT_QUEUE=true`).

**Поиск:**

```php
Post::search(\'laravel\')
    ->where(\'published\', true)
    ->paginate(15);
```

**Подводные камни:**

- **`SCOUT_QUEUE=true`** обязателен в проде — иначе SMTP к Algolia/Meili синхронно тормозит каждый `save()`.
- **SoftDeletes** требуют `SCOUT_SOFT_DELETES=true`, иначе удалённые остаются в индексе.
- **Bulk-операции** (`Model::where()->update()`) **не обновляют** Scout-индекс — нужно `Model::where()->searchable()` вручную.
- **Импорт большой таблицы** — `php artisan scout:import "App\\Models\\Post"` (чанкует через `chunkById`).',
                'code_example' => 'class Post extends Model {
    use Searchable;

    public function toSearchableArray(): array {
        return [\'title\' => $this->title, \'body\' => $this->body];
    }
}

$results = Post::search(\'laravel\')->paginate(15);

php artisan scout:import "App\\Models\\Post"',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Telescope?',
                'answer' => '**Laravel Telescope** (`laravel/telescope`) — официальный **debug-dashboard** для Laravel-приложения. Доступ через `/telescope`.

**Что показывает:**

- **HTTP-запросы** — URL, метод, status, latency, payload.
- **Database** — все SQL-запросы с временем, bindings, источником вызова.
- **Jobs** — диспатченные, обработанные, failed.
- **Mail / Notifications** — что отправлено, кому, payload.
- **Events / Listeners** — что бросалось, кто отреагировал.
- **Cache / Redis** — hit/miss, set/forget.
- **Logs / Exceptions** — все ошибки с stack trace.
- **Models** — created/updated/deleted с diff.
- **Schedule** — расписание-команды.
- **Gates / Policies** — авторизация решения.

**Когда нужен:**

- **Development** — стандартный дефолт.
- **Staging** — ограниченный доступ для QA.
- **Production** — **обычно выключают** (или ограничивают `--tag=fresh-after-installer-only` для админа), потому что:

**Подводные камни в проде:**

- **Большой объём данных** — Telescope пишет в БД на **каждый запрос**, разрастается до GB за день.
- **Защита маршрута** — gate `Telescope::auth(fn ($u) => $u->isAdmin())` обязателен.
- **Sampling** — `TELESCOPE_ENABLED=true` + sampling в `TelescopeServiceProvider::register()` (например, 10% запросов).
- **Prune** — расписать `php artisan telescope:prune --hours=48` в crone, иначе таблица `telescope_entries` лопнет.

**Альтернатива для прода — Laravel Pulse** (`laravel/pulse`): легче, считает агрегаты, не пишет каждое событие.',
                'code_example' => 'composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate

// доступ /telescope',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Pulse?',
                'answer' => '**Laravel Pulse** (`laravel/pulse`) — **лёгкий production-dashboard** для мониторинга performance в реальном времени. От той же команды Laravel.

**Идея:** Telescope пишет каждое событие → разрастается, в проде не используется. Pulse **агрегирует** метрики и пишет компактные summary → можно держать в проде постоянно.

**Что показывает:**

- **Slow queries** — top SQL по latency.
- **Slow jobs** — какие очереди тормозят.
- **Slow requests** — медленные HTTP-эндпоинты с p50/p95/p99.
- **Slow outgoing requests** — медленные HTTP-вызовы наружу (Guzzle).
- **Exceptions** — топ ошибок по частоте.
- **Cache hit rate** — процент попаданий.
- **Servers** — load average, CPU, memory, disk на каждой ноде (через `pulse:check` worker).
- **Users / Usage** — активные пользователи, запросы по юзерам.

**Сравнение с Telescope:**

| Параметр | **Telescope** | **Pulse** |
|---|---|---|
| Цель | Debug в dev | Мониторинг в prod |
| Запись | Каждое событие | Агрегаты с sampling |
| Объём данных | Большой | Малый |
| Production | Не рекомендуется | Дефолтный путь |
| Что важнее | Полнота | Производительность |

**Установка:** `composer require laravel/pulse` → `php artisan pulse:install` → `php artisan migrate`. Доступ через `/pulse`.

**Подводные камни:**

- **`Pulse::user(fn ($u) => $u->isAdmin())`** — обязательно ограничить доступ.
- **Sampling** — `PULSE_INGEST_INTERVAL`, `PULSE_TRIM_TIMEFRAMES_OLDER_THAN_HOURS` тюнятся под нагрузку.
- **Pulse не заменяет** APM (NewRelic/Datadog) для глубокого профилирования — он скорее «здоровье в реальном времени».',
                'code_example' => 'composer require laravel/pulse
php artisan vendor:publish --tag=pulse-config
php artisan migrate

// доступ /pulse',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Почему нельзя использовать env() вне config-файлов после кеширования?',
                'answer' => 'При **`php artisan config:cache`** Laravel:

1. Выполняет **все файлы из `config/`** (которые вызывают `env(\'...\')`).
2. Складывает результат в **`bootstrap/cache/config.php`**.
3. **При следующих запросах `.env` НЕ читается** — фреймворк не подгружает `vlucas/phpdotenv`.

**Что из этого следует:**

- `env(\'STRIPE_KEY\')` **в коде** (не в `config/`) **вернёт null** после `config:cache`, потому что `$_ENV` уже не наполнен значениями `.env`.
- Точнее — вернёт **то, что есть в OS-окружении** (Docker `-e`, systemd `Environment=`) либо **default** из второго аргумента.

**Почему это сделано:**

- **Производительность** — `.env` парсится regex-ом, на high-load это десятки µs на запрос.
- **Атомарность** — конфиг становится «застывшим снимком», нет race между чтением и сменой `.env`.

**Правильный паттерн:**

```php
// config/services.php - env читается ОДИН раз при config:cache
return [\'stripe\' => [\'key\' => env(\'STRIPE_KEY\')]];

// в коде - config() работает всегда, читает из кеша
$key = config(\'services.stripe.key\');
```

**Подводные камни:**

- **Забыли `config:clear` после смены `.env`** — старое значение остаётся в кеше. Деплой-скрипт должен делать `config:clear` или `config:cache`.
- **`env()` в Blade-шаблоне** — та же проблема. Использовать `config()`.
- **`env()` в Service Provider `boot()`** — после `config:cache` тоже сломается, потому что boot выполняется после загрузки кеша.
- **Tests** — `config:cache` обычно **не делают** в тестовом окружении, поэтому `env()` там работает.',
                'code_example' => '// плохо - в коде
$key = env(\'STRIPE_KEY\'); // вернёт OS-env / default, но не значение из .env
                          // после config:cache - частый источник "пустых" переменных в проде

// правильно
// config/services.php
return [\'stripe\' => [\'key\' => env(\'STRIPE_KEY\')]];

// в коде
$key = config(\'services.stripe.key\');',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Macroable trait?',
                'answer' => '**`Macroable`** — трейт из `Illuminate\\Support\\Traits`, позволяющий **добавлять кастомные методы в Laravel-классы во время выполнения** через статический `::macro()`. Под капотом — `__call` / `__callStatic`, ищущие зарегистрированное замыкание по имени метода.

**Где используется в Laravel:**

- **`Collection`**, **`Str`**, **`Stringable`**, **`Arr`** — расширить чейн.
- **`Request`**, **`Response`**, **`RedirectResponse`** — добавить методы вроде `$request->isFromMobileApp()`.
- **`Route`**, **`Blueprint`**, **`QueryBuilder`** — добавить свои методы DSL.
- **`Carbon`** — наследует `Macroable` через `nesbot/carbon`.

**Где регистрируют:**

- **`AppServiceProvider::boot()`** — макросы доступны с момента boot всех провайдеров.
- **Конкретный пакетный provider** — для расширений из вендора.

**Ключевые правила:**

| Правило | Зачем |
|---|---|
| **Имя должно быть НОВЫМ** | `__call` срабатывает **после** реальных методов; если метод уже есть в классе — макрос **никогда не вызовется** |
| Регистрировать **в `boot()`**, не в `register()` | `register()` может выполниться до загрузки трейта |
| **`$this` в замыкании** = инстанс класса | Так макрос видит свойства Collection / Request |
| Для статических вызовов | Просто `Str::macro(...)` — работает через `__callStatic` |

**Подвох:** `Str::isUuid()` уже существует в ядре — макрос с таким именем станет мёртвым кодом. Перед регистрацией проверять: **`Collection::hasMacro("name")`**.

**Альтернатива:** для **более типобезопасных** расширений — собственный класс-обёртка или `Mixin::class` через `Collection::mixin(...)` (умеет принимать целый объект-набор методов).',
                'code_example' => 'use Illuminate\Support\Str;

// Имя должно быть НОВЫМ - Macroable работает через __callStatic / __call,
// и приоритет всегда у реального метода класса: если метод уже определён,
// он перекрывает макрос (макрос становится мёртвым кодом).
// Например, Str::isUuid() уже существует в ядре - макрос с таким именем недостижим.
Str::macro(\'isHexColor\', function ($value) {
    return preg_match(\'/^#[0-9a-f]{3}([0-9a-f]{3})?$/i\', $value) === 1;
});

Str::isHexColor(\'#1abc9c\'); // true

Collection::macro(\'toUpper\', function () {
    return $this->map(fn($v) => strtoupper($v));
});

collect([\'a\', \'b\'])->toUpper(); // [\'A\', \'B\']',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как обрабатывать исключения в Laravel?',
                'answer' => 'Все необработанные исключения попадают в **глобальный handler** Laravel. Он делает две вещи: **logging** (через `report()`) и **рендеринг ответа** (через `render()`).

**Где конфигурируется:**

| Версия | Где |
|---|---|
| **Laravel 10 и ниже** | `app/Exceptions/Handler.php` (свойства `$dontReport`, `$levels`, `register()` метод) |
| **Laravel 11+** | `bootstrap/app.php` через `->withExceptions(...)` — handler-файла больше нет |

**Что можно настроить:**

- **`->dontReport(ClassName::class)`** — не логировать конкретное исключение.
- **`->report(fn (CustomException $e) => Sentry::captureException($e))`** — кастомное reporting.
- **`->render(fn (NotFoundHttpException $e, Request $r) => ...)`** — кастомный response.
- **`->stopIgnoring(ClassName::class)`** — наоборот, начать логировать то, что Laravel по умолчанию пропускает.
- **`->level(NotificationException::class, LogLevel::CRITICAL)`** — задать log level для класса.

**Кастомные исключения с self-rendering:**

```php
class PaymentFailed extends Exception {
    public function render(Request $r): JsonResponse {
        return response()->json([\'error\' => $this->getMessage()], 422);
    }

    public function report(): void {
        // Кастомный logging
    }
}
```

Если кастомный exception имеет методы `render()` и/или `report()` — Laravel автоматически их использует.

**Подводные камни:**

- **`render()` возвращает `null`** → Laravel идёт по дефолтной цепочке (рендерит как обычный exception).
- **`report()` возвращает `false`** → продолжает обычное логирование (не подавляет).
- **`abort(404)`** бросает `NotFoundHttpException` — обрабатывается стандартно, рендерит `404.blade.php` или JSON.
- **API-маршруты** — в L11 определяй `api` middleware-группу с JSON-рендерингом исключений через `$r->expectsJson()`.',
                'code_example' => '// Laravel 11+ bootstrap/app.php
->withExceptions(function (Exceptions $e) {
    $e->render(function (NotFoundHttpException $e, Request $r) {
        if ($r->is(\'api/*\')) {
            return response()->json([\'error\' => \'Not found\'], 404);
        }
    });
})

// Кастомное исключение
class PaymentFailed extends Exception {
    public function render(): JsonResponse {
        return response()->json([\'error\' => $this->getMessage()], 422);
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Action Classes (Single-Purpose Service) и зачем они нужны?',
                'answer' => '**Action Class** — класс с **одним публичным методом** (`execute`, `handle` или `__invoke`), инкапсулирующий **ровно одно действие** приложения: `CreateUserAction`, `SuspendUserAction`, `ChargeFailedPaymentAction`.

**Не путать с invokable-контроллером** (`__invoke` в `app/Http/Controllers`): Action — **сервис-объект, не привязанный к HTTP-запросу**. Его можно дёрнуть из:

- **Controller** — `$action->execute($data)`.
- **Artisan-команды** — `RetryFailedPaymentsCommand::handle()`.
- **Job-а** — `dispatch(new ProcessRefundJob($id))` + `$action->execute()` внутри.
- **Тестов** — без HTTP-обвязки.

**Зачем выносить:**

- **Тонкий контроллер** — `validate → action → resource`, бизнес-логика снаружи.
- **Single Responsibility** — каждое действие — отдельный класс с собственными зависимостями.
- **Тестируемость** — мок-ать одну зависимость легче, чем весь сервис.
- **Переиспользуемость** — Controller + CLI + Job дёргают **тот же код**.

**Когда Action vs Service:**

| Признак | Action | Service |
|---|---|---|
| Сколько публичных методов | 1 | Несколько |
| Имя | `<Verb><Noun>Action` | `<Noun>Service` |
| Зависимости в `__construct` | Только нужные для одного действия | Общие для всего сервиса |
| Когда выбирать | Сложная операция со своими зависимостями | Простая связка CRUD-операций |

**Прагматика:** начинать с `Service`, выносить в `Action`, когда метод стал толстым (>30 строк) или появились свои зависимости. В одном проекте оба паттерна сосуществуют.',
                'code_example' => 'class CreateUserAction {
    public function execute(array $data): User {
        return DB::transaction(function () use ($data) {
            $user = User::create($data);
            $user->profile()->create([\'avatar\' => null]);
            event(new UserCreated($user));
            return $user;
        });
    }
}

// Controller
public function store(StoreUserRequest $r, CreateUserAction $a) {
    $user = $a->execute($r->validated());
    return new UserResource($user);
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'В чём разница между config() и env()?',
                'answer' => 'Две похожие функции с **принципиально разным контрактом**.

**`env(\'KEY\', $default)`** — читает переменную окружения процесса (через `$_ENV` / `getenv()`).

- До `php artisan config:cache` Laravel загружает `.env` через `vlucas/phpdotenv` в окружение — `env()` везде «работает».
- После `config:cache` загрузка `.env` **пропускается** — `env()` видит **только** переменные, заданные на уровне ОС (Docker `-e`, systemd `Environment=`).
- На практике в проде после `config:cache` `.env`-only переменные становятся «невидимыми» → отсюда ощущение «возвращает null».

**`config(\'app.name\')`** — читает из **загруженного конфига** (из `config/` или из кеша).

- Работает **всегда**, в любой момент жизненного цикла.
- Значения «зашиты» в кеш `bootstrap/cache/config.php` после `config:cache`.

**Сравнение:**

| Параметр | `env()` | `config()` |
|---|---|---|
| Источник | OS-окружение / `.env` (до cache) | Файлы `config/` или кеш |
| Где можно использовать | **Только** в `config/` | Везде в коде |
| После `config:cache` | Видит только OS-env / default | Работает как раньше |
| Тип значения | Строка / `true`/`false` (магическая) | Любой PHP-тип |

**Правило:** `env()` читать **только** в файлах `config/`, в коде использовать `config()`.

**Подводные камни:**

- **Не делать** `cache()->remember(env(\'KEY\'), ...)` — в проде упадёт.
- **Изменили `.env` в проде** — забыли `config:clear` → старое значение в кеше.
- **`env(\'X\', false)`** — `false` строкой! Дефолт — `null` или `boolean false`. Документация phpdotenv vs Laravel — проверять.',
                'code_example' => '// .env
APP_NAME=MyApp

// config/app.php
\'name\' => env(\'APP_NAME\', \'Laravel\'),

// в коде
config(\'app.name\'); // правильно
env(\'APP_NAME\');    // null после config:cache в проде',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Vapor и Forge? Какие у них подводные камни?',
                'answer' => 'Два **разных** платных продукта Laravel для деплоя.

**Forge** — сервис управления **VPS** (DigitalOcean, AWS, Linode, Vultr, Hetzner).

- Автоматизирует настройку **nginx**, **php-fpm**, **supervisor**, **Let\'s Encrypt SSL**, deploy через git.
- Вы платите за **VPS отдельно** + ~$12/мес за Forge.
- Поведение — обычный Linux-сервер с долгоживущими процессами (PHP-FPM, queue worker, websockets).
- Подходит для большинства production-проектов.

**Vapor** — **serverless**-платформа для Laravel на AWS Lambda.

- Без серверов: оплата по запросам, **автомасштабирование** до тысяч инстансов.
- Использует AWS Lambda (PHP), API Gateway, RDS, ElastiCache, S3, SQS, CloudWatch.
- Идеально для **резко-неравномерной** нагрузки (промо-кампании, batch-обработка).

**Сравнение:**

| Параметр | **Forge** | **Vapor** |
|---|---|---|
| Инфраструктура | Долгоживущий VPS | AWS Lambda |
| Цена | Фикс/мес + VPS | Per-invocation |
| Масштабирование | Ручное / Auto Scaling | Автоматическое |
| Cold start | Нет | Да (100-500ms) |
| Файловая система | Persistent | **/tmp эфемерная** |
| Долгоживущие процессы | Queue worker, websockets | Нет — SQS, отдельные сервисы |

**Главный подводный камень Vapor — файловая система:**

- `/tmp` размером до **10 GiB** (по умолчанию 512 MB) **технически работает** в рамках одного invoke.
- Но **эфемерна** — между прогревами контейнера данные теряются, между разными контейнерами не шарятся.
- Для **персистентного** хранения (avatars, uploads, PDF) — **обязательно S3**.
- Для transient-обработки (распаковать, отресайзить, удалить, загрузить в S3) — `/tmp` подходит.

**Что ещё специфично для Vapor:**

- **Очереди** — через **SQS** (нет долгоживущих воркеров).
- **Расписание** — через **CloudWatch Events**.
- **WebSocket** — нельзя в Lambda; отдельный сервис (Pusher/Ably/Reverb на EC2).
- **Sessions** — только Redis/Database (`SESSION_DRIVER=redis`), не `file`.',
                'code_example' => '<?php
// config/filesystems.php - в Vapor местный disk заворачивают в /tmp
"local" => [
    "driver" => "local",
    "root"   => "/tmp", // эфемерно, но работает в рамках одного invoke
],

// Workflow: скачали → обработали → залили в S3
$tmp = Storage::disk("local")->path("export.csv");
file_put_contents($tmp, $csvContent);          // /tmp/export.csv
$pdf = $this->renderPdf($tmp);                 // тоже в /tmp
Storage::disk("s3")->put("reports/{$id}.pdf", file_get_contents($pdf));
unlink($tmp); unlink($pdf);                    // подчистить за собой',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое helpers в Laravel и приведи примеры.',
                'answer' => '**Хелперы** — глобальные функции Laravel, доступные везде. Не надо импортировать неймспейсы.

Часто используемые:

- **URL и роутинг**: `route()`, `url()`, `asset()`, `redirect()`, `back()`.
- **Запрос/сессия/auth**: `request()`, `session()`, `cookie()`, `auth()`, `csrf_token()`.
- **Ответ**: `response()`, `view()`, `abort()`, `abort_if()`, `abort_unless()`.
- **Конфиг и окружение**: `config()`, `env()` (только в `config/`!).
- **Время**: `now()`, `today()` — возвращают `Carbon`.
- **Коллекции и строки**: `collect()`, `str()`, `tap()`.
- **Валидация**: `validator()`.
- **Доступ к массивам**: `data_get($array, \'user.profile.name\', \'default\')`, `data_set()`.
- **Утилиты**: `throw_if()`, `throw_unless()`.

Также есть статические классы: **`Str::`** (`Str::slug`, `Str::random`), **`Arr::`** (`Arr::get`, `Arr::pluck`).',
                'code_example' => 'now()->addDays(7);
str(\'Hello\')->upper(); // Stringable
collect([1,2,3])->sum();
abort_if(! $user, 403);
$value = data_get($array, \'user.profile.name\', \'default\');
tap($user, fn($u) => $u->update([\'last_login\' => now()]))->save();',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что нового в Laravel 11 по сравнению с Laravel 10?',
                'answer' => 'Laravel 11 — крупный релиз с **упрощённой структурой** и новыми пакетами.

**Структурные изменения:**

| Что | Было (L10) | Стало (L11) |
|---|---|---|
| HTTP middleware-стек | `app/Http/Kernel.php` | `bootstrap/app.php` → `withMiddleware()` |
| Console schedule | `app/Console/Kernel.php` | `routes/console.php` |
| Exception handler | `app/Exceptions/Handler.php` | `bootstrap/app.php` → `withExceptions()` |
| Service providers | `config/app.php` массив | `bootstrap/providers.php` |
| Конфиг-файлов | ~15 | ~7 (многие опции в дефолтах) |

**Новые фичи:**

- **`/up`** — health-endpoint из коробки.
- **`Limit::perSecond(10)`** — per-second rate limiting в Throttle.
- **`casts(): array`** — метод модели как альтернатива свойству `$casts`.
- **`php artisan install:api`** — установка Sanctum + `routes/api.php`.
- **`php artisan install:broadcasting`** — установка Reverb + `routes/channels.php`.
- **Implicit Enum Binding** в роутах (на самом деле появилось в L9, но в L11 стало стабильнее).
- **HasMiddleware-интерфейс** на контроллерах вместо конструктор-`$this->middleware()`.

**Новые пакеты экосистемы:**

- **Reverb** — WebSocket-сервер на ReactPHP, Pusher-совместимый.
- **Pennant** — feature flags.
- **Volt** — single-file Livewire components.
- **Folio** — page-based routing в духе Next.js.
- **Prompts** — красивые CLI-формы для artisan-команд.

**Требования:** минимальный **PHP 8.2**.',
                'code_example' => '// bootstrap/app.php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(web: __DIR__.\'/../routes/web.php\')
    ->withMiddleware(function (Middleware $m) {
        $m->web(append: [EnsureUserIsActive::class]);
    })
    ->withExceptions(function (Exceptions $e) {})
    ->create();',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Зачем нужен Pipeline и как его использовать вне middleware?',
                'answer' => '**`Illuminate\\Pipeline\\Pipeline`** — реализация паттерна **Chain of Responsibility** в Laravel. Принимает payload и **пропускает его через цепочку «pipe-ов»**, каждый из которых может **модифицировать**, **обогатить** или **прервать** обработку.

**Под капотом — HTTP middleware:**

- `Kernel::handle($request)` — это именно `Pipeline::send($request)->through($middlewares)`.
- Сигнатура pipe: **`handle($passable, Closure $next)`** — как у HTTP middleware.

**Где полезно вне middleware:**

| Сценарий | Цепочка |
|---|---|
| **Бизнес-цепочка оформления заказа** | `ValidateInventory → ApplyPromoCodes → ChargeCustomer → EmitEvent` |
| **Импорт данных** | `Normalize → Validate → Dedupe → Persist → Index` |
| **Обработка webhook-а** | `VerifySignature → Parse → Route → Reply` |
| **Подготовка response** | `AddCors → Compress → AddRateLimitHeaders` |

**Альтернативы и когда что брать:**

- **Pipeline** — когда цепочка **линейная**, шаги однотипные, может прерваться (через `throw` или `return`).
- **Длинный `if-else`** — плох тем, что **смешивает шаги** и нельзя переиспользовать отдельные блоки.
- **Events + Listeners** — когда шаги **независимы** и должны выполняться **параллельно** (или порядок не важен).
- **Bus chain** — когда шаги — это **отдельные jobs** в очереди.

**API:**

- **`Pipeline::send($payload)->through([...])->thenReturn()`** — вернуть финальный payload.
- **`->then(fn ($p) => ...)`** — финальный callback с финальным результатом.
- **`->via("method")`** — назвать метод pipes отличный от `handle` (например, `via("execute")`).

**Pipes могут быть:**

- **Замыканием** `fn ($payload, $next) => $next($payload)`.
- **Классом** с `handle()` (или другим методом через `via()`).',
                'code_example' => '<?php
use Illuminate\\Pipeline\\Pipeline;

// Бизнес-цепочка оформления заказа
$result = app(Pipeline::class)
    ->send(\$order)
    ->through([
        ValidateInventory::class,
        ApplyPromoCodes::class,
        ChargeCustomer::class,
        EmitOrderPlacedEvent::class,
    ])
    ->thenReturn();

// Pipe — класс
class ValidateInventory
{
    public function handle(Order \$order, Closure \$next)
    {
        foreach (\$order->items as \$item) {
            if (\$item->stock < \$item->qty) {
                throw new OutOfStockException(\$item);
            }
        }
        return \$next(\$order);
    }
}

// Pipe может прервать цепочку — просто не вызовет $next
class CheckMaintenance
{
    public function handle(\$payload, Closure \$next)
    {
        if (app()->isDownForMaintenance()) {
            return response("Maintenance", 503);  // прервали, $next не вызван
        }
        return \$next(\$payload);
    }
}

// Pipe как замыкание
\$result = app(Pipeline::class)
    ->send(\$request)
    ->through([
        fn (\$r, \$next) => \$next(\$r->merge(["traced_at" => now()])),
    ])
    ->thenReturn();',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как Laravel Scout работает и какие нюансы при индексации больших коллекций?',
                'answer' => '**Scout** — официальный пакет полнотекстового поиска, **абстракция над движком**. API одинаковый, драйвер меняется в `.env`.

**Драйверы:**

| Драйвер | Тип | Когда |
|---|---|---|
| **`algolia`** | SaaS | Прод, лучшее ранжирование |
| **`meilisearch`** | Self-hosted (Rust) | Middle-проекты |
| **`typesense`** | Self-hosted (C++) | Большие корпуса, геопоиск |
| **`database`** | LIKE по БД | Прототип, до ~100k записей |
| **`collection`** | LIKE в памяти | Тесты |

**Как работает с моделью:**

- Трейт **`Searchable`** на модели.
- Метод **`toSearchableArray()`** — какие поля индексировать.
- **`save()` / `delete()`** автоматически синхронизируют индекс (опционально через очередь).

**Критичные нюансы для больших коллекций:**

1. **`SCOUT_QUEUE=true`** — обязательно в проде. Иначе каждый `save()` синхронно вызывает HTTP к Algolia/Meili → тормозит запросы пользователей.
2. **`scout:import`** — чанкует выборку через `chunkById`, не грузит миллион моделей в память.
3. **`scout:flush`** — удалить весь индекс (перед полным re-import).
4. **Bulk-операции НЕ синхронизируют индекс:**
   - `Model::where(...)->update([...])` — обходит Eloquent-события → индекс остался старым.
   - Решение: `Model::where(...)->searchable()` руками после bulk.
5. **`SoftDeletes`** требуют **`SCOUT_SOFT_DELETES=true`** в `config/scout.php`, иначе `deleted_at` модели остаются в индексе.

**Сложные фильтры:**

- **`search($q)->where("status", "active")->whereIn("category_id", [1,2])`** — простые фильтры через `where()`.
- **`search($q, fn ($engine, $query, $options) => ...)`** — callback для движок-специфичных запросов (Meilisearch facets, Algolia numericFilters).

**Подводный камень кеша:** результаты поиска — это **ID моделей** из индекса; затем Scout делает `Model::whereIn("id", $ids)`. Если используете `softDeletes` + не передаёте флаг — пользователи увидят «удалённые» строки в выдаче.',
                'code_example' => '<?php
use Laravel\\Scout\\Searchable;

class Product extends Model
{
    use Searchable;

    public function toSearchableArray(): array
    {
        return [
            "name"        => \$this->name,
            "description" => \$this->description,
            "category"    => \$this->category->name,    // через eager-load
            "price"       => (int) \$this->price,
            "is_active"   => (bool) \$this->is_active,
        ];
    }

    // Опционально — кастомное имя индекса
    public function searchableAs(): string
    {
        return "products_index_v2";
    }

    // Опционально — не индексировать неактивные
    public function shouldBeSearchable(): bool
    {
        return \$this->is_active;
    }
}

// Импорт большой таблицы (чанкует, не валит память)
// php artisan scout:import "App\\Models\\Product"
// php artisan scout:flush  "App\\Models\\Product"

// Поиск с фильтрами
\$results = Product::search("laravel book")
    ->where("is_active", true)
    ->whereIn("category_id", [1, 2, 3])
    ->paginate(15);

// Bulk-update — индекс надо обновить вручную
Product::where("category_id", 5)->update(["price" => 100]);
Product::where("category_id", 5)->searchable();  // ← синхронизация',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое DTO (Data Transfer Object) и зачем они нужны в Laravel?',
                'answer' => '**DTO (Data Transfer Object)** — объект для передачи **типизированных данных** между слоями приложения: `Request → Action/Service → Repository`, `Service → Job`, `Service → API client`.

**Что не так с ассоциативными массивами `$request->validated()`:**

- **Нет автокомплита** и статической проверки типов в IDE.
- **«Магические строки»** по ключам — переименование поля ломает всё молча.
- **Нельзя протащить в job-ы**: при передаче в очередь исходный Request уже недоступен.
- **Опечатки в ключах** не ловятся — `$data["emial"]` вернёт `null`.

**Что даёт DTO:**

- **Класс с явно типизированными `readonly` свойствами** — IDE подсказывает поля.
- **Конструктор делает контракт явным** — нельзя забыть параметр.
- **PHPStan / Psalm ловят опечатки** на этапе анализа.
- **Сериализуется в очередь** как обычный объект — независим от HTTP.

**Современные подходы в Laravel:**

| Подход | Когда |
|---|---|
| **`final readonly class`** (PHP 8.2+) | Чистый PHP, без зависимостей |
| **Отдельные `public readonly` свойства** (PHP 8.1+) | Если нужны не-readonly utility-методы |
| **`spatie/laravel-data`** | Декларативно — автосбор из Request, валидация, сериализация |

**Критично для job-ов:**

- DTO сериализуется в payload очереди **как обычный объект** — никакой зависимости от Request.
- Можно дёрнуть тот же Action из **CLI / job / контроллера** с одинаковым контрактом.

**Антипаттерн:** передавать сырой `$request` в Action/Job — нарушает Single Responsibility и делает класс непригодным к запуску из консоли или теста.',
                'code_example' => '<?php
// Чистый PHP 8.1+ readonly DTO
final readonly class CreateOrderData
{
    public function __construct(
        public int $userId,
        public string $currency,
        public array $items,        // OrderItemData[]
        public ?string $promoCode = null,
    ) {}

    public static function fromRequest(StoreOrderRequest $r): self
    {
        return new self(
            userId:    $r->user()->id,
            currency:  $r->validated("currency"),
            items:     array_map(OrderItemData::fromArray(...), $r->validated("items")),
            promoCode: $r->validated("promo"),
        );
    }
}

// Использование - типизированный контракт между слоями
public function store(StoreOrderRequest $r, CreateOrderAction $action) {
    $order = $action->execute(CreateOrderData::fromRequest($r));
    return new OrderResource($order);
}

// spatie/laravel-data делает то же декларативно
class CreateOrderData extends Data {
    public function __construct(
        public int $userId,
        #[Rule("required|string|size:3")] public string $currency,
        #[DataCollectionOf(OrderItemData::class)] public array $items,
    ) {}
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'В чём разница между Service и Action классами? Когда что выбирать?',
                'answer' => 'Это **разные уровни декомпозиции** — не «правильный/неправильный» подход, а **разная гранулярность** под разный объём логики.

**Сравнение:**

| Признак | **Service** | **Action** |
|---|---|---|
| Сколько публичных методов | **Несколько** (`create`, `update`, `suspend`, `restore`) | **Один** (`execute` / `handle` / `__invoke`) |
| Что инкапсулирует | Набор связанных операций над сущностью | **Ровно одно** действие |
| Имя | `UserService` | `CreateUserAction`, `SuspendUserAction` |
| Зависимости в `__construct` | **Общие** для всех методов | **Только** для одного действия |
| Точка входа | Группа CRUD-операций | Конкретная операция |

**Когда выбирать Service:**

- Набор **простых CRUD-операций** над одной сущностью.
- Много **общего state / зависимостей** между методами.
- Точка входа в bounded context для не-DDD-проектов.

**Когда выбирать Action:**

- Операции имеют **разные зависимости** (одна нуждается в `Mailer`, другая — в `StripeClient`).
- **Сложная бизнес-логика** внутри одной операции (>30 строк).
- Нужно **переиспользовать** в Controller + Artisan Command + Job + Listener.

**Минусы Service:**

- Со временем разрастается до **God Object** на 30 методов.
- **Тесты тяжёлые** — приходится мокать всё, даже не используемое в данном тесте.
- **DI-конструктор раздут** — все зависимости на каждый метод.

**Минусы Action:**

- **Больше файлов** в проекте.
- Между связанными операциями приходится **прыгать по файлам**.

**Прагматичный подход:** **начинать с Service**, выделять **Action**, когда метод стал толстым (>30 строк) или появились свои зависимости. **В обоих случаях контроллер тонкий**: `validate → call → return resource`.',
                'code_example' => '<?php
// Service - связка CRUD на одной сущности
final class UserService
{
    public function __construct(
        private Mailer $mailer,
        private AuditLogger $audit,
    ) {}

    public function create(CreateUserData $data): User { /* ... */ }
    public function update(User $u, UpdateUserData $d): User { /* ... */ }
    public function suspend(User $u, string $reason): void { /* ... */ }
    public function restore(User $u): void { /* ... */ }
}

// Action - одна тяжёлая операция со своими зависимостями
final class ChargeFailedPaymentRetryAction
{
    public function __construct(
        private StripeClient $stripe,         // нужен только здесь
        private RetryPolicyResolver $policy,  // нужен только здесь
        private SlackNotifier $slack,
    ) {}

    public function execute(Payment $payment): PaymentResult
    {
        // 50 строк сложной логики ретраев
    }
}

// Один и тот же Action из разных входных точек:
// - HTTP: PaymentController::retry() → $action->execute($payment)
// - CLI:  RetryFailedPaymentsCommand::handle() → foreach ... $action->execute($p)
// - Job:  RetryPaymentJob::handle(ChargeFailedPaymentRetryAction $a) → $a->execute(...)',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое фасад Context (Laravel 11+) и зачем он нужен?',
                'answer' => '**`Illuminate\\Support\\Facades\\Context`** (появился в **Laravel 11**) — механизм хранения **метаданных в рамках текущего request/job**, которые:

1. **Автоматически добавляются ко всем log-записям** этого запроса.
2. **Автоматически передаются в диспатченные job-ы** через сериализацию.

**Решаемая проблема — observability:**

- Связать **логи разных слоёв** (controller → service → job → notification) одним `trace_id`.
- **Не таскать** trace_id руками через каждый параметр и конструктор.

**Как использовать:**

| Метод | Что делает |
|---|---|
| **`Context::add("trace_id", $id)`** | Добавить в context (видно в логах) |
| **`Context::addHidden("tenant_id", 7)`** | **НЕ** попадает в логи, **передаётся** в jobs |
| **`Context::get("trace_id")`** | Прочитать значение |
| **`Context::push("breadcrumbs", $event)`** | Добавить в массив (audit-trail) |
| **`Context::flush()`** | Очистить (Octane делает сам между запросами) |

**Под капотом:**

- Живёт как **singleton** сервиса в контейнере.
- В **Octane** слушатель `RequestReceived` вызывает `Context::flush()` между запросами — данные не утекают.
- При **`dispatch`** job-а **текущий снимок Context** сериализуется в payload и восстанавливается в воркере.

**Сравнение public vs hidden context:**

| | `Context::add` | `Context::addHidden` |
|---|---|---|
| Попадает в логи | **Да** | Нет |
| Передаётся в jobs | Да | **Да** |
| Применение | `trace_id`, `user_id` | `tenant_id`, auth state, секреты |

**Заменяет старый хак** с глобальным singleton + `Log::shareContext()` — теперь это **first-class citizen** в фреймворке.',
                'code_example' => '<?php
// Middleware - добавляем trace_id один раз
class AssignTraceId
{
    public function handle(Request $request, Closure $next)
    {
        Context::add("trace_id", $request->header("X-Trace-Id", (string) Str::uuid()));
        Context::add("user_id",  $request->user()?->id);

        return $next($request);
    }
}

// Где-то глубоко в коде
Log::info("Order created", ["order_id" => $order->id]);
// → лог уже содержит trace_id и user_id из Context

// Job, диспатченный в этом запросе
class ProcessOrder implements ShouldQueue
{
    public function handle()
    {
        // здесь Context::get("trace_id") вернёт ТОТ ЖЕ trace_id,
        // что был у HTTP-запроса, инициировавшего dispatch
        Log::info("Processing order"); // тоже с trace_id
    }
}

// Hidden context - в логи не попадает, в job - передаётся
Context::addHidden("tenant_id", $tenant->id);

// В Octane контекст изолирован между запросами - утечки не будет',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Pennant и зачем он нужен?',
                'answer' => '**Pennant** — официальный пакет Laravel для **feature flags** (`composer require laravel/pennant`).

**Какие задачи решает:**
1. **Trunk-based development** — вливать незавершённую фичу в `main` **за флагом**, не держа долгоживущих feature-веток.
2. **Постепенный rollout** — включить новую фичу **5% юзеров → 50% → всем**; откат **без редеплоя**.
3. **A/B-тесты** вариантов UI / алгоритма.
4. **Kill switch** — мгновенно отключить сбойную фичу при инциденте.
5. **Gating по сегменту** — «только premium», «только tenant X», «только определённый регион».

**Регистрация и проверка:**
- В `AppServiceProvider::boot()`:
  `Feature::define("new-checkout", fn (User $u) => ...)` — resolver возвращает **`bool`** или **строку** для variant-флагов.
- В коде: **`Feature::active("new-checkout")`** или `$user->features()->active(...)`.
- В Blade: **`@feature("new-checkout") ... @endfeature`**.

**Хранилища (драйверы):**
- **`array`** — in-memory, per-request, для тестов.
- **`database`** — persistent, простое и дешёвое (таблица `features`).
- **`redis`** — быстро, для высокой нагрузки.

**Полезное API:**
- **`Lottery::odds(1, 100)`** — стохастический rollout процентом.
- **`Feature::for($user)`** — явный scope.
- **`Feature::activate()`** / **`deactivate()`** — управление в тестах в `setUp`.
- **Variants** — больше двух состояний: `"control" / "blue" / "green"`.

**Альтернативы:**
- **`Gate`** — только bool, без rollout / variants / scopes.
- **SaaS**: LaunchDarkly, GrowthBook, Unleash — UI, аналитика, real-time targeting; платно.',
                'code_example' => '<?php
// composer require laravel/pennant

// AppServiceProvider::boot()
use Laravel\\Pennant\\Feature;
use Illuminate\\Support\\Lottery;

public function boot(): void
{
    // Простой bool-флаг с правилом
    Feature::define("new-checkout", fn (User $user) =>
        $user->isInternal() ||                    // всегда для своих
        $user->id % 10 === 0                       // и для 10% юзеров (стабильно по id)
    );

    // Variant-флаг (несколько вариантов)
    Feature::define("homepage", fn (User $user) =>
        Lottery::odds(1, 3)
            ->winner(fn () => "blue")
            ->loser(fn () => "control")
            ->choose()
    );

    // По tenant вместо юзера
    Feature::define("instant-search", fn (Team $team) =>
        in_array($team->plan, ["pro", "enterprise"])
    );
}

// Использование
if (Feature::active("new-checkout")) {
    return view("checkout.v2");
}

// Per-user
if ($user->features()->active("new-checkout")) { /* ... */ }

// Variant - получить текущий вариант
$variant = Feature::value("homepage"); // "blue" | "control"

// В Blade
@feature("new-checkout")
    <NewCheckout />
@else
    <OldCheckout />
@endfeature

// Принудительно для не-юзерного scope
Feature::for($team)->active("instant-search");

// В тестах
public function test_new_checkout(): void
{
    Feature::activate("new-checkout"); // глобально
    // ...
    Feature::deactivate("new-checkout");
}

// Очистка кеша флагов
Feature::flushCache();
Feature::for($user)->forget("new-checkout");',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Contracts в Laravel и чем они отличаются от Facades?',
                'answer' => '**Contracts** — набор **интерфейсов** в неймспейсе **`Illuminate\\Contracts`**, описывающих основные сервисы фреймворка.

**Главные контракты:**
- `Cache\\Repository` — кэш.
- `Queue\\Queue` — очереди.
- `Mail\\Mailer` — почта.
- `Filesystem\\Filesystem` — файлы.
- `Auth\\Guard` — аутентификация.
- `Events\\Dispatcher`, `Hashing\\Hasher`, `Bus\\Dispatcher`, …

**Contracts vs Facades:**

| | **Facade** | **Contract** |
|---|---|---|
| Стиль | `Cache::put(...)` — **статика** | `__construct(Repository $cache)` — **DI** |
| Краткость | **выигрывает** в простом коде | требует объявить параметр |
| Видимость зависимости | **скрытая** | **явная** в сигнатуре |
| Подмена в тестах | `Cache::shouldReceive(...)` | **`$this->instance(Repository::class, $mock)`** |
| Завязка кода на Laravel | **жёсткая** | **слабая** (можно использовать пакет вне Laravel) |
| Что под капотом | proxy к биндингу из контейнера | сам биндинг |

**Когда что брать:**
- В **контроллерах / простых сценариях** — фасад, **короче**.
- В **сервисах / Action-классах / пакетах** — **контракт через DI**: явные зависимости, легко мокать.
- В **переиспользуемых библиотеках** — только контракты, чтобы не тянуть `Illuminate\\Support\\Facades` за собой.

**Подвох:** один и тот же сервис, по сути, **один и тот же объект** — фасад `Cache` и контракт `Repository` через `app(Repository::class)` вернут **один singleton**. Выбор — про **стиль написания и тестируемость**, не про производительность.',
                'code_example' => '<?php
// Facade - кратко, статика
use Illuminate\\Support\\Facades\\Cache;
Cache::put("k", "v", 60);

// Contract - явная зависимость через DI
use Illuminate\\Contracts\\Cache\\Repository as CacheContract;

class ReportService {
    public function __construct(
        private CacheContract $cache,        // тот же объект, что за фасадом Cache
        private \\Illuminate\\Contracts\\Mail\\Mailer $mailer,
    ) {}

    public function generate(): void {
        $this->cache->put("last_report_at", now(), 3600);
    }
}

// В тесте подмена тривиальна
$this->instance(CacheContract::class, new InMemoryCache());',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Sail и зачем он нужен, если уже есть Docker?',
                'answer' => '**Laravel Sail** — официальная **CLI-обёртка** над **`docker compose`** с готовым `docker-compose.yml` для типового **dev-стека**.

**Что в коробке:**
- **PHP** (нужной версии), **Node**, **Composer**.
- **MySQL / PostgreSQL / MariaDB** на выбор.
- **Redis**, **MeiliSearch** / **Typesense**.
- **Mailpit** (бывш. MailHog) — локальный SMTP-вьювер.
- **Selenium** для **Dusk**-тестов.

**Зачем нужно, если уже есть Docker:**
- **Готовый compose-файл** — не нужно писать вручную и держать актуальным.
- **Проксирование команд**: `sail artisan migrate`, `sail composer require`, `sail npm i` запускаются **в контейнере** — на хосте не нужны PHP/Node нужных версий.
- **Унифицированный onboarding** — у всех команды одинаковые, разные ОС работают идентично.
- Утилиты типа `sail mysql`, `sail shell`, `sail dusk` — короче, чем `docker compose exec ...`.

**Когда брать:**
- Стандартное **dev-окружение** для команды (особенно с Mac/Windows-разработчиками).
- Быстрый старт на новой машине.
- Onboarding джунов.

**Когда НЕ Sail:**
- **На проде** — там FPM/Octane + nginx, kubernetes, продакшен-стек, не dev-compose.
- Если в команде уже есть свой выстроенный compose / Devcontainer / DDEV — Sail не нужен.
- Если хост-окружение PHP/Node уже настроено и устраивает — `php artisan serve` быстрее.

**Установка:** `composer require laravel/sail --dev` + `php artisan sail:install`. Часто заводят алиас `alias sail="bash vendor/bin/sail"`, чтобы не писать длинный путь.',
                'code_example' => '# Установка
composer require laravel/sail --dev
php artisan sail:install   # выбрать сервисы интерактивно

# Запуск/остановка
./vendor/bin/sail up -d
./vendor/bin/sail down

# Алиас для удобства - в ~/.zshrc или ~/.bashrc
alias sail="bash vendor/bin/sail"

# Привычные команды через sail
sail artisan migrate
sail artisan tinker
sail composer require spatie/laravel-permission
sail npm run dev
sail test
sail mysql               # клиент в контейнер БД
sail shell               # bash в контейнер app

# Опубликовать docker-compose.yml для правок
sail artisan sail:publish',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Pint и чем он отличается от php-cs-fixer?',
                'answer' => '**Laravel Pint** — официальный **фиксер стиля кода** для Laravel **поверх `php-cs-fixer`**. Не свой движок, а **обёртка с пресетами и удобным CLI**.

**Что в коробке:**
- **Пресеты**: `laravel` (дефолт), `psr12`, `per` (PER-CS), `symfony`.
- **Нулевая конфигурация** — достаточно `vendor/bin/pint` без аргументов.
- Удобный **CLI поверх PHP-CS-Fixer**:
  - **`--test`** — dry-run, ничего не пишет, валит CI ненулевым кодом возврата.
  - **`--dirty`** — только файлы, изменённые в git (быстро для pre-commit).
  - **`--bail`** — упасть на **первой** проблеме.
  - **`-v`** — показать применённые правила по файлам.

**Pint vs `php-cs-fixer`:**

| Аспект | **Pint** | **`php-cs-fixer`** |
|---|---|---|
| Движок | тот же `friendsofphp/php-cs-fixer` | сам по себе |
| Конфиг | **`pint.json`** в корне | `.php-cs-fixer.php` (PHP-объект) |
| Дефолтный пресет | **`laravel`** | `@PSR12` / надо настраивать |
| Идиоматика | оптимизирован под Laravel-кодстайл | универсален |
| CLI | `--test` / `--dirty` / `--bail` сразу | через флаги fixer-а |

**Кастомизация:** `pint.json` в корне — указывают `preset`, расширяют `rules`, добавляют `exclude` для генерируемых файлов (миграции, IDE-helper).

**Где использовать:**
- **`pre-commit`** хуком (через Husky / Captain Hook) — на коммитах.
- **CI** через `pint --test` — фейл, если код не отформатирован.
- **Локально** руками после большой правки.

**Установка:** ставится **по умолчанию** в новых Laravel 9+. Для старых проектов — `composer require laravel/pint --dev`.',
                'code_example' => '# Запустить fixer
vendor/bin/pint

# Только проверка (для CI - вернёт !=0 при ошибках)
vendor/bin/pint --test

# Только файлы, изменённые в git
vendor/bin/pint --dirty

# Конкретный путь
vendor/bin/pint app/Models

# pint.json в корне проекта
{
    "preset": "laravel",
    "rules": {
        "simplified_null_return": true,
        "no_unused_imports": true,
        "ordered_imports": { "sort_algorithm": "alpha" }
    },
    "exclude": ["database/migrations"]
}',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Cashier и какие провайдеры он поддерживает?',
                'answer' => '**Cashier** — официальный пакет для **подписочного биллинга**. Закрывает рутину работы с платёжной системой через выразительный API на модели `User`.

**Две независимые версии (под разные платёжки):**

| Версия | Пакет | Назначение |
|---|---|---|
| **Cashier Stripe** | `laravel/cashier` | для **Stripe** |
| **Cashier Paddle** | `laravel/cashier-paddle` | для **Paddle** (Merchant of Record) |

Раньше существовали Cashier Mollie / Braintree — **deprecated** / community-maintained.

**Что покрывает:**
- **Подписки** и **тарифные планы** (price IDs).
- **Trial-периоды** (с/без payment method).
- **Купоны** и промо-коды.
- **Single-charge** платежи (`charge`).
- **Инвойсы** — генерация, выгрузка PDF.
- **Webhooks** — готовый контроллер обрабатывает `invoice.paid`, `customer.subscription.deleted`, `customer.subscription.updated` и сам обновляет статус в БД.
- **Payment intents / 3DS** — прокси-роуты для подтверждения карт.

**Как подключается:**
1. **`composer require laravel/cashier`**.
2. **`php artisan vendor:publish --tag="cashier-migrations"`** + `migrate` — добавляет колонки `stripe_id`, `stripe_status` к `users` и таблицы `subscriptions`, `subscription_items`.
3. На модели **`User`** подключают трейт **`Billable`**.

**Типовой API:**
- `$user->newSubscription("default", "price_monthly")->trialDays(14)->create($pm)`.
- `$user->subscribed("default")` / `$user->subscription("default")->onTrial()`.
- `$user->subscription("default")->swap("price_pro")` — смена плана.
- `$user->subscription("default")->cancel()` / `cancelNow()`.

**Подвох:** Stripe и Paddle модели **разные** (например, у Paddle MoR-сценарий — налоги/возвраты включены), поэтому код одного **не переносится** на другой без правок.',
                'code_example' => '<?php
// composer require laravel/cashier
// php artisan vendor:publish --tag="cashier-migrations"
// php artisan migrate

use Laravel\\Cashier\\Billable;

class User extends Authenticatable {
    use Billable;
}

// Создать подписку
$user->newSubscription("default", "price_monthly_basic")
    ->trialDays(14)
    ->create($paymentMethodId);

// Проверки
$user->subscribed("default");                 // активна ли
$user->subscription("default")->onTrial();    // в trial-периоде
$user->subscribedToPrice("price_pro", "default");

// Смена тарифа
$user->subscription("default")->swap("price_yearly_pro");

// Отмена
$user->subscription("default")->cancel();     // до конца оплаченного периода
$user->subscription("default")->cancelNow();  // сразу

// Инвойсы
$user->invoices()->each(fn($i) => /* ... */);
return $user->downloadInvoice($invoiceId, ["vendor" => "MyApp"]);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Folio и в чём его особенность маршрутизации?',
                'answer' => '**Folio** — пакет **page-based routing** в духе **Next.js**: маршрут создаётся **самим фактом существования Blade-файла** в каталоге `resources/views/pages/`. Никаких записей в `routes/web.php`.

**Соглашения по именам файлов:**

| Путь к файлу | URL |
|---|---|
| `pages/index.blade.php` | **`GET /`** |
| `pages/about.blade.php` | `GET /about` |
| `pages/users/[id].blade.php` | `GET /users/{id}` |
| `pages/users/[id]/edit.blade.php` | `GET /users/{id}/edit` |
| `pages/blog/[...slug].blade.php` | catch-all `GET /blog/{slug...}` |
| `pages/users/[User].blade.php` | Route Model Binding по типу — переменная `$User` |

**Метаданные внутри страницы** через фронт-маттер-директивы:
- `name("users.show")` — имя роута для `route(...)`.
- `middleware(["auth", "verified"])` — middleware.
- `withTrashed()` — soft-deleted в binding.
- `render(fn(View $v) => ...)` — кастомный рендер.

**Когда брать:**
- **Контентные сайты**, **лендинги**, **документация** — много простых страниц.
- **Прототипы / MVP** — добавил Blade-файл, страница уже доступна.
- Минимизация **бойлерплейта** routes-файла.

**Когда НЕ Folio:**
- Сложный API / REST CRUD — там нужны `Route::apiResource`, контроллеры, FormRequest.
- Логика далеко выходит за «отрендерить шаблон» — лучше явный контроллер.

**Сравнение с `routes/web.php`:**
- **Folio**: маршрут привязан к **файлу** на диске — структура каталогов = карта сайта.
- **`routes/web.php`**: централизованный реестр — удобно искать «куда ведёт URL».',
                'code_example' => '<?php
// composer require laravel/folio
// php artisan folio:install

// resources/views/pages/index.blade.php → GET /
// resources/views/pages/about.blade.php → GET /about
// resources/views/pages/users/[id].blade.php → GET /users/{id}
// resources/views/pages/users/[id]/edit.blade.php → GET /users/{id}/edit
// resources/views/pages/blog/[...slug].blade.php → GET /blog/{slug?...} catch-all

// pages/users/[User].blade.php — Route Model Binding по имени класса
?>
@php
    use function Laravel\\Folio\\{name, middleware};

    name("users.show");
    middleware(["auth", "verified"]);
@endphp

<x-layout>
    <h1>{{ $User->name }}</h1>
    <p>{{ $User->email }}</p>
</x-layout>',
                'code_language' => 'blade',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Envoy и для чего он применяется?',
                'answer' => '**Laravel Envoy** — простой **раннер задач на удалённых серверах через SSH**. Задачи описываются в **`Envoy.blade.php`** в корне проекта с Blade-синтаксисом.

**Базовые директивы:**
- **`@servers([...])`** — список хостов (имя → SSH-цель).
- **`@task("deploy", ["on" => "production"]) ... @endtask`** — команды для выбранных хостов.
- **`@story("zero-downtime") ... @endstory`** — **последовательность** нескольких task-ов.
- **`@before / @after / @error / @success`** — хуки до/после/при ошибке.
- **`@slack($webhook, "#deploys", $msg)`** — нотификация в Slack.

**Запуск:**
- **`envoy run deploy`** — выполнит задачу `deploy`.
- **`envoy run deploy --branch=staging`** — передача аргументов.
- **`envoy run zero-downtime`** — запустит story из task-ов.

**Где применяется:**
- **Деплой** на classic-VPS: `git pull`, `composer install --no-dev`, `migrate --force`, `php artisan optimize`, `php artisan queue:restart`.
- **Миграции** на проде по требованию.
- **Обслуживание** — ротация логов, прогрев кеша, рестарт воркеров.

**С чем сравним:**
- Упрощённый **Capistrano** (Ruby) / **Deployer** (PHP-альтернатива с zero-downtime из коробки).
- **Не делает** zero-downtime сам: для symlink-swap пишут вручную или берут Deployer.

**Когда НЕ Envoy:**
- **Kubernetes / Lambda / Vapor** — деплой идёт через `kubectl apply` / CI-pipelines, не SSH.
- Многосервисный микросервисный стек — Envoy на каждый сервис превратится в зоопарк, проще CI/CD pipelines (GitLab/GitHub Actions).

**Когда хорошо:** classic Linux VPS, моноинстанс или 2-3 сервера, нужна простая автоматизация без оркестратора.',
                'code_example' => '# composer global require laravel/envoy

# Envoy.blade.php в корне проекта
@servers([\'web\' => [\'deploy@1.2.3.4\', \'deploy@5.6.7.8\']])

@story(\'deploy\')
    pull
    composer
    migrate
    restart-php
@endstory

@task(\'pull\', [\'on\' => \'web\', \'parallel\' => true])
    cd /var/www/app
    git pull origin main
@endtask

@task(\'composer\', [\'on\' => \'web\'])
    cd /var/www/app && composer install --no-dev --optimize-autoloader
@endtask

@task(\'migrate\', [\'on\' => \'web\'])
    cd /var/www/app && php artisan migrate --force
@endtask

@task(\'restart-php\', [\'on\' => \'web\', \'parallel\' => true])
    sudo systemctl reload php8.3-fpm
@endtask

# Запуск
# envoy run deploy
# envoy run migrate',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Prompts и где он используется?',
                'answer' => '**Laravel Prompts** — пакет **красивых интерактивных форм для CLI**. Заменяет устаревшие `$this->ask()` / `$this->choice()` в artisan-командах на современный UX.

**Доступные prompts:**
- **`text()`** — обычная строка.
- **`password()`** — ввод без отображения.
- **`confirm()`** — Yes/No.
- **`select()`** — выбор из списка.
- **`multiselect()`** — несколько из списка (пробел переключает).
- **`search()`** — поиск с фильтрацией по мере ввода.
- **`suggest()`** — text + автодополнение.
- **`spin()`** — long-running задача со **спиннером**.
- **`progress()`** — **progress bar** для итераций.
- **`form()`** — **мультишаговая** форма с back/next.

**Возможности:**
- **`validate: fn($v) => ...`** — валидация ввода.
- **`transform: fn($v) => trim($v)`** — преобразование результата.
- **`default:`** — дефолт, кнопка Enter применяет.
- **`required: true`** — нельзя оставить пустым.
- **`hint:`** — подсказка под вопросом.

**Где используется:**
- **`make:*` команды** Laravel 10.17+ — сам фреймворк теперь спрашивает «Use git?» и т.п. красивее.
- **Инсталлеры пакетов** — Spatie / Filament всё активнее переходят на Prompts.
- **Свои artisan-команды** — заменить `$this->ask("Имя?")` на **`text("Имя?")`**.

**Подвох — не-TTY окружения:**
- CI, Docker **без `-it`**, фоновый процесс — нет терминала.
- Prompts **автоматически фолбэчатся** на простые ASCII-prompts (или используют дефолты).
- В CI-скриптах все вопросы лучше пропускать через флаги команды (`--name=...`) или **`Prompts::fallbackWhen(fn() => app()->runningInCi())`**.

**Установка:** уже **в коробке Laravel 10.17+**. Для старых проектов — `composer require laravel/prompts`.',
                'code_example' => '<?php
use function Laravel\\Prompts\\{text, password, select, confirm, multiselect, search, spin, progress};

// Простой ввод с валидацией
$name = text(
    label: "Как тебя зовут?",
    placeholder: "Иван",
    required: true,
    validate: fn ($v) => strlen($v) < 2 ? "Минимум 2 символа" : null,
);

// Пароль
$pwd = password(label: "Пароль", required: true);

// Выбор из списка
$db = select(
    label: "Какую БД использовать?",
    options: ["mysql" => "MySQL", "pgsql" => "PostgreSQL", "sqlite" => "SQLite"],
    default: "mysql",
);

// Подтверждение
if (! confirm("Точно удалить?", default: false)) {
    return;
}

// Мульти-выбор
$features = multiselect(
    label: "Какие пакеты ставим?",
    options: ["pint", "larastan", "telescope", "horizon"],
);

// Поиск с автоподсказкой
$user = search(
    label: "Найди юзера",
    options: fn (string $q) => User::where("name", "like", "%{$q}%")->pluck("name", "id")->all(),
);

// Long-running task
$users = spin(fn () => User::all(), "Загружаем пользователей...");

// Progress bar
progress(label: "Импорт", steps: $rows, callback: fn ($row) => importRow($row));',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel IDE Helper и зачем он нужен?',
                'answer' => '**IDE Helper** (`barryvdh/laravel-ide-helper`) — **dev-пакет**, генерирующий **PHPDoc-метаинформацию** для **фасадов**, **моделей** и **контейнерных биндингов**.

**Зачем нужен:**
- PhpStorm / VS Code / **PHPStan** видят **`Cache::get(...)`** как «undefined method» — это магия фасада через `__callStatic`.
- Eloquent-модель имеет магические **`where{Field}`**, **`findOrFail`** и атрибуты из БД — статанализ их **не видит**.
- IDE Helper **генерирует PHPDoc-обёртки**, по которым IDE и статический анализ всё распознают.

**Три ключевые команды:**

| Команда | Что делает |
|---|---|
| **`ide-helper:generate`** | создаёт `_ide_helper.php` — `@method` для всех фасадов (`Cache`, `Auth`, `Route` …) с реальными сигнатурами |
| **`ide-helper:models`** | добавляет `@property` / `@method` **в сами файлы моделей** (или в `_ide_helper_models.php` с флагом `-N`) — на основе схемы БД |
| **`ide-helper:meta`** | создаёт `.phpstorm.meta.php` для **PhpStorm** — он понимает `app()->make(X::class)`, `resolve(...)`, контекстные биндинги |

**Где запускать:**
- Локально вручную после изменения миграций / биндингов.
- **`composer.json` → `scripts → post-update-cmd`** — автогенерация после `composer install/update`:
  ```
  "post-update-cmd": [
      "@php artisan ide-helper:generate --ansi",
      "@php artisan ide-helper:meta --ansi"
  ]
  ```
- **`.gitignore`** для `_ide_helper*.php` — не коммитят, регенерируется.

**Подвох:** **`ide-helper:models -W`** (write to model files) **меняет файлы моделей** — конфликтует с code-review. Команды обычно держат `-N` (separate file), чтобы не модифицировать сами модели.',
                'code_example' => '# Установка
composer require --dev barryvdh/laravel-ide-helper

# Команды
php artisan ide-helper:generate     # фасады
php artisan ide-helper:models -N    # модели (в отдельный файл, без правки)
php artisan ide-helper:models -W    # модели (записывает PHPDoc в файл модели)
php artisan ide-helper:meta         # PhpStorm meta

# composer.json - автогенерация после composer update
{
  "scripts": {
    "post-update-cmd": [
      "@php artisan ide-helper:generate",
      "@php artisan ide-helper:meta"
    ]
  }
}

# .gitignore - сгенерированные файлы в git не нужны
_ide_helper.php
_ide_helper_models.php
.phpstorm.meta.php',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Nova и чем она отличается от бесплатных админок (Filament, Backpack)?',
                'answer' => '**Laravel Nova** — **официальная платная админка** для Laravel. Resource-классы описывают CRUD-страницы, фильтры, **lenses** (saved views), **actions** (групповые операции), **metrics** (KPI-карточки на дашборде).

**Что есть в Nova:**
- **Resource** — конфиг страниц для модели (`fields()`, `filters()`, `actions()`).
- **Lenses** — кастомные представления (e.g., «только активные подписчики этого месяца»).
- **Actions** — bulk-операции на выбранных строках с UI-формой.
- **Metrics** — Value/Trend/Partition/Progress карточки.
- Стек — **Vue.js** + Laravel API.

**Сравнение с альтернативами:**

| | **Nova** | **Filament** | **Backpack** |
|---|---|---|---|
| Цена | **`$199`** / сайт (бессрочно) | **бесплатно** | **бесплатно** (плагины Pro платные) |
| Стек фронта | Vue.js | **TALL** (Tailwind + Alpine + Livewire) | CoreUI + Blade |
| Зрелость | официальная, стабильно | очень активная экосистема, **самый растущий** | самый старый, **много плагинов** |
| UX | классический Vue-SPA | **современный**, Livewire-реактивность | классический |
| Лучшая интеграция с | Scout, Horizon, Pulse, Sanctum | Spatie-стек, любые пакеты | community-плагины |

**Когда что брать:**
- **Nova** — нужна **официальная поддержка**, тесная интеграция со Scout / Horizon / Pulse, корпоратив готов заплатить за уверенность.
- **Filament** — стартап / pet-project с современным стеком, активной комьюнити-экосистемой плагинов, **быстрый старт**.
- **Backpack** — нужна **зрелая функциональность** «из коробки» (импорт/экспорт, медиа-менеджер, мощный CRUD), привыкшие к Blade.

**Вне Laravel-мира:** **`django-admin`** (Python), **`rails_admin`** (Ruby), **Strapi** (headless CMS) — концептуально похожие решения.',
                'code_example' => '<?php
// Nova Resource - app/Nova/User.php
use Laravel\\Nova\\Resource;
use Laravel\\Nova\\Fields\\{ID, Text, Email, Password, BelongsTo, HasMany};

class User extends Resource {
    public static $model = \\App\\Models\\User::class;
    public static $title = "name";
    public static $search = ["id", "name", "email"];

    public function fields(NovaRequest $request): array {
        return [
            ID::make()->sortable(),
            Text::make("Name")->sortable()->rules("required", "max:255"),
            Email::make("Email")->sortable()->rules("required", "email", "unique:users,email,{{resourceId}}"),
            Password::make("Password")->onlyOnForms()->creationRules("required", "min:8"),
            BelongsTo::make("Team"),
            HasMany::make("Posts"),
        ];
    }

    public function actions(NovaRequest $request): array {
        return [new \\App\\Nova\\Actions\\SuspendUser];
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Какие основные helper-функции Laravel часто используются?',
                'answer' => 'Глобальные функции, доступные везде:

**URL и ассеты:**
- `route(\'users.show\', $id)` — URL по имени маршрута.
- `url(\'/foo\')` — абсолютный URL от `APP_URL`.
- `asset(\'css/app.css\')` — URL статики из `public/`.

**Формы и валидация:**
- `old(\'email\')` — старое значение поля после ошибки валидации.
- `csrf_token()` — CSRF-токен текущей сессии.

**Конфиг и окружение:**
- `config(\'app.name\')` — значение из `config/`.
- `env(\'KEY\')` — переменная окружения. Читать **только внутри `config/`**, иначе после `config:cache` вернёт `null`/default.

**Сервисы:**
- `auth()`, `request()`, `session()`, `redirect()`, `back()`, `abort()`, `response()`, `view()`.

**Утилиты:**
- `now()`, `today()` — `Carbon`.
- `collect([...])` — `Collection`.
- `str(\'...\')` — `Stringable` (цепочки методов над строкой).',
                'code_example' => '// в Blade
<a href="{{ route(\'users.show\', $user) }}">Профиль</a>
<link rel="stylesheet" href="{{ asset(\'css/app.css\') }}">
<input name="email" value="{{ old(\'email\') }}">

// в коде
$url    = url(\'/login\');             // https://app.test/login
$name   = config(\'app.name\');        // "MyApp"
$today  = now();                     // Carbon
$user   = auth()->user();
$email  = request(\'email\');
session([\'cart\' => $items]);
return redirect()->route(\'home\');
abort_if(! $user, 403);',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Какие способы вернуть ответ из контроллера в Laravel?',
                'answer' => 'Что можно вернуть из метода контроллера:

- `view(\'users.show\', [\'user\' => $user])` — HTML-страница из Blade.
- `redirect(\'/login\')` или `redirect()->route(\'home\')` — редирект (с `->with()` или `->withErrors()`).
- `back()` — назад на предыдущую страницу.
- `response()->json([\'ok\' => true], 201)` — JSON с HTTP-кодом.
- `response()->download($path)` — файл на скачивание.
- `response(\'Hello\', 200)->header(\'X-Custom\', \'v\')` — произвольный текст с заголовками.
- `abort(404, \'Не найдено\')` — прервать с ошибкой.

**Авто-сериализация:** если вернуть массив или Eloquent-модель — Laravel сам отдаст JSON.',
                'code_example' => 'public function show(int $id)
{
    $user = User::findOrFail($id);

    return view(\'users.show\', [\'user\' => $user]);     // HTML
    return response()->json([\'user\' => $user], 200);   // JSON
    return $user;                                      // тоже JSON (auto)
    return redirect()->route(\'users.index\')
        ->with(\'success\', \'Готово\');                  // redirect + flash
    return response()->download(storage_path("invoices/{$id}.pdf"));
    return back()->withInput()->withErrors([\'email\' => \'занят\']);
    abort(404, \'Не найдено\');
}',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'laravel.misc',
            ],
        ];
    }
}
