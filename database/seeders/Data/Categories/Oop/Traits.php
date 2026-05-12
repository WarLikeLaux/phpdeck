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
                'answer' => 'Трейты соблазнительны как способ "подключить функциональность", но платят за это серьёзными архитектурными штрафами. Главные минусы: 1) Скрытые зависимости. Если трейт использует $this->db, $this->logger или вызывает $this->save() - это неявная зависимость, которой не видно в конструкторе класса. Класс перестаёт быть честным про свои требования. 2) Нарушение SRP. Класс с пятью трейтами имеет пять причин для изменения - и обычно это означает, что трейты прячут отдельные ответственности, которые должны были быть отдельными объектами и инжектиться в конструктор. 3) Сложность тестирования. Трейт нельзя замокать отдельно - он встраивается в класс-носитель, и любой тест поведения, добавленного трейтом, требует инстанцирования всего класса. Подменить реализацию метода трейта можно только через override в самом классе или через as-rename + написание заместителя. 4) Композиция через статическое связывание. Поведение трейта фиксируется в compile-time, нельзя подменить в рантайме (в отличие от DI зависимости). Когда трейты ОК: маленькие, без внешних зависимостей, чистые - HasTimestamps, генерация UUID, Macroable, простые value-преобразования. Когда плохо: трейты, тянущие сервисы (логгер, БД, http-клиент) - это всегда сигнал, что должно быть DI. Альтернатива почти всегда: композиция (объект внутри класса) или интерфейс + реализация по DI.',
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
                'answer' => 'PHP имеет строгий method resolution order для трейтов. Речь только о МЕТОДАХ - для свойств отдельный механизм: либо одинаковые определения сливаются, либо fatal. Приоритет методов (от высшего к низшему): 1) Метод самого класса (определённый в нём напрямую). 2) Метод из подключённого трейта. 3) Метод унаследованный от родительского класса. То есть для методов трейт ВСЕГДА переопределяет унаследованный метод от parent, но метод самого класса переопределяет трейт. Это критично для понимания mixin-механики PHP: трейт не "усиливает" класс, а буквально подмешивает свой код "поверх" родительского, но "под" собственный код класса. Если в нескольких трейтах одинаковый метод - это конфликт, и компилятор требует разрешить его через insteadof/as. Как проверить на практике: если ваш Eloquent-класс наследует Model (где есть scopeA), и подключил трейт с собственным scopeA - победит трейт; но если в самой User написать собственный scopeA - победит User. Применение знания: если хотите дать ровно гибкость "поведение по умолчанию из трейта, но возможность переопределить в классе" - просто оставляйте метод в трейте. Если нужна "подкладка" под parent - не сработает, метод класса всегда сверху parent через любой трейт.',
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
                'question' => 'Почему свойства внутри трейта (stateful trait) - это антипаттерн?',
                'answer' => 'Трейт может объявлять не только методы, но и свойства. Технически это работает: при use в классе свойства "копируются" в класс, как будто объявлены прямо в нём. На практике это создаёт проблемы. 1) ОДНА ОБЛАСТЬ ИМЁН: Fatal error "Class X and Trait Y define the same property ($foo) in the composition" возникает, если определения свойства в трейте и классе расходятся (разный тип, разный default, разная видимость). Идентичные определения PHP принимает молча - это и делает рефакторинг хрупким. В отличие от методов, для свойств НЕТ операторов insteadof/as - конфликт не разрешить декларативно. 2) ХРУПКОСТЬ РЕФАКТОРИНГА: переименование свойства в трейте сломает все классы-потребители молча, без подсказок IDE. 3) НЕТЕСТИРУЕМОСТЬ: трейт нельзя инстанцировать отдельно; если его поведение опирается на состояние - тест становится тестом конкретного класса-носителя, а не трейта. 4) НАРУШЕНИЕ ИНКАПСУЛЯЦИИ: внешний код, читая класс, не видит без раскрытия трейта, какие у него поля и инварианты. 5) В трейте нельзя нормально объявить КОНСТРУКТОР для инициализации своего состояния - его конструктор конфликтует с конструктором класса/других трейтов. Senior-практика: трейты использовать ТОЛЬКО для методов (Searchable, HasFactory, SoftDeletes - либо stateless, либо опираются на конвенции типа "колонка deleted_at"). Если нужно состояние - выносить в отдельный класс и инжектить через композицию.',
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
                'answer' => 'Способ переиспользовать набор методов в нескольких классах без наследования. Объявляется trait, подключается use внутри класса. Методы трейта становятся методами класса. Решает проблему множественного наследования (в PHP его нет) для шаринга поведения.',
            ],
            [
                'category' => 'ООП',
                'topic' => 'oop.traits',
                'difficulty' => 2,
                'question' => 'Когда использовать трейт, а когда — нет?',
                'answer' => 'Стоит: для общего ПОВЕДЕНИЯ без состояния — форматтеры, утилиты (FormatsMoney, HasTimestamps в Eloquent). Не стоит: для общего СОСТОЯНИЯ (свойств), для замены наследования, когда отношение is-a. Минусы: трудно тестировать отдельно, скрытая связанность, конфликты имён.',
            ],
        ];
    }
}
