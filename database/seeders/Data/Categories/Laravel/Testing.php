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
                'answer' => 'PHPUnit - стандартный фреймворк тестирования для PHP, тесты пишутся как методы класса. Pest - надстройка над PHPUnit с более лаконичным синтаксисом (как Jest для JS): тесты как функции, expect-API. Под капотом всё равно PHPUnit. Можно использовать оба одновременно.',
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
                'answer' => 'RefreshDatabase - перед запуском всего тест-сьюта мигрирует БД с нуля, каждый тест оборачивается в транзакцию и откатывается. DatabaseTransactions - не мигрирует, просто оборачивает каждый тест в транзакцию (требует чтобы БД была уже мигрирована). RefreshDatabase надёжнее - всегда чистая БД. DatabaseMigrations - запускает миграции для каждого теста (медленно).',
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
                'answer' => 'HTTP-тесты позволяют делать "фейковые" запросы к приложению без реального HTTP. Методы: get, post, put, delete, json, getJson, postJson. Помощники для assert: assertOk, assertStatus, assertRedirect, assertSee, assertJson, assertJsonStructure. Можно работать с auth: actingAs($user).',
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
                'answer' => 'Fake - это подмена реального сервиса фейковым для тестов, чтобы проверить, что нужный код был вызван, без побочных эффектов. Fakes: Mail::fake(), Queue::fake(), Notification::fake(), Event::fake(), Bus::fake(), Storage::fake(), Http::fake().',
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
                'answer' => 'Через фасады (Cache::shouldReceive), через Mockery (mock(), partialMock()), либо через подмену в контейнере ($this->instance(...)). Фасады удобно мокать сразу - они изначально проксируют через контейнер.',
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
                'answer' => 'Test doubles - объекты-заглушки для зависимостей в тестах. Классификация Мешароса (xUnit Patterns), популяризованная Фаулером ("Mocks Aren\'t Stubs"). 1) Dummy - просто заполняет параметр, никогда не используется. Когда метод требует Logger в конструкторе, но в этом тесте логирование не вызывается. 2) Stub - возвращает заранее заданные ответы (canned), НИКАКОГО verify по вызовам. Вопрос "что вернёт getUser(1)?" → "User Tom". State-based testing: проверяем итоговое состояние SUT, не вызовы. 3) Spy - как stub, но дополнительно ЗАПИСЫВАЕТ все вызовы (что вызвали, с какими аргументами, сколько раз). После теста ассерт делается ПОСТФАКТУМ: assertCalled / assertCalledWith. 4) Mock - заранее ОЖИДАЕТ конкретные вызовы (expect()), и сам провалит тест, если ожидание не выполнено или вызвано что-то лишнее. Behavior-based testing: проверяем взаимодействия. Главное отличие от spy: expectation задаётся ДО действия, проверяется автоматически. 5) Fake - рабочая, упрощённая реализация (in-memory репозиторий вместо БД, FakeMailer вместо SMTP). Фактически работает, но не подходит для прода (потеря данных при рестарте, нет транзакций). В Mockery (используется в Laravel): shouldReceive("foo")->andReturn(...) - stub; shouldReceive("foo")->once()->with(42) - mock; spy() + shouldHaveReceived(...) - spy. Senior-практика: предпочитать stubs/fakes для большинства тестов (прочнее к рефакторингу), mocks использовать когда взаимодействие ЯВЛЯЕТСЯ предметом теста (event dispatched, http request sent). Чрезмерное использование mocks даёт хрупкие тесты, ломающиеся при невинном рефакторинге.',
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
                'answer' => 'Data Provider - механизм запуска одного теста с разными наборами входных данных. Вместо копи-пасты теста под каждый случай (test_zero, test_negative, test_huge) пишется один тест, а данные подаются провайдером - PHPUnit выполнит тест по разу для каждого набора и в отчёте покажет каждый прогон отдельно. Если упал случай №3 - сразу видно какой именно. Типичные кейсы: проверка валидатора с десятками граничных значений, парсинг разных форматов строк, ассертится одна и та же логика на разных входах, табличные тесты (table-driven tests). PHPUnit: атрибут #[DataProvider("methodName")] на методе теста + статический метод-провайдер, возвращающий iterable массивов параметров. Можно key-by name каждого случая, чтобы в отчёте было читаемо. Можно использовать generator (yield) - удобно для больших или ленивых наборов. Pest: метод ->with([...]) на тесте, или ->with("dataset_name") + dataset("name", [...]) в Pest.php. Принимает массив, генератор, или замыкание. Senior-нюанс: data provider не имеет доступа к setUp() и контейнеру (его метод статический и вызывается ДО setUp), поэтому в нём нельзя создавать Eloquent-модели через factory - используйте closure-параметр в Pest или ленивый генератор в PHPUnit, чтобы создание объектов произошло уже в тесте. Тест с провайдером даёт параметризацию без потери читаемости и снижает дублирование.',
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
                'answer' => 'Когда логика зависит от now() / Carbon::now() (например, "токен живёт час", "напомнить через 3 дня", "запретить вход после 23:00") - в тесте нельзя ждать реальный час. Laravel даёт обёртки над Carbon::setTestNow() для управления временем. Основные хелперы (доступны в TestCase). 1) $this->travel(int $value)->{minutes()|hours()|days()|...} - сдвинуть текущее время на относительный интервал. 2) $this->travelTo(Carbon $instant) - перепрыгнуть на абсолютное время. 3) $this->travelBack() - вернуться к реальному времени (вызывается автоматически в tearDown, но можно явно для частичного теста). 4) $this->freezeTime() / $this->freezeSecond() - заморозить время (now() возвращает одно и то же значение между вызовами); удобно когда тест чувствителен к долям секунды. 5) В Pest - похожие методы, и есть expectation expect($carbon)->toBeBetween(...). Под капотом всё через Carbon::setTestNow($instant) - меняется глобально для всего приложения, включая Eloquent-таймстампы (created_at сохранится с этим временем). Подводные камни: 1) Если код использует time() или \\DateTime() напрямую - они НЕ подменятся, нужен Carbon. 2) При travel в БД-таймстампы попадает фейковое время - проверяйте, что это ОК для рассматриваемого теста. 3) В параллельных тестах travel влияет только на текущий процесс. Альтернатива более чистая - инжектить Clock-сервис в код (PSR-20 ClockInterface), и в тестах подавать FakeClock; но в Laravel-приложениях обычно используют travel.',
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
                'answer' => 'Фасад - это прокси к биндингу в Service Container: статика Cache::get() через __callStatic уходит в Container::make("cache") и зовёт ->get() на реальном объекте. У базового класса Facade есть методы swap($mock) / shouldReceive(...) / expects(...), которые подменяют биндинг в контейнере на Mockery-мок. shouldReceive создаёт stub - "если позвали, верни значение"; expects - mock с явным ожиданием ("должны позвать ровно один раз"). После теста PHPUnit/Pest TestCase автоматически вызывает Facade::clearResolvedInstances(), поэтому моки не утекают между тестами. Альтернатива - подмена через $this->instance() (если зависимость пробрасывается через DI, а не через фасад). Партиал-мок: $this->partialMock(Service::class)->shouldReceive("only-this-method")->andReturn(...) - остальные методы работают как настоящие.',
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
                'answer' => 'Dusk — пакет для end-to-end (browser) тестирования. Использует ChromeDriver и реальный браузер, эмулируя пользовательские действия: клики, ввод, ожидание элементов, скриншоты. В отличие от HTTP-тестов ($this->get()), Dusk выполняет настоящий JS — поэтому только им можно тестировать Livewire/Vue/React-фронтенд. Не требует ставить Selenium/JDK — ChromeDriver идёт в комплекте. Тесты лежат в tests/Browser, запускаются php artisan dusk.',
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
                'answer' => 'Это разные инструменты под разные задачи. Telescope - детальный отладочный профайлер для dev/staging: для КАЖДОГО запроса пишет в БД отдельные записи по каждому SQL-запросу, исключению, job, mail, cache-операции, view-рендеру - почти распределённая трассировка. Полезно при разработке и расследовании incident-а на staging, но генерирует тонну записей: telescope_entries раздувается за дни, на проде даёт значимый overhead (доп. INSERT-ы на каждое событие). Pulse - агрегированный мониторинг для продакшена в реальном времени: не пишет отдельную строку на каждое событие, а собирает агрегаты в Redis/БД (top slow routes за последний час, slow queries, активные пользователи, нагрузка серверов через php artisan pulse:check на cron, cache hit rate, очереди). Дёшево по записи и месту, дашборд показывает агрегаты, а не построчные трассы. Правило: Telescope - локально и на staging для отладки, Pulse - на проде для real-time мониторинга. Часто стоят оба, но Telescope гасят через ENABLED-флаг.',
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
                'answer' => 'Telescope пишет каждое событие (запрос, query, job, exception, mail) в отдельную таблицу telescope_entries — на нагруженном проде это быстро раздувает БД и тормозит запросы. Также записи содержат payload запросов и могут стать утечкой PII. На проде включают только при необходимости, ограничивают через TelescopeServiceProvider::filter и регулярно запускают telescope:prune; для постоянного мониторинга используют Pulse.',
                'difficulty' => 4,
                'topic' => 'laravel.testing',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Debugbar и стоит ли использовать его в продакшене?',
                'answer' => 'Debugbar (barryvdh/laravel-debugbar) - community-пакет, выводящий toolbar внизу страницы с временем выполнения, SQL-запросами и их EXPLAIN, route-инфой, view-данными, событиями, кешем, переменными окружения, dumper-ами. Очень удобен для отладки N+1 (видно дублирующиеся запросы) и понимания, какие данные передаются во view. Включать ТОЛЬКО локально - dev-зависимость в composer.json и app.debug=true в .env. На продакшене категорически нельзя: 1) Замедляет каждый запрос (собирает данные о каждом SQL/event/view). 2) Раздувает HTML ответа на десятки КБ. 3) Раскрывает структуру приложения: SQL-запросы, имена таблиц, пути файлов - готовая разведка для атакующего. 4) Может выдать значения переменных окружения наружу (включая ключи). По умолчанию активируется только если APP_DEBUG=true; на проде APP_DEBUG обязан быть false (это отдельная боль - debug-режим прода = утечка stack-trace через Whoops). Безопасная альтернатива для прода - Laravel Pulse (агрегаты, без построчного дампа).',
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
