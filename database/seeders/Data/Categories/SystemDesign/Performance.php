<?php

namespace Database\Seeders\Data\Categories\SystemDesign;

class Performance
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Архитектура систем',
                'question' => 'В чём разница между latency и throughput?',
                'answer' => 'Две **разные** метрики производительности — путают часто, но **оптимизация одной не означает улучшение другой**.

| Метрика | Что измеряет | Единицы |
|---|---|---|
| **Latency** | задержка **одной операции** — сколько ждать ответа на один запрос | `ms`, `μs`, `s` |
| **Throughput** | **пропускная способность** — сколько запросов в секунду система обрабатывает | `RPS`, `TPS`, `req/s`, `MB/s` |

**Аналогия с дорогами:**
- **Автобан с одной полосой и скоростью 100 км/ч** → **низкая latency** (мало ждать своей машины), но **низкий throughput** (мало машин за час проедет).
- **Дорога в 10 полос с пробкой 30 км/ч** → **высокая latency** (каждая машина едет медленно), но **высокий throughput** (много машин одновременно).

**Связь через закон Литтла:**
- **`concurrency = throughput × latency`** (в установившемся режиме).
- Нельзя бесконечно увеличивать concurrency без роста throughput — latency пропорционально вырастет.

**Что важно для какой задачи:**
- **Игры реального времени**, торговля акций, видео-чаты → **latency** (тут p99 < 50ms).
- **Batch-задачи** (ETL, обработка логов, машинное обучение) → **throughput** (миллионы записей в час).
- **Веб-API** обычно — баланс: p99 latency < 500ms + throughput, соответствующий нагрузке.

**Подвох:**
- Метрика **«среднее»** обманывает — нужны **перцентили** (p50, p95, p99) для honest-измерения latency.
- Высокий throughput **не означает** низкую latency — может быть очередь.
- Низкая latency на пустом сервере **не масштабируется**: добавь concurrency → latency растёт нелинейно (queueing).',
                'difficulty' => 3,
                'topic' => 'system_design.performance',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое перцентили P50, P95, P99 в метриках?',
                'answer' => '**Перцентили** — характеристика **распределения** значений, а не среднее. **P95** = «95% запросов **быстрее** этого значения, 5% медленнее».

**Ключевые перцентили:**

| Перцентиль | Что показывает | Где смотреть |
|---|---|---|
| **P50** (медиана) | типичный пользователь | сравнить со средним — если расходится, распределение скошено |
| **P95** | граница «обычно быстро» | основная SRE-метрика |
| **P99** | хвост — у 1% юзеров тормозит | SLO «99% запросов под 500ms» |
| **P99.9** | острый хвост — крупные системы | критично при микросервисах |

**Почему среднее обманывает:**
- Среднее `avg = 100ms`, **`P99 = 2s`** → у **1% пользователей** всё тормозит, но среднее это **скрывает**.
- Skewed-распределения (long tail) типичны для latency — медиана и P99 могут отличаться на **порядок**.

**Почему именно P99 критичен в распределённых системах:**
- Один пользовательский запрос порождает **десятки** внутренних вызовов.
- Итоговый ответ ограничен **самым медленным** звеном.
- Если у каждого микросервиса P99 = 1% медленных запросов, то в системе из 100 вызовов **уже у 63% юзеров** будет хотя бы один медленный звено (`1 - 0.99^100`).

**SLO задают по перцентилям:**
- **«99.9% запросов отвечают за < 200ms»** — это P99.9 ≤ 200ms.
- Цель SRE — **снижать tail**, не среднее.

**Подвох в агрегации:**
- **Перцентили не агрегируются** — нельзя сложить P95 с разных машин и поделить.
- Правильный путь — **histograms** (Prometheus) с buckets → `histogram_quantile(0.95, ...)`.
- Усреднение «P95 за 5 минут» в Datadog/Grafana может **врать** на бесткреционных распределениях.

