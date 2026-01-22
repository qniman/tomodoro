<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pomodoro_sessions', function (Blueprint $table) {
            $table->enum('phase', ['work', 'break'])->default('work')->after('status');
            $table->dateTime('paused_at')->nullable()->after('synced_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('pomodoro_sessions', function (Blueprint $table) {
            $table->dropColumn(['phase', 'paused_at']);
        });
    }
};
