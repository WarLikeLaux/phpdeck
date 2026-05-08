<?php

namespace Database\Seeders\Data\Categories\SystemDesign;

class DesignTasks
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example: ?string, code_language: ?string, difficulty: int, topic: string}>
     */
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
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 5,
                'topic' => 'system_design.design_tasks',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как спроектировать новостную ленту (Twitter/Instagram)?',
                'answer' => 'Два подхода: 1) Pull (на чтение) - при загрузке ленты делаем запрос "посты от моих фолловингов, последние, отсортировано" - просто, но тяжёлое чтение. 2) Push (fan-out на запись) - при публикации поста копируем его во все ленты подписчиков - быстрое чтение, но тяжёлая запись (особенно у звёзд с миллионами фолловеров). Гибрид: push для обычных, pull для celebrity-аккаунтов. Хранилище: Redis для горячих лент, Cassandra для архива.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 5,
                'topic' => 'system_design.design_tasks',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как спроектировать систему уведомлений?',
                'answer' => 'Компоненты: 1) Notification Service принимает запросы (event-driven через Kafka). 2) Template engine рендерит шаблон под язык/канал. 3) Channel adapters - email (SES, SendGrid), push (FCM, APNS), SMS (Twilio), in-app (WebSocket). 4) Очередь на канал с rate limit и retry. 5) Preference Service - что юзер хочет получать. 6) Tracking - delivered/opened/clicked. 7) Idempotency для предотвращения дублей. Throttling: не спамить в quiet hours.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 5,
                'topic' => 'system_design.design_tasks',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как спроектировать систему платежей с идемпотентностью?',
                'answer' => '1) Клиент передаёт Idempotency-Key. 2) КОРОТКАЯ транзакция: пытаемся INSERT запись по ключу в idempotency-таблицу с UNIQUE-индексом и status=pending. На violation UNIQUE читаем существующую: если completed - возвращаем сохранённый ответ, если pending - либо статус "in progress", либо poll-им шлюз по сохранённому transaction_id. КОММИТ. 3) КРИТИЧНО: внешний HTTP-вызов к шлюзу делается ПОСЛЕ коммита, ВНЕ транзакции. Никогда не держите DB-транзакцию открытой через сетевой вызов: блокировки висят секундами на время задержки шлюза, забивается connection pool, плодятся deadlock-и. 4) Вызываем платёжный шлюз. 5) НОВАЯ короткая транзакция: обновляем запись результатом (status=completed/failed + provider_id + payload). Сетевой сбой между 4 и 5 - повторный запрос с тем же ключом увидит pending, и реализация либо poll-ит шлюз по сохранённому transaction_id, либо возвращает текущий pending-статус клиенту. 6) Retry-логика клиента шлёт тот же Idempotency-Key - повторно шлюз не дёргается. 7) Outbox pattern для событий "payment_succeeded" - запись в outbox в ТОЙ ЖЕ короткой транзакции, что и обновление статуса. 8) Webhook от шлюза проверяется по подписи и тоже идемпотентен (по Idempotency-Key или transaction_id шлюза).',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 5,
                'topic' => 'system_design.design_tasks',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое throttling и как отличается от rate limiting?',
                'answer' => 'Часто используются как синонимы, но строго: rate limiting - жёсткий лимит "не больше N запросов в минуту", при превышении 429. Throttling - замедление: "запросы выше лимита обрабатываются медленнее, в очереди". Простыми словами: rate limit - "не пустим", throttling - "пустим, но в порядке очереди". Throttling мягче для пользователя, но требует буфер. На практике обычно делают rate limiting на API gateway.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 3,
                'topic' => 'system_design.design_tasks',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое APM (Application Performance Monitoring)?',
                'answer' => 'APM - инструменты для мониторинга производительности приложения в проде. Простыми словами: рентген для приложения - видно где тормозит, какой SQL медленный, где исключения, какой endpoint самый горячий. Включает: tracing запросов, профилирование, алерты, дашборды, error tracking. Инструменты: New Relic, Datadog, Sentry, Elastic APM, Laravel Telescope/Pulse. Без APM в большом проде ты слепой.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 3,
                'topic' => 'system_design.design_tasks',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое connection pooling и зачем нужен?',
                'answer' => 'Connection pool - готовый набор открытых коннектов к БД, переиспользуемых между запросами. Простыми словами: вместо того чтобы заново звонить и здороваться при каждом обращении - держим телефон поднятым. Открытие коннекта к Postgres дорого (TCP+TLS+auth = 10-50мс). Pool: 20-50 коннектов, при запросе берём свободный, после возвращаем в пул. Внешние пулы для PHP-FPM (где коннект на запрос): pgbouncer, RDS Proxy.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 3,
                'topic' => 'system_design.design_tasks',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое service mesh?',
                'answer' => 'Service mesh - инфраструктурный слой для взаимодействия микросервисов. Прокси (sidecar) рядом с каждым сервисом перехватывает весь сетевой трафик и обеспечивает: retry, circuit breaker, mTLS, tracing, traffic splitting (canary), rate limiting - без изменений в коде сервисов. Простыми словами: общий "сетевой стек" для всех сервисов вынесен в инфраструктуру. Реализации: Istio, Linkerd, Consul Connect. Sidecar обычно Envoy.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 5,
                'topic' => 'system_design.design_tasks',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое event-driven architecture?',
                'answer' => 'Event-driven architecture - сервисы общаются через события вместо прямых вызовов. Сервис A публикует "OrderCreated" в шину (Kafka, RabbitMQ), сервисы B, C, D подписываются и реагируют каждый по-своему. Простыми словами: вместо телефонных звонков - публикация новостей в газете. Плюсы: loose coupling, легко добавить нового подписчика, асинхронность. Минусы: сложнее дебажить, eventual consistency, нужна хорошая обсервабилити для трассировки.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 4,
                'topic' => 'system_design.design_tasks',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Content Security Policy (CSP)?',
                'answer' => 'CSP - HTTP-заголовок, говорящий браузеру откуда можно загружать ресурсы (JS, CSS, картинки, fetch). Защита от XSS: даже если злоумышленник вставил скрипт, браузер откажется его выполнять, если источник не разрешён. Например: "JS только из своего домена и cdn.jsdelivr.net". Очень эффективно, но требует настройки и тестирования - можно сломать сайт.',
                'code_example' => 'Content-Security-Policy:
  default-src \'self\';
  script-src \'self\' https://cdn.jsdelivr.net;
  style-src \'self\' \'unsafe-inline\';
  img-src \'self\' data: https:;
  connect-src \'self\' https://api.example.com;',
                'code_language' => 'bash',
                'difficulty' => 4,
                'topic' => 'system_design.design_tasks',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'В чём разница между monitoring и observability?',
                'answer' => 'Monitoring - заранее знаем что мерить и алертим на отклонения (CPU>80%, latency>500ms). Отвечает на вопрос "система здорова?". Observability - возможность задать любой вопрос системе и получить ответ из её сигналов. Отвечает на "почему так?". Простыми словами: monitoring - "горит ли красная лампочка", observability - "что именно сломалось и где". Одно дополняет другое: monitoring алертит, observability помогает диагностировать.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 4,
                'topic' => 'system_design.design_tasks',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'В чём разница между cache-aside, write-through и write-behind?',
                'answer' => 'Cache-aside: приложение само читает кэш, при промахе читает БД и заполняет кэш. Запись напрямую в БД, кэш инвалидируется. Простота и надёжность, но возможна stale data при гонке. Write-through: запись идёт через кэш в БД синхронно - кэш всегда консистентен, но запись медленнее. Write-behind: запись в кэш, асинхронно flush в БД - самая быстрая, но при падении кэша теряются данные. Cache-aside - дефолт для веба; write-behind - для high-throughput с допустимой потерей.',
                'code_example' => '<?php
