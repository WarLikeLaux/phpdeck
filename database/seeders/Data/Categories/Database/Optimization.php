<?php

namespace Database\Seeders\Data\Categories\Database;

class Optimization
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Базы данных',
                'question' => 'Что такое EXPLAIN и EXPLAIN ANALYZE?',
                'answer' => 'EXPLAIN показывает план выполнения запроса - как БД будет его выполнять (какие индексы, JOIN-ы, сортировки). EXPLAIN ANALYZE дополнительно реально выполняет запрос и показывает фактическое время и количество строк. Главное смотреть: тип scan (Seq/Index), оценочные vs реальные строки, самые дорогие узлы.',
                'code_example' => <<<'SQL'
EXPLAIN SELECT * FROM users WHERE email = 'ivan@mail.ru';

EXPLAIN ANALYZE
SELECT u.name, COUNT(o.id)
FROM users u
LEFT JOIN orders o ON o.user_id = u.id
GROUP BY u.name;
SQL,
                'code_language' => 'sql',
                'difficulty' => 4,
                'topic' => 'database.optimization',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое Nested Loop, Hash Join, Merge Join?',
                'answer' => 'Это три алгоритма JOIN. Nested Loop: для каждой строки слева ищем подходящие справа (хорошо когда слева мало строк и есть индекс справа). Hash Join: строим хэш-таблицу из правой стороны и для каждой левой ищем в хэше O(1) (хорошо для больших таблиц без индексов). Merge Join: обе стороны должны быть отсортированы по ключу JOIN, идём слиянием как при merge sort (хорошо для уже отсортированных данных). Планировщик сам выбирает.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 4,
                'topic' => 'database.optimization',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Когда стоит добавлять индекс?',
                'answer' => 'Добавлять индекс стоит, когда: столбец часто в WHERE/JOIN/ORDER BY и таблица большая; запрос медленный, EXPLAIN показывает Seq Scan; столбец имеет высокую cardinality; чтений намного больше записей. НЕ стоит: маленькие таблицы (<1000 строк), очень частые INSERT/UPDATE, низкая селективность фильтра.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 3,
                'topic' => 'database.optimization',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Почему слишком много индексов - это плохо?',
                'answer' => 'Каждый индекс: занимает место на диске, замедляет INSERT/UPDATE/DELETE (БД должна обновлять все индексы), увеличивает время бэкапов, может запутать планировщик и привести к выбору не самого быстрого. Правило: индексируй только то, что реально часто запрашивается. Удаляй неиспользуемые индексы (в PG: pg_stat_user_indexes).',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 3,
                'topic' => 'database.optimization',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'OFFSET vs Keyset (cursor) пагинация - что лучше?',
                'answer' => 'OFFSET-пагинация: LIMIT 20 OFFSET 10000. Минусы: БД сканирует и отбрасывает все 10000 пропускаемых строк - очень медленно на больших таблицах (стоимость растёт с глубиной OFFSET). Keyset (cursor) пагинация: запоминаем последний ключ предыдущей страницы и фильтруем WHERE id > last_id. Преимущество: стоимость не зависит от глубины OFFSET - при подходящем индексе это index seek + чтение LIMIT строк (~O(log N + page_size); не O(1), как часто пишут, но стабильно на любой глубине). Минус: нельзя "перейти на страницу 50", только next/prev; при ORDER BY по неуникальному полю нужен составной курсор (sort_col, id) для tiebreaker, иначе пропуски/дубли.',
                'code_example' => <<<'SQL'
-- OFFSET (медленно на глубине)
SELECT * FROM articles ORDER BY id LIMIT 20 OFFSET 10000;

-- Keyset (быстро всегда)
SELECT * FROM articles
WHERE id > 12345  -- last id с прошлой страницы
ORDER BY id
LIMIT 20;
SQL,
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.optimization',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое N+1 проблема и как её решать?',
                'answer' => 'N+1 - проблема ORM, когда для получения N записей делается 1 запрос на список + N запросов на связанные данные. Например, 100 пользователей -> 1 + 100 = 101 запрос. Решение: eager loading (Eloquent: with(), JPA: JOIN FETCH), JOIN-ы вручную, dataloader (для GraphQL). В Laravel: User::with("posts")->get() вместо ->get() + загрузка $user->posts по требованию.',
                'code_example' => <<<'PHP'
// Плохо: N+1 (1 запрос users + N запросов posts)
$users = User::all();
foreach ($users as $user) {
    echo $user->posts->count(); // ленивая загрузка posts на каждой итерации
}

// Хорошо: eager loading (всего 2 запроса)
$users = User::with('posts')->get();
foreach ($users as $user) {
    echo $user->posts->count(); // posts уже загружены, count() по коллекции
}

// Ещё лучше для счётчиков: withCount (один запрос с подзапросом)
$users = User::withCount('posts')->get();
foreach ($users as $user) {
    echo $user->posts_count;
}
PHP,
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
                'answer' => 'Главный столбец — type: значения system, const, eq_ref, ref означают точечный доступ по индексу, range — диапазон, index — полный скан индекса, ALL — полный скан таблицы и обычно красный флаг. key показывает фактически использованный индекс, key_len — сколько байтов индекса задействовано (важно для составных). rows — оценка прочитанных строк, filtered — процент после фильтрации. Extra сигналит о проблемах: Using filesort означает сортировку без индекса, Using temporary — внутреннюю временную таблицу под GROUP BY/DISTINCT, Using index — наоборот, отличный признак covering-плана.',
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
                'answer' => 'Slow query log — встроенный журнал MySQL, куда пишутся запросы, чьё время выполнения превысило long_query_time (по умолчанию 10 секунд, на проде обычно ставят 0.1-1). Включают через slow_query_log=1 и slow_query_log_file=/path. Опция log_queries_not_using_indexes дополнительно ловит запросы без индекса. Анализируют лог утилитой pt-query-digest или mysqldumpslow — они группируют запросы по форме и показывают топ по сумме времени, что важнее, чем "самый медленный единичный запрос". Это первый инструмент при жалобах на тормоза до подключения APM.',
                'difficulty' => 3,
                'topic' => 'database.optimization',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Зачем нужен ANALYZE TABLE и чем он отличается от EXPLAIN?',
                'answer' => 'ANALYZE TABLE пересчитывает статистику распределения значений в индексах: cardinality, гистограммы, плотность ключей. На основе этой статистики оптимизатор решает, какой индекс использовать и в каком порядке соединять таблицы. EXPLAIN, наоборот, ничего не пересчитывает — он только показывает план для конкретного запроса. Если после массовой загрузки или DELETE планы выглядят странно ("оптимизатор берёт ALL вместо очевидного индекса"), часто помогает именно ANALYZE TABLE. EXPLAIN ANALYZE — отдельная команда MySQL 8, она выполняет запрос и показывает фактические времена и количества строк рядом с оценками.',
                'difficulty' => 3,
                'topic' => 'database.optimization',
            ],
        ];
    }
}
