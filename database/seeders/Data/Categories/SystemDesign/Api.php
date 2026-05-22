<?php

namespace Database\Seeders\Data\Categories\SystemDesign;

class Api
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое API простыми словами?',
                'answer' => '**API** (**Application Programming Interface**) — это **контракт, по которому одна программа просит что-то у другой**.

Аналогия: **розетка**. Ты не знаешь, как устроена электростанция, но знаешь форму вилки и напряжение — и этого хватает.

**Веб-API** обычно работает по **HTTP**:

- клиент шлёт запрос на URL с **методом** (`GET`/`POST`/`PUT`/`DELETE`) и телом
- сервер отвечает данными в **JSON** и **статус-кодом** (`200`, `404`, `500`)

Бывает разный по доступности:

- **публичный** — открыт всем, например GitHub API
- **приватный** — внутри компании, для своего фронта/мобилки
- **партнёрский** — по договору с конкретными клиентами

Главное: **API скрывает внутренности и даёт стабильный интерфейс** — внутри можно переписать что угодно, клиент не сломается.',
                'code_example' => 'GET /api/users/42 HTTP/1.1
Host: api.example.com
Authorization: Bearer abc123

HTTP/1.1 200 OK
Content-Type: application/json

{"id":42,"name":"Vasya","email":"v@example.com"}',
                'code_language' => 'http',
                'difficulty' => 1,
                'topic' => 'system_design.api',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Из чего состоит HTTP-запрос и ответ?',
                'answer' => 'И запрос, и ответ состоят из **четырёх частей одинаковой структуры**.

**Запрос**:

1. **Стартовая строка** — метод + путь + версия протокола: `POST /api/users HTTP/1.1`
2. **Заголовки** — `Host`, `Authorization`, `Content-Type`, `Content-Length`
3. **Пустая строка-разделитель**
4. **Тело** — опционально, обычно у `POST`/`PUT`/`PATCH`

**Ответ**:

1. **Статусная строка** — версия + код + текст: `HTTP/1.1 201 Created`
2. **Заголовки** — `Content-Type`, `Cache-Control`, `Set-Cookie`, `Location`
3. **Пустая строка**
4. **Тело** с данными — JSON, HTML, картинка

Ключевое: **пустая строка отделяет заголовки от тела** — её видят и браузер, и `curl`, и Postman, и любой HTTP-клиент.',
                'code_example' => 'POST /api/users HTTP/1.1
Host: api.example.com
Authorization: Bearer abc123
Content-Type: application/json
Content-Length: 25

{"name":"Vasya","age":30}

HTTP/1.1 201 Created
Content-Type: application/json
Location: /api/users/42

{"id":42,"name":"Vasya"}',
                'code_language' => 'http',
                'difficulty' => 1,
                'topic' => 'system_design.api',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое REST и какие у него принципы?',
                'answer' => '**REST** (Representational State Transfer) — архитектурный стиль API поверх HTTP, описанный Roy Fielding (диссертация 2000 г.).

**Базовая идея:** **ресурсы** обозначаются **URL** (существительные, `/users/42`), а **действия** над ними — **HTTP-методами** (`GET`/`POST`/`PUT`/`PATCH`/`DELETE`).

**6 принципов:**

1. **Stateless** — сервер не хранит состояние клиента между запросами, каждый запрос самодостаточен (с токеном внутри)
2. **Client-Server** — клиент и сервер развязаны, могут эволюционировать независимо
3. **Cacheable** — ответы должны явно говорить, можно ли их кэшировать (`Cache-Control`, `ETag`)
4. **Uniform Interface** — единый набор операций: стандартные методы, URL, статус-коды
5. **Layered System** — между клиентом и сервером могут стоять прокси/CDN/балансировщики, незаметно для клиента
6. **Code on Demand** — опционально: сервер может присылать исполняемый код (JS)

**Правила хорошего REST API:**

- URL — **существительные** во множественном числе (`/users`, не `/getUsers`)
- **HTTP-методы** = действия (`POST /users`, не `POST /createUser`)
- **статус-коды** несут смысл (`201` после `POST`, `404` если нет, `422` при валидации)
- **JSON** в теле, версионирование (`/api/v1/...`)',
                'code_example' => 'GET    /api/users          # список
GET    /api/users/42       # один пользователь
POST   /api/users          # создать (тело — JSON)
PUT    /api/users/42       # полная замена
PATCH  /api/users/42       # частичное обновление
DELETE /api/users/42       # удалить

# Связанные ресурсы — вложенные URL
GET    /api/users/42/orders        # заказы пользователя 42
POST   /api/users/42/orders        # создать заказ для пользователя 42

# Фильтрация, сортировка, пагинация — через query
GET    /api/users?role=admin&sort=-created_at&page=2',
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'system_design.api',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'В чём разница между REST, GraphQL и gRPC?',
                'answer' => 'Три **разные парадигмы** общения с API.

| | **REST** | **GraphQL** | **gRPC** |
|---|---|---|---|
| **Транспорт** | HTTP/1.1 + JSON | HTTP + JSON | **HTTP/2 + Protobuf** (binary) |
| **Модель** | ресурсо-ориентированная (URL = существительное) | один эндпоинт `/graphql`, **клиент выбирает поля** | RPC: вызов метода с типизированными аргументами |
| **Контракт** | OpenAPI/Swagger (отдельно) | SDL — **typed schema** | `.proto` + кодогенерация |
| **Кэш** | штатный HTTP-кэш по URL | **не работает** (POST) — нужен Persisted Queries | нет встроенного |
| **Over/under-fetching** | бывает | **нет** — клиент берёт что нужно | по контракту |
| **Streaming** | SSE/WebSocket отдельно | subscriptions через WS | **встроенный** (4 типа RPC) |
| **Browser support** | нативный | нативный | **нужен `grpc-web`** (overhead) |
| **Tooling** | огромная экосистема | хорошая | отличный, кодген на 11+ языков |

**Когда что выбирать:**

- **REST** — публичные API, CRUD, кэширование на CDN, простая интеграция, документация через OpenAPI
- **GraphQL** — **BFF** для богатого UI (web/mobile с разными view), множество источников данных, развязка фронта от бэка по releases
- **gRPC** — **межсервисное** общение в микросервисах: низкая задержка, бинарный формат, четкий контракт, streaming

**Подводные камни:**

- **GraphQL** даёт клиенту мощь, но без `Query Complexity Analysis` один запрос может вытащить полбазы (защита: `max depth`, cost limits, persisted queries)
- **gRPC** требует `grpc-web` для браузера (прослойка с overhead), сложнее дебажить (бинарь), нужны протоколы версионирования `.proto`
- **REST** без HATEOAS легко превращается в кашу из ad-hoc эндпоинтов вроде `POST /createUser`

**Внутренние сервисы — `gRPC`. Публичный API и интеграции — `REST`. Богатый клиент с переменными view — `GraphQL`.**',
                'code_example' => '# REST: классический CRUD
GET    /api/users/42                          → {"id":42,"name":"Vasya","email":"v@..."}
POST   /api/users    {"name":"Petya",...}     → 201, Location: /api/users/43

# GraphQL: клиент выбирает поля
POST /graphql
{
  "query": "{ user(id:42) { name posts(limit:5) { title createdAt } } }"
}
# → { "data": { "user": { "name":"Vasya", "posts":[...] } } }

# gRPC: .proto контракт
syntax = "proto3";
service UserService {
  rpc GetUser (UserRequest) returns (UserReply);
  rpc StreamUsers (Empty) returns (stream UserReply);  // server streaming
}
message UserRequest { int32 id = 1; }
message UserReply { int32 id = 1; string name = 2; string email = 3; }

# → grpc-генератор клиента для PHP/Go/Java/etc',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.api',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое gRPC и Protocol Buffers?',
                'answer' => '**gRPC** — RPC-фреймворк от **Google** поверх **HTTP/2**. **Protocol Buffers (protobuf)** — бинарный формат сериализации с **`.proto`-схемами**.

**Как работает:**

1. Описываешь сервис в `.proto`-файле (контракт)
2. `protoc` генерирует **клиентский и серверный код** для нужного языка
3. Клиент вызывает метод как обычную функцию — gRPC сериализует в protobuf, шлёт по HTTP/2
4. Сервер десериализует, выполняет, отвечает

