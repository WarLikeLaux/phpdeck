<?php

namespace Database\Seeders\Data\Categories\Yii2;

class Gii
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Yii2',
                'question' => 'Что такое Gii в Yii2?',
                'answer' => '**Gii** — встроенный **генератор кода** для Yii2, поставляется как модуль (`yii\\gii\\Module` из пакета `yiisoft/yii2-gii`).

**Доступ:**

- Через **веб-интерфейс** по адресу **`/gii`** (после регистрации модуля).
- Через **CLI** — `./yii gii/<generator>` (например, `./yii gii/model --tableName=user`).

**Зачем нужен:**

- Сгенерировать boilerplate: модель из таблицы БД, CRUD-контроллер с view, форму, модуль, расширение.
- Сэкономить время на рутине — Gii пишет ~80% кода, разработчик дописывает специфику.

**Безопасность:** Gii **никогда не включается на prod** — даёт прямой доступ к генерации файлов в проекте. Регистрируется только в `YII_ENV_DEV` с белым списком IP (`allowedIPs`).',
                'code_example' => '// config/web.php
$config = [
    // ... основной конфиг
];

if (YII_ENV_DEV) {
    $config[\'bootstrap\'][] = \'gii\';
    $config[\'modules\'][\'gii\'] = [
        \'class\' => \'yii\\gii\\Module\',
        \'allowedIPs\' => [\'127.0.0.1\', \'::1\'],
    ];
}

return $config;

// Открыть в браузере:
// http://localhost:8080/gii

// CLI-режим:
// ./yii gii/model --tableName=user --modelClass=User',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.gii',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Какие стандартные генераторы есть в Gii?',
                'answer' => 'Gii поставляется с **набором генераторов** для типовых задач.

| Генератор | Что делает |
| --- | --- |
| **Model** | сгенерировать ActiveRecord-класс из таблицы БД (поля, связи, rules, labels) |
| **CRUD** | контроллер + view (`index`/`view`/`create`/`update`/`delete`) для модели; обычно использует ActiveDataProvider + GridView |
| **Controller** | пустой контроллер с заданными actions |
| **Form** | view-файл формы по модели |
| **Module** | каркас модуля (Module.php + папки controllers/views) |
| **Extension** | каркас Yii2-расширения для публикации в Packagist |

**Типичный workflow:**

1. Сделать миграцию (`migrate/create create_post_table`) и применить.
2. **Model** → сгенерировать `app\\models\\Post`.
3. **CRUD** → задать `Model Class` = `app\\models\\Post`, получить `PostController` + view.
4. Поправить генерированное (rules, labels, view, доступы) под бизнес-задачу.

**Важно:** Gii **перезаписывает** файлы при повторной генерации — после правок руками новый прогон может затереть изменения (есть diff-режим).',
                'code_example' => '// CLI-режим: сгенерировать модель Post из таблицы post
./yii gii/model \\
    --tableName=post \\
    --modelClass=Post \\
    --ns=app\\\\models \\
    --generateRelations=all \\
    --generateLabelsFromComments=1

// Сгенерировать CRUD
./yii gii/crud \\
    --modelClass=app\\\\models\\\\Post \\
    --controllerClass=app\\\\controllers\\\\PostController \\
    --searchModelClass=app\\\\models\\\\PostSearch

// Сгенерировать модуль
./yii gii/module \\
    --moduleID=admin \\
    --moduleClass=app\\\\modules\\\\admin\\\\Module',
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'yii2.gii',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Можно ли написать свой генератор для Gii?',
                'answer' => 'Да. **Кастомный генератор** — это класс, унаследованный от **`yii\\gii\\Generator`**, который описывает форму ввода и шаблоны генерации.

**Что нужно реализовать:**

- **`getName()`** — название в UI (например, `\'My Service Generator\'`).
- **`getDescription()`** — описание.
- **`generate()`** — возвращает массив **`CodeFile`** объектов (путь → содержимое).
- **`rules()`** — валидация параметров формы.
- **`stickyAttributes()`** — какие поля запоминать между сессиями.
- View **`form.php`** — HTML-форма ввода параметров.
- View-шаблоны (обычно `default/*.php`) — заготовки кода.

**Регистрация:** в конфиге Gii-модуля через **`generators`**:

`\'generators\' => [\'myService\' => [\'class\' => \'app\\generators\\service\\Generator\']]`

**Зачем:** автоматизировать **свой** boilerplate — например, генератор сервиса с конструктором, репозитория, DTO, тест-класса.',
                'code_example' => '// config/web.php
if (YII_ENV_DEV) {
    $config[\'modules\'][\'gii\'] = [
        \'class\' => \'yii\\gii\\Module\',
        \'allowedIPs\' => [\'127.0.0.1\', \'::1\'],
        \'generators\' => [
            \'myService\' => [
                \'class\' => \'app\\generators\\service\\Generator\',
                \'templates\' => [
                    \'myTemplate\' => \'@app/generators/service/default\',
                ],
            ],
        ],
    ];
}

// generators/service/Generator.php
namespace app\\generators\\service;

use yii\\gii\\CodeFile;

class Generator extends \\yii\\gii\\Generator
{
    public $serviceName;

    public function getName(): string { return \'Service Generator\'; }
    public function getDescription(): string { return \'Создаёт сервис-класс\'; }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            [[\'serviceName\'], \'required\'],
        ]);
    }

    public function generate(): array
    {
        $path = Yii::getAlias("@app/services/{$this->serviceName}.php");
        $code = $this->render(\'service.php\');
        return [new CodeFile($path, $code)];
    }
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.gii',
            ],
        ];
    }
}
