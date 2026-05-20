<?php

namespace Database\Seeders\Data\Categories\Oop;

class ConstructorDestructor
{
    public static function all(): array
    {
        return [
            [
                'category' => 'ООП',
                'topic' => 'oop.constructor_destructor',
                'difficulty' => 2,
                'question' => 'Что такое конструктор?',
                'answer' => '**Конструктор** — специальный метод `__construct()`, вызывается **автоматически** при `new`. Главная задача — **инициализировать** свойства, чтобы объект сразу был в валидном состоянии.

**Особенности в PHP:**

- **Constructor property promotion** (PHP 8.0) — свойства можно объявить прямо в параметрах: `public function __construct(public string $name) {}`. Меньше бойлерплейта.
- **Параметры**: любое количество и тип, поддерживает default-значения и nullable.
- **Родительский конструктор НЕ вызывается автоматически** (в отличие от Java/C++). Если переопределяешь — пиши `parent::__construct(...)` сам.
- Можно делать `private`/`protected` — закрытие `new` снаружи в пользу фабричных методов.',
                'code_example' => '<?php
// Старый стиль
class UserOld
{
    public string $name;
    public int $age;

    public function __construct(string $name, int $age)
    {
        $this->name = $name;
        $this->age = $age;
    }
}

// PHP 8+ promotion
class User
{
    public function __construct(
        public string $name,
        public int $age,
    ) {}
}

$u = new User(\'Иван\', 30);',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.constructor_destructor',
                'difficulty' => 2,
                'question' => 'Что такое деструктор?',
                'answer' => '**Деструктор** — метод `__destruct()`, вызывается **автоматически** при уничтожении объекта: когда на него больше нет ссылок или скрипт завершается.

**Зачем:** освобождать ресурсы — закрыть файл (`fclose`), соединение, очистить кэш.

**В PHP используется редко** — сборщик мусора и `try/finally` обычно справляются лучше.

**Важные подводные камни:**

- Порядок вызова **не гарантирован** при завершении скрипта.
- Могут **не выполниться** при `fatal error`, OOM или циклических ссылках на shutdown.
- **Нельзя бросать исключения** из деструктора — если объект уничтожается в shutdown, исключение становится fatal error.
- Не подходит для критичной логики (запись в БД при выходе) — используй явный `close()` / `finally`.',
                'code_example' => '<?php
class FileLogger
{
    private $handle;

    public function __construct(string $path)
    {
        $this->handle = fopen($path, \'a\');
    }

    public function log(string $msg): void
    {
        fwrite($this->handle, $msg . PHP_EOL);
    }

    public function __destruct()
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
        }
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Зачем нужен constructor property promotion простыми словами?',
                'answer' => '**Constructor property promotion** (PHP 8.0) — сокращённый синтаксис: параметр конструктора с модификатором видимости (`public`/`protected`/`private`/`readonly`) **автоматически** становится свойством.

**Что заменяет в одной строке:**

1. Объявление поля.
2. Указание типа.
3. Присваивание `$this->x = $x`.

**Где особенно полезно:** **DTO**, **Value Objects**, immutable-объекты с `readonly`. Меньше шума — больше видно полезной логики.

Внутри конструктора можно дописать обычное тело (`{...}`) — например, валидацию.',
                'difficulty' => 2,
                'topic' => 'oop.constructor_destructor',
                'code_example' => '<?php
// ❌ Старый стиль - бойлерплейт
class UserOld
{
    public string $name;
    public int $age;

    public function __construct(string $name, int $age)
    {
        $this->name = $name;
        $this->age = $age;
    }
}

// ✅ PHP 8.0 promotion - то же самое одной строкой
final class User
{
    public function __construct(
        public string $name,
        public int $age,
    ) {}
}

$u = new User(\'Иван\', 30);
echo $u->name; // Иван',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Зачем вызывать parent::__construct() в конструкторе наследника?',
                'answer' => 'PHP **не вызывает** родительский конструктор автоматически (в отличие от Java/C++).

**Что будет, если забыть:**

- Свойства родителя останутся **неинициализированными**.
- При обращении к `typed property` — `Error: must not be accessed before initialization`.
- Логика родителя (открытие соединения, регистрация в реестре) — не сработает.

**Правило:** в конструкторе наследника вызывай `parent::__construct(...)` **первой строкой**, потом своя инициализация.

**Исключение:** если родитель — `abstract` без своего `__construct`, или наследник принципиально хочет полностью заменить инициализацию (редкий случай, обычно повод пересмотреть дизайн).',
                'difficulty' => 2,
                'topic' => 'oop.constructor_destructor',
                'code_example' => '<?php
class Animal
{
    protected string $kind;

    public function __construct(string $kind)
    {
        $this->kind = $kind;
    }
}

// ❌ Забыли parent::__construct - $kind остался без значения
class DogBad extends Animal
{
    public function __construct(public string $name) {}
}
// (new DogBad("Рекс"))->kind; // Error: uninitialized

// ✅ Правильно: сначала родитель, потом своё
class Dog extends Animal
{
    public function __construct(public string $name)
    {
        parent::__construct(\'dog\');
    }
}

echo (new Dog(\'Рекс\'))->name; // Рекс',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.constructor_destructor',
                'difficulty' => 2,
                'question' => 'Что такое readonly-свойство (PHP 8.1)?',
                'answer' => '**`readonly`-свойство** (PHP 8.1) — поле, которое можно записать **только один раз**, и только **в конструкторе того же класса**.

**Поведение:**

- После инициализации любая запись бросит `Error: Cannot modify readonly property`.
- Должно быть **типизировано** — иначе `Fatal error`.
- В **наследнике** изменить тоже нельзя — read-only действует на всё дерево.

**Зачем:** иммутабельные объекты — **Value Objects, DTO, Event Objects**. Гарантирует, что значение не изменится после создания.

**PHP 8.2:** можно сделать **класс** целиком `readonly` — тогда все свойства автоматически readonly.

**Клонирование:** до PHP 8.3 — пересоздавали через new, с PHP 8.3 в методе `__clone` можно один раз изменить readonly-поля клона.',
                'code_example' => '<?php
final class Point
{
    public function __construct(
        public readonly float $x,
        public readonly float $y,
    ) {}
}

$p = new Point(1.0, 2.0);
// $p->x = 5.0; // Error: Cannot modify readonly property',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.constructor_destructor',
                'difficulty' => 2,
                'question' => 'Можно ли в PHP объявить несколько конструкторов (перегрузка)?',
                'answer' => '**Нет.** В одном классе может быть **только один** метод `__construct` — PHP не поддерживает перегрузку методов по типу/количеству параметров.

**Что вместо:** **named constructors** — статические фабричные методы.

- `Money::fromCents(100)` — из копеек
- `Money::fromDollars(1.5)` — из долларов
- `User::fromArray($row)` — из массива
- `Order::createPending($items)` — с дефолтным статусом

**Плюсы такого подхода:**

- Имя говорит о **смысле** создания (а не угадывать по типам параметров).
- Сам `__construct` обычно делают `private` — навязывает использование фабрик.
- Каждая фабрика может валидировать вход и бросать понятное исключение.',
                'code_example' => '<?php
final class Money
{
    private function __construct(public int $cents) {}

    public static function fromCents(int $cents): self
    {
        return new self($cents);
    }

    public static function fromDollars(float $dollars): self
    {
        return new self((int) round($dollars * 100));
    }
}

$a = Money::fromCents(150);
$b = Money::fromDollars(1.5);',
                'code_language' => 'php',
            ],
        ];
    }
}
