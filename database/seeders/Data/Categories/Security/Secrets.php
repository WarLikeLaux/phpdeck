<?php

namespace Database\Seeders\Data\Categories\Security;

class Secrets
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Безопасность',
                'question' => 'Где НЕ хранить пароли БД и API-ключи простыми словами?',
                'answer' => 'НЕ хранить: в коде (config.php, hardcoded), в git (даже в private repo — git history помнит), в логах, в URL (попадают в access_log), в JS-бандле фронта (его любой может скачать). Хранить — в .env (в .gitignore), переменных окружения, секрет-менеджере (HashiCorp Vault, AWS Secrets Manager).',
                'difficulty' => 1,
                'topic' => 'security.secrets',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что делать, если секрет случайно закоммитили в git?',
                'answer' => '1) НЕМЕДЛЕННО ротировать (заменить) секрет — он уже считается скомпрометированным. 2) Удалить из истории через git filter-repo / BFG Repo Cleaner + force-push. 3) Уведомить команду, всем сделать git clone заново. ВАЖНО: просто git rm не помогает — секрет остаётся в истории. Если репо публичный, считай, что секрет уже у атакующих.',
                'difficulty' => 2,
                'topic' => 'security.secrets',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое .env файл и почему он не должен коммититься?',
                'answer' => 'Текстовый файл с переменными окружения для приложения: DB_PASSWORD=..., APP_KEY=..., STRIPE_SECRET=.... В .gitignore по умолчанию. Коммитится только .env.example — шаблон БЕЗ реальных значений. В Laravel читается автоматически через env() / config().',
                'difficulty' => 1,
                'topic' => 'security.secrets',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое утечка стека в проде и почему она опасна?',
                'answer' => 'Когда страница ошибки показывает Stack Trace с путями файлов, версиями фреймворка, частями кода. Атакующий узнаёт стек технологий, пути, может найти уязвимости. Защита: в проде APP_DEBUG=false, кастомная страница 500, error-tracking (Sentry) собирает stack trace внутри.',
                'difficulty' => 2,
                'topic' => 'security.secrets',
            ],
        ];
    }
}
