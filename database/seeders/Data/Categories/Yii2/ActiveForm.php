<?php

namespace Database\Seeders\Data\Categories\Yii2;

class ActiveForm
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Yii2',
                'question' => 'Что такое ActiveForm в Yii2 и зачем он нужен?',
                'answer' => '`ActiveForm` — это **виджет** для генерации HTML-форм, связанных с моделью.

- Сам создаёт `<form>` с CSRF-токеном.
- Через `$form->field()` рендерит **label, input, error, hint** разом.
- Поддерживает **client-side** и **AJAX** валидацию (использует те же `rules()`).
- Имя атрибута берётся из модели, поэтому при `$model->load($post)` всё собирается обратно.',
                'code_example' => 'use yii\\widgets\\ActiveForm;
use yii\\helpers\\Html;

<?php $form = ActiveForm::begin([\'id\' => \'login-form\']) ?>
    <?= $form->field($model, \'email\') ?>
    <?= $form->field($model, \'password\')->passwordInput() ?>
    <?= Html::submitButton(\'Войти\', [\'class\' => \'btn btn-primary\']) ?>
<?php ActiveForm::end() ?>',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.active_form',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Какие input-методы есть у $form->field() в Yii2?',
                'answer' => '`$form->field()` возвращает `ActiveField`, у которого **fluent API**.

- `textInput([\'maxlength\' => true])` — обычный input.
- `passwordInput()` — `type=password`.
- `textarea([\'rows\' => 6])`.
- `checkbox()` / `radio()`.
- `dropDownList($items)` — `<select>`.
- `listBox($items, [\'multiple\' => true])`.
- `checkboxList($items)` / `radioList($items)`.
- `fileInput()` — для загрузки файлов (плюс `\'options\' => [\'enctype\' => \'multipart/form-data\']` у формы).',
                'code_example' => '<?= $form->field($model, \'title\')->textInput([\'maxlength\' => true]) ?>
<?= $form->field($model, \'description\')->textarea([\'rows\' => 6]) ?>
<?= $form->field($model, \'category_id\')->dropDownList(
    Category::find()->select(\'name\')->indexBy(\'id\')->column(),
    [\'prompt\' => \'-- выберите --\']
) ?>
<?= $form->field($model, \'tags\')->checkboxList([
    \'php\' => \'PHP\', \'yii\' => \'Yii\', \'sql\' => \'SQL\',
]) ?>',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.active_form',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как ActiveForm рендерит ошибки валидации в Yii2?',
                'answer' => 'После `$model->validate()` ошибки лежат в `$model->errors`. ActiveForm **автоматически**:

- Добавляет класс `has-error` (или `is-invalid` в Bootstrap 5) на контейнер поля.
- Подставляет первое сообщение в `<div class="help-block">` (или аналог).
- Если включена client-валидация — обновляет ошибки **без перезагрузки**.
- Метод `errorSummary($model)` рисует **общий список ошибок** наверху формы.',
                'code_example' => '// Controller
if ($model->load(Yii::$app->request->post()) && $model->validate()) {
    $model->save();
    return $this->redirect([\'view\', \'id\' => $model->id]);
}
return $this->render(\'form\', [\'model\' => $model]);

// View
<?= $form->errorSummary($model) ?>
<?= $form->field($model, \'email\') ?>',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.active_form',
            ],
            [
                'category' => 'Yii2',
                'question' => 'В чём разница между enableClientValidation и enableAjaxValidation в Yii2?',
                'answer' => 'Два независимых типа клиентской валидации.

| Опция | Где валидируется | Какие правила работают |
|---|---|---|
| `enableClientValidation` (по умолч. `true`) | В **браузере JS** | Только те, у которых валидатор реализует `clientValidateAttribute` |
| `enableAjaxValidation` (по умолч. `false`) | На **сервере**, AJAX-запросом | **Все** правила Yii2 |

- AJAX-валидация полезна для **серверных** правил (`unique`, кастомные).
- Контроллер должен на AJAX-запрос вернуть `ActiveForm::validate($model)` (JSON).',
                'code_example' => '$form = ActiveForm::begin([
    \'enableClientValidation\' => true,
    \'enableAjaxValidation\' => true,
]);

// Controller
public function actionCreate()
{
    $model = new User();
    if ($model->load(Yii::$app->request->post())) {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if ($model->save()) {
            return $this->redirect([\'view\', \'id\' => $model->id]);
        }
    }
    return $this->render(\'create\', compact(\'model\'));
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.active_form',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как сделать сабмит-кнопку в форме Yii2?',
                'answer' => 'Через хелпер **`Html::submitButton`** внутри формы.

- Первый аргумент — текст кнопки (HTML-encoded **не** автоматически — передавай безопасный текст).
- Второй — массив HTML-атрибутов.
- Также есть `Html::button`, `Html::a` (ссылка-как-кнопка).
- Внутри `ActiveForm` кнопка просто триггерит submit формы — обработчик ловится в контроллере.',
                'code_example' => 'use yii\\helpers\\Html;

<?= Html::submitButton(
    $model->isNewRecord ? \'Создать\' : \'Сохранить\',
    [\'class\' => $model->isNewRecord ? \'btn btn-success\' : \'btn btn-primary\']
) ?>

<?= Html::a(\'Отмена\', [\'index\'], [\'class\' => \'btn btn-secondary\']) ?>',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.active_form',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Какие самые полезные методы у Html-хелпера Yii2?',
                'answer' => 'Класс `yii\\helpers\\Html` — это **безопасный билдер HTML**.

- `Html::encode($str)` — экранирует HTML (защита от XSS).
- `Html::a($text, $url, $opts)` — ссылка (`$url` может быть **массив-маршрут**).
- `Html::img($src, $opts)`.
- `Html::tag($name, $content, $opts)`, `Html::beginTag` / `Html::endTag`.
- `Html::csrfMetaTags()` — мета-теги CSRF для AJAX.
- `Html::dropDownList`, `Html::radioList`, `Html::checkboxList`.',
                'code_example' => 'use yii\\helpers\\Html;

echo Html::encode($user->name);
// safe: <script> => &lt;script&gt;

echo Html::a(\'Профиль\', [\'user/view\', \'id\' => $user->id],
    [\'class\' => \'link-primary\']);

echo Html::tag(\'span\', $user->role, [\'class\' => \'badge\']);

echo Html::img(\'@web/avatar.png\', [\'alt\' => \'avatar\']);',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.active_form',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как кастомизировать template ActiveField в Yii2?',
                'answer' => 'У `$form->field()` есть свойства **`template`**, **`options`**, **`inputOptions`**, **`labelOptions`**.

- `template` — строка с плейсхолдерами `{label}`, `{input}`, `{hint}`, `{error}`.
- Можно задать **глобально** на форме (`fieldConfig`) или для конкретного поля.
- Полезно для **inline-форм**, **bootstrap horizontal** layout, кастомных обёрток.',
                'code_example' => '$form = ActiveForm::begin([
    \'fieldConfig\' => [
        \'template\' => "<div class=\\"row\\"><div class=\\"col-3\\">{label}</div><div class=\\"col-9\\">{input}{error}</div></div>",
        \'options\' => [\'class\' => \'form-group mb-3\'],
    ],
]);

// Для одного поля:
<?= $form->field($model, \'name\', [
    \'template\' => \'{input}<small>{error}</small>\',
])->textInput() ?>',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.active_form',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое errorSummary в ActiveForm Yii2?',
                'answer' => '`$form->errorSummary($model)` рисует **список всех ошибок** модели одним блоком.

- Удобно для форм с большим количеством полей.
- Принимает **массив моделей** — покажет ошибки по всем.
- По умолчанию — `<div class="error-summary"><p>...</p><ul>...</ul></div>`.
- Опция `header` меняет заголовок, `footer` — подвал.',
                'code_example' => '<?php $form = ActiveForm::begin() ?>
    <?= $form->errorSummary($model, [
        \'header\' => \'<p>Исправьте следующие ошибки:</p>\',
    ]) ?>
    <?= $form->field($model, \'email\') ?>
    <?= $form->field($model, \'password\')->passwordInput() ?>
<?php ActiveForm::end() ?>',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.active_form',
            ],
        ];
    }
}
