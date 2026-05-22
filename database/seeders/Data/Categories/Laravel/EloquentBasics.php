<?php

namespace Database\Seeders\Data\Categories\Laravel;

class EloquentBasics
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Laravel',
                'question' => 'Что такое Eloquent ORM?',
                'answer' => '**Eloquent** — встроенная в Laravel ORM (Object Relational Mapper), реализующая паттерн **Active Record**.

- Каждая **таблица** в БД представлена классом-моделью в `app/Models`.
- Каждая **запись** в таблице — объект этого класса.
- Вместо SQL пишем объектный код: `User::find(1)`, `$user->save()`, `User::where(...)->get()`.
- Поддерживает связи (`hasMany`, `belongsTo`), события модели, `$casts`, soft delete.',
                'code_example' => 'class User extends Model {
    protected $fillable = [\'name\', \'email\'];
}

$user = User::create([\'name\' => \'Anna\', \'email\' => \'a@b.c\']);
$user = User::find(1);
$users = User::where(\'active\', true)->get();',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'laravel.eloquent_basics',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Какие основные CRUD-методы есть у Eloquent-модели?',
                'answer' => 'Базовые CRUD-методы модели:

**Create:**

- `User::create([...])` — создать запись из массива (требует `$fillable`).
- `$user->save()` — сохранить инстанс.

**Read:**

- `User::find($id)` — по PK, `null` если нет.
- `User::findOrFail($id)` — то же, но 404.
- `User::first()` / `firstOrFail()` — первая.
- `User::all()`, `User::where(...)->get()` — коллекция.

**Update:**

- `$user->update([\'name\' => \'B\'])` или присваивание + `save()`.

**Delete:**

- `$user->delete()`.

**Свежесть данных:**

- `fresh()` — **новый** объект из БД, текущий не трогает.
- `refresh()` — перечитать данные в **текущий** инстанс.',
                'code_example' => '$user = User::create([\'name\' => \'A\', \'email\' => \'a@b.c\']);
$user->name = \'B\';
$user->save();

$user->update([\'name\' => \'C\']);

$fresh = $user->fresh(); // новый объект
$user->refresh();        // обновляет тот же объект

$user->delete();',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.eloquent_basics',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое mass assignment и зачем нужны $fillable и $guarded?',
                'answer' => '**Mass assignment** — создание/обновление модели **массивом данных** сразу: `User::create($request->all())`.

Опасность: пользователь может подсунуть лишние поля в форму — например, `is_admin=1` — и попасть в БД.

Защита:

- **`$fillable`** (whitelist) — список **разрешённых** полей. Всё остальное игнорируется.
- **`$guarded`** (blacklist) — список **запрещённых** полей. `$guarded = []` означает «всё разрешено» (так делать **не стоит**).

Если запрещённое поле попало в `create()`, Laravel при `Model::preventSilentlyDiscardingAttributes()` бросит `MassAssignmentException`. По умолчанию — тихо отфильтрует.

На практике используют **`$fillable`** — явный whitelist безопаснее.',
                'code_example' => 'class User extends Model {
    protected $fillable = [\'name\', \'email\', \'password\'];
    // или
    protected $guarded = [\'is_admin\']; // все поля разрешены кроме is_admin
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.eloquent_basics',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое casts в Eloquent?',
                'answer' => '**Casts** — автоматическое преобразование атрибутов модели **при чтении и записи**. Описываются в свойстве `$casts` (или методе `casts()` в L11+).

Пример: поле в БД хранится как JSON-строка, а в коде вы работаете с массивом — без ручных `json_encode`/`decode`.

Стандартные касты:

- **Скаляры**: `int`, `bool`, `float`, `string`.
- **Массивы/JSON**: `array`, `json`, `collection`, `AsArrayObject`, `AsCollection`.
- **Даты**: `date`, `datetime`, `immutable_datetime`, `timestamp`.
- **Деньги/точность**: `decimal:2`.
- **Безопасность**: `encrypted`, `hashed`.
- **Enums**: `UserStatus::class` (нужно backed enum).

Бонус: на новых полях модели можно сразу не писать accessor — `decimal:2` уже округлит до 2 знаков.',
                'code_example' => 'class User extends Model {
    protected $casts = [
        \'is_admin\' => \'bool\',
        \'options\' => \'array\',
        \'birthday\' => \'datetime\',
        \'salary\' => \'decimal:2\',
    ];
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.eloquent_basics',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Cast для Value Object с раскладкой в несколько колонок (price → price_amount + price_currency)?',
                'answer' => '**Задача DDD:** хранить **Value Object** (например, `Money`) **в нескольких физических колонках** (`price_amount`, `price_currency`), но в коде работать с ним как с **одним свойством модели** — `$product->price`.

**Решение — custom cast c многоколоночным `set()`:**

- Cast реализует **`CastsAttributes<TGet, TSet>`**.
- В **`set()`** возвращаем **массив с несколькими ключами** — Laravel запишет каждый ключ в свою колонку.
- В **`get()`** читаем те же колонки из **`$attributes`** по **префиксу `$key`**.
- В `$casts` ключ — это **префикс** (`price`), а не реальная колонка.

**Что под капотом:**

- При **чтении** атрибута `$product->price` Laravel вызывает `MoneyCast::get($model, \'price\', null, $attributes)` — возвращаем `Money` из `$attributes[\'price_amount\']` и `$attributes[\'price_currency\']`.
- При **записи** `$product->price = new Money(100, \'USD\')` Laravel вызывает `MoneyCast::set(...)` и сохраняет в `$attributes` обе колонки.
- В SQL `INSERT/UPDATE` уходят **обе колонки**, а виртуального `price` нет.

**Подводные камни:**

- **Все участвующие колонки** должны быть в `$fillable`/`$guarded` корректно настроены.
- **Нельзя выбрать только `select(\'price_amount\')`** — `get()` упадёт на отсутствующем `price_currency`. Решение — `Model::preventAccessingMissingAttributes()` в dev для отлова, либо защитный `?? null` в cast.
- **Сравнение Value Object** — иммутабельность важна (`final class Money`), без сеттеров; иначе `$product->price->amount = 200` не пройдёт через `set()` cast-а и не запишется.
- **Альтернатива в L9+** — **`Castable`** интерфейс прямо на Value Object: `Money::castUsing()` → возвращает экземпляр Cast. Получается, что Value Object **сам знает**, как себя кастить.
- **`AsArrayObject`** / **`AsCollection`** / **`AsEnumCollection`** — встроенные multi-attribute cast-ы из коробки.',
                'code_example' => '// Cast разворачивает ОДНО логическое поле "price" в ДВЕ физические колонки
// price_amount (int) и price_currency (string). $key даст префикс "price".
class MoneyCast implements CastsAttributes {
    public function get($model, $key, $value, $attributes): Money {
        // $value тут не используется: значения хранятся в price_amount / price_currency
        return new Money(
            (int)    $attributes["{$key}_amount"],
            (string) $attributes["{$key}_currency"],
        );
    }

    public function set($model, $key, $value, $attributes): array {
        // Возвращаем те же ключи, какие читали - Laravel запишет их в БД
        return [
            "{$key}_amount"   => $value->amount,
            "{$key}_currency" => $value->currency,
        ];
    }
}

// В $casts ключ совпадает с префиксом колонок: price → price_amount, price_currency
protected $casts = [\'price\' => MoneyCast::class];',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.eloquent_basics',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Accessors и Mutators? Какой современный синтаксис?',
                'answer' => '**Accessor** — преобразует значение **при чтении** атрибута модели (геттер с побочной логикой).

**Mutator** — преобразует **при записи** (сеттер с нормализацией: trim, lowercase, hash).

**Старый синтаксис (до Laravel 9):**

- `getNameAttribute($value)` — accessor.
- `setNameAttribute($value)` — mutator.
- Имя метода соответствует **CamelCase** имени атрибута: `first_name` → `getFirstNameAttribute`.

**Новый синтаксис (Laravel 9+) через `Attribute`:**

- Один метод `name(): Attribute` с парой `get`/`set`.
- Чище — accessor и mutator рядом, нет «двух методов на одно поле».
- Поддерживает **caching** результата (для тяжёлых вычислений).
- Поддерживает **`shouldCache()`** и **`withoutObjectCaching()`**.

**Подводные камни:**

- Метод **должен возвращать `Attribute::make(...)`**, не сам результат.
- `Attribute::make(get: fn ($v) => ...)` принимает **второй аргумент** — массив всех атрибутов модели (нужно при composite-полях типа `full_name = first + last`).
- **Mutator не вызывается при массовом UPDATE через query builder** (`User::where(...)->update([...])`) — он работает только при `save()` на инстансе. Это та же ловушка, что с events.
- Для тяжёлых вычислений: `->shouldCache()` запоминает результат в инстансе модели до перезагрузки.',
                'code_example' => '// Старый синтаксис
public function getNameAttribute($value) {
    return ucfirst($value);
}
public function setNameAttribute($value): void {
    $this->attributes[\'name\'] = strtolower($value);
}

// Новый синтаксис
protected function name(): Attribute {
    return Attribute::make(
        get: fn($value) => ucfirst($value),
        set: fn($value) => strtolower($value),
    );
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.eloquent_basics',
            ],
            [
                'category' => 'Laravel',
                'question' => 'В чём разница между firstOrCreate, updateOrCreate и upsert?',
                'answer' => 'Три метода с похожими именами, но **разной семантикой и стоимостью**.

**Сравнение:**

| Метод | Что делает | Запросов | Событие | Bulk? |
|---|---|---|---|---|
| **`firstOrCreate`** | Найти по атрибутам — если нет, создать | 1-2 | `creating/created` (если создал) | Нет |
| **`firstOrNew`** | То же, но **не сохраняет** новую запись | 1 | — | Нет |
| **`updateOrCreate`** | Найти и обновить **или** создать | 1-2 | `updating/updated` или `creating/created` | Нет |
| **`upsert`** | Массово вставить/обновить **одним запросом** | 1 | **Не срабатывают** | Да |

**Когда что выбирать:**

- **`firstOrCreate`** — справочники, идемпотентная регистрация юзера по email.
- **`updateOrCreate`** — sync настроек, импорт по уникальному ключу.
- **`upsert`** — массовая загрузка из CSV/API, тысячи записей.

**Подводные камни — race conditions:**

- `firstOrCreate` делает **SELECT + INSERT** — между ними два процесса могут увидеть «нет записи» и оба сделать INSERT → дубликат. Защита — **UNIQUE-индекс на колонках поиска**, тогда второй INSERT упадёт с 23000/23505 и Laravel сделает retry-SELECT.
- `upsert` атомарен на уровне БД (`ON DUPLICATE KEY UPDATE` в MySQL, `ON CONFLICT DO UPDATE` в Postgres) — **обязательно** требует UNIQUE/PK на колонках из `uniqueBy`.
- `upsert` **не триггерит** наблюдателей и события модели — Scout-индекс не обновится, нужно вручную `searchable()`.
- `updated_at` при `upsert` обновляется автоматически, `created_at` — только для **новых** строк.',
                'code_example' => 'User::firstOrCreate(
    [\'email\' => \'a@b.c\'],
    [\'name\' => \'Anna\']
);

User::updateOrCreate(
    [\'email\' => \'a@b.c\'],
    [\'name\' => \'Updated\']
);

User::upsert([
    [\'email\' => \'a@b.c\', \'name\' => \'A\'],
    [\'email\' => \'b@b.c\', \'name\' => \'B\'],
], uniqueBy: [\'email\'], update: [\'name\']);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.eloquent_basics',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Query Builder в Laravel?',
                'answer' => '**Query Builder** — инструмент построения SQL-запросов через цепочки PHP-методов, **не привязанный к моделям**. Под капотом Eloquent сам использует Query Builder.

Точка входа — фасад **`DB`**:

- `DB::table(\'users\')->where(...)->get()` — стартует с таблицы.
- Eloquent-модель сразу даёт билдер: `User::where(...)->get()`.

Когда брать Query Builder вместо Eloquent:

- **Нет нужды в модели** — отчёты, аналитика, миграции, ETL.
- **Скорость** — без гидратации моделей и событий. Возвращает `stdClass`/массивы.
- **Тяжёлый SQL** — оконные функции, CTE, агрегаты, JOIN на 5 таблиц.

Возвращает `Collection` со `stdClass` объектами, а не модели.',
                'code_example' => 'use Illuminate\Support\Facades\DB;

$users = DB::table(\'users\')
    ->select(\'id\', \'name\')
    ->where(\'active\', true)
    ->whereIn(\'role\', [\'admin\', \'editor\'])
    ->orderBy(\'created_at\', \'desc\')
    ->limit(10)
    ->get();',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.eloquent_basics',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как делать joins в Query Builder?',
                'answer' => 'Query Builder поддерживает все четыре типа SQL JOIN.

**Типы:**

| Метод | SQL | Когда применять |
|---|---|---|
| `join()` | `INNER JOIN` | Только записи с совпадением в обеих таблицах |
| `leftJoin()` | `LEFT JOIN` | Все записи слева + матчи справа (NULL, если нет) |
| `rightJoin()` | `RIGHT JOIN` | Зеркало leftJoin (используется редко) |
| `crossJoin()` | `CROSS JOIN` | Декартово произведение, без `ON` |

**Сложные условия — через closure:**

```php
DB::table(\'users\')
    ->join(\'posts\', function ($join) {
        $join->on(\'users.id\', \'=\', \'posts.user_id\')
             ->where(\'posts.published\', true);
    })
    ->get();
```

**Подводные камни:**

- **`select()` обязателен при JOIN** — иначе получите перемешанные поля и `id` будет последним совпадением.
- При JOIN с Eloquent (`User::join(...)`) hydration работает корректно **только если выбраны колонки одной модели**: `->select(\'users.*\')`.
- **JOIN ломает `with()` для отношений** — если делать `User::join(\'posts\', ...)->with(\'posts\')`, eager-load выполнит **отдельный** запрос; eager-load не использует JOIN.
- **Дубликаты строк** при `JOIN` с one-to-many — используйте `groupBy(\'users.id\')` или `distinct()`.
- Для отношений Eloquent чаще нужен **`whereHas`** + **`with`**, а не ручной JOIN.',
                'code_example' => 'DB::table(\'users\')
    ->join(\'posts\', \'users.id\', \'=\', \'posts.user_id\')
    ->leftJoin(\'profiles\', \'users.id\', \'=\', \'profiles.user_id\')
    ->select(\'users.*\', \'posts.title\')
    ->get();',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.eloquent_basics',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как использовать сырые выражения (raw expressions) в Query Builder?',
                'answer' => '**Raw expressions** — способ воткнуть произвольный SQL в запрос Query Builder/Eloquent.

**Основные методы:**

- **`DB::raw(\'COUNT(*)\')`** — вставка сырого SQL в `select`/`where`/любое место.
- **`selectRaw(\'COUNT(*) as total, status\', $bindings)`** — короткая обёртка.
- **`whereRaw(\'created_at > ?\', [now()->subMonth()])`** — RAW в `where`.
- **`orderByRaw(\'FIELD(status, ?, ?, ?)\', [\'open\', \'pending\', \'closed\'])`** — кастомная сортировка.
- **`havingRaw(\'SUM(price) > ?\', [1000])`** — RAW в `having`.

**Зачем нужен:**

- Оконные функции (`ROW_NUMBER()`, `LAG()`, `RANK() OVER (...)`).
- Агрегаты со сложными выражениями.
- Специфичные функции СУБД (`JSON_EXTRACT`, `ST_Distance`, `ts_rank`).

**Критическое правило безопасности:**

`DB::raw()` **вставляет строку напрямую в SQL без экранирования**. Если внутри окажется пользовательский ввод — это **SQL-инъекция**.

| Плохо | Правильно |
|---|---|
| `whereRaw("status = \'{$request->status}\'")` | `whereRaw(\'status = ?\', [$request->status])` |
| `orderByRaw(request(\'sort\'))` | Whitelist: `in_array($sort, [\'id\',\'name\'], true)` |

**Bindings** проходят через PDO-плейсхолдеры и экранируются драйвером — это безопасно. Для **имён колонок и направления сортировки** bindings не работают (это часть синтаксиса) — нужен whitelist.',
                'code_example' => 'DB::table(\'users\')
    ->selectRaw(\'COUNT(*) as total, status\')
    ->whereRaw(\'created_at > ?\', [now()->subMonth()])
    ->groupBy(\'status\')
    ->get();',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.eloquent_basics',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое DB::transaction и как работают вложенные транзакции?',
                'answer' => '**`DB::transaction($callback, $attempts)`** оборачивает код в транзакцию: исключение внутри → **rollback**, иначе → **commit**. Второй аргумент включает **retry при ошибках конкуренции** (deadlock).

**Альтернатива** — ручное управление: `DB::beginTransaction()` / `DB::commit()` / `DB::rollBack()`.

**Вложенные транзакции — это SAVEPOINT, не настоящие nested:**

В MySQL/Postgres настоящих nested transactions **не существует**, Laravel эмулирует их через `SAVEPOINT`. Отсюда ловушки:

**Ловушка 1 — внешний rollback отменяет внутренние:**

«Закоммиченная» внутренняя транзакция — это всего лишь `RELEASE SAVEPOINT`. Если внешняя откатится, **откатится всё**, включая внутреннее. Поэтому для событий после фактического commit используйте **`DB::afterCommit()`** или `$afterCommit = true` на Job/Listener.

**Ловушка 2 — Postgres aborted-state:**

После **любой** SQL-ошибки в транзакции PG переходит в состояние «current transaction is aborted» — все следующие запросы возвращают `commands ignored until end of transaction block`. Хорошая новость: `DB::transaction(callable)` **автоматически** делает `rollBack()` (= `ROLLBACK TO SAVEPOINT` для вложенных) и пробрасывает исключение — выловив его во внешнем callback, можно безопасно продолжать.

**Ловушка 3 — ручной `beginTransaction` + проглоченное исключение:**

Если поймали исключение через `try/catch` и **не вызвали `DB::rollBack()`**, в PG транзакция остаётся в aborted state и все следующие запросы упадут.

**Правило:** используйте `DB::transaction(callable)` и не глотайте исключения внутри без явного `rollBack()`. При retry для deadlock — `attempts: 3`.',
                'code_example' => '<?php
// ✅ Закрытая форма - Laravel сам ловит и rollback-ает (включая SAVEPOINT)
DB::transaction(function () {
    User::create([...]);
    Post::create([...]);
}, attempts: 3);

// ✅ Вложенные через DB::transaction - savepoint автоматически откатывается
DB::transaction(function () {
    try {
        DB::transaction(function () {
            User::create([...]);     // SAVEPOINT trans2
            throw new RuntimeException("oops"); // ROLLBACK TO SAVEPOINT trans2
        });
    } catch (RuntimeException) {
        // savepoint уже откачен Laravel-ом, можно продолжать
        Order::create([...]);        // в PG это сработает корректно
    }
});

// ❌ Ручной beginTransaction + проглоченное исключение - в PG ломается
DB::beginTransaction();
try {
    User::query()->insert([...invalid...]);  // SQL error
} catch (Throwable $e) {
    Log::error($e); // НЕ вызвали rollBack!
}
User::create([...]); // ⚠️ PG: "current transaction is aborted"

// ✅ Правильно: всегда rollBack после catch при ручном управлении
DB::beginTransaction();
try {
    /* ... */
    DB::commit();
} catch (Throwable $e) {
    DB::rollBack();
    throw $e;
}

// Ловушка #1: откат внешней транзакции отменит "закоммиченную" внутреннюю
DB::transaction(function () use ($order) {
    DB::transaction(function () use ($order) {
        $order->update(["status" => "paid"]); // SAVEPOINT trans2
    }); // RELEASE SAVEPOINT trans2 - "закоммичено"

    throw new RuntimeException("fail"); // откатит ВСЁ, включая update выше
});

// Поэтому события - через afterCommit
DB::transaction(function () use ($user) {
    $user->save();
    DB::afterCommit(fn () => Mail::send(new WelcomeMail($user)));
    // отправится только после реального коммита самой ВНЕШНЕЙ транзакции
});',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.eloquent_basics',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое custom cast и чем он отличается от accessor/mutator?',
                'answer' => '**Два способа преобразовывать атрибуты модели — но с разной семантикой.**

| Параметр | **Accessor/Mutator** | **Custom Cast** (`CastsAttributes`) |
|---|---|---|
| Где описан | Метод **на самой модели** (`name(): Attribute`) | **Отдельный класс**, подключается в `$casts` |
| Переиспользование | Дублируется в каждой модели | Один класс → подключаем на любые модели |
| Состояние | Привязан к одной модели | Чистый — данные приходят как аргументы |
| Multi-attribute (one logical field → multiple columns) | **Нет** | **Да** — `set()` возвращает массив |
| Кеширование результата | `Attribute::make()->shouldCache()` | По умолчанию кешируется в `$model->classCastCache[$key]` |
| Связь с DDD/Value Object | Слабая | **Сильная** — `Castable` интерфейс на Value Object |
| Параметризация | Только closure capture | `MoneyCast:USD` — `:параметр` в `$casts` |

**Когда выбирать accessor/mutator:**

- Просто `ucfirst($name)`, `strtolower($email)` — одноразовая логика на одной модели.
- Composite поле без отдельного типа — `full_name = first_name . \' \' . last_name`.
- Mutator-side эффект, привязанный к контексту модели.

**Когда выбирать custom cast:**

- Один и тот же тип на **5+ моделях** — `Money`, `Distance`, `Coordinates`.
- **DDD Value Object** с собственным поведением (методы `add()`, `convert()`, etc.).
- **Multi-attribute** (один логический атрибут → несколько колонок).
- Нужны **параметры**: `\'price\' => MoneyCast::class . \':USD\'`.

**Bonus — `Castable` на самом Value Object:**

- VO реализует `Castable::castUsing()` и возвращает свой Cast-класс.
- В `$casts` пишем просто `\'price\' => Money::class` — Laravel сам найдёт каст.
- Получается **внутренне-замкнутый VO** — он сам знает, как сериализоваться.

**Встроенные касты L9+:** `AsArrayObject`, `AsCollection`, `AsEncryptedCollection`, `AsEnumCollection`, `AsStringable`. Покрывают типовые задачи без своего кода.',
                'code_example' => '<?php
// === 1. Accessor/Mutator - простая логика на ОДНОЙ модели ===
class User extends Model {
    protected function name(): Attribute {
        return Attribute::make(
            get: fn ($value) => ucfirst($value),
            set: fn ($value) => strtolower($value),
        );
    }
}

// === 2. Custom cast - переиспользуемая логика, multi-attribute ===
final class MoneyCast implements CastsAttributes {
    public function __construct(private ?string $defaultCurrency = null) {}

    public function get($model, $key, $value, $attrs): Money {
        return new Money(
            (int)    $attrs["{$key}_amount"],
            (string) ($attrs["{$key}_currency"] ?? $this->defaultCurrency),
        );
    }

    public function set($model, $key, $value, $attrs): array {
        return [
            "{$key}_amount"   => $value->amount,
            "{$key}_currency" => $value->currency,
        ];
    }
}

class Product extends Model {
    protected $casts = [
        "price" => MoneyCast::class . ":USD", // параметр - дефолтная валюта
        "cost"  => MoneyCast::class,
    ];
}

// === 3. Castable - VO сам знает свой Cast ===
final class Money implements Castable {
    public function __construct(public int $amount, public string $currency) {}

    public static function castUsing(array $arguments): string {
        return MoneyCast::class;
    }

    public function add(Money $other): self { /* ... */ }
}

class Order extends Model {
    protected $casts = [
        "total" => Money::class, // короче, VO сам решает
    ];
}

// === 4. Встроенные касты L9+ ===
protected $casts = [
    "options"     => AsCollection::class,                       // Collection
    "settings"    => AsArrayObject::class,                      // ArrayObject (изменяемый)
    "secrets"     => AsEncryptedCollection::class,              // зашифрованный JSON
    "permissions" => AsEnumCollection::class . ":" . Permission::class,
    "bio"         => AsStringable::class,                       // Str-helper
];',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.eloquent_basics',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Атомарность firstOrCreate и upsert: где гонки и зачем UNIQUE-индекс?',
                'answer' => 'Все три метода **похожи по API**, но имеют **разную атомарность** и разные риски race condition.

| Метод | Запросов | Атомарен на уровне БД? | Защита от race |
|---|---|---|---|
| **`firstOrCreate`** | `SELECT` + `INSERT` | **Нет** — окно гонки между ними | **UNIQUE-индекс** + retry |
| **`updateOrCreate`** | `SELECT` + `UPDATE`/`INSERT` | **Нет** — даже шире окно | **UNIQUE-индекс** или `lockForUpdate` |
| **`upsert`** | Один SQL: `INSERT ... ON DUPLICATE KEY UPDATE` (MySQL) / `ON CONFLICT DO UPDATE` (PG) | **Да** | **Требует** UNIQUE/PK |

**Race condition в `firstOrCreate`:**

1. T1: `SELECT * FROM users WHERE email = ?` → пусто.
2. T2: то же → пусто.
3. T1: `INSERT INTO users (email, ...)` → ✅.
4. T2: `INSERT INTO users (email, ...)` → **дубликат**.

**Защита — UNIQUE-индекс на колонках поиска:**

- Без индекса — дубликаты в БД.
- С индексом — второй `INSERT` упадёт с **`23000`/`23505`**, Laravel ловит `QueryException` и **перевыполняет SELECT** (в современных версиях `firstOrCreate` это делает автоматически).

**`updateOrCreate` — расширенная гонка:**

- К окну `SELECT + INSERT` добавляется параллельный `UPDATE`.
- Два процесса могут сделать UPDATE поверх друг друга — **lost update**.
- Решение: либо UNIQUE + retry (как выше), либо **`lockForUpdate()` внутри `DB::transaction`**.

**`upsert` — атомарность из коробки:**

- Один SQL — `INSERT ... ON CONFLICT (email) DO UPDATE SET name = EXCLUDED.name`.
- **Обязательно** UNIQUE/PK на колонках из `uniqueBy` — иначе UPDATE не сработает, БД сделает INSERT.
- **Не триггерит** events/observers/мутаторы — обновления Scout, broadcasting нужно вручную.
- `created_at` — только для новых строк, `updated_at` — для всех (для Eloquent-метода).

**Правило выбора:**

- **Одиночные кейсы** (регистрация юзера, идемпотентная подписка) — `firstOrCreate`/`updateOrCreate` + UNIQUE-индекс.
- **Импорты, массовые операции** — `upsert` (один SQL вместо N) — но события не триггерятся.
- **Read-modify-write в банковском стиле** — `DB::transaction` + `lockForUpdate`, не `firstOrCreate`.',
                'code_example' => '<?php
User::upsert(
    [["email" => "a@b", "name" => "A"], ["email" => "c@d", "name" => "C"]],
    uniqueBy: ["email"],
    update:   ["name"],
);',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.eloquent_basics',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Чем ULID лучше UUID v4 в качестве первичного ключа?',
                'answer' => '**Проблема `UUID v4` как PK** — 128 случайных бит. В B-tree индексе новые ключи попадают в **произвольные позиции** → проблемы:

- **Page splits** — каждая вставка может расщепить страницу индекса.
- **Cache miss** — холодные страницы вытесняют горячие из buffer pool.
- **Фрагментация** — индекс растёт быстрее данных.
- **INSERT-производительность падает** в 2-5x под нагрузкой против `bigint AUTO_INCREMENT`.

**`ULID` (Universally Unique Lexicographically Sortable Identifier):**

- Те же **128 бит**, но **первые 48** — timestamp в **миллисекундах**, последние **80** — случайные.
- **Лексикографически сортируется по времени создания** (и численно).
- Новые записи идут в **«правый край»** B-tree → как обычный AUTO_INCREMENT, без фрагментации.

**Сравнение:**

| Параметр | **`UUID v4`** | **`ULID`** | **`UUID v7`** (RFC 9562) |
|---|---|---|---|
| Размер | 128 бит | 128 бит | 128 бит |
| Текст | 36 символов (с `-`) | **26 символов** (Crockford Base32) | 36 символов |
| Сортируемость | Нет | **Да** | **Да** |
| Совместимость с `UUID`-колонкой | Да | Нет (своя колонка `CHAR(26)`) | Да |
| Стандарт | RFC 4122 | de-facto | **RFC 9562** (2024) |

**Бонусы ULID:**

- Можно **сортировать по PK** вместо `created_at` — быстрее.
- Компактнее в URL и логах.
- **Crockford Base32** — без неоднозначных символов (`I`/`l`, `O`/`0`).

**Минусы:**

- **Утечка времени создания** через ID. **Не использовать** в публичных URL для чувствительных ресурсов (медкарты, финансы).
- Колонка типа `CHAR(26)` — не стандартная `UUID` (но `BINARY(16)` тоже можно).

**В Laravel 11+:**

- **`HasUlids`** trait — заменяет PK с `id` на ULID.
- **`$table->ulid(\'id\')->primary()`** в миграциях.
- **`Str::ulid()`** — сгенерировать.
- **`Str::uuid7()`** — альтернатива по стандарту.

**Когда выбирать что:**

- **Внутренние таблицы под нагрузкой** — ULID или UUID v7.
- **Публичные ID** (для чужих API) — UUID v4 (без утечки времени).
- **Малая нагрузка** — любой подойдёт.',
                'code_example' => '<?php
use Illuminate\\Database\\Eloquent\\Concerns\\HasUlids;

class Order extends Model
{
    use HasUlids; // вместо HasUuids

    // primary key теперь string CHAR(26), сортируемый по времени
}

// миграция
Schema::create("orders", function (Blueprint $table) {
    $table->ulid("id")->primary(); // вместо $table->uuid()->primary()
    $table->timestamps();
});

// результат: 01HRZ8K3M9... - первые символы растут со временем
// → новые ID идут в конец B-tree, без фрагментации',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.eloquent_basics',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Чем опасен DB::raw() и как делать безопасные подстановки в raw-выражения?',
                'answer' => '**`DB::raw()`** и обёртки **`selectRaw`/`whereRaw`/`orderByRaw`/`havingRaw`/`groupByRaw`** **вставляют строку прямо в SQL без экранирования**. Если в строке оказались данные пользователя — это **SQL-injection**.

**Классический антипаттерн:**

```php
->whereRaw("status = \'{$request->status}\'")
// → status = \'\' OR 1=1; --\'
```

**Правильный способ — второй аргумент с bindings:**

- Bindings проходят через **PDO-плейсхолдеры (`?`)** и экранируются драйвером БД.
- У `selectRaw`/`whereRaw`/`orderByRaw`/`havingRaw` — **одинаковый** второй аргумент `[...]`.

```php
->whereRaw(\'status = ?\', [$request->status])
```

**Что НЕЛЬЗЯ передать через bindings:**

| Что | Почему | Чем заменить |
|---|---|---|
| Имя колонки | Часть синтаксиса, не значение | **Whitelist** через `in_array($col, $allowed, true)` |
| Направление сортировки | То же | Whitelist (`asc`/`desc`) |
| Имя таблицы | То же | Whitelist |
| Идентификатор оператора (`=`, `<>`, `LIKE`) | То же | Whitelist |

**Защита для динамической сортировки:**

```php
$allowed = [\'id\', \'created_at\', \'name\'];
$column  = in_array($req->sort, $allowed, true) ? $req->sort : \'id\';
$dir     = $req->direction === \'desc\' ? \'desc\' : \'asc\';
User::orderBy($column, $dir)->get();
```

**Безопасные альтернативы `whereRaw`:**

- **Огромный список чисел** — `whereIntegerInRaw($col, $array)`:
  - Laravel приводит каждый элемент к `(int)` и склеивает строку **без bindings**.
  - Спасает от **лимита PDO ~65k плейсхолдеров**.
  - **Только** для целых чисел и **только** одиночных колонок.
- **Список строк/UUID** — `whereIn` + chunk: `collect($emails)->chunk(1000)->each(fn ($c) => User::whereIn(\'email\', $c->all())->get())`.
- **Schema-aware проверка колонки** — `Schema::hasColumn(\'users\', $col)` перед использованием.

**Особенно опасные методы:**

- **`DB::statement($userInput)`** — нет bindings в принципе. **Никогда** не пускать туда пользовательские данные.
- **`DB::unprepared($sql)`** — тем более.
- **`DB::raw($value)`** **внутри** Eloquent — `User::create([\'created_at\' => DB::raw($input)])` тоже инъекция.

**Правило ревью:** найди в коде любой `Raw` и проверь, что **все** интерполяции уехали во **второй аргумент**.',
                'code_example' => '<?php
// УЯЗВИМО - SQL-injection
DB::table("users")
    ->whereRaw("email = \'" . request("email") . "\'")
    ->get();

DB::table("users")
    ->orderByRaw(request("sort")) // "; DROP TABLE users;--"
    ->get();

// ПРАВИЛЬНО - bindings через ?
DB::table("orders")
    ->selectRaw("price * ? as price_with_tax", [1.0825])
    ->whereRaw("price > IF(state = ?, ?, ?)", ["TX", 200, 100])
    ->get();

// Динамическая колонка - whitelist, не bindings
$allowed = ["id", "created_at", "name"];
$column  = in_array(request("sort"), $allowed, true) ? request("sort") : "id";
$direction = request("direction") === "desc" ? "desc" : "asc";
User::orderBy($column, $direction)->get();

// Безопасный путь для массива чисел
User::whereIntegerInRaw("id", $userIds)->get();',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.eloquent_basics',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Почему $model->save() может ТИХО вернуть false и в коде "ничего не сохранилось"?',
                'answer' => '**Малоизвестная боль Eloquent:** `save()` возвращает **`bool`**. В happy-path — `true`. Но **возвращает `false` БЕЗ исключения**, если какой-то подписчик событий `saving`/`creating`/`updating`/`deleting` **вернул `false`**.

**Механика — `fireModelEvent`:**

- На каждом этапе lifecycle модель вызывает `fireModelEvent($event, $halt = true)`.
- `false` из **любого** подписчика = **veto**, операция отменяется.
- `save()` ловит этот результат и возвращает `false`.
- **Никакого исключения** не бросается — это «нормальное» поведение по контракту.

**Симптом в проде:**

- Разработчик пишет `$user->save()` без проверки результата.
- Объект «как будто сохранился» (никаких ошибок в логе), но в БД ничего нет.
- Юзер жалуется через два дня — баг ищут полдня.

**Кто чаще всего возвращает `false`:**

| Источник | Пример |
|---|---|
| **Observer/Listener без явного `return`** | `void` метод вернёт `null`, `fireModelEvent` приведёт к `false` (зависит от версии) |
| **Условная валидация в Observer** | `if ($u->balance < 0) return false;` — «не сохранять, если баланс отрицательный» |
| **Глобальный listener от пакета** | `audit-log`, `activity` — отказался писать какой-то тип |
| **Bool-возвращающие методы** | Метод модели вызвал `false` где-то по пути |

**Те же грабли с `delete()`:**

- `deleting` event возвращает `false` → `$model->delete()` тоже вернёт `false` молча.

**Решения:**

| Решение | Когда |
|---|---|
| **`$user->saveOrFail()`** / **`$user->deleteOrFail()`** | **Канон** — бросают `ModelNotSavedException`, оборачивают в транзакцию |
| **`if (! $user->save()) throw ...`** | Ручная проверка — там, где `OrFail` не подходит |
| **`Model::shouldBeStrict()`** в dev | Падает на других связанных ловушках, но не на этой конкретно |
| **В Observer — без `return false`** | Бросать исключение (`InvalidStateException`) вместо тихого veto |
| **`: void` тип на Observer-методах** | Гарантия, что случайный `return ...` не вернёт false |

**Что не помогает:**

- **`try/catch`** — нет исключения.
- **`Model::preventSilentlyDiscardingAttributes`** — это про массовое присваивание.

**Правило ревью:**

- `$model->save()` без проверки результата — **code smell**.
- Если есть Observer/listener на `saving`/`creating`/`updating`/`deleting` — проверить, что `return false` либо не используется, либо обработан вызывающей стороной.',
                'code_example' => '<?php
// ❌ Тихий баг
class UserObserver {
    public function saving(User $user) {
        if ($user->balance < 0) {
            return false; // veto - save() вернёт false
        }
        // забыли явный return - в некоторых случаях даст null/void
    }
}

// Где-то в контроллере - без проверки
$user->balance = -100;
$user->save();         // false, но никто не узнает
return response()->json($user); // пользователь видит "успех", в БД ничего

// ✅ Гарантия исключения
$user->balance = -100;
$user->saveOrFail();   // ModelNotSavedException

// ✅ Или ручная проверка
if (! $user->save()) {
    throw new \RuntimeException("Не удалось сохранить пользователя (veto observer-а)");
}

// ✅ Observer без случайного false
class UserObserver {
    public function saving(User $user): void {
        if ($user->balance < 0) {
            throw new InvalidStateException("balance < 0"); // явно, а не false
        }
        // void возврат из метода с : void - safe
    }
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.eloquent_basics',
            ],
            [
                'category' => 'Laravel',
                'question' => 'В чём разница между ORM и сырыми SQL-запросами?',
                'answer' => '**ORM (Object-Relational Mapper)** — слой, маппящий строки таблиц на объекты PHP и дающий работать с БД через объектный API:

```php
$user = User::find(1);
$user->name = \'Иван\';
$user->save();
```

Eloquent (Laravel), Doctrine (Symfony), Yii AR — всё это ORM.

**Сырой SQL** — текст запроса напрямую через `PDO`/`DB::select`/`DB::statement`, который ORM **не интерпретирует**.

**Ключевые отличия:**

| Параметр | **ORM (Eloquent)** | **Сырой SQL** |
|---|---|---|
| Уровень абстракции | Прячет SQL, диалект, кавычки, экранирование | Полный контроль над запросом |
| Кросс-СУБД | Генерирует SQL под текущий драйвер | При смене БД переписывать |
| Безопасность | Bindings из коробки | Легко уронить в SQL-injection при конкатенации |
| Производительность | Hydration в объекты, события, lazy load → N+1 | Близко к нулевому overhead |
| Сложный SQL | Оконные функции, CTE, hint-ы — неудобно | Естественный путь |
| Поддержка моделей | Связи, события, soft delete, casts, accessors | Возвращает `stdClass`/`array` |
| Тестируемость | Легко мокать модели, factory | Truncate + INSERT в setUp |

**Когда что:** для CRUD и доменной логики — ORM. Для аналитики, отчётов, миграций данных и сложных оптимизаций — сырой SQL или Query Builder без модели.',
                'difficulty' => 3,
                'topic' => 'laravel.eloquent_basics',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Когда выгодно использовать ORM, а когда — сырые SQL-запросы?',
                'answer' => 'Выбор между ORM, Query Builder и сырым SQL — **прагматичный**: разные инструменты для разных задач.

**Используй ORM (Eloquent), когда:**

- **CRUD-код приложения** — 90% запросов это `find/where/save/delete`, ORM даёт большой выигрыш в скорости разработки.
- **Доменная логика** крутится вокруг моделей со связями, событиями, валидацией.
- **Команда большая** — ORM дисциплинирует и единообразит стиль.
- **Нужны Resources/API** — Eloquent отлично интегрируется с API Resources, Sanctum, policies.
- **Безопасность из коробки** — bindings.

**Переходи на raw (`DB::select`, `DB::statement`, `DB::raw`) когда:**

- **Тяжёлая аналитика** — оконные функции, рекурсивные CTE, сложные `GROUP BY` с агрегатами.
- **Bulk-операции** — UPDATE/DELETE миллионов строк, `COPY`/`LOAD DATA INFILE`. ORM-events на каждую запись положат прод.
- **Узкие места после профилирования** — нужны index hint-ы, конкретный план запроса.
- **Миграции, ETL, отчёты, разовые скрипты** — где модели только мешают.
- **Специфичные фичи СУБД**: PG `jsonb @>`, GIN-индексы; MySQL `SQL_CALC_FOUND_ROWS`, `FULLTEXT MATCH AGAINST`.

**Золотая середина — Query Builder без модели:**

```php
DB::table(\'users\')->where(\'active\', true)->get();
```

Синтаксис ORM-стиля, **без overhead на hydration** модели. Часто лучший выбор для read-heavy кода и больших выборок.

**Правило:** Eloquent — это **инструмент**, а не **догма**. В одном проекте могут спокойно жить и Eloquent в контроллерах, и raw SQL в репортах.',
                'difficulty' => 3,
                'topic' => 'laravel.eloquent_basics',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое $table и $primaryKey в Eloquent-модели?',
                'answer' => 'Свойства модели для переопределения соглашений по именованию:

- `$table` — имя таблицы, если отличается от автогенерации (по умолчанию `snake_case` + множественное: `User` → `users`).
- `$primaryKey` — имя поля PK, если не `id`.
- `$incrementing = false` — если PK не AUTO_INCREMENT.
- `$keyType = \'string\'` — если PK строка (например, UUID).',
                'code_example' => 'class Article extends Model
{
    protected $table = \'blog_articles\'; // иначе было бы articles
    protected $primaryKey = \'uuid\';
    public $incrementing = false;        // PK не AUTO_INCREMENT
    protected $keyType = \'string\';       // PK - строка (UUID)
}',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'laravel.eloquent_basics',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как отключить timestamps у модели Eloquent?',
                'answer' => 'По умолчанию Eloquent ожидает колонки **`created_at`** и **`updated_at`** и сам заполняет их при `save()`/`update()`.

Варианты настройки:

- **Полностью отключить** — `public $timestamps = false;` на модели.
- **Поменять имена колонок** — константы `CREATED_AT` / `UPDATED_AT`.
- **Поменять формат хранения** — `protected $dateFormat = \'U\';` (например, unix timestamp).
- **Разово не трогать `updated_at`** — `$model->timestamps = false;` перед `save()`.
- **`updateQuietly([...])`** — апдейт **без событий модели** (`saving`/`saved`/`updating`/`updated`), Observer не сработает.

Миграция: одной строкой `$table->timestamps()` создаёт обе колонки `TIMESTAMP NULLABLE`.',
                'code_example' => 'class Post extends Model
{
    public $timestamps = false; // совсем нет created_at/updated_at
}

// или переименовать
class Post extends Model
{
    const CREATED_AT = \'created\';
    const UPDATED_AT = \'modified\';
}

// разово - сохранить, не трогая updated_at
$post->timestamps = false;
$post->save();',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.eloquent_basics',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как создать модель Laravel через artisan?',
                'answer' => 'Команда `php artisan make:model` создаёт модель в `app/Models`. Опции — флаги для попутной генерации связанных файлов:

- `-m` — миграция.
- `-f` — фабрика (для тестов и сидеров).
- `-s` — сидер.
- `-c` — контроллер.
- `-r` — resource-контроллер (с методами `index/show/create/store/edit/update/destroy`).
- `--pivot` — для pivot-моделей `belongsToMany`.

Чаще всего пишут `make:model Post -mfsc` — модель + миграция + фабрика + сидер + контроллер одной командой.',
                'code_example' => '# только модель
php artisan make:model User

# модель + миграция
php artisan make:model Post -m

# модель + миграция + фабрика + сидер + контроллер
php artisan make:model Post -mfsc

# модель + resource-контроллер (с index/show/create/store/edit/update/destroy)
php artisan make:model Post -mr',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'laravel.eloquent_basics',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как Eloquent определяет имя таблицы для модели?',
                'answer' => 'По умолчанию: имя класса переводится в `snake_case` + множественное число.

- `Post` → `posts`
- `OrderItem` → `order_items`
- `User` → `users`

Множественное число выбирает `Str::plural()` — учитывает английские правила: `child` → `children`, `person` → `people`. Если нужно другое имя (или таблица не на английском) — задай `protected $table = \'my_table\'` явно.',
                'code_example' => 'class Post extends Model {} // → posts
class OrderItem extends Model {} // → order_items

class News extends Model {
    protected $table = \'news\'; // не "news" автоматически, лучше явно
}',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'laravel.eloquent_basics',
            ],
            [
                'category' => 'Laravel',
                'question' => 'В чём разница между find() и findOrFail()?',
                'answer' => 'Оба метода ищут запись по первичному ключу.

- `User::find(5)` — вернёт модель или `null`, если запись не найдена.
- `User::findOrFail(5)` — вернёт модель или бросит `ModelNotFoundException`, который Laravel автоматически превращает в **HTTP 404**.

В контроллерах почти всегда используют `findOrFail()`, чтобы не писать `if (!$user) abort(404)`. `find()` удобен, когда `null` — валидный сценарий (например, проверка существования).',
                'code_example' => '// Так писать не нужно
$user = User::find($id);
if (! $user) {
    abort(404);
}

// Достаточно
$user = User::findOrFail($id); // 404 если не найден

// findOrFail с массивом id - бросит, если найдено меньше, чем запрошено
$users = User::findOrFail([1, 2, 3]);',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'laravel.eloquent_basics',
            ],
        ];
    }
}