**Что даёт HTTP/2 (vs HTTP/1.1 REST):**

- **Multiplexing** — много запросов в одном TCP-соединении
- **Binary framing** — компактнее текста
- **Header compression** (`HPACK`)
- **Server push** и **streaming**

**Сравнение с JSON:**

| | **JSON** | **Protobuf** |
|---|---|---|
| **Размер** | 100 байт | **5-10x меньше** |
| **Парсинг** | strings | **бинарь, типы из схемы** |
| **Скорость** | медленный | **в разы быстрее** |
| **Читаемость** | **да** | нет (нужен `.proto`) |
| **Schema** | необязательна | **обязательна** |
| **Браузер** | везде | **нужен `grpc-web`** |

**4 типа методов в gRPC:**

| Тип | Поток |
|---|---|
| **Unary** | один запрос → один ответ |
| **Server streaming** | один запрос → поток ответов (например, live updates) |
| **Client streaming** | поток запросов → один ответ (например, upload) |
| **Bidirectional streaming** | поток ↔ поток (например, chat) |

**Плюсы:**

- **Размер и скорость** — критично для микросервисов с миллионами RPS
- **Типизированные контракты** — компилятор ловит несовместимость на ранней стадии
- **Кодогенерация** на **11+ языков** — Go, Java, Python, C++, Rust, PHP, JS
- **Backward/forward compatibility** через **поля с тегами** (`int32 id = 1;`)
- **Streaming** из коробки

**Минусы:**

- **Бинарь** — сложнее дебажить (`grpcurl`, `BloomRPC`)
- **Браузер** — нужен **`grpc-web`** + прокси
- **Кэширование на HTTP-уровне не работает** (POST + binary)
- **Тулинг беднее** REST (`OpenAPI`, `Postman`)
- **Не для публичных API** — обычно internal

**Когда брать:**

- **Внутреннее общение** микросервисов
- **Высокий throughput** (миллионы RPS)
- **Streaming** (live data, file upload)
- **Полиглотная** экосистема с typed contracts

**Когда НЕ брать:**

- **Публичный API** для веба
- **Простой CRUD** между двумя сервисами
- Маленькая команда без infra-поддержки',
                'code_example' => 'syntax = "proto3";
service UserService {
  rpc GetUser (UserRequest) returns (UserReply);
}
message UserRequest { int32 id = 1; }
message UserReply { string name = 1; string email = 2; }',
                'code_language' => 'bash',
                'difficulty' => 4,
                'topic' => 'system_design.api',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Какие способы версионирования API существуют?',
                'answer' => 'Пять способов с разными trade-offs:

| Способ | Пример | Плюсы | Минусы |
|---|---|---|---|
| **URI versioning** | `/api/v1/users` | просто, видно сразу, легко кэшировать | нарушает REST-идею «URL = ресурс» (Roy Fielding критикует) |
| **Media type** (`Accept`) | `Accept: application/vnd.myapi.v2+json` | чище URL, **HATEOAS-friendly** | скрытнее, сложнее тестировать в браузере |
| **Custom header** | `API-Version: 2` | URL стабилен | нестандартно |
| **Query parameter** | `/api/users?version=2` | гибко | не каноничен, **плохо кэшируется** |
| **Subdomain** | `v1.api.example.com` | разделение инфры | DNS overhead, CORS |

**На практике чаще всего — URI versioning** (Twitter, Stripe, GitHub). Просто и читаемо.

**Strategy patterns:**

**1. Deprecation через headers (RFC 8594):**

```http
HTTP/1.1 200 OK
Deprecation: true
Sunset: Sat, 31 Dec 2026 23:59:59 GMT
Link: </api/v3/users>; rel="successor-version"
```

Клиент видит, что версия устаревает, и до какой даты будет жить.

**2. Semantic Versioning для контракта:**

- **MAJOR** — breaking changes (новый endpoint, удалили поле) → новая `/v2/`
- **MINOR** — добавили опциональное поле (backward compatible) → ту же версию
- **PATCH** — баги, документация → ту же версию

**3. Параллельная поддержка:**

- **6–12 месяцев** старая версия живёт параллельно с новой
- Метрики **usage** старой версии (Datadog/Prometheus) — смотреть, кто ещё ходит
- Уведомления крупным клиентам **за квартал** до отключения

**4. Внутреннее versioning через flags:**

```php
if ($request->header("API-Version") >= 2) {
    return new UserResourceV2($user);
}
return new UserResourceV1($user);
```

**Грабли:**

- **Версионирование "на каждое изменение"** — через год у тебя `v15`, никто не помнит, что в каждой
- **Breaking changes без version bump** — клиенты ломаются молча
- **Нет deprecation policy** — старая версия живёт вечно, нагрузка двойная

**Альтернатива versioning — Evolution:**

Не делать breaking changes вообще, только добавлять (additive). Клиенты не падают, новые поля игнорируют старые. Сложно для крупных рефакторингов, но для большинства API работает.',
                'code_example' => 'GET /api/v2/users HTTP/1.1
Host: api.example.com
Accept: application/json

# или через media type
GET /api/users HTTP/1.1
Accept: application/vnd.myapi.v2+json

# ответ для устаревшей версии
HTTP/1.1 200 OK
Deprecation: true
Sunset: Sat, 31 Dec 2026 23:59:59 GMT
Link: </api/v3/users>; rel="successor-version"',
                'code_language' => 'bash',
                'difficulty' => 4,
                'topic' => 'system_design.api',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Какие HTTP методы идемпотентны?',
                'answer' => 'По **RFC 9110** методы делятся на три пересекающиеся категории.

| Метод | **Safe** | **Идемпотентен** | Что значит |
|---|---|---|---|
| **`GET`** | ✓ | ✓ | читает, не меняет |
| **`HEAD`** | ✓ | ✓ | как GET, без тела |
| **`OPTIONS`** | ✓ | ✓ | preflight/capability |
| **`TRACE`** | ✓ | ✓ | диагностика |
| **`PUT`** | ✗ | ✓ | полная замена ресурса — повтор даёт то же состояние |
| **`DELETE`** | ✗ | ✓ | удаление — повтор даёт `404`/`204`, состояние то же |
| **`POST`** | ✗ | **✗** | каждый вызов создаёт новый ресурс |
| **`PATCH`** | ✗ | **зависит** | по умолчанию нет; может быть идемпотентным по семантике тела |

- **Safe** — операция **не меняет состояние** сервера в принципе. Можно кэшировать, prefetch-ить, ретраить.
- **Идемпотентен** — `f(f(x)) == f(x)`: повторение N раз даёт тот же эффект на сервере, что и один вызов.

**`PATCH`** по `RFC 5789` **не идемпотентен по умолчанию**, но может быть определён таким:

- `{"status": "paid"}` — **идемпотентен** (повтор не меняет)
- `{"qty": "+1"}` — **не идемпотентен** (повтор инкрементирует ещё раз)

**Зачем это важно практически:**

- **Retry-политика прокси/балансировщика** — safe методы можно ретраить **свободно**; идемпотентные — после ошибки сети
- **`POST`** ретраит только клиент сознательно — через **`Idempotency-Key`** (Stripe-стиль): сервер кэширует ответ по ключу на N часов
- **браузер** при `F5` после `POST` спрашивает «отправить повторно?» именно потому что не-идемпотентен

**Подводный камень:** **`DELETE` идемпотентен**, хотя на повтор возвращает `404`. Состояние сервера то же — ресурс удалён. Это не противоречие.',
                'code_example' => '# Safe + идемпотентны — ретраить безопасно
GET    /api/users/42    → 200 (всегда тот же ответ)
HEAD   /api/users/42    → 200 (как GET, без тела)

# Идемпотентны, но не safe
PUT    /api/users/42 {"name":"Vasya"}    → 200/204 (двойной PUT = одно состояние)
DELETE /api/users/42                     → 204; повтор → 404, состояние то же

# НЕ идемпотентен — ретрай создаст дубль
POST /api/orders {"item_id":1,"qty":2}   → 201 (каждый вызов = новый заказ)

# POST с Idempotency-Key — клиент защищает от дубля
POST /api/payments HTTP/1.1
Idempotency-Key: 7c1f...3b2e
Content-Type: application/json

