<?php

namespace Database\Seeders\Data\Categories\Database;

class Partitioning
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Базы данных',
                'question' => 'Что такое партиционирование (partitioning) простыми словами?',
                'answer' => 'Партиционирование - разделение одной таблицы на несколько физических частей (партиций) внутри одной БД, прозрачно для приложения. Простыми словами: если шкаф переполнен, ты разделяешь содержимое по полкам - "одежда зимняя", "одежда летняя". Приложение видит один шкаф, а БД ищет только в нужной полке. Бывает range, list, hash. Преимущества: быстрее запросы (partition pruning), быстрее удаление старых данных (DROP PARTITION), отдельные индексы. Это НЕ шардирование - всё на одном сервере.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 4,
                'topic' => 'database.partitioning',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Range vs List vs Hash партиционирование?',
                'answer' => 'Range - по диапазонам значений (часто даты): партиция за каждый месяц. Удобно для time-series данных. List - по списку явных значений (по странам, по статусам). Hash - по хешу ключа, равномерно. Range и List лучше когда данные имеют естественные группы; hash - для равномерного распределения по партициям без логических групп.',
                'code_example' => <<<'SQL'
-- PostgreSQL Range Partitioning
CREATE TABLE events (
    id BIGSERIAL,
    occurred_at TIMESTAMP NOT NULL,
    payload JSONB
) PARTITION BY RANGE (occurred_at);

CREATE TABLE events_2024_01 PARTITION OF events
    FOR VALUES FROM ('2024-01-01') TO ('2024-02-01');

CREATE TABLE events_2024_02 PARTITION OF events
    FOR VALUES FROM ('2024-02-01') TO ('2024-03-01');

-- List
CREATE TABLE users PARTITION BY LIST (country);
CREATE TABLE users_ru PARTITION OF users FOR VALUES IN ('RU', 'BY', 'KZ');
CREATE TABLE users_us PARTITION OF users FOR VALUES IN ('US', 'CA');

-- Hash
CREATE TABLE logs PARTITION BY HASH (user_id);
CREATE TABLE logs_0 PARTITION OF logs FOR VALUES WITH (modulus 4, remainder 0);
SQL,
                'code_language' => 'sql',
                'difficulty' => 5,
                'topic' => 'database.partitioning',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Чем B-tree индекс отличается от Hash и от GIN, и в каких случаях выбирать GIN?',
                'answer' => 'B-tree - упорядоченное дерево, поддерживает =, <, >, BETWEEN, ORDER BY, LIKE \'prefix%\'. Hash - только равенство, в Postgres с PG10+ wal-логируется и пригоден для интенсивных = поиска. GIN - обратный индекс: ключ → набор строк; идеален для tsvector (full-text), jsonb (?, @>), массивов и trigram (pg_trgm) для LIKE \'%inside%\'. GIN строится медленнее и больше на диске, но запросы по "содержит" выигрывают на порядки.',
                'code_example' => '-- быстрый поиск по вхождению
CREATE INDEX idx_products_name_trgm ON products USING gin (name gin_trgm_ops);
SELECT * FROM products WHERE name ILIKE \'%phone%\';

-- jsonb-фильтр
CREATE INDEX idx_events_payload ON events USING gin (payload);
SELECT * FROM events WHERE payload @> \'{"type":"click"}\';',
                'code_language' => 'sql',
                'difficulty' => 4,
                'topic' => 'database.partitioning',
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
                'topic' => 'database.partitioning',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Чем отличаются Nested Loop, Hash Join и Merge Join?',
                'answer' => 'Nested Loop: для каждой строки внешней таблицы ищет совпадения во внутренней; эффективен, если внешняя маленькая, а на внутренней есть индекс по ключу. Hash Join: строит хеш-таблицу по меньшей стороне в памяти, потом сканирует большую и ищет совпадения; хорош для больших равенств без индексов. Merge Join: обе стороны отсортированы по ключу - идёт двусторонний слиянием; быстро, если данные уже отсортированы (или есть подходящий индекс).',
                'code_example' => '-- форсируем тип join для теста
