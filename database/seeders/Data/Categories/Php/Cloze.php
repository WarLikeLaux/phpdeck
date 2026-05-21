<?php

namespace Database\Seeders\Data\Categories\Php;

class Cloze
{
    public static function all(): array
    {
        return [
            [
                'category' => 'PHP',
                'question' => 'Заполни сигнатуру readonly value-object Money с конструктором.',
                'answer' => '**`final readonly class` (PHP 8.2+)** — все нестатические свойства автоматически **`readonly`**: после первой записи их **нельзя переприсвоить**.

**Главное отличие от deep immutability:**
- **`readonly`** запрещает **только** повторное присваивание **самого свойства**
- если внутри лежит **изменяемый объект** (`Bag`, `ArrayObject`) — его внутреннее состояние **меняется** (interior mutability)
- для глубокой иммутабельности **все вложенные объекты** тоже должны быть immutable

**Constructor property promotion** — объявление и инициализация полей **одной строкой** прямо в параметрах конструктора.

**Что делает `final` на классе:**
- запрещает наследование
- закрывает обход иммутабельности через подкласс с другим конструктором',
                'cloze_text' => 'final {{readonly}} class Money {
    public function __construct(
        public {{int}} $amount,
        public string $currency,
    ) {}
}',
                'difficulty' => 3,
                'topic' => 'php.cloze',
                'cloze_text' => 'final {{readonly}} class Money {
    public function __construct(
        public {{int}} $amount,
        public string $currency,
    ) {}
}',
                'difficulty' => 3,
                'topic' => 'php.cloze',
            ],
            [
                'category' => 'PHP',
                'question' => 'Заполни match-выражение для типов HTTP-методов.',
                'answer' => '**`match` vs `switch`:**

| Аспект | `match` | `switch` |
|---|---|---|
| Сравнение | **строгое** `===` | **нестрогое** `==` |
| Fallthrough | нет, нужны `,` | есть, нужен `break` |
| Возвращает значение | да (expression) | нет (statement) |
| Непокрытый кейс | **`UnhandledMatchError`** | молча провалится |

**Когда брать `match`:**
- Когда **обязан** учесть все варианты — компилятор/runtime подскажет через `UnhandledMatchError`.
- Когда нужен **результат-выражение** для присваивания.

**Подвох:** `match (true)` — идиома для серии условий (как `if/elseif`), не злоупотреблять — это уже не «pattern matching».',
                'cloze_text' => '$cmd = {{match}}($method) {
    "GET", "HEAD" => "read",
    "POST", "PUT", "PATCH" => "write",
    "DELETE" => "delete",
    {{default}} => throw new InvalidArgumentException(),
};',
                'difficulty' => 3,
                'topic' => 'php.cloze',
            ],
            [
                'category' => 'PHP',
                'question' => 'Заполни generator для чтения большого CSV.',
                'answer' => '**`yield`** превращает функцию в **ленивый итератор** (`Generator`): на каждой итерации читается **одна строка**, не весь файл.

**Зачем это здесь:**
- CSV на **гигабайты** не влезет в массив через `file()` / `fgetcsv()` в цикле с накоплением — **OOM**.
- Генератор отдаёт строки **по одной**, потребитель сразу обрабатывает и забывает.

**Тонкости:**
- Возвращаемый тип — **`Generator`** (а не `iterable`), если хочешь точную сигнатуру.
- **`finally { fclose($h); }`** обязателен: при `break` в потребителе генератор уничтожается, но дескриптор файла останется висеть без `finally`.
- `yield $key => $row` — можно отдавать пары ключ-значение.
- Генератор **одноразовый** — нельзя пройти второй раз без перевызова функции.',
                'cloze_text' => 'function readCsv(string $path): {{Generator}} {
    $h = fopen($path, "r");
    if ($h === false) {
        throw new RuntimeException("Cannot open file");
    }
    try {
        while (($row = fgetcsv($h)) !== false) {
            {{yield}} $row;
        }
    } finally {
        fclose($h);
    }
}',
                'difficulty' => 3,
                'topic' => 'php.cloze',
            ],
        ];
    }
}
