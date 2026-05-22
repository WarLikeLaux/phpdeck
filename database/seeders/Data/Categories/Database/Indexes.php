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
                'answer' => '**`B-tree`** (точнее `B+ tree` в современных СУБД) — **сбалансированное многоуровневое дерево** с большим коэффициентом ветвления, где все ключи **отсортированы**.

**Устройство:**
- **внутренние узлы** — только ключи-разделители и указатели на детей;
- **листья** — собственно идентификаторы строк;
- листья **связаны двусвязным списком** → дешёвое **range-сканирование** (нашли начало диапазона, идём по соседям без возврата в корень).

**Сложность** — `O(log n)`. На миллиарде строк реальная высота дерева — **3–5 уровней** (несколько обращений к диску/буферному пулу).

**Что поддерживает:**
- `=`, `<`, `>`, `BETWEEN`, `IN`;
- `ORDER BY` (по индексированному префиксу);
- префиксный `LIKE \'abc%\'`.

**Что НЕ поддерживает:**
- `LIKE \'%abc%\'` (нужен `GIN`/`FULLTEXT`);
- функцию от колонки `WHERE LOWER(col) = ...` (нужен **expression index**).

**По умолчанию** — везде `B-tree`.',
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
                'answer' => '**Hash-индекс** хранит **хэш-функцию от ключа** и указатель на строку.

**Что поддерживает / не поддерживает:**

| Операция | Hash-индекс |
|---|---|
| `=` (равенство) | **да**, амортизированно `O(1)` |
| `<`, `>`, `BETWEEN` | нет |
| `ORDER BY` | нет |
| `LIKE \'abc%\'` | нет |

**Причина:** хэш-функция **ломает порядок**.

**Поддержка в СУБД:**
- **PostgreSQL** — `USING hash`; с PG 10 crash-safe и реплицируется, но используют **редко**: `B-tree` сопоставим по скорости на `=` и универсальнее;
- **MySQL/InnoDB** — пользовательский hash-индекс **отсутствует**, но InnoDB сам поддерживает **Adaptive Hash Index** в памяти как кеш над `B-tree`;
- **Redis**, **Memcached** — hash как основной механизм.

**Вывод:** как тип индекса в SQL — **нишевый**, почти всегда хватает `B-tree`.',
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
                'answer' => 'В PostgreSQL кроме `B-tree` есть несколько специализированных типов индексов под нестандартные данные.

| Тип | Что хорошо умеет | Типичные данные |
|---|---|---|
| **`GIN`** (Generalized **Inverted** Index) | поиск по **подзначениям внутри** значения | массивы (`int[]`, `text[]`), `jsonb`, **full-text** (`tsvector`), `pg_trgm` |
| **`GiST`** (Generalized **Search Tree**) | геометрия, range types, **ближайшие соседи** (`<->`) | `geometry` (PostGIS), `tsrange`/`int4range`, `point` |
| **`SP-GiST`** | дисбалансированные деревья | quad-tree, IP-префиксы, **phone-prefix** поиск |
| **`BRIN`** | **очень крупные** таблицы, упорядоченные по времени | append-only логи, time-series, partitioned данные |

**`GIN` vs `GiST` — компромисс:**

| | `GIN` | `GiST` |
|---|---|---|
| Чтение | **быстрее** | медленнее |
| Запись (`INSERT`/`UPDATE`) | **медленнее** (`fastupdate` смягчает) | быстрее |
| Размер | больше | меньше |
| Lossy | нет | бывает (требует recheck) |

**Правило выбора:**
- **много чтений, редкие записи** (поиск по тегам, full-text) — `GIN`;
- **часто пишем, разнородные операторы** (геометрия, ближайший сосед) — `GiST`.',
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
                'answer' => '**Composite-индекс** — индекс по **нескольким столбцам**.

**Главное правило — `leftmost prefix`:** порядок столбцов **важен**.

Индекс `(a, b, c)` ускоряет запросы по:
- `a`;
- `(a, b)`;
- `(a, b, c)`;
- `a` + сортировка по `b`/`c`.

**НЕ ускоряет** запросы:
- по `b` или `c` отдельно;
- по `(b, c)` без `a`.

**Как выбрать порядок колонок:**
1. сначала колонки, по которым **`=` (равенство)**;
2. затем — по которым **диапазон** (`<`, `>`, `BETWEEN`);
3. затем — по которым `ORDER BY`.