**Связанные техники mitigation:**
- **Hedged requests** — параллельно к двум репликам, берём первый ответ.
- **Request coalescing** — объединение одинаковых параллельных вызовов.
- **Deadline propagation**, **bulkhead pools**, отдельные пулы для тяжёлых запросов.',
                'difficulty' => 3,
                'topic' => 'system_design.performance',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое observability и три её столпа?',
                'answer' => 'Observability - возможность понять что происходит внутри системы по её внешним сигналам. Три столпа: 1) Logs - дискретные записи событий ("user 42 logged in"), 2) Metrics - числовые ряды во времени (RPS, CPU, latency), 3) Traces - путь запроса через систему (через какие сервисы прошёл, где сколько времени). Monitoring отвечает "система работает?", observability - "почему так работает?". Инструменты: Prometheus+Grafana, OpenTelemetry, Jaeger.',
                'difficulty' => 4,
                'topic' => 'system_design.performance',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое distributed tracing?',
                'answer' => 'Distributed tracing - отслеживание прохождения одного запроса через множество микросервисов. Каждый запрос получает trace_id, каждый шаг - span_id с родителем. На выходе видно: запрос пошёл в gateway → auth-service (5мс) → user-service (20мс) → db (15мс). Сразу видно где тормозит. Стандарт - W3C Trace Context (заголовки traceparent/tracestate), инструменты: Jaeger, Zipkin, OpenTelemetry, Datadog APM. Часто применяют sampling (1-10% трейсов) - storage и overhead для всех 100% дорог.',
                'difficulty' => 4,
                'topic' => 'system_design.performance',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Чем вертикальное масштабирование отличается от горизонтального?',
                'answer' => 'Два **разных подхода** к увеличению мощности системы.

| Аспект | **Vertical (scale up)** | **Horizontal (scale out)** |
|---|---|---|
| Что делаем | **больше мощности одной машине**: CPU, RAM, NVMe | **больше машин**, распределяем через **load balancer** |
| Лимит | потолок железа (`128 cores`, `1.5TB RAM` — дорого) | **почти безлимитно** |
| Точка отказа | **одна** машина — упала, всё легло | **отказоустойчиво** — упал узел, остальные работают |
| Цена | растёт **нелинейно**: TOP-CPU стоит непропорционально дорого | **линейно** — N машин стоят как N |
| Сложность кода | **нет изменений** — апгрейд железа прозрачен | требует **stateless** приложений, общих хранилищ |
| Простоев нужно | **есть** (downtime на reboot после апгрейда) | **нет** (rolling deployment) |

**Что нужно для горизонтального масштабирования:**
- **Stateless** приложение — никакого `$_SESSION` в файлах сервера.
- **Sessions в общем хранилище** (Redis, БД).
- **Распределённый кэш** (Redis cluster, Memcached).
- **БД-репликация** (read replicas) или **шардирование**.
- **Sticky-балансировка** — только если нет общего хранилища (см. отдельную карточку).
- Файлы — в **S3 / object storage**, не в локальной `storage/`.

**Правило выбора:**
- **Stateless web-слой** → **горизонтально** (легко добавлять инстансы PHP-FPM / Octane).
- **БД** → **вертикально до предела** (один primary), потом **read replicas**, потом **sharding** (дорого по сложности).
- **Кэши** → горизонтально (Redis Cluster).
- **Файлы** → horizontal через S3.

**Сloud-native тяготеет к horizontal:**
- Kubernetes / autoscaling работает именно на этом — `HPA` (Horizontal Pod Autoscaler) добавляет/убирает поды по CPU/RPS.
- Cloud LB managed.

**Подвох:** horizontal **не магия** — Amdahl’s law: при наличии последовательной части ускорение ограничено. БД, общий cache, locks → bottleneck даже с 100 инстансами app.',
                'difficulty' => 3,
                'topic' => 'system_design.performance',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Какие алгоритмы балансировки нагрузки бывают?',
                'answer' => 'Алгоритм балансировщика определяет, **по какому правилу** выбирается backend-узел.

