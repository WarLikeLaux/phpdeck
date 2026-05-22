<?php

namespace Database\Seeders\Data\Categories\Yii2;

class Migrations
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Yii2',
                'question' => 'Как создать и применить миграцию в Yii2 через консоль?',
                'answer' => 'В Yii2 миграции — это **PHP-классы**, наследующие `yii\\db\\Migration`. Управляются командой `./yii migrate`.

- **Создать**: `./yii migrate/create create_user_table` — создаёт файл `m{timestamp}_create_user_table.php` в `migrations/`.
- **Применить все новые**: `./yii migrate` (или `migrate/up`).
- **Применить N новых**: `./yii migrate/up 1`.
- **Откатить последнюю**: `./yii migrate/down 1` (или просто `migrate/down`).
- **Статус**: `./yii migrate/history`, `migrate/new` (что ещё не применено).
- **Сбросить состояние**: `migrate/redo` — откатить и применить заново.

Yii ведёт **таблицу `migration`** в БД, где хранит имена применённых миграций и время.',
                'code_example' => '# Создать
./yii migrate/create create_user_table

# Применить все новые
./yii migrate

# Применить ровно одну новую
./yii migrate/up 1

# Откатить одну
./yii migrate/down 1

# Что ещё не применено
./yii migrate/new

# История
./yii migrate/history',
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'yii2.migrations',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Чем отличается safeUp()/safeDown() от up()/down() в Yii2 миграции?',
                'answer' => '**`safeUp()` и `safeDown()`** оборачивают тело миграции в **транзакцию БД**.

| Метод | Транзакция | Когда использовать |
|---|---|---|
| `up()` / `down()` | Нет | DDL, который СУБД **не поддерживает** в транзакции (например, MySQL `CREATE TABLE` коммитит сам) |
| `safeUp()` / `safeDown()` | Да | Несколько операций, которые должны быть атомарны |

- В MySQL DDL **всё равно** не входит в транзакцию (implicit commit) — но `safeUp` хотя бы откатит DML.
- В PostgreSQL DDL **полностью транзакционно** — `safeUp` действительно атомарно.
- Большинство официальных шаблонов Yii используют именно `safeUp/safeDown`.',
                'code_example' => 'use yii\\db\\Migration;

