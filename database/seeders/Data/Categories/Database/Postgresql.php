<?php

namespace Database\Seeders\Data\Categories\Database;

class Postgresql
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Базы данных',
                'question' => 'jsonb vs json в PostgreSQL?',
                'answer' => 'json - хранит JSON как текст, сохраняя пробелы и порядок ключей. Парсится при каждом обращении. jsonb - бинарный формат, преобразуется при вставке. Минус: чуть медленнее запись, есть преобразование. Плюсы: быстрее операции, поддержка GIN-индекса, операторы @>, ?, #>>. На практике: используй jsonb всегда, если не нужно сохранить сырое представление.',
                'code_example' => 'CREATE TABLE events (id BIGSERIAL PRIMARY KEY, data JSONB);

CREATE INDEX idx_events_data ON events USING gin(data);

SELECT * FROM events WHERE data @> \'{"type": "click"}\';
SELECT data->>\'user_id\' FROM events;',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.postgresql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое массивы (arrays) в PostgreSQL?',
                'answer' => 'PostgreSQL поддерживает массивы любых типов как нативные значения столбцов. Можно хранить, индексировать (GIN), искать. Полезно для тегов, ролей и т.п. без отдельной таблицы. Однако, если связи нужно расширять или фильтровать сложно, лучше нормализованная таблица.',
                'code_example' => 'CREATE TABLE posts (
    id BIGSERIAL PRIMARY KEY,
    title TEXT,
    tags TEXT[]
);