**Основные алгоритмы:**

| Алгоритм | Как работает | Плюсы | Минусы |
|---|---|---|---|
| **Round Robin** | по очереди, по кругу | прост | **не учитывает нагрузку** узлов |
| **Weighted Round Robin** | с **весами** — мощные получают больше | учитывает разное железо | веса задаются вручную |
| **Least Connections** | на узел с **минимумом активных коннектов** | хорош при разной длительности запросов | дороже считать |
| **Least Response Time** | на **самый быстрый** | реагирует на просадки | требует метрик latency |
| **IP Hash** | `hash(client_ip) % N` | sticky без cookie | проблемы с **NAT/CGNAT** (много клиентов на одном узле) |
| **Consistent Hash** | hash-ring, добавление узла → перемещается только 1/N ключей | удобно для кэш-нод | сложнее в реализации |
| **Power of Two Choices** | случайные **2**, шлём на менее загруженный | **близко к Least Connections при O(1)** | требует знание load узлов |
| **Random** | случайный | проще не бывает | возможны hot-spots |

**L4 vs L7:**

| Уровень | Примеры | Скорость | Что умеет |
|---|---|---|---|
| **L4** (TCP/UDP) | **HAProxy**, **AWS NLB**, IPVS | **быстрее** (не парсит HTTP) | базовая балансировка, sticky по IP |
| **L7** (HTTP) | **Nginx**, **Envoy**, **AWS ALB**, Traefik | чуть медленнее | **routing по path/host**, **canary**, **WAF**, sticky по cookie |

**Когда что брать:**
- **Web / API** — обычно **L7** + **Least Connections** или **Power of Two**.
- **TCP-сервисы** (БД, gRPC, WebSocket) — **L4** + Least Connections.
- **CDN / static** — Consistent Hash для cache-locality.
- **При sticky session** — IP Hash / Cookie-based.

**Подвох:**
- **Slow start** — новый узел получает сразу полную нагрузку, может упасть. Решение — **gradual warmup** (Nginx `slow_start=30s`).
- **Long-polling / WebSocket** — Least Connections медленно «увидит» долго висящие соединения, нужна осторожность.',
                'code_example' => 'upstream backend {
    least_conn;
    server backend1.example.com weight=3 max_fails=2 fail_timeout=10s;
    server backend2.example.com weight=1;
    server backend3.example.com backup;
    keepalive 32;
}',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.performance',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое N+1 запросы и как их избегать?',
                'answer' => '**N+1** — анти-паттерн: **1 запрос** за списком + **N запросов** за связанными данными для каждого элемента.

**Пример в Laravel:**
```
$posts = Post::all();              // 1 SELECT * FROM posts
foreach ($posts as $p) {
    echo $p->author->name;         // N раз: SELECT * FROM users WHERE id = ?
}
```
На **1000 постов** — **1001 запрос** вместо 2.

**Решение — eager loading:**
```
$posts = Post::with("author")->get();
// SELECT * FROM posts;
// SELECT * FROM users WHERE id IN (1,2,3,...,1000);  ← один батч
```
Всего **2 запроса** независимо от N.

**Варианты eager loading в Eloquent:**
- **`->with("rel")`** — загрузить отношение сразу.
- **`->load("rel")`** — после получения коллекции, lazy eager (если знаешь, что точно понадобится).
- **`->with(["author", "comments.user"])`** — вложенные.
- **`->with("author:id,name")`** — выбрать **только нужные** колонки.
- **`->withCount("comments")`** — счётчик через подзапрос вместо подгрузки коллекции.

**Диагностика:**
- **Laravel Debugbar** — счётчик запросов в footer-баре.
- **Telescope** / **Pulse** — production-friendly наблюдение.
- **`DB::listen(...)`** — программный лог запросов в тестах.

