<?php

namespace Database\Seeders\Data\Categories\Database;

class Indexes
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Базы данных',
                'question' => 'Зачем нужны индексы и что это такое простыми словами?',
                'answer' => '**Индекс** — вспомогательная **структура данных**, которая **ускоряет поиск** по таблице.

**Аналогия:** толстая книга. Чтобы найти главу «Пингвины», ты не листаешь каждую страницу — смотришь в **алфавитный указатель** в конце книги, видишь «Пингвины — стр. 524» и сразу открываешь нужную страницу. Индекс в БД работает так же: вместо полного сканирования таблицы СУБД сначала идёт в индекс, находит позицию и читает **только нужные строки**.

**Минусы:**
- занимает **место на диске**;
- **замедляет** `INSERT`/`UPDATE`/`DELETE` (вместе с данными надо обновлять и индекс).

**Куда ставить:** на колонки, по которым часто:
- фильтруют — `WHERE`;
- соединяют — `JOIN`;
- сортируют — `ORDER BY`.

На всё подряд — **не нужно**.',
                'code_example' => '-- Создать индекс на колонке email
CREATE INDEX idx_users_email ON users(email);

-- Теперь поиск по email — быстрый, БД идёт через индекс
SELECT * FROM users WHERE email = \'ivan@mail.ru\';

-- На внешних ключах индекс почти всегда нужен (JOIN-ы)
CREATE INDEX idx_orders_user_id ON orders(user_id);

-- Удалить индекс, если он не нужен
DROP INDEX idx_users_email;',
                'code_language' => 'sql',
                'difficulty' => 1,
                'topic' => 'database.indexes',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Как устроен B-tree индекс?',
                'answer' => 'B-tree (balanced tree, точнее B+ tree в современных СУБД) — это сбалансированное многоуровневое дерево с большим коэффициентом ветвления, где все ключи отсортированы. Внутренние узлы содержат только ключи-разделители и указатели на детей, а собственно данные/идентификаторы строк лежат в листьях; все листья дополнительно связаны двусвязным списком — это даёт дешёвое range-сканирование (нашли начало диапазона, дальше идём по соседям без возврата в корень). Поиск, вставка и удаление — O(log n), причём из-за высокого ветвления реальная высота дерева на миллиарде строк — 3-5 уровней (несколько обращений к диску/буферному пулу). Это самый универсальный тип индекса и стоит по умолчанию: подходит для =, <, >, BETWEEN, IN, ORDER BY, для префиксного LIKE \'abc%\'. Не помогает на LIKE \'%abc%\' и на функции от колонки (нужен expression index).',
                'code_example' => '-- Создание (по умолчанию это и есть B-tree)
CREATE INDEX idx_orders_created ON orders(created_at);
-- PG явно: CREATE INDEX ... USING btree(created_at);

-- B-tree работает на равенстве, диапазоне и сортировке
SELECT * FROM orders WHERE created_at >= \'2026-01-01\'
                       AND created_at <  \'2026-02-01\'
ORDER BY created_at; -- range + order по индексу, без отдельной сортировки

-- Префиксный LIKE — индексируется
SELECT * FROM products WHERE name LIKE \'iPhone%\';

-- Полный поиск \'%pro%\' — индекс не помогает, нужен GIN/FULLTEXT',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.indexes',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое hash-индекс и когда он уместен?',
                'answer' => 'Hash-индекс хранит хэш-функцию от ключа и указатель на строку. Поиск по равенству — амортизированно O(1) и часто компактнее B-tree, но он принципиально не поддерживает диапазоны (<, >, BETWEEN), сортировку (ORDER BY) и префиксы (LIKE \'abc%\'), потому что хэш ломает порядок. В PostgreSQL hash-индексы есть и с версии 10 они полноценно crash-safe и реплицируются, но используют их редко: B-tree почти всегда сопоставим по скорости на равенство, а универсальнее. В MySQL/InnoDB пользовательский hash-индекс отсутствует, но InnoDB сам поддерживает Adaptive Hash Index в памяти как кеш над B-tree. Hash — основной механизм в Redis и memcached, как тип индекса в SQL — нишевый.',
                'code_example' => '-- PostgreSQL: явный hash-индекс
