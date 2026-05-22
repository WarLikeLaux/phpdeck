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
                'answer' => '**`READ COMMITTED`** — компромиссный уровень изоляции **по умолчанию** в **PostgreSQL** и **Oracle**.

**Что разрешено / запрещено:**

| Аномалия | На `READ COMMITTED` |
|---|---|
| **Dirty read** | **запрещён** — никогда не видим незакоммиченные данные |
| **Non-repeatable read** | **допустим** — один `SELECT` может вернуть строку, второй (после `COMMIT` другой транзакции) — её новую версию |
| **Phantom read** | **допустим** — новые подходящие строки могут появиться между двумя `SELECT` |

**Как работает в PG:** каждый `SELECT` берёт **свой свежий snapshot** (а не один на всю транзакцию).

**Сравнение с другими уровнями:**

| Уровень | Dirty | Non-repeatable | Phantom |
|---|---|---|---|
| `READ UNCOMMITTED` | да | да | да |
| **`READ COMMITTED`** | **нет** | **да** | **да** |
| `REPEATABLE READ` | нет | нет | в стандарте — да; в InnoDB/PG — **нет** |
| `SERIALIZABLE` | нет | нет | нет |

**В MySQL InnoDB** по умолчанию **`REPEATABLE READ`** (а не `READ COMMITTED`).',
                'short_answer' => 'READ COMMITTED',
                'difficulty' => 4,
                'topic' => 'database.type_in',
            ],
        ];
    }
}
