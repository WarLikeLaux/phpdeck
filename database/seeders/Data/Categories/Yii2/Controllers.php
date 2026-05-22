<?php

namespace Database\Seeders\Data\Categories\Yii2;

class Controllers
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Yii2',
                'question' => 'От какого класса наследуется веб-контроллер в Yii2 и зачем?',
                'answer' => 'Веб-контроллер наследуется от `yii\\web\\Controller`.

- Базовый класс даёт **методы рендера** (`render`, `renderPartial`, `renderAjax`).
- Готовые **редиректы** (`redirect`, `goHome`, `goBack`).
- Хелперы ответа (`asJson`, `asXml`).
- Поддержку **layout** через свойство `$layout`.
- Хуки `beforeAction` и `afterAction`.',
                'code_example' => 'namespace app\\controllers;

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
                'topic' => 'yii2.controllers',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как объявить экшен в контроллере Yii2 (inline action)?',
                'answer' => 'Экшены inline — это **публичные методы** с префиксом `action`.

- Метод `actionView` соответствует пути `/site/view`.
- Имя экшена в URL — это **lowercase + дефисы** для CamelCase: `actionUserList` → `/site/user-list`.
- Возвращаемое значение — это содержимое ответа (строка от `render`, массив для JSON, объект `Response`).',
                'code_example' => 'namespace app\\controllers;

use yii\\web\\Controller;

