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
                'answer' => '**Хеширование** — **одностороннее** преобразование:

- из «password123» получаешь строку фиксированной длины;
- обратно пароль не достать **никаким** ключом;
- применяется там, где значение знать не надо — только сверить: **пароли**, контрольные суммы файлов, цифровые отпечатки.

**Шифрование** — **двустороннее**:

- зашифровал ключом — расшифровал тем же (или парным) ключом, получил исходные данные;
- применяется там, где значение нужно потом **прочитать**: номера карт в БД, токены, личные данные, переписка.

**Главное правило**:

- Пароли пользователей — **хешируем** (`password_hash`, `Hash::make`).
- Личные данные и секреты — **шифруем** (`Crypt::encrypt` в Laravel, `openssl_encrypt` в чистом PHP).

Если код «расшифровывает пароль» — это баг. Пароли не должны быть расшифровываемыми в принципе.',
                'code_example' => "<?php
// Хеширование — обратно никак, можно только сверить
\$hash = password_hash('secret', PASSWORD_DEFAULT);
// \$2y\$12\$N9qo8uLOickgx2ZMRZoMye...
password_verify('secret', \$hash); // true — пароль подошёл
// нет функции password_decrypt — её и быть не может

// Шифрование — можно расшифровать обратно тем же ключом
\$cipher = Crypt::encrypt('4111-1111-1111-1111'); // длинная случайная строка
\$plain  = Crypt::decrypt(\$cipher);              // '4111-1111-1111-1111'

// Правило: пароли — хешируем, личные данные — шифруем
\$user->password    = Hash::make(\$request->password);       // хеш
\$user->card_number = Crypt::encryptString(\$request->card); // шифр",
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'security.crypto',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое симметричное и асимметричное шифрование простыми словами?',
                'answer' => 'Два класса алгоритмов с разной моделью ключей.

**Симметричное** (`AES`, `ChaCha20`):

- **один** ключ и для шифрования, и для расшифровки;
- быстро, подходит для больших объёмов;
- проблема — обе стороны должны иметь **один и тот же** ключ, и его надо как-то безопасно передать.

**Асимметричное** (`RSA`, `ECDSA`, `EdDSA`):

- **пара ключей**: публичный шифрует / приватный расшифровывает;
- публичный ключ можно открыто раздать, приватный — никому;
- медленнее симметричного на 2-3 порядка.

**На практике** их комбинируют: `TLS`/`SSH`/`GPG` используют асимметричное только чтобы согласовать **симметричный сессионный ключ**, а дальше весь трафик шифруют им — быстро и безопасно.',
                'code_example' => "# Симметричное — один ключ
openssl enc -aes-256-cbc -salt -in file.txt -out file.enc -k 'secret-key'
openssl enc -aes-256-cbc -d   -in file.enc -out file.txt -k 'secret-key'

# Асимметричное — пара ключей
openssl genpkey -algorithm RSA -out private.pem -pkeyopt rsa_keygen_bits:2048
openssl pkey -in private.pem -pubout -out public.pem

# Шифруем публичным, расшифровываем приватным
openssl pkeyutl -encrypt -in msg.txt -pubin -inkey public.pem  -out msg.enc
openssl pkeyutl -decrypt -in msg.enc          -inkey private.pem -out msg.txt",
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'security.crypto',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое bcrypt и почему его используют для паролей?',
                'answer' => 'Алгоритм хеширования паролей на основе **Blowfish**, специально сделанный **медленным** — десятки миллисекунд на один хеш.

**Зачем медленный**: на обычный логин это незаметно, но брутфорс миллиардов паролей становится непрактичным.

**Параметр `cost`** (4-31) — это `log2` числа итераций:

- `cost=12` в **2 раза** медленнее, чем `cost=11`;
- по мере роста железа cost поднимают (сейчас норма **12-13**).

**Особенности**:

