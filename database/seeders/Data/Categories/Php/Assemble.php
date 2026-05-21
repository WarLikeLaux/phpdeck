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
                'answer' => '**Laravel Collection** — fluent-обёртка над массивом.

**Eager vs Lazy:**
- `collect()` — **eager**: каждый шаг (`filter`, `pluck`, `unique`, `values`) сразу материализует промежуточный массив.
- `LazyCollection` — **lazy** через генераторы: `LazyCollection::make()`, `Model::cursor()`, `->lazy()`.

**Когда что:**
- Данные **уже в памяти** и набор небольшой → eager-цепочка (как здесь).
- Большой dataset / стрим из БД → `LazyCollection`, иначе OOM на промежуточных массивах.

**Подвох:** `unique()` сравнивает через `==` (loose); для строгого сравнения — `unique(strict: true)` или `unique(fn($x) => $x->id)`. `values()` сбрасывает ключи после `filter()`, иначе массив получится разреженным.',
                'assemble_chunks' => ['collect($users)', '->', 'filter(fn($u) => $u->active)', '->', 'pluck(\'email\')', '->', 'unique()', '->', 'values()', '->', 'all()'],
                'difficulty' => 3,
                'topic' => 'php.assemble',
            ],
            [
                'category' => 'PHP',
                'question' => 'Собери try/catch для нескольких типов исключений.',
                'answer' => '**Multi-catch** (PHP **7.1+**): `TypeA|TypeB $e` — один блок для нескольких типов.

**Зачем нужно:**
- Убирает дублирование `catch (A $e) { log; } catch (B $e) { log; }`.
- Логически объединяет «технические сбои» (`Network`, `Timeout`, `ConnectException`) в один путь обработки.

**Подводные камни:**
- Порядок `catch` важен: **сначала специфичные**, потом общие — иначе `Throwable` поглотит всё.
- В multi-catch можно опустить имя переменной (PHP **8.0+**): `catch (A|B)` — если объект не нужен.
- Не путать с union types в сигнатурах — это разные фичи.',
                'assemble_chunks' => ['try {', '    $client->send($request);', '} catch (', 'NetworkException ', '| ', 'TimeoutException ', '$e) {', '    report($e);', '}'],
                'difficulty' => 3,
                'topic' => 'php.assemble',
            ],
        ];
    }
}
