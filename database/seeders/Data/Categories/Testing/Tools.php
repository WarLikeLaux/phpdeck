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
                'answer' => 'Стандартный фреймворк для написания тестов на PHP. Тесты — это классы, наследующиеся от TestCase, методы которых начинаются с «test»: public function testCalculation(). Запуск через vendor/bin/phpunit. Используется в Laravel, Symfony, почти везде.',
                'code_example' => "<?php\n\nuse PHPUnit\\Framework\\TestCase;\n\nclass CalculatorTest extends TestCase\n{\n    public function test_adds_two_numbers(): void\n    {\n        \$calc = new Calculator();\n\n        \$this->assertSame(5, \$calc->add(2, 3));\n    }\n}",
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
                'answer' => 'php artisan test — запускает все тесты с красивым выводом. php artisan test --filter UserTest — только указанный класс/метод. php artisan test --parallel — параллельно (быстрее). Под капотом — phpunit с phpunit.xml-конфигом.',
                'code_example' => "# Все тесты\nphp artisan test\n\n# Только Feature-папка\nphp artisan test --testsuite=Feature\n\n# Один класс или метод по имени\nphp artisan test --filter UserTest\nphp artisan test --filter test_user_can_login\n\n# Параллельный прогон (быстрее на больших наборах)\nphp artisan test --parallel\n\n# Остановиться на первой ошибке — удобно при отладке\nphp artisan test --stop-on-failure",
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'testing.tools',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Где лежат тесты в Laravel и как организованы?',
                'answer' => 'В папке tests/. Внутри две главные подпапки: Feature (тесты HTTP-эндпоинтов, БД) и Unit (изолированные тесты классов). Конфиг — phpunit.xml. Базовый класс — TestCase, для Unit — обычный TestCase без Laravel-бутстрапа (быстрее).',
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
