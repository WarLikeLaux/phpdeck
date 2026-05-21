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
                'answer' => '- **Класс** — шаблон, описание структуры данных (свойства) и поведения (методы)
- **Объект** — конкретный экземпляр класса, созданный через **`new`**

**Главный нюанс — объекты ведут себя как «ссылки»:**
- переменная хранит **object handle** (идентификатор объекта в памяти)
- при `$b = $a` или передаче в функцию **копируется только handle** — оба имени указывают на **один и тот же объект**
- изменения через `$b->name = ...` видны и через `$a`
- технически это **«pass-by-value of a reference»**, не настоящая ссылка — для неё нужен **`&`**

**Внутри методов:**
- **`$this`** — ссылка на текущий объект
- свойства всегда объявляются с **модификатором видимости**: `public` / `protected` / `private`',
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
                'answer' => '**Наследование** позволяет создать класс **на базе другого**, переиспользуя его свойства и методы.

**Ключевые правила в PHP:**
- ключевое слово **`extends`** — `class Dog extends Animal`
- только **одиночное** наследование — у класса **один родитель**
- наследуются **`public`** и **`protected`**, **`private` не виден** в потомке
- метод родителя вызывается через **`parent::method()`**
- **конструктор родителя НЕ вызывается автоматически** — нужно явно `parent::__construct(...)` в первой строке своего
- **`final`** на классе/методе **запрещает** дальнейшее наследование или переопределение

**Подводные камни:**
- наследование создаёт **сильную связанность** — лучше **композиция** там, где можно
- глубокие иерархии (3+ уровня) трудно поддерживать
- **LSP** (Liskov): потомок должен быть **полностью заменяем** на родителя без сюрпризов',
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
                'answer' => '**Интерфейс** — **контракт**: какие методы класс **обязан** реализовать, без указания **КАК** они работают.

**Базовые правила:**
- все методы интерфейса **`public`** и **без тела**
- класс подключает через **`implements`** и может реализовать **сразу несколько** интерфейсов
- интерфейс может **расширять** другие интерфейсы через `extends`

**Что внутри может быть:**
- **константы** (с PHP 8.1 — c модификатором **`final`**, с PHP 8.3 — **типизированные**)
- с PHP 8.4 — **виртуальные свойства** через **property hooks**

**Зачем нужен:**
- **полиморфизм** — функция принимает тип-интерфейс, работает с любой реализацией
- **моки** в тестах — подменить реальную реализацию на фейк
- принцип **Dependency Inversion** из SOLID — зависим от абстракции, не от конкретного класса
- **множественная реализация** компенсирует отсутствие множественного наследования',
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
                'answer' => '**Абстрактный класс** объявляется через **`abstract`** и **нельзя создать через `new`** — только унаследовать.

**Внутри может быть смесь:**
- **`abstract`-методы** без тела — наследник **обязан** реализовать
- обычные **реализованные** методы — общий код для всех наследников
- свойства с состоянием
- методы могут быть **любой видимости** (`public`/`protected`)

**Параллельное сравнение с интерфейсом:**

| | Abstract class | Interface |
| --- | --- | --- |
| Свойства/состояние | **да** | нет (до 8.4) |
| Готовая реализация методов | **да** | нет (только сигнатуры) |
| Модификаторы видимости | любые | только `public` |
| Сколько можно унаследовать/реализовать | **один** | **несколько** |
| Конструктор | можно | нет |

**Когда что брать:**
- **abstract class** — есть **общая логика и общие данные** у родственных классов (`Shape` → `Circle`/`Square`)
- **interface** — нужен **контракт**, без общей реализации; готовиться к нескольким **независимым** способам реализации',
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
                'answer' => '**Трейт** — механизм **горизонтального переиспользования** кода. Набор методов/свойств, который «подмешивается» в класс через **`use TraitName;`** и **копируется в него на этапе компиляции**.

**Чем не является:**
- **не тип** — нельзя инстанцировать через `new`
- **нельзя использовать как type-hint**
- не наследуется (нет `extends Trait`)

**Что может быть внутри:**
- свойства, методы (обычные и **`abstract`**)
- константы (с **PHP 8.2**)
- статические методы и свойства

**Конфликты имён** при `use A, B`:
- **`insteadof`** — выбрать, какой метод оставить
- **`as`** — переименовать или сменить видимость

