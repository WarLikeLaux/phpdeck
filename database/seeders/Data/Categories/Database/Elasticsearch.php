<?php

namespace Database\Seeders\Data\Categories\Database;

class Elasticsearch
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Базы данных',
                'question' => 'Что такое инвертированный индекс в Elasticsearch и почему он работает быстро?',
                'answer' => '**Инвертированный индекс** — структура, в которой каждому **термину** (слову после анализа) сопоставлен **posting list** — список документов, где этот термин встречается (иногда с позициями и частотами для скоринга).

**Прямой vs инвертированный:**

| | Что хранит |
|---|---|
| **Прямой** | `документ → текст` |
| **Инвертированный** | `термин → [doc1, doc5, doc20]` |

**Почему быстро:** при поиске ES **не сканирует тексты документов**:
1. берёт термин запроса (или несколько после анализа);
2. достаёт **готовые posting lists**;
3. **пересекает / объединяет** их (AND / OR).

Обе операции — почти **линейное время от размера списка совпадений**, не от размера корпуса. Поэтому полнотекстовый поиск по миллионам документов — **миллисекунды**.

**Та же идея** — в индексах книг и в **`GIN`-индексах PostgreSQL** для `tsvector`/`JSONB`.',
                'code_example' => '// Концептуально: документы
// doc1: "the quick brown fox"
// doc2: "the lazy fox"
// doc3: "brown sugar"

// Инвертированный индекс (после анализа: lowercase, без стоп-слов)
// term     postings
// brown    [doc1, doc3]
// fox      [doc1, doc2]
// lazy     [doc2]
// quick    [doc1]
// sugar    [doc3]

// Поиск "brown fox" = intersect(postings[brown], postings[fox])
//                   = [doc1, doc3] & [doc1, doc2] = [doc1]',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.elasticsearch',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое анализатор в Elasticsearch и из каких частей он состоит?',
                'answer' => '**Анализатор** — **конвейер обработки текста**, применяется и при **индексации** поля `text`, и при **поиске** по нему.

**Цель** — привести термины запроса и документа к **одной форме**, чтобы `"Apples"` находил `"apple"`.

**Три ступени конвейера:**

| Ступень | Что делает | Примеры |
|---|---|---|
| **1. character filters** | обработка сырого текста **до токенизации** | удалить HTML-теги, заменить `&` → `and` |
| **2. tokenizer** | режет текст на **токены** | `standard` (по границам слов), `n-gram` (для поиска по подстроке) |
| **3. token filters** | модифицируют каждый токен | `lowercase`, удаление стоп-слов (`the`, `и`), **стемминг** (`cars → car`), синонимы, обрезка длины |

**Важно:** анализатор на индексации и анализатор на поиске обычно **совпадают**, но могут быть **разными** — например, при индексации применять синонимы, при поиске — нет.',
                'code_example' => '// Пример определения custom analyzer
PUT /articles
{
  "settings": {
    "analysis": {
      "analyzer": {
        "ru_custom": {
          "type": "custom",
          "char_filter": ["html_strip"],
          "tokenizer": "standard",
          "filter":      ["lowercase", "russian_stop", "russian_stemmer"]
        }
      },
      "filter": {
        "russian_stop":    { "type": "stop",    "stopwords": "_russian_" },
        "russian_stemmer": { "type": "stemmer", "language": "russian" }
      }
    }
  },
  "mappings": {
    "properties": {
      "body": { "type": "text", "analyzer": "ru_custom" }
    }
  }
}

// Тест анализатора
POST /articles/_analyze
{ "analyzer": "ru_custom", "text": "<p>Купил БЕЛЫЕ носки</p>" }
// -> tokens: ["куп", "бел", "носк"]',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.elasticsearch',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое Standard Analyzer и когда нужен пользовательский анализатор?',
                'answer' => '**Standard Analyzer** — анализатор **по умолчанию** для типа `text`.

**Что делает:**
- режет текст по **границам слов** согласно Unicode (`UAX #29`);
- удаляет **пунктуацию**;
- приводит все токены к **`lowercase`**.

