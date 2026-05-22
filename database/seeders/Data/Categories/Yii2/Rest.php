<?php

namespace Database\Seeders\Data\Categories\Yii2;

class Rest
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Yii2',
                'question' => 'Что такое ActiveController в Yii2 REST?',
                'answer' => '**`yii\\rest\\ActiveController`** — готовый базовый класс для **CRUD-эндпоинтов** над `ActiveRecord`-моделью.

Главные особенности:

- Указываем **`public $modelClass = ...`** — и получаем готовые actions.
- Унаследован от **`yii\\rest\\Controller`** (JSON по умолчанию, content negotiator, rate limiter).
- Автоматически подключает **6 actions**: `index`, `view`, `create`, `update`, `delete`, `options`.
- Сериализация — через **`fields()`** / **`extraFields()`** на модели.
- Авторизация — через **`checkAccess()`**-метод контроллера (вызывается перед `update/delete/view`).

Для нестандартного API — обычно наследуют от **`yii\\rest\\Controller`** напрямую и пишут actions вручную.',
                'code_example' => 'namespace app\\controllers;

use yii\\rest\\ActiveController;

class UserController extends ActiveController
{
    public $modelClass = \'app\\models\\User\';

    // Опциональная проверка прав
    public function checkAccess($action, $model = null, $params = [])
    {
        if ($action === \'update\' || $action === \'delete\') {
            if ($model->id !== \\Yii::$app->user->id) {
                throw new \\yii\\web\\ForbiddenHttpException(\'Можно редактировать только себя\');
            }
        }
    }
}

// Получаем сразу:
// GET    /users         — index
// GET    /users/1       — view
// POST   /users         — create
// PUT    /users/1       — update
// DELETE /users/1       — delete
// OPTIONS /users        — options',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.rest',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Какие стандартные actions у ActiveController в Yii2 REST?',
                'answer' => 'У **`ActiveController`** есть **6 встроенных actions** через метод **`actions()`**:

| Action | Класс | HTTP | Что делает |
| --- | --- | --- | --- |
| **`index`** | `IndexAction` | `GET` /users | список (с pagination) |
| **`view`** | `ViewAction` | `GET` /users/1 | один объект |
| **`create`** | `CreateAction` | `POST` /users | создать (`load` + `save`) |
| **`update`** | `UpdateAction` | `PUT|PATCH` /users/1 | обновить |
| **`delete`** | `DeleteAction` | `DELETE` /users/1 | удалить |
| **`options`** | `OptionsAction` | `OPTIONS` | CORS preflight + список методов |

Все action-классы лежат в **`yii\\rest\\*`** и **переиспользуются** в любом контроллере через **`actions()`**.

Поведение настраивается через **`prepareDataProvider`** (для `IndexAction`) и **`checkAccess`** (для всех, кроме `index` и `create`).',
                'code_example' => '// ActiveController::actions() — упрощённо
public function actions()
{
    return [
        \'index\' => [
            \'class\' => \\yii\\rest\\IndexAction::class,
            \'modelClass\' => $this->modelClass,
            \'checkAccess\' => [$this, \'checkAccess\'],
        ],
        \'view\' => [
            \'class\' => \\yii\\rest\\ViewAction::class,
            \'modelClass\' => $this->modelClass,
            \'checkAccess\' => [$this, \'checkAccess\'],
        ],
        \'create\' => [
            \'class\' => \\yii\\rest\\CreateAction::class,
            \'modelClass\' => $this->modelClass,
            \'checkAccess\' => [$this, \'checkAccess\'],
        ],
        \'update\' => [/* ... */],
        \'delete\' => [/* ... */],
        \'options\' => [\'class\' => \\yii\\rest\\OptionsAction::class],
    ];
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.rest',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как переопределить или удалить стандартные actions в ActiveController?',
                'answer' => 'Переопределение делается через **`actions()`** контроллера — мерджим родительский массив и **меняем или удаляем** ключи.

Сценарии:

- **Удалить** — `unset($actions[\'delete\']);`
- **Переопределить настройки** — заменить значение (например свой `prepareDataProvider`).
- **Заменить целиком** — задать собственный класс action.

Для кастомного **`prepareDataProvider`** (для `IndexAction`) — передаём callable, который возвращает **`DataProviderInterface`** (обычно `ActiveDataProvider`).

