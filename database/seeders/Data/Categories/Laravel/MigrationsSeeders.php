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
                'answer' => '**Миграции** — версионируемые описания изменений структуры БД в коде. Вместо ручного `CREATE TABLE`/`ALTER TABLE` пишем PHP-классы.

Что даёт:

- **История изменений** в Git наравне с кодом.
- **Воспроизводимая БД** — `php artisan migrate` на новом окружении/CI собирает схему с нуля.
- **Откат** — метод `down()` отменяет изменения миграции.
- **Команда в коллективе** — все накатывают одинаковую схему, никто не пишет SQL руками в проде.

Структура файла:

- Лежат в `database/migrations` с префиксом-таймстампом — порядок применения по дате.
- Два метода: **`up()`** (применить) и **`down()`** (откатить).
- Создаются через `php artisan make:migration create_posts_table`.

Какие миграции уже применены — в таблице `migrations` (с колонкой `batch`).',
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
                'answer' => 'Все основные команды:

- **`migrate`** — применяет **невыполненные** миграции. Безопасна для прода.
- **`migrate:rollback`** — откатывает **последний batch** через `down()`. Опция `--step=N` — откатить N batch-ей.
- **`migrate:refresh`** — `rollback` всех миграций + `migrate` заново. Использует `down()`.
- **`migrate:fresh`** — **`DROP`** всех таблиц + `migrate`. **Быстрее** `refresh`, но `down()` не вызывает. **Только dev**.
- **`migrate:status`** — read-only вывод: какие миграции применены, в каком batch.
- **`migrate:reset`** — откатить **все** миграции до пустой БД.

Опасные на проде: `fresh`, `refresh`, `reset` — теряют данные. Безопасные: `migrate`, `migrate:status`.

В CI часто используют `--force` (без интерактивного подтверждения) и `--pretend` (показать SQL без выполнения).',
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
                'answer' => 'Две связанные вещи для наполнения БД данными:

- **Factory** — описывает, **как генерировать одну модель** с фейковыми данными через Faker. Лежит в `database/factories`. Создаётся через `make:factory UserFactory --model=User`. Метод `definition()` возвращает массив дефолтных полей.
- **Seeder** — класс, **запускающий** массовое создание (через `factory()->count(50)->create()`) или вставляющий реальные справочные данные (роли, типы, страны). Лежит в `database/seeders`. Точка входа — `DatabaseSeeder`.

Запуск:

- **`php artisan db:seed`** — выполнить `DatabaseSeeder`.
- **`php artisan db:seed --class=UsersSeeder`** — конкретный сидер.
- **`php artisan migrate:fresh --seed`** — пересоздать БД и сразу заполнить.

Factory = **«как генерировать»**, Seeder = **«когда и сколько вставить»**.',
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
                'answer' => 'Основные методы `Blueprint` (внутри `Schema::create`):

**Идентификаторы:**
- `$table->id()` — `BIGINT UNSIGNED AUTO_INCREMENT` PK.
- `$table->uuid(\'id\')->primary()` — UUID PK.

**Строки и текст:**
- `$table->string(\'name\', 255)` — `VARCHAR`.
- `$table->text(\'description\')` — `TEXT`.

**Числа:**
- `$table->integer(\'age\')`, `$table->bigInteger(\'views\')`.
- `$table->decimal(\'price\', 8, 2)`, `$table->float(\'rating\')`.

**Дата/время и прочее:**
- `$table->boolean(\'active\')`, `$table->date(\'birthday\')`, `$table->timestamp(\'sent_at\')`.
- `$table->json(\'meta\')`, `$table->enum(\'role\', [\'admin\', \'user\'])`.

**Стандартные комбинации:**
- `$table->foreignId(\'user_id\')->constrained()` — FK на `users.id` одной строкой.
- `$table->timestamps()` — сразу `created_at` + `updated_at`.
- `$table->softDeletes()` — `deleted_at` для soft delete.',
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
                'answer' => 'Модификаторы — методы, **цепляемые после объявления колонки**, меняющие её свойства. Цепочка читается слева направо.

Самые частые:

- **`nullable()`** — разрешает `NULL`.
- **`default($value)`** — значение по умолчанию.
- **`unique()`** — `UNIQUE`-индекс на колонку.
- **`index()`** — обычный индекс.
- **`unsigned()`** — без знака (для `integer`).
- **`comment(\'...\')`** — комментарий в схеме.

Изменение схемы:

- **`->after(\'col\')`** — позиция при `ADD COLUMN` (MySQL).
- **`->change()`** — изменить **существующую** колонку. В Laravel 11+ работает нативно; в L10 и ниже нужен `doctrine/dbal`.

Составные индексы — отдельным вызовом: `$table->unique([\'tenant_id\', \'email\'])`.',
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
                'answer' => 'Два разных типа операций над схемой:

- **`Schema::create(\'users\', fn ($t) => ...)`** — **СОЗДАЁТ** новую таблицу (`CREATE TABLE`). Падает, если таблица уже есть.
- **`Schema::table(\'users\', fn ($t) => ...)`** — **ИЗМЕНЯЕТ** существующую (`ALTER TABLE`): добавить колонку/индекс/FK, переименовать.

Внутри Blueprint-замыкания методы те же (`$table->string(\'phone\')`), но семантика разная.

Для модификации:

- **`->after(\'col\')`** — позиция при `ADD COLUMN` (MySQL-only).
- **`->change()`** — изменить тип/длину **существующей** колонки. В Laravel 11+ нативно; в L10 — нужен `doctrine/dbal`.
- **`renameColumn(\'old\', \'new\')`** — переименование.
- **`dropColumn(\'name\')`** — удалить колонку.

Удаление таблицы:

- **`Schema::dropIfExists(\'posts\')`** — обычно используется в `down()`.',
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
                'answer' => '**Factory** — класс-генератор моделей с фейковыми данными для тестов и сидеров. Лежит в `database/factories`, создаётся через `php artisan make:factory UserFactory --model=User`.

Запуск из теста/сидера:

- **`User::factory()->create()`** — создать **и сохранить** в БД.
- **`User::factory()->make()`** — создать **в памяти**, без `save`.
- **`User::factory()->count(50)->create()`** — массовая генерация.

Гибкость:

- **`->state([\'role\' => \'admin\'])`** — переопределить поля разово.
- **Именованные state-методы** — `->admin()`, `->suspended()` (определяют в самой фабрике).
- **`->has(Post::factory()->count(3))`** — сразу создать связанные записи.
- **`->for(User::factory())`** — обратная сторона, `belongsTo`.

В классе фабрики метод **`definition()`** задаёт дефолты через `fake()` (Faker — генератор имён, email, текста, чисел).',
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
