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
                'answer' => 'Flex — это плагин Composer, который автоматизирует установку и удаление пакетов Symfony. При composer require он подтягивает рецепт (recipe) из официального или приватного репозитория и применяет его: создаёт конфиг-файлы в config/packages, регистрирует бандл в bundles.php, добавляет переменные в .env. Это превращает установку пакета из ручной правки нескольких файлов в одну команду.',
                'difficulty' => 3,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое сервис-контейнер в Symfony?',
                'answer' => 'Сервис-контейнер — это центральный объект, который умеет создавать и хранить сервисы (обычные PHP-объекты приложения), управлять их зависимостями и временем жизни. Symfony компилирует контейнер в кэш как сгенерированный PHP-класс, поэтому в рантайме разрешение зависимостей почти бесплатно. Конфигурируется он через services.yaml, XML или PHP, а в современных проектах большинство сервисов регистрируется автоматически.',
                'difficulty' => 3,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое компонент HttpFoundation в Symfony?',
                'answer' => 'HttpFoundation предоставляет объектно-ориентированную обёртку над HTTP-спецификацией: Request, Response, Cookie, Session и серверные параметры. Он заменяет работу с суперглобальными $_GET, $_POST, $_SERVER на тестируемое API с явными объектами-обёртками над заголовками и параметрами (HeaderBag, ParameterBag — мутабельные; иммутабельность — это про PSR-7, а не HttpFoundation). Это базовый кирпич всей Symfony и многих сторонних фреймворков, включая Laravel.',
                'difficulty' => 3,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работает Autowiring в Symfony?',
                'answer' => 'Autowiring разрешает конструктор и аргументы методов сервиса по type-hint: контейнер ищет в реестре сервис, реализующий нужный класс или интерфейс, и подставляет его автоматически. Это убирает руками написанные arguments в YAML и оставляет конфигурацию декларативной. Когда автоматического выбора недостаточно (несколько реализаций), используют именованные алиасы по имени параметра или атрибут #[Target].',
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
                'answer' => 'Autoconfigure автоматически проставляет сервису теги контейнера на основе реализованных им интерфейсов или родительских классов. Например, класс, реализующий EventSubscriberInterface, без единой строки конфигурации получает тег kernel.event_subscriber, а команда, наследующая Command, — тег console.command. Это позволяет писать обычный PHP-класс и сразу получать его в нужном экстеншн-пойнте фреймворка.',
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
                'answer' => 'Symfony поддерживает constructor injection (через __construct), setter injection (через сеттеры или метод с #[Required]) и property injection (в публичные свойства). Рекомендуемый способ — внедрение через конструктор, потому что зависимости становятся обязательными и явными, а объект всегда находится в валидном состоянии. Сеттеры используют для опциональных зависимостей и для разрыва циклов, property injection применяют редко.',
                'difficulty' => 3,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как получить доступ к сессии в современной Symfony (5.3+)?',
                'answer' => 'С Symfony 5.3 инъекция сервиса session напрямую (через тип SessionInterface) и Request::getSession() вне RequestStack-цикла признаны устаревшими. Сессию получают через RequestStack: $requestStack->getSession() либо напрямую из объекта Request методом $request->getSession(). Такой подход совместим с воркерными SAPI вроде RoadRunner и FrankenPHP, где состояние не должно жить дольше одного запроса.',
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
                'answer' => 'Listener регистрируется в конфигурации с указанием события, метода и приоритета — конфиг хранится отдельно от класса. Subscriber реализует EventSubscriberInterface и сам в статическом getSubscribedEvents() возвращает список событий, методов и приоритетов, что делает класс самодостаточным и переносимым. Благодаря autoconfigure subscriber автоматически получает тег kernel.event_subscriber и регистрируется без ручной конфигурации.',
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
                'answer' => 'Doctrine ORM — это реализация паттерна Data Mapper для PHP, которую DoctrineBundle интегрирует в Symfony. В отличие от Active Record (как Eloquent в Laravel), сущности в Doctrine — это обычные POPO-объекты без знания о базе, а отображение задаётся атрибутами или XML. Работа идёт через EntityManager и UnitOfWork, который собирает изменения и одной транзакцией сбрасывает их в БД при flush().',
                'difficulty' => 3,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое Doctrine Migrations и зачем они нужны?',
                'answer' => 'Doctrine Migrations — отдельный пакет, который версионирует схему БД через PHP-классы с методами up() и down(). Команда doctrine:migrations:diff сравнивает текущие сущности и реальную схему, генерируя новую миграцию, а doctrine:migrations:migrate применяет невыполненные. Это позволяет хранить эволюцию схемы в git и согласованно раскатывать её на разные окружения.',
                'difficulty' => 3,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как создать собственную console-команду в Symfony?',
                'answer' => 'Команду делают наследником Symfony\\Component\\Console\\Command\\Command (или используют атрибут #[AsCommand]) с реализацией метода execute(). Благодаря autoconfigure класс автоматически получает тег console.command, регистрируется в Application и становится доступным через bin/console. Зависимости внедряют как обычно через конструктор, а ввод и вывод приходят как InputInterface и OutputInterface.',
                'difficulty' => 3,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как описывать роутинг через атрибуты в современной Symfony?',
                'answer' => 'Начиная с Symfony 6 рекомендованный способ — атрибут #[Route] из Symfony\\Component\\Routing\\Attribute\\Route прямо над методом или классом контроллера. На классе атрибут задаёт префикс пути и общие требования, на методе — конкретный путь, имя, методы HTTP и условия. Поддержка doctrine/annotations в RoutingComponent помечена deprecated в Symfony 6.4 и удалена в 7.0; сам пакет doctrine/annotations существует, но фреймворком для роутинга не используется.',
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
                'answer' => 'Компонент Form берёт на себя три задачи: маппинг данных запроса на объект (entity или DTO), обработку валидации и встроенную CSRF-защиту. Форма описывается классом-наследником AbstractType, рендерится через Twig-функции и связывается с объектом через data_class. На выходе после $form->isValid() приложение получает уже типизированный и проверенный объект.',
                'difficulty' => 3,
                'topic' => 'php.symfony',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как устроена валидация в Symfony и как она связана с формами?',
                'answer' => 'Валидация выполняется компонентом Validator: ограничения вешаются на свойства и методы сущностей атрибутами PHP 8 (#[Assert\\NotBlank], #[Assert\\Email] и так далее) или конфигурацией. Форма при сабмите автоматически вызывает валидатор для связанного объекта и складывает нарушения в FormError, которые отрисовываются в шаблоне. Это разделяет правила (живут на сущности) и UI (живёт в FormType).',
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
                'answer' => 'Messenger — это компонент для отправки и обработки сообщений через шины и транспорты (Redis, RabbitMQ, Doctrine, sync, in-memory). По умолчанию обработчик вызывается синхронно в том же процессе, а для асинхрона сообщение маршрутизируется в транспорт через routing и обрабатывается отдельным воркером, запускаемым messenger:consume. Это позволяет одной и той же командой и в dev обрабатываться сразу, и в проде уходить в очередь.',
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
                'answer' => 'Full-stack Symfony из symfony/skeleton + symfony/webapp подтягивает Twig, Doctrine, Security, Messenger, Forms и весь типичный набор бандлов для классического веб-приложения. Микрофреймворк-вариант ставит только symfony/skeleton и symfony/runtime, а компоненты добавляются по мере необходимости — удобно для API-сервисов и небольших приложений. В обоих случаях ядро одно и то же, отличается только начальный набор зависимостей и рецептов Flex.',
                'difficulty' => 3,
                'topic' => 'php.symfony',
            ],
        ];
    }
}
