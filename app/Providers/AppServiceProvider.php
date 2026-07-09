<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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
    public function boot(): void
    {
        $host = request()->getHost();

        if (! $this->app->runningInConsole() && ! str_contains($host, 'localhost')) {
            URL::forceRootUrl('https://'.$host);
            URL::forceScheme('https');
        }
    }
}
