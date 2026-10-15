<?php

namespace App\Providers;

use App\Models\Setting;
use App\Services\CartService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CartService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Event::listen(Login::class, function (Login $event) {
            app(CartService::class)->mergeGuestCartIntoUser($event->user);
        });

        View::composer('components.layouts.storefront', function ($view) {
            $view->with('cartItemCount', app(CartService::class)->itemCount());
            $view->with('wishlistCount', Auth::check() ? Auth::user()->wishlistedProducts()->count() : 0);
            $view->with('siteSettings', Setting::query()->pluck('value', 'key'));
        });
    }
}
