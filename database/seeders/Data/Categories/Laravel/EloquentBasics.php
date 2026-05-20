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
                'answer' => 'create() - создать запись из массива. save() - сохранить экземпляр. update() - обновить. delete() - удалить. find($id), findOrFail($id), first(), firstOrFail(), all(), get(). fresh() - получить актуальную копию из БД (новый экземпляр). refresh() - обновить ТЕКУЩИЙ экземпляр данными из БД.',
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
                'answer' => 'Mass assignment - это создание/обновление модели массивом данных (User::create($input)). Это опасно: пользователь может подсунуть лишние поля (например is_admin). Поэтому Laravel требует явно указать разрешённые поля через $fillable (whitelist) или запрещённые через $guarded (blacklist). Технически можно объявить оба свойства, но при конфликте $fillable имеет приоритет, и $guarded фактически игнорируется - поэтому на практике используют что-то одно.',
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
                'answer' => 'Casts - это автоматическое преобразование атрибутов модели при чтении/записи. Например, поле в БД хранится как JSON-строка, а в коде вы работаете с массивом. Стандартные касты: int, bool, array, json, datetime, decimal:2, encrypted, AsArrayObject, AsCollection.',
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
                'answer' => 'Cast реализует CastsAttributes и в set() возвращает массив с несколькими ключами - Laravel запишет каждый ключ в свою колонку. В get() читаются те же колонки из $attributes по префиксу $key. Это позволяет хранить Value Object вроде Money в нескольких физических колонках, а в коде работать с ним как с одним свойством модели. В $casts ключ совпадает с префиксом колонок.',
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
                'answer' => 'Accessor - это метод, который преобразует значение при ЧТЕНИИ атрибута. Mutator - при ЗАПИСИ. Старый синтаксис: getNameAttribute / setNameAttribute. Новый синтаксис (Laravel 9+): метод возвращает Attribute с get и set.',
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
                'answer' => 'firstOrCreate - найти запись по условию или создать новую (если нет). updateOrCreate - найти и обновить, либо создать. Оба работают по 1 строке и срабатывают события модели. upsert - массовая операция: вставить/обновить много записей одним запросом, БЕЗ событий моделей.',
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
                'answer' => 'Query Builder - это инструмент для построения SQL-запросов через PHP-методы, не привязанный к моделям. Простыми словами: альтернатива Eloquent для случаев, когда не нужна модель, или для тяжёлых SQL.',
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
                'answer' => 'Через методы join (INNER), leftJoin, rightJoin, crossJoin. Можно передавать closure для сложных условий. В Eloquent тоже работает.',
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
                'answer' => 'DB::raw() для сырого SQL внутри select/where. selectRaw, whereRaw, orderByRaw, havingRaw - для удобства. ВАЖНО: при использовании raw нельзя подставлять данные пользователя без bindings - это SQL-injection.',
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
                'answer' => 'DB::transaction оборачивает код в транзакцию: если внутри callback бросается исключение - rollback, иначе - commit. Поддерживаются deadlock-retries (второй аргумент). Альтернатива: DB::beginTransaction, DB::commit, DB::rollBack вручную. Важный нюанс про вложенные транзакции: в MySQL/Postgres настоящих nested transactions НЕ существует, Laravel эмулирует их через SAVEPOINT. Из этого вытекает несколько ловушек. 1) Если ВНЕШНЯЯ транзакция откатится, откатятся и все ранее "успешно закоммиченные" внутренние - они были лишь RELEASE SAVEPOINT, не самостоятельными коммитами. Поэтому события/уведомления, которые должны сработать только после фактического коммита, оборачивают в DB::afterCommit() или используют свойство $afterCommit. 2) PostgreSQL-специфика: после ЛЮБОЙ ошибки SQL внутри транзакции она переходит в состояние "current transaction is aborted" - все следующие запросы возвращают "current transaction is aborted, commands ignored until end of transaction block", пока не сделать ROLLBACK или ROLLBACK TO SAVEPOINT. Хорошая новость: DB::transaction(callable) АВТОМАТИЧЕСКИ ловит исключение, вызывает $this->rollBack() (= ROLLBACK TO SAVEPOINT для вложенных) и пробрасывает исключение наружу - выловив его во внешнем callback, можно безопасно продолжать (savepoint уже откачен). 3) Опасный паттерн возникает при РУЧНОМ DB::beginTransaction: если вы поймали исключение через try/catch и НЕ вызвали DB::rollBack() сами, в PG транзакция остаётся в aborted state, и все следующие запросы упадут. То же самое если ловить исключение НА УРОВНЕ savepoint, но не в обёртке DB::transaction. Правило: используйте DB::transaction(callable) и не глотайте исключения внутри без явного rollBack.',
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
                'answer' => 'Accessor/mutator - методы getXAttribute/setXAttribute на одной модели, дублируются между моделями. Custom cast (CastsAttributes) - отдельный класс, инкапсулирует пару get/set, переиспользуется на любых моделях. Поддерживает Castable-интерфейс на value object (Money::castUsing()), что даёт чистую интеграцию с DDD. Также есть AsCollection, AsEncryptedCollection, AsArrayObject из коробки.',
                'code_example' => '<?php