**Применение:** частые комбинации условий в `WHERE`. Например, для `WHERE user_id = ? AND status = ? ORDER BY created_at` — индекс `(user_id, status, created_at)`.',
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
                'answer' => '**Partial index** — индекс по **подмножеству строк**, удовлетворяющих условию `WHERE` в его определении.

**Свойства:**
- **меньше** по размеру — меньше места на диске, лучше кешируется;
- **быстрее обновляется** — `INSERT`/`UPDATE` строк **вне условия** вообще не трогают индекс;
- применяется **только к запросам, где `WHERE` совместим** с условием индекса.

**Когда полезен:**
- частая фильтрация по **конкретному значению** низкоселективной колонки (`active = true`, `status = \'pending\'`);
- большая часть таблицы — «архив», запрашиваем только «свежее» (`deleted_at IS NULL`);
- `IS NULL` на колонке, где большинство значений `NULL` — индекс компактнее.

**Поддержка:**
- **PostgreSQL** — с давних версий;
- **MySQL/InnoDB** — **нет** нативной поддержки (есть в MariaDB);
- **SQL Server** — называется **filtered index**.

**Грабли:** условие в `WHERE` запроса **должно дословно совпадать** с условием индекса (или быть его более узким подмножеством) — иначе оптимизатор не сможет использовать. `WHERE active = true` ≠ `WHERE active <> false` для матчинга.',
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
                'answer' => '**Expression (functional) index** — индекс **по результату выражения**, а не по самому столбцу.

**Зачем:** обычный индекс на колонке `email` **не используется**, если в `WHERE` идёт **функция от колонки**:

```sql
SELECT * FROM users WHERE LOWER(email) = \'ivan@mail.ru\';
-- индекс на (email) не подходит - PG видит LOWER(email), а не email
```

**Решение** — индексировать **выражение целиком**:

```sql
CREATE INDEX idx_users_lower_email ON users (LOWER(email));
-- Теперь запрос идёт через индекс
```

**Типичные случаи:**
- **регистронезависимый** поиск — `LOWER(col)` или `citext` тип;
- **JSON-поля** — `(data->>\'user_id\')`, `((data->>\'price\')::int)`;
- **частичные даты** — `DATE(created_at)` для группировки по дням;
- **триграммы** — `gin (col gin_trgm_ops)` для `LIKE \'%abc%\'`.

**Поддержка:**
- **PostgreSQL** — давно, любые **`IMMUTABLE`** функции;
- **MySQL** — с **8.0** (functional index);
- **SQLite** — с 3.9.

**Грабли:**
- выражение в индексе и в запросе должно **точно совпадать** — `LOWER(email)` ≠ `lower(email)` оптимизатор справится, но `LOWER(email) || \'\'` уже нет;
- выражение должно быть **`IMMUTABLE`** (детерминированным) — `NOW()` нельзя.',
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
                'answer' => '**Covering index** — индекс, который **содержит все столбцы, нужные запросу**: и `WHERE`-фильтр, и `SELECT`-список. БД отвечает **прямо из индекса**, **не обращаясь к таблице** — это `Index Only Scan` в PG / `Using index` в MySQL EXPLAIN.

**Синтаксис `INCLUDE`** (PG 11+, SQL Server) — добавить столбцы **как payload**, не участвующий в сортировке/поиске:

```sql
CREATE INDEX idx_users_email_inc ON users (email) INCLUDE (name, age);

-- Этот запрос - Index Only Scan
SELECT name, age FROM users WHERE email = \'ivan@mail.ru\';
```

**Без `INCLUDE`** — те же столбцы можно положить **в ключ**, но это:
- увеличит размер каждого узла дерева (медленнее);
- участвует в сортировке (бессмысленно, если в `WHERE`/`ORDER BY` не используется).

**Сравнение:**

| | Ключ индекса `(email)` | `(email, name, age)` | `(email) INCLUDE (name, age)` |
|---|---|---|---|
| Поиск по `email` | да | да | да |
| Index Only Scan для `SELECT name, age` | **нет** (heap-fetch) | да | **да** |
| Размер B-tree узлов | минимальный | большой | меньше, чем составной |
| Сортирует по `name, age` | — | да | **нет** (payload) |

**Грабли в PostgreSQL:** `Index Only Scan` работает, только если **visibility map** говорит, что страница «all-visible». После активной записи без `VACUUM` PG всё равно идёт в heap для проверки `xmin`/`xmax`.

