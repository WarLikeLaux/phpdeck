<?php

namespace Database\Seeders\Data\Categories\Yii2;

class Testing
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Yii2',
                'question' => 'Какой тестовый фреймворк используется в Yii2?',
                'answer' => 'Стандартный testing-инструмент в Yii2 — **Codeception** (пакет `codeception/codeception`), идёт в шаблонах `basic` и `advanced` из коробки.

**Зачем Codeception:**

- Один инструмент для **unit / functional / acceptance / api** тестов.
- Под капотом использует **PHPUnit** для unit-тестов (`assertEquals`, `assertTrue` и пр.).
- Имеет **высокоуровневый DSL** через объект `$I` (`AcceptanceTester`, `FunctionalTester`) — `$I->amOnPage(...)`, `$I->see(...)`.
- Поддерживает **fixtures** (фиксированные данные БД) и **mock-объекты**.

**Пакеты для Yii2:**

- **`yiisoft/yii2-codeception`** — старый интеграционный пакет (Yii 2.0.x ≤ 2.0.10).
- В современных версиях используется **`codeception/module-yii2`** (Codeception 4.x+).

Тесты лежат в **`tests/`**, конфиг — **`tests/codeception.yml`**.',
                'code_example' => '# composer.json (devDependencies в шаблоне basic)
{
    "require-dev": {
        "codeception/codeception": "^5.0",
        "codeception/module-yii2": "*",
        "codeception/module-asserts": "*",
        "codeception/module-phpbrowser": "*"
    }
}

# Запуск
composer install --dev
vendor/bin/codecept build           # сгенерировать Tester-классы
vendor/bin/codecept run unit
vendor/bin/codecept run functional
vendor/bin/codecept run acceptance
vendor/bin/codecept run             # все сразу',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'yii2.testing',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Какие типы тестов есть в Codeception для Yii2?',
                'answer' => 'Codeception разделяет тесты на **4 типа** по уровню изоляции и тому, что они проверяют.

| Тип | Что тестирует | Запуск приложения | Доступ к БД | Скорость |
| --- | --- | --- | --- | --- |
| **unit** | один класс/метод | нет | по желанию | **очень быстро** |
| **functional** | Yii внутри PHP без HTTP | Yii в памяти | да | быстро |
| **acceptance** | пользовательский сценарий через **браузер** | реальный web-сервер | да | **медленно** |
| **api** | REST/JSON-эндпоинты | реальный web-сервер | да | средне |

**Когда что брать:**

- **unit** — для бизнес-логики, валидаторов, helper-классов.
- **functional** — для контроллеров без JS (быстрее acceptance, не нужен браузер).
- **acceptance** — для сценариев с JavaScript (нужен WebDriver / Selenium / `chromedriver`).
- **api** — для REST-API (`yii\\rest\\Controller`).

Структура: **`tests/unit/`**, **`tests/functional/`**, **`tests/acceptance/`**, **`tests/api/`** + соответствующие `*.suite.yml` конфиги.',
                'code_example' => '# tests/codeception.yml (общий конфиг)
namespace: tests
actor_suffix: Tester
paths:
    tests: tests
    output: tests/_output
    data: tests/_data
    support: tests/_support
    envs: tests/_envs

# tests/unit.suite.yml
suite_namespace: tests\\unit
actor: UnitTester
modules:
    enabled:
        - Asserts
        - Yii2:
            part: [orm, email, fixtures]
            cleanup: true

# tests/functional.suite.yml
suite_namespace: tests\\functional
actor: FunctionalTester
modules:
    enabled:
        - Yii2:
            part: [orm, init]
        - \\tests\\helpers\\Helper',
                'code_language' => 'yaml',
                'difficulty' => 2,
                'topic' => 'yii2.testing',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое fixtures в Yii2 и как их использовать?',
                'answer' => '**Fixture** — набор **фиксированных данных** (строк в таблице), которые загружаются перед тестом, чтобы он работал в **предсказуемом** состоянии БД.

**Базовый класс — `yii\\test\\ActiveFixture`:**

- **`$modelClass`** — какой AR-класс заполняет.
- **`$dataFile`** — путь к PHP-файлу с массивом данных (`[\'user1\' => [\'name\' => \'Alice\']]`).
- **`$depends`** — массив других fixture-классов, которые должны быть загружены сначала (для FK).

**Использование в тесте:**

- Объявить `_fixtures()` в `Cest` / `Codeception\\Test\\Unit` классе и вернуть массив.
- Перед каждым тестом Codeception **очищает** таблицы и **заливает** данные.
- Доступ к строкам — `$this->tester->grabFixture(\'users\', \'user1\')`.

**Команда вне теста:** `./yii fixture/load User` — загрузить fixtures вручную.',
                'code_example' => '// tests/fixtures/UserFixture.php
namespace tests\\fixtures;

use yii\\test\\ActiveFixture;

class UserFixture extends ActiveFixture
{
    public $modelClass = \'app\\models\\User\';
    public $dataFile = \'@tests/_data/user.php\';
}

// tests/_data/user.php
return [
    \'user1\' => [
        \'id\' => 1,
        \'username\' => \'admin\',
        \'email\' => \'admin@example.com\',
        \'status\' => 10,
    ],
    \'user2\' => [
        \'id\' => 2,
        \'username\' => \'demo\',
        \'email\' => \'demo@example.com\',
        \'status\' => 10,
    ],
];

