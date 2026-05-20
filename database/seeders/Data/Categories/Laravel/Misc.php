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
                'answer' => '**Laravel** — самый популярный PHP-фреймворк для веб-приложений и API, построенный на архитектуре **MVC**.

Что входит «из коробки»:

- ORM **Eloquent** (Active Record) для работы с БД.
- Маршрутизация (`routes/web.php`, `routes/api.php`).
- Шаблонизатор **Blade**.
- Миграции, сидеры, фабрики.
- Аутентификация, очереди, кеш, события, уведомления.
- CLI **Artisan** для генерации кода и обслуживания.

Создатель — Тейлор Отвелл. Стартовать: `composer create-project laravel/laravel my-app`.',
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
                'answer' => 'Главные плюсы Laravel:

1. **Читаемый и элегантный синтаксис** — код легко поддерживать.
2. **Полная экосистема**: `Sanctum`, `Horizon`, `Telescope`, `Octane`, `Scout`, `Cashier`, `Pulse`, `Reverb`.
3. **Eloquent ORM** — работа с БД через объекты, со связями и событиями.
4. **Миграции, сидеры, фабрики** — версионирование схемы и тестовых данных.
5. **Из коробки**: очереди, события, кеш, сессии, аутентификация, валидация.
6. **Artisan CLI** — генерация кода и обслуживание одной командой.
7. **Огромное community** и подробная документация на laravel.com.
8. **Регулярные релизы** — раз в год, активное развитие.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 1,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое архитектура MVC и как она реализована в Laravel?',
                'answer' => '**MVC (Model-View-Controller)** — паттерн разделения логики на 3 части:

- **Model** — работа с данными. В Laravel это Eloquent-модели в `app/Models`. Знают про БД, но не про HTTP.
- **View** — отображение. Blade-шаблоны в `resources/views`. Знают про вёрстку, но не про БД.
- **Controller** — оркестратор. Принимает HTTP-запрос, дёргает модель, передаёт результат во view.

Поток запроса в Laravel:

1. **Route** (`routes/web.php`) принимает URL и направляет в метод контроллера.
2. **Controller** валидирует ввод, дёргает **Model**.
3. **Model** общается с БД.
4. **Controller** возвращает `view(...)` или JSON.

Цель — каждый слой делает свою работу. Бизнес-логика обычно выносится из контроллера в **Service/Action**-классы, чтобы контроллер оставался тонким.',
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
                'answer' => 'Основные папки в корне Laravel-проекта:

- **`app/`** — код приложения: `Models`, `Http/Controllers`, `Http/Middleware`, `Providers`, `Console`, `Jobs`, `Events`, `Listeners`.
- **`bootstrap/`** — стартовые файлы (`app.php` — конфиг ядра в L11+) и кеш фреймворка.
- **`config/`** — конфиги (`app.php`, `database.php`, `auth.php`, ...).
- **`database/`** — `migrations/`, `seeders/`, `factories/`.
- **`public/`** — точка входа `index.php` и статика (CSS, JS, изображения). Сюда смотрит web-сервер.
- **`resources/`** — `views/` (Blade), `js/`, `css/`, `lang/`.
- **`routes/`** — `web.php`, `api.php`, `console.php`, `channels.php`.
- **`storage/`** — логи, кеш, скомпилированные view, загруженные файлы (`app/`, `framework/`, `logs/`).
- **`tests/`** — тесты (`Feature/`, `Unit/`).
- **`vendor/`** — зависимости Composer (в Git **не коммитится**).',
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
                'answer' => 'Lang-файлы лежат в **`lang/`** (в Laravel 9+, до этого — `resources/lang/`). Публикуются командой `php artisan lang:publish`.

Два формата ключей:

- **JSON** (`lang/ru.json`) — ключ = английская фраза, значение = перевод. Удобно для длинных строк.
- **PHP-массивы** (`lang/ru/messages.php`) — короткие ключи: `__(\'messages.greeting\')`. Удобно для группировки.

Хелперы:

- **`__(\'key\')`** или **`trans(\'key\')`** — читает строку.
- **`trans_choice(\'apples\', $count)`** — учёт числа (`1 яблоко|:count яблок`).
- **`@lang(\'...\')`** в Blade.

Управление локалью:

- **`app()->getLocale()`** — текущая.
- **`app()->setLocale(\'ru\')`** — переключить.
- Дефолт — `config(\'app.locale\')` (берётся из `APP_LOCALE` в `.env`).

Обычно локаль ставится через middleware на основе сессии/заголовка `Accept-Language`/URL.',
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
                'answer' => '**Хелперы** — глобальные функции Laravel, доступные везде. Не надо импортировать неймспейсы.

