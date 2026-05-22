<?php

namespace Database\Seeders\Data\Categories\Database;

class Optimization
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Базы данных',
                'question' => 'Что такое Nested Loop, Hash Join, Merge Join?',
                'answer' => 'Три **физических алгоритма** соединения таблиц — оптимизатор выбирает один из них исходя из размеров входов, наличия индексов и сортировки.

| | **Nested Loop** | **Hash Join** | **Merge Join** |
|---|---|---|---|
| Идея | для каждой строки слева — поиск справа | строим hash-таблицу из правой, ищем в ней | merge двух отсортированных потоков |
| Стоимость | `O(N × M)` без индекса; `O(N × log M)` с индексом | `O(N + M)` + память на hash | `O(N + M)` + сортировка |
| Когда хорош | **N маленькое**, индекс по правой стороне | большие таблицы, **без подходящего индекса**, есть RAM | обе таблицы **уже отсортированы** по ключу JOIN |
| Память | минимальная | hash-таблица в RAM (`work_mem`) | минимальная (стримим) |
| Подходит для | OLTP, точечные JOIN | analytics, batch | merge-replication, sorted scans |

**Nested Loop:**
- классический алгоритм, **самый частый в OLTP**;
- идеален, когда **внешняя выборка маленькая** (`WHERE id = 5`) + индекс на ключе JOIN справа;
- катастрофичен, если внешний цикл — миллион строк без индекса справа (`O(N²)`).

**Hash Join:**
- **`build phase`** — читаем меньшую таблицу, строим hash-table в RAM по ключу JOIN;
- **`probe phase`** — стримим вторую таблицу, для каждой строки lookup в hash O(1);
- если хеш-таблица **не влезает в `work_mem`** — спил на диск (batch hash join);
- **MySQL 8.0.18+** наконец-то умеет hash join (раньше был только NL).

**Merge Join:**
- работает, если **обе стороны отсортированы** по ключу JOIN — обычно через `INDEX SCAN` по `B-tree`;
- идёт **двумя курсорами** слиянием, как в merge sort;
- если приходится **сортировать на лету** — обычно проигрывает Hash.

**На что смотреть в плане:**
- **MySQL `EXPLAIN`** — `Using join buffer (hash join)` в `Extra`;
- **PostgreSQL** — `Nested Loop` / `Hash Join` / `Merge Join` в верхушке плана;
- **тревожный сигнал** — `Nested Loop` с `Rows Removed by Filter` в десятках миллионов.',
                'difficulty' => 4,
                'topic' => 'database.optimization',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Почему слишком много индексов - это плохо?',
                'answer' => 'Индекс — это **не бесплатная «галочка ускорения»**, а **вторая структура данных**, которую БД обязана поддерживать.

**Цена каждого индекса:**
1. **Место на диске** — часто сопоставимое с самой таблицей при широких составных индексах;
2. **Замедление `INSERT`/`UPDATE`/`DELETE`** — при каждой модификации надо обновлять все индексы, по которым проходят изменённые колонки;
3. **Бэкапы и репликация растут** — `WAL`/`binlog` пухнут;
4. **Запутать планировщик** — если есть три похожих индекса, оптимизатор иногда выбирает не самый эффективный или дольше выбирает план.

**Правило:** индексируй **только реально частые запросы**, периодически удаляй неиспользуемые.

**Как найти неиспользуемые:**
- **PostgreSQL** — `pg_stat_user_indexes` (`idx_scan = 0` — кандидат на удаление);
- **MySQL** — `sys.schema_unused_indexes` / `performance_schema`.',
                'code_example' => '-- PostgreSQL: индексы, которыми ни разу не воспользовались
SELECT schemaname, relname, indexrelname, idx_scan, pg_size_pretty(pg_relation_size(indexrelid))
FROM pg_stat_user_indexes
WHERE idx_scan = 0
ORDER BY pg_relation_size(indexrelid) DESC;

-- MySQL: то же
SELECT object_schema, object_name, index_name
FROM sys.schema_unused_indexes;

-- Удаление лишнего индекса
DROP INDEX CONCURRENTLY orders_legacy_idx ON orders; -- PG, без блокировок',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.optimization',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'OFFSET vs Keyset (cursor) пагинация - что лучше?',
                'answer' => 'Два подхода к пагинации с принципиально разной стоимостью на глубине.

