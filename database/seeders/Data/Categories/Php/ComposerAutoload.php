<?php

namespace Database\Seeders\Data\Categories\Php;

class ComposerAutoload
{
    public static function all(): array
    {
        return [
            [
                'category' => 'PHP',
                'question' => 'Что такое Composer и зачем он нужен?',
                'answer' => 'Composer - менеджер зависимостей для PHP (как npm для Node, pip для Python). Управляет пакетами проекта через composer.json. composer.lock фиксирует точные версии для воспроизводимых сборок. composer install ставит из lock, composer update обновляет. Поддерживает автозагрузку (PSR-4, PSR-0, classmap, files). Пакеты публикуются на packagist.org.',
                'code_example' => '{
    "name": "my/project",
    "require": {
        "php": "^8.2",
        "guzzlehttp/guzzle": "^7.0"
    },
    "require-dev": {
        "phpunit/phpunit": "^10.0"
    },
    "autoload": {
        "psr-4": {
            "App\\\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\\\": "tests/"
        }
    }
}',
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'php.composer_autoload',
            ],
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
                'question' => 'Чем отличаются include, require, include_once и require_once?',
                'answer' => 'include - подключает файл, при ошибке - Warning, выполнение продолжается. require - при ошибке Fatal Error и остановка. include_once / require_once - то же самое, но если файл уже подключался - не подключают повторно. На современных проектах эти конструкции почти не используют - всё через Composer autoload (PSR-4). Также include_once/require_once ощутимо медленнее кэшируемого Composer autoloader (он опирается на realpath cache + opcache).',
                'code_example' => '<?php
// При отсутствии файла - warning, идём дальше
include "optional.php";

// При отсутствии - fatal error
require "config.php";

// Не подключит повторно
require_once "helper.php";
require_once "helper.php"; // ничего не делает

// Современный подход
require __DIR__ . "/vendor/autoload.php";',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.composer_autoload',
            ],
            [
                'category' => 'PHP',
                'question' => 'В чём разница между composer.json и composer.lock и какой из них коммитить?',
                'answer' => 'composer.json описывает требуемые пакеты с диапазонами версий (например ^8.2) и метаданные проекта, а composer.lock фиксирует точные разрешённые версии и хеши, которые установит composer install. Коммитить нужно оба: lock гарантирует воспроизводимый билд на CI и у других разработчиков, а composer update переустанавливает пакеты в рамках ограничений из json и обновляет lock.',
                'difficulty' => 2,
                'topic' => 'php.composer_autoload',
            ],
        ];
    }
}
