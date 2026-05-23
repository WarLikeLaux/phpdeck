<?php

namespace Database\Seeders\Data\Categories\Security;

class Tokens
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Безопасность',
                'question' => 'Что такое токен простыми словами?',
                'answer' => 'Длинная случайная (или подписанная) строка, которая идентифицирует клиента или сессию, чтобы **не пересылать логин и пароль на каждом запросе**.

**Поток**:

1. Клиент один раз входит логином+паролем.
2. Сервер выдаёт **токен**.
3. Клиент подкладывает его в каждый запрос в заголовке `Authorization: Bearer ...`.
4. Сервер по токену понимает, кто это, и решает, что разрешить.

**Примеры**:

- `JWT` — самодостаточный, подпись внутри.
- `API-ключ Stripe` (`sk_live_...`).
- `OAuth access_token`.
- `refresh_token` — для получения нового `access`.
- `CSRF-токен` — защита форм.

**Главное правило**: к токену относись как к **паролю** — кто его украл, тот вошёл в аккаунт. Поэтому:

- только `HTTPS`;
- куки — `HttpOnly` + `Secure`;
- **не** клади в URL и не светись в логах.',
                'code_example' => '# 1. Логин — отдаём токен в обмен на пароль
POST /api/login
{ "email": "a@b.test", "password": "secret" }
→ 200 { "token": "1|aBcD3fGh5jKlMnOpQ..." }

# 2. Дальше каждый запрос — с токеном, пароль больше не шлём
GET /api/me
Authorization: Bearer 1|aBcD3fGh5jKlMnOpQ...
→ 200 { "id": 42, "email": "a@b.test" }

# 3. Logout — просим сервер забыть/инвалидировать токен
POST /api/logout
Authorization: Bearer 1|aBcD3fGh5jKlMnOpQ...
→ 204',
                'code_language' => 'http',
                'difficulty' => 1,
                'topic' => 'security.tokens',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое JWT и из чего он состоит?',
                'answer' => '**`JSON Web Token`** (`RFC 7519`) — самодостаточный токен из **трёх** `base64url`-частей, разделённых точками:

`header.payload.signature`

**`header`** — алгоритм и тип:

- `alg` — `HS256` (HMAC), `RS256` (RSA) и т.п.;
- `typ` — `JWT`.

**`payload`** — JSON с **claims**:

- стандартные: `iss` (issuer), `sub` (subject/user id), `exp` (expires at), `iat` (issued at), `aud` (audience), `jti` (token id);
- кастомные: `role`, `email`, что нужно приложению.

**`signature`** — `HMAC` или `RSA`-подпись по первым двум частям с секретом/приватным ключом. Сервер при получении проверяет подпись и `exp` — и **доверяет** payload.

**ВАЖНО**: payload только **закодирован** `base64url`, **НЕ зашифрован** — содержимое читает любой, у кого есть токен. Не клади туда секреты.',
                'code_example' => '// Пример JWT — три base64url-части через точку
eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiI0MiIsInJvbGUiOiJ1c2VyIiwiaWF0IjoxNzE2MDAwMDAwLCJleHAiOjE3MTYwMDM2MDB9.SflKxw...

// Декодируется так:
header  = {"alg":"HS256","typ":"JWT"}
payload = {"sub":"42","role":"user","iat":1716000000,"exp":1716003600}
sig     = HMAC-SHA256(base64url(header) + "." + base64url(payload), secret)',
                'code_language' => 'http',
                'difficulty' => 2,
                'topic' => 'security.tokens',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Чем JWT отличается от сессии простыми словами?',
                'answer' => 'Разная **модель хранения** состояния.

**Сессия** (stateful):

- сервер **хранит** данные (`user_id`, корзину, права) в `Redis`/БД;
- клиенту отдаёт только короткий `session_id` (32 байта) в куке;
- на каждом запросе сервер идёт в стор и достаёт сессию.

**JWT** (stateless):

- данные **внутри токена** в виде подписанного JSON;
- сервер **ничего не хранит** — на каждом запросе проверяет только подпись и `exp`.

**Плюсы JWT**:

- легко **масштабировать** — нет общего стора между нодами;
- удобно для **микросервисов** и мобильных приложений.

**Минусы JWT**:

