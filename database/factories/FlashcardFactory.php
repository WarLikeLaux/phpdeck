<?php

namespace Database\Factories;

use App\Models\Flashcard;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Flashcard>
 */
class FlashcardFactory extends Factory
{
    protected $model = Flashcard::class;

    public function definition(): array
    {
        return [
            'category' => $this->faker->randomElement(['PHP', 'Laravel', 'OOP', 'Database']),
            'topic' => null,
            'difficulty' => $this->faker->numberBetween(1, 5),
            'question' => $this->faker->sentence().'?',
            'answer' => $this->faker->paragraph(),
            'code_example' => null,
            'code_language' => null,
            'cloze_text' => null,
            'short_answer' => null,
            'assemble_chunks' => null,
        ];
    }

    public function withCode(?string $code = null, string $language = 'php'): self
    {
        return $this->state(fn () => [
            'code_example' => $code ?? "<?php\n\$x = 1;",
            'code_language' => $language,
        ]);
    }

    public function withCloze(string $template = 'php artisan {{make:controller}} UserController'): self
    {
        return $this->state(fn () => ['cloze_text' => $template]);
    }

    public function withShortAnswer(string $value = 'explode'): self
    {
        return $this->state(fn () => ['short_answer' => $value]);
    }

    /**
     * @param  array<int, string>|null  $chunks
     */
    public function withAssemble(?array $chunks = null): self
    {
        return $this->state(fn () => [
            'assemble_chunks' => $chunks ?? ['User::', "where('active', 1)", '->', 'get()'],
        ]);
    }
}
