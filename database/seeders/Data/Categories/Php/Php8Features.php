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
                'answer' => 'Named arguments (PHP 8.0+) позволяют передавать аргументы в функцию по имени параметра, а не позиции. Синтаксис: name: value. Полезно когда много опциональных параметров - не нужно помнить порядок и пропускать промежуточные. Можно смешивать с позиционными, но именованные строго после позиционных. С PHP 8.1 - в аттрибутах и enum.',
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
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работает match-выражение в PHP 8?',
                'answer' => 'match (PHP 8.0+) - выражение для сопоставления значений, аналог switch, но: 1) использует строгое сравнение ===, 2) возвращает значение, 3) не нуждается в break, 4) выбрасывает UnhandledMatchError если ни одна ветка не подошла, 5) может объединять несколько значений через запятую. Намного безопаснее и лаконичнее switch.',
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
                'answer' => 'Enum (PHP 8.1+) - тип-перечисление с фиксированным набором значений. Бывает: pure (просто кейсы) и backed (каждый кейс - значение типа int или string). Поддерживает методы, интерфейсы, статические методы. Кейс - синглтон, сравнение через ===. Backed enum имеет from() (выбросит ошибку) и tryFrom() (вернёт null). Cases() возвращает все варианты.',
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
                'answer' => 'В обычном fpm-цикле каждый запрос получает свежее состояние и память освобождается после завершения скрипта - можно почти не думать об утечках. В CLI-демонах (Laravel queue:work, Octane, Swoole, ReactPHP) процесс живёт часами/днями, и любой объект, который не отпустили, навсегда занимает память. PHP использует reference counting + mark-and-sweep для циклов: память освобождается, когда refcount = 0; циклические ссылки (A→B, B→A) собирает GC периодически (порог 10000 возможных корней либо вручную gc_collect_cycles()). Важный нюанс: даже после gc_collect_cycles() Zend Memory Manager удерживает память в виде арен и чанков - формально она "свободна" в PHP, но не возвращена в ОС. В долгоживущих процессах (Octane, RoadRunner, queue:work) для реального возврата памяти ОС вызывают gc_mem_caches() - очищает внутренние кеши Zend MM. Без этого RSS-процесса (видный в top/htop) только растёт. Типичные источники утечек: 1) Глобальные/статические массивы-кеши без TTL. 2) Eloquent-модели, удерживаемые в Job через свойства. 3) Singleton-сервисы с накапливающимся state. 4) Замыкания, захватывающие $this и удерживающие крупные объекты. Практика: queue:work --max-jobs=1000 --max-time=3600 - воркер сам перезапустится, освобождая всё; в Octane есть встроенный листенер Laravel\\Octane\\Listeners\\CollectGarbage, который вешается на OperationTerminated и вызывает gc_collect_cycles() при превышении порога memory_get_usage() > config("octane.garbage") (дефолт 50 МБ - именно мегабайты, не "запросов"). ВАЖНО: в стоковом config/octane.php этот листенер ЗАКОММЕНТИРОВАН - без раскомментирования автоматического GC между запросами не будет; gc_mem_caches() Octane не зовёт вообще, для реального возврата RSS в ОС его всё ещё надо вызывать руками или через свой листенер. Мониторинг через memory_get_usage(true) (учитывает реальный RSS, в отличие от false).',
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
                'answer' => 'FFI (расширение php-ffi, появилось в PHP 7.4) позволяет вызывать функции C-библиотек напрямую из PHP-кода без написания нативного PHP-расширения. Раньше для интеграции с libsodium, libcurl, libwebp нужно было писать .c-обёртку и компилировать в .so/.dll. С FFI достаточно загрузить заголовочный файл (или строку с C-объявлениями) и вызвать функции через объект FFI. Применение: интеграция с системными библиотеками без extension-разработки, прототипирование биндингов, доступ к нативным API ОС. FrankenPHP использует FFI/cgo для интеграции PHP-runtime с Go-сервером Caddy. Подводные камни: FFI медленнее обычного нативного расширения (overhead на маршалинг типов), небезопасен (типичные C-проблемы - segfault, undefined behavior), на проде нужно использовать opcache.preload для FFI - иначе FFI::cdef парсит .h-файл на каждом запросе. По умолчанию в production режим FFI ограничен через ffi.enable=preload (только preload-скрипт может его использовать).',
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
                'answer' => 'Атрибут #[\\Override] (PHP 8.3) ставится на метод и проверяет в момент компиляции, что этот метод действительно переопределяет метод родителя или реализует метод интерфейса/абстрактного класса. Если родитель удалит/переименует метод, или в дочернем классе будет опечатка - PHP сразу выбросит ошибку (Override of unknown method). Без атрибута опечатка тихо создаст НОВЫЙ метод, который никогда не вызовется через полиморфизм - классический баг рефакторинга, особенно при крупных миграциях между версиями фреймворка. Особенно ценен в комбинации с трейтами: если класс переопределяет метод трейта, а трейт потом переименовали или метод вынесли - без #[\\Override] класс молча получает orphaned-метод, который никто не вызовет через композицию трейта. Аналог @Override в Java, override в C#/Kotlin, override в TypeScript. Не имеет рантайм-стоимости (только compile-time проверка). Хорошая практика - добавлять на ВСЕ переопределяющие методы; PHPStan/Psalm и многие IDE умеют добавлять автоматически. Особенно полезен для методов с длинными именами и сложной сигнатурой (handle, process), где опечатка не очевидна.',
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
                'answer' => 'json_validate(string $json): bool - проверяет, что строка является валидным JSON, БЕЗ создания PHP-структуры в памяти. До PHP 8.3 единственный способ был json_decode + проверка json_last_error() (или json_decode($s, false, 512, JSON_THROW_ON_ERROR) с try/catch) - но это аллоцировало массив/объект под весь декодированный JSON. На больших payload-ах (мегабайты вебхуков, дампы) - бесполезный расход CPU и RAM, особенно в горячем пути или в долгоживущих воркерах. json_validate использует тот же парсер, что и json_decode, но останавливается, как только сделал проход - O(N) по времени, O(1) по памяти. Применение: rate-limiting роутов с большим JSON-телом, валидация webhook-ов перед очередью, фильтрация мусора на API gateway, проверка структуры до тяжёлой записи. Аргументы: $depth (макс. вложенность), $flags - из публичных флагов разрешён ТОЛЬКО JSON_INVALID_UTF8_IGNORE; передача JSON_THROW_ON_ERROR валит ValueError ("Argument #3 ($flags) must be a valid flag"), и это by design: смысл функции - вернуть bool без исключений и без аллокации, поэтому оборачивать json_validate в try/catch не нужно - используйте обычный if (!json_validate(...)).',
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
                'answer' => 'PHP 8.4 добавил четыре функции, заполняющие давнюю дыру в стандартной библиотеке: array_find, array_find_key, array_any, array_all. Все принимают $array и $callback, возвращают разное: array_find($arr, $cb) - первое значение, для которого callback вернул truthy, иначе null. array_find_key($arr, $cb) - ключ первого совпадения, иначе null. array_any($arr, $cb) - true, если хотя бы один элемент удовлетворяет, иначе false (аналог some/anyMatch). array_all($arr, $cb) - true, если ВСЕ элементы удовлетворяют, иначе false (аналог every/allMatch). Главное преимущество - early termination: останавливаются на первом совпадении (или первом несовпадении для array_all), не проходят весь массив. До 8.4 для тех же задач писали array_filter() (но он проходит ВЕСЬ массив и аллоцирует промежуточный результат) или явный foreach с break. Эти функции - аналоги Array.find / .some / .every из JS, anyMatch / allMatch / findFirst из Java Streams.',
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
                'answer' => 'Расширение URI добавляет иммутабельные классы для парсинга и сборки URL по RFC 3986 и WHATWG, например Uri\\Rfc3986\\Uri. В отличие от parse_url оно валидирует ввод, корректно обрабатывает кодирование и нормализацию и заменяет десятки сторонних библиотек.',
                'difficulty' => 4,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое типизированные константы классов в PHP 8.3?',
                'answer' => 'С PHP 8.3 у констант класса, интерфейса, enum и trait можно указывать тип, например const string ROLE = "admin". Тип проверяется при объявлении и при переопределении в наследнике — нельзя сузить его до несовместимого. Это полезно прежде всего для интерфейсных констант: реализации больше не могут случайно положить туда значение неподходящего типа.',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое динамическое получение констант класса в PHP 8.3?',
                'answer' => 'Раньше для обращения к константе по имени из переменной приходилось писать constant(MyClass::class . "::" . $name). В PHP 8.3 появился прямой синтаксис MyClass::{$name}, аналогичный динамическому доступу к свойствам и методам. Это короче, не требует строки с именем класса и работает с enum cases, что особенно удобно при маршрутизации и десериализации.',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое first-class callable syntax в PHP 8.1?',
                'answer' => 'Это запись foo(...) или $obj->method(...), которая возвращает Closure из любой функции, метода или статического метода. В отличие от строковых callable вроде "MyClass::foo" она проверяется на этапе компиляции, корректно работает с приватными методами в области видимости и автоматически биндит $this. По сути это типобезопасная замена Closure::fromCallable().',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что разрешает синтаксис new в инициализаторах PHP 8.1?',
                'answer' => 'С PHP 8.1 объект, созданный через new, можно использовать как значение по умолчанию для параметра, как значение свойства, статической переменной и параметра атрибута. Это убирает костыль с присваиванием в теле конструктора через ?? new NullLogger() и упрощает DI с разумными дефолтами. Аргументы конструктора, естественно, должны сами быть константными выражениями.',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Зачем нужен атрибут #[SensitiveParameter] в PHP 8.2?',
                'answer' => 'Атрибут помечает параметр функции как чувствительный — пароль, токен, ключ. При формировании stack trace, в том числе в логах и сообщениях об исключениях, его значение заменяется на объект SensitiveParameterValue без реальных данных. Это снижает риск утечки секретов через незачищенные логи и Sentry-репорты, не требуя ручной зачистки трейсов.',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что нового в работе с DateTime появилось в PHP 8.4?',
                'answer' => 'Появились статические методы DateTime::createFromTimestamp() и DateTimeImmutable::createFromTimestamp(), принимающие int или float и возвращающие соответствующий объект. Раньше для этого приходилось писать new DateTimeImmutable("@$ts") и потом вручную выставлять таймзону, потому что @-конструкция всегда даёт UTC. Новый API короче и предсказуемее.',
                'difficulty' => 2,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое функция request_parse_body() в PHP 8.4?',
                'answer' => 'request_parse_body() позволяет вручную распарсить тело HTTP-запроса в форматах multipart/form-data и application/x-www-form-urlencoded и заполнить аналоги $_POST/$_FILES. До 8.4 это делалось только автоматически на старте запроса и только для метода POST, поэтому, например, PUT/PATCH с multipart приходилось парсить руками. Теперь есть штатный способ.',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что изменилось в синтаксисе создания и использования объектов в PHP 8.4?',
                'answer' => 'PHP 8.4 разрешил цепочку прямо после new без внешних скобок: new User($name)->save() и new Config()->path вместо (new User($name))->save(). Это убирает визуальный шум в текучих API и фабриках. Внутри по-прежнему создаётся новый объект, и приоритет такой же, как у обычной цепочки методов и обращения к свойствам.',
                'difficulty' => 2,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Почему в PHP 8.4 exit и die превратили из языковых конструкций в функции?',
                'answer' => 'Раньше exit и die были языковыми конструкциями, поэтому их нельзя было передать как callable, обернуть в first-class callable syntax или замокать в тестах. В PHP 8.4 это полноценные функции с сигнатурой exit(string|int $status = 0): never. Это даёт согласованность с остальным языком и позволяет использовать их в pipe-цепочках и функциях высшего порядка.',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Зачем в PHP 8.4 повысили cost у password_hash для PASSWORD_BCRYPT?',
                'answer' => 'Дефолтный cost для bcrypt в PHP 8.4 поднят с 10 до 12 — каждый шаг удваивает время хэширования, и за годы железо стало достаточно быстрым, чтобы старое значение перестало быть «достаточно медленным» для атакующего. Прикладной код это касается мало, но при апгрейде стоит запускать password_needs_rehash() при следующем успешном логине, чтобы постепенно перехэшировать старые пароли.',
                'difficulty' => 4,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое функции array_first() и array_last() в PHP 8.5?',
                'answer' => 'array_first() и array_last() возвращают первое и последнее значение массива (или null, если массив пуст), не двигая внутренний указатель и не создавая новых массивов. До 8.5 для этого приходилось писать reset()/end(), которые мутируют указатель, либо array_key_first() в связке с обращением по ключу. Новые функции чистые и проще читаются.',
                'difficulty' => 2,
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
                'answer' => 'Во-первых, атрибуты теперь можно вешать на константы классов. Во-вторых, #[\\Override] разрешено применять не только к методам, но и к свойствам — полезно вместе с property hooks, чтобы зафиксировать факт переопределения в наследнике. В-третьих, #[\\Deprecated] расширен на трейты и константы. Это закрывает дыры, оставшиеся после ввода атрибутов в 8.0.',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Какие важные deprecation-ы пришли в PHP 8.5?',
                'answer' => 'Помечены устаревшими: завершение case-веток через точку с запятой вместо двоеточия, обратные кавычки `cmd` как алиас shell_exec(), магические __sleep()/__wakeup() в пользу __serialize()/__unserialize(), нестандартные имена приведений (boolean)/(integer)/(double) при наличии (bool)/(int)/(float). Также рекомендованным инструментом установки расширений вместо PECL объявлен PIE.',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Зачем в PHP 8.2 ввели новое расширение Random с классом Randomizer?',
                'answer' => 'Старые rand()/mt_rand() работают через единый глобальный движок, который сложно подменить в тестах и невозможно использовать параллельно с воспроизводимым seed. Расширение Random даёт ОО-API: класс Randomizer принимает движок (Mt19937, Xoshiro256StarStar, Secure и т.п.) и инкапсулирует состояние. Это позволяет иметь несколько независимых потоков случайности и детерминированно сидировать тесты.',
                'difficulty' => 3,
                'topic' => 'php.php8_features',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем атрибут #[\\Deprecated] из PHP 8.4 лучше пометки @deprecated в PHPDoc?',
                'answer' => 'PHPDoc-тег видят только IDE и статанализаторы и только при наличии исходников. Атрибут #[\\Deprecated(since: \'1.1\', message: ...)] — это часть рантайма: вызов помеченного метода, функции или обращение к константе генерирует E_USER_DEPRECATED со стандартным форматом сообщения. Это унифицирует deprecation-сообщения в библиотеках и позволяет ловить их в логах и в тестах.',
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
        ];
    }
}
