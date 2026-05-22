<?php

namespace Database\Seeders\Data\Categories\Yii2;

class Errors
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Yii2',
                'question' => 'Что такое ErrorHandler в Yii2?',
                'answer' => '**ErrorHandler** — компонент, который **перехватывает PHP-ошибки и исключения** и превращает их в красивую страницу/JSON-ответ.

**Два класса:**

- **`yii\\web\\ErrorHandler`** — для web-приложений.
- **`yii\\console\\ErrorHandler`** — для CLI.

**Что делает:**

- Конвертирует **PHP errors** (warnings, notices, fatal) в исключения через `set_error_handler`.
- Логирует исключения через **`yii\\log\\Logger`**.
- В **debug-режиме** показывает stack trace, контекст, версии — через шаблон `views/errorHandler/exception.php`.
- В **prod-режиме** показывает безопасную страницу, рендеря **`errorAction`** (по умолчанию `site/error`).

**Настройка** через `components.errorHandler.errorAction` в конфиге.',
                'code_example' => '// config/web.php
return [
    \'components\' => [
        \'errorHandler\' => [
            \'errorAction\' => \'site/error\',
        ],
    ],
];

// В debug — фреймворк показывает подробный отладочный экран.
// В prod (YII_DEBUG=false) — рендерится actionError() из SiteController.

