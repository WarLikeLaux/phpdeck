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
                'answer' => 'Это **разные по архитектуре** инструменты, которые лишь поверхностно выглядят похоже.

| Аспект | **`RabbitMQ`** (classic/quorum) | **`Kafka`** |
|---|---|---|
| **Модель** | broker + queue + `ack` | **append-only log** + offset |
| **Протокол** | `AMQP 0.9.1` | свой бинарный |
| **Routing** | через **exchanges** (`direct`/`topic`/`fanout`/`headers`) | по **partition** = `hash(key)` |
| **Удаление** | после `ack` | **по retention** (часы/дни/forever) |
| **Модель доставки** | **push** consumer-у | **pull** consumer-ом |
| **Replay** | нет | **да** — по offset |
| **Порядок** | в пределах очереди | **в пределах partition** |
| **Throughput** | сотни тысяч msg/s (quorum) | **миллионы** msg/s/брокер |
| **Типичный use case** | task queue, RPC, priority | event streaming, аналитика, `CDC` |

**Подвох с «производительностью RabbitMQ»** — три разных типа очередей:

1. **`Classic Mirrored Queues`** — давали `30-50k msg/s` на узел, **deprecated** в `3.13`, **удалены** в `4.0`.
2. **`Quorum Queues`** (`3.8+`, на `Raft`) — сотни тысяч msg/s, **durable**, **replicated** — стандарт для классического task-queue.
3. **`Streams`** (`3.9+`, log-based как Kafka) — **миллионы msg/s**, нацелены догнать Kafka в event-streaming.

**Важно:** сравнивать чистый throughput Classic/Quorum с Kafka **некорректно** — это разные модели (broker/task-queue vs log). Sравнимы только `RabbitMQ Streams` ↔ `Kafka`.

**`SQS` (managed AWS)** — третий частый вариант:

- **`Standard`** — at-least-once, **без порядка**, очень дёшево
- **`FIFO`** — порядок внутри `MessageGroupId` + дедупликация по `MessageDeduplicationId` (5-минутное окно)
- Throughput: `~300 TPS` на FIFO без батчинга, до `3000 msg/s` с batch=10, **High Throughput** для FIFO — `70 000+ msg/s/queue`
- AWS называет это «exactly-once», но **end-to-end** для side-effects всё равно нужен идемпотентный consumer

**Выбор по семантике, не по бенчмаркам:**

- **`RabbitMQ` classic/quorum** — task queue с богатой routing-логикой, per-message ack, priority, RPC
- **`Kafka`** или **`RabbitMQ Streams`** — event streaming, infinite retention, replay, log compaction, partition-параллелизм
- **`SQS`** — serverless AWS, минимум эксплуатации',
                'difficulty' => 4,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое at-most-once, at-least-once, exactly-once в очередях?',
                'answer' => 'Три **семантики доставки** сообщений — главный архитектурный выбор для любой очереди.

| Семантика | Сколько раз доставится | Когда теряем | Когда дублим |
|---|---|---|---|
| **`at-most-once`** | **0 или 1** | при сбое до обработки | никогда |
| **`at-least-once`** | **1 или больше** | никогда | при сбое до `ack` |
| **`exactly-once`** | **строго 1** | никогда | никогда |

**`at-most-once`** — fire-and-forget без `ack`:

- producer кинул и забыл
- consumer не подтверждает обработку
- **подходит** для метрик, телеметрии, логов — где потеря пары сообщений не критична

**`at-least-once`** — стандарт почти везде:

- `Kafka` с `acks=all`, `RabbitMQ` с `ack`, `SQS Standard`
- producer **ретраит** при отсутствии ack, consumer **ack-ает после обработки**
- **дубли возможны** — нужен **идемпотентный consumer**

**`exactly-once`** — **в распределённой системе строго недостижимо** (теорема **Two Generals Problem**). На практике:

- **«effectively-once»** = `at-least-once` + **идемпотентный consumer** (dedup по `message_id` / `idempotency_key`)
- **`Kafka EOS`** (Exactly-Once Semantics) — `idempotent producer` + `transactions` + `isolation.level=read_committed`. Работает **внутри Kafka** в схеме read-process-write.
- **За пределами Kafka** (БД, HTTP) — транзакция Kafka не помогает, ответственность на consumer.

**Подводный камень `SQS FIFO`:** дедупликация по `MessageDeduplicationId` в 5-минутном окне и порядок в `MessageGroupId` — это **НЕ** end-to-end exactly-once для записи в БД/HTTP. `visibility timeout` может истечь до ack → сообщение вернётся другому consumer-у → ваш код увидит повтор.

