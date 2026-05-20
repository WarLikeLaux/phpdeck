<?php

namespace Database\Seeders\Data\Categories\Laravel;

class Artisan
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Laravel',
                'question' => 'Что такое php artisan?',
                'answer' => '**Artisan** — встроенный CLI-инструмент Laravel. Запускается из корня проекта командой `php artisan <команда>`.

Что им делают:

- **Генерация кода**: `make:controller`, `make:model`, `make:migration`, `make:test`.
- **БД**: `migrate`, `migrate:fresh --seed`, `db:seed`.
- **Очереди**: `queue:work`, `queue:retry`.
- **Кеш**: `cache:clear`, `config:cache`, `route:cache`.
- **Отладка**: `tinker` (REPL), `route:list`, `about`.
- **Свои команды** — через `make:command`.

`php artisan list` покажет все доступные команды.',
                'code_example' => 'php artisan list                 # список всех команд
php artisan make:model Post -mfc # модель + миграция + фабрика + контроллер
php artisan migrate
php artisan db:seed
php artisan tinker
php artisan route:list
php artisan optimize',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'laravel.artisan',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Tinker?',
                'answer' => '**Tinker** — интерактивный REPL для Laravel (под капотом — `psy/psysh`). Командная строка, в которой можно писать PHP-код с доступом ко всем классам приложения: моделям, фасадам, сервисам.

Зачем используют:

- **Отладка**: быстро проверить, что вернёт запрос или метод модели.
- **Разовые операции**: пересчитать поле, заблокировать юзера, дёрнуть Job.
- **Изучение API**: попробовать вызовы Eloquent/коллекций без написания контроллера.

Запуск — `php artisan tinker`. Выход — `exit` или `Ctrl+D`.

Важно: **в проде использовать осторожно** — изменения сразу пишутся в боевую БД.',
                'code_example' => 'php artisan tinker

>>> User::count()
=> 42

>>> $u = User::find(1)
=> App\Models\User { ... }

