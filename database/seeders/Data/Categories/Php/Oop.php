<?php

namespace Database\Seeders\Data\Categories\Php;

class Oop
{
    public static function all(): array
    {
        return [
            [
                'category' => 'PHP',
                'question' => 'Что такое класс и объект в PHP?',
                'answer' => 'Класс - это шаблон, описание структуры данных и поведения (свойства и методы). Объект - конкретный экземпляр класса, созданный через new. Переменные хранят object id (handle); при присваивании/передаче в функцию копируется именно handle, оба имени указывают на один и тот же объект. Это «pass-by-value of a reference», а не настоящий pass-by-reference — для последнего нужен &. $this внутри метода - ссылка на текущий объект. Свойства объявляются с указанием видимости.',
                'code_example' => '<?php
class User {
    public string $name;
    private int $age;

    public function __construct(string $name, int $age) {
        $this->name = $name;
        $this->age = $age;
    }

    public function greet(): string {
        return "Привет, я {$this->name}";
    }
}

$user = new User("Иван", 30);
echo $user->greet();   // Привет, я Иван
echo $user->name;      // Иван (public)
// echo $user->age;    // Fatal error (private)

// Объект - по ссылке-идентификатору
$user2 = $user;
$user2->name = "Аня";
echo $user->name;      // Аня !',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое наследование и как использовать?',
                'answer' => 'Наследование позволяет создать класс на базе другого, переиспользуя его свойства и методы. Используется ключевое слово extends. PHP поддерживает только одиночное наследование (один родитель). Метод родителя можно вызвать через parent::method(). Конструктор родителя НЕ вызывается автоматически - нужно явно parent::__construct(). Для запрета переопределения используется final.',
                'code_example' => '<?php
class Animal {
    public function __construct(protected string $name) {}

    public function describe(): string {
        return "Я животное по имени {$this->name}";
    }
}

class Dog extends Animal {
    public function __construct(string $name, private string $breed) {
        parent::__construct($name);
    }

    public function describe(): string {
        return parent::describe() . " породы {$this->breed}";
    }
}

$dog = new Dog("Рекс", "лабрадор");
echo $dog->describe();
// "Я животное по имени Рекс породы лабрадор"

final class Cat extends Animal {} // нельзя наследовать дальше',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое интерфейс и зачем он нужен?',
                'answer' => 'Интерфейс - это контракт, описывающий, какие методы должен реализовать класс, без указания, КАК они работают. Все методы публичные и абстрактные. Класс может реализовать НЕСКОЛЬКО интерфейсов. Интерфейсы могут содержать константы (только public - private/protected допустимы только для констант КЛАССА с PHP 7.1, не интерфейса). С PHP 8.1 для констант (включая интерфейсные) появился модификатор final - запрет на переопределение в реализующем классе. С PHP 8.3 константы можно типизировать. С PHP 8.4 интерфейсы могут также объявлять виртуальные свойства через property hooks. Используется для полиморфизма, тестирования (моки), Dependency Inversion.',
                'code_example' => '<?php
interface Logger {
    public function log(string $message): void;
    public function error(string $message): void;
}

interface Formatter {
    public function format(string $message): string;
}

// Множественная реализация
class FileLogger implements Logger, Formatter {
    public function log(string $message): void {
        file_put_contents("log.txt", $this->format($message), FILE_APPEND);
    }
    public function error(string $message): void {
        $this->log("[ERROR] $message");
    }
    public function format(string $message): string {
        return "[" . date("Y-m-d") . "] $message\\n";
    }
}

function process(Logger $logger) {
    $logger->log("test"); // полиморфизм
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое абстрактный класс и чем отличается от интерфейса?',
                'answer' => 'Абстрактный класс - класс, объявленный с abstract, который нельзя инстанцировать напрямую. Может содержать как реализованные, так и абстрактные методы (без тела). От интерфейса отличается тем, что: может иметь свойства и реализованные методы, можно унаследовать только ОДИН абстрактный класс, методы могут быть с любой видимостью. Используется когда есть общая логика и общие данные у наследников.',
                'code_example' => '<?php
abstract class Shape {
    public function __construct(public string $color) {}

    // Абстрактный - наследник должен реализовать
    abstract public function area(): float;

    // Готовый метод
    public function describe(): string {
        return "Это {$this->color} фигура площадью {$this->area()}";
    }
}

class Circle extends Shape {
    public function __construct(string $color, private float $radius) {
        parent::__construct($color);
    }

    public function area(): float {
        return M_PI * $this->radius ** 2;
    }
}

// $s = new Shape("red"); // Fatal error
$c = new Circle("красная", 5);
echo $c->describe();',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое трейт (trait) и когда его использовать?',
                'answer' => 'Трейт - это механизм горизонтального переиспользования кода в PHP. Простыми словами: это набор методов и свойств, которые можно "впихнуть" в класс через use. Решает проблему отсутствия множественного наследования. Не является типом, нельзя инстанцировать. Если возникает конфликт имён - используется insteadof и as. Подходит для cross-cutting concerns: логирование, кэширование, soft deletes.',
                'code_example' => '<?php
trait Timestampable {
    private ?int $createdAt = null;
    private ?int $updatedAt = null;

    public function touch(): void {
        $this->updatedAt = time();
        $this->createdAt ??= time();
    }
}

trait Loggable {
    public function log(string $msg): void {
        echo "[" . static::class . "] $msg\\n";
    }
}

class Post {
    use Timestampable, Loggable;
}

$post = new Post();
$post->touch();
$post->log("created");

// Конфликт имён
trait A { public function hello() { echo "A"; } }
trait B { public function hello() { echo "B"; } }
class C {
    use A, B {
        A::hello insteadof B;
        B::hello as helloB;
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое статические свойства и методы?',
                'answer' => 'static - модификатор, делающий свойство или метод принадлежащим КЛАССУ, а не экземпляру. Доступ через ClassName::method() или self::method() / static::method() внутри класса. Статический метод не имеет $this. Статические свойства разделяются между всеми экземплярами. Часто применяется для фабричных методов, утилит, синглтонов. Минус: статика плохо тестируется и моделируется через DI.',
                'code_example' => '<?php
class Counter {
    private static int $count = 0;

    public static function increment(): int {
        return ++self::$count;
    }

    public static function reset(): void {
        self::$count = 0;
    }
}

echo Counter::increment(); // 1
echo Counter::increment(); // 2

// Фабричный метод
class User {
    public static function fromArray(array $data): self {
        return new self($data["name"], $data["age"]);
    }
    public function __construct(public string $name, public int $age) {}
}

$user = User::fromArray(["name" => "Иван", "age" => 30]);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое constructor property promotion в PHP 8?',
                'answer' => 'Constructor property promotion (PHP 8.0+) - это сокращённый синтаксис, позволяющий объявлять свойства класса прямо в параметрах конструктора через указание модификатора видимости. Простыми словами: одна строка вместо трёх (объявить, передать, присвоить). Можно комбинировать с readonly (PHP 8.1+) для immutable объектов.',
                'code_example' => '<?php
// Старый способ
class UserOld {
    private string $name;
    private int $age;

    public function __construct(string $name, int $age) {
        $this->name = $name;
        $this->age = $age;
    }
}

// PHP 8+
class User {
    public function __construct(
        private string $name,
        private int $age,
        public readonly string $email = "",
    ) {}
}

$user = new User("Иван", 30, "i@i.ru");
echo $user->email; // i@i.ru',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое property hooks в PHP 8.4?',
                'answer' => 'Property hooks позволяют задавать логику get и set прямо при объявлении свойства, без отдельных геттеров и сеттеров. Это устраняет шаблонный код и сохраняет естественный синтаксис обращения через стрелочную нотацию, при этом хуки могут вычислять значение или валидировать вход.',
                'difficulty' => 4,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое asymmetric visibility в PHP 8.4?',
                'answer' => 'Асимметричная видимость позволяет задать разные модификаторы для чтения и записи свойства, например public private(set). Снаружи такое свойство доступно только для чтения, а изменять его может только сам класс, что упрощает создание иммутабельных объектов без отдельного геттера.',
                'difficulty' => 4,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что разрешает PHP 8.3 делать с readonly-свойствами внутри __clone()?',
                'answer' => 'До 8.3 readonly-свойство нельзя было перезаписать даже в магическом __clone, поэтому глубокое клонирование объектов с readonly DateTime внутри было сломано. В 8.3 разрешена однократная переинициализация readonly-свойств именно в __clone — обычно для того, чтобы заменить вложенные мутабельные объекты на их клоны. Вне __clone правило неизменности по-прежнему действует.',
                'difficulty' => 4,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что даёт атрибут #[AllowDynamicProperties] и почему он понадобился в PHP 8.2?',
                'answer' => 'В 8.2 создание свойств, не объявленных в классе, помечено как deprecated и в 9.0 станет ошибкой — это закрывает класс опечаток вроде $user->emial = .... Чтобы класс мог сознательно остаться «магическим контейнером», на него вешают #[AllowDynamicProperties]. На stdClass и классы, реализующие __get/__set, deprecation не распространяется.',
                'difficulty' => 3,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое константы в трейтах в PHP 8.2?',
                'answer' => 'С PHP 8.2 trait может объявлять константы наравне со свойствами и методами. Прямого обращения через имя трейта нет — константа становится частью использующего класса и доступна через него или через self/static внутри методов трейта. При конфликте констант одинаковых трейтов, как и для свойств, нужно совпадение значений, иначе возникает фатальная ошибка.',
                'difficulty' => 3,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое clone with (clone-with-properties) в PHP 8.5?',
                'answer' => 'Синтаксис clone($obj, [\'status\' => 200, \'reason\' => \'OK\']) клонирует объект и одновременно перезаписывает указанные свойства. Особенно ценен для readonly- и immutable-классов вроде PSR-7 Response: раньше для withStatus() приходилось писать собственный конструктор копирования или использовать __clone. Запись в свойства внутри clone with разрешена даже для readonly при условии, что вызов происходит из scope, имеющего право на запись (для readonly — обычно изнутри класса или его методов вроде wither-а). Из глобального scope clone($obj, [\'x\'=>...]) для readonly выдаст Error.',
                'difficulty' => 4,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что даёт модификатор final у promoted-свойств в PHP 8.5?',
                'answer' => 'В 8.5 свойство, объявленное через constructor property promotion, можно пометить как final, например public final string $id. Это запрещает наследникам переопределять данное свойство (в сочетании с property hooks, которые в 8.4 ввели понятие переопределяемого свойства). Семантически близко к readonly, но фиксирует именно «не переопределяй в подклассе», а не «не пиши после инициализации».',
                'difficulty' => 4,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое Reflection в PHP?',
                'answer' => 'Reflection - API для интроспекции кода в рантайме: получить информацию о классах, методах, свойствах, параметрах. Простыми словами: код, который анализирует другой код. Используется фреймворками для DI-контейнеров, ORM, сериализаторов, тестов. Основные классы: ReflectionClass, ReflectionMethod, ReflectionProperty, ReflectionParameter, ReflectionAttribute (PHP 8). С PHP 8.1 setAccessible() стал deprecated/no-op — Reflection даёт доступ к private/protected свойствам и методам по умолчанию. Минус - медленнее прямых вызовов.',
                'code_example' => '<?php
class User {
    public function __construct(
        public string $name,
        private int $age,
    ) {}
    public function greet(): string { return "Hi, $this->name"; }
}

$ref = new ReflectionClass(User::class);
echo $ref->getName(); // "User"

foreach ($ref->getProperties() as $prop) {
    echo $prop->getName() . "\\n";
}

$ctor = $ref->getConstructor();
foreach ($ctor->getParameters() as $p) {
    echo $p->getName() . ": " . $p->getType() . "\\n";
}

// Создать через рефлексию
$user = $ref->newInstance("Иван", 30);

// Доступ к private
$ageProp = $ref->getProperty("age");
echo $ageProp->getValue($user);',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое late static binding и зачем нужен static вместо self?',
                'answer' => 'Late static binding (LSB) - механизм, когда static:: ссылается на класс, в котором был ВЫЗВАН метод, а не на тот, где он объявлен. self:: всегда ссылается на класс объявления. Простыми словами: static подстраивается под наследников, self - нет. Критично для фабричных методов в родительских классах: с static новые подклассы автоматически получают правильное поведение.',
                'code_example' => '<?php
class Model {
    public static function create(): self {
        return new self();   // всегда Model
    }
    public static function createStatic(): static {
        return new static(); // тот класс, что вызвал
    }
}

class User extends Model {}

$a = User::create();        // Model!
$b = User::createStatic();  // User

var_dump($a instanceof User); // false
var_dump($b instanceof User); // true',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое Dependency Injection в PHP?',
                'answer' => 'DI (внедрение зависимостей) - паттерн, когда зависимости класса передаются ему ИЗВНЕ (через конструктор/сеттер), а не создаются внутри. Простыми словами: класс не сам делает new Logger(), а получает готовый Logger через параметр. Плюсы: легче тестировать (подменить мок), легче менять реализации, явные зависимости. DI-контейнер автоматизирует создание объектов с зависимостями.',
                'code_example' => '<?php
// ПЛОХО - hard-coded зависимость
class UserService {
    private Logger $logger;
    public function __construct() {
        $this->logger = new FileLogger(); // нельзя подменить!
    }
}

// ХОРОШО - DI через конструктор
class UserService {
    public function __construct(
        private LoggerInterface $logger,
        private UserRepository $repo,
    ) {}

    public function create(string $name): User {
        $user = $this->repo->create($name);
        $this->logger->log("created $name");
        return $user;
    }
}

// В тестах легко подменить
$service = new UserService($mockLogger, $mockRepo);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Расскажи о принципах SOLID в PHP-контексте.',
                'answer' => 'S - Single Responsibility: один класс - одна причина для изменения. O - Open/Closed: класс открыт для расширения (через композицию, наследование, стратегию), закрыт для модификации. L - Liskov: подклассы должны быть взаимозаменяемы с родителем. I - Interface Segregation: лучше много мелких интерфейсов, чем один "толстый". D - Dependency Inversion: завись от абстракций (интерфейсов), не от конкретных классов. Все принципы про управление сложностью и переиспользование.',
                'code_example' => '<?php
// SRP - класс User не должен сам себя в БД сохранять
class User { /* данные */ }
class UserRepository {
    public function save(User $user): void {}
}

// OCP - расширяем через стратегию, не правим класс
interface Discount {
    public function calc(float $price): float;
}
class NewYearDiscount implements Discount {}
class BlackFridayDiscount implements Discount {}

// DIP - зависим от интерфейса
class Order {
    public function __construct(
        private PaymentGateway $gateway, // интерфейс!
    ) {}
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое Iterator и IteratorAggregate?',
                'answer' => 'Iterator - интерфейс, который надо реализовать чтобы объект работал в foreach. Методы: rewind, valid, current, key, next. IteratorAggregate проще - нужно только реализовать getIterator(), возвращающий любой Iterator (часто - ArrayIterator). Plus Generator: метод getIterator() может быть генератором (yield). Это делает обход коллекций ленивым и кастомным.',
                'code_example' => '<?php
// Через IteratorAggregate + Generator
class Collection implements IteratorAggregate {
    public function __construct(private array $items) {}

    public function getIterator(): Generator {
        foreach ($this->items as $key => $value) {
            yield $key => $value;
        }
    }
}

$c = new Collection(["a", "b", "c"]);
foreach ($c as $item) {
    echo $item;
}

// Полный Iterator
class Range implements Iterator {
    private int $current;
    public function __construct(private int $start, private int $end) {
        $this->current = $start;
    }
    public function rewind(): void { $this->current = $this->start; }
    public function valid(): bool { return $this->current <= $this->end; }
    public function current(): int { return $this->current; }
    public function key(): int { return $this->current - $this->start; }
    public function next(): void { $this->current++; }
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое ArrayAccess и Countable?',
                'answer' => 'ArrayAccess - интерфейс позволяющий обращаться с объектом как с массивом через []. Методы: offsetExists, offsetGet, offsetSet, offsetUnset. Countable - чтобы count($obj) работал, реализуй метод count(). Вместе с Iterator/IteratorAggregate позволяют создать класс-коллекцию, неотличимый от массива в использовании. Laravel Collection - яркий пример.',
                'code_example' => '<?php
class Bag implements ArrayAccess, Countable, IteratorAggregate {
    public function __construct(private array $items = []) {}

    public function offsetExists(mixed $offset): bool {
        return isset($this->items[$offset]);
    }
    public function offsetGet(mixed $offset): mixed {
        return $this->items[$offset] ?? null;
    }
    public function offsetSet(mixed $offset, mixed $value): void {
        if ($offset === null) $this->items[] = $value;
        else $this->items[$offset] = $value;
    }
    public function offsetUnset(mixed $offset): void {
        unset($this->items[$offset]);
    }
    public function count(): int {
        return count($this->items);
    }
    public function getIterator(): ArrayIterator {
        return new ArrayIterator($this->items);
    }
}

$bag = new Bag(["a", "b"]);
$bag[] = "c";
echo count($bag);   // 3
echo $bag[0];       // "a"',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое stdClass в PHP?',
                'answer' => 'stdClass - встроенный пустой класс PHP. Используется как контейнер для произвольных свойств. Когда json_decode без второго параметра возвращает объект - это stdClass. Также получается при касте массива в (object). Полей и методов своих нет, можно динамически добавлять любые свойства. Не путать с (object) или ArrayObject.',
                'code_example' => '<?php
// Создание
$obj = new stdClass();
$obj->name = "Иван";
$obj->age = 30;

// Из массива
$obj = (object) ["name" => "Иван", "age" => 30];
echo $obj->name;

// Из JSON
$obj = json_decode("{\\"name\\":\\"Иван\\"}");
echo $obj->name;

// Обратно в массив
$arr = (array) $obj;
print_r($arr); // ["name" => "Иван"]',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как сравнивать объекты в PHP?',
                'answer' => 'Оператор == (нестрогое): объекты равны если они одного класса и все свойства равны (рекурсивно). Оператор === (строгое): должны быть тот же экземпляр (один объект, не разные с одинаковыми свойствами). Для кастомного сравнения - реализуй метод equals() в классе. Не путать с clone - там создаётся новый объект.',
                'code_example' => '<?php
class Point {
    public function __construct(
        public int $x,
        public int $y,
    ) {}

    public function equals(Point $other): bool {
        return $this->x === $other->x && $this->y === $other->y;
    }
}

$a = new Point(1, 2);
$b = new Point(1, 2);
$c = $a;

var_dump($a == $b);   // true (поля равны)
var_dump($a === $b);  // false (разные экземпляры)
var_dump($a === $c);  // true (тот же экземпляр)

var_dump($a->equals($b)); // true',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Зачем нужны readonly-свойства и readonly-классы (PHP 8.2) и какие у них ограничения?',
                'answer' => 'readonly-свойство можно инициализировать один раз изнутри объявившего класса (обычно в конструкторе, но строго это "первая запись из scope класса", а не только из конструктора). После первой записи переписать его снаружи или из наследника нельзя - Error. readonly-класс (PHP 8.2+) делает все нестатические свойства readonly автоматически. Это даёт иммутабельные DTO/value objects без бойлерплейта геттеров. Ограничения: нельзя static-свойства, нельзя дефолтные значения у типизированных readonly-свойств. Про клонирование: до PHP 8.3 clone не позволял переписать readonly на копии, использовали wither (return new self(...)); с PHP 8.3 (RFC "readonly amendments") readonly-свойства можно reinitialize СТРОГО внутри тела магического метода __clone() того класса, где они объявлены - вне __clone() запись по-прежнему Error. Полезно это для глубокого клонирования вложенных readonly-объектов и сброса кешированного state на копии; для классических wither-ов new self(...) остаётся каноном.',
                'code_example' => '<?php
final readonly class Money {
    public function __construct(
        public int $amount,
        public string $currency,
    ) {}
}
$m = new Money(100, "USD");
// $m->amount = 200; // Error',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'В чём разница между WeakMap, WeakReference и SplObjectStorage?',
                'answer' => 'SplObjectStorage хранит сильные ссылки - объект-ключ не освободится, пока хранилище живёт. WeakReference (PHP 7.4) - обёртка, не препятствующая GC, get() вернёт null после уборки. WeakMap (PHP 8.0) - ассоциативный массив со слабыми ключами: при удалении объекта запись исчезает автоматически. Используется для кэшей и метаданных, привязанных к объекту, без утечек.',
                'code_example' => '<?php
$cache = new WeakMap();
$user = new stdClass();
$cache[$user] = "expensive_payload";
unset($user);             // запись из WeakMap уйдёт автоматически',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Приведи практический пример утечки памяти, которую решает WeakMap',
                'answer' => 'Классический сценарий - кеширование вычисленных метаданных по объекту в долгоживущем процессе (Octane, queue:work, ReactPHP). Например, EventDispatcher запоминает прав доступа для каждого Request/User, чтобы не ходить в БД повторно при каждом fired event. Если кеш - обычный array со spl_object_id($user) или SplObjectStorage в качестве ключа, то ссылка на $user в кеше СИЛЬНАЯ: даже когда обработчик запроса завершён и нигде в коде $user больше не нужен, refcount остаётся > 0 - объект не освобождается, и через 100k запросов память кончается. С WeakMap ключ - слабая ссылка: как только закончился запрос и кончились сильные ссылки на $user, GC уничтожит и объект, и автоматически уберёт запись из WeakMap. Это правильный инструмент для "side-table" данных: метаданных, прав, ленивых вычислений, observer-паттерна (слушатели не должны мешать GC своих субъектов). Аналогичная проблема в JS: WeakMap используется для приватных полей и DOM-метаданных по той же причине.',
                'code_example' => '<?php
// ❌ УТЕЧКА в long-running процессе
class PermissionCacheBad
{
    private array $cache = []; // массив с object_id ключами

    public function for(User $user): array
    {
        $id = spl_object_id($user);
        return $this->cache[$id] ??= $this->compute($user);
        // ⚠️ $this->compute($user) может содержать $user
        // или ссылки на него - сильная ссылка остаётся в $cache
    }
}

// после 100k запросов:
// memory_get_usage() = 1 GB, OOM

// ✅ Без утечки благодаря WeakMap
class PermissionCacheGood
{
    private WeakMap $cache;

    public function __construct() { $this->cache = new WeakMap(); }

    public function for(User $user): array
    {
        return $this->cache[$user] ??= $this->compute($user);
    }
}

// $user из текущего запроса попадает в WeakMap;
// когда контроллер вернул response и $user вышел из scope,
// GC удаляет объект И запись из WeakMap - память стабильна.

// Реальный кейс: Symfony EventDispatcher, Doctrine UnitOfWork,
// Laravel Octane кешируют метаданные именно через WeakMap',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что произойдёт при new ClassName(...) для класса с конструктором, объявленным как private?',
                'answer' => 'Получите Error: Call to private ClassName::__construct(). Такой паттерн используется для именованных конструкторов и Singleton: класс предоставляет статические фабричные методы (fromArray, fromString), которые внутри вызывают new self(). Это позволяет инкапсулировать инвариант построения и иметь несколько способов создания с осмысленными именами.',
                'difficulty' => 3,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как объявить класс в PHP и создать его объект?',
                'answer' => 'Класс — шаблон, объект — конкретный экземпляр. Объявляется через class Name { ... } со свойствами и методами. Создание объекта — через ключевое слово new: $u = new User(). Доступ к свойствам и методам — через стрелочку ->: $u->name. Конструктор public function __construct(...) {} вызывается автоматически при new — обычно в нём принимают и сохраняют начальные данные.',
                'code_example' => '<?php
class User {
    public function __construct(
        public string $name,
        public int $age,
    ) {}

    public function greet(): string {
        return "Привет, я {$this->name}";
    }
}

$u = new User("Иван", 30);
echo $u->name;     // "Иван"
echo $u->greet();  // "Привет, я Иван"',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работает наследование в PHP (extends)?',
                'answer' => 'class Admin extends User {} — Admin получает все public/protected свойства и методы User. Доступ к родительскому методу через parent::method(). Если переопределяешь метод родителя — это override. PHP поддерживает только одиночное наследование (один родитель), множественное — только через интерфейсы или трейты.',
                'difficulty' => 2,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работает abstract class в PHP?',
                'answer' => 'abstract class Animal { abstract public function makeSound(): string; }. Сам класс нельзя создать через new — только наследоваться. abstract-метод не имеет тела, наследник обязан его реализовать. Может содержать обычные методы и свойства — общая основа для группы классов.',
                'difficulty' => 2,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работает interface в PHP?',
                'answer' => 'interface Sendable { public function send(): void; }. Описывает «контракт» — какие методы должен иметь класс. Класс реализует через implements: class Email implements Sendable {}. Можно реализовывать несколько интерфейсов: implements A, B, C. Все методы интерфейса должны быть public.',
                'difficulty' => 2,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работает trait в PHP?',
                'answer' => 'trait Loggable { public function log($msg) {...} }. Подключается в класс через use Loggable; — методы трейта становятся методами класса. Решает проблему отсутствия множественного наследования: можно подмешать поведение из нескольких трейтов в один класс.',
                'difficulty' => 2,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое static-метод и как его вызывать?',
                'answer' => 'Метод, принадлежащий КЛАССУ, а не его экземпляру. Объявляется ключевым словом static и вызывается через ::: User::generateId() — без new. Внутри static-метода нет $this, есть только self:: и static:: (последний учитывает наследование — late static binding). Часто используется для фабричных методов (User::fromArray($data)) и чистых утилит без состояния. Минус: статика сложнее тестируется и мокается, чем обычные методы.',
                'code_example' => '<?php
class IdGenerator {
    private static int $last = 0;

    public static function next(): int {
        return ++self::$last;
    }
}

echo IdGenerator::next(); // 1
echo IdGenerator::next(); // 2

// Фабричный метод
class User {
    public function __construct(public string $name) {}

    public static function guest(): self {
        return new self("Гость");
    }
}
$u = User::guest();',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое Reflection API в PHP простыми словами?',
                'answer' => 'Встроенный API, позволяющий В РАНТАЙМЕ изучать классы, методы, свойства, параметры — даже если ты не знаешь их заранее. new ReflectionClass(User::class) — получить инфу о классе; ->getMethods() — список методов; ->getProperty("name") — конкретное свойство. Применяется фреймворками: Laravel так разбирает type-hints в конструкторах для авто-инжекта, PHPUnit — чтобы находить test-методы, ORM — для маппинга колонок на свойства.',
                'difficulty' => 2,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое инкапсуляция в ООП простыми словами?',
                'answer' => 'Инкапсуляция — это принцип «спрятать внутренности класса и оставить наружу только нужный интерфейс». Свойства делают private (или protected), а доступ к ним организуют через публичные методы (геттеры/сеттеры) или специальные методы поведения (deposit/withdraw у Account). Это позволяет менять внутреннее устройство класса, не ломая код, который им пользуется, и поддерживать инварианты (например, баланс не может стать отрицательным) в одном месте.',
                'code_example' => 'class BankAccount {
    private int $balance = 0;

    public function deposit(int $amount): void {
        if ($amount <= 0) throw new InvalidArgumentException();
        $this->balance += $amount;
    }

    public function getBalance(): int {
        return $this->balance;
    }
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое Copy-on-Write в PHP?',
                'answer' => 'Оптимизация: при передаче переменной в функцию или присваивании $b = $a, PHP НЕ копирует значение сразу. Обе переменные указывают на один и тот же zval с refcount=2. Копия создаётся ТОЛЬКО при попытке изменения одной из них (write). Это экономит память и время для больших массивов. Поэтому function f(array $arr) {} в 99% случаев не делает копию — она появится, только если внутри $arr[0] = "new". Для объектов CoW не нужен — они и так передаются по handle (как ссылка на объект).',
                'difficulty' => 3,
                'topic' => 'php.oop',
            ],
        ];
    }
}
