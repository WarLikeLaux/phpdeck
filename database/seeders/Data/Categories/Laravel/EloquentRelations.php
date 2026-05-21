<?php

namespace Database\Seeders\Data\Categories\Laravel;

class EloquentRelations
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Laravel',
                'question' => 'Какие виды связей в Eloquent? Опиши hasOne, hasMany, belongsTo, belongsToMany.',
                'answer' => 'Четыре основных вида связей Eloquent:

- **`hasOne`** — **один-к-одному**. На родителе. FK в дочерней. `User → Profile` (`profiles.user_id`).
- **`hasMany`** — **один-ко-многим**. На родителе. FK в дочерней. `User → Post[]` (`posts.user_id`).
- **`belongsTo`** — **обратная сторона** `hasOne`/`hasMany`. На ребёнке, у которого хранится FK. `Post → User` (`posts.user_id`).
- **`belongsToMany`** — **многие-ко-многим** через pivot-таблицу. `User ↔ Role` через `role_user`.

Запоминалка: **`belongsTo` — там, где лежит FK**, `hasOne`/`hasMany` — на противоположной стороне.

По конвенции FK = `имя_родителя` в snake_case + `_id` (`user_id`). Pivot-таблица — два имени моделей в snake_case по алфавиту (`role_user`).',
                'code_example' => 'class User extends Model {
    public function profile() { return $this->hasOne(Profile::class); }
    public function posts()   { return $this->hasMany(Post::class); }
    public function roles()   { return $this->belongsToMany(Role::class); }
}

class Post extends Model {
    public function user() { return $this->belongsTo(User::class); }
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.eloquent_relations',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое hasManyThrough и hasOneThrough?',
                'answer' => '**`hasManyThrough`** — связь «через» промежуточную таблицу, когда две модели соединены не напрямую, а через третью.

**Канонический пример:** `Country → User → Post`.

- `Country` имеет много `User` (`users.country_id`).
- `User` имеет много `Post` (`posts.user_id`).
- `Country` хочет **все Post-ы своих юзеров одним запросом**: `$country->posts`.

Без `hasManyThrough` пришлось бы делать `$country->users()->with(\'posts\')` и собирать вручную.

**Сигнатура:**

```
hasManyThrough(
    FinalModel,           // Post
    IntermediateModel,    // User
    foreignKeyOnIntermediate, // users.country_id
    foreignKeyOnFinal,    // posts.user_id
    localKey,             // countries.id
    secondLocalKey,       // users.id
)
```

По умолчанию Laravel угадывает имена FK (`country_id`, `user_id`) и PK (`id`) — если соглашение соблюдено, аргументы **можно опустить**.

**`hasOneThrough`** — то же самое, но возвращает **одну** модель (хорошо комбинируется с `latestOfMany()`).

**Ограничения:**

- Работает только для **прямой цепочки** `belongsTo → hasMany` (через один уровень).
- Для **many-to-many через pivot** — нужен пакет `staudenmeir/eloquent-has-many-deep` или явный `join`.
- Сложные `through`-связи плохо комбинируются с `whereHas` и могут давать неожиданные планы запросов — смотрите `EXPLAIN`.',
                'code_example' => '<?php
class Country extends Model {
    public function users() {
        return $this->hasMany(User::class);
    }

    // Country -> User -> Post (через user_id)
    public function posts() {
        return $this->hasManyThrough(
            Post::class,     // финальная модель
            User::class,     // промежуточная
            "country_id",    // FK на countries.id в таблице users
            "user_id",       // FK на users.id в таблице posts
            "id",            // PK countries
            "id",            // PK users (вторая локальная)
        );
    }

