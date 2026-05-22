<?php

namespace Database\Seeders\Data\Categories\SystemDesign;

class DesignTasks
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Архитектура систем',
                'question' => 'Как спроектировать rate limiter?',
                'answer' => 'Rate limiter — компонент, ограничивающий **число запросов** от клиента за интервал. Защищает от перегрузки, brute-force, DoS, abuse.

**Пять классических алгоритмов:**

| Алгоритм | Точность | Память | Burst | Сложность |
|---|---|---|---|---|
| **Fixed window** | низкая | мало | **всплеск на границе** | простой |
| **Sliding window log** | **точная** | **много** (timestamp каждого) | плавный | сложный |
| **Sliding window counter** | средняя | мало | плавный | средний |
| **`token bucket`** | средняя | мало | **поддерживает burst** | **классика** |
| **`leaky bucket`** | средняя | мало | **сглаживает** | средний |

**1. `Fixed window`** — счётчик за минуту:

- `INCR rate:user:42:202405221430` + `EXPIRE 60`
- **Минус:** **всплеск на границе** — 100 в `:14:59`, ещё 100 в `:15:00` = 200 в секунду
- **Когда:** простые сценарии, грубое ограничение

**2. `Sliding window log`** — массив timestamp:

- Храним **timestamp каждого запроса** в Redis `ZSET`
- При запросе удаляем устаревшие (`ZREMRANGEBYSCORE`), считаем (`ZCARD`)
- **Точно**, но **дорого по памяти** при больших RPS

**3. `Sliding window counter`** — компромисс:

- Два счётчика: текущая минута + предыдущая
- `count = current + previous * (1 - elapsed_in_current_window / window_size)`
- Близко к sliding log, дёшево

**4. `token bucket`** — классика для бёрстов:

- Bucket с **capacity** токенов, **refill rate** (например, 10 токенов/сек)
- На каждый запрос — `-1 token`
- **`token < 1`** → отказ
- **Поддерживает всплески** до capacity, плавно ограничивает на длинной дистанции
- Используется в `AWS API Gateway`, `Stripe`, **`Laravel RateLimiter`**

**5. `leaky bucket`** — сглаживание:

- Очередь с **фиксированной скоростью вытекания**
- Излишек **переполняет** bucket → отказ
- **Гарантирует ровную нагрузку** на downstream

**Реализация в Redis:**

- **Атомарность** — `INCR`/`EXPIRE` или **`Lua`-скрипт** (несколько команд под GIL Redis)
- **Ключ:** `user_id`, `IP`, `api_key`, `tenant_id`
- **Distributed** — Redis Cluster с consistent hashing

**Ответ при превышении:**

- HTTP **`429 Too Many Requests`**
- **`Retry-After: <seconds>`** — когда можно повторить
- **`X-RateLimit-Limit`**, **`X-RateLimit-Remaining`**, **`X-RateLimit-Reset`** headers

**Уровни rate limiting:**

| Уровень | Где |
|---|---|
| **Edge / CDN** | Cloudflare, Fastly — защита от DDoS |
| **API Gateway** | per-tenant, per-endpoint |
| **Application** | per-user feature, per-action (login: 5/min) |
| **Database** | connection pool, query timeout |

**В Laravel из коробки:**

- **`Route::middleware(\'throttle:60,1\')`** — 60 запросов в минуту
- **`RateLimiter::for(...)`** в `AppServiceProvider` — кастомные лимиты',
                'code_example' => '-- Redis Lua: token bucket
-- KEYS[1]=bucket key, ARGV: capacity, refill_rate(per ms), now_ms, ttl
local capacity = tonumber(ARGV[1])
local rate = tonumber(ARGV[2])
local now = tonumber(ARGV[3])
local tokens = tonumber(redis.call("HGET", KEYS[1], "t") or capacity)
local last = tonumber(redis.call("HGET", KEYS[1], "l") or now)
tokens = math.min(capacity, tokens + (now - last) * rate)
if tokens < 1 then return 0 end
redis.call("HMSET", KEYS[1], "t", tokens - 1, "l", now)
redis.call("EXPIRE", KEYS[1], ARGV[4])
return 1',
                'code_language' => 'bash',
                'difficulty' => 5,
                'topic' => 'system_design.design_tasks',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как спроектировать URL shortener (как bit.ly)?',
                'answer' => 'Классическая system-design задача. Главное — **read-heavy** характер нагрузки (`reads >> writes`) и горячие ссылки.

