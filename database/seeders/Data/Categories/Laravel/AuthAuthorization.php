<?php

namespace Database\Seeders\Data\Categories\Laravel;

class AuthAuthorization
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Laravel',
                'question' => 'Что такое Authentication Guards в Laravel?',
                'answer' => 'Guard - это способ аутентификации (как Laravel определяет, кто пользователь). Стандартные: web (через сессии); api (исторически token-driven через TokenGuard, но он удалён в L6+ - в современном скаффолде api настраивают с драйвером sanctum); sanctum (cookies для SPA + bearer-токены для мобильных). Можно настроить несколько guards в config/auth.php (multi-auth) - например для admin и user отдельно.',
                'code_example' => 'auth()->guard(\'admin\')->attempt($credentials);
auth(\'admin\')->user();
Auth::guard(\'api\')->check();

// в config/auth.php
\'guards\' => [
    \'web\' => [\'driver\' => \'session\', \'provider\' => \'users\'],
    \'admin\' => [\'driver\' => \'session\', \'provider\' => \'admins\'],
],',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.auth_authorization',
            ],
            [
                'category' => 'Laravel',
                'question' => 'В чём разница между Sanctum и Passport?',
                'answer' => 'Sanctum - простой и лёгкий пакет: API-токены (как GitHub) + SPA-аутентификация через сессии и cookies. Подходит для большинства SPA и мобильных приложений. Passport - полноценный OAuth2 сервер: авторизация сторонних приложений, grant types, refresh tokens. Использовать только если реально нужен OAuth2.',
                'code_example' => '// Sanctum
$token = $user->createToken(\'mobile\')->plainTextToken;

// в маршрутах
Route::middleware(\'auth:sanctum\')->get(\'/me\', fn(Request $r) => $r->user());

// клиент
fetch(\'/api/me\', { headers: { Authorization: `Bearer ${token}` } });',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.auth_authorization',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Authorization, Gates и Policies?',
                'answer' => 'Authorization - проверка прав ("может ли пользователь сделать X"). Gate - простая проверка через closure (для отдельных действий). Policy - класс, привязанный к модели, с методами view/create/update/delete. Используется через can(), authorize(), Blade-директиву @can.',
                'code_example' => '// Gate
Gate::define(\'edit-settings\', fn(User $u) => $u->is_admin);
if (Gate::allows(\'edit-settings\')) { /* ... */ }

// Policy
php artisan make:policy PostPolicy --model=Post

class PostPolicy {
    public function update(User $user, Post $post): bool {
        return $user->id === $post->user_id;
    }
}

$this->authorize(\'update\', $post);
$user->can(\'update\', $post);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.auth_authorization',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что делает Gate::before?',
                'answer' => 'Gate::before - это глобальный pre-check, выполняемый перед любой проверкой Gate/Policy. Если возвращает true - доступ разрешён, false - запрещён, null - проверка идёт дальше. Часто используется для super-admin: "админу можно всё".',
                'code_example' => 'Gate::before(function (User $user, string $ability) {
    if ($user->is_super_admin) {
        return true;
    }
});',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.auth_authorization',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое уязвимость IDOR и как её предотвращать в Laravel?',
                'answer' => 'IDOR (Insecure Direct Object Reference) - атака, при которой авторизованный пользователь меняет идентификатор в URL/теле запроса (/orders/5 → /orders/6, или body { "user_id": 7 }) и получает доступ к чужой сущности. Уязвимость возникает, когда контроллер находит модель только по первичному ключу, не проверяя ВЛАДЕНИЕ. Ловушка: route model binding (Order $order) сам по себе не защищает - он лишь делает Order::findOrFail($id), не зная про текущего пользователя. Три способа защиты, по возрастанию надёжности: 1) Policy + $this->authorize() в контроллере - явная проверка владения на уровне домена, легко тестировать, видно в логах. 2) Scoped query через отношение текущего пользователя: Auth::user()->orders()->findOrFail($id) - SQL-запрос изначально содержит WHERE user_id = ?, просто невозможно достать чужое. 3) Scoped implicit binding через Route::scopeBindings() (или метод scopeBindings() на конкретном роуте) - заставляет Laravel при /users/{user}/orders/{order} проверять, что order принадлежит user. Также: для multi-tenant приложений заворачивайте всё в Global Scope с tenant_id, и тестируйте политики через actingAs($otherUser)->getJson("/orders/{$ownOrder->id}")->assertForbidden(). Никогда не доверяйте $request->input("user_id") в клиентских действиях - всегда брать $request->user()->id. Исключение - админские/системные эндпоинты, где назначение чужого user_id легитимно: там input("user_id") можно принимать, но Policy сначала проверяет, что текущий user - админ с нужной ролью, а сам user_id валидируется на существование.',
                'code_example' => '<?php
// УЯЗВИМО: route model binding без проверки владения
public function show(Order $order) {
    return new OrderResource($order); // /orders/6 от чужого юзера → утечка
}

// 1) Через Policy - явно
public function show(Order $order) {
    $this->authorize("view", $order); // OrderPolicy::view → user_id === auth()->id()
    return new OrderResource($order);
}

// 2) Scoped query - запросом, не PHP-проверкой
public function show(int $id) {
    $order = $request->user()->orders()->findOrFail($id);
    return new OrderResource($order);
}

// 3) Scoped implicit binding - Laravel сам проверит связь
Route::get("/users/{user}/orders/{order}", function (User $user, Order $order) {
    // order гарантированно принадлежит user (WHERE user_id = users.id)
    return $order;
})->scopeBindings();',
                'code_language' => 'php',
                'difficulty' => 5,
                'topic' => 'laravel.auth_authorization',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое CSRF и как Laravel защищает от него?',
                'answer' => 'CSRF (Cross-Site Request Forgery) - атака, при которой пользователя заставляют отправить запрос с его сессией на ваш сайт с другого. Laravel автоматически защищает все POST/PUT/DELETE формы web-роутов через middleware VerifyCsrfToken. В формах нужен @csrf, в Ajax - заголовок X-CSRF-TOKEN. Можно исключить роуты через $except.',
                'code_example' => '<form method="POST">
    @csrf
    <input name="title">
</form>

// Ajax (мета-тег + axios)
<meta name="csrf-token" content="{{ csrf_token() }}">

axios.defaults.headers.common[\'X-CSRF-TOKEN\'] =
    document.querySelector(\'meta[name="csrf-token"]\').content;',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.auth_authorization',
            ],
            [
                'category' => 'Laravel',
                'question' => 'В чём практическая разница между auth middleware и Authentication Guard?',
                'answer' => 'Guard — это стратегия идентификации пользователя (session, token, sanctum) и определяется в config/auth.php; он отвечает за вопрос "кто этот пользователь". Middleware auth:web проверяет, что guard "web" вернул пользователя, и иначе бросает AuthenticationException, редиректя на login. Guard можно использовать без middleware (Auth::guard("api")->user()), а middleware всегда работает поверх какого-то guard.',
                'difficulty' => 3,
                'topic' => 'laravel.auth_authorization',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Breeze и для чего он нужен?',
                'answer' => 'Breeze — минимальный стартовый набор, который ставит готовую аутентификацию: вход, регистрацию, сброс пароля, подтверждение email и пароля. Поставляется в вариантах Blade, Livewire, Inertia+Vue, Inertia+React и API-only. В отличие от Jetstream, Breeze осознанно простой и подходит как стартовая точка для проектов без 2FA и команд.',
                'difficulty' => 2,
                'topic' => 'laravel.auth_authorization',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Jetstream и чем он отличается от Breeze?',
                'answer' => 'Jetstream — продвинутый стартовый набор поверх Sanctum с двухфакторной аутентификацией, управлением сессиями браузера, профилем пользователя, API-токенами и опциональными командами (teams). Выбирается стек Livewire или Inertia. Breeze значительно проще и не тянет 2FA/команды — Jetstream берут, когда нужны фичи из коробки, иначе Breeze.',
                'difficulty' => 2,
                'topic' => 'laravel.auth_authorization',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Socialite и через каких провайдеров он умеет логинить?',
                'answer' => 'Socialite — официальный пакет для OAuth-аутентификации через сторонних провайдеров. Из коробки поддерживает Facebook, Twitter/X, Google, LinkedIn, GitHub, GitLab, Bitbucket и Slack. Дополнительные провайдеры подключаются через Socialite Providers (community-репозиторий). Скрывает детали OAuth2-flow за выразительным фасадом Socialite::driver()->redirect()/user().',
                'difficulty' => 2,
                'topic' => 'laravel.auth_authorization',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Spatie Laravel-Permission и какую задачу он решает?',
                'answer' => 'Spatie Laravel-Permission — самый популярный community-пакет для ролей и разрешений в Laravel. Добавляет трейты HasRoles и HasPermissions модели User, таблицы roles, permissions, model_has_roles, кэширует разрешения и интегрируется с Gate/Policy через canAny, hasRole, hasPermissionTo. Поддерживает множественные guards и teams для мультиарендных приложений.',
                'difficulty' => 3,
                'topic' => 'laravel.auth_authorization',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Fortify и как он связан с Breeze и Jetstream?',
                'answer' => 'Fortify — backend-агностик аутентификации без UI: реализует роуты и контроллеры для логина, регистрации, сброса пароля, 2FA, подтверждения email. Jetstream использует Fortify под капотом, добавляя сверху Livewire/Inertia-вьюхи. Breeze, наоборот, не использует Fortify и идёт со своим набором контроллеров. Fortify выбирают, когда нужна готовая логика аутентификации, но UI пишется самостоятельно (например, для headless API).',
                'difficulty' => 3,
                'topic' => 'laravel.auth_authorization',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как получить текущего залогиненного пользователя в Laravel?',
                'answer' => 'Auth::user() или auth()->user() — модель текущего юзера или null. Auth::id() — только id. Auth::check() — true/false. В контроллерах удобно через $request->user(). Если юзер не залогинен — все эти методы вернут null/false, поэтому до защищённой логики ставят middleware \'auth\', которое сделает редирект на /login.',
                'code_example' => 'public function index(Request $request) {
    $user = $request->user(); // или auth()->user()
    return view(\'dashboard\', compact(\'user\'));
}',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'laravel.auth_authorization',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как защитить маршрут от неавторизованных пользователей?',
                'answer' => 'Навесь middleware \'auth\' на route: Route::get(\'/profile\', ...)->middleware(\'auth\') — без логина юзера редиректит на /login. На группу: Route::middleware(\'auth\')->group(...). В контроллере Laravel 11+ middleware задаётся через интерфейс HasMiddleware и статический метод middleware() (старый $this->middleware(\'auth\') в конструкторе из L10 убран — базовый Controller больше не имеет такого метода).',
                'code_example' => '// На отдельном роуте
Route::get(\'/profile\', [ProfileController::class, \'show\'])->middleware(\'auth\');

// На группе
Route::middleware(\'auth\')->group(function () {
    Route::get(\'/dashboard\', [DashboardController::class, \'index\']);
    Route::get(\'/settings\', [SettingsController::class, \'edit\']);
});

// В контроллере (Laravel 11+)
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProfileController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [\'auth\', new Middleware(\'verified\', except: [\'show\'])];
    }
}',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'laravel.auth_authorization',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое guards в Laravel простыми словами?',
                'answer' => 'Guard — это стратегия проверки «кто этот юзер». Дефолтный web — сессия и куки (для браузера). api с драйвером sanctum — Bearer-токен. Можно настроить несколько guards в config/auth.php: например отдельный admin для админки со своей таблицей admins. Доступ к конкретному guard — Auth::guard(\'admin\'). Middleware auth:web проверяет, что web-guard вернул юзера, иначе редирект на /login.',
                'code_example' => '// config/auth.php
\'guards\' => [
    \'web\'   => [\'driver\' => \'session\', \'provider\' => \'users\'],
    \'admin\' => [\'driver\' => \'session\', \'provider\' => \'admins\'],
    \'api\'   => [\'driver\' => \'sanctum\', \'provider\' => \'users\'],
],

// Использование конкретного guard
auth()->guard(\'admin\')->attempt($credentials);
$admin = auth(\'admin\')->user();
Auth::guard(\'admin\')->check();

// Защита маршрутов конкретным guard
Route::middleware(\'auth:admin\')->group(function () {
    Route::get(\'/admin\', [AdminController::class, \'index\']);
});',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.auth_authorization',
            ],
        ];
    }
}
