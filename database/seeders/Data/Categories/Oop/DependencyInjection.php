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
                'answer' => 'Dependency Injection (внедрение зависимостей) - это техника, при которой объект получает свои зависимости извне, а не создаёт их сам. Виды: 1) Constructor Injection (через конструктор) - предпочтительный. 2) Setter Injection (через setter). 3) Property Injection (на практике обычно аннотированное приватное поле через рефлексию, как Symfony #[Required]; публичные поля как DI - редкая и плохая практика). DI - это паттерн, позволяющий следовать DIP. Делает код тестируемым, гибким, слабо связанным.',
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
                'answer' => 'Это разные понятия. DIP (Dependency Inversion Principle) - принцип проектирования: зависим от абстракций, а не от конкретных классов. DI (Dependency Injection) - техника, способ передавать зависимости в объект (через конструктор, сеттер). Можно соблюдать DIP без DI (например, создавая абстракции вручную). Можно использовать DI без соблюдения DIP (передавать конкретные классы). На практике DI - один из главных способов реализации DIP.',
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
                'answer' => 'IoC (инверсия управления) — принцип, при котором поток выполнения контролирует ФРЕЙМВОРК/КОНТЕЙНЕР, а не код приложения. Голливудский принцип: "Don\'t call us, we\'ll call you". В обычной библиотеке ты вызываешь её функции; в IoC-фреймворке ты регистрируешь компоненты, а фреймворк сам решает, КОГДА их позвать. Формы IoC: 1) Dependency Injection — контейнер создаёт твои объекты и передаёт зависимости. 2) Event-driven — фреймворк дёргает твои обработчики. 3) Template Method / lifecycle hooks — родитель вызывает твои переопределённые методы. 4) Routing/middleware — фреймворк маршрутизирует запрос в твой контроллер. DI — это ОДНА из техник реализации IoC, не синоним.',
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
                'answer' => 'Service Locator - объект-реестр, в котором по ключу можно получить нужный сервис: $locator->get(\'mailer\'). Считается анти-паттерном потому что: 1) Скрывает зависимости класса (по конструктору не видно, что нужно). 2) Усложняет тестирование (надо мокать локатор и регистрировать в нём). 3) Связывает класс с локатором. 4) Нарушает SRP. Альтернатива - Constructor Injection: явные зависимости через параметры. Laravel App container можно использовать как Service Locator (плохо) или как DI-контейнер (хорошо, через type-hint в конструкторе).',
                'code_example' => '<?php
// Анти-паттерн Service Locator
class OrderService
{
    public function process(): void
    {
        $repo = ServiceLocator::get(\'orderRepo\'); // скрытая зависимость
        $repo->save(\'...\');
    }
}

// Хорошо: явная зависимость
class OrderServiceGood
{
    public function __construct(private OrderRepository $repo) {}
    public function process(): void
    {
        $this->repo->save(\'...\');
    }
}',
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
                'answer' => 'Объект, который умеет создавать другие объекты, автоматически разбираясь, что им нужно. Ты говоришь $container->make(OrderService::class) — он смотрит на конструктор, видит «нужен OrderRepository», создаёт его, и вручает тебе готовый OrderService. В Laravel — это Service Container, фундамент всего фреймворка.',
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