**Где подходит:** cross-cutting concerns — **`Loggable`**, **`Timestampable`**, **`SoftDeletes`** (Laravel), **`Cacheable`**, **`HasUuids`**.

**Минусы:**
- скрытые зависимости — трейт может ожидать определённые свойства/методы у класса
- усложняет статанализ — реальный набор методов класса виден только после раскрытия `use`',
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
                'answer' => '**`static`** делает свойство или метод принадлежащим **классу**, а не экземпляру.

**Как обращаться:**
- снаружи: **`ClassName::method()`** или **`ClassName::$prop`**
- внутри класса: **`self::`** (этот же класс) или **`static::`** (учитывает наследование — **late static binding**)

**Особенности:**
- статический метод **не имеет `$this`**
- статическое свойство **разделяется** между всеми экземплярами
- инициализируется при первом обращении

**Типичные сценарии:**
- **фабричные методы** — `User::fromArray($data)`, `User::guest()`
- утилитарные **хелперы** без состояния
- **Singleton** (антипаттерн в большинстве случаев)
- **счётчики** созданных экземпляров

**Минусы статики:**
- **глобальное состояние** — статические свойства живут до конца запроса
- **трудно тестировать** — нельзя подменить через DI
- зависимости становятся **неявными** — класс «дёргает» `OtherClass::method()` без объявления

**Альтернатива:** обычные методы + DI-контейнер — лучше тестируется и моки тривиальны.',
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
                'answer' => '**Constructor property promotion (PHP 8.0+)** — сокращённый синтаксис: свойство объявляется **прямо в параметре конструктора** с модификатором видимости. Одна строка вместо трёх (объявить + передать + присвоить).

**Что можно комбинировать:**
- модификаторы **`public`** / **`protected`** / **`private`**
- **`readonly`** (PHP 8.1+) — для immutable-объектов
- **`final`** для promoted-свойств (PHP 8.5)
- **значения по умолчанию** в параметре
- **атрибуты** на параметре (`#[Assert\\NotBlank]` и т. п.)

**Когда НЕ работает:**
- в **`abstract __construct()`** интерфейсов/абстрактных классов без тела
- **`callable`** в типе promoted-параметра запрещён
- свойство **не должно быть объявлено** в классе ещё раз — иначе ошибка

**Подходит для:** DTO, value objects, services с обязательными зависимостями. Резко уменьшает boilerplate.',
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
                'answer' => '**Проблема:** до PHP 8.2 можно было присваивать **любое свойство** любому объекту, даже если оно не объявлено. Это маскировало **опечатки** вроде `$user->emial = "..."` — баг тихо живёт в коде.

**Что сделали в PHP 8.2:**
- создание **необъявленных свойств** помечено **deprecated**
- в **PHP 9.0** это станет **ошибкой**

**Решение для классов, которым нужны динамические свойства осознанно:**
- навесить атрибут **`#[AllowDynamicProperties]`** — класс снова разрешает писать любые свойства

**На что deprecation НЕ распространяется:**
- **`stdClass`** — он по определению «мешок свойств»
- классы, реализующие **`__get`** / **`__set`** — у них своя логика
- **анонимные** классы, наследники `stdClass`

**Когда атрибут оправдан:** легаси-«bag of data» классы, сериализация в `stdClass`-подобный объект, ORM-сущности с динамическими полями.',
                'code_example' => '<?php
// Без атрибута — Deprecated в 8.2, Error в 9.0
class User { public string $name; }

$u = new User();
$u->emial = "test@test.com"; // опечатка — Deprecated warning

// Осознанно разрешить динамику
#[AllowDynamicProperties]
class Bag {}

$b = new Bag();
$b->whatever = 42; // OK, без deprecation

// stdClass не требует атрибута
$o = new stdClass();
$o->any_property = "ok";',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое константы в трейтах в PHP 8.2?',
                'answer' => '**С PHP 8.2** trait может объявлять **константы** наравне со свойствами и методами.

