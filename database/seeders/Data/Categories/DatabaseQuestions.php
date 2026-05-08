<?php

namespace Database\Seeders\Data\Categories;

use Database\Seeders\Data\Categories\Database\Assemble;
use Database\Seeders\Data\Categories\Database\BasicConcepts;
use Database\Seeders\Data\Categories\Database\BasicQa;
use Database\Seeders\Data\Categories\Database\Cloze;
use Database\Seeders\Data\Categories\Database\Distributed;
use Database\Seeders\Data\Categories\Database\Elasticsearch;
use Database\Seeders\Data\Categories\Database\Indexes;
use Database\Seeders\Data\Categories\Database\InfrastructureBackup;
use Database\Seeders\Data\Categories\Database\Mysql;
use Database\Seeders\Data\Categories\Database\Normalization;
use Database\Seeders\Data\Categories\Database\Nosql;
use Database\Seeders\Data\Categories\Database\Optimization;
use Database\Seeders\Data\Categories\Database\Partitioning;
use Database\Seeders\Data\Categories\Database\Postgresql;
use Database\Seeders\Data\Categories\Database\SqlBasics;
use Database\Seeders\Data\Categories\Database\TransactionsAcid;
use Database\Seeders\Data\Categories\Database\TypeIn;

class DatabaseQuestions
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, cloze_text?: ?string, short_answer?: ?string, assemble_chunks?: ?array<int, string>, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return array_merge(
            BasicConcepts::all(),
            SqlBasics::all(),
            Normalization::all(),
            TransactionsAcid::all(),
            Indexes::all(),
            Optimization::all(),
            Postgresql::all(),
            Mysql::all(),
            Nosql::all(),
            Elasticsearch::all(),
            Distributed::all(),
            InfrastructureBackup::all(),
            Partitioning::all(),
            BasicQa::all(),
            Cloze::all(),
            TypeIn::all(),
            Assemble::all(),
        );
    }
}
