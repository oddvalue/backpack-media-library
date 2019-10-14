<?php

namespace Oddvalue\BackpackMediaLibrary;

use Illuminate\Support\ServiceProvider;

class BackpackMediaLibraryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if (!$this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('media-library.php'),
            ], 'config');
            $this->loadViewsFrom(__DIR__.'/../resources/views', 'media-library');
            $this->publishes([
                __DIR__.'/../resources/views' => base_path('resources/views/vendor/media-library'),
            ], 'views');
        }
    }
    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'media-library');
    }
}
