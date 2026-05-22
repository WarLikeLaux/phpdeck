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
                'answer' => '**Архитектура** — это **крупные решения о том, как устроено приложение**: на какие слои и модули оно разбито, кто с кем общается, где лежат данные, как масштабируется.

Аналогия: **чертёж дома** — где несущие стены, где окна, где лестница. Код можно переписать за день, архитектуру — за полгода, поэтому решения принимают осознанно и заранее.

Базовые стили, которые надо знать на старте:

- **монолит** — всё в одном приложении
- **микросервисы** — много мелких независимых сервисов
- **MVC** — разделение на **Model-View-Controller** внутри одного приложения

Главное помнить: **нет «правильной» архитектуры — есть подходящая под задачу, команду и нагрузку**. Для пет-проекта микросервисы — оверкилл, для Netflix монолит — узкое место.',
                'difficulty' => 1,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое монолит простыми словами?',
                'answer' => '**Монолит** — одно большое приложение, в котором весь код (бизнес-логика, доступ к БД, UI) живёт вместе и деплоится **одной «коробкой»**.

Аналогия: огромный швейцарский нож — один инструмент со всеми функциями.

**Плюсы:**

- проще разрабатывать и дебажить (всё в одном репо, один процесс)
- один деплой — одна команда (`php artisan deploy` / `git push`)
- транзакции БД работают «бесплатно», без распределёнки
- одна кодовая база, одна команда

**Минусы:**

- нельзя масштабировать отдельные части — только всё целиком
- любая ошибка может уронить всё приложение
- тяжёлый деплой: правка одной строки = передеплой всего
- со временем код становится «комом», границы модулей размываются

**Когда брать:** стартап, MVP, маленькая команда, неясный домен. Большинство успешных проектов начинают с монолита.',
                'difficulty' => 2,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое микросервисы простыми словами?',
                'answer' => '**Микросервисы** — разбиение большого приложения на **много маленьких независимых сервисов**. Каждый отвечает за свою бизнес-задачу и общается с другими **через сеть** (`HTTP`/`REST`, `gRPC`, очереди вроде `RabbitMQ`/`Kafka`).

Аналогия: вместо одного огромного швейцарского ножа — набор отдельных инструментов. **Order Service**, **Payment Service**, **Notification Service** — каждый можно разрабатывать, деплоить и масштабировать отдельно.

**Признаки настоящих микросервисов:**

- **своя БД** у каждого сервиса (не общая)
- независимый деплой (можно выкатить Payment, не трогая Order)
- общение только через **публичный API** (HTTP/очереди), не через прямой доступ к чужой БД
- независимое масштабирование (Payment под нагрузкой — поднимаем 10 инстансов только его)
- маленькая команда владеет сервисом end-to-end

**За что платим:** сеть ненадёжна (retry, timeout), распределённые транзакции через **saga**, eventual consistency, нужен DevOps (`Docker`, `k8s`, мониторинг, distributed tracing).

**Когда брать:** большой домен с чёткими границами, несколько команд, разные требования к масштабированию. Маленькому проекту микросервисы — оверкилл.',
                'difficulty' => 2,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Какие плюсы и минусы у микросервисов по сравнению с монолитом?',
                'answer' => 'Сравнение **микросервисы vs монолит** — главное помнить, что «правильного» ответа нет, есть **подходящий под задачу и команду**.

**Плюсы микросервисов:**
- **Независимое масштабирование** — узкое место (Payment под Black Friday) поднимают отдельно, не реплицируя всё приложение.
- **Разные технологии** — `Notification` на Go, `Search` на Java, `Admin` на Laravel. Полиглотный стек.
- **Изоляция отказов** — упал Recommendation → каталог продолжает работать, fallback показывает «без рекомендаций».
- **Независимые деплои** — команда Payment релизит несколько раз в день, не дожидаясь общего release-окна.
- **Маленькие команды владеют сервисом end-to-end** — короче цепочка решений, меньше согласований.
- Чёткие **bounded contexts** — границы доменов фиксируются физически.

**Минусы микросервисов:**
- **Сложная инфраструктура** — `k8s`, service mesh, мониторинг, логи, distributed tracing — нужен DevOps.
- **Сеть ненадёжна** — `timeout`, **retry**, **circuit breaker**, idempotency, дедупликация.
- **Распределённые транзакции** — нет «бесплатных» ACID, нужны **saga**, outbox, eventual consistency.
- **Дебаг через сервисы** — один запрос проходит 5 сервисов, нужен **OpenTelemetry / Jaeger**.
- **Дублирование кода** — общие модели DTO, утилиты, миграции на каждом сервисе.
- **Latency** — `HTTP`-вызовы между сервисами медленнее, чем in-memory call.
- **Cognitive overhead** — джуну нужно понять структуру 20 сервисов вместо одного репо.

**Когда что брать:**

| Сценарий | Выбор |
|---|---|
| MVP, стартап до 10 разработчиков | **Монолит** |
| Чёткие домены, разные команды, разные NFR | **Микросервисы** |
| Бизнес меняет домен каждый месяц | **Монолит** — границы ещё не устаканились |
| Не хватает DevOps-навыков в команде | **Монолит** |

**Часто оптимальный путь:** монолит → **модульный монолит** (чёткие bounded contexts внутри одного процесса) → **Strangler Fig** распиливание по мере роста.',
                'difficulty' => 3,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Service-Oriented Architecture (SOA)?',
                'answer' => '**SOA (Service-Oriented Architecture)** — архитектурный стиль с **переиспользуемыми сервисами** и **явными контрактами**. Концептуально **предшественник микросервисов**.

**Классическая enterprise-реализация (2000-е):**
- Транспорт — **`SOAP`** + **`XML`** + **`WSDL`** (схема контракта).
- Корпоративная шина — **`ESB`** (Enterprise Service Bus): маршрутизация, **трансформация форматов**, оркестрация, орхестрация бизнес-процессов.
- Реестр сервисов — **UDDI**.

