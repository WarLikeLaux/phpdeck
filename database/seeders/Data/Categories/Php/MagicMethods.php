<?php

namespace Database\Seeders\Data\Categories\Php;

class MagicMethods
{
    public static function all(): array
    {
        return [
            [
                'category' => 'PHP',
                'question' => 'Какие магические методы есть в PHP?',
                'answer' => 'Магические методы вызываются автоматически в специальных ситуациях, имеют префикс __. Основные: __construct/__destruct, __get/__set/__isset/__unset (для несуществующих свойств), __call/__callStatic (для несуществующих методов), __toString (приведение к строке), __invoke (вызов объекта как функции), __clone (после клонирования), __serialize/__unserialize (с PHP 7.4 - современная замена пары __sleep/__wakeup; новые проекты должны использовать только их). Точнее про статус: __sleep/__wakeup сами по себе НЕ помечены как deprecated в спеке - это просто legacy-стиль; формально deprecated в PHP 8.1 был именно интерфейс Serializable, который PHP советует менять на __serialize/__unserialize. Также есть __debugInfo (для var_dump) и __set_state (используется при eval(var_export($x, true))). Пара __serialize/__unserialize имеет смысл задавать ВМЕСТЕ — иначе магия частично не работает. Минус магических методов: непрозрачны, тяжелее анализировать.',
                'code_example' => '<?php
class Container {
    private array $data = [];

    public function __get(string $name) {
        return $this->data[$name] ?? null;
    }

    public function __set(string $name, $value): void {
        $this->data[$name] = $value;
    }

    public function __isset(string $name): bool {
        return isset($this->data[$name]);
    }

    public function __call(string $method, array $args) {
        echo "Вызван несуществующий: $method";
    }

    public function __toString(): string {
        return json_encode($this->data);
    }

    public function __invoke(string $key) {
        return $this->data[$key] ?? null;
    }
}

$c = new Container();
$c->name = "Иван";    // __set
echo $c->name;        // __get
echo $c;              // __toString
echo $c("name");      // __invoke',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.magic_methods',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работает __invoke и зачем он нужен?',
                'answer' => '__invoke позволяет вызывать объект как функцию. Простыми словами: добавляешь метод __invoke - и можно писать $obj() вместо $obj->method(). Полезно для callable-объектов: handlers, action-классы, single-action invokables в Laravel, middleware. Объект с __invoke считается callable, его можно передавать туда, где ожидается функция.',
                'code_example' => '<?php
class Multiplier {
    public function __construct(private int $factor) {}

    public function __invoke(int $x): int {
        return $x * $this->factor;
    }
}

$double = new Multiplier(2);
echo $double(5);  // 10 - вызвали как функцию

// Передача в функции, ожидающие callable
$nums = [1, 2, 3, 4];
$result = array_map($double, $nums);
// [2, 4, 6, 8]

// Паттерн Single Action в Laravel
class CreateUserAction {
    public function __invoke(array $data): User {
        return User::create($data);
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.magic_methods',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое clone и как работает магический __clone?',
                'answer' => 'clone создаёт ПОВЕРХНОСТНУЮ копию объекта - все свойства копируются. НО: вложенные объекты копируются по ссылке-идентификатору (то есть указывают на тот же объект). Для глубокой копии нужно реализовать __clone и в нём вручную клонировать вложенные объекты. __clone вызывается автоматически после копирования свойств.',
                'code_example' => '<?php
class Address {
    public string $city = "Moscow";
}

class User {
    public Address $address;
    public function __construct() {
        $this->address = new Address();
    }
}

$a = new User();
$b = clone $a;
$b->address->city = "SPB";
echo $a->address->city; // "SPB"! - тот же объект

// Глубокая копия
class UserDeep {
    public Address $address;
    public function __clone(): void {
        $this->address = clone $this->address;
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.magic_methods',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что делает __destruct()?',
                'answer' => 'Специальный метод, который PHP вызывает АВТОМАТИЧЕСКИ когда объект удаляется (последняя ссылка ушла или скрипт закончился). Используется для очистки: закрыть файл, отвязать соединение. На практике редко нужен — обычно вместо него используют try/finally.',
                'difficulty' => 2,
                'topic' => 'php.magic_methods',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что делает __toString() в PHP?',
                'answer' => 'Позволяет объекту вести себя как строка: echo $obj автоматически вызовет $obj->__toString(). Удобно для классов вроде Money, Email, Url — превратить объект в человекочитаемое представление. С PHP 8 объект автоматически реализует Stringable, если есть __toString.',
                'code_example' => 'class Money {
    public function __construct(public int $amount, public string $currency) {}
    public function __toString(): string {
        return "{$this->amount} {$this->currency}";
    }
}
echo new Money(100, "USD"); // "100 USD"',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.magic_methods',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что делают __get и __set в PHP простыми словами?',
                'answer' => '__get($name) вызывается при чтении НЕСУЩЕСТВУЮЩЕГО свойства: echo $obj->unknown — PHP вместо ошибки вызовет __get("unknown"). __set($name, $value) — при записи. Используется для динамических свойств (контейнеры, прокси), но усложняет анализ кода — обычно лучше явные свойства.',
                'difficulty' => 2,
                'topic' => 'php.magic_methods',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что делает __construct() простыми словами?',
                'answer' => 'Магический метод-конструктор, который PHP АВТОМАТИЧЕСКИ вызывает при создании объекта через new. В нём обычно инициализируют свойства из переданных аргументов. С PHP 8 есть constructor property promotion — модификатор видимости прямо в параметре сразу делает его свойством, без отдельного объявления и присваивания. Конструктор родителя НЕ вызывается автоматически — нужно явно parent::__construct().',
                'code_example' => '<?php
// Классический вид
class UserOld {
    public string $name;
    public function __construct(string $name) {
        $this->name = $name;
    }
}

// PHP 8: property promotion
class User {
    public function __construct(public string $name, public int $age) {}
}

$u = new User("Иван", 30);
echo $u->name; // "Иван"',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.magic_methods',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что делает __call() в PHP простыми словами?',
                'answer' => 'Магический метод, который вызывается при попытке обратиться к НЕСУЩЕСТВУЮЩЕМУ или недоступному методу объекта: $obj->unknown($x) → $obj->__call("unknown", [$x]). Без __call PHP выбросит Error. Применяется для динамических прокси, фасадов, fluent-API: например, Eloquent через __call перехватывает where(), orderBy() и пересылает их в QueryBuilder. Есть и парный __callStatic для статических вызовов: ClassName::unknown() → ClassName::__callStatic("unknown", [...]).',
                'code_example' => 'class Proxy {
    public function __call(string $name, array $args): mixed {
        echo "Вызван $name с " . count($args) . " аргументами";
        return null;
    }
}
$p = new Proxy();
$p->doSomething(1, 2, 3); // "Вызван doSomething с 3 аргументами"',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.magic_methods',
            ],
        ];
    }
}
