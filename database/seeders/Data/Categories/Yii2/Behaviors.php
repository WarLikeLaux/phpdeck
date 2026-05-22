<?php

namespace Database\Seeders\Data\Categories\Yii2;

class Behaviors
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Yii2',
                'question' => 'Что такое behavior в Yii2?',
                'answer' => '**Behavior (поведение)** — отдельный класс, который **прикрепляется к Component** и **добавляет** к нему:

- **новые свойства**,
- **новые методы**,
- **обработчики событий**.

**Зачем нужно:**

- Переиспользовать поведение между разными классами **без множественного наследования**.
- Не плодить базовые классы — например, одна модель `Post` может одновременно иметь `TimestampBehavior`, `BlameableBehavior`, `SluggableBehavior`.

**Главные классы:**

- **`yii\\base\\Behavior`** — базовый класс для своих behaviors.
- Подключённый behavior получает `$this->owner` — объект, к которому прицепился.',
                'code_example' => '<?php
namespace app\\behaviors;

use yii\\base\\Behavior;
use yii\\db\\ActiveRecord;

class UuidBehavior extends Behavior
{
    public string $attribute = \'uuid\';

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => \'setUuid\',
        ];
    }

    public function setUuid(): void
    {
        $this->owner->{$this->attribute} = bin2hex(random_bytes(16));
    }
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.behaviors',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое метод behaviors() в Component?',
                'answer' => '**`behaviors()`** — метод компонента, возвращающий **массив behaviors**, которые нужно прицепить **автоматически** при создании объекта.

**Структура массива:**

- Ключи — **имена** behaviors (для последующего `getBehavior(\'name\')`).
- Значения — конфиги: имя класса или массив с `class` и параметрами.

**Когда вызывается:**

- Внутри `ensureBehaviors()` — лениво при первом обращении к behavior или событию.

**Альтернатива:**

- **`attachBehavior($name, $config)`** — прицепить вручную в коде.
- **`detachBehavior($name)`** — отцепить.',
                'code_example' => '<?php
use yii\\db\\ActiveRecord;
use yii\\behaviors\\TimestampBehavior;
use yii\\behaviors\\BlameableBehavior;

class Post extends ActiveRecord
{
    public function behaviors()
    {
        return [
            \'timestamp\' => TimestampBehavior::class,
            \'blameable\' => [
                \'class\' => BlameableBehavior::class,
                \'createdByAttribute\' => \'author_id\',
                \'updatedByAttribute\' => \'editor_id\',
            ],
        ];
    }
}

// Использование
$post = new Post();
$post->save();
// created_at, updated_at, author_id заполнены автоматически',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.behaviors',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как работают attachBehavior и detachBehavior?',
                'answer' => 'Эти методы позволяют **в рантайме** прикрепить/отцепить поведение к объекту-Component.

**API:**

- **`attachBehavior($name, $behavior)`** — прикрепить. Можно передать имя класса, массив-конфиг или готовый экземпляр.
- **`attachBehaviors([$name => $config, ...])`** — батчем.
- **`detachBehavior($name)`** — отцепить по имени, возвращает отцепленный behavior.
- **`detachBehaviors()`** — отцепить **все**.
- **`getBehavior($name)`** — получить экземпляр behavior.

**Что происходит при attach:**

1. Создаётся объект behavior (через `Yii::createObject` если передан конфиг).
2. Вызывается `$behavior->attach($owner)` — внутри регистрируются обработчики событий из `events()`.

**Detach** делает обратное: снимает обработчики и удаляет behavior из списка.',
                'code_example' => '<?php
$post = new Post();

// Прицепить в рантайме
$post->attachBehavior(\'audit\', [
    \'class\' => \\app\\behaviors\\AuditBehavior::class,
    \'logChannel\' => \'audit\',
]);

