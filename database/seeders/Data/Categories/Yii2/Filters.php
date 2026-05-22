<?php

namespace Database\Seeders\Data\Categories\Yii2;

class Filters
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Yii2',
                'question' => 'Что такое filter в Yii2?',
                'answer' => '**Filter** в Yii2 — это **`Behavior`**, прицепленный к контроллеру, который вмешивается в `beforeAction` и/или `afterAction`.

Особенности:

- Базовый класс — **`yii\\base\\ActionFilter`** (наследник `Behavior`).
- Подключается через метод **`behaviors()`** контроллера.
- Может **прерывать** action (вернуть `false` из `beforeAction()` или бросить исключение).
- Может **модифицировать** ответ в `afterAction()`.
- Можно ограничивать **`only`** / **`except`** — список actions.

Встроенные фильтры:

- **`AccessControl`** — авторизация.
- **`VerbFilter`** — ограничение HTTP-методов.
- **`ContentNegotiator`** — выбор формата ответа.
- **`HttpCache` / `PageCache`** — кеширование.
- **`Cors`** — CORS.
- **`auth/*`** — аутентификация для REST.
- **`RateLimiter`** — лимит запросов.
- **`AjaxFilter`** — только AJAX.',
                'code_example' => 'use yii\\filters\\AccessControl;
use yii\\filters\\VerbFilter;

class PostController extends \\yii\\web\\Controller
{
    public function behaviors()
    {
        return [
            \'access\' => [
                \'class\' => AccessControl::class,
                \'rules\' => [
                    [\'allow\' => true, \'roles\' => [\'@\']],
                ],
            ],
            \'verbs\' => [
                \'class\' => VerbFilter::class,
                \'actions\' => [
                    \'delete\' => [\'POST\', \'DELETE\'],
                ],
            ],
        ];
    }
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.filters',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как работает метод behaviors() в контроллере Yii2?',
                'answer' => 'Метод **`behaviors()`** возвращает массив, описывающий **`Behavior`-объекты** (включая фильтры), которые цепляются к контроллеру.

Структура:

- **Ключ** — имя поведения (можно ссылаться, удалять, переопределять).
- **Значение** — конфиг (массив с `class` или объект `Behavior`).

Особенности:

- Вызывается **один раз** при инициализации контроллера (`init()`).
- Если контроллер наследует от родителя — обычно делают **`array_merge(parent::behaviors(), [...])`**, чтобы не потерять родительские (важно для `ActiveController`).
- Поведения с тем же ключом **перезаписывают** родительские.
- Несколько фильтров выполняются **в порядке** объявления (по `beforeAction`); в `afterAction` — в обратном порядке.

В REST-контроллерах (`yii\\rest\\Controller`, `yii\\rest\\ActiveController`) уже подключены `authenticator`, `contentNegotiator`, `verbFilter`, `rateLimiter`, `corsFilter` — для кастомизации мерджим с родителем.',
                'code_example' => 'use yii\\rest\\ActiveController;
use yii\\filters\\auth\\HttpBearerAuth;

class UserController extends ActiveController
{
    public $modelClass = \'app\\models\\User\';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Заменяем authenticator
        $behaviors[\'authenticator\'] = [
            \'class\' => HttpBearerAuth::class,
            \'except\' => [\'options\'],   // OPTIONS — без auth для CORS preflight
        ];

        // Добавляем свой фильтр
        $behaviors[\'verbs\'] = [
            \'class\' => \\yii\\filters\\VerbFilter::class,
            \'actions\' => [\'delete\' => [\'DELETE\']],
        ];

        return $behaviors;
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.filters',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как настроить AccessControl в Yii2 (@/?, roles, allow/deny)?',
                'answer' => '**`yii\\filters\\AccessControl`** проверяет правила **сверху вниз**; первое подходящее **`allow`/`deny`** срабатывает. По умолчанию — **deny all**.

Главные ключи правила:

- **`allow`** — `true` пропустить, `false` запретить.
- **`actions`** — список actions (пустой — все).
- **`roles`** — список ролей:
  - **`@`** — любой залогиненный.
  - **`?`** — любой гость.
  - **`admin`**, **`editor`** — конкретные роли (через `authManager`).
- **`permissions`** — RBAC-разрешения (`updatePost`).
- **`ips`** — список IP с wildcard (`192.168.1.*`).
- **`verbs`** — HTTP-методы (`GET`, `POST`).
- **`matchCallback`** — функция для кастомной логики.
- **`denyCallback`** — что делать при `deny` (по умолчанию `ForbiddenHttpException`).

Свойство **`only`** / **`except`** на фильтре ограничивает, к каким actions он вообще применяется.',
                'code_example' => 'use yii\\filters\\AccessControl;

public function behaviors()
{
    return [
        \'access\' => [
            \'class\' => AccessControl::class,
            \'only\' => [\'create\', \'update\', \'delete\', \'view\'],
            \'rules\' => [
                // 1. Гости могут только view
                [
                    \'allow\' => true,
                    \'actions\' => [\'view\'],
                    \'roles\' => [\'?\'],
                ],
                // 2. Залогиненные — create/update
                [
                    \'allow\' => true,
                    \'actions\' => [\'create\', \'update\'],
                    \'roles\' => [\'@\'],
                ],
                // 3. delete — только admin
                [
                    \'allow\' => true,
                    \'actions\' => [\'delete\'],
                    \'roles\' => [\'admin\'],
                ],
                // 4. ip-whitelist для админки
                [
                    \'allow\' => true,
                    \'actions\' => [\'update\'],
                    \'ips\' => [\'192.168.1.*\'],
                ],
            ],
        ],
    ];
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.filters',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как работает VerbFilter в Yii2?',
                'answer' => '**`yii\\filters\\VerbFilter`** — фильтр, ограничивающий **разрешённые HTTP-методы** для конкретных actions.

