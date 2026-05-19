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
                'answer' => 'Test-Driven Development — пишешь сначала ТЕСТ, потом код. Цикл «Red-Green-Refactor»: 1) Написал тест → он падает (Red). 2) Написал минимум кода, чтобы прошёл (Green). 3) Отрефакторил, тесты всё ещё зелёные. Преимущества: думаешь о дизайне через интерфейс, тесты гарантированно есть, не отлынуть.',
                'difficulty' => 2,
                'topic' => 'testing.practice',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое code coverage простыми словами?',
                'answer' => 'Процент строк кода, которые покрыты тестами. 80% coverage = тесты «прошли» по 80% строк. Метрика обманчива: 100% не значит «нет багов», 50% может быть достаточно для критичной логики. Не цель сама по себе — её гонят, чтобы найти НЕпокрытые участки.',
                'difficulty' => 2,
                'topic' => 'testing.practice',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое flaky test простыми словами?',
                'answer' => 'Тест, который иногда проходит, иногда падает БЕЗ изменений в коде. Причины: зависимость от времени (time-based), от порядка тестов, от внешних сервисов, race conditions. Самое худшее, что может быть в CI — команда теряет доверие к тестам. Лечится изоляцией, фиксацией времени, mock-ами.',
                'difficulty' => 2,
                'topic' => 'testing.practice',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое fixture простыми словами?',
                'answer' => 'Заранее подготовленный набор данных, в котором тест стартует — «известное начальное состояние». Например, БД с 5 юзерами и заказами под них, или JSON-файл с ожидаемым payload-ом API. Раньше хранили в YAML/JSON в /tests/fixtures и грузили через loadFixture(\'users.yml\'). В Laravel классические fixture-файлы почти ушли — вместо них factory-классы (User::factory()->count(5)->create()) и database seeders, потому что они выразительнее и генерируют рандомные валидные данные.',
                'difficulty' => 2,
                'topic' => 'testing.practice',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое factory в тестах Laravel простыми словами?',
                'answer' => 'Класс-генератор тестовых моделей. User::factory()->create() создаст в БД пользователя со случайным валидным именем и email. Удобно: ->count(10)->create() — 10 юзеров; ->state([\'role\' => \'admin\']) — переопределить поля; ->hasPosts(3)->create() — юзер с 3 постами.',
                'code_example' => "<?php\n\n// 1 юзер со случайными валидными данными\n\$user = User::factory()->create();\n\n// 10 юзеров\n\$users = User::factory()->count(10)->create();\n\n// Переопределить поля\n\$admin = User::factory()->create(['role' => 'admin']);\n\n// Связанные модели: юзер с 3 постами\n\$user = User::factory()->hasPosts(3)->create();\n\n// make() — создаёт объект без сохранения в БД\n\$user = User::factory()->make();",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'testing.practice',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое RefreshDatabase trait в Laravel?',
                'answer' => 'Трейт в тестах, который изолирует БД между тестами: каждый тест оборачивается в транзакцию, в конце — ROLLBACK. Зачем: тест №2 не должен видеть данные теста №1 — иначе тесты взаимозависимы и flaky. На первом запуске трейт прогоняет миграции (php artisan migrate), дальше — только транзакции, поэтому быстро. Альтернатива — DatabaseMigrations: дропает и мигрирует БД заново на каждый тест (медленно, но работает, когда транзакции не подходят, например, при тестировании самих транзакций).',
                'code_example' => "<?php\n\nnamespace Tests\\Feature;\n\nuse App\\Models\\User;\nuse Illuminate\\Foundation\\Testing\\RefreshDatabase;\nuse Tests\\TestCase;\n\nclass UserRegistrationTest extends TestCase\n{\n    use RefreshDatabase; // каждый тест стартует с чистой БД\n\n    public function test_first_user_is_created(): void\n    {\n        User::factory()->create();\n\n        \$this->assertDatabaseCount('users', 1);\n    }\n\n    public function test_second_test_does_not_see_previous_user(): void\n    {\n        // Здесь БД снова пуста — никакого юзера из прошлого теста\n        \$this->assertDatabaseCount('users', 0);\n    }\n}",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'testing.practice',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что значит «тест должен быть детерминированным» простыми словами?',
                'answer' => 'Один и тот же тест на одном и том же коде должен ВСЕГДА давать один и тот же результат. Враги детерминизма: 1) Зависимость от времени (now() в тесте). 2) Случайные данные без seed. 3) Зависимость от других тестов. 4) Внешние сервисы. Решение: Carbon::setTestNow(), фиксированный seed, mock-и.',
                'difficulty' => 2,
                'topic' => 'testing.practice',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое setUp и tearDown в PHPUnit?',
                'answer' => 'setUp() — метод, который PHPUnit вызывает ПЕРЕД каждым тестом класса: туда кладут общую подготовку (создание сервиса, аутентификация юзера). tearDown() — ПОСЛЕ каждого теста: туда кладут уборку (закрыть файл, сбросить Carbon::setTestNow()). Альтернатива — setUpBeforeClass()/tearDownAfterClass() выполняются один раз на класс. ВАЖНО: всегда вызывай parent::setUp() / parent::tearDown() первой строкой.',
                'code_example' => "<?php\n\nuse Illuminate\\Support\\Carbon;\nuse Tests\\TestCase;\n\nclass OrderServiceTest extends TestCase\n{\n    private OrderService \$service;\n\n    protected function setUp(): void\n    {\n        parent::setUp(); // обязательно — иначе Laravel не загрузится\n\n        \$this->service = new OrderService();\n        Carbon::setTestNow('2025-01-01 12:00:00');\n    }\n\n    protected function tearDown(): void\n    {\n        Carbon::setTestNow(); // сбрасываем «замороженное» время\n        parent::tearDown();\n    }\n}",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'testing.practice',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое data provider в PHPUnit?',
                'answer' => 'Способ запустить один и тот же тест с РАЗНЫМИ входными данными — без копипасты. Метод-провайдер возвращает массив наборов аргументов, PHPUnit прогоняет тест по каждому набору. Полезно для граничных случаев: пустая строка, очень длинная, со спецсимволами и т.п. В PHPUnit 10+ задаётся атрибутом #[DataProvider(\'methodName\')].',
                'code_example' => "<?php\n\nuse PHPUnit\\Framework\\Attributes\\DataProvider;\nuse PHPUnit\\Framework\\TestCase;\n\nclass EmailValidatorTest extends TestCase\n{\n    #[DataProvider('invalidEmails')]\n    public function test_rejects_invalid_email(string \$email): void\n    {\n        \$this->assertFalse(EmailValidator::isValid(\$email));\n    }\n\n    public static function invalidEmails(): array\n    {\n        return [\n            'empty' => [''],\n            'no @' => ['plainstring'],\n            'no domain' => ['user@'],\n            'spaces' => ['a b@example.com'],\n        ];\n    }\n}",
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'testing.practice',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Главные database-ассерты Laravel для джуна?',
                'answer' => 'assertDatabaseHas(table, fields) — проверяет, что строка с такими полями есть. assertDatabaseMissing(table, fields) — что нет. assertDatabaseCount(table, n) — что в таблице ровно n строк. assertModelExists($model) / assertModelMissing($model) — для конкретного объекта. assertSoftDeleted($model) — что мягко удалён. Эти проверки переживают любые ORM-нюансы и работают через прямой SQL.',
                'code_example' => "<?php\n\npublic function test_register_creates_user_row(): void\n{\n    \$this->post('/register', [\n        'name' => 'Jane',\n        'email' => 'jane@example.com',\n        'password' => 'secret123',\n        'password_confirmation' => 'secret123',\n    ]);\n\n    \$this->assertDatabaseHas('users', [\n        'email' => 'jane@example.com',\n        'name' => 'Jane',\n    ]);\n    \$this->assertDatabaseCount('users', 1);\n}",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'testing.practice',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Как тестировать код, зависящий от времени?',
                'answer' => 'Не звать new DateTime() / time() напрямую в коде — звать через Carbon::now() или now() в Laravel. В тесте «замораживаем» время через Carbon::setTestNow(\'2025-01-01 12:00:00\') — все вызовы now() возвращают эту дату. Не забыть в tearDown() позвать Carbon::setTestNow() без аргументов, иначе следующие тесты увидят замороженное время. Альтернатива в Laravel: $this->travelTo($date).',
                'code_example' => "<?php\n\nuse Illuminate\\Support\\Carbon;\n\npublic function test_subscription_expires_after_30_days(): void\n{\n    \$this->travelTo('2025-01-01 12:00:00');\n\n    \$subscription = Subscription::factory()->create();\n\n    \$this->travel(31)->days();\n\n    \$this->assertTrue(\$subscription->fresh()->isExpired());\n}",
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'testing.practice',
            ],
        ];
    }
}
