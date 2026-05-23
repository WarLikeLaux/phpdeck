<?php

namespace Database\Seeders\Data\Categories\Security;

class Auth
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Безопасность',
                'question' => 'В чём разница между аутентификацией и авторизацией простыми словами?',
                'answer' => '**Аутентификация** (`AuthN`) — «**кто ты?**», проверка личности. **Авторизация** (`AuthZ`) — «**что тебе можно?**», проверка прав на конкретное действие.

- **AuthN**: логин+пароль, токен, отпечаток пальца, SSO через Google.
- **AuthZ**: может ли *этот* юзер редактировать *этот* пост, удалить *этот* заказ.

Порядок всегда один: **сначала** аутентификация (узнали юзера), **потом** авторизация (проверили его права). В Laravel за первое отвечает `guard` (`Auth::check`), за второе — `Policy`/`Gate` (`$this->authorize`).',
                'code_example' => "<?php
// 1. Аутентификация — кто ты?
if (!Auth::check()) {
    abort(401); // не залогинен
}

// 2. Авторизация — что тебе можно?
\$post = Post::findOrFail(\$id);
if (Auth::user()->cannot('update', \$post)) {
    abort(403); // залогинен, но это не твой пост
}
\$post->update(\$request->validated());

// В роуте те же два шага через middleware:
// Route::put('/posts/{post}', ...)->middleware(['auth', 'can:update,post']);",
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'security.auth',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое 2FA/MFA простыми словами?',
                'answer' => '**Two/Multi-Factor Authentication** — вход не только по паролю, а по **двум независимым факторам**. Три категории:

1. **Что ты знаешь** — пароль, PIN.
2. **Что у тебя есть** — телефон с кодом из Google Authenticator/SMS, аппаратный ключ `YubiKey`.
3. **Кто ты** — отпечаток, Face ID.

**Зачем**: если пароль утёк через фишинг или утечку базы, атакующий всё равно не войдёт без второго фактора. Самый распространённый второй фактор — **TOTP** (6-значный код, меняется каждые 30 секунд в приложении). **SMS** считается слабым (`SIM swap`), **аппаратные ключи** — самые надёжные.',
                'code_example' => "// Поток входа с 2FA:
// 1. POST /login {email, password}        → 200, но сессия в состоянии \"ждём 2FA\"
// 2. POST /2fa/verify {code: 123456}      → сверяем TOTP против секрета юзера
// 3. Только теперь сессия полная, можно ходить по защищённым роутам

// Laravel + pragmarx/google2fa-laravel:
\$google2fa = app('pragmarx.google2fa');
if (!\$google2fa->verifyKey(\$user->two_factor_secret, \$request->code)) {
    return back()->withErrors('Неверный код');
}
session(['2fa_passed' => true]);",
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'security.auth',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое TOTP и почему он лучше SMS?',
                'answer' => '**Time-based One-Time Password** (`RFC 6238`) — 6-значный код, который и сервер, и приложение (`Google Authenticator`, `Authy`, `1Password`) считают по одной формуле:

`TOTP = HOTP(secret, floor(time / 30))`

Секрет один раз шарится через **QR-код**, дальше код меняется каждые 30 секунд **оффлайн**.

**Чем лучше SMS**:

- не зависит от сотовой сети и роуминга;
- защищён от **SIM swap** (SMS перехватывают через подкуп оператора);
- работает в самолёте;
- бесплатно.

На сервере проверяй текущий шаг и **±1 шаг** — компенсация дрейфа часов между устройством и сервером.',
                'difficulty' => 2,
                'topic' => 'security.auth',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Как НЕ хранить пароли в БД и как правильно простыми словами?',
                'answer' => '**НЕЛЬЗЯ**:

- открытым текстом;
- через `md5`/`sha1`/`sha256`/`base64` — они **быстрые**, GPU-ферма перебирает миллиарды вариантов в секунду и пробивает «password123» за минуты.

Если БД сольётся, все такие пароли считаются скомпрометированными.

**ПРАВИЛЬНО** — `password_hash($pass, PASSWORD_DEFAULT)`:

- внутри `bcrypt` (или `argon2id`), специально **медленный**;
- сам кладёт случайную **соль** внутрь итоговой строки;
- проверка через `password_verify($input, $hashFromDb)` — соль и cost вытащит сам.

В Laravel это `Hash::make()` и `Hash::check()`. Колонка в БД — `VARCHAR(255)`. Никогда не пиши своё хеширование и не «улучшай» его вручную.',
                'code_example' => "<?php