CREATE INDEX idx_users_email_hash ON users USING hash(email);

-- Работает только на равенство
SELECT * FROM users WHERE email = \'a@b.c\'; -- OK, index scan
SELECT * FROM users WHERE email LIKE \'a%\'; -- индекс НЕ используется
SELECT * FROM users ORDER BY email;        -- индекс НЕ используется',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.indexes',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое GIN и GiST индексы (PostgreSQL)?',
                'answer' => 'GIN (Generalized Inverted Index) - инвертированный индекс, хорош для значений, содержащих несколько подзначений: массивы, JSONB, full-text search. GiST (Generalized Search Tree) - универсальное дерево, подходит для геоданных (PostGIS), range types, ближайших соседей. GIN быстрее на чтение, GiST на запись.',
                'code_example' => '-- GIN для full-text search
CREATE INDEX idx_articles_tsv ON articles USING gin(to_tsvector(\'russian\', body));

-- GIN для JSONB
CREATE INDEX idx_data ON events USING gin(data);

-- GiST для геометрии
CREATE INDEX idx_locations ON places USING gist(geom);',
                'code_language' => 'sql',
                'difficulty' => 4,
                'topic' => 'database.indexes',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое composite (составной) индекс?',
                'answer' => 'Composite-индекс - индекс по нескольким столбцам. Порядок столбцов важен! Индекс (a, b, c) ускоряет запросы по a, по (a, b), по (a, b, c), но НЕ по b или по c отдельно. Это правило "leftmost prefix". Используется для частых комбинаций условий.',
                'code_example' => 'CREATE INDEX idx_orders_user_status ON orders(user_id, status, created_at);

-- Этот запрос использует индекс
SELECT * FROM orders WHERE user_id = 1 AND status = \'paid\';

-- А этот - нет (нет user_id впереди)
SELECT * FROM orders WHERE status = \'paid\';',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.indexes',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое partial index?',
                'answer' => 'Partial index - индекс по подмножеству строк, удовлетворяющих условию WHERE. Меньше по размеру, быстрее обновляется, применяется только к подходящим запросам. Полезен, когда часто фильтруют по конкретному значению (например, active = true).',
                'code_example' => '-- Индексируем только активных пользователей
CREATE INDEX idx_users_active ON users(email) WHERE active = true;

-- Только незавершённые заказы
CREATE INDEX idx_orders_pending ON orders(created_at) WHERE status = \'pending\';',
                'code_language' => 'sql',
                'difficulty' => 4,
                'topic' => 'database.indexes',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое expression (functional) index?',
                'answer' => 'Expression index - индекс по результату выражения, а не по самому столбцу. Помогает запросам, где в WHERE используется функция от столбца. Без такого индекса БД не может использовать обычный индекс на столбце.',
                'code_example' => '-- Чтобы поиск по нижнему регистру использовал индекс
CREATE INDEX idx_users_lower_email ON users(LOWER(email));

SELECT * FROM users WHERE LOWER(email) = \'ivan@mail.ru\';',
                'code_language' => 'sql',
                'difficulty' => 4,
                'topic' => 'database.indexes',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое covering index и INCLUDE?',
                'answer' => 'Covering index - индекс, который содержит все столбцы, нужные запросу, так что БД отвечает прямо из индекса, не обращаясь к таблице (index-only scan). В PostgreSQL и SQL Server есть синтаксис INCLUDE - дополнительные столбцы хранятся в индексе как payload, не участвуют в сортировке.',
                'code_example' => '-- email - ключ индекса, name и age - включенные
CREATE INDEX idx_users_email_inc ON users(email) INCLUDE (name, age);

-- Этот запрос - index-only scan
SELECT name, age FROM users WHERE email = \'ivan@mail.ru\';',
                'code_language' => 'sql',
                'difficulty' => 4,
                'topic' => 'database.indexes',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Когда индекс не используется?',
                'answer' => 'Индекс не работает, когда: применена функция к столбцу (WHERE LOWER(email) = ...) - нужен expression index; LIKE с ведущим % (WHERE name LIKE "%abc") - нет prefix; столбец в выражении (WHERE age + 1 = 30); неподходящий тип (CAST); очень малая селективность (большая часть таблицы - быстрее seq scan); статистика устарела (нужен ANALYZE).',
                'difficulty' => 4,
                'topic' => 'database.indexes',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое Index Scan и Sequential Scan?',
                'answer' => 'Sequential Scan (seq scan) - полное сканирование таблицы, чтение строк подряд. Подходит для маленьких таблиц или когда возвращается большая доля строк. Index Scan - чтение через индекс, идёт по нему, потом по указателям к таблице. Бывает Index Only Scan (когда все нужные данные есть в индексе) и Bitmap Index Scan (PostgreSQL/Oracle — собирает битмап позиций, потом за один проход читает таблицу; в MySQL/InnoDB этого режима нативно нет). Какой использовать решает планировщик.',
                'difficulty' => 4,
                'topic' => 'database.indexes',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое selectivity и cardinality?',
                'answer' => 'Терминология часто путается на собеседованиях, потому что слово "selectivity" используют в ДВУХ разных смыслах: 1) Predicate selectivity (selectivity предиката) - доля строк таблицы, проходящих фильтр (от 0 до 1). Чем НИЖЕ predicate selectivity (мало подходит) - тем эффективнее индекс под этот конкретный запрос: меньше строк читать. WHERE id = 5 на pk - selectivity ~ 1/N (отлично для индекса); WHERE active = true где 90% активных - selectivity 0.9 (индекс почти бесполезен, оптимизатор уйдёт в Seq Scan). 2) Column selectivity (= cardinality / N, "избирательность колонки") - насколько разнообразны значения. ВЫШЕ - обычно лучше для индексирования. Cardinality - просто число уникальных значений. Индекс на bool-столбце обычно бесполезен (cardinality = 2, низкая column selectivity). Индекс на email - отличный (high cardinality ≈ high column selectivity). Связь: predicate selectivity на equality по уникальной колонке = 1/cardinality. На собесе уточните, в каком смысле спрашивают - чтобы не запутаться "ниже-лучше" vs "выше-лучше".',
                'difficulty' => 4,
                'topic' => 'database.indexes',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Попадают ли NULL-значения в B-tree индекс? Можно ли искать по IS NULL через индекс?',
                'answer' => 'Зависит от СУБД - это классический cross-vendor вопрос на собеседованиях. PostgreSQL: NULL-ы попадают в B-tree индекс, и WHERE col IS NULL может использовать index scan. По умолчанию NULL сортируются последними (NULLS LAST для ASC, NULLS FIRST для DESC) - можно переопределить в CREATE INDEX. MySQL/InnoDB: тоже хранит NULL-ы в B-tree, и IS NULL может использовать индекс (MySQL умеет это с давних версий, оптимизатор показывает type=ref в EXPLAIN). Oracle: классически НЕ хранит NULL в обычном B-tree (если все колонки индекса NULL - запись не попадает в индекс), поэтому WHERE col IS NULL делает full scan. Обходной путь - функциональный индекс CREATE INDEX ... ON t(NVL(col, "x")) или составной индекс с константой (col, 1). SQL Server: хранит NULL в B-tree, IS NULL индексируется. Практический совет: для частых IS NULL / IS NOT NULL фильтров в PG используй partial index WHERE col IS NULL - индекс будет компактнее.',
                'code_example' => 'SELECT version(); -- PostgreSQL

CREATE INDEX idx_email ON users(email);
EXPLAIN SELECT * FROM users WHERE email IS NULL;
-- Index Scan using idx_email - PG умеет

-- Partial index только для NULL - очень компактный
CREATE INDEX idx_users_no_email ON users(id) WHERE email IS NULL;

-- Управление позицией NULL в сортировке
CREATE INDEX idx_users_created
  ON users(created_at DESC NULLS LAST);

-- В Oracle для WHERE col IS NULL по индексу нужен трюк:
-- CREATE INDEX idx_t ON t(col, 1);
-- WHERE col IS NULL AND 1=1; -- индекс используется',
                'code_language' => 'sql',
                'difficulty' => 4,
                'topic' => 'database.indexes',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Всегда ли индекс (A, B) бесполезен при WHERE B = 10? Что такое Index Skip Scan?',
                'answer' => 'Классическая теория B-tree говорит "leftmost prefix": композитный индекс (A, B) бесполезен для запроса WHERE B=10 - нужен либо отдельный индекс по B, либо WHERE с обоими столбцами. Это так в наивной реализации, потому что внутри B-tree значения B сгруппированы внутри каждой подгруппы по A, а не глобально отсортированы. Однако современные оптимизаторы умеют делать Index Skip Scan, когда КАРДИНАЛЬНОСТЬ ПЕРВОЙ КОЛОНКИ НИЗКАЯ. Идея: если у A только 2 уникальных значения ("M" и "F" в gender), оптимизатор перебирает уникальные значения A и для каждого делает обычный поиск по B - под капотом получается WHERE A="M" AND B=10 UNION ALL WHERE A="F" AND B=10. На индексе (gender, login_at) это работает; на индексе (user_id, login_at) с миллионом уникальных user_id - не сработает (skip scan на каждый user_id будет дороже full scan). Поддержка по СУБД. Oracle - есть с 9i (2001), документирован. MySQL - с 8.0.13 (2018), называется "Skip Scan range access method", виден в EXPLAIN как "Using index for skip scan". PostgreSQL - до PG 17 нативно НЕ умел; в PG 18 (релиз сент. 2025, commit 92fe23d93 за авторством Peter Geoghegan) добавили нативный btree skip scan: оптимизатор сам перебирает значения первого столбца составного индекса и для каждого делает обычный поиск по последующим. До PG 18 обходной путь - рекурсивный CTE ("loose index scan"). Полу-смежная, но другая фича - B-tree deduplication из PG 13: при низкой кардинальности первой колонки одинаковые ключи в листовых страницах дедуплицируются (хранится один ключ + posting list из TID), что кардинально уменьшает раздутие индексов вида (gender, last_login). SQL Server - есть в форме Skip Read для определённых сценариев. Практический вывод: если у вас Postgres - не полагайтесь на skip scan, создавайте отдельный индекс по B (или (B, A) если он чаще используется). На MySQL/Oracle для "леворукого" использования композитного индекса с низкокардинальной первой колонкой skip scan может спасти ситуацию, но всё равно лучше - правильный композит. Принцип проектирования: "сначала равенство, потом range" - WHERE A=1 AND B>10 хорошо ляжет на (A, B); порядок частоты использования - редко используемая колонка идёт последней или выносится в отдельный индекс.',
                'code_example' => '-- MySQL 8.0+: Index Skip Scan
CREATE TABLE users (
    id        INT PRIMARY KEY,
    gender    CHAR(1),       -- 2 уникальных значения, идеально для skip scan
    last_login DATETIME,
    INDEX idx_gender_login (gender, last_login)
);

-- Запрос только по второй колонке индекса
EXPLAIN SELECT id FROM users WHERE last_login > NOW() - INTERVAL 1 HOUR;
-- Extra: "Using index for skip scan" - оптимизатор сам нашёл способ

-- Проверка через EXPLAIN ANALYZE
EXPLAIN FORMAT=TREE SELECT * FROM users WHERE last_login > NOW();
-- Skip scan on idx_gender_login over last_login

-- ❌ Не сработает - высокая кардинальность первой колонки
CREATE INDEX idx_user_login ON events (user_id, created_at);
-- WHERE created_at > NOW() - skip scan по миллиону user_id
-- хуже full table scan, оптимизатор откажется

-- ✅ Лучшее решение - отдельный индекс если запрос часто только по B
CREATE INDEX idx_events_created ON events (created_at);

-- В Postgres до 18 skip scan нет - сразу делайте отдельный индекс
-- (PG 18+ умеет нативный btree skip scan)
-- если есть только индекс (gender, last_login), Postgres сделает Seq Scan

-- Принцип: equality columns first, range columns last
-- Хороший: WHERE status=\'active\' AND created_at > X с индексом (status, created_at)
-- Плохой:  WHERE created_at > X с индексом (status, created_at) без skip scan',
                'code_language' => 'sql',
                'difficulty' => 5,
                'topic' => 'database.indexes',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Какие типы индексов кроме B-Tree существуют в MySQL и когда их применяют?',
                'answer' => 'Hash-индекс ищет точные совпадения за O(1), но не поддерживает диапазоны и сортировку, и в InnoDB он управляется автоматически как Adaptive Hash Index, явно его не создают. FULLTEXT индекс рассчитан на поиск по словам в больших текстовых полях (с MATCH ... AGAINST), даёт стемминг и релевантность. Spatial-индекс на R-tree обслуживает геометрические типы (POINT, POLYGON) для запросов вроде "точки внутри прямоугольника". B-Tree остаётся выбором по умолчанию для всех остальных колонок и поддерживает и равенство, и диапазон, и ORDER BY.',
                'difficulty' => 4,
                'topic' => 'database.indexes',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Чем clustered index отличается от non-clustered и сколько их может быть на таблицу?',
                'answer' => 'Clustered index определяет физический порядок строк на диске — сами данные хранятся в листьях этого B-Tree. На таблицу он один, и в InnoDB это всегда первичный ключ (если PK не задан, MySQL выбирает первый UNIQUE NOT NULL индекс или скрытый ROW_ID). Non-clustered (secondary) индексы — отдельные структуры, листья которых хранят значения PK; чтобы достать остальные колонки, движок делает дополнительный поиск по кластеру. Из этого следует две практики: длинный первичный ключ распухает во всех secondary-индексах, а covering-индекс позволяет вообще обойтись без обращения к данным.',
                'difficulty' => 4,
                'topic' => 'database.indexes',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Как спроектировать составной индекс под фильтр (sku, status, created_at)?',
                'answer' => 'Сначала ставят колонку с равенством и максимальной селективностью (sku, который часто уникален), затем колонку равенства с меньшей селективностью (status с 15 значениями), и в самом конце — колонку диапазона (created_at). Такой порядок позволяет оптимизатору сразу сузить дерево поиска до точечного совпадения, а потом использовать диапазон по дате как range-сканирование внутри отобранной ветки. Если поставить created_at раньше, диапазонный фильтр обрежет дальнейшее использование индекса, и status/sku превратятся в фильтр после выборки. Index Skip Scan в MySQL 8 частично спасает, но рассчитывать на него по умолчанию не стоит.',
                'difficulty' => 4,
                'topic' => 'database.indexes',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'На какие колонки стоит ставить индекс?',
                'answer' => 'Кандидаты — колонки, которые часто стоят в WHERE, JOIN и ORDER BY горячих запросов. На foreign keys индекс почти всегда нужен: по ним идут JOIN-ы и проверки целостности при удалении/обновлении родителя. Лучше всего индекс работает на полях с высокой селективностью — много уникальных значений (email, user_id), мало совпадений на одно значение. На колонке с двумя-тремя значениями (boolean is_active, status с тремя статусами) обычный индекс почти бесполезен — оптимизатор всё равно уйдёт в Seq Scan; для редкого статуса лучше partial index. Каждый индекс замедляет INSERT/UPDATE, поэтому добавлять их «на всякий случай» не стоит.',
                'code_example' => '-- FK почти всегда индексируем
CREATE INDEX idx_orders_user_id ON orders(user_id);

-- Высокая селективность — хороший индекс
CREATE INDEX idx_users_email ON users(email);

-- Низкая селективность — partial index только для нужного значения
CREATE INDEX idx_orders_pending ON orders(created_at)
WHERE status = \'pending\';',
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.indexes',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое UNIQUE INDEX и чем он отличается от обычного индекса?',
                'answer' => 'UNIQUE INDEX делает то же самое, что и обычный индекс (ускоряет поиск по столбцу), но дополнительно запрещает вставлять дубли значений — попытка вставки или обновления, ведущая к повтору, упадёт с ошибкой. Под UNIQUE constraint в реляционных БД создаётся именно такой индекс. По одному столбцу пишут UNIQUE (email), по нескольким — составной UNIQUE (user_id, team_id), который запрещает только повтор пары целиком.',
                'code_example' => 'CREATE UNIQUE INDEX idx_users_email ON users(email);

-- Cоставной uniq: один пользователь = одна запись в команду
CREATE UNIQUE INDEX idx_memberships_user_team
  ON memberships(user_id, team_id);',
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.indexes',
            ],
        ];
    }
}
