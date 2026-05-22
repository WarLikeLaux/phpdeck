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
                'answer' => 'Два типа для хранения JSON — один **текстовый**, другой **бинарный**.

| | `json` | `jsonb` |
|---|---|---|
| Хранение | как **текст** (сохраняет пробелы и порядок ключей) | **бинарный** формат |
| Парсинг | при **каждом** обращении | один раз при вставке |
| Запись | быстрее | чуть медленнее (преобразование) |
| Операторы | базовые | `@>`, `?`, `#>>`, `->`, `->>` |
| Индексы | ограниченно | **`GIN`-индекс** |

**Правило:** используй **`jsonb`**, кроме редкого случая — нужно **сохранить сырое представление** (логирование, ретрансляция чужого JSON).',
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
                'answer' => 'PostgreSQL поддерживает **массивы любых типов** как нативные значения столбцов: `TEXT[]`, `INT[]`, `JSONB[]` и т. д.

**Возможности:**
- хранение без отдельной таблицы;
- поиск через `ANY(...)`, `@>` (contains), `&&` (overlap);
- индексация **`GIN`** для быстрого поиска по элементам.

**Когда уместно** — однородные **простые списки**: теги, роли, права.

**Когда лучше нормализованная таблица:**
- элементам нужны **свои атрибуты** (timestamp когда добавлен, кто добавил);
- нужны **`FOREIGN KEY`** и каскады;
- сложная фильтрация / агрегаты по элементам;
- список **большой и часто меняется** (UPDATE массива переписывает всю строку).',
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
                'answer' => 'Из-за **MVCC** в PostgreSQL `UPDATE`/`DELETE` **не удаляют строки физически**, а создают новые версии и помечают старые как **dead tuples**.

**Термины:**
- **`VACUUM`** — фоновая очистка, **освобождает место в страницах** от dead tuples (но не возвращает место ОС — для этого `VACUUM FULL`);
- **`autovacuum`** — демон, запускающий `VACUUM` **автоматически** по порогам (`autovacuum_vacuum_threshold`, `autovacuum_vacuum_scale_factor`);
- **`bloat`** — **раздутие** таблицы/индекса от dead tuples; замедляет seq scan и индексы, увеличивает I/O;
- **`ANALYZE`** — обновляет **статистику** для оптимизатора (часто идёт вместе с `VACUUM`).

**Команды:**

| Команда | Что делает | Локи |
|---|---|---|
| `VACUUM table` | освобождает место **в страницах** | не блокирует чтения/записи |
| `VACUUM ANALYZE table` | + обновление статистики | не блокирует |
| `VACUUM FULL table` | **полностью переписывает** таблицу, возвращает место ОС | **`ACCESS EXCLUSIVE`** — блокирует всё |
| `pg_repack` (extension) | как `VACUUM FULL`, но **без блокировки** | минимальный лок |

**Когда `autovacuum` не справляется:**
- очень частые `UPDATE` горячей таблицы (например, счётчик в одной строке);
- **долгие транзакции** — удерживают xmin, и dead tuples нельзя очистить;
- **`idle in transaction`** — забытые `BEGIN` блокируют очистку.

**Диагностика:**

```sql
-- Сколько dead tuples и когда был последний autovacuum
SELECT relname, n_live_tup, n_dead_tup,
       last_autovacuum, last_vacuum
FROM pg_stat_user_tables
ORDER BY n_dead_tup DESC;
```

**HOT updates** — если `UPDATE` не меняет индексируемые колонки и в странице есть место, новая версия пишется **в ту же страницу** без обновления индексов — bloat **меньше**.',
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
                'answer' => '**Materialized View** — представление, чей результат **физически сохранён на диске**, в отличие от обычного `VIEW` (вычисляется при каждом обращении).

| | `VIEW` | `MATERIALIZED VIEW` |
|---|---|---|
| Хранение | только запрос | результат **на диске** |
| Чтение | пересчитывается каждый раз | как обычная таблица — **быстро** |
| Актуальность | всегда свежий | **устаревает**, нужно `REFRESH` |
| Индексы | нет | **можно строить** |

**Когда брать:** тяжёлые **аналитические запросы** — посчитал один раз, читаешь много раз быстро (дневные агрегаты продаж, отчёты).

