<?php

namespace Database\Seeders\Data\Categories\Oop;

class AbstractAndInterfaces
{
    public static function all(): array
    {
        return [
            [
                'category' => 'ООП',
                'topic' => 'oop.abstract_interfaces',
                'difficulty' => 2,
                'question' => 'Что такое абстрактный класс?',
                'answer' => '**Абстрактный класс** — класс с ключевым словом `abstract`, который **нельзя инстанцировать** через `new`.

**Что в нём может быть:**

- обычные **реализованные** методы и свойства (общая логика)
- **`abstract`-методы** без тела — потомок обязан их реализовать

**Зачем нужен:** **частичная реализация** — общий код выносим в родитель, специфику оставляем потомкам (Template Method). Сам по себе абстрактный класс — это «недоделанный» класс, существующий только ради наследования.

**Ограничение:** в PHP можно наследовать **только один** абстрактный класс (как и обычный).',
                'code_example' => '<?php
abstract class Shape
{
    abstract public function area(): float;

    public function describe(): string
    {
        return \'Площадь: \' . $this->area();
    }
}

class Circle extends Shape
{
    public function __construct(private float $r) {}
    public function area(): float
    {
        return M_PI * $this->r ** 2;
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.abstract_interfaces',
                'difficulty' => 2,
                'question' => 'Что такое интерфейс?',
                'answer' => '**Интерфейс** — **контракт**: список методов без реализации. Класс через `implements` обязан реализовать **все** методы интерфейса с совместимыми сигнатурами.

**Что внутри:**

- сигнатуры **публичных** методов (без тела)
- **константы** (с PHP 8.3 — типизированные)
- **нет** свойств и нет состояния

**Особенности:**

- Один класс может **`implements` сразу несколько** интерфейсов (множественное наследование контрактов).
- Интерфейсы — основа **полиморфизма, DI и подменяемости** (моки в тестах).
- Можно объявить наследование интерфейсов: `interface Sortable extends Comparable`.',
                'code_example' => '<?php
interface Loggable
{
    public function log(string $message): void;
}

interface Cacheable
{
    public function getCacheKey(): string;
}

class Service implements Loggable, Cacheable
{
    public function log(string $message): void { /* ... */ }
    public function getCacheKey(): string
    {
        return \'service\';
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.abstract_interfaces',
                'difficulty' => 2,
                'question' => 'В чём разница между абстрактным классом и интерфейсом?',
                'answer' => '| | **Абстрактный класс** | **Интерфейс** |
|---|---|---|
| Реализация методов | да, частичная | нет (только сигнатуры) |
| Свойства / состояние | да | нет |
| Множественное наследование | нет (один) | да (несколько) |
| `extends` / `implements` | `extends` | `implements` |

**Когда что брать:**

- **Абстрактный класс** — есть общий **код** и общие **свойства** для группы родственных классов (`Animal` с реализованным `name()` и `abstract speak()`).
- **Интерфейс** — описать **поведение**, не привязываясь к иерархии. Классы из разных деревьев могут быть `Comparable`, `Iterable`, `Loggable`.

**Правило:** сначала пробуй интерфейс — он гибче и не съедает «единственного родителя».',
                'code_example' => '<?php
interface Drawable
{
    public function draw(): void; // только контракт
}

abstract class Widget // частичная реализация
{
    public function __construct(protected int $x, protected int $y) {}
    abstract public function render(): string;
    public function position(): string
    {
        return "($this->x, $this->y)";
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.abstract_interfaces',
                'difficulty' => 3,
                'question' => 'Может ли интерфейс содержать константы и свойства?',
                'answer' => '| Что | В интерфейсе |
|---|---|
| **Константы** (`public const`) | да |
| **Свойства-поля** | **нет** (интерфейс описывает контракт, а не состояние) |
| **Тело методов** | нет — только сигнатуры |
| Модификатор `abstract` у методов | не пишут — он подразумевается |

**Все методы интерфейса публичные** (`public`) — другие модификаторы запрещены.

**Эволюция возможностей константы интерфейса:**

- **PHP 8.1** — `final public const X` запрещает переопределение в реализующих классах.
- **PHP 8.3** — типизированные константы: `public const int OK = 200`.
- **PHP 8.4** — **property hooks в интерфейсах**: можно объявить «контракт на свойство» через `get`/`set` хуки (это всё ещё **не поле**, а виртуальное свойство).

**Что делать, если нужны общие свойства между классами:**

- **`abstract class`** — если есть общий код + состояние.
- **`trait`** — если нужно «подмешать» поля и методы в неродственные классы.',
                'code_example' => '<?php
interface HttpStatus
{
    public const int OK = 200;          // PHP 8.3: типизированная константа
    public const int NOT_FOUND = 404;
    final public const int SERVER_ERROR = 500; // PHP 8.1: final - запрет переопределения

    public function getStatus(): int;
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.abstract_interfaces',
                'difficulty' => 1,
                'question' => 'Когда брать абстрактный класс, а когда интерфейс — простыми словами?',
                'answer' => '- **Интерфейс** — когда нужно описать **ЧТО** объект умеет, а как — пусть каждый решает сам. Можно реализовать сколько угодно интерфейсов сразу.
- **Абстрактный класс** — когда есть общий **КОД** и общие **СВОЙСТВА** для группы классов: часть методов уже реализована, часть оставлена на потомков. У класса может быть **только один** абстрактный родитель.

**Правило:** если сомневаешься — начни с интерфейса, он гибче.',
                'code_example' => '<?php
// ✅ Интерфейс - просто контракт без реализации
interface Logger
{
    public function log(string $message): void;
}

class FileLogger implements Logger
{
    public function log(string $message): void { /* пишем в файл */ }
}

class NullLogger implements Logger
{
    public function log(string $message): void { /* ничего */ }
}

// ✅ Абстрактный класс - общий код + абстрактные методы для потомков
abstract class HttpController
{
    // общая реализация для всех потомков
    protected function json(array $data): string
    {
        return json_encode($data);
    }

    // потомок ОБЯЗАН реализовать
    abstract public function handle(): string;
}

class UserController extends HttpController
{
    public function handle(): string
    {
        return $this->json([\'user\' => \'Иван\']);
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.abstract_interfaces',
                'difficulty' => 2,
                'question' => 'Может ли абстрактный класс не иметь ни одного абстрактного метода?',
                'answer' => '**Да, может.** Достаточно ключевого слова `abstract` перед `class` — `new` уже будет запрещён.

**Зачем так делают:**

- Все методы реализованы, но семантически класс **не самостоятелен** — это **база только для наследования**.
- Явный сигнал: «не инстанцировать напрямую, только потомков» (типичные `BaseController`, `BaseModel`).

**Обратное правило:** если класс **не** объявлен `abstract`, но хотя бы один метод `abstract` — PHP даст **fatal error**. Логично: иначе можно было бы создать объект с «дырой».',
                'code_example' => '<?php
abstract class BaseController
{
    public function json(array $data): string
    {
        return json_encode($data);
    }
}

// new BaseController(); // Error: Cannot instantiate abstract class
class UserController extends BaseController {}',
                'code_language' => 'php',
            ],
        ];
    }
}
