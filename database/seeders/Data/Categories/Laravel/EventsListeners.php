<?php

namespace Database\Seeders\Data\Categories\Laravel;

class EventsListeners
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Laravel',
                'question' => 'Что такое Events и Listeners?',
                'answer' => '**Event** — обычный PHP-класс, описывающий «что-то произошло»: `UserRegistered`, `OrderPaid`, `MessageSent`. В конструкторе хранятся данные события (модель, payload).

**Listener** — класс с методом `handle($event)`, реагирующий на событие.

**Ключевая идея — развязка кода:**

- Код, бросающий событие, **не знает**, кто на него отреагирует.
- На одно событие — **несколько listeners**, каждый получит объект события в `handle()`.
- Добавить новую реакцию (SMS, бонусы, Slack) = создать новый listener, **не трогая код-источник**.

**Как бросить событие:**

- **`UserRegistered::dispatch($user)`** — статический метод от трейта `Illuminate\\Foundation\\Events\\Dispatchable` (добавляется в `make:event`).
- **`event(new UserRegistered($user))`** — без трейта.

**Регистрация в Laravel 11+:**

- **Auto-discovery** — Laravel находит listener по type-hint аргумента `handle()`. Никаких `$listen`-массивов **не нужно**.
- **Явно** — `Event::listen(UserRegistered::class, SendWelcomeEmail::class)` в `AppServiceProvider::boot()`.

**Async через очередь:** listener реализует `ShouldQueue` → выполняется в воркере, контроллер не ждёт SMTP/HTTP-вызовы.',
                'code_example' => 'use Illuminate\\Foundation\\Events\\Dispatchable;

class UserRegistered {
    use Dispatchable; // даёт ::dispatch() и ::dispatchIf()
    public function __construct(public User $user) {}
}

class SendWelcomeEmail implements ShouldQueue {
    public function handle(UserRegistered $event): void {
        Mail::to($event->user)->send(new WelcomeMail());
    }
}

UserRegistered::dispatch($user);
// либо без трейта
event(new UserRegistered($user));',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.events_listeners',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Broadcasting в Laravel?',
                'answer' => '**Broadcasting** — передача серверных событий **на клиента в реальном времени** через WebSocket-протокол. Превращает обычный Laravel-event в push-сообщение для браузеров.

**Поток:**

1. На сервере: `event(new MessageSent($msg))` с `implements ShouldBroadcast`.
2. Laravel сериализует event в JSON и отправляет в **broadcaster** (Reverb / Pusher / Ably).
3. Broadcaster через WebSocket доставляет сообщение **всем клиентам**, подписанным на канал.
4. Клиент (Laravel Echo) ловит событие и обновляет UI.

**Три типа каналов:**

| Канал | Кто может подписаться | Особенности |
|---|---|---|
| **Public** | Любой | Без авторизации, любой WS-клиент |
| **Private** | Залогиненные + callback в `routes/channels.php` вернул `true` | Авторизация через `/broadcasting/auth` |
| **Presence** | Как private + callback вернул **массив user-data** | Знает «кто онлайн», события `joining/leaving/here` |

**Драйверы (broadcasters):**

| Драйвер | Тип | Когда |
|---|---|---|
| **`reverb`** | Self-hosted PHP (L11+) | Дефолт в L11, бесплатно, один стек |
| **`pusher`** | Managed SaaS | Платно, без operations |
| **`ably`** | Managed SaaS | Альтернатива Pusher |
| **`redis`** | **Только pub/sub транспорт** | Нужен внешний WS-сервер (Soketi, Echo Server) |
| **`log` / `null`** | Для тестов / dev | Не доставляет |

**Установка в L11:**

- **`php artisan install:broadcasting`** — ставит Reverb + создаёт `routes/channels.php` + настраивает `.env` + публикует JS-bootstrap.

**Клиент:** **Laravel Echo** (`laravel-echo` npm) — обёртка над `pusher-js` / `socket.io-client`.',
                'code_example' => '<?php