| | **OFFSET** | **Keyset (cursor)** |
|---|---|---|
| Запрос | `LIMIT 20 OFFSET 10000` | `WHERE id > last_id LIMIT 20` |
| Стоимость на глубине | **растёт** — БД сканирует и отбрасывает все 10 000 пропускаемых строк | **стабильна** — `O(log N + page_size)` |
| Переход на стр. 50 | да | **нельзя**, только next/prev |
| `ORDER BY` по неуникальному полю | работает | нужен **составной курсор** `(sort_col, id)` для tiebreaker |

**Когда что брать:**
- **OFFSET** — админка, маленькие наборы, нужно «перейти на страницу N»;
- **Keyset** — feed, бесконечная прокрутка, **большие таблицы**, мобильные API.

**Подводный камень keyset:** при `ORDER BY created_at DESC` две строки с одинаковым `created_at` могут **пропуститься или продублироваться** — поэтому всегда добавляют `id` в tiebreaker.',
                'code_example' => '-- OFFSET (медленно на глубине)
SELECT * FROM articles ORDER BY id LIMIT 20 OFFSET 10000;

-- Keyset (быстро всегда)
SELECT * FROM articles
WHERE id > 12345  -- last id с прошлой страницы
ORDER BY id
LIMIT 20;',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.optimization',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое N+1 проблема и как её решать?',
                'answer' => '**N+1** — проблема **ORM**, когда для получения N записей делается:
- **1 запрос** на список;
- **+ N запросов** на связанные данные.

**Пример:** 100 пользователей → `1 + 100 = 101` запрос вместо двух.

**Как обнаружить:**
- Laravel Debugbar, Telescope, Clockwork;
- `Model::preventLazyLoading()` в `AppServiceProvider::boot()` (бросит исключение в dev/test).

**Решения:**
- **eager loading** — `User::with(\'posts\')->get()` (Eloquent), `JOIN FETCH` (JPA), `Include` (EF);
- **JOIN-ы вручную** через query builder;
- **`withCount`** — если нужны только счётчики;
- **DataLoader** — для GraphQL (батчинг + кеш в рамках запроса).

**В Laravel:** `User::with(\'posts\')->get()` делает **2 запроса** вместо 101.',
                'code_example' => '// Плохо: N+1 (1 запрос users + N запросов posts)
$users = User::all();
foreach ($users as $user) {
    echo $user->posts->count(); // ленивая загрузка posts на каждой итерации
}

