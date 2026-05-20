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
                'answer' => 'Singleton (одиночка) гарантирует один экземпляр класса и даёт глобальную точку доступа к нему. Реализация: приватный конструктор + статический метод getInstance(). Подводные камни: глобальное состояние мешает тестам и прячет зависимости, нарушает SRP. В PHP, чтобы запечатать singleton полностью, надо закрыть ещё __clone, __wakeup и __unserialize — иначе через unserialize можно получить второй экземпляр в обход конструктора. В современных приложениях вместо классического Singleton используют DI-контейнер со singleton-биндингом.',
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
                'answer' => 'Factory Method (фабричный метод) определяет интерфейс для создания объекта, но позволяет подклассам решать, какой класс инстанцировать. Используется, когда заранее не известно, какие именно объекты нужно создавать. Разделяет код-клиент и код-создание. Практический пример: в Laravel - метод make() контейнера, ResponseFactory.',
                'code_example' => '<?php
abstract class Logger
{
    abstract protected function createWriter(): Writer;

    public function log(string $msg): void
    {
        $this->createWriter()->write($msg);
    }
}

class FileLogger extends Logger
{
    protected function createWriter(): Writer
    {
        return new FileWriter();
    }
}

class DbLogger extends Logger
{
    protected function createWriter(): Writer
    {
        return new DbWriter();
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.gof_creational',
                'difficulty' => 4,
                'question' => 'Паттерн Abstract Factory',
                'answer' => 'Abstract Factory (абстрактная фабрика) предоставляет интерфейс для создания семейства связанных объектов, не указывая их конкретных классов. Отличие от Factory Method: одна фабрика создаёт несколько связанных продуктов. Пример: GUI-фабрика для разных ОС - WinFactory создаёт WinButton+WinWindow, MacFactory - MacButton+MacWindow, и они согласованы между собой.',
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
                'answer' => 'Builder (строитель) отделяет конструирование сложного объекта от его представления, позволяя одним и тем же кодом строить разные представления. Удобен, когда у объекта много опциональных параметров (телескопический конструктор). Часто реализуется через fluent interface (цепочки вызовов). Пример: построитель SQL-запроса в Laravel Query Builder.',
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
                'answer' => 'Prototype (прототип) позволяет копировать существующие объекты без зависимости от их конкретных классов. Применяют чаще для копирования состояния (например, пресета конфигурации с дальнейшей точечной правкой) и реже из-за стоимости конструктора. В PHP реализуется через ключевое слово clone и магический метод __clone() (для глубокого копирования вложенных объектов). По умолчанию clone делает поверхностную копию.',
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
                'answer' => 'Стандартный clone в PHP делает поверхностную копию: вложенные объекты остаются общими ссылками, а магический __clone, написанный наивно, просто рекурсивно клонирует вложенные сущности. Если в графе есть цикл (A ссылается на B, B на A), наивная рекурсия зациклится или продублирует объекты. Решение — вести карту "оригинал → копия" во время глубокого клонирования и возвращать уже созданную копию при повторной встрече, либо сериализовать через serialize/unserialize, что снимает цикличность за счёт собственного механизма ссылок.',
                'difficulty' => 4,
                'topic' => 'oop.gof_creational',
            ],
            [
                'category' => 'ООП',
                'question' => 'В чём разница между паттерном Factory и Dependency Injection?',
                'answer' => 'Понятия ортогональные. Factory отвечает на «КАК создать»: знает, какие конкретные классы выбрать (по конфигу, типу из запроса), как собрать параметры, инкапсулирует сложную логику конструирования. DI отвечает на «КТО ПРИНЕСЁТ»: способ ДОСТАВКИ уже готовой зависимости через конструктор/сеттер. Это разные оси, они сочетаются. Когда брать что: 1) Объект нужен ОДИН на запрос — DI, инжектим в конструктор. 2) Объект создаётся МНОГОКРАТНО или его тип определяется в рантайме (по полю запроса) — Factory. 3) Часто фабрика сама регистрируется как сервис и инжектится через DI туда, где нужно создавать объекты по требованию.',
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