**Главное правило:** проектируйте **идемпотентный handler** и не пытайтесь добиться exactly-once на уровне инфраструктуры — это всегда дешевле и надёжнее.',
                'difficulty' => 4,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Какие гарантии порядка сообщений дают разные брокеры?',
                'answer' => '**Гарантии порядка — всегда локальны**, глобального порядка в распределённой очереди не бывает.

| Брокер | Где порядок гарантирован | Где ломается |
|---|---|---|
| **`Kafka`** | внутри **partition** | между partitions — нет |
| **`RabbitMQ`** | в одной очереди при **1 consumer** | при нескольких consumer-ах + redelivery |
| **`SQS Standard`** | **нигде** | везде |
| **`SQS FIFO`** | внутри **`MessageGroupId`** | между группами — нет |

**`Kafka` — порядок по partition:**

- partition выбирается через `partition = hash(key) % num_partitions`
- все сообщения с одинаковым `key` → **одна partition** → строгий порядок
- типичный кейс: `key = order_id` — все события заказа №42 (`Created` → `Paid` → `Shipped`) обрабатываются **последовательно**

**`RabbitMQ` — порядок ломают:**

- **параллельные consumer-ы** на одной очереди (work queue)
- **redelivery** после `nack` — сообщение возвращается, но уже после следующих
- `priority queues` — высокоприоритетные обгоняют

**`SQS FIFO`** — `MessageGroupId` = аналог partition key, дедупликация в 5-минутном окне.

**Главное правило проектирования:** если **важен порядок** — выбирай **ключ группировки осознанно** по `entity_id` и держи **parallelism=1 на ключ**:

- **`Kafka`** — partition обрабатывается **одним consumer-ом** в группе
- **`RabbitMQ`** — `x-single-active-consumer` на очереди
- **`SQS FIFO`** — один consumer на `MessageGroupId`

**Подвох hot key:** если `key = country_id`, в России много трафика → одна partition перегружена, остальные простаивают. Берите более гранулярный ключ (`user_id`, `order_id`).',
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
                'answer' => '**`Idempotency-Key`** — уникальный токен **от клиента** в HTTP-заголовке, под который сервер **кэширует результат** операции на N часов.

**Поток обработки:**

1. Клиент генерирует `UUID` и шлёт в `Idempotency-Key: <uuid>` при `POST /payments`
2. Сервер ищет ключ в хранилище:
   - **есть и `completed`** → возвращает **сохранённый ответ**, не делая операцию
   - **есть и `pending`** → возвращает `409 Conflict` или текущий статус
   - **нет** → создаёт запись `pending`, выполняет операцию, сохраняет ответ
3. Клиент ретраит при таймауте сети **с тем же ключом** — двойного списания не будет

**Зачем критично в платежах:**

- **сетевые таймауты** — клиент не знает, прошёл ли `POST /charge` или ответ потерялся
- **retry на стороне клиента** без идемпотентности списывает деньги дважды
- **at-least-once** в очередях даёт дубли при сбое consumer

**Кто так делает в проде:** `Stripe`, `AWS`, `Twilio`, `PayPal`, `Adyen` — стандарт для платёжных API.

**Подводные камни:**

- **TTL ключа** — обычно `24h`, дольше — раздувает хранилище, короче — клиент не успеет ретрайнуть
- **`UNIQUE`-индекс на ключе** в БД — единственный надёжный способ обработать гонку двух одновременных запросов
- **Никогда не делать внешний HTTP-вызов внутри транзакции** — блокировки висят на время задержки шлюза
- **Webhook от шлюза** тоже должен быть идемпотентен — по своему `event_id`',
                'code_example' => '<?php
$key = $request->header("Idempotency-Key");
if (! $key) {
    abort(400, "Idempotency-Key header required");
}

// 1. Атомарный INSERT — гонка решается UNIQUE-индексом
try {
    DB::table("idempotency_keys")->insert([
        "key" => $key,
        "status" => "pending",
        "created_at" => now(),
    ]);
} catch (QueryException $e) {
    // ключ уже есть — возвращаем кэшированный ответ
    $row = DB::table("idempotency_keys")->where("key", $key)->first();
    if ($row->status === "completed") {
        return response()->json(json_decode($row->response, true));
    }
    return response()->json(["status" => "pending"], 202);
}

// 2. ВНЕ транзакции — внешний вызов
$result = $gateway->charge($amount);

