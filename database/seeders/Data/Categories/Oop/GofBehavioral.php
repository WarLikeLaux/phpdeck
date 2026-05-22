<?php

namespace Database\Seeders\Data\Categories\Oop;

class GofBehavioral
{
    public static function all(): array
    {
        return [
            [
                'category' => 'ООП',
                'topic' => 'oop.gof_behavioral',
                'difficulty' => 3,
                'question' => 'Паттерн Chain of Responsibility',
                'answer' => '**Chain of Responsibility (CoR, цепочка обязанностей)** — поведенческий паттерн: **передаёт запрос последовательно** по цепочке обработчиков, пока кто-то его не обработает (или цепочка не закончится).

**Идея:**

- Каждый обработчик хранит ссылку на **следующего** в цепочке.
- При получении запроса решает: **обработать** или **передать дальше** (или **и то, и другое**).
- Клиент **не знает** длину и состав цепочки.

**Два варианта прохождения:**

| Вариант | Поведение |
|---|---|
| **Pure CoR** | первый, кто умеет, обрабатывает и **завершает** цепочку |
| **Pipeline** | **каждый** обработчик что-то делает (логирует/трансформирует) и **обязательно** передаёт дальше |

**Где встречается:**

- **HTTP middleware** (Laravel, Symfony, PSR-15) — классическая pipeline-форма.
- **Event bubbling** в DOM / GUI — событие всплывает, пока кто-то не вызовет `stopPropagation()`.
- **Логгеры** с уровнями — DEBUG передаёт INFO передаёт WARN.
- **Авторизация** — несколько `Gate`/`Policy` проверок последовательно.
- **Валидация формы** — цепочка правил.
- **PHP `set_exception_handler`** до middleware-стека.

**Плюсы:**

- **Слабая связанность** — отправитель не знает получателя.
- **Гибкость** — порядок и состав меняются в рантайме (массив middleware из конфига).
- **OCP** — новый обработчик = новый класс, цепочка не правится.

**Минусы:**

- **Не гарантировано**, что запрос будет обработан (можно дойти до конца без результата).
- **Трудная отладка** — стек вызовов длинный, не сразу видно, кто что сделал.
- **Производительность** — лишняя индирекция при глубокой цепочке.

**Laravel Pipeline** — это именно CoR: `Pipeline::send($request)->through($middlewares)->then($destination)`.',
                'code_example' => '<?php
abstract class Handler
{
    protected ?Handler $next = null;
    public function setNext(Handler $h): Handler
    {
        $this->next = $h;
        return $h;
    }
    abstract public function handle(Request $req): ?Response;
}

class AuthHandler extends Handler
{
    public function handle(Request $req): ?Response
    {
        if (!$req->isAuth()) return new Response(\'401\');
        return $this->next?->handle($req);
    }
}

class LogHandler extends Handler
{
    public function handle(Request $req): ?Response
    {
        echo "log\n";
        return $this->next?->handle($req);
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.gof_behavioral',
                'difficulty' => 3,
                'question' => 'Паттерн Command',
                'answer' => '**Command (команда)** — поведенческий паттерн: **превращает запрос в объект**, содержащий действие и все его параметры.

**Зачем превращать вызов метода в объект:**

| Возможность | Как используется |
|---|---|
| **Очередь** запросов | Laravel `dispatch(new SendEmailJob(...))`, очереди задач |
| **Логирование** | сохраняем команду в журнал, можем повторить |
| **Undo / Redo** | команда умеет `execute()` и `undo()` |
| **Параметризация клиента** | передаём готовую команду как callback |
| **Распределённое выполнение** | сериализуем команду, отправляем на другой сервер |
| **Транзакционность** | накопить команды и выполнить пакетом, откатить при ошибке |

**Структура (4 роли):**

| Роль | Что делает |
|---|---|
| **Command** | интерфейс с `execute()` |
| **ConcreteCommand** | хранит **receiver + параметры**, в `execute()` дёргает receiver |
| **Receiver** | объект, **выполняющий реальную работу** |
| **Invoker** | хранит команду и вызывает её `execute()`, **не зная** что внутри |
| **Client** | создаёт команду и связывает с receiver |

**Где встречается:**

- **Laravel Jobs** — `class SendEmailJob` с методом `handle()` — это Command.
- **Laravel Commands** (Artisan, `php artisan ...`) — Command + Invoker (`Kernel`).
- **Symfony Messenger** — `MessageHandler` для Command-объектов.
- **CQRS Command** — Command-объект описывает изменение состояния, отдельный Handler выполняет.
- **GUI** кнопки/меню с `undo`-стеком.

**CQRS-связь:** Command в архитектурном смысле (CQRS) — это **частный случай** GoF Command, заточенный под **изменение состояния** домена (в противоположность Query).

**Подводные камни:**

- **Серилизация** команды для очереди требует, чтобы все поля были сериализуемыми (или использовать `SerializesModels` в Laravel).
- **Undo** работает только если команда **сохраняет состояние ДО** — иначе нечем откатывать.',
                'code_example' => '<?php
interface Command
{
    public function execute(): void;
}

class SendEmailCommand implements Command
{
    public function __construct(
        private Mailer $mailer,
        private string $to,
        private string $body,
    ) {}

    public function execute(): void
    {
        $this->mailer->send($this->to, $this->body);
    }
}

class CommandQueue
{
    private array $queue = [];
    public function add(Command $c): void { $this->queue[] = $c; }
    public function run(): void
    {
        foreach ($this->queue as $c) $c->execute();
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.gof_behavioral',
                'difficulty' => 3,
                'question' => 'Паттерн Iterator',
                'answer' => '**Iterator (итератор)** — поведенческий паттерн: **способ последовательного доступа** к элементам коллекции, **не раскрывая её внутреннего устройства**. Клиенту всё равно — массив, связный список или дерево.

**В PHP два встроенных интерфейса:**

| Интерфейс | Методы | Когда брать |
|---|---|---|
| **`Iterator`** | `current()`, `key()`, `next()`, `rewind()`, `valid()` | **полный контроль** курсора, ручной сдвиг |
| **`IteratorAggregate`** | один `getIterator(): Iterator` | **по умолчанию** — делегируешь готовому итератору или **генератору** |

Реализация **любого** из них позволяет использовать объект в **`foreach`**.

**Современная альтернатива — генераторы (`yield`):**

- `yield` возвращает `Generator`, который **сам реализует `Iterator`**.
- Удобно для **ленивой выдачи** — читать строки большого файла, не загружая весь файл в память.
- Меньше boilerplate — нет пяти методов с состоянием курсора.

**Связанные интерфейсы:**

| Интерфейс | Что добавляет |
|---|---|
| **`Countable`** | `count($obj)` |
| **`ArrayAccess`** | `$obj[$k]` |
| **`Traversable`** | базовый «маркерный» интерфейс для `foreach` |

**Где встречается:**

- **Eloquent `Collection`** — реализует `IteratorAggregate`.
- **`SplDoublyLinkedList`**, **`SplQueue`**, **`SplStack`** — встроенные итерируемые контейнеры.
- **`DirectoryIterator`**, **`FilesystemIterator`**, **`RecursiveDirectoryIterator`** — обход файловой системы.
- Чтение **больших CSV** — пишут генератор, который `yield`-ит по строкам.

**Подводные камни:**

- Реализуя свой `Iterator`, **легко забыть `rewind()`** — `foreach` упадёт на втором прогоне.
- Генератор **одноразовый** — повторный `foreach` по тому же `Generator` бросит `Exception`. Нужно вызывать функцию-генератор заново.
- `Iterator` курсорный — не подходит для **многократного обхода** без явного `rewind()`.',
                'code_example' => '<?php
class NumberCollection implements \IteratorAggregate
{
    public function __construct(private array $items) {}

    public function getIterator(): \Generator
    {
        foreach ($this->items as $item) {
            yield $item * 2;
        }
    }
}

$nc = new NumberCollection([1, 2, 3]);
foreach ($nc as $n) echo $n . \' \'; // 2 4 6',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.gof_behavioral',
                'difficulty' => 4,
                'question' => 'Паттерн Mediator',
                'answer' => '**Mediator (посредник)** — поведенческий паттерн: **уменьшает связность между классами**, заставляя их общаться **не напрямую**, а через **объект-посредник**.

**До и после:**

| Без Mediator | С Mediator |
|---|---|
| `N × N` связей «**все со всеми**» | `N` связей **«все через одного»** |
| Каждый компонент знает про остальные | Каждый знает **только Mediator** |
| Добавление участника = правка всех | Добавление = регистрация в Mediator |

**Структура:**

- **`Mediator`** — интерфейс с методом `notify($sender, $event)`.
- **`ConcreteMediator`** — реализация, знающая всех участников.
- **`Colleague`** (участник) — хранит ссылку **только на Mediator**, не на других участников.

**Применения:**

| Где | Пример |
|---|---|
| **Чат** | `ChatRoom` — пользователи пишут через комнату |
| **GUI** | диалоговое окно: кнопки/чекбоксы реагируют **через диалог** |
| **Airline traffic** | вышка управляет всеми самолётами вместо переговоров «борт-борт» |
| **Workflow** | оркестратор шагов вместо прямых вызовов |

**В Laravel:** `Event Dispatcher` — частный случай Mediator (но **слабее связанный**): отправитель события не знает, кто его слушает.

**Подводный камень:** **Mediator склонен превращаться в god object** — см. отдельную карточку. Решение — делить на узкие mediator-ы по подсистемам, выносить логику в участников.',
                'code_example' => '<?php
interface ChatMediator
{
    public function send(string $msg, User $from): void;
}

class ChatRoom implements ChatMediator
{
    private array $users = [];
    public function add(User $u): void { $this->users[] = $u; }
    public function send(string $msg, User $from): void
    {
        foreach ($this->users as $u) {
            if ($u !== $from) $u->receive($msg);
        }
    }
}

class User
{
    public function __construct(
        private string $name,
        private ChatMediator $chat,
    ) {}
    public function send(string $msg): void
    {
        $this->chat->send($msg, $this);
    }
    public function receive(string $msg): void
    {
        echo "{$this->name} получил: $msg\n";
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.gof_behavioral',
                'difficulty' => 4,
                'question' => 'Паттерн Memento',
                'answer' => '**Memento (хранитель)** — поведенческий паттерн: **сохраняет и восстанавливает** прошлые состояния объекта, **не нарушая его инкапсуляции**.

**Типичное применение:** **undo/redo**, snapshot-based откат транзакции, save/load в играх.

**Три участника:**

| Участник | Роль |
|---|---|
| **`Originator`** | объект, чьё состояние сохраняется (`Editor`) |
| **`Memento`** | **immutable снимок** состояния (`EditorMemento`) |
| **`Caretaker`** | **хранит** снимки, **не зная** их внутренней структуры (`History`) |

**Идея инкапсуляции:**

- `Originator` **сам создаёт** memento (через `save()`) и **сам читает** из него (через `restore()`).
- `Caretaker` хранит memento как **«чёрный ящик»** — не знает, что внутри.
- Внешний код **не видит** приватного состояния — оно живёт только между `Originator` и его собственным memento.

**Реализации в PHP:**

| Способ | Плюсы | Минусы |
|---|---|---|
| **Отдельный класс `Memento`** с `readonly` полями | классический GoF, явно | многословно |
| **Сериализация** (`serialize`/`__sleep`) | универсально | теряем контроль над форматом |
| **Immutable DTO** | подходит и для event sourcing | требует копии всего состояния |

**Подводные камни:**

- **Память** — каждый snapshot копирует весь объект. Для больших объектов — оверхед.
- **Глубокие ссылки** — snapshot не должен **разделять mutable-объекты** с originator-ом.
- В PHP «**инкапсуляция через memento не абсолютна**» — Reflection и `__clone` дают обход (см. отдельную карточку).',
                'code_example' => '<?php
class EditorMemento // снимок
{
    public function __construct(public readonly string $content) {}
}

class Editor // originator
{
    private string $content = \'\';

    public function type(string $text): void
    {
        $this->content .= $text;
    }
    public function save(): EditorMemento
    {
        return new EditorMemento($this->content);
    }
    public function restore(EditorMemento $m): void
    {
        $this->content = $m->content;
    }
}

$editor = new Editor();
$editor->type(\'Hello\');
$snapshot = $editor->save();
$editor->type(\' world\');
$editor->restore($snapshot); // вернулись к "Hello"',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.gof_behavioral',
                'difficulty' => 3,
                'question' => 'Паттерн Observer',
                'answer' => '**Observer (наблюдатель)** — поведенческий паттерн: определяет зависимость **«один ко многим»** между объектами. Когда **Subject** меняет состояние, все его **Observers** **уведомляются автоматически**.

**Структура:**

| Роль | Что делает |
|---|---|
| **Subject** | хранит список наблюдателей, метод `attach`/`detach`/`notify` |
| **Observer** | имеет `update($subject)` или типизированный `update(SomeEvent $e)` |

**Две модели уведомления:**

| Модель | Кто откуда тянет данные | Плюс | Минус |
|---|---|---|---|
| **Push** | subject передаёт **все данные** в `update($data)` | observer не зависит от subject | передаём данные, которые не всем нужны |
| **Pull** | subject передаёт **себя**, observer **сам спрашивает** что нужно | гибко | observer связан с subject |

**Где применяется:**

- **Event-driven системы** — Laravel Events, Symfony EventDispatcher.
- **Реактивное программирование** — RxJS, RxPHP.
- **Eloquent Observers** — `UserObserver::created()`, `updated()`, `deleting()`.
- **Pub/Sub** — Redis pub/sub, Kafka (Observer + Mediator + persistence).
- **WebSocket / SSE broadcasts** — клиенты подписаны на канал.

**В современном Web-MVC** (Laravel/Symfony) **push-модель из контроллера в шаблон**, не Observer. Исторический Smalltalk-80 MVC (View подписана на Model) почти не встречается, потому что HTTP-запрос **stateless** — нет долгоживущего соединения между моделью и view.

**Observer vs Mediator:**

| | **Observer** | **Mediator** |
|---|---|---|
| Связь | **один subject → много observers** | **много объектов через одного посредника** |
| Тип уведомления | **broadcast** одного события | **роутинг** между конкретными компонентами |
| Кто кого знает | observer знает subject (через подписку) | компоненты знают только медиатор |

**Подводные камни:**

- **Утечка памяти** — забыл `detach`, observer держит ссылку → не освобождается.
- **Каскад уведомлений** — observer меняет subject → notify → бесконечный цикл.
- **Порядок вызова** — observers вызываются в порядке регистрации, но клиент не должен на это полагаться.',
                'code_example' => '<?php
interface Observer
{
    public function update(string $event): void;
}

class EventBus
{
    private array $observers = [];
    public function subscribe(Observer $o): void
    {
        $this->observers[] = $o;
    }
    public function emit(string $event): void
    {
        foreach ($this->observers as $o) $o->update($event);
    }
}

class EmailSubscriber implements Observer
{
    public function update(string $event): void
    {
        echo "Send email on $event\n";
    }
}

$bus = new EventBus();
$bus->subscribe(new EmailSubscriber());
$bus->emit(\'order.created\');',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.gof_behavioral',
                'difficulty' => 3,
                'question' => 'Паттерн State',
                'answer' => '**State (состояние)** — поведенческий паттерн: позволяет объекту **менять поведение** при изменении внутреннего состояния. Создаёт **иллюзию**, что класс меняется.

**Идея:**

- **Каждое состояние** — **отдельный класс**, реализующий общий интерфейс.
- **Context** (`Order`) **делегирует** всю работу текущему `State`.
- Переход в новое состояние = **подмена объекта** state у контекста.

**Альтернатива:**

| Подход | Проблема |
|---|---|
| **`switch ($status)`** в каждом методе | новый статус — правка всех методов; нарушение OCP |
| **Set of flags** (`$isPaid`, `$isShipped`) | неконсистентные комбинации, инварианты размыты |
| **State** | новый статус — новый класс, без правки старых |

**Где применяется:**

- **Workflow заказа** — `New → Paid → Shipped → Delivered → Returned`.
- **Конечный автомат** — статусы пользователя (`Guest → Registered → Active → Banned`).
- **TCP-соединение** — `LISTEN → SYN_SENT → ESTABLISHED → ...`.
- **UI-компоненты** — кнопка `Idle → Loading → Success → Error`.
- **Парсеры** — Lexer как FSM.

**State vs Strategy** — структурно идентичны (Context + интерфейс + N реализаций):

| | **State** | **Strategy** |
|---|---|---|
| Намерение | смена **внутреннего состояния** объекта | выбор **алгоритма** |
| Кто меняет | states **переходят сами** (state знает следующий) | клиент выбирает стратегию |
| Связь между реализациями | state знают друг друга, реализуют переходы | стратегии **независимы** |
| Пример | workflow заказа | способ оплаты, сортировки |

**Подводные камни:**

- **Кто отвечает за переходы** — Context или State? GoF предлагает оба варианта. Если State — состояния знают друг друга, **жёсткая связь**.
- **Дублирование** — методы, которые «недопустимы» в большинстве состояний, приходится реализовывать заглушками (бросать исключение). Помогает **AbstractState** с дефолтным «throw».
- **Сериализация** — для persistence обычно хранят **enum** статуса в БД и пересоздают State при загрузке.

**Современная альтернатива в Laravel** — пакет [spatie/laravel-model-states](https://github.com/spatie/laravel-model-states) реализует State поверх Eloquent.',
                'code_example' => '<?php
interface OrderState
{
    public function pay(Order $o): void;
    public function ship(Order $o): void;
}

class NewState implements OrderState
{
    public function pay(Order $o): void
    {
        $o->setState(new PaidState());
        echo "Оплачено\n";
    }
    public function ship(Order $o): void
    {
        throw new \DomainException(\'Нельзя отправить - не оплачено\');
    }
}

class PaidState implements OrderState
{
    public function pay(Order $o): void
    {
        throw new \DomainException(\'Уже оплачено\');
    }
    public function ship(Order $o): void
    {
        $o->setState(new ShippedState());
        echo "Отправлено\n";
    }
}

class ShippedState implements OrderState
{
    public function pay(Order $o): void
    {
        throw new \DomainException(\'Уже отправлено\');
    }
    public function ship(Order $o): void
    {
        throw new \DomainException(\'Уже отправлено\');
    }
}

class Order
{
    private OrderState $state;
    public function __construct() { $this->state = new NewState(); }
    public function setState(OrderState $s): void { $this->state = $s; }
    public function pay(): void { $this->state->pay($this); }
    public function ship(): void { $this->state->ship($this); }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.gof_behavioral',
                'difficulty' => 3,
                'question' => 'Паттерн Strategy',
                'answer' => '**Strategy (стратегия)** — поведенческий паттерн: определяет **семейство взаимозаменяемых алгоритмов** и делает их **подменяемыми во время выполнения**.

**Идея:**

- Объявляется **общий интерфейс** — `SortStrategy`, `PaymentMethod`, `ShippingCalculator`.
- Каждый алгоритм — **отдельная реализация**.
- **Клиент** (`Context`) принимает стратегию в конструкторе/методе и **делегирует** работу.

**Что заменяет:** длинные `if`/`else` или `switch` по типу — антипаттерн «**Replace Conditional with Polymorphism**».

**Где применяется:**

- **Способы оплаты** — `StripePayment`, `PayPalPayment`, `YandexKassaPayment`.
- **Расчёт доставки** — `CdekCalculator`, `PostCalculator`, `CourierCalculator`.
- **Алгоритмы сортировки / сжатия / шифрования**.
- **Форматы экспорта** — `CsvExporter`, `XmlExporter`, `PdfExporter`.
- **Стратегии кеширования** — Redis vs Memcached vs in-memory.
- **Скидки / тарифы** — `BlackFridayDiscount`, `LoyaltyDiscount`.

**Что даёт:**

- **OCP** — новый алгоритм = новый класс, клиент не правится.
- **Тестируемость** — каждую стратегию **юнит-тестируем отдельно** без HTTP/БД.
- **Подмена в рантайме** — выбираем стратегию по конфигу/параметру запроса.

**Strategy vs State:**

| | **Strategy** | **State** |
|---|---|---|
| Намерение | выбор **алгоритма** | смена **состояния** |
| Кто меняет | клиент | состояние **переходит само** |
| Связь между реализациями | независимы | знают друг друга |

**Strategy vs Template Method:**

| | **Strategy** | **Template Method** |
|---|---|---|
| Механизм | **композиция** | **наследование** |
| Подмена | runtime | compile-time (`extends`) |
| Зависимость | от интерфейса | от базового класса |

**Когда классическая Strategy избыточна:** если алгоритм — это **короткая чистая функция** без зависимостей, можно передавать **`callable`** (замыкание / first-class callable `$fn(...)`). Граница: появилось **состояние или зависимости** — возвращаемся к полноценному классу.

**Тэгирование в Laravel:** удобно зарегистрировать все стратегии как **tagged services** в контейнере и резолвить через `Container::tagged(\'shipping\')`.',
                'code_example' => '<?php
interface SortStrategy
{
    public function sort(array $data): array;
}

class QuickSort implements SortStrategy
{
    public function sort(array $data): array { sort($data); return $data; }
}

class BubbleSort implements SortStrategy
{
    public function sort(array $data): array { /* ... */ return $data; }
}

class Sorter
{
    public function __construct(private SortStrategy $strategy) {}
    public function sort(array $data): array
    {
        return $this->strategy->sort($data);
    }
}

$s = new Sorter(new QuickSort());
$s->sort([3, 1, 2]);',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.gof_behavioral',
                'difficulty' => 3,
                'question' => 'Паттерн Template Method',
                'answer' => '**Template Method (шаблонный метод)** — поведенческий паттерн: определяет **скелет алгоритма** в методе базового класса, **делегируя реализацию некоторых шагов потомкам**. Потомки переопределяют **шаги**, не меняя **структуру** алгоритма.

**Идея:**

- В базовом классе **`final` (или non-final) метод** содержит **порядок шагов**.
- Шаги бывают **трёх видов**:

| Тип шага | Где определён | Может ли потомок переопределить |
|---|---|---|
| **`final` шаг** | в базе с реализацией | нет — фиксированная часть алгоритма |
| **`abstract` шаг (hook)** | объявлен в базе без тела | **обязан** реализовать |
| **`virtual` шаг с дефолтом** | в базе с дефолтом | **может** переопределить |

**Где применяется:**

- **Импорт данных** — `load → parse → validate → save`, специфичный `parse()` в каждом формате.
- **Тестовые фреймворки** — `setUp() → test() → tearDown()` (`PHPUnit\\TestCase`).
- **HTTP-фреймворки** — обработка запроса со специфичной валидацией/обработкой.
- **Жизненный цикл объекта** — `register → boot → shutdown` (Laravel `ServiceProvider`).
- **ORM** — `beforeSave → save → afterSave`.

**Template Method vs Strategy:**

| | **Template Method** | **Strategy** |
|---|---|---|
| Механизм | **наследование** | **композиция** |
| Скелет алгоритма | в базовом классе | в клиенте |
| Подменяемость | compile-time (один потомок) | runtime (любая стратегия) |
| Иерархия | да | нет |

**Подводные камни — fragile base class и нарушение LSP:**

- **Open recursion** — базовый метод вызывает `$this->step()`, который переопределён в потомке; изменение базы тихо ломает потомков.
- Базовый класс **негласно полагается** на инварианты hook-методов (вернул не-null, не бросил, вернул непустой массив). Потомок их нарушает — родительский алгоритм ломается → **нарушение LSP**.

**Защита:**

- `final` для **«обязательных»** шагов — нельзя переопределить.
- **Явный контракт** hook-методов (типы, `@throws`, `@return`, ассерты).
- Тесты, проверяющие соблюдение инвариантов **на каждом наследнике**.
- Если расширяемость существенно важна — **уход на Strategy** через композицию.',
                'code_example' => '<?php
abstract class DataImporter
{
    final public function import(string $file): void
    {
        $raw = $this->load($file);
        $data = $this->parse($raw);
        $this->save($data);
    }

    protected function load(string $f): string
    {
        return file_get_contents($f);
    }
    abstract protected function parse(string $raw): array;
    protected function save(array $d): void { /* ... */ }
}

class CsvImporter extends DataImporter
{
    protected function parse(string $raw): array
    {
        return array_map(\'str_getcsv\', explode("\n", $raw));
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.gof_behavioral',
                'difficulty' => 4,
                'question' => 'Паттерн Visitor',
                'answer' => '**Visitor (посетитель)** — поведенческий паттерн: **добавляет новые операции** к иерархии объектов, **не меняя их классы**.

**Проблема:** есть **иерархия классов** (`Circle`, `Square`, `Triangle`), и нужно добавить новую операцию (расчёт площади / экспорт / валидация / рендер). **Без Visitor** — менять **каждый** класс под каждую новую операцию.

**Решение:** **собрать все операции одного типа в одном Visitor-классе** — `AreaCalculator`, `JsonExporter`, `Validator`.

**Double dispatch** (двойная диспетчеризация):

| Шаг | Вызов | Что определяется |
|---|---|---|
| 1 | `$shape->accept($visitor)` | **конкретный класс shape** (Circle/Square) — через виртуальный вызов |
| 2 | внутри: `$visitor->visitCircle($this)` | **конкретный метод visitor** под этот тип |

PHP **не имеет нативного multiple dispatch** — поэтому два явных вызова.

**Когда брать:**

- Иерархия типов **стабильна**, но операций над ней **много и разных**.
- Операции лучше держать **рядом друг с другом** (один Visitor = один use case).
- Hop в стороне от иерархии — например, `ToJsonVisitor` не должен жить в `Shape`.

**Когда — не брать:**

- Иерархия часто **расширяется новыми типами** → каждое добавление = правка **всех visitor-ов** (см. отдельную карточку про инкапсуляцию).
- Операций мало — проще обычный метод в классах.

**Связанные паттерны:** часто ходит **по Composite-структуре**; конкурирует со `switch (true) { instanceof... }`.',
                'code_example' => '<?php
interface Visitor
{
    public function visitCircle(Circle $c): void;
    public function visitSquare(Square $s): void;
}

interface Shape
{
    public function accept(Visitor $v): void;
}

class Circle implements Shape
{
    public float $r = 1;
    public function accept(Visitor $v): void { $v->visitCircle($this); }
}

class Square implements Shape
{
    public float $side = 1;
    public function accept(Visitor $v): void { $v->visitSquare($this); }
}

class AreaCalculator implements Visitor
{
    public float $total = 0;
    public function visitCircle(Circle $c): void
    {
        $this->total += M_PI * $c->r ** 2;
    }
    public function visitSquare(Square $s): void
    {
        $this->total += $s->side ** 2;
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.gof_behavioral',
                'difficulty' => 4,
                'question' => 'Strategy в реальном CRUD: как избавиться от switch по типу доставки (СДЭК / Почта России / курьер) в контроллере?',
                'answer' => '**Антипаттерн:** контроллер **switch-ит** по строке `method` и **сам считает** стоимость.

**Что нарушено:**

- **SRP** — контроллер и про HTTP, и про калькуляцию доставки.
- **OCP** — новый перевозчик = **правка контроллера**.
- **Тестируемость** — не проверить логику без HTTP-обвязки.
- **Дублирование** — те же варианты появятся в `OrderObserver`, в e-mail-шаблоне, в админке.

**Решение — Strategy:**

- Каждый способ — **отдельный класс** с общим интерфейсом:

```php
interface ShippingCalculator {
    public function supports(string $method): bool;
    public function calculate(Order $order): Money;
}
```

- **`ShippingResolver`** перебирает реализации, возвращает подходящую.
- **Контроллер инжектит резолвер** и **делегирует**.

**В Laravel — три способа сборки:**

| Способ | Когда брать |
|---|---|
| **Container tagging** — `$app->tag([...], "shipping")` + `$app->tagged("shipping")` | список стратегий растёт |
| **Контейнерный bind через match** | стратегий 2-3, выбор по ключу |
| **Service Provider с реестром** | нужна динамическая регистрация (плагины) |

**Что даёт:**

- **Новый перевозчик** = **новый класс**, без правки старого кода — соблюдён OCP.
- Каждая стратегия **unit-тестируется без HTTP**.
- Контроллер **тонкий** — только маршрутизация запроса.
- Логика **переиспользуется** в Job, Observer, админке — везде через тот же резолвер.

**Эта же форма применима к:** оплата (Stripe / PayPal / СБП), экспорт (CSV / Excel / PDF), импорт, расчёт налога, расчёт скидок.',
                'code_example' => '<?php
// ❌ Антипаттерн - switch внутри контроллера
class CheckoutController {
    public function shippingCost(Request $r): JsonResponse {
        switch ($r->input("method")) {
            case "cdek":    $cost = $r->weight * 50; break;
            case "post":    $cost = max(300, $r->weight * 30); break;
            case "courier": $cost = 500; break;
            default: throw new InvalidArgumentException();
        }
        return response()->json(["cost" => $cost]);
    }
}

// ✅ Strategy - каждый способ в отдельном классе
interface ShippingCalculator {
    public function supports(string $method): bool;
    public function calculate(Order $order): Money;
}

final class CdekCalculator implements ShippingCalculator {
    public function supports(string $m): bool { return $m === "cdek"; }
    public function calculate(Order $o): Money {
        return Money::rub($o->totalWeight() * 50);
    }
}

final class RussianPostCalculator implements ShippingCalculator {
    public function supports(string $m): bool { return $m === "post"; }
    public function calculate(Order $o): Money {
        return Money::rub(max(300, $o->totalWeight() * 30));
    }
}

final class ShippingResolver {
    /** @param iterable<ShippingCalculator> $calculators */
    public function __construct(private iterable $calculators) {}
    public function for(string $method): ShippingCalculator {
        foreach ($this->calculators as $c) {
            if ($c->supports($method)) return $c;
        }
        throw new InvalidArgumentException("Unknown shipping: $method");
    }
}

// AppServiceProvider::register
$this->app->tag(
    [CdekCalculator::class, RussianPostCalculator::class, CourierCalculator::class],
    "shipping"
);
$this->app->bind(
    ShippingResolver::class,
    fn ($app) => new ShippingResolver($app->tagged("shipping"))
);

// Тонкий контроллер
class CheckoutController {
    public function shippingCost(Request $r, ShippingResolver $resolver): JsonResponse {
        $cost = $resolver->for($r->input("method"))->calculate($r->user()->cartOrder());
        return response()->json(["cost" => $cost->amount()]);
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Что такое SplObserver и SplSubject в стандартной библиотеке PHP?',
                'answer' => '**`SplObserver` и `SplSubject`** — встроенные в SPL интерфейсы, реализующие **классический паттерн Observer** без своих базовых классов.

**Контракты:**

| Интерфейс | Методы |
|---|---|
| **`SplSubject`** | `attach(SplObserver $o)`, `detach(SplObserver $o)`, `notify()` |
| **`SplObserver`** | один метод `update(SplSubject $subject)` |

**Модель — pull:** субъект передаёт **сам себя** в `update()`, а наблюдатель **сам выясняет**, что нужно из его состояния.

**Где удобны:**

- Совместимость с кодом, который **ждёт именно SPL-контракт** (старые библиотеки, фреймворки до Symfony EventDispatcher).
- **Учебные примеры** Observer с минимумом кода.
- Очень узкие случаи, где не хочется тащить большой event-dispatcher ради одного события.

**Почему в современных приложениях редко используют:**

| Проблема `SplObserver` | Что в Laravel/Symfony |
|---|---|
| **`update(SplSubject $s)`** не типизирован — каждый listener делает `instanceof` | типизированные события: `class OrderPaid { public function __construct(public Order $order) {} }` |
| Pull-модель — listener связан с субъектом | Push с конкретными полями события |
| Нет приоритетов, асинхронности, очередей | Event dispatcher: `ShouldQueue`, listener priority, broadcasting |
| Subject хранит **сам** список observers | централизованный dispatcher, listeners регистрируются глобально |

**Laravel-эквивалент:**

```php
event(new OrderPaid($order));
// в listener:
class SendReceipt { public function handle(OrderPaid $event): void {} }
```

**Когда `SplObserver` всё ещё имеет смысл:** **простые внутрипроцессные подписки** в библиотеках без зависимостей, где не хочется тащить полный event-dispatcher.',
                'difficulty' => 3,
                'topic' => 'oop.gof_behavioral',
                'code_example' => '<?php
final class Order implements \SplSubject
{
    private \SplObjectStorage $observers;
    public function __construct(public string $status = \'new\')
    {
        $this->observers = new \SplObjectStorage();
    }

    public function attach(\SplObserver $o): void { $this->observers->attach($o); }
    public function detach(\SplObserver $o): void { $this->observers->detach($o); }

    public function notify(): void
    {
        foreach ($this->observers as $o) {
            $o->update($this); // передаём себя
        }
    }

    public function pay(): void
    {
        $this->status = \'paid\';
        $this->notify();
    }
}

final class EmailNotifier implements \SplObserver
{
    public function update(\SplSubject $subject): void
    {
        // достаём состояние из субъекта - типа конкретного нет
        if ($subject instanceof Order && $subject->status === \'paid\') {
            echo "Order paid - sending email\n";
        }
    }
}

$order = new Order();
$order->attach(new EmailNotifier());
$order->pay(); // notify → EmailNotifier::update

// В Laravel вместо этого - типизированные события:
// event(new OrderPaid($order));
// class SendReceipt { public function handle(OrderPaid $event): void {} }',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Почему Memento в PHP не гарантирует неизменность снимка состояния?',
                'answer' => '**В статически типизированных языках** (Java, C#, Rust) memento можно **жёстко закрыть** через private final поля + иммутабельные структуры — внешний код **физически не доберётся**.

**В PHP** (как в Python и JavaScript) у внешнего кода **всегда есть лазейки**:

| Лазейка | Что позволяет |
|---|---|
| **`Reflection`** | видит и **изменяет** `private` свойства любого объекта |
| **Передача по ссылке** | объект внутри memento — общая ссылка, **мутируется снаружи** |
| **Shallow copy** | массивы и **вложенные объекты** копируются поверхностно — общие ссылки |
| **`__get`/`__set`** | если перехватчик не запрещает запись — можно «дописать» поле |
| **`unserialize`** | вернёт **изменяемый** объект из произвольного формата |

**Вывод:** «хранитель» в PHP — **договорённость**, а **не жёсткая гарантия**.

**Как сделать надёжнее:**

1. **Иммутабельные DTO** для memento — `final readonly class`.
2. **Сериализовать состояние в строку** (JSON / serialize) — строка иммутабельна на уровне языка.
3. **Deep copy** вложенных объектов в `__clone()` — отсекаем общие ссылки.
4. **Не отдавать memento** наружу — хранить в caretaker в приватном поле.
5. **На уровне процесса** — Reflection всегда обходит; полностью «закрыть» нельзя, но цена обхода **видна в code review**.

**Граница:** в PHP **достаточно** обеспечить **неизменность по контракту** — это покрывает 99% реальных багов. Защита от злого Reflection в обычном приложении бессмысленна.',
                'difficulty' => 4,
                'topic' => 'oop.gof_behavioral',
            ],
            [
                'category' => 'ООП',
                'question' => 'Почему Mediator со временем превращается в god object и как этого избежать?',
                'answer' => '**Корень проблемы:** Mediator **централизует** общение компонентов — каждый шлёт сигналы **в одну точку**, она раздаёт остальным.

**Как это деградирует:**

| Стадия | Что происходит |
|---|---|
| 1. Начало | 3-5 компонентов, чистая связь «звезда» |
| 2. Рост | каждый новый компонент = новый метод в Mediator |
| 3. Бизнес-логика течёт внутрь | условные ветки, проверки прав, валидация |
| 4. **God Object** | Mediator знает **про каждый** компонент и **все сценарии** |

**Симптомы god-mediator:**

- **20+ методов** разных подсистем.
- **Огромный switch/match** внутри `notify()`.
- Compile-зависит от **всех** конкретных классов компонентов.
- **Тесты ломаются** при любом изменении.
- Невозможно понять, **где** живёт бизнес-правило.

**Лечение (аналогично Facade):**

1. **Разбить на узкие mediator-ы** по подсистемам — `ChatMediator`, `GameMediator`, `WorkflowMediator`.
2. **Вынести бизнес-логику в сами компоненты** — Mediator только маршрутизирует, не решает.
3. **Заменить на Event Bus** там, где связь слабая — компоненты не должны знать друг о друге **никак**, только публиковать события.
4. **Запретить** добавление методов в Mediator без явного обоснования — порог в code review.
5. **Лимиты по метрикам** — N методов / N зависимостей сигнализируют о пере-росте.

**Граница Mediator vs Event Bus:**

| | **Mediator** | **Event Bus** |
|---|---|---|
| Знает участников | **да** | нет |
| Адресация | напрямую | по подписке |
| Связь | средняя | **минимальная** |
| Когда брать | small set, **есть логика маршрутизации** | many-to-many, fire-and-forget |',
                'difficulty' => 4,
                'topic' => 'oop.gof_behavioral',
            ],
            [
                'category' => 'ООП',
                'question' => 'Почему Visitor проблематичен с точки зрения инкапсуляции?',
                'answer' => '**Корень конфликта:** Visitor — **внешний** по отношению к иерархии класс, и **не имеет доступа** к `private`/`protected` полям элементов. Но **чтобы что-то делать**, ему нужны их данные.

**Два пути — и оба компромиссные:**

| Путь | Что страдает |
|---|---|
| **Открыть геттеры** под посетителей | **инкапсуляция размыта** — поля стали публичны для всех, не только для visitor |
| **Передавать данные через `accept()`** | элемент **сам решает**, что отдать — но это **двойная диспетчеризация** с дублированием логики |

**Вторая проблема — направление расширяемости:**

| Тип расширения | Visitor | Полиморфизм методов |
|---|---|---|
| Новая **операция** для всех типов | **легко** — новый Visitor | сложно — править все классы |
| Новый **тип** элемента | **сложно** — править **всех visitor-ов** | легко — новый класс |

Visitor **оптимизирован** под добавление **новых операций**, **не** новых типов. Если иерархия часто получает новые типы — Visitor создаёт **OCP-конфликт** в visitor-классах.

**Дополнительные минусы:**

- **Виден весь набор операций** — `visitCircle()`, `visitSquare()`, `visitTriangle()` в одном интерфейсе → IDE подсказывает всё, но связность высокая.
- **Циклическая зависимость** между Visitor-интерфейсом и Element-классами.
- **PHP-специфика** — нет нативного multiple dispatch, два вызова руками.

**Когда не брать Visitor:**

- Иерархия **молодая** или **растёт**.
- Операций **мало**.
- Большая часть логики **уже** внутри элементов.

**Альтернативы:** **полиморфизм** в самих элементах (когда логика принадлежит типу), **pattern matching** (`match (true) { instanceof... }`) в PHP 8 — короче, но **взрывается** на росте типов.',
                'difficulty' => 4,
                'topic' => 'oop.gof_behavioral',
            ],
            [
                'category' => 'ООП',
                'question' => 'Когда Strategy через классы избыточен и его заменяют замыканиями?',
                'answer' => '**Классический Strategy** требует интерфейс **и по классу** на каждый алгоритм. Это **окупается**, когда есть **состояние**, зависимости и DI. **Когда нет** — раздувание иерархии без выигрыша.

**Когда брать класс-стратегию:**

| Признак | Почему класс |
|---|---|
| **Зависимости** от других сервисов | конструктор + DI |
| **Состояние** между вызовами | поля класса |
| **Несколько методов** в одной стратегии | сгруппированы в одном объекте |
| **Конфигурируется** при создании | параметры в `__construct` |
| **Тестируется отдельно** | unit-тесты, моки |

**Когда хватает замыкания / first-class callable:**

| Признак | Почему callable |
|---|---|
| Алгоритм — **короткая чистая функция** | сравнения, валидаторы, мапперы |
| **Нет состояния** между вызовами | каждый раз с нуля |
| **Одна точка вызова** | не нужны несколько методов |
| Удобно **встраивать на месте** | `usort`, `array_filter`, `Collection::sortBy` |

**Современный PHP даёт три формы callable:**

- **Анонимная функция** — `fn ($x) => $x->price`
- **First-class callable** (PHP 8.1) — `Str::slug(...)`
- **Замыкание с захватом** — `fn ($x) => $x->price * $tax` (захватывает `$tax`)

**Граница «вернуться к классу»:** появляется **зависимость** (Mailer, Repository), **состояние** (счётчик retry), **несколько методов** в стратегии (`canHandle()` + `handle()`), нужна **сериализация для очереди** (`SerializableClosure` — обходное решение, но class чище). Когда хотя бы один пункт — **бери класс**.

**В Laravel:** `Pipeline::through([...])` принимает и классы, и замыкания; `Collection::sortBy` — обычно callable.',
                'difficulty' => 4,
                'topic' => 'oop.gof_behavioral',
            ],
            [
                'category' => 'ООП',
                'question' => 'Как Template Method может приводить к нарушению LSP?',
                'answer' => '**Template Method** фиксирует **скелет алгоритма** в родителе и оставляет **hook-методы** для наследников. Сигнатуры hook-ов компилятор проверяет — но **инварианты** (контракт поведения) **нет**.

**Где ломается LSP:**

| Базовый класс ожидает | Наследник нарушил |
|---|---|
| Hook вернёт **непустой массив** | вернул `[]` → деление на 0, исключение в шаблоне |
| Hook **не выбросит исключение** | бросил `RuntimeException` → шаблон не готов |
| Hook вернёт **отсортированный** результат | вернул в произвольном порядке → бинарный поиск сломан |
| Hook **идемпотентен** | имеет побочный эффект → повторный вызов меняет данные |
| Hook завершится **за < 100ms** | сделал HTTP-вызов → шаблон висит |

**Это и есть нарушение LSP** — клиент (шаблонный алгоритм) написан против контракта родителя, наследник тихо его расшатывает.

**Защита:**

1. **Явные контракты** — типы, `@throws`, описание возврата в PHPDoc, **тесты на инварианты**.
2. **Дискриминирующие методы** — `protected abstract function step()` для обязательных шагов; `final` с дефолтом для опциональных.
3. **`final` на самом шаблонном методе** — наследник не сможет «переоткрыть» алгоритм.
4. **Hook-методы — `protected`, не `public`** — чтобы клиент не вызывал в произвольном порядке.
5. **Composition over inheritance** — если шаблон сложный, замени на **Strategy** с явным интерфейсом контракта.
6. **Linter-проверки** — `#[\\Override]` на hook-ах ловит **переименования**, но не семантику.

**Главный урок:** **наследование = публичный контракт**. Базовый класс должен **документировать ожидания** к hook-ам так же чётко, как публичный API.',
                'difficulty' => 4,
                'topic' => 'oop.gof_behavioral',
            ],
            [
                'category' => 'ООП',
                'question' => 'Что такое Strategy (стратегия) простыми словами?',
                'answer' => '**Strategy** — поведенческий паттерн: алгоритм выбирается **во время выполнения** через подмену объекта-стратегии.

**Идея:**

- Объявляешь общий интерфейс — `PaymentMethod::pay()`.
- Каждый алгоритм — отдельная реализация (`StripePayment`, `PayPalPayment`).
- Клиент (`Checkout`) принимает стратегию в конструкторе или метод и **делегирует** работу.

**Что заменяет:** длинные `if/else` или `switch` по типу.

**Плюсы:**

- Новый способ оплаты = **новый класс**, без правки клиента (соблюдён OCP).
- Каждую стратегию можно **юнит-тестировать** отдельно.

**Когда брать:** есть **семейство взаимозаменяемых алгоритмов** — оплата, доставка, формат экспорта, способы кеширования.',
                'difficulty' => 2,
                'topic' => 'oop.gof_behavioral',
                'code_example' => '<?php
interface PaymentMethod
{
    public function pay(int $cents): void;
}

class StripePayment implements PaymentMethod
{
    public function pay(int $cents): void { /* Stripe API */ }
}
class PayPalPayment implements PaymentMethod
{
    public function pay(int $cents): void { /* PayPal API */ }
}

class Checkout
{
    public function pay(PaymentMethod $method, int $cents): void
    {
        $method->pay($cents); // подменяемая стратегия
    }
}

(new Checkout())->pay(new StripePayment(), 1000);
(new Checkout())->pay(new PayPalPayment(), 1000);',
                'code_language' => 'php',
            ],
        ];
    }
}
