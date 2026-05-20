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
                'answer' => "**Test double** («тестовый дублёр», аналогия с каскадёрами в кино) — общее название для **подменок** настоящих объектов в тесте. Пять видов:\n\n- **`dummy`** — пустышка, передаётся только для параметра, не используется.\n- **`stub`** — возвращает заранее заданные значения (про **данные**).\n- **`mock`** — проверяет, как с ним взаимодействуют (про **вызовы**).\n- **`fake`** — упрощённая, но рабочая реализация (in-memory вместо настоящей БД).\n- **`spy`** — молча записывает все вызовы, проверки делаются **после** действия.\n\nЗачем вообще:\n\n- Настоящая БД/`Stripe`/`SMTP`/внешний API — **медленные, ненадёжные, дорогие** в тестах.\n- Дублёр работает мгновенно и **детерминированно**.\n- Позволяет проверить **граничные случаи** (`API` вернул `500`, `Stripe` отказал), которые не воспроизведёшь руками.",
                'difficulty' => 2,
                'topic' => 'testing.doubles',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое stub простыми словами?',
                'answer' => "**Stub** — подменка, которая **возвращает** заранее заданные значения и **ничего не проверяет**. Правило простое: «когда тебя вызовут — верни вот это».\n\nКогда применять:\n\n- Тестируемому коду нужны **данные** от зависимости (курс валюты от провайдера, профиль из API).\n- Нас интересует **итоговое состояние** (state-based testing), а не «как именно код позвал зависимость».\n- Не нужны проверки числа вызовов или порядка аргументов.\n\nКак выглядит в `PHPUnit`:\n\n- `\$this->createStub(MyClass::class)` — создаёт «тихий» дублёр.\n- `->method('foo')->willReturn(\$value)` — задаёт ответ.\n- `->method('foo')->willThrowException(new RuntimeException())` — заставить кинуть исключение (для тестирования ветки ошибки).\n\nЕсли в тесте есть `expects(\$this->once())` — это уже **`mock`**, а не stub.",
                'code_example' => "<?php\n\npublic function test_discount_uses_rate_from_provider(): void\n{\n    // Stub: возвращает заранее заданное значение, ничего не проверяет\n    \$provider = \$this->createStub(RateProvider::class);\n    \$provider->method('currentRate')->willReturn(0.2);\n\n    \$calculator = new DiscountCalculator(\$provider);\n\n    \$this->assertSame(80.0, \$calculator->apply(100.0));\n}",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'testing.doubles',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое mock простыми словами?',
                'answer' => "**Mock** — подменка, которая **проверяет, как с ней взаимодействуют**. Перед действием задаём ожидание: «жду, что `send()` будет вызван **ровно 1 раз** с e-mail `a@b.ru`». Если ожидание нарушено — тест **сам падает** в `tearDown()`.\n\nКогда применять:\n\n- Проверяем не итоговое состояние, а сам **факт вызова** (interaction-based testing).\n- Метод **не возвращает** ничего полезного — только что-то делает (отправляет письмо, кладёт job в очередь, дёргает внешний API).\n- Нужно убедиться, что код «правильно пользуется» зависимостью.\n\nКак выглядит в `PHPUnit`:\n\n- `\$this->createMock(Mailer::class)` — создаёт mock.\n- `->expects(\$this->once())` — ожидание числа вызовов (`once`, `never`, `exactly(3)`, `atLeastOnce`).\n- `->method('send')->with('a@b.ru', \$this->anything())` — ожидание аргументов.\n\n**Минус mock-ов**: тест жёстко привязан к **деталям реализации**. Поменяешь сигнатуру метода — рухнут все тесты с этим mock.",
                'code_example' => "<?php\n\npublic function test_register_sends_welcome_email(): void\n{\n    // Mock: задаём ожидание ДО действия — будет ли вызов и какой\n    \$mailer = \$this->createMock(Mailer::class);\n    \$mailer->expects(\$this->once())\n        ->method('send')\n        ->with(\$this->equalTo('a@b.ru'), \$this->anything());\n\n    \$service = new RegistrationService(\$mailer);\n    \$service->register('a@b.ru', 'pass');\n\n    // Ожидание проверяется автоматически в tearDown\n}",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'testing.doubles',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое fake простыми словами?',
                'answer' => "**Fake** — **рабочая, но упрощённая** реализация зависимости. От `stub` отличается тем, что в нём **есть логика** — он реально работает, просто без тяжёлой инфраструктуры.\n\nКлассические примеры:\n\n- **In-memory репозиторий** вместо настоящей БД — хранит сущности в массиве `\$users`, поддерживает `find`/`save`/`delete`, работает мгновенно.\n- **Fake-очередь** — складывает джобы в массив, не дёргает Redis.\n- **Fake-файлсистема** — пишет в виртуальный диск, ничего не остаётся на реальном.\n\nВ Laravel fake-ов целая обойма прямо из коробки:\n\n- `Storage::fake('public')` — подменяет диск виртуальным.\n- `Mail::fake()`, `Notification::fake()` — перехватывают рассылку.\n- `Bus::fake()`, `Queue::fake()`, `Event::fake()` — для джоб/событий.\n- `Http::fake([...])` — для исходящих HTTP-запросов.\n\nКаждый предоставляет **ассерты** (`Mail::assertSent`, `Storage::assertExists`) — то есть в Laravel `fake` обычно работает ещё и как `spy`.",
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
                'answer' => "**Dummy** — объект-**пустышка**, который передаётся в код только для **заполнения параметра**, но в тесте реально **не используется**.\n\nПример: конструктор требует `Logger`, но в этом сценарии логирование точно не вызывается — передаём dummy, который ничего не делает.\n\nПризнаки:\n\n- **Самый «лёгкий» вид double** — без поведения, без проверок.\n- Часто это просто пустая реализация интерфейса или `\$this->createStub(Logger::class)` без `willReturn`.\n- Если в коде неожиданно **вызовется** метод dummy — обычно прилетит исключение или `null`, и тест это покажет.\n\nКогда нужен: метод/класс требует зависимость по сигнатуре, но **эта ветка теста её не использует**. Через `dummy` чётко показываем читателю: «вот тут зависимость нерелевантна, не вглядывайся».",
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
