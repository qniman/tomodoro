<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pomodoro_sessions', function (Blueprint $table) {
            $table->unsignedInteger('synced_seconds')->default(0)->after('ended_at');
        });
    }

    public function down(): void
    {
        Schema::table('pomodoro_sessions', function (Blueprint $table) {
            $table->dropColumn('synced_seconds');
        });
    }
};
