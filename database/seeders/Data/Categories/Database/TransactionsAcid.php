<?php

namespace Database\Seeders\Data\Categories\Database;

class TransactionsAcid
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Базы данных',
                'question' => 'Что такое транзакция?',
                'answer' => '**Транзакция** — группа операций над БД, выполняющаяся **как единое целое**: либо **все** изменения применяются, либо **ни одно** (откат).

**Команды:**
- **`BEGIN`** / `START TRANSACTION` — открыть транзакцию;
- **`COMMIT`** — зафиксировать все изменения;
- **`ROLLBACK`** — отменить всё, сделанное с момента `BEGIN`.

**Классический пример** — перевод денег между счетами:
1. Снять 100 с одного счёта;
2. Зачислить 100 на другой.

Если сервер упадёт **между** шагами — без транзакции деньги исчезнут. С транзакцией — БД либо применит **оба** `UPDATE`, либо **откатит** первый.

В Laravel: `DB::transaction(function () { ... })` — автоматический `COMMIT` при успехе, `ROLLBACK` при исключении.',
                'code_example' => 'BEGIN;

UPDATE accounts SET balance = balance - 100 WHERE id = 1;
UPDATE accounts SET balance = balance + 100 WHERE id = 2;

-- Если всё ок:
COMMIT;
-- Если ошибка:
-- ROLLBACK;',
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое ACID?',
                'answer' => '**ACID** — четыре гарантии, которые СУБД даёт для каждой **транзакции**:

| Буква | Гарантия | Кратко |
|---|---|---|
| **A**tomicity | всё или ничего | `ROLLBACK` откатывает частичные изменения |
| **C**onsistency | согласованность | после `COMMIT` не нарушены `PK`/`FK`/`CHECK`/`UNIQUE` и бизнес-инварианты |
| **I**solation | изолированность | параллельные транзакции не видят промежуточных состояний друг друга |
| **D**urability | долговечность | после `COMMIT` данные на диске, даже при падении сервера (`WAL`/redo log) |

**Кто гарантирует:**
- **реляционные БД** (PostgreSQL, MySQL/InnoDB) — полный ACID;
- многие **NoSQL** ради скорости и горизонтального масштабирования отдают только **BASE** (Basically Available, Soft state, **Eventual consistency**).

**На собесе** важно уметь раскрыть каждую букву на классическом примере **перевода денег между счетами**.',
                'code_example' => 'BEGIN;
UPDATE accounts SET balance = balance - 100 WHERE id = 1; -- A: обе строки или ни одной
UPDATE accounts SET balance = balance + 100 WHERE id = 2; -- C: CHECK (balance >= 0)
COMMIT; -- D: WAL уже на диске, не пропадёт
-- I: параллельные SELECT видят либо старое, либо новое состояние, не промежуточное',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'A в ACID - что такое Atomicity (атомарность)?',
                'answer' => '**Atomicity (атомарность)** — транзакция выполняется **целиком или не выполняется вообще**. Промежуточных состояний для других транзакций не существует.

**Как это работает:**
- если в середине что-то упало (ошибка, сбой сервера, явный `ROLLBACK`) — БД **откатывает все** уже сделанные изменения транзакции;
- после `COMMIT` все изменения становятся видны разом.

**Аналогия:** нажатие одной кнопки — либо **полностью сработало**, либо **ничего не произошло**. «Полу-перевод» (списали с одного счёта, не зачислили на другой) — невозможен.

Технически в InnoDB обеспечивается через **undo log**, в PostgreSQL — через **MVCC**: старые версии строк остаются, пока транзакция не закоммитится.',
                'difficulty' => 2,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'C в ACID - что такое Consistency (согласованность)?',
                'answer' => '**Consistency (согласованность)** — после `COMMIT` БД находится в **логически корректном состоянии**. Транзакция переводит БД из одного валидного состояния в другое.

**Что проверяет БД автоматически** (декларативные ограничения):
- `PRIMARY KEY` / `UNIQUE` — нет дублей;
- `FOREIGN KEY` — нет «висячих» ссылок;
- `NOT NULL` — обязательные поля заполнены;
- `CHECK` — произвольные условия.

Если что-то нарушено — БД сама делает `ROLLBACK`.

**Что НЕ проверяет БД** — **бизнес-инварианты**. Например, «сумма счетов при переводе сохраняется» — это правило приложения, его пишет **разработчик в коде транзакции**.

Поэтому **C** в ACID — самая «прикладная» буква: в значительной части это **A + I + декларативные правила + логика разработчика**.',
                'difficulty' => 2,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'I в ACID - что такое Isolation (изолированность)?',
                'answer' => '**Isolation** определяет, насколько параллельные транзакции «видят» друг друга. В идеале результат должен быть как при **последовательном** выполнении (*serializable schedule*), но это дорого — поэтому стандарт SQL вводит **4 уровня**:

| Уровень | Что допускает |
|---|---|
| `READ UNCOMMITTED` | dirty read |
| `READ COMMITTED` | non-repeatable read, phantom |
| `REPEATABLE READ` | phantom (по стандарту) |
| `SERIALIZABLE` | ничего |

**Трейдофф:** чем строже уровень — тем **больше блокировок или откатов** и ниже throughput.

**Дефолты:**
- **PostgreSQL** → `READ COMMITTED`;
- **MySQL/InnoDB** → `REPEATABLE READ`.

**На собесе** важно знать аномалии каждого уровня и уметь выбрать **минимально достаточный**.',
                'code_example' => '-- PostgreSQL: установить уровень для текущей транзакции
