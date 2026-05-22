<?php

namespace Database\Seeders\Data\Categories\Laravel;

class Testing
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Laravel',
                'question' => 'Чем отличаются PHPUnit и Pest в Laravel?',
                'answer' => 'Два способа писать тесты в Laravel:

- **PHPUnit** — стандартный PHP-фреймворк. Тесты пишутся как **методы класса**, наследующего `TestCase`. Метод теста начинается с `test_` или имеет атрибут `#[Test]`.
- **Pest** — надстройка над PHPUnit с **более лаконичным синтаксисом** (вдохновлён Jest из JS). Тесты пишутся как **функции** с описательными названиями + цепочечный `expect`-API.

**Под капотом обоих — PHPUnit.** Pest просто транслирует свой DSL в PHPUnit-классы. Можно держать оба в одном проекте.

С Laravel 11 **Pest идёт по умолчанию** в новом проекте (выбирается при `laravel new`). Старые проекты — обычно на PHPUnit.

Запуск:

- **PHPUnit**: `php artisan test` или `./vendor/bin/phpunit`.
- **Pest**: `./vendor/bin/pest` или тот же `php artisan test`.',
                'code_example' => '// PHPUnit
class UserTest extends TestCase {
    public function test_user_can_register(): void {
        $response = $this->post(\'/register\', [...]);
        $response->assertOk();
    }
}

// Pest
test(\'user can register\', function () {
    $response = $this->post(\'/register\', [...]);
    expect($response->status())->toBe(200);
});',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.testing',
            ],
            [
                'category' => 'Laravel',
                'question' => 'В чём разница между RefreshDatabase и DatabaseTransactions?',
                'answer' => 'Три **trait-а изоляции БД** в Laravel — отличаются тем, **что они делают со схемой и данными**:

| Trait | Что делает со схемой | Между тестами | Скорость |
| --- | --- | --- | --- |
| `RefreshDatabase` | Один раз на сьют `migrate:fresh` (в in-memory SQLite — заново каждый тест) | Транзакция вокруг каждого теста → откат | Быстро |
| `DatabaseTransactions` | **Ничего** — схема должна быть уже мигрирована | Транзакция → откат | Быстро |
| `DatabaseMigrations` | `migrate:fresh` **перед каждым** тестом | Полный сброс | Медленно |

**Что важно:**

- `RefreshDatabase` — **дефолт** для большинства проектов: чистая БД + транзакция на тест.
- `DatabaseTransactions` подходит, когда **схема общая** для всех окружений (например, в CI БД мигрируется отдельным шагом) — экономит время на миграциях.
- Транзакционные trait-ы **не работают для кода, использующего вложенные/чужие соединения** или `DB::commit()` — данные «утекут» между тестами. Тогда нужен `DatabaseMigrations` или ручной `truncate`.
- В Laravel 11 есть свойство `$connectionsToTransact` — список соединений, которые нужно оборачивать (полезно при многобазных тестах).',
                'code_example' => 'use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase {
    use RefreshDatabase;

    public function test_create_user(): void {
        $user = User::factory()->create();
        $this->assertDatabaseHas(\'users\', [\'id\' => $user->id]);
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.testing',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое HTTP-тесты в Laravel?',
                'answer' => '**HTTP-тесты** прогоняют запрос **через весь HTTP-стек приложения** (middleware, роутинг, контроллер, response) **без реальной сети** — Laravel внутри делает `Kernel::handle($request)` и возвращает `TestResponse` с десятками assert-методов.

**Основные методы запроса:**

- HTML-формы: `get`, `post`, `put`, `patch`, `delete`, `options`.
- JSON: `getJson`, `postJson`, `putJson`, `deleteJson` — выставляют `Accept: application/json`.
- Загрузка файлов: `$this->post(uri, [\'file\' => UploadedFile::fake()->image(\'a.jpg\')])`.

**Популярные assert-ы:**

- Статус: `assertOk`, `assertCreated`, `assertNoContent`, `assertStatus(422)`, `assertRedirect(\'/x\')`, `assertForbidden`, `assertUnauthorized`.
- Содержимое: `assertSee`, `assertSeeText`, `assertDontSee`, `assertJson`, `assertJsonPath`, `assertJsonStructure`, `assertJsonFragment`, `assertJsonValidationErrors`.
- Сессия и заголовки: `assertSessionHas`, `assertSessionHasErrors`, `assertHeader`, `assertCookie`.

**Аутентификация и сессия:**

- `actingAs($user)` или `actingAs($user, \'api\')` — логинит без реального запроса на `/login`.
- `withSession([...])`, `withHeaders([...])`, `withCookie(...)`.
- `withoutMiddleware()` / `withoutMiddleware(VerifyCsrfToken::class)` — выключить middleware на этом тесте.

**Подводные камни:**

- По умолчанию `Throw exceptions` включён — 500-ка падает наружу. Отключить точечно: `withoutExceptionHandling()` наоборот, **показывает** реальный stack-trace вместо JSON-ответа.
- HTTP-тест **не запускает JavaScript** — для SPA/Livewire нужен `Dusk`.',
                'code_example' => 'public function test_index_returns_users(): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->getJson(\'/api/users\');

    $response->assertOk()
        ->assertJsonStructure([\'data\' => [[\'id\', \'name\']]]);
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.testing',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое fakes в Laravel-тестах?',
                'answer' => '**Fake** — встроенная в Laravel **подмена сервиса заглушкой-«писцом»**: вместо реальной отправки/публикации/записи фейк **собирает то, что от него хотели**, и потом тест проверяет это через `assert*`-методы.

**Зачем:**

- **Нет побочных эффектов** — реальные письма/SMS/job-ы не уходят.
- Тест становится **быстрым и детерминированным**.
- Можно проверить **факт**, **количество**, **аргументы** вызовов.

**Стандартный набор:**

| Фасад | Что подменяет | Типовой assert |
| --- | --- | --- |
| `Mail::fake()` | отправку писем | `Mail::assertSent(Cls::class)` |
| `Notification::fake()` | уведомления | `Notification::assertSentTo($u, Cls::class)` |
| `Queue::fake()` | постановку job в очередь | `Queue::assertPushed(Cls::class)` |
| `Bus::fake()` | dispatch через bus | `Bus::assertDispatched(Cls::class)` |
| `Event::fake()` | события | `Event::assertDispatched(Cls::class)` |
| `Storage::fake(\'public\')` | файловый диск (in-memory) | `Storage::disk(\'public\')->assertExists($path)` |
| `Http::fake([...])` | внешние HTTP-запросы через `Http::` | `Http::assertSent(fn ($r) => ...)` |
| `Process::fake()` | внешние процессы | `Process::assertRan(...)` |

**Нюансы:**

- `Event::fake()` **перехватывает все события**, поэтому `Mail::fake()` после `Event::fake()` не сработает — почта идёт через события. Используй `Event::fake([...])` со списком, либо `Event::fakeExcept`.
- `Bus::fake()` отключает **выполнение** job-ов — если тест ожидает, что job что-то сделал в БД, нужен `Queue::fake()` + `Bus::dispatchSync()` или интеграционный прогон.
- В Pest есть `Mail::fake()` точно так же — фасады работают одинаково.',
                'code_example' => 'public function test_email_sent(): void {
    Mail::fake();

    $this->post(\'/orders\', [...]);

    Mail::assertSent(OrderShipped::class, fn($m) => $m->hasTo(\'a@b.c\'));
}

public function test_job_dispatched(): void {
    Queue::fake();

    ProcessOrder::dispatch();

    Queue::assertPushed(ProcessOrder::class);
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.testing',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как делать mocking в Laravel-тестах?',
                'answer' => 'В Laravel **три уровня** замоканья — выбор зависит от того, как зависимость попадает в код:

**1) Фасады — `Cache::shouldReceive(...)`**

Фасад это прокси к биндингу в контейнере; `shouldReceive` подменяет биндинг **Mockery-моком** на время теста. Между тестами Laravel сам чистит resolved-инстансы.

**2) Класс через DI — `$this->mock(Class::class)`**

Хелпер `mock()` создаёт Mockery-мок **и сразу регистрирует его в контейнере** — следующий `app(Class::class)` отдаст его. Аналог — `$this->instance(Class::class, $fake)`.

**3) Частичный мок — `$this->partialMock(Class::class, fn ($m) => ...)`**

Подменяет **только указанные методы**, остальные работают как у реального объекта. Удобно, когда нужен реальный сервис, но **один** метод дорого/нежелательно звать.

**Полезные сравнения:**

- `mock()` vs `instance()` — `mock()` сам ставит экспектации через замыкание; `instance()` принимает любой объект (например, готовый Fake-класс).
- `Mockery::mock(Cls::class)` создаёт **strict mock** (любой не объявленный вызов — ошибка); `Mockery::spy(Cls::class)` принимает любые вызовы и пишет их.
- Для фасадов есть `Cache::spy()` — собирает вызовы, потом `Cache::shouldHaveReceived(\'get\')`.

**Подводные камни:**

- Mockery **не работает с final-классами/методами** — мокать нельзя; либо `instance()` с реальным fake-классом, либо `mockery/mockery` с `--allow-mocking-non-existent-methods` (для типобезопасности — лучше interface).
- После `$this->mock()` забудь про **typehints родителя**: контейнер отдаст мок, не реальный класс.',
                'code_example' => '// Mock фасада
Cache::shouldReceive(\'get\')->once()->with(\'key\')->andReturn(\'value\');

// Mock сервиса
$mock = $this->mock(PaymentService::class);
$mock->shouldReceive(\'charge\')->once()->andReturn(true);

// Подмена в контейнере
$this->instance(PaymentService::class, new FakePaymentService());',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.testing',
            ],
            [
                'category' => 'Laravel',
                'question' => 'В чём разница между Mock, Stub, Spy, Fake и Dummy? (классификация Мешароса/Фаулера)',
                'answer' => '**Test doubles** — объекты-заглушки для зависимостей в тестах. Классификация **Мешароса** (xUnit Patterns), популяризованная **Фаулером** в статье «Mocks Aren\'t Stubs».

**Пять типов double:**

| Тип | Что делает | Проверка | Стиль тестирования |
|---|---|---|---|
| **Dummy** | Просто заполняет параметр, **никогда не используется** | Нет | — |
| **Stub** | Возвращает **canned ответы**, никакого verify по вызовам | Через состояние SUT | **State-based** |
| **Spy** | Как stub, но **записывает все вызовы** (что/с чем/сколько раз) | **Постфактум** через `assertCalled` | State + behavior |
| **Mock** | Заранее **ожидает** конкретные вызовы через `expect()` | **Автоматически** при `Mockery::close()` | **Behavior-based** |
| **Fake** | **Рабочая упрощённая реализация** (in-memory вместо БД) | Через состояние SUT | State-based |

**Детальное сравнение Stub vs Mock vs Spy:**

| | **Stub** | **Spy** | **Mock** |
|---|---|---|---|
| Возвращает значение | Да | Да | Да |
| Записывает вызовы | Нет | Да | Да |
| Где задаётся expectation | Не задаётся | После действия (`shouldHaveReceived`) | До действия (`expects()`) |
| Кто валит тест | Сам тест (через assert) | Сам тест (через assert) | **Сам double** в `Mockery::close()` |

**Mockery API (используется в Laravel):**

| Что нужно | Код |
|---|---|
| **Stub** | `$m->shouldReceive("foo")->andReturn(...)` |
| **Mock** | `$m->shouldReceive("foo")->once()->with(42)` |
| **Spy** | `Mockery::spy(Cls::class)` + `$spy->shouldHaveReceived(...)` |
| **Dummy** | Просто `new NullLogger()` или `Mockery::mock(Cls::class)` без expectations |

**Laravel-специфичные fakes:**

- **`Mail::fake()`**, **`Queue::fake()`**, **`Event::fake()`** — под капотом это **spy** (записывает + `assertSent`).
- **`Storage::fake()`** — это **fake** (in-memory диск, реально работает).

**Senior-практика:**

- **Предпочитать stubs/fakes** для большинства тестов — **прочнее к рефакторингу**.
- **Mocks использовать**, когда взаимодействие **ЯВЛЯЕТСЯ предметом теста** (event dispatched, http request sent, audit log written).
- **Чрезмерное использование mocks** даёт хрупкие тесты, ломающиеся при невинном рефакторинге.',
                'code_example' => '<?php
use Mockery;

// STUB - возвращает заданное значение, не проверяет вызовы
$repo = Mockery::mock(UserRepository::class);
$repo->shouldReceive("find")->andReturn(new User("Tom"));

// MOCK - явное ожидание вызова
$mailer = Mockery::mock(Mailer::class);
$mailer->shouldReceive("send")
    ->once()
    ->with(Mockery::on(fn($email) => $email->to === "tom@a"));
// Тест провалится, если send не вызван или вызван с другими аргументами

// SPY - запись для последующей проверки
$logger = Mockery::spy(Logger::class);
$service = new OrderService($logger);
$service->place();
$logger->shouldHaveReceived("info")->with("order.placed");

// FAKE - рабочая упрощённая реализация
class FakeUserRepository implements UserRepository
{
    private array $users = [];
    public function save(User $u): void { $this->users[$u->id] = $u; }
    public function find(int $id): ?User { return $this->users[$id] ?? null; }
}

// DUMMY - просто чтобы конструктор не упал
new OrderService(new NullLogger()); // никто его не вызовет в этом тесте

// Laravel-специфичные fakes
Mail::fake();
Queue::fake();
Event::fake();
// под капотом Mail::fake() - это spy: записывает отправленные письма,
// потом Mail::assertSent(InvoicePaid::class)',
                'code_language' => 'php',
                'difficulty' => 5,
                'topic' => 'laravel.testing',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Data Providers в PHPUnit / Pest и зачем они нужны?',
                'answer' => '**Data Provider** — механизм запуска **одного теста** с **разными наборами входных данных**. Вместо копи-пасты `test_zero`, `test_negative`, `test_huge` — пишется **один** тест, а данные подаются провайдером. PHPUnit выполнит тест по разу для каждого набора и в отчёте покажет каждый прогон отдельно (например, упал «leading dot» — сразу видно какой случай).

**Когда применять:**

- Валидатор/парсер с десятками граничных входов.
- Табличные тесты (table-driven tests).
- Один и тот же ассерт на разных типах данных.

**Синтаксис:**

- **PHPUnit** — атрибут `#[DataProvider(\'methodName\')]` на тесте + **статический** метод, возвращающий `iterable`. Удобно ключить кейсы строками (`yield \'leading dot\' => [...]`) — попадут в имя теста.
- **Pest** — `->with([...])` прямо на тесте, либо `->with(\'name\')` + глобальный `dataset(\'name\', [...])`.

**Под капотом и ограничения:**

- Метод-провайдер **статический** и вызывается **до `setUp()`** — поэтому в нём **нельзя создавать Eloquent-модели через factory** (БД ещё не готова). Решения: передавать **замыкание** (Pest), или **`yield`** ленивые значения, либо строить модель **внутри тела теста** по ключу из провайдера.
- Тест с большим набором кейсов остаётся **читаемым** — параметризация без потери ясности и дублирования.',
                'code_example' => '<?php
// PHPUnit
use PHPUnit\\Framework\\Attributes\\DataProvider;

final class EmailValidatorTest extends TestCase
{
    #[DataProvider("emailCases")]
    public function test_validation(string $email, bool $expected): void
    {
        $this->assertSame($expected, EmailValidator::isValid($email));
    }

    public static function emailCases(): iterable
    {
        yield "valid simple"      => ["a@b.co", true];
        yield "valid plus"        => ["a+tag@b.co", true];
        yield "missing @"         => ["abc.com", false];
        yield "no tld"            => ["a@b", false];
        yield "leading dot"       => [".a@b.co", false];
        yield "unicode"           => ["юзер@домен.рф", true];
    }
}

// Pest
test("email validation", function (string $email, bool $expected) {
    expect(EmailValidator::isValid($email))->toBe($expected);
})->with([
    "valid simple" => ["a@b.co", true],
    "missing @"    => ["abc.com", false],
    "no tld"       => ["a@b", false],
]);

// Pest dataset reuse
dataset("emails", [
    ["a@b.co", true],
    ["abc.com", false],
]);

test("validator", fn ($email, $valid) => expect(...))
    ->with("emails");',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.testing',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как тестировать логику, зависящую от времени? (travel, freeze, setTestNow)',
                'answer' => 'Когда код смотрит на `now()` («токен живёт час», «напомнить через 3 дня», «не позднее 23:00») — в тесте **нельзя ждать реальное время**. Laravel даёт обёртки над `Carbon::setTestNow()`.

**Основные хелперы (`Illuminate\\Foundation\\Testing\\Concerns\\InteractsWithTime`):**

- `$this->travel(1)->hour()` / `->days()` / `->minutes()` — сдвинуть **относительно**.
- `$this->travelTo(Carbon::parse(\'2026-01-01 09:00\'))` — прыжок на **абсолютное** время.
- `$this->travelBack()` — вернуться к реальному времени (Laravel **сам зовёт** это в `tearDown`).
- `$this->freezeTime(fn () => ...)` / `$this->freezeSecond(fn () => ...)` — **заморозить** время, чтобы `now()` возвращал одно значение между вызовами (полезно при сравнении timestamp-ов с точностью до мс).

**Что важно понимать:**

- Под капотом — `Carbon::setTestNow($instant)`. Это **глобально** для всего приложения: `created_at` у новых записей запишется с этим временем.
- **`time()` и `new DateTime()` НЕ подменяются** — Laravel умеет двигать только `Carbon::now()` / `now()`. В коде нужно использовать Carbon.
- В **параллельных** тестах (`php artisan test --parallel`) `travel` действует только в текущем процессе — никаких глобальных race-conditions нет.

**Альтернатива чище:** инжектить `PSR-20 ClockInterface` (или собственный `Clock`-сервис) в код, в тестах биндить `FakeClock`. В типовом Laravel-проекте обычно достаточно `travel`.',
                'code_example' => '<?php
use Illuminate\\Foundation\\Testing\\TestCase;

class TokenTest extends TestCase
{
    public function test_token_expires_after_hour(): void
    {
        $token = PasswordReset::create(["user_id" => 1, "expires_at" => now()->addHour()]);
        expect($token->isExpired())->toBeFalse();

        $this->travel(1)->hour();
        expect($token->fresh()->isExpired())->toBeTrue();
    }

    public function test_streak_increments_each_day(): void
    {
        $user = User::factory()->create();
        $this->travelTo("2026-05-01 09:00");
        $user->logActivity();

        $this->travelTo("2026-05-02 09:00");
        $user->logActivity();

        expect($user->fresh()->current_streak)->toBe(2);
    }

    public function test_two_calls_same_microsecond(): void
    {
        $this->freezeTime();
        $a = now();
        usleep(1000);
        $b = now();
        expect($a)->toEqual($b); // время заморожено
    }

    public function test_with_explicit_back(): void
    {
        $this->travel(1)->day();
        // ...
        $this->travelBack(); // явный возврат, если нужно в середине
        expect(now())->toBeBetween(now()->subSecond(), now()->addSecond());
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.testing',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как замокать фасад в тесте и почему это вообще возможно?',
                'answer' => '**Фасад — это прокси к биндингу в Service Container**. Статический `Cache::get()` через `__callStatic` идёт в `Container::make(\'cache\')` и зовёт `->get()` на **реальном** объекте. Это значит: достаточно подменить **биндинг** — и весь код, ходящий через фасад, начнёт обращаться к моку.

**API на базовом классе `Facade`:**

- `Cache::shouldReceive(\'get\')->with(\'k\')->andReturn(\'v\')` — **stub**: «если позовут — верни».
- `Cache::expects(\'put\')->once()->with(\'k\', \'v\', 60)` — **mock** с явным ожиданием (тест упадёт, если не позвали или позвали с другими аргументами).
- `Cache::spy()` + `Cache::shouldHaveReceived(\'get\')` — пишет вызовы, проверяем **после** действия.
- `Cache::swap($obj)` / `Cache::partialMock()` — подменить целиком / частично.

**Почему моки не утекают:** в `tearDown` `TestCase` зовёт `Facade::clearResolvedInstances()` и `Mockery::close()`.

**Когда не работает:**

- Если код берёт сервис **через DI**, а не через фасад — фасадный мок не сработает. Используй `$this->mock(Class::class)` или `$this->instance(...)`.
- Если фасад завязан на **синглтон, который уже разрезолвлен** до `shouldReceive` (редко, но в `boot()` бывает) — мок не подменит уже выданный инстанс.

**Партиал-мок:** `$this->partialMock(PaymentService::class, fn ($m) => $m->shouldReceive(\'charge\')->andReturn(...))` — остальные методы остаются настоящими, удобно когда менять надо **только одну ветку**.',
                'code_example' => '<?php
public function test_cache_is_used(): void
{
    // STUB - просто вернуть значение
    Cache::shouldReceive("get")
        ->with("user:42")
        ->andReturn(["id" => 42, "name" => "Tom"]);

    $response = $this->getJson("/api/users/42");
    $response->assertOk();
}

public function test_cache_is_written_exactly_once(): void
{
    // MOCK - проверка факта вызова
    Cache::shouldReceive("put")
        ->once()
        ->with("user:42", Mockery::any(), 3600);

    $this->postJson("/api/users/42/refresh")->assertOk();
}

public function test_partial_mock(): void
{
    // только нужный метод подменили, остальное - настоящий
    $this->partialMock(PaymentService::class, function ($mock) {
        $mock->shouldReceive("charge")->andReturn(new PaidResult);
    });

    $this->post("/checkout")->assertRedirect("/thanks");
}

public function test_via_instance(): void
{
    // Альтернатива - подмена в контейнере (для DI, не фасадов)
    $this->instance(Mailer::class, new ArrayMailer);
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.testing',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Dusk и для чего он используется?',
                'answer' => '**Laravel Dusk** — пакет для **end-to-end (browser)** тестирования.

Что делает:

- Запускает **настоящий браузер** (Chromium через ChromeDriver) в headless-режиме.
- Эмулирует **действия пользователя**: клики, ввод текста, нажатия клавиш, ожидание элементов, скриншоты, выбор файла.
- Выполняет **настоящий JavaScript** — поэтому только им можно тестировать Livewire/Vue/React-фронтенд.

Сравнение с HTTP-тестами (`$this->get(...)`):

- HTTP-тест — мгновенный, не запускает JS, проверяет ответ сервера.
- Dusk — медленнее, но **видит DOM после JS**.

Особенности:

- **Не нужен Selenium/JDK** — ChromeDriver идёт в комплекте.
- Тесты лежат в `tests/Browser`, наследуются от `DuskTestCase`.
- Запуск: `php artisan dusk`. Скриншоты падений — `tests/Browser/screenshots`.
- Установка: `composer require --dev laravel/dusk` + `php artisan dusk:install`.',
                'code_example' => '# Установка
composer require --dev laravel/dusk
php artisan dusk:install

# Создать тест
php artisan dusk:make LoginTest

# Запустить
php artisan dusk

# tests/Browser/LoginTest.php
class LoginTest extends DuskTestCase {
    public function test_user_can_login(): void {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit(\'/login\')
                ->type(\'email\', $user->email)
                ->type(\'password\', \'password\')
                ->press(\'Войти\')
                ->assertPathIs(\'/dashboard\')
                ->assertSee("Привет, {$user->name}");
        });
    }
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.testing',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Сравните Laravel Telescope и Laravel Pulse.',
                'answer' => '**Это два разных инструмента под две разные задачи.**

| | `Telescope` | `Pulse` |
| --- | --- | --- |
| Назначение | **Детальный профайлер** для dev/staging | **Агрегированный мониторинг** для прода |
| Что пишет | Каждый запрос/SQL/job/mail/cache/view **построчно** | **Агрегаты** за окно (top slow routes, slow queries, нагрузка) |
| Хранилище | Таблицы `telescope_entries`, `telescope_entries_tags` | Redis + таблицы `pulse_*` (агрегированные счётчики) |
| Overhead | **Значимый** — лишний INSERT на каждое событие | **Низкий** — буферизация через Redis ingest |
| Дашборд | Развёрнутая трасса каждого события | Live-плитки с агрегатами |

**Правило выбора:**

- **Telescope** — локально и на staging, для отладки и расследования инцидентов «по горячим следам». На проде **гасить** через `TELESCOPE_ENABLED=false` и/или ограничивать gate-ом доступа.
- **Pulse** — на проде в реальном времени: видно slow routes/queries, активных пользователей, hit-rate кэша, длину очередей, нагрузку воркеров.

**Совместимость:** часто стоят оба пакета; Telescope включают по необходимости (через флаг), Pulse работает постоянно. Не забыть `telescope:prune` (раз в сутки) и `pulse:clean` — иначе таблицы пухнут.',
                'code_example' => '# Telescope - dev only
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate

# .env
TELESCOPE_ENABLED=true       # dev/staging
# TELESCOPE_ENABLED=false    # production (отключить, чтоб не писало)

# или гейтинг через TelescopeServiceProvider::gate()
Gate::define("viewTelescope", fn ($user) => $user?->is_admin);

# В schedule - удалять старые записи
Schedule::command("telescope:prune --hours=48")->daily();

# Pulse - production
composer require laravel/pulse
php artisan vendor:publish --tag=pulse-config
php artisan migrate

# pulse:check периодически собирает метрики
# в schedule:
Schedule::command("pulse:check")->everyMinute();
Schedule::command("pulse:clean --before=\\"7 days ago\\"")->daily();',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'laravel.testing',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Почему Laravel Telescope не рекомендуется держать включённым на продакшене?',
                'answer' => '**Telescope пишет каждое событие построчно** — на нагруженном проде это превращается в боль.

**Что именно пишет в `telescope_entries`:**

- Каждый **HTTP-запрос** с URL/method/status/headers/payload.
- Каждый **SQL-query** с bindings.
- Каждый **диспатченный job** с serialized payload.
- Каждое **отправленное письмо** с получателем и body.
- Каждое **exception** со stack trace.
- Каждое **обращение к кешу** (get/put/forget).

**Главные проблемы на проде:**

| Проблема | Последствие |
|---|---|
| **Раздувание БД** | Таблица `telescope_entries` растёт на GB/день при нормальной нагрузке |
| **Тормоза** | Лишний `INSERT` на каждое событие → +5-20% latency запросов |
| **Утечка PII** | Payload содержит email/password/credit card → попадает в БД и backup |
| **Дублирование секретов** | API-ключи в HTTP-запросах сохраняются в telescope_entries |
| **Доступ через `/telescope`** | Если забыли gate — публичный доступ к внутренней кухне |

**Правильная стратегия на проде:**

1. **`TELESCOPE_ENABLED=false`** в `.env.production` — выключить совсем.
2. Если **нужно временно** включить для диагностики:
   - **`Telescope::filter(fn ($entry) => ...)`** — сэмплить (например, 10% запросов).
   - **`Telescope::auth(fn ($user) => $user?->isAdmin())`** — обязательный gate.
   - Расписать **`schedule->command("telescope:prune --hours=48")`** в cron.
3. Для **постоянного мониторинга — Laravel Pulse**: легче, считает агрегаты, не пишет каждое событие.

**Сравнение:**

| | **Telescope** | **Pulse** |
|---|---|---|
| Назначение | **Debug в dev** | **Мониторинг в prod** |
| Запись | Каждое событие | Агрегаты с sampling |
| Объём | GB/день | MB/день |
| Production | **Не рекомендуется** | Дефолтный путь |',
                'code_example' => '<?php
// .env.production
TELESCOPE_ENABLED=false

// Если временно включаем - обязательно ограничения
// app/Providers/TelescopeServiceProvider.php

public function register(): void
{
    Telescope::night();   // тёмная тема :)

    // Sampling - только 10% запросов
    Telescope::filter(function (IncomingEntry \$entry) {
        if (app()->isLocal()) return true;
        return \$entry->isReportableException()
            || \$entry->isFailedRequest()
            || \$entry->isFailedJob()
            || \$entry->isSlowQuery()
            || random_int(1, 100) <= 10;
    });
}

// Gate доступа
protected function gate(): void
{
    Gate::define("viewTelescope", fn (\$user) => \$user?->is_admin);
}

// routes/console.php - автопрюнинг
Schedule::command("telescope:prune --hours=48")->daily();',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.testing',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Debugbar и стоит ли использовать его в продакшене?',
                'answer' => '**`barryvdh/laravel-debugbar`** — community-пакет, выводящий **toolbar** внизу страницы со SQL-запросами (+ EXPLAIN), route-инфой, view-данными, событиями, кешем, переменными окружения, дамперами и таймингами.

**Чем полезен в dev:**

- Сразу видно **N+1** (дублирующиеся SELECT-ы).
- Видно, какие **данные передаются во view**.
- Помогает отлавливать тормоза по реальной картинке, а не по логам.

**На продакшене — категорически нельзя:**

- **Тормозит** каждый запрос (сбор всех SQL/event/view).
- **Раздувает HTML** ответа на десятки КБ.
- **Раскрывает внутренности**: имена таблиц, SQL, пути файлов — готовая разведка для атакующего.
- Может **слить переменные окружения** наружу (ключи API, секреты).

**Что делать правильно:**

- Ставить как **dev-зависимость**: `composer require --dev barryvdh/laravel-debugbar`. На проде `composer install --no-dev` физически **не положит пакет**.
- Toolbar активируется только при `APP_DEBUG=true` — на проде **должен быть `false`** (иначе ещё и Whoops отдаст stack-trace).
- Для прода — `Laravel Pulse` (агрегаты без построчных дампов).',
                'code_example' => '# Установка - ТОЛЬКО как dev-зависимость
composer require barryvdh/laravel-debugbar --dev

# .env
APP_ENV=local
APP_DEBUG=true        # toolbar появится только при true
DEBUGBAR_ENABLED=true # альтернатива - явный флаг

# .env.production - оба должны быть false
APP_ENV=production
APP_DEBUG=false
# Debugbar не загрузится, так как стоит --dev (нет в composer install --no-dev)

# Дамп с подсветкой прямо в toolbar
debugbar()->info("user", $user);
debugbar()->error("Bad thing");
debugbar()->addMeasure("db", $start, microtime(true));

# Игнорировать AJAX-запросы (по умолчанию пишет и их)
// config/debugbar.php
"capture_ajax" => false,',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'laravel.testing',
            ],
        ];
    }
}