// ПЛОХО — быстрый хеш, пробьётся брутфорсом
\$bad = md5(\$password);
\$alsoBad = sha1(\$password.'my-static-salt');

// ХОРОШО — bcrypt с автогенерируемой солью внутри
\$hash = password_hash(\$password, PASSWORD_DEFAULT);
// получится строка вида: \$2y\$12\$N9qo8uLOickgx2ZMRZoMye...
// в БД хранится одна колонка password VARCHAR(255)

// Проверка при логине
if (password_verify(\$inputPassword, \$user->password)) {
    // пароль правильный — пускаем
}

// В Laravel это делает Hash::make() / Hash::check(), бери их:
\$user->password = Hash::make(\$request->password);
if (Hash::check(\$request->password, \$user->password)) { /* ok */ }",
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'security.auth',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое соль (salt) в хешировании паролей простыми словами?',
                'answer' => '**Соль** — случайные данные, которые подмешиваются к паролю **перед** хешированием.

**Зачем**:

- без соли `md5("password123")` одинаков у всех — атакующий заранее считает **rainbow-таблицу** и моментально пробивает миллионы хешей одним лукапом;
- с солью у каждого юзера свой **уникальный** хеш — общая таблица бесполезна, придётся брутить каждого отдельно.

**Главное**: `bcrypt`/`argon2id` генерируют соль **сами** и кладут её внутрь итоговой строки рядом с алгоритмом и `cost`. Поэтому в БД достаточно **одной** колонки `password` — отдельная колонка для соли не нужна.',
                'code_example' => "<?php
// Один и тот же пароль — два разных хеша из-за разной соли
echo password_hash('qwerty', PASSWORD_BCRYPT);
// \$2y\$10\$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy
echo password_hash('qwerty', PASSWORD_BCRYPT);
// \$2y\$10\$8K1p/a0dURXAm7QiTRqB7uYx0Kx3X6.6yWcZpC9mY8.7XHK8m5kSe
//   ^^^ алгоритм  ^^ cost  ^^^^^^^^^^^^^^^^^^^^^^ соль + хеш",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'security.auth',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое OAuth2 и Authorization Code flow простыми словами?',
                'answer' => '**OAuth2** — протокол **делегирования доступа**: пользователь разрешает приложению (`client`) часть своих данных у провайдера (`Google`/`GitHub`), **не отдавая ему пароль**.

Самый частый сценарий — **Authorization Code flow**:

1. Клиент редиректит юзера на `/authorize` провайдера.
2. Юзер логинится у провайдера и подтверждает запрошенные `scope`-ы.
3. Провайдер редиректит обратно с одноразовым `code`.
4. **Бэкенд** клиента меняет `code` на `access_token` (и опционально `refresh_token`) через серверный `POST /token` с `client_secret`.

**PKCE** (для SPA и мобильных): `client_secret` нельзя хранить на клиенте, поэтому вместо него используется одноразовый `code_verifier` + его SHA256-хеш `code_challenge`. Перехват `code` без `code_verifier` бесполезен.',
                'code_example' => '# 1. Редирект пользователя к провайдеру
GET https://accounts.google.com/o/oauth2/v2/auth?
    client_id=APP_ID
    &redirect_uri=https://app.test/callback
    &response_type=code
    &scope=openid%20email
    &state=RANDOM

# 2. После согласия — редирект назад с code
GET https://app.test/callback?code=ONE_TIME_CODE&state=RANDOM

# 3. Бэкенд меняет code на access_token (server-to-server)
POST https://oauth2.googleapis.com/token
  code=ONE_TIME_CODE
  &client_id=APP_ID
  &client_secret=APP_SECRET
  &redirect_uri=https://app.test/callback
  &grant_type=authorization_code',
                'code_language' => 'http',
                'difficulty' => 2,
                'topic' => 'security.auth',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое OIDC и чем он отличается от OAuth2 простыми словами?',
                'answer' => '**OIDC (OpenID Connect)** — тонкий **слой аутентификации поверх OAuth2**. OAuth2 говорит «**этому приложению можно**», OIDC ещё и подтверждает «**вот кто это такой**».

**Главное различие:**

