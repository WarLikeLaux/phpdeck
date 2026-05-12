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
                'answer' => 'Интерфейс - это контракт, описывающий какие методы должен иметь класс, но не их реализацию. Класс реализует интерфейс через implements и обязан реализовать все его методы. Интерфейс содержит только сигнатуры методов и константы (с PHP 8.3 - типизированные константы), а с PHP 8.4 может также объявлять виртуальные свойства через property hooks - класс-реализация обязан предоставить get/set хуки. Своего хранимого состояния (полей с данными) у интерфейса по-прежнему нет - это контракт, а не структура данных. PHP позволяет одному классу реализовывать несколько интерфейсов - это форма множественного наследования контрактов. Интерфейсы - основа полиморфизма и DI.',
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
                'answer' => 'Абстрактный класс может содержать реализацию методов и хранимые свойства; интерфейс содержит сигнатуры методов, константы и (с PHP 8.4) объявления виртуальных свойств через property hooks - но без собственного состояния. Класс может наследоваться только от одного абстрактного класса, но реализовывать множество интерфейсов. Абстрактный класс используют, когда нужна общая частичная реализация для группы схожих классов. Интерфейс - когда нужно описать поведение, не привязываясь к иерархии (например, классы из совершенно разных иерархий могут быть Comparable).',
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
                'answer' => 'Интерфейс может содержать константы (public const), но НЕ может содержать свойства (поля). Все методы интерфейса должны быть public и не могут иметь тело (модификатор abstract указывать НЕ нужно - он подразумевается; явное private/protected или abstract на методе интерфейса - синтаксическая ошибка). С PHP 8.1 константы интерфейсов можно объявлять как final (запрет переопределения в реализациях). С PHP 8.3 константы можно типизировать (const int OK = 200). С PHP 8.4 интерфейсы могут описывать абстрактные свойства с геттерами/сеттерами через property hooks. Для общих свойств между классами традиционно используют абстрактный класс или трейт.',
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
        ];
    }
}