// 3. Сохраняем результат
DB::table("idempotency_keys")->where("key", $key)->update([
    "status" => "completed",
    "response" => json_encode($result),
]);

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
                'answer' => '**`CQRS`** (Command Query Responsibility Segregation) — **разделение модели чтения и записи**.

| | **Command** (write) | **Query** (read) |
|---|---|---|
| **Назначение** | менять состояние | читать данные |
| **Возвращает** | `void` / `id` / ack | данные / DTO |
| **Модель** | агрегат с инвариантами | плоская денормализованная |
| **Хранилище** | OLTP (Postgres, Eloquent) | реплика / `Elasticsearch` / `Redis` |
| **Оптимизация под** | целостность, транзакции | скорость, агрегации |

**Аналогия:** банк. **Write-модель** — форма перевода с полным набором полей и валидацией. **Read-модель** — выписка, удобно отформатированный отчёт.

**Важное заблуждение — `CQRS` и `Event Sourcing` это РАЗНЫЕ паттерны:**

- **`CQRS` без `Event Sourcing`** — типичный прод-сетап: запись через Eloquent-агрегаты в Postgres-master, чтение через read-replicas / денормализованную таблицу / `Elasticsearch` / `Redis`-индекс. Это **уже полноценный `CQRS`**, никаких событий хранить не надо.
- **`Event Sourcing` без `CQRS`** — теоретически возможно, но **почти не имеет смысла**: чтение текущего состояния через replay медленное.
- **`Event Sourcing` — лишь один из способов** хранить write-модель (как лог событий вместо текущего state).

**Когда `CQRS` оправдан:**

- **`read >> write`** — нагрузка на чтение в десятки раз выше
- **Сложные read-модели** — агрегации, поисковая выдача, дашборды
- **Несколько read-проекций** одних данных (карточка / список / экспорт)
- **Разные SLA** на чтение и запись

**Когда НЕ нужен:**

- Простой CRUD — `CQRS` добавит сложность без выгоды
- Нет проблем с производительностью чтения
- Команда не готова к eventual consistency между write и read моделями

**Подвох:** read-модель **всегда чуть отстаёт** от write — нужна стратегия обновления (sync через события, async через `CDC`/Debezium, периодическая re-projection).',
                'difficulty' => 4,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Outbox pattern?',
                'answer' => '**`outbox pattern`** — решение проблемы **dual write** при надёжной отправке событий в очередь из транзакции БД.

**Проблема dual write:**

```
DB::transaction(function () use ($order) {
    $order->save();           // 1. записали в БД
    Kafka::publish($event);   // 2. отправили в Kafka
});
```

**Что может пойти не так:**

- БД сохранила → Kafka **упала** → событие **потеряно**, downstream не узнает
- Kafka получила → БД **откатилась** → событие **есть**, заказа нет (фантом)
- Между БД и Kafka **нет распределённой транзакции** — атомарности не существует

**Решение outbox — две записи в одной БД-транзакции:**

1. Бизнес-данные пишутся в основную таблицу (`orders`)
2. **Событие** пишется в **таблицу `outbox`** (`type`, `payload`, `sent_at = null`) — в **той же транзакции**
3. **Отдельный relay-процесс** читает `outbox WHERE sent_at IS NULL`, шлёт в Kafka, помечает `sent_at = NOW()`
4. Сбой relay → следующая итерация **доберёт** непосланные

**Что гарантирует:**

- **Атомарность** — событие записывается **только** если бизнес-транзакция закоммитилась
- **`at-least-once`** доставка — relay переотправит при сбое
- **Порядок** — relay читает по `id` ASC (или с `FOR UPDATE SKIP LOCKED` для параллелизма)

**Comsumer должен быть идемпотентным** — дубли возможны при retry relay.

**Варианты реализации relay:**

- **Polling** — простой `SELECT ... FOR UPDATE SKIP LOCKED LIMIT 100` каждые `N` мс
- **`CDC`** через **`Debezium`** — читает `WAL` Postgres, публикует в Kafka **без polling** (production-grade)

**Альтернатива** — **`Transactional Outbox + CDC`** (best of both worlds): сервис пишет в outbox, Debezium читает WAL и публикует — нет polling-нагрузки, есть доменные события.',
                'code_example' => '<?php
// 1. Запись в БД + outbox в одной транзакции
DB::transaction(function () use ($order) {
    $order->save();

    OutboxEvent::create([
        "aggregate_type" => "Order",
        "aggregate_id" => $order->id,
        "type" => "OrderCreated",
        "payload" => json_encode($order->toArray()),
        "sent_at" => null,
    ]);
});

