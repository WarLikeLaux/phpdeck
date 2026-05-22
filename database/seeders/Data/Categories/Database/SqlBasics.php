<?php

namespace Database\Seeders\Data\Categories\Database;

class SqlBasics
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Базы данных',
                'question' => 'Что такое SELECT и из чего состоит базовый запрос?',
                'answer' => '`SELECT` — команда **выборки данных** из таблицы.

Базовая структура:
1. `SELECT какие_столбцы`
2. `FROM из_какой_таблицы`
3. `WHERE по_какому_условию` — необязательно;
4. `ORDER BY по_чему_сортировать` — необязательно;
5. `LIMIT сколько_строк` `OFFSET сколько_пропустить` — необязательно.

Звёздочка `SELECT *` означает «все столбцы», но в продакшен-коде её обычно не используют — лучше **явно перечислять** нужные поля (быстрее и понятнее).',
                'code_example' => '-- Берём конкретные столбцы у активных юзеров,
-- сортируем по дате регистрации и берём 10 свежих
SELECT id, name, email
FROM users
WHERE created_at > \'2024-01-01\'
ORDER BY created_at DESC
LIMIT 10 OFFSET 20;

-- Самый простой SELECT
SELECT * FROM users;',
                'code_language' => 'sql',
                'difficulty' => 1,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Чем отличается WHERE от HAVING?',
                'answer' => '- `WHERE` фильтрует **отдельные строки** **до** группировки (`GROUP BY`).
- `HAVING` фильтрует **уже посчитанные группы** **после** группировки.

**Ключевая разница:**
- в `WHERE` **нельзя** использовать агрегаты (`COUNT`, `SUM`, `AVG`);
- в `HAVING` — **можно** (он работает поверх агрегированных данных).

**Порядок выполнения:** `FROM` → `WHERE` → `GROUP BY` → `HAVING` → `SELECT`.',
                'code_example' => 'SELECT user_id, COUNT(*) AS orders_count
FROM orders
WHERE status = \'completed\'         -- фильтр строк
GROUP BY user_id
HAVING COUNT(*) > 5;               -- фильтр групп',
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое GROUP BY?',
                'answer' => '`GROUP BY` **группирует строки** с одинаковыми значениями указанных столбцов в **одну** строку результата.

Используется вместе с **агрегатными функциями**:
- `COUNT(*)` — количество строк в группе;
- `SUM(col)` — сумма;
- `AVG(col)` — среднее;
- `MIN(col)` / `MAX(col)` — минимум / максимум.

**Правило:** все колонки в `SELECT`, которые **не обёрнуты в агрегат**, должны быть указаны в `GROUP BY`. В MySQL при включённом `ONLY_FULL_GROUP_BY` нарушение даст ошибку, в PostgreSQL — всегда ошибка.',
                'code_example' => 'SELECT category_id, COUNT(*) AS total, AVG(price) AS avg_price
FROM products
GROUP BY category_id;',
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое ORDER BY и как сортировать по нескольким полям?',
                'answer' => '`ORDER BY` **сортирует строки** в результате запроса.

- `ASC` — по возрастанию (по умолчанию, можно не писать);
- `DESC` — по убыванию.

**По нескольким полям** — через запятую: сначала по первому, при равенстве — по второму, и так далее (как алфавитный порядок: сперва по фамилии, при совпадении — по имени).

**Важно:** без `ORDER BY` порядок строк в выдаче БД **не гарантирует** — может меняться от запроса к запросу.',
                'code_example' => '-- По убыванию зарплаты, при равной зарплате — по возрастанию возраста,
-- затем по имени
SELECT name, age, salary
FROM employees
ORDER BY salary DESC, age ASC, name;

-- Свежие записи сверху — типичный шаблон
SELECT * FROM posts ORDER BY created_at DESC;',
                'code_language' => 'sql',
                'difficulty' => 1,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое LIMIT и OFFSET?',
                'answer' => '- `LIMIT N` — вернуть **не больше N строк**.
- `OFFSET M` — **пропустить** первые M строк.

Чаще всего используется для **постраничной выдачи** (пагинации).

**Важно:** без `ORDER BY` порядок строк **не гарантирован** — пагинация будет «прыгать». Всегда добавляй сортировку.

**Минус OFFSET-пагинации:** на больших значениях БД всё равно **сканирует и отбрасывает** все пропускаемые строки — на миллионных таблицах это медленно. Решение — **keyset (cursor) пагинация**: `WHERE id > last_id LIMIT N`.',
                'code_example' => '-- 3-я страница по 20 элементов
SELECT * FROM articles
ORDER BY published_at DESC
LIMIT 20 OFFSET 40;',
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое DISTINCT?',
                'answer' => '`DISTINCT` **убирает дубликаты** строк в результате запроса.

**Важно:** применяется к **всему набору колонок** в `SELECT`, а не к одной. `SELECT DISTINCT country, city` уберёт только полностью совпадающие пары.

**Минусы:**
- БД делает **сортировку или хеширование** — на больших данных это **медленно**;
- часто `DISTINCT` — это **симптом криво написанного запроса** (например, лишний `JOIN` плодит дубли) — стоит подумать, нельзя ли убрать причину дублей, а не «замазать» их.',
                'code_example' => 'SELECT DISTINCT country FROM users;

-- Дубликаты определяются по комбинации всех столбцов
SELECT DISTINCT country, city FROM users;',
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Какие есть виды JOIN и чем они отличаются?',
                'answer' => '- **`INNER JOIN`** — только **совпадающие** строки в обеих таблицах (без совпадения — строка отбрасывается).