**Защита от случайных lazy load:**
- **`Model::preventLazyLoading(! app()->isProduction())`** в `AppServiceProvider` — в **local/test** среде любой lazy выбрасывает **`LazyLoadingViolationException`**.
- **В проде НЕ throw** — иначе один забытый `with()` уронит юзеру 500.
- Мягкий вариант для прода: **`Model::handleLazyLoadingViolationUsing(fn ($m, $r) => Log::warning(...))`** — пишет в Sentry, не валит запрос.

**Аналогии в других ORM:**
- **Doctrine** — `setFetchMode(FetchMode::EAGER)`.
- **GraphQL** — **DataLoader** (батчит и дедуплицирует загрузки в рамках одного запроса).
- **Active Record (Rails)** — `includes(:author)`.',
                'code_example' => '<?php
// плохо: N+1
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->author->name; // SELECT для каждого
}

// хорошо: eager loading
$posts = Post::with("author")->get();
// SELECT * FROM posts;
// SELECT * FROM users WHERE id IN (1,2,3,...);

// в проде: ловим lazy loading на старте
Model::preventLazyLoading(! app()->isProduction());',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'system_design.performance',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как закон Литтла связывает latency, throughput и concurrency?',
                'answer' => 'Закон Литтла утверждает, что в установившемся режиме среднее число одновременно обрабатываемых запросов равно произведению пропускной способности и средней задержки: Concurrency = Throughput × Latency. Из него следует, что нельзя бесконечно наращивать concurrency без роста throughput — иначе latency пропорционально вырастет. Закон используют для расчёта размеров пула воркеров, соединений к БД и оценки, выдержит ли система целевой RPS при заданной задержке.',
                'difficulty' => 4,
                'topic' => 'system_design.performance',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое tail latency (P99/P99.9) и почему она критична в распределённых системах?',
                'answer' => 'Tail latency — это задержка верхних перцентилей (P99, P99.9), то есть время отклика самых медленных запросов, а не среднее или медианное. В крупной системе один пользовательский запрос порождает десятки или сотни внутренних вызовов, и итоговый ответ ограничен самым медленным звеном, поэтому даже 1% медленных запросов в одном сервисе превращается в заметную долю медленных пользовательских запросов. Поэтому SLO принято задавать именно по tail-перцентилям, а не по среднему.',
                'difficulty' => 4,
                'topic' => 'system_design.performance',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Какие техники применяют для снижения tail latency в распределённой системе?',
                'answer' => 'Hedged requests — отправка одного и того же запроса нескольким репликам и использование ответа первой ответившей. Request coalescing объединяет одинаковые параллельные запросы в один вызов к бэкенду. Deadline propagation передаёт суммарный дедлайн всем подсервисам, чтобы они отменялись досрочно. Jitter в плановых задачах размазывает thundering herd. Latency-aware балансировка избегает временно медленных узлов. Дополнительно помогают warm-up пулов, отдельные пулы для тяжёлых запросов (bulkhead) и ограничение длины очередей.',
                'difficulty' => 4,
                'topic' => 'system_design.performance',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Медленный GET-эндпоинт возвращает много записей: как ускорить, не трогая формат ответа?',
                'answer' => 'Сначала смотрим план: добавляем покрывающий индекс, чтобы выборка шла без обращения к heap, и переписываем offset-пагинацию на keyset-пагинацию по индексированному ключу. Дальше — материализованное представление или денормализованная таблица под этот конкретный read-сценарий, чтобы убрать JOIN на горячем пути. Поверх — кэш ответов в Redis с инвалидацией по событию изменения и read-replica для разгрузки мастера. Тело ответа при этом не меняется, меняется только то, откуда и как мы достаём данные.',
                'difficulty' => 4,
                'topic' => 'system_design.performance',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как ускорить массовую вставку миллионов строк в большую таблицу?',
                'answer' => 'Главный приём — bulk insert: одной командой INSERT с пачкой VALUES вместо миллиона отдельных запросов, обёрнутый в транзакцию, чтобы не fsync-ить каждую строку. Для совсем больших объёмов в MySQL берут LOAD DATA INFILE, в Postgres — COPY, они на порядок быстрее обычного INSERT. На время загрузки имеет смысл отключить вторичные индексы и foreign key checks, а потом построить индексы заново — это дешевле, чем поддерживать их инкрементально. Если таблица партиционирована, льют сразу в новую партицию, а потом её ATTACH к основной таблице.',
                'difficulty' => 4,
                'topic' => 'system_design.performance',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как правильно обрабатывать deadlock в высоконагруженном приложении?',
                'answer' => 'Deadlock на уровне БД — это не баг, а штатная ситуация под нагрузкой, и приложение должно её ожидать. Базовая защита — короткие транзакции и обращение к таблицам всегда в одном и том же порядке, чтобы взаимная блокировка просто не возникала. Снимать deadlock errno (1213 в MySQL, 40P01 в Postgres) надо retry-логикой с exponential backoff и небольшим jitter, а не показом 500 пользователю. Снизить вероятность помогают точечные блокировки (SELECT ... FOR UPDATE по конкретному id) и понижение уровня изоляции там, где допустимо.',
                'difficulty' => 4,
                'topic' => 'system_design.performance',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое throttling и как отличается от rate limiting?',
                'answer' => 'Часто **используют как синонимы**, но строго это **разные стратегии**.

