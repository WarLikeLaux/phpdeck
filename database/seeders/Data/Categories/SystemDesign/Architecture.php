<?php

namespace Database\Seeders\Data\Categories\SystemDesign;

class Architecture
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое архитектура приложения простыми словами?',
                'answer' => 'Архитектура — это крупные решения о том, как устроено приложение: на какие слои/модули оно разбито, кто с кем общается, где лежат данные, как масштабируется. Простыми словами: чертёж дома — где стены, где окна, где лестница. Код можно переписать за день, архитектуру — за полгода, поэтому важно сразу решать осознанно. На уровне junior главное знать пару базовых стилей (монолит, микросервисы, MVC) и понимать, что нет "правильной" архитектуры — есть подходящая под задачу и команду.',
                'difficulty' => 1,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое монолит простыми словами?',
                'answer' => 'Монолит - это одно большое приложение, в котором весь код (бизнес-логика, БД, UI) живёт вместе и деплоится одной "коробкой". Простыми словами: представь огромный швейцарский нож - один инструмент со всеми функциями. Плюсы: проще разрабатывать, дебажить, деплоить. Минусы: сложно масштабировать отдельные части, любая ошибка может уронить всё, тяжёлый деплой.',
                'difficulty' => 2,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое микросервисы простыми словами?',
                'answer' => 'Микросервисы - это разбиение большого приложения на маленькие независимые сервисы, каждый отвечает за свою бизнес-задачу и общается с другими через сеть (HTTP, очереди). Простыми словами: вместо одного большого швейцарского ножа у тебя набор отдельных инструментов. Сервис заказов, сервис платежей, сервис уведомлений - каждый можно разрабатывать, деплоить и масштабировать отдельно.',
                'difficulty' => 2,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Какие плюсы и минусы у микросервисов по сравнению с монолитом?',
                'answer' => 'Плюсы микросервисов: независимое масштабирование, разные технологии, изоляция отказов, независимые деплои, маленькие команды владеют сервисами. Минусы: сложность инфраструктуры (k8s, service mesh), сетевые задержки, распределённые транзакции, дебаг через множество сервисов, нужны DevOps-практики, eventual consistency. Монолит проще для маленьких команд, микросервисы оправданы при росте.',
                'difficulty' => 3,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Service-Oriented Architecture (SOA)?',
                'answer' => 'SOA — архитектурный стиль с переиспользуемыми сервисами и явными контрактами; концептуально предшественник микросервисов. В enterprise-реализации часто шла связка SOAP/XML + WSDL + корпоративная шина ESB (для маршрутизации, трансформации, оркестрации) — но это инструменты эпохи, а не определение SOA, формально SOA можно строить и на REST/JSON. Отличия от микросервисов: SOA-сервисы крупнее, координация централизована через ESB (orchestration), часто общие схемы и общая БД на несколько сервисов. Микросервисы — мельче, decentralized governance, «smart endpoints + dumb pipes» (REST/gRPC, message broker без бизнес-логики), своя БД на сервис. Грубо: SOA = «много сервисов вокруг одной шины», микросервисы = «много независимых сервисов с минимальной связью».',
                'difficulty' => 3,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое 3-х уровневая архитектура?',
                'answer' => '3-tier architecture - разделение приложения на три части: 1) Presentation (UI, фронтенд), 2) Business Logic (бэкенд, контроллеры, сервисы), 3) Data (БД, файловое хранилище). Каждая часть общается только со смежной. Важно различать tier и layer: классически 3-tier — это physical separation на 3 узла/процесса (отдельный фронт, отдельный сервер приложений, отдельный сервер БД), а 3-layer — logical разделение модулей внутри одного процесса. Современный веб часто реализуется как 3-layer на одном backend-процессе с отдельной БД, и его строго говоря не всегда корректно называть 3-tier.',
                'difficulty' => 3,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое гексагональная архитектура (Ports and Adapters)?',
                'answer' => 'Hexagonal architecture - подход, где доменная логика в центре, а вокруг "порты" (интерфейсы) и "адаптеры" (конкретные реализации: HTTP, БД, очередь). Простыми словами: ядро приложения не знает, откуда пришёл запрос (CLI, HTTP, тест) и куда сохраняются данные (Postgres, файл). Это даёт тестируемость и возможность менять инфраструктуру не трогая бизнес-логику.',
                'code_example' => '<?php
