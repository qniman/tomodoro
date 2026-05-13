<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('kanban_column_id')
                ->nullable()
                ->after('project_id')
                ->constrained('kanban_columns')
                ->nullOnDelete();

            $table->unsignedBigInteger('kanban_position')->default(0)->after('kanban_column_id');

            $table->index(['kanban_column_id', 'kanban_position']);
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['kanban_column_id']);
            $table->dropIndex(['kanban_column_id', 'kanban_position']);
            $table->dropColumn(['kanban_column_id', 'kanban_position']);
        });
    }
};
