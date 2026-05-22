<?php

namespace Database\Seeders\Data\Categories\Laravel;

class ServiceContainer
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Laravel',
                'question' => 'Что такое Service Container (IoC контейнер) в Laravel?',
                'answer' => '**Service Container** (он же **IoC-контейнер**, **DI-контейнер**) — механизм управления **зависимостями** и их **инъекции**. Это **«склад» объектов**, который сам умеет создавать и подставлять нужное.

**Что делает контейнер:**
- **Разрешает** граф зависимостей через **Reflection** type-hints в конструкторах.
- **Хранит биндинги** «интерфейс → реализация» (`bind`, `singleton`, `scoped`).
- **Кеширует** объекты по правилам жизненного цикла.
- Подставляет зависимости **автоматически** в контроллеры, middleware, jobs, команды.

**Inversion of Control (IoC) — что инвертируется:**
- В традиционном коде класс **сам** создаёт зависимости через `new`.
- При IoC класс **получает** их извне — контейнер контролирует создание.
- Результат: код **не привязан** к конкретным реализациям, легко мокается.

**Где живёт в Laravel:**
- Сам экземпляр — **`$app`** в `bootstrap/app.php`, типа `Illuminate\\Foundation\\Application`.
- Доступ — через `app()`, `resolve()`, фасад `App`.
- Регистрация биндингов — в **`ServiceProvider::register()`** (а **`boot()`** — после регистрации всех провайдеров).

**Возможности (помимо basic bind):**
- **`singleton`** — один объект на весь жизненный цикл приложения.
- **`scoped`** — один на request (важно для Octane).
- **`extend`** — обернуть резолвнутый объект декоратором.
- **`when`** → **`needs`** → **`give`** — **contextual** биндинг для конкретного потребителя.

**Главная польза:** один контроллер сегодня работает со Stripe, завтра — с PayPal, в тесте — с FakeGateway, **без правки самого контроллера**.',
                'code_example' => '// Bind
app()->bind(PaymentInterface::class, StripePayment::class);

// Resolve
$payment = app(PaymentInterface::class);

