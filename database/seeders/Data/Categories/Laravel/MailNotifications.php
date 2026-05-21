<?php

namespace Database\Seeders\Data\Categories\Laravel;

class MailNotifications
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Laravel',
                'question' => 'Что такое Notifications в Laravel?',
                'answer' => '**Notifications** — унифицированный способ отправлять уведомления через **разные каналы** одним классом. **Один класс уведомления**, метод **`via()`** выбирает каналы для конкретного получателя.

**Встроенные каналы:**

| Канал | Метод формирования | Для чего |
|---|---|---|
| **`mail`** | `toMail($n)` → `MailMessage` | письмо |
| **`database`** | `toArray($n)` | запись в таблицу `notifications` (in-app список) |
| **`broadcast`** | `toBroadcast($n)` / `toArray($n)` | WebSocket (Reverb, Pusher) |
| **`slack`** | `toSlack($n)` | Slack-сообщение |
| **`vonage`** (бывш. `nexmo`) | `toVonage($n)` | SMS |
| **`mail` (markdown)** | `view+markdown` | оформление через Blade-markdown |

Канал может быть **кастомным** — обычный класс с методом `send(object $notifiable, Notification $notification)`.

**Что даёт:**
- **Унификация** — одно событие («счёт оплачен») → разные доставки **разным юзерам** (юзер только в email, админ — в Slack).
- Различные **формат-методы** в одном файле — никаких параллельных классов «MailInvoicePaid», «SlackInvoicePaid».
- **`ShouldQueue`** на классе → отправка идёт через **очередь**, не блокирует HTTP-запрос.

**Получатели:**
- Любая модель с trait **`Notifiable`** (по умолчанию у `User`): `$user->notify($notification)`.
- **`Notification::send($users, ...)`** — массовая отправка коллекции.
- **`Notification::route("mail", "x@y.com")`** — on-demand для адреса без модели.

**Подвох:** канал `database` использует `morphs` колонки `notifiable_type/notifiable_id` — после `php artisan make:notifications-table` помнить про **миграцию**.',
                'code_example' => 'class InvoicePaid extends Notification {
    public function via($notifiable): array {
        return [\'mail\', \'database\', \'broadcast\'];
    }
    public function toMail($notifiable): MailMessage {
        return (new MailMessage)->line(\'Оплата получена\');
    }
    public function toArray($notifiable): array {
        return [\'invoice_id\' => $this->invoice->id];
    }
}

$user->notify(new InvoicePaid($invoice));',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.mail_notifications',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как отправлять Mail в Laravel?',
                'answer' => 'Поток:

1. **Создать Mailable-класс**: `php artisan make:mail OrderShipped --markdown=mail.orders.shipped`.
2. **Описать письмо** в трёх методах:
   - **`envelope()`** — тема, отправитель, заголовки.
   - **`content()`** — view-шаблон (`view: \'...\'`, `markdown: \'...\'` или `text: \'...\'`).
   - **`attachments()`** — вложения (массив `Attachment::fromPath(...)`).
3. **Отправить** через фасад `Mail`:
   - **`Mail::to($user)->send(...)`** — синхронно (юзер ждёт).
   - **`Mail::to($user)->queue(...)`** — в очередь (быстро, обрабатывает воркер).
   - **`Mail::to($user)->later(now()->addMinutes(10), ...)`** — отложенная.

Драйверы (`config/mail.php`): **`smtp`**, **`mailgun`**, **`ses`** (AWS), **`postmark`**, **`resend`**, **`log`** (в `storage/logs/laravel.log`), **`array`** (для тестов).',
                'code_example' => 'php artisan make:mail OrderShipped --markdown=mail.orders.shipped

class OrderShipped extends Mailable {
    public function envelope(): Envelope {
        return new Envelope(subject: \'Заказ отправлен\');
    }
    public function content(): Content {
        return new Content(markdown: \'mail.orders.shipped\');
    }
}

Mail::to($user)->send(new OrderShipped($order));      // синхронно
Mail::to($user)->queue(new OrderShipped($order));     // в очередь
Mail::to($user)->later(now()->addMinutes(10), new OrderShipped($order));',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.mail_notifications',
            ],
        ];
    }
}
