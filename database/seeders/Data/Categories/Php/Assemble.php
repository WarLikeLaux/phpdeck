<?php

namespace Database\Seeders\Data\Categories\Php;

class Assemble
{
    public static function all(): array
    {
        return [
            [
                'category' => 'PHP',
                'question' => 'Собери цепочку Collection: уникальные emails из активных юзеров.',
                'answer' => 'Laravel Collection - fluent-обёртка над массивом. collect() возвращает eager-коллекцию: каждый шаг (filter, pluck, unique, values) сразу материализует промежуточный массив. Для ленивой обработки больших наборов нужен LazyCollection (LazyCollection::make() / Model::cursor() / ->lazy()). Здесь данные уже в памяти, поэтому eager-цепочка уместна.',
                'assemble_chunks' => ['collect($users)', '->', 'filter(fn($u) => $u->active)', '->', 'pluck(\'email\')', '->', 'unique()', '->', 'values()', '->', 'all()'],
                'difficulty' => 3,
                'topic' => 'php.assemble',
            ],
            [
                'category' => 'PHP',
                'question' => 'Собери try/catch для нескольких типов исключений.',
                'answer' => 'Multi-catch (PHP 7.1+): TypeA|TypeB $e - общий блок для нескольких типов.',
                'assemble_chunks' => ['try {', '    $client->send($request);', '} catch (', 'NetworkException ', '| ', 'TimeoutException ', '$e) {', '    report($e);', '}'],
                'difficulty' => 3,
                'topic' => 'php.assemble',
            ],
        ];
    }
}
