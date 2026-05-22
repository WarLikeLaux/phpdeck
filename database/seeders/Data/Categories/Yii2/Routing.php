<?php

namespace Database\Seeders\Data\Categories\Yii2;

class Routing
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Yii2',
                'question' => 'Что такое UrlManager в Yii2?',
                'answer' => '`UrlManager` — это **компонент приложения**, отвечающий за разбор URL и генерацию ссылок.

- Регистрируется в `config/web.php` в секции `components`.
- Парсит входящий URL → `[controller, action, params]`.
- Делает обратное: по маршруту строит URL (`Url::to`, `Url::toRoute`).
- Без него работают только **дефолтные роуты** вида `?r=site/index`.',
                'code_example' => '// config/web.php
return [
    \'components\' => [
        \'urlManager\' => [
            \'enablePrettyUrl\' => true,
            \'showScriptName\' => false,
            \'rules\' => [
                \'<controller:\\w+>/<action:\\w+>\' => \'<controller>/<action>\',
            ],
        ],
    ],
];',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.routing',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Зачем включать enablePrettyUrl и showScriptName в Yii2?',
                'answer' => 'Два флага UrlManager, которые делают **красивые URL**:

- `enablePrettyUrl = true` — включает rules-based роутинг. Без него URL выглядит как `index.php?r=site/view&id=1`.
- `showScriptName = false` — убирает `index.php` из URL.
- Для работы `showScriptName = false` нужен **rewrite-движок** веб-сервера (`mod_rewrite` в Apache или `try_files` в nginx).',
                'code_example' => '\'urlManager\' => [
    \'enablePrettyUrl\' => true,
    \'showScriptName\' => false,
    \'rules\' => [],
],

// До: /index.php?r=site/view&id=1
// После: /site/view?id=1',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.routing',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как работает простое правило rules в UrlManager Yii2?',
                'answer' => 'Правило — это **пара `шаблон => маршрут`** в массиве `rules`.

- В шаблоне `<имя:regex>` — это **именованный параметр**.
- Имя без regex (`<id>`) использует дефолтное `[^\\/]+`.
- Маршрут указывает контроллер/экшен и куда подставить параметры.
- Правила проверяются **сверху вниз** — первое совпавшее побеждает.',
                'code_example' => '\'rules\' => [
    // /posts/42 => site/view?id=42
    \'posts/<id:\\d+>\' => \'site/view\',

    // /article/best-tips => blog/post?slug=best-tips
    \'article/<slug:[\\w-]+>\' => \'blog/post\',

    // Общее правило (в конце)
    \'<controller:\\w+>/<action:\\w+>\' => \'<controller>/<action>\',
],',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.routing',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как сделать дефолтный роут "/" в Yii2?',
                'answer' => 'Два способа:

- Свойство `defaultRoute` приложения — по умолчанию `site/index`. Меняется в `config/web.php`.
- Свойство `$defaultAction` контроллера — по умолчанию `index`. Меняет, какой экшен открывается при `/site`.
- Маршрут `/` → `defaultRoute` приложения → дефолтный экшен этого контроллера.',
                'code_example' => '// config/web.php
return [
    \'defaultRoute\' => \'main/dashboard\',
    // ...
];

// Внутри контроллера
class MainController extends Controller
{
    public $defaultAction = \'dashboard\';