- нельзя **мгновенно отозвать** (валиден до `exp` без чёрного списка);
- размер больше — **сотни байт** против 32-байтного `session_id`;
- любое изменение данных юзера требует **переиздания** токена.

**Правило**: монолит + браузер — обычно сессия проще; распределённый API + мобилка — `JWT`.',
                'difficulty' => 2,
                'topic' => 'security.tokens',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое access token и refresh token простыми словами?',
                'answer' => 'Пара токенов с **разной ролью** — компромисс между удобством и безопасностью.

**`access_token`** — короткоживущий (5-60 минут):

- клиент авторизует им **каждый** запрос к API;
- идёт в заголовке `Authorization: Bearer ...`;
- если украдут — протухнет за минуты.

**`refresh_token`** — долгоживущий (дни-месяцы):

- **не показывается** API эндпоинтам;
- нужен только чтобы **получить новый** `access_token`, когда старый истёк;
- лежит в защищённом месте: `HttpOnly`+`Secure`-кука в браузере, `Keychain`/`Keystore` в мобильном;
- редко уходит по сети — только на `/auth/refresh`.

**На сервере** `refresh` обычно хранится с привязкой к `user_id`. Logout = **удалить запись** → юзер выйдет, как только истечёт текущий `access`.',
                'code_example' => '# 1. Запрос на API с просроченным access — 401
GET /api/me
Authorization: Bearer eyJ...expired
→ 401 Unauthorized

# 2. Клиент молча обменивает refresh на новый access
POST /auth/refresh
Cookie: refresh_token=long-random-string
→ 200 { "access_token": "eyJ...new", "expires_in": 900 }

# 3. Повторяем оригинальный запрос с новым access
GET /api/me
Authorization: Bearer eyJ...new
→ 200 { "id": 42, "email": "..." }',
                'code_language' => 'http',
                'difficulty' => 2,
                'topic' => 'security.tokens',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Почему JWT нельзя отозвать сразу и как обходят это ограничение?',
                'answer' => 'Сервер `JWT` **не хранит** — он только проверяет **подпись** и **`exp`**. Поэтому украденный токен работает **до своего `exp`** независимо от того, что юзер «вышел» или сменил пароль. «Logout» на клиенте обычно просто **удаляет токен в браузере**, но украденная копия у атакующего остаётся **валидной**.

**Стандартные паттерны обхода**:

1. **Короткий `exp`** (5-15 минут) **+ `refresh_token`** — узкое окно для атакующего, refresh легко отзывается.
2. **Blacklist по `jti`** в `Redis` с `TTL` = оставшийся срок жизни токена — теряется stateless, но Redis-лукап дёшев.
3. **`token_version`** (или `iat`-cutoff) в БД на пользователя: при logout/смене пароля поднимаем версию; payload должен содержать ту же — иначе **`401`**.
4. **Refresh rotation**: каждый refresh выдаёт **новый** `refresh_token` и инвалидирует старый. Повторное использование старого = **сигнал кражи**, выкидываем всю «семью» токенов.

**Когда что выбирать**:

| Подход | Stateless | Скорость | Гранулярность |
| --- | --- | --- | --- |
| Короткий `exp` + refresh | да | мгновенно | до минут |
| `jti`-blacklist в Redis | **нет** | быстро | мгновенно |
| `token_version` в БД | **нет** | один lookup | мгновенно, по юзеру |
| Refresh rotation | да | мгновенно | при детекте кражи |',
                'code_example' => "<?php
// Подход с token_version — stateless для access, инвалидация одним UPDATE
// users: token_version INT default 1
// access token payload: {sub:42, ver:1, exp:...}

public function verifyJwt(array \$payload): User {
    \$user = User::findOrFail(\$payload['sub']);
    if ((\$payload['ver'] ?? 0) !== \$user->token_version) {
        abort(401, 'token revoked');
    }
    return \$user;
}

// Logout «со всех устройств» / смена пароля
public function logoutEverywhere(User \$user): void {
    \$user->increment('token_version'); // все старые JWT мгновенно мертвы
}",
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'security.tokens',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что НЕЛЬЗЯ класть в JWT простыми словами?',
                'answer' => 'Payload JWT — это **`base64url`**, а **НЕ шифрование**. Любой с токеном открывает `jwt.io` и читает содержимое. Подпись защищает от **подделки**, а не от **чтения**.