// 2. Relay-воркер: периодически читает unsent и шлёт в Kafka
class OutboxRelay
{
    public function handle(): void
    {
        DB::transaction(function () {
            // SKIP LOCKED — параллельные relay не дерутся за строки
            $events = OutboxEvent::whereNull("sent_at")
                ->orderBy("id")
                ->limit(100)
                ->lockForUpdate()
                ->get(); // SELECT ... FOR UPDATE SKIP LOCKED

            foreach ($events as $event) {
                Kafka::publish("orders", $event->payload, key: $event->aggregate_id);
                $event->update(["sent_at" => now()]);
            }
        });
    }
}

// 3. Или Debezium читает WAL Postgres и публикует в Kafka сам',
                'code_language' => 'php',
                'difficulty' => 5,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Change Data Capture (CDC) и зачем он нужен?',
                'answer' => '**`Change Data Capture`** — паттерн извлечения **изменений из транзакционного лога БД** и стрима их в другую систему (`Kafka`, `Elasticsearch`, `ClickHouse`, data lake) **без правки исходного приложения**.

**Решает проблему dual write:** сервис должен записать в основную БД **И** в очередь — между ними нет распределённой транзакции, одна из записей может пропасть. **`outbox pattern`** решает это правкой кода, **`CDC`** — вообще без неё.

**Как работает:**

1. CDC-агент подключается к **транзакционному логу** СУБД:
   - **PostgreSQL** — `WAL` (Write-Ahead Log) через **logical replication**
   - **MySQL** — `binlog`
   - **SQL Server** — change tracking
   - **MongoDB** — `oplog`
2. WAL хранит каждое `INSERT`/`UPDATE`/`DELETE` **в порядке коммита**
3. Агент **читает лог**, конвертирует в JSON, публикует в Kafka-топик `db.public.orders` с `before`/`after`
4. Сохраняет позицию — после рестарта **догоняет** с нужного offset

**Инструменты:**

- **`Debezium`** — стандарт де-факто (Kafka Connect)
- **`AWS DMS`** — managed
- **`Maxwell`** — для MySQL
- **`Oracle GoldenGate`** — enterprise

**Преимущества:**

- **Нулевое влияние на код** — монолиту не нужно знать про Kafka
- **`at-least-once`** — WAL хранит всё, можно перечитать после рестарта
- **Транзакционная консистентность** — в Kafka попадают **только закоммиченные** изменения, в правильном порядке
- **Нет dual-write** — изменение либо есть в БД (и попадёт в Kafka), либо нет

**Недостатки:**

- **Структура сообщений привязана к схеме БД** — переименование колонки сломает downstream. Нужна осторожная schema evolution или **`outbox`-таблица как промежуточный слой**.
- **Сложность эксплуатации** — мониторинг **replication slot lag**, дисковое место под `WAL`, перезапуск Debezium
- **Видит строки, не доменные события** — `UPDATE orders SET status=\'shipped\'` ≠ `OrderShipped`. Для бизнес-событий нужен явный outbox.

**Применение:**

- Репликация монолит → микросервис **без нагрузки** на исходный сервис
- Обновление поискового индекса (`Elasticsearch`)
- Стриминг в data warehouse (`Snowflake`, `BigQuery`)
- Audit log
- Сине-зелёные миграции БД

**Best practice:** **`Outbox + CDC`** — сервис в той же транзакции пишет **доменное событие** в outbox-таблицу, Debezium читает WAL и публикует events. Получаем domain events + транзакционную гарантию + zero dual-write.',
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
                'answer' => '**`ISR`** (In-Sync Replicas) — набор реплик партиции **(включая лидера)**, которые **догнали лидера** в пределах `replica.lag.time.max.ms` и считаются **синхронными**.

**Ключевое свойство:** **только реплика из `ISR`** может быть выбрана **новым лидером** при failover. Поэтому от размера `ISR` напрямую зависит **durability**.

**`min.insync.replicas`** — параметр на стороне топика:

- Задаёт, **сколько `ISR`** должно подтвердить запись при `acks=all`
- Если `ISR < min.insync.replicas` → продюсер получает **`NotEnoughReplicasException`**, запись **останавливается**
- Это **страховка от тихой потери данных** — лучше отказ записи, чем «успех» без репликации

**Боевая комбинация:**

| Параметр | Значение |
|---|---|
| `replication.factor` | `3` |
| `min.insync.replicas` | `2` |
| `acks` | `all` |

Эта тройка переживает падение **1 брокера** (остаются 2 ISR ≥ min=2). Падение 2 брокеров **остановит запись**, но не **потеряет** уже записанное.

