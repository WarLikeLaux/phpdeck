<?php

namespace Database\Seeders\Data\Categories\Testing;

class Doubles
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Тестирование',
                'question' => 'Что такое test double простыми словами?',
                'answer' => 'Общее название для «подменок» настоящих объектов в тесте: mock, stub, fake, spy, dummy. Зачем: настоящая БД/API/Stripe медленные, ненадёжные, дорогие. В тесте подсовываем поддельный объект, который имитирует поведение и позволяет проверить, как наш код с ним взаимодействует.',
                'difficulty' => 2,
                'topic' => 'testing.doubles',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое stub простыми словами?',
                'answer' => 'Подменка, которая ВОЗВРАЩАЕТ заранее заданные значения, не делая ничего реального. «Когда тебя вызовут с такими-то аргументами, верни вот это». Используется, когда тестируемому коду нужны данные от зависимости, а нас не интересует, как он их получил.',
                'code_example' => "<?php\n\npublic function test_discount_uses_rate_from_provider(): void\n{\n    // Stub: возвращает заранее заданное значение, ничего не проверяет\n    \$provider = \$this->createStub(RateProvider::class);\n    \$provider->method('currentRate')->willReturn(0.2);\n\n    \$calculator = new DiscountCalculator(\$provider);\n\n    \$this->assertSame(80.0, \$calculator->apply(100.0));\n}",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'testing.doubles',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое mock простыми словами?',
                'answer' => 'Подменка, которая ПРОВЕРЯЕТ, как с ней взаимодействуют. Можно сказать: «ожидаю, что метод send() будет вызван 1 раз с email \'a@b.ru\'». Если ожидание нарушено — тест падает. Используется, чтобы проверить «правильно ли мой код пользуется зависимостью».',
                'code_example' => "<?php\n\npublic function test_register_sends_welcome_email(): void\n{\n    // Mock: задаём ожидание ДО действия — будет ли вызов и какой\n    \$mailer = \$this->createMock(Mailer::class);\n    \$mailer->expects(\$this->once())\n        ->method('send')\n        ->with(\$this->equalTo('a@b.ru'), \$this->anything());\n\n    \$service = new RegistrationService(\$mailer);\n    \$service->register('a@b.ru', 'pass');\n\n    // Ожидание проверяется автоматически в tearDown\n}",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'testing.doubles',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое fake простыми словами?',
                'answer' => 'Полноценная, но УПРОЩЁННАЯ реализация. Например, in-memory репозиторий вместо настоящей БД — хранит данные в массиве, работает быстро, не требует БД. В Laravel — Storage::fake(), Mail::fake(), Bus::fake() — подменяют сервисы fake-версией с возможностью проверки.',
                'code_example' => "<?php\n\nuse Illuminate\\Support\\Facades\\Mail;\nuse App\\Mail\\WelcomeMail;\n\npublic function test_register_sends_welcome_mail(): void\n{\n    Mail::fake(); // вместо реальной отправки — fake-реализация\n\n    \$this->post('/register', [\n        'email' => 'a@b.ru',\n        'password' => 'secret123',\n    ]);\n\n    Mail::assertSent(WelcomeMail::class, fn (\$m) => \$m->hasTo('a@b.ru'));\n}",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'testing.doubles',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое spy простыми словами?',
                'answer' => 'Подменка, которая молча ЗАПИСЫВАЕТ все вызовы — какие методы, с какими аргументами, сколько раз — и позволяет проверить это ПОСЛЕ действия, отдельными assert-ами. В отличие от mock-а, ожидания не задаются заранее: «сначала сделай, потом я проверю, что ты сделал». Это даёт читаемый AAA-порядок (arrange-act-assert) и не привязывает тест к точным деталям интеракции до запуска. Минус — assert-ы можно случайно забыть, и тест останется зелёным при отсутствии важного вызова. В Laravel Mail::fake(), Bus::fake(), Queue::fake(), Event::fake() работают именно как spy — записывают вызовы, чтобы потом assertSent/assertDispatched проверили факт.',
                'code_example' => "<?php\n\nuse Illuminate\\Support\\Facades\\Mail;\nuse App\\Mail\\WelcomeMail;\n\npublic function test_registration_sends_welcome_email_to_user(): void\n{\n    Mail::fake(); // spy: молча запишет все Mail::send() / Mailable::queue()\n\n    \$this->post('/register', [\n        'email' => 'jane@example.com',\n        'password' => 'secret123',\n        'password_confirmation' => 'secret123',\n    ]);\n\n    // assert ПОСЛЕ действия — все три проверки делает spy\n    Mail::assertSent(WelcomeMail::class, 1);\n    Mail::assertSent(WelcomeMail::class, fn (\$m) => \$m->hasTo('jane@example.com'));\n    Mail::assertNotSent(PasswordResetMail::class);\n}",
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'testing.doubles',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое dummy простыми словами?',
                'answer' => 'Объект-«пустышка», который передаётся только для заполнения параметра, но в тесте не используется. Например, метод требует Logger, но в этом сценарии логирование не вызывается — передаём dummy-logger, который ничего не делает. Самый «лёгкий» вид double — без поведения и проверок.',
                'difficulty' => 2,
                'topic' => 'testing.doubles',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Чем mock отличается от stub простыми словами?',
                'answer' => 'Stub — про ДАННЫЕ: подсовывает результат, чтобы тестируемый код мог продолжить работу (state-based testing — проверяем итоговое состояние). Mock — про ВЗАИМОДЕЙСТВИЕ: задаёт ожидание, что метод будет вызван конкретное число раз с конкретными аргументами, и сам падает, если ожидание нарушено (interaction-based testing). Правило: если падающий assert проверяет «что вернулось» — stub; если проверяет «что был вызов» — mock. Один объект может играть обе роли (PHPUnit/Mockery умеют это в одном createMock()), но смешивать дорого: тест становится про «и данные, и звонки» — растёт сцепление с реализацией.',
                'code_example' => "<?php\n\n// STUB: интересует только возвращаемое значение\n\$rates = \$this->createStub(RateProvider::class);\n\$rates->method('current')->willReturn(0.2);\n\n\$calc = new DiscountCalculator(\$rates);\n\$this->assertSame(80.0, \$calc->apply(100.0)); // проверяем СОСТОЯНИЕ\n\n// MOCK: интересует факт и параметры вызова\n\$mailer = \$this->createMock(Mailer::class);\n\$mailer->expects(\$this->once())\n    ->method('send')\n    ->with('a@b.ru', \$this->anything());\n\n(new RegistrationService(\$mailer))->register('a@b.ru', 'pass');\n// падение в tearDown, если send() не был вызван ровно 1 раз",
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'testing.doubles',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Чем spy отличается от mock простыми словами?',
                'answer' => 'Mock задаёт ожидания ДО действия («жду, что send() будет вызван 1 раз с таким аргументом») и сам падает в tearDown, если ожидание не выполнено. Spy ожиданий не задаёт — он молча записывает все вызовы, а проверки делаются ПОСЛЕ действия отдельными assert-ами. Результат похож, но spy читается естественнее по AAA: arrange-act-assert идут по порядку, без «забегания вперёд» с настройкой ожиданий. На практике в Laravel Bus::fake(), Mail::fake(), Queue::fake() — это spy: настройка → действие → assertDispatched/assertSent после.',
                'code_example' => "<?php\n\nuse Illuminate\\Support\\Facades\\Bus;\nuse App\\Jobs\\SendReportJob;\n\n// MOCK-стиль: ожидание ДО действия\n\$bus = Mockery::mock(Dispatcher::class);\n\$bus->shouldReceive('dispatch')->once()->with(Mockery::type(SendReportJob::class));\n// ... action ...\n// падает в tearDown, если dispatch() не был вызван\n\n// SPY-стиль: assert ПОСЛЕ действия — естественный AAA\nBus::fake();\n\n\$this->post('/exports');\n\nBus::assertDispatched(SendReportJob::class, 1);\nBus::assertDispatched(SendReportJob::class, fn (\$job) => \$job->userId === 42);",
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'testing.doubles',
            ],
        ];
    }
}