**Нельзя** класть:

- пароли (даже хеши);
- `API`-ключи и `client_secret` сторонних сервисов;
- номера карт;
- паспортные / медицинские / финансовые ПДн;
- внутренние секреты приложения.

**Можно** класть:

- `sub` (user id);
- `role`, `permissions`;
- имя пользователя, email (если можно показать);
- `exp`, `iat`, `iss`, `aud`, `jti`.

Если **очень нужно** прятать содержимое — используй `JWE` (`JSON Web Encryption`), а не обычный `JWS`. Но для большинства задач проще не класть лишнего.',
                'code_example' => "// Декодирование payload без секрета — доступно ВСЕМ
\$jwt = 'eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiI0MiIsImNjIjoiNDExMS0xMTExLTExMTEtMTExMSJ9.sig';
\$parts = explode('.', \$jwt);
\$payload = json_decode(base64_decode(strtr(\$parts[1], '-_', '+/')), true);
print_r(\$payload);
// Array ( [sub] => 42 [cc] => 4111-1111-1111-1111 )  ← КАРТА УТЕКЛА",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'security.tokens',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое CSRF-токен простыми словами?',
                'answer' => 'Случайная строка, которую сервер **генерирует на сессию** и подкладывает в каждую форму как скрытое поле.

**Как работает**:

- при `POST`/`PUT`/`DELETE` сервер сравнивает токен из тела/заголовка с тем, что в сессии;
- сравнение через `hash_equals` (constant-time);
- атакующий сайт **может** заставить браузер юзера отправить запрос (куки прицепятся автоматически), но **НЕ может прочитать** токен из-за `Same-Origin Policy`;
- значит чужой `POST` придёт без правильного токена → отклоняется.

**В Laravel**:

- `@csrf` в Blade-форме автоматически вставляет `<input type="hidden" name="_token" value="...">`;
- middleware `VerifyCsrfToken` проверяет токен на всех state-changing запросах;
- для AJAX — токен из `<meta name="csrf-token">` в заголовок `X-CSRF-TOKEN`.

**Дополнительно**: кука `SameSite=Lax`/`Strict` — браузер сам не пошлёт куку с чужого домена на `POST`.',
                'code_example' => "<!-- Blade — токен ставится автоматически -->
