<?php

namespace Database\Seeders\Data\Categories\Database;

class Mysql
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Базы данных',
                'question' => 'Чем отличается InnoDB от MyISAM в MySQL?',
                'answer' => '**InnoDB** — движок MySQL **по умолчанию** с MySQL 5.5 (2010). **MyISAM** — старый движок, остался только в системных таблицах `mysql.*` и легаси.

| | **InnoDB** | **MyISAM** |
|---|---|---|
| Транзакции / ACID | да | **нет** |
| `FOREIGN KEY` | да, с проверкой | нет |
| Блокировки | **row-level** | **table-level** |
| Concurrency | десятки тысяч писателей | один писатель на таблицу |
| `MVCC` | да (неблокирующее чтение) | нет |
| Crash recovery | redo / undo log | нет, нужен `REPAIR TABLE` |
| `FULLTEXT` | да (с 5.6) | да |

**На практике:** **всегда `InnoDB`**. «Плюсы» MyISAM (компактнее на диске, чуть быстрее на чистом `SELECT`) сегодня **не актуальны** — все важные фичи (транзакции, FK, crash recovery) перевешивают.',
                'code_example' => '-- Узнать движок таблицы
SELECT engine FROM information_schema.tables
WHERE table_schema = DATABASE() AND table_name = \'orders\';

-- Создать с явным движком (по умолчанию InnoDB)
CREATE TABLE orders (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) -- FK — только InnoDB
) ENGINE=InnoDB;

-- Конвертация MyISAM → InnoDB
ALTER TABLE legacy_table ENGINE=InnoDB;',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.mysql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое Clustered index в InnoDB?',
                'answer' => '**Кластерный индекс** = строки таблицы **физически хранятся в порядке `PRIMARY KEY`**. Сам индекс **и есть таблица** — листовые страницы B-tree содержат **целые строки данных**, а не указатели на них.

**Последствия для производительности:**
- **`PK lookup` — самый быстрый** доступ: один спуск по B-tree → строка уже в листе, лишнего I/O нет;
- **вторичные (secondary) индексы** хранят в качестве «ссылки на строку» **значение `PK`**, а не физический адрес — каждый lookup по secondary index делает **два спуска**: secondary → PK → строка;
- **range-сканы по `PK`** идут по соседним страницам — отличная **локальность диска**.

**Design rules:**

| Правило | Почему |
|---|---|
| **PK короткий** | дублируется в каждом secondary index — длинный PK раздувает все индексы |
| **PK последовательный** (`BIGINT AUTO_INCREMENT`, `ULID`) | вставка всегда в конец B-tree → нет page splits, локальность записи |
| **PK не меняется** | при `UPDATE PK` строка физически переезжает + обновляются все secondary |
| **`UUID v4` как PK — анти-паттерн** | рандомные вставки по всему B-tree, fragmentation, дорогие inserts |

**Если PK явно не задан** — InnoDB возьмёт **первый `UNIQUE NOT NULL`** или сгенерит скрытый **6-байтный `DB_ROW_ID`** (его лучше не допускать — он недоступен через SQL).

**В PostgreSQL** наоборот — таблицы хранятся в **heap** (без кластеризации); `CLUSTER table USING idx` — разовая операция, не поддерживается автоматически.',
                'code_example' => '-- Хороший PK: компактный, монотонный
CREATE TABLE orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT,
    INDEX idx_user (user_id) -- secondary хранит (user_id, id)
) ENGINE=InnoDB;

-- Антипаттерн: UUID v4 как PK
-- CREATE TABLE bad (id CHAR(36) PRIMARY KEY, ...);
-- Случайные вставки по всему дереву → page splits, медленная запись.

-- Лучше для распределённых ID — UUID v7 / ULID (монотонные)
-- или BIGINT + отдельная UNIQUE-колонка под UUID.',
                'code_language' => 'sql',
                'difficulty' => 4,
                'topic' => 'database.mysql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Как выбрать storage engine в MySQL?',
                'answer' => 'В **99% случаев — `InnoDB`** (он по умолчанию). Остальные движки нужны под **специфические задачи**:

| Движок | Когда брать |
|---|---|
| **`InnoDB`** | дефолт; транзакции, `FK`, row-level locks, `MVCC` |
| **`MyISAM`** | редкий легаси-кейс «много чтения, нет записи» |
| **`Memory`** (`HEAP`) | временные **in-memory** таблицы (теряются при рестарте) |
| **`Archive`** | архивные данные только на **чтение/append**, очень компактно |
| **`NDB`** | кластерный **MySQL Cluster** (распределённый shared-nothing) |
| **`CSV`** | таблица = CSV-файл (импорт/экспорт) |

**Правило:** если в собесе спрашивают «какой движок» — отвечай **`InnoDB`** и кратко обоснуй: транзакции + `FK` + row-level lock.',
                'code_example' => 'CREATE TABLE users (
    id INT PRIMARY KEY,
    name VARCHAR(100)
) ENGINE = InnoDB;',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.mysql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'REPEATABLE READ в InnoDB и gap locks?',
                'answer' => 'По стандарту SQL **`REPEATABLE READ`** защищает от **non-repeatable read**, но **не от phantoms**. **В InnoDB механизм двойной** и зависит от типа чтения.

**(1) Consistent reads** (обычный `SELECT` без блокировок):
- работает **MVCC-снимок** — транзакция читает версии **на момент первого `SELECT`**;
- новые строки от других транзакций **просто не существуют** в этом snapshot;
- **phantoms исключены**, **без блокировок** на стороне читателя.

**(2) Locking reads** (`SELECT ... FOR UPDATE`, `LOCK IN SHARE MODE`, `UPDATE`/`DELETE` по диапазону):
- включаются **next-key locks** = **record lock** + **gap lock** на промежутках между значениями индекса;
- блокируются не только существующие строки, **но и «пустоты» между ними**;
- вторая транзакция **не сможет вставить** в защищённый диапазон.

**Пример next-key lock:**

```sql
-- Транзакция T1
START TRANSACTION;
SELECT * FROM t WHERE x BETWEEN 5 AND 10 FOR UPDATE;
-- Заблокировано: строки с x ∈ [5..10] + промежутки (5,6), (6,7) ... (10,∞]

-- Транзакция T2 - повиснет
INSERT INTO t (x) VALUES (7); -- ⚠ block
```

**Сравнение уровней в InnoDB:**

| Уровень | MVCC consistent reads | Locking reads | Phantoms возможны? |
|---|---|---|---|
| `READ COMMITTED` | свежий snapshot **на каждый `SELECT`** | **только row locks** | **да** |
| `REPEATABLE READ` (default) | snapshot **на всю транзакцию** | **next-key + gap locks** | **нет** (даже для locking reads) |
| `SERIALIZABLE` | все `SELECT` → `LOCK IN SHARE MODE` | full locking | нет |

**Боль gap locks:** **частая причина неожиданных deadlock-ов** под нагрузкой — две транзакции цепляются за смежные gap-ы и блокируют друг друга. На `READ COMMITTED` gap locks **отключены** (только row locks) — но возвращаются phantoms.

**Уникальная фишка InnoDB** — именно **MVCC + gap locks на одном уровне изоляции**. В PostgreSQL `REPEATABLE READ` решает фантомы **только через MVCC** (snapshot transactions), gap locks отсутствуют.',
                'difficulty' => 5,
                'topic' => 'database.mysql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое ONLY_FULL_GROUP_BY и почему он часто ломает легаси-MySQL-запросы?',
                'answer' => '**`ONLY_FULL_GROUP_BY`** — режим в **`sql_mode`** MySQL (**включён по умолчанию с MySQL 5.7.5**), который заставляет соблюдать стандарт SQL для `GROUP BY`.

