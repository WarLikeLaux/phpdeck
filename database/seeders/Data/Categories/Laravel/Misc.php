<?php

namespace Database\Seeders\Data\Categories\Laravel;

class Misc
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel?',
                'answer' => 'Laravel - это PHP-фреймворк для разработки веб-приложений, основанный на архитектуре MVC. Простыми словами: это набор готовых инструментов и правил, который помогает быстро писать веб-сайты и API на PHP, не изобретая велосипед. Laravel включает ORM (Eloquent), систему маршрутизации, миграции, очереди, аутентификацию, шаблонизатор Blade и многое другое.',
                'code_example' => 'composer create-project laravel/laravel example-app
cd example-app
php artisan serve',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Какие основные преимущества Laravel?',
                'answer' => 'Преимущества Laravel: 1) Элегантный синтаксис и читаемый код. 2) Огромная экосистема пакетов (Sanctum, Horizon, Telescope, Octane, Scout). 3) Eloquent ORM - удобная работа с БД. 4) Встроенные миграции, сидеры, фабрики. 5) Очереди, события, кеш, сессии "из коробки". 6) Большое сообщество и документация. 7) Artisan CLI для генерации кода. 8) Активное развитие и регулярные релизы.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 1,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое архитектура MVC и как она реализована в Laravel?',
                'answer' => 'MVC (Model-View-Controller) - это паттерн разделения логики на 3 части. Model - работа с данными (Eloquent-модели). View - отображение (Blade-шаблоны). Controller - обработка запроса и связь между моделью и видом. В Laravel роуты направляют запрос в контроллер, контроллер достаёт данные через модель и передаёт их во view.',
                'code_example' => '// Route
