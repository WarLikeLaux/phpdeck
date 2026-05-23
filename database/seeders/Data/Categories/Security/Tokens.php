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