**MySQL/InnoDB** — covering index «бесплатный»: secondary index хранит PK как payload, поэтому `SELECT id` через любой secondary index — Using index.',
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
                'answer' => 'Шесть классических причин, по которым `B-tree` индекс **не используется**, хотя «должен бы».

| Причина | Пример «плохого» запроса | Что делать |
|---|---|---|
| Функция от столбца | `WHERE LOWER(email) = ?` | **expression index** на `LOWER(email)` |
| `LIKE` с ведущим `%` | `WHERE name LIKE \'%abc\'` | `pg_trgm` + `GIN`, или **FULLTEXT** |
| Столбец в выражении | `WHERE age + 1 = 30` | переписать: `WHERE age = 29` |
| Несовпадение типов (implicit `CAST`) | `WHERE user_id = \'42\'` при `int` колонке | использовать правильный тип |
| **Низкая селективность** | `WHERE active = true` где 90% — true | **partial index** или вообще без индекса |
| Устаревшая статистика | оптимизатор недооценивает фильтр | **`ANALYZE`** таблицы |

**Как диагностировать:**
- **`EXPLAIN ANALYZE`** в PG / **`EXPLAIN FORMAT=TREE`** в MySQL — увидеть `Seq Scan` вместо `Index Scan`;
- проверить `pg_stat_user_indexes` — есть ли вообще обращения к индексу (`idx_scan = 0` — индекс не используется, можно удалять);
- в `EXPLAIN` смотреть **`Filter`** (условие применилось после чтения) vs **`Index Cond`** (условие ушло в индекс).

**Ещё две менее очевидные причины:**
- `OR` между разными колонками — `WHERE a = 1 OR b = 2` — индексы по `a` и по `b` не объединяются (в PG спасает **`Bitmap Or`**);
- **`NOT IN` с NULL** — становится `NULL`, теряет селективность.',
                'difficulty' => 4,
                'topic' => 'database.indexes',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое Index Scan и Sequential Scan?',
                'answer' => 'Способы, которыми оптимизатор может прочитать таблицу.

| Метод | Как работает | Когда выбирается |
|---|---|---|
| **`Sequential Scan`** (seq scan) | читает **все** страницы таблицы подряд | маленькая таблица **или** большая доля строк подходит фильтру |
| **`Index Scan`** | идёт по `B-tree`, для каждой найденной строки — **random read** в heap | средняя селективность, нужна сортировка |
| **`Index Only Scan`** | данные **полностью в индексе** — heap не читается | covering index + актуальная visibility map (PG) |
| **`Bitmap Index Scan`** | строит **битмап позиций** в индексе, потом **`Bitmap Heap Scan`** читает страницы **по порядку** | много строк подходит, надо избежать random I/O; есть в **PG/Oracle**, **в MySQL/InnoDB нативно нет** |

**Почему планировщик может выбрать `Seq Scan` даже при наличии индекса:**
- **много строк** подходят фильтру — `random reads` по heap дороже `sequential reads`;
- маленькая таблица помещается в **shared buffers** — индекс не даёт выигрыша;
- устаревшая статистика — `ANALYZE` спасает.

**Что решает оптимизатор:** оценивает **стоимость** каждого плана по **статистике** (`pg_stats` / `INFORMATION_SCHEMA.STATISTICS`) и выбирает самый дешёвый. Видишь странное — `EXPLAIN (ANALYZE, BUFFERS)` показывает реальную картину.',
                'difficulty' => 4,
                'topic' => 'database.indexes',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое selectivity и cardinality?',
                'answer' => 'Терминология **часто путается** на собесах, потому что слово **«selectivity»** используют в **двух разных смыслах**.

**1. `Cardinality`** — просто **число уникальных значений** в колонке. На таблице из 1М пользователей:
- `email` — cardinality ≈ 1М (почти все уникальные);
- `country` — cardinality ≈ 200;
- `gender` — cardinality = 2;
- `active` — cardinality = 2.

**2. `Predicate selectivity`** — **доля строк**, проходящих **конкретный фильтр** (от 0 до 1):
- `WHERE id = 5` на PK → **`1/N`** (отлично для индекса);
- `WHERE active = true` где 90% true → **`0.9`** (индекс почти бесполезен);
- **чем НИЖЕ** predicate selectivity, тем **выгоднее** индекс под этот запрос.

