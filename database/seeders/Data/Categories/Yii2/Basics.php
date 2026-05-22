<?php

namespace Database\Seeders\Data\Categories\Yii2;

class Basics
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Yii2',
                'question' => 'Что такое Yii2?',
                'answer' => '**Yii2** — PHP-фреймворк общего назначения, поколение **2.0** (релиз 2014, активно поддерживается до 2.0.x).

- **Архитектура:** MVC + компоненты + DI-контейнер.
- **Стиль:** конфигурация через **массивы PHP**, а не аннотации/атрибуты.
- **Особенности:** встроенный **ActiveRecord**, gii (генератор кода), мощные **виджеты** для форм/таблиц.
- Поддерживает **PHP 7.x и 8.x** (последние версии 2.0.49+ работают на PHP 8.2/8.3).
- Два стартовых шаблона: **basic** (одноуровневый) и **advanced** (frontend/backend/console).',
                'code_example' => '// composer create-project --prefer-dist yiisoft/yii2-app-basic myapp
// cd myapp
// php yii serve

// Минимальный контроллер
namespace app\\controllers;

use yii\\web\\Controller;

class SiteController extends Controller
{
    public function actionIndex()
    {
        return $this->render(\'index\');
    }
}',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.basics',
            ],
            [
                'category' => 'Yii2',
                'question' => 'В чём разница между basic и advanced шаблонами Yii2?',
                'answer' => '**basic** и **advanced** — два стартовых шаблона проекта, отличаются **структурой и количеством приложений**.

| Признак | basic | advanced |
| --- | --- | --- |
| Приложений | **1** (`web`) | **3** (`frontend`, `backend`, `console`) |
| Конфигов | `config/web.php` | свой `main.php` в каждом приложении |
| Подходит для | **сайтов, API, прототипов** | проектов с **публичной частью и админкой** |
| Окружения | вручную | **init**-скрипт (`dev`/`prod`) |
| Сложность | низкая | выше (общие модели в `common/`) |

- В **advanced** есть папка `common/` с общими моделями, конфигом и helper-классами.
- Для **junior**: начинать стоит с **basic** — структура понятнее.',
                'code_example' => '// basic
composer create-project --prefer-dist yiisoft/yii2-app-basic myapp

// advanced
composer create-project --prefer-dist yiisoft/yii2-app-advanced myapp
cd myapp
php init  // выбрать Development или Production

// advanced-структура:
// frontend/   — публичный сайт
// backend/    — админка
// console/    — CLI-команды и миграции
// common/     — общие модели и конфиг',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.basics',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое entry script в Yii2?',
                'answer' => '**Entry script** — единственный PHP-файл, через который проходят **все** HTTP-запросы (в basic это `web/index.php`).

**Что делает:**

1. Подключает `vendor/autoload.php` (Composer).
2. Подключает `Yii.php` — bootstrap-класс фреймворка.
3. Загружает массив конфигурации (`config/web.php`).
4. Создаёт экземпляр `yii\\web\\Application` с этим конфигом.
5. Вызывает `$application->run()` — запускает обработку запроса.

- Для **CLI** аналог — `yii` (без расширения) в корне проекта.
- Веб-сервер должен направлять **все** URL на `index.php` (правила `.htaccess` или nginx-rewrite).',
                'code_example' => '<?php
// web/index.php

defined(\'YII_DEBUG\') or define(\'YII_DEBUG\', true);
defined(\'YII_ENV\') or define(\'YII_ENV\', \'dev\');

require __DIR__ . \'/../vendor/autoload.php\';
require __DIR__ . \'/../vendor/yiisoft/yii2/Yii.php\';

$config = require __DIR__ . \'/../config/web.php\';

(new yii\\web\\Application($config))->run();',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.basics',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как устроена конфигурация Yii2?',
                'answer' => 'Конфигурация Yii2 — это **обычный PHP-массив**, возвращаемый из файла.

**Основные файлы (basic):**

- `config/web.php` — конфиг web-приложения (компоненты, modules, components, params).
- `config/console.php` — конфиг консольного приложения (миграции, cron).
- `config/db.php` — параметры подключения к БД (подключается из web/console).
- `config/params.php` — пользовательские параметры приложения (доступны через `Yii::$app->params`).

**Структура массива:**

- `id` — идентификатор приложения.
- `basePath` — корневой каталог.
- `components` — массив компонентов (db, cache, mailer, urlManager...).
- `modules` — подключённые модули.
- `params` — пользовательские параметры.',
                'code_example' => '<?php
// config/web.php
return [
    \'id\' => \'basic\',
    \'basePath\' => dirname(__DIR__),
    \'components\' => [
        \'db\' => require __DIR__ . \'/db.php\',
        \'request\' => [
            \'cookieValidationKey\' => \'secret-key\',
        ],
        \'user\' => [
            \'identityClass\' => \'app\\models\\User\',
        ],
    ],
    \'params\' => require __DIR__ . \'/params.php\',
];',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.basics',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое окружения (environments) в Yii2 advanced и команда init?',
                'answer' => '**Environments** — механизм advanced-шаблона для разделения конфигов **dev** и **prod**.

