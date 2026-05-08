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
                'question' => 'Какие модификаторы видимости есть в PHP?',
                'answer' => 'public - доступно отовсюду. protected - доступно из самого класса и наследников. private - только из самого класса (не из наследников!). PHP 7.1 - private/protected константы класса. PHP 8.1 - модификатор final для констант (запрет переопределения в наследниках; в интерфейсах константы по-прежнему только public). В PHP 8.4 (вышел в ноябре 2024) появились две крупные фичи для свойств: 1) asymmetric visibility - раздельная видимость на чтение и запись (public private(set) int $id - читать всем, писать только внутри класса), убирает необходимость в паре приватного поля + публичного геттера. 2) Property hooks - встроенный get/set прямо в свойстве (public string $email { get => strtolower($this->email); set => trim($value); }), без перехода на отдельные методы и __get/__set. Хорошая практика: по умолчанию private, повышать видимость только по необходимости.',
                'code_example' => '<?php
class Animal {
    public string $name;
    protected int $age;
    private string $secret = "shh";

    private function privateMethod() {}
    protected function protectedMethod() {}
}

class Dog extends Animal {
    public function showAge() {
        return $this->age;     // OK (protected)
        // return $this->secret; // Error (private)
    }
}

$dog = new Dog();
echo $dog->name;     // OK
// echo $dog->age;   // Error
// echo $dog->secret;// Error',
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
        return "[" . date("Y-m-d") . "] $message\n";
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
        echo "[" . static::class . "] $msg\n";
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
        ];
    }
}
