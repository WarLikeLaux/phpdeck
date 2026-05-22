<?php

namespace Database\Seeders\Data\Categories\Laravel;

class EloquentAdvanced
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Laravel',
                'question' => 'Что такое Eager Loading и зачем он нужен?',
                'answer' => '**Eager loading** — предварительная загрузка связей **дополнительным пакетным запросом** вместо ленивой подгрузки на каждом обращении.

**Что лечит:** проблему **N+1** — когда `Post::all()` делает 1 запрос, а в цикле `$post->user` делает ещё N запросов.

**Два способа:**

- **`with(\'user\')`** — при **построении запроса**, до выполнения.
- **`load(\'user\')`** — на уже **полученной** коллекции/модели.

**Что Laravel делает под капотом:**

- `SELECT * FROM posts WHERE ...` — основная выборка.
- `SELECT * FROM users WHERE id IN (1, 5, 12, ...)` — все связанные users одним запросом.
- Laravel сшивает результаты по FK в памяти.

Итого: **2 запроса вместо N+1**, независимо от размера выборки.

**Возможности:**

- **Несколько связей** — `with([\'user\', \'comments\'])`.
- **Вложенные** — `with(\'comments.author.profile\')`.
- **С условием** — `with([\'comments\' => fn ($q) => $q->where(\'approved\', true)])`.
- **Только нужные колонки** — `with(\'user:id,name,email\')` (обязательно включить FK, иначе сшивка сломается).

**Подводный камень `limit` внутри `with`:**

- **Laravel 11+** — это **per-parent** limit (5 комментов на каждый пост).
- **Laravel ≤10** — лимит на **общую** выборку (5 комментов всего, не на каждого) — классическая ловушка. Решение — `hasOne()->latestOfMany()`.',
                'code_example' => '// плохо (N+1)
foreach (Post::all() as $post) {
    echo $post->user->name; // запрос к БД на каждом посте
}

