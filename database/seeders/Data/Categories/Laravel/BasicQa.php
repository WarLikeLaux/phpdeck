<?php

namespace Database\Seeders\Data\Categories\Laravel;

class BasicQa
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Laravel',
                'question' => 'Чем отличается Service Provider от Middleware?',
                'answer' => 'Это разные слои приложения:

- **Service Provider** — точка инициализации контейнера. Запускается **один раз** при бутстрапе приложения (до того, как пришёл хоть один запрос). Регистрирует биндинги, синглтоны, события, кастомные Blade-директивы.
- **Middleware** — слой HTTP-конвейера. Работает **на каждый запрос**: смотрит/меняет `Request` до контроллера и `Response` после.

Ключевая разница:

- Provider настраивает **что есть в приложении**.
- Middleware решает **пускать ли запрос дальше и как его модифицировать**.',
                'code_example' => '// Service Provider — один раз при старте
class AppServiceProvider extends ServiceProvider
{
    public function register(): void {
        $this->app->singleton(PaymentGateway::class, StripeGateway::class);
    }
}

// Middleware — на каждый запрос
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response {
        if (! $request->user()?->is_active) {
            return redirect(\'/banned\');
        }
        return $next($request);
    }
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Чем hasOne отличается от belongsTo?',
                'answer' => 'Это две стороны одной связи. Различаются тем, **где лежит внешний ключ** (FK).

- **`hasOne`** — со стороны **родителя**. FK на дочерней таблице.
- **`belongsTo`** — со стороны **дочерней**. FK у себя.

Пример: у `User` один `Phone`. Таблица `phones` хранит `user_id`.

- `User::phone()` → `$this->hasOne(Phone::class)`.
- `Phone::user()` → `$this->belongsTo(User::class)`.

Запоминалка: **«у кого FK — тот `belongsTo`»**.',
                'code_example' => 'class User extends Model {
    public function phone() {
        return $this->hasOne(Phone::class);  // ждёт user_id в phones
    }
}

class Phone extends Model {
    public function user() {
        return $this->belongsTo(User::class); // у себя есть user_id
    }
}

// Использование
$phone = $user->phone;       // Phone|null
$owner = $phone->user;       // User',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Чем отличаются queue jobs от events?',
                'answer' => 'Это разные сущности с разной семантикой:

- **Job** — **единица фоновой работы**. Ставится в очередь через `dispatch()`, выполняется воркером `queue:work`. Один job знает, **что** делать.
- **Event** — **объект-сигнал** «что-то произошло». Может иметь **N подписанных listener-ов**. Знает только факт, не действие.

По умолчанию listener выполняется **синхронно** в том же запросе. Для асинхронности listener реализует **`ShouldQueue`** — тогда сам listener становится job-ом и уходит в очередь.

Когда что брать:

- **Job** — нужно одно конкретное действие (отправить отчёт, сжать видео).
- **Event** — на одно действие повесить несколько реакций (юзер зарегался → отправить welcome + создать профиль + начислить бонус).',
                'code_example' => '// Job — одно действие
class SendReport implements ShouldQueue {
    public function handle(): void { /* ... */ }
}
SendReport::dispatch($user);

// Event + несколько listener-ов
class UserRegistered { public function __construct(public User $user) {} }

class SendWelcomeEmail implements ShouldQueue {
    public function handle(UserRegistered $e): void { /* ... */ }
}
class CreateUserProfile { /* sync */
    public function handle(UserRegistered $e): void { /* ... */ }
}

event(new UserRegistered($user)); // оба listener-а сработают',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Чем отличаются session, cookie и cache в Laravel?',
                'answer' => 'Три разных хранилища:

- **Cookie** — лежит **у клиента** (браузер). Маленький payload, шлётся в каждом запросе. Подходит для preferences, language, баннеров.
- **Session** — состояние **на сервере**, привязанное к конкретному пользователю. Идентифицируется cookie `laravel_session` с id сессии. Хранится в `file`/`redis`/`database` (см. `config/session.php`). Туда кладут flash-сообщения, корзину, временные ошибки валидации.
- **Cache** — общее **key-value-хранилище** без привязки к пользователю. Для кеширования тяжёлых запросов, ответов API, рассчитанных данных. Драйверы: `redis`, `memcached`, `file`, `database`.

Запоминалка: cookie — **у клиента**, session — **у сервера про этого юзера**, cache — **у сервера для всех**.',
                'code_example' => '// Cookie