**Требования:**

- `POST /shorten?url=...` → возвращает `short_code` (6-7 символов)
- `GET /<short_code>` → **301/302 редирект** на оригинал
- Аналитика (clicks, geo, referer)
- Кастомные алиасы

**Оценка нагрузки** (типичная для интервью):

- `100M URLs` в год → `~3 URL/sec` записи
- **`reads >> writes`** — соотношение **100:1 или 1000:1**
- → `~300 redirect/sec`, при пиках до `10k+/sec`

**1. Генерация `short_code`:**

| Подход | Плюсы | Минусы |
|---|---|---|
| **`base62(autoincrement_id)`** | без коллизий, короткие коды | **предсказуемо** (security/scrape) |
| **`hash(url + salt)`** + проверка коллизий | случайные | нужен retry на UNIQUE conflict |
| **`Snowflake ID`** | distributed-friendly | дольше при decode |
| **`UUID v7` → base62** | sortable, distributed | длиннее |

`base62` алфавит — `[0-9a-zA-Z]` = 62 символа. **6 символов** = `62^6 ≈ 56 млрд` комбинаций.

**2. Хранилище:**

- **K/V** (DynamoDB, Redis с persistence) — идеально подходит для `key → value`
- **Sharded RDBMS** по `hash(short_code)` — Postgres / MySQL c шардингом

**Схема:**

```sql
CREATE TABLE links (
    short_code VARCHAR(10) PRIMARY KEY,
    long_url TEXT NOT NULL,
    user_id BIGINT,
    created_at TIMESTAMP,
    expires_at TIMESTAMP NULL,
    custom BOOLEAN DEFAULT FALSE
);
CREATE INDEX idx_user ON links(user_id);
```

**3. Кэширование** — ключевая оптимизация:

- **Чтение в 100-1000 раз чаще записи** → агрессивный **`Redis`** кэш
- **Hit ratio 95%+** — большинство кликов попадают по top-K популярных ссылок
- TTL по популярности (`LRU`/`LFU` стратегия)
- Pre-warm для известных VIP-ссылок

**4. CDN перед редиректом:**

- **Cache `301 Moved Permanently`** ответ на edge
- Hot links отдаются **без захода в origin** — миллисекунды latency
- `Cache-Control: public, max-age=86400`

**5. Аналитика — асинхронно:**

```
Click → Edge log → Kafka topic clicks
                          ↓
                    Stream processor (Flink, Kafka Streams)
                          ↓
                    Aggregates (ClickHouse / BigQuery)
```

- **Не блокируем** редирект
- Агрегаты раз в N минут — счётчики, top countries, referers
- Real-time не критичен (аналитика терпит latency)

**6. Кастомные алиасы:**

- **`UNIQUE`-индекс** на `short_code`
- Обработка conflict → `409 Conflict`
- Blocklist для зарезервированных слов (`admin`, `api`)

**Дополнительно:**

- **`expires_at`** + scheduled job для удаления просроченных
- **Spam/malware detection** — VirusTotal API, blacklist domains
- **Rate limiting** на создание (per-user, per-IP)
- **HTTPS обязательно** — иначе MITM подменит редирект

**Главные архитектурные решения:**

- **301 vs 302** — `301` permanent кэшируется CDN агрессивно, но нельзя поменять url; `302` гибче, но дороже
- **Bigtable/DynamoDB vs RDBMS** — для read-heavy K/V предпочтительнее
- **Shard key = `short_code`** — все запросы по нему',
                'difficulty' => 5,
                'topic' => 'system_design.design_tasks',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как спроектировать новостную ленту (Twitter/Instagram)?',
                'answer' => 'News feed — одна из **самых сложных** задач system design из-за **дисбаланса** «много подписчиков ↔ много постов».

