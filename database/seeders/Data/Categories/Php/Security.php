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
                'answer' => '**Используйте `password_hash($password, PASSWORD_DEFAULT)`** — единственный правильный путь в современном PHP.

**Что эта функция делает:**
- **сама генерирует соль** (можно не заботиться)
- использует **текущий рекомендуемый алгоритм** (сегодня **`bcrypt`**)
- PHP оставляет за собой право **поменять дефолт** в будущих версиях → колонку под хеш делайте **`VARCHAR(255)`**

**Алгоритмы:**
- **`PASSWORD_DEFAULT`** — рекомендованный (сейчас `bcrypt`)
- **`PASSWORD_BCRYPT`** — явный bcrypt, с опцией `cost` (12+ в проде)
- **`PASSWORD_ARGON2ID`** — современный, с тонкой настройкой `memory_cost`, `time_cost`, `threads`

**Никогда не используйте для паролей** `md5` / `sha1` / `sha256` — они **быстрые** и заточены под GPU-брутфорс (миллиарды хешей/сек).

**Проверка:** **`password_verify($password, $hash)`** — сравнение **time-safe** (как `hash_equals`), защита от timing-атак.

**Бонус:** **`password_needs_rehash($hash, PASSWORD_DEFAULT)`** — вызывается **на успешном логине**: если параметры устарели (новый дефолт или поднят `cost`), пересохраните хеш с новыми настройками **прозрачно для пользователя**.',
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
                'answer' => '**XSS (Cross-Site Scripting)** — внедрение **JavaScript** в страницу через пользовательский ввод. Браузер выполняет код от имени жертвы → крадутся куки, токены, делаются действия.

**Главное правило: экранировать на ВЫВОДЕ, не на входе.**

**Основные приёмы:**
- **HTML-контекст:** **`htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8")`**
- **Blade-шаблоны Laravel:** **`{{ $var }}`** экранирует автоматически, **`{!! $var !!}`** — НЕ экранирует (только для уже доверенного HTML)
- **JS-контекст:** `json_encode` с флагами **`JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_APOS`**
- **атрибуты HTML:** оборачивайте в **кавычки** и экранируйте через `htmlspecialchars` с `ENT_QUOTES`
- **URL-контекст:** **`urlencode`** для параметров

**Дополнительная защита:**
- **`Content-Security-Policy`** — даже если XSS-payload просочился, браузер откажется выполнять inline-скрипты
- **`HttpOnly`** на кукиях — украденная XSS-ом кука недоступна из JS
- санитизация HTML от пользователя (например, **HTMLPurifier**) — только если **вы реально** хотите дать пользователю писать HTML',
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
                'answer' => '**CSRF (Cross-Site Request Forgery)** — атака, при которой жертва, **уже авторизованная** на сайте A, заходит на сайт B, и B заставляет её браузер сделать **запрос на A с её куками**. Браузер автоматически приложит куки → действие выполнится **от имени жертвы**.

**Классический пример:** скрытая форма на B, которая шлёт `POST https://bank.example/transfer` с параметрами вывода денег.

**Защита — двойной заслон:**

**1. CSRF-токен (синхронизирующий токен):**
- сервер генерирует случайный **токен** при отрисовке формы и кладёт в **сессию** и **скрытое поле формы**
- при POST токен из формы сравнивается с тем, что в сессии **через `hash_equals`** (time-safe, защита от timing-атак)
- атакующий сайт **не может прочитать** токен из A (Same-Origin Policy) и не подставит его

**2. `SameSite` cookie:**
- **`SameSite=Strict`** — кука **не уходит** при cross-site запросах вообще
- **`SameSite=Lax`** (default в современных браузерах) — компромисс
- **`SameSite=None`** только с `Secure` — для целенаправленных межсайтовых сценариев

**В Laravel** защита **из коробки** — middleware `VerifyCsrfToken` + директива **`@csrf`** в формах.',
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
                'answer' => '**`rand()` и `mt_rand()` — НЕ криптостойкие.** Они быстрые, но предсказуемые (Mersenne Twister можно «угадать» после нескольких сэмплов).

