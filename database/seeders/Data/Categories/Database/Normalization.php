<?php

namespace Database\Seeders\Data\Categories\Database;

class Normalization
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Базы данных',
                'question' => 'Что такое нормализация баз данных?',
                'answer' => 'Нормализация — это процесс разделения данных на таблицы так, чтобы каждый факт хранился ровно в одном месте. Цель — устранить избыточность и три типа аномалий: вставки (нельзя добавить факт без других данных), обновления (надо синхронно править все копии) и удаления (удаление строки случайно теряет другую информацию). Идёт по нормальным формам: 1НФ → 2НФ → 3НФ → БКНФ → 4НФ → 5НФ; на практике почти всегда достаточно 3НФ. Иногда сознательно идут в обратную сторону — денормализация ради скорости чтения (счётчики likes_count, агрегаты в отчётной таблице).',
                'code_example' => '-- Денормализованно: данные пользователя повторяются в каждом заказе
CREATE TABLE orders_bad (
    id BIGINT PRIMARY KEY,
    user_email VARCHAR(255),  -- повторяется
    user_name  VARCHAR(255),  -- повторяется
    total DECIMAL(10,2)
);

-- Нормализованно: пользователь — в своей таблице, в orders только FK
CREATE TABLE users  (id BIGINT PRIMARY KEY, email VARCHAR(255), name VARCHAR(255));
CREATE TABLE orders (
    id BIGINT PRIMARY KEY,
    user_id BIGINT REFERENCES users(id),
    total DECIMAL(10,2)
);',
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.normalization',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое 1НФ (первая нормальная форма)?',
                'answer' => 'По классическому определению Кодда 1НФ требует: все значения атомарны (нельзя хранить список значений в одной ячейке) и нет повторяющихся групп столбцов (phone1/phone2/phone3...). Пример нарушения: столбец phones со значением "+7-111, +7-222" - не атомарно. Решение - вынести в отдельную таблицу phones. Уточнение: "у каждой строки есть уникальный идентификатор" часто добавляют к 1НФ, но это требование реляционной модели в целом (proper relation = set, без дублей), а не строго 1НФ; PRIMARY KEY/uniqueness относятся к проектированию таблицы, а не к 1НФ как таковой.',
                'code_example' => '-- Нарушение 1НФ
CREATE TABLE users_bad (
    id INT,
    name VARCHAR(100),
    phones VARCHAR(255)  -- "+7-111, +7-222"
);

-- 1НФ
CREATE TABLE users (id INT PRIMARY KEY, name VARCHAR(100));
CREATE TABLE phones (
    id INT PRIMARY KEY,
    user_id INT REFERENCES users(id),
    phone VARCHAR(20)
);',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.normalization',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое 2НФ?',
                'answer' => '2НФ: таблица уже в 1НФ, и все неключевые атрибуты зависят от ВСЕГО первичного ключа, а не от его части. Это правило актуально только при СОСТАВНОМ первичном ключе — если PK состоит из одной колонки, 2НФ выполняется автоматически. Классический пример нарушения: таблица order_items с PK(order_id, product_id) и колонкой product_name — последняя зависит только от product_id, не от пары. Исправление: вынести product_name в отдельную таблицу products. Симптом проблемы: при изменении названия товара придётся обновлять все order_items с ним; новый товар нельзя добавить без заказа.',
                'code_example' => '-- Нарушение 2НФ: product_name зависит только от части ключа
CREATE TABLE order_items_bad (
    order_id   BIGINT,
    product_id BIGINT,
    product_name VARCHAR(255), -- зависит от product_id, а не от (order_id, product_id)
    qty INT,
    PRIMARY KEY (order_id, product_id)
);

-- 2НФ: вынесли products в свою таблицу
CREATE TABLE products (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);
CREATE TABLE order_items (
    order_id   BIGINT REFERENCES orders(id),
    product_id BIGINT REFERENCES products(id),
    qty INT NOT NULL,
    PRIMARY KEY (order_id, product_id)
);',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.normalization',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое 3НФ?',
                'answer' => '3НФ: таблица в 2НФ, и нет транзитивных зависимостей (неключевой атрибут не зависит от другого неключевого). Пример нарушения: users(id, city_id, city_name) - city_name зависит от city_id, не от id. Решение: вынести города в отдельную таблицу.',
                'code_example' => '-- Нарушение 3НФ
CREATE TABLE users_bad (
    id INT PRIMARY KEY,
    name VARCHAR(100),
    city_id INT,
    city_name VARCHAR(100)  -- зависит от city_id
);

