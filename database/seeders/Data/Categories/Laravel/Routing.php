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
                'answer' => 'Маршрут - это правило, которое связывает URL и HTTP-метод с конкретным действием (контроллером или замыканием). Простыми словами: когда пользователь заходит на /users, Laravel смотрит в файл routes/web.php и находит, какой код выполнить.',
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
                'answer' => 'Параметры в фигурных скобках {id}. Опциональный - {name?} с обязательным значением по умолчанию в замыкании/контроллере. Regex-ограничения через ->where(). Также есть готовые helper-методы whereNumber, whereAlpha, whereUuid.',
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
                'answer' => 'Именованный маршрут - это маршрут с уникальным именем, по которому можно генерировать URL через route() и редиректить через redirect()->route(). Главный плюс: если URL изменится, не нужно менять ссылки по всему коду - имя остаётся тем же.',
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
                'answer' => 'Route group - это способ применить общие настройки (middleware, prefix, namespace, name prefix) к группе маршрутов. Простыми словами: вместо того чтобы дублировать middleware на каждом роуте, оборачиваем их в группу.',
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
                'answer' => 'php artisan route:cache компилирует все роуты в один сериализованный PHP-файл, что ускоряет загрузку маршрутов в продакшене (особенно полезно при сотнях роутов). Исторически closure-роуты ломали кеш, потому что Closure нельзя было сериализовать - начиная с Laravel 8.62 (сент. 2021) подключён laravel/serializable-closure (форк opis/closure, который Laravel вытащил в собственный пакет; opis/closure окончательно вырезан в Laravel 9.0), и Route::prepareForSerialization() пакует Closure в SerializableClosure::unsigned() прямо в момент кеширования. Реальные ограничения сегодня: после route:cache изменения в файлах routes/* не подхватываются до следующего route:clear/route:cache, поэтому это команда исключительно для деплоя. Для api - artisan route:cache; для повседневной разработки - не использовать.',
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
                'answer' => 'Signed URLs - механизм Laravel для генерации URL с криптографической подписью, защищающей от подделки параметров. URL::signedRoute("unsubscribe", ["user" => 42]) создаёт ссылку вида /unsubscribe/42?signature=abc123. Под подписью - HMAC-SHA256 от полной канонизированной строки URL (хост + путь + параметры в отсортированном виде), посчитанный с APP_KEY как секретом. Если злоумышленник попробует подменить хоть один параметр (например, user=42 → user=43), HMAC не сойдётся, и middleware ValidateSignature вернёт 403. Дополнительно есть signedRoute с ->expiresAt() / temporarySignedRoute($name, $expiration, $params) - в подпись включается параметр expires (timestamp), и если он в прошлом, ссылка считается невалидной (даже с правильной подписью). Применение: одноразовые ссылки на скачивание файлов, email-подтверждения регистрации, отписка от рассылки одним кликом, password reset (но Laravel для reset использует свой токен в БД, не signed urls), magic-link auth. Защита от replay - не из коробки: signed url можно использовать многократно до истечения; если нужна разовость, добавляйте в подпись nonce и сохраняйте использованные nonce-ы в Redis с TTL. URL должен совпадать дословно - иначе подпись не сойдётся; если за прокси работает rewriting (X-Forwarded-Host, X-Forwarded-Proto), нужны TrustProxies middleware и совпадающий APP_URL, иначе подпись посчитается от другого хоста. В контроллере проверка: ->middleware("signed") в роуте, или $request->hasValidSignature() вручную.',
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
                'answer' => 'Атрибуты не перезаписываются, а мерджатся: prefix конкатенируется ("api" + "v1" -> "api/v1"), middleware и where-ограничения объединяются в массив, name-prefix также склеивается через точку. Поэтому admin внутри api/v1 даст префикс api/v1/admin и сумму всех middleware. Это поведение задаётся в RouteGroup::merge.',
                'difficulty' => 3,
                'topic' => 'laravel.routing',
            ],
        ];
    }
}
