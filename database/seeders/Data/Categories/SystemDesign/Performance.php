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
                'answer' => '**Observability** — возможность **понять, что происходит внутри системы**, по её **внешним сигналам**, не залезая в дебаггер. Из теории управления: «насколько внутреннее состояние реконструируется по выходным данным».

**Три столпа (`three pillars`):**

| Столп | Что это | Пример | Отвечает на |
|---|---|---|---|
| **Logs** | дискретные **текстовые** записи событий | `"user 42 logged in from 1.2.3.4"` | что **именно** произошло |
| **Metrics** | **числовые** ряды во времени | `http_requests_total`, `cpu_usage`, `p99_latency` | **сколько** и **как часто** |
| **Traces** | путь **одного запроса** через систему | `gateway → auth (5ms) → users (20ms) → db (15ms)` | **где** медленно / падает |

**Дополнительно сейчас выделяют:**

- **Events** — структурированные доменные события (signup, payment)
- **Profiling** — continuous CPU/memory profiling (`Pyroscope`, `Parca`)
- **eBPF** — kernel-level observability без инструментирования

**Monitoring vs Observability:**

| | **Monitoring** | **Observability** |
|---|---|---|
| **Подход** | заранее знаем, что мерить (`CPU>80%`) | можно задать **любой вопрос** к системе |
| **Алерты** | да | да, но строятся поверх данных |
| **Отвечает на** | «**работает?**» | «**почему так работает?**» |
| **Известные unknowns** | да | + **unknown unknowns** |

**Стек инструментов:**

- **Logs:** `ELK` (Elastic + Logstash + Kibana), `Loki`+Grafana, `Splunk`, `Datadog Logs`
- **Metrics:** `Prometheus`+`Grafana`, `Datadog`, `CloudWatch`, `VictoriaMetrics`
- **Traces:** `Jaeger`, `Zipkin`, `Tempo`, `AWS X-Ray`
- **Стандарт** — **OpenTelemetry** (унифицированный SDK для всех трёх)

**Корреляция между столпами** — главная ценность: видишь spike на метрике → переходишь на traces в этом окне → находишь медленный span → смотришь logs этого `trace_id`.',
                'difficulty' => 4,
                'topic' => 'system_design.performance',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое distributed tracing?',
                'answer' => '**Distributed tracing** — отслеживание прохождения **одного запроса** через множество сервисов с временной разбивкой.

**Модель данных:**

- **`trace_id`** — уникальный ID **всего запроса** (одинаковый во всех сервисах)
- **`span_id`** — ID **одного шага** (например, вызов `users-service`)
- **`parent_span_id`** — связь со span-ом, из которого был вызван текущий
- **Tags / attributes** — `http.method`, `db.statement`, `user.id`
- **Events** — точечные события внутри span-а

**Пример trace-а:**

```
Trace abc123 (350ms total)
├── gateway        [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 350ms
│   ├── auth       [▓]                       5ms
│   ├── users      [▓▓▓▓]                   20ms
│   │   └── postgres [▓▓▓]                  15ms
│   └── orders     [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓]   300ms ← тормозит!
│       └── redis  [▓]                       2ms
```

Сразу видно: 300мс уходит в **`orders`**, и это **не БД**, не сеть — значит локальная логика. Без tracing пришлось бы лезть в логи каждого сервиса с `request_id`.

**Стандарт распространения контекста — W3C Trace Context:**

```
traceparent: 00-0af7651916cd43dd8448eb211c80319c-b9c7c989f97918e1-01
             ^^ ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^ ^^^^^^^^^^^^^^^^ ^^
             ver trace-id                        parent-span-id   flags
tracestate: rojo=00f067aa0ba902b7,congo=t61rcWkgMzE
```

Каждый сервис получает `traceparent` во входящем запросе и пробрасывает в исходящие. OpenTelemetry SDK делает это автоматически.

**Инструменты:**