**Два базовых подхода — pull vs push:**

| | **`pull`** (read fan-out) | **`push`** (write fan-out) |
|---|---|---|
| **Когда работает** | при загрузке ленты | при публикации поста |
| **Чтение** | **тяжёлое** (JOIN N таблиц) | **быстрое** — готовая лента |
| **Запись** | **дешёвая** — просто INSERT | **тяжёлая** для звёзд (× N подписчиков) |
| **Свежесть** | всегда актуально | задержка propagation |
| **Хранилище** | мало | много (× N) |
| **Когда брать** | у пользователя **мало фолловингов** | у звёзды **много подписчиков** |

**`pull` (fan-out on read):**

```sql
-- При загрузке ленты:
SELECT * FROM posts
WHERE author_id IN (SELECT followee_id FROM follows WHERE follower_id = ?)
ORDER BY created_at DESC
LIMIT 20;
```

**Плюсы:**

- Дёшево по записи — пост сохраняется **один раз**
- Всегда актуальная лента

**Минусы:**

- **Тяжёлое чтение** — JOIN, sort, N таблиц
- **Не масштабируется** при больших фолловингах
- **Hot read** — каждый клиент дёргает БД при открытии ленты

**`push` (fan-out on write):**

```
Пользователь публикует пост →
  для каждого подписчика → INSERT в его feed
                                ↓
            user_1234.feed: [post_5678, post_5677, ...]
```

**Плюсы:**

- **Быстрое чтение** — лента **уже готова**, просто `GET user.feed`
- Идеально для **mobile-first** (мгновенная подгрузка)

**Минусы:**

- **Тяжёлая запись для звёзд** — Justin Bieber с 100M подписчиков → 100M INSERT на каждый твит
- **Hot write** — может занимать минуты
- **Дубликаты** в хранилище × N подписчиков
- **Удаление поста** требует fan-out delete

**Гибрид — стандарт индустрии:**

| Тип пользователя | Стратегия |
|---|---|
| **Обычный** (≤ 10k фолловеров) | **`push`** при публикации |
| **Celebrity** (> 10k) | **`pull`** — подписчик сам тянет при открытии |

**Поток для гибрида:**

```
GET /feed →
  1. читаем pre-computed feed (push) от обычных авторов
  2. отдельно читаем последние посты звёзд, на которых подписан (pull)
  3. merge + sort by created_at + ranking
```

**Хранилище:**

- **`Redis`** (sorted set по timestamp) — **горячие** ленты последних 7 дней
  - `ZADD feed:user_42 <ts> <post_id>`
  - `ZREVRANGE feed:user_42 0 19`
- **`Cassandra`** / **`HBase`** — архив (column-family по `user_id`)
- **CDN** — медиа-контент (картинки, видео)

**Ранжирование** (Twitter/Instagram давно не chronological):

- ML-модель: engagement prediction, recency, affinity, freshness
- A/B тесты разных моделей
- Edge-case: «ленту неинтересно листать» → diversity penalty

**Доп. сложности:**

- **Block / mute** — фильтрация при чтении
- **Privacy** — закрытые аккаунты, друзья друзей
- **Real-time** — WebSocket для новых постов сверху
- **Pagination** — cursor-based (`max_id`), не offset
- **Cold start** — пустая лента у нового пользователя

**Главный вопрос архитектора:** **«как изменится система, если придёт пользователь со 100M фолловеров?»** — это и есть проверка на понимание fan-out проблемы.',
                'difficulty' => 5,
                'topic' => 'system_design.design_tasks',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как спроектировать систему уведомлений?',
                'answer' => 'Notification service — **критический инфраструктурный компонент** с множеством каналов, языков, провайдеров.

**Архитектура — типичный layered design:**

```
Application events → Notification Service → Channel Adapters → Provider
                            ↓                       ↓             ↓
                    Template Engine          Per-channel queue   Email/SMS/Push
                            ↓
                    Preference Service
```

