<?php

namespace Notify\Laravel;


use Illuminate\Support\ServiceProvider;

class NotifyServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([__DIR__ . '/config/config.php' => config_path('notify.php')]);

        $this->loadViewsFrom(__DIR__ . '/view', 'notify');

        $this->publishes([
            __DIR__ . '/view' => resource_path('views/vendor/notify'),
        ]);
    }

    public function register()
    {

    }

}