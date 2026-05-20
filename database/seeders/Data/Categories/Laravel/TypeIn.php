<?php

namespace Database\Seeders\Data\Categories\Laravel;

class TypeIn
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, short_answer?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Laravel',
                'question' => 'Команда artisan, очищающая весь app-cache (config, route, view, events).',
                'answer' => '**`php artisan optimize:clear`** — одной командой сбрасывает **все** кеши Laravel.

Объединяет:

- **`cache:clear`** — application cache.
- **`compiled:clear`** — скомпилированные классы (`bootstrap/cache/compiled.php`).
- **`config:clear`** — кеш конфигов.
- **`event:clear`** — кеш событий.
- **`route:clear`** — кеш маршрутов.
- **`view:clear`** — скомпилированные Blade-шаблоны.

Полезно после деплоя или когда что-то «странно работает» в dev. Парная команда — **`php artisan optimize`** — наоборот, прогревает все кеши для прода.',
                'short_answer' => 'optimize:clear',
                'difficulty' => 2,
                'topic' => 'laravel.type_in',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Метод query builder, возвращающий среднее значение по столбцу.',
                'answer' => '**`avg($column)`** возвращает среднее значение по столбцу.

Семейство агрегатных методов Query Builder и Eloquent:

- **`sum($column)`** — сумма.
- **`avg($column)`** — среднее.
- **`max($column)`** — максимум.
- **`min($column)`** — минимум.
- **`count()`** — количество строк.

Все возвращают **число** (или `null` при пустой выборке), а не `Collection`. Округление — самим через `round()`.

Пример: `Order::where(\'status\', \'paid\')->avg(\'total\')`.',
                'short_answer' => 'avg',
                'difficulty' => 2,
                'topic' => 'laravel.type_in',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Хелпер, который возвращает singleton-экземпляр приложения.',
                'answer' => 'Хелпер **`app()`** — главный мост к Service Container.

Поведение:

- **`app()`** без аргументов — возвращает сам контейнер (`Illuminate\\Foundation\\Application`).
- **`app(UserService::class)`** — резолвит зависимость через контейнер (то же, что `resolve()` и `App::make()`).
- **`app(Service::class, [\'id\' => 5])`** — резолвит с явными параметрами конструктора.

Полезные методы самого `Application`:

- `app()->environment(\'production\')` — текущее окружение.
- `app()->isProduction()`, `app()->isLocal()` — короткие проверки.
- `app()->runningInConsole()` — внутри artisan/CLI.
- `app()->bound($abstract)` — зарегистрирован ли биндинг.',
                'short_answer' => 'app',
                'difficulty' => 2,
                'topic' => 'laravel.type_in',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Метод модели, перезагружающий её атрибуты из БД.',
                'answer' => 'Два метода Eloquent для получения свежих данных из БД:

- **`refresh()`** — перезаписывает атрибуты **текущего** инстанса и возвращает `$this`. Все ссылки на этот объект увидят обновлённые поля.
- **`fresh()`** — возвращает **новый** инстанс из БД. Текущий объект не трогает.

Когда что брать:

- **`refresh()`** — когда хочешь обновить объект «на месте» (например, после `$user->update()` другие сервисы видят обновлённые поля).
- **`fresh()`** — когда нужно сравнить «что было — что стало», или объект может быть `null` (если запись удалили).

Оба бьют в БД одним SELECT по PK.

Пример: `$user->refresh()` после `event(new UserUpdated($user))`, чтобы получить значения, выставленные listener-ом.',
                'short_answer' => 'refresh',
                'difficulty' => 2,
                'topic' => 'laravel.type_in',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Хелпер для диспатча события (в т.ч. с broadcasting).',
                'answer' => 'Глобальный хелпер **`event(new EventClass(...))`** диспатчит событие — все зарегистрированные на него listeners получат объект и вызовутся.

Эквивалент: `EventClass::dispatch(...)` — если у события есть трейт `Dispatchable` (Laravel добавляет его автоматически при `make:event`).

Для **broadcasting** (трансляции событий клиенту через WebSocket):

- Класс события должен реализовать **`ShouldBroadcast`** — отправится через очередь.
- Или **`ShouldBroadcastNow`** — отправится **синхронно**, минуя очередь.
- Должен быть метод `broadcastOn()`, возвращающий `Channel`/`PrivateChannel`/`PresenceChannel`.

В тестах диспатч можно «глушить»: **`Event::fake()`** + `Event::assertDispatched(MyEvent::class)`.',
                'short_answer' => 'event',
                'difficulty' => 2,
                'topic' => 'laravel.type_in',
            ],
        ];
    }
}