defined(\'YII_DEBUG\') or define(\'YII_DEBUG\', false);
defined(\'YII_ENV\') or define(\'YII_ENV\', \'prod\');',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.errors',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как работает `SiteController::actionError()` в Yii2?',
                'answer' => '**`actionError()`** — стандартный экшен в `SiteController`, на который **ErrorHandler** перенаправляет при ошибке. Создаётся в шаблоне `basic` из коробки.

**Что делает:**

1. Получает исключение из **`Yii::$app->errorHandler->exception`**.
2. Если есть — рендерит view **`views/site/error.php`** с переменными `$name` и `$message`.
3. View показывает заголовок и сообщение пользователю.

**Зачем отдельный экшен:** ErrorHandler нельзя просто рендерить view напрямую — нужен полноценный controller с layout, themes, контекстом приложения.

**Можно переопределить:**

- Сделать **свой `actionError`** в любом контроллере и прописать его в `errorAction`.
- Для **REST/API** возвращать JSON, например `[\'error\' => $exception->getMessage()]`.
- В `Yii::$app->errorHandler->exception` доступен исходный объект — можно проверить тип (`NotFoundHttpException` и т. д.) и кастомизировать.',
                'code_example' => 'namespace app\\controllers;

use Yii;
use yii\\web\\Controller;
use yii\\filters\\VerbFilter;
use yii\\web\\ErrorAction;

class SiteController extends Controller
{
    public function actions()
    {
        return [
            \'error\' => [
                \'class\' => ErrorAction::class,
                \'view\' => \'@app/views/site/error.php\',
            ],
        ];
    }

    // Или вручную:
    public function actionError()
    {
        $exception = Yii::$app->errorHandler->exception;
        if ($exception !== null) {
            return $this->render(\'error\', [
                \'name\' => $exception instanceof \\yii\\web\\HttpException
                    ? $exception->getName()
                    : \'Ошибка\',
                \'message\' => $exception->getMessage(),
                \'exception\' => $exception,
            ]);
        }
    }
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.errors',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Какие HTTP-исключения есть в Yii2 и как их использовать?',
                'answer' => 'Yii2 предоставляет **готовые исключения** в `yii\\web\\*`, которые **автоматически** превращаются в HTTP-ответ с нужным статусом.

| Исключение | HTTP-статус | Когда бросать |
| --- | --- | --- |
| **`BadRequestHttpException`** | **400** | невалидный запрос (плохой JSON, missing param) |
| **`UnauthorizedHttpException`** | **401** | не аутентифицирован |
| **`ForbiddenHttpException`** | **403** | аутентифицирован, но нет прав |
| **`NotFoundHttpException`** | **404** | ресурс не найден |
| **`MethodNotAllowedHttpException`** | **405** | неверный HTTP-метод |
| **`ConflictHttpException`** | **409** | конфликт состояния |
| **`UnprocessableEntityHttpException`** | **422** | валидация формы не прошла |
| **`TooManyRequestsHttpException`** | **429** | rate limit |
| **`ServerErrorHttpException`** | **500** | внутренняя ошибка |

Все наследуют **`yii\\web\\HttpException`** — можно бросить его напрямую с любым статусом.',
                'code_example' => 'use yii\\web\\NotFoundHttpException;
use yii\\web\\ForbiddenHttpException;
use yii\\web\\BadRequestHttpException;

public function actionView($id)
{
    $post = Post::findOne($id);
    if ($post === null) {
        throw new NotFoundHttpException(\'Пост не найден.\');
    }
    if ($post->user_id !== Yii::$app->user->id) {
        throw new ForbiddenHttpException(\'Нет доступа.\');
    }
    return $this->render(\'view\', [\'post\' => $post]);
}

public function actionImport()
{
    $file = UploadedFile::getInstanceByName(\'file\');
    if (!$file) {
        throw new BadRequestHttpException(\'Файл не загружен.\');
    }
    // ...
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.errors',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Чем отличаются PHP error и exception в Yii2?',
                'answer' => 'В Yii2 **`ErrorHandler`** видит и то и другое, но обрабатывает по-разному.

| Признак | PHP Error | Exception |
| --- | --- | --- |
| Источник | `E_WARNING`, `E_NOTICE`, `E_ERROR` | `throw new ...` |
| Хук PHP | `set_error_handler` | `set_exception_handler` |
| В Yii2 | **конвертируется в `\\yii\\base\\ErrorException`** | пробрасывается как есть |
| Можно поймать `try/catch` | **только после конвертации** | да |
| Fatal errors | через `register_shutdown_function` | — |

**Что это даёт:**

- Любой `notice` или `warning` в коде превращается в исключение → можно ловить `try/catch`.
- В **debug** мелкие warnings всё равно ломают страницу (это **правильное** поведение для разработки).
- На **prod** обычно ставят `error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED)` чтобы не падать на мелочах.

**Базовые классы:** `yii\\base\\Exception`, `yii\\base\\ErrorException`, `yii\\base\\InvalidConfigException`, `yii\\base\\InvalidArgumentException`, `yii\\base\\UserException` (последний — «ошибка от пользователя», показывается в красивой странице).',
                'code_example' => 'use yii\\base\\ErrorException;
use yii\\base\\InvalidConfigException;

// notice превратится в исключение
try {
    $value = $undefinedVariable; // E_NOTICE
} catch (ErrorException $e) {
    Yii::warning($e->getMessage(), \'app\');
}

// Невалидная конфигурация компонента
public function init()
{
    parent::init();
    if (empty($this->apiKey)) {
        throw new InvalidConfigException(\'PaymentGateway::$apiKey обязателен.\');
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.errors',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как вернуть кастомный HTTP-статус из контроллера в Yii2?',
                'answer' => 'Через **`Yii::$app->response->statusCode`** или метод **`setStatusCode($code, $text = null)`**.

**Способы:**

- **`Yii::$app->response->statusCode = 201;`** — самый простой.
- **`Yii::$app->response->setStatusCode(201, \'Created\');`** — со строкой статуса.
- **Бросить `HttpException`** — статус выставится автоматически (рекомендуется для ошибок 4xx/5xx).
- В REST-контроллере (`yii\\rest\\Controller`) — статус ставится автоматически по экшену: `create` → 201, `delete` → 204.

**Для редиректов** — `return $this->redirect($url, 302)` (статус вторым параметром).

**JSON-ответ с кастомным статусом** — установить `response->format = Response::FORMAT_JSON` и `statusCode`, вернуть массив.',
                'code_example' => 'use Yii;
use yii\\web\\Response;
use yii\\web\\NotFoundHttpException;

public function actionCreate()
{
    $model = new Post();
    if ($model->load(Yii::$app->request->post()) && $model->save()) {
        Yii::$app->response->statusCode = 201;
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [\'id\' => $model->id];
    }

    Yii::$app->response->setStatusCode(422, \'Validation Failed\');
    Yii::$app->response->format = Response::FORMAT_JSON;
    return [\'errors\' => $model->errors];
}

public function actionView($id)
{
    $post = Post::findOne($id);
    if ($post === null) {
        // Идиоматический способ — бросить исключение
        throw new NotFoundHttpException();
    }
    return $this->render(\'view\', [\'post\' => $post]);
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.errors',
            ],
        ];
    }
}
