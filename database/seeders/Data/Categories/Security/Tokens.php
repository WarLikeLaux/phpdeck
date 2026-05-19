<?php

namespace Database\Seeders\Data\Categories\Security;

class Tokens
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Безопасность',
                'question' => 'Что такое токен простыми словами?',
                'answer' => 'Длинная случайная (или подписанная) строка, которая идентифицирует клиента или сессию, чтобы не пересылать логин и пароль на каждом запросе. Поток обычно такой: клиент один раз входит логином+паролем, сервер в ответ выдаёт токен, клиент сохраняет его и подкладывает в каждый последующий запрос в заголовке Authorization: Bearer .... Сервер по токену понимает, кто это, и решает, что разрешить. Примеры токенов: JWT (самодостаточный, подпись внутри), API-ключ Stripe (sk_live_...), OAuth access_token, refresh_token (для получения нового access), CSRF-токен (защита форм). Главное правило: к токену относись как к паролю — кто его украл, тот вошёл в аккаунт. Поэтому только HTTPS, HttpOnly+Secure для куки, не клади в URL и не светись в логах.',
                'code_example' => "# 1. Логин — отдаём токен в обмен на пароль
POST /api/login
{ \"email\": \"a@b.test\", \"password\": \"secret\" }
→ 200 { \"token\": \"1|aBcD3fGh5jKlMnOpQ...\" }

# 2. Дальше каждый запрос — с токеном, пароль больше не шлём
GET /api/me
Authorization: Bearer 1|aBcD3fGh5jKlMnOpQ...
→ 200 { \"id\": 42, \"email\": \"a@b.test\" }

# 3. Logout — просим сервер забыть/инвалидировать токен
POST /api/logout
Authorization: Bearer 1|aBcD3fGh5jKlMnOpQ...
→ 204",
                'code_language' => 'http',
                'difficulty' => 1,
                'topic' => 'security.tokens',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое JWT и из чего он состоит?',
                'answer' => 'JSON Web Token (RFC 7519) — самодостаточный токен из трёх base64url-частей, разделённых точками: header.payload.signature. Header описывает алгоритм (alg) и тип. Payload — JSON с claims: стандартными (iss — кто выдал, sub — subject/user id, exp — истекает, iat — выдан в, aud — для кого, jti — id токена) и кастомными (role, email). Signature — HMAC или RSA-подпись по первым двум частям с секретом. Сервер при получении проверяет подпись и срок, и доверяет payload. ВАЖНО: payload только закодирован base64, НЕ зашифрован — содержимое читает любой.',
                'code_example' => "// Пример JWT — три base64url-части через точку
eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiI0MiIsInJvbGUiOiJ1c2VyIiwiaWF0IjoxNzE2MDAwMDAwLCJleHAiOjE3MTYwMDM2MDB9.SflKxw...

// Декодируется так:
header  = {\"alg\":\"HS256\",\"typ\":\"JWT\"}
payload = {\"sub\":\"42\",\"role\":\"user\",\"iat\":1716000000,\"exp\":1716003600}
sig     = HMAC-SHA256(base64url(header) + \".\" + base64url(payload), secret)",
                'code_language' => 'http',
                'difficulty' => 2,
                'topic' => 'security.tokens',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Чем JWT отличается от сессии простыми словами?',
                'answer' => 'Сессия (stateful): сервер хранит данные (user_id, корзину, права) в Redis/БД, клиенту отдаёт только короткий session_id в куке. JWT (stateless): данные ВНУТРИ токена в виде подписанного JSON, сервер ничего не хранит — на каждом запросе проверяет только подпись и срок. Плюсы JWT: легко масштабировать (нет общего стора между нодами), удобно для микросервисов и мобильных приложений. Минусы: нельзя мгновенно отозвать (валиден до exp без чёрного списка), токен больше по размеру (сотни байт против 32-байтного session_id), любое изменение данных юзера требует переиздания токена. Правило: монолит + браузер — обычно сессия проще; распределённый API + мобилка — JWT.',
                'difficulty' => 2,
                'topic' => 'security.tokens',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое access token и refresh token простыми словами?',
                'answer' => 'Пара токенов с разной ролью. access_token — короткоживущий (5-60 минут), которым клиент авторизует КАЖДЫЙ запрос к API в заголовке Authorization: Bearer .... refresh_token — долгоживущий (дни-месяцы), не показывается API, нужен только чтобы получить новый access_token, когда старый истёк. Логика: украденный access протухнет за минуты, а refresh лежит в защищённом месте (HttpOnly+Secure-кука в браузере, Keychain/Keystore в мобильном) и редко уходит по сети. На сервере refresh обычно хранится с привязкой к user_id, есть возможность revoke (logout = удалить запись).',
                'code_example' => "# 1. Запрос на API с просроченным access — 401
GET /api/me
Authorization: Bearer eyJ...expired
→ 401 Unauthorized

# 2. Клиент молча обменивает refresh на новый access
POST /auth/refresh
Cookie: refresh_token=long-random-string
→ 200 { \"access_token\": \"eyJ...new\", \"expires_in\": 900 }

# 3. Повторяем оригинальный запрос с новым access
GET /api/me
Authorization: Bearer eyJ...new
→ 200 { \"id\": 42, \"email\": \"...\" }",
                'code_language' => 'http',
                'difficulty' => 2,
                'topic' => 'security.tokens',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Почему JWT нельзя отозвать сразу и как обходят это ограничение?',
                'answer' => 'Сервер JWT не хранит — он только проверяет подпись и exp. Поэтому украденный токен работает до своего exp независимо от того, что юзер «вышел» или сменил пароль. «Logout» обычно просто удаляет токен на клиенте, но украденная копия остаётся валидной. Стандартные подходы: 1) Короткий exp (5-15 минут) + refresh token — узкое окно для атакующего. 2) Blacklist по jti (id токена) в Redis с TTL = оставшийся срок жизни — теряется stateless, но Redis-лукап на запрос дёшев. 3) token_version (или iat-cutoff) в БД на пользователя — при logout/смене пароля поднимаем версию, payload должен содержать ту же версию, иначе 401. 4) Refresh rotation: каждый рефреш выдаёт НОВЫЙ refresh_token и инвалидирует старый, повторное использование старого = сигнал кражи, выкидываем всю семью токенов.',
                'code_example' => "<?php
// Подход с token_version — stateless для access, инвалидация одним UPDATE
// users: token_version INT default 1
// access token payload: {sub:42, ver:1, exp:...}

public function verifyJwt(array \$payload): User {
    \$user = User::findOrFail(\$payload['sub']);
    if ((\$payload['ver'] ?? 0) !== \$user->token_version) {
        abort(401, 'token revoked');
    }
    return \$user;
}

// Logout «со всех устройств» / смена пароля
public function logoutEverywhere(User \$user): void {
    \$user->increment('token_version'); // все старые JWT мгновенно мертвы
}",
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'security.tokens',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что НЕЛЬЗЯ класть в JWT простыми словами?',
                'answer' => 'Payload JWT — это base64url, а НЕ шифрование. Любой клиент или прохожий с токеном открывает jwt.io и читает содержимое — подпись лишь защищает от ПОДДЕЛКИ, а не от чтения. Поэтому в JWT нельзя класть: пароли (даже хеши), API-ключи и client_secret сторонних сервисов, номера карт, паспортные/медицинские ПДн, внутренние секреты. Можно класть идентификаторы и роли: sub (user id), role, имя, exp, iat. Если очень нужно прятать содержимое — используй JWE (JSON Web Encryption), не обычный JWS.',
                'code_example' => "// Декодирование payload без секрета — доступно ВСЕМ
\$jwt = 'eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiI0MiIsImNjIjoiNDExMS0xMTExLTExMTEtMTExMSJ9.sig';
\$parts = explode('.', \$jwt);
\$payload = json_decode(base64_decode(strtr(\$parts[1], '-_', '+/')), true);
print_r(\$payload);
// Array ( [sub] => 42 [cc] => 4111-1111-1111-1111 )  ← КАРТА УТЕКЛА",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'security.tokens',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое CSRF-токен простыми словами?',
                'answer' => 'Случайная строка, которую сервер генерирует на сессию и подкладывает в каждую форму как скрытое поле. При POST/PUT/DELETE сервер сравнивает токен из тела/заголовка с тем, что в сессии — через hash_equals для constant-time. Атакующий сайт может заставить браузер юзера отправить запрос (куки прицепятся автоматически), но НЕ может прочитать или угадать токен из-за Same-Origin Policy → запрос отклоняется. В Laravel — @csrf в Blade-форме добавляет input автоматически, middleware VerifyCsrfToken проверяет на всех state-changing запросах. Современная дополнительная защита — cookie SameSite=Lax/Strict.',
                'code_example' => "<!-- Blade — токен ставится автоматически -->
<form method=\"POST\" action=\"/post\">
    @csrf  {{-- разворачивается в: --}}
    {{-- <input type=\"hidden\" name=\"_token\" value=\"...\"> --}}
</form>

<!-- Для AJAX/SPA — токен в meta + header -->
<meta name=\"csrf-token\" content=\"{{ csrf_token() }}\">
<script>
fetch('/post', {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
});
</script>",
                'code_language' => 'html',
                'difficulty' => 2,
                'topic' => 'security.tokens',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Где хранить JWT на клиенте: localStorage или cookie?',
                'answer' => 'Два варианта, каждый с разным профилем рисков. localStorage / sessionStorage — удобно для SPA, токен сам цепляется в Authorization-заголовок, но JS его читает напрямую: любой XSS (вредный npm-пакет, чужой script на странице) угоняет токен мгновенно. Cookie с флагами HttpOnly + Secure + SameSite=Lax/Strict — JS прочитать не может, XSS токен не вытащит, но появляется риск CSRF (браузер сам шлёт куку с любого сайта), решается CSRF-токеном или SameSite=Strict. Современная рекомендация для веба: refresh_token — в HttpOnly+Secure+SameSite=Strict куку с path=/auth/refresh, access_token — в памяти JS-приложения (переменная/closure, не localStorage), живёт 5-15 минут, при перезагрузке страницы тихо обновляется через refresh. Никогда не клади токены в URL — попадут в access_log, history браузера и Referer-заголовок. Для мобильных — Keychain (iOS) / EncryptedSharedPreferences (Android), не AsyncStorage в открытую.',
                'code_example' => "// Refresh — HttpOnly cookie, JS не достанет
Set-Cookie: refresh=eyJ...; HttpOnly; Secure; SameSite=Strict; Path=/auth/refresh; Max-Age=2592000

// Access — в памяти SPA, не в localStorage
// app.js
let accessToken = null; // closure, нет в DevTools → Application

async function refresh() {
  const r = await fetch('/auth/refresh', { method: 'POST', credentials: 'include' });
  accessToken = (await r.json()).access_token;
}

fetch('/api/me', { headers: { Authorization: 'Bearer ' + accessToken } });",
                'code_language' => 'http',
                'difficulty' => 3,
                'topic' => 'security.tokens',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Почему JWT с alg=none и алгоритм-confusion — опасные атаки?',
                'answer' => 'В RFC 7519 разрешено значение alg=none — «токен без подписи». Старые/наивные библиотеки сначала читали alg из header, а потом по этому полю выбирали способ верификации: видят none — пропускают проверку, считают подпись валидной. Атакующий просто кладёт нужный payload, ставит alg=none, оставляет пустую секцию подписи — и стал админом. Вторая близкая атака — algorithm confusion: сервис подписывает RS256 (приватным RSA), верификатор принимает любой alg из заголовка. Атакующий берёт ПУБЛИЧНЫЙ ключ сервера (часто доступен на /.well-known/jwks.json), пересобирает токен с alg=HS256, использует публичный ключ как HMAC-секрет — библиотека верит. Защита: 1) При верификации передавай белый список алгоритмов (например, [RS256]) — НЕ доверяй полю alg из header. 2) Используй разные ключи для разных операций. 3) Отдельно валидируй iss, aud, exp. 4) Лучше выбрать библиотеку, которая привязывает ключ к алгоритму на уровне API.',
                'code_example' => "<?php
use Firebase\\JWT\\JWT;
use Firebase\\JWT\\Key;

// ПЛОХО — без явного указания алгоритма, библиотека прочтёт alg из header
// (старые версии firebase/php-jwt такое позволяли)
\$payload = JWT::decode(\$jwt, \$publicKey);

// ХОРОШО — алгоритм задан жёстко, чужой alg отклоняется
\$payload = JWT::decode(\$jwt, new Key(\$publicKey, 'RS256'));

// Дополнительно — обязательная проверка iss/aud
if (\$payload->iss !== 'https://my-issuer') abort(401);
if (\$payload->aud !== 'my-api')             abort(401);",
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'security.tokens',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Как правильно сравнивать токены и подписи в PHP?',
                'answer' => 'Через hash_equals($known, $user) — constant-time сравнение, защищает от timing-атак. Обычное == выходит на первом несовпадающем байте, и атакующий по разнице времени побайтово подбирает токен. Это касается CSRF-токенов, HMAC-подписей webhook, API-ключей. Для паролей отдельная функция — password_verify (тоже constant-time внутри).',
                'code_example' => "<?php
\$sentToken = \$_POST['token'] ?? '';
\$realToken = \$_SESSION['csrf'];

if (!hash_equals(\$realToken, \$sentToken)) {
    http_response_code(403);
    exit('CSRF token mismatch');
}",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'security.tokens',
            ],
        ];
    }
}
