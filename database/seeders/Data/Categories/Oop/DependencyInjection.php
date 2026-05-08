<?php

namespace Database\Seeders\Data\Categories\Oop;

class DependencyInjection
{
    public static function all(): array
    {
        return [
            [
                'category' => 'ООП',
                'topic' => 'oop.dependency_injection',
                'difficulty' => 3,
                'question' => 'Что такое Dependency Injection (DI)?',
                'answer' => 'Dependency Injection (внедрение зависимостей) - это техника, при которой объект получает свои зависимости извне, а не создаёт их сам. Виды: 1) Constructor Injection (через конструктор) - предпочтительный. 2) Setter Injection (через setter). 3) Property Injection (на практике обычно аннотированное приватное поле через рефлексию, как Symfony #[Required]; публичные поля как DI - редкая и плохая практика). DI - это паттерн, позволяющий следовать DIP. Делает код тестируемым, гибким, слабо связанным.',
                'code_example' => '<?php
// Без DI: жёсткая зависимость
class OrderServiceBad
{
    public function process(): void
    {
        $repo = new OrderRepository(); // вшито
    }
}

// С DI: зависимость передана извне
class OrderService
{
    public function __construct(
        private OrderRepository $repo,
        private Mailer $mailer,
    ) {}
}

// Передаём зависимости явно
$service = new OrderService(
    new OrderRepository(),
    new SmtpMailer()
);',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.dependency_injection',
                'difficulty' => 3,
                'question' => 'В чём разница между DI и DIP?',
                'answer' => 'Это разные понятия. DIP (Dependency Inversion Principle) - принцип проектирования: зависим от абстракций, а не от конкретных классов. DI (Dependency Injection) - техника, способ передавать зависимости в объект (через конструктор, сеттер). Можно соблюдать DIP без DI (например, создавая абстракции вручную). Можно использовать DI без соблюдения DIP (передавать конкретные классы). На практике DI - один из главных способов реализации DIP.',
                'code_example' => '<?php
// DI без DIP: инъекция есть, но зависим от конкретного класса
class OrderServiceA
{
    public function __construct(private MysqlOrderRepository $repo) {}
}

// DIP без DI: зависим от абстракции, но создаём её внутри
class OrderServiceB
{
    private OrderRepository $repo;
    public function __construct()
    {
        $this->repo = RepositoryFactory::make(); // не DI, но абстракция
    }
}

// DIP + DI (идеал): и абстракция, и инъекция
class OrderService
{
    public function __construct(private OrderRepository $repo) {}
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.dependency_injection',
                'difficulty' => 3,
                'question' => 'Что такое Inversion of Control (IoC)?',
                'answer' => 'IoC (инверсия управления) - принцип, при котором управление потоком программы передаётся фреймворку или контейнеру, а не пишется в коде приложения. Простыми словами: "не звоните нам, мы позвоним вам" - вы пишете компоненты, а фреймворк решает, когда их вызвать. Примеры IoC: фреймворк вызывает ваш контроллер, DI-контейнер создаёт объекты, hooks/события дёргают ваши обработчики. DI - это одна из техник реализации IoC (по Фаулеру).',
                'code_example' => null,
                'code_language' => null,
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.dependency_injection',
                'difficulty' => 3,
                'question' => 'Что такое Service Locator и почему его считают анти-паттерном?',
                'answer' => 'Service Locator - объект-реестр, в котором по ключу можно получить нужный сервис: $locator->get(\'mailer\'). Считается анти-паттерном потому что: 1) Скрывает зависимости класса (по конструктору не видно, что нужно). 2) Усложняет тестирование (надо мокать локатор и регистрировать в нём). 3) Связывает класс с локатором. 4) Нарушает SRP. Альтернатива - Constructor Injection: явные зависимости через параметры. Laravel App container можно использовать как Service Locator (плохо) или как DI-контейнер (хорошо, через type-hint в конструкторе).',
                'code_example' => '<?php
// Анти-паттерн Service Locator
class OrderService
{
    public function process(): void
    {
        $repo = ServiceLocator::get(\'orderRepo\'); // скрытая зависимость
        $repo->save(\'...\');
    }
}

// Хорошо: явная зависимость
class OrderServiceGood
{
    public function __construct(private OrderRepository $repo) {}
    public function process(): void
    {
        $this->repo->save(\'...\');
    }
}',
                'code_language' => 'php',
            ],
        ];
    }
}
