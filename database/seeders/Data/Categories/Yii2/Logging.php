<?php

namespace Database\Seeders\Data\Categories\Yii2;

class Logging
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Yii2',
                'question' => 'Как писать логи в Yii2?',
                'answer' => 'В Yii2 для логирования используется **класс `Yii`** со статическими методами по **уровням важности**:

- **`Yii::error($message, $category)`** — критическая ошибка.
- **`Yii::warning($message, $category)`** — предупреждение.
- **`Yii::info($message, $category)`** — информационное сообщение.
- **`Yii::debug($message, $category)`** — отладочное (в старом API назывался **`Yii::trace`**, тот **deprecated**).

**Второй аргумент** `$category` — строка-категория для фильтрации (например, `\'app.payment\'`). По умолчанию `\'application\'`.

Сообщения попадают в **`yii\\log\\Logger`**, который накапливает их в памяти и сбрасывает через **targets** (файлы, БД, email).',
                'code_example' => 'use Yii;

Yii::info(\'Пользователь вошёл\', \'app.auth\');
Yii::warning(\'Платёж задерживается > 5 секунд\', \'app.payment\');
Yii::error(\'Не удалось списать средства\', \'app.payment\');

// debug — обычно вместо deprecated trace
Yii::debug(\'SQL: \' . $query->createCommand()->rawSql, \'app.sql\');

// Лог исключения целиком
try {
    $service->charge($order);
} catch (\\Throwable $e) {
    Yii::error($e, \'app.payment\');
}',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.logging',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое log targets в Yii2?',
                'answer' => '**Log target** — куда **выгружаются** накопленные сообщения. Настраивается в **`components.log.targets`** в конфиге.

**Стандартные targets (`yii\\log\\*`):**

- **`FileTarget`** — пишет в файл (`runtime/logs/app.log` по умолчанию). Поддерживает ротацию (`maxFileSize`, `maxLogFiles`).
- **`DbTarget`** — пишет в таблицу `log` (нужна миграция).
- **`EmailTarget`** — шлёт письмо.
- **`SyslogTarget`** — системный syslog.

**Каждый target имеет:**

- **`levels`** — массив уровней, на которые подписан (`error`, `warning`, `info`, `trace`, `profile`).
- **`categories`** / **`except`** — фильтр по категориям (поддерживает `*`-шаблоны).
- **`logVars`** — какие глобальные переменные приложить (`$_GET`, `$_POST` и т. д.). На prod обычно ставят `[]` чтобы не утекали данные.',
                'code_example' => '// config/web.php
return [
    \'bootstrap\' => [\'log\'],
    \'components\' => [
        \'log\' => [
            \'traceLevel\' => YII_DEBUG ? 3 : 0,
            \'targets\' => [
                [
                    \'class\' => \'yii\\log\\FileTarget\',
                    \'levels\' => [\'error\', \'warning\'],
                    \'logFile\' => \'@runtime/logs/app.log\',
                    \'maxFileSize\' => 10240, // KB
                    \'maxLogFiles\' => 5,
                    \'logVars\' => [],
                ],
                [
                    \'class\' => \'yii\\log\\EmailTarget\',
                    \'levels\' => [\'error\'],
                    \'categories\' => [\'app.payment\'],
                    \'message\' => [
                        \'from\' => [\'noreply@example.com\'],
                        \'to\' => [\'ops@example.com\'],
                        \'subject\' => \'Ошибка платежа\',
                    ],
                ],
            ],
        ],
    ],
];',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.logging',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Чем `Yii::trace` отличается от `Yii::debug`?',
                'answer' => '**`Yii::trace($message, $category)`** — старый метод (Yii 2.0.0 — 2.0.13).

**`Yii::debug($message, $category)`** — новый псевдоним для того же действия, появился в **Yii 2.0.14**.

| Признак | `Yii::trace` | `Yii::debug` |
| --- | --- | --- |
| Появился | 2.0.0 | **2.0.14** |
| Статус | **deprecated** | актуальный |
| Уровень логирования | `Logger::LEVEL_TRACE` | `Logger::LEVEL_TRACE` (тот же!) |
| Уровень в `levels` target | `\'trace\'` | `\'trace\'` |

**Под капотом — то же самое.** Переименование сделано, чтобы вернуть привычное название `debug` (термин `trace` путали со stack trace).

**Junior-правило:** в новом коде писать **`Yii::debug`**, в `levels` target по-прежнему указывать **`\'trace\'`**.',
                'code_example' => '// Старый код (deprecated, но работает)
Yii::trace(\'Step 1 finished\', \'app.import\');

// Новый код — то же самое
Yii::debug(\'Step 1 finished\', \'app.import\');

// В конфиге target — уровень всё равно \'trace\'
[
    \'class\' => \'yii\\log\\FileTarget\',
    \'levels\' => [\'trace\', \'info\', \'warning\', \'error\'],
    \'logFile\' => \'@runtime/logs/debug.log\',
],',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.logging',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как работает flushInterval и exportInterval у логгера в Yii2?',
                'answer' => 'Сообщения **не пишутся в target сразу** — `yii\\log\\Logger` накапливает их в памяти, чтобы не делать I/O на каждый вызов `Yii::info`.

**Два параметра:**

- **`flushInterval`** (на **`log`**-компоненте, по умолчанию **1000**) — сколько сообщений накопить, прежде чем передать всем targets.
- **`exportInterval`** (на **каждом target**, по умолчанию **1000**) — сколько накопить в target, прежде чем реально записать в файл/БД/email.

**В конце запроса** оставшиеся сообщения **выгружаются автоматически** через зарегистрированный `register_shutdown_function`.

**Проблемы:**

- На **fatal error** часть логов может потеряться, если `flushInterval` большой.
- На **долгих CLI-командах** (миграции, импорт) логи появятся в файле только **по завершении**, если не уменьшить интервалы.
- Решение для CLI: ставить `flushInterval = 1` и `exportInterval = 1`.',
                'code_example' => '// config/console.php — для CLI-команд сразу писать
\'components\' => [
    \'log\' => [
        \'flushInterval\' => 1, // сразу передать targets
        \'targets\' => [
            [
                \'class\' => \'yii\\log\\FileTarget\',
                \'levels\' => [\'error\', \'warning\', \'info\'],
                \'exportInterval\' => 1, // сразу писать в файл
                \'logFile\' => \'@runtime/logs/console.log\',
            ],
        ],
    ],
],