**Что НЕ делает по умолчанию** (вопреки ожиданиям):
- **стоп-слова** не удаляются;
- **стемминг** выключен.

Стандартного хватает для общих **англоязычных** задач и быстрых прототипов.

**Когда нужен пользовательский анализатор:**
- **email / артикулы** — `user@gmail.com` режется в `user` / `gmail` / `com` → **лучше `keyword`**;
- **русский / немецкий** — нужен стемминг и удаление стоп-слов;
- **поиск по подстроке без префикса** — `n-gram tokenizer` вместо `standard`;
- сохранить **спецсимволы** (`#hashtag`, `$price`).',
                'code_example' => '// Стандартный поведение: "Hello, World! user@gmail.com" ->
//   ["hello", "world", "user", "gmail.com"]
POST /_analyze
{ "analyzer": "standard", "text": "Hello, World! user@gmail.com" }

// Свой analyzer для русских товаров с морфологией
PUT /products
{
  "settings": {
    "analysis": {
      "analyzer": {
        "ru_products": {
          "tokenizer": "standard",
          "filter": ["lowercase", "russian_stop", "russian_stemmer"]
        }
      },
      "filter": {
        "russian_stop": { "type": "stop", "stopwords": "_russian_" },
        "russian_stemmer": { "type": "stemmer", "language": "russian" }
      }
    }
  }
}',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.elasticsearch',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Какие основные возможности Elasticsearch делают его востребованным?',
                'answer' => '**Elasticsearch** — распределённый **RESTful поисковый и аналитический движок** поверх **Apache Lucene**.

**Главные возможности:**

1. **Near real-time индексация** — документ доступен для поиска через **~1 сек** после записи (refresh interval).
2. **Полнотекстовый поиск:**
   - стемминг;
   - **fuzzy** (нечёткий) для опечаток;
   - **highlight** (подсветка совпадений);
   - фасеты, автокомплит.
3. **Документная модель** — JSON-документы через **REST API**, без SQL и схемы заранее.
4. **Аналитика** — агрегации (`terms`, `histogram`, `date_histogram`, `percentiles`) хорошо параллелятся по шардам, считаются за миллисекунды.
5. **Горизонтальное масштабирование** — шарды и реплики, отказоустойчивость из коробки.
6. **Богатый Query DSL** — `bool`, `range`, `geo`, `span`, `more_like_this`.

**Роль в архитектуре:** ставят **рядом с реляционной БД** как **поисковый и аналитический слой**, не как primary store.',
                'difficulty' => 3,
                'topic' => 'database.elasticsearch',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Почему Elasticsearch называют документо-ориентированным и что значит "бессхемный"?',
                'answer' => '**Документо-ориентированный:** единица хранения — **JSON-документ** с вложенными объектами и массивами, **а не строка плоской таблицы**.

Это близко к тому, как объекты живут в коде приложения: вместо `JOIN`-ов «пользователь + адреса + теги» можно положить всё в **один документ** и за один запрос вернуть готовую структуру.

**«Бессхемный» (schemaless)** — можно индексировать документы **без заранее объявленного mapping**. ES при первом `INSERT` сам определит типы (**dynamic mapping**):
- `"active": true` → `boolean`;
- `"age": 30` → `long`;
- `"name": "Ivan"` → `text + keyword`.

**В проде на это НЕ полагаются:**
- mapping задают **явно**, потому что dynamic часто **угадывает не то**:
  - дата `"2026-05-20"` определится как `text`, а не `date`;
  - число `"123"`, пришедшее строкой → `text`, не `long`;
- **поменять тип уже проиндексированного поля** без **полного `reindex`** нельзя.',
                'code_example' => '// Без mapping - dynamic mapping
POST /events/_doc
{ "user": "ivan", "age": 30, "tags": ["a", "b"] }
// ES сам решит: user=text+keyword, age=long, tags=text+keyword