**Ключевые правила:**
- **нет прямого обращения** `TraitName::CONST` — константа становится частью **использующего класса**
- доступна через **`ClassName::CONST`** или **`self::CONST`**/**`static::CONST`** внутри методов трейта
- работают модификаторы видимости и **`final`** (как у обычных констант класса)

**Конфликты при `use A, B`:**
- если оба трейта объявляют константу с **одинаковым именем**, их значения должны совпадать
- иначе — **fatal error**, как и со свойствами

**Зачем это нужно:**
- константы для **внутренней логики** трейта (статусы, лимиты) теперь живут рядом с кодом, который ими пользуется
- больше не нужно «выносить» их в отдельный класс или в каждый использующий класс',
                'code_example' => '<?php
trait HasStatuses {
    public const STATUS_NEW    = "new";
    public const STATUS_ACTIVE = "active";

    public function isActive(): bool {
        return $this->status === self::STATUS_ACTIVE;
    }
}

class Order {
    use HasStatuses;
    public string $status = self::STATUS_NEW;
}

echo Order::STATUS_NEW;   // "new" — через класс, НЕ через трейт
// echo HasStatuses::STATUS_NEW; // Fatal error — так нельзя',
                'code_language' => 'php',
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
                'answer' => '**Dependency Injection (DI)** — паттерн, при котором зависимости класса **передаются извне**, а не создаются внутри.

**Три формы:**
- **Constructor injection** — стандарт, зависимости в `__construct`
- **Setter injection** — для опциональных зависимостей или разрыва циклов
- **Property injection** — редко (через атрибут `#[Required]`)

**Что это даёт:**
- **легко тестировать** — в тестах подменяем зависимость **моком**
- **легко менять реализацию** — конкретный класс можно заменить, оставив интерфейс
- **зависимости явны** — все видны в сигнатуре конструктора
- работает **принцип Dependency Inversion** из SOLID

**DI-контейнер** (`Illuminate\\Container\\Container`, `Symfony\\Component\\DependencyInjection\\Container`) автоматизирует:
- создание объектов с **разрешением зависимостей по type-hint**
- управление **жизненным циклом** (singleton, scoped, transient)
- автосвязывание через **autowiring**

**Антипаттерн:** **Service Locator** — класс просит контейнер дать ему сервис вместо явного объявления зависимости в конструкторе; зависимости становятся снова неявными.',
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
                'answer' => '**SOLID — пять принципов проектирования классов:**

- **S — Single Responsibility:** у класса **одна причина** для изменения. `User` хранит данные, `UserRepository` — пишет в БД, `Mailer` — шлёт письма. Не `UserService` на 500 строк.
- **O — Open/Closed:** **открыт для расширения, закрыт для модификации**. Добавляем новый тип скидки — пишем новую реализацию `DiscountStrategy`, не правим существующий `switch`.
- **L — Liskov Substitution:** любой потомок должен быть **полностью взаимозаменяем** с родителем без сюрпризов. Если `Bird::fly()` бросает исключение в `Penguin` — LSP нарушен.
- **I — Interface Segregation:** **много мелких** интерфейсов лучше одного «толстого». `Readable`, `Writable`, `Closeable` вместо `FullFileInterface` с 20 методами, из которых класс реализует 3.
- **D — Dependency Inversion:** зависим от **абстракций (интерфейсов)**, а не от конкретных классов. `Order` принимает `PaymentGateway`, а не `StripeGateway` — потом подменим на `PayPalGateway` без правки `Order`.

**Главная идея:** все пять про **управление сложностью** и **локализацию изменений** — чтобы правка одного места не разламывала всё остальное.',
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
                'answer' => '**Встроенные SPL-интерфейсы** PHP, превращающие объект в **массивоподобный**.

**`ArrayAccess`** — обращение через **`[]`** работает с объектом. Реализуй четыре метода:
- **`offsetExists($offset): bool`** — для `isset($obj["key"])`
- **`offsetGet($offset): mixed`** — для `$obj["key"]`
- **`offsetSet($offset, $value): void`** — для `$obj["key"] = $value`
- **`offsetUnset($offset): void`** — для `unset($obj["key"])`

**`Countable`** — функция **`count($obj)`** работает. Реализуй один метод:
- **`count(): int`**

**Триада для полноценной коллекции:**
- **`ArrayAccess`** — доступ по ключу
- **`Countable`** — длина
- **`IteratorAggregate`** или **`Iterator`** — обход через `foreach`

**Где встречается:** **`Laravel Collection`**, **`Doctrine ArrayCollection`**, **`Symfony ParameterBag`** — все реализуют эту тройку и потому ведут себя как массив в коде, но при этом имеют **методы-помощники** (`map`, `filter`, `pluck`).',
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
                'answer' => '**`stdClass`** — встроенный пустой класс PHP, контейнер для произвольных свойств.

**Откуда чаще всего берётся:**
- `json_decode($json)` **без второго аргумента** возвращает объект `stdClass`
- каст массива через `(object) $arr` тоже даёт `stdClass`
- ручное `new stdClass()` и присваивание свойств

**Особенности:**
- нет своих методов и свойств
- свойства добавляются **динамически** — `$obj->name = "Иван"`
- **не путать** с `(object)`-кастом (это просто конструкция языка) и с `ArrayObject` (отдельный класс из SPL).

**Когда стоит использовать:** разовый bag для данных. Для бизнес-логики лучше типизированный класс — `stdClass` непрозрачен для IDE и статанализа.',
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
                'answer' => '**Два встроенных оператора и кастомный путь:**

| Оператор | Условие равенства |
| --- | --- |
| **`==`** (нестрогое) | **один класс** И **все свойства равны** (рекурсивно) |
| **`===`** (строгое) | **тот же экземпляр** (один объект в памяти) |
| **`<=>`** (spaceship) | для объектов сравнивает свойства; возвращает `-1`/`0`/`1` |

**Подводные камни:**
- **`==`** сравнивает свойства **рекурсивно** — на циклических ссылках можно нарваться на бесконечный обход (PHP это ловит, но на больших графах медленно)
- **`===`** не считает «равными» **два разных объекта** с одинаковыми данными
- сравнение **DateTime через `==`** работает, через `===` — нет

**Кастомное сравнение по бизнес-смыслу** — реализуй метод **`equals()`**:
- для **value objects** обычно достаточно сравнить поля
- для **entity** часто сравнивают только по **`id`**, а не по всем полям

**Не путать с `clone`** — это создание **нового объекта-копии**, к сравнению отношения не имеет.',
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
                'answer' => '**Получите `Error: Call to private ClassName::__construct()`** — снаружи `new` запрещён.

**Зачем так делают — два классических паттерна:**

**1. Именованные конструкторы (named constructors):**
- класс предоставляет **статические фабричные методы** с осмысленными именами: `User::fromArray($data)`, `User::guest()`, `Money::fromCents(100, "USD")`
- внутри они вызывают `new self(...)` — это **разрешено**, потому что вызов изнутри класса
- плюсы: **несколько способов создания** с понятными именами вместо одного перегруженного конструктора; инварианты гарантируются в одном месте

**2. Singleton:**
- статический метод **`getInstance()`** хранит единственный экземпляр в **`self::$instance`**
- `new` снаружи закрыт, остаётся только `Foo::getInstance()`
- в современном PHP **антипаттерн** — лучше DI-контейнер с биндингом `singleton`

**Бонус:** `private __clone()` и `__wakeup()` нужны, чтобы Singleton нельзя было обойти через `clone` или `unserialize`.',
                'code_example' => '<?php
final class Money {
    private function __construct(
        public readonly int $cents,
        public readonly string $currency,
    ) {}

    public static function fromCents(int $cents, string $currency): self {
        if ($cents < 0) throw new InvalidArgumentException();
        return new self($cents, $currency);
    }

    public static function zero(string $currency): self {
        return new self(0, $currency);
    }
}

$m = Money::fromCents(100, "USD");
// $m2 = new Money(100, "USD"); // Error: Call to private constructor',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как объявить класс в PHP и создать его объект?',
                'answer' => '- **Класс** — шаблон (описание свойств и методов).
- **Объект** — конкретный экземпляр класса.

**Объявление:** `class Name { ... }` со свойствами и методами.

**Создание объекта:** через ключевое слово **`new`** — `$u = new User()`.

**Доступ к свойствам и методам** — через стрелочку `->`: `$u->name`, `$u->greet()`.

**Конструктор** `public function __construct(...) {}` вызывается **автоматически** при `new` — обычно в нём принимают и сохраняют начальные данные. С PHP 8 удобна **constructor property promotion** — модификатор видимости прямо в параметре.',
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
                'answer' => '**`class Admin extends User {}`** — `Admin` получает все **`public`** и **`protected`** свойства и методы родителя. **`private` — НЕ наследуется.**

**Ключевые правила:**
- доступ к методу родителя — через **`parent::method()`**
- одноимённый метод в потомке — **override** (переопределение)
- **конструктор родителя НЕ вызывается автоматически** — нужно явно `parent::__construct(...)` в первой строке своего конструктора
- PHP поддерживает только **одиночное** наследование (один родитель)

**Если нужно поведение из нескольких источников:**
- **интерфейсы** — для контракта (можно реализовать несколько)
- **трейты** — для реализации (можно подключить несколько)

**Запретить дальнейшее наследование** — модификатор **`final`** на классе или на методе.',
                'code_example' => '<?php
class User {
    public function __construct(public string $name) {}
    public function role(): string { return "user"; }
}

class Admin extends User {
    public function __construct(string $name, public array $perms) {
        parent::__construct($name);
    }
    public function role(): string { return "admin"; } // override
}

$a = new Admin("Иван", ["edit"]);
echo $a->role(); // "admin"',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работает abstract class в PHP?',
                'answer' => '**Абстрактный класс** объявляется ключевым словом **`abstract`** и **нельзя создать через `new`** — только унаследовать.

**Внутри может быть смесь:**
- **`abstract`-методы** — без тела, наследник **ОБЯЗАН** реализовать (иначе fatal error)
- обычные **реализованные** методы — общий код, доступный всем наследникам
- **свойства** с состоянием

**Чем отличается от `interface`:**
- может иметь **состояние** (свойства) и **готовую реализацию** методов
- методы могут быть **любой видимости** (`public`/`protected`)
- наследовать можно **только один** abstract class (как и любой обычный класс)

**Когда брать abstract:** есть общая логика и общие данные у группы родственных классов (`Shape` → `Circle`, `Square`).',
                'code_example' => '<?php
abstract class Shape {
    public function __construct(public string $color) {}

    abstract public function area(): float;        // должен реализовать наследник

    public function describe(): string {           // общий код
        return "{$this->color}, S = {$this->area()}";
    }
}

class Circle extends Shape {
    public function __construct(string $color, public float $r) {
        parent::__construct($color);
    }
    public function area(): float { return M_PI * $this->r ** 2; }
}

// new Shape("red"); // Error: cannot instantiate abstract class',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работает interface в PHP?',
                'answer' => '**Интерфейс** описывает **контракт** — какие `public`-методы обязан иметь класс, **без указания КАК** они работают.

**Ключевые правила:**
- класс подключает через **`implements`** и может реализовать **СРАЗУ НЕСКОЛЬКО**: `implements A, B, C` — так PHP закрывает отсутствие множественного наследования
- все методы интерфейса **`public`** и **без тела**
- можно объявлять **константы** (с PHP 8.3 — с типом)
- интерфейс может **расширять** другие интерфейсы через `extends`

**Зачем нужен:**
- **полиморфизм** — функция принимает тип-интерфейс и работает с любой реализацией
- упрощает **моки** в тестах
- основа принципа **Dependency Inversion** из SOLID — зависим от абстракции, не от конкретного класса',
                'code_example' => '<?php
interface Sendable {
    public function send(string $to): void;
}

class EmailNotifier implements Sendable {
    public function send(string $to): void { /* SMTP */ }
}