-- 3НФ
CREATE TABLE cities (id INT PRIMARY KEY, name VARCHAR(100));
CREATE TABLE users (
    id INT PRIMARY KEY,
    name VARCHAR(100),
    city_id INT REFERENCES cities(id)
);',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.normalization',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое БКНФ (Бойса-Кодда)?',
                'answer' => 'БКНФ - усиление 3НФ: каждая нетривиальная функциональная зависимость должна иметь в левой части суперключ. Простыми словами: если X определяет Y, то X должен быть уникальным в таблице. БКНФ часто совпадает с 3НФ, но решает редкие случаи, когда есть несколько перекрывающихся ключей.',
                'difficulty' => 4,
                'topic' => 'database.normalization',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое денормализация и когда она уместна?',
                'answer' => 'Денормализация - намеренное добавление избыточности (хранение одних и тех же данных в нескольких местах) ради производительности. Уместна, когда: read-heavy нагрузка, JOIN-ы стали узким местом, нужна аналитика, есть отдельная OLAP-БД. Минусы: данные могут рассинхронизироваться, сложнее обновлять. Примеры: счётчики (likes_count в постах), хранение полного имени вместо JOIN.',
                'code_example' => '-- Денормализованный счётчик
CREATE TABLE posts (
    id BIGINT PRIMARY KEY,
    title VARCHAR(255),
    likes_count INT DEFAULT 0,  -- избыточность для скорости
    comments_count INT DEFAULT 0
);',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.normalization',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Какие есть виды связей между таблицами?',
                'answer' => '1-к-1 (один-к-одному): пользователь и его профиль (один на один). Реализуется через UNIQUE FK. 1-ко-многим: пользователь и его заказы. FK на стороне "многих". М-ко-многим: студент и курсы. Реализуется через промежуточную (pivot) таблицу с двумя FK.',
                'code_example' => '-- 1-к-1
CREATE TABLE users (id BIGINT PRIMARY KEY);
CREATE TABLE profiles (
    id BIGINT PRIMARY KEY,
    user_id BIGINT UNIQUE REFERENCES users(id)
);

-- 1-ко-многим
CREATE TABLE orders (
    id BIGINT PRIMARY KEY,
    user_id BIGINT REFERENCES users(id)
);

-- M-ко-многим через pivot
CREATE TABLE students (id BIGINT PRIMARY KEY);
CREATE TABLE courses (id BIGINT PRIMARY KEY);
CREATE TABLE student_course (
    student_id BIGINT REFERENCES students(id),
    course_id BIGINT REFERENCES courses(id),
    PRIMARY KEY (student_id, course_id)
);',
                'code_language' => 'sql',
                'difficulty' => 2,
                'topic' => 'database.normalization',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое ER-диаграмма?',
                'answer' => 'ER-диаграмма (Entity-Relationship) - визуальная модель данных, показывающая сущности (таблицы), их атрибуты (столбцы) и связи между ними. Бывают разные нотации: Чена, Crow Foot (вороньи лапки) - наиболее популярная. На лапке: одиночная палочка - "один", вилка - "много", кружок - "ноль". Используется на этапе проектирования БД до написания SQL.',
                'difficulty' => 2,
                'topic' => 'database.normalization',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое аномалии при отсутствии нормализации?',
                'answer' => 'В ненормализованной таблице один факт хранится в нескольких строках, что порождает три типа аномалий. (1) Аномалия вставки: нельзя добавить факт, потому что не хватает других данных — например, нельзя занести новую категорию, пока в ней не появился хотя бы один товар. (2) Аномалия обновления: при изменении факта надо обновить ВСЕ его повторения, иначе данные рассинхронизируются — поменяли название города у одного сотрудника, в строках других сотрудников осталось старое название. (3) Аномалия удаления: удаление одной строки случайно стирает другую важную информацию — уволили последнего сотрудника отдела, в этот момент потерялся сам факт существования отдела. Нормализация (выделение справочников, нормальные формы) устраняет все три типа, потому что каждый факт начинает храниться ровно в одном месте.',
                'code_example' => '-- Денормализованная таблица: дублируется departments.location
CREATE TABLE employees_bad (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    dept_name VARCHAR(255),
    dept_location VARCHAR(255)
);

-- (1) Вставка: добавить отдел без сотрудника нельзя
-- (2) Обновление: переехал отдел - надо обновить N строк
UPDATE employees_bad SET dept_location = \'Saint-Petersburg\' WHERE dept_name = \'IT\';
-- забыли пару строк → данные разъехались
-- (3) Удаление: удалили последнего сотрудника IT - потеряли сам отдел

-- Нормализованно: факты хранятся ровно в одном месте
CREATE TABLE departments (id BIGINT PRIMARY KEY, name VARCHAR(255), location VARCHAR(255));
CREATE TABLE employees   (id BIGINT PRIMARY KEY, name VARCHAR(255), dept_id BIGINT REFERENCES departments(id));',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.normalization',
            ],
        ];
    }
}
