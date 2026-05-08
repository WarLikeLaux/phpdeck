<?php

namespace Database\Seeders\Data\Categories\Php;

class StdLib
{
    public static function all(): array
    {
        return [
            [
                'category' => 'PHP',
                'question' => 'Как работать с DateTime и DateTimeImmutable?',
                'answer' => 'DateTime - изменяемый объект даты, методы modify/add/sub МУТИРУЮТ объект. DateTimeImmutable - неизменяемый, методы возвращают НОВЫЙ объект. Всегда предпочитай Immutable - мутабельность дат причина множества багов. Форматирование через format(). Парсинг через createFromFormat. Разница через diff(). Часовые пояса через DateTimeZone.',
                'code_example' => '<?php
$dt = new DateTime("2026-05-01");
$dt->modify("+1 day");
echo $dt->format("Y-m-d"); // 2026-05-02 - изменился!

$dti = new DateTimeImmutable("2026-05-01");
$dti2 = $dti->modify("+1 day");
echo $dti->format("Y-m-d");  // 2026-05-01
echo $dti2->format("Y-m-d"); // 2026-05-02

// Парсинг
$dt = DateTimeImmutable::createFromFormat("d.m.Y", "01.05.2026");

// Разница
$diff = $dti->diff($dti2);
echo $diff->days; // 1

// Часовой пояс
$tz = new DateTimeZone("Europe/Moscow");
$dt = new DateTimeImmutable("now", $tz);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'php.std_lib',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работать с JSON в PHP?',
                'answer' => 'json_encode превращает PHP-структуру в JSON-строку. json_decode парсит JSON. По умолчанию json_decode возвращает объект stdClass, передай true вторым аргументом для массива. Полезные флаги: JSON_THROW_ON_ERROR (PHP 7.3+, выбросит исключение вместо false), JSON_UNESCAPED_UNICODE (не экранировать кириллицу), JSON_PRETTY_PRINT, JSON_UNESCAPED_SLASHES.',
                'code_example' => '<?php
$data = ["name" => "Иван", "age" => 30];

$json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

// Парсинг в массив
$arr = json_decode($json, true);

// Парсинг в объект
$obj = json_decode($json);
echo $obj->name;

// С исключением
try {
    $data = json_decode($invalid, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
    echo $e->getMessage();
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'php.std_lib',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое сериализация в PHP?',
                'answer' => 'Сериализация - превращение PHP-объекта или структуры в строку, из которой потом можно восстановить. serialize() / unserialize() - бинарный PHP-формат, сохраняет тип. json_encode() / json_decode() - текстовый, межъязыковой. С PHP 7.4 есть __serialize / __unserialize - современная замена устаревших Serializable. ВАЖНО: unserialize небезопасен с недоверенными данными - может выполнить код через __wakeup/__destruct (POP-цепочки).',
                'code_example' => '<?php
class User {
    public function __construct(
        public string $name,
        private string $secret,
    ) {}

    public function __serialize(): array {
        return ["name" => $this->name];
    }

    public function __unserialize(array $data): void {
        $this->name = $data["name"];
        $this->secret = "";
    }
}

$user = new User("Иван", "pwd");
$str = serialize($user);

$user2 = unserialize($str);

// Безопасный режим
$obj = unserialize($str, ["allowed_classes" => [User::class]]);',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'php.std_lib',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что такое SPL и какие структуры из неё реально полезны на собеседованиях?',
                'answer' => 'Standard PHP Library предоставляет специализированные структуры данных и итераторы. SplQueue/SplStack/SplDoublyLinkedList - связные списки с O(1) на голову/хвост. SplPriorityQueue - куча. SplObjectStorage - set/map для объектов. SplFixedArray - массив с числовыми индексами и фиксированным размером; немного экономит память по сравнению с обычным array (~1.1-1.3x на PHP 8 для int/string-значений - замерено через memory_get_usage), а не в 3-5 раз, как часто пишут (это легенда из эпохи PHP 5, когда HashTable был тяжёлым; в PHP 7+ packed array хранится как сплошной блок и почти догоняет SplFixedArray). Реальная польза SplFixedArray сегодня - жёсткая фиксация размера и невозможность нечисловых ключей, а не радикальная экономия памяти. Итераторы (RecursiveIteratorIterator, FilterIterator) дают компонуемые потоки.',
                'difficulty' => 4,
                'topic' => 'php.std_lib',
            ],
        ];
    }
}
