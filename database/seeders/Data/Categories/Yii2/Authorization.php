<?php

namespace Database\Seeders\Data\Categories\Yii2;

class Authorization
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Yii2',
                'question' => 'Что такое RBAC в Yii2 и какие у него основные сущности?',
                'answer' => '**RBAC (Role-Based Access Control)** — встроенная система авторизации Yii2 на основе ролей и разрешений (`yii\\rbac\\ManagerInterface`).

Четыре главные сущности:

- **Role** (`yii\\rbac\\Role`) — роль (`admin`, `editor`, `author`).
- **Permission** (`yii\\rbac\\Permission`) — разрешение (`createPost`, `updatePost`, `deletePost`).
- **Rule** (`yii\\rbac\\Rule`) — **динамическое правило** (PHP-класс), например «только если ты автор».
- **Assignment** (`yii\\rbac\\Assignment`) — связь `user_id → role/permission`.

Ключевые идеи:

- **Иерархия**: роли наследуют разрешения через **`addChild()`**.
- Проверка: **`Yii::$app->user->can(\'updatePost\', [\'post\' => $post])`**.
- Хранение: **`PhpManager`** (файлы) или **`DbManager`** (БД).',
                'code_example' => 'use Yii;

$auth = Yii::$app->authManager;

// 1. Создаём permission
$updatePost = $auth->createPermission(\'updatePost\');
$updatePost->description = \'Update a post\';
$auth->add($updatePost);

// 2. Создаём роль
$author = $auth->createRole(\'author\');
$auth->add($author);

// 3. Иерархия: author может updatePost
$auth->addChild($author, $updatePost);

// 4. Назначаем роль пользователю
$auth->assign($author, $userId);

// Проверка
Yii::$app->user->can(\'updatePost\');  // true для author',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.authorization',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Чем PhpManager отличается от DbManager в Yii2 RBAC?',
                'answer' => 'Yii2 предоставляет **две** реализации `authManager` — выбирают по тому, **где** хранить RBAC-данные.

| Признак | PhpManager | DbManager |
| --- | --- | --- |
| Где хранит | **PHP-файлы** (`@app/rbac/items.php`) | **таблицы БД** (`auth_*`) |
| Изменение в проде | пересохранение **файла** | `INSERT/UPDATE` в БД |
| Для CI/CD | удобно (файлы под git) | миграции/сидеры |
| Кол-во пользователей | до **~сотен ролей** | **миллионы** assignment |
| Динамические assignments | плохо (race conditions) | **штатно** (транзакции) |
| Скорость чтения | быстрая (файлы кешируются) | требуется **cache** |
| Подходит для | блогов, мелких проектов | продакшен с админкой |

**Правило**: если ассайменты меняются редко и из кода — `PhpManager`. Если из админки/в рантайме — `DbManager`.',
                'code_example' => '// config/web.php

// Вариант 1: PhpManager — данные в файлах
\'components\' => [
    \'authManager\' => [
        \'class\' => \'yii\\rbac\\PhpManager\',
        // файлы по умолчанию: @app/rbac/items.php, assignments.php, rules.php
    ],
],

// Вариант 2: DbManager — данные в БД
\'components\' => [
    \'authManager\' => [
        \'class\' => \'yii\\rbac\\DbManager\',
        \'cache\' => \'cache\',  // ВАЖНО для производительности
    ],
],

// Для DbManager сначала запускаем миграции:
// php yii migrate --migrationPath=@yii/rbac/migrations',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.authorization',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Какие таблицы создаёт DbManager в Yii2 (auth_*)?',
                'answer' => '`DbManager` хранит RBAC в **4 таблицах**, создаваемых миграцией `yii/rbac/migrations`.

| Таблица | Что хранит | Ключевые колонки |
| --- | --- | --- |
| **`auth_item`** | роли + разрешения + правила | `name` (PK), `type` (1=role, 2=permission), `rule_name`, `description` |
| **`auth_item_child`** | иерархию ролей и прав | `parent`, `child` |
| **`auth_assignment`** | назначения user → role | `item_name`, `user_id`, `created_at` |
| **`auth_rule`** | сериализованные `Rule`-объекты | `name` (PK), `data` (BLOB) |

