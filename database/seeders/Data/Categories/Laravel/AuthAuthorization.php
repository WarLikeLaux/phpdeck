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
                'answer' => '**Guard** — стратегия, **как Laravel опознаёт текущего пользователя**. Описывается в `config/auth.php` парой «`driver` + `provider`»: **driver** определяет, *откуда читать* данные (`session`, `token`, `sanctum`, `passport`), **provider** — *в какой таблице/модели* искать пользователя.

**Дефолтные guards в Laravel 11:**

| Guard | Driver | Где применяется |
| --- | --- | --- |
| `web` | `session` | Браузерные запросы — куки + CSRF |
| `api` (если настроен) | `sanctum` | SPA на том же домене (куки) и мобильные/токены (Bearer) |
| `admin` (кастомный) | `session` | Отдельная таблица `admins`, своя сессия |

**Что важно про драйверы:**

- Старый `token` (`TokenGuard`) в L6+ убран — в современных скаффолдах `api` сразу настраивают с `sanctum`.
- `sanctum` — гибрид: для SPA выдаёт **сессионную куку** через `EnsureFrontendRequestsAreStateful`, для мобильных — **Bearer-токен**.
- `passport` — полноценный OAuth2-сервер (если действительно нужно).

**Несколько guards одновременно (multi-auth):**

- Один и тот же роут можно открыть нескольким guard-ам: `auth:web,sanctum` — пускает, если **хотя бы один** опознал пользователя.
- Логин в конкретный guard: `Auth::guard(\'admin\')->attempt(...)`.
- Дефолтный guard меняется через `config(\'auth.defaults.guard\')`.',
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
                'answer' => '**Sanctum** и **Passport** — два официальных пакета Laravel для **API-аутентификации**, но решают **разные задачи**.

**Sanctum (`laravel/sanctum`) — лёгкий и простой:**

- **API-токены** в стиле GitHub Personal Access Tokens (Bearer-токен в БД).
- **SPA-аутентификация** через **сессии и cookies** (тот же домен).
- **Mobile-токены** для нативных приложений.
- Token abilities (≈ простые scopes).
- **Без OAuth2-flow** — никаких refresh tokens, authorization codes, client_credentials.

**Passport (`laravel/passport`) — полноценный OAuth2-сервер:**

- **OAuth2 grant types**: Authorization Code (с PKCE), Client Credentials, Personal Access, Password (deprecated).
- **Refresh tokens** для долгоживущих сессий.
- **Сторонние приложения** запрашивают доступ к вашему API от имени юзера (как Google/Facebook login).
- JWT-токены (опционально).
- Гораздо сложнее в настройке и обслуживании.

**Сравнение:**

| Параметр | **Sanctum** | **Passport** |
|---|---|---|
| Стандарт | Свой простой | **OAuth2 RFC 6749** |
| Размер таблиц | 1 (`personal_access_tokens`) | 5+ (`oauth_clients`, `oauth_auth_codes`, `oauth_access_tokens`, ...) |
| Refresh tokens | Нет | **Да** |
| Сторонние приложения | Нет | **Да** |
| SPA на том же домене | **Да** (cookie + session) | Нет (только Bearer) |
| Мобильные | **Да** (Bearer) | Да |
| 2FA / PKCE | Нет | **Да** |
| Сложность | **Низкая** | Высокая |

**Когда что брать:**

- **Sanctum** — **в 95% случаев**. SPA + мобильное приложение, простой API для своего фронта.
- **Passport** — только если **реально** нужен OAuth2: вы строите public API, где сторонние компании пишут клиентов под ваш сервис.

**Правило:** **не брать Passport «на вырост»** — миграция Passport→Sanctum проще, чем наоборот.',
                'code_example' => '<?php
// === Sanctum ===
// php artisan install:api  (Laravel 11) или composer require laravel/sanctum
// php artisan migrate

// 1) Mobile token
\$token = \$user->createToken("mobile", ["read", "write"])->plainTextToken;

// 2) Защита маршрута
Route::middleware("auth:sanctum")->get("/api/me", fn (Request \$r) =>
    \$r->user()
);