- сам генерирует соль и зашивает её в строку `$2y$<cost>$<salt+hash>` — отдельная колонка не нужна;
- `password_needs_rehash` позволяет тихо обновить хеш при повышении cost.

**ВАЖНО**: bcrypt **молча обрезает** пароли длиннее **72 байт** — длинный passphrase надо предварительно прогнать через `hash(\'sha256\', ...)` или брать `argon2id`.',
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
                'answer' => 'Современный алгоритм хеширования паролей, **победитель Password Hashing Competition 2015**.

**Чем лучше bcrypt**:

- `bcrypt` грузит только `CPU`;
- `argon2` **memory-hard** — ест ещё и заданное количество **памяти** (десятки-сотни МБ);
- это дорого для `GPU`/`ASIC`-ферм, где много ядер, но мало памяти на ядро.

**Три варианта**:

- `argon2d` — защита от GPU, уязвим к side-channel;
- `argon2i` — защита от side-channel;
- `argon2id` — **гибрид**, рекомендация `OWASP`.

**Параметры**: `memory_cost` (КБ памяти), `time_cost` (итерации), `threads` (параллелизм).

В PHP — `password_hash($pass, PASSWORD_ARGON2ID)`.

**Правило для джуна**: `PASSWORD_DEFAULT` — ок для большинства проектов; для новых проектов с серьёзными требованиями выбирай `PASSWORD_ARGON2ID` явно.',
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
                'answer' => 'Это **разные классы** хешей для **разных задач**.

**`MD5`, `SHA-1`, `SHA-256`** — быстрые хеши **общего назначения**:

- проектировались для контрольных сумм и проверки целостности;
- **миллиарды** операций в секунду на одной GPU;
- брутфорс пароля занимает минуты-часы.

**`bcrypt`, `argon2id`** — **специально медленные** и параметризируемые:

- параметр `cost` / `memory_cost` подстраивается под железо;
- один хеш считается **десятки-сотни миллисекунд**;
- ферма перебирает уже миллионы, а не миллиарды.

**Правило**:

- для **паролей** — только `password_hash` (`bcrypt`/`argon2id`);
- для контрольных сумм файлов, `HMAC`-подписей, `ETag` — `SHA-256` нормально;
- `MD5`/`SHA-1` уже **криптографически сломаны** (коллизии), не использовать ни для подписей, ни для паролей.',
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
                'answer' => '**`HMAC`** (`Hash-based Message Authentication Code`, `RFC 2104`) — способ доказать **целостность** и **аутентичность** сообщения через общий **секретный ключ**.

**Конструкция**: `H(K xor opad || H(K xor ipad || message))`.

Защищает от **length-extension** атак, которым уязвим наивный `sha256(secret . message)`.

**Где применяется**:

- `JWT` с `HS256`;
- webhook-сигнатуры (`Stripe-Signature`, `X-Hub-Signature-256` у `GitHub`);
- `AWS SigV4`;
- собственные подписанные ссылки и `capability tokens`.

**Правила использования**:

1. **Секрет 32+ байт** случайных (`random_bytes(32)`), **не словарный пароль**.
2. **Сверяй** через **`hash_equals`** — обычное `==` даёт timing-leak.
3. Не путай `HMAC` с **цифровой подписью**:
   - `HMAC` — **симметричный**, секрет делится с проверяющим;
   - `RS256`/`ES256` — **асимметричный**, проверяющему достаточно публичного ключа.
4. Добавляй `timestamp` и `nonce` в подписываемый payload — отбивает **replay-атаки**.',
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
                'answer' => '**Цифровая подпись** — **асимметричный** механизм: доказывает, что данные созданы владельцем **приватного** ключа, при этом проверяющему **нужен только публичный** ключ.

**Как работает**:

