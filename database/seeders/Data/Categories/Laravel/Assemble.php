<?php

namespace Database\Seeders\Data\Categories\Laravel;

class Assemble
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, assemble_chunks?: ?array<int, string>, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Laravel',
                'question' => 'Собери Eloquent-запрос: всех активных юзеров, упорядоченных по имени.',
                'answer' => 'Типичная цепочка Eloquent:

- **`User::`** — стартует Query Builder от модели.
- **`where(\'active\', 1)`** — фильтр.
- **`orderBy(\'name\')`** — сортировка по возрастанию (для убывания — `orderByDesc(\'name\')`).
- **`get()`** — выполнить запрос и вернуть **`Collection`** моделей.

Важно помнить: пока не вызвал терминальный метод (`get`/`first`/`paginate`/`count`), запрос **не выполняется**. Можно собирать цепочку условно через `when()` и выполнить в конце.',
                'assemble_chunks' => ['User::', "where('active', 1)", '->', "orderBy('name')", '->', 'get()'],
                'code_example' => 'User::where(\'active\', 1)->orderBy(\'name\')->get();',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.assemble',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Собери eager load с ограничением relations.',
                'answer' => 'with принимает имя relation или массив. Замыкание доращивает запрос на загружаемом отношении.',
                'assemble_chunks' => [
                    'Post::',
                    "with(['comments' => fn(\$q) => \$q->latest()])",
                    '->',
                    'paginate(20)',
                ],
                'difficulty' => 3,
                'topic' => 'laravel.assemble',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Собери транзакцию с retry на 3 попытки.',
                'answer' => 'DB::transaction(closure, attempts: N) сам повторяет транзакцию при ошибках конкуренции (deadlock, serialization failure, lock wait timeout - всё, что ConcurrencyErrorDetector считает таковым). На constraint violation, syntax error и обычный QueryException ретрая нет - исключение пробрасывается сразу.',
                'assemble_chunks' => [
                    'DB::',
                    'transaction(function () {',
                    '    Account::lockForUpdate()->find($id);',
                    '    // ...',
                    '}, 3)',
                ],
                'difficulty' => 4,
                'topic' => 'laravel.assemble',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Собери диспатч джобы в очередь high с задержкой 30 секунд.',
                'answer' => 'onQueue выбирает очередь, delay - отложенный запуск.',
                'assemble_chunks' => [
                    'ProcessOrder::',
                    'dispatch($order)',
                    '->',
                    "onQueue('high')",
                    '->',
                    'delay(now()->addSeconds(30))',
                ],
                'difficulty' => 3,
                'topic' => 'laravel.assemble',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Собери rate-limited маршрут в группе.',
                'answer' => 'middleware throttle принимает имя именованного limiter или формат N,M.',
                'assemble_chunks' => [
                    'Route::',
                    "middleware(['auth', 'throttle:60,1'])",
                    '->',
                    'group(function () {',
                    '    Route::get(\'/profile\', ProfileController::class);',
                    '})',
                ],
                'difficulty' => 3,
                'topic' => 'laravel.assemble',
            ],
        ];
    }
}
