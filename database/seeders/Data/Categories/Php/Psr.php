<?php

namespace Database\Seeders\Data\Categories\Php;

class Psr
{
    public static function all(): array
    {
        return [
            [
                'category' => 'PHP',
                'question' => 'Что описывает PSR-1 и какие базовые правила он задаёт?',
                'answer' => '**PSR-1** — базовый стандарт кодирования от **PHP-FIG**, обеспечивающий совместимость кода между проектами.

**Главные правила:**
- только теги **`<?php`** и **`<?=`** (без `<?` и `<%`)
- кодировка **UTF-8 без BOM**
- классы в **`StudlyCaps`** (`UserController`)
- методы в **`camelCase`** (`getUserName`)
- константы классов в **`UPPER_SNAKE_CASE`** (`MAX_USERS`)

**Правило «одно из двух»:** файл должен **либо** объявлять символы (классы, функции, константы), **либо** производить побочные эффекты (`echo`, `require`, `header()`), **но не то и другое сразу**.',
                'difficulty' => 2,
                'topic' => 'php.psr',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое PSR-12 и чем он отличается от PSR-2?',
                'answer' => '**PSR-12** — расширенный стандарт стиля кодирования, **заменивший устаревший PSR-2** и описывающий более полный набор правил.

**Главные правила:**
- отступы — **4 пробела**, табуляция запрещена
- **мягкий лимит строки** — 120 символов (рекомендуется до 80)
- **открывающая `{`** классов и методов — на **новой строке**
- открывающая `{` управляющих конструкций (`if`, `for`) — на той же строке
- **обязательное** указание видимости (`public`/`protected`/`private`) у всех свойств и методов
- ключевые слова и `true`/`false`/`null` — в **нижнем регистре**

**Сегодня:** PSR-12 формально заменён **«живым» документом PER Coding Style**, который продолжает развитие под современный синтаксис (enums, readonly, promotion).',
                'difficulty' => 2,
                'topic' => 'php.psr',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем PSR-4 отличается от устаревшего PSR-0?',
                'answer' => '**PSR-4** — современный стандарт автозагрузки. Заменил устаревший **PSR-0**.

| Свойство | **PSR-4** | **PSR-0** (deprecated) |
|---|---|---|
| Маппинг префикса | префикс **`App\\`** → произвольная папка `app/` | **весь** путь должен отражать namespace |
| Подчёркивания в имени | как обычные символы | трактовались как **разделители каталогов** |
| Vendor namespace | требуется (`Vendor\\Package\\...`) | требуется |
| Подходит для PSR-0 совместимых классов из PHP <5.3 | нет | да |

**Пример PSR-4:**
```json
"autoload": { "psr-4": { "App\\\\": "app/" } }
```
- класс `App\\Http\\Controllers\\UserController` → файл `app/Http/Controllers/UserController.php`
- префикс **`App\\`** маппится в **`app/`**, дальше структура повторяет namespace

**Пример PSR-0** (старый):
- класс `Vendor_Package_Module_Class` → файл `Vendor/Package/Module/Class.php` (подчёркивания → слеши)
- это **наследие PHP 5.2** и до-namespace эпохи

**Что использовать:**
- **новый код** — **только PSR-4**
- PSR-0 в `composer.json` Composer всё ещё поддерживает, но **deprecated**
- composer dump-autoload генерирует **карту классов** для PSR-4 — оптимизация поиска',
                'difficulty' => 3,
                'topic' => 'php.psr',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что описывает PSR-3 и зачем нужен LoggerInterface?',
                'answer' => '**PSR-3** — общий интерфейс для библиотек логирования. Определяет **`Psr\\Log\\LoggerInterface`**.

**Восемь уровней** (от RFC 5424, syslog):

| Уровень | Когда |
|---|---|
| **`debug`** | детальная отладка |
| **`info`** | обычные события (запрос, регистрация) |
| **`notice`** | необычное, но не ошибка |
| **`warning`** | потенциальная проблема |
| **`error`** | ошибка, требует внимания, но не критично |
| **`critical`** | критично — БД упала, компонент сломан |
| **`alert`** | срочно — нужно реагировать сразу |
| **`emergency`** | система непригодна |

Плюс универсальный `log($level, $message, $context)`.

**Зачем нужен:** приложения и библиотеки **зависят от абстракции**, а не от Monolog/Laravel Log:
```php
public function __construct(private LoggerInterface $logger) {}
```
Пользователь подставит **любой** совместимый: Monolog, Laravel `Log`, Symfony Logger, `NullLogger`.

**Стандартизированы плейсхолдеры** в фигурных скобках `{name}`:
```php
$logger->info("User {email} registered", ["email" => $email]);
```
- бэкенд **сам подставит** значение из `context`
- структурированные данные (JSON-логи, ELK, Loki) — `context` идёт **отдельным полем**

**Дополнительно:** `Psr\\Log\\LoggerAwareInterface` для опциональной инъекции через setter, `NullLogger` — заглушка для тестов и default-инициализации.',
                'code_example' => '<?php
use Psr\\Log\\LoggerInterface;

class UserService
{
    public function __construct(private LoggerInterface $logger) {}

    public function register(string $email): void
    {
        // {placeholder} — стандарт PSR-3
        $this->logger->info("User {email} registered", ["email" => $email]);
    }
}

// Подойдёт любой PSR-3: Monolog, Laravel Log, Symfony, NullLogger',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.psr',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что описывает PSR-7 и почему его объекты неизменяемы?',
                'answer' => '**PSR-7** определяет общие интерфейсы для **HTTP-сообщений**:

| Интерфейс | Что моделирует |
|---|---|
| `MessageInterface` | базовое — заголовки, тело, версия HTTP |
| `RequestInterface` | исходящий HTTP-запрос (метод, URI) |
| `ServerRequestInterface` | входящий запрос на сервер (`$_GET`, `$_POST`, `$_FILES`, `$_COOKIE`, `$_SERVER`) |
| `ResponseInterface` | HTTP-ответ (статус, тело) |
| `UriInterface` | URI |
| `StreamInterface` | поток тела (file-stream, in-memory) |
| `UploadedFileInterface` | загруженный файл |

**Главная особенность — иммутабельность:**

Все методы-модификаторы называются **`with*()`** и **возвращают новый экземпляр** вместо изменения текущего:
```php
$new = $response->withHeader("X-Foo", "bar")->withStatus(201);
// $response не изменился
```

**Зачем иммутабельность:**
- **безопасно** передавать объект между слоями middleware — никто не «подкрутит» его незаметно
- **проще рассуждать** о состоянии запроса
- работает в **многопоточных** runtime-ах (Octane, Swoole) без synchronization
- даёт **time-travel debugging** — каждый шаг возвращает новый объект

**Интероперабельность:** один HTTP-клиент или middleware-стек работает поверх **любого** PSR-7 ядра (Guzzle PSR-7, nyholm/psr7, laminas-diactoros).

**Подводные камни:**
- забыть, что `withHeader` возвращает **новый** объект — частая ошибка, оригинал не меняется
- **stream позиции** — `$body->rewind()` нужен перед повторным чтением
- цепочки `with*` мутируют **много** памяти, на горячем пути дорого',
                'code_example' => '<?php
use Psr\\Http\\Message\\ResponseInterface;

function addCors(ResponseInterface $response): ResponseInterface
{
    // НЕ мутируем — каждый with* возвращает новый объект
    return $response
        ->withHeader("Access-Control-Allow-Origin", "*")
        ->withHeader("Access-Control-Allow-Methods", "GET, POST")
        ->withStatus(200);
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.psr',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что описывает PSR-15 и как он связан с PSR-7?',
                'answer' => '**PSR-15** стандартизирует **серверные HTTP-компоненты** на базе **PSR-7**.

**Два интерфейса:**

| Интерфейс | Метод | Что делает |
|---|---|---|
| `RequestHandlerInterface` | `handle(ServerRequestInterface): ResponseInterface` | конечный обработчик (контроллер, экшен) |
| `MiddlewareInterface` | `process(ServerRequestInterface, RequestHandlerInterface): ResponseInterface` | слой, обёртывающий обработчик |

**Поток:**
1. Middleware получает **запрос** и **следующий обработчик** (`$handler`)
2. Может **изменить запрос** перед передачей: `$request->withAttribute("user", $user)`
3. Вызывает `$handler->handle($request)` — **либо нет** (короткое замыкание, например auth)
4. Может **изменить ответ** после: `$response->withHeader(...)`

**Что строится поверх:**
- цепочки **auth, logging, CORS, rate-limit, cache** на любом фреймворке
- переносимые middleware-пакеты (`middlewares/*`)

**Single-pass vs double-pass:**

| Модель | Сигнатура | Статус |
|---|---|---|
| **Single-pass** (PSR-15) | `process($req, $handler)` | **актуальный** стандарт |
| **Double-pass** (старая) | `__invoke($req, $res, $next)` | устарело — `$res` приходил пустой, неоднозначно |

**Single-pass** более явный, **типобезопасный**, не плодит «пустой ответ для модификации».

**Подводный камень:** middleware-цепочка — это **рекурсия** через `$handler->handle()`. Глубокие цепочки могут заметно нагрузить стек на больших нагрузках. Большинство фреймворков (Slim, Mezzio, Laravel pipeline) разруливают через `array_reduce` или построение цепочки заранее.',
                'code_example' => '<?php
use Psr\\Http\\Message\\{ServerRequestInterface, ResponseInterface};
use Psr\\Http\\Server\\{MiddlewareInterface, RequestHandlerInterface};

class AuthMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
    ): ResponseInterface {
        if (!$request->getHeaderLine("Authorization")) {
            return new Response(401);
        }
        return $handler->handle($request); // вперёд по цепочке
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.psr',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что описывает PSR-11 и какие методы у ContainerInterface?',
                'answer' => '**PSR-11** — общий интерфейс **DI-контейнеров**. Определяет **`Psr\\Container\\ContainerInterface`** с двумя методами:

| Метод | Что делает |
|---|---|
| **`get(string $id): mixed`** | получить запись по идентификатору; бросает `NotFoundExceptionInterface`, если нет |
| **`has(string $id): bool`** | проверка наличия записи |

**Зачем нужен:** библиотека/пакет может принять **любой** PSR-11 контейнер и работать с ним, не зная, **Symfony**, **Laravel** или **PHP-DI**.

```php
public function __construct(private ContainerInterface $container) {}
```

**Стандартизированные исключения:**

| Интерфейс | Когда |
|---|---|
| `ContainerExceptionInterface` | базовая ошибка контейнера |
| `NotFoundExceptionInterface` extends ContainerExceptionInterface | запись не найдена |

Поэтому код может писать:
```php
try { $svc = $container->get("logger"); }
catch (NotFoundExceptionInterface $e) { /* нет */ }
catch (ContainerExceptionInterface $e) { /* ошибка контейнера */ }
```

**Что PSR-11 НЕ определяет:**
- **как** биндить сервисы (`bind`, `singleton`, фабрики, autowiring) — это **дело реализации**
- область видимости (scoped, singleton, transient)
- **PSR-11 — read-only** контракт для **потребителя**, не для конфигурации

**Подводный камень:** использование `$container->get()` напрямую в коде сервиса — это **Service Locator antipattern**. Контейнер должен **сам внедрять** зависимости через конструктор (DI), `get()` — только в **корневом composition root** (роутер, фабрика).',
                'difficulty' => 3,
                'topic' => 'php.psr',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что описывает PSR-14 и из каких частей состоит Event Dispatcher?',
                'answer' => '**PSR-14** — общий интерфейс диспетчеризации событий. Состоит из **трёх частей**:

| Часть | Что это | Метод |
| --- | --- | --- |
| **Event** | произвольный класс события (POPO); опционально `StoppableEventInterface` | — |
| **`ListenerProviderInterface`** | по событию возвращает **iterable** слушателей | **`getListenersForEvent(object $event): iterable`** |
| **`EventDispatcherInterface`** | прогоняет событие через слушатели | **`dispatch(object $event): object`** |

**Разделение dispatcher ↔ provider:**
- **dispatcher** — отвечает за **доставку** (синхронно, через очередь, в Fibers)
- **provider** — отвечает за **подбор** слушателей (по типу, по атрибутам, из конфига)
- их можно **подменять независимо** — тестовый provider + реальный dispatcher и наоборот

**`StoppableEventInterface` (опционально на событии):**
- метод **`isPropagationStopped(): bool`**
- если возвращает `true` — dispatcher **прекращает** обход оставшихся слушателей
- классический use case: **`BeforeSaveEvent`** — один listener валидирует и останавливает сохранение

**В Laravel:**
- встроенный диспетчер **до версии 11** не реализовал PSR-14, но был адаптер
- сегодня типично подключают **`symfony/event-dispatcher`** как PSR-14 реализацию

**Где встречается:** Symfony, Mezzio, Laminas; библиотеки-плагины обычно **зависят от PSR-14 контракта**, а не от конкретного фреймворка.',
                'code_example' => '<?php
use Psr\\EventDispatcher\\{
    EventDispatcherInterface,
    ListenerProviderInterface,
    StoppableEventInterface,
};

// Event — обычный объект
final class UserRegistered {
    public function __construct(public readonly int $userId) {}
}

// Stoppable event
final class BeforeSave implements StoppableEventInterface {
    private bool $stopped = false;
    public function stopPropagation(): void { $this->stopped = true; }
    public function isPropagationStopped(): bool { return $this->stopped; }
}

// Provider — список слушателей по типу
final class TypedProvider implements ListenerProviderInterface {
    /** @var array<class-string, callable[]> */
    private array $map = [];

    public function on(string $eventClass, callable $listener): void {
        $this->map[$eventClass][] = $listener;
    }

    public function getListenersForEvent(object $event): iterable {
        foreach ($this->map as $type => $listeners) {
            if ($event instanceof $type) yield from $listeners;
        }
    }
}

// Dispatcher
final class Dispatcher implements EventDispatcherInterface {
    public function __construct(private ListenerProviderInterface $provider) {}

    public function dispatch(object $event): object {
        foreach ($this->provider->getListenersForEvent($event) as $listener) {
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                break;
            }
            $listener($event);
        }
        return $event;
    }
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.psr',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что описывает PSR-17 и зачем фабрики при наличии PSR-7?',
                'answer' => '**PSR-17** — интерфейсы **фабрик** для создания PSR-7 объектов.

**Шесть фабрик:**

| Интерфейс | Создаёт | Основной метод |
| --- | --- | --- |
| **`RequestFactoryInterface`** | `RequestInterface` | `createRequest(string $method, $uri)` |
| **`ResponseFactoryInterface`** | `ResponseInterface` | `createResponse(int $code = 200, string $reason = "")` |
| **`ServerRequestFactoryInterface`** | `ServerRequestInterface` | `createServerRequest($method, $uri, array $serverParams = [])` |
| **`StreamFactoryInterface`** | `StreamInterface` | `createStream(string $content = "")`, `createStreamFromFile(...)`, `createStreamFromResource(...)` |
| **`UriFactoryInterface`** | `UriInterface` | `createUri(string $uri = "")` |
| **`UploadedFileFactoryInterface`** | `UploadedFileInterface` | `createUploadedFile(StreamInterface $stream, ?int $size = null, ...)` |

**Зачем фабрики при наличии PSR-7:**
- PSR-7 объекты **immutable** — нет стандартного «конструктора», у каждой реализации **свой** new
- библиотека/SDK хочет создавать **`Response`** или **`Request`**, не зная, какая реализация подключена (**Guzzle PSR-7**, **nyholm/psr7**, **laminas-diactoros**, **slim/psr7**)
- через **DI** инжектится PSR-17 фабрика — конкретный класс выбирает пользователь

**Где критично:**
- **PSR-15 middleware** — нужно создать новый `Response` без жёсткого `new GuzzleResponse()`
- **PSR-18 HTTP-клиенты** — внутри строят `Request` из URL/метода
- **тесты** — мок фабрики возвращает заранее подготовленные объекты

**Типичная связка в DI-контейнере (`nyholm/psr7` — самая компактная реализация):**
```php
$factory = new \\Nyholm\\Psr7\\Factory\\Psr17Factory();
$container->set(ResponseFactoryInterface::class, $factory);
$container->set(StreamFactoryInterface::class, $factory);
$container->set(UriFactoryInterface::class, $factory);
// один класс реализует все шесть фабрик
```',
                'code_example' => '<?php
use Psr\\Http\\Message\\{ResponseFactoryInterface, StreamFactoryInterface};

final class JsonResponder
{
    public function __construct(
        private ResponseFactoryInterface $responses,
        private StreamFactoryInterface $streams,
    ) {}

    public function ok(array $data): Psr\\Http\\Message\\ResponseInterface
    {
        $body = $this->streams->createStream(
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
        );

        return $this->responses->createResponse(200)
            ->withHeader("Content-Type", "application/json")
            ->withBody($body);
    }
}

// Та же логика работает с Nyholm/psr7, GuzzleHttp/Psr7, Laminas/Diactoros —
// разница только в DI-биндинге фабрики.',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.psr',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое PER Coding Style и чем он отличается от PSR-12?',
                'answer' => '**PER (PHP Evolved Recommendation) Coding Style** — преемник **PSR-12** и **«живой»** стандарт стиля кодирования от **PHP-FIG**.

**Главное отличие от PSR:**

| | PSR | PER |
| --- | --- | --- |
| Статус документа | **фиксированный** после принятия (frozen) | **versioned, evolving** |
| Может ли обновляться | нет — только новая PSR | **да** — новые версии (PER 2.0, 3.0) |
| Покрытие современного синтаксиса | PSR-12 не охватывает PHP 8.x | **охватывает** |
| Обратная совместимость | строгая | через **major-версии** |

**Что добавляет PER поверх PSR-12:**
- **constructor property promotion** — форматирование promoted-параметров
- **enums** (PHP 8.1) — отступы кейсов, методов
- **readonly** свойства и классы
- **first-class callable** (`func(...)`)
- **named arguments** — выравнивание, разрывы строк
- **match-выражения** — отступы и `,` после последнего варианта
- **intersection types** (`A&B`), **DNF types** (`(A&B)|C` в PHP 8.2)

**Связь с PSR-1:**
- PER **наследует** базовые правила PSR-1 (теги, кодировка, naming)
- расширяет, но **не отменяет** их

**Инструменты:**

| Инструмент | Поддержка PER |
| --- | --- |
| **`friendsofphp/php-cs-fixer`** | **set `@PER-CS`** |
| **`squizlabs/php_codesniffer`** | стандарт **`PER`** |
| **PhpStorm** | импорт через PHP > Code Style |

**Best practice:** новые проекты — **PER**; легаси на PSR-12 — мигрировать постепенно (оба совместимы по базе).',
                'code_example' => '# Composer
composer require --dev friendsofphp/php-cs-fixer

# .php-cs-fixer.php
<?php
return (new PhpCsFixer\Config())
    ->setRules([
        "@PER-CS"             => true,    // главное правило
        "@PER-CS:risky"       => true,    // плюс strict_types и т.п.
        "@PHP84Migration"     => true,    // обновлять на актуальный синтаксис
    ])
    ->setRiskyAllowed(true);

# Запуск
vendor/bin/php-cs-fixer fix
vendor/bin/php-cs-fixer fix --dry-run --diff  # в CI',
                'code_language' => 'bash',
                'difficulty' => 4,
                'topic' => 'php.psr',
            ],
            [
                'category' => 'PHP',
                'question' => 'В чём разница между PSR-6 и PSR-16 для кэширования?',
                'answer' => 'Два **параллельных** стандарта кеширования от PHP-FIG, **не заменяющих** друг друга.

**Сравнение интерфейсов:**

| | **PSR-6** (Cache Pool) | **PSR-16** (SimpleCache) |
| --- | --- | --- |
| Главный интерфейс | **`CacheItemPoolInterface`** | **`CacheInterface`** |
| Тип возврата `get` | **`CacheItemInterface`** (обёртка) | значение **напрямую** |
| Промах | `item->isHit() === false` | возвращает **`$default`** аргумент |
| Запись | `$item->set($v)->expiresAfter($ttl); $pool->save($item)` | **`$cache->set($key, $v, $ttl)`** |
| Отложенное сохранение | **`saveDeferred()` + `commit()`** | **нет** |
| Метаданные у item-а | **да** (`getKey`, expiration) | нет |
| Сложность API | выше | ниже |

**PSR-6 — типичная работа с item-ами:**
```php
$item = $pool->getItem("user.42");
if (!$item->isHit()) {
    $item->set($expensive)->expiresAfter(3600);
    $pool->save($item);
}
$value = $item->get();
```

**PSR-16 — прямые методы:**
```php
$value = $cache->get("user.42") ?? $cache->set("user.42", $compute(), 3600);
```

**Когда что выбирать:**

| Сценарий | Стандарт |
| --- | --- |
| Простой `get/set/delete` | **PSR-16** |
| Нужны **отложенные** записи (`saveDeferred` + `commit` пакетно) | **PSR-6** |
| Нужны **тэги**, метаданные item | PSR-6 расширения (Symfony Cache, Cache/TagInterop) |
| Hot path, минимум boilerplate | **PSR-16** |
| Совместимость с фреймворком, который ждёт пул | **PSR-6** |

**Реализации:** **Symfony Cache**, **`cache/cache`** — реализуют **оба** интерфейса поверх одного бэкенда (Redis, Memcached, APCu, файлы). Можно дёрнуть тот, что нужен.

**Подводный камень:** **`InvalidArgumentException`** в обоих стандартах **не** PSR-6/16-специфичный — это **`Psr\\Cache\\InvalidArgumentException`** / **`Psr\\SimpleCache\\InvalidArgumentException`**, разные namespace-ы.',
                'difficulty' => 4,
                'topic' => 'php.psr',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что описывает PSR-18 и как он сочетается с PSR-7 и PSR-17?',
                'answer' => '**PSR-18** — общий интерфейс **HTTP-клиента**. Определяет **`Psr\\Http\\Client\\ClientInterface`** с **одним методом**:

```php
public function sendRequest(RequestInterface $request): ResponseInterface;
```

**Связка с PSR-7 и PSR-17:**

| Стандарт | Роль |
| --- | --- |
| **PSR-7** | контракт **сообщений** (`Request`, `Response`, `Stream`) |
| **PSR-17** | **фабрики** для создания PSR-7 объектов |
| **PSR-18** | контракт **отправки** запроса и получения ответа |

SDK или библиотека **зависит от всех трёх интерфейсов**, не привязываясь к Guzzle / Symfony HttpClient / cURL.

**Иерархия исключений (`Psr\\Http\\Client\\`):**

| Интерфейс | Когда |
| --- | --- |
| **`ClientExceptionInterface`** | базовое — любое исключение клиента |
| **`NetworkExceptionInterface`** extends ClientException | сетевая проблема (DNS, connection refused, TCP-reset); **`getRequest()`** доступен |
| **`RequestExceptionInterface`** extends ClientException | запрос **невалиден** (нельзя сериализовать тело, неверный URI) |

**Важно:** **HTTP-ответы 4xx/5xx — это НЕ исключение**. PSR-18 возвращает их как обычный `Response`. Бросать `ClientException` нужно **только** при невозможности получить ответ.

**Конкретные реализации:**

| Клиент | Пакет |
| --- | --- |
| **Guzzle** (поверх cURL) | `guzzlehttp/guzzle` |
| **Symfony HttpClient** (cURL+amphp) | `symfony/http-client` |
| **Buzz** | `kriswallsmith/buzz` |
| **`php-http/curl-client`** | минимальный adapter на cURL |
| **mock** для тестов | `php-http/mock-client` |

**HTTPlug discovery** — `php-http/discovery` помогает SDK найти **любую** установленную реализацию без жёсткой зависимости.',
                'code_example' => '<?php
use Psr\\Http\\Client\\{ClientInterface, NetworkExceptionInterface, ClientExceptionInterface};
use Psr\\Http\\Message\\{RequestFactoryInterface, StreamFactoryInterface};

final class GithubClient
{
    public function __construct(
        private ClientInterface         $http,    // PSR-18
        private RequestFactoryInterface $requests, // PSR-17
        private StreamFactoryInterface  $streams,
    ) {}

    /** @return array<string, mixed> */
    public function repo(string $owner, string $name): array
    {
        $req = $this->requests
            ->createRequest("GET", "https://api.github.com/repos/$owner/$name")
            ->withHeader("Accept", "application/vnd.github+json")
            ->withHeader("User-Agent", "my-sdk");

        try {
            $res = $this->http->sendRequest($req);
        } catch (NetworkExceptionInterface $e) {
            throw new RuntimeException("network: " . $e->getMessage(), previous: $e);
        } catch (ClientExceptionInterface $e) {
            throw new RuntimeException("http client: " . $e->getMessage(), previous: $e);
        }

        // 4xx/5xx — НЕ исключение в PSR-18, проверяем сами
        if ($res->getStatusCode() >= 400) {
            throw new RuntimeException("api: " . $res->getStatusCode());
        }

        return json_decode((string) $res->getBody(), true, flags: JSON_THROW_ON_ERROR);
    }
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.psr',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что описывает PSR-20 и зачем абстрагировать чтение времени?',
                'answer' => '**PSR-20** — стандарт **«часов»**. Определяет **`Psr\\Clock\\ClockInterface`** с единственным методом:

```php
public function now(): DateTimeImmutable;
```

**Зачем абстрагировать чтение времени:**
- прямые вызовы `new DateTime()`, `time()`, `microtime()` делают код **недетерминированным**
- тесты, зависящие от текущей даты, становятся **flaky** (проходит сейчас, упадёт в следующем месяце)
- инжектируем `ClockInterface` → в тестах подставляем **`FixedClock`** или **`FrozenClock`**

**Где особенно нужно:**
- **срок действия** токенов (JWT, password reset)
- **окна rate-limit** (`5 запросов в минуту`)
- **расписания** (`scheduler->daily()`)
- **TTL** в кешах
- **бизнес-логика** дат (просрочка платежа, дни до окончания подписки)

**Стандартные реализации (`symfony/clock`):**

| Реализация | Что делает |
| --- | --- |
| **`NativeClock`** | обычные системные часы (`new DateTimeImmutable("now")`) |
| **`MockClock`** | фиксированное время, `sleep()` сдвигает виртуально |
| **`MonotonicClock`** | для измерения интервалов (не идёт назад при NTP-корректировке) |

**Аналоги в других языках:**
- Java — `java.time.Clock`
- .NET — `System.TimeProvider` (с .NET 8)
- Rust — `std::time::Instant` + ручная абстракция

**В Laravel:** **`Carbon::setTestNow()`** + хелпер `$this->travelTo()` исторически выполняли ту же роль. Современный путь — инжектировать **`ClockInterface`** в сервисы и использовать `MockClock` в feature-тестах для совместимости с не-Carbon кодом.',
                'code_example' => '<?php
use Psr\\Clock\\ClockInterface;
use Symfony\\Component\\Clock\\{NativeClock, MockClock};

final class TokenValidator
{
    public function __construct(private ClockInterface $clock) {}

    public function isExpired(DateTimeImmutable $expiresAt): bool
    {
        return $this->clock->now() > $expiresAt;
    }
}

// В тестах — детерминированно
$clock = new MockClock("2026-01-01 12:00:00");
$validator = new TokenValidator($clock);

$expiresAt = new DateTimeImmutable("2026-01-01 13:00:00");
var_dump($validator->isExpired($expiresAt)); // false

$clock->sleep(7200); // +2 часа
var_dump($validator->isExpired($expiresAt)); // true — без реальной паузы',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.psr',
            ],
            [
                'category' => 'PHP',
                'question' => 'Зачем нужны стандарты PSR в PHP-экосистеме?',
                'answer' => '**PSR** — рекомендации **PHP-FIG**, обеспечивающие **интероперабельность** между фреймворками и библиотеками.

**Что это даёт:**
- код против **`PSR-3`-логгера** или **`PSR-11`-контейнера** работает с **любой** совместимой реализацией без переписывания
- **единый стиль** (PSR-1/12, PER) — проще читать чужой код
- **PSR-4 автозагрузка** — Composer сам находит классы по namespace
- **PSR-7 / PSR-15** — middleware и HTTP-сообщения переносятся между фреймворками
- легче собирать **Composer-пакеты** и SDK

**Без PSR** каждая крупная библиотека определяла бы собственные интерфейсы и адаптеры — экосистема бы **фрагментировалась**, как было в эпоху до 2014.',
                'difficulty' => 2,
                'topic' => 'php.psr',
            ],
            [
                'category' => 'PHP',
                'question' => 'Какой статус у PSR-0, PSR-2 и PSR-12 сегодня?',
                'answer' => '| PSR | Статус | Чем заменён |
|---|---|---|
| **PSR-0** (автозагрузка) | **deprecated** | **PSR-4** |
| **PSR-2** (стиль кодирования) | заменён | **PSR-12** |
| **PSR-12** (расширенный стиль) | формально заменён | **PER Coding Style** (живой документ) |

**PSR-0 → PSR-4:**
- PSR-0 трактовал **подчёркивания** в имени класса как разделители каталогов (наследие до-namespace PHP 5.2)
- PSR-0 требовал **полного** отражения namespace в структуре папок
- PSR-4 даёт **гибкий маппинг** префикса на произвольную базовую директорию

**PSR-2 → PSR-12 → PER:**
- PSR-2 — фиксированный документ от 2012 года, синтаксис PHP того времени
- **PSR-12** расширил его (PHP 7-эра): typed properties, return types, intersection-типы
- **PER (PHP Evolved Recommendation)** — «живой» документ, обновляется под современный синтаксис: **enums, readonly, property promotion, first-class callable, named arguments**

**На практике:**
- PSR-12 ещё **широко используется** как стабильный snapshot
- новые правила (PHP 8.1+ синтаксис) идут в **PER**
- инструменты — **php-cs-fixer**, **PHP_CodeSniffer** — постепенно переключаются на PER
- новые проекты обычно ориентируются на **PER**

**Что выбрать в проекте:** PER (если CI готов), иначе PSR-12 — оба совместимы по базе.',
                'difficulty' => 3,
                'topic' => 'php.psr',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое PSR простыми словами?',
                'answer' => '**PSR** (PHP Standards Recommendations) — набор стандартов от **PHP-FIG** (Framework Interop Group), которые принимают крупные фреймворки и библиотеки.

**Цель:** чтобы код от разных авторов работал вместе без переписывания.

**Самые ходовые PSR:**
- **PSR-1 / PSR-12** — стиль кода (PascalCase для классов, отступы, фигурные скобки)
- **PSR-4** — автозагрузка (namespace → путь к файлу)
- **PSR-3** — единый интерфейс логгера (`LoggerInterface`)
- **PSR-7** — HTTP-запросы и ответы
- **PSR-11** — DI-контейнер
- **PSR-15** — middleware

Знание PSR делает код переносимым между **Laravel**, **Symfony** и любыми другими совместимыми библиотеками.',
                'difficulty' => 1,
                'topic' => 'php.psr',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое namespace простыми словами?',
                'answer' => '**Пространство имён** — способ группировки классов, функций и констант, чтобы избежать **конфликтов имён** в разных частях проекта.

**Как работает:**
- `namespace App\\Models;` в начале файла делает класс `User` полным именем `App\\Models\\User` — это и есть **FQCN** (Fully Qualified Class Name).
- В другом файле сокращают через `use App\\Models\\User;` или дают алиас: `use App\\Models\\Post as PostModel;`.

По **PSR-4** namespace отображается на путь к файлу — это и позволяет Composer **автозагружать** классы.',
                'code_example' => '<?php
// app/Models/User.php
namespace App\\Models;
class User {}

// другой файл
use App\\Models\\User;
use App\\Models\\Post as PostModel;

$u = new User();
$p = new PostModel();',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.psr',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что делает PSR-4 простыми словами?',
                'answer' => '**PSR-4** описывает правила **автозагрузки классов** через сопоставление **namespace → путь к файлу**.

**Пример:**
- класс `App\Http\Controllers\UserController`
- лежит в `app/Http/Controllers/UserController.php`

**Как Composer это знает:** читает раздел **`autoload.psr-4`** в `composer.json`:
```
"autoload": {
    "psr-4": {
        "App\\": "app/"
    }
}
```
Префикс `App\\` маппится на каталог `app/`, дальше PSR-4 повторяет структуру namespace в файловой системе.

**Заменил устаревший PSR-0**, который трактовал подчёркивания в имени класса как разделители каталогов (наследие PHP до namespace).',
                'difficulty' => 2,
                'topic' => 'php.psr',
            ],
            [
                'category' => 'PHP',
                'question' => 'Какие соглашения по именованию приняты в PHP (PSR-1/PSR-12)?',
                'answer' => '- **Классы** — `PascalCase` (UpperCamelCase): `UserController`, `OrderRepository`
- **Методы** — `camelCase`: `getUserName`, `saveOrder`
- **Свойства и переменные** — `camelCase`: `$firstName`, `$userId`
- **Константы** — `UPPER_SNAKE_CASE`: `MAX_USERS`, `API_KEY`

**`snake_case`** для переменных и методов считается устаревшим стилем (наследие PHP 4 и WordPress).

**Исключение** — тестовые методы PHPUnit, где `snake_case` допускают для читаемости: `test_user_can_login`.',
                'code_example' => '<?php
class UserController {              // PascalCase
    private const MAX_RETRIES = 3;  // UPPER_SNAKE_CASE

    private string $firstName;      // camelCase

    public function getUserName(): string {  // camelCase
        $userId = 42;                // camelCase
        return $this->firstName;
    }
}',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.psr',
            ],
        ];
    }
}
