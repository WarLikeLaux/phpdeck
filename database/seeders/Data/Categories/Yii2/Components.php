<?php

namespace Database\Seeders\Data\Categories\Yii2;

class Components
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Yii2',
                'question' => 'Что такое yii\\base\\Component?',
                'answer' => '**`yii\\base\\Component`** — базовый класс для **компонентов** Yii2. Расширяет `BaseObject` и добавляет **3 ключевые возможности**:

- **События** (`on()`, `off()`, `trigger()`).
- **Поведения** (`behaviors()`, `attachBehavior()`).
- **Магические getter/setter** через методы `getXxx()` / `setXxx()` (унаследовано от `BaseObject`).

**Когда использовать:**

- Если объект должен **выбрасывать события** или **подключать behaviors** — наследуй от `Component`.
- Если нужен просто объект с геттерами/сеттерами и без событий — хватит `BaseObject`.

Почти все классы Yii2 (контроллеры, модели, виджеты, компоненты приложения) наследуются от `Component`.',
                'code_example' => '<?php
namespace app\\components;

use yii\\base\\Component;

class Order extends Component
{
    const EVENT_PAID = \'paid\';

    public function pay()
    {
        // ... логика
        $this->trigger(self::EVENT_PAID);
    }
}

// Подписка
$order = new Order();
$order->on(Order::EVENT_PAID, function ($event) {
    Yii::info(\'Order paid: \' . $event->sender->id);
});
$order->pay();',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.components',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое yii\\base\\BaseObject?',
                'answer' => '**`yii\\base\\BaseObject`** — самый базовый класс Yii2. Даёт **3 ключевые особенности**:

- **Конструктор с массивом-конфигом** последним параметром: `new MyClass([\'name\' => \'X\'])`.
- **Магические getter/setter** через методы `getXxx()` / `setXxx()`.
- Методы `init()`, `hasProperty()`, `canGetProperty()`, `canSetProperty()`.

**Отличие от `Component`:**

- `BaseObject` **не поддерживает события и behaviors** — он легче.
- `Component` наследуется от `BaseObject` и добавляет события + behaviors.

Используется для классов-DTO, валидаторов, helper-объектов, где события не нужны.',
                'code_example' => '<?php
namespace app\\models;

use yii\\base\\BaseObject;

class Money extends BaseObject
{
    public int $amount = 0;
    public string $currency = \'USD\';

    private string $_formatted;

    // Магический геттер: $m->formatted
    public function getFormatted(): string
    {
        return $this->amount . \' \' . $this->currency;
    }

    public function init()
    {
        parent::init(); // ОБЯЗАТЕЛЬНО!
        // ... своя инициализация
    }
}

$m = new Money([\'amount\' => 100, \'currency\' => \'EUR\']);
echo $m->formatted; // \'100 EUR\'',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.components',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как работают магические __get / __set в BaseObject?',
                'answer' => 'В `BaseObject` магия работает через **методы-геттеры/сеттеры** с префиксами `get` / `set`.

**Правила:**

- Если объявлен **`getXxx()`** — обращение `$obj->xxx` вызовет этот метод.
- Если объявлен **`setXxx($value)`** — присваивание `$obj->xxx = ...` вызовет этот метод.
- Если есть только **`getXxx()`** без `setXxx()` — свойство **read-only**, попытка записи кинет `InvalidCallException`.
- Если **ни метода, ни публичного свойства** нет — обращение кинет `UnknownPropertyException`.

**Зачем:**

- Имитация properties (как в C# или PHP 8.4) — но **без накладных расходов в каждом случае**.
- Возможность ввести computed-свойства, валидацию при записи, ленивую инициализацию.',
                'code_example' => '<?php
use yii\\base\\BaseObject;

class User extends BaseObject
{
    private string $_email;

    public function getEmail(): string
    {
        return $this->_email;
    }

    public function setEmail(string $value): void
    {
        $this->_email = strtolower(trim($value));
    }

    // Только геттер — read-only
    public function getId(): int
    {
        return 42;
    }
}

$u = new User();
$u->email = \'  Foo@Bar.COM  \'; // вызовет setEmail()
echo $u->email; // \'foo@bar.com\'
echo $u->id;    // 42

$u->id = 1; // InvalidCallException: Setting read-only property',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.components',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что делают hasProperty / canGetProperty / canSetProperty?',
                'answer' => 'Это методы интроспекции свойств `BaseObject` — отвечают, **возможна ли работа с конкретным свойством**.

- **`hasProperty($name)`** — есть ли свойство (публичное **или** через `getXxx`/`setXxx`).
- **`canGetProperty($name)`** — можно ли **читать** (есть `getXxx` или публичное свойство).
- **`canSetProperty($name)`** — можно ли **писать** (есть `setXxx` или публичное свойство).

**Зачем:**

- Динамические формы и валидаторы используют их, чтобы проверить, можно ли работать со свойством модели.
- Massive assignment (`$model->setAttributes($_POST)`) опирается на `canSetProperty`.

**Параметры:**

- Второй аргумент `$checkVars = true` — учитывать ли публичные свойства (по умолчанию да).
- Третий `$checkBehaviors = true` (только в Component) — учитывать ли свойства behaviors.',
                'code_example' => '<?php
use yii\\base\\BaseObject;

class Product extends BaseObject
{
    public string $name = \'\';

    public function getPrice(): float { return 99.0; }
    // нет setPrice — price read-only
}

$p = new Product();

$p->hasProperty(\'name\');     // true (публичное)
$p->hasProperty(\'price\');    // true (есть getPrice)
$p->hasProperty(\'foo\');      // false

$p->canGetProperty(\'price\'); // true
$p->canSetProperty(\'price\'); // false (нет setPrice)
$p->canSetProperty(\'name\');  // true',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.components',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Зачем нужен метод init() в Yii2-классах?',
                'answer' => '**`init()`** — хук, который Yii2 вызывает **после** того, как конструктор `BaseObject` применил конфиг-массив к свойствам.

**Порядок вызова в `new MyClass($config)`:**

1. Базовый конструктор `BaseObject` записывает значения из `$config` в свойства / сеттеры.
2. Вызывается **`$this->init()`**.

**Зачем:**

- Нужна логика, **зависящая от уже заполненных свойств** (например проверка, что `apiKey` задан).
- Подключение к внешним сервисам, инициализация internal-кэшей.
- Установка значений по умолчанию для невидимых конфигу полей.

**Правила:**

- Всегда вызывай **`parent::init()`** первой строкой.
- Не делай тяжёлую работу — компоненты создаются лениво, но `init` всё равно блокирующий.',
                'code_example' => '<?php
use yii\\base\\Component;

class Mailer extends Component
{
    public string $host;
    public int $port = 587;
    public string $username;

    public $client; // SMTP-клиент

    public function init()
    {
        parent::init();

        if (empty($this->host)) {
            throw new \\yii\\base\\InvalidConfigException(\'Mailer::host is required\');
        }

        $this->client = new \\SmtpClient($this->host, $this->port);
    }
}

// Конфиг сразу применяется к свойствам, потом init()
$m = new Mailer([
    \'host\' => \'smtp.mailgun.org\',
    \'username\' => \'user@example.com\',
]);',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.components',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Чем Component отличается от BaseObject?',
                'answer' => '**Сравнение:**

| Возможность | `BaseObject` | `Component` |
| --- | --- | --- |
| Конфиг-массив в конструкторе | да | да |
| Магические `getXxx`/`setXxx` | да | да |
| `init()` | да | да |
| **События** (`on/off/trigger`) | нет | **да** |
| **Поведения** (`behaviors()`, `attachBehavior`) | нет | **да** |
| Накладные расходы | минимальные | больше (лишние массивы для events/behaviors) |

**Когда что брать:**

- **`BaseObject`** — value-объекты, helper-классы без событий: лёгкий, без лишней памяти.
- **`Component`** — модели, контроллеры, виджеты, компоненты приложения: всё, что должно **участвовать в событийной модели**.

Класс `Component` extends `BaseObject` — это **тонкая надстройка**.',
                'code_example' => '<?php
// Component поддерживает события и behaviors
class Order extends \\yii\\base\\Component
{
    public function behaviors()
    {
        return [
            \\yii\\behaviors\\TimestampBehavior::class,
        ];
    }
}

$order = new Order();
$order->on(\'paid\', fn ($e) => Yii::info(\'paid\'));
$order->trigger(\'paid\');

// BaseObject — нельзя on()/trigger()/behaviors
class Money extends \\yii\\base\\BaseObject
{
    public int $amount = 0;
}
$m = new Money([\'amount\' => 100]);
// $m->on(...); — Error: метод не существует',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.components',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как настроить компонент через массив-конфиг?',
                'answer' => 'Любой `Component` / `BaseObject` Yii2 настраивается **массивом** с двумя видами ключей:

- **`\'class\'`** — полное имя класса.
- Остальные ключи — **публичные свойства или сеттеры** объекта.

**Где используется:**

- В `config/web.php` секция `components`.
- В свойстве `behaviors()` модели/контроллера.
- В вызове `Yii::createObject($config)`.

**Под капотом:**

- Yii создаёт `new ClassName()`.
- Применяет каждый ключ конфига как `$obj->key = $value` (через магию getter/setter).
- Вызывает `$obj->init()`.',
                'code_example' => '<?php
// config/web.php
return [
    \'components\' => [
        \'db\' => [
            \'class\' => \'yii\\db\\Connection\',
            \'dsn\' => \'mysql:host=localhost;dbname=shop\',
            \'username\' => \'root\',
            \'password\' => \'\',
            \'charset\' => \'utf8mb4\',
        ],
        \'cache\' => [
            \'class\' => \'yii\\caching\\FileCache\',
            \'cachePath\' => \'@runtime/cache\',
        ],
        \'mailer\' => [
            \'class\' => \'app\\components\\Mailer\',
            \'host\' => \'smtp.mailgun.org\',
            \'username\' => \'noreply@example.com\',
        ],
    ],
];

// Yii::createObject — тот же приём
$cache = Yii::createObject([
    \'class\' => \'yii\\caching\\FileCache\',
    \'cachePath\' => \'@runtime/cache\',
]);',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.components',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как обращаться к компонентам приложения через Yii::$app?',
                'answer' => 'Все зарегистрированные в `config[\'components\']` компоненты доступны как **свойства** объекта `Yii::$app`.

**Стандартные компоненты:**

- **`Yii::$app->db`** — `yii\\db\\Connection`.
- **`Yii::$app->user`** — `yii\\web\\User` (авторизация).
- **`Yii::$app->request`** — `yii\\web\\Request`.
- **`Yii::$app->response`** — `yii\\web\\Response`.
- **`Yii::$app->session`** — `yii\\web\\Session`.
- **`Yii::$app->cache`** — `yii\\caching\\Cache`.
- **`Yii::$app->urlManager`** — построение URL.
- **`Yii::$app->mailer`** — почта.

**Особенности:**

- Компоненты создаются **лениво** — при первом обращении.
- `Yii::$app->has(\'foo\')` — проверка, зарегистрирован ли.
- `Yii::$app->set(\'foo\', $config)` — добавить/заменить во время выполнения.',
                'code_example' => '$user = Yii::$app->user->identity;
if ($user === null) {
    return $this->redirect([\'site/login\']);
}

$products = Yii::$app->db
    ->createCommand(\'SELECT * FROM products WHERE active = 1\')
    ->queryAll();

$ip = Yii::$app->request->userIP;
$lang = Yii::$app->request->preferredLanguage();

Yii::$app->session->setFlash(\'success\', \'Сохранено\');

Yii::$app->cache->set(\'key\', $value, 3600);

if (Yii::$app->has(\'redis\')) {
    Yii::$app->redis->set(\'foo\', \'bar\');
}',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.components',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как зарегистрировать свой компонент в Yii2?',
                'answer' => 'Свой компонент регистрируется как **новый ключ в массиве `components`** конфига приложения.

**Шаги:**

1. Создать класс, наследующий **`yii\\base\\Component`**.
2. Добавить в `config/web.php` (или `console.php`) ключ с **именем компонента**.
3. Обращаться через `Yii::$app->myComponent`.

**Лайфхак:**

- Компонент создаётся при **первом обращении** (lazy load) — добавление в конфиг не съедает память.
- Если нужен **типизированный IDE-доступ**, можно объявить `@property` в PHPDoc на классе `Yii` (через свой helper).',
                'code_example' => '<?php
// components/Mailer.php
namespace app\\components;

use yii\\base\\Component;

class Mailer extends Component
{
    public string $host = \'\';

    public function send(string $to, string $body): bool
    {
        Yii::info("Sending to $to via $this->host");
        return true;
    }
}

// config/web.php
return [
    \'components\' => [
        \'mailer\' => [
            \'class\' => \'app\\components\\Mailer\',
            \'host\' => \'smtp.mailgun.org\',
        ],
    ],
];

// Использование
Yii::$app->mailer->send(\'a@b.com\', \'Hi\');',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.components',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что произойдёт при обращении к несуществующему свойству Component?',
                'answer' => 'Yii2 пройдёт по **цепочке поиска**:

1. **Публичное свойство** класса.
2. **Метод `getXxx()`** для чтения / **`setXxx()`** для записи.
3. **Свойство присоединённого behavior** (если есть).
4. **Если ничего не найдено** — кидает исключение.

**Исключения:**

- **`UnknownPropertyException`** — если свойство вообще не существует.
- **`InvalidCallException`** — если свойство **read-only** (есть getter, нет setter) или **write-only**.

**Полезно знать:**

- Это работает только для `BaseObject`/`Component`. Для обычного PHP-объекта чтение `$obj->foo` молча даёт `null` (warning в PHP 8.2+).
- Защита от опечаток: `$model->emial` в Yii2 сразу падает с понятной ошибкой.',
                'code_example' => '<?php
use yii\\base\\Component;

class Foo extends Component
{
    public function getName(): string { return \'foo\'; }
}

$f = new Foo();

echo $f->name;       // \'foo\' — через getter
echo $f->unknown;
// yii\\base\\UnknownPropertyException:
// Getting unknown property: Foo::unknown

$f->name = \'bar\';
// yii\\base\\InvalidCallException:
// Setting read-only property: Foo::name',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.components',
            ],
        ];
    }
}
