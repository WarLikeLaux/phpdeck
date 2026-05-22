<?php

namespace Database\Seeders\Data\Categories\Yii2;

class Events
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Yii2',
                'question' => 'Что такое событие в Yii2?',
                'answer' => '**Событие** — именованный сигнал, который объект (`sender`) **испускает** в определённый момент, и на который **подписываются** обработчики.

**Зачем нужно:**

- Развязка кода: один класс делает основную работу, другой реагирует — без прямой зависимости.
- Расширяемость: подключаешь обработчик, не правя исходный класс.
- Стандартные события `ActiveRecord` (`EVENT_BEFORE_INSERT`, `EVENT_AFTER_UPDATE`) — самый частый пример.

**Чтобы класс выбрасывал события**, он должен наследовать **`yii\\base\\Component`** (не `BaseObject`).

**Базовый API:**

- `on($name, $handler)` — подписаться.
- `off($name, $handler)` — отписаться.
- `trigger($name, $event)` — выстрелить.',
                'code_example' => '<?php
use yii\\base\\Component;

class Order extends Component
{
    const EVENT_PAID = \'paid\';

    public function pay()
    {
        // ... основная логика
        $this->trigger(self::EVENT_PAID);
    }
}

$order = new Order();
$order->on(Order::EVENT_PAID, function ($event) {
    Yii::info(\'Order paid\');
});
$order->pay(); // обработчик выполнится',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.events',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что делают on(), off() и trigger()?',
                'answer' => 'Три ключевых метода событийной модели `Component`:

- **`on($name, $handler, $data = null, $append = true)`** — добавить обработчик к событию `$name`. Опционально передать `$data`, доступные в `$event->data`.
- **`off($name, $handler = null)`** — отписать обработчик. Без второго аргумента — снять **все** обработчики этого события.
- **`trigger($name, Event $event = null)`** — выстрелить событием. Если `$event` не передан, создаётся `new Event()`.

**Особенности:**

- Обработчики вызываются **в порядке регистрации**.
- `$append = false` в `on()` — вставить обработчик в начало (выполнится раньше остальных).
- Внутри обработчика можно установить **`$event->handled = true`** — следующие обработчики **не выполнятся**.',
                'code_example' => '$button = new Button();

$button->on(\'click\', function ($event) {
    echo "Clicked!\\n";
});

