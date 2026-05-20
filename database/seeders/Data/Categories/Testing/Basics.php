<?php

namespace Database\Seeders\Data\Categories\Testing;

class Basics
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Тестирование',
                'question' => 'Зачем нужны автоматические тесты простыми словами?',
                'answer' => "**Автотест** — кусок кода, который сам проверяет другой кусок кода. Зачем нужны:\n\n- **Уверенность при изменениях** — поменял что-то, прогнал тесты, увидел что не сломал соседнее.\n- **Документация поведения** — читаешь имя теста (`user_can_login_with_valid_credentials`) и сразу видишь, как код должен работать.\n- **Быстрая обратная связь** — баг ловится в CI за секунды, а не на проде через неделю от пользователя.\n- **Защита от регрессий** — старая фича не ломается, когда добавляют новую.\n- **Экономия времени** — ручная проверка десятка сценариев занимает час, тесты — несколько секунд.\n\nБез тестов рефакторинг превращается в лотерею: «вроде работает, а ломаем ли мы что-то — узнаем от пользователей».",
                'code_example' => "<?php\n\n// Без теста: каждый раз руками открываешь браузер, логинишься, кликаешь\n// — и так на каждое изменение в коде логина\n\n// С автотестом: один раз написал, потом запускаешь одной командой\nuse PHPUnit\\Framework\\TestCase;\n\nfinal class LoginValidatorTest extends TestCase\n{\n    public function test_rejects_empty_password(): void\n    {\n        \$validator = new LoginValidator();\n\n        \$this->assertFalse(\$validator->isValid('user@example.com', ''));\n    }\n\n    public function test_accepts_valid_credentials(): void\n    {\n        \$validator = new LoginValidator();\n\n        \$this->assertTrue(\$validator->isValid('user@example.com', 'secret123'));\n    }\n}\n\n// Запуск: php artisan test  (или vendor/bin/phpunit)\n// Если кто-то сломает LoginValidator — тест покраснеет в CI",
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'testing.basics',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое unit-тест простыми словами?',
                'answer' => "**Unit-тест** — тест на одну маленькую единицу кода (метод, функция, класс) **в изоляции** от всего остального. БД, сеть, файлы, время не используются по-настоящему: либо они не нужны для этой логики, либо подменяются mock-ом/stub-ом.\n\nПризнаки хорошего unit-теста:\n\n- **Быстрый** — миллисекунды, тысячи штук прогоняются за пару секунд.\n- **Детерминированный** — на одном коде всегда даёт один результат.\n- **Локальный диагноз** — если упал, понятно, что баг именно в этой функции, а не «где-то в цепочке».\n\nТипичный кейс — **чистая логика без зависимостей**: калькулятор, валидатор, форматтер цены, парсер строки. Если в тесте есть `RefreshDatabase` или `\$this->get('/route')` — это уже не unit, а feature/integration.",
                'code_example' => "<?php\n\nuse PHPUnit\\Framework\\TestCase;\n\nfinal class DiscountCalculatorTest extends TestCase\n{\n    public function test_applies_percentage_discount(): void\n    {\n        \$calc = new DiscountCalculator();\n\n        // Никакой БД, HTTP, файлов — только чистая логика\n        \$this->assertSame(80.0, \$calc->apply(100.0, 0.2));\n    }\n\n    public function test_zero_discount_returns_original_price(): void\n    {\n        \$calc = new DiscountCalculator();\n\n        \$this->assertSame(100.0, \$calc->apply(100.0, 0.0));\n    }\n}",
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'testing.basics',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое integration-тест простыми словами?',
                'answer' => "**Integration-тест** проверяет, что несколько компонентов работают **вместе** как ожидается. Типичный набор: сервис + Eloquent-модель + реальная (но тестовая) БД, или контроллер + кеш + Redis.\n\n- **Медленнее unit-теста** (секунды, а не миллисекунды) — поднимается БД/инфраструктура.\n- **Ловит реальные стыковочные баги**: опечатка в SQL, неверная миграция, плохой `JOIN`, неправильная связь в relation, забытый индекс.\n- **Отличие от unit**: трогает «настоящие» границы (БД, файловая система, очередь).\n- **Отличие от feature**: обычно без HTTP-слоя сверху — это уже зона feature-теста.\n\nВ Laravel ставится трейт `RefreshDatabase`: реальная тестовая БД, миграции, `ROLLBACK` после теста.",
                'code_example' => "<?php\n\nnamespace Tests\\Feature;\n\nuse App\\Models\\User;\nuse App\\Services\\UserRegistrationService;\nuse Illuminate\\Foundation\\Testing\\RefreshDatabase;\nuse Tests\\TestCase;\n\nclass UserRegistrationServiceTest extends TestCase\n{\n    use RefreshDatabase; // настоящая тестовая БД, миграции, ROLLBACK после теста\n\n    public function test_registration_creates_user_with_hashed_password(): void\n    {\n        \$service = app(UserRegistrationService::class);\n\n        // Сервис → Eloquent → реальный INSERT в тестовую БД\n        \$user = \$service->register('jane@example.com', 'secret123');\n\n        \$this->assertDatabaseHas('users', ['email' => 'jane@example.com']);\n        \$this->assertTrue(password_verify('secret123', \$user->password));\n    }\n}",
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'testing.basics',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое e2e-тест простыми словами?',
                'answer' => "**End-to-end** («от края до края») — тест, проверяющий приложение целиком как живой пользователь: запускает настоящий браузер, открывает URL, кликает кнопки, заполняет формы, ждёт ответ, читает текст на странице.\n\n- **Самый медленный** — секунды-минуты на тест.\n- **Самый хрупкий** — упал, может быть и баг, и просто сеть моргнула.\n- **Самый реалистичный** — проверяет front + back + БД + JS вместе.\n\n**Инструменты**: `Cypress`, `Playwright`, `Selenium`, в Laravel — `Dusk` (запускает Chrome через ChromeDriver).\n\nПо **пирамиде тестирования** таких тестов держат немного — только критические user-flow: логин, оплата, регистрация. Всё остальное проверяют дешёвыми unit/feature-тестами.",
                'code_example' => "<?php\n\nnamespace Tests\\Browser;\n\nuse App\\Models\\User;\nuse Laravel\\Dusk\\Browser;\nuse Tests\\DuskTestCase;\n\nclass LoginTest extends DuskTestCase\n{\n    public function test_user_can_login_through_form(): void\n    {\n        \$user = User::factory()->create([\n            'email' => 'jane@example.com',\n            'password' => bcrypt('secret123'),\n        ]);\n\n        \$this->browse(function (Browser \$browser) {\n            \$browser->visit('/login')\n                ->type('email', 'jane@example.com')\n                ->type('password', 'secret123')\n                ->press('Войти')\n                ->assertPathIs('/dashboard')\n                ->assertSee('Jane');\n        });\n    }\n}",
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'testing.basics',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое feature-тест в Laravel простыми словами?',
                'answer' => 'Тест функциональности с точки зрения HTTP-запроса. Делает $this->get(\'/users\') или $this->post(\'/login\', [...]) и проверяет ответ + изменения в БД. Между unit и e2e: проходит через middleware, контроллеры, БД, но без браузера. Самый частый тип тестов в Laravel.',
                'code_example' => "<?php\n\nnamespace Tests\\Feature;\n\nuse App\\Models\\User;\nuse Illuminate\\Foundation\\Testing\\RefreshDatabase;\nuse Tests\\TestCase;\n\nclass LoginTest extends TestCase\n{\n    use RefreshDatabase;\n\n    public function test_user_can_login_with_valid_credentials(): void\n    {\n        \$user = User::factory()->create([\n            'email' => 'jane@example.com',\n            'password' => bcrypt('secret123'),\n        ]);\n\n        \$response = \$this->post('/login', [\n            'email' => 'jane@example.com',\n            'password' => 'secret123',\n        ]);\n\n        \$response->assertRedirect('/dashboard');\n        \$this->assertAuthenticatedAs(\$user);\n    }\n}",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'testing.basics',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое пирамида тестирования простыми словами?',
                'answer' => 'Картинка-метафора: внизу много дешёвых unit-тестов, посередине меньше integration, сверху совсем мало e2e. Дешёвые быстрые тесты ловят 80% багов, дорогие медленные — оставшиеся 20%. Антипаттерн «мороженое» — наоборот, много e2e + мало unit — медленный, хрупкий, ловит мало.',
                'difficulty' => 2,
                'topic' => 'testing.basics',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое assertion простыми словами?',
                'answer' => "**Assertion** — утверждение «должно быть так-то», которое тест проверяет. Если так — assert молча проходит дальше. Если не так — тест падает с понятным сообщением вида `Failed asserting that 4 is identical to 5`.\n\n**Важно**: без assert-ов тест ничего не проверяет — просто прогоняет код и всегда зелёный. Распространённая ошибка джунов.\n\nБазовые ассерты `PHPUnit`:\n\n- `assertSame` — строгое `===` (тип + значение)\n- `assertEquals` — нестрогое `==`\n- `assertTrue` / `assertFalse`\n- `assertNull`, `assertCount`, `assertInstanceOf`, `assertContains`\n\nВ Laravel сверху — **response-ассерты**: `assertStatus(200)`, `assertOk()`, `assertJson([...])`, `assertJsonFragment([...])`, `assertRedirect('/url')`, `assertSee('текст')`. И **database-ассерты**: `assertDatabaseHas`, `assertDatabaseCount`.",
                'code_example' => "<?php\n\npublic function test_basic_assertions(): void\n{\n    // assertSame — строгое === сравнение (тип + значение)\n    \$this->assertSame(5, 2 + 3);\n\n    // assertEquals — нестрогое == (5 == '5' пройдёт, assertSame — нет)\n    \$this->assertEquals('5', 2 + 3);\n\n    \$this->assertTrue(is_string('hello'));\n    \$this->assertFalse(empty([1]));\n    \$this->assertCount(3, [1, 2, 3]);\n    \$this->assertNull(null);\n    \$this->assertContains('php', ['php', 'go', 'rust']);\n}\n\npublic function test_laravel_response_assertions(): void\n{\n    \$response = \$this->get('/api/users/1');\n\n    \$response->assertStatus(200);                       // или assertOk()\n    \$response->assertJsonFragment(['name' => 'Jane']);\n    \$response->assertSee('Jane');\n}",
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'testing.basics',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое AAA в тестах простыми словами?',
                'answer' => 'Arrange-Act-Assert — структура хорошего теста. Arrange: подготовь данные ($user = User::factory()->create()). Act: выполни проверяемое действие ($response = $this->post(\'/login\')). Assert: проверь результат ($response->assertOk()). Каждая часть отделена пустой строкой — тест читается линейно.',
                'code_example' => "<?php\n\npublic function test_user_can_change_email(): void\n{\n    // Arrange — готовим данные\n    \$user = User::factory()->create(['email' => 'old@example.com']);\n    \$this->actingAs(\$user);\n\n    // Act — выполняем проверяемое действие\n    \$response = \$this->patch('/profile', [\n        'email' => 'new@example.com',\n    ]);\n\n    // Assert — проверяем результат\n    \$response->assertRedirect('/profile');\n    \$this->assertSame('new@example.com', \$user->fresh()->email);\n}",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'testing.basics',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое регрессионный тест простыми словами?',
                'answer' => 'Тест, написанный после того, как починили баг — чтобы баг больше не вернулся незаметно. Алгоритм: 1) Нашли баг. 2) Написали падающий тест, воспроизводящий баг. 3) Починили код — тест позеленел. 4) Этот тест остаётся в кодбазе навсегда и стоит на страже. Так растёт сеть тестов, защищающая от повторных регрессий.',
                'difficulty' => 2,
                'topic' => 'testing.basics',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое smoke-тест простыми словами?',
                'answer' => 'Быстрый поверхностный тест «работает ли вообще»: главная страница открывается (200), логин не падает, healthcheck отвечает. Цель — за секунды убедиться, что сборка не «дымится», прежде чем гонять полный прогон или после деплоя. Не заменяет нормальные тесты, а дополняет.',
                'difficulty' => 2,
                'topic' => 'testing.basics',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Black-box vs white-box тестирование простыми словами?',
                'answer' => 'Black-box: тестируем «снаружи», не зная как устроен код — только вход и выход (типичный feature-тест через HTTP). White-box: тестируем «изнутри», зная структуру кода — проверяем приватные ветки, конкретные методы (типичный unit-тест). Серая середина — gray-box: знаем устройство в общих чертах, но проверяем поведение.',
                'difficulty' => 2,
                'topic' => 'testing.basics',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Почему один тест должен проверять одну вещь?',
                'answer' => 'Когда тест проверяет 10 вещей и падает — непонятно, что именно сломалось, читаешь весь тест. Когда тест проверяет одно («регистрация отправляет welcome-email») и падает — диагноз по имени теста. Правило: один сценарий = один тест. Это не запрет на несколько assert-ов — assert-ы могут проверять разные аспекты одного сценария.',
                'difficulty' => 2,
                'topic' => 'testing.basics',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Как именовать тесты, чтобы они читались как документация?',
                'answer' => 'Имя теста = предложение про поведение. Плохо: testLogin, testUser1. Хорошо: test_user_can_login_with_valid_credentials, test_login_fails_with_wrong_password, test_guest_cannot_access_dashboard. Pattern «что_делает_кто_при_каких_условиях». В Pest: it(\'redirects guest to login\', ...). Когда видишь упавший тест в CI — по имени уже ясно, что сломалось.',
                'difficulty' => 2,
                'topic' => 'testing.basics',
            ],
        ];
    }
}