// 3) Проверка ability
if (\$request->user()->tokenCan("delete-posts")) { /* ... */ }

// 4) Revoke
\$user->tokens()->delete();              // все токены
\$user->currentAccessToken()->delete();   // только текущий

// Клиент - Bearer
fetch("/api/me", {
    headers: { Authorization: `Bearer \${token}` }
});

// === Passport (OAuth2) ===
// composer require laravel/passport
// php artisan passport:install

// Создать OAuth-клиент для стороннего приложения
// php artisan passport:client  (interactive)

// Защита - тот же middleware-синтаксис, другой driver
Route::middleware("auth:api")->get("/api/user", ...);

// В config/auth.php
"guards" => [
    "api" => ["driver" => "passport", "provider" => "users"],
],',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.auth_authorization',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Authorization, Gates и Policies?',
                'answer' => '**Authorization** — проверка **«может ли этот пользователь сделать X»** (в отличие от authentication — «кто он»). В Laravel два механизма: **Gates** и **Policies**.

**Gate** — простой closure, привязанный к **имени действия** (не к модели):

- Регистрируется в любом провайдере через `Gate::define(\'edit-settings\', fn (User $u) => $u->is_admin)`.
- Подходит для **сквозных правил**: «может зайти в админку», «может видеть billing».

**Policy** — **класс**, привязанный к **модели**, с методами по CRUD:

- `php artisan make:policy PostPolicy --model=Post` создаёт `view`, `create`, `update`, `delete`, `restore`, `forceDelete`.
- **Автоматически связывается**: Laravel 11+ ищет policy по конвенции (`App\\Models\\Post` → `App\\Policies\\PostPolicy`), без явной регистрации.
- Подходит для **правил вокруг сущности**: «владелец поста», «модератор может удалять чужие».

**Как зовётся в коде:**

- В контроллере: `$this->authorize(\'update\', $post)` — бросит `403` при отказе.
- На юзере: `$user->can(\'update\', $post)` / `cannot(...)`.
- В роуте: `->middleware(\'can:update,post\')` (явное связывание параметра).
- В Blade: `@can(\'update\', $post)` / `@cannot`.
- В FormRequest: переопределить `authorize()`.

**Возврат значений:**

- `true`/`false` — простой да/нет.
- `Response::allow()` / `Response::deny(\'Причина\')` — кастомное сообщение для ответа `403`.',
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
                'answer' => '**`Gate::before(...)`** — глобальный **pre-check**, который запускается **перед каждой** проверкой Gate/Policy.

**Семантика возврата:**

| Возврат | Что значит |
| --- | --- |
| `true` | Доступ **разрешён** — обычная проверка пропускается |
| `false` | Доступ **запрещён** — обычная проверка пропускается |
| `null` | Не вмешиваться — идём дальше в `Gate::define()` или метод Policy |

Типовой кейс — **super-admin «можно всё»**. Дополняющий хук — **`Gate::after(...)`** — срабатывает **после** обычной проверки, если та вернула `null` (только дополняет, не переопределяет).

**Подводные камни:**

- Возврат **`false`** из `before` **жёстко** запрещает действие — даже если Policy разрешает. Поэтому для «по умолчанию запретить, кроме админа» возвращай `true` для админа и **`null`** (а не `false`) для остальных.
- `before` срабатывает на **все** ability — фильтруй по имени `$ability` или по типу объекта, иначе суперадмин обойдёт даже строгие бизнес-проверки (например, «нельзя удалить уже оплаченный заказ»).',
                'code_example' => 'use App\\Models\\User;
use Illuminate\\Support\\Facades\\Gate;

Gate::before(function (User $user, string $ability) {
    if ($user->is_super_admin) {
        return true; // открыли всё
    }

    return null; // НЕ false — иначе перекроем все остальные правила
});

Gate::after(function (User $user, string $ability, ?bool $result) {
    // подоткнуть результат, если базовая проверка вернула null
});',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.auth_authorization',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое уязвимость IDOR и как её предотвращать в Laravel?',
                'answer' => '**IDOR (Insecure Direct Object Reference)** — атака, при которой авторизованный пользователь **меняет идентификатор** в URL/теле запроса и получает доступ к **чужой сущности**.

