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
                'answer' => 'Через объект Illuminate\Http\Request. Методы: input("name"), all(), only(["a", "b"]), except(["c"]), has("name"), filled("name"), get(), post(), query(), file("avatar"). Заголовки: header(). Cookie: cookie().',
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
                'answer' => 'response($content), response()->json($data), response()->view("name"), response()->download($path), response()->stream(), redirect()->route(), back(), abort(404). Можно установить статус и заголовки.',
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
                'answer' => 'required (обязательное), email (формат email), integer/numeric (число), string (строка), min:N / max:N (диапазон), unique:users,email (уникальность в БД), exists:users,id (существование в БД), confirmed (нужно поле email_confirmation), in:foo,bar (одно из), nullable (разрешён null).',
                'difficulty' => 1,
                'topic' => 'laravel.requests_validation',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как вывести ошибки валидации в Blade-шаблоне?',
                'answer' => 'Laravel передаёт $errors в view при редиректе обратно. Для одного поля: @error(\'email\') {{ $message }} @enderror. Все: @if($errors->any()) @foreach($errors->all() as $err) ... @endforeach @endif. old(\'email\') — старое значение для авто-заполнения формы.',
                'difficulty' => 2,
                'topic' => 'laravel.requests_validation',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Как получить уже провалидированные данные из FormRequest?',
                'answer' => '$request->validated() — массив только тех полей, что прошли валидацию (всё лишнее отброшено). $request->safe()->only([\'name\', \'email\']) — подмножество. $request->safe()->merge([\'user_id\' => auth()->id()]) — добавить вычисленные поля. Никогда не передавайте сырой $request->all() в Model::create() — это путь к mass-assignment-уязвимости.',
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
