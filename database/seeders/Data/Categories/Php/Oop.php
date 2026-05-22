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
                'answer' => '**Property hooks (PHP 8.4+)** — встроенные `get`/`set`-хуки прямо при объявлении свойства. Убирают шаблонный геттер/сеттер, **сохраняя естественный синтаксис `$user->name`**.

**Что это даёт:**
- **виртуальное** свойство — значение вычисляется на лету (нет хранения)
- **валидация / нормализация** на запись без отдельного `set`-метода
- **переопределение в наследнике** — раньше требовало переопределения метода
- **в интерфейсах** теперь можно объявить **abstract property**: `public string $name { get; set; }`

**Два вида хуков:**

| Хук | Когда вызывается | Особенности |
| --- | --- | --- |
| **`get`** | при чтении `$obj->name` | можно `=> expression` (short form) либо блок с `return` |
| **`set`** | при записи `$obj->name = $v` | параметр `$value` или `set(Type $v)` для type-narrowing |

**Подкапотные тонкости:**
- хук не вызывается **изнутри своего же хука** — иначе бесконечная рекурсия; для доступа к «сырому» значению есть `$this->name::raw` или backing field
- `readonly` + `set` несовместимы; **`asymmetric visibility`** (`public private(set)`) — отдельная фича, но хорошо комбинируется
- хуки **наследуются и переопределяются** — `parent::$name::get()` зовёт родительский
- работают в **constructor property promotion**',
                'code_example' => '<?php
class User {
    private string $first;
    private string $last;

    // Виртуальное свойство — не хранится
    public string $fullName {
        get => "$this->first $this->last";
        set(string $v) {
            [$this->first, $this->last] = explode(" ", $v, 2);
        }
    }

    // Нормализация на запись + валидация
    public string $email {
        set(string $v) {
            $v = strtolower(trim($v));
            if (!filter_var($v, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException("bad email");
            }
            $this->email = $v;   // backing field — запись «как есть»
        }
    }

    public function __construct(string $first, string $last) {
        $this->first = $first;
        $this->last  = $last;
    }
}

$u = new User("Иван", "Петров");
echo $u->fullName;            // "Иван Петров" — get-хук
$u->fullName = "Аня Сидорова";// set-хук разрезал
$u->email = "  X@Y.RU ";      // нормализация → "x@y.ru"',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое asymmetric visibility в PHP 8.4?',
                'answer' => '**Asymmetric visibility (PHP 8.4+)** — разная видимость для **чтения** и **записи** свойства.

**Синтаксис:** `public private(set) string $id;`
- `public` — **видимость чтения**
- `private(set)` — **видимость записи** (только сам класс)

**Допустимые комбинации:**

| Объявление | Read | Write |
| --- | --- | --- |
| **`public private(set)`** | везде | только свой класс |
| **`public protected(set)`** | везде | свой класс + наследники |
| **`protected private(set)`** | свой класс + наследники | только свой класс |

**Правило:** видимость записи **не может быть шире** видимости чтения. **`private public(set)`** — синтаксическая ошибка.

**Чем отличается от `readonly`:**

| | `readonly` | `public private(set)` |
| --- | --- | --- |
| Запись изнутри класса | **один раз** | **сколько угодно** |
| Запись из наследника | **нет** (даже у protected) | возможна (`protected(set)`) |
| Чтение снаружи | да | да |

**Когда брать что:**
- **readonly** — value-object, инициализированный один раз в конструкторе
- **asymmetric visibility** — entity, у которой состояние **меняется внутри**, но снаружи **только читается** (счётчики, статусы, поля, обновляемые через методы)

**Работает с promoted-параметрами** в конструкторе и **сочетается с property hooks**.',
                'code_example' => '<?php
final class Order {
    public function __construct(
        public private(set) string $id,
        public protected(set) string $status = "new",
    ) {}