Запустить миграции:

```
php yii migrate --migrationPath=@yii/rbac/migrations
```

Все запросы DbManager идут через эти таблицы — поэтому **`cache` обязателен** в проде, иначе `can()` делает 3–4 JOIN на каждый запрос.',
                'code_example' => '// Структура auth_item:
// name=admin, type=1 (role)
// name=updatePost, type=2 (permission), rule_name=AuthorRule

// Структура auth_item_child:
// parent=admin, child=updatePost
// parent=author, child=updatePost

// Структура auth_assignment:
// item_name=author, user_id=42

// Запуск миграций
// $ php yii migrate --migrationPath=@yii/rbac/migrations

// Команда выведет:
// m140506_102106_rbac_init
// m170907_052038_rbac_add_index_on_auth_assignment_user_id
// m180523_151638_rbac_updates_indexes_without_prefix
// m200409_180000_rbac_update_mssql_trigger

// Можно посмотреть назначения вручную:
// SELECT * FROM auth_assignment WHERE user_id = 42;',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.authorization',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как создать роль и разрешение в Yii2 RBAC (createRole, createPermission, add)?',
                'answer' => 'Создание RBAC-сущностей идёт через **`Yii::$app->authManager`**.

Шаги:

- **`$auth->createRole($name)`** или **`$auth->createPermission($name)`** — **только конструируют** объект (не сохраняют).
- **`$auth->add($item)`** — **сохраняет** в файл/БД.
- **`$auth->addChild($parent, $child)`** — строит иерархию (роль наследует разрешение).
- **`$auth->assign($role, $userId)`** — назначает пользователю.

Типичная ошибка: забыть **`add()`** после `createRole()` — объект существует только в памяти.

Удаление:

- **`$auth->remove($item)`** — удаляет сам item.
- **`$auth->removeChild($parent, $child)`** — отвязывает.
- **`$auth->revoke($role, $userId)`** — снимает назначение с пользователя.',
                'code_example' => 'use Yii;

