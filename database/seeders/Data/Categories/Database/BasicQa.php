<?php

namespace Database\Seeders\Data\Categories\Database;

class BasicQa
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, difficulty?: int, topic?: string}>
     */
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
        ];
    }
}
