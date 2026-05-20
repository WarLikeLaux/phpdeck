<?php

namespace Database\Seeders\Data\Categories\Security;

class Owasp
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Безопасность',
                'question' => 'Что такое OWASP Top 10 простыми словами?',
                'answer' => 'Список из **10 самых распространённых классов уязвимостей** веб-приложений. Раз в несколько лет составляет некоммерческая организация **OWASP** (`Open Worldwide Application Security Project`) на основе реальной статистики взломов.

**Версия 2021**:

1. **Broken Access Control** — ошибки проверки прав, можно зайти на чужой ресурс по URL.
2. **Cryptographic Failures** — слабая криптография, пароли в `md5`, нет `HTTPS`.
3. **Injection** — `SQLi`, `XSS`, command injection.
4. **Insecure Design** — небезопасная бизнес-логика.
5. **Security Misconfiguration** — `APP_DEBUG=true` на проде, дефолтные пароли.
6. **Vulnerable Components** — старые версии библиотек с `CVE`.
7. **Identification and Authentication Failures** — слабый логин/сессии.
8. **Software and Data Integrity Failures** — supply chain атаки, `unserialize`.
9. **Security Logging and Monitoring Failures** — нет логов и алертов.
10. **SSRF** — сервер ходит туда, куда подсунули.

Большая часть реальных взломов идёт именно через эти классы — поэтому `OWASP Top 10` учат все разработчики.',
                'difficulty' => 1,
                'topic' => 'security.owasp',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое Broken Access Control простыми словами?',
                'answer' => '№1 в OWASP Top 10 2021 — ошибки в проверках «кому что можно». Типичные баги: 1) GET /users/42/orders — юзер 1 видит заказы юзера 42, нет проверки владельца (IDOR). 2) Админская кнопка спрятана только на фронте, а POST /admin/users открыт публично. 3) Mass assignment role=admin через update($request->all()). 4) Прямой доступ к /admin без middleware. Принципы защиты: deny by default, проверка авторизации на КАЖДОМ эндпоинте (не только UI), Policies/Gates в Laravel, явный whitelist полей в $fillable / FormRequest.',
                'code_example' => "<?php
// Группа админских роутов — middleware на ВСЕЙ группе
Route::middleware(['auth', 'can:admin'])->group(function () {
    Route::resource('admin/users', AdminUserController::class);
});

// В контроллере — Policy на конкретный ресурс
public function update(UpdatePostRequest \$request, Post \$post) {
    \$this->authorize('update', \$post); // PostPolicy::update
    \$post->update(\$request->validated());
}",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'security.owasp',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое IDOR простыми словами?',
                'answer' => 'Insecure Direct Object Reference — частный случай Broken Access Control. Пользователь меняет ID в URL/теле запроса и получает доступ к чужим данным: /invoices/123 → /invoices/124 покажет чужой инвойс, если сервер делает Invoice::find($id) и не проверяет принадлежность. Защита: авторизация на каждом обращении к ресурсу — фильтр по auth()->id() в запросе или Gate/Policy в Laravel. Дополнительно — UUID/ULID вместо инкрементных id, чтобы соседний id нельзя было угадать перебором (security-through-obscurity, не основная защита).',
                'code_example' => "<?php
// ПЛОХО — берём по id без проверки владельца
public function show(\$id) {
    return Invoice::findOrFail(\$id);
}

// ХОРОШО — фильтр в запросе
public function show(\$id) {
    return auth()->user()->invoices()->findOrFail(\$id);
}

