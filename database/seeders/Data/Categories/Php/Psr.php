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
                'answer' => 'PSR-4 — современный стандарт автозагрузки, заменивший PSR-0 и более гибкий: он позволяет привязывать префикс пространства имён к произвольной базовой директории, не требуя полного отражения namespace в структуре каталогов. PSR-0, напротив, требовал точного соответствия всего пути неймспейсу и трактовал подчёркивания в имени класса как разделители каталогов, что было наследием PHP 5.2 и до-namespace эпохи. На практике PSR-0 в Composer уже считается deprecated.',
                'difficulty' => 3,
                'topic' => 'php.psr',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что описывает PSR-3 и зачем нужен LoggerInterface?',
                'answer' => 'PSR-3 — общий интерфейс для библиотек логирования, определяющий LoggerInterface с восемью уровнями (debug, info, notice, warning, error, critical, alert, emergency) и методом log(). Он позволяет приложениям и библиотекам зависеть от абстракции, а не от конкретной реализации вроде Monolog: достаточно принять Psr\\Log\\LoggerInterface в конструкторе, и пользователь подставит любой совместимый логгер. Также стандартизирован формат плейсхолдеров в сообщениях через фигурные скобки.',
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
                'answer' => 'PSR-7 определяет общие интерфейсы для HTTP-сообщений: запросов, ответов, URI, потоков и загруженных файлов. Все объекты иммутабельны — методы вроде withHeader() возвращают новый экземпляр вместо изменения текущего, что делает их безопасными в многопоточном или middleware-окружении и упрощает рассуждения о состоянии запроса. Это даёт интероперабельность между фреймворками: один HTTP-клиент или middleware-стек работает поверх любого PSR-7 совместимого ядра.',
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
                'answer' => 'PSR-15 стандартизирует серверные HTTP-компоненты и определяет два интерфейса: RequestHandlerInterface и MiddlewareInterface. Middleware принимает PSR-7 запрос и следующий обработчик, а возвращает PSR-7 ответ, что позволяет выстраивать конвейер слоёв (аутентификация, логирование, CORS, кэш) поверх любой совместимой реализации. Стандарт целиком построен на PSR-7 и заменил старую модель double-pass middleware более явной и типобезопасной single-pass моделью.',
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
                'answer' => 'PSR-11 — общий интерфейс контейнеров внедрения зависимостей, описывающий ContainerInterface с двумя методами: get($id) для получения записи по идентификатору и has($id) для проверки её наличия. Стандарт делает библиотеки контейнеро-агностичными: пакет может принять любой PSR-11 контейнер и работать с ним, не зная, Symfony это, Laravel или PHP-DI. Также определены исключения NotFoundExceptionInterface и ContainerExceptionInterface для единообразной обработки ошибок.',
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
                'answer' => 'PSR-0 (старая автозагрузка) объявлен deprecated в пользу PSR-4 — он менее гибкий и тащит подчёркивания как разделители каталогов из эпохи PHP 5.2. PSR-2 (старый стиль кодирования) заменён PSR-12, который, в свою очередь, формально заменён «живым» документом PER Coding Style. На практике PSR-12 ещё широко используется как стабильный snapshot, но новые правила для современного синтаксиса (enums, readonly, promotion) идут уже в PER, поэтому новые проекты обычно ориентируются на него.',
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
