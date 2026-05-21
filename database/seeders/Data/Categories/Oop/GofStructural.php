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
                'answer' => 'Bridge (мост) разделяет абстракцию и реализацию так, чтобы их можно было изменять независимо. Простыми словами: вместо комбинаторного взрыва классов (КвадратКрасный, КругКрасный, КвадратСиний...) выделяем две независимые иерархии - формы и цвета - и связываем их через композицию. Полезен, когда у сущности есть несколько ортогональных вариативностей.',
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
                'answer' => 'Flyweight (приспособленец, легковес) экономит память за счёт разделения общего состояния между множеством объектов. Простыми словами: вместо тысячи объектов с одинаковыми данными - один общий объект, который разделяется. Внутреннее состояние (общее) хранится во flyweight, внешнее (уникальное) передаётся в методы. Пример: рендеринг 10000 деревьев в лесу - тип дерева (модель, текстура) общий, координаты уникальны.',
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
                'answer' => 'Структурно они близнецы: оба реализуют общий с целью интерфейс и делегируют ей вызовы. Разница — в намерении. Decorator РАСШИРЯЕТ поведение цели (логирование, кеширование, шифрование), цель передаётся извне, декораторы можно стекать (Cache(Logger(Repo))). Proxy КОНТРОЛИРУЕТ доступ к цели и часто сам управляет её жизненным циклом: ленивая инициализация (Virtual Proxy), проверка прав (Protection Proxy), удалённый объект (Remote Proxy), кеширование результата. Laravel: декораторы — CachingRepo, LoggingRepo. Proxy — Eloquent lazy relations, Doctrine entity proxies.',
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
                'answer' => 'Facade задумывается как тонкий слой над сложной подсистемой, но со временем в него тянут всё новые методы — и он становится фасадом для всего приложения сразу, зависящим от десятков классов. Тогда он и сам нарушает SRP, и провоцирует клиентов вызывать "одну удобную точку" вместо явных зависимостей. Лечится разбиением на несколько узких фасадов по бизнес-сценариям, перемещением части логики в отдельные сервисы и явным запретом тащить в фасад методы, не относящиеся к заявленной подсистеме.',
                'difficulty' => 4,
                'topic' => 'oop.gof_structural',
            ],
            [
                'category' => 'ООП',
                'question' => 'Почему стек декораторов чувствителен к порядку и как этим управлять?',
                'answer' => 'Декораторы оборачивают друг друга, поэтому результат зависит от того, кто внешний, а кто внутренний: например, кэширующий декоратор поверх логирующего закэширует и логи, а в обратном порядке — нет. Удалить конкретную обёртку из середины стека тоже непросто: нужно пересобрать цепочку. На практике порядок задают явно при сборке (часто в DI-контейнере или билдере), стараются проектировать декораторы так, чтобы они были коммутативны, и документируют ожидаемую последовательность слоёв.',
                'difficulty' => 4,
                'topic' => 'oop.gof_structural',
            ],
            [
                'category' => 'ООП',
                'question' => 'Когда Bridge оказывается излишним и только усложняет код?',
                'answer' => 'Bridge оправдан, когда абстракция и реализация реально варьируются по двум независимым осям — например, фигуры и рендереры, платёжные провайдеры и транспорты. Если же класс уже сплочён и его реализация одна, насильное разделение на иерархии абстракции и имплементации только добавляет интерфейсов и фабрик без выигрыша. Признак злоупотребления — "вторая ось" не имеет реальных альтернативных реализаций и появилась "на всякий случай"; в таком случае проще оставить один класс и применить Bridge позже, когда вторая ось действительно понадобится.',
                'difficulty' => 4,
                'topic' => 'oop.gof_structural',
            ],
        ];
    }
}