Но это **инструменты эпохи**, не определение SOA. Формально SOA можно строить и на REST/JSON — главное «сервис как переиспользуемая единица с контрактом».

**SOA vs Микросервисы:**

| Аспект | **SOA** | **Микросервисы** |
|---|---|---|
| Размер сервиса | крупный, на bounded context | мелкий, на одну ответственность |
| Координация | **централизованная** через **ESB** | **decentralized**, без центра |
| Транспорт | SOAP/XML типично | REST/JSON, gRPC, события |
| Базы данных | **общая** на несколько сервисов | **своя у каждого** |
| Логика интеграции | в **шине** (smart pipes) | в сервисах (**smart endpoints, dumb pipes**) |
| Governance | централизованный | команда владеет сервисом end-to-end |
| Деплой | часто **общий** релизный цикл | **независимый** на сервис |

**Грубая формула:**
- **SOA** = много сервисов **вокруг одной шины**.
- **Микросервисы** = много **независимых** сервисов с минимальной связью.

**Почему SOA «вышло из моды»:**
- ESB стал bottleneck и SPOF.
- Тяжёлые SOAP/WSDL-контракты усложняли эволюцию.
- Централизованное governance замедляло команды.
- Современные replacement-инструменты (Kafka как dumb pipe, k8s, service mesh) дали микросервисам ту же интеграцию без централизованной шины.',
                'difficulty' => 3,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое 3-х уровневая архитектура?',
                'answer' => '**3-tier architecture** — разделение приложения на **три части** с **жёстким правилом**: каждая часть **общается только со смежной**.

**Три слоя:**

| Слой | Что внутри | Технологии |
|---|---|---|
| **1. Presentation** | UI, фронт, клиентская часть | браузер, мобильное приложение, SPA |
| **2. Business Logic** | бэкенд: контроллеры, сервисы, валидация, бизнес-правила | Laravel, Spring, ASP.NET |
| **3. Data** | хранение | PostgreSQL, MySQL, MongoDB, S3 |

**Правила взаимодействия:**
- **Presentation → Business**: HTTP / WebSocket.
- **Business → Data**: SQL / ORM.
- **Presentation НЕ обращается напрямую к Data** — это базовый запрет архитектуры.

**Важно различать `tier` и `layer`:**

| | **Tier** (классическое 3-tier) | **Layer** (3-layer) |
|---|---|---|
| Уровень разделения | **физический** — 3 разных узла / процесса | **логический** — модули внутри **одного процесса** |
| Пример | отдельный фронт-сервер, отдельный app-сервер, отдельный сервер БД | Blade-view + Controller + Eloquent в одном PHP-FPM процессе |

**Современный веб часто:**
- **3-layer**: один backend-процесс (контроллеры + сервисы + Eloquent) + отдельная БД.
- Строго говоря, это **не классический 3-tier** — но из-за смешения терминологии так часто называют.

**Плюсы:**
- Чёткое разделение ответственности.
- Независимое масштабирование слоёв.
- Замена одного слоя без правки других (поменяли БД — Business не трогаем).

**Минусы:**
- Дополнительная сетевая задержка между tier-ами.
- Усложнение деплоя.
- При неаккуратной реализации — anemic domain model.',
                'difficulty' => 3,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое гексагональная архитектура (Ports and Adapters)?',
                'answer' => '**Hexagonal architecture** (Alistair Cockburn, 2005) — подход, где **доменная логика в центре**, а вокруг **порты** (интерфейсы) и **адаптеры** (конкретные реализации).

**Ключевая идея:** ядро приложения **не знает**:

- **откуда** пришёл запрос (`CLI`, `HTTP`, тест, очередь, scheduler)
- **куда** сохраняются данные (`Postgres`, файл, in-memory, S3)

**Структура:**

- **Domain (центр)** — entity, value objects, бизнес-правила. **Чистый PHP**, без `Eloquent`/`HTTP`
- **Application services** — use-cases, оркестрация
- **Ports (интерфейсы)** — контракты для внешнего мира (`OrderRepository`, `PaymentGateway`, `EmailSender`)
- **Adapters (реализации)** — `PgOrderRepository`, `StripePaymentGateway`, `MailgunEmailSender`

**Driving vs Driven (Primary vs Secondary):**

| | **Driving (primary)** | **Driven (secondary)** |
|---|---|---|
| **Кто инициирует** | внешний мир → приложение | приложение → внешний мир |
| **Примеры** | HTTP-контроллер, CLI-команда, queue-listener | DB-репозиторий, email-sender, payment-gateway |
| **Зависимость** | вызывает port | реализует port |

**Что даёт:**

- **Тестируемость** — подмена адаптеров на in-memory (mock БД, fake email)
- **Можно менять инфраструктуру** не трогая бизнес-логику
- **Несколько входов** — один use-case доступен через HTTP и CLI одновременно
- **Чистая граница** между domain и фреймворком

**Когда брать:**

- Сложная **доменная модель**
- Много **интеграций** с разными внешними системами
- Долгоживущий проект (5+ лет)

**Когда НЕ брать:**

- CRUD-приложение
- Прототип, MVP
- Маленькая команда (overhead изоляции > benefit)

**Связь с другими подходами:** **Onion** Architecture — иерархическая вариация. **Clean Architecture** (Uncle Bob) — обобщение Hexagonal + Onion с явными слоями.',
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
                'answer' => '**Clean Architecture** (Robert «Uncle Bob» Martin, 2012) — **концентрические слои** с **правилом зависимостей**: зависимости направлены **только внутрь**. Внутренний слой **ничего не знает** о внешнем.

**Слои снаружи внутрь:**

