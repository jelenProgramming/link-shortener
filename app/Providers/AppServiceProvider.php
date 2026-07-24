<?php

namespace App\Providers;

use App\Models\Link;
use App\Policies\LinkPolicy;
use Illuminate\Support\Facades\Gate;
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
        Gate::policy(Link::class, LinkPolicy::class);

        // behind Render's tls proxy the request looks like http, force https so
        // short_url and any generated url use the correct scheme
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
