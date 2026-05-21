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
                'answer' => 'Классическая боль: на users.email стоит UNIQUE, юзер регистрируется → удаляет аккаунт (deleted_at заполняется) → пытается зарегистрироваться снова с тем же email → 23000/23505 (duplicate entry), потому что для БД "удалённая" запись физически жива и всё ещё держит email. Eloquent-валидация Rule::unique() умеет игнорировать soft-deleted (->ignore() / whereNull("deleted_at")), но БД-уровень уникальности про SoftDeletes ничего не знает. Решения: 1) PostgreSQL - partial unique index, элегантный путь: CREATE UNIQUE INDEX users_email_active ON users(email) WHERE deleted_at IS NULL. Уникальность проверяется только для живых записей; удалённые могут иметь какие угодно дубли email. В Laravel это $table->unique("email")->where("deleted_at IS NULL") нельзя - надо raw DB::statement в миграции. 2) MySQL/MariaDB - partial index НЕ поддерживается. Наивный составной UNIQUE (email, deleted_at) при дефолтном Laravel-поведении (deleted_at=NULL для живых) ЛОМАЕТСЯ: в MySQL для UNIQUE NULL != NULL, поэтому "(email=X, NULL)" и "(email=X, NULL)" считаются РАЗНЫМИ парами и БД пропустит ДВУХ ЖИВЫХ юзеров с одним email - уничтожает уникальность активных. Решения для MySQL: 2a) хранить deleted_at у живых не как NULL, а как sentinel (0 или 1970-01-01) - тогда (X, 0) дубли отлавливаются, (X, 2024-...) среди удалённых остаются уникальными по timestamp. Требует переопределить $dates / casts модели и прибить дефолт в схеме (DEFAULT 0). 2b) добавить generated column email_unique = (deleted_at IS NULL) и UNIQUE(email, email_unique) - живые получают TRUE, удалённые - ничего страшного из-за того же NULL != NULL. 2c) использовать MariaDB 10.2.x JSON / generated columns похожим способом. 3) Альтернатива - hard delete + архивная таблица users_archive (свобода схемы, но теряются связи через foreign key). 4) Альтернатива - анонимизировать email при удалении (email = "deleted_{$id}@example.com"), uniqueness сохраняется естественно и в MySQL, и в Postgres. Выбор зависит от: нужно ли восстанавливать аккаунт (тогда не аноним), есть ли GDPR/right-to-be-forgotten (тогда лучше hard delete), какая СУБД.',
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
                'answer' => 'chunk - выбирает по N записей через LIMIT/OFFSET и отдаёт коллекцию в callback. lazy - возвращает LazyCollection, выбирая записи порциями (внутри тоже chunk). cursor - использует серверный SQL-курсор и держит ОДНУ запись в памяти, экономит память сильнее всего, но удерживает соединение и не работает с eager loading. ⚠️ КРИТИЧЕСКАЯ ЛОВУШКА chunk при UPDATE. Если внутри chunk() вы обновляете записи так, что они перестают подпадать под исходное where (например, where("processed", false) и в callback ставите processed=true), произойдёт сдвиг OFFSET и ПОЛОВИНА записей будет ПРОПУЩЕНА. Механика: первый запрос берёт строки 0-999, обновляет их → они уходят из выборки. Строки 1000-1999 уходят из выборки, а OFFSET 1000 теперь указывает на строки 2000-2999 - между ними пропускается 1000 записей. Для миграций данных и любых обновлений всегда используйте chunkById() (или lazyById()): он использует WHERE id > $lastId вместо нестабильного OFFSET, поэтому устойчив к изменению набора записей. Тот же риск есть в обратную сторону при INSERT в обрабатываемую таблицу. Lazy для просто чтения - ок; для UPDATE - lazyById. ⚠️ ОТДЕЛЬНОЕ ТРЕБОВАНИЕ chunkById/lazyById: колонка ($column, по умолчанию "id") должна быть СТРОГО МОНОТОННО ВОЗРАСТАЮЩЕЙ И УНИКАЛЬНОЙ. На неуникальной колонке (created_at без секунд, status, datetime с дублями) механизм WHERE column > $lastValue ПРОПУСКАЕТ записи с тем же значением, что у границы чанка - все строки с дубликатом ключа за пределами первого попадания теряются. Если естественной такой колонки нет - либо chunkById по pk, дополнительно фильтруя нужный where, либо chunkByIdDesc для обратного направления, либо вручную делать пагинацию через "WHERE (sort_col, id) > (?, ?)" (keyset pagination на составном ключе).',
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
                'answer' => 'lockForUpdate - "пишущая" блокировка строки до конца транзакции (FOR UPDATE), другие транзакции не смогут читать с lock или писать. sharedLock - "читающая" блокировка (FOR SHARE), другие могут читать, но не писать. Используется для борьбы с гонками (race conditions).',
                'code_example' => 'DB::transaction(function () {
    $account = Account::where(\'id\', 1)->lockForUpdate()->first();
    $account->balance -= 100;
    $account->save();
});',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.eloquent_advanced',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что делает DB::afterCommit?',
                'answer' => 'afterCommit регистрирует callback, который выполнится только ПОСЛЕ успешного commit транзакции. Полезно для отправки событий, очередей, уведомлений - чтобы не отправлять их, если транзакция откатится. У моделей и job-ов есть свойства $afterCommit или ShouldQueueAfterCommit.',
                'code_example' => 'DB::transaction(function () use ($order) {
    $order->save();

    DB::afterCommit(function () use ($order) {
        SendOrderConfirmation::dispatch($order);
    });
});',
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
                'answer' => 'Observer - класс, методы которого - это коллбэки на жизненный цикл модели (creating, saved, deleted). Удобен, когда логика тесно связана с моделью. Event/Listener - общая шина: модель/код диспатчит произвольное событие, на него подписываются несколько слушателей, легко асинхронить через ShouldQueue. Observer лаконичнее для аудита/таймстампов, события - для кросс-доменной интеграции.',
                'code_example' => '<?php
