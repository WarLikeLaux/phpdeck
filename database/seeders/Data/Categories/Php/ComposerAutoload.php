<?php

namespace Database\Seeders\Data\Categories\Php;

class ComposerAutoload
{
    public static function all(): array
    {
        return [
            [
                'category' => 'PHP',
                'question' => 'Что такое PSR-4 автозагрузка?',
                'answer' => '**PSR-4** — стандарт автозагрузки классов. **Имя класса с namespace однозначно отображается в путь к файлу**, и Composer генерирует автозагрузчик по правилам в `composer.json`. Заменяет ручные `require_once`.

**Правило отображения:**
- `App\Models\User` → `src/Models/User.php` (при правиле `"App\\\\": "src/"`)
- `namespace` после префикса → подпапки.
- Имя класса → имя файла **символ в символ** (case-sensitive на Linux!).

**Ключевые детали:**
- В `composer.json` префикс **обязан** заканчиваться на `\\`, базовая директория — на `/`.
- Один namespace может маппиться **на несколько папок**: `"App\\\\": ["src/", "app/"]`.
- **Один класс на файл**, имя файла **совпадает** с именем класса.

**PSR-4 vs PSR-0** (исторический контекст):
- **PSR-0** (deprecated): разрешал `_` в имени класса как разделитель (PEAR-стиль) — `App_Models_User` → `App/Models/User.php`.
- **PSR-4**: `_` **не имеет специального значения**, маппится дословно.

**Прод-оптимизация:** `composer dump-autoload -o` (`--optimize`) строит **classmap** «класс → файл», убирая поиск по диску.',
                'code_example' => '<?php
// composer.json: "autoload": { "psr-4": { "App\\\\": "src/" } }

// src/Models/User.php
namespace App\\Models;
class User {}

// public/index.php
require __DIR__ . "/../vendor/autoload.php";

use App\\Models\\User;
$user = new User(); // автоматически подгрузится файл

// composer dump-autoload -o    // оптимизированный для прода',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'В чём разница между composer.json и composer.lock и какой из них коммитить?',
                'answer' => '- **`composer.json`** — **«хочу»**: описывает требуемые пакеты с **диапазонами** версий (`"php": "^8.2"`, `"laravel/framework": "^11.0"`) и метаданные проекта.
- **`composer.lock`** — **«поставлено»**: фиксирует **точные версии** и хеши пакетов, которые `composer install` поставит у всех одинаково.

**Коммитить нужно оба** — иначе у тебя одни версии, у CI другие, у коллеги третьи.

**Команды:**
- **`composer install`** — читает `lock`, ставит точно те версии → **воспроизводимый билд** (CI, прод)
- **`composer update`** — игнорирует lock, тянет новейшие версии **в рамках** `composer.json`, перезаписывает `lock`',
                'code_example' => '# composer.json (что хочу)
{
    "require": {
        "guzzlehttp/guzzle": "^7.5"
    }
}

# composer.lock (что реально стоит — упрощённо)
{
    "packages": [
        {
            "name": "guzzlehttp/guzzle",
            "version": "7.8.1",
            "dist": { "url": "...", "shasum": "abc..." }
        }
    ]
}

# CI / прод
composer install --no-dev   # ставит точно 7.8.1 из lock

# Обновить пакет — локально, потом коммит lock
composer update guzzlehttp/guzzle',
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое Composer простыми словами?',
                'answer' => '**Composer** — менеджер зависимостей для PHP. Аналог `npm` в JS, `pip` в Python, `cargo` в Rust.

**Что делает:**
- скачивает сторонние библиотеки из репозитория **packagist.org**
- кладёт их в папку **`vendor/`**
- генерирует автозагрузчик `vendor/autoload.php`

**Два ключевых файла:**
- **`composer.json`** — список зависимостей и диапазоны версий
- **`composer.lock`** — фактически установленные версии (для воспроизводимого билда)

**Базовые команды:**
- `composer install` — поставить из `composer.lock`
- `composer update` — обновить пакеты
- `composer require vendor/package` — добавить новый пакет',
                'code_example' => 'composer require guzzlehttp/guzzle
composer install
composer update
composer require --dev phpunit/phpunit

# Подключение в коде
require __DIR__ . "/vendor/autoload.php";',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что лежит в папке vendor/?',
                'answer' => '**Все установленные сторонние пакеты** плюс автоген-файл `vendor/autoload.php` — единственная точка входа автозагрузчика Composer.

**Ключевые правила:**
- папка генерируется командой `composer install`
- **НЕ коммитится в git** (она есть в `.gitignore` любого нормального шаблона)
- на сервере и в CI создаётся заново через `composer install`
- для прода — **`composer install --no-dev --optimize-autoloader`** (без dev-пакетов и с оптимизированным classmap)
- в коде подключается одной строкой в bootstrap: `require __DIR__ . "/../vendor/autoload.php";`',
                'code_example' => '# Структура проекта
project/
├── composer.json
├── composer.lock
├── src/                ← твой код
├── vendor/             ← всё от Composer (в .gitignore!)
│   ├── autoload.php   ← точка входа
│   ├── composer/
│   └── guzzlehttp/
└── public/index.php

# Bootstrap — одна строка подключает ВСЁ
# public/index.php:
# require __DIR__ . "/../vendor/autoload.php";

# На сервере
composer install --no-dev --optimize-autoloader',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Чем отличаются composer install и composer update (кратко)?',
                'answer' => '- **`composer install`** — читает **`composer.lock`** и ставит **ТОЧНО** те версии, что там зафиксированы. **Воспроизводимый билд** — нужен на **CI** и **проде**.
- **`composer update`** — **игнорирует lock**, тянет новейшие версии **в рамках** ограничений `composer.json`, потом **перезаписывает `composer.lock`**.

**Когда что:**
- ставишь чужой проект, переключил ветку, деплой → **`install`**
- хочешь обновить зависимости → **`update`** (можно один пакет: `composer update vendor/pkg`)

**На проде:** только `composer install --no-dev --optimize-autoloader`. `update` на проде — путь к сюрпризам.',
                'difficulty' => 2,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'В чём разница между require и require-dev в composer.json?',
                'answer' => '- **`require`** — пакеты, нужные приложению **в рантайме**: Laravel, Guzzle, Sentry. Без них прод **не запустится**.
- **`require-dev`** — пакеты только **для разработки и тестов**: PHPUnit, Pint, PHPStan, Larastan.

**На проде** обычно ставят **`composer install --no-dev`** — dev-пакеты не попадают, образ легче и поверхность атаки меньше.

**Добавить пакет в dev:** `composer require --dev <vendor/package>`.',
                'code_example' => '{
    "require": {
        "php": "^8.2",
        "laravel/framework": "^11.0"
    },
    "require-dev": {
        "phpunit/phpunit": "^11.0",
        "laravel/pint": "^1.0"
    }
}',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое semver и что значат ^ и ~ в Composer?',
                'answer' => '**Semantic Versioning** — формат версии `MAJOR.MINOR.PATCH`:
- **MAJOR** — ломающие изменения (`1.x` → `2.0`)
- **MINOR** — новые фичи без поломок (`1.2` → `1.3`)
- **PATCH** — только баг-фиксы (`1.2.3` → `1.2.4`)

**Операторы Composer (от широкого к узкому):**
- **`^1.2.3`** — до следующего MAJOR: `>=1.2.3, <2.0.0`. Самый частый — пускает minor и patch.
- **`~1.2.3`** — до следующего MINOR: `>=1.2.3, <1.3.0`. Только patch-апдейты.
- **`~1.2`** — до следующего MAJOR: `>=1.2, <2.0` (другое поведение!).
- **`1.2.*`** — wildcard: `>=1.2.0, <1.3.0`.
- **`>=1.2,<2.0`** — явный диапазон.
- **`"1.2.3"`** — точная фиксация.

**Best practice:** `^` для большинства зависимостей; `~` если боишься minor-изменений.',
                'code_example' => '{
    "require": {
        "php": "^8.2",                  // 8.2.x — 8.99.x, не 9.0
        "laravel/framework": "^11.0",   // 11.x, не 12
        "guzzlehttp/guzzle": "~7.5.0",  // 7.5.x, не 7.6
        "symfony/console": "6.4.*"      // любой patch 6.4
    }
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое автозагрузка (autoload) в PHP простыми словами?',
                'answer' => '**Автозагрузка** — механизм, который при первом упоминании класса (`new App\\User`, `App\\User::CONST`, `instanceof App\\User`) **автоматически подгружает его файл** — без ручных `require`.

**Как устроено:** функция **`spl_autoload_register($callback)`** регистрирует обработчик. Когда PHP встречает неизвестный класс, он вызывает все зарегистрированные callback с именем класса — задача callback найти файл и подключить его.

**Composer** делает это за тебя:
- описываешь в `composer.json` правило **PSR-4**: `"App\\\\": "src/"`
- `composer install` (или `dump-autoload`) **генерирует** `vendor/autoload.php`
- в bootstrap (например, `public/index.php`) — **одна строка** `require __DIR__ . "/../vendor/autoload.php";`
- дальше все классы из `vendor/` и твоего `src/` грузятся автоматически

**Оптимизация для прода:** `composer dump-autoload -o` строит classmap-карту «класс → файл» и убирает поиск по диску.',
                'code_example' => '<?php
// bootstrap (обычно public/index.php)
require __DIR__ . "/../vendor/autoload.php";

use App\Models\User;       // ← Composer сам найдёт src/Models/User.php
$u = new User();

// Ручной автозагрузчик без Composer — для общего понимания
spl_autoload_register(function (string $class) {
    $path = __DIR__ . "/src/" . str_replace("\\\\", "/", $class) . ".php";
    if (file_exists($path)) require $path;
});',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как искать и устанавливать пакеты в Composer?',
                'answer' => 'Главный каталог пакетов PHP — **packagist.org**, там описание пакета, версии и статистика установок.

**Основные команды:**
- `composer search <keyword>` — поиск из командной строки
- `composer require vendor/package` — установка (сам добавит запись в `composer.json` и обновит `composer.lock`)
- `composer require --dev vendor/package` — установка только для разработки (PHPUnit, Pint)
- `composer remove vendor/package` — удаление
- `composer update vendor/package` — обновить один пакет в рамках диапазона версий
- `composer require laravel/framework:^11.0` — указать версию явно',
                'code_example' => '# Поиск
composer search guzzle

# Установка в основные зависимости (попадёт в require)
composer require guzzlehttp/guzzle

# Установка в dev (попадёт в require-dev)
composer require --dev phpunit/phpunit

# Указать версию явно
composer require laravel/framework:^11.0

# Удаление
composer remove guzzlehttp/guzzle

# Обновить только один пакет
composer update guzzlehttp/guzzle',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое автозагрузчик classmap в Composer и когда его использовать?',
                'answer' => 'Кроме `psr-4` в `composer.json` есть раздел **`autoload.classmap`** — перечень путей (директорий или файлов), которые Composer **сканирует** при `composer dump-autoload` и строит карту **«полное имя класса → файл»**.

**Где применяют:**
- **legacy-код**, не следующий PSR-4 (старые библиотеки, имена с `_` вместо namespace)
- **прод-оптимизация:** `composer dump-autoload -o` (или `--classmap-authoritative`) превращает **все** PSR-4-правила в один classmap. Это убирает поиск файла по диску на каждый `new`.

**Эффект:** ускорение автозагрузки в 2-5 раз на прод-приложениях, особенно с большим `vendor/`.

**Минус:** новые классы появляются только после `composer dump-autoload`, поэтому `-o` не используют на dev.',
                'code_example' => '{
    "autoload": {
        "psr-4": { "App\\\\": "src/" },
        "classmap": [
            "legacy/",
            "database/seeders/"
        ]
    }
}

# На проде
composer install --no-dev --optimize-autoloader
# или после деплоя
composer dump-autoload --classmap-authoritative',
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что делает раздел autoload.files в composer.json?',
                'answer' => 'Раздел **`autoload.files`** содержит список файлов, которые Composer **автоматически подключает** при `require vendor/autoload.php` — на каждом запросе, **до** любой автозагрузки классов.

**Зачем нужно:** глобальные **функции** и **константы** нельзя автозагрузить по имени класса — их нужно явно подключать. Через `files` это происходит один раз и прозрачно.

**Примеры использования:**
- **хелперы Laravel** (`helpers.php` с `dd()`, `dump()`, `value()`)
- **`symfony/polyfill`** — добавляет функции из новых версий PHP в старые
- свои `helpers.php` с глобальными функциями

**Главное правило:** в таких файлах должны быть **ТОЛЬКО** объявления функций и констант — никаких side-effects (не пиши там `echo`, `$_SESSION[...] = ...` и т.п.), иначе они будут выполняться **на каждом запросе**.',
                'code_example' => '{
    "autoload": {
        "psr-4": { "App\\\\": "src/" },
        "files": [
            "src/helpers.php"
        ]
    }
}

# src/helpers.php
function fullName(User $u): string {
    return $u->first . " " . $u->last;
}
const APP_TIMEZONE = "Europe/Moscow";

# Дальше эти функции/константы доступны везде — без use, без require',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.composer_autoload',
            ],
        ];
    }
}