1. Подписант хеширует данные (`SHA-256`/`SHA-384`).
2. Применяет к хешу **приватный** ключ (`RSA-PSS`, `ECDSA`, `EdDSA`).
3. Получает подпись, прикладывает к данным.
4. Любой проверяющий — публичным ключом — сравнивает с хешем сообщения.

**HMAC vs цифровая подпись**:

| | `HMAC` | Цифровая подпись |
| --- | --- | --- |
| Ключи | **один общий** секрет | пара: **приватный** + **публичный** |
| Кто может **подделать** | все, у кого секрет | только владелец приватного |
| Кто может **проверить** | те же | **любой** с публичным ключом |
| Скорость | очень быстро | медленнее на 2-3 порядка |
| `JWT` алгоритм | `HS256` | `RS256`, `ES256`, `EdDSA` |

**Где применяется**:

- `TLS`-сертификаты;
- `JWT` с `alg=RS256`/`ES256`;
- git commit signing (`GPG`, `SSH`);
- подписи релизов (`cosign`, `minisign`);
- `JWS` (`JSON Web Signature`).

**Рекомендация 2026**: `ES256` (`ECDSA-P256`) или `EdDSA` — **компактнее и быстрее** `RSA` при той же стойкости.',
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
                'answer' => '**Timing attack** — атака на сравнение секретов через **измерение времени** ответа.

**Почему обычные `==` и `strcmp` опасны**:

- выходят из сравнения **на первом несовпавшем байте**;
- чем длиннее совпавший префикс, тем **дольше** работает функция;
- замеряя время **сотен тысяч** запросов и усредняя, атакующий **побайтово подбирает** токен, не зная его.

На локалке разница в наносекунды, но в реальной сети при должном усреднении она **выделяется из шума**.

**`hash_equals($known, $user)`** — constant-time сравнение:

- проходит **все байты** независимо от расхождений;
- накапливает побитовый `xor`;
- время не зависит от того, где именно отличие.

**Где применять**:

- `CSRF`-токены;
- `HMAC`-подписи webhook (`Stripe-Signature`, `X-Hub-Signature-256`);
- `API`-ключи и capability-токены;
- **любые секретные строки**.

**Для паролей** — отдельно `hash_equals` не нужен: `password_verify` внутри уже **constant-time**.

**Важно**: первым аргументом передавай **известное правильное** значение — так гарантирована одинаковая длина при сравнении.',
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
                'answer' => '**HTTPS** = `HTTP` поверх `TLS`. Даёт **три гарантии** сразу:

1. **Шифрование** — провайдер, владелец Wi-Fi в кафе или корпоративный прокси не прочитают трафик. Пароли, токены, куки уходят в виде шифра.
2. **Целостность** — никто в пути не подменит ответ: не вставит рекламный JS, не модифицирует JSON.
3. **Аутентичность** — TLS-сертификат, подписанный доверенным центром (`Let\'s Encrypt`, `DigiCert`), подтверждает, что ты говоришь именно с `example.com`, а не с поддельным сервером.

Без HTTPS любая публичная Wi-Fi-сеть — угроза: атакующий поднимает точку «Free_Airport_WiFi», и весь HTTP-трафик у него как на ладони.

В 2026 HTTPS **обязателен везде**: браузеры пишут «Не защищено» на HTTP-страницах, многие API возвращают ошибку, поисковики понижают в выдаче. Сертификат бесплатно даёт `Let\'s Encrypt`, всё настраивается одной командой `certbot`. Дополнительно — заголовок `HSTS`, чтобы браузер запомнил «к этому домену только по HTTPS».',
                'code_example' => "# Бесплатный сертификат от Let's Encrypt через certbot
sudo certbot --nginx -d example.com -d www.example.com

# В nginx после этого появится редирект http → https и блок listen 443 ssl

# Дополнительно — HSTS, чтобы браузер запомнил «только HTTPS»
add_header Strict-Transport-Security \"max-age=31536000; includeSubDomains\" always;",
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'security.crypto',
            ],
        ];
    }
}
