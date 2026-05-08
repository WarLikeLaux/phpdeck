<?php

namespace Database\Seeders\Data\Categories\SystemDesign;

class Caching
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example: ?string, code_language: ?string, difficulty: int, topic: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое кэш простыми словами?',
                'answer' => 'Кэш - это быстрое временное хранилище для часто запрашиваемых данных. Простыми словами: записная книжка под рукой - не нужно каждый раз идти в большой архив. Если данные есть в кэше (cache hit) - отдаём моментально; если нет (cache miss) - берём из БД, кладём в кэш, отдаём. Хранилища: Redis, Memcached, локальная память приложения, файловый кэш.',
                'code_example' => '<?php
$user = Cache::remember("user:$id", 3600, function () use ($id) {
    return User::find($id);
});',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Какие уровни кэширования бывают в веб-приложении?',
                'answer' => 'Слои кэша от клиента к БД: 1) Browser cache (Cache-Control в headers), 2) CDN (статика близко к пользователю), 3) Reverse proxy cache (Nginx, Varnish), 4) Application cache (Redis, Memcached), 5) ORM/query cache (внутри ORM), 6) Database buffer pool (внутри БД). Принцип: чем ближе к пользователю, тем быстрее, но меньше данных можно хранить.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 3,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое cache-aside (lazy loading) стратегия?',
                'answer' => 'Cache-aside - приложение само управляет кэшем: при чтении сначала смотрит в кэш, если нет - читает из БД и кладёт в кэш. При записи - пишет в БД и инвалидирует/обновляет кэш. Простыми словами: ты сам решаешь, что и когда положить в записную книжку. Плюсы: простота, кэш не зависит от БД. Минусы: возможен stale data при гонке, первый запрос всегда медленный. Подвох: проверка через `=== null` не отличает реально закэшированный null (negative caching) от cache miss — в Laravel надёжнее `Cache::has()` или `Cache::remember()` со специальным sentinel-значением.',
                'code_example' => '<?php
