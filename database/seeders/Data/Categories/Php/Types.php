<?php

namespace Database\Seeders\Data\Categories\Php;

class Types
{
    public static function all(): array
    {
        return [
            [
                'category' => 'PHP',
                'question' => 'Что такое nullable types и union types?',
                'answer' => 'Nullable type (?Type, PHP 7.1+) - значит может быть Type или null. Union type (Type1|Type2, PHP 8.0+) - может быть любым из перечисленных. Intersection (Type1&Type2, PHP 8.1+) - должен реализовать все. DNF (Disjunctive Normal Form, PHP 8.2+) - комбинация union и intersection с правильным порядком. mixed - любой тип. never - функция никогда не вернётся (выбросит/exit).',
                'code_example' => '<?php
// Nullable
function find(int $id): ?User {
    return $id > 0 ? new User() : null;
}

// Union (PHP 8)
function format(int|float|string $value): string {
    return (string) $value;
}

// Intersection (PHP 8.1)
function process(Countable&Iterator $items): void {
    echo count($items);
    foreach ($items as $item) {}
}

// DNF (PHP 8.2)
function handle((Countable&Iterator)|null $x): void {}

// never
function abort(string $msg): never {
    throw new RuntimeException($msg);
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.types',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое readonly свойства и классы?',
                'answer' => 'readonly свойство (PHP 8.1+) можно записать ровно один раз из области видимости класса (обычно в конструкторе, но не строго только там). После первой записи попытка изменить - Error. Идеально для immutable value objects. readonly класс (PHP 8.2+) - все нестатические свойства автоматически readonly. Ограничения: только типизированные свойства, нельзя статические. До PHP 8.3 для "обновлённой" копии использовался wither-метод, возвращающий new self(...). С PHP 8.3 (RFC "readonly amendments") readonly-свойства можно reinitialize СТРОГО внутри тела магического метода __clone() того класса, где они объявлены - то есть deep-cloning вложенных readonly-объектов и сброс кешированного state на копии стали возможны без обхода через Reflection. Важный нюанс: "reinitialize during cloning" означает именно ВНУТРИ __clone(), а не в любом коде после оператора clone - снаружи __clone() записать readonly-свойство по-прежнему Error.',
                'code_example' => '<?php
class Point {
    public function __construct(
        public readonly float $x,
        public readonly float $y,
    ) {}

    // ✅ Wither - канонический паттерн для immutable update, работает с 8.1
    public function withX(float $x): self {
        return new self($x, $this->y);
    }
}

$p = new Point(1, 2);
// $p->x = 5; // Error: cannot modify readonly

// ❌ ТАК НЕЛЬЗЯ даже на 8.3 - модификация снаружи __clone()
// public function withX(float $x): self {
//     $clone = clone $this;
//     $clone->x = $x; // Error: Cannot modify readonly property
//     return $clone;
// }

// ✅ PHP 8.3 - reinitialize ТОЛЬКО внутри __clone(); полезно для
// глубокого клонирования и сброса кеша на копии
class Order {
    public function __construct(
        public readonly DateTimeImmutable $createdAt,
        public readonly Money $total,
    ) {}

    public function __clone(): void {
        // OK - readonly можно записать в __clone того же класса
        $this->total = clone $this->total; // deep clone вложенного VO
    }
}

// PHP 8.2 readonly class
readonly class Coordinates {
    public function __construct(
        public float $lat,
        public float $lng,
    ) {}
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.types',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем intersection types отличаются от union types?',
                'answer' => 'Union types (PHP 8.0) задаются через | и позволяют значению быть одного из перечисленных типов, например int|string. Intersection types (PHP 8.1) задаются через & и требуют, чтобы объект реализовывал сразу все указанные интерфейсы или классы, например Countable&Iterator.',
                'difficulty' => 3,
                'topic' => 'php.types',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое тип возврата never в PHP 8.1 и чем он отличается от void?',
                'answer' => 'never говорит, что функция гарантированно не вернёт управление вызывающему коду — она либо бросит исключение, либо вызовет exit/die, либо уйдёт в бесконечный цикл. void же означает, что функция возвращает управление, но без значения. Поэтому never полезен для редиректов и thrower-функций: статический анализ и компилятор могут считать код после вызова недостижимым и не требовать там return.',
                'difficulty' => 3,
                'topic' => 'php.types',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое DNF-типы в PHP 8.2 и зачем нужна именно дизъюнктивная нормальная форма?',
                'answer' => 'DNF-types позволяют комбинировать union- и intersection-типы, но только в дизъюнктивной нормальной форме — то есть как ИЛИ из групп И, например (Countable&Traversable)|null. Каждая intersection-группа обязана быть в скобках, иначе синтаксис неоднозначен. Это снимает прежний запрет на смешение & и | и позволяет описывать вещи вроде «коллекция, либо null», не вводя промежуточный интерфейс.',
                'difficulty' => 4,
                'topic' => 'php.types',
            ],
            [
                'category' => 'PHP',
                'question' => 'Зачем PHP 8.2 разрешил null, false и true как самостоятельные типы?',
                'answer' => 'Раньше эти значения существовали только внутри union (например string|false), и нельзя было сказать «функция всегда возвращает false» иначе как через bool. Самостоятельные типы убирают этот пробел: alwaysFalse(): false точно описывает контракт legacy-функций вроде strpos и помогает статанализу. true появился для симметрии, а отдельный тип null официально оформил то, что и так использовалось на практике.',
                'difficulty' => 3,
                'topic' => 'php.types',
            ],
            [
                'category' => 'PHP',
                'question' => 'Зачем в PHP 8.4 объявили устаревшим неявный nullable у параметров?',
                'answer' => 'Конструкция function f(string $name = null) долгое время неявно делала параметр nullable, потому что null был совместим со значением по умолчанию. Это противоречило принципу «тип говорит правду» и было источником багов при рефакторинге. С 8.4 такой код выдаёт deprecation, и нужно писать ?string $name = null, явно указывая, что null допустим.',
                'difficulty' => 3,
                'topic' => 'php.types',
            ],
            [
                'category' => 'PHP',
                'question' => 'Какие значения PHP считает ложными при приведении к bool?',
                'answer' => 'К false приводятся: сам false, целые 0 и -0, числа с плавающей точкой 0.0 и -0.0, пустая строка "", строка "0" (именно из одного нолика), пустой массив, null и SimpleXML-объект, созданный из пустых тегов. Всё остальное — true, в том числе строки "false", "0.0", "00", "null" и массив с одним нулевым элементом. Это поведение не совпадает с empty() для строк "0.0" и " ": empty("0.0") вернёт false, а (bool)"0.0" — true.',
                'difficulty' => 2,
                'topic' => 'php.types',
            ],
            [
                'category' => 'PHP',
                'question' => 'Почему числа с плавающей точкой в PHP неточны и как корректно сравнивать float?',
                'answer' => 'PHP хранит float в формате IEEE 754 double, в двоичной системе. Многие десятичные дроби, такие как 0.1 и 0.2, в двоичном виде дают бесконечные периодические дроби, поэтому уже на этапе литерала теряется точность. В результате 0.1 + 0.2 даёт примерно 0.30000000000000004, и var_dump(0.1 + 0.2 == 0.3) выводит false. Корректное сравнение — через эпсилон: abs($a - $b) < PHP_FLOAT_EPSILON или другой малой константой, подходящей под предметную область.',
                'difficulty' => 3,
                'topic' => 'php.types',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем BCMath отличается от GMP и когда что выбирать?',
                'answer' => 'BCMath работает с числами произвольной точности, представленными как строки, и поддерживает дробную часть и масштаб (bcscale, bcadd, bccomp), поэтому подходит для денежных расчётов, где важна десятичная точность. GMP оперирует только целыми числами произвольной длины, использует объектный или ресурсный API и заметно быстрее BCMath за счёт нативной libgmp. Поэтому GMP выбирают для криптографии и работы с большими целыми, а BCMath — для финансов и точной десятичной арифметики, где деньги хранятся как DECIMAL в БД.',
                'difficulty' => 4,
                'topic' => 'php.types',
            ],
            [
                'category' => 'PHP',
                'question' => 'Почему деньги нельзя хранить в float и какой тип использовать в БД?',
                'answer' => 'Float в PHP и в большинстве СУБД — это IEEE 754 double, который не может точно представить такие значения, как 0.1 или 0.7. После цепочки сложений и умножений накапливается ошибка, и итог отличается от ожидаемого на доли копейки, что в финансовом учёте недопустимо. Поэтому деньги хранят либо как DECIMAL/NUMERIC с фиксированным масштабом (например, DECIMAL(19,4)), либо как целое число «в копейках/центах», а арифметику делают через BCMath или value-object Money с целыми. Float остаётся уделом научных расчётов, где приближённость допустима.',
                'difficulty' => 3,
                'topic' => 'php.types',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое zval и как он устроен в PHP 7+?',
                'answer' => 'zval — внутренняя структура Zend Engine, в которую завёрнута любая переменная PHP. В PHP 7 её сжали до 16 байт и состоит она из трёх частей: value (объединение размером 8 байт, где лежит lval/dval для скаляров или указатель на zend_string, zend_array, zend_object), u1 с тегом типа и флагами, и u2 для вспомогательных данных вроде следующего бакета хеш-таблицы. Refcount вынесен из zval в сами сложные структуры, поэтому скаляры (int, float, bool, null) хранятся прямо в zval без аллокаций в куче и без подсчёта ссылок.',
                'difficulty' => 4,
                'topic' => 'php.types',
            ],
        ];
    }
}
