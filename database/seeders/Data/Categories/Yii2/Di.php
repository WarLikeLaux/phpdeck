<?php

namespace Database\Seeders\Data\Categories\Yii2;

class Di
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Yii2',
                'question' => 'Что такое yii\\di\\Container в Yii2?',
                'answer' => '**`yii\\di\\Container`** — встроенный **DI-контейнер** Yii2, отвечающий за создание объектов и **разрешение их зависимостей**.

**Возможности:**

- **Constructor injection** — подставляет зависимости в параметры конструктора по type-hint.
- **Setter / property injection** — заполняет публичные свойства / сеттеры из конфига.
- **Singleton-биндинги** — один экземпляр на весь запрос.
- **Резолв по интерфейсам** — `IUserRepo::class => UserRepoMysql::class`.

**Доступ к контейнеру:**

- **`Yii::$container`** — глобальный экземпляр.
- Внутренне используется в `Yii::createObject()` и при создании компонентов.',
                'code_example' => '<?php
use yii\\di\\Container;

// Yii уже создал контейнер при бутстрапе
$container = Yii::$container;

// Биндинг интерфейса → реализация
$container->set(
    \'app\\interfaces\\UserRepository\',
    \'app\\repositories\\UserRepositoryMysql\'
);

// Singleton
$container->setSingleton(
    \'app\\services\\PaymentGateway\',
    [
        \'class\' => \'app\\services\\StripeGateway\',
        \'apiKey\' => Yii::$app->params[\'stripeKey\'],
    ]
);

// Резолв
$repo = $container->get(\'app\\interfaces\\UserRepository\');
$gateway = $container->get(\'app\\services\\PaymentGateway\');',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.di',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое Yii::$container?',
                'answer' => '**`Yii::$container`** — статическое свойство класса `Yii`, **глобальный экземпляр DI-контейнера** (`yii\\di\\Container`).

**Где используется:**

- Внутри **`Yii::createObject($config)`** — именно `Yii::$container` создаёт объекты и подставляет зависимости.
- Когда контроллер берёт зависимости через **action injection** (типизированные параметры action-метода).
- При создании любого компонента из конфига приложения.

**Когда обращаться к нему напрямую:**

- При **бутстрапе** — зарегистрировать singleton-сервисы и алиасы интерфейс→класс.
- В тестах — подменить реализацию на mock.

**Не путать с `Yii::$app`:**

- **`Yii::$app`** — service locator (готовые именованные компоненты).
- **`Yii::$container`** — DI-контейнер (создание объектов по запросу).',
                'code_example' => '<?php
// config/web.php (раздел container)
\'container\' => [
    \'definitions\' => [
        \'app\\interfaces\\Mailer\' => \'app\\components\\SmtpMailer\',
    ],
    \'singletons\' => [
        \'app\\services\\Cache\' => function () {
            return new RedisCache(\'localhost\');
        },
    ],
],

