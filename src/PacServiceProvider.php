<?php

namespace FeiMx\Pac;

use FeiMx\Pac\Contracts\Factory;
use Illuminate\Support\ServiceProvider;

class PacServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/pac.php' => config_path('pac.php'),
            ], 'config');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/pac.php', 'pac'
        );

        $this->app->singleton(Factory::class, function ($app) {
            return new PacManager($app);
        });
    }

    public function provides()
    {
        return [Factory::class];
    }
}