Route::get(\'/users/{id}\', [UserController::class, \'show\']);

// Controller
class UserController extends Controller {
    public function show($id) {
        $user = User::findOrFail($id); // Model
        return view(\'users.show\', compact(\'user\')); // View
    }
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Опиши структуру проекта Laravel - что лежит в основных папках?',
                'answer' => 'app/ - основной код приложения (модели, контроллеры, провайдеры). bootstrap/ - стартовые файлы и кеш. config/ - конфигурационные файлы. database/ - миграции, сидеры, фабрики. public/ - точка входа index.php и статика. resources/ - views, css, js, lang-файлы. routes/ - файлы маршрутов (web.php, api.php, console.php). storage/ - логи, кеш, загрузки. tests/ - тесты. vendor/ - зависимости composer.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 2,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Опиши жизненный цикл запроса в Laravel.',
                'answer' => '1) Запрос попадает в public/index.php. 2) Загружается composer autoload и создаётся экземпляр Application (контейнер). 3) Bootstraps - регистрируются провайдеры, загружается env, конфиги. 4) HTTP-ядро (Illuminate\\Foundation\\Http\\Kernel) пропускает запрос через глобальные middleware - в L11 user-facing класса app/Http/Kernel.php нет, конфигурация ядра живёт в bootstrap/app.php, но сам класс Foundation\\Http\\Kernel остался внутри фреймворка. 5) Запрос диспатчится в роутер, который находит маршрут и его middleware. 6) Запускается контроллер/closure. 7) Формируется Response. 8) Response проходит обратно через middleware (terminate). 9) Ответ отправляется клиенту.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Facades в Laravel и как они работают?',
                'answer' => 'Facade - это статический "прокси" к объекту в контейнере. Простыми словами: вы пишете Cache::get(), а на самом деле вызывается метод объекта, который Laravel взял из контейнера. Под капотом Facade использует магический метод __callStatic, перенаправляя вызов на реальный сервис.',
                'code_example' => 'use Illuminate\Support\Facades\Cache;

Cache::put(\'key\', \'value\', 60);
// эквивалентно
app(\'cache\')->put(\'key\', \'value\', 60);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как работает локализация в Laravel?',
                'answer' => 'Lang-файлы лежат в lang/ (или resources/lang/). Хелпер __() читает строку, trans_choice - с учётом числа (plural). Можно использовать lang-ключи (json) или короткие ключи (php-массивы). Текущая локаль: app()->getLocale(), app()->setLocale(\'ru\').',
                'code_example' => '// lang/ru.json
{ "Welcome": "Добро пожаловать" }

// lang/ru/messages.php
return [\'greeting\' => \'Привет, :name\'];

echo __(\'Welcome\');
echo __(\'messages.greeting\', [\'name\' => \'Anna\']);
echo trans_choice(\'apples\', 5);',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Scout?',
                'answer' => 'Scout - это пакет для полнотекстового поиска в Eloquent-моделях. Драйверы: Algolia, Meilisearch, Typesense, database (простой LIKE), collection. Простыми словами: добавили трейт Searchable, и модель автоматически индексируется при сохранении.',
                'code_example' => 'class Post extends Model {
    use Searchable;

    public function toSearchableArray(): array {
        return [\'title\' => $this->title, \'body\' => $this->body];
    }
}

$results = Post::search(\'laravel\')->paginate(15);

php artisan scout:import "App\\Models\\Post"',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Telescope?',
                'answer' => 'Telescope - это инструмент отладки и мониторинга Laravel-приложения. Простыми словами: dashboard, который показывает все запросы, SQL-запросы, jobs, события, кеш, логи, mail, exceptions. Используется в development. В продакшене обычно отключается или ограничивается доступ.',
                'code_example' => 'composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate

// доступ /telescope',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Pulse?',
                'answer' => 'Pulse - это лёгкий dashboard для мониторинга performance в продакшене (от Laravel). Простыми словами: показывает медленные запросы, нагруженные jobs, slow queries, активных пользователей, cache hit rate в реальном времени. Альтернатива Telescope для production.',
                'code_example' => 'composer require laravel/pulse
php artisan vendor:publish --tag=pulse-config
php artisan migrate

// доступ /pulse',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Почему нельзя использовать env() вне config-файлов после кеширования?',
                'answer' => 'При config:cache Laravel выполняет все config-файлы и сохраняет результат. Файл .env при этом НЕ читается на каждом запросе. Если в коде вы вызовете env() напрямую (вне config), оно вернёт null в продакшене. Правильно: значения env читать только в config/, а в коде использовать config(\'app.something\').',
                'code_example' => '// плохо - в коде
$key = env(\'STRIPE_KEY\'); // вернёт OS-env / default, но не значение из .env
                          // после config:cache - частый источник "пустых" переменных в проде

// правильно
// config/services.php
return [\'stripe\' => [\'key\' => env(\'STRIPE_KEY\')]];

// в коде
$key = config(\'services.stripe.key\');',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Macroable trait?',
                'answer' => 'Macroable - это трейт, позволяющий добавлять кастомные методы в классы Laravel runtime через ::macro(). Простыми словами: можно расширять Collection, Str, Request, Response своими методами. Регистрируется в Service Provider boot().',
                'code_example' => 'use Illuminate\Support\Str;

// Имя должно быть НОВЫМ - Macroable работает через __callStatic / __call,
// и приоритет всегда у реального метода класса: если метод уже определён,
// он перекрывает макрос (макрос становится мёртвым кодом).
// Например, Str::isUuid() уже существует в ядре - макрос с таким именем недостижим.
Str::macro(\'isHexColor\', function ($value) {
    return preg_match(\'/^#[0-9a-f]{3}([0-9a-f]{3})?$/i\', $value) === 1;
});

Str::isHexColor(\'#1abc9c\'); // true

Collection::macro(\'toUpper\', function () {
    return $this->map(fn($v) => strtoupper($v));
});

collect([\'a\', \'b\'])->toUpper(); // [\'A\', \'B\']',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как обрабатывать исключения в Laravel?',
                'answer' => 'Все exceptions попадают в обработчик. В Laravel 10 - app/Exceptions/Handler.php, в Laravel 11+ - bootstrap/app.php (метод withExceptions). Можно: переопределить рендеринг конкретных исключений, добавить контекст в логи, добавить reportable/renderable callbacks. Кастомные исключения могут реализовать report()/render().',
                'code_example' => '// Laravel 11+ bootstrap/app.php
->withExceptions(function (Exceptions $e) {
    $e->render(function (NotFoundHttpException $e, Request $r) {
        if ($r->is(\'api/*\')) {
            return response()->json([\'error\' => \'Not found\'], 404);
        }
    });
})

// Кастомное исключение
class PaymentFailed extends Exception {
    public function render(): JsonResponse {
        return response()->json([\'error\' => $this->getMessage()], 422);
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Action Classes (Single-Purpose Service) и зачем они нужны?',
                'answer' => 'Action Class - это класс с одним методом execute/handle/__invoke, который инкапсулирует одно действие приложения (например, "создать пользователя"). НЕ путать с invokable-контроллером: Action - сервис-объект, не привязанный к HTTP-запросу, его можно вызвать из контроллера, ArtisanCommand или Job. Простыми словами: вытащить бизнес-логику из контроллера в отдельный класс. Чище контроллер, легче тестировать, переиспользуемо в job/console/controller.',
                'code_example' => 'class CreateUserAction {
    public function execute(array $data): User {
        return DB::transaction(function () use ($data) {
            $user = User::create($data);
            $user->profile()->create([\'avatar\' => null]);
            event(new UserCreated($user));
            return $user;
        });
    }
}

// Controller
public function store(StoreUserRequest $r, CreateUserAction $a) {
    $user = $a->execute($r->validated());
    return new UserResource($user);
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'В чём разница между config() и env()?',
                'answer' => 'env() читает переменную из окружения процесса (через $_ENV/getenv()). До php artisan config:cache Laravel загружает .env через Dotenv в это окружение, поэтому env() везде "работает". После config:cache загрузка .env пропускается, и env() видит ТОЛЬКО переменные, заданные на уровне ОС (Docker -e, systemd Environment=, переменные окружения сервера) либо вернёт второй аргумент-default. То есть после кеша env() в коде НЕ ВСЕГДА возвращает null - возвращает default/OS-env, если они есть; на практике в проде .env-only переменные становятся "невидимыми" - отсюда ощущение "вернёт null". Правильно: env() читать ТОЛЬКО в config/, в коде использовать config(). config() работает всегда - значения зашиты в кеш.',
                'code_example' => '// .env
APP_NAME=MyApp

// config/app.php
\'name\' => env(\'APP_NAME\', \'Laravel\'),

// в коде
config(\'app.name\'); // правильно
env(\'APP_NAME\');    // null после config:cache в проде',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Vapor и Forge? Какие у них подводные камни?',
                'answer' => 'Forge - сервис для развёртывания Laravel-приложений на VPS (DigitalOcean, AWS, Linode). Автоматизирует настройку nginx, php-fpm, supervisor, SSL, deploy через git. Vapor - serverless-платформа для Laravel на AWS Lambda. Не нужны серверы, оплата по запросам, автомасштабирование. Главный подводный камень Vapor - файловая система. В Lambda есть директория /tmp размером до 10 GiB (по умолчанию 512 MB, конфигурируется), которая ТЕХНИЧЕСКИ работает: можно временно сохранить файл, обработать, отдать клиенту или загрузить в S3 в рамках одного запроса. Но /tmp ЭФЕМЕРНА - между прогревами контейнера данные теряются, между разными контейнерами не шарятся. Поэтому для персистентного хранения (avatars, uploads, generated PDFs) обязателен S3 диск; для transient-обработки (распаковать, отресайзить, удалить) /tmp вполне подходит. Также важно: в Vapor нет долгоживущих процессов - очереди работают через SQS, расписание - через CloudWatch, websockets - через отдельный сервис (Pusher/Ably/Reverb на EC2).',
                'code_example' => '<?php
// config/filesystems.php - в Vapor местный disk заворачивают в /tmp
"local" => [
    "driver" => "local",
    "root"   => "/tmp", // эфемерно, но работает в рамках одного invoke
],

// Workflow: скачали → обработали → залили в S3
$tmp = Storage::disk("local")->path("export.csv");
file_put_contents($tmp, $csvContent);          // /tmp/export.csv
$pdf = $this->renderPdf($tmp);                 // тоже в /tmp
Storage::disk("s3")->put("reports/{$id}.pdf", file_get_contents($pdf));
unlink($tmp); unlink($pdf);                    // подчистить за собой',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое helpers в Laravel и приведи примеры.',
                'answer' => 'Хелперы - это глобальные функции, доступные везде. Примеры: route(), url(), config(), env(), auth(), now(), today(), abort(), back(), redirect(), response(), request(), session(), cookie(), view(), validator(), collect(), str(), tap(), throw_if(), throw_unless(), data_get(), data_set(), Arr::, Str::.',
                'code_example' => 'now()->addDays(7);
str(\'Hello\')->upper(); // Stringable
collect([1,2,3])->sum();
abort_if(! $user, 403);
$value = data_get($array, \'user.profile.name\', \'default\');
tap($user, fn($u) => $u->update([\'last_login\' => now()]))->save();',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что нового в Laravel 11 по сравнению с Laravel 10?',
                'answer' => 'Laravel 11: упрощённая структура (нет app/Http/Kernel.php, ConsoleKernel, app/Exceptions/Handler.php - всё в bootstrap/app.php). routes/console.php вместо ConsoleKernel для расписания/команд. Health-endpoint /up из коробки. Per-second rate limiting (perSecond). Метод casts() в модели как альтернатива свойству $casts. Slimmer config: многие опции убраны в дефолты. Минимальный PHP 8.2. Из новых пакетов экосистемы: Reverb (WebSocket-сервер), Pennant (feature flags), Volt (single-file Livewire), Folio (page-based routing).',
                'code_example' => '// bootstrap/app.php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(web: __DIR__.\'/../routes/web.php\')
    ->withMiddleware(function (Middleware $m) {
        $m->web(append: [EnsureUserIsActive::class]);
    })
    ->withExceptions(function (Exceptions $e) {})
    ->create();',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Зачем нужен Pipeline и как его использовать вне middleware?',
                'answer' => 'Pipeline - декоратор поверх классов pipe-методов. Принимает входное значение и пропускает через цепочку, каждый pipe вызывает $next($payload). Используется для middleware HTTP, но прекрасно подходит для бизнес-цепочек: валидация-обогащение-вычисление-сохранение. Альтернатива длинному if-else или CoR вручную. Pipes могут быть Closure или класс с handle().',
                'code_example' => '<?php
$result = app(Pipeline::class)
    ->send($order)
    ->through([
        ValidateInventory::class,
        ApplyPromoCodes::class,
        ChargeCustomer::class,
        EmitOrderPlacedEvent::class,
    ])
    ->thenReturn();',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как Laravel Scout работает и какие нюансы при индексации больших коллекций?',
                'answer' => 'Scout - абстракция над поисковыми движками (Algolia, Meilisearch, database). Использует Searchable-трейт: автоматически синхронизирует модели с индексом на save/delete через очередь (если SCOUT_QUEUE=true). Для больших коллекций используют scout:import, который чанкует выборку. Для сложных фильтров комбинируют search($q)->where()->whereIn() и Builder-callback для специфичных запросов. softDeletes требуют отдельного флага, иначе удалённые остаются в индексе.',
                'code_example' => '<?php
class Product extends Model {
    use Searchable;
    public function toSearchableArray(): array {
        return ["name" => $this->name, "category" => $this->category->name];
    }
}
// php artisan scout:import App\\Models\\Product',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое DTO (Data Transfer Object) и зачем они нужны в Laravel?',
                'answer' => 'DTO - объект для передачи типизированных данных между слоями приложения (Request → Action/Service → Repository, Service → Job, Service → API client). Заменяет передачу ассоциативных массивов вида $request->validated(), которые: (1) не дают автокомплита и статической проверки типов; (2) превращаются в "магические строки" по ключам, и переименование поля ломает всё молча; (3) не валидируются повторно при передаче в job (где исходный Request уже недоступен). DTO решает это: класс с явно типизированными readonly-свойствами, конструктор делает контракт явным, IDE подсказывает поля, phpstan ловит опечатки. В современном Laravel чаще всего используют readonly-классы (модификатор на уровне всего класса доступен с PHP 8.2; readonly у отдельных свойств - с 8.1) с named arguments, либо пакет spatie/laravel-data, который умеет автоматически собирать DTO из Request, валидировать и сериализовать обратно в JSON. Для job DTO критичен: сериализуется в очередь как обычный объект, без зависимости от Request. Антипаттерн: передавать в Action/Job сырой $request - это нарушает single responsibility и делает класс непригодным к запуску из консоли/теста.',
                'code_example' => '<?php
// Чистый PHP 8.1+ readonly DTO
final readonly class CreateOrderData
{
    public function __construct(
        public int $userId,
        public string $currency,
        public array $items,        // OrderItemData[]
        public ?string $promoCode = null,
    ) {}

    public static function fromRequest(StoreOrderRequest $r): self
    {
        return new self(
            userId:    $r->user()->id,
            currency:  $r->validated("currency"),
            items:     array_map(OrderItemData::fromArray(...), $r->validated("items")),
            promoCode: $r->validated("promo"),
        );
    }
}

// Использование - типизированный контракт между слоями
public function store(StoreOrderRequest $r, CreateOrderAction $action) {
    $order = $action->execute(CreateOrderData::fromRequest($r));
    return new OrderResource($order);
}

// spatie/laravel-data делает то же декларативно
class CreateOrderData extends Data {
    public function __construct(
        public int $userId,
        #[Rule("required|string|size:3")] public string $currency,
        #[DataCollectionOf(OrderItemData::class)] public array $items,
    ) {}
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'В чём разница между Service и Action классами? Когда что выбирать?',
                'answer' => 'Service - класс с НЕСКОЛЬКИМИ публичными методами, объединёнными общей предметной областью: UserService::create(), update(), suspend(), restore(). Action - класс с ОДНИМ публичным методом (execute / handle / __invoke), инкапсулирующий ровно одну операцию: CreateUserAction, SuspendUserAction. Это разные уровни декомпозиции, а не "правильный/неправильный". Когда Service: набор простых CRUD-операций, между которыми много общего state/зависимостей; точка входа в bounded context для не-DDD-проектов. Когда Action: операции имеют разные зависимости (одна нуждается в почтовом клиенте, другая - в платёжном API), сложную бизнес-логику внутри, или должны переиспользоваться в Controller + ArtisanCommand + Job. Минусы Service: со временем разрастается до "god object" на 30 методов, тесты тяжёлые (приходится мокать всё, даже не используемое в данном тесте), DI-конструктор раздут. Минусы Action: больше файлов, между связанными операциями нужно прыгать. Прагматичный подход: начинать с Service, выделять Action, когда метод стал толстым (>30 строк) или появились свои зависимости. В обоих случаях контроллер тонкий: validate → call → return resource.',
                'code_example' => '<?php
// Service - связка CRUD на одной сущности
final class UserService
{
    public function __construct(
        private Mailer $mailer,
        private AuditLogger $audit,
    ) {}

    public function create(CreateUserData $data): User { /* ... */ }
    public function update(User $u, UpdateUserData $d): User { /* ... */ }
    public function suspend(User $u, string $reason): void { /* ... */ }
    public function restore(User $u): void { /* ... */ }
}

// Action - одна тяжёлая операция со своими зависимостями
final class ChargeFailedPaymentRetryAction
{
    public function __construct(
        private StripeClient $stripe,         // нужен только здесь
        private RetryPolicyResolver $policy,  // нужен только здесь
        private SlackNotifier $slack,
    ) {}

    public function execute(Payment $payment): PaymentResult
    {
        // 50 строк сложной логики ретраев
    }
}

// Один и тот же Action из разных входных точек:
// - HTTP: PaymentController::retry() → $action->execute($payment)
// - CLI:  RetryFailedPaymentsCommand::handle() → foreach ... $action->execute($p)
// - Job:  RetryPaymentJob::handle(ChargeFailedPaymentRetryAction $a) → $a->execute(...)',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое фасад Context (Laravel 11+) и зачем он нужен?',
                'answer' => 'Context (Illuminate\Support\Facades\Context, появился в Laravel 11) - это механизм для хранения метаданных в рамках текущего request/job, которые автоматически добавляются ко всем log-записям и автоматически передаются в queued jobs. Простыми словами: вы один раз пишете Context::add("trace_id", $id) в начале запроса, и это значение попадёт в каждую log-строку этого запроса, а также автоматически окажется доступно внутри любого job, диспатченного во время этого запроса. Это решает классическую проблему observability: связать логи разных слоёв (controller → service → job → notification) одним trace_id, не таская его руками через каждый параметр. Под капотом Context живёт в singleton сервиса в контейнере; в Octane слушатель события RequestReceived вызывает Context::flush() между запросами, чтобы данные не утекли. При dispatch job текущий снимок Context-а сериализуется в payload job-а и восстанавливается в воркере. Также есть hidden context (Context::addHidden()) - не попадает в логи, но передаётся между job-ами; полезно для tenant_id или auth-state. Заменяет хак с глобальным singleton + Log::shareContext().',
                'code_example' => '<?php
// Middleware - добавляем trace_id один раз
class AssignTraceId
{
    public function handle(Request $request, Closure $next)
    {
        Context::add("trace_id", $request->header("X-Trace-Id", (string) Str::uuid()));
        Context::add("user_id",  $request->user()?->id);

        return $next($request);
    }
}

// Где-то глубоко в коде
Log::info("Order created", ["order_id" => $order->id]);
// → лог уже содержит trace_id и user_id из Context

// Job, диспатченный в этом запросе
class ProcessOrder implements ShouldQueue
{
    public function handle()
    {
        // здесь Context::get("trace_id") вернёт ТОТ ЖЕ trace_id,
        // что был у HTTP-запроса, инициировавшего dispatch
        Log::info("Processing order"); // тоже с trace_id
    }
}

// Hidden context - в логи не попадает, в job - передаётся
Context::addHidden("tenant_id", $tenant->id);

// В Octane контекст изолирован между запросами - утечки не будет',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Pennant и зачем он нужен?',
                'answer' => 'Laravel Pennant - официальный пакет (composer require laravel/pennant) для управления feature flags. Решает задачи: 1) Trunk-based development - вливать незавершённую фичу в main за флагом, чтобы не держать долгоживущие feature-ветки. 2) Постепенный rollout - включить новую фичу 5% юзеров, потом 50%, потом всем; откатить быстро без редеплоя. 3) A/B-тестирование вариантов UI/алгоритма. 4) Kill switch - мгновенно отключить проблемную фичу при инциденте. 5) Feature gating по сегменту (только premium-юзеры, только определённые tenant-ы). Регистрация флага в provider через Feature::define(name, resolver), где resolver - замыкание, получающее scope (по умолчанию current user) и возвращающее bool/строку для variant-флагов. Проверка в коде: Feature::active("new-checkout") или $user->features()->active("new-checkout"). Хранение: array (in-memory, per-request) для тестов, database (persistent, дёшево), Redis (быстро). Variant-флаги дают больше двух состояний - "control" / "blue-button" / "green-button". Полезные методы: Lottery::odds() для случайной выборки процентом, when()/unless() в blade, scope() для не-юзерных скоупов (tenant, organisation). Тестирование: Feature::activate() / Feature::deactivate() в setUp. Альтернативы: Laravel Gate (только bool через политики), сторонние SaaS (LaunchDarkly, GrowthBook, Unleash) - богаче по UI и аналитике, дороже.',
                'code_example' => '<?php
// composer require laravel/pennant

// AppServiceProvider::boot()
use Laravel\\Pennant\\Feature;
use Illuminate\\Support\\Lottery;

public function boot(): void
{
    // Простой bool-флаг с правилом
    Feature::define("new-checkout", fn (User $user) =>
        $user->isInternal() ||                    // всегда для своих
        $user->id % 10 === 0                       // и для 10% юзеров (стабильно по id)
    );

    // Variant-флаг (несколько вариантов)
    Feature::define("homepage", fn (User $user) =>
        Lottery::odds(1, 3)
            ->winner(fn () => "blue")
            ->loser(fn () => "control")
            ->choose()
    );

    // По tenant вместо юзера
    Feature::define("instant-search", fn (Team $team) =>
        in_array($team->plan, ["pro", "enterprise"])
    );
}

// Использование
if (Feature::active("new-checkout")) {
    return view("checkout.v2");
}

// Per-user
if ($user->features()->active("new-checkout")) { /* ... */ }

// Variant - получить текущий вариант
$variant = Feature::value("homepage"); // "blue" | "control"

// В Blade
@feature("new-checkout")
    <NewCheckout />
@else
    <OldCheckout />
@endfeature

// Принудительно для не-юзерного scope
Feature::for($team)->active("instant-search");

// В тестах
public function test_new_checkout(): void
{
    Feature::activate("new-checkout"); // глобально
    // ...
    Feature::deactivate("new-checkout");
}

// Очистка кеша флагов
Feature::flushCache();
Feature::for($user)->forget("new-checkout");',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Contracts в Laravel и чем они отличаются от Facades?',
                'answer' => 'Contracts — это набор интерфейсов в Illuminate\\Contracts, описывающих основные сервисы фреймворка (Cache, Queue, Mail и т.д.). Внедряя контракт через конструктор, вы получаете ту же реализацию, что стоит за фасадом, но через явный DI. Фасад даёт удобный статический фасадный синтаксис, контракт — типизированную зависимость, удобную для подмены и unit-тестов.',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Sail и зачем он нужен, если уже есть Docker?',
                'answer' => 'Sail — лёгкий CLI-обёртка над docker compose с готовым набором сервисов (PHP, MySQL/PostgreSQL, Redis, MeiliSearch, MailHog/Mailpit, Selenium). Даёт команды вида sail up, sail artisan, sail composer без необходимости писать собственный docker-compose.yml. Подходит как стандартное dev-окружение, в продакшен напрямую не предназначен.',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Pint и чем он отличается от php-cs-fixer?',
                'answer' => 'Pint — официальный фиксер стиля кода Laravel, построенный поверх PHP-CS-Fixer. Идёт с готовыми пресетами (laravel, psr12, per, symfony) и нулевой конфигурацией: достаточно запустить vendor/bin/pint. Под капотом тот же php-cs-fixer, но с дефолтами под Laravel и удобным CLI; кастомные правила задаются через pint.json.',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Cashier и какие провайдеры он поддерживает?',
                'answer' => 'Cashier — официальный пакет для подписочного биллинга. Существует две версии: Cashier Stripe и Cashier Paddle. Закрывает создание подписок, тарифные планы, пробные периоды, купоны, single-charge платежи, инвойсы и обработку вебхуков. На стороне модели User добавляется трейт Billable, дальше биллинг ведётся выразительным API вместо ручных вызовов SDK.',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Folio и в чём его особенность маршрутизации?',
                'answer' => 'Folio — пакет page-based роутинга: маршрут создаётся самим фактом существования Blade-файла в каталоге resources/views/pages. Файл users/[id].blade.php автоматически становится маршрутом /users/{id}. Параметры в квадратных скобках, middleware и имена объявляются прямо во фронт-маттере страницы. Удобен для сайтов с большим числом простых страниц без явной декларации в routes/web.php.',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Envoy и для чего он применяется?',
                'answer' => 'Envoy — простой раннер задач на удалённых серверах через SSH. Задачи описываются в Envoy.blade.php в синтаксисе, похожем на Blade: @servers, @task. Используется для деплоя, миграций, обслуживания серверов: envoy run deploy выполнит указанные команды на всех заданных серверах. По функциям сравним с упрощённым Capistrano или Deployer для PHP.',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Prompts и где он используется?',
                'answer' => 'Prompts — пакет красивых интерактивных форм для CLI: text, password, confirm, select, multiselect, search, suggest, spin. Используется внутри artisan-команд и инсталлеров пакетов вместо $this->ask()/$this->choice(). С Laravel 10+ ставится по умолчанию и применяется самим фреймворком в make:* командах.',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel IDE Helper и зачем он нужен?',
                'answer' => 'IDE Helper (barryvdh/laravel-ide-helper) — dev-пакет, генерирующий PHPDoc-метаинформацию для фасадов, моделей и контейнерных биндингов. Команды ide-helper:generate, ide-helper:models, ide-helper:meta создают _ide_helper.php и .phpstorm.meta.php, чтобы IDE и статические анализаторы понимали магические методы Eloquent (where{Field}, find), фасады и резолв из контейнера.',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Nova и чем она отличается от бесплатных админок (Filament, Backpack)?',
                'answer' => 'Nova — официальная платная админка Laravel: Resource-классы описывают CRUD-страницы, фильтры, lenses, actions и метрики. Filament и Backpack — open-source конкуренты с похожей моделью и часто более активным комьюнити. Nova даёт официальную поддержку и интеграцию с экосистемой (Scout, Sanctum), Filament — современный TALL-стек и плагины, Backpack — наиболее зрелое решение из бесплатных.',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Какие основные helper-функции Laravel часто используются?',
                'answer' => 'route(\'users.show\', $id) — URL по имени маршрута. url(\'/foo\') — URL от base. asset(\'css/app.css\') — URL статики из public. old(\'email\') — старое значение формы после ошибки. now() — Carbon::now(). config(\'app.name\') — значение из config. env(\'KEY\') — переменная .env (только в config-файлах).',
                'difficulty' => 1,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Какие способы вернуть ответ из контроллера в Laravel?',
                'answer' => 'return view(\'users.show\', [\'user\'=>$user]) — HTML-страница. return redirect(\'/login\') или redirect()->route(\'home\') — редирект. return response()->json([\'ok\'=>true]) — JSON. return response()->download($path) — файл на скачивание. return back() — назад. return abort(404) — прервать 404.',
                'difficulty' => 1,
                'topic' => 'laravel.misc',
            ],
        ];
    }
}
