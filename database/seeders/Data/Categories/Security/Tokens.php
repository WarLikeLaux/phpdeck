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
                'answer' => 'Строка, идентифицирующая клиента или сессию — заменяет повторную отправку логина/пароля. Примеры: JWT в Authorization-заголовке, API-ключ Stripe, OAuth access_token, refresh_token, CSRF-токен в форме. Сервер выдаёт токен после успешной аутентификации, клиент шлёт его с каждым запросом. Токен — конфиденциальная строка, относиться к нему надо как к паролю.',
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
                'question' => 'Почему JWT нельзя «отозвать» сразу простыми словами?',
                'answer' => 'Сервер JWT не хранит — он только проверяет подпись. Поэтому украденный токен работает до своего exp (срок действия). «Logout» обычно просто удаляет токен на клиенте — но если он уже украден, ничего не поможет. Решения: 1) короткий exp (5-15 минут). 2) Чёрный список revoked-токенов (теряется stateless). 3) Версия токена в БД (token_version), инвалидация всех при logout.',
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
                'answer' => 'localStorage — доступен из JS, любой XSS = угнанный токен. Cookie с флагами HttpOnly + Secure + SameSite=Strict/Lax — JS не прочитает, но появляется риск CSRF (решается CSRF-токеном или SameSite). Общая рекомендация: refresh_token — в HttpOnly-куку, access_token — в памяти JS (не сохранять между перезагрузками). Никогда не клади токены в URL-параметры — попадут в логи и истории браузера.',
                'difficulty' => 3,
                'topic' => 'security.tokens',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Почему JWT с alg=none — опасная фича?',
                'answer' => 'В стандарте JWT есть значение alg=none — токен «без подписи». Старые библиотеки могли принимать такой токен как валидный — атакующий просто кладёт нужный payload и alg=none. Аналогичная классическая дыра — алгоритм-confusion (alg=HS256 с публичным RSA-ключом в качестве «секрета»). Защита: при верификации жёстко указывай разрешённые алгоритмы списком, не доверяй полю alg из header.',
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
