<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pomodoro_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained()->nullOnDelete();

            // queued | running | paused | finished | aborted
            $table->string('status', 16)->default('queued');
            // work | short_break | long_break
            $table->string('phase', 16)->default('work');

            $table->unsignedInteger('work_seconds')->default(1500);
            $table->unsignedInteger('short_break_seconds')->default(300);
            $table->unsignedInteger('long_break_seconds')->default(900);
            $table->unsignedInteger('long_break_every')->default(4);

            $table->unsignedInteger('total_pomodoros')->default(1);
            $table->unsignedInteger('completed_pomodoros')->default(0);

            $table->dateTime('phase_started_at')->nullable();
            $table->dateTime('paused_at')->nullable();
            $table->unsignedInteger('synced_seconds')->default(0);

            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pomodoro_sessions');
    }
};