**3. `Column selectivity`** = `cardinality / N` — насколько **разнообразны значения** в колонке:
- `email` → high column selectivity (≈ 1.0);
- `gender` → low (≈ 2/N);
- **чем ВЫШЕ** column selectivity, тем **лучше колонка кандидат** для индексирования.

**Сравнительная таблица:**

| Термин | Шкала | Что значит | Правило для индекса |
|---|---|---|---|
| `Cardinality` | целое число | уникальных значений в колонке | больше — лучше |
| `Column selectivity` | 0..1 | `cardinality / N` | **выше** — лучше |
| `Predicate selectivity` | 0..1 | доля строк под `WHERE` | **ниже** — лучше |

**Связь:** для `WHERE col = ?` на уникальной колонке — `predicate selectivity = 1 / cardinality`.

**На собесе:** **уточняйте**, в каком смысле спрашивают — чтобы не запутаться **«ниже-лучше»** vs **«выше-лучше»**.',
                'difficulty' => 4,
                'topic' => 'database.indexes',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Попадают ли NULL-значения в B-tree индекс? Можно ли искать по IS NULL через индекс?',
                'answer' => '**Зависит от СУБД** — классический cross-vendor вопрос на собеседовании.

| СУБД | Хранит `NULL` в B-tree? | `WHERE col IS NULL` идёт через индекс? |
|---|---|---|
| **PostgreSQL** | **да** | **да** (Index Scan) |
| **MySQL/InnoDB** | **да** | **да** (`type=ref` в `EXPLAIN`) |
| **SQL Server** | **да** | **да** |
| **Oracle** | **нет** в одноколоночном — если **все** колонки `NULL`, запись **не попадает** | **нет** (Full Table Scan), нужны трюки |

**Oracle — обходные пути:**
- `CREATE INDEX idx ON t(NVL(col, \'x\'))` — функциональный индекс с заменой `NULL`;
- `CREATE INDEX idx ON t(col, 1)` — составной с константой, тогда запись всегда попадает в индекс.

**PostgreSQL — `NULLS FIRST / LAST`:**
- по умолчанию для `ASC` — `NULLS LAST`, для `DESC` — `NULLS FIRST`;
- можно переопределить в **самом индексе**: `CREATE INDEX ... ON t(created_at DESC NULLS LAST)`;
- **`ORDER BY`** в запросе и индексе **должны совпадать**, иначе Index Scan не подойдёт.

**Практический совет для PG:** для частых **`IS NULL`** фильтров — **partial index**:

```sql
CREATE INDEX idx_users_no_email ON users(id) WHERE email IS NULL;
-- индекс крошечный, содержит только нужные строки
```',
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
                'answer' => '**Классическая теория B-tree** — правило **`leftmost prefix`**: композитный индекс `(A, B)` бесполезен для запроса `WHERE B = 10` — нужен либо отдельный индекс по `B`, либо `WHERE` с **обоими** столбцами.

**Почему так в наивной реализации:** внутри `B-tree` значения `B` **сгруппированы внутри** каждой подгруппы по `A`, **а не глобально отсортированы**.

**Index Skip Scan — современная оптимизация.** Когда **кардинальность первой колонки низкая**, оптимизатор сам перебирает уникальные значения `A` и для каждого делает поиск по `B`. Под капотом получается:

```sql
-- Эквивалент Skip Scan для индекса (gender, login_at):
WHERE gender = \'M\' AND last_login > NOW()
UNION ALL
WHERE gender = \'F\' AND last_login > NOW()
```

**Когда работает:**
- `(gender, last_login)` с **2** уникальными значениями `gender` — **OK**;
- `(user_id, created_at)` с **миллионом** уникальных `user_id` — **хуже full scan**, оптимизатор откажется.

**Поддержка по СУБД:**

| СУБД | Поддержка skip scan |
|---|---|
| **Oracle** | с **9i** (2001), документирован |
| **MySQL** | с **8.0.13** (2018), «Skip Scan range access method», в `EXPLAIN` — `Using index for skip scan` |
| **PostgreSQL** | до **PG 17** — нет; в **PG 18** (сент. 2025) добавили нативный btree skip scan (Peter Geoghegan, commit `92fe23d93`) |
| **SQL Server** | в форме **Skip Read** для определённых сценариев |