**Подводные камни:**
- данные **не свежие** — нужен расписанный `REFRESH`;
- обычный `REFRESH` берёт `ACCESS EXCLUSIVE` — блокирует чтения;
- `REFRESH ... CONCURRENTLY` не блокирует, но требует **`UNIQUE`-индекс** на матвью.',
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
                'answer' => 'Три **оконные функции (window functions)**, которые нумеруют строки по сортировке внутри окна (`ORDER BY` в `OVER`).

**Разница — в поведении при равных значениях:**

| Функция | При равных значениях | Пример: salary [100, 90, 90, 80] |
|---|---|---|
| **`ROW_NUMBER()`** | всегда уникальные | **1, 2, 3, 4** |
| **`RANK()`** | одинаковый ранг, потом **пропуск** | **1, 2, 2, 4** |
| **`DENSE_RANK()`** | одинаковый ранг, **без пропуска** | **1, 2, 2, 3** |

**Что выбирать:**
- **`ROW_NUMBER`** — нужна **уникальная** нумерация (топ-N, dedup, пагинация);
- **`RANK`** — олимпийская модель: «два золотых места, серебра нет»;
- **`DENSE_RANK`** — нужна **плотная шкала** уровней без дырок (грейды зарплат).

**Типичное применение:**
- **топ-N в группе** — `ROW_NUMBER() OVER (PARTITION BY user_id ORDER BY created_at DESC)` + `WHERE rn <= 3`;
- **дедупликация** — оставить только одну строку с каждым `email` — `ROW_NUMBER() ... + WHERE rn = 1`;
- **процентили** — `PERCENT_RANK()`, `NTILE(100)` — родственные функции.

**Грабли:**
- без `ORDER BY` в `OVER` — порядок **недетерминирован**, нумерация бесполезна;
- результат окна **нельзя использовать** в `WHERE` той же выборки — нужна обёртка через `CTE` или подзапрос.',
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
                'answer' => 'Оконные функции для **сравнения с соседними строками**.

| Функция | Что возвращает |
|---|---|
| **`LAG(col, n, default)`** | значение `col` в строке на **`n` позиций РАНЬШЕ** (по `ORDER BY` окна) |
| **`LEAD(col, n, default)`** | значение `col` в строке на **`n` позиций ПОЗЖЕ** |
| **`FIRST_VALUE(col)`** | значение в **первой** строке окна |
| **`LAST_VALUE(col)`** | значение в **последней** строке окна |

**Параметры `LAG`/`LEAD`:**
- `col` — какое поле взять;
- `n` (по умолчанию `1`) — на сколько строк сдвинуться;
- `default` — что вернуть, если такой строки нет (для крайних строк).

**Типичное применение:**
- **дельта** между периодами — `sales - LAG(sales) OVER (ORDER BY day)`;
- **процент роста** — `(sales / LAG(sales) OVER (...)) - 1`;
- **детект изменений состояния** — `WHERE status <> LAG(status) OVER (PARTITION BY user_id ORDER BY ts)`;
- **gap-detection** — найти разрывы в последовательности.

**С `PARTITION BY`** — сдвиг работает **внутри группы**, не вылезая за её границы (по умолчанию `LAG` вернёт `NULL` для первой строки группы).',
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
                'answer' => 'Расширения `GROUP BY` для **агрегации по нескольким уровням за один запрос**. Часто используются в **OLAP** и аналитических отчётах.

| Конструкция | Что генерирует |
|---|---|
| **`GROUPING SETS ((a, b), (a), ())`** | **явные** комбинации группировки |
| **`ROLLUP(a, b)`** | **иерархические** подытоги: `(a, b)`, `(a)`, `()` |
| **`CUBE(a, b)`** | **все** комбинации: `(a, b)`, `(a)`, `(b)`, `()` |

**Где `()` — общий итог** (`GRAND TOTAL`).

**`ROLLUP` vs `CUBE`:**
- **`ROLLUP`** — для **иерархии** (год → месяц → день): «итог за день, за месяц, за год, за всё»;
- **`CUBE`** — для **независимых измерений** (страна × продукт): «по каждой паре, по странам, по продуктам, всего».