// Явный mapping в проде
PUT /events
{
  "mappings": {
    "properties": {
      "user_id":    { "type": "keyword" },
      "happened_at":{ "type": "date" },
      "amount":     { "type": "double" },
      "title":      { "type": "text", "analyzer": "ru_custom",
                      "fields": { "raw": { "type": "keyword" } } }
    }
  }
}',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.elasticsearch',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Какую роль играет Apache Lucene внутри Elasticsearch?',
                'answer' => '**`Apache Lucene`** — низкоуровневая **Java-библиотека полнотекстового поиска**, на которой стоит весь Elasticsearch (и OpenSearch, и Solr).

**Что делает Lucene:**
- реализует **инвертированный индекс**;
- **токенизацию** и анализ текста (через analyzers);
- **скоринг по `BM25`** (с 6.0; раньше был TF-IDF);
- **структуры данных на диске** — segments, posting lists, doc values, stored fields;
- **near-realtime** search через `IndexWriter` + `IndexReader`.

**Что добавляет Elasticsearch поверх Lucene:**

| Слой | Что даёт |
|---|---|
| **Distributed layer** | шардирование, репликация, маршрутизация запросов |
| **Cluster management** | discovery, master election (Zen Discovery → Voting в 7+) |
| **REST API + JSON** | удобная работа из любого языка |
| **Query DSL** | `bool`, `range`, `geo`, агрегации, scripted-queries |
| **Snapshot / restore** | бэкапы в S3/HDFS |
| **Index lifecycle management** (ILM) | rollover, hot-warm-cold-delete фазы |
| **Security + Kibana** | RBAC, audit, UI |

**Ключевой факт:** **каждый шард ES — это отдельный Lucene-индекс**. Когда ES получает search-запрос, он:
1. **fan-out** — рассылает запрос по всем шардам индекса;
2. каждый шард **выполняет Lucene search** локально;
3. координирующий узел **сливает результаты** (merge sort) и возвращает клиенту.

**`Solr`** — другая популярная обёртка над Lucene, конкурент ES. Архитектура отличается, но Lucene под капотом тот же.',
                'difficulty' => 4,
                'topic' => 'database.elasticsearch',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое шардирование и репликация в Elasticsearch и зачем они нужны?',
                'answer' => '**Шардирование** делит индекс на **N primary shards** — это N **независимых Lucene-индексов**, распределённых по узлам кластера.

**Маршрутизация документа:** `hash(routing_key) % N` (по умолчанию `routing_key = _id`).

**Что даёт:**
- хранить **объём**, который не влезает на один узел;
- **параллелить поиск** — каждый шард обрабатывает свою часть запроса.

**Репликация** создаёт **K копий** каждого primary shard (**replica shards**) на **других** узлах.

**Роли реплик:**
- **отказоустойчивость** — при падении узла реплика **повышается до primary**;
- **read-throughput** — реплики обслуживают часть search-запросов.

**Важная гибкость:**

| Параметр | Меняется? |
|---|---|
| `number_of_shards` (primary) | **фиксируется** при создании, только через `reindex` |
| `number_of_replicas` | **на лету** |

**Типовая конфигурация:** 3 узла × 3 primary × 1 replica = 6 шардов, по 2 на узел.',
                'code_example' => 'PUT /products
{
  "settings": {
    "number_of_shards":   3,   // фиксируется при создании
    "number_of_replicas": 1    // можно менять
  }
}

// Поменять количество реплик на лету
PUT /products/_settings
{ "number_of_replicas": 2 }

// Состояние кластера
GET /_cluster/health     // status: green / yellow / red
GET /_cat/shards/products?v',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.elasticsearch',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое мэппинг (mapping) в Elasticsearch?',
                'answer' => '**Mapping** — описание **схемы индекса**: какие поля у документа, их **типы** и какие **анализаторы** применяются к `text`-полям.

**Поддерживаемые типы:** `text`, `keyword`, `date`, `long`, `double`, `boolean`, `geo_point`, `nested`, `object`.

**Ключевая разница — `text` vs `keyword`:**

| | `text` | `keyword` |
|---|---|---|
| Анализ | да (токенизация, `lowercase`, стемминг) | нет, хранится **целиком** |
| `match`-поиск | да | нет |
| Точный `term`-поиск | плохо | да |
| Сортировка / агрегации | **нельзя** | **да** |

