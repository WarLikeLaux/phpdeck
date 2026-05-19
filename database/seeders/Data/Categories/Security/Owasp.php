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
                'answer' => 'Список из 10 самых распространённых классов уязвимостей веб-приложений, который обновляется раз в несколько лет некоммерческой организацией OWASP. В версии 2021 в топе: Broken Access Control (№1), Cryptographic Failures, Injection (включая SQLi/XSS), Insecure Design, Security Misconfiguration, Vulnerable Components, Auth Failures, Software/Data Integrity, Logging Failures, SSRF. Это must-read для веб-разработчика — подавляющее большинство реальных взломов идёт через эти классы.',
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
                'question' => 'Что такое Insecure Design простыми словами?',
                'answer' => 'Уязвимости, заложенные на этапе проектирования, а не в реализации. Примеры: восстановление пароля только по дате рождения (легко угадать), reset-токен с длиной 6 цифр без rate limit, отсутствие в архитектуре идеи throttling/lockout. Даже идеальный код не спасёт плохой дизайн. Защита: threat modeling, security review до старта разработки.',
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
                'question' => 'Что такое Software and Data Integrity Failures простыми словами?',
                'answer' => 'Доверие коду/данным без проверки подписи. Примеры: CI/CD ставит npm-пакет без integrity-чека, автообновление ПО без проверки подписи, десериализация недоверенных данных (PHP unserialize() с пользовательским вводом — путь к RCE). Защита: lock-файлы, SRI для CDN-скриптов, подписи артефактов, json_decode вместо unserialize для внешних данных.',
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
                'question' => 'Что такое Server-Side Request Forgery (SSRF) как пункт OWASP Top 10?',
                'answer' => 'Атакующий заставляет твой сервер послать HTTP-запрос куда ему нужно — на внутренние сервисы (Redis, metadata-эндпоинт облака), localhost, частные сети. Появилась как отдельный пункт №10 в OWASP 2021. Защита: whitelist разрешённых доменов, блок 127.0.0.1 / 10.0.0.0/8 / 169.254.169.254, отдельный egress-firewall для исходящего трафика.',
                'difficulty' => 3,
                'topic' => 'security.owasp',
            ],
        ];
    }
}