class UserObserver {
    public function created(User $u): void { Mail::to($u)->send(new Welcome()); }
    public function deleting(User $u): void { $u->posts()->delete(); }
}
// AppServiceProvider::boot
User::observe(UserObserver::class);',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.eloquent_advanced',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как избежать N+1 при полиморфных связях morphTo?',
                'answer' => 'Обычный with("commentable") не работает напрямую, потому что для каждого типа нужен отдельный запрос. Используйте with("commentable") + morphWith() для жадной подзагрузки конкретных типов с их связями. Также есть morphMap в boot() - фиксирует строковые алиасы вместо FQCN, что устойчиво к рефакторингу. Альтернативно - явный foreach с groupBy типа.',
                'code_example' => '<?php
Comment::with(["commentable" => function (MorphTo $morphTo) {
    $morphTo->morphWith([
        Post::class => ["author"],
        Video::class => ["channel"],
    ]);
}])->get();',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.eloquent_advanced',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Чем отличаются local query scope от global scope и какие подводные камни у global?',
                'answer' => 'Local scope - public method scopeXxx, явно вызывается в цепочке (User::active()->get()). Global scope автоматически применяется ко всем запросам модели, реализуется через Scope-интерфейс или Closure в booted(). Проблема: можно забыть и удивляться "куда делись записи". Снимать глобальный scope через withoutGlobalScope/withoutGlobalScopes. Также job, сериализующий модель и достающий её через query, может зависеть от текущего auth/tenant контекста, который во время выполнения job уже другой.',
                'code_example' => 'protected static function booted(): void {
    static::addGlobalScope(\'tenant\', function (Builder $b) {
        if ($tenantId = auth()->user()?->tenant_id) {
            $b->where(\'tenant_id\', $tenantId);
        }
    });
}