// 1) Event с интерфейсом ShouldBroadcast
class MessageSent implements ShouldBroadcast
{
    public function __construct(public Message \$message) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("chat.{\$this->message->room_id}");
    }

    public function broadcastAs(): string
    {
        return "message.sent";  // имя для клиента
    }
}

// 2) Авторизация канала - routes/channels.php
Broadcast::channel("chat.{roomId}", function (User \$user, int \$roomId) {
    return \$user->rooms()->whereKey(\$roomId)->exists();
});

// 3) JS клиент - resources/js/bootstrap.js
import Echo from "laravel-echo";
import Pusher from "pusher-js";
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "reverb",
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
});

// 4) Подписка в компоненте
window.Echo.private(`chat.\${roomId}`)
    .listen(".message.sent", (e) => {     // точка перед именем = broadcastAs
        appendMessage(e);
    });',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.events_listeners',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое broadcasting и в чём разница private и presence-каналов?',
                'answer' => '**Broadcasting** публикует серверные события клиенту через драйверы (`Pusher`, `Reverb`, `Soketi`). Три типа каналов отличаются **авторизацией** и **возможностями**.

**Сравнение трёх каналов:**

| | **Public** | **Private** | **Presence** |
|---|---|---|---|
| Авторизация | Не нужна | `Auth::user()` + callback `→ bool` | `Auth::user()` + callback **`→ array`** |
| JS API | `Echo.channel(name)` | `Echo.private(name)` | `Echo.join(name)` |
| Знает участников | Нет | Нет | **Да** |
| События | `listen` | `listen` | `listen` + `here` + `joining` + `leaving` |
| Auth-эндпоинт | — | `/broadcasting/auth` | `/broadcasting/auth` |
| Typical use | Новости, ленты | Личные уведомления, чат one-to-one | Online-статус, совместное редактирование |

**Авторизация private — callback возвращает `bool`:**

```php
Broadcast::channel("orders.{userId}", fn (User $u, $userId) =>
    (int) $u->id === (int) $userId
);
```

**Авторизация presence — callback возвращает `array` с user-data (или `false`):**

```php
Broadcast::channel("room.{roomId}", function (User $u, int $roomId) {
    if (! $u->canJoin($roomId)) {
        return false;
    }
    return ["id" => $u->id, "name" => $u->name, "avatar" => $u->avatar_url];
});
```

**Presence-API на клиенте:**

| Метод | Что даёт |
|---|---|
| **`.here(callback)`** | Текущий список участников при подключении |
| **`.joining(callback)`** | Кто-то **вошёл** в канал |
| **`.leaving(callback)`** | Кто-то **вышел** |
| **`.listen("EventName", callback)`** | Обычные события |
| **`.whisper("typing", data)`** / **`.listenForWhisper("typing", ...)`** | Client-to-client события (без сервера) |

**Когда что брать:**

- **Public** — новости сайта, лента активности (всем видно).
- **Private** — личный inbox юзера, его заказы, его уведомления.
- **Presence** — чат-комната с «кто онлайн», collaborative editing с курсорами, multiplayer-фичи.',
                'code_example' => '<?php
use Illuminate\\Broadcasting\\PrivateChannel;
use Illuminate\\Broadcasting\\PresenceChannel;

// PRIVATE - событие для одного юзера
class OrderShipped implements ShouldBroadcast
{
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("orders.{\$this->order->user_id}");
    }
}

Broadcast::channel("orders.{userId}", fn (User \$u, \$userId) =>
    (int) \$u->id === (int) \$userId
);

// PRESENCE - кто в комнате
class MessageSent implements ShouldBroadcast
{
    public function broadcastOn(): PresenceChannel
    {
        return new PresenceChannel("room.{\$this->message->room_id}");
    }
}

Broadcast::channel("room.{roomId}", function (User \$user, int \$roomId) {
    if (! \$user->canEnter(\$roomId)) {
        return false;
    }
    return [
        "id"     => \$user->id,
        "name"   => \$user->name,
        "avatar" => \$user->avatar_url,
    ];
});