    public function pay(): void {
        $this->status = "paid";        // ✅ внутри своего класса
    }
}

class PriorityOrder extends Order {
    public function expedite(): void {
        $this->status = "rushed";      // ✅ наследник может (protected(set))
        // $this->id = "X";            // ❌ Error — private(set)
    }
}

$o = new Order("ord_1");
echo $o->status;                       // "new" — read OK
// $o->status = "hacked";              // ❌ Error: protected(set)
$o->pay();
echo $o->status;                       // "paid"',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что разрешает PHP 8.3 делать с readonly-свойствами внутри __clone()?',
                'answer' => '**Проблема до PHP 8.3:**
- `readonly`-свойство нельзя было **перезаписать** даже в магическом `__clone()`
- **глубокое клонирование** объектов с вложенными `readonly`-полями было **сломано**
- приходилось писать **wither-методы** (`return new self(...)`) — много boilerplate

**Что разрешили в PHP 8.3 (RFC «readonly amendments»):**
- внутри `__clone()` того класса, **где свойство объявлено**, разрешена **однократная переинициализация** `readonly`-свойств
- вне `__clone()` правило неизменности **по-прежнему действует** — запись снаружи → `Error`

**Зачем это нужно — типичный сценарий:**
1. У вас `readonly`-агрегат с вложенными `readonly`-объектами
2. При **`clone $aggregate`** PHP делает **поверхностную копию** — вложенные объекты остаются те же
3. Если внутренний объект **должен быть свежей копией** — `__clone()` теперь может выполнить `$this->inner = clone $this->inner;`

**Подкапотные правила:**
- разрешена **запись только в `readonly`-свойство своего класса** — у унаследованных всё ещё `Error`
- `__clone()` вызывается **после** копирования полей, видит уже скопированные значения
- объекты, которые сами реализуют свой `__clone()`, получают согласованную deep-copy через каскад

**До 8.3 — wither-паттерн** для immutable-обновления остаётся каноном и в 8.3+ для тех случаев, когда нужно создать модифицированную копию **снаружи** объекта.',
                'code_example' => '<?php
final class OrderSnapshot {
    public function __construct(
        public readonly string $id,
        public readonly DateTimeImmutable $createdAt,
        public readonly Money $total,
    ) {}

    // PHP 8.3+: deep-clone вложенных объектов
    public function __clone(): void {
        // readonly-поле своего класса можно один раз переписать в __clone()
        $this->total = clone $this->total;
        // $this->createdAt уже immutable — клонировать не обязательно
    }
}

$a = new OrderSnapshot("ord_1", new DateTimeImmutable(), new Money(100, "USD"));
$b = clone $a;
// $b->total — новый Money-объект, не тот же, что у $a
// $b->id  = "X";   // ❌ Error: cannot modify readonly snaружи __clone
// Wither-паттерн остаётся канонным для модификации снаружи:
// public function withTotal(Money $m): self { return new self($this->id, $this->createdAt, $m); }',
                'code_language' => 'php',
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
                'answer' => '**Clone-with-properties (PHP 8.5+)** — синтаксис **`clone($obj, [\'prop\' => $value])`** клонирует объект **и одновременно перезаписывает** указанные свойства.

**Зачем нужно — типичная задача:**
- **PSR-7 Response** и подобные immutable-объекты: метод `withStatus(int $code)` должен вернуть новую копию с обновлённым статусом
- раньше для этого приходилось писать **собственный wither**: `$new = clone $this; $new->status = $code; return $new;` — boilerplate

**Что делает синтаксис:**
1. вызывает обычный `clone` (включая `__clone()`)
2. применяет перезапись свойств **после** `__clone()` — атомарно для вызывающего
3. возвращает уже модифицированный новый объект

**Правила scope для записи:**

| Где вызвали `clone($obj, [...])` | readonly-свойство | обычное свойство |
| --- | --- | --- |
| Из метода **того же класса** | **разрешено** | разрешено |
| Из **наследника** | `Error` | зависит от видимости |
| Снаружи (глобально) | **`Error`** для readonly | зависит от видимости |

**Главное правило:** **scope записи в `clone($obj, [...])` определяется по правилам обычного присваивания** в этом месте кода. Для `public readonly` свойств снаружи всё равно нельзя — нужно остаться внутри класса или его wither-метода.

**Что выигрываем:** wither-методы становятся **одной строкой**, без отдельного клона + присваивания.',
                'code_example' => '<?php
final class HttpResponse {
    public function __construct(
        public readonly int $status,
        public readonly string $reason,
        public readonly array $headers = [],
        public readonly string $body = "",
    ) {}

    // PHP 8.5: wither в одну строку
    public function withStatus(int $code, string $reason): self {
        return clone($this, ["status" => $code, "reason" => $reason]);
    }

