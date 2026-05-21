<?php

namespace Database\Seeders\Data\Categories\Php;

class Php8Features
{
    public static function all(): array
    {
        return [
            [
                'category' => 'PHP',
                'question' => 'Что такое именованные аргументы (named arguments)?',
                'answer' => '**Named arguments** (PHP 8.0+) — передача аргументов **по имени параметра**, а не по позиции. Синтаксис: **`name: value`**.

**Зачем нужно:**
- много опциональных параметров — **не надо помнить порядок** и пропускать промежуточные `null, null, null`
- читаемость на вызове: видно, что значит каждый аргумент
- удобно для функций со многими булевыми флагами (`str_replace`, `preg_match`)

**Правила:**
- именованные **строго после** позиционных, смешивать можно
- имя должно совпадать с именем параметра — переименование параметра становится breaking change
- работает и в **атрибутах** (`#[Route(path: "/x", methods: ["GET"])]`)',
                'code_example' => '<?php
function createUser(
    string $name,
    int $age = 18,
    bool $isAdmin = false,
    string $role = "user",
) {}

// Старый способ - пришлось бы передавать всё
createUser("Иван", 30, false, "manager");

// Named arguments - только нужное
createUser(name: "Иван", role: "manager");

// Можно в любом порядке
createUser(role: "admin", name: "Аня");

// Смешанно
createUser("Петя", isAdmin: true);

// В сложных функциях очень помогает
str_replace(
    search: ["a", "b"],
    replace: ["1", "2"],
    subject: $text,
);',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работает match-выражение в PHP 8?',
                'answer' => '**`match`** (PHP 8.0+) — выражение для сопоставления значений, безопасная замена `switch`.

**Чем отличается от `switch`:**

| Свойство | `match` | `switch` |
|---|---|---|
| Сравнение | **строгое `===`** | нестрогое `==` |
| Возвращает значение | да (это **выражение**) | нет (стейтмент) |
| `break` | не нужен (нет fallthrough) | обязателен |
| Без совпадения | бросает **`UnhandledMatchError`** | тихо проходит мимо |
| Несколько значений в ветке | через запятую | падающие `case`-ы |

**Подводные камни:**
- **строгое `===`** — `match(1)` не совпадёт с веткой `"1"`
- **`default`** обязателен, если не покрыты все возможные значения, иначе `UnhandledMatchError`
- ветки `match` — **выражения**, нельзя выполнить блок из нескольких операторов (для этого вызывайте функцию)
- значение слева вычисляется **один раз**, в отличие от цепочки `if/elseif`',
                'code_example' => '<?php
$status = "active";

// match - выражение
$label = match($status) {
    "active", "online" => "Активен",
    "inactive" => "Неактивен",
    "banned" => "Заблокирован",
    default => "Неизвестно",
};

// switch требует break и не возвращает значение
switch ($status) {
    case "active":
    case "online":
        $label = "Активен";
        break;
    // ...
}

// match со строгим сравнением
$result = match(1) {
    "1" => "string",  // не совпадёт!
    1 => "int",       // совпадёт
};

// Без default - UnhandledMatchError при отсутствии
$x = match($y) { 1 => "a" };',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое enum в PHP 8.1?',
                'answer' => '**`enum`** (PHP 8.1+) — тип-перечисление с фиксированным набором значений. Заменяет константы класса там, где значение должно ограничиваться **закрытым множеством**.

**Два вида:**
- **Pure enum** — просто кейсы без скалярного значения (`case Active;`)
- **Backed enum** — каждый кейс привязан к **`int` или `string`** (`case Active = "active";`), удобно для БД и JSON

**Возможности:**
- кейс — **синглтон**, сравнение через `===` (или через `match`)
- может реализовывать **интерфейсы** и иметь методы (обычно через `match($this)`)
- статический метод `cases()` — все варианты
- у backed: **`from($v)`** бросает `ValueError`, **`tryFrom($v)`** возвращает `null`

**Ограничения:**
- нельзя `new Enum()` — только статический доступ
- **нет состояния** — свойства запрещены (поэтому методы должны полагаться только на `$this`)
- backed enum не наследуется, но может имплементить `BackedEnum`/`UnitEnum`',
                'code_example' => '<?php
// Pure enum
enum Status {
    case Active;
    case Inactive;
    case Banned;
}

$s = Status::Active;
var_dump($s === Status::Active); // true

// Backed enum
enum Role: string {
    case Admin = "admin";
    case User = "user";
    case Guest = "guest";

    public function label(): string {
        return match($this) {
            Role::Admin => "Администратор",
            Role::User => "Пользователь",
            Role::Guest => "Гость",
        };
    }
}

$role = Role::from("admin");          // Role::Admin
$role = Role::tryFrom("xxx");         // null
echo Role::Admin->value;              // "admin"
echo Role::Admin->label();            // "Администратор"
print_r(Role::cases());               // все варианты',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как PHP управляет памятью в долгоживущих CLI-процессах (демонах, воркерах) и как избежать утечек?',
                'answer' => 'В обычном fpm-цикле каждый запрос получает свежее состояние и память освобождается после завершения скрипта - можно почти не думать об утечках. В CLI-демонах (Laravel queue:work, Octane, Swoole, ReactPHP) процесс живёт часами/днями, и любой объект, который не отпустили, навсегда занимает память. PHP использует reference counting + mark-and-sweep для циклов: память освобождается, когда refcount = 0; циклические ссылки (A→B, B→A) собирает GC периодически (порог 10000 возможных корней либо вручную gc_collect_cycles()). Важный нюанс: даже после gc_collect_cycles() Zend Memory Manager удерживает память в виде арен и чанков - формально она "свободна" в PHP, но не возвращена в ОС. В долгоживущих процессах (Octane, RoadRunner, queue:work) для реального возврата памяти ОС вызывают gc_mem_caches() - очищает внутренние кеши Zend MM. Без этого RSS-процесса (видный в top/htop) только растёт. Типичные источники утечек: 1) Глобальные/статические массивы-кеши без TTL. 2) Eloquent-модели, удерживаемые в Job через свойства. 3) Singleton-сервисы с накапливающимся state. 4) Замыкания, захватывающие $this и удерживающие крупные объекты. Практика: queue:work --max-jobs=1000 --max-time=3600 - воркер сам перезапустится, освобождая всё; в Octane есть встроенный листенер Laravel\\Octane\\Listeners\\CollectGarbage, который вешается на OperationTerminated и вызывает gc_collect_cycles() при превышении порога memory_get_usage() > config("octane.garbage") (дефолт 50 МБ - именно мегабайты, не "запросов"). ВАЖНО: в стоковом config/octane.php этот листенер ЗАКОММЕНТИРОВАН - без раскомментирования автоматического GC между запросами не будет; gc_mem_caches() Octane не зовёт вообще, для реального возврата RSS в ОС его всё ещё надо вызывать руками или через свой листенер. Мониторинг: memory_get_usage(true) возвращает память, выделенную Zend MM из ОС (с учётом пула), false — только использованную; настоящий RSS процесса берётся из ОС (/proc/<pid>/status VmRSS).',
                'code_example' => '<?php
// Job, накапливающий утечку
class ProcessOrderJob
{
    private static array $cache = []; // ⚠️ статика живёт между jobs

    public function handle(int $orderId): void
    {
        self::$cache[$orderId] = Order::with("items")->find($orderId);
        // через 100k jobs - OOM
    }
}

// Грамотный воркер
// supervisor: php artisan queue:work --max-jobs=1000 --max-time=3600 --memory=256

// Принудительная очистка между jobs
gc_collect_cycles();   // собрать циклические ссылки
gc_mem_caches();       // вернуть кеши Zend MM в ОС - снижает RSS
DB::disconnect();

// Мониторинг
Log::info("memory", [
    "mb" => round(memory_get_usage(true) / 1024 / 1024, 1),
    "peak" => round(memory_get_peak_usage(true) / 1024 / 1024, 1),
]);',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое FFI (Foreign Function Interface) в PHP?',
                'answer' => 'FFI (расширение php-ffi, формально PHP 7.4, но активно используется в современных PHP 8.x-стэках) позволяет вызывать функции C-библиотек напрямую из PHP-кода без написания нативного PHP-расширения. Раньше для интеграции с libsodium, libcurl, libwebp нужно было писать .c-обёртку и компилировать в .so/.dll. С FFI достаточно загрузить заголовочный файл (или строку с C-объявлениями) и вызвать функции через объект FFI. Применение: интеграция с системными библиотеками без extension-разработки, прототипирование биндингов, доступ к нативным API ОС. FrankenPHP использует FFI/cgo для интеграции PHP-runtime с Go-сервером Caddy. Подводные камни: FFI медленнее обычного нативного расширения (overhead на маршалинг типов), небезопасен (типичные C-проблемы - segfault, undefined behavior), на проде нужно использовать opcache.preload для FFI - иначе FFI::cdef парсит .h-файл на каждом запросе. По умолчанию в production режим FFI ограничен через ffi.enable=preload: вне preload-скрипта FFI::cdef/FFI::load запрещены, но FFI::scope() и сами объекты, загруженные в preload, при этом доступны везде.',
                'code_example' => '<?php
// Вызов libc strlen
$ffi = FFI::cdef(
    "size_t strlen(const char *s);",
    "libc.so.6"
);
echo $ffi->strlen("hello"); // 5

// Своя .so
$lib = FFI::cdef(file_get_contents("mylib.h"), "./mylib.so");
$result = $lib->compute_hash("data");

// php.ini в проде
// ffi.enable=preload
// opcache.preload=/path/to/preload.php',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Дают ли Fibers настоящую многопоточность? Чем они отличаются от потоков ОС?',
                'answer' => 'НЕТ. Fibers (PHP 8.1) - это кооперативная многозадачность (concurrency), а не параллелизм (parallelism). Все fibers выполняются в ОДНОМ потоке ОС: только один fiber работает в любой момент времени, переключение происходит явно через Fiber::suspend()/resume() - не вытесняюще. Это значит: CPU-bound задачи в fibers не ускорятся (используется одно ядро), но IO-bound задачи могут эффективно совмещаться (пока один fiber ждёт сокет, шедулер запускает другой). Зачем их вообще добавили в PHP? Чтобы решить проблему "function colors" (Bob Nystrom, 2015), известную из JS/Python: когда любая async-операция требует, чтобы вся цепочка вызывающих функций тоже стала async (async/await заражает код). С Fibers async-runtime может приостанавливать стек ВНУТРИ синхронной по виду функции - например, обычный $pdo->query() умеет yield-ить fiber и пускать другие задачи, а сигнатура остаётся синхронной. Для пользователя код выглядит как обычный, асинхронность спрятана в реализации драйвера. Fibers - это примитив для написания async-runtimes (amphp v3, ReactPHP в новых версиях, RoadRunner, FrankenPHP), сами по себе они не делают код асинхронным, нужен event loop, который при IO переключает fiber. Для настоящего параллелизма (несколько ядер) в PHP используют: ext-parallel (отдельные потоки с shared-nothing), pcntl_fork (форк процессов), очереди (распределение задач на воркеры).',
                'code_example' => '<?php
$fiber = new Fiber(function (): void {
    echo "1 ";
    $value = Fiber::suspend("paused");
    echo "3 (получил: $value) ";
});

echo $fiber->start();   // "1 paused"
echo "2 ";
$fiber->resume("hi"); // "3 (получил: hi)"
// Вывод: "1 paused 2 3 (получил: hi) "

// Всё в ОДНОМ потоке - никакого параллелизма
// Для CPU-параллелизма:
// - ext-parallel: $runtime->run(fn() => heavyWork())
// - pcntl_fork()
// - очереди: dispatch(new HeavyJob)->onQueue("workers")',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Какие инструменты профилирования PHP-кода есть и в чём их различия?',
                'answer' => 'Профилирование - сбор статистики по горячим точкам в коде (CPU, память, IO). Главные инструменты в экосистеме PHP. 1) Xdebug Profiler - встроен в Xdebug. Включается через xdebug.mode=profile, генерирует Cachegrind-файлы (формат от valgrind), которые открываются в KCachegrind/qcachegrind/Webgrind или плагином PhpStorm. ПЛЮСЫ: бесплатно, локально, точные данные по каждому function call. МИНУСЫ: огромный overhead (10-100x замедление) - НЕ для прода, только локально на воспроизводимом сценарии; результирующий файл огромный. 2) Blackfire (платный, есть бесплатный tier) - SaaS-профайлер от создателей Symfony. Малый overhead (~5-10%), можно гонять на проде на части запросов. Web-UI с timeline, call-graph, сравнением профилей, performance тестами в CI. ПЛЮСЫ: production-ready, отличный UX, тесты регрессий. МИНУСЫ: платный, отправка данных в SaaS. 3) SPX (open source, бесплатный) - alternative от NoiseByNorthwest. Низкий overhead, web-UI как у Blackfire, можно использовать на проде. Установка: pecl install spx или собрать из исходников. Триггер через query-параметр или cookie - "запустить профилирование только этого запроса". Для personal/команды - оптимальный выбор. 4) Tideways - APM с профилированием, конкурент Blackfire. 5) Datadog/New Relic APM - не полноценные профайлеры, но дают агрегированную картину "какой endpoint медленный" в проде. Когда что использовать: локально на конкретный сценарий - Xdebug; CI-тесты регрессий и продовое выборочное - Blackfire/SPX; постоянный мониторинг - APM. Senior-приём: профилировать ПЕРЕД оптимизацией, иначе оптимизируется не то место. Правило 80/20: 80% времени в 20% кода - найти и оптимизировать именно их.',
                'code_example' => '<?php
// 1. Xdebug Profiler - локально
// php.ini:
//   zend_extension = xdebug
//   xdebug.mode = profile
//   xdebug.output_dir = /tmp/xdebug
//   xdebug.start_with_request = trigger
// curl "https://localhost/api/slow?XDEBUG_PROFILE=1"
// откроет /tmp/xdebug/cachegrind.out.<pid>
// открыть в KCachegrind / PhpStorm

// 2. SPX - быстрый production-ready профайлер
// php.ini:
//   extension = spx
//   spx.http_enabled = 1
//   spx.http_key = "secret"
//   spx.http_ip_whitelist = "10.0.0.0/8"
// curl -H "SPX_KEY: secret" -H "SPX_ENABLED: 1" https://app/slow
// открыть https://app/?SPX_UI_URI=/

// 3. Blackfire - в проде на части запросов
// blackfire run php artisan import:big
// или через расширение + вебхук в CI

// 4. Простейший inline-замер для проверки гипотезы
$t = hrtime(true);
$result = expensiveOperation();
$elapsedMs = (hrtime(true) - $t) / 1_000_000;
Log::info("expensive", ["ms" => $elapsedMs, "mem_mb" => memory_get_peak_usage(true) / 1024 / 1024]);

// Правило: ИЗМЕРЯЙ перед оптимизацией; угадывание узкого места почти всегда ошибается',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что делает атрибут #[\\Override] (PHP 8.3) и зачем он нужен?',
                'answer' => '**`#[\\Override]`** (PHP 8.3) — атрибут на методе, который проверяет в момент компиляции, что метод **действительно переопределяет** метод родителя или реализует метод интерфейса/абстрактного класса.

**Какую проблему решает:**
- родитель **переименовал/удалил** метод — наследник тихо превращается в обычный метод, полиморфизм ломается без ошибки
- **опечатка** в имени (`speack` вместо `speak`) — создаётся новый метод, родительский никогда не подменяется
- класс переопределяет метод **трейта**, который потом переименовали — orphaned-метод, который никто не вызовет

**Поведение без `#[\\Override]`:** PHP молча принимает «новый» метод и баг проявляется через `parent::method()` или в рантайме, иногда через недели.

**Поведение с `#[\\Override]`:** компилятор сразу бросает `Override of unknown method`.

**Ключевые свойства:**
- **compile-time only** — никакого overhead в рантайме
- работает и для **методов интерфейса** (наследник имплементит — добавляем `#[\\Override]`)
- **аналоги:** `@Override` в Java, `override` в C#/Kotlin/Swift/TypeScript

**Практика:** добавлять на **все** переопределяющие методы. PHPStan/Psalm и IDE добавляют автоматически. Особенно важен для методов с длинными именами (`handle`, `process`) и в крупных миграциях фреймворка.',
                'code_example' => '<?php
class Animal
{
    public function speak(): string { return "..."; }
}

class Dog extends Animal
{
    #[\\Override]
    public function speak(): string { return "Гав"; } // OK

    #[\\Override]
    public function speack(): string { return "..."; }
    // ⚠️ Compile error: Dog::speack() has #[\\Override] attribute,
    // but no matching parent method exists
}

// Без атрибута опечатка тихо создаёт новый метод
class DogBad extends Animal
{
    public function speack(): string { return "..."; } // новый метод!
}
$d = new DogBad();
echo $d->speak();  // "..." из Animal - молча сломалось

// Работает и с интерфейсами
interface Repository { public function find(int $id): ?Model; }

class UserRepo implements Repository
{
    #[\\Override]
    public function find(int $id): ?User { return /* ... */; }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Зачем нужна функция json_validate() (PHP 8.3)?',
                'answer' => '**`json_validate(string $json): bool`** (PHP 8.3) — проверяет, что строка является валидным JSON, **без создания PHP-структуры в памяти**.

**Зачем нужна:**

До 8.3 единственный способ — `json_decode` + `json_last_error()` (или `JSON_THROW_ON_ERROR` с `try/catch`). Но `json_decode` **аллоцирует** массив/объект под весь декодированный JSON. На больших payload-ах (мегабайты вебхуков, дампы) — лишний расход CPU и RAM, особенно в горячем пути и в долгоживущих воркерах.

**Что делает `json_validate`:**
- использует тот же парсер, что и `json_decode`, но **только парсит**, ничего не строит
- **O(N) по времени**, **O(1) по памяти**

**Аргументы:**
- `$depth` — макс. вложенность
- `$flags` — из публичных разрешён **только** `JSON_INVALID_UTF8_IGNORE`
- передача `JSON_THROW_ON_ERROR` валит `ValueError` (by design — функция возвращает `bool`, оборачивать в `try/catch` не надо)

**Применение:**
- rate-limit роутов с большим JSON-телом
- валидация **webhook**-ов до постановки в очередь
- фильтрация мусора на API gateway
- проверка структуры до тяжёлой записи

**Бенчмарк на 50 МБ JSON:** `json_decode` — ~120 МБ peak memory, `json_validate` — ~600 КБ.',
                'code_example' => '<?php
$payload = file_get_contents("php://input");

// ❌ До PHP 8.3 - аллоцируется вся структура
json_decode($payload);
if (json_last_error() !== JSON_ERROR_NONE) {
    return response("invalid json", 400);
}
// + дополнительный декод позже когда нужно реально использовать

// ✅ PHP 8.3+ - O(1) памяти
if (! json_validate($payload)) {
    return response("invalid json", 400);
}
// затем декодируем, когда уверены и реально нужен результат
$data = json_decode($payload, true);

// ❌ Так НЕ работает: json_validate запрещает JSON_THROW_ON_ERROR
// и кинет ValueError, а не JsonException - try/catch бессмысленен
// json_validate("invalid", flags: JSON_THROW_ON_ERROR);

// ✅ Правильный способ - проверять bool
if (! json_validate($payload)) {
    Log::warning("bad json", ["payload_size" => strlen($payload)]);
    return response("invalid json", 400);
}

// Бенчмарк: на 50 МБ JSON
// json_decode + last_error: ~120 МБ peak memory
// json_validate:            ~600 КБ peak memory',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Какие новые функции для массивов появились в PHP 8.4?',
                'answer' => 'PHP 8.4 добавил **четыре функции**, заполняющие давнюю дыру в stdlib. Все принимают `$array` и `$callback`.

| Функция | Возвращает | Аналог в JS |
|---|---|---|
| `array_find($arr, $cb)` | первое **значение**, где `$cb` вернул truthy, иначе `null` | `Array.find` |
| `array_find_key($arr, $cb)` | **ключ** первого совпадения, иначе `null` | — |
| `array_any($arr, $cb)` | `true`, если **хотя бы один** удовлетворяет | `Array.some` |
| `array_all($arr, $cb)` | `true`, если **все** удовлетворяют | `Array.every` |

**Главное преимущество — `early termination`:** останавливаются на первом совпадении (или первом несовпадении для `array_all`), **не проходят весь массив**.

**Поведение на пустом массиве:**
- `array_any([])` → `false`
- `array_all([])` → `true` (**vacuous truth** — для всех 0 элементов утверждение тривиально верно)

**До 8.4** для тех же задач писали:
- `array_filter()` — но проходит **весь** массив и **аллоцирует** промежуточный результат
- явный `foreach` с `break` — многословно

Это аналоги `Array.find/some/every` из JS и `anyMatch/allMatch/findFirst` из Java Streams.',
                'code_example' => '<?php
$users = [
    ["name" => "Иван", "active" => false],
    ["name" => "Аня",  "active" => true,  "admin" => true],
    ["name" => "Петя", "active" => true],
];

// PHP 8.4
$admin = array_find($users, fn($u) => $u["admin"] ?? false);
// ["name"=>"Аня", "active"=>true, "admin"=>true] или null

$adminIndex = array_find_key($users, fn($u) => $u["admin"] ?? false);
// 1 или null

$hasActive = array_any($users, fn($u) => $u["active"]);
// true

$allActive = array_all($users, fn($u) => $u["active"]);
// false (Иван неактивен)

// До PHP 8.4 - быстро, но многословно
$admin = null;
foreach ($users as $u) {
    if ($u["admin"] ?? false) { $admin = $u; break; }
}

// Антипаттерн через array_filter - проходит весь массив
$admin = array_filter($users, fn($u) => $u["admin"] ?? false);
$admin = $admin ? reset($admin) : null;',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что делает атрибут #[\\NoDiscard] в PHP 8.5?',
                'answer' => 'Атрибут #[\\NoDiscard] помечает функцию или метод, чьё возвращаемое значение нельзя игнорировать. Если результат вызова не используется, PHP выдаёт предупреждение, что полезно для функций валидации, иммутабельных операций и любых результатов, которые имеют смысл только при использовании.',
                'difficulty' => 4,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что даёт встроенное расширение URI в PHP 8.5?',
                'answer' => 'Расширение URI добавляет иммутабельные классы для парсинга и сборки URL по RFC 3986 и WHATWG, например Uri\\Rfc3986\\Uri и Uri\\WhatWg\\Url для WHATWG-семантики. В отличие от parse_url оно валидирует ввод, корректно обрабатывает кодирование и нормализацию и заменяет десятки сторонних библиотек.',
                'difficulty' => 4,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое типизированные константы классов в PHP 8.3?',
                'answer' => '**Типизированные константы** (PHP 8.3+) — у констант **класса, интерфейса, enum, trait** можно указывать тип: `const string ROLE = "admin"`.

**Зачем нужно:**
- тип проверяется при **объявлении** — нельзя положить значение неверного типа
- тип проверяется при **переопределении в наследнике** — нельзя сузить до несовместимого
- особенно полезно для **констант интерфейсов**: реализации больше не могут случайно положить туда что-то не то

**Допустимые типы:** скаляры (`int`, `string`, `float`, `bool`), `array`, `object`, конкретные классы, union/intersection, `nullable`, `mixed`. Запрещены `void`, `never`, `callable`.

**Правило ковариантности:** наследник может **уточнить** тип константы (расширить нельзя). Если у предка `string|int`, у потомка можно `string`, но не `string|int|null`.',
                'code_example' => '<?php
interface HasStatus {
    const string DEFAULT = "active";   // тип константы интерфейса
}

class User implements HasStatus {
    // const int DEFAULT = 1;          // Error: тип несовместим
    const string DEFAULT = "guest";    // OK — тот же тип
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое динамическое получение констант класса в PHP 8.3?',
                'answer' => '**Динамический доступ к константам класса** (PHP 8.3+) — синтаксис `MyClass::{$name}` для обращения по имени из переменной.

**До 8.3:**
```php
echo constant(Status::class . "::" . $name);
```
- многословно
- работает только со строкой полного имени
- легко допустить опечатку, IDE плохо подсказывает

**С 8.3:**
```php
echo Status::{$name};
```

**Свойства:**
- симметрично динамическому доступу к **свойствам** (`$obj->{$prop}`) и **методам** (`$obj->{$method}()`)
- работает с **enum cases** (`Role::{"Admin"}`) — удобно при десериализации
- работает со **statics** через имя константы из переменной/выражения

**Применение:** маршрутизация (имя константы соответствует параметру роута), десериализация enum-значений из JSON/БД, плагинная архитектура с лукапом по имени.',
                'code_example' => '<?php
class Status {
    const ACTIVE = "active";
    const BANNED = "banned";
}

$name = "ACTIVE";

// До PHP 8.3
echo constant(Status::class . "::" . $name); // "active"

// PHP 8.3+
echo Status::{$name};                        // "active"

// Работает и с enum
enum Role: string { case Admin = "admin"; }
echo Role::{"Admin"}->value;                 // "admin"',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что разрешает синтаксис new в инициализаторах PHP 8.1?',
                'answer' => '**`new` в инициализаторах** (PHP 8.1+) — объект, созданный через `new`, можно использовать как:
- значение **по умолчанию для параметра**
- значение **свойства**
- инициализатор **статической переменной**
- значение **параметра атрибута**

**Какую проблему решает:** убирает костыль с присваиванием в теле конструктора через `?? new NullLogger()` и упрощает DI с разумными дефолтами.

**Ограничения:**
- аргументы конструктора **должны быть константными выражениями** — нельзя `new Foo($someService)`, можно `new Foo("static")` или `new Foo(SomeEnum::A)`
- **рекурсия запрещена** — нельзя `class A { public function __construct(B $b = new B()) {} }` где B зависит от A
- объект создаётся **при каждом вызове** (не один раз) — для синглтона нужен явный контейнер

**Типичный паттерн:** Null Object для опционального логгера/кеша/диспетчера событий без `if ($logger === null)` в каждом методе.',
                'code_example' => '<?php
class Service {
    // До PHP 8.1
    public function __construct(?LoggerInterface $logger = null) {
        $this->logger = $logger ?? new NullLogger();
    }
}

// PHP 8.1+
class ServiceNew {
    public function __construct(
        private LoggerInterface $logger = new NullLogger(),
    ) {}
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Зачем нужен атрибут #[SensitiveParameter] в PHP 8.2?',
                'answer' => '**`#[\\SensitiveParameter]`** (PHP 8.2+) — атрибут на параметре функции, помечающий его как **чувствительный** (пароль, токен, API-key).

**Что меняет:**
- при формировании **stack trace** (исключения, `debug_backtrace()`, логи Sentry/Bugsnag) значение параметра заменяется на объект **`SensitiveParameterValue`** без реальных данных
- сама переменная внутри функции **не меняется** — это только про сериализацию трейса

**Зачем нужно:** до 8.2 пароль/токен попадал в стек-трейс при любом исключении глубже по коду — и оттуда в логи, Sentry, email-уведомления. Чтобы скрыть, надо было руками **зачищать трейсы** или ловить исключения и переоборачивать. Атрибут делает это автоматически.

**Где применять:**
- параметры с паролями, JWT, API-ключами, секретами
- свежий контракт PSR/Laravel — `Hash::make($password)`, `password_verify($password, $hash)` уже помечены
- свои сервисы аутентификации, платёжные интеграции

**Ограничения:** не защищает от **`var_dump`/`print_r`** внутри функции (это явный вывод значения, не трейс).',
                'code_example' => '<?php
function login(
    string $email,
    #[\\SensitiveParameter] string $password,
): void {
    throw new RuntimeException("auth failed");
}

try {
    login("a@b.c", "supersecret");
} catch (Throwable $e) {
    echo $e->getTraceAsString();
    // В трейсе вместо "supersecret" — Object(SensitiveParameterValue)
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что нового в работе с DateTime появилось в PHP 8.4?',
                'answer' => 'PHP 8.4 добавил статические методы **`DateTime::createFromTimestamp()`** и **`DateTimeImmutable::createFromTimestamp()`**, принимающие `int` или `float` и возвращающие объект соответствующего типа.

**Что было неудобно раньше:**
- `new DateTimeImmutable("@$ts")` — конструкция с `@` **всегда** интерпретирует timestamp как UTC и **игнорирует переданную таймзону**
- чтобы получить корректное локальное время, надо было руками вызывать `setTimezone()`
- это типичная ловушка: люди передают часовой пояс в конструктор, а он молча игнорируется

**Новый API:**
- принимает `int` (секунды) или **`float`** (с микросекундами — приятный бонус)
- возвращает объект с дефолтной таймзоной (или явно переданной)
- предсказуемое поведение, не требует «знать про @-багу»

**Применение:** парсинг Unix-timestamps из JSON-API, БД (`UNIX_TIMESTAMP()`), внешних сервисов.',
                'code_example' => '<?php
// До PHP 8.4
$dt = (new DateTimeImmutable("@1700000000"))
    ->setTimezone(new DateTimeZone("Europe/Moscow"));

// PHP 8.4+
$dt = DateTimeImmutable::createFromTimestamp(1700000000);
$dt = DateTimeImmutable::createFromTimestamp(1700000000.123); // микросекунды',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое функция request_parse_body() в PHP 8.4?',
                'answer' => 'request_parse_body() позволяет вручную распарсить тело HTTP-запроса в форматах multipart/form-data и application/x-www-form-urlencoded и заполнить аналоги $_POST/$_FILES. До 8.4 это делалось только автоматически на старте запроса и только для метода POST, поэтому, например, PUT/PATCH с multipart приходилось парсить руками. Теперь есть штатный способ.',
                'difficulty' => 4,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что изменилось в синтаксисе создания и использования объектов в PHP 8.4?',
                'answer' => 'PHP 8.4 разрешил **цепочку прямо после `new` без внешних скобок**.

**До 8.4** — обязательны внешние скобки:
```php
$id = (new User($name))->save();
$path = (new Config())->path;
```

**С 8.4:**
```php
$id = new User($name)->save();
$path = new Config()->path;
```

**Что разрешено в цепочке:**
- вызов **методов**: `new Foo()->bar()`
- обращение к **свойствам**: `new Foo()->prop`
- **индексирование**: `new Bar()[0]`
- цепочка из **нескольких** методов: `new Req("GET", $url)->withHeader(...)->send()`

**Что не меняется:** внутри по-прежнему создаётся новый объект, приоритет такой же, как у обычной цепочки. Это **синтаксический сахар**, никаких новых семантик.

**Где даёт выигрыш:** текучие (fluent) API, фабрики, разовые DTO, билдеры — везде, где раньше внешние скобки были визуальным шумом.',
                'code_example' => '<?php
// До PHP 8.4 — обязательны скобки
$id = (new User($name))->save();
$path = (new Config())->path;

// PHP 8.4+
$id = new User($name)->save();
$path = new Config()->path;

// Текучий API
$response = new Request("GET", $url)
    ->withHeader("Accept", "application/json")
    ->send();',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Почему в PHP 8.4 exit и die превратили из языковых конструкций в функции?',
                'answer' => 'До PHP 8.4 **`exit`** и **`die`** были **языковыми конструкциями** (как `echo`, `print`, `include`), а не функциями. Это приводило к ограничениям:

- нельзя было передать как **callable**: `array_walk($items, "exit")` — ошибка
- нельзя было использовать **first-class callable syntax**: `exit(...)` не работало
- сложно **замокать в тестах** — обычный мок-фреймворк не подменит конструкцию
- неконсистентно с другими «выходами»: `throw` стал выражением в 8.0, а `exit` оставался конструкцией

**В PHP 8.4** это полноценные функции с сигнатурой `exit(string|int $status = 0): never` (и `die` как алиас).

**Что даёт:**
- согласованность с остальным языком
- можно использовать в **функциях высшего порядка** и pipe-цепочках
- удобно в тестах: задизайнить через интерфейс/обёртку и подменить
- тип возврата **`never`** — статический анализ знает, что после `exit()` код недостижим',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Зачем в PHP 8.4 повысили cost у password_hash для PASSWORD_BCRYPT?',
                'answer' => 'Дефолтный cost для bcrypt в PHP 8.4 поднят с 10 до 12 — каждый шаг удваивает время хэширования, и за годы железо стало достаточно быстрым, чтобы старое значение перестало быть «достаточно медленным» для атакующего. Прикладной код это касается мало, но при апгрейде стоит вызывать password_needs_rehash() при следующем успешном логине, чтобы постепенно перехэшировать старые пароли.',
                'difficulty' => 4,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое функции array_first() и array_last() в PHP 8.5?',
                'answer' => '**`array_first()`** и **`array_last()`** (PHP 8.5+) — возвращают **первое и последнее значение** массива (или `null`, если массив пуст).

**Главное свойство — чистота:**
- **не двигают** внутренний указатель массива
- **не аллоцируют** новых массивов
- **не мутируют** исходный

**До 8.5** приходилось писать `reset()`/`end()`:
- они **мутируют** внутренний указатель — невидимый побочный эффект, легко создать баг при foreach по тому же массиву позже
- передача по значению — `reset($arr)` создаёт **копию** массива (для не-references)
- альтернатива `array_key_first()` + обращение по ключу — два вызова

**Поведение:**
- `array_first([])` → **`null`** (не warning, как у `reset` на пусто)
- работает и со **списками**, и с **ассоциативными** массивами
- значения по умолчанию нет (явно сравнивать с `null` или использовать `??`)

**Где применять:** получение первого/последнего элемента в коллекции без мутации — особенно полезно в **immutable-стиле** и функциональном коде.',
                'code_example' => '<?php
$users = ["a" => "Иван", "b" => "Аня", "c" => "Петя"];

// До PHP 8.5
$first = reset($users);  // "Иван", но указатель смещён
$last  = end($users);    // "Петя", указатель в конце

// PHP 8.5+ — чисто
$first = array_first($users); // "Иван"
$last  = array_last($users);  // "Петя"

array_first([]); // null',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что разрешил PHP 8.5 в константных выражениях относительно замыканий?',
                'answer' => 'В 8.5 в константных выражениях — параметрах атрибутов, значениях констант классов, дефолтах параметров — теперь допустимы статические замыкания и first-class callables, например strtoupper(...). Раньше там можно было использовать только литералы и new (с 8.1). Это даёт способ передавать «функцию по умолчанию» через атрибут или константу без отдельной фабрики.',
                'difficulty' => 4,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что нового в применении атрибутов появилось в PHP 8.5?',
                'answer' => 'PHP 8.5 расширил применимость атрибутов, закрыв дыры, оставшиеся с 8.0:

**1. Атрибуты на константах класса**
```php
class Config {
    #[\\Deprecated("используйте VERSION_V2")]
    const VERSION = "1.0";
}
```
Раньше пометить устаревшую константу можно было только PHPDoc-тегом.

**2. `#[\\Override]` на свойствах**
Полезно вместе с **property hooks** (PHP 8.4) — зафиксировать факт переопределения свойства в наследнике, чтобы рефакторинг родителя ломал компиляцию, а не молча создавал orphan-свойство.

**3. `#[\\Deprecated]` на трейтах и константах**
Раньше работал только на функциях, методах, классах. Теперь генерирует `E_USER_DEPRECATED` для всех видов «вызовов» устаревшего API единообразно.

**Зачем все эти точечные расширения:** единый рантайм-механизм deprecation вместо смеси PHPDoc-тегов, `trigger_error()` и комментариев в README.',
                'code_example' => '<?php
class Config {
    #[\\Deprecated("используйте VERSION_V2")]
    const VERSION = "1.0";       // атрибут на константе (PHP 8.5)
}

class Admin extends User {
    #[\\Override]
    public string $role = "admin"; // Override на свойстве (PHP 8.5)
}

#[\\Deprecated("используйте трейт LoggableV2")]
trait Loggable {}                  // Deprecated на трейте',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Какие важные deprecation-ы пришли в PHP 8.5?',
                'answer' => '**Подтверждённое в UPGRADING для 8.5:**
- завершение **case-веток** через **точку с запятой** вместо двоеточия (`case 1; ... break;` — синтаксический deprecation)
- продвижение **PIE** как рекомендованного инструмента установки расширений вместо устаревшего **PECL**
- точечные изменения в `date`/`curl`

**В RFC обсуждалось, но в финальный 8.5 в виде `E_DEPRECATED` НЕ вошло:**
- `__sleep`/`__wakeup` (в пользу `__serialize`/`__unserialize` из 7.4)
- backtick-исполнение `` `cmd` `` (в пользу `proc_open` / `Symfony\\Process`)
- нестандартные имена приведений: `(boolean)`, `(integer)`, `(double)` (в пользу канонических `(bool)`, `(int)`, `(float)`)

**Совет для подготовки кода:** ориентироваться на **релизные заметки** и `UPGRADING.md` в репозитории php-src, а не только на RFC-странички — между «принят RFC» и «попал в релиз» часто проходит цикл-два.',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Зачем в PHP 8.2 ввели новое расширение Random с классом Randomizer?',
                'answer' => '**Проблемы старого API:**
- `rand()`/`mt_rand()` используют **единый глобальный движок**
- сложно **подменить в тестах** (нет инъекции)
- невозможно иметь **несколько независимых потоков** случайности с воспроизводимыми seed
- `srand()` сидирует **глобал**, что ломает другие части программы

**Что даёт `ext-random`:**
- класс **`Random\\Randomizer`** — инкапсулирует состояние генератора
- принимает в конструктор **движок** через интерфейс `Random\\Engine`

**Доступные движки:**

| Движок | Назначение |
|---|---|
| `Random\\Engine\\Mt19937` | Mersenne Twister, совместим со старым `mt_rand` |
| `Random\\Engine\\Xoshiro256StarStar` | быстрый современный PRNG |
| `Random\\Engine\\PcgOneseq128XslRr64` | PCG, хорошая статистика |
| `Random\\Engine\\Secure` | **CSPRNG** (используется когда нужна крипто-безопасность) |

**Практическая польза:**
- **детерминированный seed** для воспроизводимых тестов (`new Mt19937(42)`)
- **несколько независимых** потоков случайности
- **Secure engine** — единый API для крипто и для не-крипто кейсов',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем атрибут #[\\Deprecated] из PHP 8.4 лучше пометки @deprecated в PHPDoc?',
                'answer' => '| Свойство | `@deprecated` в PHPDoc | `#[\\Deprecated]` (PHP 8.4+) |
|---|---|---|
| Видимость | только **IDE** и статанализаторы | часть **рантайма** |
| Требует исходников | да (PHPDoc) | нет (хранится в opcache) |
| Поведение при вызове | ничего | **`E_USER_DEPRECATED`** |
| Формат сообщения | произвольный текст | стандартный (since + message) |
| Можно поймать в логе/тесте | нет | да (`set_error_handler`) |
| Включается в opcache.preload | нет | да |

**Что даёт атрибут:**
- **унифицированный формат** сообщений в библиотеках экосистемы
- легко **поймать в CI** через `set_error_handler` или `expectDeprecation()` в PHPUnit
- агрегируется в **Sentry/Bugsnag** как обычные ошибки уровня notice
- видно даже если автор кода скрыл PHPDoc через минификацию

**Применение в библиотеках:** Symfony, Doctrine, Laravel постепенно мигрируют с собственных `trigger_error(..., E_USER_DEPRECATED)` на атрибут.

**Параметры:** `since` (версия), `message` (что использовать взамен).',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'В чём разница между Function JIT и Tracing JIT в PHP 8?',
                'answer' => 'Function JIT компилирует функцию целиком при первом её вызове в нативный машинный код, не анализируя поведение в рантайме. Это проще, но эффект скромный, потому что компилятор не видит, какие ветки реально горячие. Tracing JIT, который рекомендован и включается режимом 1254 в opcache.jit, отслеживает реально часто исполняемые трассы (последовательности опкодов через границы функций) и компилирует именно их с агрессивными оптимизациями. На CPU-bound задачах Tracing JIT даёт ускорение в разы, на типичном I/O-bound веб-приложении выигрыш почти не виден.',
                'difficulty' => 4,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое атрибуты PHP 8 (Attributes)?',
                'answer' => 'Атрибуты PHP 8 - метаданные, прикрепляемые к классам, методам, свойствам через синтаксис #[AttrName(args)]. Простыми словами: "теги" для кода, читаемые через Reflection. До PHP 8 использовались PHPDoc-аннотации (Doctrine, Symfony) - те парсились как комментарии. Атрибуты - часть языка, проверяются на этапе компиляции, доступны через ReflectionClass::getAttributes().',
                'code_example' => '<?php
#[Attribute(Attribute::TARGET_METHOD)]
class Route {
    public function __construct(
        public string $path,
        public string $method = "GET",
    ) {}
}

class UserController {
    #[Route("/users", method: "GET")]
    public function index() {}

    #[Route("/users/{id}", method: "POST")]
    public function update(int $id) {}
}

// Чтение
$ref = new ReflectionClass(UserController::class);
foreach ($ref->getMethods() as $method) {
    foreach ($method->getAttributes(Route::class) as $attr) {
        $route = $attr->newInstance();
        echo "$route->method $route->path\\n";
    }
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое constructor property promotion в PHP 8 простыми словами?',
                'answer' => '**Сокращённый синтаксис конструктора** (PHP 8.0+): **одна строка** вместо трёх — объявить свойство, принять параметр, присвоить его.

**Как работает:** модификатор видимости **прямо в параметре конструктора** (`public string $name`) автоматически:
- создаёт свойство `$this->name` с этим типом
- принимает параметр
- присваивает `$this->name = $name`

**Можно комбинировать с:**
- **`readonly`** (PHP 8.1+) — иммутабельные DTO одной строкой
- **типами**, значениями по умолчанию, **named arguments** на вызове

**Ограничения:** нельзя в `abstract`-конструкторах и интерфейсах, нельзя для `static`-свойств.',
                'code_example' => 'class User {
    public function __construct(
        public string $name,
        public int $age,
    ) {}
}
$u = new User("Иван", 30);
echo $u->name; // "Иван"',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Зачем нужен nullsafe-оператор ?-> и как он работает?',
                'answer' => '**`?->`** (PHP 8.0+) — безопасное обращение к свойству или методу через цепочку, в которой что-то может быть `null`.

**Как работает:** `$user?->profile?->avatar->url`
- если `$user` или `$user->profile` равны `null` — вся цепочка **сразу возвращает `null`**
- без него получили бы `Error: Attempt to read property on null`

**Что заменяет:** вложенные `if ($x !== null)` или каскад из `??`.

**Ограничения:**
- **не работает на запись** — `$obj?->prop = 1` запрещено
- **не работает со статикой** — нет `?::method`
- short-circuit: при `null` остаток цепочки не вычисляется (это важно для побочных эффектов)',
                'code_example' => '// Старый стиль
$avatar = null;
if ($user !== null && $user->profile !== null) {
    $avatar = $user->profile->avatar;
}

// PHP 8.0+
$avatar = $user?->profile?->avatar;',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем отличаются ключевые версии PHP 5.6 → 7.x → 8.x?',
                'answer' => 'Главные вехи (то, что чаще всего спрашивают на собесе):

**PHP 5.6 (2014)** — последняя 5.x. Variadic `...`, spread в вызовах, constant expressions.

**PHP 7.0 (2015)** — переписан движок (Phpng): **2× скорость**, **2× меньше памяти** против 5.6. **Скалярные типы** (`int`, `string`), **return types**, **`??`** (null coalescing), **`<=>`** (spaceship), anonymous classes, **`Throwable`** как корень `Error`/`Exception`.

**PHP 7.1** — **nullable-типы** (`?int`), `void`, `iterable`, **multi-catch** (`catch (A|B $e)`).

**PHP 7.2** — `object` тип, Sodium как ext, deprecation `each()`.

**PHP 7.3** — стабильные heredoc, `list()` с ключами.

**PHP 7.4** — **typed properties** (`public int $age`), **arrow functions** `fn() =>`, spread в массиве, **FFI**, **preloading** в opcache, **`??=`**, covariant return / contravariant param.

**PHP 8.0 (2020)** — **JIT**, **named arguments**, **attributes** `#[...]`, **constructor property promotion**, **`match`**, **nullsafe `?->`**, **union types** `int|string`, `throw` как выражение, `WeakMap`, **`str_contains`/`str_starts_with`/`str_ends_with`**.

**PHP 8.1** — **`enum`** (pure + backed), **`readonly`** properties, **first-class callable syntax** `strlen(...)`, **`never`** type, **intersection types** `A&B`, **`new` в инициализаторах**, **`Fiber`**.

**PHP 8.2** — **`readonly` классы**, **DNF**-типы `(A&B)|null`, `true`/`false`/`null` standalone, deprecation **динамических свойств**.

**PHP 8.3** — **typed class constants**, **`json_validate()`**, **`#[\\Override]`**, динамический доступ к константам.

**PHP 8.4** — **property hooks**, **asymmetric visibility** (`public private(set)`), **`array_find`/`array_all`/`array_any`**, цепочка после `new` без скобок.

**PHP 8.5** — `array_first`/`array_last`, атрибуты на константах, расширение применимости `#[\\Override]` и `#[\\Deprecated]`.',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Какие проблемы могут возникнуть при переходе проекта на новую мажорную версию PHP?',
                'answer' => 'Главные категории проблем при апгрейде:

**1. BC breaks из RFC**
- удалённые/изменённые функции: `each`, `create_function`, `mb_strrpos` с offset, `money_format`
- изменённое поведение операторов: `1 + "5 apples"` в 8.0 — `Warning`, в 9.0 — ошибка

**2. Строже типизация**
- внутренние функции теперь бросают **`TypeError`** вместо тихих warnings
- `strlen(null)` в 8.1+ — deprecation, в 9.0 — fatal

**3. Новые зарезервированные слова**
- `readonly`, `enum`, `never`, `match` как идентификаторы перестают работать
- ломаются методы/константы с такими именами

**4. Удалённые расширения**
- `mysql_*` (5 → 7), `ereg`, `mcrypt` (7.2)
- `oci8` отдельной установкой

**5. Динамические свойства (8.2)**
- deprecation на присвоение свойства, не объявленного в классе
- решения: атрибут **`#[AllowDynamicProperties]`** или явные свойства/DTO

**6. opcache/JIT**
- старые prefab-конфиги могут уронить performance или съесть память
- JIT в 8.0 в типовом веб-проде даёт ~0% и иногда крашит — обычно выключают (`opcache.jit_buffer_size=0`)

**7. Composer-зависимости**
- `composer outdated` / `composer why-not php:8.x`
- часто застревают: Symfony <5, Doctrine <2.10, старые сторонние SDK

**8. Driver-несовместимость**
- `pdo_mysql`/`mysqlnd` отдают новые типы (BIGINT теперь `int`, не `string` в 8.1+)
- ломается код, ожидавший строки

**9. PECL-расширения и opcache-shared-memory** — иногда отстают на месяцы.

**Стратегия миграции:**
1. **`rector --set php:81`** + PHPStan/Psalm на максимум
2. CI с **матрицей версий** PHP
3. **Канареечный деплой**
4. Обязательные **e2e + smoke + load**-тесты — часть проблем (память, GC, JIT) видна только под нагрузкой',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
        ];
    }
}
