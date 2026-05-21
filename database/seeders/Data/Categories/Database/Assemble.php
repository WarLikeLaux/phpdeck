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
                'answer' => 'Задача: для каждого пользователя записать **последнюю дату его заказа** в денормализованное поле `users.last_order_at`.

**Паттерн — `UPDATE` + коррелированный подзапрос:**
1. **`UPDATE users u`** — целевая таблица с алиасом для ссылки во вложенном запросе;
2. **`SET last_order_at = (SELECT MAX(created_at) FROM orders o WHERE o.user_id = u.id)`** — для каждой строки `users` считаем максимум по её заказам;
3. **`WHERE EXISTS (...)`** — обновляем **только тех**, у кого есть хоть один заказ. Без этого пользователи без заказов получат `NULL`.

**Альтернатива через `JOIN`** (быстрее на больших таблицах в PG):
- `UPDATE users u SET last_order_at = t.max_at FROM (SELECT user_id, MAX(created_at) AS max_at FROM orders GROUP BY user_id) t WHERE t.user_id = u.id;`

**Подводный камень:** в **MySQL** нельзя одной командой делать `UPDATE` таблицы и `SELECT` из той же — пришлось бы оборачивать в `JOIN`.',
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