- В каталоге `environments/` лежат две версии файлов: `dev/` и `prod/`.
- Скрипт **`php init`** копирует нужную версию в рабочие каталоги (`frontend/config/`, `backend/config/`, `common/config/`).
- Удобно держать в Git разные `main-local.php` (с реальными паролями БД) — они в `.gitignore`, а шаблоны — в `environments/`.

**В basic такого нет** — там используют один конфиг и проверяют константу `YII_ENV` (`dev`/`prod`/`test`).',
                'code_example' => '// advanced
php init

// Выбор: 0 — Development, 1 — Production

// environments/index.php
return [
    \'Development\' => [
        \'path\' => \'dev\',
        \'setWritable\' => [\'runtime\', \'web/assets\'],
        \'setExecutable\' => [\'yii\'],
    ],
    \'Production\' => [
        \'path\' => \'prod\',
        \'setWritable\' => [\'runtime\', \'web/assets\'],
    ],
];

// в basic — через константу:
defined(\'YII_ENV\') or define(\'YII_ENV\', \'dev\');
if (YII_ENV_DEV) {
    $config[\'bootstrap\'][] = \'debug\';
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.basics',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое namespace app\\* в Yii2?',
                'answer' => 'В **basic-шаблоне** корневой namespace проекта — **`app\\`** (соответствует корню проекта).

- `app\\controllers\\SiteController` → `controllers/SiteController.php`.
- `app\\models\\User` → `models/User.php`.
- `app\\widgets\\Alert` → `widgets/Alert.php`.

**В advanced** namespace разный для каждого приложения: `frontend\\`, `backend\\`, `console\\`, `common\\`.

**Зарегистрирован в `composer.json`** через **PSR-4** autoloader:

- `"autoload": { "psr-4": { "app\\\\": "" } }` для basic.
- После добавления нового namespace нужно выполнить **`composer dump-autoload`**.',
                'code_example' => '<?php
// controllers/SiteController.php
namespace app\\controllers;

use yii\\web\\Controller;
use app\\models\\User;

class SiteController extends Controller
{
    public function actionIndex()
    {
        $user = User::findOne(1);
        return $this->render(\'index\', [\'user\' => $user]);
    }
}

// composer.json (basic)
{
    "autoload": {
        "psr-4": {
            "app\\\\": ""
        }
    }
}',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.basics',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое Yii::$app?',
                'answer' => '**Yii::$app** — глобальный экземпляр класса `yii\\web\\Application` (или `yii\\console\\Application`).

- Через него получают доступ ко всем зарегистрированным компонентам: `Yii::$app->db`, `Yii::$app->user`, `Yii::$app->request`.
- Создаётся **один раз** в entry script (`web/index.php`) на основе массива конфигурации.
- Работает как **service locator**: компоненты ленивые — создаются при первом обращении.
- Полная аналогия с `app()` или `App::` в Laravel — но **статическое свойство**, а не функция-хелпер.',
                'code_example' => '$db = Yii::$app->db;              // компонент БД
$user = Yii::$app->user->identity; // авторизованный пользователь
$cache = Yii::$app->cache;
$request = Yii::$app->request;
$params = Yii::$app->params[\'adminEmail\'];

// В консоли — тот же объект, но другой класс
// Yii::$app instanceof yii\\console\\Application',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.basics',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое Yii::createObject()?',
                'answer' => '**`Yii::createObject()`** — фабричный метод для создания объектов **через DI-контейнер Yii2**.

- Принимает **класс-имя** или **массив-конфиг** с ключом `class`.
- Возвращает объект с **внедрёнными зависимостями** (если контейнер их знает).
- Заполняет публичные свойства/сеттеры значениями из массива.
- Используется внутри фреймворка везде, где создаются компоненты, behaviors, validators.

**Когда применять:**

- Если нужно создать объект **с конфигом-массивом** (стиль Yii2).
- Если объект имеет зависимости — DI их разрешит.
- В обычном коде юнита часто проще `new` — но `createObject` даёт расширяемость.',
                'code_example' => '// По имени класса
$mailer = Yii::createObject(\'app\\components\\Mailer\');

// По массиву-конфигу (как в config)
$cache = Yii::createObject([
    \'class\' => \'yii\\caching\\FileCache\',
    \'cachePath\' => \'@runtime/cache\',
]);

// С параметрами конструктора
$db = Yii::createObject(
    \'yii\\db\\Connection\',
    [\'mysql:host=localhost;dbname=test\']
);

// Под капотом использует Yii::$container
Yii::$container->set(\'app\\interfaces\\Logger\', \'app\\components\\FileLogger\');
$logger = Yii::createObject(\'app\\interfaces\\Logger\'); // вернёт FileLogger',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.basics',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое алиасы путей в Yii2 (@app, @web, @runtime)?',
                'answer' => '**Алиасы** — короткие имена с префиксом **`@`** для путей к каталогам/URL.

**Встроенные:**

- **`@app`** — корень приложения (`basePath`).
- **`@vendor`** — каталог `vendor/` (Composer).
- **`@yii`** — каталог `vendor/yiisoft/yii2/`.
- **`@web`** — URL-корень веб-приложения.
- **`@webroot`** — путь к `web/` (DocumentRoot).
- **`@runtime`** — каталог `runtime/` (логи, кэш-файлы).

**Зачем:**

- Не зависеть от абсолютных путей в коде.
- Конфигурировать пути в одном месте.

**Преобразование:**

- **`Yii::getAlias(\'@app/models\')`** → реальный путь.
- **`Yii::setAlias(\'@uploads\', \'@webroot/uploads\')`** — задать свой.',
                'code_example' => '$path = Yii::getAlias(\'@app/models\');
// /var/www/myapp/models

$url = Yii::getAlias(\'@web/images/logo.png\');
// /images/logo.png

// Свои алиасы (часто в config bootstrap)
Yii::setAlias(\'@uploads\', \'@webroot/uploads\');
Yii::setAlias(\'@assetsUrl\', \'@web/assets\');

// Использование внутри Yii2
$cache = [
    \'class\' => \'yii\\caching\\FileCache\',
    \'cachePath\' => \'@runtime/cache\', // алиас работает прямо в строке
];',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.basics',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Какая типичная структура каталогов basic-проекта Yii2?',
                'answer' => 'Структура **basic**-шаблона:

- **`assets/`** — AssetBundle-классы (фронтенд-ресурсы).
- **`commands/`** — консольные контроллеры (`yii <команда>`).
- **`config/`** — конфиги (`web.php`, `console.php`, `db.php`, `params.php`).
- **`controllers/`** — веб-контроллеры (`SiteController` и т.п.).
- **`models/`** — модели (ActiveRecord и формы).
- **`runtime/`** — логи, кэш, временные файлы (**не в Git**).
- **`tests/`** — Codeception-тесты.
- **`vendor/`** — Composer-зависимости (**не в Git**).
- **`views/`** — шаблоны (`.php`-файлы).
- **`web/`** — публичный каталог (**DocumentRoot**), здесь `index.php` и `assets/`.
- **`widgets/`** — собственные виджеты.

Корневые файлы: `yii` (CLI), `composer.json`, `requirements.php`.',
                'code_example' => 'myapp/
|-- assets/
|-- commands/
|   `-- HelloController.php
|-- config/
|   |-- web.php
|   |-- console.php
|   |-- db.php
|   `-- params.php
|-- controllers/
|   `-- SiteController.php
|-- models/
|   `-- User.php
|-- runtime/       # gitignore
|-- vendor/        # gitignore
|-- views/
|   `-- site/
|       `-- index.php
|-- web/           # DocumentRoot
|   |-- index.php
|   `-- assets/
|-- yii            # CLI entry
`-- composer.json',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.basics',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как Composer связан с Yii2?',
                'answer' => 'Yii2 устанавливается и обновляется **только через Composer**.

**Главный пакет:**

- **`yiisoft/yii2`** — ядро фреймворка.
- **`yiisoft/yii2-app-basic`** / **`yiisoft/yii2-app-advanced`** — стартовые шаблоны.

**Полезные расширения:**

- `yiisoft/yii2-bootstrap5` — виджеты Bootstrap.
- `yiisoft/yii2-debug` — debug-toolbar.
- `yiisoft/yii2-gii` — генератор кода (CRUD, модели).
- `yiisoft/yii2-redis` — Redis-кэш и сессии.

**Особенности:**

- Расширения автоматически регистрируются через `vendor/yiisoft/extensions.php`.
- После добавления PSR-4 namespace в `composer.json` нужно **`composer dump-autoload`**.',
                'code_example' => '# Создание проекта
composer create-project --prefer-dist yiisoft/yii2-app-basic myapp

# Установка расширения
composer require yiisoft/yii2-redis
composer require --dev yiisoft/yii2-debug yiisoft/yii2-gii

# Обновление
composer update

# Обновить autoload после нового namespace
composer dump-autoload

# composer.json (фрагмент)
{
    "require": {
        "php": ">=7.4",
        "yiisoft/yii2": "~2.0.49",
        "yiisoft/yii2-bootstrap5": "~2.0"
    }
}',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'yii2.basics',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Какие версии PHP поддерживает Yii2?',
                'answer' => '**Yii 2.0** — единственная актуальная линейка (Yii 3.0 был переименован в **Yii Framework 3** и пока не выпущен как стабильный).

**Поддержка PHP:**

- **PHP 5.4+** — для старых версий 2.0.x (исторически).
- **PHP 7.x** — основная целевая платформа.
- **PHP 8.0/8.1/8.2/8.3** — поддерживаются с версии **2.0.45+** (рекомендуется **2.0.49+**).

**Совместимость:**

- Yii2 написан без `declare(strict_types=1)` — типы свойств и параметров **не строгие**.
- На PHP 8.x работает корректно, но deprecations о nullable-параметрах нужно отслеживать (исправлено в 2.0.49+).
- Для PHP 8.x ставь **последнюю минорную версию** Yii2.',
                'code_example' => '// composer.json
{
    "require": {
        "php": ">=7.4",
        "yiisoft/yii2": "~2.0.49"
    }
}

// Проверка через CLI
php -v
php yii migrate

// requirements.php в корне проекта
php requirements.php
// показывает соответствие PHP-версии и расширений',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.basics',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое Application class в Yii2?',
                'answer' => '**Application** — главный класс приложения, наследник `yii\\base\\Application`.

**Два потомка:**

- **`yii\\web\\Application`** — для HTTP-запросов.
- **`yii\\console\\Application`** — для CLI (команда `yii`).

**Роль:**

- **Service locator** — хранит все компоненты (`db`, `user`, `request`, `cache`...).
- **Точка маршрутизации** — направляет запрос в нужный контроллер.
- **Конфигуратор** — применяет массив-конфиг к самому себе и компонентам.

- Доступен как **`Yii::$app`** глобально.
- Создаётся один раз в entry script и живёт до конца запроса.',
                'code_example' => '// web/index.php
$config = require __DIR__ . \'/../config/web.php\';
(new yii\\web\\Application($config))->run();

// Внутри run():
// 1. EVENT_BEFORE_REQUEST
// 2. Разбор URL через urlManager
// 3. Создание контроллера и вызов action
// 4. Отправка response
// 5. EVENT_AFTER_REQUEST

// Из любого места
echo Yii::$app->id;        // \'basic\'
echo Yii::$app->basePath;  // /var/www/myapp
echo Yii::$app->language;  // \'en-US\'

// В консоли — другой класс
// php yii migrate
// Yii::$app instanceof yii\\console\\Application === true',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.basics',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как запустить Yii2-проект локально?',
                'answer' => '**Самый простой способ — встроенный сервер PHP** через консольную команду Yii.

**Команда:**

- **`php yii serve`** — запускает PHP built-in server на `localhost:8080`.
- **`php yii serve --port=8888`** — другой порт.
- **`php yii serve 0.0.0.0:8080`** — слушать все интерфейсы.

**Альтернативы:**

- **Apache + mod_rewrite**: DocumentRoot должен указывать на `web/`, нужен `.htaccess` с `RewriteRule . index.php`.
- **nginx + php-fpm**: `root /var/www/myapp/web` и `try_files $uri $uri/ /index.php?$args`.
- **Docker** через готовые образы (`yiisoftware/yii2-php`).

**Только для разработки** — встроенный сервер однопоточный.',
                'code_example' => '# Запуск встроенного сервера
cd myapp
php yii serve
# Server is running at http://localhost:8080

# Другой порт
php yii serve --port=8888

# nginx-конфиг (фрагмент)
server {
    listen 80;
    server_name myapp.local;
    root /var/www/myapp/web;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    location ~ \\.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        include fastcgi_params;
    }
}',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'yii2.basics',
            ],
        ];
    }
}
