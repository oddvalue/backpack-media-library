<?php

namespace Oddvalue\BackpackMediaLibrary;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class BackpackMediaLibraryServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;


    /**
     * Where the route file lives, both inside the package and in the app (if overwritten).
     *
     * @var string
     */
    public $routeFilePath = '/routes/backpack/media-library.php';

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        // define the routes for the application
        $this->setupRoutes($this->app->router);

        if (!$this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('media-library.php'),
            ], 'config');
            $this->loadViewsFrom(__DIR__.'/../resources/views', 'media-library');
            $this->publishes([
                __DIR__.'/../resources/views' => base_path('resources/views/vendor/media-library'),
            ], 'views');
        }

        // publish the migrations and seeds
        $this->publishes([__DIR__.'/../database/migrations/' => database_path('migrations')], 'migrations');
        $this->publishes([__DIR__.'/../resources/js/dist/' => public_path('packages/media-library/js/')], 'public');
        // publish translation files
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'media-library');
    }

    /**
     * Define the routes for the application.
     *
     * @param \Illuminate\Routing\Router $router
     *
     * @return void
     */
    public function setupRoutes(Router $router)
    {
        // by default, use the routes file provided in vendor
        $routeFilePathInUse = __DIR__.'/..'.$this->routeFilePath;
        // but if there's a file with the same name in routes/backpack, use that one
        if (file_exists(base_path().$this->routeFilePath)) {
            $routeFilePathInUse = base_path().$this->routeFilePath;
        }
        $this->loadRoutesFrom($routeFilePathInUse);
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'media-library');
    }
}
