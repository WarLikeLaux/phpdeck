<?php

namespace Database\Seeders\Data\Categories\Yii2;

class Requests
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Yii2',
                'question' => 'Как получить объект Request в Yii2?',
                'answer' => 'Через **компонент приложения**: `Yii::$app->request`.

- Это объект `yii\\web\\Request` (для веба).
- Даёт ООП-обёртку над `$_GET`, `$_POST`, `$_COOKIE`, `$_SERVER`, заголовками.
- Можно инжектить через DI: `public function actionForm(Request $request)`.
- Внутри контроллера доступен также через `$this->request`.',
                'code_example' => 'use Yii;

public function actionIndex()
{
    $request = Yii::$app->request;
    $page = $request->get(\'page\', 1);
    $isAjax = $request->isAjax;

    return $this->render(\'index\', compact(\'page\'));
}',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.requests',
            ],
            [
                'category' => 'Yii2',
                'question' => 'В чём разница между $request->get() и $request->post() в Yii2?',
                'answer' => 'Оба метода — **безопасные геттеры** с поддержкой default.

- `get($name, $default = null)` — читает из query-параметров (`$_GET`).
- `post($name, $default = null)` — читает из тела формы (`$_POST`).
- Без аргументов — возвращают **весь массив**.
- Преимущество перед `$_GET`/`$_POST` — встроенный default и единая точка входа (можно подменить в тестах).',
                'code_example' => '$request = Yii::$app->request;

// /search?q=php&page=2
$q = $request->get(\'q\');           // \'php\'
$page = $request->get(\'page\', 1);  // 2 или 1 если нет

// POST form
$email = $request->post(\'email\');
$all = $request->post();           // весь массив

// Все query сразу:
$allGet = $request->get();',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.requests',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как определить метод запроса (GET/POST/AJAX) в Yii2?',
                'answer' => 'У объекта `Request` есть **булевы геттеры**:

- `isGet`, `isPost`, `isPut`, `isPatch`, `isDelete`, `isHead`, `isOptions`.
- `isAjax` — проверяет заголовок `X-Requested-With: XMLHttpRequest`.
- `isPjax` — частный случай AJAX от Pjax-виджета.
- `method` — строка с именем метода (`\'GET\'`, `\'POST\'`).',
                'code_example' => '$request = Yii::$app->request;

if ($request->isPost) {
    // обработка формы
}

if ($request->isAjax) {
    return $this->asJson([\'ok\' => true]);
}

echo $request->method; // \'POST\'',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.requests',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как читать заголовки и тело запроса в Yii2?',
                'answer' => 'У `Request` есть свойства для разных частей запроса.

- `$request->headers->get(\'User-Agent\')` — заголовок (через `HeaderCollection`).
- `$request->bodyParams` — распарсенное тело (POST + JSON, если включён парсер).
- `$request->rawBody` — **сырое** тело строкой.
- `$request->userIP` — IP клиента (учитывает `trustedHosts`).
- `$request->userAgent` — UA-строка.',
                'code_example' => '$request = Yii::$app->request;

$ua = $request->headers->get(\'User-Agent\');
$auth = $request->headers->get(\'Authorization\');

$data = $request->bodyParams;   // массив
$raw = $request->rawBody;       // строка JSON, например

$ip = $request->userIP;',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.requests',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как принимать JSON-тело в Yii2 (REST endpoint)?',
                'answer' => 'Нужно включить **JSON parser** в конфиге `Request`.

- В `components.request.parsers` указать `application/json => yii\\web\\JsonParser`.
- После этого `$request->bodyParams` содержит распарсенный JSON.
- Без парсера придётся читать `rawBody` и звать `json_decode` вручную.',
                'code_example' => '// config/web.php
\'components\' => [
    \'request\' => [
        \'parsers\' => [
            \'application/json\' => \'yii\\web\\JsonParser\',
        ],
    ],
],

// Controller
public function actionCreate()
{
    $data = Yii::$app->request->bodyParams;
    $user = new User();
    $user->load($data, \'\');
    return $this->asJson([\'ok\' => $user->save()]);
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.requests',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как работать с куками в Yii2 безопасно?',
                'answer' => 'Через коллекции `request->cookies` и `response->cookies` — **не через `$_COOKIE`**.

- Yii2 при отправке **подписывает куки** ключом `cookieValidationKey`, при чтении проверяет подпись.
- `$_COOKIE` напрямую читать **нельзя** — подпись помешает.
- Кука создаётся как объект `yii\\web\\Cookie` с `name`, `value`, `expire`, `httpOnly`, `secure`.',
                'code_example' => '// Чтение
$lang = Yii::$app->request->cookies->getValue(\'lang\', \'ru\');

// Запись
Yii::$app->response->cookies->add(new \\yii\\web\\Cookie([
    \'name\' => \'lang\',
    \'value\' => \'en\',
    \'expire\' => time() + 86400 * 30,
    \'httpOnly\' => true,
]));',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.requests',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое CSRF-токен в Yii2 и как он работает?',
                'answer' => 'CSRF (`Cross-Site Request Forgery`) — атака, которую Yii2 блокирует через **двойной токен**.

- В сессии/куке хранится секрет, на каждый запрос генерится привязанный токен.
- При POST/PUT/DELETE Yii2 проверяет токен из поля `_csrf` или заголовка `X-CSRF-Token`.
- `ActiveForm` и `Html::beginForm` **автоматически** добавляют скрытое поле.
- Для AJAX токен берётся из `Yii::$app->request->csrfToken` (или мета-тега `csrf-token`).
- Отключить можно через `public $enableCsrfValidation = false;` в контроллере (для API).',
                'code_example' => '// В layout
<?= \\yii\\helpers\\Html::csrfMetaTags() ?>

// В JS
fetch(\'/api/save\', {
    method: \'POST\',
    headers: {
        \'X-CSRF-Token\': document.querySelector(\'meta[name=csrf-token]\').content,
    },
    body: JSON.stringify({title: \'Hi\'}),
});

// В контроллере, если API:
public $enableCsrfValidation = false;',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.requests',
            ],
        ];
    }
}