- **`LEFT JOIN`** — **все из левой** + совпадающие справа (если нет совпадения — справа `NULL`).
- **`RIGHT JOIN`** — наоборот: все из правой + совпадения слева.
- **`FULL OUTER JOIN`** — **все из обеих** таблиц, несовпадения заполняются `NULL`.
- **`CROSS JOIN`** — **декартово произведение** (каждая строка слева с каждой справа), без условия `ON`.
- **`SELF JOIN`** — таблица соединяется **сама с собой** (например, для иерархий: сотрудник → его руководитель).

**Подсказка:** `LEFT JOIN` — самый частый при поиске «все А, и связанные Б, если есть».',
                'code_example' => '-- INNER: только пользователи с заказами
SELECT u.name, o.id
FROM users u
INNER JOIN orders o ON o.user_id = u.id;

-- LEFT: все пользователи, заказы если есть
SELECT u.name, o.id
FROM users u
LEFT JOIN orders o ON o.user_id = u.id;

-- FULL OUTER: все строки из обеих таблиц
SELECT u.name, o.id
FROM users u
FULL OUTER JOIN orders o ON o.user_id = u.id;

-- CROSS: каждый с каждым (опасно)
SELECT a.name, b.name FROM colors a CROSS JOIN sizes b;

-- SELF JOIN: иерархия сотрудников
SELECT e.name AS employee, m.name AS manager
FROM employees e
LEFT JOIN employees m ON e.manager_id = m.id;',
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Как найти строки слева, у которых НЕТ совпадений справа?',
                'answer' => '**Стандартный приём** — `LEFT JOIN` + `WHERE правая_колонка IS NULL`:
1. `LEFT JOIN` возвращает все строки слева, у несовпавших справа — `NULL`;
2. фильтр `IS NULL` оставляет только те, где совпадения **не нашлось**.

**Пример:** «все пользователи без заказов» — `LEFT JOIN orders ON o.user_id = u.id` + `WHERE o.id IS NULL`.

**Альтернатива:** `NOT EXISTS` (часто читаемее и работает корректно с `NULL`).

**Антипаттерн:** `NOT IN (SELECT ...)` — если подзапрос вернёт хоть один `NULL`, весь результат окажется пустым (трёхзначная логика).',
                'code_example' => '-- Найти пользователей БЕЗ заказов
SELECT u.id, u.name
FROM users u
LEFT JOIN orders o ON o.user_id = u.id
WHERE o.id IS NULL;',
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое INSERT, UPDATE, DELETE?',
                'answer' => 'Три команды **DML**, изменяющие данные:
- `INSERT` — **добавляет** новые строки в таблицу;
- `UPDATE` — **меняет** значения в уже существующих строках;
- `DELETE` — **удаляет** строки.

**Опасность:** `UPDATE` и `DELETE` **без `WHERE`** применятся ко **всей** таблице — так можно случайно стереть или переписать миллион записей.

**Безопасная привычка:** перед `UPDATE`/`DELETE` сначала запусти `SELECT` с тем же `WHERE` — посмотри, какие строки попадают, и только потом меняй или удаляй. Ещё надёжнее — оборачивать в транзакцию (`BEGIN` ... `COMMIT` / `ROLLBACK`).',
                'code_example' => '-- Добавить одну строку