BEGIN;
SET TRANSACTION ISOLATION LEVEL REPEATABLE READ;
SELECT SUM(balance) FROM accounts; -- увидим консистентный снимок
SELECT SUM(balance) FROM accounts; -- та же сумма, даже если идут параллельные UPDATE
COMMIT;

-- MySQL: то же
SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE;',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'D в ACID - что такое Durability (долговечность)?',
                'answer' => '**Durability (долговечность)** — после `COMMIT` изменения сохранены **навсегда**, даже если сервер **сразу упадёт** (выключили питание, kill -9).

**Как обеспечивается:**
- БД **сначала пишет изменения в журнал** на диск (с `fsync`), **потом** подтверждает `COMMIT` клиенту;
- в MySQL/InnoDB это **redo log** (`ib_logfile`);
- в PostgreSQL — **WAL** (Write-Ahead Log).

После рестарта БД проигрывает журнал и **восстанавливает** все зафиксированные транзакции.

**Простыми словами:** `COMMIT` прошёл успешно — значит, данные точно не пропадут, даже если сервер упал через миллисекунду.

**Цена надёжности:** `fsync` — самая дорогая операция в БД. Поэтому Durability и производительность приходится балансировать (например, `innodb_flush_log_at_trx_commit`).',
                'difficulty' => 2,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Какие бывают уровни изоляции транзакций?',
                'answer' => 'Стандарт SQL определяет **4 уровня изоляции** через **аномалии**, которые они допускают:

| Уровень | Dirty Read | Non-Repeatable | Phantom |
|---|---|---|---|
| `READ UNCOMMITTED` | да | да | да |
| `READ COMMITTED` | нет | да | да |
| `REPEATABLE READ` | нет | нет | **да (по стандарту)** |
| `SERIALIZABLE` | нет | нет | нет |

**Реализация в реальных СУБД:**

- **PostgreSQL** — не реализует `READ UNCOMMITTED` (минимум `READ COMMITTED`). На `REPEATABLE READ` использует **snapshot isolation** — фантомов нет, но возможен **write skew**. `SERIALIZABLE` реализован через **SSI** (Serializable Snapshot Isolation) — может откатывать конкурирующую транзакцию с `serialization_failure`, приложение должно ретраить.
- **MySQL/InnoDB** — на `REPEATABLE READ` блокирует фантомы через **gap-locks** и **next-key locks** (read-view + range locks для `FOR UPDATE`/`FOR SHARE`).

**Трейдофф:** чем строже уровень — тем **меньше concurrency** (больше блокировок) и **больше retry** на serialization failures. Выбирай **минимально достаточный**.',
                'code_example' => '-- Таблица аномалий по стандарту SQL
-- Уровень           | Dirty | NonRepeat | Phantom
-- READ UNCOMMITTED  |   +   |    +      |    +
-- READ COMMITTED    |   -   |    +      |    +
-- REPEATABLE READ   |   -   |    -      |    +
-- SERIALIZABLE      |   -   |    -      |    -
SET TRANSACTION ISOLATION LEVEL REPEATABLE READ;',
                'code_language' => 'sql',
                'difficulty' => 4,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое Lost Update (потерянное обновление)?',
                'answer' => '**Lost Update** — аномалия, когда **две транзакции читают** одно значение, **обе модифицируют и пишут**, и обновление одной **перезаписывает** другое — изменение «теряется».

**Классический пример** (read-modify-write):

| Шаг | T1 | T2 |
|---|---|---|
| 1 | `SELECT balance` → 100 | |
| 2 | | `SELECT balance` → 100 |
| 3 | `UPDATE SET balance = 90` (-10) | |
| 4 | | `UPDATE SET balance = 80` (-20) |
| 5 | `COMMIT` | `COMMIT` |

Ожидали `100 - 10 - 20 = 70`, получили **`80`** — обновление T1 потеряно.

**Способы защиты:**
1. **Атомарный `UPDATE` с выражением** — самый дешёвый: `UPDATE accounts SET balance = balance - 10 WHERE id = 1`. Чтение и запись в одной операции, движок сам блокирует строку;
2. **Пессимистичный лок** — `SELECT ... FOR UPDATE` перед `UPDATE` (X-lock до `COMMIT`);
3. **Оптимистичная блокировка** — колонка `version`, при `UPDATE` сравниваем с прочитанной (`WHERE version = ?`);
4. **`SERIALIZABLE`** — БД сама обнаружит конфликт и откатит вторую.',
                'code_example' => '-- Атомарное обновление - безопасно
UPDATE accounts SET balance = balance - 10 WHERE id = 1;

-- Оптимистическая блокировка
UPDATE products
SET price = 99, version = version + 1
WHERE id = 1 AND version = 5;
-- если 0 строк изменено - кто-то опередил, ретраим',
                'code_language' => 'sql',
                'difficulty' => 4,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое Dirty Read (грязное чтение)?',
                'answer' => '**Dirty Read (грязное чтение)** — транзакция **читает данные, изменённые другой ещё незакоммиченной транзакцией**. Если та потом сделает `ROLLBACK` — мы прочитали **«несуществующие»** данные.

**Сценарий:**
1. T1 делает `UPDATE accounts SET balance = 0 WHERE id = 1` (но **не коммитит**);
2. T2 читает `balance = 0` и принимает решение (например, отказывает в выводе);
3. T1 откатывается — `balance` снова `100`, но решение T2 уже принято на ложных данных.

