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
                'answer' => "**Feature-тест** — тест функциональности **с точки зрения HTTP-запроса**. Делает `\$this->get('/users')` или `\$this->post('/login', [...])` и проверяет ответ + изменения в БД.\n\nГде он на шкале:\n\n- **Между unit и e2e** — проходит через `middleware` → `controller` → `service` → БД, **но без браузера** (никакого JS).\n- **Самый частый тип тестов в Laravel** — даёт лучший баланс «реалистичность / скорость».\n\nТипичные проверки:\n\n- **Ответ**: `assertOk()`, `assertStatus(422)`, `assertRedirect('/dashboard')`, `assertJson([...])`.\n- **БД**: `assertDatabaseHas('users', [...])`, `assertDatabaseCount('orders', 1)`.\n- **Авторизация**: `assertAuthenticatedAs(\$user)`, `assertGuest()`.\n\nЛежит в `tests/Feature/`, наследует `Tests\\TestCase` (поднимает приложение), почти всегда — с трейтом `RefreshDatabase`.",
                'code_example' => "<?php\n\nnamespace Tests\\Feature;\n\nuse App\\Models\\User;\nuse Illuminate\\Foundation\\Testing\\RefreshDatabase;\nuse Tests\\TestCase;\n\nclass LoginTest extends TestCase\n{\n    use RefreshDatabase;\n\n    public function test_user_can_login_with_valid_credentials(): void\n    {\n        \$user = User::factory()->create([\n            'email' => 'jane@example.com',\n            'password' => bcrypt('secret123'),\n        ]);\n\n        \$response = \$this->post('/login', [\n            'email' => 'jane@example.com',\n            'password' => 'secret123',\n        ]);\n\n        \$response->assertRedirect('/dashboard');\n        \$this->assertAuthenticatedAs(\$user);\n    }\n}",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'testing.basics',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое пирамида тестирования простыми словами?',
                'answer' => "**Пирамида тестирования** — картинка-метафора, как должны соотноситься типы тестов в проекте:\n\n- **Внизу — много `unit`-тестов**: дешёвые, миллисекунды, ловят основную массу логических багов.\n- **Посередине — меньше `integration` / `feature`**: секунды, проверяют склейку (БД, HTTP, сервисы).\n- **Сверху — совсем мало `e2e` (`Dusk`, `Cypress`)**: десятки секунд, хрупкие, гоняем только критические user-flow (логин, оплата).\n\n**Идея**: дешёвые быстрые тесты ловят ~80% багов, дорогие медленные — оставшиеся ~20%.\n\n**Антипаттерн «мороженое»** (ice-cream cone) — наоборот: много `e2e` + мало `unit`. Прогон долгий, тесты flaky, диагноз падения — сложный.",
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
                'answer' => "**AAA** = **Arrange-Act-Assert** — структура хорошего теста, разбитая на три блока:\n\n- **Arrange** — подготовь данные: `\$user = User::factory()->create()`, `\$this->actingAs(\$user)`.\n- **Act** — выполни **одно** проверяемое действие: `\$response = \$this->post('/login', [...])`.\n- **Assert** — проверь результат: `\$response->assertRedirect('/dashboard')`, `assertDatabaseHas(...)`.\n\nПравила:\n\n- Каждый блок отделён **пустой строкой** — тест читается сверху вниз без напряжения.\n- В блоке `Act` — **одно действие**, не пять.\n- Если тяжело отделить `Arrange` от `Act` — обычно слишком много готовки, выноси в `setUp()` или фабрику.\n\nВ Pest часто пишут одной выразительной строкой, но логически блоки те же.",
                'code_example' => "<?php\n\npublic function test_user_can_change_email(): void\n{\n    // Arrange — готовим данные\n    \$user = User::factory()->create(['email' => 'old@example.com']);\n    \$this->actingAs(\$user);\n\n    // Act — выполняем проверяемое действие\n    \$response = \$this->patch('/profile', [\n        'email' => 'new@example.com',\n    ]);\n\n    // Assert — проверяем результат\n    \$response->assertRedirect('/profile');\n    \$this->assertSame('new@example.com', \$user->fresh()->email);\n}",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'testing.basics',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое регрессионный тест простыми словами?',
                'answer' => "**Регрессионный тест** — тест, написанный **после** того, как починили баг, чтобы баг **больше не вернулся незаметно**.\n\nАлгоритм при починке бага:\n\n1. Нашли баг (пришёл из прода или нашли сами).\n2. **Сначала** написали тест, который воспроизводит баг — он **падает**.\n3. Починили код — тест **позеленел**.\n4. Тест остаётся в кодбазе **навсегда** и стоит на страже.\n\nЗачем именно так:\n\n- Гарантируешь, что баг действительно был и действительно починен (а не «вроде работает»).\n- Через полгода кто-то рефакторит, случайно ломает ту же логику — тест ловит регрессию в CI.\n- Так растёт **сеть тестов**, защищающая от повторных багов.\n\nИмя теста — описание сценария бага: `test_login_does_not_lock_user_after_one_failed_attempt`.",
                'difficulty' => 2,
                'topic' => 'testing.basics',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое smoke-тест простыми словами?',
                'answer' => "**Smoke-тест** — быстрый поверхностный тест «работает ли приложение вообще». Название из электроники: включил плату — если **не дымится**, можно проверять дальше.\n\nТипичные проверки:\n\n- Главная страница отдаёт **`200`**.\n- `/login` открывается, форма видна.\n- `/healthcheck` отвечает.\n- Главная админка не падает с `500`.\n\nГде применяют:\n\n- **После деплоя** на staging/production — за 10 секунд понять, не разнесли ли мы прод.\n- **В CI перед полным прогоном** — если smoke упал, не имеет смысла гонять тысячу долгих тестов.\n\nSmoke **не заменяет** нормальные тесты, а **дополняет** — это первая линия защиты, а не вся.",
                'difficulty' => 2,
                'topic' => 'testing.basics',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Black-box vs white-box тестирование простыми словами?',
                'answer' => "Подход к написанию тестов с точки зрения того, **что мы знаем о внутреннем устройстве** проверяемого кода.\n\n- **Black-box** («чёрный ящик») — тестируем **снаружи**, не зная, как устроен код. Видим только **вход и выход**. Типичный пример — `feature`-тест через HTTP: `\$this->post('/login', [...])` → проверяем ответ и БД, **как** контроллер делает работу — нам всё равно.\n- **White-box** («белый ящик») — тестируем **изнутри**, зная структуру кода. Проверяем конкретные методы, приватные ветки, граничные условия. Типичный пример — `unit`-тест на класс.\n- **Gray-box** — компромисс: знаем устройство в общих чертах, но проверяем именно **поведение** через публичный API.\n\n**Правило джуна**: тесты привязывай к **поведению**, а не к деталям реализации — иначе любой рефакторинг ломает тесты, хотя поведение не изменилось.",
                'difficulty' => 2,
                'topic' => 'testing.basics',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Почему один тест должен проверять одну вещь?',
                'answer' => "**Правило**: один сценарий — один тест. Это **не** про «один `assert` на тест», а про **одну логическую проверку**.\n\nПочему так:\n\n- **Локальный диагноз**. Когда тест проверяет 10 вещей и падает — открывай файл, читай весь сценарий, ищи, что именно сломалось. Когда тест проверяет одно (`test_registration_sends_welcome_email`) — диагноз сразу из имени в CI.\n- **Имя как документация**. Один сценарий = одно осмысленное имя. Слепленные тесты не назовёшь нормально (`test_registration`).\n- **Независимость**. Сломали логику e-mail — падает один тест, остальные зелёные. Все три проверки в одном тесте — падает на первой, остальные **не выполнятся** и баги останутся скрытыми.\n\nНесколько `assert`-ов в одном тесте — это **нормально**, если они проверяют **разные аспекты одного сценария** (статус-код + JSON-структура + запись в БД для одного `POST /users`).",
                'difficulty' => 2,
                'topic' => 'testing.basics',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Как именовать тесты, чтобы они читались как документация?',
                'answer' => "**Имя теста = предложение про поведение системы.** Когда в CI красным горит имя теста — по нему уже должно быть ясно, **что** сломалось.\n\n**Плохо:**\n\n- `testLogin` — что именно про логин?\n- `testUser1`, `testCase2` — ни о чём.\n- `test_works` — слишком общо.\n\n**Хорошо:**\n\n- `test_user_can_login_with_valid_credentials`\n- `test_login_fails_with_wrong_password`\n- `test_guest_cannot_access_dashboard`\n- `test_order_total_includes_vat_when_user_is_in_eu`\n\n**Шаблон**: `<кто>_<что делает>_<при каких условиях>` через `snake_case`.\n\nВ `Pest` — то же самое, только описанием в кавычках: `it('redirects guest to login', fn () => ...)`.\n\n**Бонус**: красивые имена тестов — это бесплатная документация. `php artisan test --testdox` печатает их человекочитаемым списком.",
                'difficulty' => 2,
                'topic' => 'testing.basics',
            ],
        ];
    }
}
