<?php

namespace Database\Seeders\Data\Categories\Database;

class BasicConcepts
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Базы данных',
                'question' => 'Что такое база данных простыми словами?',
                'answer' => 'База данных (БД) - это организованное хранилище данных, к которому удобно обращаться, добавлять, изменять и удалять записи. Простыми словами: представь огромный шкаф с папками, где всё разложено по полочкам и есть быстрый способ найти нужное. СУБД (Система Управления Базами Данных) - это программа, которая управляет этим шкафом: PostgreSQL, MySQL, SQLite, Oracle и т.д.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 1,
                'topic' => 'database.basic_concepts',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое таблица, строка и столбец?',
                'answer' => 'Таблица - это набор данных в виде сетки (как Excel-лист). Строка (row, record, кортеж) - одна запись, например один пользователь. Столбец (column, поле, атрибут) - характеристика записи, например имя или email. Каждый столбец имеет тип данных (integer, varchar, timestamp и т.д.).',
                'code_example' => <<<'SQL'
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
SQL,
                'code_language' => 'sql',
                'difficulty' => 1,
                'topic' => 'database.basic_concepts',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое реляционная база данных?',
                'answer' => 'Реляционная БД - это БД, основанная на математической модели отношений (relations). Данные хранятся в таблицах, связанных между собой через ключи. Главные принципы: данные структурированы, есть схема, поддерживаются транзакции и SQL. Примеры: PostgreSQL, MySQL, Oracle, SQL Server, SQLite.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 1,
                'topic' => 'database.basic_concepts',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое первичный ключ (PRIMARY KEY)?',
                'answer' => 'Первичный ключ - это столбец (или набор столбцов), который уникально идентифицирует каждую строку в таблице. Свойства: значение всегда уникальное, не может быть NULL, в таблице только один PRIMARY KEY. Обычно на первичный ключ автоматически создаётся индекс.',
                'code_example' => <<<'SQL'
-- Простой первичный ключ
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    email VARCHAR(255)
);

-- Составной первичный ключ
CREATE TABLE order_items (
    order_id BIGINT,
    product_id BIGINT,
    quantity INT,
    PRIMARY KEY (order_id, product_id)
);
SQL,
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.basic_concepts',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое внешний ключ (FOREIGN KEY)?',
                'answer' => 'Внешний ключ - это столбец, который ссылается на первичный ключ другой таблицы. Он обеспечивает ссылочную целостность: нельзя добавить заказ для несуществующего пользователя. У FK можно настроить ON DELETE и ON UPDATE: CASCADE, SET NULL, RESTRICT, NO ACTION.',
                'code_example' => <<<'SQL'
CREATE TABLE orders (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    total DECIMAL(10,2),
    FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);
SQL,
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.basic_concepts',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое UNIQUE constraint?',
                'answer' => 'UNIQUE constraint гарантирует, что во всех строках значения в указанном столбце (или комбинации столбцов) будут уникальны. В отличие от PRIMARY KEY, UNIQUE-столбец обычно может содержать NULL. Можно иметь несколько UNIQUE constraints на одной таблице. ВАЖНО про NULL - поведение РАЗНОЕ у разных СУБД, классическая cross-vendor ловушка на собеседованиях. PostgreSQL и MySQL/MariaDB: NULL != NULL для целей уникальности, поэтому в UNIQUE-колонку можно вставить ЛЮБОЕ количество NULL-ов. С PostgreSQL 15+ это поведение можно изменить через UNIQUE NULLS NOT DISTINCT - тогда NULL считается равным самому себе и в столбце будет максимум один NULL. SQL Server: НАОБОРОТ - стандартный UNIQUE constraint допускает только ОДИН NULL, попытка вставить второй даёт ошибку. Обходной путь в MS SQL - filtered index: CREATE UNIQUE INDEX ... ON t(col) WHERE col IS NOT NULL - получаете "PostgreSQL-like" поведение. Oracle: множество NULL разрешено, если UNIQUE на одной колонке; для составных UNIQUE правила хитрее. Практический совет: если бизнес-смысл "уникально или отсутствует", в Postgres можно UNIQUE INDEX ... WHERE col IS NOT NULL для явности.',
                'code_example' => <<<'SQL'
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE,
    phone VARCHAR(20) UNIQUE
);

-- Составной уникальный constraint
ALTER TABLE memberships
ADD CONSTRAINT uniq_user_team UNIQUE (user_id, team_id);
SQL,
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.basic_concepts',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое триггер в SQL и когда его уместно использовать?',
                'answer' => 'Триггер — это процедурный код, привязанный к таблице, который сервер автоматически выполняет на BEFORE/AFTER INSERT, UPDATE или DELETE. Его применяют для аудит-логов, синхронизации денормализованных счётчиков, проверки сложных инвариантов, которые нельзя выразить через CHECK. Минусы: логика прячется от приложения и тестов, отладка трудная, миграция между СУБД болезненна, а тяжёлый триггер замедляет каждую запись. Поэтому современный подход — переносить такую логику в код приложения и оставлять триггеры только для критичных консистентностей или legacy.',
                'difficulty' => 3,
                'topic' => 'database.basic_concepts',
            ],
        ];
    }
}
