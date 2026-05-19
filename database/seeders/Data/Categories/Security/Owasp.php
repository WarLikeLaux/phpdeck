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
                'answer' => 'Список из 10 самых распространённых уязвимостей веб-приложений, который обновляется раз в несколько лет некоммерческой организацией OWASP. Это must-read для любого веб-разработчика: подавляющее большинство реальных взломов идёт через эти классы уязвимостей.',
                'difficulty' => 1,
                'topic' => 'security.owasp',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое Broken Access Control простыми словами?',
                'answer' => 'Ошибки в проверках, кому что можно. Примеры: 1) GET /users/42/orders — пользователь 1 может посмотреть заказы пользователя 42, нет проверки. 2) Админская кнопка спрятана только на фронте, а API-эндпоинт доступен. 3) Изменение role=admin в форме. №1 в OWASP Top 10 на 2021. Защита: авторизация на каждом эндпоинте, deny by default, Policies/Gates в Laravel.',
                'difficulty' => 2,
                'topic' => 'security.owasp',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое IDOR простыми словами?',
                'answer' => 'Insecure Direct Object Reference — частный случай Broken Access Control. Пользователь меняет ID в URL/теле запроса и получает доступ к чужим данным: /invoices/123 → /invoices/124 покажет чужой инвойс, если нет проверки «принадлежит ли он мне». Защита — авторизация на каждом обращении к ресурсу: where(\'user_id\', auth()->id()) или Gate/Policy.',
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
                'answer' => 'Атакующий подсовывает свой код туда, где сервер ждёт данные. Виды: SQL Injection (вставка SQL), Command Injection (вставка shell-команд через exec/system), LDAP Injection, NoSQL Injection, HTML/XSS (по сути инъекция JS). Общее правило защиты: НЕ склеивать пользовательский ввод со строкой команды — использовать параметризованные API (prepared statements, escapeshellarg, htmlspecialchars).',
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
                'answer' => 'Слабые/дефолтные настройки безопасности: открыт debug-режим на проде, stack trace в ошибках, дефолтные пароли admin/admin, открытые порты, слишком разрешающий CORS, нет HTTPS, /phpinfo доступен миру. Часто это самые «лёгкие» взломы — атакующему даже не нужно искать уязвимость в коде.',
                'difficulty' => 2,
                'topic' => 'security.owasp',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое Vulnerable Components простыми словами?',
                'answer' => 'Использование старых версий библиотек/фреймворков с известными уязвимостями (CVE). Пример: Log4Shell в Log4j (2021) — Java-приложения с уязвимой версией позволяли RCE через лог-строку. Защита: composer audit, npm audit, Dependabot/Renovate, регулярно обновляй зависимости.',
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
