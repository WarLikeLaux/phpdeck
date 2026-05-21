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
                'answer' => 'Lucene — это низкоуровневая Java-библиотека полнотекстового поиска, которая реализует инвертированный индекс, токенизацию, скоринг по BM25 и собственно структуры данных на диске. Elasticsearch оборачивает Lucene в распределённый слой: добавляет шардирование, репликацию, кластеризацию, REST API и query DSL. Каждый шард в ES — это, по сути, отдельный индекс Lucene.',
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
                'answer' => 'match анализирует текст запроса тем же анализатором, что и индексируемое поле, поэтому подходит для полнотекстового поиска по text-полям с учётом стемминга и регистра. term ищет точное совпадение токена без анализа — его применяют по keyword-полям, числам, датам, флагам. Если запустить term по text-полю с фильтром нижнего регистра, поиск по «Apple» не найдёт ничего, потому что в индексе лежит «apple».',
                'difficulty' => 4,
                'topic' => 'database.elasticsearch',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Зачем нужен bool-запрос в Elasticsearch и из каких частей он состоит?',
                'answer' => 'bool-запрос комбинирует несколько условий и состоит из четырёх секций: must (должно совпасть, влияет на скор), should (повышает скор при совпадении), must_not (исключает документы) и filter (отфильтровывает без вклада в релевантность и кэшируется). Через bool строят сложные запросы с обязательными и желательными критериями, фильтрами по статусу, тегам, диапазонам — это базовый кирпич query DSL.',
                'difficulty' => 4,
                'topic' => 'database.elasticsearch',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Чем Elasticsearch отличается от реляционной базы и когда его выбирают?',
                'answer' => 'Реляционная БД оптимизирована под транзакции, нормализованную схему и точные выборки по ключам, тогда как Elasticsearch — под полнотекстовый и аналитический поиск по большим объёмам JSON-документов с релевантным ранжированием. ES не даёт честных ACID-транзакций и согласованных join-ов, зато на порядки быстрее ищет по тексту, фасетам и агрегациям. На практике его ставят рядом с реляционкой как поисковый слой, синхронизируя данные.',
                'difficulty' => 4,
                'topic' => 'database.elasticsearch',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что значит eventual consistency у Elasticsearch на практике?',
                'answer' => 'После записи документа он не сразу виден в поиске: ES сначала пишет в транслог и буфер, и только при следующем refresh (по умолчанию раз в секунду) сегмент становится поисковым. Реплики тоже догоняют первичный шард не мгновенно, поэтому два подряд GET по разным узлам могут вернуть разную картину. На приложение это влияет так: сразу после сохранения нельзя рассчитывать, что документ найдётся в search-запросе — для критичных сценариев приходится явно ждать или использовать GET по id.',
                'difficulty' => 4,
                'topic' => 'database.elasticsearch',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Как PHP-приложение интегрируется с Elasticsearch?',
                'answer' => 'Базовый способ — официальный пакет elasticsearch/elasticsearch: клиент строит REST-запросы к кластеру, отдаёт массивы для index/search/bulk и возвращает ответы Elasticsearch. В Laravel поверх этого используют Scout с драйвером для Elasticsearch: он подписывается на события Eloquent и сам синхронизирует модели в индекс, а модель получает методы вроде search(). На больших объёмах данные обычно индексируют пачками через bulk API из очередей, чтобы не нагружать кластер по одному документу.',
                'difficulty' => 4,
                'topic' => 'database.elasticsearch',
            ],
        ];
    }
}
