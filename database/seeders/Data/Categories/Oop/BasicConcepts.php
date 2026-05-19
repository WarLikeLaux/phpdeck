<?php

namespace Database\Seeders\Data\Categories\Oop;

class BasicConcepts
{
    public static function all(): array
    {
        return [
            [
                'category' => 'ООП',
                'topic' => 'oop.basic_concepts',
                'difficulty' => 1,
                'question' => 'Что такое ООП?',
                'answer' => 'ООП (объектно-ориентированное программирование) - это парадигма программирования, в которой программа моделируется как набор взаимодействующих объектов. Каждый объект - это сущность, которая имеет состояние (свойства) и поведение (методы). ООП помогает структурировать код, переиспользовать его и моделировать предметную область реального мира в коде.',
                'code_example' => null,
                'code_language' => null,
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.basic_concepts',
                'difficulty' => 2,
                'question' => 'Какие основные парадигмы программирования существуют?',
                'answer' => 'Основные парадигмы: 1) Императивная (пошаговое описание команд) - например, процедурное программирование. 2) Декларативная (SQL, логическая) - описание "что нужно", а не "как". 3) ООП (объектно-ориентированная) - программа как набор объектов. 4) Функциональная - частный случай декларативной: программа как композиция чистых функций. 5) Логическая - на основе логических утверждений (Prolog). PHP поддерживает несколько парадигм одновременно.',
                'code_example' => null,
                'code_language' => null,
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.basic_concepts',
                'difficulty' => 1,
                'question' => 'Зачем нужно ООП?',
                'answer' => 'ООП решает проблемы сложности больших программ: 1) Структурирует код в логические блоки (классы). 2) Повторное использование кода через наследование и композицию. 3) Инкапсуляция скрывает детали реализации. 4) Полиморфизм позволяет менять поведение без переписывания кода. 5) Близость к реальному миру - проще моделировать домен. 6) Тестируемость - легче изолировать модули. 7) Командная разработка - чёткое разделение ответственностей.',
                'code_example' => null,
                'code_language' => null,
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.basic_concepts',
                'difficulty' => 1,
                'question' => 'Что такое класс?',
                'answer' => 'Класс - это шаблон (чертёж), описывающий структуру и поведение объектов. В классе определяются свойства (данные) и методы (действия). Сам по себе класс - это не объект, а описание того, какими будут его экземпляры. Аналогия: класс - это чертёж дома, а объект - это конкретный построенный дом.',
                'code_example' => '<?php
class User
{
    public string $name;
    public int $age;

    public function greet(): string
    {
        return \'Привет, я \' . $this->name;
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.basic_concepts',
                'difficulty' => 1,
                'question' => 'Что такое объект?',
                'answer' => 'Объект - это конкретный экземпляр класса, существующий в памяти. У каждого объекта своё состояние (значения свойств), но поведение (методы) общее, заданное классом. Объект создаётся через оператор new. У одного класса может быть множество объектов, каждый со своими данными.',
                'code_example' => '<?php
$user1 = new User();
$user1->name = \'Иван\';

$user2 = new User();
$user2->name = \'Мария\';

// Это два разных объекта одного класса
echo $user1->greet(); // Привет, я Иван
echo $user2->greet(); // Привет, я Мария',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.basic_concepts',
                'difficulty' => 1,
                'question' => 'Чем класс отличается от объекта?',
                'answer' => 'Класс - это описание (тип, шаблон), а объект - это конкретный экземпляр класса в памяти. Класс существует на этапе компиляции/определения, объект - во время выполнения. Класс один, объектов может быть много. Аналогия: класс Cat - это понятие "кошка вообще", а объект - это конкретная кошка Мурка с её цветом, возрастом, именем.',
                'code_example' => '<?php
// Класс - описание
class Cat
{
    public string $name;
}

// Объекты - конкретные экземпляры
$murka = new Cat();
$murka->name = \'Мурка\';

$barsik = new Cat();
$barsik->name = \'Барсик\';',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.basic_concepts',
                'difficulty' => 1,
                'question' => 'Что такое метод?',
                'answer' => 'Метод - это функция, объявленная внутри класса. Метод описывает поведение объектов класса и обычно работает с их данными (свойствами). Методы вызываются через объект (->) или через имя класса (::) для статических методов. Нестатические методы получают доступ к $this, статические - нет.',
                'code_example' => '<?php
class Calculator
{
    public function add(int $a, int $b): int
    {
        return $a + $b;
    }
}

$calc = new Calculator();
echo $calc->add(2, 3); // 5',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.basic_concepts',
                'difficulty' => 1,
                'question' => 'Что такое свойство?',
                'answer' => 'Свойство (property, поле) - это переменная, объявленная внутри класса. Свойство хранит данные конкретного объекта. У каждого объекта свой набор значений свойств, но имена и типы свойств описаны в классе. С PHP 7.4 свойства можно типизировать, с PHP 8.1 - помечать readonly (запись только в конструкторе), с PHP 8.4 - использовать property hooks (виртуальные геттеры/сеттеры) и асимметричную видимость (public read, private write).',
                'code_example' => '<?php
class Product
{
    public string $name;
    public float $price;
    public int $quantity = 0; // значение по умолчанию
}

$p = new Product();
$p->name = \'Книга\';
$p->price = 599.99;',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Чем свойство отличается от метода?',
                'answer' => 'Свойство — переменная объекта, хранит данные (состояние): public string $name. Метод — функция объекта, описывает действие (поведение): public function greet() {...}. Грубо: свойство ХРАНИТ, метод ДЕЛАЕТ. Обращение к свойству — без скобок ($obj->name), к методу — со скобками ($obj->greet()).',
                'difficulty' => 1,
                'topic' => 'oop.basic_concepts',
                'code_example' => '<?php
class User
{
    public string $name = \'\';            // свойство (данные)

    public function greet(): string       // метод (действие)
    {
        return \'Hi, \' . $this->name;
    }
}

$u = new User();
$u->name = \'Иван\';     // обращение к свойству
echo $u->greet();      // вызов метода',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Что такое $this в методе?',
                'answer' => 'Ссылка на текущий объект, у которого вызвали метод. Через $this->property получаешь свойство ЭТОГО экземпляра, через $this->method() — вызываешь другой его метод. Доступен только внутри нестатических методов: в static-методах $this не существует.',
                'difficulty' => 1,
                'topic' => 'oop.basic_concepts',
                'code_example' => '<?php
class User
{
    public string $name = \'\';

    public function rename(string $newName): void
    {
        $this->name = $newName;       // свойство этого объекта
    }

    public function greet(): string
    {
        return \'Hi, \' . $this->name; // вызвалось у конкретного $u
    }
}

$u = new User();
$u->rename(\'Иван\');
echo $u->greet(); // Hi, Иван',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Что такое self и в чём разница с $this?',
                'answer' => 'self ссылается на текущий КЛАСС (а не на объект). Используется для доступа к статическим свойствам/методам и константам: self::COUNT, self::$instances. $this — на конкретный объект. Внутри обычного метода работают оба, в статическом методе $this недоступен.',
                'difficulty' => 2,
                'topic' => 'oop.basic_concepts',
            ],
            [
                'category' => 'ООП',
                'question' => 'Можно ли создать экземпляр абстрактного класса?',
                'answer' => 'Нет. new AbstractClass() даст Error: «Cannot instantiate abstract class». Абстрактный класс — только для наследования. Чтобы получить объект — наследуй конкретным классом и реализуй абстрактные методы, потом new ConcreteChild().',
                'difficulty' => 2,
                'topic' => 'oop.basic_concepts',
            ],
            [
                'category' => 'ООП',
                'question' => 'Как работает синтаксис extends простыми словами?',
                'answer' => 'class Admin extends User — класс Admin наследует все public/protected свойства и методы класса User. Может добавить новые или переопределить (override). PHP разрешает наследовать ТОЛЬКО ОДИН класс. Для нескольких контрактов — implements у интерфейсов, для подмешивания поведения — use trait.',
                'difficulty' => 1,
                'topic' => 'oop.basic_concepts',
                'code_example' => '<?php
class User
{
    public string $name = \'\';
    public function greet(): string { return \'Hi, \' . $this->name; }
}

class Admin extends User
{
    public function ban(string $reason): void { /* ... */ }
}

$a = new Admin();
$a->name = \'Root\';
echo $a->greet(); // унаследовано
$a->ban(\'spam\'); // своё',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Что такое parent:: и зачем оно нужно?',
                'answer' => 'parent:: — обращение к методу или константе РОДИТЕЛЯ из наследника. Чаще всего используется: 1) в конструкторе потомка позвать parent::__construct(...), чтобы инициализировать родительские свойства. 2) При override — позвать родительскую реализацию и добавить своё поведение: parent::save(); $this->log();.',
                'difficulty' => 1,
                'topic' => 'oop.basic_concepts',
                'code_example' => '<?php
class Repository
{
    public function save(): void { /* запись в БД */ }
}

class LoggingRepository extends Repository
{
    public function save(): void
    {
        parent::save();              // сначала родительская логика
        error_log(\'saved at \' . time()); // потом своё
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Что такое константа класса (const)?',
                'answer' => 'Неизменяемое значение, привязанное к классу. Объявляется через const, доступ через :: и имя класса. Имени без $ (это не переменная). По соглашению — UPPER_CASE. Может быть и в интерфейсе. С PHP 8.1 можно пометить final (запрет переопределения в потомке), с PHP 8.3 — типизировать.',
                'difficulty' => 1,
                'topic' => 'oop.basic_concepts',
                'code_example' => '<?php
class Status
{
    const ACTIVE = 1;
    const BANNED = 2;
    const int OK = 200;          // PHP 8.3: типизированная
    final const string VERSION = \'v1\'; // PHP 8.1: final
}

echo Status::ACTIVE;  // 1
echo Status::VERSION; // v1',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Что такое implements в PHP простыми словами?',
                'answer' => 'Ключевое слово, обозначающее реализацию интерфейса. class Email implements Sendable — класс Email обязан содержать все методы интерфейса Sendable. Можно реализовывать несколько: class Foo implements Sendable, Cacheable, Loggable. Любой класс, реализующий интерфейс, можно type-hint через имя интерфейса: function send(Sendable $s).',
                'difficulty' => 2,
                'topic' => 'oop.basic_concepts',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.basic_concepts',
                'difficulty' => 1,
                'question' => 'Что такое геттер и сеттер?',
                'answer' => 'Геттер (getter) — метод, который возвращает значение свойства: getName(): string. Сеттер (setter) — метод, который меняет значение свойства: setName(string $name): void. Зачем: свойство объявляют приватным, а доступ дают через методы — так можно валидировать вход, логировать, скрыть детали хранения. По соглашению имена начинаются с get/set, но в PHP это просто методы — никакой магии.',
                'code_example' => '<?php
class User
{
    private string $name = \'\';

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        if ($name === \'\') {
            throw new \InvalidArgumentException(\'Имя не может быть пустым\');
        }
        $this->name = $name;
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.basic_concepts',
                'difficulty' => 1,
                'question' => 'Что такое наследование простыми словами?',
                'answer' => 'Когда один класс (потомок) получает все public/protected свойства и методы другого класса (родителя). В PHP объявляется через extends: class Admin extends User. Потомок может добавить новые методы или переопределить родительские. PHP разрешает наследовать только ОДИН класс. Для нескольких контрактов — implements (интерфейсы), для шаринга методов — use (трейты).',
                'code_example' => '<?php
class User
{
    public function greet(): string { return \'Привет\'; }
}

class Admin extends User
{
    public function ban(): void { /* ... */ }
}

$a = new Admin();
echo $a->greet(); // унаследовано от User
$a->ban();        // своё',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.basic_concepts',
                'difficulty' => 2,
                'question' => 'Что такое переопределение метода (override)?',
                'answer' => 'Когда наследник заменяет реализацию метода родителя своей, сохраняя совместимую сигнатуру. Вызов через объект-наследник всегда идёт в его версию. К родительской реализации можно обратиться через parent::method(). С PHP 8.3 есть атрибут #[\\Override] — он не обязателен, но IDE и компилятор подскажут, если в имени опечатка и метода у родителя на самом деле нет.',
                'code_example' => '<?php
class Animal
{
    public function speak(): string { return \'звук\'; }
}

class Dog extends Animal
{
    #[\\Override]
    public function speak(): string { return \'гав\'; }
}

echo (new Dog())->speak(); // гав',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.basic_concepts',
                'difficulty' => 1,
                'question' => 'Зачем нужен интерфейс простыми словами?',
                'answer' => 'Интерфейс — список методов без реализации. Класс, который его implements, обязан реализовать все эти методы. Это контракт: кто получает Logger $log, знает, что у объекта есть метод log() — независимо от того, FileLogger это или NullLogger. Зачем: 1) подменяемость реализаций (DI, тесты с моками); 2) полиморфизм; 3) разные иерархии могут реализовать один интерфейс.',
                'code_example' => '<?php
interface Logger
{
    public function log(string $message): void;
}

class FileLogger implements Logger
{
    public function log(string $message): void { /* в файл */ }
}

class NullLogger implements Logger
{
    public function log(string $message): void { /* ничего */ }
}

function run(Logger $log): void { $log->log(\'work\'); }',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.basic_concepts',
                'difficulty' => 1,
                'question' => 'Что такое typehint (объявление типа) простыми словами?',
                'answer' => 'Указание ожидаемого типа аргумента, возвращаемого значения или свойства: function save(User $user): void. PHP при вызове проверит, что передан именно объект User (или его наследник/реализация интерфейса) — иначе TypeError. Делает контракты явными, помогает IDE и статанализу, ловит баги раньше.',
                'code_example' => '<?php
function greet(string $name): string
{
    return \'Привет, \' . $name;
}

greet(\'Иван\'); // OK
// greet(123);  // TypeError',
                'code_language' => 'php',
            ],
        ];
    }
}