**Правило:** каждая колонка в `SELECT`, которая **не агрегат** (`SUM`/`COUNT`/`AVG`/`MIN`/`MAX`) и **не константа**, должна:
- **либо** присутствовать в `GROUP BY`;
- **либо** быть **функционально зависимой** от `GROUP BY` (т.е. однозначно определяться через `PK` или `UNIQUE NOT NULL`).

**Что было до 5.7.5:** MySQL **молча возвращал произвольное значение** для несгруппированных колонок:

```sql
SELECT u.id, u.name, COUNT(*) FROM users u JOIN orders o ON ... GROUP BY u.id;
-- u.name = первое попавшееся (или вообще ни от куда) — UB!
```

Это **классический источник скрытых багов в отчётах**.

**С включённым режимом** запрос валится с **ошибкой 1055**:
> `Expression #N of SELECT list is not in GROUP BY clause and contains nonaggregated column`

**Четыре способа починить:**

| # | Способ | Когда уместен |
|---|---|---|
| 1 | Добавить **все non-aggregate колонки** в `GROUP BY` | если по бизнесу значения и так одинаковые в группе |
| 2 | Обернуть в **`ANY_VALUE(col)`** | явно: «значение не важно, дай любое» |
| 3 | Переписать через **window functions** (`OVER (PARTITION BY ...)`) | MySQL 8+, элегантно сохраняет все строки |
| 4 | **Выключить режим** в `sql_mode` | **не рекомендуется** на проде |

**Антипаттерн** на собесе:
```sql
SELECT * FROM orders GROUP BY user_id;
-- Нарушает ONLY_FULL_GROUP_BY И возвращает непредсказуемые данные.
```',
                'code_example' => '-- ❌ Сломается при ONLY_FULL_GROUP_BY (1055)
SELECT u.id, u.name, u.email, COUNT(o.id) AS orders
FROM users u LEFT JOIN orders o ON o.user_id = u.id
GROUP BY u.id;

-- ✅ Все non-aggregate в GROUP BY
SELECT u.id, u.name, u.email, COUNT(o.id) AS orders
FROM users u LEFT JOIN orders o ON o.user_id = u.id
GROUP BY u.id, u.name, u.email;

-- ✅ ANY_VALUE - "не важно какое значение"
SELECT u.id, ANY_VALUE(u.name) AS name, COUNT(o.id) AS orders
FROM users u LEFT JOIN orders o ON o.user_id = u.id
GROUP BY u.id;

-- ✅ Window function вместо GROUP BY (MySQL 8+)
SELECT id, name, email,
       COUNT(*) OVER (PARTITION BY id) AS orders
FROM users;

-- Проверить текущий sql_mode
SELECT @@sql_mode;',
                'code_language' => 'sql',
                'difficulty' => 4,
                'topic' => 'database.mysql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'В чём разница между MySQL DATETIME и TIMESTAMP, и какая боль возникает при смене таймзоны сервера?',
                'answer' => 'Оба хранят дату и время, но **семантика принципиально разная**.

| | **`TIMESTAMP`** | **`DATETIME`** |
|---|---|---|
| Размер | 4 байта (+0..3 на дробные сек) | 5 байт (+0..3 на дробные сек, с 5.6.4) |
| Диапазон | **1970-01-01 — 2038-01-19** (Y2K38) | **1000 — 9999** годы |
| Хранение | **внутри в UTC** | **как есть**, без преобразований |
| При записи | конвертация из `session time_zone` → UTC | байт-в-байт |
| При чтении | конвертация UTC → `session time_zone` | байт-в-байт |
| `DEFAULT CURRENT_TIMESTAMP` | да | да (с 5.6.5) |
| Реакция на смену TZ сервера | **значения «сдвигаются»** | **не меняются** |

**Боль при миграции в другую таймзону:**

```
Проект: MSK (UTC+3) → AWS Frankfurt (UTC+1)
TIMESTAMP-поля: «сдвинулись» на 2 часа в отчётах
DATETIME-поля:   остались как были
```

**Правило выбора:**