Зачем нужен:

- Защита от **CSRF**-вариантов: например, `delete` через `GET` — анти-паттерн.
- Семантика **REST**: `POST` — создание, `DELETE` — удаление.
- Если метод не разрешён → **`405 Method Not Allowed`** с заголовком `Allow`.

Конфиг — массив **`action => [methods]`**:

- `[\'POST\']` — только POST.
- `[\'POST\', \'DELETE\']` — оба варианта.
- Используют для `actionDelete`, `actionUpdate`, `actionLogout`.

В REST-контроллерах подключён по умолчанию (`yii\\rest\\Controller::verbs()`) с правильными методами для CRUD.',
                'code_example' => 'use yii\\filters\\VerbFilter;

class PostController extends \\yii\\web\\Controller
{
    public function behaviors()
    {
        return [
            \'verbs\' => [
                \'class\' => VerbFilter::class,
                \'actions\' => [
                    \'index\'  => [\'GET\'],
                    \'view\'   => [\'GET\'],
                    \'create\' => [\'POST\'],
                    \'update\' => [\'PUT\', \'POST\'],
                    \'delete\' => [\'DELETE\', \'POST\'],
                    \'logout\' => [\'POST\'],
                ],
            ],
        ];
    }
}

// При GET /post/delete?id=1 ответ:
// HTTP/1.1 405 Method Not Allowed
// Allow: DELETE, POST',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.filters',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое ContentNegotiator в Yii2 и как переключить формат ответа?',
                'answer' => '**`yii\\filters\\ContentNegotiator`** определяет **формат ответа** и **язык** на основе:

- Заголовка **`Accept`** (например `application/json`, `application/xml`).
- Параметра запроса **`?format=json`** (имя задаётся в `formatParam`).
- Параметра **`?lang=ru`** для языка (имя в `languageParam`).

Поддерживаемые форматы (`Response::FORMAT_*`):

- **`json`** → `application/json`.
- **`xml`** → `application/xml`.
- **`html`** → `text/html`.
- **`jsonp`** → `application/javascript`.

В REST-контроллерах **уже подключён** с приоритетом JSON, для web — обычно подключают в `config/web.php` через **`as contentNegotiator`** на уровне приложения.

В контроллере достаточно `return $array` — Yii **сам сериализует** в JSON/XML по выбранному формату.',
                'code_example' => '// config/web.php — глобально для всего приложения
return [
    \'as contentNegotiator\' => [
        \'class\' => \\yii\\filters\\ContentNegotiator::class,
        \'formats\' => [
            \'application/json\' => \\yii\\web\\Response::FORMAT_JSON,
            \'application/xml\'  => \\yii\\web\\Response::FORMAT_XML,
        ],
    ],
];

// Локально в контроллере:
public function behaviors()
{
    return [
        \'contentNegotiator\' => [
            \'class\' => \\yii\\filters\\ContentNegotiator::class,
            \'only\' => [\'api\'],
            \'formats\' => [
                \'application/json\' => \\yii\\web\\Response::FORMAT_JSON,
            ],
            \'languages\' => [\'en\', \'ru\'],
        ],
    ];
}

// Клиент: Accept: application/xml  → XML
// или GET /post/index?format=xml',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.filters',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как работает RateLimiter в Yii2 REST?',
                'answer' => '**`yii\\filters\\RateLimiter`** ограничивает **частоту запросов** для REST API (по умолчанию подключён в `yii\\rest\\Controller`).

Требования:

- Модель пользователя должна реализовывать **`yii\\filters\\RateLimitInterface`** (методы `getRateLimit`, `loadAllowance`, `saveAllowance`).
- `getRateLimit($request, $action)` возвращает **`[$limit, $window]`** — сколько запросов за сколько секунд.
- Состояние хранится **в той же БД** (обычно колонки `allowance` + `allowance_updated_at` на user).

