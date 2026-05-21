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
                'answer' => '**НЕЛЬЗЯ**:

1. В коде хардкодом (`$apiKey = "sk_live_..."`).
2. В **git** — даже в приватном репо, даже если потом удалить: история помнит, боты сканируют GitHub и подбирают утёкшие ключи за минуты.
3. В URL и query-параметрах — попадут в `access_log` nginx, history браузера, заголовок `Referer`.
4. В логах приложения (`Log::info($payload)` с токеном внутри).
5. В JS-бандле фронта — любой откроет DevTools и увидит.
6. В Slack/Telegram/тикетах.

**ПРАВИЛЬНО**:

1. Локально — в `.env`, который лежит в `.gitignore`. Коммитится только `.env.example` без значений.
2. На сервере — переменные окружения, файл с правами `600` у пользователя приложения.
3. На проде серьёзных систем — **секрет-менеджер**: `HashiCorp Vault`, `AWS Secrets Manager`, `GCP Secret Manager`, `Doppler`. Выдаёт секреты по запросу с аудитом и ротацией.

В Laravel секреты читаются через `env()` **только в config-файлах**, а в коде приложения — `config(\'services.stripe.secret\')` (после `config:cache` `env()` вернёт `null`).',
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
                'answer' => 'Порядок действий:

1. **НЕМЕДЛЕННО** ротировать (заменить) секрет — он уже считается **скомпрометированным**. Это первый и главный шаг.
2. Удалить из истории через `git filter-repo` (рекомендуется) или `BFG Repo Cleaner` + `git push --force-with-lease`.
3. Уведомить команду — все делают **свежий** `git clone` (старые локальные клоны всё ещё содержат секрет).
4. Проверить логи использования секрета на подозрительную активность.

**ВАЖНО**:

- просто `git rm` **не помогает** — секрет остаётся в истории всех предыдущих коммитов;
- если репо публичный, считай, что секрет **уже у атакующих** — боты сканируют GitHub в реальном времени и подбирают утёкшие ключи за минуты.',
                'code_example' => "# Шаг 1 — РОТИРОВАТЬ секрет в провайдере (Stripe, AWS, БД и т.п.)

# Шаг 2 — выпилить файл из всей истории
git filter-repo --path .env --invert-paths
# или для BFG:
bfg --delete-files .env

# Шаг 3 — force push (всем нужен свежий clone)
git push --force-with-lease --all
git push --force-with-lease --tags",
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'security.secrets',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое .env файл и почему он не должен коммититься?',
                'answer' => 'Текстовый файл в корне проекта с **переменными окружения**: `DB_PASSWORD=...`, `APP_KEY=...`, `STRIPE_SECRET=...`.

- Каждое окружение (**локалка**, **staging**, **prod**) держит **свой** `.env` с подходящими значениями.
- Коммитить нельзя — это слило бы боевые секреты в git **навсегда**.
- Поэтому `.env` лежит в `.gitignore` по умолчанию.
- В репозиторий коммитят `.env.example` — шаблон с теми же ключами, но **без значений**.

**Поток для нового разработчика**:

1. `git clone` репо.
2. `cp .env.example .env`.
3. `php artisan key:generate` сгенерирует `APP_KEY`.
4. Заполнить остальные значения руками.

В Laravel `.env` читается на старте. В **config-файлах** — `env(\'DB_PASSWORD\')`, в коде приложения — `config(\'database.connections.mysql.password\')`. После `php artisan config:cache` `env()` возвращает `null` — поэтому только через `config()`.',
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
                'answer' => 'Случайный **32-байтовый** ключ в `.env` в формате `base64:...`, генерируется через `php artisan key:generate`.

**Где используется**:

- шифрование куки и сессии (`AES-256-CBC` / `AES-256-GCM`);
- фасад `Crypt` / `encrypt()` / `decrypt()`;
- подпись signed URLs (`URL::signedRoute`);
- password reset токены.

**Последствия**:

- **утёк** → атакующий расшифровывает куки и подделывает сессии любого пользователя;
- **потерян** → все зашифрованные данные и сессии становятся нечитаемыми, юзеров разлогинит.

**Правила**:

- **разные** ключи на `dev`/`staging`/`prod`;
- бэкап ключа **отдельно** от БД (иначе утечка БД = утечка всего);
- при ротации — переход через `config/app.php` → `previous_keys`, чтобы старые куки ещё расшифровывались.',
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
                'answer' => '**НЕ** в репозитории и **НЕ** в открытых логах джоб.

**Правильные места**:

