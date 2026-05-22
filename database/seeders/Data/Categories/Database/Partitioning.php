<?php

namespace Database\Seeders\Data\Categories\Database;

class Partitioning
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Базы данных',
                'question' => 'Что такое партиционирование (partitioning) простыми словами?',
                'answer' => '**Партиционирование** — разделение одной таблицы на **несколько физических частей (партиций)** внутри **одной БД**, **прозрачно для приложения**. С точки зрения SQL — это одна таблица.

**Партиционирование ≠ шардирование:**

| | **Партиционирование** | **Шардирование** |
|---|---|---|
| Где данные | **один сервер**, разные сегменты диска | **разные серверы** |
| Прозрачность | прозрачно | нужен роутер запросов |
| Что масштабирует | I/O, удаление старых данных | объём и QPS |
| Зачем | управляемость, быстрая чистка | горизонтальный рост |

**Три типа партиций:**
- **`RANGE`** — по диапазону (часто по дате/`id`);
- **`LIST`** — по явному списку значений (страна, тенант);
- **`HASH`** — `hash(key) % N`, равномерно.

**Главные плюсы:**
- **partition pruning** — оптимизатор **читает только нужные партиции** (`WHERE created_at >= \'2026-01-01\'` → только январь);
- **`DROP PARTITION`** удаляет старые данные **мгновенно** (без `DELETE`-сканов и vacuum) — золотое решение для time-series и логов;
- **локальные индексы** компактнее (меньше B-tree на партицию);
- параллельное обслуживание (`VACUUM`, `REINDEX`) per partition.

**Подводные камни:**
- **partition key должен быть в `WHERE`** для pruning — иначе сканируются все партиции;
- **`UNIQUE`-индекс** должен включать partition key (нет глобальных уникальных индексов в PG; в MySQL ограничение другое);
- **cross-partition queries** дороже обычных;
- **много партиций (тысячи)** → накладные расходы планировщика, лимиты на open files.

**Когда нужно:**
- таблица **> 100 ГБ** или **> 100M строк**;
- естественное разделение по **времени** (логи, события, метрики) с регулярным удалением старого;
- partition pruning даёт **на порядок** меньше I/O.',
                'difficulty' => 4,
                'topic' => 'database.partitioning',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Range vs List vs Hash партиционирование?',
                'answer' => 'Три классические стратегии — **выбор сильно влияет на pruning и удаление старых данных**.

| | **`RANGE`** | **`LIST`** | **`HASH`** |
|---|---|---|---|
| Принцип | по диапазонам значений | по явному списку | `hash(key) % N` |
| Типичный ключ | `created_at`, `id` | `country`, `tenant_id`, `status` | `user_id` |
| Pruning | отлично для `WHERE col BETWEEN ...` | отлично для `WHERE col IN (...)` | только для `WHERE col = ?` |
| Удаление старых | **`DROP PARTITION`** за месяц = мгновенно | переезд тенанта/страны | нет очевидного «старого» |
| Добавление партиции | руками или auto (`pg_partman`) | руками при появлении новой страны | фиксированное N |
| Распределение | **неравномерное** (текущий месяц горячий) | зависит от бизнеса | **равномерное** |
| Hot-partition риск | **высокий** (последняя партиция) | низкий-средний | низкий |

**`RANGE` — типичный случай для time-series:**
- партиция за каждый месяц/день;
- старые партиции **детачат и архивируют** или дропают;
- классика для логов, событий, метрик, IoT.

**`LIST` — для естественных категорий:**
- multi-tenant (партиция на тенанта);
- по странам (RU/EU/US — разное законодательство, GDPR);
- по статусам с очень разной активностью.

**`HASH` — когда нет естественного диапазона:**
- нужно **равномерное** распределение I/O;
- секционируем по `user_id`/`order_id` для параллельных скан-ов;
- **не даёт быстрого `DROP PARTITION`** для очистки — для time-series подходит хуже.

