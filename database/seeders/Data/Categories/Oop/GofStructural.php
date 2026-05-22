<?php

namespace Database\Seeders\Data\Categories\Oop;

class GofStructural
{
    public static function all(): array
    {
        return [
            [
                'category' => 'ООП',
                'topic' => 'oop.gof_structural',
                'difficulty' => 3,
                'question' => 'Паттерн Adapter',
                'answer' => '**Adapter (адаптер, переходник)** — структурный паттерн: **преобразует интерфейс** одного класса в интерфейс, который **ожидает клиент**.

**Когда применять:**

1. **Интеграция стороннего SDK** (Stripe, AWS, Guzzle) — оборачиваешь его в `implements PaymentGateway`, чтобы сигнатуры говорили **на языке домена**.
2. **Legacy-класс** + новый код — пишешь адаптер, **старый класс не правишь**.
3. **Несколько реализаций** (`Stripe`, `PayPal`, `YandexKassa`) приводишь к **одному** интерфейсу приложения.
4. **Anti-Corruption Layer** в DDD — изолируешь свой домен от чужой модели.

**Два вида:**

| | **Object Adapter** | **Class Adapter** |
|---|---|---|
| Как реализован | **композиция** (хранит цель в поле) | **наследование** (extends цели) |
| В PHP | удобно | ограничено единственным наследованием |
| Когда | по умолчанию | редко — почти всегда выбирают object adapter |

**Не путать со «соседями»:**

| Паттерн | Что делает |
|---|---|
| **Adapter** | **МЕНЯЕТ** интерфейс, ничего нового не добавляя |
| **Facade** | **УПРОЩАЕТ** доступ к сложной подсистеме за **одним** API |
| **Decorator** | **ОБОГАЩАЕТ** поведение, **сохраняя** тот же интерфейс |
| **Proxy** | **КОНТРОЛИРУЕТ** доступ к цели, тот же интерфейс |

**Запомни:** Adapter всегда отвечает на вопрос **«как сделать несовместимое совместимым»**.',
                'code_example' => '<?php
// Старый класс с неудобным интерфейсом
class LegacyXmlLogger
{
    public function writeXml(string $xml): void {}
}

// Желаемый клиентом интерфейс
interface Logger
{
    public function log(string $message): void;
}

// Адаптер: оборачивает старый класс
class LegacyLoggerAdapter implements Logger
{
    public function __construct(private LegacyXmlLogger $legacy) {}

    public function log(string $message): void
    {
        $xml = "<log>$message</log>";
        $this->legacy->writeXml($xml);
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.gof_structural',
                'difficulty' => 4,
                'question' => 'Паттерн Bridge',
                'answer' => '**Bridge (мост)** — структурный паттерн: **разделяет абстракцию и реализацию** в **две независимые иерархии** и связывает их **композицией**, чтобы их можно было изменять **независимо**.

**Проблема без Bridge:**

| | Red | Blue | Green |
|---|---|---|---|
| **Circle** | `RedCircle` | `BlueCircle` | `GreenCircle` |
| **Square** | `RedSquare` | `BlueSquare` | `GreenSquare` |
| **Triangle** | `RedTriangle` | `BlueTriangle` | `GreenTriangle` |

**Комбинаторный взрыв:** N форм × M цветов = **N × M классов**. Добавили новую форму → ещё **M** классов.

**С Bridge:**

- Иерархия **абстракции** (`Shape`): `Circle`, `Square`, `Triangle`.
- Иерархия **реализации** (`Color`): `Red`, `Blue`, `Green`.
- **Связь — `Shape $shape` хранит `Color $color`** (композиция).
- N **+** M классов вместо N × M.

**Когда применять:**

- Сущность имеет **2+ ортогональных оси** вариативности.
- Хочешь **менять абстракцию и реализацию независимо** (новый цвет ≠ правка всех форм).
- Иерархия **разрастается** и становится неуправляемой.

**Реальные примеры:**

| Абстракция | Реализация |
|---|---|
| `Renderer` (PDF/HTML/PNG) | `Document`, `Invoice`, `Report` |
| `Notification` (email/SMS/push) | `Order`, `Comment`, `Mention` |
| `Database` (MySQL/Pg/SQLite) | `QueryBuilder` |
| `MessageBus` (sync/async/queue) | `Handler` |

**Bridge vs Strategy:** структурно похожи (композиция), но Bridge — **проектное решение** для двух иерархий, Strategy — **рантайм-замена** одного алгоритма.',
                'code_example' => '<?php
interface Color
{
    public function fill(): string;
}

class Red implements Color
{
    public function fill(): string { return \'red\'; }
}

abstract class Shape
{
    public function __construct(protected Color $color) {}
    abstract public function draw(): string;
}

class Circle extends Shape
{
    public function draw(): string
    {
        return \'Circle filled with \' . $this->color->fill();
    }
}

$c = new Circle(new Red());',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.gof_structural',
                'difficulty' => 3,
                'question' => 'Паттерн Composite',
                'answer' => '**Composite (компоновщик)** — структурный паттерн: **группирует объекты в древовидную структуру** и позволяет работать с группой **так же**, как с одиночным объектом.

**Ключевая идея:**

- **Лист** (`File`) и **контейнер** (`Folder`) реализуют **один интерфейс** (`Component`).
- Контейнер **хранит детей** и **делегирует им вызов**, **рекурсивно** агрегируя результат.
- **Клиент не различает** — это один объект или дерево.

**Применения:**

| Где | Что компонуется |
|---|---|
| Файловая система | `File` / `Folder` — `size()`, `search()` |
| DOM / UI-дерево | `Element` / `Composite` — `render()`, `dispatchEvent()` |
| Организационная структура | сотрудник / отдел — общая зарплата, число сотрудников |
| Пайплайны валидаторов | `Rule` / `RuleSet` — `validate()` |
| Меню / навигация | `MenuItem` / `Menu` — `render()` |
| Бизнес-правила (Specification) | `Spec` + `AndSpec` / `OrSpec` — `isSatisfiedBy()` |

**Подводный камень — «прозрачность vs безопасность»:**

| Подход | Плюсы | Минусы |
|---|---|---|
| **Прозрачный** — `add`/`remove` в **общем интерфейсе** | клиент работает единообразно | лист «не умеет» add — нужно бросать исключение в рантайме |
| **Безопасный** — `add`/`remove` **только в контейнере** | компиляция/IDE ловят ошибку | клиент проверяет тип через `instanceof` перед добавлением |

**На практике** в PHP обычно выбирают **безопасный** вариант: лист реализует только «полезные» методы, а `Folder` extends/implements ещё `add()`/`remove()` дополнительно.

**Связь с другими паттернами:**

- **Iterator** часто ходит по composite-структуре.
- **Visitor** выполняет операции над листьями/контейнерами **без правки** их классов.
- **Specification** комбинируется через Composite (`AndSpec`, `OrSpec`).',
                'code_example' => '<?php
interface FsNode
{
    public function size(): int;
}

class File implements FsNode
{
    public function __construct(private int $size) {}
    public function size(): int { return $this->size; }
}

class Folder implements FsNode
{
    private array $children = [];
    public function add(FsNode $n): void { $this->children[] = $n; }
    public function size(): int
    {
        return array_sum(array_map(fn($c) => $c->size(), $this->children));
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.gof_structural',
                'difficulty' => 3,
                'question' => 'Паттерн Decorator',
                'answer' => '**Decorator (декоратор, обёртка)** — структурный паттерн: **динамически добавляет объекту новые обязанности**, оборачивая его в **другой объект с тем же интерфейсом**.

**Идея:**

- Декоратор `implements Component` и **хранит** `Component` внутри.
- Каждый метод **делегирует** обёрнутому объекту + **добавляет** своё поведение **до/после**.
- Декораторы **стекаются**: `new Cache(new Logging(new RealRepo()))`.

**Альтернатива наследованию для расширения поведения:**

| | **Наследование** | **Decorator** |
|---|---|---|
| Когда выбирается | compile-time (`extends Foo`) | **runtime** (`new Decorator($foo)`) |
| Комбинаторика | 4 фичи = 16 классов | 4 декоратора = любая комбинация |
| Связанность | жёсткая | через интерфейс |
| Подмена в тестах | нет | легко |

**Где применяется:**

- **Logging / Caching / Retry / RateLimit** для репозиториев и API-клиентов.
- **Middleware** в HTTP-пайплайне — каждое middleware оборачивает следующий хэндлер.
- **PSR-15 MiddlewareInterface** — стек декораторов над `RequestHandlerInterface`.
- **Symfony service decoration** — `#[AsDecorator]` подменяет существующий сервис.
- **Laravel pipeline** — `Pipeline::through($middlewares)->then(...)`.

**Подводные камни:**

- **Порядок стека критичен** — `Cache(Logging($repo))` закеширует и логи; `Logging(Cache($repo))` логирует только промахи кеша.
- **Много мелких классов** — на простых случаях лучше один декоратор с настройкой.
- **Утрата identity** — `$cache instanceof RealRepo` вернёт `false`. Если код где-то делал `instanceof` — сломается.

**Decorator vs Proxy** — структурно близнецы, разница в **намерении**: Decorator **расширяет поведение**, Proxy **контролирует доступ** (ленивая инициализация, проверка прав, удалённый объект).',
                'code_example' => '<?php
interface Notifier
{
    public function send(string $msg): void;
}

// Базовая реализация
class EmailNotifier implements Notifier
{
    public function send(string $msg): void
    {
        echo "Email: $msg\n";
    }
}

// Декоратор - добавляет SMS поверх существующего notifier
class SmsDecorator implements Notifier
{
    public function __construct(private Notifier $inner) {}

    public function send(string $msg): void
    {
        $this->inner->send($msg); // делегируем
        echo "SMS: $msg\n";        // добавляем своё
    }
}

// Ещё один декоратор - логирование
class LoggingDecorator implements Notifier
{
    public function __construct(private Notifier $inner) {}

    public function send(string $msg): void
    {
        $start = microtime(true);
        $this->inner->send($msg);
        $duration = microtime(true) - $start;
        echo "[log] sent in {$duration}s\n";
    }
}

// Стекуем декораторы в любом порядке
$n = new LoggingDecorator(
    new SmsDecorator(
        new EmailNotifier()
    )
);
$n->send(\'Привет\'); // Email + SMS + log',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.gof_structural',
                'difficulty' => 3,
                'question' => 'Паттерн Facade',
                'answer' => '**Facade (фасад)** — структурный паттерн: предоставляет **упрощённый унифицированный интерфейс** к **сложной подсистеме**, скрывая много мелких классов за одним удобным API.

**Идея:**

- Подсистема состоит из десятков классов с запутанными связями.
- Клиенту нужен **типичный сценарий** — не вся гибкость.
- Фасад — это **тонкий класс**, который собирает нужные части и предоставляет **высокоуровневые методы**.

**Когда применять:**

- **Упрощение** взаимодействия с библиотекой / модулем.
- **Изоляция** клиента от внутренней структуры (можно менять подсистему, не трогая клиентов).
- **Уменьшение** связанности — клиент зависит **от одного класса**, а не от десяти.

**Не путать:**

| | **GoF Facade** | **Laravel Facades** |
|---|---|---|
| Что это | объект, упрощающий подсистему | **статический прокси** к сервису контейнера |
| Реализация | обычный класс с методами | `Cache::get()` через `__callStatic` к контейнеру |
| Цель | скрыть сложность | синтаксический сахар над DI |

`Laravel Facades` — **другой паттерн** (несмотря на имя), это **Service Locator** в обёртке `__callStatic`.

| Паттерн | Главное отличие |
|---|---|
| **Facade** | **упрощает** доступ, ничего не добавляет, не меняет интерфейс ниже |
| **Adapter** | **меняет** интерфейс несовместимого класса |
| **Mediator** | централизует **обмен** между объектами |
| **Proxy** | **контролирует** доступ к одному объекту |

**Подводный камень — god object:** фасад **разрастается** до «единой точки доступа ко всему», нарушая SRP. Лечится **разбиением на несколько узких фасадов** по бизнес-сценариям.',
                'code_example' => '<?php
class VideoConverter // Facade
{
    public function convert(string $file, string $format): string
    {
        $video = (new VideoFile($file))->load();
        $codec = (new CodecFactory())->extract($video);
        $buffer = (new BitrateReader())->read($file, $codec);
        $result = (new AudioMixer())->fix($buffer);
        return (new VideoFile($result))->save($format);
    }
}

// Клиент использует один простой метод
$converter = new VideoConverter();
$converter->convert(\'a.mp4\', \'webm\');',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.gof_structural',
                'difficulty' => 4,
                'question' => 'Паттерн Flyweight',
                'answer' => '**Flyweight (приспособленец, легковес)** — структурный паттерн: **экономит память** за счёт **разделения общего состояния** между множеством объектов.

**Идея:** вместо **тысячи объектов** с одинаковыми данными — **один общий**, разделяемый между потребителями.

**Разделение состояния:**

| Тип | Где хранится | Пример: лес |
|---|---|---|
| **Intrinsic** (внутреннее) | в самом flyweight, **общее** | модель дерева, текстура, цвет |
| **Extrinsic** (внешнее) | **передаётся в метод** при вызове, уникально | координаты `(x, y)`, поворот |

**Архитектура:**

- **Flyweight Factory** — кеш по ключу (`name + texture`); если объекта нет — создаём, если есть — возвращаем существующий.
- **Flyweight** — иммутабельный объект с intrinsic-состоянием.
- **Клиент** — хранит extrinsic-состояние и передаёт его в методы flyweight.

**Когда применять:**

- **Очень много** объектов (десятки тысяч+).
- Объекты **дублируют** значительную часть состояния.
- Большую часть состояния можно **вынести вовне**.
- Идентичность конкретного экземпляра **не важна** (важно только состояние).

**Реальные примеры:**

- **Рендеринг** — частицы, иконки, символы шрифта (Glyph).
- **Парсер AST** — узлы операторов (`+`, `-`, `*`) — один на всё дерево.
- **PHP-строки** — интернирование коротких строк под капотом.
- **Кеши конфигурации** в Laravel — `config(\'app.name\')` возвращает один объект.

**Подводный камень:** flyweight **должен быть иммутабельным** — мутация общего объекта **испортит** всех клиентов.',
                'code_example' => '<?php
class TreeType // flyweight: общее состояние
{
    public function __construct(
        public string $name,
        public string $texture,
    ) {}

    public function draw(int $x, int $y): void
    {
        echo "Tree {$this->name} at ($x,$y)\n";
    }
}

class TreeFactory
{
    private static array $types = [];

    public static function get(string $name, string $texture): TreeType
    {
        $key = $name . $texture;
        return self::$types[$key] ??= new TreeType($name, $texture);
    }
}

// 1000 деревьев, но только N уникальных типов
foreach (range(1, 1000) as $i) {
    $type = TreeFactory::get(\'oak\', \'oak.png\');
    $type->draw(rand(0, 100), rand(0, 100));
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.gof_structural',
                'difficulty' => 3,
                'question' => 'Паттерн Proxy',
                'answer' => '**Proxy (заместитель)** — структурный паттерн: **объект с тем же интерфейсом**, что и реальный объект, но **контролирующий доступ** к нему.

**Виды Proxy:**

| Вид | Что контролирует | Пример |
|---|---|---|
| **Virtual Proxy** | **ленивая** инициализация дорогого объекта | Doctrine lazy entities, PHP 8.4 `Reflection::newLazyProxy` |
| **Protection Proxy** | проверка **прав доступа** | `AuthorizedRepo` — проверяет `Gate::allows()` перед find |
| **Remote Proxy** | прозрачное обращение к **удалённому** объекту | RPC-клиент с тем же интерфейсом, что и серверный сервис |
| **Logging Proxy** | запись **аудита** вызовов | оборачиваем сервис для трейса |
| **Caching Proxy** | хранение результатов | оборачиваем `Repository` |
| **Smart Reference** | подсчёт ссылок, доп. действия при доступе | редко в PHP, чаще в C++ |

**Где встречается в PHP/Laravel:**

- **Eloquent relations** — `$user->posts` при первом обращении делает запрос (Virtual Proxy).
- **Doctrine** — `EntityManager::getReference()` возвращает прокси, грузит сущность при первом обращении к полям.
- **PHP 8.4 Lazy Objects** — нативная поддержка `Reflection::newLazyProxy()` и `newLazyGhost()`.
- **Telescope / Debugbar** оборачивают сервисы для аудита.

**Proxy vs Decorator** — структурно близнецы:

| | **Decorator** | **Proxy** |
|---|---|---|
| Намерение | **расширить** поведение | **контролировать** доступ |
| Объект внутри | передаётся **извне** | часто создаётся **самим прокси** |
| Стекуется | да (несколько декораторов подряд) | редко |
| Жизненный цикл | управляет клиент | управляет прокси |

**Подводный камень:** объект **не равен** своему прокси (`$proxy !== $real`), `instanceof` на конкретный класс не сработает. Если код где-то делал такие проверки — сломается. Поэтому Proxy всегда работает **через интерфейс**, не через конкретный класс.',
                'code_example' => '<?php
interface Image
{
    public function display(): void;
}

class RealImage implements Image
{
    public function __construct(private string $file)
    {
        echo "Loading $file\n"; // дорого
    }
    public function display(): void { echo "Displaying $this->file\n"; }
}

class ImageProxy implements Image // ленивый
{
    private ?RealImage $real = null;
    public function __construct(private string $file) {}

    public function display(): void
    {
        $this->real ??= new RealImage($this->file);
        $this->real->display();
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.gof_structural',
                'difficulty' => 4,
                'question' => 'Чем Proxy отличается от Decorator? Структурно ведь они почти одинаковые',
                'answer' => '**Структурно** они **близнецы:** оба реализуют **общий с целью интерфейс** и **делегируют** ей вызовы. **Разница — в намерении.**

**Семантическое сравнение:**

| Признак | **Decorator** | **Proxy** |
|---|---|---|
| **Намерение** | **расширить** поведение цели | **контролировать** доступ к цели |
| **Цель приходит** | **извне** (передаётся в конструктор) | часто **создаётся самим прокси** |
| **Жизненный цикл цели** | управляет клиент | управляет **прокси** |
| **Стекуемость** | **да** — `Cache(Logger(Repo))` | редко, обычно один |
| **Клиент знает о цепочке** | **да** — он её собирает | **нет** — прокси прозрачен |
| **Типичная задача** | логирование, кеш, шифрование | lazy load, ACL, RPC, аудит |

**Decorator-примеры (Laravel):**

- `CachingRepo(LoggingRepo(UserRepo))` — стек.
- Middleware-пайплайн.
- `Symfony\\Cache\\Adapter\\TraceableAdapter`.

**Proxy-примеры (Laravel):**

- **Eloquent lazy relations** — `$user->posts` делает запрос при первом обращении (Virtual Proxy).
- **Doctrine entity proxies** — `EntityManager::getReference()`.
- **PHP 8.4 Lazy Objects** — `Reflection::newLazyProxy()`, `newLazyGhost()`.
- **Telescope/Debugbar** — оборачивают сервисы для аудита.

**Простая эвристика:**

> «Можно ли **снять обёртку** и работать дальше?» — **да** → Decorator. **нет**, прокси нужен для контроля → Proxy.',
                'code_example' => '<?php
interface Repository { public function find(int $id): User; }

class UserRepo implements Repository
{
    public function find(int $id): User { /* heavy DB query */ }
}

// DECORATOR - обогащаем поведение, объект передан извне
final class CachingRepo implements Repository
{
    public function __construct(private Repository $inner, private Cache $cache) {}

    public function find(int $id): User
    {
        return $this->cache->remember(
            "user:$id", 300,
            fn() => $this->inner->find($id), // делегирует
        );
    }
}

final class LoggingRepo implements Repository
{
    public function __construct(private Repository $inner) {}
    public function find(int $id): User
    {
        Log::info("find user", ["id" => $id]);
        return $this->inner->find($id);
    }
}

$repo = new CachingRepo(new LoggingRepo(new UserRepo()), $cache);
// стекуем decorators

// PROXY - контролируем доступ и жизненный цикл цели
final class LazyUserRepo implements Repository
{
    private ?Repository $real = null; // ещё не создан

    public function __construct(private Container $c) {}

    public function find(int $id): User
    {
        // создаём цель только когда реально нужна
        $this->real ??= $this->c->make(UserRepo::class);
        return $this->real->find($id);
    }
}

final class AuthorizedRepo implements Repository
{
    public function __construct(private Repository $inner, private User $user) {}

    public function find(int $id): User
    {
        if (! $this->user->can("users.read")) {
            throw new AccessDeniedException;
        }
        return $this->inner->find($id);
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'question' => 'Почему Facade рискует превратиться в god object и как с этим бороться?',
                'answer' => '**Изначальная задача Facade** — **тонкий слой** над сложной подсистемой. **Проблема** — со временем в него тянут **всё новые методы**, и он превращается в **«один на всё приложение»** объект с десятками зависимостей.

**Симптомы god-фасада:**

| Симптом | Что значит |
|---|---|
| **20+ методов** в одном классе | SRP **сломан** |
| Зависит от **10+ сервисов** | high coupling |
| Методы из **разных** бизнес-доменов | cohesion **низкая** |
| Сложно найти, **где** живёт логика | клиент идёт «в фасад», прячет реальные зависимости |
| Тесты на **`OrderController`** требуют мокать **весь фасад** | хрупкие тесты |

**Почему это случается:**

- Удобно добавить «**ещё один метод**» в уже существующий фасад.
- **Псевдо-DRY** — кажется, что мы избегаем дублирования.
- Клиенты **полюбили «одну удобную точку»** — вызывают вместо явных зависимостей.

**Лечение:**

1. **Разбить на узкие фасады** по бизнес-сценариям (`CheckoutFacade`, `BillingFacade`, `ReportFacade`).
2. **Вынести логику в отдельные сервисы** — фасад остаётся **тонким** оркестратором.
3. **Явный запрет** тянуть в фасад методы не из его подсистемы — фиксируется в code review.
4. **Метрика** — порог: «фасад > 10 методов или > 5 зависимостей — пора резать».
5. В Laravel **различай** Facade-паттерн GoF и фасады Laravel (`Cache::`, `DB::`) — последнее это **прокси над контейнером**, и оно собственное.

**Граница:** хороший фасад **знает названия** методов подсистемы и **порядок их вызова** — но **не содержит** бизнес-логики.',
                'difficulty' => 4,
                'topic' => 'oop.gof_structural',
            ],
            [
                'category' => 'ООП',
                'question' => 'Почему стек декораторов чувствителен к порядку и как этим управлять?',
                'answer' => '**Декораторы оборачивают друг друга**, поэтому результат зависит от того, **кто внешний, а кто внутренний**.

**Классический пример:**

| Сборка | Что происходит |
|---|---|
| `Cache(Logger(Repo))` | **закешируется и логирование** — на cache-hit логов не будет |
| `Logger(Cache(Repo))` | логируется **каждый** вызов, кеш работает только для `Repo` |
| `Auth(Logger(Repo))` | логируем **после** проверки прав — отказы не логируем |
| `Logger(Auth(Repo))` | логируем **все попытки** — включая отказы |

**Где порядок критичен:**

- **Кеш + логирование** — кеш-хиты не должны логироваться **или** должны — выбираешь ты.
- **Авторизация + аудит** — что важнее: лог попыток или лог только разрешённых.
- **Транзакция + retry** — retry **снаружи** транзакции (повторить весь блок), не внутри (вечная rollback-цепочка).
- **Шифрование + сериализация** — обычно serialize → encrypt, не наоборот.

**Сложности:**

- **Удалить из середины** стека сложно — нужно **пересобрать** цепочку.
- **Декомпозиция тестов** требует проверки **каждого слоя** отдельно + **интеграцию**.

**Как управлять:**

1. **Сборка в одном месте** — в **DI-контейнере** или фабрике, не разбросано.
2. **Назвать сборку** — `decoratedRepo()` возвращает уже готовый стек.
3. **Проектировать коммутативно** там где возможно — но не всегда удаётся.
4. **Документировать порядок** — комментарий «cache **выше** logger, чтобы логировать только misses».
5. **Тесты на сборку** — проверять конкретный порядок интеграционным тестом.',
                'difficulty' => 4,
                'topic' => 'oop.gof_structural',
            ],
            [
                'category' => 'ООП',
                'question' => 'Когда Bridge оказывается излишним и только усложняет код?',
                'answer' => '**Bridge оправдан**, когда абстракция и реализация **реально варьируются** по **двум независимым осям**. **Без двух осей** он только добавляет интерфейсов и фабрик без выигрыша.

**Когда оправдан:**

| Ось 1 (абстракция) | Ось 2 (реализация) |
|---|---|
| Формы (Shape) | Рендереры (PDF/SVG/Canvas) |
| Платёжные провайдеры | Транспорты (sync/async/queue) |
| Уведомления | Каналы (email/SMS/push) |
| Документы | Хранилища (local/S3/FTP) |

**Когда — излишен:**

- **Только одна реализация** существует и не предвидится других.
- Класс уже **сплочён**, и разделение его на две иерархии — **искусственно**.
- «**Вторая ось**» появилась «на всякий случай» — но **нет реальных** альтернативных реализаций.
- Сейчас 2 формы × 2 цвета = 4 класса — это **не** комбинаторный взрыв, Bridge не нужен.

**Признаки злоупотребления:**

| Запах | Что значит |
|---|---|
| Конкретных реализаций второй оси **меньше 2** | YAGNI — не понадобилось |
| **Интерфейсы пустые** или с одним методом | абстракция «на вырост», не сейчас |
| Никто не вызывает **`setImplementation()`** | смена реализации в рантайме **никому не нужна** |
| Фабрика создаёт **только одну пару** | две оси оказались **одной** |

**Совет:** **YAGNI** — оставь один класс, **рефактори в Bridge позже**, когда **реально** понадобится вторая реализация. Превентивный Bridge — **типичный over-engineering**.',
                'difficulty' => 4,
                'topic' => 'oop.gof_structural',
            ],
        ];
    }
}