**Альтернатива без расширений** — `UNION ALL` нескольких `GROUP BY`, но это:
- читает таблицу **столько раз, сколько группировок** — медленно;
- `ROLLUP`/`CUBE` сканируют **один раз** и считают всё параллельно — намного эффективнее.

**Полезный спутник** — функция **`GROUPING(col)`** возвращает `1` для итоговых строк (`NULL` в `col` — это «всё»), `0` для обычных. Используется, чтобы отличать `NULL` от «итог по группе» в выводе.',
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
                'answer' => 'Два способа репликации в PostgreSQL — **физический** и **логический**.

| | **Streaming (физическая)** | **Logical (логическая)** |
|---|---|---|
| Что передаётся | **`WAL`** — изменения страниц на уровне блоков | изменения **по таблицам** через decode плагин (`pgoutput`) |
| Схема реплики | **точная копия** master | **любая** — можно отличаться |
| Версия PG | **одинаковая** на master/replica | можно **разные** major-версии (полезно для **zero-downtime upgrade**) |
| Селективность | **вся БД** | **выборочно** — конкретные таблицы через `PUBLICATION`/`SUBSCRIPTION` |
| Запись на реплике | **только чтение** (hot standby) | **возможна** — это просто другой кластер |
| Применение | **failover**, **read-replica** | **CDC**, миграции версий, шардинг, ETL |

**Streaming replication — режимы:**
- **asynchronous** (по умолчанию) — `COMMIT` на master не ждёт реплику, минимальная задержка, **возможна потеря** последних транзакций при падении;
- **synchronous** — `COMMIT` ждёт подтверждения от реплики, **нет потерь**, но выше latency;
- **`synchronous_commit = remote_apply`** — ждать применения, не просто получения.

**Logical replication — типичные применения:**
1. **Zero-downtime upgrade** PG 14 → 16 — поднять PG 16, подписаться logical, переключить трафик;
2. **CDC** (Change Data Capture) — `Debezium` через logical decoding шлёт изменения в Kafka;
3. **Шардинг** — разные таблицы на разных кластерах;
4. **Read-replica с другой схемой** — отчёты на отдельной БД.

**Грабли logical replication:**
- **не реплицирует DDL** — `ALTER TABLE` нужно прокатывать вручную;
- **не реплицирует sequences** — `nextval` будут расходиться;
- **большие транзакции** до PG 14 буферизовались целиком (PG 14+ — streaming, передаются по мере выполнения).',
                'difficulty' => 5,
                'topic' => 'database.postgresql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое Full-Text Search в PostgreSQL?',
                'answer' => '**Full-Text Search (FTS)** в PostgreSQL — встроенный полнотекстовый поиск через два типа:

| Тип | Что хранит |
|---|---|
| **`tsvector`** | предобработанный документ — **лексемы** + позиции, после стемминга и удаления стоп-слов |
| **`tsquery`** | запрос с **операторами** — `&` (AND), `|` (OR), `!` (NOT), `<->` (followed by) |

**Возможности:**
- **стемминг** — приведение слов к основе (`лошадей` → `лошадь`);
- **стоп-слова** — игнор служебных слов (`и`, `в`, `the`, `of`);
- **ранжирование** — `ts_rank()`, `ts_rank_cd()`;
- **разные языки** через configurations (`russian`, `english`, `simple`);
- **подсветка** — `ts_headline()`.

**Индексация — через `GIN`:**

```sql
-- На лету при поиске (медленно для больших таблиц)
CREATE INDEX idx_articles_tsv
  ON articles USING gin (to_tsvector(\'russian\', body));

-- Лучше - материализованная колонка (PG 12+ - generated)
ALTER TABLE articles ADD COLUMN tsv tsvector
  GENERATED ALWAYS AS (to_tsvector(\'russian\', body)) STORED;
CREATE INDEX idx_articles_tsv ON articles USING gin (tsv);
```

**FTS vs Elasticsearch — когда что:**

| Сценарий | PG FTS | Elasticsearch |
|---|---|---|
| Поиск в пределах **одной БД** | **да** | избыточно |
| Сложная релевантность, BM25, ML | ограниченно | **да** |
| Аналитика по логам | нет | **да** |
| Транзакционная консистентность с данными | **да** | нет (eventual) |
| Объёмы > сотен ГБ + миллионы документов | трудно | **да** |

