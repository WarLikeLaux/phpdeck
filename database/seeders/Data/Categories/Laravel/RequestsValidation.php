<?php

namespace Database\Seeders\Data\Categories\Laravel;

class RequestsValidation
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Laravel',
                'question' => 'Как получить данные из Request в Laravel?',
                'answer' => 'Через объект **`Illuminate\\Http\\Request`** в параметре метода контроллера. Laravel сам подсунет его через DI-контейнер.

Основные методы:

- **`input(\'name\', \'default\')`** — конкретное поле (с дефолтом).
- **`all()`** — весь payload (POST + query).
- **`only([\'a\', \'b\'])`** / **`except([\'c\'])`** — подмножество.
- **`has(\'name\')`** — есть ли ключ.
- **`filled(\'name\')`** — есть и не пустой.
- **`query(\'page\')`** — только из query string (`?page=2`).
- **`post(\'email\')`** — только из тела POST.
- **`file(\'avatar\')`** — загруженный файл (`UploadedFile`).

Заголовки и cookies:

- **`header(\'Authorization\')`**.
- **`cookie(\'lang\')`**.
- **`bearerToken()`** — токен из `Authorization: Bearer ...`.

Контекст:

- **`$request->user()`** — текущий юзер.
- **`$request->ip()`**, **`$request->path()`**, **`$request->method()`**.