return response(\'ok\')->cookie(\'lang\', \'ru\', 60 * 24 * 30);
$lang = $request->cookie(\'lang\');

// Session
session([\'cart\' => $cart]);
$cart = session(\'cart\');
session()->flash(\'success\', \'Сохранено\'); // живёт один запрос

// Cache
Cache::put(\'stats\', $heavy, 600);
$stats = Cache::remember(\'stats\', 600, fn() => $service->compute());',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое контроллер в Laravel?',
                'answer' => '**Контроллер** — класс, который обрабатывает HTTP-запрос: достаёт данные через модель, применяет бизнес-логику и возвращает ответ.

- Лежит в `app/Http/Controllers`, наследует `Controller`.
- Метод получает `Request`, возвращает `Response`, `view` или JSON.
- Связывается с URL через `routes/web.php` или `routes/api.php`.
- Создаётся командой `php artisan make:controller UserController`.',
                'code_example' => 'use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Http\Request;

// routes/web.php
Route::get(\'/users/{id}\', [UserController::class, \'show\']);

// app/Http/Controllers/UserController.php
class UserController extends Controller
{
    public function show(int $id)
    {
        $user = User::findOrFail($id);

        return view(\'users.show\', [\'user\' => $user]);
    }
}',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое модель в Laravel?',
                'answer' => '**Модель** — класс, представляющий одну таблицу в БД. Один объект = одна строка.

- Лежит в `app/Models`, наследует `Illuminate\\Database\\Eloquent\\Model`.
- Имя в **единственном числе**, таблица — во множественном: `User` → `users`.
- Через модель идёт CRUD: `find`, `save`, `update`, `delete`, `where`.
- Создаётся командой `php artisan make:model Post` (с `-m` ещё и миграция).',
                'code_example' => 'namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = [\'name\', \'email\'];
}

// Использование
$user = User::find(5);                       // найти по id
$user->name = \'Anna\';
$user->save();                               // UPDATE

User::create([\'name\' => \'Bob\', \'email\' => \'b@b.c\']); // INSERT
User::where(\'active\', true)->get();          // SELECT с условием',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Какие базовые методы есть у Eloquent для чтения?',
                'answer' => 'Базовые методы выборки на модели:

- `User::all()` — все записи (`Collection`).
- `User::find(5)` — по первичному ключу, вернёт **модель или `null`**.
- `User::findOrFail(5)` — то же, но бросит `ModelNotFoundException` → автоматически HTTP **404**.
- `User::first()` / `User::firstOrFail()` — первая запись.
- `User::where(\'active\', true)->get()` — с условием.
- `User::count()` — количество (без загрузки моделей).
- `User::pluck(\'email\')` — массив значений одной колонки.',
                'code_example' => '$all     = User::all();                       // Collection всех юзеров
$user    = User::find(5);                     // User|null
$user    = User::findOrFail(5);               // 404 если не найден
$first   = User::where(\'active\', true)->first();
$active  = User::where(\'active\', true)->get();  // Collection
$total   = User::count();                     // int
$emails  = User::pluck(\'email\');               // [\'a@b.c\', \'c@d.e\', ...]',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как сделать валидацию в Laravel простыми словами?',
                'answer' => 'Самый простой способ — **`$request->validate([...])`** прямо в контроллере.

Что делает Laravel под капотом, если данные не подходят:

- **Для обычной формы** — редиректит назад со старыми input-ами и `$errors` в сессии (доступны в Blade через `@error` / `$errors->all()`).
- **Для JSON/API/Inertia** — возвращает **`422 Unprocessable Entity`** с массивом ошибок.

Метод `validate()` возвращает **массив только провалидированных полей** — удобно сразу пихать в `create()`.

Для сложной логики — выносят правила в **`FormRequest`** через `php artisan make:request StoreUserRequest`. У `FormRequest` есть `rules()`, `authorize()`, `prepareForValidation()`, `messages()`.',
                'code_example' => '// Простой способ в контроллере
public function store(Request $request)
{
    $data = $request->validate([
        \'email\'    => \'required|email|unique:users,email\',
        \'password\' => \'required|min:8|confirmed\',
        \'age\'      => \'nullable|integer|min:18\',
    ]);

    return User::create($data);
}

// FormRequest - для сложных сценариев
class StoreUserRequest extends FormRequest {
    public function rules(): array {
        return [
            \'email\'    => [\'required\', \'email\', Rule::unique(\'users\')],
            \'password\' => [\'required\', \'min:8\', \'confirmed\'],
        ];
    }
}

public function store(StoreUserRequest $request)
{
    return User::create($request->validated());
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое .env в Laravel и зачем он?',
                'answer' => 'Файл с **переменными окружения** для конкретного окружения (`dev`/`staging`/`prod`): креды БД, ключи API, флаг `APP_DEBUG`, `APP_KEY`, адреса сервисов.

Главные правила:

- **`.env` не коммитится** (в `.gitignore`), у каждого свой.
- **`.env.example` коммитится** — это шаблон с пустыми/дефолтными значениями.
- Читай переменные через **`config()`-обёртки**, не `env()` напрямую: в проде `config:cache` кеширует конфиги, и прямой `env()` начнёт возвращать `null`.

Поток для нового разработчика: `git clone` → `cp .env.example .env` → `php artisan key:generate` → заполнить креды → `php artisan migrate`.',
                'code_example' => '# .env (не коммитится)
APP_ENV=local
APP_DEBUG=true
APP_KEY=base64:...
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=flashcards
DB_USERNAME=root
DB_PASSWORD=secret

// в config/database.php — env() ОК тут
\'mysql\' => [
    \'host\'     => env(\'DB_HOST\', \'127.0.0.1\'),
    \'database\' => env(\'DB_DATABASE\'),
],

// в коде приложения — config(), а не env()
$host = config(\'database.connections.mysql.host\'); // правильно',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как связаны route, controller, model и view в Laravel?',
                'answer' => 'Это четыре главных слоя обычного запроса в Laravel:

1. **Route** (`routes/web.php`) — принимает URL и направляет в нужный метод контроллера.
2. **Controller** (`app/Http/Controllers`) — оркестратор: дёргает модель и решает, что вернуть.
3. **Model** (`app/Models`, Eloquent) — работа с данными в БД.
4. **View** (`resources/views`, Blade) — HTML-шаблон.

Поток: запрос → роут → контроллер → модель → view (или JSON).',
                'code_example' => '// routes/web.php
Route::get(\'/users/{id}\', [UserController::class, \'show\']);

// app/Http/Controllers/UserController.php
class UserController extends Controller {
    public function show(int $id) {
        $user = User::findOrFail($id); // Model
        return view(\'users.show\', [\'user\' => $user]); // View
    }
}',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое route() helper и зачем нужны имена маршрутов?',
                'answer' => '**`route(\'users.show\', [\'user\' => 5])`** генерирует URL по имени маршрута.

Зачем имена:

- При изменении пути в `routes/web.php` все ссылки **автоматически обновятся** — не надо искать `/users/...` по проекту.
- Удобно делать **редиректы** и сравнивать текущий маршрут.
- Стандарт-стайл в Laravel: всё именованное.

Имя задаётся через `->name(\'users.show\')`. Конвенция — `<resource>.<action>`: `posts.index`, `posts.show`, `posts.store`. У `Route::resource()` имена даются автоматически.',
                'code_example' => 'Route::get(\'/users/{user}\', [UserController::class, \'show\'])->name(\'users.show\');

// в Blade
<a href="{{ route(\'users.show\', $user) }}">Профиль</a>

// в контроллере
return redirect()->route(\'users.show\', $user);',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Какие способы редиректа в Laravel?',
                'answer' => 'Способы редиректа из контроллера:

- `redirect(\'/login\')` — по URL.
- `redirect()->route(\'home\')` — по имени маршрута (предпочтительно).
- `back()` или `redirect()->back()` — на предыдущую страницу.
- `redirect()->action([Ctrl::class, \'method\'])` — на метод контроллера.

Дополнительно через цепочку:

- `->with(\'success\', \'Готово\')` — flash-сообщение в сессию (живёт один запрос).
- `->withInput()` — сохранить заполненные поля формы.
- `->withErrors($errors)` — передать ошибки во view (`$errors`).',
                'code_example' => 'return redirect(\'/login\');
return redirect()->route(\'profile\', $user);
return back()->with(\'success\', \'Сохранено\');
return redirect()->route(\'login\')->withInput()->withErrors([\'email\' => \'Неверный email\']);',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как создать новую запись через Eloquent простыми словами?',
                'answer' => 'Два основных способа:

1. **Через `create()`** — массово из массива: `User::create([...])`. Требует `protected $fillable` на модели — список разрешённых полей. Возвращает уже сохранённую модель.
2. **Через `new` + `save()`** — пошагово: создать объект, присвоить свойства, вызвать `save()`. Не требует `$fillable`.

Обновление по аналогии — `update([...])` или `save()` после изменения свойств.',
                'code_example' => '// Способ 1: create
$user = User::create([\'name\' => \'Anna\', \'email\' => \'a@b.c\']);

// Способ 2: new + save
$user = new User;
$user->name = \'Anna\';
$user->email = \'a@b.c\';
$user->save();

// Обновление
$user->update([\'name\' => \'Bob\']);

// или
$user->name = \'Bob\';
$user->save();',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое middleware простыми словами?',
                'answer' => '**Middleware** — прослойка, через которую проходит каждый HTTP-запрос **до** контроллера (и ответ — после).

Типичные задачи:

- `auth` — пускать только залогиненных, иначе редирект на `/login`.
- `verified` — только с подтверждённым email.
- `throttle` — ограничить число запросов в минуту.
- CSRF-проверка для POST/PUT/DELETE форм.
- Логирование, добавление заголовков, локализация.

Если middleware решает «нельзя» — оно возвращает свой ответ и контроллер **не вызовется**. Готовое middleware вешается на роут через `->middleware(\'auth\')` или на группу.',
                'code_example' => '// Назначить готовое middleware на роут
Route::get(\'/profile\', [ProfileController::class, \'show\'])->middleware(\'auth\');

// Несколько middleware и группа
Route::middleware([\'auth\', \'verified\'])->group(function () {
    Route::get(\'/dashboard\', [DashboardController::class, \'index\']);
});

// Своё middleware: php artisan make:middleware EnsureUserIsActive
public function handle(Request $request, Closure $next): Response
{
    if (! $request->user()?->is_active) {
        return redirect(\'/banned\');
    }
    return $next($request); // пропустить дальше в контроллер
}',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'laravel.basic_qa',
            ],
        ];
    }
}
