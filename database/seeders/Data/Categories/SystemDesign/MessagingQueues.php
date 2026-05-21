<?php

namespace Database\Seeders\Data\Categories\SystemDesign;

class MessagingQueues
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое очередь сообщений простыми словами?',
                'answer' => '**Message queue** — буфер между компонентами системы. Один сервис **кладёт сообщение** в очередь и идёт дальше, другой **читает и обрабатывает** когда удобно.

Аналогия: **почтовый ящик** — отправитель опустил письмо и пошёл по делам, получатель забрал, когда смог. Они **не видят друг друга** и **не синхронизированы по времени**.

**Зачем используют:**

- **Асинхронность** — пользователь не ждёт, пока отправится email или обработается тяжёлый отчёт. HTTP-запрос возвращает `202 Accepted`, работа уходит в очередь.
- **Сглаживание пиков** — в Чёрную Пятницу прилетело 10k заказов в секунду, БД тянет 500. Очередь буферизует, воркеры обрабатывают со своей скоростью.
- **Decoupling** — `OrderService` не знает про `MailService`/`InvoiceService`/`AnalyticsService`. Бросил событие `OrderCreated` — кто хотел, подписался.
- **Надёжность** — сообщения **переживают рестарт** воркера/сервера. При сбое обработки — **retry** автоматически.

**Брокеры:** `RabbitMQ`, `Kafka`, `AWS SQS`, `Redis Streams`. В Laravel — обёртка через `Queue::push()` / `dispatch()`.',
                'code_example' => '<?php
// Producer: уйти из контроллера за миллисекунды
SendInvoiceEmail::dispatch($order)->onQueue("emails");

// Consumer: воркер тянет сообщения и выполняет
// php artisan queue:work --queue=emails