Алгоритм — **leaky bucket**:

- При каждом запросе считаем, сколько «токенов» восстановилось с прошлого запроса.
- Если токенов **меньше 1** — **`429 Too Many Requests`** + заголовки `X-Rate-Limit-Limit`, `X-Rate-Limit-Remaining`, `X-Rate-Limit-Reset`.
- Иначе уменьшаем баланс на 1 и пропускаем.

Если **`user`** есть только для авторизованных — гостям лимит не применяется. Для лимита всем (включая гостей) — обычно используют **отдельный прокси/nginx limit_req** или внешние `Redis`-фильтры.',
                'code_example' => 'use yii\\base\\Model;
use yii\\filters\\RateLimitInterface;

class User extends Model implements RateLimitInterface, \\yii\\web\\IdentityInterface
{
    public $allowance;
    public $allowance_updated_at;

    public function getRateLimit($request, $action)
    {
        return [100, 600];   // 100 запросов за 600 секунд
    }

    public function loadAllowance($request, $action)
    {
        return [$this->allowance, $this->allowance_updated_at];
    }

    public function saveAllowance($request, $action, $allowance, $timestamp)
    {
        $this->allowance = $allowance;
        $this->allowance_updated_at = $timestamp;
        $this->save(false);
    }
}

// В REST-контроллере уже подключён:
// $behaviors[\'rateLimiter\'][\'class\'] = RateLimiter::class;
// $behaviors[\'rateLimiter\'][\'enableRateLimitHeaders\'] = true;',
                'code_language' => 'php',
                'difficulty' => 5,
                'topic' => 'yii2.filters',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как работают HttpCache и PageCache в Yii2?',
                'answer' => 'Два фильтра кеширования с **разной стратегией**.

| Фильтр | Где кеш | Что делает | Когда применять |
| --- | --- | --- | --- |
| **`HttpCache`** | **в браузере** | возвращает `304 Not Modified` через `Last-Modified` / `ETag` | публичные ресурсы, статика, RSS |
| **`PageCache`** | **на сервере** (cache-component) | целиком сохраняет HTML страницы | гостевые лендинги, тяжёлый рендер |

**`HttpCache`** — главные опции:

- **`lastModified`** — callback, возвращающий `timestamp` последнего изменения.
- **`etagSeed`** — callback, возвращающий данные для ETag.
- При совпадении заголовков Yii **прерывает action** и шлёт `304`.

**`PageCache`** — главные опции:

- **`duration`** — TTL в секундах.
- **`dependency`** — `yii\\caching\\Dependency` (например `DbDependency` по запросу).
- **`variations`** — массив параметров, от которых зависит кеш (язык, роль).',
                'code_example' => 'use yii\\filters\\HttpCache;
use yii\\filters\\PageCache;

public function behaviors()
{
    return [
        \'httpCache\' => [
            \'class\' => HttpCache::class,
            \'only\' => [\'view\'],
            \'lastModified\' => function ($action, $params) {
                $q = (new \\yii\\db\\Query())->from(\'post\');
                return $q->max(\'updated_at\');
            },
        ],
        \'pageCache\' => [
            \'class\' => PageCache::class,
            \'only\' => [\'index\'],
            \'duration\' => 3600,
            \'variations\' => [
                Yii::$app->language,
                Yii::$app->user->isGuest ? \'guest\' : \'auth\',
            ],
            \'dependency\' => [
                \'class\' => \\yii\\caching\\DbDependency::class,
                \'sql\' => \'SELECT MAX(updated_at) FROM post\',
            ],
        ],
    ];
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.filters',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как настроить CORS-фильтр в Yii2 REST?',
                'answer' => '**`yii\\filters\\Cors`** добавляет **CORS-заголовки** (`Access-Control-Allow-*`) и обрабатывает **preflight `OPTIONS`**.

Главные опции:

- **`Origin`** — массив разрешённых origins или `[\'*\']`.
- **`Access-Control-Request-Method`** — допустимые методы.
- **`Access-Control-Request-Headers`** — допустимые заголовки.
- **`Access-Control-Allow-Credentials`** — `true` для cookie/auth (но тогда **нельзя `*`** в Origin).
- **`Access-Control-Max-Age`** — TTL preflight-ответа.

Критичный момент для REST: **`corsFilter` нужно регистрировать ДО `authenticator`**, и **`authenticator`** должен исключать **`OPTIONS`** (`except => [\'options\']`), иначе preflight упадёт на аутентификации.

В `yii\\rest\\ActiveController` действие `options` уже есть для CORS preflight.',
                'code_example' => 'use yii\\filters\\Cors;
use yii\\filters\\auth\\HttpBearerAuth;

