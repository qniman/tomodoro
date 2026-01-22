<?php

namespace App\Providers;

use App\Models\User;
use App\Services\Todo\TaskPresetService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(TaskPresetService $presetService): void
    {
        User::created(function (User $user) use ($presetService) {
            $presetService->seedDefaultsFor($user);
        });
    }
}
