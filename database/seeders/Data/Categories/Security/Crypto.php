<?php

namespace Database\Seeders\Data\Categories\Security;

class Crypto
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Безопасность',
                'question' => 'В чём разница между хешированием и шифрованием простыми словами?',
                'answer' => 'Хеширование — ОДНОНАПРАВЛЕННОЕ: из «password123» получаешь хеш, обратно пароль не достанешь. Применяется для паролей, контрольных сумм, подписей. Шифрование — ДВУНАПРАВЛЕННОЕ: можно зашифровать данные и потом расшифровать обратно ключом. Применяется для секретов в БД, токенов, ПДн. Главное правило: пароли — хешируем, личные данные — шифруем.',
                'difficulty' => 1,
                'topic' => 'security.crypto',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое симметричное и асимметричное шифрование простыми словами?',
                'answer' => 'Симметричное — один ключ и для шифрования, и для расшифровки (AES). Быстро, но обе стороны должны иметь один и тот же ключ. Асимметричное — пара ключей: публичный (шифрует) + приватный (расшифровывает). Можно публичный ключ открыто раздать, приватный никому не показывать. Используется в TLS, SSH, GPG.',
                'difficulty' => 2,
                'topic' => 'security.crypto',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое bcrypt и почему его используют для паролей?',
                'answer' => 'Алгоритм хеширования паролей на основе Blowfish, специально сделанный МЕДЛЕННЫМ — десятки миллисекунд на один хеш. На обычный логин это незаметно, но брутфорс миллиардов паролей становится непрактичным. Параметр cost (4-31) — это log2 числа итераций, то есть cost=12 в 2 раза медленнее, чем cost=11. По мере роста железа cost поднимают (сейчас норма 12-13). bcrypt сам генерирует соль и зашивает в строку «$2y$cost$salt+hash» — отдельная колонка для соли не нужна. ВАЖНО: bcrypt молча обрезает пароли длиннее 72 байт.',
                'code_example' => "<?php
\$hash = password_hash(\$pass, PASSWORD_BCRYPT, ['cost' => 12]);
// \$2y\$12\$N9qo8uLOickgx2ZMRZoMye...
//  алг  cost  соль+хеш

// Проверка
if (password_verify(\$pass, \$hashFromDb)) {
    // Поднялся cost? Пересохранить хеш на лету
    if (password_needs_rehash(\$hashFromDb, PASSWORD_BCRYPT, ['cost' => 13])) {
        \$user->update(['password' => password_hash(\$pass, PASSWORD_BCRYPT, ['cost' => 13])]);
    }
}",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'security.crypto',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое argon2 и чем он лучше bcrypt простыми словами?',
                'answer' => 'Современный алгоритм хеширования паролей, победитель Password Hashing Competition 2015. В отличие от bcrypt, который грузит только CPU, argon2 — memory-hard: ест ещё и заданное количество памяти (десятки-сотни МБ). Это дорого для GPU/ASIC-ферм, где много ядер, но мало памяти на ядро. Три варианта: argon2d (защита от GPU, уязвим к side-channel), argon2i (защита от side-channel), argon2id (гибрид, рекомендация OWASP). Параметры: memory_cost, time_cost, threads. В PHP — password_hash($pass, PASSWORD_ARGON2ID). Правило для джуна: PASSWORD_DEFAULT — ок, для новых проектов выбирай argon2id явно.',
                'code_example' => "<?php
\$hash = password_hash(\$pass, PASSWORD_ARGON2ID, [
    'memory_cost' => 65536, // 64 MB
    'time_cost'   => 4,     // 4 итерации
    'threads'     => 1,
]);
// \$argon2id\$v=19\$m=65536,t=4,p=1\$<salt>\$<hash>

password_verify(\$pass, \$hash); // true/false",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'security.crypto',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'В чём разница между MD5/SHA и bcrypt/argon2 для паролей?',
                'answer' => 'MD5, SHA-1, SHA-256 — БЫСТРЫЕ хеши общего назначения, проектировались для контрольных сумм и проверки целостности, миллиарды операций в секунду на одной GPU. Поэтому брутфорс пароля занимает минуты-часы. bcrypt и argon2 наоборот СПЕЦИАЛЬНО медленные и параметризируемые (cost, memory) — один хеш считается десятки-сотни миллисекунд, ферма перебирает уже миллионы, а не миллиарды. Правило: для ПАРОЛЕЙ только password_hash (bcrypt/argon2id). Для контрольных сумм файлов, HMAC-подписей, ETag — SHA-256 нормально, MD5/SHA-1 уже криптографически сломаны (коллизии).',
                'code_example' => "<?php
// ПЛОХО — быстрый хеш, легко брутфорсится
\$bad = sha1(\$password.'static-salt');

// ХОРОШО — медленный хеш, соль внутри
\$good = password_hash(\$password, PASSWORD_DEFAULT);

// Контрольная сумма файла — SHA-256 ок
\$checksum = hash_file('sha256', '/var/release.zip');",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'security.crypto',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое HMAC простыми словами?',
                'answer' => 'Hash-based Message Authentication Code — способ доказать, что сообщение не подменили, используя общий секретный ключ. HMAC = hash(secret + message). Получатель пересчитывает HMAC своим секретом и сравнивает — если совпало, сообщение настоящее. Используется в JWT-подписях, webhook-сигнатурах (Stripe, GitHub).',
                'code_example' => "<?php
\$secret = 'shared-secret';
\$payload = '{\"order_id\":42}';

// Отправитель шлёт payload + signature
\$signature = hash_hmac('sha256', \$payload, \$secret);

// Получатель пересчитывает и сверяет в constant time
\$expected = hash_hmac('sha256', \$payload, \$secret);
if (hash_equals(\$expected, \$signature)) {
    // сообщение настоящее
}",
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'security.crypto',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое цифровая подпись простыми словами?',
                'answer' => 'Способ доказать, что данные созданы конкретным владельцем приватного ключа. Подписант хеширует данные и шифрует хеш своим приватным ключом — это и есть подпись. Любой с публичным ключом может проверить: расшифровывает подпись, сравнивает с хешем данных. Используется в TLS-сертификатах, JWT (alg=RS256), git commit signing, обновлениях ПО.',
                'difficulty' => 3,
                'topic' => 'security.crypto',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое timing attack и зачем нужен hash_equals простыми словами?',
                'answer' => 'Если сравнивать секреты обычным ==, PHP выходит из сравнения на первом несовпавшем байте — атакующий по времени ответа может побайтово подобрать токен. hash_equals($known, $user) сравнивает строки за константное время (всегда проходит все байты). Используй его для CSRF-токенов, HMAC-подписей, API-ключей. Для паролей не нужно — password_verify уже делает constant-time внутри.',
                'code_example' => "<?php
// ПЛОХО — timing leak
if (\$_POST['token'] === \$_SESSION['csrf']) { /* ... */ }

// ХОРОШО — constant-time
if (hash_equals(\$_SESSION['csrf'], \$_POST['token'])) { /* ... */ }",
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'security.crypto',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Зачем нужен HTTPS простыми словами?',
                'answer' => 'HTTPS = HTTP + TLS. Даёт три вещи: 1) Шифрование — провайдер/Wi-Fi-точка не прочитают трафик (пароли, токены, куки). 2) Целостность — никто не подменит ответ в дороге (например, не вставит рекламный JS). 3) Аутентичность — сертификат подтверждает, что ты говоришь именно с example.com, а не с поддельным сервером. Без HTTPS любая публичная Wi-Fi-сеть = угроза.',
                'difficulty' => 1,
                'topic' => 'security.crypto',
            ],
        ];
    }
}
