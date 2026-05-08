<?php

namespace Database\Seeders\Data\Categories\Laravel;

class OctaneHorizon
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Octane? Какие у него плюсы и подводные камни?',
                'answer' => 'Octane - это пакет для Laravel, который держит приложение в памяти между запросами вместо перезагрузки. Простыми словами: обычный PHP при каждом запросе заново загружает Laravel - это медленно. Octane загружает один раз и потом каждый запрос обрабатывается мгновенно. Серверы: Swoole, RoadRunner, FrankenPHP. Подводные камни: 1) Утечки памяти - переменные класса не сбрасываются. 2) Состояние singleton-ов сохраняется. 3) Глобальные/статические переменные опасны. 4) Нужно использовать scoped-биндинги вместо singleton там, где состояние per-request. 5) Долгоживущие соединения с БД могут отваливаться по wait_timeout (gone away) - нужны reconnect-стратегии или DB::reconnect() на лонг-айдл.',
                'code_example' => 'composer require laravel/octane
php artisan octane:install
php artisan octane:start --workers=4 --task-workers=2  # --task-workers только для Swoole

// scoped binding для per-request состояния
$this->app->scoped(RequestContext::class);',
                'code_language' => 'bash',
                'difficulty' => 5,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Horizon?',
                'answer' => 'Horizon - это пакет для управления Redis-очередями: красивый dashboard, балансировка воркеров (auto/simple), метрики, мониторинг failed jobs, теги задач. Простыми словами: GUI и автомасштабирование для php artisan queue:work на Redis.',
                'code_example' => 'composer require laravel/horizon
php artisan horizon:install
php artisan horizon

// config/horizon.php
\'environments\' => [
    \'production\' => [
        \'supervisor-1\' => [
            \'connection\' => \'redis\',
            \'queue\' => [\'default\', \'high\'],
            \'balance\' => \'auto\',
            \'maxProcesses\' => 10,
        ],
    ],
],',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Какие подводные камни у Octane по сравнению с обычным FPM?',
                'answer' => 'Octane держит фреймворк в памяти между запросами. Singletons и статические свойства не сбрасываются - типичный источник утечек данных между пользователями. Запрещено хранить Auth::user() в синглтонах, использовать array-кэши на жизнь приложения, изменять контейнер из контроллеров. Решения: 1) регистрировать per-request сервисы через $this->app->scoped() - Octane сам сбрасывает scoped-биндинги между запросами; 2) подписаться на lifecycle-события Octane (RequestReceived/RequestHandled/RequestTerminated/WorkerStarting в config/octane.php → listeners) и сбрасывать там state, чистить статику, переподключать БД. Также Octane не любит долгие и блокирующие операции - нужна модель Tasks/Coroutines.',
                'code_example' => '<?php
// плохо в Octane
class CartHolder { public static array $items = []; }

// хорошо
$this->app->scoped(CartHolder::class, fn() => new CartHolder());',
                'code_language' => 'php',
                'difficulty' => 5,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как работает Laravel Horizon и какие метрики он даёт?',
                'answer' => 'Horizon - дашборд и супервизор для Redis-очередей. Конфигурируется в config/horizon.php: массив supervisors с балансингом (auto/simple/false), maxProcesses, queues, balanceMaxShift, balanceCooldown. Дашборд показывает throughput, runtime, failed jobs, worker memory, recent jobs. auto-balance перераспределяет процессы между очередями по нагрузке. horizon:terminate грейсфул-перезапускает воркеры при деплое (текущие job дорабатываются).',
                'code_example' => '// config/horizon.php
\'environments\' => [
    \'production\' => [
        \'supervisor-1\' => [
            \'connection\' => \'redis\',
            \'queue\' => [\'default\', \'emails\', \'notifications\'],
            \'balance\' => \'auto\',
            \'minProcesses\' => 1,
            \'maxProcesses\' => 20,
            \'tries\' => 3,
            \'timeout\' => 60,
        ],
    ],
],

