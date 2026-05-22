<?php

namespace Database\Seeders\Data\Categories\Yii2;

class Misc
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Yii2',
                'question' => 'Что такое yii\\helpers\\ArrayHelper?',
                'answer' => '**`ArrayHelper`** — встроенный helper для работы с массивами и коллекциями объектов.

**Часто используемые методы:**

- **`getValue($obj, $key, $default = null)`** — безопасное чтение значения по ключу или dot-пути (`\'address.city\'`). Не падает при отсутствии.
- **`map($array, $from, $to)`** — построить ассоциативный массив (например, для `dropdownList`).
- **`index($array, $key)`** — переиндексировать массив объектов по полю.
- **`merge($a, $b, ...)`** — глубокое слияние массивов (умнее, чем `array_merge`).
- **`toArray($obj)`** — превратить объект (модель) в массив.

Универсально работает и с массивами, и с объектами (`ActiveRecord`, `BaseObject`).',
                'code_example' => '<?php
use yii\\helpers\\ArrayHelper;

$users = User::find()->all();

// map — для dropdownList
$options = ArrayHelper::map($users, \'id\', \'username\');
// [1 => \'admin\', 2 => \'guest\', ...]

// index — массив по id
$byId = ArrayHelper::index($users, \'id\');
echo $byId[5]->username;

// getValue — безопасное чтение
$city = ArrayHelper::getValue($user, \'address.city\', \'неизвестно\');

// merge — глубокое слияние конфигов
$config = ArrayHelper::merge(
    require \'common/main.php\',
    require \'frontend/main.php\'
);',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.misc',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что делает yii\\helpers\\Url::to?',
                'answer' => '**`Url::to($route)`** — строит URL по описанию маршрута, учитывая правила **`urlManager`**.

**Форматы аргумента:**

- **Строка-алиас**: `\'@web/images/logo.png\'` → подставит реальный URL.
- **Строка-маршрут**: `\'site/about\'` → `/site/about`.
- **Массив**: `[\'site/view\', \'id\' => 5, \'lang\' => \'ru\']` → URL с параметрами.
- **Абсолютный URL** (`http://...`) — вернётся как есть.

**Параметры:**

- Второй аргумент **`$scheme`**: `true` — абсолютный URL с текущей схемой, `\'https\'` — с принудительной.

**Парный метод:** **`Url::toRoute()`** — только массивы/строки-маршруты (не алиасы).',
                'code_example' => '<?php
use yii\\helpers\\Url;

Url::to([\'site/index\']);
// /index.php?r=site/index — или /site если pretty urls

Url::to([\'post/view\', \'id\' => 42]);
// /post/view?id=42

Url::to(\'@web/css/style.css\');
// /css/style.css

Url::to([\'site/about\'], true);
// https://example.com/site/about

Url::current();        // текущий URL
Url::home();           // главная
Url::previous();       // предыдущий (из сессии)',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.misc',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое yii\\helpers\\Html?',
                'answer' => '**`yii\\helpers\\Html`** — helper для **безопасной** генерации HTML.

**Часто используемые методы:**

- **`encode($s)`** — экранирование (`htmlspecialchars`), защита от XSS.
- **`a($text, $url, $options = [])`** — ссылка `<a>`.
- **`tag($name, $content, $options)`** — произвольный тег.
- **`img($src, $options)`** — изображение.
- **`submitButton($label, $options)`** — кнопка submit.
- **`beginForm($action, $method)`** / **`endForm()`** — форма с CSRF-токеном внутри.

**Важно:**

- `Html::encode()` — must-have при выводе пользовательских данных в Twig/PHP-шаблонах Yii2 (там по умолчанию **не экранируется**).',
                'code_example' => '<?php
use yii\\helpers\\Html;

// Безопасный вывод
echo Html::encode($user->bio);
// преобразует <, >, & и кавычки

// Ссылка
echo Html::a(\'Перейти\', [\'site/view\', \'id\' => 1], [\'class\' => \'btn\']);
// <a class="btn" href="/site/view?id=1">Перейти</a>

// Форма
echo Html::beginForm([\'site/login\'], \'post\');
echo Html::input(\'text\', \'username\');
echo Html::submitButton(\'Войти\');
echo Html::endForm();

// Тег
echo Html::tag(\'span\', \'Привет\', [\'class\' => \'badge\']);',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.misc',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое yii\\helpers\\Json и yii\\helpers\\FileHelper?',
                'answer' => 'Два часто используемых helper-класса Yii2.

**`yii\\helpers\\Json`** — обёртка над `json_encode` / `json_decode` с обработкой ошибок:

- **`Json::encode($data)`** — кодирует, кидает **`InvalidArgumentException`** при ошибке.
- **`Json::decode($str, $asArray = true)`** — декодирует в массив (по умолчанию).
- **`Json::htmlEncode($data)`** — для безопасной вставки в `<script>`.

**`yii\\helpers\\FileHelper`** — работа с файлами/каталогами:

- **`createDirectory($path, $mode = 0775)`** — создать рекурсивно.
- **`copyDirectory($src, $dst)`** — копировать.
- **`removeDirectory($path)`** — удалить рекурсивно.
- **`findFiles($dir, $options)`** — поиск с фильтрами по маске.
- **`normalizePath($path)`** — привести `..`, `/`, `\\` к каноничному виду.',
                'code_example' => '<?php
use yii\\helpers\\Json;
use yii\\helpers\\FileHelper;

// JSON
$data = Json::decode(\'{"a":1,"b":2}\');
echo Json::encode([\'a\' => 1]); // {"a":1}

try {
    Json::decode(\'{ broken\');
} catch (\\yii\\base\\InvalidArgumentException $e) {
    echo $e->getMessage();
}

// FileHelper
FileHelper::createDirectory(\'@runtime/exports\');

$files = FileHelper::findFiles(\'@app/uploads\', [
    \'only\' => [\'*.jpg\', \'*.png\'],
    \'recursive\' => true,
]);

FileHelper::removeDirectory(\'@runtime/cache\');',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.misc',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Когда выбирать basic, а когда advanced шаблон Yii2?',
                'answer' => 'Краткое сравнение для решения «с чего стартовать».

| Сценарий | Шаблон |
| --- | --- |
| Сайт-визитка, лендинг | **basic** |
| REST API без админки | **basic** |
| Прототип, MVP | **basic** |
| Учебный проект | **basic** |
| Сайт + админка с разным URL и сессией | **advanced** |
| Несколько фронтенд-приложений (web + api + cli) | **advanced** |
| Общие модели и сервисы между приложениями | **advanced** |
| Сложные окружения (dev/staging/prod через init) | **advanced** |

**Правило большого пальца:**

- Начинать с **basic** — структура понятнее, проще освоить.
- Переезжать на **advanced** только когда **реально нужны** несколько приложений с общим кодом.
- Хороший компромисс: basic + модули (`modules/admin`) — даёт админку без перехода на advanced.',
                'code_example' => '# basic — одно приложение
composer create-project --prefer-dist yiisoft/yii2-app-basic myapp

# Структура:
# config/  controllers/  models/  views/  web/

# advanced — три приложения
composer create-project --prefer-dist yiisoft/yii2-app-advanced myapp
php init

# Структура:
# common/    — общий код
# frontend/  — публичный сайт
# backend/   — админка
# console/   — CLI

# basic + админ-модуль (промежуточный вариант)
# modules/admin/
#   controllers/
#   views/
#   Module.php',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'yii2.misc',
            ],
        ];
    }
}
