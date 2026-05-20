<?php

namespace Database\Seeders\Data\Categories\Database;

class Assemble
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Базы данных',
                'question' => 'Собери SQL: 5 самых дорогих заказов с email клиента.',
                'answer' => 'Шаблон: **`JOIN` по внешнему ключу** + **`ORDER BY ... DESC`** + **`LIMIT N`**.

**Порядок частей запроса:**
1. `SELECT` — что взять;
2. `FROM` — главная таблица;
3. `JOIN ... ON` — присоединить связанную таблицу;
4. `ORDER BY` — сортировка по нужной колонке;
5. `LIMIT` — обрезать до N строк.

Здесь `INNER JOIN` уместен: нужны только те заказы, у которых **есть клиент** в `users`. Если важно показать и «осиротевшие» заказы — нужен `LEFT JOIN`.',
                'code_language' => 'sql',
                'assemble_chunks' => ['SELECT o.id, o.total, u.email', '
FROM orders o', '
JOIN users u ON u.id = o.user_id', '
ORDER BY o.total DESC', '
LIMIT 5', ';'],
                'difficulty' => 2,
                'topic' => 'database.assemble',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Собери UPDATE с подзапросом на максимум.',
                'answer' => 'Можно использовать коррелированный подзапрос или CTE для денормализованного поля.',
                'code_language' => 'sql',
                'assemble_chunks' => ['UPDATE users u', '
SET last_order_at = (SELECT MAX(created_at) FROM orders o WHERE o.user_id = u.id)', '
WHERE EXISTS (SELECT 1 FROM orders o WHERE o.user_id = u.id)', ';'],
                'difficulty' => 3,
                'topic' => 'database.assemble',
            ],
        ];
    }
}
