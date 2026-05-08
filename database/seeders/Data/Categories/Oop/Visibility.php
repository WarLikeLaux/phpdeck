<?php

namespace Database\Seeders\Data\Categories\Oop;

class Visibility
{
    public static function all(): array
    {
        return [
            [
                'category' => 'ООП',
                'topic' => 'oop.visibility',
                'difficulty' => 2,
                'question' => 'Какие модификаторы видимости есть в PHP?',
                'answer' => 'В PHP три модификатора видимости: 1) public - доступен везде (внутри класса, потомках, снаружи). 2) protected - доступен только внутри класса и его потомков. 3) private - доступен только внутри объявившего класса (даже потомки не видят). По умолчанию (если не указан) свойство/метод имеет видимость public. С PHP 8.4 появилась asymmetric visibility для свойств: можно указать раздельную видимость для чтения и записи (например, public private(set) - читать всем, писать только внутри класса). Это устраняет необходимость в паре приватного поля + публичного геттера.',
                'code_example' => '<?php
class Example
{
    public string $publicProp = \'видно везде\';
    protected string $protectedProp = \'видно в классе и потомках\';
    private string $privateProp = \'видно только в этом классе\';

    // PHP 8.4: асимметричная видимость
    public private(set) int $id = 0; // читать всем, писать только изнутри

    public function show(): void { /* доступ ко всем трём */ }
}

$e = new Example();
echo $e->publicProp;    // OK
echo $e->id;            // OK (read)
// $e->id = 42;         // Error: write only inside class
// echo $e->privateProp; // Error: private',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.visibility',
                'difficulty' => 2,
                'question' => 'В чём разница между protected и private?',
                'answer' => 'protected - свойство/метод доступно внутри объявившего класса И всех его потомков. private - доступно ТОЛЬКО внутри того класса, где объявлено, потомки его не видят. Оба модификатора работают на уровне класса, а не отдельного экземпляра - private метод одного объекта может читать private поля другого объекта того же класса. Используйте private для деталей реализации, которые не должны меняться даже в наследниках. protected - когда хотите дать потомкам доступ для расширения. На практике private чаще предпочтительнее: меньше связности.',
                'code_example' => '<?php
class Base
{
    private string $secret = \'тайна\';
    protected string $shared = \'для потомков\';
}

class Child extends Base
{
    public function test(): void
    {
        echo $this->shared; // OK
        // echo $this->secret; // Ошибка - private недоступен
    }
}',
                'code_language' => 'php',
            ],
        ];
    }
}