// Клиент - присоединиться к presence
Echo.join(`room.\${roomId}`)
    .here((users) => setOnline(users))
    .joining((user) => addOnline(user))
    .leaving((user) => removeOnline(user))
    .listen("MessageSent", (e) => appendMessage(e));',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.events_listeners',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Чем listener с интерфейсом ShouldQueue отличается от обычного слушателя события?',
                'answer' => '**Обычный listener** выполняется **синхронно** в том же процессе, что и `dispatch` события — **блокирует ответ** на HTTP-запрос. Если в `handle()` есть `Mail::send` (SMTP 1-2 сек) или HTTP к внешнему API — пользователь ждёт.

**Listener с `ShouldQueue`** сериализуется (вместе с event-объектом) и отправляется в очередь, обрабатывается воркером `queue:work`. Контроллер возвращает ответ мгновенно.

**Сравнение:**

| Свойство | Обычный | `ShouldQueue` |
|---|---|---|
| Где выполняется | В web-процессе | В воркере `queue:work` |
| Блокирует ответ | Да | Нет |
| Доступны Job-механизмы | Нет | `$tries`, `$backoff`, `failed()`, `middleware`, `$afterCommit` |
| Failover при сбое | Уронит запрос | Уходит в `failed_jobs`, можно `queue:retry` |

**Бонус — на listener распространяются все Job-механизмы:**

- `public int $tries = 5` / `$backoff = [10, 30, 120]`.
- `public function failed(UserRegistered $event, \\Throwable $e)`.
- `public bool $afterCommit = true` — ждать commit транзакции.
- `public function shouldQueue(UserRegistered $event): bool` — условный skip.

**Подводный камень — сериализуемость:**

- Event-объект должен быть **serializable**: никаких `Closure`, `PDO`, file handles.
- Если в event Eloquent-модель → трейт `SerializesModels` сохраняет **только ID**, при handle модель **re-fetch-ится из БД**. Если её удалили между dispatch и handle — `ModelNotFoundException`.

**Связанные интерфейсы:** `ShouldBroadcastNow` / `ShouldBroadcast` — для событий, передаваемых клиенту через WebSocket.',
                'code_example' => '<?php
// Синхронно - блокирует ответ
class SendWelcomeEmail
{
    public function handle(UserRegistered $event): void
    {
        Mail::to($event->user)->send(new WelcomeMail()); // 1-2 сек SMTP
    }
}

// Асинхронно - dispatch вернётся мгновенно
class SendWelcomeEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;
    public int $backoff = 60;          // сек между попытками
    public string $queue = "emails";
    public bool $afterCommit = true;   // ждать commit транзакции

    public function handle(UserRegistered $event): void
    {
        Mail::to($event->user)->send(new WelcomeMail());
    }

    public function failed(UserRegistered $event, \\Throwable $e): void
    {
        Log::error("Welcome mail failed", [
            "user_id" => $event->user->id,
            "error"   => $e->getMessage(),
        ]);
    }

    // Условный skip - например при maintenance
    public function shouldQueue(UserRegistered $event): bool
    {
        return ! app()->isDownForMaintenance();
    }
}

// Часть слушателей синхронны, часть в очереди - управляется на каждом отдельно
event(new UserRegistered($user));',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.events_listeners',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Reverb и чем он отличается от Pusher и Soketi?',
                'answer' => '**Laravel Reverb** (`laravel/reverb`) — официальный высокопроизводительный WebSocket-сервер от Laravel, появившийся в **Laravel 11**.

- Написан на чистом PHP поверх **ReactPHP** (event loop).
- **Совместим с Pusher-протоколом** — Laravel Echo и любые Pusher-клиенты работают **без изменений в коде**.
- Ставится одной командой: `php artisan install:broadcasting`.

**Сравнение трёх вариантов:**

| Параметр | **Reverb** | **Pusher** | **Soketi** |
|---|---|---|---|
| Тип | Self-hosted PHP | Managed SaaS | Self-hosted Node.js |
| Цена | Бесплатно | Платно (connections + messages) | Бесплатно |
| Стек | PHP + ReactPHP | — | Node.js |
| Поддержка | Laravel core team | Pusher Inc | Open-source community |
| Pusher-протокол | Совместим | Источник | Совместим |
| Подходит | Self-hosted прод, single-stack | Managed без операций | Self-hosted, Node-команды |