| | **Rate Limiting** | **Throttling** |
|---|---|---|
| Поведение | **жёсткий отказ** при превышении лимита | **замедление**, обработка в очереди |
| HTTP-ответ | **`429 Too Many Requests`** | `200`, но с задержкой |
| UX | «не пустим» | «пустим, но в очередь» |
| Буфер | не нужен | **нужен** (queue) |
| Сложность | проще | сложнее (нужна очередь и backpressure) |

**Rate Limiting — реализации алгоритмов:**
- **Fixed Window** — `N` запросов в окно `1m`. Дёшево, но **burst** на границе окна (2N в одну секунду).
- **Sliding Window** — учитывает прошлое окно с весом. Точнее.
- **Token Bucket** — bucket пополняется со скоростью `R/s`, ёмкость `B` — позволяет короткие burst-ы.
- **Leaky Bucket** — фиксированная скорость «утечки», превышение **дропается**.

**Throttling — как реализуется:**
- **Очередь** перед обработчиком (`SQS`, `RabbitMQ`).
- **Concurrency limit** — N одновременно выполняющихся запросов, остальные ждут.
- **Adaptive throttling** — Google SRE-паттерн с динамическим клиентским коэффициентом дропа.

**Где применяется на практике:**
- **API Gateway** (Kong, AWS API Gateway, nginx) — **rate limiting** на public API (защита от DoS, контроль трафика).
- **Backend** — **throttling** для тяжёлых операций (отчёты, ML-инференс).
- **External SDK** — клиентский throttling, чтобы не превысить лимит чужого API.
- **Laravel** — `RateLimiter::for("api", fn ($r) => Limit::perMinute(60))`.

**HTTP-этикет:**
- **`429`** ответ должен содержать **`Retry-After: 60`** заголовок.
- **`X-RateLimit-Limit`**, **`X-RateLimit-Remaining`**, **`X-RateLimit-Reset`** — клиенту видно, сколько осталось.

**Подвох:** при микросервисах — лимит **должен быть распределённым** (Redis counter), иначе каждый инстанс пускает по своему счёту → суммарно в N раз больше лимита.',
                'difficulty' => 3,
                'topic' => 'system_design.performance',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое APM (Application Performance Monitoring)?',
                'answer' => '**APM (Application Performance Monitoring)** — инструменты для **мониторинга производительности приложения в проде**. «Рентген» для приложения.