    public function actionDashboard()
    {
        return $this->render(\'dashboard\');
    }
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.routing',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как генерировать URL в Yii2 (Url::to)?',
                'answer' => 'Класс-хелпер `yii\\helpers\\Url` строит URL по маршруту через **тот же UrlManager**.

- `Url::to([\'site/view\', \'id\' => 1])` — относительный URL.
- `Url::to([\'site/view\'], true)` — абсолютный (с хостом).
- `Url::toRoute(...)` — то же, но без `to()`-логики для строк.
- Первый элемент массива — маршрут, остальные — query/path-параметры.',
                'code_example' => 'use yii\\helpers\\Url;

echo Url::to([\'site/view\', \'id\' => 42]);
// => /site/view?id=42 (или /posts/42, если есть rule)

echo Url::to([\'site/view\', \'id\' => 42], true);
// => https://example.com/posts/42

echo Url::home();          // /
echo Url::current([\'page\' => 2]); // текущий URL с заменой page',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.routing',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как задать суффикс .html для всех URL в Yii2?',
                'answer' => 'У `UrlManager` есть свойство **`suffix`**.

- `suffix => \'.html\'` — добавит `.html` ко всем сгенерированным URL и потребует его при парсинге.
- Применяется ко **всем правилам** разом.
- Для конкретного правила можно переопределить (`suffix` в самом rule).
- Пустая строка `suffix => \'\'` — без суффикса (дефолт).',
                'code_example' => '\'urlManager\' => [
    \'enablePrettyUrl\' => true,
    \'showScriptName\' => false,
    \'suffix\' => \'.html\',
    \'rules\' => [
        \'posts/<id:\\d+>\' => \'site/view\',
    ],
],

// /posts/42.html => site/view?id=42',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.routing',
            ],
            [
                'category' => 'Yii2',
                'question' => 'В каком порядке UrlManager проверяет правила в Yii2?',
                'answer' => 'Правила проверяются **сверху вниз** по массиву `rules`.

- Первое **совпавшее** правило побеждает — остальные игнорируются.
- Поэтому **специфичные правила** (с фиксированным префиксом) идут **раньше** общих.
- Общее правило `<controller:\\w+>/<action:\\w+>` должно быть **в конце**.
- Если ни одно правило не совпало — Yii2 пытается парсить URL как дефолтный `controller/action`.',
                'code_example' => '\'rules\' => [
    // Специфичные — сначала
    \'admin/dashboard\' => \'admin/index\',
    \'posts/<id:\\d+>\' => \'site/view\',
    \'<slug:[\\w-]+>\' => \'blog/show\',

    // Общие — в конце
    \'<controller:\\w+>/<action:\\w+>\' => \'<controller>/<action>\',
],',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.routing',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как ограничить HTTP-метод в правиле UrlManager Yii2?',
                'answer' => 'Используется **префикс верба** в ключе правила.

- Поддерживаемые верба: `GET`, `POST`, `PUT`, `DELETE`, `OPTIONS`, `HEAD`, `PATCH`.
- Можно несколько через запятую: `\'GET,HEAD posts\'`.
- Если метод не подходит — правило просто **не матчится**, и Yii2 идёт дальше.
- Полезно для REST-стиля (`UrlManager` + ограничение метода).',
                'code_example' => '\'rules\' => [
    \'GET posts\' => \'post/index\',
    \'POST posts\' => \'post/create\',
    \'GET posts/<id:\\d+>\' => \'post/view\',
    \'PUT,PATCH posts/<id:\\d+>\' => \'post/update\',
    \'DELETE posts/<id:\\d+>\' => \'post/delete\',
],',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.routing',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое GroupUrlRule в Yii2 и зачем он нужен?',
                'answer' => '`GroupUrlRule` группирует набор правил под **общим префиксом** и/или модулем.

- Префикс не повторяется в каждом правиле.
- Часто используется для **админки**, **API**, **модулей**.
- В `ruleConfig` можно задать общий класс правила.
- Уменьшает дублирование и **ускоряет матчинг** (внутри группы есть быстрый отсев по префиксу).',
                'code_example' => '\'rules\' => [
    [
        \'class\' => \'yii\\web\\GroupUrlRule\',
        \'prefix\' => \'admin\',
        \'rules\' => [
            \'\' => \'index\',                   // /admin => admin/default/index
            \'<controller:\\w+>\' => \'<controller>\',
            \'<controller:\\w+>/<id:\\d+>\' => \'<controller>/view\',
        ],
    ],
],',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.routing',
            ],
        ];
    }
}