| Слой | Что | Зависит от |
|---|---|---|
| **Frameworks & Drivers** | Laravel, Postgres, Stripe SDK, web framework | всего внутреннего |
| **Interface Adapters** | Controllers, Presenters, Repositories | Use Cases + Entities |
| **Use Cases** (Application Business Rules) | application-specific бизнес-логика | только Entities |
| **Entities** (Enterprise Business Rules) | домен, правила бизнеса организации | **ничего** |

**Главное правило (Dependency Rule):**

> **Source code dependencies могут указывать только внутрь.**

`Entities` ничего не знают о `Use Cases`. `Use Cases` ничего не знают о `Controllers`. `Controllers` ничего не знают о Laravel-deployment.

**Что это даёт:**

- **Бизнес-правила (entities, use cases) не зависят** от Laravel, Postgres или REST
- **Можно переключить любой внешний слой** не трогая ядро (с Laravel на Symfony, с MySQL на Postgres)
- **Тестируется без поднятой инфраструктуры**
- **Долго живёт** при смене технологий

**Сравнение с Hexagonal и Onion:**

| | **Hexagonal** | **Onion** | **Clean** |
|---|---|---|---|
| **Год** | 2005 | 2008 | 2012 |
| **Геометрия** | шестиугольник | концентрические кольца | **концентрические + 4 явных слоя** |
| **Иерархия слоёв** | не диктует | да | **да, жёстко** |
| **Правило зависимостей** | через ports | внутрь | **внутрь** |

**Clean = синтез Hexagonal + Onion** + явное разделение Use Cases vs Entities (DDD-влияние).

**Практическое следствие:**

- Use case `RegisterUser` — чистый PHP, тестируется без Laravel
- `UserRepository` — интерфейс в Use Cases, реализация в Frameworks
- Контроллер — **тонкий**, только распаковка запроса и вызов Use Case

**Боль:**

- **Overhead** — много интерфейсов и DTO
- **Для CRUD избыточно** — Active Record (Eloquent) проще
- **Команда должна знать паттерн** — иначе деградирует в обычный MVC

**Когда брать:** сложный домен (банкинг, медицина, ERP), долгоживущий проект, большая команда с разделением слоёв.',
                'difficulty' => 4,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Onion Architecture?',
                'answer' => '**Onion Architecture** (Jeffrey Palermo, 2008) — слои-кольца луковицы с **зависимостями, направленными внутрь**.

**Слои (от центра к периферии):**

| Слой | Что | Зависит от |
|---|---|---|
| **Domain Model** *(центр)* | entities, value objects, domain events | **ничего** |
| **Domain Services** | логика, выходящая за один entity | Domain Model |
| **Application Services** | use-cases, оркестрация | Domain Services + Model |
| **Infrastructure** *(периферия)* | БД, UI, тесты, внешние API | всех внутренних |

**Ключевая идея:**

> **Бизнес-логика стабильна, инфраструктура меняется** — значит **инфраструктура должна зависеть от логики**, а не наоборот.

Postgres сменится через 5 лет, REST → gRPC через 10 лет, но **«заказ должен иметь хотя бы одну позицию»** — правило бизнеса, оно вечное. Зависимости должны это отражать.

**Сравнение с Hexagonal и Clean:**

| | **Hexagonal** | **Onion** | **Clean** |
|---|---|---|---|
| **Геометрия** | шестиугольник | **кольца** | кольца с явными именами слоёв |
| **Симметрия** | **симметричен** (вход = выход) | иерархичен | иерархичен |
| **Структуру слоёв диктует** | нет | **да** | да, ещё строже |
| **Фокус** | интеграции на границе | **изоляция домена** | синтез обоих |

**Domain Services vs Application Services:**

- **Domain Service** — логика, **не принадлежащая** одному entity, но всё ещё **доменная**: `TransferMoneyService` (между двумя `Account`)
- **Application Service** — оркестрация use-case, **не доменная** логика: загрузить, провалидировать, вызвать domain, сохранить

**На практике:**

- **Domain Model** — `final class Order { ... }` без Eloquent, чистый PHP с methods (`addItem`, `calculateTotal`)
- **Domain Services** — `PricingService`, `TaxCalculator`
- **Application Services** — `PlaceOrderUseCase` (вызывает domain + сохраняет через repository interface)
- **Infrastructure** — `EloquentOrderRepository implements OrderRepository`, controllers, Laravel-инфра

**Связь с DDD:** Onion часто используется как **техническое воплощение** Domain-Driven Design — bounded context = одна «луковица».

**Когда брать:** богатая доменная модель (`Order`, `Account`, `Transfer`), долгоживущая система. **Когда НЕ брать:** CRUD с минимальной логикой.',
                'difficulty' => 4,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Anti-Corruption Layer?',
                'answer' => '**Anti-Corruption Layer (ACL)** — прослойка между **двумя bounded contexts** (или твоим доменом и внешним сервисом), которая **транслирует данные** и **не даёт чужой модели «испортить» твою**.

Термин из **DDD** (Eric Evans, 2003). Аналогия: **переводчик на границе** между странами — данные на одном языке/формате идут к тебе, на другом возвращаются.

**Зачем нужен:**

1. **Изоляция от legacy** — старая CRM с косыми именами полей (`full_name`, `client_id`, `contact_email`) не должна засорять твой чистый домен
2. **Защита от изменений внешнего API** — Stripe поменял формат webhook → правишь только ACL, остальной код не трогаешь
3. **Bounded context separation** — у каждого микросервиса своя модель `User`, ACL транслирует между ними
4. **Семантический мостик** — `client_data` в legacy = `Customer` в твоём домене с дополнительной валидацией

**Что внутри ACL:**

- **Mapper** — преобразование DTO ↔ domain object
- **Translator** — семантический перевод (статусы, enum)
- **Adapter** — технический мостик (HTTP-клиент, SOAP-парсер)
- **Validator** — отсев невалидных данных от external

**Когда нужен ACL:**