**Multi-field** — частый паттерн: `title.text` для поиска + `title.keyword` для агрегаций.

**Подводные камни:**
- формально ES бессхемный (**dynamic mapping**), но в проде mapping задают **явно** — иначе дата → `text`, число строкой → `text`, и поиск/агрегации сломаются;
- **тип поля после первой индексации НЕ меняется** — нужен **`reindex`** в новый индекс с правильным mapping.',
                'code_example' => 'PUT /products
{
  "mappings": {
    "properties": {
      "sku":        { "type": "keyword" },
      "title": {
        "type": "text",
        "analyzer": "ru_custom",
        "fields": {
          "raw": { "type": "keyword" }  // для агрегаций/сортировки
        }
      },
      "price":      { "type": "double" },
      "created_at": { "type": "date", "format": "strict_date_optional_time" },
      "location":   { "type": "geo_point" },
      "tags":       { "type": "keyword" }
    }
  }
}',
                'code_language' => 'sql',
                'difficulty' => 3,
                'topic' => 'database.elasticsearch',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'В чём разница между запросами match и term в Elasticsearch?',
                'answer' => 'Два базовых запроса с **разной семантикой обработки** — частая ловушка для новичков.

| | **`match`** | **`term`** |
|---|---|---|
| Применяет анализатор к запросу | **да** (тот же, что у поля) | **нет** — буквально |
| Тип поля | **`text`** | **`keyword`**, число, дата, boolean |
| `"Apple iPhone"` сматчит | `apple`, `iphone` (после lowercase + tokenize) | только точную строку `"Apple iPhone"` |
| Скоринг | да (BM25) | filter-семантика (без скора, кэшируется) |
| Использование | **полнотекстовый поиск** | **точное совпадение** (статус, тег, id) |

**Подводный камень term по text-полю:**

```json
PUT /products  // text-поле проходит через standard analyzer (lowercase)
{
  "mappings": { "properties": { "name": { "type": "text" } } }
}
PUT /products/_doc/1 { "name": "Apple iPhone" }
// В индексе токены: ["apple", "iphone"]

GET /products/_search
{ "query": { "term": { "name": "Apple" } } }
// → пустой результат! В индексе нет "Apple", есть "apple"
```

**Multi-field паттерн:** объявляют **`title.text`** для поиска и **`title.keyword`** для агрегаций/exact match:

```json
"title": {
  "type": "text",
  "fields": {
    "keyword": { "type": "keyword", "ignore_above": 256 }
  }
}
// поиск: GET /_search { "query": { "match":   { "title":         "iphone" }}}
// exact: GET /_search { "query": { "term":    { "title.keyword": "iPhone 15 Pro" }}}
// agg:   GET /_search { "aggs":  { "by":      { "terms": { "field": "title.keyword" }}}}
```

**Правило:**
- **полнотекст** → `match` по `text`;
- **точное** → `term` по `keyword` или цифровому полю;
- **диапазон** → `range`;
- **сочетание** → `bool`.',
                'difficulty' => 4,
                'topic' => 'database.elasticsearch',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Зачем нужен bool-запрос в Elasticsearch и из каких частей он состоит?',
                'answer' => '**`bool`-запрос** — **основной комбинатор** Query DSL, через который строят сложные запросы из простых.

**Четыре секции:**

| Секция | Что значит | Влияет на скор? | Кэшируется? |
|---|---|---|---|
| **`must`** | условие **должно** совпасть (`AND`) | **да** | нет |
| **`should`** | **«желательно»** — повышает скор при совпадении; в режиме без `must` хотя бы один `should` обязателен (`OR`) | **да** | нет |
| **`must_not`** | условие **не должно** совпасть (`NOT`) | нет (filter context) | **да** |
| **`filter`** | условие должно совпасть, **но без вклада в скор** | нет (filter context) | **да** |

**Главное правило:** **точные/числовые/булевые** условия (статус, диапазон цены, теги) — кладут в **`filter`**, чтобы:
- запрос **не считал скоринг** (быстрее);
- результат **кэшировался** в node query cache.

