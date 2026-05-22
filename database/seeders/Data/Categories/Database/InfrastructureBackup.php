<?php

namespace Database\Seeders\Data\Categories\Database;

class InfrastructureBackup
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Базы данных',
                'question' => 'Что такое connection pooling и зачем pgbouncer?',
                'answer' => '**Открытие соединения с БД — дорогая операция:**
- **TLS-handshake** (несколько RTT);
- **аутентификация** (`SCRAM`/`MD5`);
- **fork процесса** в PostgreSQL (отдельный backend на каждое соединение, ~10 МБ RAM);
- инициализация сессионных параметров.

**Connection pool** — набор уже открытых соединений, **переиспользуемых** приложением. PHP-FPM создаёт пул на воркере, в долгоживущих процессах (`Octane`, `Roadrunner`) — на процесс приложения.

**`pgbouncer`** — **внешний** пулер для PostgreSQL, между приложением и БД:
- мультиплексирует **тысячи клиентских** соединений на **десятки** реальных к Postgres;
- очень лёгкий (одно ядро держит 10K+ клиентов);
- идёт отдельным сервисом или sidecar.

**Три режима pgbouncer:**

| Режим | Соединение клиента ↔ PG | Когда отдаётся обратно в пул | Ограничения |
|---|---|---|---|
| **`session`** | один-к-одному на всю сессию | при `disconnect` клиента | дефолт, поддерживает всё |
| **`transaction`** | один-к-одному **на транзакцию** | при `COMMIT`/`ROLLBACK` | **самый частый**; нельзя `LISTEN`/`NOTIFY`, prepared statements, session-variables |
| **`statement`** | один-к-одному на запрос | сразу после запроса | нельзя транзакции (используется крайне редко) |

**Выбор:** **`transaction` mode** — обычно лучший trade-off для веб-приложений.

**Без пулера** большое приложение легко уроет PG:
- `max_connections = 100` — типовой потолок;
- 50 PHP-воркеров × 2 коннекшна каждый = 100 → новые HTTP-запросы валятся с `too many connections`;
- каждый процесс PG потребляет память — 1000 соединений = ~10 ГБ RAM **только под backends**.

**Альтернативы:**
- **`PgCat`** — современный реворк pgbouncer с шардированием;
- **`AWS RDS Proxy`** — managed;
- встроенный **MySQL Router** / `ProxySQL` для MySQL;
- **`pgpool-II`** — старая альтернатива с replication/load-balancing.',
                'code_example' => '# pgbouncer.ini — типовая прод-конфигурация
[databases]
mydb = host=postgres.internal port=5432 dbname=mydb

[pgbouncer]
listen_port = 6432
listen_addr = *
auth_type   = scram-sha-256

pool_mode               = transaction
max_client_conn         = 10000   # сколько клиентов держим
default_pool_size       = 25      # реальных коннектов к PG на пользователя/DB
reserve_pool_size       = 5       # резерв при наплыве

# В Laravel — просто меняем порт на 6432
# .env:
# DB_HOST=pgbouncer.internal
# DB_PORT=6432',
                'code_language' => 'bash',
                'difficulty' => 4,
                'topic' => 'database.infrastructure_backup',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое WAL и crash recovery в PostgreSQL?',
                'answer' => '**`WAL`** (Write-Ahead Log) — журнал, в который PostgreSQL **записывает все изменения ДО** применения их к страницам данных. Это базовый принцип **WAL = Write-Ahead Logging** во всех серьёзных БД (InnoDB redo log — то же самое).

**Зачем нужен:**
- **`D`urability из ACID** — после `COMMIT` транзакция гарантированно сохранится;
- **crash recovery** — после краха при старте PG проигрывает WAL и восстанавливает зафиксированные транзакции;
- **базис для репликации** — streaming replication отправляет WAL на реплики;
- **базис для PITR** (Point-in-Time Recovery) — восстановление на любую секунду.

