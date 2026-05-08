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
                'answer' => 'UNION удаляет дубликаты, UNION ALL - оставляет.',
                'short_answer' => 'UNION',
                'difficulty' => 2,
                'topic' => 'database.type_in',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'SQL-конструкция для условного выражения, аналог if/then.',
                'answer' => 'CASE WHEN ... THEN ... ELSE ... END - стандартное портируемое выражение.',
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