$handler = function ($event) {
    echo "Second handler\\n";
};
$button->on(\'click\', $handler);

$button->trigger(\'click\');
// Clicked!
// Second handler

$button->off(\'click\', $handler);
$button->trigger(\'click\');
// Clicked!

$button->off(\'click\'); // снять все
$button->trigger(\'click\');
// (ничего)',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.events',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Какие основные события у ActiveRecord в Yii2?',
                'answer' => 'Жизненный цикл записи `ActiveRecord` сопровождается набором событий:

**Сохранение:**

- **`EVENT_BEFORE_INSERT`** / **`EVENT_AFTER_INSERT`** — до/после `INSERT`.
- **`EVENT_BEFORE_UPDATE`** / **`EVENT_AFTER_UPDATE`** — до/после `UPDATE`.
- **`EVENT_BEFORE_VALIDATE`** / **`EVENT_AFTER_VALIDATE`** — вокруг `validate()`.

**Удаление:**

- **`EVENT_BEFORE_DELETE`** / **`EVENT_AFTER_DELETE`** — до/после `DELETE`.

**Поиск:**

- **`EVENT_AFTER_FIND`** — после успешной загрузки записи из БД.

**Где задавать обработчики:**

- В методе `init()` модели через `$this->on(...)`.
- Через behaviors (например `TimestampBehavior`).
- Переопределить методы `beforeSave()`, `afterSave()`, `beforeDelete()` — внутри **обязательно** вызвать `parent::...()` (иначе события не выстрелят).',
                'code_example' => '<?php
namespace app\\models;

use yii\\db\\ActiveRecord;

class Post extends ActiveRecord
{
    public function init()
    {
        parent::init();

        $this->on(self::EVENT_BEFORE_INSERT, function ($event) {
            $this->created_at = time();
        });

        $this->on(self::EVENT_BEFORE_UPDATE, function ($event) {
            $this->updated_at = time();
        });
    }

    // Альтернатива через метод
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $this->slug = \\yii\\helpers\\Inflector::slug($this->title);
        return true;
    }
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.events',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое class-level events (Event::on)?',
                'answer' => '**Class-level events** — подписка на событие **на уровне класса**, а не конкретного объекта. Сработает для **любого** экземпляра данного класса (и его потомков).

**API:** `yii\\base\\Event::on(класс, событие, обработчик)`.

**Когда применять:**

- Когда нужно повесить хук на все объекты класса (например, логировать любое сохранение `ActiveRecord`).
- Когда подписку делают **в bootstrap** приложения, а сам объект ещё не создан.
- Реагировать на статические события фреймворка.

**Парные методы:**

- `Event::off($class, $name, $handler = null)` — отписаться.
- `Event::offAll()` — снять все class-level обработчики.

**Особенность:**

- Class-level обработчики вызываются **после** обычных object-level.',
                'code_example' => '<?php
use yii\\base\\Event;
use yii\\db\\ActiveRecord;
use app\\models\\Post;

// В bootstrap или в init() компонента
Event::on(
    Post::class,
    Post::EVENT_AFTER_INSERT,
    function ($event) {
        $post = $event->sender;
        Yii::info("Post #$post->id created", \'audit\');
    }
);

// Сработает на любую новую запись Post
$p = new Post([\'title\' => \'Hello\']);
$p->save();

// Снять
Event::off(Post::class, Post::EVENT_AFTER_INSERT);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.events',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Какая сигнатура у обработчика события?',
                'answer' => 'Обработчик события — любой `callable` с **одним параметром `$event`** типа `yii\\base\\Event`.

**Допустимые формы:**

- **Closure:** `function ($event) { ... }`.
- **Имя глобальной функции:** `\'my_function\'`.
- **Метод объекта:** `[$obj, \'method\']`.
- **Статический метод:** `[\'app\\handlers\\Logger\', \'onSave\']` или `\'app\\handlers\\Logger::onSave\'`.

**Что доступно в `$event`:**

- **`$event->sender`** — объект, который выстрелил событие.
- **`$event->name`** — имя события.
- **`$event->data`** — дополнительные данные, переданные в `on()`.
- **`$event->handled`** — флаг «обработано»: после `true` остальные обработчики не выполнятся.',
                'code_example' => '<?php
use yii\\base\\Event;

class Logger
{
    public static function onSave(Event $event): void
    {
        Yii::info(get_class($event->sender) . \' saved\');
    }

    public function onLog(Event $event): void
    {
        // через объект
    }
}

// 1. Замыкание
$model->on(\'save\', fn ($e) => Yii::info(\'saved\'));

// 2. Метод объекта
$logger = new Logger();
$model->on(\'save\', [$logger, \'onLog\']);

// 3. Статический метод
$model->on(\'save\', [Logger::class, \'onSave\']);
$model->on(\'save\', \'app\\Logger::onSave\');',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.events',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое $event->sender и $event->data?',
                'answer' => 'Два часто используемых свойства объекта **`yii\\base\\Event`**, переданного в обработчик.

- **`$event->sender`** — **объект-источник** события (тот, на котором вызван `trigger()`). Через него получают доступ к атрибутам модели, состоянию.
- **`$event->data`** — **произвольные данные**, переданные третьим аргументом в `on()`. Полезно, чтобы привязать к обработчику контекст без замыкания.

**Зачем `data`:**

- Один и тот же обработчик может быть подписан на разные объекты с разным контекстом.
- Альтернатива замыканию с `use ($foo)` — проще для статических методов.',
                'code_example' => '<?php
use yii\\base\\Event;

$post = new Post([\'title\' => \'Hi\']);

// data — третий аргумент on()
$post->on(\'save\', function (Event $event) {
    $sender = $event->sender;  // Post object
    $data = $event->data;      // [\'user_id\' => 5]

    Yii::info("Post {$sender->title} saved by user {$data[\'user_id\']}");
}, [\'user_id\' => Yii::$app->user->id]);

$post->trigger(\'save\');',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.events',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Зачем нужно $event->handled = true?',
                'answer' => '**`$event->handled = true`** — флаг, которым обработчик сообщает: **«я обработал событие, остальных не вызывать»**.

**Поведение:**

- После того как один обработчик выставил `handled = true`, **следующие обработчики этой подписки пропускаются**.
- Class-level обработчики **тоже** проверяют этот флаг.
- Удобно для chain-of-responsibility: первый, кто справился — останавливает цепочку.

**Где используется в Yii2:**

- `ActionEvent` (`EVENT_BEFORE_ACTION` контроллера/модуля) — если обработчик установил `handled = true` и `$event->isValid = false`, экшен **не запустится**.
- Пользовательские bus-подобные события.',
                'code_example' => '<?php
use yii\\base\\Event;

$order = new Order();

$order->on(\'pay\', function (Event $event) {
    if (testMode()) {
        echo "Тестовый режим — оплата пропущена\\n";
        $event->handled = true; // остальные не выполнятся
    }
});

$order->on(\'pay\', function (Event $event) {
    echo "Реальная оплата\\n";
});

$order->trigger(\'pay\');
// В тестовом режиме увидим только первое сообщение',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.events',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Чем события Yii2 отличаются от событий Laravel?',
                'answer' => 'Сравнение событийных моделей:

| Признак | Yii2 | Laravel |
| --- | --- | --- |
| Тип события | **строка** (`\'paid\'`, `EVENT_BEFORE_INSERT`) | **класс-объект** (`OrderPaid`) |
| Источник | **`$event->sender`** — тот объект, что выстрелил | передаётся в конструктор события |
| Регистрация | `$obj->on(...)` или `Event::on(class, ...)` | `EventServiceProvider::$listen` или `Event::listen(...)` |
| Подписка на класс | через **class-level events** | через listener mapping |
| Очереди | вручную (положить job в обработчик) | **`ShouldQueue`** на listener |
| Discovery | нет (всё вручную) | **`EventDiscoveryEnabled`** в провайдере |

**Идеи общие:**

- Подписка, dispatch, несколько слушателей на одно событие.

**Главное отличие:** в Yii2 события — **часть объекта** (`Component`); в Laravel — **отдельные сущности**, ходящие через глобальный `Dispatcher`.',
                'code_example' => '// Yii2
class Order extends Component
{
    public function pay()
    {
        $this->trigger(\'paid\');
    }
}
$order->on(\'paid\', fn ($e) => Yii::info(\'paid\'));

// Laravel
class OrderPaid
{
    public function __construct(public Order $order) {}
}
class SendInvoice
{
    public function handle(OrderPaid $event) { /* ... */ }
}
// В коде:
event(new OrderPaid($order));',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.events',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Наследуются ли события от родительского класса?',
                'answer' => 'Да, **class-level подписка** на родителя сработает и для всех **потомков**.

**Правила:**

- `Event::on(BaseClass::class, \'evt\', $h)` — обработчик вызовется и при `trigger(\'evt\')` у наследников `BaseClass`.
- Yii2 проходит **по всей цепочке наследования** при поиске обработчиков.

**На уровне объекта:**

- `on()` действует только на конкретный экземпляр — наследования тут нет.

**Полезно для:**

- Логировать любое сохранение всех ActiveRecord — подписать на `ActiveRecord::EVENT_AFTER_INSERT`.
- Хук на все контроллеры — подписать на `Controller::EVENT_BEFORE_ACTION`.

**Осторожно:**

- Чем выше в иерархии подписан обработчик, тем больше событий он ловит — может стать узким местом производительности.',
                'code_example' => '<?php
use yii\\base\\Event;
use yii\\db\\ActiveRecord;

// Подписка на ЛЮБОЙ ActiveRecord
Event::on(
    ActiveRecord::class,
    ActiveRecord::EVENT_AFTER_INSERT,
    function ($event) {
        $class = get_class($event->sender);
        Yii::info("$class created");
    }
);

// Сработает для Post, User, Order, Comment — для всех
$post = new Post([\'title\' => \'Hi\']);
$post->save();
// log: app\\models\\Post created

$user = new User([\'username\' => \'admin\']);
$user->save();
// log: app\\models\\User created',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.events',
            ],
        ];
    }
}
