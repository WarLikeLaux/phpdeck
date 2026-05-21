<?php

namespace Database\Seeders\Data\Categories\Oop;

class CouplingCohesion
{
    public static function all(): array
    {
        return [
            [
                'category' => 'ООП',
                'topic' => 'oop.coupling_cohesion',
                'difficulty' => 3,
                'question' => 'Что такое Law of Demeter (закон Деметры)?',
                'answer' => '**Law of Demeter (LoD, принцип минимального знания)** — метод должен общаться **только с непосредственными «друзьями»**, не лазая через них к чужим объектам.

**Формальная формулировка:** метод `$obj->m()` имеет право вызывать только методы:

1. **самого** `$obj` (`$this->...`).
2. Объектов, **переданных как параметры** в `m()`.
3. Объектов, **созданных внутри** `m()`.
4. **Полей** `$obj` (своих свойств).

Чужие объекты, полученные **через цепочку**, — за пределами «друзей».

**Признак нарушения — train wreck:**

```php
$user->getProfile()->getAddress()->getCity()->getName();
```

**Чем это плохо:**

- **Сильная связанность по транзитивности** — метод знает структуру `User`, `Profile`, `Address`, `City`.
- Изменение `City::getName()` ломает код, **ничего не знавший про `City`**.
- Нарушает **инкапсуляцию** — внутреннее устройство `User` протекает наружу.

**Решение — Tell, Don\'t Ask:** даём `User` свой метод-фасад над цепочкой, клиент обращается только к ближайшему другу.

**Когда LoD можно нарушать:** **Fluent-builder** (`$query->where()->orderBy()->limit()`) — каждый шаг возвращает **тот же объект** или однотипный билдер, это **не цепочка чужих типов**, а DSL.',
                'code_example' => '<?php
// Плохо - нарушение Law of Demeter
$user->getProfile()->getAddress()->getCity()->getName();

// Хорошо - даём User метод, скрывающий внутренности
class User
{
    public function cityName(): string
    {
        return $this->profile->cityName();
    }
}
$user->cityName();',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.coupling_cohesion',
                'difficulty' => 3,
                'question' => 'Что такое принцип "Tell, Don\'t Ask"?',
                'answer' => '**Tell, Don\'t Ask** — **скажи** объекту сделать, а **не спрашивай** у него данные, чтобы потом принять решение снаружи.

**Антипаттерн (Ask):**

```php
if ($order->getStatus() === \'new\' && $order->getTotal() > 0) {
    $order->setStatus(\'paid\');
}
```

Геттер → проверка → сеттер: **бизнес-правило живёт в сервисе**, а `Order` — анемичная сумка с полями.

**Корректный вариант (Tell):**

```php
$order->markAsPaid();
```

Логика «можно ли оплатить» — **внутри** `Order`. Снаружи только команда.

**Что даёт:**

- **Rich Domain Model** — поведение живёт там, где живут данные.
- Меньше **дублирования инвариантов** (правило «нельзя оплатить отменённый заказ» нигде не размажется).
- **Инкапсуляция** реально работает — `getStatus()`/`setStatus()` исчезают из API.
- Снижает риск **нарушить Law of Demeter** — клиент не лезет внутрь объекта.

**Когда Ask допустим:** **запросы для отображения** (UI берёт `$order->status` для рендера) — там просто читаем данные, а не принимаем бизнес-решение.',
                'code_example' => '<?php
// ❌ Ask - спрашиваем поля и решаем снаружи; правила размазаны по сервисам
class CheckoutService
{
    public function pay(Order $order): void
    {
        if ($order->getStatus() !== \'new\') {
            throw new \DomainException(\'wrong status\');
        }
        if ($order->getTotal() <= 0) {
            throw new \DomainException(\'empty order\');
        }
        $order->setStatus(\'paid\');
        $order->setPaidAt(new \DateTimeImmutable());
    }
}

// ✅ Tell - команда объекту; инварианты внутри Order
final class Order
{
    private string $status = \'new\';
    private ?\DateTimeImmutable $paidAt = null;

    public function __construct(private int $totalCents) {}

    public function markAsPaid(): void
    {
        if ($this->status !== \'new\') {
            throw new \DomainException(\'cannot pay: status \' . $this->status);
        }
        if ($this->totalCents <= 0) {
            throw new \DomainException(\'cannot pay: empty order\');
        }
        $this->status = \'paid\';
        $this->paidAt = new \DateTimeImmutable();
    }
}

class CheckoutService
{
    public function pay(Order $order): void
    {
        $order->markAsPaid(); // одна строка, правила внутри
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.coupling_cohesion',
                'difficulty' => 3,
                'question' => 'Что такое Coupling и Cohesion?',
                'answer' => 'Две **ортогональные** меры качества модульного дизайна.

| | **Coupling** (связанность) | **Cohesion** (сплочённость) |
|---|---|---|
| Про что | **между** модулями | **внутри** модуля |
| Хорошо | **низкая** (loose) | **высокая** |
| Признак | A зависит от B только через интерфейс | класс делает одно дело |
| Связан с | **DIP**, ISP, Law of Demeter | **SRP** |

**Цель проектирования:** **low coupling + high cohesion**.

**Coupling — почему важна:** сильно связанные модули невозможно менять, тестировать и переиспользовать по отдельности. Признаки сильной связанности — `new ConcreteClass()` прямо в методе, длинные цепочки `$a->getB()->getC()`, доступ к публичным полям другого класса.

**Cohesion — почему важна:** в классе с низкой сплочённостью методы и поля **используются клиентами по отдельности** — такой класс рано или поздно распадётся на куски. Имена `Utility`, `Helper`, `Manager` — типичный сигнал.

**Хитрость:** повышая cohesion (разбивая `UserService` на `UserRepository` + `Mailer` + `Logger`), вы автоматически **создаёте новые связи** между ними — coupling растёт. **Баланс:** связь через узкие интерфейсы оставляет coupling низким, а cohesion — высокой.

**Связь с SOLID:** **SRP ≈ высокая cohesion** (одна причина меняться). **DIP ≈ низкая coupling** (зависим от абстракции, а не от конкретного класса). **ISP** дополнительно снижает coupling — клиент не зависит от методов, которыми не пользуется.',
                'code_example' => '<?php
// Высокий coupling + низкий cohesion (плохо)
class UserManager
{
    public function register(array $data): void
    {
        $pdo = new \PDO(\'mysql:...\'); // прямая зависимость от PDO
        $pdo->exec(\'INSERT INTO users ...\');
        mail($data[\'email\'], \'Welcome\', \'...\'); // отправка почты тут же
        file_put_contents(\'/var/log/users.log\', \'...\'); // логи тут же
    }
}

// Низкий coupling + высокий cohesion (хорошо)
class UserRegistrar
{
    public function __construct(
        private UserRepository $users,   // абстракция
        private Mailer $mailer,
        private LoggerInterface $logger,
    ) {}

    public function register(User $user): void
    {
        $this->users->save($user);
        $this->mailer->sendWelcome($user);
        $this->logger->info(\'user.registered\', [\'id\' => $user->id]);
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Что такое Coupling (связанность) простыми словами?',
                'answer' => '**Coupling (связанность)** — насколько сильно один класс зависит от другого.

- **Низкая** (loose coupling) — общение через **интерфейсы**, класс знает только контракт. Замена реализации не ломает клиента.
- **Высокая** (tight coupling) — класс знает внутренности другого (поля, конкретные классы, цепочки методов). Изменение там → поломка здесь.

**Цель:** **loose coupling** — проще менять, тестировать (моки), переиспользовать.

**Признаки сильной связанности:**

- `new ConcreteClass()` прямо в методе.
- Длинные цепочки `$a->getB()->getC()->doX()` (Law of Demeter).
- Класс лезет в `public`-поля другого вместо вызова метода.',
                'difficulty' => 2,
                'topic' => 'oop.coupling_cohesion',
                'code_example' => '<?php
// ❌ Высокая связанность - сервис прибит к конкретному классу
class OrderServiceBad
{
    public function pay(int $cents): void
    {
        $gateway = new StripeGateway();   // тут конкретный класс
        $gateway->charge($cents);          // знает его метод
    }
}

// ✅ Низкая связанность - зависим от интерфейса
interface PaymentGateway { public function charge(int $cents): void; }

class OrderService
{
    public function __construct(private PaymentGateway $gateway) {}

    public function pay(int $cents): void
    {
        $this->gateway->charge($cents); // подменяемо: Stripe, PayPal, FakeGateway
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Что такое Cohesion (зацепление) простыми словами?',
                'answer' => '**Cohesion (сплочённость)** — насколько элементы внутри класса/модуля связаны **одной задачей**.

- **Высокая cohesion** — класс делает **одну вещь**, его методы и свойства логично связаны (`Mailer` — про отправку писем).
- **Низкая cohesion** — в одном классе намешано всё подряд (auth + email + парсинг CSV).

**Правило:** **высокая cohesion + низкая coupling = хороший дизайн**.

**Связь с SOLID:** высокая cohesion — это, по сути, **SRP** (Single Responsibility): один класс — одна причина для изменения.

**Признак низкой cohesion:** имя класса `Utility`, `Helper`, `Manager`; методы трудно объединить общим словом; части класса используются клиентами по отдельности.',
                'difficulty' => 2,
                'topic' => 'oop.coupling_cohesion',
                'code_example' => '<?php
// ❌ Низкая cohesion - класс делает всё подряд
class Utility
{
    public function calculateTax(float $sum): float {}
    public function sendEmail(string $to): void {}
    public function parseCsv(string $file): array {}
    public function hashPassword(string $pwd): string {}
}

// ✅ Высокая cohesion - каждый класс сосредоточен на одной задаче
class TaxCalculator
{
    public function calculate(float $sum): float { return $sum * 0.2; }
}
class Mailer
{
    public function send(string $to, string $body): void {}
}
class CsvParser
{
    public function parse(string $file): array { return []; }
}',
                'code_language' => 'php',
            ],
        ];
    }
}
