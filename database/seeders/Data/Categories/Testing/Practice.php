<?php

namespace Database\Seeders\Data\Categories\Testing;

class Practice
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Тестирование',
                'question' => 'Что такое TDD простыми словами?',
                'answer' => "**TDD** = **Test-Driven Development** — техника, при которой **сначала пишешь тест, потом код**. Цикл называется **Red-Green-Refactor**:\n\n1. **Red** — написал тест на ещё несуществующее поведение. Запустил — упал (красный).\n2. **Green** — написал **минимальный** код, чтобы тест прошёл. Без перфекционизма, лишь бы зеленело.\n3. **Refactor** — почистил код (вынес метод, убрал дубль), тесты **всё ещё зелёные**.\n\nЗачем:\n\n- **Дизайн через интерфейс** — пишешь тест от лица будущего пользователя API, классы получаются удобными в использовании.\n- **Тесты гарантированно есть** — не «потом допишу» (никто не допишет).\n- **Маленькие шаги** — баги ловятся сразу, не накапливаются.\n\nМинусы:\n\n- Требует дисциплины и привычки.\n- Плохо подходит для UI / разведочного программирования, где сам ещё не знаешь, что делаешь.",
                'difficulty' => 2,
                'topic' => 'testing.practice',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое code coverage простыми словами?',
                'answer' => "**Code coverage** — **процент строк кода**, которые во время прогона тестов **хотя бы раз выполнились**. `80% coverage` = тесты прошли по `80%` строк.\n\nЗачем смотреть:\n\n- Видно **непокрытые ветки** — обычно именно там сидят будущие баги.\n- Помогает решить, где добить тестов в первую очередь.\n\nЧем метрика **обманчива**:\n\n- **`100%` coverage ≠ «нет багов»** — строка вызвалась, но условия `if/else` могли быть не проверены.\n- Тест без `assert`-ов даёт `100%` coverage и **ничего не проверяет**.\n- Coverage не отличает критичную логику оплаты от косметического форматирования.\n\nКак запускать:\n\n- `php artisan test --coverage` — текстовый отчёт.\n- Нужен **`Xdebug`** или **`PCOV`** (`PCOV` сильно быстрее для CI).\n\n**Правило джуна**: coverage — не цель, а **подсказка**. Цель — проверенное **поведение**.",
                'difficulty' => 2,
                'topic' => 'testing.practice',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое flaky test простыми словами?',
                'answer' => "**Flaky-тест** — тест, который **иногда проходит, иногда падает БЕЗ изменений в коде**. Самое худшее, что может быть в CI: команда **теряет доверие** к тестам и начинает «перезапускать билд, пока не позеленеет».\n\nТипичные причины:\n\n- **Время** — `now()`, `time()` в коде, тест на границе суток падает в полночь. Лечится `Carbon::setTestNow()` / `\$this->travelTo()`.\n- **Порядок тестов** — тест №2 зависит от данных теста №1. Лечится `RefreshDatabase`, изоляцией состояния.\n- **Внешние сервисы** — `Stripe` моргнул, `DNS` ответил с задержкой. Лечится `Http::fake()` / `Bus::fake()`.\n- **Случайные данные** — `faker` без seed выдал крайний случай. Лечится фиксацией данных в конкретном тесте.\n- **Race conditions** в очередях/параллельных тестах. Лечится `--parallel` с отдельной БД на токен и явной синхронизацией.\n\n**Правило**: упавший раз flaky-тест — **не «перезапусти и забудь»**, а тикет на расследование. Иначе скоро в CI будет 20 таких и любой деплой превращается в лотерею.",
                'difficulty' => 2,
                'topic' => 'testing.practice',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое fixture простыми словами?',
                'answer' => "**Fixture** («крепёж») — заранее подготовленный набор данных, в котором тест **стартует**. То есть **известное начальное состояние** мира до выполнения `Act`.\n\nПримеры:\n\n- БД с **5 юзерами и заказами** под них.\n- `JSON`-файл с ожидаемым payload-ом ответа внешнего API.\n- Тестовый PDF/картинка для проверки парсинга.\n\nРаньше хранили в `tests/fixtures/` (`users.yml`, `orders.json`) и грузили через `loadFixture('users.yml')`.\n\nВ Laravel классические fixture-файлы для **данных в БД** почти ушли — вместо них:\n\n- **`User::factory()->count(5)->create()`** — фабрика генерирует рандомные валидные данные.\n- **Database seeders** — `\$this->seed(TestDataSeeder::class)` прямо из теста.\n\nПочему лучше: фабрики **выразительнее** (`->hasPosts(3)->state(...)`) и не ломаются при изменении схемы. Fixture-файлы остались разве что для **бинарных артефактов** (примеры файлов, snapshot-ы API).",
                'difficulty' => 2,
                'topic' => 'testing.practice',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое factory в тестах Laravel простыми словами?',
                'answer' => "**Factory** — класс-**генератор тестовых моделей**. Лежит в `database/factories/UserFactory.php`. Главная польза: тест не пишет вручную `User::create(['name' => 'aaa', 'email' => 'a@b.ru', ...])`, а зовёт `User::factory()->create()` — фабрика подставит **случайные валидные** значения через `faker`.\n\nКлючевые приёмы:\n\n- **Создание**: `User::factory()->create()` — пишет в БД; `->make()` — только объект без `INSERT`.\n- **Несколько**: `->count(10)->create()` — 10 юзеров.\n- **Переопределение**: `->create(['role' => 'admin'])` — указанные поля заменены, остальные остаются случайными.\n- **Состояния**: `->state(['banned' => true])` или метод `->admin()` в фабрике — переиспользуемые «варианты».\n- **Связи**: `->hasPosts(3)->create()` — юзер с 3 постами; `->for(\$team)` — задать `belongsTo`.\n\nЧто это даёт: каждый тест **подготавливает ровно нужное** ему состояние, не зависит от глобального seeder-а и не падает, когда схема растёт.",
                'code_example' => "<?php\n\n// 1 юзер со случайными валидными данными\n\$user = User::factory()->create();\n\n// 10 юзеров\n\$users = User::factory()->count(10)->create();\n\n// Переопределить поля\n\$admin = User::factory()->create(['role' => 'admin']);\n\n// Связанные модели: юзер с 3 постами\n\$user = User::factory()->hasPosts(3)->create();\n\n// make() — создаёт объект без сохранения в БД\n\$user = User::factory()->make();",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'testing.practice',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое RefreshDatabase trait в Laravel?',
                'answer' => "**`RefreshDatabase`** — трейт, который **изолирует БД между тестами**. Каждый тест оборачивается в **транзакцию**, в конце — `ROLLBACK`. Тест №2 **не видит** данные теста №1.\n\nКак работает:\n\n- На **первом запуске** в процессе трейт прогоняет миграции (по сути `php artisan migrate:fresh`).\n- Дальше **каждый тест** = `BEGIN` перед, `ROLLBACK` после — **без повторных миграций**, поэтому быстро.\n- Все изменения внутри теста «откатываются», БД возвращается к пустому состоянию (после миграций).\n\nКогда не подходит:\n\n- Тестируем **сами транзакции** — наш `BEGIN/COMMIT` сломает обёртку трейта.\n- Очереди с отдельным процессом, которые читают БД до commit.\n\nАльтернативы:\n\n- **`DatabaseMigrations`** — дропает и мигрирует БД на **каждый тест** (медленно, но честно).\n- **`DatabaseTransactions`** — то же, что `RefreshDatabase`, **без** начальных миграций (БД уже должна быть готова).\n\nВ `phpunit.xml` обычно ставят `DB_CONNECTION=sqlite` и `DB_DATABASE=:memory:` — тогда `RefreshDatabase` летает.",
                'code_example' => "<?php\n\nnamespace Tests\\Feature;\n\nuse App\\Models\\User;\nuse Illuminate\\Foundation\\Testing\\RefreshDatabase;\nuse Tests\\TestCase;\n\nclass UserRegistrationTest extends TestCase\n{\n    use RefreshDatabase; // каждый тест стартует с чистой БД\n\n    public function test_first_user_is_created(): void\n    {\n        User::factory()->create();\n\n        \$this->assertDatabaseCount('users', 1);\n    }\n\n    public function test_second_test_does_not_see_previous_user(): void\n    {\n        // Здесь БД снова пуста — никакого юзера из прошлого теста\n        \$this->assertDatabaseCount('users', 0);\n    }\n}",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'testing.practice',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что значит «тест должен быть детерминированным» простыми словами?',
                'answer' => "**Детерминированный** = один и тот же тест на одном и том же коде **всегда даёт один и тот же результат**. Прогоняешь 1000 раз — 1000 раз зелёный или 1000 раз красный, без «иногда».\n\nВраги детерминизма:\n\n- **Время** — `now()`, `time()`, `new DateTime()` в коде. Решение: `Carbon::setTestNow('2025-01-01')` или `\$this->travelTo(...)`.\n- **Случайные данные** — `faker` без `seed`. Решение: явно задать значения в тесте через `->create(['email' => 'a@b.ru'])`.\n- **Порядок тестов** — тест №2 зависит от данных №1. Решение: `RefreshDatabase`, отдельный `setUp()`.\n- **Внешние сервисы** — `Stripe`, e-mail-провайдер, погода. Решение: `Http::fake()`, `Mail::fake()`.\n- **Часовой пояс / локаль / порядок ключей в массиве** в `assertEquals`. Решение: фиксируй явно.\n\n**Правило**: если для красного теста нужен «правильный момент» — он недетерминирован, и однажды покрасит в CI без видимой причины.",
                'difficulty' => 2,
                'topic' => 'testing.practice',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое setUp и tearDown в PHPUnit?',
                'answer' => "**Хуки жизненного цикла теста** в `PHPUnit`:\n\n- **`setUp()`** — вызывается **перед каждым** тестом класса. Сюда — общая подготовка: создать сервис, залогинить юзера, заморозить время.\n- **`tearDown()`** — вызывается **после каждого** теста. Сюда — уборка: закрыть файл, `Mockery::close()`, `Carbon::setTestNow()` без аргументов.\n- **`setUpBeforeClass()`** / **`tearDownAfterClass()`** — выполняются **один раз на класс** (статические!), редко нужны.\n\nДве важные ловушки:\n\n- **Всегда** зови `parent::setUp()` / `parent::tearDown()` **первой строкой**. Иначе в Laravel-тесте контейнер и трейты (`RefreshDatabase`) не загрузятся, и тесты будут падать в неожиданных местах.\n- **Не клади в `setUp()` уникальное** для одного теста — это шумит и заставляет читателя прыгать туда-сюда. Общее — в `setUp()`, специфичное — в самом тесте.\n\nВ `Pest` аналоги: `beforeEach(fn () => ...)` и `afterEach(...)`.",
                'code_example' => "<?php\n\nuse Illuminate\\Support\\Carbon;\nuse Tests\\TestCase;\n\nclass OrderServiceTest extends TestCase\n{\n    private OrderService \$service;\n\n    protected function setUp(): void\n    {\n        parent::setUp(); // обязательно — иначе Laravel не загрузится\n\n        \$this->service = new OrderService();\n        Carbon::setTestNow('2025-01-01 12:00:00');\n    }\n\n    protected function tearDown(): void\n    {\n        Carbon::setTestNow(); // сбрасываем «замороженное» время\n        parent::tearDown();\n    }\n}",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'testing.practice',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое data provider в PHPUnit?',
                'answer' => 'Способ запустить один и тот же тест с РАЗНЫМИ входными данными — без копипасты. Метод-провайдер (обязательно public static, возвращает array или Generator) отдаёт массивы наборов аргументов; PHPUnit прогоняет тест по каждому набору как отдельный кейс. В PHPUnit 10+ — атрибут #[DataProvider(\'methodName\')], в PHPUnit 9 — аннотация @dataProvider в докблоке. Ключи массива становятся именами кейсов в выводе (test_rejects_invalid_email with data set "empty") — поэтому именуй ключи смыслово, а не 0/1/2. Типичные сценарии: граничные случаи (пустая строка, очень длинная, со спецсимволами), таблицы валидации, матрица «вход → ожидаемый выход».',
                'code_example' => "<?php\n\nuse PHPUnit\\Framework\\Attributes\\DataProvider;\nuse PHPUnit\\Framework\\TestCase;\n\nclass EmailValidatorTest extends TestCase\n{\n    #[DataProvider('invalidEmails')]\n    public function test_rejects_invalid_email(string \$email): void\n    {\n        \$this->assertFalse(EmailValidator::isValid(\$email));\n    }\n\n    // public static — обязательное требование PHPUnit 10+\n    public static function invalidEmails(): array\n    {\n        return [\n            // именованные ключи попадут в имя кейса в выводе\n            'empty' => [''],\n            'no @' => ['plainstring'],\n            'no domain' => ['user@'],\n            'spaces' => ['a b@example.com'],\n        ];\n    }\n}",
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'testing.practice',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Главные database-ассерты Laravel для джуна?',
                'answer' => "Ассерты Laravel, которые делают **прямой SQL** к тестовой БД — они не зависят от кеша Eloquent и переживают любые ORM-нюансы.\n\n- **`assertDatabaseHas(table, fields)`** — есть ли строка с такими полями.\n- **`assertDatabaseMissing(table, fields)`** — что **нет** такой строки.\n- **`assertDatabaseCount(table, n)`** — в таблице **ровно `n`** строк.\n- **`assertModelExists(\$model)`** / **`assertModelMissing(\$model)`** — для конкретного объекта по его `id`.\n- **`assertSoftDeleted(\$model)`** / **`assertNotSoftDeleted(\$model)`** — что строка мягко удалена (`deleted_at IS NOT NULL`).\n\nПрактика:\n\n- Используй вместе с `RefreshDatabase` — иначе counts не будут сходиться от теста к тесту.\n- Поле `password` сравнивать через `assertDatabaseHas` **нельзя** — в БД хеш, не plain-текст. Проверяй через `Hash::check(...)` или `password_verify(...)` на свежей модели.\n- В `assertDatabaseHas` можно передать **подмножество** полей, остальные игнорируются.",
                'code_example' => "<?php\n\npublic function test_register_creates_user_row(): void\n{\n    \$this->post('/register', [\n        'name' => 'Jane',\n        'email' => 'jane@example.com',\n        'password' => 'secret123',\n        'password_confirmation' => 'secret123',\n    ]);\n\n    \$this->assertDatabaseHas('users', [\n        'email' => 'jane@example.com',\n        'name' => 'Jane',\n    ]);\n    \$this->assertDatabaseCount('users', 1);\n}",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'testing.practice',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Как тестировать код, зависящий от времени?',
                'answer' => 'Главное правило — НЕ звать new DateTime() / time() / date() напрямую в коде, а ходить через Carbon::now() или хелпер now() в Laravel: только тогда время «замораживается» в тесте. В голом PHPUnit замораживаем через Carbon::setTestNow(\'2025-01-01 12:00:00\') — все вызовы now() будут возвращать эту дату. ОБЯЗАТЕЛЬНО в tearDown() звать Carbon::setTestNow() без аргументов, иначе следующие тесты увидят замороженное время и станут flaky. В Laravel удобнее $this->travelTo($date) / $this->travel(31)->days() / $this->freezeTime() — фреймворк сам сбрасывает время после теста. Альтернатива на уровне дизайна: внедрять Clock-интерфейс (PSR-20) и подменять его в тесте на FixedClock.',
                'code_example' => "<?php\n\nuse Illuminate\\Support\\Carbon;\n\npublic function test_subscription_expires_after_30_days(): void\n{\n    // travelTo + travel: автоматически сбросится после теста\n    \$this->travelTo('2025-01-01 12:00:00');\n\n    \$subscription = Subscription::factory()->create();\n\n    \$this->travel(31)->days(); // прыгаем в будущее на 31 день\n\n    \$this->assertTrue(\$subscription->fresh()->isExpired());\n}\n\npublic function test_with_raw_carbon_freeze(): void\n{\n    Carbon::setTestNow('2025-01-01 12:00:00');\n\n    \$this->assertSame('2025-01-01', now()->toDateString());\n\n    Carbon::setTestNow(); // ОБЯЗАТЕЛЬНО размораживаем\n}",
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'testing.practice',
            ],
        ];
    }
}
