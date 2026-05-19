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
                'answer' => 'Подменка, которая ЗАПИСЫВАЕТ все вызовы — какие методы, с какими аргументами, сколько раз. В отличие от mock-а, ожидания проверяются ПОСЛЕ действия, а не задаются заранее. То есть «сначала сделай, потом я проверю, что ты сделал». В Laravel Mail::fake() работает как spy — записывает отправленные письма для последующих assertSent().',
                'code_example' => "<?php\n\nuse Illuminate\\Support\\Facades\\Bus;\nuse App\\Jobs\\SendReportJob;\n\npublic function test_export_dispatches_report_job(): void\n{\n    Bus::fake(); // ведёт себя как spy для очередей\n\n    \$this->post('/exports');\n\n    // Проверяем ПОСЛЕ действия\n    Bus::assertDispatched(SendReportJob::class, 1);\n}",
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
                'answer' => 'Stub отвечает на вопрос «какие данные вернёт зависимость» — это state-based testing. Mock отвечает «как мой код пользуется зависимостью» — это interaction-based testing. Stub: «верни 10». Mock: «должен быть вызван 1 раз». Часто используются вместе и часто путаются — современные фреймворки умеют и то, и то.',
                'difficulty' => 3,
                'topic' => 'testing.doubles',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Чем spy отличается от mock простыми словами?',
                'answer' => 'Mock задаёт ожидания ДО действия: «жду, что send() будет вызван 1 раз» — и сам падает в tearDown, если нет. Spy ожиданий не задаёт — он молча записывает все вызовы, а проверки делаются ПОСЛЕ действия отдельными assert-ами. Результат похож, но spy читается естественнее по AAA: arrange-act-assert идут по порядку, без «забегания вперёд».',
                'difficulty' => 3,
                'topic' => 'testing.doubles',
            ],
        ];
    }
}
