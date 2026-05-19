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
                'question' => 'Что такое HMAC и как его правильно использовать?',
                'answer' => 'Hash-based Message Authentication Code (RFC 2104) — способ доказать целостность и аутентичность сообщения через общий секретный ключ. Конструкция HMAC = H(K xor opad || H(K xor ipad || message)) защищает от length-extension атак, которым уязвим наивный sha256(secret . message). Применяется в JWT с HS256, webhook-сигнатурах (Stripe-Signature, X-Hub-Signature-256 у GitHub), AWS SigV4. Правила: 1) секрет 32+ байт случайных (random_bytes), не словарный пароль. 2) Сверяй через hash_equals — обычное == даёт timing-leak. 3) Не путай HMAC с подписью — для HMAC нужно ДЕЛИТЬ секрет с проверяющим, для асимметричной подписи (RS256) — нет. 4) Добавляй timestamp и nonce в подписываемый payload, чтобы отбить replay-атаки.',
                'code_example' => "<?php
// На webhook GitHub: header X-Hub-Signature-256: sha256=<hex>
\$secret = config('services.github.webhook_secret');
\$payload = file_get_contents('php://input');
\$header = \$_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

\$expected = 'sha256='.hash_hmac('sha256', \$payload, \$secret);

// ОБЯЗАТЕЛЬНО constant-time
if (!hash_equals(\$expected, \$header)) {
    http_response_code(401);
    exit('invalid signature');
}

// Защита от replay: проверь timestamp из payload < 5 минут назад
// и сохрани nonce/delivery_id, чтобы не принять тот же запрос дважды",
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'security.crypto',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое цифровая подпись и чем она отличается от HMAC?',
                'answer' => 'Асимметричная подпись доказывает, что данные созданы владельцем приватного ключа, и при этом проверяющему НЕ нужно знать секрет — достаточно публичного ключа. Подписант хеширует данные (SHA-256/384) и применяет к хешу приватный ключ (RSA-PSS, ECDSA, EdDSA). Любой проверяет публичным ключом и сравнивает с хешем сообщения. Отличие от HMAC: HMAC симметричный (общий секрет, все, кто проверяет, могут и подделать), подпись — асимметричная (приватный ключ не покидает подписанта, проверять может кто угодно). Применяется в TLS-сертификатах, JWT alg=RS256/ES256, git commit signing (GPG), подписи релизов (cosign, minisign), JWS. Современная рекомендация — ES256 (ECDSA-P256) или EdDSA: компактнее и быстрее RSA при той же стойкости.',
                'code_example' => "<?php
// Генерация ключевой пары один раз:
// openssl genpkey -algorithm RSA -out private.pem -pkeyopt rsa_keygen_bits:2048
// openssl pkey -in private.pem -pubout -out public.pem

\$data = '{\"order_id\":42,\"amount\":100}';

// Подписант (есть приватный ключ)
\$priv = openssl_pkey_get_private(file_get_contents('private.pem'));
openssl_sign(\$data, \$signature, \$priv, OPENSSL_ALGO_SHA256);
\$signatureB64 = base64_encode(\$signature);

// Проверяющий (есть только публичный)
\$pub = openssl_pkey_get_public(file_get_contents('public.pem'));
\$ok = openssl_verify(\$data, base64_decode(\$signatureB64), \$pub, OPENSSL_ALGO_SHA256);
// \$ok === 1 — валидна, 0 — невалидна, -1 — ошибка",
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'security.crypto',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое timing attack и зачем нужен hash_equals?',
                'answer' => 'Атака на сравнение секретов через измерение времени ответа. Обычное == и strcmp выходят из сравнения на первом несовпавшем байте — чем больше совпавший префикс, тем дольше работает функция. Замеряя время сотен тысяч запросов и усредняя, атакующий побайтово подбирает токен, не зная его. На локалке разница в наносекунды, но в реальной сети при должном усреднении она выделяется из шума. hash_equals($known, $user) сравнивает строки за КОНСТАНТНОЕ время — проходит все байты, накапливает побитовый xor, возвращает результат. Используй для CSRF-токенов, HMAC-подписей webhook, API-ключей, capability-токенов. Для паролей не нужно — password_verify уже делает constant-time внутри. Важно: первым аргументом передавай ИЗВЕСТНУЮ строку (так гарантирована та же длина при сравнении).',
                'code_example' => "<?php
// ПЛОХО — timing leak, == выходит на первом несовпавшем байте
if (\$_POST['token'] === \$_SESSION['csrf']) { /* ... */ }

// Также плохо для бинарных подписей — strcmp/strncmp тоже не constant-time
if (strcmp(\$signature, \$expected) === 0) { /* ... */ }

// ХОРОШО — constant-time, первым параметром «правильное» значение
if (hash_equals(\$_SESSION['csrf'], \$_POST['token'] ?? '')) { /* ok */ }

// Для бинарных HMAC — тоже hash_equals (он работает с любыми строками)
\$expected = hash_hmac('sha256', \$payload, \$secret);
if (hash_equals(\$expected, \$received)) { /* ok */ }",
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