**Правило:** для **базового** поиска внутри приложения — PG FTS хватает; для **поискового движка** как продукта — Elasticsearch / OpenSearch.

**Альтернатива для нечёткого поиска / опечаток** — расширение **`pg_trgm`** с **`gin (col gin_trgm_ops)`**: умеет `LIKE \'%abc%\'`, `similarity()`, и работает в дополнение к FTS.',
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
                'answer' => '**`pg_stat_statements`** — **стандартное расширение** PostgreSQL, накапливающее **агрегированную статистику** по запросам.

**Ключевая фишка — нормализация:** литералы заменяются на `$1`, `$2` — запросы с разными значениями **группируются вместе**. `SELECT * FROM users WHERE id = 5` и `... WHERE id = 7` будут одной строкой статистики.

**Что хранит для каждого запроса:**

| Поле | Что значит |
|---|---|
| `calls` | число вызовов |
| `total_exec_time` | **суммарное** время |
| `mean_exec_time` | среднее |
| `stddev_exec_time` | стандартное отклонение (нестабильность) |
| `rows` | прочитанных/записанных строк |
| `shared_blks_hit/read` | попадания/промахи в shared buffers |
| `temp_blks_*` | использование temp-файлов (сортировки в диск) |

**Зачем нужно — найти «топ-10 запросов по суммарному времени»:**
- именно они дают **основную нагрузку**, даже если каждый отдельный быстрый;
- 1000 раз × 10мс = `10s` нагрузки на один запрос > 1 раз × `1s`.

**Установка:**

1. `postgresql.conf` → `shared_preload_libraries = \'pg_stat_statements\'`;
2. рестарт PG;
3. `CREATE EXTENSION pg_stat_statements;`

**Сброс статистики** — `SELECT pg_stat_statements_reset()`.

**Связка для self-hosted APM:**

| Инструмент | Что даёт |
|---|---|
| **`pg_stat_statements`** | агрегированные метрики по запросам |
| **`auto_explain`** | логирует `EXPLAIN ANALYZE` для запросов дольше `N` мс |
| **`pg_stat_activity`** | текущие выполняющиеся запросы, поиск **`idle in transaction`** |
| **`log_min_duration_statement`** | пишет в лог запросы дольше порога |
| **`pgBadger`** (offline) | парсит логи в красивый отчёт |

Сочетание `pg_stat_statements` + `auto_explain` даёт **почти полноценный APM** без DataDog/NewRelic.

**Грабли:**
- `total_exec_time` сбрасывается при рестарте — для долгого анализа нужно периодически сохранять snapshot;
- запросы из `pg_stat_statements_info` могут быть **обрезаны** (по умолчанию 1024 символа `pg_stat_statements.max`);
- не показывает **планы** — для них `auto_explain`.',
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
                'answer' => '**PgBouncer** — самый популярный пулер соединений для PostgreSQL.

**Три режима пулинга:**

| Режим | Когда физ-соединение возвращается в пул | Эффективность пула |
|---|---|---|
| **`session`** | при **отключении** клиента | как без пулера |
| **`transaction`** | после **`COMMIT`/`ROLLBACK`** | **высокая**, самый частый в проде |
| **`statement`** | после **каждого** запроса | максимальная, но многое ломает |

**Проблема:** **Prepared Statements** — это **server-side** объект, привязанный к **физическому** соединению.

**Как PDO работает с prepared statements:**

| Режим PDO | Что отправляет на сервер |
|---|---|
| **native (`ATTR_EMULATE_PREPARES = false`)** | `PREPARE name AS ... ` + `EXECUTE name` — **серверный** prepared statement |
| **emulate (`ATTR_EMULATE_PREPARES = true`)** | подставляет параметры в SQL **на стороне PHP**, шлёт обычный query — **без `PREPARE`** |

**Что ломается в `transaction pooling`:** между `PREPARE` и `EXECUTE` PgBouncer может **выдать соединение другому клиенту**. При возврате на этом физ-соединении уже **не будет нашего `PREPARE`** — ошибка:

