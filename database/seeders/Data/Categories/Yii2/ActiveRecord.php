<?php

namespace Database\Seeders\Data\Categories\Yii2;

class ActiveRecord
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Yii2',
                'question' => 'Что такое ActiveRecord в Yii2 и чем он отличается от DAO и Query Builder?',
                'answer' => '**ActiveRecord** — это ORM-паттерн: один объект = одна строка таблицы. Класс модели предоставляет методы CRUD без написания SQL.

| Слой | Что делает | Пример |
|---|---|---|
| `yii\\db\\Command` (DAO) | Сырой SQL + bind-параметры | `createCommand($sql)->execute()` |
| `yii\\db\\Query` (Query Builder) | Сборка SQL без знания таблиц-моделей | `(new Query)->from(...)->all()` |
| `yii\\db\\ActiveRecord` (ORM) | Объекты со связями, валидацией, событиями | `User::findOne(5)->save()` |

- DAO — самый низкий уровень, **полный контроль над SQL**.
- Query Builder — удобный конструктор, но возвращает массивы.
- ActiveRecord — даёт **объекты**, **отношения** (`hasOne/hasMany`), **rules()/scenarios()**, события `beforeSave/afterSave`.',
                'code_example' => 'use yii\\db\\ActiveRecord;

class User extends ActiveRecord
{
    public static function tableName(): string
    {
        return \'{{%user}}\';
    }
}

$user = User::findOne(1);
$user->email = \'new@example.com\';
$user->save();',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.active_record',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как создать модель ActiveRecord в Yii2: от какого класса наследовать и зачем нужен tableName()?',
                'answer' => 'Модель **наследуется от `yii\\db\\ActiveRecord`** (или `yii\\mongodb\\ActiveRecord` и т.п. для других БД).

- Метод `tableName()` возвращает имя таблицы.
- **По умолчанию** Yii выводит имя из класса: `User` → `user`, `OrderItem` → `order_item` (snake_case).
- `tableName()` нужно переопределять, когда имя таблицы **не совпадает** с автоматическим — например с префиксом `{{%user}}` (где `%` подставляет `db->tablePrefix`).
- Возвращать имя через `{{%...}}` — **рекомендуется**: тогда префикс таблиц подменяется конфигом.',
                'code_example' => 'namespace app\\models;

use yii\\db\\ActiveRecord;