INSERT INTO posts (title, tags) VALUES (\'Hello\', ARRAY[\'php\', \'laravel\', \'sql\']);

SELECT * FROM posts WHERE \'php\' = ANY(tags);
SELECT * FROM posts WHERE tags @> ARRAY[\'laravel\'];

CREATE INDEX idx_posts_tags ON posts USING gin(tags);',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.postgresql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое VACUUM, autovacuum и bloat?',
                'answer' => 'В PostgreSQL из-за MVCC при UPDATE/DELETE строки не удаляются физически, а помечаются - получаются "мёртвые" версии (dead tuples). VACUUM очищает их, освобождая место в страницах. Autovacuum - демон, который запускает VACUUM автоматически по порогам. Bloat - распухание таблицы/индекса от мёртвых версий, замедляет всё. Простыми словами: VACUUM - это уборщик, который выкидывает мусор; bloat - это гора мусора, которая копится, если уборщик не справляется.',
                'code_example' => '-- Ручной запуск
VACUUM users;
VACUUM ANALYZE users;       -- + обновить статистику
VACUUM FULL users;          -- агрессивный, переписывает таблицу (lock!)

-- Посмотреть мёртвые строки
SELECT relname, n_dead_tup FROM pg_stat_user_tables;',
                'code_language' => 'sql',
                'difficulty' => 4,
                'topic' => 'database.postgresql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое Materialized View?',
                'answer' => 'Materialized View - это представление, чей результат физически сохранён на диске, в отличие от обычного VIEW (вычисляется при каждом обращении). Полезно для тяжёлых аналитических запросов: считаешь раз - читаешь много раз быстро. Минус: данные могут устареть, нужно обновлять вручную (REFRESH).',
                'code_example' => 'CREATE MATERIALIZED VIEW daily_sales AS
SELECT DATE(created_at) AS day, SUM(amount) AS total
FROM orders
GROUP BY DATE(created_at);

CREATE INDEX ON daily_sales(day);

-- Обновление данных
REFRESH MATERIALIZED VIEW daily_sales;
REFRESH MATERIALIZED VIEW CONCURRENTLY daily_sales;  -- без локa',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.postgresql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Чем отличаются ROW_NUMBER, RANK, DENSE_RANK?',
                'answer' => 'Все нумеруют строки по сортировке внутри окна. ROW_NUMBER - всегда уникальные номера 1, 2, 3, 4 (даже при равных значениях). RANK - при равных значениях даёт одинаковый ранг и пропускает: 1, 2, 2, 4. DENSE_RANK - даёт одинаковый ранг, но не пропускает: 1, 2, 2, 3.',
                'code_example' => 'SELECT
    name,
    salary,
    ROW_NUMBER() OVER (ORDER BY salary DESC) AS rn,
    RANK() OVER (ORDER BY salary DESC) AS rnk,
    DENSE_RANK() OVER (ORDER BY salary DESC) AS dense_rnk
FROM employees;',
                'code_language' => 'sql',
                'difficulty' => 4,
                'topic' => 'database.postgresql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что делают LAG и LEAD?',
                'answer' => 'LAG(col, n) возвращает значение col в строке на n позиций РАНЬШЕ (по ORDER BY окна). LEAD - на n позиций ПОЗЖЕ. Полезны для сравнения с предыдущей/следующей строкой - например, рассчитать дельту продаж по дням.',
                'code_example' => 'SELECT
    day,
    sales,
    LAG(sales) OVER (ORDER BY day) AS prev_day,
    sales - LAG(sales) OVER (ORDER BY day) AS delta
FROM daily_sales;',
                'code_language' => 'sql',
                'difficulty' => 4,
                'topic' => 'database.postgresql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое GROUPING SETS, ROLLUP, CUBE?',
                'answer' => 'Расширения GROUP BY для агрегации по нескольким уровням за один запрос. GROUPING SETS - явные комбинации группировки. ROLLUP(a, b) - иерархические подытоги: (a,b), (a), (). CUBE(a, b) - все комбинации: (a,b), (a), (b), (). Часто используется в OLAP/аналитике.',
                'code_example' => '-- Подытоги по году, месяцу и общий
SELECT year, month, SUM(amount)
FROM sales
GROUP BY ROLLUP (year, month);

-- Все комбинации измерений
SELECT country, product, SUM(amount)
FROM sales
GROUP BY CUBE (country, product);',
                'code_language' => 'sql',
                'difficulty' => 4,
                'topic' => 'database.postgresql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое репликация в PostgreSQL: streaming vs logical?',
                'answer' => 'Streaming replication - физическая репликация, реплика получает записи WAL мастера (изменения страниц на уровне блоков). Точная копия. Используется для отказоустойчивости и read-replicas. Logical replication - логическая, реплицируются изменения по таблицам через publication/subscription. Можно реплицировать выборочно (только нужные таблицы), между разными мажорными версиями, делать преобразования.',
                'difficulty' => 5,
                'topic' => 'database.postgresql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое Full-Text Search в PostgreSQL?',
                'answer' => 'Full-text search в PG - встроенный полнотекстовый поиск через типы tsvector (предобработанный документ) и tsquery (запрос). Поддерживает стемминг (приведение слов к основе), стоп-слова, ранжирование, разные языки. Индексируется через GIN. Для базовых задач хватает; для сложного - Elasticsearch.',
                'code_example' => 'SELECT * FROM articles
WHERE to_tsvector(\'russian\', body) @@ to_tsquery(\'russian\', \'лошадь & белая\');

CREATE INDEX idx_articles_tsv
ON articles USING gin(to_tsvector(\'russian\', body));

-- С ранжированием
SELECT title, ts_rank(to_tsvector(\'russian\', body), q) AS rank
FROM articles, to_tsquery(\'russian\', \'лошадь\') q
WHERE to_tsvector(\'russian\', body) @@ q
ORDER BY rank DESC;',
                'code_language' => 'sql',
                'difficulty' => 4,
                'topic' => 'database.postgresql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Как искать медленные запросы в PostgreSQL без APM? Что такое pg_stat_statements?',
                'answer' => 'pg_stat_statements - стандартное расширение PostgreSQL, накапливающее агрегированную статистику по нормализованным запросам (литералы заменяются на $1, $2 - запросы с разными значениями группируются вместе). Для каждого запроса хранит: число вызовов (calls), общее и среднее время (total_exec_time, mean_exec_time), число прочитанных/записанных строк (rows), shared/local block hits/reads (попадания/промахи в shared buffers), стандартное отклонение времени (stddev_exec_time). Это позволяет в проде найти "топ-10 запросов по суммарному времени" - именно они дают основную нагрузку, даже если каждый отдельный запрос быстрый. Включается через shared_preload_libraries = pg_stat_statements в postgresql.conf + CREATE EXTENSION. Сброс статистики - SELECT pg_stat_statements_reset(). Альтернативы и дополнения: 1) auto_explain - логирует EXPLAIN ANALYZE для запросов дольше N мс. 2) pg_stat_activity - текущие выполняющиеся запросы, поиск висящих транзакций (state="idle in transaction" - типичная проблема). 3) log_min_duration_statement в postgresql.conf - пишет в лог запросы дольше порога. Связка pg_stat_statements + auto_explain даёт почти полноценный self-hosted APM для PG.',
                'code_example' => '-- Установка
-- 1) postgresql.conf:
--    shared_preload_libraries = \'pg_stat_statements\'
--    pg_stat_statements.track = all
-- 2) рестарт PG
-- 3) CREATE EXTENSION pg_stat_statements;

-- Топ-10 запросов по суммарному времени
SELECT
    query,
    calls,
    round(total_exec_time::numeric, 2)         AS total_ms,
    round(mean_exec_time::numeric, 2)          AS mean_ms,
    round((total_exec_time/sum(total_exec_time) OVER ())::numeric * 100, 2) AS pct,
    rows
FROM pg_stat_statements
ORDER BY total_exec_time DESC
LIMIT 10;

-- Запросы с самым большим разбросом времени (нестабильные)
SELECT query, calls, mean_exec_time, stddev_exec_time
FROM pg_stat_statements
WHERE calls > 100
ORDER BY stddev_exec_time DESC
LIMIT 10;

-- Найти зависшие транзакции
SELECT pid, state, age(now(), xact_start) AS age, query
FROM pg_stat_activity
WHERE state = \'idle in transaction\'
ORDER BY age DESC;

-- Сброс статистики
SELECT pg_stat_statements_reset();',
                'code_language' => 'sql',
                'difficulty' => 5,
                'topic' => 'database.postgresql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Почему в PgBouncer transaction pooling ломаются Prepared Statements в Laravel/PDO и как это починить?',
                'answer' => 'PgBouncer - популярный пулер соединений для Postgres. Имеет три режима. Session pooling: одна клиентская сессия = одно физическое соединение на всё время до отключения клиента (поведение как без pooler). Transaction pooling (самый распространённый в проде): физическое соединение выдаётся клиенту только на ВРЕМЯ ОДНОЙ ТРАНЗАКЦИИ, после COMMIT/ROLLBACK возвращается в пул и может быть отдано другому клиенту. Statement pooling: ещё агрессивнее, на одну statement. ПРОБЛЕМА: Prepared Statements (PG-команды PREPARE name AS ... + EXECUTE name) - это server-side объект, привязанный к ФИЗИЧЕСКОМУ соединению. PDO в НАТИВНОМ (не-эмулирующем) режиме на $stmt = $pdo->prepare() отправляет настоящую серверную команду PREPARE name AS ... и при последующих $stmt->execute() шлёт EXECUTE name по сохранённому имени. Эмулирующий режим, наоборот, ничего на сервер не prepare-ит - PDO сам подставляет параметры в SQL-строку на стороне PHP и шлёт её как обычный query, поэтому с pooling-ом проблемы нет. В transaction pooling между PREPARE и EXECUTE PgBouncer может выдать соединение другому клиенту, и при возврате - на этом соединении уже не будет нашего PREPARE, выскочит "prepared statement does not exist". В Laravel это ловится как PDOException или QueryException в самых неожиданных местах под нагрузкой. Решения: 1) PDO::ATTR_EMULATE_PREPARES => true (в config/database.php в options) - PDO сам подставляет параметры в PHP, на сервер уходит уже готовая SQL-строка без PREPARE/EXECUTE. Минус: меньше защита от типов на стороне БД, но безопасность от инъекций сохраняется (PDO правильно экранирует). 2) В PgBouncer 1.21+ (выпущен в 2023) появился experimental support для protocol-level prepared statements - max_prepared_statements > 0 в pgbouncer.ini, тогда PgBouncer сам реплицирует PREPARE на каждое физ-соединение, на которое попадает клиент. Стабильно с 1.22+. 3) Использовать session pooling вместо transaction - но это убивает преимущество пула (меньше effective connections). 4) В Laravel - php artisan queue:work обычно использует transaction pooling и страдает от этого; решение - emulate prepares на queue connection. Симптомы: спорадические ошибки "prepared statement \\"pdo_stmt_00000123\\" does not exist" под нагрузкой, особенно когда несколько воркеров одновременно. Связанные подводные камни transaction pooling: нельзя использовать SET (настройки сессии теряются между транзакциями), LISTEN/NOTIFY не работает, advisory locks на уровне сессии тоже.',
                'code_example' => '<?php
// config/database.php
\'pgsql\' => [
    \'driver\' => \'pgsql\',
    \'host\' => env(\'DB_HOST\', \'pgbouncer.internal\'),
    \'port\' => env(\'DB_PORT\', 6432), // PgBouncer порт
    // ...
    \'options\' => [
        // КРИТИЧНО при transaction pooling без max_prepared_statements
        PDO::ATTR_EMULATE_PREPARES => true,
    ],
],

// Альтернатива - PgBouncer 1.22+ с поддержкой prepared statements
// /etc/pgbouncer/pgbouncer.ini:
// pool_mode = transaction
// max_prepared_statements = 100  ; 0 по умолчанию = выключено
// после этого PDO::ATTR_EMULATE_PREPARES можно НЕ ставить

// Симптом проблемы в логах:
// SQLSTATE[26000]: Invalid sql statement name:
// 7 ERROR: prepared statement "pdo_stmt_00000abc" does not exist

// Проверка какой режим pooling у вашего PgBouncer
// $ psql -h pgbouncer -p 6432 -U user pgbouncer
// SHOW POOLS;
// SHOW CONFIG; // pool_mode = transaction|session|statement

// Прочие подводные камни transaction pooling
DB::statement("SET search_path TO custom_schema"); // НЕ сохраняется
DB::listen(...); // LISTEN/NOTIFY не работает между транзакциями
DB::raw("SELECT pg_advisory_lock(?)"); // session-level lock протекает на чужого клиента!
// Используйте pg_advisory_xact_lock - живёт только до конца транзакции',
                'code_language' => 'php',
                'difficulty' => 5,
                'topic' => 'database.postgresql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Какие числовые типы предоставляет PostgreSQL и когда какой выбирать?',
                'answer' => '**Целые:**
- **`smallint`** — 2 байта (~±32K);
- **`integer`** — 4 байта (~±2.1 млрд);
- **`bigint`** — 8 байт (~±9.2·10¹⁸).

**Дробные:**
- **`numeric(p, s)`** / **`decimal(p, s)`** — **точная** фиксированная точка. Для **денег** и любых сумм без потери копейки. Медленнее остальных;
- **`real`** — 4 байта, приближённая плавающая точка;
- **`double precision`** — 8 байт, приближённая. Для научных расчётов, **не** для финансов.

**Автоинкрементные `id`:**
- исторически — `serial` / `bigserial` (макрос над `INTEGER + SEQUENCE`);
- **современный способ** — `GENERATED ALWAYS AS IDENTITY` (стандарт SQL, более предсказуемо).',
                'code_example' => 'CREATE TABLE products (
    id         BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    qty        INTEGER          NOT NULL,
    price      NUMERIC(10, 2)   NOT NULL,   -- точная сумма
    weight_kg  DOUBLE PRECISION              -- приближённый вес
);',
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.postgresql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Чем различаются CHAR(n), VARCHAR(n) и TEXT в PostgreSQL?',
                'answer' => '- **`CHAR(n)`** — **фиксированная длина**: строка добивается пробелами до `n` символов;
- **`VARCHAR(n)`** — **переменная длина** с лимитом `n`;
- **`TEXT`** — переменная длина **без лимита**.

**Главная фишка PG:** в отличие от многих СУБД, все три типа **хранятся одинаково** и имеют **одинаковую производительность**.

**Практика в PostgreSQL:**
- обычно берут **`TEXT`** или `VARCHAR` без указания `n`;
- если нужен лимит — задают через **`CHECK (length(col) <= 255)`** (это удобнее: лимит можно изменить без `ALTER TYPE`, в отличие от `VARCHAR(n)`);
- `CHAR(n)` — почти никогда, кроме фиксированных кодов типа `ISO-кода страны`.',
                'code_example' => 'CREATE TABLE users (
    id    BIGSERIAL PRIMARY KEY,
    code  CHAR(3),       -- ровно 3 символа, добивается пробелами
    name  VARCHAR(255),  -- до 255 символов
    bio   TEXT           -- произвольная длина
        CHECK (length(bio) <= 10000)  -- мягкий лимит через CHECK
);',
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.postgresql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Чем timestamp отличается от timestamptz в PostgreSQL?',
                'answer' => 'timestamp without time zone хранит "голую" дату-время как есть и никаких преобразований часового пояса не выполняет — что записали, то и прочитали. timestamp with time zone (timestamptz) при INSERT берёт значение, приводит его к UTC по текущему SET TIME ZONE сессии и хранит в UTC; при SELECT отдаёт обратно в timezone сессии. Несмотря на название, сам пояс в строке не сохраняется — сохраняется точный момент во времени. Из этого вытекает практическое правило: для приложений с пользователями в разных таймзонах и для всех системных полей (created_at, updated_at) почти всегда нужен timestamptz. timestamp без TZ имеет смысл только для абстрактного "время суток" (например, расписание звонков "звонок в 09:00 локального времени каждого офиса"), где привязка к UTC не нужна. Сравнивать timestamp и timestamptz напрямую — частая ошибка.',
                'code_example' => 'CREATE TABLE events (
    id BIGSERIAL PRIMARY KEY,
    at_local timestamp,        -- расписание "в 9:00 локально"
    at_utc   timestamptz       -- "произошло в этот момент"
);

SET TIME ZONE \'Europe/Moscow\';                   -- UTC+3
INSERT INTO events(at_local, at_utc) VALUES
    (\'2026-05-20 09:00\', \'2026-05-20 09:00\');     -- сохраняем

SET TIME ZONE \'Europe/London\';                   -- UTC+1
SELECT at_local, at_utc FROM events;
-- at_local = 2026-05-20 09:00:00 (без изменений)
-- at_utc   = 2026-05-20 07:00:00+01 (тот же момент во времени London)',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.postgresql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое тип ENUM в PostgreSQL и какие у него ограничения?',
                'answer' => '**`ENUM`** — пользовательский тип со **статическим упорядоченным списком строковых значений**. Создаётся через `CREATE TYPE имя AS ENUM (...)`.

**Свойства:**
- занимает **4 байта** (хранится как номер позиции);
- сравнение и сортировка идут **по порядку объявления**, а не по алфавиту;
- значение можно **`ADD VALUE`** через `ALTER TYPE`.

**Минусы:**
- **удалить или переименовать** существующее значение — сложно (требует пересоздания типа);
- порядок значений в списке **влияет на `ORDER BY`** — добавил `\'refunded\'` после `\'cancelled\'`, сортировка может оказаться неинтуитивной.

**Альтернативы** для бизнес-статусов:
- **lookup-таблица** + `FOREIGN KEY` (гибкость, можно добавить флаги);
- **`CHECK (status IN (...))`** на обычной `VARCHAR`-колонке (проще менять).',
                'code_example' => 'CREATE TYPE order_status AS ENUM (\'new\', \'paid\', \'shipped\', \'cancelled\');

CREATE TABLE orders (
    id     BIGSERIAL PRIMARY KEY,
    status order_status NOT NULL DEFAULT \'new\'
);

-- Добавить значение можно, удалить — нет
ALTER TYPE order_status ADD VALUE \'refunded\';',
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.postgresql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Какие действия ON DELETE/ON UPDATE поддерживает FOREIGN KEY в PostgreSQL?',
                'answer' => 'CASCADE — каскадно удалить или обновить все ссылающиеся строки. RESTRICT и NO ACTION оба запрещают операцию, если есть ссылающиеся строки; разница в том, что NO ACTION откладывает проверку до конца транзакции (совместимо с DEFERRABLE INITIALLY DEFERRED), а RESTRICT падает мгновенно. SET NULL обнуляет ссылающийся столбец (требует, чтобы он был nullable). SET DEFAULT ставит ему значение по умолчанию (и оно тоже должно проходить FK-проверку, иначе ошибка). По умолчанию используется NO ACTION. Выбирать действие надо явно под бизнес-смысл: CASCADE удобно для строго подчинённых сущностей (комментарии при удалении поста), но опасно на больших таблицах — может развернуться в долгие каскадные DELETE с блокировками и логированием в WAL.',
                'code_example' => 'CREATE TABLE posts (
    id BIGSERIAL PRIMARY KEY,
    title TEXT NOT NULL
);

CREATE TABLE comments (
    id BIGSERIAL PRIMARY KEY,
    post_id BIGINT NOT NULL,
    body TEXT NOT NULL,
    -- при удалении поста удалить все комментарии к нему
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);

CREATE TABLE orders (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT,
    -- при удалении пользователя оставить заказ, но обнулить ссылку
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Отложенная проверка - удобно при цикличных FK
ALTER TABLE orders ADD CONSTRAINT fk_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    DEFERRABLE INITIALLY DEFERRED;',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.postgresql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое CHECK constraint и зачем он нужен на уровне БД?',
                'answer' => '**`CHECK`** — ограничение, проверяющее **произвольное логическое выражение** для каждой строки.

**Примеры:**
- `CHECK (price > 0)` — цена не может быть отрицательной;
- `CHECK (status IN (\'new\',\'paid\',\'shipped\'))` — только заданные статусы;
- `CHECK (end_date >= start_date)` — конец позже начала.

**Зачем именно на уровне БД, а не в коде приложения:**
- ограничение **нельзя обойти** — ни ORM-баг, ни ручной `UPDATE` из `psql`, ни прямой импорт CSV;
- работает **всегда**, кто бы ни писал данные.

**Ограничения самого `CHECK`:**
- может ссылаться на **колонки одной строки**, не на другие таблицы;
- для межтабличных правил — **триггеры** или `EXCLUDE` constraint.

Проверяется на `INSERT`/`UPDATE` и при `ADD CONSTRAINT` к существующим данным.',
                'code_example' => 'CREATE TABLE products (
    id    BIGSERIAL PRIMARY KEY,
    price NUMERIC(10, 2) NOT NULL CHECK (price > 0),
    status VARCHAR(20)   NOT NULL CHECK (status IN (\'new\', \'paid\', \'shipped\')),
    -- многоколоночное условие
    CHECK (discount_price IS NULL OR discount_price < price)
);

-- Добавить CHECK к уже существующей таблице
ALTER TABLE products ADD CONSTRAINT price_positive CHECK (price > 0);',
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.postgresql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Какие сетевые типы и UUID есть в PostgreSQL и зачем они отдельно?',
                'answer' => 'inet хранит IPv4/IPv6 адрес с опциональной маской подсети (7 или 19 байт), cidr — сеть с обязательной маской (хосто-биты обязательно нули). macaddr (6 байт) и macaddr8 (8 байт) — MAC-адреса. По сравнению с обычным TEXT они занимают меньше места, валидируют формат на входе (нельзя записать "999.999.999.999"), сортируются по числовому значению адреса и поддерживают специальные операторы: <<= ("входит ли адрес в сеть"), && ("пересекаются ли сети"), >> и т. д. Тип uuid (16 байт) хранит 128-битный идентификатор в бинарном виде, в два с лишним раза компактнее 36-символьного TEXT, лучше индексируется и имеет встроенный генератор gen_random_uuid() (из расширения pgcrypto, начиная с PG 13 — нативно).',
                'code_example' => 'CREATE TABLE audit (
    id BIGSERIAL PRIMARY KEY,
    user_id uuid DEFAULT gen_random_uuid(),
    ip      inet NOT NULL,
    mac     macaddr,
    network cidr
);

INSERT INTO audit (ip, mac, network) VALUES
    (\'192.168.1.42\', \'08:00:2b:01:02:03\', \'192.168.1.0/24\');

-- Найти запросы из определённой подсети
SELECT * FROM audit WHERE ip <<= \'192.168.1.0/24\';

-- Сравнить с TEXT: \'192.168.1.42\' длиной 12 байт vs inet 7 байт + операторы',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.postgresql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что делает WITH CHECK OPTION при создании VIEW в PostgreSQL?',
                'answer' => 'По умолчанию updatable VIEW позволяет INSERT и UPDATE строк, которые сразу же "выпадают" из условия WHERE этого VIEW — то есть видимы через таблицу, но не через сам VIEW. WITH CHECK OPTION включает проверку: любая INSERT/UPDATE через VIEW, после которого строка перестаёт удовлетворять WHERE, упадёт с ошибкой "new row violates check option". Это превращает VIEW в инструмент изоляции: например, выдали аналитику доступ только к VIEW active_users WHERE status = \'active\' — он не сможет UPDATE-ом перевести юзера в \'banned\' и "потерять" его из своего среза. Бывает двух уровней: LOCAL проверяет условие только текущего VIEW, CASCADED — ещё и условия всех нижележащих VIEW (если VIEW построен на другом VIEW). По умолчанию в PG — CASCADED.',
                'code_example' => 'CREATE TABLE users (id BIGINT PRIMARY KEY, status TEXT);
INSERT INTO users VALUES (1, \'active\'), (2, \'banned\');

CREATE VIEW active_users AS
    SELECT * FROM users WHERE status = \'active\'
    WITH CHECK OPTION;

-- Это пройдёт - строка остаётся active
UPDATE active_users SET status = \'active\' WHERE id = 1;

-- Это упадёт: ERROR: new row violates check option for view
UPDATE active_users SET status = \'banned\' WHERE id = 1;

-- INSERT, который сразу "выпадает" из VIEW - тоже запрещён
INSERT INTO active_users (id, status) VALUES (3, \'banned\'); -- ошибка',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.postgresql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что делает REFRESH MATERIALIZED VIEW CONCURRENTLY и зачем нужен уникальный индекс?',
                'answer' => 'Обычный REFRESH MATERIALIZED VIEW берёт ACCESS EXCLUSIVE lock и блокирует чтения на всё время пересборки. CONCURRENTLY делает refresh в стороне и применяет дельту через сравнение со старой копией, поэтому SELECT-ы продолжают работать без ожидания. Для этого PostgreSQL должен уметь сопоставлять строки между старой и новой версией, и требует, чтобы на матвью был хотя бы один UNIQUE-индекс. Платой становится более долгая и тяжёлая операция, поэтому CONCURRENTLY используют там, где важна доступность чтений.',
                'difficulty' => 4,
                'topic' => 'database.postgresql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что значит «расширяемость» PostgreSQL и при чём тут CREATE TYPE и CREATE EXTENSION?',
                'answer' => 'PostgreSQL спроектирован как объектно-реляционная СУБД, поэтому пользователь может добавлять собственные типы данных через CREATE TYPE (составные, диапазоны через CREATE TYPE ... AS RANGE, ENUM), функции на нескольких языках (PL/pgSQL, PL/Python), агрегаты, операторы и классы операторов для индексов. CREATE EXTENSION одной командой подключает готовые наборы такого функционала: PostGIS для геоданных, pg_trgm для нечёткого поиска, pgcrypto, hstore, uuid-ossp. Эта расширяемость — главное системное отличие PG от MySQL и причина, по которой PG выбирают под нестандартные домены.',
                'difficulty' => 4,
                'topic' => 'database.postgresql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Какие отличия между MySQL InnoDB и PostgreSQL практически важны для разработки?',
                'answer' => 'InnoDB: clustered primary index - данные физически отсортированы по PK, secondary indexes хранят PK как pointer. Postgres: heap-таблица + отдельные индексы, никакого clustering. InnoDB lock на gap для phantom; Postgres использует MVCC снапшоты. UPSERT: MySQL - ON DUPLICATE KEY UPDATE, Postgres - ON CONFLICT. Postgres имеет CTE, оконные с расширенным синтаксисом, jsonb, arrays, partial и expression indexes - MySQL это получил позже и беднее. КРИТИЧНО для DevOps и миграций: в PostgreSQL DDL-операции (CREATE/ALTER/DROP TABLE, CREATE INDEX и т.д.) ТРАНЗАКЦИОННЫ - можно обернуть BEGIN; ALTER TABLE...; ROLLBACK; и схема вернётся к исходному состоянию. Это позволяет: атомарно применять цепочку миграций (всё или ничего), безопасно тестировать миграции в транзакции с откатом, использовать SAVEPOINT для частичной отмены. В MySQL/MariaDB любая DDL-операция вызывает НЕЯВНЫЙ COMMIT текущей транзакции и сама не откатывается - если миграция упала на 5-м из 10 ALTER, первые 4 уже применены без возможности отката. Поэтому в MySQL миграции пишут осторожно, по одному изменению за релиз, с продумыванием rollback-стратегии. Исключение: CREATE INDEX CONCURRENTLY в Postgres - НЕ внутри транзакции (использует свой механизм неблокирующего создания, открывает несколько внутренних транзакций). SQL Server - DDL транзакционно (BEGIN TRAN; CREATE TABLE; ROLLBACK; - таблица не создастся), как в Postgres. Oracle, наоборот, делает implicit COMMIT и ДО, и ПОСЛЕ каждого DDL - CREATE/ALTER/DROP нельзя обернуть в транзакцию и откатить, поведение здесь ближе к MySQL, чем к Postgres.',
                'code_example' => '-- Postgres: partial index только для активных записей
CREATE INDEX idx_users_active_email ON users(email)
WHERE deleted_at IS NULL;
-- MySQL аналога нет - нужен FULL index',
                'code_language' => 'sql',
                'difficulty' => 4,
                'topic' => 'database.postgresql',
            ],
        ];
    }
}
