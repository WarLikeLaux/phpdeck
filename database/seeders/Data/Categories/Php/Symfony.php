<?php

namespace Database\Seeders\Data\Categories\Php;

class Symfony
{
    public static function all(): array
    {
        return [
            [
                'category' => 'PHP',
                'question' => 'Что такое Symfony и в чём его философия?',
                'answer' => '**Symfony** — это **набор многоразовых PHP-компонентов** и **full-stack веб-фреймворк** на их основе.

**Философия строится на трёх принципах:**
- **Повторное использование кода** — каждый компонент (`HttpFoundation`, `Console`, `EventDispatcher`, `Routing`) можно использовать **отдельно**, без всего фреймворка
- **Следование PSR** — стандарты автозагрузки, логирования, контейнера, HTTP
- **Слабая связанность** — через **DI и сервис-контейнер**, события и интерфейсы

**Где встречается:** компоненты Symfony — фундамент для **Laravel** (HttpFoundation, Console, Routing, Process), **Drupal**, **API Platform**, **Magento 2**. Знание Symfony-компонентов делает понятным внутреннее устройство многих фреймворков.',
                'difficulty' => 2,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое бандлы (Bundles) в Symfony?',
                'answer' => '**Бандл (Bundle)** — это **плагин**, который упаковывает связанный код, конфигурацию и ресурсы и подключается к ядру через **`AbstractBundle`** или Bundle-класс.

**В Symfony буквально всё устроено как бандл:**
- **`FrameworkBundle`** — ядро (роутинг, кеш, профайлер)
- **`SecurityBundle`** — фаерволы, аутентификация
- **`TwigBundle`** — шаблонизатор Twig
- **`DoctrineBundle`** — интеграция Doctrine ORM
- сторонние библиотеки тоже поставляются как бандлы

**С Symfony 4** ваше **приложение перестало быть отдельным `AppBundle`** — теперь это просто код в `src/`, регистрируемый в `bundles.php`. Сторонние библиотеки регистрируются автоматически через **Symfony Flex** при `composer require`.',
                'difficulty' => 2,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое Symfony Flex и как он работает с Composer?',
                'answer' => '**Flex** — плагин **Composer**, который автоматизирует установку и удаление Symfony-пакетов.

**Что он делает при `composer require ...`:**
- скачивает **рецепт** (recipe) из официального или приватного репозитория
- создаёт **конфиг-файлы** в `config/packages/<пакет>.yaml`
- регистрирует бандл в **`config/bundles.php`**
- добавляет переменные в **`.env`** и `.env.dist`
- пишет копию применённого рецепта в **`symfony.lock`** — чтобы при `composer remove` понять, что откатывать

**Зачем нужен:** одна команда вместо ручной правки 3–5 файлов. Установка `messenger`, `mailer`, `security-bundle` сводится к `composer require` — конфиги уже на месте.

**Aliases:** Flex даёт короткие имена пакетов — `composer require orm` ставит **`symfony/orm-pack`**.',
                'difficulty' => 3,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое сервис-контейнер в Symfony?',
                'answer' => '**Сервис-контейнер** — центральный объект, который **создаёт, хранит и связывает сервисы** (обычные PHP-объекты приложения) и управляет их **жизненным циклом**.

**Ключевая особенность:**
- Symfony **компилирует контейнер в кэш** — генерирует обычный PHP-класс `var/cache/.../App_KernelDevDebugContainer.php`
- в рантайме разрешение зависимостей **почти бесплатно** — это просто `new` в сгенерированном коде
- пересборка только при изменении конфигурации (в dev — автоматически)

**Конфигурируется через:**
- **`config/services.yaml`** — стандарт
- XML или **PHP-DSL** — для сложной логики
- **атрибуты** PHP 8 (`#[AsAlias]`, `#[Autoconfigure]`, `#[Target]`)

**В современных проектах** большинство сервисов регистрируется **автоматически** благодаря `_defaults: { autowire: true, autoconfigure: true }` и `resource: "../src/"`.',
                'difficulty' => 3,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое компонент HttpFoundation в Symfony?',
                'answer' => '**`HttpFoundation`** — объектно-ориентированная обёртка над **HTTP-спецификацией**. Заменяет суперглобалы (`$_GET`, `$_POST`, `$_SERVER`, `$_COOKIE`, `$_FILES`) на **тестируемые объекты**.

**Основные классы:**
- **`Symfony\\Component\\HttpFoundation\\Request`** — входящий запрос (`$request->query`, `$request->request`, `$request->headers`)
- **`Symfony\\Component\\HttpFoundation\\Response`** — ответ (`JsonResponse`, `RedirectResponse`, `BinaryFileResponse`)
- **`Cookie`**, **`Session`**, **`ServerBag`**

**Параметры разложены по «сумкам» (Bag):**
- **`HeaderBag`** — заголовки
- **`ParameterBag`** — query/post/attributes
- **`FileBag`** — загруженные файлы

**Важно:** Bag-объекты HttpFoundation — **мутабельные**. **Иммутабельность** — это про **PSR-7** (`$request->withHeader(...)`), а не про HttpFoundation.

**Кто использует:** базовый кирпич всей Symfony, а также **Laravel** (его `Illuminate\\Http\\Request` наследует `Symfony\\Component\\HttpFoundation\\Request`).',
                'difficulty' => 3,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работает Autowiring в Symfony?',
                'answer' => '**Autowiring** разрешает аргументы конструктора и методов сервиса **по type-hint**: контейнер находит в реестре сервис, реализующий нужный класс/интерфейс, и подставляет автоматически.

**Что это даёт:**
- больше **не нужно писать `arguments:`** в YAML вручную
- конфигурация остаётся **декларативной**
- IDE и статанализ видят зависимости через типы

**Когда autowiring не справляется:**
- **несколько реализаций** одного интерфейса (`LoggerInterface` для app/audit/security)
- **скалярные параметры** (`string $apiKey`)

**Решения для конфликтов:**
- **именованный alias** — имя параметра в коде совпадает с именем сервиса: `LoggerInterface $applicationLogger`
- атрибут **`#[Target("audit")]`** на параметре — явно выбирает нужный сервис
- атрибут **`#[Autowire(service: "...")]`** или **`#[Autowire(env: "API_KEY")]`** — точечная конфигурация',
                'code_example' => '<?php
class UserService
{
    public function __construct(
        private LoggerInterface $logger,          // подставится автоматически
        private UserRepository $repo,
        #[Target("audit")] private LoggerInterface $audit, // именованный
    ) {}
}

// config/services.yaml — больше не нужно перечислять аргументы
// services:
//     _defaults:
//         autowire: true
//         autoconfigure: true
//     App\\:
//         resource: "../src/"',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что делает Autoconfigure в Symfony?',
                'answer' => '**Autoconfigure** автоматически вешает на сервис **теги контейнера**, исходя из его базового класса или реализованных интерфейсов.

**Типичные автоматические теги:**
| Что реализует класс | Тег |
| --- | --- |
| `EventSubscriberInterface` | `kernel.event_subscriber` |
| `Command` (наследник) или `#[AsCommand]` | `console.command` |
| `MessageHandlerInterface` или `#[AsMessageHandler]` | `messenger.message_handler` |
| `VoterInterface` | `security.voter` |
| `ConstraintValidatorInterface` | `validator.constraint_validator` |

**Что это даёт:**
- пишешь обычный PHP-класс — он сразу попадает в нужный extension point фреймворка
- **не нужны строки `tags:` в YAML** для типового кода
- меньше шанс «забыл зарегистрировать»

**Включается** в `services.yaml` через `_defaults: { autoconfigure: true }` и в стандартном Symfony-скелете уже включён.',
                'code_example' => '<?php
// Класс автоматически получит тег kernel.event_subscriber
class UserSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [UserRegisteredEvent::class => "onRegister"];
    }
}

// Команда автоматически получит тег console.command
#[AsCommand("app:sync")]
class SyncCommand extends Command
{
    protected function execute(InputInterface $i, OutputInterface $o): int
    { return 0; }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Какие типы внедрения зависимостей есть в Symfony и какой предпочтителен?',
                'answer' => '**Три формы DI в Symfony:**

| Способ | Как объявить | Когда применять |
| --- | --- | --- |
| **Constructor injection** | `__construct(LoggerInterface $log)` | **по умолчанию** для обязательных зависимостей |
| **Setter injection** | публичный сеттер + атрибут **`#[Required]`** | опциональные зависимости, разрыв **циклических** ссылок |
| **Property injection** | публичное свойство + `#[Required]` | очень редко, в legacy-сервисах |

**Почему constructor injection — стандарт:**
- зависимости **обязательны и явные** — без них объект не создастся
- объект **всегда в валидном состоянии** после `new`
- легко **протестировать** — все зависимости видны в сигнатуре
- работает с **`readonly`-свойствами** и **property promotion** из PHP 8

**Setter** оставляют для редких случаев: например, сервис **`A`** зависит от **`B`**, а **`B`** — от **`A`** — конструктор не разрулит, спасает сеттер.',
                'difficulty' => 3,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как получить доступ к сессии в современной Symfony (5.3+)?',
                'answer' => '**Устарело (deprecated с 5.3):**
- инъекция сервиса `session` напрямую через тип `SessionInterface`
- вызов `Request::getSession()` вне цикла `RequestStack`

**Современный путь — через `RequestStack`:**
- инъектируйте **`Symfony\\Component\\HttpFoundation\\RequestStack`** в сервис
- вызывайте **`$requestStack->getSession()`**
- либо берите сессию **прямо из объекта Request** в контроллере: `$request->getSession()`

**Почему так:**
- сессия **привязана к конкретному запросу**, а не к процессу
- совместимо с **воркер-SAPI** (RoadRunner, FrankenPHP, Swoole), где **`$_SESSION`** и глобальное состояние нельзя оставлять между запросами
- легче тестировать — `RequestStack` подменяется в тестах',
                'code_example' => '<?php
use Symfony\\Component\\HttpFoundation\\RequestStack;

class CartService
{
    public function __construct(private RequestStack $requestStack) {}

    public function add(string $sku): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get("cart", []);
        $cart[] = $sku;
        $session->set("cart", $cart);
    }
}

// В контроллере — проще
public function index(Request $request): Response
{
    $cart = $request->getSession()->get("cart", []);
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Какие основные события HttpKernel срабатывают при обработке запроса?',
                'answer' => '**`Symfony\\Component\\HttpKernel`** проводит запрос через **последовательность событий**, на каждое можно подписаться через `EventSubscriber`/`EventListener`.

**Полный жизненный цикл (от запроса к ответу):**

| Событие | Когда срабатывает | Зачем подписываться |
| --- | --- | --- |
| **`kernel.request`** | до выбора контроллера | роутинг, фаервол, CORS, локаль, тенант-резолвер |
| **`kernel.controller`** | контроллер найден, до вызова | подмена контроллера, профайлер |
| **`kernel.controller_arguments`** | подготовлены аргументы для контроллера | ArgumentResolver, авторизация |
| _(вызов контроллера)_ | — | — |
| **`kernel.view`** | контроллер вернул **НЕ `Response`** (например, массив) | автомаппинг array → JsonResponse, сериализация |
| **`kernel.response`** | финальный `Response` готов, до отправки | заголовки CSP/Cache-Control, замер времени |
| **`kernel.finish_request`** | запрос обработан, перед TerminableInterface | очистка request-scoped state в воркерах |
| _(`Response::send()`)_ | байты ушли клиенту | — |
| **`kernel.terminate`** | **после** отправки ответа | долгие действия: отправить аналитику, прогреть кеш |
| **`kernel.exception`** | в любой момент брошено исключение | конвертация в ErrorResponse, логирование |

**Приоритеты:**

- у каждого подписчика есть **`priority`** (число) — выше = раньше
- встроенные слушатели Symfony:
  - **`RouterListener`** (priority 32) — на `kernel.request`, выбирает маршрут
  - **`FirewallListener`** (priority 8) — на `kernel.request`, проверяет аутентификацию
  - **`ProfilerListener`** — на `kernel.response`, добавляет debug toolbar

**Структура listener-методов:**

```php
public function onKernelRequest(RequestEvent \$event): void
{
    if (! \$event->isMainRequest()) {
        return; // sub-requests пропускаем
    }
    \$request = \$event->getRequest();
    // ...
    if (\$shouldShortCircuit) {
        \$event->setResponse(new Response("blocked", 403));
        // дальнейшие слушатели kernel.request не вызовутся (StopPropagation)
    }
}
```

**Подводные камни:**

- **`kernel.terminate`** работает в FPM только если SAPI поддерживает `fastcgi_finish_request` — в CLI/Octane всё в одном процессе, после `send()` блокирующая работа задерживает следующий запрос
- **sub-requests** (через `HttpKernelInterface::SUB_REQUEST`) тоже проходят весь цикл — фильтруйте через `isMainRequest()`
- порядок событий внутри одного `kernel.request` определяется **только** `priority` — не предполагайте, что бандлы зарегистрировали слушатели в нужном порядке',
                'difficulty' => 4,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем EventSubscriber отличается от EventListener в Symfony?',
                'answer' => '**Параллельное сравнение:**

| | EventListener | EventSubscriber |
| --- | --- | --- |
| **Что слушает** | задано в YAML (`tags:`) | задано в **`getSubscribedEvents()`** |
| **Интерфейс** | любой класс | реализует **`EventSubscriberInterface`** |
| **Где хранится конфиг** | в `services.yaml` отдельно от класса | **внутри класса** |
| **Авторегистрация** | нужно прописать тег вручную | через **autoconfigure** автоматически |
| **Перенос между проектами** | надо тащить ещё и конфиг | один класс — и готово |

**Когда что брать:**
- **EventSubscriber** — стандарт; класс самодостаточен, всё видно в одном месте
- **EventListener** — когда логика должна включаться/выключаться через конфиг **без правки кода** (например, для бандла, чьи слушатели зависят от опций пользователя)',
                'code_example' => '<?php
// Subscriber — самодостаточный
class AuthSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => ["onLogin", 10],
            LogoutEvent::class       => "onLogout",
        ];
    }