Для нестандартных endpoints просто **добавляем `actionXxx`** на контроллер — рядом со стандартными.',
                'code_example' => 'namespace app\\controllers;

use yii\\rest\\ActiveController;
use yii\\data\\ActiveDataProvider;

class UserController extends ActiveController
{
    public $modelClass = \'app\\models\\User\';

    public function actions()
    {
        $actions = parent::actions();

        // 1. Удаляем delete
        unset($actions[\'delete\']);

        // 2. Кастомизируем index — фильтр и сортировка
        $actions[\'index\'][\'prepareDataProvider\'] = [$this, \'prepareDataProvider\'];

        return $actions;
    }

    public function prepareDataProvider()
    {
        return new ActiveDataProvider([
            \'query\' => \\app\\models\\User::find()->where([\'status\' => \\app\\models\\User::STATUS_ACTIVE]),
            \'pagination\' => [\'pageSize\' => 20],
            \'sort\' => [\'defaultOrder\' => [\'created_at\' => SORT_DESC]],
        ]);
    }

    // Свой нестандартный endpoint
    public function actionStats()
    {
        return [\'total\' => \\app\\models\\User::find()->count()];
    }
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.rest',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как настроить сериализацию через fields() и extraFields() в Yii2?',
                'answer' => 'Yii2 REST сериализует модели через **`yii\\base\\Model::fields()`** и **`extraFields()`**.

| Метод | Когда возвращает поле |
| --- | --- |
| **`fields()`** | **всегда** (если поле в списке) |
| **`extraFields()`** | **только** при `?expand=name1,name2` |

Что вернуть:

- Просто **имя атрибута** (`\'id\'`, `\'username\'`).
- Или **`alias => callable`** для трансформации (`\'fullName\' => fn ($m) => $m->first . \' \' . $m->last`).
- В `fields()` **не возвращай** секретные поля (`password_hash`, `auth_key`, `access_token`)!

Полезные методы:

- Параметр **`?fields=id,username`** — клиент **сужает** список полей.
- Параметр **`?expand=posts,profile`** — клиент **расширяет** через `extraFields()`.

`User` из `Yii2 advanced` по умолчанию возвращает `password_hash` через `fields()` — это **уязвимость**, всегда переопределяй.',
                'code_example' => 'namespace app\\models;

use yii\\db\\ActiveRecord;

class User extends ActiveRecord implements \\yii\\web\\IdentityInterface
{
    public function fields()
    {
        return [
            \'id\',
            \'username\',
            \'email\',
            \'fullName\' => function ($model) {
                return $model->first_name . \' \' . $model->last_name;
            },
            \'createdAt\' => function ($model) {
                return date(\'c\', $model->created_at);
            },
            // НЕ возвращаем: password_hash, auth_key, access_token
        ];
    }

    public function extraFields()
    {
        return [\'posts\', \'profile\'];
    }

    public function getPosts() { return $this->hasMany(Post::class, [\'user_id\' => \'id\']); }
    public function getProfile() { return $this->hasOne(Profile::class, [\'user_id\' => \'id\']); }
}

// GET /users/1?expand=posts,profile&fields=id,username,posts',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.rest',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как настроить REST-роутинг через UrlManager и UrlRule в Yii2?',
                'answer' => 'Для **pretty REST-URL** регистрируют **`yii\\rest\\UrlRule`** в `urlManager` — он генерирует **CRUD-маршруты** автоматически.

Главные опции:

- **`controller`** — имя или массив контроллеров (`\'user\'`, `[\'user\', \'post\']`).
- **`pluralize`** — `true` (по умолчанию) — использует `/users` вместо `/user`.
- **`prefix`** — общий префикс (`\'api/v1\'`).
- **`extraPatterns`** — дополнительные маршруты (`\'GET search\' => \'search\'`).
- **`only`** / **`except`** — какие из CRUD-действий включить.
- **`tokens`** — кастомные паттерны (`{id}` → `<id:\\\\d+>`).

Также включают:

- **`enablePrettyUrl => true`** — убирает `?r=`.
- **`enableStrictParsing => true`** — 404 для всего, что не описано в правилах.
- **`showScriptName => false`** — убирает `index.php` из URL.',
                'code_example' => '// config/web.php
return [
    \'components\' => [
        \'urlManager\' => [
            \'enablePrettyUrl\' => true,
            \'enableStrictParsing\' => true,
            \'showScriptName\' => false,
            \'rules\' => [
                [
                    \'class\' => \'yii\\rest\\UrlRule\',
                    \'controller\' => [\'user\', \'post\'],
                    \'prefix\' => \'api/v1\',
                    \'pluralize\' => true,
                    \'extraPatterns\' => [
                        \'GET search\' => \'search\',           // GET /api/v1/users/search
                        \'POST {id}/restore\' => \'restore\',   // POST /api/v1/users/1/restore
                    ],
                ],
            ],
        ],
    ],
];

// Получаем:
// GET    /api/v1/users
// GET    /api/v1/users/1
// POST   /api/v1/users
// PUT    /api/v1/users/1
// DELETE /api/v1/users/1',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.rest',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как убрать index.php из REST-URL в Yii2 (showScriptName)?',
                'answer' => 'Чтобы получить чистые URL вида **`/api/v1/users/1`** без **`/index.php?r=...`**, нужно настроить **`urlManager`** **и** веб-сервер.

Конфиг `urlManager`:

- **`enablePrettyUrl => true`** — обязательное условие.
- **`showScriptName => false`** — убирает `index.php` из генерируемых URL.
- **`enableStrictParsing => true`** — для REST обычно ставят (404 на неизвестные пути).

Веб-сервер (Apache `.htaccess` в `web/`):

```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . index.php
```

Nginx:

```
location / {
    try_files $uri $uri/ /index.php?$args;
}
```

Без mod_rewrite/try_files придётся обращаться к `/index.php/api/v1/users/1` (с слешем после `index.php` — это `PATH_INFO`).',
                'code_example' => '// config/web.php
return [
    \'components\' => [
        \'urlManager\' => [
            \'enablePrettyUrl\' => true,
            \'showScriptName\' => false,
            \'enableStrictParsing\' => true,
            \'rules\' => [
                [
                    \'class\' => \'yii\\rest\\UrlRule\',
                    \'controller\' => \'user\',
                    \'prefix\' => \'api/v1\',
                ],
            ],
        ],
    ],
];

// web/.htaccess для Apache
// RewriteEngine On
// RewriteCond %{REQUEST_FILENAME} !-f
// RewriteCond %{REQUEST_FILENAME} !-d
// RewriteRule . index.php

// Теперь:
// /api/v1/users           — index
// /api/v1/users/1         — view
// без /index.php?r=user/view&id=1',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.rest',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как переключить формат REST-ответа на XML или JSON в Yii2?',
                'answer' => 'В **`yii\\rest\\Controller`** по умолчанию подключён **`ContentNegotiator`** с приоритетом **JSON**.

Способы переключения:

- Заголовок клиента: **`Accept: application/xml`** → XML.
- Параметр URL: **`?format=xml`** (имя параметра — `formatParam`).
- Жёстко в коде: `Yii::$app->response->format = Response::FORMAT_XML;`

Поддерживаемые форматы (`yii\\web\\Response::FORMAT_*`):

- **`json`** → `application/json` (по умолчанию).
- **`xml`** → `application/xml`.
- **`jsonp`** → `application/javascript`.
- **`html`** / **`raw`** — редко в REST.

Чтобы добавить **другие** форматы — переопределяют `formats` в `contentNegotiator`. Для **полностью кастомного** парсинга запроса (например `multipart/form-data`) — настраивают `request->parsers`.',
                'code_example' => 'use yii\\rest\\ActiveController;
use yii\\web\\Response;

class UserController extends ActiveController
{
    public $modelClass = \'app\\models\\User\';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors[\'contentNegotiator\'][\'formats\'] = [
            \'application/json\' => Response::FORMAT_JSON,
            \'application/xml\'  => Response::FORMAT_XML,
        ];

        return $behaviors;
    }
}

// Запрос:
// curl -H "Accept: application/xml" http://api/users/1
// → <response><id>1</id>...</response>

// curl -H "Accept: application/json" http://api/users/1
// → {"id": 1, ...}

// Жёстко:
public function actionExport()
{
    \\Yii::$app->response->format = Response::FORMAT_XML;
    return User::find()->all();
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.rest',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как настроить аутентификацию по Bearer-токену в Yii2 REST?',
                'answer' => '**`HttpBearerAuth`** — стандартный auth-метод для REST: токен в **`Authorization: Bearer <token>`**.

Шаги:

1. На модели `User` реализовать **`findIdentityByAccessToken($token, $type)`** — найти пользователя по колонке `access_token`.
2. В REST-контроллере прописать **`authMethods`** через `behaviors()`.
3. Исключить **`options`** из проверок (для CORS preflight).
4. Конфиг `user` — **`enableSession => false`** (stateless).

`HttpBearerAuth` автоматически:

- Парсит заголовок `Authorization`.
- Вызывает `User::findIdentityByAccessToken()`.
- При неуспехе шлёт **`401 Unauthorized`** + заголовок `WWW-Authenticate: Bearer realm="api"`.

Для безопасности:

- Токены — **длинные random** (`generateRandomString(64)`).
- Хранить в БД **захешированно** (например через `password_hash`), сравнивать через `validatePassword` — защита от утечки БД.
- Или хранить **отдельную таблицу** `user_token` с TTL и device.',
                'code_example' => 'namespace app\\controllers;

use yii\\rest\\ActiveController;
use yii\\filters\\auth\\HttpBearerAuth;

class UserController extends ActiveController
{
    public $modelClass = \'app\\models\\User\';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[\'authenticator\'] = [
            \'class\' => HttpBearerAuth::class,
            \'except\' => [\'options\'],   // CORS preflight без auth
        ];
        return $behaviors;
    }
}

// app/models/User.php
public static function findIdentityByAccessToken($token, $type = null)
{
    return static::findOne([
        \'access_token\' => $token,
        \'status\' => self::STATUS_ACTIVE,
    ]);
}

// Клиент:
// curl -H "Authorization: Bearer eyJ0eXA..." https://api/users/me',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.rest',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как сделать версионирование REST API в Yii2 (api/v1, api/v2)?',
                'answer' => 'Yii2 поддерживает версионирование REST API через **отдельные модули** с namespace.

Подход:

- Создаём модули **`api\\v1`** и **`api\\v2`** — каждый со своим конфигом и контроллерами.
- В `modules/v1/controllers/UserController.php` — собственная реализация.
- В **`urlManager`** настраиваем prefix `\'api/v1\'` и `\'api/v2\'`.

Альтернативы версионирования (хуже для Yii2):

- **`Accept: application/vnd.myapi.v2+json`** — content-versioning, требует кастомного парсера.
- **`?version=2`** — параметр URL, плохо кешируется.

Структура файлов (basic-шаблон):

```
modules/
  api/
    Module.php       — bootstrap модуля
    v1/
      Module.php
      controllers/
        UserController.php
        PostController.php
      models/
        User.php
    v2/
      Module.php
      controllers/
        UserController.php
```

В **`v2`** обычно меняют `fields()`/`extraFields()` и формат ответа, не ломая `v1`.',
                'code_example' => '// config/web.php
return [
    \'modules\' => [
        \'api\' => [
            \'class\' => \'app\\modules\\api\\Module\',
            \'modules\' => [
                \'v1\' => [\'class\' => \'app\\modules\\api\\v1\\Module\'],
                \'v2\' => [\'class\' => \'app\\modules\\api\\v2\\Module\'],
            ],
        ],
    ],
    \'components\' => [
        \'urlManager\' => [
            \'enablePrettyUrl\' => true,
            \'enableStrictParsing\' => true,
            \'showScriptName\' => false,
            \'rules\' => [
                [\'class\' => \'yii\\rest\\UrlRule\', \'controller\' => \'api/v1/user\', \'prefix\' => \'api/v1\'],
                [\'class\' => \'yii\\rest\\UrlRule\', \'controller\' => \'api/v2/user\', \'prefix\' => \'api/v2\'],
            ],
        ],
    ],
];

