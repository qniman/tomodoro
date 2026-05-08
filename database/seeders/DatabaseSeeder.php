<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** Для нагрузочного наполнения: php artisan db:seed --class=MassSeeder */
    public function run(): void
    {
        $this->call([
            DemoSeeder::class,
        ]);
    }
}
