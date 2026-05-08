<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Ограничение названия тега до 20 символов (валидация в приложении; БД дополнительно сужается на MySQL/PGSQL).
 */
return new class extends Migration
{
    public function up(): void
    {
        $tags = DB::table('tags')->orderBy('id')->get();

        foreach ($tags as $row) {
            $userId = $row->user_id;
            $raw = trim((string) $row->name);
            $base = mb_substr($raw !== '' ? $raw : 'tag', 0, 20);

            $candidate = $base;
            $n = 0;

            while (
                DB::table('tags')
                    ->where('user_id', $userId)
                    ->where('name', $candidate)
                    ->where('id', '!=', $row->id)
                    ->exists()
            ) {
                $n++;
                $suffix = '-'.$n;
                $suffixLen = mb_strlen($suffix);
                $maxBase = max(1, 20 - $suffixLen);
                $candidate = mb_substr($base, 0, $maxBase).$suffix;
            }

            if ($candidate !== $row->name) {
                DB::table('tags')->where('id', $row->id)->update(['name' => $candidate]);
            }
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE tags MODIFY COLUMN name VARCHAR(20) NOT NULL');
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE tags ALTER COLUMN name TYPE VARCHAR(20)');
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE tags MODIFY COLUMN name VARCHAR(64) NOT NULL');
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE tags ALTER COLUMN name TYPE VARCHAR(64)');
        }
    }
};
