<?php

namespace Database\Seeders\Data\Categories\Testing;

class Tools
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Тестирование',
                'question' => 'Что такое PHPUnit простыми словами?',
                'answer' => 'Стандартный фреймворк для написания тестов на PHP. Тесты — это классы, наследующиеся от TestCase, методы которых начинаются с «test»: public function testCalculation(). Запуск через vendor/bin/phpunit. Используется в Laravel, Symfony, почти везде.',
                'difficulty' => 1,
                'topic' => 'testing.tools',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое Pest простыми словами?',
                'answer' => 'Современный PHP-фреймворк тестирования поверх PHPUnit, с более лаконичным синтаксисом: test(\'sum works\', fn() => expect(sum(1, 2))->toBe(3));. Без классов, без public function test*. Под капотом — PHPUnit, можно смешивать. Лёгкий, активно набирает популярность в Laravel-сообществе.',
                'difficulty' => 2,
                'topic' => 'testing.tools',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Как запустить тесты в Laravel?',
                'answer' => 'php artisan test — запускает все тесты с красивым выводом. php artisan test --filter UserTest — только указанный класс/метод. php artisan test --parallel — параллельно (быстрее). Под капотом — phpunit с phpunit.xml-конфигом.',
                'difficulty' => 1,
                'topic' => 'testing.tools',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Где лежат тесты в Laravel и как организованы?',
                'answer' => 'В папке tests/. Внутри две главные подпапки: Feature (тесты HTTP-эндпоинтов, БД) и Unit (изолированные тесты классов). Конфиг — phpunit.xml. Базовый класс — TestCase, для Unit — обычный TestCase без Laravel-бутстрапа (быстрее).',
                'difficulty' => 1,
                'topic' => 'testing.tools',
            ],
            [
                'category' => 'Тестирование',
                'question' => 'Что такое Mockery простыми словами?',
                'answer' => 'Библиотека для создания mock-объектов в PHP. Mockery::mock(PaymentGateway::class)->shouldReceive(\'charge\')->once()->andReturn(true). Часто используется вместе с PHPUnit (PHPUnit имеет встроенный mock, но Mockery более выразительный). В Laravel интегрирован «из коробки».',
                'difficulty' => 2,
                'topic' => 'testing.tools',
            ],
        ];
    }
}
