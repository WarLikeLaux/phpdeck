<?php

namespace Database\Seeders\Data\Categories\Database;

class SqlBasics
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Базы данных',
                'question' => 'Что такое SELECT и из чего состоит базовый запрос?',
                'answer' => 'SELECT - это команда выборки данных. Базовая структура: SELECT столбцы FROM таблица WHERE условие ORDER BY столбец LIMIT N OFFSET M. Порядок логического выполнения (важно для понимания, что в WHERE нельзя использовать алиасы из SELECT): FROM/JOIN -> WHERE -> GROUP BY -> HAVING -> SELECT (включая оконные функции и DISTINCT) -> ORDER BY -> LIMIT/OFFSET.',
                'code_example' => <<<'SQL'
SELECT id, name, email
FROM users
WHERE created_at > '2024-01-01'
ORDER BY created_at DESC
LIMIT 10 OFFSET 20;
SQL,
                'code_language' => 'sql',
                'difficulty' => 1,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Чем отличается WHERE от HAVING?',
                'answer' => 'WHERE фильтрует строки ДО группировки (GROUP BY), HAVING - ПОСЛЕ группировки. В WHERE нельзя использовать агрегатные функции (COUNT, SUM, AVG), а в HAVING - можно. Простыми словами: WHERE отбирает отдельные строки, HAVING отбирает уже посчитанные группы.',
                'code_example' => <<<'SQL'
SELECT user_id, COUNT(*) AS orders_count
FROM orders
WHERE status = 'completed'         -- фильтр строк
GROUP BY user_id
HAVING COUNT(*) > 5;               -- фильтр групп
SQL,
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое GROUP BY?',
                'answer' => 'GROUP BY группирует строки с одинаковыми значениями указанных столбцов в одну строку. Используется вместе с агрегатными функциями (COUNT, SUM, AVG, MIN, MAX). Все столбцы в SELECT, которых нет в агрегате, должны быть в GROUP BY.',
                'code_example' => <<<'SQL'
SELECT category_id, COUNT(*) AS total, AVG(price) AS avg_price
FROM products
GROUP BY category_id;
SQL,
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое ORDER BY и как сортировать по нескольким полям?',
                'answer' => 'ORDER BY сортирует результат. ASC - по возрастанию (по умолчанию), DESC - по убыванию. Можно сортировать по нескольким столбцам - сначала по первому, затем по второму при равенстве. Также можно сортировать по выражениям и порядковому номеру столбца в SELECT.',
                'code_example' => <<<'SQL'
SELECT name, age, salary
FROM employees
ORDER BY salary DESC, age ASC, name;
SQL,
                'code_language' => 'sql',
                'difficulty' => 1,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое LIMIT и OFFSET?',
                'answer' => 'LIMIT N - вернуть не больше N строк. OFFSET M - пропустить первые M строк. Часто используются для постраничной выдачи. Минус OFFSET-пагинации: при больших OFFSET-ах БД всё равно сканирует все пропускаемые строки, поэтому на больших таблицах это медленно.',
                'code_example' => <<<'SQL'
-- 3-я страница по 20 элементов
SELECT * FROM articles
ORDER BY published_at DESC
LIMIT 20 OFFSET 40;
SQL,
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое DISTINCT?',
                'answer' => 'DISTINCT убирает дубликаты строк в результате запроса. Применяется ко всему набору столбцов в SELECT, а не к одному. DISTINCT может быть медленным на больших данных, потому что требует сортировки или хеширования.',
                'code_example' => <<<'SQL'
SELECT DISTINCT country FROM users;

-- Дубликаты определяются по комбинации всех столбцов
SELECT DISTINCT country, city FROM users;
SQL,
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Какие есть виды JOIN и чем они отличаются?',
                'answer' => 'INNER JOIN - только совпадающие строки в обеих таблицах. LEFT JOIN - все из левой + совпадающие справа (несовпадения - NULL). RIGHT JOIN - наоборот. FULL OUTER JOIN - все из обеих таблиц, несовпадения - NULL. CROSS JOIN - декартово произведение (каждая с каждой). SELF JOIN - таблица соединяется сама с собой.',
                'code_example' => <<<'SQL'
-- INNER: только пользователи с заказами
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
LEFT JOIN employees m ON e.manager_id = m.id;
SQL,
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'В чём разница между LEFT JOIN и INNER JOIN на практике?',
                'answer' => 'INNER JOIN отбросит строки из левой таблицы, для которых нет совпадений справа. LEFT JOIN сохранит все строки слева, заполнив правую часть NULL-ами. Используй LEFT JOIN, когда важно сохранить все левые записи (например, "все пользователи, даже те, у кого нет заказов").',
                'code_example' => <<<'SQL'
