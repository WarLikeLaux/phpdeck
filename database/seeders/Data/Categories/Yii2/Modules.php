<?php

namespace Database\Seeders\Data\Categories\Yii2;

class Modules
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Yii2',
                'question' => 'Что такое модуль в Yii2?',
                'answer' => 'Модуль — это **самодостаточное под-приложение** внутри основного приложения.

- Имеет **свои** контроллеры, модели, вьюхи, миграции, конфиг.
- Класс модуля — наследник `yii\\base\\Module`.
- Типичные примеры: **админка** (`admin`), **API** (`api`), биллинг, форум.
- Доступ к контроллерам идёт через **id модуля** в URL: `/admin/user/index`.',
                'code_example' => '// modules/admin/Module.php
namespace app\\modules\\admin;

class Module extends \\yii\\base\\Module
{
    public $controllerNamespace = \'app\\\\modules\\\\admin\\\\controllers\';
    public $defaultRoute = \'dashboard\';

    public function init()
    {
        parent::init();
        // Здесь можно подключить свой layout, BD, и т.д.
    }
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.modules',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как зарегистрировать модуль в Yii2?',
                'answer' => 'В конфиге приложения, в секции **`modules`**.

- Ключ массива — это **id модуля** (он же префикс URL).
- Значение — класс или конфиг.
- После регистрации URL `/<modulId>/<controller>/<action>` будет искать контроллер в **`controllerNamespace`** модуля.
- Модуль можно получить из кода: `Yii::$app->getModule(\'admin\')`.',
                'code_example' => '// config/web.php
return [
    \'modules\' => [
        \'admin\' => [
            \'class\' => \'app\\modules\\admin\\Module\',
            \'layout\' => \'admin\',
        ],
        \'api\' => \'app\\modules\\api\\Module\',
    ],
];

// Доступ:
// /admin/user/view?id=1 => app\\modules\\admin\\controllers\\UserController::actionView()
$adminModule = Yii::$app->getModule(\'admin\');',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.modules',
            ],
            [
                'category' => 'Yii2',
                'question' => 'В чём разница между модулем и приложением в Yii2?',
                'answer' => 'Оба наследуются от `yii\\base\\Module`, но **роли разные**.

| | Application | Module |
|---|---|---|
| Запуск | Единственный, через `index.php` | Внутри Application |
| Конфиг | Полный (`db`, `request`, ...) | Свои сервисы поверх Application |
| URL-префикс | Нет | Есть (id модуля) |
| Доступ | `Yii::$app` | `Yii::$app->getModule(\'id\')` |

- Application **сам является** модулем (root module).
- Внутри модуля можно регистрировать **под-модули** (для админки → подсистемы).',
                'code_example' => '// app/web/index.php
$config = require __DIR__ . \'/../config/web.php\';
(new yii\\web\\Application($config))->run();

// А модуль доступен так:
$admin = Yii::$app->getModule(\'admin\');
$sub = $admin->getModule(\'reports\'); // вложенный модуль',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.modules',
            ],
        ];
    }
}
