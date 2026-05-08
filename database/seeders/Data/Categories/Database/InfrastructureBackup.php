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
                'answer' => 'Установка соединения с БД дорогая (TLS-handshake, аутентификация, форк процесса в PG). Connection pool - набор уже открытых соединений, которые переиспользуются приложением. pgbouncer - внешний пулер для PostgreSQL: между приложением и БД, мультиплексирует тысячи клиентов на десятки соединений. Режимы: session, transaction, statement (чем агрессивнее - тем меньше PG-ресурсов, но больше ограничений). Без пулера большое приложение легко уроет PG.',
                'difficulty' => 4,
                'topic' => 'database.infrastructure_backup',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Что такое WAL и crash recovery в PostgreSQL?',
                'answer' => 'WAL (Write-Ahead Log) - журнал, в который PG записывает все изменения ПЕРЕД тем как применить их к страницам данных. Это даёт Durability (свойство D в ACID): если сервер упал, при старте PG читает WAL и "доигрывает" непримененные изменения - crash recovery. Также WAL основа репликации (streaming) и point-in-time recovery (PITR).',
                'difficulty' => 5,
                'topic' => 'database.infrastructure_backup',
            ],
            [
                'category' => 'Базы данных',
                'question' => 'Какие есть стратегии резервного копирования?',
                'answer' => 'Full backup - полная копия всей БД, простое восстановление, но место и время. Incremental - только изменения с последнего full/incremental, экономно но восстановление длиннее. Differential - изменения с последнего full. Point-in-Time Recovery (PITR) - восстановление на любой момент: full + накат WAL до нужного времени. Логические (pg_dump) vs физические (pg_basebackup) - первое переносимо, второе быстрее. Best practice: регулярные backup-ы, тестировать восстановление, хранить в другом регионе.',
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