| Что хранить | Тип |
|---|---|
| **Технические `created_at`/`updated_at`** | **`TIMESTAMP`** — UTC-семантика как раз нужна |
| **Бизнес-даты** (заказ оформлен в `"12:00"` локального времени) | **`DATETIME`** |
| **Даты после 2038** (день рождения, дата окончания контракта) | **`DATETIME`** — `TIMESTAMP` упрётся в Y2K38 |

**Дисциплина:** на серверах и в клиенте держать **`time_zone = "+00:00"`** — тогда оба типа ведут себя предсказуемо.

**Laravel-нюансы:**
- метод `$table->timestamp()` в миграции = `TIMESTAMP`;
- `$casts = [\'col\' => \'datetime\']` — приведение к `Carbon` на PHP-стороне;
- `app.timezone` и `CARBON_TIMEZONE` влияют **только на PHP**, не на MySQL.',
                'code_example' => '-- TIMESTAMP - хранится в UTC, конвертируется на лету
CREATE TABLE events_ts (
    id INT PRIMARY KEY,
    happened_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
SET time_zone = "+03:00";
INSERT INTO events_ts(id) VALUES(1); -- залетит UTC, виден как +03:00
SELECT happened_at FROM events_ts WHERE id=1;  -- например, 12:00:00
SET time_zone = "+00:00";
SELECT happened_at FROM events_ts WHERE id=1;  -- то же значение, но 09:00:00
-- ⚠ значение "сдвинулось" из-за смены timezone

-- DATETIME - стабильно, что положил, то прочёл
CREATE TABLE events_dt (
    id INT PRIMARY KEY,
    happened_at DATETIME
);
INSERT INTO events_dt VALUES(1, "2024-06-15 12:00:00");
SET time_zone = "+00:00";
SELECT happened_at FROM events_dt; -- по-прежнему 2024-06-15 12:00:00

-- Проверка текущей timezone
SELECT @@global.time_zone, @@session.time_zone;',
                'code_language' => 'sql',
                'difficulty' => 4,
                'topic' => 'database.mysql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Зачем отключают FOREIGN_KEY_CHECKS на массовых импортах и почему "NOT NULL DEFAULT ..." - design rule?',
                'answer' => '**Foreign keys не бесплатны:** на каждый `INSERT`/`UPDATE`/`DELETE` InnoDB:
- делает **lookup** в индексе родительской таблицы;
- часто берёт **shared lock** на родительскую строку.

На **массовых операциях** (миграции данных, dump-restore, bulk-import 10M строк) это:
- **сильно тормозит** (проверка на каждую строку);
- **ломает порядок** — нельзя залить детей раньше родителей.

**Паттерн массового импорта:**

```sql
SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;
SET autocommit = 0;

LOAD DATA INFILE \'/tmp/orders.csv\' INTO TABLE orders ...;
LOAD DATA INFILE \'/tmp/order_items.csv\' INTO TABLE order_items;

COMMIT;
SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;
```

**Важно:**
- после выключения FK **не валидируются** — **ответственность на разработчике**;
- после импорта **прогнать аудит-запросы** на сирот (`LEFT JOIN parent WHERE parent.id IS NULL`);
- **на репликах** нужны те же `_CHECKS = 0`, иначе binlog-events упадут.

**Связанный design rule — `NOT NULL DEFAULT ...`:**

**Почему `NULL` проблемный:**
- семантически — «значение **неизвестно**»;
- **трёхзначная логика** (`TRUE`/`FALSE`/`NULL`) усложняет `WHERE`:
  - `WHERE x = 5` **не вернёт** строки с `x IS NULL`;
  - `NOT IN (subquery с NULL)` **возвращает ничего**;
- `COUNT(col)` пропускает `NULL` (а `COUNT(*)` — нет);
- индексы по nullable-колонкам ведут себя по-разному в разных СУБД.

**Правило:** **если значение всегда есть** по бизнес-смыслу — объявлять **`NOT NULL DEFAULT 0/-1/""/sentinel`**.

**`NULL` оставляем только когда он имеет отдельный смысл** «ещё не задано» — `deleted_at`, `completed_at`, `archived_at`, `email_verified_at`.',
                'code_example' => '-- Bulk-импорт миллионов строк - FK выключаем
SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;     -- бонус: пропустить уникальные проверки
SET autocommit = 0;        -- одна большая транзакция на все

LOAD DATA INFILE "/tmp/orders.csv" INTO TABLE orders FIELDS TERMINATED BY ",";
LOAD DATA INFILE "/tmp/order_items.csv" INTO TABLE order_items;

COMMIT;
SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;
SET autocommit = 1;

-- Аудит-запрос на сирот после импорта
SELECT oi.id FROM order_items oi
LEFT JOIN orders o ON o.id = oi.order_id
WHERE o.id IS NULL;

-- Design rule: NOT NULL DEFAULT там, где NULL не нужен бизнесу
CREATE TABLE products (
    id INT PRIMARY KEY,
    name VARCHAR(255) NOT NULL DEFAULT "",
    qty  INT          NOT NULL DEFAULT 0,
    archived_at DATETIME NULL  -- NULL имеет смысл "не архивирован"
);',
                'code_language' => 'sql',
                'difficulty' => 4,
                'topic' => 'database.mysql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Какие основные числовые типы есть в MySQL и когда что выбирать?',
                'answer' => '**Целые** (диапазон со знаком / без знака):
- **`TINYINT`** — 1 байт (-128..127 / 0..255);
- **`SMALLINT`** — 2 байта (~±32K / 0..65K);
- **`MEDIUMINT`** — 3 байта (~±8M);
- **`INT`** — 4 байта (~±2.1 млрд);
- **`BIGINT`** — 8 байт (~±9.2·10¹⁸).

**Дробные:**
- **`DECIMAL(p, s)`** — **точная** фиксированная точка. Для **денег** и любых сумм, где недопустима потеря копейки;
- **`FLOAT`** / **`DOUBLE`** — приближённая плавающая точка. Для научных расчётов, **не** для финансов (`0.1 + 0.2 ≠ 0.3`).

**Правило:** выбирай **минимально достаточный** тип под бизнес-диапазон. Лишний `BIGINT` там, где хватило бы `INT`, **удваивает** размер индексов и буферного пула.',
                'code_example' => 'CREATE TABLE products (
    id          BIGINT UNSIGNED PRIMARY KEY,   -- много записей, нужен запас
    stock       SMALLINT UNSIGNED NOT NULL,    -- 0..65535
    price       DECIMAL(10, 2)   NOT NULL,     -- точная сумма
    rating_avg  FLOAT                          -- приближённый рейтинг
);',
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.mysql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Чем ENUM и SET в MySQL отличаются от обычного VARCHAR?',
                'answer' => '**`ENUM`** — ровно **одно** значение из заранее объявленного списка.