    public function withHeader(string $name, string $value): self {
        return clone($this, ["headers" => [...$this->headers, $name => $value]]);
    }
}

$r1 = new HttpResponse(200, "OK");
$r2 = $r1->withStatus(404, "Not Found");
echo $r1->status;          // 200 — оригинал не тронут
echo $r2->status;          // 404

// Снаружи для readonly — Error
// $r3 = clone($r1, ["status" => 500]);   // Error: cannot modify readonly',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что даёт модификатор final у promoted-свойств в PHP 8.5?',
                'answer' => '**`final` для promoted-свойств (PHP 8.5+)** — запрещает **наследникам** переопределять конкретное свойство.

**Зачем это вообще появилось:** в PHP 8.4 property hooks ввели понятие **переопределяемого свойства** — наследник может перекрыть `get`/`set` родителя. До 8.5 не было способа сказать «я объявил это поле — оно итоговое, не трогайте».

**Что `final` фиксирует:**
- запрещает **переопределение свойства** в подклассе (как `final` у методов)
- наследник, попытавшийся объявить такое же свойство → **`Error: Cannot override final property`**

**Чем отличается от `readonly`:**

| | `readonly` | `final` |
| --- | --- | --- |
| Запрещает | **запись после инициализации** | **переопределение в наследнике** |
| Время проверки | runtime | compile-time |
| Можно вместе? | **да**: `public final readonly string $id` |
| Влияет на hooks? | нет | **да** — наследник не сможет перекрыть |

**Когда брать `final`:**
- библиотечные базовые классы — гарантировать, что **критичный property не будет перекрыт** в чужом коде
- свойства с **property hooks**, поведение которых важно зафиксировать
- защита **invariant-ов** при наследовании

**Где работает:**
- promoted-параметры: `public final string $id`
- обычные объявления свойств в классе
- **не на интерфейсах** (там свойство абстрактное по определению)',
                'code_example' => '<?php
class Entity {
    public function __construct(
        public final readonly string $id,    // PHP 8.5: final + readonly
    ) {}
}

class User extends Entity {
    // ❌ Error: Cannot override final property Entity::$id
    // public string $id = "X";
}

// Использование с property hooks — наследник не может перекрыть get
class Money {
    public final string $formatted {
        get => number_format($this->amount, 2) . " " . $this->currency;
    }