**Компоненты:**

**1. `Notification Service`** — точка входа:

- Принимает события: `event(\'order.shipped\', userId, payload)`
- **Event-driven через Kafka** — async, не блокирует caller-а
- Дедупликация по `event_id` (idempotency)
- Routing по типу события → каналы

**2. `Template Engine`** — рендеринг:

- Шаблоны на каждый **язык × канал**: `order_shipped.en.email.html`, `order_shipped.ru.sms.txt`
- Подстановка переменных, локализация дат/валют
- Версионирование шаблонов через S3 / git

**3. `Channel Adapters`** — конкретные провайдеры:

| Канал | Провайдеры |
|---|---|
| **Email** | `SES`, `SendGrid`, `Postmark`, `Mailgun` |
| **Push** | **`FCM`** (Android+iOS+Web), **`APNS`** (iOS native) |
| **SMS** | **`Twilio`**, `MessageBird`, `Vonage` |
| **In-app** | `WebSocket` / `Pusher` / `Reverb` |
| **Voice** | Twilio Voice, Plivo |
| **Webhook** | для B2B-клиентов |

**4. Per-channel queue** с rate limit и retry:

- **Email** медленный → отдельная очередь, `concurrency=50`
- **Push** быстрый → отдельная, `concurrency=500`
- **SMS** имеет **жёсткие лимиты** провайдера → отдельная очередь, throttle
- **DLQ** для разбора неудач
- **Exponential backoff** для retry (см. отдельную карточку)

**5. `Preference Service`** — что пользователь хочет получать:

```sql
user_preferences (
    user_id, channel, event_type,
    enabled, frequency, quiet_hours
)
```

- Per-channel opt-out
- **Quiet hours** — не слать push ночью по timezone пользователя
- **Frequency limits** — не больше N уведомлений в день
- **GDPR / unsubscribe link** в email (обязательно по закону)

**6. Tracking & analytics:**

- **States:** `queued` → `sent` → `delivered` → `opened` → `clicked`
- **Webhooks от провайдеров** (SES `bounce`, SendGrid `delivered`)
- **Bounce handling** — soft/hard bounce, marking address invalid
- **Conversion tracking** для transactional vs marketing

**7. Idempotency** — критично:

- **`Idempotency-Key`** в API
- **Dedup по `event_id` + `channel` + `user_id`** в БД
- TTL — `24h` обычно достаточно
- Защищает от **двойной отправки** при retry

**Дополнительные паттерны:**

- **`Digest`** — собрать события за час и отправить **одним email** («10 новых уведомлений»)
- **Smart batching** — не слать сразу, ждать `30s` на возможные дополнительные события
- **A/B тесты** шаблонов — какая тема даёт больше open rate
- **Priority queues** — `OrderShipped` важнее `WeeklyDigest`
- **Multi-region failover** — если SendGrid лёг, перебрасываемся на SES
- **Cost optimization** — SMS дорогой, email дешёвый → fallback chain

**Подводные камни:**

- **Rate limits провайдеров** — Twilio имеет жёсткие квоты, FCM limits per-token
- **Spam complaints** — несоблюдение опт-ин → ESP блокирует домен
- **iOS Quiet Notifications** — APNS приоритеты влияют на доставку
- **Token expiration** — push-токены протухают, нужно убирать невалидные
- **DKIM/SPF/DMARC** для email-доменов — иначе в спам

**Тестирование:**

- **Sandbox-провайдеры** в dev (MailHog, FakeSMS)
- **Test mode** на проде — слать только в whitelist
- **Canary** для шаблонов перед массовой рассылкой',
                'difficulty' => 5,
                'topic' => 'system_design.design_tasks',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как спроектировать систему платежей с идемпотентностью?',
                'answer' => 'Платежи — **самый строгий** случай идемпотентности: **двойное списание = реальные деньги**. Простыми ретраями не решается.

**Главный принцип:** **НИКОГДА не держать DB-транзакцию открытой через сетевой вызов**.

**Алгоритм (8 шагов):**

