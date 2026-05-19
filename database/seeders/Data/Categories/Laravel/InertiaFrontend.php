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
                'answer' => 'Blade - это шаблонизатор Laravel. Преимущества: компилируется в PHP (быстрый), есть директивы (@if, @foreach, @auth, @csrf), наследование шаблонов (@extends, @section), компоненты, slots, безопасный по умолчанию (auto-escape через {{ }}).',
                'code_example' => '@extends(\'layouts.app\')

@section(\'content\')
    @if($user)
        <h1>Привет, {{ $user->name }}!</h1>
    @endif

    @foreach($posts as $post)
        <p>{{ $post->title }}</p>
    @endforeach
@endsection',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.inertia_frontend',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Blade Components и как работают slots?',
                'answer' => 'Blade-компонент - это переиспользуемый кусок UI (как в React/Vue). Создаётся через make:component, имеет класс с свойствами и шаблон. В шаблоне через <x-component-name>. Slots - именованные "дырки" для вставки контента: {{ $slot }} (default), <x-slot name="header">.',
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
                'answer' => '@props объявляет свойства анонимного компонента (без класса). Можно задать значение по умолчанию. Все остальные атрибуты тега попадают в $attributes и могут быть выведены через {{ $attributes }}.',
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
                'answer' => 'Inertia.js - это "монолит с SPA-чувствами". Простыми словами: вы пишете обычный Laravel (controllers, routes), но возвращаете не Blade, а компоненты Vue/React/Svelte. Inertia сам обновляет страницу через AJAX, без перезагрузки. Идея: иметь SPA без отдельного API. Не нужно строить REST или GraphQL.',
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
                'answer' => 'Livewire - это пакет для создания "реактивных" интерфейсов на чистом PHP/Blade без написания JavaScript. Простыми словами: ваш компонент - это PHP-класс + Blade-шаблон, а Livewire под капотом сам делает AJAX-запросы при изменении свойств. Идея: SPA без SPA, для тех кто не хочет учить Vue/React.',
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
                'answer' => '@once гарантирует, что блок отрендерится один раз за HTTP-ответ, даже если родительский шаблон/компонент включается несколько раз — типичный кейс: пушить JS/CSS из компонента, который повторяется на странице, в общий @stack. @verbatim — отключает интерпретацию {{ }} и @-директив внутри блока. Нужен когда страница использует Vue/Alpine/Mustache: их шаблонные {{ }} конфликтуют с Blade, без @verbatim пришлось бы экранировать каждую фигурную скобку.',
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
                'answer' => 'Volt - single-file API для Livewire 3-компонентов: класс компонента и Blade-шаблон описываются в ОДНОМ .blade.php-файле через функции state(), computed(), mount(), rules(). Это синтаксический сахар поверх обычного Livewire - под капотом Volt анонимно генерирует тот же Livewire-класс. Похоже на single-file components Vue (<script setup>). Удобен для небольших страниц и идеально связывается с Laravel Folio (page-based routing): один файл резко увеличивает density компонента. Для крупных компонентов часто остаются на классическом class-based Livewire (отдельный класс + view) - проще тестировать и поддерживать. Есть два варианта Volt: functional (через top-level вызовы функций - как в примере) и class-based (анонимный класс через new class extends Component внутри файла). Не путать с Laravel Volt 1.0 и Vue Volt (это другое). Появился в 2023 году.',
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
                'answer' => '@if/@elseif/@else/@endif — условия. @foreach($items as $i) ... @endforeach — циклы. @forelse — цикл с веткой @empty, если массив пуст. @extends(\'layouts.app\') + @section(\'content\')/@yield(\'content\') — наследование шаблонов. @include(\'partial\') — вставка частичного шаблона. @csrf — скрытое поле _token в формы. @method(\'PUT\') — для PUT/PATCH/DELETE из HTML-формы. @auth/@guest — проверка авторизации. @error(\'field\') — вывод ошибки валидации.',
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
                'answer' => '{{ $var }} — автоматически экранирует HTML через htmlspecialchars (защита от XSS). Если в $var лежит <script>alert(1)</script>, отобразится как текст, не выполнится. {!! $var !!} — выводит как есть, без экранирования. Использовать только когда уверен в безопасности данных (например, заранее очищенный/отрендеренный markdown). Никогда не вставлять через {!! !!} пользовательский ввод напрямую.',
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
                'answer' => 'Директива Blade, вставляющая в форму скрытое поле <input type="hidden" name="_token" value="..."> с CSRF-токеном текущей сессии. Middleware VerifyCsrfToken (включён по умолчанию для web-роутов) сверяет _token из запроса с токеном сессии, при несовпадении возвращает 419 Page Expired. Так Laravel защищает от Cross-Site Request Forgery — со стороннего сайта невозможно отправить POST/PUT/DELETE-запрос от имени залогиненного юзера, так как в нём не будет валидного токена. Для AJAX-запросов токен передаётся через заголовок X-CSRF-TOKEN (берётся из <meta name="csrf-token">).',
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