    public function onLogin(LoginSuccessEvent $e): void {}
    public function onLogout(LogoutEvent $e): void {}
}

// Listener — описание событий вынесено в config
# services.yaml:
# App\\EventListener\\AuthListener:
#     tags:
#         - { name: kernel.event_listener, event: kernel.request, priority: 10 }',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое CompilerPass и зачем он нужен?',
                'answer' => '**`CompilerPassInterface`** — хук в процессе **компиляции контейнера**, выполняющийся **до** того, как контейнер закэшируется в PHP-класс `App_KernelDevDebugContainer.php`.

**Почему это важная концепция:**

- Symfony **компилирует** DI-контейнер: после `composer dump-autoload` и `cache:warmup` все определения сервисов превращаются в **один сгенерированный PHP-класс** с массивом фабрик
- runtime-разрешение зависимостей становится **почти бесплатным** — это просто `new` в готовом коде
- **CompilerPass** даёт возможность **до этой заморозки** перебрать определения и что-то изменить: добавить аргумент, переопределить класс, собрать теги

**Классический use-case — Tagged Services pattern:**

```php
final class RegisterTransportsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder \$container): void
    {
        if (! \$container->hasDefinition(TransportRegistry::class)) {
            return;
        }
        \$registry = \$container->findDefinition(TransportRegistry::class);
        foreach (\$container->findTaggedServiceIds("messenger.transport") as \$id => \$tags) {
            \$registry->addMethodCall("add", [new Reference(\$id), \$tags[0]["name"] ?? \$id]);
        }
    }
}
```

