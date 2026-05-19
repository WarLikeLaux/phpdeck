<?php

namespace Database\Seeders\Data\Categories\Database;

class Nosql
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Базы данных',
                'question' => 'Что такое NoSQL простыми словами?',
                'answer' => 'NoSQL ("Not Only SQL") - семейство БД, которые отходят от строгой реляционной модели. Часто гибче по схеме, лучше масштабируются горизонтально, но обычно не дают полноценных транзакций. Простыми словами: если SQL - это шкаф с одинаковыми ящиками с метками, то NoSQL - это коробки разной формы, в которые можно класть что угодно.',
                'difficulty' => 2,
                'topic' => 'database.nosql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Какие есть виды NoSQL баз?',
                'answer' => '1) Key-Value: ключ -> значение (Redis, DynamoDB, Memcached). 2) Document: хранит документы (обычно JSON) - MongoDB, CouchDB. 3) Column-family (wide-column): таблицы с динамическими столбцами - Cassandra, HBase. 4) Graph: вершины и рёбра - Neo4j, ArangoDB. Также сюда часто относят поисковые движки (Elasticsearch) и time-series БД (InfluxDB, Prometheus, OpenTSDB); TimescaleDB строго говоря не NoSQL — это расширение PostgreSQL с полноценной реляционной моделью и SQL.',
                'difficulty' => 2,
                'topic' => 'database.nosql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое Redis и его основные структуры данных?',
                'answer' => 'Redis - in-memory key-value БД, чрезвычайно быстрая. Поддерживает структуры: String, List, Hash, Set, Sorted Set (ZSet), Stream, HyperLogLog, Bitmap, Geo. Используется как: кэш, сессии, очереди (LPUSH/BRPOP, Streams), счётчики, лидерборды (ZSet), pub/sub, распределённые блокировки.',
                'code_example' => 'SET user:1:name "Иван"
GET user:1:name

LPUSH queue "task1"
RPOP queue

ZADD leaderboard 100 "alice" 200 "bob"
# ZREVRANGE deprecated с Redis 6.2 - используйте ZRANGE с REV
ZRANGE leaderboard 0 9 REV WITHSCORES

INCR page:home:views
EXPIRE page:home:views 60',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'database.nosql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое MongoDB?',
                'answer' => 'MongoDB - документная NoSQL БД. Хранит JSON-подобные документы (BSON) в коллекциях, без жёсткой схемы. Поддерживает богатые запросы по полям документа (включая вложенные), индексы, агрегационный пайплайн, транзакции (с 4.0). Хороша когда схема меняется часто, JOIN-ы дороги (формально $lookup есть, но не подходит для сложных связей), нужна гибкость. Минус: транзакции через несколько коллекций медленнее реляционных.',
                'code_example' => 'db.users.insertOne({ name: "Иван", tags: ["admin", "user"] });

db.users.find({ "tags": "admin" });

