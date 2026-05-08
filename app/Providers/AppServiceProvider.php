<?php

namespace App\Providers;

use App\Socialite\VkIdRedirectProvider;
use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('vkontakte', VkIdRedirectProvider::class);
        });

        Vite::prefetch(concurrency: 3);
        Carbon::setLocale('ru');
    }
}