**Преимущества Reverb:**

- **Один стек** (PHP, как и приложение) — не нужно держать Node-демона.
- **Официальная поддержка** Laravel-команды.
- **Бесплатно**.
- Производительность сопоставима с Soketi.

**Минусы:**

- Новый продукт — экосистема плагинов меньше.
- Pusher оставляют, если нужен **managed**-сервис без обслуживания инфраструктуры.',
                'code_example' => '# Установка
php artisan install:broadcasting    # выбираешь reverb
# или вручную:
composer require laravel/reverb
php artisan reverb:install

# .env - Reverb
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=app-id
REVERB_APP_KEY=app-key
REVERB_APP_SECRET=app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

# Vite
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"

# Запуск сервера
php artisan reverb:start --debug
# В проде - под supervisor

# resources/js/bootstrap.js - клиент тот же что для Pusher
import Echo from "laravel-echo";
import Pusher from "pusher-js";
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "reverb",
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: false,
    enabledTransports: ["ws", "wss"],
});',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'laravel.events_listeners',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Echo и как он связан с broadcasting?',
                'answer' => '**Laravel Echo** (`laravel-echo`, npm-пакет) — JS-клиент, который подписывается на broadcasting-каналы и слушает события, транслируемые сервером через **Pusher/Reverb/Ably**.

Сам протокол **не реализует** — это надстройка над `pusher-js` (или `socket.io-client`). Прячет работу с именами каналов, авторизацией, парсингом payload в красивый API.

**Серверная сторона события:**

- **`implements ShouldBroadcast`** — Laravel отправит событие в broadcasting-драйвер.
- **`broadcastOn()`** возвращает `Channel` / `PrivateChannel` / `PresenceChannel`.
- **`broadcastAs()`** — кастомное имя события для клиента (иначе FQCN).
- **`broadcastWith()`** — payload (если нужно отдать не все public-свойства).

**Три типа каналов:**

| Канал | Использование | Аутентификация |
|---|---|---|
| **Public** | `Echo.channel(\'news\')` | Не нужна |
| **Private** | `Echo.private(\'orders.42\')` | `/broadcasting/auth` + правило в `routes/channels.php` |
| **Presence** | `Echo.join(\'room.5\')` | Как private + получает `here`/`joining`/`leaving` |

**Авторизация private/presence:**

```php
// routes/channels.php
Broadcast::channel(\'orders.{userId}\', fn (User $u, $userId) =>
    $u->id === (int) $userId
);
```

**Подводные камни:**

- Имя custom-события из `broadcastAs()` слушают с **точкой**: `.listen(\'.message.sent\', ...)`.
- Для presence-канала callback в `Broadcast::channel` должен **вернуть массив с данными** юзера, а не bool.
- Без `install:broadcasting` нет `routes/channels.php` — Laravel 11 не создаёт его по умолчанию.',
                'code_example' => '<?php
// Server - событие
class MessageSent implements ShouldBroadcast
{
    public function __construct(public Message $message) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("chat.{$this->message->room_id}");
    }

    public function broadcastAs(): string
    {
        return "message.sent";  // имя для клиента
    }

    public function broadcastWith(): array
    {
        return [
            "id"      => $this->message->id,
            "text"    => $this->message->text,
            "author"  => $this->message->author->name,
        ];
    }
}

// routes/channels.php - авторизация
Broadcast::channel("chat.{roomId}", function (User $user, int $roomId) {
    return $user->rooms()->whereKey($roomId)->exists();
});

# Client (Vue/React/vanilla JS)
window.Echo.private(`chat.${roomId}`)
    .listen(".message.sent", (e) => {     // точка перед именем - кастомный broadcastAs
        console.log(e.author, e.text);
        appendMessage(e);
    });

# Presence channel - кто онлайн в комнате
window.Echo.join(`room.${roomId}`)
    .here((users) => setOnline(users))
    .joining((user) => appendOnline(user))
    .leaving((user) => removeOnline(user));
