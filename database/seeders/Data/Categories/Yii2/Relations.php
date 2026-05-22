<?php

namespace Database\Seeders\Data\Categories\Yii2;

class Relations
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Yii2',
                'question' => 'Как объявить отношение hasOne в Yii2 ActiveRecord?',
                'answer' => '**`hasOne($class, $link)`** — отношение «один к одному» (или «один к одному из многих»: пользователь и его профиль).

- Объявляется в **getter-методе** с префиксом `get`: `getProfile()`.
- Параметры:
  - `$class` — имя класса связанной модели (`Profile::class`).
  - `$link` — массив `[\'fk_в_связанной_таблице\' => \'pk_в_текущей\']`.
- Доступ через **магический геттер** без `get`: `$user->profile`.
- Возвращает `ActiveQuery`, на котором можно делать `->andWhere(...)` до выполнения.',
                'code_example' => 'class User extends ActiveRecord
{
    public function getProfile()
    {
        return $this->hasOne(Profile::class, [\'user_id\' => \'id\']);
    }
}

$user = User::findOne(1);
$profile = $user->profile; // Profile|null

// Можно цепочкой
$activeProfile = $user->getProfile()->andWhere([\'is_active\' => 1])->one();',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.relations',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Чем отличается hasMany от hasOne в Yii2?',
                'answer' => 'Различие — в **множественности**: `hasOne` возвращает одну модель, `hasMany` — массив.

| Аспект | `hasOne` | `hasMany` |
|---|---|---|
| Возвращает | `Model|null` | `array` моделей |
| Под капотом SQL | `... LIMIT 1` (для скаляра PK) | без `LIMIT` |
| Пример | `User → Profile` | `User → Post[]` |
| Геттер | `$user->profile` | `$user->posts` |

- Сигнатура та же: `hasMany($class, $link)`.
- Ключ `$link` — `[\'fk_в_дочерней\' => \'pk_в_родительской\']`.
- В hasMany часто добавляют `->orderBy([\'created_at\' => SORT_DESC])`.',
                'code_example' => 'class User extends ActiveRecord
{
    public function getPosts()
    {
        return $this->hasMany(Post::class, [\'author_id\' => \'id\'])
            ->orderBy([\'created_at\' => SORT_DESC]);
    }
}

$user = User::findOne(1);
foreach ($user->posts as $post) {
    echo $post->title;
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.relations',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Чем отличается lazy loading от eager loading в Yii2 (with vs прямой доступ)?',
                'answer' => 'Lazy и eager — два способа загрузить связанные данные.

| Стратегия | Когда грузится | SQL-запросов | Подходит для |
|---|---|---|---|
| **Lazy** (`$user->posts`) | При первом обращении к свойству | 1 на каждую модель — **N+1 problem** | Случайный доступ к relation в одной модели |
| **Eager** (`User::find()->with(\'posts\')`) | Сразу одним запросом для всех | 2 (модель + relation одним IN) | Списки, циклы по моделям |

- Lazy кэширует результат **на инстансе**: повторный `$user->posts` не делает SQL.
- Eager строит **один `IN (...)`** для всех родителей.
- Eager + asArray() — самый быстрый read-only.',
                'code_example' => '// LAZY — 1 + N запросов
$users = User::find()->all();
foreach ($users as $user) {
    echo count($user->posts); // SELECT для каждого
}

// EAGER — 2 запроса
$users = User::find()->with(\'posts\')->all();
foreach ($users as $user) {
    echo count($user->posts); // уже в памяти
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.relations',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как объявить many-to-many через via() / viaTable() в Yii2?',
                'answer' => 'Many-to-many делается **через промежуточную таблицу** (junction). В Yii2 — два способа.

- **`via(\'relationName\')`** — ссылается на **другое отношение** модели (рекомендуется, если junction-таблица имеет свою модель).
- **`viaTable(\'tablename\', [\'fk\' => \'pk\'])`** — указать **имя таблицы напрямую** (когда отдельной модели нет).

В обоих случаях основное отношение — это `hasMany(Target, [\'pk\' => \'fk_в_junction\'])`, а `via` указывает мост.',
                'code_example' => 'class User extends ActiveRecord
{
    // 1) Через viaTable — без модели-моста
    public function getRoles()
    {
        return $this->hasMany(Role::class, [\'id\' => \'role_id\'])
            ->viaTable(\'{{%user_role}}\', [\'user_id\' => \'id\']);
    }

    // 2) Через via — нужна модель UserRole + getUserRoles()
    public function getUserRoles()
    {
        return $this->hasMany(UserRole::class, [\'user_id\' => \'id\']);
    }
    public function getRolesViaRelation()
    {
        return $this->hasMany(Role::class, [\'id\' => \'role_id\'])
            ->via(\'userRoles\');
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.relations',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что делают методы link() и unlink() в Yii2 ActiveRecord?',
                'answer' => '`link()` и `unlink()` — **меняют связь** между двумя моделями без ручного `INSERT/UPDATE`.

- **`$model->link($relationName, $target)`**:
  - Для `hasOne/hasMany` — выставляет FK у target.
  - Для many-to-many (`via/viaTable`) — **вставляет строку в junction**.
- **`$model->unlink($relationName, $target, $delete = false)`**:
  - Для `hasMany` — обнуляет FK (или удаляет, если передать `true`).
  - Для many-to-many — удаляет строку из junction.
- **`unlinkAll($relationName, $delete = false)`** — массовая отвязка.
- Если FK `NOT NULL`, передавайте `$delete = true`, чтобы реально удалить запись.',
                'code_example' => '$user = User::findOne(1);
$role = Role::findOne(2);

$user->link(\'roles\', $role); // INSERT в user_role

$user->unlink(\'roles\', $role); // DELETE из user_role

$post = new Post([\'title\' => \'Hello\']);
$post->save();
$user->link(\'posts\', $post); // выставит post.author_id = $user->id

$user->unlinkAll(\'posts\', true); // DELETE FROM post WHERE author_id = ...',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.relations',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Чем joinWith() отличается от with() в Yii2?',
                'answer' => '**`with()`** делает **отдельные SQL** на каждое отношение (одна модель — один доп. запрос).

**`joinWith()`** добавляет **`JOIN` в основной запрос** и одновременно eager-loadит отношение.

| Метод | SQL для relation | Можно фильтровать/сортировать по полям relation в основном WHERE/ORDER BY |
|---|---|---|
| `with(\'posts\')` | Отдельный запрос с `IN (...)` | Нет |
| `joinWith(\'posts\')` | `INNER JOIN post ON ...` | Да |

- `joinWith()` по умолчанию использует `LEFT JOIN`; вторым аргументом можно сменить на `INNER`/`RIGHT`.
- Третий аргумент — тип join: `\'INNER JOIN\'`, `\'LEFT JOIN\'`.
- **Внимание**: при join-условии в `where` нужно квалифицированное имя — `post.status` или `{{post}}.[[status]]`.',
                'code_example' => '// Отфильтровать пользователей у которых есть опубликованные посты
$users = User::find()
    ->joinWith([\'posts\' => function ($q) {
        $q->andWhere([\'post.status\' => 1]);
    }])
    ->where([\'user.is_active\' => 1])
    ->all();

// INNER JOIN вместо LEFT
$users = User::find()->joinWith(\'posts\', true, \'INNER JOIN\')->all();',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.relations',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как задать алиас таблицы и тип JOIN в Yii2 ActiveQuery?',
                'answer' => 'Для контроля JOIN в Yii2 используется метод **`alias($name)`** на ActiveQuery и параметры `joinWith()`.

- `alias()` назначает алиас **основной таблице**, чтобы в `where` не повторять имя.
- `innerJoinWith($relation)` — синтаксический сахар для `joinWith($relation, true, \'INNER JOIN\')`.
- Можно вызвать `innerJoin()` / `leftJoin()` / `rightJoin()` напрямую для произвольной таблицы (без relation), указав условие.
- При JOIN-конфликтах одинаковых имён колонок ставьте `select()` явно.',
                'code_example' => '// Алиас + INNER JOIN
$users = User::find()
    ->alias(\'u\')
    ->innerJoinWith([\'posts p\' => function ($q) {
        $q->andWhere([\'p.status\' => 1]);
    }])
    ->where([\'u.is_active\' => 1])
    ->all();

// Произвольный JOIN без relation
$query = User::find()
    ->select([\'user.*\', \'role_count\' => \'COUNT(ur.role_id)\'])
    ->leftJoin(\'{{%user_role}} ur\', \'ur.user_id = user.id\')
    ->groupBy(\'user.id\');',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.relations',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как добавить условие к relation при eager loading через with() в Yii2?',
                'answer' => 'В `with()` можно передать **анонимную функцию**, которая принимает `ActiveQuery` отношения.

- Ключ массива — имя relation, значение — callback или `null`.
- Внутри можно вызывать `andWhere()`, `orderBy()`, `limit()`, `select()`, **другие** `with()`.
- Это **не фильтрует основную модель** (для этого — `joinWith` + `where`).
- Часто используется, чтобы подгрузить **только активные** связанные записи или **выбрать только нужные колонки**.',
                'code_example' => '$users = User::find()
    ->with([
        \'posts\' => function ($q) {
            $q->andWhere([\'status\' => 1])
              ->orderBy([\'created_at\' => SORT_DESC])
              ->limit(5);
        },
        \'profile\',
    ])
    ->all();

// Вложенный with: посты + их теги
$users = User::find()
    ->with([
        \'posts.tags\',
        \'posts.author\',
    ])
    ->all();',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.relations',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое inverseOf() в отношениях Yii2 и зачем оно нужно?',
                'answer' => '**`inverseOf($relationName)`** говорит Yii: «обратное отношение к этому называется так-то».

- Без `inverseOf()` при `$user->posts[0]->author` Yii **снова** сделает запрос — даже если `$user` уже известен.
- С `inverseOf` Yii **переиспользует** уже загруженного `$user` для всех его постов — экономит память и SQL.
- Объявляется парно: в обеих моделях.
- Особенно полезно при **обходе графа** объектов (например, в иерархических деревьях, рендеринге дерева комментариев).',
                'code_example' => 'class User extends ActiveRecord
{
    public function getPosts()
    {
        return $this->hasMany(Post::class, [\'author_id\' => \'id\'])
            ->inverseOf(\'author\');
    }
}

class Post extends ActiveRecord
{
    public function getAuthor()
    {
        return $this->hasOne(User::class, [\'id\' => \'author_id\'])
            ->inverseOf(\'posts\');
    }
}

$users = User::find()->with(\'posts\')->all();
foreach ($users[0]->posts as $post) {
    // $post->author — это уже $users[0], без SQL
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.relations',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Чем отличается $user->getPosts() от $user->posts в Yii2?',
                'answer' => 'Это **два разных уровня доступа** к relation.

- **`$user->getPosts()`** — возвращает **`ActiveQuery`** (запрос ещё не выполнен). К нему можно цеплять `andWhere`, `orderBy`, `count`, `exists`, `one`.
- **`$user->posts`** (без `get`) — Yii магически вызывает `getPosts()->all()`/`->one()` и **возвращает результат**.
  - Результат **кэшируется** на инстансе модели: повторный `$user->posts` не делает SQL.
- Используйте `getPosts()`, когда нужно **доуточнить** запрос или сделать `count()`/`exists()` без выборки строк.',
                'code_example' => '$user = User::findOne(1);

// Готовый результат (массив или null)
$posts = $user->posts; // array

// ActiveQuery — можно цеплять
$publishedCount = $user->getPosts()
    ->andWhere([\'status\' => 1])
    ->count();

$hasAny = $user->getPosts()->exists();

// Сбросить кэш relation
$user->refresh(); // или unset($user->posts);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.relations',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что делают getRelation() и populateRelation() в Yii2 ActiveRecord?',
                'answer' => 'Это **низкоуровневые методы** для работы с отношениями.

- **`getRelation($name, $throwException = true)`** возвращает объект `ActiveQuery` для relation по имени-строке. Удобно когда имя relation известно динамически.
- **`populateRelation($name, $records)`** **вручную выставляет** результат relation на модели — без SQL.

`populateRelation()` нужен в редких случаях: при кастомной загрузке (например, из кэша или REST), в тестах, в реализации `inverseOf`-подобного оптимизатора вручную.',
                'code_example' => '$user = User::findOne(1);

// Получить ActiveQuery по строковому имени
$query = $user->getRelation(\'posts\');
$published = $query->andWhere([\'status\' => 1])->all();

// Вручную задать результат relation (например из кэша)
$cachedPosts = Yii::$app->cache->get("user_posts_{$user->id}") ?: [];
$user->populateRelation(\'posts\', $cachedPosts);
// Теперь $user->posts вернёт $cachedPosts без SQL',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.relations',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как добавить условие через подзапрос в Yii2 ActiveQuery (например EXISTS)?',
                'answer' => 'В Yii2 можно вкладывать `Query`/`ActiveQuery` в `where()` — Yii соберёт **подзапрос** в скобках.

- Оператор **`[\'exists\', $subQuery]`** и **`[\'not exists\', $subQuery]`** — для EXISTS.
- Можно сделать подзапрос-объект и подставить в `in`: `[\'in\', \'id\', $subQuery]`.
- Подзапросы — типовой способ заменить дорогой `JOIN`+`DISTINCT` на компактный `WHERE EXISTS`.',
                'code_example' => 'use yii\\db\\Query;

// Пользователи с хотя бы одним опубликованным постом
$sub = (new Query())
    ->select(\'id\')
    ->from(\'{{%post}}\')
    ->where(\'post.author_id = user.id\')
    ->andWhere([\'status\' => 1]);

$users = User::find()
    ->where([\'exists\', $sub])
    ->all();

// IN через подзапрос
$activeAuthors = (new Query())
    ->select(\'author_id\')
    ->from(\'{{%post}}\')
    ->where([\'status\' => 1]);

$users = User::find()
    ->where([\'in\', \'id\', $activeAuthors])
    ->all();',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.relations',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как получить доступ к доп. колонке junction-таблицы many-to-many в Yii2?',
                'answer' => 'Если junction-таблица содержит **дополнительные колонки** (например, `position`, `created_at`), есть два подхода.

- **Без отдельной модели**: используйте `viaTable()` и для атрибутов junction отдельно делайте запрос — стандартные `via/viaTable` отдают **только модель-цель**, без junction-полей.
- **Рекомендованный**: сделать **полноценную модель** junction-таблицы (например, `UserRole`) и работать с ней как с обычной AR. Связь `via(\'userRoles\')` даёт доступ через `getUserRoles()`.
- Третий аргумент `link()` принимает массив доп. колонок: `$user->link(\'roles\', $role, [\'position\' => 1])` — Yii запишет это в junction.',
                'code_example' => 'class User extends ActiveRecord
{
    public function getUserRoles()
    {
        return $this->hasMany(UserRole::class, [\'user_id\' => \'id\']);
    }
    public function getRoles()
    {
        return $this->hasMany(Role::class, [\'id\' => \'role_id\'])
            ->via(\'userRoles\');
    }
}

// Связать с extra-колонкой position
$user->link(\'roles\', $role, [\'position\' => 1, \'created_at\' => time()]);

// Доступ к доп. полю — через junction-модель
foreach ($user->userRoles as $ur) {
    echo $ur->position;
    echo $ur->role->name;
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.relations',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое hasOne через via() в Yii2 на примере «through»-отношения?',
                'answer' => 'Через `via()` можно сделать **«through»-отношение**: достать связанную через цепочку.

- Пример: `Post → User → Country`. У `Post` нет прямого FK на `Country`, но через `author` (User) можно добраться.
- В модели объявляются **обе цепочки**: сначала промежуточное, потом target с `via()` на промежуточное.
- Поддерживается и для `hasOne`, и для `hasMany`.
- При `with(\'author.country\')` Yii уже делает 2 SQL — JOIN через `via` нужен только если хотите фильтровать в основном запросе.',
                'code_example' => 'class Post extends ActiveRecord
{
    public function getAuthor()
    {
        return $this->hasOne(User::class, [\'id\' => \'author_id\']);
    }

    public function getAuthorCountry()
    {
        return $this->hasOne(Country::class, [\'id\' => \'country_id\'])
            ->via(\'author\');
    }
}

$post = Post::findOne(1);
echo $post->authorCountry->name; // США',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.relations',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как сбросить кэш связанного relation в Yii2 ActiveRecord?',
                'answer' => 'Когда обращаешься к `$user->posts`, результат **кэшируется на инстансе**. Если данные изменились и нужно перечитать — кэш надо сбросить.

- **`unset($user->posts)`** — удаляет только конкретный relation из кэша.
- **`$user->refresh()`** — перечитывает атрибуты самой модели **и** очищает все кэшированные relations.
- **`Model::findOne($user->id)`** — даст совсем свежий экземпляр.

Это важно учитывать, например, после `link()`/`unlink()` или `Post::updateAll(...)`, которые меняют БД, но **не трогают** уже загруженный объект.',
                'code_example' => '$user = User::findOne(1);
$first = $user->posts; // SQL

$user->link(\'posts\', new Post([\'title\' => \'new\']));
$cached = $user->posts; // ТО ЖЕ что first — кэш

unset($user->posts);
$fresh = $user->posts; // снова SQL, видит новый пост

// Или полностью обновить
$user->refresh();',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.relations',
            ],
        ];
    }
}