**Антипаттерн:** `min.insync.replicas=1` при `acks=all` — формально acks=all, но достаточно одного лидера → при unclean leader election теряем данные.',
                'difficulty' => 4,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое High-Water Mark в Kafka и какие данные видит потребитель?',
                'answer' => '**`High-Water Mark`** (`HW`) — **offset последнего сообщения**, **подтверждённого всеми `ISR`** и считающегося **«зафиксированным»** (committed).

**Что видит consumer:**

- **Только данные до `HW`** — это и есть **механизм согласованности**
- Всё **выше `HW`** — записано на лидера, но **ещё не реплицировано** и может пропасть при failover
- Читатель **никогда не увидит** сообщение, которое потом исчезнет

**Что происходит при падении лидера:**

1. Лидер упал **до обновления `HW`**
2. Новый лидер выбирается из оставшихся `ISR`
3. Новый лидер **начинает с прежнего `HW`**
4. Часть **«лидер-only»** данных (между старым HW и log-end) **откатывается** (truncated)

**Зачем такая модель:**

- **Durability** — `HW` гарантирует, что прочитанное **точно реплицировано**
- **No phantom reads** — consumer не увидит данных, которые потом исчезнут
- **Безопасное failover** — новый лидер не теряет того, что уже видел читатель

**Связь с `LSO`:** для транзакций есть отдельный **`Last Stable Offset`** — выше него видны только сообщения из закоммиченных транзакций (при `isolation.level=read_committed`).',
                'difficulty' => 4,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как работает идемпотентный продюсер Kafka и от чего он защищает?',
                'answer' => 'При **`enable.idempotence=true`** продюсер работает с двумя метаданными:

- **`PID`** (Producer ID) — уникальный идентификатор сессии продюсера, выдаётся брокером
- **`sequence number`** — **монотонный** номер сообщения в пределах **`(PID, partition)`**

**Как работает дедупликация:**

1. Producer шлёт сообщение с `(PID=42, partition=3, seq=100)`
2. Брокер **запоминает** последний `seq` для `(PID, partition)`
3. При ретрае с тем же `seq` брокер **молча отбрасывает дубликат** и возвращает ack
4. Если приходит `seq > last+1` → **gap** → `OutOfOrderSequenceException`

**От чего защищает:**

- **Классический сценарий** — «брокер записал, ack потерялся в сети» → без идемпотентности producer повторил бы → **два одинаковых сообщения** в логе
- **Несколько in-flight requests** при `max.in.flight.requests.per.connection > 1` — порядок сохраняется

**Что обязательно идёт с `enable.idempotence=true`:**

- **`acks=all`** — иначе невозможно гарантировать порядок
- **`retries > 0`** — иначе нечего дедуплицировать
- **`max.in.flight.requests.per.connection ≤ 5`** — лимит inflight для гарантии порядка

**Ограничения:**

- Работает **в пределах одной сессии producer-а** — после рестарта новый `PID`, прежняя дедупликация недоступна
- **В пределах одной partition** — между partitions гарантий нет
- **`exactly-once` между топиками** — нужны **транзакции** (`transactional.id`)

**С Kafka 3.0:** `enable.idempotence=true` стал **дефолтом** — сообщество признало, что безопасность важнее legacy-совместимости.',
                'difficulty' => 4,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как Kafka реализует exactly-once семантику и какова её цена?',
                'answer' => '**`EOS`** (Exactly-Once Semantics) в Kafka собирается из **трёх компонентов**:

1. **Идемпотентный продюсер** (`acks=all` + `enable.idempotence=true`) — убирает дубликаты при ретраях через `PID + sequence`
2. **Транзакции** через **`transactional.id`** — атомарно записывают в **несколько партиций/топиков**:
   - `producer.beginTransaction()`
   - `producer.send(...)` в N топиков
   - `producer.commitTransaction()` или `abortTransaction()`
3. **Transactional consumer** — фиксирует **offsets внутри той же транзакции**, что и результат обработки (`sendOffsetsToTransaction()`)

**Где работает — read-process-write внутри Kafka:**

```
Topic A → consumer → process → producer → Topic B
                       ↑                     ↓
                       └──── offset commit ──┘
                          (одна транзакция)
```

**Что видит downstream:** при `isolation.level=read_committed` потребитель **не видит** сообщений из aborted-транзакций и сообщений выше `Last Stable Offset`.

**Цена:**

- **Рост латентности** из-за **двухфазного коммита** через **transaction coordinator** (специальный брокер с топиком `__transaction_state`)
- **Снижение throughput** на `10-30%` при коротких транзакциях
- **Сложность отладки** — повисшие транзакции блокируют LSO

