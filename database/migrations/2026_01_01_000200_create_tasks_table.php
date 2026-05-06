<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('tasks')->nullOnDelete();

            $table->string('title');
            $table->longText('description_html')->nullable();
            $table->text('description_text')->nullable();

            // low | normal | high | urgent
            $table->string('priority', 16)->default('normal');

            $table->dateTime('due_at')->nullable();
            $table->dateTime('reminder_at')->nullable();
            $table->boolean('all_day')->default(false);

            $table->dateTime('completed_at')->nullable();
            $table->boolean('is_pinned')->default(false);

            $table->unsignedInteger('estimated_minutes')->nullable();
            $table->unsignedInteger('spent_seconds')->default(0);
            $table->unsignedInteger('completed_pomodoros')->default(0);

            $table->unsignedBigInteger('position')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'completed_at']);
            $table->index(['user_id', 'due_at']);
            $table->index(['user_id', 'project_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
