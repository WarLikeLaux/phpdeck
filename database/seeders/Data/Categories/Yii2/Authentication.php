<?php

namespace Database\Seeders\Data\Categories\Yii2;

class Authentication
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Yii2',
                'question' => 'Что такое IdentityInterface в Yii2 и какие методы он требует?',
                'answer' => '**`IdentityInterface`** — интерфейс (`yii\\web\\IdentityInterface`), который **должна реализовывать модель пользователя**, чтобы её мог использовать компонент `Yii::$app->user`.

Пять обязательных методов:

- **`findIdentity($id)`** — найти пользователя по PK (для восстановления из сессии).
- **`findIdentityByAccessToken($token, $type = null)`** — найти пользователя по API-токену (для stateless REST).
- **`getId()`** — вернуть идентификатор (обычно PK).
- **`getAuthKey()`** — вернуть **secret-ключ** для валидации cookie «remember me».
- **`validateAuthKey($authKey)`** — проверить, совпадает ли переданный ключ с сохранённым.

Если REST API не нужен — `findIdentityByAccessToken()` можно просто `return null`. Если cookie-аутентификация не используется — `getAuthKey()`/`validateAuthKey()` можно сделать заглушками.',
                'code_example' => 'use yii\\db\\ActiveRecord;
use yii\\web\\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    public static function findIdentity($id)
    {
        return static::findOne([\'id\' => $id, \'status\' => self::STATUS_ACTIVE]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne([\'access_token\' => $token]);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.authentication',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое компонент `user` в Yii2 (Yii::$app->user)?',
                'answer' => '**`Yii::$app->user`** — компонент `yii\\web\\User`, отвечающий за **состояние аутентификации** текущего пользователя в web-приложении.

Главные свойства и методы:

- **`identity`** — объект `IdentityInterface` текущего пользователя (или `null` для гостя).
- **`isGuest`** — `true`, если пользователь **не залогинен**.
- **`id`** — PK залогиненного пользователя.
- **`login($identity, $duration = 0)`** — залогинить (опционально с **remember me**).
- **`logout($destroySession = true)`** — разлогинить и (по умолчанию) уничтожить сессию.
- **`can($permission, $params = [], $allowCaching = true)`** — проверить право через **AuthManager** (RBAC).
- **`loginRequired()`** — редирект на `loginUrl`, если не залогинен.

По умолчанию хранит состояние в **PHP-сессии** (web), но для REST настраивается как **stateless**.',
                'code_example' => 'use Yii;

// В контроллере
if (Yii::$app->user->isGuest) {
    return $this->redirect([\'site/login\']);
}

$user = Yii::$app->user->identity;  // объект User
$id = Yii::$app->user->id;          // PK
echo $user->username;

// Логин
Yii::$app->user->login($identity);

// Логаут
Yii::$app->user->logout();

// Проверка права через RBAC
if (Yii::$app->user->can(\'updatePost\', [\'post\' => $post])) {
    // ...
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.authentication',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как настроить компонент `user` в Yii2 (identityClass, enableAutoLogin, loginUrl)?',
                'answer' => 'Компонент `user` настраивается в `components` конфига приложения (`config/web.php`).

Главные опции:

- **`identityClass`** — класс модели, реализующий `IdentityInterface` (обычно `app\\models\\User`).
- **`enableAutoLogin`** — `true` включает **cookie-based remember me** (используется в `login($identity, $duration)`).
- **`loginUrl`** — путь редиректа неавторизованных (по умолчанию `[\'site/login\']`). Если `null` — вместо редиректа кидается `403 Forbidden`.
- **`identityCookie`** — настройки cookie для auto-login (`name`, `httpOnly`).
- **`enableSession`** — `false` для **stateless REST** (см. отдельную карточку).
- **`authTimeout`** — авто-логаут после `N` секунд неактивности.
- **`absoluteAuthTimeout`** — жёсткий лимит, даже если пользователь активен.',
                'code_example' => '// config/web.php
return [
    \'components\' => [
        \'user\' => [
            \'identityClass\' => \'app\\models\\User\',
            \'enableAutoLogin\' => true,
            \'loginUrl\' => [\'site/login\'],
            \'identityCookie\' => [
                \'name\' => \'_identity\',
                \'httpOnly\' => true,
            ],
            \'authTimeout\' => 3600,           // 1 час бездействия
            \'absoluteAuthTimeout\' => 86400,  // макс 24 часа
        ],
    ],
];',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.authentication',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Чем сессионная аутентификация отличается от stateless в Yii2 REST API?',
                'answer' => 'Web-приложение хранит логин в **PHP-сессии**, REST API — **stateless** (каждый запрос несёт токен).

| Признак | Web (session) | REST (stateless) |
| --- | --- | --- |
| Где состояние | в **`$_SESSION`** на сервере | **нигде** — токен в каждом запросе |
| Метод входа | `login($identity)` пишет в сессию | передаётся `Authorization: Bearer <token>` |
| Что вызывается | `findIdentity($id)` (id из сессии) | **`findIdentityByAccessToken($token)`** |
| `enableSession` | `true` (по умолчанию) | **`false`** |
| `loginUrl` | `[\'site/login\']` | **`null`** (кидать `401`) |
| Auth-метод | внутренний (cookie/session) | **`HttpBearerAuth`** / `QueryParamAuth` |

Для REST в конфиге выключают сессию и куки, иначе при каждом запросе будет создаваться сессия зря.',
                'code_example' => '// config/web.php — конфиг REST-приложения
return [
    \'components\' => [
        \'user\' => [
            \'identityClass\' => \'app\\models\\User\',
            \'enableAutoLogin\' => false,
            \'enableSession\' => false,   // stateless
            \'loginUrl\' => null,         // кидать 401, не редиректить
        ],
        \'request\' => [
            \'enableCookieValidation\' => false,
            \'enableCsrfValidation\' => false,  // REST не нуждается в CSRF
            \'parsers\' => [
                \'application/json\' => \'yii\\web\\JsonParser\',
            ],
        ],
    ],
];',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.authentication',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как работает "remember me" в Yii2 (параметр $duration в login())?',
                'answer' => '**`login($identity, $duration)`** — второй аргумент **`$duration`** в секундах активирует **cookie-based auto-login**.