function getUser(int $id): User {
    if (! Cache::has("user:$id")) {
        $user = User::find($id);
        Cache::put("user:$id", $user, 3600);
        return $user;
    }
    return Cache::get("user:$id");
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое write-through стратегия кэша?',
                'answer' => 'Write-through - на запись приложение/кэш-прослойка пишет одновременно (синхронно) и в кэш, и в БД; ответ возвращается только после успешной записи в оба слоя. Это про путь ЗАПИСИ. Путь ЧТЕНИЯ - отдельный паттерн (read-through или cache-aside): они часто комбинируются, но это разные стратегии. Плюсы write-through: кэш не отстаёт от БД на штатном пути записи, не нужно ловить инвалидацию. Минусы: запись медленнее (два последовательных I/O); кэш заполняется только тем, что записывали (cold reads пойдут в БД, если read-through не настроен). Stale-данные всё ещё возможны при сбоях между шагами (запись в кэш прошла, в БД упала и наоборот), при асинхронной репликации БД, при гонках с другими записями - "нет stale data" - слишком сильное заявление, гарантия "штатно консистентен", не "никогда не отстаёт". Подходит когда чтение во много раз чаще записи и важна свежесть кэша по штатному пути.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 3,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое read-through стратегия кэша?',
                'answer' => 'Read-through - кэш сам читает из БД при промахе, приложение всегда обращается только к кэшу. В отличие от cache-aside, где промахом управляет код приложения, здесь логика загрузки спрятана внутрь кэш-провайдера/библиотеки. Плюсы: код приложения проще, единая точка работы с данными. Минусы: первый запрос всегда медленный (cache miss), нужна готовая интеграция кэша с источником. Часто комбинируется с write-through. Пример: Hibernate 2nd-level cache, Ehcache CacheLoader, AWS DAX перед DynamoDB.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 3,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое write-behind (write-back) стратегия кэша?',
                'answer' => 'Write-behind - запись только в кэш, в БД асинхронно через некоторое время или батчем. Простыми словами: записал в блокнот, в большую тетрадь перепишу позже. Самая быстрая запись. Минусы: в наивной реализации (in-memory only) при падении кэша теряются ещё не сброшенные данные; production-решения (Caffeine + RDBMS, Hazelcast WriteBehind) добавляют WAL/persistence. Сложнее реализовать. Подходит для счётчиков, метрик, логов - где небольшая потеря не катастрофа.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 4,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое write-around стратегия кэша?',
                'answer' => 'Write-around - запись идёт сразу в БД минуя кэш, кэш заполняется только при чтении. Плюсы: не засоряем кэш редко читаемыми данными, простая запись. Минусы: первое чтение после записи всегда медленное (cache miss). Хорошо когда записи много, а читается малая часть данных (логи событий, аудит).',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 3,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Какие политики вытеснения (eviction) есть в кэше?',
                'answer' => 'LRU (Least Recently Used) - вытесняем то, что давно не использовали. LFU (Least Frequently Used) - то, что редко используется. FIFO - в порядке поступления. Random - случайно. TTL - по времени жизни. Redis по умолчанию использует noeviction (отказывает в записи при OOM), но можно настроить allkeys-lru, volatile-lru и др. Выбор зависит от паттерна доступа.',
                'code_example' => '# redis.conf
maxmemory 2gb
maxmemory-policy allkeys-lru',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое cache stampede (dogpile) и как с ним бороться?',
                'answer' => 'Cache stampede (он же dogpile, thundering herd) - когда популярный ключ истекает в кэше и тысячи запросов одновременно начинают перестраивать его, забивая БД. Простыми словами: магазин закрылся на учёт - все клиенты ломятся одновременно. Решения: 1) atomic lock на пересборку (SET NX + блокировка - только один запрос строит, остальные ждут или отдают stale); важно: сначала Cache::get, и только при miss - брать lock, чтобы не сериализовать cache hits; внутри lock - повторная проверка кэша (double-check), потому что пока ждали lock, другой воркер мог уже перестроить. 2) probabilistic early expiration (XFetch: с растущей вероятностью при подходе к TTL один воркер заранее перестраивает). 3) stale-while-revalidate (отдаём старое значение, пока в фоне обновляется новое). 4) refresh-ahead / cache warming - перестройка по cron / событию ДО истечения TTL, через очередь job-ов; пользовательский запрос всегда попадает на горячий ключ. (Не путать с BGREWRITEAOF - это команда Redis для перезаписи AOF-файла, к stampede отношения не имеет.)',
                'code_example' => '<?php
// ✅ Double-check pattern: cache hit не блокируется
$value = Cache::get($key);
if ($value === null) {
    $value = Cache::lock("rebuild:$key", 10)->block(5, function () use ($key) {
        // в lock - снова проверяем: другой воркер мог уже перестроить
        return Cache::remember($key, 300, fn () => expensiveQuery());
    });
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое CDN простыми словами?',
                'answer' => 'CDN (Content Delivery Network) - сеть серверов по всему миру, которые хранят копии твоих статичных файлов (картинки, JS, CSS) близко к пользователям. Простыми словами: твой сайт в Москве, а пользователь в Токио - вместо запроса через полпланеты, ему отдают файл с сервера CDN в Токио. Уменьшает latency, разгружает origin, защищает от DDoS. Примеры: Cloudflare, Fastly, AWS CloudFront, Akamai.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 2,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'В чём разница между Redis и Memcached?',
                'answer' => 'Memcached - чистый in-memory key/value кэш: только строки/блобы, multi-threaded, очень простой и предсказуемый, slab-аллокатор, нет персистентности и репликации (в open-source). Redis - data structure server: строки, hashes, lists, sets, sorted sets, streams, bitmap, HyperLogLog, geo; есть Lua-скрипты, pub/sub, транзакции, persistence (RDB/AOF), репликация и Cluster, single-threaded I/O loop (Redis 6+ имеет multi-threaded I/O). Когда что: Memcached - когда нужен только LRU-кэш и шардирование клиентом; Redis - когда нужны структуры (rate limit на sorted set, очереди, leaderboards), persistence или replication.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 3,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Почему cache invalidation - это сложно?',
                'answer' => 'Знаменитая цитата: "There are only two hard things in CS: cache invalidation and naming things". Сложно потому что: 1) трудно понять когда именно данные устарели, 2) одно изменение в БД может затронуть много ключей кэша, 3) в распределённой системе инвалидация сама требует консистентности, 4) баланс между актуальностью и производительностью. Решения: TTL, версионирование ключей (etag), tag-based invalidation, событийная инвалидация.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 3,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Какие основные структуры данных предоставляет Redis и где их применяют?',
                'answer' => 'Redis — это не просто key-value, а сервер структур данных. Strings хранят произвольные байты до 512 МБ и подходят под счётчики (INCR) и кэш сериализованных значений. Lists — двусторонние очереди (LPUSH/RPOP), типичный кейс — простая очередь задач. Hashes — словари полей внутри одного ключа, удобны для объектов. Sets — множества с проверкой принадлежности O(1), Sorted Sets — множества с числовым score для рейтингов и лидербордов, Streams — append-only лог сообщений с consumer groups, замена легковесному Kafka.',
                'difficulty' => 3,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Какие механизмы персистентности есть у Redis и в чём разница RDB и AOF?',
                'answer' => 'RDB периодически делает снимок всей базы на диск через fork процесса — компактный файл, быстрая загрузка, но при падении теряются данные с момента последнего снимка (минуты). AOF (Append-Only File) дозаписывает каждую модифицирующую команду; с fsync=everysec теряется максимум секунда, при always — ничего, ценой скорости. AOF файл крупнее RDB и периодически сжимается через rewrite. На практике в проде включают оба: RDB для быстрого восстановления, AOF для минимальной потери данных, либо отключают всё, если Redis используется только как кэш.',
                'difficulty' => 4,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Redis Pub/Sub и чем он отличается от Redis Streams?',
                'answer' => 'Pub/Sub — это fire-and-forget модель: подписчик получает только те сообщения, что пришли пока он подключён, нет хранения и offset, при дисконнекте всё пропущенное теряется. Streams — это persistent append-only лог: сообщения хранятся в ключе, у каждого есть id, поддерживаются consumer groups с подтверждением (XACK) и pending-list, как в Kafka. Pub/Sub годится для realtime-уведомлений, где допустима потеря, Streams — для очередей задач и event-логов с гарантиями at-least-once.',
                'difficulty' => 4,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как делаются транзакции в Redis через MULTI/EXEC и при чём тут WATCH?',
                'answer' => 'MULTI начинает блок команд, которые буферизуются на сервере и выполняются атомарно одним пакетом по EXEC — никакой другой клиент не вклинится между ними. Но это не транзакции в смысле RDBMS: rollback нет, ошибки исполнения отдельных команд не отменяют остальные. WATCH ключ_X добавляет оптимистическую блокировку: если до EXEC другой клиент изменит этот ключ, EXEC вернёт nil и весь блок не выполнится — клиент должен повторить попытку. Это паттерн optimistic concurrency control для Redis. В Redis Cluster MULTI/EXEC ограничен ключами одного hash-slot — мульти-ключевые транзакции через шарды не работают.',
                'difficulty' => 4,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Чем Redis Sentinel отличается от Redis Cluster?',
                'answer' => 'Sentinel — это сервис мониторинга поверх классической схемы master + реплики: он следит за здоровьем мастера, инициирует failover (повышает реплику в мастера) и сообщает клиентам новый адрес. Данные при этом не шардируются — весь датасет лежит на каждом узле. Cluster — это шардирование: ключи распределяются по 16384 hash-слотам между несколькими мастерами, каждый со своими репликами; failover встроен. Sentinel выбирают, когда нужна HA, но датасет помещается в один сервер; Cluster — когда нужно масштабировать запись или объём за пределы одной машины.',
                'difficulty' => 4,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Зачем в Redis Lua-скрипты через EVAL?',
                'answer' => 'EVAL выполняет Lua-скрипт на сервере атомарно: пока он работает, никакая другая команда не вклинится. Это позволяет реализовать сложную логику типа «прочитай ключ, проверь условие, обнови несколько ключей» одной операцией без race condition между чтением и записью. Классические применения — кастомные rate limiter (token bucket), безопасное снятие распределённого lock (DEL только если значение совпадает с владельцем), атомарные счётчики с лимитом. Минус: длинный скрипт блокирует весь однопоточный сервер, поэтому Lua должен быть быстрым.',
                'difficulty' => 4,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Redis pipelining и почему он ускоряет работу?',
                'answer' => 'В обычном режиме клиент шлёт команду и ждёт ответа — каждая операция стоит один RTT (round-trip time) до сервера. Pipelining позволяет отправить пачку команд одним пакетом, не дожидаясь ответов, а потом прочитать все ответы подряд. На сети с RTT 1 мс это превращает 1000 операций из секунды в десяток миллисекунд. Pipelining не делает команды атомарными (это не транзакция), он только устраняет сетевую задержку. На практике сильно помогает при массовых SET/GET и при инициализации кэша.',
                'difficulty' => 3,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как сделать распределённый lock на Redis и что не так с наивным SETNX?',
                'answer' => 'Наивная схема SETNX key 1 без TTL опасна: если владелец упадёт, ключ останется навсегда и заблокирует всех. Правильный минимум — SET key uniq_token NX PX ttl_ms одной командой и при освобождении DEL только если значение совпадает (через Lua), иначе можно случайно удалить чужой lock после истечения TTL. Для multi-master Redis есть алгоритм Redlock от автора Redis: брать lock на N>=5 независимых узлах, считать успешным при majority. Redlock спорен: Martin Kleppmann в статье "How to do distributed locking" (2016) критиковал его за зависимость от системных часов и отсутствие fencing-токенов, для критичных кейсов берут etcd/ZooKeeper.',
                'difficulty' => 4,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое hot key и big key в Redis и чем они опасны?',
                'answer' => 'Hot key — ключ, на который идёт непропорционально много трафика; в Cluster он не распределяется по шардам, поэтому весь load бьёт в один мастер и упирается в его CPU. Лечится локальным кэшем перед Redis, репликой только под этот ключ или шардированием значения по версии (key:1, key:2, ...). Big key — слишком большое значение (мегабайты, миллионы элементов в коллекции): команды над ним блокируют однопоточный сервер на десятки миллисекунд и тормозят всех. Лечится разбиением (hash вместо одной строки), сканированием через HSCAN/SSCAN и мониторингом MEMORY USAGE.',
                'difficulty' => 4,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое negative caching и зачем он нужен?',
                'answer' => 'Negative caching — это кэширование самого факта «ничего нет»: если запрос к БД вернул null или 404, мы кладём в кэш специальный маркер с коротким TTL. Без этого каждый запрос несуществующего id бьёт мимо кэша прямо в БД — атакующий может уронить базу подбором случайных id (cache penetration). Альтернатива/дополнение — Bloom filter перед кэшем: он быстро отвечает «такого id точно нет», без обращения к хранилищу. TTL у негативного кэша делают коротким, чтобы новая запись стала видимой быстро.',
                'difficulty' => 4,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Зачем добавлять jitter к TTL в кэше?',
                'answer' => 'Если множество ключей создано одной операцией с одинаковым TTL (например, прогрев кэша при деплое), они истекут одновременно и в эту секунду все промахи одновременно ударят по БД — это разновидность cache stampede. Jitter — это добавление случайной поправки к TTL, например ttl + rand(0, ttl*0.1), чтобы разнести истечение во времени. Это дешёвая страховка, которая хорошо комбинируется с другими защитами от stampede (single-flight lock, probabilistic early expiration).',
                'difficulty' => 3,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое hit ratio и о чём говорит низкий показатель?',
                'answer' => 'Hit ratio = hits / (hits + misses) — доля запросов, обслуженных из кэша. У зрелого кэша горячих данных он обычно 90+%. Низкий hit ratio означает одно из: TTL слишком короткий и ключи истекают раньше повторного запроса; памяти не хватает и работает eviction, выкидывая нужные ключи; ключевое пространство слишком большое и распределение запросов плоское (long tail), кэш не помогает архитектурно; неправильно подобран ключ (например, включает таймстемп и каждый запрос уникален). Метрика смотрится вместе с evicted_keys и used_memory.',
                'difficulty' => 3,
                'topic' => 'system_design.caching',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое многоуровневый кэш (L1/L2) и какие у него подводные камни?',
                'answer' => 'L1 — это локальный in-process кэш в памяти приложения (APCu, array в long-running воркере), L2 — общий распределённый кэш (Redis/Memcached). L1 даёт наносекундные обращения без сети, L2 — общую согласованность между инстансами. Главная проблема — инвалидация: при изменении данных надо сбросить L1 на всех воркерах сразу, иначе разные инстансы возвращают разные значения. Решают через короткий TTL на L1, pub/sub-уведомления об инвалидации или event-driven сброс. В Octane/RoadRunner об этом надо помнить отдельно: L1 переживает запросы.',
                'difficulty' => 4,
                'topic' => 'system_design.caching',
            ],
        ];
    }
}