**`Composite` partitioning** (PG 11+, MySQL):
- `RANGE` по дате + `HASH` по `tenant_id` внутри каждой даты;
- двухмерное pruning, для очень больших таблиц.',
                'code_example' => '-- PostgreSQL Range Partitioning
CREATE TABLE events (
    id BIGSERIAL,
    occurred_at TIMESTAMP NOT NULL,
    payload JSONB
) PARTITION BY RANGE (occurred_at);

CREATE TABLE events_2024_01 PARTITION OF events
    FOR VALUES FROM (\'2024-01-01\') TO (\'2024-02-01\');

CREATE TABLE events_2024_02 PARTITION OF events
    FOR VALUES FROM (\'2024-02-01\') TO (\'2024-03-01\');

-- List
CREATE TABLE users PARTITION BY LIST (country);
CREATE TABLE users_ru PARTITION OF users FOR VALUES IN (\'RU\', \'BY\', \'KZ\');
CREATE TABLE users_us PARTITION OF users FOR VALUES IN (\'US\', \'CA\');

-- Hash
CREATE TABLE logs PARTITION BY HASH (user_id);
CREATE TABLE logs_0 PARTITION OF logs FOR VALUES WITH (modulus 4, remainder 0);',
                'code_language' => 'sql',
                'difficulty' => 5,
                'topic' => 'database.partitioning',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Какие виды партиционирования есть в Postgres и какие проблемы они решают?',
                'answer' => '**Postgres declarative partitioning** (с PG 10) поддерживает три типа:

| Тип | Использование |
|---|---|
| **`PARTITION BY RANGE`** | по диапазону, чаще по дате — типично для **логов и time-series**, позволяет **`DROP PARTITION`** старых данных мгновенно |
| **`PARTITION BY LIST`** | по явному перечислению (страна, тенант, статус) |
| **`PARTITION BY HASH`** | равномерное распределение (PG 11+) |

**Какие проблемы решают:**
1. **Управляемость больших таблиц** — отдельный `VACUUM`, `REINDEX`, статистика per partition;
2. **Быстрое удаление старого** — `DROP PARTITION` вместо `DELETE` + `VACUUM`;
3. **Partition pruning** — оптимизатор **не сканирует** партиции, не подходящие под `WHERE`;
4. **Локальные индексы** — компактнее, быстрее `B-tree` lookup внутри партиции;
5. **Параллельные планы** — каждая партиция может обрабатываться отдельным worker-ом.

**Эволюция фич по версиям PG:**

| Версия | Что появилось |
|---|---|
| **PG 10** | declarative partitioning (range + list), декларативный синтаксис |
| **PG 11** | `HASH` partitioning, `FK` **из** партиционированной таблицы, default partition, `UPDATE` с переездом между партициями, локальные индексы автоматически на ребёнке |
| **PG 12** | **`FK` на** партиционированную таблицу (как referenced), быстрее pruning |
| **PG 13+** | runtime pruning, паралелизация планов, logical replication партиционированных таблиц |

**Ограничения, которые продолжают болеть:**
- **глобальный `UNIQUE`** через все партиции — **нет**; partition key обязан быть в любом `UNIQUE`/`PRIMARY KEY`;
- `INSERT` без partition key → fail;
- `ATTACH PARTITION` блокируется на проверку CHECK constraint — на проде ставят `CHECK` заранее;
- автоматическое создание партиций «на следующий месяц» — через **`pg_partman`** extension или cron.',
                'code_example' => 'CREATE TABLE events (
    id bigserial, created_at timestamptz NOT NULL, payload jsonb
) PARTITION BY RANGE (created_at);

CREATE TABLE events_2026_05 PARTITION OF events
    FOR VALUES FROM (\'2026-05-01\') TO (\'2026-06-01\');',
                'code_language' => 'sql',
                'difficulty' => 5,
                'topic' => 'database.partitioning',
            ],
        ];
    }
}
