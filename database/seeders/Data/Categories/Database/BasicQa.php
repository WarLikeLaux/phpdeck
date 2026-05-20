<?php

namespace Database\Seeders\Data\Categories\Database;

class BasicQa
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Базы данных',
                'question' => 'Как подключиться к MySQL из PHP и в чём разница между mysqli и PDO?',
                'answer' => 'Два встроенных способа: расширение **`mysqli`** или класс **`PDO`**. Подключение в обоих случаях требует **DSN-строку**, имя пользователя и пароль.

**mysqli:**
- работает **только с MySQL/MariaDB**;
- два API — процедурный (`mysqli_query()`) и объектный (`$mysqli->query()`);
- только **позиционные** плейсхолдеры (`?`);
- требует явного `bind_param` с типами (`\'i\'`, `\'s\'`).

**PDO:**
- **абстрактный** драйвер — MySQL, PostgreSQL, SQLite, SQL Server одной API;
- **именованные** плейсхолдеры (`:id`) и позиционные (`?`);
- единый интерфейс ошибок (`PDOException`);
- удобный `fetch()`/`fetchAll()` с режимами (`PDO::FETCH_ASSOC`).

**Вывод:** в новом коде — **PDO** (или ORM поверх PDO, как Laravel/Eloquent).',
                'code_example' => '<?php
