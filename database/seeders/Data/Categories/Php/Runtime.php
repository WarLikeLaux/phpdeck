<?php

namespace Database\Seeders\Data\Categories\Php;

class Runtime
{
    public static function all(): array
    {
        return [
            [
                'category' => 'PHP',
                'question' => 'Что такое сборщик мусора (GC) в PHP?',
                'answer' => '**Двухуровневая модель памяти PHP:**

**1. Reference counting (основной механизм):**
- каждый `zval` имеет **`refcount`** — счётчик ссылок
- при `unset($x)` / выходе из scope refcount уменьшается
- когда **`refcount === 0`** → память **освобождается немедленно**
- работает за **O(1)** на присваивании, без пауз

**2. Циклический GC (mark-and-sweep для циклов):**
- проблема: при **циклических ссылках** (`A → B → A`) refcount **не доходит до 0**, объекты недостижимы из кода, но память не освобождается
- решение: PHP собирает **возможные корни циклов** в буфер
- при заполнении буфера (порог **`gc_threshold = 10000`** по умолчанию) запускается **mark-and-sweep** только по этому буферу
- история: в **PHP 7.3** Дмитрий Стогов **переписал** GC — стал намного быстрее и компактнее

**Управляющие функции:**

| Функция | Что делает |
| --- | --- |
| **`gc_enable()` / `gc_disable()`** | включить/выключить цикл-GC |
| **`gc_enabled(): bool`** | проверить статус |
| **`gc_collect_cycles(): int`** | принудительно запустить + вернуть число собранных |
| **`gc_status(): array`** | статистика: `runs`, `collected`, `threshold`, `roots` |
| **`gc_mem_caches(): int`** | вернуть кешированные арены Zend MM в **ОС** (полезно в long-running) |

**В долгоживущих процессах (Octane, queue:work, ReactPHP):**
- между запросами стоит вызывать **`gc_collect_cycles()` + `gc_mem_caches()`**
- отслеживать `gc_status()["runs"]` — частые срабатывания сигнализируют о циклах в коде',
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
                'topic' => 'php.runtime',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работает copy-on-write для массивов и строк в PHP и когда он перестаёт работать?',
                'answer' => '**Базовый механизм:** PHP хранит значения в **`zval`** со счётчиком **`refcount`**.
- при присваивании `$b = $a` → **`refcount++`**, физическая копия **НЕ делается**
- глубокое копирование (**separation**) выполняется **только при первой записи** в одну из связанных переменных
- если ни одна сторона **не пишет** — копии не будет никогда

**К чему применяется CoW:**

| Тип | CoW? | Почему |
| --- | --- | --- |
| **`array`** | **да** | hashtable копируется при записи |
| **`string`** | **да** | буфер копируется при записи |
| **`int` / `float` / `bool` / `null`** | **нет** (PHP 7+) | zval 16 байт — копировать дешевле, чем refcount++ |
| **`object`** | **нет** | переменная держит **handle** (object id) — `$b = $a` копирует handle, оба имени указывают на **тот же** объект; «копия» появляется **только при `clone`** |
| **`resource`** | **нет** | handle-семантика, как у объектов |

**Когда CoW «ломается» (separation не происходит как обычно):**

- **`$c = &$a`** — теперь `$a` и `$c` имеют **общий zval с флагом `is_ref`**; запись через любой алиас **видна через все**, копии нет
- **`function f(array &$arr)`** — передача по ссылке, изменения видны снаружи без копирования
- **`use (&$v)`** в closure — захват по ссылке, тот же эффект
- **`foreach ($arr as &$v)`** — итерация по ссылкам отключает CoW для `$v`

**Подкапотный нюанс:**
- `refcount` хранится в **zend_refcounted_h** структуре
- скаляры PHP 7+ — в zval **inline** (нет указателя), поэтому им refcount не нужен
- из-за этого `array_sum`, `range`, целочисленный `foreach` на скалярах **существенно** быстрее, чем на эре PHP 5',
                'code_example' => '<?php
$a = range(1, 1_000_000);     // 1 zval, ~32 MB
$b = $a;                       // refcount=2, копии нет (ровно тот же zval)
echo memory_get_usage();       // не вырос

$b[0] = 0;                     // separation: $b отделяется в отдельный zval
echo memory_get_usage();       // ~64 MB — теперь две копии

$c = &$a;                      // CoW отключён для пары $a/$c (is_ref=1)
$c[0] = 999;
echo $a[0];                    // 999 — изменение видно через оба имени

// Объект — handle-семантика, CoW не нужен
$o1 = new stdClass();
$o1->x = 1;
$o2 = $o1;                     // копируется только handle
$o2->x = 42;
echo $o1->x;                   // 42 — один и тот же объект

$o3 = clone $o1;               // явная копия → отдельный объект
$o3->x = 0;
echo $o1->x;                   // 42 — не задело',
                'code_language' => 'php',
                'difficulty' => 5,
                'topic' => 'php.runtime',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что делает OPcache и почему важна opcache.validate_timestamps в проде?',
                'answer' => '**OPcache** — встроенное расширение PHP, кеширующее **скомпилированный байткод** скриптов в **shared memory** (SHM) **между запросами**.

**Что отжимает в плюс:**
- без OPcache **каждый запрос** = lex + parse + compile **всех включённых файлов**
- с OPcache байткод берётся из SHM — **2-5× ускорение** на типовых сайтах

**Ключ — `opcache.validate_timestamps`:**

| Значение | Поведение | Когда |
| --- | --- | --- |
| **`1`** (default) | при каждом запросе проверять `mtime` файлов и переcompile при изменении | **dev** |
| **`0`** | **никогда** не проверять — байткод фиксируется до **`opcache_reset()`** или перезапуска FPM | **prod** |

**Почему `0` в проде:**
- проверка `mtime` каждого подключаемого файла = **stat-call в ядро** на запрос × сотни файлов = заметная нагрузка на FS
- в проде файлы меняются **только при деплое** — никаких stat-ов между не нужно

**При деплое со `validate_timestamps=0` обязательно:**
1. **`opcache_reset()`** через CLI/admin-endpoint **или**
2. **`systemctl reload php-fpm`** (graceful) **или**
3. передеплой с **atomic symlink switch** + перезапуск FPM (zero-downtime)

**Остальные критичные настройки прода:**

| Опция | Зачем |
| --- | --- |
| **`opcache.memory_consumption`** | размер SHM в МБ (по умолчанию 128 — мало для крупных проектов) |
| **`opcache.max_accelerated_files`** | лимит количества файлов в кеше (поднимают до 20-50k) |
| **`opcache.interned_strings_buffer`** | дедупликация строк-литералов (имена методов, классов) |
| **`opcache.preload`** | preloading в master при старте FPM (PHP 7.4+) |
| **`opcache.jit_buffer_size`** | размер буфера для JIT (PHP 8+) |
| **`opcache.revalidate_freq`** | если уж `validate_timestamps=1`, как часто проверять (сек) |

**Мониторинг:** `opcache_get_status()` — `memory_usage`, `hits` / `misses`, `oom_restarts`, `cached_scripts`. Если **`oom_restarts > 0`** — поднимайте `memory_consumption`.',
                'code_example' => '; production php.ini
opcache.enable=1
opcache.enable_cli=0                       ; CLI отдельный SAPI, обычно не нужен
opcache.validate_timestamps=0              ; критично для скорости
opcache.memory_consumption=256             ; в МБ
opcache.max_accelerated_files=20000
opcache.interned_strings_buffer=16
opcache.preload=/var/www/preload.php
opcache.preload_user=www-data
opcache.jit_buffer_size=128M
opcache.jit=tracing',
                'code_language' => 'bash',
                'difficulty' => 5,
                'topic' => 'php.runtime',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работает JIT в PHP 8 и в каких задачах он реально ускоряет?',
                'answer' => '**JIT (PHP 8.0+)** — компилирует **горячий байткод** в **нативный машинный код** через **DynASM**. Часть расширения **OPcache** (отдельного pecl-пакета нет).

**Два режима компиляции:**

| Режим | Что делает | Когда быстрее |
| --- | --- | --- |
| **`function`** | компилит **функцию целиком** при первом вызове | предсказуемые горячие функции |
| **`tracing`** (предпочтительный) | собирает **трассы исполнения**, оптимизирует **горячие пути** включая ветки | реальная нагрузка с типизацией |

**Где JIT даёт реальный выигрыш:**
- **CPU-bound** задачи: численные расчёты, ML-инференс, парсеры, image-processing
- **тесные циклы** с типизированными скалярами
- бенчмарки вроде Mandelbrot — **2-3× ускорение**

**Где JIT почти не помогает:**
- **типичный web** (Laravel, Symfony): bottleneck в **IO** (БД, Redis, HTTP), CPU занят 5-15% времени запроса
- **много вызовов** разных небольших методов — overhead на трассировку ≈ выигрыш
- код с большим количеством **`mixed`** и без type-hint — JIT не может сделать assumptions

**Включение в `php.ini`:**

```ini
opcache.enable=1
opcache.jit_buffer_size=128M
opcache.jit=tracing             ; имя режима, не число
```

**Важно про флаги:**
- **числовая форма** (`1255`, `1235`, `1101`) — **deprecated с PHP 8.4**
- использовать **именованные значения**: `tracing`, `function`, `on`, `off`, `disable`
- `disable` — нельзя включить даже из ini-файла userdir; `off` — можно динамически

**Подкапотные нюансы:**
- JIT хранит машинный код в **отдельном буфере SHM** (`jit_buffer_size`)
- использует **observed types** — если ваша функция получает только `int`, JIT выпустит код для int; первый вызов со `string` сделает **deoptimization** (возврат в интерпретатор)
- профилирование результата — `opcache_get_status()["jit"]`',
                'difficulty' => 5,
                'topic' => 'php.runtime',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как устроен сборщик циклических ссылок в PHP и когда он включается?',
                'answer' => '**Двухуровневая модель управления памятью:**

**Уровень 1 — Reference counting:**
- **`refcount === 0`** → `zval` **освобождается немедленно**
- **O(1)** на каждом присваивании, **без stop-the-world** пауз
- покрывает **99%** аллокаций в обычном PHP-коде

**Уровень 2 — циклический GC (mark-and-sweep):**
- проблема: `A → B → A` держит `refcount > 0`, но из кода **недостижим**
- решение: **буфер «possible cycle roots»** — туда попадают объекты, у которых **refcount уменьшился, но не до нуля** (потенциально часть цикла)
- порог буфера **по умолчанию 10 000 узлов**; при заполнении запускается **mark-and-sweep**
- алгоритм метит достижимые из буфера, удаляет **недостижимые** циклы

**История реализации:**
- **PHP 5.3** — алгоритм Bacon-Rajan (медленный, прожорливый)
- **PHP 7.3** — Дмитрий Стогов **переписал** GC: меньше памяти, в разы быстрее; концептуально всё ещё mark-and-sweep
- **PHP 8.0+** — улучшения для JIT и Fibers

**Управление из user-space:**

| Функция | Что делает |
| --- | --- |
| **`gc_enable()`** / **`gc_disable()`** | включить/выключить циклический GC |
| **`gc_collect_cycles(): int`** | запустить **немедленно**, вернуть число собранных циклов |
| **`gc_mem_caches(): int`** | вернуть кешированные **арены Zend MM** в **ОС** — RSS падает |
| **`gc_status(): array`** | `runs`, `collected`, `threshold`, `roots`, `protected`, `full` |

**В долгоживущих процессах (Octane, RoadRunner, queue:work, ReactPHP):**
- между запросами вызывать **`gc_collect_cycles() + gc_mem_caches()`**
- мониторить `gc_status()["runs"]` — частые срабатывания = циклы в коде (стоит их разорвать через **`WeakReference`** / **`WeakMap`**)
- для **batch-job-ов** иногда выгоднее `gc_disable()` на время прогона и один `gc_collect_cycles()` в конце',
                'difficulty' => 5,
                'topic' => 'php.runtime',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем PHP-FPM отличается от mod_php и как настраиваются пулы?',
                'answer' => '**Сравнение моделей:**

| | **`mod_php`** | **PHP-FPM** |
| --- | --- | --- |
| Что это | модуль **внутри** Apache | **отдельный демон** FastCGI |
| Связь с веб-сервером | встроен в процесс Apache | сокет (UNIX/TCP) |
| Совместимость | только Apache **`prefork`** MPM | **nginx**, Apache `event`, Caddy |
| Изоляция пулов под разных пользователей | плохая | **`user/group`** на пул |
| Переиспользование воркера | да, но связан с Apache | да, независимо |
| Перезапуск без даунтайма | сложно | **`reload`** (SIGUSR2) |

**Стратегии менеджмента воркеров (`pm = ...`):**

| Режим | Поведение | Когда брать |
| --- | --- | --- |
| **`static`** | **фиксированный** пул в `pm.max_children` процессов | стабильная нагрузка, известная capacity |
| **`dynamic`** | масштабирует от `min_spare_servers` до `max_spare_servers`, верх — `max_children` | переменная нагрузка (стандарт) |
| **`ondemand`** | форкает воркер **только на запрос**, убивает после `process_idle_timeout` | малая нагрузка, экономия RAM |

**Ключевые параметры пула:**

| Параметр | Что делает |
| --- | --- |
| **`pm.max_children`** | потолок процессов = **потолок параллелизма** |
| **`pm.start_servers`** | сколько форкнуть сразу при старте FPM |
| **`pm.min/max_spare_servers`** | окно «свободных» в dynamic |
| **`pm.max_requests`** | сколько запросов воркер обработает до **graceful-respawn** — защита от утечек памяти |
| **`request_terminate_timeout`** | hard-kill долго висящего запроса |
| **`pm.process_idle_timeout`** | в `ondemand` — через сколько прибить простаивающий воркер |
| **`slowlog`** / **`request_slowlog_timeout`** | логировать backtrace при долгих запросах |

**Capacity-планирование:**
- `pm.max_children` ≈ `(доступная_RAM − ОС − Redis/MySQL) / средний_размер_воркера`
- средний воркер с Laravel ≈ **60-150 МБ** RSS
- ставить **выше** реального RPS бессмысленно — упрётесь в БД/CPU
- мониторить `listen.backlog` и `accepted conn` через FPM status-страницу',
                'code_example' => '; pool.conf (production-готовые значения)
[www]
user  = www-data
group = www-data
listen = /run/php/php8.4-fpm.sock
listen.owner = www-data
listen.group = www-data

pm = dynamic
pm.max_children       = 50
pm.start_servers      = 10
pm.min_spare_servers  = 5
pm.max_spare_servers  = 20
pm.max_requests       = 1000           ; graceful-respawn от утечек

; защита от висящих запросов
request_terminate_timeout = 30s
request_slowlog_timeout    = 5s
slowlog                    = /var/log/php-fpm-slow.log

; статус и health-check
pm.status_path = /fpm-status
ping.path      = /fpm-ping
ping.response  = pong',
                'code_language' => 'bash',
                'difficulty' => 4,
                'topic' => 'php.runtime',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое preloading в PHP 7.4+ и какие у него ограничения?',
                'answer' => '**Preloading (PHP 7.4+)** — загружает указанные **классы и функции в OPcache при старте FPM-мастера** и держит их в SHM **навсегда**.

**Что выигрываем:**
- доступны во **всех воркерах** **без** `include`/`require`-стейтментов
- **без stat-проверок** файла
- байткод **связан и оптимизирован** на этапе preload (опции уровня linkage)
- замер на больших Laravel/Symfony — **+5-15%** к старту запроса

**Как работает:**
1. в `php.ini` указывается **`opcache.preload=/path/preload.php`**
2. при старте FPM-мастера PHP исполняет этот скрипт **в контексте мастера**
3. через **`opcache_compile_file()`** (или `require_once`) загружает нужные классы
4. дальнейшие воркеры получают **forked** мастер с уже прогретой памятью

**Ограничения:**

| Ограничение | Объяснение |
| --- | --- |
| **Полный рестарт FPM при изменении** | preloaded-классы фиксированы — `opcache_reset()` **не сбрасывает** их |
| **Только декларации** | preload-скрипт может объявлять классы/функции, **не должен исполнять рантайм-код** (БД, HTTP) |
| **Нельзя preload trait-ы**, если они не используются классами в preload | trait сам по себе не «прицепляется» |
| **Зависимости должны быть в preload** | если класс `A extends B`, то `B` нужно прогрузить **раньше** |
| **`opcache.preload_user`** | в Linux preload **нельзя** запускать от root — указать unprivileged-юзера (`www-data`) |
| **Несовместимость с inheritance-cache** | в PHP 8.0+ есть отдельный кеш наследования, отчасти заменяющий ручной preload для простых случаев |

**Best practice:**
- собирать список классов автоматически (Composer `classmap`-сканер)
- **в Laravel** — пакет **`darkghosthunter/preloader`** или ручной скрипт через `composer dump-autoload --classmap-authoritative` + `opcache_compile_file()` в цикле
- помнить: при деплое **`systemctl restart php-fpm`** (не reload) — preload пересоберётся
- **CI-проверка**: запустить preload-скрипт в pipeline, чтобы поймать опечатки до прода',
                'code_example' => '<?php
// /var/www/preload.php — запускается мастером FPM при старте
// Запрещён рантайм-код: никаких new DB-Connection, HTTP-запросов

opcache_compile_file(__DIR__ . "/vendor/autoload.php");

// Прогрузить весь src/ — берём Composer classmap
$loader = require __DIR__ . "/vendor/autoload.php";
foreach (array_keys($loader->getClassMap()) as $class) {
    try {
        opcache_compile_file((new ReflectionClass($class))->getFileName());
    } catch (Throwable) {
        // пропускаем классы со сломанными зависимостями
    }
}',
                'code_language' => 'php',
                'difficulty' => 5,
                'topic' => 'php.runtime',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем PHP CLI отличается от PHP в веб-режиме простыми словами?',
                'answer' => '- **CLI** (Command Line Interface) — запуск через `php script.php` в терминале.
- **Веб-режим** — исполнение скрипта внутри **FPM** или mod_php в ответ на HTTP-запрос.

**Главные отличия:**
- **отдельные** `php.ini`-файлы (`cli/php.ini` и `fpm/php.ini`), лимиты отличаются
- в CLI **нет** ограничения `max_execution_time`, `memory_limit` мягче
- в CLI **нет суперглобальных** `$_GET` / `$_POST` / `$_SESSION` и cookies
- в CLI есть `$argv` / `$argc` и потоки `STDIN` / `STDOUT`

**Узнать текущий режим:** функция `php_sapi_name()` → `"cli"` или `"fpm-fcgi"`.

**Где применяется CLI:** cron, миграции, очереди, одноразовые скрипты.',
                'code_example' => '<?php
// какой SAPI
echo php_sapi_name();  // "cli" в терминале, "fpm-fcgi" в проде

// CLI получает аргументы через $argv (не $_GET)
// php script.php hello world
print_r($argv);  // [0 => "script.php", 1 => "hello", 2 => "world"]',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.runtime',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое PHP-FPM простыми словами?',
                'answer' => '**PHP-FPM** (**FastCGI Process Manager**) — отдельный демон, который держит **пул PHP-процессов** (воркеров) и обрабатывает запросы от веб-сервера (обычно **nginx**).

**Как работает поток запроса:**
1. **nginx** принимает HTTP-запрос
2. передаёт его в FPM через **UNIX-socket** или TCP
3. свободный **воркер** выполняет PHP-скрипт
4. результат возвращается nginx, тот — клиенту

**Главное свойство:** каждый запрос — **изолированный жизненный цикл**. Глобальные переменные, открытые соединения, выделенная память — всё уничтожается после ответа (модель «share nothing»).

**Альтернатива** — старый `mod_php` (встраивается в Apache) или современные runtime: **Octane**, **RoadRunner**, **FrankenPHP** (воркер живёт между запросами).',
                'difficulty' => 2,
                'topic' => 'php.runtime',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое opcache простыми словами?',
                'answer' => '**OPcache** — встроенное расширение PHP, которое **кэширует скомпилированный байткод** скриптов в **shared memory**.

**Что меняет:**
- **без OPcache** каждый запрос = парсинг + компиляция всех PHP-файлов заново (медленно)
- **с OPcache** байткод берётся из памяти — ускорение в **2-5 раз**

**Ключевые настройки в проде:**
- **`opcache.enable=1`** — включить
- **`opcache.validate_timestamps=0`** — НЕ проверять mtime файлов на каждом запросе (быстрее), но требует **`opcache_reset()`** или **перезапуск FPM** при деплое
- **`opcache.memory_consumption`** — размер кеша в МБ
- **`opcache.max_accelerated_files`** — лимит количества кешируемых файлов

**В проде** включён по умолчанию и критичен для производительности. **JIT** (PHP 8+) — отдельная фича внутри того же OPcache.',
                'difficulty' => 2,
                'topic' => 'php.runtime',
            ],
            [
                'category' => 'PHP',
                'question' => 'Откроет ли повторный запрос в PHP-FPM новое TCP-соединение с БД? При каких условиях нет?',
                'answer' => '**По умолчанию — ДА, откроет.** Модель PHP **«share nothing»**: каждый запрос — **изолированный** жизненный цикл скрипта внутри воркера.
- `PDO`/`MySQLi`-объект создаётся при `new PDO(...)`
- в **конце запроса** все user-space переменные уничтожаются, соединение **закрывается**
- даже если тот же воркер **сразу** обрабатывает следующий запрос

**Три условия, при которых соединение НЕ переоткрывается:**

**1. Persistent-соединение** — `PDO::ATTR_PERSISTENT => true` или `mysqli_pconnect`:
- после завершения скрипта драйвер **не закрывает сокет**, а кладёт его в пул **внутри ЭТОГО ЖЕ воркера**
- следующий запрос находит сокет по ключу **`(host:port:user:db:options)`** и переиспользует
- ⚠️ пул **на воркер**, не на FPM в целом — при `pm.max_children=50` будет **до 50 живых соединений** на одного DB-пользователя

**2. Внешний connection pooler:**

| Пулер | СУБД |
| --- | --- |
| **pgbouncer** | PostgreSQL |
| **ProxySQL** | MySQL |
| **RDS Proxy** | AWS RDS (MySQL/PG) |

- приложение делает **короткий TCP** к пулеру (локальный сокет — дёшево)
- пулер держит **долгие** соединения с реальной БД
- лучший вариант для **больших ферм** с тысячами FPM-воркеров

**3. Долгоживущий runtime** (Swoole, RoadRunner, **Laravel Octane**, FrankenPHP):
- воркер **не умирает** между запросами
- обычное `PDO` естественно переживает запросы
- требует **аккуратной очистки** состояния (см. ниже)

**Подводные камни persistent / long-living:**

| Проблема | Что происходит |
| --- | --- |
| **Незакоммиченная транзакция** | переходит к следующему запросу — фантомные блокировки |
| **Temporary tables** | живут до конца соединения, накапливаются |
| **Сессионные переменные** (`SET search_path`, `SET sql_mode`, `SET TIME ZONE`) | следующий запрос видит чужие настройки |
| **prepared-statement cache** | растёт, ест RAM на стороне БД |
| **`max_connections`** на стороне БД | можно упереться: 50 воркеров × 4 пула = 200 соединений |

**Защита:**
- явный `ROLLBACK` в shutdown-handler
- сброс `SET SESSION ...` в начале каждого запроса
- мониторинг `SHOW PROCESSLIST` / `pg_stat_activity`
- лучше — **внешний пулер** вместо `ATTR_PERSISTENT`',
                'difficulty' => 4,
                'topic' => 'php.runtime',
            ],
        ];
    }
}
