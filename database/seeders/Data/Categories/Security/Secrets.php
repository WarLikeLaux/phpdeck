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
                'answer' => 'НЕЛЬЗЯ: 1) В коде хардкодом ($apiKey = "sk_live_..."). 2) В git — даже в приватном репо, даже если потом удалить: git history помнит, боты регулярно сканируют GitHub и подбирают утёкшие ключи за минуты. 3) В URL и query-параметрах — попадут в access_log nginx, history браузера, заголовок Referer. 4) В логах приложения (Log::info($payload) с токеном внутри). 5) В JS-бандле фронта — любой откроет DevTools и увидит. 6) В Slack/Telegram/тикетах. ПРАВИЛЬНО: 1) Локально — в .env, который лежит в .gitignore, коммитится только .env.example без значений. 2) На сервере — переменные окружения, файл с правами 600 у пользователя приложения. 3) На проде серьёзных систем — секрет-менеджер (HashiCorp Vault, AWS Secrets Manager, GCP Secret Manager, Doppler), который выдаёт секреты по запросу с аудитом и ротацией. В Laravel секреты читаются через env() в config-файлах, а в коде — только config(\'services.stripe.secret\').',
                'code_example' => "# .env (НЕ коммитим, в .gitignore)
DB_PASSWORD=real-secret-password
STRIPE_SECRET=sk_live_abc123xyz
JWT_SECRET=base64:Mn9k...

# .env.example (коммитим — только ключи, без значений)
DB_PASSWORD=
STRIPE_SECRET=
JWT_SECRET=",
                'code_language' => 'bash',
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
                'answer' => 'Текстовый файл в корне проекта с переменными окружения: DB_PASSWORD=..., APP_KEY=..., STRIPE_SECRET=.... Каждое окружение (локалка, staging, prod) держит СВОЙ .env с подходящими значениями, поэтому коммитить его нельзя — это слило бы боевые секреты в git навсегда. Поэтому .env лежит в .gitignore по умолчанию, а в репозиторий коммитят .env.example — шаблон с теми же ключами, но БЕЗ значений или с placeholder. Новый разработчик клонирует репо, копирует cp .env.example .env, заполняет своими значениями. В Laravel .env читается автоматически на старте: в config-файлах вызывают env(\'DB_PASSWORD\') (только там!), а в коде приложения используют config(\'database.connections.mysql.password\') — так после php artisan config:cache всё работает быстро, потому что env() после кеша конфига уже не возвращает значений.',
                'code_example' => "# .env (НЕ коммитим — в .gitignore по умолчанию)
APP_KEY=base64:r4nd0mGenerated...
DB_PASSWORD=real-prod-password
STRIPE_SECRET=sk_live_abc123

# .env.example (коммитим — шаблон с ключами без значений)
APP_KEY=
DB_PASSWORD=
STRIPE_SECRET=

# Поток для нового разработчика:
cp .env.example .env
php artisan key:generate   # сгенерирует APP_KEY
# дальше руками заполняет DB_PASSWORD и т.п.",
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
                'question' => 'Что такое ротация секретов и как сделать её без простоя?',
                'answer' => 'Ротация — регулярная (раз в квартал-полгода) и внеплановая (утечка, увольнение админа, подозрение на компромат) замена паролей БД, API-ключей, JWT-секретов, APP_KEY. Цель — минимизировать время жизни скомпрометированного секрета. Сложность: нельзя «выключить и включить с новым» — это простой. Стандартный паттерн — overlap (graceful rotation): приложение поддерживает старый и новый секрет ОДНОВРЕМЕННО. Подписывает новым, при верификации сначала пробует новым, потом старым. Через N часов/дней, когда все клиенты получили новый, старый удаляется. Для refresh-токенов — refresh rotation (на каждый refresh выдаём новый и инвалидируем старый). Для секретов в managed-хранилище — Vault dynamic secrets, AWS Secrets Manager rotation lambda. Главные принципы: ротация автоматизирована (не «надо вспомнить»), есть аудит-лог, есть план на экстренную ротацию (раз в год тренировка).',
                'code_example' => "<?php
// Laravel APP_KEY — overlap-режим, никого не разлогинит
// config/app.php
return [
    'key' => env('APP_KEY'),
    'previous_keys' => array_filter([
        env('APP_PREVIOUS_KEY'), // старый — расшифровка ещё работает
    ]),
];

// JWT-секрет в overlap
\$current = config('jwt.secret');           // подписываем им
\$previous = config('jwt.previous_secret'); // ещё принимаем при verify
foreach (array_filter([\$current, \$previous]) as \$key) {
    try { return JWT::decode(\$token, new Key(\$key, 'HS256')); }
    catch (SignatureInvalidException \$e) { /* try next */ }
}
abort(401);",
                'code_language' => 'php',
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
