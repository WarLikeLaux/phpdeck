<?php

namespace Database\Seeders\Data\Categories\Yii2;

class QueryBuilder
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Yii2',
                'question' => 'Что такое yii\\db\\Query и yii\\db\\Command в Yii2 и чем они отличаются?',
                'answer' => 'Это **два слоя** работы с БД в Yii2.

- **`yii\\db\\Query`** — **построитель запросов** (Query Builder). Декларативно собирает SQL через цепочку методов (`select`, `from`, `where`, ...). Возвращает массивы, не привязан к моделям.
- **`yii\\db\\Command`** — **выполнитель SQL**. Получается через `Yii::$app->db->createCommand($sql)`. Имеет методы `execute()`, `queryAll()`, `queryOne()`.
- Когда вызывается терминальный метод на `Query` (`all()`, `one()`, `count()`), Yii внутри строит `Command` и запускает его.
- `Query` универсальнее и безопаснее (bind-параметры), `Command` нужен когда хочется писать **сырой SQL**.',
                'code_example' => 'use yii\\db\\Query;

// Query Builder
$rows = (new Query())
    ->select([\'id\', \'email\'])
    ->from(\'{{%user}}\')
    ->where([\'status\' => 1])
    ->all();

// Сырой SQL через Command
$rows = Yii::$app->db->createCommand(
    \'SELECT id, email FROM {{%user}} WHERE status = :s\',
    [\':s\' => 1]
)->queryAll();',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.query_builder',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое Yii::$app->db и как обратиться к подключению БД в Yii2?',
                'answer' => '**`Yii::$app->db`** — это компонент-подключение к БД, экземпляр `yii\\db\\Connection`.

- Настраивается в `config/db.php` как массив с `dsn`, `username`, `password`, `charset`.
- Доступен в любом месте приложения через `Yii::$app->db`.
- На нём вызывают `createCommand()`, `transaction()`, `getSchema()`, `quoteValue()`.
- Можно завести **несколько** соединений с разными ID (`db`, `dbLog`, `dbReplica`) и переключаться по ID компонента.',
                'code_example' => '// config/db.php
return [
    \'class\' => \'yii\\\\db\\\\Connection\',
    \'dsn\' => \'mysql:host=localhost;dbname=app;charset=utf8mb4\',
    \'username\' => \'app\',
    \'password\' => \'secret\',
    \'charset\' => \'utf8mb4\',
];

// Где угодно в приложении
$db = Yii::$app->db;
$count = $db->createCommand(\'SELECT COUNT(*) FROM {{%user}}\')->queryScalar();

// Дополнительное соединение
$slave = Yii::$app->dbReplica;',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.query_builder',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как Yii2 защищается от SQL-инъекций: prepared statements и bind-параметры?',
                'answer' => 'Yii2 **всегда** использует prepared statements под капотом — параметры биндятся отдельно от SQL.

- В `Query`/`ActiveQuery` параметры в `where([\'name\' => $input])` биндятся **автоматически**.
- В сыром SQL через `Command` нужно использовать **именованные плейсхолдеры**: `:name`, `:id`.
- **Никогда** не подставляйте переменные через конкатенацию в SQL — это путь к инъекции.
- Если нужно вставить **имя таблицы/колонки** (нельзя биндить как параметр), используйте `quoteTableName()` / `quoteColumnName()` или синтаксис `{{%table}}` / `[[column]]`.',
                'code_example' => '// ПРАВИЛЬНО — bind
$user = Yii::$app->db->createCommand(
    \'SELECT * FROM {{%user}} WHERE email = :email\',
    [\':email\' => $input]
)->queryOne();

// Через Query Builder bind делается сам
$rows = (new \\yii\\db\\Query())
    ->from(\'{{%user}}\')
    ->where([\'email\' => $input])
    ->all();

// НЕПРАВИЛЬНО — НИКОГДА ТАК
// $sql = "SELECT * FROM user WHERE email = \'$input\'"; // SQL injection!',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.query_builder',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Чем различаются execute(), queryAll(), queryOne(), queryScalar(), queryColumn() у Command в Yii2?',
                'answer' => 'Все эти методы вызываются на `Command` — но возвращают **разный формат** результата.

| Метод | Что возвращает | Когда использовать |
|---|---|---|
| `execute()` | `int` — число затронутых строк | `INSERT`, `UPDATE`, `DELETE`, DDL |
| `queryAll()` | `array` массивов-строк | SELECT с несколькими строками |
| `queryOne()` | `array|false` — одна строка | SELECT одной записи |
| `queryScalar()` | значение **первой колонки первой строки** | `COUNT(*)`, `MAX(...)`, `SUM(...)` |
| `queryColumn()` | массив значений **первой колонки** | список ID, список email |

- Все они уже работают с bind-параметрами.
- Для DML (`INSERT/UPDATE/DELETE`) используется именно `execute()` — остальные ждут result set.',
                'code_example' => '$db = Yii::$app->db;

// SELECT множества
$users = $db->createCommand(\'SELECT * FROM {{%user}}\')->queryAll();

// SELECT одной записи
$u = $db->createCommand(\'SELECT * FROM {{%user}} WHERE id=:id\', [\':id\' => 1])->queryOne();

// COUNT
$cnt = $db->createCommand(\'SELECT COUNT(*) FROM {{%user}}\')->queryScalar();

// Список email
$emails = $db->createCommand(\'SELECT email FROM {{%user}}\')->queryColumn();

// UPDATE
$affected = $db->createCommand()->update(\'{{%user}}\', [\'status\' => 0], [\'id\' => 1])->execute();',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.query_builder',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Какие основные методы есть у yii\\db\\Query для сборки запроса?',
                'answer' => 'Класс `Query` поддерживает полный набор операций SQL.

- **`select(\'*\' | [...])`** — список колонок (или массив с алиасами: `[\'cnt\' => \'COUNT(*)\']`).
- **`from(\'{{%table}}\')`** — таблица. Можно несколько (через массив или join).
- **`where()`**, **`andWhere()`**, **`orWhere()`** — условия.
- **`orderBy([\'col\' => SORT_ASC])`**, **`groupBy([\'col\'])`**, **`having([...])`** — сортировка, группировка.
- **`limit($n)`**, **`offset($n)`** — пагинация.
- **`indexBy(\'id\')`** — массив результатов индексируется по полю.
- **`distinct()`** — `SELECT DISTINCT`.
- Терминалы: `all()`, `one()`, `count()`, `sum()`, `exists()`, `scalar()`, `column()`.',
                'code_example' => 'use yii\\db\\Query;

$rows = (new Query())
    ->select([\'u.id\', \'u.email\', \'cnt\' => \'COUNT(p.id)\'])
    ->from([\'u\' => \'{{%user}}\'])
    ->leftJoin([\'p\' => \'{{%post}}\'], \'p.author_id = u.id\')
    ->where([\'u.status\' => 1])
    ->andWhere([\'>\', \'u.created_at\', time() - 86400])
    ->groupBy(\'u.id\')
    ->having([\'>\', \'cnt\', 0])
    ->orderBy([\'cnt\' => SORT_DESC])
    ->limit(20)
    ->indexBy(\'id\')
    ->all();',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.query_builder',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Чем отличаются hash-формат и operator-формат условий where() в Yii2?',
                'answer' => 'В Yii2 `where()` поддерживает **два DSL-формата** записи условий.

| Формат | Пример | Что в SQL |
|---|---|---|
| **Hash** (массив поле=>значение) | `[\'status\' => 1, \'role\' => \'admin\']` | `status = 1 AND role = \'admin\'` |
| **Hash** (значение-массив) | `[\'id\' => [1, 2, 3]]` | `id IN (1, 2, 3)` |
| **Operator** (тег + аргументы) | `[\'>\', \'age\', 18]` | `age > 18` |
| **Operator IN** | `[\'in\', \'id\', [1, 2, 3]]` | `id IN (1, 2, 3)` |
| **Operator NOT IN** | `[\'not in\', \'id\', [1, 2, 3]]` | `id NOT IN (1, 2, 3)` |
| **Сырой SQL** | `[\'status = :s\', [\':s\' => 1]]` | как есть, с bind |

- Hash удобен для **равенств**.
- Operator — для **сравнений, LIKE, BETWEEN, IN, EXISTS**.
- Можно **смешивать** через `andWhere()`.',
                'code_example' => '// Hash
$q = User::find()->where([\'status\' => 1, \'role\' => \'admin\']);

// Hash с IN
$q = User::find()->where([\'id\' => [1, 2, 3]]);

// Operator
$q = User::find()->where([\'>\', \'age\', 18]);
$q->andWhere([\'in\', \'role\', [\'admin\', \'editor\']]);
$q->andWhere([\'not\', [\'status\' => 0]]);

// Сырой SQL (когда формат не подходит)
$q->andWhere(\'LENGTH(email) > :l\', [\':l\' => 5]);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.query_builder',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как сделать LIKE и BETWEEN в Yii2 Query Builder?',
                'answer' => 'Для `LIKE` и `BETWEEN` используется **operator-формат**.

- **`[\'like\', \'name\', \'foo\']`** → `name LIKE \'%foo%\'` (Yii **сам добавит** `%` с обеих сторон и экранирует `_`, `%`, `\\`).
- Чтобы поиск шёл **только в начале строки**, передайте `false`: `[\'like\', \'name\', \'foo%\', false]`.
- `[\'or like\', \'name\', [\'a\', \'b\']]` — `name LIKE \'%a%\' OR name LIKE \'%b%\'`.
- `[\'not like\', ...]`, `[\'or not like\', ...]` — отрицания.
- **`[\'between\', \'date\', $start, $end]`** → `date BETWEEN $start AND $end` (включительно).
- **`[\'not between\', ...]`** — отрицание.',
                'code_example' => 'use yii\\db\\Query;

// LIKE с автоматическими %
$q = User::find()->where([\'like\', \'email\', \'@example.com\']);

// Префиксный поиск — отключаем экранирование %
$q = User::find()->where([\'like\', \'name\', \'Ива%\', false]);

// LIKE по нескольким значениям
$q = User::find()->where([\'or like\', \'name\', [\'Иван\', \'Пётр\']]);

// BETWEEN
$q = (new Query())
    ->from(\'{{%order}}\')
    ->where([\'between\', \'created_at\', strtotime(\'-7 days\'), time()]);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.query_builder',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как сделать INNER JOIN и LEFT JOIN в Yii2 Query Builder?',
                'answer' => 'У `Query` есть отдельные методы: **`innerJoin($table, $on, $params = [])`** и **`leftJoin(...)`**, **`rightJoin(...)`**.

- Первый аргумент — имя таблицы или массив `[\'alias\' => \'tablename\']`.
- Второй — условие ON в виде строки или массива (тот же DSL что в `where`).
- Третий — bind-параметры, если в `$on` есть плейсхолдеры.
- В ActiveRecord для JOIN по relation удобнее `joinWith()`.
- Если хочется один общий метод — есть `join($type, $table, $on, $params)`.',
                'code_example' => 'use yii\\db\\Query;

$rows = (new Query())
    ->select([\'u.id\', \'u.email\', \'p.title\'])
    ->from([\'u\' => \'{{%user}}\'])
    ->innerJoin([\'p\' => \'{{%post}}\'], \'p.author_id = u.id\')
    ->where([\'u.status\' => 1])
    ->all();

// LEFT JOIN с массивом-условием
$rows = (new Query())
    ->select([\'u.*\', \'r.name AS role_name\'])
    ->from([\'u\' => \'{{%user}}\'])
    ->leftJoin([\'r\' => \'{{%role}}\'], [\'r.id\' => new \\yii\\db\\Expression(\'u.role_id\')])
    ->all();',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.query_builder',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое yii\\db\\Expression и когда его использовать?',
                'answer' => '**`yii\\db\\Expression`** — обёртка для **сырого SQL-выражения**, которое Yii **не должен квотить как значение**.

- Когда вы пишете `[\'created_at\' => time()]`, Yii биндит `time()` как число.
- А если хочется вставить `created_at = NOW()`, то простое присваивание `[\'created_at\' => \'NOW()\']` запишет в БД **строку** `"NOW()"`. Нужно: `[\'created_at\' => new Expression(\'NOW()\')]`.
- `Expression` принимает SQL-строку и опционально массив параметров (для безопасной подстановки переменных).
- Часто используется в `update()`, `insert()`, `select()` для функций БД (`COUNT()`, `IFNULL()`, `CURRENT_TIMESTAMP`).',
                'code_example' => 'use yii\\db\\Expression;

// UPDATE user SET login_count = login_count + 1, last_login = NOW() WHERE id = 1
Yii::$app->db->createCommand()->update(
    \'{{%user}}\',
    [
        \'login_count\' => new Expression(\'login_count + 1\'),
        \'last_login\' => new Expression(\'NOW()\'),
    ],
    [\'id\' => 1]
)->execute();

// В select
$rows = (new \\yii\\db\\Query())
    ->select([\'id\', \'now\' => new Expression(\'NOW()\')])
    ->from(\'{{%user}}\')
    ->all();',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.query_builder',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое подзапрос в where() в Yii2 (Query внутри Query)?',
                'answer' => 'В Yii2 в `where()` (и других местах) можно передать **`yii\\db\\Query`** — Yii сделает из него **подзапрос в скобках**.

- Особенно полезно с операторами `in`, `not in`, `exists`, `not exists`.
- Подзапрос можно использовать как **источник** в `from()`: `(new Query)->from([\'sub\' => $subQuery])`.
- Также можно подставить `Query` в `select()` как **скалярный подзапрос**.
- Bind-параметры подзапроса **сливаются** с основным — ничего вручную делать не нужно.',
                'code_example' => 'use yii\\db\\Query;

$sub = (new Query())
    ->select(\'author_id\')
    ->from(\'{{%post}}\')
    ->where([\'status\' => 1])
    ->groupBy(\'author_id\')
    ->having([\'>=\', \'COUNT(*)\', 5]);

// Авторы у кого >= 5 опубликованных постов
$users = User::find()
    ->where([\'in\', \'id\', $sub])
    ->all();

// Подзапрос как FROM
$rows = (new Query())
    ->select([\'cnt\' => \'COUNT(*)\'])
    ->from([\'sub\' => $sub])
    ->scalar();',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.query_builder',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Чем отличаются batch() и each() в Yii2 Query и зачем они нужны?',
                'answer' => 'При больших выборках загрузить **сразу всё в память** — путь к out-of-memory. Yii2 предлагает **итераторы**.

- **`batch($size = 100)`** — возвращает PHP-итератор, отдающий **массивы по `$size` строк** (через `LIMIT/OFFSET` или server-side cursor).
- **`each($size = 100)`** — то же самое, но отдаёт **по одной строке** (под капотом тот же batch, но разворачивает).
- В `foreach` можно работать с миллионами строк, держа в памяти только текущий чанк.
- Работает и на `Query`, и на ActiveRecord.
- **Подводный камень**: при `each()/batch()` нельзя одновременно итерироваться по двум независимым выборкам на одном соединении (Yii использует unbuffered query) — выделите отдельный `Connection`.',
                'code_example' => '// Экспорт всех пользователей в CSV — без OOM
foreach (User::find()->each(500) as $user) {
    fputcsv($fp, [$user->id, $user->email]);
}

// Пачками по 1000
foreach (User::find()->batch(1000) as $users) {
    Yii::$app->db->createCommand()->batchInsert(\'{{%archive_user}}\', ...);
}

// На Query — тоже работает
foreach ((new \\yii\\db\\Query())->from(\'{{%log}}\')->each(2000) as $row) {
    // ...
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.query_builder',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как квотировать имена таблиц/колонок и значения в Yii2?',
                'answer' => 'В Yii2 есть **специальный синтаксис** и **методы Connection** для квотирования.

- **`{{%table_name}}`** — таблица с префиксом БД (учитывает `tablePrefix` из конфига).
- **`{{table_name}}`** — без префикса, но с учётом квот.
- **`[[column_name]]`** — имя колонки, заквоченное под текущую СУБД (бэктики в MySQL, двойные кавычки в PG).
- Yii **сам** обрабатывает эти конструкции при сборке SQL.
- Программно: `Yii::$app->db->quoteTableName()`, `quoteColumnName()`, `quoteValue()`.
- `quoteValue()` — последнее средство (для значений почти всегда лучше bind-параметры).',
                'code_example' => '$sql = \'SELECT [[id]], [[email]] FROM {{%user}} WHERE [[status]] = :s\';
$rows = Yii::$app->db->createCommand($sql, [\':s\' => 1])->queryAll();

// Программно
$db = Yii::$app->db;
$quotedTable = $db->quoteTableName(\'user\'); // `user` или "user"
$quotedCol = $db->quoteColumnName(\'email\'); // `email`
$quotedVal = $db->quoteValue("O\'Brien"); // \'O\\\'Brien\'',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.query_builder',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как сделать INSERT, UPDATE, DELETE через Command в Yii2?',
                'answer' => 'У `Command` есть **fluent-методы** `insert()`, `update()`, `delete()`, `batchInsert()`. Они принимают имя таблицы и массив данных, без ручной сборки SQL.

- **`insert($table, $columns)`** — `INSERT INTO ... VALUES (...)`.
- **`update($table, $columns, $condition = \'\', $params = [])`** — `UPDATE ... SET ... WHERE ...`. Condition — тот же DSL что в `where()`.
- **`delete($table, $condition = \'\', $params = [])`** — `DELETE FROM ... WHERE ...`.
- **`upsert($table, $insertColumns, $updateColumns, $params)`** — `INSERT ... ON DUPLICATE KEY UPDATE` (или эквивалент в PG).
- Все эти методы возвращают `Command`, который надо **завершить `->execute()`**.',
                'code_example' => '$db = Yii::$app->db;

// INSERT
$db->createCommand()->insert(\'{{%user}}\', [
    \'email\' => \'a@b.c\',
    \'status\' => 1,
    \'created_at\' => time(),
])->execute();

// UPDATE
$db->createCommand()->update(
    \'{{%user}}\',
    [\'status\' => 0],
    [\'last_login\' => null]
)->execute();

// DELETE
$db->createCommand()->delete(\'{{%user}}\', [\'>\', \'created_at\', time() - 3600])->execute();

// UPSERT
$db->createCommand()->upsert(\'{{%counter}}\',
    [\'key\' => \'hits\', \'value\' => 1],
    [\'value\' => new \\yii\\db\\Expression(\'value + 1\')]
)->execute();',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.query_builder',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как сделать EXISTS и NOT EXISTS в Yii2 Query Builder?',
                'answer' => 'EXISTS оформляется как **operator-формат** условия: **`[\'exists\', $subQuery]`** и **`[\'not exists\', $subQuery]`**.

- `$subQuery` — это `yii\\db\\Query`.
- В подзапросе обычно есть **корреляция** на внешнюю таблицу: `where(\'post.author_id = user.id\')`.
- `EXISTS` часто **быстрее**, чем `JOIN ... GROUP BY` или `IN (SELECT ...)` для проверки «есть ли хоть одна связанная запись».
- Можно комбинировать с `andWhere`, `orWhere` как обычное условие.',
                'code_example' => 'use yii\\db\\Query;

$hasPosts = (new Query())
    ->from(\'{{%post}}\')
    ->where(\'post.author_id = user.id\')
    ->andWhere([\'status\' => 1]);

// Пользователи с хотя бы одним опубликованным постом
$users = User::find()->where([\'exists\', $hasPosts])->all();

// Пользователи без единого поста
$users = User::find()->where([\'not exists\', $hasPosts])->all();',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.query_builder',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что делает gii migrate-schema / schema cache в Yii2 и как его очистить?',
                'answer' => 'Yii2 при работе с `ActiveRecord` запрашивает **метаданные таблицы** (колонки, типы, PK). По умолчанию это происходит **на каждый запрос**.

- В конфиге `db` включают **`enableSchemaCache => true`** + `schemaCache => \'cache\'` + `schemaCacheDuration => 3600` — Yii кэширует метаданные.
- На **prod** включать обязательно (особенно с PostgreSQL — там `information_schema` тяжёлый).
- На **dev** часто выключают, чтобы не сбрасывать вручную после миграций.
- Команда консоли **`./yii cache/flush-schema db`** — сброс схемного кэша после миграций.
- **Gii** (`./yii gii/model`) генерирует модели, читая ту же схему — там тоже свежий снимок важен.',
                'code_example' => '// config/db.php
return [
    \'class\' => \'yii\\\\db\\\\Connection\',
    \'dsn\' => \'mysql:host=db;dbname=app\',
    \'enableSchemaCache\' => YII_ENV_PROD,
    \'schemaCache\' => \'cache\',
    \'schemaCacheDuration\' => 3600,
    \'schemaCacheExclude\' => [\'tmp_log\'],
];

// После миграций
// ./yii migrate
// ./yii cache/flush-schema db

// Gii — генерация моделей по схеме
// ./yii gii/model --tableName=user --modelClass=User',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.query_builder',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как итерироваться по миллионной таблице в Yii2: batch vs each, ловушка OFFSET, keyset pagination и unbuffered queries?',
                'answer' => '**Проблема:** `User::find()->all()` на таблице 10M строк = **out-of-memory** + минуты ожидания. Yii2 предлагает `batch()` / `each()`, но у них есть **подводные камни**.

**Что под капотом `batch()` / `each()`:**

- Оба итератора работают через **server-side cursor** (`PDO::MYSQL_ATTR_USE_BUFFERED_QUERY = false`) **если** есть один SELECT на одном соединении.
- В Yii 2.0.x **до 2.0.20**: batch использовал `LIMIT/OFFSET` — каждый чанк = новый `OFFSET 1000`, `OFFSET 2000`, ...
- В современных версиях: **unbuffered query** — драйвер тянет строки с сервера **по мере чтения**.
- `each($size)` под капотом вызывает `batch($size)` и просто разворачивает пачки в одиночные строки.

**Почему `OFFSET 1000000` плохо:**

| OFFSET | Что делает MySQL |
|---|---|
| `LIMIT 1000 OFFSET 0` | читает первые 1000 — быстро |
| `LIMIT 1000 OFFSET 100000` | читает **101000** строк, выкидывает 100000 — медленно |
| `LIMIT 1000 OFFSET 1000000` | читает **миллион** + 1000, выкидывает миллион — **очень** медленно |

OFFSET — это **count-then-skip**, индекс не помогает. Время растёт **линейно с глубиной**.

**Решение — keyset (cursor) pagination через `id > $lastId`:**

- Используется **только** `WHERE id > ? ORDER BY id ASC LIMIT 1000`.
- Каждый чанк = **один range scan** по B-tree индексу = **O(log N + 1000)**.
- Не зависит от глубины — миллионная страница так же быстра, как первая.

**Что ломается при долгой итерации:**

| Проблема | Причина | Решение |
|---|---|---|
| **`MySQL server has gone away`** | `wait_timeout` истёк, обработка чанка медленная | `pingMaster=true` или мини-чанки + `Yii::$app->db->open()` |
| **Connection lifetime** | unbuffered query занимает **всё** соединение | для INSERT/UPDATE внутри цикла — **отдельный `Connection`** |
| **Replication lag** | долго пишем на master, slave отстаёт | использовать **read replica** для итерации |
| **Memory** в PHP-процессе | хранится state модели + relations | `asArray()` + `unset($model)` в цикле |
| **`Commands out of sync`** | пытаешься выполнить **второй** запрос на том же соединении пока идёт unbuffered | завести `db2 = clone Yii::$app->db; $db2->open();` |
| **Transaction holding locks** | вся итерация в одной транзакции = блокировки на час | НЕ оборачивать в транзакцию, либо commit каждые N чанков |',
                'code_example' => '// 1. ПЛОХО: OFFSET на большой таблице
foreach (User::find()->batch(1000) as $users) {  // под капотом OFFSET если старый Yii
    // Чанк #1000 = OFFSET 1000000 — ужасно
}

// 2. ХОРОШО: keyset (cursor) pagination — самостоятельная реализация
$lastId = 0;
while (true) {
    $users = User::find()
        ->where([\'>\', \'id\', $lastId])
        ->orderBy([\'id\' => SORT_ASC])
        ->limit(1000)
        ->asArray()                // экономим память — не строим объекты
        ->all();

    if (empty($users)) break;

    foreach ($users as $u) {
        processUser($u);
    }
    $lastId = end($users)[\'id\'];
    // Можно коммитить чекпойнт в Redis для resume после падения
}

// 3. each() — но НЕ запускать второй запрос на том же $db
foreach (User::find()->each(500) as $user) {
    // ОШИБКА: Yii::$app->db занят unbuffered query
    // Order::find()->where([\'user_id\' => $user->id])->one();   // Commands out of sync

    // Решение: отдельное соединение для побочных запросов
    $orderCount = Yii::$app->dbWriter
        ->createCommand(\'SELECT COUNT(*) FROM orders WHERE user_id=:u\', [\':u\' => $user->id])
        ->queryScalar();
}

// 4. config/db.php — два соединения: для итерации и для записи
return [
    \'components\' => [
        \'db\' => [    // unbuffered, для each/batch
            \'class\' => \'yii\\db\\Connection\',
            \'dsn\' => \'mysql:host=replica;dbname=app\',
            \'attributes\' => [PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false],
        ],
        \'dbWriter\' => [   // обычное, для UPDATE/INSERT внутри цикла
            \'class\' => \'yii\\db\\Connection\',
            \'dsn\' => \'mysql:host=master;dbname=app\',
        ],
    ],
];

// 5. Защита от MySQL gone away на длинной итерации
register_shutdown_function(function () {
    if ($e = error_get_last()) {
        Yii::error("Iteration died at lastId=" . file_get_contents(\'/tmp/checkpoint\'));
    }
});',
                'code_language' => 'php',
                'difficulty' => 5,
                'topic' => 'yii2.query_builder',
            ],
        ];
    }
}