// cache-aside на Laravel
$user = Cache::remember("user:$id", 3600, fn() => User::find($id));
// при обновлении
$user->save();
Cache::forget("user:$id");',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'system_design.design_tasks',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое idempotency key и как реализовать его в платежах?',
                'answer' => 'Idempotency key - уникальный токен от клиента, гарантирующий, что повторный POST не выполнит операцию дважды. Сервер хранит маппинг ключ → результат с TTL (например, 24ч). При повторе с тем же ключом возвращает кэшированный ответ, не вызывая внешний gateway. Реализация: уникальный индекс по ключу + транзакция, либо Redis SET NX EX. Полезно для платежей, заказов и любых операций с сетевыми ретраями.',
                'code_example' => '<?php
$key = $request->header("Idempotency-Key");
$result = Cache::lock("idemp:$key", 60)->block(5, function () use ($key) {
    return Cache::remember("idemp-result:$key", 86400, function () {
        return $this->payment->charge(...);
    });
});',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'system_design.design_tasks',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Чем at-least-once отличается от exactly-once в очередях и достижим ли exactly-once на практике?',
                'answer' => 'At-least-once: сообщение точно доставится один или больше раз - стандарт в SQS, Kafka, RabbitMQ. Exactly-once delivery в чистом виде в распределённой системе невозможен из-за неотличимости "сообщение потерялось" от "ack потерялся" (а в asynchronous-моделях ещё и из-за FLP-impossibility для consensus с failures). На практике достигается комбинацией at-least-once + идемпотентного потребителя (dedup по message-id) - это называется "effectively-once". Kafka даёт transactional EOS внутри своих топиков, но при выходе наружу ответственность ложится на consumer.',
                'code_example' => '<?php
// идемпотентный consumer
public function handle(Message $m): void {
    if (ProcessedMessage::where("id", $m->id)->exists()) return;
    DB::transaction(function () use ($m) {
        ProcessedMessage::create(["id" => $m->id]);
        $this->doWork($m);
    });
}',
                'code_language' => 'php',
                'difficulty' => 5,
                'topic' => 'system_design.design_tasks',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Когда event sourcing уместен и какие у него подводные камни?',
                'answer' => 'Event sourcing: вместо текущего состояния хранится последовательность событий; состояние получается их сверткой. Уместен в доменах с богатой историей (банкинг, аудит, медицина), для аналитики "почему" и для восстановления состояния на любую точку. Минусы: сложность, проекции/read-models надо строить отдельно, миграции схемы событий тяжёлые (нужен upcasting), нельзя удалять события без compensating event-а - конфликт с GDPR требует crypto-shredding.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 5,
                'topic' => 'system_design.design_tasks',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Зачем нужна CDN перед приложением и какие нюансы при работе с динамикой?',
                'answer' => 'CDN кэширует статику (изображения, JS/CSS) близко к пользователю - снижает latency и нагрузку на origin. Для динамики используют edge-кэш с коротким TTL и cache-key по нормализованному URL без cookies. Stale-while-revalidate отдаёт чуть устаревший ответ, пока на фоне обновляется. Surrogate-Control + tag-based purge (Fastly, Cloudflare) позволяют точечно инвалидировать. Важно: avoid Vary: Cookie без необходимости - он рушит cache-hit ratio.',
                'code_example' => 'Cache-Control: public, max-age=60, s-maxage=300, stale-while-revalidate=600
Surrogate-Key: user-123 product-42',
                'code_language' => 'bash',
                'difficulty' => 4,
                'topic' => 'system_design.design_tasks',
            ],
        ];
    }
}
