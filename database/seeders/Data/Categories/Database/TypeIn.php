<?php

namespace Database\Seeders\Data\Categories\Database;

class TypeIn
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Базы данных',
                'question' => 'SQL-команда, объединяющая результаты двух запросов без дубликатов.',
                'answer' => '**`UNION`** — объединяет результаты и **убирает дубликаты** (через сортировку/хеширование).

**Сравнение:**
- `UNION` — без дубликатов, **медленнее**;
- `UNION ALL` — с дубликатами, **значительно быстрее**.

**Требования к обоим:** одинаковое **количество колонок** и **совместимые типы** в каждой позиции.',
                'short_answer' => 'UNION',
                'difficulty' => 2,
                'topic' => 'database.type_in',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'SQL-конструкция для условного выражения, аналог if/then.',
                'answer' => '**`CASE WHEN ... THEN ... ELSE ... END`** — стандартное портируемое выражение SQL.

**Применяется в:** `SELECT`, `WHERE`, `ORDER BY`, `GROUP BY`, агрегатах.

**Пример с условным счётчиком:** `COUNT(CASE WHEN status = \'paid\' THEN 1 END)`.

В MySQL есть короткий `IF(cond, then, else)`, но он **не переносим**.',
                'short_answer' => 'CASE',
                'difficulty' => 2,
                'topic' => 'database.type_in',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Уровень изоляции, при котором допустимы non-repeatable reads и phantom reads.',
                'answer' => 'READ COMMITTED - компромиссный уровень по умолчанию во многих БД (Postgres, Oracle).',
                'short_answer' => 'READ COMMITTED',
                'difficulty' => 4,
                'topic' => 'database.type_in',
            ],
        ];
    }
}
