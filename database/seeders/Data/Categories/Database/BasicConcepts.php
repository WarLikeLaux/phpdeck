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
                'answer' => '**База данных (БД)** — это организованное **хранилище данных**, из которого удобно **искать, добавлять, изменять и удалять** записи.

**Аналогия:** огромный шкаф с папками, где всё разложено по полочкам по правилам, и есть быстрый способ найти нужное.

Для работы с БД используют **СУБД** (Систему Управления Базами Данных) — это программа, которая «управляет шкафом». Примеры:
- **PostgreSQL**, **MySQL**, **SQLite** — реляционные;
- **MongoDB** — документная;
- **Redis** — ключ-значение в памяти.

В приложении мы шлём СУБД **запросы** (например, на `SQL`), а она возвращает данные.',
                'code_example' => '-- Простой пример: создаём БД, таблицу, добавляем и читаем запись
CREATE DATABASE shop;

CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

INSERT INTO users (name) VALUES (\'Иван\');
SELECT * FROM users;
-- id | name
--  1 | Иван',
                'code_language' => 'sql',
                'difficulty' => 1,
                'topic' => 'database.basic_concepts',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое таблица, строка и столбец?',
                'answer' => '- **Таблица** — набор данных в виде **сетки**, как Excel-лист (например, `users`).
- **Строка** (row, record, запись) — **одна конкретная сущность**: один пользователь со своим `id`, именем и email.
- **Столбец** (column, поле, атрибут) — **одна характеристика** всех записей: «имя», «email», «дата регистрации».

У каждого столбца есть **тип данных** (`INT`, `VARCHAR`, `TIMESTAMP`) и можно задать **правила**:
- `NOT NULL` — обязательное;
- `UNIQUE` — без повторений;
- `DEFAULT` — значение по умолчанию;
- `PRIMARY KEY` / `FOREIGN KEY` — ключи.',
                'code_example' => 'CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,            -- столбец id (большое целое)
    name VARCHAR(255) NOT NULL,           -- столбец name (строка, обязательный)
    email VARCHAR(255) UNIQUE NOT NULL,   -- столбец email (уникальный)
    created_at TIMESTAMP DEFAULT NOW()    -- столбец с дефолтом
);

