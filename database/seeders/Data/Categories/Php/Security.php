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
        ];
    }
}