**Для безопасности (токены, пароли, CSRF, ID сессий) используйте функции PHP 7+:**
- **`random_int($min, $max)`** — целое в диапазоне, криптостойкое
- **`random_bytes($n)`** — `n` байт криптослучайных данных
- результат `random_bytes` обычно превращают в hex через **`bin2hex()`** (16 байт → 32 hex-символа)

**PHP 8.2+ — объектный API:**
- **`Random\\Randomizer`** — основной класс
- **`Random\\Engine\\*`** — взаимозаменяемые движки:
  - **`Random\\Engine\\Secure`** (default) — криптостойкий
  - `Mt19937`, `PcgOneseq128XslRr64`, `Xoshiro256StarStar` — для **воспроизводимых** последовательностей (тесты, симуляции)
- удобные методы: **`getBytes()`**, **`getInt()`**, **`shuffleArray()`**, **`shuffleBytes()`**, **`pickArrayKeys()`**

**Источник случайности:**
- Linux — `/dev/urandom`
- Windows — `CryptGenRandom`/`BCryptGenRandom`
- внутри — `arc4random` или syscall `getrandom`',
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
                'answer' => '**Prepared statements отделяют шаблон SQL от данных:**
- драйвер парсит запрос **один раз** с плейсхолдерами (`?` или `:name`)
- значения отправляются **отдельно от шаблона**, **в виде параметров**
- сервер БД **никогда не интерпретирует** их как SQL → инъекция **невозможна**

**Два режима у PDO:**

| | **Native prepares** | **Emulated prepares** |
| --- | --- | --- |
| Где парсится | **на сервере БД** | на стороне PDO |
| Защита от инъекций | ✅ | ✅ (PDO правильно экранирует) |
| Типобезопасность | строже | теряется |
| Кеш плана запроса | работает | нет |
| По умолчанию | в новых драйверах | в `mysql` (исторически) |

Управляется через **`PDO::ATTR_EMULATE_PREPARES`**.

**Подводный камень с PgBouncer:**
- в **transaction pooling** режиме native prepares **ломаются** — server-side `PREPARE` привязан к физическому соединению, которое PgBouncer отдаёт **другому клиенту** между `PREPARE` и `EXECUTE`
- решения:
  - **`PDO::ATTR_EMULATE_PREPARES = true`**
  - **PgBouncer 1.21+/1.22+** с `max_prepared_statements > 0`

**В Laravel и Doctrine** prepares используются автоматически — ручная сборка SQL почти не нужна.',
                'difficulty' => 3,
                'topic' => 'php.security',
            ],
            [
                'category' => 'PHP',
                'question' => 'Зачем в коде логина вызывать password_needs_rehash() после успешной проверки?',
                'answer' => '**Алгоритмы и параметры хеширования устаревают:**
- **`cost`** у `bcrypt` периодически поднимают (12 → 13 → 14) — железо становится быстрее
- проект может **перейти** с `PASSWORD_BCRYPT` на `PASSWORD_ARGON2ID`
- PHP может **поменять `PASSWORD_DEFAULT`** в новой минорной версии

**Что делает `password_needs_rehash($hash, PASSWORD_DEFAULT)`:**
- сравнивает **параметры существующего хеша** с текущим конфигом
- возвращает **`true`**, если хеш нужно перевычислить

**Типичный приём — лениво мигрировать БД на новые параметры:**
1. пользователь вводит пароль на логине
2. **`password_verify()`** проверяет совпадение
3. если успех → **`password_needs_rehash()`** проверяет актуальность параметров
4. если `true` → `password_hash(...)` заново и **`UPDATE users SET password = ?`**

**Что это даёт:**
- база **постепенно мигрирует** на свежие настройки **без принудительного сброса паролей**
- пользователь не замечает миграции
- старые слабые хеши заменяются на новые сильные **по факту входа**',
                'code_example' => '<?php
function login(string $email, string $password): ?User
{
    $user = $userRepo->findByEmail($email);
    if (! $user || ! password_verify($password, $user->password_hash)) {
        return null;
    }

    // Параметры устарели — пересохраним хеш прозрачно
    if (password_needs_rehash($user->password_hash, PASSWORD_DEFAULT)) {
        $userRepo->updatePasswordHash(
            $user->id,
            password_hash($password, PASSWORD_DEFAULT),
        );
    }

    return $user;
}',
                'code_language' => 'php',
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