Часто используемые:

- **URL и роутинг**: `route()`, `url()`, `asset()`, `redirect()`, `back()`.
- **Запрос/сессия/auth**: `request()`, `session()`, `cookie()`, `auth()`, `csrf_token()`.
- **Ответ**: `response()`, `view()`, `abort()`, `abort_if()`, `abort_unless()`.
- **Конфиг и окружение**: `config()`, `env()` (только в `config/`!).
- **Время**: `now()`, `today()` — возвращают `Carbon`.
- **Коллекции и строки**: `collect()`, `str()`, `tap()`.
- **Валидация**: `validator()`.
- **Доступ к массивам**: `data_get($array, \'user.profile.name\', \'default\')`, `data_set()`.
- **Утилиты**: `throw_if()`, `throw_unless()`.

Также есть статические классы: **`Str::`** (`Str::slug`, `Str::random`), **`Arr::`** (`Arr::get`, `Arr::pluck`).',
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
                'answer' => 'Contracts - набор интерфейсов в неймспейсе Illuminate\\Contracts, описывающих основные сервисы фреймворка: Cache\\Repository, Queue\\Queue, Mail\\Mailer, Filesystem\\Filesystem, Auth\\Guard и т.д. Внедряя контракт через конструктор, вы получаете ту же реализацию, что стоит за фасадом, но через явный DI. Преимущества: 1) Подменяется в тестах через $this->instance(Contract::class, $mock) без shouldReceive на фасадах. 2) Типизированная зависимость видна в сигнатуре - реальный контракт класса. 3) Удобно для пакетов, которые не хотят жёстко зависеть от фасадов Laravel. Фасад выигрывает по краткости в простом коде, контракт - по тестируемости и явности в сервисах/Action-классах.',
                'code_example' => '<?php
// Facade - кратко, статика
use Illuminate\\Support\\Facades\\Cache;
Cache::put("k", "v", 60);

// Contract - явная зависимость через DI
use Illuminate\\Contracts\\Cache\\Repository as CacheContract;

class ReportService {
    public function __construct(
        private CacheContract $cache,        // тот же объект, что за фасадом Cache
        private \\Illuminate\\Contracts\\Mail\\Mailer $mailer,
    ) {}

    public function generate(): void {
        $this->cache->put("last_report_at", now(), 3600);
    }
}

// В тесте подмена тривиальна
$this->instance(CacheContract::class, new InMemoryCache());',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Sail и зачем он нужен, если уже есть Docker?',
                'answer' => 'Sail - официальный CLI-обёртка над docker compose с готовым docker-compose.yml для типового dev-стека: PHP, MySQL/PostgreSQL/MariaDB, Redis, MeiliSearch/Typesense, MailHog/Mailpit, Selenium для Dusk. Команды sail up, sail artisan migrate, sail composer require, sail npm i проксируют команды в контейнеры приложения - не нужно держать локально установленные PHP/Node/composer и не нужно писать свой docker-compose. Ставится через composer require laravel/sail --dev + php artisan sail:install. Подходит как стандартное dev-окружение и onboarding новых разработчиков; для прода не предназначен - там FPM/Octane + nginx/k8s.',
                'code_example' => '# Установка
composer require laravel/sail --dev
php artisan sail:install   # выбрать сервисы интерактивно

# Запуск/остановка
./vendor/bin/sail up -d
./vendor/bin/sail down

# Алиас для удобства - в ~/.zshrc или ~/.bashrc
alias sail="bash vendor/bin/sail"

# Привычные команды через sail
sail artisan migrate
sail artisan tinker
sail composer require spatie/laravel-permission
sail npm run dev
sail test
sail mysql               # клиент в контейнер БД
sail shell               # bash в контейнер app

# Опубликовать docker-compose.yml для правок
sail artisan sail:publish',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Pint и чем он отличается от php-cs-fixer?',
                'answer' => 'Pint - официальный фиксер стиля кода Laravel поверх PHP-CS-Fixer. Идёт с готовыми пресетами (laravel - дефолт, psr12, per, symfony) и нулевой конфигурацией: достаточно vendor/bin/pint. Технически это тот же php-cs-fixer, но с подкрученными под Laravel правилами и удобным CLI: --test (dry-run для CI), --dirty (только изменённые в git файлы), --bail (упасть на первой проблеме). Кастомные правила и исключения - в pint.json в корне проекта. Ставится по умолчанию в Laravel 9+; для старых версий composer require laravel/pint --dev.',
                'code_example' => '# Запустить fixer
