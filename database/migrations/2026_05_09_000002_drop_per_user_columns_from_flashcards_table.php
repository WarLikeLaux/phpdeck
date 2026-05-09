<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('flashcards', function (Blueprint $table) {
            $table->dropIndex(['is_learned']);
            $table->dropIndex(['studied']);
            $table->dropIndex(['next_review_at']);
            $table->dropColumn([
                'correct_streak',
                'correct_modes',
                'required_correct',
                'is_learned',
                'studied',
                'srs_step',
                'next_review_at',
                'note',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('flashcards', function (Blueprint $table) {
            $table->unsignedInteger('correct_streak')->default(0);
            $table->json('correct_modes')->nullable();
            $table->unsignedInteger('required_correct')->default(1);
            $table->boolean('is_learned')->default(false)->index();
            $table->boolean('studied')->default(false)->index();
            $table->unsignedTinyInteger('srs_step')->default(0);
            $table->timestamp('next_review_at')->nullable()->index();
            $table->text('note')->nullable();
        });
    }
};