// Получить behavior
$audit = $post->getBehavior(\'audit\');

// Отцепить
$post->detachBehavior(\'audit\');

// Прицепить через behaviors() — стандартный путь
class Post extends ActiveRecord
{
    public function behaviors()
    {
        return [
            \'timestamp\' => TimestampBehavior::class,
        ];
    }
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.behaviors',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что делает TimestampBehavior?',
                'answer' => '**`yii\\behaviors\\TimestampBehavior`** — встроенный behavior, **автоматически** заполняющий поля `created_at` и `updated_at`.

**Поведение по умолчанию:**

- При **`INSERT`** — записывает текущее unix-время в **`created_at`** и **`updated_at`**.
- При **`UPDATE`** — обновляет **`updated_at`**.
- Использует `time()` — значение в **секундах с эпохи**.

**Параметры:**

- **`createdAtAttribute`** / **`updatedAtAttribute`** — имена колонок (по умолчанию `created_at` / `updated_at`).
- **`value`** — функция, возвращающая значение (например, `new Expression(\'NOW()\')` для DATETIME).
- **`attributes`** — кастомное сопоставление событие → атрибуты.',
                'code_example' => '<?php
use yii\\behaviors\\TimestampBehavior;
use yii\\db\\Expression;

class Post extends ActiveRecord
{
    public function behaviors()
    {
        return [
            // Простой вариант — int unix-timestamp
            TimestampBehavior::class,

            // Или с DATETIME колонкой
            [
                \'class\' => TimestampBehavior::class,
                \'value\' => new Expression(\'NOW()\'),
            ],

            // Полностью кастом
            [
                \'class\' => TimestampBehavior::class,
                \'createdAtAttribute\' => \'created\',
                \'updatedAtAttribute\' => \'updated\',
                \'value\' => fn () => date(\'Y-m-d H:i:s\'),
            ],
        ];
    }
}',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.behaviors',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что делает BlameableBehavior?',
                'answer' => '**`yii\\behaviors\\BlameableBehavior`** — автоматически заполняет поля **«кто создал»** и **«кто обновил»** ID-ом текущего пользователя.

**По умолчанию:**

- **`createdByAttribute`** = `created_by` — выставляется при `INSERT`.
- **`updatedByAttribute`** = `updated_by` — выставляется при `INSERT` и `UPDATE`.
- Значение берётся из **`Yii::$app->user->id`**.

**Параметры:**

- Можно переопределить имена колонок.
- **`value`** — кастомная функция (например, чтобы взять id из другого источника).
- Если `Yii::$app->user` отсутствует (CLI) — поле не заполнится (или останется null).',
                'code_example' => '<?php
use yii\\behaviors\\BlameableBehavior;

class Post extends ActiveRecord
{
    public function behaviors()
    {
        return [
            // Минимальный вариант
            BlameableBehavior::class,

            // С кастомными именами
            [
                \'class\' => BlameableBehavior::class,
                \'createdByAttribute\' => \'author_id\',
                \'updatedByAttribute\' => \'editor_id\',
            ],
        ];
    }
}

// Логин и сохранение
Yii::$app->user->login($admin);
$post = new Post([\'title\' => \'Hi\']);
$post->save();
// $post->author_id === $admin->id
// $post->editor_id === $admin->id',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.behaviors',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что делает SluggableBehavior?',
                'answer' => '**`yii\\behaviors\\SluggableBehavior`** — генерирует **URL-дружественную строку** (slug) из заданного атрибута модели.

**Поведение:**

- Берёт значение из **`attribute`** (например `title`).
- Прогоняет через **`Inflector::slug()`** — транслитерация, нижний регистр, дефисы.
- Записывает в **`slugAttribute`** (по умолчанию `slug`).
- При **`ensureUnique = true`** — гарантирует **уникальность** в БД (добавляет суффикс `-2`, `-3`).

**Когда применять:**

- Slug в URL постов, категорий, страниц: `/posts/привет-мир` → `/posts/privet-mir`.',
                'code_example' => '<?php
use yii\\behaviors\\SluggableBehavior;

class Post extends ActiveRecord
{
    public function behaviors()
    {
        return [
            [
                \'class\' => SluggableBehavior::class,
                \'attribute\' => \'title\',
                \'slugAttribute\' => \'slug\',
                \'ensureUnique\' => true,
                \'immutable\' => false, // менять slug при смене title
            ],
        ];
    }
}

$post = new Post([\'title\' => \'Привет, мир!\']);
$post->save();
echo $post->slug; // \'privet-mir\'

$post2 = new Post([\'title\' => \'Привет, мир!\']);
$post2->save();
echo $post2->slug; // \'privet-mir-2\' — уникальность',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.behaviors',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое AttributeBehavior?',
                'answer' => '**`yii\\behaviors\\AttributeBehavior`** — **базовый** behavior для случаев, когда **нужно автоматически заполнить произвольный атрибут** при определённом событии.

**Параметры:**

- **`attributes`** — массив `событие => атрибут (или массив атрибутов)`.
- **`value`** — значение или `callable`, возвращающий значение.
- **`preserveNonEmptyValues`** — не перезаписывать, если уже заполнено.

**Это «родитель»** `TimestampBehavior` и `BlameableBehavior` — они задают свои `value` и `attributes` поверх него.

**Когда брать `AttributeBehavior` напрямую:**

- Нужно автозаполнить, например, `status = \'draft\'` при создании.
- Нужно поставить `uuid`, `hash`, `ip_address` — но писать свой behavior долго.',
                'code_example' => '<?php
use yii\\behaviors\\AttributeBehavior;
use yii\\db\\ActiveRecord;

class Post extends ActiveRecord
{
    public function behaviors()
    {
        return [
            [
                \'class\' => AttributeBehavior::class,
                \'attributes\' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => \'status\',
                ],
                \'value\' => \'draft\',
            ],
            [
                \'class\' => AttributeBehavior::class,
                \'attributes\' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => [\'uuid\', \'ip\'],
                ],
                \'value\' => function ($event) {
                    return [
                        \'uuid\' => bin2hex(random_bytes(16)),
                        \'ip\' => Yii::$app->request->userIP,
                    ];
                },
            ],
        ];
    }
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.behaviors',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Чем behavior отличается от PHP trait?',
                'answer' => 'Сравнение **поведения Yii2** и **PHP-трейта**:

| Признак | Behavior (Yii2) | Trait (PHP) |
| --- | --- | --- |
| Подключение | **в рантайме** (`attachBehavior`) | **на этапе компиляции** (`use Trait`) |
| Доступ к владельцу | через **`$this->owner`** | через **`$this`** (становится частью класса) |
| Конфигурируется | да, **массивом параметров** | нет (только public свойства) |
| Состояние | **отдельный объект** с собственными свойствами | свойства класса, в который вкомпилировано |
| События | **подписывает обработчики** через `events()` | нет |
| Снять | **`detachBehavior`** | невозможно |
| Множественное «наследование» | да, можно вешать сколько угодно | да, но конфликты имён |

**Когда что брать:**

- **Trait** — общий код без состояния и без событий (например, общий метод).
- **Behavior** — настраиваемое расширение с реакцией на события (`TimestampBehavior` через trait не сделать).',
                'code_example' => '// Trait — статическая часть кода
trait Sluggable
{
    public function slug(): string
    {
        return strtolower(str_replace(\' \', \'-\', $this->title));
    }
}

class Post extends ActiveRecord
{
    use Sluggable;
}

// Behavior — отдельный конфигурируемый объект
class Post extends ActiveRecord
{
    public function behaviors()
    {
        return [
            [
                \'class\' => SluggableBehavior::class,
                \'attribute\' => \'title\',
                \'ensureUnique\' => true,
            ],
        ];
    }
}
// behavior сам подписывается на EVENT_BEFORE_INSERT',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.behaviors',
            ],
        ];
    }
}
