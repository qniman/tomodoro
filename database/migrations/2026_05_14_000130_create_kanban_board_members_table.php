<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kanban_board_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained('kanban_boards')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['board_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kanban_board_members');
    }
};
