<?php

namespace Database\Seeders\Data\Categories\Oop;

class GofCreational
{
    public static function all(): array
    {
        return [
            [
                'category' => 'ООП',
                'topic' => 'oop.gof_creational',
                'difficulty' => 3,
                'question' => 'Паттерн Singleton',
                'answer' => '**Singleton (одиночка)** — порождающий паттерн: **один экземпляр на всё приложение** + **глобальная точка доступа**.

**Минимальная реализация:**

- **`private __construct`** — `new` снаружи запрещён.
- Статическое поле `$instance` хранит единственный объект.
- Статический метод `getInstance()` возвращает existing-объект, создавая при первом вызове.

**Чтобы запечатать singleton в PHP полностью**, закрывают **четыре** способа обхода конструктора:

| Способ обхода | Как закрыть |
|---|---|
| `clone $a` | `private function __clone()` |
| `unserialize(...)` (старый формат) | `__wakeup()` бросает |
| `unserialize(...)` (PHP 7.4+ формат) | `__unserialize()` бросает |
| **`Reflection::newInstanceWithoutConstructor()`** | **никак** — это всегда лазейка |

**Подводные камни:**

- **Глобальное состояние** — скрытая зависимость, не видно по сигнатуре класса.
- **Тяжело тестировать** — мокать статику сложно, состояние утекает между тестами.
- **Нарушает SRP** — класс отвечает и за свою задачу, и за управление жизненным циклом.
- **Скрытая связь** — `Foo::getInstance()` внутри `Bar` делает `Bar` зависимым от `Foo` без объявления.
- **Проблемы конкурентности** — в **persistent runtime** (Swoole, RoadRunner, Octane) `$instance` живёт между запросами, состояние утекает между пользователями.

**Современная альтернатива в Laravel/Symfony:** **singleton-биндинг в DI-контейнере**:

```php
$this->app->singleton(Config::class);
```

Класс остаётся **обычным** — без статики, без `getInstance()`. Контейнер сам гарантирует один экземпляр на запрос (в Octane — на worker). В тестах легко подменить через `$this->app->instance()`.',
                'code_example' => '<?php
// "Глухой" singleton, защищённый от всех способов создания второго экземпляра
final class Config
{
    private static ?self $instance = null;
    private array $data;

    private function __construct() { $this->data = []; }

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    // 1. Запретить клонирование
    private function __clone() {}

    // 2. Запретить десериализацию (старый магический метод)
    public function __wakeup(): void
    {
        throw new \\LogicException("Cannot unserialize singleton");
    }

    // 3. Запретить новый формат десериализации (PHP 7.4+)
    public function __unserialize(array $data): void
    {
        throw new \\LogicException("Cannot unserialize singleton");
    }
}

// Без __wakeup это работает - получаем ВТОРОЙ экземпляр:
// $a = Config::getInstance();
// $b = unserialize("O:6:\\"Config\\":0:{}");
// var_dump($a === $b); // false - singleton сломан

// С защитой - бросает исключение:
$c1 = Config::getInstance();
$c2 = Config::getInstance();
var_dump($c1 === $c2); // true',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.gof_creational',
                'difficulty' => 3,
                'question' => 'Паттерн Factory Method',
                'answer' => '**Factory Method (фабричный метод)** — порождающий паттерн: **определяет интерфейс создания объекта**, но **подклассы решают**, какой конкретный класс инстанцировать.

**Структура:**

- **Базовый класс** содержит общий алгоритм, который дёргает **абстрактный** `createX(): X`.
- **Подклассы** переопределяют `createX()` и подставляют конкретный продукт.
- Клиент работает с **базовым классом**, не зная конкретного типа продукта.

**Когда применять:**

- Класс **не знает заранее**, объекты какого типа создавать.
- Хочется **дать подклассам шанс** подменить создание объекта (для расширяемости).
- Создание объекта **сложное** или зависит от параметров.

**Отличие от других «фабрик» (терминология плавает):**

| | **Factory Method** | **Simple/Static Factory** | **Abstract Factory** |
|---|---|---|---|
| Реализация | абстрактный метод в базе + override | статический метод (`Money::fromCents`) | интерфейс с **несколькими** create-методами |
| Выбор продукта | подкласс | параметр / switch | конкретная фабрика |
| Сколько продуктов | один | один | семейство связанных |
| GoF | да | нет | да |

**Примеры в Laravel:**

- `Illuminate\\Database\\Eloquent\\Model::newInstance()` — `newFromBuilder()` подкласса может вернуть свой тип.
- `Illuminate\\Http\\ResponseFactory::make()` — производит `Response` разных видов.
- `Container::make()` — резолвит через биндинги (ближе к Service Locator + Factory).

**Подводные камни:** добавляет **иерархию классов** только ради создания объекта. Если выбор реализации — это **одноразовый switch по параметру**, проще статическая фабрика. Если нужно семейство — `Abstract Factory`.',
                'code_example' => '<?php
// Базовый класс с алгоритмом и абстрактным фабричным методом
abstract class Logger
{
    // Общий алгоритм - "темплейтный" вызов
    public function log(string $msg): void
    {
        $writer = $this->createWriter(); // hook для подклассов
        $writer->write(\'[\' . date(\'c\') . \'] \' . $msg);
    }

    // Подклассы решают, какой Writer создать
    abstract protected function createWriter(): Writer;
}

interface Writer { public function write(string $msg): void; }

class FileWriter implements Writer
{
    public function __construct(private string $path) {}
    public function write(string $msg): void { file_put_contents($this->path, $msg . PHP_EOL, FILE_APPEND); }
}

class DbWriter implements Writer
{
    public function write(string $msg): void { /* INSERT ... */ }
}

// Конкретные продукты
class FileLogger extends Logger
{
    public function __construct(private string $path) {}
    protected function createWriter(): Writer
    {
        return new FileWriter($this->path);
    }
}

class DbLogger extends Logger
{
    protected function createWriter(): Writer { return new DbWriter(); }
}

// Клиент использует абстрактный Logger, не зная типа Writer
function audit(Logger $logger, string $action): void
{
    $logger->log("action: $action");
}

audit(new FileLogger(\'/var/log/app.log\'), \'login\');
audit(new DbLogger(), \'logout\');',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.gof_creational',
                'difficulty' => 4,
                'question' => 'Паттерн Abstract Factory',
                'answer' => '**Abstract Factory (абстрактная фабрика)** — порождающий паттерн: **интерфейс для создания семейства связанных объектов**, не указывая их конкретных классов.

**Ключевая идея:** одна фабрика создаёт **несколько согласованных** продуктов — `WinFactory` → `WinButton` **и** `WinCheckbox`; `MacFactory` → `MacButton` **и** `MacCheckbox`. **Перемешать нельзя.**

**Abstract Factory vs Factory Method:**

| Признак | **Factory Method** | **Abstract Factory** |
|---|---|---|
| Что создаёт | **один** продукт | **семейство** продуктов |
| Реализация | один метод (часто `static` или абстрактный) | **набор методов** в интерфейсе |
| Когда брать | вариативность одного типа | согласованность нескольких типов |
| Пример | `Logger::create()` | `GuiFactory` (button + window + checkbox) |

**Когда применять:**

- Нужно создавать **группу объектов**, которые **должны быть совместимы** друг с другом (cross-platform UI, темы оформления, драйверы СУБД с разными типами объектов).
- Хочешь **менять всё семейство** одним переключателем (`new WinFactory()` → `new MacFactory()`).
- Конкретные классы клиенту знать **нельзя**.

**Подводные камни:**

- **Тяжело добавить новый продукт** — нужно обновить **все** конкретные фабрики (OCP-конфликт).
- Часто **оверкилл** для простых случаев — если согласованности не нужно, достаточно Factory Method или DI-контейнера.

**В Laravel:** `Illuminate\\Database\\Connectors\\ConnectionFactory` — для каждого драйвера (`mysql`, `pgsql`, `sqlite`) свой набор объектов (`Connector` + `Connection` + `Schema\\Grammar` + `Query\\Grammar`).',
                'code_example' => '<?php
interface Button { public function render(): string; }
interface Checkbox { public function render(): string; }

interface GuiFactory
{
    public function createButton(): Button;
    public function createCheckbox(): Checkbox;
}

class WinFactory implements GuiFactory
{
    public function createButton(): Button { return new WinButton(); }
    public function createCheckbox(): Checkbox { return new WinCheckbox(); }
}

class MacFactory implements GuiFactory
{
    public function createButton(): Button { return new MacButton(); }
    public function createCheckbox(): Checkbox { return new MacCheckbox(); }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.gof_creational',
                'difficulty' => 3,
                'question' => 'Паттерн Builder',
                'answer' => '**Builder (строитель)** — порождающий паттерн: **отделяет конструирование сложного объекта от его представления**, чтобы один процесс мог строить разные результаты.

**Когда применять:**

- У объекта **много опциональных** параметров — иначе **телескопический конструктор** (`new User($name, $email, $phone = null, $age = null, $city = null, ...)`).
- Объект **строится поэтапно**, в разном порядке.
- Нужны **разные представления** одного процесса (HTML / PDF / JSON отчёт).

**Реализации:**

| Вариант | Как выглядит | Где встречается |
|---|---|---|
| **Fluent Builder** | цепочка `$qb->where()->orderBy()->limit()` | Laravel Query Builder, SQL-DSL |
| **Step Builder** | методы возвращают **другой интерфейс** — заставляет идти в правильном порядке | `Mail::to($u)->cc($x)->send()` |
| **Director + Builder** | отдельный `Director` управляет шагами, `Builder` строит части | классический GoF |
| **Immutable Builder** | каждый метод возвращает **новый** билдер | конкурентно-безопасно |

**Что даёт:**

- Уход от **телескопического конструктора**.
- **Читаемость** — `->where(...)->orderBy(...)` лучше, чем `new Query($w, $o, $l, $s, ...)`.
- **Валидация порядка** (Step Builder) — не вызовешь `->send()` без `->to()`.
- **Гибкость** — пропускаешь ненужные шаги.

**Подводные камни:**

- **Стейтфул билдер** — каждый вызов мутирует его; пере-использовать один билдер для двух разных запросов — баг. **Immutable Builder** решает.
- **Незавершённое состояние** — забыл вызвать `->build()`, в коде осталась болтаться полу-собранная сущность. Step Builder с финальным методом решает.
- **Усложняет дизайн** — если у объекта 2–3 параметра, builder не нужен — достаточно конструктора с **named arguments** (PHP 8).

**Примеры в Laravel:** `Illuminate\\Database\\Query\\Builder`, `Illuminate\\Mail\\PendingMail`, `Validator::make()->stopOnFirstFailure()`, `Notification::send()`.',
                'code_example' => '<?php
class QueryBuilder
{
    private array $where = [];
    private string $table = \'\';
    private ?int $limit = null;

    public function from(string $t): self
    {
        $this->table = $t; return $this;
    }
    public function where(string $cond): self
    {
        $this->where[] = $cond; return $this;
    }
    public function limit(int $n): self
    {
        $this->limit = $n; return $this;
    }
    public function build(): string
    {
        $sql = "SELECT * FROM $this->table";
        if ($this->where) $sql .= \' WHERE \' . implode(\' AND \', $this->where);
        if ($this->limit) $sql .= " LIMIT $this->limit";
        return $sql;
    }
}

$sql = (new QueryBuilder())
    ->from(\'users\')
    ->where(\'age > 18\')
    ->limit(10)
    ->build();',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.gof_creational',
                'difficulty' => 4,
                'question' => 'Паттерн Prototype',
                'answer' => '**Prototype (прототип)** — порождающий паттерн: **копирование существующих объектов** без зависимости от их конкретных классов.

**Идея:** вместо `new Foo($a, $b, $c, ...)` или вызова дорогого конструктора — **клонируем готовый «эталон»** и точечно правим поля. Клиент работает с **интерфейсом-прототипом**, не зная конкретного класса.

**Когда применять:**

- **Дорогой конструктор** (загрузка из БД, сетевой запрос) — клонировать дешевле.
- **Пресеты конфигурации** — есть «эталонный» объект, нужны варианты с малыми правками.
- **Динамический набор классов** — конкретные типы известны только в рантайме.
- **Сложный граф** объектов, который проще скопировать, чем собрать заново.

**В PHP — два ключевых элемента:**

| Механизм | Что делает |
|---|---|
| `clone $obj` | **поверхностная** копия объекта (новый id, те же ссылки внутри) |
| **`__clone()`** | магический метод — вызывается **после** копирования; место для **deep copy** вложенных объектов |

**Опасности shallow copy:**

- Вложенные **объекты остаются общими** ссылками — мутация копии **изменит оригинал**.
- **Массивы** копируются (PHP arrays — value type), но **объекты внутри массивов** — нет.
- **Ресурсы** (handle, PDO) — общие, что часто **нежелательно**.
- **Циклические ссылки** ломают наивный рекурсивный `__clone` — нужна карта `original → copy` или `serialize`/`unserialize`.

**В Laravel:** Eloquent `$model->replicate()` — создаёт копию без id, готовую к `save()` как новый row — типичный Prototype.',
                'code_example' => '<?php
class Author
{
    public function __construct(public string $name) {}
}

class Document
{
    public function __construct(
        public string $title,
        public Author $author,
    ) {}

    public function __clone(): void
    {
        // глубокое копирование вложенного объекта
        // без этого $copy->author === $original->author (общая ссылка)
        $this->author = clone $this->author;
    }
}

$original = new Document(\'A\', new Author(\'Иван\'));
$copy = clone $original;
$copy->title = \'B\';
$copy->author->name = \'Пётр\';
echo $original->author->name; // Иван (благодаря __clone)',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Почему клонирование в Prototype ломается на циклических ссылках?',
                'answer' => '**Проблема в трёх слоях механики PHP:**

1. **`clone` делает shallow copy** — вложенные объекты **остаются общими** ссылками.
2. **Наивный `__clone()`** просто **рекурсивно клонирует** вложенные сущности (`$this->b = clone $this->b`).
3. **Цикл в графе** (`A → B → A`) → рекурсия **бесконечная** или **дублирует** объекты по разу на каждый виток.

**Что случается на цикле:**

| Сценарий | Результат |
|---|---|
| Stack overflow | бесконечная рекурсия `clone A → clone B → clone A → ...` |
| Дублирование | каждый узел копируется N раз вместо одного |
| Потеря идентичности | после копии `$a->b->a !== $a` — связь сломана |

**Решения:**

| Подход | Как работает | Минусы |
|---|---|---|
| **Карта `original → copy`** | вести `SplObjectStorage`; при повторной встрече возвращать **уже созданную копию** | руками во всех `__clone` |
| **`serialize` + `unserialize`** | PHP **сам отслеживает** ссылки — циклы сохраняются | не работает с `Closure`, ресурсами, лимит сериализуемых типов |
| **Внешний deep-clone** | сторонняя функция/lib (`myclabs/deep-copy`) | внешняя зависимость |

**Лучшая практика:** **избегать циклов** в доменной модели (использовать `id` вместо обратных ссылок), либо использовать **проверенную deep-copy библиотеку**.',
                'difficulty' => 4,
                'topic' => 'oop.gof_creational',
            ],
            [
                'category' => 'ООП',
                'question' => 'В чём разница между паттерном Factory и Dependency Injection?',
                'answer' => '**Понятия ортогональные** — они отвечают на разные вопросы и **часто сочетаются**.

| | **Factory** | **Dependency Injection** |
|---|---|---|
| Вопрос | **«КАК создать»** | **«КТО ПРИНЕСЁТ»** |
| Знает | конкретные классы, параметры, конфиг | только сигнатуру (тип-хинт) |
| Когда вызывается | в момент **создания** объекта | в момент **сборки графа** (старт приложения / запрос) |
| Жизненный цикл | каждый вызов = **новый** объект (обычно) | обычно **один** на запрос |
| Контекст | runtime-выбор реализации | compile-time контракт |

**Когда брать что:**

| Сценарий | Решение |
|---|---|
| Объект нужен **ОДИН** на запрос/сервис | **DI** — инжектим в конструктор |
| Тип объекта определяется **в рантайме** (по полю запроса, конфигу) | **Factory** |
| Объект создаётся **многократно** в цикле | **Factory** |
| Создание **сложное** (валидация, выбор реализации) | **Factory** |
| Нужна **подменяемость в тестах** | **DI + интерфейс** |
| Семейство связанных объектов | **Abstract Factory** + DI |

**Главное — они комбинируются:** часто **фабрика сама становится сервисом** и **инжектится через DI** туда, где нужно создавать объекты по требованию. Получаем лучшее: явная зависимость от **фабрики** + рантайм-создание конкретных продуктов.

**Антипаттерн:** делать `new Foo()` внутри сервиса вместо DI — теряем подменяемость. Делать DI там, где нужно много разных экземпляров с runtime-параметрами, — заставляет тащить контейнер в код (Service Locator). **Граница:** **зависимость один раз** → DI. **Несколько объектов с параметрами** → Factory.',
                'difficulty' => 3,
                'topic' => 'oop.gof_creational',
                'code_example' => '<?php
// DI - доставляем УЖЕ ГОТОВУЮ зависимость
class OrderService
{
    public function __construct(
        private OrderRepository $repo,    // создаст контейнер - один на сервис
        private Logger $logger,
    ) {}
}

// Factory - создаём МНОГО объектов в рантайме, выбор по входу
interface Notifier { public function send(string $msg): void; }
class EmailNotifier implements Notifier { public function send(string $m): void {} }
class SmsNotifier   implements Notifier { public function send(string $m): void {} }
class PushNotifier  implements Notifier { public function send(string $m): void {} }

final class NotifierFactory
{
    public function __construct(
        private Container $container, // фабрика сама зависит от DI
    ) {}

    public function make(string $channel): Notifier
    {
        return match ($channel) {
            \'email\' => $this->container->make(EmailNotifier::class),
            \'sms\'   => $this->container->make(SmsNotifier::class),
            \'push\'  => $this->container->make(PushNotifier::class),
            default => throw new InvalidArgumentException("Unknown: $channel"),
        };
    }
}

// DI + Factory вместе: фабрика инжектится, в рантайме создаёт нужный Notifier
class NotificationService
{
    public function __construct(private NotifierFactory $factory) {}

    public function notify(User $user, string $msg): void
    {
        foreach ($user->channels() as $channel) {
            $this->factory->make($channel)->send($msg);
        }
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Что такое Singleton простыми словами?',
                'answer' => '**Singleton** — порождающий паттерн: гарантия **одного экземпляра** класса на всё приложение + глобальная точка доступа.

**Реализация в PHP:**

- **`private __construct`** — `new` снаружи запрещён.
- Статическое поле `$instance` хранит единственный объект.
- Статический метод `getInstance()` создаёт при первом вызове, потом возвращает существующий.
- Закрывают `__clone`, `__wakeup`, `__unserialize` — иначе можно получить второй экземпляр.

**Где встречается:** конфиги, логгеры, соединения с БД, кэш в памяти процесса.

**Минусы:**

- **Глобальное состояние** — скрытая зависимость, видно только при `Foo::getInstance()` внутри.
- Тестировать тяжело — мокать статику сложно.
- Нарушает SRP — класс отвечает и за свою задачу, и за управление жизненным циклом.

**Современная альтернатива в Laravel:** singleton-биндинг в контейнере `$this->app->singleton(Config::class)` — контейнер сам гарантирует один экземпляр, а класс остаётся обычным.',
                'difficulty' => 2,
                'topic' => 'oop.gof_creational',
                'code_example' => '<?php
final class Config
{
    private static ?self $instance = null;

    private function __construct(private array $data = []) {}

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    private function __clone() {} // запрет копирования
}

$a = Config::getInstance();
$b = Config::getInstance();
var_dump($a === $b); // true - один и тот же объект

// В Laravel современная альтернатива:
// $this->app->singleton(Config::class);',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Что такое Factory (фабрика) простыми словами?',
                'answer' => '**Factory** — порождающий паттерн: создание объектов отдаётся отдельному классу или методу. Вместо `new ConcreteClass(...)` — `Factory::create($type)`.

**Зачем:**

1. **Логика создания сложная** — много параметров, валидация, выбор реализации.
2. **Конкретный класс выбирается по конфигу/параметру** в рантайме (`\'email\'` → `EmailNotifier`).
3. Удобно для **тестов** — фабрику можно подменить, чтобы создавала фейки.
4. Изолирует клиента от **конкретных классов** — соблюдён DIP.

**Варианты:**

- **Static factory method** — `Money::fromCents(100)` на самом классе.
- **Factory class** — отдельный класс `NotifierFactory::make($type)`.
- **Abstract Factory** — семейство фабрик для связанных продуктов.
- **Factory Method (GoF)** — подклассы решают, что создавать.

**Не путать с Laravel Factories** — это генераторы fake-данных для моделей в тестах, это про базу данных, не GoF.',
                'difficulty' => 2,
                'topic' => 'oop.gof_creational',
                'code_example' => '<?php
interface Notifier
{
    public function send(string $msg): void;
}

class EmailNotifier implements Notifier { public function send(string $m): void {} }
class SmsNotifier   implements Notifier { public function send(string $m): void {} }

class NotifierFactory
{
    public static function create(string $type): Notifier
    {
        return match ($type) {
            \'email\' => new EmailNotifier(),
            \'sms\'   => new SmsNotifier(),
            default  => throw new \InvalidArgumentException("Unknown: $type"),
        };
    }
}

$n = NotifierFactory::create(\'email\');
$n->send(\'hello\');',
                'code_language' => 'php',
            ],
        ];
    }
}