**Типичные сценарии для CompilerPass:**

| Сценарий | Что делает |
| --- | --- |
| **Сбор tagged services** | `findTaggedServiceIds()` + добавление в реестр через `addMethodCall` |
| **Подмена класса сервиса** | `setDefinition()` для замены реализации |
| **Условная регистрация** | если расширение установлено — зарегистрировать, иначе — нет |
| **Валидация конфига** | проверить, что обязательные сервисы есть, иначе `throw RuntimeException` |
| **Динамическое декорирование** | обернуть сервис в Proxy/Decorator программно |

**Регистрация:**

```php
// В Bundle (предпочтительно) или Kernel
public function build(ContainerBuilder \$container): void
{
    parent::build(\$container);
    \$container->addCompilerPass(new RegisterTransportsPass());
}
```

**Этапы компиляции (`PassConfig::TYPE_*`):**

| Этап | Когда срабатывает | Зачем |
| --- | --- | --- |
| `TYPE_BEFORE_OPTIMIZATION` (default) | до оптимизаций | основное место для своих pass-ов |
| `TYPE_OPTIMIZE` | оптимизация графа сервисов | внутреннее, не трогать |
| `TYPE_BEFORE_REMOVING` | до удаления неиспользуемых | если делаете setMethodCall на «private» сервисах |
| `TYPE_REMOVE` | удаление private/unused | внутреннее |
| `TYPE_AFTER_REMOVING` | финальные правки | последний шанс |

**Подводные камни:**

- CompilerPass запускается **только при пересборке** контейнера — в проде после `cache:clear` или при изменении конфига
- **не использует runtime-данные** — на этапе компиляции нет request, БД, env-переменных (можно `\$container->resolveEnvPlaceholders` для env, но осторожно)
- ошибки в pass-е валят `cache:warmup` — отлаживайте локально
- сложные сценарии (динамическая зависимость от runtime) делаются **через runtime DI** (`ServiceLocator`, `TaggedIteratorArgument`), не через pass',
                'difficulty' => 4,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как внедрить два разных экземпляра одного класса в Symfony?',
                'answer' => '**Корневая проблема:** autowiring подбирает сервис **по type-hint**. Если в контейнере **две реализации** одного интерфейса (`LoggerInterface` для app/audit, `CacheInterface` для users/posts), сам тип не различает их — нужен **второй сигнал** контейнеру.

**Четыре механизма для разрешения:**

**1. Named autowiring (имя параметра ≡ имя сервиса)**

```yaml
# services.yaml
services:
    Psr\\Log\\LoggerInterface \$applicationLogger:
        alias: monolog.logger.app

    Psr\\Log\\LoggerInterface \$auditLogger:
        alias: monolog.logger.audit
```

```php
class UserService {
    public function __construct(
        private LoggerInterface \$applicationLogger,  // → monolog.logger.app
        private LoggerInterface \$auditLogger,        // → monolog.logger.audit
    ) {}
}
```

