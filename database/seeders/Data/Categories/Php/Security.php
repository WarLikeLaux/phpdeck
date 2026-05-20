<?php

namespace Database\Seeders\Data\Categories\Php;

class Security
{
    public static function all(): array
    {
        return [
            [
                'category' => 'PHP',
                'question' => 'Как безопасно хешировать пароли в PHP?',
                'answer' => 'Используй password_hash($password, PASSWORD_DEFAULT) - функция автоматически генерирует соль и использует "текущий рекомендуемый PHP алгоритм" (на сегодня - bcrypt; PHP оставляет за собой право поменять дефолт в будущих версиях, поэтому колонку для хеша делайте VARCHAR(255)). Если в сборке доступен Argon2id и нужен явно он - используйте PASSWORD_ARGON2ID; password_hash($pwd, PASSWORD_ARGON2ID, ["memory_cost" => ..., "time_cost" => ..., "threads" => ...]). Никогда не используй md5/sha1/sha256 для паролей - они быстрые и заточены под GPU-брутфорс. password_verify($password, $hash) - проверка; сравнение хешей внутри password_verify выполняется time-safe (как hash_equals), что защищает от timing-атак. password_needs_rehash($hash, PASSWORD_DEFAULT) проверяет, не пора ли пересчитать хеш (после смены дефолта или повышения cost) - вызывается на успешном логине.',
                'code_example' => '<?php
// При регистрации
$password = "secret123";
$hash = password_hash($password, PASSWORD_DEFAULT);
// сохранить $hash в БД

// При логине
if (password_verify($password, $hashFromDb)) {
    echo "OK";

    if (password_needs_rehash($hashFromDb, PASSWORD_DEFAULT)) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        // обновить в БД
    }
}

// С опциями
$hash = password_hash($password, PASSWORD_BCRYPT, ["cost" => 12]);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.security',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как защититься от XSS в PHP?',
                'answer' => 'XSS (Cross-Site Scripting) - внедрение JS-кода через пользовательский ввод. Защита: всегда экранировать вывод через htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8"). В шаблонах Blade {{ $var }} экранирует автоматически, {!! $var !!} - НЕ экранирует (опасно). Для JSON в JS - json_encode с JSON_HEX_TAG. CSP-заголовки добавляют второй слой защиты.',
                'code_example' => '<?php
$userInput = "<script>alert(1)</script>";

// ПЛОХО
echo "<div>$userInput</div>";

// ХОРОШО
echo "<div>" . htmlspecialchars($userInput, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8") . "</div>";

// В JS-коде
$data = ["name" => $userInput];
echo "<script>const data = " . json_encode($data, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_APOS) . ";</script>";

// CSP-заголовок
header("Content-Security-Policy: default-src \'self\'");',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.security',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое CSRF и как от него защититься?',
                'answer' => 'CSRF (Cross-Site Request Forgery) - атака, когда пользователь, авторизованный на сайте A, заходит на сайт B, и B заставляет его браузер сделать запрос на A с куками. Защита: CSRF-токен, генерируемый сервером и проверяемый при отправке форм. Токен кладут в форму и сессию, при сабмите сравнивают через hash_equals (защита от timing-атак). SameSite=Strict/Lax cookie тоже защищает.',
                'code_example' => '<?php
session_start();

// Генерация при показе формы
if (empty($_SESSION["csrf"])) {
    $_SESSION["csrf"] = bin2hex(random_bytes(32));
}

// В форме
// <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION["csrf"]) ?>">

// Проверка при обработке
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST["csrf"]) || !hash_equals($_SESSION["csrf"], $_POST["csrf"])) {
        http_response_code(403);
        die("CSRF токен неверный");
    }
    // обработка
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.security',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как генерировать криптостойкие случайные числа в PHP?',
                'answer' => 'rand() и mt_rand() - НЕ криптографически безопасны. Для безопасности (токены, пароли, CSRF) используй random_bytes() и random_int() (PHP 7+) - они дают криптостойкую случайность. random_int($min, $max) для целых, random_bytes($n) для бинарных данных. С PHP 8.2 - объектный API: Random\\Randomizer (top-level класс) + Random\\Engine\\* (движки), для криптостойкости — Random\\Engine\\Secure (default).',
                'code_example' => '<?php
// ПЛОХО - предсказуемо
$token = md5(rand());

// ХОРОШО - криптостойко
$token = bin2hex(random_bytes(16));
// 32 hex-символа

$pin = random_int(1000, 9999);

// PHP 8.2+ объектный API
$randomizer = new Random\\Randomizer();
$bytes = $randomizer->getBytes(16);
$num = $randomizer->getInt(1, 100);

// Перемешивание массива
$shuffled = $randomizer->shuffleArray([1, 2, 3, 4]);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.security',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как защититься от инъекций при сборке SQL вручную и почему prepared statements решают проблему?',
                'answer' => 'Prepared statements отделяют шаблон запроса от данных: драйвер парсит SQL один раз и подставляет значения как параметры на стороне сервера. Это исключает интерпретацию пользовательского ввода как кода. Эмулированные prepares (PDO::ATTR_EMULATE_PREPARES=true) на самом деле подставляют значения в шаблон на стороне клиента - безопасно от инъекций (PDO правильно экранирует), но теряются проверки типов и planning-cache. По умолчанию обычно лучше native prepares (быстрее, типобезопаснее). ИСКЛЮЧЕНИЕ: при работе через PgBouncer в transaction pooling режиме native prepared statements ломаются (server-side PREPARE привязан к физическому соединению, которое PgBouncer передаёт другому клиенту между PREPARE и EXECUTE) - там нужен либо EMULATE_PREPARES=true, либо PgBouncer 1.21+/1.22+ с max_prepared_statements > 0. См. отдельную карточку про PgBouncer + Laravel.',
                'difficulty' => 3,
                'topic' => 'php.security',
            ],
            [
                'category' => 'PHP',
                'question' => 'Зачем в коде логина вызывать password_needs_rehash() после успешной проверки?',
                'answer' => 'Алгоритмы и параметры хеширования со временем устаревают: cost у bcrypt поднимают, на проект могут перевести с PASSWORD_BCRYPT на PASSWORD_ARGON2ID. password_needs_rehash() сравнивает параметры существующего хеша с текущим конфигом и говорит, надо ли перехешировать. Типичный приём — после password_verify() проверить needs_rehash и, если да, прозрачно для пользователя пересохранить его пароль с новыми параметрами. Так база постепенно мигрирует на актуальные настройки без принудительного сброса паролей.',
                'difficulty' => 3,
                'topic' => 'php.security',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое SQL-инъекция и как от неё защищаться в PHP?',
                'answer' => '**SQL-инъекция** — атака, при которой пользовательский ввод подставляется **прямо в SQL** и **меняет смысл запроса**.

**Пример:**
- запрос: `"SELECT * FROM users WHERE name = \'$name\'"`
- ввод: `$name = "\' OR \'1\'=\'1"`
- результат: `WHERE name = \'\' OR \'1\'=\'1\'` — вернёт **всех пользователей**

**Защита — prepared statements:**
- значения передаются **отдельно от шаблона** запроса
- драйвер БД (PDO/mysqli) **не интерпретирует их как SQL**, только как данные

**Правила:**
- **никогда** не строить SQL через **конкатенацию** с пользовательским вводом
- использовать **`?`** или **именованные плейсхолдеры** (`:name`)
- в **Laravel Eloquent** и **Query Builder** защита **автоматическая**
- для `LIKE` — экранировать `%` и `_` дополнительно',
                'code_example' => '<?php
// ❌ Опасно
$pdo->query("SELECT * FROM users WHERE name = \'$name\'");

// ✅ Безопасно — prepared statement
$stmt = $pdo->prepare("SELECT * FROM users WHERE name = ?");
$stmt->execute([$name]);
$user = $stmt->fetch();',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.security',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое htmlspecialchars и почему его важно использовать при выводе?',
                'answer' => '**`htmlspecialchars($s, ENT_QUOTES, "UTF-8")`** заменяет в строке HTML-метасимволы на безопасные **сущности**:
- `<` → `&lt;`
- `>` → `&gt;`
- `&` → `&amp;`
- `"` → `&quot;`
- `\'` → `&#039;` (только с `ENT_QUOTES`)

**Зачем нужно — защита от XSS:** пользовательский ввод, выведенный в HTML **без экранирования**, может содержать `<script>...</script>` и выполнить вредоносный JS у других пользователей.

**В Blade:**
- **`{{ $var }}`** — экранирует автоматически (вызывает `e()` → `htmlspecialchars`)
- **`{!! $var !!}`** — НЕ экранирует, **только для уже доверенного HTML**

**Главное правило:** экранировать **на выводе** (в шаблоне), не на входе (в БД).',
                'code_example' => '<?php
$userInput = \'<script>alert(1)</script>\';
echo htmlspecialchars($userInput, ENT_QUOTES, "UTF-8");
// &lt;script&gt;alert(1)&lt;/script&gt; — браузер выведет как текст',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.security',
            ],
            [
                'category' => 'PHP',
                'question' => 'Почему нельзя хранить пароли в открытом виде или через md5/sha1?',
                'answer' => '**В открытом виде** — нельзя категорически: при утечке БД злоумышленники **сразу получат все аккаунты**.

**`md5`/`sha1`/`sha256` для паролей не подходят:**
- они спроектированы быть **БЫСТРЫМИ** (для контрольных сумм)
- современные GPU перебирают **миллиарды хешей в секунду**
- готовые **rainbow tables** делают взлом коротких паролей мгновенным
- одинаковые пароли → одинаковые хеши (без соли)

**Для паролей нужен МЕДЛЕННЫЙ алгоритм с встроенной солью:** **`bcrypt`** или **`Argon2id`**.

**В PHP правильно так:**
- **`password_hash($pwd, PASSWORD_DEFAULT)`** — хеширование (соль PHP генерирует сам и сохраняет **внутри строки хеша**)
- **`password_verify($pwd, $hash)`** — проверка (time-safe, защита от timing-атак)
- **`password_needs_rehash()`** — проверить, не пора ли обновить параметры',
                'difficulty' => 2,
                'topic' => 'php.security',
            ],
        ];
    }
}