// Или программно
Yii::$container->set(\'app\\interfaces\\Mailer\', \'app\\components\\SmtpMailer\');

// Получить
$mailer = Yii::$container->get(\'app\\interfaces\\Mailer\');

// В тесте
Yii::$container->set(\'app\\interfaces\\Mailer\', \\PHPUnit\\Framework\\TestCase::createMock(...));',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.di',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Какие типы внедрения зависимостей поддерживает Yii2 DI?',
                'answer' => 'Yii2-контейнер поддерживает **три способа** внедрения:

- **Constructor injection** — самый частый. Контейнер смотрит на type-hint параметров конструктора и подставляет нужные объекты.
- **Setter / property injection** — через массив-конфиг (`[\'apiKey\' => \'X\']`) заполняются публичные свойства/сеттеры объекта.
- **PHP callable injection** — биндинг как фабрика-замыкание, которое само решает, что вернуть.

**Method injection (action injection):**

- Контроллеры Yii2 умеют резолвить **зависимости из параметров action-метода** — это разновидность method injection.

**Лимиты:**

- В отличие от Laravel-контейнера, **скалярные параметры** (string/int без значения по умолчанию) **не резолвятся** — контейнер кинет исключение.',
                'code_example' => '<?php
class UserService
{
    public function __construct(
        private UserRepository $repo,  // constructor
        private Mailer $mailer
    ) {}

    public string $logChannel = \'audit\'; // setter via config
}

// Constructor injection — автоматически
$service = Yii::$container->get(UserService::class);

// Setter injection — через массив
$service = Yii::createObject([
    \'class\' => UserService::class,
    \'logChannel\' => \'critical\',
]);

// Callable injection — фабрика
Yii::$container->set(\'app\\interfaces\\Cache\', function ($c, $params, $config) {
    return new RedisCache(\'localhost\', 6379);
});

// Action injection
public function actionShow(int $id, UserRepository $repo)
{
    return $repo->findOne($id);
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.di',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Чем отличается set() от setSingleton() в DI-контейнере?',
                'answer' => 'Оба регистрируют, **как создавать объект**, но отличаются временем жизни:

| Метод | Поведение | Когда применять |
| --- | --- | --- |
| **`set($class, $def)`** | **Каждый** `get()` создаёт **новый объект** | Stateful-объекты, формы, value-объекты |
| **`setSingleton($class, $def)`** | **Один объект** на весь запрос | Stateless-сервисы: репозитории, шлюзы, кэш |

**Что можно передать в `$def`:**

- **Имя класса** — `\'app\\Foo\'`.
- **Массив-конфиг** — `[\'class\' => \'app\\Foo\', \'apiKey\' => \'X\']`.
- **Замыкание-фабрика** — `function ($container, $params, $config) { return new ...; }`.
- **Готовый объект** — будет всегда возвращаться этот экземпляр (де-факто singleton).',
                'code_example' => '<?php
$c = Yii::$container;

// set — каждый раз новый объект
$c->set(\'app\\models\\LoginForm\');
$f1 = $c->get(\'app\\models\\LoginForm\');
$f2 = $c->get(\'app\\models\\LoginForm\');
// $f1 !== $f2

// setSingleton — один объект на весь запрос
$c->setSingleton(\'app\\services\\Logger\', [
    \'class\' => \'app\\services\\FileLogger\',
    \'path\' => \'@runtime/app.log\',
]);
$l1 = $c->get(\'app\\services\\Logger\');
$l2 = $c->get(\'app\\services\\Logger\');
// $l1 === $l2

// Готовый объект — тоже singleton
$c->set(\'app\\services\\Clock\', new \\app\\services\\SystemClock());',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.di',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что делает метод get() у DI-контейнера?',
                'answer' => '**`$container->get($class, $params = [], $config = [])`** — **резолвит и возвращает объект** по имени класса или алиаса.

**Алгоритм:**

1. Если у класса есть **singleton-биндинг** и объект уже создан — вернуть его.
2. Если есть **обычный биндинг** — выполнить его (массив-конфиг, фабрика-замыкание, имя класса).
3. Иначе — `Reflection` параметров конструктора и **рекурсивный резолв зависимостей** по type-hint.
4. Применить **`$config`** к свойствам объекта (как массив-конфиг).
5. Вызвать **`init()`**, если это `BaseObject`.

**Параметры метода:**

- **`$params`** — позиционные аргументы конструктора (override).
- **`$config`** — конфиг для свойств (override).',
                'code_example' => '<?php
class Post
{
    public function __construct(
        public Logger $logger,
        public string $title = \'\'
    ) {}
}

// Простой резолв — Logger подставится автоматически
$post = Yii::$container->get(Post::class);

// С позиционными аргументами
$post = Yii::$container->get(
    Post::class,
    [null, \'Привет\']  // logger возьмётся из контейнера, title — \'Привет\'
);

// С конфигом свойств
$post = Yii::$container->get(
    Post::class,
    [],
    [\'title\' => \'Hi\']
);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.di',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как резолвить зависимости по интерфейсу в Yii2?',
                'answer' => 'Регистрируем биндинг **`интерфейс => класс`** в контейнере — и Yii будет подставлять реализацию везде, где встречается type-hint интерфейса.

**Шаги:**

1. Объявить интерфейс: `app\\interfaces\\UserRepository`.
2. Сделать класс-реализацию: `app\\repositories\\UserRepositoryMysql`.
3. Зарегистрировать в `config[\'container\'][\'definitions\']` или через `Yii::$container->set()`.
4. Type-hint в конструкторах сервисов / параметрах action — `UserRepository $repo`.

**Где регистрировать:**

- В `config/web.php` / `config/console.php` — секция `container`.
- В bootstrap-классе или Service Provider-аналоге.

**Зачем:**

- Подмена реализации в **тестах** (на mock).
- Замена SQL→Redis репозитория без правки сервисов.',
                'code_example' => '<?php
// app/interfaces/UserRepository.php
interface UserRepository
{
    public function findOne(int $id): ?User;
}

// app/repositories/UserRepositoryMysql.php
class UserRepositoryMysql implements UserRepository { /* ... */ }

// config/web.php
return [
    \'container\' => [
        \'definitions\' => [
            \'app\\interfaces\\UserRepository\' => \'app\\repositories\\UserRepositoryMysql\',
        ],
    ],
];

// Использование
class UserService
{
    public function __construct(private UserRepository $repo) {}
}

$service = Yii::$container->get(UserService::class);
// внутри $repo — UserRepositoryMysql',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.di',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как настроить контейнер через config[container][definitions]?',
                'answer' => 'Стандартный путь регистрации **в конфиге приложения** — секция **`container`** с двумя подключами:

- **`definitions`** — обычные биндинги (новый объект на каждый `get()`).
- **`singletons`** — singleton-биндинги.

**Что можно прописать:**

- **`\'Interface\' => \'ClassName\'`** — простое сопоставление.
- **`\'Interface\' => [\'class\' => \'ClassName\', \'param\' => \'X\']`** — с параметрами.
- **`\'Interface\' => Closure`** — фабрика.

Применяется при бутстрапе через `Yii::configure(Yii::$container, $config[\'container\'])`.',
                'code_example' => '<?php
// config/web.php
return [
    \'container\' => [
        \'definitions\' => [
            // Простое сопоставление
            \'app\\interfaces\\Logger\' => \'app\\services\\FileLogger\',

            // С параметрами
            \'yii\\widgets\\LinkPager\' => [
                \'maxButtonCount\' => 5,
            ],

            // Фабрика
            \'app\\services\\HttpClient\' => function () {
                return new GuzzleHttp\\Client([
                    \'timeout\' => 5.0,
                ]);
            },
        ],
        \'singletons\' => [
            \'app\\services\\PaymentGateway\' => [
                \'class\' => \'app\\services\\StripeGateway\',
                \'apiKey\' => getenv(\'STRIPE_KEY\'),
            ],
        ],
    ],
];',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.di',
            ],
            [
                'category' => 'Yii2',
                'question' => 'В чём разница между Yii::createObject() и new?',
                'answer' => 'Сравнение двух способов создания объекта в Yii2:

| Признак | `new ClassName()` | `Yii::createObject($config)` |
| --- | --- | --- |
| Резолв зависимостей | **нет** | **да** (через DI) |
| Применение массива-конфига | нет | **да** (свойства из массива) |
| Подмена через container | нет | **да** (учитывает биндинги) |
| Производительность | быстрее | чуть медленнее (Reflection) |
| Возможность тестировать | сложно (жёсткая связь) | легко (можно подменить класс) |

**Когда что брать:**

- **`new`** — простые value-объекты, helper-классы без зависимостей.
- **`Yii::createObject()`** — сервисы, компоненты, всё, что должно поддерживать **расширение конфигом** или **подмену в тестах**.

Внутри фреймворка везде используется `createObject`.',
                'code_example' => '<?php
// new — прямая инстанциация
$money = new Money(100, \'USD\');

// createObject из строки
$logger = Yii::createObject(\'app\\services\\FileLogger\');

// createObject из массива
$cache = Yii::createObject([
    \'class\' => \'yii\\caching\\FileCache\',
    \'cachePath\' => \'@runtime/cache\',
]);

// Если зарегистрирован биндинг — учтётся
Yii::$container->set(\'app\\Logger\', \'app\\NullLogger\');
$l = Yii::createObject(\'app\\Logger\');
// $l instanceof NullLogger',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.di',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Чем DI-контейнер отличается от service locator в Yii2?',
                'answer' => 'В Yii2 эти **два паттерна сосуществуют** — и важно их различать.

| Признак | Service Locator (`Yii::$app`) | DI Container (`Yii::$container`) |
| --- | --- | --- |
| Класс | **`yii\\base\\Application`** (расширяет `ServiceLocator`) | **`yii\\di\\Container`** |
| Хранит | **именованные компоненты** (`db`, `cache`, `user`) | **биндинги по имени класса/интерфейса** |
| Доступ | `Yii::$app->db` | `Yii::$container->get(Foo::class)` |
| Резолв зависимостей | **не делает** (компонент сам конфигурируется конфигом) | **да** — рекурсивно по type-hint |
| Конфиг | `components` | `container.definitions` / `container.singletons` |
| Lazy load | да | да |

**Главная мысль:**

- **Service Locator** возвращает **готовый именованный сервис** (по строке-ключу).
- **DI-контейнер** **создаёт объект** по имени класса, **разрешая его зависимости**.

Под капотом ServiceLocator при создании компонента **тоже использует** `Yii::createObject()` → `Yii::$container`.',
                'code_example' => '<?php
// Service Locator — именованный компонент
$db = Yii::$app->db;       // \'db\' — ключ в components
$cache = Yii::$app->cache; // \'cache\' — ключ в components

// DI Container — по имени класса
$repo = Yii::$container->get(\'app\\repositories\\UserRepository\');

// Конфиг
return [
    // Service Locator
    \'components\' => [
        \'db\' => [\'class\' => \'yii\\db\\Connection\', ...],
    ],
    // DI Container
    \'container\' => [
        \'definitions\' => [
            \'app\\interfaces\\Mailer\' => \'app\\services\\SmtpMailer\',
        ],
    ],
];',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.di',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Почему Yii2 использует одновременно Service Locator (Application) и DI Container (Yii::$container), как это ломается в long-running и что изменилось в Yii3?',
                'answer' => '**Архитектурный факт:** Yii2 — **единственный** из мажорных PHP-фреймворков, где **оба паттерна** работают вместе на равных правах. Это сделано **намеренно** для производительности (lazy-load компонентов) и обратной совместимости с Yii 1.x.

**Как они взаимодействуют:**

| Слой | Класс | Зачем |
|---|---|---|
| **Application = ServiceLocator** | `yii\\base\\Application` extends `Module` extends `ServiceLocator` | хранит **именованные** компоненты: `db`, `cache`, `user`, `urlManager` |
| **DI Container** | `Yii::$container` (`yii\\di\\Container`) | хранит **биндинги по имени класса/интерфейса** для autowiring |
| **Мост** | `Yii::createObject($config)` | вызывает `$container->get()` для создания объекта из любого массива-конфига |

**Поток создания компонента:**

1. `Yii::$app->db` → ServiceLocator находит **definition** в `components` → ещё не инстанциирован.
2. ServiceLocator вызывает `Yii::createObject(definition)`.
3. `createObject` идёт в `Yii::$container->get(...)`, который через **Reflection** разрешает зависимости конструктора.
4. Готовый объект кэшируется в ServiceLocator как **singleton на запрос**.

**Долгоживущие процессы (RoadRunner / Swoole / ReactPHP) — где это ломается:**

| Проблема | Причина | Решение |
|---|---|---|
| **State leakage** между запросами | singleton в `Yii::$app->user` хранит identity предыдущего юзера | пересоздавать `Application` на каждый request (worker reset) |
| **Memory leak** в DI | биндинги через `setSingleton` + replaced объекты накапливаются | использовать **scoped** контейнер на request |
| **Stale config** | `Yii::$app->params` мутирован запросом и виден другим | заморозить params, не мутировать `Yii::$app` |
| **DB-соединение** | `db` singleton — то же соединение для всех запросов воркера | OK для прод-MySQL, но pgBouncer + prepared statements ломаются |
| **transient vs singleton** | в Yii2 нет "scoped" — всё либо `set()` (new each time) либо `setSingleton()` (на всю жизнь воркера) | вручную сбрасывать через `Yii::$container->set(...)` после каждого request |

**С PHP-FPM + OPcache** этих проблем **нет** — каждый запрос = свежий `Application`, всё умирает с процессом.

**В Yii3 (yiisoft/di + yiisoft/app):**

- ServiceLocator **убран**, остался **только DI** (`yiisoft/di` на основе PSR-11).
- Используется **`yiisoft/aliases`** вместо `Yii::getAlias()`.
- **`StateResetter`** — официальный механизм сброса состояния singleton между запросами в RoadRunner.
- Все компоненты регистрируются через **`config/common.php`** и **`config/web.php`** как PSR-11 определения.
- Полный autowiring + **scoped** контейнер уровня запроса.',
                'code_example' => '// 1. Один и тот же сервис двумя способами — оба работают
// Service Locator подход (Yii2-стиль)
\'components\' => [
    \'mailer\' => [\'class\' => \'app\\components\\SmtpMailer\', \'host\' => \'smtp.example.com\'],
],
Yii::$app->mailer->send(...);   // по строке-ключу

// DI Container подход (более OOP)
\'container\' => [
    \'singletons\' => [
        \'app\\interfaces\\Mailer\' => [\'class\' => \'app\\components\\SmtpMailer\', \'host\' => \'smtp.example.com\'],
    ],
],
class OrderService {
    public function __construct(private \app\interfaces\Mailer $mailer) {}
}

// 2. RoadRunner: сброс состояния между запросами
// worker.php
$psr7 = new \Spiral\RoadRunner\Http\PSR7Worker(...);
while ($req = $psr7->waitRequest()) {
    // КАЖДЫЙ запрос — свежий Yii-app
    $config = require __DIR__ . \'/config/web.php\';
    (new \yii\web\Application($config))->run();
    // После завершения — старый Yii::$app остаётся в памяти процесса
    // Чтобы избежать утечки — обнуляем
    Yii::$app = null;
    gc_collect_cycles();
}

// 3. Yii3-стиль (для сравнения) — чистый DI без ServiceLocator
// config/common/params.php — определения
return [
    \'yiisoft/mailer\' => [
        \'fromEmail\' => \'no-reply@example.com\',
    ],
];
// config/common/di.php
return [
    \Yiisoft\Mailer\MailerInterface::class => \Yiisoft\Mailer\SymfonyMailer\Mailer::class,
];
// Контроллер
final class SiteController {
    public function __construct(private MailerInterface $mailer) {}  // autowiring
}',
                'code_language' => 'php',
                'difficulty' => 5,
                'topic' => 'yii2.di',
            ],
        ];
    }
}