// Или через Policy
public function show(Invoice \$invoice) {
    \$this->authorize('view', \$invoice); // 403, если не владелец
    return \$invoice;
}",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'security.owasp',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое Cryptographic Failures простыми словами?',
                'answer' => 'Ошибки в работе с криптографией и чувствительными данными. Примеры: пароли в md5, секреты в открытом виде в БД, отсутствие HTTPS, использование устаревших алгоритмов (DES, RC4, SHA-1), самописное шифрование. №2 в OWASP Top 10 на 2021 (раньше называлось Sensitive Data Exposure). Защита: bcrypt/argon2 для паролей, AES-GCM для шифрования, готовые библиотеки, HTTPS везде.',
                'difficulty' => 2,
                'topic' => 'security.owasp',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое Injection простыми словами как класс уязвимостей?',
                'answer' => 'Класс уязвимостей, в которых атакующий подсовывает свой КОД туда, где сервер ждёт ДАННЫЕ. Корень проблемы — конкатенация пользовательского ввода со строкой команды/запроса. Виды: SQL Injection (вставка SQL в запрос), Command Injection (вставка shell-команд в exec/system), LDAP/NoSQL Injection, XSS (инъекция JS в HTML), Template Injection (Blade/Twig), Header Injection (\\r\\n в заголовках). Общее правило защиты: НЕ склеивать ввод с командой — использовать параметризованные API: prepared statements, escapeshellarg, htmlspecialchars, валидацию по whitelist.',
                'difficulty' => 2,
                'topic' => 'security.owasp',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое Insecure Design и чем он отличается от Misconfiguration?',
                'answer' => 'Пункт №4 в OWASP Top 10 2021 — уязвимости, заложенные на этапе ПРОЕКТИРОВАНИЯ бизнес-логики, а не в коде или настройках. Идеальная реализация плохой идеи всё равно небезопасна. Отличие от Security Misconfiguration: misconfig — это «забыли включить флаг»/«дефолтный пароль», insecure design — «фича изначально позволяет атаку». Классические примеры: восстановление пароля по дате рождения (легко угадать через соцсети), 4-6 цифр в reset-коде без rate limit (брутится за минуты), купон на скидку без проверки one-time-use, race condition в переводе денег (TOCTOU), отсутствие лимита попыток ввода 2FA, бизнес-flow «верификация по SMS» как единственный фактор для крупных операций. Защита: threat modeling до старта (STRIDE, abuser stories наряду с user stories), security review дизайна, явные требования к rate limit / lockout / идемпотентности на уровне спецификации, «secure by default» — фича сначала закрыта, потом открывается.',
                'code_example' => "<?php
// Плохой дизайн — 6-значный код без лимита попыток
public function verifyReset(Request \$r) {
    \$u = User::where('reset_code', \$r->code)->first();
    if (\$u) Auth::login(\$u); // брутфорс 1000000 за минуты
}

// Хороший дизайн — длинный токен + lockout + одноразовость
public function verifyReset(Request \$r) {
    RateLimiter::hit('reset:'.\$r->ip(), 60); // 5 попыток в минуту
    if (RateLimiter::tooManyAttempts('reset:'.\$r->ip(), 5)) abort(429);

    \$record = PasswordReset::where('token_hash', hash('sha256', \$r->token))
        ->where('expires_at', '>', now())->firstOr(fn() => abort(401));
    \$record->delete(); // одноразовый
    Auth::login(\$record->user);
}",
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'security.owasp',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое Security Misconfiguration простыми словами?',
                'answer' => 'Слабые/дефолтные настройки безопасности — самый «дешёвый» класс взломов, кода атакующего может не понадобиться. Типичные грабли в Laravel/PHP-стеке: APP_DEBUG=true на проде (stack trace + переменные окружения наружу), дефолтные пароли admin/admin, открытые порты MySQL/Redis наружу, CORS с * + credentials, отсутствие HTTPS/HSTS, доступный /phpinfo.php или /.env, незакрытые админки CI/мониторинга. Защита: чек-лист на деплое, security headers (CSP, HSTS, X-Frame-Options), сканеры конфигов, отдельные prod-настройки.',
                'difficulty' => 2,
                'topic' => 'security.owasp',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое Vulnerable Components простыми словами?',
                'answer' => 'Использование старых версий библиотек, фреймворков или рантайма с публично известными уязвимостями (CVE). Хрестоматийный пример — Log4Shell в Log4j (CVE-2021-44228): любая Java-программа с уязвимой версией Log4j позволяла RCE через специальную строку в логе. Аналогично в PHP-экосистеме регулярно прилетают CVE в Laravel, Symfony, Guzzle. Защита: composer audit / npm audit в CI, Dependabot/Renovate для автоматических PR с обновлениями, отслеживание CVE-фидов, регулярные мажорные апгрейды, отказ от заброшенных пакетов.',
                'code_example' => "# Проверить известные CVE в composer.lock
composer audit

