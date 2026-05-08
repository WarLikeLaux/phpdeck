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
                'answer' => 'Event - это объект, описывающий "что-то произошло" (UserRegistered, OrderPaid). Listener - класс, реагирующий на событие. Простыми словами: один событие может иметь много слушателей, что позволяет отделять логику. Слушатель может реализовать ShouldQueue для асинхронной обработки.',
                'code_example' => 'class UserRegistered {
    public function __construct(public User $user) {}
}

class SendWelcomeEmail implements ShouldQueue {
    public function handle(UserRegistered $event): void {
        Mail::to($event->user)->send(new WelcomeMail());
    }
}

UserRegistered::dispatch($user);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.events_listeners',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Broadcasting в Laravel?',
                'answer' => 'Broadcasting - это передача событий с сервера на клиента в реальном времени через WebSockets. Каналы: public (любой), private (требует авторизации), presence (с информацией о подключённых пользователях). Драйверы: Pusher, Ably, Reverb (свой WebSocket сервер от Laravel), Redis. Клиент использует Laravel Echo.',
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
                'answer' => 'Broadcasting публикует серверные события клиенту через драйверы (Pusher, Reverb, Soketi). Public - открыт всем. Private - требует Auth::user() и колбэк в Broadcast::channel("orders.{user}", fn($u, $userId) => $u->id === $userId), который проверяет доступ. Presence - расширение private, ещё возвращает массив с данными присутствующих пользователей; используется для онлайн-статуса и совместного редактирования.',
                'code_example' => '<?php
Broadcast::channel("orders.{userId}", fn($u, $userId) => (int)$u->id === (int)$userId);

class OrderShipped implements ShouldBroadcast {
    public function broadcastOn(): PrivateChannel {
        return new PrivateChannel("orders.{$this->order->user_id}");
    }
}',
                'code_language' => 'php',
                'difficulty' => 5,
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
                'difficulty' => 2,
                'topic' => 'laravel.events_listeners',
            ],
        ];
    }
}
