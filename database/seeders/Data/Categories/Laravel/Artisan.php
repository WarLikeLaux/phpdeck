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
                'answer' => 'Artisan - это CLI-интерфейс Laravel. Простыми словами: командная строка для генерации кода (make:), управления БД (migrate), очередями (queue:work), кешем (cache:clear) и т.д. Можно создавать свои команды через make:command.',
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
                'answer' => 'Tinker - это интерактивный REPL (как python REPL) для Laravel. Простыми словами: командная строка, в которой можно писать PHP-код с доступом ко всем классам Laravel. Удобно для отладки, проверки моделей, выполнения разовых операций.',
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
                'answer' => 'Выводит таблицу всех зарегистрированных маршрутов: HTTP-метод, URL, имя, действие (контроллер@метод). Полезно для понимания «какие endpoints есть в приложении». Фильтры: --path=user (только содержащие user), --method=POST, --name=admin.*.',
                'difficulty' => 1,
                'topic' => 'laravel.artisan',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Какие команды artisan для создания файлов самые частые?',
                'answer' => 'make:controller, make:model (с опцией -mfsc создаёт и migration/factory/seeder/controller), make:migration, make:seeder, make:factory, make:request, make:job, make:event, make:listener, make:middleware, make:command, make:resource, make:test.',
                'difficulty' => 1,
                'topic' => 'laravel.artisan',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что делает php artisan serve?',
                'answer' => 'Поднимает встроенный PHP-сервер для разработки на localhost:8000. Удобно для быстрого тестирования без настройки nginx/apache. Только для dev! В проде используется FPM/Octane.',
                'difficulty' => 1,
                'topic' => 'laravel.artisan',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что делает php artisan migrate:fresh --seed?',
                'answer' => 'fresh — дропает ВСЕ таблицы и заново выполняет миграции (полностью свежая схема). --seed — после миграций запускает сидеры. Используется на dev для сброса БД к нулевому состоянию. В проде запускать НЕЛЬЗЯ — потеряются все данные.',
                'difficulty' => 2,
                'topic' => 'laravel.artisan',
            ],
        ];
    }
}
