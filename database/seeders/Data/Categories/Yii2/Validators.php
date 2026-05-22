<?php

namespace Database\Seeders\Data\Categories\Yii2;

class Validators
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Yii2',
                'question' => 'Что такое rules() в модели Yii2?',
                'answer' => 'Метод `rules()` возвращает **массив правил валидации** атрибутов модели.

- Каждое правило — массив: `[атрибуты, валидатор, опции...]`.
- Валидатор — это либо **встроенное имя** (`required`, `string`, `email`...), либо **inline-метод** модели, либо класс.
- Атрибут может быть строкой или массивом строк.
- Применяется при вызове `$model->validate()`.',
                'code_example' => 'public function rules()
{
    return [
        [[\'name\', \'email\'], \'required\'],
        [\'email\', \'email\'],
        [\'name\', \'string\', \'min\' => 2, \'max\' => 50],
        [\'age\', \'integer\', \'min\' => 18],
    ];
}',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'yii2.validators',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Какие самые ходовые встроенные валидаторы в Yii2?',
                'answer' => 'Yii2 поставляет десятки **named-валидаторов**:

- **Обязательность/пустота**: `required`, `default`.
- **Типы**: `string`, `integer`, `number`, `boolean`, `date`.
- **Форматы**: `email`, `url`, `match` (regex), `ip`, `captcha`.
- **Списки**: `in`, `each`.
- **Сравнение**: `compare` (с другим атрибутом).
- **БД**: `exist`, `unique`.
- **Файлы**: `file`, `image`.
- **Прочее**: `filter` (трансформация значения), `safe` (только массовое присваивание).',
                'code_example' => 'public function rules()
{
    return [
        [\'password\', \'required\'],
        [\'password\', \'string\', \'min\' => 8],
        [\'email\', \'email\'],
        [\'website\', \'url\', \'defaultScheme\' => \'https\'],
        [\'phone\', \'match\', \'pattern\' => \'/^\\+?\\d{10,15}$/\'],
        [\'role\', \'in\', \'range\' => [\'admin\', \'user\', \'guest\']],
        [\'email\', \'unique\', \'targetClass\' => User::class],
    ];
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.validators',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как ограничить длину строкового поля в Yii2?',
                'answer' => 'Через валидатор **`string`** с опциями `min`, `max`, `length`.

- `min` / `max` — границы (включительно).
- `length` — массив `[min, max]` или фиксированное число.
- Проверяется **в символах** (по умолчанию мультибайт через `mb_strlen`).
- Можно указать `tooShort` / `tooLong` сообщения.',
                'code_example' => 'public function rules()
{
    return [
        [\'title\', \'string\', \'min\' => 3, \'max\' => 120],
        [\'slug\', \'string\', \'length\' => [3, 80]],
        [\'code\', \'string\', \'length\' => 6,
            \'message\' => \'Код должен быть ровно 6 символов\'],
    ];
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.validators',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Зачем нужен валидатор compare в Yii2?',
                'answer' => 'Сравнивает атрибут **с другим значением** или атрибутом.

- `compareAttribute` — имя соседнего атрибута модели.
- `compareValue` — фиксированное значение.
- `operator` — `==`, `===`, `!=`, `>`, `<`, `>=`, `<=` (по умолчанию `==`).
- Типичный кейс — поле **"повторите пароль"**.',
                'code_example' => 'public $password;
public $password_repeat;

public function rules()
{
    return [
        [[\'password\', \'password_repeat\'], \'required\'],
        [\'password_repeat\', \'compare\', \'compareAttribute\' => \'password\',
            \'message\' => \'Пароли не совпадают\'],
        [\'age\', \'compare\', \'compareValue\' => 18, \'operator\' => \'>=\',
            \'type\' => \'number\'],
    ];
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.validators',
            ],
            [
                'category' => 'Yii2',
                'question' => 'В чём разница между unique и exist в Yii2?',
                'answer' => 'Оба валидатора **проверяют БД**, но в противоположных направлениях.

| Валидатор | Что проверяет |
|---|---|
| `unique` | Что значения **нет** в таблице (новый email) |
| `exist` | Что значение **есть** в таблице (FK на существующий ID) |

- Оба используют `targetClass` (ActiveRecord) и `targetAttribute`.
- Для `unique` при update **игнорируется текущая запись** (по PK).
- `filter` опция — дополнительный where-кляуз.',
                'code_example' => 'public function rules()
{
    return [
        [\'email\', \'unique\', \'targetClass\' => User::class,
            \'message\' => \'Email уже занят\'],

        [\'category_id\', \'exist\', \'targetClass\' => Category::class,
            \'targetAttribute\' => \'id\'],

        // Composite unique:
        [\'name\', \'unique\', \'targetClass\' => Tag::class,
            \'targetAttribute\' => [\'name\', \'workspace_id\']],
    ];
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.validators',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое сценарии (scenarios) в Yii2?',
                'answer' => 'Сценарии — это **именованные наборы** атрибутов, валидируемые в разных контекстах.

- В правиле указывается опция `on` или `except`.
- Метод `scenarios()` определяет, какие атрибуты считаются **safe** для каждого сценария (важно для `load()`).
- Сценарий устанавливается через `$model->scenario = \'create\'`.
- По умолчанию все правила работают в сценарии `default`.',
                'code_example' => 'class User extends ActiveRecord
{
    const SCENARIO_CREATE = \'create\';
    const SCENARIO_UPDATE = \'update\';

    public function rules()
    {
        return [
            [[\'username\', \'email\'], \'required\'],
            [\'password\', \'required\', \'on\' => self::SCENARIO_CREATE],
            [\'password\', \'string\', \'min\' => 8],
        ];
    }

    public function scenarios()
    {
        $s = parent::scenarios();
        $s[self::SCENARIO_CREATE] = [\'username\', \'email\', \'password\'];
        $s[self::SCENARIO_UPDATE] = [\'username\', \'email\'];
        return $s;
    }
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.validators',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что делает опция when у валидатора Yii2?',
                'answer' => '`when` — это **условный валидатор**: правило применится только если callback вернёт `true`.

- Принимает анонимную функцию `function ($model, $attribute) { ... }`.
- Для **клиентской** проверки есть `whenClient` (JS-функция).
- Часто используется для **зависимых полей** (например, телефон обязателен, только если выбран контакт по телефону).',
                'code_example' => 'public function rules()
{
    return [
        [\'phone\', \'required\', \'when\' => function ($model) {
            return $model->contact_type === \'phone\';
        }, \'whenClient\' => "function (attribute, value) {
            return $(\'#contact_type\').val() === \'phone\';
        }"],
    ];
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.validators',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как написать inline-валидатор в Yii2?',
                'answer' => 'Inline-валидатор — это **метод модели**, имя которого указывается в `rules()`.

- Сигнатура: `function ($attribute, $params, $validator)`.
- Внутри использует `$this->addError(\'attr\', \'msg\')` для регистрации ошибки.
- Подходит для **разовой** логики, специфичной для конкретной модели.
- Если логика **переиспользуется** — лучше класс-валидатор.',
                'code_example' => 'public function rules()
{
    return [
        [\'username\', \'validateUsername\'],
    ];
}

public function validateUsername($attribute, $params, $validator)
{
    if (in_array(strtolower($this->$attribute), [\'admin\', \'root\'])) {
        $this->addError($attribute, \'Это имя зарезервировано\');
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.validators',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как написать кастомный класс-валидатор в Yii2?',
                'answer' => 'Кастомный валидатор — класс, наследник `yii\\validators\\Validator`, с методом `validateAttribute`.

- Внутри `validateAttribute($model, $attribute)` вызывается `$this->addError(...)` или `$model->addError(...)`.
- Свойства класса доступны как опции в правиле (`\'minLength\' => 3`).
- Можно сделать **standalone** валидатор: тогда вызывается через `Validator::createValidator(MyValidator::class, ...)`.
- Преимущество перед inline — **переиспользование** в нескольких моделях.',
                'code_example' => '// validators/SlugValidator.php
class SlugValidator extends \\yii\\validators\\Validator
{
    public $pattern = \'/^[a-z0-9-]+$/\';

    public function validateAttribute($model, $attribute)
    {
        if (!preg_match($this->pattern, $model->$attribute)) {
            $this->addError($model, $attribute,
                \'Slug может содержать только a-z, 0-9 и дефис\');
        }
    }
}

// Использование:
public function rules()
{
    return [
        [\'slug\', SlugValidator::class],
    ];
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.validators',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как запустить валидацию модели и получить ошибки в Yii2?',
                'answer' => 'Основной метод — **`validate()`**, который возвращает `bool`.

- `$model->validate()` — прогоняет все применимые правила сценария.
- `$model->validate([\'email\'])` — только указанные атрибуты.
- `$model->hasErrors()` — есть ли ошибки.
- `$model->getErrors()` — массив `[attr => [msg1, msg2]]`.
- `$model->getFirstError(\'email\')` — первая ошибка по полю.
- `save()` для ActiveRecord **вызывает validate()** автоматически.',
                'code_example' => '$model = new ContactForm();
$model->load(Yii::$app->request->post());

if ($model->validate()) {
    // ok
} else {
    var_dump($model->getErrors());
    // [\'email\' => [\'Email is invalid\']]
    echo $model->getFirstError(\'email\');
}

// Пропустить валидацию при сохранении:
$user->save(false);',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.validators',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как валидировать загружаемый файл/картинку в Yii2?',
                'answer' => 'Валидаторы `file` и `image` работают с `UploadedFile`.

- `file` — расширения, MIME, размер, количество файлов.
- `image` — то же + размеры (ширина/высота).
- Атрибут модели должен **храниться как `UploadedFile`** (через `getInstance` или `getInstances`).
- Для multiple — `maxFiles > 1`.',
                'code_example' => 'class UploadForm extends \\yii\\base\\Model
{
    public $avatar;

    public function rules()
    {
        return [
            [\'avatar\', \'image\',
                \'extensions\' => \'png, jpg, webp\',
                \'maxSize\' => 2 * 1024 * 1024,
                \'minWidth\' => 100, \'minHeight\' => 100],
        ];
    }
}

// Controller
$model->avatar = \\yii\\web\\UploadedFile::getInstance($model, \'avatar\');
if ($model->validate()) {
    $model->avatar->saveAs(\'@webroot/uploads/\' . $model->avatar->name);
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.validators',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что делает валидатор default в Yii2?',
                'answer' => '`default` — это **не проверка**, а **подстановка значения**, если атрибут пустой.

- Срабатывает **только** для пустых значений (`null`, `\'\'`, `[]`).
- Опция `value` — статичное значение или callable.
- Удобно ставить в начало правил, чтобы дальше другие валидаторы видели уже выставленное значение.
- Не вызывает ошибок — всегда «успешен».',
                'code_example' => 'public function rules()
{
    return [
        [\'status\', \'default\', \'value\' => \'draft\'],
        [\'created_at\', \'default\', \'value\' => function ($model) {
            return time();
        }],
        [\'status\', \'in\', \'range\' => [\'draft\', \'published\']],
    ];
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.validators',
            ],
        ];
    }
}
