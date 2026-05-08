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
        ];
    }
}