class SmsNotifier implements Sendable {
    public function send(string $to): void { /* SMS API */ }
}

// Полиморфизм — функция принимает любую реализацию
function notify(Sendable $n, string $to): void {
    $n->send($to);
}
notify(new EmailNotifier(), "a@b.c");',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работает trait в PHP?',
                'answer' => '**Трейт** — набор методов и свойств, который «подмешивается» в класс через **`use TraitName;`**. Решает проблему отсутствия множественного наследования: подключил несколько трейтов — получил поведение из каждого.

**Ключевые свойства:**
- **не тип** — нельзя инстанцировать через `new` и нельзя использовать как type-hint
- может иметь свойства, методы (обычные и `abstract`), константы (PHP 8.2+)
- методы и свойства как бы **копируются в класс** при компиляции

**При конфликте имён** в нескольких трейтах:
- **`insteadof`** — какой именно метод оставить
- **`as`** — переименовать или сменить видимость

**Типичные сценарии:** cross-cutting concerns — `Loggable`, `Timestampable`, `Cacheable`, `HasUuids`.',
                'code_example' => '<?php
trait Timestampable {
    public ?int $createdAt = null;
    public function touch(): void { $this->createdAt ??= time(); }
}

trait Loggable {
    public function log(string $msg): void {
        echo "[" . static::class . "] $msg\n";
    }
}

