<?php

namespace Database\Seeders\Data\Categories\Yii2;

class Console
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Yii2',
                'question' => 'Как создать консольную команду в Yii2?',
                'answer' => 'Консольная команда — это **контроллер**, унаследованный от **`yii\\console\\Controller`** и положенный в **`commands/`** (или `console/controllers/` в advanced).

**Шаги:**

1. Создать класс `app\\commands\\HelloController` extends `yii\\console\\Controller`.
2. Добавить публичные методы **`actionXxx()`** — каждый становится подкомандой.
3. (Опционально) прописать класс в **`config/console.php`** → `controllerMap`, чтобы дать ему ID, отличающийся от имени класса.

**Запуск:**

- В **basic**: `./yii hello/index` → вызовет `HelloController::actionIndex()`.
- ID по умолчанию формируется из имени класса (`HelloController` → `hello`).

**Возвращаемое значение** экшена — **exit code**: `ExitCode::OK` (0) или ненулевое для ошибки.',
                'code_example' => '<?php
// commands/HelloController.php
namespace app\\commands;

use yii\\console\\Controller;
use yii\\console\\ExitCode;

class HelloController extends Controller
{
    /**
     * Печатает приветствие.
     */
    public function actionIndex($name = \'World\')
    {
        $this->stdout("Hello, {$name}!\\n");
        return ExitCode::OK;
    }
}

// Запуск:
// ./yii hello              → Hello, World!
// ./yii hello/index Иван   → Hello, Иван!',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.console',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как добавить опции (CLI-флаги) к консольной команде в Yii2?',
                'answer' => 'Через метод **`options($actionID)`** контроллера — он возвращает массив **имён публичных свойств**, которые становятся флагами `--name=value`.

**Шаги:**

1. Объявить публичные свойства на контроллере (`public $force = false; public $limit = 100;`).
2. В `options($actionID)` вернуть массив имён: `[\'force\', \'limit\']`.
3. (Опционально) **`optionAliases()`** — короткие алиасы (`-f` для `--force`).

**Особенности:**

- Yii **автоматически** парсит `--option=value` или `--option value`.
- **Булевые** флаги: `--force` без значения = `true`.
- Опции, общие для всего контроллера, возвращаются для любого `$actionID` (или вернуть из `parent::options(...)`).
- Стандартные опции (`--color`, `--interactive`, `--help`) доступны из коробки.',
                'code_example' => 'namespace app\\commands;

use yii\\console\\Controller;
use yii\\console\\ExitCode;

class ImportController extends Controller
{
    public $force = false;
    public $limit = 100;
    public $file;

    public function options($actionID)
    {
        return array_merge(parent::options($actionID), [
            \'force\', \'limit\', \'file\',
        ]);
    }

    public function optionAliases()
    {
        return array_merge(parent::optionAliases(), [
            \'f\' => \'force\',
            \'l\' => \'limit\',
        ]);
    }

    public function actionRun()
    {
        $this->stdout("force={$this->force}, limit={$this->limit}\\n");
        return ExitCode::OK;
    }
}

// Запуск:
// ./yii import/run --file=data.csv --limit=500 --force
// ./yii import/run -f -l=500',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.console',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как выводить текст и возвращать exit code в консольной команде Yii2?',
                'answer' => '**Три способа вывода:**

- **`$this->stdout($text, $format)`** — обычный вывод (с поддержкой цветов из `yii\\helpers\\Console`).
- **`$this->stderr($text)`** — поток ошибок (правильно для логов ошибок, чтобы можно было разделять).
- **`Console::output($text)`** — статический хелпер, добавляет `\\n` в конце.

**Цвета и форматирование:**

- `Console::FG_RED`, `Console::FG_GREEN`, `Console::BOLD` — константы.
- `$this->ansiFormat($text, ...$styles)` или `Console::ansiFormat($text, [...])`.

**Exit code** — `return` из экшена. Константы из **`yii\\console\\ExitCode`**:

| Константа | Код | Смысл |
| --- | --- | --- |
| **`OK`** | 0 | успех |
| **`USAGE`** | 64 | неверное использование |
| **`DATAERR`** | 65 | плохие данные |
| **`NOINPUT`** | 66 | нет входных данных |
| **`UNAVAILABLE`** | 69 | сервис недоступен |
| **`SOFTWARE`** | 70 | внутренняя ошибка |

Без `return` Yii считает код **0**. Кодами пользуется CI/cron, чтобы понять, упала ли команда.',
                'code_example' => 'use yii\\console\\Controller;
use yii\\console\\ExitCode;
use yii\\helpers\\Console;

class SyncController extends Controller
{
    public function actionRun()
    {
        $this->stdout("Старт синхронизации...\\n", Console::FG_GREEN);

        try {
            $count = $this->doSync();
        } catch (\\Throwable $e) {
            $this->stderr("Ошибка: {$e->getMessage()}\\n", Console::FG_RED);
            return ExitCode::SOFTWARE;
        }

        $this->stdout(
            $this->ansiFormat("Готово: {$count} записей\\n", Console::FG_GREEN, Console::BOLD)
        );
        return ExitCode::OK;
    }

