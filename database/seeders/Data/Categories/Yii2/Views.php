<?php

namespace Database\Seeders\Data\Categories\Yii2;

class Views
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Yii2',
                'question' => 'Что такое View в Yii2 и где лежат шаблоны?',
                'answer' => '`View` — это **компонент** для рендера PHP-шаблонов (`.php` файлы).

- Шаблоны лежат в `views/<controller-id>/<action-id>.php`.
- Layouts — в `views/layouts/main.php`.
- Внутри шаблона переменная **`$this`** — это **сам объект `View`**, а не контроллер.
- Параметры из `render(\'view\', [\'name\' => \'X\'])` распакованы в **локальные переменные**.',
                'code_example' => '// SiteController.php
public function actionAbout()
{
    return $this->render(\'about\', [
        \'company\' => \'ACME\',
        \'year\' => 2026,
    ]);
}

// views/site/about.php
<?php
/** @var yii\\web\\View $this */
$this->title = \'О нас\';
?>
<h1>О компании <?= \\yii\\helpers\\Html::encode($company) ?></h1>
<p>Год: <?= $year ?></p>',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.views',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как работает layout в Yii2 и что такое $content?',
                'answer' => 'Layout — это **внешний шаблон**, в который вставляется результат рендера экшен-вьюхи.

- В layout доступна переменная **`$content`** — это HTML, отрендеренный экшен-вьюхой.
- По умолчанию `views/layouts/main.php`.
- Меняется через `$this->layout` в контроллере (`\'admin\'` → `layouts/admin.php`).
- `$this` в layout — тот же `View`, что и во вьюхе.',
                'code_example' => '// views/layouts/main.php
<?php
use yii\\helpers\\Html;
/** @var yii\\web\\View $this */
/** @var string $content */
$this->beginPage();
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
    <?php $this->beginBody() ?>
    <main><?= $content ?></main>
    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.views',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Зачем нужны $this->title и $this->params во View Yii2?',
                'answer' => '`View` хранит **состояние страницы**, доступное и во вьюхе, и в layout.

- `$this->title` — будет в `<title>` через `Html::encode($this->title)` в layout.
- `$this->params` — массив для произвольных данных между вьюхой и layout (`breadcrumbs`, sidebar и т.д.).
- Это удобнее, чем пробрасывать вручную через `render` — данные **прокидываются неявно**.',
                'code_example' => '// views/post/view.php
