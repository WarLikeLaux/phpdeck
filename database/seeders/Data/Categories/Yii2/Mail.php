<?php

namespace Database\Seeders\Data\Categories\Yii2;

class Mail
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Yii2',
                'question' => 'Как отправить email в Yii2?',
                'answer' => 'Через компонент **`mailer`** (`Yii::$app->mailer`). API единый независимо от драйвера.

**Цепочка вызовов:**

- **`compose($view, $params)`** — создать сообщение из view-шаблона.
- **`setFrom($email)`** / **`setTo($email)`** / **`setSubject($s)`**.
- **`setTextBody($t)`** / **`setHtmlBody($h)`** — если без view.
- **`attach($path)`** — вложение.
- **`send()`** — отправить, возвращает `true`/`false`.

**Драйверы:**

- **`yii\\swiftmailer\\Mailer`** — из пакета `yiisoft/yii2-swiftmailer` (исторический выбор; SwiftMailer больше не поддерживается).
- **`yii\\symfonymailer\\Mailer`** — современный, из пакета `yiisoft/yii2-symfonymailer` (**рекомендуется** для новых проектов).

API почти одинаковый — миграция сводится к смене класса в конфиге.',
                'code_example' => 'use Yii;

Yii::$app->mailer->compose(\'welcome\', [\'user\' => $user])
    ->setFrom([\'noreply@example.com\' => \'My Site\'])
    ->setTo($user->email)
    ->setSubject(\'Добро пожаловать!\')
    ->send();

// Без view — простой текст
Yii::$app->mailer->compose()
    ->setFrom(\'noreply@example.com\')
    ->setTo(\'admin@example.com\')
    ->setSubject(\'Alert\')
    ->setTextBody(\'Сервер упал\')
    ->send();

// С вложением
Yii::$app->mailer->compose(\'invoice\', [\'order\' => $order])
    ->setTo($order->email)
    ->setSubject(\'Счёт #\' . $order->id)
    ->attach(Yii::getAlias(\'@runtime/invoice.pdf\'))
    ->send();',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.mail',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как сделать HTML + text версии письма в Yii2?',
                'answer' => 'В **`compose()`** передать **массив** с двумя ключами — `html` и `text`. Yii соберёт письмо с обеими версиями (`multipart/alternative`).

**Зачем text-версия:**

- Для **спам-фильтров** — письма только с HTML чаще попадают в спам.
- Для **почтовых клиентов**, которые не показывают HTML.
- Для **accessibility** (screen readers, plain text users).

**Поиск шаблонов:**

- По умолчанию ищутся в **`@app/mail/`** (настраивается через `viewPath`).
- Если файлов с `_text` нет — Yii **не** генерирует text автоматически.
- Шаблоны — обычные view-файлы (можно использовать `$this->layout`).

**Layout для писем:** настраивается через `htmlLayout` / `textLayout` в компоненте mailer. По умолчанию `@app/mail/layouts/html.php` и `@app/mail/layouts/text.php`.',
                'code_example' => '// Отправка с двумя версиями
Yii::$app->mailer->compose([
    \'html\' => \'welcome-html\',  // mail/welcome-html.php
    \'text\' => \'welcome-text\',  // mail/welcome-text.php
], [\'user\' => $user])
    ->setFrom(\'noreply@example.com\')
    ->setTo($user->email)
    ->setSubject(\'Добро пожаловать\')
    ->send();

// mail/welcome-html.php
/* @var $user app\\models\\User */
?>
<h1>Здравствуйте, <?= \\yii\\helpers\\Html::encode($user->name) ?>!</h1>
<p>Спасибо за регистрацию.</p>

// mail/welcome-text.php
echo "Здравствуйте, {$user->name}!\\n\\nСпасибо за регистрацию.\\n";',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.mail',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое `useFileTransport` в Yii2 mailer?',
                'answer' => '**`useFileTransport`** — флаг на компоненте `mailer`. Когда `true`, письма **не отправляются**, а сохраняются как **`.eml`-файлы** в `runtime/mail/`.

**Зачем нужно:**

- **Локальная разработка** — не нужен SMTP-сервер, видно полное содержимое письма.
- **Тестирование шаблонов** — открыть `.eml` в Thunderbird/Apple Mail и проверить вёрстку.
- **CI/integration-тесты** — проверить, что письмо отправилось с нужным содержимым (через mock или чтение файла).

**Где задавать:** в `config/web.php` → `components.mailer.useFileTransport`. Часто оборачивают в `YII_ENV_DEV` или `YII_ENV_TEST`.

**В prod должно быть `false`** — иначе письма не дойдут до пользователей.

**Файлы** именуются по timestamp, например `1716394800-3a7c.eml`. После просмотра — удалять руками или чистить `runtime/mail/`.',
                'code_example' => '// config/web.php
\'components\' => [
    \'mailer\' => [
        \'class\' => \'yii\\symfonymailer\\Mailer\',
        \'viewPath\' => \'@app/mail\',
        // Только в dev: не слать реально, а сохранять в runtime/mail/
        \'useFileTransport\' => YII_ENV_DEV,
        \'transport\' => [
            \'scheme\' => \'smtp\',
            \'host\' => \'smtp.example.com\',
            \'port\' => 587,
            \'username\' => \'user\',
            \'password\' => \'secret\',
        ],
    ],
],

// После отправки в dev:
// ls runtime/mail/
// 1716394800-3a7c.eml',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.mail',
            ],
            [
                'category' => 'Yii2',
                'question' => 'В чём разница между SwiftMailer и Symfony Mailer в Yii2?',
                'answer' => 'Два **разных пакета** интеграции почты в Yii2.

| Признак | `yii2-swiftmailer` | `yii2-symfonymailer` |
| --- | --- | --- |
| Основа | **SwiftMailer** | **Symfony Mailer** |
| Статус ядра | **EOL с 2021** | **активно поддерживается** |
| Класс | `yii\\swiftmailer\\Mailer` | `yii\\symfonymailer\\Mailer` |
| Конфиг transport | `transport.class = \'Swift_SmtpTransport\'` | `transport.scheme = \'smtp\'` + `host`/`port` |
| DSN | нет | поддержка `smtp://user:pass@host:port` |
| PHP-требования | PHP 5.4+ | **PHP 7.2+** |

**Рекомендация:**

- **Новые проекты** — `yiisoft/yii2-symfonymailer`.
- **Старые проекты** — миграция обычно сводится к смене класса и формата `transport` в конфиге, остальной код (`compose()->setTo()->send()`) **не меняется**.',
                'code_example' => '// SwiftMailer (устаревший)
\'mailer\' => [
    \'class\' => \'yii\\swiftmailer\\Mailer\',
    \'transport\' => [
        \'class\' => \'Swift_SmtpTransport\',
        \'host\' => \'smtp.gmail.com\',
        \'username\' => \'user\',
        \'password\' => \'pass\',
        \'port\' => 587,
        \'encryption\' => \'tls\',
    ],
],

// Symfony Mailer (рекомендуется)
\'mailer\' => [
    \'class\' => \'yii\\symfonymailer\\Mailer\',
    \'transport\' => [
        \'scheme\' => \'smtps\',
        \'host\' => \'smtp.gmail.com\',
        \'username\' => \'user\',
        \'password\' => \'pass\',
        \'port\' => 465,
    ],
],

// Или через DSN
\'mailer\' => [
    \'class\' => \'yii\\symfonymailer\\Mailer\',
    \'transport\' => \'smtps://user:pass@smtp.gmail.com:465\',
],',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.mail',
            ],
        ];
    }
}