{"amount":1000,"currency":"USD"}

# Сервер кэширует ответ по ключу на 24 часа
# повтор с тем же ключом → возвращает закэшированный 201, без второй транзакции',
                'code_language' => 'http',
                'difficulty' => 3,
                'topic' => 'system_design.api',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что означают основные HTTP статус-коды?',
                'answer' => 'Статус-коды делятся на **5 классов** по первой цифре.

**2xx Success — успех:**

- `200 OK` — общий успех (тело есть)
- `201 Created` — ресурс создан (после `POST`)
- `204 No Content` — успех, тела нет (после `DELETE`, `PUT`)

**3xx Redirect — перенаправление:**

- `301 Moved Permanently` — постоянное (кэшируется)
- `302 Found` — временное
- `304 Not Modified` — кэш на стороне клиента актуален (по `ETag`/`If-Modified-Since`)

**4xx Client error — ошибка клиента:**

- `400 Bad Request` — кривой запрос (битый JSON)
- `401 Unauthorized` — **не аутентифицирован** (нет/невалидный токен)
- `403 Forbidden` — аутентифицирован, но **нет прав**
- `404 Not Found` — ресурс не найден
- `409 Conflict` — конфликт состояния (дубль email)
- `422 Unprocessable Entity` — **валидация не прошла** (стандарт Laravel)
- `429 Too Many Requests` — rate limit

**5xx Server error — ошибка сервера:**

- `500 Internal Server Error` — упало в коде
- `502 Bad Gateway` — апстрим вернул мусор
- `503 Service Unavailable` — временно недоступно (maintenance)
- `504 Gateway Timeout` — апстрим не ответил вовремя

**Запомни путаницу:** `401` vs `403` — «не знаю кто ты» vs «знаю, но нельзя».',
                'code_example' => '// Laravel
return response()->json($user, 201);              // created
return response()->json(null, 204);               // no content
abort(404, "User not found");                     // 404
abort(403, "You cannot edit this post");          // 403
return response()->json(["errors" => ...], 422);  // validation',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'system_design.api',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое WebSockets и для чего нужны?',
                'answer' => '**WebSocket** — протокол **двунаправленной связи** между клиентом и сервером **поверх одного TCP-соединения**. RFC 6455.

Аналогия: **HTTP — обмен письмами** (запрос → ответ → закрытие). **WebSocket — телефонный разговор** — открыли канал и говорят оба, пока кто-то не положит трубку.

**Как работает:**

1. Клиент шлёт `HTTP/1.1` запрос с `Upgrade: websocket` + `Connection: Upgrade` + `Sec-WebSocket-Key`
2. Сервер отвечает `101 Switching Protocols` + `Sec-WebSocket-Accept`
3. С этого момента **то же TCP-соединение** используется **в обе стороны**, в кадрах WebSocket-протокола (`text`/`binary`/`ping`/`pong`/`close`)
4. Канал **stateful** — каждое соединение привязано к конкретному серверу

**Когда выбирают WS:**

- **двунаправленный** трафик — клиент шлёт, сервер тоже шлёт без запроса
- **низкая задержка** — нет overhead на новый HTTP-handshake каждый раз
- **частые мелкие сообщения** — заметно дешевле HTTP в трафике

**Типичные кейсы:** чаты, real-time уведомления, онлайн-игры, торговые платформы (тикеры, котировки), совместное редактирование, multiplayer, IoT телеметрия.

**Альтернативы и когда их хватает:**

- **`SSE`** (Server-Sent Events) — **сервер → клиент** только, поверх HTTP. Уведомления, прогресс-бары.
- **Long polling** — старый fallback, эмуляция через `HTTP`. Используют, когда WS блокируются прокси.
- **HTTP/2 push** — устарел, не используется.

**Подводные камни:**

- **stateful** → проблемы с горизонтальным масштабированием (нужен **pub/sub backplane**: Redis/NATS)
- корпоративные прокси могут **резать WS** → fallback на long-polling
- **sticky sessions** или **session affinity** на балансировщике
- лимит файловых дескрипторов (`ulimit -n`)
- нет автоматического retry — клиент сам реконнектит

**В Laravel:** `Reverb` (нативный, с 11), `Pusher` (SaaS), `Soketi` (self-hosted). Эховент через `broadcast(new MyEvent($data))`.',
                'difficulty' => 3,
                'topic' => 'system_design.api',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Главная боль масштабирования WebSockets - чем её решают?',
                'answer' => '**WebSocket — stateful протокол**: после handshake соединение между клиентом и **конкретным сервером** живёт долго. В отличие от stateless HTTP, **нельзя «положить запрос в любой инстанс через round-robin»**. Это создаёт проблемы при горизонтальном масштабировании.

**Основная проблема:**

```
3 WS-сервера, пользователи распределены:
  user A → ws-1
  user B → ws-3

user A пишет в чат → ws-1
ws-1 НЕ ЗНАЕТ, что user B существует на ws-3 → сообщение теряется
```

**Решения:**

| Решение | Принцип | Когда |
|---|---|---|
| **Pub/Sub Backplane** | все ws-серверы подписаны на общий Redis/Kafka | **стандартный выбор** |
| **Sticky sessions** | привязка клиента к серверу по IP/cookie | помогает с переподключением, **не заменяет backplane** |
| **Specialized router** | frontend хранит routing table `user_id → ws-server` | внутренний контроль, redundancy сложнее |
| **Managed SaaS** | AWS API Gateway WebSocket, Pusher, Ably | если не хочется заморачиваться |

**1. Pub/Sub Backplane — де-факто стандарт:**

```
client_A ─┐                           ┌─ client_B
          ↓                           ↑
       [ws-1]                     [ws-3]
          ↓ publish "chat:42"        ↑ deliver to client_B
          └──→  [Redis Pub/Sub]  ────┘
                    ↑ subscribe by "chat:42"
                 [ws-2] (нет подписчиков на этот канал)
```

Все ws-серверы подписываются на общие каналы в **`Redis`/`NATS`/`RabbitMQ`/`Kafka`**. При получении сообщения ws-сервер публикует его в канал `"chat:42"`, все остальные ws-серверы (тоже подписанные) получают и рассылают своим подключённым клиентам.

Используется в: **`Socket.io adapters`**, **`Laravel Reverb`** с pub/sub режимом, **`ActionCable`** в Rails.

**2. Sticky sessions** — `ip_hash` или cookie-based на nginx/HAProxy:

```
upstream ws_backend {
    ip_hash;
    server ws-1:8080;
    server ws-2:8080;
    server ws-3:8080;
}
```

Помогает с **переподключением** (клиент попадает на тот же сервер), но **не решает** проблему обмена между серверами — всё равно нужен backplane.

**Дополнительные подводные камни WS на проде:**

- **Heartbeat (ping/pong)** для обнаружения мёртвых соединений (`ws-ping-interval`)
- **Лимит файловых дескрипторов** (`ulimit -n` обычно `1024`, для WS нужно `65535+`)
- **Graceful shutdown** с уведомлением клиентов о disconnect
- **Обновление сертификатов** и rotation API-ключей без обрыва коннектов
- **NAT/proxy** — некоторые корп-сети режут WS-handshake, нужен fallback на **long-polling** (Socket.io делает автоматически)
- **Memory** — 10k открытых соединений = десятки МБ только под буферы
- **Auth** — токен в URL query (`wss://...?token=...`) логируется, лучше через первое сообщение или cookie',
                'code_example' => '<?php
// Laravel Reverb с Redis backplane для multi-server деплоя
// config/reverb.php
"servers" => [
    "reverb" => [
        "host" => env("REVERB_HOST", "0.0.0.0"),
        "port" => env("REVERB_PORT", 8080),
        "scaling" => [
            "enabled" => env("REVERB_SCALING_ENABLED", true), // ключ к multi-server
            "channel" => "reverb",
            "server" => [
                "url" => env("REDIS_URL"),
            ],
        ],
    ],
],

// Когда включён scaling, Reverb публикует все сообщения в Redis pub/sub;
// другие инстансы Reverb подписаны и форвардят клиентам

