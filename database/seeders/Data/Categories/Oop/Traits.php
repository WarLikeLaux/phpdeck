<?php

namespace Database\Seeders\Data\Categories\Oop;

class Traits
{
    public static function all(): array
    {
        return [
            [
                'category' => 'ООП',
                'topic' => 'oop.traits',
                'difficulty' => 2,
                'question' => 'Что такое трейты в PHP?',
                'answer' => '**Трейт** — механизм **горизонтального переиспользования** кода: набор методов (и свойств), который подключается в класс через `use`.

**Как работает:** на этапе компиляции методы трейта **копируются** в класс. Это похоже на наследование, но «вбок» — между не связанными классами.

**Зачем:** PHP не поддерживает **множественное наследование классов**, но один класс может подключить **сколько угодно трейтов**.

**Где уместно:** маленькие переиспользуемые куски — `HasTimestamps`, `HasFactory`, `Macroable` в Laravel.

**Минусы:**

- **скрытое поведение** — методы появляются «из воздуха»
- **конфликты имён** при подключении двух трейтов с одним методом
- **повышенная связанность** — трейт прибит к структуре класса-носителя',
                'code_example' => '<?php
trait HasTimestamps
{
    public ?\DateTime $createdAt = null;
    public ?\DateTime $updatedAt = null;

    public function touch(): void
    {
        $this->updatedAt = new \DateTime();
    }
}

class Article
{
    use HasTimestamps;

    public string $title;
}

$a = new Article();
$a->touch();',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.traits',
                'difficulty' => 4,
                'question' => 'Какие основные trade-offs у трейтов и когда их стоит/не стоит использовать?',
                'answer' => '**Трейт = горизонтальная композиция кода в compile-time.** На рантайме это просто методы класса, как будто их написали вручную.

**Минусы трейтов:**

1. **Скрытые зависимости** — трейт дёргает `$this->db` / `$this->logger`, которых **нет в конструкторе** класса. Снаружи зависимость **невидима** — IDE/анализаторы не помогут.
2. **5 трейтов в классе ≈ SRP нарушен** — это «**копилка-помощник**», который делает слишком много.
3. **Тестировать трейт изолированно нельзя** — инстанцировать `trait` нельзя; для теста нужен **тестовый класс-носитель**.
4. **Compile-time only** — поведение зашито на этапе подключения. В рантайме **не подменишь** (в отличие от DI). Невозможны mock-объекты для зависимостей трейта.
5. **`final` в трейте сложно** — `final` метод трейта нельзя переопределить, но он попадает в каждый класс-потребитель.

**Когда трейты ОК:**

| ✅ Когда уместен | ❌ Когда — нет |
|---|---|
| Маленький **stateless** кусок (`HasTimestamps`) | Подмешивание **сервисов** (`Mailer`, `Logger`) |
| **Бизнес-нейтральные** утилиты (генерация UUID, `Macroable`) | Что-то с **сетевыми вызовами** / БД |
| **Расширение фреймворка** (`HasFactory`, `SoftDeletes`) | Содержит **много полей и инвариантов** |
| Метод в нескольких **не родственных** классах | Когда можно обойтись композицией через DI |

**Правило:** **трейты — для методов**. Зависимости и состояние — **через DI и композицию**.',
                'code_example' => '<?php
// ❌ Плохо: трейт со скрытой зависимостью от БД
trait Auditable
{
    public function logChange(string $action): void
    {
        // откуда $this->db? тест должен знать об этом
        $this->db->insert("audit_log", ["action" => $action]);
    }
}

class User
{
    use Auditable;
    // конструктор НИЧЕГО не говорит про БД, но класс от неё зависит
    public function __construct(public string $name) {}
}

// ✅ Хорошо: композиция через DI
final class AuditLogger
{
    public function __construct(private DatabaseInterface $db) {}
    public function logChange(string $action): void { /* ... */ }
}

final class User
{
    public function __construct(
        public string $name,
        private AuditLogger $audit, // зависимость явная, мокается легко
    ) {}
}

// ✅ Допустимый трейт: чистый, без внешних зависимостей
trait HasTimestamps
{
    public ?\DateTimeImmutable $createdAt = null;
    public function touch(): void { $this->createdAt = new \DateTimeImmutable(); }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.traits',
                'difficulty' => 4,
                'question' => 'Как разрешить конфликты имён при использовании нескольких трейтов?',
                'answer' => 'Если два трейта содержат **метод с одинаковым именем**, при одновременном `use` возникает **fatal error**: `Trait method X has not been applied`. PHP **не выбирает автоматически**.

**Два инструмента разрешения:**

| Оператор | Что делает | Пример |
|---|---|---|
| **`insteadof`** | **выбрать** какую версию использовать вместо другой | `A::hello insteadof B;` |
| **`as`** | **создать алиас** для замещённой версии **или сменить видимость** | `B::hello as helloFromB;` |

**`as` умеет три вещи:**

- **Алиас имени** — `B::hello as helloFromB;`
- **Сменить видимость** — `B::hello as protected;`
- **И то, и другое** — `B::hello as protected helloFromB;`

**Важно:**

- **Только для методов** — для **свойств** этого механизма **НЕТ**. Если в двух трейтах одинаковое свойство — **fatal**, без обхода.
- **Класс всегда побеждает** — собственный метод класса перекрывает любой trait-метод **без `insteadof`**.

**Совет:** **не доводить до конфликтов** — переименуй методы в одном из трейтов, либо разнеси по разным классам через композицию. `insteadof`/`as` — **последнее средство**.',
                'code_example' => '<?php
trait A {
    public function hello(): string { return \'A\'; }
}
trait B {
    public function hello(): string { return \'B\'; }
}

class C
{
    use A, B {
        A::hello insteadof B; // используем версию из A
        B::hello as helloFromB; // алиас для B
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.traits',
                'difficulty' => 4,
                'question' => 'Какой приоритет у методов: класс vs трейт vs родитель?',
                'answer' => '**Method resolution order** для методов (**от высшего к низшему**):

| Приоритет | Источник | Победитель |
|---|---|---|
| **1 (высший)** | метод **самого класса** | **класс выигрывает** |
| **2** | метод из подключённого **трейта** | трейт перекрывает parent |
| **3 (низший)** | метод **родительского** класса | побеждает только если нет 1 и 2 |

**Ключевые правила:**

- **Трейт ВСЕГДА перекрывает `parent`-метод** — даже если в трейте не было `parent::` вызова, родительская версия просто **не вызовется**.
- **Метод класса ВСЕГДА перекрывает трейт** — это даёт способ переопределить **поведение по умолчанию** из трейта: просто **напиши свой метод** в классе.
- **Несколько трейтов с одним методом** → **fatal error**, нужен `insteadof`/`as`.
- **Трейт может вызвать `parent::`** — если класс-потребитель имеет родителя, `parent::method()` внутри трейта обращается к нему.

**Практические следствия:**

- **Подмешать поведение** при наследовании, не теряя контроль — `parent::save()` внутри трейта + класс-потребитель имеет `parent`.
- **Удалить поведение трейта** — просто переопределить в классе (заглушкой или своей логикой).
- **Никогда не полагайся** на то, что `parent` дойдёт «через» трейт без явного `parent::` в самом трейте.',
                'code_example' => '<?php
trait T
{
    public function hello(): string { return "from trait"; }
}

class ParentClass
{
    public function hello(): string { return "from parent"; }
}

// 1. Trait > Parent
class A extends ParentClass
{
    use T;
}
echo (new A)->hello(); // "from trait"

// 2. Class own > Trait
class B extends ParentClass
{
    use T;
    public function hello(): string { return "from B"; }
}
echo (new B)->hello(); // "from B"

// 3. Trait может вызывать parent через parent::
trait Logging
{
    public function save(): void
    {
        parent::save();        // ОК, обращается к методу parent класса
        Log::info("saved");
    }
}',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.traits',
                'difficulty' => 4,
                'question' => 'Почему свойства внутри трейта (stateful trait) — антипаттерн?',
                'answer' => 'Технически **свойства в трейте работают** — при `use` они «**копируются**» в класс. Но цена этой возможности высокая.

**Четыре проблемы stateful trait:**

| # | Проблема | Симптом |
|---|---|---|
| **1** | **Конфликт свойств** — **fatal**, если в классе и трейте определения расходятся (тип / дефолт / видимость) | `Class X and trait T define the same property` |
| **2** | Для свойств **нет `insteadof`/`as`** | конфликт нечем разрулить |
| **3** | **Переименование поля** в трейте **молча ломает** все классы-потребители | связанность 1:N без compile-time проверки |
| **4** | **Конструктор в трейте** нормально не объявить | конфликт с конструктором класса; нет способа красиво проинициализировать поле |
| **5** | **Тест возможен только через носитель** | трейт нельзя `new`-нуть отдельно |

**Senior-практика:**

- **Трейты — для методов** (`HasFactory`, `SoftDeletes`, `Macroable`).
- **Нужно состояние** — **выноси в отдельный класс**, инжекть через **композицию** (`Counter`, `MoneyFormatter`).
- **Допустимое исключение** — **очень узкие, бизнес-нейтральные** stateless-marker свойства (`bool $timestamps = true` в Eloquent).

**Признак запаха:** если трейт хранит состояние **бизнес-логики** (`int $balance`, `Order $order`) — это **скрытая Entity**, её надо вынести и инжектить.',
                'code_example' => '<?php
// ❌ Stateful trait - конфликт свойств
trait HasCounter {
    private int $counter = 0;
    public function tick(): void { $this->counter++; }
}

class Order {
    use HasCounter;
    private int $counter; // Fatal: Class Order and Trait HasCounter define same property
}

// ✅ Лучше - композиция через отдельный класс
final class Counter {
    private int $value = 0;
    public function tick(): void { $this->value++; }
    public function value(): int { return $this->value; }
}

class Order {
    public function __construct(private Counter $counter = new Counter()) {}
    public function tick(): void { $this->counter->tick(); }
}

// ✅ Stateless trait как mixin поведения - безопасно
trait FormatsMoney {
    public function asMoney(int $cents): string {
        return number_format($cents / 100, 2);
    }
}',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.traits',
                'difficulty' => 1,
                'question' => 'Что такое трейт (trait) простыми словами?',
                'answer' => '**Трейт** — способ переиспользовать набор методов в нескольких **не связанных** классах **без наследования**.

- Объявляется через `trait`, подключается в класс через `use`.
- На этапе компиляции методы трейта как бы **«копируются»** в класс.
- Решает проблему **отсутствия множественного наследования** в PHP, когда нужно расшарить поведение между классами из разных иерархий.

Типичный пример — `HasTimestamps`, `HasFactory` в Eloquent.',
                'code_example' => '<?php
trait HasTimestamps
{
    public ?\DateTimeImmutable $createdAt = null;

    public function touch(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }
}

class Article
{
    use HasTimestamps;
    public string $title = \'\';
}

class Comment
{
    use HasTimestamps; // тот же touch() без наследования
    public string $body = \'\';
}

$a = new Article();
$a->touch();',
                'code_language' => 'php',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.traits',
                'difficulty' => 2,
                'question' => 'Когда использовать трейт, а когда — нет?',
                'answer' => '**Брать трейт, когда:**

- Общее **поведение без состояния** — форматтеры, хелперы (`FormatsMoney`).
- Stateless mixin для **не связанных классов** — `HasTimestamps`, `HasFactory`.
- Маленький переиспользуемый кусок, который не тянет в иерархию.

**НЕ брать, когда:**

- Нужно общее **состояние** (свойства) — поля в трейте чреваты конфликтами без `insteadof`/`as`.
- Есть отношение **is-a** → используй наследование или интерфейс.
- Трейт **тянет зависимости** (`$this->db`, `$this->logger`) — скрытая связь, тест проваливается.

**Правило:** если у трейта появляется конструктор-логика или сервис — это **сигнал**, что надо вынести в отдельный класс и подключать через **DI**.',
                'code_example' => '<?php
// ✅ Хорошо: stateless mixin поведения
trait FormatsMoney
{
    public function asMoney(int $cents): string
    {
        return number_format($cents / 100, 2);
    }
}

class Invoice
{
    use FormatsMoney;
}

// ❌ Плохо: трейт со скрытой зависимостью от БД
trait Auditable
{
    public function audit(string $action): void
    {
        // откуда $this->db? в конструкторе класса этого не видно
        $this->db->insert(\'audit\', [\'action\' => $action]);
    }
}

// ✅ Лучше: композиция через DI
final class AuditLogger
{
    public function __construct(private Database $db) {}
    public function audit(string $action): void { /* ... */ }
}',
                'code_language' => 'php',
            ],
        ];
    }
}
