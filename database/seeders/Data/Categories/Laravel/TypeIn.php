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
                'answer' => 'optimize:clear объединяет cache:clear, compiled:clear, config:clear, event:clear, route:clear, view:clear.',
                'short_answer' => 'optimize:clear',
                'difficulty' => 2,
                'topic' => 'laravel.type_in',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Метод query builder, возвращающий среднее значение по столбцу.',
                'answer' => 'avg($column) возвращает среднее. Похожие агрегаты: sum, max, min, count. Возвращают числа (не Collection), без округления - округление делайте сами через round().',
                'short_answer' => 'avg',
                'difficulty' => 2,
                'topic' => 'laravel.type_in',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Хелпер, который возвращает singleton-экземпляр приложения.',
                'answer' => 'app() без аргументов вернёт Illuminate\\Foundation\\Application; с аргументом - резолвит зависимость из контейнера.',
                'short_answer' => 'app',
                'difficulty' => 2,
                'topic' => 'laravel.type_in',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Метод модели, перезагружающий её атрибуты из БД.',
                'answer' => 'fresh() возвращает новый экземпляр, refresh() перезаписывает текущий и возвращает $this.',
                'short_answer' => 'refresh',
                'difficulty' => 2,
                'topic' => 'laravel.type_in',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Хелпер для диспатча события (в т.ч. с broadcasting).',
                'answer' => 'event(new SomethingHappened(...)) диспатчит событие; чтобы оно отправилось через broadcasting, класс события должен реализовать ShouldBroadcast (или ShouldBroadcastNow для синхронной отправки).',
                'short_answer' => 'event',
                'difficulty' => 2,
                'topic' => 'laravel.type_in',
            ],
        ];
    }
}
