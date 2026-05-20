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
                'answer' => 'php artisan route:cache компилирует все роут-файлы (web, api, console, channels) в один сериализованный PHP-файл, что ускоряет загрузку маршрутов в продакшене (особенно полезно при сотнях роутов). Под капотом Route::prepareForSerialization() пакует роуты, включая Closure-обработчики, через laravel/serializable-closure - поэтому в современном Laravel (8.62+) closure-роуты не ломают кеш. Команда исключительно для деплоя - после route:cache любые правки в routes/* не подхватываются до route:clear; не использовать в разработке.',
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
                'answer' => 'Route Model Binding - автоматическая подстановка модели в контроллер по параметру маршрута. Простыми словами: вместо того чтобы вручную писать User::findOrFail($id), Laravel сам найдёт модель по ID или другому полю. Implicit binding - по типу параметра. Explicit binding - вручную через Route::bind.',
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
                'answer' => 'Распространённое заблуждение: "route:cache не работает с Closure-роутами". Это было верно до Laravel 8.62 - сейчас Closure сериализуются через laravel/serializable-closure (форк opis/closure, переехавший в ядро Laravel в 8.62 и полностью заменивший opis в 9.0) и команда route:cache их корректно кеширует. Реальные ограничения: 1) После route:cache любые правки в routes/web.php / routes/api.php не подхватываются - нужен route:clear; то есть это команда деплоя, не разработки. 2) config:cache замораживает env() - после кеша Laravel пропускает загрузку .env, и env() в коде видит только OS-уровень переменных (Docker -e, systemd Environment=) или default; .env-only значения становятся "невидимыми" - классический source of bugs. 3) Если в роуте используется ссылка на класс/метод, недоступный для composer dump-autoload - кеш упадёт. 4) Closure, замыкающий $this или ссылку на несериализуемый объект (PDO, file handle), всё ещё ломает кеш - serializable-closure не магия. В проде route:cache + config:cache + view:cache + event:cache - стандартная связка, всё это объединяет artisan optimize.',
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
                'answer' => 'Implicit binding ловит type-hint Model в методе контроллера и резолвит по primary key из URL-параметра. Чтобы биндить по slug, переопределите getRouteKeyName() на модели или укажите в роуте users/{user:slug}. Можно делать кастомный резолвер через Route::bind() (firstOrFail сам бросит 404). Для составных условий используйте Explicit binding в провайдере (в L11 - в любом ServiceProvider).',
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
                'answer' => 'Signed URLs - механизм Laravel для генерации URL с криптографической подписью, защищающей от подделки параметров. URL::signedRoute("unsubscribe", ["user" => 42]) создаёт ссылку вида /unsubscribe/42?signature=abc123. Под подписью - HMAC-SHA256 от полного URL (хост + путь + query-строка, как её формирует Laravel из переданных параметров), посчитанный с APP_KEY как секретом. Если злоумышленник попробует подменить хоть один параметр (например, user=42 → user=43), HMAC не сойдётся, и middleware ValidateSignature вернёт 403. Дополнительно есть signedRoute с ->expiresAt() / temporarySignedRoute($name, $expiration, $params) - в подпись включается параметр expires (timestamp), и если он в прошлом, ссылка считается невалидной (даже с правильной подписью). Применение: одноразовые ссылки на скачивание файлов, email-подтверждения регистрации, отписка от рассылки одним кликом, password reset (но Laravel для reset использует свой токен в БД, не signed urls), magic-link auth. Защита от replay - не из коробки: signed url можно использовать многократно до истечения; если нужна разовость, добавляйте в подпись nonce и сохраняйте использованные nonce-ы в Redis с TTL. URL должен совпадать дословно - иначе подпись не сойдётся; если за прокси работает rewriting (X-Forwarded-Host, X-Forwarded-Proto), нужны TrustProxies middleware и совпадающий APP_URL, иначе подпись посчитается от другого хоста. В контроллере проверка: ->middleware("signed") в роуте, или $request->hasValidSignature() вручную.',
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
                'answer' => 'Атрибуты группы НЕ перезаписываются, а МЕРДЖАТСЯ - это часто удивляет. Поведение определено в Illuminate\\Routing\\RouteGroup::merge. Правила: 1) prefix конкатенируется через слэш ("api" + "v1" -> "api/v1"); 2) middleware объединяется в массив (внешний + вложенный); 3) where-ограничения регекспов на параметры объединяются в массив (вложенный имеет приоритет на конфликте ключей); 4) name-prefix склеивается через точку ("api." + "v1." -> "api.v1."); 5) namespace конкатенируется через бэкслеш (легаси, в L11 редко используют); 6) domain - вложенный заменяет внешний (это исключение!). Поэтому admin-группа внутри api/v1 даст в итоге префикс api/v1/admin, имя api.v1.admin., и сумму всех middleware. Полезно для версионирования API + RBAC: одна группа задаёт auth:sanctum + throttle, вложенная добавляет role:admin.',
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
