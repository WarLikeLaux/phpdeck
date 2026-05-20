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
                'answer' => 'Service Provider вызывается до обработки запроса (регистрирует биндинги и инициализирует сервисы). Middleware фильтрует HTTP-запросы по конвейеру до и после контроллера.',
                'difficulty' => 2,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Чем hasOne отличается от belongsTo?',
                'answer' => 'hasOne - обратная сторона связи "один-к-одному" со стороны родителя (FK на дочерней). belongsTo - со стороны дочерней (FK у себя).',
                'difficulty' => 2,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Чем отличаются queue jobs от events?',
                'answer' => 'Job - единица фоновой работы, ставится в очередь и выполняется воркером. Event - объект, описывающий факт/сигнал; на него подписаны N listener-ов. По умолчанию listener выполняется СИНХРОННО в том же запросе; для асинхронности listener должен реализовать ShouldQueue - тогда сам listener становится job-ом и уходит в очередь.',
                'difficulty' => 2,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Чем отличаются session, cookie и cache в Laravel?',
                'answer' => 'Cookie - данные у клиента. Session - серверное состояние пользователя, обычно идентифицируется cookie. Cache - общее key-value-хранилище без привязки к пользователю.',
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
                'answer' => 'Простой способ — $request->validate([...]) прямо в контроллере. Если данные не подходят, Laravel автоматически редиректит назад со старыми input-ами и errors в сессии (для JSON/API/Inertia — 422 JSON). Метод возвращает массив только провалидированных полей. Для сложной логики — вынести правила в FormRequest (отдельный класс с методами rules(), authorize(), prepareForValidation()).',
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
                'answer' => 'Файл с переменными окружения для конкретного окружения (dev/staging/prod): креды БД, ключи API, debug. Не коммитится (.env.example — шаблон, коммитится). Читай через config()-обёртки, не env() напрямую: в проде config кэшируется.',
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
                'answer' => 'route(\'users.show\', [\'user\' => 5]) генерирует URL по имени маршрута. Если в коде используется route(\'home\'), а не URL руками, то при изменении пути в routes/web.php все ссылки автоматически обновятся. Имя маршрута задаётся через ->name(\'users.show\'). Использовать имена — стандарт в Laravel.',
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
