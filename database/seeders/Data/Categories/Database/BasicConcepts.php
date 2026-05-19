<?php

namespace Database\Seeders\Data\Categories\Database;

class BasicConcepts
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Базы данных',
                'question' => 'Что такое база данных простыми словами?',
                'answer' => 'База данных (БД) - это организованное хранилище данных, к которому удобно обращаться, добавлять, изменять и удалять записи. Простыми словами: представь огромный шкаф с папками, где всё разложено по полочкам и есть быстрый способ найти нужное. СУБД (Система Управления Базами Данных) - это программа, которая управляет этим шкафом: PostgreSQL, MySQL, SQLite, Oracle и т.д.',
                'difficulty' => 1,
                'topic' => 'database.basic_concepts',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое таблица, строка и столбец?',
                'answer' => 'Таблица - это набор данных в виде сетки (как Excel-лист). Строка (row, record, кортеж) - одна запись, например один пользователь. Столбец (column, поле, атрибут) - характеристика записи, например имя или email. Каждый столбец имеет тип данных (integer, varchar, timestamp и т.д.).',
                'code_example' => 'CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);',
                'code_language' => 'sql',
                'difficulty' => 1,
                'topic' => 'database.basic_concepts',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое реляционная база данных?',
                'answer' => 'Реляционная БД хранит данные в таблицах (отношениях) со строгой схемой: типы и обязательность столбцов известны заранее. Таблицы связаны друг с другом через ключи: PRIMARY KEY уникально идентифицирует строку, FOREIGN KEY ссылается на PK другой таблицы (например, orders.user_id -> users.id). Работают через SQL, поддерживают ACID-транзакции и обеспечивают ссылочную целостность. Примеры: PostgreSQL, MySQL, Oracle, SQL Server, SQLite. Альтернатива — NoSQL (MongoDB, Redis), где жёсткой схемы и SQL обычно нет.',
                'difficulty' => 1,
                'topic' => 'database.basic_concepts',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое первичный ключ (PRIMARY KEY)?',
                'answer' => 'Первичный ключ - это столбец (или набор столбцов), который уникально идентифицирует каждую строку в таблице. Свойства: значение всегда уникальное, не может быть NULL, в таблице только один PRIMARY KEY. Обычно на первичный ключ автоматически создаётся индекс.',
                'code_example' => '-- Простой первичный ключ
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
);',
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.basic_concepts',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое внешний ключ (FOREIGN KEY)?',
                'answer' => 'Внешний ключ - это столбец, который ссылается на первичный ключ другой таблицы. Он обеспечивает ссылочную целостность: нельзя добавить заказ для несуществующего пользователя. У FK можно настроить ON DELETE и ON UPDATE: CASCADE, SET NULL, SET DEFAULT, RESTRICT, NO ACTION (SET DEFAULT в MySQL InnoDB реально работает только с 8.0.16+; до этого синтаксис парсился, но действие игнорировалось).',
                'code_example' => 'CREATE TABLE orders (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    total DECIMAL(10,2),
    FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);',
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.basic_concepts',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое UNIQUE constraint?',
                'answer' => 'UNIQUE constraint гарантирует, что значения в указанном столбце (или комбинации столбцов) не повторяются. В отличие от PRIMARY KEY, UNIQUE-колонка обычно может содержать NULL, и таких ограничений в одной таблице может быть несколько. Важный нюанс: в PostgreSQL и MySQL NULL не считается равным NULL для уникальности, поэтому несколько NULL-ов спокойно влезут; в SQL Server, наоборот, разрешён только один NULL. Часто используется на email, номере телефона, slug — для бизнес-полей, у которых нет смысла повторяться.',
                'code_example' => 'CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE,
    phone VARCHAR(20) UNIQUE
);

-- Составной уникальный constraint
ALTER TABLE memberships
ADD CONSTRAINT uniq_user_team UNIQUE (user_id, team_id);',
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
