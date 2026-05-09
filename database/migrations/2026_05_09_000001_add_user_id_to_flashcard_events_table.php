<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('flashcard_events')->truncate();

        Schema::table('flashcard_events', function (Blueprint $table) {
            $table->foreignId('user_id')->after('id')->constrained()->cascadeOnDelete();
            $table->index(['user_id', 'occurred_at']);
            $table->index(['user_id', 'flashcard_id']);
        });
    }

    public function down(): void
    {
        Schema::table('flashcard_events', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'occurred_at']);
            $table->dropIndex(['user_id', 'flashcard_id']);
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