// Архитектура (общая для любого WS-движка):
//
//   client_A ─┐                           ┌─ client_B
//             ↓                           ↑
//          [ws-1]                     [ws-3]
//             ↓ publish "chat:42"        ↑ deliver to client_B
//             └──→  [Redis Pub/Sub]  ────┘
//                       ↑ subscribe by "chat:42"
//                    [ws-2] (нет подписчиков на этот канал)

// Без backplane: ws-1 не знает, что client_B есть на ws-3 - сообщение теряется

// Sticky session - дополнение, не замена
// nginx upstream
// ip_hash; # привязка клиента к серверу по IP
// или cookie-based: sticky cookie srv_id expires=1h domain=.example.com path=/;',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'system_design.api',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'В чём разница между offset и cursor пагинацией?',
                'answer' => 'Два способа пагинировать большие списки в API. Различаются **сложностью** и **стабильностью** при вставках.

| | **Offset** (`LIMIT/OFFSET`) | **Cursor** (keyset) |
|---|---|---|
| **SQL** | `LIMIT N OFFSET M` | `WHERE id < :last ORDER BY id LIMIT N` |
| **Сложность** | O(M + N) — БД **читает и отбрасывает** M строк | **O(log N + limit)** — seek по индексу |
| **Прыжок на N-ю страницу** | да (`?page=42`) | нет, только **next/prev** |
| **Стабильность при вставках** | **нет** — дубли/пропуски | **да** — стабильно |
| **Скорость на странице 1000+** | **тормозит** | константно |
| **Тип ответа** | `total_pages` известен | `next_cursor` или `null` |

**Проблемы offset:**

- **тормозит на больших страницах** — `OFFSET 100000` заставляет БД прочитать и отбросить 100k строк
- **race conditions** при вставках: пока юзер скроллит, в начало добавили 5 записей → на странице 2 он увидит 5 дублей со страницы 1; или удалили — пропуски

**Cursor (keyset pagination):**

- использует **`WHERE` по индексированному ключу** — БД делает быстрый seek по B-tree до позиции и читает только `limit` строк
- **стабильно** при вставках: курсор указывает на конкретный id, новые записи не сдвигают результат

**Подводный камень cursor:** сортировка по **неуникальному** полю даёт пропуски/дубли. `ORDER BY created_at` с курсором `last_created_at`: при одинаковом времени соседних строк `>` пропустит часть, `>=` продублирует.

**Решение — составной курсор с tiebreaker:**

```sql
WHERE (created_at, id) > (:last_at, :last_id)
ORDER BY created_at, id
LIMIT 20
```

**Правило выбора:**

- **offset** — админка с малыми объёмами и нумерацией страниц («Страница 3 из 12»)
- **cursor** — бесконечные ленты (Twitter, Instagram), публичные API, большие таблицы, **infinite scroll**',
                'code_example' => '# offset (плохо для больших страниц)
GET /api/posts?page=1000&per_page=20
SELECT * FROM posts ORDER BY id DESC LIMIT 20 OFFSET 19980;

# cursor (быстро, стабильно)
GET /api/posts?after=eyJpZCI6MTIzNDV9&limit=20
SELECT * FROM posts WHERE id < 12345 ORDER BY id DESC LIMIT 20;
# ответ: { data: [...], next_cursor: "eyJpZCI6MTIzMjV9" }',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.api',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'В чём разница между WebSocket, SSE, long polling?',
                'answer' => 'Три способа сделать **«серверу что-то сообщить клиенту в реальном времени»** — все решают проблему пуша от сервера к клиенту, но разной ценой.

| | **Long polling** | **SSE** | **WebSocket** |
|---|---|---|---|
| **Транспорт** | HTTP | HTTP (`text/event-stream`) | TCP с upgrade из HTTP |
| **Направление** | сервер → клиент (по запросу) | **сервер → клиент** | **двунаправленный** |
| **Reconnect** | новый запрос каждый раз | автоматический (с `Last-Event-ID`) | руками |
| **Бинарь** | да | **только текст** | да |
| **Прокси/CDN дружелюбие** | отлично | хорошо | средне (могут резать) |
| **Сложность** | низкая | низкая | средняя |
| **Поддержка браузеров** | везде | везде, кроме старого IE | везде |

**Long polling** — клиент шлёт `GET`, сервер **держит запрос** до появления данных (или timeout), отвечает, клиент **тут же шлёт следующий**. Имитирует push через HTTP. Старый fallback, когда WS режут прокси.

**SSE (Server-Sent Events)** — клиент открывает HTTP-стрим, сервер шлёт **поток текстовых событий** в формате:

```
event: message
data: {"text": "hello"}
id: 42
```

- **только сервер → клиент**
- автоматический **reconnect** браузером с заголовком `Last-Event-ID`
- работает поверх **обычного HTTP** → дружелюбен к CDN, прокси, балансировщикам
- идеален для **уведомлений, прогресс-баров, тикеров**, всего «one-way»

**WebSocket** — **двунаправленный** канал поверх одного TCP-соединения. Сложнее scaling (stateful, нужен backplane), но единственный вариант для **чатов, игр, multiplayer**.

**Когда что брать:**

- **уведомления / live-feed / progress** — **SSE** (проще, надёжнее, авто-reconnect)
- **чат / совместное редактирование / игры** — **WebSocket** (нужны оба направления)
- **legacy-окружение, корп прокси режут WS** — **long polling** (или SSE)
- **только периодические обновления** (раз в минуту) — обычный polling, не надо изобретать',
                'difficulty' => 3,
                'topic' => 'system_design.api',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Какие главные архитектурные проблемы у GraphQL на публичном API?',
                'answer' => '**GraphQL** даёт клиентам гибкость (запрос только нужных полей, один round-trip вместо нескольких REST-вызовов, типизированный schema), но в продакшене порождает **три серьёзные проблемы**, которых нет в REST.

**1. Кэширование — HTTP-кэш не работает.**

GraphQL обычно делает все запросы как `POST /graphql` с телом в JSON — стандартный HTTP-кэш (browser, CDN типа Cloudflare/Fastly, Varnish) **полностью неэффективен**, потому что они умеют кэшировать **`GET` по URL**.

**Решения:**

- **Persisted Queries** — клиент шлёт **хеш query**, сервер по хешу находит и выполняет. Запрос становится **GET-able**: `GET /graphql?id=abc123` → кэшируется на CDN
- **APQ** (Automatic Persisted Queries в Apollo) — первый раз шлётся вся query, потом только хеш
- **Клиентские кэши** — `Apollo Client` / `Relay` с нормализацией по `__typename + id`
- **Резолверный кэш** — DataLoader + Redis на уровне приложения

**2. Rate limiting — обычный per-minute не работает.**

Лимитировать GraphQL по запросам в минуту **бессмысленно**: один query может вытащить **полбазы**:

```graphql
query Evil {
  user(id: 1) {
    posts {
      comments {
        author {
          posts { comments { author { ... } } }
        }
      }
    }
  }
}
```

**Нужен Query Complexity Analysis:**

- Оцениваем **«стоимость»** каждого поля и резолвера **ДО выполнения**
- Складываем по всему query
- Превышение лимита → **`429` без выполнения**

**Дополнительные защиты:**

- **Max query depth** (например, 7 уровней)
- **Отключить introspection в проде** (или auth-only)
- **Ограничить aliases** (защита от alias-attack)
- **Persisted-queries-only** в проде (запретить произвольные queries)

**Библиотеки:** `graphql-cost-analysis`, `query-complexity`, `graphql-armor`.

**3. N+1 запросы — фундаментальная проблема резолверов.**

GraphQL резолверы вызываются **по одному на каждое поле/запись** — наивная реализация делает N+1:

```
100 постов → 1 запрос на posts + 100 запросов на user → 101 запрос
```

**Решение — DataLoader** (изобретён Facebook):

- Пакетирует запросы внутри одного tick event loop
- **Дедуплицирует** одинаковые запросы
- **Кэширует** на время запроса

С DataLoader: `100 постов → 2 запроса (1 для постов + 1 для всех users)`.

В PHP — **`webonyx/graphql-php`** + **`nuwave/lighthouse`** имеют batch loading.

**Дополнительные подводные камни:**