- **Jaeger** (Uber, CNCF) — open source UI + storage
- **Zipkin** — Twitter, классика
- **Tempo** (Grafana Labs) — масштабируемое хранилище
- **OpenTelemetry** — стандартный SDK для генерации
- **Datadog APM**, **New Relic**, **AWS X-Ray** — коммерческие

**Sampling — обязательно при большом трафике:**

- **Head-based** — решение в начале (`1%` от всех запросов)
- **Tail-based** — решение в конце (все ошибки + N% успешных) — лучше для редких проблем
- **Storage и overhead** для **100%** трейсов **дороги** — обычно `1–10%`

**В Laravel:** `OpenTelemetry SDK` + auto-instrumentation для Eloquent/HTTP клиента, либо встроенная trace-поддержка в `Laravel Pulse`/`Telescope`.',
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
                'answer' => '**Закон Литтла** (`Little\'s Law`) — фундаментальное соотношение для любой системы в **установившемся режиме**:

> **`L = λ × W`**
>
> или: **`Concurrency = Throughput × Latency`**

| Переменная | Что это | Единицы |
|---|---|---|
| **`L`** (Concurrency) | среднее число **одновременно** обрабатываемых запросов | штук |
| **`λ`** (Throughput) | средняя **скорость** поступления/обработки | `req/s` |
| **`W`** (Latency) | среднее **время** обработки одного запроса | `s` |

**Что важно:** работает для **любой** системы — очередь, БД, веб-сервер, ресторан, банк. **Не зависит от распределений**, требует только steady state.

**Практическое следствие:**

Нельзя бесконечно наращивать **concurrency без роста throughput** — **latency пропорционально вырастет**:

- Если throughput **упёрт** в `1000 RPS` (узкое место), а concurrency растёт до `10000` → `latency = 10000/1000 = 10s` на запрос
- Очередь растёт, юзеры таймаутят

**Применение в capacity planning:**

| Задача | Расчёт |
|---|---|
| **Сколько PHP-FPM воркеров?** | `workers = target_RPS × avg_latency`. Цель `1000 RPS`, средний запрос `50ms` → `50` воркеров |
| **Сколько DB-коннектов?** | `pool = QPS × query_time`. `200 QPS`, `10ms` → `2` коннекта (с запасом 4-8) |
| **Сколько consumer-ов очереди?** | `workers = msg_rate × processing_time` |
| **Выдержим ли RPS?** | `max_RPS = max_concurrency / min_latency` |

**Пример расчёта:**

- SLA: **p99 latency < 200ms** при `5000 RPS`
- Среднее время запроса ~`100ms`
- Тогда **concurrency ≈ `5000 × 0.1 = 500`** одновременных запросов
- Нужно: 500 PHP-FPM воркеров (или 5 серверов по 100 воркеров)
- DB должна выдержать `5000 × N_queries_per_request` QPS

**Подвох:**

- Работает **только в стабильном** режиме — во время spike-а формула не предсказывает
- Latency — **среднее**, не tail. Для tail latency нужны другие модели (M/M/1, M/M/c queueing)
- Bottleneck может смещаться — добавил воркеров, теперь упёрлись в БД

**Закон Литтла — главный инструмент для sizing-а** в performance engineering. Если ты не считаешь по нему — ты гадаешь.',
                'difficulty' => 4,
                'topic' => 'system_design.performance',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое tail latency (P99/P99.9) и почему она критична в распределённых системах?',
                'answer' => '**Tail latency** — задержка **верхних перцентилей** (`P99`, `P99.9`, `P99.99`), то есть **время отклика самых медленных запросов**, а не среднее или медианное.

**Почему среднее обманывает:**

- `avg = 100ms` звучит хорошо
- `P99 = 2s` — у **1% юзеров** **всё тормозит**
- Среднее **скрывает** хвост

**Почему критично в распределённых системах — амплификация хвоста:**

В крупной системе **один пользовательский запрос** порождает **десятки/сотни** внутренних вызовов, и итоговый ответ ограничен **самым медленным звеном**.

**Расчёт амплификации:**

Если каждый микросервис имеет `P99 = 1%` медленных запросов, то для пользовательского запроса из `N` внутренних вызовов вероятность **хотя бы одного** медленного:

