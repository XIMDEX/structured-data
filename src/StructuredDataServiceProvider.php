<?php

namespace Ximdex\StructuredData;

use Illuminate\Support\ServiceProvider;

class StructuredDataServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'xlinkeddata');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'xlinkeddata');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
        
        $this->commands([
            \Ximdex\StructuredData\Commands\SchemaImporter::class
        ]);
        
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/configs.php', 'structureddata');

        // Register the service the package provides.
        $this->app->singleton('structureddata', function ($app) {
            return new StructuredData;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['structureddata'];
    }
    
    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/configs.php' => config_path('structureddata.php'),
        ], 'structureddata.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/xlinkeddata'),
        ], 'linkeddata.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/xlinkeddata'),
        ], 'linkeddata.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/xlinkeddata'),
        ], 'linkeddata.views');*/

        // Registering package commands.
       
    }
}