```
SQLSTATE[26000]: Invalid sql statement name:
7 ERROR: prepared statement "pdo_stmt_00000abc" does not exist
```

В Laravel это `PDOException`/`QueryException` в неожиданных местах под нагрузкой.

**Решения:**

| Способ | Плюсы | Минусы |
|---|---|---|
| **`PDO::ATTR_EMULATE_PREPARES => true`** | работает сразу, ничего не настраивать | меньше типизация на стороне БД (инъекции всё равно защищены — PDO экранирует) |
| **PgBouncer 1.22+ `max_prepared_statements > 0`** | нативные prepared statements работают | требует свежий PgBouncer; experimental в 1.21 |
| **`session pooling`** | работает всё | теряем смысл пулинга — меньше effective connections |
| **отдельный connection без пулера** для воркеров | гибкость | усложняет конфиг |

**Что ещё ломается в `transaction pooling`:**

```php
DB::statement("SET search_path TO custom_schema"); // НЕ сохраняется
DB::listen(...);                                    // LISTEN/NOTIFY не работает
DB::raw("SELECT pg_advisory_lock(?)");              // session-lock протекает на чужого клиента!
// Используй pg_advisory_xact_lock - живёт только до конца транзакции
```

**Симптом в Laravel:** `php artisan queue:work` часто использует `transaction pooling` и страдает от этого — лекарство **`emulate prepares`** на queue connection.

**Проверить режим:**

```sh
$ psql -h pgbouncer -p 6432 -U user pgbouncer
pgbouncer=# SHOW CONFIG; -- pool_mode = transaction|session|statement
pgbouncer=# SHOW POOLS;
```',
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
                'answer' => 'Два типа с похожим названием — но разной семантикой.

**`timestamp without time zone`** — хранит «голую» дату-время **как есть**, никаких преобразований не делает. Что записали, то и прочитали.

**`timestamp with time zone` (`timestamptz`)**:
- при `INSERT` — берёт значение, приводит к **UTC** по текущему `SET TIME ZONE` сессии, хранит в **UTC**;
- при `SELECT` — отдаёт обратно в timezone **сессии**.

**Важно:** несмотря на название, **сам пояс в строке не сохраняется** — сохраняется **точный момент во времени**.

**Практическое правило:**
- **`timestamptz`** — для приложений с пользователями в разных таймзонах и для **всех системных полей** (`created_at`, `updated_at`);
- **`timestamp`** — только для абстрактного «время суток» (расписание «звонок в 09:00 локального времени каждого офиса»).

**Грабли:** напрямую сравнивать `timestamp` и `timestamptz` — частая ошибка.',
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
                'answer' => 'Действие задаётся при создании `FOREIGN KEY` и срабатывает, когда удаляется/обновляется родитель, на который есть ссылки.

| Действие | Что делает |
|---|---|
| `CASCADE` | каскадно **удалить/обновить** все ссылающиеся строки |
| `RESTRICT` | **запретить** операцию, **падает мгновенно** |
| `NO ACTION` | запретить, но проверка **откладывается** до конца транзакции (совместима с `DEFERRABLE INITIALLY DEFERRED`) |
| `SET NULL` | **обнулить** ссылающийся столбец (требует `NULL`-able) |
| `SET DEFAULT` | поставить **значение по умолчанию** (оно тоже должно проходить FK-проверку) |

**По умолчанию** — `NO ACTION`.

**Выбор:**
- `CASCADE` удобно для **строго подчинённых** сущностей (комментарии при удалении поста);
- **опасно** на больших таблицах — каскад может развернуться в долгий `DELETE` с блокировками и тяжёлым `WAL`;
- `SET NULL` — когда нужно сохранить «осиротевшую» строку (заказ без удалённого юзера).',
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
                'answer' => '**Сетевые типы:**

| Тип | Размер | Что хранит |
|---|---|---|
| `inet` | 7 / 19 байт | IPv4/IPv6 + **опциональная** маска |
| `cidr` | 7 / 19 байт | сеть с **обязательной** маской (хосто-биты — нули) |
| `macaddr` | 6 байт | MAC-адрес |
| `macaddr8` | 8 байт | расширенный MAC |