- **Интеграция с legacy** монолитом / CRM / ERP с устоявшейся моделью
- **Внешний публичный API** (Stripe, Twilio, GitHub API) с непостоянным контрактом
- **Strangler Fig** — новый сервис разговаривает с монолитом только через ACL
- **Между bounded contexts** в монорепо

**Когда НЕ нужен:**

- **Прямой контроль** над обеими сторонами + согласованная модель
- **Простой CRUD** без доменной логики
- **Внутренний API** с гарантированным контрактом

**Пример переводов:**

| Legacy (CRM) | Domain |
|---|---|
| `client_id: 42` | `Customer::id` |
| `full_name: "John Doe"` | `Customer::name` |
| `contact_email: "j@e.com"` | `Customer::email` (после валидации) |
| `status_code: "A"` | `CustomerStatus::Active` (enum) |

**Гранулярность:** ACL может быть **тонкой** (просто mapper) или **толстой** (целый bounded context с собственными moeded entities, ведущий «двойной учёт» — внутреннюю модель и legacy-вид).',
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
                'answer' => '**BFF (Backend for Frontend)** — **отдельный бэкенд для каждого фронта** (`Web`, `iOS`, `Android`, `Watch`). Вместо общего API, пытающегося угодить всем клиентам, делают **узкоспециализированный сервис** под каждый.

**Каждый BFF:**
- Отдаёт **ровно те данные** и **в том формате**, что нужен конкретному клиенту.
- Делает **агрегацию** запросов к нескольким backend-сервисам.
- Решает **серверную часть** Auth/Session/Cookie для своего клиента.

**Зачем нужно:**
- **Web** хочет SSR-ready data + breadcrumbs + SEO.
- **Mobile** хочет компактный JSON, минимум полей, экономия трафика, кеш картинок предварительно сжаты.
- **Watch** — крайне ограниченный формат, только critical fields.
- Один универсальный API под все три → **overfetching** для одного, **N+1 запросов** для другого, **усложнение версионирования**.

**Плюсы:**
- **Меньше overfetching** — каждый клиент получает свой dataset.
- **Проще версионирование** — обновление мобильного приложения не требует ломать веб-контракт.
- **Команды владеют клиентом end-to-end** — front-end и BFF в одной команде.
- **Безопасность** — секреты сервиса не светятся в браузере, агрегация на BFF.

**Минусы:**
- **Дублирование кода** — у каждого BFF своя реализация маппинга / валидации.
- **Лишний hop** — клиент → BFF → backend services (latency).
- **Дополнительная команда / репозиторий** на каждого клиента.

**Когда брать:**
- Несколько разнородных клиентов с **разными требованиями** к данным.
- Большая команда с раздельными mobile / web подкомандами.

**Когда НЕ брать:**
- Один клиент (только web или только mobile).
- Маленькая команда — BFF добавит overhead без выгоды.',
                'difficulty' => 3,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое API Gateway?',
                'answer' => '**API Gateway** — **единая точка входа** для клиентских запросов в **микросервисную** систему. Швейцар на входе: проверяет билет, направляет в нужный зал, считает посетителей.

**Что обычно делает Gateway:**
- **Routing** — `/users/*` → `users-service`, `/payments/*` → `payments-service`.
- **Authentication / Authorization** — проверка `JWT` / `OAuth`-токена в **одном месте**, бэкенды получают уже доверенный `user_id`.
- **Rate Limiting** — `100 req/min` на ключ, защита от DoS.
- **Кэширование** — повторные `GET` отдаются без вызова backend.
- **Aggregation** — собирает ответ из нескольких сервисов и отдаёт клиенту одним JSON-ом (поход не туда — см. BFF, но Gateway тоже может).
- **Versioning** — `/v1/...` → старый сервис, `/v2/...` → новый.
- **TLS termination**, **request/response transformation** (XML ↔ JSON).
- **Логирование** и **distributed tracing** (correlation ID).
- **Circuit breaker** — открыть «выключатель» при отказе сервиса, не валить весь Gateway.

**Популярные реализации:**

| Реализация | Особенности |
|---|---|
| **Kong** | OSS + Enterprise, плагины Lua |
| **Tyk** | OSS, Go, лёгкий |
| **AWS API Gateway** | managed, glue к Lambda |
| **Google Apigee** | enterprise, аналитика |
| **Nginx + конфиги** | самый простой DIY |
| **Envoy** | low-level, основа service mesh (Istio) |
| **Spring Cloud Gateway** | для Java-стека |

**API Gateway vs BFF:**
- **Gateway** — **универсальный** вход для всех клиентов, **тонкий** слой.
- **BFF** — **специализированный** для **одного типа клиента**, может содержать **бизнес-логику маппинга**.

**Подвох — SPOF:** Gateway = единая точка отказа, нужен **HA** (несколько инстансов за load-balancer), мониторинг и быстрый rollback.',
                'difficulty' => 3,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое reverse proxy и для чего он нужен?',
                'answer' => '**Reverse proxy** — сервер-посредник **перед твоим приложением**. Принимает запросы клиентов и передаёт их в бэкенд.

Аналогия: секретарь — принимает звонки и переводит их на нужного сотрудника. Снаружи виден только секретарь.

**Зачем нужен:**

- **TLS termination** — `nginx` принимает `HTTPS`, в бэкенд идёт уже `HTTP` (одно место для сертификатов)
- **балансировка нагрузки** между несколькими инстансами приложения
- **кэш** статики и иногда динамики
- **защита**: rate limiting, базовый WAF, скрытие внутренней инфраструктуры
- **отдача статики** напрямую, не дёргая `PHP-FPM`
- **graceful deploy**: можно перезапускать бэкенд, прокси держит соединения

**Популярные:** `nginx`, `HAProxy`, `Caddy`, `Traefik`, `Envoy`.

Снизу — типовой конфиг `nginx` перед Laravel-приложением на `127.0.0.1:8000`.',
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
                'answer' => '**Главное различие — кого скрывает прокси и со стороны кого он стоит.**

