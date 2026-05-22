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
                'answer' => '**Collection** — fluent-обёртка над массивом с десятками методов: `map`, `filter`, `reduce`, `pluck`, `where`, `groupBy`, `sortBy`, `chunk`. По сути «массив с цепочкой методов как в JS Array».

**Что на самом деле возвращает Eloquent — это часто путают:**

| Метод | Тип возврата | Особенность |
|---|---|---|
| `get()`, `all()`, `find([1,2,3])` | `Illuminate\\Database\\Eloquent\\Collection` | Наследник `Support\\Collection` с моделями |
| `cursor()`, `lazy()`, `lazyById()` | `LazyCollection` | Ленивая, держит в памяти одну запись |
| `paginate()`, `simplePaginate()`, `cursorPaginate()` | `LengthAwarePaginator` / `Paginator` / `CursorPaginator` | **НЕ Collection** — отдельный объект с meta пагинации |
| `chunk()`, `chunkById()`, `each()` | `bool` | Ничего не возвращают — передают порции в callback |
| `pluck(\'name\')` | `Support\\Collection` | Уже не Eloquent, скаляры |
| `pluck(\'name\', \'id\')->all()` | `array` | Тоже частый кейс |

**Полезные нюансы:**

- `Eloquent\\Collection` имеет специальные методы: `load()`, `loadMissing()`, `pluck`, `modelKeys()`, `unique` сравнивает по PK.
- `groupBy` принимает строку, callable или массив для multi-level группировки.
- На большом наборе данных `->all()` материализует массив — для стрима использовать `LazyCollection`.
- Фраза «все Eloquent-результаты — Collection» **неточна**: `cursor` отдаёт `LazyCollection`, `paginate` — `Paginator`.',
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
                'answer' => '**Сравнение по памяти и lazy-семантике:**

| | **`Collection`** | **`LazyCollection`** |
|---|---|---|
| Память | **O(N) RAM** — все элементы сразу | **O(1)** — обёртка над `Generator` |
| Когда выполняются операции | Сразу (eager) | **Отложенно** до `forEach`/`reduce`/`first` |
| Source | Массив | Generator / yield |
| Short-circuit на `first()`/`take()` | **Нет** — обходит весь массив | **Да** — стоп на первом совпадении |
| Повторная итерация | **Да** | **Нет** (только через `remember()`) |
| `count()` | O(1) если массив | **Материализует** — снова N память |

**КРИТИЧЕСКАЯ ОСОБЕННОСТЬ — short-circuit:**

- **`Collection->filter(...)->first()`** — сначала отфильтрует **ВЕСЬ** массив, потом возьмёт первый элемент.
- **`LazyCollection->filter(...)->first()`** — остановит генератор **на первом** совпадении.
- Аналогично **`take(N)`** — lazy завершает обход после N совпадений.

**Где применять `LazyCollection`:**

| Сценарий | Источник |
|---|---|
| **Построчная обработка больших файлов** | `fopen` + `yield $line` |
| **`Model::cursor()`** / **`Model::lazy()`** | Eloquent с миллионами строк |
| **CSV-импорт** | `fgetcsv` + `yield` |
| **`LazyCollection::times(INF)`** | Бесконечные последовательности |
| **`Model::lazyById($chunkSize)`** | Batch-обработка с chunkById под капотом |

**Ограничения:**

- **Однопроходный итератор** — после полного обхода нельзя вернуться к началу.
- **`count()`** или повторная итерация требуют **`remember()`** / **`eager()`** — что снова грузит в память (фактически конвертирует в обычную Collection).
- **Нельзя индексировать** — нет `$lazy[5]`.

**Альтернативы:**

- **`chunk(1000)`** на обычной Collection — компромисс: чанк в памяти, но не весь массив.
- **`chunkById()`** на Eloquent — то же на уровне БД.',
                'code_example' => '<?php
use Illuminate\\Support\\LazyCollection;

// 1) Big-file streaming + take(10) — читает только до 10-й ERROR-строки
LazyCollection::make(function () {
    \$handle = fopen("huge.log", "r");
    while ((\$line = fgets(\$handle)) !== false) {
        yield \$line;
    }
    fclose(\$handle);
})
->filter(fn (\$l) => str_contains(\$l, "ERROR"))
->take(10)
->each(fn (\$l) => print \$l);

// 2) Short-circuit на first() — expensiveCheck вызовется не INF раз
LazyCollection::times(INF)
    ->map(fn (\$n) => expensiveCheck(\$n))
    ->first(fn (\$v) => \$v === "match");

// 3) CSV-импорт чанками без памяти на весь файл
LazyCollection::make(function () {
    \$h = fopen("big.csv", "r");
    while ((\$row = fgetcsv(\$h)) !== false) yield \$row;
    fclose(\$h);
})
->chunk(1000)
->each(fn (\$chunk) => Order::insert(\$chunk->toArray()));

// 4) Eloquent lazy() — миллион строк без памяти
User::lazy()->each(fn (\$u) => \$u->recalculateStats());

// 5) Eloquent lazyById() — батч-обработка с пагинацией по PK
User::where("status", "active")
    ->lazyById(1000)
    ->each(fn (\$u) => SendReminderJob::dispatch(\$u));',
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