<form method=\"POST\" action=\"/post\">
    @csrf  {{-- разворачивается в: --}}
    {{-- <input type=\"hidden\" name=\"_token\" value=\"...\"> --}}
</form>

<!-- Для AJAX/SPA — токен в meta + header -->
<meta name=\"csrf-token\" content=\"{{ csrf_token() }}\">
<script>
fetch('/post', {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
});
</script>",
                'code_language' => 'html',
                'difficulty' => 2,
                'topic' => 'security.tokens',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Где хранить JWT на клиенте: localStorage или cookie?',
                'answer' => 'Два варианта с **разным профилем рисков**.

**`localStorage` / `sessionStorage`**:

- удобно для `SPA`, токен сам цепляется в `Authorization`-заголовок;
- но `JS` читает его **напрямую**;
- любой `XSS` (вредный `npm`-пакет, чужой script на странице) **угоняет токен мгновенно**.

**Cookie с флагами `HttpOnly` + `Secure` + `SameSite=Lax`/`Strict`**:

- `JS` прочитать **не может** — `XSS` токен не вытащит;
- но появляется риск **`CSRF`** (браузер сам шлёт куку с любого сайта);
- лечится `CSRF`-токеном или `SameSite=Strict`.

**Сравнение**:

| | `localStorage` | `HttpOnly` cookie |
| --- | --- | --- |
| Доступ из `JS` | да | **нет** |
| Риск `XSS` | **высокий** | низкий |
| Риск `CSRF` | низкий | средний (надо `SameSite`/токен) |
| Авто-отправка | нет, руками | да, браузер сам |

**Рекомендация 2026 для веба**:

- **`refresh_token`** → `HttpOnly` + `Secure` + `SameSite=Strict` cookie с `Path=/auth/refresh`;
- **`access_token`** → в **памяти** `JS` (переменная/closure, **не** `localStorage`), живёт 5-15 минут;
- при перезагрузке страницы тихо обновляется через `/auth/refresh`.

**Никогда**:

- не клади токены в `URL` — попадут в `access_log`, history браузера и `Referer`-заголовок.

**Мобильные**: `Keychain` (`iOS`) / `EncryptedSharedPreferences` (`Android`), не `AsyncStorage` в открытую.',
                'code_example' => "// Refresh — HttpOnly cookie, JS не достанет
Set-Cookie: refresh=eyJ...; HttpOnly; Secure; SameSite=Strict; Path=/auth/refresh; Max-Age=2592000

// Access — в памяти SPA, не в localStorage
// app.js
let accessToken = null; // closure, нет в DevTools → Application

async function refresh() {
  const r = await fetch('/auth/refresh', { method: 'POST', credentials: 'include' });
  accessToken = (await r.json()).access_token;
}

fetch('/api/me', { headers: { Authorization: 'Bearer ' + accessToken } });",
                'code_language' => 'http',
                'difficulty' => 3,
                'topic' => 'security.tokens',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Почему JWT с alg=none и алгоритм-confusion — опасные атаки?',
                'answer' => 'Два классических способа обойти подпись `JWT`.

**1. `alg=none`**

В `RFC 7519` разрешено значение `alg=none` — «токен **без подписи**». Старые/наивные библиотеки:

1. Сначала читают `alg` из header.
2. По этому полю выбирают способ верификации.
3. Видят `none` → **пропускают проверку**, считают подпись валидной.

Атакующий кладёт нужный `payload` (`role=admin`), ставит `alg=none`, оставляет **пустую** секцию подписи — и стал админом.

**2. Algorithm confusion (`RS256` → `HS256`)**

Сервис **подписывает** `RS256` (приватным `RSA`), верификатор **принимает любой `alg`** из заголовка.

1. Атакующий берёт **публичный** ключ сервера (часто доступен на `/.well-known/jwks.json`).
2. Пересобирает токен с `alg=HS256`.
3. Использует **публичный ключ** как `HMAC`-секрет.
4. Библиотека: «`HS256`? Проверю `HMAC` тем же ключом» — **верит**.

**Защита**:

1. При верификации **передавай белый список алгоритмов** (например, `[RS256]`) — **не доверяй** полю `alg` из header.
2. Используй **разные ключи** для разных операций.
3. Отдельно валидируй `iss`, `aud`, `exp`.
4. Лучше выбрать библиотеку, которая **привязывает ключ к алгоритму** на уровне API (`new Key($pub, \'RS256\')`).',
                'code_example' => "<?php
use Firebase\\JWT\\JWT;
use Firebase\\JWT\\Key;

// ПЛОХО — без явного указания алгоритма, библиотека прочтёт alg из header
// (старые версии firebase/php-jwt такое позволяли)
\$payload = JWT::decode(\$jwt, \$publicKey);

// ХОРОШО — алгоритм задан жёстко, чужой alg отклоняется
\$payload = JWT::decode(\$jwt, new Key(\$publicKey, 'RS256'));

// Дополнительно — обязательная проверка iss/aud
if (\$payload->iss !== 'https://my-issuer') abort(401);
if (\$payload->aud !== 'my-api')             abort(401);",
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'security.tokens',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Как правильно сравнивать токены и подписи в PHP?',
                'answer' => 'Через **`hash_equals($known, $user)`** — constant-time сравнение, защищает от **timing-атак**.

**Почему обычное `==` опасно**:

- выходит на **первом несовпадающем** байте;
- чем длиннее совпавший префикс, тем дольше работает;
- атакующий по разнице времени **побайтово** подбирает токен.

**Где применять `hash_equals`**:

- `CSRF`-токены;
- `HMAC`-подписи webhook (`X-Hub-Signature-256`, `Stripe-Signature`);
- `API`-ключи / capability-tokens;
- любые секретные строки.

**Для паролей** — отдельная функция: `password_verify($input, $hash)`. Она внутри тоже constant-time, отдельно `hash_equals` не нужен.

**Совет**: первым параметром передавай **известное правильное** значение — так гарантирована одинаковая длина при сравнении.',
                'code_example' => "<?php
\$sentToken = \$_POST['token'] ?? '';
\$realToken = \$_SESSION['csrf'];

if (!hash_equals(\$realToken, \$sentToken)) {
    http_response_code(403);
    exit('CSRF token mismatch');
}",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'security.tokens',
            ],
        ];
    }
}
