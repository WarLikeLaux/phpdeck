<?php

namespace Database\Seeders\Data\Categories\Oop;

class CleanCode
{
    public static function all(): array
    {
        return [
            [
                'category' => 'ООП',
                'question' => 'Как бы вы рефакторили старый перегруженный класс в монолите?',
                'answer' => 'Сначала покройте текущее поведение тестами, чтобы ловить регрессии. Затем выделите ответственности класса в отдельные классы по SRP, а зависимости вынесите через Dependency Injection, чтобы ослабить связанность и сделать код тестируемым. Двигайтесь маленькими шагами, прогоняя тесты после каждого изменения, и применяйте паттерны (Strategy, Factory, Service-объекты) для упрощения сложной условной логики.',
                'difficulty' => 4,
                'topic' => 'oop.clean_code',
            ],
            [
                'category' => 'ООП',
                'question' => 'Что такое говорящие имена и почему они важны в чистом коде?',
                'answer' => 'Имя должно раскрывать намерение: что переменная или функция значат и зачем существуют. Имена вроде $temp, $a1, processData() заставляют читателя реконструировать смысл по контексту, тогда как $invoiceTotal или markInvoiceAsPaid() убирают необходимость в комментарии. Хорошие имена - это первый и самый дешёвый уровень документации кода.',
                'difficulty' => 2,
                'topic' => 'oop.clean_code',
                'code_example' => '<?php
// ❌ Имена ничего не говорят
function p(array $d): float
{
    $t = 0;
    foreach ($d as $x) {
        $t += $x[\'q\'] * $x[\'pr\'];
    }
    return $t * 1.2;
}

// ✅ Имена раскрывают намерение - комментарии не нужны
function totalWithTax(array $orderItems): float
{
    $subtotal = 0;
    foreach ($orderItems as $item) {
        $subtotal += $item[\'quantity\'] * $item[\'price\'];
    }
    return $subtotal * 1.2; // VAT 20%
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Каким должен быть размер функции по принципам чистого кода?',
                'answer' => 'Функция должна быть компактной и делать ровно одну вещь - тот же Single Responsibility, но на уровне функции. Если внутри есть несколько уровней абстракции, разные «и» в описании или длинные блоки внутри ветвей if, это сигнал выделить части в отдельные функции с осмысленными именами. Маленькие функции легче читать, переиспользовать и покрывать тестами.',
                'difficulty' => 2,
                'topic' => 'oop.clean_code',
                'code_example' => '<?php
// ❌ Большая функция - сразу валидация, считалка, рассылка
function placeOrder(array $data): void
{
    if (empty($data[\'email\']) || ! filter_var($data[\'email\'], FILTER_VALIDATE_EMAIL)) {
        throw new \InvalidArgumentException();
    }
    $total = 0;
    foreach ($data[\'items\'] as $i) $total += $i[\'price\'] * $i[\'qty\'];
    $total *= 1.2;
    mail($data[\'email\'], \'Order\', "Total: $total");
}

// ✅ Разбили на маленькие функции - каждая делает одно
function placeOrder(array $data): void
{
    validateOrder($data);
    $total = calculateTotal($data[\'items\']);
    sendConfirmation($data[\'email\'], $total);
}

function validateOrder(array $d): void { /* проверки */ }
function calculateTotal(array $items): float { /* сумма */ return 0; }
function sendConfirmation(string $email, float $total): void { /* email */ }',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Сколько аргументов должна принимать функция и почему?',
                'answer' => 'По Clean Code Мартина: идеал — 0, нормально — 1-2, 3 — подозрительно, 4+ почти всегда означают, что функция делает слишком много или что аргументы пора сгруппировать в объект (Parameter Object). Чем больше параметров — тем легче перепутать порядок (особенно одинаковых типов), труднее читать вызов и комбинаторно растёт число случаев в тестах. Булевы флаги отдельно: function save($u, bool $sendEmail) — почти всегда сигнал разбить на два метода. Группировка в объект (DTO/VO) ещё и даёт типобезопасность.',
                'difficulty' => 3,
                'topic' => 'oop.clean_code',
                'code_example' => '<?php
// ❌ 6 параметров - легко перепутать порядок одинаковых типов
function createUser(
    string $name, string $email, string $phone,
    string $city, string $street, int $age,
): User {
    return new User(/* ... */);
}
// createUser("Иван", "ivan@x.ru", "Москва", "+79991234567", "Тверская", 30);
// перепутали phone и city - PHP не заметит

// ✅ Сгруппировали в Parameter Object - типы ловят ошибки
final readonly class Address
{
    public function __construct(public string $city, public string $street) {}
}

final readonly class CreateUserDto
{
    public function __construct(
        public string $name,
        public Email $email,
        public Phone $phone,
        public Address $address,
        public int $age,
    ) {}
}

function createUser(CreateUserDto $dto): User { /* ... */ }

// ❌ Булев флаг = две функции в одной
function save(User $u, bool $sendEmail): void {}

// ✅ Разделили - имя метода говорит, что произойдёт
function save(User $u): void {}
function saveAndNotify(User $u): void {}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Когда стоит писать комментарии, а когда они лишние?',
                'answer' => 'Комментарий уместен тогда, когда мысль действительно нельзя выразить кодом: бизнес-причина решения, ссылка на спецификацию, объяснение нетривиального обходного пути. «Шумовые» комментарии вроде // increment i дублируют код и быстро расходятся с ним. TODO-метки допустимы умеренно и должны иметь владельца; в остальных случаях лучше переписать код так, чтобы он сам объяснял себя через имена и структуру.',
                'difficulty' => 2,
                'topic' => 'oop.clean_code',
            ],
            [
                'category' => 'ООП',
                'question' => 'Почему исключения предпочтительнее кодов ошибок?',
                'answer' => 'Коды ошибок смешивают happy path с обработкой ошибок: вызывающий ОБЯЗАН каждый раз проверить возврат, и забытая проверка тихо ломает программу. Исключения отделяют успех от сбоя, всплывают вверх по стеку до подходящего catch и не дают «потерять» ошибку. Сигнатура метода остаётся читаемой: метод возвращает РЕЗУЛЬТАТ, а не пару «значение или код». В PHP типизированная иерархия исключений + try/catch с union-типами (PHP 8) даёт компактный точечный отлов нужных ошибок. Стоимость: bubble-up через много слоёв иногда дороже простого if, поэтому исключения — для ИСКЛЮЧИТЕЛЬНЫХ ситуаций, а не штатных веток логики.',
                'difficulty' => 3,
                'topic' => 'oop.clean_code',
                'code_example' => '<?php
// ❌ Коды ошибок - читаемость страдает, забытые проверки = тихие баги
function findUser(int $id): array
{
    if ($id <= 0) return [\'error\' => \'invalid_id\', \'user\' => null];
    $user = DB::find($id);
    if (! $user) return [\'error\' => \'not_found\', \'user\' => null];
    return [\'error\' => null, \'user\' => $user];
}

$result = findUser(42);
// если забыть проверку - $result["user"] === null, и упадём дальше
echo $result[\'user\']->name;

// ✅ Исключения - happy path не замусорен, ошибки нельзя «потерять»
function findUser(int $id): User
{
    if ($id <= 0) throw new InvalidArgumentException("id must be positive");
    return DB::find($id) ?? throw new UserNotFoundException($id);
}

try {
    $user = findUser(42);
    echo $user->name; // линейный happy path
} catch (UserNotFoundException $e) {
    // конкретный сценарий
} catch (InvalidArgumentException | DatabaseException $e) {
    // union-catch с PHP 8
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Почему стараются не возвращать null из методов?',
                'answer' => 'Null — «миллиардная ошибка» (Тони Хоар): заставляет каждого вызывающего помнить про проверку, порождает россыпь if ($x === null) по всему коду, а забытая проверка превращается в TypeError на проде. Стратегии: 1) бросить исключение, если отсутствие значения — ошибка (findOrFail). 2) Вернуть Null Object (NullLogger, GuestUser), если поведение «ничего не делать» — норма. 3) Вернуть пустую коллекцию вместо null для списков (никаких array $items ?? []). 4) Optional/Maybe-обёртка для явного флага «есть/нет». Контракт метода становится явным, клиент пишет меньше защитных проверок.',
                'difficulty' => 3,
                'topic' => 'oop.clean_code',
                'code_example' => '<?php
// ❌ Возврат null - клиент обязан помнить про проверку
class UserRepoBad
{
    public function find(int $id): ?User { /* ... */ return null; }
    public function getActiveUsers(): ?array { /* ... */ return null; }
}

$user = $repo->find(1);
echo $user->name; // TypeError если забыли if($user)

foreach ($repo->getActiveUsers() as $u) {} // TypeError на null

// ✅ Исключение, если отсутствие = ошибка (контракт явный)
class UserRepo
{
    public function findOrFail(int $id): User
    {
        return $this->find($id) ?? throw new UserNotFoundException($id);
    }

    // ✅ Пустая коллекция вместо null для списков
    /** @return User[] */
    public function getActiveUsers(): array
    {
        return $this->query->where(\'active\', true)->get() ?: [];
    }
}

// ✅ Null Object для «ничего не делать»
class GuestUser extends User
{
    public function can(string $perm): bool { return false; }
    public function email(): string { return \'\'; }
}

function current(): User { return Auth::user() ?? new GuestUser(); }',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Что такое Early Return и зачем его применяют?',
                'answer' => 'Early Return - стиль, при котором краевые условия и невалидные входы обрабатываются в начале функции через return или throw, а основной сценарий идёт ровным потоком на нулевом уровне вложенности. Это убирает «лестницу» из вложенных if-else, снижает когнитивную нагрузку при чтении и уменьшает шанс пропустить ветку. По сути - «обрабатывай ошибки сразу, не откладывай».',
                'difficulty' => 2,
                'topic' => 'oop.clean_code',
                'code_example' => '<?php
// ❌ Лестница из вложенных if - основная логика глубоко
function charge(?User $user, ?Order $order): bool
{
    if ($user !== null) {
        if ($user->isActive()) {
            if ($order !== null) {
                if ($order->total > 0) {
                    return $user->pay($order); // спрятано на 4 уровня
                }
            }
        }
    }
    return false;
}

// ✅ Early return - guard clauses вверху, основная логика плоская
function charge(?User $user, ?Order $order): bool
{
    if ($user === null)         return false;
    if (! $user->isActive())    return false;
    if ($order === null)        return false;
    if ($order->total <= 0)     return false;

    return $user->pay($order); // happy path - нулевая вложенность
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Что такое правило бойскаута в разработке?',
                'answer' => 'Правило бойскаута гласит: оставляй код чище, чем он был до тебя. На практике это означает, что при любом касании файла можно по мелочи улучшить имя, выделить функцию, удалить мёртвый код или дописать тест - даже если задача не про рефакторинг. Так кодовая база медленно, но непрерывно деградирует в обратную сторону: вместо постепенного гниения она постепенно становится чище.',
                'difficulty' => 2,
                'topic' => 'oop.clean_code',
            ],
            [
                'category' => 'ООП',
                'question' => 'Зачем оборачивать сторонние API в собственные абстракции?',
                'answer' => 'Прямые вызовы Guzzle/Stripe SDK/AwsClient из бизнес-кода превращают чужую библиотеку в неявную зависимость всего проекта: смена версии задевает десятки файлов, замена провайдера (Stripe → Cloudpayments) — переписывание сервисов. Тонкая обёртка с собственным интерфейсом на ЯЗЫКЕ ДОМЕНА (PaymentGateway::charge(Money, Card), а не Stripe::createCharge(array)) даёт три выигрыша: 1) изоляция — менять реализацию можно без правки клиентов. 2) Тестируемость — в юнит-тестах подсовываем FakePaymentGateway вместо мока чужого SDK. 3) Чистый домен — никаких Stripe-типов в сигнатурах сервисов. Цена — лишний слой. Не оборачивай тривиальные утилиты ради «может пригодится» (YAGNI), оборачивай зависимости, которые реально могут меняться или важны для тестов.',
                'difficulty' => 3,
                'topic' => 'oop.clean_code',
                'code_example' => '<?php
// ❌ Stripe SDK торчит из бизнес-сервиса
class OrderServiceBad
{
    public function pay(Order $order): void
    {
        $stripe = new \Stripe\StripeClient(config(\'stripe.key\'));
        $stripe->charges->create([
            \'amount\' => $order->total * 100,
            \'currency\' => \'usd\',
            \'source\' => $order->cardToken,
        ]);
        // Сменить провайдера? Переписывать все сервисы.
        // Тестировать? Мокать чужой SDK.
    }
}

// ✅ Свой интерфейс на языке домена
interface PaymentGateway
{
    public function charge(Money $amount, CardToken $card): PaymentResult;
}

final class StripeGateway implements PaymentGateway
{
    public function __construct(private \Stripe\StripeClient $stripe) {}

    public function charge(Money $amount, CardToken $card): PaymentResult
    {
        $charge = $this->stripe->charges->create([
            \'amount\' => $amount->cents,
            \'currency\' => strtolower($amount->currency->value),
            \'source\' => $card->value,
        ]);
        return new PaymentResult($charge->id, $charge->status === \'succeeded\');
    }
}

// В тесте - подсунем FakeGateway, никаких моков Stripe
final class FakeGateway implements PaymentGateway
{
    public function charge(Money $a, CardToken $c): PaymentResult
    {
        return new PaymentResult(\'fake_id\', true);
    }
}

class OrderService
{
    public function __construct(private PaymentGateway $gateway) {}

    public function pay(Order $order): void
    {
        $this->gateway->charge($order->total(), $order->card());
    }
}',
                'code_language' => 'php',
            ],
        ];
    }
}