| `N` вызовов | Доля юзеров с медленным запросом |
|---|---|
| **1** | 1% |
| **10** | `1 - 0.99^10 ≈ 10%` |
| **50** | `1 - 0.99^50 ≈ 39%` |
| **100** | `1 - 0.99^100 ≈ 63%` |

В системе Google/Amazon с **сотнями** микросервисов **`P99`** одного сервиса **становится `P50`** конечного юзера.

**Откуда берётся tail:**

- **GC паузы** (Java/Go)
- **Cold cache** (miss → БД)
- **TCP retransmit** при packet loss
- **Lock contention** на горячих ключах
- **Connection pool exhaustion**
- **VM noisy neighbor** в облаке
- **Network jitter** (RTT spike)
- **Background tasks** (vacuum, compaction)

**SLO задают именно по tail:**

- **«99% запросов отвечают за < 200ms»** — `P99 ≤ 200ms`
- **«99.9% < 500ms»** — `P99.9 ≤ 500ms`
- Не задают «среднее < 100ms» — это бессмысленно для UX

**Связь с финансами** (`Tail at Scale`, Jeff Dean):

- Amazon: **+100ms latency = -1% revenue**
- Google: **+400ms = -0.74% search per user**

**Что делать с tail** — отдельная карточка про hedged requests, request coalescing, deadline propagation, jitter.',
                'difficulty' => 4,
                'topic' => 'system_design.performance',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Какие техники применяют для снижения tail latency в распределённой системе?',
                'answer' => 'Снижение tail latency — отдельная инженерная дисциплина (Jeff Dean, **`Tail at Scale`**, 2013). **Среднее улучшается легко**, хвост — намного сложнее.

**Основные техники:**

| Техника | Принцип | Цена |
|---|---|---|
| **Hedged requests** | отправить запрос **двум репликам**, взять первый ответ | 2x нагрузка на подсистему |
| **Tied requests** | то же, но **отменить** на втором, когда первый ответил | сложнее реализовать |
| **Request coalescing** | объединить **одинаковые параллельные** запросы | требует locking/single-flight |
| **Deadline propagation** | передать **общий дедлайн** всем downstream сервисам | требует gRPC/контракт |
| **Jitter** в плановых задачах | размазать thundering herd | секунды задержки |
| **Latency-aware LB** | избегать временно медленных узлов | EWMA-метрики |
| **Bulkhead pools** | отдельные пулы для тяжёлых запросов | сложнее sizing |
| **Queue length cap** | дроп при переполнении вместо ожидания | теряем запросы |
| **Warm-up pools** | прогреть коннекты, JIT, кэши | start-up latency |

**1. Hedged requests:**

```
clientA.get(req)  ───┐
                     ├─ take(first_response)
clientB.get(req)  ───┘
```

Если `clientA` упал в `GC pause`, `clientB` спасает. **Удваивает запросы**, но **отрезает long-tail**.

**2. Tied requests (умнее hedged):**

Через 95-й перцентиль ожидания отправить второй запрос с **маркером** «отмени, если первый уже отвечает».

**3. Request coalescing (single-flight):**

`Cache::lock` — один воркер пересчитывает, остальные ждут результат вместо повторной работы. В Go — пакет **`singleflight`**.

**4. Deadline propagation:**

```
gateway: 1000ms deadline → auth: 50ms → user: 200ms → db: остаток
```

Если запрос медленнее дедлайна, downstream **отменяются досрочно** вместо траты ресурсов на безнадёжный запрос. gRPC поддерживает нативно (`context.WithDeadline`).

**5. Jitter в cron/retry/cache TTL:**

`sleep(retry_interval + rand(0, jitter))` — два упавших клиента не ретраят одновременно.

**6. Bulkhead** (Netflix Hystrix паттерн):

Отдельные thread pools для разных downstream — медленный сервис не съест ресурсы быстрого.

**7. Adaptive load shedding:**

При перегрузке отбрасывать **низкоприоритетные** запросы (`429` для batch-API, серверим только UI).

