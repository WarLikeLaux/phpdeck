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
                'answer' => 'Event - это объект, описывающий "что-то произошло" (UserRegistered, OrderPaid). Listener - класс, реагирующий на событие. Простыми словами: одно событие может иметь много слушателей, что позволяет отделять логику. Слушатель может реализовать ShouldQueue для асинхронной обработки. Статический метод dispatch() даёт трейт Illuminate\\Foundation\\Events\\Dispatchable - он автоматически добавляется при php artisan make:event. Без трейта нужно использовать event(new Foo($x)).',
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
                'answer' => 'Broadcasting - это передача событий с сервера на клиента в реальном времени через WebSockets. Каналы: public (любой), private (требует авторизации), presence (с информацией о подключённых пользователях). Драйверы: Pusher, Ably, Reverb (свой WebSocket сервер от Laravel), Redis (pub/sub-транспорт - сам по себе WebSocket-клиентов не обслуживает, нужен внешний WS-сервер: Echo Server, Soketi). Клиент использует Laravel Echo.',
                'code_example' => 'class MessageSent implements ShouldBroadcast {
    public function broadcastOn(): PrivateChannel {
        return new PrivateChannel(\'chat.\' . $this->message->room_id);
    }
}

// JS клиент
Echo.private(`chat.${roomId}`)
    .listen(\'MessageSent\', (e) => console.log(e));',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.events_listeners',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое broadcasting и в чём разница private и presence-каналов?',
                'answer' => 'Broadcasting публикует серверные события клиенту через драйверы (Pusher, Reverb, Soketi). Public - открыт всем. Private - требует Auth::user() и колбэк в Broadcast::channel("orders.{userId}", fn($u, $userId) => $u->id === $userId), который проверяет доступ. Presence - расширение private, ещё возвращает массив с данными присутствующих пользователей; используется для онлайн-статуса и совместного редактирования.',
                'code_example' => '<?php
Broadcast::channel("orders.{userId}", fn($u, $userId) => (int)$u->id === (int)$userId);

class OrderShipped implements ShouldBroadcast {
    public function broadcastOn(): PrivateChannel {
        return new PrivateChannel("orders.{$this->order->user_id}");
    }
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.events_listeners',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Чем listener с интерфейсом ShouldQueue отличается от обычного слушателя события?',
                'answer' => 'Обычный listener выполняется СИНХРОННО в том же процессе, что и dispatch события - блокирует ответ на HTTP-запрос. Если в handle() есть Mail::send (SMTP-вызов 1-2 сек) или HTTP-вызов внешнего API - пользователь ждёт. Listener, реализующий ShouldQueue, сериализуется (вместе с event-объектом) и отправляется в очередь, обрабатывается отдельным воркером queue:work. Контроллер возвращает ответ мгновенно, тяжёлая работа происходит асинхронно. Бонус: на listener распространяются все механизмы Job-а - public int $tries, public function backoff(), public function failed(\\Throwable $e), public function viaConnection(), retryUntil(), middleware(), $afterCommit. ShouldBroadcastNow / ShouldBroadcast - то же самое для broadcast-событий: оба отправляются в очередь, если есть ShouldQueue. Подводный камень: event-объект должен быть serializable (нет Closure, PDO, file handles); если в event Eloquent-модель - сериализуется только её ID, при handle модель re-fetch-ится из БД (SerializesModels трейт).',
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
                'answer' => 'Reverb (laravel/reverb) - официальный высокопроизводительный WebSocket-сервер от Laravel, появившийся в Laravel 11. Написан на чистом PHP поверх ReactPHP (event loop), совместим с протоколом Pusher - то есть Laravel Echo и любые существующие Pusher-клиенты работают БЕЗ изменений в коде. Pusher - внешний платный SaaS-сервис (платишь за connections и messages), Soketi - open-source альтернатива на Node.js, который тоже совместим с Pusher-протоколом. Reverb решает ту же задачу, но в виде официально поддерживаемого PHP-сервера, который ставится одной командой php artisan install:broadcasting. Преимущества Reverb: 1) Один стек (PHP, как и приложение), не нужно держать Node-демона. 2) Официальная поддержка Laravel-команды. 3) Бесплатно. 4) Производительность сопоставима с Soketi. Минус: новый продукт, экосистема плагинов меньше. Pusher оставляют, если нужен managed-сервис без обслуживания.',
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
                'answer' => 'Echo (laravel-echo, npm-пакет) - JavaScript-клиент, который подписывается на каналы broadcasting и слушает события, транслируемые сервером через Pusher/Reverb/Ably. Сам по себе протокол не реализует - это надстройка над pusher-js (или socket.io-client). На сервере событие реализует ShouldBroadcast, broadcastOn() возвращает Channel/PrivateChannel/PresenceChannel, broadcastAs() задаёт имя события для клиента, broadcastWith() - payload. На клиенте Echo.channel("chat")  .listen("MessageSent", e => ...) - публичный, .private("orders.42").listen(...) - с авторизацией через /broadcasting/auth, .join("room.5") - presence (получаешь here/joining/leaving события). Авторизация private/presence-каналов идёт в routes/channels.php через Broadcast::channel("orders.{userId}", fn(User $u, $userId) => $u->id === (int)$userId). Без Echo пришлось бы вручную работать с pusher-js, форматировать имена каналов, разбирать сообщения - Echo прячет это в красивый API.',
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
                'answer' => 'Объект, описывающий «что-то произошло»: UserRegistered, OrderPaid, MessageSent. Это обычный PHP-класс, обычно создаётся через php artisan make:event с трейтом Dispatchable. Код, инициирующий событие, не знает, кто на него отреагирует — он просто бросает event(new UserRegistered($user)) или UserRegistered::dispatch($user). На событие могут быть подписаны несколько слушателей (listeners), каждый из них получит объект события в handle(). Это даёт развязку: добавить новую реакцию (отправить SMS) = просто добавить новый listener, не трогая код регистрации.',
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
                'answer' => 'Класс с методом handle($event), реагирующий на событие. На один event можно повесить несколько listeners — все вызовутся при dispatch. Если listener реализует ShouldQueue — он выполнится асинхронно в очереди. В Laravel 11+ работает event auto-discovery: Laravel сам находит listener по type-hint аргумента handle() (а также методов вида handleX), отдельный массив $listen не нужен. В Laravel 10 и старше регистрация шла через свойство $listen в app/Providers/EventServiceProvider.php. Явная регистрация Event::listen() в AppServiceProvider::boot() работает всегда.',
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
                'answer' => 'Чтобы развязать код. Контроллер регистрации не должен знать про отправку email, начисление бонусов, оповещение в Slack — он просто бросает событие UserRegistered, а listeners сами разберутся. Это упрощает добавление новой реакции — просто добавь нового listener.',
                'difficulty' => 2,
                'topic' => 'laravel.events_listeners',
            ],
        ];
    }
}