db.orders.aggregate([
    { $match: { status: "paid" } },
    { $group: { _id: "$user_id", total: { $sum: "$amount" } } }
]);',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'database.nosql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое Cassandra и для чего она?',
                'answer' => 'Apache Cassandra - распределённая wide-column NoSQL БД с упором на масштабируемость и доступность (AP по CAP). Идеально для записи огромных объёмов данных (логи, телеметрия, IoT, временные ряды). Архитектура без мастера (peer-to-peer), линейно масштабируется добавлением нод. Минусы: ограниченные запросы (нужно проектировать под них), eventual consistency.',
                'difficulty' => 4,
                'topic' => 'database.nosql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое Elasticsearch?',
                'answer' => 'Elasticsearch — распределённый поисковый и аналитический движок поверх Apache Lucene, документная NoSQL-БД. Хранит JSON-документы в индексах, разбитых на шарды и реплики. Главные сценарии: полнотекстовый поиск с relevance scoring (BM25), фасетный поиск и автокомплит, аналитика и агрегации (terms, histogram, percentiles), гео-поиск (geo_point, geo_shape), сбор и анализ логов в составе ELK/EFK-стека. Не подходит как primary store для строгой транзакционной нагрузки: нет настоящих ACID-транзакций между документами, eventual consistency между primary и replica шардами (изменение видно через ~1с после refresh), нет JOIN-ов. Поэтому ES почти всегда ставят рядом с реляционной БД (она — источник истины) и держат данные синхронизированными через Logstash, Kafka или Laravel Scout.',
                'difficulty' => 3,
                'topic' => 'database.nosql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Когда выбирать SQL, а когда NoSQL?',
                'answer' => 'SQL: когда схема стабильна и важна целостность, нужны сложные ad-hoc запросы и JOIN-ы по нескольким таблицам, требуются полноценные ACID-транзакции, важны строгие constraints (PK/FK/CHECK/UNIQUE). Типичные домены: финансы, e-commerce (платежи, остатки), CRM/ERP, любой business-critical с отчётностью. NoSQL: когда схема нестабильна или сильно вариативна (документная модель — MongoDB), нужно горизонтально масштабироваться на терабайты (key-value, wide-column — DynamoDB, Cassandra), нагрузка — это в основном lookup по ключу и большие объёмы записи без сложных JOIN-ов (ленты, чаты, IoT-телеметрия, логи), нужен полнотекстовый поиск (Elasticsearch), нужен кэш / сессии / очереди (Redis). В реальных системах почти всегда используют polyglot persistence: реляционка как источник истины + Redis для кеша + Elasticsearch для поиска + S3 для файлов. Главный антипаттерн — выбирать NoSQL "потому что модно" под классическую транзакционную нагрузку.',
                'difficulty' => 3,
                'topic' => 'database.nosql',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Как считать уникальные посетители (DAU/MAU) на десятках миллионов записей RAM-эффективно? HyperLogLog и Bloom Filter',
                'answer' => 'Точные структуры (Redis SET, COUNT(DISTINCT) в БД) на больших объёмах уникумов взрывают RAM: миллион уникальных IP в SET - это 50+ МБ; миллиард - 50 ГБ. Решение - вероятностные структуры данных, дающие приблизительный ответ за фиксированную память. HYPERLOGLOG (HLL) - алгоритм для cardinality estimation. В Redis команды PFADD (добавить), PFCOUNT (число уникальных), PFMERGE (объединить). Под капотом: элемент хешируется, биты хеша выбирают один из ~16k bucket-ов; в bucket пишется максимальное число лидирующих нулей в хеше. Кардинальность оценивается через гармоническое среднее (формула Flajolet-Martin). Свойства: фиксированный размер ~12 КБ независимо от объёма (миллиард уникумов - те же 12 КБ), стандартное отклонение ~0.81%, можно мерджить (HLL за неделю = merge 7 дневных). Идеально для DAU/MAU/уникальных IP/search queries за период. BLOOM FILTER - вероятностная структура для membership testing "элемент возможно есть" / "элемента ТОЧНО нет". K хеш-функций кладут биты в bitmap. False positives возможны (с настраиваемой вероятностью), false negatives - НИКОГДА. Применение: 1) Защита от cache penetration - перед БД проверяем "есть ли вообще такой ключ"; если Bloom говорит "нет" - не идём в БД (актуально для атак "cache miss storm"). 2) "Видел ли пользователь эту статью" - ленты соцсетей. 3) В Cassandra/HBase каждый SSTable имеет Bloom для пропуска при чтении. В Redis есть RedisBloom модуль с BF.ADD / BF.EXISTS. COUNT-MIN SKETCH - похожая структура для частот (top-K). Когда что: HLL - "сколько уникальных", Bloom - "был ли этот", CMS - "сколько раз встречался". Минусы: нельзя перечислить элементы; нельзя удалять из обычного Bloom (нужен Counting Bloom).',
                'code_example' => '# HyperLogLog в Redis - DAU
PFADD daily:2026-05-03 user_id_42
PFADD daily:2026-05-03 user_id_99
PFADD daily:2026-05-03 user_id_42  # дубль игнорируется
PFCOUNT daily:2026-05-03           # 2 (приблизительно)

# Память: ~12 КБ независимо от количества уникумов
MEMORY USAGE daily:2026-05-03      # ~12 KB

# Объединение - MAU из дневных HLL
PFMERGE monthly:2026-05 daily:2026-05-01 daily:2026-05-02
PFCOUNT monthly:2026-05            # уникальных за месяц

# Bloom Filter (Redis с RedisBloom модулем)
BF.RESERVE seen_articles 0.001 1000000   # error rate 0.1%, capacity 1M
BF.ADD seen_articles article:42
BF.EXISTS seen_articles article:42        # 1 (вероятно есть)
BF.EXISTS seen_articles article:9999      # 0 (точно нет)

# Защита от cache penetration в псевдокоде
# if not bloom.exists(f"user:{id}"):
#     return None              # точно нет в БД, не ходим в Postgres
# if data := redis.get(f"user:{id}"):
#     return data
# data = db.fetch(id)
# if data:
#     bloom.add(f"user:{id}")
#     redis.setex(f"user:{id}", 300, data)

# Postgres extension postgresql-hll
# CREATE EXTENSION hll;
# CREATE TABLE daily_uniques (day DATE PRIMARY KEY, users hll);
# UPDATE daily_uniques SET users = users || hll_hash_text(\'user_42\')
#   WHERE day = CURRENT_DATE;
# SELECT day, hll_cardinality(users) FROM daily_uniques;',
                'code_language' => 'bash',
                'difficulty' => 4,
                'topic' => 'database.nosql',
            ],
        ];
    }
}
