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
                'answer' => 'Класс с методами, обрабатывающий HTTP-запросы. Лежит в app/Http/Controllers. Метод получает Request, обращается к моделям/сервисам и возвращает Response, view или JSON. Связывается с URL через routes.',
                'difficulty' => 1,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое модель в Laravel?',
                'answer' => 'Класс, представляющий одну таблицу в БД. Лежит в app/Models, наследует Eloquent\\Model, имя в единственном числе (User → таблица users). Через модель — CRUD: User::find(5), $user->save(), User::where(...)->get().',
                'difficulty' => 1,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Какие базовые методы есть у Eloquent для чтения?',
                'answer' => 'User::all() — все. User::find(5) — по PK (модель или null). User::findOrFail(5) — то же, но бросит 404. User::where(\'active\', true)->get() — с условием. User::first() — первая. User::count() — сколько.',
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
                'answer' => 'Это четыре главных слоя обычного запроса. Route принимает URL и направляет в Controller. Controller — оркестратор: дёргает Model для работы с БД и возвращает результат как View (HTML) или JSON. Model — данные (Eloquent). View — шаблон Blade. Маршрут указывает на метод контроллера, контроллер вызывает модель, потом отдаёт данные во view.',
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
                'answer' => 'redirect(\'/login\') — по URL. redirect()->route(\'home\') — по имени маршрута. redirect()->back() или back() — назад. redirect()->action([Ctrl::class, \'method\']) — на метод контроллера. С данными во flash-сессии: ->with(\'success\', \'Готово\'). С ошибками: ->withErrors($errors)->withInput().',
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
                'answer' => 'Два пути. Через create() — массово: User::create([\'name\' => \'A\', \'email\' => \'a@b.c\']) — требует $fillable на модели. Через new + save() — пошагово: $u = new User; $u->name = \'A\'; $u->email = \'a@b.c\'; $u->save(). create() возвращает уже сохранённую модель, удобно для одной строки кода.',
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
                'answer' => 'Прослойка, через которую проходит каждый HTTP-запрос ДО контроллера. Стандартные задачи: проверить, что юзер залогинен (\'auth\'), проверить CSRF, ограничить число запросов (\'throttle\'), залогировать запрос. Если middleware что-то не нравится — оно отклоняет запрос (например, редирект на /login) и контроллер вообще не вызовется. Готовое middleware вешается на роут через ->middleware(\'auth\') или на группу.',
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
