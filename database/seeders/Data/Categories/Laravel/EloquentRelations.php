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
                'answer' => 'hasOne - один-к-одному, объявляется на родительской модели; внешний ключ лежит в СВЯЗАННОЙ таблице (User имеет один Profile, profiles.user_id). hasMany - один-ко-многим, та же сторона/FK (User имеет много Posts, posts.user_id). belongsTo - inverse для hasOne/hasMany, объявляется на ДОЧЕРНЕЙ модели, у которой хранится внешний ключ на родителя (Post принадлежит User, posts.user_id). belongsToMany - многие-ко-многим через pivot-таблицу (User ↔ Role через role_user). Запомнить: belongsTo там, где FK; hasOne/hasMany - на противоположной стороне.',
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
                'answer' => 'hasManyThrough - связь "через" промежуточную таблицу. Например: Country имеет много Posts через User (Country -> User -> Post). hasOneThrough - то же, но только один.',
                'code_example' => 'class Country extends Model {
    public function posts() {
        return $this->hasManyThrough(Post::class, User::class);
        // ищет посты пользователей этой страны
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.eloquent_relations',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое полиморфные связи (morphTo, morphMany, morphedByMany)?',
                'answer' => 'Полиморфная связь позволяет одной модели принадлежать нескольким разным моделям через одну таблицу. Например, Comment может относиться и к Post, и к Video. В таблице comments есть commentable_id и commentable_type. morphTo - на стороне Comment. morphMany - на стороне Post/Video. morphedByMany / morphToMany - many-to-many полиморфная.',
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
                'answer' => 'Pivot - это промежуточная таблица для связи многие-ко-многим, которая хранит связи между двумя сущностями. Например, role_user. Дополнительные поля pivot достаются через withPivot, временные метки - withTimestamps. Можно создать кастомную модель пивота через using().',
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
                'answer' => 'Отношение «один-ко-многим». У User много Post — в модели User: posts() { return $this->hasMany(Post::class); }. Доступ: $user->posts вернёт коллекцию постов. FK в таблице posts должен называться user_id (или явно указать второй аргумент hasMany).',
                'difficulty' => 1,
                'topic' => 'laravel.eloquent_relations',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое belongsTo простыми словами?',
                'answer' => 'Обратная сторона hasOne/hasMany. У Post один User — в модели Post: user() { return $this->belongsTo(User::class); }. Доступ: $post->user. FK хранится у этой же таблицы (posts.user_id).',
                'difficulty' => 1,
                'topic' => 'laravel.eloquent_relations',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое belongsToMany простыми словами?',
                'answer' => 'Отношение «многие-ко-многим». У User много Role и Role у многих User. Нужна pivot-таблица role_user с user_id и role_id (по умолчанию Laravel ищет таблицу из имён моделей по алфавиту). На обеих моделях объявляется belongsToMany. Управление связями: attach($id) - добавить, detach($id) - убрать, sync([1,2,3]) - заменить набор полностью, toggle($id) - переключить.',
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
                'answer' => 'Способ заранее подгрузить связанные модели, чтобы избежать N+1. Без with: User::all() + в цикле $user->posts даст 1 + N запросов (по одному на каждого юзера). С with(\'posts\'): два запроса всего - SELECT * FROM users и SELECT * FROM posts WHERE user_id IN (...). Используют два способа: with(\'posts\') в начале цепочки запроса и load(\'posts\') на уже загруженной коллекции/модели. Внутри with можно ограничивать связь замыканием.',
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
