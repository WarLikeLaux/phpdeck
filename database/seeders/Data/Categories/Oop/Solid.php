<?php

namespace Database\Seeders\Data\Categories\Oop;

class Solid
{
    public static function all(): array
    {
        return [
            [
                'category' => 'ООП',
                'topic' => 'oop.solid',
                'difficulty' => 2,
                'question' => 'Что такое SOLID?',
                'answer' => '**SOLID** — пять принципов проектирования классов, делающих код **понятным, гибким и поддерживаемым**. Сформулированы Робертом Мартином (Uncle Bob).

- **S — Single Responsibility** — одна ответственность, одна причина меняться.
- **O — Open/Closed** — открыт для расширения, закрыт для модификации.
- **I — Interface Segregation** — много маленьких интерфейсов вместо одного жирного.
- **D — Dependency Inversion** — зависим от **абстракций**, не от деталей.
- **L — Liskov Substitution** — потомок подменяет родителя **без поломки** клиента.

**Зачем:** уменьшает связность, упрощает тестирование (подмена через интерфейсы) и снижает риск регрессий при добавлении новых требований. Применять с умом — не натягивать на простой код.',
                'code_example' => null,
                'code_language' => null,
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.solid',
                'difficulty' => 3,
                'question' => 'SRP - Single Responsibility Principle (Принцип единственной ответственности)',
                'answer' => '**SRP:** у класса должна быть **одна причина для изменения** — он отвечает за **одну задачу / одного «актёра»** (как уточняет Uncle Bob: одна причина = одна заинтересованная сторона).

**Признаки нарушения:**
- класс **`UserService`** одновременно: валидирует, шлёт email, пишет в БД, рендерит JSON.
- название класса с **«And»** или **«Manager»** / **«Helper»** — обычно красный флаг.
- разные методы меняются **по разным причинам** (один — из-за нового маркетингового письма, другой — из-за миграции БД).

**Зачем нужно:**
- **меньше merge-конфликтов** — разные люди трогают разные файлы.
- **проще тестировать** — у каждого класса свой набор моков и сценариев.
- **меньше «бабочки крылом махнула»** — правка в одной зоне не ломает другие.

**Решение (типовой рефакторинг):** разбить «жирный» сервис на:
- `UserValidator` — валидация входа.
- `EmailSender` / `Mailer` — отправка писем.
- `UserRepository` — персистенция.
- `UserSerializer` / DTO — представление наружу.

**Подвох — не «один метод = один класс».** SRP не про количество методов, а про **зону изменений**. Можно иметь толстый репозиторий с десятком методов — все они меняются вместе при изменении схемы БД, это **одна** причина.',
                'code_example' => '<?php
// Плохо: класс делает всё
class UserBad
{
    public function save() {}
    public function validate() {}
    public function sendEmail() {}
    public function toJson() {}
}

// Хорошо: каждый класс отвечает за своё
class User { public string $email; }
class UserRepository { public function save(User $u) {} }
class UserValidator { public function validate(User $u) {} }
class Mailer { public function sendTo(User $u) {} }',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.solid',
                'difficulty' => 3,
                'question' => 'OCP - Open/Closed Principle (Принцип открытости/закрытости)',
                'answer' => '**OCP:** сущности **открыты для расширения**, но **закрыты для модификации**. Новые требования → **новый код**, а не правка старого.

**Как достигается:**
- **полиморфизм** через интерфейсы и абстрактные классы.
- замена **`switch`/`if` по типу** на вызов метода через полиморфный объект.
- **Strategy**, **Visitor**, **Chain of Responsibility** — типичные паттерны OCP.

**Признаки нарушения:**
- длинный **`switch ($type)`** / **`if instanceof`** — каждый новый тип требует правки этой функции.
- класс трогается **каждый раз**, когда добавляют новую разновидность одной сущности.

**Выгода:**
- **старый код не модифицируется** → не нужно перепроверять работающее.
- **минимизация регрессий**: добавление новой фичи = добавление нового файла.
- **проще тесты** на новый тип: тестируем только новую реализацию.

**Подвох:** **не злоупотреблять** «закрытостью». Преждевременная абстракция «на все случаи» (`Strategy` для двух простых веток) ухудшает читаемость. Применять, когда **множественность реализаций уже видна** или явно ожидается.',
                'code_example' => '<?php
// Плохо: каждый новый тип - правка switch
class AreaCalc
{
    public function area(object $shape): float
    {
        if ($shape instanceof Circle) return M_PI * $shape->r ** 2;
        if ($shape instanceof Square) return $shape->side ** 2;
        // добавили Triangle - правим этот класс
        throw new \InvalidArgumentException(\'Unknown shape\');
    }
}

// Хорошо: новые фигуры - новые классы, AreaCalc не меняется
interface Shape
{
    public function area(): float;
}

class Circle implements Shape
{
    public function __construct(private float $r) {}
    public function area(): float { return M_PI * $this->r ** 2; }
}

class Square implements Shape
{
    public function __construct(private float $side) {}
    public function area(): float { return $this->side ** 2; }
}

class AreaCalcGood
{
    public function area(Shape $s): float
    {
        return $s->area(); // открыт для расширения, закрыт для модификации
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.solid',
                'difficulty' => 3,
                'question' => 'LSP - Liskov Substitution Principle (Принцип подстановки Лисков)',
                'answer' => '**LSP:** объект потомка должен **подставляться** вместо родителя так, чтобы клиент **ничего не заметил**.

**Правила (что значит «не заметил»):**

1. **Предусловия** (требования к параметрам) — **можно ослаблять**, нельзя **усиливать**. Это **контравариантность** на входе.
2. **Постусловия** (гарантии возвращаемого значения) — **можно усиливать**, нельзя **ослаблять**. Это **ковариантность** на выходе.
3. **Инварианты** родителя должны **сохраняться** (внутренние «правда всегда» состояния).
4. **Новые исключения** в наследнике — только **подтипы** уже объявленных в родителе (`@throws`).
5. **History constraint** — наследник не должен раскрывать «прошлое», которое родитель не показывал.

**Признаки нарушения LSP:**
- проверки **`instanceof`** в клиентском коде перед использованием объекта.
- **`UnsupportedOperationException`** / **`LogicException`** в перегрузке («ты вызвал, но я не умею»).
- **частичные** перегрузки, которые работают «не для всех случаев».

**Поддержка в PHP:**
- параметры **нельзя усиливать**: ребёнок не может требовать более узкий тип параметра, чем у родителя (есть с PHP 7.4 — контравариантность параметров).
- возвращаемый тип **можно сужать** (ковариантность return-type).

**Правило большого пальца:** если **`is-a`** ломает контракт — это **не наследование**. Перепиши через **композицию** или раздели иерархию по способностям (interface-segregation).',
                'code_example' => '<?php
class Bird
{
    public function fly(): void { /* летим */ }
}

// LSP нарушен: пингвин не умеет летать, но extends Bird
class Penguin extends Bird
{
    public function fly(): void
    {
        throw new \LogicException(\'Пингвины не летают\');
    }
}

function startFlight(Bird $bird): void
{
    $bird->fly(); // упадёт для Penguin - LSP нарушен
}

// Решение: разделить по способностям
interface Bird2 {}
interface FlyingBird extends Bird2 { public function fly(): void; }
class Sparrow implements FlyingBird { public function fly(): void {} }
class Penguin2 implements Bird2 {} // не FlyingBird',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.solid',
                'difficulty' => 3,
                'question' => 'Классический пример нарушения LSP - Square/Rectangle',
                'answer' => '**Парадокс:** математически квадрат — **частный случай** прямоугольника. В коде же это **разные сущности**.

**Что ломается при `Square extends Rectangle`:**
- родитель **гарантирует**: `setWidth($w)` меняет **только** ширину, `setHeight($h)` — только высоту.
- наследник вынужден **переопределить оба сеттера**, чтобы синхронно менять обе стороны (иначе квадрат перестанет быть квадратом).
- этим нарушается **инвариант независимости сторон**, который клиент-функция считает истиной.

**Симптом:**
```
function test(Rectangle $r) {
    $r->setWidth(5);
    $r->setHeight(4);
    assert($r->area() === 20);  // для Square: 16 → провал
}
```

**Корни проблемы:**
- путаница **«is-a в реальном мире»** и **«is-a в смысле LSP»** — это **не одно и то же**.
- наследование от **mutable** прямоугольника принципиально ломается. Если бы оба были **immutable** (`withWidth()` возвращает новый объект) — конфликт инвариантов оставался бы, но clients не падали бы из-за «магической» синхронизации.

**Правильные решения:**
1. **Общий интерфейс** `Shape { area(): float; }`, обе фигуры — независимые реализации.
2. **Композиция**: `Square` хранит `side`, при необходимости отдаёт `Rectangle` через метод.
3. **Immutable Value Objects** без сеттеров — `Square::create(side)`, `Rectangle::create(w, h)`.

**Главный урок:** при моделировании ориентируйся на **поведение и контракты**, а не на «классификацию из учебника».',
                'code_example' => '<?php
// Нарушение LSP
class Rectangle
{
    public function setWidth(int $w): void { $this->w = $w; }
    public function setHeight(int $h): void { $this->h = $h; }
    public function area(): int { return $this->w * $this->h; }
    protected int $w = 0; protected int $h = 0;
}

class Square extends Rectangle
{
    // ломает контракт: setWidth меняет и высоту
    public function setWidth(int $w): void {
        $this->w = $w; $this->h = $w;
    }
    public function setHeight(int $h): void {
        $this->w = $h; $this->h = $h;
    }
}

// Код, ожидавший прямоугольник, сломается:
function test(Rectangle $r): void {
    $r->setWidth(5);
    $r->setHeight(4);
    assert($r->area() === 20); // для Square: 16, провал
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.solid',
                'difficulty' => 3,
                'question' => 'ISP - Interface Segregation Principle (Принцип разделения интерфейсов)',
                'answer' => '**ISP:** клиенты **не должны** зависеть от методов, которыми они не пользуются. Лучше **много маленьких ролевых** интерфейсов, чем **один «толстый»**.

**Классический пример:** `Worker` с методами `work()`, `eat()`, `sleep()`. Когда появляется `Robot implements Worker` — он вынужден писать **пустышки** `eat()` / `sleep()` или **бросать** `BadMethodCallException`. Это **запах ISP**.

**Решение — разбить по ролям:**
- `Workable` — `work()`
- `Eatable` — `eat()`
- `Sleepable` — `sleep()`

`Human implements Workable, Eatable, Sleepable`. `Robot implements Workable`. Каждый класс реализует **только то, что реально умеет**.

**Признаки нарушения ISP:**
- **пустые** или throw-only реализации методов.
- **`@deprecated`** методы, оставленные «для совместимости» во всём интерфейсе.
- метод **используют только 1–2 клиента** из десятка реализаций.
- **изменение** интерфейса для одного клиента ломает компиляцию у других.

**Польза:**
- **`type-hint`** становится **точнее**: `function copy(Printer $p)` принимает только то, что **умеет печатать**.
- проще писать **моки** в тестах — реализуешь маленький интерфейс, а не 15 методов.
- меньше **связности** между несвязанными ролями.

**Связь с SRP:** ISP — это SRP, применённый к **интерфейсам** вместо классов. Один интерфейс — одна роль.',
                'code_example' => '<?php
// Плохо: толстый интерфейс
interface Worker
{
    public function work(): void;
    public function eat(): void;
    public function sleep(): void;
}

// Хорошо: разделили
interface Workable { public function work(): void; }
interface Eatable  { public function eat(): void; }
interface Sleepable { public function sleep(): void; }

class Human implements Workable, Eatable, Sleepable { /* ... */ }
class Robot implements Workable { /* eat и sleep не нужны */ }',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.solid',
                'difficulty' => 3,
                'question' => 'DIP - Dependency Inversion Principle (Принцип инверсии зависимостей)',
                'answer' => '**DIP:** два правила про **направление** зависимостей:

1. **Модули верхнего уровня** (бизнес-логика) **не зависят** от модулей нижнего уровня (БД, HTTP). **Оба** зависят от **абстракций**.
2. **Абстракции не зависят от деталей**; детали зависят от абстракций.

**На практике в PHP/Laravel:** вместо
```
class OrderService {
    private MySqlOrderRepo $db;
    public function __construct() {
        $this->db = new MySqlOrderRepo();   // жёсткая зависимость от реализации
    }
}
```
делают
```
interface OrderRepository { public function save(Order $o): void; }

class OrderService {
    public function __construct(private OrderRepository $repo) {}
}
```

**Что это даёт:**
- **Подмена** реализации в тестах (`InMemoryOrderRepo`) — без правки `OrderService`.
- **Свобода миграции**: с MySQL на PostgreSQL — меняешь только реализацию, бизнес-логика не трогается.
- **Тестируемость**: моки на чистый интерфейс, без БД.

**Не путать с DI:**
- **DIP** — **принцип** проектирования: «зависим от абстракций, не от конкретики».
- **DI** (Dependency Injection) — **техника** передачи зависимости извне (constructor / setter / contextual). Это **один из способов** реализовать DIP, не равенство.

**Тонкость — leaky abstraction:**
- Если интерфейс **протекает деталями реализации** (`getQueryBuilder()`, `rawConnection()`, методы с `string $sql`) — DIP **формально соблюдён**, но смысла нет: клиент всё равно зависит от деталей.
- Правило: интерфейс **именован и сформулирован в терминах домена** (`UserRepository::findActive(): array<User>`), а **не** инфраструктуры (`MySqlUserRepo::findBySql()`).
- Интерфейс **принадлежит слою клиента** (Domain), реализация — Infrastructure. Это «инверсия» в DIP: домен **не знает** про БД, а реализация **зависит от домена**.',
                'code_example' => '<?php
// Плохо: сервис привязан к конкретной реализации
class OrderServiceBad
{
    private MySqlOrderRepo $db;

    public function __construct()
    {
        $this->db = new MySqlOrderRepo(); // жёсткая зависимость
    }
}

// Хорошо: зависим от абстракции
interface OrderRepository {
    public function save(Order $o): void;
}

class OrderService
{
    public function __construct(private OrderRepository $repo) {}
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.solid',
                'difficulty' => 4,
                'question' => 'Как LSP ограничивает выброс исключений в наследниках?',
                'answer' => '**Часть контракта родителя — какие исключения может бросать метод.** Это **постусловие**, проверяемое LSP.

**Правило:** наследник имеет право бросать **только**:

- **типы, задекларированные в родителе** (в PHP — через `@throws`),
- или **их подклассы**.

**Бросать новое исключение, которого клиент не ждёт** — **нарушение LSP**: `catch`-блоки не подготовлены, исключение **пробивается** до глобального handler-а → 500 в проде.

**Типичный сценарий нарушения:**

| Шаг | Что происходит |
|---|---|
| 1 | Родитель декларирует `@throws UserNotFoundException` |
| 2 | Наследник тянет `PDO` и даёт всплыть `PDOException` |
| 3 | Клиент ловит только `UserNotFoundException` |
| 4 | `PDOException` **пробивает** все try/catch — 500 |

**Практика защиты:**

1. **Оборачивай чужие исключения** — `PDOException` → `RepositoryException`.
2. Заведи **иерархию исключений домена** — клиент ловит корневой тип и получает всех наследников.
3. **`@throws` обязателен** в интерфейсах/абстрактах — иначе контракт не определён.
4. **Никаких голых `\Exception` или `\RuntimeException` из инфраструктуры.**',
                'code_example' => '<?php
abstract class UserRepository
{
    /**
     * @throws UserNotFoundException - записи с таким id нет
     * @throws RepositoryException   - хранилище недоступно/ошибка драйвера
     */
    abstract public function find(int $id): User;
}

// ❌ Нарушение LSP - бросает то, чего клиент не ждёт
class DbUserRepoBad extends UserRepository
{
    public function find(int $id): User
    {
        $row = $this->pdo->query("SELECT ...")->fetch(); // бросит PDOException!
        if (! $row) throw new UserNotFoundException;
        return new User($row);
    }
}

try {
    $repo->find(1);
} catch (UserNotFoundException|RepositoryException) {
    return null; // готов только к задекларированным
}
// PDOException пробивается дальше - 500 в проде

// ✅ Соблюдает LSP - оборачивает чужие исключения по семантике
class DbUserRepoGood extends UserRepository
{
    public function find(int $id): User
    {
        try {
            $row = $this->pdo->query("SELECT ...")->fetch();
        } catch (PDOException $e) {
            // ошибка БД - инфраструктура, не "user not found"
            throw new RepositoryException("db error", previous: $e);
        }
        if (! $row) throw new UserNotFoundException; // семантическое not found
        return new User($row);
    }
}

// ✅ Подкласс задекларированного - тоже LSP-совместимо
class CachedUserRepo extends UserRepository
{
    public function find(int $id): User
    {
        // StaleCacheException extends RepositoryException - можно
        if ($this->cacheStale($id)) throw new StaleCacheException;
        // ...
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.solid',
                'difficulty' => 4,
                'question' => 'Почему наличие интерфейса не означает соблюдение DIP? Что такое leaky abstraction?',
                'answer' => '**DIP — это не «использовать интерфейс».** Это **направление зависимости**: high-level **не зависит** от low-level — оба зависят от **абстракции, принадлежащей high-level**.

**Интерфейс соблюдает DIP только когда:**

1. **Назван** в терминах **домена** (`UserRepository`), а не инфраструктуры (`MySqlUserRepository`).
2. **Методы говорят на языке клиента** — `findActive(): array`, не `findBySql(string $sql)`.
3. **Принадлежит слою домена/клиента**, реализация — в **Infrastructure**.
4. **В сигнатуру не утекают** детали (`Builder`, `PDO`, `SQL`-строки, `Eloquent\Model`).

**Leaky abstraction:**

| Запах | Почему плохо |
|---|---|
| `findBySql(string $sql)` | SQL — деталь реализации, утекает в контракт |
| `getQueryBuilder(): Builder` | привязка к Eloquent |
| `rawConnection(): PDO` | привязка к PDO-драйверу |
| `findOneBy(array $criteria)` | оверкилл, протекает в стиле Doctrine |

**Симптом:** при смене реализации (Eloquent → Doctrine → Mongo) **приходится менять интерфейс** и **переписывать клиента** — значит DIP не выполнен, был только **формальный интерфейс**.

**Правило:** если интерфейс вынесли «потому что так положено», но он **повторяет API реализации** — он не абстракция, а **псевдо-абстракция**. DIP **не выполнен**.',
                'code_example' => '<?php
// ❌ Leaky abstraction - интерфейс протекает деталями реализации
namespace App\Infrastructure\Persistence;

interface MySqlUserRepository {
    public function findBySql(string $sql): array; // SQL утечка
    public function getQueryBuilder(): Builder;    // Eloquent в контракте
    public function rawConnection(): PDO;          // PDO в контракте
}
// Контроллер, использующий это, фактически зависит от MySQL/Eloquent

// ✅ Чистый DIP - интерфейс принадлежит домену, говорит на его языке
namespace App\Domain\Users;

interface UserRepository {
    public function findById(UserId $id): ?User;
    public function findActive(): array;          // /** @return User[] */
    public function save(User $user): void;
    public function delete(UserId $id): void;
}

// Реализация на инфраструктурной стороне - её можно менять
namespace App\Infrastructure\Persistence;
final class EloquentUserRepository implements \App\Domain\Users\UserRepository {
    public function findById(UserId $id): ?User {
        $row = UserModel::find((string) $id);
        return $row ? $this->toDomain($row) : null;
    }
    // ...
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Что такое S в SOLID (Single Responsibility) простыми словами?',
                'answer' => '**Single Responsibility Principle (SRP)** — у класса должна быть **ОДНА причина для изменения**.

Если класс «грузит юзера + шлёт email + считает налог» — это **три** ответственности, **три** причины меняться. Разбей на отдельные классы:

- меньше связности
- проще тестировать
- проще править — правка в одной зоне не ломает другие',
                'difficulty' => 1,
                'topic' => 'oop.solid',
                'code_example' => '<?php
// ❌ Было: один класс делает всё
class UserService
{
    public function save(User $u): void { /* в БД */ }
    public function sendWelcome(User $u): void { /* email */ }
    public function calcTax(User $u): float { /* налог */ }
}

// ✅ Стало: одна ответственность = один класс
class UserRepository { public function save(User $u): void {} }
class Mailer        { public function sendWelcome(User $u): void {} }
class TaxCalculator { public function calc(User $u): float { return 0; } }',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Что такое O в SOLID (Open/Closed) простыми словами?',
                'answer' => '**Open/Closed Principle (OCP)** — код должен быть **открыт для расширения**, но **закрыт для модификации**.

- Добавили новое требование → пишем **новый класс**, не правим старые.
- Достигается **полиморфизмом**: вместо `if ($type === \'circle\')` / `elseif` — добавь новую реализацию интерфейса.

**Признак нарушения:** длинный `switch`/`if` по типу объекта. Каждый новый тип → правка функции. Решение — `interface Shape { area(); }` и новая фигура = новый класс.

**Польза:** старый код не трогаем → не ломаем работающее → не нужно тестировать всё подряд.',
                'difficulty' => 2,
                'topic' => 'oop.solid',
                'code_example' => '<?php
// ❌ Было: новый тип = правка switch
function area(object $s): float
{
    if ($s instanceof Circle) return M_PI * $s->r ** 2;
    if ($s instanceof Square) return $s->side ** 2;
    throw new \InvalidArgumentException();
}

// ✅ Стало: новая фигура = новый класс, area() не трогаем
interface Shape { public function area(): float; }

class Circle implements Shape {
    public function __construct(private float $r) {}
    public function area(): float { return M_PI * $this->r ** 2; }
}
class Square implements Shape {
    public function __construct(private float $side) {}
    public function area(): float { return $this->side ** 2; }
}

function area2(Shape $s): float { return $s->area(); }',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Что такое L в SOLID (Liskov Substitution) простыми словами?',
                'answer' => '**Liskov Substitution Principle (LSP)** — объект потомка должен быть **подставим** вместо родителя так, чтобы клиент **ничего не заметил**.

**Что нельзя:**

- Бросать новое неожиданное исключение в перегрузке.
- Усиливать предусловия (требовать больше от параметров).
- Ослаблять постусловия (возвращать меньше, чем гарантировал родитель).
- Менять инварианты — нарушать невидимый контракт.

**Классический пример:** `Penguin extends Bird` с методом `fly()`, который бросает исключение, — нарушает LSP. Любой `function startFlight(Bird $b)` сломается.

**Решение:** разделить иерархию по способностям — `FlyingBird extends Bird` отдельно. Если is-a нарушает контракт — не наследуй.',
                'difficulty' => 2,
                'topic' => 'oop.solid',
                'code_example' => '<?php
class Bird
{
    public function fly(): void {}
}

// ❌ Нарушение LSP - подмена ломает клиента
class Penguin extends Bird
{
    public function fly(): void
    {
        throw new \LogicException(\'Пингвины не летают\');
    }
}

function startFlight(Bird $b): void { $b->fly(); } // упадёт с Penguin

// ✅ Решение - разделить иерархию по способностям
interface BirdLike {}
interface FlyingBird extends BirdLike { public function fly(): void; }

class Sparrow  implements FlyingBird { public function fly(): void {} }
class Penguin2 implements BirdLike {} // не FlyingBird - не имеет fly()',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Что такое I в SOLID (Interface Segregation) простыми словами?',
                'answer' => '**Interface Segregation Principle (ISP)** — клиент **не должен зависеть** от методов, которыми он не пользуется.

**Правило:** лучше **много маленьких** ролевых интерфейсов, чем **один жирный**.

**Признак нарушения:** простой принтер реализует `MultiDevice` (`print`, `scan`, `fax`) и вынужден кидать `BadMethodCallException` в `scan()`/`fax()` — методы есть, но не работают.

**Решение:** разбить на `Printer`, `Scanner`, `Faxable`. Класс реализует **только то, что реально умеет**, а клиент зависит от **точного контракта** (`function print(Printer $p)`).',
                'difficulty' => 2,
                'topic' => 'oop.solid',
                'code_example' => '<?php
// ❌ Жирный интерфейс - простой принтер вынужден заглушать scan/fax
interface MultiDevice
{
    public function print(string $doc): void;
    public function scan(): string;
    public function fax(string $to, string $doc): void;
}

// ✅ Разделили по ролям - класс реализует только нужное
interface Printer { public function print(string $doc): void; }
interface Scanner { public function scan(): string; }
interface Faxable { public function fax(string $to, string $doc): void; }

class SimplePrinter implements Printer
{
    public function print(string $doc): void { /* ... */ }
}

class OfficeMfp implements Printer, Scanner, Faxable
{
    public function print(string $doc): void {}
    public function scan(): string { return \'\'; }
    public function fax(string $to, string $doc): void {}
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Что такое D в SOLID (Dependency Inversion) простыми словами?',
                'answer' => '**Dependency Inversion Principle (DIP)** — модули верхнего уровня **не зависят** от модулей нижнего уровня. Оба зависят от **абстракций** (интерфейсов).

**Правильно:** `OrderService` зависит от `PaymentGateway` (интерфейс). Заменить `StripeGateway` на `PayPalGateway` или `FakeGateway` в тестах — **не трогая сервис**.

**Неправильно:** `OrderService` напрямую делает `new StripeGateway()` в конструкторе — жёсткая связь.

**Не путать:**

- **DIP** — принцип (зависим от абстракции, не от конкретики).
- **DI** (Dependency Injection) — техника передачи зависимости снаружи (через конструктор, сеттер, контейнер). DI — один из способов реализовать DIP.',
                'difficulty' => 2,
                'topic' => 'oop.solid',
                'code_example' => '<?php
// ❌ Было: сервис прибит к конкретной реализации
class OrderServiceBad
{
    private StripeGateway $gateway;
    public function __construct() { $this->gateway = new StripeGateway(); }
}

// ✅ Стало: зависим от абстракции, реализация инжектится
interface PaymentGateway { public function pay(int $cents): bool; }

class StripeGateway implements PaymentGateway {
    public function pay(int $c): bool { return true; }
}
class PayPalGateway implements PaymentGateway {
    public function pay(int $c): bool { return true; }
}

class OrderService
{
    public function __construct(private PaymentGateway $gateway) {}
}

// Подменить реализацию - не трогая OrderService
$svc = new OrderService(new StripeGateway());
$svc = new OrderService(new PayPalGateway());',
                'code_language' => 'php',
            ],
        ];
    }
}
