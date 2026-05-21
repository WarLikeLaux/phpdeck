<?php

namespace Database\Seeders\Data\Categories\Laravel;

class Cloze
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, cloze_text?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Laravel',
                'question' => 'Заполни команду artisan для создания resource-контроллера UserController.',
                'answer' => 'Команда **`php artisan make:controller UserController --resource`** создаёт контроллер сразу с 7 CRUD-методами:

- **`index`** — список.
- **`create`** — форма создания.
- **`store`** — сохранение.
- **`show`** — просмотр.
- **`edit`** — форма редактирования.
- **`update`** — обновление.
- **`destroy`** — удаление.

Подключается одной строкой в `routes/web.php`:

`Route::resource(\'users\', UserController::class);`

Для API используют **`--api`** (без `create` и `edit`).',
                'cloze_text' => 'php artisan {{make:controller}} UserController {{--resource}}',
                'difficulty' => 2,
                'topic' => 'laravel.cloze',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Заполни Eloquent-определение связи "many-to-many" с пивотом role_user.',
                'answer' => '**`belongsToMany`** определяется **на обеих моделях** — это симметричная связь.

**Конвенции имён:**

- **Pivot-таблица** — singular-имена моделей в snake_case **по алфавиту**: `Role` + `User` → `role_user`. Если имя другое — второй аргумент: `belongsToMany(Role::class, \'memberships\')`.
- **FK в pivot** — `user_id` и `role_id` (родительская модель в snake_case + `_id`).

**Полезные модификаторы цепочки:**

- **`withTimestamps()`** — Laravel будет заполнять `created_at`/`updated_at` в pivot.
- **`withPivot([\'role\', \'expires_at\'])`** — подтянуть дополнительные колонки pivot в `$user->roles[0]->pivot`.
- **`using(Membership::class)`** — кастомная pivot-модель с методами и кастами.
- **`as(\'membership\')`** — переименовать свойство `pivot` (`$user->roles[0]->membership`).

**Управление связями:**

- `attach($id, [\'role\' => \'owner\'])` — добавить.
- `detach($id)` — убрать.
- `sync([1, 2, 3])` — заменить весь набор.
- `syncWithoutDetaching([...])` — добавить, ничего не удаляя.',
                'cloze_text' => 'public function roles() {
    return $this->{{belongsToMany}}(Role::class)->{{withTimestamps}}();
}',
                'code_example' => 'Schema::create(\'role_user\', function (Blueprint $table) {
    $table->foreignId(\'user_id\')->constrained()->cascadeOnDelete();
    $table->foreignId(\'role_id\')->constrained()->cascadeOnDelete();
    $table->timestamps();
});',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.cloze',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Заполни вызов очереди с retries и backoff.',
                'answer' => 'Свойства класса Job, реализующего `ShouldQueue`, настраивают поведение при ошибках.

**Основные:**

- **`public int $tries = 5`** — сколько раз воркер повторит job при исключении (1 попытка + 4 ретрая = 5).
- **`public array $backoff = [10, 30, 120]`** — задержка между попытками в секундах. Массив: первая попытка через 10 сек, вторая — 30, третья и далее — 120. Может быть `int` — фиксированная пауза.
- **`public int $maxExceptions = 3`** — лимит unhandled-исключений, после которых job уходит в `failed_jobs` сразу, даже если `$tries` ещё не достигнут.
- **`public int $timeout = 60`** — секунд до принудительного убийства процесса.
- **`public function retryUntil(): \\DateTime`** — альтернатива `$tries`: ретраить пока не пройдёт дедлайн.

**Подводные камни:**

- `$backoff` действует только при ошибке, **не** при `release()`.
- При `SerializesModels` job сериализует **id модели**, а не саму модель — при ретрае модель re-fetch-ится; если её удалили — `ModelNotFoundException`.
- Метод `backoff(): int` (вместо свойства) даёт динамическое значение.',
                'cloze_text' => 'class ProcessOrder implements ShouldQueue {
    public int ${{tries}} = 5;
    public array ${{backoff}} = [10, 30, 120];
}',
                'difficulty' => 3,
                'topic' => 'laravel.cloze',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Заполни scope для активных пользователей и его использование.',
                'answer' => '**Local scope** — публичный метод модели с префиксом `scope`, инкапсулирующий часто используемое условие запроса.

**Как работает:**

- Метод **`scopeActive(Builder $q)`** на модели → доступен как **`User::active()`** (`scope`-префикс отбрасывается, первая буква — в нижний регистр).
- Возвращает `Builder` — поэтому свободно встраивается в цепочку: `User::active()->orderBy(\'name\')->paginate(15)`.
- Принимает аргументы: `scopePopular(Builder $q, int $minViews)` → `Post::popular(1000)`.

**Зачем:**

- Перенос дублирующейся `where`-логики из контроллеров в модель.
- Читабельность: `Post::published()->popular()->withAuthor()` vs три `where` в строку.

**Сравнение с global scope:**

- **Local** — вызывается **явно**, опционально. Не аффектит остальные запросы.
- **Global** (`addGlobalScope`) — применяется **автоматически** ко всем запросам модели (soft delete, multi-tenancy).
- Снять global: `Post::withoutGlobalScope(\'tenant\')->get()`.

**Современная альтернатива (Laravel 11+):** атрибут `#[Scope]` на методе без префикса `scope`.',
                'cloze_text' => 'public function {{scopeActive}}(Builder $q): Builder {
    return $q->where("active", {{true}});
}
// usage:
User::{{active}}()->get();',
                'difficulty' => 3,
                'topic' => 'laravel.cloze',
            ],
        ];
    }
}