class User extends ActiveRecord
{
    public static function tableName(): string
    {
        return \'{{%user}}\';
    }
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.active_record',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как получить запись по первичному ключу или по условию в ActiveRecord Yii2?',
                'answer' => '**`findOne($id)`** — самый короткий способ получить одну запись.

- Принимает **скаляр** (значение PK) или **ассоциативный массив** условий.
- Возвращает `ActiveRecord|null`.
- Для нескольких записей — **`findAll([...])`**.
- Под капотом для скаляра запускает `SELECT ... WHERE pk = ? LIMIT 1`.

**ВНИМАНИЕ**: не передавайте в `findOne()` пользовательский ввод как массив в произвольном формате — для безопасности используйте простую форму `findOne($id)` или явное условие `find()->where([...])`.',
                'code_example' => '$user = User::findOne(5);
$user = User::findOne([\'email\' => \'a@b.c\']);

$activeUsers = User::findAll([\'status\' => 1]);
$users = User::findAll([1, 2, 3]); // по списку PK',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.active_record',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что возвращает User::find() и как им пользоваться?',
                'answer' => '`User::find()` возвращает **`ActiveQuery`** — наследник `Query`, привязанный к модели.

- К `find()` цепляются условия: `where()`, `andWhere()`, `orderBy()`, `limit()`, `with()`.
- Терминальные методы запускают запрос:
  - **`one()`** → `ActiveRecord|null`.
  - **`all()`** → массив моделей.
  - **`count()`**, **`sum()`**, **`exists()`**, **`scalar()`**, **`column()`**.
- Это «ленивый» билдер: SQL не выполняется, пока не вызван терминальный метод.',
                'code_example' => 'use app\\models\\User;

$users = User::find()
    ->where([\'status\' => 1])
    ->andWhere([\'>\', \'created_at\', time() - 86400])
    ->orderBy([\'created_at\' => SORT_DESC])
    ->limit(20)
    ->all();

$total = User::find()->where([\'status\' => 1])->count();
$exists = User::find()->where([\'email\' => $email])->exists();',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.active_record',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Чем отличаются save(), insert(), update(), delete() в Yii2 ActiveRecord?',
                'answer' => 'Все четыре метода — **операции записи**, но различаются по поведению.

| Метод | Что делает | Запускает валидацию |
|---|---|---|
| `save()` | `insert()` если `isNewRecord=true`, иначе `update()` | Да (можно отключить `save(false)`) |
| `insert()` | Только вставка | Да (по умолчанию) |
| `update()` | Только обновление **существующей** записи | Да |
| `delete()` | Удаляет запись по PK | Нет |

- `save()` — самый удобный «диспетчер».
- Все методы возвращают `bool` (или `false` при ошибке валидации) и **поднимают события** `beforeSave/afterSave` / `beforeInsert/afterInsert`.
- `delete()` поднимает `beforeDelete/afterDelete`.',
                'code_example' => '$user = new User();
$user->email = \'a@b.c\';
if ($user->save()) {
    echo $user->id; // PK заполнен после insert
}

$user = User::findOne(1);
$user->status = 0;
$user->save();

$user->delete();',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.active_record',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое $model->isNewRecord в Yii2 ActiveRecord?',
                'answer' => '**`isNewRecord`** — флаг, говорящий «это новая запись, ещё не сохранённая в БД».

- У `new User()` он равен `true`.
- После `save()`/`insert()` становится `false`.
- У записей, полученных через `find()/findOne()/findAll()`, всегда `false`.
- На этот флаг смотрит `save()` чтобы решить — делать `INSERT` или `UPDATE`.

Можно вручную выставить `false`/`true`, но это нужно только в очень редких сценариях (например, восстановление состояния модели после кэша).',
                'code_example' => '$user = new User();
var_dump($user->isNewRecord); // true

$user->save();
var_dump($user->isNewRecord); // false

$existing = User::findOne(1);
var_dump($existing->isNewRecord); // false',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.active_record',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое dirty attributes в Yii2 ActiveRecord (getDirtyAttributes / getOldAttributes)?',
                'answer' => '**Dirty attributes** — атрибуты, **изменённые** после загрузки модели из БД.

- `getDirtyAttributes()` — массив `[имя => новое_значение]` для изменившихся полей.
- `getOldAttributes()` — массив **исходных** значений (как было в БД).
- `getOldAttribute($name)` — старое значение одного атрибута.
- При `update()` Yii пишет в БД **только dirty-атрибуты** (а не все колонки) — экономит SQL.
- После успешного `save()` массив старых значений обновляется на текущее состояние.',
                'code_example' => '$user = User::findOne(1);
$user->email = \'new@example.com\';
$user->status = 1;

$dirty = $user->getDirtyAttributes();
// [\'email\' => \'new@example.com\', \'status\' => 1]

$oldEmail = $user->getOldAttribute(\'email\');
// прежнее значение email

$user->save();
// в SQL: UPDATE user SET email=?, status=? WHERE id=?',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.active_record',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое safe attributes и как они связаны с rules() в Yii2?',
                'answer' => 'В Yii2 атрибут считается **safe**, если он упомянут хотя бы в одном правиле `rules()` для **текущего сценария**.

- Только safe-атрибуты заполняются через **массовое присваивание**: `$model->load($data)`, `$model->attributes = $data`.
- Это защита от mass assignment vulnerability: даже если в POST пришёл `is_admin=1`, без правила он не попадёт в модель.
- Чтобы пометить атрибут как safe **без валидации**, используют валидатор `safe`: `[[\'description\'], \'safe\']`.
- Через `scenarios()` можно ограничить набор safe-атрибутов под конкретный сценарий (regular user vs admin).',
                'code_example' => 'class User extends ActiveRecord
{
    public function rules(): array
    {
        return [
            [[\'email\', \'username\'], \'required\'],
            [[\'email\'], \'email\'],
            [[\'bio\'], \'safe\'], // safe без валидации
            // is_admin отсутствует — недоступен для load()
        ];
    }
}

$user = new User();
$user->load(Yii::$app->request->post(), \'\');
// is_admin останется не заполненным',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.active_record',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Зачем нужен метод scenarios() в Yii2 ActiveRecord?',
                'answer' => '**`scenarios()`** определяет, **какие атрибуты считаются safe в каждом сценарии**.

- Один и тот же класс модели может использоваться для **регистрации**, **обновления профиля**, **админского редактирования** с разным набором полей.
- По умолчанию Yii строит сценарии автоматически из `rules()` (`on` / `except`).
- При вызове `$model->scenario = \'register\'` модель будет валидировать и сохранять только перечисленные в этом сценарии атрибуты.
- Полезно, когда не хочется плодить отдельный класс для каждого случая.',
                'code_example' => 'class User extends ActiveRecord
{
    const SCENARIO_REGISTER = \'register\';
    const SCENARIO_UPDATE = \'update\';

    public function scenarios(): array
    {
        return [
            self::SCENARIO_REGISTER => [\'username\', \'email\', \'password\'],
            self::SCENARIO_UPDATE   => [\'email\', \'bio\'],
        ];
    }
}

$user = new User([\'scenario\' => User::SCENARIO_REGISTER]);
$user->load(Yii::$app->request->post(), \'\');',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.active_record',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Какие события (хуки) есть у ActiveRecord в Yii2 и зачем они нужны?',
                'answer' => 'У `ActiveRecord` есть пары хуков `before*`/`after*` — удобные точки для кастомной логики.

- **`beforeValidate` / `afterValidate`** — вокруг `validate()`.
- **`beforeSave($insert)` / `afterSave($insert, $changedAttributes)`** — вокруг `INSERT`/`UPDATE`. Параметр `$insert` говорит, какая операция.
- **`beforeDelete` / `afterDelete`** — вокруг `DELETE`.
- **`afterFind`** — после загрузки записи из БД (раскодировать JSON, привести типы).

Если `beforeSave()` вернёт `false` — операция **отменяется**. Не забывайте `parent::beforeSave($insert)` — иначе сломаете цепочку.',
                'code_example' => 'class Post extends ActiveRecord
{
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($insert) {
            $this->created_at = time();
        }
        $this->updated_at = time();
        return true;
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->tags = json_decode($this->tags ?? \'[]\', true);
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.active_record',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое оптимистичная блокировка в Yii2 ActiveRecord и как её включить?',
                'answer' => '**Оптимистичная блокировка** защищает от потерянных обновлений, когда **два пользователя одновременно** редактируют одну запись.

- В таблицу добавляется колонка **`version` (INT)**.
- Метод модели **`optimisticLock()`** возвращает имя этой колонки.
- При `update()` Yii добавляет в `WHERE` условие `version = старая_версия` и инкрементирует `version`.
- Если **никаких строк не обновилось** (другой пользователь уже сохранил версию +1) — бросается `StaleObjectException`.
- Колонка `version` должна быть включена в `rules()` как `integer`, чтобы корректно передавалась в форму как hidden-поле.',
                'code_example' => 'class Document extends ActiveRecord
{
    public function optimisticLock(): string
    {
        return \'version\';
    }
}

try {
    $doc = Document::findOne(1);
    $doc->title = \'New\';
    $doc->save();
} catch (\\yii\\db\\StaleObjectException $e) {
    // Кто-то уже сохранил эту запись — показать конфликт
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.active_record',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как обернуть сохранение модели в транзакцию в Yii2: метод transactions() vs Yii::$app->db->transaction()?',
                'answer' => 'В Yii2 есть **два способа** запустить транзакцию вокруг записи модели.

**1. Метод `transactions()` модели** — Yii сам оборачивает указанные сценарии в транзакцию:

```
public function transactions() {
    return [
        self::SCENARIO_DEFAULT => self::OP_INSERT | self::OP_UPDATE,
    ];
}
```

**2. Явная транзакция через `Yii::$app->db->transaction(function () { ... })`** — гибче, можно охватить несколько моделей/SQL.

- Первый способ удобен, когда логика **внутри одной модели** (например, цепочка хуков).
- Второй обязателен, когда нужно сохранить **несколько связанных моделей** (Order + OrderItems).',
                'code_example' => '// Способ 1: на уровне модели
class Order extends ActiveRecord
{
    public function transactions(): array
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_INSERT | self::OP_UPDATE,
        ];
    }
}

// Способ 2: явная транзакция
Yii::$app->db->transaction(function ($db) use ($order, $items) {
    $order->save();
    foreach ($items as $item) {
        $item->order_id = $order->id;
        $item->save();
    }
});',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.active_record',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что делает $model->load($data) в Yii2 и чем отличается от ручного присваивания?',
                'answer' => '**`$model->load($data, $formName)`** — массовое заполнение модели из массива с учётом safe-атрибутов и `formName`.

- Второй аргумент `$formName` — это **префикс ключей** в `$data`:
  - `\'\'` — пустая строка, поля лежат **в корне** массива (`$_POST[\'email\']`).
  - **По умолчанию** — `$model->formName()` (имя класса), поля лежат **под ключом** (`$_POST[\'User\'][\'email\']`) — стандарт для `ActiveForm`.
- Заполняются **только safe** атрибуты (`rules()` + `scenarios()`).
- Возвращает `true`, если что-то загрузилось, `false` — если массив пустой/нет нужного ключа.
- Ручное присваивание `$model->email = $data[\'email\']` обходит проверку safe и `formName` — менее безопасно.',
                'code_example' => '// Стандартный ActiveForm: <input name="User[email]">
$user = new User();
if ($user->load(Yii::$app->request->post()) && $user->save()) {
    // ...
}

// REST API без префикса: { "email": "..." }
$user->load(Yii::$app->request->post(), \'\');

// Загрузить сразу несколько моделей
Model::loadMultiple([$user, $profile], Yii::$app->request->post());',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.active_record',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Чем различаются getAttributes() и setAttributes() в Yii2 ActiveRecord?',
                'answer' => 'Эти методы работают с **набором атрибутов модели** оптом.

- **`getAttributes($names = null, $except = [])`** возвращает массив `[имя => значение]`. Можно ограничить набор: `getAttributes([\'id\', \'email\'])`.
- **`setAttributes($values, $safeOnly = true)`** заполняет несколько атрибутов разом. По умолчанию учитывает safe-атрибуты (как `load()`); если передать `false` вторым аргументом — присвоит **любые** колонки (опасно с пользовательским вводом).
- Используются для **сериализации** (например, в JSON-ответе REST), **копирования** между моделями, **тестов**.',
                'code_example' => '$user = User::findOne(1);

$all = $user->getAttributes();
// [\'id\' => 1, \'email\' => \'a@b.c\', \'status\' => 1, ...]

$publicData = $user->getAttributes([\'id\', \'email\']);

$user->setAttributes([
    \'email\' => \'new@example.com\',
    \'status\' => 0,
]); // только safe

$user->setAttributes($adminInput, false); // ВСЕ колонки — осторожно',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.active_record',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое batchInsert в Yii2 и когда его использовать?',
                'answer' => '**`batchInsert($table, $columns, $rows)`** на `Command`/`QueryBuilder` — это **один `INSERT ... VALUES (...), (...), (...)`** с множеством строк.

- Намного быстрее, чем цикл из `$model->save()`:
  - Один SQL-запрос вместо N.
  - **Не запускаются** хуки `beforeSave/afterSave` и валидация модели.
  - **Не присваивает** PK к моделям.
- Подходит для **массовой загрузки** (импорт CSV, посев тестовых данных, аналитика).
- При огромных объёмах разбивайте на чанки (1000–5000 строк), чтобы не упереться в `max_allowed_packet`.',
                'code_example' => 'Yii::$app->db->createCommand()->batchInsert(
    \'{{%user}}\',
    [\'username\', \'email\', \'created_at\'],
    [
        [\'alice\', \'a@example.com\', time()],
        [\'bob\',   \'b@example.com\', time()],
        [\'carol\', \'c@example.com\', time()],
    ]
)->execute();',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.active_record',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что делает asArray() в ActiveQuery и зачем оно нужно?',
                'answer' => '**`asArray()`** превращает результаты `find()` в **обычные массивы** вместо объектов ActiveRecord.

- В разы быстрее: не создаются объекты, не вызывается `afterFind()`, не разворачиваются relations в объекты.
- Подходит для **read-only** сценариев: API-ответы, экспорт, отчёты.
- **Не работают** методы модели (`fullName()`, `getStatusLabel()`), потому что это просто массив.
- С `with()` совместим: связанные данные тоже придут как массивы.',
                'code_example' => '$users = User::find()
    ->select([\'id\', \'email\', \'status\'])
    ->where([\'status\' => 1])
    ->asArray()
    ->all();

// $users = [
//   [\'id\' => 1, \'email\' => \'a@b.c\', \'status\' => 1],
//   [\'id\' => 2, \'email\' => \'c@d.e\', \'status\' => 1],
// ]

// Для JSON-ответа быстрее, чем массив объектов
return $users;',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.active_record',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как объявить составной (composite) первичный ключ в Yii2 ActiveRecord?',
                'answer' => 'Если PK состоит из **нескольких колонок**, переопределите метод **`primaryKey()`**.

- По умолчанию Yii берёт PK из **схемы таблицы**. Часто этого достаточно — переопределять нужно только если хочется явно зафиксировать.
- Для составного PK `findOne()`/`findAll()` принимают **массив**: `findOne([\'order_id\' => 1, \'product_id\' => 5])`.
- В **junction-таблицах** (many-to-many) почти всегда composite PK.
- Если ваш PK составной, **`getPrimaryKey()`** возвращает массив.',
                'code_example' => 'class OrderItem extends ActiveRecord
{
    public static function tableName(): string
    {
        return \'{{%order_item}}\';
    }

    public static function primaryKey(): array
    {
        return [\'order_id\', \'product_id\'];
    }
}

$item = OrderItem::findOne([\'order_id\' => 1, \'product_id\' => 5]);
$pk = $item->getPrimaryKey(); // [\'order_id\' => 1, \'product_id\' => 5]',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.active_record',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что делает $model->save(false) в Yii2 и когда так можно?',
                'answer' => '**`save(false)`** сохраняет модель **без вызова `validate()`**.

- Полезно, когда валидация уже была проведена ранее (например, в Service-слое), а затем модель только модифицировалась внутренними методами.
- Часто используется в хуках или фабриках, где данные гарантированно корректны.
- **Опасно** делать с пользовательским вводом — потеряете защиту валидаторами.
- Сигнатура: `save($runValidation = true, $attributeNames = null)`. Второй аргумент позволяет обновить **только подмножество** атрибутов.',
                'code_example' => '$user = User::findOne(1);
$user->last_login_at = time();
$user->save(false); // не валидируем — нечего валидировать

// Сохранить только указанные поля без валидации
$user->save(false, [\'last_login_at\']);

// validate() вручную
if ($user->validate()) {
    $user->save(false); // экономим повторную валидацию
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.active_record',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Когда вызывать $model->validate() явно в Yii2 и что возвращает метод?',
                'answer' => '**`$model->validate($attributeNames = null, $clearErrors = true)`** запускает правила `rules()` для текущего сценария.

- Возвращает **`bool`**: `true` если все правила прошли, `false` — если есть ошибки.
- Ошибки доступны через `$model->errors`, `$model->getFirstError($attr)`, `$model->hasErrors()`.
- `save()` сам вызывает `validate()`, так что отдельно вызывать **обычно не нужно**.
- Явный вызов полезен:
  - Когда хочется **проверить** до операций (например, до начала транзакции).
  - В **REST-валидаторах**: вернуть `422` с массивом ошибок без сохранения.
  - В **формах без сохранения** (`yii\\base\\Model`, не AR) — например, форма поиска или контактная форма.',
                'code_example' => '$user = new User();
$user->load(Yii::$app->request->post());

if (!$user->validate()) {
    return [\'errors\' => $user->getErrors()]; // 422
}

// Бизнес-логика, потом save без повторной валидации
$user->password_hash = Yii::$app->security->generatePasswordHash($user->password);
$user->save(false);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.active_record',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое schema cache в Yii2 и как его включить?',
                'answer' => 'При запросе к ActiveRecord Yii вызывает **`SHOW COLUMNS`** / `information_schema`, чтобы узнать имена колонок и типы. Schema cache позволяет **закэшировать** этот результат.

- Включается в конфиге компонента `db`:
  - `\'schemaCache\' => \'cache\'` — какой компонент кэша использовать.
  - `\'enableSchemaCache\' => true` — включить (рекомендуется на **prod**).
  - `\'schemaCacheDuration\' => 3600` — TTL.
  - `\'schemaCacheExclude\' => [\'log\']` — исключения.
- На **dev**-окружении лучше **выключать**, иначе после миграций придётся `cache/flush-schema`.
- Команда консоли: `./yii cache/flush-schema db`.',
                'code_example' => '// config/db.php
return [
    \'class\' => \'yii\\\\db\\\\Connection\',
    \'dsn\' => \'mysql:host=localhost;dbname=app\',
    \'username\' => \'app\',
    \'password\' => \'secret\',
    \'enableSchemaCache\' => YII_ENV_PROD,
    \'schemaCacheDuration\' => 3600,
    \'schemaCache\' => \'cache\',
];

// Очистить кэш схемы после миграций:
// ./yii cache/flush-schema db',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.active_record',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как реализовать оптимистичную блокировку в продакшене Yii2: гонки, StaleObjectException и retry-стратегии?',
                'answer' => '**Проблема:** два запроса читают `version=5`, оба пишут `version=6` — теряется одно изменение (**lost update**). Yii2 решает это через `optimisticLock()`, но в проде нужны продуманные **retry-стратегии**.

**Как Yii2 это делает под капотом:**

1. `optimisticLock()` возвращает имя колонки (`version`).
2. При `$model->save()` Yii добавляет в `WHERE` старое значение версии и инкрементирует её.
3. Реальный SQL: `UPDATE t SET title=?, version=6 WHERE id=? AND version=5`.
4. Если `rowsAffected = 0` — кидается **`yii\\db\\StaleObjectException`**.

**Три подхода к конкуренции на UPDATE:**

| Подход | Когда брать | Минусы |
|---|---|---|
| **Optimistic + retry** | редкие конфликты (< 5%) | при высоком contention тратит CPU на reload |
| **Pessimistic** (`SELECT ... FOR UPDATE`) | критичные операции (деньги, склад) | row-lock, риск deadlock, не масштабируется |
| **Атомарный `UPDATE ... WHERE version=`** | счётчики, статусы | не работает для сложного бизнес-флоу |

**Когда AR-блокировка НЕ подходит:**

- Не работает с **`updateAll()`** / **`Command::update()`** — только через `save()` на отдельной модели.
- Не помогает при **`UPDATE t SET counter = counter + 1`** — для счётчиков нужен **атомарный UPDATE** без чтения.
- Несовместима с **multi-row** операциями — для них только pessimistic или advisory lock.
- При **очень высоком contention** (>50% retry) — переходи на event sourcing или CRDT.

**Retry-паттерн** должен:

- Иметь ограничение попыток (3-5), иначе **livelock**.
- Использовать **exponential backoff + jitter** для смягчения шторма.
- **Перечитывать** модель (`findOne`), а не пересохранять старую — иначе бесконечно StaleObject.',
                'code_example' => '// 1. Optimistic + retry с exponential backoff + jitter
function updateWithRetry(int $id, callable $modify, int $maxAttempts = 5): Document
{
    for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
        $doc = Document::findOne($id);
        $modify($doc);
        try {
            $doc->save();
            return $doc;
        } catch (\yii\db\StaleObjectException $e) {
            if ($attempt === $maxAttempts) {
                throw $e;
            }
            // Backoff: 10ms, 20ms, 40ms + jitter
            usleep((int)((2 ** $attempt) * 10000 + random_int(0, 5000)));
        }
    }
    throw new \RuntimeException(\'unreachable\');
}

// 2. Pessimistic — SELECT FOR UPDATE внутри транзакции
Yii::$app->db->transaction(function () use ($id) {
    $balance = (new \yii\db\Query())
        ->from(\'{{%account}}\')
        ->where([\'id\' => $id])
        ->forUpdate()    // или sql-фрагментом: "FOR UPDATE"
        ->one();
    // никто другой не прочитает эту строку до COMMIT
    Yii::$app->db->createCommand()->update(
        \'{{%account}}\',
        [\'balance\' => $balance[\'balance\'] - 100],
        [\'id\' => $id]
    )->execute();
});

// 3. Атомарный UPDATE ... WHERE version=  (без AR)
$affected = Yii::$app->db->createCommand()->update(
    \'{{%counter}}\',
    [\'value\' => new \yii\db\Expression(\'value + 1\'), \'version\' => new \yii\db\Expression(\'version + 1\')],
    [\'id\' => $id, \'version\' => $expectedVersion]
)->execute();
if ($affected === 0) {
    throw new \yii\db\StaleObjectException(\'Concurrent update\');
}',
                'code_language' => 'php',
                'difficulty' => 5,
                'topic' => 'yii2.active_record',
            ],
        ];
    }
}