**Примеры атаки:**

- `/orders/5` → **`/orders/6`** (чужой заказ).
- POST body `{ "user_id": 7 }` → действие выполнится от имени чужого юзера.
- `/files/abc` → **`/files/xyz`** (чужой документ).

**Почему возникает:** контроллер находит модель **только по первичному ключу**, не проверяя **ВЛАДЕНИЕ**.

**КРИТИЧНАЯ ЛОВУШКА — Route Model Binding не защищает:**

- `public function show(Order $order)` под капотом делает **`Order::findOrFail($id)`** — **ничего** не знает про текущего пользователя.
- Это **самая частая** дыра в Laravel-приложениях.

**Три способа защиты, по возрастанию надёжности:**

| Способ | Что делает | Когда применять |
|---|---|---|
| **1. Policy + `$this->authorize()`** | Явная проверка владения на уровне домена, видна в логах | По умолчанию для всех CRUD |
| **2. Scoped query через relation** | `auth()->user()->orders()->findOrFail($id)` — SQL уже содержит `WHERE user_id = ?` | Когда модель **всегда** принадлежит юзеру |
| **3. Scoped implicit binding** | `Route::scopeBindings()` заставляет Laravel проверять связь между parent и child | Nested routes `/users/{user}/orders/{order}` |

**Дополнительные правила:**

- **Никогда** не доверяйте `$request->input("user_id")` в **клиентских** действиях — всегда брать `$request->user()->id`.
- **Multi-tenant** приложения — оборачивайте всё в **Global Scope с `tenant_id`**.
- **Тестируйте** политики через `actingAs($otherUser)->getJson("/orders/{$ownOrder->id}")->assertForbidden()`.

**Исключение — админские/системные эндпоинты:**

- Назначение чужого `user_id` легитимно (админ создаёт юзера, импорт).
- Policy сначала проверяет, что **текущий user — админ** с нужной ролью.
- Сам `user_id` валидируется на существование через `exists:users,id`.

**Тест на IDOR должен быть в каждом проекте:**

```php
$ownOrder = Order::factory()->for($user)->create();
$otherOrder = Order::factory()->create(); // чужой

$this->actingAs($user)
    ->getJson("/orders/{$otherOrder->id}")
    ->assertForbidden(); // или 404 если не хочется палить существование
```',
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
                'answer' => '**CSRF (Cross-Site Request Forgery)** — атака, при которой **чужой сайт** заставляет браузер пользователя отправить запрос **с его сессионной кукой** на ваш сайт. Жертва залогинена → сервер видит легитимного юзера → деньги перевелись.

**Как защищает Laravel:**

- Middleware **`ValidateCsrfToken`** (в Laravel 11 — глобально в `bootstrap/app.php`) для всех **`POST`/`PUT`/`PATCH`/`DELETE`** web-роутов.
- Каждой сессии присваивается **`csrf_token()`** (хранится в `_token` сессии); сайт отдаёт его в форме, браузер шлёт обратно — проверяется на равенство.
- На API-роутах CSRF **не нужен** — там Bearer-токен/Sanctum, защита делается иначе.

**Как передавать токен:**

- В формах — **`@csrf`** (генерирует `<input type="hidden" name="_token">`).
- В AJAX — заголовок **`X-CSRF-TOKEN`**: токен берут из `<meta name="csrf-token">`. Sanctum/Inertia ставят его сами.
- В JSON-запросах с куками с того же домена браузер **сам** не пошлёт токен — нужен `X-XSRF-TOKEN` из куки `XSRF-TOKEN` (Sanctum и `axios` делают автоматически).

**Когда отключают:**

- Для webhook-ов от внешних сервисов (Stripe, GitHub) — их добавляют в `except` middleware (в Laravel 11 — через `$middleware->validateCsrfTokens(except: [...])` в `bootstrap/app.php`).
- Защита от подделки на этих роутах — **сигнатура** запроса (HMAC от тела), а не CSRF.',
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
                'answer' => 'Это **разные слои** — часто путают.

