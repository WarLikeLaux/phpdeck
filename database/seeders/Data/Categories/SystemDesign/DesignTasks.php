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
                'answer' => 'Алгоритмы: 1) Fixed window (счётчик за минуту, прост, но всплеск на границах окон), 2) Sliding window log (хранит timestamp каждого запроса, точно но дорого по памяти), 3) Sliding window counter (компромисс), 4) Token bucket (поддерживает всплески, классика), 5) Leaky bucket. Хранилище: Redis с атомарным INCR/EXPIRE и Lua-скриптом. Ключ: user_id или IP. При превышении - 429 Too Many Requests + Retry-After + X-RateLimit-* headers.',
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
                'answer' => '1) Генерация: base62 от автоинкремента (без коллизий, но предсказуемо) или хеш URL+salt с проверкой уникальности. 2) Хранилище: K/V (DynamoDB) или sharded RDBMS по hash(short_code). 3) Чтение во много раз чаще записи - агрессивный кэш в Redis с hit ratio 95%+. 4) CDN перед редиректом для статичных популярных ссылок. 5) Аналитика - асинхронно через Kafka, агрегаты раз в N минут. 6) Кастомные алиасы - UNIQUE-индекс и обработка conflict.',
                'difficulty' => 5,
                'topic' => 'system_design.design_tasks',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как спроектировать новостную ленту (Twitter/Instagram)?',
                'answer' => 'Два подхода: 1) Pull (на чтение) - при загрузке ленты делаем запрос "посты от моих фолловингов, последние, отсортировано" - просто, но тяжёлое чтение. 2) Push (fan-out на запись) - при публикации поста копируем его во все ленты подписчиков - быстрое чтение, но тяжёлая запись (особенно у звёзд с миллионами фолловеров). Гибрид: push для обычных, pull для celebrity-аккаунтов. Хранилище: Redis для горячих лент, Cassandra для архива.',
                'difficulty' => 5,
                'topic' => 'system_design.design_tasks',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как спроектировать систему уведомлений?',
                'answer' => 'Компоненты: 1) Notification Service принимает запросы (event-driven через Kafka). 2) Template engine рендерит шаблон под язык/канал. 3) Channel adapters - email (SES, SendGrid), push (FCM, APNS), SMS (Twilio), in-app (WebSocket). 4) Очередь на канал с rate limit и retry. 5) Preference Service - что юзер хочет получать. 6) Tracking - delivered/opened/clicked. 7) Idempotency для предотвращения дублей. Throttling: не спамить в quiet hours.',
                'difficulty' => 5,
                'topic' => 'system_design.design_tasks',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как спроектировать систему платежей с идемпотентностью?',
                'answer' => '1) Клиент передаёт Idempotency-Key. 2) КОРОТКАЯ транзакция: пытаемся INSERT запись по ключу в idempotency-таблицу с UNIQUE-индексом и status=pending. На violation UNIQUE читаем существующую: если completed - возвращаем сохранённый ответ, если pending - либо статус "in progress", либо poll-им шлюз по сохранённому transaction_id. КОММИТ. 3) КРИТИЧНО: внешний HTTP-вызов к шлюзу делается ПОСЛЕ коммита, ВНЕ транзакции. Никогда не держите DB-транзакцию открытой через сетевой вызов: блокировки висят секундами на время задержки шлюза, забивается connection pool, плодятся deadlock-и. 4) Вызываем платёжный шлюз. 5) НОВАЯ короткая транзакция: обновляем запись результатом (status=completed/failed + provider_id + payload). Сетевой сбой между 4 и 5 - повторный запрос с тем же ключом увидит pending, и реализация либо poll-ит шлюз по сохранённому transaction_id, либо возвращает текущий pending-статус клиенту. 6) Retry-логика клиента шлёт тот же Idempotency-Key - повторно шлюз не дёргается. 7) Outbox pattern для событий "payment_succeeded" - запись в outbox в ТОЙ ЖЕ короткой транзакции, что и обновление статуса. 8) Webhook от шлюза проверяется по подписи и тоже идемпотентен (по Idempotency-Key или transaction_id шлюза).',
                'difficulty' => 5,
                'topic' => 'system_design.design_tasks',
            ],
        ];
    }
}
