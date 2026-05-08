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
                'question' => 'Что такое Eloquent ORM и Active Record?',
                'answer' => 'Eloquent - реализация паттерна Active Record: одна модель = одна таблица, экземпляр модели = строка, методы модели инкапсулируют CRUD и связи.',
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
                'difficulty' => 2,
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
                'difficulty' => 2,
                'topic' => 'laravel.basic_qa',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Чем отличаются session, cookie и cache в Laravel?',
                'answer' => 'Cookie - данные у клиента. Session - серверное состояние пользователя, обычно идентифицируется cookie. Cache - общее key-value-хранилище без привязки к пользователю.',
                'difficulty' => 2,
                'topic' => 'laravel.basic_qa',
            ],
        ];
    }
}