?>',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.events_listeners',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое событие (event) в Laravel простыми словами?',
                'answer' => '**Событие (event)** — обычный PHP-класс, описывающий «что-то произошло»: `UserRegistered`, `OrderPaid`, `MessageSent`.

- Создаётся через `php artisan make:event UserRegistered` — добавляется трейт `Dispatchable`.
- В конструкторе хранятся данные (обычно модель): `public User $user`.
- Бросается так: `UserRegistered::dispatch($user)` или `event(new UserRegistered($user))`.

**Главная идея — развязка кода:**

- Код, бросающий событие, **не знает**, кто на него отреагирует.
- На одно событие могут быть подписаны **несколько слушателей** (listeners), каждый получит объект события в `handle()`.
- Добавить новую реакцию (отправить SMS, начислить бонусы, оповестить Slack) = добавить новый listener, **не трогая** код регистрации.',
                'code_example' => 'use Illuminate\Foundation\Events\Dispatchable;

class UserRegistered
{
    use Dispatchable;

    public function __construct(public User $user) {}
}

// Где-то в RegisterController после создания юзера
UserRegistered::dispatch($user);
// эквивалент: event(new UserRegistered($user));',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'laravel.events_listeners',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое listener в Laravel?',
                'answer' => '**Listener** — класс с методом `handle($event)`, реагирующий на событие.

Ключевые свойства:

- На **один event** можно повесить **несколько listeners** — все вызовутся при `dispatch`.
- Если listener реализует **`ShouldQueue`** — он выполнится **асинхронно** в очереди (контроллер не ждёт).
- Создаётся через `php artisan make:listener SendWelcomeEmail --event=UserRegistered`.

Регистрация:

- **Laravel 11+** — **auto-discovery**: Laravel сам находит listener по type-hint аргумента `handle()`. Отдельный массив `$listen` **не нужен**.
- **Laravel 10 и старше** — массив `$listen` в `app/Providers/EventServiceProvider.php`.
- **Везде** — явная регистрация `Event::listen(...)` в `AppServiceProvider::boot()` работает.',
                'code_example' => 'php artisan make:listener SendWelcomeEmail --event=UserRegistered

class SendWelcomeEmail implements ShouldQueue
{
    // Laravel 11+ найдёт листенер по type-hint UserRegistered
    public function handle(UserRegistered $event): void
    {
        Mail::to($event->user)->send(new WelcomeMail());
    }
}

// Альтернатива: явная регистрация в AppServiceProvider::boot()
Event::listen(UserRegistered::class, SendWelcomeEmail::class);

// Диспатч события
event(new UserRegistered($user));
UserRegistered::dispatch($user); // если есть Dispatchable',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.events_listeners',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Зачем нужны события в Laravel простыми словами?',
                'answer' => 'Чтобы **развязать код**.

Без событий контроллер регистрации сам делает кучу всего:

- Отправить welcome-email.
- Начислить бонусные баллы.
- Оповестить Slack.
- Создать запись в CRM.

С событиями контроллер просто бросает **`UserRegistered::dispatch($user)`** — а listeners сами разберутся. Каждая реакция — отдельный класс.

Что это даёт:

- **Открытость к расширению** — добавить SMS-уведомление = создать новый listener. Контроллер не трогаем.
- **Тестируемость** — каждый listener тестируется отдельно. В тестах есть `Event::fake()` чтобы проверить факт диспатча.
- **Асинхронность** — повесил `ShouldQueue`, ответ юзеру не тормозит.',
                'code_example' => '// Без событий - контроллер знает обо всём
public function register(Request $request) {
    $user = User::create($request->all());
    Mail::to($user)->send(new WelcomeMail());
    BonusService::grantSignupBonus($user);
    Slack::notify("New user: {$user->email}");
    Crm::createContact($user);
    return redirect()->route(\'home\');
}

// С событиями - одна строка, остальное в listeners
public function register(Request $request) {
    $user = User::create($request->all());
    UserRegistered::dispatch($user);
    return redirect()->route(\'home\');
}

// В тестах
Event::fake();
$this->post(\'/register\', [...]);
Event::assertDispatched(UserRegistered::class);',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.events_listeners',
            ],
        ];
    }
}