-- Каждая INSERT-команда добавляет ОДНУ строку
INSERT INTO users (name, email) VALUES (\'Иван\', \'ivan@example.com\');
INSERT INTO users (name, email) VALUES (\'Анна\', \'anna@example.com\');',
                'code_language' => 'sql',
                'difficulty' => 1,
                'topic' => 'database.basic_concepts',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое реляционная база данных?',
                'answer' => '**Реляционная БД** хранит данные в **таблицах со строгой схемой**: типы колонок и их обязательность заданы заранее (если колонка `email` — `VARCHAR NOT NULL`, то записать туда число или `NULL` не получится).

Таблицы **связываются через ключи**:
- `PRIMARY KEY` — уникально идентифицирует строку;
- `FOREIGN KEY` — ссылается на `PK` другой таблицы (`orders.user_id` → `users.id`).

Что даёт:
- работа через язык **SQL**;
- поддержка **транзакций** (ACID);
- БД сама **не даёт создать «висячие» ссылки**.

**Примеры:** PostgreSQL, MySQL, Oracle, SQL Server, SQLite.

**Альтернатива** — **NoSQL** (MongoDB, Redis), где жёсткой схемы и SQL обычно нет.',
                'code_example' => '-- Две связанные таблицы
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

CREATE TABLE orders (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id),   -- FK на users
    total DECIMAL(10, 2)
);

-- Заказ может существовать только у реального юзера
INSERT INTO users (name) VALUES (\'Иван\');             -- id = 1
INSERT INTO orders (user_id, total) VALUES (1, 500);   -- OK
INSERT INTO orders (user_id, total) VALUES (999, 500); -- ERROR: нет user 999',
                'code_language' => 'sql',
                'difficulty' => 1,
                'topic' => 'database.basic_concepts',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое первичный ключ (PRIMARY KEY)?',
                'answer' => '**`PRIMARY KEY`** — это колонка (или набор колонок), которая **уникально идентифицирует** каждую строку в таблице.

**Свойства:**
- значение всегда **уникальное**;
- **не может быть `NULL`**;
- в таблице только **один** `PRIMARY KEY` (но он может быть **составным** — из нескольких колонок);
- под `PRIMARY KEY` автоматически создаётся **индекс** → поиск очень быстрый.

**Типичные варианты:**
- числовой `id` с автоинкрементом (`BIGSERIAL` в PG, `BIGINT AUTO_INCREMENT` в MySQL) — самый частый;
- `UUID` — когда `id` нужно генерировать на клиенте или нескольких узлах;
- **составной** — для pivot-таблиц связи многие-ко-многим (`order_id, product_id`).',
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
                'answer' => '**`FOREIGN KEY`** — колонка, которая **ссылается на `PRIMARY KEY`** (или `UNIQUE`) другой таблицы. Обеспечивает **ссылочную целостность** — БД сама не даст создать «висячую» ссылку.

**Что нельзя сделать с FK:**
- вставить заказ для **несуществующего** пользователя;
- удалить пользователя, если на него ссылаются заказы (по умолчанию).

**Поведение при удалении/обновлении родителя** — настраивается через `ON DELETE` и `ON UPDATE`:
- **`CASCADE`** — каскадно удалить/обновить связанные строки;
- **`SET NULL`** — обнулить ссылку (колонка должна быть `NULL`-able);
- **`SET DEFAULT`** — поставить значение по умолчанию (в MySQL InnoDB реально работает только с 8.0.16+);
- **`RESTRICT`** — запретить операцию;
- **`NO ACTION`** — то же, но проверка откладывается до конца транзакции.',
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
                'answer' => '**`UNIQUE`** гарантирует, что значения в колонке (или **комбинации** колонок) **не повторяются**. Попытка вставить дубль → ошибка.

**Отличия от `PRIMARY KEY`:**
- `UNIQUE`-колонка обычно **может содержать `NULL`**;
- таких ограничений в таблице может быть **несколько**, а `PRIMARY KEY` — только один.

**`NULL` и уникальность:**
- в **PostgreSQL** и **MySQL** `NULL` не равен `NULL` → несколько `NULL`-ов **разрешены**;
- в **SQL Server** — разрешён только **один** `NULL`.

**Типичные применения:** `email`, номер телефона, `slug`, любые бизнес-поля, у которых не должно быть дублей.

Под `UNIQUE` автоматически создаётся **индекс** — поиск по уникальной колонке быстрый.',
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
                'answer' => '**Триггер** — **процедурный код**, привязанный к таблице, который СУБД **автоматически выполняет** на:
- `BEFORE` / `AFTER` `INSERT`, `UPDATE`, `DELETE`;
- `INSTEAD OF` — для `VIEW`.

Внутри доступны псевдо-записи **`NEW`** / **`OLD`** с новыми и старыми значениями строки.

**Применение:**
- **аудит-логи** — писать историю изменений в отдельную таблицу;
- **денормализованные счётчики** (`likes_count`, `comments_count`);
- сложные **межтабличные инварианты**, которые нельзя выразить через `CHECK`;
- **search-колонки** (`tsvector` в PG).

**Минусы:**
- логика **прячется от приложения** и code review;
- отладка трудная (нет хорошего stack trace);
- **миграция между СУБД** болезненна (`PL/pgSQL` ≠ MySQL ≠ T-SQL);
- тяжёлый триггер замедляет **каждую запись** и держит транзакцию дольше.

**Современный подход:** большую часть такой логики переносят в код приложения (**Eloquent observers**, Doctrine listeners), а триггеры оставляют для:
- **критичной целостности**;
- случаев, когда таблица обновляется **не только из приложения** (внешние ETL, ручные `psql`).',
                'code_example' => '-- PostgreSQL: триггер для синхронизации счётчика
CREATE OR REPLACE FUNCTION update_post_comments_count()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
BEGIN
    IF TG_OP = \'INSERT\' THEN
        UPDATE posts SET comments_count = comments_count + 1 WHERE id = NEW.post_id;
    ELSIF TG_OP = \'DELETE\' THEN
        UPDATE posts SET comments_count = comments_count - 1 WHERE id = OLD.post_id;
    END IF;
    RETURN NULL;
END;
$$;

CREATE TRIGGER trg_comments_count
AFTER INSERT OR DELETE ON comments
FOR EACH ROW EXECUTE FUNCTION update_post_comments_count();

-- MySQL аналог
DELIMITER $$
CREATE TRIGGER trg_comments_ins AFTER INSERT ON comments
FOR EACH ROW
    UPDATE posts SET comments_count = comments_count + 1 WHERE id = NEW.post_id;
$$
DELIMITER ;',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.basic_concepts',
            ],
        ];
    }
}