// PDO — переносимый драйвер
$pdo = new PDO(\'mysql:host=localhost;dbname=app;charset=utf8mb4\', \'user\', \'pass\');
$stmt = $pdo->prepare(\'SELECT * FROM users WHERE id = :id\');
$stmt->execute([\':id\' => 1]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// mysqli — только MySQL, позиционные плейсхолдеры
$mysqli = new mysqli(\'localhost\', \'user\', \'pass\', \'app\');
$stmt = $mysqli->prepare(\'SELECT * FROM users WHERE id = ?\');
$stmt->bind_param(\'i\', $id);
$stmt->execute();',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'database.basic_qa',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Зачем нужен первичный ключ простыми словами?',
                'answer' => '**Первичный ключ (PRIMARY KEY)** — это колонка (или набор колонок), по которой каждую строку можно **однозначно отличить** от других. Без PK нельзя сказать «обнови вот **эту** строку» — БД не поймёт какую.

Свойства:
- значения всегда **уникальные**;
- **не могут быть `NULL`**;
- на таблицу **только один** `PRIMARY KEY` (но он может быть **составным** — из нескольких колонок);
- под PK автоматически создаётся **индекс**, поиск по нему очень быстрый.

Обычно это `id` с **автоинкрементом** (`BIGSERIAL`/`AUTO_INCREMENT`) или **UUID**.',
                'code_example' => '-- Один числовой PK (самый частый случай)
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    email VARCHAR(255) NOT NULL
);

-- Составной PK: уникальна пара (order_id, product_id)
CREATE TABLE order_items (
    order_id BIGINT,
    product_id BIGINT,
    quantity INT,
    PRIMARY KEY (order_id, product_id)
);',
                'code_language' => 'sql',
                'difficulty' => 1,
                'topic' => 'database.basic_qa',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Зачем нужен внешний ключ простыми словами?',
                'answer' => '**Внешний ключ (FOREIGN KEY)** связывает таблицы и не даёт создавать **«висячие» ссылки**. `orders.user_id` ссылается на `users.id` — БД сама проверяет целостность:
- **не даст вставить** заказ для несуществующего юзера;
- **не даст удалить** юзера, у которого есть заказы (по умолчанию).

Поведение при удалении родителя настраивается через `ON DELETE`:
- `CASCADE` — удалить связанные заказы вслед за юзером;
- `SET NULL` — оставить заказ, обнулить `user_id`;
- `RESTRICT` / `NO ACTION` — запретить удаление, если есть заказы.

То же самое есть и для `ON UPDATE`.',
                'code_example' => 'CREATE TABLE orders (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    total DECIMAL(10, 2),
    FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
);

-- Попытка вставить заказ несуществующему юзеру упадёт
INSERT INTO orders (user_id, total) VALUES (9999, 100);
-- ERROR: foreign key violation',
                'code_language' => 'sql',
                'difficulty' => 1,
                'topic' => 'database.basic_qa',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое SQL?',
                'answer' => '**SQL (Structured Query Language)** — язык работы с реляционными базами данных. Он **декларативный**: ты описываешь, **что** хочешь получить, а **как** искать — решает сама СУБД.

Команды делят на группы:
- **DML** (Data Manipulation) — работа с данными: `SELECT`, `INSERT`, `UPDATE`, `DELETE`;
- **DDL** (Data Definition) — описание схемы: `CREATE`, `ALTER`, `DROP`;
- **DCL** (Data Control) — права: `GRANT`, `REVOKE`;
- **TCL** (Transaction Control) — транзакции: `BEGIN`, `COMMIT`, `ROLLBACK`.

SQL — **стандарт**, поэтому базовый синтаксис почти одинаково работает в **PostgreSQL**, **MySQL**, **SQLite**, **Oracle**, **SQL Server**. Отличия — в типах данных и функциях.',
                'code_example' => '-- DDL: создать таблицу
CREATE TABLE users (id BIGSERIAL PRIMARY KEY, name VARCHAR(255));

-- DML: добавить и выбрать данные
INSERT INTO users (name) VALUES (\'Иван\');
SELECT * FROM users WHERE name = \'Иван\';

-- TCL: транзакция
BEGIN;
UPDATE users SET name = \'Пётр\' WHERE id = 1;
COMMIT;',
                'code_language' => 'sql',
                'difficulty' => 1,
                'topic' => 'database.basic_qa',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Чем отличается DELETE от TRUNCATE?',
                'answer' => '**`DELETE`** — удаляет строки **построчно**:
- поддерживает `WHERE` — можно удалить часть;
- пишет каждую удалённую запись в **журнал транзакции** — медленнее на больших объёмах;
- срабатывают **триггеры**;
- **откатывается** через `ROLLBACK`.

**`TRUNCATE TABLE`** — моментально **очищает таблицу целиком**:
- **без `WHERE`** — только всё сразу;
- сбрасывает `AUTO_INCREMENT` / `SERIAL`;
- триггеры обычно **не срабатывают**;
- в **MySQL** — неявный `COMMIT`, **откатить нельзя**;
- в **PostgreSQL** — **транзакционен**, можно `ROLLBACK`;
- на больших таблицах **на порядки быстрее** — фактически пересоздание файла данных.

**`DROP TABLE`** — когда нужно убрать и **саму структуру** таблицы.',
                'code_example' => '-- DELETE: построчно, с WHERE, в транзакции
BEGIN;
DELETE FROM orders WHERE created_at < \'2020-01-01\';
ROLLBACK; -- строки вернутся

-- TRUNCATE: моментальная очистка, в MySQL без отката
TRUNCATE TABLE orders;',
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.basic_qa',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое СУБД и чем она отличается от базы данных?',
                'answer' => '- **База данных (БД)** — сами **данные**, организованные определённым образом (таблицы, документы, пары ключ-значение).
- **СУБД** (Система Управления Базами Данных, **DBMS**) — это **программа**, которая управляет БД: принимает запросы, хранит данные, индексирует их, выполняет транзакции, контролирует доступ.

**Аналогия:** БД — это книги в библиотеке, СУБД — библиотекарь, который умеет их искать и выдавать.

**Примеры СУБД:** PostgreSQL, MySQL, SQLite, MongoDB, Redis.

В разговоре «БД» и «СУБД» часто говорят как синонимы, но строго — это **разные вещи**.',
                'difficulty' => 1,
                'topic' => 'database.basic_qa',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что делает WHERE в SQL-запросе?',
                'answer' => '`WHERE` **фильтрует строки** таблицы по условию — в результат попадут только те, для которых условие истинно.

Условия можно комбинировать:
- `AND` — оба должны выполняться;
- `OR` — хотя бы одно;
- `NOT` — отрицание.

Операторы сравнения:
- `=` (равно), `<>` или `!=` (не равно), `<`, `>`, `<=`, `>=`;
- `BETWEEN a AND b` — диапазон;
- `IN (...)` — значение из списка;
- `LIKE` — поиск по шаблону (`%` — любые символы, `_` — один);
- `IS NULL` / `IS NOT NULL` — проверка на `NULL` (через `=` `NULL` искать **нельзя**).

Без `WHERE` команда применится **ко всей таблице** — особенно опасно с `UPDATE` и `DELETE`.',
                'code_example' => '-- Найти взрослых из России или Беларуси, не удалённых
SELECT id, name FROM users
WHERE age >= 18
  AND country IN (\'RU\', \'BY\')
  AND deleted_at IS NULL;

-- Диапазон через BETWEEN
SELECT * FROM orders WHERE total BETWEEN 100 AND 1000;

-- Поиск по началу строки
SELECT * FROM products WHERE name LIKE \'iPhone%\';',
                'code_language' => 'sql',
                'difficulty' => 1,
                'topic' => 'database.basic_qa',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Какие базовые типы данных есть в SQL?',
                'answer' => '**Числовые:**
- `INT` — обычное целое;
- `BIGINT` — большое целое (для `id`);
- `DECIMAL(p, s)` — **точное** дробное (для денег);
- `FLOAT` / `DOUBLE` — приближённое дробное (для научных расчётов, **не** для денег).

**Строки:**
- `CHAR(n)` — фиксированной длины;
- `VARCHAR(n)` — переменной длины с лимитом;
- `TEXT` — длинный текст без явного лимита.

**Даты и время:**
- `DATE` — только дата;
- `TIME` — только время;
- `TIMESTAMP` / `DATETIME` — дата + время.

**Прочее:**
- `BOOLEAN` — `true` / `false`;
- `BLOB` / `BYTEA` — бинарные данные (файлы, картинки).

Конкретные имена и размеры чуть отличаются между **MySQL** и **PostgreSQL**, но идея одинаковая.',
                'code_example' => 'CREATE TABLE products (
    id          BIGSERIAL PRIMARY KEY,        -- большое целое, автоинкремент
    name        VARCHAR(255) NOT NULL,        -- строка до 255 символов
    description TEXT,                          -- длинный текст
    price       DECIMAL(10, 2) NOT NULL,      -- точное дробное для денег
    in_stock    BOOLEAN DEFAULT true,         -- логическое
    created_at  TIMESTAMP DEFAULT NOW()       -- дата + время
);',
                'code_language' => 'sql',
                'difficulty' => 1,
                'topic' => 'database.basic_qa',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Зачем хранить деньги в DECIMAL, а не во FLOAT?',
                'answer' => '**`FLOAT`** и **`DOUBLE`** — двоичные числа с **плавающей точкой**. Не каждое десятичное число (например, `0.1`) точно представимо в двоичном виде.

**Классический пример:** `0.1 + 0.2` даёт `0.30000000000000004` — для денег это **недопустимо**: копейки будут «теряться» при суммировании миллионов транзакций.

**`DECIMAL(p, s)`** хранит число как **десятичные цифры с фиксированной точностью**:
- `p` — всего цифр;
- `s` — из них после запятой.

Стандарт для денег — **`DECIMAL(10, 2)`** (до `99999999.99`).

**Альтернатива:** хранить деньги в **минимальных единицах** (копейки, центы) обычным `BIGINT` — без `DECIMAL` и его расчётов на стороне БД. Делят на 100 уже в приложении при выводе.',
                'code_example' => 'CREATE TABLE payments (
    id BIGSERIAL PRIMARY KEY,
    amount DECIMAL(10, 2) NOT NULL  -- 99999999.99 максимум
);

-- FLOAT теряет копейки
SELECT 0.1::float + 0.2::float;       -- 0.30000000000000004
SELECT 0.1::decimal + 0.2::decimal;   -- 0.3',
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.basic_qa',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое VIEW (представление) в SQL?',
                'answer' => '**`VIEW`** — это **сохранённый именованный `SELECT`-запрос**, к которому можно обращаться как к обычной таблице. Сам по себе `VIEW` **данных не хранит** — при каждом запросе к нему БД на лету подставляет его текст и выполняет внутренний `SELECT`.

**Зачем нужен:**
- **спрятать сложный `JOIN`** за простым именем;
- **ограничить колонки/строки** для разных ролей (выдать аналитику только `VIEW`, а не таблицу);
- **переиспользовать** одну и ту же логику в нескольких запросах.

**Если нужны кэшированные данные** (тяжёлая аналитика) — используют **`MATERIALIZED VIEW`** (PostgreSQL): он **хранит результат на диске** и обновляется по команде `REFRESH MATERIALIZED VIEW`.',
                'code_example' => 'CREATE VIEW active_users AS
SELECT id, name, email FROM users
WHERE deleted_at IS NULL;

-- Используем как обычную таблицу
SELECT COUNT(*) FROM active_users;',
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.basic_qa',
            ],
        ];
    }
}