| | `Guard` | `auth` middleware |
| --- | --- | --- |
| Отвечает на | **«Кто этот пользователь?»** | **«Пускать ли его?»** |
| Где живёт | `config/auth.php` (`session`, `sanctum`, кастомный) | HTTP-стек, висит на роуте |
| Что делает | Читает сессию/токен/куку → возвращает `Authenticatable` или `null` | Зовёт `guard->check()`, при `false` бросает `AuthenticationException` |
| Сам блокирует? | **Нет** — просто опознаёт | **Да** — редирект на `/login` (web) или `401` (JSON) |
| Можно использовать без другого? | Да — `Auth::guard(\'admin\')->user()` прямо в коде | Нет — middleware всегда работает поверх какого-то guard |

**Полезный сценарий:** `auth:web,sanctum` — middleware пробует **оба guard-а** по очереди и пускает, если **хотя бы один** опознал пользователя. Удобно для эндпоинтов, которые открыты и для браузера (сессия), и для мобильных (Bearer).

**Что отсюда следует:**

- Защитить роут — это **middleware**, а не guard.
- Логин в админку с отдельной таблицей — это **guard `admin`** + `Auth::guard(\'admin\')->attempt(...)` + `auth:admin` middleware.
- Если просто хочешь «достать текущего» без блокировки — бери guard напрямую, middleware не вешай.',
                'code_example' => '<?php
// config/auth.php - два guard-а
"guards" => [
    "web" => ["driver" => "session", "provider" => "users"],
    "api" => ["driver" => "sanctum", "provider" => "users"],
],

// 1) Middleware - запретить доступ если не залогинен
Route::middleware("auth")->get("/dashboard", ...);             // дефолтный guard
Route::middleware("auth:web")->get("/profile", ...);            // явно web
Route::middleware("auth:sanctum")->get("/api/me", ...);         // API
Route::middleware("auth:web,sanctum")->get("/hybrid", ...);     // оба варианта ОК

// 2) Guard - в коде, без блокировки доступа
$admin = Auth::guard("admin")->user();    // null если не залогинен админ
if ($admin) { /* ... */ }

// 3) Сценарий "залогинен ли в принципе" без middleware
if (! auth("web")->check()) {
    return redirect("/login");
}

// 4) Логин в произвольный guard
Auth::guard("admin")->attempt([
    "email"    => $request->email,
    "password" => $request->password,
]);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.auth_authorization',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Breeze и для чего он нужен?',
                'answer' => '**Breeze** — минимальный стартовый набор Laravel, который накатывает готовую аутентификацию: вход, регистрацию, сброс пароля, подтверждение email и пароля.

Стэки на выбор:

- **Blade** — серверный рендер.
- **Livewire** — реактивный Blade.
- **Inertia + Vue** или **Inertia + React** — SPA.
- **API-only** — без UI, для мобильных клиентов.

В отличие от Jetstream, Breeze **осознанно простой**: ни 2FA, ни команд, ни управления сессиями. Берут как стартовую точку и допиливают руками.

Ставится одной командой: `composer require laravel/breeze --dev` + `php artisan breeze:install <stack>`.',
                'difficulty' => 2,
                'topic' => 'laravel.auth_authorization',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Jetstream и чем он отличается от Breeze?',
                'answer' => '**Jetstream** — продвинутый стартовый набор поверх `Sanctum` с фичами «из коробки».

Что даёт сверх Breeze:

- **2FA** (двухфакторная аутентификация через TOTP).
- **Управление сессиями браузера** — список активных, удалённый логаут.
- **Профиль пользователя** (имя, email, аватар, смена пароля).
- **API-токены** через Sanctum с UI.
- **Teams** (опционально) — команды, инвайты, роли в команде.

Стэки: **Livewire** или **Inertia + Vue**.

Когда брать: нужны 2FA/команды/UI токенов **из коробки**. Иначе — `Breeze` проще.',
                'difficulty' => 2,
                'topic' => 'laravel.auth_authorization',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Socialite и через каких провайдеров он умеет логинить?',
                'answer' => '**Socialite** — официальный пакет Laravel для OAuth-аутентификации через сторонних провайдеров («войти через Google/GitHub»).