// modules/api/v1/controllers/UserController.php
namespace app\\modules\\api\\v1\\controllers;

class UserController extends \\yii\\rest\\ActiveController
{
    public $modelClass = \'app\\modules\\api\\v1\\models\\User\';
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.rest',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как Yii2 REST обрабатывает ошибки и возвращает их в JSON?',
                'answer' => '**`yii\\rest\\Controller`** заменяет компонент **`errorHandler`** на REST-режим — все исключения **автоматически** сериализуются в JSON.

Формат ответа по умолчанию:

```
HTTP/1.1 422 Data Validation Failed
Content-Type: application/json

{
  "name": "Unprocessable Entity",
  "message": "Data Validation Failed.",
  "code": 0,
  "status": 422,
  "type": "yii\\\\web\\\\UnprocessableEntityHttpException"
}
```

Используются HTTP-исключения из **`yii\\web\\*HttpException`**:

| Исключение | Код |
| --- | --- |
| `BadRequestHttpException` | **400** |
| `UnauthorizedHttpException` | **401** |
| `ForbiddenHttpException` | **403** |
| `NotFoundHttpException` | **404** |
| `MethodNotAllowedHttpException` | **405** |
| `UnprocessableEntityHttpException` | **422** (с `errors` от `validate()`) |
| `TooManyRequestsHttpException` | **429** |

В **`dev`**-режиме (`YII_DEBUG=true`) добавляется поле **`stack-trace`** — отключи в проде.

Кастомизация: переопределить **`yii\\rest\\Serializer`** или повесить **`beforeSend`**-обработчик на `response`.',
                'code_example' => 'use Yii;
use yii\\web\\NotFoundHttpException;
use yii\\web\\UnprocessableEntityHttpException;

class PostController extends \\yii\\rest\\Controller
{
    public function actionView($id)
    {
        $post = Post::findOne($id);
        if (!$post) {
            throw new NotFoundHttpException("Post #$id не найден");
        }
        return $post;
    }