**Полнотекстовое сопоставление** — в `must`/`should`, чтобы оно влияло на ранжирование.

**Дополнительные параметры:**
- **`minimum_should_match`** — сколько `should` должны выполниться (`"2"`, `"75%"`);
- **`boost`** — множитель вклада в скор отдельного suby-запроса.

**Полный пример:**

```json
GET /products/_search
{
  "query": {
    "bool": {
      "must":   [{ "match": { "title": "iphone pro" }}],
      "should": [
        { "match": { "description": "новинка" }},
        { "match": { "tags":        "топ"     }}
      ],
      "must_not": [{ "term": { "discontinued": true }}],
      "filter":   [
        { "term":  { "category": "phone" }},
        { "range": { "price": { "gte": 50000, "lte": 150000 }}}
      ],
      "minimum_should_match": 1
    }
  }
}
```',
                'difficulty' => 4,
                'topic' => 'database.elasticsearch',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Чем Elasticsearch отличается от реляционной базы и когда его выбирают?',
                'answer' => '**ES и реляционные БД оптимизированы под разные задачи** — почти всегда живут **рядом**, а не вместо.

| | **Реляционная (PG/MySQL)** | **Elasticsearch** |
|---|---|---|
| Модель | таблицы + строки + `JOIN` | JSON-документы в индексах |
| Схема | **строгая**, миграции | **гибкая**, dynamic mapping |
| Транзакции | **полный ACID** | **нет** (атомарность только на 1 документ) |
| `JOIN` | произвольные | **нет** (только `nested`/`parent-child`) |
| Полнотекст | базовый (`FULLTEXT`, `pg_trgm`) | **профильное**, с BM25, фасетами, fuzzy |
| Аналитика | OK через `GROUP BY` | **очень быстро** через aggregations |
| Гео-поиск | `PostGIS` extension | нативно (`geo_point`, `geo_shape`) |
| Согласованность | strong | **eventual** (~1 сек refresh) |
| Масштабирование чтения | реплики | шарды + реплики, шардинг из коробки |

**Когда выбирают ES:**
- **полнотекстовый поиск** с ранжированием по релевантности (e-commerce, support knowledge base);
- **логи и observability** (стек ELK/EFK + Kibana);
- **аналитика и фасеты** на больших объёмах (миллиарды событий);
- **гео-поиск** (доставка, карты);
- **autocomplete / suggestions** (`search-as-you-type`).

**Когда НЕ выбирают как primary store:**
- **деньги, остатки, биллинг** — нужен ACID;
- сильно реляционные данные с `JOIN`;
- сценарии «прочитал ровно своё свежевписанное» — eventual consistency мешает.

**Типичная архитектура прода:**
1. **PostgreSQL** — источник истины (orders, users, payments);
2. **Elasticsearch** — поисковый/аналитический слой;
3. **Sync** через **Logstash** / `Debezium` (CDC) / `Laravel Scout` / явные ивенты в Kafka.',
                'difficulty' => 4,
                'topic' => 'database.elasticsearch',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что значит eventual consistency у Elasticsearch на практике?',
                'answer' => 'ES — **near-realtime** (NRT), а **не realtime**. Документ становится **виден в поиске не сразу**.

**Что происходит при `INSERT` (`POST /index/_doc`):**
1. Документ пишется в **`translog`** (Write-Ahead Log, durability) и в **in-memory buffer**;
2. Каждые **`refresh_interval`** (по умолчанию **1 секунда**) buffer становится новым **segment** — теперь документ **доступен поиску**;
3. Каждые **`flush`** (~30 минут или при наполнении translog) segments фиксируются на диск, translog обнуляется;
4. **Replica шарды** догоняют primary **асинхронно** — два `GET` по разным узлам могут вернуть разную картину.

**Сравнение операций:**

| Операция | Видимость |
|---|---|
| **`GET /index/_doc/{id}`** | **сразу** — читает прямо из translog/buffer |
| **`_search`** (любой query) | **через ~1 сек** после refresh |
| **`_update`** + следующий **`_search`** | через ~1 сек |

**Параметры контроля:**