// Принудительная выгрузка (например, перед `exit`)
Yii::$app->log->logger->flush(true);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.logging',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как смотреть логи в Yii2 Debug toolbar?',
                'answer' => '**Debug toolbar** — модуль **`yii\\debug\\Module`** (пакет `yiisoft/yii2-debug`), который ставится **только в dev-окружении** и показывает панель внизу страницы.

**Что показывает по логам:**

- Все сообщения текущего запроса (`error`, `warning`, `info`, `trace`).
- **SQL-запросы** с временем выполнения и стек-трейсом (вкладка **Database**).
- **Profiling** — `Yii::beginProfile()` / `Yii::endProfile()` блоки.
- HTTP-запрос: headers, GET/POST/session, response, cookies.

**Включение в конфиге** (`config/web.php`):

- В **`bootstrap`** добавить **`\'debug\'`**.
- В **`modules.debug`** прописать класс и **`allowedIPs`** (по умолчанию только `127.0.0.1` и `::1`).
- Открывается по URL **`/debug`** или через значок в правом нижнем углу страницы.

**Никогда не включать на prod** — утечёт чувствительная информация.',
                'code_example' => '// config/web.php (dev-only — обычно через if YII_ENV_DEV)
if (YII_ENV_DEV) {
    $config[\'bootstrap\'][] = \'debug\';
    $config[\'modules\'][\'debug\'] = [
        \'class\' => \'yii\\debug\\Module\',
        \'allowedIPs\' => [\'127.0.0.1\', \'::1\', \'192.168.*.*\'],
    ];
}

// Профилирование секции кода
Yii::beginProfile(\'heavy.import\', \'app.import\');
$service->importBigFile();
Yii::endProfile(\'heavy.import\', \'app.import\');

// Затем в /debug → вкладка Profiling',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.logging',
            ],
        ];
    }
}
