<?php

namespace Database\Seeders\Data\Categories\Database;

class Distributed
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Базы данных',
                'question' => 'Что такое consistent hashing простыми словами?',
                'answer' => '**Consistent hashing** (согласованное хеширование) — способ распределять ключи между серверами так, что **добавление/удаление узла приводит к перемещению только ~1/N данных**, а не всех, как при простом `hash(key) % N`.

**Как устроено хеш-кольцо:**
1. **Узлы** и **ключи** хешируются в одно числовое пространство (например, `0..2^32`);
2. Точки расставляются на **окружности**;
3. Для каждого ключа идём по кольцу **по часовой стрелке** до первого узла — он и владелец;
4. При **добавлении** узла он «откусывает» сегмент только у соседа справа;
5. При **удалении** ключи переходят к следующему по кольцу.

**Проблема перекоса** — если узлов мало, они могут оказаться рядом на кольце → неравномерная нагрузка.
**Решение:** **виртуальные узлы (vnodes)** — каждый физический сервер раскладывают `100–500` точек на кольце. Тогда даже при ±1 узле перераспределение **гладкое**.

**Сравнение с `hash % N`:**

| | `hash % N` | **Consistent hashing** |
|---|---|---|
| Переезд при `+1 узле` | **почти всё** | **~1/N** |
| Сложность | тривиальная | средняя (нужны vnodes) |
| Балансировка | идеальная | хорошая с vnodes |

**Где применяется:**
- **`DynamoDB`** — оригинальная статья Amazon Dynamo (2007);
- **`Cassandra`** — почти точная копия идеи Dynamo;
- **`memcached`** клиенты (`ketama`);
- **CDN** — выбор edge-узла под ключ;
- **partition assignment** в распределённых системах.',
                'difficulty' => 5,
                'topic' => 'database.distributed',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Master-slave vs Master-master репликация?',
                'answer' => 'Две классические топологии — кардинально разная сложность.

| | **Master-Slave** (primary-replica) | **Master-Master** (multi-leader) |
|---|---|---|
| Запись | **один** мастер | **несколько** мастеров |
| Чтение | мастер + реплики | любая нода |
| Конфликты записи | **невозможны** | **возможны** — нужно разрешение |
| Сложность | низкая | высокая |
| Failover | **promote replica** при падении мастера | автоматический в обе стороны |
| Геораспределение | плохо (запись через океан) | хорошо |

**Master-Slave:**
- запись летит на **primary**, бинарный лог стримится на реплики;
- реплики обычно **read-only**;
- при падении мастера один из replica **повышается** (`failover`) — обычно через **`Orchestrator`**, **`Patroni`**, **`Sentinel`** или managed (RDS, Cloud SQL).

**Master-Master подводные камни:**
- **конфликты при одновременной записи** одной строки в разных мастерах → стратегии: **LWW** (last-write-wins по timestamp), **CRDT**, ручной merge;
- **auto-increment коллизии** — лечат `auto_increment_offset`/`auto_increment_increment` (узел A пишет нечётные id, B — чётные);
- **`Galera Cluster`**, **`MySQL Group Replication`**, **`PostgreSQL BDR`** — синхронные multi-master с conflict detection на коммите.

**На практике:** **master-slave с автоматическим failover** покрывает 95% задач; master-master берут только для геораспределённой записи или blue-green миграций.',
                'difficulty' => 4,
                'topic' => 'database.distributed',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Asynchronous vs Synchronous replication?',
                'answer' => 'Три режима, разный trade-off между **производительностью** и **гарантией durability**.

| | **Async** | **Semi-sync** | **Sync** |
|---|---|---|---|
| Мастер отвечает клиенту | **сразу после записи на себя** | после получения **ACK ≥ 1 реплики** | после **fsync на всех** репликах |
| Latency | минимальный | +1 RTT | +1 RTT + worst replica |
| Риск потери при падении мастера | **есть** (несколько секунд писем) | **минимальный** | **нет** |
| If replica is down | мастер не страдает | таймаут → деградация в async | мастер **встаёт** |

**Asynchronous** (по умолчанию в MySQL и PostgreSQL streaming replication):
- мастер пишет в свой `redo`/`WAL`, сразу `COMMIT`-ит клиенту;
- bin-log/WAL стримится репликам **в фоне**;
- при крахе мастера до отправки **последние транзакции теряются**.

**Synchronous:**
- `Galera Cluster`, **MySQL Group Replication** (single-primary mode), **PostgreSQL synchronous_commit = on + synchronous_standby_names**;
- транзакция считается зафиксированной, **когда WAL применён на N репликах**;
- **гарантия zero data loss** ценой latency.

**Semi-sync** (MySQL **`rpl_semi_sync_master_enabled`**) — **компромисс**:
- мастер ждёт ACK от **хотя бы одной** реплики (не fsync, а получения в relay log);
- при таймауте **деградирует в async** — система не встаёт целиком.

