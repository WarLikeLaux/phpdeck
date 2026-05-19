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
                'answer' => 'Абстрактный класс - класс, помеченный ключевым словом abstract, который нельзя инстанцировать напрямую. Может содержать как реализованные методы, так и абстрактные (без тела), которые обязаны реализовать потомки. Используется как частичная реализация: общая логика в родителе, специфика в потомках. В PHP класс может наследоваться только от одного абстрактного класса.',
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
                'answer' => 'Интерфейс — контракт: список методов без реализации. Класс через implements обязан реализовать все эти методы. Содержит сигнатуры методов и константы (с PHP 8.3 — типизированные), но не хранит данных. Один класс может реализовать несколько интерфейсов (множественное наследование контрактов). Интерфейсы — основа полиморфизма, DI и подменяемости реализаций (например, для тестов).',
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
                'answer' => 'Абстрактный класс может содержать реализацию методов и хранимые свойства; интерфейс — только сигнатуры методов и константы, без состояния. Класс может наследовать только ОДИН абстрактный класс, но реализовать МНОГО интерфейсов. Когда что брать: абстрактный класс — нужна общая частичная реализация для группы схожих классов (Animal с реализованным name() и абстрактным speak()). Интерфейс — описать поведение, не привязываясь к иерархии (классы из разных деревьев могут быть Comparable, Iterable).',
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
                'answer' => 'Константы — да (public const), свойств-полей нет. Все методы интерфейса публичные и без тела; модификатор abstract писать не нужно — он и так подразумевается. С PHP 8.1 константы можно объявлять final (запрет переопределения). С PHP 8.3 константы можно типизировать. Если нужны общие свойства между классами — используют абстрактный класс или трейт.',
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
                'answer' => 'Интерфейс — когда нужно описать ЧТО объект умеет делать, а как — пусть каждый решает сам. Можно реализовать сколько угодно интерфейсов сразу. Абстрактный класс — когда есть общий КОД и общие СВОЙСТВА для группы классов, и часть методов уже реализована, а часть оставлена на потомков. У класса может быть только один абстрактный родитель. Если сомневаешься — начни с интерфейса, он гибче.',
                'code_example' => null,
                'code_language' => null,
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.abstract_interfaces',
                'difficulty' => 2,
                'question' => 'Может ли абстрактный класс не иметь ни одного абстрактного метода?',
                'answer' => 'Да, может. Достаточно ключевого слова abstract перед class, чтобы запретить создавать объекты через new. Внутри могут быть только обычные реализованные методы. Такой класс — это «база только для наследования», явный сигнал «не инстанцировать напрямую, только потомков». Обратное тоже верно: если класс не объявлен abstract, но хотя бы один метод abstract — PHP выдаст fatal error.',
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