SET enable_hashjoin = off;
SET enable_mergejoin = off;
EXPLAIN ANALYZE SELECT * FROM a JOIN b USING (id);',
                'code_language' => 'sql',
                'difficulty' => 4,
                'topic' => 'database.partitioning',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Объясните уровни изоляции и какие аномалии каждый предотвращает.',
                'answer' => 'READ UNCOMMITTED - допускает dirty read. READ COMMITTED - нет dirty, но возможны non-repeatable read и phantom. REPEATABLE READ - устраняет non-repeatable; в PostgreSQL (snapshot isolation) фантомы тоже исключены - остаётся только write skew / serialization anomalies; в MySQL InnoDB фантомы исключены за счёт MVCC для consistent reads и за счёт next-key/gap locks для locking reads. SERIALIZABLE - полная сериализуемость, в Postgres через SSI с rollback при конфликте (serialization_failure 40001), в InnoDB - через range/gap locks. Write skew отлавливается только на Serializable.',
                'code_example' => '-- write skew пример
BEGIN ISOLATION LEVEL SERIALIZABLE;
SELECT SUM(on_call) FROM doctors WHERE shift = \'night\';
-- если >=2, можно уйти
UPDATE doctors SET on_call = false WHERE id = 1;
COMMIT; -- может откатиться при serialization_failure',
                'code_language' => 'sql',
                'difficulty' => 5,
                'topic' => 'database.partitioning',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Как работает MVCC в PostgreSQL и зачем нужен VACUUM?',
                'answer' => 'MVCC: каждое UPDATE/DELETE не меняет строку, а создаёт новую версию с xmin (transaction id создания) и xmax (id удаления). Транзакции видят только версии с подходящим xmin/xmax относительно своего snapshot. Старые tuple остаются в таблице как "мертвые" - bloat. VACUUM маркирует их свободными для переиспользования; VACUUM FULL переписывает таблицу. Autovacuum триггерится по threshold; параметры autovacuum_vacuum_scale_factor нужно тюнить для горячих таблиц.',
                'code_example' => '-- увидеть bloat
SELECT relname, n_dead_tup, n_live_tup, last_autovacuum
FROM pg_stat_user_tables
ORDER BY n_dead_tup DESC LIMIT 10;',
                'code_language' => 'sql',
                'difficulty' => 5,
                'topic' => 'database.partitioning',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое deadlock и как его диагностировать?',
                'answer' => 'Deadlock - циклическое ожидание блокировок между транзакциями: T1 держит A, ждёт B; T2 держит B, ждёт A. БД детектирует цикл и убивает одну транзакцию (deadlock_timeout). Профилактика: блокировать ресурсы в одинаковом порядке (отсортировать ID), уменьшать длительность транзакций, использовать SELECT ... FOR UPDATE NOWAIT/SKIP LOCKED, индексировать колонки в WHERE при UPDATE. В Postgres логи показывают полные query обоих участников.',
                'code_example' => '-- симметричный порядок блокировок
SELECT * FROM accounts WHERE id IN (:a, :b)
ORDER BY id FOR UPDATE;
-- теперь обе транзакции лочат A раньше B',
                'code_language' => 'sql',
                'difficulty' => 4,
                'topic' => 'database.partitioning',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое covering index и когда он даёт большой выигрыш?',
                'answer' => 'Covering index содержит все колонки, нужные запросу, либо в ключе, либо в INCLUDE (Postgres 11+) - оптимизатор берёт данные прямо из индекса без обращения к heap (Index Only Scan). Это убирает random IO на табличные страницы для широких таблиц. ВАЖНАЯ специфика InnoDB: каждый secondary index АВТОМАТИЧЕСКИ и неявно содержит Primary Key в листовых узлах в качестве указателя на кластерный индекс (потому что таблица в InnoDB - это и есть B-tree по PK). Поэтому индекс по (status) под капотом фактически (status, id), и SELECT id FROM orders WHERE status="paid" - это Index Only Scan без всяких дополнительных усилий, ничего вручную не добавляешь. Добавлять PK явно не надо - это будет ошибкой. Этим InnoDB отличается от Postgres heap, где для покрытия нужно явно перечислять колонки в ключе или в INCLUDE. Когда выигрыш максимален: широкие таблицы с пустыми/неиспользуемыми колонками в SELECT, "узкие" запросы по индексу + сортировка/агрегация по индексным колонкам.',
                'code_example' => 'CREATE INDEX idx_orders_status_created