- **Обработка ошибок** — query может **частично succeed/partially fail** в одном `HTTP 200`. Нужны конвенции по полю `errors`
- **Мониторинг** — нет per-endpoint метрик, всё `/graphql`. Нужно tagging по `operation_name`
- **File uploads** — multipart spec костыльный (`graphql-multipart-request-spec`)
- **Schema bloat** — большая schema → долгая инициализация и память
- **Versioning** — GraphQL deprecates fields, не URL версии. Поля могут жить вечно с `@deprecated`

**Когда GraphQL имеет смысл:**

- **BFF** (backend-for-frontend) для разных клиентов
- **Federation** между микросервисами (Apollo Federation)
- **Внутренние API** с мобильными/SPA клиентами

**Когда REST лучше:**

- **Публичные API** с акцентом на кэш и стандарты
- **Простые CRUD**
- **File uploads / streaming**
- **OpenAPI ecosystem** уже есть',
                'code_example' => '<?php
// 1) Query Complexity (lighthouse-php / webonyx-php)
// schema.graphql
"""
type Query {
    posts(first: Int @rules(["max:100"])): [Post!]!
        @paginate(maxCount: 100)
        @complexity(resolver: "App\\\\GraphQL\\\\Complexity@perPage")
}
"""

// PerPage.php
public function perPage(int $childComplexity, array $args): int
{
    return $args["first"] * ($childComplexity + 1);
}

// 2) DataLoader pattern - решает N+1
class UserBatchLoader
{
    private array $cache = [];

    public function load(int $userId): User
    {
        // первый раз накапливаем ID, потом одним запросом
        $this->ids[] = $userId;

        if (! isset($this->cache[$userId])) {
            $users = User::whereIn("id", array_unique($this->ids))->get()->keyBy("id");
            foreach ($users as $u) $this->cache[$u->id] = $u;
        }

        return $this->cache[$userId];
    }
}

// Без DataLoader: 100 постов → 101 запрос
// С DataLoader: 100 постов → 2 запроса (1 для постов + 1 для всех users)

// 3) Persisted Queries - кеш на CDN
// Клиент:
//   GET /graphql?id=abc123&variables={"limit":10}
// Сервер:
//   читает файл queries/abc123.graphql, выполняет
//   ставит Cache-Control: public, max-age=60
// Cloudflare кеширует по URL - profit

// 4) Безопасность в проде
// - Отключить introspection: GraphQL::removeIntrospection()
// - Max depth: 7
// - Max complexity: 1000
// - Persisted-queries-only (запретить произвольные queries в проде)',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'system_design.api',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'За что отвечают основные HTTP-методы GET, POST, PUT, PATCH, DELETE?',
                'answer' => 'Пять основных HTTP-методов в REST. Различаются по **семантике**, **safe** (не меняет состояние) и **идемпотентности** (повтор даёт тот же эффект).

- **`GET`** — **читает** ресурс. **Safe + идемпотентен.** Кэшируется браузером/прокси/CDN. Не должен иметь побочных эффектов.
- **`POST`** — **создаёт** новый ресурс или запускает действие. **Не идемпотентен** — два `POST /orders` создадут два заказа.
- **`PUT`** — **полная замена** ресурса по URI. **Идемпотентен** — два одинаковых `PUT` дают одно и то же состояние.
- **`PATCH`** — **частичное** изменение (`{"name": "новое"}` меняет только имя). **В общем случае не идемпотентен** (зависит от тела: `{"qty":"+1"}` — нет, `{"status":"paid"}` — да).
- **`DELETE`** — **удаляет** ресурс. **Идемпотентен** — повторный `DELETE` обычно возвращает `404`/`204`, но состояние сервера то же.

**Типичные коды ответа:**

| Метод | Успех | Если нет |
|---|---|---|
| `GET` | `200 OK` | `404` |
| `POST` | `201 Created` + `Location` | `422` |
| `PUT` | `200`/`204` | `404` |
| `PATCH` | `200`/`204` | `404`/`409` |
| `DELETE` | `204 No Content` | `404` |',
                'code_example' => 'GET    /users/42                  → 200 {"id":42,"name":"Vasya"}
POST   /users                     → 201 (Location: /users/43)
PUT    /users/42 {full object}    → 200 / 204
PATCH  /users/42 {"name":"Petya"} → 200 / 204
DELETE /users/42                  → 204',
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'system_design.api',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'В чём практическая разница между GET и POST в HTTP?',
                'answer' => 'Главное отличие — **семантика**, всё остальное вытекает из неё.

| | `GET` | `POST` |
|---|---|---|
| **Назначение** | чтение | создание / действие с побочкой |
| **Параметры** | в **URL** (`?id=42`) | в **теле** запроса |
| **Кэширование** | да, по умолчанию | нет |
| **Сохраняется в** | истории браузера, логах прокси | только в логах сервера (тело обычно нет) |
| **Лимит размера** | URL ~`8 KB` (прокси/сервер) | тело почти не ограничено |
| **Идемпотентность** | да | нет — повтор создаёт ещё запись |
| **Safe** | да | нет |
| **Повтор после ошибки сети** | безопасно | нужен `Idempotency-Key` |

**Практические следствия:**

- **секреты и пароли в `GET` не кладут** — попадут в логи nginx, `access.log`, историю браузера
- **формы создания** — всегда `POST` (защита от случайного refresh, который повторит запрос)
- **фильтры и поиск** — `GET` (можно поделиться ссылкой, кэшируется)
- **`F5` после `POST`** — браузер спросит «отправить повторно?» именно из-за non-idempotency',
                'difficulty' => 2,
                'topic' => 'system_design.api',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Чем safe-методы отличаются от идемпотентных в HTTP?',
                'answer' => 'Это **разные свойства**, и их часто путают.

| | **Safe** | **Идемпотентен** |
|---|---|---|
| **Определение** | НЕ меняет состояние сервера | повтор даёт тот же эффект, что один вызов |
| **Можно кэшировать?** | да | не обязательно |
| **Можно `prefetch`?** | да | нет |
| **`GET`/`HEAD`/`OPTIONS`** | ✓ | ✓ |
| **`PUT`/`DELETE`** | ✗ (меняют) | ✓ |
| **`POST`/`PATCH`** | ✗ | ✗ (в общем случае) |

**Safe ⊂ Idempotent:** все safe-методы идемпотентны, но **не наоборот** — `PUT` идемпотентен, но не safe.

**Что делает каждое свойство:**

- **Safe** — обещание, что запрос **не имеет побочных эффектов**. Браузер может prefetch-ить ссылки, прокси кэшировать ответы, web-краулеры спокойно ходить по URL.
- **Idempotent** — обещание, что **повтор не вреден**. Клиент/прокси при ошибке сети может ретраить.

**Retry-политики:**

- **safe** → ретраить **свободно** (любое количество, любым клиентом)
- **idempotent** → ретраить **после ошибки сети/таймаута** (не зная, дошёл ли запрос)
- **non-idempotent** (`POST`) → ретраить **только с `Idempotency-Key`**, иначе создашь дубль

**Типичная ошибка:**

- `GET /api/orders/42/cancel` — `GET`-роут, который **меняет состояние**. Краулер пройдёт по ссылке и отменит все заказы. Используй `POST /api/orders/42/cancel`.

**RFC 9110** определяет всё это формально. Семантика метода — **контракт**, который реализация должна соблюдать.',
                'difficulty' => 3,
                'topic' => 'system_design.api',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что означает stateless в REST и какие из этого следствия?',
                'answer' => '**Stateless** — сервер **не хранит** между запросами никакого **клиентского контекста**. Каждый запрос **самодостаточен**: несёт внутри всё, что нужно для обработки — токен, идентификатор ресурса, параметры.

Аналогия: каждый раз приходишь в банк с **полным паспортом**, а не «я был у вас вчера, помните?».

**Что НЕ хранит сервер между запросами:** «текущего пользователя», «открытую корзину», «прогресс мастера регистрации». Эти данные либо в **JWT-токене**, либо в **общем хранилище** (Redis, БД).

**Плюсы:**

- **горизонтальное масштабирование «бесплатно»** — любой инстанс обработает любой запрос
- балансировщик может слать round-robin, без sticky session
- упал один сервер — клиент переходит на другой, ничего не теряет
- проще отлаживать (полный контекст в запросе)

**Минусы:**

- **больше байт по сети** — токены/метаданные летают в каждом запросе
- авторизация валидируется **на каждый запрос**
- сложнее реализовать сценарии типа «multi-step wizard»

