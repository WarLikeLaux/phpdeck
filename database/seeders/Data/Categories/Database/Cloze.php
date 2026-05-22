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
                'answer' => '**Шаблон top-N:** `GROUP BY` с `COUNT` + сортировка `DESC` + `LIMIT`.

**Зачем `LEFT JOIN`, а не `INNER`:** чтобы попали и **пользователи без заказов** (с `COUNT(o.id) = 0`). `INNER JOIN` отбросит их совсем.

**Про `LIMIT`:**
- `LIMIT N` — синтаксис **PostgreSQL**, **MySQL**, **SQLite**;
- в **стандарте SQL** (с SQL:2008) — `FETCH FIRST N ROWS ONLY` (поддерживают Oracle 12c+, SQL Server, DB2, Postgres);
- для **переносимого кода** — `FETCH FIRST`.',
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
                'answer' => '**`ROW_NUMBER()` + `PARTITION BY`** — оконная функция, которая внутри каждой группы (`PARTITION BY`) нумерует строки **1, 2, 3...** согласно `ORDER BY` окна.

**Как читать:**
- **`PARTITION BY user_id`** — «начинай нумерацию заново для каждого `user_id`»;
- **`ORDER BY created_at`** — внутри пользователя — по дате заказа.

**`ROW_NUMBER` vs `RANK` vs `DENSE_RANK`:**

| Функция | При равных значениях |
|---|---|
| `ROW_NUMBER()` | уникальные **1, 2, 3, 4** |
| `RANK()` | повторяет ранг с **пропуском**: **1, 2, 2, 4** |
| `DENSE_RANK()` | повторяет **без пропуска**: **1, 2, 2, 3** |

**Применение:** «топ-3 заказа каждого пользователя», «последняя оплата на клиента», «отбросить дубли по бизнес-ключу» (через `ROW_NUMBER` + `WHERE n = 1`).',
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