INSERT INTO users (name, email) VALUES (\'Иван\', \'ivan@example.com\');

-- Добавить сразу несколько
INSERT INTO users (name, email) VALUES
    (\'Анна\', \'anna@example.com\'),
    (\'Пётр\', \'petr@example.com\');

-- Сначала проверь, потом меняй
SELECT * FROM users WHERE id = 1;                       -- проверка
UPDATE users SET email = \'new@example.com\' WHERE id = 1;

-- Удалить старые записи
DELETE FROM users WHERE created_at < \'2020-01-01\';

-- ОПАСНО: без WHERE удалит ВСЁ
-- DELETE FROM users;',
                'code_language' => 'sql',
                'difficulty' => 1,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Чем отличается UNION от UNION ALL?',
                'answer' => '- **`UNION`** — объединяет результаты двух `SELECT` и **убирает дубликаты**. БД делает сортировку/хеширование → **медленнее**.
- **`UNION ALL`** — объединяет **как есть**, дубликаты остаются → **значительно быстрее**.

**Правило:** бери `UNION ALL`, если уверен, что дубликатов нет или они не мешают.

**Требования к обоим:**
- одинаковое **количество колонок**;
- **совместимые типы** в каждой позиции.

Имена колонок результата берутся из **первого** `SELECT`.',
                'code_example' => '-- Без дубликатов (медленнее)
SELECT name FROM customers
UNION
SELECT name FROM suppliers;

-- С дубликатами (быстрее)
SELECT name FROM customers
UNION ALL
SELECT name FROM suppliers;',
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое подзапрос (subquery) и какие виды бывают?',
                'answer' => '**Подзапрос** — это `SELECT` внутри другого запроса.

**По месту:**
- в **`SELECT`** — *скалярный* подзапрос (одна строка / одна колонка, иначе ошибка);
- в **`FROM`** — *производная таблица* (inline view);
- в **`WHERE`** / **`HAVING`** — с `IN`, `EXISTS`, `=`, `ANY`, `ALL`.

**По связи с внешним запросом:**
- **некоррелированный** — выполняется **один раз**, результат подставляется во внешний запрос;
- **коррелированный** — ссылается на колонки внешнего запроса, логически считается **для каждой строки** (оптимизатор может развернуть в `JOIN`).

**Что выбирать:**
- по **читаемости** часто выигрывает **CTE** (`WITH ... AS`);
- по **производительности** — `JOIN` или `EXISTS` вместо `IN` с большим списком.

**Подводный камень:** скалярный подзапрос в `SELECT` удобен, но в OLTP может стать **N+1 на стороне БД**, если выбирается миллион строк.',
                'code_example' => '-- Скалярный подзапрос
SELECT name, (SELECT COUNT(*) FROM orders WHERE user_id = u.id) AS orders
FROM users u;

-- В WHERE
SELECT * FROM products
WHERE category_id IN (SELECT id FROM categories WHERE active = true);

-- В FROM (производная таблица)
SELECT t.user_id, t.total
FROM (SELECT user_id, SUM(amount) AS total FROM payments GROUP BY user_id) t
WHERE t.total > 1000;',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое CTE (WITH ... AS) и зачем он нужен?',
                'answer' => '**`CTE`** (Common Table Expression) — **именованный временный набор результатов**, который существует **только в рамках одного SQL-запроса**.

**Синтаксис:**
```sql
WITH name1 AS ( SELECT ... ),
     name2 AS ( SELECT ... FROM name1 ... )
SELECT * FROM name2;
```

**Зачем нужен:**
- **читабельность** — разбиваем сложный запрос на **логические шаги** (как переменные в коде);
- избавляет от **повторений** одного и того же подзапроса несколько раз;
- **рекурсия** через `WITH RECURSIVE` — иерархии (дерево категорий, оргструктура, граф связей);
- упрощает **отладку** — каждый CTE можно запустить отдельно.

**Важный нюанс материализации:**

| СУБД | По умолчанию |
|---|---|
| **PostgreSQL 12+** | CTE **inline** (как subquery) — оптимизатор может протолкнуть предикаты; `WITH ... AS MATERIALIZED` форсирует материализацию |
| **PostgreSQL до 12** | всегда **материализуется** — был optimization fence |
| **MySQL 8+** | CTE **не материализуется**, ведёт себя как subquery |

**Когда CTE лучше subquery / temp table:**
- запрос длинный → читаемость важнее;
- одна выборка используется **2+ раза**;
- нужна **рекурсия**.

**Когда нет:**
- одноразовый простой subquery (избыточно);
- старый PG (< 12) — CTE мешал оптимизатору проталкивать `WHERE`.',
                'code_example' => '-- Обычный CTE
WITH active_users AS (
    SELECT id, name FROM users WHERE active = true
),
recent_orders AS (
    SELECT user_id, COUNT(*) AS cnt
    FROM orders
    WHERE created_at > NOW() - INTERVAL \'30 days\'
    GROUP BY user_id
)
SELECT u.name, COALESCE(o.cnt, 0) AS orders
FROM active_users u
LEFT JOIN recent_orders o ON o.user_id = u.id;

-- Рекурсивный CTE: дерево категорий
WITH RECURSIVE tree AS (
    SELECT id, name, parent_id FROM categories WHERE parent_id IS NULL
    UNION ALL
    SELECT c.id, c.name, c.parent_id
    FROM categories c
    JOIN tree t ON c.parent_id = t.id
)
SELECT * FROM tree;',
                'code_language' => 'sql',
                'difficulty' => 4,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Какие есть агрегатные функции?',
                'answer' => 'Считают одно значение по группе строк (работают с `GROUP BY` или по всей таблице).

**Базовые:**
- `COUNT(*)` — количество **строк** в группе;
- `COUNT(col)` — количество **не-NULL** значений в колонке;
- `COUNT(DISTINCT col)` — количество **уникальных** значений;
- `SUM(col)` — сумма;
- `AVG(col)` — среднее;
- `MIN(col)` / `MAX(col)` — минимум / максимум.

**Сборка значений в одно (зависит от СУБД):**
- `STRING_AGG(col, \', \')` — PostgreSQL;
- `GROUP_CONCAT(col)` — MySQL;
- `ARRAY_AGG(col)` — PostgreSQL, собрать в массив.

**Важно:** все агрегаты **игнорируют `NULL`** (кроме `COUNT(*)`).',
                'code_example' => 'SELECT
    COUNT(*) AS total_rows,
    COUNT(email) AS users_with_email,
    COUNT(DISTINCT country) AS unique_countries,
    SUM(salary) AS sum_salary,
    AVG(salary) AS avg_salary,
    MIN(created_at) AS first,
    MAX(created_at) AS last,
    STRING_AGG(name, \', \') AS all_names
FROM users;',
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое NULL и трехзначная логика?',
                'answer' => '**`NULL`** — маркер «значение **неизвестно/отсутствует**», **не** ноль и **не** пустая строка.

**Любая арифметика и сравнение с `NULL` дают `NULL`:**
- `NULL = NULL` → `NULL` (а **не** `TRUE`!);
- `NULL + 5` → `NULL`;
- `NULL <> 1` → `NULL`.

**Трёхзначная логика:** `TRUE`, `FALSE`, `UNKNOWN`. `WHERE` / `JOIN ON` пропускают **только строки, где условие явно `TRUE`** — все `UNKNOWN` отбрасываются.

**Проверка на `NULL`** — только специальные операторы: `IS NULL` / `IS NOT NULL`.

**Подводные камни:**
- `NOT IN` с подзапросом, который вернул `NULL`, **не находит вообще ничего** (`TRUE` становится `UNKNOWN`) — **`NOT EXISTS` безопаснее**;
- `COUNT(*)` считает **все строки**, `COUNT(col)` — только **не-`NULL`** значения;
- `UNIQUE`-индекс по умолчанию допускает **несколько `NULL`** (стандарт SQL).',
                'code_example' => '-- Это НЕ найдёт строки с NULL
SELECT * FROM users WHERE deleted_at = NULL;  -- неверно

-- Правильно
SELECT * FROM users WHERE deleted_at IS NULL;
SELECT * FROM users WHERE deleted_at IS NOT NULL;

-- NULL в выражениях
SELECT 1 + NULL;       -- NULL
SELECT NULL = NULL;    -- NULL (UNKNOWN)',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что делают COALESCE, NULLIF, NVL?',
                'answer' => '- **`COALESCE(a, b, c, ...)`** — возвращает **первое не-NULL** значение из списка. Стандарт SQL, работает везде.
- **`NULLIF(a, b)`** — возвращает `NULL`, если `a = b`, иначе `a`. Самый частый кейс — **избегать деления на ноль** (`a / NULLIF(b, 0)`).
- **`NVL(a, b)`** — аналог `COALESCE` на два аргумента в **Oracle**. В **PostgreSQL** и **MySQL** его нет — используй `COALESCE`.

**Типичные применения:**
- замена `NULL` на дефолт в выводе (`COALESCE(nickname, name, \'Аноним\')`);
- безопасное деление;
- условный `UPDATE`, где новое значение приходит опционально.',
                'code_example' => '-- Замена NULL на дефолт
SELECT COALESCE(nickname, name, \'Аноним\') FROM users;

-- Избегаем деления на ноль
SELECT total / NULLIF(count, 0) AS average FROM stats;

-- COALESCE в UPDATE
UPDATE products SET price = COALESCE(new_price, price);',
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое CASE WHEN?',
                'answer' => '`CASE WHEN` — **условное выражение** SQL, аналог `if/else` в коде. Стандарт SQL, работает везде.

**Две формы:**
- **searched** (универсальная): `CASE WHEN условие THEN значение ... ELSE ... END`;
- **simple**: `CASE column WHEN value THEN ... END` (сравнение только на равенство).

**Где можно применять:**
- в `SELECT` — категории и производные поля;
- в `ORDER BY` — кастомная сортировка;
- в `WHERE` — сложные условия;
- в **агрегатах** для условного подсчёта: `COUNT(CASE WHEN status = \'paid\' THEN 1 END)`.

`ELSE` опционален — без него на «не подошло ни одно условие» вернётся `NULL`.',
                'code_example' => 'SELECT
    name,
    salary,
    CASE
        WHEN salary < 50000 THEN \'low\'
        WHEN salary < 100000 THEN \'mid\'
        ELSE \'high\'
    END AS salary_band
FROM employees;

-- CASE для условной агрегации (портируемо)
SELECT
    COUNT(CASE WHEN status = \'completed\' THEN 1 END) AS done,
    COUNT(CASE WHEN status = \'pending\' THEN 1 END) AS pending
FROM orders;

-- FILTER (PostgreSQL/стандарт SQL, нет в MySQL)
SELECT COUNT(*) FILTER (WHERE status = \'completed\') AS done FROM orders;',
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'В чём разница между IN и EXISTS?',
                'answer' => '**`IN`** — сравнивает значение со **списком** значений или результатом подзапроса по колонке.

**`EXISTS`** — проверяет, **вернул ли подзапрос хоть одну строку**, возвращает `TRUE`/`FALSE`. Сам контент строки не важен — поэтому пишут `SELECT 1 FROM ...`.

**Производительность:** исторически считалось, что `EXISTS` быстрее. Современные оптимизаторы (PostgreSQL 9+, MySQL 8+, SQL Server) транслируют и `IN(subquery)`, и `EXISTS(subquery)` в один и тот же **semi-join** — план чаще всего идентичный.

**Принципиальная разница — в семантике `NULL`:**
- `NOT IN` с `NULL` внутри подзапроса возвращает `UNKNOWN` для всех строк и **«не находит» ничего**;
- `NOT EXISTS` работает **корректно**.

**Правило:**
- в **позитиве** `IN` / `EXISTS` взаимозаменяемы;
- в **негативе** — всегда `NOT EXISTS`.',
                'code_example' => '-- IN
SELECT * FROM users WHERE id IN (SELECT user_id FROM orders);

-- EXISTS - часто эффективнее
SELECT * FROM users u
WHERE EXISTS (SELECT 1 FROM orders o WHERE o.user_id = u.id);

-- NOT EXISTS - безопаснее NOT IN при NULL
SELECT * FROM users u
WHERE NOT EXISTS (SELECT 1 FROM orders o WHERE o.user_id = u.id);',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое BETWEEN, LIKE, ILIKE?',
                'answer' => '- **`BETWEEN a AND b`** — значение в диапазоне `[a, b]` **включительно** (равносильно `col >= a AND col <= b`). Работает для чисел, дат, строк.

**Поиск по шаблону:**
- **`LIKE`** — `%` означает «любые символы» (включая пустоту), `_` — ровно один символ.
- **`ILIKE`** — то же, но **без учёта регистра**. Только в PostgreSQL.

**Регистр в MySQL:** обычный `LIKE` чувствителен к регистру или нет — **зависит от collation колонки** (`*_ci` — нечувствителен, `*_bin` / `*_cs` — чувствителен).

**Для индекса:** `LIKE \'abc%\'` (префикс) использует B-tree, а `LIKE \'%abc%\'` — нет, идёт полный скан.',
                'code_example' => 'SELECT * FROM users WHERE age BETWEEN 18 AND 65;

SELECT * FROM products WHERE name LIKE \'iPhone%\';     -- начинается с
SELECT * FROM products WHERE name LIKE \'%pro%\';       -- содержит
SELECT * FROM products WHERE name LIKE \'iPhone_\';     -- ровно одна буква в конце

-- PostgreSQL: без учёта регистра
SELECT * FROM products WHERE name ILIKE \'%pro%\';',
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Защищают ли prepared statements от SQL-инъекции в ORDER BY? Можно ли передать имя колонки через параметр?',
                'answer' => '**НЕТ.** Prepared statements / PDO bindings / Eloquent параметризация защищают **только значения**.

**Что можно параметризовать (`?`/`:name`):**
- значения в `WHERE col = ?`;
- `VALUES (?, ?, ?)`;
- `SET col = ?`;
- `LIMIT ?` / `OFFSET ?` (это значения).

**Что НЕЛЬЗЯ параметризовать:**

| Категория | Примеры |
|---|---|
| Имена таблиц | `FROM ?` |
| Имена колонок | `SELECT ?, col2 FROM t` |
| Keywords | `ORDER BY ?`, `ASC`/`DESC` |
| Структурные выражения | `GROUP BY ?`, тип JOIN |

**Почему так:** парсер БД **разбирает SQL до подстановки параметров** — имена идентификаторов и keywords **должны быть в строке** на момент prepare. Это **архитектура** подготовленных запросов, **не баг**.

**Классическая дыра:**

```php
$sort = $_GET[\'sort\']; // "id; DROP TABLE users--"
DB::select("SELECT * FROM users ORDER BY $sort"); // ⚠️ catastrophic
```

**Решение — whitelist на стороне приложения:**

| Что фильтруем | Как |
|---|---|
| Имя колонки сортировки | `in_array($sort, $allowed, true)` или `match`/Form Request |
| Направление | `$dir = $request->input(\'dir\') === \'desc\' ? \'desc\' : \'asc\';` |
| Имя таблицы | конструировать в коде, **никогда** из input |
| Имена в `SELECT` | белый список или ORM |

**В Laravel** — лучший паттерн через **Form Request** с правилом `in:...`:

```php
public function rules(): array {
    return [
        \'sort\' => \'in:id,name,email,created_at\',
        \'dir\'  => \'in:asc,desc\',
    ];
}
```

**Бонус — `LIMIT`/`OFFSET`:** **это значения**, их можно биндить. В PDO для надёжности с `PDO::PARAM_INT`, иначе некоторые версии MySQL квотируют число и `LIMIT \'10\'` ломается:

```php
$stmt = $pdo->prepare(\'SELECT * FROM users LIMIT :limit OFFSET :offset\');
$stmt->bindValue(\':limit\',  20,  PDO::PARAM_INT);
$stmt->bindValue(\':offset\', 100, PDO::PARAM_INT);
```',
                'code_example' => '<?php
// ❌ КРИТИЧНАЯ дыра - имя колонки от пользователя
$sortField = $_GET[\'sort\']; // может быть "id; DROP TABLE users--"
$users = DB::select("SELECT * FROM users ORDER BY $sortField"); // ⚠️ инъекция

// ❌ Не защищает - прямая конкатенация direction
$direction = $_GET[\'dir\']; // "ASC; DELETE FROM users--"
DB::table("users")->orderByRaw("created_at $direction"); // ⚠️ инъекция

// ✅ Whitelist разрешённых колонок
$allowedSorts = [\'id\', \'created_at\', \'name\', \'email\'];
$sortField = in_array($_GET[\'sort\'] ?? \'id\', $allowedSorts, true)
    ? $_GET[\'sort\']
    : \'id\';

$direction = strtolower($_GET[\'dir\'] ?? \'asc\') === \'desc\' ? \'desc\' : \'asc\';

$users = DB::table(\'users\')->orderBy($sortField, $direction)->get();

// ✅ Через match (PHP 8.0+) или Form Request
$sortField = match ($request->input(\'sort\')) {
    \'name\', \'email\', \'created_at\' => $request->input(\'sort\'),
    default => \'id\',
};

// ✅ В Form Request validation
public function rules(): array {
    return [
        \'sort\' => \'in:id,name,email,created_at\',
        \'dir\'  => \'in:asc,desc\',
    ];
}

// Запомнить: через bindings можно ТОЛЬКО значения
// Можно: WHERE id = ?, VALUES (?, ?), SET col = ?
// Нельзя: ORDER BY ?, FROM ?, SELECT ? FROM users, GROUP BY ?

// LIMIT/OFFSET - можно (значения)
DB::select(\'SELECT * FROM users LIMIT ? OFFSET ?\', [20, 100]); // OK',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'В чём разница между неявным и явным JOIN в SQL?',
                'answer' => '**Неявный JOIN** — старый стиль SQL-89: таблицы перечисляются через **запятую** в `FROM`, условие связи **смешано с фильтрами** в `WHERE`.

```
FROM users u, orders o WHERE u.id = o.user_id AND o.total > 100
```

**Явный JOIN** — современный стиль (SQL-92+): таблицы соединяются ключевым словом `JOIN ... ON`, условие связи **отделено** от фильтров.

```
FROM users u INNER JOIN orders o ON o.user_id = u.id WHERE o.total > 100
```

**Почему явный лучше:**
- **читается понятнее** — сразу видно, что с чем связано;
- **страхует от случайного `CROSS JOIN`**: если забыть условие в `WHERE`, неявный JOIN даст декартово произведение;
- поддерживает **`LEFT/RIGHT/FULL OUTER JOIN`** — неявный синтаксис их вообще не умеет.

**Вывод:** в новом коде — **только явный** `JOIN`.',
                'code_example' => '-- Неявный JOIN (старый стиль)
SELECT u.name, o.id
FROM users u, orders o
WHERE u.id = o.user_id;

-- Явный JOIN — современный и читаемый
SELECT u.name, o.id
FROM users u
INNER JOIN orders o ON o.user_id = u.id;',
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Чем отличается условие в JOIN ... ON от условия в WHERE для LEFT JOIN?',
                'answer' => 'Для **`INNER JOIN`** это **семантически эквивалентно** — оптимизатор свободно перемещает предикаты между `ON` и `WHERE`.

Для **`LEFT JOIN`** расположение **принципиально**:

| Где условие | Когда применяется | Что делает с несовпавшими строками |
|---|---|---|
| `ON` | **во время** соединения | левая остаётся, справа → `NULL` |
| `WHERE` | **после** соединения | проверка правой (кроме `IS NULL`) **отбрасывает** строку → `LEFT` превращается в `INNER` |

**Классическая ловушка** — «все пользователи и их paid-заказы»: фильтр `o.status = \'paid\'` в `WHERE` убирает пользователей **без paid-заказов** целиком.

**Правило:**
- **условия на правую** таблицу — в `ON`;
- **условия на левую** таблицу — в `WHERE`;
- **«без совпадения»** — `WHERE o.id IS NULL` (anti-join).',
                'code_example' => '-- Цель: все пользователи + их paid-заказы (если есть)

-- НЕВЕРНО: WHERE превращает LEFT в INNER, теряем пользователей без paid-заказов
SELECT u.id, o.id
FROM users u
LEFT JOIN orders o ON o.user_id = u.id
WHERE o.status = \'paid\';

-- ВЕРНО: условие на правую таблицу в ON
SELECT u.id, o.id
FROM users u
LEFT JOIN orders o ON o.user_id = u.id AND o.status = \'paid\';

-- В WHERE можно фильтровать левую таблицу или искать "без совпадения"
SELECT u.id FROM users u
LEFT JOIN orders o ON o.user_id = u.id
WHERE o.id IS NULL; -- пользователи без заказов',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'В чём разница между LIKE и REGEXP в MySQL?',
                'answer' => '**`LIKE`** — всего **два метасимвола**:
- `%` — любая последовательность символов (включая пустую);
- `_` — ровно один символ.

**`REGEXP`** (синоним `RLIKE`) — полноценные **регулярные выражения**:
- якоря `^` `$`;
- классы `[a-z]`;
- альтернативы `|`;
- квантификаторы `+ * {n}`.

**Что с индексами:**

| Запрос | Использует `B-tree`? |
|---|---|
| `LIKE \'abc%\'` | **да** (префикс) |
| `LIKE \'%abc%\'` | **нет** (full scan) |
| любой `REGEXP` | **нет** (full scan) |

**Вывод:** `REGEXP` уместен для **разовых отчётов и админ-скриптов**. Для частых поисков нужны:
- **`FULLTEXT`-индекс** (MySQL);
- **n-gram / trigram** индекс (PostgreSQL `pg_trgm`);
- отдельный поисковой движок — **Elasticsearch**, **Meilisearch**;
- предвычисленные нормализованные колонки.',
                'code_example' => '-- LIKE: индексируется только префиксный шаблон
SELECT * FROM products WHERE name LIKE \'iPhone%\';   -- может использовать индекс
SELECT * FROM products WHERE name LIKE \'%pro%\';     -- full scan

-- REGEXP: всегда full scan, но мощный синтаксис
SELECT * FROM products WHERE name REGEXP \'^iPhone (12|13|14) Pro$\';
SELECT * FROM users WHERE email REGEXP \'[0-9]+@\';

-- Для быстрых полнотекстовых поисков в MySQL — FULLTEXT
ALTER TABLE products ADD FULLTEXT(name);
SELECT * FROM products WHERE MATCH(name) AGAINST(\'iphone pro\');',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Как сделать условное вычисление прямо в SELECT через CASE WHEN?',
                'answer' => 'Конструкция `CASE WHEN условие THEN значение ELSE другое END` формирует **категории, флаги, производные поля** прямо в `SELECT` — без процедур и кода приложения.

**Типичные применения:**
- **бакетирование значений**: `CASE WHEN amount < 100 THEN \'small\' WHEN amount < 1000 THEN \'mid\' ELSE \'large\' END`;
- **условный счётчик/сумма в одной строке**: `SUM(CASE WHEN status = \'paid\' THEN 1 ELSE 0 END) AS paid_count`;
- **кастомная сортировка**: `ORDER BY CASE WHEN status = \'urgent\' THEN 0 ELSE 1 END`.

**Альтернатива в MySQL** — `IF(condition, then, else)`, но он **не переносим**. `CASE` — стандарт SQL и работает везде.

`COALESCE` и `NULLIF` — частные случаи той же идеи.',
                'code_example' => '-- Категоризация прямо в SELECT
SELECT id, amount,
       CASE WHEN amount < 100 THEN \'small\'
            WHEN amount < 1000 THEN \'mid\'
            ELSE \'large\' END AS bucket
FROM orders;

-- Условный счётчик в одной строке
SELECT
    SUM(CASE WHEN status = \'paid\'    THEN 1 ELSE 0 END) AS paid,
    SUM(CASE WHEN status = \'pending\' THEN 1 ELSE 0 END) AS pending
FROM orders;',
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'В чём разница между подзапросами с ANY и ALL?',
                'answer' => '**`ANY`** (синоним `SOME`) — истинно, если условие верно **хотя бы для одного** значения из подзапроса.
- `x > ANY(SELECT ...)` равносильно `x > MIN(...)`.

**`ALL`** — условие должно выполняться **для всех** возвращённых значений.
- `x > ALL(SELECT ...)` равносильно `x > MAX(...)`.

**Подводный камень — пустой подзапрос:**

| Конструкция | На пустом подзапросе |
|---|---|
| `ANY` | всегда `FALSE` (нет ни одного, для которого верно) |
| `ALL` | всегда `TRUE` («для всех нуля значений верно что угодно») |

**Ещё:** `NULL` внутри подзапроса даёт `UNKNOWN` и ломает результат.

**На практике** эти конструкции встречаются **редко** — почти всегда переписывают через `MIN`/`MAX` в скалярном подзапросе или через `EXISTS`: план получается такой же, а **читается понятнее**.',
                'code_example' => '-- Зарплата выше любой (хотя бы одной) в отделе 5
SELECT name FROM employees
WHERE salary > ANY (SELECT salary FROM employees WHERE dept_id = 5);
-- Эквивалент через MIN — обычно так и пишут
SELECT name FROM employees
WHERE salary > (SELECT MIN(salary) FROM employees WHERE dept_id = 5);

-- Зарплата выше ВСЕХ в отделе 5 (то есть выше максимума)
SELECT name FROM employees
WHERE salary > ALL (SELECT salary FROM employees WHERE dept_id = 5);
-- Эквивалент через MAX
SELECT name FROM employees
WHERE salary > (SELECT MAX(salary) FROM employees WHERE dept_id = 5);',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое оконные функции (window functions) в SQL и чем они отличаются от GROUP BY?',
                'answer' => '**Оконная функция** считает агрегат или ранжирование **над «окном» строк**, **НЕ схлопывая** их в одну, как `GROUP BY` — каждая исходная строка остаётся, рядом появляется колонка с вычислением.

**Синтаксис:**
```sql
func() OVER (
    PARTITION BY col1, col2   -- на какие группы разбить
    ORDER BY     col3          -- порядок внутри партиции
    ROWS BETWEEN N PRECEDING AND M FOLLOWING   -- frame
)
```

**Три категории функций:**

| Категория | Функции | Применение |
|---|---|---|
| **Агрегатные как окно** | `SUM`, `AVG`, `COUNT`, `MIN`, `MAX` `OVER (...)` | running totals, скользящее среднее, доля от общего |
| **Ранжирование** | `ROW_NUMBER`, `RANK`, `DENSE_RANK`, `NTILE(n)`, `PERCENT_RANK` | топ-N по группе, квантили |
| **Навигация** | `LAG(col, n)`, `LEAD(col, n)`, `FIRST_VALUE`, `LAST_VALUE`, `NTH_VALUE` | дельты, сравнение со «вчера», предыдущий/следующий |

**Разница `ROW_NUMBER` vs `RANK` vs `DENSE_RANK`:**

| Значение | `ROW_NUMBER` | `RANK` | `DENSE_RANK` |
|---|---|---|---|
| 100 | 1 | 1 | 1 |
| 100 | 2 | **1** | **1** |
| 90 | 3 | **3** (пропуск) | **2** (без пропуска) |
| 80 | 4 | 4 | 3 |

**`GROUP BY` vs `Window`:**

| | `GROUP BY` | Window function |
|---|---|---|
| Что с исходными строками | **схлопывает** в одну на группу | **сохраняет все** |
| Доступ к деталям | нет | да |
| Можно ли смешивать с обычными колонками | только агрегаты + GROUP BY ключ | **любые колонки** + window |

**Типовые задачи:**
- **топ-N по группе** — `ROW_NUMBER() OVER (PARTITION BY user_id ORDER BY created_at DESC)` + `WHERE rn <= 3`;
- **running balance** — `SUM(amount) OVER (ORDER BY created_at)`;
- **скользящее среднее 7 дней** — `AVG(value) OVER (ORDER BY day ROWS BETWEEN 6 PRECEDING AND CURRENT ROW)`;
- **дельта с предыдущей строкой** — `amount - LAG(amount) OVER (ORDER BY created_at)`.

**Поддержка:** `PostgreSQL` (давно), **MySQL 8.0+**, **MariaDB 10.2+**, **SQLite 3.25+**.',
                'code_example' => '-- топ-3 заказа на каждого юзера + сумма всех его заказов
SELECT user_id, order_id, amount,
       SUM(amount) OVER (PARTITION BY user_id) AS user_total,
       ROW_NUMBER() OVER (PARTITION BY user_id ORDER BY amount DESC) AS rn,
       LAG(amount) OVER (PARTITION BY user_id ORDER BY created_at) AS prev_amount
FROM orders
QUALIFY rn <= 3; -- или WHERE в подзапросе',
                'code_language' => 'sql',
                'difficulty' => 4,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое UPSERT и как он пишется в MySQL и PostgreSQL?',
                'answer' => '**UPSERT** — «`INSERT`, а если строка с таким ключом уже есть — `UPDATE`», выполняемый **одной атомарной командой**.

**Зачем:** не делать вручную `SELECT` → `INSERT`/`UPDATE` — между двумя запросами возможна **гонка** и появится дубль или ошибка уникальности.

**Синтаксис:**

| СУБД | Команда |
|---|---|
| **MySQL** | `INSERT ... ON DUPLICATE KEY UPDATE` — срабатывает при конфликте по `PRIMARY KEY` или любому `UNIQUE`-индексу; в `SET`-части доступна `VALUES(col)` или алиас `VALUES AS new` |
| **PostgreSQL** | `INSERT ... ON CONFLICT (col) DO UPDATE SET ...` — стандарт SQL:2003; через псевдотаблицу `EXCLUDED` достаём вставляемые значения |
| **PostgreSQL** | `INSERT ... ON CONFLICT DO NOTHING` — тихо пропустить дубль |

**Подводный камень PG:** конфликт ловится **только по указанному столбцу/индексу** — нужно **явно** назвать колонку с `UNIQUE`.',
                'code_example' => '-- MySQL
INSERT INTO counters (key, value) VALUES (\'pageviews\', 1)
ON DUPLICATE KEY UPDATE value = value + 1;

-- PostgreSQL
INSERT INTO counters (key, value) VALUES (\'pageviews\', 1)
ON CONFLICT (key) DO UPDATE SET value = counters.value + 1;

-- Игнорировать дубли
INSERT INTO users (email, name) VALUES (\'a@b.c\', \'Anna\')
ON CONFLICT (email) DO NOTHING;',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Как создать таблицу через SQL?',
                'answer' => 'Команда `CREATE TABLE имя (...)` создаёт новую таблицу. Внутри скобок — список **колонок** с типами и **ограничениями**.

**Что можно указать у колонки:**
- **тип** — `BIGINT`, `VARCHAR(255)`, `DECIMAL(10, 2)`, `TIMESTAMP`, `JSON` и т. д.;
- **`NOT NULL`** — обязательное значение;
- **`DEFAULT`** — значение по умолчанию;
- **`UNIQUE`** — без повторений;
- **`PRIMARY KEY`** — первичный ключ;
- **`CHECK (...)`** — произвольное условие на значение;
- **`REFERENCES`** / **`FOREIGN KEY`** — связь с другой таблицей.

**Совет:** для денег — `DECIMAL`, не `FLOAT`. Для `id` — `BIGSERIAL` (PG) / `BIGINT AUTO_INCREMENT` (MySQL).',
                'code_example' => 'CREATE TABLE orders (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id),
    total DECIMAL(10,2) NOT NULL DEFAULT 0,
    status VARCHAR(20) NOT NULL CHECK (status IN (\'new\',\'paid\',\'shipped\')),
    created_at TIMESTAMP DEFAULT NOW()
);',
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что делает ALTER TABLE простыми словами?',
                'answer' => '`ALTER TABLE` **изменяет уже существующую таблицу** — структуру, не данные.

**Что умеет:**
- `ADD COLUMN` / `DROP COLUMN` — добавить или удалить колонку;
- `RENAME COLUMN ... TO ...` — переименовать;
- `ALTER COLUMN ... TYPE ...` — поменять тип;
- `ADD CONSTRAINT` / `DROP CONSTRAINT` — добавить или убрать `UNIQUE`, `FOREIGN KEY`, `CHECK`;
- `ADD INDEX` / `DROP INDEX` — управлять индексами.

**Опасности на больших таблицах:**
- лёгкие операции (добавить `NULL`-колонку) на современных движках быстрые;
- тяжёлые (`ADD NOT NULL` без `DEFAULT`, смена типа `PRIMARY KEY`) могут **переписать всю таблицу** и держать **длинный lock** → даунтайм.

**На проде** используют онлайн-инструменты (`pt-online-schema-change`, `gh-ost`). В Laravel `ALTER` пишут как **отдельные миграции** — удобно откатить.',
                'code_example' => '-- Добавить и удалить колонку
ALTER TABLE users ADD COLUMN phone VARCHAR(20);
ALTER TABLE users DROP COLUMN phone;

-- Переименовать колонку и добавить уникальный ключ
ALTER TABLE users RENAME COLUMN name TO full_name;
ALTER TABLE users ADD CONSTRAINT users_email_unique UNIQUE (email);',
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое DROP TABLE и в чём опасность?',
                'answer' => '`DROP TABLE` удаляет таблицу **целиком** — и все строки, и саму **структуру** (колонки, индексы, ограничения).

**Опасность:** никакой «корзины» в БД нет — откатить можно только из **бэкапа**. В проде такую команду запускают сознательно и обычно только в миграциях.

- `DROP TABLE IF EXISTS users;` — не упадёт с ошибкой, если таблицы уже нет.

**Если нужно стереть только строки**, а структуру оставить:
- `TRUNCATE TABLE` — быстрая полная очистка;
- `DELETE FROM ... WHERE ...` — когда удаляем только часть строк.',
                'code_example' => '-- Удалить таблицу целиком: и структуру, и данные
DROP TABLE users;

-- Не упадёт с ошибкой, даже если таблицы нет
DROP TABLE IF EXISTS users;

-- Только очистить строки, структура остаётся
TRUNCATE TABLE users;

-- Удалить только нужные строки
DELETE FROM users WHERE created_at < \'2020-01-01\';',
                'code_language' => 'sql',
                'difficulty' => 1,
                'topic' => 'database.sql_basics',
            ],
        ];
    }
}
