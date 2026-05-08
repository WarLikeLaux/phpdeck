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
        ];
    }
}