-- Найти пользователей БЕЗ заказов
SELECT u.id, u.name
FROM users u
LEFT JOIN orders o ON o.user_id = u.id
WHERE o.id IS NULL;
SQL,
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое INSERT, UPDATE, DELETE?',
                'answer' => 'INSERT добавляет новые строки. UPDATE изменяет существующие. DELETE удаляет. UPDATE и DELETE без WHERE применяются ко всей таблице - очень опасно! Всегда сначала пиши SELECT с тем же WHERE, чтобы убедиться, что попал в нужные строки.',
                'code_example' => <<<'SQL'
INSERT INTO users (name, email) VALUES ('Иван', 'ivan@example.com');

INSERT INTO users (name, email) VALUES
    ('Анна', 'anna@example.com'),
    ('Пётр', 'petr@example.com');

UPDATE users SET email = 'new@example.com' WHERE id = 1;

DELETE FROM users WHERE created_at < '2020-01-01';
SQL,
                'code_language' => 'sql',
                'difficulty' => 1,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Чем отличается UNION от UNION ALL?',
                'answer' => 'UNION объединяет результаты двух запросов и убирает дубликаты (что требует сортировки/хеширования - медленнее). UNION ALL объединяет без удаления дубликатов - значительно быстрее. Используй UNION ALL, если знаешь, что дубликатов нет или они не мешают.',
                'code_example' => <<<'SQL'
-- Без дубликатов (медленнее)
SELECT name FROM customers
UNION
SELECT name FROM suppliers;

-- С дубликатами (быстрее)
SELECT name FROM customers
UNION ALL
SELECT name FROM suppliers;
SQL,
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое подзапрос (subquery)?',
                'answer' => 'Подзапрос - это запрос внутри другого запроса. Бывает скалярный (возвращает одно значение), однострочный, многострочный. Может использоваться в SELECT, FROM, WHERE. Часто заменяется JOIN-ом или CTE для лучшей читаемости и производительности.',
                'code_example' => <<<'SQL'
-- Скалярный подзапрос
SELECT name, (SELECT COUNT(*) FROM orders WHERE user_id = u.id) AS orders
FROM users u;

-- В WHERE
SELECT * FROM products
WHERE category_id IN (SELECT id FROM categories WHERE active = true);

-- В FROM (производная таблица)
SELECT t.user_id, t.total
FROM (SELECT user_id, SUM(amount) AS total FROM payments GROUP BY user_id) t
WHERE t.total > 1000;
SQL,
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое CTE (WITH ... AS) и зачем он нужен?',
                'answer' => 'CTE (Common Table Expression) - это именованный временный набор результатов, который существует только в рамках одного запроса. Делает сложные запросы читаемее, разбивая их на логические шаги. Также CTE позволяет рекурсивные запросы (RECURSIVE) для работы с иерархиями.',
                'code_example' => <<<'SQL'