final class MoneyCast implements CastsAttributes {
    public function get($model, $key, $value, $attrs) {
        return new Money((int) $attrs["{$key}_amount"], $attrs["{$key}_currency"]);
    }
    public function set($model, $key, $value, $attrs) {
        return ["{$key}_amount" => $value->amount, "{$key}_currency" => $value->currency];
    }
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.eloquent_basics',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Атомарность firstOrCreate и upsert: где гонки и зачем UNIQUE-индекс?',
                'answer' => 'firstOrCreate выполняет два запроса: SELECT по атрибутам, и если не нашёл - INSERT. Между ними окно гонки: два параллельных воркера могут одновременно увидеть "нет записи" и оба сделать INSERT - в результате две строки, дубликат. Защита - UNIQUE-индекс на колонках поиска: второй INSERT упадёт с 23000/23505, и Laravel перевыполнит SELECT (в современных версиях firstOrCreate ловит QueryException и делает retry). updateOrCreate имеет ту же гонку, плюс race на самом UPDATE при параллельных вызовах - нужен либо lockForUpdate в транзакции, либо UNIQUE-индекс. upsert делает массовый INSERT ... ON DUPLICATE KEY UPDATE (MySQL) / ON CONFLICT DO UPDATE (Postgres) - атомарен на уровне БД, обходит каждую строку без N запросов и ВСЕГДА требует UNIQUE/PRIMARY KEY на колонках из uniqueBy. Правило: для импортов - upsert; для одиночных кейсов - firstOrCreate/updateOrCreate с UNIQUE-индексом для подстраховки.',
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
                'answer' => 'UUID v4 - случайные 128 бит. При вставке в B-tree индекс новые ключи попадают в произвольные места дерева - страдает кеш страниц БД, индекс фрагментируется, растёт число page splits и тормозят INSERT при высокой нагрузке. ULID (Universally Unique Lexicographically Sortable Identifier) - те же 128 бит, но первые 48 бит - timestamp в миллисекундах, последние 80 - случайные. Из-за timestamp-префикса ULID лексикографически (и численно) сортируется по времени создания, поэтому новые записи идут в "правый край" B-tree, как обычный auto-increment - индекс не фрагментируется, INSERT-производительность близка к bigint PK. Бонусы: можно сортировать по PK вместо created_at, текстовое представление компактнее (26 символов против 36). Минус: примерное время создания записи утекает через ID, поэтому не использовать в публичных URL для чувствительных ресурсов. В Laravel есть HasUlids trait + helper $table->ulid() в миграциях. Альтернатива - UUID v7 (тот же подход с timestamp-префиксом, стандартизирован в RFC 9562) - в Laravel 11+ доступен через Str::uuid7().',
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
                'answer' => 'DB::raw() (и его обёртки selectRaw, whereRaw, orderByRaw, havingRaw) вставляет переданную строку прямо в SQL без экранирования - это окно для SQL-injection, если в строке оказались данные пользователя. Классический антипаттерн: ->whereRaw("status = \'{$request->status}\'"). Правильный способ - использовать ВТОРОЙ аргумент с массивом bindings, который проходит через PDO-плейсхолдеры (?) и экранируется драйвером БД: ->whereRaw("status = ?", [$request->status]). У selectRaw, orderByRaw, havingRaw - такой же второй аргумент. Если динамическим является имя столбца или направление сортировки (которые НЕЛЬЗЯ передать через bindings - это часть синтаксиса, а не значение), нужно жёстко валидировать вход через whitelist (in_array($column, $allowed, true)), иначе пользователь сможет передать "; DROP TABLE users;--". Безопасные альтернативы: для огромных списков чисел - whereIntegerInRaw($col, $array) (Laravel приводит каждый элемент к int через (int)$value и склеивает строку без bindings - спасает от лимита PDO в ~65k плейсхолдеров и ускоряет запрос; работает ТОЛЬКО с целыми числами и ТОЛЬКО для одиночных колонок - не подходит для составных ключей и строк, для них whereIn остаётся единственным безопасным вариантом); для динамических колонок - Schema::hasColumn() + whitelist. Также избегайте DB::statement($userInput) - там вообще нет bindings.',
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
                'answer' => 'Малоизвестная боль Eloquent: save() возвращает bool. В happy-path - true (запись создана/обновлена). НО save() возвращает false БЕЗ ИСКЛЮЧЕНИЯ, если любой из listener-ов событий saving / creating / updating вернул false. Это поведение fireModelEvent: false из любого подписчика = veto, операция отменяется. Аналогично для delete() - false из deleting отменяет удаление. Симптом в проде: разработчик пишет $user->save() и не проверяет результат - объект как будто сохранился (никаких ошибок), но в БД ничего не появилось. Чаще всего ловят: Observer/listener без явного return - fireModelEvent типизирует возврат как ?bool, и null трактуется как false, отменяя операцию; явный return false для условной валидации в Observer (например, "не сохранять, если у юзера баланс отрицательный"); глобальный saving handler от какого-нибудь пакета (audit-log, activity), который не хочет писать конкретный тип записи. Решения: 1) ВСЕГДА проверять результат save()/delete() - if (!$user->save()) throw new RuntimeException(); 2) Использовать saveOrFail()/deleteOrFail() - бросают исключение при false (внутри транзакции); 3) В Observer-ах не возвращать ничего (return; явно) или return true; 4) При код-ревью observer-ов - явно проверять, что в коде нет случайного return false из логирующей логики.',
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
                'answer' => 'ORM (Object-Relational Mapper) — это слой, который маппит строки таблиц на объекты PHP и даёт работать с БД через объектный API: $user = User::find(1); $user->name = "Иван"; $user->save(). Eloquent (Laravel), Doctrine (Symfony), Yii AR — всё это ORM. Сырой SQL — текст запроса напрямую через PDO/DB::select/DB::statement, который ORM не интерпретирует. Ключевые отличия: 1) Уровень абстракции — ORM прячет SQL, диалект, кавычки, экранирование; raw — полный контроль над запросом. 2) Кросс-СУБД — ORM генерирует SQL под текущий драйвер (MySQL/PG/SQLite), raw нужно переписать при смене БД. 3) Безопасность — ORM по умолчанию использует bindings, raw легко уронить в SQL-injection, если конкатенировать ввод. 4) Производительность — ORM добавляет накладные расходы (hydration в объекты, события модели, ленивая загрузка → N+1); raw близок к нулевому overhead. 5) Удобство сложного SQL — оконные функции, CTE с UNION, hint-ы оптимизатора, экзотические агрегаты проще написать на raw. 6) Поддержка моделей — ORM даёт связи (hasMany, belongsTo), события, soft delete, casts, accessors; raw возвращает stdClass/array, всё это придётся писать руками.',
                'difficulty' => 3,
                'topic' => 'laravel.eloquent_basics',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Когда выгодно использовать ORM, а когда — сырые SQL-запросы?',
                'answer' => 'Используй ORM (Eloquent), когда: 1) Обычный CRUD-код приложения — 90% запросов это find/where/save/delete, тут ORM даёт огромный выигрыш в скорости разработки и читаемости. 2) Доменная логика крутится вокруг моделей со связями, событиями, валидацией. 3) Команда большая и важна единообразность стиля — ORM дисциплинирует. 4) Нужны Resources/API — Eloquent отлично интегрируется с API Resources, Sanctum, policies. 5) Безопасность — bindings из коробки. Переходи на raw (DB::select, DB::statement, Query Builder с DB::raw) когда: 1) Тяжёлая аналитика — оконные функции, рекурсивные CTE, сложные GROUP BY с агрегатами, которые в ORM выглядят уродливо. 2) Bulk-операции — UPDATE/DELETE миллионов строк, COPY/LOAD DATA INFILE — ORM-events на каждую запись положат прод. 3) Узкие места по производительности после профилирования — конкретный hot path хочется иметь оптимальный план запроса с index hint-ами. 4) Миграции, ETL, отчёты, разовые скрипты — где модели только мешают. 5) Используешь специфичные фичи СУБД (PG: jsonb-операторы @>, GIN-индексы; MySQL: SQL_CALC_FOUND_ROWS; FULLTEXT MATCH AGAINST). Компромисс: Query Builder без модели ($db->table("users")->where(...)) — синтаксис ORM-стиля без overhead на hydration модели, часто лучшая золотая середина для read-heavy кода.',
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
                'answer' => 'По умолчанию Eloquent ожидает колонки created_at и updated_at и сам заполняет их при save()/update(). Чтобы отключить полностью - public $timestamps = false. Поменять имена колонок - константы CREATED_AT/UPDATED_AT. Формат хранения - $dateFormat. Разово сохранить без обновления updated_at - $model->timestamps = false перед save(), либо $model->updateQuietly([...]) (не триггерит и события модели).',
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