# В npm
npm audit
npm audit fix",
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'security.owasp',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое Identification and Authentication Failures простыми словами?',
                'answer' => 'Слабые места в логине и сессиях. Примеры: разрешены пароли «123456», нет защиты от brute force, session ID в URL, сессия не инвалидируется после logout, утечка «пользователь не существует» в форме входа, MFA отсутствует для админов. Защита: rate limit на /login, 2FA, длинные пароли, проверка по haveibeenpwned, регенерация session ID после логина.',
                'difficulty' => 2,
                'topic' => 'security.owasp',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое Software and Data Integrity Failures и причём тут unserialize?',
                'answer' => 'Пункт №8 в OWASP Top 10 2021 — доверие коду или данным без проверки целостности. Примеры: CI/CD ставит npm/composer-пакет без integrity-чека (supply chain атака — atomicfoo, event-stream), автообновление ПО без проверки подписи, CDN-скрипт без SRI-хеша, десериализация недоверенных данных. Последнее — самое опасное в PHP: unserialize() с пользовательским вводом — путь к RCE через POP-чейн (PHP Object Property-Oriented Programming): атакующий конструирует строку, которая при unserialize создаёт цепочку объектов, у которых __destruct/__wakeup/__toString делают вредоносное (file_put_contents, exec). Аналог в Java — Apache Commons Collections gadget. Защита: 1) lock-файлы (composer.lock, package-lock.json) и composer/npm audit в CI. 2) SRI-атрибут integrity= у внешних <script>. 3) Подписи артефактов релизов (cosign, GPG). 4) Для внешних данных — json_decode, никогда unserialize. 5) Если unserialize неизбежен — second-аргумент allowed_classes с явным whitelist.',
                'code_example' => "<?php
// ОЧЕНЬ ПЛОХО — RCE через POP-chain
\$data = unserialize(\$_COOKIE['state']);

// Лучше — JSON, не исполняет код
\$data = json_decode(\$_COOKIE['state'], true);

// Если unserialize неизбежен (legacy) — whitelist классов
\$data = unserialize(\$blob, ['allowed_classes' => [DTO::class]]);

// SRI для внешних скриптов
// <script src=\"https://cdn.example.com/lib.js\"
//   integrity=\"sha384-oqVuAfXRKap7fdgcCY5uykM6+R9GqQ8K/uxy9rx7HNQlGYl1kPzQho1wx4JwY8wC\"
//   crossorigin=\"anonymous\"></script>",
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'security.owasp',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое Security Logging and Monitoring Failures простыми словами?',
                'answer' => 'Атака есть, а ты о ней не узнаешь. Симптомы: не логируются неудачные логины, в логах нет user_id/IP, нет алертов на массовый 403/500, логи стираются раньше, чем заметили инцидент. Защита: централизованный лог (ELK, Sentry, Datadog), алерты на аномалии, хранение access-логов 90+ дней, периодический ручной просмотр.',
                'difficulty' => 2,
                'topic' => 'security.owasp',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Почему SSRF попал в OWASP Top 10 отдельным пунктом и как защититься в архитектуре?',
                'answer' => 'SSRF (Server-Side Request Forgery) — атакующий заставляет твой сервер сходить по нужному ему URL. В OWASP Top 10 2021 SSRF выделен в отдельный №10 (раньше шёл внутри Broken Access Control), потому что взрывной рост микросервисов и облаков сделал его одной из ведущих причин крупных утечек (Capital One 2019 — через SSRF получили AWS IAM credentials из metadata-эндпоинта). Опасность не только в чтении внутренних API, но и в side-effects: внутренний Redis принимает команды по HTTP-протоколу, внутренние админки часто без auth. Защита по слоям (defense-in-depth): 1) В коде — whitelist разрешённых доменов вместо blacklist. 2) Резолвить DNS заранее и блочить приватные IP-диапазоны (включая 169.254.169.254, ::1, IPv4-mapped IPv6). 3) Запрет редиректов или проверка каждого hop. 4) Архитектурно — отдельная сетевая зона для исходящего трафика (egress proxy типа Squid с whitelist). 5) Для AWS — IMDSv2 (требует токена, защищён). 6) Запуск сервиса с минимальным IAM-ролем, чтобы даже при SSRF метадата была бесполезна.',
                'code_example' => "<?php
// В контроллере — резолвим и проверяем все IP домена
function fetchExternal(string \$url): string {
    \$host = parse_url(\$url, PHP_URL_HOST) ?: abort(400);
    foreach (gethostbynamel(\$host) ?: [] as \$ip) {
        if (!filter_var(\$ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            abort(400, 'blocked: private IP '.\$ip);
        }
    }
    return Http::withOptions(['allow_redirects' => false, 'timeout' => 5])->get(\$url)->body();
}

# На AWS — обязательно IMDSv2
aws ec2 modify-instance-metadata-options --http-tokens required",
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'security.owasp',
            ],
        ];
    }
}