**Где встречается:**
- допустим только на уровне `READ UNCOMMITTED`;
- **PostgreSQL** вообще не реализует этот уровень — минимум `READ COMMITTED`, даже если запросить `READ UNCOMMITTED`, движок молча использует `READ COMMITTED`;
- **MySQL/InnoDB** допускает `READ UNCOMMITTED`, но почти никто не использует.',
                'code_example' => '-- T1
BEGIN;
UPDATE accounts SET balance = 0 WHERE id = 1;
-- (не делает COMMIT)

-- T2 (на READ UNCOMMITTED увидит balance = 0)
SELECT balance FROM accounts WHERE id = 1;

-- T1 решает откатиться
ROLLBACK;
-- balance снова 100, но T2 уже приняла решение на 0',
                'code_language' => 'sql',
                'difficulty' => 4,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое Non-repeatable Read?',
                'answer' => '**Non-Repeatable Read (неповторяемое чтение)** — в рамках **одной транзакции** мы читаем **одну и ту же строку дважды** и получаем **разные значения**, потому что между чтениями другая транзакция её **`UPDATE`-нула и закоммитила**.

**Сценарий:**

| Шаг | T1 | T2 |
|---|---|---|
| 1 | `SELECT price FROM products WHERE id=1` → 100 | |
| 2 | | `UPDATE products SET price=120 WHERE id=1; COMMIT` |
| 3 | `SELECT price FROM products WHERE id=1` → **120** | |

**Где встречается:**
- разрешено на уровне `READ COMMITTED` (дефолт PostgreSQL);
- **решается** уровнем `REPEATABLE READ` и выше — повторное чтение увидит **тот же снимок**, что первое.

**Чем отличается от Phantom Read:** non-repeatable — про **изменение существующей строки**; phantom — про **появление новых строк** в результате повторного `SELECT ... WHERE`.',
                'difficulty' => 4,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое Phantom Read (фантомное чтение)?',
                'answer' => '**Phantom Read (фантомное чтение)** — в одной транзакции мы выполняем **один и тот же запрос с `WHERE`** дважды и получаем **разное количество строк**, потому что другая транзакция **вставила или удалила** подходящие.

**Сценарий:**

| Шаг | T1 | T2 |
|---|---|---|
| 1 | `SELECT * FROM orders WHERE status=\'new\'` → 5 строк | |
| 2 | | `INSERT INTO orders(status) VALUES(\'new\'); COMMIT` |
| 3 | `SELECT * FROM orders WHERE status=\'new\'` → **6 строк** | |

**Защита по уровню:**
- по стандарту — `SERIALIZABLE`;
- **PostgreSQL** на `REPEATABLE READ` (snapshot isolation) **уже защищает от фантомов** — повторный `SELECT` видит снимок начала транзакции;
- **MySQL/InnoDB** на `REPEATABLE READ` использует **gap locks** и **next-key locks** — `SELECT ... FOR UPDATE WHERE status=\'new\'` блокирует не только существующие строки, но и **«промежутки»** в индексе, не давая вставить новую `\'new\'`.',
                'difficulty' => 4,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое Write Skew?',
                'answer' => '**Write Skew** — самая «коварная» аномалия snapshot isolation: **две транзакции читают одни и те же данные**, принимают решение **независимо**, и **пишут в разные строки** так, что **по отдельности** каждая запись валидна, а **в сумме** нарушает **бизнес-инвариант**.

**Классический пример** — правило «хотя бы один врач на смене»:

| Шаг | T1 (доктор A) | T2 (доктор B) |
|---|---|---|
| 1 | `SELECT COUNT(*) FROM oncall WHERE is_on = true` → 2 | `SELECT COUNT(*) FROM oncall WHERE is_on = true` → 2 |
| 2 | «есть второй, можно уйти» | «есть второй, можно уйти» |
| 3 | `UPDATE oncall SET is_on=false WHERE id=A` | `UPDATE oncall SET is_on=false WHERE id=B` |
| 4 | `COMMIT` | `COMMIT` |

**Результат:** 0 врачей на смене — инвариант нарушен. Под `REPEATABLE READ` (snapshot) в PostgreSQL аномалия **сохраняется**: каждая T читает свой снимок, изменения видят разные строки.

**Чем отличается от Lost Update:** lost update — две T пишут **одну и ту же** строку; write skew — две T пишут **разные** строки.

**Защита:**
- `SERIALIZABLE` (в PG — через **SSI**) — обнаружит конфликт чтения-записи и откатит одну с `serialization_failure`;
- **материализовать конфликт** через `SELECT ... FOR UPDATE` на общей строке (например, лочить запись «смена», а не отдельных врачей);
- **денормализованный счётчик** + `CHECK` (см. карточку про лимит мест).',
                'difficulty' => 5,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое блокировки (locks) и какие у них уровни гранулярности?',
                'answer' => '**Блокировка** — механизм, не дающий нескольким транзакциям одновременно конфликтно работать с одним ресурсом.

**По типу:**
- **shared / S** — несколько транзакций могут одновременно **читать**;
- **exclusive / X** — одна транзакция **модифицирует**, остальные ждут.

**По гранулярности:**
- **row-level** — PostgreSQL, InnoDB; **самая мелкая**, максимум concurrency;
- **table-level** — `LOCK TABLES` в MySQL, `LOCK TABLE ... IN ACCESS EXCLUSIVE MODE` в PG; для **DDL** и массовых операций;
- **advisory locks** — произвольный ключ, БД не интерпретирует; удобно для **распределённой координации**.

