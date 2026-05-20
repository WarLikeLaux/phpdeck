<?php

namespace Database\Seeders\Data\Categories\Oop;

class Visibility
{
    public static function all(): array
    {
        return [
            [
                'category' => 'ООП',
                'topic' => 'oop.visibility',
                'difficulty' => 2,
                'question' => 'Какие модификаторы видимости есть в PHP?',
                'answer' => 'В PHP **три** модификатора видимости:

- **`public`** — доступ **отовсюду**: внутри класса, в потомках, снаружи через объект.
- **`protected`** — только внутри класса и его **наследников**.
- **`private`** — только **внутри объявившего класса** (даже потомки не видят).

**Особенности:**

- Если модификатор не указан — по умолчанию `public`.
- Работают на уровне **класса, а не экземпляра**: `private`-метод объекта `A` может читать `private` поля другого `A`.
- **PHP 8.4**: асимметричная видимость свойств — `public private(set) int $id` позволяет читать всем, а писать только изнутри. Убирает связку «private field + public getter».',
                'code_example' => '<?php
class Example
{
    public string $publicProp = \'видно везде\';
    protected string $protectedProp = \'видно в классе и потомках\';
    private string $privateProp = \'видно только в этом классе\';

    // PHP 8.4: асимметричная видимость
    public private(set) int $id = 0; // читать всем, писать только изнутри

    public function show(): void { /* доступ ко всем трём */ }
}

$e = new Example();
echo $e->publicProp;    // OK
echo $e->id;            // OK (read)
// $e->id = 42;         // Error: write only inside class
// echo $e->privateProp; // Error: private',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.visibility',
                'difficulty' => 1,
                'question' => 'Что такое public простыми словами?',
                'answer' => '`public` — свойство или метод доступны **откуда угодно**:

- из самого класса
- из потомков
- снаружи через объект: `$obj->method()`

Это «открытая» часть класса — его **публичный API**, на который полагается внешний код. Если не указать модификатор — PHP считает `public` по умолчанию.',
                'code_example' => '<?php
class User
{
    public string $name = \'\';

    public function greet(): string
    {
        return \'Привет, \' . $this->name;
    }
}

$u = new User();
$u->name = \'Иван\';      // OK: чтение/запись снаружи
echo $u->greet();        // OK: вызов снаружи',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.visibility',
                'difficulty' => 1,
                'question' => 'Что такое private простыми словами?',
                'answer' => '`private` — свойство или метод видны **ТОЛЬКО** внутри того класса, где объявлены.

- Наследники их **не видят**.
- Внешний код их **не видит**.

Используется для **деталей реализации**, которые нельзя трогать снаружи: меняй внутренности класса смело — никто на них не завязан.',
                'code_example' => '<?php
class Account
{
    private int $balance = 0;

    public function deposit(int $amount): void
    {
        $this->balance += $amount; // OK: внутри своего класса
    }
}

$a = new Account();
$a->deposit(100);
// echo $a->balance; // Error: Cannot access private property',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.visibility',
                'difficulty' => 1,
                'question' => 'Что такое protected простыми словами?',
                'answer' => '`protected` — свойство или метод видны:

- внутри **самого класса**
- внутри его **наследников**
- **НЕ** снаружи

Используется, когда хочется дать потомкам доступ для расширения, но скрыть от чужого кода. Это «полузакрытое» состояние — для иерархии классов.',
                'code_example' => '<?php
class Base
{
    protected string $name = \'base\';
}

class Child extends Base
{
    public function show(): string
    {
        return $this->name; // OK: потомок видит protected
    }
}

$c = new Child();
echo $c->show();       // OK
// echo $c->name;      // Error: protected недоступен снаружи',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.visibility',
                'difficulty' => 2,
                'question' => 'В чём разница между protected и private?',
                'answer' => 'Главный критерий — **видят ли потомки**.

- **`protected`** — видно в объявившем классе **И во всех наследниках**.
- **`private`** — видно **ТОЛЬКО** в том классе, где объявлено. Потомки не видят.

**Тонкость:** оба работают на уровне **класса**, а не экземпляра — `private`-метод объекта `A` может прочитать `private`-поле другого объекта `A`.

**Когда что брать:**

- **`private`** по умолчанию — детали реализации, не хотим связывать наследников.
- **`protected`** — если осознанно проектируем точку расширения для наследников.

На практике `private` предпочтительнее: меньше связности, проще менять внутренности.',
                'code_example' => '<?php
class Base
{
    private string $secret = \'тайна\';
    protected string $shared = \'для потомков\';
}

class Child extends Base
{
    public function test(): void
    {
        echo $this->shared; // OK
        // echo $this->secret; // Ошибка - private недоступен
    }
}',
                'code_language' => 'php',
            ],
        ];
    }
}
