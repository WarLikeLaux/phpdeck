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
                'answer' => 'Стандартный фреймворк для написания тестов на PHP, существует с 2004 года. Тест — это класс, наследующийся от PHPUnit\\Framework\\TestCase; методы-тесты называются с префикса «test» (test_user_can_login) или помечаются атрибутом #[Test]. Внутри теста вызываешь $this->assertSame(...) и другие assert-ы, чтобы проверить ожидаемое поведение. Запуск — vendor/bin/phpunit, конфиг — phpunit.xml в корне проекта (определяет, какие папки сканировать, как сетапить env). Используется в Laravel (под капотом php artisan test), Symfony, и почти любом современном PHP-проекте. Альтернатива — Pest, но он сам построен поверх PHPUnit.',
                'code_example' => "<?php\n\nuse PHPUnit\\Framework\\TestCase;\n\nclass CalculatorTest extends TestCase\n{\n    public function test_adds_two_numbers(): void\n    {\n        \$calc = new Calculator();\n\n        \$this->assertSame(5, \$calc->add(2, 3));\n    }\n\n    public function test_subtracts_two_numbers(): void\n    {\n        \$calc = new Calculator();\n\n        \$this->assertSame(1, \$calc->subtract(3, 2));\n    }\n}\n\n// Запуск всех тестов:\n//   vendor/bin/phpunit\n// Запуск одного класса:\n//   vendor/bin/phpunit tests/Unit/CalculatorTest.php",
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'testing.tools',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое Pest простыми словами?',
                'answer' => 'Современный PHP-фреймворк тестирования поверх PHPUnit, с более лаконичным синтаксисом: test(\'sum works\', fn() => expect(sum(1, 2))->toBe(3));. Без классов, без public function test*. Под капотом — PHPUnit, можно смешивать. Лёгкий, активно набирает популярность в Laravel-сообществе.',
                'code_example' => "<?php\n\nuse App\\Models\\User;\n\nit('redirects guest to login', function () {\n    \$this->get('/dashboard')->assertRedirect('/login');\n});\n\nit('lets authenticated user open dashboard', function () {\n    \$user = User::factory()->create();\n\n    \$this->actingAs(\$user)\n        ->get('/dashboard')\n        ->assertOk();\n});\n\ntest('calculator adds numbers', function () {\n    expect((new Calculator())->add(2, 3))->toBe(5);\n});",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'testing.tools',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Как запустить тесты в Laravel?',
                'answer' => 'Главная команда — php artisan test: запускает все тесты с красивым цветным выводом, показывает время каждого теста и итоговую сводку. Под капотом это всё тот же PHPUnit с phpunit.xml-конфигом, просто обёртка с лучшим UX. Полезные флаги: --filter UserTest — гнать только один класс или метод по имени; --testsuite=Feature — только определённую группу из phpunit.xml; --parallel — гнать параллельно в несколько процессов (сильно быстрее на больших наборах, но требует RefreshDatabase или DatabaseTransactions); --stop-on-failure — остановиться на первой ошибке (удобно при отладке); --coverage — показать процент покрытия (нужен Xdebug или PCOV). До php artisan test люди звали vendor/bin/phpunit напрямую — и сейчас так можно, просто без красивого вывода.',
                'code_example' => "# Все тесты\nphp artisan test\n\n# Только Feature-папка\nphp artisan test --testsuite=Feature\n\n# Один класс или метод по имени\nphp artisan test --filter UserTest\nphp artisan test --filter test_user_can_login\n\n# Параллельный прогон (быстрее на больших наборах)\nphp artisan test --parallel\n\n# Остановиться на первой ошибке — удобно при отладке\nphp artisan test --stop-on-failure\n\n# Показать процент покрытия кода тестами\nphp artisan test --coverage\n\n# Старый способ напрямую через PHPUnit (без красивого вывода)\nvendor/bin/phpunit",
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'testing.tools',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Где лежат тесты в Laravel и как организованы?',
                'answer' => 'Всё лежит в папке tests/ в корне проекта. Внутри две главные подпапки из коробки: tests/Feature — тесты HTTP-эндпоинтов, БД, очередей; наследуют Tests\\TestCase, который поднимает полное Laravel-приложение (контейнер, конфиг, маршруты). tests/Unit — изолированные тесты классов; наследуют PHPUnit\\Framework\\TestCase БЕЗ Laravel-бутстрапа, поэтому стартуют быстрее и не дают пользоваться хелперами фреймворка. Базовый класс для Feature-тестов — Tests\\TestCase (tests/TestCase.php), там подключён трейт CreatesApplication. Конфиг тестов — phpunit.xml в корне: задаёт переменные окружения (APP_ENV=testing, DB_CONNECTION=sqlite, DB_DATABASE=:memory:), список папок-сьютов, бутстрап-файл. Имена файлов — *Test.php (UserTest.php, LoginTest.php) — иначе PHPUnit их не подхватит.',
                'code_example' => "tests/\n├── Feature/                  # тесты через Laravel (HTTP, БД, очереди)\n│   ├── Auth/\n│   │   ├── LoginTest.php\n│   │   └── RegistrationTest.php\n│   └── UserProfileTest.php\n├── Unit/                     # изолированные тесты классов\n│   ├── Services/\n│   │   └── DiscountCalculatorTest.php\n│   └── Support/\n│       └── PriceFormatterTest.php\n├── CreatesApplication.php    # трейт, поднимающий Laravel в тестах\n└── TestCase.php              # базовый класс для Feature-тестов\n\nphpunit.xml                    # конфиг: сьюты, env-переменные, бутстрап",
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'testing.tools',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое Mockery простыми словами?',
                'answer' => 'Библиотека для создания mock-объектов в PHP. Mockery::mock(PaymentGateway::class)->shouldReceive(\'charge\')->once()->andReturn(true). Часто используется вместе с PHPUnit (PHPUnit имеет встроенный mock, но Mockery более выразительный). В Laravel интегрирован «из коробки».',
                'code_example' => "<?php\n\nuse Mockery;\n\npublic function test_checkout_charges_gateway_once(): void\n{\n    \$gateway = Mockery::mock(PaymentGateway::class);\n    \$gateway->shouldReceive('charge')\n        ->once()\n        ->with(100)\n        ->andReturn(true);\n\n    \$service = new CheckoutService(\$gateway);\n\n    \$this->assertTrue(\$service->pay(100));\n}\n\nprotected function tearDown(): void\n{\n    Mockery::close(); // обязательная проверка ожиданий\n    parent::tearDown();\n}",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'testing.tools',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое Http::fake() в Laravel для тестов?',
                'answer' => 'Подменяет HTTP-клиент Laravel (Http::get/post/...) фейком — реальные внешние API не дёргаются в тестах. Можно задать заранее заготовленные ответы по URL-маске (поддерживается * как wildcard), последовательность ответов (Http::sequence() — первый вызов вернёт первый ответ, второй — второй), статус-коды и заголовки. После действия проверяем, что нужный запрос был сделан: Http::assertSent(), Http::assertNotSent(), Http::assertSentCount(n). Без аргументов Http::fake() блокирует ВСЕ исходящие запросы — удобно как «запрет на сеть» в тестовом классе. Аналоги: Bus::fake() для очередей, Event::fake() для событий, Mail::fake() для писем, Notification::fake() для уведомлений, Storage::fake() для файлов.',
                'code_example' => "<?php\n\nuse Illuminate\\Support\\Facades\\Http;\n\npublic function test_fetches_user_from_external_api(): void\n{\n    Http::fake([\n        // wildcard по доменам\n        'api.example.com/users/*' => Http::response(['id' => 42, 'name' => 'Jane'], 200),\n        // sequence: первый запрос вернёт 500, второй — 200\n        'api.example.com/status' => Http::sequence()\n            ->push(['ok' => false], 500)\n            ->push(['ok' => true], 200),\n    ]);\n\n    \$user = (new ExternalUserClient())->find(42);\n\n    \$this->assertSame('Jane', \$user->name);\n    Http::assertSent(fn (\$req) => \$req->url() === 'https://api.example.com/users/42'\n        && \$req->hasHeader('Authorization'));\n    Http::assertSentCount(1);\n    Http::assertNotSent(fn (\$req) => str_contains(\$req->url(), '/admin'));\n}",
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'testing.tools',
            ],
        ];
    }
}