    private function doSync(): int { return 42; }
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.console',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Какие встроенные консольные команды есть в Yii2?',
                'answer' => 'Yii2 поставляется со **стандартным набором команд** (контроллеры в `yii\\console\\controllers\\*`).

| Команда | Класс | Что делает |
| --- | --- | --- |
| **`yii migrate`** | `MigrateController` | управление миграциями БД (`up`, `down`, `create`, `history`) |
| **`yii cache`** | `CacheController` | очистка кэша (`flush`, `flush-all`, `flush-schema`) |
| **`yii message`** | `MessageController` | извлечение строк для перевода (`extract`) |
| **`yii fixture`** | `FixtureController` | загрузка фикстур для тестов (`load`, `unload`) |
| **`yii asset`** | `AssetController` | компиляция и публикация asset bundles |
| **`yii help`** | `HelpController` | список всех команд, справка по команде |
| **`yii serve`** | `ServeController` | встроенный PHP web-сервер для разработки |

**Полезные подкоманды:**

- `./yii help` — все команды.
- `./yii help migrate` — все экшены `migrate`.
- `./yii migrate/create create_users` — создать миграцию.
- `./yii cache/flush-schema` — сбросить schema cache после изменений в БД.
- `./yii serve --port=8888` — запустить dev-сервер.',
                'code_example' => '# Стандартные команды Yii2
./yii help                          # все команды
./yii help migrate                  # справка по migrate
./yii help migrate/up               # справка по конкретному экшену

# Миграции
./yii migrate/create create_user_table
./yii migrate                       # применить все новые
./yii migrate/down 1                # откатить одну
./yii migrate/history               # история

# Кэш
./yii cache                         # список компонентов кэша
./yii cache/flush-all               # очистить все
./yii cache/flush-schema            # очистить schema cache

# Перевод
./yii message/extract @app/config/i18n.php

# Dev-сервер (basic-шаблон)
./yii serve --port=8888

# Свои команды
./yii hello/index --name=Mike',
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'yii2.console',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Чем отличаются конфиги `web.php` и `console.php` в Yii2?',
                'answer' => '**`web.php`** и **`console.php`** — два разных конфига приложения; CLI и HTTP — это **два разных Yii-приложения** с разными классами и компонентами.

| Признак | `config/web.php` | `config/console.php` |
| --- | --- | --- |
| Application | `yii\\web\\Application` | `yii\\console\\Application` |
| Точка входа | `web/index.php` | `yii` |
| Default route | `site/index` | `help` |
| Компоненты | `urlManager`, `request`, `response`, `session`, `user`, `errorHandler` (web) | `request`, `response`, `errorHandler` (console) |
| Контроллеры | `app\\controllers\\*` extends `yii\\web\\Controller` | `app\\commands\\*` extends `yii\\console\\Controller` |
| `controllerNamespace` | `app\\controllers` | **`app\\commands`** |

**Общие компоненты** (БД, кэш, mailer, log) обычно выносят в **`config/common.php`** или `config/db.php` и подключают в обоих конфигах через `require`. В **advanced**-шаблоне для этого есть папка `common/`.

**Важно:** у `console.php` **нет `urlManager`** и сессий, и наоборот — `web.php` обычно не нужны migration-команды.',
                'code_example' => '// config/console.php
$db = require __DIR__ . \'/db.php\';

return [
    \'id\' => \'basic-console\',
    \'basePath\' => dirname(__DIR__),
    \'bootstrap\' => [\'log\'],
    \'controllerNamespace\' => \'app\\commands\',
    \'aliases\' => [
        \'@bower\' => \'@vendor/bower-asset\',
        \'@npm\' => \'@vendor/npm-asset\',
    ],
    \'components\' => [
        \'cache\' => [
            \'class\' => \'yii\\caching\\FileCache\',
        ],
        \'log\' => [
            \'targets\' => [
                [
                    \'class\' => \'yii\\log\\FileTarget\',
                    \'levels\' => [\'error\', \'warning\'],
                    \'logFile\' => \'@runtime/logs/console.log\',
                ],
            ],
        ],
        \'db\' => $db,
    ],
    \'controllerMap\' => [
        \'migrate\' => [
            \'class\' => \'yii\\console\\controllers\\MigrateController\',
            \'migrationNamespaces\' => [\'app\\migrations\'],
        ],
    ],
];',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.console',
            ],
        ];
    }
}
