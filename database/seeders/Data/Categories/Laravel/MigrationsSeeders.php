<?php

namespace Database\Seeders\Data\Categories\Laravel;

class MigrationsSeeders
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Laravel',
                'question' => 'Что такое миграции в Laravel?',
                'answer' => 'Миграции - это версионируемые описания изменений структуры БД в коде. Простыми словами: вместо ручного SQL вы пишете PHP-классы с методами up (применить) и down (откатить). Команда php artisan migrate применяет невыполненные миграции.',
                'code_example' => 'php artisan make:migration create_posts_table

// в миграции
public function up(): void {
    Schema::create(\'posts\', function (Blueprint $table) {
        $table->id();
        $table->string(\'title\');
        $table->text(\'body\');
        $table->timestamps();
    });
}

public function down(): void {
    Schema::dropIfExists(\'posts\');
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.migrations_seeders',
            ],
            [
                'category' => 'Laravel',
                'question' => 'В чём разница между migrate, rollback, refresh и fresh?',
                'answer' => 'migrate - применяет невыполненные миграции. rollback - откатывает последний batch миграций (через down). refresh - откатывает ВСЕ миграции, потом применяет заново. fresh - удаляет ВСЕ таблицы и применяет миграции (быстрее refresh, но без down). migrate:status - показать статус миграций.',
                'code_example' => 'php artisan migrate
php artisan migrate:rollback --step=1
php artisan migrate:refresh --seed
php artisan migrate:fresh --seed
php artisan migrate:status',
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'laravel.migrations_seeders',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как объявить foreign key и индексы в миграции?',
                'answer' => 'foreign() с references()->on() - старый способ. foreignId()->constrained() - короткий вариант, который сам определяет таблицу. cascadeOnDelete, nullOnDelete, restrictOnDelete - поведение при удалении. Индексы: ->index(), ->unique(), ->primary(), составные индексы передаются массивом.',
                'code_example' => 'Schema::create(\'posts\', function (Blueprint $t) {
    $t->id();
    $t->foreignId(\'user_id\')->constrained()->cascadeOnDelete();
    $t->string(\'slug\')->unique();
    $t->string(\'status\')->index();
    $t->index([\'user_id\', \'status\']);
});',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.migrations_seeders',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Seeders и Factories?',
                'answer' => 'Seeder - класс, который заполняет БД тестовыми/начальными данными. Factory - "фабрика", которая описывает, как генерировать модели с фейковыми данными (через Faker). Используются вместе: фабрика создаёт модели, сидер вызывает фабрику.',
                'code_example' => 'php artisan make:seeder UsersSeeder
php artisan make:factory UserFactory --model=User

// Factory
public function definition(): array {
    return [
        \'name\' => fake()->name(),
        \'email\' => fake()->unique()->safeEmail(),
    ];
}

// Seeder
public function run(): void {
    User::factory()->count(50)->create();
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.migrations_seeders',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как в фабрике создать модель с дочерними записями и пивотом, не вызывая save вручную?',
                'answer' => 'Используйте методы has/for/hasAttached фабрики: User::factory()->has(Post::factory()->count(3))->create() создаст юзера с тремя постами, а ->hasAttached(Role::factory()->count(2), ["assigned_at" => now()]) добавит связи через pivot c дополнительными колонками. Метод for() задаёт родителя для belongsTo-связи (например, Post::factory()->for(User::factory())). Так фабрика сама разруливает foreign keys и pivot-таблицу.',
                'difficulty' => 3,
                'topic' => 'laravel.migrations_seeders',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Чем команда artisan migrate:status отличается от migrate:rollback и зачем нужен squash?',
                'answer' => 'migrate:status показывает таблицу пройденных и непройденных миграций со столбцом batch, ничего не меняя в БД. migrate:rollback откатывает последний batch (или N batches при --step). schema:dump --prune (squash) сворачивает все старые миграции в один SQL-снапшот в database/schema, чтобы свежая install-миграция не прогоняла сотни файлов и стартовала из дампа.',
                'difficulty' => 3,
                'topic' => 'laravel.migrations_seeders',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Какие основные типы колонок в миграции Laravel?',
                'answer' => '$table->id() — BIGINT auto-increment PK. $table->string(\'name\', 255) — VARCHAR. $table->text(\'description\') — TEXT. $table->integer(\'age\'), $table->boolean(\'active\'), $table->decimal(\'price\', 8, 2). $table->timestamp(\'sent_at\'), $table->date(\'birthday\'), $table->json(\'meta\'). $table->foreignId(\'user_id\')->constrained() — FK на users.id одной строкой. $table->timestamps() — created_at + updated_at сразу. $table->softDeletes() — deleted_at для soft delete.',
                'code_example' => 'Schema::create(\'posts\', function (Blueprint $table) {
    $table->id();
    $table->foreignId(\'user_id\')->constrained()->cascadeOnDelete();
    $table->string(\'title\');
    $table->text(\'body\');
    $table->boolean(\'is_published\')->default(false);
    $table->decimal(\'rating\', 3, 2)->nullable();
    $table->json(\'meta\')->nullable();
    $table->timestamp(\'published_at\')->nullable();
    $table->timestamps();
    $table->softDeletes();
});',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'laravel.migrations_seeders',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что делают модификаторы nullable(), default(), unique() в миграции?',
                'answer' => 'nullable() — колонка может быть NULL. default(\'value\') — значение по умолчанию. unique() — UNIQUE-индекс. index() — обычный индекс. Цепочка: $table->string(\'email\')->unique()->nullable()->default(null). primary() — PK на нескольких колонках: $table->primary([\'a\',\'b\']).',
                'difficulty' => 2,
                'topic' => 'laravel.migrations_seeders',
            ],
            [
                'category' => 'Laravel',
                'question' => 'В чём разница между Schema::create и Schema::table?',
                'answer' => 'Schema::create(\'users\', fn($t) => ...) — СОЗДАЁТ новую таблицу. Schema::table(\'users\', fn($t) => ...) — ИЗМЕНЯЕТ существующую (добавить колонку, индекс, переименовать). Внутри Blueprint-callback одинаковые методы, но семантика разная — для add используют $table->string(\'phone\')->after(\'email\').',
                'difficulty' => 2,
                'topic' => 'laravel.migrations_seeders',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое factory и как ей пользоваться?',
                'answer' => 'Класс-генератор тестовых моделей. User::factory()->create() — создаст одного юзера в БД со случайными данными. ->count(10) — десять. ->make() — без сохранения. ->state([\'role\' => \'admin\']) — переопределить поля. В классе UserFactory метод definition() возвращает дефолтные значения через fake().',
                'difficulty' => 2,
                'topic' => 'laravel.migrations_seeders',
            ],
        ];
    }
}
