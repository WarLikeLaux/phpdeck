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
                'answer' => 'belongsToMany определяется на обеих моделях. Пивот-таблица по умолчанию называется в алфавитном порядке singular-имён моделей.',
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
                'answer' => 'public свойства $tries и $backoff на классе Job настраивают количество попыток и задержку между ними.',
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
                'answer' => 'Локальный scope - метод scopeXxx, доступный без префикса через query builder.',
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