-- Обычный CTE
WITH active_users AS (
    SELECT id, name FROM users WHERE active = true
),
recent_orders AS (
    SELECT user_id, COUNT(*) AS cnt
    FROM orders
    WHERE created_at > NOW() - INTERVAL '30 days'
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
SELECT * FROM tree;
SQL,
                'code_language' => 'sql',
                'difficulty' => 4,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Какие есть агрегатные функции?',
                'answer' => 'COUNT(*) - количество строк, COUNT(col) - количество ненулевых значений, COUNT(DISTINCT col) - уникальных. SUM - сумма, AVG - среднее, MIN - минимум, MAX - максимум. Также есть STRING_AGG (PG) / GROUP_CONCAT (MySQL) для объединения строк, ARRAY_AGG для массивов.',
                'code_example' => <<<'SQL'
SELECT
    COUNT(*) AS total_rows,
    COUNT(email) AS users_with_email,
    COUNT(DISTINCT country) AS unique_countries,
    SUM(salary) AS sum_salary,
    AVG(salary) AS avg_salary,
    MIN(created_at) AS first,
    MAX(created_at) AS last,
    STRING_AGG(name, ', ') AS all_names
FROM users;
SQL,
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое NULL и трехзначная логика?',
                'answer' => 'NULL означает "значение неизвестно/отсутствует". В реляционной алгебре любая операция с NULL даёт NULL: NULL = NULL -> NULL (не TRUE!), NULL + 5 -> NULL. Поэтому используется трёхзначная логика: TRUE, FALSE, UNKNOWN. WHERE пропускает только строки, где условие TRUE. Для сравнения с NULL используй IS NULL / IS NOT NULL.',
                'code_example' => <<<'SQL'
-- Это НЕ найдёт строки с NULL
SELECT * FROM users WHERE deleted_at = NULL;  -- неверно

-- Правильно
SELECT * FROM users WHERE deleted_at IS NULL;
SELECT * FROM users WHERE deleted_at IS NOT NULL;

-- NULL в выражениях
SELECT 1 + NULL;       -- NULL
SELECT NULL = NULL;    -- NULL (UNKNOWN)
SQL,
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что делают COALESCE, NULLIF, NVL?',
                'answer' => 'COALESCE(a, b, c, ...) возвращает первое не-NULL значение из списка. NULLIF(a, b) возвращает NULL, если a = b, иначе возвращает a (удобно избегать деления на ноль). NVL - аналог COALESCE в Oracle (в PostgreSQL/MySQL - COALESCE).',
                'code_example' => <<<'SQL'
-- Замена NULL на дефолт
SELECT COALESCE(nickname, name, 'Аноним') FROM users;

-- Избегаем деления на ноль
SELECT total / NULLIF(count, 0) AS average FROM stats;

-- COALESCE в UPDATE
UPDATE products SET price = COALESCE(new_price, price);
SQL,
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое CASE WHEN?',
                'answer' => 'CASE WHEN - условное выражение SQL (аналог if/else). Бывает простой (CASE column WHEN value THEN ...) и searched (CASE WHEN condition THEN ...). Можно использовать в SELECT, ORDER BY, WHERE, GROUP BY.',
                'code_example' => <<<'SQL'
SELECT
    name,
    salary,
    CASE
        WHEN salary < 50000 THEN 'low'
        WHEN salary < 100000 THEN 'mid'
        ELSE 'high'
    END AS salary_band
FROM employees;

-- CASE для условной агрегации (портируемо)
SELECT
    COUNT(CASE WHEN status = 'completed' THEN 1 END) AS done,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) AS pending
FROM orders;

-- FILTER (PostgreSQL/стандарт SQL, нет в MySQL)
SELECT COUNT(*) FILTER (WHERE status = 'completed') AS done FROM orders;
SQL,
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'В чём разница между IN и EXISTS?',
                'answer' => 'IN сравнивает значение со списком (или результатом подзапроса). EXISTS проверяет, вернул ли подзапрос хоть одну строку (TRUE/FALSE). Исторически считалось, что EXISTS быстрее, но современные оптимизаторы (PostgreSQL 9+, MySQL 8+, SQL Server) обычно транслируют IN(subquery) и EXISTS(subquery) в один и тот же semi-join — план получается идентичный. Принципиальная разница остаётся в семантике NULL: NOT IN с NULL в подзапросе возвращает UNKNOWN и не находит ничего, поэтому NOT EXISTS почти всегда предпочтительнее.',
                'code_example' => <<<'SQL'
-- IN
SELECT * FROM users WHERE id IN (SELECT user_id FROM orders);

-- EXISTS - часто эффективнее
SELECT * FROM users u
WHERE EXISTS (SELECT 1 FROM orders o WHERE o.user_id = u.id);

-- NOT EXISTS - безопаснее NOT IN при NULL
SELECT * FROM users u
WHERE NOT EXISTS (SELECT 1 FROM orders o WHERE o.user_id = u.id);
SQL,
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое BETWEEN, LIKE, ILIKE?',
                'answer' => 'BETWEEN a AND b - значение в диапазоне [a, b] включительно. LIKE - сопоставление с шаблоном: % (любые символы), _ (один символ). ILIKE (PostgreSQL) - LIKE без учёта регистра. В MySQL по умолчанию LIKE регистронезависим (зависит от collation).',
                'code_example' => <<<'SQL'
SELECT * FROM users WHERE age BETWEEN 18 AND 65;

SELECT * FROM products WHERE name LIKE 'iPhone%';     -- начинается с
SELECT * FROM products WHERE name LIKE '%pro%';       -- содержит
SELECT * FROM products WHERE name LIKE 'iPhone_';     -- ровно одна буква в конце