**Главное ограничение — внешние side-effects:**

- Запись в **БД**, **HTTP**, **email** — **НЕ** часть транзакции Kafka
- Эти side-effects **обязательно** должны быть **идемпотентными** на своей стороне
- Для них работает связка **`at-least-once` + dedup** = **«effectively-once»**

**Когда брать `EOS`:** stream-processing внутри Kafka (Kafka Streams, аналитика), репликация topic→topic, аудит. **Когда не брать:** микросервисы с side-effects в БД/API — там проще идемпотентный consumer.',
                'difficulty' => 4,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое log compaction в Kafka и чем отличается от retention по времени?',
                'answer' => '**Две стратегии очистки** топика в Kafka — `cleanup.policy=delete` (по умолчанию) и `cleanup.policy=compact`.

| | **Retention** (`delete`) | **Log compaction** (`compact`) |
|---|---|---|
| **Критерий** | возраст / размер | ключ сообщения |
| **Параметры** | `log.retention.ms`, `log.retention.bytes` | `min.cleanable.dirty.ratio`, `segment.ms` |
| **Что сохраняется** | сообщения новее N | **последняя версия каждого ключа** |
| **Что удаляется** | старые целиком | старые версии того же ключа |
| **Удаление по ключу** | нет | **tombstone** (`value = null`) |
| **Use case** | event stream, логи | changelog, snapshot |

**Retention по времени/размеру** — простой механизм:

- `log.retention.ms=604800000` — хранить 7 дней
- `log.retention.bytes=1073741824` — хранить до 1 ГБ
- При превышении **старые сегменты удаляются целиком**, независимо от содержимого

**Log compaction** — **«материализованный снимок» по ключу**:

- Для каждого `key` **гарантированно** остаётся **как минимум последняя версия**
- Фоновый **`log cleaner`** периодически проходит по old segments и **схлопывает** дубликаты ключей
- **Tombstone** (`value=null`) — специальное сообщение, помечающее ключ к удалению; после `delete.retention.ms` исчезает физически

**Типичные кейсы compact:**

- **`changelog`-топики Kafka Streams** — restore состояния RocksDB после рестарта
- **Event sourcing snapshots** — текущее состояние агрегата по `aggregate_id`
- **Configuration / metadata topic** — `__consumer_offsets` использует compact

**Гибрид:** `cleanup.policy=compact,delete` — compaction **плюс** retention по времени для **`__consumer_offsets`** (compact текущие, удалять древние группы).

**Подвох:** compact **не гарантирует** удаление промежуточных версий **немедленно** — только «когда-нибудь», по параметрам `min.cleanable.dirty.ratio` и расписанию cleaner-а.',
                'difficulty' => 4,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Чем опасен unclean leader election в Kafka?',
                'answer' => '**`unclean.leader.election.enable`** — параметр на стороне топика, разрешающий повышать **отстающую реплику** (вне `ISR`) в лидеры при отказе всех ISR.

**Что происходит при `=true`:**

1. Все `ISR` упали, остаётся только **отстающая** реплика (out-of-sync)
2. Kafka **выбирает её** новым лидером ради доступности
3. **Она не знает** о части последних зафиксированных сообщений
4. Эти сообщения **теряются** навсегда
5. Consumer-ы, которые их **уже прочитали**, окажутся **«впереди»** нового лидера — **разрыв согласованности**

**Два неприятных последствия:**

- **Потеря данных** — `OrderPaid` событие исчезло после failover
- **Phantom reads у consumer-ов** — `committed_offset` указывает на несуществующее сообщение, при `seek()` получим `OFFSET_OUT_OF_RANGE`

**По умолчанию — `=false`:**

- При отказе всех `ISR` **запись останавливается**, кластер ждёт восстановления хотя бы одной ISR-реплики
- Kafka выбирает **`durability` > `availability`** (`CP` в терминах CAP)
- Лучше **отказ записи**, чем **тихая потеря**

**Когда включают `=true`:**

- **Availability > consistency** — например, **телеметрия**, **логи**, **метрики**
- Маленький кластер (`replication.factor=2`), где падение одной реплики типично
- Готовы пожертвовать данными ради того, чтобы pipeline не вставал

**Безопасный пресет для прода:**

```
unclean.leader.election.enable=false   # default
replication.factor=3
min.insync.replicas=2
acks=all
```

Этот набор переживает падение 1 брокера **без потери данных**, при падении 2 — **останавливает запись** до восстановления.',
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
                'answer' => '**`rebalance`** — **перераспределение partitions** между consumer-ами в группе при изменении её состава.

