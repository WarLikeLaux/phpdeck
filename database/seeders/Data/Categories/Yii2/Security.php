<?php

namespace Database\Seeders\Data\Categories\Yii2;

class Security
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Yii2',
                'question' => 'Что такое компонент security в Yii2 и какие задачи он решает?',
                'answer' => '**`Yii::$app->security`** — компонент **`yii\\base\\Security`**, набор криптографических утилит, обёрнутых над PHP-функциями (с правильными дефолтами).

Главные группы методов:

- **Хеширование паролей** — `generatePasswordHash($pass)` / `validatePassword($pass, $hash)` (через bcrypt).
- **Шифрование** — `encryptByPassword` / `encryptByKey` / `decrypt*`.
- **Случайные данные** — `generateRandomString($length)` / `generateRandomKey($length)` (использует `random_bytes`).
- **HMAC** — `hashData($data, $key)` / `validateData($data, $key)` (для подписанных URL, cookie).
- **Защита от timing-атак** — `compareString($a, $b)` (обёртка `hash_equals`).
- **MAC-валидация** — генерация и проверка HMAC.

Все методы **уже настроены** на криптостойкие алгоритмы (`sha256` для HMAC, `aes-128-cbc` для шифрования). Менять дефолты обычно **не нужно**.',
                'code_example' => 'use Yii;

