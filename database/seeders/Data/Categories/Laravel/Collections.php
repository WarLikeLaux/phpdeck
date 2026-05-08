<?php

namespace Database\Seeders\Data\Categories\Laravel;

class Collections
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Laravel',
                'question' => 'Что такое Collections в Laravel?',
                'answer' => 'Collection - это обёртка вокруг массива с десятками методов: map, filter, reduce, pluck, where, groupBy, sortBy, chunk и т.д. Простыми словами: "удобный" массив с цепочкой методов как в JavaScript. Что возвращает Eloquent: get() / all() / find() с массивом id - Eloquent\\Collection (наследник Support\\Collection с моделями); cursor() / lazy() / lazyById() - LazyCollection (ленивая, не материализует все записи в памяти); paginate() / simplePaginate() / cursorPaginate() - LengthAwarePaginator / Paginator / CursorPaginator (НЕ Collection, отдельный объект с метаданными пагинации); chunk() / chunkById() / each() - не возвращают, передают порции в callback. Фраза "все Eloquent-результаты - Collection" неточна: cursor и paginate отдают другие типы.',
                'code_example' => 'collect([1, 2, 3, 4])
    ->filter(fn($n) => $n % 2 === 0)
    ->map(fn($n) => $n * 10)
    ->sum(); // 60

User::all()
    ->groupBy(\'country\')
    ->map(fn($users) => $users->count());',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.collections',
            ],
            [
                'category' => 'Laravel',
                'question' => 'В чём разница между Collection и LazyCollection и когда её использовать?',
                'answer' => 'Collection держит все элементы в памяти сразу - O(N) RAM. LazyCollection обёртывает PHP-Generator: операции (map/filter/take) не выполняются до первого forEach/reduce и не материализуют весь поток - O(1) память. Идеальна для построчной обработки больших файлов, cursor()-выборок Eloquent, импорта CSV. КРИТИЧЕСКАЯ ОСОБЕННОСТЬ - short-circuit на first()/take(): LazyCollection->filter(...)->first() остановит генератор на первом совпадении, тогда как обычная Collection->filter()->first() сначала отфильтрует ВЕСЬ массив, потом возьмёт первый элемент. То же для take(N) - lazy завершает обход после N совпадений. Ограничение: итератор однопроходный, count() или повторная итерация требуют remember()/eager(), что снова грузит в память.',
                'code_example' => 'use Illuminate\Support\LazyCollection;

// Big-file streaming + take(10) - читает только до 10-й ERROR-строки
LazyCollection::make(function () {
    $handle = fopen(\'huge.log\', \'r\');
    while (($line = fgets($handle)) !== false) {
        yield $line;
    }
    fclose($handle);
})->filter(fn($l) => str_contains($l, \'ERROR\'))
  ->take(10)
  ->each(fn($l) => print $l);

// Short-circuit: ленивая остановка на first()
LazyCollection::times(INF)
    ->map(fn($n) => expensiveCheck($n))
    ->first(fn($v) => $v === \'match\'); // expensiveCheck вызовется N раз, не INF

// CSV-импорт чанками без памяти на весь файл
LazyCollection::make(function () {
    $h = fopen("big.csv", "r");
    while (($row = fgetcsv($h)) !== false) yield $row;
    fclose($h);
})->chunk(1000)->each(fn($chunk) => Order::insert($chunk->toArray()));',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.collections',
            ],
        ];
    }
}