| Аспект | **Forward proxy** | **Reverse proxy** |
|---|---|---|
| Где стоит | **перед клиентом** | **перед сервером** |
| Кого скрывает | **клиента** от сервера | **сервер** от клиента |
| Что видит клиент | сам прокси (явная настройка в браузере) | прокси как **сам сервер** |
| Что видит сервер | прокси как «клиента» | реального клиента (через `X-Forwarded-For`) |
| Метафора | «я хожу через посредника» | «ко мне приходят через посредника» |

**Forward proxy — типичные сценарии:**
- **Корпоративный прокси** для выхода в интернет (контроль доступа, фильтрация, кеш).
- **VPN / SOCKS** — обход геоблоков, анонимизация.
- **Школьный/офисный** контент-фильтр.
- Клиент **знает** про прокси и явно его настраивает.

**Reverse proxy — типичные сценарии:**
- **Nginx / HAProxy** перед PHP-FPM (Laravel).
- **TLS termination** — HTTPS на прокси, HTTP внутрь.
- **Load balancing** между несколькими бэкендами.
- **CDN** (Cloudflare, Akamai) — это тоже reverse proxy на стероидах.
- **Скрытие** внутренней инфраструктуры от внешнего мира.
- Клиент **не знает** про прокси — для него это «сам сервер».

**Software часто тот же:** `nginx`, `HAProxy`, `Squid`, `Envoy` могут работать в **обоих** режимах — разница только в конфиге.

**Распространённая ошибка:** называть «reverse proxy» любой прокси. Чтобы запомнить — посмотри, **со стороны кого** он стоит: forward = со стороны клиента, reverse = со стороны сервера.',
                'difficulty' => 3,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое load balancer и зачем он нужен?',
                'answer' => '**Load balancer** распределяет входящие запросы между **несколькими серверами**, скрывая их за **единым адресом**. **Одна вывеска перед группой серверов.**

**Три ключевые задачи:**

1. **Горизонтальное масштабирование** — добавил backend-узел, балансировщик начал слать на него запросы. Клиенты ничего не знают.
2. **Отказоустойчивость** — **health checks** периодически дёргают `/healthz`. Узел отвечает медленно или 500-кой → **выводится из ротации**.
3. **Удобный entrypoint** для **TLS termination**, **rate limiting**, **WAF**, метрик трафика.

**Типы по уровню OSI:**

| Тип | Где работает | Что видит | Примеры |
|---|---|---|---|
| **L4** | TCP/UDP | IP, порт | **HAProxy**, **AWS NLB**, IPVS |
| **L7** | HTTP / HTTPS | заголовки, путь, host | **Nginx**, **Envoy**, **AWS ALB**, **Traefik** |

**Что даёт L7:**
- **Routing по path/host**: `/api/*` → API-сервер, `/static/*` → CDN.
- **Canary deployment**: 5% трафика на новую версию.
- **Sticky session** через cookie.
- **Cache** ответов.

**Аппаратные** — `F5 BIG-IP`, `Citrix ADC` — дорого, для крупного enterprise; SaaS-альтернатива — Cloud LB (AWS ALB/NLB/CLB, GCP LB, Azure LB).

**Алгоритмы балансировки** (`round-robin`, `least-connections`, `IP hash`, `power of two choices`, `weighted`) — см. карточку в performance.

**Подвох:**
- **SPOF** — сам балансировщик. Решение: **HA pair** (active/passive с keepalived) или managed cloud LB.
- **Connection draining** при выводе узла — дать **долгоживущим WebSocket / SSE** соединениям закрыться gracefully.',
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
                'answer' => '**Sticky session** (он же **session affinity**) — балансировщик **привязывает** пользователя **к одному и тому же серверу** для всех его запросов. Один раз попал на сервер №2 → дальше всегда туда же.

**Зачем нужно:**
- **Сессии в памяти процесса** (`session.driver = file` / `array`) — кэшируются на конкретном сервере, недоступны другим.
- **WebSocket / SSE** соединения — установлены с конкретным процессом, переключение бессмысленно.
- **In-memory caches** на узле, прогретые под конкретного юзера.

**Как реализуется:**
- **Cookie-based** — балансировщик ставит свою cookie (`AWSALB`, `NGX_ROUTE`) с ID узла; следующий запрос направляется по cookie.
- **IP hash** — `hash(client_ip) → server`. Просто, но при NAT/CGNAT много клиентов залипает на одном узле.
- **Session ID extraction** — балансировщик читает session-cookie приложения и матчит по таблице.

**Минусы:**
- **Неравномерная нагрузка** — если активные юзеры залипли на одном узле, он перегружен.
- **Hot spots** — VIP-клиент с DDoS-объёмом запросов всё бьёт в один сервер.
- **При падении узла** пользователь теряет сессию.
- **Сложнее масштабировать** — новые узлы получают только новый трафик, старые перегружены.
- **Deploy без потерь** усложнён — нужен `connection draining`.

**Best practice — не нужно sticky:**
- Сессии в **общем хранилище** (`Redis`, `Memcached`, `DynamoDB`).
- В Laravel: `SESSION_DRIVER=redis` (или `database`).
- Любой узел обслуживает любого пользователя — балансировка справедливая.

**Когда sticky всё-таки нужен:**
- **WebSocket** при отсутствии shared pubsub.
- **Legacy** монолит с сессиями в файлах.
- Real-time multiplayer game-сервера (один матч = один процесс).',
                'difficulty' => 3,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Saga: чем отличается choreography (хореография) от orchestration (оркестрация)?',
                'answer' => '**Saga** — паттерн **распределённых транзакций**, где локальные транзакции каждого сервиса связываются цепочкой, и при сбое одного шага запускаются **compensating actions** (обратные операции). Распределённый ACID невозможен — Saga даёт **eventual consistency** с компенсациями.