**Что НЕ помогает:**

- Просто «добавить серверов» — bottleneck часто не в CPU
- Кэш — закрывает hits, miss-ы всё равно дают tail
- Просто retry — может усугубить (retry-storm)',
                'difficulty' => 4,
                'topic' => 'system_design.performance',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Медленный GET-эндпоинт возвращает много записей: как ускорить, не трогая формат ответа?',
                'answer' => 'Контракт API **фиксирован** — менять `JSON`-форму нельзя. Оптимизируем **под капотом**, по шагам от дешёвого к дорогому.

**Шаг 1. Понять, где время — `EXPLAIN ANALYZE`:**

- **Seq scan** на большой таблице → нужен индекс
- **Many rows, few used** → покрывающий индекс
- **Sort spilled to disk** → `work_mem` или `ORDER BY` по индексу
- **Nested loop на N=миллионы** → `JOIN` план плохой, нужны статистики

**Шаг 2. Индексы:**

- **Покрывающий индекс** (`INCLUDE` в Postgres, добавление колонок в композитный индекс в MySQL) — выборка **без обращения к heap**
- **Composite index** под `WHERE + ORDER BY` (leftmost prefix)
- **Partial index** на горячее подмножество (`WHERE deleted_at IS NULL`)

**Шаг 3. Пагинация:**

- **OFFSET плохо** — для страницы 10000 БД читает все 10000 строк
- **Keyset (cursor) pagination**:

```sql
SELECT * FROM posts WHERE id > :last_id ORDER BY id LIMIT 50;
```

`OFFSET → O(N+limit)`, **keyset → `O(log N + limit)`**

**Шаг 4. Денормализация под read-сценарий:**

- **Materialized view** с `REFRESH CONCURRENTLY` (Postgres)
- **Денормализованная таблица** — джойны при write, не при read
- **Read model** в **CQRS**-стиле

**Шаг 5. Кэш:**

- **`Cache::remember`** на 5 минут — `EXPLAIN` уйдёт, ответ из Redis
- **Версионирование ключа** (`posts:list:v{count}`) или **tag-based invalidation** при `save`

**Шаг 6. Инфраструктура:**

- **Read replica** для разгрузки master (`->on("replica")` в Eloquent)
- **Connection pool** (pgbouncer)
- **HTTP-cache** (`Cache-Control`, `ETag`) — браузер/CDN не дойдёт до сервера

**Шаг 7. Если всё не помогло:**

- **Денормализация в Elastic/MongoDB** под этот endpoint
- **Pre-computed** агрегаты через очереди (eventually consistent)
- **Edge cache** через CDN

**Что НЕ меняется:** `JSON`-форма, имена полей, типы. Контракт сохранён, изменена только реализация под капотом.

**Pro tip:** часто после `EXPLAIN` оказывается, что проблема в одном `JOIN` или одном `ORDER BY` — индекс закрывает всё. **Не строй кэш, пока не починил БД.**',
                'difficulty' => 4,
                'topic' => 'system_design.performance',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как ускорить массовую вставку миллионов строк в большую таблицу?',
                'answer' => 'Naïve loop с `INSERT` по одной строке — **миллион fsync, миллион round-trip-ов**. Это **в сотни раз медленнее** правильного подхода.

**Приёмы по эффективности:**

| Приём | Прирост | Сложность |
|---|---|---|
| **Bulk INSERT** (batch `VALUES`) | **~10–100x** | низкая |
| **Транзакция** на батч | **~10x** | низкая |
| **`LOAD DATA INFILE` / `COPY`** | **~10x** поверх bulk | средняя |
| **Отключить индексы на время load** | **~2–10x** | средняя |
| **Партиционирование + `ATTACH`** | **~100x** на огромных таблицах | высокая |

**1. Bulk INSERT — одной командой с пачкой `VALUES`:**

```sql
INSERT INTO products (sku, name, price) VALUES
    (?, ?, ?), (?, ?, ?), (?, ?, ?), ...;  -- 1000 строк
```

