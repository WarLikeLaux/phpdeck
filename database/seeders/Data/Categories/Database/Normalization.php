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
                'answer' => '**Нормализация** — процесс разделения данных на таблицы так, чтобы **каждый факт хранился ровно в одном месте**.

**Цель** — устранить **избыточность** и три типа **аномалий**:
- **вставки** — нельзя добавить факт без других данных;
- **обновления** — приходится синхронно править все копии (легко рассинхронизировать);
- **удаления** — удалив одну строку, случайно теряешь связанную информацию.

**Нормальные формы** идут лесенкой: **1НФ → 2НФ → 3НФ → БКНФ → 4НФ → 5НФ**. На практике почти всегда достаточно **3НФ**.

**Денормализация** — обратный приём, сознательная избыточность ради **скорости чтения**:
- счётчики (`likes_count`, `comments_count`) в самой таблице;
- агрегаты в отчётной/витринной таблице;
- кэшированные `JOIN`-результаты (`Materialized View`).',
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
                'answer' => '**1НФ** (по классическому определению **Кодда**) требует:

1. **Все значения атомарны** — нельзя хранить **список значений в одной ячейке**;
2. **Нет повторяющихся групп столбцов** — `phone1`/`phone2`/`phone3`...

**Пример нарушения:** столбец `phones` со значением `"+7-111, +7-222"` — **не атомарно**.

**Решение** — вынести в отдельную таблицу `phones` со связью **1-ко-многим** через `FOREIGN KEY`.

**Уточнение:** «у каждой строки есть уникальный идентификатор» часто добавляют к 1НФ, но это требование **реляционной модели в целом** (proper relation = set, без дублей), а не строго 1НФ — `PRIMARY KEY` / uniqueness относятся к проектированию таблицы.',
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
                'answer' => '**2НФ:** таблица уже в **1НФ**, и **все неключевые атрибуты зависят от `ВСЕГО` первичного ключа**, а не от его части.

**Когда актуально:** только при **составном `PRIMARY KEY`**. Если PK из одной колонки — **2НФ выполняется автоматически**.

**Классический пример нарушения** — таблица `order_items` с `PK(order_id, product_id)`:
- колонка `product_name` зависит **только от `product_id`**, не от пары;
- значит, нарушает 2НФ.

**Симптомы проблемы:**
- при изменении названия товара придётся **обновлять все `order_items`** с ним;
- **новый товар нельзя добавить без заказа**.

**Исправление:** вынести `product_name` в отдельную таблицу `products`.',
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
                'answer' => '**3НФ:** таблица в **2НФ**, и **нет транзитивных зависимостей** — неключевой атрибут не зависит от **другого неключевого**.

**Транзитивная зависимость:** `PK → A → B`, где `B` зависит от `A`, а не напрямую от `PK`.

**Пример нарушения:**
```
users(id, city_id, city_name)
```
- `city_name` зависит от `city_id`, не от `id`;
- получается `id → city_id → city_name` (транзитивно).

**Симптом:** переименовали город — придётся обновить **всех пользователей** этого города.

**Решение** — вынести города в **отдельную таблицу** `cities` и оставить `users.city_id` как `FK`.

**Эмпирическое правило:** «каждый неключевой атрибут зависит от **ключа, всего ключа и ничего, кроме ключа**».',
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
                'answer' => '**Денормализация** — **намеренное добавление избыточности** (хранение одних и тех же данных в нескольких местах) **ради производительности**.

**Когда уместна:**
- **read-heavy** нагрузка (много чтений, мало записей);
- `JOIN`-ы стали узким местом профайлинга;
- нужна **аналитика** / отчёты;
- есть отдельная **OLAP**-БД или матвью.

**Цена — рассинхронизация данных.** Поддерживать актуальность дубликатов придётся:
- через **триггеры** в БД;
- **транзакционные обновления** в коде приложения;
- фоновые задачи / matview refresh.

**Типовые примеры:**
- **счётчики** — `likes_count`, `comments_count` в `posts`;
- **полное имя** в денормализованной строке вместо `JOIN` с `users`;
- **снапшот цены/адреса** на момент заказа (`order_items.price`);
- агрегаты в `daily_stats`.

**Правило:** сначала **нормализуй**, потом **денормализуй точечно** там, где замерил проблему.',
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
                'answer' => '**Три классических вида:**

**1-к-1 (один-к-одному).** Одна строка слева связана максимум с одной справа. Реализуется через **`FOREIGN KEY` + `UNIQUE`** на стороне зависимой таблицы. Пример: `users` и `profiles` (один профиль на пользователя).

**1-ко-многим.** Самая частая связь. Одна строка слева связана с **несколькими** справа. **`FOREIGN KEY` на стороне «многих»**. Пример: `users` → `orders` (у пользователя много заказов, у заказа один пользователь).

**М-ко-многим.** Несколько слева связаны с несколькими справа. Реализуется через **промежуточную (pivot) таблицу** с **двумя `FOREIGN KEY`**. Пример: `students` ↔ `student_course` ↔ `courses`.

В Laravel/Eloquent им соответствуют: `hasOne`, `hasMany` / `belongsTo`, `belongsToMany`.',
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
                'answer' => '**ER-диаграмма** (**Entity-Relationship**) — визуальная **модель данных**: что показывает:
- **сущности** (таблицы) — прямоугольники;
- **атрибуты** (колонки) — внутри прямоугольников;
- **связи** между сущностями — линии с обозначением кратности.

**Популярные нотации:**
- **Crow\'s Foot** («вороньи лапки») — самая частая. На конце линии:
  - одиночная палочка — «один»;
  - **вилка** (3 расходящихся линии) — «много»;
  - кружок — «ноль» (опционально);
- **нотация Чена** — с ромбами для связей (учебная классика).

**Зачем нужна:** проектирование схемы **до** написания SQL, согласование с командой, документация. Удобные инструменты: dbdiagram.io, draw.io, DBeaver.',
                'difficulty' => 2,
                'topic' => 'database.normalization',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое аномалии при отсутствии нормализации?',
                'answer' => 'В ненормализованной таблице **один факт хранится в нескольких строках**, что порождает **три типа аномалий**:

**1. Аномалия вставки** — нельзя добавить факт, потому что **не хватает других данных**.
- Пример: нельзя занести новую категорию, пока в ней не появился хотя бы один товар.

**2. Аномалия обновления** — при изменении факта надо обновить **все его повторения**, иначе данные **рассинхронизируются**.
- Пример: переехал отдел — забыли обновить пару строк сотрудников → у части осталось старое местоположение.

**3. Аномалия удаления** — удаление одной строки **случайно стирает другую важную информацию**.
- Пример: уволили последнего сотрудника отдела → потеряли сам **факт существования отдела**.

**Решение** — **нормализация** (выделение справочников, нормальные формы). Каждый факт хранится **ровно в одном месте** — все три аномалии исчезают.',
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
