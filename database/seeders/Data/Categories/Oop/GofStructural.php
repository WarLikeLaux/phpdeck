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
                'answer' => 'Adapter (адаптер) преобразует интерфейс одного класса в интерфейс, ожидаемый клиентом. Простыми словами: позволяет работать вместе классам с несовместимыми интерфейсами. Аналогия - переходник для розетки. Часто используется при интеграции legacy-кода или сторонних библиотек.',
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
                'answer' => 'Composite (компоновщик) группирует объекты в древовидные структуры и позволяет работать с группой объектов так же, как с одиночным. Простыми словами: лист и контейнер реализуют один интерфейс. Пример - файловая система: и файл, и папка имеют size(), но папка считает size() как сумму содержимого.',
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
                'answer' => 'Decorator (декоратор) динамически добавляет объекту новые обязанности, оборачивая его в другой объект с тем же интерфейсом. Альтернатива наследованию для расширения поведения. Декораторы можно стекать. Пример: в Laravel pipeline - middleware оборачивает запрос. В Symfony - декорирование сервисов.',
                'code_example' => '<?php
interface Notifier
{
    public function send(string $msg): void;
}

class EmailNotifier implements Notifier
{
    public function send(string $msg): void
    {
        echo "Email: $msg\n";
    }
}

class SmsDecorator implements Notifier
{
    public function __construct(private Notifier $inner) {}

    public function send(string $msg): void
    {
        $this->inner->send($msg);
        echo "SMS: $msg\n";
    }
}

$n = new SmsDecorator(new EmailNotifier());
$n->send(\'Привет\'); // и email, и sms',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.gof_structural',
                'difficulty' => 3,
                'question' => 'Паттерн Facade',
                'answer' => 'Facade (фасад) предоставляет упрощённый унифицированный интерфейс к сложной подсистеме. Скрывает много мелких классов за одним удобным API. Не путать с Laravel Facades - там это статический прокси к контейнеру, а классический Facade - именно фасад над подсистемой. Используйте, когда хотите упростить взаимодействие с библиотекой/модулем.',
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
                'answer' => 'Proxy (заместитель) - объект, имеющий тот же интерфейс, что и реальный объект, но контролирующий доступ к нему. Виды: 1) Virtual Proxy - ленивая инициализация дорогого объекта. 2) Protection Proxy - проверка прав доступа. 3) Remote Proxy - представление удалённого объекта. 4) Logging/Caching Proxy. Используется в Doctrine (lazy entities), в Laravel - например, Eloquent relations при ленивой загрузке и lazy() proxies (PHP 8.4 lazy objects).',
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
