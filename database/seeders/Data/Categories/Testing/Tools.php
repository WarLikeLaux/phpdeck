<?php

namespace Database\Seeders\Data\Categories\Testing;

class Tools
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Тестирование',
                'question' => 'Что такое PHPUnit простыми словами?',
                'answer' => "**`PHPUnit`** — стандартный фреймворк для написания тестов на PHP, существует с 2004 года.\n\n- **Тест** — класс, наследующийся от `PHPUnit\\Framework\\TestCase`.\n- **Метод-тест** — называется с префикса `test` (`test_user_can_login`) или помечается атрибутом `#[Test]`.\n- **Проверки** — внутри теста вызываешь `\$this->assertSame(...)` и другие assert-ы.\n- **Запуск** — `vendor/bin/phpunit`.\n- **Конфиг** — `phpunit.xml` в корне проекта (какие папки сканировать, как сетапить env).\n\nИспользуется в Laravel (под капотом `php artisan test`), Symfony и почти любом современном PHP-проекте. Альтернатива — `Pest`, но он сам построен поверх `PHPUnit`.",
                'code_example' => "<?php\n\nuse PHPUnit\\Framework\\TestCase;\n\nclass CalculatorTest extends TestCase\n{\n    public function test_adds_two_numbers(): void\n    {\n        \$calc = new Calculator();\n\n        \$this->assertSame(5, \$calc->add(2, 3));\n    }\n\n    public function test_subtracts_two_numbers(): void\n    {\n        \$calc = new Calculator();\n\n        \$this->assertSame(1, \$calc->subtract(3, 2));\n    }\n}\n\n// Запуск всех тестов:\n//   vendor/bin/phpunit\n// Запуск одного класса:\n//   vendor/bin/phpunit tests/Unit/CalculatorTest.php",
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'testing.tools',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое Pest простыми словами?',
                'answer' => "**`Pest`** — современный PHP-фреймворк тестирования **поверх `PHPUnit`** с более лаконичным синтаксисом, вдохновлённым `Jest` / `RSpec`.\n\nГлавные отличия от `PHPUnit`:\n\n- **Без классов и `public function test*()`** — пишешь `test('name', fn () => ...)` или `it('does X', fn () => ...)`.\n- **Цепочки `expect()`** вместо `\$this->assert*()`: `expect(\$sum)->toBe(5)->toBeInt()`.\n- **Хуки** `beforeEach()` / `afterEach()` прямо в файле, без классов.\n- **Datasets** — лаконичная замена `@dataProvider`.\n- **Higher-order tests** — `it('redirects guest', ...)->expectsRedirect('/login')`.\n\nВажные факты:\n\n- Под капотом **тот же `PHPUnit`** — можно **смешивать**: Pest-тесты и классические `PHPUnit`-классы в одном проекте работают вместе.\n- Запуск: `./vendor/bin/pest` или **`php artisan test`** (он узнаёт оба формата).\n- В новых Laravel-проектах (с L11) Pest предлагается как опция при `laravel new`.",
                'code_example' => "<?php\n\nuse App\\Models\\User;\n\nit('redirects guest to login', function () {\n    \$this->get('/dashboard')->assertRedirect('/login');\n});\n\nit('lets authenticated user open dashboard', function () {\n    \$user = User::factory()->create();\n\n    \$this->actingAs(\$user)\n        ->get('/dashboard')\n        ->assertOk();\n});\n\ntest('calculator adds numbers', function () {\n    expect((new Calculator())->add(2, 3))->toBe(5);\n});",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'testing.tools',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Как запустить тесты в Laravel?',
                'answer' => "Главная команда — **`php artisan test`**: запускает все тесты с красивым цветным выводом, показывает время каждого теста и итоговую сводку. Под капотом — тот же `PHPUnit` с `phpunit.xml`-конфигом, просто обёртка с лучшим UX.\n\nПолезные флаги:\n\n- `--filter UserTest` — гнать только один класс или метод по имени.\n- `--testsuite=Feature` — только определённую группу из `phpunit.xml`.\n- `--parallel` — гнать параллельно в несколько процессов (быстрее на больших наборах, но требует `RefreshDatabase` или `DatabaseTransactions`).\n- `--stop-on-failure` — остановиться на первой ошибке (удобно при отладке).\n- `--coverage` — показать процент покрытия (нужен `Xdebug` или `PCOV`).\n\nДо `php artisan test` звали `vendor/bin/phpunit` напрямую — и сейчас так можно, просто без красивого вывода.",
                'code_example' => "# Все тесты\nphp artisan test\n\n# Только Feature-папка\nphp artisan test --testsuite=Feature\n\n# Один класс или метод по имени\nphp artisan test --filter UserTest\nphp artisan test --filter test_user_can_login\n\n# Параллельный прогон (быстрее на больших наборах)\nphp artisan test --parallel\n\n# Остановиться на первой ошибке — удобно при отладке\nphp artisan test --stop-on-failure\n\n# Показать процент покрытия кода тестами\nphp artisan test --coverage\n\n# Старый способ напрямую через PHPUnit (без красивого вывода)\nvendor/bin/phpunit",
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'testing.tools',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Где лежат тесты в Laravel и как организованы?',
                'answer' => "Всё лежит в папке **`tests/`** в корне проекта. Внутри две подпапки из коробки:\n\n- **`tests/Feature/`** — тесты HTTP-эндпоинтов, БД, очередей. Наследуют `Tests\\TestCase`, который поднимает полное Laravel-приложение (контейнер, конфиг, маршруты).\n- **`tests/Unit/`** — изолированные тесты классов. Наследуют `PHPUnit\\Framework\\TestCase` **без** Laravel-бутстрапа, поэтому стартуют быстрее и не дают пользоваться хелперами фреймворка.\n\nКлючевые файлы:\n\n- `tests/TestCase.php` — базовый класс для Feature-тестов, подключён трейт `CreatesApplication`.\n- `phpunit.xml` в корне — конфиг тестов: env-переменные (`APP_ENV=testing`, `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`), список сьютов, бутстрап-файл.\n\n**Имена файлов** — `*Test.php` (`UserTest.php`, `LoginTest.php`), иначе `PHPUnit` их не подхватит.",
                'code_example' => "tests/\n├── Feature/                  # тесты через Laravel (HTTP, БД, очереди)\n│   ├── Auth/\n│   │   ├── LoginTest.php\n│   │   └── RegistrationTest.php\n│   └── UserProfileTest.php\n├── Unit/                     # изолированные тесты классов\n│   ├── Services/\n│   │   └── DiscountCalculatorTest.php\n│   └── Support/\n│       └── PriceFormatterTest.php\n├── CreatesApplication.php    # трейт, поднимающий Laravel в тестах\n└── TestCase.php              # базовый класс для Feature-тестов\n\nphpunit.xml                    # конфиг: сьюты, env-переменные, бутстрап",
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'testing.tools',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое Mockery простыми словами?',
                'answer' => "**`Mockery`** — библиотека для создания **mock-объектов** в PHP. Более выразительная альтернатива встроенным mock-ам `PHPUnit` (`createMock`).\n\nТипичный API — цепочка:\n\n- **`Mockery::mock(PaymentGateway::class)`** — создать mock интерфейса/класса.\n- **`->shouldReceive('charge')`** — ожидаем вызов метода.\n- **`->once()`** / `->times(2)` / `->never()` — сколько раз.\n- **`->with(100, 'USD')`** — с какими аргументами (поддерживаются матчеры: `Mockery::any()`, `Mockery::type('int')`).\n- **`->andReturn(true)`** / `->andThrow(new RuntimeException())` — что вернуть.\n\nВажные моменты:\n\n- В Laravel-тестах **подключён из коробки** — `Tests\\TestCase` сам зовёт `Mockery::close()` в `tearDown()`, и ожидания проверяются автоматически.\n- В голом `PHPUnit` нужно вручную звать `Mockery::close()` в `tearDown()` — без этого ожидания не проверятся и тест останется зелёным.\n- Часто читается приятнее `createMock` — особенно когда у метода несколько разных ожиданий по аргументам.",
                'code_example' => "<?php\n\nuse Mockery;\n\npublic function test_checkout_charges_gateway_once(): void\n{\n    \$gateway = Mockery::mock(PaymentGateway::class);\n    \$gateway->shouldReceive('charge')\n        ->once()\n        ->with(100)\n        ->andReturn(true);\n\n    \$service = new CheckoutService(\$gateway);\n\n    \$this->assertTrue(\$service->pay(100));\n}\n\nprotected function tearDown(): void\n{\n    Mockery::close(); // обязательная проверка ожиданий\n    parent::tearDown();\n}",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'testing.tools',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое Http::fake() в Laravel для тестов?',
                'answer' => "**`Http::fake`** подменяет HTTP-клиент Laravel (`Http::get` / `Http::post` / ...) фейком — реальные внешние API **не дёргаются** в тестах. Тест становится быстрым, детерминированным и не зависит от того, что у `Stripe` сейчас за погода.\n\nЧто умеет задавать:\n\n- **Заготовленные ответы по URL-маске** — `*` как wildcard: `'api.example.com/users/*' => Http::response([...], 200)`.\n- **Последовательность ответов** — `Http::sequence()->push(...)->push(...)`: первый вызов вернёт первый ответ, второй — второй. Удобно для тестов **ретраев** (сначала `500`, потом `200`).\n- **Статус-коды, заголовки, тело** — `Http::response(\$body, \$status, \$headers)`.\n- **Исключения / таймауты** — `Http::failedConnection()`, бросок exception в `Http::sequence()` для проверки обработки ошибок.\n\nПроверки **после** действия (spy-стиль):\n\n- **`Http::assertSent(fn (\$req) => ...)`** — был ли нужный запрос (URL, метод, заголовки, тело).\n- **`Http::assertNotSent(...)`** — что запрос **не уходил**.\n- **`Http::assertSentCount(n)`** — ровно `n` запросов всего.\n- **`Http::assertSentInOrder([...])`** — что порядок запросов был именно такой.\n\nВажные нюансы:\n\n- **`Http::fake()` без аргументов** блокирует **все** исходящие запросы и возвращает на них пустой `200`-ответ — удобно как «запрет на сеть» в `setUp()` тестового класса.\n- На незамоканный URL **с** аргументами `Http::fake([...])` по умолчанию тоже вернётся пустой `200`, **не** реальный запрос — это иногда сюрпризит.\n- Параметр запроса доступен через `\$req->url()`, `\$req->method()`, `\$req->data()`, `\$req->hasHeader('Authorization')`.\n\nФейк-aliases того же семейства:\n\n- **`Bus::fake`** — джобы через `dispatch()`.\n- **`Queue::fake`** — джобы напрямую в очередь.\n- **`Event::fake`** — события.\n- **`Mail::fake`** — рассылки `Mailable`.\n- **`Notification::fake`** — уведомления (`assertSentTo(\$user, ...)`).\n- **`Storage::fake('public')`** — виртуальный диск.",
                'code_example' => "<?php\n\nuse Illuminate\\Support\\Facades\\Http;\n\nprotected function setUp(): void\n{\n    parent::setUp();\n    Http::preventStrayRequests(); // любой незамоканный URL → exception\n}\n\npublic function test_fetches_user_from_external_api(): void\n{\n    Http::fake([\n        // wildcard по доменам\n        'api.example.com/users/*' => Http::response(['id' => 42, 'name' => 'Jane'], 200),\n        // sequence: первый запрос вернёт 500, второй — 200 (тест ретрая)\n        'api.example.com/status' => Http::sequence()\n            ->push(['ok' => false], 500)\n            ->push(['ok' => true], 200),\n    ]);\n\n    \$user = (new ExternalUserClient())->find(42);\n\n    \$this->assertSame('Jane', \$user->name);\n    Http::assertSent(fn (\$req) => \$req->url() === 'https://api.example.com/users/42'\n        && \$req->method() === 'GET'\n        && \$req->hasHeader('Authorization'));\n    Http::assertSentCount(1);\n    Http::assertNotSent(fn (\$req) => str_contains(\$req->url(), '/admin'));\n}\n\npublic function test_retries_once_on_5xx(): void\n{\n    Http::fake([\n        'api.example.com/status' => Http::sequence()\n            ->push(['ok' => false], 500)\n            ->push(['ok' => true], 200),\n    ]);\n\n    \$result = (new StatusClient())->checkWithRetry();\n\n    \$this->assertTrue(\$result['ok']);\n    Http::assertSentCount(2); // первый упал, ретрай сработал\n}\n\npublic function test_no_network_at_all(): void\n{\n    Http::fake(); // без аргументов = глобальный запрет на сеть\n\n    (new AnalyticsClient())->track('signup'); // вернёт пустой 200\n\n    Http::assertSentCount(1);\n}",
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'testing.tools',
            ],
        ];
    }
}