**2. Атрибут `#[Target]` (Symfony 5.4+, рекомендованный путь):**

```php
use Symfony\\Component\\DependencyInjection\\Attribute\\Target;

class UserService {
    public function __construct(
        #[Target("application")] private LoggerInterface \$logger,
        #[Target("audit")] private LoggerInterface \$audit,
    ) {}
}
```

Связь сервиса с целью — через alias `LoggerInterface \\$auditLogger` (camelCase → kebab внутри атрибута). Symfony **выводит** target из суффикса алиаса.

**3. Атрибут `#[Autowire]` (Symfony 6.1+) — самый явный:**

```php
use Symfony\\Component\\DependencyInjection\\Attribute\\Autowire;

class UserService {
    public function __construct(
        #[Autowire(service: "monolog.logger.audit")]
        private LoggerInterface \$audit,

        #[Autowire(env: "API_KEY")]
        private string \$apiKey,

        #[Autowire(param: "kernel.project_dir")]
        private string \$projectDir,
    ) {}
}
```

Преимущество: всё в одном месте (классе), не нужны YAML-алиасы.

**4. `#[TaggedIterator]` — все сервисы с тегом сразу:**

```php
class PaymentService {
    public function __construct(
        #[TaggedIterator("app.payment_gateway")]
        private iterable \$gateways,  // все сервисы с тегом
    ) {}
}
```

**Сравнение четырёх способов:**

| Способ | Когда выбирать |
| --- | --- |
| Named autowiring | классика, унаследованный код |
| `#[Target]` | две-три реализации одного интерфейса |
| `#[Autowire]` | разовая «привяжи к конкретному сервису» |
| `#[TaggedIterator]` | плагины, стратегии, voter-ы |

**Подводный камень:** **autowiring требует точного типа**. Если переименовали интерфейс, оба способа выше упадут. Best practice: писать **интеграционный тест на контейнер** (`\$this->getContainer()->get(UserService::class)`), чтобы CI ловил ошибки compilation.',
                'difficulty' => 4,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Зачем нужен Doctrine ORM в проектах на Symfony?',
                'answer' => '**Doctrine ORM** — реализация паттерна **Data Mapper** для PHP, интегрированная в Symfony через **`DoctrineBundle`**.

**Чем отличается от Active Record (Eloquent в Laravel):**
- **сущности — POPO-объекты** без знания о БД (нет `$user->save()`, нет наследования от `Model`)
- **отображение** на таблицы задаётся **PHP 8-атрибутами** `#[ORM\\Entity]`, `#[ORM\\Column]`, XML или YAML
- сущности можно тестировать **без подключения к БД**

**Ключевые объекты:**
- **`EntityManager`** — главный сервис: `persist()`, `remove()`, `flush()`, `find()`
- **`UnitOfWork`** — внутренний механизм, **накапливает изменения** и одной транзакцией сбрасывает в БД при `flush()`
- **`EntityRepository`** — методы поиска (`find`, `findBy`, кастомные DQL-запросы)

**DQL** — SQL-подобный язык запросов **по сущностям**, а не по таблицам: `SELECT u FROM App\\Entity\\User u WHERE u.active = true`.',
                'difficulty' => 3,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое Doctrine Migrations и зачем они нужны?',
                'answer' => '**Doctrine Migrations** — отдельный пакет, который **версионирует схему БД** через PHP-классы с методами `up()` (накатить) и `down()` (откатить).

**Жизненный цикл:**
1. **`bin/console doctrine:migrations:diff`** — сравнивает текущие сущности с реальной схемой и **генерирует новую миграцию** с DDL
2. файл `migrations/VersionYYYYMMDDHHMMSS.php` коммитится в **git**
3. **`bin/console doctrine:migrations:migrate`** на проде применяет невыполненные миграции
4. таблица **`doctrine_migration_versions`** хранит, какие миграции уже накатаны

**Что это даёт:**
- эволюция схемы **в git** рядом с кодом
- **согласованный** накат на dev/staging/prod
- возможность **откатиться** через `migrate prev`

**Другие полезные команды:**
- `migrations:status` — посмотреть, что применено
- `migrations:generate` — пустой шаблон без diff (для data-миграций)
- `migrations:execute --up VersionXxx` — точечное применение',
                'difficulty' => 3,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как создать собственную console-команду в Symfony?',
                'answer' => '**Шаги:**
1. Унаследоваться от **`Symfony\\Component\\Console\\Command\\Command`**
2. Навесить атрибут **`#[AsCommand("app:имя", "описание")]`** — он же задаст имя
3. Реализовать метод **`execute(InputInterface $input, OutputInterface $output): int`**
4. Объявить параметры/опции в **`configure()`** (или прямо в атрибуте `#[AsCommand]`)
5. Вернуть **`Command::SUCCESS`** (0), `FAILURE` (1) или `INVALID` (2)

**Как Symfony её подхватит:**
- благодаря **autoconfigure** класс автоматически получает тег **`console.command`**
- регистрируется в `Application` и становится доступной через **`bin/console app:имя`**

**Зависимости** внедряются как обычно — через конструктор (DI работает в командах).',
                'code_example' => '<?php
use Symfony\\Component\\Console\\Attribute\\AsCommand;
use Symfony\\Component\\Console\\Command\\Command;
use Symfony\\Component\\Console\\Input\\InputInterface;
use Symfony\\Component\\Console\\Input\\InputArgument;
use Symfony\\Component\\Console\\Output\\OutputInterface;
use Symfony\\Component\\Console\\Style\\SymfonyStyle;

#[AsCommand("app:sync-users", "Синхронизирует пользователей с CRM")]
class SyncUsersCommand extends Command
{
    public function __construct(private UserSyncService $sync)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument("source", InputArgument::REQUIRED, "Источник: csv|api");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $count = $this->sync->run($input->getArgument("source"));
        $io->success("Синхронизировано: $count");
        return Command::SUCCESS;
    }
}

// bin/console app:sync-users api',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как описывать роутинг через атрибуты в современной Symfony?',
                'answer' => '**Современный путь (Symfony 6+):** атрибут **`#[Route]`** из **`Symfony\\Component\\Routing\\Attribute\\Route`** прямо над методом или классом контроллера.

**На классе** атрибут задаёт **префикс пути** и общие требования для всех action-методов внутри.

