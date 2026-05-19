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
            [
                'category' => 'PHP',
                'question' => 'Как прочитать файл целиком в строку в PHP?',
                'answer' => 'file_get_contents($path) — простейший способ. Возвращает содержимое или false при ошибке. Для построчного чтения больших файлов — fopen + fgets в цикле (экономит память). Для записи — file_put_contents($path, $data).',
                'difficulty' => 1,
                'topic' => 'php.std_lib',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что делает fopen и какие основные режимы?',
                'answer' => 'Открывает файл для чтения/записи, возвращает дескриптор. Режимы: "r" — чтение, "w" — запись (перезаписывает), "a" — дозапись, "r+" — чтение+запись, "x" — создать новый. Обязательно fclose() в конце. Для простых случаев лучше file_get_contents/file_put_contents.',
                'difficulty' => 2,
                'topic' => 'php.std_lib',
            ],
            [
                'category' => 'PHP',
                'question' => 'Как работать с датой в PHP простыми словами?',
                'answer' => 'Простые задачи: date("Y-m-d H:i:s") — отформатировать текущее время; time() — Unix timestamp; strtotime("+1 day") — строка в timestamp. Для сложного (часовые пояса, арифметика дат) — класс DateTime / DateTimeImmutable. В Laravel поверх него Carbon с удобным API.',
                'difficulty' => 1,
                'topic' => 'php.std_lib',
            ],
            [
                'category' => 'PHP',
                'question' => 'Что делают json_encode и json_decode?',
                'answer' => 'json_encode($data) — превращает массив/объект в JSON-строку. json_decode($json, true) — обратно из JSON в массив (true) или объект stdClass (false). С PHP 7.3 можно передать JSON_THROW_ON_ERROR — невалидный JSON бросит JsonException вместо тихого null/false.',
                'difficulty' => 1,
                'topic' => 'php.std_lib',
            ],
            [
                'category' => 'PHP',
                'question' => 'Какие функции в PHP применяют для математики простыми словами?',
                'answer' => 'Базовые: abs($n) — модуль; round($n, $precision) — округление, floor/ceil — вниз/вверх; min(...$args) и max(...$args) — минимум и максимум (принимают и список аргументов, и массив); pow($base, $exp) или ** — степень; sqrt($n) — квадратный корень; intval/floatval — приведение к числу. Для случайных чисел общего назначения — rand / mt_rand, для криптостойких — random_int / random_bytes.',
                'code_example' => '<?php
echo abs(-5);          // 5
echo round(3.7);       // 4
echo round(3.14159, 2);// 3.14
echo max(1, 5, 3);     // 5
echo min([4, 2, 7]);   // 2
echo sqrt(16);         // 4',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'php.std_lib',
            ],
        ];
    }
}