**Правило:** для **финансов и платежей** — semi-sync минимум, лучше sync; для **аналитики и кешей** — async достаточно.',
                'code_example' => '-- PostgreSQL: настроить synchronous replication
-- postgresql.conf
-- synchronous_commit = on
-- synchronous_standby_names = \'ANY 1 (replica1, replica2)\'  -- ждём 1 из 2

-- MySQL semi-sync (master + slave plugins)
INSTALL PLUGIN rpl_semi_sync_master SONAME \'semisync_master.so\';
SET GLOBAL rpl_semi_sync_master_enabled = 1;
SET GLOBAL rpl_semi_sync_master_timeout = 1000; -- 1s до деградации в async',
                'code_language' => 'sql',
                'difficulty' => 4,
                'topic' => 'database.distributed',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое replication lag и как с ним жить?',
                'answer' => '**Replication lag** — задержка между моментом записи на мастер и моментом, когда эти данные становятся видны на реплике.

**Откуда берётся:**
- **сеть** — RTT мастер↔реплика, перегруженный канал;
- **single-threaded SQL thread** на реплике (классический MySQL) — реплика не успевает применять параллельные транзакции мастера;
- **тяжёлые транзакции** — `ALTER TABLE`, длинные `UPDATE`, бэкап на реплике;
- **DDL под нагрузкой**.

**Где это бьёт по продукту — read-after-write violation:**
> Пользователь добавил комментарий → редирект на список → запрос пошёл на отставшую реплику → **«моего комментария нет»**.

**Стратегии «жить с лагом»:**
1. **Read-your-writes routing** — пользователя, который только что писал, **N секунд читать с мастера** (sticky session, redis-флаг с TTL);
2. **Read-after-write per request** — внутри одной HTTP-сессии после `INSERT`/`UPDATE` принудительно читать **с мастера**;
3. **GTID / LSN tracking** — клиент запоминает позицию записи и ждёт, пока реплика её догонит;
4. **`synchronous_commit = remote_apply`** в PG (один из самых дорогих режимов) — мастер ждёт **применения** на реплике;
5. **Semi-sync replication** — снижает риск потери, но не лаг чтения;
6. **Мониторинг и алерты** на лаг:
   - PostgreSQL: `pg_stat_replication`, `pg_last_xact_replay_timestamp()`;
   - MySQL: `SHOW REPLICA STATUS \\G` → `Seconds_Behind_Source`, или `pt-heartbeat`;
   - SLO обычно `< 1s`, алерт `> 5s`.

**В Laravel:**
- `DB::connection(\'read\')` vs `\'write\'`;
- `sticky => true` в `database.php` — после первой записи в рамках запроса все чтения идут на мастер.',
                'code_example' => '// config/database.php — sticky-режим
\'mysql\' => [
    \'read\'  => [\'host\' => [\'replica1\', \'replica2\']],
    \'write\' => [\'host\' => [\'master\']],
    \'sticky\' => true, // после write — читаем с master до конца запроса
],

// PostgreSQL: мониторить лаг в секундах
// SELECT now() - pg_last_xact_replay_timestamp() AS replica_lag;',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'database.distributed',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'BASE vs ACID?',
                'answer' => 'Две **противоположные философии** дизайна систем.

**`ACID`** (реляционные БД, традиционные транзакции):
- **`A`tomicity** — транзакция либо целиком, либо вообще;
- **`C`onsistency** — после транзакции БД в валидном состоянии (constraints соблюдены);
- **`I`solation** — параллельные транзакции не мешают друг другу;
- **`D`urability** — закоммиченное сохранится после краха.

**`BASE`** (NoSQL, распределённые системы):
- **`B`asically `A`vailable** — приоритет **доступности и graceful degradation**: при отказе/перегрузке система **отвечает хоть как-то** (возможно с ослабленной консистентностью или партиальными данными), а не падает целиком;
- **`S`oft state** — состояние может **меняться без явного входного сигнала** (фоновая репликация, sync, expire);
- **`E`ventual consistency** — реплики **рано или поздно сойдутся**, но в моменте могут расходиться.

| | **ACID** | **BASE** |
|---|---|---|
| Согласованность | **строгая, всегда** | **eventual** |
| Доступность под нагрузкой | может отказать | **отвечает всегда** |
| Транзакции через сущности | да | обычно нет |
| Типичные системы | `PostgreSQL`, `MySQL InnoDB`, `Oracle` | `Cassandra`, `DynamoDB`, `Riak` |
| Когда брать | **финансы, биллинг, инвентарь** | **ленты соцсетей, аналитика, IoT** |

**На практике** — **гибрид**: PostgreSQL для денег + Cassandra для логов + Redis для счётчиков просмотров. Главное — **сознательно выбрать** под бизнес-требования, а не «потому что модно».',
                'difficulty' => 4,
                'topic' => 'database.distributed',
            ],
        ];
    }
}