// tests/unit/models/UserTest.php
namespace tests\\unit\\models;

use Codeception\\Test\\Unit;
use tests\\fixtures\\UserFixture;
use app\\models\\User;

class UserTest extends Unit
{
    public function _fixtures(): array
    {
        return [\'users\' => UserFixture::class];
    }

    public function testFindByUsername(): void
    {
        $user = User::findOne([\'username\' => \'admin\']);
        $this->assertNotNull($user);
        $this->assertSame(\'admin@example.com\', $user->email);
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.testing',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как писать functional-тест в Yii2 Codeception?',
                'answer' => '**Functional-тест** запускает Yii **в том же PHP-процессе** (без реального HTTP-сервера) и проверяет поведение контроллеров через DSL-объект **`$I` (`FunctionalTester`)**.

**Типичные методы:**

- **`$I->amOnPage(\'/site/login\')`** — открыть страницу (роут или URL).
- **`$I->see(\'Welcome\')`** — найти текст в HTML.
- **`$I->dontSee(\'Error\')`**.
- **`$I->fillField(\'LoginForm[username]\', \'admin\')`**.
- **`$I->click(\'Login\')`** — по тексту кнопки или CSS-селектору.
- **`$I->seeInCurrentUrl(\'/site/index\')`**.
- **`$I->seeResponseCodeIs(200)`** / **`seeResponseCodeIsSuccessful()`**.

**Структура файла** — обычно **Cest**-формат: класс с публичными методами, каждый принимает `FunctionalTester $I`.

**База данных** — в `functional.suite.yml` включают модуль `Yii2` с `part: orm` — тесты используют реальную тестовую БД и fixtures.',
                'code_example' => '<?php
// tests/functional/LoginCest.php
namespace tests\\functional;

use tests\\fixtures\\UserFixture;
use FunctionalTester;

class LoginCest
{
    public function _fixtures(): array
    {
        return [\'users\' => UserFixture::class];
    }

    public function openLoginPage(FunctionalTester $I): void
    {
        $I->amOnPage(\'/site/login\');
        $I->see(\'Login\', \'h1\');
    }

    public function submitEmptyForm(FunctionalTester $I): void
    {
        $I->amOnPage(\'/site/login\');
        $I->submitForm(\'#login-form\', []);
        $I->expectTo(\'see validation errors\');
        $I->see(\'Username cannot be blank.\');
        $I->see(\'Password cannot be blank.\');
    }

    public function loginSuccessfully(FunctionalTester $I): void
    {
        $I->amOnPage(\'/site/login\');
        $I->fillField(\'LoginForm[username]\', \'admin\');
        $I->fillField(\'LoginForm[password]\', \'admin\');
        $I->click(\'login-button\');
        $I->seeInCurrentUrl(\'/site/index\');
        $I->see(\'Logout (admin)\');
    }
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.testing',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Чем unit-тест в Yii2 Codeception отличается от обычного PHPUnit?',
                'answer' => '**Unit-тест в Yii2 Codeception** — это **PHPUnit под капотом** + плюшки от Codeception.

| Признак | Чистый PHPUnit | Codeception unit |
| --- | --- | --- |
| Базовый класс | `PHPUnit\\Framework\\TestCase` | **`Codeception\\Test\\Unit`** |
| Запуск | `vendor/bin/phpunit` | **`vendor/bin/codecept run unit`** |
| Fixtures | руками | `_fixtures()` через `Yii2`-модуль |
| Mock объектов | `$this->createMock(...)` (PHPUnit API доступен) | то же + `$this->tester->grabFixture(...)` |
| Assertions | `assertEquals`, `assertSame`, ... | **те же** (PHPUnit API сохраняется) |
| Доступ к БД | вручную | через модуль **`Yii2`** в `unit.suite.yml` |

**Преимущество Codeception:**

- **Унифицированный запуск** всех тестов (`codecept run`).
- **Fixtures** + cleanup БД между тестами.
- **`UnitTester` `$I`** — можно использовать `$I->haveRecord(...)`, `$I->seeRecord(...)`.

**Junior-правило:** в `tests/unit/` пиши классы extends **`Codeception\\Test\\Unit`** с методами **`testXxx()`** — почти как PHPUnit, но с интеграцией Yii2.',
                'code_example' => '<?php
// tests/unit/models/PostTest.php
namespace tests\\unit\\models;

use Codeception\\Test\\Unit;
use tests\\fixtures\\UserFixture;
use app\\models\\Post;

class PostTest extends Unit
{
    /** @var \\UnitTester */
    protected $tester;

    public function _fixtures(): array
    {
        return [\'users\' => UserFixture::class];
    }

    protected function _before(): void
    {
        // setUp — выполнится перед каждым тестом
    }

    public function testValidationRequiresTitle(): void
    {
        $post = new Post([\'content\' => \'body\']);
        $this->assertFalse($post->validate());
        $this->assertArrayHasKey(\'title\', $post->errors);
    }

    public function testCountAssertions(): void
    {
        $posts = Post::find()->all();
        $this->assertCount(0, $posts);
        $this->assertSame(0, Post::find()->count());
    }
}

# Запуск только этого теста
# vendor/bin/codecept run unit models/PostTest
# vendor/bin/codecept run unit models/PostTest:testValidationRequiresTitle',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.testing',
            ],
        ];
    }
}