**Трейдофф:** чем **мельче** гранулярность — тем выше параллелизм, но дороже накладные расходы на отслеживание.

**На собесе** спрашивают сочетания: например, `UPDATE` в InnoDB берёт **X-lock** на строку и **intention exclusive (IX)** на таблицу.',
                'code_example' => '-- Row-level (неявно при UPDATE)
BEGIN;
UPDATE accounts SET balance = balance - 100 WHERE id = 1; -- X-lock на строке
COMMIT;

-- Table-level (явно)
LOCK TABLE orders IN ACCESS EXCLUSIVE MODE; -- PostgreSQL, для миграции

-- Advisory (по произвольному ключу)
SELECT pg_advisory_xact_lock(42); -- PostgreSQL
SELECT GET_LOCK(\'cron:cleanup\', 0); -- MySQL',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Чем отличаются shared lock и exclusive lock?',
                'answer' => '**Shared (S, разделяемая)** — «несколько читателей могут держать одновременно, но **писать никто не может**». Захватывается через `SELECT ... FOR SHARE` / `LOCK IN SHARE MODE`.

**Exclusive (X, эксклюзивная)** — «только **один владелец**, никаких других S или X на этом ресурсе». Ставится автоматически при `UPDATE`/`DELETE` или явно через `SELECT ... FOR UPDATE`.

**Матрица совместимости:**

|  | S | X |
|---|---|---|
| **S** | OK | wait |
| **X** | wait | wait |

**Важный нюанс MVCC-баз** (PostgreSQL, InnoDB): row-level **X НЕ блокирует обычный `SELECT`** — читатели видят прежний **снапшот**, ждать должны только пишущие транзакции и `FOR UPDATE`/`FOR SHARE`. Поэтому в Postgres правило: **«обычное чтение никогда не блокирует запись и наоборот»**.',
                'code_example' => '-- Транзакция A
BEGIN;
SELECT * FROM products WHERE id = 1 FOR UPDATE; -- X-lock

-- Транзакция B (параллельно)
SELECT * FROM products WHERE id = 1;            -- OK, видит старую версию (MVCC)
SELECT * FROM products WHERE id = 1 FOR SHARE;  -- ждёт COMMIT/ROLLBACK A
UPDATE products SET price = 99 WHERE id = 1;    -- тоже ждёт',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что делает SELECT FOR UPDATE?',
                'answer' => '`SELECT ... FOR UPDATE` захватывает **X-блокировку** на каждой выбранной строке **до конца текущей транзакции** (то есть до `COMMIT`/`ROLLBACK`).

**Кто будет ждать:**
- другие транзакции, пытающиеся прочитать те же строки с `FOR UPDATE`/`FOR SHARE`;
- `UPDATE` / `DELETE` тех же строк;
- (или упадут с **lock timeout**).

**Обычный `SELECT`** в MVCC-СУБД при этом **не блокируется** — видит прежнюю версию (snapshot).

**Классическое применение** — read-modify-write, где между чтением и записью нельзя пускать конкурентов:
- списание баланса со счёта;
- резерв места/билета;
- инкремент счётчика без атомарного выражения.

**Важно:** работает **только внутри транзакции**; вне `BEGIN`/`COMMIT` блокировка снимается сразу.

**Модификаторы:**
- `NOWAIT` — упасть **мгновенно** вместо ожидания;
- `SKIP LOCKED` — **пропустить** занятые строки; полезно для **очередей задач** (несколько воркеров разбирают разные строки без конкуренции).',
                'code_example' => 'BEGIN;
SELECT balance FROM accounts WHERE id = 1 FOR UPDATE; -- X-lock до COMMIT
-- проверяем balance в коде, потом обновляем
UPDATE accounts SET balance = balance - 100 WHERE id = 1;
COMMIT;

-- Воркер очереди: каждый забирает свою задачу
SELECT id FROM jobs WHERE status = \'pending\' ORDER BY id
LIMIT 1 FOR UPDATE SKIP LOCKED;',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что делает SELECT FOR SHARE?',
                'answer' => '`SELECT ... FOR SHARE` (в старом MySQL — `LOCK IN SHARE MODE`) ставит **S-блокировку** на выбранные строки до конца транзакции.

**Что разрешено / запрещено:**
- несколько транзакций могут **одновременно держать S-lock** на одной строке и читать её;
- никто не сможет `UPDATE`/`DELETE`/`FOR UPDATE`, пока хотя бы один S-lock не отпущен.

**Применение** — гарантировать, что связанные данные **не изменятся**, пока транзакция их использует. Пример: прочитать справочник тарифов и посчитать стоимость, не дав никому в этот момент изменить тариф.

**Подводный камень — deadlock:** если две транзакции взяли S на одной строке, а потом **обе пытаются её `UPDATE`** — обе ждут друг друга. Поэтому когда понятно, что точно будет `UPDATE`, **лучше сразу брать `FOR UPDATE`**.',
                'code_example' => 'BEGIN;
-- Прочитали тариф, дальше используем его в расчёте
SELECT price FROM tariffs WHERE id = 1 FOR SHARE;
INSERT INTO orders(tariff_id, total) VALUES (1, 1000);
COMMIT;

-- Параллельный UPDATE будет ждать
UPDATE tariffs SET price = 1200 WHERE id = 1; -- блокируется до COMMIT первой',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое advisory lock?',
                'answer' => '**Advisory lock (рекомендательная блокировка)** — блокировка **по произвольному ключу (числу)**, **не привязана** к строкам или таблицам. БД сама её **никак не использует** — смысл задаёт приложение.

**Применение:**
- **распределённые cron-задачи** — гарантировать, что одна задача запустится **только в одном инстансе** на весь кластер;
- **сериализация редких операций** без полноценной транзакции;
- **координация между процессами** там, где не нужна транзакция БД.

**Два варианта по времени жизни:**

| Функция | Когда отпускает |
|---|---|
| `pg_advisory_lock(key)` | до явного `pg_advisory_unlock(key)` **или** до закрытия соединения |
| `pg_advisory_xact_lock(key)` | автоматически на `COMMIT`/`ROLLBACK` — **безопаснее** |

**Грабли:**
- **session-level** lock «протекает» при использовании PgBouncer в transaction pooling — лок остаётся на физ-соединении, а оно достаётся другому клиенту;
- **`pg_advisory_xact_lock` свободен от этой проблемы**.

**В MySQL** — аналог `GET_LOCK(name, timeout)` / `RELEASE_LOCK(name)` (привязан к соединению, не к транзакции).',
                'code_example' => '-- Заблокировать "что-то" с ключом 42
SELECT pg_advisory_lock(42);
-- ... критическая секция ...
SELECT pg_advisory_unlock(42);

-- Транзакционный вариант, отпустится сам
SELECT pg_advisory_xact_lock(42);',
                'code_language' => 'sql',
                'difficulty' => 4,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое Deadlock и как его избегать?',
                'answer' => '**Deadlock (взаимная блокировка)** — ситуация, когда **транзакция A ждёт ресурс, удерживаемый B**, а **B ждёт ресурс, удерживаемый A**. Без вмешательства они будут ждать вечно.

**Сценарий:**

| Шаг | T1 | T2 |
|---|---|---|
| 1 | `UPDATE accounts WHERE id=1` (X-lock на A) | `UPDATE accounts WHERE id=2` (X-lock на B) |
| 2 | `UPDATE accounts WHERE id=2` → ждёт T2 | `UPDATE accounts WHERE id=1` → ждёт T1 |

**Что делает БД:**
- **PostgreSQL** и **MySQL/InnoDB** автоматически детектят deadlock в графе ожиданий и **откатывают одну из транзакций** с ошибкой (`deadlock detected` / `Deadlock found when trying to get lock`);
- победившая транзакция продолжает работать, проигравшая получает ошибку — приложение **обязано ретраить**.

**Как избегать:**
1. **Захватывать блокировки в одинаковом порядке** во всех путях кода (например, всегда сначала меньший `id`);
2. **Держать транзакции короткими** — никаких HTTP-вызовов, тяжёлой логики внутри `BEGIN`;
3. Использовать **`FOR UPDATE NOWAIT`** — упасть мгновенно вместо ожидания, или **`SKIP LOCKED`** — пропустить занятые строки (для очередей);
4. **Ретраить** откатившиеся транзакции — в Laravel это `DB::transaction($callback, $attempts = 3)`.',
                'difficulty' => 4,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Чем отличаются пессимистичные (FOR UPDATE) и оптимистичные блокировки? Когда какую применять?',
                'answer' => 'Два полярных подхода к разрешению конфликтов конкурентного доступа.

**Пессимистичная** — «сначала запрещаю, потом меняю»:
- `SELECT ... FOR UPDATE` ставит **row-level X-lock** до конца транзакции;
- другие `FOR UPDATE`/`UPDATE`/`DELETE` на этих строках **ждут** (или падают с **lock timeout**);
- гарантирует отсутствие конфликта, но **снижает concurrency** и может привести к **deadlock**.

**Оптимистичная** — «меняю, на коммите проверяю»:
- колонка **`version`** (или `updated_at`);
- при `UPDATE` сравниваем с прочитанной ранее: `WHERE id = ? AND version = ?`;
- если кто-то опередил — **`0` affected rows**, приложение **ретраит** или показывает конфликт пользователю;
- никаких блокировок в БД, **высокий throughput**, но при **частых** конфликтах работа теряется.

**Когда что брать:**

| | Pessimistic | Optimistic |
|---|---|---|
| Длина критической секции | **короткая** (внутри одного запроса) | **долгая** (HTTP round-trip, форма) |
| Вероятность конфликта | **высокая** (баланс счёта, резерв билета) | **низкая** (редактирование документа) |
| Что блокируется | строка в БД | ничего |
| Что теряется при конфликте | время ожидания | работа пользователя |
| Сетевая транзакция | да, держим `BEGIN` | нет, оптимистично |

**В Laravel:**
- pessimistic — **`lockForUpdate()`** / **`sharedLock()`** на query builder;
- optimistic — вручную через колонку `version` и `WHERE version = ?` в `UPDATE`;
- атомарный `UPDATE balance = balance - ?` — самый дешёвый частный случай pessimistic.',
                'code_example' => '<?php
// PESSIMISTIC - Laravel
DB::transaction(function () use ($userId, $amount) {
    $account = Account::where("user_id", $userId)
        ->lockForUpdate() // SELECT ... FOR UPDATE
        ->firstOrFail();

    if ($account->balance < $amount) {
        throw new InsufficientFundsException;
    }

    $account->decrement("balance", $amount);
});

// OPTIMISTIC - вручную через version
$post = Post::find($id); // version = 5
$post->title = "new";

$updated = Post::where("id", $id)
    ->where("version", $post->version)
    ->update([
        "title" => $post->title,
        "version" => $post->version + 1,
    ]);

if ($updated === 0) {
    throw new ConcurrentModificationException("Кто-то уже изменил пост");
}

// Альтернатива OPTIMISTIC - атомарное условие в SQL
DB::update("UPDATE accounts SET balance = balance - ?
            WHERE user_id = ? AND balance >= ?", [$amount, $userId, $amount]);',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Как PostgreSQL реализует MVCC через xmin, xmax и ctid?',
                'answer' => 'PostgreSQL хранит **все версии строк прямо в основной таблице** (heap-storage), в отличие от InnoDB с отдельным **undo log**.

**Скрытые системные колонки** у каждой версии строки:

| Колонка | Что хранит |
|---|---|
| **`xmin`** | id транзакции, **создавшей** эту версию |
| **`xmax`** | id транзакции, **удалившей или обновившей** эту версию (`0` если живая) |
| **`ctid`** | **физический адрес** версии — `(block, offset)` |

**Как определяется видимость:**
- транзакция со снимком `snapshot_xid` видит версию, если **`xmin <= snapshot_xid`** (создана раньше или в нашей T) **и** `xmax` либо `0`, либо больше нашего snapshot, либо принадлежит откатившейся T;
- так PG строит **snapshot isolation** без блокировок чтения.

**Последствия хранения версий в основной таблице:**
- **`UPDATE` всегда создаёт новую версию** (даже если меняется один байт);
- старые версии становятся **dead tuples**;
- их убирает **`VACUUM`**, иначе таблица и индексы **раздуваются (bloat)**;
- **HOT updates** (Heap-Only Tuple) частично спасают: если UPDATE не затрагивает индексируемые колонки и в странице есть место — новая версия пишется в **ту же страницу** без обновления индексов.

**Сравнение с InnoDB:** InnoDB пишет старые версии в **undo log** (отдельное хранилище, циклически переиспользуется), сами строки хранятся в clustered-индексе по PK — поэтому MVCC bloat в InnoDB **меньше**, но удлинение PK увеличивает все secondary-индексы.',
                'code_example' => '-- Посмотреть скрытые колонки версии строки
SELECT xmin, xmax, ctid, * FROM accounts WHERE id = 1;
-- xmin=12345 xmax=0 ctid=(0,3)

-- После UPDATE - новая версия с новым ctid
UPDATE accounts SET balance = balance + 1 WHERE id = 1;
SELECT xmin, xmax, ctid FROM accounts WHERE id = 1;
-- xmin=12346 xmax=0 ctid=(0,4)  -- новая позиция

-- Сколько dead tuples накопилось
SELECT relname, n_live_tup, n_dead_tup,
       round(100.0 * n_dead_tup / NULLIF(n_live_tup + n_dead_tup, 0), 2) AS dead_pct
FROM pg_stat_user_tables
ORDER BY dead_pct DESC NULLS LAST;',
                'code_language' => 'sql',
                'difficulty' => 4,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое режим autocommit в MySQL и как сгруппировать несколько запросов в транзакцию?',
                'answer' => 'По умолчанию в MySQL **`autocommit=1`**: каждый отдельный `INSERT`/`UPDATE`/`DELETE` **сразу фиксируется** как самостоятельная транзакция — `ROLLBACK` на него уже не подействует.

**Два способа сгруппировать запросы в одну транзакцию:**

1. **Явная транзакция** — самый частый вариант, не меняет глобальное поведение соединения:
   - `START TRANSACTION` (или `BEGIN`);
   - запросы;
   - `COMMIT` / `ROLLBACK`.
2. **Выключить autocommit** на сессию через `SET autocommit = 0` — транзакция открывается **неявно** при первом DML и держится до `COMMIT`.

**ORM под капотом:** `PDO::beginTransaction()` делает первый вариант — посылает `START TRANSACTION`, на `commit()` — `COMMIT`, на `rollBack()` — `ROLLBACK`.

**Важный подводный камень:** **DDL** (`CREATE`/`ALTER`/`DROP`) в MySQL вызывает **неявный `COMMIT`** и **не откатывается** — нельзя завернуть миграцию схемы в транзакцию (в отличие от PostgreSQL).',
                'code_example' => '-- Способ 1: явная транзакция (autocommit остаётся 1)
START TRANSACTION;
INSERT INTO orders(user_id, total) VALUES (1, 100);
UPDATE users SET orders_count = orders_count + 1 WHERE id = 1;
COMMIT; -- или ROLLBACK при ошибке

-- Способ 2: выключить autocommit на сессию
SET autocommit = 0;
INSERT INTO logs(msg) VALUES (\'a\');
INSERT INTO logs(msg) VALUES (\'b\');
COMMIT;
SET autocommit = 1;',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое SAVEPOINT и зачем он нужен внутри транзакции?',
                'answer' => '`SAVEPOINT name` создаёт **именованную точку** внутри активной транзакции, к которой можно **откатиться** через `ROLLBACK TO SAVEPOINT name`, не отменяя всю транзакцию целиком.

**Команды:**
- `SAVEPOINT name` — поставить точку;
- `ROLLBACK TO SAVEPOINT name` — частичный откат к точке;
- `RELEASE SAVEPOINT name` — удалить точку, когда возврат к ней больше не нужен.

**Зачем нужен:** если один из шагов **упал или дал не тот результат**, отменяем **только его**, а остальные изменения сохраняем и продолжаем работу.

**Применение в ORM (Laravel, Doctrine)** — имитация **«вложенных транзакций»**:
- вызов `DB::beginTransaction()` внутри уже открытой транзакции делает `SAVEPOINT trans2`;
- `commit`/`rollback` внутреннего блока — `RELEASE` / `ROLLBACK TO`;
- **реальная транзакция одна**, фиксируется только внешним `COMMIT`.',
                'code_example' => 'BEGIN;
INSERT INTO orders(user_id, total) VALUES (1, 100);

SAVEPOINT before_items;
INSERT INTO order_items(order_id, sku) VALUES (LAST_INSERT_ID(), \'A\');
INSERT INTO order_items(order_id, sku) VALUES (LAST_INSERT_ID(), \'BAD\'); -- упало
ROLLBACK TO SAVEPOINT before_items; -- откатили только items, заказ остался

INSERT INTO order_items(order_id, sku) VALUES (LAST_INSERT_ID(), \'B\');
RELEASE SAVEPOINT before_items;
COMMIT;',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что делает SELECT ... LOCK IN SHARE MODE и чем он отличается от FOR UPDATE?',
                'answer' => '**`LOCK IN SHARE MODE`** (в MySQL 8+ — **`FOR SHARE`**) ставит на отобранные строки **shared (S) lock**:
- другие транзакции тоже могут взять **S-lock** и читать строку;
- никто не сможет её **модифицировать**, пока все S-локи не сняты.

**`FOR UPDATE`** берёт **exclusive (X) lock**:
- никто **не может ни читать с локом, ни модифицировать** строку до `COMMIT`/`ROLLBACK`;
- обычный `SELECT` без лока — **в MVCC видит старую версию**, не блокируется.

**Матрица совместимости:**

|  | S | X |
|---|---|---|
| **S** | OK | wait |
| **X** | wait | wait |

**Когда что:**
- **`FOR SHARE`** — гарантировать **неизменность связанных данных** на время чтения (вычисление агрегата по таблице тарифов, чтобы никто не поменял тариф до конца расчёта);
- **`FOR UPDATE`** — транзакция **точно собирается** обновлять строку и хочет избежать **lost update** / читать актуальную версию (без MVCC старой).

**Грабли S-lock — deadlock:** две T взяли `FOR SHARE` на одной строке, обе хотят `UPDATE` — каждая ждёт, пока другая отпустит S. **Правило:** если знаешь, что будет `UPDATE` — **сразу бери `FOR UPDATE`**.',
                'difficulty' => 4,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Зачем нужны NOWAIT и SKIP LOCKED в SELECT ... FOR UPDATE?',
                'answer' => 'Модификаторы `SELECT ... FOR UPDATE` (и `FOR SHARE`), которые меняют поведение при встрече с уже занятыми строками.

| Модификатор | Что делает | Применение |
|---|---|---|
| (по умолчанию) | **ждёт** до `innodb_lock_wait_timeout` (50s по умолчанию) | стандарт |
| **`NOWAIT`** | **сразу падает** с ошибкой, если есть занятые строки | быстро отреагировать: ретрай, fallback, показать «занято» пользователю |
| **`SKIP LOCKED`** | **пропускает занятые** строки, возвращает только свободные | **очереди задач**: N воркеров параллельно разбирают разные записи |

**Канонический пример очереди на `SKIP LOCKED`:**

```sql
BEGIN;
SELECT id FROM jobs
WHERE status = \'pending\'
ORDER BY id
LIMIT 1
FOR UPDATE SKIP LOCKED;  -- каждый воркер берёт свою задачу
-- ... обработать ...
UPDATE jobs SET status = \'done\' WHERE id = ?;
COMMIT;
```

**Поддержка:**
- **PostgreSQL** — `NOWAIT` исторически, **`SKIP LOCKED`** с **PG 9.5** (2016);
- **MySQL/InnoDB** — оба с **MySQL 8.0** (2018). До этого приходилось эмулировать через короткие `innodb_lock_wait_timeout` + retry.

**Грабли:**
- `SKIP LOCKED` нарушает **`ORDER BY`** в семантическом смысле — порядок выдачи зависит от того, кто занят;
- без транзакции лок снимается сразу — модификатор бесполезен.',
                'difficulty' => 4,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое advisory lock в MySQL и когда он полезен?',
                'answer' => '**Advisory lock в MySQL** — **именованная блокировка уровня соединения**, которую приложение запрашивает явно:
- **`GET_LOCK(name, timeout)`** — взять (ждать до `timeout` секунд);
- **`RELEASE_LOCK(name)`** — отпустить;
- **`IS_FREE_LOCK(name)`** / **`IS_USED_LOCK(name)`** — проверить.

**Особенности:**
- **не привязана** ни к строкам, ни к таблицам;
- сама по себе **ничего не защищает** — это **координация на уровне приложения**;
- **живёт** ровно столько, сколько живёт **соединение** (закрылось — лок снят);
- **не транзакционная** — `COMMIT`/`ROLLBACK` её не трогает.

**Типичное применение:**
- гарантировать, что **cron-задача** запустится **в одном экземпляре** на весь кластер;
- **сериализовать** редкую операцию без полноценной транзакции;
- **дедупликация** обработки (например, webhook).

**Преимущество перед Redis-локами:**
- лок **умирает с соединением** — без проблем с TTL и потерей сессии;
- **проще консистентность**, чем у Redlock;
- одна точка отказа = БД (но она и так уже SPOF в большинстве приложений).

**Грабли:** до **MySQL 5.7.5** одно соединение могло держать **только один** именованный лок — `GET_LOCK(\'B\')` отпускал `\'A\'`. С 5.7.5+ — несколько локов одновременно.',
                'difficulty' => 4,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Как обеспечить идемпотентность вставки в консьюмере очереди через unique constraint?',
                'answer' => '**Идея:** опираемся на то, что движок **сам атомарно** проверяет уникальность — гонка решается **на уровне БД**, не приложения.

**Схема:**
1. На бизнес-ключ ставят **`UNIQUE` индекс** — `(order_id, event_id)` или отдельный **`idempotency_key`**;
2. При **повторной доставке** сообщения вторая вставка падает с ошибкой:
   - **MySQL:** `Duplicate Key (1062)` / `SQLSTATE 23000`;
   - **PostgreSQL:** `unique_violation (SQLSTATE 23505)`;
3. Приложение **перехватывает** эту ошибку и **считает её успехом** — операция уже выполнена, клиент получит тот же ответ.

**Альтернативный синтаксис без `try/catch`:**

| Запрос | Поведение |
|---|---|
| `INSERT ... ON DUPLICATE KEY UPDATE` (MySQL) | при конфликте — обновить |
| `INSERT IGNORE INTO ...` (MySQL) | при конфликте — **молча пропустить** |
| `INSERT ... ON CONFLICT DO NOTHING` (PG) | при конфликте — пропустить |
| `INSERT ... ON CONFLICT DO UPDATE` (PG) | UPSERT |

**Грабли:**
- ключ **должен совпадать** у всех попыток **одного логического события** — если генерим UUID на каждой попытке, идемпотентность ломается;
- ключ должен приходить **от клиента** (или от middleware с детерминированным id), а не генериться приложением;
- при `INSERT IGNORE` теряются и **другие** ошибки (NULL в NOT NULL колонке) — предпочитай явный `ON CONFLICT`.',
                'difficulty' => 4,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Как защитить лимит мест (например, 100 участников события) при высокой конкуренции?',
                'answer' => 'Классический пример **write skew**: наивное «`SELECT COUNT(*)` → проверить → `INSERT`» ломается под нагрузкой.

**Сравнение подходов:**

| Подход | Корректность | Производительность |
|---|---|---|
| `COUNT(*)` + `INSERT` без лока | **race condition** (две T видят 99 → обе вставят → 101) | быстро, но **некорректно** |
| `SELECT ... FOR UPDATE` на родителе | корректно | **узкое горлышко** — все регистрирующиеся выстраиваются в очередь на одной строке |
| **денормализованный счётчик + `CHECK`** | корректно | хорошо параллелится |
| `SERIALIZABLE` (PG, SSI) | корректно | дешевле локов, но **retry** на serialization failure |

**Рекомендуемый паттерн** — **денормализованный счётчик + `CHECK`**:

```sql
ALTER TABLE meetups ADD COLUMN booked_seats INT NOT NULL DEFAULT 0;
ALTER TABLE meetups ADD CONSTRAINT seats_limit
    CHECK (booked_seats <= total_seats);

-- В одной транзакции:
BEGIN;
INSERT INTO visitors(meetup_id, user_id) VALUES (?, ?);
UPDATE meetups
   SET booked_seats = booked_seats + 1
   WHERE id = ?;
-- Если 101-я попытка — UPDATE упадёт с CHECK violation
COMMIT;
```

**Почему работает:** атомарный `UPDATE ... SET col = col + 1` берёт **row-level X-lock** на `meetups`. Параллельные транзакции **выстраиваются в очередь** на этой строке, но **CHECK** срабатывает по факту инкремента — гонки нет.

**Грабли:** **гранулярность лока** — один lock на весь митап. Если митапов мало и нагрузка высокая, это узкое горлышко. Тогда: **sharded counter** (несколько строк-счётчиков на один митап, сумма по ним даёт total).',
                'difficulty' => 4,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Когда стоит использовать Redis-локи вместо встроенных блокировок SQL?',
                'answer' => '**Принцип:** защищать **данные** внутри БД — **средствами БД**, а Redis использовать там, где **БД нет** (между сервисами) или **допустим eventual consistency**.

**Когда Redis уместен:**
- **rate limiting** — счётчики с TTL, `INCR + EXPIRE` атомарно;
- **circuit breakers** — состояние «сервис упал» на короткое окно;
- **leader election** — выбор одного экземпляра cron;
- **дедупликация** на короткое окно (idempotency cache);
- **межсервисная координация** там, где нет общей БД.

**Когда SQL-блокировки выигрывают:**
- **целостность данных** внутри БД: `UNIQUE`, `FOR UPDATE`, `CHECK` работают **атомарно с `COMMIT`**;
- нет проблемы **истечения TTL посреди транзакции** (Redis-лок может истечь, пока БД ещё обрабатывает запрос — данные «без лока»);
- **advisory locks** (PG/MySQL) умирают с соединением — без TTL-гимнастики.

**Проблемы Redlock:**
- статья Мартина Клеппманна **«How to do distributed locking»** (2016) показала, что **Redlock не даёт строгих гарантий** при сбоях узлов и GC-паузах клиента;
- **fencing token** обязателен — но Redis сам по себе его не предоставляет.

**Сравнение:**

| Аспект | Advisory lock (БД) | Redis lock |
|---|---|---|
| Время жизни | до конца соединения/транзакции | TTL (могут истечь) |
| Атомарность с данными | да, в одной транзакции | нет, отдельная система |
| SPOF | БД | Redis (+ БД) |
| Производительность | хорошая (одна БД) | очень высокая |
| Корректность | сильная | требует fencing token |',
                'difficulty' => 4,
                'topic' => 'database.transactions_acid',
            ],
        ];
    }
}
