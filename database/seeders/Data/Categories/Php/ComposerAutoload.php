<?php

namespace Database\Seeders\Data\Categories\Php;

class ComposerAutoload
{
    public static function all(): array
    {
        return [
            [
                'category' => 'PHP',
                'question' => 'Что такое Composer и зачем он нужен?',
                'answer' => 'Composer - менеджер зависимостей для PHP (как npm для Node, pip для Python). Управляет пакетами проекта через composer.json. composer.lock фиксирует точные версии для воспроизводимых сборок. composer install ставит из lock, composer update обновляет. Поддерживает автозагрузку (PSR-4, PSR-0, classmap, files). Пакеты публикуются на packagist.org.',
                'code_example' => '{
    "name": "my/project",
    "require": {
        "php": "^8.2",
        "guzzlehttp/guzzle": "^7.0"
    },
    "require-dev": {
        "phpunit/phpunit": "^10.0"
    },
    "autoload": {
        "psr-4": {
            "App\\\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\\\": "tests/"
        }
    }
}',
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое PSR-4 автозагрузка?',
                'answer' => 'PSR-4 - стандарт автозагрузки классов. Простыми словами: имя класса с namespace однозначно отображается в путь к файлу. App\\Models\\User -> src/Models/User.php. Composer генерирует автозагрузчик по правилам в composer.json. Заменяет require_once для каждого файла. Старый стандарт PSR-0 заменял _ на / - устарел.',
                'code_example' => '<?php
// composer.json: "autoload": { "psr-4": { "App\\\\": "src/" } }

// src/Models/User.php
namespace App\Models;
class User {}

// public/index.php
require __DIR__ . "/../vendor/autoload.php";

use App\Models\User;
$user = new User(); // автоматически подгрузится файл

// composer dump-autoload -o    // оптимизированный для прода',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем отличаются include, require, include_once и require_once?',
                'answer' => 'include - подключает файл, при ошибке - Warning, выполнение продолжается. require - при ошибке Fatal Error и остановка. include_once / require_once - то же самое, но если файл уже подключался - не подключают повторно. На современных проектах эти конструкции почти не используют - всё через Composer autoload (PSR-4).',
                'code_example' => '<?php
// При отсутствии файла - warning, идём дальше
include "optional.php";

// При отсутствии - fatal error
require "config.php";

// Не подключит повторно
require_once "helper.php";
require_once "helper.php"; // ничего не делает

// Современный подход
require __DIR__ . "/vendor/autoload.php";',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работают сессии в PHP?',
                'answer' => 'Сессия - механизм хранения данных пользователя между запросами. session_start() инициализирует/восстанавливает сессию. Данные хранятся в $_SESSION (суперглобальный массив). PHP создаёт уникальный session_id и сохраняет его в куку (PHPSESSID). Сами данные хранятся на сервере (по умолчанию в файлах /tmp). Можно настроить хранилище: Redis, Memcached, БД.',
                'code_example' => '<?php
session_start();

$_SESSION["user_id"] = 42;
$_SESSION["cart"] = ["item1", "item2"];

// В другом запросе
session_start();
echo $_SESSION["user_id"]; // 42

unset($_SESSION["cart"]);
session_destroy();

// Регенерация ID при логине - защита от fixation
session_regenerate_id(true);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работают cookies в PHP?',
                'answer' => 'Cookie - небольшие данные, хранимые в браузере и посылаемые с каждым запросом. Установка через setcookie() ДО любого вывода (это HTTP-заголовок). Чтение через $_COOKIE. Параметры: expires, path, domain, secure (только HTTPS), httponly (недоступен JS), samesite (Strict/Lax/None - защита от CSRF). С PHP 7.3 принимает массив опций.',
                'code_example' => '<?php
setcookie("user", "Иван", [
    "expires" => time() + 3600,
    "path" => "/",
    "domain" => "example.com",
    "secure" => true,
    "httponly" => true,
    "samesite" => "Strict",
]);

echo $_COOKIE["user"] ?? "guest";

// Удалить
setcookie("user", "", time() - 3600);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работать с DateTime и DateTimeImmutable?',
                'answer' => 'DateTime - изменяемый объект даты, методы modify/add/sub МУТИРУЮТ объект. DateTimeImmutable - неизменяемый, методы возвращают НОВЫЙ объект. Всегда предпочитай Immutable - мутабельность дат причина множества багов. Форматирование через format(). Парсинг через createFromFormat. Разница через diff(). Часовые пояса через DateTimeZone.',
                'code_example' => '<?php
$dt = new DateTime("2026-05-01");
$dt->modify("+1 day");
echo $dt->format("Y-m-d"); // 2026-05-02 - изменился!

$dti = new DateTimeImmutable("2026-05-01");
$dti2 = $dti->modify("+1 day");
echo $dti->format("Y-m-d");  // 2026-05-01
echo $dti2->format("Y-m-d"); // 2026-05-02

// Парсинг
$dt = DateTimeImmutable::createFromFormat("d.m.Y", "01.05.2026");

// Разница
$diff = $dti->diff($dti2);
echo $diff->days; // 1

// Часовой пояс
$tz = new DateTimeZone("Europe/Moscow");
$dt = new DateTimeImmutable("now", $tz);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работать с JSON в PHP?',
                'answer' => 'json_encode превращает PHP-структуру в JSON-строку. json_decode парсит JSON. По умолчанию json_decode возвращает объект stdClass, передай true вторым аргументом для массива. Полезные флаги: JSON_THROW_ON_ERROR (PHP 7.3+, выбросит исключение вместо false), JSON_UNESCAPED_UNICODE (не экранировать кириллицу), JSON_PRETTY_PRINT, JSON_UNESCAPED_SLASHES.',
                'code_example' => '<?php
$data = ["name" => "Иван", "age" => 30];

$json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

// Парсинг в массив
$arr = json_decode($json, true);

// Парсинг в объект
$obj = json_decode($json);
echo $obj->name;

// С исключением
try {
    $data = json_decode($invalid, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
    echo $e->getMessage();
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как читать и писать файлы в PHP?',
                'answer' => 'Простые функции: file_get_contents (всё в строку), file_put_contents (записать). file() - читает в массив строк. fopen/fread/fwrite/fclose - для потоковой работы. fgets - построчно. file_put_contents с FILE_APPEND - дописывает. LOCK_EX - блокировка от конкурентной записи. Для больших файлов используй fopen + fgets, чтобы не загружать всё в память.',
                'code_example' => '<?php
// Простое чтение
$content = file_get_contents("file.txt");

// Простая запись
file_put_contents("file.txt", "data");

// Дописать с блокировкой
file_put_contents("log.txt", "line\n", FILE_APPEND | LOCK_EX);

// Чтение в массив строк
$lines = file("file.txt", FILE_IGNORE_NEW_LINES);

// Большой файл построчно
$fh = fopen("big.log", "r");
while (($line = fgets($fh)) !== false) {
    if (str_contains($line, "ERROR")) {
        echo $line;
    }
}
fclose($fh);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое передача по ссылке и по значению в PHP?',
                'answer' => 'По умолчанию переменные передаются по ЗНАЧЕНИЮ - функция получает копию. Чтобы изменения отражались на оригинале - используй & в сигнатуре (передача по ссылке). Объекты особый случай: переменная содержит ИДЕНТИФИКАТОР объекта, копируется он, но указывает на тот же объект. Поэтому изменения свойств видны вне функции, но переприсвоение - нет.',
                'code_example' => '<?php
function byValue($x) { $x = 100; }
function byRef(&$x) { $x = 100; }

$a = 5;
byValue($a);
echo $a; // 5

$b = 5;
byRef($b);
echo $b; // 100

// Объекты
function modify($obj) { $obj->name = "New"; }
function reassign($obj) { $obj = new stdClass(); }

$user = new stdClass();
$user->name = "Иван";
modify($user);
echo $user->name; // "New" - свойство изменилось

reassign($user);
echo $user->name; // "New" - переприсвоение НЕ работает',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое WeakMap и WeakReference?',
                'answer' => 'WeakReference (PHP 7.4+) и WeakMap (PHP 8.0+) - механизм слабых ссылок. Простыми словами: ссылка, которая НЕ удерживает объект в памяти. Если других сильных ссылок нет - GC может уничтожить объект, и weak-ссылка вернёт null. WeakMap - словарь объект -> данные, не препятствующий уничтожению ключа. Полезно для кэшей, метаданных, observer-паттерна без утечек памяти.',
                'code_example' => '<?php
// WeakReference
$obj = new stdClass();
$weak = WeakReference::create($obj);
var_dump($weak->get()); // object
unset($obj);
var_dump($weak->get()); // NULL - объект собран GC

// WeakMap - кэш метаданных
$cache = new WeakMap();
$user = new stdClass();
$cache[$user] = ["computed" => "data"];
echo count($cache); // 1

unset($user); // удалит и запись из WeakMap
echo count($cache); // 0',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое Fiber в PHP 8.1?',
                'answer' => 'Fiber - механизм PHP 8.1+, позволяющий приостанавливать и возобновлять выполнение функции в любой точке. Простыми словами: это как пауза в видео - можешь остановить выполнение, отдать управление, потом вернуться. Полезно для асинхронного кода. Главное отличие от generator - можно приостановить из любой ВЛОЖЕННОЙ функции, а не только на yield в самой функции. Используется в ReactPHP, AMPHP, Laravel Octane.',
                'code_example' => '<?php
$fiber = new Fiber(function() {
    echo "start\n";
    $value = Fiber::suspend("paused");
    echo "resumed with $value\n";
    return "done";
});

$result = $fiber->start();
echo "got: $result\n";  // "paused"

$result = $fiber->resume("hello");
echo "got: $result\n";  // "done"

var_dump($fiber->isTerminated()); // true',
                'code_language' => 'php',
                'difficulty' => 5,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как безопасно хешировать пароли в PHP?',
                'answer' => 'Используй password_hash($password, PASSWORD_DEFAULT) - функция автоматически генерирует соль и использует "текущий рекомендуемый PHP алгоритм" (на сегодня - bcrypt; PHP оставляет за собой право поменять дефолт в будущих версиях, поэтому колонку для хеша делайте VARCHAR(255)). Если в сборке доступен Argon2id и нужен явно он - используйте PASSWORD_ARGON2ID; password_hash($pwd, PASSWORD_ARGON2ID, ["memory_cost" => ..., "time_cost" => ..., "threads" => ...]). Никогда не используй md5/sha1/sha256 для паролей - они быстрые и заточены под GPU-брутфорс. password_verify($password, $hash) - проверка (за константное время, защита от timing-атак). password_needs_rehash($hash, PASSWORD_DEFAULT) проверяет, не пора ли пересчитать хеш (после смены дефолта или повышения cost) - вызывается на успешном логине.',
                'code_example' => '<?php
// При регистрации
$password = "secret123";
$hash = password_hash($password, PASSWORD_DEFAULT);
// сохранить $hash в БД

// При логине
if (password_verify($password, $hashFromDb)) {
    echo "OK";

    if (password_needs_rehash($hashFromDb, PASSWORD_DEFAULT)) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        // обновить в БД
    }
}

// С опциями
$hash = password_hash($password, PASSWORD_BCRYPT, ["cost" => 12]);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как защититься от SQL-инъекций в PHP?',
                'answer' => 'Главное правило: НИКОГДА не подставляй пользовательский ввод в SQL через конкатенацию или интерполяцию. Используй prepared statements (PDO или mysqli) - параметры передаются отдельно от SQL, БД сама их экранирует. С PDO - bindParam/bindValue или массив в execute(). Для динамических имён колонок/таблиц используй белый список allowed-значений.',
                'code_example' => '<?php
// ПЛОХО - SQL-инъекция!
$name = $_GET["name"];
$pdo->query("SELECT * FROM users WHERE name = \'$name\'");

// ХОРОШО - prepared
$stmt = $pdo->prepare("SELECT * FROM users WHERE name = ?");
$stmt->execute([$_GET["name"]]);

// С именованными параметрами
$stmt = $pdo->prepare("SELECT * FROM users WHERE age > :age AND role = :role");
$stmt->execute(["age" => 18, "role" => "admin"]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Динамическая колонка - белый список
$allowed = ["name", "email", "created_at"];
$col = in_array($_GET["sort"], $allowed) ? $_GET["sort"] : "name";
$pdo->query("SELECT * FROM users ORDER BY $col");',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как защититься от XSS в PHP?',
                'answer' => 'XSS (Cross-Site Scripting) - внедрение JS-кода через пользовательский ввод. Защита: всегда экранировать вывод через htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8"). В шаблонах Blade {{ $var }} экранирует автоматически, {!! $var !!} - НЕ экранирует (опасно). Для JSON в JS - json_encode с JSON_HEX_TAG. CSP-заголовки добавляют второй слой защиты.',
                'code_example' => '<?php
$userInput = "<script>alert(1)</script>";

// ПЛОХО
echo "<div>$userInput</div>";

// ХОРОШО
echo "<div>" . htmlspecialchars($userInput, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8") . "</div>";

// В JS-коде
$data = ["name" => $userInput];
echo "<script>const data = " . json_encode($data, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_APOS) . ";</script>";

// CSP-заголовок
header("Content-Security-Policy: default-src \'self\'");',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое CSRF и как от него защититься?',
                'answer' => 'CSRF (Cross-Site Request Forgery) - атака, когда пользователь, авторизованный на сайте A, заходит на сайт B, и B заставляет его браузер сделать запрос на A с куками. Защита: CSRF-токен, генерируемый сервером и проверяемый при отправке форм. Токен кладут в форму и сессию, при сабмите сравнивают через hash_equals (защита от timing-атак). SameSite=Strict/Lax cookie тоже защищает.',
                'code_example' => '<?php
session_start();

// Генерация при показе формы
if (empty($_SESSION["csrf"])) {
    $_SESSION["csrf"] = bin2hex(random_bytes(32));
}

// В форме
// <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION["csrf"]) ?>">

// Проверка при обработке
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST["csrf"]) || !hash_equals($_SESSION["csrf"], $_POST["csrf"])) {
        http_response_code(403);
        die("CSRF токен неверный");
    }
    // обработка
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое Reflection в PHP?',
                'answer' => 'Reflection - API для интроспекции кода в рантайме: получить информацию о классах, методах, свойствах, параметрах. Простыми словами: код, который анализирует другой код. Используется фреймворками для DI-контейнеров, ORM, сериализаторов, тестов. Основные классы: ReflectionClass, ReflectionMethod, ReflectionProperty, ReflectionParameter, ReflectionAttribute (PHP 8). Минус - медленнее прямых вызовов.',
                'code_example' => '<?php
class User {
    public function __construct(
        public string $name,
        private int $age,
    ) {}
    public function greet(): string { return "Hi, $this->name"; }
}

$ref = new ReflectionClass(User::class);
echo $ref->getName(); // "User"

foreach ($ref->getProperties() as $prop) {
    echo $prop->getName() . "\n";
}

$ctor = $ref->getConstructor();
foreach ($ctor->getParameters() as $p) {
    echo $p->getName() . ": " . $p->getType() . "\n";
}

// Создать через рефлексию
$user = $ref->newInstance("Иван", 30);

// Доступ к private
$ageProp = $ref->getProperty("age");
echo $ageProp->getValue($user);',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.composer_autoload',
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
        echo "$route->method $route->path\n";
    }
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое сериализация в PHP?',
                'answer' => 'Сериализация - превращение PHP-объекта или структуры в строку, из которой потом можно восстановить. serialize() / unserialize() - бинарный PHP-формат, сохраняет тип. json_encode() / json_decode() - текстовый, межъязыковой. С PHP 7.4 есть __serialize / __unserialize - современная замена устаревших Serializable. ВАЖНО: unserialize небезопасен с недоверенными данными - может выполнить код через __wakeup/__destruct (POP-цепочки).',
                'code_example' => '<?php
class User {
    public function __construct(
        public string $name,
        private string $secret,
    ) {}

    public function __serialize(): array {
        return ["name" => $this->name];
    }

    public function __unserialize(array $data): void {
        $this->name = $data["name"];
        $this->secret = "";
    }
}

$user = new User("Иван", "pwd");
$str = serialize($user);

$user2 = unserialize($str);

// Безопасный режим
$obj = unserialize($str, ["allowed_classes" => [User::class]]);',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Какие структуры данных есть в SPL?',
                'answer' => 'SPL (Standard PHP Library) - встроенные структуры данных и интерфейсы. Полезные классы: SplStack (LIFO), SplQueue (FIFO), SplDoublyLinkedList, SplFixedArray (массив фиксированного размера, экономит память), SplPriorityQueue (очередь с приоритетом), SplObjectStorage (хеш-таблица с объектами в ключах), SplHeap (куча). Интерфейсы: Iterator, IteratorAggregate, Countable, ArrayAccess.',
                'code_example' => '<?php
// Stack
$stack = new SplStack();
$stack->push(1);
$stack->push(2);
echo $stack->pop(); // 2

// Queue
$q = new SplQueue();
$q->enqueue("a");
$q->enqueue("b");
echo $q->dequeue(); // "a"

// Priority Queue
$pq = new SplPriorityQueue();
$pq->insert("task1", 1);
$pq->insert("task2", 5); // выше приоритет
echo $pq->extract(); // "task2"

// FixedArray
$arr = new SplFixedArray(1000);
$arr[0] = "val";

// ObjectStorage
$storage = new SplObjectStorage();
$storage[$obj] = "data";',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое OPcache в PHP?',
                'answer' => 'OPcache - встроенный байт-код кэш PHP. Простыми словами: PHP компилирует .php файлы в опкоды (промежуточный байт-код) при каждом запросе - OPcache сохраняет результат компиляции в shared memory, чтобы не пересобирать заново. Огромный буст в проде. Главные настройки: opcache.memory_consumption, opcache.max_accelerated_files, opcache.validate_timestamps (на проде = 0 для скорости, требует рестарта при деплое).',
                'code_example' => '; php.ini
opcache.enable=1
opcache.enable_cli=0
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0  ; в проде
opcache.revalidate_freq=0
opcache.save_comments=1        ; нужно для аннотаций

; opcache_get_status() - получить статус
; opcache_reset() - сбросить кэш',
                'code_language' => 'bash',
                'difficulty' => 4,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое JIT в PHP 8?',
                'answer' => 'JIT (Just-In-Time компиляция) - функция PHP 8+ (часть расширения OPcache), компилирует "горячий" опкод в нативный машинный код через DynASM прямо во время выполнения. Простыми словами: вместо интерпретации байткода - выполняется напрямую процессором. Включается через opcache.enable=1 + opcache.jit_buffer_size + opcache.jit=tracing|function. Важно для PHP 8.4: числовая форма флага (вроде 1255) объявлена deprecated - используйте именованные значения tracing / function / on / off / disable. Реально ускоряет CPU-bound задачи (математика, обработка изображений, шифры). Для типичных веб-приложений (БД, сеть) ускорения почти не даёт - бутылочное горло не CPU.',
                'code_example' => '; php.ini для JIT - современная (PHP 8.4+) форма
opcache.enable=1
opcache.jit_buffer_size=256M
opcache.jit=tracing             ; именованная форма
; opcache.jit=1255              ; ⚠️ deprecated в PHP 8.4 - не используйте

; Допустимые именованные значения:
; tracing  - анализирует частые пути выполнения (рекомендуется)
; function - JIT на уровне функций
; on       - синоним tracing
; off / disable - выключить

; Проверка во время выполнения
print_r(opcache_get_status(false)["jit"]);
// ["enabled" => true, "on" => true, "kind" => 5, ...]',
                'code_language' => 'bash',
                'difficulty' => 5,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое сборщик мусора (GC) в PHP?',
                'answer' => 'GC (Garbage Collector) - механизм освобождения памяти от объектов, которые больше не используются. PHP использует подсчёт ссылок (refcount) - когда счётчик становится 0, память освобождается сразу. Но есть проблема ЦИКЛИЧЕСКИХ ссылок (A ссылается на B, B на A) - тут refcount не доходит до 0. Для них есть отдельный циклический GC, запускающийся периодически. gc_collect_cycles() - запустить вручную.',
                'code_example' => '<?php
class Node {
    public ?Node $next = null;
}

$a = new Node();
$b = new Node();
$a->next = $b;
$b->next = $a;  // циклическая ссылка

unset($a, $b);
// Refcount не = 0, объекты остаются в памяти!

gc_collect_cycles(); // запустить циклический GC

var_dump(gc_enabled()); // true
var_dump(gc_status());
gc_disable(); // отключить',
                'code_language' => 'php',
                'difficulty' => 5,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое PHP-FPM?',
                'answer' => 'PHP-FPM (FastCGI Process Manager) - менеджер процессов PHP для работы за веб-сервером (Nginx). Простыми словами: пул долгоживущих PHP-процессов, обрабатывающих запросы по протоколу FastCGI. Альтернатива mod_php (Apache) - быстрее, гибче. Настраиваются пулы: pm.max_children (макс процессов), pm.start_servers, pm = dynamic/static/ondemand. На запрос выделяется один воркер, после ответа PHP сбрасывает состояние.',
                'code_example' => '; /etc/php/8.2/fpm/pool.d/www.conf
[www]
user = www-data
listen = /run/php/php8.2-fpm.sock

pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 10
pm.max_requests = 500   ; рестарт воркера каждые N запросов

pm.status_path = /status
ping.path = /ping',
                'code_language' => 'bash',
                'difficulty' => 4,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое late static binding и зачем нужен static вместо self?',
                'answer' => 'Late static binding (LSB) - механизм, когда static:: ссылается на класс, в котором был ВЫЗВАН метод, а не на тот, где он объявлен. self:: всегда ссылается на класс объявления. Простыми словами: static подстраивается под наследников, self - нет. Критично для фабричных методов в родительских классах: с static новые подклассы автоматически получают правильное поведение.',
                'code_example' => '<?php
class Model {
    public static function create(): self {
        return new self();   // всегда Model
    }
    public static function createStatic(): static {
        return new static(); // тот класс, что вызвал
    }
}

class User extends Model {}

$a = User::create();        // Model!
$b = User::createStatic();  // User

var_dump($a instanceof User); // false
var_dump($b instanceof User); // true',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое Dependency Injection в PHP?',
                'answer' => 'DI (внедрение зависимостей) - паттерн, когда зависимости класса передаются ему ИЗВНЕ (через конструктор/сеттер), а не создаются внутри. Простыми словами: класс не сам делает new Logger(), а получает готовый Logger через параметр. Плюсы: легче тестировать (подменить мок), легче менять реализации, явные зависимости. DI-контейнер автоматизирует создание объектов с зависимостями.',
                'code_example' => '<?php
// ПЛОХО - hard-coded зависимость
class UserService {
    private Logger $logger;
    public function __construct() {
        $this->logger = new FileLogger(); // нельзя подменить!
    }
}

// ХОРОШО - DI через конструктор
class UserService {
    public function __construct(
        private LoggerInterface $logger,
        private UserRepository $repo,
    ) {}

    public function create(string $name): User {
        $user = $this->repo->create($name);
        $this->logger->log("created $name");
        return $user;
    }
}

// В тестах легко подменить
$service = new UserService($mockLogger, $mockRepo);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Расскажи о принципах SOLID в PHP-контексте.',
                'answer' => 'S - Single Responsibility: один класс - одна причина для изменения. O - Open/Closed: класс открыт для расширения (через композицию, наследование, стратегию), закрыт для модификации. L - Liskov: подклассы должны быть взаимозаменяемы с родителем. I - Interface Segregation: лучше много мелких интерфейсов, чем один "толстый". D - Dependency Inversion: завись от абстракций (интерфейсов), не от конкретных классов. Все принципы про управление сложностью и переиспользование.',
                'code_example' => '<?php
// SRP - класс User не должен сам себя в БД сохранять
class User { /* данные */ }
class UserRepository {
    public function save(User $user): void {}
}

// OCP - расширяем через стратегию, не правим класс
interface Discount {
    public function calc(float $price): float;
}
class NewYearDiscount implements Discount {}
class BlackFridayDiscount implements Discount {}

// DIP - зависим от интерфейса
class Order {
    public function __construct(
        private PaymentGateway $gateway, // интерфейс!
    ) {}
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работают namespaces в PHP?',
                'answer' => 'Namespace - механизм группировки классов, функций, констант для избежания конфликтов имён. Объявляется namespace App\\Models; в начале файла. Используется через use App\\Models\\User;. На практике по PSR-4 принято "один файл - один namespace и один класс" (нужно для автозагрузки), но синтаксически PHP допускает несколько namespace в одном файле через блочный синтаксис namespace Foo { ... } namespace Bar { ... } - официальная документация явно называет это strongly discouraged. Полное имя начинается с \\ (FQCN). Поддерживает алиасы (use X as Y), групповые импорты (PHP 7+).',
                'code_example' => '<?php
// src/Models/User.php
namespace App\Models;

class User {}

// src/Services/UserService.php
namespace App\Services;

use App\Models\User;
use App\Models\Post as PostModel;

// Групповой импорт (PHP 7+)
use App\Models\{User as U, Post, Comment};

class UserService {
    public function find(): U {
        return new U();
    }
}

// FQCN
$cls = \App\Models\User::class;',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое callable и как передать функцию как параметр?',
                'answer' => 'callable - тип, обозначающий "вызываемое". Может быть: имя функции (строка), массив [$obj, "method"] или ["Class", "staticMethod"], замыкание (Closure), объект с __invoke, first-class callable PHP 8.1 (function(...)). Часто используется в array_map, usort. Можно типизировать параметр как callable или Closure.',
                'code_example' => '<?php
function process(callable $fn, array $data): array {
    return array_map($fn, $data);
}

// Имя функции
process("strtoupper", ["a", "b"]);

// Замыкание
process(fn($x) => $x * 2, [1, 2, 3]);

// Метод объекта
class Doubler {
    public function double($x) { return $x * 2; }
}
$d = new Doubler();
process([$d, "double"], [1, 2]);

// Статический метод
process(["Math", "square"], [1, 2]);

// First-class callable (PHP 8.1)
process(strtoupper(...), ["a", "b"]);
process($d->double(...), [1, 2]);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое spread-оператор в PHP?',
                'answer' => 'Spread-оператор ... распаковывает массив в аргументы функции (PHP 5.6+) или в другой массив (PHP 7.4+). С PHP 8.1 поддерживает строковые ключи. В сигнатуре функции ...$args собирает все аргументы в массив (variadic). Альтернатива call_user_func_array.',
                'code_example' => '<?php
// Variadic - собирает аргументы
function sum(int ...$nums): int {
    return array_sum($nums);
}
echo sum(1, 2, 3, 4); // 10

// Распаковка в вызов
$args = [1, 2, 3];
echo sum(...$args); // 6

// Распаковка в массив (PHP 7.4)
$first = [1, 2, 3];
$second = [...$first, 4, 5]; // [1,2,3,4,5]

// Со строковыми ключами (PHP 8.1)
$a = ["name" => "Иван"];
$b = ["age" => 30];
$user = [...$a, ...$b];

// Spread в named arguments
$params = ["name" => "Иван", "age" => 30];
createUser(...$params);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое clone и как работает магический __clone?',
                'answer' => 'clone создаёт ПОВЕРХНОСТНУЮ копию объекта - все свойства копируются. НО: вложенные объекты копируются по ссылке-идентификатору (то есть указывают на тот же объект). Для глубокой копии нужно реализовать __clone и в нём вручную клонировать вложенные объекты. __clone вызывается автоматически после копирования свойств.',
                'code_example' => '<?php
class Address {
    public string $city = "Moscow";
}

class User {
    public Address $address;
    public function __construct() {
        $this->address = new Address();
    }
}

$a = new User();
$b = clone $a;
$b->address->city = "SPB";
echo $a->address->city; // "SPB"! - тот же объект

// Глубокая копия
class UserDeep {
    public Address $address;
    public function __clone(): void {
        $this->address = clone $this->address;
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое строгая типизация (strict_types) в PHP?',
                'answer' => 'declare(strict_types=1) в начале файла включает строгую типизацию. ВАЖНО про точное место действия (часто путают): для ПАРАМЕТРОВ функции strict_types управляется файлом, в котором стоит ВЫЗОВ - то есть передал "5" в int-параметр в strict-файле получишь TypeError, даже если функция объявлена в файле без strict_types. А вот для ВОЗВРАЩАЕМОГО типа strict_types управляется файлом, где функция ОБЪЯВЛЕНА: если функция в strict-файле объявила `: int` и пытается вернуть "5", получит TypeError независимо от настроек вызывающего. Это две разные точки контроля - параметры читаются от caller, return - от callee. Без strict_types PHP в обоих случаях пытается привести типы (coercive mode): "5" в int-параметре станет 5, "5" из `: int` тоже станет 5. Лучшая практика - всегда включать strict_types в начале каждого файла, чтобы не зависеть от настройки вызывающего.',
                'code_example' => '<?php
declare(strict_types=1);

function add(int $a, int $b): int {
    return $a + $b;
}

add(5, 10);    // 15 - OK
// add("5", 10);  // TypeError со strict_types - параметры контролируются caller-ом

// Файл objects/Foo.php (с strict_types=1)
// function maybeId(): int { return "42"; }
// → TypeError на return - return контролируется callee
// (даже если вызвать из файла без strict_types)

// Без strict_types это сработало бы:
// "5" -> 5, 5.5 -> 5',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем отличается isset, empty и is_null?',
                'answer' => 'isset($x) - true если переменная существует и не null; не выдаёт Warning, даже если переменной нет. empty($x) - true если переменной нет или её значение falsy ("", 0, "0", null, [], false); тоже не выдаёт Warning. is_null($x) и сравнение $x === null - проверяют именно равенство null, но ОБА выдадут Warning: Undefined variable, если переменная не объявлена (PHP 8+). То есть для безопасной проверки "существует ли вообще" - только isset/empty; is_null и === null применяй когда уверен, что переменная объявлена. Для массивов: isset($arr["key"]) даёт false если значение null - чтобы отличить "нет ключа" от "ключ есть, но null", используй array_key_exists.',
                'code_example' => '<?php
$a = null;
$b = "";
$c = 0;
$d = "0";
$e = "hello";

var_dump(isset($a)); // false (null)
var_dump(isset($b)); // true ("")

var_dump(empty($a)); // true
var_dump(empty($b)); // true ("")
var_dump(empty($c)); // true (0)
var_dump(empty($d)); // true ("0" - тоже falsy!)
var_dump(empty($e)); // false

var_dump(is_null($a)); // true

// Массив с null
$arr = ["key" => null];
var_dump(isset($arr["key"]));            // false
var_dump(array_key_exists("key", $arr)); // true',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое суперглобальные переменные в PHP?',
                'answer' => 'Суперглобальные переменные - встроенные массивы, доступные везде без global. $_GET - параметры из URL. $_POST - тело POST-запроса. $_REQUEST - объединение GET, POST, COOKIE. $_SERVER - данные сервера и заголовки. $_FILES - загруженные файлы. $_COOKIE - cookies. $_SESSION - данные сессии. $_ENV - переменные окружения. $GLOBALS - все глобальные переменные.',
                'code_example' => '<?php
// URL: /search?q=php&page=2
$query = $_GET["q"] ?? "";     // "php"
$page = (int) ($_GET["page"] ?? 1);

// POST форма
$email = $_POST["email"] ?? "";

// Заголовки и сервер
$method = $_SERVER["REQUEST_METHOD"];
$ip = $_SERVER["REMOTE_ADDR"];

// Загруженный файл
if ($_FILES["avatar"]["error"] === UPLOAD_ERR_OK) {
    move_uploaded_file(
        $_FILES["avatar"]["tmp_name"],
        "uploads/" . $_FILES["avatar"]["name"]
    );
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работает copy-on-write в PHP?',
                'answer' => 'Copy-on-write (COW) - оптимизация, при которой PHP не копирует данные сразу при присваивании, а только когда одна из переменных МОДИФИЦИРУЕТСЯ. Простыми словами: $b = $a не копирует - просто увеличивает refcount. Реальная копия делается при первой записи. Для массивов и строк работает прозрачно. Для ОБЪЕКТОВ COW не работает - там handle-семантика.',
                'code_example' => '<?php
$a = range(1, 1_000_000); // большой массив
$b = $a;  // НЕ копирование, просто refcount++
// memory_get_usage() показывает, что память не выросла

$b[0] = "new"; // ВОТ ТУТ копия!
// Теперь $b - отдельный массив

// Объекты - НЕ COW, всегда handle
$obj1 = new User();
$obj2 = $obj1;
$obj2->name = "X";
echo $obj1->name; // "X" - тот же объект

// Передача в функцию - тоже COW для массивов
function process(array $data) { /* ... */ }
process($bigArray); // не копируется до изменения',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое Iterator и IteratorAggregate?',
                'answer' => 'Iterator - интерфейс, который надо реализовать чтобы объект работал в foreach. Методы: rewind, valid, current, key, next. IteratorAggregate проще - нужно только реализовать getIterator(), возвращающий любой Iterator (часто - ArrayIterator). Plus Generator: метод getIterator() может быть генератором (yield). Это делает обход коллекций ленивым и кастомным.',
                'code_example' => '<?php
// Через IteratorAggregate + Generator
class Collection implements IteratorAggregate {
    public function __construct(private array $items) {}

    public function getIterator(): Generator {
        foreach ($this->items as $key => $value) {
            yield $key => $value;
        }
    }
}

$c = new Collection(["a", "b", "c"]);
foreach ($c as $item) {
    echo $item;
}

// Полный Iterator
class Range implements Iterator {
    private int $current;
    public function __construct(private int $start, private int $end) {
        $this->current = $start;
    }
    public function rewind(): void { $this->current = $this->start; }
    public function valid(): bool { return $this->current <= $this->end; }
    public function current(): int { return $this->current; }
    public function key(): int { return $this->current - $this->start; }
    public function next(): void { $this->current++; }
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое ArrayAccess и Countable?',
                'answer' => 'ArrayAccess - интерфейс позволяющий обращаться с объектом как с массивом через []. Методы: offsetExists, offsetGet, offsetSet, offsetUnset. Countable - чтобы count($obj) работал, реализуй метод count(). Вместе с Iterator/IteratorAggregate позволяют создать класс-коллекцию, неотличимый от массива в использовании. Laravel Collection - яркий пример.',
                'code_example' => '<?php
class Bag implements ArrayAccess, Countable, IteratorAggregate {
    public function __construct(private array $items = []) {}

    public function offsetExists(mixed $offset): bool {
        return isset($this->items[$offset]);
    }
    public function offsetGet(mixed $offset): mixed {
        return $this->items[$offset] ?? null;
    }
    public function offsetSet(mixed $offset, mixed $value): void {
        if ($offset === null) $this->items[] = $value;
        else $this->items[$offset] = $value;
    }
    public function offsetUnset(mixed $offset): void {
        unset($this->items[$offset]);
    }
    public function count(): int {
        return count($this->items);
    }
    public function getIterator(): ArrayIterator {
        return new ArrayIterator($this->items);
    }
}

$bag = new Bag(["a", "b"]);
$bag[] = "c";
echo count($bag);   // 3
echo $bag[0];       // "a"',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как генерировать криптостойкие случайные числа в PHP?',
                'answer' => 'rand() и mt_rand() - НЕ криптографически безопасны. Для безопасности (токены, пароли, CSRF) используй random_bytes() и random_int() (PHP 7+) - они дают криптостойкую случайность. random_int($min, $max) для целых, random_bytes($n) для бинарных данных. С PHP 8.2 появился новый объектный API через класс Random\\Engine.',
                'code_example' => '<?php
// ПЛОХО - предсказуемо
$token = md5(rand());

// ХОРОШО - криптостойко
$token = bin2hex(random_bytes(16));
// 32 hex-символа

$pin = random_int(1000, 9999);

// PHP 8.2+ объектный API
$randomizer = new Random\Randomizer();
$bytes = $randomizer->getBytes(16);
$num = $randomizer->getInt(1, 100);

// Перемешивание массива
$shuffled = $randomizer->shuffleArray([1, 2, 3, 4]);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое stream wrappers и как использовать php://?',
                'answer' => 'Stream wrappers - механизм PHP для работы с разными источниками данных через единый файловый API (fopen, file_get_contents). Встроенные: php://stdin, php://stdout, php://memory (в памяти), php://temp (диск, если переполнило), php://input (тело запроса), php://output. file:// - локальные файлы (по умолчанию). http://, https://, ftp:// - сеть. Можно регистрировать свои через stream_wrapper_register.',
                'code_example' => '<?php
// Тело POST-запроса
$body = file_get_contents("php://input");
$data = json_decode($body, true);

// В память (быстро)
$mem = fopen("php://memory", "r+");
fwrite($mem, "data");
rewind($mem);
echo stream_get_contents($mem);

// Чтение из stdin (CLI)
$line = trim(fgets(STDIN));

// HTTP с context
$context = stream_context_create([
    "http" => ["method" => "POST", "content" => "data"],
]);
$res = file_get_contents($url, false, $context);',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем отличается print_r от var_dump и var_export?',
                'answer' => 'var_dump показывает тип и значение, удобен для отладки сложных вложенных структур. print_r выводит в "читаемом" виде, без типов. var_export выводит в виде ВАЛИДНОГО PHP-кода (подходит для генерации файлов конфигурации). Все три могут вернуть строку вместо вывода (вторым параметром true). На проде используй логирование, не вывод в браузер.',
                'code_example' => '<?php
$data = ["name" => "Иван", "age" => 30, "admin" => true];

var_dump($data);
// array(3) {
//   ["name"]=> string(4) "Иван"
//   ["age"]=> int(30)
//   ["admin"]=> bool(true)
// }

print_r($data);
// Array (
//     [name] => Иван
//     [age] => 30
//     [admin] => 1
// )

var_export($data);
// array (
//   "name" => "Иван",
//   "age" => 30,
//   "admin" => true,
// )

// Получить как строку
$str = print_r($data, true);
$str = var_export($data, true);',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое stdClass в PHP?',
                'answer' => 'stdClass - встроенный пустой класс PHP. Используется как контейнер для произвольных свойств. Когда json_decode без второго параметра возвращает объект - это stdClass. Также получается при касте массива в (object). Полей и методов своих нет, можно динамически добавлять любые свойства. Не путать с (object) или ArrayObject.',
                'code_example' => '<?php
// Создание
$obj = new stdClass();
$obj->name = "Иван";
$obj->age = 30;

// Из массива
$obj = (object) ["name" => "Иван", "age" => 30];
echo $obj->name;

// Из JSON
$obj = json_decode("{\"name\":\"Иван\"}");
echo $obj->name;

// Обратно в массив
$arr = (array) $obj;
print_r($arr); // ["name" => "Иван"]',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как сравнивать объекты в PHP?',
                'answer' => 'Оператор == (нестрогое): объекты равны если они одного класса и все свойства равны (рекурсивно). Оператор === (строгое): должны быть тот же экземпляр (один объект, не разные с одинаковыми свойствами). Для кастомного сравнения - реализуй метод equals() в классе. Не путать с clone - там создаётся новый объект.',
                'code_example' => '<?php
class Point {
    public function __construct(
        public int $x,
        public int $y,
    ) {}

    public function equals(Point $other): bool {
        return $this->x === $other->x && $this->y === $other->y;
    }
}

$a = new Point(1, 2);
$b = new Point(1, 2);
$c = $a;

var_dump($a == $b);   // true (поля равны)
var_dump($a === $b);  // false (разные экземпляры)
var_dump($a === $c);  // true (тот же экземпляр)

var_dump($a->equals($b)); // true',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работает copy-on-write для массивов и строк в PHP и когда он перестаёт работать?',
                'answer' => 'PHP хранит значения в zval со счётчиком ссылок refcount. При присваивании увеличивается refcount, а сам zval не копируется. Глубокое копирование (separation) происходит только при первой записи в одну из связанных переменных. ВАЖНО: с PHP 7+ скалярные типы (int, float, bool, null) НЕ используют refcounting и CoW - сам zval-контейнер компактный (16 байт), и прямое копирование 16 байт CPU-инструкцией оказывается быстрее, чем атомарный refcount++ с последующей разыменовкой. CoW применяется к МАССИВАМ И СТРОКАМ. Для ОБЪЕКТОВ CoW не работает - переменная держит handle (object id), при $b = $a копируется только handle, оба имени указывают на ОДИН И ТОТ ЖЕ объект; "копия" появляется только при явном clone (и __clone определяет, что именно копируется). Ресурсы - тоже handle-семантика, без CoW. CoW также ломается, если переменная передана по ссылке (&$var) или захвачена в замыкание по ссылке - тогда копия делается сразу или вообще не делается, а изменения видны через все алиасы.',
                'code_example' => '<?php
$a = range(1, 1_000_000); // 1 zval
$b = $a;                  // refcount=2, копии нет
$b[0] = 0;                // separation: копируется массив
$c = &$a;                 // CoW отключён для пары $a/$c',
                'code_language' => 'php',
                'difficulty' => 5,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем generator отличается от обычной функции и почему он экономит память?',
                'answer' => 'Generator - это функция с yield, возвращающая объект Generator, реализующий Iterator. Тело функции выполняется лениво: на каждой итерации до следующего yield, после чего стек замораживается. В памяти живёт только текущее значение и состояние корутины, а не весь набор данных. Это позволяет обрабатывать потоки данных любого размера в O(1) памяти. Дополнительно поддерживаются send() (двусторонняя коммуникация) и yield from (делегирование).',
                'code_example' => '<?php
// Обычная функция - строит весь массив
function rangeArr(int $n): array {
    $r = [];
    for ($i = 0; $i < $n; $i++) $r[] = $i;
    return $r; // O(n) памяти
}

// Generator - O(1) памяти
function rangeGen(int $n): Generator {
    for ($i = 0; $i < $n; $i++) yield $i;
}

foreach (rangeGen(1_000_000) as $i) {
    if ($i > 5) break; // не строим миллион - выходим сразу
}

// send() - двусторонняя коммуникация
function echoer(): Generator {
    while (true) {
        $msg = yield;
        echo "got: $msg\\n";
    }
}
$g = echoer();
$g->current();          // запуск до первого yield
$g->send("hi");         // got: hi',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое Fiber в PHP 8.1 и чем он отличается от generator и от корутины Go?',
                'answer' => 'Fiber - примитив пользовательских стеков: можно приостановить (Fiber::suspend) и возобновить (resume) выполнение в произвольной точке, не только на yield. Generator кооперативен и тесно связан с iterator-протоколом, fiber же универсальнее и используется в ReactPHP/AMPHP для скрытия await. В отличие от горутин, fibers однопоточные, не имеют шедулера в ядре языка и не дают параллелизма - только конкурентность.',
                'code_example' => '<?php
$fiber = new Fiber(function (): void {
    $x = Fiber::suspend("ready");
    echo "got $x\\n";
});
$msg = $fiber->start();   // "ready"
$fiber->resume("hello");  // печатает "got hello"',
                'code_language' => 'php',
                'difficulty' => 5,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Зачем нужны readonly-свойства и readonly-классы (PHP 8.2) и какие у них ограничения?',
                'answer' => 'readonly-свойство можно инициализировать один раз изнутри объявившего класса (обычно в конструкторе, но строго это "первая запись из scope класса", а не только из конструктора). После первой записи переписать его снаружи или из наследника нельзя - Error. readonly-класс (PHP 8.2+) делает все нестатические свойства readonly автоматически. Это даёт иммутабельные DTO/value objects без бойлерплейта геттеров. Ограничения: нельзя static-свойства, нельзя дефолтные значения у типизированных readonly-свойств. Про клонирование: до PHP 8.3 clone не позволял переписать readonly на копии, использовали wither (return new self(...)); с PHP 8.3 (RFC "readonly amendments") readonly-свойства можно reinitialize СТРОГО внутри тела магического метода __clone() того класса, где они объявлены - вне __clone() запись по-прежнему Error. Полезно это для глубокого клонирования вложенных readonly-объектов и сброса кешированного state на копии; для классических wither-ов new self(...) остаётся каноном.',
                'code_example' => '<?php
final readonly class Money {
    public function __construct(
        public int $amount,
        public string $currency,
    ) {}
}
$m = new Money(100, "USD");
// $m->amount = 200; // Error',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что делает OPcache и почему важна opcache.validate_timestamps в проде?',
                'answer' => 'OPcache кэширует скомпилированный байткод PHP в shared memory, избавляя от парсинга и компиляции на каждый запрос. validate_timestamps=1 заставляет PHP проверять mtime файлов; в проде её ставят в 0 для скорости и сбрасывают кэш во время деплоя через opcache_reset() или перезапуск FPM. Также важны opcache.memory_consumption, max_accelerated_files и preloading (PHP 7.4+) для разогрева классов до старта воркеров.',
                'code_example' => '; production php.ini
opcache.enable=1
opcache.validate_timestamps=0
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.preload=/var/www/preload.php',
                'code_language' => 'bash',
                'difficulty' => 5,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работает JIT в PHP 8 и в каких задачах он реально ускоряет?',
                'answer' => 'JIT (tracing/function режимы) компилирует горячий байткод в машинный код через DynASM. Для типичных веб-приложений выигрыш скромный, потому что бутылочное горлышко - IO/база, а не CPU. Реальный профит - на CPU-bound задачах: вычислениях, image-processing, парсерах, ML-инференсе. Включается через opcache.jit_buffer_size и opcache.jit=tracing в php.ini. Важный апдейт PHP 8.4: числовая форма флага (тип 1255, 1235, 1101) объявлена deprecated - используйте именованные значения tracing / function / on / off / disable. JIT остаётся частью расширения OPcache, отдельного pecl-пакета не появилось.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 5,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как устроен сборщик циклических ссылок в PHP и когда он включается?',
                'answer' => 'Базовый механизм управления памятью - reference counting. Когда refcount достигает 0, zval освобождается немедленно. Проблема: циклические ссылки (a→b→a) держат refcount > 0 даже когда объекты недостижимы из кода - чистый refcount не справляется. Поэтому существует второй уровень: буфер "возможных корней" (potential cycle roots), куда попадают объекты, у которых уменьшился refcount, но не до нуля. Когда буфер заполняется (порог по умолчанию 10000), запускается mark-and-sweep алгоритм для циклических ссылок: помечает все достижимые узлы из этого буфера, удаляет недостижимые. Исторически в PHP 5.3 был алгоритм Bacon-Rajan (упоминается в старых статьях), но в PHP 7.3 Дмитрий Стогов полностью переписал GC - реализация стала намного быстрее и компактнее по памяти, хотя концептуально это всё ещё mark-and-sweep для циклов поверх refcounting. Принудительный запуск - gc_collect_cycles(). В долгоживущих PHP-процессах (Octane, queue:work) дополнительно вызывают gc_mem_caches() - возвращает кешированные арены памяти от Zend MM обратно в ОС.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 5,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'В чём разница между WeakMap, WeakReference и SplObjectStorage?',
                'answer' => 'SplObjectStorage хранит сильные ссылки - объект-ключ не освободится, пока хранилище живёт. WeakReference (PHP 7.4) - обёртка, не препятствующая GC, get() вернёт null после уборки. WeakMap (PHP 8.0) - ассоциативный массив со слабыми ключами: при удалении объекта запись исчезает автоматически. Используется для кэшей и метаданных, привязанных к объекту, без утечек.',
                'code_example' => '<?php
$cache = new WeakMap();
$user = new stdClass();
$cache[$user] = "expensive_payload";
unset($user);             // запись из WeakMap уйдёт автоматически',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Приведи практический пример утечки памяти, которую решает WeakMap',
                'answer' => 'Классический сценарий - кеширование вычисленных метаданных по объекту в долгоживущем процессе (Octane, queue:work, ReactPHP). Например, EventDispatcher запоминает прав доступа для каждого Request/User, чтобы не ходить в БД повторно при каждом fired event. Если кеш - обычный array со spl_object_id($user) или SplObjectStorage в качестве ключа, то ссылка на $user в кеше СИЛЬНАЯ: даже когда обработчик запроса завершён и нигде в коде $user больше не нужен, refcount остаётся > 0 - объект не освобождается, и через 100k запросов память кончается. С WeakMap ключ - слабая ссылка: как только закончился запрос и кончились сильные ссылки на $user, GC уничтожит и объект, и автоматически уберёт запись из WeakMap. Это правильный инструмент для "side-table" данных: метаданных, прав, ленивых вычислений, observer-паттерна (слушатели не должны мешать GC своих субъектов). Аналогичная проблема в JS: WeakMap используется для приватных полей и DOM-метаданных по той же причине.',
                'code_example' => '<?php
// ❌ УТЕЧКА в long-running процессе
class PermissionCacheBad
{
    private array $cache = []; // массив с object_id ключами

    public function for(User $user): array
    {
        $id = spl_object_id($user);
        return $this->cache[$id] ??= $this->compute($user);
        // ⚠️ $this->compute($user) может содержать $user
        // или ссылки на него - сильная ссылка остаётся в $cache
    }
}

// после 100k запросов:
// memory_get_usage() = 1 GB, OOM

// ✅ Без утечки благодаря WeakMap
class PermissionCacheGood
{
    private WeakMap $cache;

    public function __construct() { $this->cache = new WeakMap(); }

    public function for(User $user): array
    {
        return $this->cache[$user] ??= $this->compute($user);
    }
}

// $user из текущего запроса попадает в WeakMap;
// когда контроллер вернул response и $user вышел из scope,
// GC удаляет объект И запись из WeakMap - память стабильна.

// Реальный кейс: Symfony EventDispatcher, Doctrine UnitOfWork,
// Laravel Octane кешируют метаданные именно через WeakMap',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем отличается == от === и какие сюрпризы бывают на нестрогом сравнении в PHP 8+?',
                'answer' => '=== сравнивает тип и значение, == выполняет приведение типов. В PHP 8 поведение string vs number стало строже: "abc" == 0 теперь false (раньше true). Но "1abc" == 1 всё ещё true; "10" == "1e1" тоже true (оба числовые строки). Для null-safety и иммутабельности используйте ===, а для чисел - int-cast или явное приведение. Сравнение объектов по == проверяет класс и поля, а === - идентичность ссылки.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 3,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что произойдёт при new ClassName(...) для класса с конструктором, объявленным как private?',
                'answer' => 'Получите Error: Call to private ClassName::__construct(). Такой паттерн используется для именованных конструкторов и Singleton: класс предоставляет статические фабричные методы (fromArray, fromString), которые внутри вызывают new self(). Это позволяет инкапсулировать инвариант построения и иметь несколько способов создания с осмысленными именами.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 3,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работают атрибуты PHP 8 и чем они лучше PHPDoc-аннотаций?',
                'answer' => 'Атрибуты - это нативный синтаксис #[Attr(args)], который парсится компилятором и доступен через Reflection API без сторонних парсеров. У них есть таргеты (TARGET_CLASS, TARGET_METHOD), флаг IS_REPEATABLE и валидация аргументов, как у обычных классов. По сравнению с docblock-аннотациями: быстрее, безопаснее (нет регуляркой парсинга), IDE даёт автокомплит, типобезопасны.',
                'code_example' => '<?php
#[Attribute(Attribute::TARGET_METHOD)]
final class Route {
    public function __construct(public string $path, public string $method = "GET") {}
}
class Controller {
    #[Route("/users/{id}")]
    public function show(int $id) {}
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'В чём разница между abstract, interface и trait и когда выбирать что?',
                'answer' => 'Interface задаёт контракт без реализации, поддерживает множественную реализацию, не имеет состояния. Abstract class - частичная реализация плюс контракт, одиночное наследование, может иметь свойства. Trait - горизонтальное переиспользование кода (mixin), копируется в класс при компиляции, не образует тип. Интерфейс - для polymorphism, abstract - для шаблонного метода с общим состоянием, trait - для дублирующейся логики между несвязанными классами.',
                'code_example' => '<?php
// Interface - контракт без состояния, можно несколько
interface Logger { public function log(string $m): void; }
interface Cacheable { public function key(): string; }

// Abstract - общая логика + один контракт
abstract class Repository {
    public function __construct(protected PDO $db) {}
    abstract protected function table(): string;
    public function all(): array {
        return $this->db->query("SELECT * FROM {$this->table()}")->fetchAll();
    }
}

// Trait - mixin: копируется в класс
trait HasTimestamps {
    public ?int $createdAt = null;
    public function touch(): void { $this->createdAt = time(); }
}

class UserRepository extends Repository implements Logger {
    use HasTimestamps;
    protected function table(): string { return "users"; }
    public function log(string $m): void { /* ... */ }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое late static binding и зачем нужен static вместо self?',
                'answer' => 'self ссылается на класс, в котором написана строка - связывание раннее, на этапе компиляции. static связывается поздно, по фактическому классу вызова. Это критично для фабричных методов и наследования: new self() вернёт родителя даже из дочернего класса, new static() - нужный потомок. Также static используется для возвращаемого типа методов вроде fluent API.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 4,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что выведет код с замыканием, захватившим переменную по значению, если её изменить после создания замыкания?',
                'answer' => 'use ($var) копирует значение в момент создания closure - последующие изменения снаружи не видны внутри. use (&$var) захватывает по ссылке: видны изменения в обе стороны. PHP 7.4+ поддерживает arrow functions (fn() =>), которые автоматически захватывают by value все используемые переменные внешнего скоупа.',
                'code_example' => '<?php
$x = 1;
$byValue = function () use ($x) { return $x; };
$byRef   = function () use (&$x) { return $x; };
$arrow   = fn () => $x; // arrow тоже by value, в момент создания

$x = 99;
echo $byValue(); // 1   - захвачено старое значение
echo $byRef();   // 99  - актуальное (по ссылке)
echo $arrow();   // 1   - arrow зафиксировал значение при создании',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как реализованы enum в PHP 8.1 и чем backed-enum отличается от pure?',
                'answer' => 'Enum - это специальный объектный тип; кейсы - синглтоны, сравнение по === безопасно. Pure enum просто перечисление; backed enum имеет скалярный backing-тип (int|string), что даёт ::from()/::tryFrom() и автосериализацию. Enum может реализовывать интерфейсы, иметь методы и константы, но не имеет состояния (свойств). cases() возвращает все варианты в порядке объявления.',
                'code_example' => '<?php
enum Status: string {
    case Active = "active";
    case Banned = "banned";
    public function label(): string {
        return match($this) {
            self::Active => "Активен",
            self::Banned => "Забанен",
        };
    }
}
Status::from("active");',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое match-выражение и чем оно лучше switch?',
                'answer' => 'match сравнивает строго (===), возвращает значение, требует исчерпывающего покрытия (бросает UnhandledMatchError), не имеет fallthrough - каждая ветка завершается неявно. switch использует == и требует break, легко поймать баг с числовым строковым ключом. match - выражение, поэтому удобно присваивать в переменную или возвращать.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 3,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем PHP-FPM отличается от mod_php и как настраиваются пулы?',
                'answer' => 'mod_php встраивает интерпретатор в Apache-процесс, делая воркер тяжёлым и завязанным на веб-сервер. PHP-FPM - отдельный демон с FastCGI, общается с nginx/Apache по сокету, держит пулы воркеров. Стратегии pm: static (фиксированный пул), dynamic (масштабирует от min до max), ondemand (форкает по запросу, экономит память). pm.max_requests перезапускает воркер, чтобы избежать утечек.',
                'code_example' => '; pool.conf
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 1000',
                'code_language' => 'bash',
                'difficulty' => 4,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем отличается include от require и от autoload, и почему autoload предпочтительнее?',
                'answer' => 'include выдаёт warning при отсутствии файла и продолжает выполнение, require - fatal error. _once делает идемпотентным. Autoload (spl_autoload_register / Composer PSR-4) загружает классы лениво: только когда они впервые упоминаются. Это снижает время загрузки, поддерживает namespaces и работает с opcache. В современных проектах ручные include использовать не нужно - только bootstrap.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 3,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое SPL и какие структуры из неё реально полезны на собеседованиях?',
                'answer' => 'Standard PHP Library предоставляет специализированные структуры данных и итераторы. SplQueue/SplStack/SplDoublyLinkedList - связные списки с O(1) на голову/хвост. SplPriorityQueue - куча. SplObjectStorage - set/map для объектов. SplFixedArray - массив с числовыми индексами и фиксированным размером; немного экономит память по сравнению с обычным array (~1.1-1.3x на PHP 8 для int/string-значений - замерено через memory_get_usage), а не в 3-5 раз, как часто пишут (это легенда из эпохи PHP 5, когда HashTable был тяжёлым; в PHP 7+ packed array хранится как сплошной блок и почти догоняет SplFixedArray). Реальная польза SplFixedArray сегодня - жёсткая фиксация размера и невозможность нечисловых ключей, а не радикальная экономия памяти. Итераторы (RecursiveIteratorIterator, FilterIterator) дают компонуемые потоки.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 4,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работают типы never и void и в чём практическая разница?',
                'answer' => 'void - функция не возвращает ничего полезного, но завершается нормально. never (PHP 8.1) - функция никогда не возвращает: либо бросает исключение, либо вызывает exit/die/бесконечный цикл. Тип never используется анализаторами для exhaustiveness checking: компилятор знает, что код после вызова never-функции недостижим. Это чище, чем void для guard-функций вроде throwIfInvalid() и помогает type narrowing после ранних возвратов.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 4,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'В чём разница между __get/__set и реальными свойствами и какие подводные камни?',
                'answer' => 'Магические методы вызываются, когда обращение к свойству невозможно (отсутствует или недоступно по видимости). Они в разы медленнее прямых обращений, ломают статический анализ, IDE-автодополнение и type inference. На каждом __get создаётся фрейм. Их используют для прокси-объектов и lazy-loading, но в DDD предпочтительнее явные геттеры или public readonly. С isset() работают только если определён __isset().',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 3,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работает spread-оператор для массивов и именованных аргументов в PHP 8?',
                'answer' => '... разворачивает iterable в позиционные аргументы или элементы массива. PHP 8.1 разрешает разворачивать массивы со строковыми ключами - они становятся именованными аргументами. Это удобно для proxy/decorator: принять args, добавить/изменить и пробросить дальше. Также именованные аргументы делают вызовы с длинными сигнатурами читаемыми и устойчивыми к перестановке.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 3,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое first-class callable syntax (PHP 8.1) и зачем он нужен?',
                'answer' => 'Синтаксис $fn = strlen(...) или $obj->method(...) создаёт Closure из функции/метода без строкового имени. По сравнению со старым [$obj, "method"] и "strlen" - типобезопасно, поддерживает рефакторинг IDE, и, что важно, ловит ошибки опечаток на этапе компиляции. Удобно для array_map, pipeline и DI-резолверов.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 3,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем mb_* функции отличаются от обычных строковых и когда это критично?',
                'answer' => 'strlen, substr, strtolower работают побайтово. Для UTF-8 один кириллический символ - 2 байта, эмодзи - 4. mb_* функции учитывают кодировку и возвращают длину/срез в символах. Использование strlen для валидации длины пароля или substr для превью текста - частый источник багов и mojibake. Дефолтную кодировку для mb_* функций задают через ini default_charset=UTF-8 (актуальная общая настройка кодировки PHP, которой следуют mbstring/htmlspecialchars/etc) или явно вызовом mb_internal_encoding("UTF-8") в bootstrap. Старая ini mbstring.internal_encoding deprecated с PHP 5.6 - не используйте её в новых проектах.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 3,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как защититься от инъекций при сборке SQL вручную и почему prepared statements решают проблему?',
                'answer' => 'Prepared statements отделяют шаблон запроса от данных: драйвер парсит SQL один раз и подставляет значения как параметры на стороне сервера. Это исключает интерпретацию пользовательского ввода как кода. Эмулированные prepares (PDO::ATTR_EMULATE_PREPARES=true) на самом деле подставляют значения в шаблон на стороне клиента - безопасно от инъекций (PDO правильно экранирует), но теряются проверки типов и planning-cache. По умолчанию обычно лучше native prepares (быстрее, типобезопаснее). ИСКЛЮЧЕНИЕ: при работе через PgBouncer в transaction pooling режиме native prepared statements ломаются (server-side PREPARE привязан к физическому соединению, которое PgBouncer передаёт другому клиенту между PREPARE и EXECUTE) - там нужен либо EMULATE_PREPARES=true, либо PgBouncer 1.21+/1.22+ с max_prepared_statements > 0. См. отдельную карточку про PgBouncer + Laravel.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 3,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое stream wrappers и как с их помощью читать gzip "на лету"?',
                'answer' => 'Stream wrapper - абстракция над источником данных с единым API fopen/fread/fwrite. PHP включает file://, http://, php://memory, а также compression-фильтры compress.zlib://, php://filter. Можно регистрировать свои через stream_wrapper_register. Это позволяет читать удалённые файлы, шифровать на лету и обрабатывать большие архивы потоково.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 4,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое preloading в PHP 7.4+ и какие у него ограничения?',
                'answer' => 'Preloading загружает указанные файлы в opcache при старте PHP-FPM master-процесса и навсегда держит их в памяти. Эти классы доступны во всех воркерах без файловой проверки, что даёт +5-15% к скорости старта запроса. Ограничения: при изменении preloaded-файла нужен полный рестарт FPM, нельзя использовать с runtime-кодом, скрипт исполняется в контексте master.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 5,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'В чём разница между composer.json и composer.lock и какой из них коммитить?',
                'answer' => 'composer.json описывает требуемые пакеты с диапазонами версий (например ^8.2) и метаданные проекта, а composer.lock фиксирует точные разрешённые версии и хеши, которые установит composer install. Коммитить нужно оба: lock гарантирует воспроизводимый билд на CI и у других разработчиков, а composer update переустанавливает пакеты в рамках ограничений из json и обновляет lock.',
                'difficulty' => 2,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Зачем в коде логина вызывать password_needs_rehash() после успешной проверки?',
                'answer' => 'Алгоритмы и параметры хеширования со временем устаревают: cost у bcrypt поднимают, на проект могут перевести с PASSWORD_BCRYPT на PASSWORD_ARGON2ID. password_needs_rehash() сравнивает параметры существующего хеша с текущим конфигом и говорит, надо ли перехешировать. Типичный приём — после password_verify() проверить needs_rehash и, если да, прозрачно для пользователя пересохранить его пароль с новыми параметрами. Так база постепенно мигрирует на актуальные настройки без принудительного сброса паролей.',
                'difficulty' => 3,
                'topic' => 'php.composer_autoload',
            ],
        ];
    }
}
