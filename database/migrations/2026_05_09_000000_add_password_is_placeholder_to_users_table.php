<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Аккаунты, созданные только через OAuth: пароль случайный, пользователь может задать свой в настройках.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('password_is_placeholder')->default(false);
        });

        $domain = config('services.vkontakte.placeholder_email_domain', 'oauth.local');

        DB::table('users')
            ->whereNotNull('vk_id')
            ->where('email', 'like', 'vk_oauth_%@'.$domain)
            ->update(['password_is_placeholder' => true]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('password_is_placeholder');
        });
    }
};