Вместо миллиона запросов — тысяча. Лимит — `max_allowed_packet` (MySQL) или количество параметров (`bindings`).

**2. Транзакция вокруг батча** — `BEGIN; INSERT ...; COMMIT;` — один **`fsync` на коммит**, а не на каждую строку.

**3. `LOAD DATA INFILE` (MySQL) / `COPY` (Postgres):**

```sql
-- Postgres
COPY products (sku, name, price) FROM \'/tmp/data.csv\' WITH (FORMAT csv);

-- MySQL
LOAD DATA INFILE \'/tmp/data.csv\' INTO TABLE products
FIELDS TERMINATED BY \',\';
```

**На порядок быстрее** обычного `INSERT` — нет парсинга SQL, прямой путь к storage engine.

**4. Отключить индексы и FK на время load:**

```sql
-- Postgres
ALTER TABLE products DISABLE TRIGGER ALL;
DROP INDEX idx_products_sku;
-- bulk load
CREATE INDEX CONCURRENTLY idx_products_sku ON products(sku);

-- MySQL InnoDB
SET unique_checks=0; SET foreign_key_checks=0;
-- bulk load
SET unique_checks=1; SET foreign_key_checks=1;
```

Построение индекса **на готовых данных** — дешевле, чем инкрементальное обновление при каждой вставке.

**5. Партиционированная таблица + `ATTACH`:**

- Создать **новую партицию** в отдельной таблице
- Залить туда (без индексов основной таблицы)
- Построить индексы локально
- **`ALTER TABLE ... ATTACH PARTITION`** — атомарно, без блокировки read-ов

**В Laravel:**

- `DB::table("products")->insert($chunk)` — bulk на массиве
- `$collection->chunk(1000)` + `DB::transaction(fn() => DB::table(...)->insert($chunk))`
- Для гигантских импортов — **`LOAD DATA`** через `DB::statement("LOAD DATA ...")`

**Грабли:**

- **Размер батча** — слишком большой батч (`50k`) ест RAM, может упереться в `max_allowed_packet`. Sweet spot — `1k–10k` строк
- **Триггеры** на таблице срабатывают на каждую строку — отключи на время load
- **Replication lag** при больших insert-ах
- **Vacuum** в Postgres после bulk-а — обязателен',
                'difficulty' => 4,
                'topic' => 'system_design.performance',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как правильно обрабатывать deadlock в высоконагруженном приложении?',
                'answer' => '**Deadlock** на уровне БД — это **не баг**, а **штатная ситуация** под нагрузкой. Приложение должно её **ожидать и обрабатывать**.

**Что такое deadlock:**

Транзакция A держит блокировку на строку 1, ждёт строку 2. Транзакция B держит строку 2, ждёт строку 1. **Тупик** → СУБД **выбирает жертву**, откатывает её и возвращает ошибку.

**Коды ошибок (для catch):**

| СУБД | Код | Сообщение |
|---|---|---|
| **MySQL** | `1213` (`ER_LOCK_DEADLOCK`) | `Deadlock found when trying to get lock` |
| **MySQL** | `1205` (`ER_LOCK_WAIT_TIMEOUT`) | `Lock wait timeout exceeded` (не deadlock, но retryable) |
| **Postgres** | `40P01` (`deadlock_detected`) | `deadlock detected` |
| **Postgres** | `40001` (`serialization_failure`) | для `SERIALIZABLE` |

**Стратегии (по приоритету):**

**1. Предотвращение — короткие транзакции:**

- Не делать **HTTP-вызовы** или **долгие вычисления** внутри транзакции
- Открыть → быстро поработать → закрыть
- Все `SELECT FOR UPDATE` в начале, потом батч `UPDATE`

**2. Согласованный порядок блокировок:**

Всегда брать блокировки **в одном порядке** (например, по возрастанию `id`):

```php
// плохо — порядок зависит от данных
foreach ($selectedIds as $id) { lock($id); }

// хорошо — всегда от меньшего к большему
sort($selectedIds);
foreach ($selectedIds as $id) { lock($id); }
```