// Через DI в контроллере
public function __construct(PaymentInterface $payment) {
    $this->payment = $payment;
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.service_container',
            ],
            [
                'category' => 'Laravel',
                'question' => 'В чём разница между bind, singleton, scoped и instance в Service Container?',
                'answer' => '**Четыре способа** регистрации сервиса в контейнере — отличаются **жизненным циклом** объекта.

**Сравнение:**

| Метод | Когда создаётся объект | Жизненный цикл |
|---|---|---|
| **`bind`** | **Каждый** `resolve` | Новый объект на каждый вызов |
| **`singleton`** | При первом `resolve`, переиспользуется | На весь жизненный цикл приложения |
| **`scoped`** | При первом `resolve`, **сбрасывается между запросами** | На один request/job |
| **`instance`** | **Уже создан** — просто регистрируем | Как singleton (заранее построенный) |

**Что значит «жизненный цикл приложения»:**

- В **обычном PHP-FPM** — один HTTP-запрос (новый процесс на каждый запрос).
- В **Octane / RoadRunner / Swoole / FrankenPHP** — **весь воркер**, тысячи запросов.

**Почему `scoped` появился (Laravel 9+):**

- В Octane `singleton` живёт **между запросами** → request-зависимый state «протекает» к следующему юзеру.
- **`scoped`** — это singleton-семантика, но Octane **сам сбрасывает** scoped-биндинги между запросами.

**Когда что брать:**

| Сценарий | Метод |
|---|---|
| Без shared state, дешёво создавать | **`bind`** |
| Тяжёлая инициализация (HTTP-клиент, parser) | **`singleton`** |
| **Request-зависимый** state (текущий tenant, trace_id), Octane | **`scoped`** |
| Mock в тестах | **`instance`** (`$this->instance(Cls::class, $mock)`) |

**Все четыре метода возвращают `Container` для цепочки**, регистрация обычно в **`AppServiceProvider::register()`**.',
                'code_example' => '<?php
// bind - новый объект на каждый resolve
\$this->app->bind(Foo::class, fn () => new Foo());

// singleton - один объект на жизненный цикл (в Octane = весь воркер!)
\$this->app->singleton(StripeClient::class, fn (\$app) =>
    new StripeClient(config("services.stripe.key"))
);

// scoped - singleton с автосбросом между запросами (для Octane)
\$this->app->scoped(CurrentTenant::class, fn (\$app) =>
    new CurrentTenant(\$app->make("request")->header("X-Tenant-Id"))
);

// instance - регистрируем уже созданный объект
\$mock = new FakeMailer();
\$this->app->instance(Mailer::class, \$mock);

// Получение - одинаково для всех
\$foo = app(Foo::class);
\$stripe = app(StripeClient::class);  // тот же объект на N вызовов
\$tenant = app(CurrentTenant::class); // в Octane — свежий на каждый request',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.service_container',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое contextual binding и приведите кейс из реального проекта.',
                'answer' => '**Contextual binding** — механизм контейнера, позволяющий **внедрять разные реализации одного интерфейса** в зависимости от **потребляющего класса**.

**API:**

```php
$this->app->when(Consumer::class)
    ->needs(Dependency::class)
    ->give(fn ($app) => new ConcreteImpl());
```

**Типичные кейсы:**

| Сценарий | Решение |
|---|---|
| `PhotoController` хочет `LocalFilesystem`, `VideoController` — `S3` | `when(...)->needs(Filesystem::class)->give(...)` |
| Один `PaymentProcessor` для `subscription`-флоу, другой для `one-time` | `when(SubscriptionController::class)->needs(PaymentProcessor::class)->give(...)` |
| **Параметр-примитив** в конструкторе (`$apiKey`) | `when(...)->needs("$apiKey")->give(env("...."))` |
| **Tag-based binding** — много реализаций под одним тегом | `$app->tag([ChatPolicy::class, EmailPolicy::class], "channels")` + `$app->tagged("channels")` |

**Альтернативы без contextual binding:**

- **Именованные интерфейсы** (`LocalFilesystem`, `S3Filesystem`) — больше классов, ломает principle of least surprise.
- **Конкретные классы в type-hints** — теряем абстракцию, нельзя подменить в тестах.
- **Передача через конструктор фабрикой** — больше boilerplate.

**Расширенные возможности:**

- **`giveTagged("channels")`** — отдать все сервисы с тегом.
- **`giveConfig("services.stripe.key")`** — внедрить значение из конфига.
- **`needs(\\$variableName)`** — для named-параметров примитивных типов.

**Подвох:** работает **только при резолве через контейнер** — если делать `new PhotoController(...)` напрямую, contextual binding **не сработает**. Это редко проблема (Laravel сам резолвит контроллеры/jobs/команды через DI).',
                'code_example' => '<?php
use Illuminate\\Contracts\\Filesystem\\Filesystem;

// AppServiceProvider::register()

// 1) Разные диски для разных контроллеров
\$this->app->when(PhotoController::class)
    ->needs(Filesystem::class)
    ->give(fn () => Storage::disk("local"));

\$this->app->when(VideoController::class)
    ->needs(Filesystem::class)
    ->give(fn () => Storage::disk("s3"));

// 2) Разный platform-key для разных Action
\$this->app->when(SubscriptionAction::class)
    ->needs("\$stripeKey")
    ->give(fn () => config("services.stripe.subscription_key"));

\$this->app->when(OneTimeChargeAction::class)
    ->needs("\$stripeKey")
    ->give(fn () => config("services.stripe.one_time_key"));

// 3) Tag-based — все policy-классы одним вызовом
\$this->app->bind(EmailPolicy::class);
\$this->app->bind(SmsPolicy::class);
\$this->app->bind(PushPolicy::class);
\$this->app->tag([EmailPolicy::class, SmsPolicy::class, PushPolicy::class], "channel-policies");

\$this->app->when(NotificationDispatcher::class)
    ->needs(NotificationPolicy::class)
    ->giveTagged("channel-policies");

// Использование - в контроллере ничего не меняется
class PhotoController
{
    public function __construct(private Filesystem \$disk) {}  // получит local
}

class VideoController
{
    public function __construct(private Filesystem \$disk) {}  // получит s3
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.service_container',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как обернуть сервис в Decorator через Service Container? ($app->extend)',
                'answer' => '**`$app->extend($abstract, Closure $callback)`** — метод контейнера, «перехватывающий» уже зарезолвленный экземпляр и заменяющий его на **обёртку**.

**Как работает:**

1. Контейнер строит **оригинальный объект** (по биндингу или авторезолву).
2. Передаёт его в **callback** вместе с самим контейнером.
3. То, что callback **вернёт**, становится **новой версией** сервиса в контейнере.

**Это идеальный механизм для паттерна Decorator** — без правки исходного класса (особенно полезно с **вендорными сервисами**, до которых нельзя дотянуться).

**Композиция нескольких `extend`:**

- Применяются **в порядке регистрации**, образуя стек декораторов:
- `Metrics(Logging(Original))` — последний `extend` оказывается **снаружи**.

**Типичное применение:**

- **Кеширование** вокруг репозитория (`CachedUserRepository`).
- **Логирование** вокруг HTTP-клиента.
- **Метрики / трейсинг** вокруг бизнес-сервиса.
- **Feature-flag** обёртки (toggle между старой и новой реализацией).
- **Retry-обёртки** вокруг flaky-сервиса.

**Альтернативы и когда они лучше:**

| Подход | Когда брать |
|---|---|
| **`$app->bind(Abstract, MyImpl)`** | Если **не нужна делегация** в оригинал |
| **`$app->when()->needs()->give()`** | Декорация только для **конкретного потребителя** |
| **`Pipeline`** | Пошаговая трансформация **значения**, не оборачивание инстанса |
| **`$app->extend()`** | Именно **оборачивание** инстанса, делегирующее вызовы в оригинал |

**Подвох с singleton:** если оригинал зарегистрирован как `singleton`, `extend` тоже даёт singleton — декоратор строится **один раз** при первом резолве.',
                'code_example' => '<?php
// AppServiceProvider::register()

// 1) Кеширующий слой над репозиторием
$this->app->extend(UserRepository::class, function ($repo, $app) {
    return new CachedUserRepository(
        inner: $repo,
        cache: $app->make("cache.store"),
        ttl:   300,
    );
});

// 2) Стек декораторов: Metrics(Logging(Original))
$this->app->extend(PaymentGateway::class, fn ($g) => new LoggingGateway($g));
$this->app->extend(PaymentGateway::class, fn ($g, $a) => new MetricsGateway($g, $a->make(StatsD::class)));

// 3) Декорирование сервиса из вендорного пакета
$this->app->extend(HttpClient::class, function ($client) {
    return new RetryingHttpClient($client, maxRetries: 3, backoff: [100, 500, 2000]);
});

// Использование - в потребителе ничего не меняется
class CheckoutService {
    public function __construct(private PaymentGateway $gateway) {}
    // получит весь стек декораторов
}

// 4) Декоратор только для конкретного потребителя (контекстный binding)
$this->app->when(AdminController::class)
    ->needs(UserRepository::class)
    ->give(fn ($app) => new AuditedUserRepository(
        $app->make(UserRepository::class),
        $app->make(AuditLogger::class),
    ));',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.service_container',
            ],
            [
                'category' => 'Laravel',
                'question' => 'В чём разница между app(Foo::class), resolve(Foo::class) и App::make(Foo::class)?',
                'answer' => '**Все три эквивалентны** — под капотом каждое в итоге зовёт **`Container::make()`** и возвращает один и тот же объект.

**Цепочка вызовов:**

| Конструкция | Что это |
|---|---|
| **`app($abstract)`** | helper-функция (`app()` без аргументов возвращает сам контейнер) |
| **`resolve($abstract)`** | helper, прямой алиас `app($abstract)` |
| **`App::make($abstract)`** | фасад `App` поверх того же контейнера |
| **`$this->app->make($abstract)`** | прямой вызов из ServiceProvider/класса |

**Разница — чисто стилистическая.** Выбирают по консистентности проекта.

**Чем каждый удобен:**
- **`app()`** — короче всего, удобен для **получения самого контейнера**:
  - `app()->bound(Foo::class)` — проверить, зарегистрирован ли.
  - `app()->isProduction()` — текущее окружение.
  - `app()->environment("local", "staging")` — мульти-проверка.
- **`resolve()`** — самое **«говорящее»** имя, читается как намерение.
- **`App::make()`** — единый стиль с другими фасадами в проекте.

**Передача параметров конструктора** (второй аргумент массивом):
- `app(ReportGenerator::class, ["type" => "weekly"])` — `type` подставится явно, остальные параметры резолвятся из контейнера.
- `App::makeWith(...)` — более **явная семантика** «make с параметрами».

**Главная идиома сообщества:**
- **`app()` / `resolve()`** — только для **коротких inline-резолвов** (фабрики, динамическое имя класса).
- **Type-hint в конструкторе** — для **постоянных зависимостей** (это основной и правильный способ). Использование `app()` внутри методов — **Service Locator**, скрытая зависимость.',
                'code_example' => '<?php
// Все эквивалентны
$repo1 = app(UserRepository::class);
$repo2 = resolve(UserRepository::class);
$repo3 = App::make(UserRepository::class);

// app() без аргументов - сам контейнер
if (app()->bound(UserRepository::class)) { /* ... */ }
if (app()->isProduction()) { /* ... */ }

// С параметрами конструктора
class ReportGenerator {
    public function __construct(public string $type, public Mailer $mailer) {}
}
$gen = app(ReportGenerator::class, ["type" => "weekly"]);
// $mailer резолвится из контейнера, type подставится явно

// makeWith - явная семантика "make с параметрами"
$gen = App::makeWith(ReportGenerator::class, ["type" => "weekly"]);

// Идиома "постоянная зависимость" - через type-hint, без app()
class UserController extends Controller {
    public function __construct(private UserRepository $repo) {}
    //                                  ^^^^^^^^^^^^^^^
    //                          контейнер сам подставит при резолве контроллера
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.service_container',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Service Container в Laravel простыми словами?',
                'answer' => '**Service Container** — большой реестр объектов и правил, **как их создавать**. Сердце **Dependency Injection** в Laravel.

Как работает:

- Когда нужен `OrderService`, контейнер **смотрит конструктор**, читает type-hint каждого параметра.
- **Рекурсивно** создаёт зависимости: `OrderRepo` → `DB-соединение` → конфиг → ...
- Собирает готовый объект и отдаёт его.

Вместо `new OrderService(new OrderRepo(new DB(...)))` ты просто пишешь **type-hint**, и контейнер собирает граф зависимостей сам.

**Как получить объект:**

- `app(OrderService::class)` или `resolve(OrderService::class)`.
- Просто **type-hint** в конструкторе контроллера, middleware, job, команды — Laravel внедрит автоматически.',
                'code_example' => 'class OrderRepo {}

class OrderService
{
    public function __construct(private OrderRepo $repo) {}
}

// Получить вручную - контейнер сам создаст OrderRepo и подставит
$service = app(OrderService::class);

// Type-hint в контроллере - то же самое, без app()
class OrderController extends Controller
{
    public function __construct(private OrderService $service) {}

    public function index() {
        // $this->service уже готов
    }
}',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'laravel.service_container',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что делает app()->bind() и app()->singleton()?',
                'answer' => 'Оба метода регистрируют **правило**: «когда попросят `Abstract`, создавай `Concrete`».

Разница — **в жизненном цикле**:

- **`bind(Abstract::class, Concrete::class)`** — каждый вызов `app(Abstract::class)` создаёт **новый** объект.
- **`singleton(Abstract::class, ...)`** — объект создаётся **один раз** и переиспользуется при последующих запросах из контейнера.

Что значит «жизненный цикл»:

- В **обычном FPM** — один HTTP-запрос (новый процесс на каждый запрос).
- В **Octane/RoadRunner/Swoole** — на **весь воркер** (между запросами!). Это опасно: singleton с request-зависимым состоянием может «протечь» в следующий запрос. Для такого случая есть **`scoped()`** — singleton, который Octane сбрасывает между запросами.

Регистрируется обычно в **`AppServiceProvider::register()`**.',
                'code_example' => '// в AppServiceProvider::register()
$this->app->bind(PaymentGateway::class, StripeGateway::class);
$this->app->singleton(Logger::class, fn() => new FileLogger("/var/log/app.log"));',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.service_container',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое автоматический resolve в Laravel?',
                'answer' => '**Автоматический resolve** (autowiring) — контейнер сам разбирается, как создать класс, **читая type-hints**.

Как работает:

1. Просим `app(OrderService::class)`.
2. Контейнер через **Reflection** смотрит конструктор: `__construct(OrderRepo $repo)`.
3. **Рекурсивно** создаёт `OrderRepo` (если у него тоже есть зависимости — собирает их тем же способом).
4. Передаёт всё в конструктор `OrderService` и возвращает готовый объект.

Регистрация **не нужна** — Laravel сам справится с любым конкретным классом, если все его зависимости резолвимы.

Где это уже работает «из коробки»:

- **Конструкторы контроллеров**, middleware, jobs, команд.
- **Параметры методов контроллера**: `public function show(UserRepository $repo)`.
- **`__construct` любого класса**, который получают через `app()` или DI.

Когда **нужен** ручной bind: type-hint у конструктора — **интерфейс** (например, `PaymentGateway`). Контейнер не знает, какую конкретную реализацию подставить, — об этом говорят через `bind(Interface::class, Concrete::class)`.',
                'code_example' => 'class OrderRepo {
    public function __construct(private DB $db) {}
}

class OrderService {
    public function __construct(private OrderRepo $repo) {}
}

// Просто работает - без bind() в провайдере
$service = app(OrderService::class);

// В контроллере - type-hint, Laravel внедрит сам
class OrderController extends Controller
{
    public function show(int $id, OrderService $service)
    {
        return $service->find($id);
    }
}

// Интерфейс - autowiring не справится, нужен bind()
$this->app->bind(PaymentGateway::class, StripeGateway::class);',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.service_container',
            ],
        ];
    }
}