- **OAuth2** — про **авторизацию доступа** (делегирование). Сервер вернёт `access_token` — «этот клиент имеет право читать твой gmail». Кто пользователь и зашёл ли он на самом деле — OAuth2 формально не отвечает.
- **OIDC** — про **аутентификацию** (вход). Помимо `access_token` сервер возвращает **`id_token`** — подписанный JWT с инфо о пользователе (`sub`, `email`, `name`, `iat`, `exp`).

**Как включить OIDC:**

- Добавить `scope=openid` в `/authorize`. Без `openid` это просто OAuth2.
- Опционально — `profile`, `email`, `address`, `phone` для дополнительных claim-ов.

**Что даёт практически:**

- Кнопка «Войти через Google/Apple/Microsoft» — это OIDC, не голый OAuth2.
- Сервер сам проверяет подпись `id_token` — не нужно лишний запрос на `/userinfo`.
- Стандарт **discovery**: `https://provider/.well-known/openid-configuration` отдаёт URLs и публичные ключи для проверки подписи.',
                'code_example' => '# OAuth2 (только авторизация ресурса)
GET /authorize?client_id=APP&scope=email&response_type=code&...

# OIDC (вход = openid scope обязателен)
GET /authorize?client_id=APP&scope=openid%20email&response_type=code&...

# В ответ /token бэкенд получает оба токена:
{
  "access_token": "ya29...",       # для запросов к API провайдера
  "id_token":     "eyJhbGciOi...",  # JWT с данными о юзере — для нас
  "expires_in":   3600
}

# id_token (header.payload.signature) — пример payload:
{
  "iss": "https://accounts.google.com",
  "sub": "1234567890",       # стабильный ID пользователя у провайдера
  "email": "user@example.com",
  "email_verified": true,
  "name": "Иван",
  "iat": 1700000000,
  "exp": 1700003600,
  "aud": "APP_ID"            # должен совпасть с нашим client_id
}',
                'code_language' => 'http',
                'difficulty' => 2,
                'topic' => 'security.auth',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое id_token в OIDC и чем он отличается от access_token?',
                'answer' => 'В OIDC сервер возвращает **два разных токена**, и важно их не путать.

| Признак | `access_token` | `id_token` |
| --- | --- | --- |
| **Зачем** | доступ к API провайдера от имени юзера | подтверждение «вот кто этот юзер» |
| **Формат** | непрозрачная строка или JWT (как решит провайдер) | **всегда JWT** (по спецификации OIDC) |
| **Кому адресован (`aud`)** | API-ресурсу | **нашему клиенту** (`client_id`) |
| **Кто читает payload** | сервер ресурса | **наш бэкенд** — извлекает `sub`, `email` для логина |
| **Проверка** | передаём в `Authorization: Bearer` к API | **локально**: подпись по публичному ключу провайдера + `iss`, `aud`, `exp` |

**Шаги проверки `id_token`:**

1. Скачать публичные ключи провайдера с **`/.well-known/openid-configuration → jwks_uri`** (кэшировать).
2. Проверить подпись по `kid` из заголовка JWT.
3. Сверить `iss` (issuer) — это точно наш провайдер?
4. Сверить `aud` — наш `client_id`?
5. Сверить `exp` — не истёк?
6. Опционально — `nonce`, который мы отправляли в `/authorize`.

**Что хранить у себя:** обычно `sub` (стабильный ID юзера у провайдера) + email. По `sub` находим/создаём локального пользователя.',
                'code_example' => '<?php
// Псевдокод проверки id_token (на проде — firebase/php-jwt или web-token/jwt-framework)