// хорошо (eager loading)
foreach (Post::with(\'user\')->get() as $post) {
    echo $post->user->name; // 0 доп. запросов
}

// load - после получения
$posts = Post::all();
$posts->load(\'comments\');',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.eloquent_advanced',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое проблема N+1 и как её обнаружить?',
                'answer' => '**N+1** — антипаттерн, при котором делается **1** запрос для основной выборки и ещё **N** запросов для связей.

Пример: `Post::all()` → 1 запрос; затем `foreach` с `$post->user` → ещё 100 запросов. Итого **101** вместо двух.

Как обнаружить:

- **`Model::preventLazyLoading()`** в `AppServiceProvider::boot()` — Laravel будет бросать `LazyLoadingViolationException` при попытке lazy load. Включают только в dev.
- **Laravel Debugbar** — список запросов снизу страницы.
- **Telescope** — отдельная вкладка с дублирующимися запросами.
- **Pulse** — `Slow Queries` / `N+1`.

Лечится eager loading через **`with()`** в запросе или **`load()`** на уже полученной коллекции.',
                'code_example' => '// в AppServiceProvider::boot()
Model::preventLazyLoading(! app()->isProduction());

// или одноразово
Post::preventLazyLoading();',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.eloquent_advanced',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что делает Model::shouldBeStrict() и зачем он нужен?',
                'answer' => '**`Model::shouldBeStrict()`** (Laravel 9.3+) — один вызов, включающий **три защитных режима** для Eloquent. Цель: ловить классические баги на этапе разработки, а не в проде.

**Три включаемых режима:**

| Режим | Что ловит | Исключение |
|---|---|---|
| **`preventLazyLoading()`** | Попытку lazy-load связи (N+1) | `LazyLoadingViolationException` |
| **`preventSilentlyDiscardingAttributes()`** | Передан атрибут не из `$fillable` | `MassAssignmentException` |
| **`preventAccessingMissingAttributes()`** | Обращение к полю, которого нет в выборке (забыли `select`) | `MissingAttributeException` |

**Стандартная практика — включать только в dev:**

```php
// AppServiceProvider::boot()
Model::shouldBeStrict(! $this->app->isProduction());
```

**Почему так:**

- В проде неожиданное исключение «забыл eager-load на одной редкой ветке» уронит запрос юзеру.
- В dev/staging — наоборот, **должно** падать, чтобы баг поймали до релиза.

**Альтернатива — гранулярно:**

- `Model::preventLazyLoading()` — только N+1 защита.
- `Model::handleLazyLoadingViolationUsing(fn (...) => Log::warning(...))` — не падать, а **логировать** lazy-load в проде.

**Что не включается:**

- `Model::preventAccessingMissingRelations()` — отдельный режим, в `shouldBeStrict` не входит до недавнего времени; проверяйте версию.',
                'code_example' => '<?php
// AppServiceProvider::boot()
use Illuminate\\Database\\Eloquent\\Model;

public function boot(): void
{
    Model::shouldBeStrict(! $this->app->isProduction());
}

// эквивалентно:
Model::preventLazyLoading(! $this->app->isProduction());
Model::preventSilentlyDiscardingAttributes(! $this->app->isProduction());
Model::preventAccessingMissingAttributes(! $this->app->isProduction());

// теперь это упадёт с исключением в dev:
$user = User::select(\'id\')->first();
$user->email; // MissingAttributeException

User::create([\'name\' => \'Tom\', \'admin\' => true]); // MassAssignmentException если admin не в $fillable',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.eloquent_advanced',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое withCount?',
                'answer' => '**`withCount()`** — подсчитывает количество связанных записей **одним SQL-запросом**, без их загрузки в память.

Решает классическую задачу: показать в списке юзеров «сколько у них постов» без загрузки всех постов и без N+1 на `$user->posts->count()`.

**Что Laravel делает под капотом:**

```sql
SELECT users.*, (
  SELECT COUNT(*) FROM posts WHERE posts.user_id = users.id
) AS posts_count
FROM users
```

Результат — атрибут `posts_count` на каждой модели.

**Семейство методов** (Laravel 8+):

| Метод | Что считает |
|---|---|
| **`withCount(\'posts\')`** | `COUNT(*)` |
| **`withSum(\'orders\', \'amount\')`** | `SUM(amount)` |
| **`withAvg(\'reviews\', \'rating\')`** | `AVG(rating)` |
| **`withMin(\'reviews\', \'rating\')`** | `MIN(rating)` |
| **`withMax(\'logins\', \'created_at\')`** | `MAX(created_at)` |
| **`withExists(\'unread\')`** | Boolean: есть ли связь |

**Возможности:**

- **С условием** — `withCount([\'posts as published_count\' => fn ($q) => $q->where(\'published\', true)])`.
- **Несколько сразу** — `withCount([\'posts\', \'comments\'])`.
- **Сортировка по агрегату** — `->orderByDesc(\'posts_count\')`.

**Альтернатива:** если нужно сразу несколько полей связанной модели (а не только агрегат), используйте **subquery select через `addSelect`**.',
                'code_example' => '$users = User::withCount(\'posts\', \'comments\')->get();
foreach ($users as $user) {
    echo $user->posts_count;
    echo $user->comments_count;
}

// с условием
User::withCount([\'posts as published_posts_count\' => fn($q) => $q->where(\'published\', true)])->get();',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.eloquent_advanced',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Local и Global scopes в Eloquent?',
                'answer' => 'Два механизма инкапсуляции query-условий **с принципиально разной семантикой**.

**Сравнение:**

| Параметр | **Local scope** | **Global scope** |
|---|---|---|
| Применение | **Явное** — `User::active()->get()` | **Автоматическое** ко всем запросам модели |
| Объявление | Метод `scopeActive(Builder $q)` или `#[Scope]` (L11+) | Класс с `Scope` или замыкание в `booted()` |
| Можно отключить | Не нужно — он не активен по умолчанию | `withoutGlobalScope(\'tenant\')` / `withoutGlobalScopes()` |
| Типичный use case | Часто используемые `where` (active, published, recent) | Soft delete, multi-tenancy, скрытие черновиков |

**Local scope:**

- Метод `scopePopular(Builder $q, int $minViews)` → вызывается как `Post::popular(1000)` (префикс отбрасывается, первая буква — в нижний регистр).
- В Laravel 11+ — атрибут `#[Scope]` на методе без префикса.

**Global scope:**

- Класс с `Scope` интерфейсом и методом `apply(Builder $b, Model $m)`.
- Регистрируется в `booted()` модели: `static::addGlobalScope(new TenantScope)`.
- **Уже встроен**: `SoftDeletes` использует global scope `SoftDeletingScope`.

**Подводные камни global scope:**

- **Невидимая магия** — новичок не понимает, куда делись записи. Документируйте обязательно.
- **Утечка контекста в job-ах** — если scope зависит от `auth()->user()`, при сериализации job юзер уже не тот. Решение — фиксировать tenant_id явно в job или вычислять в момент применения.
- **Снятие в админке/импортах** — `Model::withoutGlobalScope(\'tenant\')->...` обязательно для системных задач.',
                'code_example' => '// Local
public function scopeActive($query) {
    return $query->where(\'active\', true);
}
User::active()->get();

// Global
class TenantScope implements Scope {
    public function apply(Builder $b, Model $m): void {
        $b->where(\'tenant_id\', auth()->user()->tenant_id);
    }
}

class Post extends Model {
    protected static function booted(): void {
        static::addGlobalScope(new TenantScope);
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.eloquent_advanced',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Observers в Laravel?',
                'answer' => '**Observer** — класс с методами-обработчиками событий жизненного цикла модели: `creating`, `created`, `updating`, `updated`, `saving`, `saved`, `deleting`, `deleted`, `restoring`, `restored`.

**Идея:** «когда что-то происходит с моделью — выполнить код». Удобно вынести из модели в отдельный класс.

**Способы регистрации:**

| Способ | Где |
|---|---|
| **`#[ObservedBy(UserObserver::class)]`** на модели | Laravel 11+, рекомендуется — регистрация рядом с моделью |
| **`User::observe(UserObserver::class)`** | В service provider (см. ниже) |

**Где зовём `observe()` в зависимости от версии:**

- **Laravel 10 и старше** — `EventServiceProvider::boot()`.
- **Laravel 11** — `EventServiceProvider` **удалён** из дефолтного скелета. Регистрация в `AppServiceProvider::boot()` (или в любом другом провайдере). Можно вернуть `EventServiceProvider`, добавив его в `bootstrap/providers.php`.

**Какие события полезные:**

- **`creating`** — установить дефолты, slug, UUID **до** INSERT.
- **`created`** — отправить welcome-mail, создать связанные записи.
- **`updating`** — валидация изменений, audit-log diff.
- **`deleting`** — каскадно удалить детей, заархивировать.
- **`saved`** — общее для create/update (например, инвалидация кеша).

**Подводные камни:**

- **Observer не срабатывает на bulk-операции** `Model::where(...)->update(...)` — это прямой SQL без hydration.
- **`saving`/`creating` могут отменить операцию** через `return false` — `save()` вернёт `false` без исключения. Используйте `saveOrFail()`.
- В job/закешированных скриптах — observer регистрируется при загрузке провайдера, проверяйте `php artisan config:cache`.',
                'code_example' => '#[ObservedBy(UserObserver::class)]
class User extends Model {}

class UserObserver {
    public function creating(User $user): void {
        $user->slug = Str::slug($user->name);
    }
    public function deleted(User $user): void {
        Mail::to($user)->send(new GoodbyeMail());
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.eloquent_advanced',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Какие события генерируют Eloquent-модели?',
                'answer' => 'Eloquent-модели генерируют события на каждом этапе жизненного цикла. На них можно подписаться через **Observer**, **`booted()`** или статические listeners.

**Полный список событий:**

| Событие | Когда срабатывает |
|---|---|
| **`retrieved`** | Модель извлечена из БД (после `find`/`get`/`first`) |
| **`creating`** | Перед INSERT — можно отменить через `return false` |
| **`created`** | После INSERT |
| **`updating`** | Перед UPDATE — можно отменить |
| **`updated`** | После UPDATE |
| **`saving`** | Перед `creating` или `updating` — общий обработчик |
| **`saved`** | После `created` или `updated` |
| **`deleting`** | Перед DELETE |
| **`deleted`** | После DELETE |
| **`restoring`** | Перед `restore()` (soft delete) |
| **`restored`** | После `restore()` |
| **`replicating`** | При `$model->replicate()` |
| **`trashed`** | После soft delete (Laravel 11+) |
| **`forceDeleting`** / **`forceDeleted`** | При `forceDelete()` (soft delete) |

**Порядок при `save()`:**

- **Новая запись:** `saving` → `creating` → INSERT → `created` → `saved`.
- **Существующая:** `saving` → `updating` → UPDATE → `updated` → `saved`.

**Подводные камни:**

- **Bulk через query builder** (`User::where(...)->update(...)`) — события **не срабатывают**.
- **`saving`/`creating`/`updating`/`deleting`** могут вернуть `false` и **отменить** операцию без исключения. `$model->save()` вернёт `false` тихо.
- **`saveQuietly()`/`updateQuietly()`/`deleteQuietly()`** — выполнить без срабатывания событий.',
                'code_example' => 'protected static function booted(): void {
    static::creating(function (User $user) {
        $user->uuid = Str::uuid();
    });

    static::deleted(function (User $user) {
        Cache::forget("user.{$user->id}");
    });
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.eloquent_advanced',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Soft Deletes?',
                'answer' => '**Soft Delete** — «мягкое удаление»: запись **не удаляется физически**, а в столбце `deleted_at` ставится текущая дата.

- Запись помечается удалённой, но остаётся в БД.
- По умолчанию такие записи **скрыты** во всех выборках (через глобальный scope).
- Подключается трейтом **`Illuminate\\Database\\Eloquent\\SoftDeletes`** на модели и `$table->softDeletes()` в миграции.

Зачем:

- Возможность **восстановить** через `restore()`.
- История/аудит — данные не теряются.
- «Корзина» в админке.

Минусы: уникальные индексы на `email`/`slug` могут ломаться (живые + удалённые конкурируют за уникальность).',
                'code_example' => 'use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model {
    use SoftDeletes;
}

$post->delete();           // soft delete
$post->forceDelete();      // hard delete
$post->restore();          // восстановить

Post::withTrashed()->get();   // включая удалённые
Post::onlyTrashed()->get();   // только удалённые',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.eloquent_advanced',
            ],
            [
                'category' => 'Laravel',
                'question' => 'SoftDeletes ломает unique-индекс на email - как это правильно решать?',
                'answer' => '**Классическая боль:** на `users.email` стоит `UNIQUE`. Юзер регистрируется → удаляет аккаунт (`deleted_at` заполняется) → пытается зарегистрироваться снова с тем же email → **`SQLSTATE 23000`/`23505`** (`duplicate entry`).

Для БД «удалённая» запись физически жива и всё ещё держит `email`. **БД-уровень уникальности про SoftDeletes ничего не знает.**

**Eloquent-валидация** `Rule::unique()->whereNull(\'deleted_at\')` это видит, но БД — нет.

**Сравнение решений:**

| Решение | СУБД | Плюсы | Минусы |
|---|---|---|---|
| **Partial unique index** | **Postgres** ✅ | Самое элегантное — uniqueness только для живых | `Schema::table` это не умеет, нужен `DB::statement(...)` |
| **Sentinel вместо NULL + composite UNIQUE** | MySQL/MariaDB | Работает на любой версии MySQL | Нужен `\'1970-01-01\'` дефолт + override SoftDeletes |
| **Generated column** `email_unique = (deleted_at IS NULL)` | MySQL 5.7+/MariaDB | Не трогать модель | Дополнительная колонка, читать миграции |
| **Анонимизация при `deleting`** | Любая | Простое, СУБД-независимое | Email потерян → восстановить нельзя |
| **Hard delete + архивная таблица** | Любая | Чистая модель данных | Foreign keys потеряют связь, ручная миграция |

**Главная ловушка MySQL — наивный `UNIQUE (email, deleted_at)`:**

- В MySQL для UNIQUE-индекса **`NULL != NULL`**.
- Поэтому `(\'a@b.c\', NULL)` и `(\'a@b.c\', NULL)` считаются **разными парами**.
- БД пропустит **ДВУХ ЖИВЫХ юзеров** с одним email — **уничтожает уникальность активных**.

Поэтому в MySQL нужен **sentinel** (`\'1970-01-01\'`) вместо NULL у живых, **либо** generated column.

**Postgres путь (рекомендуемый):**

```php
DB::statement(\'CREATE UNIQUE INDEX users_email_active ON users (email) WHERE deleted_at IS NULL\');
```

**Выбор зависит от:**

- Нужно ли **восстанавливать аккаунт** → не аноним.
- Есть ли **GDPR/right-to-be-forgotten** → лучше hard delete + анонимизация.
- Какая **СУБД** — Postgres сильно проще.',
                'code_example' => '<?php
// Postgres - partial index в миграции
Schema::create("users", function (Blueprint $t) {
    $t->id();
    $t->string("email");
    $t->softDeletes();
});
DB::statement("CREATE UNIQUE INDEX users_email_active
               ON users (email) WHERE deleted_at IS NULL");

// MySQL - составной UNIQUE с sentinel-значением вместо NULL для живых
// (наивный UNIQUE(email, deleted_at) с deleted_at=NULL НЕ работает:
//  NULL != NULL → MySQL пропустит двух живых с одним email)
Schema::table("users", function (Blueprint $t) {
    $t->timestamp("deleted_at")->nullable(false)->default("1970-01-01 00:00:00")->change();
    $t->unique(["email", "deleted_at"]);
});
// Модель: переопределить SoftDeletes так, чтобы trash ставил now(),
// а "живой" статус = sentinel-дата, а не NULL.

// Анонимизация при удалении (универсальный способ)
class User extends Model {
    use SoftDeletes;
    protected static function booted(): void {
        static::deleting(function (User $u) {
            if ($u->isForceDeleting()) return;
            $u->forceFill(["email" => "deleted_{$u->id}@invalid"])->saveQuietly();
        });
    }
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.eloquent_advanced',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как управлять timestamps в Eloquent?',
                'answer' => 'По умолчанию у модели Eloquent есть поля **`created_at`** и **`updated_at`** — заполняются автоматически при `create` / `save`.

Что можно настраивать:

- **`public $timestamps = false`** — полностью отключить.
- **`protected $dateFormat = \'U\'`** — формат хранения (например, unix timestamp).
- **`const CREATED_AT = \'creation_date\'`**, **`const UPDATED_AT = \'last_update\'`** — другие имена колонок.
- **`$model->timestamps = false`** перед `save()` — точечно не трогать `updated_at` на одной записи.
- **`updateQuietly([...])`** — обновить **без** событий `saving/saved/updating/updated` (Observer/Listener не сработают).

Миграция: одной строкой `$table->timestamps()` создаёт обе колонки.',
                'code_example' => 'class Post extends Model {
    public $timestamps = true;
    const CREATED_AT = \'creation_date\';
    const UPDATED_AT = \'last_update\';
}

// Обновить без изменения updated_at
$post->timestamps = false;
$post->save();

// или quietly - без срабатывания событий (saving/saved/updating/updated)
$post->updateQuietly([\'views\' => $post->views + 1]);',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.eloquent_advanced',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое chunk, chunkById, lazy и cursor в Eloquent? В чём разница и где ловушка?',
                'answer' => '**Четыре способа обработать большие выборки** без OOM, с разной семантикой и ловушками.

| Метод | Механика | Память | Соединение | Eager loading |
|---|---|---|---|---|
| **`chunk(N, $cb)`** | `LIMIT N OFFSET ...` | `O(N)` на чанк | Закрыто между чанками | Да |
| **`chunkById(N, $cb)`** | `WHERE id > $lastId LIMIT N` | `O(N)` на чанк | Закрыто между чанками | Да |
| **`lazy(N)` / `lazyById(N)`** | То же, через `LazyCollection`/генератор | `O(N)` | Закрыто между чанками | Да |
| **`cursor()`** | Серверный **SQL-курсор**, по одной записи | **`O(1)`** | **Открыто** до конца итерации | **Нет** |

**Критическая ловушка `chunk` при UPDATE/DELETE — пропуск записей:**

Если внутри callback вы изменяете записи так, что они **перестают подпадать под исходный `where`**, происходит **сдвиг OFFSET** и **половина записей пропускается**.

**Механика:**

1. `where(\'processed\', false)`, `chunk(1000)`.
2. Первый запрос: `LIMIT 1000 OFFSET 0` → строки 0-999, обновили → ушли из выборки.
3. Второй запрос: `LIMIT 1000 OFFSET 1000` — но строки 1000-1999 теперь **сдвинулись в позицию 0-999**, а OFFSET 1000 указывает на 2000-2999.
4. **Пропустили 1000 записей.**

**Решение — `chunkById()`:**

- Использует **`WHERE id > $lastId`** вместо нестабильного OFFSET.
- Устойчив к изменению набора записей внутри callback.
- **Канон для миграций данных и любых UPDATE/DELETE в callback.**

**Тот же риск в обратную сторону при INSERT** в обрабатываемую таблицу.

**Отдельное требование `chunkById`/`lazyById`:**

- Колонка `$column` (дефолт `id`) должна быть **строго монотонно возрастающей и уникальной**.
- На неуникальной (`created_at` с дублями, `status`, низкоразрядный timestamp) механизм `WHERE column > $lastValue` **пропустит** записи с тем же значением, что у границы чанка.
- Если такой колонки нет — `chunkById` по PK дополнительно фильтрует нужный `where`, либо **keyset pagination на составном ключе**: `WHERE (sort_col, id) > (?, ?)`.

**Когда выбирать что:**

- **Read-only проход по диапазону, который не меняется** — `chunk()`.
- **UPDATE/DELETE внутри callback** — **`chunkById()` обязательно**.
- **Стрим с минимальной памятью, без eager-load связей** — `cursor()` (открытое соединение нюанс).
- **Унифицированный API через генератор** — `lazy()`/`lazyById()`.

**Доп. инструменты:**

- **`chunkByIdDesc()`** — обратное направление.
- **`cursor()` + `toBase()`** — `O(1)` память **и** без гидратации (stdClass).',
                'code_example' => '<?php
// ❌ Опасно: chunk + UPDATE условия фильтра - пропуски записей
User::where("notified", false)->chunk(1000, function ($users) {
    foreach ($users as $u) {
        Mail::send(new Notify($u));
        $u->update(["notified" => true]);
    }
});
// первый chunk: 1000 строк, OFFSET=0 - обработали и пометили
// записи "сдвинулись"; второй chunk OFFSET=1000 пропускает половину

// ✅ Правильно: chunkById использует WHERE id > $lastId
User::where("notified", false)->chunkById(1000, function ($users) {
    foreach ($users as $u) {
        Mail::send(new Notify($u));
        $u->update(["notified" => true]);
    }
});

// ✅ Lazy-вариант для UPDATE
User::where("notified", false)->lazyById()->each(function ($u) {
    /* ... */
});

// chunk - для read-only прохода по диапазону, который не меняется
Order::where("created_at", "<", $cutoff)->chunk(500, fn ($orders) => /* ... */);

// cursor - минимум памяти, без eager loading, с открытым курсором
foreach (User::where("active", true)->cursor() as $user) { /* ... */ }',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.eloquent_advanced',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое whereHas и whereDoesntHave?',
                'answer' => '**`whereHas`** — фильтрует основные записи по **наличию** связанных записей с условием. **`whereDoesntHave`** — наоборот, по **отсутствию**.

**Пример:** «юзеры, у которых есть хотя бы один опубликованный пост»:

```php
User::whereHas(\'posts\', fn ($q) => $q->where(\'published\', true))->get();
```

Под капотом — `WHERE EXISTS (SELECT 1 FROM posts WHERE user_id = users.id AND published = 1)`.

**Семейство методов:**

| Метод | SQL | Когда |
|---|---|---|
| **`has(\'posts\')`** | `EXISTS (...)` | Просто наличие хотя бы одной связи |
| **`has(\'posts\', \'>=\', 3)`** | `(SELECT COUNT ...) >= 3` | По количеству |
| **`whereHas(\'posts\', $closure)`** | `EXISTS (... WHERE ...)` | С условием на связь |
| **`whereDoesntHave(\'posts\', $closure)`** | `NOT EXISTS (... WHERE ...)` | Без связи (с условием) |
| **`orWhereHas(\'posts\', $closure)`** | `OR EXISTS (...)` | Логическое OR |
| **`whereRelation(\'posts\', \'published\', true)`** | Шорткат для одного `where` | Лаконичнее `whereHas` для простых условий |

**Подводные камни:**

- **Производительность** — `whereHas` использует `EXISTS`, который обычно быстрее `IN (subquery)` на больших таблицах, **но** требует индекс на FK связанной таблицы.
- **`whereHas` ≠ `with`** — `whereHas` **фильтрует** основной запрос, **не загружает** связи. Часто их используют вместе: `User::whereHas(\'posts\', ...)->with(\'posts\')`.
- **`whereDoesntHave` без условия** — `whereDoesntHave(\'posts\')` = «юзеры без постов вообще».
- **Глубокая вложенность** — `whereHas(\'posts.comments\', ...)` работает, но генерит вложенные EXISTS — смотрите EXPLAIN.',
                'code_example' => 'User::whereHas(\'posts\', function ($q) {
    $q->where(\'published\', true);
})->get();

User::whereDoesntHave(\'posts\')->get(); // без постов',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.eloquent_advanced',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое lockForUpdate и sharedLock?',
                'answer' => '**Пессимистические блокировки строк** на уровне транзакции — используются для борьбы с **race conditions** при `read-modify-write`.

**Сравнение:**

| Метод | SQL | Кому блокирует |
|---|---|---|
| **`lockForUpdate()`** | `SELECT ... FOR UPDATE` | Другим транзакциям нельзя ни читать с lock, ни писать |
| **`sharedLock()`** | `SELECT ... FOR SHARE` (PG) / `LOCK IN SHARE MODE` (MySQL) | Другим можно читать, но **нельзя писать** |

**Зачем нужны (классическая задача — списать с баланса):**

1. T1: `SELECT balance FROM accounts WHERE id = 1` → 100.
2. T2: `SELECT balance FROM accounts WHERE id = 1` → 100 (тоже видит 100!).
3. T1: `UPDATE ... balance = 100 - 50` → 50.
4. T2: `UPDATE ... balance = 100 - 30` → 70.

**Итог:** списали 80, но баланс 70 вместо 20 — **lost update**.

С `lockForUpdate()` шаг 2 будет **ждать** окончания T1 → читает уже 50.

**Подводные камни:**

- **Только внутри транзакции** — без `DB::transaction()` lock не имеет смысла, он снимается сразу.
- **`SKIP LOCKED`** (Laravel 9+: `->lockForUpdate(skipLocked: true)`) — пропустить уже залоченные строки. Канонический паттерн для **work queues** на БД: разные воркеры берут разные задачи без блокировки друг друга.
- **`NOWAIT`** — упасть сразу, если строка занята, вместо ожидания.
- **Deadlock** — два процесса берут локи в **обратном порядке** → БД убивает одного из них. Решение — фиксированный порядок локов + `DB::transaction($cb, attempts: 3)`.
- **Оптимистическая альтернатива** — `version`-колонка + `UPDATE ... WHERE version = ?` (без блокировок, но retry на ошибке).',
                'code_example' => '<?php
// Классический банковский перевод - lockForUpdate защищает от lost update
DB::transaction(function () use ($fromId, $toId, $amount) {
    $from = Account::where(\'id\', $fromId)->lockForUpdate()->first();
    $to   = Account::where(\'id\', $toId)->lockForUpdate()->first();

    if ($from->balance < $amount) {
        throw new InsufficientFundsException();
    }

    $from->decrement(\'balance\', $amount);
    $to->increment(\'balance\', $amount);
}, attempts: 3); // retry на deadlock

// Work queue на БД - SKIP LOCKED, чтобы воркеры брали РАЗНЫЕ задачи
DB::transaction(function () {
    $job = Job::where(\'status\', \'pending\')
        ->lockForUpdate(skipLocked: true) // пропустить занятые
        ->first();
    if (! $job) return;
    $job->update([\'status\' => \'processing\']);
    // ... обработка
});

// sharedLock - "никто не должен изменить, пока я смотрю"
DB::transaction(function () use ($userId) {
    $user = User::where(\'id\', $userId)->sharedLock()->first();
    // другие могут читать $user, но UPDATE будут ждать commit
    audit($user);
});',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.eloquent_advanced',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что делает DB::afterCommit?',
                'answer' => '**`DB::afterCommit($callback)`** регистрирует callback, который выполнится **только после успешного COMMIT** самой внешней транзакции. При rollback (любом — внешнем или внутреннем savepoint, который потом откатится) — **не выполнится**.

**Зачем нужен — классический баг без `afterCommit`:**

```php
DB::transaction(function () use ($order) {
    $order->save();
    SendOrderEmail::dispatch($order); // job ушёл в очередь сразу
    throw new \\Exception(\'oops\');     // транзакция откачена,
                                       // но job УЖЕ в Redis - воркер пытается
                                       // обработать несуществующий order
});
```

**Где может выстрелить:**

- **Queue jobs** — worker подхватил job до того, как транзакция закоммитилась → читает старые данные или 404.
- **Broadcasting/events** — пользователь получил уведомление о ещё не сохранённой записи.
- **External API calls** — отправили webhook, а наша запись «откатилась».

**Способы — в порядке предпочтения:**

| Способ | Где описать |
|---|---|
| **`ShouldQueueAfterCommit`** интерфейс на Job | Самый чистый (L10+) — Job сам решает |
| **`$afterCommit = true`** на Job/Event/Listener | Свойство класса |
| **`DB::afterCommit(fn () => ...)`** | Разово, прямо в коде |
| **`Bus::dispatchAfterCommit($job)`** | Принудительно для одного dispatch |

**Подводные камни:**

- **Только в Eloquent/`DB`-транзакциях** — если ваш код **не в транзакции**, callback выполнится **немедленно** (как обычный).
- **Вложенные транзакции** (savepoint) — `afterCommit` сработает после **самого внешнего** commit. Откат внешней транзакции отменит callback, даже если «внутренний savepoint закоммитился».
- **`config/queue.php` → `after_commit` => true** — глобальный дефолт для всех Job (вместо явных свойств).',
                'code_example' => '<?php
// 1. Разовый вариант - DB::afterCommit
DB::transaction(function () use ($order) {
    $order->save();
    DB::afterCommit(fn () => SendOrderConfirmation::dispatch($order));
});

// 2. Свойство на Job - универсально
class SendOrderEmail implements ShouldQueue {
    public bool $afterCommit = true; // или реализовать ShouldQueueAfterCommit

    public function __construct(public Order $order) {}
    public function handle(): void { /* ... */ }
}

// Теперь можно безопасно из транзакции:
DB::transaction(function () use ($order) {
    $order->save();
    SendOrderEmail::dispatch($order); // отложится до COMMIT
});

// 3. Принудительно через Bus
Bus::dispatchAfterCommit(new SendOrderEmail($order));

// 4. Глобально - config/queue.php
return [
    \'after_commit\' => true, // все Job по умолчанию ждут commit
];

// 5. На Event/Listener
class OrderShipped {
    public bool $afterCommit = true;
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.eloquent_advanced',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как делать пагинацию в Laravel?',
                'answer' => 'У Eloquent и Query Builder есть **три варианта пагинации** с разной семантикой и стоимостью.

**Сравнение:**

| Метод | Что возвращает | Доп. запрос на `total` | Когда выбирать |
|---|---|---|---|
| **`paginate(15)`** | `LengthAwarePaginator` | **Да** (`COUNT(*)`) | Стандартная веб-пагинация с номерами страниц |
| **`simplePaginate(15)`** | `Paginator` | Нет — берёт 16 и проверяет «есть ли ещё» | Когда total не нужен (мобильный список) |
| **`cursorPaginate(15)`** | `CursorPaginator` | Нет — keyset-пагинация | Большие таблицы, infinite scroll, нет «прыжков» на изменение данных |

**Чем `cursorPaginate` принципиально отличается:**

- Использует **WHERE id > $lastId** вместо `OFFSET` → быстро даже на миллионах записей.
- **Невосприимчив к вставкам/удалениям** — нет «дубликатов на следующей странице».
- Требует **уникальный, монотонный** order column (обычно PK или `created_at` + tiebreaker).
- **Нельзя прыгнуть на страницу N** — только next/prev (это особенность keyset-пагинации).

**Дополнительные методы:**

- `appends($request->query())` — сохранить GET-параметры в links.
- `withQueryString()` — то же, короче.
- `onEachSide(2)` — сколько кнопок страниц показывать вокруг текущей.

**Для API:**

- `UserResource::collection(User::paginate(15))` — корректно формирует `data`/`meta`/`links`.
- Resource-collection автоматически добавляет meta пагинации.

**Подводный камень `paginate`:**

- `COUNT(*)` на больших таблицах с JOIN-ами может быть **дороже** основной выборки. На таблицах 100M+ — переходить на `simplePaginate`/`cursorPaginate`.',
                'code_example' => '$users = User::paginate(15);
$users = User::simplePaginate(15);
$users = User::cursorPaginate(15);

// API
return UserResource::collection(User::paginate(15));

// Blade
{{ $users->links() }}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.eloquent_advanced',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как реализовать поиск с фильтрами в Eloquent?',
                'answer' => 'Стандартный паттерн — **условные методы `when()`** Query Builder, которые применяют callback **только если условие truthy**.

**Идея:** для каждого фильтра проверяем, передан ли он, и добавляем нужный `where` без ifs.

**Базовый пример:**

```php
Post::query()
    ->when($r->search, fn ($q, $s) => $q->where(\'title\', \'like\', "%$s%"))
    ->when($r->category, fn ($q, $c) => $q->where(\'category_id\', $c))
    ->when($r->author, fn ($q, $a) => $q->whereHas(\'author\', fn ($qa) => $qa->where(\'id\', $a)))
    ->orderBy(\'created_at\', \'desc\')
    ->paginate(15);
```

**Преимущества `when()` над `if`:**

- **Цепочка не разрывается** — читается как декларативный набор фильтров.
- **Лямбда получает значение как второй аргумент** — нет повторной проверки.
- **Есть `unless()`** — обратное условие.

**Популярные альтернативы:**

- **`spatie/laravel-query-builder`** — декларативные фильтры, сортировки, includes через query-параметры (`?filter[status]=published&sort=-created_at`). Хорошо для API.
- **Pipeline-паттерн** — каждый фильтр в отдельном классе, что упрощает тестирование.

**Подводные камни:**

- **`like \'%query%\'`** — full table scan на больших таблицах. Для серьёзного поиска нужен **полнотекстовый индекс** (MySQL `FULLTEXT`, PG `tsvector`) или **Laravel Scout** с MeiliSearch/Algolia.
- **SQL-injection при сортировке** — `orderBy($request->sort)` опасен, нужен **whitelist** разрешённых колонок.
- **Двойной запрос на `paginate`** — `COUNT(*)` + основной. На сложных фильтрах с `whereHas` стоимость растёт.',
                'code_example' => 'public function index(Request $request) {
    return Post::query()
        ->when($request->search, fn($q, $s) =>
            $q->where(\'title\', \'like\', "%$s%"))
        ->when($request->category, fn($q, $c) =>
            $q->where(\'category_id\', $c))
        ->when($request->author, fn($q, $a) =>
            $q->whereHas(\'author\', fn($qa) => $qa->where(\'id\', $a)))
        ->orderBy(\'created_at\', \'desc\')
        ->paginate(15);
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.eloquent_advanced',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Чем Eloquent Observer отличается от Event/Listener и когда выбирать что?',
                'answer' => 'Это **два разных уровня абстракции** — Observer завязан на жизненный цикл **конкретной модели**, Event/Listener — на **любое доменное событие**.

**Сравнение:**

| Параметр | **Observer** | **Event/Listener** |
|---|---|---|
| К чему привязан | К **модели** (`User`, `Order`) | К **доменному событию** (`OrderShipped`) |
| Триггер | `creating`/`saved`/`deleted` — встроенные lifecycle | Явный `event(new OrderShipped(...))` |
| Семантика | «**когда модель меняется**» | «**когда произошло бизнес-событие**» |
| Подписчиков | Один Observer на модель | Сколько угодно `Listener` на одно событие |
| Async | По умолчанию синхронно | Listener реализует `ShouldQueue` → в очередь |
| Регистрация (L11) | `#[ObservedBy]` на модели или `Model::observe()` | Auto-discovery (если включен) или явный `Event::listen()` |
| Bulk-операции | **НЕ срабатывает** (`Model::where(...)->update(...)`) | Срабатывает, если вы сами вызвали `event()` |

**Когда выбирать что:**

| Задача | Что выбрать |
|---|---|
| Slug/UUID при `creating`, timestamps, audit-log | **Observer** — данные модели сами по себе |
| `Cache::forget()` после `saved`/`deleted` | **Observer** — инвариант кеша вокруг записи |
| Каскадное удаление детей в `deleting` | **Observer** — внутри жизненного цикла |
| «Заказ оплачен → уведомить юзера + Slack + аналитику + bonus-points» | **Event** + 4 Listener-а (можно queue-async) |
| Интеграция с внешним сервисом из несвязанного домена | **Event** — слабая связанность |
| Логика, которая хочет async по умолчанию | **Event** + `ShouldQueue` Listener |

**Главное правило:** Observer = «**что-то происходит с моделью**», Event = «**случилось бизнес-событие**».

**Подводные камни:**

- **Observer не срабатывает на bulk** — `User::where(...)->update(...)` идёт в БД без hydration. Нужен `foreach` или `chunkById`.
- **Observer в Event-стиле** (Observer диспатчит `event()`) — нормальная связка: `created` в Observer → `event(new UserRegistered)` → Listener-ы.
- **Транзакции** — для Listener-ов и Job-ов на событиях из транзакции используйте `ShouldQueueAfterCommit` или `$afterCommit = true`, иначе уведомления уйдут до COMMIT.',
                'code_example' => '<?php
// === Observer - привязан к модели ===
#[ObservedBy(UserObserver::class)] // Laravel 11+
class User extends Model {}

class UserObserver {
    public function creating(User $u): void {
        $u->uuid = Str::uuid();          // данные самой модели
    }
    public function created(User $u): void {
        // тригерим доменное событие - дальше шину слушают независимые Listener-ы
        event(new UserRegistered($u));
    }
    public function deleting(User $u): void {
        $u->posts()->delete();           // каскад внутри lifecycle
    }
}

// === Event + несколько Listener ===
class UserRegistered {
    public function __construct(public User $user) {}
}

class SendWelcomeEmail implements ShouldQueue {
    public bool $afterCommit = true;
    public function handle(UserRegistered $e): void {
        Mail::to($e->user)->send(new WelcomeMail());
    }
}

class NotifySlack implements ShouldQueue {
    public function handle(UserRegistered $e): void { /* Slack webhook */ }
}

class GrantBonusPoints {
    public function handle(UserRegistered $e): void { /* sync, в той же транзакции */ }
}

// Laravel 11 - auto-discovery подхватит Listener-ы по type-hint первого аргумента
// (если EventServiceProvider не выключил discovery в bootstrap/providers.php)',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.eloquent_advanced',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как избежать N+1 при полиморфных связях morphTo?',
                'answer' => '**Особенность `morphTo`:** под одной полиморфной связью **разные модели** (`Post`, `Video`, `Photo`) — Laravel **не может загрузить всё одним SQL**, потому что у них разные таблицы.

**Что делает обычный `with(\'commentable\')`:**

- `SELECT * FROM comments` — основная выборка.
- Laravel группирует по `commentable_type`.
- Для **каждого типа** — **отдельный** запрос: `SELECT ... FROM posts WHERE id IN (...)`, `SELECT ... FROM videos WHERE id IN (...)`.

Итого: `1 + T` запросов, где `T` — число **уникальных типов** (обычно 2-3). Это **уже не N+1**, а константно-малое число.

**Когда N+1 всё-таки появляется — на связях ВНУТРИ полиморфной модели:**

- Подгрузили `comment.commentable` (`Post`), а у `Post` ещё есть `author` → N+1 на каждом посте.

**Решение — `morphWith()` в замыкании:**

- Передать closure типа `MorphTo`-builder.
- Указать вложенные связи **per type** через `morphWith([Type::class => [\'relation\']])`.

**Дополнительные приёмы:**

| Приём | Зачем |
|---|---|
| **`Relation::morphMap([...])`** в `AppServiceProvider::boot` | Хранить **строковые алиасы** (`\'post\'`) вместо FQCN — устойчиво к рефакторингу пространств имён |
| **`morphWithCount()`** | Аналог `withCount` per type |
| **`whereHasMorph(\'commentable\', [Post::class, Video::class], $closure)`** | Фильтр с условиями на полиморфного родителя |
| **Денормализация** — отдельные FK | Если 95% запросов берут конкретный тип — иногда дешевле сделать обычную `belongsTo` |

**Подводные камни:**

- **Индексы** — composite index `(commentable_type, commentable_id)` обязателен, иначе full scan.
- **FK в БД невозможен** — целостность только на уровне приложения; orphaned-комментарии после удаления родителя — типичная боль (решение — Observer на `deleting`).
- **Без `morphMap`** — рефакторинг `App\\Models\\Post` → `App\\Domain\\Blog\\Post` сломает все исторические `commentable_type`.',
                'code_example' => '<?php
// 1. morphMap - фиксирует короткие алиасы в БД, устойчиво к рефакторингу
// AppServiceProvider::boot
use Illuminate\\Database\\Eloquent\\Relations\\Relation;

Relation::morphMap([
    \'post\'  => Post::class,
    \'video\' => Video::class,
    \'photo\' => Photo::class,
]);
// теперь в БД лежит "post" вместо "App\\\\Models\\\\Post"

// 2. morphWith - eager-load связей ВНУТРИ полиморфного родителя
use Illuminate\\Database\\Eloquent\\Relations\\MorphTo;

$comments = Comment::with([
    \'commentable\' => function (MorphTo $morphTo) {
        $morphTo->morphWith([
            Post::class  => [\'author\', \'category\'],
            Video::class => [\'channel\'],
            Photo::class => [],
        ]);
    },
])->get();

// SQL: 1 (comments) + 3 (posts/videos/photos) + 2 (authors, channels) = 6 фиксированных запросов
// вместо ~1 + N (по комменту на каждую вложенную связь)

// 3. morphWithCount - агрегат per type
Comment::with([
    \'commentable\' => fn (MorphTo $m) => $m->morphWithCount([
        Post::class => [\'likes\'],
    ]),
])->get();

// 4. Фильтр - whereHasMorph
Comment::whereHasMorph(
    \'commentable\',
    [Post::class, Video::class],
    fn ($q, $type) => $q->where(\'published\', true),
)->get();

// 5. Защита на этапе разработки - prevent lazy load для morphTo тоже работает
Model::preventLazyLoading(! app()->isProduction());',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.eloquent_advanced',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Чем отличаются local query scope от global scope и какие подводные камни у global?',
                'answer' => '**Два механизма инкапсуляции query-условий с принципиально разной семантикой.**

| Параметр | **Local scope** | **Global scope** |
|---|---|---|
| Применение | **Явное** — `User::active()->get()` | **Автоматически** ко всем запросам модели |
| Объявление | Метод `scopeActive(Builder $q)` или `#[Scope]` (L11+) | Класс с `Scope` или closure в `booted()` |
| Видимость | Очевиден из кода | **Невидим** — нужно знать о его существовании |
| Отключение | Не нужно — не активен по умолчанию | `withoutGlobalScope(\'tenant\')` / `withoutGlobalScopes()` |
| Типичный use case | `active`, `published`, `recent` | Soft delete, multi-tenancy, скрытие черновиков |

**Подводные камни global scope (главная боль):**

**1. Невидимая магия — «куда делись записи?»**

- Новичок пишет `Post::find($id)` и получает `null` — потому что `TenantScope` отфильтровал.
- Симптом: «у меня в БД запись лежит, а Laravel её не видит». Документируйте global scopes **обязательно** в `README.md` модели.

**2. Утечка контекста в Job/Queue:**

```php
static::addGlobalScope(\'tenant\', function (Builder $b) {
    $b->where(\'tenant_id\', auth()->user()->tenant_id);
});
```

- Job сериализуется → попадает в Redis → воркер забирает через 10 минут.
- В воркере **`auth()->user()` = `null`** (нет HTTP-запроса) → `tenant_id` = `null` → запрос вернёт пусто или упадёт.
- Решение: фиксировать `tenant_id` **явно** в конструкторе Job, передавать через контейнер, или применять scope в момент выполнения, а не на этапе resolve.

**3. Системные задачи нужно «снимать»:**

- Админка, импорты, миграции данных: `Post::withoutGlobalScope(TenantScope::class)->...`.
- Все воркеры/cron — проверяйте, не нужен ли снять.

**4. Сломанные связи:**

- `$user->posts` через `belongsTo`/`hasMany` **тоже применяет** global scope `Post`. Если scope зависит от `auth()`, а связь грузится из cron — пусто.

**5. Octane/RoadRunner — scope считается один раз при boot:**

- Если `booted()` использует `auth()`, на новых запросах оно уже другое. Используйте closure (которое выполнится при каждом запросе), а не значение.

**Когда global выбирать — только когда нужно ВСЕГДА, по всему приложению** (SoftDeletes — канонический пример). Иначе — local scope.',
                'code_example' => '<?php
// === Local scope - явный, безопасный ===
class User extends Model {
    public function scopeActive(Builder $q): Builder {
        return $q->where(\'active\', true);
    }

    // Laravel 11+ - через атрибут, без префикса
    #[Scope]
    protected function popular(Builder $q, int $min = 1000): Builder {
        return $q->where(\'views\', \'>=\', $min);
    }
}

User::active()->popular()->get();

// === Global scope - класс ===
class TenantScope implements Scope {
    public function apply(Builder $b, Model $m): void {
        // ✅ Closure внутри apply - вычисляется в момент SQL, не при boot
        if ($tenantId = auth()->user()?->tenant_id) {
            $b->where(\'tenant_id\', $tenantId);
        }
    }
}

class Post extends Model {
    protected static function booted(): void {
        static::addGlobalScope(new TenantScope);
    }
}

// === Снять scope - обязательно для системных задач ===
Post::withoutGlobalScope(TenantScope::class)->get();      // снять конкретный
Post::withoutGlobalScopes()->get();                       // снять ВСЕ
Post::withoutGlobalScope(\'tenant\')->get();                // если scope-closure именованный

// === Job-friendly паттерн - фиксируем tenant в конструкторе ===
class ExportTenantPosts implements ShouldQueue {
    public function __construct(public int $tenantId) {} // не auth()

    public function handle(): void {
        Post::withoutGlobalScope(TenantScope::class)
            ->where(\'tenant_id\', $this->tenantId)
            ->chunkById(1000, fn ($posts) => /* ... */);
    }
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.eloquent_advanced',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что произойдёт, если вызвать $user->posts во foreach без with("posts")?',
                'answer' => 'Это классический **N+1**: для каждого юзера выполнится отдельный `SELECT * FROM posts WHERE user_id = ?`.

**Math:** 100 юзеров → 1 (`users`) + 100 (`posts`) = **101 запрос** вместо 2.

**Решение — `with(\'posts\')`:**

- `SELECT * FROM users` — основная выборка.
- `SELECT * FROM posts WHERE user_id IN (1, 2, ...)` — все связи одним запросом.
- Итого **2 запроса** независимо от размера выборки.

**Защита на этапе разработки:**

```php
// AppServiceProvider::boot()
Model::preventLazyLoading(! app()->isProduction());
```

При попытке lazy-load в dev упадёт `LazyLoadingViolationException` — баг ловится сразу, а не в проде по логам.

**Подводный камень `limit()` внутри `with`:**

| Версия | Поведение |
|---|---|
| **Laravel 11+** | **Per-parent limit** — 5 постов на КАЖДОГО юзера. В ядре реализован свой механизм. |
| **Laravel ≤10** | Лимит на **общую** eager-load выборку — 5 постов суммарно на ВСЕХ юзеров. Классическая ловушка. |

**Альтернативы для L≤10 или для «одного последнего»:**

- **`hasOne()->latestOfMany()`** — встроенная связь «hasOne по последней записи».
- **Subquery через `addSelect`** — одно поле из связанной таблицы без полного `with`.
- **`staudenmeir/eloquent-eager-limit`** — пакет для per-parent limit в старых версиях.
- **Window functions с `ROW_NUMBER() OVER (PARTITION BY ...)`** — для top-N связей.',
                'code_example' => '<?php
// AppServiceProvider::boot
Model::preventLazyLoading(! app()->isProduction());

// Laravel 11+: per-parent limit, по 5 постов на каждого юзера
$users = User::with(["posts" => fn ($q) => $q->latest()->limit(5)])->get();

// Laravel ≤10 - такая же запись даст 5 постов на ВСЮ выборку, не на юзера.
// Правильный путь: latestOfMany для "одного последнего"
class User extends Model {
    public function latestPost(): HasOne {
        return $this->hasOne(Post::class)->latestOfMany();
    }
}
$users = User::with("latestPost")->get();',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.eloquent_advanced',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как работают Laravel-транзакции с deadlock и как их повторять?',
                'answer' => '**`DB::transaction($callback, $attempts)`** автоматически **повторяет** callback при ошибках **конкуренции** — но **не при любом** `QueryException`.

**Что считается concurrency error** (повторяется):

| SQLSTATE / Сообщение | СУБД | Что это |
|---|---|---|
| **`40001`** Serialization failure | Postgres (канон) | Конфликт сериализации в `SERIALIZABLE` |
| **`Deadlock found when trying to get lock`** | MySQL (ER 1213) | Классический deadlock |
| **`deadlock detected`** | Postgres | То же на PG |
| **`Lock wait timeout exceeded`** | MySQL (ER 1205) | `innodb_lock_wait_timeout` истёк |
| **`database is locked`** | SQLite | WAL-блокировка |

Решение принимает `Illuminate\\Database\\DetectsConcurrencyErrors::causedByConcurrencyError($e)` — он матчит **SQLSTATE 40001** и **текстовые маркеры**.

**Что НЕ повторяется** (сразу пробрасывается):

- **`23000` / `23505`** — violation UNIQUE.
- **`23503`** — violation foreign key.
- **`23514`** — violation CHECK.
- Синтаксические ошибки SQL.
- **Lost connection** — отдельная ветка `causedByLostConnection()`, повторяется **только если транзакция ещё не начата**.

**Поведение по `attempts`:**

- **`attempts: 1`** (дефолт) — бросает первое же исключение.
- **`attempts: 3`** — между попытками **нет sleep** (отличие от Job `backoff`), повторение моментальное. На сильно нагруженной БД лучше комбинировать с `SKIP LOCKED` или backoff в коде.

**Вложенные транзакции (savepoint):**

- `DB::transaction` внутри другой — **SAVEPOINT trans2**, не новая транзакция.
- При deadlock на внутренней — Laravel `ROLLBACK TO SAVEPOINT` и пробует **только внутренний** callback заново. Внешняя транзакция продолжается.
- **`afterCommit`-хуки** сработают только после **внешнего** COMMIT.

**Подводные камни:**

- **Side effects в callback** — если внутри callback писали в Redis/файлы/отправляли HTTP — на retry это **повторится**. Решение: только БД-операции, остальное — `DB::afterCommit`.
- **`attempts` без `lockForUpdate`** — retry на deadlock без локов = маскировка race condition. Сначала разберитесь, почему deadlock возникает.
- **Канонический паттерн порядка локов** — всегда брать локи в **одном** порядке (например, по возрастанию `id`), чтобы избежать deadlock в принципе.',
                'code_example' => '<?php
// 1. Базовый retry на deadlock
DB::transaction(function () use ($from, $to, $sum) {
    // КАНОНИЧНО: брать локи в фиксированном порядке (по ID), иначе deadlock
    [$first, $second] = $from->id < $to->id ? [$from, $to] : [$to, $from];

    $first->lockForUpdate();
    $second->lockForUpdate();

    $from->refresh()->decrement(\'balance\', $sum);
    $to->refresh()->increment(\'balance\', $sum);
}, attempts: 3);

// 2. Что повторяется (deadlock) - что нет (unique violation)
try {
    DB::transaction(function () {
        User::create([\'email\' => \'taken@example.com\']);
    }, attempts: 3);
} catch (QueryException $e) {
    // Сюда попадаем СРАЗУ - 23000 не считается concurrency
    if ($e->getCode() === \'23000\') {
        return response(\'Email уже занят\', 422);
    }
    throw $e;
}

// 3. Опасный паттерн - side effects в transaction-callback
DB::transaction(function () use ($order) {
    $order->save();
    Mail::to($order->user)->send(new OrderPaid()); // ❌ на retry уйдёт ДВА письма
}, attempts: 3);

// ✅ Правильно - side effects через afterCommit
DB::transaction(function () use ($order) {
    $order->save();
    DB::afterCommit(fn () => Mail::to($order->user)->send(new OrderPaid()));
}, attempts: 3);

// 4. Вложенные - retry только внутренний callback
DB::transaction(function () {
    /* внешняя работа */
    DB::transaction(function () {
        /* при deadlock здесь - ROLLBACK TO SAVEPOINT + retry только этой части */
    }, attempts: 5);
});',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.eloquent_advanced',
            ],
            [
                'category' => 'Laravel',
                'question' => 'В чём разница между whereIn и whereIntegerInRaw, и когда выбирать второй?',
                'answer' => 'Оба строят `WHERE col IN (...)`, но **принципиально по-разному**.

| Параметр | **`whereIn($col, $array)`** | **`whereIntegerInRaw($col, $array)`** |
|---|---|---|
| Подстановка | PDO-bindings (`?`) на каждый элемент | Каждый элемент `(int)$value`, склейка строкой |
| SQL | `WHERE id IN (?, ?, ?, ...)` | `WHERE id IN (1, 2, 3, ...)` |
| Безопасность | Драйвер экранирует | `(int)` гарантирует, что инъекции нет |
| Лимит элементов | **65 535** (см. ниже) | Практически без лимита |
| Память на подготовку | Растёт линейно с размером | Маленькая (просто строка) |
| Типы | Любые (строки, UUID, числа) | **Только целые числа** |

**Откуда лимит 65 535 у `whereIn`:**

- В wire-протоколе **MySQL/MariaDB и PostgreSQL** число параметров prepared statement кодируется **2-байтовым полем** → потолок `0xFFFF` = **65 535** плейсхолдеров на запрос.
- В **SQL Server** лимит жёстче — **2 100**.
- **Не путать** с MySQL `max_prepared_stmt_count` (дефолт 16 382) — это число **одновременно живущих** prepared statements на сервере, а не параметров в одном.
- Помимо потолка большой `whereIn` нагружает парсер SQL и съедает память на подготовку.

**Когда выбирать `whereIntegerInRaw`:**

- **Импорт/sync** — обновить статус у 100 000 ID одной командой.
- **GDPR broadcast** — пометить пачку пользователей.
- **ETL** — массовые batch-операции, где ID берутся из доверенного источника (своя БД, не пользовательский ввод).

**Ограничения:**

- **Только целые числа** на одной колонке.
- Для **строк/UUID** — `whereIn` + `chunk` по 1000-5000: `collect($emails)->chunk(1000)->each(fn ($c) => User::whereIn(\'email\', $c->all())->update(...))`.
- Для **составных ключей** — JOIN со временной таблицей или CTE.

**Парный метод:** **`whereIntegerNotInRaw($col, $array)`** — инверсия (`NOT IN`).

**Альтернатива при подозрении на инъекцию** — `array_map(\'intval\', $ids)` руками + `whereIn` (то же самое, но через bindings — медленнее).',
                'code_example' => '<?php
// проблема - массив на 50_000 ID
$ids = User::where("region", "EU")->pluck("id")->all();

// whereIn: 50_000 плейсхолдеров - упрётся в лимит PDO/высокая память
User::whereIn("id", $ids)->update(["gdpr_notified_at" => now()]);

// whereIntegerInRaw: SQL вида WHERE id IN (1,2,3,...) без bindings
User::whereIntegerInRaw("id", $ids)->update(["gdpr_notified_at" => now()]);

// Также есть whereIntegerNotInRaw - инверсия

// Для строк - chunk
collect($emails)->chunk(1000)->each(function ($chunk) {
    User::whereIn("email", $chunk->all())->update([...]);
});',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.eloquent_advanced',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как настроить read/write connections в Laravel и что делает опция sticky?',
                'answer' => '**Read/Write split** — в `config/database.php` для одного соединения указываются **отдельные хосты** для чтения и записи. SELECT идут на реплику, `INSERT/UPDATE/DELETE` — на master. Горизонтально масштабирует read-heavy нагрузку.

**Главная проблема — replication lag:**

- Репликация **асинхронна**: десятки ms на лёгкой нагрузке, секунды под нагрузкой.
- SELECT сразу после INSERT может вернуть **старые данные или 404**.

**Опция `sticky` (по умолчанию `false`) — частичное решение:**

- После **любого** write в текущем PHP-процессе все последующие SELECT идут на **master**.
- Реализация — флаг **`$recordsModified`** на инстансе `Illuminate\\Database\\Connection`, проверяется в `getPdoForSelect()`.

**КРИТИЧЕСКОЕ ограничение sticky** — работает только в **одном** HTTP-запросе:

| Сценарий | Спасает ли sticky |
|---|---|
| `User::create()` → `User::find($id)` в **том же** запросе | **Да** — флаг живёт на Connection до конца запроса |
| **PRG**: `POST /users` → `302` → `GET /users/{id}` | **Нет** — новый запрос, новый Connection, флаг сброшен |
| Job отложенный из транзакции | **Нет** — другой процесс, нужен `afterCommit` + GTID/LSN |

**Что делать с PRG:**

| Решение | Уровень |
|---|---|
| Рендерить view вместо `redirect()` | Laravel — самое простое |
| Передать данные через `session()->flash()` | Laravel — без БД-чтения после редиректа |
| **Sticky session** на балансировщике (привязка юзера к ноде) | Infrastructure |
| **Causality tokens** (LSN/GTID в cookie/header) — реплика ждёт нужной позиции | Postgres logical replication, ProxySQL |
| **Synchronous replication** для критичных таблиц | Postgres `synchronous_commit = on`, MySQL Group Replication |
| **`useWritePdo()`** разово на конкретном запросе | Laravel — для одной критичной выборки |

**Octane/долгоживущие воркеры:**

- Между запросами Octane вызывает `ConnectionsHaveBeenForgottenEvent` → флаг `$recordsModified` сбрасывается.
- Если кастомизировал lifecycle — проверьте, что флаг чистится, иначе все запросы на воркере пойдут на master.',
                'code_example' => '<?php
// config/database.php
"connections" => [
    "mysql" => [
        "driver"   => "mysql",
        "read"     => ["host" => ["10.0.0.2", "10.0.0.3"]], // реплики
        "write"    => ["host" => "10.0.0.1"],               // master
        "sticky"   => true, // ← по умолчанию false, обязательно включить руками
        "database" => env("DB_DATABASE"),
        "username" => env("DB_USERNAME"),
        "password" => env("DB_PASSWORD"),
    ],
],

// Что РЕАЛЬНО фиксит sticky - чтение в том же запросе
$user = User::create($data);
$fresh = User::find($user->id); // → write (sticky сработал)

// Что sticky НЕ фиксит - PRG-редирект (новый HTTP-запрос)
return redirect("/users/{$user->id}"); // следующий GET - новый bootstrap,
                                       // recordsModified=false, SELECT идёт
                                       // на реплику, может вернуть 404

// Workaround для PRG: рендер без редиректа или session flash
return view("users.show", ["user" => $user]);

// Принудительно использовать master разово (любой запрос)
$fresh = User::on("mysql")->useWritePdo()->find($user->id);',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.eloquent_advanced',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как использовать PHP 8.1 Backed Enums в роутах, $casts моделей и валидации?',
                'answer' => 'Laravel 9+ поддерживает **PHP 8.1 backed enums** (`enum X: string`/`enum X: int`) в трёх ключевых местах.

**Важно:** только **backed** enum — pure enum без `: string`/`: int` **не работает** ни в роутах, ни в `$casts` (нужно соответствие БД-значению).

**1. В роутах — Implicit Enum Binding:**

- Type-hint в сигнатуре контроллера или closure — Laravel пытается `Enum::tryFrom($urlValue)`.
- При несоответствии — `BackedEnumCaseNotFoundException` → дефолтный handler рендерит **404**.
- **`->missing()`** на роуте (как для Route Model Binding) для Enum **не применим**.
- Кастомный ответ (например, 422) — перехват в `bootstrap/app.php`.

**2. В модели — `$casts`:**

- `\'status\' => UserStatus::class` — двусторонний каст:
  - На чтение — объект Enum.
  - На запись — `->value` (строка/int).
- **Коллекция enum-ов** — `AsEnumCollection::class . \':\' . Permission::class` (L11+).
- Сравнение через **`===`** — enum это singleton по case, **не строка**.

**3. В валидации — `Rule::enum()`:**

- `Rule::enum(UserStatus::class)` — проверит, что значение есть в case-ах.
- В FormRequest или массиве правил.
- **`Rule::enum(...)->only([...])`** / **`->except([...])`** — подмножество case-ов (L11+).

**Подводные камни:**

- **Прямое сравнение со строкой** — `$user->status === \'active\'` всегда **false** (объект Enum vs string). Правильно: `$user->status === UserStatus::Active` **или** `$user->status->value === \'active\'`.
- **`MassAssignmentException`** при `User::create([\'status\' => UserStatus::Active])` если `status` не в `$fillable`.
- **JSON-сериализация** — `JsonResource` сам сериализует enum в `->value`; в массивах через `$user->toArray()` — тоже **value**.
- **`whereIn` с enum-ами** — `User::whereIn(\'status\', [UserStatus::Active, UserStatus::Pending])` работает (Laravel сам берёт `->value`).
- **Миграция БД** — храните как `string` (varchar) или `tinyint` под backed-тип; для добавления нового case — миграция не нужна.',
                'code_example' => '<?php
// 1) Enum
enum UserStatus: string {
    case Active    = "active";
    case Suspended = "suspended";
    case Banned    = "banned";
}

// 2) В роуте - 404 на невалидном значении из коробки
Route::get("/users/by-status/{status}", function (UserStatus $status) {
    return User::where("status", $status->value)->get();
});
// /users/by-status/active   → ok
// /users/by-status/whatever → 404 без if-ов

// 3) В модели - двусторонний каст
class User extends Model {
    protected $casts = [
        "status" => UserStatus::class,
    ];
}
$user->status === UserStatus::Active; // bool, без сравнения строк

// 4) В валидации
$request->validate([
    "status" => [Rule::enum(UserStatus::class)],
]);

// 5) Коллекция enums (Laravel 11+)
protected $casts = [
    "permissions" => AsEnumCollection::class . ":" . Permission::class,
];

// 6) Кастомизация ответа на невалидное значение в роуте (Laravel 11+)
// bootstrap/app.php
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (BackedEnumCaseNotFoundException $e, Request $r) {
        if ($r->expectsJson()) {
            return response()->json([
                "error" => "invalid_value",
                "message" => $e->getMessage(),
            ], 422);
        }
    });
})',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.eloquent_advanced',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как вытащить одно поле из связанной модели одним запросом без with()? (Subquery select)',
                'answer' => '**Задача:** показать список юзеров **с датой последнего логина** одним запросом.

**Почему стандартные подходы не подходят:**

| Подход | Почему не годится |
|---|---|
| `with(\'logins\')` | Тащит **все** логины каждого юзера — мегабайты лишнего |
| `with([\'logins\' => fn ($q) => $q->latest()->limit(1)])` | В L≤10 — лимит на ВСЮ выборку (баг); в L11+ — per-parent, но всё равно отдельный SQL |
| `withCount(\'logins\')` | Только **число**, не дата |
| `withMax(\'logins\', \'created_at\')` | Возвращает агрегат, **но только одну колонку** |

**Решение — `addSelect()` со скалярным подзапросом** (Laravel 6+):

- Один SQL: `SELECT users.*, (SELECT created_at FROM logins WHERE user_id = users.id ORDER BY ... LIMIT 1) AS last_login_at FROM users`.
- **Ноль** доп. запросов.
- Можно **`orderBy(\'last_login_at\')`** прямо на этом виртуальном поле.

**Преимущества:**

- **Только нужные данные** — одна колонка вместо всей связанной таблицы.
- **Сортировка/фильтрация на стороне БД** — `orderByDesc(\'last_login_at\')` оптимизатор может использовать индекс.
- **`->withCasts([...])`** на лету — Carbon на выходе вместо сырой строки (L8+).

**Подводные камни:**

- **Тип значения** — сырая строка из БД (timestamp как `string`, не Carbon). Решение: `withCasts([\'last_login_at\' => \'datetime\'])` или `$casts` на модели.
- **Несколько колонок** — `addSelect` берёт **одну** колонку из subselect. Для нескольких — несколько отдельных `addSelect` или JOIN с GROUP BY.
- **Performance** — subselect выполняется **на каждую строку** основного запроса; нужен индекс на FK + `ORDER BY` в подзапросе. Иначе на 100k юзерах — N сканов.
- **Альтернатива через `latestOfMany()` (L9+)** — `hasOne(...)->latestOfMany()` или `->ofMany(\'score\', \'max\')` — превращает `hasMany` в `hasOne` по агрегату. Чище для частых паттернов «last X», но всё ещё **отдельный SQL** через `with`.
- **Window functions** — `ROW_NUMBER() OVER (PARTITION BY user_id ORDER BY ...)` через `selectRaw` — для top-N связей (N > 1).',
                'code_example' => '<?php
// Подзапрос: дата последнего логина
$users = User::query()
    ->addSelect([
        "last_login_at" => Login::query()
            ->select("created_at")
            ->whereColumn("user_id", "users.id")
            ->latest("created_at")
            ->limit(1),
    ])
    ->withCasts(["last_login_at" => "datetime"]) // Carbon на выходе
    ->orderByDesc("last_login_at")
    ->paginate(20);

// Или через addSelect для нескольких полей
User::addSelect([
    "last_login_at"   => Login::select("created_at")->whereColumn("user_id", "users.id")->latest()->limit(1),
    "last_login_ip"   => Login::select("ip")->whereColumn("user_id", "users.id")->latest()->limit(1),
    "orders_total"    => Order::selectRaw("COALESCE(SUM(amount), 0)")->whereColumn("user_id", "users.id"),
])->get();

// Альтернатива (L9+): hasOne ofMany - объявить связь "одна-к-многим, последняя"
class User extends Model {
    public function lastLogin(): HasOne {
        return $this->hasOne(Login::class)->latestOfMany();
        // или ->ofMany("score", "max") для произвольного агрегата
    }
}
User::with("lastLogin")->get();',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.eloquent_advanced',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Почему массовый UPDATE через Query Builder/Model::where->update НЕ триггерит события Eloquent (saving/updating/saved/updated)?',
                'answer' => 'Очень частая боль уровня middle/senior — **bulk-операции через query builder идут прямо в SQL, минуя слой моделей**.

**Сравнение:**

| Вызов | Lifecycle |
|---|---|
| `$model->save()` / `$model->update([...])` / `$model->delete()` | **Полный**: events (`saving`, `updating`, `saved`, `updated`), observers, мутаторы, `$casts`, `updated_at`, Scout reindex, broadcasting |
| `Model::where(...)->update([...])` / `->delete()` | **Только SQL**: `UPDATE/DELETE ... WHERE ...` |
| `DB::table(...)->update([...])` | То же — query builder не знает о моделях в принципе |

**Что НЕ срабатывает на bulk:**

- **События** — `saving/updating/saved/updated/deleting/deleted`.
- **Observers** — Observer-методы молчат.
- **Мутаторы и accessors** — `set...Attribute` / `Attribute::make(set: ...)` не вызываются.
- **`$casts`** — данные идут в БД **в сыром виде**; например, передали Carbon — БД получит непредсказуемое значение, передали enum — упадёт.
- **`updated_at`** — Laravel **сам** добавляет в SET для Eloquent-builder (НЕ для `DB::table`), но через мутатор оно не пройдёт.
- **Scout** — индекс остаётся **со старыми данными**.
- **Broadcasting** — события через `BroadcastsEvents` не уйдут.

**Классический баг:**

- Логика «при архивации юзера → отправь email» висит в `UserObserver::updated`.
- Batch-скрипт: `User::where(\'last_login\', \'<\', now()->subYear())->update([\'status\' => \'archived\'])`.
- **Письма не уходят**, баг живёт месяцами, пока кто-то не заметит.

**Решения:**

| Решение | Когда |
|---|---|
| **`foreach` + `$u->update()`** | Маленькие выборки — точно сработают события, медленно (N запросов) |
| **`chunkById` + `$chunk->each->update()`** | Большие выборки — компромисс: события работают, запросов меньше |
| **Bulk + явный side effect** | Огромные выборки — bulk-update, потом руками `event(...)`, `Scout::reindex()`, `Cache::flush()` |
| **`updateQuietly([...])`** | Намеренно без событий — массовое обновление `updated_at` и т.п. |

**Правило ревью:** если рядом с моделью лежит **Observer**, **Searchable**, **broadcast**, или сложные **мутаторы** — `where(...)->update(...)` это **анти-паттерн**, ищите `foreach` / `chunkById`.',
                'code_example' => '<?php
// ❌ События НЕ сработают - observer молчит
User::where("last_login_at", "<", now()->subYear())
    ->update(["status" => "inactive"]);

// ❌ То же самое - DB::table напрямую
DB::table("users")
    ->where("last_login_at", "<", now()->subYear())
    ->update(["status" => "inactive"]);

// ✅ Полный lifecycle - но N запросов вместо 1
User::where("last_login_at", "<", now()->subYear())
    ->each(fn (User $u) => $u->update(["status" => "inactive"]));

// ✅ Компромисс - chunk-ом, события работают, запросов меньше
User::where("last_login_at", "<", now()->subYear())
    ->chunkById(500, function ($chunk) {
        $chunk->each->update(["status" => "inactive"]);
    });

// ✅ Bulk + явный side effect, если события дешевле дублировать
User::where(...)->update([...]);
event(new UsersDeactivated($affectedIds));
User::whereIn("id", $affectedIds)->searchable(); // если нужен Scout',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.eloquent_advanced',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Когда выгоднее DB::table вместо Eloquent? Цена гидратации моделей.',
                'answer' => '**Цена гидратации Eloquent** на каждый ряд из БД:

- Конструктор `new Model()`.
- Наполнение `$original` и `$attributes`.
- Прогон через **`$casts`** (Carbon, enum, JSON-decode).
- Регистрация observers, подготовка lazy-load связей.
- **~10-30 микросекунд + ~2-3 КБ памяти на объект.**

На 50k записей это уже **секунды и сотни МБ** — реальный риск **OOM** на 512 МБ-воркере.

**Sentor-правило:** если запрос — это просто **«массив скалярных строк»** (экспорт CSV, агрегаты, рассылка email), а методы/связи модели не нужны — **`DB::table()`** вместо Eloquent.

**Сравнение вариантов:**

| Вариант | Гидратация моделей | Память | События/casts | Когда |
|---|---|---|---|---|
| **`User::all()`** | Полная | O(N), большая | Да | Маленькие выборки, нужны модели |
| **`User::cursor()`** | Полная (по одной) | **O(1)** | Да | Большие выборки, нужны модели |
| **`User::query()->toBase()->cursor()`** | **stdClass** | **O(1)** | **Нет** | Большие, без модели — гибрид |
| **`DB::table(\'users\')->get()`** | stdClass | O(N) | **Нет** | Read-only данные, скаляры |
| **`DB::table(\'users\')->cursor()`** | stdClass | **O(1)** | **Нет** | Стрим, экспорт |
| **`DB::select(\'...raw SQL...\')`** | array of stdClass | O(N) | **Нет** | Сложный SQL (CTE, window) |

**Когда оставить Eloquent:**

- Нужны **связи** (`with`).
- Нужны **`$casts`/мутаторы** (decimal:2, Carbon, JSON).
- Бизнес-методы модели — `$user->canDoX()`, `$order->total()`.
- **Events/observers** — обновление Scout, audit-log.
- **Resources** — `UserResource` ожидает модель.

**Промежуточные приёмы:**

- **`->cursor()`** — O(1) памяти, генератор, **всё ещё гидратирует** модели → выигрыш только в памяти, не в CPU.
- **`->lazy(1000)`** / **`->lazyById(1000)`** — то же, но порциями по N (под капотом chunk).
- **`->toBase()`** на Eloquent-Builder — скастует результат к stdClass и **пропустит гидратацию**.
- **`pluck(\'email\')`** на DB-builder — возвращает Collection строк, минимум памяти.

**Когда переходить:**

- **>10k записей и просто перечитываем** — `DB::table()`.
- **Экспорт в CSV** — `cursor()` с любого слоя.
- **Bulk-операции** — `DB::table()->update(...)` (но помним: события моделей не сработают).
- **Сложный SQL с window/CTE** — `DB::select($sql)` гораздо чище.',
                'code_example' => '<?php
// ❌ Из 100k юзеров - OOM на 512МБ воркере
$emails = User::all()->pluck("email")->toArray();

// ✅ Без гидратации, в разы быстрее и без OOM
$emails = DB::table("users")->pluck("email")->toArray();

// ✅ Стрим через cursor - O(1) памяти, но гидратирует модели
foreach (User::cursor() as $u) {
    sendDigest($u);
}

// ✅ toBase() - eloquent-builder, но без модели на выходе
foreach (User::query()->where(...)->toBase()->cursor() as $row) {
    // $row - stdClass, нет casts/мутаторов/связей
    echo $row->email;
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.eloquent_advanced',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что делает withTrashed, onlyTrashed и restore при использовании SoftDeletes?',
                'answer' => 'Трейт **`SoftDeletes`** регистрирует **глобальный scope** `SoftDeletingScope`, который **автоматически добавляет** `WHERE deleted_at IS NULL` ко всем запросам — удалённые записи скрыты по умолчанию.

**Три способа «увидеть» удалённое + два способа удаления:**

| Метод | Что делает | SQL |
|---|---|---|
| **`Post::all()`** (дефолт) | Только живые | `WHERE deleted_at IS NULL` |
| **`Post::withTrashed()`** | **Все** — живые + удалённые | scope отключен |
| **`Post::onlyTrashed()`** | **Только** удалённые («корзина») | `WHERE deleted_at IS NOT NULL` |
| **`$post->delete()`** | Soft delete | `UPDATE ... SET deleted_at = NOW()` |
| **`$post->forceDelete()`** | **Физическое** удаление | `DELETE FROM ...` |
| **`$post->restore()`** | Восстановить | `UPDATE ... SET deleted_at = NULL` |

**События жизненного цикла:**

- `deleting` / `deleted` — на soft delete.
- `restoring` / `restored` — при `restore()`.
- `forceDeleting` / `forceDeleted` — при `forceDelete()`.
- `trashed` (L11+) — после soft delete.

**Типичные use cases:**

- **«Корзина» в админке** — `Post::onlyTrashed()` показывает удалённые, кнопка `restore`.
- **Аудит/история** — данные не теряются физически.
- **GDPR-удаление** — `forceDelete()` гарантированно убирает PII.

**Подводные камни:**

- **UNIQUE-индекс ломается** — `users.email` UNIQUE, юзер удалён, регистрируется снова с тем же email → duplicate. Решение: partial unique index (PG) или анонимизация email при soft-delete.
- **FK с ON DELETE CASCADE** — soft delete родителя **не каскадит**, нужно вручную обрабатывать в Observer.
- **`onlyTrashed` + `restore()`** в админке — частый паттерн.',
                'code_example' => '<?php
class Post extends Model {
    use SoftDeletes;
}

// Дефолт - только живые
Post::all();

// Включая удалённые
Post::withTrashed()->get();

// Только удалённые - например "Корзина"
Post::onlyTrashed()->get();

// Восстановить из корзины
$post = Post::onlyTrashed()->find($id);
$post->restore();           // deleted_at = null + событие restored

// Жёсткое удаление - данные исчезают физически
$post->forceDelete();       // событие forceDeleted

// На одной выборке
Post::withTrashed()->where("user_id", $id)->restore();',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.eloquent_advanced',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что нужно учитывать при выборе движка Scout: database, MeiliSearch, Algolia, Typesense?',
                'answer' => '**Laravel Scout** — абстракция над поисковыми движками. Драйвер меняется одной строкой в `.env` (`SCOUT_DRIVER=...`), API запросов одинаковый: `Product::search(\'ноутбук\')->paginate(15)`.

**Сравнение драйверов:**

| Драйвер | Тип | Цена | Производительность | Когда выбирать |
|---|---|---|---|---|
| **`database`** | LIKE по индексам той же БД | 0 | До ~100k записей | Прототип, MVP, ноль инфраструктуры |
| **`collection`** | LIKE в памяти | 0 | — | Тестовый окружение |
| **MeiliSearch** | Self-hosted на Rust | Бесплатно + сервер | Миллионы записей | Дефолт для middle-проектов |
| **Typesense** | Self-hosted на C++ | Бесплатно + сервер | Миллионы записей | Большие корпуса, геопоиск |
| **Algolia** | SaaS | $$$ | Любой объём | Лучшее ранжирование, готовое решение |

**Критерии выбора:**

- **Объём индекса** — `database` до 100k, Meili/Typesense до миллионов, Algolia на любой размер.
- **Бюджет** — self-hosted (Meili/Typesense) бесплатные, но требуют сервер и обслуживание; Algolia платный, но managed.
- **Compliance** — можно ли отдавать данные внешнему вендору? Если нет (PII, GDPR-чувствительные) — только self-hosted.
- **Языковая поддержка** — морфология русского лучше всего у Meili 1.x+ и Typesense.
- **Фичи** — typo-tolerance, фасеты, синонимы, geo есть у всех кроме `database`.

**Что общего:**

- Все драйверы умеют `Model::search()->where()->orderBy()->paginate()`.
- Все поддерживают `php artisan scout:import` для bulk-индексации с chunk.
- `Searchable` trait автоматически синхронизирует индекс на `save`/`delete` (опционально через очередь — `SCOUT_QUEUE=true`).',
                'code_example' => '<?php
// .env
// SCOUT_DRIVER=meilisearch
// MEILISEARCH_HOST=http://127.0.0.1:7700

class Product extends Model {
    use Searchable;

    public function toSearchableArray(): array {
        return [
            "id"          => (string) $this->id,
            "name"        => $this->name,
            "category"    => $this->category->name,
            "price"       => (float) $this->price,
        ];
    }
}

// Поиск, одинаковый для всех движков
Product::search("ноутбук")
    ->where("category", "electronics")
    ->paginate(15);

// Импорт в индекс (поточно по chunkById)
// php artisan scout:import "App\\Models\\Product"

// Переключить store на лету (например, для админ-поиска - Algolia,
// для публичного - database)
Product::search("...", function ($engine, string $query, array $opts) {
    // кастомизация под конкретный движок
})->get();',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.eloquent_advanced',
            ],
        ];
    }
}