class UserController extends \\yii\\rest\\ActiveController
{
    public $modelClass = \'app\\models\\User\';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // 1. CORS — ПЕРЕД auth
        $behaviors[\'corsFilter\'] = [
            \'class\' => Cors::class,
            \'cors\' => [
                \'Origin\' => [\'https://app.example.com\'],
                \'Access-Control-Request-Method\' => [\'GET\', \'POST\', \'PUT\', \'DELETE\', \'OPTIONS\'],
                \'Access-Control-Request-Headers\' => [\'Authorization\', \'Content-Type\'],
                \'Access-Control-Allow-Credentials\' => true,
                \'Access-Control-Max-Age\' => 3600,
            ],
        ];

        // 2. Auth — ПОСЛЕ cors, OPTIONS обходит auth
        $behaviors[\'authenticator\'] = [
            \'class\' => HttpBearerAuth::class,
            \'except\' => [\'options\'],
        ];

        return $behaviors;
    }
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.filters',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Чем отличаются HttpBasicAuth, HttpBearerAuth, QueryParamAuth и CompositeAuth в Yii2?',
                'answer' => 'В Yii2 REST есть **четыре** встроенных auth-метода (`yii\\filters\\auth\\*`).

| Auth-метод | Где токен | RFC / типовой клиент |
| --- | --- | --- |
| **`HttpBasicAuth`** | `Authorization: Basic <base64(login:pass)>` | RFC 7617, простые скрипты |
| **`HttpBearerAuth`** | `Authorization: Bearer <token>` | RFC 6750, OAuth, **стандарт REST** |
| **`HttpHeaderAuth`** | произвольный заголовок (`X-Api-Key`) | кастомные API |
| **`QueryParamAuth`** | `?access-token=<token>` | webhook, простые интеграции |
| **`CompositeAuth`** | **несколько методов** на одном endpoint | гибридные API |

Все они вызывают **`User::findIdentityByAccessToken($token, $authClass)`** на модели.

**`CompositeAuth`** пробует методы **по очереди** — первый, вернувший identity, выигрывает. Удобно, когда часть клиентов — браузер (`HttpBasicAuth`), часть — мобильные приложения (`HttpBearerAuth`).

**Правило**: для современного REST — **`HttpBearerAuth`**; `QueryParamAuth` опасен (токен попадает в логи nginx, history, Referer).',
                'code_example' => 'use yii\\filters\\auth\\CompositeAuth;
use yii\\filters\\auth\\HttpBasicAuth;
use yii\\filters\\auth\\HttpBearerAuth;
use yii\\filters\\auth\\QueryParamAuth;

public function behaviors()
{
    $behaviors = parent::behaviors();

    $behaviors[\'authenticator\'] = [
        \'class\' => CompositeAuth::class,
        \'authMethods\' => [
            HttpBasicAuth::class,
            HttpBearerAuth::class,
            [\'class\' => QueryParamAuth::class, \'tokenParam\' => \'access-token\'],
        ],
    ];

    return $behaviors;
}

// User модель:
public static function findIdentityByAccessToken($token, $type = null)
{
    // $type — класс auth-метода, можно различать тип токена
    return static::findOne([\'access_token\' => $token]);
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.filters',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое AjaxFilter в Yii2?',
                'answer' => '**`yii\\filters\\AjaxFilter`** пропускает **только AJAX-запросы** (заголовок **`X-Requested-With: XMLHttpRequest`**).

Что делает:

- При **не-AJAX**-запросе бросает **`BadRequestHttpException` (400)**.
- Удобен для actions, которые **не должны** открываться в браузере напрямую (autocomplete-эндпоинт, validate-form).

Ограничения:

- jQuery/axios автоматически шлют `X-Requested-With` — другие клиенты могут не слать.
- Заголовок **подделывается** — не используется как защита, **только** как маршрутный фильтр (UX, не security).
- Для безопасности всё равно нужны **CSRF** и **AccessControl**.

`only` / `except` работает так же, как у других фильтров.',
                'code_example' => 'use yii\\filters\\AjaxFilter;

class UserController extends \\yii\\web\\Controller
{
    public function behaviors()
    {
        return [
            \'ajax\' => [
                \'class\' => AjaxFilter::class,
                \'only\' => [\'autocomplete\', \'validate-email\'],
            ],
        ];
    }

    public function actionAutocomplete($q)
    {
        // только из JS, иначе 400
        \\Yii::$app->response->format = \\yii\\web\\Response::FORMAT_JSON;
        return User::find()
            ->select([\'id\', \'username\'])
            ->where([\'like\', \'username\', $q])
            ->limit(10)
            ->asArray()
            ->all();
    }
}

// Из JS:
// fetch(\'/user/autocomplete?q=mar\', { headers: { \'X-Requested-With\': \'XMLHttpRequest\' }})',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.filters',
            ],
        ];
    }
}
