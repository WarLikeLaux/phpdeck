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
                'answer' => 'Latency - задержка одной операции (сколько ждать ответа на один запрос). Throughput - пропускная способность (сколько запросов в секунду). Простыми словами: автобан с одной полосой и скоростью 100 км/ч - низкая latency, но низкий throughput. Дорога в 10 полос с пробкой 30 км/ч - высокая latency, но высокий throughput. Оптимизировать одно не значит улучшить другое.',
                'difficulty' => 3,
                'topic' => 'system_design.performance',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое перцентили P50, P95, P99 в метриках?',
                'answer' => 'Перцентили - не среднее, а распределение. P50 (медиана) - 50% запросов быстрее этого значения. P95 - 95% быстрее (худшие 5% хуже). P99 - 99% быстрее. Простыми словами: средняя задержка может быть 100мс, но P99=2с означает что у 1% пользователей всё тормозит. Среднее обманывает - перцентили показывают "хвост". Цель SRE - снижать P95/P99, потому что именно они портят UX.',
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
                'answer' => 'Vertical scaling (scale up) - увеличиваем мощность одной машины: больше CPU, RAM, NVMe. Просто, не требует изменений в коде, но есть потолок железа и точка отказа одна. Horizontal scaling (scale out) - добавляем больше машин и распределяем нагрузку через load balancer. Почти безлимитно, отказоустойчиво, но требует stateless-приложений, общего хранилища сессий, распределённых кэшей и БД-репликации/шардинга. Правило: stateless web-слой - горизонтально, БД - вертикально до предела, потом read replicas, потом sharding. Cloud-native всегда тяготеет к horizontal.',
                'difficulty' => 3,
                'topic' => 'system_design.performance',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Какие алгоритмы балансировки нагрузки бывают?',
                'answer' => 'Round Robin - по очереди, простой, но не учитывает нагрузку. Weighted Round Robin - с весами (мощные серверы получают больше). Least Connections - на сервер с минимумом активных коннектов, хорош при разной длительности запросов. Least Response Time - на самый быстрый. IP Hash / Consistent Hash - один клиент всегда на тот же бэк (для sticky sessions без cookie). Random with Two Choices (Power of Two) - выбирает 2 случайных, шлёт на менее загруженный, эмпирически близко к Least Connections при O(1). L4 (TCP) балансировщики (HAProxy, AWS NLB) быстрее, L7 (Nginx, Envoy) умнее (могут смотреть в HTTP-заголовки, делать canary).',
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
                'answer' => 'N+1 - антипаттерн: 1 запрос за списком + N запросов за связанными данными для каждого элемента. Например, $posts = Post::all(); foreach ($posts as $p) echo $p->author->name - 1 SELECT posts + N SELECT users. На 1000 постов - 1001 запрос вместо 2. Решение: eager loading (Post::with("author")->get() даёт 2 запроса через WHERE IN). Диагностика: Laravel Debugbar, Telescope, Pulse, или Model::preventLazyLoading(! $this->app->isProduction()) в AppServiceProvider - включается в локальной/тестовой среде, чтобы любой lazy load валил LazyLoadingViolationException ДО прода; в самом проде НЕ включают как throw, иначе случайный lazy уронит юзеру 500. Для прода есть мягкий вариант: Model::handleLazyLoadingViolationUsing(fn ($model, $relation) => Log::warning(...)) - не валит запрос, а только пишет в лог/Sentry. В GraphQL аналог - DataLoader (батчит и дедуплицирует загрузки в рамках одного запроса).',
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
                'answer' => 'Часто используются как синонимы, но строго: rate limiting - жёсткий лимит "не больше N запросов в минуту", при превышении 429. Throttling - замедление: "запросы выше лимита обрабатываются медленнее, в очереди". Простыми словами: rate limit - "не пустим", throttling - "пустим, но в порядке очереди". Throttling мягче для пользователя, но требует буфер. На практике обычно делают rate limiting на API gateway.',
                'difficulty' => 3,
                'topic' => 'system_design.performance',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое APM (Application Performance Monitoring)?',
                'answer' => 'APM - инструменты для мониторинга производительности приложения в проде. Простыми словами: рентген для приложения - видно где тормозит, какой SQL медленный, где исключения, какой endpoint самый горячий. Включает: tracing запросов, профилирование, алерты, дашборды, error tracking. Инструменты: New Relic, Datadog, Sentry, Elastic APM, Laravel Telescope/Pulse. Без APM в большом проде ты слепой.',
                'difficulty' => 3,
                'topic' => 'system_design.performance',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое connection pooling и зачем нужен?',
                'answer' => 'Connection pool - готовый набор открытых коннектов к БД, переиспользуемых между запросами. Простыми словами: вместо того чтобы заново звонить и здороваться при каждом обращении - держим телефон поднятым. Открытие коннекта к Postgres дорого (TCP+TLS+auth = 10-50мс). Pool: 20-50 коннектов, при запросе берём свободный, после возвращаем в пул. Внешние пулы для PHP-FPM (где коннект на запрос): pgbouncer, RDS Proxy.',
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
        ];
    }
}
