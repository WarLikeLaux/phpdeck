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
                'answer' => 'НЕ хранить: в коде (config.php, hardcoded), в git (даже в private repo — git history помнит), в логах, в URL (попадают в access_log), в JS-бандле фронта (его любой может скачать). Хранить — в .env (в .gitignore), переменных окружения, секрет-менеджере (HashiCorp Vault, AWS Secrets Manager, GCP Secret Manager).',
                'difficulty' => 1,
                'topic' => 'security.secrets',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что делать, если секрет случайно закоммитили в git?',
                'answer' => '1) НЕМЕДЛЕННО ротировать (заменить) секрет — он уже считается скомпрометированным. 2) Удалить из истории через git filter-repo / BFG Repo Cleaner + force-push. 3) Уведомить команду, всем сделать git clone заново. ВАЖНО: просто git rm не помогает — секрет остаётся в истории. Если репо публичный, считай, что секрет уже у атакующих (боты сканируют GitHub в реальном времени).',
                'difficulty' => 2,
                'topic' => 'security.secrets',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое .env файл и почему он не должен коммититься?',
                'answer' => 'Текстовый файл с переменными окружения для приложения: DB_PASSWORD=..., APP_KEY=..., STRIPE_SECRET=.... В .gitignore по умолчанию. Коммитится только .env.example — шаблон БЕЗ реальных значений. В Laravel читается автоматически через env() (используется в config-файлах) и config() (используется в коде).',
                'code_example' => "# .env (НЕ коммитим)
DB_PASSWORD=real-prod-password
STRIPE_SECRET=sk_live_abc123

# .env.example (коммитим, шаблон)
DB_PASSWORD=
STRIPE_SECRET=",
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'security.secrets',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое APP_KEY в Laravel и зачем он нужен?',
                'answer' => 'Случайный 32-байтовый ключ в .env (формат base64:...), генерируется через php artisan key:generate. Laravel использует его для: шифрования куки и сессии (AES-256-CBC/GCM), фасада Crypt / encrypt(), подписи signed URLs и password reset токенов. Если ключ утёк — атакующий расшифровывает куки и подделывает сессии любого пользователя. Если потеряли — все зашифрованные данные и сессии становятся нечитаемыми (юзеров разлогинит). Поэтому: разные ключи на dev/staging/prod, бэкап ключа отдельно от БД, при ротации — переход через config/app.php previous_keys.',
                'code_example' => "# Генерация
php artisan key:generate
# пишет в .env что-то вида:
# APP_KEY=base64:r4nd0mBytesEncodedAsBase64...

# Ротация без разлогина всех:
# config/app.php
'previous_keys' => [
    env('APP_PREVIOUS_KEY'), // старый ключ — расшифровка ещё работает
],",
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'security.secrets',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Где хранить секреты для CI/CD и деплоя простыми словами?',
                'answer' => 'НЕ в репозитории и не в open-логах джоб. Хранить в специальных хранилищах: GitHub Actions Secrets, GitLab CI/CD Variables (с masked + protected), HashiCorp Vault, AWS Secrets Manager, Doppler. На сервере .env создаётся при деплое из этих секретов, права 600, владелец — пользователь приложения. В логах CI секреты должны быть замаскированы.',
                'difficulty' => 2,
                'topic' => 'security.secrets',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое ротация секретов и зачем она нужна?',
                'answer' => 'Регулярная плановая замена ключей и паролей (раз в квартал/полгода) и внеплановая — после утечки, увольнения админа, подозрений. Минимизирует время жизни скомпрометированного секрета. Удобнее всего, когда приложение поддерживает «два валидных ключа одновременно» на время переходного периода — иначе ротация = простой.',
                'difficulty' => 3,
                'topic' => 'security.secrets',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое утечка стека в проде и почему она опасна?',
                'answer' => 'Когда страница ошибки показывает Stack Trace с путями файлов, версиями фреймворка, частями кода, иногда — значениями переменных и SQL-запросов. Атакующий узнаёт стек технологий, пути, ищет публичные CVE под версии. Защита: в проде APP_DEBUG=false, кастомная страница 500, error-tracking (Sentry) собирает stack trace внутри, наружу — только request-id.',
                'difficulty' => 2,
                'topic' => 'security.secrets',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Можно ли класть секреты в JavaScript-бандл фронтенда?',
                'answer' => 'Нет. Любой бандл, ушедший в браузер, виден через DevTools и доступен любому посетителю — это эквивалентно публикации секрета. На фронте могут быть только публичные ключи (Stripe publishable key, Google Maps API key с доменными ограничениями), а серверные секреты (Stripe secret, DB-пароль, JWT-секрет) живут только на бэкенде. В Vite/Webpack будь осторожен с VITE_*/NEXT_PUBLIC_* префиксами — они зашивают значение в бандл.',
                'difficulty' => 2,
                'topic' => 'security.secrets',
            ],
        ];
    }
}
