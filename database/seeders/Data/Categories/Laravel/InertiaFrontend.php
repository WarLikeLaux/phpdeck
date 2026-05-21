<?php

namespace Database\Seeders\Data\Categories\Laravel;

class InertiaFrontend
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Laravel',
                'question' => 'Что такое Blade и какие у него преимущества?',
                'answer' => '**Blade** — встроенный шаблонизатор Laravel. Файлы `*.blade.php` в `resources/views`.

Преимущества:

- **Компилируется в чистый PHP** и кешируется в `storage/framework/views` — на рантайме быстрый.
- **Директивы**: `@if`, `@foreach`, `@auth`, `@csrf`, `@error` — короче и читаемее, чем `<?php if (...) ?>`.
- **Наследование шаблонов**: `@extends(\'layouts.app\')` + `@section`/`@yield` — общий лэйаут без копипасты.
- **Компоненты и slots** — переиспользуемые куски UI (`<x-alert>`).
- **Auto-escape**: `{{ $var }}` экранирует HTML — защита от **XSS** из коробки. `{!! $var !!}` — без экранирования.
- **Тесная интеграция с Laravel**: `route()`, `old()`, `$errors`, `auth()` доступны прямо из шаблона.',
                'code_example' => '@extends(\'layouts.app\')

@section(\'content\')
    @auth
        <h1>Привет, {{ auth()->user()->name }}!</h1>
    @endauth

    @forelse ($posts as $post)
        <article>
            <h2>{{ $post->title }}</h2>
            <p>{{ $post->excerpt }}</p>
        </article>
    @empty
        <p>Постов пока нет.</p>
    @endforelse
@endsection',
                'code_language' => 'blade',
                'difficulty' => 2,
                'topic' => 'laravel.inertia_frontend',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Blade Components и как работают slots?',
                'answer' => '**Blade-компонент** — переиспользуемый кусок UI (как в React/Vue), описанный в Laravel.

**Два вида компонентов:**

- **Class-based** — `php artisan make:component Alert` создаёт класс `App\\View\\Components\\Alert` (свойства, конструктор) **и** шаблон `resources/views/components/alert.blade.php`. Подходит для логики (computed-свойства, методы).
- **Anonymous** — только шаблон в `resources/views/components/*.blade.php`, **без класса**. Параметры объявляются через `@props([...])`. Подходит для **чистого UI**.

**Использование:**

- Тег `<x-alert type="error">...</x-alert>` — `kebab-case` от имени файла.
- Вложенные папки: `<x-forms.input>` → `components/forms/input.blade.php`.

**Slots — «дырки» для контента:**

| Тип | Где объявить | Как передать |
| --- | --- | --- |
| **Default** | `{{ $slot }}` | Всё между открывающим и закрывающим тегами |
| **Named** | `{{ $header }}` | `<x-slot:header>...</x-slot:header>` |
| **Scoped attributes** | `$header->attributes` | На теге слота можно навешивать `class="..."` |

**Полезное:**

- `<x-dynamic-component :component="$name">` — компонент по переменной.
- `@aware([\'color\'])` в дочернем — забрать `props` родителя без проброса.
- `$attributes->merge([\'class\' => \'btn\'])` — корректно слить дефолтные классы с пришедшими снаружи.',
                'code_example' => '// resources/views/components/alert.blade.php
<div class="alert alert-{{ $type }}">
    {{ $slot }}
</div>

// Использование
<x-alert type="success">
    Всё хорошо!
</x-alert>',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.inertia_frontend',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое @props в Blade?',
                'answer' => '**`@props([...])`** объявляет **свойства анонимного компонента** (без PHP-класса). Используется только в шаблоне `resources/views/components/*.blade.php`.

**Что делает:**

- Перечисленные ключи становятся **переменными внутри шаблона**.
- Значения в массиве — **дефолты**: `@props([\'type\' => \'primary\', \'size\' => \'md\'])`.
- Эти атрибуты **исключаются** из объекта `$attributes` (туда попадает только «остальное»).

**`$attributes` — bag со всем, что пришло сверх `@props`:**

- `{{ $attributes }}` — вывести всё как есть.
- `$attributes->merge([\'class\' => \'btn\'])` — корректно склеить классы.
- `$attributes->only([\'id\', \'data-*\'])`, `$attributes->except([\'class\'])` — фильтры.
- `$attributes->class([\'btn\', \'btn-primary\' => $type === \'primary\'])` — условные классы.

**Когда выбирать `@props` (anonymous) vs class-based:**

- **Anonymous** — кнопки, инпуты, бейджи, **никакой PHP-логики**.
- **Class-based** — если нужны методы, computed properties, инжекция сервисов в конструктор.

**Подводный камень:** в `@props` нельзя класть значения, требующие выполнения SQL/контейнера — `@props` парсится при компиляции шаблона. Динамические дефолты вычисляй в самом теле компонента: `@php $type ??= \'primary\'; @endphp`.',
                'code_example' => '// resources/views/components/button.blade.php
@props([\'type\' => \'primary\', \'size\' => \'md\'])

<button {{ $attributes->merge([\'class\' => "btn btn-$type btn-$size"]) }}>
    {{ $slot }}
</button>

// Использование
<x-button type="danger" id="del-btn">Удалить</x-button>',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.inertia_frontend',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Inertia.js?',
                'answer' => '**Inertia.js** — «монолит с ощущениями SPA». Вы пишете обычный Laravel (controllers, routes), но возвращаете **не Blade, а компоненты Vue/React/Svelte** через `Inertia::render(\'Users/Index\', [...])`.

**Как устроено:**

- На **первый запрос** браузер получает обычный HTML — там Vite-бандл и JSON с `page` (имя компонента + `props`).
- На **последующие переходы** — `axios`-запрос на тот же URL с заголовком `X-Inertia: true`; Laravel отдаёт **тот же ответ контроллера**, но только как JSON.
- Клиент **подменяет компонент** страницы, передаёт ему свежие `props` — браузер не перезагружается.

**Что не нужно строить:**

- **API** — нет REST/GraphQL слоя; «эндпоинты» это ваши контроллеры.
- **Свой роутинг на фронте** — роуты по-прежнему в `routes/web.php`.

**Полезные фичи Inertia:**

- **`Inertia::share([...])`** — данные, видимые **на всех страницах** (например, текущий юзер, flash).
- **`Inertia::lazy(fn () => ...)`** / **partial reloads** — `router.reload({ only: [\'stats\'] })` обновит только указанные props без полной перерисовки.
- **Validation** — `withErrors()` из Laravel автоматически попадает в `usePage().props.errors`.
- **SSR** — есть отдельный режим server-side rendering для SEO.

**Когда брать:** один продукт, одна команда, нужен SPA-UX без отдельного API.
**Когда не брать:** нужен публичный API (мобильные клиенты, сторонние интеграции) — там REST/GraphQL правильнее.',
                'code_example' => '// Controller
return Inertia::render(\'Users/Index\', [
    \'users\' => User::all(),
]);

// Vue-компонент resources/js/Pages/Users/Index.vue
<script setup>
defineProps({ users: Array })
</script>',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.inertia_frontend',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Livewire?',
                'answer' => '**Livewire** — пакет для **реактивных** интерфейсов на **PHP + Blade** без написания JS-фреймворка. Один компонент = **класс на PHP + Blade-шаблон**.

**Как работает под капотом:**

- На каждом действии (`wire:click`, `wire:model`) Livewire отправляет **AJAX-запрос** на сервер с **текущим состоянием** компонента (свойства + payload).
- Сервер запускает соответствующий метод PHP-класса, **рендерит шаблон заново** и возвращает diff.
- На клиенте **AlpineJS** делает morph-патч DOM-а (без полной перерисовки страницы).

**Ключевые директивы:**

- `wire:click=\'increment\'` — вызвать метод.
- `wire:model=\'name\'` — двунаправленный биндинг (re-render на `blur`/`debounce`).
- `wire:model.live=\'name\'` — обновлять **на каждом keystroke** (дороже, осторожно).
- `wire:loading` / `wire:loading.delay` — состояние «идёт запрос».
- `wire:poll.5s` — опрос сервера по таймеру.

**Чем отличается от Inertia:**

| | `Livewire` | `Inertia` |
| --- | --- | --- |
| Фронтенд-стек | Blade + Alpine | Vue / React / Svelte |
| Где живёт логика | На сервере (каждый клик → PHP) | На клиенте (JS-компонент) |
| Размер ответа | Diff HTML | JSON с props |
| Зависимость от сети | **Высокая** (каждое действие = HTTP) | Только переходы и сабмиты |

**Когда брать:** хочется реактивности, но команда — на бэке; нет желания держать отдельный JS-стек.',
                'code_example' => 'class Counter extends Component {
    public int $count = 0;

    public function increment(): void {
        $this->count++;
    }

    public function render() {
        return view(\'livewire.counter\');
    }
}

// blade
<button wire:click="increment">+</button>
<span>{{ $count }}</span>',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.inertia_frontend',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что делают директивы @once и @verbatim в Blade?',
                'answer' => 'Две полезные «нишевые» директивы:

**`@once`** — гарантирует, что блок отрендерится **только один раз** за HTTP-ответ, даже если родительский шаблон/компонент включается несколько раз.

- Типичный кейс: компонент с datepicker использован 5 раз на странице — нужно подключить JS **один раз**.
- Часто комбинируется с `@push(\'scripts\')` или `@prepend`.

**`@verbatim`** — **отключает** интерпретацию `{{ }}` и `@`-директив внутри блока.

- Нужен, когда страница использует Vue/Alpine/Mustache: их шаблонные `{{ }}` **конфликтуют** с Blade.
- Без `@verbatim` пришлось бы экранировать каждую фигурную скобку (`@{{ }}`).',
                'code_example' => '{{-- @once: подключить скрипт один раз, даже если компонент использован 5 раз --}}
@once
    @push(\'scripts\')
        <script src="{{ asset(\'js/datepicker.js\') }}"></script>
    @endpush
@endonce

{{-- @verbatim: Vue/Alpine синтаксис без конфликта с Blade --}}
@verbatim
<div id="app">
    <p>Hello {{ user.name }}!</p>
    <p>{{ count * 2 }} items</p>
</div>
@endverbatim

{{-- Без @verbatim Blade попытался бы выполнить {{ user.name }} как PHP --}}',
                'code_language' => 'blade',
                'difficulty' => 2,
                'topic' => 'laravel.inertia_frontend',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Laravel Volt и как он связан с Livewire?',
                'answer' => '**`Laravel Volt`** — **single-file API** для **Livewire 3** компонентов: класс компонента и Blade-шаблон в **одном `.blade.php`-файле** через функции `state()`, `computed()`, `mount()`, `rules()`. Под капотом Volt **генерирует обычный Livewire-класс** — это синтаксический сахар, а не отдельный движок.

**Аналогия:** Vue `<script setup>` — короткая запись single-file component.

**Два варианта синтаксиса:**

- **Functional** — top-level вызовы `state([...])`, `mount(fn () => ...)`, `$increment = fn () => ...`. Минимум boilerplate.
- **Class-based** — анонимный `new class extends Component { ... }` прямо в файле. Близко к классическому Livewire.

**Связка с экосистемой L11:**

- Идеально работает с **`Laravel Folio`** (page-based routing): один файл = одна страница со своей логикой. Density резко вырастает.
- Сохраняется вся Livewire-обвязка (`wire:click`, `wire:model`, события, lifecycle).

**Когда брать Volt, когда оставаться на class-based Livewire:**

| | Volt | Class-based Livewire |
| --- | --- | --- |
| Размер | Маленькие/средние страницы | Сложные компоненты |
| Тестирование | Сложнее (нет явного класса) | Проще, привычно |
| Density | Высокая (всё в одном файле) | Низкая (класс + view) |

**Установка:** `composer require livewire/volt` + `php artisan volt:install`. Появился в 2023 году в составе Laravel 10.x ecosystem.',
                'code_example' => '<?php
// composer require livewire/volt
// php artisan volt:install

// resources/views/livewire/counter.blade.php - functional Volt
use function Livewire\\Volt\\{state, computed, mount, on};

state([
    "count" => 0,
    "step"  => 1,
]);

mount(function () {
    $this->count = session("counter", 0);
});

$double = computed(fn () => $this->count * 2);

$increment = fn () => $this->count += $this->step;

on(["echo:counter,Reset" => fn () => $this->count = 0]);
?>

<div>
    <h1>Count: {{ $count }} (x2 = {{ $this->double }})</h1>
    <button wire:click="increment">+ {{ $step }}</button>

    <input wire:model.live="step" type="number">
</div>

{{-- Использование в Blade --}}
<livewire:counter />

{{-- Или в Folio-странице: --}}
{{-- resources/views/pages/counter.blade.php --}}
<x-layout>
    <livewire:counter />
</x-layout>',
                'code_language' => 'blade',
                'difficulty' => 3,
                'topic' => 'laravel.inertia_frontend',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Какие основные директивы Blade?',
                'answer' => 'Самые ходовые директивы:

**Условия и циклы:**
- `@if` / `@elseif` / `@else` / `@endif` — условия.
- `@foreach($items as $i) ... @endforeach` — обычный цикл.
- `@forelse ... @empty ... @endforelse` — цикл с веткой, если массив пуст.

**Наследование шаблонов:**
- `@extends(\'layouts.app\')` + `@section(\'content\')` / `@yield(\'content\')`.
- `@include(\'partial\')` — вставить другой шаблон.

**HTML-формы:**
- `@csrf` — скрытое поле `_token` (защита от CSRF).
- `@method(\'PUT\')` — для PUT/PATCH/DELETE из HTML-формы.

**Auth и ошибки:**
- `@auth` / `@guest` — проверка авторизации.
- `@error(\'field\') ... @enderror` — вывод ошибки валидации поля.',
                'code_example' => '@extends(\'layouts.app\')

@section(\'content\')
    @auth
        <p>Привет, {{ auth()->user()->name }}!</p>
    @endauth

    @forelse ($posts as $post)
        @include(\'posts.partials.card\', [\'post\' => $post])
    @empty
        <p>Постов пока нет.</p>
    @endforelse

    <form method="POST" action="{{ route(\'posts.update\', $post) }}">
        @csrf
        @method(\'PUT\')
        <input name="title" value="{{ old(\'title\', $post->title) }}">
        @error(\'title\') <span class="err">{{ $message }}</span> @enderror
    </form>
@endsection',
                'code_language' => 'blade',
                'difficulty' => 1,
                'topic' => 'laravel.inertia_frontend',
            ],
            [
                'category' => 'Laravel',
                'question' => 'В чём разница между {{ $var }} и {!! $var !!} в Blade?',
                'answer' => '- `{{ $var }}` — автоматически **экранирует HTML** через `htmlspecialchars` (защита от **XSS**). Если в `$var` лежит `<script>alert(1)</script>`, отобразится как текст и не выполнится.
- `{!! $var !!}` — выводит **как есть**, без экранирования.

**Правило:** по умолчанию всегда `{{ }}`. `{!! !!}` — только когда уверен в безопасности данных (например, заранее очищенный/отрендеренный markdown). **Никогда** не вставлять через `{!! !!}` пользовательский ввод напрямую.',
                'code_example' => '@php
    $name = \'<script>alert("XSS")</script>\';
    $html = \'<strong>Жирный</strong>\';
@endphp

{{ $name }}    {{-- &lt;script&gt;alert("XSS")&lt;/script&gt; - безопасно --}}
{!! $html !!}  {{-- <strong>Жирный</strong> - HTML отрендерится --}}
{!! $name !!}  {{-- ОПАСНО: выполнит JS, если $name от пользователя --}}',
                'code_language' => 'blade',
                'difficulty' => 1,
                'topic' => 'laravel.inertia_frontend',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое @csrf и зачем он нужен?',
                'answer' => '**`@csrf`** — директива Blade, вставляющая в форму скрытое поле `<input type="hidden" name="_token" value="...">` с CSRF-токеном текущей сессии.

Как работает защита:

- Middleware `VerifyCsrfToken` включён по умолчанию для web-роутов.
- Сверяет `_token` из запроса с токеном сессии.
- При несовпадении — **HTTP 419 Page Expired**.

**Зачем:** защищает от **CSRF** (Cross-Site Request Forgery) — со стороннего сайта нельзя отправить POST/PUT/DELETE-запрос от имени залогиненного юзера, так как в нём не будет валидного токена.

Для AJAX токен передают в заголовке `X-CSRF-TOKEN` (берётся из `<meta name="csrf-token">`).',
                'code_example' => '<form method="POST" action="{{ route(\'posts.store\') }}">
    @csrf
    <input name="title">
    <button>Создать</button>
</form>

{{-- Что превратится в HTML --}}
<input type="hidden" name="_token" value="aB3Xz...сессионный токен">

{{-- Для AJAX --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
fetch(\'/api/posts\', {
    method: \'POST\',
    headers: {
        \'X-CSRF-TOKEN\': document.querySelector(\'meta[name="csrf-token"]\').content,
        \'Content-Type\': \'application/json\',
    },
    body: JSON.stringify({ title: \'Hello\' }),
});
</script>',
                'code_language' => 'blade',
                'difficulty' => 1,
                'topic' => 'laravel.inertia_frontend',
            ],
        ];
    }
}
