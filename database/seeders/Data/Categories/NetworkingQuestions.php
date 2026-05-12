<?php

namespace Database\Seeders\Data\Categories;

use Database\Seeders\Data\Categories\Networking\Basics;
use Database\Seeders\Data\Categories\Networking\Dns;
use Database\Seeders\Data\Categories\Networking\Http;
use Database\Seeders\Data\Categories\Networking\Loadbalancers;
use Database\Seeders\Data\Categories\Networking\Realtime;
use Database\Seeders\Data\Categories\Networking\Tls;
use Database\Seeders\Data\Categories\Networking\Transport;

class NetworkingQuestions
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, cloze_text?: ?string, short_answer?: ?string, assemble_chunks?: ?array<int, string>, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return array_merge(
            Basics::all(),
            Transport::all(),
            Dns::all(),
            Http::all(),
            Tls::all(),
            Loadbalancers::all(),
            Realtime::all(),
        );
    }
}
