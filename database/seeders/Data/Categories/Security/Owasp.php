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
                'answer' => 'Ошибки в проверках, кому что можно. Примеры: 1) GET /users/42/orders — пользователь 1 может посмотреть заказы пользователя 42, нет проверки. 2) Админская кнопка спрятана только на фронте, а API-эндпоинт доступен. 3) Изменение role=admin в форме. №1 в OWASP Top 10 на 2021.',
                'difficulty' => 2,
                'topic' => 'security.owasp',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое IDOR простыми словами?',
                'answer' => 'Insecure Direct Object Reference — частный случай Broken Access Control. Пользователь меняет ID в URL/теле запроса и получает доступ к чужим данным: /invoices/123 → /invoices/124 покажет чужой инвойс, если нет проверки «принадлежит ли он мне». Защита — авторизация на каждом обращении к ресурсу.',
                'difficulty' => 2,
                'topic' => 'security.owasp',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое Security Misconfiguration простыми словами?',
                'answer' => 'Слабые/дефолтные настройки безопасности: открыт debug-режим на проде, dispatched stack trace в ошибках, дефолтные пароли admin/admin, открытые порты, незакрытые CORS, нет HTTPS. Часто это самые «лёгкие» взломы — атакующему даже не нужно искать уязвимость в коде.',
                'difficulty' => 2,
                'topic' => 'security.owasp',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое Vulnerable Components простыми словами?',
                'answer' => 'Использование старых версий библиотек/фреймворков с известными уязвимостями (CVE). Пример: Log4Shell в Log4j (2021) — Java-приложения с уязвимой версией позволяли RCE через лог-строку. Защита: composer audit, npm audit, Dependabot, обновляй зависимости.',
                'difficulty' => 2,
                'topic' => 'security.owasp',
            ],
        ];
    }
}