>>> $u->update([\'name\' => \'Test\'])
=> true',
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'laravel.artisan',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что делают artisan optimize, route:cache, config:cache, view:cache?',
                'answer' => 'config:cache - объединяет все config-файлы в один кеш. route:cache - кеширует роуты в один файл. view:cache - предкомпилирует Blade-шаблоны. event:cache - кеширует события. optimize - вызывает несколько кешей сразу. Все вместе ускоряют работу в продакшене. После деплоя нужно выполнить, после изменений - сбрасывать СООТВЕТСТВУЮЩИЙ кеш или все сразу через optimize:clear (это объединяет config:clear, route:clear, view:clear, event:clear, cache:clear). Частая ошибка - запустить только config:clear после правок и удивляться, что закешированные роуты/вьюхи всё ещё старые: каждый clear сбрасывает только свой кеш.',
                'code_example' => 'php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan optimize

# сброс
php artisan optimize:clear',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'laravel.artisan',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как создать кастомную artisan команду?',
                'answer' => 'Через php artisan make:command. Класс наследуется от Command, имеет $signature (имя и аргументы) и $description. Логика в методе handle(). Зависимости можно инжектить в handle() или конструктор. Синтаксис опций: {--queue} - bool-флаг, {--queue=} - опция со значением, {--queue=default} - со значением по умолчанию, {--Q|queue=} - короткий alias.',
                'code_example' => 'php artisan make:command SendEmails

class SendEmails extends Command {
    protected $signature = \'app:send-emails {user} {--Q|queue=default} {--dry}\';
    protected $description = \'Send emails to user\';

    public function handle(): int {
        $userId = $this->argument(\'user\');
        $queue  = $this->option(\'queue\');     // "default" если не передано
        $dry    = $this->option(\'dry\');       // bool
        $this->info("Sending to user {$userId} on {$queue}");
        return Command::SUCCESS;
    }
}

// Запуск
php artisan app:send-emails 1 --queue=high
php artisan app:send-emails 1 -Qhigh --dry',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.artisan',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как планировать задачи (Task Scheduling) в Laravel?',
                'answer' => 'В Laravel есть встроенный планировщик задач, описываемый в коде, а не в crontab. На сервере добавляется ОДНА cron-запись на php artisan schedule:run каждую минуту, всё остальное - в коде. Доступны методы everyMinute, hourly, daily, cron(), withoutOverlapping и др.',
                'code_example' => '// routes/console.php (Laravel 11+) или Console/Kernel
Schedule::command(\'reports:generate\')->dailyAt(\'02:00\');
Schedule::job(new CleanLogs)->weekly();
Schedule::call(fn() => DB::table(\'sessions\')->delete())
    ->everyFifteenMinutes()
    ->withoutOverlapping();

// crontab
* * * * * cd /var/www && php artisan schedule:run >> /dev/null 2>&1',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.artisan',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что делает php artisan route:list?',
                'answer' => 'Выводит таблицу всех зарегистрированных маршрутов: HTTP-метод, URL, имя, действие (`контроллер@метод`), middleware.

Полезно, чтобы:

- Понять, какие endpoints вообще есть в приложении.
- Найти имя маршрута для `route(\'...\')`.
- Отладить «почему 404» — посмотреть, есть ли маршрут.

Частые фильтры:

- `--path=user` — только содержащие `user` в URL.
- `--method=POST` — только POST.
- `--name=admin.*` — по имени.
- `--except-vendor` — без роутов пакетов.
- `-v` — показать middleware каждого маршрута.',
                'code_example' => 'php artisan route:list
php artisan route:list --path=user
php artisan route:list --method=POST
php artisan route:list --name=admin.*
php artisan route:list -v --except-vendor',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'laravel.artisan',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Какие команды artisan для создания файлов самые частые?',
                'answer' => 'Все генерирующие команды начинаются с `make:`.

- `make:controller` — контроллер (с `--resource` — все CRUD-методы).
- `make:model` — модель (с `-mfsc` ещё миграция/фабрика/сидер/контроллер).
- `make:migration` — миграция.
- `make:seeder`, `make:factory` — тестовые данные.
- `make:request` — `FormRequest` для валидации.
- `make:middleware` — middleware.
- `make:job`, `make:event`, `make:listener` — фон/события.
- `make:command` — своя artisan-команда.
- `make:resource` — API Resource (трансформер JSON).
- `make:policy`, `make:rule` — авторизация и правила валидации.
- `make:test` — тест.',
                'code_example' => 'php artisan make:controller PostController --resource
php artisan make:model Post -mfsc
php artisan make:migration add_status_to_posts_table --table=posts
php artisan make:request StorePostRequest
php artisan make:middleware EnsureUserIsActive
php artisan make:job ProcessPodcast
php artisan make:resource PostResource
php artisan make:policy PostPolicy --model=Post
php artisan make:test PostControllerTest',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'laravel.artisan',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что делает php artisan serve?',
                'answer' => 'Поднимает встроенный PHP-сервер для разработки на `http://localhost:8000`.

- Под капотом — обёртка над `php -S`, **однопоточный** dev-сервер (один запрос за раз).
- Удобно для быстрого локального старта без `nginx`/`apache`.
- **Только для разработки!** В проде — FPM или Octane за nginx.

Полезные опции:

- `--host=0.0.0.0` — доступ с других машин в сети (по умолчанию `127.0.0.1`).
- `--port=8080` — другой порт (по умолчанию `8000`).
- `--tries=10` — попытки занять следующий свободный порт.',
                'code_example' => '# Запустить на localhost:8000
php artisan serve

# Доступно с других машин в сети
php artisan serve --host=0.0.0.0 --port=8080

# В отдельных окнах одновременно: web-сервер + watcher Vite + очереди
php artisan serve
npm run dev
php artisan queue:work',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'laravel.artisan',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что делает php artisan migrate:fresh --seed?',
                'answer' => '**`migrate:fresh`** — дропает **ВСЕ** таблицы (включая `migrations`) и заново выполняет миграции с нуля.

- Быстрее `migrate:refresh`: тот сначала откатывает миграции через `down()`, а `fresh` просто `DROP`.
- Флаг `--seed` после миграций запускает `DatabaseSeeder`.

Используется на dev для сброса БД к нулевому состоянию.

**В проде запускать НЕЛЬЗЯ** — потеряются все данные пользователей. Laravel требует подтверждение, в CI обходят через `--force`.

Соседние команды:

- `migrate:refresh --seed` — медленнее: `rollback` всех + `migrate`.
- `migrate:rollback` — откат последнего batch.
- `db:seed` — только сидеры, без миграций.',
                'code_example' => '# Полный сброс БД + миграции + сидеры (dev)
php artisan migrate:fresh --seed

# Только конкретные сидеры
php artisan migrate:fresh --seeder=UsersSeeder

# В CI / non-interactive окружении
php artisan migrate:fresh --seed --force

# Альтернативы
php artisan migrate:refresh --seed   # медленнее: rollback всех + migrate
php artisan migrate:rollback         # откат последнего batch
php artisan db:seed                  # только сидеры, без миграций',
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'laravel.artisan',
            ],
        ];
    }
}
