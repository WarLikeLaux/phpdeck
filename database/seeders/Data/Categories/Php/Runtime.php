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
                'topic' => 'php.runtime',
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
                'topic' => 'php.runtime',
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
                'topic' => 'php.runtime',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работает JIT в PHP 8 и в каких задачах он реально ускоряет?',
                'answer' => 'JIT (tracing/function режимы) компилирует горячий байткод в машинный код через DynASM. Для типичных веб-приложений выигрыш скромный, потому что бутылочное горлышко - IO/база, а не CPU. Реальный профит - на CPU-bound задачах: вычислениях, image-processing, парсерах, ML-инференсе. Включается через opcache.jit_buffer_size и opcache.jit=tracing в php.ini. Важный апдейт PHP 8.4: числовая форма флага (тип 1255, 1235, 1101) объявлена deprecated - используйте именованные значения tracing / function / on / off / disable. JIT остаётся частью расширения OPcache, отдельного pecl-пакета не появилось.',
                'difficulty' => 5,
                'topic' => 'php.runtime',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как устроен сборщик циклических ссылок в PHP и когда он включается?',
                'answer' => 'Базовый механизм управления памятью - reference counting. Когда refcount достигает 0, zval освобождается немедленно. Проблема: циклические ссылки (a→b→a) держат refcount > 0 даже когда объекты недостижимы из кода - чистый refcount не справляется. Поэтому существует второй уровень: буфер "возможных корней" (potential cycle roots), куда попадают объекты, у которых уменьшился refcount, но не до нуля. Когда буфер заполняется (порог по умолчанию 10000), запускается mark-and-sweep алгоритм для циклических ссылок: помечает все достижимые узлы из этого буфера, удаляет недостижимые. Исторически в PHP 5.3 был алгоритм Bacon-Rajan (упоминается в старых статьях), но в PHP 7.3 Дмитрий Стогов полностью переписал GC - реализация стала намного быстрее и компактнее по памяти, хотя концептуально это всё ещё mark-and-sweep для циклов поверх refcounting. Принудительный запуск - gc_collect_cycles(). В долгоживущих PHP-процессах (Octane, queue:work) дополнительно вызывают gc_mem_caches() - возвращает кешированные арены памяти от Zend MM обратно в ОС.',
                'difficulty' => 5,
                'topic' => 'php.runtime',
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
                'topic' => 'php.runtime',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое preloading в PHP 7.4+ и какие у него ограничения?',
                'answer' => 'Preloading загружает указанные файлы в opcache при старте PHP-FPM master-процесса и навсегда держит их в памяти. Эти классы доступны во всех воркерах без файловой проверки, что даёт +5-15% к скорости старта запроса. Ограничения: при изменении preloaded-файла нужен полный рестарт FPM, нельзя использовать с runtime-кодом, скрипт исполняется в контексте master.',
                'difficulty' => 5,
                'topic' => 'php.runtime',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем PHP CLI отличается от PHP в веб-режиме простыми словами?',
                'answer' => 'CLI (Command Line Interface) — запуск через php script.php в терминале. Веб-режим — PHP запускается веб-сервером для обработки HTTP-запросов (через FPM или mod_php). У них РАЗНЫЕ php.ini-конфиги: cli/php.ini и fpm/php.ini. В CLI нет $_GET/$_POST, нет лимита времени по умолчанию, нет лимита памяти. Используется для скриптов, миграций, очередей, cron.',
                'difficulty' => 1,
                'topic' => 'php.runtime',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое PHP-FPM простыми словами?',
                'answer' => 'FastCGI Process Manager — отдельный демон, который держит пул PHP-процессов (воркеров) и обрабатывает запросы от веб-сервера (nginx). Веб-сервер передаёт запрос через сокет в FPM, воркер выполняет PHP-скрипт и возвращает ответ. Каждый запрос — изолированный жизненный цикл.',
                'difficulty' => 2,
                'topic' => 'php.runtime',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое opcache простыми словами?',
                'answer' => 'Встроенное расширение PHP, которое кэширует скомпилированный байткод скриптов в памяти. Без opcache каждый запрос — это парсинг и компиляция всех PHP-файлов заново (медленно). С opcache — байткод берётся из памяти, ускорение в 2-5 раз. В проде включён по умолчанию.',
                'difficulty' => 2,
                'topic' => 'php.runtime',
            ],
            [
                'category' => 'PHP',
                'question' => 'Откроет ли повторный запрос в PHP-FPM новое TCP-соединение с БД? При каких условиях нет?',
                'answer' => 'По умолчанию — ДА, откроет. Модель PHP «share nothing»: каждый запрос — это изолированный жизненный цикл скрипта внутри воркера. PDO/MySQLi-объект создаётся при new PDO(...), а в конце запроса все user-space переменные уничтожаются и соединение закрывается — даже если тот же воркер сразу обрабатывает следующий запрос. Соединение НЕ переоткроется (или, точнее, переиспользуется тем же воркером) только при специальных условиях: 1) Используется PERSISTENT-соединение: PDO::ATTR_PERSISTENT => true в options или mysqli_pconnect / mysql_pconnect. Тогда после завершения скрипта драйвер не закрывает сокет, а кладёт его обратно в пул внутри ЭТОГО ЖЕ воркера; при следующем запросе тот же воркер найдёт сокет по ключу (host:port:user:db) и переиспользует. Важно: пул на воркер, не на FPM в целом — поэтому при pm.max_children=50 будет до 50 живых соединений на один пул-пользователь. 2) Используется внешний connection pooler (pgbouncer для Postgres, ProxySQL для MySQL, RDS Proxy) — приложение каждый раз открывает короткий TCP-коннект к пулеру, но пулер уже держит постоянные соединения с реальной БД. 3) Долгоживущий runtime (Swoole/RoadRunner/Laravel Octane) — воркер не умирает между запросами, и обычное PDO-соединение естественно переживает запросы (требует осторожности: очистка транзакций, сброс sql_mode/search_path). Подводные камни persistent: незакоммиченные транзакции, оставшиеся temporary-таблицы, изменённый search_path/sql_mode переходят к следующему запросу — нужно либо явно сбрасывать состояние, либо не использовать persistent в критичных кодпасах.',
                'difficulty' => 4,
                'topic' => 'php.runtime',
            ],
        ];
    }
}
