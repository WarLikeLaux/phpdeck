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
                'answer' => 'Time-based One-Time Password (RFC 6238) — 6-значный код, который и сервер, и приложение (Google Authenticator, Authy, 1Password) считают по формуле TOTP = HOTP(secret, floor(time/30)). Секрет один раз шарится через QR-код, дальше код меняется каждые 30 секунд оффлайн. Лучше SMS, потому что: 1) Не зависит от сотовой сети и роуминга. 2) Защищён от SIM swap (SMS перехватывают через подкуп оператора). 3) Работает в самолёте. 4) Бесплатно. На сервере проверяй текущий шаг и ±1 шаг — компенсация дрейфа часов.',
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
                'answer' => 'Случайные данные, которые подмешиваются к паролю ПЕРЕД хешированием. Без соли md5("password123") одинаков у всех — атакующий заранее считает rainbow-таблицу и моментально пробивает миллионы хешей. С солью у каждого пользователя свой уникальный хеш, общая таблица бесполезна — нужно брутфорсить каждого отдельно. bcrypt/argon2 генерируют соль сами и кладут её внутрь итоговой строки рядом с алгоритмом и cost — поэтому в БД достаточно одной колонки password.',
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
                'question' => 'Что такое сессия простыми словами с точки зрения безопасности?',
                'answer' => 'Способ сохранить «кто вошёл» между HTTP-запросами без повторной отправки логина/пароля. Сервер генерирует длинный случайный session_id, кладёт его в куку, а сами данные хранит у себя (file/redis/db). С каждым запросом кука приходит — по id находим сессию. Кража куки = полный доступ к аккаунту, поэтому обязательны три флага: HttpOnly (JS не прочитает — защита от XSS), Secure (только по HTTPS — защита от перехвата), SameSite=Lax/Strict (защита от CSRF). Дополнительно: регенерация id после логина (session fixation) и инвалидация на сервере при logout.',
                'code_example' => "<?php
// Установка флагов через session.cookie_* в php.ini
// или ini_set до session_start():
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure',   '1');
ini_set('session.cookie_samesite', 'Lax');
session_start();

// Регенерация ID после логина — защита от session fixation
if (login_succeeded(\$user)) {
    session_regenerate_id(true);
    \$_SESSION['user_id'] = \$user->id;
}",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'security.auth',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое OAuth2 и Authorization Code flow простыми словами?',
                'answer' => 'OAuth2 — протокол делегирования доступа: пользователь разрешает приложению (client) часть своих данных у провайдера (Google/GitHub), не отдавая ему пароль. Самый частый сценарий — Authorization Code flow: 1) Клиент шлёт юзера на /authorize провайдера. 2) Юзер логинится у провайдера и подтверждает scope-ы. 3) Провайдер редиректит обратно с одноразовым code. 4) Бэкенд клиента меняет code на access_token (и опционально refresh_token) через серверный запрос на /token с client_secret. Для SPA/мобильных приложений добавляют PKCE — client_secret нельзя хранить на клиенте, поэтому используется одноразовый code_verifier.',
                'code_example' => "# 1. Редирект пользователя к провайдеру
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
  &grant_type=authorization_code",
                'code_language' => 'http',
                'difficulty' => 2,
                'topic' => 'security.auth',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое brute force и как от него защищаться простыми словами?',
                'answer' => 'Атака перебором: бот шлёт тысячи пар логин/пароль из утечек и словарей, рассчитывая на слабый пароль или повтор. Подвид — credential stuffing (перебор готовых пар из чужих утечек). Защита идёт слоями: 1) Rate limit на /login по связке IP + логин (например, 5 попыток в минуту). 2) Captcha/задержка после нескольких неудач. 3) Временный lockout аккаунта. 4) 2FA — главный замок: даже зная пароль, бот не пройдёт второй фактор. 5) Проверка пароля по haveibeenpwned. В Laravel — RateLimiter::for и трейт ThrottlesLogins из коробки.',
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
                'answer' => 'Поток: 1) Пользователь вводит email. 2) Сервер ВСЕГДА отвечает одинаково «если email зарегистрирован — письмо ушло» (иначе утечка существования аккаунта). 3) Если есть — генерирует криптостойкий одноразовый токен через random_bytes, в БД кладёт его ХЕШ + user_id + expires_at (30-60 минут). 4) В письме — ссылка /reset?token=<plaintext>. 5) По переходу сверяет хеш токена через hash_equals, даёт сменить пароль и сразу удаляет токен. Дополнительно: rate limit на форму, инвалидация всех активных сессий после смены, уведомление на email. НИКОГДА не присылать старый пароль — значит ты его не хешируешь.',
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
                'answer' => 'Стирание куки лишь у клиента не убивает сессию: запись в Redis/БД остаётся живой, и если атакующий уже скопировал session_id (через XSS/MITM/чужой ноутбук), он спокойно продолжит работать от твоего имени. Правильный logout: на сервере удалить/инвалидировать запись в хранилище (или пометить revoked), затем регенерировать CSRF-токен и очистить куку. В Laravel — три вызова в связке: Auth::logout() (забыть пользователя), session()->invalidate() (стереть данные сессии), session()->regenerateToken() (новый CSRF). Для JWT же реального logout без чёрного списка/version-bump не бывает — токен валиден до exp.',
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
