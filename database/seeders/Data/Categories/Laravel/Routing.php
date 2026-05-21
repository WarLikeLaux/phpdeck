<?php

namespace Database\Seeders\Data\Categories\Laravel;

class Routing
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Laravel',
                'question' => 'Что такое маршрут (route) в Laravel?',
                'answer' => '**Маршрут (route)** — правило, которое связывает **URL + HTTP-метод** с конкретным действием (методом контроллера или замыканием).

- Лежат в `routes/web.php` (для браузера, с сессиями и CSRF) и `routes/api.php` (для API, stateless).
- Когда приходит запрос — Laravel ищет подходящий маршрут и вызывает его обработчик.
- Объявляются через фасад `Route`: `Route::get`, `Route::post`, `Route::put`, `Route::delete`, `Route::match`, `Route::any`.
- Для CRUD есть `Route::resource(\'posts\', PostController::class)` — сразу 7 стандартных роутов.',
                'code_example' => 'Route::get(\'/users\', [UserController::class, \'index\']);
Route::post(\'/users\', [UserController::class, \'store\']);
Route::put(\'/users/{id}\', [UserController::class, \'update\']);
Route::delete(\'/users/{id}\', [UserController::class, \'destroy\']);',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'laravel.routing',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как объявить параметры маршрута, в том числе опциональные и с regex-ограничениями?',
                'answer' => 'Параметры — в **фигурных скобках**: `{id}`.

Варианты:

- **Обычный**: `{id}` — обязательный.
- **Опциональный**: `{name?}` — нужно дать значение по умолчанию в сигнатуре метода.

Ограничения regex через **`->where(\'имя\', \'regex\')`** или массивом для нескольких параметров.

Готовые helper-методы:

- **`whereNumber(\'id\')`** — только цифры.
- **`whereAlpha(\'slug\')`** — только буквы.
- **`whereAlphaNumeric(...)`** — буквы и цифры.
- **`whereUuid(\'user\')`** / **`whereUlid(\'user\')`** — UUID/ULID.
- **`whereIn(\'lang\', [\'ru\', \'en\'])`** — список значений.

Если параметр не подходит под regex — Laravel вернёт **404**.',
                'code_example' => 'Route::get(\'/user/{id}\', fn($id) => $id)
    ->where(\'id\', \'[0-9]+\');

Route::get(\'/user/{name?}\', fn($name = \'guest\') => $name);

Route::get(\'/post/{slug}\', [PostController::class, \'show\'])
    ->whereAlpha(\'slug\');',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.routing',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое именованные маршруты (named routes) и зачем они нужны?',
                'answer' => '**Именованный маршрут** — маршрут с уникальным **именем**, по которому можно:

- Генерировать URL через хелпер **`route(\'имя\', [параметры])`**.
- Редиректить через **`redirect()->route(\'имя\')`**.
- Использовать в Blade: `<a href="{{ route(\'profile\') }}">`.

Главный плюс: если URL изменится, не нужно искать и менять ссылки по всему коду — **имя остаётся тем же**.

Имя задаётся через **`->name(\'profile\')`** в конце цепочки маршрута.

Конвенция: **`<resource>.<action>`** — `posts.index`, `posts.show`, `posts.store`. У `Route::resource()` имена даются автоматически.

Дополнительно:

- В группе с `->name(\'admin.\')` имена внутри получат префикс: `admin.users.index`.
- В контроллере проверить текущий маршрут: `$request->routeIs(\'admin.*\')`.',
                'code_example' => 'Route::get(\'/user/profile\', [ProfileController::class, \'show\'])
    ->name(\'profile\');

$url = route(\'profile\'); // /user/profile
return redirect()->route(\'profile\');',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.routing',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое route groups (группы маршрутов)?',
                'answer' => '**Route group** — способ применить общие настройки сразу к **нескольким маршрутам**, чтобы не дублировать. Группы можно вкладывать.

Что можно вынести в группу:

- **`middleware([\'auth\', \'verified\'])`** — несколько middleware.
- **`prefix(\'admin\')`** — общий URL-префикс: маршрут `/users` станет `/admin/users`.
- **`name(\'admin.\')`** — префикс имени: имя `users.index` станет `admin.users.index`.
- **`controller(UserController::class)`** — общий контроллер, в маршрутах указываем только метод.
- **`domain(\'admin.example.com\')`** — субдомен.
- **`as(\'admin.\')`** — алиас `->name()`.

Вместо:

```
Route::get(\'/admin/users\', ...)->middleware(\'auth\')->name(\'admin.users.index\');
Route::get(\'/admin/posts\', ...)->middleware(\'auth\')->name(\'admin.posts.index\');
```

Пишем группу с одним описанием атрибутов.',
                'code_example' => 'Route::middleware([\'auth\'])->prefix(\'admin\')->name(\'admin.\')->group(function () {
    Route::get(\'/users\', [UserController::class, \'index\'])->name(\'users.index\');
    Route::get(\'/posts\', [PostController::class, \'index\'])->name(\'posts.index\');
});',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.routing',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что делает route caching и какие у него ограничения?',
                'answer' => '**`php artisan route:cache`** компилирует все роут-файлы (`web`, `api`, `console`, `channels`) **в один сериализованный PHP-файл** (`bootstrap/cache/routes-v7.php`) — Laravel при старте читает его вместо парсинга роутов на каждый запрос.

**Когда даёт ощутимый выигрыш:** проекты с **сотнями маршрутов**, особенно при холодном опкеше — экономит десятки мс на bootstrap.

**Что важно знать:**

- Под капотом `Route::prepareForSerialization()` пакует роуты, **включая `Closure`-обработчики**, через `laravel/serializable-closure` (с **L8.62+**). До этого был миф «route:cache несовместим с closure-роутами» — он устарел.
- Команда **исключительно для деплоя**. Любые правки в `routes/*` после `route:cache` **не подхватываются** до `route:clear`.
- Связка прод-команд: **`php artisan optimize`** = `config:cache` + `route:cache` + `view:cache` + `event:cache`.

**Подводные камни:**

- `config:cache` **замораживает `env()`** — после кеша `.env` не читается; **`env()` в коде** видит только OS-уровень или дефолт. Правильно: использовать `config(\'...\')`, а `env()` — только в `config/*.php`.
- `Closure`, замыкающий `$this` или несериализуемый объект (`PDO`, file handle), всё ещё **ломает кеш** — serializable-closure не магия.',
                'code_example' => 'php artisan route:cache
php artisan route:clear
php artisan route:list',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'laravel.routing',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое route model binding?',
                'answer' => '**Route Model Binding** — автоматическая подстановка модели в контроллер **по параметру маршрута**. Вместо `User::findOrFail($id)` в каждом методе — type-hint, и Laravel сам находит запись или возвращает **404**.

**Виды биндинга:**

- **Implicit** (по умолчанию) — Laravel смотрит на **type-hint** параметра метода и резолвит по primary key (или `getRouteKeyName()`). Имя параметра в URL и в сигнатуре должны **совпадать**.
- **По другому полю** — `users/{user:slug}` или переопределение `getRouteKeyName()` на модели.
- **Enum binding** — type-hint `BackedEnum` в сигнатуре автоматически валидирует значение и кастит (404 на невалидное).
- **Explicit** — `Route::bind(\'user\', fn ($v) => ...)` — кастомная логика поиска.

**Дополнительные приёмы:**

- **Scoped bindings** — `Route::scopeBindings()` или `users/{user}/posts/{post:slug}`: Laravel ищет `Post` **через отношение** `$user->posts()` — гарантирует, что `post` действительно принадлежит `user`.
- **`->withTrashed()`** на роуте — позволяет резолвить мягко удалённые модели.
- **`Model::resolveRouteBindingUsing(...)`** — кастомизация для конкретной модели на уровне сервис-провайдера.',
                'code_example' => '// Implicit
Route::get(\'/users/{user}\', function (User $user) {
    return $user; // автоматически найдено
});

// По другому полю
Route::get(\'/posts/{post:slug}\', fn(Post $post) => $post);

// Explicit
Route::bind(\'user\', fn($value) => User::where(\'username\', $value)->firstOrFail());',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.routing',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Какие ограничения у route:cache в современном Laravel?',
                'answer' => '**Распространённое заблуждение** — «`route:cache` не работает с `Closure`-роутами». Это было верно **до L8.62**; сейчас `Closure` сериализуются через **`laravel/serializable-closure`** (с L9.0 полностью вытеснил `opis/closure`), и `route:cache` их корректно кеширует.

**Реальные ограничения:**

- **Только для деплоя.** После `route:cache` правки в `routes/*` **не видны** до `route:clear` — локально не запускать.
- **`config:cache` замораживает `env()`** — после кеша `.env` не парсится, `env()` в коде видит только OS-уровень (Docker `-e`, systemd `Environment=`) или дефолт. Правило: использовать `env()` **только в `config/*.php`**, в приложении — `config(\'...\')`.
- **Несериализуемые closure** — те, что замыкают `$this`, `PDO`, file handle, открытое соединение — всё ещё **ломают кеш**.
- **Несуществующие классы** — если роут ссылается на `[NotExisting::class, \'foo\']` и `composer dump-autoload` не видит класс, кеш упадёт.

**Стандартная связка прод-команд:**

| Команда | Что кеширует |
| --- | --- |
| `config:cache` | Слитый `config/*.php` |
| `route:cache` | Все `routes/*.php` |
| `view:cache` | Скомпилированные Blade |
| `event:cache` | Карту слушателей/событий |
| `optimize` | **Всё перечисленное за один шаг** |',
                'code_example' => '<?php
// оба варианта корректны и кешируются
Route::get("/", function () { return "hi"; });
Route::get("/", [HomeController::class, "index"]);

// деплой:
// composer install --no-dev --optimize-autoloader
// php artisan optimize  // = config:cache + route:cache + view:cache + event:cache

// разработка: НЕ запускайте route:cache локально - правки не подхватятся',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.routing',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое model binding и как сделать кастомное связывание по slug?',
                'answer' => '**Implicit binding** ловит type-hint модели в методе контроллера и резолвит запись **по primary key** из URL-параметра. Для нестандартного поля (например, `slug`) — три способа, по возрастанию гибкости:

**1. `getRouteKeyName()` на модели — глобально для всех роутов:**

```php
class Post extends Model {
    public function getRouteKeyName(): string { return \'slug\'; }
}
Route::get(\'/posts/{post}\', fn (Post $p) => $p);
```

**2. Параметр прямо в роуте — точечно:**

```php
Route::get(\'/posts/{post:slug}\', fn (Post $p) => $p);
```

**3. Explicit `Route::bind()` — кастомная логика:**

```php
Route::bind(\'post\', fn ($v) => Post::where(\'slug\', $v)
    ->where(\'published\', true)
    ->firstOrFail());
```

**Полезные расширения:**

- **`scopeBindings()`** — `users/{user}/posts/{post:slug}` с `->scopeBindings()` проверит, что `post` принадлежит `user` (ищет через `$user->posts()`). Защита от **IDOR**.
- **`->withTrashed()`** на роуте — позволит резолвить мягко удалённые модели.
- **`resolveRouteBindingUsing(...)`** в `boot()` сервис-провайдера — кастомизация для конкретной модели глобально.

**Подводный камень:** `getRouteKeyName()` влияет **и на `route(\'name\', $model)`** — URL-генератор тоже начнёт подставлять `slug` вместо `id`.',
                'code_example' => '// Вариант 1: getRouteKeyName в модели
class Post extends Model {
    public function getRouteKeyName(): string { return \'slug\'; }
}
Route::get(\'/posts/{post}\', fn(Post $p) => $p);

// Вариант 2: указать поле в роуте
Route::get(\'/posts/{post:slug}\', fn(Post $p) => $p);

// Вариант 3: explicit bind с кастомной логикой
Route::bind(\'post\', fn($value) =>
    Post::where(\'slug\', $value)->where(\'published\', true)->firstOrFail()
);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.routing',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Signed URLs в Laravel и как они защищены от подделки?',
                'answer' => '**Signed URLs** — механизм Laravel для генерации URL **с криптографической подписью**, защищающей от подделки параметров.

**Как устроено:**

- `URL::signedRoute(\'unsubscribe\', [\'user\' => 42])` создаёт ссылку вида `/unsubscribe/42?signature=eyJ...`.
- Подпись — **HMAC-SHA256** от полного URL (хост + путь + query), с **`APP_KEY`** как секретом.
- Любая подмена параметра (`user=42` → `user=43`) меняет хеш — middleware **`signed`** вернёт **403**.

**Срок жизни:**

- `URL::temporarySignedRoute(\'download\', now()->addHour(), [\'file\' => $id])` — в подпись попадает `expires` (timestamp).
- После истечения ссылка невалидна **даже с верной подписью**.

**Где применять:**

- Magic-link логин.
- Подтверждение email / отписка одним кликом.
- Одноразовые ссылки на скачивание приватных файлов.
- (Для **password reset** Laravel использует **свой токен в БД**, а не signed URL — у него своя ротация.)

**Подводные камни:**

- **Не защищает от replay** — ссылку можно использовать **многократно** до истечения. Нужна разовость → добавь `nonce` в параметры и **храни использованные** в Redis с TTL.
- **URL должен совпадать дословно.** За прокси с rewriting (`X-Forwarded-Host`, `X-Forwarded-Proto`) включай `TrustProxies` middleware и держи `APP_URL` в `.env` точным — иначе сервер считает подпись от другого хоста.
- **UTM-метки** ломают подпись → используй `->hasValidSignatureWhileIgnoring([...])` или middleware `signed:relative`.',
                'code_example' => '<?php
use Illuminate\\Support\\Facades\\URL;

// Генерация подписанного URL без срока истечения
$url = URL::signedRoute("unsubscribe", ["user" => $user->id]);
// https://app.test/unsubscribe/42?signature=eyJ...

// С истечением (1 час)
$url = URL::temporarySignedRoute(
    "download",
    now()->addHour(),
    ["file" => $file->id],
);

// Регистрация роута с проверкой подписи
Route::get("/unsubscribe/{user}", UnsubscribeController::class)
    ->name("unsubscribe")
    ->middleware("signed"); // 403 при невалидной/просроченной подписи

// Ручная проверка
public function unsubscribe(Request $request, User $user): Response
{
    if (! $request->hasValidSignature()) {
        abort(403);
    }
    $user->unsubscribe();
    return view("unsubscribed");
}

// Игнорировать конкретные параметры (полезно при utm-метках)
Route::get("/...", ...)->middleware("signed:relative")
    // или: $request->hasValidSignatureWhileIgnoring(["utm_source", "utm_medium"])

// В Mail/Notification
class WelcomeNotification extends Notification {
    public function toMail($notifiable) {
        return (new MailMessage)
            ->action(
                "Подтвердить",
                URL::temporarySignedRoute("verify", now()->addHour(), ["user" => $notifiable->id])
            );
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.routing',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что произойдёт, если вложенная route-группа задаёт middleware и prefix, которые уже есть у внешней?',
                'answer' => '**Атрибуты группы не перезаписываются, а мерджатся** — это часто удивляет. Поведение реализовано в **`Illuminate\\Routing\\RouteGroup::merge`**.

**Правила слияния:**

| Атрибут | Как мерджится |
| --- | --- |
| `prefix` | Конкатенация **через `/`** — `\'api\'` + `\'v1\'` → `api/v1` |
| `middleware` | **Сумма** массивов — внешний + вложенный |
| `name` / `as` | Конкатенация **через `.`** — `\'api.\'` + `\'v1.\'` → `api.v1.` |
| `where` (regex) | Сумма массивов; **вложенный** имеет приоритет при конфликте ключей |
| `namespace` | Конкатенация через `\\` (легаси, в L11 почти не нужно) |
| **`domain`** | **Исключение** — **вложенный заменяет** внешний |

**Типовое применение — версионирование + RBAC:**

- Внешняя группа: `prefix(\'api\')->middleware([\'throttle:60,1\'])->name(\'api.\')`.
- Вложенная `v1`: добавляет `auth:sanctum` и префикс имени `v1.`.
- Ещё одна `admin`: добавляет `role:admin` — в итоге `api/v1/admin/*` с тремя middleware и именами `api.v1.admin.*`.

**Полезный приём:** вложенная группа может также **переопределить `where`** для конкретного параметра — внешний regex для `id` останется в силе, новые добавятся.',
                'code_example' => '<?php
Route::prefix("api")
    ->middleware(["throttle:60,1"])
    ->name("api.")
    ->group(function () {

        // /api/v1/* с throttle
        Route::prefix("v1")
            ->middleware(["auth:sanctum"])    // + throttle
            ->name("v1.")                      // + "api."
            ->group(function () {

                // /api/v1/users - throttle + auth:sanctum
                Route::get("/users", ...)->name("users.index");
                // route name = "api.v1.users.index"

                // /api/v1/admin/* - throttle + auth:sanctum + role:admin
                Route::prefix("admin")
                    ->middleware(["role:admin"])
                    ->name("admin.")
                    ->group(function () {
                        Route::delete("/users/{id}", ...);
                        // route name = "api.v1.admin..." и т.д.
                    });
            });
    });

// where - тоже мерджится
Route::where(["id" => "[0-9]+"])->group(function () {
    Route::where(["slug" => "[a-z-]+"])->group(function () {
        // оба where применятся: id регексп + slug регексп
        Route::get("/{id}/{slug}", ...);
    });
});',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.routing',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Чем отличаются routes/web.php и routes/api.php?',
                'answer' => 'Два разных файла маршрутов с разными middleware-группами:

**`routes/web.php`** — для **браузерных запросов**:

- Группа middleware **`web`** — сессии, cookies, **CSRF**, `ShareErrorsFromSession`.
- Auth работает через **сессию** (`auth:web`).
- Видно `$errors` и `old()` в Blade.

**`routes/api.php`** — для **API**:

- **Stateless** — нет сессий и CSRF.
- Группа middleware **`api`** — обычно `throttle:api`.
- Auth через **Bearer-токен** (`auth:sanctum`).
- URL автоматически получают префикс **`/api`**.

**Laravel 11**: `api.php` **не создаётся по умолчанию**. Чтобы подключить — `php artisan install:api`. Это создаст `routes/api.php`, поставит `Sanctum` и зарегистрирует группу в `bootstrap/app.php`.',
                'code_example' => '// routes/web.php — браузер, сессии, CSRF
Route::get(\'/dashboard\', [DashboardController::class, \'index\'])->middleware(\'auth\');

// routes/api.php — API, без сессий, Bearer-токен
Route::middleware(\'auth:sanctum\')->get(\'/user\', fn (Request $r) => $r->user());

// Laravel 11
php artisan install:api',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.routing',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как передать параметр из URL в контроллер?',
                'answer' => 'Параметр в маршруте — в **фигурных скобках**: `{id}`. В методе контроллера он приходит как обычный аргумент.

Два варианта:

1. **По имени** — имена в роуте и в сигнатуре метода должны **совпадать**. Контроллер сам делает `User::findOrFail($id)`.
2. **Route Model Binding** — type-hint модели: Laravel сам находит запись по `id` или возвращает **404** через `findOrFail`. Меньше кода, нагляднее.',
                'code_example' => '// routes/web.php
Route::get(\'/users/{id}\', [UserController::class, \'show\']);

// UserController
public function show(int $id) {
    $user = User::findOrFail($id);
    return view(\'users.show\', compact(\'user\'));
}

// С Route Model Binding (короче)
Route::get(\'/users/{user}\', [UserController::class, \'show\']);

public function show(User $user) {
    // $user уже найден или 404
    return view(\'users.show\', compact(\'user\'));
}',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'laravel.routing',
            ],
        ];
    }
}
