<?php

namespace Database\Seeders;

use Database\Seeders\DemoDataSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Services\Todo\TaskPresetService;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            DemoDataSeeder::class,
        ]);

        $user = User::first();
        if ($user) {
            app(TaskPresetService::class)->seedDefaultsFor($user);
        }
    }
}