- `GitHub Actions Secrets`;
- `GitLab CI/CD Variables` (флаги `masked` + `protected`);
- `HashiCorp Vault`;
- `AWS Secrets Manager` / `GCP Secret Manager`;
- `Doppler`, `1Password CI`.

**На сервере**:

- `.env` **создаётся при деплое** из этих секретов (не лежит в репо);
- права файла `chmod 600`, владелец — пользователь приложения (`www-data`/`deploy`);
- доступ только у тех, кому реально нужно.

**В логах CI**:

- секреты должны быть **замаскированы** (`***`);
- никаких `echo $TOKEN`, `set -x` в шагах, где есть секреты.',
                'code_example' => "# .github/workflows/deploy.yml
jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Build .env on server
        env:
          DB_PASSWORD: \${{ secrets.DB_PASSWORD }}
          STRIPE_SECRET: \${{ secrets.STRIPE_SECRET }}
        run: |
          ssh deploy@prod 'cat > /var/app/.env && chmod 600 /var/app/.env' <<EOF
          DB_PASSWORD=\${DB_PASSWORD}
          STRIPE_SECRET=\${STRIPE_SECRET}
          EOF",
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'security.secrets',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое ротация секретов и как сделать её без простоя?',
                'answer' => '**Ротация** — замена паролей БД, API-ключей, `JWT_SECRET`, `APP_KEY` на новые. Цель — минимизировать **время жизни** скомпрометированного секрета.

**Когда делать**:

- **плановая** — раз в квартал-полгода;
- **внеплановая** — утечка, увольнение админа, подозрение на компрометацию.

**Главная сложность**: нельзя «выключить и включить с новым» — это **простой**. Стандартный паттерн — **overlap** (graceful rotation):

1. Приложение поддерживает **старый и новый** секрет одновременно.
2. **Подписывает** новым.
3. При **верификации** сначала пробует новым, потом старым.
4. Через `N` часов/дней (когда все клиенты получили новый) — старый удаляется.

**Частные случаи**:

| Секрет | Паттерн |
| --- | --- |
| `APP_KEY` Laravel | `previous_keys` в `config/app.php` |
| `JWT_SECRET` (HS256) | accept = `[current, previous]`, sign = `current` |
| `refresh_token` | refresh rotation: каждый refresh → новый токен, старый инвалидируется |
| БД-пароль | managed: AWS Secrets Manager rotation lambda / Vault dynamic secrets |

**Принципы**:

- ротация **автоматизирована** — не «надо вспомнить»;
- есть **аудит-лог** каждого использования;
- есть план **экстренной** ротации, тренировка раз в год.',
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
                'answer' => 'Когда страница ошибки в проде показывает **`Stack Trace`** с путями файлов, версиями фреймворка, частями кода, иногда — значениями переменных и SQL-запросов.

**Чем опасно**:

- атакующий узнаёт **стек технологий** и **версии** библиотек;
- ищет публичные `CVE` под эти версии;
- по путям файлов видит структуру проекта;
- из дампа переменных может всплыть значение `DB_PASSWORD`, токенов, payload запроса с паролем.

**Защита**:

- в проде **`APP_DEBUG=false`**;
- кастомная страница `500` / `errors.500.blade.php` без деталей;
- error-tracking (`Sentry`, `Bugsnag`) собирает stack trace **внутри**, наружу — только `request-id`;
- логи приложения не доступны через web (вне `public/`).',
                'difficulty' => 2,
                'topic' => 'security.secrets',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Можно ли класть секреты в JavaScript-бандл фронтенда?',
                'answer' => '**Нет.** Любой бандл, ушедший в браузер, виден через **`DevTools`** → `Sources` и доступен любому посетителю — это эквивалентно публикации секрета на твоём же сайте.

**На фронте могут быть только публичные ключи**:

- `Stripe publishable key` (`pk_live_...`);
- `Google Maps API key` с доменными ограничениями;
- публичный ключ для проверки `JWT`.

**Серверные секреты живут только на бэкенде**:

- `Stripe secret` (`sk_live_...`);
- пароль БД;
- `JWT_SECRET`;
- API-ключи третьих сервисов.

**Осторожно с префиксами сборщиков**: `VITE_*`, `NEXT_PUBLIC_*`, `REACT_APP_*` — Vite/Next/CRA **зашивают** такие переменные прямо в бандл. Никогда не давай этот префикс серверным секретам.',
                'difficulty' => 2,
                'topic' => 'security.secrets',
            ],
        ];
    }
}
