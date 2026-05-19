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
                'answer' => 'Закон Деметры (принцип минимального знания): объект должен взаимодействовать только с непосредственными "друзьями", не лазить через них к чужим объектам. Простыми словами: говорите только со своими ближайшими объектами. Признак нарушения - длинные цепочки $a->getB()->getC()->doSomething() (train wreck). Это создаёт сильную связность - изменения в C ломают код, который ничего о C не знал. Решение - дать $a метод, который сам обратится к C, инкапсулируя цепочку.',
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
                'answer' => '"Tell, Don\'t Ask" - не спрашивай у объекта данные, чтобы потом принять решение - скажи ему сделать. Простыми словами: вместо "получить статус и проверить можно ли отменить" - "попроси заказ отменить себя". Это ведёт к более инкапсулированному коду: бизнес-логика живёт внутри объектов, а не размазана по сервисам. Тесно связан с Rich-моделью и Law of Demeter.',
                'code_example' => '<?php
// Ask - спрашиваем и решаем снаружи
if ($order->getStatus() === \'new\' && $order->getTotal() > 0) {
    $order->setStatus(\'paid\');
}

// Tell - говорим объекту что делать
$order->markAsPaid();',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.coupling_cohesion',
                'difficulty' => 3,
                'question' => 'Что такое Coupling и Cohesion?',
                'answer' => 'Coupling (зацепление, связанность) - степень зависимости одного модуля от другого. Чем сильнее coupling - тем труднее менять код, тестировать, переиспользовать. Стремимся к loose coupling (слабой связности). Cohesion (сплочённость) - насколько элементы внутри модуля связаны общей задачей. Высокая cohesion - модуль делает одно дело хорошо. Низкая - смесь несвязанных функций. Цель ООП: low coupling + high cohesion. Это коррелирует с SRP (cohesion) и DIP (coupling). Хитрость: повышая cohesion (выделяя ответственности в отдельные классы), часто непреднамеренно повышаем и coupling между ними - баланс важен.',
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
                'answer' => 'Степень зависимости одного класса от другого. Низкая связанность — модули общаются через интерфейсы, замена одного не ломает другие. Высокая — класс знает внутренности другого, изменение там — поломка тут. Цель: слабая связанность (loose coupling), потому что код легче менять, тестировать, переиспользовать.',
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
                'answer' => 'Степень, насколько элементы одного класса/модуля связаны между собой одной задачей. Высокая cohesion — класс делает ОДНУ вещь, его методы и свойства логично связаны. Низкая — в одном классе всё подряд (auth + email + logging). Цель: высокая cohesion + низкая coupling = хороший дизайн.',
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