Что происходит:

- Если **`$duration > 0`** и `enableAutoLogin = true`, Yii выставляет cookie **`_identity`** (по умолчанию) с `id`, `authKey`, `duration`.
- При следующем запросе после истечения сессии Yii **читает cookie**, находит пользователя по `id` и **сравнивает `authKey`** через `validateAuthKey()`.
- Если ключ совпал — пользователь авто-залогинен.

Зачем `authKey`:

- Если злоумышленник украл cookie — можно **сбросить `auth_key`** в БД (например при смене пароля), и все старые cookie перестанут работать.
- `id` сам по себе **не секрет** — нужен второй фактор для валидации.

Типичный `$duration`: **30 дней** = `2592000` секунд.',
                'code_example' => '// SiteController::actionLogin
public function actionLogin()
{
    $model = new LoginForm();

    if ($model->load(Yii::$app->request->post()) && $model->validate()) {
        $duration = $model->rememberMe ? 3600 * 24 * 30 : 0;  // 30 дней или сессия
        Yii::$app->user->login($model->getUser(), $duration);

        return $this->goBack();
    }

    return $this->render(\'login\', [\'model\' => $model]);
}

// При смене пароля — сбросить authKey, чтобы все старые cookie умерли
public function setPassword($password)
{
    $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    $this->auth_key = Yii::$app->security->generateRandomString();
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.authentication',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что обычно возвращает `getId()` в IdentityInterface?',
                'answer' => '**`getId()`** возвращает **уникальный идентификатор** пользователя, который Yii кладёт в **сессию** (или в cookie auto-login) и потом передаёт обратно в `findIdentity($id)`.

Обычно:

- **`return $this->id;`** — целое число, PK таблицы `user`.
- Но может быть **UUID** (`string`), email или составной идентификатор — Yii не накладывает ограничений на тип.

Важные нюансы:

- Значение **сериализуется** в `$_SESSION[\'__id\']` — должно быть **скалярным** (или сериализуемым).
- `findIdentity()` должен уметь принять **именно то**, что вернул `getId()`.
- Если используешь soft-delete или статусы — фильтруй в `findIdentity()`: пользователь со `status = inactive` не должен восстанавливаться из сессии.',
                'code_example' => 'class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 10;

    public function getId()
    {
        return $this->getPrimaryKey();   // эквивалент $this->id для простого PK
    }

    public static function findIdentity($id)
    {
        // Восстанавливаем только активных
        return static::findOne([
            \'id\' => $id,
            \'status\' => self::STATUS_ACTIVE,
        ]);
    }
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.authentication',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как реализовать findIdentityByAccessToken для REST API в Yii2?',
                'answer' => '**`findIdentityByAccessToken($token, $type = null)`** — вызывается **auth-методами REST** (`HttpBearerAuth`, `QueryParamAuth`, `HttpHeaderAuth`) для поиска пользователя по токену.

Минимальная реализация — найти пользователя по колонке `access_token`:

- Обычно `access_token` — отдельная колонка в таблице `user`, генерируется при логине.
- Параметр **`$type`** — класс auth-метода (например `yii\\filters\\auth\\HttpBearerAuth::class`), позволяет различать типы токенов.
- Для безопасности: используй **длинный random-string** (`Yii::$app->security->generateRandomString(64)`).
- Лучше хранить **отдельную таблицу** `user_token` с TTL (для отзыва, ротации, многократного логина с разных устройств).

Если в проекте нет REST — `return null;` достаточно.',
                'code_example' => 'class User extends ActiveRecord implements IdentityInterface
{
    public static function findIdentityByAccessToken($token, $type = null)
    {
        if (empty($token)) {
            return null;
        }

        return static::findOne([
            \'access_token\' => $token,
            \'status\' => self::STATUS_ACTIVE,
        ]);
    }

    public function generateAccessToken()
    {
        $this->access_token = Yii::$app->security->generateRandomString(64);
    }
}

// Использование в REST-контроллере:
public function behaviors()
{
    $behaviors = parent::behaviors();
    $behaviors[\'authenticator\'] = [
        \'class\' => \\yii\\filters\\auth\\HttpBearerAuth::class,
    ];
    return $behaviors;
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.authentication',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как использовать Yii::$app->user->can() для проверки прав?',
                'answer' => '**`Yii::$app->user->can($permissionName, $params = [], $allowCaching = true)`** — основной способ проверки прав через **RBAC** (компонент `authManager`).

Что делает:

- Ищет, **назначено** ли пользователю разрешение (или роль, содержащая это разрешение) через `auth_assignment`.
- Если у разрешения есть **`rule`** (PHP-класс правила) — вызывает `execute()` правила с `$params`.
- Возвращает `bool`.

Параметры:

- **`$permissionName`** — строка имени разрешения или роли (например `\'updatePost\'`).
- **`$params`** — массив параметров для **rule-объекта** (например `[\'post\' => $post]` для `AuthorRule`).
- **`$allowCaching`** — кешировать ли результат внутри запроса (по умолчанию `true`).

Если `authManager` **не настроен** — `can()` всегда возвращает `false`.',
                'code_example' => 'use Yii;

// Простая проверка
if (Yii::$app->user->can(\'admin\')) {
    // только админ
}

// Проверка с параметрами — для AuthorRule
public function actionUpdate($id)
{
    $post = Post::findOne($id);

    if (!Yii::$app->user->can(\'updatePost\', [\'post\' => $post])) {
        throw new \\yii\\web\\ForbiddenHttpException(\'Нет прав на редактирование\');
    }

    // ...редактирование
}

// В представлении (view)
<?php if (Yii::$app->user->can(\'deletePost\', [\'post\' => $post])): ?>
    <?= Html::a(\'Удалить\', [\'delete\', \'id\' => $post->id]) ?>
<?php endif; ?>',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.authentication',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Какие события вызывает компонент user (EVENT_AFTER_LOGIN, EVENT_AFTER_LOGOUT)?',
                'answer' => 'Компонент **`yii\\web\\User`** вызывает события на ключевых этапах жизненного цикла:

- **`EVENT_BEFORE_LOGIN`** — перед входом, можно **отменить** (`$event->isValid = false`).
- **`EVENT_AFTER_LOGIN`** — после успешного входа.
- **`EVENT_BEFORE_LOGOUT`** — перед выходом, можно отменить.
- **`EVENT_AFTER_LOGOUT`** — после выхода.

Объект события — **`yii\\web\\UserEvent`**, у него есть:

- **`identity`** — объект пользователя.
- **`cookieBased`** — `true`, если вход через cookie auto-login.
- **`duration`** — длительность `remember me` (для `login`).

Типичные применения:

- Логирование входов/выходов (security audit).
- Сброс welcome-баннеров, обновление `last_login_at`.
- Очистка кешей пользователя при logout.',
                'code_example' => 'use Yii;
use yii\\web\\User;
use yii\\web\\UserEvent;

// В bootstrap (config/web.php → \'bootstrap\' => [\'log\', \'audit\'])
// или в init()-методе компонента
Yii::$app->user->on(User::EVENT_AFTER_LOGIN, function (UserEvent $event) {
    /** @var \\app\\models\\User $user */
    $user = $event->identity;
    $user->updateAttributes([
        \'last_login_at\' => time(),
        \'last_login_ip\' => Yii::$app->request->userIP,
    ]);

    Yii::info("User {$user->id} logged in", \'auth\');
});

Yii::$app->user->on(User::EVENT_AFTER_LOGOUT, function (UserEvent $event) {
    Yii::info("User {$event->identity->id} logged out", \'auth\');
});',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.authentication',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Зачем нужен authKey в Yii2 и как он защищает cookie auto-login?',
                'answer' => '**`auth_key`** — случайная строка, привязанная к пользователю в БД, нужна для **защиты cookie auto-login** ("remember me").

Зачем:

- Cookie auto-login содержит **`[id, authKey, duration]`** в **подписанном** виде (через `cookieValidationKey`).
- При следующем запросе Yii читает cookie, вызывает `findIdentity($id)` и **`validateAuthKey($authKey)`**.
- Если `authKey` в БД **не совпадает** с переданным — Yii **отвергает** вход.

Сценарии безопасности:

- **Смена пароля** → перегенерируем `auth_key` → все старые cookie **умирают**.
- **Логаут с других устройств** → перегенерируем `auth_key` → активная сессия остаётся (пока жив `$_SESSION`), остальные cookie мертвы.
- **Компрометация cookie** → админ сбрасывает `auth_key` пользователя.

`authKey` НЕ заменяет cookie-подпись (`cookieValidationKey`) — это **второй уровень** защиты на стороне БД.',
                'code_example' => 'class User extends ActiveRecord implements IdentityInterface
{
    // Вызывается при создании пользователя
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($insert) {
            $this->generateAuthKey();
        }
        return true;
    }

    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        // hash_equals — защита от timing attack
        return Yii::$app->security->compareString($this->auth_key, $authKey);
    }
}

// При смене пароля — сбросить authKey
$user->setPassword($newPassword);
$user->generateAuthKey();   // все старые cookie теперь невалидны
$user->save();',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.authentication',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как мигрировать Yii2 с session auth на JWT: что сломается, refresh tokens, revocation strategy?',
                'answer' => '**Контекст:** monolith на Yii2 сделан с session-based auth. Появилось мобильное приложение / SPA / микросервисы → нужен **stateless JWT**. Это **не просто** «поменять компонент».

**Что сломается / нужно изменить в конфиге:**

| Компонент | Было (session) | Стало (JWT) |
|---|---|---|
| `user.enableSession` | `true` | **`false`** |
| `user.enableAutoLogin` | `true` | **`false`** |
| `user.loginUrl` | `[\'site/login\']` | **`null`** (кидать `401`) |
| `request.enableCookieValidation` | `true` | **`false`** |
| `request.enableCsrfValidation` | `true` | **`false`** (CSRF не нужен — нет cookie с auth) |
| `User::findIdentityByAccessToken` | `return null;` (заглушка) | **полная реализация** с JWT-валидацией |

**Почему CSRF не нужен с JWT:**

- CSRF атакует когда **браузер автоматически шлёт** auth-cookie на чужой origin.
- JWT хранится в `Authorization: Bearer` или в `localStorage` — браузер **сам его не пришлёт**.
- НО: если хранить JWT в `HttpOnly cookie` — CSRF снова становится актуален.

**Refresh tokens — два подхода:**

| Подход | Где хранится | Плюсы | Минусы |
|---|---|---|---|
| **Только в claims** (stateless) | внутри самого JWT | нет БД-запросов | нельзя отозвать до истечения |
| **В БД с rotation** | таблица `refresh_token` | можно отозвать, audit | требует БД-запрос на каждый refresh |

**Revocation strategy — две техники:**

| Стратегия | Как работает | Когда брать |
|---|---|---|
| **Token versioning** (через `auth_key`) | в JWT кладём `auth_key_version`, при logout инкрементируем в БД | мало пользователей, простой кейс |
| **JTI blacklist** | каждый JWT имеет уникальный `jti`, при logout пишем в Redis с TTL = exp токена | масштабируемо, audit-friendly |

**Реализация `findIdentityByAccessToken`:**

- Парсить JWT, проверить **подпись** через `firebase/php-jwt`.
- Проверить **`exp`** (истёк ли).
- Проверить `iss` / `aud` / `nbf` (issuer / audience / not before).
- Опционально: проверить **`jti`** в blacklist (Redis).
- Опционально: сравнить `auth_key_version` из токена с актуальным в БД.
- Если всё OK — вернуть `User::findOne($claims->sub)`.

**Подводные камни:**

- **`exp` long-lived JWT** = невозможность мгновенного logout. Делай **`exp <= 15 минут`**, всё остальное — через refresh.
- **Symmetric (HS256) vs asymmetric (RS256)**: для multi-service → **RS256** (микросервисы валидируют public key, не имея secret).
- **`alg=none` vulnerability** — всегда **whitelist** разрешённых алгоритмов в декодере.
- **Clock skew** между серверами — добавь `leeway` 30 сек.',
                'code_example' => '// 1. config/web.php — JWT-режим
return [
    \'components\' => [
        \'user\' => [
            \'identityClass\' => \'app\\models\\User\',
            \'enableSession\' => false,
            \'enableAutoLogin\' => false,
            \'loginUrl\' => null,
        ],
        \'request\' => [
            \'enableCookieValidation\' => false,
            \'enableCsrfValidation\' => false,
            \'parsers\' => [
                \'application/json\' => \'yii\\web\\JsonParser\',
            ],
        ],
    ],
];

// 2. models/User.php — findIdentityByAccessToken c полной JWT-валидацией
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
    public static function findIdentityByAccessToken($token, $type = null)
    {
        try {
            $key = new Key(Yii::$app->params[\'jwtPublicKey\'], \'RS256\');
            JWT::$leeway = 30;   // tolerate 30s clock skew
            $claims = JWT::decode($token, $key);

            // Проверка blacklist по jti (Redis)
            if (Yii::$app->cache->exists("jwt:blacklist:{$claims->jti}")) {
                return null;
            }

            $user = static::findOne([\'id\' => $claims->sub, \'status\' => self::STATUS_ACTIVE]);
            if (!$user) return null;

            // Token versioning: сравнить с актуальным auth_key
            if ($claims->avk !== $user->auth_key_version) {
                return null;
            }
            return $user;
        } catch (\Throwable $e) {
            return null;
        }
    }
}

// 3. POST /api/login — выдача пары токенов
public function actionLogin()
{
    $user = User::findOne([\'email\' => Yii::$app->request->post(\'email\')]);
    if (!$user || !$user->validatePassword(Yii::$app->request->post(\'password\'))) {
        throw new \yii\web\UnauthorizedHttpException(\'invalid credentials\');
    }

    $now = time();
    $accessClaims = [
        \'iss\' => \'app.example.com\',
        \'aud\' => \'api.example.com\',
        \'sub\' => $user->id,
        \'jti\' => bin2hex(random_bytes(16)),
        \'iat\' => $now,
        \'exp\' => $now + 900,         // 15 min
        \'avk\' => $user->auth_key_version,
    ];
    $accessToken = JWT::encode($accessClaims, Yii::$app->params[\'jwtPrivateKey\'], \'RS256\');

    // Refresh token — отдельная запись в БД с rotation
    $refresh = new RefreshToken([
        \'user_id\' => $user->id,
        \'token_hash\' => hash(\'sha256\', $refreshTokenPlain = bin2hex(random_bytes(32))),
        \'expires_at\' => $now + 86400 * 30,
    ]);
    $refresh->save();

    return [\'access\' => $accessToken, \'refresh\' => $refreshTokenPlain];
}

// 4. Revocation: logout — пишем jti в blacklist на оставшийся TTL
public function actionLogout()
{
    $token = preg_replace(\'/^Bearer /\', \'\', Yii::$app->request->headers->get(\'Authorization\'));
    $claims = JWT::decode($token, new Key(Yii::$app->params[\'jwtPublicKey\'], \'RS256\'));
    Yii::$app->cache->set("jwt:blacklist:{$claims->jti}", 1, $claims->exp - time());
    return [\'ok\' => true];
}

// 5. "Logout from all devices" — token versioning
public function logoutEverywhere(User $user)
{
    $user->auth_key_version = bin2hex(random_bytes(8));
    $user->save();   // все старые JWT сразу невалидны
}',
                'code_language' => 'php',
                'difficulty' => 5,
                'topic' => 'yii2.authentication',
            ],
        ];
    }
}
