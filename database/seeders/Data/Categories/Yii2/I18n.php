<?php

namespace Database\Seeders\Data\Categories\Yii2;

class I18n
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Yii2',
                'question' => 'Как переводить строки в Yii2 через `Yii::t`?',
                'answer' => '**`Yii::t($category, $message, $params = [], $language = null)`** — основной метод перевода в Yii2.

- **`$category`** — имя «коробки сообщений» (например, `\'app\'`, `\'app/errors\'`). Используется для маршрутизации к нужному `MessageSource`.
- **`$message`** — исходный текст (обычно на **`sourceLanguage`**, по умолчанию **`en-US`**).
- **`$params`** — массив параметров для подстановки `{name}` в строку.
- **`$language`** — целевой язык; по умолчанию берётся **`Yii::$app->language`**.

Если перевода нет — возвращается **исходный текст** (с подстановкой параметров).

**Где использовать:**

- Во view: `<?= Yii::t(\'app\', \'Welcome\') ?>`.
- В контроллерах, моделях (`attributeLabels`, `rules` messages).
- В клиентских сообщениях валидации.',
                'code_example' => 'use Yii;

echo Yii::t(\'app\', \'Welcome to our site!\');

// С параметрами
echo Yii::t(\'app\', \'Hello, {name}!\', [\'name\' => $user->name]);

// Множественные формы (ICU plurals)
echo Yii::t(\'app\', \'{n, plural, =0{нет постов} =1{# пост} other{# постов}}\', [\'n\' => $count]);

// Принудительно другой язык
echo Yii::t(\'app\', \'Welcome\', [], \'ru-RU\');

// В модели
public function attributeLabels()
{
    return [
        \'name\' => Yii::t(\'app\', \'Имя\'),
        \'email\' => Yii::t(\'app\', \'Электронная почта\'),
    ];
}',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.i18n',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Какие message sources есть в Yii2?',
                'answer' => '**Message source** — откуда `Yii::t` берёт переводы. Настраивается в компоненте **`i18n.translations`**.

| Класс | Хранилище | Когда выбирать |
| --- | --- | --- |
| **`yii\\i18n\\PhpMessageSource`** | PHP-файлы `messages/<lang>/<category>.php` | **дефолт**, простой проект |
| **`yii\\i18n\\GettextMessageSource`** | `.po` / `.mo` файлы | интеграция с gettext-тулами (Poedit) |
| **`yii\\i18n\\DbMessageSource`** | таблицы `source_message` + `message` | переводы редактируют в админке |

**`PhpMessageSource`-структура:**

- **`basePath`** — корень переводов (`@app/messages`).
- **`fileMap`** — маппинг категории → файла (опционально).
- Сам файл — массив `[\'Original\' => \'Перевод\', ...]`.

**Команда `./yii message/extract`** автоматически собирает все вызовы `Yii::t` и обновляет файлы переводов.',
                'code_example' => '// config/web.php
return [
    \'components\' => [
        \'i18n\' => [
            \'translations\' => [
                \'app*\' => [
                    \'class\' => \'yii\\i18n\\PhpMessageSource\',
                    \'basePath\' => \'@app/messages\',
                    \'sourceLanguage\' => \'en-US\',
                    \'fileMap\' => [
                        \'app\' => \'app.php\',
                        \'app/errors\' => \'errors.php\',
                    ],
                ],
            ],
        ],
    ],
];

// messages/ru-RU/app.php
return [
    \'Welcome to our site!\' => \'Добро пожаловать!\',
    \'Hello, {name}!\' => \'Привет, {name}!\',
];

// Запустить сбор строк:
// ./yii message/extract @app/config/i18n.php',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.i18n',
            ],
            [
                'category' => 'Yii2',
                'question' => 'В чём разница между `sourceLanguage` и `language` в Yii2?',
                'answer' => 'Это **два разных параметра** на уровне приложения и каждого `MessageSource`.

| Параметр | Где | Что означает |
| --- | --- | --- |
| **`language`** | `Yii::$app->language` | **Целевой** язык — на который переводим |
| **`sourceLanguage`** | `Yii::$app->sourceLanguage` + у каждого `MessageSource` | **Исходный** язык — на котором написаны строки в коде |

**Правило:**

- Если `language === sourceLanguage` — `Yii::t` возвращает строку **как есть**, без обращения к message source (быстрая ветка).
- Если разные — идёт лукап в `MessageSource`. Нет перевода → возвращается исходник.

**Типичная схема:**

- В коде писать на английском (`Yii::t(\'app\', \'Hello\')`), `sourceLanguage = \'en-US\'`.
- Менять `Yii::$app->language` в зависимости от пользователя (cookie, URL, заголовок `Accept-Language`).

**Альтернатива:** некоторые команды пишут русский напрямую в коде → ставят `sourceLanguage = \'ru-RU\'`. Это допустимо, но усложняет интеграцию с переводческими сервисами.',
                'code_example' => '// config/web.php
return [
    \'sourceLanguage\' => \'en-US\', // строки в коде на английском
    \'language\' => \'ru-RU\',       // показываем русский

    \'components\' => [
        \'i18n\' => [
            \'translations\' => [
                \'app*\' => [
                    \'class\' => \'yii\\i18n\\PhpMessageSource\',
                    \'sourceLanguage\' => \'en-US\',
                    \'basePath\' => \'@app/messages\',
                ],
            ],
        ],
    ],
];