| Параметр | Что делает |
|---|---|
| **`refresh_interval`** | период refresh. На bulk-load ставят `-1` (отключить) → ускоряет в 5-10× |
| **`?refresh=wait_for`** | блокирует ответ до следующего refresh — критичные сценарии |
| **`?refresh=true`** | принудительный refresh на этой операции (**не использовать** на нагруженных индексах — насилует кластер) |

**Боль в приложении:**
- сохранил → редирект на список → **«моего нет»** → пользователь жмёт F5;
- решения: **`?refresh=wait_for`** на критичных операциях; **показывать оптимистично** из локального стейта; кешировать в Redis на время до refresh.

**Bulk indexing best practice:**
```
PUT /index/_settings { "refresh_interval": "-1" }    // выключаем refresh
// bulk insert N миллионов документов
PUT /index/_settings { "refresh_interval": "1s" }    // возвращаем
POST /index/_refresh                                 // один финальный refresh
```',
                'difficulty' => 4,
                'topic' => 'database.elasticsearch',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Как PHP-приложение интегрируется с Elasticsearch?',
                'answer' => 'Три уровня интеграции — **от низкого к высокому**.

**1. Официальный клиент** — **`elasticsearch/elasticsearch`** (composer):
- строит REST-запросы к кластеру;
- принимает PHP-массивы для `index`/`search`/`bulk`/`update`;
- возвращает ответ ES как массив;
- поддерживает **connection pool**, **retry**, **sniffing**.

**2. Laravel Scout с драйвером ES** — **`matchish/laravel-scout-elasticsearch`** / `babenkoivan/scout-elasticsearch-driver`:
- модели объявляют `use Searchable`;
- Scout **подписывается на события Eloquent** (`created`/`updated`/`deleted`) → сам индексирует/удаляет документы;
- метод `Model::search(\'query\')->get()` → выполняет search в ES.

**3. CDC pipeline** для масштабных систем — Postgres/MySQL → **Debezium** → **Kafka** → ES consumer.

**Best practices индексации:**

| Подход | Когда |
|---|---|
| Sync (в том же запросе) | мало данных, простая модель |
| **Async через queue** (Job → bulk API) | прод нагрузки — не блокировать HTTP-запрос |
| **Bulk API** | пачки 500-5000 документов за раз |
| **`refresh_interval=-1`** на массовой загрузке | начальная заливка миллионов документов |

**Антипаттерны:**
- **по одному документу** через `index` API в цикле — кладёт кластер на миллионах;
- **синхронная индексация** в HTTP-запросе — пользователь ждёт ES;
- **`?refresh=true`** на каждой записи — насилует merge-thread кластера.',
                'code_example' => '<?php
// Низкий уровень — официальный клиент
use Elastic\Elasticsearch\ClientBuilder;

$client = ClientBuilder::create()->setHosts([\'http://es:9200\'])->build();

$client->index([
    \'index\' => \'products\',
    \'id\'    => 42,
    \'body\'  => [\'title\' => \'iPhone 15\', \'price\' => 99990],
]);

$response = $client->search([
    \'index\' => \'products\',
    \'body\'  => [\'query\' => [\'match\' => [\'title\' => \'iphone\']]],
]);

// Laravel Scout
class Product extends Model {
    use \Laravel\Scout\Searchable;

    public function toSearchableArray(): array {
        return [
            \'id\'    => $this->id,
            \'title\' => $this->title,
            \'price\' => $this->price,
        ];
    }
}

// В контроллере
$results = Product::search(\'iphone\')->where(\'in_stock\', true)->paginate(20);

// Bulk через очередь
class ReindexProductsJob implements ShouldQueue {
    public function handle(\Elastic\Elasticsearch\Client $es): void {
        Product::query()->chunk(1000, function ($chunk) use ($es) {
            $body = [];
            foreach ($chunk as $p) {
                $body[] = [\'index\' => [\'_index\' => \'products\', \'_id\' => $p->id]];
                $body[] = $p->toSearchableArray();
            }
            $es->bulk([\'body\' => $body]);
        });
    }
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'database.elasticsearch',
            ],
        ];
    }
}