// 1. Пароли
$hash = Yii::$app->security->generatePasswordHash(\'secret123\');
// $2y$13$... — bcrypt cost 13
Yii::$app->security->validatePassword(\'secret123\', $hash);   // true

// 2. Случайные строки
$token = Yii::$app->security->generateRandomString(64);   // 64 символа [a-zA-Z0-9_-]
$key   = Yii::$app->security->generateRandomKey(32);      // 32 байта бинарных

// 3. Шифрование
$cipher = Yii::$app->security->encryptByPassword(\'secret data\', \'my-password\');
$plain  = Yii::$app->security->decryptByPassword($cipher, \'my-password\');

// 4. Подпись (HMAC)
$signed = Yii::$app->security->hashData(\'user=42\', \'app-key\');
$data   = Yii::$app->security->validateData($signed, \'app-key\');   // \'user=42\' или false

// 5. Защита от timing attack
Yii::$app->security->compareString($tokenFromUser, $tokenFromDb);',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.security',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как правильно хешировать и проверять пароли в Yii2?',
                'answer' => 'Пароли в Yii2 хешируются через **`generatePasswordHash()`** и проверяются через **`validatePassword()`** — обёртки над **bcrypt** (`password_hash` PHP).

Правила:

- **Никогда** не хранить пароли в открытом виде или через `md5`/`sha1` — они быстрые и подбираются на GPU.
- Хеш — **строка длиной 60 символов** (`$2y$...`), хранится в колонке `password_hash VARCHAR(255)`.
- **`cost`** (по умолчанию **`13`**) — фактор замедления; каждое +1 удваивает время.
- bcrypt сам **генерирует salt** и встраивает в хеш — отдельная колонка `salt` не нужна.
- `validatePassword()` использует **`password_verify`**, который **константно-временной** (защита от timing).

При смене стандартов (новый рекомендованный `cost`) — после успешного `validatePassword` пересохрани хеш с актуальными параметрами.',
                'code_example' => 'use Yii;

class User extends \\yii\\db\\ActiveRecord
{
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }
}

// Регистрация
$user = new User([\'email\' => \'a@b.com\']);
$user->setPassword(\'secret123\');
$user->generateAuthKey();
$user->save();

// Логин
$user = User::findOne([\'email\' => \'a@b.com\']);
if ($user && $user->validatePassword($_POST[\'password\'])) {
    Yii::$app->user->login($user);
} else {
    // НЕ выдавай «email не найден» — это утечка (user enumeration)
    Yii::$app->session->setFlash(\'error\', \'Неверный email или пароль\');
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.security',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как использовать encryptByPassword/decryptByPassword и generateRandomString в Yii2?',
                'answer' => '**`encryptByPassword`** — симметричное шифрование (AES) с **деривацией ключа** из пароля через **PBKDF2**.

Сценарии:

- Шифрование данных пользователя **его собственным паролем** (только сам пользователь может расшифровать).
- Шифрование с **общим секретом** — лучше `encryptByKey($data, $key)` (без PBKDF2, быстрее).

**`generateRandomString($length = 32)`** — криптостойкая случайная строка из алфавита **`[A-Za-z0-9_-]`** (urlSafe-base64).

| Метод | Что возвращает | Когда применять |
| --- | --- | --- |
| **`generateRandomString($n)`** | строка из 64 символов алфавита | API-токены, ссылки сброса пароля, `auth_key` |
| **`generateRandomKey($n)`** | бинарные **байты** | ключ шифрования, секрет для `hashData` |
| **`encryptByPassword`** | бинарные данные | шифрование с паролем (медленно) |
| **`encryptByKey`** | бинарные данные | шифрование с ключом (быстро) |

Не используй **`rand()`** / **`mt_rand()`** — они **не криптостойкие**.',
                'code_example' => 'use Yii;

// 1. Random для токенов / ссылок сброса
$user->password_reset_token = Yii::$app->security->generateRandomString();
$user->access_token         = Yii::$app->security->generateRandomString(64);

// 2. Шифрование с паролем (медленно, для секретов пользователя)
$secret  = \'мой ИНН: 1234567890\';
$cipher  = Yii::$app->security->encryptByPassword($secret, $userPassword);
// Сохраняем $cipher в БД

// Расшифровка
try {
    $plain = Yii::$app->security->decryptByPassword($cipher, $userPassword);
} catch (\\yii\\base\\InvalidArgumentException $e) {
    // неверный пароль или повреждённые данные
}

// 3. Шифрование с ключом (быстро, для общих секретов)
$key    = Yii::$app->security->generateRandomKey(32);   // хранить в .env / Vault
$cipher = Yii::$app->security->encryptByKey($data, $key);
$plain  = Yii::$app->security->decryptByKey($cipher, $key);',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.security',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое hashData/validateData в Yii2 (HMAC-подпись)?',
                'answer' => '**`hashData($data, $key, $rawHash = false)`** — **префиксует** строку HMAC-подписью; **`validateData($signed, $key)`** — проверяет и возвращает оригинал или `false`.

Что внутри:

- Генерируется **HMAC-SHA256** от `data` с ключом `key`.
- Возвращаемая строка = `[hmac][data]`.
- При валидации — отделяет hmac, **сравнивает константно-временно** (защита от timing).

Зачем нужно:

- **Подписанные cookie** (Yii это делает сам через `cookieValidationKey`).
- **Подписанные URL** (email-токены, ссылки одноразового доступа).
- Передача состояния клиенту, где **нельзя доверять** ему как источнику (forms, JWT-like).

Важно: подпись **не шифрует** — клиент видит данные, но **не может их изменить** без знания ключа. Для секретности нужно дополнительное **`encryptByKey`**.',
                'code_example' => 'use Yii;

$key = Yii::$app->security->generateRandomKey(32);   // хранить в .env

// 1. Подписать
$signed = Yii::$app->security->hashData(\'user_id=42&action=reset\', $key);
// Например: a4f1b8c...|user_id=42&action=reset

// 2. Отправить пользователю по email
$resetUrl = \'https://app.com/reset?token=\' . urlencode($signed);

// 3. На приёме — валидируем
$incoming = $_GET[\'token\'];
$data = Yii::$app->security->validateData($incoming, $key);

if ($data === false) {
    throw new \\yii\\web\\BadRequestHttpException(\'Подпись недействительна\');
}

parse_str($data, $params);
// $params[\'user_id\'] === \'42\', $params[\'action\'] === \'reset\'

// validateData использует hash_equals — защита от timing attack
// Подделать $signed без $key вычислительно невозможно (HMAC-SHA256)',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.security',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как работает CSRF-защита в Yii2 и как её отключить для action?',
                'answer' => '**CSRF-защита** в Yii2 включена **по умолчанию** для web-приложения и работает по схеме **Double Submit Cookie**.

Что делает Yii:

- При первом GET генерирует **CSRF-токен**, кладёт в cookie **`_csrf`** и в **meta-тег** на странице.
- При POST/PUT/DELETE сравнивает **токен из формы** (`_csrf`) с **токеном из cookie**.
- Если не совпадают — **`400 Bad Request`**.
- **`Html::beginForm()`** и `ActiveForm` **сами вставляют** hidden-поле `_csrf`.

Настройки:

- **`enableCsrfValidation`** — `true`/`false` глобально на `request` или per-controller.
- **`csrfParam`** — имя POST-параметра (по умолчанию `_csrf`).

Когда отключить:

- **REST API** (stateless) — токена нет, нужен `Bearer`-токен вместо.
- **Webhook**-endpoints (Stripe, PayPal) — подпись от провайдера, не CSRF.

Способы отключить per-action — переопределить **`beforeAction()`** или **`enableCsrfValidation = false`** на контроллере.',
                'code_example' => 'class WebhookController extends \\yii\\web\\Controller
{
    // 1. Отключить для всего контроллера
    public $enableCsrfValidation = false;

    // 2. ИЛИ отключить точечно перед action
    public function beforeAction($action)
    {
        if ($action->id === \'stripe\') {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    public function actionStripe()
    {
        // POST от Stripe — без CSRF, но проверяем подпись Stripe
        $signature = Yii::$app->request->headers->get(\'Stripe-Signature\');
        // ... валидация
    }
}

// В формах CSRF добавляется автоматически:
// <?= Html::beginForm() ?>  → вставит <input type="hidden" name="_csrf" value="...">
// <?= ActiveForm::begin() ?> → то же самое

// Получить токен для AJAX:
// <meta name="csrf-token" content="<?= Yii::$app->request->csrfToken ?>">
// fetch(url, { headers: { \'X-CSRF-Token\': csrf } })',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.security',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как Yii2 защищает от mass assignment через safe атрибуты?',
                'answer' => '**Mass assignment** — это присваивание **сразу всех** атрибутов модели из массива (`$model->load($_POST)`). Уязвимость возникает, когда юзер шлёт в POST поля, которые **не должен** менять (`is_admin`, `balance`, `created_by`).

В Yii2 защита — через **`rules()`** и понятие **safe-атрибута**:

- Атрибут считается **safe**, если он указан в правиле с **любым валидатором** (`required`, `string`, `integer`, ...) — на сценарии, активном в данный момент.
- Если в правилах **нет валидатора** для поля, можно явно отметить **`\'safe\'`** — поле проходит, но **не валидируется**.
- При **`load($data)`** Yii **отбрасывает** все ключи, которые не safe в текущем сценарии.

Дополнительные инструменты:

- **`scenarios()`** — разные наборы safe-атрибутов для разных контекстов (`create` vs `update` vs `admin`).
- **`safe`-валидатор** — для полей без других правил.

**Правило для junior**: если поле есть в форме — оно должно быть в `rules()` с валидатором. Никогда не используй `$model->attributes = $_POST` напрямую.',
                'code_example' => 'class User extends \\yii\\db\\ActiveRecord
{
    public function rules()
    {
        return [
            [[\'email\', \'username\'], \'required\'],
            [\'email\', \'email\'],
            [\'password\', \'string\', \'min\' => 8],

            // is_admin отсутствует в rules — load() его НЕ установит
            // даже если придёт в POST: { username, email, password, is_admin: true }
        ];
    }

    public function scenarios()
    {
        return [
            \'register\' => [\'email\', \'username\', \'password\'],
            \'admin\'    => [\'email\', \'username\', \'password\', \'is_admin\'],   // is_admin safe только для admin
        ];
    }
}

// В контроллере
$user = new User([\'scenario\' => \'register\']);
$user->load(Yii::$app->request->post());   // is_admin отброшен
if ($user->validate()) {
    $user->setPassword($user->password);
    $user->save(false);
}

// ОПАСНО — не делай так:
// $user->attributes = $_POST;   // обходит сценарии? Нет — Yii2 всё равно фильтрует через safeAttributes()
// Но: $user->is_admin = $_POST[\'is_admin\'];   // прямое присваивание ОБХОДИТ защиту',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.security',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как Yii2 защищает от SQL-injection через параметризованные запросы?',
                'answer' => 'Yii2 защищает от **SQL-injection** через **параметризованные (prepared) запросы** на уровне PDO — все пользовательские данные передаются **отдельно** от текста SQL.

Безопасные способы:

- **ActiveRecord** — `User::find()->where([\'email\' => $email])` — `$email` уходит как **параметр**.
- **QueryBuilder** — `(new Query())->where([\'and\', [\'>\', \'age\', 18], [\'name\' => $name]])`.
- **Hash-формат where** — `[\'col\' => $val]` (массив автоматически биндит).
- **`Yii::$app->db->createCommand($sql, $params)`** — явный bind для raw SQL.

**Опасные** способы (нужно избегать):

- **Строковая конкатенация в where**: `where("name = \'$name\'")` — **уязвимо**.
- **`Yii::$app->db->createCommand("...$userInput...")`** без `$params` — уязвимо.
- В **`orderBy`** имя колонки **не биндится** — фильтруй через белый список.
- В **`LIKE`**: всегда экранируй `%` и `_` — иначе пользователь подсунет `%` и сделает full-scan.',
                'code_example' => 'use yii\\db\\Query;
use Yii;

// 1. ХОРОШО — ActiveRecord (биндинг автоматический)
$user = User::find()->where([\'email\' => $_GET[\'email\']])->one();

// 2. ХОРОШО — QueryBuilder с массивом
$posts = (new Query())
    ->from(\'post\')
    ->where([\'and\',
        [\'status\' => \'published\'],
        [\'>\', \'created_at\', $_GET[\'since\']],
    ])
    ->all();

// 3. ХОРОШО — явный bind в raw SQL
$result = Yii::$app->db
    ->createCommand(\'SELECT * FROM user WHERE email = :email\', [\':email\' => $_GET[\'email\']])
    ->queryAll();

// 4. ПЛОХО — конкатенация (SQL-injection)
// $user = User::find()->where("email = \'" . $_GET[\'email\'] . "\'")->one();
// если email = "x\' OR 1=1 --" → вернутся все пользователи

// 5. Спецслучай — orderBy не биндится, нужен whitelist
$allowed = [\'name\', \'created_at\', \'views\'];
$sort = in_array($_GET[\'sort\'], $allowed, true) ? $_GET[\'sort\'] : \'name\';
User::find()->orderBy($sort)->all();

// 6. LIKE — экранируем %, _
$q = addcslashes($_GET[\'q\'], \'%_\');
User::find()->where([\'like\', \'name\', $q])->all();',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.security',
            ],
        ];
    }
}
