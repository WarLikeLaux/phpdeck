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
                'answer' => 'Жизненный цикл проходит через kernel.request (до выбора контроллера, тут работает роутинг и фаервол), kernel.controller (контроллер найден), kernel.controller_arguments (готовятся аргументы), kernel.view (если контроллер вернул не Response), kernel.response (финальные модификации ответа), kernel.finish_request и kernel.terminate (после отправки ответа, для долгих фоновых действий) и kernel.exception (при выбросе исключения). Подписавшись на нужное событие, можно вмешаться почти в любую точку обработки запроса.',
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
                'answer' => 'CompilerPass — это хук, который запускается на этапе компиляции контейнера и может менять определения сервисов до того, как контейнер закэшируется. Самый частый сценарий — собрать все сервисы с определённым тегом и инъектировать их в реестр или диспетчер: например, фабрика транспортов Messenger таким образом узнаёт обо всех зарегистрированных типах транспорта. Регистрируют CompilerPass в методе build() Kernel или бандла.',
                'difficulty' => 4,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как внедрить два разных экземпляра одного класса в Symfony?',
                'answer' => 'Когда autowiring не может различить реализации одного интерфейса, используют named autowiring: имя параметра конструктора должно совпадать с алиасом сервиса, например LoggerInterface $applicationLogger. С PHP 8 для того же служит атрибут #[Target("application")] на параметре, который явно указывает контейнеру, какой именно сервис подставить.',
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
                'answer' => 'Security строится вокруг фаерволов (firewalls), authenticator-классов и системы голосующих за доступ (voters). Authenticator извлекает учётные данные из запроса и возвращает Passport, фаервол создаёт TokenStorage с аутентифицированным пользователем, а проверки isGranted() делегируют решение AccessDecisionManager и Voters. Authorization-правила описывают через access_control, атрибут #[IsGranted] на контроллере или вызов $this->denyAccessUnlessGranted().',
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
                'answer' => 'Каждая шина Messenger — это стек middleware, через который последовательно проходит конверт (Envelope) с сообщением. Стандартный набор включает SendMessageMiddleware (отправляет в транспорт, если есть routing), HandleMessageMiddleware (вызывает обработчик), ValidationMiddleware и DoctrineTransactionMiddleware. Свой middleware пишут для логирования, метрик, повторных попыток или кастомной транзакционности.',
                'difficulty' => 4,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как Symfony работает с RoadRunner и FrankenPHP в режиме воркера?',
                'answer' => 'Компонент Runtime отделяет точку входа приложения (public/index.php) от среды выполнения: выбор runtime определяется переменной APP_RUNTIME или autoload_runtime.php. Для RoadRunner и FrankenPHP есть готовые runtime, которые держат Kernel в памяти и переиспользуют его между запросами, обнуляя только request-state. Это даёт значительный прирост производительности по сравнению с классическим PHP-FPM, но требует осторожности с глобальным состоянием и сессиями.',
                'difficulty' => 4,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как избежать циклических ссылок при сериализации сущностей в Symfony?',
                'answer' => 'Есть три рабочих подхода: задавать группы сериализации атрибутами #[Groups] и сериализовать только нужные группы; вводить отдельные DTO и маппить сущности на них вручную или через ObjectMapper; ограничивать глубину атрибутом #[MaxDepth] и включать опцию AbstractObjectNormalizer::ENABLE_MAX_DEPTH в контексте. Группы и DTO дают самый явный контроль над контрактом API.',
                'difficulty' => 4,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как оптимизировать Symfony-приложение под высокую нагрузку?',
                'answer' => 'Включают OPcache и при подходящем профиле нагрузки JIT, делают composer dump-autoload --classmap-authoritative и предкомпилируют контейнер в проде (cache:warmup), переходят на воркер-серверы вроде RoadRunner или FrankenPHP, чтобы не пересоздавать Kernel на каждом запросе. На уровне Doctrine борются с N+1 через жадные ассоциации и DTO-проекции, кэшируют Query, Result и метаданные, а тяжёлые сценарии выносят в Messenger.',
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
