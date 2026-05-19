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
                'answer' => 'Инвертированный индекс — структура, в которой каждому термину (слову после анализа) сопоставлен posting list — список документов, где этот термин встречается, иногда с позициями и частотами для скоринга. Прямой индекс бы выглядел как "док -> текст", а инвертированный наоборот "термин -> [док1, док5, док20]". При поиске Elasticsearch не сканирует тексты документов: берёт термин запроса (или несколько после анализа), достаёт готовые posting lists и пересекает/объединяет их — обе операции работают почти за линейное время от размера списка совпадений, а не от размера корпуса. Поэтому полнотекстовый поиск по миллионам документов отрабатывает за миллисекунды. Это та же идея, что в индексах книг или GIN-индексах PostgreSQL для tsvector/JSONB.',
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
                'answer' => 'Анализатор — конвейер обработки текста, применяется и при индексации поля text, и при поиске по нему. Цель — привести термины запроса и документа к одной форме, чтобы "Apples" находил "apple". Состоит из трёх ступеней. (1) character filters обрабатывают сырой текст ДО токенизации: убирают HTML-теги, заменяют символы (& → and). (2) tokenizer режет текст на токены — обычно по границам слов (standard) или по N-граммам (n-gram, для поиска по подстрокам). (3) token filters модифицируют каждый токен: lowercase, удаление стоп-слов (the, и), стемминг (cars → car), синонимы, обрезка длины. Анализатор на индексации и анализатор на поиске обычно совпадают, но могут быть разными (например, при индексации применять синонимы, при поиске — нет).',
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
                'answer' => 'Standard Analyzer — анализатор по умолчанию для типа text. Делает три вещи: режет текст по границам слов согласно Unicode (UAX #29), удаляет пунктуацию, приводит все токены к lowercase. Стоп-слова и стемминг по умолчанию ВЫКЛЮЧЕНЫ — это уже не базовое поведение. Стандартного хватает для общих англоязычных задач и быстрых прототипов. Пользовательский анализатор нужен, когда стандартный ломает домен: для емейлов и артикулов (\'user@gmail.com\' разрезается в три токена user/gmail/com — лучше keyword), для русского/немецкого (нужен стемминг и удаление стоп-слов), для поиска по подстроке без лидирующего префикса (n-gram tokenizer вместо standard), для случаев, где нужно сохранять символы (#hashtag, $price).',
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
                'answer' => 'Elasticsearch — распределённый RESTful поисковый и аналитический движок поверх Apache Lucene. Главное: 1) Near real-time индексация — документ становится доступным для поиска через ~1 секунду после записи (refresh interval). 2) Полнотекстовый поиск со стеммингом, нечётким поиском (fuzzy для опечаток), подсветкой совпадений (highlight), фасетами и автокомплитом. 3) Документная модель — JSON-документы через REST API, никакого SQL и схемы заранее. 4) Аналитика — агрегации (terms, histogram, date_histogram, percentiles) хорошо параллелятся по шардам и считаются за миллисекунды. 5) Горизонтальное масштабирование через шарды и реплики, отказоустойчивость из коробки. 6) Богатый query DSL: bool, range, geo, span, more_like_this. Поэтому ES ставят рядом с реляционной БД как поисковый и аналитический слой.',
                'difficulty' => 3,
                'topic' => 'database.elasticsearch',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Почему Elasticsearch называют документо-ориентированным и что значит "бессхемный"?',
                'answer' => 'Единица хранения в ES — JSON-документ с вложенными объектами и массивами, а не строка плоской таблицы. Это близко к тому, как объекты живут в коде приложения: вместо JOIN-ов "пользователь + адреса + теги" можно положить всё в один документ и за один запрос вернуть готовую структуру. "Бессхемный" (schemaless) означает, что можно индексировать документы без заранее объявленного mapping — ES при первом INSERT сам определит типы полей (dynamic mapping): "active": true → boolean, "age": 30 → long, "name": "Ivan" → text+keyword. На практике в проде на это не полагаются: мапинг задают явно, потому что dynamic mapping часто угадывает не то (дата "2026-05-20" определится как text вместо date, число "123" пришедшее строкой — как text вместо long), и поменять тип уже проиндексированного поля без переиндексации нельзя.',
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
                'answer' => 'Шардирование делит индекс на N primary shards — это N независимых Lucene-индексов, которые распределяются по узлам кластера. Документ попадает в конкретный шард по hash(routing_key) % N (по умолчанию routing_key = _id). Это даёт две выгоды: можно хранить объём, который не влезает на один узел, и параллелить поиск — каждый шард обрабатывает свою часть запроса. Репликация создаёт по K копий каждого primary shard (replica shards) на ДРУГИХ узлах. Реплики выполняют две роли: при падении узла реплика повышается до primary (отказоустойчивость), и реплики обслуживают часть search-запросов, повышая read-throughput. Число primary shards ФИКСИРУЕТСЯ при создании индекса (изменить можно только через reindex), а число реплик меняется на лету. Типичный баланс: 3 узла, 3 primary, 1 replica = 6 шардов всего, каждый узел держит 2.',
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
                'answer' => 'Mapping — описание схемы индекса: какие поля есть у документа, их типы (text, keyword, date, long, double, boolean, geo_point, nested, object) и какие анализаторы применяются к text-полям. Ключевая разница: text — анализируется (токенизация, lowercase, стемминг), пригоден для match-поиска, но НЕ для точного term/сортировки/агрегаций; keyword — хранится целиком как одна строка, годится для фильтров, сортировки и агрегаций. Часто комбинируют через multi-field: title.text для поиска + title.keyword для агрегаций. Формально ES бессхемный (dynamic mapping создаст поля сам), но в проде mapping всегда задают явно: иначе дата может стать text, число пришедшее строкой — text, и поиск/агрегации сломаются. Тип поля после первой индексации НЕ меняется — нужен reindex в новый индекс с правильным mapping.',
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
