<?php

namespace Notify\Laravel;


use Illuminate\Support\ServiceProvider;

class NotifyServiceProvider extends ServiceProvider
{
    /**
     * Publishes notify.php config file and mail.php view file.
     */
    public function boot()
    {
        $this->publishes([__DIR__ . '/config/config.php' => config_path('notify.php')]);

        $this->loadViewsFrom(__DIR__ . '/view', 'notify');

        $this->publishes([
            __DIR__ . '/view' => resource_path('views/vendor/notify'),
        ]);
    }

    /**
     * Register for Facade. Facade uses default constructor.
     */
    public function register()
    {
        $this->app->bind('notify.laravel', function($app) {
            return new Notify();
        });

    }

}