**3. Точечные блокировки** вместо range-locks:

- `SELECT FOR UPDATE` по **конкретному `id`** вместо `WHERE status = ?`
- **`SKIP LOCKED`** — пропустить занятые строки (для job-очередей)
- **`NOWAIT`** — fail быстро вместо ожидания

**4. Retry с exponential backoff + jitter:**

```php
for ($attempt = 1; $attempt <= 3; $attempt++) {
    try {
        return DB::transaction(fn () => doWork());
    } catch (QueryException $e) {
        if (in_array($e->errorInfo[1], [1213, 1205])) {
            usleep(($attempt * 100 + random_int(0, 50)) * 1000);
            continue;
        }
        throw $e;
    }
}
```

В Laravel — встроенный третий аргумент: **`DB::transaction(fn() => ..., $attempts = 3)`** автоматически retry-ит deadlock-и.

**5. Понижение уровня изоляции** там, где допустимо:

- **`READ COMMITTED`** вместо `REPEATABLE READ` — меньше блокировок (но возможны non-repeatable reads)
- Не для финансов, но норм для read-heavy операций

**Чего НЕ делать:**

- **Не показывать `500`** юзеру — deadlock retryable, обработай молча
- **Не делать `WHILE TRUE retry`** — exponential backoff обязателен, иначе retry-storm
- **Не игнорировать в логах** — растущий deadlock-rate = архитектурная проблема

**Мониторинг:** алерт на `deadlock_rate > 0.1%`, разбор топ-3 самых частых через `INFORMATION_SCHEMA.INNODB_TRX` (MySQL) или `pg_stat_activity` + `pg_locks` (Postgres).',
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
                'answer' => 'Часто **используют как синонимы**, но это **разные подходы**.

| | **Monitoring** | **Observability** |
|---|---|---|
| **Подход** | заранее знаем, **что мерить** | можно задать **любой вопрос** |
| **Метрики** | предопределённый набор (`CPU`, `latency`, `errors`) | широкие, **с высокой кардинальностью** |
| **Алерты** | да — на отклонения | да, но строятся поверх данных |
| **Отвечает на** | «**система здорова?**» | «**почему так работает?**» |
| **Unknowns** | known unknowns | + **unknown unknowns** |
| **Корни** | infra/systems engineering | теория управления |
| **Инструменты** | Nagios, Zabbix, Prometheus + dashboard | OpenTelemetry, Honeycomb, Datadog с structured events |

**Аналогия:**

- **Monitoring** — «горит ли красная лампочка на панели?»
- **Observability** — «**что именно** сломалось, **где**, **для кого** и **почему**?»

**Monitoring достаточно для:**

- Простой архитектуры (монолит + БД)
- Известных failure modes (`CPU>80%`, `disk>90%`, `5xx > 1%`)
- Шаблонных алертов

**Observability нужна, когда:**

- **Микросервисы** — отказ может быть в любом из десятков сервисов
- **Highly-dynamic infrastructure** — Kubernetes scaling, serverless
- Сложные user-facing проблемы («у юзеров из Канады с iPhone 15 не работает оплата картой Visa по понедельникам»)
- Нужны **редкие комбинации** атрибутов (high cardinality)

**Высокая кардинальность — главный shift:**

- Monitoring: `http_requests_total{method, status}` — десятки рядов
- Observability: события с `user_id`, `request_id`, `customer_tier`, `feature_flag`, `deploy_version`, `geographic_region` — миллионы уникальных комбинаций

**Они дополняют друг друга:**

- **Monitoring алертит** — «p99 latency > 500ms, инцидент!»
- **Observability помогает диагностировать** — «у юзеров premium-tier в EU после deploy `v2.5` упала latency endpoint `/checkout`»

**Современный стек:**

- **Metrics**: Prometheus / VictoriaMetrics
- **Logs**: Loki / Elasticsearch с structured JSON
- **Traces**: Tempo / Jaeger
- **Events**: Honeycomb / Datadog с custom attributes
- Связывающий клей — **OpenTelemetry**',
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
