<?php

namespace Database\Seeders\Data\Categories\Yii2;

class AssetBundles
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Yii2',
                'question' => 'Что такое AssetBundle в Yii2?',
                'answer' => '`AssetBundle` — это **группа статических файлов** (CSS/JS/изображения), описанная PHP-классом.

- Класс наследуется от `yii\\web\\AssetBundle`.
- Содержит **массивы** `$css` и `$js` с путями относительно `$basePath`.
- Регистрируется в шаблоне через `MyAsset::register($this)`.
- Главная идея — **переиспользование** и автоматическая публикация в `web/assets/`.',
                'code_example' => 'namespace app\\assets;

class AppAsset extends \\yii\\web\\AssetBundle
{
    public $basePath = \'@webroot\';
    public $baseUrl = \'@web\';

    public $css = [
        \'css/site.css\',
    ];
    public $js = [
        \'js/site.js\',
    ];
    public $depends = [
        \\yii\\web\\YiiAsset::class,
        \\yii\\bootstrap5\\BootstrapAsset::class,
    ];
}',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.asset_bundles',
            ],
            [
                'category' => 'Yii2',
                'question' => 'В чём разница между sourcePath, basePath и baseUrl в AssetBundle Yii2?',
                'answer' => 'Эти свойства определяют, **где лежат** файлы и **откуда** их отдавать.

| Свойство | Назначение |
|---|---|
| `sourcePath` | Папка-источник вне `web/` (например, `@vendor/...`) — Yii2 **скопирует** её в `web/assets/<hash>/` |
| `basePath` | Папка, **уже доступная** через HTTP (например, `@webroot`) — копировать не нужно |
| `baseUrl` | Префикс URL для отдачи (например, `@web`) |

- Если задан `sourcePath` — `basePath`/`baseUrl` **вычислятся автоматически** после публикации.
- Свои ассеты в `web/` — указывают только `basePath` + `baseUrl`.',
                'code_example' => '// Свои файлы в /web/css/
class SiteAsset extends AssetBundle {
    public $basePath = \'@webroot\';
    public $baseUrl = \'@web\';
    public $css = [\'css/site.css\'];
}

// Vendor-файлы вне web/
class ChartAsset extends AssetBundle {
    public $sourcePath = \'@npm/chart.js/dist\';
    public $js = [\'chart.umd.js\'];
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.asset_bundles',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Зачем нужен $depends у AssetBundle в Yii2?',
                'answer' => '`$depends` — массив **классов** других bundle, которые должны быть подключены **раньше**.

- Гарантирует **порядок** загрузки: сначала зависимости, потом сам bundle.
- Часто указывают **базовые** bundle: `YiiAsset` (yii.js), `BootstrapAsset` (CSS), `JqueryAsset`, `BootstrapPluginAsset`.
- Дедупликация: один и тот же bundle, указанный несколько раз, **подключается один раз**.
- Без `depends` твой `site.js` может загрузиться **до** jQuery — и сломаться.',
                'code_example' => 'class FormsAsset extends \\yii\\web\\AssetBundle
{
    public $basePath = \'@webroot\';
    public $baseUrl = \'@web\';
    public $js = [\'js/forms.js\'];

    public $depends = [
        \\yii\\web\\JqueryAsset::class,    // jQuery первым
        \\yii\\bootstrap5\\BootstrapPluginAsset::class,
        \\app\\assets\\AppAsset::class,
    ];
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.asset_bundles',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как зарегистрировать AssetBundle в шаблоне Yii2?',
                'answer' => 'Статическим методом **`::register($this)`** во вьюхе или layout.

- `$this` — это объект `View`, ему bundle отдаёт CSS/JS.
- Все зависимости подключаются **рекурсивно**.
- Обычно базовый `AppAsset::register($this)` ставится в **layout** один раз для всех страниц.
- Локальный bundle (например, для одной страницы) регистрируется **в самой вьюхе**.',
                'code_example' => '// views/layouts/main.php
use app\\assets\\AppAsset;
AppAsset::register($this);

// views/post/view.php — добавочный bundle на конкретную страницу
use app\\assets\\ChartAsset;
ChartAsset::register($this);

$this->registerJs("renderChart(\'#sales\');");',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.asset_bundles',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как AssetManager публикует ассеты в Yii2?',
                'answer' => 'Если у bundle задан `sourcePath` — `AssetManager` **публикует** файлы.

- Копирует (или **симлинкует** при `linkAssets = true`) папку в `@webroot/assets/<hash>/`.
- Хеш считается по пути исходника (стабильный, не меняется без надобности).
- Поэтому файлы вне `web/` **доступны через HTTP** без раскрытия их реального расположения.
- Чистится через `php yii cache/flush-all` или удалением `web/assets/`.
- Опция `appendTimestamp = true` добавляет `?v=mtime` к URL для **busting кеша**.',
                'code_example' => '// config/web.php
\'components\' => [
    \'assetManager\' => [
        \'linkAssets\' => false,            // true для dev (симлинки)
        \'appendTimestamp\' => true,        // cache busting
        \'forceCopy\' => YII_ENV_DEV,       // всегда перекопировать в dev
    ],
],

// При первом запросе:
// vendor/bower-asset/jquery/dist/jquery.js
//   -> web/assets/9a3b1c2d/jquery.js
//   URL: /assets/9a3b1c2d/jquery.js?v=1716393600',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.asset_bundles',
            ],
        ];
    }
}