**Что показывает APM:**
- **Latency-разбивка** по endpoint-ам (p50/p95/p99 на `GET /users/{id}`).
- **Какой SQL** медленный, сколько раз вызвается.
- **Throughput** — RPS, queue depth.
- **Где исключения** — stack trace, частота, regressed-deploy.
- **Самый горячий endpoint** — куда уходит CPU.
- **External API** — latency Stripe / Slack / S3.
- **Memory / GC** — утечки, allocation patterns.

**Из чего состоит APM:**

| Компонент | Что делает |
|---|---|
| **Tracing** | путь запроса через сервисы (distributed tracing) |
| **Profiling** | где в коде CPU/memory тратится (continuous profiling) |
| **Алерты** | уведомление при `error_rate > 1%`, `p99 > 1s` |
| **Дашборды** | визуализация trends, корреляций |
| **Error tracking** | группировка исключений, breadcrumbs |
| **Real User Monitoring (RUM)** | метрики с фронта (page load, click-to-action) |

**Популярные инструменты:**
- **New Relic** — full APM, исторический лидер.
- **Datadog** — APM + logs + infra в одном.
- **Sentry** — error tracking + performance.
- **Elastic APM** — open source / on-premise.
- **OpenTelemetry** + Jaeger / Tempo — open standard.
- **Laravel Telescope** — dev-only debug-tool.
- **Laravel Pulse** — production-friendly мониторинг для Laravel-стека.

**APM vs обычное логирование:**
- Логи отвечают на «что произошло» — текстовые события.
- **APM** отвечает на «**почему медленно**» — структурированные метрики и trace-ы с временной разбивкой.

**Без APM в большом проде ты слепой** — проблемы будут, но искать их «по логам» = часы вместо минут.

**Подвох:** APM-агенты добавляют **overhead** (1-5% CPU/memory). Sampling (1-10% трейсов) — компромисс между точностью и стоимостью.',
                'difficulty' => 3,
                'topic' => 'system_design.performance',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое connection pooling и зачем нужен?',
                'answer' => '**Connection pool** — **готовый набор открытых коннектов** к БД, **переиспользуемых** между запросами. Вместо того чтобы каждый раз заново «звонить и здороваться» — **держим телефон поднятым**.

**Что дорого в открытии коннекта к Postgres / MySQL:**
- **TCP-handshake** (3-way): ~RTT.
- **TLS-handshake**: 1-2 RTT для **TLS 1.3**, 2 RTT для TLS 1.2.
- **Auth**: пароль / certificate проверка.
- **Backend fork** в Postgres — `~5-10MB` RAM на коннект, **слот в `max_connections`**.

В сумме — **10-50ms на открытие**. На горячем endpoint это **в 10 раз** дольше, чем сам запрос.

**Как работает pool:**
- Стартом приложение открывает **N коннектов** (обычно 20-50) и держит их **в памяти**.
- Запрос → **берём свободный** из пула → выполняем → **возвращаем**.
- Пул следит за «живостью» (`ping`), **переоткрывает** мёртвые.

**Конфигурация:**
- **min_connections** — минимум, чтобы pool никогда не пустел.
- **max_connections** — потолок, чтобы не положить БД.
- **idle_timeout** — закрывать неиспользуемые коннекты.
- **wait_timeout** — сколько ждать свободный коннект, прежде чем выдать ошибку.

**Подвох для PHP:**
- PHP-FPM **не держит коннект между запросами** — после `fpm_request_complete` всё освобождается. Pooling внутри PHP-процесса даёт мало.
- Решение — **внешний pooler**:
  - **`pgbouncer`** для Postgres (transaction-pooling режим — расшаривает коннект между транзакциями).
  - **`RDS Proxy`** для AWS.
  - **`ProxySQL`** для MySQL.

**Внешний pooler важен**:
- 100 PHP-FPM workers × 4 DB-коннекта = 400 коннектов на БД → быстро **упрётесь в `max_connections`**.
- pgbouncer перед БД сводит это к **20-50 реальным** коннектам.

