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
                'answer' => 'Это три алгоритма JOIN. Nested Loop: для каждой строки слева ищем подходящие справа (хорошо когда слева мало строк и есть индекс справа). Hash Join: строим хэш-таблицу из правой стороны и для каждой левой ищем в хэше O(1) (хорошо для больших таблиц без индексов). Merge Join: обе стороны должны быть отсортированы по ключу JOIN, идём слиянием как при merge sort (хорошо для уже отсортированных данных). Планировщик сам выбирает.',
                'difficulty' => 4,
                'topic' => 'database.optimization',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Почему слишком много индексов - это плохо?',
                'answer' => 'Индекс — это не бесплатная "галочка ускорения", а вторая структура данных, которую БД обязана поддерживать. Каждый дополнительный индекс: 1) занимает место на диске (часто сопоставимое с самой таблицей при широких составных индексах), 2) замедляет INSERT/UPDATE/DELETE — при каждой модификации надо обновлять все индексы, по которым проходят изменённые колонки, 3) увеличивает время и объём бэкапов и репликации (WAL/binlog растут), 4) может запутать планировщик: если есть три похожих индекса, оптимизатор иногда выбирает не самый эффективный или начинает дольше выбирать план. Правило: индексируй только реально частые запросы, периодически удаляй неиспользуемые. В PG смотрят pg_stat_user_indexes (idx_scan = 0 — кандидат на удаление), в MySQL — sys.schema_unused_indexes / performance_schema.',
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
                'answer' => 'OFFSET-пагинация: LIMIT 20 OFFSET 10000. Минусы: БД сканирует и отбрасывает все 10000 пропускаемых строк - очень медленно на больших таблицах (стоимость растёт с глубиной OFFSET). Keyset (cursor) пагинация: запоминаем последний ключ предыдущей страницы и фильтруем WHERE id > last_id. Преимущество: стоимость не зависит от глубины OFFSET - при подходящем индексе это index seek + чтение LIMIT строк (~O(log N + page_size); не O(1), как часто пишут, но стабильно на любой глубине). Минус: нельзя "перейти на страницу 50", только next/prev; при ORDER BY по неуникальному полю нужен составной курсор (sort_col, id) для tiebreaker, иначе пропуски/дубли.',
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
                'answer' => 'N+1 - проблема ORM, когда для получения N записей делается 1 запрос на список + N запросов на связанные данные. Например, 100 пользователей -> 1 + 100 = 101 запрос. Решение: eager loading (Eloquent: with(), JPA: JOIN FETCH), JOIN-ы вручную, dataloader (для GraphQL). В Laravel: User::with("posts")->get() вместо ->get() + загрузка $user->posts по требованию.',
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
                'answer' => 'Точная цифра в MyISAM хранилась прямо в метаданных таблицы - SELECT COUNT(*) без WHERE возвращал её за O(1). InnoDB и PostgreSQL так не могут из-за MVCC (Multi-Version Concurrency Control). Когда в БД одновременно работают несколько транзакций с разными snapshot-ами, "точное количество строк" - не одна цифра, а N разных цифр для N снапшотов: транзакция T1, начавшаяся в момент A, видит одни строки; T2, начавшаяся позже - другие (часть удалённых стала "не видна", часть добавленных - "не видна"). Для каждой транзакции БД должна пройти и проверить visibility (видимость) каждой строки относительно её snapshot-а - это full scan по таблице или по индексу. В InnoDB с PK можно сделать count по самому компактному индексу (не по таблице), но всё равно линейный проход. В PostgreSQL ещё хуже: heap-страницы могут содержать "мёртвые" строки (удалённые, но не очищенные VACUUM); visibility map иногда позволяет ускорить через index-only scan, но при свежих изменениях map неактуален. Способы ускорения: 1) SELECT reltuples FROM pg_class WHERE relname="t" - приблизительная оценка (обновляется ANALYZE/VACUUM, может отставать). 2) Своя счётная таблица + триггеры на INSERT/DELETE. 3) Для UI-пагинации - simplePaginate / cursor-пагинация без COUNT вообще. 4) Кеш с TTL: если погрешность приемлема. 5) В Postgres 16+ EXPLAIN с estimate - часто достаточно. Когда COUNT(*) ОК: с селективным WHERE по индексу, на маленьких таблицах. Анти-паттерн: показывать "Найдено 12 345 678 записей" в админке на 100M-таблице - полный скан на каждый клик.',
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
                'answer' => 'Базовые типы операций в плане. SEQ SCAN (Postgres) / type=ALL (MySQL): полное чтение таблицы строка за строкой. Дешёвая операция, если читать НАДО почти всю таблицу (>10-30%) - последовательный I/O быстрее, чем рандомные seek-и. ДОРОГАЯ, если из 10М строк нужно 10 - но оптимизатор всё равно выбрал Seq Scan: это сигнал, что либо нет подходящего индекса, либо он есть но не используется (см. ниже). INDEX SCAN: бинарный спуск по B-tree до нужного диапазона + чтение листовых страниц + дереференс TID/RID к heap. Хорошо при селективном фильтре (1-5% строк). INDEX-ONLY SCAN (Postgres) / Using index (MySQL): данные взяты ПРЯМО из индекса, без обращения к heap-таблице - возможно, когда все нужные колонки покрыты индексом (covering index). BITMAP SCAN (Postgres): много несмежных рядов - сначала собрать bitmap позиций, потом одним проходом прочитать heap. ВАЖНОЕ ЯВЛЕНИЕ - LIMIT МЕНЯЕТ ПЛАН. Запрос SELECT * FROM orders WHERE user_id = 5 ORDER BY created_at DESC может пойти через Index Scan (orders_user_id_idx) + Sort. Тот же запрос с LIMIT 10 оптимизатор может перестроить совсем иначе: пойти ПО ИНДЕКСУ (created_at DESC) и читать по одной строке, отбрасывая не подходящих по user_id, пока не наберёт 10 - надеясь, что 10 первых среди свежих заказов скорее всего окажутся искомым user_id. На "плотных" данных это работает, на разреженных (user_id=5 заказывал последний раз год назад) - оптимизатор перебирает весь индекс впустую и получается медленнее, чем без LIMIT. Это классический "abort early" паттерн - и его узнают по плану с Limit над Index Scan и низкими actual rows. Боремся: 1) ANALYZE для свежей статистики; 2) форс через индекс-хинт (USE INDEX в MySQL); 3) переписать как WHERE id IN (subquery с LIMIT по нужному индексу); 4) добавить составной индекс (user_id, created_at DESC) - тогда оптимизатор увидит "копеечный" путь.',
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
                'answer' => 'Главный столбец — type, он же тип доступа, упорядочен от лучшего к худшему: system/const (одна строка по PK) → eq_ref/ref (точечный доступ по индексу при JOIN) → range (диапазон по индексу) → index (полный скан индекса) → ALL (полный скан таблицы, обычно красный флаг на большой таблице). key — какой индекс реально выбран (NULL — индекс не использован). key_len — сколько байтов составного индекса задействовано (можно понять, сколько колонок реально работают). rows — оценка прочитанных строк, filtered — процент строк, оставшихся после WHERE. Extra — место с подсказками: Using filesort (сортировка без индекса — добавь индекс под ORDER BY), Using temporary (внутренняя временная таблица под GROUP BY/DISTINCT/UNION — тоже плохо), Using index (covering index, отлично), Using where (WHERE применён после чтения — норма).',
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
                'answer' => 'Using filesort — это не сортировка в файле буквально, а отдельный проход для упорядочивания результата, потому что подходящего индекса для ORDER BY нет; на больших наборах он сильно бьёт по CPU и памяти. Using temporary означает, что MySQL создал временную таблицу (сначала в памяти, при переполнении — на диске) для GROUP BY, DISTINCT, UNION или подзапроса. Оба маркера — кандидаты на оптимизацию: либо добавить составной индекс, покрывающий ORDER BY/GROUP BY, либо переписать запрос так, чтобы группировка шла по индексированному префиксу.',
                'difficulty' => 4,
                'topic' => 'database.optimization',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое slow query log и как им пользоваться?',
                'answer' => 'Slow query log — встроенный журнал MySQL, куда пишутся все запросы, выполнявшиеся дольше порога long_query_time (по умолчанию 10 секунд, на проде обычно ставят 0.1-1с — иначе ничего не поймаешь). Включают через slow_query_log=1 и slow_query_log_file=/path/file.log; дополнительная опция log_queries_not_using_indexes ловит запросы без индекса, даже если они быстрые сейчас (вырастут с объёмом данных). Анализируют не глазами, а утилитами pt-query-digest (из Percona Toolkit) или mysqldumpslow: они группируют запросы по нормализованной форме и сортируют по суммарному времени, а это правильная метрика — частый "быстрый" запрос на 50мс в 10000 раз в минуту страшнее одного на 5 секунд. В PostgreSQL аналог называется log_min_duration_statement плюс расширение pg_stat_statements. Это первый инструмент при жалобах на тормоза до подключения APM.',
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
                'answer' => 'ANALYZE TABLE пересчитывает статистику распределения значений в таблице и индексах: cardinality (сколько уникальных значений в колонке), гистограммы распределения, плотность ключей. На этой статистике оптимизатор строит cost-модель — решает, какой индекс брать, в каком порядке соединять таблицы, делать ли Hash Join или Nested Loop. EXPLAIN ничего не пересчитывает, он только показывает план для конкретного запроса на основе уже имеющейся статистики. Если после массовой загрузки, большого DELETE или TRUNCATE+INSERT планы вдруг становятся "тупыми" (оптимизатор берёт ALL вместо очевидного индекса) — почти всегда это устаревшая статистика, и помогает ANALYZE TABLE. EXPLAIN ANALYZE — отдельная команда (MySQL 8.0.18+ и PostgreSQL), которая выполняет запрос и показывает фактические rows/time рядом с оценками, чтобы найти расхождения. PostgreSQL запускает ANALYZE автоматически через autovacuum, но после большой загрузки часто запускают вручную.',
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
                'answer' => 'EXPLAIN показывает план; ANALYZE реально выполняет запрос и добавляет actual time, rows, loops. Тревожные признаки: Seq Scan по большой таблице с селективным WHERE (нет индекса), резкое расхождение rows-estimate vs actual (плохая статистика, нужен ANALYZE), Nested Loop с большим внешним циклом (надо Hash Join), Sort с внешним диском (work_mem мал), Bitmap Heap Scan + Recheck Cond (lossy). Используют BUFFERS для shared hit/read.',
                'code_example' => 'EXPLAIN (ANALYZE, BUFFERS, VERBOSE)
