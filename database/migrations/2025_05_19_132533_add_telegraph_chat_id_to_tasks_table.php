<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('telegraph_chat_id')
                ->nullable()
                ->constrained()
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['telegraph_chat_id']);
            $table->dropColumn('telegraph_chat_id');
        });
    }
};