**В long-running PHP** (Octane, RoadRunner, Swoole) — pool работает «по-нормальному» в самом процессе.

**В Java / Node.js** — стандартный pool в коде приложения (HikariCP, pg-pool).',
                'difficulty' => 3,
                'topic' => 'system_design.performance',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'В чём разница между monitoring и observability?',
                'answer' => 'Monitoring - заранее знаем что мерить и алертим на отклонения (CPU>80%, latency>500ms). Отвечает на вопрос "система здорова?". Observability - возможность задать любой вопрос системе и получить ответ из её сигналов. Отвечает на "почему так?". Простыми словами: monitoring - "горит ли красная лампочка", observability - "что именно сломалось и где". Одно дополняет другое: monitoring алертит, observability помогает диагностировать.',
                'difficulty' => 4,
                'topic' => 'system_design.performance',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Prometheus и как он устроен на верхнем уровне?',
                'answer' => '**Prometheus** — open-source система мониторинга с **pull-моделью** и **собственной time-series БД** (TSDB).

**Архитектура (верхний уровень):**
1. **Сервер Prometheus** периодически **scrape**-ит HTTP-эндпоинт **`/metrics`** на целевых сервисах.
2. Сервис отвечает **текстовым форматом** с метриками и **labels**:
   - `http_requests_total{method="GET",code="200"} 12345`
3. Prometheus **сохраняет** в TSDB.
4. **PromQL** — язык запросов для выборки и агрегации.
5. **Grafana** рисует дашборды поверх PromQL.
6. **Alertmanager** шлёт алерты в Slack / PagerDuty / email.

**Push vs Pull:**
- Prometheus — **pull** (сервер опрашивает targets). Просто понять, кто доступен (health = success scrape).
- Для **коротких jobs / cron** — **pushgateway** (job пушит метрики туда, Prometheus scrape-ит pushgateway).

**Типы метрик:**

| Тип | Что | Пример |
|---|---|---|
| **Counter** | только **растёт** (или сбрасывается при restart) | `http_requests_total`, `errors_total` |
| **Gauge** | **текущее значение** (вверх и вниз) | `memory_used_bytes`, `queue_depth` |
| **Histogram** | **распределение** по buckets | `request_duration_seconds_bucket{le="0.1"}` |
| **Summary** | **квантили** считаются на клиенте | `request_duration_seconds{quantile="0.95"}` |

**Labels (ключ к мощности):**
- Каждая комбинация labels = **отдельный временной ряд**.
- **Подвох — кардинальность**: `user_id` или `request_id` как label → миллионы рядов, **убивают Prometheus**.
- Правило: только **конечные размерности** (method, status code, region), **никогда unbounded**.

**Базовые PromQL-запросы:**
- **`rate(http_requests_total[5m])`** — RPS за 5 минут.
- **`histogram_quantile(0.95, sum(rate(request_duration_seconds_bucket[5m])) by (le))`** — p95 latency.
- **`up == 0`** — таргеты, недоступные сейчас.

**Service discovery:**
- Динамические таргеты — **Kubernetes** (через API), **Consul**, **EC2**, **DNS**.

**Экосистема экспортеров:**
- `node_exporter` (хост-метрики), `mysqld_exporter`, `redis_exporter`, `postgres_exporter`, `blackbox_exporter` (ping endpoints).

**Сильные стороны:** простота, локальная TSDB, активная экосистема, де-факто стандарт.
**Слабые стороны:**
- **Single-server** — не реплицируется горизонтально из коробки.
- **Long-term storage** ограничен диском узла.
- **Multi-tenant** — нет.

**Для production-scale ставят сверху:** **Thanos**, **Cortex**, **Mimir** — distributed storage + multi-tenant + долгосрочное хранение.',
                'difficulty' => 3,
                'topic' => 'system_design.performance',
            ],
        ];
    }
}
