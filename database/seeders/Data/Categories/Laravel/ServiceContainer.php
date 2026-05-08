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
                'answer' => 'Service Container - это механизм для управления зависимостями и инъекции зависимостей (DI). Простыми словами: контейнер - это "склад" объектов, который умеет сам создавать и подставлять нужные зависимости. Когда вы в конструкторе указываете тип параметра, Laravel автоматически найдёт и подставит нужный объект.',
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
                'answer' => 'Все три в итоге зовут Container::make и эквивалентны по результату — возвращают разрешённый из контейнера экземпляр. resolve() — это просто хелпер-обёртка над app(), а App — фасад того же контейнера. Разница чисто стилистическая; для передачи параметров в конструктор используется второй аргумент: app(Foo::class, ["id" => 5]).',
                'difficulty' => 2,
                'topic' => 'laravel.service_container',
            ],
        ];
    }
}