class Post {
    use Timestampable, Loggable;
}

$p = new Post();
$p->touch();
$p->log("created");',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое static-метод и как его вызывать?',
                'answer' => '**Static-метод** принадлежит **классу**, а не его экземпляру.

**Объявляется** через `static`, **вызывается** через `::`: `User::generateId()` — без `new`.

**Особенности:**
- внутри static-метода **нет `$this`**
- есть `self::` (текущий класс) и `static::` (учитывает наследование — late static binding)

**Где применяют:**
- фабричные методы: `User::fromArray($data)`, `User::guest()`
- чистые утилиты без состояния (хелперы)

**Минус:** статика сложнее **тестируется и мокается**, чем обычные методы — её сложно подменить через DI.',
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
                'answer' => 'Встроенный API для **интроспекции кода в рантайме**: получить информацию о классах, методах, свойствах, параметрах, атрибутах — даже если они неизвестны заранее.

**Основные классы:**
- **`ReflectionClass`** — про класс целиком
- **`ReflectionMethod`** / **`ReflectionProperty`** / **`ReflectionParameter`**
- **`ReflectionAttribute`** (PHP 8+) — про атрибуты `#[...]`

**Где применяется на практике:**
- **DI-контейнер** Laravel разбирает type-hints в конструкторах для авто-инжекта
- **PHPUnit** находит методы `test*`
- **ORM** мапит колонки БД на свойства модели
- **Маршрутизаторы** читают атрибут `#[Route]`

