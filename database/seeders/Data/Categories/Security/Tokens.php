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
                'question' => 'Что такое JWT простыми словами?',
                'answer' => 'JSON Web Token — строка из трёх частей, разделённых точками: header.payload.signature. Header описывает алгоритм подписи, payload — данные о пользователе (id, role, exp), signature — криптографическая подпись. Сервер при получении проверяет подпись и доверяет payload. Используется для авторизации API.',
                'code_example' => "// JWT состоит из трёх base64url-частей через точку
eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiI0MiIsInJvbGUiOiJ1c2VyIn0.SflKxw...

// Декодируется так:
header  = {\"alg\":\"HS256\"}
payload = {\"sub\":\"42\",\"role\":\"user\"}
sig     = HMAC-SHA256(base64(header) + \".\" + base64(payload), secret)",
                'code_language' => 'http',
                'difficulty' => 2,
                'topic' => 'security.tokens',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Чем JWT отличается от сессии простыми словами?',
                'answer' => 'Сессия: на сервере хранится state (имя, корзина, права), у клиента только короткий ID. JWT: state ВНУТРИ токена, сервер ничего не хранит — нужна только проверка подписи. Плюс JWT — stateless (легко масштабировать). Минус — нельзя «выйти» мгновенно (токен валиден до истечения), и токен больше по размеру.',
                'difficulty' => 2,
                'topic' => 'security.tokens',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое access token и refresh token простыми словами?',
                'answer' => 'access_token — короткоживущий (минуты-часы), даёт доступ к API. refresh_token — долгоживущий (недели-месяцы), не используется для API, но позволяет получить новый access_token, когда старый истёк. Идея: украденный access_token быстро протухнет, а refresh хранится надёжнее (HttpOnly-кука, secure storage в мобильном).',
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
                'answer' => 'Payload JWT — это base64, НЕ шифрование. Любой может прочитать содержимое (есть только подпись от подделки). Нельзя класть пароли, секреты, ПДн, токены сторонних сервисов. Можно: id, role, имя, экспирейшн. Если очень нужно прятать — используй JWE (JSON Web Encryption), не обычный JWT.',
                'difficulty' => 2,
                'topic' => 'security.tokens',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое CSRF-токен простыми словами?',
                'answer' => 'Случайная строка, которую сервер генерирует и кладёт в форму/cookie при загрузке страницы. При POST/PUT/DELETE сервер проверяет, что токен из запроса совпадает с сохранённым. Атакующий сайт не знает токен → запрос отклоняется. В Laravel — @csrf в Blade-форме автоматически.',
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