**Важно:** stateless — про **API-сервер**, не про систему в целом. БД, кэш, очереди — у них своё состояние. Это нормально.',
                'difficulty' => 2,
                'topic' => 'system_design.api',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Чем SOAP отличается от REST и когда выбирают SOAP сегодня?',
                'answer' => '`SOAP` и `REST` — **разные эпохи и подходы**.

| | **SOAP** | **REST** |
|---|---|---|
| **Что это** | полноценный **протокол** | архитектурный **стиль** |
| **Транспорт** | HTTP, SMTP, JMS | только HTTP |
| **Формат** | **XML envelope** (обязательно) | обычно JSON (любой) |
| **Контракт** | **`WSDL`** — машиночитаемое описание | OpenAPI (опционально, отдельно) |
| **Кодген клиента** | из WSDL «из коробки» | через OpenAPI tooling |
| **Безопасность** | встроенный **`WS-Security`** (подписи, шифрование на уровне сообщения) | TLS + JWT/OAuth снаружи |
| **Транзакции** | **`WS-AtomicTransaction`** через несколько систем | нет встроенного, делают через Saga |
| **Размер сообщения** | большой (XML overhead) | компактный (JSON) |
| **Дебаг** | сложный | `curl`, Postman |
| **Кэширование** | нет | штатный HTTP-кэш |

**Что даёт SOAP:**