Из коробки:

- **Facebook**, **Twitter/X**, **Google**, **LinkedIn**, **GitHub**, **GitLab**, **Bitbucket**, **Slack**.
- Прочие — через **Socialite Providers** (community-репозиторий).

Скрывает детали OAuth2-flow за двумя вызовами:

- `Socialite::driver(\'github\')->redirect()` — отправить юзера на провайдера.
- `Socialite::driver(\'github\')->user()` — на callback забрать данные.

Ключи провайдеров кладут в `config/services.php` и `.env`. Дальше — найти/создать пользователя в своей таблице `users` и залогинить через `Auth::login($user)`.',
                'difficulty' => 2,
                'topic' => 'laravel.auth_authorization',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Spatie Laravel-Permission и какую задачу он решает?',
                'answer' => '**`spatie/laravel-permission`** — самый популярный community-пакет для **RBAC** (ролей и разрешений) в Laravel. Решает задачу гибкой системы доступа **без своих таблиц и логики**.

**Что приносит из коробки:**

- Миграции: `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`.
- Трейт `HasRoles` на `User` — методы `assignRole`, `removeRole`, `hasRole`, `hasAnyRole`, `syncRoles`, `givePermissionTo`, `hasPermissionTo`.
- **Интеграция с Gate**: если пермишен есть в БД, `$user->can(\'edit posts\')` сразу работает — не нужно регистрировать каждый в `AuthServiceProvider`.
- **Blade-директивы**: `@role`, `@hasrole`, `@hasanyrole`, `@can`.
- **Middleware**: `role:admin`, `permission:edit-posts`, `role_or_permission:admin|publish-articles`.
- **Кеширование** пермишенов в Redis/файлах — проверка прав не бьёт в БД на каждом запросе.

**Продвинутые возможности:**

- **Несколько guards** — отдельные роли для `web` и `api`.
- **Teams** (multi-tenancy) — одна роль `admin` может существовать в разных командах независимо.
- **Wildcard-пермишены** (с включением в конфиге): `posts.*` покрывает `posts.create`, `posts.update`, ...

**Когда не брать:**

- Если правил всего 2–3 («админ/не админ») — хватит **флага в users** или enum-роли + Gate, без отдельных таблиц.
- Для **ABAC** (атрибутивные правила, например «модератор видит только свой регион») — Spatie слабоват; берут Policy + scope-ы.',
                'code_example' => '<?php
// composer require spatie/laravel-permission
// php artisan vendor:publish --provider="Spatie\\Permission\\PermissionServiceProvider"
// php artisan migrate

use Spatie\\Permission\\Traits\\HasRoles;

class User extends Authenticatable {
    use HasRoles;
}

// Создание ролей и пермишенов (обычно в seeder)
use Spatie\\Permission\\Models\\Role;
use Spatie\\Permission\\Models\\Permission;

Permission::create(["name" => "edit articles"]);
Permission::create(["name" => "delete articles"]);

$role = Role::create(["name" => "writer"]);
$role->givePermissionTo("edit articles");

// Назначение
$user->assignRole("writer");
$user->givePermissionTo("delete articles");

// Проверки
$user->hasRole("writer");
$user->hasPermissionTo("edit articles");
$user->can("delete articles");  // тот же Gate::allows

// Middleware
Route::middleware("role:admin")->get("/admin", ...);
Route::middleware("permission:edit articles")->put("/articles/{id}", ...);

// Blade
@role("admin")
    <a href="/admin">Админка</a>
@endrole

@can("edit articles")
    <button>Редактировать</button>
@endcan',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.auth_authorization',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Fortify и как он связан с Breeze и Jetstream?',
                'answer' => '**`Fortify`** — **backend-агностичная** реализация аутентификации **без UI**. Регистрирует роуты и контроллеры для:

- логина / регистрации,
- сброса и обновления пароля,
- подтверждения email и confirmable password,
- **двухфакторной аутентификации (2FA)** через TOTP + recovery codes.