class m250520_120000_create_user_table extends Migration
{
    public function safeUp()
    {
        $this->createTable(\'{{%user}}\', [
            \'id\' => $this->primaryKey(),
            \'email\' => $this->string(255)->notNull()->unique(),
            \'status\' => $this->smallInteger()->notNull()->defaultValue(1),
            \'created_at\' => $this->integer()->notNull(),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable(\'{{%user}}\');
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.migrations',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Какие методы DDL есть у yii\\db\\Migration (createTable, dropTable и т.д.)?',
                'answer' => 'Класс `Migration` инкапсулирует основные DDL-операции, **независимые от СУБД**.

- **Таблицы**: `createTable()`, `dropTable()`, `renameTable()`, `truncateTable()`.
- **Колонки**: `addColumn()`, `dropColumn()`, `renameColumn()`, `alterColumn()`.
- **Внешние ключи**: `addForeignKey()`, `dropForeignKey()`.
- **Индексы**: `createIndex()`, `dropIndex()`.
- **Первичный ключ**: `addPrimaryKey()`, `dropPrimaryKey()`.
- **Данные**: `insert()`, `batchInsert()`, `update()`, `delete()`.
- Все методы логируют в консоль и измеряют время.',
                'code_example' => 'class m250520_130000_add_role_to_user extends Migration
{
    public function safeUp()
    {
        $this->addColumn(\'{{%user}}\', \'role\', $this->string(32)->notNull()->defaultValue(\'user\'));
        $this->createIndex(\'idx-user-role\', \'{{%user}}\', \'role\');
    }

    public function safeDown()
    {
        $this->dropIndex(\'idx-user-role\', \'{{%user}}\');
        $this->dropColumn(\'{{%user}}\', \'role\');
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.migrations',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Зачем нужны типы Schema::TYPE_PK / $this->integer() в миграциях Yii2?',
                'answer' => 'Yii2 предлагает **абстрактные типы**, которые транслируются в нативные типы каждой СУБД. Это даёт миграции, **переносимые** между MySQL, PostgreSQL, SQLite.

- На уровне класса `Schema` есть константы: `TYPE_PK`, `TYPE_BIGPK`, `TYPE_STRING`, `TYPE_INTEGER`, `TYPE_DATETIME`, `TYPE_TEXT`, `TYPE_BOOLEAN`, `TYPE_DECIMAL`, `TYPE_JSON` и т.д.
- В `Migration` есть **builder-методы**: `$this->primaryKey()`, `$this->string(255)`, `$this->integer()`, `$this->datetime()`, `$this->boolean()`, `$this->text()`, `$this->json()`.
- К ним цепляются модификаторы: `notNull()`, `null()`, `defaultValue($v)`, `unique()`, `unsigned()`, `after(\'col\')`, `comment(\'...\')`.
- `primaryKey()` = `INT AUTO_INCREMENT PRIMARY KEY` в MySQL, `SERIAL PRIMARY KEY` в PostgreSQL.',
                'code_example' => '$this->createTable(\'{{%post}}\', [
    \'id\' => $this->primaryKey(),                      // INT PK auto-increment
    \'author_id\' => $this->integer()->notNull(),
    \'title\' => $this->string(255)->notNull(),
    \'content\' => $this->text(),
    \'status\' => $this->smallInteger()->notNull()->defaultValue(0),
    \'is_pinned\' => $this->boolean()->notNull()->defaultValue(false),
    \'metadata\' => $this->json(),
    \'created_at\' => $this->dateTime()->notNull(),
    \'updated_at\' => $this->dateTime(),
]);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.migrations',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как добавить и удалить foreign key в миграции Yii2?',
                'answer' => '**`addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete = null, $update = null)`** создаёт FK.

- `$name` — **имя constraint** (нужно для последующего `dropForeignKey`).
- `$columns` / `$refColumns` — колонка-источник и колонка-цель (строка или массив для composite FK).
- `$delete`, `$update` — стратегии **`CASCADE`** / **`RESTRICT`** / **`SET NULL`** / **`NO ACTION`**.
- Удалить — `dropForeignKey($name, $table)`.
- Имя FK обычно делают `fk-<table>-<col>`, чтобы легко найти.',
                'code_example' => 'public function safeUp()
{
    $this->addForeignKey(
        \'fk-post-author_id\',
        \'{{%post}}\',
        \'author_id\',
        \'{{%user}}\',
        \'id\',
        \'CASCADE\',  // ON DELETE
        \'CASCADE\'   // ON UPDATE
    );
}

public function safeDown()
{
    $this->dropForeignKey(\'fk-post-author_id\', \'{{%post}}\');
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.migrations',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как создать индекс (в т.ч. уникальный и составной) в миграции Yii2?',
                'answer' => '**`createIndex($name, $table, $columns, $unique = false)`** добавляет индекс.

- `$columns` — строка или массив (для составного индекса).
- `$unique = true` — UNIQUE-индекс.
- Удалить — **`dropIndex($name, $table)`**.
- Имя обычно делают `idx-<table>-<col>` или `uq-<table>-<col>` для уникального.
- Уникальные ключи можно объявлять и в `createTable()` через модификатор `->unique()` на колонке — но как **отдельный** индекс с осмысленным именем удобнее в чейндже схемы.',
                'code_example' => 'public function safeUp()
{
    // Простой индекс
    $this->createIndex(\'idx-user-status\', \'{{%user}}\', \'status\');

    // Уникальный
    $this->createIndex(\'uq-user-email\', \'{{%user}}\', \'email\', true);

    // Составной
    $this->createIndex(\'idx-order-user_status\', \'{{%order}}\', [\'user_id\', \'status\']);
}

public function safeDown()
{
    $this->dropIndex(\'idx-order-user_status\', \'{{%order}}\');
    $this->dropIndex(\'uq-user-email\', \'{{%user}}\');
    $this->dropIndex(\'idx-user-status\', \'{{%user}}\');
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.migrations',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как вставлять данные в миграции Yii2 (insert / batchInsert / delete)?',
                'answer' => 'У `Migration` есть **обёртки над DML** — с логированием в консоль.

- **`$this->insert($table, $columns)`** — одна строка.
- **`$this->batchInsert($table, $columns, $rows)`** — много строк одним SQL.
- **`$this->update($table, $columns, $condition = \'\')`** — UPDATE.
- **`$this->delete($table, $condition = \'\')`** — DELETE.
- Полезно для **посева справочников** (роли, статусы, начальные настройки), которые должны жить в схеме.
- Для большого объёма данных лучше **посев через `DbSeeder`**, а не миграции — миграции должны быть быстрыми и идемпотентными.',
                'code_example' => 'public function safeUp()
{
    $this->createTable(\'{{%role}}\', [
        \'id\' => $this->primaryKey(),
        \'name\' => $this->string(32)->notNull()->unique(),
    ]);

    $this->batchInsert(\'{{%role}}\', [\'name\'], [
        [\'admin\'],
        [\'editor\'],
        [\'user\'],
    ]);
}

public function safeDown()
{
    $this->dropTable(\'{{%role}}\');
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.migrations',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что делает migrate/redo и в каких случаях он полезен?',
                'answer' => '**`./yii migrate/redo`** = `migrate/down 1` + `migrate/up 1` — откатывает последнюю миграцию и применяет заново.

- Полезно в **разработке**, когда поправил `safeUp()` и хочешь быстро перепрогнать.
- Принимает количество: `migrate/redo 3` — пересоберёт 3 последние миграции.
- **Никогда** не используйте `redo` на проде — потеряете данные, добавленные после миграции.
- Аналог в Laravel: `php artisan migrate:refresh --step=1`.',
                'code_example' => '# Пересобрать последнюю
./yii migrate/redo

# Пересобрать 3 последние
./yii migrate/redo 3

# Безопасный sequence в dev:
# 1. поправить safeUp в файле
# 2. ./yii migrate/redo
# 3. проверить результат через mysql клиента',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'yii2.migrations',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое таблица migration в Yii2 и как Yii отслеживает применённые миграции?',
                'answer' => 'Yii2 хранит **историю применённых миграций** в специальной таблице (по умолчанию `migration`).

- При первом запуске `migrate` Yii **создаёт** эту таблицу автоматически.
- В ней две колонки: **`version`** (имя класса миграции, например `m250520_120000_create_user_table`) и **`apply_time`** (UNIX timestamp).
- Перед каждым запуском Yii сравнивает список файлов в папке миграций с записями в таблице — применяет недостающие.
- Имя таблицы можно сменить в конфиге консольного приложения: `migrationTable => \'{{%migration}}\'`.
- Удалять записи руками не стоит — Yii не пройдёт `migrate/down` корректно.',
                'code_example' => '// console.php
return [
    \'controllerMap\' => [
        \'migrate\' => [
            \'class\' => \'yii\\\\console\\\\controllers\\\\MigrateController\',
            \'migrationTable\' => \'{{%migration}}\',
            \'migrationPath\' => \'@app/migrations\',
        ],
    ],
];

// SQL внутри:
// CREATE TABLE migration (
//   version VARCHAR(180) PRIMARY KEY,
//   apply_time INTEGER
// );',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.migrations',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое неймспейснутые (namespaced) миграции в Yii2 и зачем они нужны?',
                'answer' => 'По умолчанию миграции лежат **без namespace** — в глобальном пространстве — что мешает использовать миграции из нескольких источников (модулей, пакетов).

- **Namespaced migrations** добавлены начиная с Yii 2.0.10. Файл лежит в `migrations/M{timestamp}NameClass.php` с `namespace app\\migrations`.
- В конфиге указывают `migrationNamespaces => [\'app\\\\migrations\', \'common\\\\migrations\']` — Yii соберёт миграции из всех неймспейсов.
- Это решает конфликт имён классов между **разными пакетами** (например, ваше приложение + Yii2-user + Yii2-rbac).
- При создании: `./yii migrate/create app\\\\migrations\\\\CreateUser`.
- Современные шаблоны (advanced template) уже используют неймспейсы.',
                'code_example' => '// console.php
return [
    \'controllerMap\' => [
        \'migrate\' => [
            \'class\' => \'yii\\\\console\\\\controllers\\\\MigrateController\',
            \'migrationNamespaces\' => [
                \'app\\\\migrations\',
                \'common\\\\migrations\',
            ],
            \'migrationPath\' => null, // отключает старый flat-mode
        ],
    ],
];

// Создание
// ./yii migrate/create app\\\\migrations\\\\CreateUser

// migrations/M250520120000CreateUser.php
namespace app\\migrations;
use yii\\db\\Migration;

class M250520120000CreateUser extends Migration
{
    public function safeUp() { /* ... */ }
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.migrations',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как делать zero-downtime миграции в Yii2: expand-and-contract, transaction-DDL подводные камни и pt-online-schema-change/gh-ost?',
                'answer' => '**Проблема:** прямой `ALTER TABLE users ADD COLUMN ...` на таблице 50M строк в MySQL может **блокировать запись на 30 минут** (зависит от движка/версии). Сервис лежит. Решение — **expand-and-contract** на нескольких миграциях + специальные инструменты.

**Паттерн expand-and-contract (4 фазы):**

| Фаза | Что делает | Деплой кода |
|---|---|---|
| **1. Expand** | добавить **новую** колонку рядом со старой (`addColumn`) | старый код работает |
| **2. Backfill** | в фоне скопировать данные старая → новая (batch UPDATE) | старый код пишет в **обе** колонки (dual-write) |
| **3. Switch** | переключить **чтение** на новую колонку | новый код читает new, пишет в обе |
| **4. Contract** | удалить старую колонку | новый код читает/пишет только new |

**Между фазами — деплой нового кода**, миграция стоит отдельно от релиза.

**Подводный камень MySQL: DDL НЕ транзакционен!**

```sql
START TRANSACTION;
CREATE TABLE t (...);   -- ← MySQL делает implicit COMMIT перед этим
INSERT INTO t ...;
ROLLBACK;               -- ← откатит только INSERT, таблица останется!
```

| СУБД | DDL в транзакции |
|---|---|
| **MySQL/MariaDB** | **НЕТ** — `CREATE/ALTER/DROP` делают неявный `COMMIT` |
| **PostgreSQL** | **ДА** — почти весь DDL транзакционен (кроме `CREATE INDEX CONCURRENTLY`) |
| **SQLite** | частично |

Поэтому `safeUp()` в MySQL **обманчив** — если упало после `addColumn`, откат не сработает. **Решение:** дробить миграции на минимальные шаги + ручной cleanup `safeDown()`.

**Namespaced migrations для модульного приложения:**

- Каждый модуль (`user`, `billing`, `notification`) имеет свой namespace: `app\\modules\\billing\\migrations`.
- В консольном конфиге: `migrationNamespaces => [\'app\\\\modules\\\\billing\\\\migrations\', ...]`.
- Удобно для **переиспользования** пакетов и для **независимого** релиза модулей.

**Для PROD-масштаба Yii migrate НЕ подходит — нужны online-DDL инструменты:**

| Инструмент | Подход | Когда брать |
|---|---|---|
| **`pt-online-schema-change`** (Percona Toolkit) | теневая таблица + триггеры + RENAME | MySQL, проверено годами |
| **`gh-ost`** (GitHub) | бинлог-репликация в shadow | MySQL, без триггеров, лучше для high-write |
| **MySQL 8 `ALGORITHM=INSTANT`** | inplace для add column | начиная с 8.0.12 |
| **PG `ALTER ... ADD COLUMN NULL`** | мгновенно (с PG 11) | без default — мгновенно |
| **PG `CREATE INDEX CONCURRENTLY`** | non-blocking | PG, не работает в транзакции |

Yii migrate тут — только **обёртка для трекинга** через таблицу `migration`. Реальный ALTER делается **снаружи** (gh-ost), а в Yii-миграции — пустой `safeUp()` или просто `INSERT INTO migration`.',
                'code_example' => '// 1. Expand: добавить email_v2 рядом со старым email
namespace app\migrations;

class M260101120000AddEmailV2ToUser extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->addColumn(\'{{%users}}\', \'email_v2\', $this->string(320)->null());
    }
    public function safeDown()
    {
        $this->dropColumn(\'{{%users}}\', \'email_v2\');
    }
}

// 2. Backfill: отдельная миграция или консольная команда — batched UPDATE
class M260101130000BackfillEmailV2 extends \yii\db\Migration
{
    public function safeUp()
    {
        $lastId = 0;
        while (true) {
            $rows = (new \yii\db\Query())
                ->select([\'id\', \'email\'])
                ->from(\'{{%users}}\')
                ->where([\'>\', \'id\', $lastId])
                ->andWhere([\'email_v2\' => null])
                ->orderBy([\'id\' => SORT_ASC])
                ->limit(1000)
                ->all($this->db);

            if (empty($rows)) break;

            foreach ($rows as $row) {
                $this->db->createCommand()->update(\'{{%users}}\',
                    [\'email_v2\' => mb_strtolower($row[\'email\'])],
                    [\'id\' => $row[\'id\']]
                )->execute();
            }
            $lastId = end($rows)[\'id\'];
            usleep(50000);   // throttle, чтобы не лочить репликацию
        }
    }
    public function safeDown()
    {
        $this->db->createCommand()->update(\'{{%users}}\', [\'email_v2\' => null])->execute();
    }
}

// 3. Contract: удаляем старую колонку (после деплоя кода, который читает email_v2)
class M260101140000DropOldEmail extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->dropColumn(\'{{%users}}\', \'email\');
        $this->renameColumn(\'{{%users}}\', \'email_v2\', \'email\');
    }
}

// 4. Для PROD-масштаба — миграция-обёртка над gh-ost
// gh-ost запускается из CI:
// gh-ost --host=master --user=root --database=app --table=users \
//        --alter="ADD COLUMN email_v2 VARCHAR(320) NULL" \
//        --execute
// А Yii-миграция только записывает запись в таблицу migration:
class M260101150000NoopForGhost extends \yii\db\Migration
{
    public function safeUp() { /* gh-ost уже выполнил ALTER */ }
    public function safeDown() { /* также вручную через gh-ost */ }
}',
                'code_language' => 'php',
                'difficulty' => 5,
                'topic' => 'yii2.migrations',
            ],
        ];
    }
}
