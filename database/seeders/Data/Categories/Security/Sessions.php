<?php

namespace Database\Seeders\Data\Categories\Security;

class Sessions
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Безопасность',
                'question' => 'Что такое сессия простыми словами с точки зрения безопасности?',
                'answer' => 'Способ сохранить «**кто вошёл**» между HTTP-запросами без повторной отправки логина/пароля.

**Как устроено**:

- сервер генерирует длинный случайный `session_id`, кладёт его в куку;
- сами данные сессии хранятся **на сервере** (`file`/`redis`/`db`);
- с каждым запросом кука приходит — по id находим запись.

**Кража куки = полный доступ к аккаунту**, поэтому обязательны три флага:

- `HttpOnly` — JS не прочитает, защита от **XSS**;
- `Secure` — только по `HTTPS`, защита от перехвата;
- `SameSite=Lax`/`Strict` — браузер не пошлёт куку с чужого домена, защита от **CSRF**.

**Дополнительно**:

- `session_regenerate_id(true)` сразу после логина — защита от **session fixation**;
- инвалидация записи **на сервере** при logout (стирание куки на клиенте не убивает сессию).',
                'code_example' => "<?php
// Установка флагов через session.cookie_* в php.ini
// или ini_set до session_start():
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure',   '1');
ini_set('session.cookie_samesite', 'Lax');
session_start();

// Регенерация ID после логина — защита от session fixation
if (login_succeeded(\$user)) {
    session_regenerate_id(true);
    \$_SESSION['user_id'] = \$user->id;
}",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'security.sessions',
            ],
            [
                'category' => 'Безопасность',
                'question' => 'Что такое cookie простыми словами?',
                'answer' => '**Cookie** — маленький кусочек данных (до ~4 КБ), который сервер просит браузер сохранить и слать обратно с каждым запросом к этому сайту. Браузер хранит куки в привязке к домену.

Используются для сессий, авторизации, настроек интерфейса, A/B-тестов, корзин гостей.

- Сервер ставит куку заголовком `Set-Cookie` в ответе.
- Браузер шлёт её обратно заголовком `Cookie`.

**Ключевые атрибуты безопасности:**

- `Secure` — только по HTTPS.
- `HttpOnly` — недоступна из JavaScript (защита от XSS-кражи).
- `SameSite=Lax/Strict` — защита от CSRF.
- `Expires` / `Max-Age` — срок жизни (без них кука «сессионная» и умирает с закрытием браузера).
- `Domain` и `Path` — для каких URL отправлять.',
                'code_example' => '# Ответ сервера
HTTP/1.1 200 OK
Set-Cookie: session_id=abc123; HttpOnly; Secure; SameSite=Lax

# Следующий запрос браузера
GET /me HTTP/1.1
Cookie: session_id=abc123',
                'code_language' => 'http',
                'difficulty' => 1,
                'topic' => 'security.sessions',
            ],
        ];
    }
}
