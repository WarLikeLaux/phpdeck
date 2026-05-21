<?php

namespace Database\Seeders\Data\Categories\Php;

class BasicQa
{
    public static function all(): array
    {
        return [
            [
                'category' => 'PHP',
                'question' => 'Чем отличается print_r от var_dump и var_export?',
                'answer' => 'Три функции для **визуализации значений**, каждая под свою задачу.

- **`var_dump`** — показывает **тип И значение**: `string(4)`, `int(30)`, `bool(true)`. Лучший выбор для **отладки**, особенно вложенных структур — видны типы каждого поля.
- **`print_r`** — «читаемый» вид, **без типов**: `bool true` выглядит как `1`. Удобно для быстрого взгляда на массив.
- **`var_export`** — выводит **валидный PHP-код**, который можно скопировать обратно в исходник. Идеально для **генерации файлов конфигурации**.

**Все три** принимают второй параметр **`true`** — возвращают строку вместо вывода (для `var_dump` — с PHP 5.4 через output buffering, аналога нет; правильно — `print_r`/`var_export` с `true`).

**На проде:** никогда не дампи в браузер. Используй логи (`Log::info()` в Laravel) или **Xdebug** для отладки.',
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
                'topic' => 'php.basic_qa',
            ],
            [
                'category' => 'PHP',
                'question' => 'Можно ли менять readonly-свойство из метода того же класса? А из наследника? Чем это отличается от private?',
                'answer' => '**`readonly`** vs **`private`** — частая путаница на собесе.

| Свойство | **`private`** | **`readonly`** (PHP 8.1+) |
|---|---|---|
| Ограничивает | **видимость** | **запись** |
| Чтение из своего класса | да, сколько угодно | да, сколько угодно |
| Запись из своего класса | **да, многократно** | **ровно один раз** |
| Чтение снаружи | нет | **да** (если `public readonly`) |
| Запись из наследника | да (если `protected`) | **нет**, scope записи у объявившего класса |

**Точные правила `readonly`:**

1. Запись разрешена **из любого метода того же класса** — **не только из конструктора**
2. После **первой** записи **любая** последующая → **`Error`**
3. Из **наследника** записать нельзя, даже если свойство `protected readonly`

**Исключение — `__clone()`** (PHP 8.3+):
- внутри `__clone()` того класса, где свойство **объявлено**, разрешена переинициализация
- нужно для **deep-clone**: `$this->total = clone $this->total;`
- снаружи `__clone()` запись по-прежнему `Error`

**До 8.3** «обновлённую» копию делали **wither**-методом:
```php
public function withName(string $name): self {
    return new self($name, $this->age);
}
```

**Подводный камень:** `readonly` не делает значение **deep-immutable**. Если внутри `readonly object`, его свойства можно менять. Глубокая иммутабельность — это уже `readonly class` (PHP 8.2) **плюс** value-objects во всех полях.',
                'code_example' => '<?php
class User {
    public function __construct(public readonly string $name) {}

    public function init(): void {
        // ✅ Можно из метода ОБЪЯВИВШЕГО класса до первой записи
        // НО $name уже был записан в конструкторе → Error
        // $this->name = "new";
    }
}

class Admin extends User {
    public function rename(): void {
        // ❌ Из наследника — Error: cannot modify readonly property
        // $this->name = "admin";
    }
}

$u = new User("Иван");
// $u->name = "X"; // Error: readonly',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.basic_qa',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое PHP простыми словами и где он применяется?',
                'answer' => '**PHP** — серверный скриптовый язык программирования с открытым исходным кодом, изначально созданный для генерации HTML-страниц на сервере.

**Как работает:** сервер исполняет PHP-код и отдаёт браузеру уже **готовый HTML** — сам код в браузер не попадает.

**Где применяется сегодня:**
- веб-сайты (WordPress, Wikipedia, e-commerce, корпоративные порталы)
- REST/GraphQL **API**
- **CLI-утилиты**, очереди, демоны
- десктопные приложения (через **NativePHP**)

**Самые популярные фреймворки** — **Laravel** и **Symfony**.

**Причины популярности:** динамическая типизация, низкий порог входа, огромная экосистема пакетов через **Composer**.',
                'difficulty' => 1,
                'topic' => 'php.basic_qa',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем интерпретируемый язык отличается от компилируемого и куда относится PHP?',
                'answer' => '**Компилируемые** (`C`, `Rust`, `Go`) — исходник заранее **переводится компилятором в машинный код**, на проде запускается уже бинарник.

**Интерпретируемые** (`Python`, `Ruby`, классический JS) — исходник **исполняется интерпретатором** во время запуска.

**PHP — гибрид:**
1. Исходник **компилируется в байткод** Zend Engine при каждом запросе
2. Виртуальная машина **исполняет байткод**

**Оптимизации:**
- **OPcache** — кеширует байткод в памяти между запросами, чтобы не парсить файлы заново (включён по умолчанию с PHP 5.5)
- **JIT** (PHP 8.0+) — компилирует «горячий» байткод в нативный машинный код, ускоряет CPU-bound задачи (для типичных web-приложений выигрыш небольшой)',
                'difficulty' => 2,
                'topic' => 'php.basic_qa',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое Xdebug и для чего он нужен?',
                'answer' => '**Xdebug** — PHP-расширение для **отладки** и **профилирования**.

**Основные возможности:**
- **Step-debugging** в IDE (PhpStorm, VS Code) через протокол **DBGp**: точки останова, осмотр переменных, шаг внутрь/над, watch
- **Красивые трассировки** исключений (с локальными переменными и подсветкой)
- **Code coverage** для PHPUnit (`--coverage-html`)
- **Профилирование** — отчёт в формате Cachegrind, открывается в KCachegrind/Webgrind

**Режимы** (PHP 8+) — через `xdebug.mode` в `php.ini`:
- `debug` — пошаговая отладка
- `coverage` — покрытие тестами
- `profile` — профилирование
- `develop` — улучшенные сообщения об ошибках

**Внимание:** в проде Xdebug **никогда не включают** — он сильно замедляет PHP.',
                'difficulty' => 2,
                'topic' => 'php.basic_qa',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое встроенный сервер разработки PHP и для чего он нужен?',
                'answer' => '**`php -S host:port`** — встроенный **однопроцессный** веб-сервер. Запускается одной командой, **не нужно** ставить Apache, Nginx или Docker.

**Параметры:**
- **`-t <dir>`** — «document root» (откуда отдавать файлы)
- последним аргументом — **router-скрипт** (single-entry-point, как `public/index.php` в Laravel)

**Когда использовать:** быстрые демо, локальная разработка маленьких скриптов, прототипы.

**Почему не подходит для прода:**
- **однопоточный** — обрабатывает запросы **последовательно** (один зависший запрос блокирует всех)
- нет тонкого тюнинга, кэширования, защиты
- нет HTTPS из коробки

**В Laravel** обёртка — **`php artisan serve`** (запускает `php -S` с роутером).',
                'code_example' => '# Поднять сервер на 8000 порту с корнем public/
php -S localhost:8000 -t public

# С роутером (single-entry-point — как Laravel)
php -S 0.0.0.0:8000 -t public public/index.php

# Laravel-обёртка
php artisan serve',
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'php.basic_qa',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое PHPStan и Psalm и зачем они нужны?',
                'answer' => '**PHPStan** и **Psalm** — инструменты **статического анализа** PHP-кода. Читают исходники и **PHPDoc-блоки**, находят ошибки **без запуска программы**.

**Что ловят:**
- ошибки **типов** (`int` передали в `string`-параметр)
- вызовы **несуществующих** методов/функций
- обращение к **возможно null** свойствам без проверки
- **недостижимый код** после `throw`/`return`
- **неверные сигнатуры** при переопределении
- забытые `return`, нарушение covariance/contravariance
- **assert-overlap** (`if ($x === null) ... if ($x !== null)`)

**Уровни строгости:**
- PHPStan: **0–9** (max), плюс `bleedingEdge`
- Psalm: `--show-info`, плюс таксономия `MixedAssignment`, `TaintedInput`, etc.

**Преимущества:**
- работает **в CI/IDE** без рантайма
- **никакого overhead** в проде
- ловит баги, которые тесты пропускают (особенно типы и null)

**Generics через PHPDoc:**
```php
/**
 * @template T
 * @param class-string<T> $class
 * @return T
 */
function make(string $class) { return new $class(); }
```
Это даёт уровень **TypeScript** без изменения языка.

**Сравнение:**

| | **PHPStan** | **Psalm** |
|---|---|---|
| Автор | Ondřej Mirtes | Vimeo |
| Уровни | 0–9 | discrete issue types |
| Скорость | очень быстрый | средняя |
| Taint analysis (для security) | через расширение | встроенный |
| Расширения | много (`larastan`, `phpstan-strict-rules`) | плагины |

В Laravel-проектах чаще ставят **PHPStan + `larastan`**.',
                'difficulty' => 3,
                'topic' => 'php.basic_qa',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое NativePHP?',
                'answer' => '**NativePHP** — фреймворк, упаковывающий PHP-приложение (чаще всего на **Laravel**) в **нативное десктопное / мобильное** приложение.

**Под капотом:**
- **Desktop** — обёртка вокруг **Electron** (Chromium + Node.js): PHP-runtime поднимается как локальный воркер, фронт — HTML/CSS/JS из приложения.
- **Mobile** (бета) — `iOS` / `Android` через native-bridge.

**Что даёт:**
- доступ к **файловой системе**, **окнам**, **меню**, **трею**, системным уведомлениям через PHP-API.
- работу с **локальной БД** (SQLite по умолчанию), очередями, шедулером — всё прямо как в Laravel.
- сборку через `php artisan native:build` в **`.dmg` / `.exe` / `.AppImage`**.

**Когда брать:**
- хотите переиспользовать **Laravel-кодовую базу** для desktop-утилит.
- не нужен жёсткий native-feel или предельная производительность (Electron-overhead).

**Альтернативы для GUI на PHP:** `PHP-GTK` (заброшен), `php-cpp + Qt` (низкоуровнево).',
                'difficulty' => 3,
                'topic' => 'php.basic_qa',
            ],
            [
                'category' => 'PHP',
                'question' => 'Какие имена в PHP регистрозависимы, а какие нет?',
                'answer' => '**Регистронезависимы** (PHP игнорирует регистр):
- имена **функций** и **методов** — `strlen() == STRLEN()`
- имена **классов** — `new User` == `new USER`
- языковые конструкции и литералы — `echo`, `IF`, `true`, `NULL`

**Регистрозависимы** (`$user` и `$User` — **разные**):
- **переменные** и параметры
- **ключи массивов** (`$a["name"]` ≠ `$a["NAME"]`)
- **константы** — `PHP_EOL` ≠ `php_eol` (если только `define` без флага `true` — второй аргумент `case_insensitive` deprecated с PHP 7.3)

**Стиль (PSR-1 / PSR-12):**
- классы — `PascalCase`
- методы и переменные — `camelCase`
- константы — `UPPER_SNAKE_CASE`

Не полагайся на регистронезависимость — пиши единообразно.',
                'code_example' => '<?php
function hello() { echo "hi"; }
HELLO();      // ОК — функции регистронезависимы

$user = 1;
echo $User;   // Warning: Undefined variable — переменные регистрозависимы

define("MAX", 10);
echo MAX;     // 10
// echo max;  // Warning — это не та же константа',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.basic_qa',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем unset() отличается от unlink() в PHP?',
                'answer' => 'Похожие имена — **совершенно разные функции**, классическая ловушка собеса.

- **`unset($x)`** — языковая конструкция, работает с переменными **в памяти**:
  - убирает переменную, элемент массива (`unset($arr["key"])`) или свойство объекта
  - под капотом уменьшает refcount; когда refcount достигает 0 — память освобождается
  - **ничего не делает с файлами**

- **`unlink($path)`** — функция файловой системы:
  - **удаляет файл с диска** по указанному пути
  - возвращает `bool` (`true` при успехе)
  - для пустой папки — `rmdir()`; рекурсивно — через `RecursiveIteratorIterator`

**Связывает их только** то, что оба «что-то удаляют», но в разных слоях: один в RAM, второй на диске.',
                'code_example' => '<?php
$user = ["name" => "Иван", "age" => 30];
unset($user["age"]);          // удалили ключ из массива в памяти
unset($user);                 // удалили саму переменную

unlink("/tmp/cache.json");    // удалили файл с диска

// Удалить директорию — rmdir() (только пустую) или рекурсивно через RecursiveIteratorIterator',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.basic_qa',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое .env-файл и зачем он нужен в PHP-проектах?',
                'answer' => '**`.env`** хранит **конфигурацию окружения** (адрес БД, ключи API, режим отладки) в виде `KEY=value`, **отдельно от кода**.

**Правила работы с ним:**
- кладут в `.gitignore` — **не коммитят**
- в репозиторий коммитят шаблон **`.env.example`** без секретов
- читают через переменные окружения: `getenv`, `$_ENV`, в Laravel — `env("KEY")`
- библиотека для загрузки — **`vlucas/phpdotenv`**

**Зачем:** один и тот же код запускается **локально, на staging и в проде** с разными настройками без правки исходников. Секреты (пароли, токены) лежат только в `.env` конкретного сервера и никогда не попадают в git.',
                'code_example' => '# .env (на сервере, в .gitignore)
APP_ENV=production
APP_DEBUG=false
DB_HOST=127.0.0.1
DB_DATABASE=shop
DB_USERNAME=app
DB_PASSWORD=super_secret
STRIPE_KEY=sk_live_...

# .env.example (в git, без секретов — шаблон)
APP_ENV=local
APP_DEBUG=true
DB_HOST=127.0.0.1
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
STRIPE_KEY=',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'php.basic_qa',
            ],
            [
                'category' => 'PHP',
                'question' => 'Какие риски возникают при запуске PHP в режиме долгоживущего воркера (Swoole/RoadRunner)?',
                'answer' => 'Главные риски — утечки памяти и загрязнение глобального состояния. Скрипт больше не умирает в конце запроса, поэтому статические свойства, синглтоны и неосвобождённые ресурсы (PDO-соединения, файловые дескрипторы) накапливаются от запроса к запросу. Данные одного пользователя могут просочиться в обработчик другого, если контейнер DI или Auth не сбрасываются между запросами. Фатальная ошибка валит весь воркер, а не один запрос, и обновление кода требует перезапуска воркеров. Решения: лимит max_requests на воркер, явное обнуление состояния, аккуратное управление пулом соединений.',
                'difficulty' => 4,
                'topic' => 'php.basic_qa',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что даёт PDO::ATTR_PERSISTENT и какие у него подводные камни?',
                'answer' => 'PDO::ATTR_PERSISTENT держит TCP-соединение с БД открытым между запросами FPM-воркера, экономя время на handshake и аутентификации, что заметно на коротких запросах под высокой нагрузкой. Платой становятся побочные эффекты: незакрытая транзакция, сессионные переменные, временные таблицы, изменённый search_path или sql_mode переходят к следующему запросу, использующему то же соединение. Можно упереться в max_connections СУБД, потому что соединения не освобождаются по завершении скрипта. Поэтому persistent включают, только если состояние соединения предсказуемо и есть мониторинг пула.',
                'difficulty' => 4,
                'topic' => 'php.basic_qa',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как ведут себя статические свойства в иерархии наследования?',
                'answer' => '**Главное правило:** статическое свойство принадлежит **классу, в котором объявлено**, и **разделяется со всеми наследниками** — пока его явно не переопределили.

**Что это значит на практике:**
- `Child::$prop = 5` и `Parent::$prop` читают **одну ячейку памяти**.
- Несколько наследников **видят одно и то же значение** — изменение одним «протекает» в других.
- Чтобы у наследника было **своё хранилище**, в нём нужно объявить `public static $prop;` заново.

**`self::` vs `static::` при доступе:**

| Конструкция | К чему обращается | Где использовать |
|---|---|---|
| **`self::$prop`** | свойство **класса, где написан код** (early binding) | когда хочешь именно родительское хранилище |
| **`static::$prop`** | свойство **вызывающего класса** (LSB, late static binding) | когда хочешь, чтобы наследник «получил своё» |

**Типичные баги:**
- **Singleton-регистр** через `self::$instance` в родителе — все наследники получат **один** instance.
- В тестах: статическое свойство, не сброшенное в `tearDown`, утекает в следующий тест → flaky-тесты.
- **Долгоживущий воркер** (Octane / RoadRunner / Swoole) — статика живёт **между запросами** и копит мусор.

**Защита:** избегать мутабельной статики; для регистров — DI-контейнер; в тестах — явный `reset()` в `setUp/tearDown`.',
                'code_example' => '<?php
class Counter {
    public static int $count = 0;
}

class A extends Counter {}
class B extends Counter {}

A::$count = 5;
echo B::$count;        // 5 — общее хранилище!
echo Counter::$count;  // 5

// Чтобы разделить — переопределить
class C extends Counter {
    public static int $count = 0;  // своё хранилище
}
C::$count = 10;
echo Counter::$count;  // 5 — не задело

// self vs static
class Base {
    public static string $name = "Base";
    public static function whoSelf(): string   { return self::$name; }
    public static function whoStatic(): string { return static::$name; }
}
class Sub extends Base {
    public static string $name = "Sub";
}
echo Sub::whoSelf();    // "Base" — early binding
echo Sub::whoStatic();  // "Sub"  — LSB',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.basic_qa',
            ],
        ];
    }
}