// Хорошо: eager loading (всего 2 запроса)
$users = User::with(\'posts\')->get();
foreach ($users as $user) {
    echo $user->posts->count(); // posts уже загружены, count() по коллекции
}

// Ещё лучше для счётчиков: withCount (один запрос с подзапросом)
$users = User::withCount(\'posts\')->get();
foreach ($users as $user) {
    echo $user->posts_count;
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'database.optimization',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Почему SELECT COUNT(*) без WHERE работает медленно в InnoDB и PostgreSQL, хотя в старом MyISAM был мгновенным?',
                'answer' => '**Старый `MyISAM`** хранил **точную цифру в метаданных таблицы** → `COUNT(*)` без `WHERE` возвращал её за **`O(1)`**.

**`InnoDB`** и **`PostgreSQL`** так **не могут из-за MVCC** — точное количество строк **зависит от snapshot транзакции**:

> Транзакция T1, стартовавшая в момент A, **видит одни строки**. T2, стартовавшая позже, — другие (часть удалена, часть добавлена). «Точное количество» — это **N разных цифр** для N snapshot-ов.

**Что происходит на самом деле:**
- БД должна **физически пройти** все строки и проверить **visibility** каждой относительно read view → **full scan**;
- в `InnoDB` с PK счёт делается по **самому компактному индексу** — всё равно линейный проход;
- в `PostgreSQL` ещё хуже — heap содержит **dead tuples** (удалённые, не очищенные `VACUUM`); **visibility map** иногда помогает, но при свежих изменениях устарела.

**Пять способов ускорить:**

| # | Способ | Точность | Когда применять |
|---|---|---|---|
| 1 | **`SELECT reltuples FROM pg_class WHERE relname = \'t\'`** | ~99% после `ANALYZE` | большая таблица, погрешность ОК |
| 2 | **Своя счётная таблица** + триггеры на `INSERT`/`DELETE` | точная | нужна гарантия + допустим оверхед записи |
| 3 | **`simplePaginate` / cursor-пагинация** без `COUNT` | n/a | UI-пагинация |
| 4 | **Кеш с TTL** (Redis) | устаревает | разрешена погрешность в N минут |
| 5 | `EXPLAIN` оценка (PG 16+ имеет лучшую) | ~ | разовый отчёт |

**Когда `COUNT(*)` ещё ОК:**
- **селективный `WHERE`** по индексу (`WHERE user_id = 42`);
- маленькие таблицы (`< 100K`).

**Антипаттерн** в админке на 100M-таблице:
> «Найдено **12 345 678** записей» — полный скан **на каждый клик** пагинации.',
                'code_example' => '-- ❌ Медленно на больших таблицах
SELECT COUNT(*) FROM events; -- full scan / index scan, O(N)

-- ✅ Быстрая приблизительная оценка в Postgres
SELECT reltuples::bigint AS approx_count
FROM pg_class WHERE relname = \'events\';
-- ~99% точность после свежего ANALYZE, мгновенно

-- ✅ Точная цифра без COUNT(*) - своя счётная таблица
CREATE TABLE table_counts (table_name TEXT PRIMARY KEY, n BIGINT NOT NULL);

CREATE OR REPLACE FUNCTION events_count_trigger()
RETURNS TRIGGER LANGUAGE plpgsql AS $body$
BEGIN
    IF TG_OP = \'INSERT\' THEN
        UPDATE table_counts SET n = n + 1 WHERE table_name = \'events\';
    ELSIF TG_OP = \'DELETE\' THEN
        UPDATE table_counts SET n = n - 1 WHERE table_name = \'events\';
    END IF;
    RETURN NULL;
END $body$;

CREATE TRIGGER events_count_trg
    AFTER INSERT OR DELETE ON events
    FOR EACH ROW EXECUTE FUNCTION events_count_trigger();

-- ✅ COUNT(*) с селективным WHERE - быстро
SELECT COUNT(*) FROM events WHERE user_id = 42; -- индекс по user_id

-- ❌ Анти-паттерн в API
-- { "page": 1, "data": [...], "total": 12345678 }
--                                ^ COUNT(*) на каждый запрос

-- ✅ Cursor pagination без COUNT
-- { "data": [...], "next_cursor": "eyJpZCI6MTIzNDV9" }',
                'code_language' => 'sql',
                'difficulty' => 4,
                'topic' => 'database.optimization',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Как читать EXPLAIN: чем отличается Seq Scan от Index Scan, и почему LIMIT иногда заставляет оптимизатор отказаться от индекса?',
                'answer' => '**Базовые типы операций в плане:**

| Операция | PG | MySQL | Что делает | Когда выбирается |
|---|---|---|---|---|
| **Seq Scan** | `Seq Scan` | `type=ALL` | полное чтение таблицы строка за строкой | нужно **>10-30%** строк — последовательный I/O дешевле рандомных seek-ов |
| **Index Scan** | `Index Scan` | `type=ref/range` | спуск по B-tree + dereference TID к heap | **селективный** фильтр (1-5% строк) |
| **Index-Only Scan** | `Index Only Scan` | `Using index` | данные **из самого индекса**, без heap | **covering index** покрывает все нужные колонки |
| **Bitmap Scan** | `Bitmap Heap Scan` | (нет аналога) | собрать bitmap позиций + один проход по heap | много несмежных строк, средняя селективность |

**Если выбран `Seq Scan` на большой таблице с селективным WHERE** — **тревожный сигнал**:
- либо нет подходящего **индекса**;
- либо он есть, **но не используется** (`WHERE LOWER(email) = ...` без функционального индекса; неявное приведение типов; `LIKE \'%abc%\'`).

**Важное явление — `LIMIT` меняет план («abort early» pattern):**

```sql
-- Без LIMIT
SELECT * FROM orders WHERE user_id = 5 ORDER BY created_at DESC;
-- → Index Scan(user_id) + Sort

-- С LIMIT 10 оптимизатор может пойти иначе:
SELECT * FROM orders WHERE user_id = 5 ORDER BY created_at DESC LIMIT 10;
-- → Index Scan по (created_at DESC), читаем по одной, фильтруем user_id,
--   пока не наберём 10
```

**Когда работает плохо:** если `user_id=5` заказывал **год назад**, оптимизатор **переберёт весь индекс впустую**, отбрасывая не подходящих — получится медленнее, чем без `LIMIT`.

**Узнаётся по плану:** `Limit` над `Index Scan` + `Rows Removed by Filter` в сотни тысяч.

**Как чинить:**
1. **`ANALYZE`** — обновить статистику;
2. **Force index hint** — `USE INDEX (orders_user_id_idx)` в MySQL;
3. Переписать как **`WHERE id IN (subquery с LIMIT по нужному индексу)`**;
4. Добавить **составной индекс** `(user_id, created_at DESC)` — тогда оптимизатор сразу видит «копеечный» путь.',
                'code_example' => '-- Postgres: читать вывод EXPLAIN ANALYZE сверху вниз
EXPLAIN (ANALYZE, BUFFERS) SELECT * FROM users WHERE email = ?;
-- Index Scan using users_email_idx on users  (cost=0.42..8.44 rows=1)
--   Index Cond: (email = $1)
--   Buffers: shared hit=4
--   Planning Time: 0.1 ms / Execution Time: 0.05 ms

-- Полный скан (плохо, если нужно мало строк)
EXPLAIN ANALYZE SELECT * FROM users WHERE last_name = "Иванов";
-- Seq Scan on users  (cost=0.00..18334 rows=12 width=...)
--   Filter: (last_name = "Иванов")
--   Rows Removed by Filter: 99988
-- ⚠ нужно добавить индекс на last_name

-- LIMIT обычно ускоряет, но иногда хуже
EXPLAIN ANALYZE
SELECT * FROM orders WHERE user_id = 5 ORDER BY created_at DESC LIMIT 10;
-- Limit  (cost=0..123 rows=10)
--   ->  Index Scan Backward using orders_created_at_idx
--       Filter: (user_id = 5)
--       Rows Removed by Filter: 540000   ← ПРОБЛЕМА: пробежал полтаблицы
-- Решение: составной индекс (user_id, created_at DESC) - "abort early" будет дешевым

-- MySQL аналог
EXPLAIN FORMAT=TREE SELECT * FROM users WHERE email = ?;
-- Чему смотреть: type (const/eq_ref < ref < range << ALL),
-- key (какой индекс), rows (оценка), Extra (Using index / Using filesort / Using temporary)',
                'code_language' => 'sql',
                'difficulty' => 4,
                'topic' => 'database.optimization',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'На какие столбцы вывода EXPLAIN в MySQL стоит смотреть в первую очередь?',
                'answer' => 'Главный столбец — **`type`** (тип доступа), упорядочен **от лучшего к худшему**:

| `type` | Что значит |
|---|---|
| `system`, `const` | одна строка по `PK` |
| `eq_ref`, `ref` | точечный доступ по индексу при `JOIN` |
| `range` | диапазон по индексу |
| `index` | полный скан **индекса** |
| `ALL` | полный скан **таблицы** — **красный флаг** на большой таблице |

**Остальные ключевые столбцы:**
- **`key`** — какой индекс реально выбран (`NULL` — индекс не использован);
- **`key_len`** — сколько байтов составного индекса задействовано (понятно, сколько колонок реально работают);
- **`rows`** — оценка прочитанных строк;
- **`filtered`** — процент строк, оставшихся после `WHERE`.

**`Extra` — подсказки:**
- **`Using filesort`** — сортировка без индекса; добавь индекс под `ORDER BY`;
- **`Using temporary`** — внутренняя временная таблица под `GROUP BY`/`DISTINCT`/`UNION`;
- **`Using index`** — **covering index**, отлично;
- **`Using where`** — `WHERE` применён после чтения, норма.',
                'code_example' => 'EXPLAIN SELECT id, email FROM users WHERE email = \'a@b.c\';
-- id | select_type | table | type | key             | key_len | rows | Extra
--  1 | SIMPLE      | users | ref  | users_email_idx | 767     |    1 | Using index

-- Плохой пример: type=ALL, нет ключа
EXPLAIN SELECT * FROM orders WHERE LOWER(email) = \'a@b.c\';
-- ... | type=ALL | key=NULL | rows=1000000 | Using where
-- Решение: индекс на выражение LOWER(email) или нормализовать данные

-- Современный формат
EXPLAIN FORMAT=TREE SELECT ...;
EXPLAIN ANALYZE  SELECT ...; -- MySQL 8.0.18+, реальные времена',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.optimization',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что значат Using filesort и Using temporary в выводе EXPLAIN?',
                'answer' => 'Два маркера в колонке **`Extra`** вывода `EXPLAIN` MySQL — **обычно кандидаты на оптимизацию**.

| Маркер | Что происходит |
|---|---|
| **`Using filesort`** | **не файл!** — отдельный проход для сортировки результата, потому что **нет индекса под `ORDER BY`**. При маленьких наборах — в памяти (`sort_buffer_size`), при больших — на диск |
| **`Using temporary`** | MySQL создал **внутреннюю temporary table** (сначала в RAM, потом на диск при переполнении `tmp_table_size`/`max_heap_table_size`) для `GROUP BY`, `DISTINCT`, `UNION`, derived tables, оконных функций |

**`Using filesort` — что делать:**
- добавить **составной индекс**, покрывающий `WHERE` + `ORDER BY`:
  - `WHERE user_id = ? ORDER BY created_at DESC` → индекс `(user_id, created_at)`;
- проверить, что **порядок колонок** в индексе совпадает с `ORDER BY`;
- ослабить требования к сортировке (если бизнес позволяет);
- увеличить `sort_buffer_size` — поможет только маленьким наборам.

**`Using temporary` — что делать:**
- переписать так, чтобы группировка шла по **индексированному префиксу** (`GROUP BY user_id` при индексе `(user_id, ...)`);
- избавиться от `DISTINCT` (часто это симптом лишних `JOIN`);
- `UNION ALL` вместо `UNION` (не нужна dedup);
- в MySQL 8+ window functions иногда дешевле `GROUP BY` для аналитики.

**Желательный маркер — `Using index`** (covering index): данные взяты **прямо из индекса**, к таблице не лезли.

**В PostgreSQL аналоги:** `Sort` (= filesort), `HashAggregate` / `GroupAggregate` (= temporary под GROUP BY).',
                'code_example' => '-- Без индекса под ORDER BY → Using filesort
EXPLAIN SELECT * FROM orders WHERE user_id = 5 ORDER BY created_at DESC;
-- type=ref, Extra: Using filesort

-- С составным индексом → нет filesort
CREATE INDEX idx_user_created ON orders (user_id, created_at DESC);
EXPLAIN SELECT * FROM orders WHERE user_id = 5 ORDER BY created_at DESC;
-- type=ref, Extra: (пусто или Using index condition)

-- GROUP BY → Using temporary
EXPLAIN SELECT category_id, COUNT(*) FROM products GROUP BY brand;
-- Extra: Using temporary; Using filesort

-- С правильным индексом GROUP BY идёт без temporary
CREATE INDEX idx_brand ON products (brand);
EXPLAIN SELECT brand, COUNT(*) FROM products GROUP BY brand;',
                'code_language' => 'sql',
                'difficulty' => 4,
                'topic' => 'database.optimization',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое slow query log и как им пользоваться?',
                'answer' => '**Slow query log** — встроенный журнал MySQL, куда пишутся все запросы, выполнявшиеся **дольше порога** `long_query_time`.

**Настройки:**
- `slow_query_log = 1` — включить;
- `long_query_time = 0.1` — порог (по умолчанию 10 сек; на проде ставят **0.1–1 с**, иначе ничего не поймаешь);
- `slow_query_log_file = /path/file.log`;
- `log_queries_not_using_indexes` — ловит запросы **без индекса**, даже если они быстрые сейчас (вырастут с объёмом данных).

**Как анализировать:**
- **не глазами**, а утилитами: **`pt-query-digest`** (Percona Toolkit) или `mysqldumpslow`;
- они группируют по **нормализованной форме** и сортируют по **суммарному времени**.

**Правильная метрика — `total time`:** частый «быстрый» запрос на **50 мс × 10 000 раз/мин** страшнее одного на **5 сек**.

**Аналог в PostgreSQL:** `log_min_duration_statement` + расширение **`pg_stat_statements`**.

**Это первый инструмент** при жалобах на тормоза, **до** подключения APM.',
                'code_example' => '-- Включить slow log на лету (MySQL)
SET GLOBAL slow_query_log = ON;
SET GLOBAL long_query_time = 0.2; -- 200 мс
SET GLOBAL log_queries_not_using_indexes = ON;

-- Постоянно — в my.cnf
-- slow_query_log = 1
-- long_query_time = 0.2
-- slow_query_log_file = /var/log/mysql/slow.log

-- Анализ лога (bash)
-- pt-query-digest /var/log/mysql/slow.log | head -100

-- PostgreSQL аналог: pg_stat_statements
SELECT query, calls, total_exec_time, mean_exec_time
FROM pg_stat_statements ORDER BY total_exec_time DESC LIMIT 20;',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.optimization',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Зачем нужен ANALYZE TABLE и чем он отличается от EXPLAIN?',
                'answer' => 'Это **разные команды**, которые часто путают.

| Команда | Что делает |
|---|---|
| `ANALYZE TABLE` | **пересчитывает статистику** распределения значений: cardinality, гистограммы, плотность ключей |
| `EXPLAIN` | **только показывает план** для запроса на основе уже имеющейся статистики |
| `EXPLAIN ANALYZE` (MySQL 8.0.18+, PG) | выполняет запрос и показывает **фактические `rows`/`time` рядом с оценками** |

**На статистике** оптимизатор строит **cost-модель** — решает, какой индекс брать, в каком порядке соединять таблицы, делать ли `Hash Join` или `Nested Loop`.

**Симптом устаревшей статистики:** после массовой загрузки, большого `DELETE` или `TRUNCATE+INSERT` планы становятся «тупыми» — оптимизатор берёт `ALL` вместо очевидного индекса. Лечится **`ANALYZE TABLE`**.

**Автоматика:** PostgreSQL запускает `ANALYZE` через **autovacuum**, но после большой загрузки часто **запускают вручную**.',
                'code_example' => '-- Обновить статистику
ANALYZE TABLE orders;                 -- MySQL
ANALYZE orders;                       -- PostgreSQL
ANALYZE VERBOSE orders;               -- PG, с прогрессом

-- Сравнить план до и после
EXPLAIN SELECT * FROM orders WHERE user_id = 42; -- может быть ALL
ANALYZE TABLE orders;
EXPLAIN SELECT * FROM orders WHERE user_id = 42; -- ожидаем ref/Index Scan

-- Проверить расхождение оценок и факта (PG)
EXPLAIN (ANALYZE) SELECT ... ;
-- Plan rows=1 vs Actual rows=500000 → нужен ANALYZE',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.optimization',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Как читать вывод EXPLAIN ANALYZE в PostgreSQL и какие признаки плохого плана?',
                'answer' => '`EXPLAIN` показывает **план**; **`EXPLAIN ANALYZE`** **реально выполняет** запрос и добавляет фактические **`actual time`**, **`rows`**, **`loops`** рядом с оценками планировщика.

**Полезные опции:**
- **`BUFFERS`** — сколько страниц прочитано из buffer pool (`shared hit`) vs с диска (`shared read`);
- **`VERBOSE`** — раскрыть все выражения;
- **`SETTINGS`** — что повлияло на план;
- **`FORMAT JSON`** — для парсинга в инструменты (`explain.depesz.com`, `tatiyants.com`).

**Тревожные признаки в плане:**

| Симптом | Что значит | Лечение |
|---|---|---|
| **`Seq Scan`** на большой таблице с селективным `WHERE` | нет индекса или он не используется | создать индекс / переписать предикат |
| **`Plan rows` ≠ `actual rows`** (разница в 100× и больше) | устаревшая статистика | **`ANALYZE`** таблицу, увеличить `default_statistics_target` |
| **`Nested Loop`** с большим внешним циклом | оптимизатор недооценил размер | `ANALYZE`; иногда форсируют hash через `SET enable_nestloop = off` |
| **`Sort Method: external merge Disk: …kB`** | `work_mem` мал, sort пошёл на диск | увеличить `work_mem` для сессии |
| **`Bitmap Heap Scan`** + `Recheck Cond` с большими `lossy` | bitmap не помещается в RAM | увеличить `work_mem` |
| **`Rows Removed by Filter`** в миллионах | предикат не индексируется | добавить **функциональный** или **partial** индекс |

**Универсальное правило:** читать план **снизу вверх** (исполняется так), а **смотреть на `actual time` каждого узла** — самый дорогой и есть bottleneck.',
                'code_example' => '-- Базовый EXPLAIN ANALYZE с BUFFERS
EXPLAIN (ANALYZE, BUFFERS, VERBOSE)
SELECT u.id, COUNT(o.id)
FROM users u JOIN orders o ON o.user_id = u.id
WHERE u.created_at > NOW() - INTERVAL \'30 days\'
GROUP BY u.id;

-- HashAggregate (cost=10000.0..10500.0 rows=5000 width=12)
--                (actual time=120.5..125.3 rows=4870 loops=1)
--   Group Key: u.id
--   Buffers: shared hit=15000 read=300
--   -> Hash Join (actual time=10..100 rows=50000)
--        Hash Cond: (o.user_id = u.id)
--        -> Seq Scan on orders o
--        -> Hash
--             -> Index Scan using users_created_at_idx on users u
--                  Index Cond: (created_at > now() - \'30 days\')
-- Planning Time: 0.5 ms
-- Execution Time: 126.0 ms

-- Бывает важно: SET LOCAL work_mem = \'256MB\'; перед запросом',
                'code_language' => 'sql',
                'difficulty' => 4,
                'topic' => 'database.optimization',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Как устроен оптимизатор запросов и что такое статистики?',
                'answer' => 'Современные SQL-оптимизаторы — **cost-based (CBO)**: перебирают возможные **планы выполнения**, оценивают **стоимость** каждого через формулы (`cost = CPU + I/O + ...`), выбирают **самый дешёвый**.

**Этапы работы оптимизатора:**
1. **Parse + bind** — синтаксический разбор, разрешение имён;
2. **Rewrite** — алгебраические преобразования (выталкивание предикатов, упрощение `JOIN`, view inlining);
3. **Plan enumeration** — перебор порядка `JOIN`, выбор алгоритмов (Nested Loop / Hash / Merge), стратегии доступа (Seq Scan / Index Scan);
4. **Cost estimation** — оценка цены каждого плана **на основе статистик**;
5. **Execution** — выполняется выбранный план.

**Что такое статистики:**

| Метаданные | Что хранит | Использование |
|---|---|---|
| **`n_distinct`** | количество уникальных значений в колонке | оценка selectivity `WHERE col = ?` |
| **`MCV` (most common values)** | топ N частых значений + частоты | точная оценка selectivity для «горячих» значений |
| **Гистограмма** | распределение значений по бакетам | range-предикаты `WHERE col BETWEEN ...` |
| **`null_frac`** | доля NULL | `IS NULL` selectivity |
| **`correlation`** | связь логического и физического порядка | дешевизна Index Scan |

**В PostgreSQL** — `pg_statistic` (видимый view `pg_stats`), обновляется **`ANALYZE`** (или фоновым autovacuum).

**Когда план «кривой»:**
- **устаревшая статистика** — после bulk-load, большого `DELETE` → план берёт ALL вместо индекса;
- **коррелированные предикаты** — оптимизатор по умолчанию считает колонки независимыми. `WHERE city = \'Moscow\' AND country = \'Russia\'` → перемножает selectivity, недооценивая;
- **skew** — гистограмма не покрывает реальное распределение.

**Лечение:**
- **`ANALYZE table;`** после массовых изменений;
- **`SET default_statistics_target = 1000;`** — больше бакетов в гистограмме (стоит память и время `ANALYZE`);
- **`CREATE STATISTICS`** для **multivariate-зависимостей** (PG 10+):',
                'code_example' => '-- 1. Базовый ANALYZE
ANALYZE orders;
ANALYZE VERBOSE orders;  -- с прогрессом

-- 2. Увеличить разрешение гистограммы для критичной колонки
ALTER TABLE orders ALTER COLUMN user_id SET STATISTICS 1000;
ANALYZE orders (user_id);

-- 3. Multivariate statistics для коррелированных колонок (PG 10+)
CREATE STATISTICS orders_corr (dependencies, ndistinct)
ON status, payment_method FROM orders;
ANALYZE orders;

-- 4. Посмотреть, что планировщик "видит"
SELECT * FROM pg_stats WHERE tablename = \'orders\' AND attname = \'user_id\';
-- n_distinct | most_common_vals | most_common_freqs | histogram_bounds

-- 5. Сравнить оценку и реальность
EXPLAIN (ANALYZE) SELECT * FROM orders WHERE user_id = 42;
-- если Plan rows=1 vs Actual rows=500000 → нужен ANALYZE или статистика плохая',
                'code_language' => 'sql',
                'difficulty' => 5,
                'topic' => 'database.optimization',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое медленный запрос простыми словами?',
                'answer' => '**Медленный запрос** — это SQL-запрос, который выполняется **дольше «нормы»**. Норма зависит от проекта, но обычно порог — **100-500 мс**: всё, что дольше — повод разобраться.

**Типичные причины:**
- на колонке из `WHERE` **нет индекса** — БД сканирует всю таблицу;
- `JOIN` большой таблицы **без индекса** по полю связи;
- `ORDER BY` без подходящего индекса — БД сортирует «на лету»;
- запрос возвращает **огромное количество строк**, которые приложению не нужны (нет `LIMIT`).

**Как ловить:** в **MySQL** и **PostgreSQL** включают **slow query log** — БД сама пишет в файл все запросы, превысившие заданное время.',
                'code_example' => '-- MySQL: включить slow log на лету и поймать всё дольше 200 мс
SET GLOBAL slow_query_log = ON;
SET GLOBAL long_query_time = 0.2;
SET GLOBAL slow_query_log_file = \'/var/log/mysql/slow.log\';

-- PostgreSQL: писать в лог запросы дольше 200 мс
-- (в postgresql.conf)
-- log_min_duration_statement = 200',
                'code_language' => 'sql',
                'difficulty' => 1,
                'topic' => 'database.optimization',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое EXPLAIN простыми словами?',
                'answer' => '**`EXPLAIN`** показывает **план выполнения** запроса — что именно БД собирается делать.

**Что видно в плане:**
- какой **индекс** будет использован (или не будет);
- **сколько строк** планировщик ожидает прочесть;
- **порядок соединения** таблиц;
- **тип доступа**: `Index Scan` / `Seq Scan` / `Nested Loop` / `Hash Join`.

Это **первый инструмент**, когда запрос «тормозит» — сразу видно, идёт ли `Seq Scan` по миллиону строк вместо `Index Scan`.

**Два режима:**
- **`EXPLAIN`** — только оценка, запрос не выполняется;
- **`EXPLAIN ANALYZE`** — **реально запускает** запрос и добавляет **фактическое время и строки**.

**На что смотреть:**
- в **MySQL** — колонки `type`, `key`, `rows`, `Extra`;
- в **PostgreSQL** — тип узла, `cost`, `actual time`, `rows`.',
                'code_example' => '-- PostgreSQL
EXPLAIN ANALYZE
SELECT * FROM users WHERE email = \'ivan@mail.ru\';
-- Index Scan using users_email_idx on users
--   Index Cond: (email = \'ivan@mail.ru\')

-- MySQL
EXPLAIN SELECT * FROM users WHERE email = \'ivan@mail.ru\';
-- type=ref, key=users_email_idx, rows=1',
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.optimization',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Как понять, что запрос медленный простыми словами?',
                'answer' => 'Несколько практичных способов — от продакшена к локальной отладке:

1. **Slow query log БД** — все запросы дольше порога пишутся в файл.
   - MySQL: `slow_query_log = ON`, `long_query_time = 0.2`;
   - PostgreSQL: `log_min_duration_statement = 200` (мс).
2. **APM** — Laravel Telescope, Datadog, Sentry, New Relic. Покажут **топ-N медленных запросов** в проде.
3. **Локально** — Laravel **Debugbar** или **Telescope** показывают время **каждого** запроса прямо в браузере.
4. **`EXPLAIN ANALYZE`** на подозрительном запросе — посмотреть план и реальное время.
5. **`pg_stat_statements`** (PostgreSQL) — встроенная агрегированная статистика: средний и суммарный time per query.

**Совет:** смотри **суммарное время × частота**, а не только разовые «тормоза». Запрос на 50 мс, вызываемый 10 000 раз/мин, страшнее одного запроса на 5 секунд.',
                'difficulty' => 2,
                'topic' => 'database.optimization',
            ],
        ];
    }
}