// Порт (интерфейс)
interface OrderRepository {
    public function save(Order $order): void;
}

// Адаптер для Postgres
class PgOrderRepository implements OrderRepository {
    public function save(Order $order): void { /* SQL */ }
}

// Адаптер для тестов
class InMemoryOrderRepository implements OrderRepository {
    private array $items = [];
    public function save(Order $order): void { $this->items[] = $order; }
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Clean Architecture?',
                'answer' => 'Clean Architecture (Дядя Боб) - концентрические слои, зависимости направлены только внутрь. Слои снаружи внутрь: Frameworks & Drivers, Interface Adapters, Use Cases, Entities. Простыми словами: бизнес-правила (entities, use cases) не зависят от Laravel, Postgres или REST - можно переключить любой внешний слой не трогая ядро. Чем-то похоже на гексагональную, но с явной иерархией слоёв.',
                'difficulty' => 4,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Onion Architecture?',
                'answer' => 'Onion Architecture - вариация Clean Architecture со слоями-кольцами луковицы. Центр - Domain Model (сущности), вокруг Domain Services, Application Services, на периферии Infrastructure (БД, UI, тесты). Зависимости направлены внутрь: внешние слои знают о внутренних, но не наоборот. Идея: бизнес-логика стабильна, инфраструктура меняется - значит инфраструктура должна зависеть от логики, а не наоборот.',
                'difficulty' => 4,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Anti-Corruption Layer?',
                'answer' => 'Anti-Corruption Layer (ACL) - прослойка между двумя bounded contexts, которая транслирует данные и не даёт чужой модели "испортить" твою. Простыми словами: переводчик на границе, который превращает данные внешнего сервиса (например, легаси-CRM) в чистые объекты твоего домена. Если завтра CRM поменяют - правишь только ACL, остальной код не трогаешь.',
                'code_example' => '<?php
class LegacyCrmAcl {
    public function __construct(private LegacyCrmClient $crm) {}

    public function getCustomer(int $id): Customer {
        $raw = $this->crm->getClientData($id);
        return new Customer(
            id: $raw["client_id"],
            name: $raw["full_name"],
            email: $raw["contact_email"],
        );
    }
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое BFF (Backend for Frontend)?',
                'answer' => 'BFF - отдельный бэкенд для каждого фронтенда (web, iOS, Android). Простыми словами: вместо общего API, который пытается угодить всем клиентам, делают отдельный сервис для мобильного и отдельный для веба. Каждый отдаёт ровно те данные и в том формате, что нужен конкретному клиенту. Уменьшает overfetching, упрощает версионирование, но плодит дублирование кода.',
                'difficulty' => 3,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое API Gateway?',
                'answer' => 'API Gateway - единая точка входа для всех клиентских запросов в микросервисную систему. Делает: маршрутизацию к нужному сервису, аутентификацию, rate limiting, кэширование, логирование, агрегацию ответов из нескольких сервисов. Простыми словами: швейцар на входе - проверяет билет, направляет в нужный зал, считает посетителей. Примеры: Kong, Tyk, AWS API Gateway, Nginx с конфигами.',
                'difficulty' => 3,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое reverse proxy и для чего он нужен?',
                'answer' => 'Reverse proxy - сервер-посредник перед твоим приложением, который принимает запросы клиентов и передаёт их в бэкенд. Простыми словами: секретарь, который принимает звонки и переводит их на нужного сотрудника. Зачем: терминация TLS, балансировка нагрузки, кэш, защита от DDoS, скрытие внутренней инфраструктуры, обслуживание статики. Популярные: Nginx, HAProxy, Caddy, Traefik.',
                'code_example' => 'server {
    listen 443 ssl;
    server_name api.example.com;