**`SET`** — **комбинация нескольких** (до 64 значений) как **битовая маска**.

**Как хранятся:** оба кодируются **целым числом** (1 или 2 байта в зависимости от размера списка) — занимают меньше места и сравниваются быстрее, чем `VARCHAR`.

**Минусы по сравнению с `VARCHAR` / lookup-таблицей:**
- расширение списка требует **`ALTER TABLE`** (на большой таблице — длинная операция, риск даунтайма);
- **порядок значений в списке влияет на сортировку** — `ORDER BY enum_col` идёт **по позиции**, а не по тексту;
- синтаксис **не переносится** между MySQL и PostgreSQL/SQL Server;
- добавление/удаление значений у `SET` особенно болезненно.

**Правило:**
- `ENUM`/`SET` — для **редко меняющихся** коротких списков (пол, размер футболки);
- бизнес-статусы → **lookup-таблица** с `FK` или **`CHECK`-ограничение** на `VARCHAR`.',
                'code_example' => 'CREATE TABLE orders (
    id BIGINT PRIMARY KEY,
    status ENUM(\'new\',\'paid\',\'shipped\',\'cancelled\') NOT NULL DEFAULT \'new\',
    tags   SET(\'urgent\',\'gift\',\'fragile\',\'bulk\') NOT NULL DEFAULT \'\'
);

INSERT INTO orders(id, status, tags) VALUES (1, \'paid\', \'urgent,gift\');

-- Поиск одного значения в SET
SELECT * FROM orders WHERE FIND_IN_SET(\'urgent\', tags);

-- Добавление нового статуса — переписывает определение таблицы
ALTER TABLE orders MODIFY status
    ENUM(\'new\',\'paid\',\'shipped\',\'cancelled\',\'refunded\') NOT NULL DEFAULT \'new\';',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.mysql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'В чём разница между BLOB и TEXT в MySQL?',
                'answer' => '**`BLOB`** и **`TEXT`** — два семейства типов для **больших значений** с одинаковыми лимитами:
- `TINY*` — до 255 байт;
- обычный (`BLOB`/`TEXT`) — до 64 КБ;
- `MEDIUM*` — до 16 МБ;
- `LONG*` — до 4 ГБ.

**Главное различие — семантика:**
- **`TEXT`** — **строка символов** с привязкой к `CHARSET` и `COLLATION`. Работают `UPPER`, `LIKE`, сортировка по правилам языка;
- **`BLOB`** — **последовательность байтов** без collation. Для картинок, архивов, бинарных протоколов.

**На практике:** большие файлы **в БД хранить не стоит** — лучше класть на S3/диск, а в таблице держать только **путь/URL**. Иначе разрастаются бэкапы, репликация, и каждое чтение тянет мегабайты.',
                'code_example' => 'CREATE TABLE articles (
    id   BIGINT PRIMARY KEY,
    body LONGTEXT CHARACTER SET utf8mb4 NOT NULL  -- текст со словами
);

CREATE TABLE attachments (
    id   BIGINT PRIMARY KEY,
    data LONGBLOB NOT NULL                        -- сырые байты файла
);',
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.mysql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Чем CHAR_LENGTH отличается от LENGTH в MySQL?',
                'answer' => '`CHAR_LENGTH` (синоним `CHARACTER_LENGTH`) — количество **символов** с учётом charset.
`LENGTH` — количество **байтов** в её внутреннем представлении.

**Для ASCII** обе функции дают одинаковый результат. **Для `utf8mb4`** значения **расходятся**:

| Символ | `CHAR_LENGTH` | `LENGTH` (utf8mb4) |
|---|---|---|
| `a` (ASCII) | 1 | 1 |
| `п` (кириллица) | 1 | 2 |
| `中` (иероглиф) | 1 | 3 |
| `😀` (эмодзи) | 1 | 4 |

**Важно:** лимит в `VARCHAR(255)` в MySQL считается **в символах**, а не в байтах — поэтому проверку длины перед `INSERT` надо делать через **`CHAR_LENGTH`**.

**Типичный баг:** `LENGTH(name) <= 100` для никнейма — строка из 60 эмодзи (240 байт) ложно «не помещается», хотя в `VARCHAR(100)` влезает спокойно.',
                'code_example' => 'SELECT
    CHAR_LENGTH(\'hello\') AS chars1, LENGTH(\'hello\') AS bytes1,   -- 5, 5
    CHAR_LENGTH(\'привет\') AS chars2, LENGTH(\'привет\') AS bytes2, -- 6, 12 (utf8mb4)
    CHAR_LENGTH(\'😀😀\')   AS chars3, LENGTH(\'😀😀\')   AS bytes3;  -- 2, 8

-- Корректная валидация лимита
SELECT * FROM users WHERE CHAR_LENGTH(nickname) > 30;',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.mysql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Какие скрытые системные колонки InnoDB использует для MVCC?',
                'answer' => 'У каждой строки в InnoDB есть **три скрытых системных поля**, на которых построен **MVCC** (Multi-Version Concurrency Control).

| Колонка | Размер | Что хранит |
|---|---|---|
| **`DB_TRX_ID`** | 6 байт | id транзакции, **последней изменившей строку** |
| **`DB_ROLL_PTR`** | 7 байт | **указатель в `undo log`** на предыдущую версию строки |
| **`DB_ROW_ID`** | 6 байт | внутренний id строки — **используется как PK**, только если нет ни `PRIMARY KEY`, ни подходящего `UNIQUE NOT NULL` |

**Как работает чтение с MVCC:**
1. Каждая транзакция имеет **read view** — снимок состояния на момент старта (или первого `SELECT` в `READ COMMITTED`);
2. При `SELECT` InnoDB сравнивает **`DB_TRX_ID`** строки с **read view**;
3. Если строка изменена **позже** view → идём **по `DB_ROLL_PTR`** в undo log;
4. Шагаем назад по цепочке версий, пока не найдём подходящую;
5. Получили **consistent read без блокировок**.

**Сравнение с PostgreSQL:**

| | **InnoDB** | **PostgreSQL** |
|---|---|---|
| Где старые версии | **`undo log`** (отдельный сегмент) | **прямо в heap** через `xmin`/`xmax` |
| Откат транзакции | играем `undo log` | переписывание не нужно — `xmax` указывает на dead tuple |
| Очистка старых версий | автоматическая, фоновый purge | **`VACUUM`** (autovacuum) |
| Боль | долгие транзакции тормозят purge → distended undo | долгие транзакции **держат view** → bloat heap |

**Видимость `DB_ROW_ID`:**
- если у таблицы **нет `PRIMARY KEY`** и нет `UNIQUE NOT NULL` — InnoDB **молча сгенерит `DB_ROW_ID`** как cluster key;
- эта колонка **недоступна через SQL** — её не запросить;
- **антипаттерн**: всегда явно объявляй `PRIMARY KEY`.',
                'difficulty' => 4,
                'topic' => 'database.mysql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Зачем InnoDB нужны redo log и undo log?',
                'answer' => 'InnoDB ведёт **два независимых лога** — без них не было бы ни Durability, ни MVCC, ни `ROLLBACK`.

**`redo log`** (файлы `ib_logfile0`/`ib_logfile1`, с MySQL 8.0.30 — `#innodb_redo/`):
- **последовательный журнал физических изменений страниц**;
- пишется **до** фиксации данных в табличное пространство (**WAL-принцип**: Write-Ahead Logging);
- даёт **`D`urability** из ACID:
  - после `COMMIT` транзакция записана в redo, страницы пока могут лежать в buffer pool;
  - при крахе сервер при старте **проигрывает redo log** и восстанавливает все commited транзакции (**crash recovery**);
- размер контролируется `innodb_redo_log_capacity` (8.0.30+);
- паттерн **circular write** — два файла переключаются по очереди.

**`undo log`** (раньше в системном tablespace, сейчас обычно в `undo_001`/`undo_002`):
- хранит **обратные операции** для каждой модификации (insert → delete, update → старое значение, delete → resurrect);
- нужен **сразу для двух целей**:

| Применение | Как используется |
|---|---|
| **`ROLLBACK`** | проигрываем undo-записи в обратном порядке |
| **MVCC consistent read** | по `DB_ROLL_PTR` восстанавливаем старую версию строки для другой транзакции |

**Сравнение:**

| | **`redo log`** | **`undo log`** |
|---|---|---|
| Что хранит | физические изменения «вперёд» | логические изменения «назад» |
| Когда играем | при **crash recovery** | при `ROLLBACK` или MVCC read |
| Свойство ACID | **`D`urability** | **`A`tomicity** + MVCC |

**Боль с распухшим undo log:**
- **долгая транзакция** (например, забытый `START TRANSACTION` на час) **держит read view**;
- purge thread **не может почистить** старые версии — они нужны для view;
- undo log **раздувается до сотен ГБ** → InnoDB **деградирует**;
- мониторить через `SHOW ENGINE INNODB STATUS` → `History list length`.',
                'difficulty' => 4,
                'topic' => 'database.mysql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Как делать ALTER TABLE на больших живых таблицах без даунтайма?',
                'answer' => '**Online DDL** (`ALGORITHM=INPLACE` или `INSTANT`, **MySQL 5.6+**) покрывает многие операции **без блокировки записи** или почти без неё:

| Операция | Алгоритм | Блокировка |
|---|---|---|
| `ADD COLUMN ... NULL` в конец таблицы (8.0+) | **`INSTANT`** | ~0, mgnа изменение метаданных |
| `ADD INDEX` (secondary) | `INPLACE` | DML работает |
| `RENAME COLUMN` | `INPLACE` | DML работает |
| `ADD COLUMN NOT NULL DEFAULT ...` | в 8.0 — `INSTANT`; раньше `COPY` | блокировка таблицы при COPY |
| `MODIFY COLUMN` (смена типа) | **`COPY`** | **полная блокировка** записи |
| Смена `PRIMARY KEY` | **`COPY`** | блокировка |

**Команды для контроля:**
```sql
ALTER TABLE orders ADD INDEX idx_user (user_id), ALGORITHM=INPLACE, LOCK=NONE;
-- Если такой режим недоступен — MySQL вернёт ошибку, а не перейдёт в COPY.
```

**Когда даже Online DDL не подходит** — берут **внешние инструменты**:

| Инструмент | Как работает |
|---|---|
| **`pt-online-schema-change`** (Percona Toolkit) | создаёт **теневую таблицу** с новой схемой, **триггеры** на оригинале синхронизируют записи, в конце — `RENAME` |
| **`gh-ost`** (GitHub) | читает **binlog** (не триггеры) — меньше нагрузки на основной поток, можно паузить и резать throttle |

**Оба инструмента:**
1. Создают «теневую» таблицу с целевой схемой;
2. Копируют данные **порциями**, не нагружая прод;
3. Догоняют изменения через triggers (`pt-osc`) или binlog (`gh-ost`);
4. **Атомарно** делают `RENAME TABLE old TO _old, new TO old;` в самом конце;
5. Дроп старой таблицы по таймауту.

**Выбор:** `gh-ost` обычно мягче по нагрузке (нет триггеров), `pt-osc` — лучше с FK и старее (проверено годами).',
                'code_example' => '# pt-online-schema-change: добавить колонку
pt-online-schema-change \\
    --alter "ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT \'new\'" \\
    --execute D=mydb,t=orders

# gh-ost: то же, через binlog
gh-ost \\
    --user=root --password=... --host=replica.host \\
    --database=mydb --table=orders \\
    --alter="ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT \'new\'" \\
    --execute',
                'code_language' => 'bash',
                'difficulty' => 4,
                'topic' => 'database.mysql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое MariaDB и почему она существует отдельно от MySQL?',
                'answer' => '**MariaDB** — **форк MySQL**, созданный оригинальными разработчиками во главе с Michael «Monty» Widenius в **2009 году**, после того как **Oracle купила Sun** (владельца MySQL). Сообщество опасалось, что Oracle ослабит открытую разработку — форк страховал лицензию **GPL**.

**Drop-in replacement** на старте:
- клиент `mysql`;
- протокол подключения;
- формат файлов данных;
- большинство запросов совместимы;
- в Linux-дистрибутивах пакет `mysql-server` часто фактически указывает на MariaDB.

**Со временем разошлись:**
- у **MariaDB** — собственные движки (`Aria`, `MyRocks`, `ColumnStore`); window functions и роли появились **раньше**;
- **MySQL 8** в ответ добавил `CTE` и JSON-функции.

**Что важно знать:**
- на уровне **прикладного разработчика** разница обычно несущественна;
- на уровне **DBA** различаются параметры конфигурации, реализация **репликации** (`MariaDB GTID` ≠ `MySQL GTID`) и поведение оптимизатора;
- «перепрыгнуть» в обе стороны **без проверки уже нельзя**.',
                'difficulty' => 3,
                'topic' => 'database.mysql',
            ],
        ];
    }
}