**1. Клиент передаёт `Idempotency-Key`** в HTTP-заголовке:

- UUID, сгенерированный клиентом
- Уникален для **бизнес-операции**, не для retry

**2. Короткая транзакция №1 — claim idempotency key:**

```sql
BEGIN;
INSERT INTO idempotency_keys (key, status, request_hash)
VALUES (?, \'pending\', ?);   -- UNIQUE constraint на key
COMMIT;
```

- При `UNIQUE violation` → читаем существующую:
  - **`completed`** → возвращаем **сохранённый ответ**
  - **`pending`** → либо `409 Conflict`, либо **poll** шлюза по `transaction_id`
- При успехе → продолжаем

**3. КРИТИЧНО — внешний HTTP-вызов ВНЕ транзакции:**

- **Никогда** не держать DB-tx открытой через сетевой вызов
- **Почему опасно:**
  - **Блокировки висят секундами** на время задержки шлюза
  - **Connection pool забивается** — другие запросы ждут коннект
  - **Deadlock-и** плодятся при долгих tx
  - В Postgres — `idle in transaction` процессы блокируют VACUUM

**4. Вызов платёжного шлюза:**

```http
POST https://api.stripe.com/v1/charges
Idempotency-Key: <same uuid>
Authorization: Bearer ...

{ amount: 1000, currency: usd, source: tok_... }
```

- **Передаём наш `Idempotency-Key` шлюзу** — Stripe/Adyen тоже дедуплицируют у себя
- **Timeout** разумный (`30s`), не блокировать вечно

**5. Короткая транзакция №2 — сохраняем результат:**

```sql
BEGIN;
UPDATE idempotency_keys
SET status = ?, response = ?, provider_id = ?
WHERE key = ?;

INSERT INTO outbox_events (type, payload)
VALUES (\'payment_succeeded\', ?);   -- outbox в той же tx
COMMIT;
```

**6. Сетевой сбой между шагом 4 и 5** — самый сложный случай:

- Шлюз **уже списал**, но мы **не сохранили** результат
- Повторный запрос с тем же ключом увидит **`pending`**
- Логика восстановления:
  - **Polling** — `GET /charges/{transaction_id}` к шлюзу
  - Или вернуть **`202 Accepted`** клиенту с `pending` статусом
  - Background-worker дотягивает статус

**7. Retry-логика клиента** шлёт **тот же `Idempotency-Key`**:

- Дублирование не происходит — ни на нашей стороне (шаг 2), ни у шлюза (шаг 4)
- **Должно быть документировано** в API

**8. Webhook от шлюза** — отдельная история:

- **Проверка подписи** обязательна (HMAC-SHA256)
- **Идемпотентен** — по `event_id` шлюза
- **Eventual consistency** — webhook может прийти **раньше**, чем мы успели сохранить шаг 5

**Дополнительные паттерны:**

- **`outbox pattern`** для событий `payment_succeeded` — атомарно с обновлением статуса (шаг 5)
- **Сага** при многошаговых платежах (резерв → списание → отгрузка)
- **`PCI-DSS`** — карточные данные не хранятся, используется **tokenization** через Stripe/Adyen
- **Reconciliation** — ежедневный сверочный job: наша БД vs шлюз
- **Audit log** — все шаги логируются для compliance

**Подводные камни:**

- **Не использовать `auto_increment`** для `transaction_id` (предсказуемо)
- **TTL `Idempotency-Key`** — обычно `24h`, дальше история архивируется
- **Multi-currency** — округление через `BigDecimal`, не float
- **Refund == отдельная** идемпотентная операция со своим ключом
- **Webhook + sync response race** — кто пришёл первым выигрывает

**Метрики, которые алертить:**

- **`pending` платежи > 5 минут** — что-то застряло
- **Расхождение** с reconciliation отчётом шлюза
- **Двойное списание** (баг ловится сверкой)
- **Failed payments rate** — рост = проблема со шлюзом или картами',
                'difficulty' => 5,
                'topic' => 'system_design.design_tasks',
            ],
        ];
    }
}