class RbacController extends \\yii\\console\\Controller
{
    public function actionInit()
    {
        $auth = Yii::$app->authManager;
        $auth->removeAll();   // чистим всё

        // Permissions
        $createPost = $auth->createPermission(\'createPost\');
        $createPost->description = \'Создать пост\';
        $auth->add($createPost);

        $updatePost = $auth->createPermission(\'updatePost\');
        $updatePost->description = \'Редактировать пост\';
        $auth->add($updatePost);

        // Roles
        $author = $auth->createRole(\'author\');
        $auth->add($author);
        $auth->addChild($author, $createPost);
        $auth->addChild($author, $updatePost);

        $admin = $auth->createRole(\'admin\');
        $auth->add($admin);
        $auth->addChild($admin, $author);   // admin наследует author
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.authorization',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как назначить роль пользователю в Yii2 RBAC (assign)?',
                'answer' => '**`$auth->assign($role, $userId)`** — создаёт запись в `auth_assignment` (или в `assignments.php` для `PhpManager`).

Сигнатура:

- **`$role`** — объект `Role` или `Permission` (не строка!).
- **`$userId`** — обычно `Yii::$app->user->id`.
- Возвращает объект **`Assignment`**.

Особенности:

- Можно **назначить роль ещё несуществующему пользователю** — Yii2 не проверяет FK.
- Один пользователь может иметь **несколько ролей** — `can()` проверит все.
- Назначение конкретного **permission** (не роли) — допустимо, но обычно плохой стиль (теряется группировка).
- Для удаления — **`$auth->revoke($role, $userId)`** или **`revokeAll($userId)`**.',
                'code_example' => 'use Yii;

$auth = Yii::$app->authManager;

// Назначаем роль "author" пользователю с id=42
$author = $auth->getRole(\'author\');   // получить уже созданную
$auth->assign($author, 42);

// Назначить сразу несколько ролей
$auth->assign($auth->getRole(\'author\'), $userId);
$auth->assign($auth->getRole(\'moderator\'), $userId);

// Получить все роли пользователя
$roles = $auth->getRolesByUser($userId);   // array<Role>
foreach ($roles as $name => $role) {
    echo $name;
}

// Снять одну роль
$auth->revoke($auth->getRole(\'moderator\'), $userId);

// Снять все роли
$auth->revokeAll($userId);',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.authorization',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как построить иерархию ролей через addChild() в Yii2?',
                'answer' => '**`$auth->addChild($parent, $child)`** строит иерархию RBAC: `$parent` **наследует** все разрешения `$child`.

Правила:

- В графе **не должно быть циклов** — Yii2 кидает `InvalidParamException`.
- `Role` может содержать **`Role` и `Permission`**.
- `Permission` может содержать **только `Permission`** (нельзя положить роль внутрь разрешения).

Типичная иерархия блога:

- **`admin`** → `manageUsers`, `editor`
- **`editor`** → `updateAnyPost`, `author`
- **`author`** → `createPost`, `updateOwnPost`

При проверке **`can(\'createPost\')`**:

1. Yii ищет назначения пользователя.
2. Идёт **по графу вверх**: если у пользователя есть `admin`, проверяет всё, что доступно через `addChild`.
3. Находит `createPost` через `admin → editor → author → createPost` — возвращает `true`.',
                'code_example' => 'use Yii;

$auth = Yii::$app->authManager;

// Permissions
$createPost = $auth->createPermission(\'createPost\'); $auth->add($createPost);
$updateOwnPost = $auth->createPermission(\'updateOwnPost\'); $auth->add($updateOwnPost);
$updateAnyPost = $auth->createPermission(\'updateAnyPost\'); $auth->add($updateAnyPost);
$manageUsers = $auth->createPermission(\'manageUsers\'); $auth->add($manageUsers);

// Roles
$author = $auth->createRole(\'author\'); $auth->add($author);
$editor = $auth->createRole(\'editor\'); $auth->add($editor);
$admin = $auth->createRole(\'admin\'); $auth->add($admin);

// Иерархия
$auth->addChild($author, $createPost);
$auth->addChild($author, $updateOwnPost);

$auth->addChild($editor, $updateAnyPost);
$auth->addChild($editor, $author);          // editor включает author

$auth->addChild($admin, $manageUsers);
$auth->addChild($admin, $editor);            // admin включает editor

// admin теперь может ВСЁ из дерева ниже',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.authorization',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое Rule в Yii2 RBAC и как написать AuthorRule?',
                'answer' => '**`yii\\rbac\\Rule`** — PHP-класс для **динамической** проверки прав: можно ли пользователю выполнить действие **именно над этим объектом**.

Классический пример — **`AuthorRule`**: «`updatePost` разрешён, только если ты автор поста».

Как работает:

- Наследуем `yii\\rbac\\Rule`, задаём **`public $name = \'isAuthor\'`**.
- Реализуем **`execute($user, $item, $params)`** — возвращает `bool`.
- Сохраняем правило: **`$auth->add(new AuthorRule())`**.
- Привязываем правило к разрешению: **`$updatePost->ruleName = $rule->name`** → `add()`.
- При вызове `can(\'updatePost\', [\'post\' => $post])` Yii **передаст `$params` в `execute()`**.

Без `$params` правило обычно `false` или бросает исключение — всегда проверяй наличие ключа.',
                'code_example' => 'namespace app\\rbac;

use yii\\rbac\\Rule;

class AuthorRule extends Rule
{
    public $name = \'isAuthor\';

    public function execute($user, $item, $params)
    {
        return isset($params[\'post\'])
            ? $params[\'post\']->created_by == $user
            : false;
    }
}

// Регистрация в RBAC:
$auth = Yii::$app->authManager;
$rule = new \\app\\rbac\\AuthorRule();
$auth->add($rule);

$updateOwnPost = $auth->createPermission(\'updateOwnPost\');
$updateOwnPost->ruleName = $rule->name;
$auth->add($updateOwnPost);

$author = $auth->createRole(\'author\');
$auth->add($author);
$auth->addChild($author, $updateOwnPost);

// Проверка
$post = Post::findOne($id);
Yii::$app->user->can(\'updateOwnPost\', [\'post\' => $post]);',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.authorization',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Где в Yii2 обычно лежит RBAC-bootstrap?',
                'answer' => 'Yii2 не диктует, где **создавать** структуру RBAC — есть три подхода.

| Подход | Плюсы | Минусы |
| --- | --- | --- |
| **Console-команда** (`yii rbac/init`) | повторяемо, под git | надо помнить запуск после deploy |
| **Миграция** (`m_xxxx_rbac_init.php`) | автоматически в CI/CD | сложнее править ассайменты |
| **Админка** (UI) | удобно бизнесу | данных нет в коде/git |

Самый частый — **console controller + миграция** для скелета + UI для назначений.

Структура файлов (basic-шаблон):

- **`console/controllers/RbacController.php`** — инициализация ролей/прав.
- **`common/rbac/AuthorRule.php`** — кастомные правила.
- В `console/config/main.php` подключают **`authManager`** (тот же класс что и `web`).

Команда запуска: **`php yii rbac/init`**.',
                'code_example' => '// console/controllers/RbacController.php
namespace console\\controllers;

use Yii;
use yii\\console\\Controller;
use common\\rbac\\AuthorRule;

class RbacController extends Controller
{
    public function actionInit()
    {
        $auth = Yii::$app->authManager;
        $auth->removeAll();

        // Permissions
        $createPost = $auth->createPermission(\'createPost\');
        $auth->add($createPost);

        // Rule
        $rule = new AuthorRule();
        $auth->add($rule);

        $updateOwnPost = $auth->createPermission(\'updateOwnPost\');
        $updateOwnPost->ruleName = $rule->name;
        $auth->add($updateOwnPost);

        // Roles
        $author = $auth->createRole(\'author\');
        $auth->add($author);
        $auth->addChild($author, $createPost);
        $auth->addChild($author, $updateOwnPost);

        $this->stdout("RBAC initialized\\n");
    }
}

// Запуск:
// $ php yii rbac/init',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.authorization',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как сделать простую авторизацию в Yii2 через AccessControl без RBAC?',
                'answer' => '**`yii\\filters\\AccessControl`** — простой фильтр уровня контроллера для **базовой авторизации** без подключения RBAC.

Когда хватает:

- **`@`** vs **`?`** — залогинен vs гость.
- ограничение по **ролям** (если включён `authManager`).
- ограничение по **HTTP-методу** или **IP**.
- **кастомная** функция `matchCallback`.

Правила проверяются **сверху вниз**: первое подходящее **`allow`/`deny`** срабатывает. По умолчанию — **deny all**.

Когда стоит переходить на RBAC:

- разные права на **разные объекты** (только свои посты).
- иерархия ролей.
- больше **3–5** правил.',
                'code_example' => 'use yii\\filters\\AccessControl;

class PostController extends \\yii\\web\\Controller
{
    public function behaviors()
    {
        return [
            \'access\' => [
                \'class\' => AccessControl::class,
                \'only\' => [\'create\', \'update\', \'delete\', \'admin-only\'],
                \'rules\' => [
                    [
                        \'allow\' => true,
                        \'actions\' => [\'create\', \'update\'],
                        \'roles\' => [\'@\'],          // только залогиненные
                    ],
                    [
                        \'allow\' => true,
                        \'actions\' => [\'admin-only\'],
                        \'roles\' => [\'admin\'],      // через RBAC если включён
                    ],
                    [
                        \'allow\' => true,
                        \'actions\' => [\'delete\'],
                        \'matchCallback\' => function ($rule, $action) {
                            return Yii::$app->user->identity->isAdmin();
                        },
                    ],
                ],
            ],
        ];
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.authorization',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Зачем нужен cache для AuthManager в Yii2 и как его включить?',
                'answer' => '**`DbManager`** при каждом `can()` делает несколько **JOIN** по `auth_item`/`auth_item_child`/`auth_assignment` — это **дорого** при большой иерархии.

Свойство **`cache`** на `DbManager` включает кеш:

- **Что кешируется**: список всех `Item` и связей `parent → children` (граф RBAC).
- **Где не помогает**: `auth_assignment` (назначения) кешируются **на запрос**, не глобально.
- Кеш **инвалидируется автоматически** при изменении графа (`add`, `addChild`, `remove`).
- Без кеша на каждом `can()` 3–5 запросов; с кешем — **0 запросов** к таблице `auth_item`.

В проекте уровня middle/senior **`cache`** для `DbManager` — **обязательная** настройка.

Дополнительно: третий аргумент **`can($name, $params, $allowCaching = true)`** включает кеш **внутри одного запроса**.',
                'code_example' => '// config/web.php
return [
    \'components\' => [
        \'cache\' => [
            \'class\' => \'yii\\caching\\FileCache\',
            // или \\yii\\redis\\Cache, \\yii\\caching\\MemCache
        ],
        \'authManager\' => [
            \'class\' => \'yii\\rbac\\DbManager\',
            \'cache\' => \'cache\',         // ВКЛЮЧЕНО
            \'defaultRoles\' => [\'guest\'], // роли для всех (включая гостей)
        ],
    ],
];

// Внутри запроса повторные вызовы тоже мемоизируются
Yii::$app->user->can(\'updatePost\');   // SQL/cache hit
Yii::$app->user->can(\'updatePost\');   // мемо, без обращения

// Чтобы отключить мемо внутри запроса:
Yii::$app->user->can(\'updatePost\', [], false);',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.authorization',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое bizRule в Yii2 и почему он deprecated?',
                'answer' => '**`bizRule`** — устаревший механизм Yii **1.1**, где правило задавалось **PHP-кодом в строке** (`eval()`).

Как было в Yii 1.1:

```
$bizRule = \'return Yii::app()->user->id == $params["post"]->author_id;\';
$auth->createOperation(\'updatePost\', \'\', $bizRule);
```

Минусы:

- **`eval()`** — небезопасно (любой `$bizRule` исполнится).
- Нет статического анализа, IDE-подсказок, отладки.
- **Невозможно** написать тесты на отдельный класс.
- Конфликт с **PSR-стилем** и **autoload**.

В Yii2 заменено на **class-based rules** (`yii\\rbac\\Rule`):

- Отдельный класс с **`execute()`**-методом.
- Имя класса хранится в `auth_rule.data` сериализованно.
- Полноценный **OOP**: тесты, наследование, mocking.

**Правило**: если видишь `bizRule` в старом коде — это легаси из Yii 1.x, нужно переписать на `Rule`-классы.',
                'code_example' => '// СТАРО (Yii 1.x, deprecated):
// $bizRule = \'return Yii::app()->user->id == $params["post"]->author_id;\';
// $auth->createOperation(\'updatePost\', \'\', $bizRule);

// НОВО (Yii 2.x):
namespace app\\rbac;

use yii\\rbac\\Rule;

class AuthorRule extends Rule
{
    public $name = \'isAuthor\';

    public function execute($user, $item, $params)
    {
        return isset($params[\'post\'])
            ? $params[\'post\']->created_by == $user
            : false;
    }
}

// Регистрация:
$rule = new \\app\\rbac\\AuthorRule();
Yii::$app->authManager->add($rule);

$updatePost = Yii::$app->authManager->createPermission(\'updatePost\');
$updatePost->ruleName = $rule->name;
Yii::$app->authManager->add($updatePost);',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.authorization',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как масштабировать RBAC DbManager в высоконагруженной системе: N+1, кэширование и кастомный AuthManager?',
                'answer' => '**Проблема:** в крупной admin-панели на одной странице может быть **30-50 вызовов `can()`** (на каждую кнопку/виджет). Без кэша `DbManager` делает **3-4 JOIN** на каждый вызов = **150+ SQL-запросов** за рендер страницы.

**N+1 в `DbManager` без кэша:**

```sql
-- На каждый can():
SELECT * FROM auth_assignment WHERE user_id = ?;
SELECT * FROM auth_item WHERE name IN (...);
SELECT * FROM auth_item_child WHERE parent IN (...);  -- рекурсия по иерархии
SELECT * FROM auth_rule WHERE name IN (...);
```

**Решение — `$authManager->cache`:**

- Свойство **`cache`** на `DbManager` кэширует **граф `auth_item` + `auth_item_child` + `auth_rule`** целиком.
- При первом `can()` граф загружается **одним пакетом** и хранится в кэше как сериализованный массив.
- **`auth_assignment`** (назначения user → role) **не** входит в общий кэш — мемоизируется только в рамках текущего запроса (`$checkAccessAssignments`).
- Инвалидация — **автоматическая** при `add()`, `addChild()`, `update()`, `remove()` через `invalidateCache()`.

**Когда переходить с PhpManager → DbManager в проде:**

| Признак | Сигнал к переходу |
|---|---|
| Изменение ролей **из админки** в рантайме | сразу `DbManager` |
| Concurrent writes в RBAC | `PhpManager` ломается на race condition (`file_put_contents` без lock) |
| > 1000 ролей или > 10k assignment | `PhpManager` грузит весь файл при `init()` |
| Multi-server deployment | `PhpManager` требует синхронизации файла между нодами |

**Миграция PhpManager → DbManager:**

1. Запустить миграции: `php yii migrate --migrationPath=@yii/rbac/migrations`.
2. Написать **скрипт-конвертер**: пройти по `$phpAuth->getRoles()` / `getPermissions()` / `getRules()` и вызвать `$dbAuth->add()`.
3. Перенести `getAssignments()` через `$dbAuth->assign()`.
4. Сменить класс в конфиге.
5. Включить `cache`.

**Custom AuthManager** нужен когда:

- Хочешь хранить роли в **внешней системе** (LDAP, Keycloak, IAM).
- Нужна **многотенантность** — роли изолированы по `tenant_id`.
- Дополнительные поля в `auth_assignment` (TTL, область видимости, `granted_by`).

Наследуй от `BaseManager` (а не `DbManager`) и реализуй **8 абстрактных методов**: `getItem`, `getItems`, `addItem`, `removeItem`, `updateItem`, `getChildrenList`, `getRule`, и т.д. Или extend `DbManager` и переопредели нужные методы.',
                'code_example' => '// 1. Конфиг DbManager с кэшем + бутстрап-предзагрузка
return [
    \'bootstrap\' => [\'authManager\'],   // загружаем граф один раз в начале запроса
    \'components\' => [
        \'cache\' => [
            \'class\' => \'yii\\redis\\Cache\',  // в проде — Redis, не File
        ],
        \'authManager\' => [
            \'class\' => \'yii\\rbac\\DbManager\',
            \'cache\' => \'cache\',
            \'defaultRoles\' => [\'guest\'],
        ],
    ],
];

// 2. Скрипт миграции PhpManager → DbManager
$phpAuth = new \yii\rbac\PhpManager();
$dbAuth = new \yii\rbac\DbManager();
$dbAuth->db = Yii::$app->db;
$dbAuth->init();

foreach ($phpAuth->getRules() as $rule) { $dbAuth->add($rule); }
foreach ($phpAuth->getPermissions() as $perm) { $dbAuth->add($perm); }
foreach ($phpAuth->getRoles() as $role) {
    $dbAuth->add($role);
    foreach ($phpAuth->getChildren($role->name) as $child) {
        $dbAuth->addChild($role, $child);
    }
}
foreach ($phpAuth->getAssignments(0) as $userId => $assignments) {
    foreach ($assignments as $name => $assignment) {
        $dbAuth->assign($dbAuth->getRole($name) ?? $dbAuth->getPermission($name), $userId);
    }
}

// 3. Custom AuthManager — добавляем tenant_id
class TenantAwareDbManager extends \yii\rbac\DbManager
{
    public ?int $tenantId = null;

    protected function getAssignments($userId)
    {
        $assignments = parent::getAssignments($userId);
        // Фильтруем по текущему тенанту
        return array_filter($assignments, function ($a) {
            $row = (new \yii\db\Query())
                ->from($this->assignmentTable)
                ->where([\'user_id\' => $a->userId, \'item_name\' => $a->roleName, \'tenant_id\' => $this->tenantId])
                ->exists($this->db);
            return $row;
        });
    }
}',
                'code_language' => 'php',
                'difficulty' => 5,
                'topic' => 'yii2.authorization',
            ],
        ];
    }
}