- **сильная типизация** — каждое поле в WSDL имеет тип, валидируется
- **кодген клиентов** на любом языке (Java, C#, PHP) автоматически
- **WS-*** стек — формальные транзакции, надёжная доставка, signed/encrypted messages на уровне сообщения

**Где SOAP до сих пор живёт:**

- **банковский** межсистемный обмен (SWIFT, ISO 20022)
- **государственные** интеграции (ЕСИА, СМЭВ в РФ, eGovernment в ЕС)
- **enterprise B2B** — там, где контракт зафиксирован договором
- **legacy** в страховании, телекомах, ERP (SAP, Oracle)

**Когда выбирают SOAP сегодня:**

- внешняя система **навязывает** SOAP (банк, госорган)
- нужны **WS-Security** или **формальные транзакции** через несколько систем
- ваша экосистема — `.NET`/`Java EE` с уже отлаженным SOAP-стеком

**В любом другом случае — REST или gRPC.** Новые проекты SOAP не выбирают: дорого, медленно, плохо дружит с браузерами и облачными tooling-ами.',
                'difficulty' => 3,
                'topic' => 'system_design.api',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Чем REST отличается от JSON-RPC и где JSON-RPC удобнее?',
                'answer' => 'Два **разных способа** думать про API.

| | **REST** | **JSON-RPC** |
|---|---|---|
| **Ориентация** | **ресурсы** (существительные) | **методы** (глаголы) |
| **URL** | `/users/42/orders` | один эндпоинт `/api/rpc` |
| **HTTP-метод** | несёт семантику (`GET`/`POST`/`PUT`) | всегда `POST` |
| **HTTP-статус** | несёт смысл (`200`, `404`, `422`) | почти всегда `200` — успех/ошибка в теле |
| **Тело** | данные ресурса | `{"method":"X","params":{...},"id":1}` |
| **Кэш** | штатный HTTP-кэш | **не работает** |
| **Conditional requests** | `ETag`, `If-Modified-Since` | нет |
| **Tooling/OpenAPI** | огромная экосистема | свой, ограниченный |
| **Batch** | надо городить | **встроен** — массив RPC-вызовов |

**Когда JSON-RPC удобнее:**

- API — это **набор действий**, не CRUD: `calculate(...)`, `recalculate(...)`, `send(...)`, `validate(...)`
- натягивать REST-семантику **искусственно** (типа `POST /calculations` чтобы вызвать `calculate`)
- нужны **batch-вызовы** — отправить 50 RPC одним запросом, получить массив ответов
- внутренний RPC между сервисами, где HTTP-семантика не нужна
- сложился исторически (Bitcoin Core RPC, JSON-RPC over WebSocket в Ethereum)

**Чем платишь за JSON-RPC:**

- мимо проходит **HTTP-кэш**, `If-None-Match`, `429`/`503` с правильной семантикой
- весь **REST-tooling** (Postman collections из OpenAPI, gateways с rate limiting на эндпоинт, observability) работает плохо
- нужно отдельно описывать **схему методов** (часто через JSON Schema или собственное соглашение)
- ошибки приходят с `HTTP 200` — мониторинг alerting-инструментов нужно настраивать на тело ответа

**Краткое правило:** ресурсы → REST. Чистые операции, batch, RPC-стиль → JSON-RPC. Стриминг, типы, codegen → **gRPC**.',
                'difficulty' => 3,
                'topic' => 'system_design.api',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Можно ли ввести свой HTTP-метод вроде POSTAWESOME и остаётся ли это REST?',
                'answer' => '**Технически** HTTP допускает кастомные методы (`RFC 9110` про IANA registry), и `nginx`/Apache можно научить пробрасывать, а PHP-фреймворк — на них роутить. **Но это нарушает Uniform Interface** — одно из фундаментальных ограничений REST.

**Почему ломается:**

| Что | Что ломается |
|---|---|
| **Прокси и кэши** | не знают, **safe** ли метод, **идемпотентен** ли — не кэшируют, не ретраят правильно |
| **CORS preflight** | не пропускает нестандартные методы без явного `Access-Control-Allow-Methods` |
| **Клиентские либы** | не умеют ретраить корректно (не знают семантики) |
| **Load balancers** | могут не пропускать неизвестные методы |
| **WAF / IDS** | могут блокировать как «подозрительный»  трафик |
| **Logging tools** | не агрегируют — каждый кастомный метод как unknown |
| **Documentation tools** (`OpenAPI`, Postman) | не поддерживают custom methods |

**REST Uniform Interface принцип:**

> Интероперабельность держится на том, что **весь мир знает `GET`/`POST`/`PUT`/`PATCH`/`DELETE`** и их семантику.

Вводя `POSTAWESOME`, ты заставляешь **каждого** клиента и инфраструктурный компонент учить твою кастомную семантику. Это **anti-REST**.

**Если стандартных глаголов мало — правильные паттерны:**

**1. Под-ресурс для действия:**

```
POST /orders/42/cancel
POST /orders/42/refund
POST /users/42/activate
```

Здесь `cancel`/`refund`/`activate` — **подресурс**, а не глагол. **`POST`** значит «создаю запись об отмене».

**2. State transition через PATCH:**

```
PATCH /orders/42
{"status": "cancelled"}
```

Если статусы — first-class в твоей модели.

**3. Command endpoint:**

```
POST /commands
{"type": "CancelOrder", "orderId": 42}
```

Для CQRS-стиля.

**4. RPC-стиль если REST не подходит:**

Не выдумывай свои HTTP-методы — переходи на **JSON-RPC** или **gRPC**, где RPC-семантика **родная**:

```
POST /jsonrpc
{"method": "orders.cancel", "params": {"id": 42}}
```

**Краткое правило:** если хочется свой метод — это **сигнал**, что либо ты используешь не тот протокол (REST не подходит), либо у тебя есть **скрытый ресурс**, который стоит выделить.',
                'difficulty' => 4,
                'topic' => 'system_design.api',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое HATEOAS и почему его так редко доводят до конца?',
                'answer' => '**HATEOAS** (Hypermedia as the Engine of Application State) — **уровень 3 зрелости REST по Ричардсону**, на котором **ответ сервера содержит не только данные**, но и **ссылки на возможные следующие действия**:

```json
{
  "id": 42,
  "status": "new",
  "_links": {
    "self": "/orders/42",
    "cancel": {"href": "/orders/42/cancel", "method": "POST"},
    "ship": {"href": "/orders/42/ship", "method": "POST"},
    "customer": "/customers/7"
  }
}
```

**Идея:** клиент **двигается по API как браузер по гиперссылкам** и **не зашивает URL-шаблоны**. Если завтра поменяется URL — клиент узнает из ответа.

**Уровни Ричардсона (Richardson Maturity Model):**

| Уровень | Что |
|---|---|
| **0** | Один URL, всё через POST (SOAP-стиль) |
| **1** | Ресурсы (`/users`, `/orders`) |
| **2** | HTTP-методы (`GET`, `POST`, `PUT`, `DELETE`) — **большинство «REST API»** |
| **3** | **HATEOAS** — гипермедийные ссылки в ответах |

**По Roy Fielding** настоящий REST — **только уровень 3**. Без HATEOAS это «HTTP-API», а не REST.

**Гипермедийные форматы:**

- **HAL** (`application/hal+json`) — `_links`, `_embedded`
- **JSON:API** — стандарт с `relationships`, `links`
- **Siren** — `actions`, `entities`, `links`
- **JSON Hyper-Schema**

**Почти никто не делает HATEOAS — почему:**

1. **Фронтенд всё равно знает структуру URL** — генерируется из OpenAPI/Swagger
2. **Генерация клиентов из OpenAPI** решает проблему контракта **проще**
3. **Гипермедийные форматы добавляют сложности** без явной выгоды
4. **Кэширование становится сложнее** — каждый ответ зависит от ссылок
5. **Размер ответов растёт** на 30-50%
6. **Mobile/embedded** клиенты предпочитают предсказуемый shape ответа
7. **TypeScript-codegen из OpenAPI** даёт type safety, HATEOAS — нет

**Когда HATEOAS уместен:**

- **Долгоживущие публичные API** (Spotify, GitHub частично использует)
- **Гипермедийные платформы** (Atom feed, Web)
- **API с переменным workflow** — `payment` может быть в разных состояниях, ссылки динамически отражают доступные действия
- **Loose coupling** долгосрочных интеграций

**Когда НЕ нужно:**

- **Типовой бизнес-API** для своего фронтенда
- **gRPC/protobuf** — там типизация контракта другая
- **Маленькая команда** без compliance-требований

**Современный консенсус:** **OpenAPI + типизированный клиент** покрывает 90% задач HATEOAS за меньшую цену. HATEOAS остаётся как **архитектурная стрелка**, чем как практика.',
                'difficulty' => 4,
                'topic' => 'system_design.api',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое content negotiation через Accept и зачем нужен Vary?',
                'answer' => '**Content negotiation** — механизм HTTP, в котором клиент указывает **что хочет** получить, а сервер выбирает **подходящий** вариант из доступных.

**Заголовки negotiation:**

| Запрос (клиент) | Ответ (сервер) | Что регулирует |
|---|---|---|
| **`Accept`** | `Content-Type` | формат ответа (JSON/XML/HTML) |
| **`Accept-Language`** | `Content-Language` | локаль |
| **`Accept-Encoding`** | `Content-Encoding` | сжатие (gzip/br/zstd) |
| **`Accept-Charset`** *(устар.)* | `Content-Type` charset | кодировка |

**q-веса (quality values):**

```
Accept: application/json;q=1.0, application/xml;q=0.8, */*;q=0.1
```

Сервер выбирает формат с **максимальным `q`** из доступных.

**Пример flow:**

```
GET /users/42
Accept: application/xml, application/json;q=0.9

→ HTTP/1.1 200 OK
   Content-Type: application/json   # XML не поддерживаем, отдаём JSON
   Vary: Accept

{"id": 42, ...}
```

**`Vary` — критичен для кэша:**

Без `Vary: Accept` CDN/Varnish/browser **кэширует** один ответ и отдаёт всем, **игнорируя `Accept`** клиента:

```
Клиент A: GET /users/42, Accept: application/json
  → закэшировано JSON

Клиент B: GET /users/42, Accept: application/xml
  → CDN отдаёт ЗАКЭШИРОВАННЫЙ JSON 🚫
```

С `Vary: Accept` CDN **хранит отдельные копии** для каждого значения `Accept`.

**Versioning через `Accept`:**

```
GET /users/42
Accept: application/vnd.acme.v2+json
```

Сервер видит `vnd.acme.v2` → отдаёт v2-формат. **Плюсы:** URL стабилен. **Минусы:** сложнее дебажить в браузере, нужно объяснять клиентам.

**`Vary` в комбинации:**

```
Vary: Accept, Accept-Language, Authorization
```

CDN держит **отдельные копии** для каждой комбинации. **Грабли:** слишком много `Vary` headers = **взрывная кардинальность кэша**, hit rate падает.

**Anti-pattern:**

- **Echo `Origin`** в `Access-Control-Allow-Origin` + `Vary: Origin` — корректно, без `Vary` — security/cache problem
- **Забыть `Vary: Accept-Encoding`** при gzip — старые прокси отдают gzip клиентам, которые не понимают

**Современный подход:**

- **CDN** (Cloudflare/Fastly) уважают `Vary` headers, но лучше **минимизировать** их количество
- Для **versioning** проще URI versioning (`/v2/`) — кэш не страдает',
                'difficulty' => 4,
                'topic' => 'system_design.api',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как работают ETag и If-None-Match для условных запросов?',
                'answer' => '**ETag** (Entity Tag) — **хеш или версия ресурса**, который сервер отдаёт в ответе. Клиент использует его для **условных запросов**, экономя трафик и защищаясь от потерянных обновлений.

**Два сценария:**

### 1. **Кэширование — `If-None-Match`:**

```http
# Первый запрос
GET /users/42
→ HTTP/1.1 200 OK
   ETag: "v3-abc"
   Content-Type: application/json
   {"id": 42, "name": "John"}

# Второй запрос
GET /users/42
If-None-Match: "v3-abc"
→ HTTP/1.1 304 Not Modified
   (без тела — экономия трафика!)
```

Если ресурс **не изменился** → `304` без тела. Если изменился → `200` с новым ETag и новым телом.

### 2. **Optimistic concurrency — `If-Match`:**

Защита от **lost update** при `PUT`/`PATCH`:

```http
# Клиент прочитал v3-abc, делает изменения
PUT /users/42
If-Match: "v3-abc"
{"name": "John Updated"}

# Если на сервере уже v4-xyz (кто-то обновил раньше):
→ HTTP/1.1 412 Precondition Failed
# Клиент перечитывает свежую версию и решает конфликт
```

Без `If-Match` — **last writer wins**, изменения первого затираются молча.

**Сильные (strong) vs слабые (weak) ETag:**

```
ETag: "v3-abc"        ← strong (бит-в-бит идентичность)
ETag: W/"v3-abc"      ← weak (семантически эквивалентно)
```

- **Strong** — для байтовой проверки (CDN, range requests)
- **Weak** — для семантической эквивалентности (gzip vs non-gzip — одно содержимое)

**Как генерировать ETag:**

| Способ | Плюсы | Минусы |
|---|---|---|
| **`MD5`/`SHA1` от тела** | точно | дорого считать |
| **`updated_at` + `id`** | дешёво | секундная гранулярность |
| **Version column** (`int`) | дёшево, точно | нужно обновлять при `UPDATE` |
| **Хеш от ключевых полей** | компромисс | сложнее |

**`Last-Modified` + `If-Modified-Since` — аналог по времени:**

```
Last-Modified: Wed, 22 May 2026 12:00:00 GMT
If-Modified-Since: Wed, 22 May 2026 12:00:00 GMT
```

**ETag точнее** — не страдает от **секундной гранулярности**. Если в одну секунду было 10 апдейтов, `Last-Modified` не различит.

**Поддерживается стандартно:**

- **`fetch()` API** в браузере — автоматически шлёт `If-None-Match` для cached responses
- **CDN** (Cloudflare, Varnish) проверяют `If-None-Match` без обращения к origin
- **Laravel** — `$response->setEtag($hash)` или middleware

**Не забыть `Cache-Control`:** ETag сам по себе не активирует кэширование — нужны `Cache-Control: private, must-revalidate` для конкретного клиента или `public` для CDN.',
                'difficulty' => 4,
                'topic' => 'system_design.api',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что должен возвращать сервер по 429 и как клиенту правильно ретраить?',
                'answer' => '`429 Too Many Requests` — сервер сигнализирует, что **клиент превысил лимит** (rate limit), запросы режутся.

**Что должен слать сервер:**

- `429 Too Many Requests` — статус
- `Retry-After: 30` — **сколько секунд** ждать до повтора (или дата в HTTP-формате)
- `X-RateLimit-Limit: 100` — потолок на окно
- `X-RateLimit-Remaining: 0` — сколько осталось
- `X-RateLimit-Reset: 1717000000` — Unix-время, когда лимит обнулится
- тело с понятным сообщением и (опционально) `error_code`

**Как клиент должен реагировать:**

1. **Уважать `Retry-After`** — спать **ровно** столько секунд, не меньше
2. Если заголовка нет — **`exponential backoff` с `jitter`**: `delay = min(cap, base * 2^attempt) + random(0, jitter)`
3. Ставить **`max_retries`** (3–5) — после исчерпания вернуть ошибку наверх
4. **Логировать** каждый 429 — это сигнал, что клиент превышает квоту систематически

**Почему `jitter` критичен:**

Без `jitter` все клиенты, получившие `429` одновременно, **ретраят в один момент** — `thundering herd` → лимитер режет всех → ещё `429` → бесконечный цикл. **`Jitter` размазывает** повторы по времени.

**`429` vs `503`:**

- **`429`** — «**твой** лимит превышен» — клиент знает, что виноват сам
- **`503`** — «**сервер** не справляется» — общее перегружение
- Не смешивать: `429` помогает клиенту правильно отреагировать; `503` сигнализирует инцидент

**Анти-паттерн:**

- слепой `while (1) { try { ... } catch { sleep(1); } }` — клиент **усугубляет** инцидент
- ретрай без `max_retries` — может крутиться часами
- ретрай **safe и идемпотентных** запросов автоматически (`GET`, `PUT`) ОК; **non-idempotent** (`POST`) — только с `Idempotency-Key`',
                'code_example' => '<?php
// Клиент: уважаем Retry-After + exponential backoff + jitter
function callWithRetry(callable $request, int $maxRetries = 5): mixed
{
    $attempt = 0;
    while (true) {
        $response = $request();
        if ($response->status() !== 429) return $response;
        if (++$attempt > $maxRetries) {
            throw new RateLimitedException("max retries exceeded");
        }

        $delay = (int) ($response->header("Retry-After")
            ?? min(60, pow(2, $attempt)));  // exponential cap 60s

        $delay += random_int(0, 1000) / 1000.0;  // jitter

        Log::warning("rate limited, sleeping {$delay}s", ["attempt" => $attempt]);
        sleep($delay);
    }
}

// Сервер: Laravel throttle отдаёт правильные заголовки автоматически
Route::middleware("throttle:60,1")->group(function () {
    Route::get("/api/users", [UserController::class, "index"]);
});
// → 429 + Retry-After + X-RateLimit-* headers',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'system_design.api',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Когда выбирают webhooks, а когда polling, и как сделать webhooks надёжным?',
                'answer' => 'Два способа «получать обновления» из чужой системы.

| | **Polling** | **Webhooks** |
|---|---|---|
| **Кто инициирует** | клиент дёргает `GET` периодически | **сервер шлёт `POST`** на URL клиента |
| **Латентность** | до `interval` (минута/час) | **секунды** |
| **Нагрузка** | растёт линейно с количеством клиентов | по факту событий |
| **Сложность клиента** | низкая | нужен **публичный endpoint** |
| **Файрволлы** | работает везде | требует входящие соединения |
| **Дебаг** | лёгкий | сложнее (запрос пришёл — где смотреть) |

**Когда брать polling:** простые сценарии, нет публичного URL (за NAT), редкие события, не критична задержка.

**Когда webhooks:** real-time обновления (payment status, webhook от GitHub), много событий, не хотим грузить сервер polling-ом.

**Надёжный webhook — обязательные элементы:**

1. **Подпись тела HMAC-секретом** в заголовке (`X-Hub-Signature-256: sha256=...`)
2. **Уникальный `event_id`** в payload — для **дедупликации** на стороне получателя
3. **Версионирование** payload — `X-Webhook-Version: 2024-05`
4. **Timestamp** + лимит на возраст события (`X-Timestamp: ...`) — защита от replay-атак
5. **Retry с exponential backoff** на стороне отправителя (1с, 5с, 30с, 2м, 10м, 1ч)
6. **`2xx` от получателя** = принял; **`5xx`/timeout** → retry; **`4xx`** → перестать ретраить
7. **DLQ** после N неудач — складывать в админку для ручного разбора
8. **Idempotent receiver** — повторная доставка возможна, не выполнять дважды

**Лучшая практика на стороне получателя:**

- ответить **`2xx` максимально быстро** (просто записать в очередь)
- **тяжёлую обработку** делать в фоне через `dispatch`
- если обрабатывать синхронно — таймаут источника (обычно 5–10 сек) приведёт к повторной доставке',
                'code_example' => '<?php
// Receiver: GitHub-style webhook с HMAC + dedup
Route::post("/webhooks/payments", function (Request $request) {
    // 1. Проверка подписи (timing-safe)
    $payload = $request->getContent();
    $expected = "sha256=" . hash_hmac("sha256", $payload, config("services.gw.secret"));

    if (!hash_equals($expected, $request->header("X-Signature"))) {
        abort(401, "invalid signature");
    }

    // 2. Защита от replay (timestamp не старше 5 мин)
    if (abs(time() - (int) $request->header("X-Timestamp")) > 300) {
        abort(400, "stale timestamp");
    }

    // 3. Идемпотентность по event_id
    $eventId = $request->input("id");
    if (!Redis::set("webhook:{$eventId}", 1, "EX", 86400, "NX")) {
        return response()->json(["status" => "duplicate"], 200);
    }

    // 4. Быстрый 2xx, обработка в фоне
    ProcessWebhook::dispatch($request->all());
    return response()->json(["status" => "accepted"], 202);
});

// Sender: retry + DLQ
class SendWebhook implements ShouldQueue
{
    public int $tries = 6;
    public array $backoff = [1, 5, 30, 120, 600, 3600];
    public function handle(): void
    {
        $r = Http::timeout(10)->withHeaders([
            "X-Signature" => "sha256=" . hash_hmac("sha256", $this->body, $this->secret),
            "X-Timestamp" => time(),
        ])->post($this->url, $this->body);
        if (!$r->successful()) $this->release(); // retry
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'system_design.api',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Какие коды 2xx, кроме 200, реально используют в REST API?',
                'answer' => 'Сильное API **различает успешные ответы по смыслу**, а не отдаёт всё через `200 OK`.

| Код | Когда возвращать | Тело | Доп. заголовки |
|---|---|---|---|
| **`200 OK`** | универсальный успех | да | — |
| **`201 Created`** | **`POST` создал ресурс** | новый объект | **`Location: /api/users/42`** |
| **`202 Accepted`** | приняли на **асинхронную** обработку | задача / ссылка на статус | `Location: /api/jobs/123` |
| **`204 No Content`** | успех **без тела** | пусто | — |
| **`206 Partial Content`** | ответ на **`Range`-запрос** | часть | `Content-Range: bytes 0-499/12345` |
| **`207 Multi-Status`** | **batch**, в каждом подзапросе свой статус | массив с per-item статусами | — |
| **`304 Not Modified`** | условный запрос, кэш свеж | пусто | работает с `ETag` |

**Где применять:**

- **`201`** после `POST /api/users` — отдаёт **`Location`** и тело только что созданного объекта. Стандарт REST.
- **`202`** для **долгих операций** — генерация отчёта, отправка письма: тело `{"job_id": "abc", "status_url": "/api/jobs/abc"}`, клиент опрашивает.
- **`204`** для **`DELETE`** и **идемпотентного `PUT`** — успех, нечего возвращать. Не путать с `200` с пустым телом.
- **`206`** для **video streaming**, **download resume** — отдача части файла по `Range: bytes=0-499`.
- **`207`** для **bulk-операций** — `POST /api/users/bulk` создаёт 100 юзеров, каждый со своим статусом.

**Почему важно:**

- клиент по коду **понимает, что делать**, без чтения тела
- **CDN/прокси** кэшируют разные коды по-разному (`200` vs `204`)
- **observability** — отделить «обработали асинхронно» от «обработали моментально» в метриках
- **HATEOAS-friendly** — `201 + Location` ведёт клиента к новому ресурсу

**В Laravel:**

- `response()->json($data, 201)`
- `response()->noContent()` → `204`
- `abort(202)` или `response(null, 202)->header("Location", $url)`',
                'difficulty' => 3,
                'topic' => 'system_design.api',
            ],
        ];
    }
}