**Триггеры rebalance:**

- Добавили consumer-а в группу
- Consumer ушёл штатно (graceful)
- Consumer **не прислал heartbeat** за `session.timeout.ms` (default `45 сек`)
- Consumer **не вызвал `poll()`** за `max.poll.interval.ms` (default `5 мин`) — посчитан зависшим
- Добавили/удалили partitions в топике

**Главная боль — `stop-the-world` в классическом протоколе:**

- На время rebalance **вся группа НЕ потребляет**
- Длительность: **секунды на здоровом кластере**, **минуты при проблемах**
- При **флакающих сетях** или **долгих GC-паузах** — группа постоянно ребалансируется и **стоит**

**Сценарий «rebalance storm»:**

```
GC pause 30s → пропустили heartbeat → выкинули из группы →
rebalance → кто-то ещё пропустил → ещё rebalance → ...
```

**Лечение:**

| Решение | Что делает |
|---|---|
| **`cooperative-sticky` assignor** | **incremental rebalance** — переезжают только нужные partitions, не stop-the-world (Kafka 2.4+) |
| **`session.timeout.ms ↑`** (60-120s) | терпим более долгие GC-паузы |
| **`max.poll.interval.ms ↑`** (10-30 мин) | даём больше времени на обработку батча |
| **`max.poll.records ↓`** | меньший батч обрабатывается быстрее, не выпадаем по таймауту |
| **`group.instance.id`** | **static membership** — не выкидываем при кратковременном уходе (rolling restart, k8s reschedule) |

**Best practice:** `partition.assignment.strategy=cooperative-sticky` + `group.instance.id=<pod-name>` для k8s — большинство rolling restart-ов вообще не вызовут rebalance.',
                'difficulty' => 4,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Чем KRaft режим Kafka отличается от классической схемы с ZooKeeper?',
                'answer' => 'Исторически Kafka использовала **`ZooKeeper`** для координации, **`KRaft`** заменил его встроенным консенсусом.

| Аспект | **ZooKeeper-режим** (legacy) | **`KRaft`** (`Kafka Raft`) |
|---|---|---|
| **Метаданные** | в ZooKeeper | в **внутреннем топике** Kafka |
| **Консенсус** | ZAB (свой алгоритм) | **`Raft`** (стандартный) |
| **Координация** | внешний кластер ZK (3-5 узлов) | **встроена** в брокеров |
| **Что администрировать** | Kafka + ZooKeeper | **только Kafka** |
| **Контроллер** | один из брокеров через ZK | **выделенные controller-узлы** |
| **Восстановление** | минуты | **секунды** |
| **Лимит partitions** | сотни тысяч | **миллионы** |
| **Статус** | удалён в 4.0 | **GA с 3.3** |

**Что давало ZooKeeper:**

- Хранение метаданных (топики, ACL, конфиги)
- Выбор контроллера
- Leader election partitions
- Membership брокеров

**Проблемы ZooKeeper:**

- **Отдельный кластер** для администрирования и мониторинга
- **Bottleneck при большом числе partitions** — метаданные читаются через ZK
- **Долгое восстановление** после сбоя контроллера (пересоздание watch-ей)
- **Усложнённая эксплуатация** — два разных consensus-протокола

**Что даёт `KRaft`:**

- **Одна система** вместо двух — меньше moving parts
- **Быстрее failover** — controller-quorum уже в Kafka
- **Масштабируемость** — миллионы partitions без деградации
- **Стандартный Raft** — лучше документирован, меньше surprises

**Roadmap:**

- **Kafka 2.8** (2021) — KRaft preview
- **Kafka 3.3** (2022) — **KRaft GA**
- **Kafka 3.5+** — рекомендуют миграцию
- **Kafka 4.0** — **ZooKeeper полностью удалён**

**Для новых кластеров** — выбор очевидный: **только `KRaft`**.',
                'difficulty' => 4,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Kafka Streams и зачем нужны changelog-топики?',
                'answer' => '**`Kafka Streams`** — **библиотека** для stream-processing **поверх Kafka**, без отдельного кластера обработки.

**Главное отличие от Flink/Spark Streaming:**

- **Нет отдельного cluster manager** — Kafka Streams = просто JVM-приложение
- **Масштабируется через consumer group** — больше инстансов = больше партиций обрабатывается параллельно
- **Stateful operations локальны** — состояние в RocksDB на диске инстанса

**Что умеет:**