Реализуется в **двух стилях**:

| | **Choreography** (хореография) | **Orchestration** (оркестрация) |
|---|---|---|
| **Координатор** | **нет**, каждый сервис сам решает | **центральный orchestrator** |
| **Связь** | через события (event bus) | через команды от orchestrator |
| **State machine** | размазана по сервисам | явная, в orchestrator |
| **Дебаг** | сложный (нужен distributed tracing) | проще (state в одном месте) |
| **SPOF** | нет | orchestrator (нужен HA) |
| **Связность** | слабая | сильная (все знают orchestrator) |
| **Подходит для** | 2-3 шага, простой flow | 5+ шагов, ветвления, retry |

**Choreography (хореография):**

Каждый сервис **слушает события** и решает сам, что делать.

```
Order создан → publish OrderCreated
  → Payment слушает → списывает → publish PaymentCharged
  → Inventory слушает → резервирует → publish InventoryReserved
  → Shipping слушает → планирует → publish OrderConfirmed
```

При сбое сервис publish-ит **компенсирующее событие** (`PaymentFailed`), на которое подписаны все, кому нужно откатить свою часть.

**Плюсы:** loose coupling, нет SPOF, легко добавлять новых участников
**Минусы:** бизнес-процесс **размазан** по сервисам, трудно понять текущее состояние саги, легко получить **циклы** и неявные зависимости

**Orchestration (оркестрация):**

Центральный **orchestrator** явно вызывает шаги и обрабатывает результаты, ведя state machine.

```
OrderSagaOrchestrator:
  ChargePaymentCommand → response
  ReserveInventoryCommand → response
  ScheduleShippingCommand → response
  → SagaCompleted

При сбое — компенсации в ОБРАТНОМ порядке:
  CancelShipping → ReleaseInventory → RefundPayment
```

**Плюсы:** бизнес-логика в **одном месте**, явная state machine, проще debug
**Минусы:** orchestrator — SPOF и узкое место по нагрузке, сильная связь сервисов с orchestrator

**Когда что:**

- **Choreography** — простые саги из 2-3 шагов, event-driven архитектура, нет центрального владельца процесса
- **Orchestration** — сложные саги (5+ шагов, ветвления, retry-логика), регулируемые домены (банкинг, страховка), нужен audit trail

**Реализации:**

| Тип | Инструменты |
|---|---|
| **Orchestration** | **Temporal**, **Camunda**, **AWS Step Functions**, **Netflix Conductor** |
| **Choreography** | `Kafka`/`RabbitMQ` + **Transactional Outbox** + idempotent consumers |

**Outbox pattern** обязателен для choreography — гарантирует, что event опубликуется **iff** локальная транзакция закоммитилась.',
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
                'answer' => '**Strangler Fig** (Strangler Application) — паттерн от **Мартина Фаулера** (2004) для **постепенной замены** legacy-системы новой, без полного переписывания **«всё с нуля»**.

**Почему не Big Bang rewrite:**

- **Scope creep** — оригинальная функциональность за годы обросла кейсами, которых нет в спеке
- **Потерянная бизнес-логика** — недокументированные edge cases
- **Год без релизов** — бизнес против
- **Все провалы 2nd-system effect** (Joel Spolsky: «Things You Should Never Do, Part I»)

**Аналогия из природы:** фиговое дерево **обвивает** старое дерево, постепенно «душит» его и в конце концов занимает его место. В коде так же: новая система **растёт вокруг старой**, забирая по одной функции, пока legacy не остаётся пустой оболочкой.

**Механика:**

1. **Фасад перед монолитом** — API Gateway, reverse proxy (`nginx`/`HAProxy`/`Envoy`), service mesh, или роутер фреймворка
2. **Все запросы пока идут в монолит** как обычно
3. **Новый сервис** с **одной** функциональностью (например, «регистрация пользователей»)
4. **На фасаде роут переключается** — `POST /users` → новый сервис, остальное → монолит
5. **Шаг повторяется** — один эндпоинт, один use-case, один bounded context. **Месяцами и годами**
6. **В конце монолит выключается**

**Плюсы:**

- **Постоянная работа с прода** — можно откатить любой шаг
- **Бизнес продолжает релизить** фичи параллельно
- **Снижение риска** — на любом этапе можно остановиться
- **Легко обосновать бизнесу** (incremental value)

**Подводные камни:**

| Проблема | Решение |
|---|---|
| **Общая БД** — новый сервис читает монолитную БД | Anti-Corruption Layer / replicated read-store через **CDC** (Debezium) |
| **Auth/сессии** | Единый Auth-сервис, JWT для обоих |
| **Distributed transactions** | **Saga + Outbox** вместо одной DB-транзакции |
| **Дольше живёт суммарно** | Принимаем — риск меньше Big Bang |
| **Дисциплина команды** | Запрет добавлять фичи в legacy после старта удушения |

**Branch-by-abstraction (внутри монолита):**

1. Интерфейс `UserService` (абстракция)
2. Две реализации: `LegacyUserService` (DB monolith) и `RemoteUserService` (HTTP к новому)
3. **Feature flag** (`Laravel Pennant`) переключает между ними
4. Сначала `1%` юзеров на Remote, потом `50%`, потом `100%`
5. Удаляется `LegacyUserService`

**Anti-corruption layer (DDD):** новый сервис общается со старым через **адаптер**, который переводит legacy data model в чистую domain-модель нового сервиса. **Legacy не загрязняет** новый код своей семантикой.

**Когда НЕ работает Strangler:**

- **Монолит без явных границ** — невозможно вырезать один use-case
- **Сильно связанная общая БД** с FK везде
- **Нет фасада на входе** (нельзя поставить proxy)
- **Команда не дисциплинирована** — фичи продолжают писаться в монолит',
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
                'answer' => 'Обе про **изоляцию домена от инфраструктуры**, но **фокус** разный.