**До PG 18 в PostgreSQL** — обходной путь через **рекурсивный CTE** («loose index scan»).

**Полу-смежная фича — B-tree deduplication (PG 13+):** при низкой кардинальности первой колонки одинаковые ключи в листовых страницах **дедуплицируются** (один ключ + posting list из TID) — кардинально уменьшает **раздутие индексов** вида `(gender, last_login)`.

**Принцип проектирования индексов:**
1. **Equality columns first, range columns last** — `WHERE A=1 AND B>10` хорошо ляжет на `(A, B)`;
2. **Редко используемая колонка** — последней или **в отдельный индекс**;
3. Не полагайся на skip scan **в PG до 18** — создавай отдельный индекс по `B` (или `(B, A)`, если он чаще используется);
4. На MySQL/Oracle skip scan **может спасти**, но всё равно лучше — правильный композит.',
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
                'answer' => 'В MySQL кроме **`B-tree`** существует четыре типа индексов под специфические задачи.

| Тип | Что умеет | Когда применять | Ограничения |
|---|---|---|---|
| **`B-Tree`** | `=`, `<`, `>`, `BETWEEN`, `ORDER BY`, префиксный `LIKE` | **по умолчанию для всего** | — |
| **`HASH`** | только `=`, амортизированно `O(1)` | автоматический **Adaptive Hash Index** в InnoDB | нет range/sort/`LIKE`; явно создать пользовательский HASH в InnoDB **нельзя** (только в MEMORY engine) |
| **`FULLTEXT`** | поиск по словам через `MATCH ... AGAINST`, стемминг, релевантность | большие текстовые поля (`TEXT`, `VARCHAR`) — статьи, описания | `MyISAM` и `InnoDB` (с 5.6); минимальная длина слова, стоп-листы |
| **`SPATIAL`** (`R-tree`) | геометрические запросы — «точка в прямоугольнике», `ST_Contains`, `ST_Distance` | типы `POINT`, `POLYGON`, `GEOMETRY` | колонка должна быть `NOT NULL` |

**Adaptive Hash Index в InnoDB** — нюанс, который часто спрашивают:
- InnoDB **сам** строит hash-индекс **поверх B-tree** в памяти, если видит много одинаковых запросов;
- управляется автоматически, **отключить** можно через `innodb_adaptive_hash_index = OFF`;
- помогает на точечных `=`-запросах с горячими ключами.

**По умолчанию для всех колонок** — **`B-Tree`**: универсален, поддерживает и равенство, и диапазон, и `ORDER BY`, и префиксный `LIKE \'abc%\'`.',
                'difficulty' => 4,
                'topic' => 'database.indexes',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Чем clustered index отличается от non-clustered и сколько их может быть на таблицу?',
                'answer' => '**Сравнение:**

| | `Clustered Index` | `Non-Clustered (Secondary)` |
|---|---|---|
| Что хранится в листьях | **сами строки** таблицы | значение **PK** + ключи индекса |
| Сколько на таблицу | **ровно 1** | сколько угодно |
| Определяет порядок строк | **да** (физический порядок на диске) | нет |
| Чтение колонок не из индекса | **бесплатно** (уже в листе) | **дополнительный lookup** по PK |

**В InnoDB clustered-индекс — всегда первичный ключ.** Если PK не задан:
1. движок берёт **первый `UNIQUE NOT NULL`** индекс;
2. иначе создаёт **скрытый 6-байтовый `ROW_ID`**.

**В PostgreSQL** clustered-индекса **нет** — все таблицы **heap**, индексы хранят `ctid` (физический адрес версии). Команда `CLUSTER table USING idx` **одноразово** упорядочивает строки по индексу, но при `UPDATE`/`INSERT` порядок не поддерживается.

**Практические следствия в InnoDB:**

1. **Длинный PK раздувает все secondary-индексы** — каждый листовой узел secondary хранит PK как payload. Поэтому в InnoDB предпочитают **компактный `BIGINT`-PK**, а не `UUID` в `VARCHAR(36)`;
2. **Covering index экономит lookup** — если все нужные колонки есть в secondary-индексе, движок не обращается к clustered;
3. **Sequential PK** (`AUTO_INCREMENT`, `BIGINT`) даёт **append-only вставки** в конец B-tree — минимум page splits, лучше fillfactor;
4. **Random PK** (UUIDv4) — вставки в **середину** дерева, page splits, фрагментация. **UUIDv7** или **`COMB GUID`** решают (упорядочены по времени).',
                'difficulty' => 4,
                'topic' => 'database.indexes',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Как спроектировать составной индекс под фильтр (sku, status, created_at)?',
                'answer' => '**Правило проектирования композитных индексов** для запроса вида `WHERE sku=? AND status=? AND created_at > ?`:

