<?php

namespace Database\Seeders\Data\Categories\Laravel;

class ApiResources
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Laravel',
                'question' => 'Что такое API Resources в Laravel?',
                'answer' => '**API Resource** — класс-трансформер Eloquent-модели в JSON для API. Лежит в `app/Http/Resources`, наследует `JsonResource`, реализует `toArray($request)`.

**Зачем он нужен:**

- **Скрыть лишние поля** — пароль, FK, `remember_token`, внутренние флаги.
- **Зафиксировать контракт** — структура ответа не зависит от схемы БД, миграция не сломает клиентов.
- **Форматировать** — даты в ISO 8601, деньги в копейках/рублях, флаги в bool.
- **Условные поля** — `when()`, `whenLoaded()`, `whenPivotLoaded()` — отдать только если есть данные/связь подгружена/право доступа.

**Способы использования:**

- **Одна модель** — `new UserResource($user)`.
- **Коллекция** — `UserResource::collection(User::paginate(15))` — корректно работает с пагинатором (`data`/`meta`/`links`).
- **Собственная коллекция** — `class UsersCollection extends ResourceCollection` — если нужны общие meta.

**Подводные камни:**

- **`whenLoaded(\'posts\')`** — спасает от N+1: без `with(\'posts\')` Resource не дёрнет связь, не «случайно» сделает 100 запросов.
- **`new JsonResource::withoutWrapping()`** — убрать оборачивающий ключ `data`.
- В `toArray` доступ к атрибутам идёт через `$this->...` — это прокси к модели.',
                'code_example' => 'class UserResource extends JsonResource {
    public function toArray($request): array {
        return [
            \'id\' => $this->id,
            \'name\' => $this->name,
            \'email\' => $this->when((bool) $request->user()?->is_admin, $this->email),
            \'posts\' => PostResource::collection($this->whenLoaded(\'posts\')),
        ];
    }
}

return new UserResource($user);
return UserResource::collection(User::all());',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.api_resources',
            ],
        ];
    }
}