UI — **на тебе**: можешь рендерить Blade/Vue/React/мобильный клиент. Одна backend-логика обслуживает все фронтенды.

**Связь со стартерами:**

| Стартер | Использует Fortify? | UI |
| --- | --- | --- |
| `Breeze` | **Нет** — свои простые контроллеры и вьюшки | Blade / Livewire / Inertia |
| `Jetstream` | **Да** — Fortify под капотом | Livewire или Inertia + Vue (с готовой страницей 2FA и сессий) |

**Когда выбирать Fortify напрямую:**

- **Headless API** — бэкенд для мобильного приложения, UI отсутствует.
- **Кастомный фронт** на своём дизайне, но не хочется руками писать password-reset/2FA.
- **Микросервис аутентификации** для нескольких приложений.

**Минус:** «потрогать» Fortify нельзя — без своего UI он невидим, нужно самому строить страницы и подключаться к его эндпоинтам.',
                'code_example' => '<?php
// composer require laravel/fortify
// php artisan vendor:publish --provider="Laravel\\Fortify\\FortifyServiceProvider"
// php artisan migrate

// app/Providers/FortifyServiceProvider.php
public function boot(): void
{
    // Какие фичи включаем
    Fortify::createUsersUsing(CreateNewUser::class);
    Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

    // Какой view рендерить на каждом эндпоинте
    Fortify::loginView(fn () => view("auth.login"));
    Fortify::registerView(fn () => view("auth.register"));
    Fortify::twoFactorChallengeView(fn () => view("auth.2fa"));

    // Rate limiting
    RateLimiter::for("login", function (Request $request) {
        return Limit::perMinute(5)->by($request->email . $request->ip());
    });
}

// config/fortify.php - какие фичи включить
"features" => [
    Features::registration(),
    Features::resetPasswords(),
    Features::emailVerification(),
    Features::updateProfileInformation(),
    Features::updatePasswords(),
    Features::twoFactorAuthentication(),
],',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.auth_authorization',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как получить текущего залогиненного пользователя в Laravel?',
                'answer' => 'Несколько эквивалентных способов:

- `Auth::user()` или `auth()->user()` — модель текущего юзера или `null`.
- `Auth::id()` или `auth()->id()` — только `id`.
- `Auth::check()` — `true`/`false`, залогинен ли.
- В контроллере удобно через `$request->user()`.

Если юзер не залогинен — все эти методы вернут `null`/`false`. Чтобы не проверять каждый раз — навешивают middleware `auth` на роут, и тогда внутри метода юзер **гарантированно** есть.',
                'code_example' => 'use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

public function index(Request $request)
{
    $user = $request->user();      // или auth()->user(), или Auth::user()
    $id   = auth()->id();          // только id

    if (auth()->check()) {
        // юзер залогинен
    }

    return view(\'dashboard\', compact(\'user\'));
}',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'laravel.auth_authorization',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как защитить маршрут от неавторизованных пользователей?',
                'answer' => 'Навесить middleware **`auth`** — без логина Laravel редиректит на `/login`.

Три места, где можно повесить:

1. **На отдельный роут** — `->middleware(\'auth\')` в конце цепочки.
2. **На группу роутов** — `Route::middleware(\'auth\')->group(...)`.
3. **В контроллере (Laravel 11+)** — через интерфейс `HasMiddleware` и статический метод `middleware()`.

В Laravel 11+ старый `$this->middleware(\'auth\')` в конструкторе **убран** — базовый `Controller` такого метода больше не имеет.',
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
                'answer' => '**Guard** — стратегия проверки «кто этот юзер».

Дефолтные guards в `config/auth.php`:

- **`web`** — сессия и куки (для браузера).
- **`api`** с драйвером `sanctum` — Bearer-токен.

Можно настроить несколько guards: например отдельный **`admin`** для админки со своей таблицей `admins`.

- Доступ к конкретному guard — `Auth::guard(\'admin\')`.
- Middleware `auth:web` проверяет, что `web`-guard вернул юзера, иначе редирект на `/login`.

Guard отвечает на вопрос **«кто»**, middleware — **«пускать ли»**.',
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