Часто используют **`$request->validate([...])`** — провалидировать и сразу получить массив только нужных полей.',
                'code_example' => 'public function store(Request $request) {
    $name = $request->input(\'name\');
    $email = $request->input(\'email\', \'default@mail.com\');
    $only = $request->only([\'name\', \'email\']);
    $hasName = $request->has(\'name\');
    $token = $request->header(\'Authorization\');
    $file = $request->file(\'avatar\');
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.requests_validation',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Какие способы вернуть Response в Laravel?',
                'answer' => 'Способы вернуть ответ из контроллера:

**Простые:**

- **`return view(\'users.show\', [...])`** — HTML из Blade.
- **`return $user`** или **`return [\'ok\' => true]`** — автоматически сериализуется в JSON.
- **`return response(\'Hello\', 200)`** — произвольный контент с кодом.

**JSON и API:**

- **`response()->json([\'user\' => $user], 201)`** — явный JSON.
- **`new UserResource($user)`** — через API Resource (рекомендуется).

**Файлы:**

- **`response()->download($path, \'file.pdf\')`** — скачивание.
- **`response()->stream($callback)`** — стриминг (для больших файлов).

**Редиректы:**

- **`redirect()->route(\'home\')`** — на имя маршрута.
- **`redirect(\'/login\')`** — по URL.
- **`back()`** — назад с возможностью `->with()`/`->withErrors()`.

**Прерывание:**

- **`abort(404, \'Не найдено\')`** — `HttpException`.
- **`abort_if(...)`**, **`abort_unless(...)`** — условные варианты.

Заголовки/коды: `->header(\'X-Custom\', \'v\')`, `->setStatusCode(202)`, `->cookie(...)`.',
                'code_example' => 'return response(\'Hello\', 200)->header(\'X-Custom\', \'value\');
return response()->json([\'user\' => $user], 201);
return response()->download($path, \'file.pdf\');
return redirect()->route(\'home\')->with(\'success\', \'Готово\');
abort(404, \'Не найдено\');',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.requests_validation',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое FormRequest и зачем он нужен?',
                'answer' => '**`FormRequest`** — специальный класс-наследник `Illuminate\\Foundation\\Http\\FormRequest`, инкапсулирующий **валидацию и авторизацию** входящих данных **отдельно от контроллера**.

**Как подключается:** type-hint в параметре метода контроллера — Laravel сам резолвит его через DI, **до** выполнения метода запускает `authorize()` и `rules()`.

**Что внутри:**

| Метод | Что делает |
| --- | --- |
| `rules()` | Массив правил валидации |
| `authorize()` | `true`/`false` — можно ли пользователю выполнить запрос (если `false` → **`403`**) |
| `messages()` | Кастомные сообщения для конкретных ошибок |
| `attributes()` | Человекочитаемые имена полей |
| `prepareForValidation()` | Нормализация **до** валидации (`$this->merge([...])`) |
| `withValidator($v)` | After-callback с кастомными правилами |
| `passedValidation()` / `failedValidation()` | Хуки после успеха/провала |

**Что даёт:**

- **Тонкий контроллер** — он берёт уже валидные данные через `$request->validated()`.
- **Переиспользование** — `StoreUserRequest` работает в `store`/`update`/импортах.
- **Тестируется отдельно** — можно гонять unit-тесты на правилах без HTTP.
- **Безопасный mass assignment** — `validated()` отдаёт только описанные в `rules()` поля.

**Поведение при провале:**

- HTML-запрос → **редирект назад** с ошибками в сессии и `old()` для полей.
- JSON-запрос → ответ **`422 Unprocessable Entity`** с массивом ошибок.',
                'code_example' => 'class StoreUserRequest extends FormRequest {
    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        return [
            \'name\' => [\'required\', \'string\', \'max:255\'],
            \'email\' => [\'required\', \'email\', \'unique:users\'],
        ];
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.requests_validation',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Какие методы есть у FormRequest для кастомизации валидации?',
                'answer' => 'Полный набор хуков жизненного цикла FormRequest:

| Метод | Когда зовётся | Зачем |
| --- | --- | --- |
| `prepareForValidation()` | **До** валидации | Нормализовать вход — `merge`, `replace`, тримминг, `Str::slug` |
| `authorize()` | После prepare | Вернуть `true`/`false` — можно ли запросить |
| `rules()` | Перед валидацией | Правила; могут зависеть от `$this->route(\'id\')` |
| `messages()` | При формировании ошибок | Тексты под конкретные правила |
| `attributes()` | При формировании ошибок | Имена полей по-человечески (`:attribute` в сообщениях) |
| `withValidator($v)` | После создания валидатора | Кастомные **after-rules**, кросс-полевые проверки |
| `passedValidation()` | После успеха | Доп. обработка (например, поднять данные в DTO) |
| `failedValidation($v)` | При провале | Переопределить ответ (часто — для API) |
| `failedAuthorization()` | При `authorize() === false` | Кастомный 403 |

**Важные тонкости:**

- `prepareForValidation()` выполняется **до `authorize()`** — поэтому `authorize()` уже видит нормализованные данные.
- В `rules()` можно делать **разные** правила под `POST`/`PUT`: `match ($this->method()) { ... }` или ветка по `$this->route(\'user\')`.
- Кросс-полевые правила (`title !== body`) удобнее в `withValidator()->after(...)` — там доступен полный массив значений.
- Для API можно переопределить `failedValidation()`, чтобы всегда отдавать единый JSON-формат ошибок.',
                'code_example' => 'public function prepareForValidation(): void {
    $this->merge([\'slug\' => Str::slug($this->title)]);
}

public function withValidator($validator): void {
    $validator->after(function ($v) {
        if ($this->title === $this->body) {
            $v->errors()->add(\'body\', \'Заголовок и текст одинаковые\');
        }
    });
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.requests_validation',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как создать кастомное правило валидации?',
                'answer' => 'Три способа, по возрастанию переиспользуемости:

**1) Class-based (рекомендуется в L10+):**

- `php artisan make:rule Uppercase` — класс, реализующий **`Illuminate\\Contracts\\Validation\\ValidationRule`** с методом `validate($attribute, $value, Closure $fail)`.
- При нарушении — звать `$fail(\'сообщение\')` (можно `->translate()` для i18n).
- Доступ к другим значениям формы — через **`DataAwareRule`** (`setData`) и **`ValidatorAwareRule`** (`setValidator`).

**2) Closure прямо в массиве `rules()`:**

```php
\'token\' => [\'required\', function ($attr, $value, $fail) {
    if (! str_starts_with($value, \'tok_\')) $fail(\'Неверный формат.\');
}]
```

**3) Готовые сложные правила из `Illuminate\\Validation\\Rule`:**

| Хелпер | Что делает |
| --- | --- |
| `Rule::unique(\'users\', \'email\')->ignore($id)` | Уникальность с исключением своей записи (важно для `update`) |
| `Rule::exists(\'users\', \'id\')->where(\'active\', 1)` | Существование + доп. условия |
| `Rule::in([...])`, `Rule::notIn([...])` | Перечисления |
| `Rule::enum(Status::class)` | Бэк-энам |
| `Rule::dimensions()->minWidth(100)` | Размеры изображения |
| `Rule::array([...])` | Только указанные ключи |
| `Rule::when($cond, $rules, $else)` | Условный набор правил |

**Когда выбирать что:** разовый чек — closure; повторяющаяся бизнес-логика — отдельный класс; чисто библиотечная валидация — `Rule::*`.',
                'code_example' => 'class Uppercase implements ValidationRule {
    public function validate(string $attribute, mixed $value, Closure $fail): void {
        if (strtoupper($value) !== $value) {
            $fail(\'Значение должно быть в верхнем регистре.\');
        }
    }
}

// Использование
$request->validate([\'code\' => [\'required\', new Uppercase()]]);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.requests_validation',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Form Request и какие у него этапы валидации?',
                'answer' => '**`FormRequest`** — типизированный request с **инкапсулированными валидацией и авторизацией**. Контейнер резолвит его и через **`ValidatesWhenResolvedTrait::validateResolved()`** запускает **фиксированную последовательность хуков**.

**Этапы жизненного цикла FormRequest (в порядке вызова):**

| № | Метод | Что делает |
|---|---|---|
| 1 | **`prepareForValidation()`** | **Нормализация входа** — `merge`/`replace` ДО авторизации и правил |
| 2 | **`passesAuthorization()` → `authorize()`** | Проверка прав; если `false` → **`failedAuthorization()`** (по умолчанию `403`) |
| 3 | **`getValidatorInstance()`** | Создание валидатора |
| 4 | Внутри (3): чтение **`rules()` / `messages()` / `attributes()`** | Сбор правил |
| 5 | Внутри (3): **`withValidator($validator)`** | After-rules, кросс-полевые проверки |
| 6 | Валидация выполняется | Если **fails** → **`failedValidation($validator)`** |
| 7 | **`passedValidation()`** | Пост-обработка (например, поднять данные в DTO) |

**Что можно переопределять:**

- **`failedValidation($validator)`** — кастомный ответ при провале (обычно для API — единый JSON-формат).
- **`failedAuthorization()`** — кастомный 403 / редирект.

**Тонкий момент — `prepareForValidation` выполняется ДО `authorize()`:**

- `authorize()` уже видит **нормализованные данные** (`$this->input()` отражает изменения из `merge()`).
- Это позволяет писать `authorize()` против чистого payload.

**Полезные продвинутые техники:**

- В **`rules()`** разные правила под `POST`/`PUT`: `match ($this->method()) { ... }`.
- В **`rules()`** правила, зависящие от `$this->route("id")` — для `update` с `Rule::unique()->ignore()`.
- **Кросс-полевые правила** удобнее в `withValidator()->after(...)` — там доступен полный массив значений.
- **`$this->validated()`** — массив **только тех полей**, что прошли валидацию (безопасно для `Model::create`).
- **`$this->safe()->only([...])`** / **`->merge([...])`** — fine-grained доступ к валидным данным.

**Главное правило:** **не передавайте `$request->all()` в `Model::create`** — это путь к mass assignment-уязвимости. Используйте **`$request->validated()`**.',
                'code_example' => '<?php
use Illuminate\\Foundation\\Http\\FormRequest;
use Illuminate\\Validation\\Rule;

class StoreUserRequest extends FormRequest
{
    // 1) Нормализация входа ДО authorize и rules
    protected function prepareForValidation(): void
    {
        \$this->merge([
            "email" => strtolower(\$this->email ?? ""),
            "name"  => trim(\$this->name ?? ""),
        ]);
    }

    // 2) Авторизация - видит нормализованные данные
    public function authorize(): bool
    {
        return \$this->user()->can("create-user");
    }

    // 3) Правила
    public function rules(): array
    {
        \$id = \$this->route("user")?->id;
        return [
            "email"    => ["required", "email", Rule::unique("users")->ignore(\$id)],
            "name"     => ["required", "string", "max:255"],
            "password" => ["required", "string", "min:8", "confirmed"],
        ];
    }

    // 4) Кросс-полевые правила
    public function withValidator(\$validator): void
    {
        \$validator->after(function (\$v) {
            if (str_contains(\$this->password, \$this->name)) {
                \$v->errors()->add("password", "Пароль не должен содержать имя");
            }
        });
    }

    // 5) Кастомный ответ при провале (для API)
    protected function failedValidation(\\Illuminate\\Contracts\\Validation\\Validator \$validator)
    {
        if (\$this->expectsJson()) {
            throw new \\Illuminate\\Http\\Exceptions\\HttpResponseException(
                response()->json([
                    "message" => "Validation failed",
                    "errors"  => \$validator->errors(),
                ], 422)
            );
        }
        parent::failedValidation(\$validator);
    }
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.requests_validation',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как валидировать вложенные массивы и поля внутри них в Laravel (items.*.id, items.*.qty)?',
                'answer' => 'Laravel поддерживает **dot-нотацию** для вложенных полей и **`*`** как универсальный матчер по индексам массива.

**Базовый шаблон валидации списка позиций:**

| Ключ | Что проверяет |
| --- | --- |
| `items` | `required\|array\|min:1\|max:100` — сам массив непустой и ограничен |
| `items.*.id` | `required\|integer\|exists:products,id` — каждый элемент содержит существующий товар |
| `items.*.qty` | `required\|integer\|min:1` — количество |
| `items.*.note` | `nullable\|string\|max:255` |
| `tags.*` | `string\|distinct` — `distinct` запрещает повторы в массиве |

**Важная оптимизация — `exists`/`unique` с `*`:**

- Laravel под капотом склеивает значения в **`WHERE id IN (...)`** — **один SQL** на всю проверку.
- Наивный `foreach` с `exists` по одному превратит проверку в N запросов — это распространённая ошибка.

**Кастомные сообщения для вложенных полей** — ключи тоже идут с `*`:

```php
\'items.*.id.exists\' => \'Товар :input не найден\',
\'items.*.qty.min\'   => \'Минимум 1 шт. в позиции\',
```

**Кросс-полевые правила:**

- `required_with:items.*.id` — `qty` обязателен, если в этой же позиции есть `id`.
- Сложнее «количество позиций уникальны по product_id» — выносится в `withValidator()->after(...)`, потому что декларативно не выразить.',
                'code_example' => '<?php
class StoreOrderRequest extends FormRequest {
    public function rules(): array {
        return [
            "customer_id"     => ["required", "integer", "exists:users,id"],
            "items"           => ["required", "array", "min:1", "max:100"],
            "items.*.id"      => ["required", "integer", "exists:products,id"],
            "items.*.qty"     => ["required", "integer", "min:1"],
            "items.*.note"    => ["nullable", "string", "max:255"],
            "shipping.city"   => ["required_with:shipping", "string"],
            "shipping.zip"    => ["required_with:shipping", "regex:/^\\d{6}$/"],
            "tags"            => ["array"],
            "tags.*"          => ["string", "distinct"], // distinct - в массиве нет дублей
        ];
    }

    public function messages(): array {
        return [
            "items.*.id.exists" => "Товар :input не найден",
            "items.*.qty.min"   => "Минимум 1 шт. в позиции",
        ];
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.requests_validation',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Какие самые частые validation rules в Laravel?',
                'answer' => 'Самые ходовые правила валидации:

**Обязательность и null:**
- `required` — поле обязательно.
- `nullable` — разрешён `null`.

**Типы:**
- `string`, `integer`, `numeric`, `boolean`, `array`, `date`, `url`.

**Длина/диапазон:**
- `min:N`, `max:N` — длина строки или значение числа.

**Формат:**
- `email`, `regex:/.../`.

**БД:**
- `unique:users,email` — уникальность в таблице.
- `exists:users,id` — значение должно существовать в БД.

**Прочее:**
- `confirmed` — требует парного поля `<field>_confirmation` (типично для пароля).
- `in:admin,editor,viewer` — одно из перечисленных значений.

Записывают строкой через `|` или массивом (предпочтительно — лучше читается и не ломается при значениях со специальными символами).',
                'code_example' => '$request->validate([
    \'name\'        => [\'required\', \'string\', \'max:255\'],
    \'email\'       => [\'required\', \'email\', \'unique:users,email\'],
    \'age\'         => [\'nullable\', \'integer\', \'min:18\'],
    \'password\'    => [\'required\', \'string\', \'min:8\', \'confirmed\'],
    \'role\'        => [\'required\', \'in:admin,editor,viewer\'],
    \'company_id\'  => [\'required\', \'exists:companies,id\'],
    \'website\'     => [\'nullable\', \'url\'],
]);

// или строкой
$request->validate([\'email\' => \'required|email|unique:users,email\']);',
                'code_language' => 'php',
                'difficulty' => 1,
                'topic' => 'laravel.requests_validation',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как вывести ошибки валидации в Blade-шаблоне?',
                'answer' => 'Если валидация падает, Laravel делает редирект **назад** и через middleware **`ShareErrorsFromSession`** кладёт в каждый view переменную **`$errors`** (`MessageBag`).

Способы вывода:

**Одно поле — директива `@error`:**

- `@error(\'email\') ... {{ $message }} ... @enderror` — внутри доступна переменная `$message`.

**Все ошибки списком:**

- `@if($errors->any())` + `@foreach($errors->all() as $error)`.

**Сохранение введённых данных:**

- Хелпер **`old(\'field\', $default)`** возвращает старое значение поля — форма не очищается после ошибки.
- В `value="{{ old(\'email\', $user->email) }}"` — второй аргумент это дефолт (полезно при редактировании).

Дополнительно:

- `$errors->has(\'email\')` — есть ли ошибка по полю.
- `$errors->first(\'email\')` — первая ошибка по полю.
- `$errors->get(\'email\')` — массив всех ошибок по полю.',
                'code_example' => '<form method="POST" action="{{ route(\'users.store\') }}">
    @csrf

    <input name="email" value="{{ old(\'email\') }}">
    @error(\'email\')
        <span class="text-red-500">{{ $message }}</span>
    @enderror

    <input type="password" name="password">
    @error(\'password\')
        <span class="text-red-500">{{ $message }}</span>
    @enderror

    <button>Сохранить</button>
</form>

{{-- Все ошибки списком --}}
@if ($errors->any())
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif',
                'code_language' => 'blade',
                'difficulty' => 2,
                'topic' => 'laravel.requests_validation',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как получить уже провалидированные данные из FormRequest?',
                'answer' => 'Несколько методов на `FormRequest`:

- **`$request->validated()`** — массив **только тех полей**, что прошли валидацию. Всё лишнее отброшено.
- **`$request->safe()->only([\'name\', \'email\'])`** — подмножество провалидированного.
- **`$request->safe()->except([\'password\'])`** — обратная сторона.
- **`$request->safe()->merge([\'user_id\' => auth()->id()])`** — добавить вычисленные поля.

**Почему важно:**

Никогда не передавайте сырой **`$request->all()`** в `Model::create()` — это путь к **mass assignment**-уязвимости. Юзер может подкинуть лишние поля типа `is_admin=1`.

`validated()` гарантирует: в массиве **только то**, что описано в `rules()`. Безопасно скармливать в `create()`.',
                'code_example' => 'public function store(StoreUserRequest $request) {
    // ВСЕ провалидированные поля
    $data = $request->validated();

    // Только нужные
    $data = $request->safe()->only([\'name\', \'email\']);

    // С добавлением вычисленных полей
    $user = User::create([
        ...$request->validated(),
        \'created_by\' => auth()->id(),
    ]);

    return redirect()->route(\'users.show\', $user);
}',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.requests_validation',
            ],
        ];
    }
}
