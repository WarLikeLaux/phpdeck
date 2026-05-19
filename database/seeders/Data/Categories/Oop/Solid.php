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
                'answer' => 'SOLID - 5 принципов проектирования классов, помогающих делать код понятным, гибким и поддерживаемым. Сформулированы Робертом Мартином (Uncle Bob). S - Single Responsibility (единственная ответственность). O - Open/Closed (открыт для расширения, закрыт для модификации). L - Liskov Substitution (подстановка Лисков). I - Interface Segregation (разделение интерфейсов). D - Dependency Inversion (инверсия зависимостей).',
                'code_example' => null,
                'code_language' => null,
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.solid',
                'difficulty' => 3,
                'question' => 'SRP - Single Responsibility Principle (Принцип единственной ответственности)',
                'answer' => 'У класса должна быть только одна причина для изменения - иначе говоря, он отвечает за одну задачу. Классы, делающие много всего (Бог-объекты), сложно сопровождать: правка одной функции случайно ломает другую. Признак нарушения SRP: класс UserService, который и валидирует, и шлёт email, и пишет в БД, и форматирует JSON. Решение - разбить на отдельные классы: UserValidator, EmailSender, UserRepository, UserSerializer.',
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
                'answer' => 'Сущности должны быть открыты для расширения, но закрыты для модификации. То есть добавление новой функциональности должно происходить через создание новых классов, а не через правку существующих. Достигается через абстракции: интерфейсы, абстрактные классы, полиморфизм. Это снижает риск сломать работающий код при добавлении новых требований.',
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
                'answer' => 'Объекты потомков должны быть подставимыми вместо объектов родителя без поломки программы. Правила: 1) предусловия (требования к параметрам) можно ослаблять, но не усиливать (контравариантность). 2) постусловия (гарантии возвращаемого значения) можно усиливать, но не ослаблять (ковариантность). 3) инварианты родителя должны сохраняться. 4) наследник не должен бросать новые типы исключений, кроме подтипов уже объявленных. Признак нарушения LSP - проверки instanceof в клиентском коде перед использованием объекта. Если наследник не может выполнить контракт родителя - это не его наследник.',
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
                'answer' => 'Математически квадрат - частный случай прямоугольника, но в коде это разные сущности. Если Square extends Rectangle и переопределяет setWidth/setHeight чтобы менять обе стороны - это нарушение LSP: код, ожидающий Rectangle (можно менять стороны независимо), сломается с Square. Правильное решение - не делать Square наследником Rectangle, а ввести общий интерфейс Shape с методом area(), либо моделировать через композицию.',
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
                'answer' => 'Клиенты не должны зависеть от методов, которыми они не пользуются. Лучше много маленьких специализированных интерфейсов, чем один "толстый". Если у вас интерфейс Worker с методами work(), eat(), sleep(), а робот реализует его - ему придётся реализовывать eat()/sleep() пустышками. Правильнее разбить на Workable, Eatable, Sleepable.',
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
                'answer' => 'Модули верхнего уровня не должны зависеть от модулей нижнего уровня - оба должны зависеть от абстракций. Абстракции не должны зависеть от деталей; детали должны зависеть от абстракций. На практике: вместо new MySqlRepo() в сервисе принимайте RepositoryInterface через конструктор. Это позволяет менять реализацию (БД, моки) без правки сервиса. DIP - это про направление зависимостей в архитектуре.',
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
                'answer' => 'Часть контракта родителя — какие исключения может бросать метод. Наследник имеет право бросать ТОЛЬКО типы, задекларированные в родителе (в PHP — @throws), или их подклассы. Бросать новое исключение, которого клиент не ждёт, — нарушение LSP: catch-блоки не подготовлены, упадёт всё выше. Практика: оборачивай чужие исключения (PDOException → RepositoryException) и держи свою иерархию исключений домена, чтобы клиент ловил корневой тип и получал любых наследников.',
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
                'answer' => 'Интерфейс соблюдает DIP только когда: 1) Назван и определён в терминах ДОМЕНА (UserRepository), а не инфраструктуры (MySqlUserRepository). 2) Методы говорят на языке клиента (findActive(): array), а не реализации (findBySql(string $sql)). 3) Интерфейс ПРИНАДЛЕЖИТ слою клиента/домена; реализация — в Infrastructure. Если в интерфейс протекают детали (Builder, PDO, SQL-строки) — это leaky abstraction: high-level код становится зависим от low-level через «формально интерфейс». Сменишь реализацию — придётся переписывать и интерфейс, и клиента.',
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
                'answer' => 'Single Responsibility Principle: у класса должна быть ОДНА причина для изменения. Если класс делает «загрузить юзера + отправить email + посчитать налог» — это три ответственности, три причины меняться. Каждую — в свой класс. Меньше связности, проще тестировать и менять.',
                'difficulty' => 1,
                'topic' => 'oop.solid',
            ],
            [
                'category' => 'ООП',
                'question' => 'Что такое O в SOLID (Open/Closed) простыми словами?',
                'answer' => 'Open for extension, closed for modification: код должен быть открыт для расширения (можно добавить новое поведение), но закрыт для изменения (не трогать существующий). Достигается полиморфизмом: вместо if/elseif/elseif по типу — добавь новый класс, реализующий интерфейс. Старый код не меняется.',
                'difficulty' => 2,
                'topic' => 'oop.solid',
            ],
            [
                'category' => 'ООП',
                'question' => 'Что такое L в SOLID (Liskov Substitution) простыми словами?',
                'answer' => 'Liskov Substitution: объект подкласса должен быть СОВМЕСТИМ с объектом родителя — можно подменить и всё продолжит работать. Классический пример нарушения: Square extends Rectangle. У Rectangle есть setWidth/setHeight независимо; у Square ширина = высота, и подмена сломает код, ожидающий Rectangle. Решение — не наследовать там, где is-a нарушает контракт.',
                'difficulty' => 2,
                'topic' => 'oop.solid',
            ],
            [
                'category' => 'ООП',
                'question' => 'Что такое I в SOLID (Interface Segregation) простыми словами?',
                'answer' => 'Interface Segregation: лучше много маленьких интерфейсов с конкретной ролью, чем один «жирный». Класс не должен зависеть от методов, которые ему не нужны. Вместо одного MultiPrinter с print/scan/fax — три интерфейса: Printer, Scanner, Faxable. Класс реализует только то, что реально умеет.',
                'difficulty' => 2,
                'topic' => 'oop.solid',
            ],
            [
                'category' => 'ООП',
                'question' => 'Что такое D в SOLID (Dependency Inversion) простыми словами?',
                'answer' => 'Dependency Inversion: высокоуровневые модули не зависят от низкоуровневых; оба зависят от АБСТРАКЦИЙ. То есть в OrderService зависимость должна быть от интерфейса PaymentGateway, а не от StripeGateway напрямую. Тогда StripeGateway легко поменять на PayPalGateway без правки OrderService. DI (Dependency Injection) — техника реализации DIP.',
                'difficulty' => 2,
                'topic' => 'oop.solid',
            ],
        ];
    }
}