    public function __construct(
        public readonly float $amount,
        public readonly string $currency,
    ) {}
}

class Btc extends Money {
    // ❌ Error: final property не переопределяется
    // public string $formatted { get => ...; }
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое Reflection в PHP?',
                'answer' => '**Reflection** — API для **интроспекции кода в рантайме**: получить информацию о классах, методах, свойствах, параметрах, атрибутах.

**Основные классы:**

| Класс | Что описывает |
| --- | --- |
| **`ReflectionClass`** | класс целиком |
| **`ReflectionMethod`** | метод |
| **`ReflectionProperty`** | свойство |
| **`ReflectionParameter`** | параметр функции/метода |
| **`ReflectionAttribute`** (PHP 8+) | атрибут `#[...]` |
| **`ReflectionEnum`** / **`ReflectionEnumCase`** | enum и его кейсы |
| **`ReflectionIntersectionType`** / **`ReflectionUnionType`** | составные типы |

**Где реально применяется:**
- **DI-контейнер** Laravel разбирает type-hints конструкторов для **autowiring**
- **PHPUnit** ищет методы с префиксом `test*` или с `#[Test]`
- **ORM** (Eloquent, Doctrine) мапит колонки БД на свойства
- **сериализаторы / валидаторы** читают атрибуты (`#[Assert\\NotBlank]`)
- **роутеры** (`#[Route]`)

**Доступ к приватным членам:**
- **PHP < 8.1** — нужно было `setAccessible(true)`
- **PHP 8.1+** — `setAccessible()` стал **deprecated / no-op**; Reflection видит `private`/`protected` **по умолчанию**

**Создание объекта:**
- **`newInstance($args...)`** — обычные аргументы
- **`newInstanceArgs([...])`** — массивом
- **`newInstanceWithoutConstructor()`** — **в обход** конструктора (нужно ORM для гидратации из БД)

**Минусы:**
- **медленнее** прямых вызовов (parsing метаданных)
- паттерн: использовать **один раз на старте** + **кэшировать** результат (compiled DI container, hydrator-pool)
- **JIT не оптимизирует** код, написанный через Reflection',
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
                'answer' => '**Late static binding (LSB)** — механизм, когда **`static::`** ссылается на класс, в котором метод был **ВЫЗВАН**, а не на тот, где он **объявлен**.

**Сравнение `self::` vs `static::`:**

| | `self::` | `static::` |
| --- | --- | --- |
| Когда резолвится | **compile-time** (early binding) | **runtime** (late binding) |
| Указывает на | класс, где **написан** код | класс, где **вызван** метод |
| Учитывает наследование | **нет** | **да** |
| Применимо к | методам, свойствам, константам, `new` | то же самое |

**Где имеет значение:**

**1. Фабричные методы в базовом классе:**
- `public static function create(): static` — с LSB наследники **автоматически** возвращают свой тип
- классический `: self` — всегда базовый, наследник придётся переопределять

**2. Eloquent / Active Record:**
- `Model::query()`, `User::find(1)` работают через LSB — статический метод в `Model`, но `static::` указывает на `User`

**3. Тип возврата `: static`** (PHP 8.0+):
- compile-time гарантирует, что fluent-методы (`->save()`, `->refresh()`) возвращают **именно класс, у которого их вызвали**
- IDE/статанализ знают точный тип

**Подкапотные правила:**
- LSB активируется при вызове через **`static::`**, **`new static()`** или с возврат-типом **`: static`**
- внутри **статического метода** `$this` нет — LSB работает через **служебный stack frame** Zend Engine
- при `forward_static_call($cb)` LSB **переносится** в вызываемую функцию; обычный вызов сбрасывает',
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
                'answer' => '**Два SPL-интерфейса** для того, чтобы объект работал в **`foreach`**.

**`Iterator`** — низкоуровневый, нужно реализовать **5 методов** строгого протокола:

| Метод | Когда вызывается | Что должен делать |
| --- | --- | --- |
| **`rewind(): void`** | при входе в `foreach` | переставить внутренний курсор в начало |
| **`valid(): bool`** | перед каждой итерацией | есть ли ещё элементы |
| **`current(): mixed`** | получение значения | вернуть текущее значение |
| **`key(): mixed`** | если используется `$k => $v` | вернуть текущий ключ |
| **`next(): void`** | в конце итерации | продвинуть курсор |

**`IteratorAggregate`** — проще, **один метод**:
- **`getIterator(): Iterator`** возвращает **любой** `Iterator` (часто **`ArrayIterator`**)
- бонус: метод может быть **генератором** (`yield`) — компилятор сам обернёт его в `Generator`, который реализует `Iterator`

**Что выбирать:**

| Случай | Берите |
| --- | --- |
| Хочется **переиспользовать** готовый итератор (массив, генератор) | `IteratorAggregate` |
| Нужен **сложный внутренний курсор** с состоянием | `Iterator` |
| **Бесконечная** или ленивая последовательность | `IteratorAggregate` + `Generator` |

**Подкапотные нюансы:**
- `Iterator` **не может быть пройден дважды** без правильно реализованного `rewind`
- `Generator` — одноразовый; для повторного обхода нужно создать заново
- **`Traversable`** — родительский интерфейс **обоих**; type-hint `iterable` принимает `array | Traversable`
- порядок вызовов в `foreach`: `rewind` → `valid` → (`current` + `key`) → тело → `next` → `valid` → ...',
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
                'answer' => '**`readonly` свойство (PHP 8.1+)** — можно записать **ровно один раз** из **scope объявившего класса**.

**Точные правила записи:**
- запись разрешена **из любого метода своего класса** — **не только из конструктора** (важный нюанс)
- после **первой** записи любая последующая → **`Error: Cannot modify readonly property`**
- из **наследника** записать нельзя, **даже если** свойство `protected readonly`

**`readonly` класс (PHP 8.2+)** — `final readonly class Foo`:
- автоматически **все нестатические** свойства становятся `readonly`
- даёт immutable DTO / value object **без boilerplate**

**Ограничения:**
- **нельзя** на `static`-свойствах
- **нельзя** с дефолтным значением у типизированного свойства (как поля; promoted с дефолтом — можно)
- **нельзя** untyped `readonly` (тип **обязателен**)
- **нельзя** в трейтах напрямую (трейт **объявляющий** readonly-поле — `Fatal error`, надо в самом классе)

**Эволюция clone:**

| Версия | Что можно с readonly при `clone` |
| --- | --- |
| **PHP 8.1** | ничего — wither (`return new self(...)`) **обязателен** |
| **PHP 8.3** | **reinitialize** разрешено **только внутри `__clone()`** объявившего класса (RFC «readonly amendments») |
| **PHP 8.5** | синтаксис **`clone($obj, [...])`** + scope-правила |

**Подкапотный нюанс:**
- `readonly` **не делает глубокую неизменность**: внутри `readonly` поле-объект, его собственные **свойства** менять можно (если они не readonly)
- глубокая immutability = `readonly class` **плюс** value-objects во всех полях',
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
                'answer' => '**Три инструмента для хранения «по объекту»** — с принципиально разной семантикой ссылок.

| Структура | Версия | Тип ссылки на ключ | Освобождается? |
| --- | --- | --- | --- |
| **`SplObjectStorage`** | PHP 5.3 | **сильная** | нет, пока хранилище живо |
| **`WeakReference`** | PHP 7.4 | **слабая** (обёртка) | да — `get()` вернёт `null` |
| **`WeakMap`** | PHP 8.0 | **слабые ключи** | да — запись **автоматически** уходит |

**`SplObjectStorage`:**
- работает как **map**: `$storage[$obj] = $data` или **set**: `$storage->attach($obj)`
- использует **`spl_object_hash`** под капотом
- хранит **сильную** ссылку на ключ — объект **не освободится**, пока хранилище живёт ⚠️
- **источник утечек** в long-running процессах

**`WeakReference`:**
- одиночная **обёртка** над объектом, **не препятствующая GC**
- создаётся через **`WeakReference::create($obj)`**
- `$ref->get()` возвращает объект **или `null`**, если объект уже собран
- удобно для **обратных ссылок** (Observer не должен «держать» Subject)

**`WeakMap`:**
- ассоциативный map со **слабыми ключами**
- когда **последняя сильная ссылка** на объект-ключ уходит → запись **исчезает автоматически**
- используется для **side-table метаданных**: per-object кеши, ленивые вычисления, права доступа
- **значения** хранятся сильно (если значение содержит ссылку на ключ — кольцо, GC решит)

**Что брать:**
- **per-object метаданные/кеш без утечек** — **`WeakMap`**
- **обратная ссылка** на единственный объект — `WeakReference`
- **множество объектов** где время жизни хочется контролировать **извне** — `SplObjectStorage`',
                'code_example' => '<?php
// WeakMap — без утечек: запись уходит вместе с ключом
$cache = new WeakMap();
$user = new stdClass();
$cache[$user] = "expensive_payload";
var_dump(count($cache));       // 1
unset($user);
var_dump(count($cache));       // 0 — запись пропала автоматически

// WeakReference — слабая обёртка на единственный объект
$obj = new stdClass();
$ref = WeakReference::create($obj);
var_dump($ref->get() !== null); // true
unset($obj);
var_dump($ref->get());          // NULL — GC собрал

// SplObjectStorage — сильная ссылка, объект не освободится
$store = new SplObjectStorage();
$user = new stdClass();
$store[$user] = "data";
unset($user);
var_dump(count($store));        // 1 — утечка: $user всё ещё в $store',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.oop',
            ],
            [
                'category' => 'PHP',
                'question' => 'Приведи практический пример утечки памяти, которую решает WeakMap',
                'answer' => '**Классический сценарий:** кеширование **вычисленных метаданных по объекту** в **долгоживущем процессе** — Laravel **Octane**, `queue:work`, ReactPHP/AMPHP, Swoole.

**Типовая постановка:**
- `PermissionCache` запоминает права доступа для каждого `User`, чтобы не ходить в БД повторно при каждом fired event
- кеш живёт **между запросами** в Octane-воркере
- проблема всплывает только под нагрузкой через часы работы

**Как ломается с обычным массивом:**
1. Ключ кеша: **`spl_object_id($user)`** или сам объект через `SplObjectStorage`
2. Значение содержит **ссылку на `$user`** (или вычисленные данные с ним)
3. Контроллер закончился, `$user` нигде больше не нужен
4. **refcount остаётся > 0** — кеш держит сильную ссылку
5. Через **100k запросов** memory кончается → **OOM**

**Как решает `WeakMap`:**
- ключ — **слабая ссылка** на `$user`
- как только из контроллера и сервисов уходят **все сильные ссылки**, GC уничтожает `$user`
- запись **автоматически исчезает** из `WeakMap`
- **side-table data** — метаданные, права, ленивые вычисления, observer-паттерн (слушатели не должны мешать GC субъектов)

**Где реально используется в экосистеме:**
- **Symfony EventDispatcher** — связь listener ↔ subject
- **Doctrine UnitOfWork** — отслеживание managed entities
- **Laravel Octane** — кеширование вычисленных view-binding-ов
- любые **per-request scoped** структуры в long-running runtime

**Аналог в JS** — `WeakMap` используется для приватных полей и DOM-метаданных по той же причине.',
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
