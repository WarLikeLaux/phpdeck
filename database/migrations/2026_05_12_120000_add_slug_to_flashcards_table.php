<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('flashcards', function (Blueprint $table) {
            $table->string('slug', 64)->nullable()->after('id');
        });

        // Backfill для существующих карточек: slug = sha256(category|question).
        // Делаем без unique-индекса, потому что дубли вопросов в исторических данных
        // (если есть) дадут одинаковый slug — мы их допреобразуем дальше через сидер.
        DB::table('flashcards')->orderBy('id')->lazyById()->each(function ($card) {
            $slug = hash('sha256', $card->category.'|'.$card->question);
            DB::table('flashcards')->where('id', $card->id)->update(['slug' => $slug]);
        });

        // Если в данных оказались дубли по slug, оставляем минимальный id, остальные удаляем.
        DB::statement(<<<'SQL'
            DELETE FROM flashcards
            WHERE id IN (
                SELECT id FROM (
                    SELECT id, slug,
                           ROW_NUMBER() OVER (PARTITION BY slug ORDER BY id) AS rn
                    FROM flashcards
                    WHERE slug IS NOT NULL
                )
                WHERE rn > 1
            )
        SQL);

        Schema::table('flashcards', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('flashcards', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