| Аспект | **Hexagonal (Ports & Adapters)** | **Onion** |
|---|---|---|
| Геометрия | **шестиугольник** с входами/выходами | **концентрические слои-кольца** |
| Основная идея | **порты** (интерфейсы) + **адаптеры** (реализации) на границе | строгая иерархия слоёв с правилом зависимости **внутрь** |
| Внутреннюю структуру | **не диктует** | **жёстко предписывает** (Domain → Domain Services → Application → Infrastructure) |
| Симметрия | **симметричен**: вход и выход равноправны | **иерархичен**: доменное ядро в центре |
| Кто автор | Alistair Cockburn, 2005 | Jeffrey Palermo, 2008 |

**Ключевые акценты:**

- **Hexagonal**: «приложение не должно знать, **через что** пришёл запрос (HTTP, CLI, тест) и **куда** сохранили данные (Postgres, файл, in-memory)». Все взаимодействия с внешним миром — через **порты**, реализации — **адаптеры**.
- **Onion**: «**бизнес-логика стабильна, инфраструктура меняется** — значит инфраструктура зависит от логики, не наоборот». Зависимости **всегда направлены внутрь**.

**Что общего:**
- **Dependency Inversion** во главе угла.
- Доменная модель **не знает** про Laravel/SQL/HTTP.
- Тестируется без поднятой БД, через подмену адаптеров.
- Тесно связаны с **DDD** и **Clean Architecture** (Clean — обобщение обеих).

**Когда что:**
- **Hexagonal** — когда фокус на **интеграции** с разными внешними системами (несколько входов: HTTP API + CLI + worker + scheduler; несколько выходов: SQL + S3 + email + SMS).
- **Onion** — когда фокус на **сложной доменной модели** с богатыми entity и domain services.

**На практике** — часто комбинируют: Onion-слои + Hexagonal-порты на границах. **Clean Architecture** Дяди Боба — синтез обоих.',
                'difficulty' => 3,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'В чём разница между функциональными и нефункциональными требованиями к системе?',
                'answer' => 'Два **дополняющих** типа требований к системе.

| | **Functional Requirements** | **Non-Functional Requirements (NFR)** |
|---|---|---|
| Описывают | **что** система делает | **как** система работает |
| Формулировка | сценарии и поведение | характеристики и качества |
| Влияние | **доменная модель**, API | **архитектура**, инфраструктура |
| Пример | «пользователь бронирует самокат» | «p95 latency < 200ms при 10k RPS» |

**Функциональные требования:**
- Конкретные **use-cases**: «зарегистрироваться», «оплатить заказ», «экспортировать отчёт в CSV».
- Описываются через user stories, формальные сценарии (Given/When/Then), API-контракты.
- Проверяются **functional / acceptance** тестами.

**Нефункциональные требования (NFR) — категории:**
- **Performance** — latency, throughput (RPS, TPS).
- **Scalability** — поддержать 10x нагрузки, горизонтально / вертикально.
- **Availability** — uptime: 99.9% (8.76h downtime/год), 99.99% (52min/год), 99.999% (5min/год).
- **Reliability** — частота сбоев, MTBF / MTTR.
- **Security** — шифрование, RBAC, OWASP-защита.
- **Maintainability** — насколько легко поменять / задеплоить.
- **Observability** — логи, метрики, tracing.
- **Compliance** — GDPR, PCI-DSS, HIPAA, ISO 27001.
- **Cost** — бюджет на хостинг / лицензии.

**Почему различение критично:**
- **Архитектура определяется NFR**, не функционалом. «Лента постов» в Twitter и небольшом блоге функционально похожа, но NFR (миллиарды постов, латентность <100ms) требуют совершенно разных архитектур.
- **Скрытые требования** к производительности и доступности — частая причина провала проектов: «работало в dev, легло в проде».

**Best practice — фиксировать NFR измеримо:**
- ❌ «быстро работает» → ✅ «p95 < 300ms, p99 < 1s».
- ❌ «надёжно» → ✅ «доступность 99.95%, RPO < 5min, RTO < 30min».',
                'difficulty' => 3,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое service mesh?',
                'answer' => '**Service mesh** — инфраструктурный слой для взаимодействия микросервисов. **Прокси (sidecar) рядом с каждым сервисом** перехватывает весь сетевой трафик и предоставляет **сетевые возможности из коробки**, **без изменений в коде сервисов**.

**Идея:** «общий сетевой стек» для всех сервисов **вынесен в инфраструктуру** — каждый сервис общается с локальным `127.0.0.1:proxy`, а тот разруливает всё остальное.

**Архитектура:**

| Plane | Что | Примеры |
|---|---|---|
| **Data plane** | sidecar-прокси рядом с каждым сервисом | **`Envoy`**, `Linkerd2-proxy` |
| **Control plane** | управление всеми sidecar-ами, политики | `Istio`, `Linkerd`, `Consul Connect` |

**Что даёт без изменений кода:**

- **`mTLS`** — взаимная TLS-аутентификация между всеми сервисами (zero-trust сеть)
- **Retry с backoff** — авто-повтор failed запросов
- **Circuit breaker** — отрубить мёртвый сервис
- **Timeout enforcement** — ни один запрос не висит больше N
- **Rate limiting** между сервисами
- **Traffic splitting** — `90%` в `v1`, `10%` в `v2` (canary)
- **Distributed tracing** — авто-инжект `traceparent`
- **Observability** — метрики L4/L7 без инструментирования кода
- **Access policies** — `service A` может вызывать `B`, но не `C`

**Популярные реализации:**

| | **Istio** | **Linkerd** | **Consul Connect** |
|---|---|---|---|
| **Sidecar** | Envoy | linkerd2-proxy (Rust) | Envoy |
| **Сложность** | **высокая** | **низкая** | средняя |
| **Performance** | средняя | **лучшая** | средняя |
| **Features** | **максимум** | минимум, но всё нужное | средне |
| **Ambient mode** (без sidecar) | да (новое) | нет | нет |

