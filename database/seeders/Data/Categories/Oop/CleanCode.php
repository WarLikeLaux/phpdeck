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
                'answer' => '**Говорящее имя** раскрывает **намерение**: что переменная значит и зачем существует.

**Сравни:**

- `$temp`, `$a1`, `processData()` — читателю приходится восстанавливать смысл по контексту.
- `$invoiceTotal`, `markInvoiceAsPaid()` — смысл очевиден без комментария.

**Правила:**

- Имя должно отвечать: что это, зачем, и как используется.
- Длина имени **пропорциональна** области видимости: `$i` ок в коротком цикле, в методе класса — `$index`.
- Глагол в имени метода (`calculateTotal`), существительное в имени класса (`OrderService`).
- Без аббревиатур (`usrSvc`), без венгерской нотации (`strName`).

Хорошие имена — **первый и самый дешёвый** уровень документации.',
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
                'answer' => '**Функция должна быть маленькой** и делать **одну вещь** — это SRP на уровне функции.

**Ориентир (Uncle Bob):** 5-20 строк, **один уровень абстракции** внутри. Если внутри одновременно «парсим строку» и «считаем налог» — это уже два уровня, надо разделять.

**Признаки, что пора резать:**

- Описание функции содержит **«и»** — `validateAndSendEmail()`.
- Глубокая **вложенность** `if`-ов (4+ уровней).
- Длинные блоки внутри `if`/`else` — каждая ветвь сама на функцию.
- Хочется писать **комментарии-разделители** `// === считаем тотал ===`.

**Что даёт:** легче читать, переиспользовать, тестировать и **изолированно** менять.',
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
                'answer' => '**По Clean Code (Uncle Bob):** идеал **0**, норма **1–2**, **3** — подозрительно, **4+** почти всегда означают, что функция делает слишком много или что аргументы пора **сгруппировать в объект**.

**Почему много параметров — плохо:**

- **Перестановка** — порядок легко перепутать, особенно у одинаковых типов (`string $email, string $phone, string $city`). PHP не заметит.
- **Читаемость вызова** — `createUser($a, $b, $c, $d, $e, $f)` без IDE-подсказки нечитаем.
- **Тесты** — N параметров → комбинаторный взрыв сценариев.
- **Связанность** — функция становится зависима от **всего набора** входов.

**Что делать:**

1. **Parameter Object / DTO** — сгруппировать связанные параметры в один объект (`Address`, `CreateUserDto`). Бонус: типобезопасность.
2. **Value Objects** вместо `string $email` → `Email $email` — устраняет primitive obsession.
3. **Named arguments** (PHP 8.0) — `createUser(name: \'Иван\', email: \'...\')` — спасает читаемость, **но не лечит** дизайн.

**Bool-флаг — отдельная проблема:**

```php
function save(User $u, bool $sendEmail): void
```

— **почти всегда сигнал разбить на два метода** (`save()` и `saveAndNotify()`). Имя метода тогда **говорит**, что произойдёт, а вызов `save($u, true)` ничего не объясняет читателю.',
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
                'answer' => '**Хороший комментарий** объясняет то, что **код не может** сам:

- **Зачем** так сделано (бизнес-причина, ссылка на тикет/RFC).
- Объяснение **нетривиального** обходного пути или баги внешней системы.
- Договорённость, не выводимая из контекста (`// must match SchemaV2 from billing-service`).
- **PHPDoc**: типы для IDE и статанализа там, где PHP не выражает (`@return User[]`).

**Шумовые комментарии (плохо):**

- `// increment i` — дублирует код.
- Закомментированный мёртвый код — место для git.
- Расходятся с реальностью при правках → дезинформация.