-- PostgreSQL: без учёта регистра
SELECT * FROM products WHERE name ILIKE '%pro%';
SQL,
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Защищают ли prepared statements от SQL-инъекции в ORDER BY? Можно ли передать имя колонки через параметр?',
                'answer' => 'НЕТ. Prepared statements / PDO bindings / Eloquent параметризация защищают только ЗНАЧЕНИЯ в WHERE / VALUES / SET - то, что в SQL заменяется плейсхолдером ?. Имена ТАБЛИЦ, КОЛОНОК и ключевые слова (ASC/DESC, LIMIT, OFFSET, GROUP BY, ORDER BY) ПАРАМЕТРИЗОВАТЬ НЕЛЬЗЯ. Парсер БД распарсивает SQL ДО подстановки параметров - на этапе разбора имена идентификаторов и keywords уже должны быть в строке. SELECT * FROM users ORDER BY ? - здесь ? может быть только value (например, порядковый номер ORDER BY 1), но не "name". Это не баг, а архитектура подготовленных запросов - так работает сетевой протокол PDO/MySQL/Postgres. Прямая дыра возникает, когда разработчик принимает имя колонки от пользователя для сортировки и подставляет в строку: ORDER BY {$_GET["sort"]} - классический SQL-injection ("id; DROP TABLE users--" в URL уничтожит базу). Аналогично с динамическими WHERE colname (имя колонки), таблицами, JOIN-ами. Решение - whitelist на стороне приложения: фиксированный список разрешённых полей, и если запрос пришёл вне него - либо подменить на дефолт, либо вернуть 422. В Laravel используют match, in_array или enum-валидацию. Для совсем сложных кейсов (динамические JOIN-ы, динамические колонки SELECT) - запрос собирается на стороне приложения по белому списку, никогда конкатенацией user input. То же самое относится к ASC/DESC: $direction = $request->input("dir") === "desc" ? "desc" : "asc"; - нельзя передавать произвольную строку. Бонус: LIMIT/OFFSET - это ЗНАЧЕНИЯ, их параметризовать можно (PDO с PDO::PARAM_INT для надёжности).',
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
                'answer' => 'Неявный JOIN перечисляет таблицы через запятую в FROM, а условие связи живёт в WHERE: FROM a, b WHERE a.id = b.a_id. Явный JOIN использует ключевое слово JOIN ... ON и сразу отделяет условие связи от условий фильтрации. Явная форма читается лучше и страхует от случайного декартова произведения, если про условие в WHERE забудут. Современные руководства по стилю требуют только явные JOIN, неявные остались как наследие старого SQL-92.',
                'difficulty' => 2,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Чем отличается условие в JOIN ... ON от условия в WHERE для LEFT JOIN?',
                'answer' => 'Для INNER JOIN это эквивалентно: оптимизатор может перенести условие из ON в WHERE и обратно. Для LEFT JOIN расположение принципиально: условие в ON решает, какие строки правой таблицы прицепляются, оставляя все строки левой таблицы и подставляя NULL, если нет совпадения. То же условие в WHERE применяется уже к итоговому набору и отфильтровывает строки с NULL, фактически превращая LEFT JOIN во внутренний. Это типичная ловушка — фильтр по правой таблице пишут в WHERE и теряют все ненайденные записи.',
                'difficulty' => 3,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'В чём разница между LIKE и REGEXP в MySQL?',
                'answer' => 'LIKE использует две метасимвола: % для любой последовательности и _ для одного символа. REGEXP принимает полноценные регулярные выражения с якорями, классами, альтернативами и квантификаторами. REGEXP мощнее, но почти всегда вызывает полный скан таблицы — индекс по строке помогает только LIKE, и то лишь при префиксном шаблоне без % в начале. Поэтому REGEXP уместен для разовых отчётов, а для частых поисков лучше нормализовать данные или использовать FULLTEXT.',
                'difficulty' => 3,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Как сделать условное вычисление прямо в SELECT через CASE WHEN?',
                'answer' => 'Конструкция CASE WHEN условие THEN значение ELSE другое END позволяет в одном запросе формировать категории, флаги или производные поля без хранимых функций. Её удобно комбинировать с агрегатами — SUM(CASE WHEN ... THEN 1 ELSE 0 END) даёт условный COUNT по нескольким группам в одной строке. В MySQL есть и более короткий IF(condition, then, else), но он не переносим между СУБД, тогда как CASE — это стандарт SQL и работает везде. NULLIF и COALESCE — частные случаи той же идеи.',
                'difficulty' => 2,
                'topic' => 'database.sql_basics',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'В чём разница между подзапросами с ANY и ALL?',
                'answer' => 'Сравнение через ANY (синоним SOME) истинно, если условие верно хотя бы для одного значения из подзапроса: x > ANY(SELECT ...) равносильно x > MIN(...). ALL требует, чтобы условие выполнялось для всех значений: x > ALL(SELECT ...) то же, что x > MAX(...). С пустым подзапросом ANY всегда даёт false, а ALL — true, что часто становится сюрпризом и приводит к неожиданным результатам. На практике эти конструкции встречаются редко — обычно их переписывают через MIN/MAX или EXISTS.',
                'difficulty' => 3,
                'topic' => 'database.sql_basics',
            ],
        ];
    }
}
