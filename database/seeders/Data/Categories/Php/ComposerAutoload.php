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
                'answer' => 'Менеджер зависимостей для PHP — аналог npm в JS, pip в Python, cargo в Rust. Скачивает сторонние библиотеки, ставит их в vendor/ и настраивает автозагрузку. Описание зависимостей хранится в composer.json.',
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
                'answer' => 'require — пакеты для работы в проде (Laravel, библиотеки). require-dev — только для разработки и тестов (PHPUnit, Pint, PHPStan). На сервере ставят composer install --no-dev — dev-пакеты не попадают, образ легче.',
                'difficulty' => 1,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое semver и что значат ^ и ~ в Composer?',
                'answer' => 'Semantic Versioning: MAJOR.MINOR.PATCH — поломка совместимости.новые фичи.фиксы. ^1.2.3 разрешает обновления до 2.0 (не включая) — фичи и фиксы. ~1.2.3 разрешает только 1.2.* — только патчи. * — любая. Точное "1.2.3" — без отклонений.',
                'difficulty' => 2,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое автозагрузка (autoload) в PHP простыми словами?',
                'answer' => 'Механизм, который при использовании класса (new App\\User) автоматически подгружает его файл — без ручных require. В Composer-проектах подключается одним require __DIR__.\'/vendor/autoload.php\' в bootstrap, дальше всё работает само.',
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
        ];
    }
}
