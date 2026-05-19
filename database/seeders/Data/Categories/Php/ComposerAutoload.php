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
                'answer' => 'Менеджер зависимостей для PHP — аналог npm в JS, pip в Python, cargo в Rust. Скачивает сторонние библиотеки из репозитория packagist.org, кладёт их в папку vendor/ и генерирует автозагрузчик. Список зависимостей и их версии описаны в composer.json, фактически установленные версии зафиксированы в composer.lock. Базовые команды: composer install (поставить из lock), composer update (обновить), composer require vendor/package (добавить).',
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
                'answer' => 'Все установленные сторонние пакеты + автоген-файл autoload.php. Папка генерируется командой composer install и НЕ коммитится в git (есть в .gitignore). На сервере и в CI её создают заново через composer install --no-dev.',
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
                'answer' => 'В разделе require перечислены пакеты, нужные приложению в РАНТАЙМЕ (Laravel, Guzzle, sentry, etc.) — без них прод не запустится. В require-dev — пакеты только для разработки и тестов (PHPUnit, Pint, PHPStan, Larastan). На прод-сервере или в Docker-образе обычно ставят composer install --no-dev — dev-пакеты не попадают, образ легче и поверхность атаки меньше. Добавить пакет в dev: composer require --dev <vendor/package>.',
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
                'answer' => 'Каталог пакетов — packagist.org. Установка: composer require vendor/package. Для dev: composer require --dev phpunit/phpunit. Удаление: composer remove vendor/package. После — обновятся composer.json и composer.lock.',
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