vendor/bin/pint

# Только проверка (для CI - вернёт !=0 при ошибках)
vendor/bin/pint --test

# Только файлы, изменённые в git
vendor/bin/pint --dirty

# Конкретный путь
vendor/bin/pint app/Models

# pint.json в корне проекта
{
    "preset": "laravel",
    "rules": {
        "simplified_null_return": true,
        "no_unused_imports": true,
        "ordered_imports": { "sort_algorithm": "alpha" }
    },
    "exclude": ["database/migrations"]
}',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Cashier и какие провайдеры он поддерживает?',
                'answer' => 'Cashier - официальный пакет для подписочного биллинга. Существует две независимых версии под разные платёжные системы: Cashier Stripe (laravel/cashier) и Cashier Paddle (laravel/cashier-paddle). Закрывает подписки, тарифные планы, пробные периоды, купоны, single-charge платежи, инвойсы, обработку вебхуков, прокси-роуты для payment intent (3DS). На модели User подключают трейт Billable, дальше биллинг ведётся выразительным API ($user->newSubscription, $user->subscribed, $user->invoices) вместо ручных вызовов Stripe/Paddle SDK. Webhook controller из коробки обрабатывает все основные события (invoice.paid, customer.subscription.deleted) и обновляет статус подписки в БД.',
                'code_example' => '<?php
// composer require laravel/cashier
// php artisan vendor:publish --tag="cashier-migrations"
// php artisan migrate

use Laravel\\Cashier\\Billable;

class User extends Authenticatable {
    use Billable;
}

// Создать подписку
$user->newSubscription("default", "price_monthly_basic")
    ->trialDays(14)
    ->create($paymentMethodId);

// Проверки
$user->subscribed("default");                 // активна ли
$user->subscription("default")->onTrial();    // в trial-периоде
$user->subscribedToPrice("price_pro", "default");

// Смена тарифа
$user->subscription("default")->swap("price_yearly_pro");

// Отмена
$user->subscription("default")->cancel();     // до конца оплаченного периода
$user->subscription("default")->cancelNow();  // сразу

// Инвойсы
$user->invoices()->each(fn($i) => /* ... */);
return $user->downloadInvoice($invoiceId, ["vendor" => "MyApp"]);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Folio и в чём его особенность маршрутизации?',
                'answer' => 'Folio - пакет page-based роутинга в духе Next.js: маршрут создаётся самим фактом существования Blade-файла в каталоге resources/views/pages. Файл pages/users/[id].blade.php автоматически становится роутом GET /users/{id}, [...slug].blade.php - catch-all. Параметры в квадратных скобках, middleware/name/доменные настройки задаются прямо во фронт-маттере страницы через директивы. Удобен для контентных сайтов и landing-страниц с большим числом простых страниц - не нужно объявлять каждый роут в routes/web.php. Для сложного API/CRUD остаётся классический routes/web.php.',
                'code_example' => '<?php
// composer require laravel/folio
// php artisan folio:install

// resources/views/pages/index.blade.php → GET /
// resources/views/pages/about.blade.php → GET /about
// resources/views/pages/users/[id].blade.php → GET /users/{id}
// resources/views/pages/users/[id]/edit.blade.php → GET /users/{id}/edit
// resources/views/pages/blog/[...slug].blade.php → GET /blog/{slug?...} catch-all

// pages/users/[User].blade.php — Route Model Binding по имени класса
?>
@php
    use function Laravel\\Folio\\{name, middleware};

    name("users.show");
    middleware(["auth", "verified"]);
@endphp

<x-layout>
    <h1>{{ $User->name }}</h1>
    <p>{{ $User->email }}</p>
</x-layout>',
                'code_language' => 'blade',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Envoy и для чего он применяется?',
                'answer' => 'Envoy - простой раннер задач на удалённых серверах через SSH. Задачи описываются в Envoy.blade.php в синтаксисе, похожем на Blade: @servers задаёт хосты, @task - команды для них. Используется для деплоя, миграций, выкладки секретов, обслуживания серверов: envoy run deploy выполнит указанную задачу на всех заданных серверах. По функциям сравним с упрощённым Capistrano или Deployer. Поддерживает интерполяцию задач, передачу аргументов, hipchat/slack-уведомления, story (последовательное выполнение нескольких task-ов). Альтернатива для k8s/lambda - не нужен; для classic VPS-деплоев живёт хорошо.',
                'code_example' => '# composer global require laravel/envoy

