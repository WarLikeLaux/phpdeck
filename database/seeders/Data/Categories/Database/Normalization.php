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
                'answer' => 'Нормализация - это процесс структурирования таблиц, чтобы устранить избыточность и аномалии (вставки, обновления, удаления). Идёт по нормальным формам: 1НФ, 2НФ, 3НФ, БКНФ, 4НФ, 5НФ. На практике обычно достаточно 3НФ. Цель - каждый факт хранится в одном месте.',
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
                'answer' => '2НФ: таблица в 1НФ, и все неключевые атрибуты зависят от ВСЕГО первичного ключа, а не от его части. Актуально для составных ключей. Пример: в таблице (order_id, product_id, product_name) - product_name зависит только от product_id, нарушение 2НФ. Надо вынести в таблицу products.',
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
                'answer' => 'В ненормализованной таблице один факт хранится в нескольких строках, что порождает три типа аномалий. Аномалия вставки: нельзя добавить факт, потому что не хватает других данных (например, нельзя добавить категорию, пока нет товара в этой категории). Аномалия обновления: при изменении факта надо обновить все его повторения, иначе данные рассогласуются (поменяли название города в одной строке — в других осталось старое). Аномалия удаления: удаление одной строки случайно стирает другую информацию (удалили последнего сотрудника отдела — потеряли сам отдел). Нормализация устраняет все три типа.',
                'difficulty' => 3,
                'topic' => 'database.normalization',
            ],
        ];
    }
}
