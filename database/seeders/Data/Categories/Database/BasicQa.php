<?php

namespace Database\Seeders\Data\Categories\Database;

class BasicQa
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Базы данных',
                'question' => 'Что такое индекс и когда он не помогает?',
                'answer' => 'Структура для ускорения поиска по столбцам (B-tree, hash, GIN/GiST). Не помогает: на маленьких таблицах (быстрее seq scan), при низкой селективности (большая часть строк подходит), при функциях/преобразованиях столбца без expression-индекса, при LIKE с ведущим % (%x%) - нет prefix; для второго столбца составного индекса без leftmost prefix.',
                'difficulty' => 3,
                'topic' => 'database.basic_qa',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Чем отличается INNER JOIN от LEFT JOIN?',
                'answer' => 'INNER возвращает только пары, удовлетворяющие условию. LEFT возвращает все строки слева плюс совпадения справа, отсутствующие справа заполняются NULL.',
                'difficulty' => 2,
                'topic' => 'database.basic_qa',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое нормальные формы и зачем нормализация?',
                'answer' => 'Правила декомпозиции таблиц для устранения избыточности и аномалий. 1НФ - атомарность, 2НФ - зависимость от полного ключа, 3НФ - отсутствие транзитивных зависимостей.',
                'difficulty' => 3,
                'topic' => 'database.basic_qa',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Как подключиться к MySQL из PHP и в чём разница между mysqli и PDO?',
                'answer' => 'Подключение делают расширением mysqli или классом PDO, передавая DSN-строку, имя пользователя и пароль. mysqli работает только с MySQL и имеет процедурный и объектный API, PDO абстрактен — поддерживает PostgreSQL, SQLite, SQL Server и другие СУБД, что упрощает смену драйвера. У PDO есть именованные плейсхолдеры (:id) в подготовленных запросах, у mysqli — только позиционные знаки вопроса. Для нового кода почти всегда выбирают PDO ради переносимости и удобного API.',
                'difficulty' => 2,
                'topic' => 'database.basic_qa',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Зачем нужен первичный ключ простыми словами?',
                'answer' => 'Чтобы каждую строку можно было однозначно отличить от других. Без PK ты не можешь сказать «обнови вот ЭТУ строку» — БД не поймёт какую. Обычно это id с автоинкрементом или UUID. Не может быть NULL и не может повторяться.',
                'difficulty' => 1,
                'topic' => 'database.basic_qa',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Зачем нужен внешний ключ простыми словами?',
                'answer' => 'Чтобы связывать таблицы и не давать создавать «висячие» ссылки. orders.user_id ссылается на users.id — БД не даст вставить заказ для несуществующего юзера и не даст удалить юзера, у которого есть заказы (либо удалит каскадно, по настроенному правилу).',
                'difficulty' => 1,
                'topic' => 'database.basic_qa',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое SQL?',
                'answer' => 'Structured Query Language — язык запросов к реляционным БД. На нём пишут запросы для чтения (SELECT), вставки (INSERT), изменения (UPDATE), удаления (DELETE) и для управления схемой (CREATE, ALTER, DROP).',
                'difficulty' => 1,
                'topic' => 'database.basic_qa',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что делает SELECT и какой минимальный синтаксис?',
                'answer' => 'SELECT читает данные. Минимум: SELECT колонки FROM таблица WHERE условие. Пример: SELECT name, email FROM users WHERE id = 5. SELECT * — все колонки.',
                'code_example' => '-- все колонки всех юзеров
SELECT * FROM users;

-- только нужные колонки с фильтром
SELECT name, email FROM users WHERE age >= 18;',
                'code_language' => 'sql',
                'difficulty' => 1,
                'topic' => 'database.basic_qa',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что делает INSERT?',
                'answer' => 'Добавляет новую строку в таблицу. Синтаксис: INSERT INTO users (name, email) VALUES ("Иван", "i@x.ru"). Можно вставить сразу несколько: VALUES (...), (...), (...).',
                'difficulty' => 1,
                'topic' => 'database.basic_qa',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что делает UPDATE и зачем там почти всегда нужен WHERE?',
                'answer' => 'UPDATE меняет существующие строки: UPDATE users SET email = "new@x.ru" WHERE id = 5. БЕЗ WHERE обновит ВСЕ строки таблицы — типичная катастрофа. Всегда проверяй наличие WHERE перед запуском.',
                'difficulty' => 1,
                'topic' => 'database.basic_qa',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что делает DELETE и в чём опасность?',
                'answer' => 'Удаляет строки: DELETE FROM users WHERE id = 5. Без WHERE удалит ВСЕ строки. TRUNCATE TABLE users — быстро очищает всю таблицу (без WHERE, без триггеров, иногда нельзя откатить).',
                'difficulty' => 1,
                'topic' => 'database.basic_qa',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что делает ORDER BY?',
                'answer' => 'Сортирует результат запроса. ORDER BY age — по возрастанию. ORDER BY age DESC — по убыванию. Можно по нескольким: ORDER BY country, age DESC — сначала по стране, внутри страны по возрасту.',
                'difficulty' => 1,
                'topic' => 'database.basic_qa',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое JOIN простыми словами?',
                'answer' => 'JOIN — операция, соединяющая строки из двух таблиц по условию. Например, к users присоединить orders по users.id = orders.user_id, чтобы получить «пользователь + его заказы» в одной таблице результата.',
                'difficulty' => 2,
                'topic' => 'database.basic_qa',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Зачем нужны COUNT, SUM, AVG, MIN, MAX?',
                'answer' => 'Это агрегатные функции, считающие что-то по группе строк. COUNT(*) — сколько строк. SUM(amount) — сумма. AVG(age) — среднее. MIN/MAX — наименьшее/наибольшее. Работают с GROUP BY (или без — сворачивают всё в одно число).',
                'difficulty' => 1,
                'topic' => 'database.basic_qa',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое NULL в SQL?',
                'answer' => 'Специальное значение «нет данных» — не пустая строка и не ноль. Сравнения через = NULL не работают (NULL = NULL → NULL, не true). Проверяй через IS NULL и IS NOT NULL.',
                'difficulty' => 2,
                'topic' => 'database.basic_qa',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Чем отличается DELETE от TRUNCATE?',
                'answer' => 'DELETE удаляет строки построчно (можно WHERE, можно откатить в транзакции, срабатывают триггеры). TRUNCATE TABLE моментально очищает таблицу целиком (без WHERE, обычно без триггеров, сбрасывает auto_increment, не всегда откатывается). Для очистки больших таблиц TRUNCATE на порядки быстрее.',
                'difficulty' => 2,
                'topic' => 'database.basic_qa',
            ],
        ];
    }
}