    // Один последний пост любого юзера страны
    public function latestPost() {
        return $this->hasOneThrough(Post::class, User::class)
            ->latestOfMany();
    }
}

// Использование
$country = Country::find(1);
foreach ($country->posts as $post) {                // все посты юзеров страны
    echo $post->title;
}

// С eager loading - один запрос вместо N
Country::with("posts")->get();

// SQL под капотом:
// SELECT posts.*, users.country_id
// FROM posts
// INNER JOIN users ON posts.user_id = users.id
// WHERE users.country_id IN (1, 2, 3)',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.eloquent_relations',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое полиморфные связи (morphTo, morphMany, morphedByMany)?',
                'answer' => '**Полиморфная связь** — одна модель может «принадлежать» **нескольким разным** моделям через **одну** таблицу.

Канонический пример: `Comment` относится и к `Post`, и к `Video` (и к `Photo`).

**Схема pivot:** в `comments` лежит **две** колонки:

- **`commentable_id`** — id «родителя» (например, `42`).
- **`commentable_type`** — класс «родителя» (`App\\Models\\Post`).

**Какие отношения:**

| Сторона | Метод | Пример |
|---|---|---|
| Дочка (`Comment`) | `morphTo()` | `$comment->commentable` → `Post` или `Video` |
| Родитель (`Post`/`Video`) | `morphMany()` | `$post->comments` |
| Полиморфный many-to-many | `morphToMany()` | `Post::tags()` через `taggables` |
| Обратная сторона M:N | `morphedByMany()` | `Tag::posts()` |

**Подводные камни:**

- **`morphMap`** в `AppServiceProvider::boot` — фиксирует **короткие алиасы** (`\'post\' => Post::class`) вместо FQCN в БД; иначе рефакторинг неймспейса сломает связи.
- **N+1 при `morphTo`** — обычный `with(\'commentable\')` делает по запросу на каждый тип; нужен `morphWith()` для подгрузки вложенных связей конкретных типов.
- **Каскадное удаление** — нельзя сделать FK в БД (тип-зависимое), нужен Observer или job.',
                'code_example' => 'class Comment extends Model {
    public function commentable() { return $this->morphTo(); }
}
class Post extends Model {
    public function comments() { return $this->morphMany(Comment::class, \'commentable\'); }
}
class Video extends Model {
    public function comments() { return $this->morphMany(Comment::class, \'commentable\'); }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.eloquent_relations',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое pivot-таблица в belongsToMany и как работать с ней?',
                'answer' => '**Pivot** — промежуточная таблица для связи **многие-ко-многим**, хранящая связи между двумя сущностями.

**Пример:** `role_user` с парой FK `(user_id, role_id)`.

**Конвенции:**

- **Имя таблицы** — singular-имена обеих моделей в snake_case по **алфавиту**: `role_user`.
- **Дополнительные поля pivot** — `withPivot([\'expires_at\', \'priority\'])`.
- **Timestamps** — `withTimestamps()` подключает `created_at`/`updated_at` на pivot.
- **Кастомная модель** — `->using(Membership::class)` с наследованием от `Pivot` (или `MorphPivot` для полиморфных).

**Работа с pivot:**

| Метод | Что делает |
|---|---|
| `attach($id, [\'role\' => \'owner\'])` | Добавить связь с дополнительными полями |
| `detach($id)` или `detach()` | Убрать одну связь / все |
| `sync([1, 2, 3])` | **Заменить весь набор** — что не в массиве, удалится |
| `syncWithoutDetaching([...])` | Добавить, не удаляя существующие |
| `toggle($id)` | Переключить: был → удалить, не было → добавить |
| `updateExistingPivot($id, [...])` | Обновить дополнительные поля |

**Доступ к данным pivot:** `$user->roles->first()->pivot->expires_at`. Через `as(\'membership\')` можно переименовать свойство.',
                'code_example' => 'public function roles() {
    return $this->belongsToMany(Role::class)
        ->withPivot(\'expires_at\', \'priority\')
        ->withTimestamps();
}

// Доступ
$user->roles->first()->pivot->expires_at;

// Прикрепление
$user->roles()->attach($roleId, [\'expires_at\' => now()->addYear()]);
$user->roles()->detach($roleId);
$user->roles()->sync([1, 2, 3]);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.eloquent_relations',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое pivot model и зачем он нужен в belongsToMany?',
                'answer' => 'Базовый pivot - это просто строка-связка. Когда на ней нужны дополнительные поля (role, joined_at), методы или события, объявляют отдельную модель, наследующую Pivot, и подключают её через using(MembershipPivot::class). Это позволяет иметь withPivot, withTimestamps, accessors и события created/updated на самой связке. Для many-to-many полиморфных используется MorphPivot.',
                'code_example' => 'class Membership extends Pivot {
    protected $casts = [\'joined_at\' => \'datetime\'];

    public function isOwner(): bool {
        return $this->role === \'owner\';
    }
}

class User extends Model {
    public function teams() {
        return $this->belongsToMany(Team::class)
            ->using(Membership::class)
            ->withPivot([\'role\', \'joined_at\'])
            ->withTimestamps();
    }
}

$user->teams->first()->pivot->isOwner();',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.eloquent_relations',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое hasMany простыми словами?',
                'answer' => 'Отношение **«один-ко-многим»**: у одной записи может быть много связанных.

Пример: у `User` много `Post`.

- В модели `User` объявляется метод `posts()`, возвращающий `$this->hasMany(Post::class)`.
- Доступ к коллекции: `$user->posts` — `Collection` моделей `Post`.
- FK по умолчанию ищется в таблице `posts` как `user_id` (имя родителя в snake_case + `_id`), PK — `id`.
- Если имена другие — указать аргументами: `hasMany(Post::class, \'author_id\', \'id\')`.

Создать связанный пост через отношение — FK заполнится сам.',
                'code_example' => 'class User extends Model {
    public function posts() {
        return $this->hasMany(Post::class);
        // эквивалент: hasMany(Post::class, \'user_id\', \'id\')
    }
}

// Использование
$user = User::find(1);
foreach ($user->posts as $post) {
    echo $post->title;
}

// Создать связанный пост через relation - FK заполнится сам
$user->posts()->create([\'title\' => \'Привет\', \'body\' => \'...\']);',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'laravel.eloquent_relations',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое belongsTo простыми словами?',
                'answer' => 'Обратная сторона `hasOne`/`hasMany`: ребёнок «принадлежит» родителю.

Пример: у `Post` один автор-`User`.

- В модели `Post` объявляется метод `user()`, возвращающий `$this->belongsTo(User::class)`.
- Доступ: `$post->user` — одна модель `User` или `null`.
- **FK хранится в этой же таблице** (`posts.user_id`). Laravel определяет имя по имени метода: `user()` → `user_id`.
- Если имя поля другое — указать: `belongsTo(User::class, \'author_id\')`.

Привязка/отвязка: `$post->user()->associate($user)` / `$post->user()->dissociate()`.',
                'code_example' => 'class Post extends Model {
    public function user() {
        return $this->belongsTo(User::class);
        // эквивалент: belongsTo(User::class, \'user_id\', \'id\')
    }
}

// Использование
$post = Post::find(1);
echo $post->user->name; // SELECT * FROM users WHERE id = posts.user_id

// Привязать пост к юзеру
$post->user()->associate($user);
$post->save();

// Отвязать
$post->user()->dissociate();
$post->save();',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'laravel.eloquent_relations',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое belongsToMany простыми словами?',
                'answer' => 'Отношение **«многие-ко-многим»**: у `User` много `Role` и у `Role` много `User`.

Нужна **pivot-таблица** `role_user` с двумя FK: `user_id` и `role_id`. По умолчанию Laravel ищет имя таблицы из имён моделей в snake_case **по алфавиту**.

На обеих моделях объявляется `belongsToMany`.

Управление связями:

- **`attach($id)`** — добавить.
- **`detach($id)`** — убрать.
- **`sync([1, 2, 3])`** — **заменить весь набор** (что не в массиве — удалится).
- **`syncWithoutDetaching([...])`** — добавить, ничего не удаляя.
- **`toggle($id)`** — переключить (был → удалить, не было → добавить).

Pivot живёт на свойстве `$user->roles[0]->pivot` — там лежат данные строки `role_user`.',
                'code_example' => 'class User extends Model {
    public function roles() {
        return $this->belongsToMany(Role::class);
    }
}

class Role extends Model {
    public function users() {
        return $this->belongsToMany(User::class);
    }
}

$user->roles;                   // Collection ролей юзера
$user->roles()->attach(5);      // добавить роль 5
$user->roles()->detach(5);      // убрать роль 5
$user->roles()->sync([1, 2, 3]); // оставить только эти роли',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.eloquent_relations',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое eager loading через with() в Eloquent?',
                'answer' => '**Eager loading через `with()`** — способ **заранее подгрузить связанные модели**, чтобы избежать **N+1**.

Без `with`:

- `User::all()` → 1 запрос.
- В цикле `$user->posts` → ещё N запросов (по одному на каждого юзера).
- Итого **1 + N**.

С `with(\'posts\')`:

- `SELECT * FROM users`.
- `SELECT * FROM posts WHERE user_id IN (1, 2, ...)`.
- Итого **2** запроса для любого количества юзеров.

Два способа:

- **`with(\'posts\')`** — на запросе, до выполнения.
- **`load(\'posts\')`** — на уже полученной коллекции/модели.

Бонусы:

- **Вложенные связи**: `with(\'posts.comments\')`.
- **Условие на связь**: `with([\'posts\' => fn($q) => $q->where(\'published\', true)])`.
- **Только нужные колонки**: `with(\'posts:id,user_id,title\')`.',
                'code_example' => '// Без eager loading - N+1
foreach (User::all() as $user) {
    echo $user->posts->count(); // запрос на каждой итерации
}

// С with - 2 запроса всего
$users = User::with(\'posts\')->get();
foreach ($users as $user) {
    echo $user->posts->count();
}

// Несколько связей + вложенные
User::with([\'posts.comments\', \'profile\'])->get();

// load после получения
$users = User::all();
$users->load(\'posts\');

// С условием на связь
User::with([\'posts\' => fn ($q) => $q->where(\'published\', true)])->get();',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.eloquent_relations',
            ],
        ];
    }
}