class SiteController extends Controller
{
    public function actionAbout()
    {
        return $this->render(\'about\');
    }

    public function actionUserList()
    {
        // URL: /site/user-list
        return $this->render(\'user-list\');
    }
}',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.controllers',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как параметры из URL/POST попадают в экшен Yii2?',
                'answer' => 'Yii2 биндит параметры экшена **по имени** аргумента метода.

- Сначала ищет в `$_GET`, затем в `$_POST`.
- Если параметра нет и **нет default value** — будет `BadRequestHttpException`.
- Тип `int $id` приводит значение автоматически.
- Это работает только для inline-экшенов; в standalone — через `$actionParams`.',
                'code_example' => 'public function actionView($id, $tab = \'main\')
{
    // URL: /site/view?id=42&tab=stats
    // $id = 42, $tab = \'stats\'
    return $this->render(\'view\', [
        \'id\' => (int) $id,
        \'tab\' => $tab,
    ]);
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.controllers',
            ],
            [
                'category' => 'Yii2',
                'question' => 'В чём разница между render(), renderPartial() и renderAjax() в Yii2?',
                'answer' => 'Все три метода **рендерят вьюху**, но по-разному обрабатывают layout и assets.

| Метод | Применяет layout | Регистрирует JS/CSS | Когда использовать |
|---|---|---|---|
| `render` | Да | Да | Обычная страница |
| `renderPartial` | Нет | Нет | Кусок HTML внутри другой страницы |
| `renderAjax` | Нет | Да (инжектит inline) | AJAX-ответ, где нужны скрипты виджета |

- `renderAjax` — это `renderPartial` + регистрация JS/CSS прямо в HTML-ответ.',
                'code_example' => 'public function actionForm()
{
    if (Yii::$app->request->isAjax) {
        return $this->renderAjax(\'_form\', [\'model\' => $model]);
    }
    return $this->render(\'form\', [\'model\' => $model]);
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.controllers',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как сделать редирект и вернуть пользователя назад в Yii2?',
                'answer' => 'Для редиректов используются методы базового контроллера:

- `redirect(url)` — редирект на URL (можно массив-маршрут: `[\'site/index\']`).
- `goHome()` — на `Yii::$app->homeUrl`.
- `goBack()` — на сохранённый URL возврата (используется после логина).
- Все возвращают **объект `Response`** — его нужно вернуть из экшена через `return`.',
                'code_example' => 'public function actionSave()
{
    // ... сохранение
    Yii::$app->session->setFlash(\'success\', \'Сохранено\');
    return $this->redirect([\'site/view\', \'id\' => $model->id]);
}

public function actionLogin()
{
    // ... логин
    return $this->goBack();
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.controllers',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как вернуть JSON из экшена Yii2?',
                'answer' => 'Два способа:

- `$this->asJson($data)` — возвращает объект `Response` с уже выставленным форматом.
- Установить формат ответа вручную: `Yii::$app->response->format = Response::FORMAT_JSON;` и `return $array;`.

В обоих случаях Yii2 сам сериализует массив/объект в JSON и проставит заголовок `Content-Type: application/json`.',
                'code_example' => 'use yii\\web\\Response;

public function actionApiUser($id)
{
    $user = User::findOne($id);
    return $this->asJson([
        \'id\' => $user->id,
        \'name\' => $user->name,
    ]);
}

// Альтернатива:
public function actionApiList()
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    return User::find()->asArray()->all();
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.controllers',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Зачем нужны beforeAction() и afterAction() в контроллере Yii2?',
                'answer' => 'Это **хуки контроллера** для логики до/после экшена.

- `beforeAction($action)` — вызывается **перед** экшеном. Если вернуть `false`, экшен **не выполнится**.
- `afterAction($action, $result)` — вызывается **после**, может изменить результат.
- Важно вызывать `parent::beforeAction($action)` — иначе сломается CSRF и фильтры.
- Альтернатива хукам — **behaviors** (`AccessControl`, `VerbFilter`).',
                'code_example' => 'public function beforeAction($action)
{
    if (!parent::beforeAction($action)) {
        return false;
    }
    if ($action->id === \'admin\' && !Yii::$app->user->can(\'admin\')) {
        throw new \\yii\\web\\ForbiddenHttpException();
    }
    return true;
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.controllers',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как сменить layout в конкретном контроллере или экшене Yii2?',
                'answer' => 'Через свойство `$layout` базового контроллера.

- На уровне класса: `public $layout = \'admin\';` (файл `views/layouts/admin.php`).
- Внутри экшена: `$this->layout = \'print\';` — действует только на этот вызов.
- Значение `false` — **отключает layout** (вьюха рендерится без обёртки).
- Путь относительно `views/layouts/` модуля или приложения.',
                'code_example' => 'class AdminController extends Controller
{
    public $layout = \'admin\';

    public function actionPrint($id)
    {
        $this->layout = false; // без layout
        return $this->render(\'print\', [\'id\' => $id]);
    }
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.controllers',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое standalone action в Yii2 и когда его использовать?',
                'answer' => 'Standalone action — это **отдельный класс**, наследник `yii\\base\\Action`, подключаемый через `actions()`.

- Используется, когда экшен **переиспользуется** в разных контроллерах (например, `CaptchaAction`, `ErrorAction`).
- Класс должен иметь метод `run()`.
- В `actions()` контроллера указывается ключ → класс/конфиг.
- Параметры биндятся в `run()` так же по имени.',
                'code_example' => '// classes/actions/HelloAction.php
class HelloAction extends \\yii\\base\\Action
{
    public function run($name = \'world\')
    {
        return $this->controller->render(\'hello\', [\'name\' => $name]);
    }
}

// SiteController
public function actions()
{
    return [
        \'hello\' => HelloAction::class,
        \'error\' => [\'class\' => \'yii\\web\\ErrorAction\'],
    ];
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.controllers',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как переименовать контроллер ProductCardController в URL Yii2?',
                'answer' => 'Yii2 формирует ID контроллера по правилу **PascalCase → lowercase-with-dashes**.

- `ProductCardController` → `product-card`.
- `actionSpecialOffer` → `special-offer`.
- Итоговый URL: `/product-card/special-offer`.
- Это нужно учитывать в `Url::to([...])` и в `urlManager` rules — там пишутся именно ID, а не имена классов.',
                'code_example' => '// app/controllers/ProductCardController.php
class ProductCardController extends Controller
{
    public function actionSpecialOffer($id)
    {
        // URL: /product-card/special-offer?id=10
        return $this->render(\'special-offer\', [\'id\' => $id]);
    }
}

// Генерация ссылки:
echo Url::to([\'product-card/special-offer\', \'id\' => 10]);',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.controllers',
            ],
        ];
    }
}