$jwt = $tokenResponse[\'id_token\'];
[$h64, $p64, $s64] = explode(\'.\', $jwt);

$header = json_decode(base64_decode(strtr($h64, \'-_\', \'+/\')), true);
$payload = json_decode(base64_decode(strtr($p64, \'-_\', \'+/\')), true);

// 1. Достать публичный ключ по kid из jwks
$jwks = json_decode(file_get_contents(\'https://provider/.well-known/jwks.json\'), true);
$key  = findKeyByKid($jwks, $header[\'kid\']);

// 2. Проверить подпись
if (!verifySignature("$h64.$p64", $s64, $key, $header[\'alg\'])) {
    throw new \\RuntimeException(\'bad signature\');
}

// 3. Бизнес-проверки
if ($payload[\'iss\']   !== \'https://accounts.google.com\') abort(401);
if ($payload[\'aud\']   !== getenv(\'GOOGLE_CLIENT_ID\'))    abort(401);
if ($payload[\'exp\']   <  time())                          abort(401);

// 4. Логиним по sub
$user = User::firstOrCreate(
    [\'oidc_sub\' => $payload[\'sub\']],
    [\'email\' => $payload[\'email\']]
);
Auth::login($user);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'security.auth',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое brute force и как от него защищаться простыми словами?',
                'answer' => '**Brute force** — атака перебором: бот шлёт тысячи пар логин/пароль из словарей и утечек, рассчитывая на слабый или повторно использованный пароль.

**Подвид — `credential stuffing`**: перебор готовых пар из чужих утечек (большинство юзеров используют один пароль на нескольких сайтах).

**Защита слоями**:

1. **Rate limit** на `/login` по связке `IP + login` (например, 5 попыток в минуту).
2. **Captcha** или нарастающая задержка после нескольких неудач.
3. Временный **lockout** аккаунта.
4. **`2FA`** — главный замок: даже зная пароль, бот не пройдёт второй фактор.
5. Проверка пароля по базе `haveibeenpwned` при регистрации/смене.

В Laravel — `RateLimiter::for` (`AppServiceProvider::boot`) и middleware `throttle` из коробки.',
                'code_example' => "<?php
// routes/web.php — лимит на /login
Route::middleware('throttle:login')
    ->post('/login', [LoginController::class, 'store']);

// AppServiceProvider::boot()
RateLimiter::for('login', function (Request \$request) {
    \$key = \$request->input('email').'|'.\$request->ip();
    return Limit::perMinute(5)->by(\$key);
});",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'security.auth',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Как должен быть устроен безопасный сброс пароля простыми словами?',
                'answer' => '**Поток**:

1. Пользователь вводит email.
2. Сервер **ВСЕГДА** отвечает одинаково: «если email зарегистрирован — письмо ушло». Разные ответы = утечка существования аккаунта.
3. Если юзер найден — генерируем криптостойкий одноразовый токен через `random_bytes(32)`. В БД кладём **хеш** токена + `user_id` + `expires_at` (30-60 минут).
4. В письме — ссылка вида `/reset?token=<plaintext>`.
5. По переходу сверяем хеш токена через `hash_equals` (constant-time), даём сменить пароль, **сразу удаляем** запись.

**Дополнительно**:

- `rate limit` на форму запроса (по IP и email);
- инвалидация **всех активных сессий** после смены;
- уведомление пользователю на email о смене.

**НИКОГДА** не присылать старый пароль письмом — значит ты его не хешируешь, а хранишь обратимым шифрованием или в открытую.',
                'code_example' => "<?php
// Генерация и сохранение
\$token = bin2hex(random_bytes(32));
DB::table('password_resets')->insert([
    'email'      => \$user->email,
    'token_hash' => hash('sha256', \$token),
    'expires_at' => now()->addMinutes(60),
]);
Mail::to(\$user)->send(new ResetMail(\"/reset?token={\$token}\"));

// Проверка
\$row = DB::table('password_resets')
    ->where('email', \$email)
    ->where('expires_at', '>', now())
    ->first();
if (\$row && hash_equals(\$row->token_hash, hash('sha256', \$token))) {
    // OK — менять пароль, удалить запись
}",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'security.auth',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Зачем нужен logout на сервере, если можно просто стереть куку у клиента?',
                'answer' => 'Стирание куки **только у клиента** не убивает сессию: запись в `Redis`/БД остаётся живой. Если атакующий уже скопировал `session_id` (через `XSS`/`MITM`/чужой ноутбук), он спокойно продолжит работать от твоего имени.

**Правильный logout на сервере**:

1. Удалить/инвалидировать запись сессии в хранилище (или пометить `revoked`).
2. Регенерировать `CSRF`-токен.
3. Очистить куку у клиента.

В Laravel — **три вызова** в связке:

- `Auth::logout()` — забыть пользователя в guard;
- `$request->session()->invalidate()` — стереть данные сессии;
- `$request->session()->regenerateToken()` — новый CSRF.

Для **JWT** реального logout без чёрного списка / `token_version` бамп **не бывает** — токен валиден до `exp` по своей подписи, сервер ничего о нём не знает.',
                'code_example' => "<?php
public function logout(Request \$request)
{
    Auth::logout();                       // 1. забыть юзера в guard
    \$request->session()->invalidate();    // 2. стереть данные сессии
    \$request->session()->regenerateToken(); // 3. новый CSRF
    return redirect('/');
}",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'security.auth',
            ],
        ];
    }
}
