<?php

namespace Database\Seeders\Data\Categories\Laravel;

class Controllers
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Laravel',
                'question' => 'Как создать контроллер в Laravel?',
                'answer' => 'Контроллеры создаются через artisan-команду **`make:controller`**.

Варианты:

- **`make:controller UserController`** — пустой контроллер.
- **`--resource`** — заготовка с 7 CRUD-методами (`index`, `create`, `store`, `show`, `edit`, `update`, `destroy`).
- **`--api`** — то же, но без `create` и `edit` (формы не нужны для API).
- **`--invokable`** — один метод `__invoke()` (single action).
- **`--model=Post`** — добавит type-hint модели в методы для route model binding.

Файл появится в `app/Http/Controllers`. Имя — в **единственном числе** + `Controller`: `PostController`.',
                'code_example' => 'php artisan make:controller UserController
php artisan make:controller UserController --resource
php artisan make:controller UserController --invokable
php artisan make:controller Api/UserController --api',
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'laravel.controllers',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое resource-контроллер?',
                'answer' => '**Resource-контроллер** — контроллер с 7 стандартными методами для CRUD:

- **`index`** — `GET /posts` — список.
- **`create`** — `GET /posts/create` — форма создания.
- **`store`** — `POST /posts` — сохранение.
- **`show`** — `GET /posts/{post}` — просмотр одной.
- **`edit`** — `GET /posts/{post}/edit` — форма редактирования.
- **`update`** — `PUT/PATCH /posts/{post}` — обновление.
- **`destroy`** — `DELETE /posts/{post}` — удаление.

Подключается одной строкой **`Route::resource(\'posts\', PostController::class)`** — Laravel сам зарегистрирует все 7 маршрутов с правильными HTTP-методами и именами (`posts.index`, `posts.show` и т.д.).

Для API используют **`apiResource`** — без `create` и `edit`.',
                'code_example' => 'Route::resource(\'posts\', PostController::class);
// для API без create/edit
Route::apiResource(\'posts\', PostController::class);
// несколько ресурсов
Route::apiResources([
    \'posts\' => PostController::class,
    \'users\' => UserController::class,
]);',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.controllers',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое single action (invokable) контроллер?',
                'answer' => '**Single action (invokable) контроллер** — класс с единственным магическим методом `__invoke()`. Создаётся через `php artisan make:controller ShowProfile --invokable`.

Когда брать:

- Контроллер делает **ровно одно действие** — нет смысла в `index`/`show`/`store`.
- Хочется, чтобы имя класса читалось как **глагол** (`PublishPost`, `SendInvoice`, `GenerateReport`).
- Сложная одна операция — не разбухает controller с разнородными методами.

В роуте указывается просто класс — без `@method`: **`Route::get(\'/profile/{id}\', ShowProfile::class)`**.',
                'code_example' => 'class ShowProfile {
    public function __invoke($id) {
        return view(\'profile\', [\'user\' => User::findOrFail($id)]);
    }
}

Route::get(\'/profile/{id}\', ShowProfile::class);',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.controllers',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как работает dependency injection в контроллерах?',
                'answer' => 'Laravel автоматически разрешает зависимости в конструкторе и методах контроллеров через Service Container. Достаточно указать тип параметра, и контейнер подставит нужный объект. Это работает в __construct, методах действий и для FormRequest.',
                'code_example' => 'class UserController {
    public function __construct(private UserService $service) {}

    public function store(StoreUserRequest $request, UserRepository $repo) {
        return $repo->create($request->validated());
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.controllers',
            ],
        ];
    }
}
