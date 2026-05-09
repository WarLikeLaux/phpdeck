<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flashcard_user_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('flashcard_id')->constrained()->cascadeOnDelete();
            $table->boolean('studied')->default(false);
            $table->boolean('is_learned')->default(false);
            $table->unsignedInteger('correct_streak')->default(0);
            $table->json('correct_modes')->nullable();
            $table->unsignedInteger('required_correct')->default(3);
            $table->unsignedInteger('srs_step')->default(0);
            $table->timestamp('next_review_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'flashcard_id']);
            $table->index(['user_id', 'is_learned']);
            $table->index(['user_id', 'next_review_at']);
            $table->index(['user_id', 'studied']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flashcard_user_progress');
    }
};