SELECT u.id, COUNT(o.id)
FROM users u JOIN orders o ON o.user_id = u.id
WHERE u.created_at > NOW() - INTERVAL \'30 days\'
GROUP BY u.id;',
                'code_language' => 'sql',
                'difficulty' => 4,
                'topic' => 'database.optimization',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Как устроен оптимизатор запросов и что такое статистики?',
                'answer' => 'Оптимизатор перебирает планы и оценивает стоимость через cost-based модель. Статистики (pg_statistic, ANALYZE) дают cardinality для столбцов: гистограммы, MCV, n_distinct. На их основе оценивается selectivity предикатов и размер промежуточных наборов. Если статистики устарели или коррелированные предикаты - план кривой. Решения: ANALYZE, увеличить default_statistics_target, CREATE STATISTICS для функциональных зависимостей.',
                'code_example' => '-- multivariate statistics для коррелированных колонок
CREATE STATISTICS orders_corr (dependencies)
ON status, payment_method FROM orders;
ANALYZE orders;',
                'code_language' => 'sql',
                'difficulty' => 5,
                'topic' => 'database.optimization',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое медленный запрос простыми словами?',
                'answer' => 'Медленный запрос — это SQL-запрос, который выполняется дольше «нормы». Норма зависит от проекта, но обычно ставят порог 100-500 мс: всё, что дольше — повод разобраться. Типичные причины: на колонке из WHERE нет индекса и БД сканирует всю таблицу; JOIN большой таблицы без индекса по полю связи; ORDER BY без подходящего индекса (БД сортирует на лету); запрос возвращает огромное количество строк, которые приложению не нужны. Чтобы ловить такие запросы, в MySQL и PostgreSQL включают slow query log — БД сама пишет в файл все запросы, превысившие заданное время.',
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
                'answer' => 'EXPLAIN показывает план выполнения запроса: какой индекс будет использован, сколько строк планировщик ожидает прочесть, в каком порядке соединяются таблицы и какой тип доступа выбран (Index Scan, Seq Scan, Nested Loop, Hash Join). Это первый инструмент, когда запрос «тормозит» — обычно сразу видно, что вместо Index Scan идёт Seq Scan по миллиону строк. Обычный EXPLAIN ничего не выполняет, только оценивает; EXPLAIN ANALYZE реально запускает запрос и добавляет фактическое время и количество строк. В MySQL смотрят на колонки type, key, rows, Extra; в PostgreSQL — на тип узла и его cost/actual time.',
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
                'answer' => '1) Включить slow query log в БД — все долгие запросы будут писаться в файл. 2) Использовать APM (Laravel Telescope, Datadog, Sentry) — увидишь топ медленных запросов. 3) Локально — Debugbar/Telescope покажет время каждого запроса. 4) EXPLAIN на подозрительном запросе — посмотреть план.',
                'difficulty' => 2,
                'topic' => 'database.optimization',
            ],
        ];
    }
}