**На методе** задаются:
- **`path`** — путь
- **`name`** — имя маршрута (для генерации URL)
- **`methods`** — HTTP-методы (`GET`, `POST`, …)
- **`requirements`** — regex-ограничения параметров (`["id" => "\\d+"]`)
- **`condition`** — динамическое условие (по заголовку, IP)

**Историческая справка:**
- старые **аннотации** `@Route` через `doctrine/annotations` помечены deprecated в **Symfony 6.4** и удалены в **7.0**
- сам пакет `doctrine/annotations` остаётся (используется в Doctrine ORM), но **для роутинга фреймворком не используется**',
                'code_example' => '<?php
use Symfony\\Component\\Routing\\Attribute\\Route;

#[Route("/api/users")]                  // префикс на классе
class UserController
{
    #[Route("", methods: ["GET"], name: "users_list")]
    public function index(): JsonResponse {}

    #[Route("/{id}", methods: ["GET"], requirements: ["id" => "\\\\d+"])]
    public function show(int $id): JsonResponse {}
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Зачем нужен компонент Form в Symfony?',
                'answer' => '**Компонент `Form`** закрывает **три задачи** в одной декларации:

- **Маппинг** данных HTTP-запроса на объект (entity или DTO) — без ручного `$user->setName($_POST["name"])`
- **Валидация** через интеграцию с компонентом `Validator`
- **Встроенная CSRF-защита** — токен добавляется и проверяется автоматически

**Как описывается:**
- класс-наследник **`AbstractType`** с методом **`buildForm(FormBuilderInterface $builder, array $options)`**
- метод **`configureOptions(OptionsResolver $resolver)`** задаёт **`data_class`** — на какой объект мапить
- рендеринг — через Twig-функции **`{{ form_start(form) }}`**, **`{{ form_row(form.email) }}`**

**Поток в контроллере:**
1. `$form = $this->createForm(UserType::class, $user)`
2. `$form->handleRequest($request)` — заполнить из POST
3. `if ($form->isSubmitted() && $form->isValid())` — на выходе **типизированный и валидный** объект',
                'difficulty' => 3,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как устроена валидация в Symfony и как она связана с формами?',
                'answer' => '**Валидация** делается компонентом **`Validator`**, ограничения вешаются прямо на свойства/методы сущности **PHP 8-атрибутами** (или YAML/XML/PHP-конфигом).

**Типичные ограничения:**
- **`#[Assert\\NotBlank]`** — не пустое
- **`#[Assert\\Email]`** — валидный email
- **`#[Assert\\Length(min: 3, max: 100)]`**
- **`#[Assert\\Range(min: 0)]`**
- **`#[Assert\\Choice(["new", "active", "archived"])]`**
- **`#[Assert\\Callback]`** — кастомная функция-валидатор

**Связь с формами:**
1. форма знает, какой объект ей соответствует (через `data_class`)
2. при **`$form->handleRequest($request)`** Symfony **автоматически вызывает Validator** для этого объекта
3. нарушения превращаются в **`FormError`** и привязываются к нужному полю
4. в Twig они отрисовываются через `{{ form_errors(form.email) }}`

**Главное архитектурное решение:** правила **живут на сущности** (одно место правды), а UI **живёт в `FormType`** — их можно менять независимо.',
                'difficulty' => 3,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое Twig и почему он используется в Symfony?',
                'answer' => '**Twig** — шаблонизатор от создателей Symfony, заточенный под **три вещи**:

- **Безопасность** — **автоэкранирование по умолчанию** (защита от XSS); `{{ var }}` экранирует, для сырого HTML нужен `{{ var|raw }}` явно
- **Наследование шаблонов** — `{% extends "base.html.twig" %}` и **блоки** `{% block content %}` для гибкой композиции
- **Удобный синтаксис** — фильтры через **пайп** (`{{ name|upper|trim }}`), функции, циклы, условия

**Производительность:** шаблоны **компилируются в PHP-классы** и кешируются — накладные расходы минимальны.

**В Symfony** интегрирован через **`TwigBundle`** и используется как **стандартный движок представлений**. Аналог в Laravel — Blade, но Twig старше и стал отдельным проектом, используемым в Drupal, Slim, eZ Platform.',
                'difficulty' => 2,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как устроен компонент Security в Symfony?',
                'answer' => '**`SecurityBundle`** разделяет аутентификацию (**кто ты?**) и авторизацию (**что тебе можно?**) на отдельные подсистемы.

**Аутентификация (AuthN):**

| Компонент | Роль |
| --- | --- |
| **Firewall** | блок конфига, описывающий, **какие маршруты** защищены и **как** аутентифицировать (`config/packages/security.yaml`) |
| **Authenticator** | класс, **извлекающий** учётные данные из запроса (form login, JWT в header, API key, OAuth) |
| **Passport** | объект-контейнер «удостоверение» — содержит `UserBadge`, `CredentialsBadge`, доп. бейджи (`CsrfTokenBadge`, `RememberMeBadge`) |
| **UserProvider** | загружает `UserInterface` из БД/LDAP/API по identifier |
| **TokenStorage** | хранит **`TokenInterface`** аутентифицированного пользователя в текущем запросе |
| **PasswordHasher** | хеширует/проверяет пароль (argon2id, bcrypt) |

**Поток AuthN:**

1. Запрос приходит на путь firewall
2. **Authenticator** проверяет, его ли запрос (`supports()`) — например, есть ли `Authorization: Bearer ...`
3. Authenticator делает `authenticate()` → возвращает `Passport`
4. Symfony вызывает `UserProvider->loadUserByIdentifier()` → получает `UserInterface`
5. Проверяются **бейджи** (`PasswordCredentials::check`, `CsrfTokenBadge::validate`)
6. Создаётся `Token` и кладётся в `TokenStorage`

**Авторизация (AuthZ):**

| Компонент | Роль |
| --- | --- |
| **AccessDecisionManager** | главный сервис, принимает решение «можно/нельзя» |
| **Voter** | реализация `VoterInterface` — голосует за/против/воздерживается по конкретному `attribute` (`ROLE_ADMIN`, `EDIT`, `VIEW`) |
| **AccessDecisionStrategy** | как агрегировать голоса: `affirmative` (хоть один за), `consensus`, `unanimous`, `priority` (Symfony 5.4+) |

**Способы запросить проверку:**

```yaml
# 1. Декларативно в access_control (security.yaml)
access_control:
    - { path: ^/admin, roles: ROLE_ADMIN }
```

```php
// 2. Атрибут на контроллере (Symfony 6.2+)
#[IsGranted("ROLE_ADMIN")]
public function admin(): Response { ... }

#[IsGranted("EDIT", subject: "post")]
public function edit(Post \$post): Response { ... }

// 3. В коде контроллера
\$this->denyAccessUnlessGranted("EDIT", \$post);

// 4. Из сервиса
\$this->security->isGranted("EDIT", \$post);
```

**Voter — кастомные правила:**

```php
class PostVoter extends Voter {
    protected function supports(string \$attribute, mixed \$subject): bool {
        return in_array(\$attribute, ["VIEW", "EDIT", "DELETE"])
            && \$subject instanceof Post;
    }
    protected function voteOnAttribute(string \$attr, mixed \$post, TokenInterface \$t): bool {
        \$user = \$t->getUser();
        return match (\$attr) {
            "VIEW" => true,
            "EDIT", "DELETE" => \$post->getAuthor() === \$user,
        };
    }
}
```

**Подводные камни:**

- **`access_control`** проверяется **раньше** атрибута `#[IsGranted]` — нужно согласовывать
- **`UserProvider::refreshUser()`** вызывается на каждый запрос — медленный provider тормозит весь firewall, кешируйте
- **Stateless firewall** (для API) не использует сессии — без неё нет `TokenStorage` между запросами
- **`role_hierarchy`** позволяет `ROLE_ADMIN → ROLE_USER`, чтобы не дублировать
- Для **тестов** используется `loginUser()` на тестовом `KernelBrowser`',
                'difficulty' => 4,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое Symfony Messenger и как он разделяет sync и async?',
                'answer' => '**Symfony Messenger** — компонент для отправки и обработки **сообщений** (DTO-объекты) через **шины** (`MessageBusInterface`) и **транспорты**.

**Транспорты:**
- **`sync`** — синхронное выполнение в том же процессе (по умолчанию для dev)
- **`async`** через Redis, **RabbitMQ (AMQP)**, Doctrine, Amazon SQS, Beanstalkd
- **`in-memory`** — для тестов
- **`failed`** — отдельная очередь для упавших сообщений

**Главная фишка — маршрутизация:**
- одна и та же команда `$bus->dispatch(new SendEmailMessage(...))` в коде
- в **`config/packages/messenger.yaml`** прописано `routing: { App\\Message\\SendEmailMessage: async }`
- в **dev** транспорт `async = sync` — обработчик вызывается сразу
- в **prod** транспорт `async = redis://...` — сообщение уходит в очередь, обрабатывается отдельным воркером **`bin/console messenger:consume async`**

**Что это даёт:** код пишется один раз, а **режим выполнения меняется конфигом**. Идеально для отложенных писем, экспортов, интеграций.',
                'difficulty' => 3,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как устроена цепочка middleware в Symfony Messenger?',
                'answer' => '**Архитектурно Messenger — это шина (`MessageBus`), которая прогоняет каждое сообщение через стек middleware**, аналогично HTTP middleware в PSR-15. Сообщение оборачивается в **`Envelope`**, который собирает по пути **`StampInterface`**-марки (метаданные).

**`MessageBusInterface::dispatch(\$message)` → \$envelope → middleware-стек → \$envelope с результатом.**

**Стандартный набор middleware (порядок важен):**

| Middleware | Что делает |
| --- | --- |
| **`AddBusNameStampMiddleware`** | помечает envelope именем шины |
| **`DispatchAfterCurrentBusMiddleware`** | откладывает дочерние dispatch до завершения текущего (для атомарности) |
| **`FailedMessageProcessingMiddleware`** | специальная логика для повторных попыток из `failed`-транспорта |
| **`SendMessageMiddleware`** | смотрит в `routing:` конфиг — если транспорт `async`, шлёт в очередь и **обрывает цепочку**; если `sync` — пропускает дальше |
| **`HandleMessageMiddleware`** | находит handler через `#[AsMessageHandler]`, вызывает, кладёт `HandledStamp` |
| **`ValidationMiddleware`** | если включен — валидирует сообщение через компонент Validator |
| **`DoctrineTransactionMiddleware`** | оборачивает обработку в Doctrine-транзакцию |
| **`DoctrinePingConnectionMiddleware`** | пингует БД перед обработкой (для долгоживущих воркеров) |
| **`DoctrineCloseConnectionMiddleware`** | закрывает соединение после обработки |

**`Envelope` и `Stamp` — ключевые типы:**

```php
\$envelope = new Envelope(new SendEmailMessage(\$to, \$body), [
    new DelayStamp(60_000),                    // задержать на 60 сек
    new TransportNamesStamp(["async_high"]),   // конкретный транспорт
    new AmqpStamp(routing_key: "high"),        // RabbitMQ-specific
]);
\$bus->dispatch(\$envelope);
```

**Кастомный middleware:**

```php
final class LoggingMiddleware implements MiddlewareInterface
{
    public function __construct(private LoggerInterface \$log) {}

    public function handle(Envelope \$envelope, StackInterface \$stack): Envelope
    {
        \$message = \$envelope->getMessage();
        \$this->log->info("dispatch", ["class" => \$message::class]);

        try {
            \$envelope = \$stack->next()->handle(\$envelope, \$stack);
        } catch (\\Throwable \$e) {
            \$this->log->error("failed", ["error" => \$e->getMessage()]);
            throw \$e;
        }
        return \$envelope;
    }
}
```

**Регистрация в `messenger.yaml`:**

```yaml
framework:
    messenger:
        buses:
            messenger.bus.default:
                middleware:
                    - validation
                    - doctrine_transaction
                    - App\\Messenger\\Middleware\\LoggingMiddleware
```

**Типичные кастомные middleware:**

| Назначение | Где обычно ставится |
| --- | --- |
| **Логирование** | сразу после `AddBusNameStampMiddleware` |
| **Метрики** (StatsD/Prometheus) | то же — до Send |
| **Tenant context** | в самом начале, читает `TenantIdStamp` |
| **Idempotency** (проверка дубля по UUID) | перед `HandleMessageMiddleware` |
| **Retry policy** override | вместо стандартного retry-listener |
| **Outbox pattern** интеграция | вместо `SendMessageMiddleware` |

**Подводные камни:**

- **порядок** middleware определяется конфигом — встроенные ставятся **первыми**, потом ваши
- `SendMessageMiddleware` **обрывает** цепочку для async — последующие middleware **не сработают** при отправке, **только** на consumer-стороне
- **`DoctrineTransactionMiddleware`** оборачивает целиком — если handler шлёт **другое** сообщение в той же шине, оно тоже попадёт в транзакцию (`DispatchAfterCurrentBusMiddleware` это решает)',
                'difficulty' => 4,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как Symfony работает с RoadRunner и FrankenPHP в режиме воркера?',
                'answer' => '**`symfony/runtime`** (с Symfony **5.3**) — компонент, отделяющий **точку входа приложения** от **среды исполнения**.

**Как было до Runtime:**

- `public/index.php` для веба + `bin/console` для CLI — **разные** точки входа
- встроенный `Symfony\\Component\\HttpKernel\\Kernel` тесно связан с FPM
- любая поддержка воркеров (Swoole, RoadRunner) требовала **переписать** entry point

**С Runtime:**

```php
// public/index.php — единый код для всех окружений
use App\\Kernel;

require_once dirname(__DIR__) . "/vendor/autoload_runtime.php";

return function (array \$context) {
    return new Kernel(\$context["APP_ENV"], (bool) \$context["APP_DEBUG"]);
};
```

Файл **`autoload_runtime.php`** генерируется Composer-плагином и **выбирает runtime** по переменной **`APP_RUNTIME`** или `extra.runtime.class` в `composer.json`.

**Доступные runtime:**

| Runtime | Назначение |
| --- | --- |
| **`Symfony\\Component\\Runtime\\SymfonyRuntime`** (default) | классический PHP-FPM + CLI |
| **`Runtime\\RoadRunnerSymfonyNyholm\\Runtime`** | RoadRunner с PSR-7/nyholm |
| **`Runtime\\FrankenPhpSymfony\\Runtime`** | FrankenPHP с worker-mode |
| **`Runtime\\Bref\\Runtime`** | AWS Lambda через Bref |
| **`Runtime\\Swoole\\Runtime`** | Swoole |
| **`Runtime\\ReactPhp\\Runtime`** | ReactPHP |

**Что делает worker-runtime:**

1. **Один раз** при старте воркера создаёт `Kernel`, прогружает контейнер, autoload
2. В цикле принимает request от Go-сервера (RoadRunner) или Caddy (FrankenPHP)
3. Конвертирует **PSR-7 request → Symfony Request**, передаёт в Kernel
4. Получает Symfony Response → конвертирует **обратно в PSR-7**
5. Отправляет клиенту
6. **Сбрасывает request-scoped state**, переходит к следующему запросу

**Выигрыш в производительности:**

- **в 3-10× быстрее** FPM на типичном веб-приложении: bootstrap не повторяется
- меньше CPU, меньше RAM на запрос
- но: **больше осторожности** с памятью (запрос не «убивает» процесс)

**На что обращать внимание:**

| Проблема | Решение |
| --- | --- |
| **Глобальное состояние** в сервисах | используйте `#[AsScopedService]` (Symfony 6.4+) или сбрасывайте в `kernel.reset` |
| **Сессии** через `$_SESSION` | только через `RequestStack`, см. отдельную карточку |
| **Doctrine EntityManager** держит references | вызывайте `clear()` между запросами или используйте `ResettableInterface` |
| **`Carbon::setTestNow`** или **`Mockery`** | сбрасывайте в `kernel.terminate` |
| **Утечки памяти** | `RR_HTTP_NUM_WORKERS` + перезапуск по `RR_HTTP_MAX_JOBS=1000` |
| **`exit`/`die`** в коде | прибьёт **весь воркер** — никогда не использовать |

**FrankenPHP worker-mode** (с **PHP 8.2+**) интересен тем, что **встраивает PHP внутрь Caddy** через cgo/FFI — без отдельного RoadRunner-сервера.

**Альтернатива — Octane-стиль** в Laravel-мире (`laravel/octane` поддерживает Swoole, RoadRunner, FrankenPHP с похожей моделью).',
                'difficulty' => 4,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как избежать циклических ссылок при сериализации сущностей в Symfony?',
                'answer' => '**Корневая проблема:** Doctrine-сущности часто связаны **bi-directional** (`Post→Author + Author→posts`), и при сериализации в JSON компонент Serializer уходит в **бесконечную рекурсию** или валит **`CircularReferenceException`**.

**Три рабочих подхода (от менее к более строгому):**

**1. Serialization Groups (`#[Groups]`)**

```php
class Post {
    #[Groups(["post:read", "post:list"])]
    public int \$id;

    #[Groups(["post:read"])]
    public string \$body;

    #[Groups(["post:read"])]
    #[MaxDepth(1)]
    public Author \$author;
}

class Author {
    #[Groups(["post:read", "author:read"])]
    public string \$name;

    // НЕ в группе post:read — не попадёт в JSON при сериализации Post
    #[Groups(["author:read"])]
    public Collection \$posts;
}
```

Сериализуем с контекстом:

```php
\$json = \$serializer->serialize(\$post, "json", [
    "groups" => ["post:read"],
]);
// posts автора не попадут — цикл разорван по группе
```

**2. DTO-маппинг (самый строгий, рекомендуется для API)**

```php
final readonly class PostDto {
    public function __construct(
        public int \$id,
        public string \$body,
        public AuthorBriefDto \$author,  // только нужные поля
    ) {}

    public static function fromEntity(Post \$p): self {
        return new self(
            \$p->getId(),
            \$p->getBody(),
            AuthorBriefDto::fromEntity(\$p->getAuthor()),
        );
    }
}

final readonly class AuthorBriefDto {
    public function __construct(
        public int \$id,
        public string \$name,
        // posts НЕТ — структурно отсутствует
    ) {}
}
```

**3. `#[MaxDepth]` + `ENABLE_MAX_DEPTH`**

```php
class Post {
    #[MaxDepth(2)]
    public Author \$author;
}

\$json = \$serializer->serialize(\$post, "json", [
    AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
]);
```

После заданной глубины Serializer **обрезает** граф.

**Сравнение трёх подходов:**

| Подход | Гибкость | Контроль API | Сложность |
| --- | --- | --- | --- |
| **Groups** | средняя — переключение через context | плохой (поля рассыпаны по сущностям) | низкая |
| **DTO** | максимальная — структура отдельная | **отличный** (DTO == контракт API) | средняя (нужны мапперы или ObjectMapper) |
| **MaxDepth** | глобальный лимит | плохой (произвольная обрезка) | минимальная |

**Дополнительные приёмы:**

- **`CIRCULAR_REFERENCE_HANDLER`**: callback в контексте, который возвращает `\$obj->id` вместо повтора объекта
- **`@ApiResource`** в **API Platform** имеет встроенные нормализационные группы
- для **Doctrine lazy-loading**: проверяйте, что Proxy не цепляет неинициализированную коллекцию (`\$em->initialize(\$proxy)` или EAGER loading через DQL)

**Best practice senior-уровня:** **никогда не сериализуйте сущности напрямую в API**. DTO — единый источник правды для контракта; сущность — модель домена. Это разные концерны.',
                'difficulty' => 4,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как оптимизировать Symfony-приложение под высокую нагрузку?',
                'answer' => '**Подход — оптимизация по уровням, от runtime к коду.**

**1. Runtime PHP**

| Настройка | Зачем |
| --- | --- |
| **`opcache.enable=1`** + `opcache.memory_consumption=256` | байткод-кеш — обязателен в проде |
| `opcache.preload` | предзагрузка фреймворка в shared memory (Symfony хорошо работает с preload) |
| `opcache.jit=tracing` + `jit_buffer_size=256M` | если бенчмарк показал выигрыш |
| `realpath_cache_size=4096K` | ускорение `require`/`include` |
| `composer dump-autoload --classmap-authoritative --no-dev` | classmap вместо PSR-4-сканирования |

**2. Symfony контейнер и cache**

| Действие | Эффект |
| --- | --- |
| **`bin/console cache:warmup`** на деплое | предкомпилировать контейнер, маршруты, валидатор |
| **`APP_ENV=prod`** + `APP_DEBUG=0` | выключение dev-инструментов |
| **`framework.router.utf8=true`** + кеш роутов | в prod уже включён, проверьте |
| Compiler passes для дорогих графов сервисов | предсобирать вместо рантайма |

**3. Воркер-режим вместо FPM**

Переход на **RoadRunner / FrankenPHP / Octane-стиль** даёт **3-10× RPS** на типичном веб-приложении: Kernel не пересоздаётся, нет cold-start.

**4. Doctrine ORM**

| Проблема | Решение |
| --- | --- |
| **N+1 запросы** | EAGER fetch в DQL, `partial` selects, `fetchJoin` |
| Тяжёлая гидрация сущностей | **DTO-projections** через DQL `SELECT NEW App\\Dto\\...(...)` |
| Большие выборки | `Pagerfanta` + cursor-based pagination |
| Холодный старт метаданных | `doctrine.orm.metadata_cache_driver: php_array` (preloadable) |
| Повторные запросы | `query_cache` + `result_cache` (Redis) |
| Долгие транзакции в воркере | `EntityManager::clear()` периодически |

**5. Кеширование**

| Уровень | Технология |
| --- | --- |
| **HTTP-кеш** (Symfony) | ESI, `Cache-Control`, `Vary`, `Surrogate-Control` |
| **Reverse proxy** | Varnish, **Symfony HttpCache** (встроенный) |
| **Application cache** | `cache.adapter.redis`, `cache.adapter.apcu` для in-memory |
| **CDN** | статика и **edge-кеш JSON** для `GET /api/...` |

**6. Асинхронность через Messenger**

Любая длительная задача (письма, отчёты, сторонние API) → **`async`-транспорт** + воркер:

```
\$bus->dispatch(new SendNotificationMessage(\$userId));
// HTTP-запрос завершается мгновенно, обработка в background
```

**7. БД и инфра**

| Что | Зачем |
| --- | --- |
| **PgBouncer / ProxySQL** | пул соединений вместо новых на каждый запрос |
| **Read replicas** | `doctrine.orm.connections` с разными `slaves`/`master` |
| **Индексы** под реальные запросы | `EXPLAIN ANALYZE` для каждого slow-query |
| **Партиционирование** больших таблиц | по дате, тенанту |
| Redis для **session/cache/queue** | вместо файлов |

**8. Профилирование как процесс**

| Инструмент | Когда |
| --- | --- |
| **Blackfire** | прод-выборочное профилирование + регрессионные тесты в CI |
| **Symfony Profiler** | dev-окружение, **never** в проде (`APP_ENV=prod`) |
| **APM** (NewRelic/Datadog/Tideways) | непрерывный мониторинг |
| **`stopwatch`** + Symfony Stopwatch | замер участков кода |

**Главное правило:** оптимизировать **по бенчмаркам**, а не по интуиции. На разных профилях нагрузки разные узкие места — где-то JIT даст +30%, а где-то 0%, где-то Redis-cache решит всё.',
                'difficulty' => 4,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем full-stack Symfony отличается от использования его как микрофреймворка?',
                'answer' => '**Сравнение двух старт-наборов:**

| | Full-stack (`symfony/webapp`) | Микрофреймворк (`symfony/skeleton`) |
| --- | --- | --- |
| **Сразу включено** | Twig, Doctrine, Security, Forms, Validator, Mailer, Messenger | только Kernel + Routing + Runtime |
| **Размер vendor** | большой | минимальный |
| **Когда выбирать** | классическое веб-приложение с UI и формами | **API-сервисы**, микросервисы, CLI-утилиты |
| **Подключение компонентов** | уже стоят | по мере необходимости через `composer require` |

**Что общего:**
- **ядро одинаковое** — `HttpKernel`, `DependencyInjection`, `Routing`, `EventDispatcher`
- работа с **Flex-рецептами** одинаковая
- можно **превратить микрофреймворк в фуллстек**, просто доустановив `composer require webapp`

**Вывод:** разница только в **стартовом наборе зависимостей**, а не в архитектуре фреймворка.',
                'difficulty' => 3,
                'topic' => 'php.symfony',
            ],
        ];
    }
}
