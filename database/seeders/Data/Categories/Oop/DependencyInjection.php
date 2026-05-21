<?php

namespace Database\Seeders\Data\Categories\Oop;

class DependencyInjection
{
    public static function all(): array
    {
        return [
            [
                'category' => 'ООП',
                'topic' => 'oop.dependency_injection',
                'difficulty' => 3,
                'question' => 'Что такое Dependency Injection (DI)?',
                'answer' => '**Dependency Injection (DI, внедрение зависимостей)** — техника, при которой объект **получает зависимости извне**, а не создаёт их сам.

**Три вида DI:**

| Вид | Как выглядит | Когда брать |
|---|---|---|
| **Constructor Injection** | зависимости в `__construct(...)` | **по умолчанию** — для **обязательных** зависимостей |
| **Setter Injection** | `$obj->setLogger($logger)` | **опциональные** зависимости с разумным дефолтом |
| **Property Injection** | приватное поле + атрибут (`#[Required]`) или публичное поле | редко; в Symfony — через autowiring аттрибутов |

**Constructor Injection — почему предпочтителен:**

- Делает зависимости **обязательными** — объект **не существует** без них.
- **Иммутабельность** — `readonly`-поля закрывают подмену зависимостей после создания.
- Зависимости **видны в сигнатуре** — нельзя забыть передать.
- В PHP 8: **constructor property promotion** делает запись компактной.

**Что даёт DI:**

- **Тестируемость** — подсунул mock/fake вместо реальной реализации.
- **Гибкость** — реализация подменяется без правки клиента (Strategy, окружения dev/prod).
- **Слабая связанность** — класс знает только интерфейс зависимости.
- **Явный контракт** — по конструктору видно, **от чего класс зависит**.

**DI vs DIP — не путать:**

- **DI** — техника **доставки** зависимости.
- **DIP** (Dependency Inversion Principle) — принцип **зависеть от абстракций**.
- DI без DIP = инжектим конкретный класс (бесполезно). DIP без DI = создаём абстракцию внутри (`Factory::make()`). **Хорошо — оба вместе.**',
                'code_example' => '<?php
// Без DI: жёсткая зависимость
class OrderServiceBad
{
    public function process(): void
    {
        $repo = new OrderRepository(); // вшито
    }
}

// С DI: зависимость передана извне
class OrderService
{
    public function __construct(
        private OrderRepository $repo,
        private Mailer $mailer,
    ) {}
}

// Передаём зависимости явно
$service = new OrderService(
    new OrderRepository(),
    new SmtpMailer()
);',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.dependency_injection',
                'difficulty' => 3,
                'question' => 'В чём разница между DI и DIP?',
                'answer' => '**Это разные оси проектирования** — их часто путают, потому что они почти всегда применяются вместе.

| | **DIP** (принцип) | **DI** (техника) |
|---|---|---|
| Полное название | **Dependency Inversion Principle** | **Dependency Injection** |
| Категория | принцип (SOLID, **D**) | паттерн / способ реализации |
| Отвечает на | **«от чего зависим»** | **«как доставляется»** |
| Правило | зависим от **абстракций**, не от конкретных классов | зависимость **передаётся извне** |
| Где применяется | в архитектуре, направлении зависимостей | в коде конкретного класса |

**Все четыре комбинации возможны:**

- **DI + DIP** (идеал) — `__construct(OrderRepository $repo)` с интерфейсом.
- **DI без DIP** — `__construct(EloquentOrderRepository $repo)` — инжектим, но конкретный класс. Подменить трудно.
- **DIP без DI** — `$this->repo = Factory::make()` — абстракция есть, но создаём внутри. Подменить можно через подмену фабрики, но не через тест.
- **Ни того ни другого** — `new EloquentOrderRepository()` прямо в методе. **Антипаттерн**.

**Запомни:** **DI — это инструмент**, **DIP — это цель**. DI — главный (но не единственный) способ реализовать DIP. Можно соблюдать DIP другими способами — паттернами `Factory`, `Service Locator`, рефлексией — но DI прозрачнее и тестируемее.',
                'code_example' => '<?php
// DI без DIP: инъекция есть, но зависим от конкретного класса
class OrderServiceA
{
    public function __construct(private MysqlOrderRepository $repo) {}
}

// DIP без DI: зависим от абстракции, но создаём её внутри
class OrderServiceB
{
    private OrderRepository $repo;
    public function __construct()
    {
        $this->repo = RepositoryFactory::make(); // не DI, но абстракция
    }
}

// DIP + DI (идеал): и абстракция, и инъекция
class OrderService
{
    public function __construct(private OrderRepository $repo) {}
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.dependency_injection',
                'difficulty' => 3,
                'question' => 'Что такое Inversion of Control (IoC)?',
                'answer' => '**Inversion of Control (IoC, инверсия управления)** — принцип, при котором **поток выполнения контролирует фреймворк/контейнер**, а не код приложения.

**Голливудский принцип:** *«Don\'t call us, we\'ll call you.»*

**Разница:**

| | **Библиотека** | **Фреймворк (IoC)** |
|---|---|---|
| Кто главный | **твой код** | **фреймворк** |
| Поток | ты вызываешь функции библиотеки | фреймворк зовёт твой код |
| Точка входа | твой `main()` | `bootstrap` фреймворка |
| Пример | `Carbon`, `Str::slug()` | Laravel, Symfony |

**Формы IoC (не только DI!):**

| Форма | Кто что делает | Пример в Laravel |
|---|---|---|
| **Dependency Injection** | контейнер создаёт твои объекты и передаёт зависимости | type-hint в конструкторе контроллера |
| **Event-driven** | фреймворк дёргает твои обработчики | `Event`-listeners, observer |
| **Template Method / lifecycle hooks** | родитель/фреймворк вызывает твои переопределённые методы | `ServiceProvider::register()`, `boot()` |
| **Routing / middleware** | фреймворк маршрутизирует запрос | `Route::post(...)` |
| **Callbacks** | передаёшь замыкание, фреймворк зовёт его | `Route::get(\'/\', fn () => ...)` |

**Что даёт IoC:**

- **Меньше boilerplate** — не пишешь свой главный цикл и резолвинг зависимостей.
- **Расширяемость** — фреймворк закрыт, ты пишешь только специфику.
- **Стандартизация** — все приложения на Laravel похожи структурно.

**DI vs IoC:** **DI — это одна из техник IoC**, не синоним. Любой DI — это IoC, но не любой IoC — это DI. Часто говорят «IoC-контейнер» именно про **DI-контейнер**, потому что DI — самая видимая форма IoC во фреймворках.',
                'code_example' => '<?php
// ❌ Без IoC - приложение само управляет потоком
$request = Request::createFromGlobals();
$router = new Router($routes);
$controller = $router->resolve($request);
$response = $controller->handle($request);
$response->send();
// Ты пишешь главный цикл сам.

// ✅ С IoC (Laravel) - фреймворк сам вызывает твой код в нужный момент

// 1) DI: контейнер создаёт контроллер и передаёт зависимости
class OrderController
{
    public function __construct(private OrderService $orders) {}

    public function store(StoreOrderRequest $request): JsonResponse
    {
        // Laravel сам:
        // - смаршрутизировал POST /orders сюда
        // - инстанцировал контроллер
        // - резолвил OrderService через контейнер
        // - валидировал и инжектнул FormRequest
        $order = $this->orders->place($request->validated());
        return response()->json($order);
    }
}

// 2) Event-driven: ты регистрируешь listener, Laravel зовёт его сам
class SendWelcomeEmail
{
    public function handle(UserRegistered $event): void
    {
        // Laravel вызовет тебя, когда событие случится
    }
}

// 3) Lifecycle hooks: фреймворк вызывает твой boot/register
class AppServiceProvider extends ServiceProvider
{
    public function register(): void { /* фреймворк позовёт сам */ }
    public function boot(): void { /* и это тоже */ }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.dependency_injection',
                'difficulty' => 3,
                'question' => 'Что такое Service Locator и почему его считают анти-паттерном?',
                'answer' => '**Service Locator** — объект-реестр, в котором **по ключу** можно получить нужный сервис: `$locator->get(\'mailer\')` или `app(Mailer::class)`.

**Почему считают анти-паттерном:**

1. **Скрывает зависимости** — по `__construct(...)` **не видно**, что нужно классу. Зависимости «всплывают» только при чтении тела методов.
2. **Усложняет тестирование** — надо подменять локатор глобально или регистрировать в нём моки. С DI: просто передал mock в конструктор.
3. **Связь с инфраструктурой** — каждый класс зависит от локатора, без него ничего не работает.
4. **Нарушает SRP** — класс отвечает и за свою задачу, и за резолвинг зависимостей.
5. **Runtime-ошибки вместо compile-time** — забыл зарегистрировать сервис → крах в проде вместо ошибки сразу.

**Альтернатива — Constructor Injection:**

```php
public function __construct(private Mailer $mailer) {} // явно
```

**Тонкость:** **Laravel `app()` / `Container::make()` можно использовать двумя способами**:

| Использование | Что это |
|---|---|
| `app(Mailer::class)` **внутри метода** | **Service Locator** (плохо) — скрытая зависимость |
| `app(Mailer::class)` **в фабрике / провайдере** | **DI-контейнер** (хорошо) — сборка графа |
| **Type-hint** в конструкторе/методе | **DI** (хорошо) — контейнер резолвит зависимости |

**Где Service Locator оправдан:**

- **Lazy-резолвинг** **очень тяжёлых** зависимостей, которые **редко** нужны (но честнее — `Closure`-инъекция).
- **Плагинная система**, где сервисы регистрируются динамически по конфигу.
- **Legacy-код**, куда DI добавить дорого.

**Правило:** в новом коде по умолчанию — **Constructor DI**. К `app()` обращаться только в провайдерах, фабриках и роут-замыканиях.',
                'code_example' => '<?php
// ❌ Service Locator - зависимости скрыты, тестировать через global mock
class OrderServiceBad
{
    public function process(int $id): void
    {
        // ничего не видно по сигнатуре класса
        $repo    = ServiceLocator::get(\'orderRepo\');
        $mailer  = ServiceLocator::get(\'mailer\');
        $logger  = ServiceLocator::get(\'logger\');

        $order = $repo->find($id);
        $mailer->send($order->email, \'paid\');
        $logger->info(\'processed\', [\'id\' => $id]);
    }
}

// ❌ Антипаттерн в Laravel - app() внутри метода
class OrderServiceLaravelBad
{
    public function process(int $id): void
    {
        $repo = app(OrderRepository::class); // скрытая зависимость
        $repo->find($id);
    }
}

// ✅ Constructor Injection - всё видно по сигнатуре
class OrderService
{
    public function __construct(
        private OrderRepository $repo,
        private Mailer $mailer,
        private LoggerInterface $logger,
    ) {}

    public function process(int $id): void
    {
        $order = $this->repo->find($id);
        $this->mailer->send($order->email, \'paid\');
        $this->logger->info(\'processed\', [\'id\' => $id]);
    }
}

// В тесте - просто подсунули моки/фейки
$service = new OrderService(
    new InMemoryOrderRepo(),
    new FakeMailer(),
    new NullLogger(),
);',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.dependency_injection',
                'difficulty' => 1,
                'question' => 'Что такое Dependency Injection простыми словами?',
                'answer' => '**Dependency Injection (DI, внедрение зависимостей)** — принцип: класс **НЕ создаёт** свои зависимости сам (`new OrderRepo()`), а **получает их снаружи** — обычно через конструктор.

Аналогия: «передать готовый чайник», а не «найти и купить чайник самому».

**Что даёт:**

- **тестируемость** — можно подсунуть mock
- **гибкость** — реализацию легко подменить
- **явность** — по конструктору видно, что нужно классу',
                'code_example' => '<?php
// ❌ Без DI: зависимость вшита в класс
class OrderServiceBad
{
    public function process(): void
    {
        $repo = new OrderRepository(); // жёстко прибито
        $repo->save();
    }
}

// ✅ С DI: зависимость передана извне
class OrderService
{
    public function __construct(private OrderRepository $repo) {}

    public function process(): void
    {
        $this->repo->save();
    }
}

$service = new OrderService(new OrderRepository());',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.dependency_injection',
                'difficulty' => 1,
                'question' => 'Зачем нужен Dependency Injection простыми словами?',
                'answer' => '1. **Тестирование** — легко подменить зависимость mock-ом в юнит-тесте.
2. **Гибкость** — реализацию (`StripePayment` → `PayPalPayment`) можно поменять, не трогая клиент.
3. **Явность** — по конструктору сразу видно, от чего класс зависит.
4. **Меньше связанности** — классы не «прибиты гвоздями» друг к другу.',
                'code_example' => '<?php
interface PaymentGateway { public function pay(int $cents): bool; }

class StripeGateway implements PaymentGateway { public function pay(int $c): bool { /* ... */ return true; } }
class FakeGateway   implements PaymentGateway { public function pay(int $c): bool { return true; } }

class OrderService
{
    public function __construct(private PaymentGateway $gateway) {}
}

// В проде - реальный платёжник
$service = new OrderService(new StripeGateway());

// В тесте - фейк, не дёргает внешний API
$service = new OrderService(new FakeGateway());',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.dependency_injection',
                'difficulty' => 2,
                'question' => 'Что такое DI-контейнер простыми словами?',
                'answer' => '**DI-контейнер** — объект, который умеет **создавать** другие объекты, **автоматически** разбираясь в их зависимостях.

**Как работает:**

1. Просишь `$container->make(OrderService::class)`.
2. Контейнер смотрит на **typehint в конструкторе** — `__construct(OrderRepository $repo)`.
3. **Рекурсивно** создаёт `OrderRepository` (и его зависимости).
4. Вручает готовый `OrderService` со всем графом зависимостей.

**Возможности типового контейнера:**

- `bind(Interface::class, Concrete::class)` — биндинг интерфейса на реализацию.
- `singleton(...)` — один экземпляр на запрос/процесс.
- Автоматическая инъекция через **typehint** (constructor/method).
- Контекстные биндинги — разные реализации для разных мест.

**В Laravel** это **Service Container** — фундамент всего фреймворка: контроллеры, FormRequest, Job-ы создаются именно через него.',
                'code_example' => '<?php
interface OrderRepository {}
class EloquentOrderRepository implements OrderRepository {}

class OrderService
{
    public function __construct(private OrderRepository $repo) {}
}

// В Laravel
// 1) Биндим интерфейс на реализацию
$this->app->bind(OrderRepository::class, EloquentOrderRepository::class);

// 2) Просим у контейнера сервис - он сам разбирает конструктор,
//    создаёт EloquentOrderRepository и подставляет в OrderService
$service = app(OrderService::class);

// 3) Type-hint в контроллере - автоматическая инъекция
class OrderController
{
    public function store(OrderService $service) { /* service уже готов */ }
}',
                'code_language' => 'php',
            ],
        ];
    }
}
