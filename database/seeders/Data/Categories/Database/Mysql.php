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
                'answer' => 'InnoDB — движок MySQL по умолчанию с MySQL 5.5 (2010). Поддерживает транзакции с полным ACID, foreign keys с проверкой целостности, row-level блокировки (десятки тысяч одновременных писателей на одной таблице), MVCC для неблокирующего чтения и crash recovery через redo/undo log. MyISAM — старый движок: только table-level lock (на запись в таблицу — все остальные ждут), нет транзакций, нет FK, нет crash recovery (при падении таблицу нужно чинить REPAIR TABLE). Плюсы MyISAM, которые сегодня уже не актуальны: компактнее на диске, чуть быстрее на чистом SELECT, поддержка FULLTEXT (с MySQL 5.6 это есть и в InnoDB). На практике почти все таблицы — InnoDB; MyISAM остался в системных таблицах mysql.* и в легаси-проектах.',
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
                'answer' => 'В InnoDB строки физически хранятся в порядке primary key - это и есть кластерный индекс. Поэтому PK lookup очень быстрый - данные сразу с ним. Все остальные (вторичные) индексы хранят PK как ссылку на строку. Следствия: PK должен быть коротким (он повторяется в каждом вторичном индексе), последовательный PK даёт лучшую локальность записи.',
                'difficulty' => 4,
                'topic' => 'database.mysql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Как выбрать storage engine в MySQL?',
                'answer' => 'В 99% случаев - InnoDB (он по умолчанию). Используй MyISAM только для ныне редких юзкейсов "много чтения, нет записи". Memory (HEAP) - для временных in-memory таблиц. Archive - для архивных данных только на чтение/append. NDB - для кластерного MySQL Cluster.',
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
                'answer' => 'В стандарте SQL уровень REPEATABLE READ защищает от non-repeatable read, но не от phantoms. В InnoDB на REPEATABLE READ механизм ДВОЙНОЙ и зависит от типа чтения. (1) Для CONSISTENT READS (обычный SELECT без FOR UPDATE / LOCK IN SHARE MODE) phantom-ы исключены за счёт MVCC-снимка: транзакция читает на момент первого SELECT, новые строки от других транзакций для неё просто не существуют - блокировки тут ни при чём. (2) Для LOCKING READS (SELECT ... FOR UPDATE / LOCK IN SHARE MODE, UPDATE/DELETE по диапазону) включаются next-key и gap locks - блокировки на "промежутках" между значениями индекса. SELECT * FROM t WHERE x BETWEEN 5 AND 10 FOR UPDATE заблокирует не только существующие строки, но и пустые промежутки - вторая транзакция не сможет вставить x=7. Это и предотвращает фантомы для locking reads. ВАЖНО: gap locks - частая причина неожиданных deadlock-ов под нагрузкой; на READ COMMITTED InnoDB их отключает (только row locks), но тогда фантомы возможны и для locking reads тоже. Уникальная особенность InnoDB - именно сочетание MVCC + gap locks на одном уровне изоляции.',
                'difficulty' => 5,
                'topic' => 'database.mysql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое ONLY_FULL_GROUP_BY и почему он часто ломает легаси-MySQL-запросы?',
                'answer' => 'ONLY_FULL_GROUP_BY - режим в sql_mode MySQL (по умолчанию ВКЛЮЧЁН с MySQL 5.7.5), который требует строгое соответствие SQL-стандарту: каждое поле в SELECT, которое не является агрегатом (SUM/COUNT/AVG/MIN/MAX) или константой, должно либо присутствовать в GROUP BY, либо быть функционально зависимым от GROUP BY-колонок (PK / UNIQUE NOT NULL). До 5.7.5 (и в MySQL по дефолту до того) MySQL разрешал писать SELECT u.id, u.name, COUNT(*) ... GROUP BY u.id - и для каждой группы возвращал ПРОИЗВОЛЬНОЕ значение u.name, что часто приводило к неочевидным багам в отчётах. С включённым ONLY_FULL_GROUP_BY такой запрос валится с ошибкой 1055 "Expression #N of SELECT list is not in GROUP BY clause and contains nonaggregated column". Решения: 1) добавить все non-aggregate колонки в GROUP BY; 2) обернуть лишние колонки агрегатами вроде ANY_VALUE(u.name) - явно сказать "мне всё равно, какое значение"; 3) переписать через subquery / window функции; 4) (НЕ рекомендуется в проде) выключить режим в sql_mode. На собесе spotter: запросы вида "SELECT * FROM orders GROUP BY user_id" - нарушение ONLY_FULL_GROUP_BY и одновременно бизнес-ошибка (какие * вернутся - непредсказуемо).',
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
                'answer' => 'DATETIME и TIMESTAMP - оба хранят дату и время, но семантика принципиально разная. TIMESTAMP: 4 байта, диапазон 1970-01-01 00:00:01 UTC - 2038-01-19 03:14:07 UTC (Y2K38), хранится ВНУТРЕННЕ В UTC. При записи MySQL конвертирует значение из текущей session timezone в UTC, при чтении - обратно из UTC в timezone сессии. То есть значение "плавает" вместе с настройкой time_zone клиента/сервера. DATETIME: исторически 8 байт; начиная с MySQL 5.6.4 формат пересчитан в 5 байт + 0–3 байта на дробные секунды (TIMESTAMP по той же схеме — 4 + 0–3 байта), цель изменения — компактнее хранить и поддержать дроби секунд. Диапазон DATETIME — 1000-9999 годы, хранится КАК ЕСТЬ - никаких преобразований; что положили строкой "2024-06-15 12:00:00" - то и достанете, независимо от timezone. ПРАКТИЧЕСКАЯ БОЛЬ при миграции сервера в другую таймзону: TIMESTAMP-колонки начинают возвращать другие значения для тех же физических байтов (потому что меняется конверсия из UTC), DATETIME-колонки остаются неизменными. Прод-история: проект начат в Москве, сервер MSK (UTC+3), миграция в AWS Frankfurt (UTC+1) - все TIMESTAMP-поля "сдвинулись" на 2 часа в отчётах. Лечение: либо заранее держать time_zone="+00:00" на серверах и фронте, либо использовать DATETIME для бизнес-дат (заказ оформлен в "12:00") и TIMESTAMP только для технических полей (created_at/updated_at, где UTC-семантика как раз нужна). Laravel дефолт - timestamp() в миграции для created_at/updated_at, $casts =>"datetime" для отдельных полей; CARBON_TIMEZONE и app.timezone в config влияют только на PHP-сторону.',
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
                'answer' => 'Foreign keys - не бесплатны: на каждый INSERT/UPDATE/DELETE InnoDB проверяет валидность ссылки, что требует look-up в индексе родительской таблицы и часто берёт shared lock на родительскую строку. На массовых операциях (миграции данных, dump-restore, bulk-import 10M строк) это: а) сильно тормозит, потому что проверки на каждую строку; б) ломает порядок: нельзя залить детей раньше родителей. Стандартный паттерн на тяжёлых импортах: SET FOREIGN_KEY_CHECKS=0 в начале, заливаем данные в любом порядке/любым методом (LOAD DATA INFILE или multi-row INSERT), SET FOREIGN_KEY_CHECKS=1 в конце. ВАЖНО: после этого FK не валидируются автоматически - ответственность на разработчике; рекомендуется отдельно прогнать аудит-запросы на сирот (LEFT JOIN ... WHERE parent.id IS NULL). MySQL также пропускает запись о дочерних строках в binary log для slave-репликации в этом режиме - на репликах нужны те же чек-настройки. Связанное design rule "NOT NULL DEFAULT ...": NULL семантически означает "значение неизвестно", это специальное состояние с трёхзначной логикой (TRUE/FALSE/NULL), которое усложняет запросы (WHERE x = 5 не вернёт строки, где x IS NULL; индексы по nullable-колонкам в некоторых СУБД не покрывают IS NULL - см. Oracle). Если по бизнес-смыслу значение всегда есть - объявляйте NOT NULL DEFAULT 0/-1/""/sentinel: меньше боли в WHERE, чище семантика, чуть лучше планы запросов, нет ловушек NULL-распространения в выражениях. NULL только когда NULL имеет ОТДЕЛЬНЫЙ смысл "ещё не задано" (deleted_at, completed_at, archived_at).',
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
                'answer' => 'ENUM хранит ровно одно значение из заранее объявленного списка, SET — комбинацию из нескольких (до 64 значений) как битовую маску. Внутри оба кодируются целым числом (1 или 2 байта в зависимости от размера списка), поэтому занимают меньше места и сравниваются быстрее, чем VARCHAR. Минусы по сравнению с VARCHAR / справочной таблицей: расширение списка значений требует ALTER TABLE (на большой таблице — длинная операция и риск даунтайма), порядок значений в списке влияет на сортировку (ORDER BY enum_col сортирует по позиции, а не по тексту), синтаксис не переносится между MySQL и PostgreSQL/SQL Server, добавление/удаление значений у SET вообще болезненно. Поэтому ENUM/SET — для редко меняющихся коротких списков (пол, размер футболки), для бизнес-статусов чаще делают отдельную справочную таблицу с FK или CHECK-ограничение.',
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
                'answer' => 'CHAR_LENGTH (синоним CHARACTER_LENGTH) возвращает количество символов в строке с учётом charset, а LENGTH — количество байтов в её внутреннем представлении. Для чистого ASCII обе функции дают одинаковый результат. Для utf8mb4 один кириллический символ занимает 2 байта, эмодзи — до 4 байт, иероглиф — 3, и значения расходятся. Это критично при валидации пользовательских строк и при проверке против лимита колонки: лимит в VARCHAR(255) в MySQL считается в СИМВОЛАХ, а не в байтах, поэтому корректную проверку длины перед INSERT надо делать через CHAR_LENGTH. Типичный баг — использовать LENGTH(name) <= 100 для никнейма, и тогда строка из 60 эмодзи (240 байт) ложно "не помещается".',
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
                'answer' => 'У каждой строки в InnoDB есть три скрытых поля: DB_TRX_ID — идентификатор транзакции, последней изменившей строку; DB_ROLL_PTR — указатель в undo log на предыдущую версию, по цепочке которого можно восстановить любое состояние; DB_ROW_ID — внутренний идентификатор строки, который InnoDB использует как кластерный ключ только если у таблицы нет ни PRIMARY KEY, ни подходящего UNIQUE NOT NULL индекса (сначала пробуется первый такой UNIQUE NOT NULL, и только при его отсутствии генерируется DB_ROW_ID). При SELECT InnoDB сравнивает DB_TRX_ID с собственным read view транзакции и, если запись новее, идёт по DB_ROLL_PTR назад, пока не найдёт подходящую версию. Это и есть механизм неблокирующего чтения InnoDB, в отличие от PostgreSQL, который хранит версии прямо в heap через xmin/xmax.',
                'difficulty' => 4,
                'topic' => 'database.mysql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Зачем InnoDB нужны redo log и undo log?',
                'answer' => 'Redo log (ib_logfile) — это последовательный журнал физических изменений страниц, который пишется до фиксации данных в табличном пространстве. После сбоя сервер прокручивает redo log и восстанавливает зафиксированные транзакции, обеспечивая Durability из ACID. Undo log хранит обратные операции для каждой модификации и нужен сразу для двух целей: ROLLBACK откатывает транзакцию, проигрывая undo-записи, а MVCC по DB_ROLL_PTR строит старые версии строк для consistent read. Если undo log распух (долгая транзакция держит view), MVCC не может почистить старые версии, и InnoDB теряет производительность.',
                'difficulty' => 4,
                'topic' => 'database.mysql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Как делать ALTER TABLE на больших живых таблицах без даунтайма?',
                'answer' => 'Многие операции в MySQL 5.6+ поддерживают Online DDL (ALGORITHM=INPLACE, LOCK=NONE) — добавление nullable-колонки, переименование, создание secondary-индекса проходят без блокировки записи. Тяжёлые изменения (изменение типа PK, добавление NOT NULL колонки без default) используют COPY и блокируют таблицу — для них берут внешние инструменты: pt-online-schema-change от Percona создаёт теневую таблицу и синхронизирует через триггеры, gh-ost от GitHub читает binlog без триггеров и нагружает основной поток меньше. Оба инструмента переключают таблицы атомарным RENAME в самом конце.',
                'difficulty' => 4,
                'topic' => 'database.mysql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое MariaDB и почему она существует отдельно от MySQL?',
                'answer' => 'MariaDB — форк MySQL, созданный его оригинальными разработчиками во главе с Michael "Monty" Widenius в 2009 году, после того как Oracle купила Sun (владельца MySQL). Сообщество опасалось, что Oracle ослабит открытую разработку, и форк страховал лицензию GPL. MariaDB задумывалась как drop-in replacement: клиент mysql, протокол подключения, формат файлов данных и большинство запросов совместимы, в Linux-дистрибутивах пакет mysql-server часто фактически указывает на MariaDB. Со временем продукты разошлись: у MariaDB появились собственные движки (Aria, MyRocks, ColumnStore), оконные функции и роли поддерживались раньше, чем в MySQL; MySQL 8 в ответ добавил CTE и JSON-функции. На уровне прикладного разработчика разница обычно несущественна; на уровне DBA различаются параметры конфигурации, реализация репликации (MariaDB GTID отличается от MySQL GTID) и поведение оптимизатора, поэтому "перепрыгнуть" в обе стороны без проверки уже нельзя.',
                'difficulty' => 3,
                'topic' => 'database.mysql',
            ],
        ];
    }
}