1. **`=` с максимальной селективностью** — `sku` (часто уникален или почти);
2. **`=` с меньшей селективностью** — `status` (15 значений);
3. **`range`/`ORDER BY`** — `created_at` (диапазон по дате) — **последней**.

**Итоговый индекс:** `(sku, status, created_at)`.

**Почему именно такой порядок:**
- движок **сразу сужает** дерево до точечного совпадения по `sku`;
- внутри уже узкой подгруппы — `=` по `status`;
- внутри неё — **range-сканирование** по `created_at`.

**Почему НЕ `(created_at, sku, status)`:**
- диапазонный фильтр в начале **обрезает** дальнейшее использование индекса — `B-tree` упорядочен по `created_at`, и значения `sku`, `status` **не сгруппированы** в найденном диапазоне;
- `sku`/`status` превратятся в **`Filter` после выборки**, а не в `Index Cond`.

**Принцип в одну строку:** **«equality columns first, range columns last».**

**Что насчёт `ORDER BY` в запросе:**
- если запрос `... ORDER BY created_at DESC LIMIT 10` — индекс `(sku, status, created_at)` подходит (внутри найденной подгруппы `created_at` уже отсортирован);
- если разные запросы используют разную сортировку — может понадобиться **несколько индексов**.

**Index Skip Scan в MySQL 8** частично спасает «леворукое» использование индекса, но рассчитывать на него **по умолчанию не стоит** — проектируй индекс под **самый частый/тяжёлый** запрос.',
                'code_example' => '-- Правильный композит
CREATE INDEX idx_orders_sku_status_created
  ON orders (sku, status, created_at);

-- Полностью покрывается индексом
EXPLAIN SELECT * FROM orders
WHERE sku = \'ABC-123\' AND status = \'paid\'
  AND created_at >= \'2026-01-01\';
-- type=range, key=idx_orders_sku_status_created

-- Антипаттерн - range первым
CREATE INDEX idx_bad ON orders (created_at, sku, status);
-- sku и status пойдут в Filter, не в Index Cond',
                'code_language' => 'sql',
                'difficulty' => 4,
                'topic' => 'database.indexes',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'На какие колонки стоит ставить индекс?',
                'answer' => '**Кандидаты на индекс** — колонки, которые часто стоят в:
- `WHERE` — фильтрация;
- `JOIN ... ON` — соединение;
- `ORDER BY` — сортировка;
- `GROUP BY` — группировка.

**Почти всегда индексируют:**
- **`FOREIGN KEY`** — по ним идут `JOIN`-ы и проверки при удалении/обновлении родителя;
- колонки с **высокой селективностью** (много уникальных значений: `email`, `user_id`).

**Плохие кандидаты:**
- `boolean` (`is_active`) или `status` с 2-3 значениями — оптимизатор всё равно уйдёт в `Seq Scan`. Для редкого значения лучше **partial index** (`WHERE status = \'pending\'`).

**Цена индекса:**
- занимает **место** на диске;
- замедляет `INSERT` / `UPDATE` / `DELETE` (надо обновлять и индекс).

Не добавляй индексы «на всякий случай» — только под **реальные горячие запросы**.',
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
                'answer' => '**`UNIQUE INDEX`** делает то же, что обычный индекс — **ускоряет поиск** — но дополнительно **запрещает дубли**. Попытка `INSERT`/`UPDATE`, приводящая к повтору значения, упадёт с ошибкой `duplicate key`.

Под **`UNIQUE` constraint** в реляционных БД создаётся именно такой индекс — это **два названия одного механизма**.

**Формы:**
- по **одной колонке**: `UNIQUE (email)` — уникальный email на всю таблицу;
- **составной**: `UNIQUE (user_id, team_id)` — запрещает повтор **пары**, но одно `user_id` может быть в разных командах.

**`NULL`-нюанс:** в PostgreSQL и MySQL `NULL` не равен `NULL`, поэтому несколько `NULL`-ов в уникальной колонке **разрешены**.',
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