// Переключение языка по запросу — например, из middleware
Yii::$app->language = Yii::$app->request->cookies->getValue(\'lang\', \'ru-RU\');',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.i18n',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как форматировать даты, валюту и числа в Yii2?',
                'answer' => 'Через **компонент `formatter`** (`Yii::$app->formatter` или просто `Yii::$app->formatter->asXxx`). Под капотом — PHP-расширение **`intl`**.

**Основные методы:**

- **`asDate($value, $format)`** — дата (`\'short\'`, `\'medium\'`, `\'long\'`, `\'full\'` или ICU-паттерн).
- **`asTime($value, $format)`** / **`asDatetime`**.
- **`asRelativeTime($value)`** — «5 минут назад».
- **`asCurrency($value, $currency = null)`** — валюта (`100 → "100,00 ₽"`).
- **`asDecimal($value, $decimals = null)`** — число с разделителями.
- **`asPercent($value, $decimals = null)`** — процент.
- **`asSize`** / **`asShortSize`** — байты (`1024 → "1 KB"`).

**Настройка** через компонент: `locale`, `timeZone`, `dateFormat`, `currencyCode`, `decimalSeparator`, `thousandSeparator`.',
                'code_example' => '// config/web.php
\'components\' => [
    \'formatter\' => [
        \'locale\' => \'ru-RU\',
        \'timeZone\' => \'Europe/Moscow\',
        \'dateFormat\' => \'dd.MM.yyyy\',
        \'currencyCode\' => \'RUB\',
        \'thousandSeparator\' => \' \',
        \'decimalSeparator\' => \',\',
    ],
],

// Использование
$f = Yii::$app->formatter;

echo $f->asDate(time(), \'long\');                 // 22 мая 2026 г.
echo $f->asDatetime($post->created_at);           // 22.05.2026, 14:30
echo $f->asRelativeTime($post->created_at);       // 3 часа назад
echo $f->asCurrency(1500.50);                     // 1 500,50 ₽
echo $f->asDecimal(1500000.5);                    // 1 500 000,5
echo $f->asPercent(0.25);                         // 25 %
echo $f->asShortSize(2097152);                    // 2 MB',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.i18n',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое параметризованные сообщения и ICU plurals в Yii2?',
                'answer' => 'Yii2 умеет **подставлять параметры** в переводимые строки двумя способами:

**1) Простая подстановка** через `{name}`:

`Yii::t(\'app\', \'Hello, {name}!\', [\'name\' => $user->name])`

**2) ICU MessageFormat** (нужен PHP-расширение **`intl`**) — поддерживает множественные формы, селекторы, форматы:

| Формат | Пример | Что делает |
| --- | --- | --- |
| **`plural`** | `{n, plural, one{# пост} few{# поста} other{# постов}}` | склонение по числу |
| **`select`** | `{gender, select, male{он} female{она} other{оно}}` | выбор по строке |
| **`number`** | `{price, number, currency}` | формат числа |
| **`date`** | `{d, date, short}` | формат даты |
| **`time`** | `{t, time, medium}` | формат времени |

**`#`** в plural — заменяется на само число.

**Категории plural для русского:** `one` (1, 21, 31), `few` (2-4, 22-24), `many` (5-20), `other`.',
                'code_example' => 'use Yii;

// Простая подстановка
echo Yii::t(\'app\', \'Hello, {name}!\', [\'name\' => \'Иван\']);
// → Hello, Иван!

// ICU plural (русский)
echo Yii::t(\'app\',
    \'{n, plural, one{# пост} few{# поста} many{# постов} other{# постов}}\',
    [\'n\' => 5]
);
// → 5 постов

// ICU select (по полу)
echo Yii::t(\'app\',
    \'{gender, select, male{Он зарегистрирован} female{Она зарегистрирована} other{Зарегистрирован}}\',
    [\'gender\' => $user->gender]
);

// ICU number + date
echo Yii::t(\'app\',
    \'Заказ {id} на сумму {amount, number, currency} оформлен {created, date, long}.\',
    [\'id\' => 42, \'amount\' => 1500.5, \'created\' => time()]
);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.i18n',
            ],
        ];
    }
}