# деплой
php artisan horizon:terminate',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое RoadRunner и за счёт чего он быстрее PHP-FPM?',
                'answer' => 'RoadRunner — это PHP application server и менеджер процессов, написанный на Go. Он держит пул PHP-воркеров живыми и передаёт им запросы по бинарному протоколу Goridge. В отличие от PHP-FPM, который перезапускает интерпретатор и заново бутстрапит фреймворк на каждом запросе, RoadRunner загружает приложение один раз, поэтому накладные расходы на инициализацию контейнера и роутов исчезают. Octane использует RoadRunner (или FrankenPHP/Swoole) как базовый сервер.',
                'difficulty' => 3,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое протокол Goridge и какие каналы связи он поддерживает?',
                'answer' => 'Goridge — это бинарный протокол, по которому Go-сервер RoadRunner общается с PHP-воркерами. Он умеет работать через стандартные pipes (по умолчанию, не требует настройки), TCP-сокеты (можно разнести воркеры по машинам или контейнерам) и Unix-сокеты для быстрой локальной связи. Любой echo или предупреждение, ушедшие в STDOUT, повредят протокол, поэтому RoadRunner 2.0+ автоматически перенаправляет STDOUT в STDERR.',
                'difficulty' => 3,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое PHP Worker в RoadRunner и как выглядит его жизненный цикл?',
                'answer' => 'PHP Worker — это обычный PHP-скрипт, в котором через Spiral\\RoadRunner\\Worker крутится бесконечный цикл while waitRequest. Воркер ждёт запрос от RoadRunner, обрабатывает его (часто через PSR-7 HttpWorker), отдаёт ответ методом respond и снова идёт ждать. При исключении следует вызвать $worker->error(), иначе процесс упадёт и RoadRunner поднимет новый. Между итерациями скрипт остаётся в памяти со всем загруженным фреймворком.',
                'difficulty' => 3,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Какие лимиты пула воркеров RoadRunner важно настраивать в .rr.yaml?',
                'answer' => 'Базовые параметры: num_workers задаёт стартовое количество процессов, max_jobs ограничивает число запросов до перезапуска воркера (защита от утечек памяти), max_memory мягко завершает воркер, превысивший лимит, ttl и idle_ttl — максимальное время жизни и простоя. В разработке удобно ставить pool.debug=true: воркер создаётся под каждый запрос, сбрасывая состояние и упрощая отладку. В продакшене debug отключают.',
                'difficulty' => 3,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Какие основные подводные камни кода в долгоживущем PHP-окружении (RoadRunner, Octane)?',
                'answer' => 'Главных четыре: утечки памяти накапливаются между запросами, поэтому ставят max_memory или max_jobs как страховку; статические свойства, синглтоны и глобальное состояние сохраняются и могут «протекать» данные одного пользователя в запрос другого; долгоживущие соединения с БД и файловые дескрипторы могут отвалиться по таймауту, нужен retry или ttl воркера; необработанные исключения роняют воркер целиком, их обязательно ловят и репортят через $worker->error().',
                'difficulty' => 4,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Зачем нужен KV-плагин в RoadRunner и какие у него драйверы?',
                'answer' => 'KV-плагин даёт единый интерфейс к key-value хранилищам и позволяет вынести часть кеша или управление состоянием из PHP в Go-процесс RoadRunner. Поддерживаются драйверы redis и memcached (распределённые), boltdb (файловое хранилище) и memory (быстрый локальный кеш экземпляра RR). Это удобно, когда нужно делиться состоянием между воркерами без накладных расходов на полноценный внешний кеш.',
                'difficulty' => 3,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как работают очереди (Jobs) в RoadRunner и какие брокеры поддерживаются?',
                'answer' => 'RoadRunner сам выступает потребителем и менеджером задач: получает задачи от брокера и передаёт их PHP-воркерам через Goridge, поэтому отдельный CLI-консьюмер не нужен. Поддерживаются драйверы amqp, beanstalk, redis, sqs, nats, а также boltdb и memory для локальных и тестовых сценариев. Это централизует обработку очередей в том же процессе, что и HTTP, и упрощает деплой.',
                'difficulty' => 3,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что HTTP-плагин RoadRunner делает с входящими запросами и поддерживает ли он стриминг ответов?',
                'answer' => 'HTTP-плагин принимает запросы как обычный веб-сервер и преобразует их в PSR-7 объекты для PHP-воркера. На уровне Go он умеет gzip-сжатие, кастомные заголовки, статические файлы, SSL/TLS и HTTP/2, а также middleware. Поддерживается Response Streaming: PHP может отдавать большой ответ клиенту инкрементально, не держа его целиком в памяти, что важно для скачиваний и server-sent events.',
                'difficulty' => 3,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Зачем нужен плагин Centrifuge в RoadRunner?',
                'answer' => 'Плагин Centrifuge интегрирует RoadRunner с Centrifugo — сервером сообщений в реальном времени по WebSockets и SockJS. Тысячи постоянных соединений держит Go-часть, а PHP-воркеры получают только события и реализуют бизнес-логику. Это снимает с PHP типовую боль с долгими WS-соединениями, для которых однопоточная синхронная модель плохо подходит.',
                'difficulty' => 4,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что делает плагин Service в RoadRunner?',
                'answer' => 'Плагин Service позволяет RoadRunner запускать произвольный бинарник или скрипт как sidecar-процесс, следить за его работоспособностью и автоматически перезапускать при падении. Это удобно для фоновых консьюмеров, экспортёров метрик, прокси-серверов и других вспомогательных демонов, которые хочется поднимать вместе с приложением одним конфигом, без отдельного supervisord.',
                'difficulty' => 4,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как работает плагин Locks в RoadRunner?',
                'answer' => 'Плагин Locks даёт PHP-воркерам распределённые блокировки для синхронизации доступа к общим ресурсам. Lock можно взять между несколькими воркерами одного экземпляра RoadRunner или между несколькими экземплярами RR на разных машинах. В качестве бэкенда используют Redis для распределённого случая или локальную память, если синхронизация нужна только внутри одного процесса.',
                'difficulty' => 3,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Какие лучшие практики запуска RoadRunner в продакшене?',
                'answer' => 'Главный процесс держат под systemd или supervisord, чтобы он автоматически поднимался. Обязательно ставят max_jobs или max_memory, иначе утечки в сторонних библиотеках раздуют память. Подключают плагин status для liveness/readiness, увеличивают ulimit -n под большое число дескрипторов и настраивают структурированное JSON-логирование для агрегатора. Без этих базовых вещей долгоживущий процесс быстро деградирует.',
                'difficulty' => 4,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что выдают эндпоинты /health и /ready плагина status в RoadRunner?',
                'answer' => '/health отвечает «жив ли сам сервер RoadRunner» — это liveness-проба, по которой Kubernetes понимает, что под надо перезапустить. /ready проверяет «есть ли хотя бы один свободный PHP-воркер, готовый принять запрос» — это readiness-проба, она убирает под из балансировщика, пока пул занят или ещё прогревается. Оба эндпоинта обычно подключают как probes в Kubernetes или как healthcheck в LB.',
                'difficulty' => 4,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как RoadRunner интегрируется с Temporal?',
                'answer' => 'Temporal — это движок оркестрации stateful и долгоживущих workflow, и RoadRunner является его основным PHP-воркером. PHP-разработчик пишет workflow и activity в виде обычных PHP-классов, а RoadRunner обеспечивает связь с сервером Temporal по протоколу Goridge: получает задания, вызывает методы воркфлоу, возвращает результаты. Это позволяет делать сложную распределённую логику с retry и таймерами на стандартном PHP.',
                'difficulty' => 4,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Почему в Octane/RoadRunner опасно держать состояние, специфичное для запроса, в синглтонах сервис-контейнера?',
                'answer' => 'Синглтоны и статические свойства живут весь жизненный цикл воркера, то есть переживают тысячи запросов. Если положить в синглтон текущего пользователя, request-scoped репозиторий или открытое соединение, эти данные утекут в следующие запросы — другому пользователю отдастся чужой контекст. Поэтому request-scoped сервисы регистрируют как scoped (Octane сбрасывает их между запросами) или явно ребиндят на каждый запрос.',
                'difficulty' => 4,
                'topic' => 'laravel.octane_horizon',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Чем Laravel Octane отличается от Laravel Horizon в задачах производительности?',
                'answer' => 'Octane ускоряет HTTP-запросы: держит приложение в памяти под Swoole/RoadRunner/FrankenPHP, экономя бутстрап Laravel. Horizon ускоряет фоновую обработку: запускает воркеры Redis-очередей, балансирует их, показывает дашборд по jobs. Это разные слои: Octane про fastpath онлайн-запросов, Horizon про оффлайн-задачи. На крупном проде их часто используют вместе.',
                'difficulty' => 3,
                'topic' => 'laravel.octane_horizon',
            ],
        ];
    }
}