$this->title = $post->title;
$this->params[\'breadcrumbs\'][] = [\'label\' => \'Блог\', \'url\' => [\'index\']];
$this->params[\'breadcrumbs\'][] = $post->title;

// views/layouts/main.php
<title><?= Html::encode($this->title) ?></title>
<?= \\yii\\widgets\\Breadcrumbs::widget([
    \'links\' => $this->params[\'breadcrumbs\'] ?? [],
]) ?>',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.views',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как подключать CSS и JS в шаблоне Yii2?',
                'answer' => '`View` имеет методы для **регистрации ассетов**.

- `registerCssFile($url)` / `registerJsFile($url)` — подключение внешних файлов.
- `registerCss($code)` / `registerJs($code)` — inline-блоки.
- У JS есть позиция: `View::POS_HEAD`, `POS_BEGIN`, `POS_END` (по умолч.), `POS_READY` (внутри `$(document).ready`), `POS_LOAD` (внутри `$(window).on(\'load\')`).
- Регистрация **дедуплицируется** по ключу (второй аргумент).',
                'code_example' => 'use yii\\web\\View;

$this->registerCssFile(\'@web/css/post.css\', [\'depends\' => [\\yii\\bootstrap5\\BootstrapAsset::class]]);

$this->registerJs(
    "console.log(\'Loaded!\');",
    View::POS_READY,
    \'post-init\'
);

$this->registerCss(\'.post { color: #333; }\');',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.views',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Зачем нужны beginPage/endPage/beginBody/endBody в layout Yii2?',
                'answer' => 'Это **«крючки»** View, без которых не работает регистрация ассетов и виджетов.

- `beginPage()` / `endPage()` — обёртка всей страницы.
- `head()` (в `<head>`) — место для CSS и meta.
- `beginBody()` / `endBody()` — границы `<body>`. Сюда инжектятся JS-блоки нужных позиций.
- Без них зарегистрированные CSS/JS **просто не появятся** в HTML, а виджеты типа `ActiveForm` не закроются корректно.',
                'code_example' => '<?php $this->beginPage() ?>
<!DOCTYPE html>
<html>
<head>
    <?php $this->head() ?>
</head>
<body>
    <?php $this->beginBody() ?>

    <?= $content ?>

    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.views',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как написать собственный виджет в Yii2?',
                'answer' => 'Виджет — класс-наследник `yii\\base\\Widget` с методами **`init()`** и **`run()`**.

- `init()` — инициализация, валидация свойств.
- `run()` — возвращает HTML или печатает (но **лучше возвращать**).
- Вызывается через статический метод **`::widget([\'prop\' => \'value\'])`** или парой `::begin()` / `::end()`.
- Параметры передаются как **публичные свойства**.',
                'code_example' => '// widgets/Alert.php
namespace app\\widgets;

class Alert extends \\yii\\base\\Widget
{
    public $type = \'info\';
    public $message = \'\';

    public function init()
    {
        parent::init();
        if (!$this->message) {
            throw new \\yii\\base\\InvalidConfigException(\'message required\');
        }
    }

    public function run()
    {
        return \\yii\\helpers\\Html::tag(\'div\',
            \\yii\\helpers\\Html::encode($this->message),
            [\'class\' => "alert alert-{$this->type}"]
        );
    }
}

// В шаблоне:
<?= \\app\\widgets\\Alert::widget([\'type\' => \'success\', \'message\' => \'Сохранено\']) ?>',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.views',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое Pjax в Yii2 и когда его использовать?',
                'answer' => '`Pjax` — виджет, оборачивающий часть страницы и подменяющий её **через AJAX** без полной перезагрузки.

- Перехватывает клики по ссылкам и сабмиты форм **внутри блока**.
- Использует `history.pushState` — URL обновляется, кнопка «назад» работает.
- Часто используется поверх **`GridView`** для пагинации и сортировки.
- Опция `enablePushState`, `timeout`, `clientOptions`.',
                'code_example' => 'use yii\\widgets\\Pjax;

<?php Pjax::begin([\'id\' => \'users-grid\', \'timeout\' => 5000]) ?>
    <?= \\yii\\grid\\GridView::widget([
        \'dataProvider\' => $dataProvider,
        \'columns\' => [\'id\', \'name\', \'email\'],
    ]) ?>
<?php Pjax::end() ?>',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.views',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как переиспользовать кусок шаблона во View Yii2?',
                'answer' => 'Через **`$this->render(\'_partial\', $params)`** во вьюхе (без `Partial`-приставки в имени).

- `render` внутри `View` — это **то же**, что и в контроллере, только не применяет layout.
- Имя файла начинают с подчёркивания (`_form.php`) — это **конвенция Yii2** для приватных partial.
- Альтернатива — виджет, если кусок имеет логику.
- `renderFile(\'@app/views/site/_x.php\', $params)` — путь напрямую.',
                'code_example' => '// views/post/view.php
<h1><?= Html::encode($post->title) ?></h1>
<?= $this->render(\'_meta\', [\'post\' => $post]) ?>
<?= $this->render(\'_comments\', [\'comments\' => $post->comments]) ?>

// views/post/_meta.php
<small>
    Автор: <?= Html::encode($post->author->name) ?>,
    дата: <?= Yii::$app->formatter->asDate($post->created_at) ?>
</small>',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.views',
            ],
        ];
    }
}