    location / {
        proxy_pass http://backend:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    }
}',
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Чем reverse proxy отличается от forward proxy?',
                'answer' => 'Forward proxy стоит перед клиентом, скрывает клиента от сервера (корпоративный прокси для выхода в интернет). Reverse proxy стоит перед сервером, скрывает сервер от клиента (Nginx перед Laravel). Простыми словами: forward proxy - это "я хожу через посредника", reverse proxy - это "ко мне приходят через посредника". Для пользователя reverse proxy выглядит как сам сервер.',
                'difficulty' => 3,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое load balancer и зачем он нужен?',
                'answer' => 'Load balancer распределяет входящие запросы между несколькими серверами, скрывая их за единым адресом. Решает три задачи: горизонтальное масштабирование (можно добавлять/убирать backend-узлы без правки клиентов), отказоустойчивость (health checks выводят упавшие узлы из ротации), удобный entrypoint для TLS termination и rate limiting. Реализации бывают аппаратные (F5), L4-софтовые (HAProxy, AWS NLB — балансируют по TCP/UDP, быстрые), L7-софтовые (Nginx, Envoy, AWS ALB — могут смотреть в HTTP-заголовки, делать routing по host/path, canary). Простыми словами: одна вывеска перед группой серверов. Конкретные алгоритмы (round robin, least connections, IP hash, power of two) — отдельная карточка в performance.',
                'code_example' => 'upstream backend {
    least_conn;
    server backend1.example.com weight=3;
    server backend2.example.com weight=1;
    server backend3.example.com backup;
}',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое sticky session и зачем нужна?',
                'answer' => 'Sticky session (session affinity) - привязка пользователя к одному и тому же серверу для всех его запросов. Простыми словами: если один раз попал на сервер №2, дальше всегда туда же. Нужна, когда сессии хранятся в памяти конкретного сервера и не вынесены в общее хранилище (Redis/Memcached). Минусы: неравномерная нагрузка, при падении сервера пользователь теряет сессию. Лучше хранить сессии в Redis - тогда sticky не нужны.',
                'difficulty' => 3,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Saga: чем отличается choreography (хореография) от orchestration (оркестрация)?',
                'answer' => 'Saga - паттерн распределённых транзакций, где локальные транзакции каждого сервиса связываются цепочкой, и при сбое одного шага запускаются compensating actions (обратные операции). Реализуется в двух стилях. Choreography (хореография): нет центрального координатора - каждый сервис слушает события и решает сам, что делать дальше. Order создан → publish OrderCreated → Payment слушает, списывает деньги → publish PaymentCharged → Inventory слушает, резервирует товар → publish InventoryReserved → ... При сбое сервис publish-ит компенсирующее событие (например, PaymentFailed), на которое подписаны все, кому нужно откатить свою часть. Плюсы: слабая связность, нет SPOF, легко добавлять новых участников. Минусы: бизнес-процесс "размазан" по сервисам - трудно понять текущее состояние саги; сложно отслеживать и дебажить (нужен distributed tracing); легко получить циклы и неявные зависимости. Orchestration (оркестрация): есть центральный сервис-оркестратор (saga orchestrator/manager), который явно вызывает шаги и обрабатывает их результаты, ведя state machine саги. Order Saga: оркестратор шлёт ChargePaymentCommand → ждёт ответа → шлёт ReserveInventoryCommand → ... При сбое оркестратор шлёт компенсации в обратном порядке. Плюсы: бизнес-логика в одном месте, явная state machine, проще debug. Минусы: оркестратор - SPOF и узкое место по нагрузке; сильная связь сервисов с оркестратором. Когда что: choreography - простые саги из 2-3 шагов, событийная архитектура. Orchestration - сложные саги (5+ шагов, ветвления, retry-логика), регулируемые домены. Реализации: Temporal, Camunda, AWS Step Functions для orchestration; Kafka/RabbitMQ + outbox для choreography.',
                'code_example' => '<?php
// CHOREOGRAPHY (события)
class PaymentService
{
    public function onOrderCreated(OrderCreated $event): void
    {
        try {
            $this->chargeCard($event->orderId, $event->amount);
            event(new PaymentCharged($event->orderId));
        } catch (Throwable $e) {
            event(new PaymentFailed($event->orderId, $e->getMessage()));
        }
    }
}

class InventoryService
{
    public function onPaymentCharged(PaymentCharged $event): void { /* резерв */ }
    public function onPaymentFailed(PaymentFailed $event): void { /* ничего не делать */ }
    public function onInventoryReservationFailed(...$e): void { /* публикуем для отката Payment */ }
}

// ORCHESTRATION (центральный координатор)
class OrderSagaOrchestrator
{
    public function execute(int $orderId): void
    {
        $state = SagaState::start($orderId);
        try {
            $state->mark("payment_started");
            $this->payments->charge($orderId);  // sync или через ответное событие

            $state->mark("inventory_started");
            $this->inventory->reserve($orderId);

            $state->mark("shipping_started");
            $this->shipping->schedule($orderId);

            $state->complete();
        } catch (SagaStepFailed $e) {
            // компенсации в обратном порядке по state
            foreach (array_reverse($state->completedSteps()) as $step) {
                $this->compensate($step, $orderId);
            }
            $state->fail($e);
        }
    }
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Strangler Fig pattern и как им безопасно распилить монолит?',
                'answer' => 'Strangler Fig (Strangler Application) - паттерн от Мартина Фаулера для постепенной замены legacy-системы новой, без полного переписывания "всё с нуля" (Big Bang rewrite, который проваливается в большинстве случаев из-за scope creep, потерянной бизнес-логики и год без релизов). Аналогия из природы: фиговое дерево обвивает старое дерево, постепенно "душит" его и в конце концов занимает его место - старое дерево становится опорой и в итоге погибает. В коде так же: новая система растёт вокруг старой, забирая по одной функции, пока legacy не остаётся пустой оболочкой. Механика: 1) Перед монолитом ставится фасад - API Gateway, reverse-proxy (nginx/HAProxy/Envoy), service mesh, или просто роутер на уровне фреймворка. 2) Все запросы пока идут в монолит как обычно. 3) Создаётся новый сервис с одной (!) функциональностью - например, "регистрация пользователей". 4) На фасаде роут "POST /users" переключается с монолита на новый сервис. Все остальные роуты по-прежнему идут в монолит. 5) Шаг повторяется для других областей: каждый раз - один эндпоинт, один use-case, один bounded context. Месяцами и годами. 6) В конце монолит больше ничего не обслуживает - его выключают. Плюсы: 1) Постоянная работа с прода - можно откатить любой шаг. 2) Бизнес продолжает релизить фичи параллельно. 3) Снижение риска - на любом этапе можно остановиться. 4) Легко обоснуется бизнесу (incremental value). Подводные камни: 1) Общая БД - часто новый сервис вынужден читать монолитную БД, создаётся anti-corruption layer / replicated read-store через CDC. 2) Аутентификация и сессии - решается через единый Auth-сервис или валидацию JWT в обоих. 3) Транзакции, которые были одной DB-транзакцией в монолите, становятся распределёнными - нужны Saga/Outbox. 4) Дольше живёт суммарно (год legacy + год переписывания), но риск меньше. 5) Команда должна быть дисциплинированной - иначе новые фичи будут добавляться и в монолит, и в микросервис, и удушения не произойдёт.',
                'code_example' => '# Шаг 0: всё в монолите
# nginx.conf
location / { proxy_pass http://monolith; }

# Шаг 1: вынесли регистрацию в новый сервис
# nginx.conf
location /users { proxy_pass http://users-service; }
location /     { proxy_pass http://monolith; }

# Шаг N: большая часть функций мигрировала
location /users    { proxy_pass http://users-service; }
location /orders   { proxy_pass http://orders-service; }
location /payments { proxy_pass http://payments-service; }
location /billing  { proxy_pass http://billing-service; }
location /     { proxy_pass http://monolith; }   # последние 5% - reports/admin

# Шаг финал: монолит мёртв
# location / уходит через тот же gateway, монолит выключаем

# Для сложных случаев: branch-by-abstraction внутри монолита
# 1) интерфейс UserService
# 2) две реализации: LegacyUserService (DB monolith), RemoteUserService (HTTP к новому)
# 3) feature flag (Pennant) переключает между ними
# 4) сначала 1% юзеров на Remote, потом 50%, потом 100% - и удаляется LegacyUserService

# Anti-corruption layer (DDD): новый сервис общается со старым через адаптер,
# который переводит legacy data model в чистую domain-модель нового сервиса.
# Так legacy не загрязняет новый код своей семантикой.',
                'code_language' => 'bash',
                'difficulty' => 4,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'В чём ключевая разница между Hexagonal (Ports & Adapters) и Onion архитектурами?',
                'answer' => 'Hexagonal делает акцент на изоляции приложения от внешнего мира через порты (интерфейсы) и адаптеры (реализации), но не диктует внутреннюю структуру «гексагона». Onion жёстко предписывает концентрические слои с правилом, что зависимости всегда направлены внутрь — к доменной модели в центре. Hexagonal симметричен относительно входов и выходов, Onion иерархичен и подчёркивает доменное ядро.',
                'difficulty' => 3,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'В чём разница между функциональными и нефункциональными требованиями к системе?',
                'answer' => 'Функциональные требования описывают, что система делает: конкретные сценарии и поведение, например «пользователь может забронировать самокат» или «отчёт экспортируется в CSV». Нефункциональные требования (NFR) описывают, как система работает: масштабируемость (10k RPS), доступность (99.9% uptime), задержка, безопасность, поддерживаемость. NFR обычно определяют выбор архитектуры и инфраструктуры, тогда как функциональные требования диктуют доменную модель и API.',
                'difficulty' => 3,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое service mesh?',
                'answer' => 'Service mesh - инфраструктурный слой для взаимодействия микросервисов. Прокси (sidecar) рядом с каждым сервисом перехватывает весь сетевой трафик и обеспечивает: retry, circuit breaker, mTLS, tracing, traffic splitting (canary), rate limiting - без изменений в коде сервисов. Простыми словами: общий "сетевой стек" для всех сервисов вынесен в инфраструктуру. Реализации: Istio, Linkerd, Consul Connect. Sidecar обычно Envoy.',
                'difficulty' => 5,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое event-driven architecture?',
                'answer' => 'Event-driven architecture - сервисы общаются через события вместо прямых вызовов. Сервис A публикует "OrderCreated" в шину (Kafka, RabbitMQ), сервисы B, C, D подписываются и реагируют каждый по-своему. Простыми словами: вместо телефонных звонков - публикация новостей в газете. Плюсы: loose coupling, легко добавить нового подписчика, асинхронность. Минусы: сложнее дебажить, eventual consistency, нужна хорошая обсервабилити для трассировки.',
                'difficulty' => 4,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое MVC простыми словами?',
                'answer' => 'Model-View-Controller — паттерн разделения приложения на 3 слоя. Model — данные и бизнес-логика (юзер, заказ, как они сохраняются и считаются). View — представление, что видит пользователь (HTML-страница, JSON-ответ). Controller — связующее звено: принимает HTTP-запрос, дёргает Model, отдаёт View. Идея — каждый слой отвечает за своё, можно менять UI не трогая логику и наоборот. Большинство веб-фреймворков (Laravel, Symfony, Rails) построены вокруг MVC.',
                'difficulty' => 1,
                'topic' => 'system_design.architecture',
            ],
        ];
    }
}