class SendInvoiceEmail implements ShouldQueue
{
    public function handle(): void
    {
        Mail::to($this->order->user)->send(new InvoiceMail($this->order));
    }
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Producer и Consumer в очередях?',
                'answer' => 'Две роли в системе обмена сообщениями:

- **Producer** (издатель, продюсер) — кто **кладёт сообщения** в очередь
- **Consumer** (подписчик, потребитель) — кто **читает и обрабатывает**

Они **не знают друг о друге** — общаются только через брокер.

**Связи могут быть разными:**

- Один Producer → одна очередь → один Consumer (классическая очередь задач)
- Один Producer → много Consumer-ов **на одной очереди** — это **work queue**, сообщения **делятся** между ними (каждое идёт **только одному**, балансировка нагрузки)
- Один Producer → много Consumer-ов **по разным очередям** — это **pub/sub**, **каждый** получает **копию** сообщения (нотификации, broadcast)
- Много Producer-ов → одна очередь — нормально, очередь сериализует

**В Laravel:**

- Producer — `dispatch(new SendEmail(...))` в контроллере/сервисе
- Consumer — процесс `php artisan queue:work`, чаще под `supervisor` в нескольких копиях
- Несколько `queue:work` на одну очередь = **work queue** (балансировка)
- События + listener-ы = **pub/sub** внутри Laravel',
                'code_example' => '<?php
// Producer
SendOrderEmail::dispatch($order)->onQueue("emails");
GenerateInvoice::dispatch($order)->onQueue("pdf");

// Work queue: несколько одинаковых воркеров на одной очереди
// (под supervisor запущены 4 копии — сообщения делятся 1:1)
// php artisan queue:work --queue=emails

// Pub/sub: одно событие → несколько listener-ов
event(new OrderCreated($order));
// → SendOrderEmailListener
// → NotifyWarehouseListener
// → TrackOrderAnalyticsListener',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'В чём разница между RabbitMQ и Kafka?',
                'answer' => 'RabbitMQ - классический message broker по AMQP 0.9.1: маршрутизация через exchanges (direct/topic/fanout/headers), сообщение удаляется после ack, push-модель, сложная routing-логика, ordering на уровне очереди. Kafka - распределённый append-only лог: сообщения хранятся по retention (часы/дни/forever), ничего не удаляется по факту чтения, consumer сам держит offset (pull), горизонтально масштабируется через partitions, ordering гарантируется только внутри partition. SQS - managed AWS-очередь, Standard (at-least-once, без порядка) и FIFO (порядок внутри MessageGroupId + дедупликация по MessageDeduplicationId в 5-минутном окне; AWS позиционирует это как "exactly-once" на уровне доставки, но end-to-end для side-effects всё равно нужен идемпотентный consumer; пропускная способность ~300 TPS на FIFO-queue без батчинга, до 3000 msg/s с batching по 10 сообщений; в режиме High Throughput for FIFO квоты считаются на partition (message group) и общий потолок поднимается до 70 000+ msg/s/queue). Производительность - типичная цифра, которую часто путают: 1) Classic Mirrored Queues RabbitMQ давали ~30-50k msg/s на узел и были замечательны для классического AMQP-кейса, но deprecated в 3.13 и удалены в 4.0. 2) Quorum Queues (RabbitMQ 3.8+, на Raft) - сотни тысяч msg/s, durable, replicated. 3) Streams (RabbitMQ 3.9+, log-based как Kafka) - миллионы msg/s, замысел был догнать Kafka на сходных юзкейсах. Так что в нишах, где RabbitMQ Streams применим, он догоняет Kafka по throughput - но это именно log-based вариант для event-streaming сценариев, а не альтернатива классическим/quorum очередям. Classic и Quorum queues - это broker/task-queue модель с ack/routing/priority, которая по чистой пропускной способности не конкурирует с log-based Kafka и не должна напрямую с ней сравниваться. Выбор делается по семантике: если нужна богатая routing-логика, per-message ack, priority queues, RPC - RabbitMQ classic/quorum; event streaming, infinite retention, replay, log compaction, partition-параллелизм - Kafka или RabbitMQ Streams. Kafka типично - миллионы msg/s/брокер. Выбор: RabbitMQ для task queue с богатой routing; Kafka для event streaming, аналитики, CDC, replay; SQS - serverless AWS.',
                'difficulty' => 4,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое at-most-once, at-least-once, exactly-once в очередях?',
                'answer' => 'At-most-once - сообщение доставится 0 или 1 раз (можно потерять при сбое), реализуется через fire-and-forget без ack. At-least-once - 1 или больше раз: producer ретраит при отсутствии ack, consumer ack-ает после обработки; стандарт SQS Standard, RabbitMQ с ack, Kafka с acks=all. Дубли возможны - нужна идемпотентность. Exactly-once строго в распределённой системе недостижимо (Two Generals Problem). На практике достигается комбинацией at-least-once + идемпотентный consumer (dedup по message-id) - это называется "effectively-once". Kafka даёт transactional EOS внутри своих топиков (idempotent producer + transactions + isolation.level=read_committed), но при выходе наружу (БД, HTTP) ответственность ложится на consumer. SQS FIFO в пределах 5-минутного окна дедуплицирует сообщения с одинаковым MessageDeduplicationId и сохраняет порядок внутри MessageGroupId - это НЕ end-to-end exactly-once для side-effects в БД/HTTP. Consumer всё равно должен быть идемпотентным: visibility timeout может истечь до ack, при сбое consumer-а сообщение вернётся другому, и ваш код увидит повтор.',
                'difficulty' => 4,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Какие гарантии порядка сообщений дают разные брокеры?',
                'answer' => 'Kafka: строгий порядок только внутри partition; partition выбирается по hash(key), поэтому сообщения с одним key всегда в одной partition (используется для "все события заказа №42 в одном порядке"). При нескольких partition между ними порядка нет. RabbitMQ: порядок в пределах одной очереди при одном consumer; при нескольких consumer-ах порядок ломается из-за параллельной обработки и redelivery. SQS Standard - порядок не гарантируется вообще; SQS FIFO - порядок в пределах MessageGroupId. Если важен порядок - выбирай ключ группировки осознанно (по entity_id) и держи parallelism=1 на ключ (single-active consumer в RabbitMQ, partition=consumer в Kafka).',
                'difficulty' => 4,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое идемпотентность простыми словами?',
                'answer' => '**Идемпотентность** — свойство операции, при котором её **повторное выполнение даёт тот же результат**, что и первое.

Аналогия: **кнопка лифта** — нажми 1 раз или 5 раз, лифт всё равно приедет **один раз**.

**Формально:** `f(f(x)) == f(x)`.

**В HTTP** (RFC 9110):

- **идемпотентные**: `GET`, `HEAD`, `PUT`, `DELETE`, `OPTIONS`
- **не идемпотентен**: `POST` — каждый вызов создаёт новый ресурс
- `PATCH` — **зависит от тела** (`{"status":"paid"}` — да; `{"qty":"+1"}` — нет)

**Зачем критично в очередях:**

- **at-least-once** доставка — стандарт у `Kafka`, `RabbitMQ` с ack, `SQS Standard`
- **гарантирует дубли** при сбоях сети, перезапусках consumer-а, `visibility timeout` expiry
- если handler **не идемпотентен** — повтор спишет деньги дважды, отправит две накладные, заминусует склад

**Способы сделать handler идемпотентным:**

1. **Уникальный ключ + INSERT с UNIQUE-constraint** — БД не даст вставить дубль (`idempotency_key`)
2. **Дедупликация по `message_id`** — храним обработанные ID в Redis/таблице с TTL
3. **Логически идемпотентный код** — `UPDATE status = "paid" WHERE id = ?` идемпотентен сам по себе, в отличие от `balance = balance + 100`
4. **Условные операции** — `UPDATE ... WHERE status = "pending"` сработает один раз

**Главное правило:** в распределённой системе **проектируй handler так, чтобы повтор был безопасен** — это всегда дешевле, чем гнаться за exactly-once.',
                'code_example' => '<?php
// Подход 1: уникальный ключ + транзакция
public function handle(PaymentMessage $msg): void
{
    if (Payment::where("idempotency_key", $msg->key)->exists()) {
        return; // уже обработано — выходим тихо
    }

    DB::transaction(function () use ($msg) {
        Payment::create([
            "idempotency_key" => $msg->key,   // UNIQUE constraint
            "user_id" => $msg->userId,
            "amount" => $msg->amount,
        ]);
        $this->charge($msg);
    });
}

// Подход 2: условный UPDATE — идемпотентен сам по себе
DB::update(
    "UPDATE orders SET status = ? WHERE id = ? AND status = ?",
    ["paid", $msg->orderId, "pending"]   // повтор не сработает: уже paid
);

// Подход 3: дедупликация по message_id в Redis
if (!Redis::set("processed:{$msg->id}", 1, "EX", 86400, "NX")) {
    return; // уже видели — пропускаем
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Idempotency-Key и как использовать в платежах?',
                'answer' => 'Idempotency-Key - уникальный токен от клиента в HTTP-заголовке. Сервер хранит маппинг ключ → результат на N часов. Если приходит повторный запрос с тем же ключом - возвращаем кэшированный ответ, реально не выполняя операцию. Это защита от двойных списаний при сетевых ретраях. Stripe, AWS, Twilio - все так делают для платёжных API.',
                'code_example' => '<?php
$key = $request->header("Idempotency-Key");
if ($cached = Cache::get("idemp:$key")) {
    return response()->json($cached);
}
$result = $payment->charge($amount);
Cache::put("idemp:$key", $result, 86400);
return response()->json($result);',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'В чём разница между Pub/Sub и Message Queue?',
                'answer' => 'Два **способа доставки** сообщений с разной семантикой.

| | **Message Queue** (work queue) | **Pub/Sub** (fanout) |
|---|---|---|
| **Кто получит сообщение** | **один** consumer из группы | **все** подписчики |
| **Зачем нужно** | распределить **работу** | разослать **уведомление** |
| **Метафора** | список задач, который разбирают исполнители | газета, на которую подписаны N читателей |
| **Удаление** | после ack — пропадает | у каждого подписчика своя копия |
| **Масштабирование** | добавил воркера — быстрее обработка | добавил подписчика — ещё один получатель |

**Message Queue** — для **задач**: «отправить email», «сгенерить PDF», «обработать видео». Каждое задание должно выполниться **ровно один раз** (с учётом идемпотентности).

**Pub/Sub** — для **событий**: «пользователь зарегистрировался» → одновременно `send_welcome_email`, `create_profile`, `add_bonus`, `notify_analytics`. Каждый подписчик делает **своё** независимо.

**Реализации:**

- **`Kafka`** — pub/sub через **разные `consumer group`** на одном топике. Внутри группы — work queue (партиции делятся между consumer-ами).
- **`RabbitMQ`** — топология через `exchange`:
  - `direct`/`topic` exchange → одна очередь → **work queue**
  - `fanout` exchange → много очередей → **pub/sub**
- **`Redis`** — `LPUSH/BRPOP` (queue) vs `PUBLISH/SUBSCRIBE` или `Streams` с consumer groups
- **`SQS`** — только queue; для pub/sub комбинируется с **`SNS`** (SNS → N SQS)

**В Laravel:** `Queue::push()` / `dispatch()` — work queue; **события + listeners** или `Broadcasting` (`Pusher`/`Reverb`) — pub/sub.',
                'code_example' => '<?php
// Work queue: несколько одинаковых воркеров делят сообщения
SendInvoiceEmail::dispatch($order)->onQueue("emails");
// supervisor запустил 4 копии: php artisan queue:work --queue=emails
// → каждое сообщение получит ровно один воркер

// Pub/sub через события: одно событие → много слушателей
event(new OrderCreated($order));
// → SendWelcomeEmailListener::handle()
// → CreateInvoiceListener::handle()
// → NotifyWarehouseListener::handle()
// → TrackAnalyticsListener::handle()

// RabbitMQ fanout
// $channel->exchange_declare("order.events", "fanout");
// $channel->basic_publish($msg, "order.events");
// Все привязанные очереди получат копию

// Kafka: разные consumer groups читают независимо
// group.id=email-sender  → читает orders, шлёт письма
// group.id=analytics     → читает те же orders, пишет в ClickHouse',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Dead Letter Queue?',
                'answer' => '**Dead Letter Queue** (`DLQ`) — отдельная очередь, куда **сбрасываются сообщения**, которые **не удалось обработать** после N попыток или которые «протухли» по времени.

Аналогия: «**проблемные письма**» — на почте есть отдельный ящик для конвертов с нечитаемым адресом, чтобы они не блокировали сортировку остальных.

**Когда сообщение попадает в DLQ:**

- **превышен `max_attempts`** (обычно 3–5 retry)
- истёк `message_ttl` (`x-message-ttl` в RabbitMQ)
- очередь переполнилась (`x-max-length`)
- consumer вернул `nack` без re-queue

**Зачем нужна:**

- **не теряем сообщения** — даже неудачные хранятся для разбора
- **не блокируем pipeline** — «отравленное» сообщение (`poison message`) не крутится бесконечно, не съедает воркера
- **дебаг** — видно, что именно ломалось, можно проиграть после фикса
- **алертинг** — рост DLQ = инцидент, заводим алерт

**Реализации:**

- **`RabbitMQ`** — настраивается через `x-dead-letter-exchange`/`x-dead-letter-routing-key`
- **`SQS`** — встроенное поле `Redrive Policy` со ссылкой на отдельную DLQ
- **`Kafka`** — нет нативного DLQ, делают руками: при N сбоях продьюсят в топик `orders.DLT`
- **`Laravel`** — таблица `failed_jobs` (`php artisan queue:retry all`, `queue:flush`)

**Что обычно сохраняют в DLQ-сообщении:**

- оригинальное тело
- **последний exception** + stack trace
- timestamp первой попытки и сбоя
- счётчик попыток

**Анти-паттерн:** автоматический бесконечный retry **в той же очереди** без backoff — `poison message` блокирует обработку всего остального.',
                'code_example' => '<?php
// Laravel — failed_jobs работает как DLQ из коробки
class ProcessPayment implements ShouldQueue
{
    public int $tries = 3;                    // 3 попытки
    public array $backoff = [10, 60, 300];    // exponential backoff

    public function handle(): void
    {
        $this->gateway->charge($this->order);
    }

    public function failed(Throwable $e): void
    {
        // вызовется после $tries попыток — джоба ушла в failed_jobs
        Log::error("payment failed", [
            "order_id" => $this->order->id,
            "error" => $e->getMessage(),
        ]);
        Alert::critical("Payment in DLQ: {$this->order->id}");
    }
}

// Управление DLQ
// php artisan queue:failed              — посмотреть
// php artisan queue:retry {id}          — повторить
// php artisan queue:flush               — очистить',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое backpressure?',
                'answer' => '**Backpressure** (**обратное давление**) — механизм, при котором **медленный consumer заставляет producer-а замедлиться**, чтобы не переполнить очередь и не уронить систему.

Аналогия: **конвейер на заводе** — если рабочий в конце не успевает, конвейер замедляется или останавливается. Если бы он гнал на той же скорости, детали падали бы на пол.

**Зачем нужен:**

- защита от **OOM**: очередь без ограничения вырастет до памяти/диска, **процесс упадёт**
- защита от **каскадного отказа**: переполненная очередь → producer ждёт → API таймаутится → клиент ретраит → ещё хуже
- адаптация к **пикам нагрузки** без необходимости масштабироваться мгновенно

**Способы реализации:**

- **bounded queue** — лимит на размер; при заполнении producer **блокируется** или получает ошибку
- **`pause`/`resume`** на стороне consumer-а — в Kafka вызывается `consumer.pause(partitions)`, когда внутренний буфер обработки заполнен
- **`prefetch` / QoS** — `RabbitMQ` `basic.qos(prefetch_count=10)` — не давать consumer-у больше 10 необработанных
- **rate limiting на producer** — `token bucket` ограничивает RPS
- **reactive streams** — `RxJS`, `Project Reactor`: subscriber **запрашивает** N элементов через `request(n)`, producer не шлёт больше
- **TCP-style flow control** — окно подтверждений

**Стратегии при переполнении** (`overflow policy`):

- **block** — producer ждёт, пока освободится место
- **drop newest / drop oldest** — терять данные сознательно (метрики, телеметрия)
- **fail fast** — вернуть `503` клиенту с `Retry-After`
- **spill to disk** — записать в файл, потом догнать

**Без backpressure:** система ломается под пиками, ловите cascading failures и thundering herd при восстановлении.',
                'code_example' => '<?php
// RabbitMQ — QoS ограничивает количество unacknowledged сообщений
$channel->basic_qos(
    prefetch_size: 0,
    prefetch_count: 10,   // не давать больше 10 in-flight
    a_global: false,
);

// Kafka — pause/resume на партиции
if ($buffer->size() > $maxBuffer) {
    $consumer->pause($assignedPartitions);
} elseif ($buffer->size() < $minBuffer) {
    $consumer->resume($assignedPartitions);
}

// Bounded queue в приложении (PHP-ish псевдокод)
if ($queue->size() >= $queue->capacity()) {
    throw new QueueFullException("backpressure: producer must slow down");
    // или вернуть 503 Retry-After: 5
}

// Laravel — throttle middleware на producer-стороне
Route::post("/api/events", [EventController::class, "store"])
    ->middleware("throttle:100,1");  // 100 req/min от клиента',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Apache Kafka простыми словами?',
                'answer' => '**`Kafka`** — **распределённый append-only лог** сообщений с высокой пропускной способностью (миллионы msg/s на брокер).

В отличие от классических брокеров (`RabbitMQ`, `SQS`), Kafka **ничего не удаляет** при чтении. Сообщения хранятся **по retention** (часы/дни/forever), и consumer **сам держит offset** — может перечитать историю.

Аналогия: огромный **журнал** — события дописываются в конец, любой подписчик читает **с любой позиции** в своём темпе.

**Ключевые сущности:**

- **Topic** — именованный поток событий (`orders`, `clicks`, `user-events`)
- **Partition** — топик физически разбит на N **упорядоченных** append-only логов. Параллелизм = число партиций
- **Offset** — позиция сообщения в партиции (монотонно растущий long)
- **Broker** — узел кластера, хранит партиции
- **Replica** — копия партиции на других брокерах для отказоустойчивости (обычно `replication.factor=3`)
- **Producer** — пишет в топик
- **Consumer** + **consumer group** — читает; партиции делятся между членами группы

**Модель доставки:** **pull** (consumer сам опрашивает) vs push в RabbitMQ. Это даёт натуральный backpressure.

**Где применяется:**

- **event streaming** — `OrderCreated`, `PaymentProcessed` в реальном времени
- **аналитика** — потоковая обработка (Kafka Streams, Flink, ClickHouse)
- **CDC** (Change Data Capture) — `Debezium` тянет из WAL Postgres → Kafka
- **log aggregation** — центральный лог-сервер от всех сервисов
- **event sourcing** — топик как источник правды

**Чем отличается от `RabbitMQ`:**

| | **Kafka** | **RabbitMQ (classic/quorum)** |
|---|---|---|
| Модель | log + offset | queue + ack |
| Удаление | по retention | после ack |
| Routing | по partition (hash key) | через exchanges |
| Replay | да | нет |
| Throughput | миллионы msg/s | сотни тысяч |',
                'difficulty' => 3,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Event Sourcing простыми словами?',
                'answer' => '**Event Sourcing** — паттерн, при котором **состояние** сущности хранится **не как текущий снимок**, а как **последовательность событий**, которые к нему привели.

**Классический CRUD:** `accounts(id, balance)` → `balance = 1000`. Видим только текущее значение, история теряется.

**Event Sourcing:** `events(stream_id, version, type, payload, occurred_at)`:

```
account-42, v1, MoneyDeposited, {amount: 500}
account-42, v2, MoneyDeposited, {amount: 700}
account-42, v3, MoneyWithdrawn, {amount: 200}
```

Текущий баланс **вычисляется** проигрыванием событий: `0 + 500 + 700 - 200 = 1000`.

**Плюсы:**

- **полный аудит из коробки** — кто, когда, что изменил, без отдельной audit-таблицы
- **time travel** — состояние на любой момент в прошлом
- **легко перестроить read-модели** (`projections`) — изменил формат отчёта → переиграл события
- хорошо ложится на **`Kafka`** (топик = event store)
- **естественный fit** для финансов, бухгалтерии, любых доменов с строгой регуляторкой

**Минусы:**

- **сложнее CRUD** — кривая обучения для команды
- **миграции схемы событий** болезненны: старые события нельзя «переписать», надо `upcasting`
- **eventual consistency** в read-моделях
- **производительность чтения** — проигрывание тысяч событий долгое (решается **snapshot-ами** через каждые N событий)
- сложно с **`GDPR` right to erasure** — события «не удаляются»

**Часто путают с CQRS:** это **разные паттерны**. Event Sourcing — *способ хранить* write-модель. CQRS — *разделение* модели чтения и записи. Можно делать CQRS без ES (write в Eloquent, read в Elasticsearch) и наоборот, но **связка ES+CQRS** — каноническая (без CQRS чтение текущего состояния через replay медленное).

**Когда брать:** строгий аудит, сложный домен с временной семантикой (отмены, корректировки), нужны разные view одних данных. **Когда не брать:** простой CRUD — ES добавит сложность без выгоды.',
                'code_example' => '<?php
// События (immutable value objects)
final class MoneyDeposited
{
    public function __construct(public readonly int $amount) {}
}

final class MoneyWithdrawn
{
    public function __construct(public readonly int $amount) {}
}

// Aggregate: восстанавливает state из истории событий
final class Account
{
    private int $balance = 0;

    public static function fromHistory(array $events): self
    {
        $account = new self();
        foreach ($events as $event) {
            $account->apply($event);
        }
        return $account;
    }

    private function apply(object $event): void
    {
        match (true) {
            $event instanceof MoneyDeposited => $this->balance += $event->amount,
            $event instanceof MoneyWithdrawn => $this->balance -= $event->amount,
        };
    }

    public function balance(): int { return $this->balance; }
}

// Snapshot каждые 100 событий — оптимизация
// reconstruct: snapshot_v100 + events[101..current]',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое CQRS простыми словами?',
                'answer' => 'CQRS (Command Query Responsibility Segregation) - разделение модели чтения и записи. Команды (write) меняют состояние, не возвращают данных. Запросы (read) только читают и могут использовать денормализованную модель для скорости. Простыми словами: для записи в банк используешь форму с полным набором полей (write-модель), для просмотра выписки - удобно отформатированный отчёт (read-модель). ВАЖНОЕ заблуждение: CQRS и Event Sourcing - РАЗНЫЕ паттерны, их часто упоминают вместе и путают. CQRS можно (и обычно стоит) применять БЕЗ Event Sourcing: типичный продакшен-сетап - запись через Eloquent-агрегаты в Postgres-master, чтение через read-only реплики или денормализованную таблицу/Elasticsearch/Redis-индекс - это уже полноценный CQRS, никаких событий хранить не надо. Event Sourcing - лишь один из способов хранить write-модель (как лог событий вместо текущего state), и его можно использовать без CQRS (хотя на практике без CQRS он почти не имеет смысла из-за плохой производительности чтения текущего состояния). Когда CQRS оправдан: разные требования к нагрузке на чтение и запись (read >> write), сложные read-модели (агрегации, поисковая выдача), нужно несколько read-проекций одних данных. Когда не нужен: простой CRUD - CQRS добавляет сложность без выгоды.',
                'difficulty' => 4,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Outbox pattern?',
                'answer' => 'Outbox - паттерн для надёжной отправки событий в очередь из транзакции. Проблема: если в транзакции и пишем в БД, и шлём в Kafka - могут разойтись (БД сохранила, Kafka упала). Решение: пишем событие в таблицу outbox в той же транзакции что и бизнес-данные. Отдельный процесс читает outbox и шлёт в Kafka, помечая как отправленные. Гарантирует at-least-once и атомарность с БД-операцией.',
                'code_example' => '<?php
DB::transaction(function () use ($order) {
    $order->save();
    OutboxEvent::create([
        "type" => "OrderCreated",
        "payload" => json_encode($order->toArray()),
        "sent_at" => null,
    ]);
});
// Отдельный воркер читает unsent события и шлёт в Kafka',
                'code_language' => 'php',
                'difficulty' => 5,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Change Data Capture (CDC) и зачем он нужен?',
                'answer' => 'Change Data Capture - паттерн для извлечения изменений из БД и стрима их в другую систему (Kafka, Elasticsearch, ClickHouse, дата-озеро) без изменения исходного приложения. Решает классическую проблему dual write: когда сервис должен записать в основную БД И в очередь/индекс одновременно - между ними нет распределённой транзакции, и одна из записей может пропасть при сбое. Outbox-паттерн решает это с помощью изменений в коде (запись в outbox-таблицу в той же транзакции), а CDC - вообще без правки кода. Как работает: инструмент (Debezium - стандарт де-факто, или AWS DMS, Maxwell, GoldenGate) подключается к транзакционному логу СУБД: WAL (write-ahead log) в PostgreSQL через logical replication, binlog в MySQL, change tracking в SQL Server, oplog в MongoDB. WAL хранит каждое INSERT/UPDATE/DELETE в порядке коммита; CDC-агент читает его, конвертирует строки в JSON-сообщения и публикует в Kafka-топик "db.public.users" с before/after состояниями. Преимущества: 1) Нулевое влияние на код приложения - монолиту вообще не надо знать про Kafka. 2) Гарантия at-least-once - WAL хранит все изменения; CDC-агент может перечитать с нужного offset после рестарта. 3) Транзакционная консистентность - в Kafka попадают только закоммиченные изменения, в правильном порядке. 4) Нет dual-write - изменение либо записалось в БД (и попадёт в Kafka), либо нет. Недостатки: 1) Структура сообщений привязана к схеме БД - переименование колонки сломает downstream consumers, нужна осторожная schema evolution и версионирование событий через Outbox-таблицу как промежуточный слой. 2) Сложность в эксплуатации (мониторинг replication slot lag в PG, дисковое место под WAL, перезапуск Debezium). 3) Не подходит для отправки бизнес-событий ("OrderShipped") - CDC видит изменения rows, не доменные события; для этого нужен явный outbox с domain-событиями. Применение: репликация монолит → микросервис без передачи нагрузки на исходный сервис, обновление поискового индекса (Elasticsearch), стриминг в data warehouse (Snowflake, BigQuery), создание audit-лога, сине-зелёные миграции БД.',
                'code_example' => '-- PostgreSQL: включить logical replication
-- postgresql.conf:
--   wal_level = logical
--   max_replication_slots = 4
--   max_wal_senders = 4

-- Создать публикацию для Debezium
CREATE PUBLICATION debezium_pub FOR TABLE orders, users;

-- Replication slot создаст сам Debezium при подключении
SELECT * FROM pg_replication_slots;

-- В Debezium connector config (kafka-connect):
-- {
--   "connector.class": "io.debezium.connector.postgresql.PostgresConnector",
--   "database.hostname": "pg.internal",
--   "database.dbname": "shop",
--   "publication.name": "debezium_pub",
--   "slot.name": "debezium_slot",
--   "topic.prefix": "shop"
-- }
-- → Debezium publishes:
--   topic "shop.public.orders" message:
--   { "before": null,
--     "after": {"id": 42, "status": "paid"},
--     "op": "c", "ts_ms": 1717000000 }

-- Consumer (Laravel/PHP):
-- читает Kafka-топик shop.public.orders, обновляет ES/Redis-индекс
-- Изменения приходят в порядке коммита, можно безопасно реплицировать состояние

-- Outbox + CDC = best of both worlds
-- 1. Сервис в той же транзакции пишет domain-событие в outbox-таблицу
-- 2. Debezium читает WAL и публикует events из outbox в Kafka
-- 3. Получаем доменные события + транзакционную гарантию + zero dual-write',
                'code_language' => 'sql',
                'difficulty' => 5,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое топик, партиция и реплика в Kafka?',
                'answer' => 'Три ключевые сущности Kafka, без которых ничего не понять.

- **Топик** (topic) — **именованный поток сообщений**, логическая категория (`orders`, `user-clicks`, `audit-log`). Producer пишет в топик, consumer читает.
- **Партиция** (partition) — топик физически **разбит на N партиций**, каждая — **упорядоченный append-only лог**. **Параллелизм** чтения и записи = **числу партиций**: 10 партиций → до 10 consumer-ов могут читать параллельно. Сообщение попадает в партицию по `hash(key)` — все события одного `key` идут в **одну партицию** и сохраняют **порядок**.
- **Реплика** (replica) — каждая партиция **копируется на N брокеров** для отказоустойчивости. **Одна реплика — leader** (через неё идут все чтения/записи), остальные — **followers** (догоняют leader). При падении leader-а один из followers становится новым leader-ом.

**Replication factor** — сколько копий каждой партиции в кластере. **Обычно `3` для прода** (выживает падение 1 брокера).

**Зачем это всё:**

- партиции → **горизонтальное масштабирование** и параллелизм
- реплики → **отказоустойчивость** и доступность
- ключ → **гарантированный порядок** внутри партиции',
                'difficulty' => 2,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое consumer group и как Kafka распределяет партиции между потребителями?',
                'answer' => '**Consumer group** — группа потребителей с **одним `group.id`**, между которыми Kafka **делит партиции** топика.

**Главное правило:** **каждая партиция** в момент времени читается **ровно одним** consumer-ом из группы.

**Как делятся партиции** (assignment):

- 10 партиций + 2 consumer-а → каждому **по 5**
- 10 партиций + 10 consumer-ов → каждому **по 1** (максимальный параллелизм)
- 10 партиций + 15 consumer-ов → 10 работают, **5 простаивают** (`partitions` — потолок параллелизма)
- один consumer упал → оставшимся передаются его партиции (**rebalance**)

**Зачем такая модель:**

- **горизонтальное масштабирование** — добавил consumer → быстрее обработка
- **отказоустойчивость** — падение consumer-а не теряет сообщения, их подхватит другой
- **гарантия порядка по ключу** — все сообщения с одним `hash(key)` идут в одну партицию → к одному consumer-у → в порядке

**Разные consumer groups читают НЕЗАВИСИМО:**

- `group.id=email-sender` читает топик `orders` и шлёт письма
- `group.id=analytics` читает **тот же топик** в свою сторону, **свой offset**
- → это и есть **pub/sub поверх Kafka**

**Assignment strategies:**

- **`range`** (default) — партиции делятся диапазонами; неравномерно при разном числе топиков
- **`round-robin`** — равномерно, но при rebalance все партиции переезжают
- **`sticky`** — старается оставить партиции у тех же consumer-ов
- **`cooperative-sticky`** — incremental rebalance, не stop-the-world (с Kafka 2.4+)

**Подводный камень:** при rebalance группа **не потребляет вообще** (классический протокол) — поэтому большие группы и нестабильные сети больно.',
                'code_example' => '<?php
// php-rdkafka — consumer в группе
$conf = new RdKafka\\Conf();
$conf->set("group.id", "email-sender");           // имя группы
$conf->set("bootstrap.servers", "kafka:9092");
$conf->set("auto.offset.reset", "earliest");
$conf->set("partition.assignment.strategy", "cooperative-sticky");

$consumer = new RdKafka\\KafkaConsumer($conf);
$consumer->subscribe(["orders"]);

while (true) {
    $message = $consumer->consume(1000);

    if ($message->err === RD_KAFKA_RESP_ERR_NO_ERROR) {
        processOrder($message->payload);
        $consumer->commit($message);   // фиксируем offset для этой группы
    }
}

// Запустили 4 копии — Kafka раздаст 10 партиций (по 2-3 на копию)
// Параллельно другая группа "analytics" читает те же сообщения независимо',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое offset в Kafka и как он коммитится?',
                'answer' => '**Offset** — **позиция сообщения внутри партиции**, монотонно растущее число (long, 64 бита).

Каждое сообщение в партиции имеет свой offset: 0, 1, 2, … Consumer group хранит **«последний обработанный offset»** в специальном внутреннем топике **`__consumer_offsets`**.

**Три стратегии коммита** — главный выбор семантики доставки:

| Стратегия | Что делает | Гарантия |
|---|---|---|
| **Autocommit** (`enable.auto.commit=true`) | клиент каждые `auto.commit.interval.ms` (5 сек по умолчанию) коммитит **уже выданные** сообщения | **at-most-once** — упал между autocommit и обработкой → **потеря** |
| **Manual после обработки** (`commitSync`/`commitAsync`) | код **сам** фиксирует offset **после успешной обработки** | **at-least-once** — упал до commit → сообщение придёт **ещё раз** → нужна идемпотентность |
| **Transactional** | offset + результат в **одной транзакции Kafka** | **exactly-once** *внутри Kafka* (read-process-write) |

**Стандарт на проде:** manual commit **после** обработки + идемпотентный handler.

**Подводный камень autocommit:**

```
1. poll() вернул 10 сообщений
2. обработал первые 3
3. сработал autocommit — закоммитил offset последнего из 10 (!)
4. упал — оставшиеся 7 потеряны
```

**`commitSync` vs `commitAsync`:**

- `commitSync` — блокирующий, гарантия, но снижает throughput
- `commitAsync` — fire-and-forget, быстрее, но при сбое возможна **потеря** последнего commit (хотя сообщения переобработаются)
- паттерн: `commitAsync` каждый poll + `commitSync` при штатном shutdown

**Exactly-once вне Kafka** (БД, HTTP) транзакцией Kafka **не достичь** — там работает связка at-least-once + идемпотентный consumer (`effectively-once`).',
                'code_example' => '<?php
// php-rdkafka — manual commit после обработки
$conf->set("enable.auto.commit", "false");      // отключаем autocommit
$conf->set("auto.offset.reset", "earliest");

while (true) {
    $message = $consumer->consume(1000);
    if ($message->err !== RD_KAFKA_RESP_ERR_NO_ERROR) continue;

    try {
        DB::transaction(function () use ($message) {
            // 1. бизнес-логика (идемпотентная — по message key)
            processOrder($message);

            // 2. дедуп по offset+partition в той же транзакции
            DB::table("processed_offsets")->insert([
                "topic" => $message->topic_name,
                "partition" => $message->partition,
                "offset" => $message->offset,
            ]);
        });

        // 3. только теперь — commit в Kafka
        $consumer->commit($message);   // at-least-once + idempotent = effectively-once
    } catch (Throwable $e) {
        Log::error("failed offset {$message->offset}", ["e" => $e]);
        // НЕ коммитим — сообщение придёт ещё раз
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Зачем в Kafka партиционирование по ключу и как оно работает?',
                'answer' => '**Партиционирование по ключу** — главный инструмент для **гарантии порядка** обработки в Kafka.

**Как выбирается партиция:**

- **с ключом** → `partition = hash(key) % num_partitions` (default: `Murmur2`)
- **без ключа** → round-robin или `sticky partitioner` (батчит в одну партицию, потом меняет)
- **явная партиция** → можно указать прямо в producer

**Свойства партиционирования по ключу:**

- все сообщения с **одинаковым `key`** → **одна и та же партиция**
- внутри партиции порядок **строго сохраняется** (append-only лог)
- **глобальный порядок** для конкретного `key` через весь pipeline: producer → partition → consumer

**Зачем это нужно — типичные кейсы:**

- **порядок по сущности** — `key = order_id`: все события заказа (`OrderCreated` → `OrderPaid` → `OrderShipped`) обрабатываются **строго последовательно** одним consumer-ом
- **session affinity** — `key = user_id`: события пользователя в правильном порядке
- **денежные операции** — `key = account_id`: транзакции счёта без race conditions
- **stateful processing** — Kafka Streams агрегирует по ключу локально, не пересылая по сети

**Подводные камни:**

- **изменение `num_partitions`** ломает раскладку: hash тех же ключей попадёт в другие партиции → **порядок ломается**. Партиции **только добавляют** в конце или через mirror в новый топик
- **hot key** — `key = country_id`, в России много трафика → одна партиция перегружена. Решение: `key = user_id`, не `country`
- **без ключа порядок** есть только **внутри одной партиции**, между партициями — нет
- **гарантия порядка теряется при retry** producer-а, если не включён `enable.idempotence=true` и `max.in.flight.requests.per.connection > 1`',
                'code_example' => '<?php
// Producer с ключом — все события заказа идут в одну партицию
$producer = new RdKafka\\Producer($conf);
$topic = $producer->newTopic("orders");

foreach ($events as $event) {
    $topic->produce(
        RD_KAFKA_PARTITION_UA,   // unassigned — Kafka выберет по hash(key)
        0,
        json_encode($event),
        key: (string) $event[\'order_id\']   // ← ключ = order_id
    );
}

// Producer без ключа — round-robin/sticky
$topic->produce(RD_KAFKA_PARTITION_UA, 0, json_encode($telemetryEvent));
// → распределится равномерно, но порядок только внутри партиции

// Кастомный partitioner — например, по тенанту
$conf->set("partitioner", "murmur2_random");

// Гарантия порядка при ретраях
$conf->set("enable.idempotence", "true");
$conf->set("max.in.flight.requests.per.connection", "5");  // safe с idempotence',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что означают уровни acks=0, acks=1, acks=all у Kafka-продюсера?',
                'answer' => '`acks` — настройка producer-а, задающая **компромисс между скоростью и надёжностью**. Какой ответ от брокера достаточен, чтобы считать запись успешной.

| `acks` | Ждём | Гарантия | Латентность | Когда брать |
|---|---|---|---|---|
| **`0`** | ничего, fire-and-forget | **at-most-once** — теряем при любом сбое | минимальная | метрики, телеметрия, логи |
| **`1`** (default до 3.0) | leader записал | **теряем при падении leader до репликации** | средняя | средний компромисс |
| **`all`** / **`-1`** | leader + все **ISR** подтвердили | **at-least-once** — не теряем, пока жив хоть 1 ISR | максимальная | финансы, заказы, важные события |

**Что такое `ISR` (In-Sync Replicas):** реплики партиции, которые **догнали leader-а** в пределах `replica.lag.time.max.ms` и считаются синхронными. Только из ISR может быть выбран новый leader.

**Боевой набор для durability:**

```
acks=all
min.insync.replicas=2          # требуем хотя бы 2 ISR
enable.idempotence=true        # защита от дублей при ретраях
retries=Integer.MAX_VALUE      # бесконечные ретраи
replication.factor=3           # на топик
```

**Что значит `min.insync.replicas=2`** при `acks=all`: если ISR упал ниже 2 (один брокер отвалился) — producer получает `NotEnoughReplicasException` вместо тихой потери. Лучше **остановить запись**, чем потерять данные.

**Подводный камень:** `acks=all` + `replication.factor=3` + `min.insync.replicas=1` — теоретически можно потерять данные после повышения отставшей реплики (нужно `min.insync.replicas=2`).

**Изменение с Kafka 3.0:** дефолт `acks` поменяли на **`all`** (раньше был `1`) — сообщество признало, что надёжность важнее по умолчанию.',
                'code_example' => '# Producer config для durability (financial events)
acks=all
enable.idempotence=true
min.insync.replicas=2          # на стороне топика
retries=2147483647             # MAX_INT
max.in.flight.requests.per.connection=5
delivery.timeout.ms=120000
replication.factor=3

# Producer config для throughput (metrics, logs)
acks=1                          # или 0, если потеря допустима
batch.size=65536
linger.ms=20
compression.type=lz4

# Создание топика с правильной durability
kafka-topics.sh --create --topic orders \\
    --replication-factor 3 \\
    --partitions 12 \\
    --config min.insync.replicas=2',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое ISR (In-Sync Replicas) в Kafka и зачем нужен min.insync.replicas?',
                'answer' => 'ISR — это набор реплик партиции (включая лидера), которые догнали лидера в пределах допустимого отставания и считаются синхронными. Только реплика из ISR может быть выбрана новым лидером при failover, поэтому от размера ISR напрямую зависит durability. Параметр min.insync.replicas задаёт, сколько ISR должно подтвердить запись при acks=all: если их меньше, продюсер получит ошибку NotEnoughReplicas и запись остановится. Это страховка от тихой потери данных, когда реплики массово отстали.',
                'difficulty' => 4,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое High-Water Mark в Kafka и какие данные видит потребитель?',
                'answer' => 'High-Water Mark (HW) — это offset последнего сообщения, которое уже подтверждено всеми ISR и считается «зафиксированным». Потребители видят только данные до HW; всё, что выше, — записано на лидера, но ещё не реплицировано и может пропасть при failover. Это и есть механизм согласованности: читатель не увидит сообщение, которое потом исчезнет. Если лидер упадёт до обновления HW, новый лидер начнёт с прежнего HW, и часть лидер-only данных откатится.',
                'difficulty' => 4,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как работает идемпотентный продюсер Kafka и от чего он защищает?',
                'answer' => 'При enable.idempotence=true продюсер получает уникальный Producer ID (PID), а каждому сообщению в рамках партиции присваивается монотонный sequence number. Брокер запоминает последний подтверждённый sequence на PID и при ретрае молча отбрасывает дубликаты. Это закрывает классический сценарий «брокер записал, но ack потерялся в сети» — без идемпотентности продюсер повторил бы и получил два одинаковых сообщения. Идемпотентность работает в пределах одной сессии продюсера и одной партиции, для exactly-once между топиками нужны транзакции.',
                'difficulty' => 4,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как Kafka реализует exactly-once семантику и какова её цена?',
                'answer' => 'Exactly-once в Kafka собирается из трёх кусков: идемпотентный продюсер (acks=all + enable.idempotence) убирает дубликаты при ретраях; транзакции через transactional.id атомарно записывают в несколько партиций/топиков; transactional consumer фиксирует offsets внутри той же транзакции, что и результат обработки. Это работает в схеме read-process-write внутри Kafka. Цена — рост латентности из-за двухфазного коммита через transaction coordinator и обязательная idempotency на стороне внешних сайд-эффектов, которые в транзакцию Kafka не входят.',
                'difficulty' => 4,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое log compaction в Kafka и чем отличается от retention по времени?',
                'answer' => 'Retention по времени или размеру (log.retention.ms, log.retention.bytes) удаляет старые сообщения целиком, независимо от их содержимого. Log compaction работает по ключу: для каждого ключа гарантированно сохраняется как минимум последняя версия, более старые версии того же ключа со временем вычищаются фоновым процессом. Это превращает топик в материализованный «текущий снимок» состояния — типичный кейс для changelog-топиков Kafka Streams и event-sourcing snapshot. Удаление выражается tombstone-сообщением (значение null).',
                'difficulty' => 4,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Чем опасен unclean leader election в Kafka?',
                'answer' => 'Когда unclean.leader.election.enable=true, лидером может стать реплика вне ISR — то есть отстающая. Она не знает о части последних зафиксированных сообщений, и при её повышении эти сообщения теряются, а потребители, которые их уже прочитали, окажутся «впереди» нового лидера — это разрыв согласованности. По умолчанию опция выключена: при отказе всех ISR Kafka лучше остановит запись, чем нарушит durability. Включают её только когда доступность важнее консистентности (например, телеметрия).',
                'difficulty' => 4,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое consumer lag в Kafka и как с ним бороться?',
                'answer' => '**Consumer lag** — **разница между последним offset партиции и offset, до которого дочитал consumer group**.

`lag = log-end-offset - committed-offset`

Растущий лаг означает: **producer пишет быстрее, чем consumer успевает обрабатывать**. Это самая важная метрика здоровья Kafka-пайплайна.

**Чем грозит:**

- **задержка обработки** — сообщения в Kafka, но обработка ещё впереди (события заказа применятся через час)
- **переполнение retention** — если лаг растёт быстрее `retention.ms`, **сообщения удалятся** до обработки → потеря
- **каскадные эффекты** — downstream не получает данные вовремя

**Как лечить (по порядку):**

1. **Масштабирование группы** — добавить consumer-ов, но **не больше числа партиций** (лишние простаивают). Если упёрлись — увеличить **число партиций топика**.
2. **Оптимизация handler-а** — медленный SQL, синхронный HTTP-вызов в обработке → профилировать, оптимизировать
3. **Batch processing** — обрабатывать пачкой, не по одному (`max.poll.records=500`)
4. **Асинхронная выгрузка** тяжёлых side-effects — сложил в БД, ответ снаружи делает отдельный воркер
5. **Уменьшить размер сообщений** или **сжимать** (`compression.type=lz4`)
6. **Параллелизм внутри consumer-а** — отдельный thread pool на обработку при синхронной poll

**Мониторинг:**

- **`kafka-consumer-groups.sh --describe --group <id>`** — текущий лаг по партициям
- **JMX-метрика `records-lag-max`** (consumer-side)
- **`Burrow`** (LinkedIn) — мониторинг лага со status-логикой (OK/WARNING/ERROR)
- **`kafka-exporter` + Prometheus + Grafana** — алерты по тренду

**Если лаг растёт при простаивающих CPU:**

- **rebalance storms** — нестабильные сети, GC-паузы → `session.timeout.ms` increase, `cooperative-sticky` assignor
- **slow poll** — превышение `max.poll.interval.ms` (default 5 мин) → consumer выкидывается из группы
- **slow downstream** — БД упёрлась, HTTP-апстрим тормозит
- **hot partition** — все сообщения с одним ключом → одна партиция → один consumer перегружен',
                'code_example' => '# Посмотреть лаг по группе
kafka-consumer-groups.sh --bootstrap-server kafka:9092 \\
    --describe --group order-processor

# GROUP            TOPIC   PARTITION  CURRENT-OFFSET  LOG-END-OFFSET  LAG
# order-processor  orders  0          1500            1500            0
# order-processor  orders  1          800             4500            3700   ← проблема
# order-processor  orders  2          1200            1250            50

# Алерт в Prometheus
# alert: HighKafkaConsumerLag
# expr: kafka_consumergroup_lag > 10000
# for: 5m
# annotations:
#   summary: "Consumer group {{ $labels.consumergroup }} lag = {{ $value }}"

# Лечение: больше consumer-ов
# Текущее: 3 consumer-а, 12 партиций → каждый по 4
# Увеличиваем до 12 consumer-ов → каждый по 1 → max parallelism
# Если всё ещё лагает — увеличиваем партиции до 24, потом до 48

# Профилировать handler
# php artisan queue:work --memory=128 --timeout=60 -vv',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое rebalance в consumer group Kafka и почему он бывает болезненным?',
                'answer' => 'Rebalance — это перераспределение партиций между потребителями в группе при добавлении, уходе или таймауте участника. На время rebalance в классическом протоколе stop-the-world группа не потребляет вообще — это и есть главная боль: при флакающих сетях или долгих GC-паузах группа постоянно ребалансируется и стоит. Лечат через cooperative-sticky assignor (incremental rebalance, переезжают только нужные партиции), увеличение session.timeout.ms и max.poll.interval.ms, static membership (group.instance.id), который не выкидывает потребителя при кратковременном уходе.',
                'difficulty' => 4,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Чем KRaft режим Kafka отличается от классической схемы с ZooKeeper?',
                'answer' => 'Исторически Kafka использовала ZooKeeper для хранения метаданных кластера, выбора контроллера и leader election партиций — это отдельный кластер, который надо администрировать. KRaft (Kafka Raft) встроил консенсус Raft прямо в брокеров: метаданные хранятся в специальном внутреннем топике, контроллеры — это выделенные узлы Kafka. Плюсы — одна система вместо двух, быстрее восстановление после сбоев, поддержка миллионов партиций. С Kafka 3.3 KRaft GA, в 4.0 ZooKeeper полностью удалён.',
                'difficulty' => 4,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Kafka Streams и зачем нужны changelog-топики?',
                'answer' => 'Kafka Streams — это библиотека для stream-processing поверх Kafka, без отдельного кластера: приложение само читает топик, обрабатывает и пишет результат. Состояние (агрегаты, joins, window) хранится локально в RocksDB у каждого инстанса. Чтобы не потерять его при падении или ребалансе, каждое изменение state store параллельно пишется в специальный compacted-топик — changelog; при перезапуске инстанс восстанавливает RocksDB, проигрывая changelog. Если этот топик удалить, всё локальное состояние придётся пересчитывать с нуля.',
                'difficulty' => 4,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как реализовать обратное давление в Kafka-потребителе на PHP?',
                'answer' => 'Сам Kafka не пушит сообщения — потребитель опрашивает их сам через poll/consume, поэтому при перегрузке достаточно замедлить вызовы. В php-rdkafka используют pause()/resume() на конкретных партициях: пока внутренняя очередь обработчика заполнена, ставят паузу, после освобождения — снимают. Параллельно тюнят max.poll.records и fetch.max.bytes, чтобы не выгребать больше, чем сможет обработать воркер за max.poll.interval.ms. Иначе rebalance посчитает воркера зависшим и заберёт партиции.',
                'difficulty' => 4,
                'topic' => 'system_design.messaging_queues',
            ],
        ];
    }
}