# Envoy.blade.php в корне проекта
@servers([\'web\' => [\'deploy@1.2.3.4\', \'deploy@5.6.7.8\']])

@story(\'deploy\')
    pull
    composer
    migrate
    restart-php
@endstory

@task(\'pull\', [\'on\' => \'web\', \'parallel\' => true])
    cd /var/www/app
    git pull origin main
@endtask

@task(\'composer\', [\'on\' => \'web\'])
    cd /var/www/app && composer install --no-dev --optimize-autoloader
@endtask

@task(\'migrate\', [\'on\' => \'web\'])
    cd /var/www/app && php artisan migrate --force
@endtask

@task(\'restart-php\', [\'on\' => \'web\', \'parallel\' => true])
    sudo systemctl reload php8.3-fpm
@endtask

# Запуск
# envoy run deploy
# envoy run migrate',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Prompts и где он используется?',
                'answer' => 'Prompts - пакет красивых интерактивных форм для CLI: text, password, confirm, select, multiselect, search, suggest, spin (long-running task с спиннером), progress (progress bar), form (мультишаговая форма). Используется внутри artisan-команд и инсталлеров пакетов вместо устаревших $this->ask()/$this->choice() (они всё ещё работают, но Prompts красивее). С Laravel 10.17+ ставится по умолчанию и применяется самим фреймворком в make:* командах. Поддерживает валидацию, transform-функции, defaults; на не-TTY окружениях (CI, Docker без -it) автоматически фолбэчится на старые prompts.',
                'code_example' => '<?php
use function Laravel\\Prompts\\{text, password, select, confirm, multiselect, search, spin, progress};

// Простой ввод с валидацией
$name = text(
    label: "Как тебя зовут?",
    placeholder: "Иван",
    required: true,
    validate: fn ($v) => strlen($v) < 2 ? "Минимум 2 символа" : null,
);

// Пароль
$pwd = password(label: "Пароль", required: true);

// Выбор из списка
$db = select(
    label: "Какую БД использовать?",
    options: ["mysql" => "MySQL", "pgsql" => "PostgreSQL", "sqlite" => "SQLite"],
    default: "mysql",
);

// Подтверждение
if (! confirm("Точно удалить?", default: false)) {
    return;
}

// Мульти-выбор
$features = multiselect(
    label: "Какие пакеты ставим?",
    options: ["pint", "larastan", "telescope", "horizon"],
);

// Поиск с автоподсказкой
$user = search(
    label: "Найди юзера",
    options: fn (string $q) => User::where("name", "like", "%{$q}%")->pluck("name", "id")->all(),
);

// Long-running task
$users = spin(fn () => User::all(), "Загружаем пользователей...");

// Progress bar
progress(label: "Импорт", steps: $rows, callback: fn ($row) => importRow($row));',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel IDE Helper и зачем он нужен?',
                'answer' => 'IDE Helper (barryvdh/laravel-ide-helper) - dev-пакет, генерирующий PHPDoc-метаинформацию для фасадов, моделей и контейнерных биндингов. Команды: ide-helper:generate - создаёт _ide_helper.php с PHPDoc для всех фасадов (Cache::get → реальная сигнатура); ide-helper:models - добавляет @property/@method PHPDoc прямо в файлы моделей (или в отдельный _ide_helper_models.php), чтобы IDE понимала магические where{Field}, findOrFail и атрибуты из БД; ide-helper:meta - создаёт .phpstorm.meta.php для PhpStorm, чтобы он понимал app()->make() и резолв из контейнера. Без него PhpStorm/static анализаторы ругаются на "undefined method" у фасадов и Eloquent-магии. Запускается обычно в post-update-cmd composer-скрипта и/или в deploy.',
                'code_example' => '# Установка
composer require --dev barryvdh/laravel-ide-helper

# Команды
php artisan ide-helper:generate     # фасады
php artisan ide-helper:models -N    # модели (в отдельный файл, без правки)
php artisan ide-helper:models -W    # модели (записывает PHPDoc в файл модели)
php artisan ide-helper:meta         # PhpStorm meta

# composer.json - автогенерация после composer update
{
  "scripts": {
    "post-update-cmd": [
      "@php artisan ide-helper:generate",
      "@php artisan ide-helper:meta"
    ]
  }
}

# .gitignore - сгенерированные файлы в git не нужны
_ide_helper.php
_ide_helper_models.php
.phpstorm.meta.php',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Nova и чем она отличается от бесплатных админок (Filament, Backpack)?',
                'answer' => 'Nova - официальная платная админка Laravel: Resource-классы описывают CRUD-страницы, фильтры, lenses (saved views), actions (групповые операции), metrics (KPI-карточки). Стек - Vue.js + Laravel API. Filament - бесплатная open-source альтернатива на TALL-стеке (Tailwind + Alpine + Livewire + Laravel), сейчас самая активная экосистема и плагины. Backpack - бесплатная (Pro-плагины платные) с самой длинной историей и зрелостью, шаблон CoreUI. Nova - выбирают за официальную поддержку и тесную интеграцию с экосистемой (Scout, Sanctum, Horizon, Pulse); Filament - за современный стек и быстрый старт; Backpack - за зрелую функциональность и community-плагины. Цена: Nova - $199/сайт (бессрочная лицензия), Filament/Backpack - бесплатно.',
                'code_example' => '<?php
// Nova Resource - app/Nova/User.php
use Laravel\\Nova\\Resource;
use Laravel\\Nova\\Fields\\{ID, Text, Email, Password, BelongsTo, HasMany};

class User extends Resource {
    public static $model = \\App\\Models\\User::class;
    public static $title = "name";
    public static $search = ["id", "name", "email"];

    public function fields(NovaRequest $request): array {
        return [
            ID::make()->sortable(),
            Text::make("Name")->sortable()->rules("required", "max:255"),
            Email::make("Email")->sortable()->rules("required", "email", "unique:users,email,{{resourceId}}"),
            Password::make("Password")->onlyOnForms()->creationRules("required", "min:8"),
            BelongsTo::make("Team"),
            HasMany::make("Posts"),
        ];
    }

    public function actions(NovaRequest $request): array {
        return [new \\App\\Nova\\Actions\\SuspendUser];
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Какие основные helper-функции Laravel часто используются?',
                'answer' => 'Глобальные функции, доступные везде:

**URL и ассеты:**
- `route(\'users.show\', $id)` — URL по имени маршрута.
- `url(\'/foo\')` — абсолютный URL от `APP_URL`.
- `asset(\'css/app.css\')` — URL статики из `public/`.

**Формы и валидация:**
- `old(\'email\')` — старое значение поля после ошибки валидации.
- `csrf_token()` — CSRF-токен текущей сессии.

**Конфиг и окружение:**
- `config(\'app.name\')` — значение из `config/`.
- `env(\'KEY\')` — переменная окружения. Читать **только внутри `config/`**, иначе после `config:cache` вернёт `null`/default.

**Сервисы:**
- `auth()`, `request()`, `session()`, `redirect()`, `back()`, `abort()`, `response()`, `view()`.

**Утилиты:**
- `now()`, `today()` — `Carbon`.
- `collect([...])` — `Collection`.
- `str(\'...\')` — `Stringable` (цепочки методов над строкой).',
                'code_example' => '// в Blade
<a href="{{ route(\'users.show\', $user) }}">Профиль</a>
<link rel="stylesheet" href="{{ asset(\'css/app.css\') }}">
<input name="email" value="{{ old(\'email\') }}">

// в коде
$url    = url(\'/login\');             // https://app.test/login
$name   = config(\'app.name\');        // "MyApp"
$today  = now();                     // Carbon
$user   = auth()->user();
$email  = request(\'email\');
session([\'cart\' => $items]);
return redirect()->route(\'home\');
abort_if(! $user, 403);',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'laravel.misc',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Какие способы вернуть ответ из контроллера в Laravel?',
                'answer' => 'Что можно вернуть из метода контроллера:

- `view(\'users.show\', [\'user\' => $user])` — HTML-страница из Blade.
- `redirect(\'/login\')` или `redirect()->route(\'home\')` — редирект (с `->with()` или `->withErrors()`).
- `back()` — назад на предыдущую страницу.
- `response()->json([\'ok\' => true], 201)` — JSON с HTTP-кодом.
- `response()->download($path)` — файл на скачивание.
- `response(\'Hello\', 200)->header(\'X-Custom\', \'v\')` — произвольный текст с заголовками.
- `abort(404, \'Не найдено\')` — прервать с ошибкой.

**Авто-сериализация:** если вернуть массив или Eloquent-модель — Laravel сам отдаст JSON.',
                'code_example' => 'public function show(int $id)
{
    $user = User::findOrFail($id);

    return view(\'users.show\', [\'user\' => $user]);     // HTML
    return response()->json([\'user\' => $user], 200);   // JSON
    return $user;                                      // тоже JSON (auto)
    return redirect()->route(\'users.index\')
        ->with(\'success\', \'Готово\');                  // redirect + flash
    return response()->download(storage_path("invoices/{$id}.pdf"));
    return back()->withInput()->withErrors([\'email\' => \'занят\']);
    abort(404, \'Не найдено\');
}',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'laravel.misc',
            ],
        ];
    }
}
