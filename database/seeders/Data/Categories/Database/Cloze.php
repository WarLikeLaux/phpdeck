<?php

namespace Database\Seeders\Data\Categories\Database;

class Cloze
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Базы данных',
                'question' => 'Заполни SQL для топ-10 пользователей по количеству заказов.',
                'answer' => 'GROUP BY с COUNT и сортировкой DESC + LIMIT - распространённая конструкция top-N. LIMIT/OFFSET - синтаксис PostgreSQL/MySQL/SQLite, в стандарте SQL (с SQL:2008) для top-N используется FETCH FIRST N ROWS ONLY (его поддерживают Oracle 12c+, SQL Server, DB2, Postgres). Для переносимого кода — FETCH FIRST.',
                'code_language' => 'sql',
                'cloze_text' => 'SELECT u.id, COUNT(o.id) AS orders
FROM users u
{{LEFT JOIN}} orders o ON o.user_id = u.id
{{GROUP BY}} u.id
ORDER BY orders {{DESC}}
LIMIT 10;',
                'difficulty' => 2,
                'topic' => 'database.cloze',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Заполни оконную функцию для нумерации заказов внутри пользователя по дате.',
                'answer' => 'ROW_NUMBER() с PARTITION BY раскладывает строки по группам и нумерует внутри каждой согласно ORDER BY.',
                'code_language' => 'sql',
                'cloze_text' => 'SELECT id, user_id,
       {{ROW_NUMBER}}() OVER ({{PARTITION BY}} user_id ORDER BY created_at) AS n
FROM orders;',
                'difficulty' => 4,
                'topic' => 'database.cloze',
            ],
        ];
    }
}
