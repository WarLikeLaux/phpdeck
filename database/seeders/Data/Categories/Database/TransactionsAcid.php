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
                'answer' => 'Транзакция - это группа операций над БД, выполняющаяся как единое целое: либо все операции применяются, либо ни одна (откат). Управляется командами BEGIN/START TRANSACTION, COMMIT, ROLLBACK. Классический пример: перевод денег - надо снять с одного счёта и зачислить на другой; если упадёт между, без транзакции деньги исчезнут.',
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
                'answer' => 'ACID — набор гарантий, которые СУБД даёт для каждой транзакции: Atomicity (всё или ничего, ROLLBACK откатывает частичные изменения), Consistency (после COMMIT не нарушены ограничения PK/FK/CHECK/UNIQUE и бизнес-инварианты), Isolation (параллельные транзакции не видят промежуточных состояний друг друга, степень регулируется уровнем изоляции), Durability (после COMMIT данные сохранены даже при падении сервера — за счёт WAL/redo log). Реляционные БД (PostgreSQL, MySQL/InnoDB) гарантируют ACID; многие NoSQL ради скорости и горизонтального масштабирования отдают только BASE — eventual consistency. На собесе важно уметь раскрыть каждую букву на примере перевода денег между счетами.',
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
                'answer' => 'Атомарность означает: транзакция выполняется целиком или не выполняется вообще. Промежуточных состояний нет. Если в середине что-то падает, БД откатывает все уже сделанные изменения транзакции. Простыми словами: всё или ничего, как нажатие одной кнопки.',
                'difficulty' => 2,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'C в ACID - что такое Consistency (согласованность)?',
                'answer' => 'Consistency: транзакция сохраняет инварианты приложения — после COMMIT БД находится в логически корректном состоянии. БД помогает проверить декларативные ограничения (PK, FK, CHECK, NOT NULL, UNIQUE) и откатывает транзакцию, если они нарушены, но сами инварианты определяет разработчик: например, "сумма счётов сохраняется при переводе" никаким CHECK/FK не выразить, эту логику пишут в коде транзакции. Поэтому C в ACID — самая "слабая" и прикладная буква, которая в значительной части дублирует A+I плюс прикладную логику.',
                'difficulty' => 2,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'I в ACID - что такое Isolation (изолированность)?',
                'answer' => 'Isolation определяет, насколько параллельные транзакции "видят" друг друга. В идеале результат должен быть как при последовательном выполнении (serializable schedule), но это дорого, поэтому стандарт SQL вводит уровни: READ UNCOMMITTED (допускает dirty read), READ COMMITTED (видит только закоммиченные изменения, но возможны non-repeatable read), REPEATABLE READ (одна и та же строка читается одинаково), SERIALIZABLE (никаких аномалий). Чем строже уровень — тем больше блокировок или откатов и ниже throughput. PostgreSQL по умолчанию READ COMMITTED, MySQL/InnoDB — REPEATABLE READ. На собесе важно знать аномалии каждого уровня и уметь выбрать минимально достаточный.',
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
                'answer' => 'Долговечность: после COMMIT изменения сохранены навсегда, даже если сервер сразу упадёт. БД пишет изменения в журнал (WAL/redo log) на диск перед подтверждением. Простыми словами: COMMIT прошёл - значит, данные точно не пропадут.',
                'difficulty' => 2,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Какие бывают уровни изоляции транзакций?',
                'answer' => '4 стандартных уровня по SQL и аномалии, которые они допускают: READ UNCOMMITTED - dirty/non-repeatable/phantom; READ COMMITTED - non-repeatable/phantom; REPEATABLE READ - phantom (по стандарту); SERIALIZABLE - ничего. Особенности: PostgreSQL не реализует READ UNCOMMITTED (минимум READ COMMITTED), а в его REPEATABLE READ (snapshot isolation) фантомы тоже не возникают, но возможен write skew. InnoDB на REPEATABLE READ блокирует фантомы через gap-locks. SERIALIZABLE в PG реализован через SSI (Serializable Snapshot Isolation) и может откатывать транзакции с ошибкой serialization_failure.',
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
                'answer' => 'Lost Update - аномалия, когда две транзакции читают одно значение, обе модифицируют и пишут, и обновление одной перезаписывает другое - изменение "теряется". Пример: T1 и T2 читают balance=100, T1 пишет 90 (-10), T2 пишет 80 (-20), вместо ожидаемых 70. Защита: SERIALIZABLE; SELECT FOR UPDATE перед UPDATE; атомарный UPDATE с выражением (UPDATE accounts SET balance = balance - 10); оптимистическая блокировка через version-столбец.',
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
                'answer' => 'Грязное чтение - транзакция читает данные, изменённые другой ещё незакоммиченной транзакцией. Если та откатится - мы прочитали "несуществующие" данные. Случается на уровне READ UNCOMMITTED. PostgreSQL вообще не допускает грязного чтения, минимальный уровень - READ COMMITTED.',
                'difficulty' => 4,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое Non-repeatable Read?',
                'answer' => 'Неповторяемое чтение - в рамках одной транзакции мы читаем строку дважды и получаем разные значения, потому что между чтениями другая транзакция её обновила и закоммитила. Случается на READ COMMITTED. Решается уровнем REPEATABLE READ или выше.',
                'difficulty' => 4,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое Phantom Read (фантомное чтение)?',
                'answer' => 'Фантомное чтение - в одной транзакции мы выполняем один и тот же запрос (SELECT WHERE) дважды и получаем разное количество строк, потому что другая транзакция вставила/удалила подходящие. Решается уровнем SERIALIZABLE. В PostgreSQL REPEATABLE READ уже защищает от фантомов (snapshot-уровень).',
                'difficulty' => 4,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое Write Skew?',
                'answer' => 'Write Skew - аномалия, когда две транзакции читают одни и те же данные, принимают решения, и пишут разные строки, нарушая бизнес-инвариант. Пример: правило "хотя бы один врач на смене", обе транзакции читают, видят что есть двое, и обе уходят с дежурства. Решается на уровне SERIALIZABLE или явными блокировками SELECT FOR UPDATE.',
                'difficulty' => 5,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое блокировки (locks) и какие у них уровни гранулярности?',
                'answer' => 'Блокировка — механизм, не дающий нескольким транзакциям одновременно конфликтно работать с одним ресурсом. По типу: shared/S (несколько транзакций могут одновременно читать) и exclusive/X (одна транзакция модифицирует, остальные ждут). По гранулярности: row-level (PostgreSQL, InnoDB — самая мелкая, максимум concurrency), table-level (LOCK TABLES в MySQL, LOCK TABLE ... IN ACCESS EXCLUSIVE MODE в PG — для DDL и массовых операций), а также advisory locks — произвольный ключ, который БД не интерпретирует, удобно для распределённой координации. Чем мельче гранулярность, тем выше параллелизм, но тем дороже накладные расходы на отслеживание. На собесе спрашивают сочетания: например, UPDATE в InnoDB берёт X-lock на строку и intention exclusive (IX) на таблицу.',
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
                'answer' => 'Shared (S, разделяемая) — "несколько читателей могут держать одновременно, но писать никто не может". Захватывается через SELECT ... FOR SHARE / LOCK IN SHARE MODE. Exclusive (X, эксклюзивная) — "только один владелец, никаких других S или X на этом ресурсе"; ставится автоматически при UPDATE/DELETE или явно через SELECT ... FOR UPDATE. Матрица совместимости: S+S совместимы, S+X и X+X — нет. Важный нюанс MVCC-баз (PostgreSQL, InnoDB): row-level X НЕ блокирует обычный SELECT — читатели видят прежний снапшот, ждать должны только пишущие транзакции и FOR UPDATE/SHARE. Поэтому в Postgres "обычное чтение никогда не блокирует запись и наоборот".',
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
                'answer' => 'SELECT ... FOR UPDATE захватывает X-блокировку на каждой выбранной строке до конца текущей транзакции (то есть до COMMIT/ROLLBACK). Другие транзакции, пытающиеся прочитать эти строки с FOR UPDATE/FOR SHARE, обновить или удалить, будут ждать (или упадут с lock timeout). Обычный SELECT в MVCC-СУБД при этом не блокируется — видит прежнюю версию. Классическое применение: read-modify-write, где между чтением и записью нельзя пускать конкурентов (списание баланса, резерв места, инкремент счётчика без атомарного выражения). Работает только внутри транзакции; вне BEGIN/COMMIT блокировка снимается сразу. Модификаторы NOWAIT (упасть мгновенно вместо ожидания) и SKIP LOCKED (пропустить занятые строки — полезно для очередей) расширяют поведение.',
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
                'answer' => 'SELECT ... FOR SHARE (в старом MySQL — LOCK IN SHARE MODE) ставит S-блокировку на выбранные строки до конца транзакции. Несколько транзакций могут одновременно держать S-lock на одной строке и читать её, но никто не сможет UPDATE/DELETE/FOR UPDATE, пока хотя бы один S-lock не отпущен. Применение: гарантировать, что связанные данные не изменятся, пока транзакция их использует — например, прочитать справочник тарифов и посчитать стоимость, не дав никому в этот момент изменить тариф. Важно понимать, что FOR SHARE может приводить к deadlock\'ам: если две транзакции взяли S на одной строке, а потом обе пытаются её UPDATE — обе ждут друг друга. Поэтому когда понятно, что точно будет UPDATE, лучше сразу брать FOR UPDATE.',
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
                'answer' => 'Advisory lock (рекомендательная блокировка) - блокировка по произвольному ключу (числу), не привязана к строкам. БД не использует её для своей логики - её смысл задаёт приложение. Удобно для распределённых cron-задач, очередей, координации между процессами. В PostgreSQL: pg_advisory_lock(key).',
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
                'answer' => 'Deadlock (взаимная блокировка) - ситуация, когда транзакция A ждёт ресурс, удерживаемый B, а B ждёт ресурс, удерживаемый A. БД сама обнаруживает deadlock и убивает одну из транзакций (откатывая её). Простыми словами: два человека пытаются разойтись в узком коридоре и не могут. Как избегать: всегда захватывать блокировки в одинаковом порядке, держать транзакции короткими, использовать SELECT FOR UPDATE NOWAIT/SKIP LOCKED, ретраить откатившиеся транзакции.',
                'difficulty' => 4,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Чем отличаются пессимистичные (FOR UPDATE) и оптимистичные блокировки? Когда какую применять?',
                'answer' => 'Пессимистичная блокировка - "сначала запрещаю, потом меняю": SELECT ... FOR UPDATE ставит row-level X-lock на строки, и другие транзакции, попытавшиеся их прочитать с FOR UPDATE/UPDATE/DELETE, будут ждать (или упадут с lock timeout). Гарантирует отсутствие конфликта, но снижает concurrency и может привести к взаимоблокировкам (deadlock). Оптимистичная блокировка - "сначала меняю, на коммите проверяю": в таблицу добавляется поле version (или updated_at), при UPDATE сравнивается с прочитанной ранее версией; если кто-то успел изменить - 0 affected rows и приложение перезапускает операцию или показывает пользователю конфликт. Никаких блокировок в БД, высокий throughput, но при частых конфликтах теряется работа. Когда что использовать: PESSIMISTIC - короткие критические секции с высокой вероятностью конфликта (списание со счёта, резерв билета, инкремент счётчика); требуется явная транзакция, держать lock как можно меньше. OPTIMISTIC - длительные пользовательские операции (редактирование документа в форме, "вы открыли страницу 10 минут назад"), низкая вероятность одновременного изменения, нельзя удерживать транзакцию через сетевой round-trip. В Laravel: pessimistic - lockForUpdate() / sharedLock(); optimistic - вручную через колонку version и WHERE version = ? в UPDATE.',
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
                'answer' => 'PostgreSQL хранит все версии строк прямо в основной таблице (heap), а не в отдельном undo-логе как InnoDB. У каждой строки есть скрытые системные колонки: xmin — id транзакции, создавшей версию, xmax — id транзакции, удалившей или обновившей её, ctid — физический адрес версии (block, offset). Видимость определяется сравнением xmin/xmax со снимком транзакции. Из-за такого хранения UPDATE всегда создаёт новую версию, старые версии копятся как «мёртвые» строки и убираются VACUUM, иначе возникает bloat.',
                'difficulty' => 4,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое режим autocommit в MySQL и как сгруппировать несколько запросов в транзакцию?',
                'answer' => 'По умолчанию в MySQL autocommit=1: каждый отдельный INSERT/UPDATE/DELETE сразу фиксируется как самостоятельная транзакция, ROLLBACK на него уже не подействует. Чтобы выполнить несколько операций атомарно, есть два пути: либо явно открыть блок START TRANSACTION (или BEGIN), выполнить запросы и закрыть его COMMIT/ROLLBACK — это самый частый вариант, который не меняет глобальное поведение соединения; либо выключить режим через SET autocommit = 0, тогда транзакция открывается неявно при первом DML и держится до COMMIT. PDO::beginTransaction под капотом делает первый вариант — временно посылает START TRANSACTION, на commit() — COMMIT, на rollBack() — ROLLBACK. Важно: DDL (CREATE/ALTER/DROP) в MySQL вызывают неявный COMMIT и не откатываются.',
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
                'answer' => 'SAVEPOINT name создаёт именованную точку внутри активной транзакции, к которой можно откатиться через ROLLBACK TO SAVEPOINT name, не отменяя всю транзакцию целиком. Это нужно для частичного отката: если один из шагов упал или дал не тот результат, отменяем только его, а остальные изменения сохраняем и продолжаем работу. RELEASE SAVEPOINT name удаляет точку, когда возврат к ней больше не нужен. ORM (Laravel, Doctrine) используют savepoint-ы для имитации "вложенных транзакций": вызов DB::beginTransaction() внутри уже открытой транзакции делает SAVEPOINT trans2, а commit/rollback внутреннего блока — RELEASE/ROLLBACK TO. Реальная транзакция одна, фиксируется только внешним COMMIT.',
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
                'answer' => 'LOCK IN SHARE MODE (в MySQL 8 — FOR SHARE) ставит на отобранные строки разделяемую (S) блокировку: другие транзакции тоже могут взять S-lock и читать строку, но никто не сможет её изменить, пока все S-локи не сняты. FOR UPDATE берёт эксклюзивную (X) блокировку: и читать, и модифицировать строку другим транзакциям нельзя до COMMIT/ROLLBACK. S-lock уместен, когда нужно гарантировать неизменность связанных данных на время чтения (например, при вычислении агрегата), а X-lock — когда транзакция точно собирается обновлять строку и хочет избежать lost update.',
                'difficulty' => 4,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Зачем нужны NOWAIT и SKIP LOCKED в SELECT ... FOR UPDATE?',
                'answer' => 'NOWAIT заставляет MySQL не ждать освобождения чужой блокировки, а сразу вернуть ошибку — это даёт приложению быстро отреагировать (повторить, пропустить, показать пользователю "занято") вместо зависания на innodb_lock_wait_timeout. SKIP LOCKED пропускает уже заблокированные строки и возвращает только свободные — идеальная семантика для очередей задач, чтобы N воркеров параллельно разбирали разные записи без конкуренции за одни и те же. Оба модификатора появились в MySQL 8, до этого приходилось эмулировать поведение через попытки с короткими таймаутами.',
                'difficulty' => 4,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое advisory lock в MySQL и когда он полезен?',
                'answer' => 'Advisory lock — это именованная блокировка уровня соединения, которую приложение запрашивает командами GET_LOCK(name, timeout) и снимает RELEASE_LOCK(name). Она не привязана ни к строкам, ни к таблицам, и сама по себе ничего не защищает — это координация на уровне приложения. Типичное применение: гарантировать, что cron-задача или фоновая обработка запустится в одном экземпляре на весь кластер инстансов, или сериализовать редкую операцию без полноценной транзакции. Преимущество перед Redis-локами — лок живёт ровно столько, сколько живёт соединение, без проблем с TTL и потерей сессии.',
                'difficulty' => 4,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Как обеспечить идемпотентность вставки в консьюмере очереди через unique constraint?',
                'answer' => 'На бизнес-ключ ставят UNIQUE-индекс (например, (order_id, event_id) или отдельный idempotency_key). При повторной доставке сообщения вторая вставка падает с ошибкой Duplicate Key (1062 в MySQL, 23505 в PostgreSQL). Приложение перехватывает эту ошибку и считает её успехом — операция уже выполнена, клиент получит тот же ответ. Альтернативный синтаксис — INSERT ... ON DUPLICATE KEY UPDATE или INSERT IGNORE, чтобы не ловить исключение. Главное — ключ должен совпадать у всех попыток одного логического события, иначе идемпотентность ломается.',
                'difficulty' => 4,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Как защитить лимит мест (например, 100 участников события) при высокой конкуренции?',
                'answer' => 'Самый надёжный способ — денормализованный счётчик booked_seats в родительской таблице плюс CHECK (booked_seats <= total_seats). В одной транзакции INSERT в visitors сопровождается UPDATE meetups SET booked_seats = booked_seats + 1 WHERE id = :id, и движок сам отклонит коммит, нарушающий CHECK. Без счётчика приходится сериализовать всех регистрирующихся через SELECT * FROM meetups WHERE id = :id FOR UPDATE на родителе — корректно, но это узкое горлышко. Подход с COUNT(*) перед INSERT всегда содержит race condition: две транзакции увидят 99, обе вставят, получится 101.',
                'difficulty' => 4,
                'topic' => 'database.transactions_acid',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Когда стоит использовать Redis-локи вместо встроенных блокировок SQL?',
                'answer' => 'Redis уместен для координационных задач, не связанных с консистентностью данных: rate limiting (счётчики с TTL), circuit breakers, выбор лидера для cron, дедупликация на короткое окно. SQL-блокировки выигрывают, когда речь о целостности данных в самой БД: уникальные ключи, FOR UPDATE на строке, CHECK-ограничения работают атомарно с COMMIT и не имеют проблем с истечением TTL посреди транзакции. Распределённый Redis-лок (Redlock) ещё и не даёт строгих гарантий при сбоях узлов. Принцип: защищать данные внутри БД средствами БД, а Redis использовать там, где БД нет (между сервисами) или где допустим eventual consistency.',
                'difficulty' => 4,
                'topic' => 'database.transactions_acid',
            ],
        ];
    }
}
