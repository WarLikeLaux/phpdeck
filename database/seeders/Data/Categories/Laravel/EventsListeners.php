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
                'answer' => 'Обычный listener выполняется синхронно в том же процессе, что и dispatch события, и блокирует ответ на запрос. Listener, реализующий ShouldQueue, сериализуется и отправляется в очередь, обрабатываясь воркером отдельно; его методы failed/retryUntil/backoff работают как у Job. Это критично, если внутри тяжёлая логика типа отправки писем или обращения к внешним API.',
                'difficulty' => 3,
                'topic' => 'laravel.events_listeners',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Reverb и чем он отличается от Pusher и Soketi?',
                'answer' => 'Reverb — официальный высокопроизводительный WebSocket-сервер на ReactPHP, появившийся в Laravel 11. Совместим с протоколом Pusher, поэтому Laravel Echo и существующие клиенты работают без изменений. Pusher — внешний платный сервис, Soketi — open-source альтернатива на Node.js; Reverb решает ту же задачу, но в виде официально поддерживаемого PHP-сервера, который ставится через php artisan install:broadcasting.',
                'difficulty' => 3,
                'topic' => 'laravel.events_listeners',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Echo и как он связан с broadcasting?',
                'answer' => 'Echo — это JavaScript-клиент, который подписывается на каналы broadcasting и слушает события, которые сервер транслирует через Pusher/Reverb/Ably. Сам по себе Echo не реализует протокол: он надстройка над pusher-js или socket.io-client. На сервере событие реализует ShouldBroadcast, на клиенте Echo.private("channel").listen("EventName", ...) ловит его.',
                'difficulty' => 3,
                'topic' => 'laravel.events_listeners',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое событие (event) в Laravel простыми словами?',
                'answer' => 'Объект, описывающий «что-то произошло»: UserRegistered, OrderPaid. Код, инициирующий событие, не знает, кто на него отреагирует — он просто бросает event(new UserRegistered($user)). На событие могут быть подписаны несколько слушателей.',
                'difficulty' => 1,
                'topic' => 'laravel.events_listeners',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое listener в Laravel?',
                'answer' => 'Класс с методом handle($event), который реагирует на событие: получает объект события и что-то делает. На один event можно повесить несколько listeners. В Laravel 11+ Laravel сам находит подписки по type-hint в handle() (event discovery) — отдельный $listen массив больше не нужен. В Laravel 10 и старше регистрация шла в app/Providers/EventServiceProvider.php в свойстве $listen. Также всегда работает явная Event::listen(SomeEvent::class, SomeListener::class) в AppServiceProvider::boot().',
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
