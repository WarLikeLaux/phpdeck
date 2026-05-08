<?php

namespace Database\Seeders\Data\Categories\SystemDesign;

class Distributed
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example: ?string, code_language: ?string, difficulty: int, topic: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое CAP теорема простыми словами?',
                'answer' => 'CAP теорема (Brewer): в распределённой системе при разрыве сети (Partition) приходится выбирать между Consistency (все читают самые свежие данные) и Availability (любой живой узел отвечает). P выбирать не приходится - сеть всегда может разорваться, поэтому реальный выбор: CP или AP. Простыми словами: два магазина одной сети, связь оборвалась. Либо запрещаем продавать (CP - потеряли A), либо разрешаем, но остатки разойдутся (AP - потеряли C). Примеры: HBase, MongoDB (с majority writes), etcd, Zookeeper - CP; Cassandra, DynamoDB, Riak - AP. Важно: классификация условна, многие системы tunable (Cassandra с QUORUM ближе к CP).',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 4,
                'topic' => 'system_design.distributed',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое PACELC?',
                'answer' => 'PACELC - расширение CAP. При partition (P) выбираешь между A и C (как в CAP). Else (E), даже без сбоев, выбираешь между Latency (L) и Consistency (C). Простыми словами: даже когда сеть работает, синхронизация между репликами тратит время - либо ждём подтверждения от всех (медленнее, консистентно), либо отвечаем сразу (быстро, но возможна eventual consistency). DynamoDB - PA/EC по дефолту (eventual reads, но через ConsistentRead=true можно получить strongly consistent reads на отдельный запрос - тогда поведение ближе к PC). MongoDB сложнее одним ярлыком: по умолчанию драйверы читают с primary (read preference = primary), что даёт strong reads; eventual consistency проявляется при чтении со secondary (readPreference=secondaryPreferred / nearest) и при write concern w:1; majority writes + linearizable reads дают почти CP. Cassandra - PA/EL для базовых операций.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 5,
                'topic' => 'system_design.distributed',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое eventual consistency простыми словами?',
                'answer' => 'Eventual consistency - "в конечном итоге согласованность": после записи разные узлы могут какое-то время видеть разные значения, но рано или поздно сойдутся к одному. Простыми словами: новый пост в инстаграме - друг в Москве уже видит, друг в Токио ещё нет, но через секунду увидит. Достаточно для лайков, постов, корзин; не подходит для денежных операций где нужна strong consistency.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 3,
                'topic' => 'system_design.distributed',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое distributed lock и зачем нужен?',
                'answer' => 'Distributed lock - блокировка, работающая между несколькими процессами/серверами. Если на одном сервере достаточно mutex, то когда воркеров на разных машинах - им нужен общий "ключ" в Redis или Zookeeper. Зачем: чтобы только один воркер обрабатывал джобу, чтобы только один cron запустился. Реализации: Redis SET NX EX (один узел), Redlock (несколько Redis-узлов), Zookeeper / etcd (через ephemeral nodes / leases). КЛЮЧЕВАЯ ПРОБЛЕМА: TTL лока может истечь, пока воркер работает (например, его GC pause / IO stall на 30 секунд) - тогда второй воркер возьмёт лок параллельно с первым, и оба будут писать в общее хранилище. Решение - fencing token. Это монотонный счётчик, который выдаётся вместе с локом: воркер_А получил token=42, воркер_Б после переотбора - token=43. КАК РАБОТАЕТ ТОКЕН (важно!): сам по себе номер бесполезен, его должно проверять ЦЕЛЕВОЕ хранилище. БД/API при записи проверяет: "последний принятый токен был 43, а сейчас приходит 42 - отвергаю". Без поддержки на стороне storage fencing-токен НЕ защищает от race-condition - просто наблюдается. Поэтому для критичных операций нужно либо хранилище, умеющее conditional update (DynamoDB ConditionExpression, etcd compare-and-swap, Postgres SELECT FOR UPDATE с WHERE token > stored_token), либо использовать оптимистическую блокировку через версионирование (которое по сути тот же fencing). В Laravel Cache::lock реализует первую часть (взять/не взять) и owner-based release: каждый лок при создании генерирует случайный owner-токен (Str::random() в Illuminate\Cache\Lock::__construct), и release() проходит через атомарный Lua-скрипт, который снимает лок только если текущий holder в Redis == собственный owner. Это защищает от классики: "воркер А уснул, TTL лока истёк, воркер Б взял лок, воркер А проснулся и сделал release() - и снёс ЧУЖОЙ лок". forceRelease() это игнорирует. НО монотонного fencing-токена нет: random owner защищает только release-операцию, а от записи в защищаемое хранилище уже устаревшим воркером (которого "выбросили" из лока) Laravel не спасает. Для критичных сценариев нужно строить поверх (хранить в БД свой монотонный счётчик, выдаваемый при взятии лока) либо использовать оптимистическую блокировку через version/updated_at в самой целевой записи.',
                'code_example' => '<?php
