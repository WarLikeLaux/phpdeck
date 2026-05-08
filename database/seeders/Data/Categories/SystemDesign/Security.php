<?php

namespace Database\Seeders\Data\Categories\SystemDesign;

class Security
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example: ?string, code_language: ?string, difficulty: int, topic: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое HTTPS и TLS простыми словами?',
                'answer' => 'HTTPS = HTTP + TLS. TLS (Transport Layer Security) - криптографический протокол, шифрующий передачу данных. Простыми словами: если HTTP - это открытка, которую может прочесть любой почтальон, то HTTPS - запечатанный конверт. Решает три задачи: 1) шифрование (никто не подслушает), 2) аутентификация сервера (через сертификат - это правда тот сайт), 3) целостность (данные не подменены). SSL - устаревший предшественник TLS.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 2,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как работает TLS handshake простыми словами?',
                'answer' => 'TLS handshake (рукопожатие) - обмен между клиентом и сервером перед началом шифрования: 1) клиент: "привет, поддерживаю эти алгоритмы", 2) сервер: "выбираю этот, вот мой сертификат", 3) клиент проверяет сертификат через CA (центр сертификации), 4) обмениваются ключами через асимметричную криптографию (RSA/ECDHE), 5) договариваются о симметричном ключе для скорости, 6) дальше всё шифруется этим ключом. TLS 1.3 сократил handshake до 1 RTT.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 4,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'В чём разница между аутентификацией и авторизацией?',
                'answer' => 'Аутентификация (Authentication, AuthN) - "кто ты?" - проверка личности (логин/пароль, токен, биометрия). Авторизация (Authorization, AuthZ) - "что тебе можно?" - проверка прав на действие (роли, разрешения). Простыми словами: на проходной показал паспорт - аутентифицировали; чтобы войти в серверную - проверили разрешение, авторизовали. Сначала всегда AuthN, потом AuthZ.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 2,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое OAuth 2.0 и какие у него grant types?',
                'answer' => 'OAuth 2.0 - стандарт делегирования доступа. Простыми словами: ты разрешаешь приложению X получить доступ к твоим данным на сервисе Y, не отдавая пароль. 4 основных grant type: 1) Authorization Code - для веб-приложений с бэкендом (самый безопасный), 2) Client Credentials - сервис-сервис, 3) Resource Owner Password Credentials - устаревший, прямой логин/пароль, 4) Implicit - устарел, заменён на Code+PKCE для SPA.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 4,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое JWT и какие плюсы и минусы?',
                'answer' => 'JWT (JSON Web Token, RFC 7519) - подписанный токен с тремя частями: header.payload.signature, разделёнными точкой, base64url-кодированы. Внутри payload - claims (sub, iat, exp, roles). Подписан симметричным секретом (HS256) или асимметричным ключом (RS256/ES256). Плюсы: stateless (сервер не хранит сессию), удобен для микросервисов и SPA, любой сервис с public key может проверить токен. Минусы: 1) нельзя отозвать до exp без серверного blacklist (теряем stateless), поэтому короткий exp 5-15 мин + refresh token, 2) размер больше cookie session_id, 3) payload не зашифрован, только подписан - не клади туда чувствительное (для шифрования есть JWE), 4) исторически были атаки alg=none и confusion (RS256→HS256 с public key как secret) - всегда явно whitelisting alg.',
                'code_example' => 'eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiIxMjMiLCJleHAiOjE3MDB9.signature',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'JWT vs server-side sessions - что выбрать?',
                'answer' => 'Server-side session: сервер хранит сессию (Redis/БД), клиенту даёт session_id в HttpOnly cookie. Плюсы: моментальная отзываемость (удалил из Redis - session мертва), маленький cookie, изменения профиля видны сразу. Минусы: каждый запрос идёт в session store, нужен общий store при горизонтальном масштабировании. JWT: токен с подписью, сервер ничего не помнит. Плюсы: stateless, любой микросервис верифицирует через public key, хорош для distributed/edge. Минусы: отзыв сложен (blacklist убивает stateless), большой размер. Правило: monolith с одной БД сессии - sessions проще; распределённые системы и B2B SSO - JWT (или opaque tokens с introspection как в OAuth2).',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 4,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое access token и refresh token?',
                'answer' => 'Access token - короткоживущий токен (15 мин - 1 час) для доступа к API. Refresh token - долгоживущий (дни/недели) для получения нового access token без логина. Простыми словами: access - пропуск на сегодня, refresh - удостоверение, по которому выдают пропуска. Если access утёк - украли на короткое время. Refresh хранится безопаснее (HttpOnly cookie), может быть отозван. На каждом запросе используется access, при истечении - refresh обновляет его.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 3,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое RBAC и ABAC?',
                'answer' => 'RBAC (Role-Based Access Control) - доступ через роли: admin, editor, viewer. У пользователя роль, у роли набор прав. Простой и понятный. ABAC (Attribute-Based Access Control) - доступ через атрибуты: пользователь+ресурс+действие+контекст. Например: "редактировать может автор, или админ, или модератор отдела автора, в рабочее время". Гибче RBAC, но сложнее. В реальности часто комбинируют: RBAC как база + ABAC-правила сверху.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 4,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое CORS простыми словами?',
                'answer' => 'CORS (Cross-Origin Resource Sharing) - механизм браузера, контролирующий, может ли cross-origin JavaScript ЧИТАТЬ ответ другого origin. По same-origin policy браузер по умолчанию запрещает сайту example.com через JS читать ответ api.other.com; сервер api.other.com может разрешить через заголовок Access-Control-Allow-Origin. Preflight (OPTIONS) идёт перед "сложными" запросами для проверки разрешений. ВАЖНО: CORS - это НЕ защита от XSS и не от CSRF; это механизм управления чтением cross-origin данных в браузере. От XSS защищают экранирование вывода, CSP, HttpOnly/SameSite cookies; от CSRF - CSRF-токены и SameSite cookies. CORS лишь определяет, что разрешит браузер cross-origin JS прочесть из ответа.',
                'code_example' => 'Access-Control-Allow-Origin: https://example.com