**Минус:** медленнее прямых вызовов — обычно Reflection используют **один раз на старте** и **кэшируют** результат (например, скомпилированный DI-контейнер).',
                'code_example' => '<?php
class User {
    public function __construct(public string $name, private int $age) {}
    public function greet(): string { return "Hi, $this->name"; }
}

$r = new ReflectionClass(User::class);
echo $r->getName();                       // "User"

foreach ($r->getMethods() as $m) {
    echo $m->getName() . "\n";            // __construct, greet
}

$ctor = $r->getConstructor();
foreach ($ctor->getParameters() as $p) {
    echo $p->getName() . ": " . $p->getType() . "\n";
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое инкапсуляция в ООП простыми словами?',
                'answer' => '**Инкапсуляция** — принцип «**спрятать внутренности класса** и оставить наружу только нужный интерфейс».

**Как реализуется в PHP:**
- свойства делают **`private`** (или **`protected`**)
- доступ — через **`public`-методы**: либо геттеры/сеттеры, либо методы поведения (`deposit`, `withdraw` у `Account`)

**Что это даёт:**
- можно **менять внутреннее устройство** класса, не ломая код, который им пользуется
- **инварианты** (баланс не может стать отрицательным, email валиден) поддерживаются **в одном месте** — внутри класса
- защита от случайных мутаций «снаружи»',
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
                'answer' => '**Copy-on-Write (CoW)** — оптимизация рантайма PHP: при `$b = $a` или передаче массива в функцию **копия НЕ создаётся сразу**.

**Что происходит под капотом:**
- обе переменные указывают на **один `zval`** с `refcount = 2`
- копия создаётся **только при попытке изменения** (write) — отсюда и название
- если ни одна из сторон ничего не пишет, **копии не будет никогда**

**Что это даёт:**
- передача больших **массивов в функцию** — почти бесплатна по памяти
- `array_map`, `array_filter` с **read-only** обходом — без дублирования

**Для объектов CoW не нужен:**
- объекты в PHP передаются **по handle** (идентификатор объекта)
- `$b = $a` — копируется только handle; **оба имени указывают на один и тот же объект**
- изменения видны с обеих сторон без CoW

**Когда копия точно создастся:**
- `$arr[0] = "new"` после `$b = $a` — отделит `$b` от `$a`
- передача массива **по ссылке** `function f(array &$arr)` — наоборот, отключает CoW',
                'difficulty' => 3,
                'topic' => 'php.oop',
            ],
        ];
    }
}