- **Stateless** — `map`, `filter`, `flatMap`
- **Stateful** — `count`, `aggregate`, `reduce`
- **Windowed** — tumbling/hopping/session windows
- **Joins** — stream-stream, stream-table, table-table
- **Materialized state** — `KTable` = view над топиком

**Состояние и проблема его потери:**

- Каждый инстанс держит **локальный state store** в **`RocksDB`**
- При **падении** или **rebalance** партиция уезжает к другому инстансу
- Если состояние **только локальное** — теряется или нужно **пересчитать с нуля** (минуты-часы)

**Решение — changelog-топики:**

1. Каждое изменение state store **параллельно пишется** в специальный **`compacted` топик** `<app-id>-<store-name>-changelog`
2. При перезапуске или rebalance инстанс **восстанавливает RocksDB**, **проигрывая changelog**
3. Топик `compact` → хранит **только последнее значение** на ключ → размер ограничен

**Почему compaction обязательна:**

- Без неё changelog рос бы бесконечно → restore занимал часы
- С compaction restore = размер актуального state, **не вся история**

**Если changelog-топик удалить — всё локальное состояние пересчитывается с нуля** через `application.reset` и replay входных топиков. На больших stateful-приложениях это часы downtime.

**Best practice:** мониторить **`records-lag-max`** для changelog-топика — отстающий changelog = долгий restore при failover.',
                'difficulty' => 4,
                'topic' => 'system_design.messaging_queues',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как реализовать обратное давление в Kafka-потребителе на PHP?',
                'answer' => 'У Kafka **pull-модель** — потребитель сам опрашивает брокера через `poll`/`consume`. Поэтому **backpressure встроен**: при перегрузке достаточно **замедлить вызовы**.

**Главный механизм — `pause()` / `resume()` на partition:**

- Пока внутренняя очередь обработчика **заполнена** — вызываем `pause(partitions)`
- При освобождении буфера — `resume(partitions)`
- На `pause` poll **продолжает работать** (нужен для heartbeat!), но **не выдаёт сообщений** с этих partitions

**Что тюнить параллельно:**

| Параметр | Зачем |
|---|---|
| **`max.poll.records`** | сколько сообщений за один `poll()`; меньший батч = быстрее обработка, меньше шанс таймаута |
| **`fetch.max.bytes`** | объём данных за `poll()` в байтах |
| **`max.poll.interval.ms`** | **сколько максимум** может идти обработка между poll-ами (default `5 мин`) |
| **`max.partition.fetch.bytes`** | лимит на partition |

**Главный подвох — `max.poll.interval.ms`:**

- Если обработка **батча длится дольше**, чем `max.poll.interval.ms` → consumer **посчитан зависшим**
- Rebalance заберёт partitions, при следующем commit будет `CommitFailedException`
- **Лечение**: уменьшить `max.poll.records` или увеличить `max.poll.interval.ms`

**`heartbeat` vs `poll`:**

- **`heartbeat.interval.ms`** (default `3s`) — фоновый поток шлёт heartbeat, **не зависит** от обработки
- **`session.timeout.ms`** (default `45s`) — если heartbeat пропал → выкидывают из группы
- **`max.poll.interval.ms`** — отдельная проверка «poll вообще вызывается?»

**Best practice для PHP-консьюмера:** работать с **маленьким батчем** (`max.poll.records=10-100`), обрабатывать **до конца** перед следующим `poll()`, **не делать тяжёлую обработку в фоне** — иначе сложно отслеживать backpressure.',
                'code_example' => '<?php
// php-rdkafka: backpressure через pause/resume
$conf = new RdKafka\\Conf();
$conf->set("group.id", "order-processor");
$conf->set("enable.auto.commit", "false");
$conf->set("max.poll.interval.ms", "600000"); // 10 мин на батч
$conf->set("max.poll.records", "50");
$conf->set("partition.assignment.strategy", "cooperative-sticky");

$consumer = new RdKafka\\KafkaConsumer($conf);
$consumer->subscribe(["orders"]);

$buffer = [];
$maxBuffer = 1000;

while (true) {
    $message = $consumer->consume(1000);
    if ($message->err !== RD_KAFKA_RESP_ERR_NO_ERROR) continue;

    $buffer[] = $message;

    // Backpressure: буфер забит → pause
    if (count($buffer) >= $maxBuffer) {
        $consumer->pause($consumer->getAssignment());
        processBatch($buffer);
        $buffer = [];
        $consumer->resume($consumer->getAssignment());
    }

    // Коммитим offset только после обработки
    $consumer->commit($message);
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'system_design.messaging_queues',
            ],
        ];
    }
}