**Преимущества над `TEXT`:**
- занимают **меньше места**;
- **валидируют формат** на входе (нельзя записать `999.999.999.999`);
- **сортировка по числовому** значению адреса;
- специальные операторы: `<<=` («входит ли адрес в сеть»), `&&` («пересекаются ли сети»), `>>`.

**`uuid` (16 байт):**
- 128-битный идентификатор в **бинарном** виде;
- в **2+ раза** компактнее 36-символьного `TEXT`;
- лучше индексируется;
- встроенный генератор **`gen_random_uuid()`** (из `pgcrypto`; с PG 13 — **нативно**).',
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
                'answer' => '**По умолчанию** updatable `VIEW` позволяет `INSERT`/`UPDATE` строк, которые **сразу же «выпадают»** из условия `WHERE` этого `VIEW` — видимы через таблицу, но не через сам `VIEW`.

**`WITH CHECK OPTION`** включает проверку: любой `INSERT`/`UPDATE` через `VIEW`, после которого строка **перестаёт удовлетворять `WHERE`**, упадёт с ошибкой:

```
ERROR: new row violates check option for view
```

**Зачем нужно** — инструмент **изоляции**:
- выдали аналитику доступ только к `VIEW active_users WHERE status = \'active\'`;
- он **не сможет** `UPDATE`-ом перевести юзера в `\'banned\'` и «потерять» его из своего среза.

**Два уровня:**
- **`LOCAL`** — проверка только **текущего** `VIEW`;
- **`CASCADED`** — ещё и условия **всех нижележащих** `VIEW`.

**По умолчанию в PG** — `CASCADED`.',
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
                'answer' => 'Сравнение двух режимов обновления матвью.

| | `REFRESH MATERIALIZED VIEW` | `REFRESH MATERIALIZED VIEW CONCURRENTLY` |
|---|---|---|
| Лок на матвью | **`ACCESS EXCLUSIVE`** — блокирует `SELECT` | минимальный, `SELECT` **не ждут** |
| Алгоритм | пересоздаёт всё с нуля | строит новую копию, **применяет дельту** через сравнение |
| Скорость | быстрее | **медленнее** и тяжелее по I/O |
| Требование | — | **`UNIQUE` индекс** обязателен |

**Зачем нужен `UNIQUE` индекс при `CONCURRENTLY`:** PostgreSQL должен уметь **сопоставлять строки** между старой и новой версией — без уникального ключа сопоставление неоднозначно.

**Когда что использовать:**
- **обычный `REFRESH`** — ночные регламентные пересборки, когда нет читателей или они допустимо подождать;
- **`CONCURRENTLY`** — когда **читатели всегда онлайн**, нужна доступность матвью **24/7**.

**Грабли:**
- **долгий `CONCURRENTLY`** удерживает старую версию **plus** строит новую — двойной размер на диске на время операции;
- если матвью **большой и часто меняется**, дешевле пересоздать **обычные таблицы** через `swap` (создать `_new`, `RENAME`-ом подменить);
- `CONCURRENTLY` нельзя запустить **внутри транзакции** (нужно автоконфирмо).

**Канонический паттерн:**

```sql
CREATE MATERIALIZED VIEW daily_sales AS
SELECT DATE(created_at) AS day, SUM(amount) AS total
FROM orders GROUP BY DATE(created_at);

-- UNIQUE индекс - обязательно для CONCURRENTLY
CREATE UNIQUE INDEX ON daily_sales(day);

-- В cron каждый час
REFRESH MATERIALIZED VIEW CONCURRENTLY daily_sales;
```',
                'difficulty' => 4,
                'topic' => 'database.postgresql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что значит «расширяемость» PostgreSQL и при чём тут CREATE TYPE и CREATE EXTENSION?',
                'answer' => 'PostgreSQL спроектирован как **объектно-реляционная СУБД** — пользователь может добавлять **собственные** низкоуровневые сущности.

**Что можно создавать:**

| Сущность | Команда | Пример |
|---|---|---|
| Тип данных | `CREATE TYPE` | составные, `ENUM`, **range types** (`int4range`, `tsrange`) |
| Функцию | `CREATE FUNCTION` | на `PL/pgSQL`, `PL/Python`, `PL/Perl`, `SQL`, `C` |
| Агрегат | `CREATE AGGREGATE` | свой `SUM` для нестандартного типа |
| Оператор | `CREATE OPERATOR` | `@@@` для своего домена |
| Класс операторов | `CREATE OPERATOR CLASS` | поддержка типа в `B-tree`/`GIN`/`GiST`/`SP-GiST` |
| Доменный тип | `CREATE DOMAIN` | `email`, `phone` с `CHECK`-валидацией |