ON orders (status, created_at) INCLUDE (total);
-- запрос обслуживается Index Only Scan
SELECT total FROM orders
WHERE status = \'paid\' AND created_at > NOW() - INTERVAL \'1 day\';',
                'code_language' => 'sql',
                'difficulty' => 4,
                'topic' => 'database.partitioning',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Какие виды партиционирования есть в Postgres и какие проблемы они решают?',
                'answer' => 'PARTITION BY RANGE (по диапазону, чаще по дате) - типично для логов и time-series, позволяет быстро дропать старые данные через DROP PARTITION. PARTITION BY LIST - по перечислению (страна, тенант). PARTITION BY HASH - равномерное распределение. Partition pruning - оптимизатор не сканирует партиции, не подходящие под WHERE. Local indexes на каждой партиции; FK *из* партиционированной таблицы появились в PG 11, FK *на* партиционированную таблицу (когда она выступает referenced-стороной) — только с PG 12+.',
                'code_example' => 'CREATE TABLE events (
    id bigserial, created_at timestamptz NOT NULL, payload jsonb
) PARTITION BY RANGE (created_at);

CREATE TABLE events_2026_05 PARTITION OF events
    FOR VALUES FROM (\'2026-05-01\') TO (\'2026-06-01\');',
                'code_language' => 'sql',
                'difficulty' => 5,
                'topic' => 'database.partitioning',
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
                'topic' => 'database.partitioning',
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
                'topic' => 'database.partitioning',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое write skew и приведите пример из реального приложения.',
                'answer' => 'Write skew - две транзакции читают пересекающийся набор строк, принимают решение на основе snapshot и пишут разные строки, нарушая инвариант. Классический пример: дежурство врачей. Две транзакции видят, что дежурят 2 человека, и одновременно "уходят домой" - оба отметят off-call, нарушив правило "минимум один". REPEATABLE READ не ловит write skew, нужен SERIALIZABLE или SELECT FOR UPDATE на конфликтующие строки.',
                'code_example' => '-- защита через SELECT FOR UPDATE
BEGIN;
SELECT count(*) FROM doctors WHERE on_call = true FOR UPDATE;
-- если > 1, можно отключиться
UPDATE doctors SET on_call = false WHERE id = :me;
COMMIT;',
                'code_language' => 'sql',
                'difficulty' => 5,
                'topic' => 'database.partitioning',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Как реализовать поиск с пагинацией без OFFSET и почему OFFSET плохой?',
                'answer' => 'OFFSET N сканирует и отбрасывает первые N строк - стоимость линейная, на 10-й странице запрос медленнее, чем на 1-й, при том же limit. Keyset (cursor) пагинация использует значение последней увиденной строки в WHERE и ORDER BY: WHERE (created_at, id) < (:last_at, :last_id). Стоимость стабильна и низкая при индексе на (created_at, id). Минус - нельзя прыгнуть на конкретную страницу, только next/prev.',
                'code_example' => '-- keyset pagination
SELECT id, title, created_at FROM posts
WHERE (created_at, id) < (:cursor_at, :cursor_id)
ORDER BY created_at DESC, id DESC
LIMIT 20;',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.partitioning',
            ],
        ];
    }
}