**Плюсы:**

- **Полиглот** — `Java`, `Go`, `PHP`, `Python` получают одинаковые capabilities
- **Centralized policy** — security/traffic правила в одном месте
- **Zero-trust** в сети

**Минусы:**

- **Overhead** — каждый запрос идёт через sidecar (`+1-3ms` latency, `+CPU/memory`)
- **Сложность** — управление mesh-ом сравнимо с управлением микросервисами
- **Debugging** — добавляется ещё один hop
- **Cost** — `100 pods × 100 МБ sidecar = 10 ГБ` overhead

**Когда брать:**

- **20+ микросервисов** на разных языках
- **Zero-trust security** требование
- **Compliance** требует mTLS

**Когда НЕ брать:**

- Монолит / `<10` сервисов
- Один язык — проще библиотека (`Polly` в .NET, `Resilience4j` в Java)
- Маленькая команда без SRE

**Ambient mode (Istio 2024+):** новый подход **без sidecar-ов** — общий ztunnel на ноду + waypoint proxy для L7. Снижает overhead, упрощает upgrade.',
                'difficulty' => 5,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое event-driven architecture?',
                'answer' => '**Event-Driven Architecture (EDA)** — сервисы общаются через **события вместо прямых вызовов**. Один сервис **публикует** событие, остальные подписываются и реагируют асинхронно.

Аналогия: **публикация новостей в газете** вместо телефонных звонков. Издатель не знает, кто читает; читатели сами решают, на что реагировать.

**Поток:**

```
Order Service → publish OrderCreated event → Event Bus (Kafka/RabbitMQ)
                                                ↓
                              ┌─────────────────┼─────────────────┐
                              ↓                 ↓                 ↓
                       Inventory Service  Payment Service   Email Service
                       (резервирует)      (списывает)       (шлёт письмо)
```

**Виды событий:**

| Тип | Что | Пример |
|---|---|---|
| **Domain events** | факт изменения в домене | `OrderCreated`, `PaymentReceived` |
| **Integration events** | для внешних подписчиков | `OrderShipped` (с PII фильтрацией) |
| **Event-carried state transfer** | событие содержит **полный snapshot** | весь Order в payload |
| **Event notification** | только указатель «иди забери» | `id=42 changed` |

**Стили event-driven:**

| Стиль | Что | Когда |
|---|---|---|
| **Publish-Subscribe** | broadcast, fire-and-forget | уведомления, audit log |
| **Event Sourcing** | event store как source of truth | финансы, аудит, time-travel |
| **CQRS + events** | разделение write/read через события | сложные read-views |
| **Saga (choreography)** | распределённые транзакции через события | мульти-сервис flow |
| **Event streaming** | log-based processing (Kafka) | analytics, ML, real-time |

**Плюсы:**

- **Loose coupling** — publisher не знает о subscribers
- **Легко добавить нового подписчика** — без правок publisher-а
- **Асинхронность** — publisher не ждёт consumers
- **Scalability** — каждый consumer масштабируется независимо
- **Replay** — Kafka позволяет переиграть события (debug, миграция)
- **Resilience** — упавший consumer догонит из очереди

**Минусы:**

- **Сложнее дебажить** — нет stack trace через всю систему
- **Eventual consistency** — данные **временно** несогласованны
- **Нужна observability** для трассировки (`distributed tracing`)
- **Out-of-order delivery** — event B может прийти раньше A
- **Duplicate delivery** — at-least-once требует idempotent consumers
- **Schema evolution** — изменение event-payload ломает старых consumers

**Обязательные практики:**

- **Transactional Outbox** — гарантия, что event опубликуется **iff** локальная транзакция закоммитилась
- **Idempotent consumers** — повторная обработка не ломает state
- **Event versioning** — `OrderCreatedV1`, `OrderCreatedV2`, schema registry
- **Distributed tracing** — `traceparent` пробрасывается через события
- **Dead Letter Queue (DLQ)** — для невозможно обработанных событий

**Инструменты:**

- **Kafka** — log-based, replay, высокий throughput
- **RabbitMQ** — message broker, гибкие routing rules
- **AWS EventBridge** / **GCP Pub/Sub** — managed
- **NATS** — лёгкий, высокая скорость
- **Redis Streams** — если Redis уже есть

**Когда брать:** микросервисы с асинхронными flow, sporadic high load, нужна resilience. **Когда НЕ брать:** простой sync flow с двумя сервисами — REST/gRPC проще.',
                'difficulty' => 4,
                'topic' => 'system_design.architecture',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое MVC простыми словами?',
                'answer' => '**Model-View-Controller** — паттерн, который делит приложение на **3 слоя** с чёткими ролями:

- **Model** — **данные и бизнес-логика**: что такое `User`, `Order`, как они сохраняются в БД, как считаются.
- **View** — **представление**, то что видит пользователь: HTML-страница, JSON-ответ для API.
- **Controller** — **связующее звено**: принимает HTTP-запрос, дёргает нужную Model, передаёт результат во View.

Идея — **каждый слой отвечает за своё**: можно менять UI не трогая логику и наоборот, и тестировать слои отдельно.

Большинство веб-фреймворков (**Laravel**, **Symfony**, **Rails**, **ASP.NET MVC**) построены вокруг этого паттерна. В Laravel поток такой:

маршрут → метод контроллера в `app/Http/Controllers` → модель Eloquent в `app/Models` → шаблон **Blade** или JSON-ресурс',
                'code_example' => '// Controller — принимает запрос, дёргает Model, отдаёт View
class UserController
{
    public function show(int $id)
    {
        $user = User::findOrFail($id);          // Model
        return view("users.show", ["user" => $user]); // View
    }
}',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'system_design.architecture',
            ],
        ];
    }
}
