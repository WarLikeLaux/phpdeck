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
                'answer' => 'PSR-4 - стандарт автозагрузки классов. Простыми словами: имя класса с namespace однозначно отображается в путь к файлу. App\\Models\\User -> src/Models/User.php. Composer генерирует автозагрузчик по правилам в composer.json. Заменяет require_once для каждого файла. Старый стандарт PSR-0 разрешал и _ в имени класса (legacy от PEAR-стиля), и \\ в namespace — оба заменялись на /. Сейчас deprecated, имена с _ для маппинга в PSR-4 не интерпретируются.',
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
                'answer' => 'composer.json описывает требуемые пакеты с диапазонами версий (например ^8.2) и метаданные проекта, a composer.lock фиксирует точные разрешённые версии и хеши, которые установит composer install. Коммитить нужно оба: lock гарантирует воспроизводимый билд на CI и у других разработчиков, а composer update переустанавливает пакеты в рамках ограничений из json и обновляет lock.',
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
                'answer' => 'install читает composer.lock и ставит ТОЧНО те версии, что там зафиксированы — воспроизводимый билд (CI, прод). update игнорирует lock, тянет новейшие версии в рамках ограничений composer.json и перезаписывает lock. Локально перед коммитом — install. Обновить пакеты — update.',
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
                'answer' => 'Semantic Versioning — формат версии MAJOR.MINOR.PATCH: MAJOR ломает обратную совместимость, MINOR добавляет фичи без поломок, PATCH — только баг-фиксы. В Composer: ^1.2.3 разрешает обновления до следующего MAJOR (>=1.2.3, <2.0.0) — фичи и патчи. ~1.2.3 — только патчи (>=1.2.3, <1.3.0); ~1.2 — минор + патчи. >=1.2, <2 — явный диапазон. 1.2.* — wildcard. Точная "1.2.3" — без отклонений. Знак @dev / @stable управляет min-stability.',
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
                'answer' => 'Механизм, который при первом упоминании класса (new App\\User, App\\User::CONST, instanceof App\\User) автоматически подгружает его файл — без ручных require. Реализуется через spl_autoload_register: PHP при «не знаю такого класса» вызывает зарегистрированный callback с именем класса, тот находит файл и подключает. В Composer-проектах достаточно одного require __DIR__ . "/vendor/autoload.php" в bootstrap — дальше все классы из vendor и твоего src/ грузятся сами по правилам PSR-4.',
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
                'answer' => 'Кроме psr-4 в composer.json есть раздел autoload.classmap — там перечисляют пути (директории или файлы), и Composer при composer dump-autoload сканирует их, строит карту «полное имя класса → файл». Применяют для legacy-кода, который не следует PSR-4 (например, старые библиотеки с подчёркиваниями в именах), а также для прод-оптимизации: composer dump-autoload -o (или --classmap-authoritative) превращает все psr-4-правила в один classmap и убирает поиск по файловой системе на каждый new — типичное ускорение autoload.',
                'difficulty' => 2,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что делает раздел autoload.files в composer.json?',
                'answer' => 'Если в composer.json есть autoload.files со списком файлов, Composer автоматически подключит их в каждом запросе при подключении vendor/autoload.php. Это нужно для глобальных функций (helpers), которые нельзя автозагрузить по имени класса. Так работают, например, хелперы Laravel или функции из пакета symfony/polyfill. Главное правило — там должны лежать ТОЛЬКО объявления функций/констант, никакой исполняемой логики со side-effects.',
                'difficulty' => 2,
                'topic' => 'php.composer_autoload',
            ],
        ];
    }
}
