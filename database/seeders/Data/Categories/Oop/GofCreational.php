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
                'answer' => 'Singleton (одиночка) гарантирует, что у класса есть только один экземпляр, и предоставляет глобальную точку доступа к нему. Подводные камни: 1) Глобальное состояние - усложняет тестирование, рождает скрытые зависимости. 2) Нарушает SRP (класс отвечает и за свою логику, и за свой жизненный цикл). 3) Сложно подменить мок в тестах. 4) Проблемы с многопоточностью (в PHP не критично, но в других языках). 5) Считается анти-паттерном; в современных приложениях вместо Singleton используют DI-контейнер с одним экземпляром (scope=singleton). PHP-специфика: чтобы по-настоящему "запечатать" синглтон, недостаточно закрыть __construct и __clone - нужно ещё заблокировать __wakeup() и __unserialize(). Иначе через unserialize("O:6:\\"Config\\":0:{}") можно создать второй экземпляр в обход конструктора (PHP реально позволяет это сделать, get_class вернёт Config, а === с оригиналом - false). Также стоит финализировать класс (final) от наследования, которое могло бы переоткрыть видимость.',
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
                'answer' => 'Сравнение методически некорректно — это ортогональные понятия: Factory отвечает на вопрос «как создать», DI — «кто принесёт». Factory знает, какие конкретные классы выбрать, какие параметры собрать, и инкапсулирует логику создания. Dependency Injection — это вообще не паттерн создания, а способ доставки уже готовой зависимости тому, кто её использует, через конструктор или сеттер. Они не взаимоисключающие: фабрика часто сама регистрируется как сервис в DI-контейнере и инжектится туда, где нужно создавать объекты по требованию (например, по типу из запроса).',
                'difficulty' => 3,
                'topic' => 'oop.gof_creational',
            ],
        ];
    }
}