**Правило:** сначала попробуй переписать код так, чтобы он **сам объяснял себя** через имена и структуру. **TODO** — допустимы, но с автором и сроком (`// TODO(ivan, ticket-123)`).',
                'difficulty' => 2,
                'topic' => 'oop.clean_code',
            ],
            [
                'category' => 'ООП',
                'question' => 'Почему исключения предпочтительнее кодов ошибок?',
                'answer' => '**Главная проблема кодов ошибок** — happy path и обработка сбоев **смешиваются** в одной функции. Каждый вызывающий **обязан** проверить возврат, забыл проверку → программа тихо едет дальше с битым значением.

| | **Коды ошибок** | **Исключения** |
|---|---|---|
| Сигнатура | возвращает `[\'value\' => ..., \'error\' => ...]` | возвращает **результат** |
| Игнорирование | компилятор не заметит | unhandled → краш с трейсом |
| Bubble-up | вручную пробрасывать через слои | **автоматически** до `catch` |
| Семантика типа | размытая (массив/пара) | типизированная иерархия |
| Стоимость | дёшево (просто `if`) | дороже (stack trace) |

**Что дают исключения:**

- **Отделение** успеха от сбоя — happy path **линеен**, без `if ($result[\'error\'])` после каждой строки.
- **Нельзя «потерять»** — необработанное исключение всплывает наверх и громко падает.
- **Типизированная иерархия** — клиент ловит **конкретный** тип (`UserNotFoundException`) или **родителя** (`DomainException`), unionом — оба сразу (PHP 8).
- **Контекст** — `previous`, message, stack trace, дополнительные поля.

**Когда коды ошибок уместны:**

- **Hot path** в критичном по производительности коде — выбрасывание/раскрутка дороже простого `if`.
- **Result/Either-тип** для **ожидаемых** исходов (валидация формы), когда «ошибка» — это **штатная ветка** логики, а не аномалия. В современных PHP это часто паттерн `Result<Ok, Err>`.

**Правило:** исключения — для **исключительных** ситуаций (нарушенный инвариант, недоступная инфраструктура), а **штатные** альтернативы — через типизированный результат.',
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
                'answer' => '**`null` — «миллиардная ошибка»** (Тони Хоар, 1965). Проблемы возврата `null`:

- Каждый вызывающий **обязан** помнить про проверку.
- Россыпь `if ($x === null)` **по всему коду** — visual noise.
- Забытая проверка превращается в `TypeError` / `Error: Call to a member function on null` **на проде**.
- Контракт **не говорит** — это «ошибка» или «легитимное отсутствие».

**Четыре стратегии вместо `null`:**

1. **Исключение** — если отсутствие = **ошибка** (`findOrFail($id)`). Клиент **не сможет забыть** обработать.
2. **Null Object** — если поведение «ничего не делать» **легитимно** (`NullLogger`, `GuestUser`). Реализует тот же интерфейс, методы — no-op. Клиент пишет **линейный код** без `if`.
3. **Пустая коллекция** для списков — `getActiveUsers(): array` возвращает `[]`, а не `?array`. `foreach` по пустому массиву безопасен.
4. **Optional / Maybe / Result** — обёртка `Option<T>` с методами `isSome()`, `unwrapOr($default)`. Клиент **вынужден** распаковать значение явно.

**Когда `?Type` (nullable) допустим:**

- В DTO/VO, где **поле legitimно отсутствует** (`?string $middleName`).
- В **запросах**, где «нет — это нормально» и клиент **близко** проверит (`$repo->find()` с прицелом на «попробуй найти»).

**Правило:** `null` в возврате — это **скрытый второй тип**. Если можно сделать контракт явным (исключение/Null Object/коллекция) — делай.',
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
                'answer' => '**Early Return (guard clauses)** — стиль, при котором краевые условия и невалидный вход обрабатываются **в начале** функции через `return` или `throw`. Основной сценарий идёт **на нулевом уровне** вложенности.

**Что это даёт:**

- Убирает **лестницу** из `if-else`.
- **Happy path** виден сразу, без раскопок через 4 уровня скобок.
- Снижает шанс пропустить ветку, легче читать.

**Идея в одной строке:** «Обрабатывай ошибки **сразу**, не откладывай.»

Это плотно связано с принципом **«Fail Fast»**: чем раньше прервёшь невалидный поток, тем меньше байт-кода успеет навредить.',
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
                'answer' => '**Правило бойскаута** (Boy Scout Rule, Uncle Bob): «**Оставляй код чище**, чем он был до тебя.»

**Что это значит на практике:** при любом касании файла — даже если задача не про рефакторинг — можно **по мелочи**:

- улучшить имя переменной/метода
- выделить кусок в функцию
- удалить мёртвый код или закомментированный «на всякий»
- дописать недостающий тест
- разбить длинную функцию

**Что это даёт:** кодовая база **непрерывно** улучшается вместо постепенной деградации. Большой рефакторинг не нужен — много маленьких улучшений суммируются.

**Ограничение:** не превращай каждый PR в косметический шторм — изменения должны быть **скромными** и **рядом** с твоей задачей, иначе ревьюверу сложно отделить смысл от наведения красоты.',
                'difficulty' => 2,
                'topic' => 'oop.clean_code',
            ],
            [
                'category' => 'ООП',
                'question' => 'Зачем оборачивать сторонние API в собственные абстракции?',
                'answer' => '**Проблема прямых вызовов сторонних SDK** (`Guzzle`, `Stripe`, `AwsClient`) из бизнес-кода:

- Чужая библиотека становится **неявной зависимостью всего проекта**.
- Смена **версии** SDK задевает десятки файлов (breaking changes).
- Замена **провайдера** (Stripe → Cloudpayments) = переписывание сервисов.
- Бизнес-код **говорит на чужом языке**: `Stripe::createCharge([\'amount\' => 100, \'currency\' => \'usd\'])` вместо `$gateway->charge(Money::usd(100))`.
- **Тесты** требуют моков чужого SDK с сотнями методов.

**Решение — тонкая обёртка (паттерны `Adapter` / `Anti-Corruption Layer` из DDD):**

- Объявляем **свой интерфейс** на языке домена: `PaymentGateway::charge(Money, Card): PaymentResult`.
- Реализация (`StripeGateway`) **переводит** между доменом и SDK.
- Бизнес-код зависит **от интерфейса**, не от SDK.

**Что даёт:**

1. **Изоляция** — менять реализацию можно **без правки клиентов** (соблюдён DIP).
2. **Тестируемость** — в юнит-тестах подсовываем `FakePaymentGateway`, никаких моков `Stripe\\StripeClient`.
3. **Чистый домен** — никаких `Stripe`-типов в сигнатурах сервисов. **Anti-Corruption Layer** не пускает чужие модели внутрь.
4. **Версионирование** — обновление SDK = правка **одного класса**, а не сотни вызовов.
5. **Совместимость** — несколько провайдеров (Stripe + PayPal) реализуют один интерфейс — `Strategy` из коробки.

**Цена:** дополнительный слой, риск over-engineering.

**Когда НЕ оборачивать (YAGNI):**

- Тривиальные **утилиты** (`Carbon`, `Str::slug`) — `array_map`-уровня.
- Библиотеки, которые **никогда не сменятся** (`PSR-логгер`, `Symfony Console`).
- **Прототип** / MVP — обернёшь позже, когда станет больно.

**Когда оборачивать обязательно:** платёжки, email/SMS-провайдеры, облачные SDK, любые **внешние сервисы**, у которых есть альтернативы и которые нужно мокать в тестах.',
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