Access-Control-Allow-Methods: GET, POST, PUT, DELETE
Access-Control-Allow-Headers: Content-Type, Authorization
Access-Control-Max-Age: 86400',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое CSRF и как защищаться?',
                'answer' => 'CSRF (Cross-Site Request Forgery) - атака, когда вредоносный сайт от имени залогиненного пользователя шлёт запрос на твой сайт через его cookies. Простыми словами: ты залогинен в банке, открыл вредоносный сайт - он скрытно шлёт POST /transfer от твоего имени. Защита: CSRF-токен в форме (Laravel @csrf), который вредоносный сайт не может узнать. SameSite cookies (Strict/Lax) тоже помогают.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 3,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое XSS и как защищаться?',
                'answer' => 'XSS (Cross-Site Scripting) - инъекция вредоносного JS в страницу. Простыми словами: пользователь оставил коммент с <script>alert(stolenCookie)</script>, и этот скрипт выполняется у других посетителей. Защита: 1) экранировать вывод (Blade {{ }} автоматом), 2) Content-Security-Policy header, 3) HttpOnly cookies (JS не доступен), 4) sanitize HTML если разрешён (HTMLPurifier). Никогда не вставляй пользовательский ввод в HTML/JS без обработки.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 3,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое OWASP Top 10?',
                'answer' => 'OWASP Top 10 (2021) - топ-10 веб-уязвимостей: A01 Broken Access Control (нарушение прав доступа, IDOR), A02 Cryptographic Failures (слабая криптография, plain text), A03 Injection (SQLi, NoSQLi, командная), A04 Insecure Design, A05 Security Misconfiguration (дефолтные креды, debug в проде), A06 Vulnerable and Outdated Components (старые либы с CVE), A07 Identification and Authentication Failures (слабые пароли, brute-force), A08 Software and Data Integrity Failures (CI/CD, supply chain), A09 Security Logging and Monitoring Failures, A10 Server-Side Request Forgery (SSRF). Это базовый чек-лист для аудита.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 4,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое SQL injection и как защищаться?',
                'answer' => 'SQL Injection - подмена логики SQL-запроса через непроверенный пользовательский ввод. Пример: "SELECT * FROM users WHERE name = \'" . $name . "\'", где $name = "\' OR 1=1 --" даёт всех пользователей. Грозит чтением/изменением/удалением данных, эскалацией прав. Защита: 1) prepared statements (параметризованные запросы) - ввод никогда не попадает в SQL-парсер, всегда как value, 2) ORM (Eloquent, Doctrine) делает это по умолчанию, 3) валидация и whitelist для имён колонок/таблиц (их параметризовать нельзя), 4) принцип минимальных прав - у app-юзера нет DROP, 5) WAF как доп. слой. Никогда не делай ручную конкатенацию SQL.',
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
                'answer' => 'md5 и sha256 быстрые - именно поэтому плохие для паролей. Современные GPU считают миллиарды sha256/сек, при утечке базы пароли подбираются за минуты. md5 ещё и ломан криптографически (collision attacks). Для паролей нужны медленные функции: bcrypt, argon2, scrypt - они спроектированы быть медленными и потребляющими память. bcrypt: классика с 1999, cost factor (12+), есть лимит в 72 байта. Argon2id (победитель Password Hashing Competition 2015) - современный выбор, защищён от GPU/ASIC, есть параметры memory/time/parallelism. scrypt - между ними, memory-hard. Все они автоматически добавляют salt (защита от rainbow tables). В PHP - password_hash/password_verify (PASSWORD_BCRYPT по умолчанию, рекомендуется PASSWORD_ARGON2ID).',
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
                'answer' => 'libsodium (расширение sodium, входит в стандарт PHP 7.2+) - современная криптографическая библиотека от Daniel J. Bernstein и команды, спроектированная по принципу "secure by default". OpenSSL - универсальный инструмент с тысячами опций, многие из которых небезопасны или устарели. Главные различия. 1) Защита от ошибок API. В sodium практически нечего настраивать: sodium_crypto_secretbox($plaintext, $nonce, $key) - один правильный набор примитивов (XSalsa20-Poly1305 или ChaCha20-Poly1305), authenticated encryption из коробки, защита от подмены шифротекста. В OpenSSL вы выбираете cipher (AES-128/256), mode (CBC/CTR/GCM), padding, IV длину - и шанс выбрать небезопасное (CBC без HMAC = padding oracle attack) огромен. 2) Современная криптография. ChaCha20-Poly1305 быстрее AES на устройствах без AES-NI (мобилки, embedded), Curve25519/Ed25519 для подписей, BLAKE2 для хешей, Argon2id для KDF. 3) Защита от side-channel. Все sodium-функции выполняются за константное время - устойчивы к timing-атакам. В OpenSSL это надо думать самому (и ошибаться). 4) Меньшая поверхность атаки: ~50 функций sodium vs тысячи openssl. 5) Forward secrecy и nonce-misuse resistant конструкции. Когда что: новый код - sodium всегда, кроме специфичных случаев (нужны конкретные cipher для совместимости со сторонним сервером, X.509-сертификаты, S/MIME). OpenSSL остаётся для совместимости, парсинга сертификатов, специфичных алгоритмов. Laravel\'s Crypt фасад (Illuminate\\Encryption\\Encrypter) использует openssl AES-256-CBC и применяет именно Encrypt-then-MAC: сначала openssl_encrypt над сериализованным значением, затем hash_hmac("sha256", $iv.$ciphertext, $key) - это безопасный канонический подход (защищает от padding-oracle), а не уязвимое MAC-then-Encrypt, погубившее SSL/TLS. Для AEAD-шифров (AES-GCM) HMAC не используется - tag/MAC даёт сам openssl_encrypt. Для нового кода всё равно sodium предпочтительнее (меньше ручных параметров, встроенный AEAD), но Crypt-фасад реализован грамотно.',
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
                'difficulty' => 4,
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
                'answer' => 'Хеширование — это односторонняя функция: из входных данных получается строка фиксированной длины, и обратно восстановить исходник математически нельзя, можно только подбирать. Шифрование — двусторонняя операция с ключом: ciphertext расшифровывается обратно в plaintext тем же или парным ключом. Поэтому пароли хешируют (нам не нужно их читать, нужно только сравнить), а данные карты или личные сообщения шифруют, потому что их потом надо доставать в исходном виде.',
                'difficulty' => 2,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Зачем при хешировании паролей нужна соль и почему она должна быть уникальной?',
                'answer' => 'Соль — это случайные байты, которые подмешиваются к паролю перед хешированием, чтобы одинаковые пароли разных пользователей давали разные хеши. Без соли атакующий с дампом базы заранее считает rainbow-таблицу один раз и мгновенно сопоставляет хеши популярным паролям. Уникальная соль на каждого пользователя ломает этот подход: атакующему приходится брутить каждый пароль отдельно. В password_hash() соль генерируется и хранится прямо в результирующей строке, поэтому отдельной колонки под неё заводить не надо.',
                'difficulty' => 2,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем pepper отличается от соли и зачем его иногда добавляют поверх password_hash()?',
                'answer' => 'Соль уникальна на каждого пользователя и хранится рядом с хешем, а pepper — это один общий секрет, который держится не в БД, а в конфиге приложения или KMS. Идея в том, что при утечке только базы (без файлов приложения) хеши становятся бесполезны, потому что атакующий не знает pepper. Реализуется через hash_hmac($password, $pepper) перед password_hash(), либо через sodium_crypto_pwhash_str с дополнительным ключом. Минус — ротация pepper требует пересчёта хешей, поэтому в большинстве проектов хватает обычного password_hash().',
                'difficulty' => 4,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Чем argon2id отличается от bcrypt и почему его сейчас обычно предпочитают?',
                'answer' => 'bcrypt тюнится только параметром cost (раундами CPU), а argon2id подкручивает три параметра: время, память и параллелизм. За счёт memory-hard свойства argon2id ломается на GPU и ASIC заметно дороже, чем bcrypt с тем же временем проверки. Плюс у bcrypt есть исторический предел в 72 байта на пароль, у argon2id такого ограничения нет. На практике argon2id выбирают как PASSWORD_ARGON2ID в password_hash(), а bcrypt оставляют там, где argon2 не поддерживается.',
                'difficulty' => 3,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Чем отличаются stored, reflected и DOM-based XSS?',
                'answer' => 'Stored XSS — вредоносный скрипт сохраняется на сервере (в комментарии, профиле) и потом выдаётся всем посетителям страницы. Reflected XSS — скрипт прилетает в URL или форме одного запроса и сразу же выводится в ответ без сохранения, поэтому атака требует, чтобы жертва кликнула по подготовленной ссылке. DOM-based XSS вообще не доходит до сервера: уязвимость целиком в JS на клиенте, который берёт location.hash или document.referrer и пишет его в innerHTML. Поэтому защита разная: первые две лечатся экранированием на сервере, третья — корректной работой с DOM в клиентском коде.',
                'difficulty' => 3,
                'topic' => 'system_design.security',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Какие флаги cookie и зачем нужны для защиты сессии?',
                'answer' => 'Secure запрещает браузеру отправлять куку по http, чтобы её нельзя было снять с открытого Wi-Fi. HttpOnly прячет куку от document.cookie, и украденный XSS-кодом скрипт не сможет утащить идентификатор сессии. SameSite=Lax или Strict отрезает отправку куки в кросс-доменных запросах, что закрывает большую часть CSRF-сценариев. Domain и Path сужают область действия, а __Host- префикс дополнительно требует Secure, отсутствия Domain и Path=/, чтобы поддомен не мог переписать куку родителя.',
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
                'answer' => 'Любой пароль может утечь — фишингом, реюзом с другого сайта, кейлоггером, дампом базы соседнего сервиса. MFA добавляет второй фактор из другой категории (что-то, чем владеешь — TOTP-приложение, аппаратный ключ; или что-то, что ты есть — биометрия), и одной кражи пароля уже недостаточно для входа. TOTP по RFC 6238 даёт одноразовый код раз в 30 секунд, а WebAuthn/FIDO2 ещё лучше — он привязан к домену и не подделывается фишинговой страницей. SMS как второй фактор формально считается слабым из-за SIM-swap, но всё равно сильнее, чем один пароль.',
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
        ];
    }
}