    public function actionCreate()
    {
        $post = new Post();
        $post->load(Yii::$app->request->bodyParams, \'\');

        if (!$post->save()) {
            // Yii\\rest\\ActiveController автоматически возвращает 422 с errors
            throw new UnprocessableEntityHttpException(json_encode($post->errors));
        }

        return $post;
    }
}

// Кастомизация ответа об ошибке:
\\Yii::$app->response->on(\\yii\\web\\Response::EVENT_BEFORE_SEND, function ($event) {
    $response = $event->sender;
    if ($response->data !== null && isset($response->data[\'type\'])) {
        unset($response->data[\'type\']);   // прячем класс исключения
    }
});',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.rest',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как работает pagination в Yii2 REST (X-Pagination-* заголовки)?',
                'answer' => 'В Yii2 REST pagination идёт через **`ActiveDataProvider`** — он сам выставляет **`X-Pagination-*`** заголовки и `Link`-заголовок.

Заголовки ответа:

- **`X-Pagination-Total-Count`** — всего записей.
- **`X-Pagination-Page-Count`** — всего страниц.
- **`X-Pagination-Current-Page`** — текущая страница.
- **`X-Pagination-Per-Page`** — размер страницы.
- **`Link`** — ссылки `first`, `prev`, `next`, `last` (RFC 5988).

Параметры запроса:

- **`?page=2`** — страница (имя параметра — `pageParam`).
- **`?per-page=50`** — размер (имя — `pageSizeParam`).

Дефолт **`pageSize = 20`**, максимум обычно ограничивают через **`pagination => [\'pageSizeLimit\' => [1, 100]]`** — иначе клиент может запросить миллион записей.

Параметр **`fields=...`** и **`expand=...`** работают одновременно с pagination — не конфликтуют.',
                'code_example' => 'use yii\\data\\ActiveDataProvider;

class PostController extends \\yii\\rest\\ActiveController
{
    public $modelClass = \'app\\models\\Post\';

    public function actions()
    {
        $actions = parent::actions();
        $actions[\'index\'][\'prepareDataProvider\'] = [$this, \'prepareDataProvider\'];
        return $actions;
    }

    public function prepareDataProvider()
    {
        return new ActiveDataProvider([
            \'query\' => \\app\\models\\Post::find()->where([\'status\' => \'published\']),
            \'pagination\' => [
                \'defaultPageSize\' => 20,
                \'pageSizeLimit\' => [1, 100],   // защита от ?per-page=1000000
            ],
            \'sort\' => [
                \'defaultOrder\' => [\'created_at\' => SORT_DESC],
                \'attributes\' => [\'created_at\', \'title\', \'views\'],
            ],
        ]);
    }
}

// Запрос: GET /posts?page=2&per-page=50
// Ответ:
// HTTP/1.1 200 OK
// X-Pagination-Total-Count: 320
// X-Pagination-Page-Count: 7
// X-Pagination-Current-Page: 2
// X-Pagination-Per-Page: 50
// Link: <...?page=1>; rel="first", <...?page=3>; rel="next"',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.rest',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Какие три стратегии версионирования REST API существуют и как реализовать каждую в Yii2?',
                'answer' => '**Три канонические стратегии** версионирования REST + их trade-offs.

| Стратегия | Пример | SEO | Кэш CDN | Клиентские либы |
|---|---|---|---|---|
| **URL path** | `/api/v1/users` | хорошо (разные URL) | **отлично** (URL = ключ кэша) | проще всего |
| **Header (media-type)** | `Accept: application/vnd.app.v2+json` | плохо (один URL) | требует `Vary: Accept` | сложнее, нужен middleware |
| **Domain / subdomain** | `v2.api.example.com` | отлично | отлично (разные хосты) | разные base URL |

**Реализация URL-versioning в Yii2 через namespace-модули:**

- Создаются модули **`app\\modules\\api\\v1`** и **`app\\modules\\api\\v2`**.
- В каждом — свой набор `controllers/` и `models/` (модели можно наследовать от общей).
- В `urlManager` — два правила `UrlRule` с разными `prefix` и `controller`.

**Реализация Header-versioning:**

- Кастомный **`ContentNegotiator`** или middleware читает `Accept`-header.
- По media-type выбирается модуль/неймспейс.
- Обязательно отдавать заголовок **`Vary: Accept`** иначе CDN отдаст не ту версию.

**Breaking change policy + RFC 8594 `Sunset`:**

- **Минор-релизы** (`v1.1`) — backward-compatible, не требуют новой версии.
- **Мажор** (`v2`) — добавляется параллельно. Старая остаётся работать **минимум 6-12 месяцев**.
- За 90 дней до отключения старой версии отдавать **`Sunset: Sat, 31 Dec 2026 23:59:59 GMT`** (RFC 8594) + **`Deprecation: true`** + `Link: <https://...>; rel="successor-version"`.
- Логировать all `Sunset` requests с user-agent — потом написать клиентам.

**Что НЕ делать:**

- **`?version=2`** — параметр URL: плохо кэшируется, ломает HATEOAS, легко забыть.
- **Hardcode** в коде контроллера через `if/else` — невозможно потом удалить старую версию.
- **Не версионировать вообще** — любое изменение схемы ответа = поломка клиентов.',
                'code_example' => '// 1. config/web.php — URL versioning через namespace-модули
return [
    \'modules\' => [
        \'api-v1\' => [\'class\' => \'app\\modules\\api\\v1\\Module\'],
        \'api-v2\' => [\'class\' => \'app\\modules\\api\\v2\\Module\'],
    ],
    \'components\' => [
        \'urlManager\' => [
            \'enablePrettyUrl\' => true,
            \'enableStrictParsing\' => true,
            \'showScriptName\' => false,
            \'rules\' => [
                [\'class\' => \'yii\\rest\\UrlRule\', \'controller\' => [\'api-v1/user\', \'api-v1/post\'], \'prefix\' => \'api/v1\'],
                [\'class\' => \'yii\\rest\\UrlRule\', \'controller\' => [\'api-v2/user\', \'api-v2/post\'], \'prefix\' => \'api/v2\'],
            ],
        ],
    ],
];

// 2. Header-versioning через кастомный ContentNegotiator
class ApiVersionNegotiator extends \yii\filters\ContentNegotiator
{
    public function negotiate()
    {
        $accept = Yii::$app->request->headers->get(\'Accept\', \'\');
        if (preg_match(\'#application/vnd\\.app\\.v(\\d+)\\+json#\', $accept, $m)) {
            Yii::$app->params[\'apiVersion\'] = (int)$m[1];
        }
        Yii::$app->response->headers->set(\'Vary\', \'Accept\');
        return parent::negotiate();
    }
}

// 3. Sunset header (RFC 8594) для v1, который убираем 31 Dec 2026
class V1Controller extends \yii\rest\ActiveController
{
    public function afterAction($action, $result)
    {
        $r = Yii::$app->response;
        $r->headers->set(\'Deprecation\', \'true\');
        $r->headers->set(\'Sunset\', \'Sat, 31 Dec 2026 23:59:59 GMT\');
        $r->headers->set(\'Link\', \'<https://api.example.com/api/v2/>; rel="successor-version"\');
        Yii::warning("v1 API used: " . Yii::$app->request->userAgent, \'api.deprecated\');
        return parent::afterAction($action, $result);
    }
}',
                'code_language' => 'php',
                'difficulty' => 5,
                'topic' => 'yii2.rest',
            ],
        ];
    }
}
