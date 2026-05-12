<?php

namespace Database\Seeders\Data\Categories\Laravel;

class BasicQa
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Laravel',
                'question' => 'Чем отличается Service Provider от Middleware?',
                'answer' => 'Service Provider вызывается до обработки запроса (регистрирует биндинги и инициализирует сервисы). Middleware фильтрует HTTP-запросы по конвейеру до и после контроллера.',
                'difficulty' => 2,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Чем hasOne отличается от belongsTo?',
                'answer' => 'hasOne - обратная сторона связи "один-к-одному" со стороны родителя (FK на дочерней). belongsTo - со стороны дочерней (FK у себя).',
                'difficulty' => 2,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое N+1 проблема и как её решать в Eloquent?',
                'answer' => 'N+1 - N дополнительных запросов на связанные записи при итерации. Решается eager loading через with(), withCount() или предзагрузкой через load().',
                'difficulty' => 3,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Чем отличаются queue jobs от events?',
                'answer' => 'Job - единица фоновой работы, ставится в очередь и выполняется воркером. Event - объект, описывающий факт/сигнал; на него подписаны N listener-ов. По умолчанию listener выполняется СИНХРОННО в том же запросе; для асинхронности listener должен реализовать ShouldQueue - тогда сам listener становится job-ом и уходит в очередь.',
                'difficulty' => 2,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что делает middleware throttle?',
                'answer' => 'Ограничивает число запросов с одного клиента за период (rate limiting), используя кэш для счётчиков. Например, throttle:60,1 - 60 запросов в минуту.',
                'difficulty' => 2,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Чем отличается Request от FormRequest?',
                'answer' => 'FormRequest - наследник Request с валидацией и авторизацией в отдельном классе. Валидация запускается до контроллера, ошибки автоматически возвращаются.',
                'difficulty' => 2,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Policy и Gate?',
                'answer' => 'Gate - замыкание для проверки права действия. Policy - класс, группирующий правила доступа для конкретной модели. Используются через can()/authorize().',
                'difficulty' => 2,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Чем отличается soft delete от обычного delete?',
                'answer' => 'Soft delete устанавливает deleted_at вместо физического удаления. Записи скрываются из выборок, восстанавливаются через restore(), удаляются окончательно через forceDelete().',
                'difficulty' => 2,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое observers в Eloquent?',
                'answer' => 'Класс с обработчиками событий жизненного цикла модели (creating, created, updating, deleted и т.д.). Регистрируется через ObservedBy-атрибут или Model::observe.',
                'difficulty' => 3,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Зачем нужен php artisan optimize?',
                'answer' => 'Связка из четырёх команд: config:cache + route:cache + view:cache + event:cache. Кэширует конфиг, роуты, события и вьюхи в одиночные файлы для production - ускоряет загрузку фреймворка, исключая парсинг при каждом запросе. Сбрасывается через optimize:clear.',
                'difficulty' => 2,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое database transaction и как использовать в Laravel?',
                'answer' => 'Атомарная группа SQL-операций, либо все коммитятся, либо все откатываются. В Laravel - DB::transaction(closure) или явные beginTransaction/commit/rollBack.',
                'difficulty' => 3,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Чем отличаются session, cookie и cache в Laravel?',
                'answer' => 'Cookie - данные у клиента. Session - серверное состояние пользователя, обычно идентифицируется cookie. Cache - общее key-value-хранилище без привязки к пользователю.',
                'difficulty' => 2,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel простыми словами?',
                'answer' => 'PHP-фреймворк для веб-приложений. Даёт готовые решения для маршрутизации, работы с БД, шаблонов, авторизации, валидации, очередей. Стандарт для современной PHP-разработки.',
                'difficulty' => 1,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое контроллер в Laravel?',
                'answer' => 'Класс с методами, обрабатывающий HTTP-запросы. Лежит в app/Http/Controllers. Метод получает Request, обращается к моделям/сервисам и возвращает Response, view или JSON. Связывается с URL через routes.',
                'difficulty' => 1,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое модель в Laravel?',
                'answer' => 'Класс, представляющий одну таблицу в БД. Лежит в app/Models, наследует Eloquent\\Model, имя в единственном числе (User → таблица users). Через модель — CRUD: User::find(5), $user->save(), User::where(...)->get().',
                'difficulty' => 1,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Какие базовые методы есть у Eloquent для чтения?',
                'answer' => 'User::all() — все. User::find(5) — по PK (модель или null). User::findOrFail(5) — то же, но бросит 404. User::where(\'active\', true)->get() — с условием. User::first() — первая. User::count() — сколько.',
                'difficulty' => 1,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как сделать валидацию в Laravel простыми словами?',
                'answer' => 'Внутри контроллера: $request->validate([\'email\' => \'required|email\', \'age\' => \'integer|min:18\']);. Если не подходит — Laravel редиректит назад с ошибками (в Inertia/API возвращает 422 JSON). Для сложных сценариев — Form Request.',
                'difficulty' => 2,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое .env в Laravel и зачем он?',
                'answer' => 'Файл с переменными окружения для конкретного окружения (dev/staging/prod): креды БД, ключи API, debug. Не коммитится (.env.example — шаблон, коммитится). Читай через config()-обёртки, не env() напрямую: в проде config кэшируется.',
                'difficulty' => 2,
                'topic' => 'laravel.basic_qa',
            ],
        ];
    }
}