**`CREATE EXTENSION`** — одной командой подключает **готовые наборы** такого функционала:

| Extension | Что добавляет |
|---|---|
| **`PostGIS`** | геометрические типы, R-tree через GiST, SRID, проекции — **де-факто стандарт ГИС** |
| **`pg_trgm`** | триграммы для нечёткого поиска, `similarity()`, GIN-индекс на `LIKE \'%abc%\'` |
| **`pgcrypto`** | `crypt()`, `digest()`, `gen_random_uuid()` (до PG 13) |
| **`hstore`** | key-value пары в одном столбце (предшественник `jsonb`) |
| **`uuid-ossp`** | генератор UUID разных версий |
| **`pg_stat_statements`** | статистика по запросам (см. отдельную карточку) |
| **`TimescaleDB`** | time-series надстройка через extension |
| **`Citus`** | distributed PostgreSQL — шардинг через extension |

**Почему расширяемость важна:**
- **главное системное отличие** PG от MySQL — последний почти не позволяет добавлять типы/индексы пользовательски;
- PG выбирают под **нестандартные домены** — ГИС, time-series, графы, документные БД;
- ядро PG **тоньше** — многое, что в других СУБД встроено, в PG приходит через extensions.',
                'difficulty' => 4,
                'topic' => 'database.postgresql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Какие отличия между MySQL InnoDB и PostgreSQL практически важны для разработки?',
                'answer' => 'Практически важные отличия для разработчика.

**Хранение и индексы:**

| | MySQL/InnoDB | PostgreSQL |
|---|---|---|
| Таблица | **clustered** по PK — данные в листьях B-tree | **heap** + отдельные индексы |
| Secondary index хранит | PK как payload (+ lookup) | `ctid` (физ. адрес версии) |
| MVCC хранилище | **undo log** (отдельно) | **в самой таблице** + dead tuples |
| Партиционирование | через `PARTITION BY` | через `PARTITION BY` или наследование |

**Блокировки и изоляция:**
- **InnoDB**: на `REPEATABLE READ` использует **gap locks** и **next-key locks** для защиты от phantom;
- **Postgres**: snapshot isolation — фантомов и так нет, write skew спасает **`SERIALIZABLE` (SSI)**;
- по умолчанию: MySQL — `REPEATABLE READ`, PG — `READ COMMITTED`.

**UPSERT:**

| | Синтаксис |
|---|---|
| MySQL | `INSERT ... ON DUPLICATE KEY UPDATE` |
| PostgreSQL | `INSERT ... ON CONFLICT (...) DO UPDATE` |

**Богатство SQL:** Postgres имеет `CTE` (рекурсивные), оконные с расширенным синтаксисом, `jsonb`, arrays, **partial** и **expression indexes**, `LATERAL JOIN`, `FILTER` в агрегатах — MySQL это получил позже и **беднее**.

**КРИТИЧНО — транзакционность DDL:**

| СУБД | DDL транзакционно? | Что значит |
|---|---|---|
| **PostgreSQL** | **да** | `BEGIN; ALTER TABLE; ROLLBACK;` — схема вернётся |
| **SQL Server** | **да** | как PG |
| **MySQL/MariaDB** | **нет** | implicit `COMMIT` до каждого DDL |
| **Oracle** | **нет** | implicit `COMMIT` **до и после** DDL |

**Последствия:**
- в **PG** можно **атомарно** применять цепочку миграций (всё или ничего), тестировать с `ROLLBACK`, использовать `SAVEPOINT`;
- в **MySQL** если миграция упала на 5-м `ALTER` из 10 — первые 4 **уже применены**; пишут миграции **по одному изменению**, с продуманным rollback;
- **исключение в PG**: **`CREATE INDEX CONCURRENTLY`** **нельзя** запускать внутри транзакции — он сам открывает несколько внутренних.',
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
