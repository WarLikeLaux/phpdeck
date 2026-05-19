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
                'answer' => 'Фабрики Laravel умеют автоматически создавать связи через has(), for() и hasAttached(). 1) has(Factory $factory) - для hasMany/hasOne: создаёт родителя, потом дочерние записи с правильным FK. 2) for(Factory $factory) - для belongsTo: сначала создаёт родителя, потом записывает его id в текущую модель. 3) hasAttached(Factory $factory, array $pivotData) - для belongsToMany: создаёт связанные модели и записи в pivot-таблице с дополнительными колонками. 4) Магические методы по имени отношения: ->hasPosts(3), ->forAuthor() - alias к has()/for() с авто-распознаванием класса фабрики. Так фабрика сама разруливает FK и pivot - в seeder/тесте не нужно сохранять руками.',
                'code_example' => '<?php
// hasMany - юзер с 3 постами
$user = User::factory()
    ->has(Post::factory()->count(3))
    ->create();

// Тот же через магический метод (если есть relation posts)
$user = User::factory()->hasPosts(3)->create();

// belongsTo - пост с автором
$post = Post::factory()
    ->for(User::factory()->state(["role" => "admin"]))
    ->create();

// belongsToMany с дополнительными pivot-колонками
$user = User::factory()
    ->hasAttached(
        Role::factory()->count(2),
        ["assigned_at" => now(), "assigned_by" => 1]
    )
    ->create();

// Вложенные связи
$post = Post::factory()
    ->for(User::factory())                          // автор
    ->has(Comment::factory()->count(5)              // 5 комментов
        ->for(User::factory(), "author"))           // у каждого свой автор
    ->create();

// Кастомный foreign key (если не стандартное имя)
User::factory()
    ->has(Post::factory()->count(3), "publishedPosts")
    ->create();',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.migrations_seeders',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Чем команда artisan migrate:status отличается от migrate:rollback и зачем нужен squash?',
                'answer' => 'migrate:status показывает таблицу пройденных и непройденных миграций со столбцом batch (номер пакета, в котором миграция была применена); ничего не меняет в БД, чисто read-only - полезно для debug в CI и проверке состояния прода. migrate:rollback откатывает миграции последнего batch через метод down(); с --step=N - последние N batch-ей; с --pretend - показывает SQL без выполнения. migrate:reset - откатывает все, migrate:refresh - откат + повтор, migrate:fresh - drop всех таблиц + migrate (быстрее refresh, но без down()). Зачем squash (schema:dump --prune): за годы накапливаются сотни миграций, и новый разработчик тратит минуты на их прогон с нуля. schema:dump компилирует ТЕКУЩУЮ структуру БД в один SQL-снапшот в database/schema/{driver}-schema.sql; --prune ещё и удаляет сами файлы старых миграций. При следующем migrate (на пустой БД) Laravel сначала залит снапшот, потом применит миграции, добавленные ПОСЛЕ снапшота. Откат старых миграций после squash, разумеется, невозможен.',
                'code_example' => '# Состояние миграций - где какой batch
php artisan migrate:status

# Откатить последний batch
php artisan migrate:rollback

# Откатить последние 3 batch-а
php artisan migrate:rollback --step=3

# Посмотреть SQL без выполнения
php artisan migrate:rollback --pretend

# Откатить все
php artisan migrate:reset

# Refresh - откат всех + migrate (с down)
php artisan migrate:refresh

# Fresh - DROP всех таблиц + migrate (без down, быстрее)
php artisan migrate:fresh --seed

# Squash - свернуть существующие миграции в snapshot
php artisan schema:dump
php artisan schema:dump --prune   # + удалить файлы миграций

# После squash файл database/schema/mysql-schema.sql
# применится автоматически при следующем migrate на пустой БД',
                'code_language' => 'bash',
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
                'answer' => 'Модификаторы — это методы, цепляемые после объявления колонки, которые меняют её свойства. nullable() — разрешает NULL. default($value) — значение по умолчанию. unique() — UNIQUE-индекс. index() — обычный индекс. ->after(\'col\') — позиция при ADD COLUMN (MySQL). ->change() — изменить существующую колонку. ->comment(\'...\') — комментарий в схеме. Цепочка читается слева направо.',
                'code_example' => 'Schema::create(\'users\', function (Blueprint $table) {
    $table->id();
    $table->string(\'email\')->unique();
    $table->string(\'phone\')->nullable();
    $table->boolean(\'is_active\')->default(true);
    $table->string(\'role\')->default(\'user\')->index();
    $table->timestamps();

    // составной PK / уникальный индекс
    $table->unique([\'tenant_id\', \'email\']);
});

// добавить колонку после email
Schema::table(\'users\', function (Blueprint $table) {
    $table->string(\'phone\')->nullable()->after(\'email\');
});',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.migrations_seeders',
            ],
            [
                'category' => 'Laravel',
                'question' => 'В чём разница между Schema::create и Schema::table?',
                'answer' => 'Schema::create(\'users\', fn ($t) => ...) — СОЗДАЁТ новую таблицу (CREATE TABLE). Schema::table(\'users\', fn ($t) => ...) — ИЗМЕНЯЕТ существующую (ALTER TABLE): добавить колонку, индекс, FK, переименовать. Внутри Blueprint-замыкания методы те же ($table->string(\'phone\')), но семантика разная. Для add-операций используют ->after(\'col\') (MySQL), для изменения существующей колонки — ->change() (требует пакета doctrine/dbal в Laravel 10 и ниже; в Laravel 11+ работает нативно).',
                'code_example' => '// Создать таблицу
Schema::create(\'posts\', function (Blueprint $table) {
    $table->id();
    $table->string(\'title\');
    $table->timestamps();
});

// Добавить колонку
Schema::table(\'posts\', function (Blueprint $table) {
    $table->text(\'body\')->nullable()->after(\'title\');
});

// Изменить тип / переименовать
Schema::table(\'posts\', function (Blueprint $table) {
    $table->string(\'title\', 500)->change();
    $table->renameColumn(\'body\', \'content\');
});

// Удалить колонку
Schema::table(\'posts\', function (Blueprint $table) {
    $table->dropColumn(\'content\');
});',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.migrations_seeders',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое factory и как ей пользоваться?',
                'answer' => 'Factory — класс-генератор моделей с фейковыми данными для тестов и сидеров. User::factory()->create() сохраняет в БД, ->make() возвращает в памяти без save. ->count(N) делает массовую генерацию, ->state([...]) и именованные state-методы переопределяют поля. Через has()/for() сразу создаются связи. В классе UserFactory метод definition() задаёт дефолты через fake() (Faker).',
                'code_example' => '// database/factories/UserFactory.php
class UserFactory extends Factory {
    public function definition(): array {
        return [
            \'name\'  => fake()->name(),
            \'email\' => fake()->unique()->safeEmail(),
            \'role\'  => \'user\',
        ];
    }

    // именованный state
    public function admin(): static {
        return $this->state(fn () => [\'role\' => \'admin\']);
    }
}

// В тесте / сидере
User::factory()->create();                         // 1 юзер в БД
User::factory()->count(50)->create();              // 50 юзеров
User::factory()->admin()->create();                // юзер с role=admin
User::factory()->make();                           // без save
User::factory()->has(Post::factory()->count(3))->create(); // с постами',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.migrations_seeders',
            ],
        ];
    }
}
