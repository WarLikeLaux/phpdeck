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
                'answer' => 'Трейт - механизм горизонтального переиспользования кода в PHP. Это набор методов и свойств, которые можно "подключить" в класс через use. По сути - копирование кода в класс на этапе компиляции. Решает проблему отсутствия множественного наследования: класс может использовать множество трейтов. Минусы: скрытое поведение, конфликты имён, повышенная связность. Хорошее применение - небольшие переиспользуемые куски (например, HasTimestamps, Macroable).',
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
                'answer' => 'Минусы трейтов: 1) Скрытые зависимости — трейт, дёргающий $this->db или $this->logger, прячет требования класса. 2) Класс с пятью трейтами обычно нарушает SRP. 3) Тест: трейт нельзя инстанцировать отдельно, мокать его реализацию — только через override в классе. 4) Поведение фиксируется в compile-time, в рантайме не подменишь (в отличие от DI). Трейты ОК для маленьких stateless-кусков (HasTimestamps, генерация UUID, Macroable). Плохо — для подмешивания сервисов; их место в конструкторе через DI.',
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
                'answer' => 'Если два трейта содержат метод с одинаковым именем, при их одновременном использовании возникнет ошибка. Решение - инструкции insteadof (выбрать какой использовать) и as (создать алиас). Также as позволяет изменить видимость метода. Это сложный механизм, и обычно проще не допускать таких конфликтов.',
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
                'answer' => 'Method resolution order для методов (от высшего к низшему): 1) метод самого класса; 2) метод из подключённого трейта; 3) метод родительского класса. То есть трейт ВСЕГДА переопределяет parent-метод, но метод класса переопределяет трейт. Если в нескольких трейтах одинаковый метод — fatal, конфликт разрешают через insteadof/as. Применение: чтобы класс мог переопределить «поведение по умолчанию» из трейта — просто напиши свой метод в классе.',
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
                'answer' => 'Технически свойства в трейте работают (при use «копируются» в класс), но есть проблемы. 1) Конфликт свойств — fatal, если в классе и трейте определения расходятся (тип, дефолт, видимость); для свойств нет insteadof/as. 2) Переименование поля в трейте молча ломает все классы-потребители. 3) Конструктор в трейте нормально не объявить — конфликт с конструктором класса. 4) Тест трейта возможен только через конкретный класс-носитель. Senior-практика: трейты — только для методов (HasFactory, SoftDeletes). Нужно состояние — выноси в отдельный класс и инжекть через композицию.',
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
                'answer' => 'Стоит: для общего ПОВЕДЕНИЯ без состояния — форматтеры, утилиты (FormatsMoney, HasTimestamps в Eloquent). Не стоит: для общего СОСТОЯНИЯ (свойств), для замены наследования, когда отношение is-a. Минусы: трудно тестировать отдельно, скрытая связанность, конфликты имён. Если у трейта есть зависимости — выноси в отдельный сервис и инжекть через DI.',
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
