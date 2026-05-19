<?php

namespace Database\Seeders\Data\Categories\Testing;

class Basics
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Тестирование',
                'question' => 'Зачем нужны автоматические тесты простыми словами?',
                'answer' => '1) Уверенность при изменениях — поменял код, прогнал тесты, понял что не сломал. 2) Документация поведения — тест показывает, как код должен работать. 3) Быстрая обратная связь — баг ловится в CI, не на проде. 4) Защита от регрессий — старая фича не сломалась с новой. Без тестов рефакторинг — лотерея.',
                'difficulty' => 1,
                'topic' => 'testing.basics',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое unit-тест простыми словами?',
                'answer' => 'Тест на одну маленькую единицу кода: метод, функция, класс — В ИЗОЛЯЦИИ от других частей системы. БД, сеть, файлы заменяются на mock-и. Быстрый (миллисекунды), запускается тысячами в CI. Например, тест «calculateDiscount(100, 0.2) возвращает 80».',
                'difficulty' => 1,
                'topic' => 'testing.basics',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое integration-тест простыми словами?',
                'answer' => 'Тест, проверяющий взаимодействие нескольких компонентов вместе — например, контроллер + сервис + реальная БД (тестовая). Медленнее unit-теста (секунды), но ловит баги, которые в изоляции не видны (опечатка в SQL, неверная миграция, плохой JOIN).',
                'difficulty' => 1,
                'topic' => 'testing.basics',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое e2e-тест простыми словами?',
                'answer' => 'End-to-end — проверяет всё приложение целиком, как пользователь: открывает браузер, кликает кнопки, заполняет формы, ждёт ответ. Самый медленный (минуты), но самый «реалистичный». Инструменты: Cypress, Playwright, Selenium, Laravel Dusk.',
                'difficulty' => 1,
                'topic' => 'testing.basics',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое feature-тест в Laravel простыми словами?',
                'answer' => 'Тест функциональности с точки зрения HTTP-запроса. Делает $this->get(\'/users\') или $this->post(\'/login\', [...]) и проверяет ответ + изменения в БД. Между unit и e2e: проходит через middleware, контроллеры, БД, но без браузера. Самый частый тип тестов в Laravel.',
                'code_example' => "<?php\n\nnamespace Tests\\Feature;\n\nuse App\\Models\\User;\nuse Illuminate\\Foundation\\Testing\\RefreshDatabase;\nuse Tests\\TestCase;\n\nclass LoginTest extends TestCase\n{\n    use RefreshDatabase;\n\n    public function test_user_can_login_with_valid_credentials(): void\n    {\n        \$user = User::factory()->create([\n            'email' => 'jane@example.com',\n            'password' => bcrypt('secret123'),\n        ]);\n\n        \$response = \$this->post('/login', [\n            'email' => 'jane@example.com',\n            'password' => 'secret123',\n        ]);\n\n        \$response->assertRedirect('/dashboard');\n        \$this->assertAuthenticatedAs(\$user);\n    }\n}",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'testing.basics',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое пирамида тестирования простыми словами?',
                'answer' => 'Картинка-метафора: внизу много дешёвых unit-тестов, посередине меньше integration, сверху совсем мало e2e. Дешёвые быстрые тесты ловят 80% багов, дорогие медленные — оставшиеся 20%. Антипаттерн «мороженое» — наоборот, много e2e + мало unit — медленный, хрупкий, ловит мало.',
                'difficulty' => 2,
                'topic' => 'testing.basics',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое assertion простыми словами?',
                'answer' => 'Утверждение «должно быть так-то», которое тест проверяет. Если не так — тест падает. Примеры: assertEquals($expected, $actual), assertTrue($x), assertCount(3, $arr). В Laravel: $response->assertStatus(200), $response->assertJsonFragment(...).',
                'difficulty' => 1,
                'topic' => 'testing.basics',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое AAA в тестах простыми словами?',
                'answer' => 'Arrange-Act-Assert — структура хорошего теста. Arrange: подготовь данные ($user = User::factory()->create()). Act: выполни проверяемое действие ($response = $this->post(\'/login\')). Assert: проверь результат ($response->assertOk()). Каждая часть отделена пустой строкой — тест читается линейно.',
                'code_example' => "<?php\n\npublic function test_user_can_change_email(): void\n{\n    // Arrange — готовим данные\n    \$user = User::factory()->create(['email' => 'old@example.com']);\n    \$this->actingAs(\$user);\n\n    // Act — выполняем проверяемое действие\n    \$response = \$this->patch('/profile', [\n        'email' => 'new@example.com',\n    ]);\n\n    // Assert — проверяем результат\n    \$response->assertRedirect('/profile');\n    \$this->assertSame('new@example.com', \$user->fresh()->email);\n}",
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'testing.basics',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое регрессионный тест простыми словами?',
                'answer' => 'Тест, написанный после того, как починили баг — чтобы баг больше не вернулся незаметно. Алгоритм: 1) Нашли баг. 2) Написали падающий тест, воспроизводящий баг. 3) Починили код — тест позеленел. 4) Этот тест остаётся в кодбазе навсегда и стоит на страже. Так растёт сеть тестов, защищающая от повторных регрессий.',
                'difficulty' => 2,
                'topic' => 'testing.basics',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое smoke-тест простыми словами?',
                'answer' => 'Быстрый поверхностный тест «работает ли вообще»: главная страница открывается (200), логин не падает, healthcheck отвечает. Цель — за секунды убедиться, что сборка не «дымится», прежде чем гонять полный прогон или после деплоя. Не заменяет нормальные тесты, а дополняет.',
                'difficulty' => 2,
                'topic' => 'testing.basics',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Black-box vs white-box тестирование простыми словами?',
                'answer' => 'Black-box: тестируем «снаружи», не зная как устроен код — только вход и выход (типичный feature-тест через HTTP). White-box: тестируем «изнутри», зная структуру кода — проверяем приватные ветки, конкретные методы (типичный unit-тест). Серая середина — gray-box: знаем устройство в общих чертах, но проверяем поведение.',
                'difficulty' => 2,
                'topic' => 'testing.basics',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Почему один тест должен проверять одну вещь?',
                'answer' => 'Когда тест проверяет 10 вещей и падает — непонятно, что именно сломалось, читаешь весь тест. Когда тест проверяет одно («регистрация отправляет welcome-email») и падает — диагноз по имени теста. Правило: один сценарий = один тест. Это не запрет на несколько assert-ов — assert-ы могут проверять разные аспекты одного сценария.',
                'difficulty' => 2,
                'topic' => 'testing.basics',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Как именовать тесты, чтобы они читались как документация?',
                'answer' => 'Имя теста = предложение про поведение. Плохо: testLogin, testUser1. Хорошо: test_user_can_login_with_valid_credentials, test_login_fails_with_wrong_password, test_guest_cannot_access_dashboard. Pattern «что_делает_кто_при_каких_условиях». В Pest: it(\'redirects guest to login\', ...). Когда видишь упавший тест в CI — по имени уже ясно, что сломалось.',
                'difficulty' => 2,
                'topic' => 'testing.basics',
            ],
        ];
    }
}