// Снять
Post::withoutGlobalScope(\'tenant\')->get();
Post::withoutGlobalScopes()->get();',
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
                'answer' => 'DB::transaction($callback, $attempts) повторяет колбэк только при ОШИБКАХ КОНКУРЕНЦИИ - не при любом QueryException. Решение принимает Illuminate\Database\ConcurrencyErrorDetector::causedByConcurrencyError(): срабатывает на SQLSTATE 40001 (serialization failure - канон Postgres) и на текстовые маркеры в сообщении драйвера: "Deadlock found when trying to get lock" (MySQL ER_LOCK_DEADLOCK = 1213), "deadlock detected" (Postgres), "Lock wait timeout exceeded" (MySQL ER_LOCK_WAIT_TIMEOUT = 1205), "database is locked" (SQLite), и аналогичные в MariaDB Galera/WSREP. Что НЕ повторяется и сразу пробросится наверх: violation уникального индекса (23000/23505), foreign key (23503), check constraint, синтаксические ошибки, lost connection - на это есть отдельная ветка causedByLostConnection() и она ретраит уже по другому правилу (только если транзакция не начата). Без указания attempts (default 1) DB::transaction бросает первое же исключение. Для nested-транзакций Laravel использует SAVEPOINT - DB::transaction внутри другой создаёт точку отката, а не новую транзакцию. afterCommit-хуки сработают только после внешнего коммита.',
                'code_example' => '<?php
DB::transaction(function () use ($from, $to, $sum) {
    $from->lockForUpdate()->decrement("balance", $sum);
    $to->lockForUpdate()->increment("balance", $sum);
}, attempts: 3);',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.eloquent_advanced',
            ],
            [
                'category' => 'Laravel',
                'question' => 'В чём разница между whereIn и whereIntegerInRaw, и когда выбирать второй?',
                'answer' => 'whereIn($col, $array) использует PDO-bindings: для каждого элемента массива добавляется плейсхолдер (?), значение проходит через драйвер БД и экранируется. Это безопасно для строк/смешанных типов, но имеет цену: на 50 000 ID будет 50 000 плейсхолдеров, что упирается в лимит wire-протокола - и у MySQL/MariaDB, и у PostgreSQL число параметров кодируется 2-байтовым полем, поэтому потолок ровно 65 535 (0xFFFF) плейсхолдеров на запрос; в SQL Server лимит ещё жёстче - 2 100. Не путать с MySQL-настройкой max_prepared_stmt_count (по умолчанию 16 382) - она ограничивает общее число одновременно живущих prepared statement-ов на сервере, а не параметров в одном запросе. Помимо потолка большой whereIn сильно нагружает парсер SQL и сжирает память при подготовке запроса. whereIntegerInRaw($col, $array) поступает иначе: каждый элемент массива принудительно кастится в (int) и подставляется ПРЯМО в SQL-строку без bindings: WHERE id IN (1, 2, 3, ...). Безопасно потому что (int) гарантирует - там не может оказаться SQL-инъекции; профит - запрос обходит протокольный лимит на параметры и снижает память приложения при подготовке. Когда использовать: импорты, синхронизация с внешним источником, broadcast-операции вида "обновить статус у списка из 100k записей". Работает ТОЛЬКО с одиночными числовыми колонками - для строк, UUID и составных ключей аналога нет: там либо ->whereIn() с chunk на части по 1000-5000, либо JOIN со временной таблицей.',
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
                'answer' => 'В config/database.php у соединения можно указать массив "read" и "write" с отдельными хостами: SELECT-запросы пойдут на read-реплику, INSERT/UPDATE/DELETE - на write (master). Это горизонтально масштабирует чтение в типичном "много чтений / мало записей" приложении. Подводный камень: репликация асинхронна, лаг между master и реплики - десятки миллисекунд (а под нагрузкой - секунды), поэтому SELECT сразу после INSERT может вернуть старые данные или 404. Опция "sticky" => true в конфиге соединения (по умолчанию false): после ЛЮБОЙ операции записи в текущем PHP-процессе все последующие SELECT идут на write-соединение. Реализация - флаг $recordsModified на инстансе Connection (Illuminate\Database\Connection), который проверяется в getPdoForSelect(). КРИТИЧЕСКОЕ ОГРАНИЧЕНИЕ: sticky работает ТОЛЬКО в рамках одного PHP-запроса, потому что флаг живёт на инстансе Connection. Классический PRG-паттерн (POST /users → 302 → GET /users/{id}) sticky НЕ спасёт: следующий GET - это новый HTTP-запрос, новый bootstrap, новый Connection с recordsModified=false. Чтобы выжить с PRG: 1) на стороне Laravel - пробрасывать данные через session flash или сразу рендерить ответ без редиректа; 2) на инфраструктурном уровне - sticky-сессии на балансировщике (привязка пользователя к ноде), причинно-следственные токены (LSN/GTID-токен в куке/заголовке, по которому реплика дожидается нужной позиции), синхронная репликация для критичных таблиц. В Octane/долгоживущих воркерах sticky опасен в обратную сторону: флаг между запросами обнуляется через ConnectionsHaveBeenForgottenEvent / app reset, но если кастомизировал жизненный цикл - проверь, что recordsModified сбрасывается. Разово принудить master: Model::on("mysql")->useWritePdo()->find($id) или DB::connection()->getPdo() с явным write-pdo.',
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
                'answer' => 'Laravel 9+ поддерживает PHP 8.1 backed enums (string или int) в трёх ключевых местах. 1) В роутах через Implicit Enum Binding: если параметр в сигнатуре контроллера затайпхинчен enum-классом, Laravel автоматически попытается создать экземпляр через Enum::tryFrom($urlValue); если значение не соответствует ни одному case - бросается BackedEnumCaseNotFoundException, который Laravel-овский ExceptionHandler по умолчанию рендерит как 404 (NotFoundHttpException). Чтобы кастомизировать (например, отдать 422 с описанием доступных значений), перехватите исключение в bootstrap/app.php через ->withExceptions(fn ($e) => $e->render(...)); метод ->missing() на роуте, который работает для Route Model Binding, для Enum НЕ применим. 2) В модели в массиве $casts: "status" => UserStatus::class - при чтении атрибута получаете объект Enum, при сохранении в БД уходит ->value (строка/int); работает и с однозначными, и с массивами enums (AsEnumCollection). 3) В валидации через Rule::enum(UserStatus::class) - проверяет, что значение есть в case-ах. Также есть has() / In::enum() для расширенных кейсов. Бонус: в Blade и Resource классе можно сравнивать через ===, потому что enum - это singleton по case, а не строка. Подводный камень: чистый enum (без ": string"/": int") НЕ поддерживается ни в роутах, ни в $casts - нужен именно backed enum, потому что нужно соответствие БД-значению.',
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
                'answer' => 'Классическая задача: показать список пользователей с датой их последнего логина. with("logins") тащит ВСЕ логины каждого юзера - нерационально, нужен только один. withCount() считает только количество. withMax/Min/Avg/Sum - считают агрегат, но не возвращают другие поля связанной строки. Решение - subquery select через addSelect() (Laravel 6+). Пишете SELECT со скалярным подзапросом: SELECT users.*, (SELECT created_at FROM logins WHERE user_id = users.id ORDER BY created_at DESC LIMIT 1) AS last_login_at FROM users. Один запрос, никакого N+1, можно ORDER BY этого виртуального поля. Преимущества: 1) Только нужные данные. 2) Ноль дополнительных запросов. 3) Можно сортировать и фильтровать по subselect-полю на стороне БД. Подводные камни: тип значения - сырая строка из БД (для дат - timestamp-строка, не Carbon). Чтобы получить нормальный тип, добавьте в модель $casts (или addSelect + ->withCasts(["last_login_at" => "datetime"]) на лету в Laravel 8+). Если subselect возвращает несколько колонок - не подойдёт; нужно либо несколько отдельных subselect-ов, либо JOIN c GROUP BY. Альтернативный синтаксис в L9+: HasOne::ofMany("created_at", "max") - "latest of many" relation, который превращает hasMany в hasOne по агрегату.',
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
                'answer' => 'Очень частая боль уровня middle/senior. Когда вы вызываете $model->save() / $model->update($data) / $model->delete() на КОНКРЕТНОМ инстансе - Eloquent проходит через весь lifecycle: events (saving, updating, saved, updated), observers, мутаторы, $casts, апдейт updated_at, индексы Scout (Searchable), broadcasting. А когда вы делаете BULK-операцию через query builder вида User::where("active", false)->update(["status" => "archived"]) или Post::where(...)->delete() - это превращается в одиночный SQL "UPDATE ... WHERE / DELETE ... WHERE", который улетает в БД минуя инстансы моделей. Никаких событий, наблюдателей, мутаторов, обновления Scout-индекса, рассылки broadcast - ничего. Классический баг: бизнес-логика "при архивации юзера отправь email" висит в наблюдателе UserObserver::updated; разработчик пишет batch-скрипт User::where(...)->update(["status" => "archived"]) - письма не уходят, никто не замечает месяцами. Решения: 1) если важны события - foreach с $u->update() или $u->save() (медленнее, но lifecycle цел); 2) chunk-обработка: User::where(...)->chunkById(500, fn ($users) => $users->each->update([...])) - компромисс между скоростью и тем, что события сработают; 3) если bulk важен по скорости - сознательно дублируем нужные побочные эффекты (Scout::reindex, явный dispatch события) после массового запроса. То же самое верно для DB::table(...)->update(): query builder событий моделей не знает в принципе. Проверяйте: если рядом с update лежит observer/cast/Scout - ищите явный foreach или дозированный bulk.',
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
                'answer' => 'Eloquent на каждый ряд из БД создаёт полноценный объект Model: вызывает конструктор, наполняет $original/$attributes, прогоняет $casts, регистрирует наблюдателей, готовит lazy load связей. Для одной записи это ~10-30 микросекунд + ~2-3 КБ памяти на объект; на 50 000 строк это уже секунды и сотни МБ - реальный риск Out of Memory. Senior-правило: если результат запроса - это просто "массив скалярных строк, которые надо отдать дальше / посчитать / экспортировать в CSV", а методы и связи модели не нужны - используй DB::table("users")->select(...)->get() или ->cursor(). Возвращаются stdClass-объекты, никаких events, casts, мутаторов - в разы быстрее и меньше памяти. Когда оставить Eloquent: когда нужны связи (with), мутаторы/casts, бизнес-методы модели ($user->canDoX()), события/observers, или результат маленький. Промежуточный вариант: ->cursor() / ->lazy() возвращает по одной записи через генератор - O(1) памяти, но всё ещё гидратирует модели; ->toBase() на Eloquent-Builder - скастует результат к stdClass и пропустит гидратацию.',
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