// Базовый локсинг (Laravel Cache lock)
$lock = Cache::lock("import-orders", 60);
if ($lock->get()) {
    try {
        importOrders();
    } finally {
        $lock->release();
    }
}

// ⚠️ Уязвимая схема - без fencing token
// Воркер_А: lock acquired, GC pause на 90s
// (TTL=60s истёк - lock освобождён автоматически)
// Воркер_Б: lock acquired, обновляет БД
// Воркер_А: проснулся, тоже пишет в БД - race!

// ✅ С fencing token (хранилище проверяет монотонность)
$token = LockManager::acquire("orders-import"); // вернёт 42
processOrders();
DB::transaction(function () use ($token) {
    // условный update только если текущий токен <= нашего
    $rows = DB::update("
        UPDATE orders_import_state
           SET last_processed_id = ?, fencing_token = ?
         WHERE fencing_token <= ?
    ", [$lastId, $token, $token]);

    if ($rows === 0) {
        throw new StaleLockException; // пришёл воркер с большим токеном - откатываемся
    }
});

// Альтернатива: оптимистическая блокировка через version
$user = User::find($id);
$updated = User::where("id", $id)
    ->where("version", $user->version)
    ->update(["balance" => $user->balance - 100, "version" => DB::raw("version + 1")]);
if (! $updated) throw new ConcurrentModificationException;',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'system_design.distributed',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Circuit Breaker простыми словами?',
                'answer' => 'Circuit Breaker (предохранитель) - паттерн защиты от каскадных сбоев. Простыми словами: как электрический предохранитель - если в розетке КЗ, отключает ток, чтобы не сгорела вся проводка. Так же circuit breaker: если внешний сервис не отвечает, после N неудач "размыкает цепь" и сразу отдаёт ошибку, не дёргая сервис. Через время пробует один запрос (half-open) - если ОК, замыкает обратно. Защищает от долгих таймаутов и истощения коннектов.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 4,
                'topic' => 'system_design.distributed',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое retry с exponential backoff и jitter?',
                'answer' => 'Retry - повторные попытки при ошибке. Exponential backoff - задержка растёт экспоненциально (1с, 2с, 4с, 8с, 16с) чтобы не забивать упавший сервис. Jitter - добавляем случайность к задержке, чтобы тысячи клиентов не ретраили одновременно (thundering herd). Без jitter после сбоя все клиенты ждут ровно 8 секунд и одновременно бьются - сервис снова падает. С jitter - размазываются во времени.',
                'code_example' => '<?php
$attempt = 1;
$base = 100; // ms
$delay = min($base * (2 ** $attempt), 30000) + random_int(0, 1000);
usleep($delay * 1000);',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'system_design.distributed',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Bulkhead pattern?',
                'answer' => 'Bulkhead (переборка) - изоляция ресурсов чтобы сбой одной части не уронил всё. Название от переборок на корабле: одна секция затопилась, корабль не тонет. Простыми словами: пул коннектов разделён по сервисам - если внешний API завис, на него уходят только 10 коннектов из 100, остальные обслуживают других. Реализуется через отдельные thread pools, connection pools, rate limits на сервис.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 4,
                'topic' => 'system_design.distributed',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое service discovery?',
                'answer' => 'Service discovery - механизм, через который сервисы находят друг друга в динамической инфраструктуре. Простыми словами: микросервисы стартуют на разных IP/портах, постоянно меняются - вместо хардкода адресов есть "телефонная книга". Сервис при старте регистрируется, при отключении - удаляется. Клиенты спрашивают "где сервис заказов?" и получают актуальный адрес. Реализации: Consul, etcd, Kubernetes DNS, AWS Service Discovery.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 3,
                'topic' => 'system_design.distributed',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое consensus и алгоритмы Paxos/Raft?',
                'answer' => 'Distributed consensus - проблема согласования значения между узлами при сбоях сети и отказах. Используется для leader election, репликации лога, atomic commit. Paxos (Lamport, 1989) - формально доказан, но печально известен сложностью реализации. Raft (Stanford, 2013) - спроектирован для понимаемости: явное разделение на leader election, log replication, safety. Все алгоритмы требуют большинства (quorum, N/2+1) - кластер из 5 узлов переживает падение 2. ZAB (Zookeeper) и Multi-Paxos похожи. Используется в etcd, Consul, CockroachDB, Kafka KRaft, MongoDB.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 5,
                'topic' => 'system_design.distributed',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое leader election и split-brain?',
                'answer' => 'Leader election - выбор одного узла-лидера среди множества для координации операций (запись в реплицированный лог, master в БД). Реализуется через consensus (Raft, ZAB) или distributed lock (Zookeeper ephemeral node, Redis lock с fencing). Split-brain - ситуация когда из-за разрыва сети два узла одновременно считают себя лидером и принимают записи. Грозит расхождением данных и невозможностью merge. Защита: 1) quorum (лидер требует подтверждения большинства - в меньшинстве он не сможет писать), 2) fencing token - монотонный счётчик, старый лидер не сможет писать после переизбрания, 3) STONITH ("Shoot The Other Node In The Head") в HA-кластерах.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 5,
                'topic' => 'system_design.distributed',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое 2PC (Two-Phase Commit)?',
                'answer' => '2PC - протокол распределённой транзакции через несколько БД. Фаза 1 (prepare): координатор спрашивает у всех "готовы коммитить?", все либо да, либо нет. Фаза 2 (commit/abort): если все ответили да - команда commit, иначе rollback. Простыми словами: голосование перед общим решением. Минусы: блокирующий протокол, при падении координатора всё стоит. Поэтому в микросервисах предпочитают Saga вместо 2PC.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 5,
                'topic' => 'system_design.distributed',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое шардирование простыми словами?',
                'answer' => 'Шардирование (sharding) - разделение одной большой базы на несколько маленьких частей (шардов) на разных серверах. Простыми словами: огромный торт, который не съешь одному - разрезали на куски и раздали друзьям. Каждый шард хранит свою часть данных. Зачем: когда одна БД не справляется по объёму или нагрузке. Шарды могут делиться по диапазону (id 1-1M на шарде A), хешу или директории.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 4,
                'topic' => 'system_design.distributed',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Какие стратегии шардирования бывают?',
                'answer' => 'Range sharding - по диапазонам ключей (id 1-1M на шарде A, 1M-2M на B). Простой routing, но горячие шарды на свежих данных. Hash sharding - по хешу ключа, равномерно. Range queries становятся scatter-gather. Consistent hashing - добавление узла перемещает только малую часть ключей (Cassandra, Memcached). Directory-based - lookup-сервис знает где какой ключ. Гибко, но сам lookup точка отказа.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 4,
                'topic' => 'system_design.distributed',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Чем партиционирование таблицы отличается от шардирования?',
                'answer' => 'Партиционирование — это разбиение одной большой таблицы на несколько физических частей внутри одного экземпляра СУБД (по диапазону, списку или хешу) для ускорения запросов и обслуживания. Шардирование — это распределение данных по нескольким независимым серверам БД, где каждый шард хранит свой поднабор и обслуживается отдельным процессом. Партиционирование решает проблему размера таблицы, шардирование — проблему ёмкости и пропускной способности всего узла.',
                'difficulty' => 3,
                'topic' => 'system_design.distributed',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Sharding Key и по каким критериям его выбирают?',
                'answer' => 'Sharding Key — атрибут записи, по которому система определяет, на каком шарде она хранится (User ID, Tenant ID, hash(email) и т. п.). Хороший ключ обеспечивает равномерное распределение данных и нагрузки, чтобы не возникали «горячие» шарды, и сохраняет связанные записи вместе, минимизируя межшардовые join-ы и распределённые транзакции. Плохой ключ (например, монотонный timestamp) создаёт перекос и узкое горлышко на одном узле.',
                'difficulty' => 4,
                'topic' => 'system_design.distributed',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Сравните стратегии маршрутизации шардов: application-level, proxy и coordinator-based.',
                'answer' => 'Application-level: логика выбора шарда зашита в клиент или ORM, лишних сетевых хопов нет, но правила дублируются между сервисами и менять схему шардирования больно. Proxy-based (Vitess, ProxySQL, Mongos): отдельный слой между приложением и БД делает шардирование прозрачным, централизует ребалансировку, но добавляет хоп и точку отказа. Coordinator-based (Zookeeper, etcd, кастомный metadata-сервис): клиент сначала спрашивает координатор о расположении шарда, что гибко при респлитах, но сложнее в эксплуатации и требует кэширования метаданных.',
                'difficulty' => 4,
                'topic' => 'system_design.distributed',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Сравните 2PC, 3PC, TCC и Saga для распределённых транзакций.',
                'answer' => '2PC (Two-Phase Commit) — синхронный блокирующий протокол с координатором: даёт строгую согласованность, но при падении координатора участники зависают с залоченными ресурсами. 3PC добавляет фазу pre-commit, чтобы уменьшить блокировки, но усложняет протокол и в продакшене почти не встречается. TCC (Try-Confirm-Cancel) — прикладной паттерн с явным резервированием ресурсов и компенсациями, типичен в финтехе. Saga — цепочка локальных транзакций с компенсирующими действиями, даёт только eventual consistency, зато хорошо масштабируется и стандартна для микросервисов.',
                'difficulty' => 4,
                'topic' => 'system_design.distributed',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Чем strong consistency отличается от eventual, и где между ними находится linearizability?',
                'answer' => 'Strong consistency гарантирует, что любое чтение после успешной записи вернёт это новое значение — нужна для денег, остатков на складе, бронирований. Eventual consistency допускает, что реплики временно расходятся, и сходятся к общему состоянию через какое-то время — годится для лайков, лент, каталогов. Linearizability — самая жёсткая модель: операции выглядят так, будто выполнились мгновенно в каком-то одном глобальном порядке, согласованном с реальным временем; это сильнее, чем просто strong consistency на одной записи, и обычно требует консенсус-алгоритмов (Raft, Paxos).',
                'difficulty' => 4,
                'topic' => 'system_design.distributed',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое master-slave репликация БД и какие задачи она решает?',
                'answer' => 'Master принимает все записи и асинхронно (или полусинхронно) проигрывает их binlog на одну или несколько реплик-slave. Это даёт три выигрыша: горячий резерв на случай падения мастера (failover), отделённое чтение для аналитики и тяжёлых отчётов, чтобы не мешать OLTP, и геораспределённые реплики ближе к клиенту. Главный компромисс — реплика всегда чуть отстаёт, и при чтении сразу после записи можно увидеть старую версию данных. Репликация не заменяет бэкапы: ошибочный DELETE мгновенно прилетит на все реплики.',
                'difficulty' => 3,
                'topic' => 'system_design.distributed',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое read/write splitting и какие у него подводные камни?',
                'answer' => 'Идея проста: запись идёт в master, чтение — в реплики, и горизонтально масштабируется только дешёвая нагрузка чтения. Подвох в repl lag: после успешного INSERT следующий SELECT с реплики может вернуть пустой результат, потому что транзакция ещё не доехала. Лечится «sticky read» (после записи N секунд читаем с мастера для этого пользователя), маршрутизацией по типу запроса в конкретном бизнес-сценарии или GTID-ожиданием на реплике. Ещё одна ловушка — отчёты, которые висят на реплике и блокируют её догоняние мастера.',
                'difficulty' => 4,
                'topic' => 'system_design.distributed',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Чем партиционирование отличается от шардирования?',
                'answer' => 'Партиционирование — это разбиение одной большой таблицы на части (по диапазону дат, hash от ключа, списку значений) внутри одного экземпляра БД: оптимизатор может пропускать ненужные партиции (partition pruning), а DROP PARTITION удаляет старые данные за миллисекунды без долгого DELETE. Шардирование — это разнесение данных по разным физическим серверам, и тогда масштабируется не только диск, но и CPU/память/сеть, ценой того, что JOIN между шардами и глобальные транзакции становятся болезненными. Партиционирование — это про объём и удобство обслуживания, шардирование — про предел одного сервера.',
                'difficulty' => 4,
                'topic' => 'system_design.distributed',
            ],
        ];
    }
}
