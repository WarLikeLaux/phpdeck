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
                'answer' => 'Подключение делают расширением mysqli или классом PDO, передавая DSN-строку, имя пользователя и пароль. mysqli работает только с MySQL и имеет процедурный и объектный API, PDO абстрактен — поддерживает PostgreSQL, SQLite, SQL Server и другие СУБД, что упрощает смену драйвера. У PDO есть именованные плейсхолдеры (:id) в подготовленных запросах, у mysqli — только позиционные знаки вопроса. Для нового кода почти всегда выбирают PDO ради переносимости и удобного API.',
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
                'answer' => 'Чтобы каждую строку можно было однозначно отличить от других. Без PK ты не можешь сказать «обнови вот ЭТУ строку» — БД не поймёт какую. Обычно это id с автоинкрементом или UUID. Свойства: всегда уникальный, не может быть NULL, в таблице только один PK (но он может состоять из нескольких колонок — составной ключ). Под PK автоматически создаётся индекс — поиск по нему очень быстрый.',
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
                'answer' => 'Чтобы связывать таблицы и не давать создавать «висячие» ссылки. orders.user_id ссылается на users.id — БД не даст вставить заказ для несуществующего юзера и не даст удалить юзера, у которого есть заказы. Поведение при удалении настраивается через ON DELETE: CASCADE (удалить связанные заказы вслед за юзером), SET NULL (оставить заказ, обнулить user_id), RESTRICT (запретить удаление, если есть заказы).',
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
                'answer' => 'SQL (Structured Query Language) — язык работы с реляционными базами данных. Он декларативный: ты описываешь, ЧТО хочешь получить, а КАК искать — решает сама СУБД. Команды делят на группы: DML — работа с данными (SELECT, INSERT, UPDATE, DELETE); DDL — описание схемы (CREATE, ALTER, DROP); DCL/TCL — права и транзакции (GRANT, COMMIT, ROLLBACK). SQL — это стандарт, поэтому базовый синтаксис почти одинаково работает в PostgreSQL, MySQL, SQLite, Oracle и SQL Server (отличия — в типах данных и функциях).',
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
                'answer' => 'DELETE удаляет строки построчно: поддерживает WHERE, пишет каждую удалённую запись в журнал транзакции, срабатывают триггеры, операция откатывается через ROLLBACK. TRUNCATE TABLE моментально очищает таблицу целиком — без WHERE, обычно без триггеров, сбрасывает AUTO_INCREMENT, и в MySQL делает неявный COMMIT и откатить её нельзя (в PostgreSQL TRUNCATE как раз транзакционен). Для удаления всех строк из большой таблицы TRUNCATE на порядки быстрее, потому что внутри это фактически пересоздание файла данных. DROP TABLE — для случая, когда нужно убрать и саму структуру.',
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
                'answer' => 'База данных (БД) — это сами данные, организованные определённым образом (таблицы, документы, пары ключ-значение). СУБД (Система Управления Базами Данных, DBMS) — это ПРОГРАММА, которая управляет БД: принимает запросы, хранит данные, индексирует их, выполняет транзакции, контролирует доступ. Аналогия: БД — это сами книги в библиотеке, СУБД — библиотекарь, который умеет их искать и выдавать. Примеры СУБД: PostgreSQL, MySQL, SQLite, MongoDB, Redis. В разговоре «БД» и «СУБД» часто говорят как синонимы, но строго это разные вещи.',
                'difficulty' => 1,
                'topic' => 'database.basic_qa',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что делает WHERE в SQL-запросе?',
                'answer' => 'WHERE фильтрует строки таблицы по условию: в результат попадут только те, для которых условие истинно. Условия можно комбинировать через AND (оба должны выполняться) и OR (хотя бы одно), отрицание — через NOT. Операторы сравнения: = (равно), <> или != (не равно), <, >, <=, >=, BETWEEN a AND b (диапазон), IN (значение из списка), LIKE (шаблон), IS NULL / IS NOT NULL (проверка на NULL). Без WHERE команда применится ко ВСЕЙ таблице — это особенно опасно с UPDATE и DELETE.',
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
                'answer' => 'Числовые: INT (обычное целое), BIGINT (большое целое для id), DECIMAL(p, s) (точное дробное — для денег), FLOAT/DOUBLE (приближённое дробное — для научных расчётов, НЕ для денег). Строки: CHAR(n) (фиксированной длины), VARCHAR(n) (переменной длины с лимитом), TEXT (длинный текст). Даты и время: DATE (только дата), TIME (только время), TIMESTAMP/DATETIME (дата и время вместе). Логический: BOOLEAN (true/false). Бинарный: BLOB/BYTEA (файлы, картинки). Конкретные имена и размеры чуть отличаются между MySQL и PostgreSQL, но идея одинаковая.',
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
                'answer' => 'FLOAT и DOUBLE — это двоичные числа с плавающей точкой, и далеко не каждое десятичное число (например, 0.1) точно представимо в двоичном виде. Из-за этого простой расчёт 0.1 + 0.2 даёт 0.30000000000000004 — для денег это недопустимо. DECIMAL(p, s) хранит число как десятичные цифры с фиксированной точностью, поэтому никакие копейки не "потеряются" при суммировании. Стандарт: DECIMAL(10, 2) — 10 цифр всего, 2 после запятой. Альтернатива — хранить деньги в копейках/центах целым числом BIGINT.',
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
                'answer' => 'VIEW — это сохранённый именованный SELECT-запрос, к которому можно обращаться как к обычной таблице. Сама таблица VIEW не хранит данные; при каждом запросе к VIEW БД на лету подставляет его текст и выполняет внутренний SELECT. Зачем: спрятать сложный JOIN за простым именем, ограничить набор колонок для разных ролей, переиспользовать одну и ту же логику в разных запросах. Если нужны кэшированные данные — используют MATERIALIZED VIEW (он уже хранит результат на диске).',
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
