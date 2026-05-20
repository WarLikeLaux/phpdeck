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
                'answer' => 'FormRequest - это специальный класс для валидации входящих данных, отдельно от контроллера. Когда вы указываете FormRequest в типе параметра контроллера, Laravel автоматически запустит валидацию ДО выполнения метода. Если валидация не прошла - вернётся ошибка 422 (или редирект с ошибками).',
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
                'answer' => 'rules() - правила. messages() - кастомные сообщения. attributes() - читаемые имена полей. authorize() - проверка прав. prepareForValidation() - изменить данные ПЕРЕД валидацией. withValidator() - добавить кастомные правила/after-callback. passedValidation() - после успешной валидации. failedValidation() - переопределить поведение при ошибке.',
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
                'answer' => 'Через artisan make:rule создать класс, реализующий ValidationRule (Laravel 10+) с методом validate. Также можно использовать closure-правило прямо в массиве rules. Класс Rule предоставляет готовые сложные правила: Rule::unique, Rule::exists, Rule::in, Rule::enum(EnumClass::class), Rule::dimensions(), Rule::array([...]). Rule::when($condition, $rules, $defaultRules) - условно подключить набор правил.',
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
                'answer' => 'FormRequest - типизированный request с инкапсулированной валидацией и авторизацией. Контейнер резолвит его и через ValidatesWhenResolvedTrait::validateResolved() запускает фиксированную последовательность: 1) prepareForValidation() (нормализация входа - merge/replace ДО авторизации и правил), 2) passesAuthorization() → authorize() (если false → failedAuthorization), 3) getValidatorInstance() - создание валидатора, внутри которого читаются rules()/messages()/attributes() и вызывается withValidator() для after-rules, 4) если валидатор fails → failedValidation, иначе passedValidation() для пост-обработки. failedValidation/failedAuthorization можно переопределять для кастомных ответов. Тонкий момент: prepareForValidation() выполняется ДО authorize(), поэтому authorize() уже видит нормализованные данные ($this->input()).',
                'code_example' => '<?php
class StoreUserRequest extends FormRequest {
    protected function prepareForValidation(): void {
        $this->merge(["email" => strtolower($this->email ?? "")]);
    }
    public function rules(): array {
        return ["email" => ["required", "email", Rule::unique("users")]];
    }
    public function authorize(): bool { return $this->user()->can("create-user"); }
}',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'laravel.requests_validation',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как валидировать вложенные массивы и поля внутри них в Laravel (items.*.id, items.*.qty)?',
                'answer' => 'Laravel поддерживает dot-нотацию для вложенных полей и звёздочку * как универсальный матчер по индексам массива. Базовое: "items" => "required|array|min:1" - сам массив непустой; "items.*.id" => "required|integer|exists:products,id" - КАЖДЫЙ элемент массива должен иметь поле id, существующее в products.id; "items.*.qty" => "required|integer|min:1" - количество позиций. Проверка существования через exists делается ОДНИМ запросом для всех значений (Laravel под капотом делает WHERE id IN (...)). Это критично: наивный foreach с проверкой по одному превратит ~N запросов в БД. Для unique аналогично: "emails.*" => "unique:users,email". Для кастомных messages используется такой же паттерн ключей: "items.*.id.exists" => "продукта :input не существует". Подводный камень: при правиле required_with на вложенном уровне писать "items.*.qty" => "required_with:items.*.id" - синтаксис тот же. Для условной валидации зависящей от родителя - withValidator + after callback.',
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