**Как работает (flow `INSERT`/`UPDATE`):**
1. Транзакция модифицирует страницу в **`shared_buffers`** (RAM);
2. Запись в WAL → **`pg_wal/`** (раньше `pg_xlog/`);
3. На `COMMIT` — **`fsync` WAL** на диск (это и есть точка durability);
4. **`checkpoint`** в фоне периодически сбрасывает грязные страницы в основной datafile;
5. После checkpoint **WAL до этой точки можно архивировать или удалять**.

**Crash recovery:**
- PG читает последнюю запись `pg_control` → определяет последний checkpoint;
- **проигрывает WAL** от checkpoint и до конца — приводит datafiles в состояние **«как было на момент краха»**;
- транзакции **с `COMMIT` в WAL** восстанавливаются;
- транзакции **без `COMMIT`** откатываются (как будто ROLLBACK).

**Ключевые параметры:**

| Параметр | Что делает |
|---|---|
| **`wal_level`** | `replica` (default) / `logical` (для logical replication) |
| **`synchronous_commit`** | `on` (fsync на коммите) / `off` (асинхронно — потеря < 200ms при крахе) |
| **`max_wal_size`** | сколько WAL держать между checkpoint-ами |
| **`archive_mode`** + **`archive_command`** | копирование WAL в S3 / NAS для PITR |

**PITR через `WAL`:**
1. Полный бэкап через **`pg_basebackup`**;
2. Архивируем WAL через `archive_command`;
3. Restore: восстанавливаем basebackup → задаём `recovery_target_time`;
4. PG **проигрывает WAL до нужной секунды**.

**Инструменты:** **`pgBackRest`**, **`WAL-G`**, **`Barman`** — стандарт де-факто для управляемого PITR.',
                'code_example' => '-- Смотреть текущую позицию WAL
SELECT pg_current_wal_lsn();

-- Лаг реплики в байтах
SELECT pid, application_name, client_addr,
       pg_wal_lsn_diff(pg_current_wal_lsn(), replay_lsn) AS lag_bytes
FROM pg_stat_replication;

-- Принудительный checkpoint (на проде осторожно)
CHECKPOINT;

-- Размер WAL-директории
SELECT pg_size_pretty(sum(size)) FROM pg_ls_waldir();

-- archive_command в postgresql.conf для PITR в S3 (WAL-G)
-- archive_mode = on
-- archive_command = \'wal-g wal-push %p\'
-- restore_command = \'wal-g wal-fetch %f %p\'',
                'code_language' => 'sql',
                'difficulty' => 5,
                'topic' => 'database.infrastructure_backup',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Какие есть стратегии резервного копирования?',
                'answer' => '**По объёму копирования:**

| Стратегия | Что сохраняет | Плюсы | Минусы |
|---|---|---|---|
| **Full** | вся БД | простое восстановление | место + время |
| **Incremental** | изменения с последнего full/incremental | экономно по месту | восстановление длиннее (накатываем цепочку) |
| **Differential** | изменения с последнего **full** | быстрее восстановление, чем incremental | растёт со временем |
| **PITR** (Point-in-Time) | full + накат `WAL` до момента | восстановление **на любую секунду** | сложнее настроить |

**По типу — логические vs физические:**

| | Логические (`pg_dump`) | Физические (`pg_basebackup`) |
|---|---|---|
| Что копируется | SQL-команды | бинарные файлы кластера |
| Скорость | медленнее | **быстрее** |
| Переносимость | между версиями PG | только та же major-версия |
| Гранулярность | таблица / схема | весь кластер |

**Best practice:**
- регулярные **расписанные** бэкапы (cron, pgBackRest, WAL-G);
- **тестировать восстановление** — бэкап без проверки = нет бэкапа;
- хранить **в другом регионе** (защита от пожара в ЦОД);
- знать свои **RPO** (сколько данных не жалко потерять) и **RTO** (сколько готовы простаивать).',
                'code_example' => '# PostgreSQL логический бэкап
pg_dump -U user -d mydb -F c -f backup.dump

# Восстановление
pg_restore -U user -d mydb backup.dump

# Физический бэкап
pg_basebackup -D /backup -F tar -X stream -P',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'database.infrastructure_backup',
            ],
        ];
    }
}
