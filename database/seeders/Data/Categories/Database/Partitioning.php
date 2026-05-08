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
                'answer' => 'Партиционирование - разделение одной таблицы на несколько физических частей (партиций) внутри одной БД, прозрачно для приложения. Простыми словами: если шкаф переполнен, ты разделяешь содержимое по полкам - "одежда зимняя", "одежда летняя". Приложение видит один шкаф, а БД ищет только в нужной полке. Бывает range, list, hash. Преимущества: быстрее запросы (partition pruning), быстрее удаление старых данных (DROP PARTITION), отдельные индексы. Это НЕ шардирование - всё на одном сервере.',
                'difficulty' => 4,
                'topic' => 'database.partitioning',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Range vs List vs Hash партиционирование?',
                'answer' => 'Range - по диапазонам значений (часто даты): партиция за каждый месяц. Удобно для time-series данных. List - по списку явных значений (по странам, по статусам). Hash - по хешу ключа, равномерно. Range и List лучше когда данные имеют естественные группы; hash - для равномерного распределения по партициям без логических групп.',
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
                'answer' => 'PARTITION BY RANGE (по диапазону, чаще по дате) - типично для логов и time-series, позволяет быстро дропать старые данные через DROP PARTITION. PARTITION BY LIST - по перечислению (страна, тенант). PARTITION BY HASH - равномерное распределение. Partition pruning - оптимизатор не сканирует партиции, не подходящие под WHERE. Local indexes на каждой партиции; FK *из* партиционированной таблицы появились в PG 11, FK *на* партиционированную таблицу (когда она выступает referenced-стороной) — только с PG 12+.',
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
