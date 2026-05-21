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
                'answer' => 'bind - каждый раз создаётся новый объект при resolve. singleton - объект создаётся один раз в рамках жизненного цикла приложения (т.е. одного запроса в FPM, всего воркера в Octane/RoadRunner). scoped - объект живёт в рамках одного запроса/job (Octane сбрасывает scoped между запросами, singleton - нет). instance - регистрирует уже созданный объект как singleton.',
                'code_example' => '$this->app->bind(Foo::class, fn() => new Foo());
$this->app->singleton(Bar::class, fn() => new Bar());
$this->app->scoped(Baz::class, fn() => new Baz());
$this->app->instance(Qux::class, new Qux());',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.service_container',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое contextual binding и приведите кейс из реального проекта.',
                'answer' => 'Contextual binding позволяет внедрять разные реализации интерфейса в зависимости от потребляющего класса. Пример: PhotoController должен использовать LocalFilesystem, а VideoController - S3, оба зависят от Filesystem. Без contextual binding пришлось бы вводить именованные интерфейсы или конкреты в типах. when()->needs()->give() решает это в одном месте.',
                'code_example' => '<?php
$this->app->when(PhotoController::class)
    ->needs(Filesystem::class)
    ->give(fn() => Storage::disk("local"));

$this->app->when(VideoController::class)
    ->needs(Filesystem::class)
    ->give(fn() => Storage::disk("s3"));',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.service_container',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как обернуть сервис в Decorator через Service Container? ($app->extend)',
                'answer' => '$app->extend(string $abstract, Closure $callback) - метод контейнера, который "перехватывает" уже зарезолвленный экземпляр и заменяет его на обёртку. Контейнер сначала строит оригинальный объект (по биндингу или авторезолву), затем передаёт его в callback вместе с самим контейнером, и то, что callback вернёт - становится новой версией сервиса в контейнере. Это идеальный механизм для применения паттерна Decorator без правки исходного класса (особенно полезно с вендорными сервисами, до которых нельзя дотянуться). Можно компоновать несколько extend - они применяются в порядке регистрации, образуя стек декораторов. Применение: добавить кеширование вокруг репозитория, логирование вокруг http-клиента, метрики/трейсинг, feature-flag-обёртки. Альтернативные подходы и когда они лучше: 1) Просто bind вашу реализацию вместо оригинала - если не нужна делегация в оригинал. 2) Контекстный binding ($app->when()->needs()->give()) - когда декорация нужна только для конкретного потребителя, а не глобально. 3) Pipeline - для пошаговой трансформации значения. extend - именно для оборачивания инстанса.',
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
