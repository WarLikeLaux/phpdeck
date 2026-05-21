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
                'answer' => 'PSR-14 определяет общий интерфейс диспетчеризации событий и состоит из трёх частей: объекта события (произвольный класс, опционально реализующий StoppableEventInterface), ListenerProviderInterface, который по событию возвращает список слушателей, и EventDispatcherInterface, чей dispatch() прогоняет событие через слушателей. Разделение dispatcher и provider позволяет независимо менять стратегию подбора слушателей и саму доставку, а stoppable-события дают возможность раннего прерывания цепочки.',
                'difficulty' => 4,
                'topic' => 'php.psr',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что описывает PSR-17 и зачем фабрики при наличии PSR-7?',
                'answer' => 'PSR-17 определяет интерфейсы фабрик для создания PSR-7 объектов: RequestFactory, ResponseFactory, StreamFactory, UriFactory, UploadedFileFactory и ServerRequestFactory. Поскольку PSR-7 объекты иммутабельны и не имеют стандартизированных конструкторов, фабрики нужны, чтобы код мог создавать новые Request или Response без жёсткой привязки к конкретной реализации (Guzzle PSR-7, nyholm/psr7, laminas-diactoros). Это особенно важно для middleware и HTTP-клиентов PSR-18.',
                'difficulty' => 4,
                'topic' => 'php.psr',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое PER Coding Style и чем он отличается от PSR-12?',
                'answer' => 'PER (PHP Evolved Recommendation) Coding Style — преемник PSR-12 и «живой» стандарт стиля кодирования от PHP-FIG. В отличие от PSR, которые после принятия фиксируются и не меняются, PER может выпускать новые версии, чтобы покрывать свежий синтаксис: constructor property promotion, enums, readonly, first-class callable, named arguments. PER расширяет PSR-12 и по-прежнему опирается на PSR-1, поэтому существующие линтеры и автоформаттеры (php-cs-fixer, PHP_CodeSniffer) постепенно переключаются именно на него.',
                'difficulty' => 4,
                'topic' => 'php.psr',
            ],
            [
                'category' => 'PHP',
                'question' => 'В чём разница между PSR-6 и PSR-16 для кэширования?',
                'answer' => 'PSR-6 — «тяжёлый» интерфейс кэша на основе пула и объектов CacheItem: вы получаете item через getItem(), проверяете isHit(), при промахе заполняете значением и сохраняете через save(). PSR-16 (SimpleCache) — упрощённый интерфейс с прямыми методами get/set/delete/has, без обёртки CacheItem. PSR-6 удобнее для отложенного сохранения и работы с метаданными, PSR-16 — для простых сценариев и быстрого старта; библиотеки часто реализуют оба интерфейса поверх одного бэкенда.',
                'difficulty' => 4,
                'topic' => 'php.psr',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что описывает PSR-18 и как он сочетается с PSR-7 и PSR-17?',
                'answer' => 'PSR-18 определяет ClientInterface — единый интерфейс HTTP-клиента с одним методом sendRequest(RequestInterface): ResponseInterface. Запрос передаётся как PSR-7 объект (создаваемый PSR-17 фабрикой) и ответ возвращается как PSR-7. Это позволяет писать SDK и библиотеки, не привязываясь к Guzzle, Symfony HttpClient или cURL: пользователь подставляет любой PSR-18 клиент. Стандартизированы и исключения: ClientExceptionInterface с подтипами NetworkExceptionInterface и RequestExceptionInterface.',
                'difficulty' => 4,
                'topic' => 'php.psr',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что описывает PSR-20 и зачем абстрагировать чтение времени?',
                'answer' => 'PSR-20 определяет ClockInterface с единственным методом now(), возвращающим DateTimeImmutable. Стандарт нужен, чтобы код не вызывал new DateTime() или time() напрямую: в тестах вы подменяете часы фиксированным или замороженным временем и получаете детерминированные тесты для логики, зависящей от текущей даты (просрочка токенов, окна rate limit, расписания). Это аналог Clock в Java и системных часов в .NET, оформленный для PHP-экосистемы.',
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
